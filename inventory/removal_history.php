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
 * Equipment removal history report interface.
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
$context = context_system::instance();
require_capability('local/equipment:manageinventory', $context);

// Set up page
$PAGE->set_context($context);
$PAGE->set_url('/local/equipment/inventory/removal_history.php');
$PAGE->set_title(get_string('removal_history', 'local_equipment'));
$PAGE->set_heading(get_string('removal_history', 'local_equipment'));
$PAGE->set_pagelayout('admin');

// Get filtering parameters
$product_filter = optional_param('product', 0, PARAM_INT);
$reason_filter = optional_param('reason', '', PARAM_TEXT);
$date_from = optional_param('date_from', '', PARAM_TEXT);
$date_to = optional_param('date_to', '', PARAM_TEXT);
$removed_by_filter = optional_param('removed_by', 0, PARAM_INT);
$export = optional_param('export', '', PARAM_ALPHA);

// Handle CSV export
if ($export === 'csv') {
    $filename = 'equipment_removal_history_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // CSV headers
    $headers = [
        'Product Name',
        'Manufacturer',
        'Model',
        'UUID',
        'Serial Number',
        'Removal Reason',
        'Removal Date',
        'Removed By',
        'Last User',
        'Days in Service',
        'Total Checkouts',
        'Location',
        'Condition',
        'Notes'
    ];
    fputcsv($output, $headers);

    // Get data (we'll define this query below)
    $removed_items = get_removed_items_data($product_filter, $reason_filter, $date_from, $date_to, $removed_by_filter);

    foreach ($removed_items as $item) {
        $row = [
            $item->product_name,
            $item->manufacturer ?: '',
            $item->model ?: '',
            $item->uuid,
            $item->serial_number ?: '',
            $item->removal_reason ?: '',
            $item->removal_date ? date('Y-m-d H:i', $item->removal_date) : '',
            $item->removed_by_name ?: '',
            $item->last_user_name ?: '',
            $item->days_in_service,
            $item->total_checkouts,
            $item->location_name ?: '',
            $item->condition_status ?: '',
            $item->condition_notes ?: ''
        ];
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('removal_history', 'local_equipment'));

// Filter form
echo html_writer::start_tag('div', ['class' => 'card mb-4']);
echo html_writer::start_tag('div', ['class' => 'card-header']);
echo html_writer::tag('h5', 'Filter Options', ['class' => 'card-title mb-0']);
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', ['class' => 'card-body']);
echo html_writer::start_tag('form', ['method' => 'get', 'action' => '', 'class' => 'row g-3']);

// Product filter
echo html_writer::start_div('col-md-3');
echo html_writer::tag('label', 'Product', ['for' => 'product', 'class' => 'form-label']);
$products = $DB->get_records_menu('local_equipment_products', ['active' => 1], 'name ASC', 'id, name');
$product_options = [0 => 'All Products'] + $products;
echo html_writer::select($product_options, 'product', $product_filter, false, ['class' => 'form-select', 'id' => 'product']);
echo html_writer::end_div();

// Removal reason filter
echo html_writer::start_div('col-md-3');
echo html_writer::tag('label', 'Removal Reason', ['for' => 'reason', 'class' => 'form-label']);
$reason_options = [
    '' => 'All Reasons',
    'damaged' => 'Damaged',
    'lost' => 'Lost',
    'stolen' => 'Stolen',
    'end-of-life' => 'End of Life',
    'disposed' => 'Disposed',
    'returned-to-vendor' => 'Returned to Vendor'
];
echo html_writer::select($reason_options, 'reason', $reason_filter, false, ['class' => 'form-select', 'id' => 'reason']);
echo html_writer::end_div();

// Date from filter
echo html_writer::start_div('col-md-3');
echo html_writer::tag('label', 'Removed From', ['for' => 'date_from', 'class' => 'form-label']);
echo html_writer::empty_tag('input', [
    'type' => 'date',
    'id' => 'date_from',
    'name' => 'date_from',
    'value' => $date_from,
    'class' => 'form-control'
]);
echo html_writer::end_div();

// Date to filter
echo html_writer::start_div('col-md-3');
echo html_writer::tag('label', 'Removed To', ['for' => 'date_to', 'class' => 'form-label']);
echo html_writer::empty_tag('input', [
    'type' => 'date',
    'id' => 'date_to',
    'name' => 'date_to',
    'value' => $date_to,
    'class' => 'form-control'
]);
echo html_writer::end_div();

// Removed by filter
echo html_writer::start_div('col-md-3');
echo html_writer::tag('label', 'Removed By', ['for' => 'removed_by', 'class' => 'form-label']);
$staff_users = $DB->get_records_sql("
    SELECT DISTINCT u.id, CONCAT(u.firstname, ' ', u.lastname) as fullname
    FROM {user} u
    JOIN {local_equipment_items} ei ON u.id = ei.removed_by
    WHERE ei.status = 'removed'
    ORDER BY u.firstname, u.lastname
");
$staff_options = [0 => 'All Staff'];
foreach ($staff_users as $user) {
    $staff_options[$user->id] = $user->fullname;
}
echo html_writer::select($staff_options, 'removed_by', $removed_by_filter, false, ['class' => 'form-select', 'id' => 'removed_by']);
echo html_writer::end_div();

// Filter buttons
echo html_writer::start_div('col-12');
echo html_writer::tag('button', 'Apply Filters', [
    'type' => 'submit',
    'class' => 'btn btn-primary me-2'
]);
echo html_writer::link(
    new moodle_url('/local/equipment/inventory/removal_history.php'),
    'Clear Filters',
    ['class' => 'btn btn-secondary me-2']
);
echo html_writer::link(
    new moodle_url('/local/equipment/inventory/removal_history.php', [
        'product' => $product_filter,
        'reason' => $reason_filter,
        'date_from' => $date_from,
        'date_to' => $date_to,
        'removed_by' => $removed_by_filter,
        'export' => 'csv'
    ]),
    'Export to CSV',
    ['class' => 'btn btn-success']
);
echo html_writer::end_div();

echo html_writer::end_tag('form');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

// Get and display removed items
try {
    $removed_items = get_removed_items_data($product_filter, $reason_filter, $date_from, $date_to, $removed_by_filter);

    if ($removed_items) {
        // Summary statistics
        $total_value_lost = 0;
        $reason_counts = [];
        $avg_days_service = 0;
        $total_checkouts = 0;

        foreach ($removed_items as $item) {
            if (!isset($reason_counts[$item->removal_reason])) {
                $reason_counts[$item->removal_reason] = 0;
            }
            $reason_counts[$item->removal_reason]++;
            $avg_days_service += $item->days_in_service;
            $total_checkouts += $item->total_checkouts;
        }

        $avg_days_service = count($removed_items) > 0 ? round($avg_days_service / count($removed_items)) : 0;

        // Display summary
        echo html_writer::start_tag('div', ['class' => 'row mb-4']);

        echo html_writer::start_div('col-md-3');
        echo html_writer::start_tag('div', ['class' => 'card text-center']);
        echo html_writer::start_tag('div', ['class' => 'card-body']);
        echo html_writer::tag('h5', count($removed_items), ['class' => 'card-title text-danger']);
        echo html_writer::tag('p', 'Total Removed Items', ['class' => 'card-text']);
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
        echo html_writer::end_div();

        echo html_writer::start_div('col-md-3');
        echo html_writer::start_tag('div', ['class' => 'card text-center']);
        echo html_writer::start_tag('div', ['class' => 'card-body']);
        echo html_writer::tag('h5', $avg_days_service, ['class' => 'card-title text-info']);
        echo html_writer::tag('p', 'Avg Days in Service', ['class' => 'card-text']);
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
        echo html_writer::end_div();

        echo html_writer::start_div('col-md-3');
        echo html_writer::start_tag('div', ['class' => 'card text-center']);
        echo html_writer::start_tag('div', ['class' => 'card-body']);
        echo html_writer::tag('h5', $total_checkouts, ['class' => 'card-title text-warning']);
        echo html_writer::tag('p', 'Total Checkouts Lost', ['class' => 'card-text']);
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
        echo html_writer::end_div();

        echo html_writer::start_div('col-md-3');
        echo html_writer::start_tag('div', ['class' => 'card text-center']);
        echo html_writer::start_tag('div', ['class' => 'card-body']);
        $most_common_reason = !empty($reason_counts) ? array_keys($reason_counts, max($reason_counts))[0] : 'N/A';
        echo html_writer::tag('h5', ucfirst($most_common_reason), ['class' => 'card-title text-secondary']);
        echo html_writer::tag('p', 'Most Common Reason', ['class' => 'card-text']);
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
        echo html_writer::end_div();

        echo html_writer::end_tag('div');

        // Detailed table
        echo html_writer::start_tag('div', ['class' => 'table-responsive']);
        echo html_writer::start_tag('table', ['class' => 'table table-striped table-hover']);
        echo html_writer::start_tag('thead', ['class' => 'table-dark']);
        echo html_writer::start_tag('tr');
        echo html_writer::tag('th', 'Product');
        echo html_writer::tag('th', 'UUID');
        echo html_writer::tag('th', 'Serial #');
        echo html_writer::tag('th', 'Reason');
        echo html_writer::tag('th', 'Removed Date');
        echo html_writer::tag('th', 'Removed By');
        echo html_writer::tag('th', 'Last User');
        echo html_writer::tag('th', 'Days in Service');
        echo html_writer::tag('th', 'Total Checkouts');
        echo html_writer::tag('th', 'Location');
        echo html_writer::tag('th', 'Condition');
        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thead');
        echo html_writer::start_tag('tbody');

        foreach ($removed_items as $item) {
            echo html_writer::start_tag('tr');

            // Product info
            echo html_writer::start_tag('td');
            echo html_writer::tag('strong', $item->product_name);
            if ($item->manufacturer || $item->model) {
                echo html_writer::empty_tag('br');
                echo html_writer::tag(
                    'small',
                    ($item->manufacturer ? $item->manufacturer : '') .
                        ($item->manufacturer && $item->model ? ' - ' : '') .
                        ($item->model ? $item->model : ''),
                    ['class' => 'text-muted']
                );
            }
            echo html_writer::end_tag('td');

            // UUID (truncated with tooltip)
            echo html_writer::tag(
                'td',
                html_writer::tag('code', substr($item->uuid, 0, 8) . '...', [
                    'title' => $item->uuid,
                    'data-bs-toggle' => 'tooltip'
                ])
            );

            // Serial number
            echo html_writer::tag('td', $item->serial_number ?: '-');

            // Removal reason with color coding
            $reason_class = '';
            switch ($item->removal_reason) {
                case 'damaged':
                    $reason_class = 'text-warning';
                    break;
                case 'lost':
                case 'stolen':
                    $reason_class = 'text-danger';
                    break;
                case 'end-of-life':
                case 'disposed':
                    $reason_class = 'text-secondary';
                    break;
                default:
                    $reason_class = 'text-muted';
            }
            echo html_writer::tag(
                'td',
                html_writer::tag('span', ucfirst($item->removal_reason ?: 'Unknown'), ['class' => $reason_class])
            );

            // Removal date
            echo html_writer::tag('td', $item->removal_date ? date('M j, Y', $item->removal_date) : '-');

            // Removed by
            echo html_writer::tag('td', $item->removed_by_name ?: '-');

            // Last user
            echo html_writer::tag('td', $item->last_user_name ?: 'None');

            // Days in service
            $days_class = $item->days_in_service < 30 ? 'text-danger' : ($item->days_in_service < 365 ? 'text-warning' : 'text-success');
            echo html_writer::tag(
                'td',
                html_writer::tag('span', $item->days_in_service, ['class' => $days_class])
            );

            // Total checkouts
            echo html_writer::tag('td', $item->total_checkouts);

            // Location
            echo html_writer::tag('td', $item->location_name ?: '-');

            // Condition
            $condition_class = '';
            switch ($item->condition_status) {
                case 'excellent':
                    $condition_class = 'text-success';
                    break;
                case 'good':
                    $condition_class = 'text-info';
                    break;
                case 'fair':
                    $condition_class = 'text-warning';
                    break;
                case 'poor':
                case 'needs_repair':
                    $condition_class = 'text-danger';
                    break;
                default:
                    $condition_class = 'text-muted';
            }
            echo html_writer::tag(
                'td',
                html_writer::tag('span', ucfirst($item->condition_status ?: 'Unknown'), [
                    'class' => $condition_class,
                    'title' => $item->condition_notes ?: 'No notes',
                    'data-bs-toggle' => 'tooltip'
                ])
            );

            echo html_writer::end_tag('tr');
        }

        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');
        echo html_writer::end_tag('div');
    } else {
        echo html_writer::start_tag('div', ['class' => 'alert alert-info']);
        echo html_writer::tag('h4', 'No Removed Items Found', ['class' => 'alert-heading']);
        echo html_writer::tag('p', 'There are no equipment items matching your filter criteria that have been removed from inventory.');
        if ($product_filter || $reason_filter || $date_from || $date_to || $removed_by_filter) {
            echo html_writer::tag('p', 'Try adjusting your filters or ');
            echo html_writer::link(
                new moodle_url('/local/equipment/inventory/removal_history.php'),
                'clear all filters',
                ['class' => 'alert-link']
            );
            echo ' to see all removed items.';
        }
        echo html_writer::end_tag('div');
    }
} catch (Exception $e) {
    echo html_writer::tag('div', 'Error loading removal history: ' . $e->getMessage(), ['class' => 'alert alert-danger']);
}

// Navigation links
echo html_writer::start_tag('div', ['class' => 'mt-4']);
echo html_writer::link(
    new moodle_url('/local/equipment/inventory/manage.php'),
    '← Back to Inventory Management',
    ['class' => 'btn btn-outline-primary']
);
echo html_writer::end_tag('div');

echo $OUTPUT->footer();

/**
 * Get removed items data with filtering
 */
function get_removed_items_data($product_filter, $reason_filter, $date_from, $date_to, $removed_by_filter) {
    global $DB;

    $where_conditions = ["ei.status = 'removed'"];
    $params = [];

    if ($product_filter) {
        $where_conditions[] = "ei.productid = ?";
        $params[] = $product_filter;
    }

    if ($reason_filter) {
        $where_conditions[] = "ei.removal_reason = ?";
        $params[] = $reason_filter;
    }

    if ($date_from) {
        $where_conditions[] = "ei.removal_date >= ?";
        $params[] = strtotime($date_from);
    }

    if ($date_to) {
        $where_conditions[] = "ei.removal_date <= ?";
        $params[] = strtotime($date_to . ' 23:59:59');
    }

    if ($removed_by_filter) {
        $where_conditions[] = "ei.removed_by = ?";
        $params[] = $removed_by_filter;
    }

    $where_clause = implode(' AND ', $where_conditions);

    $sql = "
        SELECT ei.*,
               p.name as product_name,
               p.manufacturer,
               p.model,
               p.category,
               p.is_consumable,
               l.name as location_name,
               CONCAT(rb.firstname, ' ', rb.lastname) as removed_by_name,
               CONCAT(lu.firstname, ' ', lu.lastname) as last_user_name,
               COALESCE(
                   CASE
                       WHEN ei.removal_date > 0 AND ei.timecreated > 0
                       THEN ROUND((ei.removal_date - ei.timecreated) / 86400)
                       ELSE 0
                   END, 0
               ) as days_in_service,
               COALESCE(checkout_counts.total_checkouts, 0) as total_checkouts
        FROM {local_equipment_items} ei
        JOIN {local_equipment_products} p ON ei.productid = p.id
        LEFT JOIN {local_equipment_locations} l ON ei.locationid = l.id
        LEFT JOIN {user} rb ON ei.removed_by = rb.id
        LEFT JOIN {user} lu ON ei.current_userid = lu.id
        LEFT JOIN (
            SELECT itemid, COUNT(*) as total_checkouts
            FROM {local_equipment_transactions}
            WHERE transaction_type = 'checkout'
            GROUP BY itemid
        ) checkout_counts ON ei.id = checkout_counts.itemid
        WHERE {$where_clause}
        ORDER BY ei.removal_date DESC, ei.timemodified DESC
    ";

    return $DB->get_records_sql($sql, $params);
}
