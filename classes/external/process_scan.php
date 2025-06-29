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
 * External API for processing barcode scans.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_system;
use local_equipment\inventory\barcode_scanner;

/**
 * External API for processing barcode scans.
 */
class process_scan extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'barcode_data' => new external_value(PARAM_TEXT, 'Scanned barcode data'),
            'scan_type' => new external_value(PARAM_ALPHA, 'Type of scan (auto, qr, upc)', VALUE_DEFAULT, 'auto'),
            'session_id' => new external_value(PARAM_ALPHANUMEXT, 'Scan session ID', VALUE_OPTIONAL, ''),
        ]);
    }

    /**
     * Process a barcode scan.
     *
     * @param string $barcode_data Scanned barcode data
     * @param string $scan_type Type of scan
     * @param string $session_id Scan session ID
     * @return array Scan result
     */
    public static function execute(string $barcode_data, string $scan_type = 'auto', string $session_id = ''): array {
        global $USER;

        // Validate parameters
        $params = self::validate_parameters(self::execute_parameters(), [
            'barcode_data' => $barcode_data,
            'scan_type' => $scan_type,
            'session_id' => $session_id,
        ]);

        // Check capability
        $context = context_system::instance();
        require_capability('local/equipment:checkinout', $context);

        // Process the scan
        $scanner = new barcode_scanner();
        $result = $scanner->process_scan($params['barcode_data'], $params['scan_type']);

        // Log the scan attempt
        self::log_scan_attempt($params['barcode_data'], $params['scan_type'], $result, $params['session_id']);

        return $result;
    }

    /**
     * Returns description of method return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the scan was successful'),
            'timestamp' => new external_value(PARAM_INT, 'Scan timestamp'),
            'error_code' => new external_value(PARAM_ALPHANUMEXT, 'Error code if failed', VALUE_OPTIONAL),
            'message' => new external_value(PARAM_TEXT, 'Error message if failed', VALUE_OPTIONAL),
            'data' => new external_single_structure([
                'scan_type' => new external_value(PARAM_ALPHA, 'Type of scan performed'),
                'barcode_data' => new external_value(PARAM_TEXT, 'Original barcode data'),
                'item' => new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Item ID'),
                    'uuid' => new external_value(PARAM_TEXT, 'Item UUID'),
                    'status' => new external_value(PARAM_ALPHA, 'Item status'),
                    'condition_status' => new external_value(PARAM_ALPHA, 'Item condition'),
                    'student_label' => new external_value(PARAM_TEXT, 'Student label', VALUE_OPTIONAL),
                ], 'Equipment item information', VALUE_OPTIONAL),
                'product' => new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Product ID'),
                    'name' => new external_value(PARAM_TEXT, 'Product name'),
                    'manufacturer' => new external_value(PARAM_TEXT, 'Manufacturer', VALUE_OPTIONAL),
                    'category' => new external_value(PARAM_TEXT, 'Category', VALUE_OPTIONAL),
                    'is_consumable' => new external_value(PARAM_BOOL, 'Is consumable'),
                ], 'Product information', VALUE_OPTIONAL),
                'location' => new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Location ID'),
                    'name' => new external_value(PARAM_TEXT, 'Location name'),
                    'description' => new external_value(PARAM_TEXT, 'Location description', VALUE_OPTIONAL),
                ], 'Location information', VALUE_OPTIONAL),
                'current_user' => new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'User ID'),
                    'firstname' => new external_value(PARAM_TEXT, 'First name'),
                    'lastname' => new external_value(PARAM_TEXT, 'Last name'),
                    'email' => new external_value(PARAM_EMAIL, 'Email'),
                ], 'Current user assignment', VALUE_OPTIONAL),
                'available_actions' => new external_multiple_structure(
                    new external_value(PARAM_ALPHA, 'Available action'),
                    'Available actions for this item',
                    VALUE_OPTIONAL
                ),
                'available_items' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'Item ID'),
                        'uuid' => new external_value(PARAM_TEXT, 'Item UUID'),
                        'status' => new external_value(PARAM_ALPHA, 'Item status'),
                        'condition_status' => new external_value(PARAM_ALPHA, 'Item condition'),
                    ]),
                    'Available items for UPC scans',
                    VALUE_OPTIONAL
                ),
                'item_count' => new external_value(PARAM_INT, 'Number of available items', VALUE_OPTIONAL),
                'source' => new external_value(PARAM_ALPHA, 'Data source (local_database, brocade_api)', VALUE_OPTIONAL),
                'product_info' => new external_single_structure([
                    'name' => new external_value(PARAM_TEXT, 'Product name'),
                    'description' => new external_value(PARAM_TEXT, 'Product description', VALUE_OPTIONAL),
                    'manufacturer' => new external_value(PARAM_TEXT, 'Manufacturer', VALUE_OPTIONAL),
                    'upc' => new external_value(PARAM_TEXT, 'UPC code'),
                    'category' => new external_value(PARAM_TEXT, 'Category', VALUE_OPTIONAL),
                    'image_url' => new external_value(PARAM_URL, 'Product image URL', VALUE_OPTIONAL),
                ], 'External product information', VALUE_OPTIONAL),
                'can_add_to_catalog' => new external_value(PARAM_BOOL, 'Can add to product catalog', VALUE_OPTIONAL),
            ], 'Scan result data', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Log scan attempt for debugging and analytics.
     *
     * @param string $barcode_data Barcode data
     * @param string $scan_type Scan type
     * @param array $result Scan result
     * @param string $session_id Session ID
     */
    private static function log_scan_attempt(string $barcode_data, string $scan_type, array $result, string $session_id): void {
        global $DB, $USER;

        try {
            $log_record = new \stdClass();
            $log_record->userid = $USER->id;
            $log_record->barcode_data = substr($barcode_data, 0, 255); // Truncate if too long
            $log_record->scan_type = $scan_type;
            $log_record->success = $result['success'] ? 1 : 0;
            $log_record->error_code = $result['error_code'] ?? null;
            $log_record->session_id = $session_id;
            $log_record->timestamp = time();

            // We'll create this table in the next database update
            // $DB->insert_record('local_equipment_scan_log', $log_record);

            // For now, just log to debugging
            debugging('Scan attempt: ' . json_encode($log_record), DEBUG_DEVELOPER);
        } catch (\Exception $e) {
            // Don't let logging errors break the scan process
            debugging('Failed to log scan attempt: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
}
