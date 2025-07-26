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
 * QR code print queue manager for equipment inventory system.
 *
 * @package     local_equipment
 * @copyright   2025 Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\inventory;

/**
 * QR code print queue manager class.
 *
 * Handles queueing and printing of QR codes for equipment items.
 *
 * @package     local_equipment
 * @copyright   2025 Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class print_queue_manager {

    /**
     * Add an item to the print queue.
     *
     * @param int $itemid The equipment item ID
     * @param string $uuid The item's UUID
     * @param int $userid The user ID who queued it
     * @param string $notes Optional notes
     * @return bool Success status
     * @throws \dml_exception
     */
    public function add_item_to_queue(int $itemid, string $uuid, int $userid, string $notes = ''): bool {
        global $DB;

        // Check if item is already in queue
        if ($this->is_item_in_queue($itemid)) {
            return false;
        }

        $record = new \stdClass();
        $record->itemid = $itemid;
        $record->uuid = $uuid;
        $record->queued_by = $userid;
        $record->queued_time = time();
        $record->notes = $notes;

        return (bool) $DB->insert_record('local_equipment_qr_print_queue', $record);
    }

    /**
     * Check if an item is already in the print queue.
     *
     * @param int $itemid The equipment item ID
     * @return bool True if item is in queue
     * @throws \dml_exception
     */
    public function is_item_in_queue(int $itemid): bool {
        global $DB;

        return $DB->record_exists('local_equipment_qr_print_queue', [
            'itemid' => $itemid,
            'printed_time' => null
        ]);
    }

    /**
     * Get all unprinted items in the queue.
     *
     * @return array Array of queue records
     * @throws \dml_exception
     */
    public function get_unprinted_queue(): array {
        global $DB;

        $sql = "SELECT q.*, i.uuid as current_uuid, p.name as product_name, p.manufacturer
                FROM {local_equipment_qr_print_queue} q
                JOIN {local_equipment_items} i ON q.itemid = i.id
                JOIN {local_equipment_products} p ON i.productid = p.id
                WHERE q.printed_time IS NULL
                ORDER BY q.queued_time ASC";

        return $DB->get_records_sql($sql);
    }

    /**
     * Mark items as printed.
     *
     * @param array $queueids Array of queue record IDs
     * @return bool Success status
     * @throws \dml_exception
     */
    public function mark_items_printed(array $queueids): bool {
        global $DB;

        if (empty($queueids)) {
            return true;
        }

        list($insql, $params) = $DB->get_in_or_equal($queueids);
        $params[] = time();

        return $DB->execute("UPDATE {local_equipment_qr_print_queue}
                            SET printed_time = ?
                            WHERE id $insql", $params);
    }

    /**
     * Remove items from the queue.
     *
     * @param array $queueids Array of queue record IDs
     * @return bool Success status
     * @throws \dml_exception
     */
    public function remove_items_from_queue(array $queueids): bool {
        global $DB;

        if (empty($queueids)) {
            return true;
        }

        list($insql, $params) = $DB->get_in_or_equal($queueids);

        return $DB->execute("DELETE FROM {local_equipment_qr_print_queue} WHERE id $insql", $params);
    }

    /**
     * Get queue count.
     *
     * @return int Number of unprinted items in queue
     * @throws \dml_exception
     */
    public function get_queue_count(): int {
        global $DB;

        return $DB->count_records('local_equipment_qr_print_queue', ['printed_time' => null]);
    }

    /**
     * Get printed queue count.
     *
     * @return int Number of printed items in queue
     * @throws \dml_exception
     */
    public function get_printed_queue_count(): int {
        global $DB;

        return $DB->count_records_select('local_equipment_qr_print_queue', 'printed_time IS NOT NULL');
    }

    /**
     * Clear all printed items from queue (cleanup).
     *
     * @return bool Success status
     * @throws \dml_exception
     */
    public function cleanup_printed_items(): bool {
        global $DB;

        return $DB->delete_records_select('local_equipment_qr_print_queue', 'printed_time IS NOT NULL');
    }

    /**
     * Clear printed items from queue and return count.
     *
     * @return int Number of items cleared
     * @throws \dml_exception
     */
    public function clear_printed_items(): int {
        global $DB;

        // Count printed items first
        $count = $DB->count_records_select('local_equipment_qr_print_queue', 'printed_time IS NOT NULL');

        // Delete printed items
        if ($count > 0) {
            $DB->delete_records_select('local_equipment_qr_print_queue', 'printed_time IS NOT NULL');
        }

        return $count;
    }

    /**
     * Get queue item by item ID.
     *
     * @param int $itemid The equipment item ID
     * @return \stdClass|false Queue record or false if not found
     * @throws \dml_exception
     */
    public function get_queue_item_by_itemid(int $itemid) {
        global $DB;

        return $DB->get_record('local_equipment_qr_print_queue', [
            'itemid' => $itemid,
            'printed_time' => null
        ]);
    }
}
