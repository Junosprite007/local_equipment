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

declare(strict_types=1);

namespace local_equipment\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use core\external\external_api;
use core\external\external_function_parameters;
use core\external\external_single_structure;
use core\external\external_value;
use core\external\external_multiple_structure;
use local_equipment\service\vcc_submission_service;
use local_equipment\table\vcc_submissions_table;
use core\clock;

/**
 * External function for getting VCC table data via AJAX
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_table_data extends external_api {

    /** @var bool Debug flag for AJAX table data operations */
    private const DEBUG_AJAX_OPERATIONS = false;

    /** @var bool Debug flag for table query performance */
    private const DEBUG_PERFORMANCE = false;

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'page' => new external_value(PARAM_INT, 'Page number (1-based)', VALUE_DEFAULT, 1),
            'perpage' => new external_value(PARAM_INT, 'Records per page (10-100)', VALUE_DEFAULT, 25),
            'filters' => new external_single_structure([
                'partnership' => new external_value(PARAM_INT, 'Partnership ID filter', VALUE_OPTIONAL),
                'search' => new external_value(PARAM_TEXT, 'Search term', VALUE_OPTIONAL),
                'datestart' => new external_value(PARAM_INT, 'Start date timestamp', VALUE_OPTIONAL),
                'dateend' => new external_value(PARAM_INT, 'End date timestamp', VALUE_OPTIONAL)
            ], 'Filter criteria', VALUE_DEFAULT, []),
            'sesskey' => new external_value(PARAM_ALPHANUM, 'Session key for security validation')
        ]);
    }

    /**
     * Get table data for AJAX pagination
     *
     * @param int $page Page number
     * @param int $perpage Records per page
     * @param array $filters Filter criteria
     * @param string $sesskey Session key for validation
     * @return array Table data with pagination info
     */
    public static function execute(int $page, int $perpage, array $filters, string $sesskey): array {
        global $DB, $OUTPUT;

        // Parameter validation
        $params = self::validate_parameters(self::execute_parameters(), [
            'page' => $page,
            'perpage' => $perpage,
            'filters' => $filters,
            'sesskey' => $sesskey
        ]);

        // Security checks
        self::validate_context(\context_system::instance());

        // Verify sesskey for CSRF protection
        if (!confirm_sesskey($params['sesskey'])) {
            throw new \moodle_exception('invalidsesskey', 'error');
        }

        // Check capability
        if (!has_capability('local/equipment:viewvccsubmissions', \context_system::instance())) {
            throw new \moodle_exception('nopermissions', 'error', '', 'view VCC submissions');
        }

        // Validate page and perpage parameters
        $page = max(1, $params['page']);
        $perpage = max(10, min(100, $params['perpage']));

        try {
            // Convert filters array to stdClass
            $filter_obj = new \stdClass();
            foreach ($params['filters'] as $key => $value) {
                if (!empty($value)) {
                    $filter_obj->$key = $value;
                }
            }

            // Initialize service with proper dependencies
            $clock = \core\di::get(clock::class);
            $service = new vcc_submission_service($DB, $clock);

            // Get paginated data
            $data = $service->get_paginated_table_data($filter_obj, $page, $perpage);

            // Process records for display using Mustache templates
            $processed_rows = [];
            foreach ($data['records'] as $record) {
                $row_data = self::process_table_row($record, $service, $OUTPUT);
                $processed_rows[] = $row_data;
            }

            return [
                'success' => true,
                'message' => '',
                'rows' => $processed_rows,
                'pagination' => $data['pagination'],
                'has_data' => !empty($processed_rows)
            ];

        } catch (\Exception $e) {
            // Log the error for debugging
            debugging('Error getting table data: ' . $e->getMessage(), DEBUG_DEVELOPER);

            return [
                'success' => false,
                'message' => get_string('errorloadingdata', 'local_equipment'),
                'rows' => [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perpage,
                    'total_records' => 0,
                    'total_pages' => 0,
                    'start_record' => 0,
                    'end_record' => 0,
                    'has_previous' => false,
                    'has_next' => false,
                    'previous_page' => 1,
                    'next_page' => 1
                ],
                'has_data' => false
            ];
        }
    }

    /**
     * Process a single table row for display
     *
     * @param \stdClass $record Database record
     * @param vcc_submission_service $service Service instance
     * @param \renderer_base $output Output renderer
     * @return array Processed row data
     */
    private static function process_table_row(\stdClass $record, vcc_submission_service $service, \renderer_base $output): array {
        // Get students data
        $students_data = $service->get_students_display_data($record);

        // Get exchange pickup data
        $pickup_data = $service->get_pickup_display_data($record);

        // Format addresses
        $mailing_address = self::format_address_data($record, 'mailing');
        $billing_address = self::format_address_data($record, 'billing');

        // Format dates
        $timecreated = userdate($record->timecreated, get_string('strftimedatetimeshort', 'core_langconfig'));
        $timemodified = $record->timemodified ? userdate($record->timemodified, get_string('strftimedatetimeshort', 'core_langconfig')) : '';

        // Determine status indicators
        $email_status = $record->email_confirmed ? 'confirmed' : 'pending';
        $phone_status = $record->phone_confirmed ? 'confirmed' : 'pending';

        return [
            'id' => $record->id,
            'timecreated' => $timecreated,
            'timemodified' => $timemodified,
            'firstname' => $record->firstname ?? '',
            'lastname' => $record->lastname ?? '',
            'email' => $record->email ?? '',
            'email_status' => $email_status,
            'phone' => $record->phone ?? '',
            'phone_status' => $phone_status,
            'partnership_name' => $record->partnership_name ?? '',
            'students' => $students_data,
            'mailing_address' => $mailing_address,
            'billing_address' => $billing_address,
            'exchange_partnership' => $record->exchange_partnership_name ?? '',
            'exchange_timeframe' => $pickup_data['timeframe'] ?? '',
            'exchange_pickup_info' => $pickup_data,
            'usernotes' => $record->usernotes ?? '',
            'adminnotes' => $record->adminnotes ?? '',
            'electronicsignature' => $record->electronicsignature ?? '',
            'actions' => self::get_row_actions($record->id)
        ];
    }

    /**
     * Format address data for display
     *
     * @param \stdClass $record Database record
     * @param string $type Address type (mailing|billing)
     * @return array Formatted address data
     */
    private static function format_address_data(\stdClass $record, string $type): array {
        $prefix = $type . '_';

        $address_parts = array_filter([
            $record->{$prefix . 'streetaddress'} ?? '',
            $record->{$prefix . 'apartment'} ? get_string('apt', 'local_equipment') . ' ' . $record->{$prefix . 'apartment'} : '',
            $record->{$prefix . 'city'} ?? '',
            $record->{$prefix . 'state'} ?? '',
            $record->{$prefix . 'zipcode'} ?? ''
        ]);

        return [
            'formatted' => empty($address_parts) ? '-' : implode(', ', $address_parts),
            'streetaddress' => $record->{$prefix . 'streetaddress'} ?? '',
            'apartment' => $record->{$prefix . 'apartment'} ?? '',
            'city' => $record->{$prefix . 'city'} ?? '',
            'state' => $record->{$prefix . 'state'} ?? '',
            'zipcode' => $record->{$prefix . 'zipcode'} ?? ''
        ];
    }

    /**
     * Get available actions for a table row
     *
     * @param int $record_id Record ID
     * @return array Action data
     */
    private static function get_row_actions(int $record_id): array {
        $actions = [];

        // Check permissions for different actions
        if (has_capability('local/equipment:editvccsubmissions', \context_system::instance())) {
            $actions[] = [
                'type' => 'edit',
                'url' => new \moodle_url('/local/equipment/vccsubmissions/edit.php', ['id' => $record_id]),
                'title' => get_string('edit'),
                'icon' => 'fa-edit'
            ];
        }

        if (has_capability('local/equipment:deletevccsubmissions', \context_system::instance())) {
            $actions[] = [
                'type' => 'delete',
                'url' => new \moodle_url('/local/equipment/vccsubmissions/delete.php', ['id' => $record_id]),
                'title' => get_string('delete'),
                'icon' => 'fa-trash',
                'confirm' => get_string('confirmdelete', 'local_equipment')
            ];
        }

        return $actions;
    }

    /**
     * Returns description of method return value
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'message' => new external_value(PARAM_TEXT, 'Status message'),
            'has_data' => new external_value(PARAM_BOOL, 'Whether table has data'),
            'rows' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Record ID'),
                    'timecreated' => new external_value(PARAM_TEXT, 'Creation date formatted'),
                    'timemodified' => new external_value(PARAM_TEXT, 'Modification date formatted'),
                    'firstname' => new external_value(PARAM_TEXT, 'First name'),
                    'lastname' => new external_value(PARAM_TEXT, 'Last name'),
                    'email' => new external_value(PARAM_TEXT, 'Email address'),
                    'email_status' => new external_value(PARAM_TEXT, 'Email confirmation status'),
                    'phone' => new external_value(PARAM_TEXT, 'Phone number'),
                    'phone_status' => new external_value(PARAM_TEXT, 'Phone confirmation status'),
                    'partnership_name' => new external_value(PARAM_TEXT, 'Partnership name'),
                    'students' => new external_multiple_structure(
                        new external_single_structure([
                            'name' => new external_value(PARAM_TEXT, 'Student name'),
                            'courses' => new external_multiple_structure(
                                new external_single_structure([
                                    'id' => new external_value(PARAM_INT, 'Course ID'),
                                    'name' => new external_value(PARAM_TEXT, 'Course name'),
                                    'status' => new external_value(PARAM_TEXT, 'Enrollment status'),
                                    'badge_class' => new external_value(PARAM_TEXT, 'CSS badge class'),
                                    'tooltip' => new external_value(PARAM_TEXT, 'Status tooltip')
                                ])
                            ),
                            'courses_text' => new external_value(PARAM_TEXT, 'Courses as text')
                        ])
                    ),
                    'mailing_address' => new external_single_structure([
                        'formatted' => new external_value(PARAM_TEXT, 'Formatted address'),
                        'streetaddress' => new external_value(PARAM_TEXT, 'Street address'),
                        'apartment' => new external_value(PARAM_TEXT, 'Apartment'),
                        'city' => new external_value(PARAM_TEXT, 'City'),
                        'state' => new external_value(PARAM_TEXT, 'State'),
                        'zipcode' => new external_value(PARAM_TEXT, 'Zip code')
                    ]),
                    'billing_address' => new external_single_structure([
                        'formatted' => new external_value(PARAM_TEXT, 'Formatted address'),
                        'streetaddress' => new external_value(PARAM_TEXT, 'Street address'),
                        'apartment' => new external_value(PARAM_TEXT, 'Apartment'),
                        'city' => new external_value(PARAM_TEXT, 'City'),
                        'state' => new external_value(PARAM_TEXT, 'State'),
                        'zipcode' => new external_value(PARAM_TEXT, 'Zip code')
                    ]),
                    'exchange_partnership' => new external_value(PARAM_TEXT, 'Exchange partnership'),
                    'exchange_timeframe' => new external_value(PARAM_TEXT, 'Exchange timeframe'),
                    'exchange_pickup_info' => new external_single_structure([
                        'method' => new external_value(PARAM_TEXT, 'Pickup method', VALUE_OPTIONAL),
                        'person_name' => new external_value(PARAM_TEXT, 'Pickup person name', VALUE_OPTIONAL),
                        'person_phone' => new external_value(PARAM_TEXT, 'Pickup person phone', VALUE_OPTIONAL),
                        'person_details' => new external_value(PARAM_TEXT, 'Pickup person details', VALUE_OPTIONAL),
                        'partnership_name' => new external_value(PARAM_TEXT, 'Partnership name', VALUE_OPTIONAL),
                        'partnership_address' => new external_value(PARAM_TEXT, 'Partnership address', VALUE_OPTIONAL),
                        'timeframe' => new external_value(PARAM_TEXT, 'Timeframe', VALUE_OPTIONAL),
                        'source' => new external_value(PARAM_TEXT, 'Data source', VALUE_OPTIONAL)
                    ]),
                    'usernotes' => new external_value(PARAM_TEXT, 'User notes'),
                    'adminnotes' => new external_value(PARAM_TEXT, 'Admin notes'),
                    'electronicsignature' => new external_value(PARAM_TEXT, 'Electronic signature'),
                    'actions' => new external_multiple_structure(
                        new external_single_structure([
                            'type' => new external_value(PARAM_TEXT, 'Action type'),
                            'url' => new external_value(PARAM_URL, 'Action URL'),
                            'title' => new external_value(PARAM_TEXT, 'Action title'),
                            'icon' => new external_value(PARAM_TEXT, 'Icon class'),
                            'confirm' => new external_value(PARAM_TEXT, 'Confirmation message', VALUE_OPTIONAL)
                        ])
                    )
                ])
            ),
            'pagination' => new external_single_structure([
                'current_page' => new external_value(PARAM_INT, 'Current page number'),
                'per_page' => new external_value(PARAM_INT, 'Records per page'),
                'total_records' => new external_value(PARAM_INT, 'Total number of records'),
                'total_pages' => new external_value(PARAM_INT, 'Total number of pages'),
                'start_record' => new external_value(PARAM_INT, 'First record number on page'),
                'end_record' => new external_value(PARAM_INT, 'Last record number on page'),
                'has_previous' => new external_value(PARAM_BOOL, 'Has previous page'),
                'has_next' => new external_value(PARAM_BOOL, 'Has next page'),
                'previous_page' => new external_value(PARAM_INT, 'Previous page number'),
                'next_page' => new external_value(PARAM_INT, 'Next page number')
            ])
        ]);
    }
}