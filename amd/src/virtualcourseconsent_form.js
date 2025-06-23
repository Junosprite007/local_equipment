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
 * JavaScript for the virtual course consent form.
 *
 * @module     local_equipment/virtualcourseconsent_form
 * @copyright  2024 Joshua Kirby <josh@funlearningcompany.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import { get_string as getString } from 'core/str';
import Ajax from 'core/ajax';

/**
 * Initialize the virtual course consent form functionality.
 */
export const init = () => {
    setupPickupMethodToggle();
    setupDynamicCourseSelection();
    setupStudentEmailGeneration();
};

/**
 * Set up the toggle for pickup method fields.
 */
const setupPickupMethodToggle = () => {
    const $pickupMethod = $('#id_pickupmethod');
    const $otherPickupFields = $(
        '#id_pickuppersonname, #id_pickuppersonphone, #id_pickuppersondetails'
    ).closest('.mb-3');

    const toggleOtherPickupFields = () => {
        if ($pickupMethod.val() === 'other') {
            $otherPickupFields.show();
        } else {
            $otherPickupFields.hide();
        }
    };

    $pickupMethod.on('change', toggleOtherPickupFields);
    toggleOtherPickupFields(); // Initial state
};

/**
 * Set up dynamic course selection based on partnership.
 */
const setupDynamicCourseSelection = () => {
    const $partnership = $('#id_partnership');
    const $courseSelects = $(
        'select[name^="studentrepeats"][name$="[student_courses]"]'
    );

    $partnership.on('change', () => {
        const partnershipId = $partnership.val();
        if (partnershipId) {
            Ajax.call([
                {
                    methodname: 'local_equipment_get_partnership_courses',
                    args: { partnershipid: partnershipId },
                    done: (courses) => {
                        $courseSelects.empty();
                        Object.entries(courses).forEach(([id, name]) => {
                            $courseSelects.append(
                                $('<option>').val(id).text(name)
                            );
                        });
                    },
                    fail: (error) => {
                        // eslint-disable-next-line no-console
                        console.error('Error fetching courses:', error);
                    },
                },
            ]);
        }
    });
};

/**
 * Set up student email generation.
 */
const setupStudentEmailGeneration = () => {
    $('input[name$="[student_email]"]').each((_, emailInput) => {
        const $email = $(emailInput);
        const $firstName = $email
            .closest('.fgroup')
            .find('input[name$="[student_firstname]"]');

        $firstName.on('blur', () => {
            if (!$email.val()) {
                const parentEmail = $('input[name="email"]').val();
                const studentFirstName = $firstName.val().toLowerCase();
                const generatedEmail = parentEmail.replace(
                    '@',
                    `+${studentFirstName}@`
                );
                $email.val(generatedEmail);
                getString('studentemailgenerated', 'local_equipment')
                    .then((string) => {
                        $email
                            .closest('.mb-3')
                            .append(
                                `<div class="form-text text-muted">${string}</div>`
                            );
                    })
                    .catch((error) => {
                        // eslint-disable-next-line no-console
                        console.error('Error fetching string:', error);
                    });
            }
        });
    });
};
