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

class testoutgoingtextconf_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        // Recipient.
        global $USER, $OUTPUT;

        $userid = $USER->id;
        $editprofileurl = new \moodle_url('/user/edit.php', array('id' => $userid));
        $editprofilelink = \html_writer::link($editprofileurl, get_string('editmyprofile'));
        $providerconfigurl = new \moodle_url('/sms/sms_gateways.php');
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

        if (empty($phone2obj->errors) && empty($phone1obj->errors)) {
            $phoneoptions = [$phone1 => $phone1formatted, $phone2 => $phone2formatted];
        } else if (empty($phone2obj->errors)) {
            $phoneoptions = [$phone2 => $phone2formatted];
        } else if (empty($phone1obj->errors)) {
            $phoneoptions = [$phone1 => $phone1formatted];
        } else {
            $phoneoptions = [];
        }

        $phoneselected = '';

        // Set the selected phone number for setDefault later.
        if ((isset($phoneoptions[$phone1]) && isset($phoneoptions[$phone2])) || isset($phoneoptions[$phone2])) {
            $phoneselected = $phone2;
        } elseif (isset($phoneoptions[$phone1])) {
            $phoneselected = $phone1;
        }

        // Provider dropdown.
        $providerstoshow = local_equipment_get_sms_gateways();

        if (!$providerstoshow) {
            // No providers configured.
            $mform->addElement(
                'static',
                'noproviderfound',
                get_string('selectphonetoverify', 'local_equipment'),
                new \lang_string('noproviderfound', 'local_equipment', $providerconfiglink)
            );
            $mform->addRule('noproviderfound', get_string('required'), 'required');
        } else {
            $mform->addElement('select', 'provider', get_string('selectprovider', 'local_equipment'), $providerstoshow);
            $mform->setType('provider', PARAM_TEXT);
            $mform->addRule('provider', get_string('required'), 'required');
        }

        if (!$phoneselected) {
            // No phone numbers available.
            if ($phone2obj->phone === '' && $phone1obj->phone === '') {
                $text = new \lang_string('nophonefound', 'local_equipment', $editprofilelink);
            } else if (!empty($phone2obj->errors) || !empty($phone1obj->errors)) {
                $text = new \lang_string('novalidphonefound', 'local_equipment', $editprofilelink);
            } else {
                $text = new \lang_string('somethingwrong_phone', 'local_equipment', $editprofilelink);
            }

            $mform->addElement(
                'static',
                'nophonefound',
                get_string('selectphonetoverify', 'local_equipment'),
                $text
            );
            $mform->addRule('nophonefound', get_string('required'), 'required');

            if (!empty($phone2obj->errors)) {
                $msg = get_string('somethingwrong_phone2', 'local_equipment') . ' ' . get_string('seeerrorsbelow', 'local_equipment');
                $notification = \html_writer::div(join("<br>", $phone2obj->errors), 'alert alert-warning');
                $mform->addElement('static', 'activeenddatewillalsoneedtobeupdated', '', $notification);
            } else if (!empty($phone1obj->errors)) {
                $msg = get_string('somethingwrong_phone1', 'local_equipment') . ' ' . get_string('seeerrorsbelow', 'local_equipment');
                echo $OUTPUT->notification($msg, \core\output\notification::NOTIFY_ERROR);
                foreach ($phone1obj->errors as $error) {
                    echo $OUTPUT->notification($error, \core\output\notification::NOTIFY_ERROR);
                }
            }
        } else {
            $mform->addElement('select', 'tonumber', get_string('selectphonetoverify', 'local_equipment'), $phoneoptions);
            $mform->setType('tonumber', PARAM_TEXT);
            $mform->setDefault('tonumber', $phoneselected);
            $mform->addRule('tonumber', get_string('required'), 'required');
            // Test setting.
            $mform->addElement('advcheckbox', 'isatest', get_string('testmessage', 'local_equipment'));
            $mform->setType('isatest', PARAM_INT);
            $mform->setDefault('isatest', 1);
        }

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'send', get_string('sendtest', 'local_equipment'));
        $buttonarray[] = $mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
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
