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
 * Library of functions and constants for the Equipment checkout module.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_xp\di;
use core\plugininfo\local;
use media_videojs\external\get_language;
use core\exception\moodle_exception;
use core_payment\external\get_available_gateways;
use core_sms\gateway;

// use core\user;
// use core_user;
// use html_writer;
// use moodle_url;
// use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Callback function to add CSS to the page.
 *
 * @param \core\hook\output\before_standard_head_html_generation $hook
 */
function local_equipment_before_standard_head_html_generation(\core\hook\output\before_standard_head_html_generation $hook) {
    global $PAGE;

    $PAGE->requires->css('/local/equipment/styles.css');
}

/**
 * Get all the states or provinces from a specified country.
 * Will only work for USA at the moment, until I add javascript.
 *
 * @param string $country The country code for which to get states or provinces.
 * @return array An array of states or provinces.
 */
function local_equipment_get_states($country = 'USA') {
    $states = [];

    switch ($country) {
        case 'USA':
            $states = [
                '' => get_string('selectstate', 'local_equipment'),
                'AL' => get_string('AL', 'local_equipment'),
                'AK' => get_string('AK', 'local_equipment'),
                'AZ' => get_string('AZ', 'local_equipment'),
                'AR' => get_string('AR', 'local_equipment'),
                'CA' => get_string('CA', 'local_equipment'),
                'CO' => get_string('CO', 'local_equipment'),
                'CT' => get_string('CT', 'local_equipment'),
                'DE' => get_string('DE', 'local_equipment'),
                'FL' => get_string('FL', 'local_equipment'),
                'GA' => get_string('GA', 'local_equipment'),
                'HI' => get_string('HI', 'local_equipment'),
                'ID' => get_string('ID', 'local_equipment'),
                'IL' => get_string('IL', 'local_equipment'),
                'IN' => get_string('IN', 'local_equipment'),
                'IA' => get_string('IA', 'local_equipment'),
                'KS' => get_string('KS', 'local_equipment'),
                'KY' => get_string('KY', 'local_equipment'),
                'LA' => get_string('LA', 'local_equipment'),
                'ME' => get_string('ME', 'local_equipment'),
                'MD' => get_string('MD', 'local_equipment'),
                'MA' => get_string('MA', 'local_equipment'),
                'MI' => get_string('MI', 'local_equipment'),
                'MN' => get_string('MN', 'local_equipment'),
                'MS' => get_string('MS', 'local_equipment'),
                'MO' => get_string('MO', 'local_equipment'),
                'MT' => get_string('MT', 'local_equipment'),
                'NE' => get_string('NE', 'local_equipment'),
                'NV' => get_string('NV', 'local_equipment'),
                'NH' => get_string('NH', 'local_equipment'),
                'NJ' => get_string('NJ', 'local_equipment'),
                'NM' => get_string('NM', 'local_equipment'),
                'NY' => get_string('NY', 'local_equipment'),
                'NC' => get_string('NC', 'local_equipment'),
                'ND' => get_string('ND', 'local_equipment'),
                'OH' => get_string('OH', 'local_equipment'),
                'OK' => get_string('OK', 'local_equipment'),
                'OR' => get_string('OR', 'local_equipment'),
                'PA' => get_string('PA', 'local_equipment'),
                'RI' => get_string('RI', 'local_equipment'),
                'SC' => get_string('SC', 'local_equipment'),
                'SD' => get_string('SD', 'local_equipment'),
                'TN' => get_string('TN', 'local_equipment'),
                'TX' => get_string('TX', 'local_equipment'),
                'UT' => get_string('UT', 'local_equipment'),
                'VT' => get_string('VT', 'local_equipment'),
                'VA' => get_string('VA', 'local_equipment'),
                'WA' => get_string('WA', 'local_equipment'),
                'WV' => get_string('WV', 'local_equipment'),
                'WI' => get_string('WI', 'local_equipment'),
                'WY' => get_string('WY', 'local_equipment'),
            ];
            break;
        default:
            break;
    }

    return $states;
}

/**
 * Get all countries. List the countries in the order
 * you want them to appear in the dropdown
 *
 * @return array An array of countries.
 */
function local_equipment_get_countries() {
    $countries = [
        '' => get_string('selectcountry', 'local_equipment'),
        'USA' => get_string('USA', 'local_equipment'),
    ];

    return $countries;
}


/**
 * Sends an SMS message to a phone number.
 *
 * @param string $gatewayid The ID of the SMS gateway to use for sending text messages.
 * @param string $tonumber The phone number to send the SMS message to.
 * @param string $message The message to send in the SMS message.
 * @param string $messagetype The type of message to send. This may change depending on the chosen provider. AWS uses
 * 'Transactional' for OTP and informational messages and 'Promotional' for marketing messages.
 * @return object
 */
function local_equipment_send_sms($gatewayid, $tonumber, $message, $messagetype) {
    global  $DB;
    // Get all SMS gateways

    $responseobject = new stdClass();
    $responseobject->errormessage = '';
    $responseobject->errorobject = new stdClass();
    $responseobject->success = false;

    try {
        $gatewayobj = $DB->get_record('sms_gateways', ['id' => $gatewayid]);
        if (!$gatewayobj) {
            throw new moodle_exception('invalidgatewayid', 'local_equipment', '', null, "\$gatewayid = $gatewayid");
        }
        switch ($gatewayobj->gateway) {
            case 'smsgateway_aws\gateway':
                return local_equipment_handle_aws_gateway($gatewayobj, $tonumber, $message, $messagetype);

                // case 'infobip':
                //     // Just for testing:
                //     // $responseobject->success = true;
                //     // break;

                //     $infobipapikey = get_config('local_equipment', 'infobipapikey');
                //     $infobipapibaseurl = get_config('local_equipment', 'infobipapibaseurl');
                //     $curl = new curl();

                //     // Set headers
                //     $headers = [
                //         'Authorization: App ' . $infobipapikey,
                //         'Content-Type: application/json',
                //         'Accept: application/json'
                //     ];

                //     $curl->setHeader($headers);
                //     $postdata = '{"messages":[{"destinations":[{"to":"' . $tonumber . '"}],"from":"' . $SITE->shortname . '","text":"' . $message . '"}]}';

                //     // Make the request
                //     $responseobject->response = $curl->post('https://' . $infobipapibaseurl . '/sms/2/text/advanced', $postdata);

                //     // Get the HTTP response code
                //     $info = $curl->get_info();
                //     echo '<pre>';
                //     var_dump($responseobject);
                //     echo '</pre>';

                //     if ($info['http_code'] >= 200 && $info['http_code'] < 300) {
                //         // The request was successful
                //         $responseobject->success = true;
                //     } else {
                //         // The request failed
                //         $responseobject->errorobject->httpcode = $info['http_code'];
                //         $responseobject->errorobject->curlcode = $curl->get_errno();
                //         $responseobject->errormessage = get_string('httprequestfailedwithcode', 'local_equipment', $responseobject->errorobject);
                //         throw new moodle_exception('httprequestfailed', 'local_equipment', '', null, $responseobject->errormessage);
                //     }
                //     break;
                // case 'twilio':
                // Just for testing:
                // $responseobject->success = true;
                // break;
                // $twilioaccountsid = get_config('local_equipment', 'twilioaccountsid');
                // $twilioauthtoken = get_config('local_equipment', 'twilioauthtoken');
                // $twilionumber = get_config('local_equipment', 'twilionumber');

                // $curl = new curl();

                // // Set headers
                // $headers = [
                //         'Content-Type: application/x-www-form-urlencoded'
                //     ];

                // $curl->setHeader($headers);

                // // Set post data
                // $postdata = http_build_query([
                //     'To' => $tonumber,
                //     'From' => $twilionumber,
                //     'Body' => $message
                // ]);

                // // Set Twilio API URL
                // $twilioapiurl = 'https://api.twilio.com/2010-04-01/Accounts/' . $twilioaccountsid . '/Messages.json';

                // // Set authentication
                // $curl->setopt(CURLOPT_USERPWD, $twilioaccountsid . ':' . $twilioauthtoken);

                // // Make the request
                // $responseobject->response = $curl->post($twilioapiurl, $postdata);

                // // Get the HTTP response code
                // $info = $curl->get_info();
                // $responseobject->errormessage = '';
                // $responseobject->errorobject = new stdClass();

                // if ($info['http_code'] >= 200 && $info['http_code'] < 300) {
                //     // The request was successful
                //     $responseobject->success = true;
                // } else {
                //     // The request failed
                //     $responseobject->errorobject->httpcode = $info['http_code'];
                //     $responseobject->errorobject->curlcode = $curl->get_errno();
                //     $responseobject->errormessage = get_string('httprequestfailedwithcode', 'local_equipment', $responseobject->errorobject);
                //     $responseobject->success = false;
                //     throw new moodle_exception('httprequestfailed', 'local_equipment', '', null, $responseobject->errormessage);
                // }
                // break;

            default:
                break;
        }
    } catch (Exception $e) {
        // Handle the exception
        $responseobject->errormessage = $e->getMessage();
    }
    // echo '<br />';
    // echo '<br />';
    // echo '<br />';
    // echo '<pre>';
    // var_dump($responseobject);
    // echo '</pre>';
    //             die();
    return $responseobject;
}


/**
 * Get available exchange times for the current user.
 *
 * @param int $userid The user ID to check for
 * @return array Array of available exchange pickup objects
 */
function local_equipment_get_available_exchanges($userid) {
    global $DB;

    // Get the current time using Moodle 5.0 approach
    $clock = \core\di::get(\core\clock::class);
    $now = $clock->now()->getTimestamp();
    $ten_minutes = 600;
    $thirty_days = 2592000;

    // Get confirmed pickups that are available for exchange
    $sql = "SELECT p.*
            FROM {local_equipment_pickup} p
            WHERE p.status = 'confirmed'
            AND p.endtime > ?
            AND p.starttime < ?
            ORDER BY p.starttime ASC";

    $params = [$now + $ten_minutes, $now + $thirty_days];

    return $DB->get_records_sql($sql, $params);
}

/**
 * Check if user has already submitted an exchange form for a specific pickup.
 *
 * @param int $userid The user ID
 * @param int $exchangeid The exchange/pickup ID
 * @return object|false The existing submission record or false if none exists
 */
function local_equipment_get_existing_exchange_submission($userid, $exchangeid) {
    global $DB;

    $clock = \core\di::get(\core\clock::class);
    $timestamp = $clock->now()->getTimestamp();

    $sql = "id = :id AND endtime > :timestamp";
    $params = [
        'id' => $exchangeid,
        'timestamp' => $timestamp
    ];
    $validexchange = $DB->get_record_select('local_equipment_pickup', $sql, $params, '*', IGNORE_MULTIPLE);

    if ($validexchange) {
        // Exchange has already past, so the user needs a new entry in the local_equipment_user_exchange table.
        return $DB->get_record('local_equipment_exchange_submission', [
            'userid' => $userid,
            'exchangeid' => $exchangeid
        ]);
    }
    return false;
}

/**
 * Save or update an exchange submission.
 *
 * @param object $data The form data
 * @param int $userid The user ID
 * @return int The submission ID
 */
