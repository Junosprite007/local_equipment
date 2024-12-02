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
 * The notification manager for the Equipment checkout module.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment;

defined('MOODLE_INTERNAL') || die();

/**
 * Manages sending of custom enrollment notifications.
 * This class handles sending welcome messages to newly enrolled users,
 * with different message templates for students and parents.
 *
 * The manager is designed to be resilient - it will attempt to send notifications
 * when possible but won't block the enrollment process if notifications fail.
 * All outcomes are reported back through the result object for admin visibility.
 */
class notification_manager {
    /**
     * Send course welcome message to newly enrolled user.
     * Returns detailed information about the notification attempt without using debugging().
     *
     * @param \stdClass $user The user object
     * @param \stdClass $course The course object
     * @param string $roletype The role type ('student' or 'parent')
     * @return \stdClass Object containing success/warning/error message arrays
     */
    public static function send_course_welcome(\stdClass $user, \stdClass $course, string $roletype): \stdClass {
        $result = new \stdClass();
        $result->successes = [];
        $result->warnings = [];
        $result->errors = [];

        // Create our message parameters for string substitution
        $msg = new \stdClass();
        $msg->firstname = $user->firstname;
        $msg->lastname = $user->lastname;
        $msg->coursename = $course->fullname;

        try {
            // Validate role type - we only handle student and parent notifications
            if (!in_array($roletype, ['student', 'parent'])) {
                $result->warnings[] = "Skipping notification - Invalid role type: {$roletype}";
                return $result;
            }

            // Check if notifications are enabled for this role type
            $setting = "notify_{$roletype}s";
            if (empty(get_config('local_equipment', $setting))) {
                // This is an intentional configuration choice, not an error
                $result->warnings[] = get_string('notificationsdisabledfor', 'local_equipment', $roletype);
                return $result;
            }

            // Get course context and URLs
            $context = \context_course::instance($course->id);
            $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);

            // Setup message data
            $site = get_site();
            $supportuser = self::get_message_sender($course);

            $messagedata = [
                'firstname' => $user->firstname,
                'coursename' => format_string($course->fullname, true, ['context' => $context]),
                'roletype' => $roletype,
                'courseurl' => $courseurl->out(false),
                'sitename' => format_string($site->fullname),
            ];

            // Create message object
            $message = new \core\message\message();
            $message->component = 'local_equipment';
            $message->name = 'coursewelcome';
            $message->userfrom = $supportuser;
            $message->userto = $user;
            $message->subject = get_string("{$roletype}welcomesubject", 'local_equipment', $course->fullname);
            $message->fullmessage = get_string("{$roletype}welcomemessage", 'local_equipment', $messagedata);
            $message->fullmessageformat = FORMAT_MARKDOWN;
            $message->fullmessagehtml = markdown_to_html($message->fullmessage);
            $message->smallmessage = $message->subject;
            $message->notification = true;
            $message->contexturl = $courseurl->out();
            $message->contexturlname = $course->fullname;
            $message->replyto = $supportuser->email;
            $message->courseid = $course->id;

            // Attempt to send the message
            if (\core\message\manager::send_message($message)) {
                $result->successes[] = get_string('welcomemessagesenttouserforcourse', 'local_equipment', $msg);
            } else {
                $result->warnings[] = get_string('welcomemessagenotsenttouserforcourse', 'local_equipment', $msg);
            }
        } catch (\Throwable $e) {
            // Only system-level issues should become errors
            // Everything else should be a warning to allow the process to continue
            if ($e instanceof \coding_exception || $e instanceof \dml_exception) {
                $result->errors[] = $e->getMessage();
            } else {
                $result->warnings[] = get_string('welcomemessagenotsenttouserforcourse', 'local_equipment', $msg) .
                    ' (' . $e->getMessage() . ')';
            }
        }

        return $result;
    }

    /**
     * Get the message sender based on plugin settings.
     * Returns the most appropriate sender based on configuration.
     *
     * @param \stdClass $course The course object
     * @return \stdClass The user object to send from
     */
    private static function get_message_sender(\stdClass $course): \stdClass {
        try {
            $senderoption = get_config('local_equipment', 'messagesender');

            switch ($senderoption) {
                case ENROL_SEND_EMAIL_FROM_COURSE_CONTACT:
                    // Try to get a course contact first
                    $context = \context_course::instance($course->id);
                    $teachers = get_enrolled_users($context, 'moodle/course:update', 0, 'u.*', 'u.id ASC', 0, 1);
                    if ($teachers) {
                        return reset($teachers);
                    }
                    // Fall through if no teachers found

                case ENROL_SEND_EMAIL_FROM_KEY_HOLDER:
                    // Try to get the primary admin
                    $adminuser = get_admin();
                    if ($adminuser) {
                        return $adminuser;
                    }
                    // Fall through if no admin found

                case ENROL_SEND_EMAIL_FROM_NOREPLY:
                default:
                    return \core_user::get_noreply_user();
            }
        } catch (\Throwable $e) {
            // If anything goes wrong getting the sender, use the noreply user
            return \core_user::get_noreply_user();
        }
    }
}
