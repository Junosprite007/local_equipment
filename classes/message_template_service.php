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
        // Get template for this specific type
        $template = get_config('local_equipment', 'reminder_template_' . $remindertype);

        // If no specific template exists, use default
        if (empty($template)) {
            $template = get_config('local_equipment', 'reminder_template_default');
        }

        // If still empty, use default from language pack
        if (empty($template)) {
            $template = get_string(
                'reminder_template_' . $remindertype,
                'local_equipment',
                get_string('reminder_template_default', 'local_equipment')
            );
        }

        return $template;
    }

    /**
     * Prepare a personalized reminder message by replacing placeholders with actual data.
     *
     * @param object $user User to send reminder to
     * @param object $exchange Exchange details
     * @param string $formatteddate Formatted date
     * @param string $formattedtime Formatted time
     * @param string $equipmentlist List of equipment
     * @param string $remindertype Type of reminder ('days' or 'hours')
     * @param float $remindervalue Value for the reminder (number of days or hours)
     * @return string Formatted message
     */
    public function prepare_message(
        \stdClass $user,
        \stdClass $exchange,
        string $formatteddate,
        string $formattedtime,
        string $equipmentlist,
        string $remindertype,
        float $remindervalue
    ): string {
        // Get template
        $template = $this->get_template($remindertype);

        // Get course information
        $coursename = '';
        if (!empty($exchange->courseid)) {
            global $DB;
            $course = $DB->get_record('course', ['id' => $exchange->courseid]);
            if ($course) {
                $coursename = $course->fullname;
            }
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

        // Format location
        $location = !empty($exchange->location) ? $exchange->location : 'your school';

        // Replace placeholders
        $replacements = [
            '{FIRSTNAME}' => $user->firstname,
            '{LASTNAME}' => $user->lastname,
            '{FULLNAME}' => $user->firstname . ' ' . $user->lastname,
            '{DAYS}' => ($remindertype == 'days') ? intval($remindervalue) : '',
            '{HOURS}' => ($remindertype == 'hours') ? intval($remindervalue) : '',
            '{DATE}' => $formatteddate,
            '{TIME}' => $formattedtime,
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
