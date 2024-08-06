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
                'WY' => get_string('WY', 'local_equipment')
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
 * Convert all string values in an array to integers.
 * These values are assumed to be numeric IDs, so they can be cast to integers properly.
 * @param array $ids An associative array or or multitude of arrays containing numeric IDs of type string.
 * @return array An array of countries.
 */
function local_equipment_convert_array_values_to_int($ids) {
    foreach ($ids as $key => $value) {
        if (is_array($value)) {
            $ids[$key] = local_equipment_convert_array_values_to_int($value);
        } elseif (is_string($value) && is_numeric($value)) {
            $ids[$key] = (int)$value;
        }
    }

    return $ids;
}

/**
 * Check if a language string exists in current component.
 *
 * @param string $identifier The string identifier.
 * @param string $component The component name.
 * @return bool True if the string exists, false otherwise.
 */
function local_equipment_lang_string_exists($identifier) {
    global $CFG;
    echo '<br />';
    echo '<br />';
    echo '<br />';
    echo '<pre>';
    $langfile = file_get_contents(__DIR__ . '/lang/en/local_equipment.php');
    $string = '$string[\'' . $identifier . '\']';
    $pattern = '/^' . preg_quote($string, '/') . '.*$/m';
    $matches = [];
    var_dump(preg_match_all($pattern, $identifier, $matches));
    var_dump($matches);
    echo '</pre>';
    die();
    if (file_exists($langfile)) {
        $strings = include($langfile);
        return array_key_exists($identifier, $strings);
    }
    return false;
}

/**
 * Get all relevant addresses from a partnerships database record.
 *
 * @param stdClass $$dbrecord A database record that contains multiple types of addresses.
 * @return string Each addresses type, plus the actual address, joined by a <br /> tag.
 */
function local_equipment_get_addresses($dbrecord) {
    $address = [];
    $addresstypes = [
        'physical',
        'mailing',
        'pickup',
        'billing'
    ];
    foreach ($addresstypes as $type) {
        if ("{$dbrecord->{"streetaddress_$type"}}") {
            $address[] = html_writer::tag('strong', s(get_string($type, 'local_equipment'))) . ": {$dbrecord->{"streetaddress_$type"}}, {$dbrecord->{"city_$type"}}, {$dbrecord->{"state_$type"}} {$dbrecord->{"zipcode_$type"}} {$dbrecord->{"country_$type"}}";
        }
    }


    return implode('<br />', $address);
}

/**
 * Get all liaison names, emails, and contact phones for a given partnership.
 * Liaisons are simply users on the system, so they must of accounts.
 * Emails and phones will be taken from the user's profile, but admins will have the option to add phone numbers for them if they don't do it themselves.
 *
 * @param stdClass $$dbrecord A database record that contains multiple types of addresses.
 * @return string True if the string exists, false otherwise.
 */
function local_equipment_get_liaison_info($partnership) {
    // $liaisons = user_get_users_by_id(json_decode($partnership->liaisonids));
    $liaisonids = json_decode($partnership->liaisonids);

    foreach ($liaisonids as $id) {
        $user = core_user::get_user($id);
        $userurl = new moodle_url('/user/profile.php', array('id' => $user->id));
        $userlink = html_writer::link($userurl, fullname($user));

        $phone = '';
        // These if/elseif statements seem inefficient, but I wanted to add a <br /> tag only if a phone number exists, so if you can find a better way, feel free.
        if ($user->phone1) {
            $phone = local_equipment_parse_phone_number($user->phone1);
            $phone = local_equipment_format_phone_number($phone);
            $phone .= ' <br />';
        } elseif ($user->phone2) {
            $phone = local_equipment_parse_phone_number($user->phone2);
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
 * Get all liaison names, emails, and contact phones for a given partnership.
 * Liaisons are simply users on the system, so they must of accounts.
 * Emails and phones will be taken from the user's profile, but admins will have the option to add phone numbers for them if they don't do it themselves.
 *
 * @param stdClass $$dbrecord A database record that contains multiple types of addresses.
 * @return string True if the string exists, false otherwise.
 */
function local_equipment_get_courses($partnership) {
    $courseids = json_decode($partnership->courseids);
    $courseinfo = [];

    // echo '<pre>';
    // var_dump($courses);
    // echo '</pre>';
    // die();
    foreach ($courseids as $id) {
        $course = get_course($id);
        $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
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
 * @return string
 */
function local_equipment_parse_phone_number($phonenumber, $country = 'US') {
    // Remove commonly used characters from the phone number that are not numbers: ().-+ and the white space char.
    $parsedphonenumber = preg_replace("/[\(\)\-\s+\.]/", "", $phonenumber);

    try {
        if (!ctype_digit($parsedphonenumber)) {
            throw new \Exception(get_string('invalidphonenumberformat', 'local_equipment') . get_string('wecurrentlyonlyacceptusnumbers', 'local_equipment'));
        }
    } catch (\Exception $e) {
        return $e->getMessage();
    }

    switch ($country) {
        case 'US':
            // Check if the number is not empty, if it only contains digits, and if it is a valid 10 or 11 digit United States phone number.
            try {
                if ((strlen($parsedphonenumber) == 10) && $phonenumber[0] != 1) {
                    $parsedphonenumber = "+1" . $parsedphonenumber;
                } elseif ((strlen($parsedphonenumber) == 11) && $phonenumber[0] == 1) {
                    $parsedphonenumber = "+" . $parsedphonenumber;
                } else {
                    throw new \Exception(new lang_string('invalidphonenumber', 'local_equipment') . new lang_string('wecurrentlyonlyacceptusnumbers', 'local_equipment'));
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            break;
        default:
            return new lang_string('notasupportedcountry', 'local_equipment', $country);
    }
    return $parsedphonenumber;
}

/**
 * Validates a cell phone number to make sure it makes sense.
 *
 * @param string $parsedphonenumber The already parsed phone number. This must follow the exact form as follows: +12345678910
 * @return string
 */
function local_equipment_format_phone_number($parsedphonenumber) {
    $formattedphonenumber = preg_replace("/^\+(\d{1})(\d{3})(\d{3})(\d{4})$/", "+$1 ($2) $3-$4", $parsedphonenumber);
    try {
        if ($parsedphonenumber == $formattedphonenumber) {
            throw new \Exception(get_string('invalidphonenumberformat', 'local_equipment') . get_string('wecurrentlyonlyacceptusphonenumbers', 'local_equipment'));
        }
    } catch (\Exception $e) {
        return $e->getMessage();
    }

    return $formattedphonenumber;
}
