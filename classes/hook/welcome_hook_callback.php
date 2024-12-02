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
 * The welcome hook callback for the Equipment checkout module.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\hook;

use core_enrol\hook\after_user_enrolled;

/**
 * Hook callback class to prevent welcome messages during bulk enrollment.
 */
class welcome_hook_callback {
    /**
     * Prevent sending of welcome message if this is a bulk enrollment.
     *
     * @param after_user_enrolled $hook
     */
    public static function prevent_welcome_message(after_user_enrolled $hook): void {
        global $SESSION;

        echo '<br />';
        echo '<br />';
        echo '<pre>';
        var_dump('$event from "Equipment" welcome_hook_callback.php');
        var_dump($hook);
        // $event->data;
        // var_dump($event->data);
        echo '</pre>';

        // Check if this is our bulk enrollment process
        if (!empty($SESSION->local_equipment_bulk_enrollment)) {
            // Prevent the welcome message from being sent
            $hook->prevent_message();
        }
    }
}
