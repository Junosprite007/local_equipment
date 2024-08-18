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
 * Edit agreement page.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/equipment/classes/form/editagreement_form.php');

$id = required_param('id', PARAM_INT);

admin_externalpage_setup('local_equipment_agreements');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/equipment/agreements/editagreement.php', ['id' => $id]));
$PAGE->set_title(get_string('editagreement', 'local_equipment'));
$PAGE->set_heading(get_string('editagreement', 'local_equipment'));

require_capability('local/equipment:manageagreements', $context);

$agreement = $DB->get_record('local_equipment_agreement', ['id' => $id], '*', MUST_EXIST);

$mform = new local_equipment\form\editagreement_form(null, ['agreement' => $agreement]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/equipment/agreements.php'));
} else if ($data = $mform->get_data()) {
    // Create a new version of the agreement
    $newagreement = new stdClass();
    $newagreement->title = $data->title;
    $newagreement->content = $data->content['text'];
    $newagreement->agreementtype = $data->agreementtype;
    $newagreement->active = $data->active;
    $newagreement->requiresignature = $data->requiresignature;
    $newagreement->timecreated = time();
    $newagreement->timemodified = time();
    $newagreement->startdate = $data->startdate;
    $newagreement->enddate = $data->enddate;
    $newagreement->version = $agreement->version + 1;
    $newagreement->parentid = $agreement->id;

    $DB->insert_record('local_equipment_agreement', $newagreement);

    // Deactivate the old version
    $agreement->active = 0;
    $agreement->timemodified = time();
    $DB->update_record('local_equipment_agreement', $agreement);

    redirect(new moodle_url('/local/equipment/agreements.php'), get_string('agreementupdated', 'local_equipment'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$mform->set_data($agreement);

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
