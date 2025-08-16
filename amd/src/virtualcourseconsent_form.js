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
    setupExchangeLocationFiltering();
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

/**
 * Set up dynamic filtering of pickup times based on exchange location selection.
 */
const setupExchangeLocationFiltering = () => {
    const $exchangeLocation = $('#id_exchange_partnershipid');
    const $pickupTime = $('#id_pickup');

    // Store all pickup time options on page load
    const allPickupOptions = $pickupTime.find('option').clone();

    const filterPickupTimes = () => {
        const selectedPartnershipId = $exchangeLocation.val();

        // Clear current options except the default "Have us contact you" option
        $pickupTime.empty();
        $pickupTime.append(allPickupOptions.filter('[value="0"]').clone());

        if (selectedPartnershipId && selectedPartnershipId !== '') {
            // Filter options by partnership - pickup times should contain partnership name
            allPickupOptions.each(function () {
                const $option = $(this);
                const optionValue = $option.val();
                const optionText = $option.text();

                // Skip the default "Have us contact you" option (value = 0)
                if (optionValue !== '0' && optionValue !== '') {
                    // Get partnership name to compare
                    const selectedPartnership = $exchangeLocation
                        .find('option:selected')
                        .text();
                    const partnershipName = selectedPartnership.split(' — ')[0]; // Get partnership name before address

                    // Check if pickup time option contains the selected partnership name
                    if (optionText.includes(partnershipName)) {
                        $pickupTime.append($option.clone());
                    }
                }
            });
        }

        // Ensure the default option remains selected
        $pickupTime.val('0');
    };

    // Set up event listener for exchange location changes
    $exchangeLocation.on('change', filterPickupTimes);

    // Apply initial filtering
    filterPickupTimes();
};
