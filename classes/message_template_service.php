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
 * Message template service for equipment exchange reminders.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment;

defined('MOODLE_INTERNAL') || die();

/**
 * Service class for managing message templates for equipment exchange reminders.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class message_template_service {
    /**
     * Get the appropriate message template for a reminder.
     *
     * @param string $remindertype Type of reminder ('days' or 'hours')
     * @return string Template string
     */
    public function get_template(string $remindertype): string {
        // First try to get the specific template from config
        $template = get_config('local_equipment', 'reminder_template_' . $remindertype);

        // If no specific template, try the default template from config
        if (empty($template)) {
            $template = get_config('local_equipment', 'reminder_template_default');
        }

        // If still empty, try to get from language strings with a fallback
        if (empty($template)) {
            $specifickey = 'reminder_template_' . $remindertype;
            $defaultkey = 'reminder_template_default';

            // Check if specific key exists in language pack
            if (get_string_manager()->string_exists($specifickey, 'local_equipment')) {
                $template = get_string($specifickey, 'local_equipment');
            } else {
                // Fallback to default string with a hardcoded ultimate fallback
                $ultimatefallback = 'REMINDER: Equipment exchange on {DATE} from {START} to {END}. Location: {LOCATION}.';
                $template = get_string($defaultkey, 'local_equipment', $ultimatefallback);
            }
        }

        return $template;
    }

    /**
     * Prepare a personalized reminder message by replacing placeholders with actual data.
     *
     * @param object $user User to send reminder to
     * @param object $exchange Exchange details
     * @param string $formatteddate Formatted date
     * @param string $formattedstarttime Formatted time
     * @param string $formattedendtime Formatted time
     * @param string $equipmentlist List of equipment
     * @param string $remindertype Type of reminder ('days' or 'hours')
     * @param float $remindervalue Value for the reminder (number of days or hours)
     * @return string Formatted message
     */
    public function prepare_message(
        \stdClass $user,
        \stdClass $exchange,
        string $formatteddate,
        string $formattedstarttime,
        string $formattedendtime,
        string $equipmentlist,
        string $remindertype,
        float $remindervalue,
        string $location = ''
    ): string {
        // Get template
        $template = $this->get_template($remindertype);

        // Use provided location or build from exchange fields
        if (empty($location)) {
            $location = !empty($exchange->pickup_streetaddress) ?
                "{$exchange->pickup_streetaddress}, {$exchange->pickup_city}, {$exchange->pickup_state} {$exchange->pickup_zipcode}" :
                'your school';
        }

        // Get coordinator information if available
        $coordinator = '';
        if (!empty($exchange->flccoordinatorid)) {
            global $DB;
            $coordinatoruser = $DB->get_record('user', ['id' => $exchange->flccoordinatorid]);
            if ($coordinatoruser) {
                $coordinator = $coordinatoruser->firstname . ' ' . $coordinatoruser->lastname;
            }
        }

        // Get course information - set to empty for now as it's course-agnostic
        $coursename = '';

        // Replace placeholders
        $replacements = [
            '{FIRSTNAME}' => $user->firstname,
            '{LASTNAME}' => $user->lastname,
            '{FULLNAME}' => $user->firstname . ' ' . $user->lastname,
            '{DAYS}' => ($remindertype == 'days') ? intval($remindervalue) : '',
            '{HOURS}' => ($remindertype == 'hours') ? intval($remindervalue) : '',
            '{DATE}' => $formatteddate,
            '{START}' => $formattedstarttime,
            '{END}' => $formattedendtime,
            '{LOCATION}' => $location,
            '{EQUIPMENT}' => $equipmentlist,
            '{COURSE}' => $coursename,
            '{COORDINATOR}' => $coordinator
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Format a date and time in a user-friendly way.
     *
     * @param int $timestamp The timestamp to format
     * @return array Array with formatted date and time
     */
    public function format_datetime(int $timestamp): array {
        return [
            'date' => date('l, F j, Y', $timestamp),
            'time' => date('g:i A', $timestamp)
        ];
    }
}
