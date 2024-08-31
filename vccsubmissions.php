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
// $addurl = new moodle_url('/local/equipment/vccsubmissionform.php');
// echo $OUTPUT->single_button($addurl, get_string('addvccsubmission', 'local_equipment'), 'get');

// Set up the table
$table = new flexible_table('local-equipment-vccsubmissions');

$columns = [
    'timecreated',
    'parent_firstname',
    'parent_lastname',
    'parent_email',
    'parent_phone2',
    'partnership_name',
    'pickup',
    'students',
    'pickupmethod',
    'pickuppersonname',
    'pickuppersonphone',
    'pickuppersondetails',
    'usernotes',
    'adminnotes',
    'actions'
];
$columns_nosort = [
    'pickup',
    'students',
    'actions'
];

$headers = array_map(function ($column) {
    return get_string($column, 'local_equipment');
}, $columns);

$table->define_columns($columns);
$table->define_headers($headers);

$nowrap_header = 'local-equipment-nowrap-header';
$nowrap_cell = 'local-equipment-nowrap-cell';

foreach ($columns as $column) {
    $table->column_class($column, $nowrap_header);
}

$table->column_class('timecreated', $nowrap_cell);
$table->column_class('pickup', $nowrap_cell);
// $table->column_class('pickuppersondetails', $minwidth_cell);
// $table->column_class('usernotes', $minwidth_cell);
// $table->column_class('adminnotes', $minwidth_cell);

$table->define_baseurl($PAGE->url);
$table->sortable(true, 'timecreated', SORT_DESC);
foreach ($columns_nosort as $column) {
    // $table->column_suppress($column);
    $table->no_sorting($column);
}
$table->collapsible(true);
$table->initialbars(true);
$table->set_attribute('id', 'vccsubmissions');
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

$select = "vccsubmission.id, vccsubmission.userid, vccsubmission.partnershipid, vccsubmission.studentids, vccsubmission.pickupid, vccsubmission.pickupmethod, vccsubmission.pickuppersonname, vccsubmission.pickuppersonphone, vccsubmission.pickuppersondetails, vccsubmission.usernotes, vccsubmission.adminnotes, vccsubmission.timecreated,
        user.firstname AS parent_firstname, user.lastname AS parent_lastname, user.email AS parent_email, user.phone2 AS parent_phone2,
        partnership.name AS partnership_name, partnership.pickup_extrainstructions, partnership.pickup_apartment, partnership.pickup_streetaddress, partnership.pickup_city, partnership.pickup_state, partnership.pickup_zipcode,
        pickup.starttime, pickup.endtime";

$from = "{local_equipment_vccsubmission} vccsubmission
        LEFT JOIN {user} user ON vccsubmission.userid = user.id
        LEFT JOIN {local_equipment_partnership} partnership ON vccsubmission.partnershipid = partnership.id
        LEFT JOIN {local_equipment_pickup} pickup ON vccsubmission.pickupid = pickup.id";
$where = "1=1";
$params = [];

if ($table->get_sql_sort()) {
    $sort = $table->get_sql_sort();
} else {
    $sort = 'vccsubmission.timecreated DESC';
}

$submissions = $DB->get_records_sql("SELECT $select FROM $from WHERE $where ORDER BY $sort", $params);

// echo '<pre>';
// var_dump($submissions);
// echo '</pre>';
// die();

// This is the first pass where we merge records of parents who have multiple children and did not put that all on one form.
foreach ($submissions as $submission) {
}

$formattedpickuplocation = get_string('contactusforpickup', 'local_equipment');

