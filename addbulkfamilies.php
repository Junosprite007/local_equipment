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
    $familiesdata = json_decode($data->familiesdata);

    $families = [];

    foreach ($familiesdata as $familydata) {
        $family = new stdClass();
        $family->partnership = $familydata->partnership->data;
        $parents = $familydata->parents;
        $students = $familydata->students;

        foreach ($parents as $parent) {
            
        }

        foreach ($students as $student) {

        }



        $result = process_bulk_family_data($familydata);
    }















    echo '<br />';
    echo '<br />';
    echo '<br />';
    echo '<pre>';
    var_dump($familiesdata);
    echo '</pre>';
    die();


    $familydata = $data->familydata;
    $result = process_bulk_family_data($familydata);

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

function process_bulk_family_data($familydata) {
    $lines = explode("\n", $familydata);
    $result = [];
    $current_family = [];
    $current_parent = null;
    $current_student = null;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            if (!empty($current_family)) {
                $result[] = process_family($current_family);
                $current_family = [];
                $current_parent = null;
                $current_student = null;
            }
            continue;
        }

        if (strpos($line, '@') !== false) {
            $current_parent['email'] = $line;
        } else if (is_numeric(str_replace([' ', '-', '(', ')'], '', $line))) {
            if ($current_student) {
                $current_student['phone'] = $line;
            } else {
                $current_parent['phone'] = $line;
            }
        } else if (strpos($line, '*') === 0) {
            $current_student = ['name' => trim(substr($line, 1))];
            $current_family['students'][] = $current_student;
        } else if (strpos($line, '**') === 0) {
            $current_student['courses'] = array_map('trim', explode(',', substr($line, 2)));
        } else if (is_numeric($line)) {
            $current_family['partnership_id'] = $line;
        } else {
            if ($current_parent) {
                $current_family['parents'][] = $current_parent;
            }
            $current_parent = ['name' => $line];
        }
    }

    if (!empty($current_family)) {
        $result[] = process_family($current_family);
    }

    return $result;
}

function process_family($family) {
    global $DB;

    $result = ['parents' => [], 'students' => []];

    foreach ($family['parents'] as $parent) {
        $user = $DB->get_record('user', ['email' => $parent['email']]);
        if (!$user) {
            $user = create_user($parent);
            $result['parents'][] = "Created new parent: {$user->username}";
        } else {
            $result['parents'][] = "Found existing parent: {$user->username}";
        }

        // Assign parent role
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'parent']);
        role_assign($roleid, $user->id, context_system::instance()->id);
    }

    foreach ($family['students'] as $student) {
        $email = isset($student['email']) ? $student['email'] : generate_student_email($student['name'], $family['parents'][0]['email']);
        $user = $DB->get_record('user', ['email' => $email]);
        if (!$user) {
            $user = create_user(['name' => $student['name'], 'email' => $email]);
            $result['students'][] = "Created new student: {$user->username}";
        } else {
            $result['students'][] = "Found existing student: {$user->username}";
        }

        // Assign student role and enrol in courses
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'student']);
        role_assign($roleid, $user->id, context_system::instance()->id);
        foreach ($student['courses'] as $courseid) {
            enrol_user($user->id, $courseid, $roleid);
        }
    }

    // Store additional data in local_equipment_user table
    $equipment_user = new stdClass();
    $equipment_user->userid = $user->id;
    $equipment_user->partnershipid = $family['partnership_id'];
    $DB->insert_record('local_equipment_user', $equipment_user);

    return $result;
}

function create_user($data) {
    $user = new stdClass();
    $names = explode(' ', $data['name']);
    $user->firstname = $names[0];
    $user->lastname = end($names);
    $user->email = $data['email'];
    $user->username = generate_username($user->firstname, $user->lastname);
    $user->password = generate_password();
    $user->auth = 'manual';
    $user->confirmed = 1;
    $user->mnethostid = $CFG->mnet_localhost_id;

    $userid = user_create_user($user, true, false);
    return $DB->get_record('user', ['id' => $userid]);
}

function generate_username($firstname, $lastname) {
    global $DB;
    $base_username = strtolower($firstname . $lastname);
    $username = $base_username;
    $i = 1;
    while ($DB->record_exists('user', ['username' => $username])) {
        $username = $base_username . $i;
        $i++;
    }
    return $username;
}

// function generate_password() {
//     return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(10 / strlen($x)))), 1, 10);
// }

function generate_student_email($name, $parent_email) {
    $names = explode(' ', $name);
    $firstname = strtolower($names[0]);
    return str_replace('@', "+{$firstname}@", $parent_email);
}
