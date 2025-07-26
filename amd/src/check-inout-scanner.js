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
 * Check-in/Check-out scanner interface.
 *
 * @module     local_equipment/check-inout-scanner
 * @copyright  2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'local_equipment/universal-scanner',
    'core/notification',
    'core/log',
    'core/ajax',
], function (UniversalScanner, Notification, Log, Ajax) {
    'use strict';

    /**
     * Initialize the check-in/out scanner.
     */
    const init = () => {
        Log.debug('local_equipment/check-inout-scanner: init() called');

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeScanner);
        } else {
            initializeScanner();
        }
    };

    /**
     * Initialize the scanner interface.
     */
    function initializeScanner() {
        Log.debug(
            'local_equipment/check-inout-scanner: initializeScanner() called'
        );

        const scannerContainer = document.getElementById('scanner-container');
        const equipmentDetails = document.getElementById('equipment-details');
        const manualUuid = document.getElementById('manual_uuid');
        const lookupBtn = document.getElementById('lookup_btn');

        if (!scannerContainer || !equipmentDetails) {
            Log.error('Required elements not found');
            return;
        }

        let scanner = null;
        let currentEquipment = null;
        let currentMode = 'student'; // 'student' or 'location'

        // Initialize scanner interface
        initScanner();

        // Mode toggle handlers
        const modeRadios = document.querySelectorAll(
            'input[name="assignment_mode"]'
        );
        modeRadios.forEach((radio) => {
            radio.addEventListener('change', function () {
                currentMode = this.value;
                Log.debug('Assignment mode changed to:', currentMode);

                // Update equipment details if equipment is loaded
                if (currentEquipment) {
                    displayEquipmentDetails(currentEquipment);
                }
            });
        });

        // Manual lookup handler
        if (lookupBtn) {
            lookupBtn.addEventListener('click', () => {
                const uuid = manualUuid.value.trim();
                if (uuid) {
                    lookupEquipment(uuid);
                    manualUuid.value = '';
                }
            });
        }

        if (manualUuid) {
            manualUuid.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    lookupBtn.click();
                }
            });
        }

        /**
         * Initialize the scanner interface.
         */
        function initScanner() {
            // Clear existing scanner interface
            scannerContainer.innerHTML = '';

            // Create scanner container
            const scannerDiv = document.createElement('div');
            scannerDiv.id = 'qr-scanner-container';
            scannerDiv.className = 'qr-scanner-container mb-3';
            scannerContainer.appendChild(scannerDiv);

            // Create scanner controls
            const controlsDiv = document.createElement('div');
            controlsDiv.className = 'scanner-controls';
            controlsDiv.innerHTML = `
                <div class="scanner-toggle mb-3">
                    <div class="btn-group" role="group">
                        <button type="button" id="start-camera-btn" class="btn btn-primary">
                            <i class="fa fa-camera"></i> Start Camera
                        </button>
                        <button type="button" id="stop-camera-btn" class="btn btn-secondary" disabled>
                            <i class="fa fa-stop"></i> Stop Camera
                        </button>
                    </div>
                    <div class="mt-2">
                        <button type="button" id="scan-qr-btn" class="btn btn-success" style="display: none;">
                            <i class="fa fa-qrcode"></i> Scan
                        </button>
                        <button type="button" id="flip-camera-btn" class="btn btn-outline-secondary btn-sm ms-2"
                            style="display: none;" title="Toggle camera mirror">
                            <i class="fa fa-arrows-h"></i> Flip
                        </button>
                    </div>
                </div>
            `;
            scannerContainer.appendChild(controlsDiv);

            // Initialize scanner instance
            scanner = new UniversalScanner({
                containerId: 'qr-scanner-container',
                resultCallback: handleScanResult,
                errorCallback: handleScanError,
            });

            // Initialize scanner
            scanner.init().then((success) => {
                if (success) {
                    Log.debug('Scanner initialized successfully');
                } else {
                    Log.error('Scanner initialization failed');
                    showFallbackInterface();
                }
            });

            // Set up control buttons
            setupScannerControls();
        }

        /**
         * Set up scanner control buttons.
         */
        function setupScannerControls() {
            const startBtn = document.getElementById('start-camera-btn');
            const stopBtn = document.getElementById('stop-camera-btn');
            const scanBtn = document.getElementById('scan-qr-btn');
            const flipBtn = document.getElementById('flip-camera-btn');

            let isScanning = false;
            let isMirrored = false; // Default to normal (good for mobile)

            if (startBtn) {
                startBtn.addEventListener('click', async () => {
                    try {
                        scanner.stream = await getCameraStreamRobust();
                        scanner.video.srcObject = scanner.stream;
                        await scanner.video.play();

                        scanner.canvas.width = scanner.video.videoWidth;
                        scanner.canvas.height = scanner.video.videoHeight;

                        scanner.updateStatus(
                            'Camera ready - click Scan to detect QR code'
                        );

                        startBtn.disabled = true;
                        stopBtn.disabled = false;
                        scanBtn.style.display = 'inline-block';
                        flipBtn.style.display = 'inline-block';

                        updateVideoMirror();
                    } catch (error) {
                        Log.error('Failed to start camera:', error);
                        Notification.addNotification({
                            message: 'Failed to start camera: ' + error.message,
                            type: 'error',
                        });
                    }
                });
            }

            if (stopBtn) {
                stopBtn.addEventListener('click', () => {
                    if (scanner.stream) {
                        scanner.stream
                            .getTracks()
                            .forEach((track) => track.stop());
                        scanner.stream = null;
                    }
                    if (scanner.video) {
                        scanner.video.srcObject = null;
                    }

                    scanner.updateStatus('Camera stopped');
                    startBtn.disabled = false;
                    stopBtn.disabled = true;
                    scanBtn.style.display = 'none';
                    flipBtn.style.display = 'none';

                    isScanning = false;
                });
            }

            // Flip camera button
            if (flipBtn) {
                flipBtn.addEventListener('click', () => {
                    isMirrored = !isMirrored;
                    updateVideoMirror();

                    const text = isMirrored
                        ? 'Flip (Mirrored)'
                        : 'Flip (Normal)';
                    flipBtn.innerHTML = `<i class="fa fa-arrows-h"></i> ${text}`;

                    const message = isMirrored
                        ? 'Camera mirrored (good for webcams)'
                        : 'Camera normal (good for mobile)';
                    showSuccessMessage(message);
                });
            }

            /**
             * Update video mirror state.
             */
            function updateVideoMirror() {
                const videoElement = document.querySelector(
                    '#qr-scanner-container video'
                );

                if (videoElement) {
                    if (isMirrored) {
                        videoElement.style.transform = 'scaleX(-1)';
                    } else {
                        videoElement.style.transform = 'scaleX(1)';
                    }

                    Log.debug(
                        'Video mirror updated:',
                        isMirrored
                            ? 'mirrored (scaleX(-1))'
                            : 'normal (scaleX(1))',
                        'Applied transform:',
                        videoElement.style.transform
                    );
                }
            }

            // Scan button
            if (scanBtn) {
                scanBtn.addEventListener('click', async () => {
                    if (isScanning) {
                        return;
                    }

                    isScanning = true;
                    scanBtn.disabled = true;
                    scanBtn.innerHTML =
                        '<i class="fa fa-spinner fa-spin"></i> Scanning...';
                    scanner.updateStatus('Scanning for QR code...');

                    try {
                        const result = await performSingleScan();
                        if (result) {
                            scanner.updateStatus('Scan successful!');
                            scanner.showScanSuccess();
                        } else {
                            scanner.updateStatus(
                                'No QR code detected - try again'
                            );
                            const msg =
                                'No QR code detected. Please ' +
                                'position the QR code in the target area ' +
                                'and try again.';
                            showErrorMessage(msg);
                        }
                    } catch (error) {
                        Log.error('Scan error:', error);
                        scanner.updateStatus('Scan failed');
                        showErrorMessage('Scan failed. Please try again.');
                    }

                    isScanning = false;
                    scanBtn.disabled = false;
                    scanBtn.innerHTML = '<i class="fa fa-qrcode"></i> Scan';

                    setTimeout(() => {
                        scanner.updateStatus(
                            'Ready to scan - position QR code and click Scan'
                        );
                    }, 2000);
                });
            }

            /**
             * Perform a single scan attempt.
             * @returns {Promise<boolean>} True if scan was successful
             */
            async function performSingleScan() {
                return new Promise((resolve) => {
                    let scanAttempts = 0;
                    const maxAttempts = 30;
                    let found = false;

                    /**
                     * Process a single frame for QR detection.
                     */
                    const scanFrame = async () => {
                        if (found || scanAttempts >= maxAttempts) {
                            resolve(found);
                            return;
                        }

                        try {
                            if (
                                !scanner.video ||
                                scanner.video.readyState !==
                                    scanner.video.HAVE_ENOUGH_DATA
                            ) {
                                scanAttempts++;
                                requestAnimationFrame(scanFrame);
                                return;
                            }

                            scanner.context.drawImage(
                                scanner.video,
                                0,
                                0,
                                scanner.canvas.width,
                                scanner.canvas.height
                            );

                            // Try native BarcodeDetector first
                            if (
                                scanner.hasBarcodeDetector &&
                                window.BarcodeDetector
                            ) {
                                try {
                                    const detector = new window.BarcodeDetector(
                                        {
                                            formats: ['qr_code'],
                                        }
                                    );

                                    const barcodes = await detector.detect(
                                        scanner.canvas
                                    );

                                    if (barcodes.length > 0) {
                                        const barcode = barcodes[0];
                                        Log.debug(
                                            'QR code detected:',
                                            barcode.rawValue
                                        );
                                        processQRCode(barcode.rawValue);
                                        found = true;
                                        resolve(true);
                                        return;
                                    }
                                } catch (error) {
                                    Log.debug('BarcodeDetector failed:', error);
                                }
                            }

                            // Note: Additional QR detection methods could be added here
                            // For now, we rely on BarcodeDetector
                        } catch (error) {
                            Log.debug('Frame processing error:', error);
                        }

                        scanAttempts++;
                        requestAnimationFrame(scanFrame);
                    };

                    requestAnimationFrame(scanFrame);
                });
            }
        }

        /**
         * Handle scan result from scanner.
         * @param {Object} result - The scan result object
         */
        function handleScanResult(result) {
            if (result.success && result.data) {
                const qrData = result.data.qr_data || result.data;
                Log.debug('Scan successful, processing QR code:', qrData);
                processQRCode(qrData);
            } else {
                Log.error('Scan failed:', result);
                showErrorMessage(
                    result.message || 'Scan failed. Please try again.'
                );
            }
        }

        /**
         * Handle scan error from scanner.
         * @param {string} errorCode - The error code
         * @param {string} message - The error message
         */
        function handleScanError(errorCode, message) {
            Log.error('Scanner error:', errorCode, message);
            if (errorCode === 'camera_access_failed') {
                showFallbackInterface();
            }
        }

        /**
         * Show fallback interface when camera is not available.
         */
        function showFallbackInterface() {
            const scannerDiv = document.getElementById('qr-scanner-container');
            if (scannerDiv) {
                scannerDiv.innerHTML = `
                    <div class="alert alert-warning text-center">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Camera not available</strong><br>
                        Please use the manual UUID entry below.
                    </div>
                `;
            }
        }

        /**
         * Process a QR code.
         * @param {string} qrData - The QR code data to process
         */
        function processQRCode(qrData) {
            // Extract UUID from QR code data
            let uuid = qrData;

            // If QR code contains JSON, extract UUID
            try {
                const parsed = JSON.parse(qrData);
                if (parsed.uuid) {
                    uuid = parsed.uuid;
                } else if (parsed.id) {
                    uuid = parsed.id;
                }
            } catch (e) {
                // Not JSON, use as-is
            }

            lookupEquipment(uuid);
        }

        /**
         * Lookup equipment by UUID.
         * @param {string} uuid - The equipment UUID to lookup
         */
        function lookupEquipment(uuid) {
            Log.debug('Looking up equipment:', uuid);

            Ajax.call([
                {
                    methodname: 'local_equipment_lookup_equipment',
                    args: {
                        uuid: uuid,
                    },
                    done: function (response) {
                        if (response.success) {
                            currentEquipment = response;
                            displayEquipmentDetails(response);
                        } else {
                            showErrorMessage(
                                response.message || 'Equipment not found'
                            );
                            clearEquipmentDetails();
                        }
                    },
                    fail: function (error) {
                        Log.error('AJAX error:', error);

                        // Fallback to direct AJAX call
                        const url =
                            M.cfg.wwwroot +
                            '/local/equipment/inventory/check_inout.php';
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type':
                                    'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                ajax: '1',
                                action: 'lookup_equipment',
                                uuid: uuid,
                                sesskey: M.cfg.sesskey,
                            }),
                        })
                            .then((response) => response.json())
                            .then((data) => {
                                if (data.success) {
                                    currentEquipment = data;
                                    displayEquipmentDetails(data);
                                } else {
                                    showErrorMessage(
                                        data.message || 'Equipment not found'
                                    );
                                    clearEquipmentDetails();
                                }
                            })
                            .catch((err) => {
                                Log.error('Fetch error:', err);
                                showErrorMessage('Network error occurred');
                                clearEquipmentDetails();
                            });
                    },
                },
            ]);
        }

        /**
         * Display equipment details.
         * @param {Object} data - The equipment data object
         */
        function displayEquipmentDetails(data) {
            const item = data.item;
            const transactions = data.transactions || [];

            let statusBadge = '';

            // Status badge
            switch (item.status) {
                case 'available':
                    statusBadge =
                        '<span class="badge bg-success">Available</span>';
                    break;
                case 'checked_out':
                    statusBadge =
                        '<span class="badge bg-warning">Checked Out</span>';
                    break;
                case 'in_transit':
                    statusBadge =
                        '<span class="badge bg-info">In Transit</span>';
                    break;
                case 'maintenance':
                    statusBadge =
                        '<span class="badge bg-secondary">Maintenance</span>';
                    break;
                case 'damaged':
                    statusBadge =
                        '<span class="badge bg-danger">Damaged</span>';
                    break;
                default:
                    statusBadge =
                        '<span class="badge bg-secondary">' +
                        item.status +
                        '</span>';
            }

            // Current assignment info
            let currentAssignment = '';
            if (item.firstname && item.lastname) {
                const email = item.user_email
                    ? `<br><small>${item.user_email}</small>`
                    : '';
                currentAssignment = `
                    <div class="alert alert-info">
                        <strong>Currently assigned to:</strong><br>
                        ${item.firstname} ${item.lastname}
                        ${email}
                    </div>
                `;
            } else if (item.location_name) {
                currentAssignment = `
                    <div class="alert alert-secondary">
                        <strong>Current location:</strong><br>
                        ${item.location_name}
                    </div>
                `;
            }

            // Assignment interface based on mode
            let assignmentInterface = '';
            if (currentMode === 'student') {
                const unassignBtn = item.current_userid
                    ? '<button type="button" id="unassign-btn" ' +
                      'class="btn btn-outline-secondary">Remove Assignment</button>'
                    : '';
                assignmentInterface = `
                    <div class="assignment-section">
                        <h5>Student Assignment</h5>
                        <div class="mb-3">
                            <label for="user-search" class="form-label">Search for student:</label>
                            <input type="text" id="user-search" class="form-control"
                                placeholder="Type student name or email...">
                            <div id="user-results" class="list-group mt-2" style="display: none;"></div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" id="assign-user-btn" class="btn btn-primary" disabled>
                                Assign to Selected Student
                            </button>
                            ${unassignBtn}
                        </div>
                    </div>
                `;
            } else {
                assignmentInterface = `
                    <div class="assignment-section">
                        <h5>Location Transfer</h5>
                        <div class="mb-3">
                            <label for="location-select" class="form-label">Transfer to location:</label>
                            <select id="location-select" class="form-select">
                                <option value="">Select location...</option>
                            </select>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" id="transfer-location-btn" class="btn btn-primary" disabled>
                                Transfer to Selected Location
                            </button>
                        </div>
                    </div>
                `;
            }

            // Recent transactions
            let transactionsList = '';
            if (transactions.length > 0) {
                transactionsList =
                    '<h6>Recent Transactions</h6><div class="list-group list-group-flush">';
                transactions.forEach((trans) => {
                    const date = new Date(
                        trans.timestamp * 1000
                    ).toLocaleDateString();
                    const type =
                        trans.transaction_type.charAt(0).toUpperCase() +
                        trans.transaction_type.slice(1);

                    let details = '';
                    if (trans.to_firstname && trans.to_lastname) {
                        details = `To: ${trans.to_firstname} ${trans.to_lastname}`;
                    } else if (trans.from_firstname && trans.from_lastname) {
                        details = `From: ${trans.from_firstname} ${trans.from_lastname}`;
                    } else if (trans.to_location) {
                        details = `To: ${trans.to_location}`;
                    } else if (trans.from_location) {
                        details = `From: ${trans.from_location}`;
                    }

                    const detailsHtml = details
                        ? `<p class="mb-1">${details}</p>`
                        : '';
                    const notesHtml = trans.notes
                        ? `<small class="text-muted">${trans.notes}</small>`
                        : '';

                    transactionsList += `
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${type}</h6>
                                <small>${date}</small>
                            </div>
                            ${detailsHtml}
                            ${notesHtml}
                        </div>
                    `;
                });
                transactionsList += '</div>';
            }

            // Notes section
            const notesSection = `
                <div class="notes-section mt-3">
                    <h6>Add Notes</h6>
                    <div class="mb-2">
                        <textarea id="equipment-notes" class="form-control" rows="2"
                            placeholder="Add notes about this equipment..."></textarea>
                    </div>
                    <button type="button" id="save-notes-btn" class="btn btn-outline-primary btn-sm">
                        Save Notes
                    </button>
                </div>
            `;

            const manufacturerHtml = item.manufacturer
                ? `<p><strong>Manufacturer:</strong> ${item.manufacturer}</p>`
                : '';
            const categoryHtml = item.category
                ? `<p><strong>Category:</strong> ${item.category}</p>`
                : '';

            const html = `
                <div class="equipment-info">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h4>${item.product_name}</h4>
                        ${statusBadge}
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>UUID:</strong><br>
                            <code>${item.uuid}</code>
                        </div>
                        <div class="col-sm-6">
                            <strong>Condition:</strong><br>
                            <span class="badge bg-light text-dark">${
                                item.condition_status || 'Unknown'
                            }</span>
                        </div>
                    </div>

                    ${manufacturerHtml}
                    ${categoryHtml}

                    ${currentAssignment}

                    <hr>

                    ${assignmentInterface}

                    ${notesSection}

                    <hr>

                    ${transactionsList}
                </div>
            `;

            equipmentDetails.innerHTML = html;

            // Set up assignment interface handlers
            setupAssignmentHandlers(item);
        }

        /**
         * Set up assignment interface handlers.
         * @param {Object} item - The equipment item object
         */
        function setupAssignmentHandlers(item) {
            if (currentMode === 'student') {
                setupStudentAssignment(item);
            } else {
                setupLocationTransfer(item);
            }

            // Notes handler
            const saveNotesBtn = document.getElementById('save-notes-btn');
            const notesTextarea = document.getElementById('equipment-notes');

            if (saveNotesBtn && notesTextarea) {
                saveNotesBtn.addEventListener('click', () => {
                    const notes = notesTextarea.value.trim();
                    if (notes) {
                        saveEquipmentNotes(item.uuid, notes);
                    }
                });
            }
        }

        /**
         * Set up student assignment interface.
         * @param {Object} item - The equipment item object
         */
        function setupStudentAssignment(item) {
            const userSearch = document.getElementById('user-search');
            const userResults = document.getElementById('user-results');
            const assignBtn = document.getElementById('assign-user-btn');
            const unassignBtn = document.getElementById('unassign-btn');

            let selectedUser = null;
            let searchTimeout = null;

            // User search
            if (userSearch) {
                userSearch.addEventListener('input', function () {
                    const query = this.value.trim();

                    clearTimeout(searchTimeout);

                    if (query.length < 2) {
                        userResults.style.display = 'none';
                        return;
                    }

                    searchTimeout = setTimeout(() => {
                        searchUsers(query);
                    }, 300);
                });
            }

            /**
             * Search for users by query.
             * @param {string} query - The search query
             */
            function searchUsers(query) {
                const url =
                    M.cfg.wwwroot +
                    '/local/equipment/inventory/check_inout.php';
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        ajax: '1',
                        action: 'search_users',
                        query: query,
                        sesskey: M.cfg.sesskey,
                    }),
                })
                    .then((response) => response.json())
                    .then((data) => {
                        displayUserResults(data.users || []);
                    })
                    .catch((error) => {
                        Log.error('User search error:', error);
                    });
            }

            /**
             * Display user search results.
             * @param {Array} users - Array of user objects
             */
            function displayUserResults(users) {
                if (users.length === 0) {
                    userResults.style.display = 'none';
                    return;
                }

                let html = '';
                users.forEach((user) => {
                    html += `
                        <button type="button" class="list-group-item list-group-item-action user-option"
                                data-userid="${user.id}" data-name="${user.firstname} ${user.lastname}">
                            <strong>${user.firstname} ${user.lastname}</strong><br>
                            <small class="text-muted">${user.email}</small>
                        </button>
                    `;
                });

                userResults.innerHTML = html;
                userResults.style.display = 'block';

                // User selection handlers
                userResults.querySelectorAll('.user-option').forEach((btn) => {
                    btn.addEventListener('click', function () {
                        selectedUser = {
                            id: this.dataset.userid,
                            name: this.dataset.name,
                        };

                        userSearch.value = selectedUser.name;
                        userResults.style.display = 'none';
                        assignBtn.disabled = false;
                    });
                });
            }

            // Assign button
            if (assignBtn) {
                assignBtn.addEventListener('click', () => {
                    if (selectedUser) {
                        assignToUser(
                            item.uuid,
                            selectedUser.id,
                            selectedUser.name
                        );
                    }
                });
            }

            // Unassign button
            if (unassignBtn) {
                unassignBtn.addEventListener('click', () => {
                    unassignEquipment(item.uuid);
                });
            }
        }

        /**
         * Set up location transfer interface.
         * @param {Object} item - The equipment item object
         */
        function setupLocationTransfer(item) {
            const locationSelect = document.getElementById('location-select');
            const transferBtn = document.getElementById(
                'transfer-location-btn'
            );

            // Load locations
            const url =
                M.cfg.wwwroot + '/local/equipment/inventory/check_inout.php';
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    ajax: '1',
                    action: 'get_locations',
                    sesskey: M.cfg.sesskey,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.locations) {
                        data.locations.forEach((location) => {
                            const option = document.createElement('option');
                            option.value = location.id;
                            option.textContent = location.name;
                            locationSelect.appendChild(option);
                        });
                    }
                })
                .catch((error) => {
                    Log.error('Location loading error:', error);
                });

            // Location selection
            if (locationSelect) {
                locationSelect.addEventListener('change', function () {
                    transferBtn.disabled = !this.value;
                });
            }

            // Transfer button
            if (transferBtn) {
                transferBtn.addEventListener('click', () => {
                    const locationId = locationSelect.value;
                    const locationName =
                        locationSelect.options[locationSelect.selectedIndex]
                            .text;
                    if (locationId) {
                        transferToLocation(item.uuid, locationId, locationName);
                    }
                });
            }
        }

        /**
         * Assign equipment to user.
         * @param {string} uuid - Equipment UUID
         * @param {string} userid - User ID
         * @param {string} userName - User display name
         */
        function assignToUser(uuid, userid, userName) {
            const notes = document
                .getElementById('equipment-notes')
                .value.trim();

            const url =
                M.cfg.wwwroot + '/local/equipment/inventory/check_inout.php';
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    ajax: '1',
                    action: 'update_assignment',
                    uuid: uuid,
                    userid: userid,
                    notes: notes,
                    sesskey: M.cfg.sesskey,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        showSuccessMessage(`Equipment assigned to ${userName}`);
                        // Refresh equipment details
                        lookupEquipment(uuid);
                    } else {
                        showErrorMessage(data.message || 'Assignment failed');
                    }
                })
                .catch((error) => {
                    Log.error('Assignment error:', error);
                    showErrorMessage('Network error occurred');
                });
        }

        /**
         * Transfer equipment to location.
         * @param {string} uuid - Equipment UUID
         * @param {string} locationid - Location ID
         * @param {string} locationName - Location display name
         */
        function transferToLocation(uuid, locationid, locationName) {
            const notes = document
                .getElementById('equipment-notes')
                .value.trim();

            const url =
                M.cfg.wwwroot + '/local/equipment/inventory/check_inout.php';
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    ajax: '1',
                    action: 'update_assignment',
                    uuid: uuid,
                    locationid: locationid,
                    notes: notes,
                    sesskey: M.cfg.sesskey,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        showSuccessMessage(
                            `Equipment transferred to ${locationName}`
                        );
                        // Refresh equipment details
                        lookupEquipment(uuid);
                    } else {
                        showErrorMessage(data.message || 'Transfer failed');
                    }
                })
                .catch((error) => {
                    Log.error('Transfer error:', error);
                    showErrorMessage('Network error occurred');
                });
        }

        /**
         * Unassign equipment.
         * @param {string} uuid - Equipment UUID
         */
        function unassignEquipment(uuid) {
            const notes = document
                .getElementById('equipment-notes')
                .value.trim();

            const url =
                M.cfg.wwwroot + '/local/equipment/inventory/check_inout.php';
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    ajax: '1',
                    action: 'update_assignment',
                    uuid: uuid,
                    notes: notes,
                    sesskey: M.cfg.sesskey,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        showSuccessMessage('Equipment unassigned');
                        // Refresh equipment details
                        lookupEquipment(uuid);
                    } else {
                        showErrorMessage(data.message || 'Unassignment failed');
                    }
                })
                .catch((error) => {
                    Log.error('Unassignment error:', error);
                    showErrorMessage('Network error occurred');
                });
        }

        /**
         * Save equipment notes.
         * @param {string} uuid - Equipment UUID
         * @param {string} notes - Notes to save
         */
        function saveEquipmentNotes(uuid, notes) {
            const url =
                M.cfg.wwwroot + '/local/equipment/inventory/check_inout.php';
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    ajax: '1',
                    action: 'update_notes',
                    uuid: uuid,
                    notes: notes,
                    sesskey: M.cfg.sesskey,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        showSuccessMessage('Notes saved successfully');
                        document.getElementById('equipment-notes').value = '';
                    } else {
                        showErrorMessage(
                            data.message || 'Failed to save notes'
                        );
                    }
                })
                .catch((error) => {
                    Log.error('Notes save error:', error);
                    showErrorMessage('Network error occurred');
                });
        }

        /**
         * Clear equipment details panel.
         */
        function clearEquipmentDetails() {
            equipmentDetails.innerHTML = `
                <div class="alert alert-info">
                    <i class="fa fa-info-circle me-2"></i>
                    Scan a QR code or enter UUID manually to view equipment details and manage assignments.
                </div>
            `;
            currentEquipment = null;
        }

        /**
         * Show success message.
         * @param {string} message - Success message to display
         */
        function showSuccessMessage(message) {
            Notification.addNotification({
                message: message,
                type: 'success',
            });
        }

        /**
         * Show error message.
         * @param {string} message - Error message to display
         */
        function showErrorMessage(message) {
            Notification.addNotification({
                message: message,
                type: 'error',
            });
        }

        /**
         * Get camera stream with robust fallbacks.
         * @returns {Promise<MediaStream>} Camera stream
         */
        async function getCameraStreamRobust() {
            const constraints = {
                video: {
                    facingMode: 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
            };

            // Try modern API first
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                try {
                    return await navigator.mediaDevices.getUserMedia(
                        constraints
                    );
                } catch (error) {
                    Log.debug('Modern API failed:', error);
                }
            }

            // Try legacy getUserMedia
            if (navigator.getUserMedia) {
                try {
                    return await new Promise((resolve, reject) => {
                        navigator.getUserMedia(constraints, resolve, reject);
                    });
                } catch (error) {
                    Log.debug('Legacy getUserMedia failed:', error);
                }
            }

            throw new Error('No camera API available');
        }
    }

    // Return the public API
    return {
        init: init,
    };
});
