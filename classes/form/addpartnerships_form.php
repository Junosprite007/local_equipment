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
class addpartnerships_form extends \moodleform {
    public function definition() {

        $mform = $this->_form;
        $repeatarray = [];
        $repeatoptions = [];
        $allpartnershipcourses = [];
        $allpartnershipcourses_json = [];
        $address = new stdClass();

        $repeatno = optional_param('repeatno', 1, PARAM_INT);
        $deletebuttonname = 'delete_partnership';
        $addfieldsname = 'addpartnership';
        $deletions = optional_param_array($deletebuttonname, [], PARAM_INT);

        if (!empty($deletions)) {
            $repeatno = $repeatno - count($deletions);
            $repeatno = max(1, $repeatno);
        }

        $users = local_equipment_auto_complete_users();
        $partnershipcategories = local_equipment_get_partnership_categories_this_year(null, true);

        foreach ($partnershipcategories->partnershipids as $id) {
            $allpartnershipcourses[$id] = local_equipment_get_partnership_courses_this_year($id);
        }

        foreach ($allpartnershipcourses as $id => $courses) {
            $allpartnershipcourses_json[$id] = $courses->courses_formatted;
        }

        $mform->addElement(
            'hidden',
            'coursesthisyear',
            get_string('coursesthisyear', 'local_equipment'),
            [
                'id' => 'id_coursesthisyear',
                'data-coursesthisyear' => json_encode($allpartnershipcourses_json)
            ]
        );
        $mform->setType('coursesthisyear', PARAM_RAW);

        $repeatarray['partnershipheader'] = $mform->createElement('header', 'partnershipheader', get_string('partnership', 'local_equipment'), ['class' => 'local-equipment-partnership-header']);

        // Add a delete button for each repeated element (except the first one).
        $repeatarray['delete'] = $mform->createElement('html', '<button type="button" class="local-equipment-remove-partnership btn btn-secondary"><i class="fa fa-trash"></i>&nbsp;&nbsp;' . get_string('deletepartnership', 'local_equipment') . '</button>');
        $repeatarray['partnershipname'] = $mform->createElement('text', 'partnershipname', get_string('partnershipname', 'local_equipment'), ['class' => 'partnership-name-input']);
        $repeatarray['liaisons'] = $mform->createElement('autocomplete', 'liaisons', get_string('selectliaisons', 'local_equipment'), [], $users);
        $repeatarray['partnershipcourselist'] = $mform->createElement('select', 'partnershipcourselist', get_string('partnershipcourselist', 'local_equipment'), $partnershipcategories->partnershipids_catnames);
        $repeatarray['active'] = $mform->createElement('advcheckbox', 'active', get_string('active'));

        $groupview = false;
        // Physical address section.
        $address = local_equipment_add_address_block($mform, 'physical', '', false, false, true, false, $groupview, false);
        $repeatarray = array_merge($repeatarray, $address->elements);
        $repeatoptions = array_merge($repeatoptions, $address->options);

        // Mailing address section.
        $address = local_equipment_add_address_block($mform, 'mailing', 'attention', false, true, true, false, $groupview, false);
        $repeatarray = array_merge($repeatarray, $address->elements);
        $repeatoptions = array_merge($repeatoptions, $address->options);

        // Pickup address section.
        $address = local_equipment_add_address_block($mform, 'pickup', '', false, true, true, true, $groupview, false);
        $repeatarray = array_merge($repeatarray, $address->elements);
        $repeatoptions = array_merge($repeatoptions, $address->options);

        // Billing address section.
        $address = local_equipment_add_address_block($mform, 'billing', 'attention', false, true, true, false, $groupview, false);
        $repeatarray = array_merge($repeatarray, $address->elements);
        $repeatoptions = array_merge($repeatoptions, $address->options);

        // Set options.
        $repeatoptions['partnershipheader']['header'] = true;
        $repeatoptions['partnershipname']['type'] = PARAM_TEXT;
        $repeatoptions['partnershipname']['rule'] = 'required';
        $repeatoptions['liaisons']['type'] = PARAM_TEXT;
        $repeatoptions['partnershipcourselist']['type'] = PARAM_RAW;
        $repeatoptions['active']['type'] = PARAM_BOOL;
        $repeatoptions['active']['default'] = 1;

        // Use this later if it helps.
        // $numberofrepeats = $this->repeat_elements(
        // This string gets added to the <div> after the last fieldset element from the repeat_elements function.

        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeatoptions,
            'partnerships',
            $addfieldsname,
            1,
            get_string('addmorepartnerships', 'local_equipment')
        );
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
}
