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

    // Let's define some variables for all the name of the part of the admin tree where the setting will be added.

    // The Site administration tab that these settings will be seen under.
    // The 'Plugins' tab is actually called 'modules' in the code. Took me forever to figure that out.
    $component = 'local_equipment';

    // Let's create all the categories and subcategories we will need for the admins to access.
    // Remember, categories and subcategories do not appear until they have a page or link added to them.

    // Main category must be created first!
    // Create a new category under the Site administration > Plugins tab, which is actually called 'modules' in the codebase. Took me forever to figure that out.
    $ADMIN->add(
        'modules',
        new admin_category(
            $component,
            new lang_string('equipment', 'local_equipment')
        )
    );

    // This is just creating a standard settings page for the Equipment plugin.
    $settings = new admin_settingpage("{$component}_settings", get_string('equipmentsettings', 'local_equipment'));

    // Add Partnership sub-settings
    $settings->add(new admin_setting_heading(
        "{$component}/partnershipheading",
        get_string(
            'partnershipsettings',
            'local_equipment'
        ),
        get_string('partnershipsettings_desc', 'local_equipment')
    ));

    // The starting school year to use for displaying courses for a given partnership.
    // Defaults to the starting year of the current school in the United States.
    $settings->add(new admin_setting_configtext(
        "{$component}/schoolyearrangetoautoselect_start",
        get_string('schoolyearrangetoautoselect_start', 'local_equipment'),
        get_string('schoolyearrangetoautoselect_start_desc', 'local_equipment') . ' ' .
        get_string('schoolyearrangetoautoselect_appendingdesc', 'local_equipment'),
        explode('-', local_equipment_get_school_year())[0],
        PARAM_TEXT
    ));

    // The ending school year to use for displaying courses for a given partnership.
    // Defaults to the ending year of the current school in the United States.
    $settings->add(new admin_setting_configtext(
        "{$component}/schoolyearrangetoautoselect_end",
        get_string('schoolyearrangetoautoselect_end', 'local_equipment'),
        get_string('schoolyearrangetoautoselect_end_desc', 'local_equipment') . ' ' .
        get_string('schoolyearrangetoautoselect_appendingdesc', 'local_equipment'),
        explode('-', local_equipment_get_school_year())[1],
        PARAM_TEXT
    ));


    // Prefix keyword for each Partnerships' "idnumber" field in mdl_course_categories, as entered by a system administrator.
    // Default is "partnership".
    // Format is "{prefix}#{partnershipid}_{schoolyearrangetoautoselect_start}-{schoolyearrangetoautoselect_end}".
    // Example: "partnership" prefix, a partnership with id of "8", "2024" schoolyear start, and "2025" school year end
    //     would result in "partnership#8_2024-2025".
    $settings->add(new admin_setting_configtext(
        "{$component}/partnershipcategoryprefix",
        get_string('partnershipcategoryprefix', 'local_equipment'),
        get_string('partnershipcategoryprefix_desc', 'local_equipment'),
        'partnership',
        PARAM_TEXT
    ));




    // Add Pickups sub-settings
    $settings->add(new admin_setting_heading(
        "{$component}/pickupheading",
        get_string(
            'pickupsettings',
            'local_equipment'
        ),
        get_string('pickupsettings_desc', 'local_equipment')
    ));

    // Number of ended pickups to show.
    $settings->add(new admin_setting_configtext(
        "{$component}/endedpickupstoshow",
        get_string('endedpickupstoshow', 'local_equipment'),
        get_string('endedpickupstoshow_desc', 'local_equipment'),
        '7', // Default value
        PARAM_INT
    ));


    $ADMIN->add($component, $settings);


    // Add 'Partnerships' subcategory.
    $ADMIN->add(
        $component,
        new admin_category(
            "{$component}_partnerships_cat",
            new lang_string('partnerships', 'local_equipment')
        )
    );
    // Add 'Pickups' subcategory.
    $ADMIN->add(
        $component,
        new admin_category(
            "{$component}_pickups_cat",
            new lang_string('pickups', 'local_equipment')
        )
    );
    // Add 'Agreements' subcategory.
    $ADMIN->add(
        $component,
        new admin_category(
                "{$component}_agreements_cat",
                new lang_string('agreements', 'local_equipment')
            )
    );
    // Add 'Virtual course consent (vcc) form' subcategory.
    $ADMIN->add(
        $component,
        new admin_category(
            "{$component}_vccsubmission_cat",
            new lang_string('virtualcourseconsent', 'local_equipment')
        )
    );
    // Add 'Add bulk families' subcategory.
    $ADMIN->add(
        $component,
        new admin_category(
            "{$component}_addbulkfamilies_cat",
            new lang_string('addbulkfamilies', 'local_equipment')
        )
    );

    // An external page is a link to a page outside of the Moodle admin settings, i.e. to a file within the custom plugin.
    // Add the manage partnerships page.
    $ADMIN->add(
        "{$component}_partnerships_cat",
        new admin_externalpage(
            "{$component}_partnerships", // Needs to match the parameter in 'admin_externalpage_setup()' on the partnerships.php page.
            new lang_string('viewmanagepartnerships', 'local_equipment'),
            new moodle_url('/local/equipment/partnerships.php'),
        )
    );
    // Add a link to the add partnership page.
    $ADMIN->add(
        "{$component}_partnerships_cat",
        new admin_externalpage(
            "{$component}_addpartnerships",
            new lang_string('addpartnerships', 'local_equipment'),
            new moodle_url('/local/equipment/partnerships/addpartnerships.php')
        )
    );
    // Add the manage pickups page.
    $ADMIN->add(
        "{$component}_pickups_cat",
        new admin_externalpage(
            "{$component}_pickups",
            new lang_string('viewmanagepickups', 'local_equipment'),
            new moodle_url('/local/equipment/pickups.php'),
        )
    );
    // Add a link to the add pickups page.
    $ADMIN->add(
        "{$component}_pickups_cat",
        new admin_externalpage(
            "{$component}_addpickups",
            new lang_string('addpickups', 'local_equipment'),
            new moodle_url('/local/equipment/pickups/addpickups.php')
        )
    );
    // Add the manage agreements page.
    $ADMIN->add(
        "{$component}_agreements_cat",
        new admin_externalpage(
            "{$component}_agreements",
            new lang_string('viewmanageagreements', 'local_equipment'),
            new moodle_url('/local/equipment/agreements.php'),
        )
    );
    // Add a link to the add agreements page.
    $ADMIN->add(
        "{$component}_agreements_cat",
        new admin_externalpage(
            "{$component}_addagreements",
            new lang_string('addagreements', 'local_equipment'),
            new moodle_url('/local/equipment/agreements/addagreements.php')
        )
    );

    // Add the manage virtual course consent (vcc) form page.
    $ADMIN->add($component, new admin_externalpage(
        'local_equipment_vccsubmissions',
        new lang_string('managevccsubmissions', 'local_equipment'),
        new moodle_url('/local/equipment/vccsubmissions.php')
    ));

    // Add the 'Add bulk families' form page.
    $ADMIN->add($component, new admin_externalpage(
        'local_equipment_addbulkfamilies',
        new lang_string('addbulkfamilies', 'local_equipment'),
        new moodle_url('/local/equipment/addbulkfamilies.php')
    ));

    // settings.php
    // $ADMIN->add($component, new admin_settingpage("{$component}_notifications", get_string('notificationsettings', 'local_equipment')));

    $settings = new admin_settingpage("{$component}_notifications", get_string('notificationsettings', 'local_equipment'));

    // Parent notification settings
    $settings->add(new admin_setting_configcheckbox(
        'local_equipment/notify_parents',
        get_string('notifyparents', 'local_equipment'),
        get_string('notifyparents_desc', 'local_equipment'),
        1
    ));

    // Student notification settings
    $settings->add(new admin_setting_configcheckbox(
        'local_equipment/notify_students',
        get_string('notifystudents', 'local_equipment'),
        get_string('notifystudents_desc', 'local_equipment'),
        1
    ));

    // Message sender setting
    $options = [
        // ENROL_SEND_EMAIL_FROM_COURSE_CONTACT => get_string(
        //     'fromcoursecontact',
        //     'local_equipment'
        // ),
        ENROL_SEND_EMAIL_FROM_KEY_HOLDER => get_string('fromkeyholder', 'local_equipment'),
        ENROL_SEND_EMAIL_FROM_NOREPLY => get_string(
            'fromnoreply',
            'local_equipment'
        )
    ];

    $settings->add(new admin_setting_configselect(
        'local_equipment/messagesender',
        get_string('messagesender', 'local_equipment'),
        get_string('messagesender_desc', 'local_equipment'),
        ENROL_SEND_EMAIL_FROM_NOREPLY,  // default value
        $options
    ));

    $ADMIN->add(
        $component,
        $settings
    );

    // Virtual course consent (vcc) submissions should not be limited to managers. All users will have access to this page for now.
    // $ADMIN->add(
    //     "{$component}_vccsubmission_cat",
    //     new admin_externalpage(
    //         "{$component}_vccsubmission",
    //         new lang_string('viewmanagevccsubmission', 'local_equipment'),
    //         new moodle_url('/local/equipment/virtualcourseconsent/index.php'),
    //     )
    // );

    $ADMIN->add('server', new admin_externalpage(
        "{$component}_testoutgoingtextconf",
        new lang_string('testoutgoingtextconf', 'local_equipment'),
        new moodle_url('/local/equipment/phonecommunication/testoutgoingtextconf.php'),
        'moodle/site:config',
        true
    ));
    $ADMIN->add('server', new admin_externalpage(
        'local_equipment_verifytestotp',
        new lang_string('verifytestotp', 'local_equipment'),
        new moodle_url('/local/equipment/phonecommunication/verifytestotp.php'),
        'moodle/site:config',
        true
    ));

    $ADMIN->add('server', new admin_category('local_equipment_phone', new lang_string('phone', 'local_equipment')));
    $settingspage = new admin_settingpage('local_equipment_managetoolphoneverification', new lang_string('phoneproviderconfiguration', 'local_equipment'));

    if ($ADMIN->fulltree) {

        // Infobip
        $link = html_writer::link('https://portal.infobip.com/', get_string('here', 'local_equipment'));
        $settingspage->add(new admin_setting_heading(
            'local_equipment_infobip',
            new lang_string('infobip', 'local_equipment'),
            new lang_string('infobip_desc', 'local_equipment', $link)
        ));
        $settingspage->add(new admin_setting_configpasswordunmask(
            'local_equipment/infobipapikey',
            new lang_string('infobipapikey', 'local_equipment'),
            new lang_string('infobipapikey_desc', 'local_equipment'),
            '',
        ));
        $settingspage->add(new admin_setting_configtext(
            'local_equipment/infobipapibaseurl',
            new lang_string('infobipapibaseurl', 'local_equipment'),
            new lang_string('infobipapibaseurl_desc', 'local_equipment'),
            '',
            PARAM_URL
        ));

        // // Twilio
        $link = html_writer::link('https://www.twilio.com/', get_string('here', 'local_equipment'));
        $settingspage->add(new admin_setting_heading(
            'local_equipment_twilio',
            new lang_string('twilio', 'local_equipment'),
            new lang_string('twilio_desc', 'local_equipment', $link)
        ));
        $settingspage->add(new admin_setting_configtext(
            'local_equipment/twilioaccountsid',
            new lang_string('twilioaccountsid', 'local_equipment'),
            new lang_string('twilioaccountsid_desc', 'local_equipment'),
            '',
            PARAM_TEXT
            // '/^[a-f0-9]{32}-[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/',
            // 69
        ));
        $settingspage->add(new admin_setting_configtext(
            'local_equipment/twilioauthtoken',
            new lang_string('twilioauthtoken', 'local_equipment'),
            new lang_string('twilioauthtoken_desc', 'local_equipment'),
            '',
            PARAM_URL
        ));
        $settingspage->add(new admin_setting_configtext(
            'local_equipment/twilionumber',
            new lang_string('twilionumber', 'local_equipment'),
            new lang_string('twilionumber_desc', 'local_equipment'),
            '',
            PARAM_TEXT
        ));

        // // AWS End User Messaging
        // $link = html_writer::link('https://console.aws.amazon.com/sms-voice/', get_string('here', 'local_equipment'));
        // $settingspage->add(new admin_setting_heading(
        //     'local_equipment_awssmsvoice',
        //     new lang_string('awssmsvoice', 'local_equipment'),
        //     new lang_string('awssmsvoice_desc', 'local_equipment', $link)
        // ));
        // $settingspage->add(new admin_setting_configtext(
        //     'local_equipment/awssmsvoiceaccesskey',
        //     new lang_string('awssmsvoiceaccesskey', 'local_equipment'),
        //     new lang_string('awssmsvoiceaccesskey_desc', 'local_equipment'),
        //     '',
        //     PARAM_TEXT,
        //     69
        // ));
        // $settingspage->add(new admin_setting_configtext(
        //     'local_equipment/awssmsvoicesecretkey',
        //     new lang_string('awssmsvoicesecretkey', 'local_equipment'),
        //     new lang_string('awssmsvoicesecretkey_desc', 'local_equipment'),
        //     '',
        //     PARAM_TEXT,
        //     69
        // ));
        // $settingspage->add(new admin_setting_configtext(
        //     'local_equipment/awssmsvoiceregion',
        //     new lang_string('awssmsvoiceregion', 'local_equipment'),
        //     new lang_string('awssmsvoiceregion_desc', 'local_equipment'),
        //     '',
        //     PARAM_TEXT,
        //     69
        // ));

        // // AWS SNS
        // $link = html_writer::link('https://aws.amazon.com/sns/', get_string('here', 'local_equipment'));
        // $settingspage->add(new admin_setting_heading(
        //     'local_equipment_awssns',
        //     new lang_string('awssns', 'local_equipment'),
        //     new lang_string('awssns_desc', 'local_equipment', $link)
        // ));
        // $settingspage->add(new admin_setting_configtext(
        //     'local_equipment/awssnsaccesskey',
        //     new lang_string('awssnsaccesskey', 'local_equipment'),
        //     new lang_string('awssnsaccesskey_desc', 'local_equipment'),
        //     '',
        //     PARAM_TEXT,
        //     69
        // ));
        // $settingspage->add(new admin_setting_configtext(
        //     'local_equipment/awssnssecretkey',
        //     new lang_string('awssnssecretkey', 'local_equipment'),
        //     new lang_string('awssnssecretkey_desc', 'local_equipment'),
        //     '',
        //     PARAM_TEXT,
        //     69
        // ));
        // $settingspage->add(new admin_setting_configtext(
        //     'local_equipment/awssnsregion',
        //     new lang_string('awssnsregion', 'local_equipment'),
        //     new lang_string('awssnsregion_desc', 'local_equipment'),
        //     '',
        //     PARAM_TEXT,
        //     69
        // ));

        // Test outgoing text configuration.
        $url = new moodle_url('/local/equipment/phonecommunication/testoutgoingtextconf.php');
        $link = html_writer::link($url, get_string('testoutgoingtextconf', 'local_equipment'));
        $settingspage->add(new admin_setting_heading(
            'local_equipment_testoutgoingtextconf',
            new lang_string('testoutgoingtextconf', 'local_equipment'),
            new lang_string('testoutgoingtextdetail', 'local_equipment', $link)
        ));

        // Verify OTP.
        $url = new moodle_url('/local/equipment/phonecommunication/verifytestotp.php');
        $link = html_writer::link($url, get_string('verifytestotp', 'local_equipment'));
        $settingspage->add(new admin_setting_heading(
            'local_equipment_verifytestotp',
            new lang_string('verifytestotp', 'local_equipment'),
            new lang_string('verifytestotpdetail', 'local_equipment', $link)
        ));
    }

    $ADMIN->add('local_equipment_phone', $settingspage);
}
