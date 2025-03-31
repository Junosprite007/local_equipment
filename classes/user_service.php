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
 * User service for equipment plugin.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment;

defined('MOODLE_INTERNAL') || die();

/**
 * Service class for managing user information related to equipment exchanges.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class user_service {
    /** @var \moodle_database Database instance */
    protected $db;

    /**
     * Constructor
     */
    public function __construct() {
        global $DB;
        $this->db = $DB;
    }

    /**
     * Get user details by ID, including contact information.
     *
     * @param int $userid User ID
     * @return object|false User record or false if not found
     */
    public function get_user(int $userid) {
        // Get basic user record
        $user = $this->db->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        if (!$user) {
            return false;
        }

        // Get user preferences
        $user->contactprefs = $this->get_user_contact_preferences($userid);

        return $user;
    }

    /**
     * Get user's contact preferences.
     *
     * @param int $userid User ID
     * @return object Contact preference settings
     */
    protected function get_user_contact_preferences(int $userid): object {
        $prefs = new \stdClass();

        // Get contact preferences from user_preferences table
        $prefnames = [
            'message_provider_local_equipment_equipment_reminder_loggedin',
            'message_provider_local_equipment_equipment_reminder_loggedoff'
        ];

        foreach ($prefnames as $prefname) {
            $value = get_user_preferences($prefname, null, $userid);
            if ($value !== null) {
                // Extract preference name without the prefix
                $shortname = str_replace('message_provider_local_equipment_equipment_reminder_', '', $prefname);
                $prefs->{$shortname} = $value;
            }
        }

        return $prefs;
    }

    /**
     * Get all enrolled users for a course with contact information.
     *
     * @param int $courseid The course ID
     * @return array An array of user records
     */
    public function get_enrolled_users(int $courseid): array {
        // Get course context
        $context = \context_course::instance($courseid);

        // Get enrolled users with active enrollment
        $enrolledusers = get_enrolled_users(
            $context,
            '',
            0,
            'u.id, u.firstname, u.lastname, u.email',
            null,
            0,
            0,
            true
        );

        // Get phone numbers
        if (!empty($enrolledusers)) {
            list($userids, $params) = $this->db->get_in_or_equal(array_keys($enrolledusers));
            $phonequery = "SELECT id, phone1, phone2 FROM {user} WHERE id $userids";
            $phonerecords = $this->db->get_records_sql($phonequery, $params);

            foreach ($enrolledusers as $userid => $user) {
                if (isset($phonerecords[$userid])) {
                    // Use first phone by default, fall back to second phone
                    $user->phone = !empty($phonerecords[$userid]->phone1) ?
                        $phonerecords[$userid]->phone1 :
                        $phonerecords[$userid]->phone2;
                } else {
                    $user->phone = '';
                }

                // Add contact preferences
                $user->contactprefs = $this->get_user_contact_preferences($userid);
            }
        }

        return $enrolledusers;
    }

    /**
     * Check if a user has completed the Virtual Course Consent form.
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     * @return bool True if user has completed the VCC form
     */
    public function has_completed_vcc(int $userid, int $courseid): bool {
        // Find any exchange records for this user in this course
        $sql = "SELECT ue.id
                FROM {local_equipment_user_exchange} ue
                JOIN {local_equipment_pickup} p ON p.id = ue.exchangeid
                WHERE ue.userid = :userid AND p.courseid = :courseid
                LIMIT 1";

        $params = [
            'userid' => $userid,
            'courseid' => $courseid
        ];

        return $this->db->record_exists_sql($sql, $params);
    }
}
