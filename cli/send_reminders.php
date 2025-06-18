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
 * CLI script for manually sending equipment exchange reminders.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/local/equipment/lib.php');

/**
 * Check if a user's phone number is verified.
 *
 * @param int $userid User ID
 * @return bool True if phone is verified
 */
function is_phone_verified($userid) {
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
function get_verified_phone_number($userid) {
    global $DB;
    $record = $DB->get_record('local_equipment_phonecommunication_otp', [
        'userid' => $userid,
        'phoneisverified' => 1
    ], 'tophonenumber', IGNORE_MULTIPLE);

    return $record ? $record->tophonenumber : false;
}

// Get CLI options
list($options, $unrecognized) = cli_get_params([
    'help' => false,
    'type' => '',
    'userid' => 0,
    'exchangeid' => 0,
    'dry-run' => false,
    'force' => false,
    'verbose' => false,
    'days' => '',
    'hours' => '',
], [
    'h' => 'help',
    't' => 'type',
    'u' => 'userid',
    'e' => 'exchangeid',
    'd' => 'dry-run',
    'f' => 'force',
    'v' => 'verbose',
]);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help = "
Manual Equipment Exchange Reminder Sender

Manually send equipment exchange reminders for testing and debugging.

Options:
-h, --help              Print out this help
-t, --type=TYPE         Reminder type: 'days', 'hours', or 'both' (default: both)
-u, --userid=ID         Send reminder only to specific user ID
-e, --exchangeid=ID     Send reminders only for specific exchange ID
-d, --dry-run           Show what would be sent without actually sending
-f, --force             Force send even if reminder was already sent
-v, --verbose           Show detailed output
    --days=NUM          Override days setting (e.g., --days=7)
    --hours=NUM         Override hours setting (e.g., --hours=2)

Examples:
\$ php local/equipment/cli/send_reminders.php --dry-run
\$ php local/equipment/cli/send_reminders.php --type=days --verbose
\$ php local/equipment/cli/send_reminders.php --userid=123 --force
\$ php local/equipment/cli/send_reminders.php --exchangeid=456 --dry-run
\$ php local/equipment/cli/send_reminders.php --days=3 --hours=1 --verbose
\$ php local/equipment/cli/send_reminders.php --type=hours --force --verbose

Types:
  days    - Send reminders for exchanges happening in X days
  hours   - Send reminders for exchanges happening in X hours
  both    - Send both types of reminders (default)
";

    echo $help;
    die;
}

cli_heading('Equipment Exchange Reminder Sender');

// Validate SMS configuration first
$gatewayid = get_config('local_equipment', 'infogateway');
if (empty($gatewayid)) {
    cli_problem('❌ No SMS gateway configured. Cannot send reminders.');
    exit(1);
}

$gateway = $DB->get_record('sms_gateways', ['id' => $gatewayid, 'enabled' => 1]);
if (!$gateway) {
    cli_problem("❌ SMS gateway with ID {$gatewayid} not found or disabled.");
    exit(1);
}

cli_writeln("✅ SMS Gateway: {$gateway->name}");

// Initialize task dependencies
$exchange_manager = new \local_equipment\exchange_manager();
$user_service = new \local_equipment\user_service();
$template_service = new \local_equipment\message_template_service();
$clock = \core\di::get(\core\clock::class);

// Get configuration
$config_days = !empty($options['days']) ? (int)$options['days'] : get_config('local_equipment', 'inadvance_days');
$config_hours = !empty($options['hours']) ? (int)$options['hours'] : get_config('local_equipment', 'inadvance_hours');

$reminder_type = $options['type'] ?: 'both';
$specific_userid = (int)$options['userid'];
$specific_exchangeid = (int)$options['exchangeid'];
$dry_run = $options['dry-run'];
$force = $options['force'];
$verbose = $options['verbose'];

cli_writeln("Configuration:");
cli_writeln("  Days reminder: " . ($config_days ?: 'disabled'));
cli_writeln("  Hours reminder: " . ($config_hours ?: 'disabled'));
cli_writeln("  Type filter: {$reminder_type}");
if ($specific_userid) cli_writeln("  User filter: {$specific_userid}");
if ($specific_exchangeid) cli_writeln("  Exchange filter: {$specific_exchangeid}");
cli_writeln("  Dry run: " . ($dry_run ? 'YES' : 'NO'));
cli_writeln("  Force send: " . ($force ? 'YES' : 'NO'));
cli_writeln("");

$total_processed = 0;
$total_sent = 0;
$total_errors = 0;

/**
 * Send reminder to a specific user with enhanced error handling.
 */
