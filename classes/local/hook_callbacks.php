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
 * Hook callback for after a user is enrolled in a course through the bulk family upload feature of the equipment plugin
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace local_equipment\local;

use local_equipment\notification_manager;
// use core\hook\described_hook;

class hook_callbacks {
    /**
     * Callback for core enrollment hook to prevent default notification
     */
    public static function prevent_default_notification(\core_enrol\hook\after_user_enrolled $hook): void {
        if (get_config('local_equipment', 'handling_enrollment')) {
            $hook->set_send_welcome_message(false);
        }
    }

    /**
     * Handle our custom enrollment notification
     */
    public static function handle_equipment_enrollment(\local_equipment\hook\equipment_user_enrolled $hook): void {
        global $DB;

        try {
            $user = $DB->get_record('user', ['id' => $hook->userid], '*', MUST_EXIST);
            $course = $DB->get_record('course', ['id' => $hook->courseid], '*', MUST_EXIST);

            notification_manager::send_course_welcome($user, $course, $hook->roletype);
        } catch (\Throwable $e) {
            debugging('Failed to handle equipment enrollment notification: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
}
