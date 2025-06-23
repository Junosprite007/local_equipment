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
 * Main inventory management dashboard.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Require login and check capabilities
require_login();
require_capability('local/equipment:manageinventory', context_system::instance());

// Set up admin external page
admin_externalpage_setup('local_equipment_inventory_manage');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('inventorymanagement', 'local_equipment'));

// Create inventory manager instance
try {
    $inventory_manager = new \local_equipment\inventory\inventory_manager();

    // Get inventory summary
    $summary = $inventory_manager->get_inventory_summary();

    // Display summary cards
    echo html_writer::start_div('row');

    // Total items card
    echo html_writer::start_div('col-md-3 mb-3');
    echo html_writer::start_div('card text-white bg-primary');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h5', get_string('totalitems', 'local_equipment'), ['class' => 'card-title']);
    echo html_writer::tag('h2', $summary->total_items, ['class' => 'card-text']);
    echo html_writer::end_div(); // card-body
    echo html_writer::end_div(); // card
    echo html_writer::end_div(); // col

    // Available items card
    echo html_writer::start_div('col-md-3 mb-3');
    echo html_writer::start_div('card text-white bg-success');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h5', get_string('availableitems', 'local_equipment'), ['class' => 'card-title']);
    echo html_writer::tag('h2', $summary->available, ['class' => 'card-text']);
    echo html_writer::end_div(); // card-body
    echo html_writer::end_div(); // card
    echo html_writer::end_div(); // col

    // Checked out items card
    echo html_writer::start_div('col-md-3 mb-3');
    echo html_writer::start_div('card text-white bg-warning');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h5', get_string('checkedoutitems', 'local_equipment'), ['class' => 'card-title']);
    echo html_writer::tag('h2', $summary->checked_out, ['class' => 'card-text']);
    echo html_writer::end_div(); // card-body
    echo html_writer::end_div(); // card
    echo html_writer::end_div(); // col

    // In transit items card
    echo html_writer::start_div('col-md-3 mb-3');
    echo html_writer::start_div('card text-white bg-info');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h5', get_string('intransititems', 'local_equipment'), ['class' => 'card-title']);
    echo html_writer::tag('h2', $summary->in_transit, ['class' => 'card-text']);
    echo html_writer::end_div(); // card-body
    echo html_writer::end_div(); // card
    echo html_writer::end_div(); // col

    echo html_writer::end_div(); // row

    // Second row for additional actions
    echo html_writer::start_div('row');

    // Add Items
    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::start_div('card');
    echo html_writer::start_div('card-body text-center');
    echo html_writer::tag('h5', get_string('additems', 'local_equipment'), ['class' => 'card-title']);
    echo html_writer::tag('p', 'Add new equipment items to inventory', ['class' => 'card-text']);
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/add_items.php'),
        get_string('additems', 'local_equipment'),
        ['class' => 'btn btn-success']
    );
    echo html_writer::end_div(); // card-body
    echo html_writer::end_div(); // card
    echo html_writer::end_div(); // col

    // Remove Items
    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::start_div('card');
    echo html_writer::start_div('card-body text-center');
    echo html_writer::tag('h5', get_string('removeitems', 'local_equipment'), ['class' => 'card-title']);
    echo html_writer::tag('p', 'Remove equipment items from inventory', ['class' => 'card-text']);
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/remove_items.php'),
        get_string('removeitems', 'local_equipment'),
        ['class' => 'btn btn-danger']
    );
    echo html_writer::end_div(); // card-body
    echo html_writer::end_div(); // card
    echo html_writer::end_div(); // col

    // Manage Locations
    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::start_div('card');
    echo html_writer::start_div('card-body text-center');
    echo html_writer::tag('h5', get_string('managelocations', 'local_equipment'), ['class' => 'card-title']);
    echo html_writer::tag('p', 'Add and manage storage locations', ['class' => 'card-text']);
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/locations.php'),
        get_string('managelocations', 'local_equipment'),
        ['class' => 'btn btn-info']
    );
    echo html_writer::end_div(); // card-body
    echo html_writer::end_div(); // card
    echo html_writer::end_div(); // col

    echo html_writer::end_div(); // row

    // Quick actions section
    echo html_writer::tag('h3', get_string('actions', 'local_equipment'), ['class' => 'mt-4']);

    echo html_writer::start_div('row');

    // QR Code Generator
    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::start_div('card');
    echo html_writer::start_div('card-body text-center');
    echo html_writer::tag('h5', get_string('generateqr', 'local_equipment'), ['class' => 'card-title']);
    echo html_writer::tag('p', 'Generate QR codes for equipment tracking', ['class' => 'card-text']);
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/generate_qr.php'),
        get_string('generateqrcodes', 'local_equipment'),
        ['class' => 'btn btn-primary']
    );
    echo html_writer::end_div(); // card-body
    echo html_writer::end_div(); // card
    echo html_writer::end_div(); // col

    // Check In/Out
    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::start_div('card');
    echo html_writer::start_div('card-body text-center');
    echo html_writer::tag('h5', get_string('checkinout', 'local_equipment'), ['class' => 'card-title']);
    echo html_writer::tag('p', 'Check equipment in or out using QR codes', ['class' => 'card-text']);
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/checkin.php'),
        get_string('checkinout', 'local_equipment'),
        ['class' => 'btn btn-primary']
    );
    echo html_writer::end_div(); // card-body
    echo html_writer::end_div(); // card
    echo html_writer::end_div(); // col

    // Manage Products
    echo html_writer::start_div('col-md-4 mb-3');
    echo html_writer::start_div('card');
    echo html_writer::start_div('card-body text-center');
    echo html_writer::tag('h5', get_string('manageproducts', 'local_equipment'), ['class' => 'card-title']);
    echo html_writer::tag('p', 'Add and manage product catalog', ['class' => 'card-text']);
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/products.php'),
        get_string('manageproducts', 'local_equipment'),
        ['class' => 'btn btn-primary']
    );
    echo html_writer::end_div(); // card-body
    echo html_writer::end_div(); // card
    echo html_writer::end_div(); // col

    echo html_writer::end_div(); // row

} catch (Exception $e) {
    echo $OUTPUT->notification('Error loading inventory data: ' . $e->getMessage(), 'error');
}

echo $OUTPUT->footer();
