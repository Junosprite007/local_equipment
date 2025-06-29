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
 * External services for the Equipment plugin.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_equipment_validate_upc' => [
        'classname' => 'local_equipment\external\validate_upc',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Validate UPC code against external API',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/equipment:manageinventory',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'local_equipment_process_scan' => [
        'classname' => 'local_equipment\external\process_scan',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Process barcode scan (QR code or UPC)',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/equipment:checkinout',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];

$services = [
    'Equipment Management Service' => [
        'functions' => [
            'local_equipment_validate_upc',
            'local_equipment_process_scan',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];
