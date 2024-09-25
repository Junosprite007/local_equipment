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
 * Virtual course consent (vcc) submission management page.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once('./lib.php');

admin_externalpage_setup('local_equipment_vccsubmissions');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/equipment/vccsubmissions.php'));
$PAGE->set_title(get_string('managevccsubmissions', 'local_equipment'));
$PAGE->set_heading(get_string('managevccsubmissions', 'local_equipment'));

require_capability('local/equipment:managevccsubmissions', $context);

// Handle delete action.
$delete = optional_param('delete', 0, PARAM_INT);
if ($delete && confirm_sesskey()) {
    $DB->delete_records('local_equipment_vccsubmission', ['id' => $delete]);
    \core\notification::success(get_string('vccsubmissiondeleted', 'local_equipment'));
    redirect($PAGE->url);
}

echo $OUTPUT->header();

// Set up the table.
$table = new flexible_table('local-equipment-vccsubmissions');

$columns = [
    'timecreated',
    'u_firstname',
    'u_lastname',
    'u_email',
    'u_phone2',
    'partnership_name',
    'sub_students',
    'parent_mailing_address',
    'parent_mailing_extrainstructions',
    'pickup',
    'pickupmethod',
    'pickuppersonname',
    'pickuppersonphone',
    'pickuppersondetails',
    'usernotes',
    'adminnotes',
    'actions'
];

// $headers = array_map(function ($column) {
//     return get_string($column, 'local_equipment');
// }, $columns);

$headers = [
    get_string('timecreated'),
    get_string('firstname'),
    get_string('lastname'),
    get_string('email'),
    get_string('phone', 'local_equipment'),
    get_string('partnership', 'local_equipment'),
    get_string('students', 'local_equipment'),
    get_string('mailingaddress', 'local_equipment'),
    get_string('mailing_extrainstructions', 'local_equipment'),
    get_string('pickup', 'local_equipment'),
    get_string('pickupmethod', 'local_equipment'),
    get_string('pickuppersonname', 'local_equipment'),
    get_string('pickuppersonphone', 'local_equipment'),
    get_string('pickuppersondetails', 'local_equipment'),
    get_string('usernotes', 'local_equipment'),
    get_string('adminnotes', 'local_equipment'),
    get_string('actions', 'local_equipment')
];

$columns_nosort = [
    'parent_mailing_address',
    'parent_mailing_extrainstructions',
    'pickup',
    'students',
    'actions'
];

$table->define_columns($columns);
$table->define_headers($headers);

$nowrap_header = 'local-equipment-nowrap-header';
$nowrap_cell = 'local-equipment-nowrap-cell';

foreach ($columns as $column) {
    $table->column_class($column, $nowrap_header);
}

$table->column_class('timecreated', $nowrap_cell);
$table->column_class('partnership_name', $nowrap_cell);
$table->column_class('parent_mailing_address', $nowrap_cell);
$table->column_class('students', $nowrap_cell);
$table->column_class('pickup', $nowrap_cell);

$table->define_baseurl($PAGE->url);
$table->sortable(true, 'timecreated', SORT_DESC);
foreach ($columns_nosort as $column) {
    $table->no_sorting($column);
}
$table->collapsible(true);
$table->initialbars(true);
$table->set_attribute('id', 'vccsubmissions');
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

