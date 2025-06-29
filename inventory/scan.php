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
 * Universal barcode scanning interface.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Check login and capabilities
require_login();
$context = context_system::instance();
require_capability('local/equipment:checkinout', $context);

// Set up page
$PAGE->set_url('/local/equipment/inventory/scan.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('scanequipment', 'local_equipment'));
$PAGE->set_heading(get_string('scanequipment', 'local_equipment'));
$PAGE->set_pagelayout('admin');

// Add CSS and JavaScript
$PAGE->requires->css('/local/equipment/styles/scanner.css');
$PAGE->requires->js_call_amd('local/equipment/universal-scanner', 'init', [
    [
        'containerId' => 'scanner-container',
        'resultCallback' => 'handleScanResult',
        'errorCallback' => 'handleScanError'
    ]
]);

echo $OUTPUT->header();

echo html_writer::start_div('equipment-scanner-page');

// Page header
echo html_writer::tag('h2', get_string('scanequipment', 'local_equipment'), ['class' => 'page-title']);
echo html_writer::tag('p', get_string('scanequipment_desc', 'local_equipment'), ['class' => 'page-description']);

// Scanner tabs
echo html_writer::start_div('scanner-tabs');
echo html_writer::start_tag('ul', ['class' => 'nav nav-tabs', 'role' => 'tablist']);

echo html_writer::start_tag('li', ['class' => 'nav-item']);
echo html_writer::link('#camera-tab', get_string('camerascan', 'local_equipment'), [
    'class' => 'nav-link active',
    'data-bs-toggle' => 'tab',
    'role' => 'tab'
]);
echo html_writer::end_tag('li');

echo html_writer::start_tag('li', ['class' => 'nav-item']);
echo html_writer::link('#upload-tab', get_string('uploadscan', 'local_equipment'), [
    'class' => 'nav-link',
    'data-bs-toggle' => 'tab',
    'role' => 'tab'
]);
echo html_writer::end_tag('li');

echo html_writer::start_tag('li', ['class' => 'nav-item']);
echo html_writer::link('#manual-tab', get_string('manualscan', 'local_equipment'), [
    'class' => 'nav-link',
    'data-bs-toggle' => 'tab',
    'role' => 'tab'
]);
echo html_writer::end_tag('li');

echo html_writer::end_tag('ul');
echo html_writer::end_div(); // scanner-tabs

// Tab content
echo html_writer::start_div('tab-content');

// Camera scanning tab
echo html_writer::start_div('tab-pane fade show active', ['id' => 'camera-tab', 'role' => 'tabpanel']);
echo html_writer::tag('h3', get_string('camerascan', 'local_equipment'));
echo html_writer::tag('p', get_string('camerascan_instructions', 'local_equipment'));

echo html_writer::start_div('scanner-controls mb-3');
echo html_writer::tag('button', get_string('startscan', 'local_equipment'), [
    'id' => 'start-scan-btn',
    'class' => 'btn btn-primary me-2'
]);
echo html_writer::tag('button', get_string('stopscan', 'local_equipment'), [
    'id' => 'stop-scan-btn',
    'class' => 'btn btn-secondary',
    'disabled' => 'disabled'
]);
echo html_writer::end_div();

echo html_writer::div('', 'scanner-container', ['id' => 'scanner-container']);
echo html_writer::end_div(); // camera-tab

// File upload tab
echo html_writer::start_div('tab-pane fade', ['id' => 'upload-tab', 'role' => 'tabpanel']);
echo html_writer::tag('h3', get_string('uploadscan', 'local_equipment'));
echo html_writer::tag('p', get_string('uploadscan_instructions', 'local_equipment'));

echo html_writer::start_div('upload-area');
echo html_writer::start_tag('form', ['id' => 'upload-form', 'enctype' => 'multipart/form-data']);
echo html_writer::start_div('form-group mb-3');
echo html_writer::tag('label', get_string('selectimage', 'local_equipment'), [
    'for' => 'image-upload',
    'class' => 'form-label'
]);
echo html_writer::empty_tag('input', [
    'type' => 'file',
    'id' => 'image-upload',
    'name' => 'image',
    'class' => 'form-control',
    'accept' => 'image/*'
]);
echo html_writer::end_div();

echo html_writer::tag('button', get_string('processimage', 'local_equipment'), [
    'type' => 'submit',
    'class' => 'btn btn-primary'
]);
echo html_writer::end_tag('form');
echo html_writer::end_div(); // upload-area

echo html_writer::div('', 'upload-preview', ['id' => 'upload-preview']);
echo html_writer::end_div(); // upload-tab

// Manual entry tab
echo html_writer::start_div('tab-pane fade', ['id' => 'manual-tab', 'role' => 'tabpanel']);
echo html_writer::tag('h3', get_string('manualscan', 'local_equipment'));
echo html_writer::tag('p', get_string('manualscan_instructions', 'local_equipment'));

echo html_writer::start_tag('form', ['id' => 'manual-form']);
echo html_writer::start_div('form-group mb-3');
echo html_writer::tag('label', get_string('barcodedata', 'local_equipment'), [
    'for' => 'manual-barcode',
    'class' => 'form-label'
]);
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'id' => 'manual-barcode',
    'name' => 'barcode',
    'class' => 'form-control',
    'placeholder' => get_string('enterbarcode', 'local_equipment')
]);
echo html_writer::end_div();

