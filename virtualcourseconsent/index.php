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
require_once($CFG->dirroot . '/lib/accesslib.php');

require_login();
// Check if the user is a guest and redirect or display an error message
if (isguestuser()) {
    $msgparams = ['form' => get_string('virtualcourseconsent', 'local_equipment'), 'site' => $SITE->shortname];
    redirect(new moodle_url('/login/index.php'), get_string('mustlogintoyourownaccount', 'local_equipment', $msgparams), null, \core\output\notification::NOTIFY_ERROR);
}

$context = \core\context\system::instance();

$PAGE->set_url(new moodle_url('/local/equipment/virtualcourseconsent/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('consentformtitle', 'local_equipment'));
$PAGE->set_heading(get_string('consentformheading', 'local_equipment'));
$PAGE->requires->js_call_amd('local_equipment/vccsubmission_addstudents_form', 'init');
$PAGE->requires->js_call_amd('local_equipment/formhandling', 'setupFieldsetNameUpdates', ['student', 'header']);
$PAGE->requires->js_call_amd('local_equipment/formhandling', 'setupMultiSelects');

// Import the Select2 library for selecting multiple courses on mobile devices.
$PAGE->requires->css(new moodle_url('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'));
// $PAGE->requires->js(new moodle_url('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'));


// In the script where you handle the form submission and display
$mform = new \local_equipment\form\vccsubmission_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($data = $mform->get_data()) {
    global $DB, $USER;
    $success = true;

    $transaction = $DB->start_delegated_transaction();

    try {

        // Insert the consent form submission into the database.

        // Fetch the partnership name
        $pickupmethod = '';
        $partnership = $DB->get_record('local_equipment_partnership', ['id' => $data->partnership], 'name');
        $pickupmethods = local_equipment_get_pickup_methods();

        switch ($data->pickupmethod) {
            case 'self':
                $pickupmethod = $pickupmethods['self'];
                break;
            case 'other':
                $pickupmethod = $pickupmethods['other'];
                break;
            case 'ship':
                $pickupmethod = $pickupmethods['ship'];
                break;
            case 'purchased':
                $pickupmethod = $pickupmethods['purchased'];
                break;
            default:
                $pickupmethod = '';
                break;
        }

        // Insert extended user (parent) record.
        $parentrecord = new stdClass();
        // Foriegn keys first.
        $parentrecord->userid = $USER->id;
        $parentrecord->partnershipid = $data->partnership;
        $parentrecord->pickupid = $data->pickup;
        $parentrecord->studentids = '[]';
        $parentrecord->vccsubmissionids = '[]';
        $parentrecord->phoneverificationids = '[]';

        // Mailing address-related fields. Must be renamed in the database schema.
        $parentrecord->mailing_extrainput = $data->mailing_extrainput ?? '';
        $parentrecord->mailing_streetaddress = $data->mailing_streetaddress;
        $parentrecord->mailing_apartment = $data->mailing_apartment ?? '';
        $parentrecord->mailing_city = $data->mailing_city;
        $parentrecord->mailing_state = $data->mailing_state;
        $parentrecord->mailing_country = $data->mailing_country;
        $parentrecord->mailing_zipcode = $data->mailing_zipcode;
        $parentrecord->mailing_extrainsructions = $data->mailing_extrainsructions ?? '';

        // Billing address-related fields. Must be renamed in the database schema.
        $parentrecord->billing_extrainput = $data->billing_extrainput ?? '';
        $parentrecord->billing_sameasmailing = $data->billing_sameasmailing ?? 0;
        $parentrecord->billing_streetaddress = $data->billing_streetaddress ?? '';
        $parentrecord->billing_apartment = $data->billing_apartment ?? '';
        $parentrecord->billing_city = $data->billing_city ?? '';
        $parentrecord->billing_state = $data->billing_state ?? '';
        $parentrecord->billing_country = $data->billing_country ?? '';
        $parentrecord->billing_zipcode = $data->billing_zipcode ?? '';
        $parentrecord->billing_extrainsructions = $data->billing_extrainsructions ?? '';

        $parentrecord->timecreated = $parentrecord->timemodified = time();
        // Insert parent user record.
        $recordscount = $DB->count_records('local_equipment_user', ['userid' => $USER->id]);

        if ($recordscount > 0) {
            $combinedrecords = local_equipment_combine_user_records_by_userid($USER->id);

            $parentrecord->id = $combinedrecords->id;
            $parentrecord->studentids = $combinedrecords->studentids;
            $parentrecord->vccsubmissionids = $combinedrecords->vccsubmissionids;

            $DB->update_record('local_equipment_user', $parentrecord);
        } else {
            $parentrecord->id = $DB->insert_record('local_equipment_user', $parentrecord);
        }

        // Insert the virtual course consent (vcc) submission.
        $vccsubmission = new stdClass();
        $vccsubmission->userid = $USER->id;
        $vccsubmission->partnershipid = $data->partnership;
        $vccsubmission->pickupid = $data->pickup;
        $vccsubmission->studentids = '[]';
        $vccsubmission->agreementids = '[]';
        $vccsubmission->confirmationid = md5(uniqid(rand(), true)); // Generate a unique confirmation ID
        $vccsubmission->confirmationexpired = 0;

        $vccsubmission->email = $data->email;
        $vccsubmission->firstname = $data->firstname;
        $vccsubmission->lastname = $data->lastname;
        $phoneobj = local_equipment_parse_phone_number($data->phone);
        $vccsubmission->phone = $phoneobj->phone;
        $vccsubmission->partnership_name = $partnership->name;

        // Mailing address-related fields. Must be renamed in the database schema.
        $vccsubmission->mailing_extrainput = $data->mailing_extrainput ?? '';
        $vccsubmission->mailing_streetaddress = $data->mailing_streetaddress;
        $vccsubmission->mailing_apartment = $data->mailing_apartment ?? '';
        $vccsubmission->mailing_city = $data->mailing_city;
        $vccsubmission->mailing_state = $data->mailing_state;
        $vccsubmission->mailing_country = $data->mailing_country;
        $vccsubmission->mailing_zipcode = $data->mailing_zipcode;
        $vccsubmission->mailing_extrainsructions = $data->mailing_extrainsructions ?? '';
        // Billing address-related fields. Must be renamed in the database schema.
        $vccsubmission->billing_extrainput = $data->billing_extrainput ?? '';
        $vccsubmission->billing_sameasmailing = $data->billing_sameasmailing ?? 0;
        $vccsubmission->billing_streetaddress = $data->billing_streetaddress ?? '';
        $vccsubmission->billing_apartment = $data->billing_apartment ?? '';
        $vccsubmission->billing_city = $data->billing_city ?? '';
        $vccsubmission->billing_state = $data->billing_state ?? '';
        $vccsubmission->billing_country = $data->billing_country ?? '';
        $vccsubmission->billing_zipcode = $data->billing_zipcode ?? '';
        $vccsubmission->billing_extrainsructions = $data->billing_extrainsructions ?? '';
        $vccsubmission->electronicsignature = $data->signature;
        $vccsubmission->pickupmethod = $pickupmethod;
        $vccsubmission->pickuppersonname = $data->pickuppersonname ?? '';
        $vccsubmission->pickuppersonphone = $data->pickuppersonphone ?? '';
        $vccsubmission->pickuppersondetails = $data->pickuppersondetails ?? '';
        $vccsubmission->usernotes = $data->usernotes ?? '';
        $vccsubmission->timecreated = $vccsubmission->timemodified = time();
        // Insert vccsubmission record.
        $vccsubmission->id = $DB->insert_record('local_equipment_vccsubmission', $vccsubmission);
        $vccsubmissionids = [$vccsubmission->id];

        $previous_record = $DB->get_record('local_equipment_user', ['id' => $parentrecord->id], 'vccsubmissionids');
        $previous_vccsubmissionids = json_decode($previous_record->vccsubmissionids);
        $vccsubmissionids = array_merge($previous_vccsubmissionids, $vccsubmissionids);
        $parentrecord->vccsubmissionids = json_encode($vccsubmissionids);

        // Update the parent record with the vccsubmission ID. Will only be one vccsubmission ID for now.
        // Eventually this will be an array of vccsubmission IDs json encoded to a string.
        // $parentrecord->vccsubmissionids = json_encode([$vccsubmission->id]);

        // Insert the virtual course consent (vcc) submission students.
        $studentids = [];
        for ($i = 0; $i < $data->students; $i++) {
            $vccsubmission_student = new stdClass();
            // The string value of $i.
            $s = strval($i);
            // The selectedcourses string should have already been decoded above.
            $selectedcourses = $data->student_courses[$i];


            $vccsubmission_student->userid = $data->student_id[$i] ?? 0;
            $vccsubmission_student->vccsubmissionid = $vccsubmission->id;
            $vccsubmission_student->courseids = json_encode($selectedcourses) ?? '[]';
            $vccsubmission_student->firstname = $data->student_firstname[$i];
            $vccsubmission_student->lastname = $data->student_lastname[$i];
            $vccsubmission_student->email = $data->student_email[$i] ?? local_equipment_generate_student_email($USER->email, $vccsubmission_student->firstname);
            $vccsubmission_student->dateofbirth = $data->student_dob[$i];
            $vccsubmission_student->timecreated = time();

            $vccsubmission_student->id = $DB->insert_record('local_equipment_vccsubmission_student', $vccsubmission_student);
            // Make an array of student IDs for later use.
            $studentids[] = $vccsubmission_student->id;

            // Insert student course records
            foreach ($selectedcourses as $courseid) {
                $vccsubmission_student_course = new stdClass();
                $vccsubmission_student_course->studentid = $vccsubmission_student->id;
                $vccsubmission_student_course->courseid = $courseid;
                $DB->insert_record('local_equipment_vccsubmission_student_course', $vccsubmission_student_course);
            }
        }

        // Only get this submission's studentids.
        $vccsubmission->studentids = json_encode($studentids);

        // Then add the studentids to any existing studentids in the parent user record.
        $previous_record = $DB->get_record('local_equipment_user', ['id' => $parentrecord->id], 'studentids');
        $previous_studentids = json_decode($previous_record->studentids);
        $studentids = array_merge($previous_studentids, $studentids);
        $parentrecord->studentids = json_encode($studentids);
        $DB->update_record('local_equipment_user', $parentrecord);

        // Get the number of agreements

        // Insert the virtual course consent (vcc) submission agreement.
        $agreementcount = $data->agreements;
        $agreementids = [];
        for ($i = 0; $i < $agreementcount; $i++) {
            $agreementid = $data->{"agreement_{$i}_id"};
            $agreementtype = $data->{"agreement_{$i}_type"};
            $agreementids[] = $agreementid;

            $vccsubmission_agreement = new stdClass();
            $vccsubmission_agreement->vccsubmissionid = $vccsubmission->id;
            $vccsubmission_agreement->agreementid = $agreementid;

            if ($agreementtype == 'optinout') {
                $choice = $data->{"agreement_{$i}_choice"};

                switch ($choice) {
                    case 'optin':
                        $vccsubmission_agreement->optinout = 1;
                        break;
                    case 'optout':
                        $vccsubmission_agreement->optinout = 2;
                        break;
                    default:
                        $vccsubmission_agreement->optinout = 0;
                        break;
                }
            } else {
                $vccsubmission_agreement->optinout = 0;
            }
            $DB->insert_record('local_equipment_vccsubmission_agreement', $vccsubmission_agreement);
        }

        // Only get this submission's agreementids.
        $vccsubmission->agreementids = json_encode($agreementids);

        $DB->update_record('local_equipment_vccsubmission', $vccsubmission);



        // WE ALSO NEED TO DO THE PHONE VERIFICATION AND UPDATE THAT RECORD.


        // update all the records.
        // $DB->update_record('local_equipment_user', $parentrecord);

        // Get the current time using Moodle's recommended approach
        $clock = \core\di::get(\core\clock::class);
        $now = $clock->now()->getTimestamp();

        // This is getting the local_equipment_user_exchange table ready for record insertion.
        $userexchange = new stdClass();
        $userexchange->userid = $USER->id;
        $userexchange->exchangeid = $data->pickup;
        $userexchange->reminder_code = '0';
        $userexchange->timemodified = $now;
        $userexchange->timecreated = $now;

        $existingrecord = $DB->get_record('local_equipment_user_exchange', ["userid" => $userexchange->userid, "exchangeid" => $userexchange->exchangeid]);
        // echo '<pre>';
        // var_dump($existingrecord);
        // echo '</pre>';
        // die();

        if ($existingrecord) {
            // I'm deciding to update the existing record if it does exist because exchange IDs should change from semester to semester —
            // an exchange date that has already past should not be selectable by users filling out the VCC form.
            $existingrecord->reminder_code = '0';
            $existingrecord->timemodified = $now;
            $DB->update_record('local_equipment_user_exchange', $existingrecord);
        } else {
            // Insert the reminder records.
            $DB->insert_record('local_equipment_user_exchange', $userexchange);
        }


        // Commit transaction
        $transaction->allow_commit();

    } catch (Exception $e) {
        $transaction->rollback($e);
        $success = false;
    }

    if ($success) {
        $successmsg = get_string('formsubmitted', 'local_equipment',  get_string('virtualcourseconsent', 'local_equipment'));
        $textuser = new stdClass();
        $textuser->tonumber = $vccsubmission->phone;

        $provider = get_config('local_equipment', 'otpgateway');
        $phoneisverified = $DB->get_record('local_equipment_phonecommunication_otp', ['userid' => $USER->id, 'tophonenumber' => $textuser->tonumber, 'phoneisverified' => 1], '*', IGNORE_MULTIPLE);

        if ($provider && !$phoneisverified) {
            $textuser->notes = [
                'shortname' => $SITE->shortname
            ];

            if (
                $USER->phone2 == '' && $USER->phone2 != $vccsubmission->phone
            ) {
                $USER->phone2 = $vccsubmission->phone;
                $DB->update_record('user', $USER);
            }
            $responseobject = local_equipment_send_secure_otp($provider, $textuser->tonumber);

            // We're eventually going to need to handle Moodle debugging options. Check out 'testoutgoingmailconf.php' for an example.

            if ($responseobject->success) {
                $redirecturl = new moodle_url('/local/equipment/phonecommunication/verifyotp.php');
                $msgparams = new stdClass();
                $msgparams->tonumber = $textuser->tonumber;
                $msgparams->link = $link;
                $successmsg = $successmsg . "<br /><br />" . get_string('phoneverificationrequire', 'local_equipment', $msgparams);
                $successmsg = $successmsg . " " . get_string('acodehasbeensent', 'local_equipment', $msgparams);
                $notificationtype = \core\output\notification::NOTIFY_SUCCESS;
                redirect($redirecturl, $successmsg, null, $notificationtype);
            } else {
                $notificationtype = 'notifyproblem';
                $successmsg = get_string('senttextfailure', 'local_equipment', $responseobject->errormessage);
            }
        } else if (!$provider) {
            $successmsg = $successmsg . "<br /><br />" . get_string('noproviderfound_user', 'local_equipment');
        }


        redirect(
            new moodle_url('/'),
            $successmsg,
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/local/equipment/virtualcourseconsent/index.php'),
            get_string('errorsubmittingform', 'local_equipment'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}

