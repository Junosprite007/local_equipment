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
 * Storage locations management for inventory system.
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
admin_externalpage_setup('local_equipment_inventory_locations');

// Handle form submissions
$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$name = optional_param('name', '', PARAM_TEXT);
$description = optional_param('description', '', PARAM_TEXT);
$address = optional_param('address', '', PARAM_TEXT);
$zone = optional_param('zone', '', PARAM_TEXT);
$active = optional_param('active', 1, PARAM_INT);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('managelocations', 'local_equipment'));

// Handle actions
if ($action === 'add' && confirm_sesskey()) {
    if (!empty($name)) {
        $location = new stdClass();
        $location->name = $name;
        $location->description = $description;
        $location->address = $address;
        $location->zone = $zone;
        $location->active = $active;
        $location->timecreated = time();
        $location->timemodified = time();

        $DB->insert_record('local_equipment_locations', $location);
        echo html_writer::tag('div', '✓ Location added successfully!', ['class' => 'alert alert-success']);
    } else {
        echo html_writer::tag('div', '✗ Location name is required', ['class' => 'alert alert-danger']);
    }
} else if ($action === 'edit' && $id && confirm_sesskey()) {
    if (!empty($name)) {
        $location = new stdClass();
        $location->id = $id;
        $location->name = $name;
        $location->description = $description;
        $location->address = $address;
        $location->zone = $zone;
        $location->active = $active;
        $location->timemodified = time();

        $DB->update_record('local_equipment_locations', $location);
        echo html_writer::tag('div', '✓ Location updated successfully!', ['class' => 'alert alert-success']);
    } else {
        echo html_writer::tag('div', '✗ Location name is required', ['class' => 'alert alert-danger']);
    }
} else if ($action === 'delete' && $id && confirm_sesskey()) {
    // Check if location has any equipment items
    $item_count = $DB->count_records('local_equipment_items', ['locationid' => $id]);
    if ($item_count > 0) {
        echo html_writer::tag('div', "✗ Cannot delete location: {$item_count} equipment items are currently at this location", ['class' => 'alert alert-danger']);
    } else {
        $DB->delete_record('local_equipment_locations', ['id' => $id]);
        echo html_writer::tag('div', '✓ Location deleted successfully!', ['class' => 'alert alert-success']);
    }
}

// Get edit data if editing
$edit_location = null;
$edit_id = optional_param('edit', 0, PARAM_INT);
if ($edit_id) {
    $edit_location = $DB->get_record('local_equipment_locations', ['id' => $edit_id]);
}

// Add/Edit form
echo html_writer::start_tag('div', ['class' => 'card mb-4']);
echo html_writer::start_tag('div', ['class' => 'card-header']);
echo html_writer::tag('h5', $edit_location ? 'Edit Location' : 'Add New Location', ['class' => 'mb-0']);
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', ['class' => 'card-body']);
echo html_writer::start_tag('form', ['method' => 'post', 'action' => '']);

// Name field
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', 'Location Name *', ['for' => 'name', 'class' => 'form-label']);
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'id' => 'name',
    'name' => 'name',
    'value' => $edit_location ? $edit_location->name : '',
    'class' => 'form-control',
    'required' => true
]);
echo html_writer::end_div();

// Description field
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', 'Description', ['for' => 'description', 'class' => 'form-label']);
echo html_writer::tag('textarea', $edit_location ? $edit_location->description : '', [
    'id' => 'description',
    'name' => 'description',
    'class' => 'form-control',
    'rows' => 3
]);
echo html_writer::end_div();

// Address field
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', 'Address', ['for' => 'address', 'class' => 'form-label']);
echo html_writer::tag('textarea', $edit_location ? $edit_location->address : '', [
    'id' => 'address',
    'name' => 'address',
    'class' => 'form-control',
    'rows' => 2
]);
echo html_writer::end_div();

