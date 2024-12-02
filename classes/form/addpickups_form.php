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

global $CFG;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/equipment/lib.php');

class addpickups_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $repeatarray = [];
        $repeatoptions = [];

        $users = local_equipment_auto_complete_users_single();
        $addpartnershipsurl = new \moodle_url('/local/equipment/partnerships/addpartnerships.php');
        $addpartnershipslink = \html_writer::link($addpartnershipsurl, get_string('addpartnershipshere', 'local_equipment'));
        $repeatno = optional_param('repeatno', 1, PARAM_INT);

        $partnerships = $DB->get_records_menu('local_equipment_partnership', null, 'name ASC', 'id,name');

        $hours = array_combine(range(0, 23), range(0, 23));
        $minutes = array_combine(range(0, 59, 5), range(0, 59, 5)); // 5-minute intervals
        $statuses = [
            'pending' => get_string('status_pending', 'local_equipment'),
            'confirmed' => get_string('status_confirmed', 'local_equipment'),
            'completed' => get_string('status_completed', 'local_equipment'),
            'cancelled' => get_string('status_cancelled', 'local_equipment'),
        ];



        $mform->addElement('hidden', 'pickups', $repeatno);

        $repeatarray = [
            'pickupheader' => $mform->createElement('header', 'pickupheader', get_string('pickup', 'local_equipment'), ['class' => 'local-equipment-pickups-addpickups-time-selectors']),
            'delete' => $mform->createElement('html', '<button type="button" class="local-equipment-remove-pickup btn btn-danger"><i class="fa fa-trash"></i></button>'),
            'pickupdate' => $mform->createElement('date_selector', 'pickupdate', get_string('pickupdate', 'local_equipment')),
            'starttime' => create_time_selector($mform, 'starttime', get_string('starttime', 'local_equipment')),
            'endtime' => create_time_selector($mform, 'endtime', get_string('endtime', 'local_equipment')),
            'partnershipid' => $mform->createElement('select', 'partnershipid', get_string('partnership', 'local_equipment'), $partnerships),
            'flccoordinatorid' => $mform->createElement('autocomplete', 'flccoordinatorid', get_string('selectflccoordinator', 'local_equipment'), [], $users),
            'partnershipcoordinatorid' => $mform->createElement('autocomplete', 'partnershipcoordinatorid', get_string('selectpartnershipcoordinator', 'local_equipment'), [], $users),
            'status' => $mform->createElement('select', 'status', get_string('status', 'local_equipment'), $statuses),
        ];

        // Set header.
        $repeatoptions['pickupheader']['header'] = true;

        // Set element types.
        $repeatoptions['pickupdate']['type'] = PARAM_INT;
        $repeatoptions['starttime']['type'] = PARAM_INT;
        $repeatoptions['endtime']['type'] = PARAM_INT;
        $repeatoptions['partnershipid']['type'] = PARAM_TEXT;
        $repeatoptions['flccoordinatorid']['type'] = PARAM_TEXT;
        $repeatoptions['partnershipcoordinatorid']['type'] = PARAM_TEXT;
        $repeatoptions['status']['type'] = PARAM_TEXT;

        // Set element rules.
        $repeatoptions['pickupdate']['rule'] = 'required';
        $repeatoptions['starttime']['rule'] = 'required';
        $repeatoptions['endtime']['rule'] = 'required';
        $repeatoptions['partnershipid']['rule'] = 'required';
        $repeatoptions['flccoordinatorid']['rule'] = 'required';

        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeatoptions,
            'pickups',
            'add_pickup',
            1,
            get_string('addmorepickups', 'local_equipment'),
            false,
            'delete_pickup'
        );

        // $mform->addElement('hidden', 'pickups', $repeatno);
        // $mform->setType('pickups', PARAM_INT);

        $this->add_action_buttons();

        // Load JavaScript for dynamic form handling.
        // $PAGE->requires->js_call_amd('local_equipment/addpickups_form', 'init');
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


        // for ($i = 0; $i < $data['pickups']; $i++) {
        //     if ($data['pickupendtime'][$i] >= $data['pickupstarttime'][$i]) {
        //         $errors["dropoffdate[$i]"] = get_string('dropoffdateafterwarning', 'local_equipment');
        //     }
        // }

        return $errors;
    }
}
