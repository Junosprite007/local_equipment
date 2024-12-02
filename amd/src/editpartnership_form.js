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
 * JavaScript for handling partnership course list functionality
 *
 * @module     local_equipment/editpartnership_form
 * @copyright  2024 Josh Kirby <josh@funlearningcompany.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Log from 'core/log';
import { get_string as getString } from 'core/str';

/**
 * Initialize the module
 */
export const init = async () => {
    // Core form elements
    const $partnershipSelect = $('#id_partnershipcourselist');
    const $coursesData = $('#id_coursesthisyear');
    const coursesContainer = $('<div>').addClass(
        'local-equipment_partnership-courses-container mt-3'
    ); // Changed to mt-3 for spacing below dropdown

    // Add courses container after the select element
    $partnershipSelect.after(coursesContainer);

    // Get strings
    const strings = await Promise.all([
        getString('errorparsingcoursesdata', 'local_equipment'),
        getString('nocoursesfoundforthispartnership', 'local_equipment'),
        getString('courseid', 'local_equipment'),
        getString('coursename', 'local_equipment'),
        getString('totalcourses', 'local_equipment'),
    ]);

    const [
        errorParsingData,
        noCoursesFound,
        courseIdLabel,
        courseNameLabel,
        totalCoursesLabel,
    ] = strings;

    // Parse the courses data
    let coursesData;
    try {
        coursesData = JSON.parse($coursesData.attr('data-coursesthisyear'));
    } catch (e) {
        Log.error(errorParsingData, e);
        return;
    }

    /**
     * Display courses for selected partnership
     * @param {string} partnershipId Selected partnership ID
     */
    const displayPartnershipCourses = (partnershipId) => {
        // Clear current courses
        coursesContainer.empty();

        // Get courses for selected partnership
        const partnershipCourses = coursesData[partnershipId];

        if (
            !partnershipCourses ||
            Object.keys(partnershipCourses).length === 0
        ) {
            coursesContainer.html(
                `<div class="alert alert-warning">${noCoursesFound}</div>`
            );
            // return;
        }

        // Create main container
        const $mainContainer = $('<div>').addClass(
            'local-equipment_main-courses-container'
        );

        // Create header
        const $header = $('<div>')
            .addClass('local-equipment_courses-header d-flex')
            .append(
                $('<div>')
                    .addClass('local-equipment_course-id-col')
                    .text(courseIdLabel),
                $('<div>')
                    .addClass('local-equipment_course-name-col')
                    .text(courseNameLabel)
            );

        // Create scrollable content area
        const $scrollContent = $('<div>').addClass(
            'local-equipment_courses-scroll-content'
        );

        // Sort and add courses
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
                .appendTo($scrollContent);
        });

        // Create footer
        const courseCount = sortedCourseIds.length;
        const $footer = $('<div>')
            .addClass('local-equipment_courses-footer')
            .text(`${totalCoursesLabel}: ${courseCount}`);

        // Assemble structure
        $mainContainer.append($header).append($scrollContent).append($footer);

        coursesContainer.append($mainContainer);
    };

    // Handle partnership selection change
    $partnershipSelect.on('change', function () {
        const selectedPartnership = $(this).val();
        if (selectedPartnership) {
            displayPartnershipCourses(selectedPartnership);
        } else {
            coursesContainer.empty();
        }
    });

    // Display initial courses if partnership is pre-selected
    const initialPartnership = $partnershipSelect.val();
    if (initialPartnership) {
        displayPartnershipCourses(initialPartnership);
    }
};
