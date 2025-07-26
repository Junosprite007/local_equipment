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

// Handle AJAX requests
if (optional_param('ajax', false, PARAM_BOOL)) {
    require_sesskey();

    $action = required_param('action', PARAM_ALPHA);

    header('Content-Type: application/json');

    try {
        $inventory_manager = new \local_equipment\inventory\inventory_manager();

        switch ($action) {
            case 'lookup_equipment':
                $uuid = required_param('uuid', PARAM_ALPHANUMEXT);
                $result = $inventory_manager->get_equipment_details($uuid);
                echo json_encode($result);
                break;

            case 'update_assignment':
                $uuid = required_param('uuid', PARAM_ALPHANUMEXT);
                $userid = optional_param('userid', 0, PARAM_INT);
                $locationid = optional_param('locationid', 0, PARAM_INT);
                $notes = optional_param('notes', '', PARAM_TEXT);

                if ($userid > 0) {
                    $result = $inventory_manager->assign_to_user($uuid, $userid, $USER->id, $notes);
                } elseif ($locationid > 0) {
                    $result = $inventory_manager->assign_to_location($uuid, $locationid, $USER->id, $notes);
                } else {
                    $result = $inventory_manager->unassign_equipment($uuid, $USER->id, $notes);
                }

                echo json_encode($result);
                break;

            case 'search_users':
                $query = required_param('query', PARAM_TEXT);
                $users = $inventory_manager->search_users($query);
                echo json_encode(['users' => $users]);
                break;

            case 'get_locations':
                $locations = $inventory_manager->get_active_locations();
                echo json_encode(['locations' => $locations]);
                break;

            case 'update_notes':
                $uuid = required_param('uuid', PARAM_ALPHANUMEXT);
                $notes = required_param('notes', PARAM_TEXT);
                $result = $inventory_manager->update_equipment_notes($uuid, $notes, $USER->id);
                echo json_encode($result);
                break;

            default:
                throw new moodle_exception('invalidaction');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    exit;
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('checkinout', 'local_equipment'));

// Main interface
echo html_writer::start_div('row');

// QR Scanner Column (Left)
echo html_writer::start_div('col-md-6');
echo html_writer::tag('h3', get_string('scanqrcode', 'local_equipment'));

// Scanner container
echo html_writer::start_div('scanner-container mb-3', ['id' => 'scanner-container']);
echo html_writer::end_div();

// Mode toggle
echo html_writer::start_div('mode-toggle mb-3');
echo html_writer::tag('label', 'Assignment Mode:', ['class' => 'form-label fw-bold']);
echo html_writer::start_div('btn-group', ['role' => 'group', 'aria-label' => 'Assignment mode']);
echo html_writer::tag('input', '', [
    'type' => 'radio',
    'class' => 'btn-check',
    'name' => 'assignment_mode',
    'id' => 'mode_student',
    'value' => 'student',
    'checked' => 'checked'
]);
echo html_writer::tag('label', 'Student Assignment', [
    'class' => 'btn btn-outline-primary',
    'for' => 'mode_student'
]);
echo html_writer::tag('input', '', [
    'type' => 'radio',
    'class' => 'btn-check',
    'name' => 'assignment_mode',
    'id' => 'mode_location',
    'value' => 'location'
]);
echo html_writer::tag('label', 'Location Transfer', [
    'class' => 'btn btn-outline-secondary',
    'for' => 'mode_location'
]);
echo html_writer::end_div();
echo html_writer::end_div();

// Manual UUID Entry
echo html_writer::tag('h4', get_string('manualentry', 'local_equipment'));
echo html_writer::start_div('manual-input');
echo html_writer::tag('label', get_string('equipmentuuid', 'local_equipment'), [
    'for' => 'manual_uuid',
    'class' => 'form-label'
]);
echo html_writer::start_div('input-group');
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'id' => 'manual_uuid',
    'class' => 'form-control',
    'placeholder' => 'Enter UUID manually'
]);
echo html_writer::tag('button', get_string('lookup', 'local_equipment'), [
    'id' => 'lookup_btn',
    'class' => 'btn btn-outline-primary',
    'type' => 'button'
]);
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div(); // col-md-6

// Equipment Details Column (Right)
echo html_writer::start_div('col-md-6');
echo html_writer::tag('h3', get_string('equipmentdetails', 'local_equipment'));

