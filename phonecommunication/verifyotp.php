<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Verify OTP code.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/formslib.php');

require_login();

global $SITE, $USER;
// Check if the user is a guest and redirect or display an error message
if (isguestuser()) {
    $msgparams = ['form' => get_string('verifyotp', 'local_equipment'), 'site' => $SITE->shortname];
    redirect(new moodle_url('/login/index.php'), get_string('mustlogintoyourownaccount', 'local_equipment', $msgparams), null, \core\output\notification::NOTIFY_ERROR);
}
$context = context_system::instance();
$url = new moodle_url('/local/equipment/phonecommunication/verifyotp.php');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('verifyotp', 'local_equipment'));
$PAGE->set_heading(get_string('verifyotp', 'local_equipment'));

$headingtitle = get_string('verifyotp', 'local_equipment');
$homeurl = new moodle_url('/');

// This form is located at local/equipment/classes/form/verifyotp_form.php.
$form = new local_equipment\form\verifyotp_form(null, ['returnurl' => $url]);
if ($form->is_cancelled()) {
    redirect($homeurl);
}

// echo $OUTPUT->heading($headingtitle);



$data = $form->get_data();
if ($data) {
    $responseobject = local_equipment_verify_otp($data->otp);
    // We're eventually going to need to handle Moodle debugging options. Check out 'testoutgoingmailconf.php' for an example.

    if ($responseobject->success) {
        $msgparams = new stdClass();

        if (isset($responseobject->tophonenumber)) {
            $msgparams->tophonenumber = local_equipment_format_phone_number($responseobject->tophonenumber);
        } else {
            $msgparams->tophonenumber = '';
        }
        if (isset($responseobject->successmessage)) {
            $msg = $responseobject->successmessage;
        } else {
            $msg = get_string('codeconfirmed', 'local_equipment', $msgparams);
            redirect($homeurl, $msg, null, \core\output\notification::NOTIFY_SUCCESS);
        }
        $notificationtype = 'notifysuccess';
    } else {
        $notificationtype = 'notifyproblem';
        $msg = get_string('otperror', 'local_equipment', $responseobject->errormessage);
    }

    // Displaying notextever warning.
    if (!empty($CFG->notextever)) {
        $msg = get_string('notexteverwarning', 'local_equipment');
        echo $OUTPUT->notification($msg, \core\output\notification::NOTIFY_ERROR);
    }

    // // Show result.
    echo $OUTPUT->notification($msg, $notificationtype);
}
echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
