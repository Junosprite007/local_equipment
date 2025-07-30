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
 * Scanner integration for remove items page.
 *
 * @module     local_equipment/remove-items-scanner
 * @copyright  2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import UniversalScanner from 'local_equipment/universal-scanner';
import Notification from 'core/notification';
import Log from 'core/log';
import jsQR from 'local_equipment/jsqr';
import Ajax from 'core/ajax';
import { debugAjaxResponse } from 'local_equipment/debug-utils';

/**
 * Initialize the scanner for remove items page.
 */
export const init = () => {
    Log.debug('local_equipment/remove-items-scanner: init() called');
    Log.debug(
        'local_equipment/remove-items-scanner: document.readyState =',
        document.readyState
    );

    // Check if DOM is already ready
    if (document.readyState === 'loading') {
        // DOM is still loading, wait for DOMContentLoaded
        Log.debug(
            'local_equipment/remove-items-scanner: DOM still loading, adding DOMContentLoaded listener'
        );
        document.addEventListener('DOMContentLoaded', function () {
            Log.debug(
                'local_equipment/remove-items-scanner: DOMContentLoaded event fired'
            );
            initializeScanner();
        });
    } else {
        // DOM is already ready, initialize immediately
        Log.debug(
            'local_equipment/remove-items-scanner: DOM already ready, initializing immediately'
        );
        initializeScanner();
    }
};

/**
 * Initialize the scanner interface.
 */
