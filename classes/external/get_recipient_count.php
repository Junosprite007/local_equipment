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
 * External libraries for the Equipment plugin.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use context_system;

defined('MOODLE_INTERNAL') || die();

/**
 * External function to get equipment recipient count for mass messaging
 */
class get_recipient_count extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * Get recipient count for mass messaging
     * @return array Result with actual count
     */
    public static function execute(): array {
        global $DB;

        // Step 1: Context validation
        $context = context_system::instance();
        self::validate_context($context);

        // Step 2: Capability check for viewing recipients
        require_capability('local/equipment:viewrecipients', $context);

        try {
            // Count parents with verified phone numbers who are in the equipment system
            $sql = "SELECT COUNT(DISTINCT eu.userid) as recipient_count
                    FROM {local_equipment_user} eu
                    JOIN {user} u ON u.id = eu.userid
                    JOIN {local_equipment_phonecommunication_otp} otp ON otp.userid = eu.userid
                    WHERE u.deleted = 0
                    AND u.suspended = 0
                    AND otp.phoneisverified = 1";

            $result = $DB->get_record_sql($sql);
            $count = $result ? (int)$result->recipient_count : 0;

            return [
                'success' => true,
                'count' => $count,
                'error' => '',
            ];
        } catch (\Exception $e) {
            // Log the actual error for debugging
            error_log('Equipment recipient count error: ' . $e->getMessage());

            return [
                'success' => false,
                'count' => 0,
                'error' => 'Database error occurred',
            ];
        }
    }

    /**
     * Returns description of method result value
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the operation was successful'),
            'count' => new external_value(PARAM_INT, 'Number of recipients'),
            'error' => new external_value(PARAM_TEXT, 'Error message if any'),
        ]);
    }
}
