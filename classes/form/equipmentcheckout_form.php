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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Equipment checkout form
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class equipmentcheckout_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'parent_name', get_string('parent_name', 'local_equipment'));
        $mform->setType('parent_name', PARAM_TEXT);
        $mform->addRule('parent_name', null, 'required', null, 'client');

        $mform->addElement('text', 'student_name', get_string('student_name', 'local_equipment'));
        $mform->setType('student_name', PARAM_TEXT);
        $mform->addRule('student_name', null, 'required', null, 'client');

        // Add more form elements here

        $this->add_action_buttons();
    }
}
