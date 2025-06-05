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
 * Equipment exchange form.
 *
 * @package     local_equipment
 * @copyright   2025 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Equipment exchange form class.
 */
class exchange_form extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        global $USER, $D;

        $mform = $this->_form;
        $customdata = $this->_customdata;

        // Get available exchanges for the current user
        $exchanges = local_equipment_get_available_exchanges($USER->id);

        if (empty($exchanges)) {
            $mform->addElement('html', '<div class="alert alert-info">' .
                get_string('noexchangesavailable', 'local_equipment') . '</div>');
            return;
        }

        // Build exchange options
        $exchangeoptions = [];
        foreach ($exchanges as $exchange) {
            $partnership = local_equipment_get_partnership_by_id($exchange->partnershipid);
            // var_dump($exchange);
            // die();
            $datetime = userdate($exchange->starttime, get_string('strftimedatetime', 'langconfig')) .
                ' - ' . userdate($exchange->endtime, get_string('strftimetime', 'langconfig'));

            if (isset($partnership->name)) {
                $name = $partnership->name;
            } else if (isset($exchange->pickup_city)) {
                $name = $exchange->pickup_city;
            } else {
                $name = "";
            }

            if ($exchange->pickup_streetaddress) {
                $exchangeoptions[$exchange->id] = "$name — $datetime — $exchange->pickup_streetaddress, $exchange->pickup_city, $exchange->pickup_state $exchange->pickup_zipcode";
            } else {
                $exchangeoptions[$exchange->id] = "$name — $datetime";
            }
        }


        $mform->addElement('static', 'exchangeselectiondescription', '', get_string('selectexchangedescription', 'local_equipment'));

        // Exchange selection
        // $mform->addElement('header', 'exchangeheader', get_string('selectexchange', 'local_equipment'));
        $mform->addElement(
            'select',
            'pickup',
            get_string('exchange_locationandtime', 'local_equipment'),
            $exchangeoptions,
            ['multiple' => false, 'size' => 10]
        );
        $mform->addRule('pickup', get_string('required'), 'required', null, 'client');
        $mform->setType('pickup', PARAM_INT);
        $mform->setDefault('pickup', '-1');

        // // Pickup information section
        // $mform->addElement('header', 'pickupheader', get_string('pickupinformation', 'local_equipment'));

        // Pickup method
        $pickupmethods = local_equipment_get_pickup_methods();
        $mform->addElement('select', 'pickup_method', get_string('pickupmethod', 'local_equipment'), $pickupmethods);
        $mform->addRule('pickup_method', get_string('required'), 'required', null, 'client');
        $mform->setType('pickup_method', PARAM_TEXT);

        // Name of person picking up equipment (conditional)
        $mform->addElement('text', 'pickup_person_name', get_string('pickuppersonname', 'local_equipment'));
        $mform->setType('pickup_person_name', PARAM_TEXT);
        $mform->hideIf('pickup_person_name', 'pickup_method', 'neq', 'other');

        // Phone number of person picking up (conditional)
        $mform->addElement('text', 'pickup_person_phone', get_string('pickuppersonphone', 'local_equipment'));
        $mform->setType('pickup_person_phone', PARAM_TEXT);
        $mform->hideIf('pickup_person_phone', 'pickup_method', 'neq', 'other');

        // Additional details for pickup (conditional)
        $mform->addElement('textarea', 'pickup_person_details', get_string('pickuppersondetails', 'local_equipment'));
        $mform->setType('pickup_person_details', PARAM_TEXT);
        $mform->hideIf('pickup_person_details', 'pickup_method', 'neq', 'other');

        // Additional notes
        $mform->addElement('textarea', 'user_notes', get_string('usernotes', 'local_equipment'));
        $mform->setType('user_notes', PARAM_TEXT);

        // Action buttons
        $this->add_action_buttons(true, get_string('submitexchange', 'local_equipment'));
    }

    /**
     * Validate the form data.
     *
     * @param array $data The form data
     * @param array $files The uploaded files
     * @return array Array of errors
     */
    public function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        // Validate pickup method specific fields
        if ($data['pickup_method'] === 'other') {
            if (empty($data['pickup_person_name'])) {
                $errors['pickup_person_name'] = get_string('required');
            }
            if (empty($data['pickup_person_phone'])) {
                $errors['pickup_person_phone'] = get_string('required');
            }

            // Validate phone number format if provided
            if (!empty($data['pickup_person_phone'])) {
                $phoneobj = local_equipment_parse_phone_number($data['pickup_person_phone']);
                if (!empty($phoneobj->errors)) {
                    $errors['pickup_person_phone'] = implode(', ', $phoneobj->errors);
                }
            }
        }

        // Validate exchange availability
        if (!empty($data['pickup'])) {
            $exchanges = local_equipment_get_available_exchanges($USER->id);
            if (!array_key_exists($data['pickup'], $exchanges)) {
                $errors['pickup'] = get_string('exchangenotavailable', 'local_equipment');
            }
        }

        return $errors;
    }
}
