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

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/equipment/classes/form/addpartnerships_form.php');

admin_externalpage_setup('local_equipment_addpartnerships');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/equipment/partnerships/addpartnerships.php'));
$PAGE->set_title(get_string('addpartnerships', 'local_equipment'));
$PAGE->set_heading(get_string('addpartnerships', 'local_equipment'));
$PAGE->requires->js_call_amd('local_equipment/addpartnerships_form', 'init', ['partnership']);
$PAGE->requires->js_call_amd('local_equipment/formhandling', 'setupFieldsetNameUpdates', ['partnership', 'header']);
$PAGE->requires->js_call_amd('local_equipment/addpartnerships_form', 'displayPartnershipCourseListing');

require_capability('local/equipment:managepartnerships', $context);

$mform = new local_equipment\form\addpartnerships_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/equipment/partnerships.php'));
} else if ($data = $mform->get_data()) {
    $partnershiprepeats = $data->partnerships;
    $success = true;

    // Inserts each partnership into the database, since you can add multiple partnerships at once.
    for ($i = 0; $i < $partnershiprepeats; $i++) {
        $partnership = new stdClass();
        // Convert the liaison and course IDs to arrays of integers instead of arrays of strings. Make sure you know what datatype is going into the functions below.
        $liaisonids = local_equipment_convert_array_values_to_int($data->{'liaisons'}[$i]);
        // $courseids = local_equipment_convert_array_values_to_int($data->{'courses'}[$i]);

        // Fill in the partnership table fields.
        $partnership->name = $data->{'partnershipname'}[$i];
        $partnership->liaisonids = json_encode($liaisonids);
        // $partnership->courseids = json_encode($courseids); // Needs to be removed from DB.
        $partnership->listingid = $data->partnershipcourselist[$i]; // Needs to be added to DB.
        $partnership->active = $data->{'active'}[$i];

        // echo '<pre>';
        // var_dump($data->partnershipcourselist);
        // echo '</pre>';
        // die();

        // Physical address specific fields.
        $partnership->physical_streetaddress = $data->physical_streetaddress[$i];
        $partnership->physical_city = $data->physical_city[$i];
        $partnership->physical_state = $data->physical_state[$i];
        $partnership->physical_country = $data->physical_country[$i];
        $partnership->physical_zipcode = $data->physical_zipcode[$i];

        // Mailing address specific fields.
        $partnership->mailing_extrainput = $data->mailing_extrainput[$i];
        $partnership->mailing_sameasphysical = $data->mailing_sameasphysical[$i] ?? 0;
        if ($partnership->mailing_sameasphysical) {
            $partnership->mailing_streetaddress = $partnership->physical_streetaddress;
            $partnership->mailing_city = $partnership->physical_city;
            $partnership->mailing_state = $partnership->physical_state;
            $partnership->mailing_country = $partnership->physical_country;
            $partnership->mailing_zipcode = $partnership->physical_zipcode;
        } else {
            $partnership->mailing_streetaddress = $data->mailing_streetaddress[$i];
            $partnership->mailing_city = $data->mailing_city[$i];
            $partnership->mailing_state = $data->mailing_state[$i];
            $partnership->mailing_country = $data->mailing_country[$i];
            $partnership->mailing_zipcode = $data->mailing_zipcode[$i];
        }

        // Pickup address specific fields.
        $partnership->pickup_extrainstructions = $data->pickup_extrainstructions[$i];
        $partnership->pickup_sameasphysical = $data->pickup_sameasphysical[$i] ?? 0;
        if ($partnership->pickup_sameasphysical) {
            $partnership->pickup_streetaddress = $partnership->physical_streetaddress;
            $partnership->pickup_city = $partnership->physical_city;
            $partnership->pickup_state = $partnership->physical_state;
            $partnership->pickup_country = $partnership->physical_country;
            $partnership->pickup_zipcode = $partnership->physical_zipcode;
        } else {
            $partnership->pickup_streetaddress = $data->pickup_streetaddress[$i];
            $partnership->pickup_city = $data->pickup_city[$i];
            $partnership->pickup_state = $data->pickup_state[$i];
            $partnership->pickup_country = $data->pickup_country[$i];
            $partnership->pickup_zipcode = $data->pickup_zipcode[$i];
        }

        // Billing address specific fields.
        $partnership->billing_extrainput = $data->billing_extrainput[$i];
        $partnership->billing_sameasphysical = $data->billing_sameasphysical[$i] ?? 0;
        if ($partnership->billing_sameasphysical) {
            $partnership->billing_streetaddress = $partnership->physical_streetaddress;
            $partnership->billing_city = $partnership->physical_city;
            $partnership->billing_state = $partnership->physical_state;
            $partnership->billing_country = $partnership->physical_country;
            $partnership->billing_zipcode = $partnership->physical_zipcode;
        } else {
            $partnership->billing_streetaddress = $data->billing_streetaddress[$i];
            $partnership->billing_city = $data->billing_city[$i];
            $partnership->billing_state = $data->billing_state[$i];
            $partnership->billing_country = $data->billing_country[$i];
            $partnership->billing_zipcode = $data->billing_zipcode[$i];
        }

        $partnership->timecreated = time();

        $partnership->id = $DB->insert_record('local_equipment_partnership', $partnership);

        if (!$partnership->id) {
            $success = false;
            break;
        }

        // Determines the relationship between the partnership and its liaisons by adding records to the partnership-liaison join table.
        if (!empty($liaisonids)) {
            $record = new stdClass();
            foreach ($liaisonids as $liaisonid) {
                $record->partnershipid = $partnership->id;
                $record->liaisonid = $liaisonid;
                $record->timecreated = $partnership->timecreated;
                if (!$DB->insert_record('local_equipment_partnership_liaison', $record, false)) {
                    $success = false;
                    break;
                }
            }
        }

        // Determines the relationship between the partnership and its courses by adding records to the partnership-course join table.
        if (!empty($courseids)) {
            $record = new stdClass();
            foreach ($courseids as $courseid) {
                $record->partnershipid = $partnership->id;
                $record->courseid = $courseid;
                $record->timecreated = $partnership->timecreated;
                if (!$DB->insert_record('local_equipment_partnership_course', $record)) {
                    $success = false;
                    break;
                }
            }
        }
    }

    if ($success) {
        redirect(
            new moodle_url('/local/equipment/partnerships.php'),
            get_string('partnershipsadded', 'local_equipment'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/local/equipment/partnerships/addpartnerships.php'),
            get_string('erroraddingpartnerships', 'local_equipment'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
