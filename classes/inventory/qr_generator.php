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
 * QR code generator class for the Equipment plugin.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\inventory;

defined('MOODLE_INTERNAL') || die();

/**
 * QR code generation with Level H error correction (30% redundancy).
 */
class qr_generator {

    /** @var int Default QR code size */
    const DEFAULT_SIZE = 200;

    /** @var int QR codes per printable sheet (4x7 grid) */
    const CODES_PER_SHEET = 28;

    /** @var int Columns per sheet */
    const SHEET_COLUMNS = 4;

    /** @var int Rows per sheet */
    const SHEET_ROWS = 7;

    /**
     * Generate QR code for an equipment item UUID.
     *
     * @param string $itemuuid Item UUID
     * @param int $size QR code size in pixels
     * @return string Base64 encoded PNG image data
     */
    public function generate_item_qr($itemuuid, $size = self::DEFAULT_SIZE) {
        try {
            // Use Moodle's native QR code class
            $qrcode = new \core_qrcode($itemuuid);

            // Get the barcode array
            $barcode_array = $qrcode->getBarcodeArray();

            if (!$barcode_array) {
                throw new \moodle_exception('qrgenerationfailed', 'local_equipment', '', 'Failed to generate QR code array');
            }

            // Calculate dimensions
            $pixel_size = max(1, intval($size / max($barcode_array['num_cols'], $barcode_array['num_rows'])));
            $image_width = $barcode_array['num_cols'] * $pixel_size;
            $image_height = $barcode_array['num_rows'] * $pixel_size;

            // Create image
            $image = imagecreate($image_width, $image_height);

            // Set colors
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);

            // Fill background
            imagefill($image, 0, 0, $white);

            // Draw QR code
            for ($row = 0; $row < $barcode_array['num_rows']; $row++) {
                for ($col = 0; $col < $barcode_array['num_cols']; $col++) {
                    if ($barcode_array['bcode'][$row][$col] == 1) {
                        imagefilledrectangle(
                            $image,
                            $col * $pixel_size,
                            $row * $pixel_size,
                            ($col + 1) * $pixel_size - 1,
                            ($row + 1) * $pixel_size - 1,
                            $black
                        );
                    }
                }
            }

            // Capture image as PNG
            ob_start();
            imagepng($image);
            $imagedata = ob_get_contents();
            ob_end_clean();

            // Clean up
            imagedestroy($image);

            // Return base64 encoded image
            return base64_encode($imagedata);
        } catch (\Exception $e) {
            throw new \moodle_exception('qrgenerationfailed', 'local_equipment', '', $e->getMessage());
        }
    }

    /**
     * Generate printable sheet of QR codes (supports multiple sheets for counts > 28).
     *
     * @param int $count Number of QR codes to generate
     * @return array Array containing HTML content and CSS for printing
     */
    public function generate_printable_sheet($count = self::CODES_PER_SHEET) {
        global $DB, $USER;

        // No artificial limit - generate as many as requested
        $qr_codes = [];
        $time = time();

        // Generate UUIDs and QR codes
        for ($i = 0; $i < $count; $i++) {
            $uuid = self::generate_uuid();
            $qr_data = $this->generate_item_qr($uuid, 100); // Optimized size for 4x7 grid printing

            $qr_codes[] = [
                'uuid' => $uuid,
                'qr_data' => $qr_data,
                'created_time' => $time
            ];

            // Store UUID in history table for future reference
            $history_record = new \stdClass();
            $history_record->itemid = 0; // Will be updated when item is created
            $history_record->uuid = $uuid;
            $history_record->is_active = 0; // Not active until assigned to an item
            $history_record->created_by = $USER->id;
            $history_record->timecreated = $time;

            $DB->insert_record('local_equipment_uuid_history', $history_record);
        }

        // Generate HTML content (supports multiple sheets)
        $html = $this->generate_sheet_html($qr_codes, $count);
        $css = $this->get_printable_sheet_css();

        return [
            'html' => $html,
            'css' => $css,
            'qr_codes' => $qr_codes
        ];
    }

    /**
     * Create new equipment items with QR codes.
     *
     * @param int $productid Product ID
     * @param int $count Number of items to create
     * @param int $locationid Initial location ID
     * @return array Array of created item records
     */
    public function create_new_items_with_qr($productid, $count, $locationid) {
        global $DB, $USER;

        $items = [];
        $time = time();

        try {
            $transaction = $DB->start_delegated_transaction();

            for ($i = 0; $i < $count; $i++) {
                $uuid = self::generate_uuid();

                // Create equipment item
                $item = new \stdClass();
                $item->uuid = $uuid;
                $item->productid = $productid;
                $item->locationid = $locationid;
                $item->status = 'available';
                $item->condition_status = 'excellent';
                $item->timecreated = $time;
                $item->timemodified = $time;

                $item->id = $DB->insert_record('local_equipment_items', $item);

                // Create UUID history record
                $history_record = new \stdClass();
                $history_record->itemid = $item->id;
                $history_record->uuid = $uuid;
                $history_record->is_active = 1;
                $history_record->created_by = $USER->id;
                $history_record->timecreated = $time;

                $DB->insert_record('local_equipment_uuid_history', $history_record);

                $items[] = $item;
            }

            $transaction->allow_commit();
            return $items;
        } catch (\Exception $e) {
            $transaction->rollback($e);
            throw new \moodle_exception('itemcreationfailed', 'local_equipment', '', $e->getMessage());
        }
    }

    /**
     * Regenerate QR code for an existing item.
     *
     * @param int $itemid Item ID
     * @param string $reason Reason for regeneration
     * @return string New UUID
     */
    public function regenerate_qr_for_item($itemid, $reason = '') {
        global $DB, $USER;

        try {
            $transaction = $DB->start_delegated_transaction();

            // Get the current item
            $item = $DB->get_record('local_equipment_items', ['id' => $itemid]);
            if (!$item) {
                throw new \moodle_exception('itemnotfound', 'local_equipment');
            }

            // Deactivate old UUID
            $DB->set_field_select(
                'local_equipment_uuid_history',
                'is_active',
                0,
                'itemid = :itemid AND is_active = 1',
                ['itemid' => $itemid]
            );

            $DB->set_field_select(
                'local_equipment_uuid_history',
                'deactivated_by',
                $USER->id,
                'itemid = :itemid AND uuid = :uuid',
                ['itemid' => $itemid, 'uuid' => $item->uuid]
            );

            $DB->set_field_select(
                'local_equipment_uuid_history',
                'deactivated_reason',
                $reason,
                'itemid = :itemid AND uuid = :uuid',
                ['itemid' => $itemid, 'uuid' => $item->uuid]
            );

            $DB->set_field_select(
                'local_equipment_uuid_history',
                'timedeactivated',
                time(),
                'itemid = :itemid AND uuid = :uuid',
                ['itemid' => $itemid, 'uuid' => $item->uuid]
            );

            // Generate new UUID
            $new_uuid = self::generate_uuid();

            // Update item with new UUID
            $item->uuid = $new_uuid;
            $item->timemodified = time();
            $DB->update_record('local_equipment_items', $item);

            // Create new UUID history record
            $history_record = new \stdClass();
            $history_record->itemid = $itemid;
            $history_record->uuid = $new_uuid;
            $history_record->is_active = 1;
            $history_record->created_by = $USER->id;
            $history_record->timecreated = time();

            $DB->insert_record('local_equipment_uuid_history', $history_record);

            $transaction->allow_commit();
            return $new_uuid;
        } catch (\Exception $e) {
            $transaction->rollback($e);
            throw new \moodle_exception('qrregenerationfailed', 'local_equipment', '', $e->getMessage());
        }
    }

    /**
     * Get CSS for printable QR code sheets.
     *
     * @return string CSS content
     */
    public function get_printable_sheet_css() {
        return '
        @media print {
            @page {
                size: 8.5in 11in;
                margin: 0.5in;
            }

            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
            }

            .qr-sheet {
                width: 100%;
                height: 100%;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                grid-template-rows: repeat(7, 1fr);
                gap: 2px;
                border: 1px solid #ccc;
            }

            .qr-cell {
                border: 1px solid #ddd;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 5px;
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
                max-width: 90%;
                max-height: 90%;
                width: auto;
                height: auto;
            }

            .perforation-line-h {
                border-top: 1px solid #ccc;
                border-style: dashed;
            }

            .perforation-line-v {
                border-left: 1px solid #ccc;
                border-style: dashed;
            }
        }

        @media screen {
            .qr-sheet {
                max-width: 8.5in;
                margin: auto;
                border: 1px solid #ccc;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                grid-template-rows: repeat(7, 1fr);
                gap: 2px;
                aspect-ratio: 8.5/11;
            }

            .qr-cell {
                border: 1px solid #ddd;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 5px;
                background: white;
            }

            .qr-code {
                max-width: 90%;
                max-height: 90%;
                width: auto;
                height: auto;
            }

            .print-button {
                text-align: center;
                margin: 20px;
            }
        }';
    }

    /**
     * Generate HTML for printable QR code sheet(s).
     *
     * @param array $qr_codes Array of QR code data
     * @param int $total_count Total number of QR codes requested
     * @return string HTML content
     */
    private function generate_sheet_html($qr_codes, $total_count) {
        $html = '';
        $sheets_needed = ceil($total_count / self::CODES_PER_SHEET);

        for ($sheet = 0; $sheet < $sheets_needed; $sheet++) {
            $html .= '<div class="qr-sheet">';

            // Calculate start and end indices for this sheet
            $start_index = $sheet * self::CODES_PER_SHEET;
            $end_index = min($start_index + self::CODES_PER_SHEET, $total_count);

            // Generate cells for this sheet (always 28 cells per sheet for consistent layout)
            for ($i = 0; $i < self::CODES_PER_SHEET; $i++) {
                $qr_index = $start_index + $i;

                $html .= '<div class="qr-cell';

                // Add perforation line classes
                if ($i % self::SHEET_COLUMNS !== 0) {
                    $html .= ' perforation-line-v';
                }
                if ($i >= self::SHEET_COLUMNS) {
                    $html .= ' perforation-line-h';
                }

                $html .= '">';

                // Only add QR code if we have one for this position
                if ($qr_index < $total_count && isset($qr_codes[$qr_index])) {
                    $html .= '<img src="data:image/png;base64,' . $qr_codes[$qr_index]['qr_data'] . '"
                             class="qr-code" alt="QR Code: ' . $qr_codes[$qr_index]['uuid'] . '">';
                }

                $html .= '</div>';
            }

            $html .= '</div>';

            // Add page break between sheets (except for the last sheet)
            if ($sheet < $sheets_needed - 1) {
                $html .= '<div style="page-break-after: always;"></div>';
            }
        }

        return $html;
    }

    /**
     * Generate a UUID v4.
     *
     * @return string UUID
     */
    public static function generate_uuid() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
