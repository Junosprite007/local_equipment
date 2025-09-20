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
use core\clock;

/**
 * External function for saving VCC table column preferences
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_column_preferences extends external_api {

    /*
     * DEBUG CONFIGURATION FLAGS FOR DEVELOPMENT AND TESTING
     *
     * These debug flags provide granular control over logging and monitoring functionality
     * within the column preference save operations. They are designed as constants to ensure
     * consistent behavior throughout the class and to enable easy toggling during development
     * without affecting production performance. Each flag controls a specific aspect of the
     * debugging system, allowing developers to focus on particular areas of functionality
     * without being overwhelmed by excessive logging output.
     *
     * Setting these flags to 'true' enables detailed logging that can help diagnose issues
     * with preference saving, performance bottlenecks, and validation problems. The flags
     * are set to 'false' by default to ensure zero performance impact in production environments.
     */

    /** @var bool Debug flag for column preference save operations */
    private const DEBUG_COLUMN_PREFERENCES = false;

    /** @var bool Debug flag for preference validation operations */
    private const DEBUG_PREFERENCE_VALIDATION = false;

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'preferences' => new external_single_structure([
                'page_size' => new external_value(PARAM_INT, 'Page size (10-100)', VALUE_OPTIONAL),
                'column_widths' => new external_value(PARAM_TEXT, 'Column widths as JSON string', VALUE_OPTIONAL),
                'hidden_columns' => new external_value(PARAM_TEXT, 'Hidden columns as JSON string', VALUE_OPTIONAL),
                'sort_column' => new external_value(PARAM_ALPHA, 'Sort column name', VALUE_OPTIONAL),
                'sort_direction' => new external_value(PARAM_ALPHA, 'Sort direction (ASC/DESC)', VALUE_OPTIONAL),
                'filter_collapsed' => new external_value(PARAM_BOOL, 'Filter section collapsed state', VALUE_OPTIONAL)
            ], 'User preferences to save'),
            'sesskey' => new external_value(PARAM_ALPHANUM, 'Session key for security validation')
        ]);
    }

    /**
     * Save column preferences for the current user
     *
     * @param array $preferences User preferences
     * @param string $sesskey Session key for validation
     * @return array Success status and message
     */
    public static function execute(array $preferences, string $sesskey): array {
        global $DB, $USER, $SESSION;

        // Parameter validation
        $params = self::validate_parameters(self::execute_parameters(), [
            'preferences' => $preferences,
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

        try {
            /*
             * DEBUG LOGGING FOR PREFERENCE SAVE OPERATIONS
             *
             * This section implements comprehensive debug logging for column preference operations.
             * The logging is conditionally enabled by the DEBUG_COLUMN_PREFERENCES flag to ensure
             * zero performance impact in production environments. Each debug operation is wrapped
             * in its own try-catch block to prevent any logging failures from causing fatal errors
             * in the main application flow. This defensive programming approach ensures that debug
             * functionality never compromises the core user experience, even if the logging system
             * encounters issues like disk space constraints or permission problems.
             */
            if (self::DEBUG_COLUMN_PREFERENCES) {
                try {
                    local_equipment_debug_log("VCC Column Preferences Save - User ID: {$USER->id}, Preferences: " . json_encode($params['preferences']));
                } catch (\Exception $e) {
                    // Silent failure for debug logging to prevent fatal errors
                    // This ensures that any issues with the logging system (disk space, permissions, etc.)
                    // do not impact the core functionality of saving user preferences
                }
            }

            // Initialize service with proper dependencies
            $clock = \core\di::get(clock::class);
            $service = new vcc_submission_service($DB, $clock);

            /*
             * PERFORMANCE TIMING FOR PREFERENCE OPERATIONS
             *
             * This performance monitoring system tracks the execution time of preference save operations
             * to help identify performance bottlenecks during development and testing. The timing is
             * only active when DEBUG_PREFERENCE_VALIDATION is enabled, ensuring no overhead in production.
             * High-precision microtime measurements capture execution times down to milliseconds,
             * providing detailed insight into database operation performance and helping optimize
             * the user experience for large datasets or high-concurrency scenarios.
             */
            if (self::DEBUG_PREFERENCE_VALIDATION) {
                $starttime = microtime(true);
            }

            // Save preferences using the service
            $success = $service->save_user_table_preferences($params['preferences']);

            /*
             * PERFORMANCE TIMING COMPLETION AND LOGGING
             *
             * After the preference save operation completes, this section calculates the total
             * execution time and logs it for performance analysis. The timing calculation uses
             * high-precision floating-point arithmetic to provide millisecond-accurate measurements.
             * Like all debug operations, this is wrapped in defensive error handling to ensure
             * that any issues with performance logging do not affect the user's ability to save
             * their column preferences successfully.
             */
            if (self::DEBUG_PREFERENCE_VALIDATION && isset($starttime)) {
                try {
                    $executiontime = round((microtime(true) - $starttime) * 1000, 2);
                    local_equipment_debug_log("VCC Preference Save Performance - Execution time: {$executiontime}ms");
                } catch (\Exception $e) {
                    // Silent failure for debug logging - performance monitoring should never
                    // interfere with the core functionality of the application
                }
            }

            if ($success) {
                return [
                    'success' => true,
                    'message' => get_string('preferencessaved', 'local_equipment'),
                    'preferences' => $service->get_user_table_preferences()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => get_string('preferencessavefailed', 'local_equipment'),
                    'preferences' => []
                ];
            }

        } catch (\Exception $e) {
            // Log the error for debugging
            debugging('Error saving column preferences: ' . $e->getMessage(), DEBUG_DEVELOPER);

            return [
                'success' => false,
                'message' => get_string('preferencessavefailed', 'local_equipment'),
                'preferences' => []
            ];
        }
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
            'preferences' => new external_single_structure([
                'page_size' => new external_value(PARAM_INT, 'Page size', VALUE_OPTIONAL),
                'column_widths' => new external_value(PARAM_TEXT, 'Column widths JSON', VALUE_OPTIONAL),
                'hidden_columns' => new external_value(PARAM_TEXT, 'Hidden columns JSON', VALUE_OPTIONAL),
                'sort_column' => new external_value(PARAM_ALPHA, 'Sort column', VALUE_OPTIONAL),
                'sort_direction' => new external_value(PARAM_ALPHA, 'Sort direction', VALUE_OPTIONAL),
                'filter_collapsed' => new external_value(PARAM_BOOL, 'Filter collapsed', VALUE_OPTIONAL)
            ], 'Saved preferences', VALUE_OPTIONAL)
        ]);
    }
}