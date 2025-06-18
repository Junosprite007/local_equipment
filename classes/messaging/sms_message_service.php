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
 * Enhanced SMS message service implementation with AWS End User Messaging pool support.
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
 * Enhanced SMS message service implementation with pool support.
 *
 * This class provides SMS messaging capabilities using AWS End User Messaging
 * with automatic pool selection based on message types.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class sms_message_service implements message_service_interface {

    /** @var string Default message type for informational messages */
    const MESSAGE_TYPE_INFO = 'info';

    /** @var string Message type for OTP/verification messages */
    const MESSAGE_TYPE_OTP = 'otp';

    /** @var string AWS message type for transactional messages - CORRECTED TO ALL CAPS */
    const AWS_TYPE_TRANSACTIONAL = 'TRANSACTIONAL';

    /** @var string AWS message type for promotional messages - CORRECTED TO ALL CAPS */
    const AWS_TYPE_PROMOTIONAL = 'PROMOTIONAL';

    /**
     * Send an SMS message to a recipient with enhanced pool support.
     *
     * @param string $recipient Recipient phone number
     * @param string $message Message content
     * @param array $options Additional options including pool preferences
     * @return bool Success status
     */
    public function send_message(string $recipient, string $message, array $options = []): bool {
        // Format phone number (remove non-numeric chars except +)
        $phonenumber = preg_replace('/[^0-9+]/', '', $recipient);

        // Skip if phone number is empty
        if (empty($phonenumber)) {
            local_equipment_debug_log('Cannot send SMS: Empty phone number');
            return false;
        }

        // Get provider ID from options or settings
        $providerid = $options['provider_id'] ?? get_config('local_equipment', 'infogateway');
        if (empty($providerid)) {
            local_equipment_debug_log('Cannot send SMS: No provider ID configured');
            return false;
        }

        // Determine message type and AWS message type
        $messagetype = $options['message_type'] ?? self::MESSAGE_TYPE_INFO;
        $awsmessagetype = $options['aws_message_type'] ?? self::AWS_TYPE_TRANSACTIONAL;

        // Log the attempt with enhanced detail
        $this->log_send_attempt($phonenumber, $message, $providerid, $messagetype);

        // Use enhanced SMS sending with automatic pool selection
        $result = $this->send_with_pool_selection($providerid, $phonenumber, $message, $messagetype, $awsmessagetype, $options);

        // Log the result with enhanced detail
        $this->log_send_result($phonenumber, $result, $messagetype);

        return $result;
    }

    /**
     * Send SMS with intelligent pool selection.
     *
     * @param string $providerid SMS provider ID
     * @param string $phonenumber Recipient phone number
     * @param string $message Message content
     * @param string $messagetype Message type (info, otp, etc.)
     * @param string $awsmessagetype AWS message type (TRANSACTIONAL, PROMOTIONAL)
     * @param array $options Additional options
     * @return bool Success status
     */
    protected function send_with_pool_selection(string $providerid, string $phonenumber, string $message, string $messagetype, string $awsmessagetype, array $options): bool {
        // Check for specific pool ID in options
        if (!empty($options['pool_id'])) {
            $poolid = $options['pool_id'];
            $originationnumber = $options['origination_number'] ?? '';

            local_equipment_debug_log("Using explicitly specified pool: {$poolid}");

            $result = local_equipment_send_sms_with_pool(
                $providerid,
                $phonenumber,
                $message,
                $awsmessagetype,
                $poolid,
                $originationnumber
            );
        } else {
            // Use automatic pool selection based on message type
            local_equipment_debug_log("Using automatic pool selection for message type: {$messagetype}");

            $result = local_equipment_send_sms_auto_pool(
                $providerid,
                $phonenumber,
                $message,
                $messagetype,
                $awsmessagetype
            );
        }

        // Enhanced result processing
        if (is_object($result)) {
            if ($result->success) {
                local_equipment_debug_log("SMS sent successfully via pool {$result->poolid} - Message ID: {$result->messageid}");
                return true;
            } else {
                local_equipment_debug_log("SMS failed: {$result->errortype} - {$result->errormessage}");

                // Try fallback strategy if pool-based sending failed
                if (!empty($result->poolid) && $result->errortype === 'aws_service_error') {
                    return $this->attempt_fallback_sending($providerid, $phonenumber, $message, $messagetype, $awsmessagetype);
                }
            }
        }

        return false;
    }

    /**
     * Attempt fallback sending strategy when pool-based sending fails.
     *
     * @param string $providerid SMS provider ID
     * @param string $phonenumber Recipient phone number
     * @param string $message Message content
     * @param string $messagetype Message type
     * @param string $awsmessagetype AWS message type
     * @return bool Success status
     */
    protected function attempt_fallback_sending(string $providerid, string $phonenumber, string $message, string $messagetype, string $awsmessagetype): bool {
        local_equipment_debug_log("Attempting fallback sending strategy for message type: {$messagetype}");

        // Try using origination phone number without pool
        $originationnumber = local_equipment_get_origination_phone_for_message_type($messagetype);

        if (!empty($originationnumber)) {
            $result = local_equipment_send_sms_with_pool(
                $providerid,
                $phonenumber,
                $message,
                $awsmessagetype,
                '', // No pool ID
                $originationnumber
            );

            if (is_object($result) && $result->success) {
                local_equipment_debug_log("Fallback sending successful using origination number: {$originationnumber}");
                return true;
            }
        }

        local_equipment_debug_log("Fallback sending failed");
        return false;
    }

    /**
     * Send an OTP verification message using the OTP pool.
     *
     * @param string $recipient Recipient phone number
     * @param string $code OTP code
     * @param array $options Additional options
     * @return bool Success status
     */
    public function send_otp_message(string $recipient, string $code, array $options = []): bool {
        global $SITE;

        $message = $options['otp_message'] ?? "Your {$SITE->shortname} verification code is: {$code}";

        $otpoptions = array_merge($options, [
            'message_type' => self::MESSAGE_TYPE_OTP,
            'aws_message_type' => self::AWS_TYPE_TRANSACTIONAL
        ]);

        return $this->send_message($recipient, $message, $otpoptions);
    }

    /**
     * Send an informational message using the info pool.
     *
     * @param string $recipient Recipient phone number
     * @param string $message Message content
     * @param array $options Additional options
     * @return bool Success status
     */
    public function send_info_message(string $recipient, string $message, array $options = []): bool {
        $infooptions = array_merge($options, [
            'message_type' => self::MESSAGE_TYPE_INFO,
            'aws_message_type' => self::AWS_TYPE_TRANSACTIONAL
        ]);

        return $this->send_message($recipient, $message, $infooptions);
    }

    /**
     * Send a promotional message using appropriate pool and settings.
     *
     * @param string $recipient Recipient phone number
     * @param string $message Message content
     * @param array $options Additional options
     * @return bool Success status
     */
    public function send_promotional_message(string $recipient, string $message, array $options = []): bool {
        $promooptions = array_merge($options, [
            'message_type' => self::MESSAGE_TYPE_INFO, // Use info pool for promotional
            'aws_message_type' => self::AWS_TYPE_PROMOTIONAL
        ]);

        return $this->send_message($recipient, $message, $promooptions);
    }

    /**
     * Validate SMS configuration including pool settings.
     *
     * @return array Validation results with detailed status
     */
    public function validate_configuration(): array {
        $validation = [
            'gateway_configured' => false,
            'info_pool_configured' => false,
            'otp_pool_configured' => false,
            'info_phone_configured' => false,
            'otp_phone_configured' => false,
            'aws_credentials_configured' => false,
            'errors' => [],
            'warnings' => []
        ];

        // Check gateway configuration
        $gatewayid = get_config('local_equipment', 'infogateway');
        if (!empty($gatewayid)) {
            global $DB;
            $gateway = $DB->get_record('sms_gateways', ['id' => $gatewayid, 'enabled' => 1]);
            if ($gateway) {
                $validation['gateway_configured'] = true;

                // Check AWS credentials
                $config = json_decode($gateway->config);
                if ($config && !empty($config->api_key) && !empty($config->api_secret) && !empty($config->api_region)) {
                    $validation['aws_credentials_configured'] = true;
                } else {
                    $validation['errors'][] = 'AWS credentials incomplete';
                }
            } else {
                $validation['errors'][] = 'Gateway not found or disabled';
            }
        } else {
            $validation['errors'][] = 'No gateway configured';
        }

        // Check pool configurations
        $infopoolid = get_config('local_equipment', 'awsinfopoolid');
        $otppoolid = get_config('local_equipment', 'awsotppoolid');
        $infophone = get_config('local_equipment', 'awsinfooriginatorphone');
        $otpphone = get_config('local_equipment', 'awsotporiginatorphone');

        $validation['info_pool_configured'] = !empty($infopoolid);
        $validation['otp_pool_configured'] = !empty($otppoolid);
        $validation['info_phone_configured'] = !empty($infophone);
        $validation['otp_phone_configured'] = !empty($otpphone);

        if (!$validation['info_pool_configured'] && !$validation['info_phone_configured']) {
            $validation['errors'][] = 'Neither info pool nor info origination phone configured';
        }

        if (!$validation['otp_pool_configured'] && !$validation['otp_phone_configured']) {
            $validation['warnings'][] = 'Neither OTP pool nor OTP origination phone configured';
        }

        return $validation;
    }

    /**
     * Log an SMS send attempt with enhanced detail.
     *
     * @param string $recipient Recipient phone number
     * @param string $message Message content
     * @param string $providerid SMS provider ID
     * @param string $messagetype Message type
     */
    protected function log_send_attempt(string $recipient, string $message, string $providerid, string $messagetype): void {
        // Create an event record with enhanced data
        $eventdata = [
            'context' => \context_system::instance(),
            'other' => [
                'recipient' => $recipient,
                'message_length' => strlen($message),
                'provider_id' => $providerid,
                'message_type' => $messagetype,
                'pool_id' => local_equipment_get_pool_id_for_message_type($messagetype),
                'timestamp' => time()
            ]
        ];

        // Trigger the event (you may need to create this event class)
        try {
            $event = \local_equipment\event\sms_send_attempted::create($eventdata);
            $event->trigger();
        } catch (Exception $e) {
            local_equipment_debug_log("Failed to trigger SMS send attempted event: " . $e->getMessage());
        }

        // Also log to debugging
        local_equipment_debug_log("SMS send attempt to: {$recipient} using {$messagetype} type");
    }

    /**
     * Log an SMS send result with enhanced detail.
     *
     * @param string $recipient Recipient phone number
     * @param bool $success Whether the send was successful
     * @param string $messagetype Message type
     */
    protected function log_send_result(string $recipient, bool $success, string $messagetype): void {
        // Create an event record with enhanced data
        $eventdata = [
            'context' => \context_system::instance(),
            'other' => [
                'recipient' => $recipient,
                'success' => $success ? 1 : 0,
                'message_type' => $messagetype,
                'timestamp' => time()
            ]
        ];

        // Trigger the event (you may need to create this event class)
        try {
            $event = \local_equipment\event\sms_send_completed::create($eventdata);
            $event->trigger();
        } catch (Exception $e) {
            local_equipment_debug_log("Failed to trigger SMS send completed event: " . $e->getMessage());
        }

        // Also log to debugging
        local_equipment_debug_log("SMS send " . ($success ? "successful" : "failed") . " to: {$recipient} using {$messagetype} type");
    }
}