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

require_once(__DIR__ . '../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

/**
 * Manage partnerships page.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// require_once($CFG->libdir . '/tablelib.php');

// class local_equipment_table extends \flexible_table {
//     public function __construct($uniqueid) {
//         parent::__construct($uniqueid);

//         // Define the columns
//         $columns = array('name', 'version', 'enabled', 'order');
//         $headers = array(
//             get_string('name'),
//             get_string('version'),
//             get_string('enabled', 'admin'),
//             get_string('order')
//         );

//         $this->define_columns($columns);
//         $this->define_headers($headers);

//         // Make columns sortable
//         $this->sortable(true, 'name', SORT_ASC);
//         $this->no_sorting('enabled'); // Example of making a column non-sortable

//         $this->setup();
//     }

//     public function build_table() {
//         global $DB;

//         $sort = $this->get_sort_column();
//         $direction = $this->get_sort_order();

//         // Fetch your data here, applying the sort
//         $plugins = $DB->get_records('local_equipment_partnership', null, "$sort $direction");

//         foreach ($plugins as $plugin) {
//             $row = array();
//             $row[] = $plugin->name;
//             $row[] = $plugin->version;
//             $row[] = $plugin->enabled ? '✓' : '✗';
//             $row[] = $plugin->order;

//             $this->add_data($row);
//         }
//     }
// }

// // Usage
// $table = new local_equipment_table('unique_table_id');
// $table->build_table();
// $table->print_html();


// Ensure only admins can access this page.
admin_externalpage_setup('local_equipment_managepartnerships');

$PAGE->set_url(new moodle_url('/local/equipment/managepartnerships.php'));
$PAGE->set_title(get_string('managepartnerships', 'local_equipment'));
$PAGE->set_heading(get_string('managepartnerships', 'local_equipment'));

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
$addurl = new moodle_url('/local/equipment/addpartnership.php');
echo $OUTPUT->single_button($addurl, get_string('addpartnership', 'local_equipment'), 'get');

// Set up the table.
$table = new flexible_table('local-equipment-partnerships');

$table->define_columns([
    'name',
    'pickupid',
    'liaisonid',
    'active',
    'actions'
]);

$table->define_headers([
    get_string('name'),
    get_string('pickupid', 'local_equipment'),
    get_string('liaisonid', 'local_equipment'),
    get_string('active'),
    get_string('actions')
]);

$table->define_baseurl($PAGE->url);
$table->sortable(true, 'name', SORT_ASC);
$table->collapsible(false);
$table->initialbars(true);

$table->set_attribute('id', 'partnerships');
$table->set_attribute('class', 'admintable generaltable');

$table->setup();

// Fetch partnerships.
$sort = $table->get_sql_sort();
$partnerships = $DB->get_records('local_equipment_partnership', null, $sort);

foreach ($partnerships as $partnership) {
    $editurl = new moodle_url('/local/equipment/editpartnership.php', ['id' => $partnership->id]);
    $deleteurl = new moodle_url(
        '/local/equipment/managepartnerships.php',
        ['delete' => $partnership->id, 'sesskey' => sesskey()]
    );

    $actions = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
    $actions .= $OUTPUT->action_icon(
        $deleteurl,
        new pix_icon('t/delete', get_string('delete')),
        new confirm_action(get_string('confirmdelete', 'local_equipment'))
    );

    $row = [
        $partnership->name,
        $partnership->pickupid,
        $partnership->liaisonids,
        $partnership->active ? get_string('yes') : get_string('no'),
        $actions
    ];

    $table->add_data($row);
}

$table->finish_output();

echo $OUTPUT->footer();
