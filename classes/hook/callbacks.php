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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Hook callbacks for equipment plugin.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\hook;

require_once(__DIR__ . '/../../lib.php');

defined('MOODLE_INTERNAL') || die();

// UNCOMMENT ALL OF THE BELOW AFTER TEXTING IS IMPLEMENTED.
class callbacks {
    /**
     * Check if phone verification is needed after user login.
     * Only redirects parent users who need verification.
     *
     * @param \core_user\hook\after_login_completed $hook The login hook
     */
    public static function check_phone_verification(\core_user\hook\after_login_completed $hook): void {
        global $DB, $SESSION, $USER;


        // Skip during initial install
        if (during_initial_install()) {
            return;
        }

        // Check if user is a parent by looking for parent role assignments
        $context = \context_system::instance();
        $parentrole = $DB->get_record('role', ['shortname' => 'parent']);

        if (!$parentrole) {
            return;
        }

        $isparent = sizeof(local_equipment_get_students_of_parent($USER->id)) > 0;

        if (!$isparent) {
            return;
        }

        // Check phone verification status
        $phoneisverified = $DB->get_records(
            'local_equipment_phonecommunication_otp',
            ['userid' => $USER->id, 'phoneisverified' => 1]
        );

        if (sizeof($phoneisverified) === 0) {
            // echo '<br />';
            // echo '<br />';
            // echo '<br />';
            // echo '<pre>';
            // var_dump($phoneisverified);
            // echo '</pre>';
            // // User needs verification - set session flag and redirect
            // $SESSION->local_equipment_phoneisverified = false;
            redirect(new \moodle_url('/local/equipment/phonecommunication/verifyphone.php'), get_string('phoneverificationrequire', 'local_equipment'), null, \core\output\notification::NOTIFY_ERROR);
        } else {
            // echo '<br />';
            // echo '<br />';
            // echo '<br />';
            // echo '<pre>';
            // var_dump($phoneisverified);
            // echo '</pre>';
            // $SESSION->local_equipment_phoneisverified = true;
        }
    }
}
