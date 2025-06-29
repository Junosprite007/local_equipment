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
 * Universal barcode scanner for QR codes and UPC/EAN barcodes.
 *
 * @module     local_equipment/universal-scanner
 * @copyright  2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Log from 'core/log';
import jsQR from 'local_equipment/jsqr';

/**
 * Universal scanner class for handling QR codes and UPC/EAN barcodes.
 */
export default class UniversalScanner {
    /**
     * Constructor for UniversalScanner.
     *
     * @param {Object} options Scanner configuration options
     */
    constructor(options = {}) {
        this.options = {
            containerId: 'scanner-container',
            videoId: 'scanner-video',
            canvasId: 'scanner-canvas',
            resultCallback: null,
            errorCallback: null,
            scanTypes: ['auto'], // Auto, qr, upc
            timeout: 30000,
            ...options,
        };

        this.isScanning = false;
        this.stream = null;
        this.video = null;
        this.canvas = null;
        this.context = null;
        this.sessionId = this.generateSessionId();
        this.scanAttempts = 0;
        this.maxScanAttempts = 50;

        // Bind methods
        this.startScanning = this.startScanning.bind(this);
        this.stopScanning = this.stopScanning.bind(this);
        this.processFrame = this.processFrame.bind(this);
    }

    /**
     * Initialize the scanner interface.
     *
     * @returns {Promise} Promise that resolves when scanner is ready
     */
    async init() {
        try {
            await this.setupInterface();
            await this.checkCameraSupport();
            return true;
        } catch (error) {
            this.handleError('init_failed', error.message);
            return false;
        }
    }

    /**
     * Setup the scanner interface elements.
     */
    async setupInterface() {
        const container = document.getElementById(this.options.containerId);
        if (!container) {
            throw new Error(
                `Container element ${this.options.containerId} not found`
            );
        }

        // Create video element
        this.video = document.createElement('video');
        this.video.id = this.options.videoId;
        this.video.setAttribute('playsinline', true);
        this.video.setAttribute('autoplay', true);
        this.video.setAttribute('muted', true);
        this.video.style.width = '100%';
        this.video.style.height = 'auto';

        // Create canvas for frame processing
        this.canvas = document.createElement('canvas');
        this.canvas.id = this.options.canvasId;
        this.canvas.style.display = 'none';
        this.context = this.canvas.getContext('2d', {
            willReadFrequently: true,
        });

        // Add elements to container
        container.appendChild(this.video);
        container.appendChild(this.canvas);

        // Add scan overlay
        this.createScanOverlay(container);
    }

    /**
     * Create scanning overlay with target box.
     *
     * @param {HTMLElement} container Container element
     */
    createScanOverlay(container) {
        const overlay = document.createElement('div');
        overlay.className = 'scanner-overlay';
        overlay.innerHTML = `
            <div class="scan-target">
                <div class="scan-corners">
                    <div class="corner top-left"></div>
                    <div class="corner top-right"></div>
                    <div class="corner bottom-left"></div>
                    <div class="corner bottom-right"></div>
                </div>
                <div class="scan-line"></div>
            </div>
            <div class="scan-instructions">
                <p>Position barcode or QR code within the frame</p>
                <div class="scan-status">Ready to scan</div>
            </div>
        `;

        container.appendChild(overlay);
        this.overlay = overlay;
    }

