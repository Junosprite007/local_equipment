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
 * Edit pickup form.
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
require_once($CFG->dirroot . '/user/lib.php');

/**
 * Folder conversion handler. This resource handler is called by moodle1_mod_resource_handler
 */
class editpickup_form extends \moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $partnerships = $DB->get_records_menu('local_equipment_partnership', null, 'name ASC', 'id,name');
        $statuses = [
            'pending' => get_string('status_pending', 'local_equipment'),
            'confirmed' => get_string('status_confirmed', 'local_equipment'),
            'completed' => get_string('status_completed', 'local_equipment'),
            'cancelled' => get_string('status_cancelled', 'local_equipment'),
        ];


        // $users = user_get_users_by_id($data->flccoordinatorid);

        // Autocomplete users.
        $users = local_equipment_auto_complete_users_single();
        // $mastercourses = local_equipment_get_master_courses('ALL_COURSES_CURRENT');
        // $coursesformatted = $mastercourses->courses_formatted;

        // Add form elements.

        $mform->addElement('hidden', 'pickupid', $data->id);
        $mform->setType('pickupid', PARAM_TEXT);

        $mform->addElement('header', 'pickupheader', get_string('pickup', 'local_equipment'), ['class' => 'local-equipment-pickups-addpickups-time-selectors']);

        $mform->addElement('date_selector', 'pickupdate', get_string('pickupdate', 'local_equipment'));
        $mform->setType('pickupdate', PARAM_INT);
        $mform->setDefault('pickupdate', $data->pickupdate);
        $mform->addRule('pickupdate', get_string('required'), 'required', null, 'client');

        $mform->addElement(create_time_selector($mform, 'starttime', get_string('starttime', 'local_equipment'), $data->starttime));
        $mform->setType('starttime', PARAM_INT);
        $mform->addRule('starttime', get_string('required'), 'required', null, 'client');
        // $mform->setDefault('starttime', $data->starttime);

        $mform->addElement(create_time_selector($mform, 'endtime', get_string('endtime', 'local_equipment'), $data->endtime));
        $mform->setType('endtime', PARAM_INT);
        $mform->addRule('endtime', get_string('required'), 'required', null, 'client');
        // $mform->setDefault('endtime', $data->starttime);

        $mform->addElement('select', 'partnershipid', get_string('partnership', 'local_equipment'), $partnerships);
        $mform->setType('partnershipid', PARAM_INT);
        $mform->setDefault('partnershipid', $data->partnershipid);
        $mform->addRule('partnershipid', get_string('required'), 'required', null, 'client');

        $mform->addElement('autocomplete', 'flccoordinatorid', get_string('selectflccoordinator', 'local_equipment'), [], $users);
        $mform->setType('flccoordinatorid', PARAM_TEXT);
        $mform->setDefault('flccoordinatorid', $data->flccoordinatorid);
        $mform->addRule('flccoordinatorid', get_string('required'), 'required', null, 'client');

        $mform->addElement('autocomplete', 'partnershipcoordinatorid', get_string('selectpartnershipcoordinator', 'local_equipment'), [], $users);
        $mform->setType('partnershipcoordinatorid', PARAM_TEXT);
        $mform->setDefault('partnershipcoordinatorid', $data->partnershipcoordinatorid);

        $mform->addElement('select', 'status', get_string('status', 'local_equipment'), $statuses);
        $mform->setType('status', PARAM_TEXT);
        $mform->setDefault('status', $data->status);

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Add custom validation if needed.
        return $errors;
    }
}