function local_equipment_save_exchange_submission($data, $userid) {
    global $DB;

    // Get the current time using Moodle 5.0 approach
    $clock = \core\di::get(\core\clock::class);
    $timestamp = $clock->now()->getTimestamp();

    $transaction = $DB->start_delegated_transaction();

    try {
        $pickupmethod = '';
        $pickupmethods = local_equipment_get_pickup_methods();

        switch ($data->pickup_method) {
            case 'self':
                $pickupmethod = $pickupmethods['self'];
                break;
            case 'other':
                $pickupmethod = $pickupmethods['other'];
                break;
            case 'ship':
                $pickupmethod = $pickupmethods['ship'];
                break;
            case 'purchased':
                $pickupmethod = $pickupmethods['purchased'];
                break;
            default:
                $pickupmethod = '';
                break;
        }
        // Check if submission already exists
        $existing = local_equipment_get_existing_exchange_submission($userid, $data->pickup);
        // echo '<pre>';
        // var_dump($existing);
        // echo '</pre>';
        // die();

        if ($existing) {
            // Update existing submission
            $existing->pickup_method = $pickupmethod;
            $existing->pickup_person_name = isset($data->pickup_person_name) ? $data->pickup_person_name : null;
            $existing->pickup_person_phone = isset($data->pickup_person_phone) ? $data->pickup_person_phone : null;
            $existing->pickup_person_details = isset($data->pickup_person_details) ? $data->pickup_person_details : null;
            $existing->user_notes = isset($data->user_notes) ? $data->user_notes : null;
            $existing->timemodified = $timestamp;

            $DB->update_record('local_equipment_exchange_submission', $existing);
            \core\notification::info(get_string('updatedyourexchangetime', 'local_equipment'));
            $submissionid = $existing->id;
        } else {
            // Create new submission
            $exchangesubmission = new stdClass();
            $exchangesubmission->userid = $userid;
            $exchangesubmission->exchangeid = $data->pickup;
            $exchangesubmission->pickup_method = $pickupmethod;
            $exchangesubmission->pickup_person_name = isset($data->pickup_person_name) ? $data->pickup_person_name : null;
            $exchangesubmission->pickup_person_phone = isset($data->pickup_person_phone) ? $data->pickup_person_phone : null;
            $exchangesubmission->pickup_person_details = isset($data->pickup_person_details) ? $data->pickup_person_details : null;
            $exchangesubmission->user_notes = isset($data->user_notes) ? $data->user_notes : null;
            $exchangesubmission->timecreated = $timestamp;
            $exchangesubmission->timemodified = $timestamp;

            $submissionid = $DB->insert_record('local_equipment_exchange_submission', $exchangesubmission);

            // Create user exchange record for reminder system
            $userexchange = new stdClass();
            $userexchange->userid = $userid;
            $userexchange->exchangeid = $data->pickup;
            $userexchange->reminder_code = '0';
            $userexchange->reminder_method = '0';
            $userexchange->timemodified = $timestamp;
            $userexchange->timecreated = $timestamp;

            $DB->insert_record('local_equipment_user_exchange', $userexchange);
        }

        $transaction->allow_commit();
        return $submissionid;
    } catch (Exception $e) {
        debugging('Exchange submission failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        $transaction->rollback($e);
        throw $e;
    }
}

/**
 * Check if a language string exists in current component.
 *
 * @param string $identifier The string identifier.
 * @return bool True if the string exists, false otherwise.
 */
function local_equipment_lang_string_exists($identifier) {
    $langfile = file_get_contents(__DIR__ . '/lang/en/local_equipment.php');
    if (file_exists($langfile)) {
        $strings = include($langfile);
        return array_key_exists($identifier, $strings);
    }
    return false;
}

/**
 * Get all relevant addresses from a partnerships database record.
 *
 * @param stdClass $dbrecord A database record that contains multiple types of addresses.
 * @return string Each addresses type, plus the actual address, joined by a <br /> tag.
 */
function local_equipment_get_addresses($dbrecord) {
    $address = [];
    $addresstypes = [
        'physical',
        'mailing',
        'pickup',
        'billing',
    ];
    foreach ($addresstypes as $type) {
        if ("{$dbrecord->{"{$type}_streetaddress"}}") {
            $address[] = html_writer::tag('strong', s(get_string($type, 'local_equipment'))) . ": {$dbrecord->{"{$type}_streetaddress"}}, {$dbrecord->{"{$type}_city"}}, {$dbrecord->{"{$type}_state"}} {$dbrecord->{"{$type}_zipcode"}} {$dbrecord->{"{$type}_country"}}";
        }
    }

    return implode('<br />', $address);
}

/**
 * Generate a user selector with autocomplete for the user's liaison.
 *
 * @return array An array with the proper parameters for a user select field with autocomplete.
 */
function local_equipment_auto_complete_users() {
    return [
        'ajax' => 'core_user/form_user_selector',
        'multiple' => true,
        'casesensitive' => false,
        'valuehtmlcallback' => 'local_equipment_user_selector_callback',
    ];
}

/**
 * Generate a user selector with autocomplete for the user's liaison.
 *
 * @return array An array with the proper parameters for a user select field with autocomplete.
 */
function local_equipment_auto_complete_users_single() {
    return [
        'ajax' => 'core_user/form_user_selector',
        'multiple' => false,
        'casesensitive' => false,
        'valuehtmlcallback' => 'local_equipment_user_selector_callback',
    ];
}

/**
 * Custom user selector callback. This is where the selected users' full names (and profile pics if exists) are generated on reload.
 *
 * @param int $id User ID.
 * @return string HTML for user selector.
 */
function local_equipment_user_selector_callback($id) {
    global $OUTPUT;
    if (!$id || $id == 'qfforcemultiselectsubmission') {
        return ''; // Return empty for invalid or placeholder IDs
    }

    try {
        $user = \core_user::get_user($id, '*', MUST_EXIST);
        return $OUTPUT->user_picture($user, ['size' => 24]) . ' ' . fullname($user);
    } catch (dml_missing_record_exception $e) {
        debugging("User with ID $id not found in user_selector_callback", DEBUG_DEVELOPER);
        return ''; // Return empty string if user not found
    }
    // $user = \core_user::get_user($id);
    // This showing the selected user as 'qfforcemultiselectsubmission' for some reason. That's not a user, lol.
    // if (!$user) {
    //     return ''; // Return an empty string or a placeholder if the user does not exist.
    // }
    // return $OUTPUT->user_picture($user, ['size' => 24]) . ' ' . fullname($user);
}

/**
 * Get all liaison names, emails, and contact phones for a given partnership.
 * Liaisons are simply users on the system, so they must of accounts.
 * Emails and phones will be taken from the user's profile, but admins will have the option to add phone numbers for them if they don't do it themselves.
 *
 * @param stdClass $partnership A database record that contains multiple types of addresses.
 * @return string Imploded array of all liaisons with there required information.
 */
function local_equipment_get_liaison_info($partnership) {
    // $liaisons = user_get_users_by_id(json_decode($partnership->liaisonids));
    $liaisonids = json_decode($partnership->liaisonids);
    $userlinks = [];
    $liaisoninfo = [];

    foreach ($liaisonids as $id) {
        $user = core_user::get_user($id);
        $userurl = new moodle_url('/user/profile.php', ['id' => $user->id]);
        $userlink = html_writer::link($userurl, fullname($user));

        $phone = '';
        // These if/elseif statements seem inefficient, but I wanted to add a <br /> tag only if a phone number exists, so if you can find a better way, feel free.
        if ($user->phone1) {
            $phoneobj = local_equipment_parse_phone_number($user->phone1);
            $phone = $phoneobj->phone;
            $phone = local_equipment_format_phone_number($phone);
            $phone .= ' <br />';
        } else if ($user->phone2) {
            $phoneobj = local_equipment_parse_phone_number($user->phone2);
            $phone = $phoneobj->phone;
            $phone = local_equipment_format_phone_number($phone);
            $phone .= ' <br />';
        }
        $userlinks[] = $userlink;
        $liaisoninfo[] = html_writer::tag('strong', $userlink)
            . '<br />' . $user->email . '<br />' . $phone;
    }

    return implode('<br />', $liaisoninfo);
}

/**
 * Get the FLC coordinator name, email, and contact phone for a given pickup location and time.
 * Emails and phones will be taken from the user's profile, but admins will have the option to add phone numbers for them if they don't do it themselves.
 *
 * @param int $id The user ID of the coordinator, taken from the Moodle's core user table. MUST be a valid user ID.
 * @return string $liasonhtml An HTML string containing the FLC coordinator's name, email, and phone number.
 */
function local_equipment_get_coordinator_info($id) {
    if (!$id) {
        return get_string('nocoordinatoradded', 'local_equipment');
    }
    // $userid = json_decode($flccoordinator->id);

    $userlinks = [];
    $liaisoninfo = [];

    // foreach ($userid as $id) {
    $user = core_user::get_user($id);
    $userurl = new moodle_url('/user/profile.php', ['id' => $user->id]);
    $userlink = html_writer::link($userurl, fullname($user));

    $phone = '';
    // These if/elseif statements seem inefficient, but I wanted to add a <br /> tag only if a phone number exists, so if you can find a better way, feel free.
    if ($user->phone1) {
        $phoneobj = local_equipment_parse_phone_number($user->phone1);
        $phone = $phoneobj->phone;
        $phone = local_equipment_format_phone_number($phone);
        $phone .= ' <br />';
    } else if ($user->phone2) {
        $phoneobj = local_equipment_parse_phone_number($user->phone2);
        $phone = $phoneobj->phone;
        $phone = local_equipment_format_phone_number($phone);
        $phone .= ' <br />';
    }
    $userlinks[] = $userlink;
    $liaisoninfo[] = html_writer::tag('strong', $userlink)
        . '<br />' . $user->email . '<br />' . $phone;

    $liasonhtml = html_writer::tag(
        'div',
        implode('<br />', $liaisoninfo),
        ['class' => 'nowrap']
    );
    // }

    return $liasonhtml;
}

/**
 * Get all courses from a given category.
 *
 * @param string $categoryname A database record that contains multiple types of addresses.
 * @return object $courses_formatted returns whatever the first category is to match give category name.
 */
function local_equipment_get_master_courses($categoryname = 'ALL_COURSES_CURRENT') {
    global $DB;

    // Set variables to be used for error checking in ./partnerships/addpartnerships.php.
    $responseobject = new stdClass();
    $responseobject->courses_formatted = [];
    $responseobject->categoryname = $categoryname;
    $responseobject->nomastercategory = false;
    $responseobject->nomastercourses = false;
    $responseobject->categoryid = '';

    // Make this an admin setting later on.
    // $categoryname = 'ALL_COURSES_CURRENT';

    // Fetch the course categories by name.
    try {
        $categories = $DB->get_records('course_categories', ['name' => $categoryname]);
        if (empty($categories)) {
            $responseobject->nomastercategory = true;
            throw new moodle_exception(get_string('nocategoryfound', 'local_equipment', $categoryname));
        }

        $category = array_values($categories)[0];
        $courses = $DB->get_records('course', ['category' => $category->id]);

        if (empty($courses)) {
            $responseobject->nomastercourses = true;
            $responseobject->categoryid = $category->id;
            throw new moodle_exception(get_string('nocoursesfoundincategory', 'local_equipment', $categoryname));
        }

        foreach ($courses as $course) {
            $responseobject->courses_formatted[$course->id] = $course->shortname;
            // $responseobject->courses_formatted[$course->id] = $course->fullname;
        }
    } catch (moodle_exception $e) {
        // Handle the exception according to Moodle Coding Standards.
        $responseobject->errors = $e->getMessage();
    }
    return $responseobject;
}

/**
 * Get all liaison names, emails, and contact phones for a given partnership.
 * Liaisons are simply users on the system, so they must of accounts.
 * Emails and phones will be taken from the user's profile, but admins will have the option to add phone numbers for them if they don't do it themselves.
 *
 * @param stdClass $partnership A database record that contains multiple types of addresses.
 * @return string The HTML links for the courses, false otherwise.
 */
function local_equipment_get_courses($partnership) {
    $courseids = json_decode($partnership->courseids);
    $courseinfo = [];

    foreach ($courseids as $id) {
        $course = get_course($id);
        $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
        $courselink = html_writer::link($courseurl, $course->fullname);
        $courseinfo[] = $courselink;
    }
    return implode('<br />', $courseinfo);
}

/**
 * Validates a mobile phone number to make sure it makes sense.
 *
 * @param string $phonenumber The mobile phone number to validate.
 * @param string $country The country code to use.
 * @return object $parsedphonedata An object containing the parsed phone number, may or may not return errors.
 */
function local_equipment_parse_phone_number($phonenumber, $country = 'US') {
    $parsedphoneobj = new stdClass();
    $parsedphoneobj->errors = [];
    $parsedphoneobj->phone = '';

    if (($phonenumber == '')) {
        $parsedphoneobj->errors[] = get_string('phonedoesnotexist', 'local_equipment');
    } else {
        // Remove commonly used characters from the phone number that are not numbers: ().-+ and the white space char.
        $parsedphoneobj->phone = preg_replace("/[\(\)\-\s+\.]/", "", $phonenumber);

        try {
            if (!ctype_digit($parsedphoneobj->phone)) {
                throw new \Exception(get_string('invalidphonenumberformat', 'local_equipment') . get_string('wecurrentlyonlyacceptusphonenumbers', 'local_equipment'));
            }
        } catch (\Exception $e) {
            $parsedphoneobj->errors[] = $e->getMessage();
        }

        switch ($country) {
            case 'US':
                // Check if the number is not empty, if it only contains digits, and if it is a valid 10 or 11 digit United States phone number.
                try {
                    if ((strlen($parsedphoneobj->phone) == 10) && $parsedphoneobj->phone[0] != 1) {
                        $parsedphoneobj->phone = "+1" . $parsedphoneobj->phone;
                    } else if ((strlen($parsedphoneobj->phone) == 11) && $parsedphoneobj->phone[0] == 1) {
                        $parsedphoneobj->phone = "+" . $parsedphoneobj->phone;
                    } else {
                        throw new \Exception(new lang_string('invalidphonenumber', 'local_equipment', $parsedphoneobj->phone) . ' ' . new lang_string('wecurrentlyonlyacceptusphonenumbers', 'local_equipment'));
                    }
                } catch (\Exception $e) {
                    $parsedphoneobj->errors[] = $e->getMessage();
                }
                break;
            default:
                $parsedphoneobj->errors[] = new lang_string('notasupportedcountry', 'local_equipment', $country);
        }
    }
    // if (empty($parsedphoneobj->errors)) {
    // } else {
    //     foreach ($parsedphoneobj->errors as $error) {
    //         \core\notification::add($error, \core\output\notification::NOTIFY_ERROR);
    //     }
    // }
    return $parsedphoneobj;
}

/**
 * Validates a cell phone number to make sure it makes sense.
 *
 * @param string $parsedphonenumber The already parsed phone number. This must follow the exact form as follows: +12345678910
 * @return string
 */
function local_equipment_format_phone_number($parsedphonenumber) {
    $formattedphonenumber = preg_replace("/^\+(\d{1})(\d{3})(\d{3})(\d{4})$/", "+$1 ($2) $3-$4", $parsedphonenumber);
    // try {
    //     if ($parsedphonenumber == $formattedphonenumber) {
    //         throw new \Exception(get_string('invalidphonenumberformat', 'local_equipment') . ' ' . get_string('wecurrentlyonlyacceptusphonenumbers', 'local_equipment'));
    //     }
    // } catch (\Exception $e) {
    //     return $e->getMessage();
    // }

    return $formattedphonenumber;
}

/**
 * Validates a mobile phone number to make sure it makes sense.
 *
 * @param string $phonenumber The mobile phone number to validate.
 * @param string $country The country code to use.
 * @return object $parsedphonedata An object containing the parsed phone number, the country code, whether or not the phone number is valid, and may or may not return errors.
 */
function local_equipment_phone_number_is_valid($phonenumber, $country = 'USA') {
    // Remove commonly used characters from the phone number that are not numbers: ().-+ and the white space char.
    $parsedphonedata = new stdClass();
    $parsedphonedata->number = preg_replace("/[\(\)\-\s+\.]/", "", $phonenumber);
    $parsedphonedata->country = $country;
    $parsedphonedata->errors = [];

    try {
        if (!ctype_digit($parsedphonedata->number)) {
            $parsedphonedata->errors[] = new moodle_exception(new lang_string('invalidphonenumberformat', 'local_equipment') . new lang_string('wecurrentlyonlyacceptusnumbers', 'local_equipment'));
        }
    } catch (moodle_exception $e) {
        return $e->getMessage();
    }

    switch ($country) {
        case 'USA':
            // Check if the number is not empty, if it only contains digits, and if it is a valid 10 or 11 digit United States phone number.
            try {
                if ((strlen($parsedphonedata->number) == 10) && $phonenumber[0] != 1) {
                    $parsedphonedata->number = "+1" . $parsedphonedata->number;
                } else if ((strlen($parsedphonedata->number) == 11) && $phonenumber[0] == 1) {
                    $parsedphonedata->number = "+" . $parsedphonedata->number;
                } else {
                    $parsedphonedata->errors[] = new moodle_exception(new lang_string('invalidphonenumber', 'local_equipment') . ' ' . new lang_string('wecurrentlyonlyacceptcertainnumbers', 'local_equipment', $country));
                }
            } catch (moodle_exception $e) {
                return $e->getMessage();
            }
            break;
        default:
            $parsedphonedata->errors[] = new lang_string('notasupportedcountry', 'local_equipment', $country);
            return;
    }

    return $parsedphonedata;
}

/**
 * Add an address group for if I want each address to appear in a line,
 * though the text boxes currently doesn't have labels doing it this way.
 * I'd have to figure that out, and I don't want to....
 *
 * @param MoodleQuickForm $mform a standard moodle form, probably will be '$this->_form'.
 * @param string $groupname the name of the group to add.
 * @param string $label the label for the group.
 */
function local_equipment_add_address_group($mform, $addresstype, $label) {

    $group = [];
    $types = [];
    $rules = [];
    $addressfields = [
        'streetaddress',
        'apartment',
        'city',
        'state',
        'country',
        'zipcode'
    ];

    $formataddressblock = function ($addressinput) use ($mform, $addresstype, &$types, &$rules) {
        $elementname = "{$addresstype}_{$addressinput}";
        $types[$elementname] = PARAM_TEXT;
        if ($addressinput !== 'apartment') {
            $rules[$elementname] = ['required'];
        }
        if ($addressinput === 'zipcode') {
            $types[$elementname] = PARAM_INT;
        }

        if ($addressinput === 'state') {
            $elementinput = $mform->createElement('select', $elementname, get_string($elementname, 'local_equipment'), local_equipment_get_states());
        } else if ($addressinput === 'country') {
            $elementinput = $mform->createElement('select', $elementname, get_string($elementname, 'local_equipment'), local_equipment_get_countries());
        } else {
            $elementinput = $mform->createElement('text', $elementname, get_string($elementname, 'local_equipment'));
        }
        return [
            $mform->createElement('html', '<div class="col-md-4">'),
            $mform->createElement(
                'static',
                "{$elementname}_label",
                '',
                html_writer::div(get_string($addressinput, 'local_equipment'), 'local-equipment-address-label')
            ),
            $elementinput,
            $mform->createElement('html', '</div>')
        ];
    };

    // $addressfields = ['streetaddress', 'apartment', 'city', 'state', 'country', 'zipcode'];
    $addresselements = array_merge(...array_map($formataddressblock, $addressfields));

    $group = array_merge(
        [$mform->createElement('html', '<div class="form-group row">')],
        $addresselements,
        [$mform->createElement('html', '</div>')]
    );

    $addressgroup = $mform->createElement('group', $addresstype, $label, $group, ' ', false);
    return [
        'element' => $addressgroup,
        'types' => $types,
        'rules' => $rules
    ];
}

/**
 * Conditionally add a div around the block.
 *
 * @param MoodleQuickForm $mform a standard moodle form, probably will be '$this->_form'.
 * @param string $fieldname the field name of address block to add: 'mailing_city', 'physical_code', etc.
 * @param object $element the element to add.
 * @return object $block a block of elements and their options to be added to the form.
 */
function local_equipment_address_group_view($mform, $fieldname, $element) {
    return [
        "{$fieldname}_before" => $mform->createElement('html', '<div class="col-md-0">'),
        $fieldname => $element,
        "{$fieldname}_after" => $mform->createElement('html', '</div>')
    ];
}

/**
 * Conditionally add a div around the block.
 *
 * @param MoodleQuickForm $mform A standard moodle form, probably will be '$this->_form'.
 * @param string $addresstype The type of address block to add: 'mailing', 'physical', 'pickup', or 'billing'.
 * @param string $extrainput An extra input to add to the form (e.g. 'attention', 'name'). If empty, no extra input will be added.
 * @param bool $showsameasmailing Whether or not to show the 'sameasmailing' checkbox and string.
 * @param bool $showsameasphysical Whether or not to show the 'sameasphysical' checkbox and string.
 * @param bool $showapartment Whether or not to show the 'apartment' input.
 * @param bool $showinstructions Whether or not to show the 'instructions' textarea input.
 * @param bool $groupview Whether or not to group the address inputs.
 * @param bool $required Whether or not the address is required.
 * @param object $customdata The custom default data to add to the form.
 * @return object $block A block of elements and their options to be added to the form.
 */
function local_equipment_add_address_block(
    $mform,
    $addresstype,
    $extrainput = '',
    $showsameasmailing = false,
    $showsameasphysical = true,
    $showapartment = false,
    $showinstructions = false,
    $groupview = false,
    $required = false,
    $customdata = null
) {
    $block = new stdClass();
    $block->elements = [];
    $block->options = [];
    $block->isgrouped = false;
    $addressfields = [];

    // Display address fields in order.
    // $extrainput ? $addressfields[] = 'extrainput' : null;
    $addressfields[] = 'streetaddress';
    $showapartment ? $addressfields[] = 'apartment' : null;
    $addressfields[] = 'city';
    $addressfields[] = 'state';
    $addressfields[] = 'country';
    $addressfields[] = 'zipcode';
    // $showinstructions ? $addressfields[] = 'extrainstructions' : null;

    $block->elements["{$addresstype}_address"] = $mform->createElement('static', "{$addresstype}_address", \html_writer::tag('label', get_string("{$addresstype}_address", 'local_equipment'), ['class' => 'form-input-group-labels fw-bold']));
    if ($extrainput !== '') {
        $fieldname = "{$addresstype}_extrainput";
        $block->elements[$fieldname] = $mform->createElement('text', $fieldname, get_string($extrainput, 'local_equipment'));
        $block->options[$fieldname]['type'] = PARAM_TEXT;
    }
    if ($showsameasmailing && $addresstype !== 'mailing') {
        $fieldname = "{$addresstype}_sameasmailing";
        $block->elements[$fieldname] = $mform->createElement('checkbox', $fieldname, get_string('sameasmailing', 'local_equipment'));
        $showsameasphysical = false;
    } else if ($showsameasphysical && $addresstype !== 'physical') {
        $fieldname = "{$addresstype}_sameasphysical";
        $showsameasmailing = false;
        $block->elements[$fieldname] = $mform->createElement('checkbox', $fieldname, get_string('sameasphysical', 'local_equipment'));
    }

    $groupview ? $block->elements['startgroup'] = $mform->createElement('html', '<div class="form-group row m-1 mb-4">') : null;

    // For each address field, we must set the element name, type, rules, and any attributes.
    foreach ($addressfields as $field) {
        // $addresstype = 'mailing', 'physical', 'pickup', or 'billing'.
        // $field = 'attention' (and others),  'streetaddress', 'apartment', 'city', 'state', 'country', or 'zipcode'.
        $fieldname = "{$addresstype}_{$field}";
        if ($field !== 'apartment' && $required) {
            $block->options[$fieldname]['rule'] = 'required';
            $block->options[$fieldname]['rules']['required'] = ['message' => get_string('required'), 'format' => null];
        }
        if ($field === 'extrainstructions') {
            $maxlength = 500;
        } else if ($field === 'zipcode') {
            $maxlength = 10;
        } else {
            $maxlength = 255;
        }
        $block->options[$fieldname]['rules']['maxlength'] = ['message' => get_string('maxlength', 'local_equipment', $maxlength), 'format' => $maxlength];

        // We only have students in the United States for now.
        $field === 'zipcode' ? $block->options[$fieldname]['rules']['regex'] = ['message' => get_string('mustbezipcodeformat', 'local_equipment'), 'format' => '/^\d{5}(-\d{4})?$/'] : null;
        $block->options[$fieldname]['type'] = PARAM_TEXT;

        // Set fields.
        switch ($field) {
            case 'state':
                $element = $mform->createElement('select', $fieldname, get_string($field, 'local_equipment'), local_equipment_get_states());
                break;
            case 'country':
                $element = $mform->createElement('select', $fieldname, get_string($field, 'local_equipment'), local_equipment_get_countries());
                break;
            default:
                $element = $mform->createElement('text', $fieldname, get_string($field, 'local_equipment'));
                break;
        }

        if ($groupview) {
            if (
                $field === 'streetaddress' ||
                $field === 'apartment' ||
                $field === 'city' ||
                $field === 'state' ||
                $field === 'country' ||
                $field === 'zipcode'
            ) {
                $element->updateAttributes(['class' => 'local-equipment-stacked-address-fields']);
            }
            $block->isgrouped = true;
            $addressgroup = local_equipment_address_group_view($mform, $fieldname, $element);

            foreach ($addressgroup as $key => $value) {
                $block->elements[$key] = $value;
            }
        } else {
            $block->elements[$fieldname] = $element;
        }
    }
    $groupview ? $block->elements['endgroup'] = $mform->createElement('html', '</div>') : null;

    if ($showinstructions) {
        $fieldname = "{$addresstype}_extrainstructions";
        $block->elements[$fieldname] = $mform->createElement('textarea', $fieldname, get_string('extrainstructions', 'local_equipment', strtolower(get_string("{$addresstype}_address", 'local_equipment'))));
    }
    foreach ($block->elements as $fieldname => $element) {
        if (isset($customdata->$fieldname)) {
            $mform->setDefault($fieldname, $customdata->$fieldname);
        }
    }
    return $block;
}

/**
 * Add an address block that may have default values populated from the database.
 *
 * @param MoodleQuickForm $mform a standard moodle form, probably will be '$this->_form'.
 * @param string $addresstype the type of address block to add: 'mailing', 'physical', 'pickup', or 'billing'.
 * @param stdClass $data the existing data to populate the form with.
 * @param bool $showsameasphysical whether or not to show the 'sameasphysical' checkbox and string.
 * @return object $block a block of elements to be added to the form.
 */
function local_equipment_add_edit_address_block($mform, $addresstype, $data, $showsameasphysical = true, $requireaddress = false) {
    // $block = new stdClass();

    $mform->addElement('static', $addresstype . 'address', \html_writer::tag('label', get_string($addresstype . 'address', 'local_equipment'), ['class' => 'form-input-group-labels']));

    if ($addresstype == 'mailing' || $addresstype == 'billing') {
        // Attention element
        $mform->addElement('text', "{$addresstype}_extrainput", get_string('attention', 'local_equipment'));
        $mform->setType("{$addresstype}_extrainput", PARAM_TEXT);
        $mform->setDefault("{$addresstype}_extrainput", $data->{"{$addresstype}_extrainput"});
    }

    // KEEP BOTH OF THE LOGICAL STATMENTS: $addresstype !== 'physical' && $showsameasphysical
    // The $addresstype !== 'physical' is what I originally used, but even though I changed to using the $showsameasphysical
    // variable, I still need to keep the original logical statement for the 'sameasphysical' checkbox to show up in the partnership
    // forms (and maybe other forms).
    if ($addresstype !== 'physical' && $showsameasphysical) {
        // Same as physical checkbox
        $mform->addElement('advcheckbox', "{$addresstype}_sameasphysical", get_string('sameasphysical', 'local_equipment'));
        $mform->setType("{$addresstype}_sameasphysical", PARAM_BOOL);
        $mform->setDefault("{$addresstype}_sameasphysical", $data->{"{$addresstype}_sameasphysical"});
    }

    $mform->addElement(
        'text',
        "{$addresstype}_streetaddress",
        get_string('streetaddress', 'local_equipment')
    );
    $mform->setType("{$addresstype}_streetaddress", PARAM_TEXT);
    $mform->setDefault("{$addresstype}_streetaddress", $data->{"{$addresstype}_streetaddress"});

    $mform->addElement(
        'text',
        "{$addresstype}_apartment",
        get_string('apartment', 'local_equipment')
    );
    $mform->setType("{$addresstype}_apartment", PARAM_TEXT);
    $mform->setDefault("{$addresstype}_apartment", $data->{"{$addresstype}_apartment"});

    $mform->addElement('text', "{$addresstype}_city", get_string('city', 'local_equipment'));
    $mform->setType("{$addresstype}_city", PARAM_TEXT);
    $mform->setDefault("{$addresstype}_city", $data->{"{$addresstype}_city"});

    $mform->addElement('select', "{$addresstype}_state", get_string('state', 'local_equipment'), local_equipment_get_states());
    $mform->setType("{$addresstype}_state", PARAM_TEXT);
    $mform->setDefault("{$addresstype}_state", $data->{"{$addresstype}_state"});

    $mform->addElement('select', "{$addresstype}_country", get_string('country', 'local_equipment'), local_equipment_get_countries());
    $mform->setType("{$addresstype}_country", PARAM_TEXT);
    $mform->setDefault("{$addresstype}_country", $data->{"{$addresstype}_country"});

    $mform->addElement('text', "{$addresstype}_zipcode", get_string('zipcode', 'local_equipment'));
    $mform->setType("{$addresstype}_zipcode", PARAM_TEXT);
    $mform->setDefault("{$addresstype}_zipcode", $data->{"{$addresstype}_zipcode"});

    if ($requireaddress) {
        $mform->addRule("{$addresstype}_streetaddress", get_string('required'), 'required', null, 'client');
        $mform->addRule("{$addresstype}_city", get_string('required'), 'required', null, 'client');
        $mform->addRule("{$addresstype}_state", get_string('required'), 'required', null, 'client');
        $mform->addRule("{$addresstype}_country", get_string('required'), 'required', null, 'client');
        $mform->addRule("{$addresstype}_zipcode", get_string('required'), 'required', null, 'client');
    }

    if ($addresstype == 'pickup') {
        // Instructions element
        $mform->addElement('textarea', "{$addresstype}_extrainstructions", get_string('pickupinstructions', 'local_equipment'));
        $mform->setType("{$addresstype}_extrainstructions", PARAM_TEXT);
        $mform->setDefault("{$addresstype}_extrainstructions", $data->{"{$addresstype}_extrainstructions"});
    }

    // if ($addresstype === 'physical') {
    //     // Physical address is the only address that's required.
    //     $mform->addRule("{$addresstype}_streetaddress", get_string('required'), 'required', null, 'client');
    //     $mform->addRule("{$addresstype}_city", get_string('required'), 'required', null, 'client');
    //     $mform->addRule("{$addresstype}_state", get_string('required'), 'required', null, 'client');
    //     $mform->addRule("{$addresstype}_country", get_string('required'), 'required', null, 'client');
    //     $mform->addRule("{$addresstype}_zipcode", get_string('required'), 'required', null, 'client');
    // }
}

/**
 * Add an address block that may have default values populated from the database.
 *
 * @param MoodleQuickForm $mform A standard moodle form, probably will be '$this->_form'.
 * @param string $addresstype The type of address block to add: 'mailing', 'physical', 'pickup', or 'billing'.
 * @param string $label The existing data to populate the form with.
 * @param string $defaultstarttime The existing hours to populate the hour dropdown menu with.
 * @param string $defaultendtime The existing minutes to populate the minute dropdown menu with.
 * @return object $block a block of elements to be added to the form.
 */
function local_equipment_create_time_selector($mform, $name, $label, $defaulttime = null) {
    $hours = array_combine(range(0, 23), range(0, 23));
    $minutes = array_combine(range(0, 59, 5), range(0, 59, 5)); // 5-minute intervals

    $hourelement = $mform->createElement('select', $name . 'hour', get_string('hour'), $hours);
    $minuteelement = $mform->createElement('select', $name . 'minute', get_string('minute'), $minutes);
    // Set the default starting hour and minute if it exists.
    if ($defaulttime) {
        $mform->setDefault($name . 'hour', userdate($defaulttime, '%H'));
        $mform->setDefault($name . 'minute', userdate($defaulttime, '%M'));
    }

    // Set the default ending hour and minute if it exists.

    $elements = array(
        $mform->createElement('html', '<div class="form-group row">'),
        $mform->createElement('html', '<div class="col-md-6">'),
        $mform->createElement(
            'static',
            $name . '_hourlabel',
            '',
            html_writer::div(get_string('hour', 'local_equipment'), 'local-equipment-pickups-addpickups-time-selectors')
        ),
        $hourelement,
        $mform->createElement('html', '</div>'),
        $mform->createElement('html', '<div class="col-md-6">'),
        $mform->createElement(
            'static',
            $name . '_minutelabel',
            '',
            html_writer::div(get_string('minute', 'local_equipment'), 'local-equipment-pickups-addpickups-time-selectors')
        ),
        $minuteelement,
        $mform->createElement('html', '</div>'),
        $mform->createElement('html', '</div>')
    );
    return $mform->createElement('group', $name, $label, $elements, ' ', false);
}

/**
 * Get the latest version of an agreement.
 *
 * @param int $agreementid The ID of the agreement
 * @return object|false The latest version of the agreement, or false if not found
 */
function local_equipment_get_latest_agreement_version($agreementid) {
    global $DB;

    $sql = "SELECT a.*
            FROM {local_equipment_agreement} a
            WHERE a.id = :agreementid OR a.previousversionid = :previousversionid
            ORDER BY a.version DESC
            LIMIT 1";

    return $DB->get_record_sql($sql, ['agreementid' => $agreementid, 'previousversionid' => $agreementid]);
}

/**
 * Check if a user has signed a specific agreement.
 *
 * @param int $agreementid The ID of the agreement
 * @param int $userid The ID of the user
 * @return bool True if the user has signed the agreement, false otherwise
 */
function local_equipment_user_has_signed_agreement($agreementid, $userid) {
    global $DB;

    $sql = "SELECT 1
            FROM {local_equipment_agreementsubmission} s
            JOIN {local_equipment_agreement} a ON s.agreementid = a.id
            WHERE (a.id = :agreementid OR a.previousversionid = :previousversionid)
            AND s.userid = :userid
            AND s.status = 'accepted'";

    return $DB->record_exists_sql($sql, ['agreementid' => $agreementid, 'previousversionid' => $agreementid, 'userid' => $userid]);
}

/**
 * Check if an agreement is currently active.
 *
 * @param object $agreement The agreement object
 * @return bool True if the agreement is active, false otherwise
 */
function local_equipment_agreement_get_status($agreement) {
    $status = 'unknown';
    $currenttime = time();
    if ($currenttime < $agreement->activestarttime) {
        return 'notstarted';
    } else if ($currenttime >= $agreement->activestarttime && $currenttime < $agreement->activeendtime) {
        return 'active';
    } else if ($currenttime >= $agreement->activeendtime) {
        return 'ended';
    }
    return $status;
}

/**
 * Remove partnership-course relationship from the local_equipment_partnership_course table.
 *
 * @param string $id The partnership ID
 * @return bool True if all rows for the partnership were deleted, false otherwise
 */
function local_equipment_remove_partnership_course_entries($id) {
    global $DB;

    return $DB->delete_records('local_equipment_partnership_course', ['partnershipid' => $id]);
}

/**
 * Add partnership-course relationship to the local_equipment_partnership_course table.
 *
 * @param string $id The partnership ID
 * @return bool True if at least one relationship was added, false otherwise
 */
function local_equipment_add_partnership_course_entries($id, $courseids) {
    global $DB;

    $success = false;
    foreach ($courseids as $courseid) {
        $record = new stdClass();
        $record->partnershipid = $id;
        $record->courseid = $courseid;
        $success = $DB->insert_record('local_equipment_partnership_course', $record);
        if (!$success) {
            $msg = new stdClass();
            $msg->partnershipid = $id;
            $msg->courseid = $courseid;
            \core\notification::add(get_string('partnershipcourserelationshipnotadded', 'local_equipment', $msg), \core\output\notification::NOTIFY_ERROR);
        }
    }
    return $success;
}

/**
 * Retrieves active partnerships.
 *
 * @return array An associative array of active partnerships, with partnership ID as the key and partnership name as the value.
 */
function local_equipment_get_active_partnerships() {
    global $DB;
    return $DB->get_records_menu('local_equipment_partnership', ['active' => 1], 'name', 'id, name');
}


/**
 * Get row of partnership data fromo the DB by partnership ID.
 *
 * @param int $id The partnership's ID.
 * @return object An associative array of the partnership, with with all it's information.
 */
function local_equipment_get_partnership_by_id($id) {
    global $DB;
    return $DB->get_record('local_equipment_partnership', ['id' => $id]);
}


/**
 * Retrieves partnership courses.
 *
 * @return array An associative array of partnership courses, with course ID as the key and course fullname as the value.
 */
function local_equipment_get_partnerships_with_courses() {
    global $DB;

    $partnerships = $DB->get_records('local_equipment_partnership', ['active' => 1]);
    $partnershipdata = array();


    foreach ($partnerships as $partnership) {

        // echo '<pre>';
        // var_dump($partnership->name);
        // echo '</pre>';
        $courses = local_equipment_get_partnership_courses_this_year($partnership->id);
        // echo '<pre>';
        // var_dump($courses->courses_formatted);
        // echo '</pre>';
        // echo '<br />';
        // echo '<br />';
        // echo '<br />';

        // $courseids = json_decode($partnership->courseids);
        if (!isset($courses->nocourses)) {
            // $courses = $DB->get_records_list('course', 'id', $courseids->, '', 'id, fullname');
            $partnershipdata[$partnership->id] = array_values($courses->courses);
        }
    }
    // die();

    return $partnershipdata;
}

/**
 * Retrieves partnership pickuptimes.
 *
 * @return array An associative array of partnership courses, with course ID as the key and course fullname as the value.
 */
function local_equipment_get_partnerships_with_pickuptimes() {
    global $DB;

    $partnerships = $DB->get_records('local_equipment_partnership', ['active' => 1]);
    $pickuptimedata = [];

    foreach ($partnerships as $partnership) {
        $pickups = $DB->get_records('local_equipment_pickup', ['partnershipid' => $partnership->id], 'pickupdate, starttime');
        if (!empty($pickups)) {
            $pickuptimedata[$partnership->id] = array_values(array_filter(array_map(function ($pickup) {
                // Only include pickup times where starttime and endtime are different
                if ($pickup->starttime !== $pickup->endtime) {
                    return [
                        'id' => $pickup->id,
                        'datetime' => userdate($pickup->pickupdate, get_string('strftimedate', 'langconfig')) . ' ' .
                            userdate($pickup->starttime, get_string('strftimetime', 'langconfig')) . ' - ' .
                            userdate($pickup->endtime, get_string('strftimetime', 'langconfig'))
                    ];
                }
                return null;
            }, $pickups)));
        }
    }

    return $pickuptimedata;
}

/**
 * Retrieves partnership pickuptimes.
 *
 * @return array An associative array of partnership courses, with course ID as the key and course fullname as the value.
 */
function local_equipment_get_all_active_pickup_times() {
    global $DB;

    $partnerships = $DB->get_records('local_equipment_pickup', ['status' => 'confirmed']);
    $pickuptimedata = array();

    foreach ($partnerships as $partnership) {
        $pickups = $DB->get_records('local_equipment_pickup', ['partnershipid' => $partnership->id], 'pickupdate, starttime');
        if (!empty($pickups)) {
            $pickuptimedata[$partnership->id] = array_values(array_filter(array_map(function ($pickup) {
                // Only include pickup times where starttime and endtime are different
                if ($pickup->starttime !== $pickup->endtime) {
                    return [
                        'id' => $pickup->id,
                        'datetime' => userdate($pickup->pickupdate, get_string('strftimedate', 'langconfig')) . ' ' .
                            userdate($pickup->starttime, get_string('strftimetime', 'langconfig')) . ' - ' .
                            userdate($pickup->endtime, get_string('strftimetime', 'langconfig'))
                    ];
                }
                return null;
            }, $pickups)));
        }
    }

    return $pickuptimedata;
}

/**
 * Retrieves partnership courses.
 *
 * @param int $partnershipid The ID of the partnership.
 * @return array An associative array of partnership courses, with course ID as the key and course fullname as the value.
 */
function local_equipment_get_partnership_courses($partnershipid) {
    global $DB;
    $partnership = $DB->get_record('local_equipment_partnership', ['id' => $partnershipid]);
    $courseids = json_decode($partnership->courseids);
    return $DB->get_records_list('course', 'id', $courseids, '', 'id, fullname');
}

/**
 * Retrieves partnership courses from the course category for the current school year. Courses can be a direct child of a parent
 * category with the matching 'idnumber' field, or the courses can be nested an arbitrary number of levels deep under the parent
 * category.
 *
 * Remember that the 'idnumber' field must be manually entered by a course manager with a prefix that matches the prefix given in
 * the plugin's configuration settings. This will be in the format of
 * {prefix}#{partnershipid}_{schoolyearrange_start}-{schoolyearrange_end}, e.g. partnership#10_2024-2025.  In other words, in this
 * example, an admin would manually enter partnership#10_2024-2025 in this 'idnumber' field for the course category.
 *
 * @param int $partnershipid The ID of the partnership.
 * @param string $listingtype The type of listing to return: 'categories' to get listings from course_categories or 'partnerships'
 * to get listings using 'listid' from local_equipment_partnership.
 * @return stdClass An object containing the courses, or an error message if no courses are found.
 */
function local_equipment_get_partnership_courses_this_year($partnershipid) {
    global $DB;

    $responseobject = new stdClass();
    $responseobject->courses_formatted = [];
    $schoolyear = local_equipment_get_school_year();
    $keyword = 'partnership';
    // The following string will look something like "partnership#1_2024-2025".
    // The word 'partnership' will be an admin-configurable string soon.
    $listingid = $DB->get_field('local_equipment_partnership', 'listingid', ['id' => $partnershipid]);
    $partnershipid_string = $keyword . '#' . $listingid . '_' . $schoolyear;

    // // Get all the courses at any depth under a given 'idnumber' from the course.
    try {

        $sql = "SELECT DISTINCT c.*
            FROM mdl_course c
            JOIN mdl_course_categories cc ON c.category = cc.id
            WHERE cc.path LIKE (
                SELECT CONCAT(path, '%')
                FROM mdl_course_categories
                WHERE idnumber = :idnumber
            )";
        $responseobject->courses = $DB->get_records_sql($sql, ['idnumber' => $partnershipid_string]);
        if (empty($responseobject->courses)) {
            $responseobject->nocourses = true;
            throw new moodle_exception(get_string('nocoursesfoundforpartnershipwithid', 'local_equipment', $partnershipid_string));
        }
        foreach ($responseobject->courses as $course) {
            // $responseobject->courses_formatted[$course->id] = $course->shortname;
            $responseobject->courses_formatted[$course->id] = $course->fullname;
        }
    } catch (moodle_exception $e) {
        // Handle the exception according to Moodle Coding Standards.
        $responseobject->errors = $e->getMessage();
    }
    return $responseobject;
}

/**
 * Retrieve partnership categories from the course category for the given school year, where the 'idnumber' field =
 * {prefix}#{partnershipid}_{schoolyearrange_start}-{schoolyearrange_end}, e.g. partnership#10_2024-2025 or partnerships#3_2032-2033
 * or location#6022_4626-4627. The {prefix} is an admin-configurable string, but the overall format is hard-coded below.
 *
 * @param int $schoolyearrange The school year range within the 'idnumber' field of the school year category.
 * @param bool $defualttoselection Whether or not to add the 'selectpartnershipforlisting' string to index 0 of the array.
 * @param bool $showidnumber Whether or not to show the ID number of the partnership to the left of its name in the dropdown menu.
 * @return stdClass An object containing the partnership categories to choose from, or an error message if no partnership categories
 * are found.
 */
function local_equipment_get_partnership_categories_for_school_year($schoolyearrange = null, $defualttoselection = false, $showidnumber = false) {
    global $DB;

    // Default to the current school year if no $schoolyearrange argument is given.
    if (!$schoolyearrange) {
        $schoolyearrange = local_equipment_get_school_year();
    }

    $partnershipcategoriesobject = new stdClass();
    $partnershipcategoriesobject->categories = [];
    $partnershipcategoriesobject->partnerships = [];

    $sql = "SELECT DISTINCT cc.id, cc.name, cc.idnumber, cc.path
            FROM mdl_course_categories cc
            WHERE cc.idnumber LIKE :idnumber";
    $prefix = get_config('local_equipment', 'partnershipcategoryprefix');
    $partnershipcategoriesobject->categories = $DB->get_records_sql($sql, ['idnumber' => "$prefix#%_$schoolyearrange"]);
    if (empty($partnershipcategoriesobject->categories)) {
        $partnershipcategoriesobject->nopartnershipcategories = true;
        throw new moodle_exception(get_string('nopartnershipcategoriesfoundforschoolyear', 'local_equipment', $schoolyearrange));
    } else {
        if ($defualttoselection) {
            $string = get_string('selectpartnershipforlisting', 'local_equipment');
            $partnershipcategoriesobject->catids_catnames[0] =
                $partnershipcategoriesobject->partnershipids_catnames[0] =
                $partnershipcategoriesobject->partnershipids_partnershipnames[0] =
                $partnershipcategoriesobject->catids_partnershipnames[0] =
                $partnershipcategoriesobject->partnershipids[0] = $string;
        }
    }

    // Get all the courses at any depth under a given 'idnumber' from the course.
    try {
        $sql = "SELECT DISTINCT cc.id, cc.name, cc.idnumber, cc.path
            FROM mdl_course_categories cc
            WHERE cc.idnumber LIKE :idnumber";
        $prefix = get_config('local_equipment', 'partnershipcategoryprefix');
        $partnershipcategoriesobject->categories = $DB->get_records_sql($sql, ['idnumber' => "$prefix#%_$schoolyearrange"]);
        $partnershipcategoriesobject->partnerships = $DB->get_records('local_equipment_partnership', ['active' => 1]);

        if (empty($partnershipcategoriesobject->categories)) {
            $partnershipcategoriesobject->nopartnershipcategories = true;
            throw new moodle_exception(get_string('nopartnershipcategoriesfoundforschoolyear', 'local_equipment', $schoolyearrange));
        } else {
            if ($defualttoselection) {
                $string = get_string('selectpartnershipforlisting', 'local_equipment');
                $partnershipcategoriesobject->catids_catnames[0] =
                    $partnershipcategoriesobject->partnershipids_catnames[0] =
                    $partnershipcategoriesobject->partnershipids_partnershipnames[0] =
                    $partnershipcategoriesobject->catids_partnershipnames[0] =
                    $partnershipcategoriesobject->partnershipids[0] = $string;
            }
            foreach ($partnershipcategoriesobject->categories as $category) {
                $partnershipid = explode('#', $category->idnumber)[1];
                $partnershipid = explode('_', $partnershipid)[0];
                $partnership = $DB->get_record('local_equipment_partnership', ['id' => $partnershipid]);

                $partnershipcategoriesobject->catids_catnames[$category->id] = $showidnumber ? "$category->id – $category->name" : $category->name;
                $partnershipcategoriesobject->partnershipids_catnames[$partnershipid] = $showidnumber ? "$category->id – $category->name" : $category->name;
                $partnershipcategoriesobject->catids_partnershipnames[$category->id] = $showidnumber ? "$partnership->id – $partnership->name" : $partnership->name;
                $partnershipcategoriesobject->partnershipids[] = $partnershipid;

                ksort($partnershipcategoriesobject->partnershipids_catnames);
            }

            foreach ($partnershipcategoriesobject->partnerships as $id => $partnership) {
                $partnershipcategoriesobject->partnershipids_partnershipnames[$id] = $showidnumber ? "$partnership->id – $partnership->name" : $partnership->name;
                ksort($partnershipcategoriesobject->partnershipids_partnershipnames);
            }
        }
    } catch (moodle_exception $e) {
        // Handle the exception according to Moodle Coding Standards.
        $partnershipcategoriesobject->errors = $e->getMessage();
    }
    return $partnershipcategoriesobject;
}

/**
 * Generate HTML for partnership course table
 *
 * @package     local_equipment
 * @param       int $partnershipid The partnership ID
 * @return      string HTML for the course table
 */
function local_equipment_generate_course_table($partnershipid) {
    global $OUTPUT;

    $coursesthisyear = local_equipment_get_partnership_courses_this_year($partnershipid);
    $courses = $coursesthisyear->courses_formatted;

    if (!$coursesthisyear || empty($coursesthisyear->courses_formatted) || empty($courses)) {
        return html_writer::div(
            get_string('nocoursesfoundforthispartnership', 'local_equipment'),
            'alert alert-warning'
        );
    }

    // Start building the table structure
    $html = html_writer::start_div('local-equipment_partnership-courses-container');
    $html .= html_writer::start_div('local-equipment_main-courses-container');

    // Header
    $html .= html_writer::start_div('local-equipment_courses-header');
    $html .= html_writer::start_div('local-equipment_courses-header-row d-flex');
    $html .= html_writer::div(
        get_string('courseid', 'local_equipment'),
        'local-equipment_course-id-col'
    );
    $html .= html_writer::div(
        get_string('coursename', 'local_equipment'),
        'local-equipment_course-name-col'
    );
    $html .= html_writer::end_div(); // End header row
    $html .= html_writer::end_div(); // End header

    // Scrollable content
    $html .= html_writer::start_div('local-equipment_courses-scroll-content');
    $html .= html_writer::start_div('local-equipment_courses-table');

    // Sort courses by ID
    $courseids = array_keys($courses);
    natsort($courseids);

    // Add each course row
    foreach ($courseids as $courseid) {
        $courseurl = new moodle_url('/course/view.php', ['id' => $courseid]);
        $html .= html_writer::start_div('local-equipment_course-row d-flex');
        $html .= html_writer::div($courseid, 'local-equipment_course-id-col');
        $html .= html_writer::div(
            html_writer::link($courseurl, $courses[$courseid]),
            'local-equipment_course-name-col'
        );
        $html .= html_writer::end_div();
    }

    $html .= html_writer::end_div(); // End courses table
    $html .= html_writer::end_div(); // End scroll content

    // Footer with count
    $html .= html_writer::div(
        get_string('totalcourses', 'local_equipment') . ': ' . count($courses),
        'local-equipment_courses-footer'
    );

    $html .= html_writer::end_div(); // End main container
    $html .= html_writer::end_div(); // End partnership courses container

    return $html;
}

/**
 * Get the school year string (yyyy-yyyy) given a unix timestamp. TODO: Accept years larger than 4-digits.
 *
 * @param int $timestamp The timestamp to get the school year for.
 * @return string The school year string.
 */
function local_equipment_get_school_year($timestamp = null) {
    $timestamp ?: time();
    $year = date('Y', $timestamp);
    $month = date('n', $timestamp);

    // Eventually, we'll need to get the school year start date from the plugin settings, probably on a Partnership basis. Meaning,
    // there should be a field in the local_equipment_partnership table the defines the start and end date for that partnership. We
    // could also automate this further to scrape government websites to fine official start dates for states and/or school
    // districts. For now, we'll just use July 1st of the given year as the start date.
    if ($month < 7) {
        return ($year - 1) . '-' . $year;
    } else {
        return $year . '-' . ($year + 1);
    }
}

/**
 * Retrieves pickup times for a partnership.
 *
 * @param int $partnershipid The ID of the partnership.
 * @return array An associative array of pickup times, with pickup ID as the key and formatted pickup time as the value.
 */
function local_equipment_get_pickup_times($partnershipid) {
    global $DB;
    $pickups = $DB->get_records('local_equipment_pickup', ['partnershipid' => $partnershipid]);
    $times = [0 => get_string('notimesforme', 'local_equipment')];
    foreach ($pickups as $pickup) {
        $times[$pickup->id] = userdate($pickup->starttime, get_string('strftimedatetime', 'langconfig'));
    }
    return $times;
}

/**
 * Retrieves active agreements.
 *
 * @return array An array of active agreements.
 */
function local_equipment_get_active_agreements() {
    global $DB;
    $now = time();
    return $DB->get_records_sql(
        "SELECT * FROM {local_equipment_agreement}
         WHERE activestarttime <= :now1 AND activeendtime > :now2
         ORDER BY version DESC",
        ['now1' => $now, 'now2' => $now]
    );
}

/**
 * Checks if an agreement requires an electronic signature.
 *
 * @param array $agreements An array of agreements.
 * @return bool True if at least one agreement requires an electronic signature, false otherwise.
 */
function local_equipment_requires_signature($agreements) {
    foreach ($agreements as $agreement) {
        if ($agreement->requireelectronicsignature) {
            return true;
        }
    }
    return false;
}

/**
 * Generate student email based on the primary parent's email.
 * @param string $parentemail The primary parent's email. It doesn't matter which parent's email is used, but it's just easier to
 * user which ever one is found first.
 * @param string $studentfirstname The student's first name.
 * @return string The generated student email address.
 */
function local_equipment_generate_student_email($parentemail, $studentfirstname) {
    $parts = explode('@', $parentemail);
    $newemail = $parts[0] . '+' . strtolower($studentfirstname) . '@' . $parts[1];
    return $newemail;
}

/**
 * Saves a consent form. This fuction is not currently used.
 *
 * @param object $data The consent form data.
 * @return bool True if the consent form is successfully saved, false otherwise.
 */
function local_equipment_save_vcc_form($data) {
    global $DB, $USER;

    // Start transaction.
    $transaction = $DB->start_delegated_transaction();

    try {
        // Decode the selected courses into an stdClass object.
        $data->selectedcourses = json_decode($data->selectedcourses);

        $vccsubmission = new stdClass();
        $vccsubmission->userid = $USER->id;
        $vccsubmission->partnershipid = $data->partnership;
        $vccsubmission->pickupid = $data->pickuptime;
        $vccsubmission->studentids = '';
        $vccsubmission->agreementids = '';
        $vccsubmission->confirmationid = md5(uniqid(rand(), true)); // Generate a unique confirmation ID
        $vccsubmission->confirmationexpired = 0;
        $vccsubmission->pickupmethod = $data->pickupmethod;
        $vccsubmission->pickuppersonname = $data->pickuppersonname ?? '';
        $vccsubmission->pickuppersonphone = $data->pickuppersonphone ?? '';
        $vccsubmission->pickuppersondetails = $data->pickuppersondetails ?? '';
        $vccsubmission->usernotes = $data->usernotes ?? '';
        $vccsubmission->timecreated = $vccsubmission->timemodified = time();

        // Insert vccsubmission record.
        $vccsubmission->id = $DB->insert_record('local_equipment_vccsubmission', $vccsubmission);

        // Make record updates for Moodle Core user.
        $userrecord = new stdClass();
        $userrecord->id = $USER->id;
        $userrecord->firstname = $data->firstname;
        $userrecord->lastname = $data->lastname;
        $userrecord->phone2 = $data->phone;

        // Update core user record.
        $DB->update_record('user', $userrecord);

        // Insert extended user (parent) record.
        $parentrecord = new stdClass();
        // Foriegn keys first.
        $parentrecord->userid = $userrecord->id;
        $parentrecord->partnershipid = $data->partnership;
        $parentrecord->pickupid = $data->pickuptime;
        // $parentrecord->studentids = '';
        // $parentrecord->vccsubmissionids = '';
        // $parentrecord->phoneverificationids = '';

        // Mailing address-related fields. Must be renamed in the database schema.
        $parentrecord->mailing_extrainput = $data->mailing_extrainput ?? '';
        $parentrecord->mailing_streetaddress = $data->mailing_streetaddress;
        $parentrecord->mailing_apartment = $data->mailing_apartment ?? '';
        $parentrecord->mailing_city = $data->mailing_city;
        $parentrecord->mailing_state = $data->mailing_state;
        $parentrecord->mailing_country = $data->mailing_country;
        $parentrecord->mailing_zipcode = $data->mailing_zipcode;
        $parentrecord->mailing_extrainsructions = $data->mailing_extrainsructions ?? '';

        // Billing address-related fields. Must be renamed in the database schema.
        $parentrecord->billing_extrainput = $data->billing_extrainput ?? '';
        $parentrecord->billing_sameasmailing = $data->billing_sameasmailing ?? 0;
        $parentrecord->billing_streetaddress = $data->billing_streetaddress ?? '';
        $parentrecord->billing_apartment = $data->billing_apartment ?? '';
        $parentrecord->billing_city = $data->billing_city ?? '';
        $parentrecord->billing_state = $data->billing_state ?? '';
        $parentrecord->billing_country = $data->billing_country ?? '';
        $parentrecord->billing_zipcode = $data->billing_zipcode ?? '';
        $parentrecord->billing_extrainsructions = $data->billing_extrainsructions ?? '';

        $parentrecord->timecreated = time();

        // Insert student records.

        $studentids = [];
        for ($i = 0; $i < $data->students; $i++) {
            $studentrecord = new stdClass();
            // The string value of $i.
            $s = strval($i);
            // The selectedcourses string should have already been decoded above.
            $selectedcourses = local_equipment_convert_array_values_to_int($data->selectedcourses->$s);

            $studentrecord->userid = $data->student_id[$i] ?? 0;
            $studentrecord->vccsubmissionid = $vccsubmission->id;
            $studentrecord->courseids = json_encode($selectedcourses) ?? '';
            $studentrecord->firstname = $data->student_firstname[$i];
            $studentrecord->lastname = $data->student_lastname[$i];
            $studentrecord->email = $data->student_email[$i] ?? local_equipment_generate_student_email($USER->email, $studentrecord->firstname);
            $studentrecord->dateofbirth = $data->student_dob[$i];

            $studentrecord->id = $DB->insert_record('local_equipment_vccsubmission_student', $studentrecord);
            // Make an array of student IDs for later use.
            $studentids[] = $studentrecord->id;

            // Insert student course records.
            foreach ($selectedcourses as $courseid) {
                $DB->insert_record('local_equipment_vccsubmission_student_course', [
                    'studentid' => $studentrecord->id,
                    'courseid' => $courseid
                ]);
            }
        }

        // Update vccsubmission with studentids.
        $DB->set_field('local_equipment_vccsubmission', 'studentids', json_encode($studentids), ['id' => $vccsubmission->id]);
        // echo '<pre>';
        // var_dump($selectedcourses);
        // echo '</pre>';
        // die();

        // Save agreement records.
        $agreementids = [];
        foreach ($data->agreement_ids as $agreementid) {
            $agreementrecord = new stdClass();
            $agreementrecord->vccsubmissionid = $vccsubmission->id;
            $agreementrecord->agreementid = $agreementid;

            // Check if this agreement has an opt-in/opt-out response.
            $optionfield = 'agreement_' . $agreementid . '_option';
            if (isset($data->$optionfield)) {
                $agreementrecord->optinout = ($data->$optionfield === 'optin') ? 1 : 2;
            } else {
                $agreementrecord->optinout = 0;
            }

            $DB->insert_record('local_equipment_vccsubmission_agreement', $agreementrecord);
            $agreementids[] = $agreementid;
        }

        // Update vccsubmission with agreementids.
        $DB->set_field('local_equipment_vccsubmission', 'agreementids', json_encode($agreementids), ['id' => $vccsubmission->id]);

        // Update or insert local_equipment_user record.
        $userrecord = $DB->get_record('local_equipment_user', ['userid' => $USER->id]);
        if (!$userrecord) {
            $userrecord = new stdClass();
            $userrecord->userid = $USER->id;
        }
        $userrecord->partnershipid = $data->partnership;
        $userrecord->pickupid = $data->pickuptime;
        $userrecord->mailing_streetaddress = $data->street;
        $userrecord->mailing_city = $data->city;
        $userrecord->mailing_state = $data->state;
        $userrecord->mailing_country = $data->country;
        $userrecord->mailing_zipcode = $data->zipcode;
        $userrecord->timemodified = time();

        if (isset($userrecord->id)) {
            $DB->update_record('local_equipment_user', $userrecord);
        } else {
            $userrecord->timecreated = time();
            $userrecord->id = $DB->insert_record('local_equipment_user', $userrecord);
        }

        // Append new vccsubmission id to user's vccsubmissionids.
        $vccsubmissionids = json_decode($userrecord->vccsubmissionids) ?: [];
        $vccsubmissionids[] = $vccsubmission->id;
        $DB->set_field(
            'local_equipment_user',
            'vccsubmissionids',
            json_encode($vccsubmissionids),
            ['id' => $userrecord->id]
        );

        // Commit transaction.
        $transaction->allow_commit();

        return $vccsubmission->id;
    } catch (Exception $e) {
        $transaction->rollback($e);
        return false;
    }
}

/**
 * Get all student names, emails, and contact phones for a given Virtual Course Consent submission.
 *
 * @param stdClass $submission A database record that contains 1 or more student ids.
 * Not user ids from core user table, but from local_equipment_vccsubmission_student.
 * @return string * @return string Imploded array of all liaisons with there required information.
 */
function local_equipment_get_vcc_students($submission) {
    global $DB;

    $studentids = json_decode($submission->studentids);
    $students = $DB->get_records_list('local_equipment_vccsubmission_student', 'id', $studentids);
    $studentinfo = [];

    foreach ($students as $student) {
        $courseids = json_decode($student->courseids);
        $courseinfo = [];
        foreach ($courseids as $course) {
            $course = get_course($course);
            $courseinfo[] = $course->shortname;
        }

        $studentinfo[] = html_writer::tag('strong', $student->firstname . ' ' . $student->lastname);
        if ($student->email) {
            $studentinfo[] = html_writer::tag('span', $student->email);
        }

        $studentinfo[] = html_writer::tag('div', implode('<br />', $courseinfo), ['class' => 'ml-4']);
    }

    return implode('<br />', $studentinfo);
}

/**
 * Checks to see if phone is verified on login.
 *
 * @param \core\event\user_loggedin $event The event.
 * @return bool True if the user's phone number has been verified; false otherwise.
 */
function local_equipment_vcc_phone_verified(\core\event\user_loggedin $event) {
    global $DB, $USER, $SESSION;
    $phone_verified = $DB->get_field('local_equipment_user', 'phone_verified', ['userid' => $USER->id], IGNORE_MULTIPLE);
    $redirecturl = new moodle_url('/local/equipment/phonecommunication/verifyphone.php');
    $msg = get_string('mustverifyphone', 'local_equipment');

    // We must use a strict type here since $phone_verified can be NULL as well as false. NULL will go to the 'if' part of the
    // statement.
    if ($phone_verified !== false) {
        if ($phone_verified === 1) {
            $SESSION->local_equipment_phone_verified = true;
        } else {
            redirect($redirecturl);
            $SESSION->local_equipment_phone_verified = false;
        }
    }
}

/**
 * Checks to see if phone exists.
 *
 * @param $userid A user id.
 * @return string The phone number, if it exists; false otherwise.
 */
function local_equipment_vccsubmission_phone_exists($userid) {
    global $DB;

    // Construct the SQL WHERE clause
    $where = 'userid = :userid AND phone != 0';
    $params = ['userid' => $userid];

    // Get the records
    $records = $DB->get_records_select('local_equipment_vccsubmission', $where, $params, '', 'id,phone');

    // Extract the phone value if a record is found
    $phone = !empty($records) ? reset($records)->phone : false;

    return $phone;
}

/**
 * Checks to see if phone is verified on login.
 *
 * @param $userid A user id.
 * @return string The phone number, if it exists; false otherwise.
 */
function local_equipment_user_phone_exists($userid) {
    global $DB;

    // Construct the SQL WHERE clause
    $where = 'userid = :userid AND phone != NULL';
    $params = ['userid' => $userid];

    // Get the records
    $records = $DB->get_records_select('local_equipment_user', $where, $params, '', 'id,phone');

    // Extract the phone value if a record is found
    $phone = !empty($records) ? reset($records)->phone : false;

    return $phone;
}

/**
 * Get all current equipment pickup methods for parents to select.
 * @return array An associative array of pickup methods.
 */
function local_equipment_get_pickup_methods() {
    return [
        'self' => get_string('pickupself', 'local_equipment'),
        'other' => get_string('pickupother', 'local_equipment'),
        'ship' => get_string('pickupship', 'local_equipment'),
        'purchased' => get_string('pickuppurchased', 'local_equipment')
    ];
}

/**
 * Gets all the phone providers that are available based on whether or not the API setting exist.
 * @param string $enabledonly Whether or not to get disabled SMS gateway providers in addition to the enabled providers.
 * @return array Array of phone providers.
 */
function local_equipment_get_sms_gateways($enabledonly = true) {
    global $DB;
    $gatewayoptions = [];

    if ($enabledonly) {
        $gateways = $DB->get_records('sms_gateways', ['enabled' => 1]);
    } else {
        $gateways = $DB->get_records('sms_gateways');
    }
    foreach ($gateways as $gateway) {
        $gatewayoptions[$gateway->id] = $gateway->name;
    }

    return $gatewayoptions;
}

// Move this to a separate function for better organization
function local_equipment_handle_aws_gateway($gatewayobj, $tonumber, $message, $messagetype) {
    global $SITE, $CFG;

    // echo '<pre>';
    // var_dump($gatewayobj);
    // echo '</pre>';
    // die();

    $responseobject = new stdClass();
    $responseobject->errormessage = '';
    $responseobject->errorobject = new stdClass();
    $responseobject->success = false;

    try {
        $awsconfig = json_decode($gatewayobj->config);
        $client = new Aws\Sns\SnsClient([
            'version' => 'latest',
            'region' => $awsconfig->api_region,
            'credentials' => [
                'key' => $awsconfig->api_key,
                'secret' => $awsconfig->api_secret,
            ]
        ]);

        // // Validate originator number
        // $originator = ($messagetype === 'OTP')
        //     ? get_config('local_equipment', 'awsotporiginatorphone')
        //     : get_config('local_equipment', 'awsinfooriginatorphone');

        // if (empty($originator)) {
        //     throw new moodle_exception('awsoriginatornotconfigured', 'local_equipment');
        // }

        $result = $client->publish([
            'Message' => $message,
            'PhoneNumber' => $tonumber,
            'MessageAttributes' => [
                'AWS.SNS.SMS.SenderID' => [
                    'DataType' => 'String',
                    'StringValue' => $SITE->shortname
                ],
                'AWS.SNS.SMS.SMSType' => [
                    'DataType' => 'String',
                    'StringValue' => $messagetype === 'OTP' ? 'Transactional' : 'Promotional'
                ]
            ]
        ]);

        if ($result['MessageId']) {
            $responseobject->success = true;
            $responseobject->messageid = $result['MessageId'];
        } else {
            throw new moodle_exception('awssmssendfailed', 'local_equipment');
        }
    } catch (Aws\Exception\AwsException $e) {
        $responseobject->errorobject->awserror = $e->getMessage();
        $responseobject->errormessage = get_string(
            'awssmsfailedwithcode',
            'local_equipment',
            $responseobject->errorobject
        );
    }

    return $responseobject;
}


/**
 * Sends an SMS message to a phone number via POST and HTTPS.
 *
 * @param string $gatewayid The ID of the gateway to use for sending the SMS message, found in the 'sms_gateways' table.
 * @param string $tophonenumber The FORMATTED phone number to send the SMS message to.
 * @param int $ttl Time to live (TTL) in seconds until the OTP expires.
 * @param bool $isatest Whether or not this is a test OTP or a real one.
 * @return object
 */
function local_equipment_send_secure_otp($gatewayid, $tophonenumber, $ttl = 600, $isatest = 0) {
    global $USER, $DB, $SESSION, $SITE;

    $responseobject = new stdClass();
    $responseobject->errorcode = -1;

    if ($isatest) {
        $verifyurl = new moodle_url('/local/equipment/phonecommunication/verifytestotp.php');
    } else {
        $verifyurl = new moodle_url('/local/equipment/phonecommunication/verifyotp.php');
    }

    try {

        $record = new stdClass();
        // Initialize $SESSION->otps.
        if (!isset($SESSION->otps)) {
            $SESSION->otps = new stdClass();
        }

        $otp = mt_rand(100000, 999999);


        // Test OTP
        // $testotp = 345844;
        $msgparams = ['otp' => $otp, 'site' => $SITE->shortname];
        $message = get_string('phoneverificationcodefor', 'local_equipment', $msgparams);
        $phone1obj = local_equipment_parse_phone_number($USER->phone1);
        $phone2obj = local_equipment_parse_phone_number($USER->phone2);

        $phone1 = $phone1obj->phone;
        $phone2 = $phone2obj->phone;

        // echo '<pre>';
        // var_dump($phone1);
        // echo '</pre>';
        // echo '<pre>';
        // var_dump($phone2);
        // echo '</pre>';
        // echo '<pre>';
        // var_dump($tophonenumber);
        // echo '</pre>';
        // echo '<pre>';
        // var_dump($tophonenumber === $phone2);
        // echo '</pre>';
        // die();
        if ($tophonenumber === $phone1) {
            $record->tophonename = 'phone1';
        } elseif ($tophonenumber === $phone2) {
            $record->tophonename = 'phone2';
        } else if ($phone2 != '') {
            $USER->phone2 = $tophonenumber;
            $record->tophonename = 'phone2';
            $DB->update_record('user', $USER);
        } else {
            throw new moodle_exception('phonefieldsdonotexist', 'local_equipment');
        }

        $sessionotpcount = 0;
        $dbotpcount = 0;
        $sessionasarray = get_object_vars($SESSION->otps);
        $sqlconditions = ['userid' => $USER->id];
        $otprecords = $DB->get_records('local_equipment_phonecommunication_otp', $sqlconditions);
        $recordexists = false;

        if (!empty($sessionasarray)) {
            // Prune bad $SESSION->otps records.
            foreach ($SESSION->otps as $key => $entry) {
                $sessionotpcount++;
                $expired = $entry->expires <= time();
                $verified = $entry->phoneisverified;
                if ($expired && !$verified) {
                    // Removing old session info.
                    unset($SESSION->otps->$key);
                    $sessionotpcount--;
                }
            }
        }

        // Prune bad $DB records.
        if (!empty($otprecords)) {
            foreach ($otprecords as $key => $entry) {
                $dbotpcount++;
                $expired = $entry->expires <= time();
                $verified = $entry->phoneisverified;
                if ($expired && !$verified) {
                    $DB->delete_records('local_equipment_phonecommunication_otp', ['id' => $entry->id]);
                    $dbotpcount--;
                } else {
                    // Override the session to match what's in the DB.
                    $SESSION->otps->{$entry->tophonename} = $entry;
                }
            }
        }

        $recordexists = isset($SESSION->otps->{$record->tophonename});
        if ($recordexists) {
            $isverified = $SESSION->otps->{$record->tophonename}->phoneisverified;
        }

        if ($isatest) {

            // Create new record.
            $record->userid = $USER->id;
            $record->otp = password_hash($otp, PASSWORD_DEFAULT);  // Hash the OTP.
            $record->tophonenumber = $tophonenumber;
            $record->phoneisverified = 0;
            $record->timecreated = time();
            $record->timeverified = null;
            $record->expires = $record->timecreated + $ttl;  // OTP expires after 10 minutes.

            $SESSION->otps->{$record->tophonename} = $record;
            $SESSION->otps->{$record->tophonename}->id = $DB->insert_record('local_equipment_phonecommunication_otp', $record);

            $msgparams = ['otp' => $otp, 'site' => $SITE->shortname];
            $message = get_string('phoneverificationcodefor', 'local_equipment', $msgparams);
            return local_equipment_send_sms($gatewayid, $tophonenumber, $message, 'OPT');
        }

        // At this point, we are guaranteed that there are as many records in the DB as there are in $SESSION->otps, and they hold
        // the same info, though formatted differently.
        if (!$recordexists && $dbotpcount < 2) {

            // Create new record.
            $record->userid = $USER->id;
            $record->otp = password_hash($otp, PASSWORD_DEFAULT);  // Hash the OTP.
            $record->tophonenumber = $tophonenumber;
            $record->phoneisverified = 0;
            $record->timecreated = time();
            $record->timeverified = null;
            $record->expires = $record->timecreated + $ttl;  // OTP expires after 10 minutes.

            $SESSION->otps->{$record->tophonename} = $record;
            $SESSION->otps->{$record->tophonename}->id = $DB->insert_record('local_equipment_phonecommunication_otp', $record);

            $msgparams = ['otp' => $otp, 'site' => $SITE->shortname];
            $message = get_string('phoneverificationcodefor', 'local_equipment', $msgparams);
            $responseobject = local_equipment_send_sms($gatewayid, $tophonenumber, $message, 'OPT');
        } elseif ($recordexists && $isverified) {
            $responseobject->errorcode = 0;
            throw new moodle_exception('phonealreadyverified', 'local_equipment');
        } elseif ($recordexists && !$isverified) {
            $responseobject->errorcode = 1;
            throw new moodle_exception('otpforthisnumberalreadyexists', 'local_equipment');
            throw new moodle_exception('wait10minutes', 'local_equipment');
        } else {
            $responseobject->errorcode = 2;
            throw new moodle_exception('somethingwentwrong', 'local_equipment');
        }
        $responseobject->verifyurl = $verifyurl;
    } catch (moodle_exception $e) {
        // Catch the exception and add it to the array
        $responseobject->verifyurl = $verifyurl;
        $responseobject->success = false;
        $responseobject->errormessage = $e->getMessage();
        if ($responseobject->errorcode == 1) {
            redirect(
                $verifyurl,
                $responseobject->errormessage,
                null,
                \core\output\notification::NOTIFY_WARNING
            );
        }
    }
    return $responseobject;
}

/**
 * Verifies an One-Time Password (OTP).
 *
 * @param string $otp The OTP to verify.
 * @return object
 */
function local_equipment_verify_otp($otp) {
    global $DB, $USER, $SESSION;

    $responseobject = new stdClass();

    if (!isset($SESSION->otps)) {
        $SESSION->otps = new stdClass();
    }

    $dbcount = 0;
    $sessioncount = 0;
    $verified = 0;

    try {
        $sqlconditions = [
            'userid' => $USER->id,
        ];
        $sessionrecords = $SESSION->otps;
        $sessionasarray = get_object_vars($sessionrecords);


        if (!empty($sessionasarray)) {
            $sessioncount = 0;
            // This means there are 1 or 2 records in $SESSION->otps.
            foreach ($sessionrecords as $key => $record) {
                $sessioncount++;
                $expired = $record->expires <= time();
                $verified = $record->phoneisverified;
                $matches = password_verify($otp, $record->otp);
                if (!$expired && !$verified && $matches) {
                    $record->timeverified = time();
                    $record->phoneisverified = 1;
                    $DB->update_record('local_equipment_phonecommunication_otp', $record);
                    $responseobject->success = true;
                    $responseobject->tophonenumber = $record->tophonenumber;
                    return $responseobject;
                }
            }
        }

        // The DB will not be access if a session record was alread found by this point because of the 'return' statement above.
        $dbrecords = $DB->get_records('local_equipment_phonecommunication_otp', $sqlconditions);

        if (!empty($dbrecords)) {
            // This means there are 1 or 2 records in $DB.
            foreach ($dbrecords as $key => $record) {
                $expired = $record->expires <= time();
                $verified = $record->phoneisverified;
                $matches = password_verify($otp, $record->otp);
                $responseobject->tophonenumber = $record->tophonenumber;
                if ($verified && $matches) {
                    $url = new moodle_url('/local/equipment/phonecommunication/testoutgoingtextconf.php');
                    $link = html_writer::link($url, get_string('testoutgoingtextconf', 'local_equipment'));
                    $responseobject->success = true;
                    $responseobject->successmessage = get_string('phonealreadyverified', 'local_equipment');
                    return $responseobject;
                    return $responseobject;
                }
                if (!$expired && !$verified && $matches) {
                    $record->timeverified = time();
                    $record->phoneisverified = 1;
                    $DB->update_record('local_equipment_phonecommunication_otp', $record);
                    $responseobject->success = true;
                    return $responseobject;
                }
            }
        }
        if ($sessioncount == 1 && $expired) {

            $url = new moodle_url('/local/equipment/phonecommunication/verifyphone.php');
            $link = html_writer::link($url, get_string('here', 'local_equipment'));

            throw new moodle_exception('otphasexpired', 'local_equipment', '', $link);
            // redirect(new moodle_url('/local/equipment/phonecommunication/verifyphone.php'), get_string('otphasexpired', 'local_equipment'), null, \core\output\notification::NOTIFY_WARNING);
        } else if (($sessioncount == 1 || $dbcount == 1) && $verified == 1) {
            throw new moodle_exception('nophonestoverify', 'local_equipment');
        } elseif ($sessioncount == 1 || $dbcount == 1) {
            throw new moodle_exception('otpdoesnotmatch', 'local_equipment');
        } elseif ($sessioncount > 0 && $dbcount > 0) {
            throw new moodle_exception('otpsdonotmatch', 'local_equipment');
        } else {
            $url = new moodle_url('/local/equipment/phonecommunication/testoutgoingtextconf.php');
            $link = html_writer::link($url, get_string('testoutgoingtextconf', 'local_equipment'));
            throw new moodle_exception('novalidotpsfound', 'local_equipment', '', $link);
        }
    } catch (moodle_exception $e) {
        // Catch the exception and add it to the array
        $responseobject->success = false;
        $responseobject->errormessage = $e->getMessage();
    }

    // OTP is valid and has not expired.
    return $responseobject;
}

/**
 * Combine the stringified array fields of all records that have the same 'userid' field from 'local_equipment_user' table.
 * @param string $userid The ID of the user.
 * @return object The combined user record as an stdClass object.
 */

function local_equipment_combine_user_records_by_userid($userid) {
    global $DB;
    $userrecord = new stdClass();
    $userrecord->errors = [];

    // These are the things being combined in this function.
    $studentids = [];
    $vccsubmissionids = [];
    // $userrecord->phoneverificationids = [];
    $existingrecords = $DB->get_records('local_equipment_user', ['userid' => $userid]);


    $count = 1;
    $userrecord->size = sizeof($existingrecords);
    if (!$existingrecords || sizeof($existingrecords) === 0) {
        $userrecord->errors[] = get_string('nousersfound', 'local_equipment');
    } else {
        foreach ($existingrecords as $record) {

            // These are arrays.
            $studentids = array_merge($studentids, json_decode($record->studentids));
            $vccsubmissionids = array_merge($vccsubmissionids, json_decode($record->vccsubmissionids));
            // The phone feature is not yet implemented, so we don't actually need the following line.
            // $userrecord->phoneverificationids = array_merge($userrecord->phoneverificationids, json_decode($record->phoneverificationids));

            // Get the ID of the most recently added record for this user.
            if ($count == $userrecord->size) {
                $userrecord->id = $record->id;
                $userrecord->studentids = json_encode(array_unique($studentids));
                $userrecord->vccsubmissionids = json_encode(array_unique($vccsubmissionids));
                $DB->update_record('local_equipment_user', $userrecord);
            } else {
                // Delete the record if there is more than one.
                // echo '<pre>';
                // var_dump("Deleting record with ID: $record->id");
                // echo '</pre>';
                $DB->delete_records('local_equipment_user', ['id' => $record->id]);
            }
            $count++;
        }
    }
    return $userrecord;
}

/**
 * Get all users who have a specific role assigned to them.
 * @param string $role The 'shortname' of the role to search for.
 * @return array An array of non-duplicate userids that have the specified role assigned to them.
 */

function local_equipment_get_users_by_role($role) {
    global $DB;
    // $context = context_system::instance();

    $roleid = $DB->get_field('role', 'id', ['shortname' => $role]);
    $userids = $DB->get_fieldset_select('role_assignments', 'userid', 'roleid = ?', [$roleid]);

    // Get the same list without duplicates.
    $users = $DB->get_records_list('user', 'id', $userids, '', 'id');
    return $users;
}

/**
 * Users who want to get a list of all their children, mentees, etc. should use this function.
 *
 * Get all the students, and their corresponding roles, of the currently logged user, such as this user's children or mentees, who
 * have the logged in user assigned to them through the 'Assign roles relative to this user' setting in Preferences.
 *
 * I.e. if a student has a role assigned to them, such as 'Mentor', with the current user listed on that assigned role, that student
 * of yours—your mentee in this case—will appear in this list along with all user info and the role that connects you to them.
 *
 * @param string $asrole The 'shortname' of the role for which you need to get students. E.g. 'parent', 'mentor'.
 * @param int $userid The ID of the user whose students you want to get.
 * @return object An object containing two arrays of equal length, each with a key: roles[] and users[].
 *
 * roles[] contains one matching role for each student of this user, but the role itself is actually the relative role the current
 * user has been assigned to by their students, such as a 'Parent' or 'Mentor' role id, as well as the context in which it's
 * assigned, and other relavent IDs.
 *
 * users[] contains one entry for each student student of the current user, filled with all student info from the core mdl_user
 * table
 *
 * Returns false if no students are found.
 */

function local_equipment_get_students_of_user_as($asrole, $userid) {
    // Get my students as a 'parent'; get them as a 'mentor'; etc.
    global $DB;
    $users = [];
    $usersinfo = new stdClass();
    $usersinfo->roles = [];
    $usersinfo->users = [];
    // This is the query to get the all the students/mentees of the currently logged in user.
    $sql = "SELECT role_assignments.id,roleid,userid,instanceid,contextid
        FROM {role_assignments} role_assignments
        JOIN {role} role ON role_assignments.roleid = role.id
        JOIN {context} context ON role_assignments.contextid = context.id
        WHERE role_assignments.userid = :userid
        AND role.shortname = :shortname;";
    $params = ['userid' => $userid, 'shortname' => $asrole];

    $rolesassignments = $DB->get_records_sql($sql, $params);

    // Now we can iterate through the role assignments to get the records of this user's students/mentees.
    $i = 0;
    foreach ($rolesassignments as $role) {
        $users[$role->instanceid] = $DB->get_record('user', ['id' => $role->instanceid]);
        $usersinfo->roles[$role->instanceid] = array_values($rolesassignments)[$i];
        $i++;
    }

    $usersinfo->users = $users;

    if (empty($usersinfo->users) || empty($usersinfo->roles)) {
        return false;
    }

    return $usersinfo;
}

/**
 * Users who want to get a list of all their parents, mentors, etc. should use this function.
 *
 * Get all the users—such as the students or mentees—who have the logged in user assigned to them through the 'Assign roles relative
 * to this user' setting in Preferences.
 *
 * @param string $role The 'shortname' of the role for which the logged in user needs to get their relative role assignments. If the
 * currently logged in user is a student who wants to see their parents or mentors, use 'parent' or 'mentor'.
 * @param int $userid The ID of the user whose assigned users you want to get. This should be the ID of the supposed student.
 * @return object An object containing two arrays of equal length, each with a key: roles[] and users[].
 *
 * roles[] contains one
 * matching role for each student of this user, but the role itself is actually the relative role the current user has been assigned
 * to by their students, such as a 'Parent' or 'Mentor' role id, as well as the context in which it's assigned, and other relavent
 * IDs.
 *
 * users[] contains one entry for each student student of the current user, filled with all student info from the core
 * mdl_user table
 *
 * Returns false if no one is assigned to this user via specified role.
 */

function local_equipment_get_users_assigned_to_user($role, $userid) {
    // i.e get my parents; get my mentors; etc.
    global $DB;
    $users = [];
    $roles = [];
    $usersinfo = new stdClass();
    // Get all the users of the specified role, regardless of the currently logged in user.
    $allusersofthisrole = local_equipment_get_users_by_role($role);

    $sql = "SELECT role_assignments.id,roleid,userid,instanceid,contextid
        FROM {role_assignments} role_assignments
        JOIN {role} role ON role_assignments.roleid = role.id
        JOIN {context} context ON role_assignments.contextid = context.id
        WHERE role_assignments.userid = :userid
        AND role.shortname = :shortname
        AND instanceid = :instanceid;";

    // This is how you get the all assigned users of the currently logged in user.
    foreach ($allusersofthisrole as $userrole) {
        $params = ['userid' => $userrole->id, 'shortname' => $role, 'instanceid' => $userid];
        $records = $DB->get_records_sql($sql, $params);
        if (!empty($records)) {
            $roles[$userrole->id] = array_values($records)[0];
        }
    }

    foreach ($roles as $r) {
        $users[$r->userid] = $DB->get_record('user', ['id' => $r->userid]);
    }
    $usersinfo->roles = $roles;
    $usersinfo->users = $users;

    if (empty($usersinfo->users) || empty($usersinfo->roles)) {
        return false;
    }

    return $usersinfo;
}

/**
 * Add a role relative to a given user, such as a parent or mentor role. If the role assignment already exists, displays a warning
 * but continues.
 *
 * @param object $user The user to whom the role will be assigned (e.g., student)
 * @param object $relativeuser The user who will be assigned the role (e.g., parent)
 * @param string $role The 'shortname' of the role to assign
 * @return stdClass returns an object of success, warning, and error messages.
 */
function local_equipment_assign_role_relative_to_user(object $user, object $relativeuser, string $role): stdClass {
    global $DB;

    $result = new stdClass();
    $result->successes = [];
    $result->warnings = [];
    $result->errors = [];

    try {
        // Get role ID and user context
        $roleid = $DB->get_field('role', 'id', ['shortname' => $role]);
        $context = context_user::instance($user->id);

        // Check if assignment already exists.
        $existing = $DB->record_exists('role_assignments', [
            'roleid' => $roleid,
            'contextid' => $context->id,
            'userid' => $relativeuser->id
        ]);

        if (!$existing) {
            role_assign($roleid, $relativeuser->id, $context->id, 'local_equipment');
            $result->successes[] = get_string(
                'userassignedtootheruserwithrole',
                'local_equipment',
                (object)[
                    'parent' => fullname($relativeuser),
                    'student' => fullname($user),
                    'role' => $role
                ]
            );
        } else {
            $result->warnings[] = get_string(
                'rolealreadyassigned',
                'local_equipment',
                (object)[
                    'parent' => fullname($relativeuser),
                    'student' => fullname($user),
                    'role' => $role
                ]
            );
        }
        return $result;
    } catch (moodle_exception $e) {
        // debugging($e->getMessage(), DEBUG_DEVELOPER);
        $result->errors[] = $e->getMessage();
        return $result;
    }
}

/**
 * Get all parents assigned to a specific user (student).
 *
 * @param int $studentid The ID of the student whose parents we want to find
 * @return array Array of parent user objects, or empty array if none found
 */
function local_equipment_get_parents_of_student(int $studentid): array {
    global $DB;

    // Get the parent role ID
    $parentrole = $DB->get_record('role', ['shortname' => 'parent'], '*', MUST_EXIST);

    // Get user context for the student
    $usercontext = context_user::instance($studentid);

    // Get all users assigned as parents in this context
    $sql = "SELECT u.*
            FROM {role_assignments} ra
            JOIN {user} u ON ra.userid = u.id
            WHERE ra.contextid = :contextid
            AND ra.roleid = :roleid
            AND u.deleted = 0";

    $params = [
        'contextid' => $usercontext->id,
        'roleid' => $parentrole->id
    ];

    return $DB->get_records_sql($sql, $params);
}

/**
 * Get all students for whom a specific user is assigned as parent.
 *
 * @param int $parentid The ID of the parent whose students we want to find
 * @return array Array of student user objects, or empty array if none found
 */
function local_equipment_get_students_of_parent(int $parentid): array {
    global $DB;

    // Get the parent role ID
    $parentrole = $DB->get_record('role', ['shortname' => 'parent'], '*', MUST_EXIST);

    // This query finds all users (students) where the given parent has a parent role in their user context
    $sql = "SELECT u.*
            FROM {role_assignments} ra
            JOIN {context} ctx ON ra.contextid = ctx.id
            JOIN {user} u ON ctx.instanceid = u.id
            WHERE ra.userid = :parentid
            AND ra.roleid = :roleid
            AND ctx.contextlevel = :contextlevel
            AND u.deleted = 0";

    $params = [
        'parentid' => $parentid,
        'roleid' => $parentrole->id,
        'contextlevel' => CONTEXT_USER
    ];

    return $DB->get_records_sql($sql, $params);
}

/**
 * Check if a user is a parent of a specific student.
 *
 * @param int $parentid The ID of the potential parent
 * @param int $studentid The ID of the student
 * @return bool True if parent relationship exists, false otherwise
 */
function local_equipment_is_parent_of_student(int $parentid, int $studentid): bool {
    global $DB;

    // Get the parent role ID
    $parentrole = $DB->get_record('role', ['shortname' => 'parent'], '*', MUST_EXIST);

    // Get user context for the student
    $usercontext = context_user::instance($studentid);

    return $DB->record_exists('role_assignments', [
        'roleid' => $parentrole->id,
        'contextid' => $usercontext->id,
        'userid' => $parentid
    ]);
}

/**
 * Create a username, programitcally:
 * first initial
 * + last name (default to primary last name)
 * + the amount of LIKE usernames in the mdl_user table +1
 *
 * E.g. John Doe -> jdoe1
 *      John Doe -> jdoe2
 *      John Doe -> jdoe3
 *
 * E.g. Jane G Harmon-Quest -> jharmon1
 *      Jane G Harmon-Quest -> jharmon2
 *      Jane G Harmon-Quest -> jharmon3
 *
 * E.g. Jord A O'neil -> joneil1
 *      Jord A O'neil -> joneil2
 *      Jord A O'neil -> joneil3
 *
 * E.g. Ümit Ñet -> unet1
 * E.g. Üma ÑetøéÒLë -> unetoeole1
 *
 * @param stdClass $user The user object.
 * @return string The generated username.
 */
function local_equipment_generate_username($user) {
    global $DB;

    // $firstname = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $user->firstname);
    // $lastname = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $user->lastname);
    // First normalize to decomposed form (separate letters from diacritics)
    $firstname = Normalizer::normalize($user->firstname, Normalizer::FORM_D);
    $lastname = Normalizer::normalize($user->lastname, Normalizer::FORM_D);

    // Then remove non-ASCII characters
    $firstname = preg_replace('/[^\x20-\x7E]/u', '', $firstname);
    $lastname = preg_replace('/[^\x20-\x7E]/u', '', $lastname);

    // Fallback in case name becomes empty after processing
    if (empty($firstname)) {
        $firstname = '';
    }
    if (empty($lastname)) {
        $lastname = 'user';
    }

    $firstpart = strtolower($firstname)[0] ?? '';
    $secondpart = strtolower($lastname);
    $secondpart = str_replace("'", '', $secondpart);
    $secondpart = explode('-', $secondpart)[0];

    $username = $firstpart . $secondpart;

    $likeusernames = $DB->count_records_select('user', 'username LIKE ?', [$username . '%']);
    $appendingnumber = $likeusernames + 1;

    // Let's say there are usernames: jdoe2, jdoe3, jdoe4 (missing 'jdoe1').  The variables $likeusernames above would be 3,
    // $appendingnumber would be 4, and $username . $appendingnumber would be 'jdoe4', which already exists, so we need to find the
    // missing 'jdoe' username and make that the output username instead.  The statement below will fill in the missing 'jdoe'
    // username and prevent the same issue from happening in the future.  Ideally, this statment will never run, but you never know
    // what the "LIKE" query will return.

    if ($DB->record_exists('user', ['username' => $username . $appendingnumber])) {
        for ($i = 1; $i < $appendingnumber; $i++) {
            if (!$DB->record_exists('user', ['username' => $username . $i])) {
                return $username .= $i;
            }
        }
    }

    return $username .= $appendingnumber;
}

/**
 * Assign parent roles to parents for their students
 *
 * @param array $family Array containing parent and student users
 * @return array Results of role assignments
 */
function local_equipment_assign_family_roles($family) {
    global $DB;

    $results = [
        'success' => [],
        'errors' => []
    ];

    // Get the parent role ID
    $parentrole = $DB->get_record('role', ['shortname' => 'parent'], 'id');
    if (!$parentrole) {
        throw new moodle_exception('parentrolenotfound', 'local_equipment');
    }

    // For each student in the family
    foreach ($family['students'] as $student) {
        // Get the user context for this student
        $studentcontext = context_user::instance($student->id);

        // Assign each parent to this student
        foreach ($family['parents'] as $parent) {
            try {
                // Check if role assignment already exists
                $existing = $DB->get_record('role_assignments', [
                    'roleid' => $parentrole->id,
                    'contextid' => $studentcontext->id,
                    'userid' => $parent->id
                ]);

                if (!$existing) {
                    role_assign(
                        $parentrole->id,
                        $parent->id,
                        $studentcontext->id,
                        'local_equipment'  // Component string
                    );

                    $results['success'][] = [
                        'parent' => $parent->id,
                        'student' => $student->id,
                        'role' => $parentrole->id
                    ];

                    // Trigger an event for the role assignment
                    \core\event\role_assigned::create([
                        'context' => $studentcontext,
                        'objectid' => $parentrole->id,
                        'relateduserid' => $student->id,
                        'other' => ['roleId' => $parentrole->id]
                    ])->trigger();
                }
            } catch (moodle_exception $e) {
                $results['errors'][] = [
                    'parent' => $parent->id,
                    'student' => $student->id,
                    'error' => $e->getMessage()
                ];
                // debugging('Error assigning role: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }
    }

    return $results;
}

/**
 * Process role assignments for multiple families
 *
 * @param array $families Array of processed family data
 * @return array Results of all role assignments
 */
function local_equipment_process_family_roles($families) {
    $all_results = [
        'success' => [],
        'errors' => []
    ];

    foreach ($families as $family) {
        try {
            $result = local_equipment_assign_family_roles($family);
            $all_results['success'] = array_merge($all_results['success'], $result['success']);
            $all_results['errors'] = array_merge($all_results['errors'], $result['errors']);
        } catch (moodle_exception $e) {
            $all_results['errors'][] = [
                'family_error' => $e->getMessage()
            ];
        }
    }

    return $all_results;
}

/**
 * Helper function to enroll a user in a course using manual enrollment method.
 * This function serves as the main entry point for course enrollments in the equipment plugin.
 * It handles all the necessary validation and delegates the actual enrollment to specialized classes.
 *
 * @param stdClass $user The user object to enroll
 * @param int $courseid The ID of the course to enroll into
 * @param int|null $roleid The ID of the role to assign (default student role if not specified)
 * @param int $timestart Timestamp when the enrollment should start (optional)
 * @param int $timeend Timestamp when the enrollment should end (optional)
 * @return stdClass Object containing success/warning/error messages and course name
 */
function local_equipment_enrol_user_in_course(
    stdClass $user,
    int $courseid,
    ?int $roleid = null,
    int $timestart = 0,
    int $timeend = 0
): stdClass {
    global $DB, $SESSION;

    // Initialize result object to store all our messages
    $result = new stdClass();
    $result->successes = [];
    $result->warnings = [];
    $result->errors = [];
    $result->coursename = '';

    // Validate parameters.
    if ($user->id <= 0 || $courseid <= 0) {
        throw new coding_exception('Invalid user or course ID provided');
    }

    // Get course and context - we need these for various checks
    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    $coursecontext = \context_course::instance($courseid);

    // Store course name for messages
    $result->coursename = $course->fullname;

    // Verify permissions
    require_capability('enrol/manual:enrol', $coursecontext);

    // Create message object for string parameters
    $msg = new stdClass();
    $msg->firstname = $user->firstname;
    $msg->lastname = $user->lastname;
    $msg->coursename = $result->coursename;

    // Verify current user has capability to enrol users.
    require_capability('enrol/manual:enrol', $coursecontext);

    try {
        // Get the manual enrolment plugin.
        $instance = $DB->get_record('enrol', [
            'courseid' => $courseid,
            'enrol' => 'manual',
            'status' => ENROL_INSTANCE_ENABLED
        ], '*', MUST_EXIST);

        $enrolplugin = enrol_get_plugin($instance->enrol);
        if (empty($enrolplugin)) {
            $result->errors[] = get_string('manualpluginnotinstalled', 'enrol_manual');
            return $result;
        }

        // Get course enrolment instance.

        // Turn off email notification for this enrolment without changing the user's preferences. We'll still send custom emails
        // using this plugin, though.
        $customint1_old = $instance->customint1;
        $instance->customint1 = ENROL_DO_NOT_SEND_EMAIL;

        // echo '<pre>';
        // var_dump('$instance->customint1 after from local_equipment: ');
        // var_dump($instance->customint1);
        // echo '</pre>';


        // If no role specified, get the default student role.
        if (is_null($roleid)) {
            $studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
            $roleid = $studentrole->id;
        }

        // Check if user is already enrolled.
        if (!is_enrolled($coursecontext, $user->id)) {
            // Set up enrollment parameters.
            $timestart = ($timestart > 0) ? $timestart : time();
            $timeend = ($timeend > 0) ? $timeend : 0;

            // Enrol the user.
            $enrolplugin->enrol_user(
                $instance,
                $user->id,
                $roleid,
                $timestart,
                $timeend,
                ENROL_USER_ACTIVE
            );

            $result->successes[] = get_string('userenrolled', 'local_equipment', $msg);
        } else {
            $result->warnings[] = get_string(
                'useralreadyenrolled',
                'local_equipment',
                $msg
            );
        }

        return $result;
    } catch (moodle_exception $e) {
        // debugging($e->getMessage(), DEBUG_DEVELOPER);
        $result->errors[] = $e->getMessage();
        return $result;
    }
}

/**
 * Extends the navigation by adding an "Equipment" item to the primary navigation.
 *
 * @param global_navigation $navigation
 */
function local_equipment_extend_navigation(global_navigation $navigation) {
    global $PAGE, $CFG;

    // Only add this for users who can see it
    if (!has_capability('local/equipment:manageequipment', context_system::instance())) {
        return;
    }

    $node = navigation_node::create(
        get_string('equipmentmanagement', 'local_equipment'),
        new moodle_url('/local/equipment/management.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'equipmentmanagement',
        new pix_icon('i/settings', '')
    );

    $node->showinflatnavigation = true;

    // Add to the primary navigation
    $navigation->add_node($node);
}

/**
 * Bulk enroll a student in multiple courses.
 *
 * @param stdClass $user The user object to enroll
 * @param array $courseids Array of course IDs
 * @return array Array of enrollment results
 */
function local_equipment_bulk_enrol_student(stdClass $user, array $courseids): array {
    $results = [
        'success' => [],
        'failed' => [],
        'messages' => []
    ];

    foreach ($courseids as $courseid) {
        $enrollResult = local_equipment_enrol_user_in_course($user, $courseid);

        if ($enrollResult['success']) {
            $results['success'][] = $courseid;
        } else {
            $results['failed'][] = $courseid;
            $results['messages'][] = $enrollResult['message'];
        }
    }

    return $results;
}


/**
 * Send welcome emails to newly enrolled users.
 *
 * @param stdClass $user The user object containing id, firstname, lastname, email
 * @param array $coursenames An array of course names with their IDs as the keys
 * @param string $roletype The role shortname ('student' or 'parent')
 * @param string|null $partnershipid The partnership ID (optional)
 * @param array|null $usersofuser An array of user first/last names relative to the user with their IDs as the keys, i.e. array of students.
 * @param bool $notifyuser Whether to send notification (from plugin settings)
 * @return object Object containing success/error information
 */
function local_equipment_send_enrollment_message($user, $coursenames, $roletype = 'generic', $partnershipid = null, $usersofuser = null, $notifyuser = true) {
    global $SITE, $DB;

    $result = new stdClass();
    $result->success = false;
    $result->successes = [];
    $result->warnings = [];
    $result->errors = [];

    $name = $user->firstname;
    if ($user->lastname !== '') {
        $name .= ' ' . $user->lastname;
    }
    $name = html_writer::link(
        new moodle_url('/user/profile.php', ['id' => $user->id]),
        $name
    );

    if (empty($coursenames)) {
        $result->warnings[] = get_string('notsendingemailtouser_nocourses', 'local_equipment', $name);
        return $result;
    }

    // Site name, parent name, student name(s), course name(s),
    $partnership = $DB->get_field('local_equipment_partnership', 'name', ['id' => $partnershipid]);

    if ($usersofuser) {
        $usersofuser = local_equipment_convert_list_string($usersofuser);
    }
    $messageinput = new stdClass();
    $messageinput->user = $name;
    $messageinput->email = $user->email;
    $messageinput->sitename = $SITE->shortname;
    $messageinput->partnership = $partnership;
    $messageinput->students = $usersofuser;
    $messageinput->courses = implode('<br />', $coursenames);
    $messageinput->courses_comma = implode(', ', $coursenames);
    $messageinput->schoolyear = local_equipment_get_school_year();

    if (!$notifyuser) {
        return $result;
    }

    // Get message sender configuration
    $messagesender = get_config('local_equipment', 'messagesender');

    // Determine the from user based on settings
    switch ($messagesender) {
        // case ENROL_SEND_EMAIL_FROM_COURSE_CONTACT:
        //     $contactuser = local_equipment_get_course_contact($course);
        //     break;
        case ENROL_SEND_EMAIL_FROM_KEY_HOLDER:
            $contactuser = get_admin();
            break;
        default:
            $noreplyuser = core_user::get_noreply_user();
            $contactuser = $noreplyuser;
    }

    $addpartnershipstring = ($partnershipid && $roletype === 'parent') ? '_partnership' : '';
    $stringkey = $roletype . 'enrollmessage' . $addpartnershipstring;

    $message =  get_string('welcomemessage_user', 'local_equipment', $messageinput);
    $message .= '<br /><br />';
    $message .= get_string($stringkey, 'local_equipment', $messageinput);

    // Get subject and message templates based on role
    if ($roletype === 'student') {
        $subject = get_string('studentwelcomesubject', 'local_equipment', $messageinput);
    } else if ($roletype === 'parent') {
        $subject = get_string('parentwelcomesubject', 'local_equipment', $messageinput);
    } else {
        $subject = get_string('genericwelcomesubject', 'local_equipment', $messageinput);
    }

    // Send the email
    try {
        $success = email_to_user(
            $user,                  // To user
            $contactuser,           // From user
            $subject,
            html_to_text($message), // Plain text version
            $message                // HTML version
        );

        if ($success) {
            $result->success = true;
            $result->successes[] = get_string(
                'enrollmentemailsenttouserforcourses',
                'local_equipment',
                $messageinput
            );
        } else {
            $result->errors[] = get_string(
                'enrollmentemailnotsenttouserforcourses',
                'local_equipment',
                $messageinput
            );
        }
    } catch (moodle_exception $e) {
        $result->errors[] = $e->getMessage();
    }

    return $result;
}

/**
 * Get the primary contact user for a course.
 *
 * @param array $list A list of text to be turned into a written list.
 * @return string The contact user object
 */
function local_equipment_convert_list_string($list) {
    global $USER;

    if (sizeof($list) <= 1) {
        return $list[0];
    }

    switch ($USER->lang) {
        case 'en':
        case 'en_us':
        case 'es':
            $list[sizeof($list) - 1] = get_string('and', 'local_equipment') . ' ' . $list[sizeof($list) - 1];
            $string = implode(', ', $list);
            break;
        default:
            $list[sizeof($list) - 1] = get_string('and', 'local_equipment') . ' ' . $list[sizeof($list) - 1];
            $string = implode(', ', $list);
    }
    return $string;

    // Fallback to admin user if no course contacts found
}

/**
 * Get the primary contact user for a course.
 *
 * @param stdClass $course The course object
 * @return stdClass The contact user object
 */
function local_equipment_get_course_contact($course) {
    global $DB;

    // Try to get the primary course contact
    $context = context_course::instance($course->id);
    $teachers = get_enrolled_users($context, 'moodle/course:update');

    if (!empty($teachers)) {
        return reset($teachers); // Return first teacher
    }

    // Fallback to admin user if no course contacts found
    return get_admin();
}

/**
 * Generate HTML for family processing notifications.
 *
 * @param string $familyname The name of the family
 * @param stdClass $messages Object containing success, warning, and error messages
 * @param string $status Overall status (success, warning, or error)
 * @return string HTML for the notification
 */
function local_equipment_generate_family_notification(string $familyname, stdClass $messages, string $status): string {
    global $OUTPUT;

    $notificationid = html_writer::random_id('family_notification_');

    $data = new stdClass();
    $data->familyname = $familyname;

    // Get notification title based on status.
    switch ($status) {
        case 'success':
            $title = get_string('familyaddedsuccessfully', 'local_equipment', $familyname);
            $alert = 'alert-success';
            break;
        case 'warning':
            $title = get_string('familyaddedwithwarnings', 'local_equipment', $familyname);
            $alert = 'alert-warning';
            break;
        case 'error':
            $title = get_string('familyaddedwitherrors', 'local_equipment', $familyname);
            $alert = 'alert-danger';
            break;
        default:
            $title = get_string('familyprocessingresults', 'local_equipment', $familyname);
    }

    $html = html_writer::start_div('local-equipment-family-notification mb-4 ' . $alert);

    // Header attributes.
    $headerattrs = [
        'class' => "local-equipment-notification-header $status d-flex justify-content-between align-items-center p-3 rounded",
        'data-toggle' => 'collapse',
        'href' => "#$notificationid",
        'role' => 'button',
        'aria-expanded' => 'false',
        'aria-controls' => $notificationid
    ];

    // Build header.
    $html .= html_writer::start_div('', $headerattrs);
    $html .= html_writer::tag('h4', $title, ['class' => 'mb-0 local-equipment-notification-title']);
    $html .= html_writer::div(
        html_writer::tag('i', '', ['class' => 'fa fa-chevron-right']),
        'icon-container'
    );
    $html .= html_writer::end_div();

    // Build content.
    $html .= html_writer::start_div('collapse local-equipment-notification-content', ['id' => $notificationid]);
    $html .= html_writer::start_div('content-wrapper p-3 border-left border-right border-bottom rounded-bottom');

    // Add messages.
    foreach (['successes', 'warnings', 'errors'] as $type) {
        if (!empty($messages->$type)) {
            foreach ($messages->$type as $message) {
                switch ($type) {
                    case 'successes':
                        $notification = $OUTPUT->notification($message, 'success');
                        break;
                    case 'warnings':
                        $notification = $OUTPUT->notification($message, 'warning');
                        break;
                    case 'errors':
                        $notification = $OUTPUT->notification($message, 'error');
                        break;
                }
                $html .= html_writer::div($notification);
            }
        }
    }

    $html .= html_writer::end_div(); // content-wrapper
    $html .= html_writer::end_div(); // notification-content
    $html .= html_writer::end_div(); // local-equipment-family-notification

    return $html;
}

// /**
//  * Callback function to handle welcome message sending.
//  *
//  * @return array Callback configuration
//  */
// function local_equipment_enrol_manual_welcome_message_callback() {
//     return [
//         'callback' => '\local_equipment\manual_enrol_callback::before_welcome_message',
//         'priority' => 9999
//     ];
// }
