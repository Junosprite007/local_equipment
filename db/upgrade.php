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

    // Saturday, August 16, 2024 upgrade to modify the local_equipment_agreement table.
    if ($oldversion < 2024081601) {

        // Drop old tables because they should not have any data in it yet. No pages even had access to any of them.
        $oldtable = new xmldb_table('local_equipment_agreements');
        if ($dbman->table_exists($oldtable)) {
            $dbman->drop_table($oldtable);
        }
        $oldtable = new xmldb_table('local_equipment_agreementsubmission');
        if ($dbman->table_exists($oldtable)) {
            $dbman->drop_table($oldtable);
        }
        $oldtable = new xmldb_table('local_equipment_parent');
        if ($dbman->table_exists($oldtable)) {
            $dbman->drop_table($oldtable);
        }
        $oldtable = new xmldb_table('local_equipment_student');
        if ($dbman->table_exists($oldtable)) {
            $dbman->drop_table($oldtable);
        }
        $oldtable = new xmldb_table('local_equipment_student_course');
        if ($dbman->table_exists($oldtable)) {
            $dbman->drop_table($oldtable);
        }

        // Define table local_equipment_agreement.
        $table = new xmldb_table('local_equipment_agreement');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('contenttext', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('contentformat', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('agreementtype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('activestarttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('activeendtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('requireelectronicsignature', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('version', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('previousversionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('previousversionid', XMLDB_INDEX_NOTUNIQUE, ['previousversionid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2024081601, 'local', 'equipment');
    }


    if ($oldversion < 2024081800) {
        // Define table local_equipment_vccsubmission.
        $table = new xmldb_table('local_equipment_vccsubmission');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('partnershipid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('pickupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('studentids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('agreementids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('confirmationid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('confirmationexpired', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('pickupmethod', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('pickuppersonname', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('pickuppersonphone', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('usernotes', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('adminnotes', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('partnershipid', XMLDB_KEY_FOREIGN, ['partnershipid'], 'local_equipment_partnership', ['id']);
        $table->add_key('pickupid', XMLDB_KEY_FOREIGN, ['pickupid'], 'local_equipment_pickup', ['id']);

        $table->add_index('confirmationid', XMLDB_INDEX_UNIQUE, ['confirmationid']);
        $table->add_index('timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_vccsubmission_agreement.
        $table = new xmldb_table('local_equipment_vccsubmission_agreement');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('vccsubmissionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('agreementid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('optinout', XMLDB_TYPE_INTEGER, '1', null, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('vccsubmissionid', XMLDB_KEY_FOREIGN, ['vccsubmissionid'], 'local_equipment_vccsubmission', ['id']);
        $table->add_key('agreementid', XMLDB_KEY_FOREIGN, ['agreementid'], 'local_equipment_agreement', ['id']);

        $table->add_index('optinout', XMLDB_INDEX_NOTUNIQUE, ['optinout']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_vccsubmission_student.
        $table = new xmldb_table('local_equipment_vccsubmission_student');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('vccsubmissionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('firstname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lastname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('dateofbirth', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('vccsubmissionid', XMLDB_KEY_FOREIGN, ['vccsubmissionid'], 'local_equipment_vccsubmission', ['id']);

        $table->add_index('email', XMLDB_INDEX_NOTUNIQUE, ['email']);
        $table->add_index('dateofbirth', XMLDB_INDEX_NOTUNIQUE, ['dateofbirth']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_vccsubmission_student_course.
        $table = new xmldb_table('local_equipment_vccsubmission_student_course');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('studentid', XMLDB_KEY_FOREIGN, ['studentid'], 'local_equipment_vccsubmission_student', ['id']);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Equipment savepoint reached.
        upgrade_plugin_savepoint(true, 2024081800, 'local', 'equipment');
    }

    if ($oldversion < 2024082505) {
        // Rename fields in the local_equipment_partnership table.
        $table = new xmldb_table('local_equipment_partnership');

        // Rename fields in the local_equipment_partnership table.
        $oldnames = [
            'streetaddress_physical',
            'city_physical',
            'state_physical',
            'country_physical',
            'zipcode_physical',
            'sameasphysical_mailing',
            'attention_mailing',
            'streetaddress_mailing',
            'city_mailing',
            'state_mailing',
            'country_mailing',
            'zipcode_mailing',
            'instructions_pickup',
            'sameasphysical_pickup',
            'streetaddress_pickup',
            'city_pickup',
            'state_pickup',
            'country_pickup',
            'zipcode_pickup',
            'attention_billing',
            'sameasphysical_billing',
            'streetaddress_billing',
            'city_billing',
            'state_billing',
            'country_billing',
            'zipcode_billing',
        ];

        foreach ($oldnames as $oldname) {
            if ($oldname === 'instructions_pickup') {
                $newname = preg_replace('/([a-zA-Z0-9]+)_([a-zA-Z0-9]+)/', '$2_extra$1', $oldname);
                $oldfield = new xmldb_field($oldname, XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
            } else if ($oldname === 'attention_mailing' || $oldname === 'attention_billing') {
                $newname = preg_replace('/([a-zA-Z0-9]+)_([a-zA-Z0-9]+)/', '$2_extrainput', $oldname);
                $oldfield = new xmldb_field($oldname, XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            } else {
                $newname = preg_replace('/([a-zA-Z0-9]+)_([a-zA-Z0-9]+)/', '$2_$1', $oldname);
                $oldfield = new xmldb_field($oldname, XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            }
            if ($dbman->field_exists($table, $oldfield) && !$dbman->field_exists($table, $newname)) {
                $dbman->rename_field($table, $oldfield, $newname);
            }
        }

        // Add new fields to the local_equipment_partnership table.
        $newfield = new xmldb_field('physical_extrainput', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'active');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('physical_sameasmailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'physical_extrainput');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('physical_apartment', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'physical_streetaddress');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('physical_extrainstructions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'physical_zipcode');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('mailing_apartment', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'mailing_streetaddress');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('mailing_extrainstructions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'mailing_zipcode');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('pickup_extrainput', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'mailing_extrainstructions');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('pickup_sameasmailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'pickup_extrainput');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('pickup_apartment', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'pickup_streetaddress');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('billing_sameasmailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'billing_extrainput');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('billing_apartment', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'billing_streetaddress');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('billing_extrainstructions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'billing_zipcode');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Add pickup fields to the local_equipment_pickup table.
        $table = new xmldb_table('local_equipment_pickup');

        $newfield = new xmldb_field('address_extrainput', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'status');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('address_sameasmailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_extrainput');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('address_sameasphysical', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_sameasmailing');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('address_streetaddress', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_sameasphysical');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('address_apartment', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_streetaddress');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('address_city', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_apartment');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('address_state', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_city');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('address_country', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_state');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('address_zipcode', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_country');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }
        $newfield = new xmldb_field('address_extrainstructions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'address_zipcode');
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }


        // Now we can add the new tables:
        // local_equipment_user,
        // local_equipment_vccsubmission,
        // local_equipment_vccsubmission_agreement,
        // local_equipment_vccsubmission_student,
        // local_equipment_vccsubmission_student_course

        // Define table local_equipment_user.
        $table = new xmldb_table('local_equipment_user');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('partnershipid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('studentids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('vccsubmissionids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('phoneverificationids', XMLDB_TYPE_TEXT, null, null, null, null, null);

        $table->add_field('mailing_extrainput', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('mailing_streetaddress', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('mailing_apartment', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('mailing_city', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('mailing_state', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('mailing_country', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('mailing_zipcode', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('mailing_extrainstructions', XMLDB_TYPE_TEXT, null, null, null, null, null);

        $table->add_field('billing_extrainput', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('billing_sameasmailing', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('billing_streetaddress', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('billing_apartment', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('billing_city', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('billing_state', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('billing_country', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('billing_zipcode', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('billing_extrainstructions', XMLDB_TYPE_TEXT, null, null, null, null, null);

        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('partnershipid', XMLDB_KEY_FOREIGN, ['partnershipid'], 'local_equipment_partnership', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_vccsubmission.
        $table = new xmldb_table('local_equipment_vccsubmission');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('partnershipid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('pickupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('studentids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('agreementids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('confirmationid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('confirmationexpired', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
        $table->add_field('pickupmethod', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('pickuppersonname', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('pickuppersonphone', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('pickuppersondetails', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('usernotes', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('adminnotes', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('partnershipid', XMLDB_KEY_FOREIGN, ['partnershipid'], 'local_equipment_partnership', ['id']);
        $table->add_key('pickupid', XMLDB_KEY_FOREIGN, ['pickupid'], 'local_equipment_pickup', ['id']);

        $table->add_index('confirmationid', null, ['confirmationid']);
        $table->add_index('timecreated', null, ['timecreated']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }


        // Define table local_equipment_vccsubmission_agreement.
        $table = new xmldb_table('local_equipment_vccsubmission_agreement');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('vccsubmissionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('agreementid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('optinout', XMLDB_TYPE_INTEGER, '1', null, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('vccsubmissionid', XMLDB_KEY_FOREIGN, ['vccsubmissionid'], 'local_equipment_vccsubmission', ['id']);
        $table->add_key('agreementid', XMLDB_KEY_FOREIGN, ['agreementid'], 'local_equipment_agreement', ['id']);

        $table->add_index('optinout', null, ['optinout']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }


        // Define table local_equipment_vccsubmission_student.
        $table = new xmldb_table('local_equipment_vccsubmission_student');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('vccsubmissionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('firstname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lastname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('dateofbirth', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('vccsubmissionid', XMLDB_KEY_FOREIGN, ['vccsubmissionid'], 'local_equipment_vccsubmission', ['id']);

        $table->add_index('email', null, ['email']);
        $table->add_index('dateofbirth', null, ['dateofbirth']);
        $table->add_index('timecreated', null, ['timecreated']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }


        // Define table local_equipment_vccsubmission_student_course.
        $table = new xmldb_table('local_equipment_vccsubmission_student_course');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('studentid', XMLDB_KEY_FOREIGN, ['studentid'], 'local_equipment_vccsubmission_student', ['id']);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Equipment savepoint reached.
        upgrade_plugin_savepoint(true, 2024082505, 'local', 'equipment');
    }

    if ($oldversion < 2024090500) {
        $table = new xmldb_table('local_equipment_vccsubmission');
        $fields = [];

        $fields[] = new xmldb_field('email', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'confirmationexpired');
        $fields[] = new xmldb_field('email_confirmed', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'email');
        $fields[] = new xmldb_field('firstname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'email_confirmed');
        $fields[] = new xmldb_field('lastname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'firstname');
        $fields[] = new xmldb_field('phone', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'lastname');
        $fields[] = new xmldb_field('phone_confirmed', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'phone');
        $fields[] = new xmldb_field('partnership_name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'phone_confirmed');
        $fields[] = new xmldb_field('mailing_extrainput', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'partnership_name');
        $fields[] = new xmldb_field('mailing_streetaddress', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'mailing_extrainput');
        $fields[] = new xmldb_field('mailing_apartment', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'mailing_streetaddress');
        $fields[] = new xmldb_field('mailing_city', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'mailing_apartment');
        $fields[] = new xmldb_field('mailing_state', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'mailing_city');
        $fields[] = new xmldb_field('mailing_country', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'mailing_state');
        $fields[] = new xmldb_field('mailing_zipcode', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'mailing_country');
        $fields[] = new xmldb_field('mailing_extrainstructions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'mailing_zipcode');
        $fields[] = new xmldb_field('billing_extrainput', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'mailing_extrainstructions');
        $fields[] = new xmldb_field('billing_sameasmailing', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'billing_extrainput');
        $fields[] = new xmldb_field('billing_streetaddress', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'billing_sameasmailing');
        $fields[] = new xmldb_field('billing_apartment', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'billing_streetaddress');
        $fields[] = new xmldb_field('billing_city', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'billing_apartment');
        $fields[] = new xmldb_field('billing_state', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'billing_city');
        $fields[] = new xmldb_field('billing_country', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'billing_state');
        $fields[] = new xmldb_field('billing_zipcode', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'billing_country');
        $fields[] = new xmldb_field('billing_extrainstructions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'billing_zipcode');
        $fields[] = new xmldb_field('pickup_locationtime', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'billing_extrainstructions');
        $fields[] = new xmldb_field('electronicsignature', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'pickup_locationtime');

        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Equipment savepoint reached.
        upgrade_plugin_savepoint(true, 2024090500, 'local', 'equipment');
    }

    if ($oldversion < 2024092601) {
        $table = new xmldb_table('local_equipment_user');
        $fields = [];

        $fields[] = new xmldb_field('phone', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'phoneverificationids');
        $fields[] = new xmldb_field('phone_verified', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'phone');

        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Equipment savepoint reached.
        upgrade_plugin_savepoint(true, 2024092601, 'local', 'equipment');
    }

    if ($oldversion < 2024092606) {
        // Define table local_equipment_vccsubmission_student.
        $table = new xmldb_table('local_equipment_phonecommunication_otp');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('otp', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->add_field('tophonenumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->add_field('tophonename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->add_field('phoneisverified', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeverified', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('expires', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Equipment savepoint reached.
        upgrade_plugin_savepoint(true, 2024092606, 'local', 'equipment');
    }

    if ($oldversion < 2024110101) {
        // Define table local_equipment_vccsubmission_student.
        $table = new xmldb_table('local_equipment_partnership');

        $field = new xmldb_field('listingid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'pickupid');

        $key = new xmldb_key('listingid', XMLDB_KEY_FOREIGN, ['listingid'], 'local_equipment_partnership', ['id']);

        if (!$dbman->field_exists($table, 'listingid')) {
            $dbman->add_field($table, $field);
            $dbman->add_key($table, $key);
        }

        $field = new xmldb_field('courseids', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if ($dbman->field_exists($table, 'courseids')) {
            $dbman->drop_field($table, $field);
        }


        // Equipment savepoint reached.
        upgrade_plugin_savepoint(true, 2024110101, 'local', 'equipment');
    }

    if ($oldversion < 2024112100) {
        // Ensure capability exists
        $capabilities = [
            'local/equipment:receivewelcomemessage' => [
                'riskbitmask' => RISK_PERSONAL,
                'captype' => 'read',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => [
                    'user' => CAP_ALLOW,
                    'student' => CAP_ALLOW,
                    'teacher' => CAP_ALLOW,
                ],
            ],
        ];

        // Update capabilities
        update_capabilities('local_equipment', $capabilities);
        upgrade_plugin_savepoint(true, 2024112100, 'local', 'equipment');
    }

    if ($oldversion < 2025031200) {

        // Define table local_equipment_user_exchange.
        $table = new xmldb_table('local_equipment_user_exchange');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('exchangeid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('reminder_code', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('reminder_method', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('exchangeid', XMLDB_KEY_FOREIGN, ['exchangeid'], 'local_equipment_pickup', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Equipment savepoint reached.
        upgrade_plugin_savepoint(true, 2025031200, 'local', 'equipment');
    }

    if ($oldversion < 2025032500) {

        // Add pickup fields to the local_equipment_pickup table.
        $table = new xmldb_table('local_equipment_pickup');

        $oldfield = new xmldb_field('address_extrainput', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'status');
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->rename_field($table, $oldfield, 'pickup_extrainput');
        }
        $oldfield = new xmldb_field('address_sameasmailing', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_extrainput');
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->rename_field($table, $oldfield, 'pickup_sameasmailing');
        }
        $oldfield = new xmldb_field('address_sameasphysical', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_sameasmailing');
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->rename_field($table, $oldfield, 'pickup_sameasphysical');
        }
        $oldfield = new xmldb_field('address_streetaddress', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_sameasphysical');
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->rename_field($table, $oldfield, 'pickup_streetaddress');
        }
        $oldfield = new xmldb_field('address_apartment', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_streetaddress');
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->rename_field($table, $oldfield, 'pickup_apartment');
        }
        $oldfield = new xmldb_field('address_city', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_apartment');
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->rename_field($table, $oldfield, 'pickup_city');
        }
        $oldfield = new xmldb_field('address_state', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_city');
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->rename_field($table, $oldfield, 'pickup_state');
        }
        $oldfield = new xmldb_field('address_country', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_state');
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->rename_field($table, $oldfield, 'pickup_country');
        }
        $oldfield = new xmldb_field('address_zipcode', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'address_country');
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->rename_field($table, $oldfield, 'pickup_zipcode');
        }
        $oldfield = new xmldb_field('address_extrainstructions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'address_zipcode');
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->rename_field($table, $oldfield, 'pickup_extrainstructions');
        }

        // Equipment savepoint reached.
        upgrade_plugin_savepoint(true, 2025032500, 'local', 'equipment');
    }

    if ($oldversion < 2025052200) {
        // Define table local_equipment_exchange_submission.
        $table = new xmldb_table('local_equipment_exchange_submission');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('exchangeid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('pickup_method', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('pickup_person_name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('pickup_person_phone', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('pickup_person_details', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('user_notes', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('exchangeid', XMLDB_KEY_FOREIGN, ['exchangeid'], 'local_equipment_pickup', ['id']);

        $table->add_index('timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
        $table->add_index('userid_exchangeid', XMLDB_INDEX_NOTUNIQUE, ['userid', 'exchangeid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Equipment savepoint reached.
        upgrade_plugin_savepoint(true, 2025052200, 'local', 'equipment');
    }

    // Inventory Management System - Phase 1: Foundation
    if ($oldversion < 2025061900) {

        // Define table local_equipment_products - Product catalog for equipment types
        $table = new xmldb_table('local_equipment_products');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('manufacturer', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('model', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('category', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('is_consumable', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('expected_shelf_life', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('purchase_value', XMLDB_TYPE_NUMBER, '10,2', null, null, null, null);
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('name', XMLDB_INDEX_NOTUNIQUE, ['name']);
        $table->add_index('manufacturer', XMLDB_INDEX_NOTUNIQUE, ['manufacturer']);
        $table->add_index('active', XMLDB_INDEX_NOTUNIQUE, ['active']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_locations - Storage locations for equipment
        $table = new xmldb_table('local_equipment_locations');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('address', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('zone', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('name', XMLDB_INDEX_NOTUNIQUE, ['name']);
        $table->add_index('active', XMLDB_INDEX_NOTUNIQUE, ['active']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_items - Individual equipment items with UUID tracking
        $table = new xmldb_table('local_equipment_items');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('uuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('serial_number', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('locationid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'available');
        $table->add_field('condition_status', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'good');
        $table->add_field('condition_notes', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('last_tested', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('current_userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('student_label', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('transfer_destination', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('is_complete_set', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('expected_return_date', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('productid', XMLDB_KEY_FOREIGN, ['productid'], 'local_equipment_products', ['id']);
        $table->add_key('locationid', XMLDB_KEY_FOREIGN, ['locationid'], 'local_equipment_locations', ['id']);
        $table->add_key('current_userid', XMLDB_KEY_FOREIGN, ['current_userid'], 'user', ['id']);
        $table->add_key('transfer_destination', XMLDB_KEY_FOREIGN, ['transfer_destination'], 'local_equipment_locations', ['id']);

        $table->add_index('uuid', XMLDB_INDEX_UNIQUE, ['uuid']);
        $table->add_index('status', XMLDB_INDEX_NOTUNIQUE, ['status']);
        $table->add_index('condition_status', XMLDB_INDEX_NOTUNIQUE, ['condition_status']);
        $table->add_index('serial_number', XMLDB_INDEX_NOTUNIQUE, ['serial_number']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_uuid_history - History of all UUIDs associated with equipment items
        $table = new xmldb_table('local_equipment_uuid_history');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('uuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null);
        $table->add_field('is_active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('created_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('deactivated_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('deactivated_reason', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timedeactivated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('itemid', XMLDB_KEY_FOREIGN, ['itemid'], 'local_equipment_items', ['id']);
        $table->add_key('created_by', XMLDB_KEY_FOREIGN, ['created_by'], 'user', ['id']);
        $table->add_key('deactivated_by', XMLDB_KEY_FOREIGN, ['deactivated_by'], 'user', ['id']);

        $table->add_index('uuid', XMLDB_INDEX_NOTUNIQUE, ['uuid']);
        $table->add_index('itemid_active', XMLDB_INDEX_NOTUNIQUE, ['itemid', 'is_active']);
        $table->add_index('is_active', XMLDB_INDEX_NOTUNIQUE, ['is_active']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_configurations - Equipment configurations for different course arrangements
        $table = new xmldb_table('local_equipment_configurations');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('code', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('createdby', XMLDB_KEY_FOREIGN, ['createdby'], 'user', ['id']);

        $table->add_index('code', XMLDB_INDEX_UNIQUE, ['code']);
        $table->add_index('active', XMLDB_INDEX_NOTUNIQUE, ['active']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_config_products - Products assigned to equipment configurations
        $table = new xmldb_table('local_equipment_config_products');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('configid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('quantity_per_student', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('is_required', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('sort_order', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('configid', XMLDB_KEY_FOREIGN, ['configid'], 'local_equipment_configurations', ['id']);
        $table->add_key('productid', XMLDB_KEY_FOREIGN, ['productid'], 'local_equipment_products', ['id']);

        $table->add_index('configid_productid', XMLDB_INDEX_UNIQUE, ['configid', 'productid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_course_configs - Links courses to equipment configurations
        $table = new xmldb_table('local_equipment_course_configs');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('configid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        $table->add_key('configid', XMLDB_KEY_FOREIGN, ['configid'], 'local_equipment_configurations', ['id']);
        $table->add_key('createdby', XMLDB_KEY_FOREIGN, ['createdby'], 'user', ['id']);


        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_transactions - Log of all equipment check-in/check-out transactions
        $table = new xmldb_table('local_equipment_transactions');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('transaction_type', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('from_userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('to_userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('from_locationid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('to_locationid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('processed_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('notes', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('condition_before', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('condition_after', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('pickup_exchangeid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('itemid', XMLDB_KEY_FOREIGN, ['itemid'], 'local_equipment_items', ['id']);
        $table->add_key('from_userid', XMLDB_KEY_FOREIGN, ['from_userid'], 'user', ['id']);
        $table->add_key('to_userid', XMLDB_KEY_FOREIGN, ['to_userid'], 'user', ['id']);
        $table->add_key('from_locationid', XMLDB_KEY_FOREIGN, ['from_locationid'], 'local_equipment_locations', ['id']);
        $table->add_key('to_locationid', XMLDB_KEY_FOREIGN, ['to_locationid'], 'local_equipment_locations', ['id']);
        $table->add_key('processed_by', XMLDB_KEY_FOREIGN, ['processed_by'], 'user', ['id']);
        $table->add_key('pickup_exchangeid', XMLDB_KEY_FOREIGN, ['pickup_exchangeid'], 'local_equipment_pickup', ['id']);

        $table->add_index('itemid_timestamp', XMLDB_INDEX_NOTUNIQUE, ['itemid', 'timestamp']);
        $table->add_index('transaction_type', XMLDB_INDEX_NOTUNIQUE, ['transaction_type']);
        $table->add_index('timestamp', XMLDB_INDEX_NOTUNIQUE, ['timestamp']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_equipment_allocations - Equipment allocated to students based on course enrollment
        $table = new xmldb_table('local_equipment_allocations');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('productid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('quantity', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('status', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'allocated');
        $table->add_field('allocated_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('assigned_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('pickup_exchangeid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('expected_return_date', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        $table->add_key('productid', XMLDB_KEY_FOREIGN, ['productid'], 'local_equipment_products', ['id']);
        $table->add_key('itemid', XMLDB_KEY_FOREIGN, ['itemid'], 'local_equipment_items', ['id']);
        $table->add_key('allocated_by', XMLDB_KEY_FOREIGN, ['allocated_by'], 'user', ['id']);
        $table->add_key('assigned_by', XMLDB_KEY_FOREIGN, ['assigned_by'], 'user', ['id']);
        $table->add_key('pickup_exchangeid', XMLDB_KEY_FOREIGN, ['pickup_exchangeid'], 'local_equipment_pickup', ['id']);

        $table->add_index('userid_courseid', XMLDB_INDEX_NOTUNIQUE, ['userid', 'courseid']);
        $table->add_index('status', XMLDB_INDEX_NOTUNIQUE, ['status']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Equipment inventory savepoint reached.
        upgrade_plugin_savepoint(true, 2025061900, 'local', 'equipment');
    }

    // Add picture field to products table for Phase 1 completion
    if ($oldversion < 2025062301) {
        $table = new xmldb_table('local_equipment_products');

        // Add picture field to store file ID for product images
        $field = new xmldb_field('picture', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'active');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Equipment inventory picture field savepoint reached.
        upgrade_plugin_savepoint(true, 2025062301, 'local', 'equipment');
    }

    // Add UPC field to products table for Phase 1 completion
    if ($oldversion < 2025062302) {
        $table = new xmldb_table('local_equipment_products');

        // Add UPC field for barcode integration
        $field = new xmldb_field('upc', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'picture');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Equipment inventory UPC field savepoint reached.
        upgrade_plugin_savepoint(true, 2025062302, 'local', 'equipment');
    }

    // Add missing removal fields to equipment items table
    if ($oldversion < 2025072201) {
        $table = new xmldb_table('local_equipment_items');

        // Add removal_reason field
        $field = new xmldb_field('removal_reason', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'timemodified');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add removal_date field
        $field = new xmldb_field('removal_date', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'removal_reason');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add removed_by field
        $field = new xmldb_field('removed_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'removal_date');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add foreign key for removed_by field
        $key = new xmldb_key('removed_by', XMLDB_KEY_FOREIGN, ['removed_by'], 'user', ['id']);
        $dbman->add_key($table, $key);

        // Equipment removal fields savepoint reached.
        upgrade_plugin_savepoint(true, 2025072201, 'local', 'equipment');
    }

    // QR code print queue system for equipment items
    if ($oldversion < 2025072400) {
        // Define table local_equipment_qr_print_queue.
        $table = new xmldb_table('local_equipment_qr_print_queue');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('uuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null);
        $table->add_field('queued_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('queued_time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('printed_time', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('notes', XMLDB_TYPE_TEXT, null, null, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('itemid', XMLDB_KEY_FOREIGN, ['itemid'], 'local_equipment_items', ['id']);
        $table->add_key('queued_by', XMLDB_KEY_FOREIGN, ['queued_by'], 'user', ['id']);

        $table->add_index('queued_time', XMLDB_INDEX_NOTUNIQUE, ['queued_time']);
        $table->add_index('printed_time', XMLDB_INDEX_NOTUNIQUE, ['printed_time']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Equipment QR print queue savepoint reached.
        upgrade_plugin_savepoint(true, 2025072400, 'local', 'equipment');
    }

    // Add dateofbirth field to local_equipment_user table for VCC form enhancement
    if ($oldversion < 2025081401) {
        // Define field dateofbirth to be added to local_equipment_user.
        $table = new xmldb_table('local_equipment_user');
        $field = new xmldb_field('dateofbirth', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'billing_extrainstructions');

        // Conditionally launch add field dateofbirth.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Equipment dateofbirth field savepoint reached.
        upgrade_plugin_savepoint(true, 2025081401, 'local', 'equipment');
    }

    if ($oldversion < 2025081500) {

        // Define table to be modified
        $table = new xmldb_table('local_equipment_vccsubmission_student');

        // First, drop the dateofbirth index if it exists
        $index = new xmldb_index('dateofbirth', XMLDB_INDEX_NOTUNIQUE, array('dateofbirth'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define field dateofbirth to be modified
        $field = new xmldb_field('dateofbirth', XMLDB_TYPE_INTEGER, '10', null, false, null, null, 'email');

        // Launch change of nullability for field dateofbirth
        $dbman->change_field_notnull($table, $field);

        // Equipment savepoint reached
        upgrade_plugin_savepoint(true, 2025081500, 'local', 'equipment');
    }

    return true;
}
