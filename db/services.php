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
 * Services for the Equipment plugin.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_equipment_get_recipient_count' => [
        'classname'   => 'local_equipment\external\get_recipient_count',
        'description' => 'Get count of recipients for equipment mass messaging',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'local/equipment:viewrecipients',
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];

$services = [
    'Equipment Management Service' => [
        'functions' => ['local_equipment_get_recipient_count'],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'equipment_service',
    ],
];
