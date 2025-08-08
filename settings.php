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
 * Equipment checkout settings.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/equipment/lib.php');

if ($hassiteconfig) {
    $component = 'local_equipment';

    // ========================================
    // 1. CREATE MAIN CATEGORY
    // ========================================
    $ADMIN->add(
        'modules',
        new admin_category(
            $component,
            new lang_string('equipment', 'local_equipment')
        )
    );

    // ========================================
    // 2. GENERAL SETTINGS PAGE
    // ========================================
    $generalsettings = new admin_settingpage(
        "{$component}_settings",
        get_string('equipmentsettings', 'local_equipment')
    );

    // Partnership settings section
    $generalsettings->add(new admin_setting_heading(
        "{$component}/partnershipheading",
        get_string('partnershipsettings', 'local_equipment'),
        get_string('partnershipsettings_desc', 'local_equipment')
    ));

    $generalsettings->add(new admin_setting_configtext(
        "{$component}/schoolyearrangetoautoselect_start",
        get_string('schoolyearrangetoautoselect_start', 'local_equipment'),
        get_string('schoolyearrangetoautoselect_start_desc', 'local_equipment') . ' ' .
            get_string('schoolyearrangetoautoselect_appendingdesc', 'local_equipment'),
        explode('-', local_equipment_get_school_year())[0],
        PARAM_TEXT
    ));

    $generalsettings->add(new admin_setting_configtext(
        "{$component}/schoolyearrangetoautoselect_end",
        get_string('schoolyearrangetoautoselect_end', 'local_equipment'),
        get_string('schoolyearrangetoautoselect_end_desc', 'local_equipment') . ' ' .
            get_string('schoolyearrangetoautoselect_appendingdesc', 'local_equipment'),
        explode('-', local_equipment_get_school_year())[1],
        PARAM_TEXT
    ));

    $generalsettings->add(new admin_setting_configtext(
        "{$component}/partnershipcategoryprefix",
        get_string('partnershipcategoryprefix', 'local_equipment'),
        get_string('partnershipcategoryprefix_desc', 'local_equipment'),
        'partnership',
        PARAM_TEXT
    ));

    // Pickups settings section
    $generalsettings->add(new admin_setting_heading(
        "{$component}/pickupheading",
        get_string('pickupsettings', 'local_equipment'),
        get_string('pickupsettings_desc', 'local_equipment')
    ));

    $generalsettings->add(new admin_setting_configtext(
        "{$component}/endedpickupstoshow",
        get_string('endedpickupstoshow', 'local_equipment'),
        get_string('endedpickupstoshow_desc', 'local_equipment'),
        '7',
        PARAM_INT
    ));

    $ADMIN->add($component, $generalsettings);

    // ========================================
    // 3. NOTIFICATIONS SETTINGS PAGE
    // ========================================
    $notificationsettings = new admin_settingpage(
        "{$component}_notifications",
        get_string('notificationsettings', 'local_equipment')
    );

    $notificationsettings->add(new admin_setting_configcheckbox(
        "{$component}/notify_parents",
        get_string('notifyparents', 'local_equipment'),
        get_string('notifyparents_desc', 'local_equipment'),
        1
    ));

    $notificationsettings->add(new admin_setting_configcheckbox(
        "{$component}/notify_students",
        get_string('notifystudents', 'local_equipment'),
        get_string('notifystudents_desc', 'local_equipment'),
        1
    ));

    $options = [
        ENROL_SEND_EMAIL_FROM_KEY_HOLDER => get_string('fromkeyholder', 'local_equipment'),
        ENROL_SEND_EMAIL_FROM_NOREPLY => get_string('fromnoreply', 'local_equipment')
    ];

    $notificationsettings->add(new admin_setting_configselect(
        "{$component}/messagesender",
        get_string('messagesender', 'local_equipment'),
        get_string('messagesender_desc', 'local_equipment'),
        ENROL_SEND_EMAIL_FROM_NOREPLY,
        $options
    ));

    // Reminders section
    $notificationsettings->add(new admin_setting_heading(
        "{$component}/reminderheading",
        get_string('reminderheading', 'local_equipment'),
        get_string('reminderheading_desc', 'local_equipment')
    ));

    $notificationsettings->add(new admin_setting_configtext(
        "{$component}/inadvance_days",
        get_string('reminder_inadvance_days', 'local_equipment'),
        get_string('reminder_inadvance_days_desc', 'local_equipment'),
        '7',
        PARAM_INT
    ));

    $notificationsettings->add(new admin_setting_configtext(
        "{$component}/inadvance_hours",
        get_string('reminder_inadvance_hours', 'local_equipment'),
        get_string('reminder_inadvance_hours_desc', 'local_equipment'),
        '1',
        PARAM_INT
    ));

    $notificationsettings->add(new admin_setting_configtext(
        "{$component}/reminder_timeout",
        get_string('reminder_timeout', 'local_equipment'),
        get_string('reminder_timeout_desc', 'local_equipment'),
        '24',
        PARAM_TEXT
    ));

    $notificationsettings->add(new admin_setting_configtextarea(
        "{$component}/reminder_template_days",
        get_string('reminder_template_days', 'local_equipment'),
        get_string('reminder_template_days_desc', 'local_equipment'),
        get_string('reminder_template_days_default', 'local_equipment'),
        PARAM_TEXT
    ));

    $notificationsettings->add(new admin_setting_configtextarea(
        "{$component}/reminder_template_hours",
        get_string('reminder_template_hours', 'local_equipment'),
        get_string('reminder_template_hours_desc', 'local_equipment'),
        get_string('reminder_template_hours_default', 'local_equipment'),
        PARAM_TEXT
    ));

    $ADMIN->add($component, $notificationsettings);

    // ========================================
    // 4. CREATE SUBCATEGORIES
    // ========================================
    $ADMIN->add(
        $component,
        new admin_category(
            "{$component}_partnerships_cat",
            new lang_string('partnerships', 'local_equipment')
        )
    );

    $ADMIN->add(
        $component,
        new admin_category(
            "{$component}_pickups_cat",
            new lang_string('pickups', 'local_equipment')
        )
    );

    $ADMIN->add(
        $component,
        new admin_category(
            "{$component}_agreements_cat",
            new lang_string('agreements', 'local_equipment')
        )
    );

    $ADMIN->add(
        $component,
        new admin_category(
            "{$component}_inventory_cat",
            new lang_string('inventory', 'local_equipment')
        )
    );

    // ========================================
    // 5. PARTNERSHIPS EXTERNAL PAGES
    // ========================================
    $ADMIN->add(
        "{$component}_partnerships_cat",
        new admin_externalpage(
            "{$component}_partnerships",
            new lang_string('viewmanagepartnerships', 'local_equipment'),
            new moodle_url('/local/equipment/partnerships.php'),
            'local/equipment:managepartnerships'
        )
    );

    $ADMIN->add(
        "{$component}_partnerships_cat",
        new admin_externalpage(
            "{$component}_addpartnerships",
            new lang_string('addpartnerships', 'local_equipment'),
            new moodle_url('/local/equipment/partnerships/addpartnerships.php'),
            'local/equipment:managepartnerships'
        )
    );

    // ========================================
    // 6. PICKUPS EXTERNAL PAGES
    // ========================================
    $ADMIN->add(
        "{$component}_pickups_cat",
        new admin_externalpage(
            "{$component}_pickups",
            new lang_string('viewmanagepickups', 'local_equipment'),
            new moodle_url('/local/equipment/pickups.php'),
            'local/equipment:managepickups'
        )
    );

    $ADMIN->add(
        "{$component}_pickups_cat",
        new admin_externalpage(
            "{$component}_addpickups",
            new lang_string('addpickups', 'local_equipment'),
            new moodle_url('/local/equipment/pickups/addpickups.php'),
            'local/equipment:managepickups'
        )
    );

    // ========================================
    // 7. AGREEMENTS EXTERNAL PAGES
    // ========================================
    $ADMIN->add(
        "{$component}_agreements_cat",
        new admin_externalpage(
            "{$component}_agreements",
            new lang_string('viewmanageagreements', 'local_equipment'),
            new moodle_url('/local/equipment/agreements.php'),
            'local/equipment:manageagreements'
        )
    );

    $ADMIN->add(
        "{$component}_agreements_cat",
        new admin_externalpage(
            "{$component}_addagreements",
            new lang_string('addagreements', 'local_equipment'),
            new moodle_url('/local/equipment/agreements/addagreements.php'),
            'local/equipment:manageagreements'
        )
    );

    // ========================================
    // 8. INVENTORY EXTERNAL PAGES
    // ========================================
    $ADMIN->add(
        "{$component}_inventory_cat",
        new admin_externalpage(
            "{$component}_inventory_manage",
            new lang_string('manageinventory', 'local_equipment'),
            new moodle_url('/local/equipment/inventory/manage.php'),
            'local/equipment:manageinventory'
        )
    );

    $ADMIN->add(
        "{$component}_inventory_cat",
        new admin_externalpage(
            "{$component}_inventory_qr",
            new lang_string('generateqr', 'local_equipment'),
            new moodle_url('/local/equipment/inventory/generate_qr.php'),
            'local/equipment:generateqr'
        )
    );

    $ADMIN->add(
        "{$component}_inventory_cat",
        new admin_externalpage(
            "{$component}_inventory_checkinout",
            new lang_string('checkinout', 'local_equipment'),
            new moodle_url('/local/equipment/inventory/check_inout.php'),
            'local/equipment:checkinout'
        )
    );

    $ADMIN->add(
        "{$component}_inventory_cat",
        new admin_externalpage(
            "{$component}_inventory_products",
            new lang_string('manageproducts', 'local_equipment'),
            new moodle_url('/local/equipment/inventory/products.php'),
            'local/equipment:manageinventory'
        )
    );

    $ADMIN->add(
        "{$component}_inventory_cat",
        new admin_externalpage(
            "{$component}_inventory_locations",
            new lang_string('managelocations', 'local_equipment'),
            new moodle_url('/local/equipment/inventory/locations.php'),
            'local/equipment:manageinventory'
        )
    );

    $ADMIN->add(
        "{$component}_inventory_cat",
        new admin_externalpage(
            "{$component}_inventory_additems",
            new lang_string('additems', 'local_equipment'),
            new moodle_url('/local/equipment/inventory/add_items.php'),
            'local/equipment:manageinventory'
        )
    );

    $ADMIN->add(
        "{$component}_inventory_cat",
        new admin_externalpage(
            "{$component}_inventory_removeitems",
            new lang_string('removeitems', 'local_equipment'),
            new moodle_url('/local/equipment/inventory/remove_items.php'),
            'local/equipment:manageinventory'
        )
    );

    $ADMIN->add(
        "{$component}_inventory_cat",
        new admin_externalpage(
            "{$component}_inventory_courseassignments",
            new lang_string('courseconfigurations', 'local_equipment'),
            new moodle_url('/local/equipment/inventory/course_assignments.php'),
            'local/equipment:manageconfigurations'
        )
    );

    $ADMIN->add(
        "{$component}_inventory_cat",
        new admin_externalpage(
            "{$component}_inventory_reports",
            new lang_string('inventoryreports', 'local_equipment'),
            new moodle_url('/local/equipment/inventory/reports.php'),
            'local/equipment:viewreports'
        )
    );

    // ========================================
    // 9. OTHER DIRECT PAGES UNDER MAIN CATEGORY
    // ========================================
    $ADMIN->add(
        $component,
        new admin_externalpage(
            "{$component}_vccsubmissions",
            new lang_string('managevccsubmissions', 'local_equipment'),
            new moodle_url('/local/equipment/vccsubmissions.php'),
            'local/equipment:managevccsubmissions'
        )
    );

    $ADMIN->add(
        $component,
        new admin_externalpage(
            "{$component}_addbulkfamilies",
            new lang_string('addbulkfamilies', 'local_equipment'),
            new moodle_url('/local/equipment/addbulkfamilies.php')
        )
    );

    $ADMIN->add(
        $component,
        new admin_externalpage(
            "{$component}_mass_text",
            new lang_string('masstextmessaging', 'local_equipment'),
            new moodle_url('/local/equipment/mass_text_message.php'),
            'local/equipment:sendmasstextmessages'
        )
    );

    // ========================================
    // 10. SERVER CATEGORY ADDITIONS
    // ========================================
    $ADMIN->add(
        'server',
        new admin_externalpage(
            "{$component}_testoutgoingtextconf",
            new lang_string('testoutgoingtextconf', 'local_equipment'),
            new moodle_url('/local/equipment/phonecommunication/testoutgoingtextconf.php'),
            'moodle/site:config',
            true
        )
    );

    $ADMIN->add(
        'server',
        new admin_externalpage(
            "{$component}_verifytestotp",
            new lang_string('verifytestotp', 'local_equipment'),
            new moodle_url('/local/equipment/phonecommunication/verifytestotp.php'),
            'moodle/site:config',
            true
        )
    );

    // ========================================
    // 11. PHONE PROVIDER SETTINGS
    // ========================================
    $ADMIN->add(
        'server',
        new admin_category(
            "{$component}_phone",
            new lang_string('phone', 'local_equipment')
        )
    );

    $phonesettings = new admin_settingpage(
        "{$component}_phoneprovider",
        new lang_string('phoneproviderconfiguration', 'local_equipment')
    );

    if ($ADMIN->fulltree) {
        // Gateway selection
        $link = html_writer::link($CFG->wwwroot . '/sms/sms_gateways.php', get_string('here', 'local_equipment'));
        $gateways = local_equipment_get_sms_gateways();
        $gatewayoptions = [0 => get_string('selectagateway', 'local_equipment')] + $gateways;

        $phonesettings->add(new admin_setting_heading(
            "{$component}/gateway",
            new lang_string('smsgatewaystouse', 'local_equipment'),
            new lang_string('smsgatewaystouse_desc', 'local_equipment', $link)
        ));

        $phonesettings->add(new admin_setting_configselect(
            "{$component}/otpgateway",
            new lang_string('otpgateway', 'local_equipment'),
            new lang_string('otpgateway_desc', 'local_equipment'),
            '',
            $gatewayoptions
        ));

        $phonesettings->add(new admin_setting_configselect(
            "{$component}/infogateway",
            new lang_string('infogateway', 'local_equipment'),
            new lang_string('infogateway_desc', 'local_equipment'),
            '',
            $gatewayoptions
        ));

        // AWS Configuration
        $phonesettings->add(new admin_setting_configtext(
            "{$component}/awsconfigurationset",
            new lang_string('awsconfigurationset', 'local_equipment'),
            new lang_string('awsconfigurationset_desc', 'local_equipment'),
            '',
            PARAM_ALPHANUMEXT,
            30
        ));

        $phonesettings->add(new admin_setting_configtext(
            "{$component}/awsinfopoolid",
            new lang_string('awsinfopoolid', 'local_equipment'),
            new lang_string('awsinfopoolid_desc', 'local_equipment'),
            '',
            PARAM_ALPHANUMEXT,
            50
        ));

        $phonesettings->add(new admin_setting_configtext(
            "{$component}/awsotppoolid",
            new lang_string('awsotppoolid', 'local_equipment'),
            new lang_string('awsotppoolid_desc', 'local_equipment'),
            '',
            PARAM_ALPHANUMEXT,
            50
        ));

        $phonesettings->add(new admin_setting_configtext(
            "{$component}/awsinfooriginatorphone",
            new lang_string('awsinfooriginatorphone', 'local_equipment'),
            new lang_string('awsinfooriginatorphone_desc', 'local_equipment'),
            '',
            PARAM_TEXT,
            20
        ));

        $phonesettings->add(new admin_setting_configtext(
            "{$component}/awsotporiginatorphone",
            new lang_string('awsotporiginatorphone', 'local_equipment'),
            new lang_string('awsotporiginatorphone_desc', 'local_equipment'),
            '',
            PARAM_TEXT,
            20
        ));

        // Test configuration links
        $testurl = new moodle_url('/local/equipment/phonecommunication/testoutgoingtextconf.php');
        $testlink = html_writer::link($testurl, get_string('testoutgoingtextconf', 'local_equipment'));
        $phonesettings->add(new admin_setting_heading(
            "{$component}_testoutgoingtextconf",
            new lang_string('testoutgoingtextconf', 'local_equipment'),
            new lang_string('testoutgoingtextdetail', 'local_equipment', $testlink)
        ));

        $otpurl = new moodle_url('/local/equipment/phonecommunication/verifytestotp.php');
        $otplink = html_writer::link($otpurl, get_string('verifytestotp', 'local_equipment'));
        $phonesettings->add(new admin_setting_heading(
            "{$component}_verifytestotp",
            new lang_string('verifytestotp', 'local_equipment'),
            new lang_string('verifytestotpdetail', 'local_equipment', $otplink)
        ));
    }

    $ADMIN->add("{$component}_phone", $phonesettings);
}
