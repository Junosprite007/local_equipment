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
 * Upload and enroll multiple families (parents and their students).
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// File location: local/equipment/addbulkfamilies.php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/equipment/classes/form/addbulkfamilies_form.php');
require_once($CFG->dirroot . '/local/equipment/lib.php');
require_once($CFG->dirroot . '/user/lib.php');

require_login();
require_capability('moodle/user:create', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/equipment/addbulkfamilies.php'));
$PAGE->set_title(get_string('bulkfamilyupload', 'local_equipment'));
$PAGE->set_heading(get_string('bulkfamilyupload', 'local_equipment'));
$PAGE->requires->js_call_amd('local_equipment/bulkfamilyupload', 'init');
$PAGE->requires->js_call_amd('local_equipment/editpartnership_form', 'init');

$form = new addbulkfamilies_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($data = $form->get_data()) {
    $familiesdata = json_decode($data->familiesdata);
    $families = [];
    $created_users = [];
    $failed_users = [];
    $userid = null;

    foreach ($familiesdata as $familydata) {
        $family = new stdClass();
        $parents = [];
        $students = [];
        $allcourses = [];

        foreach ($familydata->parents as $p) {
            $parent = new stdClass();
            $parent->firstname = clean_param($p->name->data->firstName, PARAM_TEXT);
            $parent->middlename = clean_param($p->name->data->middleName ?? '', PARAM_TEXT);
            $parent->lastname = clean_param($p->name->data->lastName, PARAM_TEXT);
            $parent->auth = 'manual';
            $parent->confirmed = 1;
            $parent->mnethostid = $CFG->mnet_localhost_id;
            $parent->lang = $USER->lang ?? $CFG->lang ?? 'en';
            $parent->email = clean_param($p->email->data, PARAM_EMAIL);
            $parent->phone2 = clean_param($p->phone->data ?? '', PARAM_TEXT);
            $parent->password = generate_password(6);

            // We'll need to get all the usernames that are the same, then append the next sequential number to the end of the new username below.
            $parent->username = local_equipment_generate_username($parent);
            $parent->username = clean_param($parent->username, PARAM_USERNAME);

            // Add the parent user.
            $parents[] = $parent;
        }

        foreach ($familydata->students as $s) {
            $student = new stdClass();
            // Below, $s->student is basically the same as $p->name above; it just gets the student's name.
            $student->firstname = clean_param($s->student->data->firstName, PARAM_TEXT);
            $student->middlename = clean_param($s->student->data->middleName ?? '', PARAM_TEXT);
            $student->lastname = clean_param($s->student->data->lastName, PARAM_TEXT);
            $student->auth = 'manual';
            $student->confirmed = 1;
            $student->mnethostid = $CFG->mnet_localhost_id;
            $student->lang = $USER->lang ?? $CFG->lang ?? 'en';
            $student->password = generate_password(6);

            $student->phone2 = clean_param($s->phone->data ?? '', PARAM_TEXT);
            $student->courses = $s->courses->data;

            if (isset($s->email->data)) {
                $student->email = $s->email->data;
            } else {
                $student->email = local_equipment_generate_student_email($parents[0]->email, $student->firstname);
            }

            $student->email = clean_param($student->email, PARAM_EMAIL);

            // Because of the way this works, we'll need to add users one-by-one before proceeding onto the next student. That will ensure students get assigned unique usernames.
            $student->username = local_equipment_generate_username($student);

            try {
                $user_student = $DB->get_record('user', ['username' => $student->username]);

                if (!$user_student) {
                    $studentid = user_create_user($student);
                } else {
                }
                // echo '<br />';
                // echo '<br />';
                // echo '<pre>';
                // var_dump($user);
                // echo '</pre>';
                // die();
                if ($studentid) {
                    $student->id = $studentid;
                    $created_users[] = $student;
                    user_delete_user($student);
                } else {
                    $failed_users[] = $student;
                }
            } catch (moodle_exception $e) {
                // Errors will be caught here.
            }

            $allcourses = array_merge($allcourses, $student->courses);
            $students[] = $student;
        }

        // Fill the family array.
        $family->parents = $parents;
        $family->students = $students;
        $family->partnership = $familydata->partnership->data ?? '';
        $family->all_courses = array_unique($allcourses);
        $families[] = $family;
    }

    // $familydata = $data->familydata;

    // Display results
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadresults', 'local_equipment'));
    echo $OUTPUT->footer();
} else {
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}
