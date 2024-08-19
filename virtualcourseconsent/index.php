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
 * Virtual course consent form submission page that users (parents) will
 * be filling out and submitting.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/equipment/classes/form/consent_form.php');

require_login();

$PAGE->set_url(new moodle_url('/local/equipment/consent_form.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('consentformtitle', 'local_equipment'));
$PAGE->set_heading(get_string('consentformheading', 'local_equipment'));

$form = new \local_equipment\form\consent_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($data = $form->get_data()) {
    if (local_equipment_save_consent_form($data)) {
        redirect(new moodle_url('/'), get_string('consentformsubmitted', 'local_equipment'), null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        redirect(new moodle_url('/local/equipment/consent_form.php'), get_string('consentformsubmissionerror', 'local_equipment'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

echo $OUTPUT->header();
$form->display();
$PAGE->requires->js_call_amd('local_equipment/consent_form', 'init');
echo $OUTPUT->footer();
