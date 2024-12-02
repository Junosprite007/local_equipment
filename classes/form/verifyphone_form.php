<?php
// This file is part of Moodle - http://moodle.org/
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
 * Testing outgoing text configuration form
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\form;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

class verifyphone_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        // Recipient.
        global $USER, $OUTPUT;

        $userid = $USER->id;
        $editprofileurl = new \moodle_url('/user/edit.php', array('id' => $userid));
        $editprofilelink = \html_writer::link($editprofileurl, get_string('editmyprofile'));
        $vccformurl = new \moodle_url('/local/equipment/virtualcourseconsent/index.php');
        $vccformlink = \html_writer::link($vccformurl, get_string('fillouttheform', 'local_equipment', get_string('virtualcourseconsent', 'local_equipment')));
        $providerconfigurl = new \moodle_url('/admin/settings.php?section=managetoolphoneverification');
        $providerconfiglink = \html_writer::link($providerconfigurl, get_string('phoneproviderconfiguration', 'local_equipment'));

        // Phone number objects.
        $phone1obj = local_equipment_parse_phone_number($USER->phone1);
        $phone2obj = local_equipment_parse_phone_number($USER->phone2);

        $phone1formatted = '';
        $phone2formatted = '';

        $phone1 = $phone1obj->phone;
        $phone2 = $phone2obj->phone;

        if (empty($phone1obj->errors) && $phone1) {
            $phone1formatted = local_equipment_format_phone_number($phone1);
        }
        if (empty($phone2obj->errors) && $phone2) {
            $phone2formatted = local_equipment_format_phone_number($phone2);
        }

        $phoneoptions = [];
        $phone_user = local_equipment_user_phone_exists($USER->id);
        $phone_vcc = local_equipment_vccsubmission_phone_exists($USER->id);
        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // var_dump($phone_user);
        // var_dump($phone_vcc);
        // $phone = false;

        // Could be a phone number, could be NULL, or could be false.

        if (empty($phone2obj->errors) && empty($phone1obj->errors)) {
            $phoneoptions = [$phone1 => $phone1formatted, $phone2 => $phone2formatted];
        } else if (empty($phone2obj->errors)) {
            $phoneoptions = [$phone2 => $phone2formatted];
        } else if (empty($phone1obj->errors)) {
            $phoneoptions = [$phone1 => $phone1formatted];
        } else {
            $phoneoptions = [];
        }

        $phonedefault = '';

        // Set the selected phone number for setDefault later.
        if ((isset($phoneoptions[$phone1]) && isset($phoneoptions[$phone2])) || isset($phoneoptions[$phone2])) {
            $phonedefault = $phoneoptions[$phone2];
        } elseif (isset($phoneoptions[$phone1])) {
            $phonedefault = $phoneoptions[$phone1];
        } else if ($phone_user) {
            $phonedefault = $phone_user;
        } else if ($phone_vcc) {
            $phonedefault = $phone_vcc;
        }

        if ($phonedefault) {
            $mform->addElement('text', 'tonumber', get_string('entermobilephone', 'local_equipment'), $phoneoptions);
            $mform->setType('tonumber', PARAM_TEXT);
            $mform->setDefault('tonumber', $phonedefault);
            $mform->addRule('tonumber', get_string('required'), 'required');
        } else {
            $mform->addElement(
                'html',
                '<div class="alert alert-warning">'
                    . get_string('haventfilledoutform', 'local_equipment', $vccformlink)
                    . '</div>'
            );
            // $mform->addRule('nophonefound', get_string('required'), 'required');
        }
        // if (!empty($phone2obj->errors)) {
        //     $msg = get_string('somethingwrong_phone2', 'local_equipment') . ' ' . get_string('seeerrorsbelow', 'local_equipment');
        //     $notification = \html_writer::div(join("<br>", $phone2obj->errors), 'alert alert-warning');
        //     $mform->addElement('static', 'activeenddatewillalsoneedtobeupdated', '', $notification);
        // }
        if ($phonedefault) {
            $buttonarray = array();
            $buttonarray[] = $mform->createElement('submit', 'send', get_string('sendtest', 'local_equipment'));
            $buttonarray[] = $mform->createElement('cancel');

            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');
        }
    }

    /**
     * Validate Form field, should be a valid text format or a username that matches with a Moodle user.
     *
     * @param array $data
     * @param array $files
     * @return array
     * @throws \dml_exception|\coding_exception
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if (isset($data['tonumber']) && $data['tonumber']) {
        }

        return $errors;
    }
}
