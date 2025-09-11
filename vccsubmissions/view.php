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
 * Virtual Course Consent (VCC) submission management page.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once('../lib.php');

use local_equipment\table\vcc_submissions_table;
use local_equipment\form\vcc_filter_form;
use local_equipment\service\vcc_submission_service;

admin_externalpage_setup('local_equipment_vccsubmissions');

$context = \core\context\system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/equipment/vccsubmissions/view.php'));
$PAGE->set_title(get_string('managevccsubmissions', 'local_equipment'));
$PAGE->set_heading(get_string('managevccsubmissions', 'local_equipment'));

require_capability('local/equipment:managevccsubmissions', $context);

// Get services using dependency injection
$clock = \core\di::get(\core\clock::class);
$vcc_service = \core\di::get(vcc_submission_service::class);

// Handle delete action
$delete = optional_param('delete', 0, PARAM_INT);
if ($delete && confirm_sesskey()) {
    $vcc_service->delete_submission($delete);
    \core\notification::success(get_string('vccsubmissiondeleted', 'local_equipment'));
    redirect($PAGE->url);
}

// Handle form submission and filtering
$filter_data = new stdClass();

// Get URL parameters for filters - handle date arrays properly
$filter_data->partnership = optional_param('partnership', 0, PARAM_INT);
$filter_data->search = optional_param('search', '', PARAM_TEXT);

// Handle date selectors (they come as arrays from Moodle forms)
$datestart_array = optional_param_array('datestart', null, PARAM_INT);
if ($datestart_array && is_array($datestart_array)) {
    // Convert Moodle date array to timestamp
    $filter_data->datestart = mktime(0, 0, 0, $datestart_array['month'], $datestart_array['day'], $datestart_array['year']);
} else {
    $filter_data->datestart = optional_param('datestart', 0, PARAM_INT);
}

$dateend_array = optional_param_array('dateend', null, PARAM_INT);
if ($dateend_array && is_array($dateend_array)) {
    // Convert Moodle date array to timestamp (end of day)
    $filter_data->dateend = mktime(23, 59, 59, $dateend_array['month'], $dateend_array['day'], $dateend_array['year']);
} else {
    $filter_data->dateend = optional_param('dateend', 0, PARAM_INT);
}

// Create filter form and set current data
$current_url = new moodle_url($PAGE->url, (array)$filter_data);
$filter_form = new vcc_filter_form($current_url);

// Set form data
$filter_form->set_data($filter_data);

// If no filters set, use defaults
if (!$filter_data->partnership && !$filter_data->datestart && !$filter_data->dateend && !$filter_data->search) {
    $filter_data = $vcc_service->get_default_filters($clock);
}

echo $OUTPUT->header();

// Render filter form using template
$filter_context = [
    'form' => $filter_form->render(),
    'has_filters' => !empty(array_filter((array)$filter_data))
];

echo $OUTPUT->render_from_template('local_equipment/vcc_filters', $filter_context);

// Create and configure table
$table = new vcc_submissions_table('vccsubmissions', $vcc_service);
$table->set_filters($filter_data);
$table->define_baseurl($current_url);

// Display summary
$total_count = $vcc_service->count_submissions($filter_data);
$summary_context = [
    'total_count' => $total_count,
    'filters_applied' => !empty(array_filter((array)$filter_data))
];

echo $OUTPUT->render_from_template('local_equipment/vcc_summary', $summary_context);

// Display table
$table->out(25, true);

echo $OUTPUT->footer();
