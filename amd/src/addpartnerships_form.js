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
 * JavaScript for the add partnerships form.
 *
 * @module     local_equipment/addpartnerships_form
 * @copyright  2024 Joshua Kirby <josh@funlearningcompany.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import { get_string as getString } from 'core/str';
import Log from 'core/log';

/**
 * Initialize the add partnerships form functionality.
 */
export const init = () => {
    Log.debug('Add Partnership Form JS initialized');
    setupPartnershipsHandling();
};

/**
 * Set up partnerships handling.
 */
const setupPartnershipsHandling = () => {
    const selector = "fieldset[id^='id_partnershipheader_']";

    $(document).on('click', '.local-equipment-remove-partnership', function () {
        const $fieldset = $(this).closest(selector);
        const $select = $fieldset.find(
            "select[id^='id_partnershipcourselist_']"
        );
        const containerId = $select
            .attr('id')
            .replace('partnershipcourselist', 'courses');
        $(`#${containerId}`).remove();

        $fieldset.remove();
        updatePartnershipNumbers();
        updateHiddenFields();
        renumberFormElements();
        updateTrashIcons();
    });

    updateTrashIcons();
};

/**
 * Update partnership numbers.
 */
const updatePartnershipNumbers = () => {
    $('.local-equipment-partnership-header').each((index, element) => {
        getString('partnership', 'local_equipment', index + 1)
            .then((string) => {
                $(element).text(string);
            })
            .catch((error) => {
                Log.error('Error updating partnership header:', error);
            });
    });
};

/**
 * Update hidden fields.
 */
const updateHiddenFields = () => {
    const partnershipsCount = $("fieldset[id^='id_partnershipheader_']").length;
    $('input[name="partnerships"]').val(partnershipsCount);

    // Update the URL if necessary
    const url = new URL(window.location.href);
    url.searchParams.set('repeatno', partnershipsCount);
    window.history.replaceState({}, '', url);
};

/**
 * Renumber form elements.
 */
const renumberFormElements = () => {
    $("fieldset[id^='id_partnershipheader_']").each((index, fieldset) => {
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
    const partnerships = $("fieldset[id^='id_partnershipheader_']");
    if (partnerships.length > 1) {
        $('.local-equipment-remove-partnership').show();
    } else {
        $('.local-equipment-remove-partnership').hide();
    }
};

/**
 * Display course listings for partnership dropdowns
 */
export const displayPartnershipCourseListing = () => {
    const coursesData = JSON.parse(
        $('#id_coursesthisyear').attr('data-coursesthisyear')
    );

    /**
     * Create course listing for a specific partnership dropdown
     * @param {jQuery} $select The partnership select element
     */
    const initializePartnershipSelect = async ($select) => {
        // Create unique container for this partnership's courses
        const containerId = $select
            .attr('id')
            .replace('partnershipcourselist', 'courses');
        let $container = $(`#${containerId}`);

        // If container doesn't exist, create it
        if (!$container.length) {
            $container = $('<div>', {
                id: containerId,
                class: 'local-equipment_partnership-courses-container mt-3',
            });
            $select.after($container);
        }

        // Get required strings
        const strings = await Promise.all([
            getString('nocoursesfoundforthispartnership', 'local_equipment'),
            getString('courseid', 'local_equipment'),
            getString('coursename', 'local_equipment'),
            getString('totalcourses', 'local_equipment'),
        ]);

        const [
            noCoursesFound,
            courseIdLabel,
            courseNameLabel,
            totalCoursesLabel,
        ] = strings;

        /**
         * Display courses for the selected partnership
         * @param {string} partnershipId The selected partnership ID
         */
        const displayCourses = (partnershipId) => {
            $container.empty();

            const partnershipCourses = coursesData[partnershipId];

            if (
                !partnershipCourses ||
                Object.keys(partnershipCourses).length === 0
            ) {
                $container.html(
                    `<div class="alert alert-warning">${noCoursesFound}</div>`
                );
            }

            // Create main container with fixed height layout
            const $mainContainer = $('<div>').addClass(
                'local-equipment_main-courses-container'
            );

            // Create fixed header
            const $header = $('<div>')
                .addClass('local-equipment_courses-header')
                .append(
                    $('<div>')
                        .addClass('local-equipment_courses-header-row d-flex')
                        .append(
                            $('<div>')
                                .addClass('local-equipment_course-id-col')
                                .text(courseIdLabel),
                            $('<div>')
                                .addClass('local-equipment_course-name-col')
                                .text(courseNameLabel)
                        )
                );

            // Create scrollable content area.
            const $scrollContent = $('<div>').addClass(
                'local-equipment_courses-scroll-content'
            );
            const $scrollTable = $('<div>').addClass(
                'local-equipment_courses-table'
            );

            // Sort and add courses.
            const sortedCourseIds = Object.keys(partnershipCourses).sort(
                (a, b) => parseInt(a) - parseInt(b)
            );

            sortedCourseIds.forEach((courseId) => {
                $('<div>')
                    .addClass('local-equipment_course-row d-flex')
                    .append(
                        $('<div>')
                            .addClass('local-equipment_course-id-col')
                            .text(courseId),
                        $('<div>')
                            .addClass('local-equipment_course-name-col')
                            .text(partnershipCourses[courseId])
                    )
                    .appendTo($scrollTable);
            });

            $scrollContent.append($scrollTable);

            // Create fixed footer
            const $footer = $('<div>')
                .addClass('local-equipment_courses-footer')
                .text(`${totalCoursesLabel}: ${sortedCourseIds.length}`);

            // Assemble the structure
            $mainContainer
                .append($header)
                .append($scrollContent)
                .append($footer);

            $container.append($mainContainer);
        };

        // Handle changes to this select
        $select.on('change', function () {
            const selectedPartnership = $(this).val();
            if (selectedPartnership) {
                displayCourses(selectedPartnership);
            } else {
                $container.empty();
            }
        });

        // Display initial courses if pre-selected
        const initialPartnership = $select.val();
        if (initialPartnership) {
            displayCourses(initialPartnership);
        }
    };

    // Initialize all existing partnership selects
    $("select[id^='id_partnershipcourselist_']").each(function () {
        initializePartnershipSelect($(this));
    });

    // Handle newly added partnerships
    $(document).on('click', '[name="addpartnership"]', function () {
        // Wait for DOM to update
        setTimeout(() => {
            const $newSelect = $(
                "select[id^='id_partnershipcourselist_']:last"
            );
            if ($newSelect.length) {
                initializePartnershipSelect($newSelect);
            }
        }, 100);
    });
};
