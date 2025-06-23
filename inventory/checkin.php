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
 * Equipment check-in/check-out interface.
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
require_capability('local/equipment:checkinout', context_system::instance());

// Set up admin external page
admin_externalpage_setup('local_equipment_inventory_checkinout');

// Handle form submission
$action = optional_param('action', '', PARAM_ALPHA);
$uuid = optional_param('uuid', '', PARAM_ALPHANUMEXT);
$userid = optional_param('userid', 0, PARAM_INT);
$locationid = optional_param('locationid', 0, PARAM_INT);
$condition = optional_param('condition', '', PARAM_ALPHA);
$notes = optional_param('notes', '', PARAM_TEXT);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('checkinout', 'local_equipment'));

// Create inventory manager instance
$inventory_manager = new \local_equipment\inventory\inventory_manager();

// Process actions
if ($action && $uuid) {
    try {
        if ($action === 'checkin' && $locationid) {
            $result = $inventory_manager->check_in_item($uuid, $locationid, $USER->id, $condition, $notes);
            if ($result->success) {
                echo $OUTPUT->notification($result->message, 'success');
            } else {
                echo $OUTPUT->notification($result->message, 'error');
            }
        } elseif ($action === 'checkout' && $userid) {
            $result = $inventory_manager->check_out_item($uuid, $userid, $USER->id, $notes);
            if ($result->success) {
                echo $OUTPUT->notification($result->message, 'success');
            } else {
                echo $OUTPUT->notification($result->message, 'error');
            }
        }
    } catch (Exception $e) {
        echo $OUTPUT->notification('Error: ' . $e->getMessage(), 'error');
    }
}

// QR Scanner Interface
echo html_writer::start_div('row');

// QR Scanner Column
echo html_writer::start_div('col-md-6');
echo html_writer::tag('h3', get_string('scanqrcode', 'local_equipment'));

echo html_writer::start_div('qr-scanner-container', [
    'style' => 'border: 2px solid #ddd; padding: 20px; text-align: center; min-height: 300px; background: #f8f9fa;'
]);

echo html_writer::tag('div', 'QR Code Scanner', [
    'id' => 'qr-scanner',
    'style' => 'width: 100%; height: 250px; background: #e9ecef; display: flex; align-items: center; justify-content: center; border: 1px dashed #6c757d;'
]);

echo html_writer::tag('p', 'Click "Start Scanner" to begin scanning QR codes', ['class' => 'mt-2 text-muted']);

echo html_writer::tag('button', get_string('startscanner', 'local_equipment'), [
    'id' => 'start-scanner-btn',
    'class' => 'btn btn-primary mt-2'
]);

echo html_writer::tag('button', get_string('stopscanner', 'local_equipment'), [
    'id' => 'stop-scanner-btn',
    'class' => 'btn btn-secondary mt-2',
    'style' => 'display: none;'
]);

echo html_writer::end_div(); // qr-scanner-container
echo html_writer::end_div(); // col-md-6

// Equipment Details Column
echo html_writer::start_div('col-md-6');
echo html_writer::tag('h3', get_string('equipmentdetails', 'local_equipment'));

echo html_writer::start_div('equipment-details', [
    'id' => 'equipment-details',
    'style' => 'border: 1px solid #ddd; padding: 20px; min-height: 300px; background: white;'
]);

echo html_writer::tag('p', 'Scan a QR code to view equipment details', ['class' => 'text-muted']);

echo html_writer::end_div(); // equipment-details
echo html_writer::end_div(); // col-md-6

echo html_writer::end_div(); // row

// Manual UUID Entry
echo html_writer::tag('h3', get_string('manualentry', 'local_equipment'), ['class' => 'mt-4']);

echo html_writer::start_tag('form', ['method' => 'post', 'action' => '', 'class' => 'row g-3']);

echo html_writer::start_div('col-md-4');
echo html_writer::tag('label', get_string('equipmentuuid', 'local_equipment'), ['for' => 'uuid', 'class' => 'form-label']);
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'id' => 'uuid',
    'name' => 'uuid',
    'class' => 'form-control',
    'placeholder' => 'Enter UUID manually'
]);
echo html_writer::end_div();

echo html_writer::start_div('col-md-2');
echo html_writer::tag('label', '&nbsp;', ['class' => 'form-label']);
echo html_writer::tag('button', get_string('lookup', 'local_equipment'), [
    'type' => 'submit',
    'name' => 'action',
    'value' => 'lookup',
    'class' => 'btn btn-outline-primary form-control'
]);
echo html_writer::end_div();

