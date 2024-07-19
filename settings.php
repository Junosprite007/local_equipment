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
    $settingspage = new admin_settingpage('manageequipmentcheckout', new lang_string('manageequipmentcheckout', 'local_equipment'));

    // $ADMIN->add('local_equipmentcategory', new admin_externalpage(
    //     'local_equipment',
    //     get_string('pluginname', 'local_equipment'),
    //     new moodle_url('/local/equipment/index.php')
    // ));
    if ($ADMIN->fulltree) {
    }
    // $ADMIN->add('local_equipmentcategory', new admin_externalpage(
    //     'equipment',
    //     new lang_string('equipmentcheckout', 'local_equipment'),
    //     new moodle_url('/admin/equipmentcheckout.php'),
    //     'moodle/site:config',
    //     true
    // ));

    $ADMIN->add('equipment', $settingspage);
}
