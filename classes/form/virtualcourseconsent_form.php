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

class consent_form extends \moodleform {
    public function definition() {
        global $USER;
        $mform = $this->_form;

        // Parent Information
        $mform->addElement('text', 'firstname', get_string('firstname'), ['value' => $USER->firstname]);
        $mform->addElement('text', 'lastname', get_string('lastname'), ['value' => $USER->lastname]);
        $mform->addElement('text', 'phone', get_string('phone'), ['value' => $USER->phone2 ?: $USER->phone1]);
        $mform->addElement('static', 'email', get_string('email'), $USER->email);

        // Address
        $mform->addElement('text', 'street', get_string('street', 'local_equipment'));
        $mform->addElement('text', 'city', get_string('city', 'local_equipment'));
        $mform->addElement('text', 'state', get_string('state', 'local_equipment'));
        $mform->addElement('text', 'zipcode', get_string('zipcode', 'local_equipment'));

        // Partnership selection
        $partnerships = local_equipment_get_active_partnerships();
        $mform->addElement('select', 'partnership', get_string('partnership', 'local_equipment'), $partnerships);

        // Repeating student elements
        $repeatarray = array(
            $mform->createElement('text', 'student_firstname', get_string('firstname')),
            $mform->createElement('text', 'student_lastname', get_string('lastname')),
            $mform->createElement('text', 'student_email', get_string('email')),
            $mform->createElement('date_selector', 'student_dob', get_string('dateofbirth', 'local_equipment')),
            $mform->createElement('select', 'student_courses', get_string('courses', 'local_equipment'), array())
        );

        $repeateloptions = array(
            'student_firstname' => array('type' => PARAM_TEXT),
            'student_lastname' => array('type' => PARAM_TEXT),
            'student_email' => array('type' => PARAM_EMAIL),
            'student_dob' => array('type' => PARAM_INT),
            'student_courses' => array('type' => PARAM_INT)
        );

        $mform->addElement('repeat', 'studentrepeats', get_string('addstudent', 'local_equipment'), $repeatarray, 3, $repeateloptions, 'add', true);

        // Pickup time selection
        $mform->addElement('select', 'pickup_time', get_string('pickuptime', 'local_equipment'), array());

        // Pickup method
        $pickupmethods = array(
            'self' => get_string('pickupself', 'local_equipment'),
            'other' => get_string('pickupother', 'local_equipment'),
            'ship' => get_string('pickupship', 'local_equipment'),
            'purchased' => get_string('pickuppurchased', 'local_equipment')
        );
        $mform->addElement('select', 'pickup_method', get_string('pickupmethod', 'local_equipment'), $pickupmethods);

        // Other pickup person details (initially hidden)
        $mform->addElement('text', 'pickup_person_name', get_string('pickuppersonname', 'local_equipment'));
        $mform->addElement('text', 'pickup_person_phone', get_string('pickuppersonphone', 'local_equipment'));
        $mform->addElement('textarea', 'pickup_person_details', get_string('pickuppersondetails', 'local_equipment'));

        // Parent notes
        $mform->addElement('textarea', 'parent_notes', get_string('parentnotes', 'local_equipment'));

        // Agreements
        $agreements = local_equipment_get_active_agreements();
        foreach ($agreements as $agreement) {
            $mform->addElement('static', 'agreement_'.$agreement->id, $agreement->title, format_text($agreement->contenttext, $agreement->contentformat));
            if ($agreement->agreementtype == 'optinout') {
                $mform->addElement('radio', 'agreement_'.$agreement->id.'_option', '', get_string('optin', 'local_equipment'), 'optin');
                $mform->addElement('radio', 'agreement_'.$agreement->id.'_option', '', get_string('optout', 'local_equipment'), 'optout');
            }
        }

        // Electronic signature
        if (local_equipment_requires_signature($agreements)) {
            $mform->addElement('text', 'signature', get_string('electronicsignature', 'local_equipment'));
            $mform->addRule('signature', get_string('required'), 'required', null, 'client');
        }

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Add custom validation here

        return $errors;
    }
}