foreach ($submissions as $submission) {
    $pickup_extrainstructions = $submission->pickup_extrainstructions;

    $datetime = userdate($submission->starttime, get_string('strftimedate', 'langconfig')) . ' ' .
        userdate($submission->starttime, get_string('strftimetime', 'langconfig')) . ' - ' .
        userdate($submission->endtime, get_string('strftimetime', 'langconfig'));

    $pickup_pattern = '/#(.*?)#/';
    $pickup_name = $submission->pickup_city;

    if (
        preg_match($pickup_pattern, $submission->pickup_extrainstructions, $matches)
    ) {
        $pickup_name = $submission->locationname = $matches[1];
        $submission->pickup_extrainstructions = trim(preg_replace($pickup_pattern, '', $submission->pickup_extrainstructions, 1));
    }
    if ($submission->pickup_streetaddress) {
        $formattedpickuplocation = "$pickup_name — $datetime — $submission->pickup_streetaddress, $submission->pickup_city, $submission->pickup_state $submission->pickup_zipcode";
        // if (isset($pickuptimedata[$id])) {
        //     $formattedpickuptimes[$id] = $pickuptimedata[$id][$i];
        //     $i++;
        // }
    }

    $submission->starttime = $submission->starttime ? userdate($submission->starttime) : get_string('contactusforpickup', 'local_equipment');
    // $submission->studentids = json_decode($submission->studentids);

    // $submission->pickupid != 0 ? $pickuptime = $DB->get_record('local_equipment_pickup', ['id' => $submission->pickupid]) : $pickuptime = get_string('contactusforpickup', 'local_equipment');

    // $pickuptime = $DB->get_record('local_equipment_pickup', ['id' => $submission->pickupid]) ?? get_string('contactusforpickup', 'local_equipment');
    // echo '<br />';
    // echo '<br />';
    // echo '<br />';
    // echo '<pre>';
    // var_dump($submission);
    // echo '</pre>';
    // die();



    // id
    // parent_name
    // parent_email
    // parent_phone
    // partnership
    // pickup
    // students
    // confirmationid
    // pickupmethod
    // timecreated
    // actions

    $minwidth_cell = 'local-equipment-minwidth-cell';

    $row = [];
    // $row[] = $submission->id;
    $row[] = userdate($submission->timecreated);
    $row[] = $submission->parent_firstname;
    $row[] = $submission->parent_lastname;
    $row[] = $submission->parent_email;
    $row[] = $submission->parent_phone2;
    // $row[] = fullname($DB->get_record('user', ['id' => $submission->userid]));
    $row[] = $submission->partnership_name;
    $row[] = $formattedpickuplocation;
    $row[] = local_equipment_get_vcc_students($submission);
    // $row[] = $submission->confirmationid;
    $row[] = $submission->pickupmethod;
    $row[] = $submission->pickuppersonname;
    $row[] = $submission->pickuppersonphone;
    // $row[] = html_writer::tag('div', $submission->pickuppersondetails, ['class' => $minwidth_cell]);
    // $row[] = html_writer::tag('div', $submission->usernotes, ['class' => $minwidth_cell]);
    // $row[] = html_writer::tag('div', $submission->adminnotes, ['class' => $minwidth_cell]);
    $row[] = $submission->pickuppersondetails;
    $row[] = $submission->usernotes;
    $row[] = $submission->adminnotes;

    $actions = '';
    $viewurl = new moodle_url('/local/equipment/vccsubmissionview.php', ['id' => $submission->id]);
    $editurl = new moodle_url('/local/equipment/vccsubmissionform.php', ['id' => $submission->id]);
    $deleteurl = new moodle_url($PAGE->url, ['delete' => $submission->id, 'sesskey' => sesskey()]);

    // $actions .= $OUTPUT->action_icon($viewurl, new pix_icon('i/search', get_string('view')));
    // $actions .= $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
    // $actions .= $OUTPUT->action_icon(
    //     $deleteurl,
    //     new pix_icon('t/delete', get_string('delete')),
    //     new confirm_action(get_string('confirmdeletevccsubmission', 'local_equipment'))
    // );

    $row[] = $actions;

    $table->add_data($row);
}

$table->finish_output();

echo $OUTPUT->footer();
