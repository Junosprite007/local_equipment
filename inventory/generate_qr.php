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
 * QR code generator for inventory management.
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
admin_externalpage_setup('local_equipment_inventory_qr');

// Set up admin external page
admin_externalpage_setup('local_equipment_inventory_qr');

// Handle form submission
$count = optional_param('count', 24, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('generateqrcodes', 'local_equipment'));

// Display form
echo html_writer::start_tag('form', ['method' => 'post', 'action' => '']);

echo html_writer::start_div('form-group mb-3');
echo html_writer::tag('label', get_string('numberofcodes', 'local_equipment'), ['for' => 'count', 'class' => 'form-label']);
echo html_writer::empty_tag('input', [
    'type' => 'number',
    'id' => 'count',
    'name' => 'count',
    'value' => $count,
    'min' => 1,
    'max' => 100,
    'class' => 'form-control',
    'style' => 'width: 200px;'
]);
echo html_writer::tag('small', 'Generate 1-100 QR codes (default: 24 for 4x6 grid)', ['class' => 'form-text text-muted']);
echo html_writer::end_div();

echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'generate']);
echo html_writer::tag('button', get_string('generatesheet', 'local_equipment'), [
    'type' => 'submit',
    'class' => 'btn btn-primary'
]);

echo html_writer::end_tag('form');

// Generate QR codes if requested
if ($action === 'generate' && $count > 0) {
    try {
        $qr_generator = new \local_equipment\inventory\qr_generator();

        echo html_writer::tag('h3', 'Generated QR Codes', ['class' => 'mt-4']);
        echo html_writer::tag('p', "Generating {$count} QR codes...", ['class' => 'alert alert-info']);

        // Generate the printable sheet
        $sheet_data = $qr_generator->generate_printable_sheet($count);

        if ($sheet_data && isset($sheet_data['html'])) {
            echo html_writer::tag('p', '✓ QR codes generated successfully!', ['class' => 'alert alert-success']);

            // Add CSS for the sheet
            echo html_writer::start_tag('style');
            echo $sheet_data['css'];
            echo html_writer::end_tag('style');

            // Display the sheet
            echo html_writer::start_div('qr-sheet-container', ['style' => 'border: 1px solid #ccc; padding: 20px; background: white;']);
            echo $sheet_data['html'];
            echo html_writer::end_div();

            // Print button
            echo html_writer::tag('button', get_string('printsheet', 'local_equipment'), [
                'onclick' => 'window.print();',
                'class' => 'btn btn-success mt-3'
            ]);

            // Show generated UUIDs for reference
            echo html_writer::tag('h4', 'Generated UUIDs:', ['class' => 'mt-4']);
            echo html_writer::start_tag('div', ['class' => 'alert alert-info']);
            echo html_writer::start_tag('small');
            foreach ($sheet_data['qr_codes'] as $index => $qr_code) {
                echo html_writer::tag('div', ($index + 1) . '. ' . $qr_code['uuid']);
            }
            echo html_writer::end_tag('small');
            echo html_writer::end_tag('div');
        } else {
            echo html_writer::tag('p', '✗ Failed to generate QR codes', ['class' => 'alert alert-danger']);
        }
    } catch (Exception $e) {
        echo html_writer::tag('p', '✗ Error: ' . $e->getMessage(), ['class' => 'alert alert-danger']);
    }
}

// Add print styles
echo html_writer::start_tag('style');
echo '
@media print {
    .btn, .form-group, h1, h3, .alert, nav, .navbar, .breadcrumb {
        display: none !important;
    }
    .qr-sheet-container {
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    body {
        margin: 0;
        padding: 0;
    }
}
';
echo html_writer::end_tag('style');

echo $OUTPUT->footer();
