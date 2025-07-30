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
    public function get_available_items(int $productid, ?int $locationid = null): array {
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
    public function check_out_item(string $itemuuid, int $userid, int $processedby, string $notes = ''): object {
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
    public function check_in_item(string $itemuuid, int $locationid, int $processedby, string $condition_status = '', string $notes = ''): object {
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
        } catch (\moodle_exception $e) {
            $transaction->rollback($e);
            return (object)[
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $transaction->rollback($e);
            return (object)[
                'success' => false,
                'message' => get_string('unexpectederror', 'local_equipment')
            ];
        }
    }

    /**
     * Get equipment details by UUID with full information.
     *
     * @param string $uuid Equipment item UUID
     * @return object Result object with equipment details
     */
    public function get_equipment_details(string $uuid): object {
        global $DB;

        try {
            $sql = "SELECT ei.*, ep.name as product_name, ep.manufacturer, ep.category, ep.description,
                           el.name as location_name,
                           u.id as user_id, u.firstname, u.lastname, u.email as user_email
                    FROM {local_equipment_items} ei
                    JOIN {local_equipment_products} ep ON ei.productid = ep.id
                    LEFT JOIN {local_equipment_locations} el ON ei.locationid = el.id
                    LEFT JOIN {user} u ON ei.current_userid = u.id
                    WHERE ei.uuid = ?";

            $item = $DB->get_record_sql($sql, [$uuid]);

            if (!$item) {
                return (object)[
                    'success' => false,
                    'message' => 'Equipment item not found'
                ];
            }

            // Get recent transactions for this item
            $transactions_sql = "SELECT t.*,
                                        u1.firstname as from_firstname, u1.lastname as from_lastname,
                                        u2.firstname as to_firstname, u2.lastname as to_lastname,
                                        l1.name as from_location, l2.name as to_location
                                 FROM {local_equipment_transactions} t
                                 LEFT JOIN {user} u1 ON t.from_userid = u1.id
                                 LEFT JOIN {user} u2 ON t.to_userid = u2.id
                                 LEFT JOIN {local_equipment_locations} l1 ON t.from_locationid = l1.id
                                 LEFT JOIN {local_equipment_locations} l2 ON t.to_locationid = l2.id
                                 WHERE t.itemid = ?
                                 ORDER BY t.timestamp DESC
                                 LIMIT 5";

            $transactions = $DB->get_records_sql($transactions_sql, [$item->id]);

            return (object)[
                'success' => true,
                'item' => $item,
                'transactions' => array_values($transactions)
            ];
        } catch (\Exception $e) {
            return (object)[
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Assign equipment to a user.
     *
     * @param string $uuid Equipment item UUID
     * @param int $userid User ID to assign to
     * @param int $processedby User ID processing the assignment
     * @param string $notes Optional notes
     * @return object Result object
     */
    public function assign_to_user(string $uuid, int $userid, int $processedby, string $notes = ''): object {
        global $DB;

        try {
            $transaction = $DB->start_delegated_transaction();

            // Get the equipment item
            $item = $DB->get_record('local_equipment_items', ['uuid' => $uuid]);
            if (!$item) {
                throw new \moodle_exception('Equipment item not found');
            }

            // Get user details for validation
            $user = $DB->get_record('user', ['id' => $userid], 'id, firstname, lastname, email');
            if (!$user) {
                throw new \moodle_exception('User not found');
            }

            $from_userid = $item->current_userid;
            $from_locationid = $item->locationid;

            // Update item status
            $item->status = 'checked_out';
            $item->current_userid = $userid;
            $item->locationid = null; // Clear location when assigned to user
            $item->timemodified = time();
            $DB->update_record('local_equipment_items', $item);

            // Create transaction record
            $transaction_record = new \stdClass();
            $transaction_record->itemid = $item->id;
            $transaction_record->transaction_type = 'checkout';
            $transaction_record->from_userid = $from_userid;
            $transaction_record->to_userid = $userid;
            $transaction_record->from_locationid = $from_locationid;
            $transaction_record->processed_by = $processedby;
            $transaction_record->notes = $notes;
            $transaction_record->timestamp = time();
            $DB->insert_record('local_equipment_transactions', $transaction_record);

            $transaction->allow_commit();

            return (object)[
                'success' => true,
                'message' => "Equipment assigned to {$user->firstname} {$user->lastname}"
            ];
        } catch (\moodle_exception $e) {
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
            return (object)[
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
            return (object)[
                'success' => false,
                'message' => get_string('unexpectederror', 'local_equipment')
            ];
        }
    }

    /**
     * Assign equipment to a location.
     *
     * @param string $uuid Equipment item UUID
     * @param int $locationid Location ID to assign to
     * @param int $processedby User ID processing the assignment
     * @param string $notes Optional notes
     * @return object Result object
     */
    public function assign_to_location(string $uuid, int $locationid, int $processedby, string $notes = ''): object {
        global $DB;

        try {
            $transaction = $DB->start_delegated_transaction();

            // Get the equipment item
            $item = $DB->get_record('local_equipment_items', ['uuid' => $uuid]);
            if (!$item) {
                throw new \moodle_exception('Equipment item not found');
            }

            // Get location details for validation
            $location = $DB->get_record('local_equipment_locations', ['id' => $locationid], 'id, name');
            if (!$location) {
                throw new \moodle_exception('Location not found');
            }

            $from_userid = $item->current_userid;
            $from_locationid = $item->locationid;

            // Update item status
            $item->status = 'available';
            $item->current_userid = null; // Clear user when assigned to location
            $item->locationid = $locationid;
            $item->timemodified = time();
            $DB->update_record('local_equipment_items', $item);

            // Determine transaction type
            $transaction_type = $from_userid ? 'checkin' : 'transfer';

            // Create transaction record
            $transaction_record = new \stdClass();
            $transaction_record->itemid = $item->id;
            $transaction_record->transaction_type = $transaction_type;
            $transaction_record->from_userid = $from_userid;
            $transaction_record->from_locationid = $from_locationid;
            $transaction_record->to_locationid = $locationid;
            $transaction_record->processed_by = $processedby;
            $transaction_record->notes = $notes;
            $transaction_record->timestamp = time();
            $DB->insert_record('local_equipment_transactions', $transaction_record);

            $transaction->allow_commit();

            return (object)[
                'success' => true,
                'message' => "Equipment transferred to {$location->name}"
            ];
        } catch (\Exception $e) {
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
            return (object)[
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Unassign equipment (make it available without specific location).
     *
     * @param string $uuid Equipment item UUID
     * @param int $processedby User ID processing the unassignment
     * @param string $notes Optional notes
     * @return object Result object
     */
    public function unassign_equipment(string $uuid, int $processedby, string $notes = ''): object {
        global $DB;

        try {
            $transaction = $DB->start_delegated_transaction();

            // Get the equipment item
            $item = $DB->get_record('local_equipment_items', ['uuid' => $uuid]);
            if (!$item) {
                throw new \moodle_exception('Equipment item not found');
            }

            $from_userid = $item->current_userid;
            $from_locationid = $item->locationid;

            // Update item status
            $item->status = 'available';
            $item->current_userid = null;
            $item->locationid = null; // No specific location
            $item->timemodified = time();
            $DB->update_record('local_equipment_items', $item);

            // Create transaction record
            $transaction_record = new \stdClass();
            $transaction_record->itemid = $item->id;
            $transaction_record->transaction_type = 'checkin';
            $transaction_record->from_userid = $from_userid;
            $transaction_record->from_locationid = $from_locationid;
            $transaction_record->processed_by = $processedby;
            $transaction_record->notes = $notes;
            $transaction_record->timestamp = time();
            $DB->insert_record('local_equipment_transactions', $transaction_record);

            $transaction->allow_commit();

            return (object)[
                'success' => true,
                'message' => 'Equipment unassigned and made available'
            ];
        } catch (\Exception $e) {
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
            return (object)[
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Search for users by name or email.
     *
     * @param string $query Search query
     * @param int $limit Maximum number of results
     * @return array Array of user objects
     */
    public function search_users(string $query, int $limit = 10): array {
        global $DB;

        if (strlen(trim($query)) < 2) {
            return [];
        }

        $query = '%' . $DB->sql_like_escape(trim($query)) . '%';

        $sql = "SELECT id, firstname, lastname, email
                FROM {user}
                WHERE deleted = 0 AND suspended = 0
                AND (
                    " . $DB->sql_like('firstname', ':query1', false) . "
                    OR " . $DB->sql_like('lastname', ':query2', false) . "
                    OR " . $DB->sql_like('email', ':query3', false) . "
                    OR " . $DB->sql_like($DB->sql_concat('firstname', "' '", 'lastname'), ':query4', false) . "
                )
                ORDER BY lastname, firstname
                LIMIT :limit";

        $params = [
            'query1' => $query,
            'query2' => $query,
            'query3' => $query,
            'query4' => $query,
            'limit' => $limit
        ];

        return array_values($DB->get_records_sql($sql, $params));
    }

    /**
     * Get all active locations.
     *
     * @return array Array of location objects
     */
    public function get_active_locations(): array {
        global $DB;

        return array_values($DB->get_records(
            'local_equipment_locations',
            ['active' => 1],
            'name ASC',
            'id, name, description, zone'
        ));
    }

    /**
     * Update equipment notes.
     *
     * @param string $uuid Equipment item UUID
     * @param string $notes Notes to add
     * @param int $processedby User ID adding the notes
     * @return object Result object
     */
    public function update_equipment_notes(string $uuid, string $notes, int $processedby): object {
        global $DB;

        try {
            // Get the equipment item
            $item = $DB->get_record('local_equipment_items', ['uuid' => $uuid]);
            if (!$item) {
                throw new \moodle_exception('Equipment item not found');
            }

            // Create transaction record for notes
            $transaction_record = new \stdClass();
            $transaction_record->itemid = $item->id;
            $transaction_record->transaction_type = 'condition_update';
            $transaction_record->processed_by = $processedby;
            $transaction_record->notes = $notes;
            $transaction_record->timestamp = time();
            $DB->insert_record('local_equipment_transactions', $transaction_record);

            return (object)[
                'success' => true,
                'message' => 'Notes added successfully'
            ];
        } catch (\moodle_exception $e) {
            return (object)[
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return (object)[
                'success' => false,
                'message' => get_string('unexpectederror', 'local_equipment')
            ];
        }
    }

    /**
     * Get equipment items by status.
     *
     * @param string $status Equipment status
     * @param int $limit Maximum number of results
     * @return array Array of equipment items
     */
    public function get_items_by_status(string $status, int $limit = 50): array {
        global $DB;

        $sql = "SELECT ei.*, ep.name as product_name, ep.manufacturer,
                       el.name as location_name,
                       u.firstname, u.lastname
                FROM {local_equipment_items} ei
                JOIN {local_equipment_products} ep ON ei.productid = ep.id
                LEFT JOIN {local_equipment_locations} el ON ei.locationid = el.id
                LEFT JOIN {user} u ON ei.current_userid = u.id
                WHERE ei.status = ?
                ORDER BY ei.timemodified DESC
                LIMIT ?";

        return array_values($DB->get_records_sql($sql, [$status, $limit]));
    }

    /**
     * Get equipment items by location.
     *
     * @param int $locationid Location ID
     * @return array Array of equipment items
     */
    public function get_items_by_location(int $locationid): array {
        global $DB;

        $sql = "SELECT ei.*, ep.name as product_name, ep.manufacturer, ep.category
                FROM {local_equipment_items} ei
                JOIN {local_equipment_products} ep ON ei.productid = ep.id
                WHERE ei.locationid = ? AND ei.status = 'available'
                ORDER BY ep.name, ei.timecreated";

        return array_values($DB->get_records_sql($sql, [$locationid]));
    }

    /**
     * Get equipment items assigned to a user.
     *
     * @param int $userid User ID
     * @return array Array of equipment items
     */
    public function get_items_by_user(int $userid): array {
        global $DB;

        $sql = "SELECT ei.*, ep.name as product_name, ep.manufacturer, ep.category
                FROM {local_equipment_items} ei
                JOIN {local_equipment_products} ep ON ei.productid = ep.id
                WHERE ei.current_userid = ? AND ei.status = 'checked_out'
                ORDER BY ep.name, ei.timemodified DESC";

        return array_values($DB->get_records_sql($sql, [$userid]));
    }

    /**
     * Get inventory summary statistics.
     *
     * @return object Summary statistics
     */
    public function get_inventory_summary(): object {
        global $DB;

        $summary = new \stdClass();

        // Total active items (excludes removed items for accurate inventory count)
        $summary->total_items = $DB->count_records_select('local_equipment_items', 'status != ?', ['removed']);

        // Items by status
        $summary->available = $DB->count_records('local_equipment_items', ['status' => 'available']);
        $summary->checked_out = $DB->count_records('local_equipment_items', ['status' => 'checked_out']);
        $summary->in_transit = $DB->count_records('local_equipment_items', ['status' => 'in_transit']);
        $summary->maintenance = $DB->count_records('local_equipment_items', ['status' => 'maintenance']);
        $summary->damaged = $DB->count_records('local_equipment_items', ['status' => 'damaged']);
        $summary->lost = $DB->count_records('local_equipment_items', ['status' => 'lost']);

        // Add removed items as separate count for audit purposes
        $summary->removed = $DB->count_records('local_equipment_items', ['status' => 'removed']);

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
    public function process_bulk_checkin(array $items, int $processedby): object {
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
    public function process_bulk_checkout(array $items, int $processedby): object {
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
    public function get_overdue_items(int $days_overdue = 30): array {
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

    /**
     * Remove an equipment item from inventory permanently.
     *
     * @param string $uuid Equipment item UUID
     * @param string $reason Reason for removal
     * @param int $processedby User ID processing the removal
     * @param string $notes Optional removal notes
     * @return object Result object with success status and message
     */
    public function remove_item_from_inventory(string $uuid, string $reason, int $processedby, string $notes = ''): object {
        global $DB;

        // 1. Validate UUID format first
        if (!$this->is_valid_uuid_format($uuid)) {
            return (object)[
                'success' => false,
                'message' => get_string('invalidqrformat', 'local_equipment'),
                'error_code' => 'invalid_uuid_format'
            ];
        }

        // 2. Check if item exists in database
        $item = $DB->get_record('local_equipment_items', ['uuid' => $uuid]);
        if (!$item) {
            return (object)[
                'success' => false,
                'message' => get_string('qrnotfound', 'local_equipment'),
                'error_code' => 'item_not_found'
            ];
        }

        // 3. Check if already removed
        if ($item->status === 'removed') {
            return (object)[
                'success' => false,
                'message' => get_string('itempreviouslyremoved', 'local_equipment'),
                'error_code' => 'already_removed'
            ];
        }

        // 4. Don't allow removal of items that are checked out unless forced
        if ($item->status === 'checked_out') {
            return (object)[
                'success' => false,
                'message' => get_string('cannotremovecheckedout', 'local_equipment'),
                'error_code' => 'item_checked_out'
            ];
        }

        try {
            $transaction = $DB->start_delegated_transaction();

            $old_status = $item->status;
            $from_userid = $item->current_userid;
            $from_locationid = $item->locationid;

            // Update item status to removed
            $item->status = 'removed';
            $item->current_userid = null;
            $item->locationid = null;
            $item->timemodified = time();
            $DB->update_record('local_equipment_items', $item);

            // Create transaction record for removal
            $transaction_record = new \stdClass();
            $transaction_record->itemid = $item->id;
            $transaction_record->transaction_type = 'removal';
            $transaction_record->from_userid = $from_userid;
            $transaction_record->from_locationid = $from_locationid;
            $transaction_record->processed_by = $processedby;
            $transaction_record->notes = "Reason: {$reason}. {$notes}";
            $transaction_record->condition_before = $item->condition_status;
            $transaction_record->timestamp = time();
            $DB->insert_record('local_equipment_transactions', $transaction_record);

            // Automatically remove from print queue
            $print_queue_manager = new \local_equipment\inventory\print_queue_manager();
            $print_queue_manager->remove_item_from_queue_by_itemid($item->id);

            $transaction->allow_commit();

            return (object)[
                'success' => true,
                'message' => 'Equipment item removed from inventory successfully',
                'item' => $item,
                'previous_status' => $old_status
            ];
        } catch (\moodle_exception $e) {
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
            return (object)[
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
            return (object)[
                'success' => false,
                'message' => 'Unexpected error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Force remove an equipment item from inventory (even if checked out).
     *
     * @param string $uuid Equipment item UUID
     * @param string $reason Reason for removal
     * @param int $processedby User ID processing the removal
     * @param string $notes Optional removal notes
     * @return object Result object with success status and message
     */
    public function force_remove_item_from_inventory(string $uuid, string $reason, int $processedby, string $notes = ''): object {
        global $DB;

        try {
            $transaction = $DB->start_delegated_transaction();

            // Get the equipment item
            $item = $DB->get_record('local_equipment_items', ['uuid' => $uuid]);
            if (!$item) {
                throw new \moodle_exception('Equipment item not found');
            }

            $old_status = $item->status;
            $from_userid = $item->current_userid;
            $from_locationid = $item->locationid;

            // Update item status to removed (forced)
            $item->status = 'removed';
            $item->current_userid = null;
            $item->locationid = null;
            $item->timemodified = time();
            $DB->update_record('local_equipment_items', $item);

            // Create transaction record for forced removal
            $transaction_record = new \stdClass();
            $transaction_record->itemid = $item->id;
            $transaction_record->transaction_type = 'removal';
            $transaction_record->from_userid = $from_userid;
            $transaction_record->from_locationid = $from_locationid;
            $transaction_record->processed_by = $processedby;
            $transaction_record->notes = "FORCED REMOVAL - Reason: {$reason}. {$notes}";
            $transaction_record->condition_before = $item->condition_status;
            $transaction_record->timestamp = time();
            $DB->insert_record('local_equipment_transactions', $transaction_record);

            // Automatically remove from print queue
            $print_queue_manager = new \local_equipment\inventory\print_queue_manager();
            $print_queue_manager->remove_item_from_queue_by_itemid($item->id);

            $transaction->allow_commit();

            return (object)[
                'success' => true,
                'message' => 'Equipment item force removed from inventory successfully',
                'item' => $item,
                'previous_status' => $old_status,
                'forced' => true
            ];
        } catch (\moodle_exception $e) {
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
            return (object)[
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
            return (object)[
                'success' => false,
                'message' => 'Unexpected error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate UUID format.
     *
     * @param string $uuid UUID string to validate
     * @return bool True if valid UUID format
     */
    private function is_valid_uuid_format(string $uuid): bool {
        // Standard UUID format: 8-4-4-4-12 hexadecimal digits
        $pattern = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';
        return preg_match($pattern, $uuid) === 1;
    }

    /**
     * Generate a new UUID.
     *
     * @return string UUID
     */
    private function generate_uuid(): string {
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
