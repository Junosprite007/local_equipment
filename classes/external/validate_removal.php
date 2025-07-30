<?php
// This file is part of FLIP Plugins for Moodle
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * External web service for barcode-based equipment removal validation and processing.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External web service class for equipment removal validation.
 */
class validate_removal extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'barcode' => new external_value(PARAM_TEXT, 'Barcode data (UUID or UPC)', VALUE_REQUIRED),
            'type' => new external_value(PARAM_TEXT, 'Barcode type (uuid, upc, unknown)', VALUE_DEFAULT, 'unknown'),
        ]);
    }

    /**
     * Execute the equipment removal validation (QR Code UUID only).
     *
     * @param string $barcode QR Code UUID data
     * @param string $type Barcode type (must be 'uuid' for removal)
     * @return array Result array
     */
    public static function execute($barcode, $type = 'unknown') {
        global $DB, $USER, $CFG;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'barcode' => $barcode,
            'type' => $type,
        ]);

        // Check capabilities.
        $context = \context_system::instance();
        require_capability('local/equipment:manageinventory', $context);

        // Validate inputs.
        $barcode = trim($params['barcode']);
        $barcode_type = trim($params['type']);

        if (empty($barcode)) {
            debugging('Empty barcode provided in removal validation', DEBUG_DEVELOPER);
            return [
                'success' => false,
                'message' => 'UUID is required for equipment removal',
                'error_code' => 'missing_uuid'
            ];
        }

        // Debug logging using Moodle standards
        debugging("Equipment removal attempt - UUID: '{$barcode}', User ID: {$USER->id}", DEBUG_DEVELOPER);

        try {
            $transaction = $DB->start_delegated_transaction();
            debugging('Database transaction started for removal validation', DEBUG_DEVELOPER);

            // Only process UUID-based removal (QR codes)
            $result = self::process_uuid_removal($barcode);

            debugging('Removal processing result: ' . json_encode($result), DEBUG_DEVELOPER);

            if ($result['success']) {
                $transaction->allow_commit();
                debugging('Database transaction committed successfully', DEBUG_DEVELOPER);
            } else {
                // Transaction will be rolled back automatically if not committed
                debugging('Database transaction will be rolled back due to failure', DEBUG_DEVELOPER);
            }

            return $result;
        } catch (\Exception $e) {
            // Transaction will be rolled back automatically

            // Comprehensive error logging for developer mode
            debugging('Equipment removal validation exception occurred', DEBUG_DEVELOPER);
            debugging('Exception message: ' . $e->getMessage(), DEBUG_DEVELOPER);
            debugging('Exception file: ' . $e->getFile() . ' (line ' . $e->getLine() . ')', DEBUG_DEVELOPER);
            debugging('Exception stack trace: ' . $e->getTraceAsString(), DEBUG_DEVELOPER);

            // Return detailed error in developer mode, generic message otherwise
            $error_message = 'Database error occurred. Please try again.';
            $detailed_error = $e->getMessage();

            if ($CFG->debugdeveloper) {
                $error_message = 'Exception: ' . $detailed_error . ' (File: ' . basename($e->getFile()) . ':' . $e->getLine() . ')';
            }

            return [
                'success' => false,
                'message' => $error_message,
                'error_code' => 'database_exception',
                'debug_info' => $CFG->debugdeveloper ? [
                    'exception_class' => get_class($e),
                    'exception_message' => $e->getMessage(),
                    'exception_file' => $e->getFile(),
                    'exception_line' => $e->getLine(),
                    'stack_trace' => $e->getTraceAsString()
                ] : null
            ];
        }
    }

    /**
     * Process UUID-based removal (standard QR code removal).
     *
     * @param string $uuid Equipment UUID
     * @return array Result array
     */
    private static function process_uuid_removal($uuid) {
        global $DB, $USER;

        // Use the inventory manager for proper removal handling
        $inventory_manager = new \local_equipment\inventory\inventory_manager();

        // Attempt removal using the inventory manager
        $result = $inventory_manager->remove_item_from_inventory(
            $uuid,
            'qr_code_removal',
            $USER->id,
            'Standard QR code removal via scanner'
        );

        if (!$result->success) {
            // Convert inventory manager errors to external service format
            $error_code = 'removal_failed';

            if (strpos($result->message, 'not found') !== false) {
                $error_code = 'item_not_found';
            } else if (strpos($result->message, 'checked out') !== false) {
                $error_code = 'item_checked_out';
            } else if (strpos($result->message, 'already removed') !== false) {
                $error_code = 'already_removed';
            }

            return [
                'success' => false,
                'message' => $result->message,
                'error_code' => $error_code,
                'barcode' => $uuid
            ];
        }

        // Get product information for the response
        $product = $DB->get_record('local_equipment_products', ['id' => $result->item->productid]);

        return [
            'success' => true,
            'item_id' => $result->item->id,
            'uuid' => $uuid,
            'product_name' => $product ? $product->name : 'Unknown Product',
            'removal_method' => 'qr_code',
            'previous_status' => $result->previous_status,
            'message' => $result->message . ' and automatically removed from print queue'
        ];
    }


    /**
     * Perform the actual item removal and transaction logging.
     *
     * @param \stdClass $item Equipment item
     * @param \stdClass $product Product information
     * @param string $removal_method Method of removal ('qr_code' or 'emergency_upc')
     * @param string $notes Removal notes
     * @return array Result array
     */
    private static function perform_item_removal($item, $product, $removal_method, $notes) {
        global $DB, $USER;

        // Check if item is in print queue before removal
        $print_queue_manager = new \local_equipment\inventory\print_queue_manager();
        $was_in_print_queue = $print_queue_manager->item_exists_in_queue($item->id);

        // Generate UUID if item doesn't have one (for emergency UPC removals).
        if (empty($item->uuid)) {
            // Generate a simple UUID-like string.
            $item->uuid = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
            );

            // Check if UUID history table exists and add entry.
            if ($DB->get_manager()->table_exists('local_equipment_uuid_history')) {
                $uuid_history = new \stdClass();
                $uuid_history->itemid = $item->id;
                $uuid_history->uuid = $item->uuid;
                $uuid_history->is_active = 1;
                $uuid_history->created_by = $USER->id;
                $uuid_history->timecreated = time();

                $DB->insert_record('local_equipment_uuid_history', $uuid_history);
            }
        }

        // Store original user ID BEFORE we clear it.
        $original_userid = $item->current_userid;

        // Update item status to removed.
        $item->status = 'removed';
        $item->timemodified = time();
        $item->current_userid = null; // Clear any user assignment.

        $DB->update_record('local_equipment_items', $item);

        // Log the removal transaction.
        $transaction = new \stdClass();
        $transaction->itemid = $item->id;
        $transaction->transaction_type = 'removal';
        $transaction->from_userid = $original_userid;
        $transaction->from_locationid = $item->locationid ?? null;
        $transaction->processed_by = $USER->id;
        $transaction->notes = $notes;
        $transaction->condition_before = $item->condition_status ?? 'unknown';
        $transaction->condition_after = 'removed';
        $transaction->timestamp = time();

        $transaction_id = $DB->insert_record('local_equipment_transactions', $transaction);

        // Remove item from print queue if it was there
        if ($was_in_print_queue) {
            $print_queue_manager->remove_item_from_queue_by_itemid($item->id);
        }

        // Build success message
        $message = 'Item successfully removed from inventory';
        if ($was_in_print_queue) {
            $message .= ' and removed from QR print queue';
        }

        // Return success response.
        return [
            'success' => true,
            'item_id' => $item->id,
            'uuid' => $item->uuid,
            'product_name' => $product->name,
            'removal_method' => $removal_method,
            'transaction_id' => $transaction_id,
            'was_in_print_queue' => $was_in_print_queue,
            'message' => $message
        ];
    }


    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'message' => new external_value(PARAM_TEXT, 'Result message', VALUE_OPTIONAL),
            'error_code' => new external_value(PARAM_TEXT, 'Error code if applicable', VALUE_OPTIONAL),
            'item_id' => new external_value(PARAM_INT, 'Item ID', VALUE_OPTIONAL),
            'uuid' => new external_value(PARAM_TEXT, 'Item UUID', VALUE_OPTIONAL),
            'product_name' => new external_value(PARAM_TEXT, 'Product name', VALUE_OPTIONAL),
            'removal_method' => new external_value(PARAM_TEXT, 'Method of removal', VALUE_OPTIONAL),
            'transaction_id' => new external_value(PARAM_INT, 'Transaction ID', VALUE_OPTIONAL),
            'was_in_print_queue' => new external_value(PARAM_BOOL, 'Whether item was removed from print queue', VALUE_OPTIONAL),
            'barcode' => new external_value(PARAM_TEXT, 'Original barcode', VALUE_OPTIONAL),
            'current_user' => new external_value(PARAM_TEXT, 'Current user if checked out', VALUE_OPTIONAL),
            'items_with_qr_count' => new external_value(PARAM_INT, 'Count of items with QR codes', VALUE_OPTIONAL),
            'debug_info' => new external_value(PARAM_RAW, 'Debug information for developer mode', VALUE_OPTIONAL),
        ]);
    }
}
