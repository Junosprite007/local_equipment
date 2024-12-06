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
$PAGE->requires->js_call_amd('local_equipment/bulkfamilyupload', 'rotateSymbol');
$PAGE->requires->js_call_amd('local_equipment/editpartnership_form', 'init');

$form = new addbulkfamilies_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($data = $form->get_data()) {
    global $DB, $SITE;


    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadresults', 'local_equipment'));

    // Start a container for results
    echo html_writer::start_div('local-equipment-upload-results');

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
            $messages->successes = [];
            $messages->warnings = [];
            $messages->errors = [];

            $family->partnership = $familydata->partnership->data ?? '';

            // First pass of the parents to get all their current students
            foreach ($familydata->parents as $p) {
                $userid = null;
                $user = null;
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
                $password = null;

                // If the parent doesn't exist based on their email, we'll create a new user. If they do exist, we'll override the
                // $parent user we made above to the matching user found in the DB.
                if (!$user) {
                    $password = $parent->password;
                    // Add an entirely new parent user. This overrides the $parent object with the new user object.
                    $userid = user_create_user($parent);
                } else {
                    // Update an existing parent user while keeping the previously created parent object as $parent_old before
                    // overriding it, just in case. Not sure if we'll actually need it, though.
                    $parent_old = $parent;
                    $parent = $user;
                }

                if ($userid !== null) {
                    // When $userid is set (in the previous if statement), it means a new user was created.
                    $parent->id = $userid;

                    // Force passord update for the parent's on next login.
                    if ($newparent_preferenceupdate = $DB->get_record('user_preferences', [
                            'userid' => $userid,
                            'name' => 'auth_forcepasswordchange',
                            'value' => 0
                        ])
                    ) {
                        $newparent_preferenceupdate->value = 1;
                        $DB->update_record('user_preferences', $newparent_preferenceupdate);
                    } else {
                        $newparent_preferenceupdate = new stdClass();
                        $newparent_preferenceupdate->userid = $userid;
                        $newparent_preferenceupdate->name = 'auth_forcepasswordchange';
                        $newparent_preferenceupdate->value = 1;
                        $newparent_preferenceupdate_id = $DB->insert_record('user_preferences', $newparent_preferenceupdate);
                    }

                    $created_users[] = $parent;
                    $messages->successes[] = get_string('accountcreatedsuccessfully', 'local_equipment', $parent);
                    // Send a welcome email to the parent.
                    try {
                        $email_messageinput = new stdClass();
                        $email_messageinput->siteshortname = $SITE->shortname;
                        $email_messageinput->sitefullname = $SITE->fullname;
                        $email_messageinput->sitefullname .= $SITE->shortname ? " ($SITE->shortname)" : '';
                        $email_messageinput->personname = $parent->firstname;
                        $email_messageinput->personname .= $parent->lastname ? " $parent->lastname" : '';
                        $email_messageinput->email = $parent->email;
                        $email_messageinput->username = $parent->username;
                        $email_messageinput->password = $password ?? get_string('contactusforyourpassword', 'local_equipment');

                        $loginurl = new moodle_url('/login/index.php');
                        $loginlink = html_writer::link($loginurl, $loginurl);
                        $email_messageinput->loginurl = $loginlink;

                        $contactuser = core_user::get_noreply_user();
                        $subject = get_string('welcomeemail_subject', 'local_equipment', $email_messageinput);
                        $email_message = get_string('welcomeemail_body', 'local_equipment', $email_messageinput);

                        $success = email_to_user(
                            $parent,                        // To user
                            $contactuser,                   // From user
                            $subject,
                            html_to_text($email_message),   // Plain text version
                            $email_message                  // HTML version
                        );

                        if ($success) {
                            $messages->successes[] = get_string(
                                'newuseremailsenttouser',
                                'local_equipment',
                                $email_messageinput
                            );
                        } else {
                            $messages->errors[] = get_string(
                                'emailfailedtosendtouser',
                                'local_equipment',
                                $email_messageinput
                            );
                        }
                    } catch (moodle_exception $e) {
                        $messages->errors[] = $e->getMessage();
                    }
                } else if ($user) {
                    if (strcasecmp($parent_old->firstname, $user->firstname) !== 0 || strcasecmp($parent_old->lastname, $user->lastname) !== 0) {
                        $parent_old->otherfirst = $user->firstname;
                        $parent_old->otherlast = $user->lastname;
                        $messages->warnings[] = get_string('accountalreadyexistsbutwithdifferentname', 'local_equipment', $parent_old);
                        unset($parent_old->otherfirst);
                        unset($parent_old->otherlast);
                    } else {
                        $messages->successes[] = get_string('accountalreadyexists', 'local_equipment', $parent);
                        unset($parent->otherfirst);
                        unset($parent->otherlast);
                    }
                    $existing_users[] = $parent;
                } else {
                    // This will probably never run, but it's here just in case. If the user doesn't exist but somehow wasn't
                    // created successfully, then there was an error, so don't add the parent to the family.
                    $messages->errors[] = get_string('usernotaddedtofamily', 'local_equipment', $parent) . ' ' . get_string('errorcreatinguser', 'local_equipment', $parent);
                    continue;
                }
                // By the end of the last iteration of this parents loop, $allstudentsofallparents should contain all students with
                // unique IDs that have these parents already assigned to them. This is used later in this file.
                $allstudentsofallparents = $allstudentsofallparents + local_equipment_get_students_of_parent($parent->id);
                $parent->partnershipid = $familydata->partnership->data ?? '';
                $parents[] = $parent;
            }

            // By this point, we should have all the parents in the current family, as well as any existing students they have, so
            // now it's time to start processing the students.
            $studentfirstnames = [];
            foreach ($familydata->students as $s) {
                $userid = null;
                $user = null;
                $student = new stdClass();
                // Below, $s->student is basically the same as $p->name above; it just gets the student's name. At the time, I
                // needed a different word 'name' when it came to the student, so I used 'student' instead. This can make things a
                // little confusing, so sorry about that.
                $student->firstname = clean_param($s->student->data->firstName, PARAM_TEXT);
                $student->middlename = clean_param($s->student->data->middleName ?? '', PARAM_TEXT);
                $student->lastname = $s->student->data->lastName !== '' ? clean_param($s->student->data->lastName, PARAM_TEXT) : $parents[0]->lastname;
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
                $password = null;
                if (!$user) {
                    foreach ($allstudentsofallparents as $sofp) {
                        if (strcasecmp($student->firstname, $sofp->firstname) === 0 && strcasecmp($student->lastname, $sofp->lastname) === 0) {
                            $user = $DB->get_record('user', ['email' => $sofp->email]);
                            $student = $user;
                            break;
                        }
                    }
                    if (!$user) {
                        $password = $student->password;
                        $userid = user_create_user($student);
                    }
                } else {
                    // Update an existing student user to the matched user. I can't think of anything that needs to be updated at
                    // the moment.
                    $student = $user;
                }

                if ($userid !== null) {
                    // When $userid is set (in the previous if statement), it means a new user was created.
                    $student->id = $userid;

                    // Force passord update for the student's on next login.
                    if ($newstudent_preferenceupdate = $DB->get_record('user_preferences', ['userid' => $userid, 'name' => 'auth_forcepasswordchange', 'value' => 0])) {
                        $newstudent_preferenceupdate->value = 1;
                        $DB->update_record('user_preferences', $newstudent_preferenceupdate);
                    } else {
                        $newstudent_preferenceupdate = new stdClass();
                        $newstudent_preferenceupdate->userid = $userid;
                        $newstudent_preferenceupdate->name = 'auth_forcepasswordchange';
                        $newstudent_preferenceupdate->value = 1;
                        $newstudent_preferenceupdate_id = $DB->insert_record('user_preferences', $newstudent_preferenceupdate);
                    }
                    $created_users[] = $student;
                    $messages->successes[] = get_string('accountcreatedsuccessfully', 'local_equipment', $student);
                    // Send a welcome email to the parent.
                    try {
                        $email_messageinput = new stdClass();
                        $email_messageinput->siteshortname = $SITE->shortname;
                        $email_messageinput->sitefullname = $SITE->fullname;
                        $email_messageinput->sitefullname .= $SITE->shortname ? " ($SITE->shortname)" : '';
                        $email_messageinput->personname = $student->firstname;
                        $email_messageinput->personname .= $student->lastname ? " $student->lastname" : '';
                        $email_messageinput->email = $student->email;
                        $email_messageinput->username = $student->username;
                        $email_messageinput->password = $password ?? get_string('contactusforyourpassword', 'local_equipment');

                        $loginurl = new moodle_url('/login/index.php');
                        $loginlink = html_writer::link($loginurl, $loginurl);
                        $email_messageinput->loginurl = $loginlink;

                        $contactuser = core_user::get_noreply_user();
                        $subject = get_string('welcomeemail_subject', 'local_equipment', $email_messageinput);
                        $email_message = get_string('welcomeemail_body', 'local_equipment', $email_messageinput);

                        $success = email_to_user(
                            $student,                       // To user
                            $contactuser,                   // From user
                            $subject,
                            html_to_text($email_message),   // Plain text version
                            $email_message                  // HTML version
                        );

                        if ($success) {
                            $messages->successes[] = get_string(
                                'newuseremailsenttouser',
                                'local_equipment',
                                $email_messageinput
                            );
                        } else {
                            $messages->errors[] = get_string(
                                'emailfailedtosendtouser',
                                'local_equipment',
                                $email_messageinput
                            );
                        }
                    } catch (moodle_exception $e) {
                        $messages->errors[] = $e->getMessage();
                    }
                } else if ($user) {
                    $messages->successes[] = get_string('accountalreadyexists', 'local_equipment', $student);
                    unset($parent->otherfirst);
                    unset($parent->otherlast);
                    $existing_users[] = $student;
                } else {
                    // This will probably never run, but it's here just in case. If the user doesn't exist but somehow wasn't
                    // created successfully, then there was an error, so don't add the parent to the family.
                    $messages->errors[] = get_string('usernotaddedtofamily', 'local_equipment', $student) . ' ' . get_string('errorcreatinguser', 'local_equipment', $student);
                    continue;
                }

                $student->courses = $s->courses->data;

                $coursenames = [];
                if (empty($student->courses)) {
                    $messages->errors[] = get_string('nocoursesfoundforuser', 'local_equipment', $student);
                } else {
                    foreach ($student->courses as $c) {
                        // Enroll the student into each course.
                        // The $c variable is simply the course ID.
                        $student->courses_results[$c] = local_equipment_enrol_user_in_course($student, $c, $roleid_student);
                        array_push($messages->successes, ...$student->courses_results[$c]->successes);
                        array_push($messages->warnings, ...$student->courses_results[$c]->warnings);
                        array_push($messages->errors, ...$student->courses_results[$c]->errors);

                        if (!empty($student->courses_results[$c]->successes)) {
                            $courseurl = new moodle_url('/course/view.php',
                                ['id' => $c]
                            );
                            $courselink = html_writer::link($courseurl, $student->courses_results[$c]->coursename);
                            $coursenames[] = $courselink;
                        }
                    }

                    // Send the enrollment email to the student only if they were successfully enrolled in at least one course.
                    $emailsentstatus = local_equipment_send_enrollment_message(
                        $student,
                        $coursenames,
                        'student',
                        $family->partnership
                    );
                    array_push($messages->successes, ...$emailsentstatus->successes);
                    array_push($messages->warnings, ...$emailsentstatus->warnings);
                    array_push($messages->errors, ...$emailsentstatus->errors);

                    $allcourses = array_merge($allcourses, $student->courses);
                }

                // Assign each parent to the current student.
                foreach ($parents as $p) {
                    $userassigned = local_equipment_assign_role_relative_to_user($student, $p, 'parent');

                    array_push($messages->successes, ...$userassigned->successes);
                    array_push($messages->warnings, ...$userassigned->warnings);
                    array_push($messages->errors, ...$userassigned->errors);
                }
                $student->partnershipid = $familydata->partnership->data ?? '';

                // Create links to each student's profile to be included in the email.
                $userurl = new moodle_url('/user/profile.php', ['id' => $student->id]);
                $userlink = html_writer::link($userurl, $student->firstname);
                $studentfirstnames[] = $userlink;
                $students[] = $student;
            }

            // Fill the family array with the parents, students, partnership, and unique courses (each student in the $students
            // array already contains a list of their individual courses).
            $family->parents = $parents;
            $family->students = $students;
            $family->all_courses = array_unique($allcourses);

            // Enroll all the parents into each course with the role of "parent". This is so they can see the grades of their
            // students, as well as the courses in which their students are enrolled.
            foreach ($family->parents as $p) {
                $coursenames = [];
                foreach ($family->all_courses as $c) {
                    $p->courses_results[$c] = local_equipment_enrol_user_in_course(
                        $p,
                        $c,
                        $roleid_parent
                    );
                    array_push($messages->successes, ...$p->courses_results[$c]->successes);
                    array_push($messages->warnings, ...$p->courses_results[$c]->warnings);
                    array_push($messages->errors, ...$p->courses_results[$c]->errors);

                    if (!empty($p->courses_results[$c]->successes)) {
                        // Create links for each course to be included in the email.
                        $courseurl = new moodle_url('/course/view.php', ['id' => $c]);
                        $courselink = html_writer::link($courseurl, $p->courses_results[$c]->coursename);
                        $coursenames[] = $courselink;
                    }
                }

                // If courses_results->successes is NOT empty, then we should include that course in the email. Those errors are
                // handled in the enrollment email function, though.
                $emailsentstatus = local_equipment_send_enrollment_message(
                    $p,
                    $coursenames,
                    'parent',
                    $family->partnership,
                    $studentfirstnames
                );
                array_push($messages->successes, ...$emailsentstatus->successes);
                array_push($messages->warnings, ...$emailsentstatus->warnings);
                array_push($messages->errors, ...$emailsentstatus->errors);
            }

            // Poopulate the $families array with the current family. HAH. Poop...
            $families[] = $family;

            // This is where we create or update the local_equipment_user


            // Determine overall status
            $status = 'success';
            if (!empty($messages->errors)) {
                $status = 'error';
            } else if (!empty($messages->warnings)) {
                $status = 'warning';
            }

            // Get family name for the notification
            $familyname = '';
            if (count($family->parents) > 0) {
                $familyname = $family->parents[0]->lastname;
            } else if (count($family->students) > 0) {
                $familyname = $family->students[0]->lastname;
            } else {
                throw new moodle_exception('familyhasnousers', 'local_equipment');
            }

            // Generate and output the notification
            echo local_equipment_generate_family_notification($familyname, $messages, $status);
        }

        $allusers = array_merge($created_users, $existing_users);

        // Insert all the users into the local_equipment_user table. At this point all users are garanteed to have an ID, so we
        // should be able to check if they already exist in the plugin's user table, add them if they don't, and update them if they
        // do.
        foreach ($allusers as $u) {
            $record = new stdClass();
            $le_user = $DB->get_record('local_equipment_user', ['userid' => $u->id]);

            if ($le_user) {
                $record = $le_user;
                // We should update the partnership ID here, just in case it has changed from the previous school year.
                $record->partnershipid = $u->partnershipid;
                $record->timemodified = time();

                $DB->update_record('local_equipment_user', $record);
            } else {
                $record->userid = $u->id;
                $record->partnershipid = $u->partnershipid;
                $record->studentids = '[]';
                $record->vccsubmissionids = '[]';
                $record->phoneverificationids = '[]';
                $record->phone = null;
                $record->phone_verified = null;
                $record->mailing_extrainput = '';
                $record->mailing_streetaddress = '';
                $record->mailing_apartment = '';
                $record->mailing_city = '';
                $record->mailing_state = '';
                $record->mailing_country = '';
                $record->mailing_zipcode = '';
                $record->mailing_extrainstructions = null;
                $record->billing_extrainput = '';
                $record->billing_sameasmailing = '0';
                $record->billing_streetaddress = '';
                $record->billing_apartment = '';
                $record->billing_city = '';
                $record->billing_state = '';
                $record->billing_country = '';
                $record->billing_zipcode = '';
                $record->billing_extrainstructions = null;
                $record->timecreated = time();
                $record->timemodified = time();

                $record->id = $DB->insert_record('local_equipment_user', $record);
            }
        }

        // Get all the user IDs in the entire system. This will be used to merge duplicate user entries in the local_equipment_user
        // table. This should be removed in the next update.
        $alluseridsinwholesystem = $DB->get_records('user', null, '', 'id');
        foreach ($alluseridsinwholesystem as $id => $u) {
            $recordcount = $DB->count_records('local_equipment_user', ['userid' => $id]);
            if ($recordcount > 1) {
                local_equipment_combine_user_records_by_userid($id);
            }
        }

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
        echo $OUTPUT->notification($e->getMessage(), 'error');
    }

    echo html_writer::end_div(); // local-equipment-upload-results container

    // Add continue button that returns to the previous page or a specific destination
    $continueurl = new moodle_url('/local/equipment/addbulkfamilies.php'); // Or whatever destination URL you prefer
    echo $OUTPUT->continue_button($continueurl);

    echo $OUTPUT->footer();
} else {
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}
