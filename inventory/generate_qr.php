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
$action = optional_param('action', '', PARAM_ALPHAEXT);
$itemids = optional_param('itemids', '', PARAM_RAW);
$mode = optional_param('mode', '', PARAM_ALPHA);
$uuid = optional_param('uuid', '', PARAM_ALPHANUMEXT);

// Debug: Show all parameters received
echo html_writer::tag('div', 'DEBUG: Received action = "' . $action . '"', ['class' => 'alert alert-warning']);
echo html_writer::tag('div', 'DEBUG: Request method = ' . $_SERVER['REQUEST_METHOD'], ['class' => 'alert alert-warning']);
if (!empty($_POST)) {
    echo html_writer::tag('div', 'DEBUG: POST data = ' . print_r($_POST, true), ['class' => 'alert alert-warning']);
} else {
    echo html_writer::tag('div', 'DEBUG: No POST data received', ['class' => 'alert alert-warning']);
}

/**
 * Generate HTML for a printable QR code sheet from queue items.
 *
 * @param array $qr_codes Array of QR code data
 * @return string HTML content for the sheet
 */
function generate_queue_sheet_html($qr_codes) {
    $html = '<div class="qr-sheet">';

    // Generate cells for a 4x7 grid (28 total cells)
    for ($i = 0; $i < 28; $i++) {
        $html .= '<div class="qr-cell';

        // Add perforation line classes
        if ($i % 4 !== 0) {
            $html .= ' perforation-line-v';
        }
        if ($i >= 4) {
            $html .= ' perforation-line-h';
        }

        $html .= '">';

        // Only add QR code if we have one for this position
        if ($i < count($qr_codes) && isset($qr_codes[$i])) {
            $qr_code = $qr_codes[$i];
            $html .= '<img src="data:image/png;base64,' . $qr_code['qr_data'] . '" ';
            $html .= 'class="qr-code" alt="QR Code: ' . htmlspecialchars($qr_code['uuid']) . '">';
        }

        $html .= '</div>';
    }

    $html .= '</div>';
    return $html;
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('generateqrcodes', 'local_equipment'));

// Display form with both options
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
echo html_writer::tag('small', 'Generate 1-100 QR codes (default: 28 for 4x7 grid)', ['class' => 'form-text text-muted']);
echo html_writer::end_div();

// Add buttons for both random and queue generation
echo html_writer::start_div('mb-3');

// Random generation button
echo html_writer::tag('button', get_string('generatesheet', 'local_equipment'), [
    'type' => 'submit',
    'name' => 'action',
    'value' => 'generate',
    'class' => 'btn btn-primary me-2'
]);

// Queue generation button (only show if queue has items)
try {
    $print_manager = new \local_equipment\inventory\print_queue_manager();
    $queue_count = $print_manager->get_queue_count();
    $printed_count = $print_manager->get_printed_queue_count();

    if ($queue_count > 0) {
        echo html_writer::tag('button', 'Generate Queue (' . $queue_count . ' items)', [
            'type' => 'submit',
            'name' => 'action',
            'value' => 'generate_queue',
            'class' => 'btn btn-success me-2'
        ]);
    }

    // Clear printed items button (only show if there are printed items)
    if ($printed_count > 0) {
        echo html_writer::tag('button', get_string('clearprinteditems', 'local_equipment') . ' (' . $printed_count . ')', [
            'type' => 'button',
            'onclick' => 'confirmClearQueue()',
            'class' => 'btn btn-outline-danger'
        ]);
    }
} catch (Exception $e) {
    echo html_writer::tag('p', 'Error loading queue status: ' . $e->getMessage(), ['class' => 'alert alert-danger']);
}

echo html_writer::end_div();
echo html_writer::end_tag('form');

