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
require_once($CFG->dirroot . '/local/equipment/classes/form/addpartnership_form.php');

admin_externalpage_setup('local_equipment_addpartnership');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/equipment/partnerships/addpartnership.php'));
$PAGE->set_title(get_string('addpartnership', 'local_equipment'));
$PAGE->set_heading(get_string('addpartnership', 'local_equipment'));
$PAGE->requires->js_call_amd('local_equipment/addpartnership_form', 'init');

require_capability('local/equipment:managepartnerships', $context);

$mform = new local_equipment\form\addpartnership_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/equipment/partnerships.php'));
}
$data = $mform->get_data();
if ($data) {
    $numberofpartnerships = $data->partnerships;
    $success = true;

    // Inserts each partnership into the database, since you can add multiple partnerships at once.
    for ($i = 0; $i < $numberofpartnerships; $i++) {
        $partnership = new stdClass();
        // Convert the liaison and course IDs to arrays of integers instead of arrays of strings. Make sure you know what datatype is going into the functions below.
        $liaisonids = local_equipment_convert_array_values_to_int($data->{'liaisons'}[$i]);
        $courseids = local_equipment_convert_array_values_to_int($data->{'courses'}[$i]);

        // Fill in the partnership table fields.
        $partnership->name = $data->{'partnershipname'}[$i];
        $partnership->liaisonids = json_encode($liaisonids);
        $partnership->courseids = json_encode($courseids);
        $partnership->active = $data->{'active'}[$i];

        // Physical address specific fields.
        $partnership->streetaddress_physical = $data->streetaddress_physical[$i];
        $partnership->city_physical = $data->city_physical[$i];
        $partnership->state_physical = $data->state_physical[$i];
        $partnership->country_physical = $data->country_physical[$i];
        $partnership->zipcode_physical = $data->zipcode_physical[$i];

        // Mailing address specific fields.
        $partnership->attention_mailing = $data->attention_mailing[$i];
        $partnership->sameasphysical_mailing = $data->sameasphysical_mailing[$i];
        if ($partnership->sameasphysical_mailing) {
            $partnership->streetaddress_mailing = $partnership->streetaddress_physical;
            $partnership->city_mailing = $partnership->city_physical;
            $partnership->state_mailing = $partnership->state_physical;
            $partnership->country_mailing = $partnership->country_physical;
            $partnership->zipcode_mailing = $partnership->zipcode_physical;
        } else {
            $partnership->streetaddress_mailing = $data->streetaddress_mailing[$i];
            $partnership->city_mailing = $data->city_mailing[$i];
            $partnership->state_mailing = $data->state_mailing[$i];
            $partnership->country_mailing = $data->country_mailing[$i];
            $partnership->zipcode_mailing = $data->zipcode_mailing[$i];
        }

        // Pickup address specific fields.
        $partnership->instructions_pickup = $data->instructions_pickup[$i];
        $partnership->sameasphysical_pickup = $data->sameasphysical_pickup[$i];
        if ($partnership->sameasphysical_pickup) {
            $partnership->streetaddress_pickup = $partnership->streetaddress_physical;
            $partnership->city_pickup = $partnership->city_physical;
            $partnership->state_pickup = $partnership->state_physical;
            $partnership->country_pickup = $partnership->country_physical;
            $partnership->zipcode_pickup = $partnership->zipcode_physical;
        } else {
            $partnership->streetaddress_pickup = $data->streetaddress_pickup[$i];
            $partnership->city_pickup = $data->city_pickup[$i];
            $partnership->state_pickup = $data->state_pickup[$i];
            $partnership->country_pickup = $data->country_pickup[$i];
            $partnership->zipcode_pickup = $data->zipcode_pickup[$i];
        }

        // Billing address specific fields.
        $partnership->attention_billing = $data->attention_billing[$i];
        $partnership->sameasphysical_billing = $data->sameasphysical_billing[$i];
        if ($partnership->sameasphysical_billing) {
            $partnership->streetaddress_billing = $partnership->streetaddress_physical;
            $partnership->city_billing = $partnership->city_physical;
            $partnership->state_billing = $partnership->state_physical;
            $partnership->country_billing = $partnership->country_physical;
            $partnership->zipcode_billing = $partnership->zipcode_physical;
        } else {
            $partnership->streetaddress_billing = $data->streetaddress_billing[$i];
            $partnership->city_billing = $data->city_billing[$i];
            $partnership->state_billing = $data->state_billing[$i];
            $partnership->country_billing = $data->country_billing[$i];
            $partnership->zipcode_billing = $data->zipcode_billing[$i];
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
