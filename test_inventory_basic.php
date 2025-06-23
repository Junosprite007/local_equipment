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
 * Basic test page for inventory system functionality.
 *
 * @package     local_equipment
 * @copyright   2025 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
require_capability('local/equipment:manageinventory', $context);

$PAGE->set_url('/local/equipment/test_inventory_basic.php');
$PAGE->set_context($context);
$PAGE->set_title('Basic Inventory Test');
$PAGE->set_heading('Basic Inventory System Test');

echo $OUTPUT->header();

echo html_writer::tag('h2', 'Inventory System Basic Test');

// Test database tables exist
echo html_writer::tag('h3', 'Database Tables Test');

$tables_to_check = [
    'local_equipment_products',
    'local_equipment_locations',
    'local_equipment_items',
    'local_equipment_uuid_history',
    'local_equipment_configurations',
    'local_equipment_config_products',
    'local_equipment_course_configs',
    'local_equipment_transactions',
    'local_equipment_allocations'
];

foreach ($tables_to_check as $table) {
    if ($DB->get_manager()->table_exists($table)) {
        echo html_writer::tag('p', "✓ Table {$table} exists", ['style' => 'color: green;']);
    } else {
        echo html_writer::tag('p', "✗ Table {$table} missing", ['style' => 'color: red;']);
    }
}

// Test class loading
echo html_writer::tag('h3', 'Class Loading Test');

try {
    $inventory_manager = new \local_equipment\inventory\inventory_manager();
    echo html_writer::tag('p', '✓ inventory_manager class loaded successfully', ['style' => 'color: green;']);
} catch (Exception $e) {
    echo html_writer::tag('p', '✗ inventory_manager class failed to load: ' . $e->getMessage(), ['style' => 'color: red;']);
}

try {
    $qr_generator = new \local_equipment\inventory\qr_generator();
    echo html_writer::tag('p', '✓ qr_generator class loaded successfully', ['style' => 'color: green;']);
} catch (Exception $e) {
    echo html_writer::tag('p', '✗ qr_generator class failed to load: ' . $e->getMessage(), ['style' => 'color: red;']);
}

// Test basic database operations
echo html_writer::tag('h3', 'Basic Database Operations Test');

try {
    // Test creating a sample location
    $location_data = (object)[
        'name' => 'Test Location',
        'description' => 'Test location for inventory system',
        'zone' => 'Test Zone',
        'active' => 1,
        'timecreated' => time(),
        'timemodified' => time()
    ];

    $location_id = $DB->insert_record('local_equipment_locations', $location_data);
    echo html_writer::tag('p', "✓ Created test location with ID: {$location_id}", ['style' => 'color: green;']);

    // Test creating a sample product
    $product_data = (object)[
        'name' => 'Test Product',
        'description' => 'Test product for inventory system',
        'manufacturer' => 'Test Manufacturer',
        'model' => 'TEST-001',
        'category' => 'test',
        'is_consumable' => 0,
        'active' => 1,
        'timecreated' => time(),
        'timemodified' => time()
    ];

    $product_id = $DB->insert_record('local_equipment_products', $product_data);
    echo html_writer::tag('p', "✓ Created test product with ID: {$product_id}", ['style' => 'color: green;']);

    // Test creating a sample equipment item
    $item_data = (object)[
        'uuid' => \local_equipment\inventory\qr_generator::generate_uuid(),
        'productid' => $product_id,
        'locationid' => $location_id,
        'status' => 'available',
        'condition_status' => 'good',
        'timecreated' => time(),
        'timemodified' => time()
    ];

    $item_id = $DB->insert_record('local_equipment_items', $item_data);
    echo html_writer::tag('p', "✓ Created test equipment item with ID: {$item_id}", ['style' => 'color: green;']);

    // Clean up test data
    $DB->delete_records('local_equipment_items', ['id' => $item_id]);
    $DB->delete_records('local_equipment_products', ['id' => $product_id]);
    $DB->delete_records('local_equipment_locations', ['id' => $location_id]);

    echo html_writer::tag('p', '✓ Test data cleaned up successfully', ['style' => 'color: green;']);
} catch (Exception $e) {
    echo html_writer::tag('p', '✗ Database operations failed: ' . $e->getMessage(), ['style' => 'color: red;']);
}

// Test navigation links
echo html_writer::tag('h3', 'Navigation Links Test');

$nav_links = [
    '/local/equipment/inventory/manage.php' => 'Inventory Dashboard',
    '/local/equipment/inventory/products.php' => 'Product Management',
    '/local/equipment/inventory/generate_qr.php' => 'QR Code Generator',
    '/local/equipment/inventory/checkin.php' => 'Check-in Interface'
];

foreach ($nav_links as $url => $title) {
    echo html_writer::link(new moodle_url($url), $title, ['target' => '_blank']);
    echo html_writer::tag('br', '');
}

echo html_writer::tag('h3', 'Test Complete');
echo html_writer::tag('p', 'Basic inventory system test completed. Check the results above for any issues.');

echo $OUTPUT->footer();