echo html_writer::end_tag('form');

// Recent Transactions
echo html_writer::tag('h3', get_string('recenttransactions', 'local_equipment'), ['class' => 'mt-4']);

try {
    global $DB;
    $recent_transactions = $DB->get_records_sql("
        SELECT t.*, ei.uuid, ep.name as product_name,
               u1.firstname as from_firstname, u1.lastname as from_lastname,
               u2.firstname as to_firstname, u2.lastname as to_lastname,
               u3.firstname as processed_firstname, u3.lastname as processed_lastname
        FROM {local_equipment_transactions} t
        JOIN {local_equipment_items} ei ON t.itemid = ei.id
        JOIN {local_equipment_products} ep ON ei.productid = ep.id
        LEFT JOIN {user} u1 ON t.from_userid = u1.id
        LEFT JOIN {user} u2 ON t.to_userid = u2.id
        JOIN {user} u3 ON t.processed_by = u3.id
        ORDER BY t.timestamp DESC
        LIMIT 10
    ");

    if ($recent_transactions) {
        echo html_writer::start_tag('div', ['class' => 'table-responsive']);
        echo html_writer::start_tag('table', ['class' => 'table table-striped']);
        echo html_writer::start_tag('thead');
        echo html_writer::start_tag('tr');
        echo html_writer::tag('th', 'Time');
        echo html_writer::tag('th', 'Type');
        echo html_writer::tag('th', 'Equipment');
        echo html_writer::tag('th', 'From/To');
        echo html_writer::tag('th', 'Processed By');
        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thead');
        echo html_writer::start_tag('tbody');

        foreach ($recent_transactions as $transaction) {
            echo html_writer::start_tag('tr');
            echo html_writer::tag('td', userdate($transaction->timestamp, '%Y-%m-%d %H:%M'));
            echo html_writer::tag('td', ucfirst($transaction->transaction_type));
            echo html_writer::tag('td', $transaction->product_name . ' (' . substr($transaction->uuid, 0, 8) . '...)');

            $from_to = '';
            if ($transaction->transaction_type === 'checkout' && $transaction->to_firstname) {
                $from_to = 'To: ' . $transaction->to_firstname . ' ' . $transaction->to_lastname;
            } elseif ($transaction->transaction_type === 'checkin' && $transaction->from_firstname) {
                $from_to = 'From: ' . $transaction->from_firstname . ' ' . $transaction->from_lastname;
            }
            echo html_writer::tag('td', $from_to);

            echo html_writer::tag('td', $transaction->processed_firstname . ' ' . $transaction->processed_lastname);
            echo html_writer::end_tag('tr');
        }

        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');
        echo html_writer::end_tag('div');
    } else {
        echo html_writer::tag('p', 'No recent transactions found.', ['class' => 'text-muted']);
    }
} catch (Exception $e) {
    echo html_writer::tag('p', 'Error loading recent transactions: ' . $e->getMessage(), ['class' => 'text-danger']);
}

// Add JavaScript for QR scanner (placeholder for now)
echo html_writer::start_tag('script');
echo "
document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('start-scanner-btn');
    const stopBtn = document.getElementById('stop-scanner-btn');
    const scanner = document.getElementById('qr-scanner');
    const details = document.getElementById('equipment-details');

    startBtn.addEventListener('click', function() {
        scanner.innerHTML = '<div style=\"padding: 50px; color: #6c757d;\">📷 QR Scanner Active<br><small>Point camera at QR code</small></div>';
        startBtn.style.display = 'none';
        stopBtn.style.display = 'inline-block';

        // Placeholder for actual QR scanning implementation
        setTimeout(function() {
            // Simulate QR code detection
            details.innerHTML = '<div class=\"alert alert-info\">QR Scanner is ready. Actual camera integration will be implemented with proper QR scanning library.</div>';
        }, 1000);
    });

    stopBtn.addEventListener('click', function() {
        scanner.innerHTML = '<div style=\"padding: 50px; color: #6c757d;\">📷 Scanner Stopped</div>';
        startBtn.style.display = 'inline-block';
        stopBtn.style.display = 'none';
        details.innerHTML = '<p class=\"text-muted\">Scan a QR code to view equipment details</p>';
    });
});
";
echo html_writer::end_tag('script');

echo $OUTPUT->footer();
