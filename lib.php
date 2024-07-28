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
