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
 * Add equipment items to inventory via UPC barcode scanning.
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
admin_externalpage_setup('local_equipment_inventory_additems');

// Load the scanner AMD module and CSS before header
$PAGE->requires->js_call_amd('local_equipment/add-items-scanner', 'init');
$PAGE->requires->css('/local/equipment/scss/scanner.scss');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('additems', 'local_equipment'));

// Get locations for dropdown
$locations = $DB->get_records_menu('local_equipment_locations', ['active' => 1], 'name ASC', 'id, name');

if (empty($locations)) {
    echo html_writer::tag(
        'div',
        'No active storage locations found. ' .
            html_writer::link(new moodle_url('/local/equipment/inventory/locations.php'), 'Add locations first') .
            ' before adding inventory items.',
        ['class' => 'alert alert-warning']
    );
    echo $OUTPUT->footer();
    exit;
}

// Instructions
echo html_writer::start_tag('div', ['class' => 'alert alert-info']);
echo html_writer::tag('h5', 'How to Add Items:', ['class' => 'alert-heading']);
echo html_writer::start_tag('ol');
echo html_writer::tag('li', 'Select the storage location where items will be placed');
echo html_writer::tag('li', 'Scan the UPC barcode on each item\'s packaging');
echo html_writer::tag('li', 'Each scan will automatically add one item to inventory');
echo html_writer::tag('li', 'Continue scanning until all items are added');
echo html_writer::end_tag('ol');
echo html_writer::tag(
    'p',
    html_writer::tag('strong', 'Note: ') .
        'If you scan a UPC that isn\'t in the system, you\'ll be prompted to add it as a new product type first.',
    ['class' => 'mb-0']
);
echo html_writer::end_tag('div');

// Location selection
echo html_writer::start_tag('div', ['class' => 'card mb-4']);
echo html_writer::start_tag('div', ['class' => 'card-header']);
echo html_writer::tag('h5', 'Step 1: Select Storage Location', ['class' => 'mb-0']);
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', ['class' => 'card-body']);
echo html_writer::start_div('mb-3');
echo html_writer::tag('label', 'Storage Location *', ['for' => 'location_select', 'class' => 'form-label']);
echo html_writer::start_tag('select', [
    'id' => 'location_select',
    'class' => 'form-select',
    'required' => true
]);
echo html_writer::tag('option', 'Select a location...', ['value' => '']);
foreach ($locations as $id => $name) {
    echo html_writer::tag('option', s($name), ['value' => $id]);
}
echo html_writer::end_tag('select');
echo html_writer::end_div();
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

// Scanner interface
echo html_writer::start_tag('div', ['class' => 'card mb-4', 'id' => 'scanner_card', 'style' => 'display: none;']);
echo html_writer::start_tag('div', ['class' => 'card-header']);
echo html_writer::tag('h5', 'Step 2: Scan UPC Barcodes', ['class' => 'mb-0']);
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', ['class' => 'card-body']);

// Scanner interface (will be populated by JavaScript)
echo html_writer::start_tag('div', ['id' => 'scanner_interface']);
echo html_writer::tag(
    'div',
    'Initializing scanner... Please wait.',
    ['class' => 'alert alert-info text-center p-4']
);
echo html_writer::end_tag('div');

// Manual UPC input (fallback and testing)
echo html_writer::start_div('mt-3');
echo html_writer::tag('label', 'Manual UPC Entry:', ['for' => 'manual_upc', 'class' => 'form-label']);
echo html_writer::start_tag('div', ['class' => 'input-group']);
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'id' => 'manual_upc',
    'class' => 'form-control',
    'placeholder' => 'Enter UPC code...',
    'disabled' => true
]);
echo html_writer::tag('button', 'Add Item', [
    'type' => 'button',
    'id' => 'add_item_btn',
    'class' => 'btn btn-primary',
    'disabled' => true
]);
echo html_writer::end_tag('div');
echo html_writer::end_div();

echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

// Session summary
echo html_writer::start_tag('div', ['class' => 'card', 'id' => 'session_summary', 'style' => 'display: none;']);
echo html_writer::start_tag('div', ['class' => 'card-header']);
echo html_writer::tag('h5', 'Session Summary', ['class' => 'mb-0']);
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', ['class' => 'card-body']);
echo html_writer::tag('p', 'Items added this session: <span id="session_count" class="badge bg-primary">0</span>');
echo html_writer::start_tag('div', ['id' => 'session_items']);
echo html_writer::end_tag('div');

