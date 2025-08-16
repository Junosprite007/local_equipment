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
 * Form for virtual course consent.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\form;

use moodle_url;
use core\output\notification;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/equipment/lib.php');

class vccsubmission_form extends \moodleform {

    /** @var array Auto-populated parent data */
    private array $parentdata;

    /** @var array Auto-populated student data */
    private array $studentdata;

    /** @var array Student eligibility warnings */
    private array $warnings = [];

    public function definition() {
        global $USER, $DB, $SITE;
        $mform = $this->_form;
        $customdata = $this->_customdata;

        // Get auto-populated data using existing functions
        $this->load_parent_data($USER->id);
        $this->load_student_data($USER->id);
        $this->validate_student_eligibility();

        // Check if parent has any eligible students
        if (empty($this->studentdata) && !is_siteadmin()) {
            redirect(
                new \moodle_url('/'),
                get_string('nostudentsenrolled', 'local_equipment'),
                null,
                \core\output\notification::NOTIFY_WARNING
            );
        }

        if (is_siteadmin()) {
            $mform->addElement('html', '<div class="alert alert-warning" role="alert">' . get_string('currentlyloggedinassiteadmin', 'local_equipment') . '</div>');
        }

        // Get the admin setting for header text
        $headertext = get_config('local_equipment', 'vccformwarning');

        // Only add the header text if it's not empty
        if (!empty($headertext)) {
            $mform->addElement('html', '<div class="alert alert-warning" role="alert">' . format_text($headertext) . '</div>');
        }

        $repeatno = optional_param('repeatno', 1, PARAM_INT);
        $deletebuttonname = 'delete_student';
        $addfieldsname = 'addstudent';
        $deletions = optional_param_array($deletebuttonname, [], PARAM_INT);

        if (!empty($deletions)) {
            $repeatno = $repeatno - count($deletions);
            $repeatno = max(1, $repeatno); // Ensure at least one student remains
        }



        $mastercourses = local_equipment_get_master_courses('ALL_COURSES_CURRENT');
        $coursesformatted = $mastercourses->courses_formatted;

        $coursesformatted_properlynamed = [];

        foreach ($coursesformatted as $courseid => $coursename) {
            // Get the current month
            $currentMonth = date('n'); // 'n' returns the month without leading zeros (1-12)

            // Determine the letter based on the current month
            if ($currentMonth >= 7 && $currentMonth <= 12) {
                $letter = 'F';
            } else {
                $letter = 'S';
            }

            $pattern = '/– ' . $letter . '\d{2} \([A-Za-z]\)/';
            if (preg_match($pattern, $coursename)) {
                $coursesformatted_properlynamed[$courseid] = $coursename;
            }
        }


        // $mform->addElement('html', '<div class="alert alert-warning" role="alert">' . get_string('attnparents_useyouraccount', 'local_equipment', $SITE->shortname) . '</div>');


        // Users would fill out this form while logged into one of their students accounts, so the email, first name,
        // and last name fields ended up being filled out with the student's information, since this form changes core user fields.
        // The parents would then fill out the electronic signature field with the student's name instead of their own.

        // This was obviously a big oversight, so I had removed this "convenience" of default values so that parents would be more
        // likely to just fill out their own information, but I put it back 'cause I'm taking a different approach now. I'm going
        // to force users to be logged in to their own personal account by validating that the logged in user has the 'Parent' role
        // assigned to their account.

        // Use auto-populated parent data to display fields as labels or pre-filled inputs

        // Profile email - display as label (read-only)
        $mform->addElement('html', '<div class="row mb-3">
            <label class="col-sm-3 col-form-label">' . get_string('email') . '</label>
            <div class="col-sm-9">
                <div class="form-control-plaintext">' . s($this->parentdata['email']) . '</div>
            </div>
        </div>');
        $mform->addElement('hidden', 'email', $this->parentdata['email']);
        $mform->setType('email', PARAM_EMAIL);

        // Profile first name - display as label (read-only)
        $mform->addElement('html', '<div class="row mb-3">
            <label class="col-sm-3 col-form-label">' . get_string('firstname') . '</label>
            <div class="col-sm-9">
                <div class="form-control-plaintext">' . s($this->parentdata['firstname']) . '</div>
            </div>
        </div>');
        $mform->addElement('hidden', 'firstname', $this->parentdata['firstname']);
        $mform->setType('firstname', PARAM_TEXT);

        // Profile last name - display as label (read-only)
        $mform->addElement('html', '<div class="row mb-3">
            <label class="col-sm-3 col-form-label">' . get_string('lastname') . '</label>
            <div class="col-sm-9">
                <div class="form-control-plaintext">' . s($this->parentdata['lastname']) . '</div>
            </div>
        </div>');
        $mform->addElement('hidden', 'lastname', $this->parentdata['lastname']);
        $mform->setType('lastname', PARAM_TEXT);

        // Phone - display as label (read-only) if exists, otherwise show input
        if (!empty($this->parentdata['phone'])) {
            $mform->addElement('html', '<div class="row mb-3">
                <label class="col-sm-3 col-form-label">' . get_string('phone') . '</label>
                <div class="col-sm-9">
                    <div class="form-control-plaintext">' . s($this->parentdata['phone']) . '</div>
                </div>
            </div>');
            $mform->addElement('hidden', 'phone', $this->parentdata['phone']);
            $mform->setType('phone', PARAM_TEXT);
        } else {
            // Enter mobile phone if no existing phone
            $mform->addElement('text', 'phone', get_string('phone'));
            $mform->setType('phone', PARAM_TEXT);
            $mform->addRule('phone', get_string('required'), 'required', null, 'client');
            $mform->addRule('phone', get_string('invalidusphonenumber', 'local_equipment'), 'regex', "/^\s*(1\d{10}|(?:\+1\s?)?(?:\(?\d{3}\)?[\s.-]?)?\d{3}[\s.-]?\d{4})\s*$/", 'client');
        }

        // Select partnership - pre-filled dropdown (still editable)
        $partnershipdata = local_equipment_get_partnerships_with_courses();
        $partnerships = $DB->get_records_menu('local_equipment_partnership', ['active' => 1], '', 'id,name');
        $partnerships = ['' => get_string('selectpartnership', 'local_equipment')] + $partnerships;
        $mform->addElement(
            'select',
            'partnership',
            get_string('partnership', 'local_equipment'),
            $partnerships,
            ['data-partnerships' => json_encode($partnershipdata)]
        );
        $mform->addRule('partnership', get_string('required'), 'required', null, 'client');

        // Set default partnership if exists
        if (!empty($this->parentdata['partnership'])) {
            $mform->setDefault('partnership', $this->parentdata['partnership']);
        }

        // // We'll need to access the partnership name in the submission form.
        // // We want to save all the information as the parent saw it when submitting the form.
        // $mform->addElement('hidden', 'partnership_name', '');
        // $mform->setType('partnership_name', PARAM_TEXT);


        // Mailing address-related fields - Pre-filled but still editable
        $groupview = false;
        $address = local_equipment_add_address_block($mform, 'mailing', '', false, false, true, false, $groupview, true);
        foreach ($address->elements as $elementname => $element) {
            $mform->addElement($element);
        }
        // Set types for each address input, using the types defined in the address group function.
        foreach ($address->options as $elementname => $options) {
            $mform->setType($elementname, $options['type']);

            if (isset($options['rules'])) {
                $rules = $options['rules'];

                foreach ($rules as $rule => $value) {
                    $mform->addRule($elementname, $value['message'], $rule, $value['format'], 'client');
                }
            }
        }
        // Add rules for each address input, using the rules defined in the address group function.
        foreach ($address->options as $elementname => $element) {
            if (!empty($element['rules'])) {
                $rules = $element['rules'];
                foreach ($rules as $key => $rule) {
                    $mform->addRule($elementname, $rule['message'], $key, $rule['format'], 'client');
                }
            }
        }

        // Set default values from auto-populated parent address data
        if (!empty($this->parentdata['address']['street'])) {
            $mform->setDefault('mailing_streetaddress', $this->parentdata['address']['street']);
        }
        if (!empty($this->parentdata['address']['apartment'])) {
            $mform->setDefault('mailing_apartment', $this->parentdata['address']['apartment']);
        }
        if (!empty($this->parentdata['address']['city'])) {
            $mform->setDefault('mailing_city', $this->parentdata['address']['city']);
        }
        if (!empty($this->parentdata['address']['state'])) {
            $mform->setDefault('mailing_state', $this->parentdata['address']['state']);
        }
        if (!empty($this->parentdata['address']['country'])) {
            $mform->setDefault('mailing_country', $this->parentdata['address']['country']);
        }
        if (!empty($this->parentdata['address']['zipcode'])) {
            $mform->setDefault('mailing_zipcode', $this->parentdata['address']['zipcode']);
        }

        // // Billing address-related fields.
        // $address = local_equipment_add_address_block($mform, 'billing', 'attention', false, false, true, true, $groupview, true);
        // foreach ($address->elements as $elementname => $element) {
        //     $mform->addElement($element);
        // }
        // foreach ($address->options as $elementname => $options) {
        //     $mform->setType($elementname, $options['type']);

        //     if (isset($options['rules'])) {
        //         $rules = $options['rules'];

        //         foreach ($rules as $rule => $value) {
        //             $mform->addRule($elementname, $value['message'], $rule, $value['format'], 'client');
        //         }
        //     }
        // }
        // // Add rules for each address input, using the rules defined in the address group function.
        // foreach ($address->options as $elementname => $element) {
        //     if (!empty($element['rule'])) {
        //         $rules = $element['rule'];
        //         foreach ($rules as $rule) {
        //             $mform->addRule($elementname, get_string($rule), $rule, null, 'client');
        //         }
        //     }
        // }


        // echo '<pre>';
        // var_dump($this->studentdata);
        // echo '</pre>';
        // die();

        // Student-specific sections - Auto-populated from database, no manual entry required
        $studentcount = count($this->studentdata);

        // Hidden field to store total number of students for form processing
        $mform->addElement('hidden', 'students', $studentcount);
        $mform->setType('students', PARAM_INT);

        if (empty($this->studentdata)) {
            $mform->addElement('html', '<div class="alert alert-warning" role="alert">' . get_string('enrolledstudentswouldshowhere', 'local_equipment') . '</div>');
        } else {
            foreach ($this->studentdata as $index => $student) {
                // Student header with auto-populated name
                $mform->addElement(
                    'header',
                    "studentheader_{$index}",
                    get_string('student', 'local_equipment') . ': ' . $student['firstname'] . ' ' . $student['lastname']
                );

                // Set the student ID as a hidden variable for use in later processing.
                $mform->addElement('hidden', "student_id[{$index}]", $student['id']);
                $mform->setType("student_id[{$index}]", PARAM_TEXT);

                // Student first name - display as label (read-only)
                $mform->addElement('html', '<div class="row mb-3">
                    <label class="col-sm-3 col-form-label">' . get_string('firstname') . '</label>
                    <div class="col-sm-9">
                        <div class="form-control-plaintext">' . s($student['firstname']) . '</div>
                    </div>
                </div>');
                $mform->addElement('hidden', "student_firstname[{$index}]", $student['firstname']);
                $mform->setType("student_firstname[{$index}]", PARAM_TEXT);

                // Student last name - display as label (read-only)
                $mform->addElement('html', '<div class="row mb-3">
                    <label class="col-sm-3 col-form-label">' . get_string('lastname') . '</label>
                    <div class="col-sm-9">
                        <div class="form-control-plaintext">' . s($student['lastname']) . '</div>
                    </div>
                </div>');
                $mform->addElement('hidden', "student_lastname[{$index}]", $student['lastname']);
                $mform->setType("student_lastname[{$index}]", PARAM_TEXT);

                // Student email - display as label (read-only)
                $mform->addElement('html', '<div class="row mb-3">
                    <label class="col-sm-3 col-form-label">' . get_string('email') . '</label>
                    <div class="col-sm-9">
                        <div class="form-control-plaintext">' . s($student['email']) . '</div>
                    </div>
                </div>');
                $mform->addElement('hidden', "student_email[{$index}]", $student['email']);
                $mform->setType("student_email[{$index}]", PARAM_EMAIL);

                // TODO: we might add this later on.
                // // Student date of birth - editable date selector (parents can update this)
                // $mform->addElement('date_selector', "student_dob[{$index}]", get_string('dateofbirth', 'local_equipment'));
                // $mform->setType("student_dob[{$index}]", PARAM_INT);
                // $mform->addRule("student_dob[{$index}]", get_string('required'), 'required', null, 'client');

                // // Set default date of birth if available
                // if (!empty($student['dateofbirth'])) {
                //     $mform->setDefault("student_dob[{$index}]", $student['dateofbirth']);
                // }

                // Student courses - display as text list (read-only, auto-populated from enrollments)
                $courselisthtml = '<div class="row mb-3">
                    <label class="col-sm-3 col-form-label">' . get_string('courses', 'core') . '</label>
                    <div class="col-sm-9">
                        <div class="form-control-plaintext">';

                $courseids = [];
                $coursenames = [];
                foreach ($student['courses'] as $course) {
                    $courseids[] = $course->id;
                    $coursenames[] = s($course->fullname);
                }

                $courselisthtml .= implode('<br>', $coursenames);
                $courselisthtml .= '</div></div></div>';

                $mform->addElement('html', $courselisthtml);

                // Hidden field to store course IDs for form processing
                $mform->addElement('hidden', "student_courses[{$index}]", implode(',', $courseids));
                $mform->setType("student_courses[{$index}]", PARAM_RAW);
            }
        }
        // Display any warnings about student eligibility
        if (!empty($this->warnings)) {
            // \core\output\html_writer::div();
            $mform->addElement('html', '<div class="alert border bg-secondary p-3" role="alert">');
            $mform->addElement('html', '<p>' . get_string('somestudentsnottakingcourses', 'local_equipment') . '</p>');
            foreach ($this->warnings as $warning) {
                $mform->addElement('html', '<div class="ms-4">' . $warning . '</div>');
            }
            $mform->addElement('html', '</div>');
        }

        $mform->addElement('header', 'pickup_header', get_string('pickupinformation', 'local_equipment'));

        // Exchange location field - populated from active partnerships
        $exchangelocations = ['' => get_string('selectexchangelocation', 'local_equipment')];
        $partnerships = $DB->get_records('local_equipment_partnership', ['active' => 1], 'name ASC');

        foreach ($partnerships as $partnership) {
            $locationstring = $partnership->name;
            if (!empty($partnership->pickup_streetaddress)) {
                $locationstring .= ' — ' . $partnership->pickup_streetaddress;
                if (!empty($partnership->pickup_city) && !empty($partnership->pickup_state)) {
                    $locationstring .= ', ' . $partnership->pickup_city . ', ' . $partnership->pickup_state;
                    if (!empty($partnership->pickup_zipcode)) {
                        $locationstring .= ' ' . $partnership->pickup_zipcode;
                    }
                }
            }
            $exchangelocations[$partnership->id] = $locationstring;
        }

        $mform->addElement(
            'select',
            'exchange_partnershipid',
            get_string('exchangelocation', 'local_equipment'),
            $exchangelocations
        );
        $mform->addRule('exchange_partnershipid', get_string('required'), 'required', null, 'client');

        // Add informational notice between location and time fields
        $mform->addElement('html', '<div class="alert alert-info mt-2 mb-3" role="alert">' .
            get_string('exchangelocationnotice', 'local_equipment') . '</div>');

        // Pickup date & time field - similar to current functionality but with default selection
        $formattedpickuptimes = ['0' => get_string('haveuscontactyou', 'local_equipment')];
        $pickuptimes = $DB->get_records('local_equipment_pickup', ['status' => 'confirmed']);

        foreach ($pickuptimes as $id => $pickup) {
            $showpickuptime = $pickup->starttime >= time();
            if (!$showpickuptime) {
                continue;
            }

            $partnership = $DB->get_record('local_equipment_partnership', ['id' => $pickup->partnershipid]);
            $partnershipname = $partnership ? $partnership->name : $pickup->pickup_city;

            $datetime = userdate($pickup->starttime, get_string('strftimedate', 'langconfig')) . ' ' .
                userdate($pickup->starttime, get_string('strftimetime', 'langconfig')) . ' - ' .
                userdate($pickup->endtime, get_string('strftimetime', 'langconfig'));

            if ($pickup->pickup_streetaddress) {
                $formattedpickuptimes[$id] = $partnershipname . ' — ' . $datetime;
            }
        }

        $mform->addElement(
            'select',
            'pickup',
            get_string('pickupdatetime', 'local_equipment'),
            $formattedpickuptimes,
            ['multiple' => false, 'size' => 8]
        );
        $mform->addRule('pickup', get_string('required'), 'required', null, 'client');
        $mform->setDefault('pickup', '0'); // Default to "Have us contact you"

        $pickupmethods = local_equipment_get_pickup_methods();
        $mform->addElement('select', 'pickupmethod', get_string('pickupmethod', 'local_equipment'), $pickupmethods);
        $mform->setType('pickupmethod', PARAM_TEXT);
        $mform->addRule('pickupmethod', get_string('required'), 'required', null, 'client');

        // Other pickup person details, which are initially hidden, depending on the pickup method selected.
        $mform->addElement('text', 'pickuppersonname', get_string('pickuppersonname', 'local_equipment'));
        $mform->setType('pickuppersonname', PARAM_TEXT);
        $mform->disabledIf('pickuppersonname', 'pickupmethod', 'neq', 'other');

        $mform->addElement('text', 'pickuppersonphone', get_string('pickuppersonphone', 'local_equipment'));
        $mform->setType('pickuppersonphone', PARAM_TEXT);
        $mform->disabledIf('pickuppersonphone', 'pickupmethod', 'neq', 'other');

        $mform->addElement('textarea', 'pickuppersondetails', get_string('pickuppersondetails', 'local_equipment'));
        $mform->setType('pickuppersondetails', PARAM_TEXT);
        $mform->disabledIf('pickuppersondetails', 'pickupmethod', 'neq', 'other');

        // Parent notes
        $mform->addElement('textarea', 'usernotes', get_string('usernotes', 'local_equipment'));
        $mform->setType('usernotes', PARAM_TEXT);

        $mform->addElement('header', 'agreements_header', get_string('agreementsandconsent', 'local_equipment'));

        // Agreements
        $agreements = local_equipment_get_active_agreements();
        $mform->addElement('hidden', 'agreements', count($agreements));
        $mform->setType('agreements', PARAM_INT);

        // $mform->addElement('html', '<hr class="my-4 border-gray">');
        $i = 0;
        foreach ($agreements as $agreement) {
            $mform->addElement('html', '<h4 class="agreement-title mt-4 mb-4 text-center">' . $agreement->title . '</h4>');
            $mform->addElement('html', '<div class="agreement-content">' . format_text($agreement->contenttext, $agreement->contentformat) . '</div>');

            // Add a hidden field to capture the agreement ID
            $mform->addElement('hidden', "agreement_{$i}_id", $agreement->id);
            $mform->setType("agreement_{$i}_id", PARAM_INT);
            // Add a hidden field to capture the agreement type
            $mform->addElement('hidden', "agreement_{$i}_type", $agreement->agreementtype);
            $mform->setType("agreement_{$i}_type", PARAM_TEXT);
            // Add a hidden field to capture the agreement title
            $mform->addElement('hidden', "agreement_{$i}_title", $agreement->title);
            $mform->setType("agreement_{$i}_title", PARAM_TEXT);

            if ($agreement->agreementtype == 'optinout') {
                $radioarray = array();
                $radioarray[] = $mform->createElement('radio', "agreement_{$i}_choice", '', get_string('optin', 'local_equipment'), 'optin');
                $radioarray[] = $mform->createElement('radio', "agreement_{$i}_choice", '', get_string('optout', 'local_equipment'), 'optout');
                $mform->addGroup($radioarray, "agreement_{$i}_group", '', array(' '), false);

                // Make the field required
                $mform->addRule("agreement_{$i}_group", get_string('required'), 'required', null, 'client');
            }
            $i++;
        }


        // Electronic signature
        if (local_equipment_requires_signature($agreements)) {

            $mform->addElement('html', '<div class="alert alert-warning" role="alert">' . get_string('signaturewarning', 'local_equipment') . '</div>');

            $mform->addElement('text', 'signature', get_string('electronicsignature', 'local_equipment'));
            $mform->setType('signature', PARAM_TEXT);
            $mform->addRule('signature', get_string('required'), 'required', null, 'client');
            // $mform->addRule('signature', get_string('required'), 'regex', '', 'client');
        }

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $OUTPUT, $USER;
        $errors = parent::validation($data, $files);
        $customerrors = [];

        // Validate the signature
        $email = $data['email'];
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];

        // Email and first/last name validation only happens to prevent savvy people from submitting the form with another email or name.
        if ($email != $USER->email) {
            $errors['email_mismatch'] = get_string('inputmismatch', 'local_equipment', "'" . strtolower(get_string('email') . "'"));
            $customerrors[] = new notification($errors['email_mismatch'], notification::NOTIFY_ERROR);
        } else {
            unset($errors['email_mismatch']);
        }
        if ($firstname != $USER->firstname) {
            $errors['firstname_mismatch'] = get_string('inputmismatch', 'local_equipment', "'" . strtolower(get_string('firstname') . "'"));
            $customerrors[] = new notification($errors['firstname_mismatch'], notification::NOTIFY_ERROR);
        } else {
            unset($errors['firstname_mismatch']);
        }
        if ($lastname != $USER->lastname) {
            $errors['lastname_mismatch'] = get_string('inputmismatch', 'local_equipment', "'" . strtolower(get_string('lastname') . "'"));
            $customerrors[] = new notification($errors['lastname_mismatch'], notification::NOTIFY_ERROR);
        } else {
            unset($errors['lastname_mismatch']);
        }

        $phone = $data['phone'];
        $phoneobj = local_equipment_parse_phone_number($phone);

        if (!empty($phoneobj->errors)) {
            $errors['phoneobj'] = get_string('invalidphonenumber', 'local_equipment');
            foreach ($phoneobj->errors as $error) {
                \core\notification::error($error);
            }
        } else {
            unset($errors['phoneobj']);
        }

        $signature = $data['signature'];
        $sigpattern  = '/^\s*' . preg_quote($firstname, '/') . '\s*.+\s*' . preg_quote($lastname, '/') . '\s*$/i';
        $sigmatched = preg_match($sigpattern, $signature);
        if (!$sigmatched) {
            $errors['signature'] = get_string('signaturemismatch', 'local_equipment');
        } else {
            unset($errors['signature']);
        }

        // Partnership validation
        if ($data['partnership'] == '0') {
            $errors['partnership'] = get_string('youmustselectapartnership', 'local_equipment');
        } else {
            unset($errors['partnership']);
        }

        // Existing students validation
        if (empty($this->studentdata)) {
            $errors['student_data'] = get_string('nostudentsinsystem', 'local_equipment');
        } else {
            unset($errors['student_data']);
        }

        // // Student courses validation
        // $sixmonthsago = usergetmidnight(time()) - 15778476;
        // for ($i = 0; $i < $data['students']; $i++) {
        //     if ($data['student_dob'][$i] > $sixmonthsago) {
        //         $errors['student_dob'][$i] = get_string('needstobeatleastsixmonthsold', 'local_equipment', $data['student_firstname'][$i]);
        //         $customerrors[] = new notification(get_string('needstobeatleastsixmonthsold', 'local_equipment', $data['student_firstname'][$i]), notification::NOTIFY_ERROR);
        //     } else {
        //         unset($errors['student_dob'][$i]);
        //     }
        // }

        // Student courses validation
        for ($i = 0; $i < $data['students']; $i++) {
            if (empty($data['student_courses'][$i])) {
                $errors['student_courses'][$i] = get_string('needsatleastonecourseselected', 'local_equipment', $data['student_firstname'][$i]);
                $customerrors[] = new notification(get_string('needsatleastonecourseselected', 'local_equipment', $data['student_firstname'][$i]), notification::NOTIFY_ERROR);
            } else {
                unset($errors['student_courses'][$i]);
            }
        }

        // Agreement validation
        for ($i = 0; $i < $data['agreements']; $i++) {

            $agreement_type = $data["agreement_{$i}_type"];
            $optinout_invalid = ($agreement_type == 'optinout' && !isset($data["agreement_{$i}_choice"]));
            if ($optinout_invalid) {
                $errors["agreement_{$i}_id"] = get_string('pleaseoptinoroutoftheagreement', 'local_equipment', $data["agreement_{$i}_title"]);
                $customerrors[] = new notification(get_string('pleaseoptinoroutoftheagreement', 'local_equipment', $data["agreement_{$i}_title"]), notification::NOTIFY_ERROR);
            } else {
                unset($errors["agreement_{$i}_id"]);
            }
        }

        if (!empty($customerrors)) {
            echo '<br /><br /><br />';
            array_unshift($customerrors, new notification(get_string('formdidnotsubmit', 'local_equipment'), notification::NOTIFY_ERROR));
            foreach ($customerrors as $error) {
                echo $OUTPUT->render($error);
            }
        }

        return $errors;
    }

    /**
     * Load parent data using existing functions
     *
     * @param int $userid Parent user ID
     */
    private function load_parent_data(int $userid): void {
        global $USER, $DB;

        // Get parent data using existing equipment user table
        $equipmentuser = $DB->get_record('local_equipment_user', ['userid' => $userid]);

        $this->parentdata = [
            'email' => $USER->email,
            'firstname' => $USER->firstname,
            'lastname' => $USER->lastname,
            'phone' => $this->get_formatted_phone($USER),
            'partnership' => $equipmentuser->partnershipid ?? '',
            'address' => [
                'street' => $equipmentuser->mailing_streetaddress ?? '',
                'apartment' => $equipmentuser->mailing_apartment ?? '',
                'city' => $equipmentuser->mailing_city ?? '',
                'state' => $equipmentuser->mailing_state ?? '',
                'country' => $equipmentuser->mailing_country ?? '',
                'zipcode' => $equipmentuser->mailing_zipcode ?? ''
            ]
        ];
    }

    /**
     * Load student data using existing functions
     *
     * @param int $parentuserid Parent user ID
     */
    private function load_student_data(int $parentuserid): void {
        global $DB;

        // Get students using existing function
        $students = local_equipment_get_students_of_parent($parentuserid);

        if (empty($students)) {
            $this->studentdata = [];
            return;
        }

        $this->studentdata = [];
        foreach ($students as $student) {
            // Get student's course enrollments
            // $sql = "SELECT c.id, c.fullname, c.shortname, ue.timeend as enddate
            //         FROM {course} c
            //         JOIN {enrol} e ON e.courseid = c.id
            //         JOIN {user_enrolments} ue ON ue.enrolid = e.id
            //         WHERE ue.userid = :userid AND ue.status = 0";

            // $courses = $DB->get_records_sql($sql, ['userid' => $student->id]);

            $ongoing_courses = local_equipment_get_student_courses_with_future_end_dates($student->id);
            // if ($student->id == '3546') {

            //     echo '<pre>';
            //     var_dump($courses);
            //     echo '</pre>';
            //     echo '<br />';
            //     echo '<br />';
            //     echo '<br />';



            // }

            // $allstudents = local_equipment_get_students_in_courses_with_future_end_dates();

            // Only include students with complete data and active courses
            if (!empty($student->firstname) && !empty($student->email) && !empty($ongoing_courses)) {
                // Get date of birth from equipment user table
                $equipmentstudent = $DB->get_record('local_equipment_user', ['userid' => $student->id]);

                $this->studentdata[] = [
                    'id' => $student->id,
                    'firstname' => $student->firstname,
                    'lastname' => $student->lastname,
                    'email' => $student->email,
                    // 'dateofbirth' => $equipmentstudent->dateofbirth ?? null,
                    'courses' => $ongoing_courses
                ];
            } else {
                // Add warning for incomplete student data
                if (empty($student->firstname) || empty($student->email)) {
                    $this->warnings[] = get_string('incompletestudentdata', 'local_equipment');
                } else if (empty($ongoing_courses)) {
                    $this->warnings[] = get_string(
                        'studentnoenrollments',
                        'local_equipment',
                        ($student->firstname ?? '') . ' ' . ($student->lastname ?? '')
                    );
                }
            }
        }
    }

    /**
     * Validate student eligibility for form access
     */
    private function validate_student_eligibility(): void {
        global $USER;

        // Get students using existing function
        $students = local_equipment_get_students_of_parent($USER->id);

        if (empty($students)) {
            $this->studentdata = [];
            return;
        }
        // Check if any students have future course enrollments
        $hasfutureenrollments = false;
        $currenttime = time();

        foreach ($this->studentdata as $student) {
            foreach ($student['courses'] as $course) {
                if (isset($course->enddate) && $course->enddate > $currenttime) {
                    $hasfutureenrollments = true;
                    break 2;
                }
            }
        }

        if (!$hasfutureenrollments) {
            $this->warnings[] = get_string('nofutureenrollments', 'local_equipment');
        }
    }

    /**
     * Get formatted phone number
     *
     * @param object $user User object
     * @return string Formatted phone number
     */
    private function get_formatted_phone($user): string {
        $phone = $user->phone2 ?: $user->phone1;
        if (empty($phone)) {
            return '';
        }

        $phoneobj = local_equipment_parse_phone_number($phone);
        return local_equipment_format_phone_number($phoneobj->phone ?? $phone);
    }
}
