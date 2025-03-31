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
        return get_string('taskname_send_equipment_reminders', 'local_equipment');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        mtrace('Starting equipment exchange reminder task...');

        // Check for days reminder
        $daysadvance = get_config('local_equipment', 'inadvance_days');
        if (!empty($daysadvance) && is_numeric($daysadvance)) {
            $this->process_reminders_for_days((int)$daysadvance);
        }

        // Check for hours reminder
        $hoursadvance = get_config('local_equipment', 'inadvance_hours');
        if (!empty($hoursadvance) && is_numeric($hoursadvance)) {
            $this->process_reminders_for_hours((float)$hoursadvance);
        }

        mtrace('Equipment exchange reminder task completed.');
    }

    /**
     * Process reminders for a specific number of days before exchange.
     *
     * @param int $daysbeforeexchange Number of days before exchange to send reminders
     */
    private function process_reminders_for_days($daysbeforeexchange) {
        mtrace("Processing reminders for $daysbeforeexchange days before exchange...");

        // Calculate the target date (equipment exchanges that are exactly $daysbeforeexchange from now)
        $targetdate = time() + ($daysbeforeexchange * DAYSECS);
        $targetdatestart = strtotime(date('Y-m-d', $targetdate)); // Start of the target day
        $targetdateend = $targetdatestart + DAYSECS - 1; // End of the target day

        // Get users who need reminders
        $userstonotify = $this->exchange_manager->get_users_needing_reminders(
            $targetdatestart,
            $targetdateend,
            $daysbeforeexchange
        );

        if (empty($userstonotify)) {
            mtrace("No users found needing reminders for $daysbeforeexchange days ahead.");
            return;
        }

        mtrace("Found " . count($userstonotify) . " users to notify about exchanges on " . date('Y-m-d', $targetdatestart));

        // Send reminders to each user
        foreach ($userstonotify as $userdata) {
            $this->send_reminder_to_user($userdata, $daysbeforeexchange);
        }
    }

    /**
     * Process reminders for a specific number of hours before exchange.
     *
     * @param float $hoursbeforeexchange Number of hours before exchange to send reminders
     */
    private function process_reminders_for_hours($hoursbeforeexchange) {
        mtrace("Processing reminders for $hoursbeforeexchange hours before exchange...");

        // Calculate the target time (equipment exchanges that are approximately $hoursbeforeexchange from now)
        $targettime = time() + ($hoursbeforeexchange * HOURSECS);

        // Get reminder timeout from settings
        $timeout = get_config('local_equipment', 'reminder_timeout');
        if (empty($timeout) || !is_numeric($timeout)) {
            $timeout = 24; // Default 24 hours
        }

        // Calculate the window
        $targetstart = $targettime - ($timeout * HOURSECS / 2);
        $targetend = $targettime + ($timeout * HOURSECS / 2);

        // Get users who need reminders
        $userstonotify = $this->exchange_manager->get_users_needing_reminders(
            $targetstart,
            $targetend,
            0  // 0 indicates hours-based reminder
        );

        if (empty($userstonotify)) {
            mtrace("No users found needing reminders for $hoursbeforeexchange hours ahead.");
            return;
        }

        mtrace("Found " . count($userstonotify) . " users to notify about exchanges in ~$hoursbeforeexchange hours");

        // Send reminders to each user
        foreach ($userstonotify as $userdata) {
            $this->send_reminder_to_user($userdata, 0); // 0 indicates hours-based reminder
        }
    }

    /**
     * Send a reminder to a specific user.
     *
     * @param object $userdata User and exchange data
     * @param int $daysbeforeexchange Days before the exchange (0 for hours-based reminder)
     * @return bool Success status
     */
    private function send_reminder_to_user($userdata, $daysbeforeexchange) {
        mtrace("Sending reminder to user {$userdata->userid} for exchange ID {$userdata->exchangeid}");

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

        // Get equipment list
        $equipmentlist = $this->exchange_manager->get_equipment_list($userdata->exchangeid);

        // Format date and time
        $formatteddate = date('l, F j, Y', $exchange->exchangedate);
        $formattedtime = date('g:i A', $exchange->exchangedate);

        // Determine if this is a days or hours reminder
        $remindertype = ($daysbeforeexchange > 0) ? 'days' : 'hours';
        $remindervalue = ($daysbeforeexchange > 0) ? $daysbeforeexchange : get_config('local_equipment', 'inadvance_hours');

        // Prepare message
        $message = $this->template_service->prepare_message(
            $user,
            $exchange,
            $formatteddate,
            $formattedtime,
            $equipmentlist,
            $remindertype,
            $remindervalue
        );

        // Determine preferred contact method
        $method = $userdata->reminder_method ?: 'text'; // Default to text if not specified

        // Send message
        $success = false;
        switch ($method) {
            case 'text':
                $providerid = get_config('local_equipment', 'infogateway');
                $success = local_equipment_send_sms($providerid, $user->phone, $message, 'Transactional');
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
        } else {
            mtrace("Failed to send {$method} reminder to user {$user->id}");
        }

        return $success;
    }
}
