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

// This file should be placed at: /local/equipment/classes/messaging/message_service.php

namespace local_equipment\messaging;

defined('MOODLE_INTERNAL') || die();

/**
 * Base messaging service interface
 */
interface message_service_interface {
    /**
     * Send a text message
     *
     * @param string $phonenumber Recipient phone number
     * @param string $message Message content
     * @return bool Success status
     */
    public function send_message(string $phonenumber, string $message): bool;
}

/**
 * Message service factory
 */
class message_service_factory {
    /**
     * Create appropriate messaging service based on configuration
     *
     * @return message_service_interface
     */
    public static function create(): message_service_interface {
        $textmethod = get_config('local_equipment', 'text_method');

        switch ($textmethod) {
            case 'api':
                return new api_message_service();
            case 'email':
                return new email_gateway_message_service();
            case 'debug':
                return new debug_message_service();
            default:
                // Default to debug service if not configured
                return new debug_message_service();
        }
    }
}

/**
 * Abstract base messaging service with common functionality
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
            'phonenumber' => $phonenumber,
            'message' => $message,
            'method' => $method
        ];
        $event = \local_equipment\event\text_message_attempt::create($eventdata);
        $event->trigger();
    }

    /**
     * Log the message delivery result
     *
     * @param string $phonenumber Recipient phone number
     * @param bool $success Whether the message was sent successfully
     */
    protected function log_result(string $phonenumber, bool $success): void {
        $event = \local_equipment\event\text_message_sent::create([
            'phonenumber' => $phonenumber,
            'success' => $success
        ]);
        $event->trigger();
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
     * @return bool Success status
     */
    public function send_message(string $phonenumber, string $message): bool {
        // Skip if phone number is empty
        if (empty($phonenumber)) {
            return false;
        }

        // Format the phone number
        $phonenumber = $this->format_phone_number($phonenumber);

        // Log the attempt
        $this->log_attempt($phonenumber, $message, $this->get_method_name());

        // Do the actual sending
        $success = $this->do_send($phonenumber, $message);

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
     * @return bool Success status
     */
    abstract protected function do_send(string $phonenumber, string $message): bool;
}

/**
 * API-based messaging service
 */
class api_message_service extends base_message_service {
    /**
     * {@inheritdoc}
     */
    protected function get_method_name(): string {
        return 'api';
    }

    /**
     * {@inheritdoc}
     */
    protected function do_send(string $phonenumber, string $message): bool {
        // Get API credentials from settings
        $apikey = get_config('local_equipment', 'api_key');
        $apisecret = get_config('local_equipment', 'api_secret');
        $apiurl = get_config('local_equipment', 'api_url');

        if (empty($apikey) || empty($apisecret) || empty($apiurl)) {
            mtrace("ERROR: Missing API credentials");
            return false;
        }

        // Prepare the API request
        $data = [
            'api_key' => $apikey,
            'api_secret' => $apisecret,
            'to' => $phonenumber,
            'text' => $message
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($apiurl, false, $context);

        if ($result === FALSE) {
            mtrace("ERROR: API request failed");
            return false;
        }

        // Parse the response
        $response = json_decode($result, true);

        return isset($response['success']) && $response['success'];
    }
}

/**
 * Email gateway messaging service
 */
class email_gateway_message_service extends base_message_service {
    /**
     * {@inheritdoc}
     */
    protected function get_method_name(): string {
        return 'email';
    }

    /**
     * {@inheritdoc}
     */
    protected function do_send(string $phonenumber, string $message): bool {
        // Get carrier gateway domain from settings
        $defaultgateway = get_config('local_equipment', 'default_gateway');

        if (empty($defaultgateway)) {
            mtrace("ERROR: Missing default carrier gateway");
            return false;
        }

        // Convert phone number to email address using carrier gateway
        $email = $phonenumber . '@' . $defaultgateway;

        // Create a message object
        $eventdata = new \core\message\message();
        $eventdata->component = 'local_equipment';
        $eventdata->name = 'equipment_reminder';
        $eventdata->userfrom = \core_user::get_support_user();
        $eventdata->subject = '';  // Most SMS gateways ignore the subject
        $eventdata->fullmessage = $message;
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml = '';
        $eventdata->smallmessage = $message;
        $eventdata->notification = 1;

        // Instead of sending to a Moodle user, we're sending to an external email
        $eventdata->userto = \core_user::get_support_user(); // Placeholder
        $eventdata->toemail = $email;

        // Send the message
        return message_send($eventdata);
    }
}

/**
 * Debug messaging service (logs but doesn't send)
 */
class debug_message_service extends base_message_service {
    /**
     * {@inheritdoc}
     */
    protected function get_method_name(): string {
        return 'debug';
    }

    /**
     * {@inheritdoc}
     */
    protected function do_send(string $phonenumber, string $message): bool {
        // Just log the message for testing purposes
        mtrace("TEXT MESSAGE TO: $phonenumber");
        mtrace("MESSAGE: $message");
        return true;
    }
}
