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

// Handle form submission
$count = optional_param('count', 28, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('generateqrcodes', 'local_equipment'));

// Display form
echo html_writer::start_tag('form', ['method' => 'post', 'action' => '']);

echo html_writer::start_div('mb-3');
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
echo html_writer::tag('small', 'Generate 1-100 QR codes (default: 28 for 5x6 grid)', ['class' => 'form-text text-muted']);
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

            // Print button - opens print-optimized page
            echo html_writer::tag('button', get_string('printsheet', 'local_equipment'), [
                'onclick' => 'openPrintWindow();',
                'class' => 'btn btn-success mt-3'
            ]);

            // Add JavaScript for print window
            echo html_writer::start_tag('script');
            echo '
            function openPrintWindow() {
                var printWindow = window.open("", "_blank", "width=800,height=600");
                var qrContent = document.querySelector(".qr-sheet-container").innerHTML;

                printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>QR Code Sheet</title>
                    <style>
                        @page {
                            size: 8.5in 11in;
                            margin: 0.5in;
                        }

                        body {
                            margin: 0;
                            padding: 0;
                            background: white;
                            font-family: Arial, sans-serif;
                        }

                        .qr-sheet-container {
                            width: 100%;
                            height: 100%;
                        }

                        .qr-sheet {
                            display: grid;
                            grid-template-columns: repeat(4, 1fr);
                            grid-template-rows: repeat(6, 1fr);
                            gap: 2px;
                            width: 100%;
                            min-height: 9in;
                        }

                        .qr-cell {
                            border: 1px solid #ccc;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            padding: 2px;
                            background: white;
                            min-height: 1.4in;
                        }

                        .qr-code, .qr-cell img {
                            max-width: 90%;
                            max-height: 90%;
                            width: auto;
                            height: auto;
                        }

                        @media print {
                            body { margin: 0; padding: 0; }
                            .qr-sheet-container { page-break-inside: avoid; }
                            .qr-cell { page-break-inside: avoid; }
                        }
                    </style>
                </head>
                <body>
                    <div class="qr-sheet-container">` + qrContent + `</div>
                </body>
                </html>
                `);

                printWindow.document.close();
                printWindow.focus();

                // Wait for content to load, then print
                setTimeout(function() {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            }
            ';
            echo html_writer::end_tag('script');

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

// Add simplified print styles that work with the QR generator's CSS
echo html_writer::start_tag('style');
echo '
@media print {
    /* Page setup */
    @page {
        size: 8.5in 11in;
        margin: 0.5in;
    }

    /* Hide everything except QR content */
    body > *:not(.qr-sheet-container) {
        display: none !important;
    }

    /* Hide Moodle navigation and UI */
    #page-header, #page-navbar, #page-footer, .navbar, .breadcrumb,
    .btn, .form-group, h1, h2, h3, h4, h5, h6, .alert,
    .page-header-headings, .context-header-settings-menu,
    .skiplinks, .sr-only, .visually-hidden {
        display: none !important;
    }

    /* Ensure body and html are clean */
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        color: black !important;
        font-size: 12pt !important;
    }

    /* Make sure QR container is visible and takes full space */
    .qr-sheet-container {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: static !important;
        width: 100% !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        background: white !important;
        page-break-inside: avoid !important;
    }

    /* Ensure QR sheet is visible */
    .qr-sheet {
        display: grid !important;
        visibility: visible !important;
        opacity: 1 !important;
        grid-template-columns: repeat(4, 1fr) !important;
        grid-template-rows: repeat(6, 1fr) !important;
        gap: 2px !important;
        width: 100% !important;
        height: auto !important;
        min-height: 9in !important;
        page-break-inside: avoid !important;
    }

    /* QR cells */
    .qr-cell {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid #ccc !important;
        padding: 2px !important;
        background: white !important;
        min-height: 1.4in !important;
        max-height: 1.4in !important;
        page-break-inside: avoid !important;
    }

    /* QR code images */
    .qr-code, .qr-cell img {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        max-width: 90% !important;
        max-height: 90% !important;
        width: auto !important;
        height: auto !important;
        margin: auto !important;
        border: none !important;
        background: white !important;
    }

    /* Force visibility of all QR-related elements */
    .qr-sheet-container,
    .qr-sheet-container *,
    .qr-sheet,
    .qr-sheet *,
    .qr-cell,
    .qr-cell *,
    .qr-code {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}

/* Screen styles for preview */
@media screen {
    .qr-sheet-container {
        max-width: 8.5in;
        margin: 20px auto;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border: 1px solid #ccc;
        padding: 20px;
        background: white;
    }

    .qr-sheet {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        grid-template-rows: repeat(6, 1fr);
        gap: 3px;
        aspect-ratio: 8.5/11;
    }

    .qr-cell {
        border: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px;
        background: white;
        position: relative;
    }

    .qr-cell::after {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        border: 1px dashed #ccc;
        pointer-events: none;
    }

    .qr-code {
        max-width: 85%;
        max-height: 85%;
        width: auto;
        height: auto;
    }
}
';
echo html_writer::end_tag('style');

echo $OUTPUT->footer();
