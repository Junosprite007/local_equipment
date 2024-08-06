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

require_once(__DIR__ . '../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once('./lib.php');

/**
 * Manage partnerships page.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Ensure only admins can access this page.
admin_externalpage_setup('local_equipment_partnerships');
require_login();

$context = context_system::instance();
$url = new moodle_url('/local/equipment/partnerships.php');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('partnerships', 'local_equipment'));
$PAGE->set_heading(get_string('partnerships', 'local_equipment'));

require_capability('local/equipment:managepartnerships', $context);

$columns = [
    'name',
    'pickups',
    'instructions_pickup',
    'liaisons',
    'courses',
    'active',
    'address',
    'actions'
];
// Columns of the database that should not be sortable.
$dontsortby = [
    'pickups',
    'liaisons',
    'courses',
    'address',
    'actions'
];

$headers = [];
foreach ($columns as $column) {
    $headers[] = get_string($column, 'local_equipment');
}

// Handle delete action.
$delete = optional_param('delete', 0, PARAM_INT);
if ($delete && confirm_sesskey()) {
    if ($DB->delete_records('local_equipment_partnership', ['id' => $delete])) {
        \core\notification::success(get_string('partnershipdeleted', 'local_equipment'));
    } else {
        \core\notification::error(get_string('errordeletingpartnership', 'local_equipment'));
    }
}

// Output starts here.
echo $OUTPUT->header();

// Add partnership button.
$addurl = new moodle_url('/local/equipment/partnerships/addpartnership.php');
echo $OUTPUT->single_button($addurl, get_string('addpartnership', 'local_equipment'), 'get');

// Set up the table.
$table = new flexible_table('local-equipment-partnerships');

$table->define_columns($columns);
$table->define_headers($headers);

$table->define_baseurl($PAGE->url);
$table->sortable(true, 'name', SORT_ASC);
foreach ($dontsortby as $column) {
    $table->no_sorting($column);
}
$table->collapsible(true);
$table->initialbars(true);
$table->set_attribute('id', 'partnerships');
$table->set_attribute('class', 'admintable generaltable');
$table->column_style(6, 'overflow-x', 'auto');
$table->setup();

$sort = $table->get_sql_sort();
$partnerships = $DB->get_records('local_equipment_partnership', null, $sort);

foreach ($partnerships as $partnership) {
    $row = [];
    $editurl = new moodle_url('/local/equipment/partnerships/editpartnership.php', ['id' => $partnership->id]);
    $deleteurl = new moodle_url(
        '/local/equipment/partnerships.php',
        ['delete' => $partnership->id, 'sesskey' => sesskey()]
    );

    $actions = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
    $actions .= $OUTPUT->action_icon(
        $deleteurl,
        new pix_icon('t/delete', get_string('delete')),
        new confirm_action(get_string('confirmdelete', 'local_equipment'))
    );

    foreach ($columns as $column) {
        switch ($column) {
            case 'pickups':
                $row[] = $partnership->pickupid;
                break;

            case 'instructions_pickup':
                $row[] = $partnership->instructions_pickup;
                break;

            case 'liaisons':
                $row[] = html_writer::tag(
                    'div',
                    local_equipment_get_liaison_info($partnership),
                    ['class' => 'scrollable-content']
                );
                break;

            case 'courses':
                $row[] = html_writer::tag(
                    'div',
                    local_equipment_get_courses($partnership),
                    ['class' => 'scrollable-content']
                );
                break;

            case 'address':
                $row[] = html_writer::tag(
                    'div',
                    local_equipment_get_addresses($partnership),
                    ['class' => 'scrollable-content']
                );
                break;

            case 'actions':
                $row[] = $actions;
                break;

            default:
                $row[] = $partnership->$column;
                break;
        }
    }

    $table->add_data($row);
}

$table->finish_output();

echo $OUTPUT->footer();
