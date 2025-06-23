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
 * Remove items from inventory page.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
admin_externalpage_setup('local_equipment_inventory_removeitems');

$context = context_system::instance();
require_capability('local/equipment:manageinventory', $context);

$PAGE->set_title(get_string('removeitems', 'local_equipment'));
$PAGE->set_heading(get_string('removeitemsfromInventory', 'local_equipment'));

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('removeitemsfromInventory', 'local_equipment'));

echo html_writer::link(
    new moodle_url('/local/equipment/inventory/manage.php'),
    '← ' . get_string('manageinventory', 'local_equipment'),
    ['class' => 'btn btn-secondary mb-3']
);

// Main container
echo html_writer::start_div('container-fluid');
echo html_writer::start_div('row');

// Left column - Scanner and manual entry
echo html_writer::start_div('col-md-6');
echo html_writer::tag('h3', get_string('scanequipment', 'local_equipment'));
echo html_writer::tag('p', get_string('scantoidentify', 'local_equipment'));

// QR Scanner placeholder
echo html_writer::start_div('card mb-3');
echo html_writer::start_div('card-body text-center');
echo html_writer::tag(
    'div',
    get_string('scannerinterfaceplaceholder', 'local_equipment'),
    ['class' => 'alert alert-info', 'style' => 'min-height: 200px; display: flex; align-items: center; justify-content: center;']
);
echo html_writer::end_div();
echo html_writer::end_div();

// Manual UUID entry
echo html_writer::start_div('card');
echo html_writer::start_div('card-header');
echo html_writer::tag('h5', get_string('manualentry', 'local_equipment'), ['class' => 'mb-0']);
echo html_writer::end_div();
echo html_writer::start_div('card-body');

echo html_writer::start_tag('form', ['method' => 'post', 'class' => 'mb-3']);
echo html_writer::start_div('input-group');
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'uuid',
    'class' => 'form-control',
    'placeholder' => get_string('equipmentuuid', 'local_equipment'),
    'required' => true
]);
echo html_writer::start_div('input-group-append');
echo html_writer::tag('button', get_string('lookup', 'local_equipment'), [
    'type' => 'submit',
    'name' => 'lookup',
    'class' => 'btn btn-primary'
]);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_tag('form');

echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // End left column

// Right column - Equipment details and removal form
echo html_writer::start_div('col-md-6');

// Handle form submissions
$uuid = optional_param('uuid', '', PARAM_TEXT);
$remove = optional_param('remove', '', PARAM_TEXT);
$reason = optional_param('reason', '', PARAM_TEXT);

if ($uuid && $remove && $reason) {
    // Process removal
    try {
        $DB->begin_sql();

        // Get the item
        $item = $DB->get_record('local_equipment_items', ['uuid' => $uuid], '*', MUST_EXIST);

        // Update item status to removed
        $item->status = 'removed';
        $item->timemodified = time();
        $DB->update_record('local_equipment_items', $item);

        // Log the removal transaction
        $transaction = new stdClass();
        $transaction->itemid = $item->id;
        $transaction->transaction_type = 'removal';
        $transaction->from_userid = $item->current_userid;
        $transaction->from_locationid = $item->locationid;
        $transaction->processed_by = $USER->id;
        $transaction->notes = $reason;
        $transaction->timestamp = time();
        $DB->insert_record('local_equipment_transactions', $transaction);

        $DB->commit_sql();

        echo html_writer::div(
            get_string('itemremoved', 'local_equipment'),
            'alert alert-success'
        );
    } catch (Exception $e) {
        $DB->rollback_sql();
        echo html_writer::div(
            'Error removing item: ' . $e->getMessage(),
            'alert alert-danger'
        );
    }
}

