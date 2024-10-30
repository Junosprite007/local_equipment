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

$form = new addbulkfamilies_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($data = $form->get_data()) {
    echo '<br />';
    echo '<br />';
    echo '<br />';
    echo 'Timestamp: ' . time();

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

            // echo '<br />';
            // echo '<br />';
            // echo '<br />';
            // echo '<br />';
            // echo '<pre>';
            // var_dump($p);
            // echo '</pre>';
            // echo '<pre>';
            // var_dump($parent);
            // echo '</pre>';
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
                $user = $DB->get_record('user', ['username' => $student->username]);
                echo '<br />';
                echo '<br />';
                echo '<pre>';
                var_dump($user);
                // echo $SITE->shortname;
                echo '</pre>';
                die();
                $userid = user_create_user($student);
                if ($userid) {
                    $student->id = $userid;
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

        // echo '<pre>';
        // var_dump($family);
        // echo '</pre>';
        // die();
        $families[] = $family;



        // $result = process_bulk_family_data($familydata);
    }





    // echo '<br />';
    echo '<br />';
    echo '<br />';
    echo '<pre>';
    var_dump($families);
    echo '</pre>';
    echo '<br />';
    echo '<br />';
    // echo 'Created users: ';
    // echo '<pre>';
    // var_dump($created_users);
    // echo '</pre>';
    // echo '<br />';
    // echo '<br />';
    // echo 'Failed users: ';
    // echo '<pre>';
    // var_dump($failed_users);
    // echo '</pre>';
    die();


    $familydata = $data->familydata;
    // $result = process_bulk_family_data($familydata);

    // Display results
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadresults', 'local_equipment'));
    echo '<pre>' . print_r($result, true) . '</pre>';
    echo $OUTPUT->footer();
} else {
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}

// function process_bulk_family_data($familydata) {
//     $lines = explode("\n", $familydata);
//     $result = [];
//     $current_family = [];
//     $current_parent = null;
//     $current_student = null;

//     foreach ($lines as $line) {
//         $line = trim($line);
//         if (empty($line)) {
//             if (!empty($current_family)) {
//                 $result[] = process_family($current_family);
//                 $current_family = [];
//                 $current_parent = null;
//                 $current_student = null;
//             }
//             continue;
//         }

//         if (strpos($line, '@') !== false) {
//             $current_parent['email'] = $line;
//         } else if (is_numeric(str_replace([' ', '-', '(', ')'], '', $line))) {
//             if ($current_student) {
//                 $current_student['phone'] = $line;
//             } else {
//                 $current_parent['phone'] = $line;
//             }
//         } else if (strpos($line, '*') === 0) {
//             $current_student = ['name' => trim(substr($line, 1))];
//             $current_family['students'][] = $current_student;
//         } else if (strpos($line, '**') === 0) {
//             $current_student['courses'] = array_map('trim', explode(',', substr($line, 2)));
//         } else if (is_numeric($line)) {
//             $current_family['partnership_id'] = $line;
//         } else {
//             if ($current_parent) {
//                 $current_family['parents'][] = $current_parent;
//             }
//             $current_parent = ['name' => $line];
//         }
//     }

//     if (!empty($current_family)) {
//         $result[] = process_family($current_family);
//     }

//     return $result;
// }

// function process_family($family) {
//     global $DB;

//     $result = ['parents' => [], 'students' => []];

//     foreach ($family['parents'] as $parent) {
//         $user = $DB->get_record('user', ['email' => $parent['email']]);
//         if (!$user) {
//             $user = create_user($parent);
//             $result['parents'][] = "Created new parent: {$user->username}";
//         } else {
//             $result['parents'][] = "Found existing parent: {$user->username}";
//         }

//         // Assign parent role
//         $roleid = $DB->get_field('role', 'id', ['shortname' => 'parent']);
//         role_assign($roleid, $user->id, context_system::instance()->id);
//     }

//     foreach ($family['students'] as $student) {
//         $email = isset($student['email']) ? $student['email'] : local_equipment_generate_student_email($student['name'], $family['parents'][0]['email']);
//         $user = $DB->get_record('user', ['email' => $email]);
//         if (!$user) {
//             $user = create_user(['name' => $student['name'], 'email' => $email]);
//             $result['students'][] = "Created new student: {$user->username}";
//         } else {
//             $result['students'][] = "Found existing student: {$user->username}";
//         }

//         // Assign student role and enrol in courses
//         $roleid = $DB->get_field('role', 'id', ['shortname' => 'student']);
//         role_assign($roleid, $user->id, context_system::instance()->id);
//         foreach ($student['courses'] as $courseid) {
//             enrol_user($user->id, $courseid, $roleid);
//         }
//     }

//     // Store additional data in local_equipment_user table
//     $equipment_user = new stdClass();
//     $equipment_user->userid = $user->id;
//     $equipment_user->partnershipid = $family['partnership_id'];
//     $DB->insert_record('local_equipment_user', $equipment_user);

//     return $result;
// }

// function create_user($data) {
//     $user = new stdClass();
//     $names = explode(' ', $data['name']);
//     $user->firstname = $names[0];
//     $user->lastname = end($names);
//     $user->email = $data['email'];
//     $user->username = generate_username($user->firstname, $user->lastname);
//     $user->password = generate_password();
//     $user->auth = 'manual';
//     $user->confirmed = 1;
//     $user->mnethostid = $CFG->mnet_localhost_id;

//     $userid = user_create_user($user, true, false);
//     return $DB->get_record('user', ['id' => $userid]);
// }

// function generate_username($firstname, $lastname) {
//     global $DB;
//     $base_username = strtolower($firstname . $lastname);
//     $username = $base_username;
//     $i = 1;
//     while ($DB->record_exists('user', ['username' => $username])) {
//         $username = $base_username . $i;
//         $i++;
//     }
//     return $username;
// }

// function generate_password() {
//     return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(10 / strlen($x)))), 1, 10);
// }

// function generate_student_email($name, $parent_email) {
//     $names = explode(' ', $name);
//     $firstname = strtolower($names[0]);
//     return str_replace('@', "+{$firstname}@", $parent_email);
// }
