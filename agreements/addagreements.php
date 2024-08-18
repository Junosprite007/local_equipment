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
 * Add agreement page.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/equipment/classes/form/addagreements_form.php');

admin_externalpage_setup('local_equipment_addagreements');
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/equipment/agreements/addagreement.php'));
$PAGE->set_title(get_string('addagreement', 'local_equipment'));
$PAGE->set_heading(get_string('addagreement', 'local_equipment'));

require_capability('local/equipment:manageagreements', $context);

$mform = new local_equipment\form\addagreements_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/equipment/agreements.php'));
} else if ($data = $mform->get_data()) {
    $agreement = new stdClass();
    $agreement->title = $data->title;
    $agreement->content = $data->content['text'];
    $agreement->agreementtype = $data->agreementtype;
    $agreement->timecreated = time();
    $agreement->activestarttime = $data->activestarttime;
    $agreement->activeendtime = $data->activeendtime;
    $agreement->requireelectronicsignature = $data->requireelectronicsignature;
    $agreement->version = 1;

    $DB->insert_record('local_equipment_agreement', $agreement);
    redirect(new moodle_url('/local/equipment/agreements.php'), get_string('agreementadded', 'local_equipment'), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
