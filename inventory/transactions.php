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
 * Equipment transactions history page.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

// Require login and check capabilities
require_login();
require_capability('local/equipment:checkinout', context_system::instance());

// Set up admin external page
admin_externalpage_setup('local_equipment_inventory_checkinout');

// Get parameters (let flexible_table handle pagination params)
$perpage = optional_param('perpage', 25, PARAM_INT);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('transactionhistory', 'local_equipment'));

// Create table
$table = new flexible_table('equipment_transactions');

// Define columns
$table->define_columns([
    'timestamp',
    'transaction_type',
    'product_name',
    'from_user',
    'to_user',
    'from_location',
    'to_location',
    'processed_by',
    'notes'
]);

// Define headers
$table->define_headers([
    get_string('timestamp', 'local_equipment'),
    get_string('type', 'local_equipment'),
    get_string('equipment', 'local_equipment'),
    get_string('fromuser', 'local_equipment'),
    get_string('touser', 'local_equipment'),
    get_string('fromlocation', 'local_equipment'),
    get_string('tolocation', 'local_equipment'),
    get_string('processedby', 'local_equipment'),
    get_string('notes', 'local_equipment')
]);

// Set up table
$table->define_baseurl(new moodle_url('/local/equipment/inventory/transactions.php'));
$table->sortable(true, 'timestamp', SORT_DESC);
$table->no_sorting('notes');
$table->pageable(true);
$table->pagesize($perpage, 1000);

$table->setup();

// Get transactions with proper pagination
global $DB;

// Base SQL for counting and data retrieval
$basesql = "SELECT t.*, ei.uuid, ep.name as product_name,
               u1.firstname as from_firstname, u1.lastname as from_lastname,
               u2.firstname as to_firstname, u2.lastname as to_lastname,
               l1.name as from_location_name, l2.name as to_location_name,
               u3.firstname as processed_firstname, u3.lastname as processed_lastname
        FROM {local_equipment_transactions} t
        JOIN {local_equipment_items} ei ON t.itemid = ei.id
        JOIN {local_equipment_products} ep ON ei.productid = ep.id
        LEFT JOIN {user} u1 ON t.from_userid = u1.id
        LEFT JOIN {user} u2 ON t.to_userid = u2.id
        LEFT JOIN {local_equipment_locations} l1 ON t.from_locationid = l1.id
        LEFT JOIN {local_equipment_locations} l2 ON t.to_locationid = l2.id
        JOIN {user} u3 ON t.processed_by = u3.id";

// Get sorting from table
$sort = $table->get_sql_sort();
if ($sort) {
    $basesql .= " ORDER BY " . $sort;
} else {
    $basesql .= " ORDER BY t.timestamp DESC";
}

// Get current page and per page from table
$page = optional_param('page', 0, PARAM_INT);
$offset = $page * $perpage;

// Get records for current page only
$transactions = $DB->get_records_sql($basesql, [], $offset, $perpage);

// Set total count for pagination
$countsql = "SELECT COUNT(t.id)
             FROM {local_equipment_transactions} t
             JOIN {local_equipment_items} ei ON t.itemid = ei.id
             JOIN {local_equipment_products} ep ON ei.productid = ep.id
             LEFT JOIN {user} u1 ON t.from_userid = u1.id
             LEFT JOIN {user} u2 ON t.to_userid = u2.id
             LEFT JOIN {local_equipment_locations} l1 ON t.from_locationid = l1.id
             LEFT JOIN {local_equipment_locations} l2 ON t.to_locationid = l2.id
             JOIN {user} u3 ON t.processed_by = u3.id";
$total = $DB->count_records_sql($countsql);

// Configure table pagination
$table->totalrows = $total;
$table->pagesize($perpage, $total);

// Add data to table
foreach ($transactions as $transaction) {
    $row = [];

    // Timestamp - using human-readable format for better UX
    $row[] = userdate($transaction->timestamp, get_string('strftimedatetimeshort', 'core_langconfig'));

    // Transaction type
    $type_badge = '';
    switch ($transaction->transaction_type) {
        case 'checkout':
            $type_badge = html_writer::span(get_string('checkout', 'local_equipment'), 'badge text-bg-warning');
            break;
        case 'checkin':
            $type_badge = html_writer::span(get_string('checkin', 'local_equipment'), 'badge text-bg-success');
            break;
        case 'transfer':
            $type_badge = html_writer::span(get_string('transfer', 'local_equipment'), 'badge text-bg-info');
            break;
        case 'maintenance':
            $type_badge = html_writer::span(get_string('maintenance ', 'local_equipment'), 'badge text-bg-secondary');
            break;
        case 'note_update':
            $type_badge = html_writer::span(get_string('noteupdate', 'local_equipment'), 'badge text-bg-light');
            break;
        default:
            $type_badge = html_writer::span(get_string($transaction->transaction_type, 'local_equipment'), 'badge text-bg-secondary');
    }
    $row[] = $type_badge;

    // Equipment - make entire cell clickable
    $equipment_info = $transaction->product_name;
    if ($transaction->uuid) {
        $equipment_info .= html_writer::tag('br', '') .
            html_writer::tag(
                'small',
                'UUID: ' . substr($transaction->uuid, 0, 8) . '...',
                ['class' => 'text-muted']
            );
    }

    // Make the entire equipment cell clickable if we have an item ID
    if (isset($transaction->itemid)) {
        $item_url = new moodle_url('/local/equipment/inventory/item_details.php', ['id' => $transaction->itemid]);
        $equipment_info = html_writer::link($item_url, $equipment_info, ['class' => 'text-decoration-none']);
    }

    $row[] = $equipment_info;

    // From user
    $from_user = '';
    if ($transaction->from_firstname && $transaction->from_lastname) {
        $from_user = $transaction->from_firstname . ' ' . $transaction->from_lastname;
    } else {
        $from_user = '-';
    }
    $row[] = $from_user;

    // To user
    $to_user = '';
    if ($transaction->to_firstname && $transaction->to_lastname) {
        $to_user = $transaction->to_firstname . ' ' . $transaction->to_lastname;
    } else {
        $to_user = '-';
    }
    $row[] = $to_user;

    // From location
    $from_location = $transaction->from_location_name ?: '-';
    $row[] = $from_location;

    // To location
    $to_location = $transaction->to_location_name ?: '-';
    $row[] = $to_location;

    // Processed by
    $processed_by = $transaction->processed_firstname . ' ' . $transaction->processed_lastname;
    $row[] = $processed_by;

    // Notes
    $notes = $transaction->notes ?: '-';
    if (strlen($notes) > 50) {
        $notes = substr($notes, 0, 50) . '...';
    }
    $row[] = $notes;

    $table->add_data($row);
}

// Display table (flexible_table handles pagination automatically)
$table->print_html();

// Back link
echo html_writer::start_div('mt-3');
echo html_writer::link(
    new moodle_url('/local/equipment/inventory/check_inout.php'),
    '← ' . get_string('backtocheckinout', 'local_equipment'),
    ['class' => 'btn btn-secondary']
);
echo html_writer::end_div();

echo $OUTPUT->footer();
