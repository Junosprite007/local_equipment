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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/equipment/lib.php');

class addbulkfamilies_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        global $DB;

        // Fetch partnerships and display in a table. I think it's a pretty snazzy-lookin' table.
        $allpartnershipcourses = [];
        $allpartnershipcourses_json = [];
        $partnershipcategories = local_equipment_get_partnership_categories_for_school_year(null, false, true);

        foreach ($partnershipcategories->partnerships as $id => $partnership) {
            $partnership->coursedata = [];
            $allpartnershipcourses[$id] = local_equipment_get_partnership_courses_this_year($partnership->listingid);
            $courses = $allpartnershipcourses[$id]->courses_formatted;

            foreach ($courses as $id => $course) {
                $coursedata[$id] = "$id – $course";
                $partnership->coursedata[$id] = $coursedata[$id];
            }

            $partnershipdata[$partnership->id] = $partnership;
        }

        foreach ($allpartnershipcourses as $id => $courses) {
            $allpartnershipcourses_json[$id] = $courses->courses_formatted;
        }

        // Use hidden field for sending partnership data over to JavaScript. Only admins are using this, so it shouldn't be a
        // security risk... I don't think anyway.
        $mform->addElement(
            'hidden',
            'partnershipdata',
            get_string('partnership', 'local_equipment'),
            ['data-partnerships' => json_encode($partnershipdata), 'id' => 'id_partnershipdata']
        );
        $mform->setType('partnershipdata', PARAM_RAW);

        // Use hidden field for sending course data over to JavaScript. Again, admins only.
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

        // This field will be empty at first but will be populated upon error free, pre-processing of the text input. This data will
        // be used for the final user creation, update, and course enrollment process.
        // TODO: This value should default to text input stored within the user's $SESSION, but I'm not currently sure how to update
        // Moodle session data from JavaScript.
        $mform->addElement(
            'hidden',
            'familiesdata',
            '',
            ['id' => 'id_familiesdata']
        );
        $mform->setType('familiesdata', PARAM_RAW);

        // This dropdown menu is purely for admin users' convenience. It will allow them to select a partnership, see its ID, and
        // view all its courses with corresponding course IDs.
        $mform->addElement('select', 'partnershipcourselist', get_string('partnershipcourselist', 'local_equipment'), $partnershipcategories->partnershipids_partnershipnames);
        $mform->setType('partnershipcourselist', PARAM_RAW);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('button', 'preprocess', get_string('preprocess', 'local_equipment'), ['class' => 'preprocessbutton']);
        $buttonarray[] = &$mform->createElement('button', 'shownexterror', get_string('shownexterror', 'local_equipment'), ['disabled' => true, 'class' => 'shownexterror-container']);
        $buttonarray[] = &$mform->createElement('button', 'noerrorsfound', get_string('noerrorsfound', 'local_equipment'), ['disabled' => true, 'class' => 'noerrorsfound-container alert-success', 'hidden' => true]);

        $mform->addElement('html', '<div class="local-equipment-errornavigation-container">');
        $mform->addGroup($buttonarray, 'preprocessanderrors_before', '', [' '], false);
        $mform->addElement('html', '</div>');

        // Add a container div for flex layout for viewing the text input area and pre-process box.
        $mform->addElement('html', '<div class="local-equipment-bulkfamilyupload-container">');

        // This is intentionally a basic text area, not tinyMCE, since I want to process the text input using a single or double
        // new line character: '\n' to define data for a given user or '\n\n' to define a new family. See 'processFamily' in the
        // JavaScript for more details.
        $mform->addElement(
            'textarea',
            'familiesinputdata',
            get_string('familiesinputdata', 'local_equipment'),
            array('rows' => 10, 'cols' => 50, 'class' => 'local-equipment-bulkfamilyupload-textarea')
        );
        $mform->setType('familiesinputdata', PARAM_RAW);
        $mform->addRule('familiesinputdata', null, 'required', null, 'client');

        // Add the pre-process div to show the output of the pre-processed text input. This is where errors are shown. This div
        // determines whether or not the "Upload & enroll" button is enabled.
        $mform->addElement('html', '<div id="id_familypreprocessdisplay" class="local-equipment-bulkfamilyupload-preprocess-output"></div>');

        // Close the container div with the input and pre-process info.
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="local-equipment-errornavigation-container">');
        $mform->addGroup($buttonarray, 'preprocessanderrors_after', '', [' '], false);
        $mform->addElement('html', '</div>');

        // Add action buttons, but don't use add_action_buttons().
        $mform->addElement('submit', 'submitbutton', get_string('uploadandenroll', 'local_equipment'), ['id' => 'id_submitbutton', 'disabled' => 'disabled']);
        $mform->closeHeaderBefore('buttonar');
    }
}
