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
 * Messaging service factory for equipment exchange reminders.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\messaging;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/message_service_interface.php');
require_once(__DIR__ . '/sms_message_service.php');
require_once(__DIR__ . '/email_message_service.php');

/**
 * Factory class for creating message service instances.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class message_service_factory {
    /**
     * Create the appropriate messaging service based on the method.
     *
     * @param string $method Messaging method ('email', 'sms', or 'auto')
     * @return message_service_interface Messaging service instance
     */
    public static function create(string $method = 'auto'): message_service_interface {
        // If method is 'auto', get the default method from settings
        if ($method === 'auto') {
            $method = get_config('local_equipment', 'default_messaging_method');
            if (empty($method)) {
                $method = 'sms'; // Default to SMS if not configured
            }
        }

        // Create the appropriate service instance
        switch (strtolower($method)) {
            case 'email':
                return new email_message_service();

            case 'sms':
            case 'text':
                return new sms_message_service();

            default:
                debugging("Unknown messaging method: $method, falling back to SMS", DEBUG_DEVELOPER);
                return new sms_message_service();
        }
    }

    /**
     * Send a message using the appropriate service based on the method.
     *
     * @param string $method Messaging method ('email', 'sms', or 'auto')
     * @param string $recipient Recipient contact info (email or phone)
     * @param string $message Message content
     * @param array $options Additional options for sending
     * @return bool Success status
     */
    public static function send_message(string $method, string $recipient, string $message, array $options = []): bool {
        $service = self::create($method);
        return $service->send_message($recipient, $message, $options);
    }
}
