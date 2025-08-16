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
 * Edit VCC submission form.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/equipment/lib.php');
require_once($CFG->dirroot . '/user/lib.php');

/**
 * Folder conversion handler. This resource handler is called by moodle1_mod_resource_handler
 */
class editvccsubmission_form extends \moodleform {
    // public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $ajaxformdata = null) {
    //     if ($action === null) {
    //         $action = new moodle_url('/local/equipment/vccsubmissions/editvccsubmission.php', ['id' => $customdata['id']]);
    //     }
    //     parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    // }
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $defaultdata = $this->_customdata['data'];
        $studentdata = $this->_customdata['studentdata'];
        $addresstypes = [
            'physical',
            'mailing',
            'pickup',
            'billing',
        ];

        // Using this as a way of validating whether or not this particular vccsubmission was submitted post DB table update.
        if ($defaultdata->email == 0) {
            $userdeets = $DB->get_record('user', ['id' => $defaultdata->userid]);
            $le_userdeets = $DB->get_record('local_equipment_user', ['userid' => $defaultdata->userid], '*', IGNORE_MULTIPLE);

            $partnership_name = $DB->get_field('local_equipment_partnership', 'name', ['id' => $defaultdata->partnershipid]);
            // echo '<br />';
            // echo '<br />';
            // echo '<br />';
            // echo '<pre>';
            // var_dump($le_userdeets);
            // // var_dump("repeatno: $repeatno");
            // // var_dump("defaultdata->id: $defaultdata->id");
            // echo '</pre>';
            // die();

            $defaultdata->email = $userdeets->email;
            $defaultdata->firstname = $userdeets->firstname;
            $defaultdata->lastname = $userdeets->lastname;
            $defaultdata->phone = $userdeets->phone2;
            $defaultdata->partnership_name = $partnership_name === false ? '' : $partnership_name;

            $defaultdata->mailing_extrainput = $le_userdeets->mailing_extrainput ?? '';
            $defaultdata->mailing_streetaddress = $le_userdeets->mailing_streetaddress ?? '';
            $defaultdata->mailing_apartment = $le_userdeets->mailing_apartment ?? '';
            $defaultdata->mailing_city = $le_userdeets->mailing_city ?? '';
            $defaultdata->mailing_state = $le_userdeets->mailing_state ?? '';
            $defaultdata->mailing_country = $le_userdeets->mailing_country ?? '';
            $defaultdata->mailing_zipcode = $le_userdeets->mailing_zipcode ?? '';
            $defaultdata->mailing_extrainstructions = $le_userdeets->mailing_extrainstructions ?? '';
        }

        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump($defaultdata);
        // // var_dump("repeatno: $repeatno");
        // // var_dump("defaultdata->id: $defaultdata->id");
        // echo '</pre>';
        // die();


        // $users = user_get_users_by_id(json_decode($defaultdata->liaisonids));

        // Autocomplete users.
        // $users = local_equipment_auto_complete_users();
        // $mastercourses = local_equipment_get_master_courses('ALL_COURSES_CURRENT');
        // $coursesformatted = $mastercourses->courses_formatted;

        // Add form elements.
        // $mform->addElement('hidden', 'id', $defaultdata->id);
        // $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'vccsubmissionid', $defaultdata->id);
        $mform->setType('vccsubmissionid', PARAM_INT);

        $studentids = json_decode($defaultdata->studentids);
        $students = [];
        foreach ($studentids as $studentid) {
            $student = $DB->get_record('local_equipment_vccsubmission_student', ['id' => $studentid]);
            $student->courseids = json_decode($student->courseids);
            // $student->dob = strtotime($student->dob);
            $students[] = $student;
        }


        $numberofstudents = count($studentids);
        $repeatno = optional_param('repeatno', $numberofstudents, PARAM_INT);
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

        // Time created and last modified.
        $mform->addElement('static', 'timecreated', get_string('timecreated', 'local_equipment'), userdate($defaultdata->timecreated));
        $mform->addElement('static', 'timemodified', get_string('timelastmodified', 'local_equipment'), userdate($defaultdata->timemodified));


        // Profile email.
        $mform->addElement('text', 'email', get_string('email'), ['value' => $defaultdata->email]);
        $mform->setType('email', PARAM_EMAIL);

        // Profile first name.
        $mform->addElement('text', 'firstname', get_string('firstname'), ['value' => $defaultdata->firstname]);
        $mform->setType('firstname', PARAM_TEXT);

        // Profile last name.
        $mform->addElement('text', 'lastname', get_string('lastname'), ['value' => $defaultdata->lastname]);
        $mform->setType('lastname', PARAM_TEXT);

        $phone = $defaultdata->phone;
        $phone_formatted = '';

        if ($phone == '0' || $phone === '') {
            $phone = '';
        } else {
            $phoneobj = local_equipment_parse_phone_number($phone);
            $phone = $phoneobj->phone;
            $phone_formatted = local_equipment_format_phone_number($phone);
        }

