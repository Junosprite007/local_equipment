<?php
define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');

echo "=== PRINT QUEUE DIAGNOSIS ===\n";

// Check if print queue table exists and has data
try {
    $queue_count = $DB->count_records('local_equipment_qr_print_queue');
    echo "Print queue table exists with $queue_count total records\n";

    $unprinted_count = $DB->count_records('local_equipment_qr_print_queue', ['printed_time' => null]);
    echo "Unprinted queue items: $unprinted_count\n";

    // Get the actual queue records
    $queue_records = $DB->get_records('local_equipment_qr_print_queue', ['printed_time' => null]);

    if (!empty($queue_records)) {
        echo "\nQueue records:\n";
        foreach ($queue_records as $record) {
            echo "ID: $record->id, ItemID: $record->itemid, UUID: $record->uuid\n";

            // Check if the item exists
            $item_exists = $DB->record_exists('local_equipment_items', ['id' => $record->itemid]);
            echo "  Item exists: " . ($item_exists ? 'YES' : 'NO') . "\n";

            if ($item_exists) {
                $item = $DB->get_record('local_equipment_items', ['id' => $record->itemid]);
                $product_exists = $DB->record_exists('local_equipment_products', ['id' => $item->productid]);
                echo "  Product exists: " . ($product_exists ? 'YES' : 'NO') . "\n";

                if ($product_exists) {
                    $product = $DB->get_record('local_equipment_products', ['id' => $item->productid]);
                    echo "  Product name: $product->name\n";
                } else {
                    echo "  Missing product ID: $item->productid\n";
                }
            } else {
                echo "  Missing item ID: $record->itemid\n";
            }
        }
    }

    // Test the complex JOIN query manually
    echo "\n=== TESTING JOIN QUERY ===\n";
    $sql = "SELECT q.*, i.uuid as current_uuid, p.name as product_name, p.manufacturer
            FROM {local_equipment_qr_print_queue} q
            JOIN {local_equipment_items} i ON q.itemid = i.id
            JOIN {local_equipment_products} p ON i.productid = p.id
            WHERE q.printed_time IS NULL
            ORDER BY q.queued_time ASC";

    $join_results = $DB->get_records_sql($sql);
    echo "JOIN query returned: " . count($join_results) . " records\n";

    if (empty($join_results) && !empty($queue_records)) {
        echo "JOIN FAILURE: Queue has records but JOIN returns none!\n";
    }
    // Test the print_queue_manager class directly
    echo "\n=== TESTING PRINT_QUEUE_MANAGER CLASS ===\n";
    $print_manager = new \local_equipment\inventory\print_queue_manager();
    $class_results = $print_manager->get_unprinted_queue();
    echo "Print manager class returned: " . count($class_results) . " records\n";

    if (empty($class_results)) {
        echo "CLASS FAILURE: Print manager returns empty but direct query works!\n";

        // Debug the actual SQL used by the class
        echo "Checking the exact SQL used by the class...\n";

        // Get the SQL from the class method
        global $DB;
        $debug_sql = "SELECT q.*, i.uuid as current_uuid, p.name as product_name, p.manufacturer
                      FROM {local_equipment_qr_print_queue} q
                      JOIN {local_equipment_items} i ON q.itemid = i.id
                      JOIN {local_equipment_products} p ON i.productid = p.id
                      WHERE q.printed_time IS NULL
                      ORDER BY q.queued_time ASC";

        $debug_results = $DB->get_records_sql($debug_sql);
        echo "Direct debug SQL returned: " . count($debug_results) . " records\n";

        if (!empty($debug_results)) {
            foreach ($debug_results as $result) {
                echo "Debug record: ID={$result->id}, UUID={$result->current_uuid}, Product={$result->product_name}\n";
            }
        }
    } else {
        echo "SUCCESS: Print manager class working correctly!\n";
        foreach ($class_results as $result) {
            echo "Class record: ID={$result->id}, UUID={$result->current_uuid}, Product={$result->product_name}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
