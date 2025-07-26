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
 * AJAX endpoint for getting QR code print queue count.
 *
 * @package     local_equipment
 * @copyright   2025 Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');

// Set CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set JSON content type
header('Content-Type: application/json');

// Require login and check capabilities
require_login();
require_capability('local/equipment:checkinout', context_system::instance());

try {
    // Get queue count using the print queue manager
    $print_manager = new \local_equipment\inventory\print_queue_manager();
    $queue_count = $print_manager->get_queue_count();

    echo json_encode([
        'success' => true,
        'count' => $queue_count
    ]);
} catch (Exception $e) {
    error_log('Queue count error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error getting queue count'
    ]);
}