        // Enter mobile phone.
        $mform->addElement('text', 'phone', get_string('phone'), ['value' => $phone_formatted]);
        $mform->setType('phone', PARAM_TEXT);
        $mform->addRule('phone', get_string('required'), 'required', null, 'client');
        $mform->addRule('phone', get_string('invalidusphonenumber', 'local_equipment'), 'regex', "/^\s*(1\d{10}|(?:\+1\s?)?(?:\(?\d{3}\)?[\s.-]?)?\d{3}[\s.-]?\d{4})\s*$/", 'client');

        // Select partnership.
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
        $mform->setDefault('partnership', $defaultdata->partnershipid);


        // // We'll need to access the partnership name in the submission form.
        // // We want to save all the information as the parent saw it when submitting the form.
        // $mform->addElement('hidden', 'partnership_name', '');
        // $mform->setType('partnership_name', PARAM_TEXT);


        // Mailing address-related fields.
        // Display all address related fields.

        $groupview = false;
        $address = local_equipment_add_address_block($mform, 'mailing', '', false, false, true, false, $groupview, true, $defaultdata);


        foreach ($address->elements as $elementname => $element) {
            $mform->addElement($element);
        }
        // Set types for each address input, using the types defined in the address group function.
        foreach ($address->options as $elementname => $options) {
            $mform->setType($elementname, $options['type']);
            // $mform->setAttributes($elementname, $options['value']);

            if (isset($options['rules'])) {
                $rules = $options['rules'];

                foreach ($rules as $rule => $value) {
                    $mform->addRule($elementname, $value['message'], $rule, $value['format'], 'client');
                }
            }
        }
        // Add rules for each address input, using the rules defined in the address group function.
        foreach ($address->options as $elementname => $element) {
            if (!empty($element['rule'])) {
                $rules = $element['rule'];
                foreach ($rules as $rule) {
                    $mform->addRule($elementname, get_string($rule), $rule, null, 'client');
                }
            }
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




        // Student-specific input fields.
        // Add one or many students to the form, and update the 'Student' header with corresponding student firstname in real-time with JavaScript.
        $repeatarray = [];
        $repeatoptions = [];

        // Add a hidden input to store selected courses
        $repeatarray['studentheader'] = $mform->createElement('header', 'studentheader', get_string('student', 'local_equipment'), ['class' => 'local-equipment-student-header']);
        $repeatarray['delete'] = $mform->createElement('html', '<button type="button" class="local-equipment-remove-student btn btn-secondary"><i class="fa fa-trash"></i>&nbsp;&nbsp;' . get_string('deletestudent', 'local_equipment') . '</button>');
        $repeatarray['student_firstname'] = $mform->createElement('text', 'student_firstname', get_string('firstname'));
        $repeatarray['student_lastname'] = $mform->createElement('text', 'student_lastname', get_string('lastname'));
        $repeatarray['student_email'] = $mform->createElement('text', 'student_email', get_string('email'));
        // $repeatarray['student_dob'] = $mform->createElement('date_selector', 'student_dob', get_string('dateofbirth', 'local_equipment'));
        $repeatarray['student_courses'] = $mform->createElement('select', 'student_courses', get_string('selectcourses', 'local_equipment'), $coursesformatted_properlynamed, ['multiple' => true, 'size' => 10, 'class' => 'custom-multiselect']);

        // Set types.
        $repeatoptions['students']['type'] = PARAM_INT;
        $repeatoptions['student_firstname']['type'] = PARAM_TEXT;
        $repeatoptions['student_lastname']['type'] = PARAM_TEXT;
        $repeatoptions['student_email']['type'] = PARAM_EMAIL;
        // $repeatoptions['student_dob']['type'] = PARAM_INT;
        $repeatoptions['student_courses']['type'] = PARAM_RAW;

        // Set rules.
        $repeatoptions['student_firstname']['rule'] = 'required';
        $repeatoptions['student_lastname']['rule'] = 'required';
        // $repeatoptions['student_dob']['rule'] = 'required';
        // $repeatoptions['student_courses']['rule'] = 'required';

        // Set other options.
        $repeatoptions['studentheader']['expanded'] = false; // This is not working for some reason.



        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeatoptions,
            'students',
            $addfieldsname,
            1,
            get_string('addstudent', 'local_equipment'),
        );

        // Set defaults for the student repeat elements.
        // $defaults = [];
        // for ($i = 0; $i < $numberofstudents; $i++) {
        //     $defaults["student_firstname[$i]"] = $students[$i]->firstname;
        //     $defaults["student_lastname[$i]"] = $students[$i]->lastname;
        //     $defaults["student_email[$i]"] = $students[$i]->email;
        //     $defaults["student_dob[$i]"] = $students[$i]->dateofbirth;
        //     $defaults["student_courses[$i]"] = $students[$i]->courseids; // Assuming $courses[$i] is an array of course IDs
        // }

        $mform->setDefaults($studentdata);

        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump($defaults);
        // echo '</pre>';
        // die();





