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
 * Equipment exchange selection page.
 *
 * @package     local_equipment
 * @copyright   2025 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/equipment/lib.php');

// Require login and check capabilities
require_login();
require_capability('local/equipment:submitexchange', context_system::instance());

$PAGE->set_url('/local/equipment/virtualcourseconsent/select_exchange.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('selectexchange', 'local_equipment'));
$PAGE->set_heading(get_string('equipmentexchangeselection', 'local_equipment'));
$PAGE->set_pagelayout('standard');

// Initialize the form
$form = new \local_equipment\form\exchange_form();

// Handle form submission
if ($form->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($data = $form->get_data()) {
    try {
        // Save the exchange submission
        $submissionid = local_equipment_save_exchange_submission($data, $USER->id);

        if ($submissionid) {
            \core\notification::add(
                get_string('exchangesubmitted', 'local_equipment'),
                \core\output\notification::NOTIFY_SUCCESS
            );
            redirect(new moodle_url('/'));
        } else {
            \core\notification::add(
                get_string('exchangesubmissionfailed', 'local_equipment'),
                \core\output\notification::NOTIFY_ERROR
            );
        }
    } catch (Exception $e) {
        \core\notification::add(
            get_string('exchangesubmissionfailed', 'local_equipment') . ': ' . $e->getMessage(),
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

// Output the page
echo $OUTPUT->header();

// Display the form
$form->display();

echo $OUTPUT->footer();