function send_user_reminder($userdata, $inadvance_time, $reminder_type, $managers, $force, $dry_run, $verbose) {
    global $DB, $total_sent, $total_errors;

    list($exchange_manager, $user_service, $template_service, $clock) = $managers;

    if ($verbose) {
        cli_writeln("Processing user {$userdata->userid} for exchange {$userdata->exchangeid} ({$reminder_type})");
    }

    // Get user details
    $user = $user_service->get_user($userdata->userid);
    if (!$user) {
        cli_problem("  ❌ User {$userdata->userid} not found");
        $total_errors++;
        return false;
    }

    // Get exchange details
    $exchange = $exchange_manager->get_exchange($userdata->exchangeid);
    if (!$exchange) {
        cli_problem("  ❌ Exchange {$userdata->exchangeid} not found");
        $total_errors++;
        return false;
    }

    // Check if already sent (unless forced)
    if (!$force) {
        $expected_code = ($reminder_type === 'days') ? 1 : 2;
        if ($userdata->reminder_code >= $expected_code) {
            if ($verbose) {
                cli_writeln("  ⏭️  Skipping {$user->firstname} {$user->lastname} - reminder already sent (code: {$userdata->reminder_code})");
            }
            return true;
        }
    }

    // Determine reminder method
    $method = $userdata->reminder_method ?: 'text';

    // SECURITY CHECK: For SMS reminders, verify phone number is verified
    if ($method === 'text') {
        if (empty($user->phone2)) {
            cli_problem("  ❌ {$user->firstname} {$user->lastname} - No mobile phone number");
            $total_errors++;
            return false;
        }

        // Check if phone number is verified
        if (!is_phone_verified($user->id)) {
            cli_problem("  ❌ {$user->firstname} {$user->lastname} - Phone number not verified (security check)");
            if ($verbose) {
                cli_writeln("     Phone: {$user->phone2} (UNVERIFIED)");
                cli_writeln("     For security, reminders are only sent to verified phone numbers");
            }
            $total_errors++;
            return false;
        }

        // Validate phone number format
        $phoneobj = local_equipment_parse_phone_number($user->phone2);
        if (!empty($phoneobj->errors)) {
            cli_problem("  ❌ {$user->firstname} {$user->lastname} - Invalid phone format: " . implode(', ', $phoneobj->errors));
            $total_errors++;
            return false;
        }

        // Get verified phone number (may be different from user->phone2)
        $verified_phone = get_verified_phone_number($user->id);
        if (!$verified_phone) {
            cli_problem("  ❌ {$user->firstname} {$user->lastname} - Could not retrieve verified phone number");
            $total_errors++;
            return false;
        }

        if ($verbose) {
            cli_writeln("     Phone: {$verified_phone} (VERIFIED ✅)");
        }
    }

    // Prepare message data
    $equipmentlist = "your equipment";
    $formatteddate = userdate($exchange->starttime, '%A, %B %d');
    $formattedstarttime = userdate($exchange->starttime, '%I:%M %p');
    $formattedendtime = userdate($exchange->endtime, '%I:%M %p');

    $location = $userdata->location ??
        "{$exchange->pickup_streetaddress}, {$exchange->pickup_city}, {$exchange->pickup_state} {$exchange->pickup_zipcode}";

    // Calculate time differences
    $now = time();
    $diff_secs = $exchange->starttime - $now;
    $daysfromnow = floor($diff_secs / 86400);
    $hoursfromnow = floor($diff_secs / 3600);
    $remindervalue = ($reminder_type === 'days') ? $daysfromnow : $hoursfromnow;

    // Prepare message
    $message = $template_service->prepare_message(
        $user,
        $exchange,
        $formatteddate,
        $formattedstarttime,
        $formattedendtime,
        $equipmentlist,
        $reminder_type,
        $remindervalue,
        $location
    );

    if ($dry_run) {
        cli_writeln("  📋 Would send {$method} to: {$user->firstname} {$user->lastname}");
        if ($verbose) {
            if ($method === 'text') {
                cli_writeln("     Phone: {$verified_phone} (VERIFIED ✅)");
            } else {
                cli_writeln("     Email: {$user->email}");
            }
            cli_writeln("     Exchange: " . userdate($exchange->starttime, '%Y-%m-%d %H:%M'));
            cli_writeln("     Location: {$location}");
            cli_writeln("     Message: " . substr($message, 0, 100) . "...");
            cli_writeln("");
        }
        // FIX: Increment counter in dry-run mode
        $total_sent++;
        return true;
    }

    // Actually send the reminder
    $success = false;

    if ($method === 'text') {
        $gatewayid = get_config('local_equipment', 'infogateway');
        $originationnumber = get_config('local_equipment', 'awsinfooriginatorphone');
        $response = local_equipment_send_sms($gatewayid, $verified_phone, $message, 'Transactional', $originationnumber);

        if (is_object($response) && isset($response->success) && $response->success) {
            cli_writeln("  ✅ SMS sent to: {$user->firstname} {$user->lastname} ({$verified_phone})");
            if ($verbose && isset($response->messageid)) {
                cli_writeln("     Message ID: {$response->messageid}");
            }
            $success = true;
        } else {
            $errorinfo = '';
            if (is_object($response) && isset($response->errormessage)) {
                $errorinfo = $response->errormessage;
            }
            cli_problem("  ❌ SMS failed to {$user->firstname} {$user->lastname}: {$errorinfo}");
            $total_errors++;
            return false;
        }
    } else if ($method === 'email') {
        $supportuser = \core_user::get_support_user();
        $success = email_to_user(
            $user,
            $supportuser,
            'Equipment Exchange Reminder',
            $message
        );

        if ($success) {
            cli_writeln("  ✅ Email sent to: {$user->firstname} {$user->lastname} ({$user->email})");
        } else {
            cli_problem("  ❌ Email failed to {$user->firstname} {$user->lastname}");
            $total_errors++;
            return false;
        }
    }

    // Update reminder status and increment counter
    if ($success) {
        $newremindercode = ($reminder_type === 'days') ? 1 : 2;
        $exchange_manager->update_reminder_status($userdata->userid, $userdata->exchangeid, $newremindercode);
        $total_sent++;

        if ($verbose) {
            cli_writeln("     Updated reminder code to: {$newremindercode}");
        }
    }

    return $success;
}

