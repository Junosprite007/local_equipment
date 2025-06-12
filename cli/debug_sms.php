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
 * CLI script for debugging SMS configuration in the Equipment plugin.
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

// Get CLI options
list($options, $unrecognized) = cli_get_params([
    'help' => false,
    'test-phone' => '',
    'send-test' => false,
    'verbose' => false
], [
    'h' => 'help',
    'p' => 'test-phone',
    's' => 'send-test',
    'v' => 'verbose'
]);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help = "
SMS Configuration Debug Tool for Equipment Plugin

Options:
-h, --help              Print out this help
-p, --test-phone        Phone number to use for testing (e.g., +15551234567)
-s, --send-test         Actually send a test SMS (requires --test-phone)
-v, --verbose           Show detailed configuration information

Examples:
\$ php local/equipment/cli/debug_sms.php
\$ php local/equipment/cli/debug_sms.php --verbose
\$ php local/equipment/cli/debug_sms.php --test-phone=+15551234567 --send-test
";

    echo $help;
    die;
}

cli_heading('Equipment Plugin SMS Debug Tool');

// Check SMS gateway configuration
cli_heading('SMS Gateway Configuration', 2);

$gatewayid = get_config('local_equipment', 'infogateway');

if (empty($gatewayid)) {
    cli_problem('❌ No SMS gateway configured for info messages');
    cli_writeln('Configure at: Site administration → Plugins → SMS → SMS gateways');
    exit(1);
}

cli_writeln("Gateway ID: {$gatewayid}");

// Check if gateway exists and is enabled
$gateway = $DB->get_record('sms_gateways', ['id' => $gatewayid]);
if (!$gateway) {
    cli_problem("❌ Gateway with ID {$gatewayid} not found");
    exit(1);
}

if (!$gateway->enabled) {
    cli_problem("❌ Gateway '{$gateway->name}' is disabled");
    exit(1);
}

cli_writeln("✅ Gateway Found: {$gateway->name} (enabled)");

// Test gateway configuration
if ($gateway->gateway === 'smsgateway_aws\\gateway') {
    cli_heading('AWS Configuration', 3);

    $config = json_decode($gateway->config);
    $configvalid = true;

    cli_writeln('API Key: ' . (empty($config->api_key) ? '❌ Missing' : '✅ Present'));
    cli_writeln('API Secret: ' . (empty($config->api_secret) ? '❌ Missing' : '✅ Present'));
    cli_writeln('Region: ' . (empty($config->api_region) ? '❌ Missing' : "✅ {$config->api_region}"));

    if (empty($config->api_key) || empty($config->api_secret) || empty($config->api_region)) {
        cli_problem('❌ Incomplete AWS configuration');
        $configvalid = false;
    }

    if ($options['verbose'] && $configvalid) {
        cli_heading('Detailed AWS Configuration', 4);
        cli_writeln("Region: {$config->api_region}");
        cli_writeln("API Key (first 8 chars): " . substr($config->api_key, 0, 8) . "...");
        cli_writeln("API Secret (length): " . strlen($config->api_secret) . " characters");
    }
} else {
    cli_problem("⚠️  Unknown gateway type: {$gateway->gateway}");
}

// Check scheduled task configuration
cli_heading('Scheduled Task Configuration', 2);

$inadvance_days = get_config('local_equipment', 'inadvance_days');
$inadvance_hours = get_config('local_equipment', 'inadvance_hours');
$reminder_timeout = get_config('local_equipment', 'reminder_timeout');

cli_writeln('Days reminder: ' . ($inadvance_days ?: 'Not configured'));
cli_writeln('Hours reminder: ' . ($inadvance_hours ?: 'Not configured'));
cli_writeln('Reminder timeout: ' . ($reminder_timeout ?: 'Not configured (default: 24 hours)'));

// Check for users who might need reminders
cli_heading('Pending Reminders', 2);

if (!empty($inadvance_days) || !empty($inadvance_hours)) {
    $manager = new \local_equipment\exchange_manager();
    $now = time();

    if (!empty($inadvance_days)) {
        $window_end = $now + ($inadvance_days * DAYSECS);
        $users_days = $manager->get_users_needing_reminders($now, $window_end, 'days');
        cli_writeln("Users needing days reminders: " . count($users_days));

        if ($options['verbose'] && !empty($users_days)) {
            foreach (array_slice($users_days, 0, 5) as $user) {
                cli_writeln("  - User {$user->userid}, Exchange {$user->exchangeid}");
            }
            if (count($users_days) > 5) {
                cli_writeln("  ... and " . (count($users_days) - 5) . " more");
            }
        }
    }

    if (!empty($inadvance_hours)) {
        $window_end = $now + ($inadvance_hours * HOURSECS);
        $users_hours = $manager->get_users_needing_reminders($now, $window_end, 'hours');
        cli_writeln("Users needing hours reminders: " . count($users_hours));

        if ($options['verbose'] && !empty($users_hours)) {
            foreach (array_slice($users_hours, 0, 5) as $user) {
                cli_writeln("  - User {$user->userid}, Exchange {$user->exchangeid}");
            }
            if (count($users_hours) > 5) {
                cli_writeln("  ... and " . (count($users_hours) - 5) . " more");
            }
        }
    }
} else {
    cli_writeln('No reminder timeframes configured');
}

// SMS Testing
if (!empty($options['test-phone']) || $options['send-test']) {
    cli_heading('SMS Testing', 2);

    if (empty($options['test-phone'])) {
        cli_problem('❌ --test-phone required for SMS testing');
        exit(1);
    }

    $testphone = $options['test-phone'];
    cli_writeln("Test phone: {$testphone}");

    // Validate phone number
    $phoneobj = local_equipment_parse_phone_number($testphone);
    if (!empty($phoneobj->errors)) {
        cli_problem('❌ Invalid phone number: ' . implode(', ', $phoneobj->errors));
        exit(1);
    }

    cli_writeln("✅ Phone number validated: {$phoneobj->phone}");

    if ($options['send-test']) {
        cli_writeln('📱 Sending test SMS...');

        $testmessage = "Test message from {$SITE->shortname} Equipment Plugin Debug Tool at " . userdate(time());

        $response = local_equipment_send_sms($gatewayid, $phoneobj->phone, $testmessage, 'Transactional');

        if ($options['verbose']) {
            cli_heading('SMS Response Details', 3);
            cli_writeln(print_r($response, true));
        }

        if (is_object($response) && isset($response->success) && $response->success) {
            cli_writeln('✅ SMS sent successfully!');
            if (isset($response->messageid)) {
                cli_writeln("Message ID: {$response->messageid}");
            }
        } else {
            cli_problem('❌ SMS failed!');
            if (is_object($response)) {
                if (isset($response->errormessage)) {
                    cli_writeln("Error: {$response->errormessage}");
                }
                if (isset($response->errortype)) {
                    cli_writeln("Error Type: {$response->errortype}");
                }
            }
            exit(1);
        }
    } else {
        cli_writeln('Use --send-test to actually send the SMS');
    }
}

cli_writeln('');
cli_writeln('Debug complete.');
