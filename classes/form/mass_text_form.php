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
 * Form for sending mass text messages to parents.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for sending mass text messages to parents.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class mass_text_form extends \moodleform {

    /**
     * Define the form elements.
     */
    public function definition() {
        $mform = $this->_form;

        // Form header
        $mform->addElement('header', 'masstextheader', get_string('masstextmessaging', 'local_equipment'));

        // Information text
        $mform->addElement('static', 'info', '', get_string('masstextinfo', 'local_equipment'));

        // Message textarea with character limit
        $mform->addElement('textarea', 'message', get_string('textmessage', 'local_equipment'), [
            'rows' => 5,
            'cols' => 60,
            'maxlength' => 250,
            'placeholder' => get_string('masstextplaceholder', 'local_equipment')
        ]);
        $mform->setType('message', PARAM_TEXT);
        $mform->addRule('message', get_string('required'), 'required', null, 'client');
        $mform->addRule('message', get_string('maximumchars', '', 250), 'maxlength', 250, 'client');

        // Character counter (will be handled by JavaScript)
        $mform->addElement(
            'static',
            'charcount',
            '',
            '<div id="char-counter" class="text-muted small">' .
                get_string('charactersremaining', 'local_equipment', 250) .
                '</div>'
        );

        // Preview section
        $mform->addElement('static', 'previewheader', '', '<h4>' . get_string('preview', 'local_equipment') . '</h4>');
        $mform->addElement(
            'static',
            'recipientcount',
            get_string('estimatedrecipients', 'local_equipment'),
            '<span id="recipient-count">' . get_string('calculating', 'local_equipment') . '</span>'
        );

        // Action buttons
        // $buttonarray = [];
        // $buttonarray[] = $mform->createElement(
        //     'submit',
        //     'submitbutton',
        //     get_string('sendmessage', 'local_equipment'),
        //     ['class' => 'btn btn-primary']
        // );
        // $buttonarray[] = $mform->createElement(
        //     'cancel',
        //     'cancel',
        //     get_string('cancel'),
        //     ['class' => 'btn btn-secondary']
        // );
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'send', get_string('sendtexts', 'local_equipment'));
        $buttonarray[] = $mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
        // $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);

        // Add JavaScript for character counting and recipient preview
        $this->add_character_counter_js();
    }

    /**
     * Validate the form data.
     *
     * @param array $data Form data
     * @param array $files Form files
     * @return array Array of errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate message length
        if (isset($data['message'])) {
            $message = trim($data['message']);

            if (empty($message)) {
                $errors['message'] = get_string('required');
            } else if (strlen($message) > 250) {
                $errors['message'] = get_string('maximumchars', '', 250);
            } else if (strlen($message) < 10) {
                $errors['message'] = get_string('minimumchars', '', 10);
            }
        }

        return $errors;
    }

    /**
     * Add JavaScript for character counting and recipient preview.
     */
    protected function add_character_counter_js() {
        global $PAGE;

        // Load the AMD module for form functionality
        $PAGE->requires->js_call_amd('local_equipment/mass_text_form', 'init');
    }
}
