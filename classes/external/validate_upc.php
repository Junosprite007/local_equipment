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
 * AJAX endpoint for UPC validation and item creation.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');

// Set JSON content type
header('Content-Type: application/json');

// Require login and check capabilities
require_login();
require_capability('local/equipment:checkinout', context_system::instance());

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

// Validate session key
if (!confirm_sesskey($input['sesskey'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid session key']);
    exit;
}

// Get parameters
$upc = trim($input['upc'] ?? '');
$locationid = intval($input['locationid'] ?? 0);

if (empty($upc)) {
    echo json_encode(['success' => false, 'message' => 'UPC code is required']);
    exit;
}

if (!$locationid) {
    echo json_encode(['success' => false, 'message' => 'Location ID is required']);
    exit;
}

// Verify location exists and is active
$location = $DB->get_record('local_equipment_locations', ['id' => $locationid, 'active' => 1]);
if (!$location) {
    echo json_encode(['success' => false, 'message' => 'Invalid or inactive location']);
    exit;
}

try {
    // Look for product with this UPC
    $product = $DB->get_record('local_equipment_products', ['upc' => $upc, 'active' => 1]);

    if (!$product) {
        // Product not found - suggest adding it
        $product_url = new moodle_url('/local/equipment/inventory/products.php', [
            'action' => 'add',
            'upc' => $upc
        ]);

        echo json_encode([
            'success' => false,
            'message' => 'Product with UPC "' . s($upc) . '" not found in system.',
            'product_url' => $product_url->out()
        ]);
        exit;
    }

    // Create new equipment item
    $qr_generator = new \local_equipment\inventory\qr_generator();
    $uuid = $qr_generator->generate_uuid();

    $item = new stdClass();
    $item->uuid = $uuid;
    $item->productid = $product->id;
    $item->locationid = $locationid;
    $item->status = 'available';
    $item->condition_status = 'good';
    $item->timecreated = time();
    $item->timemodified = time();

    $itemid = $DB->insert_record('local_equipment_items', $item);

    // Add UUID to history
    $uuid_history = new stdClass();
    $uuid_history->itemid = $itemid;
    $uuid_history->uuid = $uuid;
    $uuid_history->is_active = 1;
    $uuid_history->created_by = $USER->id;
    $uuid_history->timecreated = time();

    $DB->insert_record('local_equipment_uuid_history', $uuid_history);

    // Log transaction
    $transaction = new stdClass();
    $transaction->itemid = $itemid;
    $transaction->transaction_type = 'checkin';
    $transaction->to_locationid = $locationid;
    $transaction->processed_by = $USER->id;
    $transaction->notes = 'Item added to inventory via UPC scan: ' . $upc;
    $transaction->condition_after = 'good';
    $transaction->timestamp = time();

    $DB->insert_record('local_equipment_transactions', $transaction);

    // Return success
    echo json_encode([
        'success' => true,
        'itemid' => $itemid,
        'product_name' => $product->name,
        'uuid' => $uuid
    ]);
} catch (Exception $e) {
    error_log('UPC validation error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
}
