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
 * Upload and enroll multiple families (parents and their students).
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\plugininfo\local;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/equipment/lib.php');

class addbulkfamilies_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        global $DB;


        // Fetch partnerships and display in a table
        $activepartnerships = $DB->get_records('local_equipment_partnership', ['active' => 1]);
        $partnershipdata = [];
        $coursedata = [];

        $i = 0;
        foreach ($activepartnerships as $partnership) {

            // Eventually, we'll want to use the join table only, but we may need the below if/else statement for now.
            // if (!empty(json_decode($partnership->courseids))) {
            //     $courseids = json_decode($partnership->courseids);
            // } else {
            $coursejoin = $DB->get_records('local_equipment_partnership_course', ['partnershipid' => $partnership->id]);
            // $courseids = [];
            $partnership->coursedata = [];
            // }

            foreach ($coursejoin as $join) {
                $course = $DB->get_record('course', ['id' => $join->courseid]);

                if ($course) {
                    // $coursedata[] = "$course->id – $course->fullname";
                    $coursedata[$course->id] = "$course->id – $course->fullname";
                    $partnership->coursedata[$course->id] = "$course->id – $course->fullname";
                }
                // $courseids[] = $course->id;
            }
            // foreach ($courseids as $courseid) {
            // }
            // var_dump($partnership->id);

            // $partnership->courseids = json_decode($partnership->courseids);
            $partnershipdata[$partnership->id] = $partnership;
        }
        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump($coursedata);
        // echo '</pre>';


        // Use hidden field for sending partnership data over to JavaScript
        $mform->addElement(
            'hidden',
            'partnershipdata',
            get_string('partnership', 'local_equipment'),
            ['data-partnerships' => json_encode($partnershipdata), 'id' => 'id_partnershipdata']
        );
        $mform->setType('partnershipdata', PARAM_RAW);



        // Use hidden field for sending course data over to JavaScript
        $mform->addElement(
            'hidden',
            'coursedata',
            get_string('course'),
            ['data-courses' => json_encode($coursedata), 'id' => 'id_coursedata']
        );
        $mform->setType('coursedata', PARAM_RAW);

        if (!empty($partnershipdata)) {
            $tablehtml = '<table class="generaltable">';
            $tablehtml .= '<thead><tr><th>' . get_string('partnershipid', 'local_equipment') . '</th><th>' . get_string('partnershipname', 'local_equipment') . '</th></tr></thead>';
            $tablehtml .= '<tbody>';
            $rowclass = 'r0';
            foreach ($partnershipdata as $partnership) {
                $courses = [];




                // echo '<br />';
                // echo '<br />';
                // echo '<br />';
                // echo '<pre>';
                // var_dump($partnership);
                // echo '</pre>';
                // die();

                if (!empty($partnership->coursedata)) {
                    $courses = $partnership->coursedata;
                } else {
                    $courses[] = get_string('nocoursesfoundforthispartnership', 'local_equipment');
                }
                $tablehtml .= '<tr class="collapsible ' . $rowclass . '">';
                $tablehtml .= '<td>' . s($partnership->id) . '</td>';
                $tablehtml .= '<td>'
                    . s($partnership->name)
                    . '<div class="ml-4">' . implode('<br />', $courses) . '</div>'
                    . '</td>';
                $tablehtml .= '</tr>';
                $rowclass = ($rowclass === 'r0') ? 'r1' : 'r0'; // Alternate row class
            }
            $tablehtml .= '</tbody></table>';
            $mform->addElement('html', $tablehtml);
        }


        // Create the Pre-process button
        $preprocess_button = $mform->createElement(
            'button',
            'preprocess',
            get_string('preprocess', 'local_equipment'),
            array('class' => 'preprocessbutton')
        );

        // Add the button to the top of the form.
        $mform->addElement($preprocess_button);

        // Add a container div for flex layout.
        $mform->addElement('html', '<div class="local-equipment-bulkfamilyupload-container">');

        // Add the textarea.
        $mform->addElement(
            'textarea',
            'familiesinputdata',
            get_string('familiesinputdata', 'local_equipment'),
            array('rows' => 20, 'cols' => 50, 'class' => 'local-equipment-bulkfamilyupload-textarea')
        );
        $mform->setType('familiesinputdata', PARAM_RAW);
        $mform->addRule('familiesinputdata', null, 'required', null, 'client');

        // Add the feedback div.
        $mform->addElement('html', '<div id="family-preprocess-display" class="local-equipment-bulkfamilyupload-preprocess-output"></div>');

        // Close the container div.
        $mform->addElement('html', '</div>');

        // Add the Pre-process button to the bottom of the form as well.
        $mform->addElement($preprocess_button);

        // Add action buttons, but don't use add_action_buttons()
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('uploadandenroll', 'local_equipment'), ['id' => 'id_submitbutton', 'disabled' => 'disabled']);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');











        // $this->add_action_buttons(true, get_string('uploadandenroll', 'local_equipment'));

        // Get the submit button element
        // $submitButton = $mform->getElement('submitbutton');

        // // Set custom attributes for the submit button
        // $submitButton->updateAttributes([
        //     'disabled' => 'disabled',
        //     'id' => 'id_submitbutton'
        // ]);
    }
}
