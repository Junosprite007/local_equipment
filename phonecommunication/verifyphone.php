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
 * Test outgoing texting configuration.
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
    $msgparams = ['form' => get_string('phoneverification', 'local_equipment'), 'site' => $SITE->shortname];
    redirect(new moodle_url('/login/index.php'), get_string('mustlogintoyourownaccount', 'local_equipment', $msgparams), null, \core\output\notification::NOTIFY_ERROR);
}

$context = context_system::instance();
$url = new moodle_url('/local/equipment/phonecommunication/verifyphone.php');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('phoneverification', 'local_equipment'));
$PAGE->set_heading(get_string('phoneverification', 'local_equipment'));

$homeurl = new moodle_url('/');
$returnurl = new moodle_url('/local/equipment/phonecommunication/verifyphone.php');
$redirecturl = new moodle_url('/local/equipment/phonecommunication/verifyotp.php');
$link = html_writer::link($redirecturl, get_string('verifyotp', 'local_equipment'));
$msg = '';

// This form is located at local/equipment/classes/form/verifyphone_form.php.
$form = new local_equipment\form\verifyphone_form(null, ['returnurl' => $returnurl]);
if ($form->is_cancelled()) {
    redirect($homeurl);
}

$data = $form->get_data();
if ($data) {
    $textuser = new stdClass();
    $phoneobj = local_equipment_parse_phone_number($data->tonumber);
    $textuser->tonumber = $phoneobj->phone;

    $provider = get_config('local_equipment', 'otpgateway');
    if (!$provider) {
        $msg = get_string('enduser_nosmsgatewayselected', 'local_equipment');
        $notificationtype = \core\output\notification::NOTIFY_ERROR;
        redirect($returnurl, $msg, null, $notificationtype);
    }

    $textuser->notes = [
        'shortname' => $SITE->shortname
    ];

    $responseobject = local_equipment_send_secure_otp($provider, $textuser->tonumber);

    // We're eventually going to need to handle Moodle debugging options. Check out 'testoutgoingmailconf.php' for an example.

    if ($responseobject->success) {
        $msgparams = new stdClass();
        $msgparams->tonumber = $textuser->tonumber;
        $msgparams->link = $link;
        $msg = get_string('senttextsuccess', 'local_equipment', $msgparams);
        $notificationtype = 'notifysuccess';
        redirect($redirecturl);
    } else {
        $notificationtype = 'notifyproblem';
        $msg = get_string('senttextfailure', 'local_equipment', $responseobject->errormessage);
    }
}

// Display the page.
echo $OUTPUT->header();

if ($msg) {
    // // Show result.
    echo $OUTPUT->notification($msg, $notificationtype);
}
// Displaying notextever warning.
if (!empty($CFG->notextever)) {
    $msg = get_string('notexteverwarning', 'local_equipment');
    echo $OUTPUT->notification($msg, \core\output\notification::NOTIFY_ERROR);
}
$form->display();
echo $OUTPUT->footer();
