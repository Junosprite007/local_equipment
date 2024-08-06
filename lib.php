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
 * Generate a user selector with autocomplete for the user's liaison.
 *
 * @return array An array with the proper parameters for a user select field with autocomplete.
 */
function local_equipment_auto_complete_users() {
    return [
        'ajax' => 'core_user/form_user_selector',
        'multiple' => true,
        'casesensitive' => false,
        'valuehtmlcallback' => 'local_equipment_user_selector_callback'
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
    $user = \core_user::get_user($id);
    return $OUTPUT->user_picture($user, array('size' => 24)) . ' ' . fullname($user);
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
    $userlinks = [];
    $liaisoninfo = [];

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
 * Get all courses from a given category.
 *
 * @param string $categoryname A database record that contains multiple types of addresses.
 * @return stdClass $courses_formatted returns whatever the first category is to match give category name.
 */
function local_equipment_get_master_courses($categoryname = 'ALL_COURSES_CURRENT') {
    global $DB;

    // Make this an admin setting later on.
    // $categoryname = 'ALL_COURSES_CURRENT';

    // Fetch the course categories by name.
    $categories = $DB->get_records('course_categories', array('name' => $categoryname));
    $category = array_values($categories)[0];
    $courses = $DB->get_records('course', array('category' => $category->id));

    $courses_formatted = [];
    foreach ($courses as $course) {
        $courses_formatted[$course->id] = $course->fullname;
    }
    return $courses_formatted;
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

/**
 * Add an address group for if I want each address to appear in a line,
 * though the text boxes currently doesn't have labels doing it this way.
 * I'd have to figure that out, and I don't want to....
 *
 * @param moodleform $mform a standard moodle form, probably will be '$this->_form'.
 * @param string $groupname the name of the group to add.
 * @param string $label the label for the group.
 */
function local_equipment_add_address_group($mform, $groupname, $label) {
    $group = array();

    // $mform->addElement('header', $groupname . '_header', $label);
    $mform->addElement('static', 'streetaddress_label_' . $groupname, get_string('streetaddress_' . $groupname, 'local_equipment'), \html_writer::tag('span', get_string('streetaddress_' . $groupname, 'local_equipment')));
    $mform->addElement('static', 'city_label_' . $groupname, '', \html_writer::tag('label', get_string('city_' . $groupname, 'local_equipment')));
    $mform->addElement('static', 'state_label_' . $groupname, '', \html_writer::tag('label', get_string('state_' . $groupname, 'local_equipment')));
    $mform->addElement('static', 'zipcode_label_' . $groupname, '', \html_writer::tag('label', get_string('zipcode_' . $groupname, 'local_equipment')));
    $group[] = $mform->createElement('text', 'streetaddress_' . $groupname, get_string('streetaddress_' . $groupname, 'local_equipment'));
    $group[] = $mform->createElement('text', 'city_' . $groupname, get_string('city_' . $groupname, 'local_equipment'));
    $group[] = $mform->createElement('text', 'state_' . $groupname, get_string('state_' . $groupname, 'local_equipment'));
    $group[] = $mform->createElement('text', 'zipcode_' . $groupname, get_string('zipcode_' . $groupname, 'local_equipment'));

    $mform->addGroup($group, $groupname . '_group', $label, '<br>', false);

    // Set types for elements within the group
    $mform->setType('streetaddress_' . $groupname, PARAM_TEXT);
    $mform->setType('city_' . $groupname, PARAM_TEXT);
    $mform->setType('state_' . $groupname, PARAM_TEXT);
    $mform->setType('zipcode_' . $groupname, PARAM_TEXT);
}

/**
 * Add an address block.
 *
 * @param moodleform $mform a standard moodle form, probably will be '$this->_form'.
 * @param string $addresstype the type of address block to add: 'mailing', 'physical', 'pickup', or 'billing'.
 * @return object $block a block of elements to be added to the form.
 */
function local_equipment_add_address_block($mform, $addresstype) {
    $block = new stdClass();

    $block->elements = array();
    $block->options = array();
    $block->elements[$addresstype . 'address'] = $mform->createElement('static', $addresstype . 'address', \html_writer::tag('label', get_string($addresstype . 'address', 'local_equipment'), ['class' => 'form-input-group-labels']));

    switch ($addresstype) {
        case 'mailing':
            $block->elements['attention_' . $addresstype] = $mform->createElement('text', 'attention_' . $addresstype, get_string('attention', 'local_equipment'));
            $block->options['attention_' . $addresstype]['type'] = PARAM_TEXT;
            break;
        case 'pickup':
            $block->elements['instructions_' . $addresstype] = $mform->createElement('textarea', 'instructions_' . $addresstype, get_string('pickupinstructions', 'local_equipment'));
            $block->options['instructions_' . $addresstype]['type'] = PARAM_TEXT;
            break;
        case 'billing':
            $block->elements['attention_' . $addresstype] = $mform->createElement('text', 'attention_' . $addresstype, get_string('attention', 'local_equipment'));
            $block->options['attention_' . $addresstype]['type'] = PARAM_TEXT;
            break;
        default:
            break;
    }

    if ($addresstype !== 'physical') {
        $block->elements['sameasphysical_' . $addresstype] = $mform->createElement('advcheckbox', 'sameasphysical_' . $addresstype, get_string('sameasphysical', 'local_equipment'));
        $block->options['sameasphysical_' . $addresstype]['type'] = PARAM_BOOL;
    }

    $block->elements['streetaddress_' . $addresstype] = $mform->createElement('text', 'streetaddress_' . $addresstype, get_string('streetaddress', 'local_equipment'));
    $block->elements['city_' . $addresstype] = $mform->createElement('text', 'city_' . $addresstype, get_string('city', 'local_equipment'));
    $block->elements['state_' . $addresstype] = $mform->createElement('select', 'state_' . $addresstype, get_string('state', 'local_equipment'), local_equipment_get_states());
    $block->elements['country_' . $addresstype] = $mform->createElement('select', 'country_' . $addresstype, get_string('country', 'local_equipment'), local_equipment_get_countries());
    $block->elements['zipcode_' . $addresstype] = $mform->createElement('text', 'zipcode_' . $addresstype, get_string('zipcode', 'local_equipment'));

    $block->options['streetaddress_' . $addresstype]['type'] = PARAM_TEXT;
    $block->options['city_' . $addresstype]['type'] = PARAM_TEXT;
    $block->options['state_' . $addresstype]['type'] = PARAM_TEXT;
    $block->options['country_' . $addresstype]['type'] = PARAM_TEXT;
    $block->options['zipcode_' . $addresstype]['type'] = PARAM_TEXT;

    // The physical address is required, but none of the others are.
    if ($addresstype === 'physical') {
        $block->options['streetaddress_' . $addresstype]['rule'] = 'required';
        $block->options['city_' . $addresstype]['rule'] = 'required';
        $block->options['state_' . $addresstype]['rule'] = 'required';
        $block->options['country_' . $addresstype]['rule'] = 'required';
        $block->options['zipcode_' . $addresstype]['rule'] = 'required';
    }

    $block->options['state_' . $addresstype]['default'] = 'MI';
    $block->options['country_' . $addresstype]['default'] = 'USA';

    return $block;
}

/**
 * Add an address block that may have default values populated from the database.
 *
 * @param moodleform $mform a standard moodle form, probably will be '$this->_form'.
 * @param string $addresstype the type of address block to add: 'mailing', 'physical', 'pickup', or 'billing'.
 * @return object $block a block of elements to be added to the form.
 */
function local_equipment_add_edit_address_block($mform, $addresstype, $data) {
    // $block = new stdClass();

    $mform->addElement('static', $addresstype . 'address', \html_writer::tag('label', get_string($addresstype . 'address', 'local_equipment'), ['class' => 'form-input-group-labels']));

    if ($addresstype == 'mailing' || $addresstype == 'billing') {
        // Attention element
        $mform->addElement('text', 'attention_' . $addresstype, get_string('attention', 'local_equipment'));
        $mform->setType('attention_' . $addresstype, PARAM_TEXT);
        $mform->setDefault('attention_' . $addresstype, $data->{"attention_$addresstype"});
    } elseif ($addresstype == 'pickup') {
        // Instructions element
        $mform->addElement('textarea', 'instructions_' . $addresstype, get_string('pickupinstructions', 'local_equipment'));
        $mform->setType('instructions_' . $addresstype, PARAM_TEXT);
        $mform->setDefault('instructions_' . $addresstype, $data->{"instructions_$addresstype"});
    }

    if ($addresstype !== 'physical') {
        // Same as physical checkbox
        $mform->addElement('advcheckbox', 'sameasphysical_' . $addresstype, get_string('sameasphysical', 'local_equipment'));
        $mform->setType('sameasphysical_' . $addresstype, PARAM_BOOL);
        $mform->setDefault('sameasphysical_' . $addresstype, $data->{"sameasphysical_$addresstype"});
    }

    $mform->addElement('text', 'streetaddress_' . $addresstype, get_string('streetaddress', 'local_equipment'));
    $mform->setType('streetaddress_' . $addresstype, PARAM_TEXT);
    $mform->setDefault('streetaddress_' . $addresstype, $data->{"streetaddress_$addresstype"});

    $mform->addElement('text', 'city_' . $addresstype, get_string('city', 'local_equipment'));
    $mform->setType('city_' . $addresstype, PARAM_TEXT);
    $mform->setDefault('city_' . $addresstype, $data->{"city_$addresstype"});

    $mform->addElement('select', 'state_' . $addresstype, get_string('state', 'local_equipment'), local_equipment_get_states());
    $mform->setType('state_' . $addresstype, PARAM_TEXT);
    $mform->setDefault('state_' . $addresstype, $data->{"state_$addresstype"});

    $mform->addElement('select', 'country_' . $addresstype, get_string('country', 'local_equipment'), local_equipment_get_countries());
    $mform->setType('country_' . $addresstype, PARAM_TEXT);
    $mform->setDefault('country_' . $addresstype, $data->{"country_$addresstype"});

    $mform->addElement('text', 'zipcode_' . $addresstype, get_string('zipcode', 'local_equipment'));
    $mform->setType('zipcode_' . $addresstype, PARAM_TEXT);
    $mform->setDefault('zipcode_' . $addresstype, $data->{"zipcode_$addresstype"});

    if ($addresstype === 'physical') {
        // Physical address is the only address that's required.
        $mform->addRule('streetaddress_' . $addresstype, get_string('required'), 'required', null, 'client');
        $mform->addRule('city_' . $addresstype, get_string('required'), 'required', null, 'client');
        $mform->addRule('state_' . $addresstype, get_string('required'), 'required', null, 'client');
        $mform->addRule('country_' . $addresstype, get_string('required'), 'required', null, 'client');
        $mform->addRule('zipcode_' . $addresstype, get_string('required'), 'required', null, 'client');
    }
}
