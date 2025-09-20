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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use moodleform;

global $CFG;

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

        // Add Bootstrap 5 classes and collapsible functionality
        $mform->setAttributes(['class' => 'vcc-filter-form']);

        // Phase 4.1: Enhanced filter form layout with collapsible section
        $mform->addElement('header', 'filters_header', get_string('filters', 'local_equipment'));
        $mform->setExpanded('filters_header', true);

        // Phase 4.1: Proper Bootstrap 5 grid system implementation
        // Row 1: Partnership and Search filters
        $mform->addElement('html', '<div class="row g-3 mb-3">');

        // Partnership filter with improved styling
        $mform->addElement('html', '<div class="col-lg-4 col-md-6 col-sm-12">');
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

        // Search filter with improved placeholder and styling
        $mform->addElement('html', '<div class="col-lg-4 col-md-6 col-sm-12">');
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

        // Phase 4.2: Quick filter presets dropdown
        $mform->addElement('html', '<div class="col-lg-4 col-md-12 col-sm-12">');
        $quick_presets = [
            '' => get_string('selectquickfilter', 'local_equipment'),
            'today' => get_string('today', 'local_equipment'),
            'thisweek' => get_string('thisweek', 'local_equipment'),
            'thismonth' => get_string('thismonth', 'local_equipment'),
            'thisyear' => get_string('thisyear', 'local_equipment'),
            'last7days' => get_string('last7days', 'local_equipment'),
            'last30days' => get_string('last30days', 'local_equipment')
        ];
        $mform->addElement(
            'select',
            'quickfilter',
            get_string('quickfilters', 'local_equipment'),
            $quick_presets,
            ['class' => 'form-select', 'id' => 'id_quickfilter']
        );
        $mform->setType('quickfilter', PARAM_TEXT);
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</div>'); // End row 1

        // Phase 4.1: Enhanced date range functionality - Row 2
        $mform->addElement('html', '<div class="row g-3 mb-3">');

        // Start date with better labeling
        $mform->addElement('html', '<div class="col-md-6 col-sm-12">');
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

        // End date with better labeling
        $mform->addElement('html', '<div class="col-md-6 col-sm-12">');
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

        $mform->addElement('html', '</div>'); // End row 2

        // Phase 4.2: Enhanced action buttons with clear indicators
        $mform->addElement('html', '<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">');

        // Filter action buttons
        $mform->addElement('html', '<div class="d-flex gap-2">');
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
        $mform->addGroup($buttonarray, 'buttonar', '', '', false);
        $mform->addElement('html', '</div>');

        // Phase 4.2: Filter count indicator (populated via JavaScript)
        $mform->addElement('html', '<div class="filter-status">');
        $mform->addElement('html', '<small class="text-muted" id="filter-count-indicator">' .
            get_string('nofiltersapplied', 'local_equipment') . '</small>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</div>'); // End button row

        $mform->closeHeaderBefore('buttonar');

        // Phase 4.2: Add JavaScript for quick filter functionality and filter count
        $mform->addElement('html', '<script>
document.addEventListener("DOMContentLoaded", function() {
    // Quick filter preset functionality
    const quickFilterSelect = document.getElementById("id_quickfilter");
    if (quickFilterSelect) {
        quickFilterSelect.addEventListener("change", function() {
            const value = this.value;
            const now = new Date();
            let startDate = null;
            let endDate = null;

            switch(value) {
                case "today":
                    startDate = endDate = now;
                    break;
                case "thisweek":
                    const startOfWeek = new Date(now);
                    startOfWeek.setDate(now.getDate() - now.getDay());
                    const endOfWeek = new Date(startOfWeek);
                    endOfWeek.setDate(startOfWeek.getDate() + 6);
                    startDate = startOfWeek;
                    endDate = endOfWeek;
                    break;
                case "thismonth":
                    startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    break;
                case "thisyear":
                    startDate = new Date(now.getFullYear(), 0, 1);
                    endDate = new Date(now.getFullYear(), 11, 31);
                    break;
                case "last7days":
                    startDate = new Date(now);
                    startDate.setDate(now.getDate() - 7);
                    endDate = now;
                    break;
                case "last30days":
                    startDate = new Date(now);
                    startDate.setDate(now.getDate() - 30);
                    endDate = now;
                    break;
            }

            if (startDate && endDate) {
                setDateSelectorValue("datestart", startDate);
                setDateSelectorValue("dateend", endDate);
                updateFilterCount();
            }
        });
    }

    // Update filter count indicator
    function updateFilterCount() {
        let count = 0;
        const partnership = document.querySelector("select[name=\"partnership\"]");
        const search = document.querySelector("input[name=\"search\"]");
        const datestart = document.querySelector("select[name=\"datestart[day]\"]");
        const dateend = document.querySelector("select[name=\"dateend[day]\"]");

        if (partnership && partnership.value && partnership.value !== "0") count++;
        if (search && search.value.trim()) count++;
        if (datestart && datestart.value && datestart.value !== "0") count++;
        if (dateend && dateend.value && dateend.value !== "0") count++;

        const indicator = document.getElementById("filter-count-indicator");
        if (indicator) {
            if (count > 0) {
                indicator.textContent = "' . get_string('activefilters', 'local_equipment') . ': " + count;
                indicator.className = "text-primary fw-bold";
            } else {
                indicator.textContent = "' . get_string('nofiltersapplied', 'local_equipment') . '";
                indicator.className = "text-muted";
            }
        }
    }

    // Set date selector values (helper function)
    function setDateSelectorValue(name, date) {
        const daySelect = document.querySelector("select[name=\"" + name + "[day]\"]");
        const monthSelect = document.querySelector("select[name=\"" + name + "[month]\"]");
        const yearSelect = document.querySelector("select[name=\"" + name + "[year]\"]");

        if (daySelect) daySelect.value = date.getDate();
        if (monthSelect) monthSelect.value = date.getMonth() + 1;
        if (yearSelect) yearSelect.value = date.getFullYear();
    }

    // Initialize filter count on page load
    updateFilterCount();

    // Add event listeners to form elements for dynamic filter count updates
    document.querySelectorAll("select, input").forEach(function(element) {
        element.addEventListener("change", updateFilterCount);
        element.addEventListener("keyup", updateFilterCount);
    });
});
</script>');
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
                redirect('/local/equipment/vccsubmissions/view.php');
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
