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

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/equipment/classes/form/editpartnership_form.php');

$id = required_param('id', PARAM_INT); // Partnership ID
$context = context_system::instance();
$url = new moodle_url('/local/equipment/partnerships/editpartnership.php', array('id' => $id));
$redirecturl = new moodle_url('/local/equipment/partnerships.php');

// Set up the page.
require_login();
$PAGE->set_context($context);
$PAGE->set_url($url, array('id' => $id));
$PAGE->set_title(get_string('editpartnership', 'local_equipment'));
$PAGE->set_heading(get_string('editpartnership', 'local_equipment'));

// Check capabilities.
require_capability('local/equipment:managepartnerships', $context);


// Include the JavaScript module

// Fetch existing partnership data.
$partnership = $DB->get_record('local_equipment_partnership', array('id' => $id), '*', MUST_EXIST);

// Initialize the form.
$mform = new local_equipment\form\editpartnership_form($url, array('id' => $id, 'data' => $partnership));

if ($mform->is_cancelled()) {
    redirect($redirecturl);
} else if ($data = $mform->get_data()) {
    // Update the partnership in the database.
    $partnership->name = $data->name;
    // Add other fields as necessary.

    $DB->update_record('local_equipment_partnership', $partnership);

    // Redirect to the partnerships page.
    redirect($redirecturl, get_string('partnershipupdated', 'local_equipment'));
}

// Output everything.
echo $OUTPUT->header();
// echo $OUTPUT->heading(get_string('editpartnership', 'local_equipment'));
$mform->display();
echo $OUTPUT->footer();
