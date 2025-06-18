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
 * Base messaging service with common functionality.
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
 * Abstract base messaging service with common functionality.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
abstract class base_message_service implements message_service_interface {

    /**
     * Log the message attempt
     *
     * @param string $phonenumber Recipient phone number
     * @param string $message Message content
     * @param string $method Delivery method
     */
    protected function log_attempt(string $phonenumber, string $message, string $method): void {
        $eventdata = [
            'context' => \context_system::instance(),
            'other' => [
                'phonenumber' => $phonenumber,
                'message' => $message,
                'method' => $method,
                'timestamp' => time()
            ]
        ];

        try {
            $event = \local_equipment\event\text_message_attempt::create($eventdata);
            $event->trigger();
        } catch (Exception $e) {
            local_equipment_debug_log("Failed to trigger text message attempt event: " . $e->getMessage());
        }
    }

    /**
     * Log the message delivery result
     *
     * @param string $phonenumber Recipient phone number
     * @param bool $success Whether the message was sent successfully
     */
    protected function log_result(string $phonenumber, bool $success): void {
        $eventdata = [
            'context' => \context_system::instance(),
            'other' => [
                'phonenumber' => $phonenumber,
                'success' => $success ? 1 : 0,
                'timestamp' => time()
            ]
        ];

        try {
            $event = \local_equipment\event\text_message_sent::create($eventdata);
            $event->trigger();
        } catch (Exception $e) {
            local_equipment_debug_log("Failed to trigger text message sent event: " . $e->getMessage());
        }
    }

    /**
     * Format phone number for sending
     *
     * @param string $phonenumber Raw phone number
     * @return string Formatted phone number
     */
    protected function format_phone_number(string $phonenumber): string {
        $phoneobj = local_equipment_parse_phone_number($phonenumber);
        return $phoneobj->phone;
    }

    /**
     * Send a message with common pre-processing
     *
     * @param string $phonenumber Recipient phone number
     * @param string $message Message content
     * @param array $options Additional options for sending
     * @return bool Success status
     */
    public function send_message(string $phonenumber, string $message, array $options = []): bool {
        // Skip if phone number is empty
        if (empty($phonenumber)) {
            local_equipment_debug_log('Cannot send message: Empty phone number');
            return false;
        }

        // Format the phone number
        $phonenumber = $this->format_phone_number($phonenumber);

        // Log the attempt
        $this->log_attempt($phonenumber, $message, $this->get_method_name());

        // Do the actual sending
        $success = $this->do_send($phonenumber, $message, $options);

        // Log the result
        $this->log_result($phonenumber, $success);

        return $success;
    }

    /**
     * Get the name of this messaging method
     *
     * @return string Method name
     */
    abstract protected function get_method_name(): string;

    /**
     * Perform the actual message sending
     *
     * @param string $phonenumber Recipient phone number
     * @param string $message Message content
     * @param array $options Additional options for sending
     * @return bool Success status
     */
    abstract protected function do_send(string $phonenumber, string $message, array $options = []): bool;
}
