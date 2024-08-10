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
 * Add multiple pickups page.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/equipment/classes/form/addpickup_form.php');

admin_externalpage_setup('local_equipment_addpickup');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/equipment/pickups/addpickup.php'));
$PAGE->set_title(get_string('addpickups', 'local_equipment'));
$PAGE->set_heading(get_string('addpickups', 'local_equipment'));

require_capability('local/equipment:managepickups', $context);

$mform = new local_equipment\form\addpickup_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/equipment/pickups.php'));
} else if ($data = $mform->get_data()) {
    $numberofpickups = $data->pickups;
    $success = true;

    for ($i = 0; $i < $numberofpickups; $i++) {
        $pickup = new stdClass();
        $pickup->name = $data->name[$i];
        $pickup->partnershipid = $data->partnershipid[$i];
        $pickup->pickupdate = $data->pickupdate[$i];
        $pickup->dropoffdate = $data->dropoffdate[$i];
        $pickup->status = $data->status[$i];
        $pickup->timecreated = time();
        $pickup->timemodified = time();

        if (!$DB->insert_record('local_equipment_pickup', $pickup)) {
            $success = false;
            break;
        }
    }

    if ($success) {
        redirect(
            new moodle_url('/local/equipment/pickups.php'),
            get_string('pickupsadded', 'local_equipment'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/local/equipment/pickups/addpickup.php'),
            get_string('erroraddingpickups', 'local_equipment'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
