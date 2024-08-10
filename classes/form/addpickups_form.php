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
 * Form for adding multiple new pickups.
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

class addpickup_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $DB, $PAGE;

        $mform = $this->_form;
        $repeatno = optional_param('repeatno', 1, PARAM_INT);

        $partnerships = $DB->get_records_menu('local_equipment_partnership', null, 'name ASC', 'id,name');
        $users = local_equipment_auto_complete_users();

        $statuses = [
            'pending' => get_string('status_pending', 'local_equipment'),
            'confirmed' => get_string('status_confirmed', 'local_equipment'),
            'completed' => get_string('status_completed', 'local_equipment'),
            'cancelled' => get_string('status_cancelled', 'local_equipment'),
        ];

        $repeatarray = array(
            $mform->createElement('header', 'pickupheader', get_string('pickup', 'local_equipment')),
            $mform->createElement('date_time_selector', 'pickupstarttime', get_string('pickupstarttime', 'local_equipment')),
            $mform->createElement('date_time_selector', 'pickupendtime', get_string('pickupendtime', 'local_equipment')),
            $mform->createElement('select', 'partnership', get_string('partnership', 'local_equipment'), $partnerships),
            $mform->createElement('autocomplete', 'flccoordinator', get_string('selectflccoordinator', 'local_equipment'), [], $users),
            $mform->createElement('text', 'partnershipcoordinatorname', get_string('partnershipcoordinatorname', 'local_equipment')),
            $mform->createElement('text', 'partnershipcoordinatorphone', get_string('partnershipcoordinatorphone', 'local_equipment')),
            $mform->createElement('select', 'status', get_string('status', 'local_equipment'), $statuses),
            $mform->createElement(
                'button',
                'removepickup',
                get_string('removepickup', 'local_equipment'),
                array('class' => 'local-equipment-remove-pickup')
            )
        );

        $repeateloptions = array(
            'pickupstarttime' => array(
                'type' => PARAM_INT
            ),
            'pickupendtime' => array(
                'type' => PARAM_INT
            ),
            'partnership' => array(
                'type' => PARAM_TEXT
            ),
            'flccoordinator' => array(
                'type' => PARAM_ALPHA
            ),
            'partnershipcoordinatorname' => array(
                'type' => PARAM_TEXT
            ),
            'partnershipcoordinatorphone' => array(
                'type' => PARAM_TEXT
            ),
            'status' => array(
                'type' => PARAM_ALPHA
            )
        );

        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeateloptions,
            'pickups',
            'addpickup',
            1,
            get_string('addmorepickups', 'local_equipment'),
            true
        );

        $mform->addElement('hidden', 'pickups', $repeatno);
        $mform->setType('pickups', PARAM_INT);

        $this->add_action_buttons();

        // Load JavaScript for dynamic form handling.
        $PAGE->requires->js_call_amd('local_equipment/addpickup_form', 'init');
    }

    /**
     * Form validation.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors, or an empty array if everything is OK
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        for ($i = 0; $i < $data['pickups']; $i++) {
            if ($data['pickupdate'][$i] >= $data['dropoffdate'][$i]) {
                $errors["dropoffdate[$i]"] = get_string('dropoffdateafterwarning', 'local_equipment');
            }
        }

        return $errors;
    }
}
