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
        global $DB, $OUTPUT;

        // Fetch partnerships and display in a table
        $activepartnerships = $DB->get_records('local_equipment_partnership', ['active' => 1]);

        $allpartnershipcourses = [];
        $allpartnershipcourses_json = [];
        // $partnershipdata = [];
        // $coursedata = [];


        $partnershipcategories = local_equipment_get_partnership_categories_this_year(null, false, true, 'partnerships');

        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump($partnershipcategories);
        // echo '</pre>';
        // die();

        foreach ($partnershipcategories->partnerships as $id => $partnership) {
            $partnership->coursedata = [];
            // $partnership = $DB->get_record('local_equipment_partnership', ['id' => $id]);
            $allpartnershipcourses[$id] = local_equipment_get_partnership_courses_this_year($partnership->listingid);
            $courses = $allpartnershipcourses[$id]->courses_formatted;
            foreach ($courses as $id => $course) {
                $coursedata[$id] = "$id – $course";
                $partnership->coursedata[$id] = $coursedata[$id];
            }

            $partnershipdata[$partnership->id] = $partnership;
        }

        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump(array_keys($allpartnershipcourses));
        // echo '</pre>';
        // die();

        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump($allpartnershipcourses);
        // echo '</pre>';
        // die();

        foreach ($allpartnershipcourses as $id => $courses) {
            $allpartnershipcourses_json[$id] = $courses->courses_formatted;
        }




        // foreach ($partnershipcategories->partnershipids as $id) {
        //     $allpartnershipcourses[$id] = local_equipment_get_partnership_courses_this_year($id);
        // }

        // foreach ($allpartnershipcourses as $id => $courses) {
        //     $allpartnershipcourses_json[$id] = $courses->courses_formatted;
        // }


        // $i = 0;
        // foreach ($activepartnerships as $partnership) {
        //     // echo '<br />';
        //     // echo '<pre>';
        //     // var_dump($partnership);
        //     // echo '</pre>';
        //     // die();
        //     // $coursejoin = $DB->get_records('local_equipment_partnership_course', ['partnershipid' => $partnership->id]);
        //     if ($coursesobj = local_equipment_get_partnership_courses_this_year($partnership->id)) {
        //         $courses = $coursesobj->courses_formatted;

        //         foreach ($courses as $id => $course) {
        //             $coursedata[$id] = "$id – $course";
        //             $partnership->coursedata[$id] = $coursedata[$id];
        //         }
        //     }


        //     // foreach ($coursejoin as $join) {

        //     //     $course = $DB->get_record('course', ['id' => $join->courseid]);

        //     //     if ($course) {
        //     //         $coursedata[$course->id] = "$course->id – $course->fullname";
        //     //         $partnership->coursedata[$course->id] = "$course->id – $course->fullname";
        //     //     }
        //     // }

        //     $partnershipdata[$partnership->id] = $partnership;
        // }




        // echo '<pre>';
        // var_dump($coursedata);
        // echo '</pre>';
        // echo '<br />';

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
            'coursesthisyear',
            get_string('coursesthisyear', 'local_equipment'),
            [
                'id' => 'id_coursesthisyear',
                'data-coursesthisyear' => json_encode($allpartnershipcourses_json)
            ]
        );
        $mform->setType('coursesthisyear', PARAM_RAW);

        $mform->addElement(
            'hidden',
            'familiesdata',
            '',
            ['id' => 'id_familiesdata']
        );
        $mform->setType('familiesdata', PARAM_RAW);

        $mform->addElement('select', 'partnershipcourselist', get_string('partnershipcourselist', 'local_equipment'), $partnershipcategories->partnershipids_partnershipnames);
        $mform->setType('partnershipcourselist', PARAM_RAW);
        // $mform->setDefault('partnershipcourselist', '0');

        // if (!empty($partnershipdata)) {
        //     $tablehtml = '<table class="local-equipment_generaltable">';
        //     $tablehtml .= '<thead><tr><th>' . get_string('partnershipid', 'local_equipment') . '</th><th>' . get_string('partnershipname', 'local_equipment') . '</th></tr></thead>';
        //     $tablehtml .= '<tbody>';
        //     $rowclass = 'r0';

        //     foreach ($partnershipdata as $partnership) {
        //         $courses = [];

        //         if (!empty($partnership->coursedata)) {
        //             $courses = $partnership->coursedata;
        //         } else {
        //             $courses[] = get_string('nocoursesfoundforthispartnership', 'local_equipment');
        //         }

        //         // Create unique ID for the collapsible section
        //         $uniqueid = html_writer::random_id('partnership-');

        //         // Header row
        //         $tablehtml .= '<tr class="' . $rowclass . '">';
        //         $tablehtml .= '<td>' . s($partnership->id) . '</td>';
        //         $tablehtml .= '<td>';

        //         // Create collapsible button
        //         $button = html_writer::tag(
        //             'button',
        //             $OUTPUT->pix_icon('t/collapsed', '') . ' ' . $partnership->name,
        //             array(
        //                 'class' => 'btn btn-link w-100 text-left d-flex align-items-center collapsed', // Added 'collapsed' class
        //                 'type' => 'button',
        //                 'data-toggle' => 'collapse',
        //                 'data-target' => '#' . $uniqueid,
        //                 'aria-expanded' => 'false',
        //                 'aria-controls' => $uniqueid
        //             )
        //         );

        //         // Create collapsible content
        //         $content = html_writer::div(
        //             local_equipment_generate_course_table($partnership->listingid),
        //             'collapse',
        //             array('id' => $uniqueid)
        //         );

        //         $tablehtml .= $button . $content;
        //         $tablehtml .= '</td></tr>';

        //         $rowclass = ($rowclass === 'r0') ? 'r1' : 'r0';
        //     }

        //     $tablehtml .= '</tbody></table>';
        //     $mform->addElement('html', $tablehtml);
        // }

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('button', 'preprocess', get_string('preprocess', 'local_equipment'), ['class' => 'preprocessbutton']);
        $buttonarray[] = &$mform->createElement('button', 'shownexterror', get_string('shownexterror', 'local_equipment'), ['disabled' => true, 'class' => 'shownexterror-container']);
        $buttonarray[] = &$mform->createElement('button', 'noerrorsfound', get_string('noerrorsfound', 'local_equipment'), ['disabled' => true, 'class' => 'noerrorsfound-container alert-success', 'hidden' => true]);


        $mform->addElement('html', '<div class="local-equipment-errornavigation-container">');
        $mform->addGroup($buttonarray, 'preprocessanderrors_before', '', [' '], false);
        $mform->addElement('html', '</div>');

        // Add a container div for flex layout.
        $mform->addElement('html', '<div class="local-equipment-bulkfamilyupload-container">');

        // Add the textarea.
        $mform->addElement(
            'textarea',
            'familiesinputdata',
            get_string('familiesinputdata', 'local_equipment'),
            array('rows' => 10, 'cols' => 50, 'class' => 'local-equipment-bulkfamilyupload-textarea')
        );
        $mform->setType('familiesinputdata', PARAM_RAW);
        $mform->addRule('familiesinputdata', null, 'required', null, 'client');

        // Add the feedback div.
        $mform->addElement('html', '<div id="id_familypreprocessdisplay" class="local-equipment-bulkfamilyupload-preprocess-output"></div>');

        // Close the container div.
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="local-equipment-errornavigation-container">');
        $mform->addGroup($buttonarray, 'preprocessanderrors_after', '', [' '], false);
        $mform->addElement('html', '</div>');

        // Add action buttons, but don't use add_action_buttons()
        $mform->addElement('submit', 'submitbutton', get_string('uploadandenroll', 'local_equipment'), ['id' => 'id_submitbutton', 'disabled' => 'disabled']);
        $mform->closeHeaderBefore('buttonar');
    }
}
