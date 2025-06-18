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
     * @param string $method Messaging method ('email', 'sms', 'api', 'email_gateway', 'debug', or 'auto')
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

            case 'api':
                return new api_message_service();

            case 'email_gateway':
            case 'carrier_email':
                return new email_gateway_message_service();

            case 'debug':
            case 'test':
                return new debug_message_service();

            default:
                local_equipment_debug_log("Unknown messaging method: $method, falling back to SMS");
                return new sms_message_service();
        }
    }

    /**
     * Send a message using the appropriate service based on the method.
     *
     * @param string $method Messaging method ('email', 'sms', 'api', 'email_gateway', 'debug', or 'auto')
     * @param string $recipient Recipient contact info (email or phone)
     * @param string $message Message content
     * @param array $options Additional options for sending
     * @return bool Success status
     */
    public static function send_message(string $method, string $recipient, string $message, array $options = []): bool {
        $service = self::create($method);
        return $service->send_message($recipient, $message, $options);
    }

    /**
     * Get all available messaging methods.
     *
     * @return array Array of method => description
     */
    public static function get_available_methods(): array {
        return [
            'sms' => 'SMS via AWS End User Messaging',
            'email' => 'Email messages',
            'api' => 'Custom API integration',
            'email_gateway' => 'SMS via carrier email gateways',
            'debug' => 'Debug mode (logs only)'
        ];
    }

    /**
     * Validate configuration for a specific messaging method.
     *
     * @param string $method Messaging method to validate
     * @return array Validation results
     */
    public static function validate_method_configuration(string $method): array {
        try {
            $service = self::create($method);

            // Check if the service has a validate_configuration method
            if (method_exists($service, 'validate_configuration')) {
                return $service->validate_configuration();
            } else {
                return [
                    'method' => $method,
                    'configured' => true,
                    'errors' => [],
                    'warnings' => ['Validation not available for this method']
                ];
            }
        } catch (Exception $e) {
            return [
                'method' => $method,
                'configured' => false,
                'errors' => ['Failed to create service: ' . $e->getMessage()],
                'warnings' => []
            ];
        }
    }

    /**
     * Get the best available messaging method based on configuration.
     *
     * @return string Best available method
     */
    public static function get_best_available_method(): string {
        $methods = ['sms', 'email', 'api', 'email_gateway'];

        foreach ($methods as $method) {
            $validation = self::validate_method_configuration($method);
            if (empty($validation['errors'])) {
                return $method;
            }
        }

        // If no methods are properly configured, return debug
        return 'debug';
    }
}