        // Pickup input fields.
        $formattedpickuplocations = ['0' => get_string('contactusforpickup', 'local_equipment')];
        $formattedpickuptimes = ['0' => get_string('contactusforpickup', 'local_equipment')];
        $pickuptimedata = local_equipment_get_partnerships_with_pickuptimes();
        $pickuptimes = $DB->get_records('local_equipment_pickup', ['status' => 'confirmed']);


        $i = 0;
        foreach ($pickuptimes as $id => $pickup) {
            $partnership = $DB->get_record('local_equipment_partnership', ['id' => $pickup->partnershipid]);

            $datetime = userdate($pickup->pickupdate, get_string('strftimedate', 'langconfig')) . ' ' .
                userdate($pickup->starttime, get_string('strftimetime', 'langconfig')) . ' - ' .
                userdate($pickup->endtime, get_string('strftimetime', 'langconfig'));

            $pattern = '/#(.*?)#/';
            $name = $partnership->pickup_city;

            if (
                preg_match($pattern, $partnership->pickup_extrainstructions, $matches)
                && $partnership->pickup_streetaddress
                && $partnership->pickup_city
                && $partnership->pickup_state
                && $partnership->pickup_zipcode
            ) {
                $name = $partnership->locationname = $matches[1];
                $partnership->pickup_extrainstructions = trim(preg_replace($pattern, '', $partnership->pickup_extrainstructions, 1));
            }
            if ($partnership->pickup_streetaddress) {
                $formattedpickuplocations[$id] = "$name — $datetime — $partnership->pickup_streetaddress, $partnership->pickup_city, $partnership->pickup_state $partnership->pickup_zipcode";
                if (isset($pickuptimedata[$id])) {
                    $formattedpickuptimes[$id] = $pickuptimedata[$id][$i];
                    $i++;
                }
            }
        }

        $mform->addElement(
            'select',
            'pickup',
            get_string('pickuplocationtime', 'local_equipment'),
            $formattedpickuplocations,
            ['multiple' => false, 'size' => 10]
        );
        $mform->addRule('pickup', get_string('required'), 'required', null, 'client');
        $mform->setDefault('pickup', $defaultdata->pickup_locationtime);

        $pickupmethods = local_equipment_get_pickup_methods();
        $mform->addElement('select', 'pickupmethod', get_string('pickupmethod', 'local_equipment'), $pickupmethods);
        $mform->setType('pickupmethod', PARAM_TEXT);
        $mform->addRule('pickupmethod', get_string('required'), 'required', null, 'client');
        $mform->setDefault('pickup', $defaultdata->pickup_locationtime);

        // Other pickup person details, which are initially hidden, depending on the pickup method selected.
        $mform->addElement('text', 'pickuppersonname', get_string('pickuppersonname', 'local_equipment'));
        $mform->setType('pickuppersonname', PARAM_TEXT);
        $mform->disabledIf('pickuppersonname', 'pickupmethod', 'neq', 'other');
        $mform->setDefault('pickuppersonname', $defaultdata->pickuppersonname);

        $mform->addElement('text', 'pickuppersonphone', get_string('pickuppersonphone', 'local_equipment'));
        $mform->setType('pickuppersonphone', PARAM_TEXT);
        $mform->disabledIf('pickuppersonphone', 'pickupmethod', 'neq', 'other');
        $mform->setDefault('pickuppersonphone', $defaultdata->pickuppersonphone);

        $mform->addElement('textarea', 'pickuppersondetails', get_string('pickuppersondetails', 'local_equipment'));
        $mform->setType('pickuppersondetails', PARAM_TEXT);
        $mform->disabledIf('pickuppersondetails', 'pickupmethod', 'neq', 'other');
        $mform->setDefault('pickuppersondetails', $defaultdata->pickuppersondetails);

        // Parent notes
        $mform->addElement('textarea', 'usernotes', get_string('usernotes', 'local_equipment'));
        $mform->setType('usernotes', PARAM_TEXT);
        $mform->setDefault('usernotes', $defaultdata->usernotes);

        // Admin notes
        $mform->addElement('textarea', 'adminnotes', get_string('adminnotes', 'local_equipment'));
        $mform->setType('adminnotes', PARAM_TEXT);
        $mform->setDefault('adminnotes', $defaultdata->adminnotes);

        // Agreements
        $agreements = local_equipment_get_active_agreements();
        $mform->addElement('hidden', 'agreements', count($agreements));
        $mform->setType('agreements', PARAM_INT);

        $mform->addElement('html', '<hr class="my-4 border-gray">');
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
            if ($defaultdata->electronicsignature == 0) {
                $mform->addElement('html', '<div class="alert alert-danger" role="alert">' . get_string('thesignaturewasnotsaved', 'local_equipment') . '</div>');
            } else {
                $mform->setDefault('signature', $defaultdata->electronicsignature);
            }
            // $mform->disabledIf('signature', 'signature', 'eq', '0');
        }
        $this->add_action_buttons(true);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Add custom validation if needed.
        return $errors;
    }
}
