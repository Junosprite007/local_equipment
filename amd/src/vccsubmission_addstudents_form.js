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
 * JavaScript for the add students form.
 *
 * @module     local_equipment/vccsubmission_addstudents_form
 * @copyright  2024 Joshua Kirby <josh@funlearningcompany.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import { get_string as getString } from 'core/str';
import Log from 'core/log';

/**
 * Initialize the add students form functionality.
 */
export const init = () => {
    Log.debug('Add Student Form JS initialized');
    setupStudentsHandling();
};

/**
 * Set up students handling.
 */
const setupStudentsHandling = () => {
    const selector = "fieldset[id^='id_studentheader_']";

    $(document).on('click', '.local-equipment-remove-student', function () {
        const $fieldset = $(this).closest(selector);
        $fieldset.remove();
        updateStudentNumbers();
        updateHiddenFields();
        renumberFormElements();
        updateTrashIcons();
    });

    updateTrashIcons();
};

/**
 * Update student numbers.
 */
const updateStudentNumbers = () => {
    $('.local-equipment-student-header').each((index, element) => {
        getString('student', 'local_equipment', index + 1)
            .then((string) => {
                $(element).text(string);
            })
            .catch((error) => {
                Log.error('Error updating student header:', error);
            });
    });
};

/**
 * Update hidden fields.
 */
const updateHiddenFields = () => {
    const studentsCount = $("fieldset[id^='id_studentheader_']").length;
    $('input[name="students"]').val(studentsCount);

    // Update the URL if necessary
    const url = new URL(window.location.href);
    url.searchParams.set('repeatno', studentsCount);
    window.history.replaceState({}, '', url);
};

/**
 * Renumber form elements.
 */
const renumberFormElements = () => {
    $("fieldset[id^='id_studentheader_']").each((index, fieldset) => {
        $(fieldset)
            .find('input, select, textarea')
            .each((_, element) => {
                const name = $(element).attr('name');
                if (name) {
                    const newName = name.replace(/\[\d+\]/, `[${index}]`);
                    $(element).attr('name', newName);
                }
                const id = $(element).attr('id');
                if (id) {
                    const newId = id.replace(/_\d+_/, `_${index}_`);
                    $(element).attr('id', newId);
                }
            });
    });
};

/**
 * Update trash icons visibility.
 */
const updateTrashIcons = () => {
    const students = $("fieldset[id^='id_studentheader_']");
    if (students.length > 1) {
        $('.local-equipment-remove-student').show();
    } else {
        $('.local-equipment-remove-student').hide();
    }
};
