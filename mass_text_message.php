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
 * Mass text messaging page for administrators.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Require admin login and capability check
admin_externalpage_setup('local_equipment_mass_text');
require_capability('local/equipment:sendmasstextmessages', context_system::instance());

$PAGE->set_url('/local/equipment/mass_text_message.php');
$PAGE->set_title(get_string('masstextmessaging', 'local_equipment'));
$PAGE->set_heading(get_string('masstextmessaging', 'local_equipment'));

// Add container class for styling
$PAGE->add_body_class('local_equipment_mass_text');

// Output the page
echo $OUTPUT->header();

// Add a container div for the form and results
echo '<div id="mass-text-container" class="local_equipment_mass_text">';

// Display instructions
// echo '<div class="alert alert-info mb-3">';
// echo '<h5>' . get_string('masstextinstructions_title', 'local_equipment') . '</h5>';
// echo '<p>' . get_string('masstextinstructions', 'local_equipment') . '</p>';
// echo '</div>';

// Initialize and display the dynamic form with proper handling
$form = new \local_equipment\form\mass_text_dynamic_form();

// Check if this is a form submission
if ($form->is_submitted() && $form->is_validated()) {
    // Process the form submission
    try {
        $result = $form->process_dynamic_submission();

        // Display success/error messages
        if (isset($result['success_message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
            echo $result['success_message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }

        if (isset($result['failure_message'])) {
            echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
            echo $result['failure_message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }

        // Display detailed error information if available
        if (isset($result['error_details']) && !empty($result['error_details'])) {
            echo '<div class="alert alert-danger mt-3">';
            echo '<h5>Error Details</h5>';
            echo '<div class="collapse" id="errorDetails">';
            echo '<ul class="mb-0">';

            foreach ($result['error_details'] as $error) {
                echo '<li>';
                echo '<strong>' . $error['recipient'] . '</strong> (' . $error['phone'] . '): ';
                echo $error['error_message'];
                if (isset($error['aws_error_code']) && $error['aws_error_code']) {
                    echo ' (AWS Error: ' . $error['aws_error_code'] . ')';
                }
                echo '</li>';
            }

            echo '</ul>';
            echo '</div>';
            echo '<button class="btn btn-sm btn-outline-danger mt-2" type="button" ';
            echo 'data-bs-toggle="collapse" data-bs-target="#errorDetails" ';
            echo 'aria-expanded="false" aria-controls="errorDetails">';
            echo 'View Error Details (' . count($result['error_details']) . ' errors)';
            echo '</button>';
            echo '</div>';
        }
    } catch (Exception $e) {
        echo '<div class="alert alert-danger" role="alert">';
        echo 'Error processing form: ' . $e->getMessage();
        echo '</div>';
    }
}

// Display the form
$form->display();

echo '</div>'; // Close container

// Add JavaScript for enhanced form handling
$PAGE->requires->js_call_amd('local_equipment/mass_text_page', 'init');

echo $OUTPUT->footer();
