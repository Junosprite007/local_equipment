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
 * Debug messaging service implementation (logs but doesn't send).
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
 * Debug messaging service (logs but doesn't send).
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
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
    protected function do_send(string $phonenumber, string $message, array $options = []): bool {
        // Format the debug output
        $timestamp = date('Y-m-d H:i:s');
        $separator = str_repeat('-', 50);

        $debugoutput = "\n{$separator}\n";
        $debugoutput .= "DEBUG SMS MESSAGE [{$timestamp}]\n";
        $debugoutput .= "{$separator}\n";
        $debugoutput .= "TO: {$phonenumber}\n";
        $debugoutput .= "MESSAGE: {$message}\n";
        $debugoutput .= "LENGTH: " . strlen($message) . " characters\n";

        // Add options if provided
        if (!empty($options)) {
            $debugoutput .= "OPTIONS:\n";
            foreach ($options as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $debugoutput .= "  {$key}: {$value}\n";
            }
        }

        $debugoutput .= "{$separator}\n";

        // Output to different locations based on context
        if (defined('CLI_SCRIPT') && CLI_SCRIPT) {
            // In CLI mode, output directly
            echo $debugoutput;
        } else {
            // In web mode, use debugging
            local_equipment_debug_log("DEBUG SMS:\nTO: {$phonenumber}\nMESSAGE: {$message}");
        }

        // Also log to file if debug logging is enabled
        $this->log_to_file($phonenumber, $message, $options);

        // Always return true for debug mode
        return true;
    }

    /**
     * Log debug message to file if configured.
     *
     * @param string $phonenumber Recipient phone number
     * @param string $message Message content
     * @param array $options Additional options
     */
    protected function log_to_file(string $phonenumber, string $message, array $options = []): void {
        global $CFG;

        // Check if debug file logging is enabled
        $debugfile = get_config('local_equipment', 'debug_sms_file');
        if (empty($debugfile)) {
            return;
        }

        // Ensure the debug file path is safe
        if (!is_writable(dirname($debugfile))) {
            local_equipment_debug_log("Debug file directory not writable: " . dirname($debugfile));
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $logentry = "[{$timestamp}] TO: {$phonenumber} | MESSAGE: {$message}";

        if (!empty($options)) {
            $logentry .= " | OPTIONS: " . json_encode($options);
        }

        $logentry .= "\n";

        try {
            file_put_contents($debugfile, $logentry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            local_equipment_debug_log("Failed to write to debug file: " . $e->getMessage());
        }
    }

    /**
     * Get debug statistics.
     *
     * @return array Debug information
     */
    public function get_debug_stats(): array {
        global $DB;

        $stats = [
            'total_attempts' => 0,
            'recent_attempts' => 0,
            'debug_file_configured' => false,
            'debug_file_writable' => false
        ];

        // Count total debug attempts (if events are being logged)
        try {
            $stats['total_attempts'] = $DB->count_records('logstore_standard_log', [
                'component' => 'local_equipment',
                'action' => 'debug_sms'
            ]);

            // Count recent attempts (last 24 hours)
            $yesterday = time() - DAYSECS;
            $stats['recent_attempts'] = $DB->count_records_select('logstore_standard_log',
                'component = ? AND action = ? AND timecreated > ?',
                ['local_equipment', 'debug_sms', $yesterday]
            );
        } catch (Exception $e) {
            // If there's an error accessing logs, it's not critical
            local_equipment_debug_log("Could not retrieve debug stats: " . $e->getMessage());
        }

        // Check debug file configuration
        $debugfile = get_config('local_equipment', 'debug_sms_file');
        if (!empty($debugfile)) {
            $stats['debug_file_configured'] = true;
            $stats['debug_file_writable'] = is_writable(dirname($debugfile));
        }

        return $stats;
    }

    /**
     * Validate debug configuration.
     *
     * @return array Validation results
     */
    public function validate_configuration(): array {
        $validation = [
            'debug_enabled' => true, // Always true for debug service
            'file_logging_configured' => false,
            'file_logging_writable' => false,
            'errors' => [],
            'warnings' => []
        ];

        $debugfile = get_config('local_equipment', 'debug_sms_file');

        if (!empty($debugfile)) {
            $validation['file_logging_configured'] = true;

            if (!is_writable(dirname($debugfile))) {
                $validation['errors'][] = 'Debug file directory not writable: ' . dirname($debugfile);
            } else {
                $validation['file_logging_writable'] = true;
            }
        } else {
            $validation['warnings'][] = 'Debug file logging not configured (optional)';
        }

        return $validation;
    }
}
