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
 * Email message service implementation for equipment exchange reminders.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\messaging;

defined('MOODLE_INTERNAL') || die();

/**
 * Email message service implementation.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class email_message_service implements message_service_interface {
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
     * Send an email message to a recipient.
     *
     * @param string $recipient Recipient email address
     * @param string $message Message content
     * @param array $options Additional options (subject, from_user, cc_user, etc.)
     * @return bool Success status
     */
    public function send_message(string $recipient, string $message, array $options = []): bool {
        // Skip if email is empty
        if (empty($recipient)) {
            debugging('Cannot send email: Empty recipient', DEBUG_DEVELOPER);
            return false;
        }

        // Validate email format
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            debugging("Cannot send email: Invalid email format: $recipient", DEBUG_DEVELOPER);
            return false;
        }

        // Get the recipient user or create a dummy user with the email
        $touser = null;
        if (!empty($options['to_user_id'])) {
            $touser = $this->db->get_record('user', ['id' => $options['to_user_id']]);
        }

        if (!$touser) {
            $touser = $this->create_dummy_user($recipient);
        }

        // Get the sender (default to support user)
        $fromuser = \core_user::get_support_user();
        if (!empty($options['from_user_id'])) {
            $fromuser = $this->db->get_record('user', ['id' => $options['from_user_id']]);
            if (!$fromuser) {
                $fromuser = \core_user::get_support_user();
            }
        }

        // Get the cc user (coordinator) if specified
        $ccuser = null;
        $ccaddress = '';
        $ccname = '';
        if (!empty($options['cc_user_id'])) {
            $ccuser = $this->db->get_record('user', ['id' => $options['cc_user_id']]);
            if ($ccuser) {
                $ccaddress = $ccuser->email;
                $ccname = fullname($ccuser);
            }
        } else if (!empty($options['cc_email'])) {
            $ccaddress = $options['cc_email'];
            $ccname = $options['cc_name'] ?? '';
        }

        // Get subject (default to standard reminder subject)
        $subject = $options['subject'] ?? get_string('equipmentexchangereminder', 'local_equipment');

        // Log the attempt
        $this->log_send_attempt($recipient, $subject);

        // Send the email
        $result = email_to_user(
            $touser,
            $fromuser,
            $subject,
            $message,
            $message, // HTML message (same as plain text for simplicity)
            '', // Attachment
            '', // Attachment name
            true, // Use HTML
            $ccaddress,
            $ccname
        );

        // Log the result
        $this->log_send_result($recipient, $result);

        return $result;
    }

    /**
     * Create a dummy user object for email_to_user function.
     *
     * @param string $email Email address
     * @return \stdClass User object
     */
    protected function create_dummy_user(string $email): \stdClass {
        $user = new \stdClass();
        $user->id = -99; // Dummy ID
        $user->email = $email;
        $user->firstname = '';
        $user->lastname = '';
        $user->maildisplay = true;
        $user->mailformat = 1; // HTML
        $user->maildigest = 0;
        $user->autosubscribe = 1;
        $user->emailstop = 0;

        return $user;
    }

    /**
     * Log an email send attempt.
     *
     * @param string $recipient Recipient email
     * @param string $subject Email subject
     */
    protected function log_send_attempt(string $recipient, string $subject): void {
        // Create an event record
        $eventdata = [
            'context' => \context_system::instance(),
            'other' => [
                'recipient' => $recipient,
                'subject' => $subject
            ]
        ];

        // Trigger the event
        $event = \local_equipment\event\email_send_attempted::create($eventdata);
        $event->trigger();

        // Also log to debugging
        debugging("Email send attempt to: $recipient", DEBUG_DEVELOPER);
    }

    /**
     * Log an email send result.
     *
     * @param string $recipient Recipient email
     * @param bool $success Whether the send was successful
     */
    protected function log_send_result(string $recipient, bool $success): void {
        // Create an event record
        $eventdata = [
            'context' => \context_system::instance(),
            'other' => [
                'recipient' => $recipient,
                'success' => $success ? 1 : 0
            ]
        ];

        // Trigger the event
        $event = \local_equipment\event\email_send_completed::create($eventdata);
        $event->trigger();

        // Also log to debugging
        debugging("Email send " . ($success ? "successful" : "failed") . " to: $recipient", DEBUG_DEVELOPER);
    }
}