// Zone field
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', 'Zone/Area', ['for' => 'zone', 'class' => 'form-label']);
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'id' => 'zone',
    'name' => 'zone',
    'value' => $edit_location ? $edit_location->zone : '',
    'class' => 'form-control',
    'placeholder' => 'e.g., Aisle A, Top Shelf, Room 101'
]);
echo html_writer::end_div();

// Active checkbox
echo html_writer::start_div('mb-3 form-check');
echo html_writer::empty_tag('input', [
    'type' => 'checkbox',
    'id' => 'active',
    'name' => 'active',
    'value' => 1,
    'class' => 'form-check-input',
    'checked' => $edit_location ? $edit_location->active : true
]);
echo html_writer::tag('label', 'Active', ['for' => 'active', 'class' => 'form-check-label']);
echo html_writer::end_div();

// Hidden fields
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => $edit_location ? 'edit' : 'add']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
if ($edit_location) {
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $edit_location->id]);
}

// Submit button
echo html_writer::tag('button', $edit_location ? 'Update Location' : 'Add Location', [
    'type' => 'submit',
    'class' => 'btn btn-primary me-2'
]);

if ($edit_location) {
    echo html_writer::tag('a', 'Cancel', [
        'href' => new moodle_url('/local/equipment/inventory/locations.php'),
        'class' => 'btn btn-secondary'
    ]);
}

echo html_writer::end_tag('form');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

// List existing locations
echo html_writer::tag('h4', 'Existing Locations');

$locations = $DB->get_records('local_equipment_locations', null, 'name ASC');

if (empty($locations)) {
    echo html_writer::tag('p', 'No locations found. Add your first location above.', ['class' => 'text-muted']);
} else {
    echo html_writer::start_tag('div', ['class' => 'table-responsive']);
    echo html_writer::start_tag('table', ['class' => 'table table-striped']);

    // Table header
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', 'Name');
    echo html_writer::tag('th', 'Description');
    echo html_writer::tag('th', 'Address');
    echo html_writer::tag('th', 'Zone');
    echo html_writer::tag('th', 'Status');
    echo html_writer::tag('th', 'Items');
    echo html_writer::tag('th', 'Actions');
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');

    echo html_writer::start_tag('tbody');

    foreach ($locations as $location) {
        // Get item count for this location
        $item_count = $DB->count_records('local_equipment_items', ['locationid' => $location->id]);

        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', html_writer::tag('strong', s($location->name)));
        echo html_writer::tag('td', s($location->description ?: '-'));
        echo html_writer::tag('td', s($location->address ?: '-'));
        echo html_writer::tag('td', s($location->zone ?: '-'));
        echo html_writer::tag(
            'td',
            $location->active ?
                html_writer::tag('span', 'Active', ['class' => 'badge bg-success']) :
                html_writer::tag('span', 'Inactive', ['class' => 'badge bg-secondary'])
        );
        echo html_writer::tag('td', $item_count);

        // Actions
        echo html_writer::start_tag('td');
        echo html_writer::tag('a', 'Edit', [
            'href' => new moodle_url('/local/equipment/inventory/locations.php', ['edit' => $location->id]),
            'class' => 'btn btn-sm btn-outline-primary me-1'
        ]);

        if ($item_count == 0) {
            echo html_writer::tag('a', 'Delete', [
                'href' => new moodle_url('/local/equipment/inventory/locations.php', [
                    'action' => 'delete',
                    'id' => $location->id,
                    'sesskey' => sesskey()
                ]),
                'class' => 'btn btn-sm btn-outline-danger',
                'onclick' => 'return confirm("Are you sure you want to delete this location?");'
            ]);
        } else {
            echo html_writer::tag('span', 'Delete', [
                'class' => 'btn btn-sm btn-outline-secondary disabled',
                'title' => 'Cannot delete: location has equipment items'
            ]);
        }
        echo html_writer::end_tag('td');

        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
    echo html_writer::end_tag('div');
}

echo $OUTPUT->footer();