echo html_writer::start_div('equipment-details-panel', [
    'id' => 'equipment-details',
    'style' => 'border: 1px solid #ddd; padding: 20px; min-height: 400px; background: white; border-radius: 8px;'
]);

echo html_writer::start_div('alert alert-info');
echo html_writer::tag('i', '', ['class' => 'fa fa-info-circle me-2']);
echo 'Scan a QR code or enter UUID manually to view equipment details and manage assignments.';
echo html_writer::end_div();

echo html_writer::end_div(); // equipment-details-panel
echo html_writer::end_div(); // col-md-6

echo html_writer::end_div(); // row

// Recent Transactions Section
echo html_writer::tag('h3', get_string('recenttransactions', 'local_equipment'), ['class' => 'mt-4']);

try {
    global $DB;

    // Get recent transactions for current user only
    $recent_transactions = $DB->get_records_sql("
        SELECT t.*, ei.uuid, ep.name as product_name,
               u1.firstname as from_firstname, u1.lastname as from_lastname,
               u2.firstname as to_firstname, u2.lastname as to_lastname,
               l1.name as from_location, l2.name as to_location
        FROM {local_equipment_transactions} t
        JOIN {local_equipment_items} ei ON t.itemid = ei.id
        JOIN {local_equipment_products} ep ON ei.productid = ep.id
        LEFT JOIN {user} u1 ON t.from_userid = u1.id
        LEFT JOIN {user} u2 ON t.to_userid = u2.id
        LEFT JOIN {local_equipment_locations} l1 ON t.from_locationid = l1.id
        LEFT JOIN {local_equipment_locations} l2 ON t.to_locationid = l2.id
        WHERE t.processed_by = ?
        ORDER BY t.timestamp DESC
        LIMIT 10
    ", [$USER->id]);

    if ($recent_transactions) {
        echo html_writer::start_tag('div', ['class' => 'table-responsive']);
        echo html_writer::start_tag('table', ['class' => 'table table-striped table-sm']);
        echo html_writer::start_tag('thead');
        echo html_writer::start_tag('tr');
        echo html_writer::tag('th', 'Time');
        echo html_writer::tag('th', 'Type');
        echo html_writer::tag('th', 'Equipment');
        echo html_writer::tag('th', 'From/To');
        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thead');
        echo html_writer::start_tag('tbody');

        foreach ($recent_transactions as $transaction) {
            echo html_writer::start_tag('tr');
            echo html_writer::tag('td', userdate($transaction->timestamp, '%m/%d %H:%M'));
            echo html_writer::tag('td', ucfirst($transaction->transaction_type));
            echo html_writer::tag('td', $transaction->product_name . ' (' . substr($transaction->uuid, 0, 8) . '...)');

            $from_to = '';
            if ($transaction->transaction_type === 'checkout' && $transaction->to_firstname) {
                $from_to = 'To: ' . $transaction->to_firstname . ' ' . $transaction->to_lastname;
            } elseif ($transaction->transaction_type === 'checkin' && $transaction->from_firstname) {
                $from_to = 'From: ' . $transaction->from_firstname . ' ' . $transaction->from_lastname;
            } elseif ($transaction->to_location) {
                $from_to = 'To: ' . $transaction->to_location;
            } elseif ($transaction->from_location) {
                $from_to = 'From: ' . $transaction->from_location;
            }
            echo html_writer::tag('td', $from_to);
            echo html_writer::end_tag('tr');
        }

        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');
        echo html_writer::end_tag('div');

        // View all transactions link
        echo html_writer::link(
            new moodle_url('/local/equipment/inventory/transactions.php'),
            'View all transactions →',
            ['class' => 'btn btn-outline-secondary btn-sm']
        );
    } else {
        echo html_writer::tag('p', 'No recent transactions found.', ['class' => 'text-muted']);
    }
} catch (Exception $e) {
    echo html_writer::tag('p', 'Error loading recent transactions: ' . $e->getMessage(), ['class' => 'text-danger']);
}

// Include JavaScript
$PAGE->requires->js_call_amd('local_equipment/check-inout-scanner', 'init');
$PAGE->requires->js_call_amd('local_equipment/queue-notification', 'init');

echo $OUTPUT->footer();
