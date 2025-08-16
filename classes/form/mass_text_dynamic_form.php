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
 * Dynamic form for sending mass text messages to parents.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\form;

use core_form\dynamic_form;
use context_system;

defined('MOODLE_INTERNAL') || die();

/**
 * Dynamic form for sending mass text messages to parents.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class mass_text_dynamic_form extends dynamic_form {

    /**
     * Define the form elements.
     */
    public function definition() {
        $mform = $this->_form;

        // Hidden field for form identification
        $mform->addElement('hidden', 'formtype', 'mass_text');
        $mform->setType('formtype', PARAM_ALPHA);

        // Form title header
        $mform->addElement('header', 'formtitle', get_string('masstextmessaging', 'local_equipment'));
        $mform->setExpanded('formtitle', true);

        $description = $this->get_form_description();
        $mform->addElement('html', $description);

        // Message textarea with enhanced attributes for accessibility
        // Message textarea with character limit
        $mform->addElement('textarea', 'message', get_string('message', 'local_equipment'), [
            'rows' => 5,
            'cols' => 60,
            'maxlength' => 250,
            'placeholder' => get_string('message_placeholder', 'local_equipment'),
            'data-max-length' => 250
        ]);
        $mform->setType('message', PARAM_TEXT);
        $mform->addRule('message', get_string('required'), 'required', null, 'client');
        $mform->addRule('message', get_string('maximumchars', '', 250), 'maxlength', 250, 'client');

        // Character counter (will be handled by JavaScript)
        $mform->addElement(
            'static',
            'charcount',
            '',
            '<div id="char-counter" class="text-muted small mt-1">' .
                get_string('charactersremaining', 'local_equipment', 250) .
                '</div>'
        );

        // Preview section
        $mform->addElement('header', 'previewheader', get_string('preview', 'local_equipment'));
        $mform->setExpanded('previewheader', true);
        $description = $this->get_form_description();
        $mform->addElement(
            'html',
            '<div class="alert alert-primary d-flex align-items-start mb-4" role="region" aria-labelledby="form-desc-title">' .
                '<i class="fa fa-info-circle me-3 mt-1 flex-shrink-0" aria-hidden="true"></i>' .
                '<div>' .
                '<p class="mb-0">' . get_string('mass_text_tips', 'local_equipment') . '</p>' .
                '</div>' .
                '</div>'
        );

        $mform->addElement(
            'static',
            'recipientcount',
            get_string('estimated_recipients', 'local_equipment'),
            '<span id="recipient-count" class="badge bg-secondary">' .
                get_string('calculating', 'local_equipment') .
                '</span>'
        );

        // $mform->addElement(
        //     'static',
        //     'tips',
        //     '',
        //     '<div class="alert alert-info mt-3">' .
        //         '<i class="fa fa-info-circle me-2" aria-hidden="true"></i>' .
        //         get_string('mass_text_tips', 'local_equipment') .
        //         '</div>'
        // );

        // Add submit button
        $this->add_action_buttons(true, get_string('sendmessages', 'local_equipment'));

        // Add JavaScript for dynamic functionality
        $this->add_dynamic_form_js();
    }

    /**
     * Get the form description HTML with proper Bootstrap 5 styling.
     *
     * @return string HTML content for form description
     */
    private function get_form_description(): string {
        return '<div class="alert alert-primary d-flex align-items-start mb-4" role="region" aria-labelledby="form-desc-title">' .
            '<i class="fa fa-info-circle me-3 mt-1 flex-shrink-0" aria-hidden="true"></i>' .
            '<div>' .
            '<h6 id="form-desc-title" class="alert-heading mb-2">' .
            get_string('masstextinstructions_title', 'local_equipment') . '</h6>' .
            '<p class="mb-0">' . get_string('masstextinstructions', 'local_equipment') . '</p>' .
            '</div>' .
            '</div>';
    }

    /**
     * Add JavaScript for dynamic form functionality.
     */
    protected function add_dynamic_form_js() {
        global $PAGE;

        // Load the AMD module for dynamic form functionality
        $PAGE->requires->js_call_amd('local_equipment/mass_text_dynamic_form', 'init');
    }

    /**
     * Validate the form data.
     *
     * @param array $data Form data
     * @param array $files Form files
     * @return array Array of errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate message length
        if (isset($data['message'])) {
            $message = trim($data['message']);

            if (empty($message)) {
                $errors['message'] = get_string('required');
            } else if (strlen($message) > 250) {
                $errors['message'] = get_string('maximumchars', '', 250);
            } else if (strlen($message) < 10) {
                $errors['message'] = get_string('minimumchars', '', 10);
            }
        }

        return $errors;
    }

    /**
     * Check if current user has access to this form.
     *
     * @return void
     * @throws \required_capability_exception
     */
    protected function check_access_for_dynamic_submission(): void {
        require_capability('local/equipment:sendmasstextmessages', context_system::instance());
    }

    /**
     * Process the dynamic submission.
     *
     * @return array Result data
     */
    public function process_dynamic_submission() {
        global $USER;

        $data = $this->get_data();

        if (!$data) {
            throw new \moodle_exception('invalidformdata');
        }

        // Process the mass text message
        $manager = new \local_equipment\mass_text_manager();

        // Get students in active courses
        $studentids = $manager->get_students_in_courses_with_end_dates();

        if (empty($studentids)) {
            return [
                'success' => false,
                'message' => get_string('nostudentsinactivecourses', 'local_equipment'),
                'type' => 'warning'
            ];
        }

        // Get verified parents for these students
        $parents = $manager->get_verified_parents_for_students($studentids);

        if (empty($parents)) {
            return [
                'success' => false,
                'message' => get_string('noparentsverifiedphones', 'local_equipment'),
                'type' => 'warning'
            ];
        }

        // Send the messages
        $results = $manager->send_mass_messages(trim($data->message), $parents, $USER->id);

        // Prepare response
        $response = [
            'success' => true,
            'results' => $results,
            'redirect' => $this->get_page_url_for_dynamic_submission()->out()
        ];

        // Add success message
        if ($results->success_count > 0) {
            $response['success_message'] = get_string('masstextsuccess', 'local_equipment', [
                'sent' => $results->success_count,
                'total' => $results->total_recipients
            ]);
        }

        // Add failure information if any
        if ($results->failure_count > 0) {
            $response['failure_message'] = get_string('masstextfailures', 'local_equipment', [
                'failed' => $results->failure_count,
                'total' => $results->total_recipients
            ]);
            $response['error_details'] = $results->error_details;
        }

        return $response;
    }

    /**
     * Load in existing data as form defaults.
     *
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {
        // No default data needed for this form
    }

    /**
     * Returns form context.
     *
     * @return \context
     */
    protected function get_context_for_dynamic_submission(): \context {
        return context_system::instance();
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX.
     *
     * @return \moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): \moodle_url {
        return new moodle_url('/local/equipment/mass_text_message.php');
    }
}
