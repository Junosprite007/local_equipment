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
 * Manage agreements page.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once('./lib.php');

admin_externalpage_setup('local_equipment_agreements');

$context = context_system::instance();
$url = new moodle_url('/local/equipment/agreements.php');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('manageagreements', 'local_equipment'));
$PAGE->set_heading(get_string('manageagreements', 'local_equipment'));

require_capability('local/equipment:manageagreements', $context);

// Handle delete action
$delete = optional_param('delete', 0, PARAM_INT);
if ($delete && confirm_sesskey()) {
    $DB->delete_records('local_equipment_agreement', ['id' => $delete]);
    \core\notification::success(get_string('agreementdeleted', 'local_equipment'));
    redirect($PAGE->url);
}

echo $OUTPUT->header();

// Add agreement button
$addurl = new moodle_url('/local/equipment/agreements/addagreements.php');
echo $OUTPUT->single_button($addurl, get_string('addagreement', 'local_equipment'), 'get');

// Set up the table
$table = new flexible_table('local-equipment-agreements');
$columns = [
    'title',
    'agreementtype',
    'status',
    'version',
    'activestarttime',
    'activeendtime',
    'actions'
];
$table->define_columns($columns);
$table->define_headers([
    get_string('title', 'local_equipment'),
    get_string('type', 'local_equipment'),
    get_string('status', 'local_equipment'),
    get_string('version', 'local_equipment'),
    get_string('activestarttime', 'local_equipment'),
    get_string('activeendtime', 'local_equipment'),
    get_string('actions', 'local_equipment')
]);
$table->define_baseurl($PAGE->url);
$table->sortable(true, 'title', SORT_ASC);
$table->no_sorting('actions');
$table->collapsible(false);
$table->initialbars(true);
$table->set_attribute('id', 'agreements');
$table->set_attribute('class', 'admintable generaltable flctable');
$table->setup();

// Set up the SQL query.
$currenttime = time();
$fields = "a.id, a.title, a.agreementtype, a.activestarttime, a.activeendtime, a.version,
           CASE WHEN a.activestarttime <= :currenttime1 AND a.activeendtime > :currenttime2 THEN 1 ELSE 0 END AS status";
$from = "{local_equipment_agreement} a";
$join = "LEFT OUTER JOIN {local_equipment_agreement} b ON a.id = b.previousversionid";
$where = "b.id IS NULL";
$params = ['currenttime1' => $currenttime, 'currenttime2' => $currenttime];

$sort = $table->get_sql_sort();
if ($sort) {
    $sort = preg_replace(
        '/\bactive\b/',
        "CASE WHEN a.activestarttime <= :currenttime3 AND a.activeendtime > :currenttime4 THEN 1 ELSE 0 END",
        $sort
    );
    $params['currenttime3'] = $currenttime;
    $params['currenttime4'] = $currenttime;
}

$sql = "SELECT $fields FROM $from $join WHERE $where";
if ($sort) {
    $sql .= " ORDER BY $sort";
}

$agreements = $DB->get_records_sql($sql, $params);

$status = false;

foreach ($agreements as $agreement) {
    $latestagreement = local_equipment_get_latest_agreement_version($agreement->id);
    $status = local_equipment_agreement_get_status($agreement);
    $row = [];
    $row[] = $agreement->title;
    $row[] = get_string('agreementtype_' . $agreement->agreementtype, 'local_equipment');
    $row[] = get_string($status, 'local_equipment');
    $row[] = $agreement->version;
    $row[] = userdate($agreement->activestarttime, get_string('strfdaymonthdateyear', 'local_equipment'));
    $row[] = userdate($agreement->activeendtime, get_string('strfdaymonthdateyear', 'local_equipment'));

    $buttons = [];
    $editurl = new moodle_url('/local/equipment/agreements/editagreement.php', ['id' => $agreement->id]);
    $buttons[] = html_writer::link($editurl, $OUTPUT->pix_icon('t/edit', get_string('edit')));

    $status = local_equipment_agreement_get_status($agreement);
    if ($status === 'notstarted') {
        $deleteurl = new moodle_url($PAGE->url, ['delete' => $agreement->id, 'sesskey' => sesskey()]);
        $buttons[] = html_writer::link(
            $deleteurl,
            $OUTPUT->pix_icon('t/delete', get_string('delete')),
            ['onclick' => 'return confirm("' . get_string('confirmdeletedialog', 'local_equipment') . '");']
        );
    }

    $row[] = implode(' ', $buttons);
    $table->add_data($row);
}

$table->finish_output();

echo $OUTPUT->footer();