    /**
     * Check if camera access is supported.
     *
     * @returns {Promise<boolean>} True if camera is supported
     */
    async checkCameraSupport() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('Camera access not supported in this browser');
        }

        // Check for BarcodeDetector API support
        this.hasBarcodeDetector = 'BarcodeDetector' in window;

        return true;
    }

    /**
     * Start scanning for barcodes.
     *
     * @returns {Promise} Promise that resolves when scanning starts
     */
    async startScanning() {
        if (this.isScanning) {
            return;
        }

        try {
            this.updateStatus('Requesting camera access...');

            // Request camera access
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment', // Prefer rear camera
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
            });

            this.video.srcObject = this.stream;
            await this.video.play();

            // Set canvas dimensions to match video
            this.canvas.width = this.video.videoWidth;
            this.canvas.height = this.video.videoHeight;

            this.isScanning = true;
            this.scanAttempts = 0;
            this.updateStatus('Scanning...');

            // Start scanning loop
            this.scanLoop();
        } catch (error) {
            this.handleError('camera_access_failed', error.message);
        }
    }

    /**
     * Stop scanning and release camera.
     */
    stopScanning() {
        this.isScanning = false;

        if (this.stream) {
            this.stream.getTracks().forEach((track) => track.stop());
            this.stream = null;
        }

        if (this.video) {
            this.video.srcObject = null;
        }

        this.updateStatus('Scanner stopped');
    }

    /**
     * Main scanning loop.
     */
    async scanLoop() {
        if (!this.isScanning) {
            return;
        }

        try {
            await this.processFrame();
            this.scanAttempts++;

            // Check if we've exceeded max attempts
            if (this.scanAttempts >= this.maxScanAttempts) {
                this.updateStatus('Scan timeout - please try again');
                setTimeout(() => {
                    this.scanAttempts = 0;
                    this.updateStatus('Scanning...');
                }, 2000);
            }
        } catch (error) {
            // Continue scanning even if frame processing fails
            Log.debug('Frame processing error:', error);
        }

        // Continue scanning
        if (this.isScanning) {
            requestAnimationFrame(() => this.scanLoop());
        }
    }

    /**
     * Process current video frame for barcodes.
     */
    async processFrame() {
        if (
            !this.video ||
            this.video.readyState !== this.video.HAVE_ENOUGH_DATA
        ) {
            return;
        }

        // Draw current frame to canvas
        this.context.drawImage(
            this.video,
            0,
            0,
            this.canvas.width,
            this.canvas.height
        );

        // Try native BarcodeDetector first (Chrome/Edge)
        if (this.hasBarcodeDetector) {
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

                const barcodes = await detector.detect(this.canvas);

                if (barcodes.length > 0) {
                    const barcode = barcodes[0];
                    await this.processScanResult(
                        barcode.rawValue,
                        barcode.format
                    );
                    return;
                }
            } catch (error) {
                // Fall back to manual processing
                Log.debug('BarcodeDetector failed:', error);
            }
        }

        // Fallback: Try to detect QR codes manually using image data
        await this.detectQRCodeManually();
    }

    /**
     * Manual QR code detection fallback.
     */
    async detectQRCodeManually() {
        try {
            const imageData = this.context.getImageData(
                0,
                0,
                this.canvas.width,
                this.canvas.height
            );

            // Use jsQR library for QR code detection
            const code = jsQR.scan(
                imageData.data,
                imageData.width,
                imageData.height
            );

            if (code && code.data) {
                await this.processScanResult(code.data, 'qr_code');
            }
        } catch (error) {
            // Silently fail - this is just a fallback method
            Log.debug('Manual QR detection failed:', error);
        }
    }

    /**
     * Process a successful scan result.
     *
     * @param {string} data Scanned barcode data
     * @param {string} format Barcode format
     */
    async processScanResult(data, format) {
        if (!data || data.trim() === '') {
            return;
        }

        this.updateStatus('Processing scan...');
        this.isScanning = false; // Stop scanning while processing

        try {
            // Determine scan type
            let scanType = 'auto';
            if (format === 'qr_code') {
                scanType = 'qr';
            } else if (['ean_13', 'ean_8', 'upc_a', 'upc_e'].includes(format)) {
                scanType = 'upc';
            }

            Log.debug('Processing scan result:', {
                data: data,
                format: format,
                scanType: scanType,
            });

            // For add-items page, bypass web service and call callback directly
            if (this.options.resultCallback) {
                const result = {
                    success: true,
                    data: {
                        barcode_data: data,
                        scan_type: scanType,
                        format: format,
                    },
                    timestamp: Date.now(),
                };

                this.updateStatus('Scan successful!');
                this.showScanSuccess();
                this.options.resultCallback(result);
                return;
            }

            // Fallback: Send to server for processing (for other pages)
            const result = await this.sendScanToServer(data, scanType);

            if (result.success) {
                this.updateStatus('Scan successful!');
                this.showScanSuccess();

                // Call result callback if provided
                if (this.options.resultCallback) {
                    this.options.resultCallback(result);
                }
            } else {
                this.handleScanError(result);
            }
        } catch (error) {
            this.handleError('processing_failed', error.message);
        }
    }

    /**
     * Send scan data to server for processing.
     *
     * @param {string} barcodeData Scanned barcode data
     * @param {string} scanType Type of scan (qr, upc, auto)
     * @returns {Promise<Object>} Server response
     */
    async sendScanToServer(barcodeData, scanType) {
        const request = {
            methodname: 'local_equipment_process_scan',
            args: {
                barcode_data: barcodeData,
                scan_type: scanType,
                session_id: this.sessionId,
            },
        };

        const response = await Ajax.call([request])[0];
        return response;
    }

    /**
     * Handle scan error from server.
     *
     * @param {Object} result Error result from server
     */
    handleScanError(result) {
        const errorMessages = {
            empty_barcode: 'No barcode data detected',
            invalid_uuid: 'Invalid QR code format',
            item_not_found: 'Equipment item not found',
            product_not_found: 'Product not found in database',
            unknown_type: 'Unknown barcode type',
        };

        const message =
            errorMessages[result.error_code] || result.message || 'Scan failed';
        this.updateStatus(`Error: ${message}`);

        // Resume scanning after error
        setTimeout(() => {
            this.isScanning = true;
            this.updateStatus('Scanning...');
        }, 2000);
    }

    /**
     * Handle general errors.
     *
     * @param {string} errorCode Error code
     * @param {string} message Error message
     */
    handleError(errorCode, message) {
        Log.error(`Scanner error [${errorCode}]:`, message);
        this.updateStatus(`Error: ${message}`);

        if (this.options.errorCallback) {
            this.options.errorCallback(errorCode, message);
        }
    }

    /**
     * Update status display.
     *
     * @param {string} message Status message
     */
    updateStatus(message) {
        const statusElement = document.querySelector('.scan-status');
        if (statusElement) {
            statusElement.textContent = message;
        }
    }

    /**
     * Show scan success animation.
     */
    showScanSuccess() {
        const target = document.querySelector('.scan-target');
        if (target) {
            target.classList.add('scan-success');
            setTimeout(() => {
                target.classList.remove('scan-success');
            }, 1000);
        }
    }

    /**
     * Generate unique session ID.
     *
     * @returns {string} Session ID
     */
    generateSessionId() {
        return (
            'scan_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)
        );
    }

    /**
     * Process file upload for barcode scanning.
     *
     * @param {File} file Image file to process
     * @returns {Promise} Promise that resolves with scan result
     */
    async processFileUpload(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = async (e) => {
                try {
                    const img = new Image();
                    img.onload = async () => {
                        // Draw image to canvas
                        this.canvas.width = img.width;
                        this.canvas.height = img.height;
                        this.context.drawImage(img, 0, 0);

                        // Try to detect barcodes
                        if (this.hasBarcodeDetector) {
                            try {
                                // eslint-disable-next-line no-undef
                                const detector = new BarcodeDetector({
                                    formats: [
                                        'qr_code',
                                        'ean_13',
                                        'ean_8',
                                        'upc_a',
                                        'upc_e',
                                    ],
                                });

                                const barcodes = await detector.detect(
                                    this.canvas
                                );

                                if (barcodes.length > 0) {
                                    const result = await this.processScanResult(
                                        barcodes[0].rawValue,
                                        barcodes[0].format
                                    );
                                    resolve(result);
                                } else {
                                    reject(
                                        new Error('No barcode found in image')
                                    );
                                }
                            } catch (error) {
                                reject(error);
                            }
                        } else {
                            reject(
                                new Error('Barcode detection not supported')
                            );
                        }
                    };

                    img.src = e.target.result;
                } catch (error) {
                    reject(error);
                }
            };

            reader.onerror = () => reject(new Error('Failed to read file'));
            reader.readAsDataURL(file);
        });
    }

    /**
     * Process manual barcode entry.
     *
     * @param {string} barcodeData Manually entered barcode
     * @param {string} scanType Type of scan
     * @returns {Promise} Promise that resolves with scan result
     */
    async processManualEntry(barcodeData, scanType = 'auto') {
        try {
            this.updateStatus('Processing manual entry...');
            const result = await this.sendScanToServer(barcodeData, scanType);

            if (result.success) {
                this.updateStatus('Manual entry successful!');
                if (this.options.resultCallback) {
                    this.options.resultCallback(result);
                }
            } else {
                this.handleScanError(result);
            }

            return result;
        } catch (error) {
            this.handleError('manual_entry_failed', error.message);
            throw error;
        }
    }

    /**
     * Destroy scanner and clean up resources.
     */
    destroy() {
        this.stopScanning();

        // Remove elements
        if (this.video && this.video.parentNode) {
            this.video.parentNode.removeChild(this.video);
        }

        if (this.canvas && this.canvas.parentNode) {
            this.canvas.parentNode.removeChild(this.canvas);
        }

        if (this.overlay && this.overlay.parentNode) {
            this.overlay.parentNode.removeChild(this.overlay);
        }
    }
}

/**
 * Initialize scanner with default options.
 *
 * @param {Object} options Scanner options
 * @returns {UniversalScanner} Scanner instance
 */
export const init = (options = {}) => {
    return new UniversalScanner(options);
};
