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
 * Convert all string values in an array to integers.
 * These values are assumed to be numeric IDs, so they can be cast to integers properly.
 * @param array $ids An associative array or or multitude of arrays containing numeric IDs of type string.
 * @return array An array of countries.
 */
function local_equipment_convert_array_values_to_int($ids) {
    if (is_array($ids)) {
        foreach ($ids as $key => $value) {
            if (is_array($value)) {
                $ids[$key] = local_equipment_convert_array_values_to_int($value);
            } else if (is_string($value) && is_numeric($value)) {
                $ids[$key] = (int)$value;
            }
        }
    } else if (is_object($ids)) {
        foreach ($ids as $key => $value) {
            if (is_array($value)) {
                $ids->$key = local_equipment_convert_array_values_to_int($value);
            } else if (is_string($value) && is_numeric($value)) {
                $ids->$key = (int)$value;
            }
        }
    }

    return $ids;
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
            $phone = local_equipment_parse_phone_number($user->phone1);
            $phone = local_equipment_format_phone_number($phone);
            $phone .= ' <br />';
        } else if ($user->phone2) {
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
        $phone = local_equipment_parse_phone_number($user->phone1);
        $phone = local_equipment_format_phone_number($phone);
        $phone .= ' <br />';
    } else if ($user->phone2) {
        $phone = local_equipment_parse_phone_number($user->phone2);
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
 * @return string True if the string exists, false otherwise.
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
 * @return string
 */
function local_equipment_parse_phone_number($phonenumber, $country = 'US') {
    // Remove commonly used characters from the phone number that are not numbers: ().-+ and the white space char.
    $parsedphonenumber = preg_replace("/[\(\)\-\s+\.]/", "", $phonenumber);

    try {
        if (!ctype_digit($parsedphonenumber)) {
            throw new \Exception(get_string('invalidphonenumberformat', 'local_equipment') . get_string('wecurrentlyonlyacceptusphonenumbers', 'local_equipment'));
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
                } else if ((strlen($parsedphonenumber) == 11) && $phonenumber[0] == 1) {
                    $parsedphonenumber = "+" . $parsedphonenumber;
                } else {
                    throw new \Exception(new lang_string('invalidphonenumber', 'local_equipment') . ' ' . new lang_string('wecurrentlyonlyacceptusphonenumbers', 'local_equipment'));
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


    // function format_address_block($addressinput) {

    //     $mform->createElement('html', '<div class="col-md-6">');
    //     $mform->createElement(
    //         'static',
    //         $addresstype . '_' . $addressinput,
    //         '',
    //         html_writer::div(get_string('streetaddress', 'local_equipment'), 'local-equipment-pickups-addpickups-time-selectors')
    //     );
    //     $mform->createElement('text', $addresstype . '_' . $addressinput, get_string($addresstype . '_' . $addressinput, 'local_equipment'));
    //     $mform->createElement('html', '</div>');
    // }
    // // $starttag = $mform->createElement('html', '<div class="col-md-6">');

    // $elements = [
    //     $mform->createElement('html', '<div class="form-group row">'),
    //     format_address_block('streetaddress'),
    //     format_address_block('apartment'),
    //     format_address_block('city'),
    //     format_address_block('state'),
    //     format_address_block('country'),
    //     format_address_block('zipcode'),

    //     // $mform->createElement('text', 'streetaddress_' . $addresstype, get_string('streetaddress_' . $addresstype, 'local_equipment')),
    //     // $mform->createElement('text', 'apartment_' . $addresstype, get_string('apartment_' . $addresstype, 'local_equipment')),
    //     // $mform->createElement('text', 'city_' . $addresstype, get_string('city_' . $addresstype, 'local_equipment')),
    //     // $mform->createElement('select', 'state_' . $addresstype, get_string('state_' . $addresstype, 'local_equipment'), local_equipment_get_states()),
    //     // $mform->createElement('select', 'country_' . $addresstype, get_string('country_' . $addresstype, 'local_equipment'), local_equipment_get_countries()),
    //     // $mform->createElement('text', 'zipcode_' . $addresstype, get_string('zipcode_' . $addresstype, 'local_equipment')),
    //     $mform->createElement('html', '</div>'),
    // ];
    // $mform->createElement('text', 'streetaddress_' . $addresstype, get_string('streetaddress', 'local_equipment'));
    // $mform->createElement('text', 'apartment_' . $addresstype, get_string('apartment', 'local_equipment'));
    // $mform->createElement('text', 'city_' . $addresstype, get_string('city', 'local_equipment'));
    // $mform->createElement('select', 'state_' . $addresstype, get_string('state', 'local_equipment'), local_equipment_get_states());
    // $mform->createElement('select', 'country_' . $addresstype, get_string('country', 'local_equipment'), local_equipment_get_countries());




    // $mform->createElement('text', 'zipcode_' . $addresstype, get_string('zipcode', 'local_equipment'));
    // $mform->setType('streetaddress_' . $addresstype, PARAM_TEXT);
    // $mform->setType('city_' . $addresstype, PARAM_TEXT);
    // $mform->setType('state_' . $addresstype, PARAM_TEXT);
    // $mform->setType('country_' . $addresstype, PARAM_TEXT);
    // $mform->setType('zipcode_' . $addresstype, PARAM_TEXT);

    // if (false) {
    //     $mform->setDefault('streetaddress_' . $addresstype, $data->{"{$addresstype}_streetaddress"});
    //     $mform->setDefault('city_' . $addresstype, $data->{"{$addresstype}_city"});
    //     $mform->setDefault('state_' . $addresstype, $data->{"{$addresstype}_state"});
    //     $mform->setDefault('country_' . $addresstype, $data->{"{$addresstype}_country"});
    //     $mform->setDefault('zipcode_' . $addresstype, $data->{"{$addresstype}_zipcode"});
    // }

    // Set the default starting hour and minute if it exists.
    // if ($defaulttime) {
    //     $mform->setDefault($addresstype . 'hour', date('H', $defaulttime));
    //     $mform->setDefault($addresstype . 'minute', date('i', $defaulttime));
    // }
    // Set the default ending hour and minute if it exists.

    // $elements = array(
    //     $mform->createElement('html', '<div class="form-group row">'),
    //     $mform->createElement('html', '<div class="col-md-6">'),
    //     $mform->createElement(
    //         'static',
    //         $addresstype . '_hourlabel',
    //         '',
    //         html_writer::div(get_string('hour', 'local_equipment'), 'local-equipment-pickups-addpickups-time-selectors')
    //     ),
    //     $hourelement,
    //     $mform->createElement('html', '</div>'),
    //     $mform->createElement('html', '<div class="col-md-6">'),
    //     $mform->createElement(
    //         'static',
    //         $addresstype . '_minutelabel',
    //         '',
    //         html_writer::div(get_string('minute', 'local_equipment'), 'local-equipment-pickups-addpickups-time-selectors')
    //     ),
    //     $minuteelement,
    //     $mform->createElement('html', '</div>'),
    //     $mform->createElement('html', '</div>')
    // );
    // return $mform->createElement('group', $addresstype, $label, $elements, ' ', false);




    // $group = [];

    // // $mform->addElement('header', $groupname . '_header', $label);
    // $mform->addElement('static', 'streetaddress_label_' . $groupname, get_string('streetaddress_' . $groupname, 'local_equipment'), \html_writer::tag('span', get_string('streetaddress_' . $groupname, 'local_equipment')));
    // $mform->addElement('static', 'city_label_' . $groupname, '', \html_writer::tag('label', get_string('city_' . $groupname, 'local_equipment')));
    // $mform->addElement('static', 'state_label_' . $groupname, '', \html_writer::tag('label', get_string('state_' . $groupname, 'local_equipment')));
    // $mform->addElement('static', 'zipcode_label_' . $groupname, '', \html_writer::tag('label', get_string('zipcode_' . $groupname, 'local_equipment')));
    // $group[] = $mform->createElement('text', 'streetaddress_' . $groupname, get_string('streetaddress_' . $groupname, 'local_equipment'));
    // $group[] = $mform->createElement('text', 'city_' . $groupname, get_string('city_' . $groupname, 'local_equipment'));
    // $group[] = $mform->createElement('text', 'state_' . $groupname, get_string('state_' . $groupname, 'local_equipment'));
    // $group[] = $mform->createElement('text', 'zipcode_' . $groupname, get_string('zipcode_' . $groupname, 'local_equipment'));

    // $mform->addGroup($group, $groupname . '_group', $label, '<br>', false);

    // // Set types for elements within the group
    // $mform->setType('streetaddress_' . $groupname, PARAM_TEXT);
    // $mform->setType('city_' . $groupname, PARAM_TEXT);
    // $mform->setType('state_' . $groupname, PARAM_TEXT);
    // $mform->setType('zipcode_' . $groupname, PARAM_TEXT);
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
    $required = false
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

    $block->elements["{$addresstype}_address"] = $mform->createElement('static', "{$addresstype}_address", \html_writer::tag('label', get_string("{$addresstype}_address", 'local_equipment'), ['class' => 'form-input-group-labels font-weight-bold']));
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
        ($field !== 'apartment') && $required ? $block->options[$fieldname]['rules']['required'] = ['message' => get_string('required'), 'format' => null] : null;
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

        $required ? $options[$fieldname]['rule'] = 'required' : null;
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

    return $block;
}


/**
 * Add an address block that may have default values populated from the database.
 *
 * @param MoodleQuickForm $mform a standard moodle form, probably will be '$this->_form'.
 * @param string $addresstype the type of address block to add: 'mailing', 'physical', 'pickup', or 'billing'.
 * @param stdClass $data the existing data to populate the form with.
 * @return object $block a block of elements to be added to the form.
 */
function local_equipment_add_edit_address_block($mform, $addresstype, $data) {
    // $block = new stdClass();

    $mform->addElement('static', $addresstype . 'address', \html_writer::tag('label', get_string($addresstype . 'address', 'local_equipment'), ['class' => 'form-input-group-labels']));

    if ($addresstype == 'mailing' || $addresstype == 'billing') {
        // Attention element
        $mform->addElement('text', "{$addresstype}_extrainput", get_string('attention', 'local_equipment'));
        $mform->setType("{$addresstype}_extrainput", PARAM_TEXT);
        $mform->setDefault("{$addresstype}_extrainput", $data->{"{$addresstype}_extrainput"});
    } else if ($addresstype == 'pickup') {
        // Instructions element
        $mform->addElement('textarea', "{$addresstype}_extrainstructions", get_string('pickupinstructions', 'local_equipment'));
        $mform->setType("{$addresstype}_extrainstructions", PARAM_TEXT);
        $mform->setDefault("{$addresstype}_extrainstructions", $data->{"{$addresstype}_extrainstructions"});
    }

    if ($addresstype !== 'physical') {
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
function create_time_selector($mform, $name, $label, $defaulttime = null) {
    $hours = array_combine(range(0, 23), range(0, 23));
    $minutes = array_combine(range(0, 59, 5), range(0, 59, 5)); // 5-minute intervals

    $hourelement = $mform->createElement('select', $name . 'hour', get_string('hour'), $hours);
    $minuteelement = $mform->createElement('select', $name . 'minute', get_string('minute'), $minutes);
    // Set the default starting hour and minute if it exists.
    if ($defaulttime) {
        $mform->setDefault($name . 'hour', date('H', $defaulttime));
        $mform->setDefault($name . 'minute', date('i', $defaulttime));
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




























// Virtual course consent (vcc) functions.
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
 * Retrieves partnership courses.
 *
 * @return array An associative array of partnership courses, with course ID as the key and course fullname as the value.
 */
function local_equipment_get_partnerships_with_courses() {
    global $DB;

    $partnerships = $DB->get_records('local_equipment_partnership', ['active' => 1]);
    $partnershipdata = array();

    foreach ($partnerships as $partnership) {
        $courseids = json_decode($partnership->courseids);
        if (!empty($courseids)) {
            $courses = $DB->get_records_list('course', 'id', $courseids, '', 'id, fullname');
            $partnershipdata[$partnership->id] = array_values($courses);
        }
    }

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

// /**
//  * Retrieves active agreements.
//  *
//  * @param MoodleQuickForm $mform The form object.
//  * @param array $agreements An array of active agreements (you can use local_equipment_get_active_agreements() function).
//  *
//  * @return array An array of agreement elements.
//  */
// function local_equipment_create_agreement_elements($mform, $agreements) {
//     $elements = [];
//     foreach ($agreements as $agreement) {

//         $elements[] = $mform->createElement('static', 'agreement_' . $agreement->id, $agreement->title, format_text($agreement->contenttext, $agreement->contentformat));
//         if ($agreement->agreementtype == 'optinout') {
//             $radioarray = array();
//             $radioarray[] = $mform->createElement('radio', "optionchoice_$agreement->id", '', get_string('optin', 'local_equipment'), 'optin');
//             $radioarray[] = $mform->createElement('radio', "optionchoice_$agreement->id", '', get_string('optout', 'local_equipment'), 'optout');
//             $mform->addGroup($radioarray, "optiongroup_$agreement->id", '', array(' '), false);

//             // Make the field required
//             $mform->addRule("optiongroup_$agreement->id", get_string('required'), 'required', null, 'client');

//             // Set a default value (optional)
//             // $mform->setDefault('optionchoice', 'optin');
//         }
//     }
// }

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
 * Generates a student email address.
 *
 * @param string $parentemail The parent's email address.
 * @param string $studentfirstname The student's first name.
 * @return string The generated student email address.
 */
function local_equipment_generate_student_email($parentemail, $studentfirstname) {
    $parts = explode('@', $parentemail);
    return $parts[0] . '+' . strtolower($studentfirstname) . '@' . $parts[1];
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
        echo '<pre>';
        var_dump($selectedcourses);
        echo '</pre>';
        die();

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
 * Observer function for the user_loggedin event.
 *
 * @param \core\event\user_loggedin $event The event.
 */
function local_equipment_user_verify_phone_number(\core\event\user_loggedin $event) {
    global $USER, $SESSION;

    // Check if the notification has already been shown in this session
    if (empty($SESSION->local_equipment_shown)) {
        $message = get_string('welcomemessage', 'local_equipment', $USER->firstname);
        \core\notification::add($message, \core\output\notification::NOTIFY_INFO);

        // Set a flag to avoid showing the message multiple times in the same session
        $SESSION->local_equipment_shown = true;
    }
}
