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
 * External API for equipment lookup operations.
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
use core_external\external_multiple_structure;
use core_external\external_value;

/**
 * External API for equipment lookup operations.
 */
class lookup_equipment extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'uuid' => new external_value(PARAM_ALPHANUMEXT, 'Equipment UUID to lookup'),
        ]);
    }

    /**
     * Lookup equipment by UUID.
     *
     * @param string $uuid Equipment UUID
     * @return array Equipment details
     */
    public static function execute(string $uuid): array {
        global $USER;

        // Validate parameters
        $params = self::validate_parameters(self::execute_parameters(), [
            'uuid' => $uuid,
        ]);

        // Check capabilities
        $context = \context_system::instance();
        require_capability('local/equipment:checkinout', $context);

        try {
            $inventory_manager = new \local_equipment\inventory\inventory_manager();
            $result = $inventory_manager->get_equipment_details($params['uuid']);

            if ($result->success) {
                return [
                    'success' => true,
                    'item' => [
                        'id' => $result->item->id,
                        'uuid' => $result->item->uuid,
                        'product_name' => $result->item->product_name,
                        'manufacturer' => $result->item->manufacturer ?? '',
                        'category' => $result->item->category ?? '',
                        'description' => $result->item->description ?? '',
                        'status' => $result->item->status,
                        'condition_status' => $result->item->condition_status,
                        'condition_notes' => $result->item->condition_notes ?? '',
                        'location_name' => $result->item->location_name ?? '',
                        'current_userid' => $result->item->current_userid,
                        'user_id' => $result->item->user_id,
                        'firstname' => $result->item->firstname ?? '',
                        'lastname' => $result->item->lastname ?? '',
                        'user_email' => $result->item->user_email ?? '',
                        'student_label' => $result->item->student_label ?? '',
                        'serial_number' => $result->item->serial_number ?? '',
                        'last_tested' => $result->item->last_tested,
                        'timecreated' => $result->item->timecreated,
                        'timemodified' => $result->item->timemodified,
                    ],
                    'transactions' => array_map(function ($transaction) {
                        return [
                            'id' => $transaction->id,
                            'transaction_type' => $transaction->transaction_type,
                            'timestamp' => $transaction->timestamp,
                            'notes' => $transaction->notes ?? '',
                            'condition_before' => $transaction->condition_before ?? '',
                            'condition_after' => $transaction->condition_after ?? '',
                            'from_firstname' => $transaction->from_firstname ?? '',
                            'from_lastname' => $transaction->from_lastname ?? '',
                            'to_firstname' => $transaction->to_firstname ?? '',
                            'to_lastname' => $transaction->to_lastname ?? '',
                            'from_location' => $transaction->from_location ?? '',
                            'to_location' => $transaction->to_location ?? '',
                        ];
                    }, $result->transactions),
                    'message' => '',
                ];
            } else {
                return [
                    'success' => false,
                    'item' => null,
                    'transactions' => [],
                    'message' => $result->message,
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'item' => null,
                'transactions' => [],
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the lookup was successful'),
            'message' => new external_value(PARAM_TEXT, 'Success or error message'),
            'item' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Item ID'),
                'uuid' => new external_value(PARAM_ALPHANUMEXT, 'Item UUID'),
                'product_name' => new external_value(PARAM_TEXT, 'Product name'),
                'manufacturer' => new external_value(PARAM_TEXT, 'Manufacturer'),
                'category' => new external_value(PARAM_TEXT, 'Category'),
                'description' => new external_value(PARAM_TEXT, 'Description'),
                'status' => new external_value(PARAM_TEXT, 'Current status'),
                'condition_status' => new external_value(PARAM_TEXT, 'Condition status'),
                'condition_notes' => new external_value(PARAM_TEXT, 'Condition notes'),
                'location_name' => new external_value(PARAM_TEXT, 'Current location name'),
                'current_userid' => new external_value(PARAM_INT, 'Current user ID', VALUE_OPTIONAL),
                'user_id' => new external_value(PARAM_INT, 'User ID', VALUE_OPTIONAL),
                'firstname' => new external_value(PARAM_TEXT, 'User first name'),
                'lastname' => new external_value(PARAM_TEXT, 'User last name'),
                'user_email' => new external_value(PARAM_EMAIL, 'User email'),
                'student_label' => new external_value(PARAM_TEXT, 'Student label'),
                'serial_number' => new external_value(PARAM_TEXT, 'Serial number'),
                'last_tested' => new external_value(PARAM_INT, 'Last tested timestamp', VALUE_OPTIONAL),
                'timecreated' => new external_value(PARAM_INT, 'Created timestamp'),
                'timemodified' => new external_value(PARAM_INT, 'Modified timestamp'),
            ], 'Equipment item details', VALUE_OPTIONAL),
            'transactions' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Transaction ID'),
                    'transaction_type' => new external_value(PARAM_TEXT, 'Transaction type'),
                    'timestamp' => new external_value(PARAM_INT, 'Transaction timestamp'),
                    'notes' => new external_value(PARAM_TEXT, 'Transaction notes'),
                    'condition_before' => new external_value(PARAM_TEXT, 'Condition before'),
                    'condition_after' => new external_value(PARAM_TEXT, 'Condition after'),
                    'from_firstname' => new external_value(PARAM_TEXT, 'From user first name'),
                    'from_lastname' => new external_value(PARAM_TEXT, 'From user last name'),
                    'to_firstname' => new external_value(PARAM_TEXT, 'To user first name'),
                    'to_lastname' => new external_value(PARAM_TEXT, 'To user last name'),
                    'from_location' => new external_value(PARAM_TEXT, 'From location'),
                    'to_location' => new external_value(PARAM_TEXT, 'To location'),
                ]),
                'Recent transactions'
            ),
        ]);
    }
}