// Print QR codes button
echo html_writer::tag('button', 'Print QR Codes for Session Items', [
    'type' => 'button',
    'id' => 'print_qr_btn',
    'class' => 'btn btn-success mt-3',
    'style' => 'display: none;'
]);

echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

// JavaScript for interface functionality
echo html_writer::start_tag('script');
echo '
document.addEventListener("DOMContentLoaded", function() {
    const locationSelect = document.getElementById("location_select");
    const scannerCard = document.getElementById("scanner_card");
    const sessionSummary = document.getElementById("session_summary");
    const manualUpc = document.getElementById("manual_upc");
    const addItemBtn = document.getElementById("add_item_btn");
    const sessionCount = document.getElementById("session_count");
    const sessionItems = document.getElementById("session_items");
    const printQrBtn = document.getElementById("print_qr_btn");

    let sessionItemCount = 0;
    let sessionItemIds = [];

    // Enable scanner when location is selected
    locationSelect.addEventListener("change", function() {
        if (this.value) {
            scannerCard.style.display = "block";
            sessionSummary.style.display = "block";
            manualUpc.disabled = false;
            addItemBtn.disabled = false;

            // Reset session when location changes
            sessionItemCount = 0;
            sessionItemIds = [];
            updateSessionDisplay();
        } else {
            scannerCard.style.display = "none";
            sessionSummary.style.display = "none";
            manualUpc.disabled = true;
            addItemBtn.disabled = true;
        }
    });

    // Handle manual UPC entry
    addItemBtn.addEventListener("click", function() {
        const upc = manualUpc.value.trim();
        if (upc) {
            processUPC(upc);
            manualUpc.value = "";
        }
    });

    // Allow Enter key in UPC input
    manualUpc.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            addItemBtn.click();
        }
    });

    function processUPC(upc) {
        const locationId = locationSelect.value;
        if (!locationId) {
            alert("Please select a location first");
            return;
        }

        // Show processing indicator
        addItemBtn.disabled = true;
        addItemBtn.textContent = "Processing...";

        // AJAX call to process UPC
        fetch("' . new moodle_url('/local/equipment/classes/external/validate_upc.php') . '", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                upc: upc,
                locationid: locationId,
                sesskey: "' . sesskey() . '"
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Item added successfully
                sessionItemCount++;
                sessionItemIds.push(data.itemid);
                updateSessionDisplay();
                showSuccessMessage(data.product_name);
            } else {
                // Error occurred
                showErrorMessage(data.message, data.product_url);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showErrorMessage("Network error occurred. Please try again.");
        })
        .finally(() => {
            addItemBtn.disabled = false;
            addItemBtn.textContent = "Add Item";
        });
    }

    function updateSessionDisplay() {
        sessionCount.textContent = sessionItemCount;
        if (sessionItemCount > 0) {
            printQrBtn.style.display = "inline-block";
        } else {
            printQrBtn.style.display = "none";
        }
    }

    function showSuccessMessage(productName) {
        const alert = document.createElement("div");
        alert.className = "alert alert-success alert-dismissible fade show mt-2";
        alert.innerHTML = `
            <strong>✓ Success!</strong> Added ${productName} to inventory.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        sessionItems.appendChild(alert);

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 3000);
    }

    function showErrorMessage(message, productUrl = null) {
        const alert = document.createElement("div");
        alert.className = "alert alert-danger alert-dismissible fade show mt-2";
        let content = `<strong>✗ Error:</strong> ${message}`;
        if (productUrl) {
            content += ` <a href="${productUrl}" class="alert-link">Add this product type</a>`;
        }
        content += `<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        alert.innerHTML = content;
        sessionItems.appendChild(alert);
    }

    // Handle print QR codes button
    printQrBtn.addEventListener("click", function() {
        if (sessionItemIds.length > 0) {
            const url = "' . new moodle_url('/local/equipment/inventory/generate_qr.php') . '";
            const params = new URLSearchParams({
                action: "generate_for_items",
                itemids: sessionItemIds.join(","),
                sesskey: "' . sesskey() . '"
            });
            window.open(url + "?" + params.toString(), "_blank");
        }
    });
});
';
echo html_writer::end_tag('script');

echo $OUTPUT->footer();