// Handle different modes of QR generation
if ($mode === 'single' && $uuid) {
    // Single QR code mode - for direct printing from item details
    try {
        $qr_generator = new \local_equipment\inventory\qr_generator();

        echo html_writer::tag('h3', 'Single QR Code', ['class' => 'mt-4']);

        // Generate single QR code
        $qr_data = $qr_generator->generate_item_qr($uuid, 200);

        if ($qr_data) {
            echo html_writer::tag('p', '✓ QR code generated successfully!', ['class' => 'alert alert-success']);

            // Display the QR code for printing
            echo html_writer::start_div('qr-sheet-container single-qr', ['style' => 'border: 1px solid #ccc; padding: 40px; background: white; text-align: center;']);
            echo html_writer::empty_tag('img', [
                'src' => 'data:image/png;base64,' . $qr_data,
                'alt' => 'QR Code: ' . $uuid,
                'style' => 'max-width: 300px; max-height: 300px;'
            ]);
            echo html_writer::tag('p', $uuid, ['style' => 'margin-top: 10px; font-family: monospace; font-size: 14px;']);
            echo html_writer::end_div();

            // Print button
            echo html_writer::tag('button', 'Print QR Code', [
                'onclick' => 'window.print();',
                'class' => 'btn btn-success mt-3'
            ]);
        } else {
            echo html_writer::tag('p', '✗ Failed to generate QR code', ['class' => 'alert alert-danger']);
        }
    } catch (Exception $e) {
        echo html_writer::tag('p', '✗ Error: ' . $e->getMessage(), ['class' => 'alert alert-danger']);
    }
} else if ($action === 'generate_queue' || $action === 'print_queue') {
    // Queue generation mode - works directly with queue items
    try {
        // for deluging purposes only.
        echo html_writer::tag('p', 'DEBUG: Queue generation started, action=' . $action, ['class' => 'alert alert-warning']);

        $print_manager = new \local_equipment\inventory\print_queue_manager();
        $qr_generator = new \local_equipment\inventory\qr_generator();

        $queue_items = $print_manager->get_unprinted_queue();

        if (empty($queue_items)) {
            echo html_writer::tag('p', 'No items in print queue.', ['class' => 'alert alert-info']);
        } else {
            echo html_writer::tag('h3', 'Generated QR Codes from Queue', ['class' => 'mt-4']);
            echo html_writer::tag('p', "Generating " . count($queue_items) . " QR codes from queue...", ['class' => 'alert alert-info']);

            // Generate QR codes for queue items
            $qr_codes = [];
            foreach ($queue_items as $item) {
                $qr_data = $qr_generator->generate_item_qr($item->uuid, 100);
                if ($qr_data) {
                    $qr_codes[] = [
                        'uuid' => $item->uuid,
                        'qr_data' => $qr_data,
                        'product_name' => $item->product_name
                    ];
                }
            }

            if (!empty($qr_codes)) {
                echo html_writer::tag('p', '✓ QR codes generated successfully!', ['class' => 'alert alert-success']);

                // Get CSS for the sheet
                $css = $qr_generator->get_printable_sheet_css();
                echo html_writer::start_tag('style');
                echo $css;
                echo html_writer::end_tag('style');

                // Generate HTML with queue QR codes
                $sheet_html = generate_queue_sheet_html($qr_codes);

                // Display the sheet
                echo html_writer::start_div('qr-sheet-container', ['style' => 'border: 1px solid #ccc; padding: 20px; background: white;']);
                echo $sheet_html;
                echo html_writer::end_div();

                // Print button
                echo html_writer::tag('button', get_string('printsheet', 'local_equipment'), [
                    'onclick' => 'openPrintWindow();',
                    'class' => 'btn btn-success mt-3'
                ]);

                // JavaScript for print window
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
                                grid-template-rows: repeat(7, 1fr);
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

                // Show queue UUIDs for reference
                echo html_writer::tag('h4', 'Queue UUIDs:', ['class' => 'mt-4']);
                echo html_writer::start_tag('div', ['class' => 'alert alert-info']);
                echo html_writer::start_tag('small');
                foreach ($qr_codes as $index => $qr_code) {
                    echo html_writer::tag('div', ($index + 1) . '. ' . $qr_code['product_name'] . ' - ' . $qr_code['uuid']);
                }
                echo html_writer::end_tag('small');
                echo html_writer::end_div();
            } else {
                echo html_writer::tag('p', '✗ No valid QR codes were generated', ['class' => 'alert alert-danger']);
            }
        }
    } catch (Exception $e) {
        echo html_writer::tag('p', '✗ Error: ' . $e->getMessage(), ['class' => 'alert alert-danger']);
    }
} else if ($action === 'generate_for_items' && $itemids) {
    // Print specific items mode - for session items
    try {
        $qr_generator = new \local_equipment\inventory\qr_generator();

        $item_ids = explode(',', $itemids);
        $item_ids = array_map('intval', $item_ids);

        if (empty($item_ids)) {
            echo html_writer::tag('p', 'No items specified.', ['class' => 'alert alert-warning']);
        } else {
            echo html_writer::tag('h3', 'Session Items (' . count($item_ids) . ' items)', ['class' => 'mt-4']);

            // Get item details
            list($in_sql, $params) = $DB->get_in_or_equal($item_ids);
            $sql = "SELECT i.uuid, p.name as product_name
                    FROM {local_equipment_items} i
                    JOIN {local_equipment_products} p ON i.productid = p.id
                    WHERE i.id $in_sql";
            $items = $DB->get_records_sql($sql, $params);

            if (empty($items)) {
                echo html_writer::tag('p', 'No valid items found.', ['class' => 'alert alert-warning']);
            } else {
                // Generate QR codes for items
                $qr_codes = [];
                foreach ($items as $item) {
                    $qr_data = $qr_generator->generate_item_qr($item->uuid, 100);
                    $qr_codes[] = [
                        'uuid' => $item->uuid,
                        'qr_data' => $qr_data,
                        'product_name' => $item->product_name
                    ];
                }

                // Generate the printable sheet
                $sheet_data = $qr_generator->generate_printable_sheet(count($qr_codes));

                // Replace the generated UUIDs with our specific ones
                for ($i = 0; $i < count($qr_codes) && $i < count($sheet_data['qr_codes']); $i++) {
                    $sheet_data['qr_codes'][$i] = $qr_codes[$i];
                }

                // Regenerate HTML with our specific QR codes
                $sheet_html = generate_queue_sheet_html($qr_codes);

                echo html_writer::tag('p', '✓ Session items ready for printing!', ['class' => 'alert alert-success']);

                // Add CSS for the sheet
                echo html_writer::start_tag('style');
                echo $sheet_data['css'];
                echo html_writer::end_tag('style');

                // Display the sheet
                echo html_writer::start_div('qr-sheet-container', ['style' => 'border: 1px solid #ccc; padding: 20px; background: white;']);
                echo $sheet_html;
                echo html_writer::end_div();

                // Print button
                echo html_writer::tag('button', 'Print Session Items', [
                    'onclick' => 'window.print();',
                    'class' => 'btn btn-success mt-3'
                ]);

                // Show session items details
                echo html_writer::tag('h4', 'Session Items:', ['class' => 'mt-4']);
                echo html_writer::start_tag('div', ['class' => 'alert alert-info']);
                echo html_writer::start_tag('small');
                foreach ($qr_codes as $index => $qr_code) {
                    echo html_writer::tag('div', ($index + 1) . '. ' . $qr_code['product_name'] . ' - ' . $qr_code['uuid']);
                }
                echo html_writer::end_tag('small');
                echo html_writer::end_tag('div');
            }
        }
    } catch (Exception $e) {
        echo html_writer::tag('p', '✗ Error: ' . $e->getMessage(), ['class' => 'alert alert-danger']);
    }
} else if ($action === 'clear_queue') {
    // Clear printed items from queue
    try {
        $print_manager = new \local_equipment\inventory\print_queue_manager();
        $cleared_count = $print_manager->clear_printed_items();

        if ($cleared_count > 0) {
            echo html_writer::tag('p', "✓ Successfully cleared {$cleared_count} printed items from the queue.", ['class' => 'alert alert-success']);
        } else {
            echo html_writer::tag('p', "No printed items found to clear.", ['class' => 'alert alert-info']);
        }

        // Add button to go back to main page
        echo html_writer::link(
            new moodle_url('/local/equipment/inventory/generate_qr.php'),
            '← Back to QR Generator',
            ['class' => 'btn btn-secondary mt-3']
        );
    } catch (Exception $e) {
        echo html_writer::tag('p', '✗ Error clearing queue: ' . $e->getMessage(), ['class' => 'alert alert-danger']);
    }
} else if ($action === 'generate' && $count > 0) {
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
                            grid-template-rows: repeat(7, 1fr);
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
        grid-template-rows: repeat(7, 1fr) !important;
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
        grid-template-rows: repeat(7, 1fr);
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

// Add JavaScript for confirmation dialog
echo html_writer::start_tag('script');
echo '
function confirmClearQueue() {
    if (confirm("' . get_string('clearqueueconfirm', 'local_equipment') . '")) {
        // Create form to submit clear_queue action
        var form = document.createElement("form");
        form.method = "POST";
        form.action = "";

        var actionInput = document.createElement("input");
        actionInput.type = "hidden";
        actionInput.name = "action";
        actionInput.value = "clear_queue";

        var sessKeyInput = document.createElement("input");
        sessKeyInput.type = "hidden";
        sessKeyInput.name = "sesskey";
        sessKeyInput.value = M.cfg.sesskey;

        form.appendChild(actionInput);
        form.appendChild(sessKeyInput);
        document.body.appendChild(form);
        form.submit();
    }
}
';
echo html_writer::end_tag('script');

echo $OUTPUT->footer();