$select =
"
        vccsubmission.id,
        vccsubmission.userid,
        vccsubmission.partnershipid,
        vccsubmission.pickupid,
        vccsubmission.studentids,
        vccsubmission.agreementids,
        vccsubmission.confirmationid,
        vccsubmission.confirmationexpired,
        vccsubmission.email,
        vccsubmission.email_confirmed,
        vccsubmission.firstname,
        vccsubmission.lastname,
        vccsubmission.phone,
        vccsubmission.phone_confirmed,
        vccsubmission.partnership_name,
        vccsubmission.mailing_extrainput,
        vccsubmission.mailing_streetaddress,
        vccsubmission.mailing_apartment,
        vccsubmission.mailing_city,
        vccsubmission.mailing_state,
        vccsubmission.mailing_country,
        vccsubmission.mailing_zipcode,
        vccsubmission.mailing_extrainstructions,
        vccsubmission.billing_extrainput,
        vccsubmission.billing_sameasmailing,
        vccsubmission.billing_streetaddress,
        vccsubmission.billing_apartment,
        vccsubmission.billing_city,
        vccsubmission.billing_state,
        vccsubmission.billing_country,
        vccsubmission.billing_zipcode,
        vccsubmission.billing_extrainstructions,
        vccsubmission.pickup_locationtime,
        vccsubmission.electronicsignature,
        vccsubmission.pickupmethod,
        vccsubmission.pickuppersonname,
        vccsubmission.pickuppersonphone,
        vccsubmission.pickuppersondetails,
        vccsubmission.usernotes,
        vccsubmission.adminnotes,
        vccsubmission.timecreated,
        vccsubmission.timemodified,

        u.id AS u_id,
        u.firstname AS u_firstname,
        u.lastname AS u_lastname,
        u.email AS u_email,
        u.phone1 AS u_phone1,
        u.phone2 AS u_phone2,

        partnership.name AS p_name,
        partnership.pickup_extrainstructions,
        partnership.pickup_apartment,
        partnership.pickup_streetaddress,
        partnership.pickup_city,
        partnership.pickup_state,
        partnership.pickup_zipcode,

        pickup.starttime AS pickup_starttime,
        pickup.endtime AS pickup_endtime
";

$from =
"
        {local_equipment_vccsubmission} vccsubmission
        LEFT JOIN {user} u ON vccsubmission.userid = u.id
        LEFT JOIN {local_equipment_partnership} partnership ON vccsubmission.partnershipid = partnership.id
        LEFT JOIN {local_equipment_pickup} pickup ON vccsubmission.pickupid = pickup.id
";
$where = "1=1";
$params = [];

if ($table->get_sql_sort()) {
    $sort = $table->get_sql_sort();
} else {
    $sort = 'sub_timecreated DESC';
}
$submissions = $DB->get_records_sql("SELECT $select FROM $from WHERE $where ORDER BY $sort", $params);

$select =
"
        user.id,
        user.userid,
        user.partnershipid,
        user.studentids,
        user.vccsubmissionids,
        user.phoneverificationids,
        user.mailing_extrainput,
        user.mailing_streetaddress,
        user.mailing_apartment,
        user.mailing_city,
        user.mailing_state,
        user.mailing_country,
        user.mailing_zipcode,
        user.mailing_extrainstructions,
        user.billing_extrainput,
        user.billing_sameasmailing,
        user.billing_streetaddress,
        user.billing_apartment,
        user.billing_city,
        user.billing_state,
        user.billing_country,
        user.billing_zipcode,
        user.billing_extrainstructions,
        user.timecreated,
        user.timemodified
";

$from = "{local_equipment_user} user";
$local_equipment_user = $DB->get_records_sql("SELECT $select FROM $from WHERE $where");
// This is the first pass where we merge records of parents who have multiple children and did not put that all on one form.
$formattedpickuplocation = get_string('contactusforpickup', 'local_equipment');

