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
 * Message providers for Equipment plugin
 *
 * @package     local_equipment
 * @category    message
 * @copyright   2024 Joshua Kirby <josh@funlearningcompany.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$messageproviders = [
    'coursewelcome' => [
        'defaults' => [
            'popup' => true,
            'email' => true
        ],
        'capability'  => null,
        'contextlevel' => CONTEXT_COURSE,
        'scheduled' => false,
    ],
];