function initializeScanner() {
    Log.debug(
        'local_equipment/remove-items-scanner: initializeScanner() called'
    );

    const scannerContainer = document.getElementById('scanner-container');
    const manualUuid = document.getElementById('manual_uuid');
    const lookupBtn = document.getElementById('lookup_btn');
    const sessionItems = document.getElementById('session_items');

    Log.debug('DOM elements found:', {
        scannerContainer: !!scannerContainer,
        manualUuid: !!manualUuid,
        lookupBtn: !!lookupBtn,
        sessionItems: !!sessionItems,
    });

    let scanner = null;
    let sessionRemovedCount = 0;
    let sessionRemovedItems = [];

    // Initialize scanner immediately
    initScanner();

    /**
     * Initialize the scanner interface.
     */
    function initScanner() {
        if (!scannerContainer) {
            Log.error('Scanner container not found in DOM');
            return;
        }

        // Clear existing scanner interface
        scannerContainer.innerHTML = '';

        // Create scanner controls
        const controlsDiv = document.createElement('div');
        controlsDiv.className = 'scanner-controls';
        controlsDiv.innerHTML = `
            <div class="scanner-area mb-3" id="scanner-area">
                <!-- Scanner video will be inserted here -->
            </div>
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
                    <button type="button" id="scan-barcode-btn" class="btn btn-primary" style="display: none;">
                        <i class="fa fa-qrcode"></i> Scan
                    </button>
                    <button type="button" id="flip-camera-btn" class="btn btn-outline-secondary btn-sm ms-2" style="display: none;"
                        title="Toggle camera mirror">
                        <i class="fa fa-arrows-h"></i> Flip
                    </button>
                </div>
            </div>
            <div class="file-upload-section mb-3" style="display: none;" id="file-upload-section">
                <label for="barcode-file-input" class="form-label">
                    <i class="fa fa-camera"></i> Take Photo of Barcode/QR Code:
                </label>
                <div class="input-group">
                    <input type="file" id="barcode-file-input" class="form-control" accept="image/*" capture="environment">
                    <button type="button" id="process-file-btn" class="btn btn-outline-success" disabled>
                        <i class="fa fa-search"></i> Scan Photo
                    </button>
                </div>
                <small class="form-text text-muted">
                    Take a clear photo of the QR code or barcode with good lighting
                </small>
            </div>
            <div class="manual-input">
                <label for="scanner-manual-input" class="form-label">Or enter barcode/QR manually:</label>
                <div class="input-group">
                    <input type="text" id="scanner-manual-input" class="form-control" placeholder="Scan or type barcode/QR code...">
                    <button type="button" id="scanner-manual-btn" class="btn btn-outline-primary">Process</button>
                </div>
            </div>
        `;
        scannerContainer.appendChild(controlsDiv);

        // Initialize scanner instance
        scanner = new UniversalScanner({
            containerId: 'scanner-area',
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

        // Add network test button
        addNetworkTestButton();
    }

    /**
     * Set up scanner control buttons.
     */
    function setupScannerControls() {
        const startBtn = document.getElementById('start-camera-btn');
        const stopBtn = document.getElementById('stop-camera-btn');
        const scanBtn = document.getElementById('scan-barcode-btn');
        const flipBtn = document.getElementById('flip-camera-btn');
        const manualInput = document.getElementById('scanner-manual-input');
        const manualBtn = document.getElementById('scanner-manual-btn');

        let isScanning = false;
        let scanTimeout = null;
        let isMirrored = false; // Default to mirrored (good for webcams)

        startBtn.addEventListener('click', async () => {
            try {
                // Use robust camera detection
                scanner.stream = await getCameraStreamRobust();

                scanner.video.srcObject = scanner.stream;
                await scanner.video.play();

                // Set canvas dimensions to match video
                scanner.canvas.width = scanner.video.videoWidth;
                scanner.canvas.height = scanner.video.videoHeight;

                scanner.updateStatus(
                    'Camera ready - click Scan to detect barcode/QR code'
                );

                startBtn.disabled = true;
                stopBtn.disabled = false;
                scanBtn.style.display = 'inline-block';
                flipBtn.style.display = 'inline-block';

                // Apply initial mirror state
                updateVideoMirror();

                updateStatusMessage(
                    "Camera started. Click 'Scan' to detect QR codes or barcodes in the camera view.",
                    'info'
                );
            } catch (error) {
                Log.error('Failed to start camera:', error);

                // Show detailed error information
                const compatibility = await checkCameraCompatibility();
                if (!compatibility.supported) {
                    showDetailedCompatibilityError(compatibility);
                } else {
                    Notification.addNotification({
                        message: 'Failed to start camera: ' + error.message,
                        type: 'error',
                    });
                }
            }
        });

        stopBtn.addEventListener('click', () => {
            // Stop camera and hide scan button
            if (scanner.stream) {
                scanner.stream.getTracks().forEach((track) => track.stop());
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

            // Clear any active scanning
            if (scanTimeout) {
                clearTimeout(scanTimeout);
                scanTimeout = null;
            }
            isScanning = false;
        });

        // Flip camera button - toggles horizontal mirror
        flipBtn.addEventListener('click', () => {
            isMirrored = !isMirrored;
            updateVideoMirror();

            // Update button text to show current state
            const icon = isMirrored ? 'fa-arrows-h' : 'fa-arrows-h';
            const text = isMirrored ? 'Flip (Mirrored)' : 'Flip (Normal)';
            flipBtn.innerHTML = `<i class="fa ${icon}"></i> ${text}`;

            // Show feedback message
            const message = isMirrored
                ? 'Camera mirrored (good for webcams)'
                : 'Camera normal (good for mobile)';
            showSuccessMessage(message);
        });

        /**
         * Update video mirror state based on isMirrored flag.
         */
        function updateVideoMirror() {
            // Find the video element in the scanner area
            const videoElement = document.querySelector('#scanner-area video');

            if (videoElement) {
                // Apply transform directly via inline style (more reliable)
                if (isMirrored) {
                    videoElement.style.transform = 'scaleX(-1)';
                } else {
                    videoElement.style.transform = 'scaleX(1)';
                }

                Log.debug(
                    'Video mirror updated:',
                    isMirrored ? 'mirrored (scaleX(-1))' : 'normal (scaleX(1))',
                    'Video element found:',
                    !!videoElement,
                    'Applied transform:',
                    videoElement.style.transform
                );
            } else {
                Log.error('Video element not found for mirror update');
            }
        }

        // Scan button - triggers 1-second scan window
        scanBtn.addEventListener('click', async () => {
            if (isScanning) {
                return;
            }

            isScanning = true;
            scanBtn.disabled = true;
            scanBtn.innerHTML =
                '<i class="fa fa-spinner fa-spin"></i> Scanning...';
            scanner.updateStatus('Scanning for barcode/QR code...');

            try {
                const result = await performSingleScan();
                if (result) {
                    // Barcode found and processed
                    scanner.updateStatus('Scan successful!');
                    scanner.showScanSuccess();
                } else {
                    // No barcode found
                    scanner.updateStatus('No barcode detected - try again');
                    showErrorMessage(
                        'No barcode detected. Please position the barcode/QR code in the target area and try again.'
                    );
                }
            } catch (error) {
                Log.error('Scan error:', error);
                scanner.updateStatus('Scan failed');
                showErrorMessage('Scan failed. Please try again.');
            }

            // Reset button state
            isScanning = false;
            scanBtn.disabled = false;
            scanBtn.innerHTML = '<i class="fa fa-qrcode"></i> Scan';

            // Reset status after a delay
            setTimeout(() => {
                scanner.updateStatus(
                    'Ready to scan - position barcode/QR code and click Scan'
                );
            }, 2000);
        });

        /**
         * Perform a single 1-second scan attempt.
         * @returns {Promise<boolean>} True if barcode was found and processed
         */
        async function performSingleScan() {
            return new Promise((resolve) => {
                let scanAttempts = 0;
                const maxAttempts = 30; // ~1 second at 30fps
                let found = false;

                const scanFrame = async () => {
                    if (found || scanAttempts >= maxAttempts) {
                        resolve(found);
                        return;
                    }

                    try {
                        // Check if video is ready
                        if (
                            !scanner.video ||
                            scanner.video.readyState !==
                                scanner.video.HAVE_ENOUGH_DATA
                        ) {
                            scanAttempts++;
                            requestAnimationFrame(scanFrame);
                            return;
                        }

                        // Draw current frame to canvas
                        scanner.context.drawImage(
                            scanner.video,
                            0,
                            0,
                            scanner.canvas.width,
                            scanner.canvas.height
                        );

                        // Try native BarcodeDetector first (Chrome/Edge)
                        if (scanner.hasBarcodeDetector) {
                            try {
                                // eslint-disable-next-line no-undef
                                const detector = new BarcodeDetector({
                                    formats: [
                                        'qr_code',
                                        'ean_13',
                                        'ean_8',
                                        'upc_a',
                                        'upc_e',
                                        'code_128',
                                        'code_39',
                                    ],
                                });

                                const barcodes = await detector.detect(
                                    scanner.canvas
                                );

                                if (barcodes.length > 0) {
                                    const barcode = barcodes[0];
                                    Log.debug(
                                        'Barcode detected:',
                                        barcode.rawValue,
                                        barcode.format
                                    );
                                    processBarcode(
                                        barcode.rawValue,
                                        barcode.format
                                    );
                                    found = true;
                                    resolve(true);
                                    return;
                                }
                            } catch (error) {
                                Log.debug('BarcodeDetector failed:', error);
                            }
                        }

                        // Fallback: Try QR code detection
                        try {
                            const imageData = scanner.context.getImageData(
                                0,
                                0,
                                scanner.canvas.width,
                                scanner.canvas.height
                            );

                            // Use jsQR library for QR code detection
                            const code = jsQR.scan(
                                imageData.data,
                                imageData.width,
                                imageData.height
                            );

                            if (code && code.data) {
                                Log.debug('QR code detected:', code.data);
                                processBarcode(code.data, 'qr_code');
                                found = true;
                                resolve(true);
                                return;
                            }
                        } catch (error) {
                            Log.debug('QR detection failed:', error);
                        }
                    } catch (error) {
                        Log.debug('Frame processing error:', error);
                    }

                    scanAttempts++;
                    requestAnimationFrame(scanFrame);
                };

                // Start scanning
                requestAnimationFrame(scanFrame);
            });
        }

        // Manual input handling
        manualBtn.addEventListener('click', () => {
            const barcode = manualInput.value.trim();
            if (barcode) {
                processBarcode(barcode, 'manual');
                manualInput.value = '';
            }
        });

        manualInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                manualBtn.click();
            }
        });

        // Set up file upload
        setupFileUpload();
    }

    /**
     * Handle scan result from scanner.
     *
     * @param {Object} result Scan result
     */
    function handleScanResult(result) {
        if (result.success && result.data) {
            // Extract barcode data from the scan result
            const barcodeData = result.data.barcode_data || result.data;
            Log.debug('Scan successful, processing barcode:', barcodeData);
            processBarcode(barcodeData, 'scan');
        } else {
            Log.error('Scan failed:', result);
            // Show error message to user
            const errorMsg = result.message || 'Scan failed. Please try again.';
            showErrorMessage(errorMsg);
        }
    }

    /**
     * Handle scan error from scanner.
     *
     * @param {string} errorCode Error code
     * @param {string} message Error message
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
        if (scannerContainer) {
            const isMobile =
                /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
                    navigator.userAgent
                );
            const isHTTP = window.location.protocol === 'http:';

            let content = '';

            if (isMobile && isHTTP) {
                // Mobile + HTTP specific guidance
                content = `
                    <div class="alert alert-info">
                        <h5><i class="fa fa-mobile"></i> Mobile Camera Setup Required</h5>
                        <p><strong>To enable camera scanning on mobile:</strong></p>
                        <ol class="text-start mb-3">
                            <li>Open Chrome menu (⋮) → <strong>Settings</strong></li>
                            <li>Go to <strong>Site Settings</strong> → <strong>Camera</strong></li>
                            <li>Find this site and set to <strong>"Allow"</strong></li>
                        </ol>
                        <p><strong>Alternative:</strong> In Chrome address bar, type:<br>
                        <code>chrome://flags/#unsafely-treat-insecure-origin-as-secure</code><br>
                        Add: <code>${window.location.origin}</code></p>
                        <button type="button" id="test-camera-btn" class="btn btn-primary btn-sm mt-2">
                            <i class="fa fa-camera"></i> Test Camera Access
                        </button>
                    </div>
                `;
            } else if (isMobile) {
                // Mobile + HTTPS
                content = `
                    <div class="alert alert-warning text-center">
                        <i class="fa fa-mobile"></i>
                        <strong>Camera access denied</strong><br>
                        Please check your browser settings and allow camera access for this site.
                        <button type="button" id="test-camera-btn" class="btn btn-primary btn-sm mt-2">
                            <i class="fa fa-camera"></i> Test Camera Access
                        </button>
                    </div>
                `;
            } else {
                // Desktop fallback
                content = `
                    <div class="alert alert-warning text-center">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Camera not available</strong><br>
                        Please use the manual input below to enter barcodes/QR codes.
                    </div>
                `;
            }

            scannerContainer.innerHTML = content;

            // Add test camera button functionality
            const testBtn = document.getElementById('test-camera-btn');
            if (testBtn) {
                testBtn.addEventListener('click', testCameraAccess);
            }

            // Show file upload option for mobile users
            if (isMobile) {
                const fileUploadSection = document.getElementById(
                    'file-upload-section'
                );
                if (fileUploadSection) {
                    fileUploadSection.style.display = 'block';
                }
            }
        }
    }

    /**
     * Set up file upload functionality for mobile users.
     */
    function setupFileUpload() {
        const fileInput = document.getElementById('barcode-file-input');
        const processFileBtn = document.getElementById('process-file-btn');

        if (!fileInput || !processFileBtn) {
            return;
        }

        // Enable process button when file is selected
        fileInput.addEventListener('change', function () {
            processFileBtn.disabled = !this.files.length;
        });

        // Process uploaded file
        processFileBtn.addEventListener('click', async function () {
            const file = fileInput.files[0];
            if (!file) {
                return;
            }

            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML =
                '<i class="fa fa-spinner fa-spin"></i> Scanning Photo...';

            try {
                const barcode = await processImageFile(file);
                if (barcode) {
                    showSuccessMessage('Barcode detected from photo!');
                    processBarcode(
                        barcode.data,
                        barcode.format || 'file_upload'
                    );
                    // Clear the file input
                    fileInput.value = '';
                } else {
                    showErrorMessage(
                        'No barcode found in the image. Please try taking a clearer photo with better lighting.'
                    );
                }
            } catch (error) {
                Log.error('File processing error:', error);
                showErrorMessage('Failed to process image. Please try again.');
            }

            this.disabled = false;
            this.innerHTML = originalText;
            processFileBtn.disabled = true; // Disable until new file selected
        });
    }

    /**
     * Process an uploaded image file for barcodes.
     * @param {File} file Image file
     * @returns {Promise<Object|null>} Detected barcode object or null
     */
    async function processImageFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = function (e) {
                const img = new Image();

                img.onload = async function () {
                    try {
                        // Create a temporary canvas for processing
                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d', {
                            willReadFrequently: true,
                        });

                        // Set canvas size to image size
                        canvas.width = img.width;
                        canvas.height = img.height;

                        // Draw image to canvas
                        context.drawImage(img, 0, 0);

                        // Try native BarcodeDetector first (Chrome/Edge)
                        if ('BarcodeDetector' in window) {
                            try {
                                // eslint-disable-next-line no-undef
                                const detector = new BarcodeDetector({
                                    formats: [
                                        'qr_code',
                                        'ean_13',
                                        'ean_8',
                                        'upc_a',
                                        'upc_e',
                                        'code_128',
                                        'code_39',
                                    ],
                                });

                                const barcodes = await detector.detect(canvas);

                                if (barcodes.length > 0) {
                                    Log.debug(
                                        'Barcode detected from file:',
                                        barcodes[0].rawValue
                                    );
                                    resolve({
                                        data: barcodes[0].rawValue,
                                        format: barcodes[0].format,
                                    });
                                    return;
                                }
                            } catch (error) {
                                Log.debug(
                                    'BarcodeDetector failed on file:',
                                    error
                                );
                            }
                        }

                        // Fallback: Try QR code detection with jsQR
                        try {
                            const imageData = context.getImageData(
                                0,
                                0,
                                canvas.width,
                                canvas.height
                            );
                            const code = jsQR.scan(
                                imageData.data,
                                imageData.width,
                                imageData.height
                            );

                            if (code && code.data) {
                                Log.debug(
                                    'QR code detected from file:',
                                    code.data
                                );
                                resolve({
                                    data: code.data,
                                    format: 'qr_code',
                                });
                                return;
                            }
                        } catch (error) {
                            Log.debug('jsQR failed on file:', error);
                        }

                        // No barcode found
                        resolve(null);
                    } catch (error) {
                        reject(error);
                    }
                };

                img.onerror = function () {
                    reject(new Error('Failed to load image'));
                };

                img.src = e.target.result;
            };

            reader.onerror = function () {
                reject(new Error('Failed to read file'));
            };

            reader.readAsDataURL(file);
        });
    }

    /**
     * Test camera access with comprehensive compatibility checking.
     */
    async function testCameraAccess() {
        const testBtn = document.getElementById('test-camera-btn');
        const originalText = testBtn.innerHTML;

        testBtn.disabled = true;
        testBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Testing...';

        // First, run comprehensive compatibility check
        const compatibility = await checkCameraCompatibility();

        if (!compatibility.supported) {
            showDetailedCompatibilityError(compatibility);
            testBtn.disabled = false;
            testBtn.innerHTML = originalText;
            return;
        }

        // Try to access camera using the best available method
        try {
            const stream = await getCameraStreamRobust();

            // Success! Camera access granted
            stream.getTracks().forEach((track) => track.stop());

            showSuccessMessage(
                'Camera access granted! You can now use the Start Camera button.'
            );

            // Re-initialize scanner interface
            initScanner();
        } catch (error) {
            // Provide specific error guidance
            let errorMessage = 'Camera access failed. ';

            if (error.name === 'NotAllowedError') {
                errorMessage +=
                    'Please allow camera permissions in your browser settings.';
            } else if (error.name === 'NotFoundError') {
                errorMessage += 'No camera found on this device.';
            } else if (error.name === 'NotSupportedError') {
                errorMessage += 'Camera not supported on this browser/device.';
            } else if (error.name === 'NotReadableError') {
                errorMessage += 'Camera is being used by another application.';
            } else {
                errorMessage += `Error: ${error.message}`;
            }

            showErrorMessage(errorMessage);

            testBtn.disabled = false;
            testBtn.innerHTML = originalText;
        }
    }

    /**
     * Comprehensive camera compatibility check.
     * @returns {Promise<Object>} Compatibility information
     */
    async function checkCameraCompatibility() {
        const result = {
            supported: false,
            method: null,
            issues: [],
            browserInfo: getBrowserInfo(),
            apis: {
                mediaDevices: false,
                getUserMedia: false,
                webkitGetUserMedia: false,
                mozGetUserMedia: false,
            },
        };

        Log.debug('Browser info:', result.browserInfo);

        // Check for modern MediaDevices API
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            result.apis.mediaDevices = true;
            result.method = 'mediaDevices';
            result.supported = true;
        }

        // Check for legacy getUserMedia
        if (navigator.getUserMedia) {
            result.apis.getUserMedia = true;
            if (!result.supported) {
                result.method = 'getUserMedia';
                result.supported = true;
            }
        }

        // Check for webkit prefixed version (older Chrome/Safari)
        if (navigator.webkitGetUserMedia) {
            result.apis.webkitGetUserMedia = true;
            if (!result.supported) {
                result.method = 'webkitGetUserMedia';
                result.supported = true;
            }
        }

        // Check for moz prefixed version (older Firefox)
        if (navigator.mozGetUserMedia) {
            result.apis.mozGetUserMedia = true;
            if (!result.supported) {
                result.method = 'mozGetUserMedia';
                result.supported = true;
            }
        }

        // Check for HTTPS/secure context
        if (
            location.protocol !== 'https:' &&
            location.hostname !== 'localhost'
        ) {
            result.issues.push('insecure_context');
        }

        // Check if we're in a mobile browser
        if (result.browserInfo.mobile && !result.supported) {
            result.issues.push('mobile_no_camera_api');
        }

        Log.debug('Camera compatibility check:', result);
        return result;
    }

    /**
     * Get detailed browser information.
     * @returns {Object} Browser information
     */
    function getBrowserInfo() {
        const ua = navigator.userAgent;
        const result = {
            mobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
                ua
            ),
            chrome: /Chrome/i.test(ua),
            firefox: /Firefox/i.test(ua),
            safari: /Safari/i.test(ua) && !/Chrome/i.test(ua),
            edge: /Edge/i.test(ua),
            version: null,
            android: /Android/i.test(ua),
            ios: /iPhone|iPad|iPod/i.test(ua),
        };

        // Extract Chrome version
        if (result.chrome) {
            const match = ua.match(/Chrome\/(\d+)/);
            if (match) {
                result.version = parseInt(match[1]);
            }
        }

        return result;
    }

    /**
     * Robust camera stream acquisition with fallbacks.
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
                Log.debug('Trying modern mediaDevices.getUserMedia');
                return await navigator.mediaDevices.getUserMedia(constraints);
            } catch (error) {
                Log.debug('Modern API failed:', error);
                // Continue to fallbacks
            }
        }

        // Try legacy getUserMedia with Promise wrapper
        if (navigator.getUserMedia) {
            try {
                Log.debug('Trying legacy getUserMedia');
                return await new Promise((resolve, reject) => {
                    navigator.getUserMedia(constraints, resolve, reject);
                });
            } catch (error) {
                Log.debug('Legacy getUserMedia failed:', error);
            }
        }

        // Try webkit prefixed version
        if (navigator.webkitGetUserMedia) {
            try {
                Log.debug('Trying webkitGetUserMedia');
                return await new Promise((resolve, reject) => {
                    navigator.webkitGetUserMedia(constraints, resolve, reject);
                });
            } catch (error) {
                Log.debug('webkitGetUserMedia failed:', error);
            }
        }

        // Try moz prefixed version
        if (navigator.mozGetUserMedia) {
            try {
                Log.debug('Trying mozGetUserMedia');
                return await new Promise((resolve, reject) => {
                    navigator.mozGetUserMedia(constraints, resolve, reject);
                });
            } catch (error) {
                Log.debug('mozGetUserMedia failed:', error);
            }
        }

        throw new Error('No camera API available');
    }

    /**
     * Show detailed compatibility error with specific guidance.
     * @param {Object} compatibility Compatibility check result
     */
    function showDetailedCompatibilityError(compatibility) {
        let message =
            '<div class="alert alert-danger"><h5><i class="fa fa-exclamation-triangle"></i> Camera Not Supported</h5>';

        message +=
            '<p><strong>Browser:</strong> ' + compatibility.browserInfo.chrome
                ? `Chrome ${compatibility.browserInfo.version || 'Unknown'}`
                : 'Unknown browser';

        message +=
            '<br><strong>Mobile:</strong> ' +
            (compatibility.browserInfo.mobile ? 'Yes' : 'No');
        message +=
            '<br><strong>Secure Context:</strong> ' +
            (location.protocol === 'https:' ? 'Yes' : 'No') +
            '</p>';

        message += '<p><strong>Available APIs:</strong></p><ul>';
        Object.keys(compatibility.apis).forEach((api) => {
            message += `<li>${api}: ${
                compatibility.apis[api] ? '✓' : '✗'
            }</li>`;
        });
        message += '</ul>';

        if (compatibility.issues.length > 0) {
            message += '<p><strong>Issues Found:</strong></p><ul>';
            compatibility.issues.forEach((issue) => {
                switch (issue) {
                    case 'insecure_context':
                        message +=
                            '<li>Site is not served over HTTPS (required for camera access)</li>';
                        break;
                    case 'mobile_no_camera_api':
                        message +=
                            '<li>Mobile browser does not support camera API</li>';
                        break;
                }
            });
            message += '</ul>';
        }

        // Specific guidance based on browser
        if (
            compatibility.browserInfo.chrome &&
            compatibility.browserInfo.mobile
        ) {
            message +=
                '<div class="mt-3"><strong>Chrome Mobile Solutions:</strong>';
            message += '<ol>';
            message += '<li>Ensure Chrome is updated to latest version</li>';
            message += '<li>Try clearing Chrome cache and data</li>';
            message +=
                '<li>Check if "Use camera" is enabled in Chrome settings</li>';
            message += '<li>Try accessing via Chrome Incognito mode</li>';
            message += '</ol></div>';
        }

        message += '</div>';

        if (scannerContainer) {
            scannerContainer.innerHTML = message;
        }
    }

    /**
     * Determine if a barcode is a UUID (QR code) or UPC code.
     * @param {string} barcode Barcode data
     * @returns {string} Type: 'uuid', 'upc', or 'unknown'
     */
    function determineBarcodeType(barcode) {
        // UUID pattern (standard 8-4-4-4-12 format)
        const uuidPattern =
            /^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/;

        // UPC pattern (8-14 digits)
        const upcPattern = /^\d{8,14}$/;

        if (uuidPattern.test(barcode)) {
            return 'uuid';
        } else if (upcPattern.test(barcode)) {
            return 'upc';
        } else {
            return 'unknown';
        }
    }

    /**
     * Process a barcode (from scan or manual entry).
     *
     * @param {string} barcode Barcode data
     * @param {string} format Barcode format (optional)
     */
    function processBarcode(barcode, format = 'unknown') {
        Log.debug('Processing barcode:', barcode, 'Format:', format);

        // Determine barcode type
        const barcodeType = determineBarcodeType(barcode);

        Log.debug('Determined barcode type:', barcodeType);

        // Show processing state
        const processingBtn = document.getElementById('scanner-manual-btn');
        if (processingBtn) {
            processingBtn.disabled = true;
            processingBtn.textContent = 'Processing...';
        }

        // Update status message
        updateStatusMessage(
            `Processing ${barcodeType} code: ${barcode}...`,
            'info'
        );

        // Process via removal endpoint
        processRemoval(barcode, barcodeType).finally(() => {
            // Reset button state
            if (processingBtn) {
                processingBtn.disabled = false;
                processingBtn.textContent = 'Process';
            }
        });
    }

    /**
     * Process item removal using Moodle web service.
     *
     * @param {string} barcode Barcode data
     * @param {string} barcodeType Type of barcode (uuid, upc, unknown)
     * @returns {Promise} Processing promise
     */
    async function processRemoval(barcode, barcodeType) {
        if (!window.M || !window.M.cfg) {
            throw new Error(
                'Moodle environment not properly loaded. Please refresh the page.'
            );
        }

        Log.debug(
            'local_equipment/remove-items-scanner: Processing removal request',
            {
                barcode: barcode,
                type: barcodeType,
                timestamp: new Date().toISOString(),
            }
        );

        try {
            const request = {
                methodname: 'local_equipment_validate_removal',
                args: {
                    barcode: barcode,
                    type: barcodeType,
                },
            };

            Log.debug(
                'local_equipment/remove-items-scanner: Making AJAX request',
                request
            );

            const response = await Ajax.call([request]);
            const data = await response[0];

            Log.debug(
                'local_equipment/remove-items-scanner: Raw response received',
                {
                    response: response,
                    data: data,
                    responseType: typeof data,
                    keys: Object.keys(data || {}),
                    stringified: JSON.stringify(data),
                }
            );

            // Check if we got a valid response structure
            if (!data || typeof data !== 'object') {
                Log.error(
                    'local_equipment/remove-items-scanner: Invalid response structure',
                    {
                        response: response,
                        data: data,
                        dataType: typeof data,
                    }
                );

                throw new Error(
                    'Invalid response from server - expected object, got: ' +
                        typeof data
                );
            }

            // Enhanced debugging: log every property of the response
            Log.debug(
                'local_equipment/remove-items-scanner: Complete response analysis',
                {
                    fullResponse: response,
                    dataObject: data,
                    dataKeys: Object.keys(data),
                    dataValues: Object.values(data),
                    hasSuccessProperty: 'success' in data,
                    successValue: data.success,
                    successType: typeof data.success,
                    hasMessageProperty: 'message' in data,
                    messageValue: data.message,
                    hasErrorProperty: 'error' in data,
                    errorValue: data.error,
                    hasErrorCodeProperty: 'error_code' in data,
                    errorCodeValue: data.error_code,
                    hasExceptionProperty: 'exception' in data,
                    exceptionValue: data.exception,
                    responseAsString: JSON.stringify(data, null, 2),
                }
            );

            // Log the complete response structure for debugging
            if (data.success === true) {
                Log.debug(
                    'local_equipment/remove-items-scanner: Success response received',
                    data
                );
                handleRemovalSuccess(data);
            } else if (data.success === false) {
                Log.debug(
                    'local_equipment/remove-items-scanner: Error response received',
                    data
                );
                handleRemovalError(data);
            } else {
                // Handle case where success property is missing or invalid
                debugAjaxResponse(
                    'Response missing or invalid success property',
                    data,
                    {
                        Barcode: barcode,
                        'Barcode Type': barcodeType,
                        Function: 'processRemoval',
                        'All Properties':
                            Object.getOwnPropertyNames(data).join(', '),
                    }
                );

                // Check if this looks like a Moodle error response
                let errorMessage = 'Unknown response format from server';
                let debugInfo = data;

                if (data.exception) {
                    // This looks like a Moodle exception response
                    errorMessage = `Moodle Exception: ${data.exception} - ${
                        data.message || 'No message provided'
                    }`;
                    if (data.debuginfo) {
                        errorMessage += ` (Debug: ${data.debuginfo})`;
                    }
                } else if (data.error) {
                    // This looks like an error response without success property
                    errorMessage = `Server Error: ${data.error}`;
                } else if (data.message && !data.success) {
                    // Message without success - probably an error
                    errorMessage = `Server Response: ${data.message}`;
                } else {
                    // Try to show any message that came back
                    errorMessage =
                        data.message ||
                        data.error ||
                        'Unknown response format from server';
                    errorMessage += ` (Server returned: ${JSON.stringify(
                        data
                    )})`;
                }

                handleRemovalError({
                    success: false,
                    message: errorMessage,
                    error_code: 'invalid_response_format',
                    debug_info: debugInfo,
                });
            }
        } catch (error) {
            Log.error(
                'local_equipment/remove-items-scanner: Exception during removal processing',
                {
                    error: error.message,
                    stack: error.stack,
                    barcode: barcode,
                    barcodeType: barcodeType,
                    timestamp: new Date().toISOString(),
                }
            );

            // Enhanced error message handling
            let errorMessage = 'Network or server error occurred. ';
            let isKnownError = false;

            // Check for specific Moodle error patterns
            if (error.message && error.message.includes('invalidparameter')) {
                errorMessage = 'Invalid barcode format provided to server.';
                isKnownError = true;
            } else if (
                error.message &&
                error.message.includes('nopermissions')
            ) {
                errorMessage = 'You do not have permission to remove items.';
                isKnownError = true;
            } else if (
                error.message &&
                error.message.includes('requireloggedin')
            ) {
                errorMessage = 'Please log in to remove items.';
                isKnownError = true;
            } else if (error.message && error.message.includes('webservice')) {
                errorMessage = 'Web service error: ' + error.message;
                isKnownError = true;
            } else if (error.message && error.message.includes('network')) {
                errorMessage =
                    'Network connection error. Please check your connection.';
                isKnownError = true;
            }

            // For unknown errors, show more detail in developer mode
            if (!isKnownError) {
                // Check if we're in developer mode by looking for debug indicators
                const isDeveloperMode =
                    document.body.classList.contains('debug') ||
                    (window.M && window.M.cfg && window.M.cfg.developerdebug);

                if (isDeveloperMode) {
                    errorMessage =
                        'Exception: ' +
                        error.message +
                        ' (Check browser console for details)';
                } else {
                    errorMessage +=
                        'Please contact administrator if this persists.';
                }
            }

            // Use Moodle's notification system for better error display
            Notification.addNotification({
                message: errorMessage,
                type: 'error',
            });

            updateStatusMessage(errorMessage, 'danger');
        }
    }

    /**
     * Handle successful removal response.
     * @param {Object} data Response data
     */
    function handleRemovalSuccess(data) {
        // Update session tracking
        sessionRemovedCount++;
        sessionRemovedItems.push({
            id: data.item_id,
            uuid: data.uuid,
            product_name: data.product_name,
            removal_method: data.removal_method,
            was_in_print_queue: data.was_in_print_queue || false,
        });

        // Build success message based on what happened
        let message = `Removed: ${data.product_name}`;

        if (data.was_in_print_queue) {
            message += ' (also removed from print queue)';
        }

        // Use the enhanced message from the server if available
        const serverMessage = data.message;
        if (
            serverMessage &&
            serverMessage !== 'Item successfully removed from inventory'
        ) {
            message = serverMessage;
        }

        showSuccessMessage(message);
        updateStatusMessage(
            serverMessage || `Successfully removed item: ${data.product_name}`,
            'success'
        );

        // Refresh print queue notification if item was in queue
        if (data.was_in_print_queue) {
            refreshPrintQueueNotification();
        }

        // Update session display
        updateSessionDisplay();

        // If this is an item details page, refresh the right column
        if (data.redirect_url) {
            // Optionally redirect or update the page
            window.location.href = data.redirect_url;
        }
    }

    /**
     * Refresh the print queue notification to show updated count.
     */
    function refreshPrintQueueNotification() {
        try {
            // Check if the global queue notification object exists
            if (
                window.QRQueueNotification &&
                typeof window.QRQueueNotification.refresh === 'function'
            ) {
                Log.debug('Refreshing print queue notification after removal');
                window.QRQueueNotification.refresh();
            } else {
                Log.debug('Print queue notification not available for refresh');
            }
        } catch (error) {
            Log.error('Error refreshing print queue notification:', error);
        }
    }

    /**
     * Handle removal error response.
     * @param {Object} data Response data
     */
    function handleRemovalError(data) {
        let errorMessage = data.message || 'Removal failed';

        // Handle specific error types
        switch (data.error_code) {
            case 'item_not_found':
                errorMessage = `Equipment item not found: ${data.barcode}`;
                break;
            case 'already_removed':
                errorMessage = `Item has already been removed: ${data.product_name}`;
                break;
            case 'upc_with_qr_exists':
                errorMessage = `This item has a QR code. Please scan the QR code instead of the UPC barcode to remove it.`;
                break;
            case 'item_checked_out':
                errorMessage = `Cannot remove item that is currently checked out to: ${data.current_user}`;
                break;
            case 'invalid_barcode_type':
                errorMessage = `Invalid barcode format. Please scan a valid QR code or UPC barcode.`;
                break;
            default:
                errorMessage = data.message || 'Unknown removal error occurred';
        }

        showErrorMessage(errorMessage);
        updateStatusMessage(errorMessage, 'danger');

        // If removal URL is provided, show link to manual removal
        if (data.manual_removal_url) {
            const alert = document.querySelector('.alert:last-child');
            if (alert) {
                alert.innerHTML += ` <a href="${data.manual_removal_url}" class="alert-link">Remove manually</a>`;
            }
        }
    }

    /**
     * Update session display with removed items count.
     */
    function updateSessionDisplay() {
        // Update any session counter elements
        const sessionCounters = document.querySelectorAll(
            '.session-removed-count'
        );
        sessionCounters.forEach((counter) => {
            counter.textContent = sessionRemovedCount;
        });

        // Show session items if container exists
        if (sessionItems && sessionRemovedCount > 0) {
            let sessionHtml = `<h4>Session Removed Items (${sessionRemovedCount})</h4>`;
            sessionHtml += '<ul class="list-group">';

            sessionRemovedItems.forEach((item) => {
                const methodBadge =
                    item.removal_method === 'emergency_upc'
                        ? '<span class="badge bg-warning">Emergency UPC</span>'
                        : '<span class="badge bg-primary">QR Code</span>';

                sessionHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${item.product_name} (${item.uuid})
                        ${methodBadge}
                    </li>
                `;
            });

            sessionHtml += '</ul>';

            // Only update if content has changed
            if (sessionItems.innerHTML !== sessionHtml) {
                sessionItems.innerHTML = sessionHtml;
            }
        }
    }

    /**
     * Update status message in the UI.
     * @param {string} message Status message to display
     * @param {string} alertType Bootstrap alert type (info, success, warning, danger)
     */
    function updateStatusMessage(message, alertType = 'info') {
        // Look for existing status message container
        let statusContainer = document.getElementById('scanner-status-message');

        if (!statusContainer) {
            // Create status container if it doesn't exist
            statusContainer = document.createElement('div');
            statusContainer.id = 'scanner-status-message';
            statusContainer.className = 'mt-3';

            // Insert after scanner container
            if (scannerContainer && scannerContainer.parentNode) {
                scannerContainer.parentNode.insertBefore(
                    statusContainer,
                    scannerContainer.nextSibling
                );
            }
        }

        const alertClass = `alert alert-${alertType}`;
        statusContainer.innerHTML = `
            <div class="${alertClass}">
                <i class="fa fa-info-circle me-2"></i>
                ${message}
            </div>
        `;

        // Auto-hide info messages after 5 seconds
        if (alertType === 'info') {
            setTimeout(() => {
                if (statusContainer.innerHTML.includes(message)) {
                    statusContainer.innerHTML = '';
                }
            }, 5000);
        }
    }

    /**
     * Show success message.
     *
     * @param {string} message Success message
     */
    function showSuccessMessage(message) {
        const alert = document.createElement('div');
        alert.className =
            'alert alert-success alert-dismissible fade show mt-2';
        alert.innerHTML = `
            <strong>✓ Success!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        if (sessionItems) {
            sessionItems.appendChild(alert);
        } else {
            // Fallback to scanner container
            if (scannerContainer) {
                scannerContainer.appendChild(alert);
            }
        }

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 3000);
    }

    /**
     * Show error message.
     *
     * @param {string} message Error message
     * @param {string} actionUrl Action URL (optional)
     */
    function showErrorMessage(message, actionUrl = null) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show mt-2';
        let content = `<strong>✗ Error:</strong> ${message}`;
        if (actionUrl) {
            content += ` <a href="${actionUrl}" class="alert-link">Take action</a>`;
        }
        content += `<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        alert.innerHTML = content;

        if (sessionItems) {
            sessionItems.appendChild(alert);
        } else {
            // Fallback to scanner container
            if (scannerContainer) {
                scannerContainer.appendChild(alert);
            }
        }
    }

    /**
     * Test network connectivity to the server.
     * @returns {Promise<boolean>} True if connectivity is working
     */
    async function testNetworkConnectivity() {
        try {
            const testUrl =
                M.cfg.wwwroot +
                '/local/equipment/classes/external/validate_removal.php';

            Log.debug('Testing network connectivity to:', testUrl);

            // Send a simple OPTIONS request to test connectivity
            const response = await fetch(testUrl, {
                method: 'OPTIONS',
                headers: {
                    Accept: 'application/json',
                },
            });

            Log.debug('Network test response:', {
                status: response.status,
                ok: response.ok,
                headers: Object.fromEntries(response.headers.entries()),
            });

            return response.ok;
        } catch (error) {
            Log.error('Network connectivity test failed:', error);
            return false;
        }
    }

    /**
     * Adds a network test button to the scanner controls interface.
     */
    function addNetworkTestButton() {
        const scannerControls = document.querySelector('.scanner-controls');
        if (scannerControls && !document.getElementById('test-network-btn')) {
            const testButton = document.createElement('button');
            testButton.id = 'test-network-btn';
            testButton.type = 'button';
            testButton.className = 'btn btn-outline-info btn-sm mt-2';
            testButton.innerHTML = '<i class="fa fa-wifi"></i> Test Network';

            testButton.addEventListener('click', async function () {
                const originalText = this.innerHTML;
                this.disabled = true;
                this.innerHTML =
                    '<i class="fa fa-spinner fa-spin"></i> Testing...';

                const isConnected = await testNetworkConnectivity();

                if (isConnected) {
                    showSuccessMessage('Network connectivity test passed!');
                } else {
                    showErrorMessage(
                        'Network connectivity test failed. Check your connection.'
                    );
                }

                this.disabled = false;
                this.innerHTML = originalText;
            });

            scannerControls.appendChild(testButton);
        }
    }

    // Handle existing manual UUID input if present
    if (lookupBtn) {
        lookupBtn.addEventListener('click', function () {
            const uuid = manualUuid.value.trim();
            if (uuid) {
                processBarcode(uuid, 'manual');
                manualUuid.value = '';
            }
        });
    }

    // Allow Enter key in existing UUID input
    if (manualUuid) {
        manualUuid.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                lookupBtn.click();
            }
        });
    }
}
