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
 * Edit partnership page.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/equipment/classes/form/editpartnership_form.php');

global $DB;

$id = required_param('id', PARAM_INT); // Partnership ID
require_login();

$context = context_system::instance();
$url = new moodle_url('/local/equipment/partnerships/editpartnership.php', ['id' => $id]);
$redirecturl = new moodle_url('/local/equipment/partnerships.php');

// Set up the page.
$PAGE->set_context($context);
$PAGE->set_url($url, ['id' => $id]);
$PAGE->set_title(get_string('editpartnership', 'local_equipment'));
$PAGE->set_heading(get_string('editpartnership', 'local_equipment'));

require_capability('local/equipment:managepartnerships', $context);

// Fetch existing partnership data.
$partnership = $DB->get_record('local_equipment_partnership', ['id' => $id], '*', MUST_EXIST);

// Initialize the form.
$mform = new local_equipment\form\editpartnership_form($url, ['id' => $id, 'data' => $partnership]);

if ($mform->is_cancelled()) {
    redirect($redirecturl);
} else if ($data = $mform->get_data()) {
    // Update the partnership in the database.
    $partnership = $data;
    $partnership->id = $data->partnershipid;
    $partnership->name = $data->name;
    $partnership->liaisonids = json_encode(local_equipment_convert_array_values_to_int($data->liaisons));
    $partnership->courseids = json_encode(local_equipment_convert_array_values_to_int($data->courses));
    $partnership->active = $data->active;

    // Mailing address specific fields.
    if ($partnership->mailing_sameasphysical) {
        $partnership->mailing_streetaddress = $partnership->physical_streetaddress;
        $partnership->mailing_city = $partnership->physical_city;
        $partnership->mailing_state = $partnership->physical_state;
        $partnership->mailing_country = $partnership->physical_country;
        $partnership->mailing_zipcode = $partnership->physical_zipcode;
    }

    // Pickup address specific fields.
    if ($partnership->pickup_sameasphysical) {
        $partnership->pickup_streetaddress = $partnership->physical_streetaddress;
        $partnership->pickup_city = $partnership->physical_city;
        $partnership->pickup_state = $partnership->physical_state;
        $partnership->pickup_country = $partnership->physical_country;
        $partnership->pickup_zipcode = $partnership->physical_zipcode;
    }

    // Billing address specific fields.
    if ($partnership->billing_sameasphysical) {
        $partnership->billing_streetaddress = $partnership->physical_streetaddress;
        $partnership->billing_city = $partnership->physical_city;
        $partnership->billing_state = $partnership->physical_state;
        $partnership->billing_country = $partnership->physical_country;
        $partnership->billing_zipcode = $partnership->physical_zipcode;
    }

    $DB->update_record('local_equipment_partnership', $partnership);

    // Redirect to the partnerships page.
    redirect($redirecturl, get_string('partnershipupdated', 'local_equipment'));
}

// Output everything.
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
