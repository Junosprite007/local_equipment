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
 * SMS message service implementation for equipment exchange reminders.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\messaging;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/equipment/lib.php');

/**
 * SMS message service implementation.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class sms_message_service implements message_service_interface {
    /**
     * Send an SMS message to a recipient.
     *
     * @param string $recipient Recipient phone number
     * @param string $message Message content
     * @param array $options Additional options (provider_id, message_type, etc.)
     * @return bool Success status
     */
    public function send_message(string $recipient, string $message, array $options = []): bool {
        // Format phone number (remove non-numeric chars except +)
        $phonenumber = preg_replace('/[^0-9+]/', '', $recipient);

        // Skip if phone number is empty
        if (empty($phonenumber)) {
            debugging('Cannot send SMS: Empty phone number', DEBUG_DEVELOPER);
            return false;
        }

        // Get provider ID from options or settings
        $providerid = $options['provider_id'] ?? get_config('local_equipment', 'infogateway');
        if (empty($providerid)) {
            debugging('Cannot send SMS: No provider ID configured', DEBUG_DEVELOPER);
            return false;
        }

        // Get message type from options or use default
        $messagetype = $options['message_type'] ?? 'Transactional';

        // Log the attempt
        $this->log_send_attempt($phonenumber, $message, $providerid);

        // Use your existing send_sms function
        $result = local_equipment_send_sms($providerid, $phonenumber, $message, $messagetype);

        // Log the result
        $this->log_send_result($phonenumber, $result);

        return $result;
    }

    /**
     * Log an SMS send attempt.
     *
     * @param string $recipient Recipient phone number
     * @param string $message Message content
     * @param string $providerid SMS provider ID
     */
    protected function log_send_attempt(string $recipient, string $message, string $providerid): void {
        // Create an event record
        $eventdata = [
            'context' => \context_system::instance(),
            'other' => [
                'recipient' => $recipient,
                'message_length' => strlen($message),
                'provider_id' => $providerid
            ]
        ];

        // Trigger the event
        $event = \local_equipment\event\sms_send_attempted::create($eventdata);
        $event->trigger();

        // Also log to debugging
        debugging("SMS send attempt to: $recipient", DEBUG_DEVELOPER);
    }

    /**
     * Log an SMS send result.
     *
     * @param string $recipient Recipient phone number
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
        $event = \local_equipment\event\sms_send_completed::create($eventdata);
        $event->trigger();

        // Also log to debugging
        debugging("SMS send " . ($success ? "successful" : "failed") . " to: $recipient", DEBUG_DEVELOPER);
    }
}
