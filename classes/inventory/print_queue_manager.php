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
     * Get all unprinted items in the queue that correspond to available inventory.
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
                AND i.status = 'available'
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
     * Get queue count with automatic cleanup of orphaned items.
     *
     * @return int Number of unprinted items in queue
     * @throws \dml_exception
     */
    public function get_queue_count(): int {
        // First cleanup orphaned items, then return accurate count
        $this->cleanup_orphaned_queue_items();

        global $DB;

        // Only count items that correspond to available inventory
        $sql = "SELECT COUNT(q.id)
                FROM {local_equipment_qr_print_queue} q
                JOIN {local_equipment_items} i ON q.itemid = i.id
                WHERE q.printed_time IS NULL
                AND i.status = 'available'";

        return (int)$DB->count_records_sql($sql);
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
     * Clear all printed items from the queue.
     *
     * @return int Number of items cleared
     */
    public function clear_printed_items(): int {
        global $DB;

        $count = $DB->count_records('local_equipment_print_queue', ['printed' => 1]);

        if ($count > 0) {
            $DB->delete_records('local_equipment_print_queue', ['printed' => 1]);
        }

        return $count;
    }

    /**
     * Clean up orphaned queue items where equipment no longer exists or is not available.
     *
     * @return array Array with cleanup results ['count' => int, 'uuids' => array]
     */
    public function cleanup_orphaned_queue_items(): array {
        global $DB;

        // Find queue items where the equipment item is missing or has status != 'available'
        $sql = "SELECT pq.id, pq.itemid, ei.uuid
                FROM {local_equipment_qr_print_queue} pq
                LEFT JOIN {local_equipment_items} ei ON pq.itemid = ei.id
                WHERE ei.id IS NULL OR ei.status != 'available'";

        $orphaned_items = $DB->get_records_sql($sql);

        if (empty($orphaned_items)) {
            return ['count' => 0, 'uuids' => []];
        }

        // Collect UUIDs and queue IDs for removal
        $queue_ids = [];
        $uuids = [];

        foreach ($orphaned_items as $item) {
            $queue_ids[] = $item->id;
            if (!empty($item->uuid)) {
                $uuids[] = substr($item->uuid, 0, 8) . '...'; // Show short UUID for user
            } else {
                $uuids[] = 'Unknown UUID (ID: ' . $item->itemid . ')';
            }
        }

        // Remove orphaned items from print queue
        if (!empty($queue_ids)) {
            list($in_sql, $params) = $DB->get_in_or_equal($queue_ids);
            $DB->delete_records_select('local_equipment_qr_print_queue', "id $in_sql", $params);
        }

        return [
            'count' => count($orphaned_items),
            'uuids' => $uuids
        ];
    }

    /**
     * Get queue count with automatic cleanup of orphaned items.
     *
     * @return array Array with count and cleanup information
     */
    public function get_queue_count_with_cleanup(): array {
        // First perform cleanup
        $cleanup_result = $this->cleanup_orphaned_queue_items();

        // Then get the clean count
        $clean_count = $this->get_queue_count();

        return [
            'count' => $clean_count,
            'cleanup_performed' => $cleanup_result['count'] > 0,
            'cleanup_count' => $cleanup_result['count'],
            'cleanup_uuids' => $cleanup_result['uuids']
        ];
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

    /**
     * Remove item from queue by equipment item ID.
     *
     * This method is used when an equipment item is removed from inventory
     * to automatically clean up the print queue.
     *
     * @param int $itemid The equipment item ID
     * @return bool True if item was removed from queue, false if not found
     * @throws \dml_exception
     */
    public function remove_item_from_queue_by_itemid(int $itemid): bool {
        global $DB;

        // Remove both printed and unprinted queue entries for this item
        return $DB->delete_records('local_equipment_qr_print_queue', ['itemid' => $itemid]);
    }

    /**
     * Check if item exists in queue (printed or unprinted) by item ID.
     *
     * @param int $itemid The equipment item ID
     * @return bool True if item exists in queue
     * @throws \dml_exception
     */
    public function item_exists_in_queue(int $itemid): bool {
        global $DB;

        return $DB->record_exists('local_equipment_qr_print_queue', ['itemid' => $itemid]);
    }
}
