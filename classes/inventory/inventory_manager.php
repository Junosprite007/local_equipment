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
 * Core inventory management class for the Equipment plugin.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\inventory;

defined('MOODLE_INTERNAL') || die();

/**
 * Core inventory management operations.
 */
class inventory_manager {

    /**
     * Get available items for a specific product.
     *
     * @param int $productid Product ID
     * @param int|null $locationid Optional location filter
     * @return array Array of available equipment items
     */
    public function get_available_items($productid, $locationid = null) {
        global $DB;

        $params = [
            'productid' => $productid,
            'status' => 'available'
        ];

        $sql = "SELECT ei.*, ep.name as product_name, el.name as location_name
                FROM {local_equipment_items} ei
                JOIN {local_equipment_products} ep ON ei.productid = ep.id
                LEFT JOIN {local_equipment_locations} el ON ei.locationid = el.id
                WHERE ei.productid = :productid AND ei.status = :status";

        if ($locationid !== null) {
            $sql .= " AND ei.locationid = :locationid";
            $params['locationid'] = $locationid;
        }

        $sql .= " ORDER BY ei.condition_status DESC, ei.timemodified ASC";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Check out an equipment item to a user.
     *
     * @param string $itemuuid Item UUID
     * @param int $userid User ID receiving the item
     * @param int $processedby User ID of admin processing the transaction
     * @param string $notes Optional transaction notes
     * @return object Result object with success status and message
     */
    public function check_out_item($itemuuid, $userid, $processedby, $notes = '') {
        global $DB;

        try {
            $transaction = $DB->start_delegated_transaction();

            // Get the item
            $item = $DB->get_record('local_equipment_items', ['uuid' => $itemuuid]);
            if (!$item) {
                throw new \moodle_exception('itemnotfound', 'local_equipment');
            }

            // Check if item is available
            if ($item->status !== 'available') {
                throw new \moodle_exception('itemnotavailable', 'local_equipment');
            }

            // Update item status
            $item->status = 'checked_out';
            $item->current_userid = $userid;
            $item->timemodified = time();
            $DB->update_record('local_equipment_items', $item);

            // Create transaction record
            $transaction_record = new \stdClass();
            $transaction_record->itemid = $item->id;
            $transaction_record->transaction_type = 'checkout';
            $transaction_record->to_userid = $userid;
            $transaction_record->from_locationid = $item->locationid;
            $transaction_record->processed_by = $processedby;
            $transaction_record->notes = $notes;
            $transaction_record->condition_before = $item->condition_status;
            $transaction_record->timestamp = time();

            $DB->insert_record('local_equipment_transactions', $transaction_record);

            $transaction->allow_commit();

            return (object)[
                'success' => true,
                'message' => get_string('itemcheckedout', 'local_equipment'),
                'item' => $item
            ];
        } catch (\Exception $e) {
            $transaction->rollback($e);
            return (object)[
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check in an equipment item from a user.
     *
     * @param string $itemuuid Item UUID
     * @param int $locationid Location ID where item is being checked in
     * @param int $processedby User ID of admin processing the transaction
     * @param string $condition_status New condition status
     * @param string $notes Optional transaction notes
     * @return object Result object with success status and message
     */
    public function check_in_item($itemuuid, $locationid, $processedby, $condition_status = '', $notes = '') {
        global $DB;

        try {
            $transaction = $DB->start_delegated_transaction();

            // Get the item
            $item = $DB->get_record('local_equipment_items', ['uuid' => $itemuuid]);
            if (!$item) {
                throw new \moodle_exception('itemnotfound', 'local_equipment');
            }

            // Check if item is checked out
            if ($item->status !== 'checked_out') {
                throw new \moodle_exception('itemnotcheckedout', 'local_equipment');
            }

            $old_condition = $item->condition_status;
            $from_userid = $item->current_userid;

            // Update item status
            $item->status = 'available';
            $item->current_userid = null;
            $item->locationid = $locationid;
            $item->student_label = null; // Clear student label on check-in
            if (!empty($condition_status)) {
                $item->condition_status = $condition_status;
            }
            $item->timemodified = time();
            $DB->update_record('local_equipment_items', $item);

            // Create transaction record
            $transaction_record = new \stdClass();
            $transaction_record->itemid = $item->id;
            $transaction_record->transaction_type = 'checkin';
            $transaction_record->from_userid = $from_userid;
            $transaction_record->to_locationid = $locationid;
            $transaction_record->processed_by = $processedby;
            $transaction_record->notes = $notes;
            $transaction_record->condition_before = $old_condition;
            $transaction_record->condition_after = $item->condition_status;
            $transaction_record->timestamp = time();

            $DB->insert_record('local_equipment_transactions', $transaction_record);

            $transaction->allow_commit();

            return (object)[
                'success' => true,
                'message' => get_string('itemcheckedin', 'local_equipment'),
                'item' => $item
            ];
        } catch (\Exception $e) {
            $transaction->rollback($e);
            return (object)[
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get equipment item by UUID.
     *
     * @param string $uuid Item UUID
     * @return object|false Item record or false if not found
     */
    public function get_item_by_uuid($uuid) {
        global $DB;

        $sql = "SELECT ei.*, ep.name as product_name, ep.manufacturer, ep.category,
                       el.name as location_name, u.firstname, u.lastname
                FROM {local_equipment_items} ei
                JOIN {local_equipment_products} ep ON ei.productid = ep.id
                LEFT JOIN {local_equipment_locations} el ON ei.locationid = el.id
                LEFT JOIN {user} u ON ei.current_userid = u.id
                WHERE ei.uuid = :uuid";

        return $DB->get_record_sql($sql, ['uuid' => $uuid]);
    }

    /**
     * Get inventory summary statistics.
     *
     * @return object Summary statistics
     */
    public function get_inventory_summary() {
        global $DB;

        $summary = new \stdClass();

        // Total items
        $summary->total_items = $DB->count_records('local_equipment_items');

        // Items by status
        $summary->available = $DB->count_records('local_equipment_items', ['status' => 'available']);
        $summary->checked_out = $DB->count_records('local_equipment_items', ['status' => 'checked_out']);
        $summary->in_transit = $DB->count_records('local_equipment_items', ['status' => 'in_transit']);
        $summary->maintenance = $DB->count_records('local_equipment_items', ['status' => 'maintenance']);
        $summary->damaged = $DB->count_records('local_equipment_items', ['status' => 'damaged']);
        $summary->lost = $DB->count_records('local_equipment_items', ['status' => 'lost']);

        // Items by condition
        $summary->excellent = $DB->count_records('local_equipment_items', ['condition_status' => 'excellent']);
        $summary->good = $DB->count_records('local_equipment_items', ['condition_status' => 'good']);
        $summary->fair = $DB->count_records('local_equipment_items', ['condition_status' => 'fair']);
        $summary->poor = $DB->count_records('local_equipment_items', ['condition_status' => 'poor']);
        $summary->needs_repair = $DB->count_records('local_equipment_items', ['condition_status' => 'needs_repair']);

        // Recent transactions (last 7 days)
        $week_ago = time() - (7 * 24 * 60 * 60);
        $summary->recent_transactions = $DB->count_records_select(
            'local_equipment_transactions',
            'timestamp > :timestamp',
            ['timestamp' => $week_ago]
        );

        return $summary;
    }

    /**
     * Process bulk check-in of multiple items.
     *
     * @param array $items Array of item data with uuid, locationid, condition_status, notes
     * @param int $processedby User ID of admin processing the transaction
     * @return object Result object with success status and details
     */
    public function process_bulk_checkin($items, $processedby) {
        global $DB;

        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($items)
        ];

        foreach ($items as $item_data) {
            $result = $this->check_in_item(
                $item_data['uuid'],
                $item_data['locationid'],
                $processedby,
                $item_data['condition_status'] ?? '',
                $item_data['notes'] ?? ''
            );

            if ($result->success) {
                $results['success'][] = $item_data['uuid'];
            } else {
                $results['failed'][] = [
                    'uuid' => $item_data['uuid'],
                    'error' => $result->message
                ];
            }
        }

        return (object)[
            'success' => count($results['failed']) === 0,
            'message' => get_string('bulkcheckinresults', 'local_equipment', $results),
            'results' => $results
        ];
    }

    /**
     * Process bulk check-out of multiple items.
     *
     * @param array $items Array of item data with uuid, userid, notes
     * @param int $processedby User ID of admin processing the transaction
     * @return object Result object with success status and details
     */
    public function process_bulk_checkout($items, $processedby) {
        global $DB;

        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($items)
        ];

        foreach ($items as $item_data) {
            $result = $this->check_out_item(
                $item_data['uuid'],
                $item_data['userid'],
                $processedby,
                $item_data['notes'] ?? ''
            );

            if ($result->success) {
                $results['success'][] = $item_data['uuid'];
            } else {
                $results['failed'][] = [
                    'uuid' => $item_data['uuid'],
                    'error' => $result->message
                ];
            }
        }

        return (object)[
            'success' => count($results['failed']) === 0,
            'message' => get_string('bulkcheckoutresults', 'local_equipment', $results),
            'results' => $results
        ];
    }

    /**
     * Get items that are overdue for return.
     *
     * @param int $days_overdue Number of days to consider overdue
     * @return array Array of overdue items
     */
    public function get_overdue_items($days_overdue = 30) {
        global $DB;

        $cutoff_time = time() - ($days_overdue * 24 * 60 * 60);

        $sql = "SELECT ei.*, ep.name as product_name, u.firstname, u.lastname, u.email,
                       t.timestamp as checkout_time
                FROM {local_equipment_items} ei
                JOIN {local_equipment_products} ep ON ei.productid = ep.id
                JOIN {user} u ON ei.current_userid = u.id
                JOIN {local_equipment_transactions} t ON t.itemid = ei.id
                WHERE ei.status = 'checked_out'
                  AND t.transaction_type = 'checkout'
                  AND t.timestamp < :cutoff_time
                  AND t.timestamp = (
                      SELECT MAX(t2.timestamp)
                      FROM {local_equipment_transactions} t2
                      WHERE t2.itemid = ei.id AND t2.transaction_type = 'checkout'
                  )
                ORDER BY t.timestamp ASC";

        return $DB->get_records_sql($sql, ['cutoff_time' => $cutoff_time]);
    }
}
