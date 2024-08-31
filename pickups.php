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
 * Manage equipment pickups/drop-offs page.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once('./lib.php');


global $DB;
// Ensure only admins can access this page.
admin_externalpage_setup('local_equipment_pickups');

$context = context_system::instance();
$url = new moodle_url('/local/equipment/pickups.php');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('pickups', 'local_equipment'));
$PAGE->set_heading(get_string('pickups', 'local_equipment'));

require_capability('local/equipment:managepickups', $context);

// The names of each column should match the database column names.
// The 'actions' column is not a database column, but is used for edit/delete buttons.
// Define columns and their corresponding database fields
$columns = [
    'pickupdate',
    'starttime',
    'endtime',
    'status',
    'partnershipid',
    'flccoordinatorid',
    'partnershipcoordinatorid',
    'actions',
];
$headers = [
    'pickupdate',
    'starttime',
    'endtime',
    'status',
    'partnership',
    'flccoordinator',
    'partnershipcoordinator',
    'actions',
];
// Columns of the database that should not be sortable.
$dontsortby = [
    'actions',
];

$headers = array_map(function ($strkey) {
    return get_string($strkey, 'local_equipment');
}, $headers);

// Handle delete action.
$delete = optional_param('delete', 0, PARAM_INT);
if ($delete && confirm_sesskey()) {
    if ($DB->delete_records('local_equipment_pickup', ['id' => $delete])) {
        \core\notification::success(get_string('pickupdeleted', 'local_equipment'));
    } else {
        \core\notification::error(get_string('errordeletingpickup', 'local_equipment'));
    }
}

// Output starts here.
echo $OUTPUT->header();

// Add pickup button.
$addurl = new moodle_url('/local/equipment/pickups/addpickups.php');
echo $OUTPUT->single_button($addurl, get_string('addpickup', 'local_equipment'), 'get');

// Set up the table.
$table = new flexible_table('local-equipment-pickups');
$table->define_columns($columns);
$table->define_headers($headers);
$table->define_baseurl($PAGE->url);
$table->sortable(true, 'starttime');
foreach ($dontsortby as $column) {
    $table->no_sorting($column);
}
$table->collapsible(true);
$table->initialbars(true);
$table->set_attribute('id', 'pickups');
$table->set_attribute('class', 'admintable generaltable');
$table->column_style(6, 'overflow-x', 'auto');
$table->setup();

// Construct the SQL query
$fields =
    "ep.*,
    p.name AS partnership,"
    . $DB->sql_concat('fu.firstname', "' '", 'fu.lastname') . " AS flccoordinator,"
    . $DB->sql_concat('pu.firstname', "' '", 'pu.lastname') . " AS partnershipcoordinator";
$from =
    "{local_equipment_pickup} ep
         LEFT JOIN {local_equipment_partnership} p ON ep.partnershipid = p.id
         LEFT JOIN {user} fu ON ep.flccoordinatorid = fu.id
         LEFT JOIN {user} pu ON ep.partnershipcoordinatorid = pu.id";

$endedpickupstoshow = get_config('local_equipment', 'endedpickupstoshow');
if (!isset($endedpickupstoshow)) {
    $endedpickupstoshow = 7; // Fallback to 7 days if the setting is not found
}

$pastseconds = time() - ($endedpickupstoshow * 86400); // Convert days to seconds
$where = '';
$params = [];

if (!($endedpickupstoshow < 0)) {
    $where = "endtime >= $pastseconds";
}

// Get sorting parameters
$sort = $table->get_sql_sort();

// Construct the final SQL query
$sql = "SELECT $fields FROM $from";
if ($where) {
    $sql .= " WHERE $where";
}
// // Replace 'partnership' with 'p.name' in the sort string
if ($sort) {
    $sort = preg_replace('/\bpartnershipid\b/', 'p.name', $sort);
    $sort = preg_replace('/\bflccoordinatorid\b/', $DB->sql_concat('fu.firstname', "' '", 'fu.lastname'), $sort);
    $sort = preg_replace('/\bpartnershipcoordinatorid\b/', $DB->sql_concat('pu.firstname', "' '", 'pu.lastname'), $sort);
    $sql .= " ORDER BY $sort";
}

// Fetch the data
$pickups = $DB->get_records_sql($sql, $params);

foreach ($pickups as $pickup) {
    $row = [];
    $editurl = new moodle_url('/local/equipment/pickups/editpickup.php', ['id' => $pickup->id]);
    $deleteurl = new moodle_url('/local/equipment/pickups.php', ['delete' => $pickup->id, 'sesskey' => sesskey()]);

    $actions = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
    $actions .= $OUTPUT->action_icon(
        $deleteurl,
        new pix_icon('t/delete', get_string('delete')),
        new confirm_action(get_string('confirmdeletepickup', 'local_equipment'))
    );

    $partnership = $DB->get_record('local_equipment_partnership', ['id' => $pickup->partnershipid]);
    $partnershipinfo = $partnership->name;
    // . ', ' . $partnership->city_physical . ', ' . $partnership->state_physical;

    $row[] = userdate($pickup->pickupdate, get_string('strftimedate', 'local_equipment'));
    $row[] = userdate($pickup->starttime, get_string('strftimetime'));
    $row[] = userdate($pickup->endtime, get_string('strftimetime'));
    // $row[] = $pickup->status;
    $row[] = get_string('status_' . $pickup->status, 'local_equipment');
    $row[] = $partnership ? $partnershipinfo : '';
    $row[] = local_equipment_get_coordinator_info($pickup->flccoordinatorid);
    $row[] = local_equipment_get_coordinator_info($pickup->partnershipcoordinatorid);

    // $row[] = $pickup->partnershipcoordinatorname;
    // $row[] = $pickup->partnershipcoordinatorphone;
    $row[] = $actions;

    $table->add_data($row);
}

$table->finish_output();

echo $OUTPUT->footer();
