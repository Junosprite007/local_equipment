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

use core_customfield\field;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/equipment/lib.php');

class vccsubmission_form extends \moodleform {
    public function definition() {
        global $USER, $DB;
        $mform = $this->_form;
        $customdata = $this->_customdata;
        echo '<br />';
        echo '<br />';
        var_dump('$customdata: ');
        var_dump($customdata);
        echo '</pre>';
        // Prepare default values for repeated elements
        $defaultvalues = $this->_customdata['defaultvalues'] ?? [];
        $repeatno = empty($defaultvalues) ? 1 : count($defaultvalues);

        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump($this->_customdata);
        // echo '</pre>';
        // die();



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

        $regrules = $mform->getRegisteredRules();
        $att = $mform->getAttributes(true);

        // echo '<br />';
        // echo '<br />';
        // echo '<br />';

        // Display all address related fields.
        // Working with group view right now.
        $groupview = true;
        $address = local_equipment_add_address_block($mform, 'mailing', 'attention', false, false, true, true, $groupview, true);
        foreach ($address->elements as $elementname => $element) {
            $mform->addElement($element);
            if ($address->isgrouped) {
            }
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
            'class' => 'student-courses',
        ];
        // $mform->addElement('hidden', 'students', $repeatno);
        // Add this line to pass the attributes to JavaScript.
        // Not sure if the json_encode is necessary.
        $mform->addElement('hidden', 'course_attributes', json_encode($courseattributes));
        $mform->setType('course_attributes', PARAM_RAW);

        // Add a hidden input to store selected courses
        $mform->addElement('hidden', 'selectedcourses', '', array('id' => 'id_selectedcourses'));
        $mform->setType('selectedcourses', PARAM_RAW);
        // $mform->setDefault('selectedcourses', '');

        // $repeatarray = [
        //     'students' => $mform->createElement('header', 'studentheader', get_string('student', 'local_equipment'), ['class' => 'local-equipment-student-header']),
        //     'studentheader' => $mform->createElement('html', '<button type="button" class="local-equipment-remove-student btn btn-danger"><i class="fa fa-trash"></i></button>'),
        //     'student_firstname' => $mform->createElement('text', 'student_firstname', get_string('firstname')),
        //     'student_lastname' => $mform->createElement('text', 'student_lastname', get_string('lastname')),
        //     'student_email' => $mform->createElement('text', 'student_email', get_string('email')),
        //     'student_dob' => $mform->createElement('date_selector', 'student_dob', get_string('dateofbirth', 'local_equipment')),
        //     'student_courses' => $mform->createElement(
        //         'select',
        //         'student_courses',
        //         get_string('selectcourses', 'local_equipment'),
        //         array(),
        //         $courseattributes
        //     ),
        // ];

        $repeatarray['studentheader'] = $mform->createElement('header', 'studentheader', get_string('student', 'local_equipment'), ['class' => 'local-equipment-student-header']);
        $repeatarray['delete'] = $mform->createElement('html', '<button type="button" class="local-equipment-remove-student btn btn-secondary"><i class="fa fa-trash"></i>&nbsp;&nbsp;' . get_string('deletestudent', 'local_equipment') . '</button>');
        $repeatarray['student_firstname'] = $mform->createElement('text', 'student_firstname', get_string('firstname'));
        $repeatarray['student_lastname'] = $mform->createElement('text', 'student_lastname', get_string('lastname'));
        $repeatarray['student_email'] = $mform->createElement('text', 'student_email', get_string('email'));
        $repeatarray['student_dob'] = $mform->createElement('date_selector', 'student_dob', get_string('dateofbirth', 'local_equipment'));

        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // // var_dump($mform->_attributes);
        // var_dump($mform->getRegisteredTypes());
        // var_dump($mform->getRegisteredRules());
        // echo '</pre>';

        // $repeatarray['student_____courses'] = $mform->createElement(
        //     'select',
        //     'student_courses',
        //     get_string('selectcourses', 'local_equipment'),
        //     array(),
        //     $courseattributes
        // );
        $repeatarray['student_courses'] = $mform->createElement(
            'select',
            'student_courses',
            get_string('selectcourses', 'local_equipment'),
            array(),
            $courseattributes + array('data-student-index' => '') // We'll set this index in JavaScript
        );

        // Registered rules:
        // array(14) {
        //     [0]=>
        //     string(8) "required"
        //     [1]=>
        //     string(9) "maxlength"
        //     [2]=>
        //     string(9) "minlength"
        //     [3]=>
        //     string(11) "rangelength"
        //     [4]=>
        //     string(5) "email"
        //     [5]=>
        //     string(5) "regex"
        //     [6]=>
        //     string(11) "lettersonly"
        //     [7]=>
        //     string(12) "alphanumeric"
        //     [8]=>
        //     string(7) "numeric"
        //     [9]=>
        //     string(13) "nopunctuation"
        //     [10]=>
        //     string(7) "nonzero"
        //     [11]=>
        //     string(11) "positiveint"
        //     [12]=>
        //     string(8) "callback"
        //     [13]=>
        //     string(7) "compare"
        // }

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
        // $repeatoptions['studentheader']['header'] = true;
        $repeatoptions['studentheader']['expanded'] = false; // This is not working for some reason.
        $repeatoptions['student_courses'] = array_merge($repeatoptions['student_courses'], $courseattributes);
        // $repeatoptions['student_courses']['default'] = ['508'];
        // 'student_firstname[0]' => 'John'

        // $repeatoptions = [
        //     'students' => ['type' => PARAM_INT],
        //     'studentheader' => ['header' => true],
        //     'student_firstname' => ['type' => PARAM_TEXT, 'rule' => 'required'],
        //     'student_lastname' => ['type' => PARAM_TEXT, 'rule' => 'required'],
        //     'student_email' => ['type' => PARAM_EMAIL],
        //     'student_dob' => ['type' => PARAM_INT, 'rule' => 'required'],
        //     'student_courses' => ['type' => PARAM_INT, 'rule' => 'required'] + $courseattributes,
        // ];

        // $repeatoptions['student_courses']['type'] = PARAM_INT;
        // $repeatoptions['student_courses']['rule'] = 'required';
        // $repeatoptions['student_courses']['rule'] = 'required';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump($repeatoptions);
        // echo '</pre>';

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

        // $element = $mform->getElement('selectedcourses');
        // $att = $element->getAttribute('selectedcourses');
        // $atts = $element->getAttributes('selectedcourses');

        // echo '<pre>';
        // // var_dump($mform->_attributes);
        // var_dump($mform->elementExists('student_courses[0]'));
        // // var_dump($mform->getRegisteredRules());
        // echo '</pre>';


        // $mform->getElement('student_courses[0]')->setSelected(['508']);

        // Set default values for repeated elements
        // if (!empty($defaultvalues)) {
        //     foreach ($defaultvalues as $index => $values) {
        //         foreach ($values as $fieldname => $value) {
        //             $elementname = $fieldname . "[$index]";
        //             if ($fieldname === 'student_courses') {
        //                 $element = $mform->getElement($elementname);
        //                 if ($element && method_exists($element, 'setSelected')) {
        //                     $element->setSelected($value);
        //                 }
        //             } else {
        //                 $mform->setDefault($elementname, $value);
        //             }
        //         }
        //     }
        // }

        // Read the selectedcourses hidden input and set the default values.

        // if (!empty($customdata['selectedcourses'])) {
        //     $selectedcourses = json_decode($customdata['selectedcourses']);
        //     echo '<br />';
        //     echo '<br />';
        //     var_dump('$selectedcourses: ');
        //     echo '<pre>';
        //     var_dump($selectedcourses);
        //     echo '</pre>';

        //     $defaultvalues = [
        //         'student_firstname[0]' => 'John',
        //         'student_lastname[0]' => 'Doe',
        //         'student_email[0]' => 'john.doe@example.com',
        //         'student_courses[0]' => [511],
        //     ];

        //     $mform->setDefaults($defaultvalues);

            // foreach ($selectedcourses as $index => $values) {

            //     foreach ($values as $key => $value) {
            //         echo '<br />';
            //         echo '<br />';
            //         echo '<pre>';
            //         var_dump('$key: ');
            //         var_dump($key);
            //         var_dump('$value: ');
            //         var_dump($value);
            //         echo '</pre>';
            //         $fieldname = 'student_courses';
            //         // The extra [] is because this if for a multiple select element.
            //         $elementname = $fieldname . "[$index]";
            //         var_dump('$elementname: ', $elementname);
            //         // die();
            //         // if ($fieldname === 'student_courses') {
            //         //     $element = $mform->getElement($elementname);
            //         //     if ($element && method_exists($element, 'setSelected')) {
            //         //         $element->setSelected($value);
            //         //     }
            //         // } else {
            //         $mform->setDefaults('student_courses[0]', '511');
            //         // }
            //     }
            // }
        // }
        // Set default values for student_courses
        // if (!empty($customdata['selectedcourses'])) {
        //     echo '<br />';
        //     echo '<br />';
        //     echo '<br />';
        //     var_dump('$selectedcourses: ');
        //     echo '<pre>';
        //     $selectedcourses = json_decode($customdata['selectedcourses']);
        //     var_dump($selectedcourses);
        //     // $selectedcourses = json_decode($customdata['selectedcourses'], true);
        //     foreach ($selectedcourses as $index => $courses) {
        //         var_dump('$index: ');
        //         var_dump($index);
        //         var_dump('$courses: ');
        //         var_dump($courses);
        //         // $mform->setDefault("student_courses[$index][]", $courses);
        //         $mform->setDefault("student_courses[0][]", ['511']);
        //     }
        //     echo '</pre>';
        // }
        // if (!empty($selectedcourses)) {
        //     foreach ($selectedcourses as $index => $courses) {
        //         $elementname = 'student_courses[' . $index . ']';
        //         $element = $mform->getElement($elementname);
        //         if ($element && method_exists($element, 'setSelected')) {
        //             $element->setSelected($courses);
        //         }
        //     }
        // }

        // // Set default values for repeated elements
        // if (!empty($defaultvalues)) {
        //     foreach ($defaultvalues as $index => $values) {
        //         foreach ($values as $fieldname => $value) {
        //             $elementname = $fieldname . "[$index]";
        //             if ($fieldname === 'student_courses') {
        //                 $mform->getElement($elementname)->setSelected($value);
        //             } else {
        //                 $mform->setDefault($elementname, $value);
        //             }
        //         }
        //     }
        // }

        // // Set default values for repeated elements
        // if (!empty($defaultvalues)) {
        //     foreach ($defaultvalues as $index => $values) {
        //         foreach ($values as $fieldname => $value) {
        //             $elementname = $fieldname . "[$index]";
        //             $mform->setDefault($elementname, $value);
        //         }
        //     }
        // }


        // Pickup-specific input fields.
        $pickuplocations = $DB->get_records('local_equipment_partnership', ['active' => 1], '', 'id,name,instructions_pickup,streetaddress_pickup,city_pickup,state_pickup,zipcode_pickup');
        $formattedpickuplocations = ['0' => get_string('selectpickuplocation', 'local_equipment')];
        $formattedpickuptimes = ['0' => get_string('contactusforpickup', 'local_equipment')];


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
            $formattedpickuptimes,
            ['data-pickuptimes' => json_encode($pickuptimedata)]
        );
        // $mform->addRule('pickuptime', get_string('required'), 'required', null, 'client');

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
        // $agreements = local_equipment_create_agreement_elements($mform, $agreements);
        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump('$agreements: ');
        // var_dump($agreements);
        // echo '</pre>';
        foreach ($agreements as $agreement) {
            $mform->addElement('static', 'agreement_' . $agreement->id, $agreement->title, format_text($agreement->contenttext, $agreement->contentformat));
            if ($agreement->agreementtype == 'optinout') {
                $radioarray = array();
                $radioarray[] = $mform->createElement('radio', "optionchoice_$agreement->id", '', get_string('optin', 'local_equipment'), 'optin');
                $radioarray[] = $mform->createElement('radio', "optionchoice_$agreement->id", '', get_string('optout', 'local_equipment'), 'optout');
                $mform->addGroup($radioarray, "optiongroup_$agreement->id", '', array(' '), false);

                // Make the field required
                $mform->addRule("optiongroup_$agreement->id", get_string('required'), 'required', null, 'client');

                // Set a default value (optional)
                // $mform->setDefault('optionchoice', 'optin');
            }
        }


        // Electronic signature
        if (local_equipment_requires_signature($agreements)) {

            $mform->addElement('text', 'signature', get_string('electronicsignature', 'local_equipment'));
            $mform->setType('signature', PARAM_TEXT);
            $mform->addRule('signature', get_string('required'), 'required', null, 'client');
            // $mform->addRule('signature', get_string('required'), 'regex', '', 'client');
        }

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

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


        return $errors;
    }
}
