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

namespace local_equipment\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class editpartnership_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        $data = $this->_customdata['data'];

        // Add form elements.
        $mform->addElement('text', 'name', get_string('name', 'local_equipment'));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $data->name);

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
