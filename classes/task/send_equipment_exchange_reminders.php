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
 * Scheduled task for sending equipment exchange reminders.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/equipment/lib.php');

/**
 * Scheduled task to send equipment exchange reminders to users.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class send_equipment_exchange_reminders extends \core\task\scheduled_task {
    /** @var \local_equipment\exchange_manager Exchange manager instance */
    protected $exchange_manager;

    /** @var \local_equipment\user_service User service instance */
    protected $user_service;

    /** @var \local_equipment\message_template_service Template service instance */
    protected $template_service;

    /** @var \core\clock Clock instance for time operations */
    protected $clock;

    /**
     * Constructor - initialize dependencies
     */
    public function __construct() {
        $this->exchange_manager = new \local_equipment\exchange_manager();
        $this->user_service = new \local_equipment\user_service();
        $this->template_service = new \local_equipment\message_template_service();
        $this->clock = \core\di::get(\core\clock::class);
    }

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskname_sendexchangereminders', 'local_equipment');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        mtrace('Starting equipment exchange reminder task...');

        // Validate SMS gateway configuration before proceeding
        if (!$this->validate_sms_configuration()) {
            mtrace('SMS configuration validation failed. Aborting task.');
            return;
        }

        // Start a transaction
        $transaction = $DB->start_delegated_transaction();

        try {
            $processed = 0;
            $errors = 0;

            // Check for days reminder
            $adminsetting_inadvance_days = get_config('local_equipment', 'inadvance_days');
            if (!empty($adminsetting_inadvance_days) && is_numeric($adminsetting_inadvance_days)) {
                $result = $this->process_reminders_for_days((int)$adminsetting_inadvance_days);
                $processed += $result['processed'];
                $errors += $result['errors'];
            }

            // Check for hours reminder
            $adminsetting_inadvance_hours = get_config('local_equipment', 'inadvance_hours');
            if (!empty($adminsetting_inadvance_hours) && is_numeric($adminsetting_inadvance_hours)) {
                $result = $this->process_reminders_for_hours((int)$adminsetting_inadvance_hours);
                $processed += $result['processed'];
                $errors += $result['errors'];
            }

            // Commit the transaction if everything went well
            $transaction->allow_commit();
            mtrace("Equipment exchange reminder task completed. Processed: {$processed}, Errors: {$errors}");
        } catch (Exception $e) {
            // Rollback the transaction on failure
            $transaction->rollback($e);
            mtrace('Error in equipment exchange reminder task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate SMS configuration before sending messages.
     *
     * @return bool True if configuration is valid
     */
    protected function validate_sms_configuration(): bool {
        $gatewayid = get_config('local_equipment', 'infogateway');

        if (empty($gatewayid)) {
            mtrace('ERROR: No SMS gateway configured for info messages');
            return false;
        }

        $gateway = $this->get_sms_gateway($gatewayid);
        if (!$gateway) {
            mtrace("ERROR: SMS gateway with ID {$gatewayid} not found or disabled");
            return false;
        }

        mtrace("SMS gateway validated: {$gateway->name} (ID: {$gateway->id})");
        return true;
    }

    /**
     * Get SMS gateway by ID.
     *
     * @param int $gatewayid Gateway ID
     * @return object|false Gateway object or false if not found
     */
    protected function get_sms_gateway(int $gatewayid) {
        global $DB;
        return $DB->get_record('sms_gateways', ['id' => $gatewayid, 'enabled' => 1]);
    }

    /**
     * Process reminders for a specific number of days before exchange.
     *
     * @param int $adminsetting_inadvance_days The inadvance_days admin setting for the number of days before exchange to send reminders
     * @return array Array with processed and error counts
     */
    private function process_reminders_for_days($adminsetting_inadvance_days): array {
        mtrace("Processing reminders for {$adminsetting_inadvance_days} days before exchange...");

        // Get reminder timeout from settings
        $timeout = get_config('local_equipment', 'reminder_timeout');
        if (empty($timeout) || !is_numeric($timeout)) {
            $timeout = 24; // Default 24 hours
        }

        // Calculate the window
        $targetwindow_start = $this->clock->now()->getTimestamp();
        $targetwindow_end = $targetwindow_start + ($adminsetting_inadvance_days * DAYSECS);

        // Get users who need reminders
        $userstonotify = $this->exchange_manager->get_users_needing_reminders(
            $targetwindow_start,
            $targetwindow_end,
            'days'
        );

        if (empty($userstonotify)) {
            mtrace("No users found needing reminders for {$adminsetting_inadvance_days} days ahead.");
            return ['processed' => 0, 'errors' => 0];
        }

        mtrace("Found " . count($userstonotify) . " users to notify about exchanges in the next ~{$adminsetting_inadvance_days} day(s).");

        return $this->send_reminders_to_users($userstonotify, $adminsetting_inadvance_days, 'days');
    }

    /**
     * Process reminders for a specific number of hours before exchange.
     *
     * @param float $inadvance_hours Number of hours before exchange to send reminders
     * @return array Array with processed and error counts
     */
    private function process_reminders_for_hours($inadvance_hours): array {
        mtrace("Processing reminders for {$inadvance_hours} hours before exchange...");

        // Calculate the window
        $targetwindow_start = $this->clock->now()->getTimestamp();
        $targetwindow_end = $targetwindow_start + ($inadvance_hours * HOURSECS);

        // Get users who need reminders
        $userstonotify = $this->exchange_manager->get_users_needing_reminders(
            $targetwindow_start,
            $targetwindow_end,
            'hours'
        );

        if (empty($userstonotify)) {
            mtrace("No users found needing reminders for {$inadvance_hours} hours ahead.");
            return ['processed' => 0, 'errors' => 0];
        }

        mtrace("Found " . count($userstonotify) . " users to notify about exchanges in ~{$inadvance_hours} hours");

        return $this->send_reminders_to_users($userstonotify, 0, 'hours'); // 0 indicates hours-based reminder
    }

    /**
     * Send reminders to multiple users.
     *
     * @param array $userstonotify Array of user data objects
     * @param int $inadvance_time Time before exchange (0 for hours-based)
     * @param string $remindertype Type of reminder ('days' or 'hours')
     * @return array Array with processed and error counts
     */
    protected function send_reminders_to_users(array $userstonotify, int $inadvance_time, string $remindertype): array {
        $processed = 0;
        $errors = 0;

        foreach ($userstonotify as $userdata) {
            try {
                $success = $this->send_reminder_to_user($userdata, $inadvance_time, $remindertype);
                if ($success) {
                    $processed++;
                } else {
                    $errors++;
                }
            } catch (Exception $e) {
                mtrace("Exception sending reminder to user {$userdata->userid}: " . $e->getMessage());
                $errors++;
            }
        }

        return ['processed' => $processed, 'errors' => $errors];
    }

    /**
     * Send a reminder to a specific user.
     *
     * @param object $userdata User and exchange data
     * @param int $inadvance_time Days before the exchange (0 for hours-based reminder)
     * @param string $remindertype Type of reminder ('days' or 'hours')
     * @return bool Success status
     */
    private function send_reminder_to_user($userdata, $inadvance_time, string $remindertype): bool {
        global $DB;

        mtrace("Sending {$remindertype} reminder to user {$userdata->userid} for exchange ID {$userdata->exchangeid}");

        // Create a transaction for this specific user reminder
        $transaction = $DB->start_delegated_transaction();

        try {
            // Get user details
            $user = $this->user_service->get_user($userdata->userid);
            if (!$user) {
                mtrace("Could not find user with ID {$userdata->userid}");
                $transaction->rollback(new \Exception("User not found: {$userdata->userid}"));
                return false;
            }

            // Get exchange details
            $exchange = $this->exchange_manager->get_exchange($userdata->exchangeid);
            if (!$exchange) {
                mtrace("Could not find exchange with ID {$userdata->exchangeid}");
                $transaction->rollback(new \Exception("Exchange not found: {$userdata->exchangeid}"));
                return false;
            }

            // Get equipment list - simplified for now
            $equipmentlist = "your equipment"; // Simplified until future implementation

            // Format date and time
            $formatteddate = userdate($exchange->starttime, get_string('strftimedaymonth', 'local_equipment'));
            $formattedstarttime = userdate($exchange->starttime, get_string('strftimetime12', 'local_equipment'));
            $formattedendtime = userdate($exchange->endtime, get_string('strftimetime12', 'local_equipment'));

            // Use pickup location fields to create location string
            $location = $userdata->location ??
                "{$exchange->pickup_streetaddress}, {$exchange->pickup_city}, {$exchange->pickup_state} {$exchange->pickup_zipcode}";

            // Calculate time differences
            $now = $this->clock->now()->getTimestamp();
            $diff_secs = $exchange->starttime - $now;
            $daysfromnow = floor($diff_secs / 86400);
            $hoursfromnow = floor($diff_secs / 3600);

            // Determine reminder value
            $remindervalue = ($remindertype === 'days') ? $daysfromnow : $hoursfromnow;

            // Prepare message
            $message = $this->template_service->prepare_message(
                $user,
                $exchange,
                $formatteddate,
                $formattedstarttime,
                $formattedendtime,
                $equipmentlist,
                $remindertype,
                $remindervalue,
                $location
            );

            // Determine preferred contact method
            $method = $userdata->reminder_method ?: 'text'; // Default to text if not specified

            // Send message based on method
            $success = false;
            switch ($method) {
                case 'text':
                    $success = $this->send_sms_reminder($user, $message);
                    break;
                case 'email':
                    $success = $this->send_email_reminder($user, $exchange, $message);
                    break;
                default:
                    mtrace("Unknown reminder method: {$method} for user {$user->id}");
                    break;
            }

            // Update reminder status if successful
            if ($success) {
                $newremindercode = ($remindertype === 'days') ? 1 : 2;
                $this->exchange_manager->update_reminder_status($userdata->userid, $userdata->exchangeid, $newremindercode);
                mtrace("Successfully sent {$method} reminder to user {$user->id}");
                $transaction->allow_commit();
            } else {
                mtrace("Failed to send {$method} reminder to user {$user->id}");
                $transaction->rollback(new \Exception("Failed to send reminder"));
                return false;
            }

            return $success;
        } catch (Exception $e) {
            $transaction->rollback($e);
            mtrace("Exception caught while sending reminder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS reminder to user.
     *
     * @param object $user User object
     * @param string $message Message content
     * @return bool Success status
     */
    protected function send_sms_reminder(object $user, string $message): bool {
        // Validate phone number exists
        if (empty($user->phone2)) {
            mtrace("No mobile phone number (phone2) found for user {$user->id}");
            return false;
        }

        // SECURITY CHECK: Verify phone number is verified before sending
        if (!$this->is_phone_verified($user->id)) {
            mtrace("Phone number not verified for user {$user->id} - skipping SMS for security");
            return false;
        }

        // Get the verified phone number (may be different from user->phone2)
        $verified_phone = $this->get_verified_phone_number($user->id);
        if (!$verified_phone) {
            mtrace("Could not retrieve verified phone number for user {$user->id}");
            return false;
        }

        // Parse and validate phone number format
        $phoneobj = local_equipment_parse_phone_number($verified_phone);
        if (!empty($phoneobj->errors)) {
            mtrace("Invalid verified phone number for user {$user->id}: " . implode(', ', $phoneobj->errors));
            return false;
        }

        // Get SMS gateway
        $gatewayid = get_config('local_equipment', 'infogateway');
        if (empty($gatewayid)) {
            mtrace("No SMS gateway configured for info messages");
            return false;
        }

        // Send SMS using the verified phone number
        try {
            $originationnumber = get_config('local_equipment', 'awsinfooriginatorphone');
            $response = local_equipment_send_sms($gatewayid, $phoneobj->phone, $message, 'Transactional', $originationnumber);

            // Properly handle the response object
            if (is_object($response) && isset($response->success) && $response->success) {
                mtrace("SMS sent successfully to user {$user->id} at verified number {$phoneobj->phone}");
                return true;
            } else {
                // Log detailed error information
                $errorinfo = '';
                if (is_object($response)) {
                    if (isset($response->errormessage)) {
                        $errorinfo = $response->errormessage;
                    }
                    if (isset($response->errorobject) && is_object($response->errorobject)) {
                        if (isset($response->errorobject->awserrorcode)) {
                            $errorinfo .= " (AWS Error: {$response->errorobject->awserrorcode})";
                        }
                    }
                } else {
                    $errorinfo = 'Invalid response object';
                }

                mtrace("SMS failed to user {$user->id}: {$errorinfo}");
                return false;
            }
        } catch (Exception $e) {
            mtrace("Exception sending SMS to user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email reminder to user.
     *
     * @param object $user User object
     * @param object $exchange Exchange object
     * @param string $message Message content
     * @return bool Success status
     */
    protected function send_email_reminder(object $user, object $exchange, string $message): bool {
        try {
            $supportuser = \core_user::get_support_user();
            $flccoordinatorid = $exchange->flccoordinatorid;
            $flccoordinator = $this->user_service->get_user($flccoordinatorid);

            $replyto = '';
            $replytoname = '';
            if ($flccoordinator) {
                $replyto = $flccoordinator->email;
                $replytoname = "{$flccoordinator->firstname} {$flccoordinator->lastname}";
            }

            $success = email_to_user(
                $user,
                $supportuser,
                get_string('equipmentexchangereminder', 'local_equipment'),
                $message,
                '',
                '',
                '',
                true,
                $replyto,
                $replytoname
            );

            if ($success) {
                mtrace("Email sent successfully to user {$user->id}");
            } else {
                mtrace("Email failed to user {$user->id}");
            }

            return $success;
        } catch (Exception $e) {
            mtrace("Exception sending email to user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a user's phone number is verified.
     *
     * @param int $userid User ID
     * @return bool True if phone is verified
     */
    protected function is_phone_verified(int $userid): bool {
        global $DB;
        return $DB->record_exists('local_equipment_phonecommunication_otp', [
            'userid' => $userid,
            'phoneisverified' => 1
        ]);
    }

    /**
     * Get the verified phone number for a user.
     *
     * @param int $userid User ID
     * @return string|false Verified phone number or false if not found
     */
    protected function get_verified_phone_number(int $userid) {
        global $DB;
        $record = $DB->get_record('local_equipment_phonecommunication_otp', [
            'userid' => $userid,
            'phoneisverified' => 1
        ], 'tophonenumber', IGNORE_MULTIPLE);

        return $record ? $record->tophonenumber : false;
    }
}
