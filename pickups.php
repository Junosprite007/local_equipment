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

// Ensure only admins can access this page.
admin_externalpage_setup('local_equipment_pickups');
require_login();

$context = context_system::instance();
$url = new moodle_url('/local/equipment/pickups.php');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('pickups', 'local_equipment'));
$PAGE->set_heading(get_string('pickups', 'local_equipment'));

require_capability('local/equipment:managepickups', $context);

$columns = [
    'name',
    'partnership',
    'pickupdate',
    'dropoffdate',
    'status',
    'actions',
];

$headers = [];
foreach ($columns as $column) {
    $headers[] = get_string($column, 'local_equipment');
}

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
$addurl = new moodle_url('/local/equipment/pickups/addpickup.php');
echo $OUTPUT->single_button($addurl, get_string('addpickup', 'local_equipment'), 'get');

// Set up the table.
$table = new flexible_table('local-equipment-pickups');

$table->define_columns($columns);
$table->define_headers($headers);

$table->define_baseurl($PAGE->url);
$table->sortable(true, 'pickupdate', SORT_DESC);
$table->no_sorting('actions');
$table->collapsible(true);
$table->initialbars(true);
$table->set_attribute('id', 'pickups');
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

$sort = $table->get_sql_sort();
$pickups = $DB->get_records('local_equipment_pickup', null, $sort);

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

    $row[] = $pickup->name;
    $row[] = $partnership ? $partnership->name : '';
    $row[] = userdate($pickup->pickupdate);
    $row[] = userdate($pickup->dropoffdate);
    $row[] = get_string('status_' . $pickup->status, 'local_equipment');
    $row[] = $actions;

    $table->add_data($row);
}

$table->finish_output();

echo $OUTPUT->footer();
