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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Hook callback registrations for equipment plugin.
 *
 * @package     local_equipment
 * @category    hook
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// UNCOMMENT ALL OF THE BELOW AFTER TEXTING IS IMPLEMENTED.

// $callbacks = [
//     [
//         'hook' => \core_user\hook\after_login_completed::class,
//         'callback' => [\local_equipment\hook\callbacks::class, 'check_phone_verification'],
//         'priority' => 500,
//     ],
// ];
