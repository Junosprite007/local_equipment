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
 * Virtual course consent (vcc) submission management page.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once('./lib.php');

admin_externalpage_setup('local_equipment_vccsubmissions');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/equipment/vccsubmissions.php'));
$PAGE->set_title(get_string('managevccsubmissions', 'local_equipment'));
$PAGE->set_heading(get_string('managevccsubmissions', 'local_equipment'));

require_capability('local/equipment:managevccsubmissions', $context);

// Handle delete action
$delete = optional_param('delete', 0, PARAM_INT);
if ($delete && confirm_sesskey()) {
    $DB->delete_records('local_equipment_vccsubmission', ['id' => $delete]);
    \core\notification::success(get_string('vccsubmissiondeleted', 'local_equipment'));
    redirect($PAGE->url);
}

echo $OUTPUT->header();

// Add VCC submission button
$addurl = new moodle_url('/local/equipment/vccsubmissionform.php');
echo $OUTPUT->single_button($addurl, get_string('addvccsubmission', 'local_equipment'), 'get');

// Set up the table
$table = new flexible_table('local-equipment-vccsubmissions');

$columns = [
    'id',
    'user',
    'partnership',
    'pickup',
    'confirmationid',
    'pickupmethod',
    'timecreated',
    'actions'
];

$headers = array_map(function ($column) {
    return get_string($column, 'local_equipment');
}, $columns);

$table->define_columns($columns);
$table->define_headers($headers);

$table->define_baseurl($PAGE->url);
$table->sortable(true, 'timecreated', SORT_DESC);
$table->no_sorting('actions');
$table->collapsible(false);
$table->initialbars(true);
$table->set_attribute('id', 'vccsubmissions');
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

$select = "vs.id, vs.userid, vs.partnershipid, vs.pickupid, vs.confirmationid, vs.pickupmethod, vs.timecreated";
$from = "{local_equipment_vccsubmission} vs";
$where = "1=1";
$params = [];

if ($table->get_sql_sort()) {
    $sort = $table->get_sql_sort();
} else {
    $sort = 'vs.timecreated DESC';
}

$submissions = $DB->get_records_sql("SELECT $select FROM $from WHERE $where ORDER BY $sort", $params);

foreach ($submissions as $submission) {
    $row = [];
    $row[] = $submission->id;
    $row[] = fullname($DB->get_record('user', ['id' => $submission->userid]));
    $row[] = $DB->get_field('local_equipment_partnership', 'name', ['id' => $submission->partnershipid]);
    $row[] = userdate($DB->get_field('local_equipment_pickup', 'pickupdate', ['id' => $submission->pickupid]));
    $row[] = $submission->confirmationid;
    $row[] = $submission->pickupmethod;
    $row[] = userdate($submission->timecreated);

    $actions = '';
    $viewurl = new moodle_url('/local/equipment/vccsubmissionview.php', ['id' => $submission->id]);
    $editurl = new moodle_url('/local/equipment/vccsubmissionform.php', ['id' => $submission->id]);
    $deleteurl = new moodle_url($PAGE->url, ['delete' => $submission->id, 'sesskey' => sesskey()]);

    $actions .= $OUTPUT->action_icon($viewurl, new pix_icon('i/search', get_string('view')));
    $actions .= $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
    $actions .= $OUTPUT->action_icon(
        $deleteurl,
        new pix_icon('t/delete', get_string('delete')),
        new confirm_action(get_string('confirmdeletevccsubmission', 'local_equipment'))
    );

    $row[] = $actions;

    $table->add_data($row);
}

$table->finish_output();

echo $OUTPUT->footer();
