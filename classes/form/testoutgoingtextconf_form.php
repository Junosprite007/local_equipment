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
        global $USER;
        $userid = $USER->id;
        $editprofileurl = new \moodle_url('/user/edit.php', array('id' => $userid));
        $editprofilelink = \html_writer::link($editprofileurl, get_string('editmyprofile'));
        $providerconfigurl = new \moodle_url('/admin/settings.php?section=managetoolphoneverification');
        $providerconfiglink = \html_writer::link($providerconfigurl, get_string('phoneproviderconfiguration', 'local_equipment'));

        // Phone number.
        $phone1 = local_equipment_parse_phone_number($USER->phone1);
        $phone2 = local_equipment_parse_phone_number($USER->phone2);

        echo '<pre>';
        var_dump($phone1);
        var_dump($phone2);
        echo '</pre>';
        die();

        $phone1 = $phone1->phone;
        $phone2 = $phone2->phone;

        if ($phone1) {
            $phone1formatted = local_equipment_format_phone_number($phone1);
        }
        if ($phone1) {
            $phone2formatted = local_equipment_format_phone_number($phone2);
        }
        if ($phone1formatted === $phone2formatted) {
            $phoneoptions = [$phone1 => $phone1formatted];
        } else {
            $phoneoptions = [$phone1 => $phone1formatted, $phone2 => $phone2formatted];
        }
        $phoneselected = '';

        // Set the selected phone number for setDefault later.
        if (($phoneoptions[$phone1] && $phoneoptions[$phone2]) || ($phoneoptions[$phone1])) {
            $phoneselected = $phoneoptions[$phone1];
        } elseif ($phoneoptions[$phone2]) {
            $phoneselected = $phoneoptions[$phone2];
        }

        // Provider dropdown.
        $providerstoshow = local_equipment_providers_to_show(get_config('local_equipment'));

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
            $mform->addElement(
                'static',
                'nophonefound',
                get_string('selectphonetoverify', 'local_equipment'),
                new \lang_string('nophonefound', 'local_equipment', $editprofilelink)
            );
            $mform->addRule('nophonefound', get_string('required'), 'required');
        } else {
            $mform->addElement('select', 'tonumber', get_string('selectphonetoverify', 'local_equipment'), $phoneoptions);
            $mform->setType('tonumber', PARAM_TEXT);
            $mform->setDefault('tonumber', $phoneselected);
            $mform->addRule('tonumber', get_string('required'), 'required');
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
