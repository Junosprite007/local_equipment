<?php
// This file is part of FLIP Plugins for Moodle
//
// Debug script to check queue database state
//
// @package     local_equipment
// @copyright   2025 Joshua Kirby <josh@funlearningcompany.com>
// @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Require login and check capabilities
require_login();
require_capability('local/equipment:manageinventory', context_system::instance());

echo "<!DOCTYPE html>\n<html>\n<head>\n<title>Queue Database Debug</title>\n</head>\n<body>\n";
echo "<h1>Queue Database Debug</h1>\n";

echo "<h2>1. Checking Database Tables</h2>\n";

// Check if queue table exists
try {
    $queue_exists = $DB->get_manager()->table_exists('local_equipment_qr_print_queue');
    echo "<p><strong>Queue table exists:</strong> " . ($queue_exists ? "✓ YES" : "✗ NO") . "</p>\n";
} catch (Exception $e) {
    echo "<p><strong>Error checking queue table:</strong> " . $e->getMessage() . "</p>\n";
}

// Check if items table exists
try {
    $items_exists = $DB->get_manager()->table_exists('local_equipment_items');
    echo "<p><strong>Items table exists:</strong> " . ($items_exists ? "✓ YES" : "✗ NO") . "</p>\n";
} catch (Exception $e) {
    echo "<p><strong>Error checking items table:</strong> " . $e->getMessage() . "</p>\n";
}

// Check if products table exists
try {
    $products_exists = $DB->get_manager()->table_exists('local_equipment_products');
    echo "<p><strong>Products table exists:</strong> " . ($products_exists ? "✓ YES" : "✗ NO") . "</p>\n";
} catch (Exception $e) {
    echo "<p><strong>Error checking products table:</strong> " . $e->getMessage() . "</p>\n";
}

echo "<h2>2. Queue Records Count</h2>\n";

// Test queue count method
try {
    $print_manager = new \local_equipment\inventory\print_queue_manager();
    $queue_count = $print_manager->get_queue_count();
    echo "<p><strong>Queue count:</strong> $queue_count</p>\n";
} catch (Exception $e) {
    echo "<p><strong>Error getting queue count:</strong> " . $e->getMessage() . "</p>\n";
}

// Test raw queue count
try {
    $raw_count = $DB->count_records('local_equipment_qr_print_queue', ['printed_time' => null]);
    echo "<p><strong>Raw queue count (unprinted):</strong> $raw_count</p>\n";
} catch (Exception $e) {
    echo "<p><strong>Error getting raw queue count:</strong> " . $e->getMessage() . "</p>\n";
}

// Test total queue count
try {
    $total_count = $DB->count_records('local_equipment_qr_print_queue');
    echo "<p><strong>Total queue records:</strong> $total_count</p>\n";
} catch (Exception $e) {
    echo "<p><strong>Error getting total queue count:</strong> " . $e->getMessage() . "</p>\n";
}

echo "<h2>3. Queue Records Details</h2>\n";

// Show all queue records
try {
    $queue_records = $DB->get_records('local_equipment_qr_print_queue', null, 'id ASC');
    if (empty($queue_records)) {
        echo "<p><strong>No queue records found</strong></p>\n";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>\n";
        echo "<tr><th>ID</th><th>Item ID</th><th>UUID</th><th>Queued By</th><th>Queued Time</th><th>Printed Time</th></tr>\n";
        foreach ($queue_records as $record) {
            echo "<tr>";
            echo "<td>" . $record->id . "</td>";
            echo "<td>" . $record->itemid . "</td>";
            echo "<td>" . htmlspecialchars($record->uuid) . "</td>";
            echo "<td>" . $record->queued_by . "</td>";
            echo "<td>" . date('Y-m-d H:i:s', $record->queued_time) . "</td>";
            echo "<td>" . ($record->printed_time ? date('Y-m-d H:i:s', $record->printed_time) : 'NULL') . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
} catch (Exception $e) {
    echo "<p><strong>Error getting queue records:</strong> " . $e->getMessage() . "</p>\n";
}

echo "<h2>4. Testing Queue Join Query</h2>\n";

// Test the actual join query
try {
    $print_manager = new \local_equipment\inventory\print_queue_manager();
    $queue_items = $print_manager->get_unprinted_queue();
    echo "<p><strong>Queue items returned:</strong> " . count($queue_items) . "</p>\n";

    if (!empty($queue_items)) {
        echo "<table border='1' style='border-collapse: collapse;'>\n";
        echo "<tr><th>Queue ID</th><th>Item ID</th><th>UUID</th><th>Product Name</th><th>Manufacturer</th></tr>\n";
        foreach ($queue_items as $item) {
            echo "<tr>";
            echo "<td>" . $item->id . "</td>";
            echo "<td>" . $item->itemid . "</td>";
            echo "<td>" . htmlspecialchars($item->uuid) . "</td>";
            echo "<td>" . htmlspecialchars($item->product_name ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($item->manufacturer ?? 'NULL') . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
} catch (Exception $e) {
    echo "<p><strong>Error getting queue items:</strong> " . $e->getMessage() . "</p>\n";
    echo "<p><strong>SQL Error Details:</strong> " . $e->getTraceAsString() . "</p>\n";
}

echo "<h2>5. Testing Join Query Components</h2>\n";

// Test each join component separately
try {
    // Test items table
    $items_count = $DB->count_records('local_equipment_items');
    echo "<p><strong>Total items in inventory:</strong> $items_count</p>\n";

    // Test products table
    $products_count = $DB->count_records('local_equipment_products');
    echo "<p><strong>Total products defined:</strong> $products_count</p>\n";

    // Test if any queue items reference valid item IDs
    if ($queue_exists) {
        $sql = "SELECT q.*, i.id as valid_item
                FROM {local_equipment_qr_print_queue} q
                LEFT JOIN {local_equipment_items} i ON q.itemid = i.id
                WHERE q.printed_time IS NULL";

        $test_results = $DB->get_records_sql($sql);
        echo "<p><strong>Queue items with item check:</strong> " . count($test_results) . "</p>\n";

        foreach ($test_results as $result) {
            $status = $result->valid_item ? "✓ Valid" : "✗ Invalid (item deleted)";
            echo "<p>Queue ID {$result->id}, Item ID {$result->itemid}: $status</p>\n";
        }
    }
} catch (Exception $e) {
    echo "<p><strong>Error testing join components:</strong> " . $e->getMessage() . "</p>\n";
}

echo "<h2>6. Manual SQL Test</h2>\n";

// Try the exact SQL from the method
try {
    $sql = "SELECT q.*, i.uuid as current_uuid, p.name as product_name, p.manufacturer
            FROM {local_equipment_qr_print_queue} q
            JOIN {local_equipment_items} i ON q.itemid = i.id
            JOIN {local_equipment_products} p ON i.productid = p.id
            WHERE q.printed_time IS NULL
            ORDER BY q.queued_time ASC";

    echo "<p><strong>Testing exact SQL query...</strong></p>\n";
    echo "<pre>" . htmlspecialchars($sql) . "</pre>\n";

    $manual_results = $DB->get_records_sql($sql);
    echo "<p><strong>Manual SQL results:</strong> " . count($manual_results) . " records</p>\n";
} catch (Exception $e) {
    echo "<p><strong>Manual SQL Error:</strong> " . $e->getMessage() . "</p>\n";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>\n";
}

echo "</body>\n</html>\n";
