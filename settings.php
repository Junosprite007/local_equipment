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

$ADMIN->add('modules', new admin_category(
    'local_equipment',
    new lang_string('equipment', 'local_equipment')
));

$settings = new admin_settingpage(
    'local_equipment_settings',
    new lang_string('pluginsettings', 'local_equipment')
);
$ADMIN->add('local_equipment', $settings);

// Add link to manage partnerships page
$externalpage = new admin_externalpage(
    'local_equipment_partnerships',
    new lang_string('partnerships', 'local_equipment'),
    new moodle_url('/local/equipment/partnerships.php')
);
$ADMIN->add('local_equipment', $externalpage);

// Add link to add partnership page
$externalpage = new admin_externalpage(
    'local_equipment_addpartnership',
    new lang_string(
        'addpartnership',
        'local_equipment'
    ),
    new moodle_url('/local/equipment/partnerships/addpartnership.php')
);
$ADMIN->add('local_equipment', $externalpage);

// We don't need to add a separate menu item for edit partnership,
// as it will be accessed from the manage partnerships page

// $externalpage = new admin_externalpage(
//     'manageequipment',
//     new lang_string('manageequipment', 'local_equipment'),
//     new moodle_url('/local/equipment/manageequipment.php'),
//     'moodle/site:config'
// );
// $ADMIN->add('equipment', $externalpage);

// $externalpage = new admin_externalpage(
//     'managepickuptimes',
//     new lang_string('managekitpickuptimes', 'local_equipment'),
//     new moodle_url('/local/equipment/managepickuptimes.php'),
//     'moodle/site:config'
// );
// $ADMIN->add('equipment', $externalpage);

// $externalpage = new admin_externalpage(
//     'manageagreements',
//     new lang_string('manageagreements', 'local_equipment'),
//     new moodle_url('/local/equipment/manageagreements.php'),
//     'moodle/site:config'
// );
// $ADMIN->add('equipment', $externalpage);

// $externalpage = new admin_externalpage(
//     'viewformsubmissions',
//     new lang_string('viewformsubmissions', 'local_equipment'),
//     new moodle_url('/local/equipment/viewformsubmissions.php'),
//     'moodle/site:config'
// );
// $ADMIN->add('equipment', $externalpage);

// if ($ADMIN->fulltree) {
// }

// $ADMIN->add('localplugins', new admin_category('local_equipment_settings', get_string('pluginname', 'local_equipment')));

// Add any global settings here

// Define capabilities in access.php.

$settings = null; // We do not want standard settings link.
$externalpage = null;
