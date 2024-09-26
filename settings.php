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

    // Add Pickups sub-settings
    $settings->add(new admin_setting_heading(
        "{$component}/pickupsheading",
        get_string(
            'pickupsheading',
            'local_equipment'
        ),
        get_string('pickupsheading_desc', 'local_equipment')
    ));

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
    $ADMIN->add('local_equipment', new admin_externalpage(
        'local_equipment_vccsubmissions',
        new lang_string('managevccsubmissions', 'local_equipment'),
        new moodle_url('/local/equipment/vccsubmissions.php')
    ));

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
        'local_equipment_testoutgoingtextconf',
        new lang_string('testoutgoingtextconf', 'local_equipment'),
        new moodle_url('/local/equipment/phonecommunication/testoutgoingtextconf.php'),
        'moodle/site:config',
        true
    ));
    $ADMIN->add('server', new admin_externalpage(
        'local_equipment_verifyotp',
        new lang_string('verifyotp', 'local_equipment'),
        new moodle_url('/local/equipment/phonecommunication/verifyotp.php'),
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
        $settingspage->add(new admin_setting_configtext(
            'local_equipment/infobipapikey',
            new lang_string('infobipapikey', 'local_equipment'),
            new lang_string('infobipapikey_desc', 'local_equipment'),
            '',
            PARAM_TEXT,
            69
        ));
        $settingspage->add(new admin_setting_configtext(
            'local_equipment/infobipapibaseurl',
            new lang_string('infobipapibaseurl', 'local_equipment'),
            new lang_string('infobipapibaseurl_desc', 'local_equipment'),
            '',
            PARAM_URL
        ));

        // // Twilio
        // $link = html_writer::link('https://www.twilio.com/', get_string('here', 'local_equipment'));
        // $settingspage->add(new admin_setting_heading(
        //     'local_equipment_twilio',
        //     new lang_string('twilio', 'local_equipment'),
        //     new lang_string('twilio_desc', 'local_equipment', $link)
        // ));
        // $settingspage->add(new admin_setting_configtext(
        //     'local_equipment/twilioaccountsid',
        //     new lang_string('twilioaccountsid', 'local_equipment'),
        //     new lang_string('twilioaccountsid_desc', 'local_equipment'),
        //     '',
        //     PARAM_TEXT
        //     // '/^[a-f0-9]{32}-[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/',
        //     // 69
        // ));
        // $settingspage->add(new admin_setting_configtext(
        //     'local_equipment/twilioauthtoken',
        //     new lang_string('twilioauthtoken', 'local_equipment'),
        //     new lang_string('twilioauthtoken_desc', 'local_equipment'),
        //     '',
        //     PARAM_URL
        // ));
        // $settingspage->add(new admin_setting_configtext(
        //     'local_equipment/twilionumber',
        //     new lang_string('twilionumber', 'local_equipment'),
        //     new lang_string('twilionumber_desc', 'local_equipment'),
        //     '',
        //     PARAM_TEXT
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
        $url = new moodle_url('/local/equipment/phonecommunication/verifyotp.php');
        $link = html_writer::link($url, get_string('verifyotp', 'local_equipment'));
        $settingspage->add(new admin_setting_heading(
            'local_equipment_verifyotp',
            new lang_string('verifyotp', 'local_equipment'),
            new lang_string('verifyotpdetail', 'local_equipment', $link)
        ));
    }

    $ADMIN->add('local_equipment_phone', $settingspage);
}
