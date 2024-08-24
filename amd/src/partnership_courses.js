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
 * JavaScript for managing consents.
 *
 * @module     local_equipment/partnership_courses
 * @copyright  2024 Joshua Kirby <josh@funlearningcompany.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Log from 'core/log';
import { get_string as getString } from 'core/str';

export const init = () => {
    Log.debug('init function called in partnership_courses.js');

    const partnershipSelect = document.getElementById('id_partnership');

    if (!partnershipSelect) {
        Log.error('partnershipSelect element not found');
        return;
    } else {
        Log.debug("'partnershipSelect' variable: ");
        Log.debug(partnershipSelect);
    }

    let partnershipData;
    try {
        partnershipData = JSON.parse(
            partnershipSelect.getAttribute('data-partnerships') || '{}'
        );
        Log.debug("'partnershipData' variable: ");
        Log.debug(partnershipData);
    } catch (e) {
        Log.error('Error parsing partnership data: ', e);
        return;
    }
    // Use async/await for better readability and error handling
    const updateStudentCourses = async (partnershipId) => {
        Log.debug(
            'updateStudentCourses function called in partnership_courses.js'
        );
        Log.debug('partnershipId argument passed in: ');
        Log.debug(partnershipId);

        const courses = partnershipData[partnershipId] || [];
        Log.debug("'courses' variable: ");
        Log.debug(courses);

        const selects = document.querySelectorAll(
            'select[name^="student_courses["]'
        );
        Log.debug("'selects' variable: ");
        Log.debug(selects);
        for (const select of selects) {
            await updateCourseOptions(select, courses);
        }
    };

    // Change: Modified to handle previously selected courses
    const updateCourseOptions = async (select, courses) => {
        Log.debug(
            'updateCourseOptions function called in partnership_courses.js'
        );
        Log.debug("'select' arg passed: ");
        Log.debug(select);
        Log.debug("'courses' arg passed: ");
        Log.debug(courses);

        // Retrieve previously selected courses
        const previouslySelected = JSON.parse(
            select.getAttribute('data-selected') || '[]'
        );
        Log.debug("'previouslySelected' variable: ");
        Log.debug(previouslySelected);

        select.innerHTML = '';
        if (courses.length === 0) {
            Log.debug('courses length is 0...');
            const noCourseString = await getString(
                'nocoursesavailable',
                'local_equipment'
            );
            const option = document.createElement('option');
            option.value = '';
            option.textContent = noCourseString;
            option.disabled = true;
            select.appendChild(option);

            Log.debug("'noCourseString' variable: ");
            Log.debug(noCourseString);
            Log.debug("'option' variable: ");
            Log.debug(option);
        } else {
            Log.debug('courses length is not 0...');
            courses.forEach((course) => {
                const option = document.createElement('option');
                option.value = course.id;
                option.textContent = course.fullname;
                // Restore previously selected state
                option.selected = previouslySelected.includes(
                    course.id.toString()
                );
                select.appendChild(option);

                Log.debug("'option' variable: ");
                Log.debug(option);
            });
        }
        // Call preserveSelectedCourses after updating options
        preserveSelectedCourses();
    };

    // New function: Preserve selected courses
    const preserveSelectedCourses = () => {
        Log.debug(
            'preserveSelectedCourses function called in partnership_courses.js'
        );
        document
            .querySelectorAll('select[name^="student_courses["]')
            .forEach((select) => {
                select.addEventListener('change', () => {
                    const selectedOptions = Array.from(
                        select.selectedOptions
                    ).map((option) => option.value);
                    select.setAttribute(
                        'data-selected',
                        JSON.stringify(selectedOptions)
                    );
                    Log.debug("'option' variable: ");
                    Log.debug(selectedOptions);
                });
            });
    };

    partnershipSelect.addEventListener('change', (event) => {
        updateStudentCourses(event.target.value);
    });

    // Initial update
    updateStudentCourses(partnershipSelect.value);

    // Call preserveSelectedCourses initially
    preserveSelectedCourses();

    Log.debug('###### End init function for partnership_courses.js ######');
};
