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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Default page for the Equipment checkout module.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

// $id = required_param('id', PARAM_INT);

// $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
// require_course_login($course);
// $PAGE->requires->js_call_amd('local_equipment/helloworld', 'init');
$strequipmentcheckouts = get_string('pluginname', 'local_equipment');
// $PAGE->set_pagelayout('incourse');
// $PAGE->set_url('/local/equipment/index.php', array('id' => $id));
$PAGE->set_url('/local/equipment/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title($strequipmentcheckouts);
$PAGE->set_heading($strequipmentcheckouts);
$PAGE->navbar->add($strequipmentcheckouts);
// $PAGE->requires->js('/local/equipment/lib/amd/src/helloworld.js');
// $PAGE->requires->js('/local/equipment/amd/src/helloworld.js');

echo $OUTPUT->header();
echo $OUTPUT->heading($strequipmentcheckouts);


// This works!
$PAGE->requires->js_call_amd('local_equipment/addpartnership_form', 'showAlert', ['Message', 'Hello all you people! I\'m self-executing.']);
echo $OUTPUT->footer();
