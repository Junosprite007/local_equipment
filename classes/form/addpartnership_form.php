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
require_once($CFG->dirroot . '/local/equipment/lib.php');

/**
 * Form for adding partnerships.
 */
class addpartnership_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $PAGE, $DB;

        // $PAGE->requires->js_call_amd('local_equipment/addpartnership_form', 'init');
        $mform = $this->_form;

        $numberofrepeats = 0;
        $repeatarray = [];
        $repeatoptions = [];
        $address = new stdClass();

        // Autocomplete users.
        $users = [
            'ajax' => 'core_user/form_user_selector',
            'multiple' => true,
            'casesensitive' => false,
            // 'valuehtmlcallback' => function ($value) {
            //     global $OUTPUT;
            //     $user = \core_user::get_user($value);
            //     return $OUTPUT->user_picture($user, array('size' => 24)) . ' ' . fullname($user);
            // }
        ];



        // Make this an admin setting later on.
        $categoryname = 'ALL_COURSES_CURRENT';

        // Fetch the course categories by name.
        $categories = $DB->get_records('course_categories', array('name' => $categoryname));
        $category = array_values($categories)[0];
        $courses = $DB->get_records('course', array('category' => $category->id));

        $courses_formatted = [];
        foreach ($courses as $course) {
            $courses_formatted[$course->id] = $course->fullname;
        }

        $repeatarray['partnershipheader'] = $mform->createElement('header', 'partnershipheader', get_string('partnership', 'local_equipment'), ['class' => 'partnership-header']);

        $repeatno = optional_param('repeatno', 1, PARAM_INT);
        $mform->addElement('hidden', 'partnerships', $repeatno);
        // Add a delete button for each repeated element (except the first one).
        $repeatarray['delete'] = $mform->createElement('html', '<button type="button" class="remove-partnership btn btn-danger"><i class="fa fa-trash"></i></button>');
        // $mform->setDefault('delete', '<i class="fa fa-trash"></i>');

        $repeatarray['partnershipname'] = $mform->createElement('text', 'partnershipname', get_string('partnershipname', 'local_equipment'), ['class' => 'partnership-name-input']);
        $repeatarray['liaisons'] = $mform->createElement('autocomplete', 'liaisons', get_string('selectliaisons', 'local_equipment'), [], $users);
        $repeatarray['courses'] = $mform->createElement('select', 'courses', get_string('selectcourses', 'local_equipment'), $courses_formatted, ['multiple' => 'multiple', 'size' => 10]);
        $repeatarray['active'] = $mform->createElement('advcheckbox', 'active', get_string('active'));

        // Physical address section
        $address = $this->add_address_block($mform, 'physical');
        $repeatarray = array_merge($repeatarray, $address->elements);
        $repeatoptions = array_merge($repeatoptions, $address->options);

        // Mailing address section
        $address = $this->add_address_block($mform, 'mailing');
        $repeatarray = array_merge($repeatarray, $address->elements);
        $repeatoptions = array_merge($repeatoptions, $address->options);

        // Pickup address section
        $address = $this->add_address_block($mform, 'pickup');
        $repeatarray = array_merge($repeatarray, $address->elements);
        $repeatoptions = array_merge($repeatoptions, $address->options);

        // Billing address section
        $address = $this->add_address_block($mform, 'billing');
        $repeatarray = array_merge($repeatarray, $address->elements);
        $repeatoptions = array_merge($repeatoptions, $address->options);


        // Set options.
        $repeatoptions['partnerships']['type'] = PARAM_INT;
        $repeatoptions['partnershipheader']['header'] = true;
        $repeatoptions['partnershipname']['type'] = PARAM_TEXT;
        $repeatoptions['partnershipname']['rule'] = 'required';
        $repeatoptions['liaisons']['type'] = PARAM_TEXT;
        $repeatoptions['courses']['type'] = PARAM_TEXT;
        $repeatoptions['active']['type'] = PARAM_BOOL;
        $repeatoptions['active']['default'] = 1;
        // $repeatoptions['delete']['type'] = 'button';
        // $repeatoptions['delete']['default'] = '<i class="fa fa-trash"></i>';


        // $addfields = optional_param('add_partnership', '', PARAM_TEXT);
        // $deletefields = optional_param('delete_partnership', '', PARAM_TEXT);

        // if (!empty($deletefields)) {
        //     $repeatno--;
        // }

        // $this->repeat_elements($repeatarray, $repeatno, $repeatedoptions, 'option_repeats', 'option_add_fields', 3, get_string('addmorefields', 'form'), true);

        // Use this later if it helps.
        // $numberofrepeats = $this->repeat_elements(
        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeatoptions,
            'partnerships',
            'add_partnership',
            1,
            get_string('addmorepartnerships', 'local_equipment'),
            false,
            'delete_partnership'
        );

        // $PAGE->requires->js_call_amd('local_equipment/deletepartnership_button', 'init');
        $this->add_action_buttons(true, get_string('submit'));
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

        // No custom validation yet.

        return $errors;
    }


    /**
     * Add an address group for if I want each address to appear in a line,
     * though the text boxes currently doesn't have labels doing it this way.
     * I'd have to figure that out, and I don't want to....
     *
     * @param moodleform $mform a standard moodle form, probably will be '$this->_form'.
     * @param string $groupname the name of the group to add.
     * @param string $label the label for the group.
     */
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

    /**
     * Add an address block.
     *
     * @param moodleform $mform a standard moodle form, probably will be '$this->_form'.
     * @param string $addresstype the type of address block to add: 'mailing', 'physical', 'pickup', or 'billing'.
     * @return object $block a block of elements to be added to the form.
     */
    public function add_address_block($mform, $addresstype) {
        $block = new stdClass();

        $block->elements = array();
        $block->options = array();
        $block->elements[$addresstype . 'address'] = $mform->createElement('static', $addresstype . 'address', \html_writer::tag('label', get_string($addresstype . 'address', 'local_equipment'), ['class' => 'form-input-group-labels']));

        switch ($addresstype) {
            case 'mailing':
                $block->elements['attention_' . $addresstype] = $mform->createElement('text', 'attention_' . $addresstype, get_string('attention', 'local_equipment'));
                $block->options['attention_' . $addresstype]['type'] = PARAM_TEXT;
                break;
            case 'pickup':
                $block->elements['instructions_' . $addresstype] = $mform->createElement('textarea', 'instructions_' . $addresstype, get_string('pickupinstructions', 'local_equipment'));
                $block->options['instructions_' . $addresstype]['type'] = PARAM_TEXT;
                break;
            case 'billing':
                $block->elements['attention_' . $addresstype] = $mform->createElement('text', 'attention_' . $addresstype, get_string('attention', 'local_equipment'));
                $block->options['attention_' . $addresstype]['type'] = PARAM_TEXT;
                break;
            default:
                break;
        }

        if ($addresstype !== 'physical') {
            $block->elements['sameasphysical_' . $addresstype] = $mform->createElement('advcheckbox', 'sameasphysical_' . $addresstype, get_string('sameasphysical', 'local_equipment'));
            $block->options['sameasphysical_' . $addresstype]['type'] = PARAM_BOOL;
        }

        $block->elements['streetaddress_' . $addresstype] = $mform->createElement('text', 'streetaddress_' . $addresstype, get_string('streetaddress', 'local_equipment'));
        $block->elements['city_' . $addresstype] = $mform->createElement('text', 'city_' . $addresstype, get_string('city', 'local_equipment'));
        $block->elements['state_' . $addresstype] = $mform->createElement('select', 'state_' . $addresstype, get_string('state', 'local_equipment'), local_equipment_get_states());
        $block->elements['country_' . $addresstype] = $mform->createElement('select', 'country_' . $addresstype, get_string('country', 'local_equipment'), local_equipment_get_countries());
        $block->elements['zipcode_' . $addresstype] = $mform->createElement('text', 'zipcode_' . $addresstype, get_string('zipcode', 'local_equipment'));

        $block->options['streetaddress_' . $addresstype]['type'] = PARAM_TEXT;
        $block->options['city_' . $addresstype]['type'] = PARAM_TEXT;
        $block->options['state_' . $addresstype]['type'] = PARAM_TEXT;
        $block->options['country_' . $addresstype]['type'] = PARAM_TEXT;
        $block->options['zipcode_' . $addresstype]['type'] = PARAM_TEXT;

        // The physical address is required, but none of the others are.
        if ($addresstype === 'physical') {
            $block->options['streetaddress_' . $addresstype]['rule'] = 'required';
            $block->options['city_' . $addresstype]['rule'] = 'required';
            $block->options['state_' . $addresstype]['rule'] = 'required';
            $block->options['country_' . $addresstype]['rule'] = 'required';
            $block->options['zipcode_' . $addresstype]['rule'] = 'required';
        }

        $block->options['state_' . $addresstype]['default'] = 'MI';
        $block->options['country_' . $addresstype]['default'] = 'USA';

        return $block;
    }
}
