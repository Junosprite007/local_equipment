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
 * Mark QR codes as printed endpoint.
 *
 * @package     local_equipment
 * @copyright   2025 Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');

// Require login and check capabilities
require_login();
require_capability('local/equipment:manageinventory', context_system::instance());

// Set content type
header('Content-Type: application/json');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!$data || !isset($data['queue_ids']) || !isset($data['sesskey'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Verify session key
if (!confirm_sesskey($data['sesskey'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid session key']);
    exit;
}

$queue_ids = $data['queue_ids'];

if (!is_array($queue_ids) || empty($queue_ids)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No queue IDs provided']);
    exit;
}

try {
    // Use the print queue manager to mark items as printed
    $print_manager = new \local_equipment\inventory\print_queue_manager();

    // Validate that all IDs are integers
    $queue_ids = array_map('intval', $queue_ids);
    $queue_ids = array_filter($queue_ids, function ($id) {
        return $id > 0;
    });

    if (empty($queue_ids)) {
        throw new Exception('No valid queue IDs provided');
    }

    $success = $print_manager->mark_items_printed($queue_ids);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Items marked as printed successfully',
            'count' => count($queue_ids)
        ]);
    } else {
        throw new Exception('Failed to mark items as printed');
    }
} catch (Exception $e) {
    error_log('Mark printed error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