// Process days reminders
if (($reminder_type === 'both' || $reminder_type === 'days') && !empty($config_days)) {
    cli_heading("Processing Days Reminders ({$config_days} days ahead)", 2);

    $now = $clock->now()->getTimestamp();
    $window_end = $now + ($config_days * DAYSECS);

    $users = $exchange_manager->get_users_needing_reminders($now, $window_end, 'days');

    // Apply filters
    if ($specific_userid) {
        $users = array_filter($users, function ($user) use ($specific_userid) {
            return $user->userid == $specific_userid;
        });
    }

    if ($specific_exchangeid) {
        $users = array_filter($users, function ($user) use ($specific_exchangeid) {
            return $user->exchangeid == $specific_exchangeid;
        });
    }

    cli_writeln("Found " . count($users) . " users needing days reminders");

    if (!empty($users)) {
        $managers = [$exchange_manager, $user_service, $template_service, $clock];

        foreach ($users as $userdata) {
            $total_processed++;
            send_user_reminder($userdata, $config_days, 'days', $managers, $force, $dry_run, $verbose);
        }
    }
}

// Process hours reminders
if (($reminder_type === 'both' || $reminder_type === 'hours') && !empty($config_hours)) {
    cli_heading("Processing Hours Reminders ({$config_hours} hours ahead)", 2);

    $now = $clock->now()->getTimestamp();
    $window_end = $now + ($config_hours * HOURSECS);

    $users = $exchange_manager->get_users_needing_reminders($now, $window_end, 'hours');

    // Apply filters
    if ($specific_userid) {
        $users = array_filter($users, function ($user) use ($specific_userid) {
            return $user->userid == $specific_userid;
        });
    }

    if ($specific_exchangeid) {
        $users = array_filter($users, function ($user) use ($specific_exchangeid) {
            return $user->exchangeid == $specific_exchangeid;
        });
    }

    cli_writeln("Found " . count($users) . " users needing hours reminders");

    if (!empty($users)) {
        $managers = [$exchange_manager, $user_service, $template_service, $clock];

        foreach ($users as $userdata) {
            $total_processed++;
            send_user_reminder($userdata, $config_hours, 'hours', $managers, $force, $dry_run, $verbose);
        }
    }
}

// Summary
cli_heading('Summary', 2);
cli_writeln("Total processed: {$total_processed}");

if ($dry_run) {
    cli_writeln("📋 DRY RUN - No reminders were actually sent");
    cli_writeln("Would have sent: {$total_sent}");
} else {
    cli_writeln("✅ Successfully sent: {$total_sent}");
}

if ($total_errors > 0) {
    cli_writeln("❌ Errors: {$total_errors}");
    exit(1);
} else {
    cli_writeln("🎉 Completed without errors");
}
