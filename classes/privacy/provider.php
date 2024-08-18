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
 * Privacy provider implementation for local_equipment.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// namespace local_equipment\privacy;

// defined('MOODLE_INTERNAL') || die();

// class provider implements \core_privacy\local\metadata\null_provider {
//     public static function get_reason(): string {
//         return 'privacy:metadata';
//     }
// }




namespace local_equipment\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\helper;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider implementation for local_equipment.
 *
 * @copyright   2024 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_equipment_agreementsubmission',
            [
                'userid' => 'privacy:metadata:local_equipment_agreementsubmission:userid',
                'agreementid' => 'privacy:metadata:local_equipment_agreementsubmission:agreementid',
                'status' => 'privacy:metadata:local_equipment_agreementsubmission:status',
                'submissiondate' => 'privacy:metadata:local_equipment_agreementsubmission:submissiondate',
            ],
            'privacy:metadata:local_equipment_agreementsubmission'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT c.id
                FROM {context} c
                JOIN {local_equipment_agreementsubmission} s ON s.userid = c.instanceid
                WHERE c.contextlevel = :contextlevel AND s.userid = :userid";

        $params = [
            'contextlevel' => CONTEXT_USER,
            'userid'       => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();

        $context = \context_user::instance($user->id);

        // Export agreement submissions.
        $sql = "SELECT s.id, s.agreementid, s.status, s.submissiondate, a.title
                FROM {local_equipment_agreementsubmission} s
                JOIN {local_equipment_agreement} a ON s.agreementid = a.id
                WHERE s.userid = :userid";

        $submissions = $DB->get_records_sql($sql, ['userid' => $user->id]);

        foreach ($submissions as $submission) {
            $data = [
                'agreementtitle' => $submission->title,
                'status' => $submission->status,
                'submissiondate' => transform::datetime($submission->submissiondate),
            ];
            writer::with_context($context)->export_data(['Agreement Submissions', $submission->id], (object)$data);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }

        $DB->delete_records('local_equipment_agreementsubmission', ['userid' => $context->instanceid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $DB->delete_records('local_equipment_agreementsubmission', ['userid' => $userid]);
    }
}
