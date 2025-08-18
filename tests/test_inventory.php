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
 * Test script for inventory management classes.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

// Require login and check capabilities
require_login();
require_capability('local/equipment:manageinventory', context_system::instance());

$PAGE->set_url('/local/equipment/test_inventory.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Test Inventory System');
$PAGE->set_heading('Test Inventory System');

echo $OUTPUT->header();

echo $OUTPUT->heading('Testing Inventory Management Classes');

try {
    // Test inventory manager
    echo html_writer::tag('h3', 'Testing Inventory Manager');
    $inventory_manager = new \local_equipment\inventory\inventory_manager();
    echo html_writer::tag('p', '✓ Inventory Manager class loaded successfully', ['class' => 'alert alert-success']);

    // Test QR generator
    echo html_writer::tag('h3', 'Testing QR Generator');
    $qr_generator = new \local_equipment\inventory\qr_generator();
    echo html_writer::tag('p', '✓ QR Generator class loaded successfully', ['class' => 'alert alert-success']);

    // Test QR code generation
    echo html_writer::tag('h3', 'Testing QR Code Generation');
    $test_uuid = '550e8400-e29b-41d4-a716-446655440000';
    $qr_data = $qr_generator->generate_item_qr($test_uuid, 150);

    if ($qr_data) {
        echo html_writer::tag('p', '✓ QR Code generated successfully', ['class' => 'alert alert-success']);
        echo html_writer::tag('p', 'QR Code for UUID: ' . $test_uuid);
        echo html_writer::empty_tag('img', [
            'src' => 'data:image/png;base64,' . $qr_data,
            'alt' => 'Test QR Code',
            'style' => 'border: 1px solid #ccc; padding: 10px;'
        ]);
    } else {
        echo html_writer::tag('p', '✗ QR Code generation failed', ['class' => 'alert alert-danger']);
    }

    // Test inventory summary
    echo html_writer::tag('h3', 'Testing Inventory Summary');
    $summary = $inventory_manager->get_inventory_summary();
    echo html_writer::tag('p', '✓ Inventory summary retrieved successfully', ['class' => 'alert alert-success']);
    echo html_writer::tag('pre', print_r($summary, true));
} catch (Exception $e) {
    echo html_writer::tag('p', '✗ Error: ' . $e->getMessage(), ['class' => 'alert alert-danger']);
    echo html_writer::tag('pre', $e->getTraceAsString());
}

echo $OUTPUT->footer();
