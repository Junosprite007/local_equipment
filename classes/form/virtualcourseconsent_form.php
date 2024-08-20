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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/equipment/lib.php');

class virtualcourseconsent_form extends \moodleform {
    public function definition() {
        global $USER, $DB;
        $mform = $this->_form;
        // Prepare default values for repeated elements
        $defaultvalues = $this->_customdata['defaultvalues'] ?? [];
        $repeatno = empty($defaultvalues) ? 1 : count($defaultvalues);


        // Parent-specific input fields.
        $mform->addElement('static', 'email', get_string('email'), $USER->email);

        $mform->addElement('text', 'firstname', get_string('firstname'), ['value' => $USER->firstname]);
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'lastname', get_string('lastname'), ['value' => $USER->lastname]);
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', get_string('required'), 'required', null, 'client');

        $phone = $USER->phone2 ?: $USER->phone1;
        $phone = local_equipment_parse_phone_number($phone);
        $phone = local_equipment_format_phone_number($phone);

        $mform->addElement('text', 'phone', get_string('phone'), ['value' => $phone]);
        $mform->setType('phone', PARAM_TEXT);
        $mform->addRule('phone', get_string('required'), 'required', null, 'client');

        // Get address input fields in a 3 column 2 row layout.
        $addressgroup = local_equipment_add_address_group($mform, 'mailing', get_string('mailingaddress', 'local_equipment'));
        $mform->addElement($addressgroup['element']);
        // Set types for each address input, using the types defined in the address group function.
        foreach ($addressgroup['types'] as $elementname => $type) {
            $mform->setType($elementname, $type);
        }
        // Add rules for each address input, using the rules defined in the address group function.
        $grouprules = [];
        foreach ($addressgroup['rules'] as $elementname => $rules) {
            if (!empty($rules)) {
                $grouprules[$elementname] = array_map(function ($rule) {
                    return array(get_string($rule), $rule, null, 'client');
                }, $rules);
            }
        }
        if (!empty($grouprules)) {
            $mform->addGroupRule('mailing', $grouprules);
        }

        // Partnership dropdown menu that auto-populates the courses available using the 'data-partnerships' attribute. in the JavaScript.
        $partnershipdata = local_equipment_get_partnerships_with_courses();
        $partnerships = $DB->get_records_menu('local_equipment_partnership', ['active' => 1], '', 'id,name');
        $partnerships = [0 => get_string('selectpartnership', 'local_equipment')] + $partnerships;
        $mform->addElement(
            'select',
            'partnership',
            get_string('partnership', 'local_equipment'),
            $partnerships,
            ['data-partnerships' => json_encode($partnershipdata)]
        );
        $mform->addRule('partnership', get_string('required'), 'required', null, 'client');


        // Student-specific input fields.
        // Add one or many students to the form, and update the 'Student' header with corresponding student firstname in real-time with JavaScript.
        $repeatarray = [];
        $repeatoptions = [];
        // $repeatno = optional_param('repeatno', 1, PARAM_INT);
        $courseattributes = [
            'multiple' => true,
            'size' => 10,
            'class' => 'student-courses'
        ];
        $mform->addElement('hidden', 'students', $repeatno);
        // Add this line to pass the attributes to JavaScript.
        // Not sure if the json_encode is necessary.
        $mform->addElement('hidden', 'course_attributes', json_encode($courseattributes));
        $mform->setType('course_attributes', PARAM_RAW);

        $repeatarray = [
            'students' => $mform->createElement('header', 'studentheader', get_string('student', 'local_equipment'), ['class' => 'local-equipment-student-header']),
            'studentheader' => $mform->createElement('html', '<button type="button" class="local-equipment-remove-student btn btn-danger"><i class="fa fa-trash"></i></button>'),
            'student_firstname' => $mform->createElement('text', 'student_firstname', get_string('firstname')),
            'student_lastname' => $mform->createElement('text', 'student_lastname', get_string('lastname')),
            'student_email' => $mform->createElement('text', 'student_email', get_string('email')),
            'student_dob' => $mform->createElement('date_selector', 'student_dob', get_string('dateofbirth', 'local_equipment')),
            'student_courses' => $mform->createElement(
                'select',
                'student_courses',
                get_string('selectcourses', 'local_equipment'),
                array(),
                $courseattributes
            ),
        ];

