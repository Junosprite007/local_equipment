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
require_once($CFG->dirroot . '/local/equipment/lib.php');
require_once($CFG->dirroot . '/user/lib.php');

class editpartnership_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        $data = $this->_customdata['data'];
        $addresstypes = [
            'physical',
            'mailing',
            'pickup',
            'billing'
        ];

        $users = user_get_users_by_id(json_decode($data->liaisonids));

        // echo '<pre>';
        // // var_dump(json_decode($data->liaisonids));
        // var_dump($users);
        // echo '</pre>';
        // die();

        // Autocomplete users.
        $users = local_equipment_auto_complete_users();
        $courses_formatted = local_equipment_get_master_courses('ALL_COURSES_CURRENT');

        // Add form elements.
        $mform->addElement('hidden', 'partnershipid', $data->id);
        $mform->setType('partnershipid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name', 'local_equipment'));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $data->name);

        $mform->addElement('autocomplete', 'liaisons', get_string('selectliaisons', 'local_equipment'), [], $users);
        $mform->setType('liaisons', PARAM_RAW);
        $mform->setDefault('liaisons', json_decode($data->liaisonids));

        $mform->addElement('select', 'courses', get_string('selectcourses', 'local_equipment'), $courses_formatted, ['multiple' => 'multiple', 'size' => 10]);
        $mform->setType('courses', PARAM_RAW);
        $mform->setDefault('courses', json_decode($data->courseids));

        $mform->addElement('advcheckbox', 'active', get_string('active'));
        $mform->setType('active', PARAM_BOOL);
        $mform->setDefault('active', $data->active);

        for ($i = 0; $i < count($addresstypes); $i++) {
            local_equipment_add_edit_address_block($mform, $addresstypes[$i], $data);
        }

        // $mform->addRule('streetaddress_physical', get_string('required'), 'required', null, 'client');


        // $mform->addElement('text', 'streetaddress_physical', get_string('streetaddress_physical', 'local_equipment'));

        // $block->elements[$addresstype . 'address'] = $mform->createElement('static'
        // $block = local_equipment_add_address_block($mform, 'physical');
        // $mform->addGroup($block->elements, 'physicaladdress', get_string('physicaladdress', 'local_equipment'), '', false, $block->options);



        // $mform->setType('streetaddress_physical', PARAM_TEXT);
        // $mform->setDefault('streetaddress_physical', $data->streetaddress_physical);

        // $mform->setType('streetaddress_physical', PARAM_TEXT);
        // $mform->setDefault('streetaddress_physical', $data->streetaddress_physical);

        // $mform->addElement($block->elements['streetaddress_physical']);
        // $i = 0;
        // foreach ($block->elements as $element) {
        //     $mform->addElement($element);
        //     $typeset = isset($element->getAttributes()['type']);
        //     $ruleset = isset($element->getAttributes()['rule']);
        //     if ($i > 0) {
        //         $options = $block->options[$element->getName()];
        //         if ($typeset) {
        //             $mform->setType($element->getName(), $element->getAttributes()['type']);
        //         }
        //         if ($ruleset) {
        //             // $mform->addRule($element->getName(), null, 'required', null, 'client');
        //         }

        //         //     $mform->setType($element->getName(), $element->getAttributes()['type']);
        //     }
        //     // if (isset($element->getAttributes()['type'])) {
        //     //     $mform->setType($element->getName(), $element->getAttributes()['type']);
        //     // }
        //     // if (isset($data->{$element->getName()}) && $i != 0) {
        //     //     // $mform->setType($element->getName(), $element->getAttributes()['type']);
        //     //     $mform->setDefault($element->getName(), $data->{$element->getName()});
        //     // }
        //     echo '<pre>';
        //     var_dump($element->getAttributes());
        //     echo '</pre>';
        //     $i++;
        // }
        // die();

        // $i = 0;
        // foreach ($block->options as $option) {
        //     echo '<pre>';
        //     // var_dump('$element->getAttributes(): ', $element->getAttributes());
        //     // var_dump('$element->getName(): ', $element->getName());
        //     // var_dump($element);
        //     var_dump($option);
        //     echo '</pre>';
        // }
        // die();
        // $mform->addGroup($$block->elements);
        // $mform->setType($block->options['streetaddress_physical']['type']);
        // $mform->setType('streetaddress_physical', PARAM_BOOL);
        // $mform->setDefault('streetaddress_physical', $data->streetaddress_physical);

        // $OUTPUT->$address->elements;
        // $address->
        // echo '<pre>';
        // // var_dump(json_decode($data->liaisonids));
        // var_dump($address->elements['streetaddress_physical']);
        // echo '</pre>';
        // die();
        // $mform->addElement('textarea', 'description', get_string('description', 'local_equipment'));
        // $mform->setType('description', PARAM_TEXT);
        // $mform->setDefault('description', $data->description);

        // Add other fields as necessary.

        $this->add_action_buttons(true);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Add custom validation if needed.
        return $errors;
    }
}
