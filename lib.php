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
 * Library of functions and constants for the Equipment checkout module.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Callback function to add CSS to the page.
 *
 * @param \core\hook\output\before_standard_head_html_generation $hook
 */
function local_equipment_before_standard_head_html_generation(\core\hook\output\before_standard_head_html_generation $hook) {
    global $PAGE;

    $PAGE->requires->css('/local/equipment/styles.css');
}

// function local_equipment_add_instance($data) {
//     global $DB;

//     $data->timecreated = time();
//     $data->timemodified = $data->timecreated;

//     $id = $DB->insert_record('equipmentcheckout', $data);

//     return $id;
// }

// function local_equipment_update_instance($data) {
//     global $DB;

//     $data->timemodified = time();
//     $data->id = $data->instance;

//     $DB->update_record('equipmentcheckout', $data);

//     return true;
// }

// function local_equipment_delete_instance($id) {
//     global $DB;

//     $DB->delete_records('equipmentcheckout', array('id' => $id));

//     return true;
// }

// function local_equipment_extend_settings_navigation($settingsnav, $context) {
//     global $CFG, $PAGE;

//     // Only add this settings item on non-site course pages.
//     if (!$PAGE->course or $PAGE->course->id == 1) {
//         return;
//     }

//     // Only let users with the appropriate capability see this settings item.
//     if (!has_capability('moodle/course:update', context_course::instance($PAGE->course->id))) {
//         return;
//     }

//     if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
//         $strfoo = get_string('pluginname', 'local_equipment');
//         $url = new moodle_url('/local/equipment/view.php', array('id' => $PAGE->cm->id));
//         $foonode = navigation_node::create(
//             $strfoo,
//             $url,
//             navigation_node::NODETYPE_LEAF,
//             'local_equipment',
//             'local_equipment',
//             new pix_icon('icon', $strfoo, 'local_equipment')
//         );
//         if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
//             $foonode->make_active();
//         }
//         $settingnode->add_node($foonode);
//     }
// }

// function local_equipment_extend_navigation(global_navigation $navigation) {
//     global $CFG;

//     // Check if the user has the capability to see the Partnerships tab
//     $context = context_system::instance();
//     if (has_capability('moodle/site:config', $context)) {
//         $partnerships = $navigation->add(
//             get_string('partnerships', 'local_equipment'),
//             new moodle_url('/local/equipment/partnerships.php'),
//             navigation_node::TYPE_SITE_ADMIN,
//             null,
//             'partnerships'
//         );

//         $partnerships->add(
//             get_string('partnershipsettings', 'local_equipment'),
//             new moodle_url('/local/equipment/partnerships.php'),
//             navigation_node::TYPE_SETTING,
//             null,
//             'partnershipsettings'
//         );

//         $partnerships->add(
//             get_string('managepartnerships', 'local_equipment'),
//             new moodle_url('/local/equipment/manage_partnerships.php'),
//             navigation_node::TYPE_SETTING,
//             null,
//             'managepartnerships'
//         );
//     }
// }

// function local_equipment_extend_navigation_category_settings(navigation_node $parentnode, context $context) {
//     global $CFG;

//     if (has_capability('moodle/site:config', $context)) {
//         $parentnode->add(
//             get_string('partnerships', 'local_equipment'),
//             new moodle_url('/admin/category.php?category=partnerships'),
//             navigation_node::TYPE_CONTAINER,
//             null,
//             'partnerships'
//         );
//     }
// }