        $repeatoptions = [
            'students' => ['type' => PARAM_INT],
            'studentheader' => ['header' => true],
            'student_firstname' => ['type' => PARAM_TEXT, 'rule' => 'required'],
            'student_lastname' => ['type' => PARAM_TEXT, 'rule' => 'required'],
            'student_email' => ['type' => PARAM_EMAIL],
            'student_dob' => ['type' => PARAM_INT, 'rule' => 'required'],
            'student_courses' => ['type' => PARAM_INT, 'rule' => 'required'] + $courseattributes,
        ];

        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeatoptions,
            'students',
            'add_student',
            1,
            get_string('addstudent', 'local_equipment'),
            false,
            'delete_partnership'
        );

        // Set default values for repeated elements
        if (!empty($defaultvalues)) {
            foreach ($defaultvalues as $index => $values) {
                foreach ($values as $fieldname => $value) {
                    $elementname = $fieldname . "[$index]";
                    $mform->setDefault($elementname, $value);
                }
            }
        }


        // Pickup-specific input fields.
        $pickuplocations = $DB->get_records('local_equipment_partnership', ['active' => 1], '', 'id,name,instructions_pickup,streetaddress_pickup,city_pickup,state_pickup,zipcode_pickup');
        $formattedpickuplocations = ['0' => get_string('selectpickuplocation', 'local_equipment')];


        foreach ($pickuplocations as $id => $location) {
            $pattern = '/#(.*?)#/';
            $name = $location->city_pickup;

            if (
                preg_match($pattern, $location->instructions_pickup, $matches)
                && $location->streetaddress_pickup
                && $location->city_pickup
                && $location->state_pickup
                && $location->zipcode_pickup
            ) {
                $name = $location->locationname = $matches[1];
                $location->instructions_pickup = trim(preg_replace($pattern, '', $location->instructions_pickup, 1));
            }
            if ($location->streetaddress_pickup) {
                $formattedpickuplocations[$id] = "$name – $location->streetaddress_pickup, $location->city_pickup, $location->state_pickup $location->zipcode_pickup";
            }
        }

        // Pickup time dropdown menu that auto-populates the pickup times available using the 'data-pickuptimes' attribute sent to the JavaScript.
        $pickuptimedata = local_equipment_get_partnerships_with_pickuptimes();
        $mform->addElement(
            'select',
            'pickuplocation',
            get_string('pickuplocation', 'local_equipment'),
            $formattedpickuplocations,
            ['data-pickuptimes' => json_encode($pickuptimedata)]
        );
        $mform->addRule('pickuplocation', get_string('required'), 'required', null, 'client');

        // Even though the id=0 option should be selectable, so entering this field is required, but we don't need to actually 'require' it.
        $mform->addElement(
            'select',
            'pickuptime',
            get_string('pickuptime', 'local_equipment'),
            [0 => get_string('contactusforpickup', 'local_equipment')]
        );
        $mform->addRule('pickuptime', get_string('required'), 'required', null, 'client');

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
        foreach ($agreements as $agreement) {
            $mform->addElement('static', 'agreement_'.$agreement->id, $agreement->title, format_text($agreement->contenttext, $agreement->contentformat));
            if ($agreement->agreementtype == 'optinout') {
                $mform->addElement('radio', 'agreement_'.$agreement->id.'_option', '', get_string('optin', 'local_equipment'), 'optin');
                $mform->addElement('radio', 'agreement_'.$agreement->id.'_option', '', get_string('optout', 'local_equipment'), 'optout');
                $mform->addRule('agreement_' . $agreement->id . '_option', get_string('required'), 'required', null, 'client');
            }
        }

        // Electronic signature
        if (local_equipment_requires_signature($agreements)) {
            $mform->addElement('text', 'signature', get_string('electronicsignature', 'local_equipment'));
            $mform->setType('signature', PARAM_TEXT);
            $mform->addRule('signature', get_string('required'), 'required', null, 'client');
        }

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // // Validate phone number
        // $phone = local_equipment_parse_phone_number($data['phone']);
        // $parsed = local_equipment_format_phone_number($phone);

        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump($phone);
        // var_dump($parsed);
        // echo '</pre>';
        // die();
        // if (!empty($phone->errors)) {
        //     $errors['phone'] = implode('<br />', $phone->errors);
        // }

        // Validate student email addresses
        // for ($i = 0; $i < $data['students']; $i++) {
        //     $email = $data['student_email'][$i];
        //     if (empty($email)) {
        //         $email = local_equipment_generate_student_email($data['email'], $data['student_firstname'][$i]);
        //         // You might want to inform the user that an email was auto-generated
        //     } else if (!validate_email($email)) {
        //         $errors["student_email[$i]"] = get_string('invalidemail');
        //     }
        // }

        // // Validate signature if required
        // if (!empty($data['signature'])) {
        //     $fullname = $data['firstname'] . ' ' . $data['lastname'];
        //     $matchname = $data['signature'];
        //     $pattern = '/^\s*(\w+)\s+.*\s+(\w+)\s*$/';

        //     if (preg_match($pattern, $fullname, $matches)) {
        //         $first_name = $matches[1];
        //         $last_name = $matches[2];
        //         echo "First name: " . $first_name . "\n";
        //         echo "Last name: " . $last_name . "\n";
        //     } else {
        //         echo "No match found.";
        //     }



        //     if (strcasecmp($data['signature'], $fullname) !== 0) {
        //         $errors['signature'] = get_string('invalidsignature', 'local_equipment');
        //     }
        // }

        // if ($data['partnership'] == 0) {
        //     $errors['partnership'] = get_string('required');
        // }

        // foreach ($data['students'] as $i => $student) {
        //     if (empty($student['courses'])) {
        //         $errors["courses[$i]"] = get_string('required');
        //     }
        // }

        return $errors;
    }
}
