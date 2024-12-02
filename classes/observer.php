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
 * Observers for the Equipment plugin.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment;

defined('MOODLE_INTERNAL') || die();

class observer {
    /**
     * Handle user enrollment creation event
     *
     * @param \core\event\user_enrolment_created $event
     * @return void
     */
    public static function handle_enrolment_created(\core\event\user_enrolment_created $event): void {
        global $SESSION;

        // Check if this is our bulk enrollment process
        if (!empty($SESSION->local_equipment_bulk_enrollment)) {
            // Set flag for hooks/callbacks to check
            $SESSION->local_equipment_skip_welcome[$event->objectid] = true;
        }
    }
}

    // public static function handle_enrolment_created(\core\event\user_enrolment_created $event): void {
    //     global $SESSION, $DB;

    //     // Check if this is our bulk enrollment process
    //     if (!empty($SESSION->local_equipment_bulk_enrollment)) {
    //         // Get the enrollment instance
    //         $ue = $event->get_record_snapshot('user_enrolments', $event->objectid);
    //         $enrol = $DB->get_record('enrol', ['id' => $ue->enrolid]);

    //         // Only handle manual enrollments
    //         if ($enrol && $enrol->enrol === 'manual') {
    //             // Prevent the default welcome message by setting a flag
    //             if (!isset($SESSION->local_equipment_skip_welcome)) {
    //                 $SESSION->local_equipment_skip_welcome = [];
    //             }
    //             $SESSION->local_equipment_skip_welcome[$event->objectid] = true;
    //         }
    //     }
    // }
