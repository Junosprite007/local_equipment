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
 * Add partnerships form.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\form;

use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for adding partnerships.
 */
class addpartnership_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $PAGE;
        $var = $PAGE->requires;
        $PAGE->requires->js_call_amd('local_equipment/addpartnership_form', 'init');
        $mform = $this->_form;

        $repeatarray = array();
        $repeatoptions = array();
        $sections = new stdClass();

        $repeatarray[] = $mform->createElement('header', 'partnership_header_{no}', get_string('partnership', 'local_equipment'), ['class' => 'partnership-header']);
        $repeatarray[] = $mform->createElement('text', 'name_{no}', get_string('name'), ['class' => 'partnership-name-input']);
        // $repeatarray[] = $mform->createElement('text', 'pickupid', get_string('pickupid', 'local_equipment'));
        // $repeatarray[] = $mform->createElement('text', 'liaisonid', get_string('liaisonid', 'local_equipment'));
        $repeatarray[] = $mform->createElement('advcheckbox', 'active_{no}', get_string('active'));

        // // Mailing address section
        $sections = $this->add_address_block($mform, 'physical');
        $repeatarray = array_merge($repeatarray, $sections->elements);
        $repeatoptions = array_merge($repeatoptions, $sections->types);

        // Pickup address section
        $sections = $this->add_address_block($mform, 'pickup');
        $repeatarray = array_merge($repeatarray, $sections->elements);
        $repeatoptions = array_merge($repeatoptions, $sections->types);

        // Billing address section
        $sections = $this->add_address_block($mform, 'billing');
        $repeatarray = array_merge($repeatarray, $sections->elements);
        $repeatoptions = array_merge($repeatoptions, $sections->types);

        // $repeatoptions['partnership_header_{no}']['expanded'] = false;

        // Set types.
        // $repeatoptions['header']['expanded'] = false;
        $repeatoptions['name_{no}']['type'] = PARAM_TEXT;
        // $repeatoptions['pickupid']['type'] = PARAM_INT;
        // $repeatoptions['liaisonid']['type'] = PARAM_INT;
        $repeatoptions['active_{no}']['type'] = PARAM_BOOL;

        // $this->add_address_group($mform, 'mailing', get_string('mailingaddress', 'local_equipment'));
        // $this->add_address_group($mform, 'mailing', get_string('mailingaddress', 'local_equipment'));


        $this->repeat_elements(
            $repeatarray,
            1,
            $repeatoptions,
            'partnership_repeats',
            'add_partnership',
            1,
            get_string('addmorepartnerships', 'local_equipment'),
            false,
        );

        $this->add_action_buttons();
    }

    /**
     * Form validation.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // foreach ($data['partnerships'] as $index => $partnership) {
        //     if (empty($partnership['name'])) {
        //         $errors["partnerships[$index][name]"] = get_string('required');
        //     }
        //     if (empty($partnership['pickupid']) || !is_numeric($partnership['pickupid'])) {
        //         $errors["partnerships[$index][pickupid]"] = get_string('invalidpickupid', 'local_equipment');
        //     }
        //     if (empty($partnership['liaisonid']) || !is_numeric($partnership['liaisonid'])) {
        //         $errors["partnerships[$index][liaisonid]"] = get_string('invalidliaisonid', 'local_equipment');
        //     }
        //     // Add more specific validations as needed
        // }

        return $errors;
    }

    public function add_address_group($mform, $groupname, $label) {
        $group = array();

        // $mform->addElement('header', $groupname . '_header', $label);
        $mform->addElement('static', 'streetaddress_label_' . $groupname, get_string('streetaddress_' . $groupname, 'local_equipment'), \html_writer::tag('span', get_string('streetaddress_' . $groupname, 'local_equipment')));
        $mform->addElement('static', 'city_label_' . $groupname, '', \html_writer::tag('label', get_string('city_' . $groupname, 'local_equipment')));
        $mform->addElement('static', 'state_label_' . $groupname, '', \html_writer::tag('label', get_string('state_' . $groupname, 'local_equipment')));
        $mform->addElement('static', 'zipcode_label_' . $groupname, '', \html_writer::tag('label', get_string('zipcode_' . $groupname, 'local_equipment')));
        $group[] = $mform->createElement('text', 'streetaddress_' . $groupname, get_string('streetaddress_' . $groupname, 'local_equipment'));
        $group[] = $mform->createElement('text', 'city_' . $groupname, get_string('city_' . $groupname, 'local_equipment'));
        $group[] = $mform->createElement('text', 'state_' . $groupname, get_string('state_' . $groupname, 'local_equipment'));
        $group[] = $mform->createElement('text', 'zipcode_' . $groupname, get_string('zipcode_' . $groupname, 'local_equipment'));

        $mform->addGroup($group, $groupname . '_group', $label, '<br>', false);

        // Set types for elements within the group
        $mform->setType('streetaddress_' . $groupname, PARAM_TEXT);
        $mform->setType('city_' . $groupname, PARAM_TEXT);
        $mform->setType('state_' . $groupname, PARAM_TEXT);
        $mform->setType('zipcode_' . $groupname, PARAM_TEXT);
    }
    public function add_address_block($mform, $addresstype) {
        $block = new stdClass();

        $block->elements = array();
        // $block->elements[] = $mform->createElement('header', $addresstype . '_header', get_string($addresstype . 'address', 'local_equipment'));
        $block->elements[] = $mform->createElement('static', $addresstype . 'address', \html_writer::tag('label', get_string($addresstype . 'address', 'local_equipment'), ['class' => 'form-input-group-labels']));
        $block->elements[] = $mform->createElement('text', 'streetaddress_' . $addresstype, get_string('streetaddress', 'local_equipment'));
        $block->elements[] = $mform->createElement('text', 'city_' . $addresstype, get_string('city', 'local_equipment'));
        $block->elements[] = $mform->createElement('text', 'state_' . $addresstype, get_string('state', 'local_equipment'));
        $block->elements[] = $mform->createElement('text', 'country_' . $addresstype, get_string('country', 'local_equipment'));
        $block->elements[] = $mform->createElement('text', 'zipcode_' . $addresstype, get_string('zipcode', 'local_equipment'));

        $block->types = array();
        $block->types['streetaddress_' . $addresstype]['type'] = PARAM_TEXT;
        $block->types['city_' . $addresstype]['type'] = PARAM_TEXT;
        $block->types['state_' . $addresstype]['type'] = PARAM_TEXT;
        $block->types['country_' . $addresstype]['type'] = PARAM_TEXT;
        $block->types['zipcode_' . $addresstype]['type'] = PARAM_TEXT;


        return $block;
    }
}
