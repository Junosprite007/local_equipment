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
require_once($CFG->dirroot . '/local/equipment/classes/form/addpickups_form.php');

admin_externalpage_setup('local_equipment_addpickups');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/equipment/pickups/addpickups.php'));
$PAGE->set_title(get_string('addpickups', 'local_equipment'));
$PAGE->set_heading(get_string('addpickups', 'local_equipment'));
$PAGE->requires->js_call_amd('local_equipment/addpickups_form', 'init');

require_capability('local/equipment:managepickups', $context);

$mform = new local_equipment\form\addpickups_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/equipment/pickups.php'));
} else if ($data = $mform->get_data()) {
    $numberofpickups = $data->pickups;
    $success = true;
    // echo '<pre>';
    // var_dump($data);
    // echo '</pre>';
    // die();

    for ($i = 0; $i < $numberofpickups; $i++) {
        $pickup = new stdClass();
        $pickup->pickupdate = $data->pickupdate[$i];

        // Combine hours and minutes into a single timestamp
        $pickup->starttime = $data->pickupdate[$i] + ($data->starttimehour[$i] * 3600) + ($data->starttimeminute[$i] * 60);
        $pickup->endtime = $data->pickupdate[$i] + ($data->endtimehour[$i] * 3600) + ($data->endtimeminute[$i] * 60);

        // $pickup->starttime = $data->starttime[$i];
        // $pickup->endtime = $data->endtime[$i];
        $pickup->partnershipid = $data->partnershipid[$i];
        $pickup->flccoordinatorid = $data->flccoordinatorid[$i];
        $pickup->partnershipcoordinatorname = $data->partnershipcoordinatorname[$i];
        $pickup->partnershipcoordinatorphone = $data->partnershipcoordinatorphone[$i];
        $pickup->status = $data->status[$i];

        $pickup->id = $DB->insert_record('local_equipment_pickup', $pickup);

        if (!$pickup->id) {
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
            new moodle_url('/local/equipment/pickups/addpickups.php'),
            get_string('erroraddingpickups', 'local_equipment'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