echo html_writer::start_div('form-group mb-3');
echo html_writer::tag('label', get_string('scantype', 'local_equipment'), [
    'for' => 'scan-type',
    'class' => 'form-label'
]);
echo html_writer::start_tag('select', [
    'id' => 'scan-type',
    'name' => 'scan_type',
    'class' => 'form-select'
]);
echo html_writer::tag('option', get_string('autodetect', 'local_equipment'), ['value' => 'auto']);
echo html_writer::tag('option', get_string('qrcode', 'local_equipment'), ['value' => 'qr']);
echo html_writer::tag('option', get_string('upccode', 'local_equipment'), ['value' => 'upc']);
echo html_writer::end_tag('select');
echo html_writer::end_div();

echo html_writer::tag('button', get_string('processscan', 'local_equipment'), [
    'type' => 'submit',
    'class' => 'btn btn-primary'
]);
echo html_writer::end_tag('form');
echo html_writer::end_div(); // manual-tab

echo html_writer::end_div(); // tab-content

// Scan results area
echo html_writer::start_div('scan-results mt-4', ['id' => 'scan-results']);
echo html_writer::tag('h3', get_string('scanresults', 'local_equipment'));
echo html_writer::div('', 'results-content', ['id' => 'results-content']);
echo html_writer::end_div(); // scan-results

// Action buttons area (shown after successful scan)
echo html_writer::start_div('scan-actions mt-3 d-none', ['id' => 'scan-actions']);
echo html_writer::tag('h4', get_string('availableactions', 'local_equipment'));
echo html_writer::div('', 'action-buttons', ['id' => 'action-buttons']);
echo html_writer::end_div(); // scan-actions

echo html_writer::end_div(); // equipment-scanner-page

