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
    // Get the JSON content of the 'value' attribute from the hidden #id_familiesdata input element.
    $familiesdata = json_decode($data->familiesdata);
    $created_users = [];
    $existing_users = [];

    // This array will be filled with the final data to be saved to the DB.
    $families = [];

    $roleid_parent = $DB->get_field('role', 'id', ['shortname' => 'parent']);
    $roleid_student = $DB->get_field('role', 'id', ['shortname' => 'student']);

    try {
        // Start processing the families.
        foreach ($familiesdata as $familydata) {
            $family = new stdClass();
            $parents = [];
            $students = [];
            $allcourses = [];
            $allstudentsofallparents = [];
            $messages = new stdClass();
            $messages->success = [];
            $messages->error = [];

            // First pass of the parents to get all their current students
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

                // We'll need to get all the usernames that are the same, then append the next sequential number to the end of the
                // new username below.
                $parent->username = local_equipment_generate_username($parent);
                $parent->username = clean_param($parent->username, PARAM_USERNAME);

                $user = $DB->get_record('user', ['email' => $parent->email]);

                // If the parent doesn't exist based on their email, we'll create a new user. If the do exist, we'll override the
                // $parent user we made above to the matching user found in the DB.
                if (!$user) {
                    // Add an entirely new parent user.
                    $userid = user_create_user($parent, false, false);
                } else {
                    // Update an existing parent user while keeping the previously created parent object as $parent_old before overriding it, just in case. Not sure if
                    // we'll actually need it, though.
                    $parent_old = $parent;
                    $parent = $user;
                }
                if ($userid !== null) {
                    $parent->id = $userid;
                    $created_users[] = $parent;
                } else {
                    $existing_users[] = $parent;
                }
                // By the end of the last iteration of this parent loop, $allstudentsofallparents should contain all students with
                // unique IDs that have these parents already assigned to them. This is used later in this file.
                $allstudentsofallparents = $allstudentsofallparents + local_equipment_get_students_of_parent($parent->id);
                $parents[] = $parent;
            }

            // By this point, we should have all the parents in the current family, as well as any existing students they have, so
            // now it's time to start processing the students listed in the form by the admin user.
            foreach ($familydata->students as $s) {
                $userid = null;
                $student = new stdClass();
                // Below, $s->student is basically the same as $p->name above; it just gets the student's name. At the time, I
                // needed a different word 'name' when it came to the student, so I used 'student' instead. This can make things a
                // little confusing, so sorry about that.
                $student->firstname = clean_param($s->student->data->firstName, PARAM_TEXT);
                $student->middlename = clean_param($s->student->data->middleName ?? '', PARAM_TEXT);
                $student->lastname = clean_param($s->student->data->lastName, PARAM_TEXT);
                $student->auth = 'manual';
                $student->confirmed = 1;
                $student->mnethostid = $CFG->mnet_localhost_id;
                $student->lang = $USER->lang ?? $CFG->lang ?? 'en';
                $student->password = generate_password(6);
                $student->phone2 = clean_param($s->phone->data ?? '', PARAM_TEXT);
                $student->firstnamephonetic = '';
                $student->lastnamephonetic = '';
                $student->alternatename = '';

                if (isset($s->email->data)) {
                    $student->email = $s->email->data;
                } else {
                    $student->email = local_equipment_generate_student_email($parents[0]->email, $student->firstname);
                }

                $student->email = clean_param($student->email, PARAM_EMAIL);

                // Because of the way this works, we'll need to add users one-by-one before proceeding onto the next student (the
                // next iteration of this loop). That will ensure students get assigned unique usernames.
                $student->username = local_equipment_generate_username($student);

                // Get any student record that matches the current student's email. If a match is found, we'll update the current
                // student to match the user found in the DB. IMPORTANTLY, if no users match the provived (or generated) email, we
                // first need to check if the first and last name of the student in this iteration of the for loop matches the first
                // and last name of any student in the $allstudentsofallparents array. If a match is found by name, we'll assume
                // it's the same student. This is to prevent creating duplicate students, though, it poses a problem with a
                // potential edge case where two children of the same parent have the same first and last name.

                // Maybe the parents adopted a kid who happened to have the same name as their biological child, or maybe two
                // parents remarried and happen to have children with the exact same names. In these unique cases, the admin user
                // will either need to enter the email that already exists in the system for each of the students in question, or
                // they can just enter one of the students and then manually create the other student. I feel like this must be an
                // extremely rare case, but I wanted to mention it in case anyone has a solution to such an edge case. I mean,
                // manually entering a version of the generated email, like parent1+child1@example.com and
                // parent1+child2@example.com for each student is a solution, so admins can just do that I guess.
                $user = $DB->get_record('user', ['email' => $student->email]);
                if (!$user) {
                    foreach ($allstudentsofallparents as $sofp) {
                        if (strcasecmp($student->firstname, $sofp->firstname) === 0 && strcasecmp($student->lastname, $sofp->lastname) === 0) {
                            $user = $DB->get_record('user', ['email' => $sofp->email]);
                            $student = $user;
                            break;
                        }
                    }
                    if (!$user) {
                        $userid = user_create_user($student);
                    }
                } else {
                    // Update an existing student user to the matched user. I can't think of anything that needs to be updated at
                    // the moment.
                    $student = $user;
                }

                if ($userid !== null) {
                    $student->id = $userid;
                    $created_users[] = $student;
                } else {
                    $existing_users[] = $student;
                }

                $student->courses = $s->courses->data;


                foreach ($student->courses as $c) {
                    // Enroll the student into each course.
                    $student->courses_results[$c] = local_equipment_enrol_user_in_course($student, $c, $roleid_student);
                }
                $allcourses = array_merge($allcourses, $student->courses);

                // Assign each parent to the current student.
                foreach ($parents as $p) {
                    $userassigned = local_equipment_assign_role_relative_to_user($student, $p, 'parent');
                }
                $students[] = $student;
                // die();
            }

            // Fill the family array with the parents, students, partnership, and unique courses (each student in the $students
            // array already contains a list of their individual courses).
            $family->parents = $parents;
            $family->students = $students;
            $family->partnership = $familydata->partnership->data ?? '';
            $family->all_courses = array_unique($allcourses);

            // Enroll all the parents into each course with the role of "parent". This is so they can see the grades of their
            // students, as well as the courses in which their students are enrolled.
            foreach ($family->parents as $p) {
                foreach ($family->all_courses as $c) {
                    $p->courses_results[$c] = local_equipment_enrol_user_in_course(
                        $p,
                        $c,
                        $roleid_parent
                    );
                }
            }

            // Poopulate the $families array with the current family. HAH. Poop...
            $families[] = $family;
        }

        $allusers = array_merge($created_users, $existing_users);

        // For development purposes, we can delete all the users we just created if we want.
        // var_dump('Deleting users...');
        // foreach ($allusers as $u) {
        //     user_delete_user($u);
        // }
        // die();

    } catch (moodle_exception $e) {
        // Errors will be caught here. In general, we'll need to be displaying success and non-fatal warning messages to the admin
        // user as the form is processing. Even if there is an error, the script should continue to process the rest of the family
        // in question as well as the rest of the families in the form.
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadresults', 'local_equipment'));

    // We should display the results/notifications here.

    $form->display();
    echo $OUTPUT->footer();
} else {
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}
