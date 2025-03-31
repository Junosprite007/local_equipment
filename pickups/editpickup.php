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
 * Edit pickup page.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/equipment/classes/form/editpickup_form.php');

global $DB;

$id = required_param('id', PARAM_INT);
require_login();

$context = context_system::instance();
$url = new moodle_url('/local/equipment/pickups/editpickup.php', ['id' => $id]);
$redirecturl = new moodle_url('/local/equipment/pickups.php');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('editpickup', 'local_equipment'));
$PAGE->set_heading(get_string('editpickup', 'local_equipment'));

require_capability('local/equipment:managepickups', $context);

$pickup = $DB->get_record('local_equipment_pickup', ['id' => $id], '*', MUST_EXIST);

// The 'data' key is used to pass the existing data to the form, 'editpickup_form.php',
// which can be accessed with '$this->_customdata['data']'.
$mform = new local_equipment\form\editpickup_form($url, ['id' => $id, 'data' => $pickup]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/equipment/pickups.php'));
} else if ($data = $mform->get_data()) {
    // $pickup->name = $data->name;
    // $pickup->partnershipid = $data->partnershipid;
    // $pickup->pickupdate = $data->pickupdate;
    // $pickup->dropoffdate = $data->dropoffdate;
    // $pickup->status = $data->status;
    // $pickup->timemodified = time();

    // $pickup = new stdClass();
    $pickupdate = $data->pickupdate;
    // Convert start time to timestamp
    $starttimestamp = make_timestamp(
        date('Y', $pickupdate),  // Year from pickup date
        date('m', $pickupdate),  // Month from pickup date
        date('d', $pickupdate),  // Day from pickup date
        $data->starttimehour,        // Hour from form input
        $data->starttimeminute       // Minute from form input
    );

    // Convert end time to timestamp
    $endtimestamp = make_timestamp(
        date('Y', $pickupdate),
        date('m', $pickupdate),
        date('d', $pickupdate),
        $data->endtimehour,
        $data->endtimeminute
    );

    $pickup->pickupdate = $starttimestamp;
    // Combine hours and minutes into a single timestamp
    $pickup->starttime = $starttimestamp;
    $pickup->endtime = $endtimestamp;
    // $pickup->starttime = $data->pickupdate + ($data->starttimehour * 3600) + ($data->starttimeminute * 60);
    // $pickup->endtime = $data->pickupdate + ($data->endtimehour * 3600) + ($data->endtimeminute * 60);




    // echo '<pre>';
    // var_dump($pickup->starttime);
    // $userdate = userdate($pickup->starttime);
    // var_dump(userdate($pickup->starttime));
    // var_dump(make_timestamp($userdate));
    // echo '</pre>';
    // die();
    $pickup->partnershipid = $data->partnershipid;
    $pickup->flccoordinatorid = $data->flccoordinatorid;
    $pickup->partnershipcoordinatorid = $data->partnershipcoordinatorid;
    $pickup->status = $data->status;



    // Pickup address specific fields.
    $pickup->pickup_streetaddress = $data->pickup_streetaddress;
    $pickup->pickup_apartment = $data->pickup_apartment;
    $pickup->pickup_city = $data->pickup_city;
    $pickup->pickup_state = $data->pickup_state;
    $pickup->pickup_country = $data->pickup_country;
    $pickup->pickup_zipcode = $data->pickup_zipcode;
    $pickup->pickup_extrainstructions = $data->pickup_extrainstructions;


    if ($DB->update_record('local_equipment_pickup', $pickup)) {
        redirect(
            new moodle_url('/local/equipment/pickups.php'),
            get_string('pickupdate', 'local_equipment'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/local/equipment/pickups/editpickup.php', ['id' => $id]),
            get_string('errorupdatingpickup', 'local_equipment'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

$mform->set_data($pickup);

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
