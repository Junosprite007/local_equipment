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

/**
 * Normalize UPC code to standard 12-digit format.
 *
 * @param string $upc Raw UPC code from scanner
 * @return string|false Normalized 12-digit UPC or false if invalid
 */
function normalize_upc($upc) {
    // Remove any non-numeric characters
    $upc = preg_replace('/[^0-9]/', '', $upc);

    // Check if it's a valid length (8-14 digits)
    $length = strlen($upc);
    if ($length < 8 || $length > 14) {
        return false;
    }

    // Handle different UPC formats
    switch ($length) {
        case 8:
            // UPC-E format - expand to UPC-A
            // This is a simplified expansion - real UPC-E is more complex
            // For now, just pad with zeros to make 12 digits
            return str_pad($upc, 12, '0', STR_PAD_LEFT);

        case 12:
            // UPC-A format - this is our target format
            return $upc;

        case 13:
            // EAN-13 format - remove leading zero if it's 0
            if (substr($upc, 0, 1) === '0') {
                return substr($upc, 1);
            }
            // If it doesn't start with 0, it's a non-US EAN code
            // Store as-is but log it
            error_log("Non-US EAN-13 code detected: $upc");
            return $upc;

        case 14:
            // GTIN-14 format - remove leading zeros
            return ltrim($upc, '0');

        default:
            // 9, 10, 11 digits - pad to 12
            return str_pad($upc, 12, '0', STR_PAD_LEFT);
    }
}

// Set CORS headers for cross-origin requests (needed for IP access)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set JSON content type
header('Content-Type: application/json');

// Add debug logging for network issues
error_log('UPC validation request from: ' . $_SERVER['HTTP_HOST'] . ' - Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? 'none'));
error_log('Request method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Content type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'none'));

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

// Normalize UPC code to standard 12-digit format
$original_upc = $upc;
$normalized_upc = normalize_upc($upc);

if (!$normalized_upc) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid UPC format: "' . s($original_upc) . '". UPC must be 8-14 digits.'
    ]);
    exit;
}

// Log UPC normalization for debugging
error_log("UPC normalization: '$original_upc' -> '$normalized_upc'");
$upc = $normalized_upc;

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

    // Automatically add new item to print queue
    $print_manager = new \local_equipment\inventory\print_queue_manager();
    $queue_success = $print_manager->add_item_to_queue(
        $itemid,
        $uuid,
        $USER->id,
        'Auto-queued when added via UPC scan: ' . $upc
    );

    // Return success
    echo json_encode([
        'success' => true,
        'itemid' => $itemid,
        'product_name' => $product->name,
        'uuid' => $uuid,
        'queued_for_printing' => $queue_success
    ]);
} catch (Exception $e) {
    error_log('UPC validation error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
}
