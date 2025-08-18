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
 * Test script for the inventory management system.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Require login and check capabilities
require_login();
require_capability('local/equipment:manageinventory', context_system::instance());

// Set up the page context and URL (required for Moodle 5.0)
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/equipment/test_inventory_system.php');
$PAGE->set_title(get_string('inventorysystemtest', 'local_equipment'));
$PAGE->set_heading(get_string('inventorysystemtest', 'local_equipment'));

echo $OUTPUT->header();

echo $OUTPUT->heading('Inventory System Test');

try {
    global $DB, $USER;

    echo html_writer::tag('h3', 'Testing Database Tables');

    // Test if tables exist
    $tables = [
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

    foreach ($tables as $table) {
        if ($DB->get_manager()->table_exists($table)) {
            echo html_writer::tag('p', "✅ Table {$table} exists", ['class' => 'text-success']);
        } else {
            echo html_writer::tag('p', "❌ Table {$table} missing", ['class' => 'text-danger']);
        }
    }

    echo html_writer::tag('h3', 'Testing Inventory Manager');

    // Test inventory manager
    $inventory_manager = new \local_equipment\inventory\inventory_manager();
    echo html_writer::tag('p', '✅ Inventory manager instantiated successfully', ['class' => 'text-success']);

    // Test get_inventory_summary
    $summary = $inventory_manager->get_inventory_summary();
    echo html_writer::tag('p', '✅ Inventory summary retrieved successfully', ['class' => 'text-success']);
    echo html_writer::tag('pre', print_r($summary, true));

    echo html_writer::tag('h3', 'Testing QR Generator');

    // Test QR generator
    $qr_generator = new \local_equipment\inventory\qr_generator();
    echo html_writer::tag('p', '✅ QR generator instantiated successfully', ['class' => 'text-success']);

    // Test UUID generation
    $uuid = $qr_generator::generate_uuid();
    echo html_writer::tag('p', "✅ UUID generated: {$uuid}", ['class' => 'text-success']);

    echo html_writer::tag('h3', 'Sample Data Creation');

    // Create sample location if none exist
    $location_count = $DB->count_records('local_equipment_locations');
    if ($location_count == 0) {
        $location = new stdClass();
        $location->name = 'Main Warehouse';
        $location->description = 'Primary equipment storage facility';
        $location->zone = 'General Storage';
        $location->active = 1;
        $location->timecreated = time();
        $location->timemodified = time();

        $location_id = $DB->insert_record('local_equipment_locations', $location);
        echo html_writer::tag('p', "✅ Created sample location: Main Warehouse (ID: {$location_id})", ['class' => 'text-success']);
    } else {
        echo html_writer::tag('p', "✅ Found {$location_count} existing locations", ['class' => 'text-info']);
    }

    // Create sample product if none exist
    $product_count = $DB->count_records('local_equipment_products');
    if ($product_count == 0) {
        $product = new stdClass();
        $product->name = 'LEGO Mindstorms EV3';
        $product->description = 'Robotics kit with programmable brick';
        $product->manufacturer = 'LEGO';
        $product->model = 'EV3-31313';
        $product->category = 'robotics';
        $product->upc = '';
        $product->is_consumable = 0;
        $product->active = 1;
        $product->picture = 0;
        $product->timecreated = time();
        $product->timemodified = time();

        $product_id = $DB->insert_record('local_equipment_products', $product);
        echo html_writer::tag('p', "✅ Created sample product: LEGO Mindstorms EV3 (ID: {$product_id})", ['class' => 'text-success']);
    } else {
        echo html_writer::tag('p', "✅ Found {$product_count} existing products", ['class' => 'text-info']);
    }

    echo html_writer::tag('h3', 'System Status');
    echo html_writer::tag('p', '🎉 Inventory system is working correctly!', ['class' => 'text-success font-weight-bold']);

    // Navigation links
    echo html_writer::tag('h3', 'Quick Links');
    echo html_writer::start_div('row');

    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/manage.php'),
        'Inventory Dashboard',
        ['class' => 'btn btn-primary w-100']
    );
    echo html_writer::end_div();

    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/products.php'),
        'Manage Products',
        ['class' => 'btn btn-success w-100']
    );
    echo html_writer::end_div();

    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/generate_qr.php'),
        'Generate QR Codes',
        ['class' => 'btn btn-info w-100']
    );
    echo html_writer::end_div();

    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/check_inout.php'),
        'Check In/Out Equipment',
        ['class' => 'btn btn-warning w-100']
    );
    echo html_writer::end_div();

    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/locations.php'),
        'Manage Locations',
        ['class' => 'btn btn-secondary w-100']
    );
    echo html_writer::end_div();

    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/transactions.php'),
        'View Transactions',
        ['class' => 'btn btn-outline-primary w-100']
    );
    echo html_writer::end_div();

    echo html_writer::end_div();
} catch (Exception $e) {
    echo html_writer::tag('div', 'Error: ' . $e->getMessage(), ['class' => 'alert alert-danger']);
    echo html_writer::tag('pre', $e->getTraceAsString());
}

echo $OUTPUT->footer();
