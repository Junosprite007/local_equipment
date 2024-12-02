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
 * Register all hooks for the local_equipment plugin.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core_enrol\hook\after_user_enrolled::class,
        'callback' => [\local_equipment\local\hook_callbacks::class, 'prevent_default_notification'],
        'priority' => 101,
    ],
    [
        'hook' => \local_equipment\hook\equipment_user_enrolled::class,
        'callback' => [\local_equipment\local\hook_callbacks::class, 'handle_equipment_enrollment'],
        'priority' => 100,
    ],
];
