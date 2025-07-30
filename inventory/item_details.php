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
 * Individual equipment item details page with QR code management.
 *
 * @package     local_equipment
 * @copyright   2025 Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_equipment\inventory\print_queue_manager;
use local_equipment\inventory\qr_generator;

// Check capabilities.
admin_externalpage_setup('local_equipment_inventory_manage');
$context = context_system::instance();
require_capability('local/equipment:manageinventory', $context);

$itemid = required_param('id', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

$PAGE->set_url(new moodle_url('/local/equipment/inventory/item_details.php', ['id' => $itemid]));
$PAGE->set_title(get_string('itemdetails', 'local_equipment'));
$PAGE->set_heading(get_string('itemdetails', 'local_equipment'));

// Add queue notification JavaScript
$PAGE->requires->js_call_amd('local_equipment/queue-notification', 'init');

// Get item details.
$sql = "SELECT i.*, p.name as product_name, p.manufacturer, p.model, p.upc, p.category,
               l.name as location_name, u.firstname, u.lastname,
               t.timestamp as last_transaction_time, t.transaction_type as last_transaction_type
        FROM {local_equipment_items} i
        JOIN {local_equipment_products} p ON i.productid = p.id
        LEFT JOIN {local_equipment_locations} l ON i.locationid = l.id
        LEFT JOIN {user} u ON i.current_userid = u.id
        LEFT JOIN {local_equipment_transactions} t ON i.id = t.itemid
        WHERE i.id = :itemid
        ORDER BY t.timestamp DESC
        LIMIT 1";

$item = $DB->get_record_sql($sql, ['itemid' => $itemid]);

if (!$item) {
    throw new moodle_exception('itemnotfound', 'local_equipment');
}

$print_manager = new print_queue_manager();
$qr_generator = new qr_generator();

// Handle actions.
if ($action && confirm_sesskey()) {
    // Check if item is removed - prevent all QR-related actions
    if ($item->status === 'removed') {
        \core\notification::add(
            get_string('cannot_perform_action_removed_item', 'local_equipment'),
            \core\notification::ERROR
        );
    } else {
        switch ($action) {
            case 'addtoqueue':
                // Only allow queueing for available items
                if ($item->status !== 'available') {
                    \core\notification::add(
                        get_string('can_only_queue_available_items', 'local_equipment'),
                        \core\notification::WARNING
                    );
                    break;
                }

                if (!$print_manager->is_item_in_queue($itemid)) {
                    $success = $print_manager->add_item_to_queue($itemid, $item->uuid, $USER->id, 'Added via item details page');
                    if ($success) {
                        \core\notification::add(get_string('qrcodequeued', 'local_equipment'), \core\notification::SUCCESS);
                    } else {
                        \core\notification::add(get_string('qrcodequeuefailed', 'local_equipment'), \core\notification::ERROR);
                    }
                } else {
                    \core\notification::add(get_string('qrcodequeuedalready', 'local_equipment'), \core\notification::WARNING);
                }
                break;

            case 'printdirect':
                // Only allow direct printing for available items
                if ($item->status !== 'available') {
                    \core\notification::add(
                        get_string('can_only_print_available_items', 'local_equipment'),
                        \core\notification::WARNING
                    );
                    break;
                }

                // Generate and display single QR code for immediate printing
                $qr_url = new moodle_url('/local/equipment/inventory/generate_qr.php', [
                    'mode' => 'single',
                    'uuid' => $item->uuid,
                    'sesskey' => sesskey()
                ]);
                redirect($qr_url);
                break;
        }
    }
}

// Calculate days checked out if applicable.
$days_checked_out = '';
if ($item->status === 'checked_out' && $item->last_transaction_time) {
    $days = floor((time() - $item->last_transaction_time) / DAYSECS);
    $days_checked_out = $days . ' ' . ($days == 1 ? 'day' : 'days');
}

$in_queue = $print_manager->is_item_in_queue($itemid);

echo $OUTPUT->header();

// Item header.
echo html_writer::tag('h2', format_string($item->product_name) . ' - ' . $item->uuid);

// Status-based notice for removed items
if ($item->status === 'removed') {
    echo html_writer::div(
        html_writer::tag('i', '', ['class' => 'fa fa-exclamation-triangle me-2']) .
            get_string('item_removed_notice', 'local_equipment'),
        'alert alert-warning mb-3'
    );
}

// Action buttons - conditionally displayed based on status
echo html_writer::start_div('mb-3');

if ($item->status === 'removed') {
    // Show disabled buttons for removed items with explanatory text
    echo html_writer::tag(
        'button',
        get_string('addtoqrqueue', 'local_equipment'),
        [
            'class' => 'btn btn-secondary me-2 disabled',
            'disabled' => 'disabled',
            'title' => get_string('action_disabled_item_removed', 'local_equipment')
        ]
    );

    echo html_writer::tag(
        'button',
        get_string('reprintqrcode', 'local_equipment'),
        [
            'class' => 'btn btn-primary me-2 disabled',
            'disabled' => 'disabled',
            'title' => get_string('action_disabled_item_removed', 'local_equipment')
        ]
    );

    echo html_writer::tag(
        'small',
        get_string('actions_disabled_explanation', 'local_equipment'),
        ['class' => 'text-muted d-block mt-2']
    );
} else {
    // Show active buttons for non-removed items

    // Add to queue button - only for available items
    if ($item->status === 'available') {
        if (!$in_queue) {
            $add_queue_url = new moodle_url('/local/equipment/inventory/item_details.php', [
                'id' => $itemid,
                'action' => 'addtoqueue',
                'sesskey' => sesskey()
            ]);
            echo html_writer::link(
                $add_queue_url,
                get_string('addtoqrqueue', 'local_equipment'),
                ['class' => 'btn btn-secondary me-2']
            );
        } else {
            echo html_writer::tag(
                'span',
                get_string('qrcodequeued', 'local_equipment'),
                ['class' => 'btn btn-secondary me-2 disabled']
            );
        }

        // Direct print button - only for available items
        $print_url = new moodle_url('/local/equipment/inventory/item_details.php', [
            'id' => $itemid,
            'action' => 'printdirect',
            'sesskey' => sesskey()
        ]);
        echo html_writer::link(
            $print_url,
            get_string('reprintqrcode', 'local_equipment'),
            ['class' => 'btn btn-primary me-2']
        );
    } else {
        // Show disabled buttons for non-available, non-removed items (checked out, maintenance, etc.)
        echo html_writer::tag(
            'button',
            get_string('addtoqrqueue', 'local_equipment'),
            [
                'class' => 'btn btn-secondary me-2 disabled',
                'disabled' => 'disabled',
                'title' => get_string('action_disabled_not_available', 'local_equipment')
            ]
        );

        echo html_writer::tag(
            'button',
            get_string('reprintqrcode', 'local_equipment'),
            [
                'class' => 'btn btn-primary me-2 disabled',
                'disabled' => 'disabled',
                'title' => get_string('action_disabled_not_available', 'local_equipment')
            ]
        );

        echo html_writer::tag(
            'small',
            get_string('qr_actions_only_available', 'local_equipment'),
            ['class' => 'text-muted d-block mt-2']
        );
    }
}

// General navigation buttons - always available
echo html_writer::start_div('mt-3 pt-3 border-top');

// Link to check in/out page.
$checkinout_url = new moodle_url('/local/equipment/inventory/check_inout.php');
echo html_writer::link(
    $checkinout_url,
    get_string('checkinout', 'local_equipment'),
    ['class' => 'btn btn-outline-primary me-2']
);

// Link to products management.
$products_url = new moodle_url('/local/equipment/inventory/products.php');
echo html_writer::link(
    $products_url,
    get_string('manageproducts', 'local_equipment'),
    ['class' => 'btn btn-outline-secondary me-2']
);

// Link back to main inventory
$inventory_url = new moodle_url('/local/equipment/inventory/manage.php');
echo html_writer::link(
    $inventory_url,
    get_string('backtoinventory', 'local_equipment'),
    ['class' => 'btn btn-outline-info']
);

echo html_writer::end_div();
echo html_writer::end_div();

// Item details table.
echo html_writer::start_div('row');
echo html_writer::start_div('col-md-8');

$table = new html_table();
$table->attributes['class'] = 'table table-striped';
$table->head = [get_string('field', 'local_equipment'), get_string('value', 'local_equipment')];
$table->data = [];

// Basic information.
$table->data[] = [get_string('uuid', 'local_equipment'), html_writer::tag('code', $item->uuid)];
$table->data[] = [get_string('product', 'local_equipment'), format_string($item->product_name)];
$table->data[] = [get_string('manufacturer', 'local_equipment'), format_string($item->manufacturer ?: '-')];
$table->data[] = [get_string('model', 'local_equipment'), format_string($item->model ?: '-')];
$table->data[] = [get_string('upc', 'local_equipment'), $item->upc ?: '-'];
$table->data[] = [get_string('category', 'local_equipment'), format_string($item->category ?: '-')];

// Status information.
$status_badge = '';
switch ($item->status) {
    case 'available':
        $status_badge = html_writer::tag(
            'span',
            get_string('available', 'local_equipment'),
            ['class' => 'badge text-bg-success']
        );
        break;
    case 'checked_out':
        $status_badge = html_writer::tag(
            'span',
            get_string('checkedout', 'local_equipment'),
            ['class' => 'badge text-bg-warning']
        );
        break;
    case 'maintenance':
        $status_badge = html_writer::tag(
            'span',
            get_string('maintenance', 'local_equipment'),
            ['class' => 'badge text-bg-danger']
        );
        break;
    default:
        $status_badge = html_writer::tag(
            'span',
            format_string($item->status),
            ['class' => 'badge text-bg-secondary']
        );
}
$table->data[] = [get_string('status', 'local_equipment'), $status_badge];

$condition_badge = '';
switch ($item->condition_status) {
    case 'excellent':
        $condition_badge = html_writer::tag(
            'span',
            get_string('excellent', 'local_equipment'),
            ['class' => 'badge text-bg-success']
        );
        break;
    case 'good':
        $condition_badge = html_writer::tag(
            'span',
            get_string('good', 'local_equipment'),
            ['class' => 'badge text-bg-primary']
        );
        break;
    case 'fair':
        $condition_badge = html_writer::tag(
            'span',
            get_string('fair', 'local_equipment'),
            ['class' => 'badge text-bg-warning']
        );
        break;
    case 'poor':
        $condition_badge = html_writer::tag(
            'span',
            get_string('poor', 'local_equipment'),
            ['class' => 'badge text-bg-danger']
        );
        break;
    default:
        $condition_badge = html_writer::tag(
            'span',
            format_string($item->condition_status),
            ['class' => 'badge text-bg-secondary']
        );
}
$table->data[] = [get_string('condition', 'local_equipment'), $condition_badge];

// Location and user information.
$table->data[] = [get_string('location', 'local_equipment'), format_string($item->location_name ?: '-')];

if ($item->current_userid) {
    $user_name = fullname((object)['firstname' => $item->firstname, 'lastname' => $item->lastname]);
    $table->data[] = [get_string('checkedoutto', 'local_equipment'), format_string($user_name)];

    if ($days_checked_out) {
        $table->data[] = [get_string('dayscheckedout', 'local_equipment'), $days_checked_out];
    }
}

// Additional details.
if ($item->serial_number) {
    $table->data[] = [get_string('serialnumber', 'local_equipment'), format_string($item->serial_number)];
}

if ($item->student_label) {
    $table->data[] = [get_string('studentlabel', 'local_equipment'), format_string($item->student_label)];
}

if ($item->condition_notes) {
    $table->data[] = [get_string('conditionnotes', 'local_equipment'), format_text($item->condition_notes, FORMAT_PLAIN)];
}

// Timestamps.
$table->data[] = [get_string('timecreated', 'local_equipment'), userdate($item->timecreated)];
$table->data[] = [get_string('timemodified', 'local_equipment'), userdate($item->timemodified)];

if ($item->last_transaction_time) {
    $table->data[] = [
        get_string('lasttransaction', 'local_equipment'),
        userdate($item->last_transaction_time) . ' (' . get_string($item->last_transaction_type, 'local_equipment') . ')'
    ];
}

echo html_writer::table($table);
echo html_writer::end_div();

// QR Code display.
echo html_writer::start_div('col-md-4');
echo html_writer::tag('h4', get_string('qrcode', 'local_equipment'));

try {
    $qr_data = $qr_generator->generate_item_qr($item->uuid, 200);
    $qr_image = html_writer::empty_tag('img', [
        'src' => 'data:image/png;base64,' . $qr_data,
        'alt' => 'QR Code: ' . $item->uuid,
        'class' => 'img-fluid',
        'style' => 'max-width: 200px; max-height: 200px;'
    ]);
    echo html_writer::div($qr_image, 'text-center mb-3');
    echo html_writer::div(html_writer::tag('small', $item->uuid, ['class' => 'text-muted']), 'text-center');
} catch (Exception $e) {
    echo html_writer::div(get_string('qrgenerationfailed', 'local_equipment'), 'alert alert-warning');
}

echo html_writer::end_div();
echo html_writer::end_div();

// Recent transactions.
echo html_writer::tag('h3', get_string('recenttransactions', 'local_equipment'), ['class' => 'mt-4']);

$transactions_sql = "SELECT t.*,
                            u1.firstname as from_firstname, u1.lastname as from_lastname,
                            u1.firstnamephonetic as from_firstnamephonetic, u1.lastnamephonetic as from_lastnamephonetic,
                            u1.middlename as from_middlename, u1.alternatename as from_alternatename,
                            u2.firstname as to_firstname, u2.lastname as to_lastname,
                            u2.firstnamephonetic as to_firstnamephonetic, u2.lastnamephonetic as to_lastnamephonetic,
                            u2.middlename as to_middlename, u2.alternatename as to_alternatename,
                            l1.name as from_location, l2.name as to_location,
                            p.firstname as processed_firstname, p.lastname as processed_lastname,
                            p.firstnamephonetic as processed_firstnamephonetic, p.lastnamephonetic as processed_lastnamephonetic,
                            p.middlename as processed_middlename, p.alternatename as processed_alternatename
                     FROM {local_equipment_transactions} t
                     LEFT JOIN {user} u1 ON t.from_userid = u1.id
                     LEFT JOIN {user} u2 ON t.to_userid = u2.id
                     LEFT JOIN {local_equipment_locations} l1 ON t.from_locationid = l1.id
                     LEFT JOIN {local_equipment_locations} l2 ON t.to_locationid = l2.id
                     LEFT JOIN {user} p ON t.processed_by = p.id
                     WHERE t.itemid = :itemid
                     ORDER BY t.timestamp DESC
                     LIMIT 10";

$transactions = $DB->get_records_sql($transactions_sql, ['itemid' => $itemid]);

if ($transactions) {
    $trans_table = new html_table();
    $trans_table->attributes['class'] = 'table table-striped';
    $trans_table->head = [
        get_string('date', 'local_equipment'),
        get_string('type', 'local_equipment'),
        get_string('from', 'local_equipment'),
        get_string('to', 'local_equipment'),
        get_string('processedby', 'local_equipment'),
        get_string('notes', 'local_equipment')
    ];
    $trans_table->data = [];

    foreach ($transactions as $transaction) {
        $from = '';
        if ($transaction->from_userid) {
            $from = fullname((object)['firstname' => $transaction->from_firstname, 'lastname' => $transaction->from_lastname]);
        } else if ($transaction->from_location) {
            $from = $transaction->from_location;
        }

        $to = '';
        if ($transaction->to_userid) {
            $to = fullname((object)['firstname' => $transaction->to_firstname, 'lastname' => $transaction->to_lastname]);
        } else if ($transaction->to_location) {
            $to = $transaction->to_location;
        }

        $processed_by = fullname((object)['firstname' => $transaction->processed_firstname, 'lastname' => $transaction->processed_lastname]);

        $trans_table->data[] = [
            userdate($transaction->timestamp, get_string('strftimedatetimeshort')),
            get_string($transaction->transaction_type, 'local_equipment'),
            $from ?: '-',
            $to ?: '-',
            $processed_by,
            $transaction->notes ? format_text($transaction->notes, FORMAT_PLAIN) : '-'
        ];
    }

    echo html_writer::table($trans_table);
} else {
    echo html_writer::div(get_string('notransactions', 'local_equipment'), 'alert alert-info');
}

echo $OUTPUT->footer();
