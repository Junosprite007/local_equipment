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
 * API-based messaging service implementation.
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
 * API-based messaging service.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
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
    protected function do_send(string $phonenumber, string $message, array $options = []): bool {
        // Get API credentials from settings
        $apikey = get_config('local_equipment', 'api_key');
        $apisecret = get_config('local_equipment', 'api_secret');
        $apiurl = get_config('local_equipment', 'api_url');

        if (empty($apikey) || empty($apisecret) || empty($apiurl)) {
            local_equipment_debug_log("ERROR: Missing API credentials");
            return false;
        }

        // Prepare the API request
        $data = [
            'api_key' => $apikey,
            'api_secret' => $apisecret,
            'to' => $phonenumber,
            'text' => $message
        ];

        // Add any additional options to the request
        if (!empty($options['from'])) {
            $data['from'] = $options['from'];
        }

        if (!empty($options['message_type'])) {
            $data['type'] = $options['message_type'];
        }

        // Create HTTP context for the request
        $postdata = http_build_query($data);
        $context = stream_context_create([
            'http' => [
                'header' => [
                    "Content-type: application/x-www-form-urlencoded",
                    "Content-length: " . strlen($postdata),
                    "User-Agent: Moodle Equipment Plugin/1.0"
                ],
                'method' => 'POST',
                'content' => $postdata,
                'timeout' => 30
            ]
        ]);

        // Make the API request
        try {
            $result = file_get_contents($apiurl, false, $context);

            if ($result === false) {
                local_equipment_debug_log("ERROR: API request failed - no response");
                return false;
            }

            // Parse the response
            $response = json_decode($result, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                local_equipment_debug_log("ERROR: Invalid JSON response: " . json_last_error_msg());
                return false;
            }

            // Check for success in various response formats
            if (isset($response['success']) && $response['success']) {
                local_equipment_debug_log("API message sent successfully");
                return true;
            } elseif (isset($response['status']) && $response['status'] === 'sent') {
                local_equipment_debug_log("API message sent successfully");
                return true;
            } elseif (isset($response['error'])) {
                local_equipment_debug_log("API error: " . $response['error']);
                return false;
            } else {
                local_equipment_debug_log("API response unclear: " . print_r($response, true));
                return false;
            }

        } catch (Exception $e) {
            local_equipment_debug_log("ERROR: API request exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate API configuration.
     *
     * @return array Validation results
     */
    public function validate_configuration(): array {
        $validation = [
            'api_configured' => false,
            'errors' => [],
            'warnings' => []
        ];

        $apikey = get_config('local_equipment', 'api_key');
        $apisecret = get_config('local_equipment', 'api_secret');
        $apiurl = get_config('local_equipment', 'api_url');

        if (empty($apikey)) {
            $validation['errors'][] = 'API key not configured';
        }

        if (empty($apisecret)) {
            $validation['errors'][] = 'API secret not configured';
        }

        if (empty($apiurl)) {
            $validation['errors'][] = 'API URL not configured';
        } elseif (!filter_var($apiurl, FILTER_VALIDATE_URL)) {
            $validation['errors'][] = 'API URL is not a valid URL';
        }

        $validation['api_configured'] = empty($validation['errors']);

        return $validation;
    }
}