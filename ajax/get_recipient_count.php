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
 * AJAX endpoint for getting recipient count for mass text messaging.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');

// Require login and capability check
require_login();
require_capability('local/equipment:sendmasstextmessages', context_system::instance());

// Set JSON header
header('Content-Type: application/json');

try {
    $manager = new \local_equipment\mass_text_manager();

    // Get students in active courses
    $studentids = $manager->get_students_in_courses_with_end_dates();

    if (empty($studentids)) {
        echo json_encode([
            'success' => true,
            'count' => 0
        ]);
        exit;
    }

    // Get verified parents for these students
    $parents = $manager->get_verified_parents_for_students($studentids);

    echo json_encode([
        'success' => true,
        'count' => count($parents)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
