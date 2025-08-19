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

declare(strict_types=1);

namespace local_equipment\form;

use moodleform;
use moodle_url;

/**
 * Filter form for VCC submissions following Moodle 5.0 conventions
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_filter_form extends moodleform {

    /**
     * Define the form
     */
    public function definition(): void {
        global $DB;

        $mform = $this->_form;

        // Add Bootstrap 5 container class
        $mform->setAttributes(['class' => 'vcc-filter-form']);

        // Filter section header
        $mform->addElement('header', 'filters_header', get_string('filters', 'local_equipment'));
        $mform->setExpanded('filters_header', true);

        // Create responsive row container
        $mform->addElement('html', '<div class="row g-3 mb-3">');

        // Partnership filter
        $mform->addElement('html', '<div class="col-md-3">');
        $partnerships = $this->get_partnerships();
        $mform->addElement(
            'select',
            'partnership',
            get_string('partnership', 'local_equipment'),
            $partnerships,
            ['class' => 'form-select']
        );
        $mform->setType('partnership', PARAM_INT);
        $mform->setDefault('partnership', 0);
        $mform->addElement('html', '</div>');

        // Date start filter
        $mform->addElement('html', '<div class="col-md-3">');
        $mform->addElement(
            'date_selector',
            'datestart',
            get_string('datestart', 'local_equipment'),
            [
                'optional' => true,
                'class' => 'form-control'
            ]
        );
        $mform->addElement('html', '</div>');

        // Date end filter
        $mform->addElement('html', '<div class="col-md-3">');
        $mform->addElement(
            'date_selector',
            'dateend',
            get_string('dateend', 'local_equipment'),
            [
                'optional' => true,
                'class' => 'form-control'
            ]
        );
        $mform->addElement('html', '</div>');

        // Search filter
        $mform->addElement('html', '<div class="col-md-3">');
        $mform->addElement(
            'text',
            'search',
            get_string('search'),
            [
                'placeholder' => get_string('searchplaceholder', 'local_equipment'),
                'class' => 'form-control'
            ]
        );
        $mform->setType('search', PARAM_TEXT);
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</div>'); // End row

        // Action buttons with Bootstrap 5 styling
        $mform->addElement('html', '<div class="d-flex gap-2 mb-3">');

        $buttonarray = [];
        $buttonarray[] = $mform->createElement(
            'submit',
            'submitbutton',
            get_string('applyfilters', 'local_equipment'),
            ['class' => 'btn btn-primary']
        );
        $buttonarray[] = $mform->createElement(
            'submit',
            'resetfilters',
            get_string('resetfilters', 'local_equipment'),
            ['class' => 'btn btn-secondary']
        );

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
        $mform->closeHeaderBefore('buttonar');

        $mform->addElement('html', '</div>');
    }

    /**
     * Get partnerships for dropdown
     *
     * @return array
     */
    private function get_partnerships(): array {
        global $DB;

        $partnerships = [0 => get_string('all', 'local_equipment')];

        $records = $DB->get_records(
            'local_equipment_partnership',
            ['active' => 1],
            'name ASC',
            'id, name'
        );

        foreach ($records as $record) {
            $partnerships[$record->id] = $record->name;
        }

        return $partnerships;
    }

    /**
     * Process form data after submission
     *
     * @return \stdClass|null
     */
    public function get_data(): ?\stdClass {
        $data = parent::get_data();

        if ($data) {
            // Handle reset button
            if (isset($data->resetfilters)) {
                // Redirect to clear filters
                $url = new moodle_url('/local/equipment/vccsubmissions/view.php');
                redirect($url);
            }

            // Clean search input
            if (isset($data->search)) {
                $data->search = trim($data->search);
                if (empty($data->search)) {
                    unset($data->search);
                }
            }

            // Convert date selectors to proper timestamps if they exist
            if (!empty($data->datestart)) {
                // datestart is already a timestamp from date_selector
                $data->datestart = (int)$data->datestart;
            }

            if (!empty($data->dateend)) {
                // dateend is already a timestamp, but make it end of day
                $data->dateend = (int)$data->dateend + (24 * 60 * 60) - 1;
            }
        }

        return $data;
    }

    /**
     * Validation rules
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        // Validate date range
        if (!empty($data['datestart']) && !empty($data['dateend'])) {
            if ($data['datestart'] > $data['dateend']) {
                $errors['dateend'] = get_string('dateendbeforestart', 'local_equipment');
            }
        }

        // Validate search length
        if (!empty($data['search']) && strlen($data['search']) < 2) {
            $errors['search'] = get_string('searchtooshort', 'local_equipment');
        }

        return $errors;
    }
}
