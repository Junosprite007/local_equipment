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

use core\output\notification;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/equipment/lib.php');

class vccsubmission_form extends \moodleform {
    public function definition() {
        global $USER, $DB;
        $mform = $this->_form;
        $customdata = $this->_customdata;

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


        // Parent-specific input fields.
        $mform->addElement('static', 'email', get_string('email'), $USER->email);

        // Enter first name.
        $mform->addElement('text', 'firstname', get_string('firstname'), ['value' => $USER->firstname]);
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', get_string('required'), 'required', null, 'client');

        // Enter last name.
        $mform->addElement('text', 'lastname', get_string('lastname'), ['value' => $USER->lastname]);
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', get_string('required'), 'required', null, 'client');

        $phone = $USER->phone2 ?: $USER->phone1;
        if (empty($phone)) {
            $phone = '';
        } else {
            $phone = local_equipment_parse_phone_number($phone);
            $phone = local_equipment_format_phone_number($phone);
        }

        // Enter mobile phone.
        $mform->addElement('html', '<div class="alert alert-warning" role="alert">' . get_string('wecurrentlyonlyacceptusphonenumbers', 'local_equipment') . '</div>');
        $mform->addElement('text', 'phone', get_string('phone'), ['value' => $phone]);
        $mform->setType('phone', PARAM_TEXT);
        $mform->addRule('phone', get_string('required'), 'required', null, 'client');
        // $mform->addRule('phone', get_string('invalidphonenumber', 'local_equipment'), 'regex', "/^(?:\+1\s?)?(\(?\d{3}\)?[\s.-]?)?\d{3}[\s.-]?\d{4}$/", 'client');
        $mform->addRule('phone', get_string('invalidusphonenumber', 'local_equipment'), 'regex', "/^\s*(1\d{10}|(?:\+1\s?)?(?:\(?\d{3}\)?[\s.-]?)?\d{3}[\s.-]?\d{4})\s*$/", 'client');

        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump($mform->getRegisteredRules());
        // echo '</pre>';

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


        // Mailing address-related fields.
        // Display all address related fields.
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
        $repeatarray['student_dob'] = $mform->createElement('date_selector', 'student_dob', get_string('dateofbirth', 'local_equipment'));
        $repeatarray['student_courses'] = $mform->createElement('select', 'student_courses', get_string('selectcourses', 'local_equipment'), $coursesformatted, ['multiple' => true, 'size' => 10]);

        // Set types.
        $repeatoptions['students']['type'] = PARAM_INT;
        $repeatoptions['student_firstname']['type'] = PARAM_TEXT;
        $repeatoptions['student_lastname']['type'] = PARAM_TEXT;
        $repeatoptions['student_email']['type'] = PARAM_EMAIL;
        $repeatoptions['student_dob']['type'] = PARAM_INT;
        $repeatoptions['student_courses']['type'] = PARAM_RAW;

        // Set rules.
        $repeatoptions['student_firstname']['rule'] = 'required';
        $repeatoptions['student_lastname']['rule'] = 'required';
        $repeatoptions['student_dob']['rule'] = 'required';
        $repeatoptions['student_courses']['rule'] = 'required';

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
        $mform->setDefault('pickup', '-1');

        $pickupmethods = array(
            'self' => get_string('pickupself', 'local_equipment'),
            'other' => get_string('pickupother', 'local_equipment'),
            'ship' => get_string('pickupship', 'local_equipment'),
            'purchased' => get_string('pickuppurchased', 'local_equipment')
        );
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
            // $mform->addRule('signature', get_string('required'), 'regex', '', 'client');
        }

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $OUTPUT;
        $errors = parent::validation($data, $files);
        $customerrors = [];

        // Validate the signature
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
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

        // Student courses validation
        $sixmonthsago = usergetmidnight(time()) - 15778476;
        for ($i = 0; $i < $data['students']; $i++) {
            if ($data['student_dob'][$i] > $sixmonthsago) {
                $errors['student_dob'][$i] = get_string('needstobeatleastsixmonthsold', 'local_equipment', $data['student_firstname'][$i]);
                $customerrors[] = new notification(get_string('needstobeatleastsixmonthsold', 'local_equipment', $data['student_firstname'][$i]), notification::NOTIFY_ERROR);
            } else {
                unset($errors['student_dob'][$i]);
            }
        }

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
            echo '<br />';
            echo '<br />';
            echo '<br />';
            array_unshift($customerrors, new notification(get_string('formdidnotsubmit', 'local_equipment'), notification::NOTIFY_ERROR));
            foreach ($customerrors as $error) {
                echo $OUTPUT->render($error);
            }
        }

        return $errors;
    }
}
