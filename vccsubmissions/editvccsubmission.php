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
 * Edit VCC submission page.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/equipment/classes/form/editvccsubmission_form.php');

global $DB;

$id = required_param('id', PARAM_INT); // VCC submission ID
require_login();

$context = \core\context\system::instance();
$url = new moodle_url('/local/equipment/vccsubmissions/editvccsubmission.php', ['id' => $id]);
$redirecturl = new moodle_url('/local/equipment/vccsubmissions.php');

// Set up the page.
$PAGE->set_context($context);
$PAGE->set_url($url);
// $id = optional_param('id', 0, PARAM_INT);
// $PAGE->url->param('id', $id);
$PAGE->set_title(get_string('editvccsubmission', 'local_equipment'));
$PAGE->set_heading(get_string('editvccsubmission', 'local_equipment'));
$PAGE->requires->js_call_amd('local_equipment/vccsubmission_addstudents_form', 'init');
$PAGE->requires->js_call_amd('local_equipment/formhandling', 'setupFieldsetNameUpdates', ['student', 'header']);
$PAGE->requires->js_call_amd('local_equipment/formhandling', 'setupMultiSelects');
$PAGE->requires->js_call_amd('local_equipment/formhandling', 'updateURLId');

require_capability('local/equipment:managevccsubmissions', $context);

// Fetch existing vccsubmission data.
$vccsubmission = $DB->get_record('local_equipment_vccsubmission', ['id' => $id], '*', MUST_EXIST);

$studentids = json_decode($vccsubmission->studentids);
foreach ($studentids as $studentid) {
    $student = $DB->get_record('local_equipment_vccsubmission_student', ['id' => $studentid], '*', MUST_EXIST);
    $firstnames[] = $student->firstname;
    $lastnames[] = $student->lastname;
    $emails[] = $student->email;
    $dobs[] = $student->dateofbirth;
    $courses[] = json_decode($student->courseids);
}

$studentdata = [
    'student_firstname' => $firstnames,
    'student_lastname' => $lastnames,
    'student_email' => $emails,
    'student_dob' => $dobs,
    'student_courses' => $courses
];

// Initialize the form.
$mform = new local_equipment\form\editvccsubmission_form($url, ['id' => $id, 'data' => $vccsubmission, 'studentdata' => $studentdata]);

if ($mform->is_cancelled()) {
    redirect($redirecturl);
} else if ($data = $mform->get_data()) {
    // Update the vccsubmission in the database.
    echo '<br />';
    echo '<br />';
    echo '<br />';
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
    $vccsubmission = $data;
    $vccsubmission->id = $data->vccsubmissionid;
    // $vccsubmission->name = $data->name;
    // $vccsubmission->liaisonids = json_encode(local_equipment_convert_array_values_to_int($data->liaisons));
    // $vccsubmission->courseids = json_encode(local_equipment_convert_array_values_to_int($data->courses));
    // $vccsubmission->active = $data->active;

    // Mailing address specific fields.
    // if ($vccsubmission->mailing_sameasphysical) {
    //     $vccsubmission->mailing_streetaddress = $vccsubmission->physical_streetaddress;
    //     $vccsubmission->mailing_city = $vccsubmission->physical_city;
    //     $vccsubmission->mailing_state = $vccsubmission->physical_state;
    //     $vccsubmission->mailing_country = $vccsubmission->physical_country;
    //     $vccsubmission->mailing_zipcode = $vccsubmission->physical_zipcode;
    // }

    // // Pickup address specific fields.
    // if ($vccsubmission->pickup_sameasphysical) {
    //     $vccsubmission->pickup_streetaddress = $vccsubmission->physical_streetaddress;
    //     $vccsubmission->pickup_city = $vccsubmission->physical_city;
    //     $vccsubmission->pickup_state = $vccsubmission->physical_state;
    //     $vccsubmission->pickup_country = $vccsubmission->physical_country;
    //     $vccsubmission->pickup_zipcode = $vccsubmission->physical_zipcode;
    // }

    // // Billing address specific fields.
    // if ($vccsubmission->billing_sameasphysical) {
    //     $vccsubmission->billing_streetaddress = $vccsubmission->physical_streetaddress;
    //     $vccsubmission->billing_city = $vccsubmission->physical_city;
    //     $vccsubmission->billing_state = $vccsubmission->physical_state;
    //     $vccsubmission->billing_country = $vccsubmission->physical_country;
    //     $vccsubmission->billing_zipcode = $vccsubmission->physical_zipcode;
    // }

    $DB->update_record('local_equipment_vccsubmission', $vccsubmission);

    // Redirect to the vccsubmissions page.
    redirect($redirecturl, get_string('vccsubmissionupdated', 'local_equipment'));
}

// Output everything.
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