if ($uuid && !$remove) {
    // Look up equipment item
    try {
        $item = $DB->get_record('local_equipment_items', ['uuid' => $uuid]);

        if ($item) {
            // Get product details
            $product = $DB->get_record('local_equipment_products', ['id' => $item->productid]);
            $location = $DB->get_record('local_equipment_locations', ['id' => $item->locationid]);
            $current_user = null;
            if ($item->current_userid) {
                $current_user = $DB->get_record('user', ['id' => $item->current_userid]);
            }

            echo html_writer::tag('h3', get_string('equipmentdetails', 'local_equipment'));

            // Equipment details card
            echo html_writer::start_div('card mb-3');
            echo html_writer::start_div('card-body');

            echo html_writer::tag('h5', $product->name, ['class' => 'card-title']);
            echo html_writer::tag('p', 'UUID: ' . $item->uuid, ['class' => 'text-muted small']);

            $details = [
                get_string('manufacturer', 'local_equipment') => $product->manufacturer,
                get_string('model', 'local_equipment') => $product->model,
                get_string('serialnumber', 'local_equipment') => $item->serial_number,
                get_string('status', 'local_equipment') => ucfirst($item->status),
                get_string('conditionstatus', 'local_equipment') => ucfirst($item->condition_status),
                get_string('location', 'local_equipment') => $location ? $location->name : 'Unknown',
                get_string('currentuser', 'local_equipment') => $current_user ? fullname($current_user) : 'None'
            ];

            foreach ($details as $label => $value) {
                if (!empty($value)) {
                    echo html_writer::tag('p', html_writer::tag('strong', $label . ': ') . $value, ['class' => 'mb-1']);
                }
            }

            echo html_writer::end_div();
            echo html_writer::end_div();

            // Removal form
            if ($item->status !== 'removed') {
                echo html_writer::tag('h4', get_string('removeitem', 'local_equipment'));

                echo html_writer::start_tag('form', ['method' => 'post']);
                echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'uuid', 'value' => $uuid]);

                echo html_writer::start_div('form-group mb-3');
                echo html_writer::tag('label', get_string('reasonforremoving', 'local_equipment'), ['for' => 'reason']);
                echo html_writer::start_tag('select', ['name' => 'reason', 'id' => 'reason', 'class' => 'form-control', 'required' => true]);
                echo html_writer::tag('option', get_string('chooseoption', 'local_equipment'), ['value' => '']);
                echo html_writer::tag('option', get_string('damaged', 'local_equipment'), ['value' => 'damaged']);
                echo html_writer::tag('option', get_string('lost', 'local_equipment'), ['value' => 'lost']);
                echo html_writer::tag('option', get_string('stolen', 'local_equipment'), ['value' => 'stolen']);
                echo html_writer::tag('option', get_string('endoflife', 'local_equipment'), ['value' => 'endoflife']);
                echo html_writer::tag('option', get_string('disposed', 'local_equipment'), ['value' => 'disposed']);
                echo html_writer::tag('option', get_string('returnedtovendor', 'local_equipment'), ['value' => 'returnedtovendor']);
                echo html_writer::end_tag('select');
                echo html_writer::end_div();

                echo html_writer::tag('button', get_string('removeitem', 'local_equipment'), [
                    'type' => 'submit',
                    'name' => 'remove',
                    'value' => '1',
                    'class' => 'btn btn-danger',
                    'onclick' => 'return confirm("Are you sure you want to remove this item from inventory?")'
                ]);

                echo html_writer::end_tag('form');
            } else {
                echo html_writer::div(
                    get_string('equipmentalreadyremoved', 'local_equipment'),
                    'alert alert-warning'
                );
            }
        } else {
            echo html_writer::div(
                get_string('equipmentnotfound', 'local_equipment'),
                'alert alert-warning'
            );
        }
    } catch (Exception $e) {
        echo html_writer::div(
            'Error looking up equipment: ' . $e->getMessage(),
            'alert alert-danger'
        );
    }
} else {
    echo html_writer::tag('h3', get_string('equipmentdetails', 'local_equipment'));
    echo html_writer::div(
        get_string('scantoidentify', 'local_equipment'),
        'alert alert-info'
    );
}

echo html_writer::end_div(); // End right column

echo html_writer::end_div(); // End row
echo html_writer::end_div(); // End container

echo $OUTPUT->footer();
