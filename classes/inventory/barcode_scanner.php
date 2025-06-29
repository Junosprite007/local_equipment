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
 * Barcode scanner class for QR codes and UPC/EAN barcodes.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\inventory;

defined('MOODLE_INTERNAL') || die();

/**
 * Universal barcode scanner supporting QR codes and UPC/EAN barcodes.
 */
class barcode_scanner {

    /** @var int Scan timeout in seconds */
    const SCAN_TIMEOUT = 30;

    /** @var int Maximum retries for API calls */
    const MAX_API_RETRIES = 3;

    /** @var string Brocade.io API base URL */
    const BROCADE_API_URL = 'https://api.brocade.io/v1/products/';

    /** @var array Supported barcode formats */
    const SUPPORTED_FORMATS = [
        'qr_code',
        'upc_a',
        'upc_e',
        'ean_13',
        'ean_8',
        'code_128',
        'code_39'
    ];

    /**
     * Process a scanned barcode (QR code or UPC/EAN).
     *
     * @param string $barcode_data The scanned barcode data
     * @param string $scan_type Type of scan ('qr' or 'upc')
     * @return array Scan result with item information
     */
    public function process_scan(string $barcode_data, string $scan_type = 'auto'): array {
        global $DB, $USER;

        try {
            // Clean and validate barcode data
            $barcode_data = trim($barcode_data);
            if (empty($barcode_data)) {
                return $this->create_error_result('empty_barcode', 'Barcode data is empty');
            }

            // Determine scan type if auto-detection requested
            if ($scan_type === 'auto') {
                $scan_type = $this->detect_barcode_type($barcode_data);
            }

            // Process based on barcode type
            switch ($scan_type) {
                case 'qr':
                    return $this->process_qr_code($barcode_data);
                case 'upc':
                case 'ean':
                    return $this->process_upc_barcode($barcode_data);
                default:
                    return $this->create_error_result('unknown_type', 'Unknown barcode type');
            }
        } catch (\Exception $e) {
            return $this->create_error_result('scan_error', $e->getMessage());
        }
    }

    /**
     * Process QR code scan (equipment UUID).
     *
     * @param string $uuid Equipment UUID from QR code
     * @return array Scan result
     */
    private function process_qr_code(string $uuid): array {
        global $DB;

        // Validate UUID format
        if (!$this->is_valid_uuid($uuid)) {
            return $this->create_error_result('invalid_uuid', 'Invalid UUID format');
        }

        // Look up equipment item by UUID
        $item = $DB->get_record('local_equipment_items', ['uuid' => $uuid]);
        if (!$item) {
            return $this->create_error_result('item_not_found', 'Equipment item not found');
        }

        // Get product information
        $product = $DB->get_record('local_equipment_products', ['id' => $item->productid]);
        if (!$product) {
            return $this->create_error_result('product_not_found', 'Product information not found');
        }

        // Get location information
        $location = null;
        if ($item->locationid) {
            $location = $DB->get_record('local_equipment_locations', ['id' => $item->locationid]);
        }

        // Get current user assignment if checked out
        $current_user = null;
        if ($item->current_userid) {
            $current_user = $DB->get_record('user', ['id' => $item->current_userid], 'id,firstname,lastname,email');
        }

        return $this->create_success_result([
            'scan_type' => 'qr',
            'barcode_data' => $uuid,
            'item' => $item,
            'product' => $product,
            'location' => $location,
            'current_user' => $current_user,
            'available_actions' => $this->get_available_actions($item)
        ]);
    }

    /**
     * Process UPC/EAN barcode scan.
     *
     * @param string $barcode UPC/EAN barcode
     * @return array Scan result
     */
    private function process_upc_barcode(string $barcode): array {
        global $DB;

        // First check if we have this product in our database
        $product = $DB->get_record('local_equipment_products', ['upc' => $barcode]);

        if ($product) {
            // Get available items for this product
            $available_items = $DB->get_records('local_equipment_items', [
                'productid' => $product->id,
                'status' => 'available'
            ]);

            return $this->create_success_result([
                'scan_type' => 'upc',
                'barcode_data' => $barcode,
                'product' => $product,
                'available_items' => $available_items,
                'item_count' => count($available_items),
                'source' => 'local_database'
            ]);
        }

        // If not in our database, try brocade.io lookup
        $product_info = $this->lookup_product_brocade($barcode);

        if ($product_info) {
            return $this->create_success_result([
                'scan_type' => 'upc',
                'barcode_data' => $barcode,
                'product_info' => $product_info,
                'source' => 'brocade_api',
                'can_add_to_catalog' => true
            ]);
        }

        return $this->create_error_result('product_not_found', 'Product not found in database or external API');
    }