foreach ($submissions as $submission) {

    $submission->parent_mailing_address = '';
    $submission->parent_mailing_extrainstructions = '';

    $break = false;
    foreach ($local_equipment_user as $parentuser) {

        // echo '<br />';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump($submission);
        // echo '</pre>';
        // echo '<br />';
        // echo '<br />';
        // echo '<pre>';
        // var_dump($parentuser);
        // echo '</pre>';
        // die();
        if ($parentuser->userid == $submission->userid) {
            if ($parentuser->mailing_apartment) {
                $submission->parent_mailing_address = $parentuser->mailing_streetaddress . ', ' . get_string('apt', 'local_equipment') . ' ' . $parentuser->mailing_apartment . ', ' . $parentuser->mailing_city . ', ' . $parentuser->mailing_state . ' ' . $parentuser->mailing_zipcode;
            } else {
                $submission->parent_mailing_address = "$parentuser->mailing_streetaddress, $parentuser->mailing_city, $parentuser->mailing_state $parentuser->mailing_zipcode";
            }



            // userdate($submission->timecreated, get_string('strftime24date_mdy', 'local_equipment'));
            // $submission->firstname ?? $submission->u_firstname;
            // $submission->lastname ?? $submission->u_lastname;
            // $submission->email ?? $submission->u_email;
            // $submission->phone ?? $submission->u_phone2 ?? $submission->u_phone1;
            // $submission->partnership_name ?? $submission->p_name;
            // local_equipment_get_vcc_students($submission);
            // $submission->parent_mailing_address;
            // $submission->parent_mailing_extrainstructions;
            // $formattedpickuplocation;
            // $submission->pickupmethod;
            // $submission->pickuppersonname;
            // $submission->pickuppersonphone;
            // $submission->pickuppersondetails;
            // $submission->usernotes;
            // $submission->adminnotes;

            // if ($submission->firstname == 0) {
            //     $submission->firstname = $submission->u_firstname;
            // }
            // if ($submission->lastname == 0) {
            //     $submission->lastname = $submission->u_lastname;
            // }
            // if ($submission->email == 0) {
            //     $submission->email = $submission->u_email;
            // }
            // if ($submission->phone == 0) {
            //     if ($submission->u_phone2) {
            //         $submission->phone = $submission->u_phone2;
            //     } else {
            //         $submission->phone = $submission->u_phone1;
            //     }
            // }
            // if ($submission->partnership_name == 0) {
            //     $submission->partnership_name = $submission->p_name;
            // }


            $submission->parent_mailing_extrainstructions = $parentuser->mailing_extrainstructions;
            $break = true;
        }
        if ($break) {
            break;
        }
    }

    // $pickup_extrainstructions = $submission->pickup_extrainstructions ?? '';

    $datetime = userdate($submission->pickup_starttime, get_string('strftimedate', 'langconfig')) . ' ' .
        userdate($submission->pickup_starttime, get_string('strftimetime', 'langconfig')) . ' - ' .
        userdate($submission->pickup_endtime, get_string('strftimetime', 'langconfig'));

    $pickup_pattern = '/#(.*?)#/' ?? '';
    $pickup_name = $submission->pickup_city;

    if (!empty($submission->pickup_extrainstructions) && preg_match($pickup_pattern, $submission->pickup_extrainstructions, $matches)) {
        $pickup_name = $submission->locationname = $matches[1];
        $submission->pickup_extrainstructions = trim(preg_replace($pickup_pattern, '', $submission->pickup_extrainstructions, 1));
    }

    // if (
    //     preg_match($pickup_pattern, $submission->pickup_extrainstructions, $matches)
    // ) {
    //     $pickup_name = $submission->locationname = $matches[1];
    //     $submission->pickup_extrainstructions = trim(preg_replace($pickup_pattern, '', $submission->pickup_extrainstructions, 1));
    // }
    if ($submission->pickup_streetaddress) {
        $formattedpickuplocation = "$pickup_name — $datetime — $submission->pickup_streetaddress, $submission->pickup_city, $submission->pickup_state $submission->pickup_zipcode";
    }


    $submission->pickup_starttime = $submission->pickup_starttime ? userdate($submission->pickup_starttime) : get_string('contactusforpickup', 'local_equipment');
    $minwidth_cell = 'local-equipment-minwidth-cell';
    $actions = '';
    $viewurl = new moodle_url('/local/equipment/vccsubmissionview.php', ['id' => $submission->id]);
    $editurl = new moodle_url('/local/equipment/vccsubmissionform.php', ['id' => $submission->id]);
    $deleteurl = new moodle_url($PAGE->url, ['delete' => $submission->id, 'sesskey' => sesskey()]);

    $submission->firstname = null;
    $row = [];
    $row[] = userdate($submission->timecreated, get_string('strftime24date_mdy', 'local_equipment'));
    $row[] = $submission->firstname != 0 ? $submission->firstname : $submission->u_firstname;
    $row[] = $submission->lastname != 0 ? $submission->lastname : $submission->u_lastname;
    $row[] = $submission->email != 0 ? $submission->email : $submission->u_email;
    $row[] = $submission->phone != 0 ? $submission->phone : ($submission->u_phone2 != '' ? $submission->u_phone2 : $submission->u_phone1);
    $row[] = $submission->partnership_name != 0 ? $submission->partnership_name : $submission->p_name;
    $row[] = local_equipment_get_vcc_students($submission);
    $row[] = $submission->parent_mailing_address;
    $row[] = $submission->parent_mailing_extrainstructions;
    $row[] = $formattedpickuplocation;
    $row[] = $submission->pickupmethod;
    $row[] = $submission->pickuppersonname;
    $row[] = $submission->pickuppersonphone;
    $row[] = $submission->pickuppersondetails;
    $row[] = $submission->usernotes;
    $row[] = $submission->adminnotes;
    $row[] = $actions;

    $table->add_data($row);
}

$table->finish_output();

echo $OUTPUT->footer();
