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

    // Prepare template context
    $template_context = [
        'summary' => [
            'total_items' => $summary->total_items,
            'available' => $summary->available,
            'checked_out' => $summary->checked_out,
            'in_transit' => $summary->in_transit,
            'maintenance' => $summary->maintenance,
            'damaged' => $summary->damaged,
            'lost' => $summary->lost ?? 0,
            'recent_transactions' => $summary->recent_transactions,
        ],
        'actions' => [
            [
                'title' => get_string('additems', 'local_equipment'),
                'description' => get_string('additemsdesc', 'local_equipment'),
                'url' => (new moodle_url('/local/equipment/inventory/add_items.php'))->out(false),
                'button_text' => get_string('additems', 'local_equipment'),
                'button_class' => 'btn btn-success',
                'icon' => 'fa-plus',
            ],
            [
                'title' => get_string('removeitems', 'local_equipment'),
                'description' => get_string('removeitemsdesc', 'local_equipment'),
                'url' => (new moodle_url('/local/equipment/inventory/remove_items.php'))->out(false),
                'button_text' => get_string('removeitems', 'local_equipment'),
                'button_class' => 'btn btn-danger',
                'icon' => 'fa-trash',
            ],
            [
                'title' => get_string('managelocations', 'local_equipment'),
                'description' => get_string('managelocationsdesc', 'local_equipment'),
                'url' => (new moodle_url('/local/equipment/inventory/locations.php'))->out(false),
                'button_text' => get_string('managelocations', 'local_equipment'),
                'button_class' => 'btn btn-info',
                'icon' => 'fa-map-marker-alt',
            ],
            [
                'title' => get_string('manageproducts', 'local_equipment'),
                'description' => get_string('manageproductsdesc', 'local_equipment'),
                'url' => (new moodle_url('/local/equipment/inventory/products.php'))->out(false),
                'button_text' => get_string('manageproducts', 'local_equipment'),
                'button_class' => 'btn btn-primary',
                'icon' => 'fa-boxes',
            ],
        ],
        'quick_actions' => [
            [
                'title' => get_string('generateqr', 'local_equipment'),
                'description' => get_string('generateqrdesc', 'local_equipment'),
                'url' => (new moodle_url('/local/equipment/inventory/generate_qr.php'))->out(false),
                'button_text' => get_string('generateqrcodes', 'local_equipment'),
                'button_class' => 'btn btn-primary',
                'icon' => 'fa-qrcode',
            ],
            [
                'title' => get_string('checkinout', 'local_equipment'),
                'description' => get_string('checkinoutdesc', 'local_equipment'),
                'url' => (new moodle_url('/local/equipment/inventory/check_inout.php'))->out(false),
                'button_text' => get_string('checkinout', 'local_equipment'),
                'button_class' => 'btn btn-primary',
                'icon' => 'fa-exchange-alt',
            ],
            [
                'title' => get_string('viewtransactions', 'local_equipment'),
                'description' => get_string('viewtransactionsdesc', 'local_equipment'),
                'url' => (new moodle_url('/local/equipment/inventory/transactions.php'))->out(false),
                'button_text' => get_string('viewtransactions', 'local_equipment'),
                'button_class' => 'btn btn-outline-primary',
                'icon' => 'fa-history',
            ],
        ],
    ];

    // Render the template
    echo $OUTPUT->render_from_template('local_equipment/inventory_dashboard', $template_context);
} catch (Exception $e) {
    echo $OUTPUT->notification('Error loading inventory data: ' . $e->getMessage(), 'error');
}

echo $OUTPUT->footer();
