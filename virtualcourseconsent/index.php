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
 * Virtual course consent form submission page that users (parents) will
 * be filling out and submitting.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/equipment/classes/form/vccsubmission_form.php');

require_login();

$PAGE->set_url(new moodle_url('/local/equipment/virtualcourseconsent/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('consentformtitle', 'local_equipment'));
$PAGE->set_heading(get_string('consentformheading', 'local_equipment'));
$PAGE->requires->js_call_amd('local_equipment/vccsubmission_addstudents_form', 'init');
// $PAGE->requires->js_call_amd('local_equipment/vccsubmission_form', 'init');
// $PAGE->requires->js_call_amd('local_equipment/formhandling', 'setupStudentsHandling', ['student', 'header']);
// // $PAGE->requires->js_call_amd('local_equipment/formhandling', 'collapseNewStudentFieldset');
// $PAGE->requires->js_call_amd('local_equipment/partnership_courses', 'init');
// $PAGE->requires->js_call_amd('local_equipment/pickup_times', 'init');


// In the script where you handle the form submission and display
$selectedcourses = optional_param('selectedcourses', '', PARAM_RAW);
$customdata = ['selectedcourses' => $selectedcourses];
echo '<br />';
echo '<br />';
echo '<br />';
// echo '<pre>';
// var_dump('$selectedcourses: ');
// var_dump($customdata);
// die();

// $courses = local_equipment_get_master_courses(); // Function to get available courses.
// $selectedcourses = get_user_selected_courses($USER->id); // Function to get selected courses for the user.
// $customdata = [
//     'courses' => $courses,
//     'defaultvalues' => [
//         'selectedcourses' => $selectedcourses,
//     ],
// ];

$mform = new \local_equipment\form\vccsubmission_form(null, $customdata);
// $mform = new \local_equipment\form\vccsubmission_form();



if ($mform->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($data = $mform->get_data()) {
    echo '<br />';
    echo '<br />';
    echo '<br />';
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();

    if (local_equipment_save_vcc_form($data)) {
        // var_dump('if');
        redirect(new moodle_url('/'), get_string('consentformsubmitted', 'local_equipment'), null, \core\output\notification::NOTIFY_SUCCESS);

    } else {
        // var_dump('else');
        redirect(new moodle_url('/local/equipment/virtualcourseconsent/index.php'), get_string('consentformsubmissionerror', 'local_equipment'), null, \core\output\notification::NOTIFY_ERROR);
    }
    redirect(
        new moodle_url('/'),
        get_string('consentformsubmitted', 'local_equipment'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}