    /**
     * Lookup product information from brocade.io API.
     *
     * @param string $barcode UPC/EAN barcode
     * @return array|null Product information or null if not found
     */
    private function lookup_product_brocade(string $barcode): ?array {
        $curl = new \curl();
        $curl->setopt([
            'CURLOPT_TIMEOUT' => self::SCAN_TIMEOUT,
            'CURLOPT_CONNECTTIMEOUT' => 10,
            'CURLOPT_USERAGENT' => 'Moodle Equipment Plugin/1.0'
        ]);

        $retries = 0;
        while ($retries < self::MAX_API_RETRIES) {
            try {
                $url = self::BROCADE_API_URL . urlencode($barcode);
                $response = $curl->get($url);

                if ($curl->get_errno() === 0 && $curl->get_info()['http_code'] === 200) {
                    $data = json_decode($response, true);

                    if ($data && isset($data['product'])) {
                        return [
                            'name' => $data['product']['title'] ?? 'Unknown Product',
                            'description' => $data['product']['description'] ?? '',
                            'manufacturer' => $data['product']['brand'] ?? '',
                            'upc' => $barcode,
                            'category' => $data['product']['category'] ?? '',
                            'image_url' => $data['product']['images'][0] ?? null
                        ];
                    }
                }

                break; // Exit retry loop if we got a response (even if empty)

            } catch (\Exception $e) {
                $retries++;
                if ($retries >= self::MAX_API_RETRIES) {
                    debugging('Brocade API error after ' . self::MAX_API_RETRIES . ' retries: ' . $e->getMessage());
                    break;
                }
                usleep(500000); // Wait 0.5 seconds before retry
            }
        }

        return null;
    }

    /**
     * Detect barcode type based on data format.
     *
     * @param string $barcode_data Barcode data
     * @return string Detected type ('qr', 'upc', or 'unknown')
     */
    private function detect_barcode_type(string $barcode_data): string {
        // Check if it's a UUID (QR code)
        if ($this->is_valid_uuid($barcode_data)) {
            return 'qr';
        }

        // Check if it's a UPC/EAN barcode (numeric, specific lengths)
        if (preg_match('/^\d{8}$|^\d{12,14}$/', $barcode_data)) {
            return 'upc';
        }

        // Default to QR for other formats
        return 'qr';
    }

    /**
     * Validate UUID format.
     *
     * @param string $uuid UUID string
     * @return bool True if valid UUID
     */
    private function is_valid_uuid(string $uuid): bool {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }

    /**
     * Get available actions for an equipment item.
     *
     * @param \stdClass $item Equipment item
     * @return array Available actions
     */
    private function get_available_actions(\stdClass $item): array {
        $actions = [];

        switch ($item->status) {
            case 'available':
                $actions[] = 'checkout';
                $actions[] = 'transfer';
                $actions[] = 'update_condition';
                break;
            case 'checked_out':
                $actions[] = 'checkin';
                $actions[] = 'view_assignment';
                break;
            case 'in_transit':
                $actions[] = 'complete_transfer';
                $actions[] = 'view_transfer';
                break;
            case 'maintenance':
                $actions[] = 'complete_maintenance';
                $actions[] = 'update_condition';
                break;
        }

        return $actions;
    }

    /**
     * Create success result array.
     *
     * @param array $data Result data
     * @return array Success result
     */
    private function create_success_result(array $data): array {
        return [
            'success' => true,
            'timestamp' => time(),
            'data' => $data
        ];
    }

    /**
     * Create error result array.
     *
     * @param string $error_code Error code
     * @param string $message Error message
     * @return array Error result
     */
    private function create_error_result(string $error_code, string $message): array {
        return [
            'success' => false,
            'error_code' => $error_code,
            'message' => $message,
            'timestamp' => time()
        ];
    }

    /**
     * Validate barcode format.
     *
     * @param string $barcode Barcode data
     * @param string $format Expected format
     * @return bool True if valid
     */
    public function validate_barcode_format(string $barcode, string $format): bool {
        switch ($format) {
            case 'upc_a':
                return preg_match('/^\d{12}$/', $barcode);
            case 'upc_e':
                return preg_match('/^\d{8}$/', $barcode);
            case 'ean_13':
                return preg_match('/^\d{13}$/', $barcode);
            case 'ean_8':
                return preg_match('/^\d{8}$/', $barcode);
            case 'qr_code':
                return !empty($barcode); // QR codes can contain any data
            default:
                return false;
        }
    }

    /**
     * Get supported barcode formats.
     *
     * @return array Supported formats
     */
    public static function get_supported_formats(): array {
        return self::SUPPORTED_FORMATS;
    }
}
