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
 * Upgrade steps for the Equipment checkout module.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade local_equipment plugin.
 *
 * @param int $oldversion The old version of the local_equipment plugin.
 * @return bool
 */
function xmldb_local_equipment_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024081401) {
        // Define table local_equipment_partnership.
        $table = new xmldb_table('local_equipment_partnership');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('pickupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('liaisonid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('streetaddress_mailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('city_mailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('state_mailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('country_mailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('zipcode_mailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('streetaddress_pickup', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('city_pickup', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('state_pickup', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('country_pickup', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('zipcode_pickup', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name_billing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('streetaddress_billing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('city_billing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('state_billing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('country_billing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('zipcode_billing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('pickupid', XMLDB_KEY_FOREIGN, ['pickupid'], 'local_equipment_pickup', ['id']);
        $table->add_key('liaisonid', XMLDB_KEY_FOREIGN, ['liaisonid'], 'user', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_partnership_course.
        $table = new xmldb_table('local_equipment_partnership_course');
        $table->add_field('partnershipid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['partnershipid', 'courseid']);
        $table->add_key('partnershipid', XMLDB_KEY_FOREIGN, ['partnershipid'], 'local_equipment_partnership', ['id']);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_pickup.
        $table = new xmldb_table('local_equipment_pickup');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('partnershipid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('flccoordinatorid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('partnershipcoordinatorname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('partnershipcoordinatorphone', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('pickupdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('endtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('partnershipid', XMLDB_KEY_FOREIGN, ['partnershipid'], 'local_equipment_partnership', ['id']);
        $table->add_key('flccoordinatorid', XMLDB_KEY_FOREIGN, ['flccoordinatorid'], 'user', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_agreements.
        $table = new xmldb_table('local_equipment_agreements');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('text', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('activestarttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('activeendtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('checkboxnames', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('requireelectronicsignature', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_parent.
        $table = new xmldb_table('local_equipment_parent');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('studentfullnames', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('streetaddress_mailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('city_mailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('state_mailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('country_mailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('zipcode_mailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('streetaddress_billing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('city_billing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('state_billing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('country_billing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('zipcode_billing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['userid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_student.
        $table = new xmldb_table('local_equipment_student');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('parentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('studentid', XMLDB_KEY_FOREIGN, ['studentid'], 'user', ['id']);
        $table->add_key('parentid', XMLDB_KEY_FOREIGN, ['parentid'], 'local_equipment_parent', ['userid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_student_course.
        $table = new xmldb_table('local_equipment_student_course');
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['studentid', 'courseid']);
        $table->add_key('studentid', XMLDB_KEY_FOREIGN, ['studentid'], 'local_equipment_student', ['studentid']);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_agreementsubmission.
        $table = new xmldb_table('local_equipment_agreementsubmission');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('agreementid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('submissiondate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('agreementid', XMLDB_KEY_FOREIGN, ['agreementid'], 'local_equipment_agreement', ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Equipment savepoint reached.
        upgrade_plugin_savepoint(true, 2024081401, 'local', 'equipment');
    }

    // Thursday, August 15, 2024 upgrade
    // Replace the partnershipcoordinatorname and partnershipcoordinatorphone fields with a partnershipcoordinatorid field, which is a user.
    if ($oldversion < 2024081500) {
        // Define field partnershipcoordinatorid to be added to local_equipment_pickup.
        // The param 'flccoordinatorid' is the field that the new field will be added after in the database.
        $table = new xmldb_table('local_equipment_pickup');
        $field = new xmldb_field('partnershipcoordinatorid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'flccoordinatorid');

        // Conditionally launch add_field partnershipcoordinatorid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define foreign key partnershipcoordinatorid to be added to local_equipment_pickup.
        $key = new xmldb_key('partnershipcoordinatorid', XMLDB_KEY_FOREIGN, ['partnershipcoordinatorid'], 'user', ['id']);

        // Launch add key partnershipcoordinatorid.
        $dbman->add_key($table, $key);

        // Remove old fields
        $field = new xmldb_field('partnershipcoordinatorname');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('partnershipcoordinatorphone');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Local_equipment savepoint reached
        upgrade_plugin_savepoint(true, 2024081500, 'local', 'equipment');
    }

    // Friday, August 16, 2024 upgrade 'cause I messed up... easy fix, though.
    // Replace the pickupstarttime and pickupendtime with pickupdate, starttime, and endtime, then add a status field as well.
    if ($oldversion < 2024081600) {
        // Add missing fields to local_equipment_pickup table.
        $table = new xmldb_table('local_equipment_pickup');

        // Add pickupdate field.
        $field = new xmldb_field('pickupdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'partnershipcoordinatorid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Add starttime field.
        $field = new xmldb_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'pickupdate');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Add endtime field.
        $field = new xmldb_field('endtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'starttime');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Add status field.
        $field = new xmldb_field('status', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'endtime');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Remove old fields
        $field = new xmldb_field('pickupstarttime');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('pickupendtime');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Local_equipment savepoint reached
        upgrade_plugin_savepoint(true, 2024081600, 'local', 'equipment');
    }

    return true;
}
