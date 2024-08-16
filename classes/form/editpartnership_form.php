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
 * Edit partnership form.
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
class editpartnership_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        $data = $this->_customdata['data'];
        $addresstypes = [
            'physical',
            'mailing',
            'pickup',
            'billing',
        ];


        // var_dump($data);
        // die();


        $users = user_get_users_by_id(json_decode($data->liaisonids));

        // Autocomplete users.
        $users = local_equipment_auto_complete_users();
        $mastercourses = local_equipment_get_master_courses('ALL_COURSES_CURRENT');
        $coursesformatted = $mastercourses->courses_formatted;

        // Add form elements.
        $mform->addElement('hidden', 'partnershipid', $data->id);
        $mform->setType('partnershipid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name', 'local_equipment'));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $data->name);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $mform->addElement('autocomplete', 'liaisons', get_string('selectliaisons', 'local_equipment'), [], $users);
        $mform->setType('liaisons', PARAM_RAW);
        $mform->setDefault('liaisons', json_decode($data->liaisonids));

        $mform->addElement('select', 'courses', get_string('selectcourses', 'local_equipment'), $coursesformatted, ['multiple' => 'multiple', 'size' => 10]);
        $mform->setType('courses', PARAM_RAW);
        $mform->setDefault('courses', json_decode($data->courseids));

        $mform->addElement('advcheckbox', 'active', get_string('active'));
        $mform->setType('active', PARAM_BOOL);
        $mform->setDefault('active', $data->active);

        for ($i = 0; $i < count($addresstypes); $i++) {
            local_equipment_add_edit_address_block($mform, $addresstypes[$i], $data);
        }
        $this->add_action_buttons(true);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Add custom validation if needed.
        return $errors;
    }
}