// Add JavaScript for handling interactions
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let scanner = null;

        // Initialize scanner when camera tab is activated
        document.getElementById('start-scan-btn').addEventListener('click', async function() {
            const scannerModule = await import('/local/equipment/amd/build/universal-scanner.min.js');
            scanner = scannerModule.init({
                containerId: 'scanner-container',
                resultCallback: handleScanResult,
                errorCallback: handleScanError
            });

            const success = await scanner.init();
            if (success) {
                await scanner.startScanning();
                this.disabled = true;
                document.getElementById('stop-scan-btn').disabled = false;
            }
        });

        // Stop scanning
        document.getElementById('stop-scan-btn').addEventListener('click', function() {
            if (scanner) {
                scanner.stopScanning();
                document.getElementById('start-scan-btn').disabled = false;
                this.disabled = true;
            }
        });

        // Handle file upload
        document.getElementById('upload-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const fileInput = document.getElementById('image-upload');
            const file = fileInput.files[0];

            if (!file) {
                alert('Please select an image file');
                return;
            }

            if (!scanner) {
                const scannerModule = await import('/local/equipment/amd/build/universal-scanner.min.js');
                scanner = scannerModule.init();
                await scanner.init();
            }

            try {
                const result = await scanner.processFileUpload(file);
                handleScanResult(result);
            } catch (error) {
                handleScanError('file_processing_failed', error.message);
            }
        });

        // Handle manual entry
        document.getElementById('manual-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const barcodeInput = document.getElementById('manual-barcode');
            const scanTypeSelect = document.getElementById('scan-type');

            const barcodeData = barcodeInput.value.trim();
            const scanType = scanTypeSelect.value;

            if (!barcodeData) {
                alert('Please enter barcode data');
                return;
            }

            if (!scanner) {
                const scannerModule = await import('/local/equipment/amd/build/universal-scanner.min.js');
                scanner = scannerModule.init();
                await scanner.init();
            }

            try {
                const result = await scanner.processManualEntry(barcodeData, scanType);
                handleScanResult(result);
            } catch (error) {
                handleScanError('manual_entry_failed', error.message);
            }
        });
    });

    // Handle successful scan results
    function handleScanResult(result) {
        const resultsContent = document.getElementById('results-content');
        const scanActions = document.getElementById('scan-actions');
        const actionButtons = document.getElementById('action-buttons');

        if (!result.success) {
            handleScanError(result.error_code, result.message);
            return;
        }

        const data = result.data;
        let html = '<div class="alert alert-success">Scan successful!</div>';

        if (data.scan_type === 'qr' && data.item) {
            // QR code scan - show equipment item details
            html += '<div class="card">';
            html += '<div class="card-header"><h5>Equipment Item</h5></div>';
            html += '<div class="card-body">';
            html += `<p><strong>Product:</strong> ${data.product.name}</p>`;
            html += `<p><strong>Status:</strong> <span class="badge bg-${getStatusColor(data.item.status)}">${data.item.status}</span></p>`;
            html += `<p><strong>Condition:</strong> ${data.item.condition_status}</p>`;

            if (data.location) {
                html += `<p><strong>Location:</strong> ${data.location.name}</p>`;
            }

            if (data.current_user) {
                html += `<p><strong>Current User:</strong> ${data.current_user.firstname} ${data.current_user.lastname}</p>`;
            }

            if (data.item.student_label) {
                html += `<p><strong>Student Label:</strong> ${data.item.student_label}</p>`;
            }

            html += '</div></div>';

            // Show available actions
            if (data.available_actions && data.available_actions.length > 0) {
                let actionsHtml = '';
                data.available_actions.forEach(action => {
                    const buttonClass = getActionButtonClass(action);
                    const actionText = getActionText(action);
                    actionsHtml += `<button class="btn ${buttonClass} me-2" onclick="performAction('${action}', '${data.item.uuid}')">${actionText}</button>`;
                });
                actionButtons.innerHTML = actionsHtml;
                scanActions.classList.remove('d-none');
            }

        } else if (data.scan_type === 'upc') {
            // UPC scan - show product information
            html += '<div class="card">';
            html += '<div class="card-header"><h5>Product Information</h5></div>';
            html += '<div class="card-body">';

            if (data.source === 'local_database') {
                html += `<p><strong>Product:</strong> ${data.product.name}</p>`;
                html += `<p><strong>Available Items:</strong> ${data.item_count}</p>`;
                html += `<p><strong>Manufacturer:</strong> ${data.product.manufacturer || 'N/A'}</p>`;
                html += `<p><strong>Category:</strong> ${data.product.category || 'N/A'}</p>`;
            } else if (data.source === 'brocade_api') {
                html += `<p><strong>Product:</strong> ${data.product_info.name}</p>`;
                html += `<p><strong>Manufacturer:</strong> ${data.product_info.manufacturer || 'N/A'}</p>`;
                html += `<p><strong>Description:</strong> ${data.product_info.description || 'N/A'}</p>`;
                html += `<p><strong>UPC:</strong> ${data.product_info.upc}</p>`;

                if (data.can_add_to_catalog) {
                    actionButtons.innerHTML = '<button class="btn btn-primary" onclick="addToCatalog()">Add to Product Catalog</button>';
                    scanActions.classList.remove('d-none');
                }
            }

            html += '</div></div>';
        }

        resultsContent.innerHTML = html;
    }

    // Handle scan errors
    function handleScanError(errorCode, message) {
        const resultsContent = document.getElementById('results-content');
        const scanActions = document.getElementById('scan-actions');

        resultsContent.innerHTML = `<div class="alert alert-danger">Error: ${message}</div>`;
        scanActions.classList.add('d-none');
    }

    // Helper functions
    function getStatusColor(status) {
        const colors = {
            'available': 'success',
            'checked_out': 'warning',
            'in_transit': 'info',
            'maintenance': 'secondary',
            'damaged': 'danger',
            'lost': 'dark'
        };
        return colors[status] || 'secondary';
    }

    function getActionButtonClass(action) {
        const classes = {
            'checkout': 'btn-primary',
            'checkin': 'btn-success',
            'transfer': 'btn-info',
            'update_condition': 'btn-warning',
            'complete_transfer': 'btn-success',
            'view_assignment': 'btn-outline-primary',
            'view_transfer': 'btn-outline-info'
        };
        return classes[action] || 'btn-secondary';
    }

    function getActionText(action) {
        const texts = {
            'checkout': 'Check Out',
            'checkin': 'Check In',
            'transfer': 'Transfer',
            'update_condition': 'Update Condition',
            'complete_transfer': 'Complete Transfer',
            'view_assignment': 'View Assignment',
            'view_transfer': 'View Transfer'
        };
        return texts[action] || action;
    }

    // Action handlers
    function performAction(action, itemUuid) {
        // Redirect to appropriate action page
        const baseUrl = '/local/equipment/inventory/';
        let url = '';

        switch (action) {
            case 'checkout':
                url = `${baseUrl}checkout.php?uuid=${itemUuid}`;
                break;
            case 'checkin':
                url = `${baseUrl}checkin.php?uuid=${itemUuid}`;
                break;
            case 'transfer':
                url = `${baseUrl}transfer.php?uuid=${itemUuid}`;
                break;
            case 'update_condition':
                url = `${baseUrl}condition.php?uuid=${itemUuid}`;
                break;
            default:
                alert('Action not implemented yet');
                return;
        }

        window.location.href = url;
    }

    function addToCatalog() {
        // Redirect to add product page with pre-filled data
        window.location.href = '/local/equipment/inventory/products.php?action=add';
    }
</script>

<?php

echo $OUTPUT->footer();
