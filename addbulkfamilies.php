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

use core\plugininfo\enrol;

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
    $existing_users = [];
    $userid = null;


    $roleid_parent = $DB->get_field('role', 'id', ['shortname' => 'parent']);
    $roleid_student = $DB->get_field('role', 'id', ['shortname' => 'student']);

    try {

    foreach ($familiesdata as $familydata) {
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump($familydata);
        // echo '</pre>';
        // die();
        $family = new stdClass();
        $parents = [];
        $students = [];
        $allcourses = [];
            $allstudentsofallparents = [];

            $messages = new stdClass();
            $messages->success = [];
            $messages->error = [];

        foreach ($familydata->parents as $p) {
                $userid = null;

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

                $parent->firstnamephonetic = '';
                $parent->lastnamephonetic = '';
                $parent->alternatename = '';

            // We'll need to get all the usernames that are the same, then append the next sequential number to the end of the new username below.
            $parent->username = local_equipment_generate_username($parent);
            $parent->username = clean_param($parent->username, PARAM_USERNAME);

                $user = $DB->get_record('user', ['email' => $parent->email]);

                if (!$user) {
                    // Add an entirely new parent user.
                    $userid = user_create_user($parent, false, false);
                } else {
                    $parent->id = $user->id;
                    // Update an existing parent user. I can't think of anything that needs to be updated at the moment.
                }
                if ($userid !== null) {
                    $parent->id = $userid;
                    $created_users[] = $parent;
                    // user_delete_user($parent);
                } else {
                    $existing_users[] = $parent;
                }
                // } catch (moodle_exception $e) {
                //     // Errors will be caught here.
                // }
                $allstudentsofallparents = $allstudentsofallparents + local_equipment_get_students_of_parent($parent->id);

            // Add the parent user.
            $parents[] = $parent;
        }

            // echo '<br />';
            // echo '<br />';
            // echo '<pre>';
            // var_dump('$allstudentsofallparents: ');
            // var_dump($allstudentsofallparents);
            // echo '</pre>';

            // die();

        foreach ($familydata->students as $s) {
                $userid = null;

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

                $student->firstnamephonetic = '';
                $student->lastnamephonetic = '';
                $student->alternatename = '';

            if (isset($s->email->data)) {
                $student->email = $s->email->data;
            } else {
                $student->email = local_equipment_generate_student_email($parents[0]->email, $student->firstname);
            }

            $student->email = clean_param($student->email, PARAM_EMAIL);

            // Because of the way this works, we'll need to add users one-by-one before proceeding onto the next student. That will ensure students get assigned unique usernames.
            $student->username = local_equipment_generate_username($student);

                $user = $DB->get_record('user', ['email' => $student->email]);
                // $mystudents = [];
                if (!$user) {
                    // foreach ($parents as $p) {

                    // }
                    foreach ($allstudentsofallparents as $sofp) {
                        if (strcasecmp($student->firstname, $sofp->firstname) === 0 && strcasecmp($student->lastname, $sofp->lastname) === 0) {
                            $student->id = $sofp->id;
                            $student->username = $sofp->username;
                            $student->email = $sofp->email;
                        } else {
                            $userid = user_create_user($student);
                        }
                    }

                    // Add an entirely new student user.
                } else {
                    $student->id = $user->id;
                    // Update an existing student user. I can't think of anything that needs to be updated at the moment.
                }
                // $user_student = $DB->get_record('user', ['email' => $student->email]);

                // if (!$user_student) {
                //     // Create a new student user.
                //     $studentid = user_create_user($student, false, false);
                //     $user_student = \core_user::get_user($studentid);

                // } else {
                //     // User already exists.
                //     $student->id = $user_student->id;
                //     $user_student = \core_user::get_user($student->id);
                // }

                if ($userid !== null) {
                    $student->id = $userid;
                    $created_users[] = $student;
                } else {
                    $existing_users[] = $student;
                }
                foreach ($student->courses as $c) {
                    // Enroll the student into each course.
                    // var_dump("Not enrolling $student->firstname into $c.");
                    $student->courses_results[$c] = local_equipment_enrol_user_in_course($student, $c, $roleid_student);
                }
                $allcourses = array_merge($allcourses, $student->courses);

                // Add the parent to each student.
                foreach ($parents as $p) {
                    $userassigned = local_equipment_assign_role_relative_to_user($student, $p, 'parent');
                    // parent


                    // $s->parents[] = $p;
                }
                // $assignedusers = local_equipment_get_users_assigned_to_user('parent', $student->id);




                // $responseobject = new stdClass();
                // $responseobject->username
                // enrol();

                $students[] = $student;
            }

            // foreach ($parents as $parent) {
            //     $mystudents = local_equipment_get_students_of_parent($parent->id);
            //     echo '<br />';
            //     echo '<br />';
            //     echo '<br />';
            //     echo '<pre>';
            //     var_dump("All of $parent->firstname's students: ");
            //     var_dump($mystudents);
            //     echo '</pre>';
            // }

            // foreach ($students as $student) {
            //     $myparents = local_equipment_get_parents_of_student($student->id);
            //     echo '<br />';
            //     echo '<br />';
            //     echo '<br />';
            //     echo '<pre>';
            //     var_dump("All of $student->firstname's parents: ");
            //     var_dump($myparents);
            //     echo '</pre>';
            // }

        // Fill the family array.
        $family->parents = $parents;
        $family->students = $students;
        $family->partnership = $familydata->partnership->data ?? '';
        $family->all_courses = array_unique($allcourses);


            // Enroll the parents into each unique course.
            foreach ($family->parents as $p) {
                // $p->mystudents = local_equipment_get_students_of_user_as('parent', $p->id);
                // echo '<br />';
                // echo '<br />';
                // echo '<br />';
                // echo '<pre>';
                // var_dump('My students: ');
                // var_dump($p->mystudents);
                // echo '</pre>';
                // Enroll the parent into each course with the role of "parent".
                foreach ($family->all_courses as $c) {
                    // var_dump("Not enrolling $p->firstname into $c.");
                    $p->courses_results[$c] = local_equipment_enrol_user_in_course(
                        $p,
                        $c,
                        $roleid_parent
                    );
                }
            }








        $families[] = $family;
    }

        // local_equipment_process_family_roles($families);
        // echo '<pre>';
        // var_dump($families);
        // echo '</pre>';
        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump('$created_users: ');
        // var_dump($created_users);
        // echo '</pre>';
        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump('$existing_users: ');
        // var_dump($existing_users);
        // echo '</pre>';



        // foreach ($created_users as $user) {

        // }

        // For development purposes, we'll delete all the users we just created.
        $allusers = array_merge($created_users, $existing_users);
        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // // echo '<pre>';
        // // var_dump('$allusers: ');
        // // var_dump($allusers);
        // // echo '</pre>';
        // var_dump('Deleting users...');
        // foreach ($allusers as $u) {
        //     user_delete_user($u);
        // }
    } catch (moodle_exception $e) {
        // Errors will be caught here.
    }
    die();

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
