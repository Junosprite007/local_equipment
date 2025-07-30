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
 * Unit tests for inventory removal functionality.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../config.php');

/**
 * Test cases for inventory removal bug scenarios.
 *
 * @group local_equipment
 */
class inventory_removal_test extends \advanced_testcase {

    /**
     * Set up test environment.
     */
    protected function setUp(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();
    }

    /**
     * Test the critical duplicate removal bug scenario.
     *
     * This test replicates the exact scenario described:
     * 1. Scan item into inventory (creates UUID)
     * 2. First removal via QR code → should succeed
     * 3. Second removal attempt with same UUID → should fail
     * 4. Verify only ONE removal transaction exists
     */
    public function test_prevent_duplicate_removal_transactions() {
        global $DB, $USER;

        // Create test product
        $product = new \stdClass();
        $product->name = 'Test LEGO Kit';
        $product->manufacturer = 'LEGO';
        $product->model = 'TEST-001';
        $product->upc = '123456789012';
        $product->description = 'Test kit for removal testing';
        $product->timecreated = time();
        $product->timemodified = time();
        $productid = $DB->insert_record('local_equipment_products', $product);

        // Create test location
        $location = new \stdClass();
        $location->name = 'Test Warehouse';
        $location->address = '123 Test St';
        $location->timecreated = time();
        $location->timemodified = time();
        $locationid = $DB->insert_record('local_equipment_locations', $location);

        // Create test equipment item with UUID (simulating barcode scan)
        $uuid = uniqid('TEST_', true);
        $item = new \stdClass();
        $item->productid = $productid;
        $item->uuid = $uuid;
        $item->serial_number = 'SN12345';
        $item->status = 'available';
        $item->condition_status = 'good';
        $item->locationid = $locationid;
        $item->current_userid = null;
        $item->timecreated = time();
        $item->timemodified = time();
        $itemid = $DB->insert_record('local_equipment_items', $item);

        // Verify initial state
        $initial_item = $DB->get_record('local_equipment_items', ['id' => $itemid]);
        $this->assertEquals('available', $initial_item->status);
        $this->assertEquals(0, $DB->count_records('local_equipment_transactions', ['itemid' => $itemid]));

        // FIRST REMOVAL ATTEMPT - Should succeed
        $this->simulate_removal_request($uuid, 'damaged');

        // Verify first removal succeeded
        $item_after_first = $DB->get_record('local_equipment_items', ['id' => $itemid]);
        $this->assertEquals('removed', $item_after_first->status);

        // Verify exactly one transaction was created
        $transactions_after_first = $DB->get_records('local_equipment_transactions', ['itemid' => $itemid]);
        $this->assertCount(1, $transactions_after_first);

        $first_transaction = reset($transactions_after_first);
        $this->assertEquals('removal', $first_transaction->transaction_type);
        $this->assertEquals('damaged', $first_transaction->notes);
        $this->assertEquals($USER->id, $first_transaction->processed_by);

        // SECOND REMOVAL ATTEMPT - Should fail and not create duplicate transaction
        $output = $this->simulate_removal_request($uuid, 'lost');

        // Verify item status unchanged (still removed)
        $item_after_second = $DB->get_record('local_equipment_items', ['id' => $itemid]);
        $this->assertEquals('removed', $item_after_second->status);

        // CRITICAL: Verify still only ONE transaction exists (no duplicate)
        $transactions_after_second = $DB->get_records('local_equipment_transactions', ['itemid' => $itemid]);
        $this->assertCount(1, $transactions_after_second, 'Duplicate removal transaction was created - BUG REPRODUCED!');

        // Verify the single transaction is still the original one
        $remaining_transaction = reset($transactions_after_second);
        $this->assertEquals($first_transaction->id, $remaining_transaction->id);
        $this->assertEquals('damaged', $remaining_transaction->notes); // Should still be 'damaged', not 'lost'

        // Verify error message was shown (this would be in output buffer in real scenario)
        $this->assertStringContainsString('equipmentalreadyremoved', $output);
    }

    /**
     * Test removal with invalid UUID.
     */
    public function test_removal_with_invalid_uuid() {
        global $DB;

        $fake_uuid = 'INVALID_UUID_12345';

        // Attempt removal with non-existent UUID
        try {
            $this->simulate_removal_request($fake_uuid, 'lost');
            $this->fail('Expected exception for invalid UUID');
        } catch (\Exception $e) {
            $this->assertStringContainsString('not exist', $e->getMessage());
        }
    }

    /**
     * Test removal of item that's already in different status.
     */
    public function test_removal_of_checked_out_item() {
        global $DB, $USER;

        // Create basic test data
        list($itemid, $uuid) = $this->create_test_item();

        // Change item status to 'checked_out'
        $DB->set_field('local_equipment_items', 'status', 'checked_out', ['id' => $itemid]);

        // Attempt removal - should succeed (checked out items can be removed)
        $this->simulate_removal_request($uuid, 'lost');

        // Verify removal succeeded
        $item = $DB->get_record('local_equipment_items', ['id' => $itemid]);
        $this->assertEquals('removed', $item->status);

        // Verify transaction was created
        $transactions = $DB->get_records('local_equipment_transactions', ['itemid' => $itemid]);
        $this->assertCount(1, $transactions);
    }

    /**
     * Helper method to simulate removal request processing.
     *
     * @param string $uuid Equipment UUID
     * @param string $reason Removal reason
     * @return string Output buffer content
     */
    private function simulate_removal_request($uuid, $reason) {
        global $DB, $USER;

        ob_start();

        // Use the actual inventory manager to test the real removal logic
        $inventory_manager = new \local_equipment\inventory\inventory_manager();
        $result = $inventory_manager->remove_item_from_inventory(
            $uuid,
            $reason,
            $USER->id,
            'Test removal via scanner'
        );

        if ($result->success) {
            echo 'itemremoved'; // Simulated success message
        } else {
            echo 'equipmentalreadyremoved'; // Simulated error message
        }

        return ob_get_clean();
    }

    /**
     * Helper method to create test item.
     *
     * @return array [itemid, uuid]
     */
    private function create_test_item() {
        global $DB;

        // Create test product
        $product = new \stdClass();
        $product->name = 'Test Equipment';
        $product->manufacturer = 'Test Co';
        $product->model = 'TEST-001';
        $product->upc = '123456789012';
        $product->description = 'Test equipment';
        $product->timecreated = time();
        $product->timemodified = time();
        $productid = $DB->insert_record('local_equipment_products', $product);

        // Create test location
        $location = new \stdClass();
        $location->name = 'Test Location';
        $location->address = '123 Test St';
        $location->timecreated = time();
        $location->timemodified = time();
        $locationid = $DB->insert_record('local_equipment_locations', $location);

        // Create test equipment item
        $uuid = uniqid('TEST_', true);
        $item = new \stdClass();
        $item->productid = $productid;
        $item->uuid = $uuid;
        $item->serial_number = 'SN12345';
        $item->status = 'available';
        $item->condition_status = 'good';
        $item->locationid = $locationid;
        $item->current_userid = null;
        $item->timecreated = time();
        $item->timemodified = time();
        $itemid = $DB->insert_record('local_equipment_items', $item);

        return [$itemid, $uuid];
    }
}
