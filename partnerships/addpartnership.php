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
 * Add partnerships page.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Ensure only admins can access this page.

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/equipment/classes/form/addpartnership_form.php');

admin_externalpage_setup('local_equipment_addpartnership');

$PAGE->set_url(new moodle_url('/local/equipment/partnerships/addpartnership.php'));
$PAGE->set_title(get_string('addpartnership', 'local_equipment'));
$PAGE->set_heading(get_string('addpartnership', 'local_equipment'));

$mform = new local_equipment\form\addpartnership_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/equipment/partnerships/managepartnerships.php'));
} else if ($data = $mform->get_data()) {
    $partnerships = $data->partnerships;
    $success = true;

    foreach ($partnerships as $partnership) {
        $record = new stdClass();
        $record->pickupid = $partnership['pickupid'];
        $record->liaisonid = $partnership['liaisonid'];
        $record->active = $partnership['active'];
        $record->name = $partnership['name'];
        $record->streetaddress_mailing = $partnership['streetaddress_mailing'];
        $record->city_mailing = $partnership['city_mailing'];
        $record->state_mailing = $partnership['state_mailing'];
        $record->country_mailing = $partnership['country_mailing'];
        $record->zipcode_mailing = $partnership['zipcode_mailing'];
        $record->streetaddress_pickup = $partnership['streetaddress_pickup'];
        $record->city_pickup = $partnership['city_pickup'];
        $record->state_pickup = $partnership['state_pickup'];
        $record->country_pickup = $partnership['country_pickup'];
        $record->zipcode_pickup = $partnership['zipcode_pickup'];
        $record->name_billing = $partnership['name_billing'];
        $record->streetaddress_billing = $partnership['streetaddress_billing'];
        $record->city_billing = $partnership['city_billing'];
        $record->state_billing = $partnership['state_billing'];
        $record->country_billing = $partnership['country_billing'];
        $record->zipcode_billing = $partnership['zipcode_billing'];

        if (!$DB->insert_record('local_equipment_partnership', $record)) {
            $success = false;
            break;
        }
    }

    if ($success) {
        redirect(
            new moodle_url('/local/equipment/partnerships/managepartnerships.php'),
            get_string('partnershipsadded', 'local_equipment'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/local/equipment/partnerships/addpartnership.php'),
            get_string('erroraddingpartnerships', 'local_equipment'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
