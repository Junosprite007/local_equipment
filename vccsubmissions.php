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
    // 'id',
    'parent_firstname',
    'parent_lastname',
    'parent_email',
    'parent_phone2',
    'partnership',
    'pickup',
    'students',
    'pickupmethod',
    'timecreated',
    'actions'
];
$columns_nosort = [
    // 'id',
    // 'user',
    'partnership',
    'pickup',
    'students',
    'actions'
];

$headers = array_map(function ($column) {
    return get_string($column, 'local_equipment');
}, $columns);

$table->define_columns($columns);
$table->define_headers($headers);

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

$select = "vs.id, vs.userid, vs.partnershipid, vs.studentids, vs.pickupid, vs.pickupmethod, vs.timecreated,
        u.firstname AS parent_firstname, u.lastname AS parent_lastname, u.email AS parent_email, u.phone2 AS parent_phone2,
        p.pickup_extrainstructions, p.pickup_apartment, p.pickup_streetaddress, p.pickup_city, p.pickup_state, p.pickup_zipcode,
        pu.starttime, pu.endtime";

$from = "{local_equipment_vccsubmission} vs
        LEFT JOIN {user} u ON vs.userid = u.id
        LEFT JOIN {local_equipment_partnership} p ON vs.partnershipid = p.id
        LEFT JOIN {local_equipment_pickup} pu ON vs.pickupid = pu.id";
$where = "1=1";
$params = [];

if ($table->get_sql_sort()) {
    $sort = $table->get_sql_sort();
} else {
    $sort = 'vs.timecreated DESC';
}

$submissions = $DB->get_records_sql("SELECT $select FROM $from WHERE $where ORDER BY $sort", $params);

// echo '<pre>';
// var_dump($submissions);
// echo '</pre>';
// die();

// This is the first pass where we merge records of parents who have multiple children and did not put that all on one form.
foreach ($submissions as $submission) {
}

$formattedpickuptimes = ['0' => get_string('contactusforpickup', 'local_equipment')];

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

    $row = [];
    // $row[] = $submission->id;
    $row[] = $submission->parent_firstname;
    $row[] = $submission->parent_lastname;
    $row[] = $submission->parent_email;
    $row[] = $submission->parent_phone2;
    // $row[] = fullname($DB->get_record('user', ['id' => $submission->userid]));
    $row[] = $DB->get_field('local_equipment_partnership', 'name', ['id' => $submission->partnershipid]);
    $row[] = $formattedpickuplocation;
    $row[] = local_equipment_get_vcc_students($submission);
    // $row[] = $submission->confirmationid;
    $row[] = $submission->pickupmethod;
    $row[] = userdate($submission->timecreated);

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
