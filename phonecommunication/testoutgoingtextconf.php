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
require_once($CFG->libdir . '/adminlib.php');

global $SITE, $USER;

// This is an admin page.
admin_externalpage_setup('local_equipment_testoutgoingtextconf');

$headingtitle = get_string('testoutgoingtextconf', 'local_equipment');
$homeurl = new moodle_url('/admin/category.php', array('category' => 'local_equipment_phone'));
$returnurl = new moodle_url('/admin/testoutgoingtextconf.php');
$redirecturl = new moodle_url('/local/equipment/phonecommunication/verifytestotp.php');
$link = html_writer::link($redirecturl, get_string('verifyotp', 'local_equipment'));
$msg = '';

// This form is located at local/equipment/classes/form/testoutgoingtextconf_form.php.
$form = new local_equipment\form\testoutgoingtextconf_form(null, ['returnurl' => $returnurl]);
if ($form->is_cancelled()) {
    redirect($homeurl);
}

$data = $form->get_data();
if ($data) {
    $textuser = new stdClass();
    $textuser->tonumber = $data->tonumber;
    $textuser->notes = [
        'shortname' => $SITE->shortname,
        'provider' => $data->provider
    ];


    $responseobject = local_equipment_send_secure_otp($textuser->notes['provider'], $textuser->tonumber, 600, $data->isatest);
    // echo '<br />';
    // echo '<br />';
    // echo '<br />';
    // echo '<pre>';
    // var_dump($responseobject);
    // echo '</pre>';
    // die();

    // We're eventually going to need to handle Moodle debugging options. Check out 'testoutgoingmailconf.php' for an example.

    if ($responseobject->success) {
        $msgparams = new stdClass();
        $msgparams->tonumber = $textuser->tonumber;
        $msgparams->link = $link;
        $msg = get_string('senttextsuccess', 'local_equipment', $msgparams);
        $notificationtype = 'notifysuccess';
        // redirect($redirecturl);
    } else {
        $notificationtype = 'notifyproblem';
        $msg = get_string('senttextfailure', 'local_equipment', $responseobject->errormessage);
    }
}

// Display the page.
echo $OUTPUT->header();
echo $OUTPUT->heading($headingtitle);

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
