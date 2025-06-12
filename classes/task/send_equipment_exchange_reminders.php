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

    /**
     * Constructor - initialize dependencies
     */
    public function __construct() {
        $this->exchange_manager = new \local_equipment\exchange_manager();
        $this->user_service = new \local_equipment\user_service();
        $this->template_service = new \local_equipment\message_template_service();
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

        // Start a transaction
        $transaction = $DB->start_delegated_transaction();

        try {
            // Check for days reminder
            $adminsetting_inadvance_days = get_config('local_equipment', 'inadvance_days');
            if (!empty($adminsetting_inadvance_days) && is_numeric($adminsetting_inadvance_days)) {
                $this->process_reminders_for_days((int)$adminsetting_inadvance_days);
            }

            // Check for hours reminder
            $adminsetting_inadvance_hours = get_config('local_equipment', 'inadvance_hours');
            if (!empty($adminsetting_inadvance_hours) && is_numeric($adminsetting_inadvance_hours)) {
                $this->process_reminders_for_hours((int)$adminsetting_inadvance_hours);
            }

            // Commit the transaction if everything went well
            $transaction->allow_commit();
        mtrace('Equipment exchange reminder task completed.');
        } catch (Exception $e) {
            // Rollback the transaction on failure
            $transaction->rollback($e);
            mtrace('Error in equipment exchange reminder task: ' . $e->getMessage());
        }
    }

    /**
     * Process reminders for a specific number of days before exchange.
     *
     * @param int $adminsetting_inadvance_days The inadvance_days admin setting for the number of days before exchange to send reminders
     */
    private function process_reminders_for_days($adminsetting_inadvance_days) {
        mtrace("Processing reminders for $adminsetting_inadvance_days days before exchange...");

        // Calculate the target time (equipment exchanges that are approximately $inadvance_days from now)
        // $targettime = time() + ($adminsetting_inadvance_days * DAYSECS);

        // Get reminder timeout from settings
        $timeout = get_config('local_equipment', 'reminder_timeout');
        if (empty($timeout) || !is_numeric($timeout)) {
            $timeout = 1; // Default 1 hours
        }

        // Calculate the window
        $clock = \core\di::get(\core\clock::class);
        $targetwindow_start = $clock->now()->getTimestamp();
        // $targetwindow_start = ;
        $targetwindow_end = $targetwindow_start + ($adminsetting_inadvance_days * DAYSECS);

        // Get users who need reminders
        $userstonotify = $this->exchange_manager->get_users_needing_reminders(
            $targetwindow_start,
            $targetwindow_end,
            'days'
        );

        // var_dump('$userstonotify');
        // var_dump($userstonotify);
        // die();

        if (empty($userstonotify)) {
            mtrace("No users found needing reminders for $adminsetting_inadvance_days days ahead.");
            return;
        }

        mtrace("Found " . count($userstonotify) . " users to notify about exchanges in the next ~$adminsetting_inadvance_days day(s).");

        // Send reminders to each user
        foreach ($userstonotify as $userdata) {
            $this->send_reminder_to_user($userdata, $adminsetting_inadvance_days);
        }
    }

    /**
     * Process reminders for a specific number of hours before exchange.
     *
     * @param float $inadvance_hours Number of hours before exchange to send reminders as determined by a system administrator in
     * admin
     */
    private function process_reminders_for_hours($inadvance_hours) {
        mtrace("Processing reminders for $inadvance_hours hours before exchange...");

        // Calculate the target time (equipment exchanges that are approximately $inadvance_hours from now)
        $clock = \core\di::get(\core\clock::class);
        $targetwindow_start = $clock->now()->getTimestamp();
        $targetwindow_end = $targetwindow_start + ($inadvance_hours * HOURSECS);


        // Get reminder timeout from settings
        $timeout = get_config('local_equipment', 'reminder_timeout');
        if (empty($timeout) || !is_numeric($timeout)) {
            $timeout = 1; // Default 1 hours
        }

        // Calculate the window
        // $targetwindow_start = $targetwindow_time - ($timeout * HOURSECS / 2);
        // $targetwindow_end = $targetwindow_time + ($timeout * HOURSECS / 2);

        // Get users who need reminders
        $userstonotify = $this->exchange_manager->get_users_needing_reminders(
            $targetwindow_start,
            $targetwindow_end,
            'hours'
        );

        if (empty($userstonotify)) {
            mtrace("No users found needing reminders for $inadvance_hours hours ahead.");
            return;
        }

        mtrace("Found " . count($userstonotify) . " users to notify about exchanges in ~$inadvance_hours hours");

        // Send reminders to each user
        foreach ($userstonotify as $userdata) {
            $this->send_reminder_to_user($userdata, 0); // 0 indicates hours-based reminder
        }
    }

    /**
     * Send a reminder to a specific user.
     *
     * @param object $userdata User and exchange data
     * @param int $inadvance_time Days before the exchange to send out reminders as determined by an admin setting (0 for hours-based reminder). This is different
     * from $daysfromnow seen below, which designates how many days before the exchange it currently is.
     * @return bool Success status
     */
    private function send_reminder_to_user($userdata, $inadvance_time) {
        global $DB;

        // echo "\n\nuserdata:\n";
        // var_dump($userdata->userid);

        // echo "inadvance_time:  $inadvance_time\n\n";
        mtrace("Sending reminder to user {$userdata->userid} for exchange ID {$userdata->exchangeid}");

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

            // Get user details
            $user = $this->user_service->get_user($userdata->userid);
            if (!$user) {
                mtrace("Could not find user with ID {$userdata->userid}");
                return false;
            }

            // Get exchange details
            $exchange = $this->exchange_manager->get_exchange($userdata->exchangeid);
            if (!$exchange) {
                mtrace("Could not find exchange with ID {$userdata->exchangeid}");
                return false;
            }

            // Get equipment list - simplified for now
            $equipmentlist = "your equipment"; // Simplified until future implementation

            // Format date and time - use starttime instead of exchangedate
            $formatteddate = userdate($exchange->starttime, get_string('strftimedaymonth', 'local_equipment'));
            $formattedstarttime = userdate($exchange->starttime, get_string('strftimetime12', 'local_equipment'));
            $formattedendtime = userdate($exchange->endtime, get_string('strftimetime12', 'local_equipment'));

            // Use pickup location fields to create location string if no combined location exists
            $location = $userdata->location ??
                "{$exchange->pickup_streetaddress}, {$exchange->pickup_city}, {$exchange->pickup_state} {$exchange->pickup_zipcode}";

            // Get the current time using Moodle's recommended approach
            $clock = \core\di::get(\core\clock::class);
            $now = $clock->now()->getTimestamp();


            $diff_secs = $exchange->starttime - $now;

            // Convert seconds to full days, so 0 would mean there are only hours from now.
            $daysfromnow = floor($diff_secs / 86400);
            $hoursfromnow = floor($diff_secs / 3600);


            // echo '<pre>';
            // var_dump('$daysfromnow: ' . $daysfromnow);
            // var_dump('$hoursfromnow: ' . $hoursfromnow);
            // echo '</pre>';


            // Determine if this is a days or hours reminder
            $remindertype = ($inadvance_time > 0) ? 'days' : 'hours';
            $remindervalue = ($inadvance_time > 0) ? $daysfromnow : $hoursfromnow;

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
                $location // Added location parameter
            );

            // Determine preferred contact method
            $method = $userdata->reminder_method ?: 'text'; // Default to text if not specified

            // Send message
            $success = false;
            switch ($method) {
                case 'text':
                    // The 'infogateway'property in the config call below, refers to the type of gateway that you'll be using to
                    // send a text to users. In the case of the equipment plug-in and AWS, 'info'is another way of saying
                    // 'Transactional'. In other words, 'infogateway' refers to whatever message type you have associated with
                    // 'Transactional' in AWS SNS/ End User Messaging.
                    $providerid = get_config('local_equipment', 'infogateway');

                    // Only use phone2 (mobile phone) for SMS
                    if (!empty($user->phone2)) {
                        $success = local_equipment_send_sms($providerid, $user->phone2, $message, 'Transactional');
                        if ($success) {
                            mtrace("Successfully sent text reminder to user {$user->id}");
                        } else {
                            mtrace("Failed to send text reminder to user {$user->id}");
                        }
                    } else {
                        mtrace("No mobile phone number (phone2) found for user {$user->id}");
                        $success = false;
                    }
                    break;
                case 'email':
                    $supportuser = \core_user::get_support_user();
                    $flccoordinatorid = $exchange->flccoordinatorid;
                    $flccoordinator = $this->user_service->get_user($flccoordinatorid);
                    $success = email_to_user($user, $supportuser, get_string('equipmentexchangereminder', 'local_equipment'), $message, '', '', '', true, $flccoordinator->email, "$flccoordinator->firstname $flccoordinator->lastname");
                    break;
                default:
                    break;
            }

            // Update reminder status
            if ($success) {
                $newremindercode = ($remindertype == 'days') ? 1 : 2;
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
}
