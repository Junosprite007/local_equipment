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
    $ADMIN->add('modules', new admin_category(
        'equipment',
        new lang_string('equipment', 'local_equipment')
    ));
    // $settingspage = new admin_settingpage(
    //     'manageequipment',
    //     new lang_string('manageequipment', 'local_equipment')
    // );
    $externalpage = new admin_externalpage(
        'manageequipment',
        new lang_string('manageequipment', 'local_equipment'),
        new moodle_url('/local/equipment/manageequipment.php'),
        'moodle/site:config'
    );
    $ADMIN->add('equipment', $externalpage);

    $externalpage = new admin_externalpage(
        'managepartnerships',
        new lang_string('managepartnerships', 'local_equipment'),
        new moodle_url('/local/equipment/partnerships/managepartnerships.php'),
        'moodle/site:config'
    );
    $ADMIN->add('equipment', $externalpage);

    $externalpage = new admin_externalpage(
        'managepickuptimes',
        new lang_string('managekitpickuptimes', 'local_equipment'),
        new moodle_url('/local/equipment/managepickuptimes.php'),
        'moodle/site:config'
    );
    $ADMIN->add('equipment', $externalpage);

    $externalpage = new admin_externalpage(
        'manageagreements',
        new lang_string('manageagreements', 'local_equipment'),
        new moodle_url('/local/equipment/manageagreements.php'),
        'moodle/site:config'
    );
    $ADMIN->add('equipment', $externalpage);

    $externalpage = new admin_externalpage(
        'viewformsubmissions',
        new lang_string('viewformsubmissions', 'local_equipment'),
        new moodle_url('/local/equipment/viewformsubmissions.php'),
        'moodle/site:config'
    );
    $ADMIN->add('equipment', $externalpage);

    if ($ADMIN->fulltree) {
    }

    // $ADMIN->add('equipment', $settingspage);











    // Fix this before moving on
    $ADMIN->add('localplugins', new admin_category('local_equipment_settings', get_string('pluginname', 'local_equipment')));
    $settings = new admin_settingpage('local_equipment', get_string('pluginsettings', 'local_equipment'));

    // Add any global settings here
    // $settings->add(new admin_setting_configtext(...));

    $ADMIN->add('local_equipment_settings', $settings);

    // Add link to manage partnerships page
    $ADMIN->add('local_equipment_settings', new admin_externalpage(
        'local_equipment_managepartnerships',
        get_string('managepartnerships', 'local_equipment'),
        new moodle_url('/local/equipment/partnerships/managepartnerships.php')
    ));

    // Add link to add partnership page
    $ADMIN->add('local_equipment_settings', new admin_externalpage(
        'local_equipment_addpartnership',
        get_string('addpartnership', 'local_equipment'),
        new moodle_url('/local/equipment/partnerships/addpartnership.php')
    ));

    // We don't need to add a separate menu item for edit partnership,
    // as it will be accessed from the manage partnerships page
}

// Define capabilities
$capabilities = array(
    'local/equipment:managepartnerships' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
);
