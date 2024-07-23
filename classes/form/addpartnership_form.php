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
        $mform = $this->_form;

        $repeatarray = array();
        $repeatoptions = array();

        $repeatarray[] = $mform->createElement('header', 'partnership', get_string('partnership', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'name', get_string('name'));
        $repeatarray[] = $mform->createElement('text', 'pickupid', get_string('pickupid', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'liaisonid', get_string('liaisonid', 'local_equipment'));
        $repeatarray[] = $mform->createElement('advcheckbox', 'active', get_string('active'));




        // // Mailing address section
        $repeatarray[] = $mform->createElement('text', 'streetaddress_mailing', get_string('streetaddress_mailing', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'city_mailing', get_string('city_mailing', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'state_mailing', get_string('state_mailing', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'country_mailing', get_string('country_mailing', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'zipcode_mailing', get_string('zipcode_mailing', 'local_equipment'));

        // Pickup address section
        $repeatarray[] = $mform->createElement('text', 'streetaddress_pickup', get_string('streetaddress_pickup', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'city_pickup', get_string('city_pickup', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'state_pickup', get_string('state_pickup', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'country_pickup', get_string('country_pickup', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'zipcode_pickup', get_string('zipcode_pickup', 'local_equipment'));

        // Billing address section
        $repeatarray[] = $mform->createElement('text', 'streetaddress_billing', get_string('streetaddress_billing', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'city_billing', get_string('city_billing', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'state_billing', get_string('state_billing', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'country_billing', get_string('country_billing', 'local_equipment'));
        $repeatarray[] = $mform->createElement('text', 'zipcode_billing', get_string('zipcode_billing', 'local_equipment'));


        // Set types.
        $repeatoptions['name']['type'] = PARAM_TEXT;
        $repeatoptions['pickupid']['type'] = PARAM_INT;
        $repeatoptions['liaisonid']['type'] = PARAM_INT;
        $repeatoptions['active']['type'] = PARAM_BOOL;
        // $repeatoptions['mailing_group[streetaddress_mailing]']['type'] = PARAM_TEXT;
        $repeatoptions['streetaddress_mailing']['type'] = PARAM_TEXT;
        $repeatoptions['city_mailing']['type'] = PARAM_TEXT;
        $repeatoptions['state_mailing']['type'] = PARAM_TEXT;
        $repeatoptions['country_mailing']['type'] = PARAM_TEXT;
        $repeatoptions['zipcode_mailing']['type'] = PARAM_TEXT;
        $repeatoptions['streetaddress_pickup']['type'] = PARAM_TEXT;
        $repeatoptions['city_pickup']['type'] = PARAM_TEXT;
        $repeatoptions['state_pickup']['type'] = PARAM_TEXT;
        $repeatoptions['country_pickup']['type'] = PARAM_TEXT;
        $repeatoptions['zipcode_pickup']['type'] = PARAM_TEXT;
        $repeatoptions['name_billing']['type'] = PARAM_TEXT;
        $repeatoptions['streetaddress_billing']['type'] = PARAM_TEXT;
        $repeatoptions['city_billing']['type'] = PARAM_TEXT;
        $repeatoptions['state_billing']['type'] = PARAM_TEXT;
        $repeatoptions['country_billing']['type'] = PARAM_TEXT;
        $repeatoptions['zipcode_billing']['type'] = PARAM_TEXT;
        // $mform->addGroup();

        // $this->add_address_group($mform, 'mailing', get_string('mailingaddress', 'local_equipment'));
        $this->add_address_group($mform, 'mailing', get_string('mailingaddress', 'local_equipment'));

        $this->repeat_elements(
            $repeatarray,
            1,
            $repeatoptions,
            'partnership_repeats',
            'add_partnership',
            1,
            get_string('addmorepartnerships', 'local_equipment'),
            true,
            'delete'
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

        foreach ($data['partnerships'] as $index => $partnership) {
            if (empty($partnership['name'])) {
                $errors["partnerships[$index][name]"] = get_string('required');
            }
            if (empty($partnership['pickupid']) || !is_numeric($partnership['pickupid'])) {
                $errors["partnerships[$index][pickupid]"] = get_string('invalidpickupid', 'local_equipment');
            }
            if (empty($partnership['liaisonid']) || !is_numeric($partnership['liaisonid'])) {
                $errors["partnerships[$index][liaisonid]"] = get_string('invalidliaisonid', 'local_equipment');
            }
            // Add more specific validations as needed
        }

        return $errors;
    }

    public function add_address_group($mform, $groupname, $label) {
        $group = array();

        $group[] = $mform->createElement('text', 'streetaddress_' . $groupname, get_string('streetaddress_' . $groupname, 'local_equipment'));
        $group[] = $mform->createElement('text', 'city_' . $groupname, get_string('city_' . $groupname, 'local_equipment'));
        $group[] = $mform->createElement('text', 'state_' . $groupname, get_string('state_' . $groupname, 'local_equipment'));
        $group[] = $mform->createElement('text', 'zipcode_' . $groupname, get_string('zipcode_' . $groupname, 'local_equipment'));

        $mform->addGroup($group, $groupname . '_group', $label, '<br />', true);

        // Set types for elements within the group
        $mform->setType($groupname . '_group[streetaddress_' . $groupname . ']', PARAM_TEXT);
        $mform->setType($groupname . '_group[city_' . $groupname . ']', PARAM_TEXT);
        $mform->setType($groupname . '_group[state_' . $groupname . ']', PARAM_TEXT);
        $mform->setType($groupname . '_group[zipcode_' . $groupname . ']', PARAM_TEXT);
    }
}
