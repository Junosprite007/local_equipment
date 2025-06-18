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
 * Email gateway messaging service implementation.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\messaging;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/base_message_service.php');

/**
 * Email gateway messaging service (sends SMS via carrier email gateways).
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class email_gateway_message_service extends base_message_service {

    /** @var array Common carrier email gateways */
    private const CARRIER_GATEWAYS = [
        'verizon' => 'vtext.com',
        'att' => 'txt.att.net',
        't-mobile' => 'tmomail.net',
        'sprint' => 'messaging.sprintpcs.com',
        'uscellular' => 'email.uscc.net',
        'boost' => 'smsmyboostmobile.com',
        'cricket' => 'sms.cricketwireless.net',
        'metropcs' => 'mymetropcs.com'
    ];

    /**
     * {@inheritdoc}
     */
    protected function get_method_name(): string {
        return 'email_gateway';
    }

    /**
     * {@inheritdoc}
     */
    protected function do_send(string $phonenumber, string $message, array $options = []): bool {
        // Get carrier gateway domain from settings or options
        $gateway = $options['gateway'] ?? get_config('local_equipment', 'default_gateway');

        if (empty($gateway)) {
            local_equipment_debug_log("ERROR: Missing carrier gateway configuration");
            return false;
        }

        // If gateway is a carrier name, convert to domain
        if (isset(self::CARRIER_GATEWAYS[$gateway])) {
            $gateway = self::CARRIER_GATEWAYS[$gateway];
        }

        // Remove any non-numeric characters from phone number for email gateway
        $cleanphone = preg_replace('/[^0-9]/', '', $phonenumber);

        // Convert phone number to email address using carrier gateway
        $email = $cleanphone . '@' . $gateway;

        local_equipment_debug_log("Sending SMS via email gateway to: {$email}");

        // Create a dummy user object for the email recipient
        $touser = new \stdClass();
        $touser->id = -99;
        $touser->email = $email;
        $touser->firstname = '';
        $touser->lastname = '';
        $touser->maildisplay = true;
        $touser->mailformat = 1; // HTML
        $touser->maildigest = 0;
        $touser->autosubscribe = 1;
        $touser->emailstop = 0;

        // Get sender user
        $fromuser = \core_user::get_support_user();
        if (!empty($options['from_user_id'])) {
            global $DB;
            $tempuser = $DB->get_record('user', ['id' => $options['from_user_id']]);
            if ($tempuser) {
                $fromuser = $tempuser;
            }
        }

        // SMS gateways typically ignore the subject, but some may display it
        $subject = $options['subject'] ?? '';

        try {
            // Send the email which will be converted to SMS by the carrier
            $result = email_to_user(
                $touser,
                $fromuser,
                $subject,
                $message,
                $message, // HTML message (same as plain text)
                '', // Attachment
                '', // Attachment name
                false, // Use plain text for SMS gateways
                '', // Reply to
                '' // Reply to name
            );

            if ($result) {
                local_equipment_debug_log("Email-to-SMS sent successfully via {$gateway}");
                return true;
            } else {
                local_equipment_debug_log("Email-to-SMS failed via {$gateway}");
                return false;
            }

        } catch (Exception $e) {
            local_equipment_debug_log("ERROR: Email gateway exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available carrier gateways.
     *
     * @return array Carrier name => gateway domain
     */
    public static function get_carrier_gateways(): array {
        return self::CARRIER_GATEWAYS;
    }

    /**
     * Validate email gateway configuration.
     *
     * @return array Validation results
     */
    public function validate_configuration(): array {
        $validation = [
            'gateway_configured' => false,
            'email_configured' => false,
            'errors' => [],
            'warnings' => []
        ];

        // Check if default gateway is configured
        $gateway = get_config('local_equipment', 'default_gateway');
        if (empty($gateway)) {
            $validation['errors'][] = 'No default carrier gateway configured';
        } else {
            $validation['gateway_configured'] = true;

            // Validate gateway format
            if (!isset(self::CARRIER_GATEWAYS[$gateway]) && !filter_var('test@' . $gateway, FILTER_VALIDATE_EMAIL)) {
                $validation['warnings'][] = 'Gateway domain may not be valid: ' . $gateway;
            }
        }

        // Check if Moodle email is configured
        global $CFG;
        if (empty($CFG->smtphosts) && empty($CFG->noemailever)) {
            $validation['warnings'][] = 'Moodle email may not be properly configured';
        } else {
            $validation['email_configured'] = true;
        }

        return $validation;
    }
}
