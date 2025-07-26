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
     * Execute the equipment removal validation.
     *
     * @param string $barcode Barcode data
     * @param string $type Barcode type
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
                'message' => 'Barcode is required',
                'error_code' => 'missing_barcode'
            ];
        }

        // Debug logging using Moodle standards
        debugging("Equipment removal attempt - Barcode: '{$barcode}', Type: '{$barcode_type}', User ID: {$USER->id}", DEBUG_DEVELOPER);

        try {
            $transaction = $DB->start_delegated_transaction();
            debugging('Database transaction started for removal validation', DEBUG_DEVELOPER);

            $result = null;

            if ($barcode_type === 'uuid') {
                debugging('Processing UUID removal for barcode: ' . $barcode, DEBUG_DEVELOPER);
                $result = self::process_uuid_removal($barcode);
            } elseif ($barcode_type === 'upc') {
                debugging('Processing UPC removal for barcode: ' . $barcode, DEBUG_DEVELOPER);
                $result = self::process_upc_removal($barcode);
            } else {
                debugging('Auto-detecting barcode type for: ' . $barcode, DEBUG_DEVELOPER);
                $result = self::process_auto_detect_removal($barcode);
            }

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

        // Find item by UUID.
        $item = $DB->get_record('local_equipment_items', ['uuid' => $uuid]);

        if (!$item) {
            return [
                'success' => false,
                'message' => 'Equipment item not found with UUID: ' . $uuid,
                'error_code' => 'item_not_found',
                'barcode' => $uuid
            ];
        }

        // Check if already removed.
        if ($item->status === 'removed') {
            $product = $DB->get_record('local_equipment_products', ['id' => $item->productid]);
            return [
                'success' => false,
                'message' => 'Item has already been removed from inventory',
                'error_code' => 'already_removed',
                'product_name' => $product ? $product->name : 'Unknown Product',
                'barcode' => $uuid
            ];
        }

        // Check if currently checked out.
        if ($item->status === 'checkedout' && $item->current_userid) {
            $current_user = $DB->get_record('user', ['id' => $item->current_userid]);
            return [
                'success' => false,
                'message' => 'Cannot remove item that is currently checked out',
                'error_code' => 'item_checked_out',
                'current_user' => $current_user ? fullname($current_user) : 'Unknown User',
                'barcode' => $uuid
            ];
        }

        // Get product information.
        $product = $DB->get_record('local_equipment_products', ['id' => $item->productid]);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product information not found for this item',
                'error_code' => 'product_not_found',
                'barcode' => $uuid
            ];
        }

        // Perform the removal.
        return self::perform_item_removal($item, $product, 'qr_code', 'Standard QR code removal');
    }

    /**
     * Process UPC-based removal (emergency removal for items without QR codes).
     *
     * @param string $upc UPC barcode
     * @return array Result array
     */
    private static function process_upc_removal($upc) {
        global $DB, $USER;

        // Normalize UPC code.
        $normalized_upc = self::normalize_upc($upc);

        if (!$normalized_upc) {
            return [
                'success' => false,
                'message' => 'Invalid UPC format: "' . $upc . '". UPC must be 8-14 digits.',
                'error_code' => 'invalid_upc_format',
                'barcode' => $upc
            ];
        }

        // Find product with this UPC.
        $product = $DB->get_record('local_equipment_products', ['upc' => $normalized_upc, 'active' => 1]);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found with UPC: ' . $normalized_upc,
                'error_code' => 'product_not_found',
                'barcode' => $upc
            ];
        }

        // Find available items of this product type that are NOT removed.
        $items = $DB->get_records_select(
            'local_equipment_items',
            'productid = ? AND status != ?',
            [$product->id, 'removed'],
            'timecreated ASC' // Oldest first
        );

        if (empty($items)) {
            return [
                'success' => false,
                'message' => 'No items found for product: ' . $product->name,
                'error_code' => 'no_items_found',
                'barcode' => $upc
            ];
        }

        // Find items that do NOT have QR codes (emergency removal candidates).
        $emergency_candidates = [];
        $items_with_qr = [];

        foreach ($items as $item) {
            if (empty($item->uuid) || $item->uuid === '' || $item->uuid === null) {
                // Item has no QR code - emergency removal candidate.
                $emergency_candidates[] = $item;
            } else {
                // Item has QR code.
                $items_with_qr[] = $item;
            }
        }

        if (empty($emergency_candidates) && !empty($items_with_qr)) {
            // All items have QR codes - block UPC removal.
            return [
                'success' => false,
                'message' => 'This product has items with QR codes. Please scan the QR code instead of the UPC barcode to remove specific items.',
                'error_code' => 'upc_with_qr_exists',
                'barcode' => $upc,
                'product_name' => $product->name,
                'items_with_qr_count' => count($items_with_qr)
            ];
        }

        if (empty($emergency_candidates)) {
            return [
                'success' => false,
                'message' => 'No items available for emergency removal for product: ' . $product->name,
                'error_code' => 'no_emergency_candidates',
                'barcode' => $upc
            ];
        }

        // Select the first available emergency candidate.
        $item_to_remove = reset($emergency_candidates);

        // Check if currently checked out.
        if ($item_to_remove->status === 'checkedout' && $item_to_remove->current_userid) {
            $current_user = $DB->get_record('user', ['id' => $item_to_remove->current_userid]);
            return [
                'success' => false,
                'message' => 'Cannot remove item that is currently checked out',
                'error_code' => 'item_checked_out',
                'current_user' => $current_user ? fullname($current_user) : 'Unknown User',
                'barcode' => $upc
            ];
        }

        // Perform the emergency removal.
        return self::perform_item_removal($item_to_remove, $product, 'emergency_upc', 'Emergency UPC removal (no QR code)');
    }

    /**
     * Process auto-detection removal (try to determine barcode type).
     *
     * @param string $barcode Barcode data
     * @return array Result array
     */
    private static function process_auto_detect_removal($barcode) {
        // UUID pattern (standard 8-4-4-4-12 format).
        $uuid_pattern = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';

        // UPC pattern (8-14 digits).
        $upc_pattern = '/^\d{8,14}$/';

        if (preg_match($uuid_pattern, $barcode)) {
            return self::process_uuid_removal($barcode);
        } elseif (preg_match($upc_pattern, $barcode)) {
            return self::process_upc_removal($barcode);
        } else {
            return [
                'success' => false,
                'message' => 'Invalid barcode format. Please scan a valid QR code or UPC barcode.',
                'error_code' => 'invalid_barcode_type',
                'barcode' => $barcode
            ];
        }
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

        // Return success response.
        return [
            'success' => true,
            'item_id' => $item->id,
            'uuid' => $item->uuid,
            'product_name' => $product->name,
            'removal_method' => $removal_method,
            'transaction_id' => $transaction_id,
            'message' => 'Item successfully removed from inventory'
        ];
    }

    /**
     * Normalize UPC code to standard 12-digit format.
     *
     * @param string $upc Raw UPC code from scanner
     * @return string|false Normalized 12-digit UPC or false if invalid
     */
    private static function normalize_upc($upc) {
        // Remove any non-numeric characters.
        $upc = preg_replace('/[^0-9]/', '', $upc);

        // Check if it's a valid length (8-14 digits).
        $length = strlen($upc);
        if ($length < 8 || $length > 14) {
            return false;
        }

        // Handle different UPC formats.
        switch ($length) {
            case 8:
                // UPC-E format - expand to UPC-A.
                // This is a simplified expansion - real UPC-E is more complex.
                // For now, just pad with zeros to make 12 digits.
                return str_pad($upc, 12, '0', STR_PAD_LEFT);

            case 12:
                // UPC-A format - this is our target format.
                return $upc;

            case 13:
                // EAN-13 format - remove leading zero if it's 0.
                if (substr($upc, 0, 1) === '0') {
                    return substr($upc, 1);
                }
                // If it doesn't start with 0, it's a non-US EAN code.
                // Store as-is but log it.
                error_log("Non-US EAN-13 code detected: $upc");
                return $upc;

            case 14:
                // GTIN-14 format - remove leading zeros.
                return ltrim($upc, '0');

            default:
                // 9, 10, 11 digits - pad to 12.
                return str_pad($upc, 12, '0', STR_PAD_LEFT);
        }
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
            'barcode' => new external_value(PARAM_TEXT, 'Original barcode', VALUE_OPTIONAL),
            'current_user' => new external_value(PARAM_TEXT, 'Current user if checked out', VALUE_OPTIONAL),
            'items_with_qr_count' => new external_value(PARAM_INT, 'Count of items with QR codes', VALUE_OPTIONAL),
            'debug_info' => new external_value(PARAM_RAW, 'Debug information for developer mode', VALUE_OPTIONAL),
        ]);
    }
}
