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
    const partnershipSelect = document.getElementById('id_partnership');

    if (!partnershipSelect) {
        Log.error('Partnership select element not found');
        return;
    }

    let partnershipData;
    try {
        partnershipData = JSON.parse(
            partnershipSelect.getAttribute('data-partnerships') || '{}'
        );
    } catch (e) {
        Log.error('Error parsing partnership data:', e);
        return;
    }
    // Use async/await for better readability and error handling
    const updateStudentCourses = async (partnershipId) => {
        const courses = partnershipData[partnershipId] || [];
        Log.debug('Courses: ');
        Log.debug(courses);
        const selects = document.querySelectorAll(
            'select[name^="student_courses["]'
        );
        for (const select of selects) {
            await updateCourseOptions(select, courses);
        }
    };
    // const updateStudentCourses = async (partnershipId) => {
    //     const courses = partnershipData[partnershipId] || [];
    //     Log.debug('Courses:', courses);
    //     const selects = document.querySelectorAll('select[name^="courses["]');
    //     for (const select of selects) {
    //         await updateCourseOptions(select, courses);
    //     }
    // };

    // Change: Modified to handle previously selected courses
    const updateCourseOptions = async (select, courses) => {
        // Retrieve previously selected courses
        const previouslySelected = JSON.parse(
            select.getAttribute('data-selected') || '[]'
        );
        select.innerHTML = '';
        if (courses.length === 0) {
            const noCourseString = await getString(
                'nocoursesavailable',
                'local_equipment'
            );
            const option = document.createElement('option');
            option.value = '';
            option.textContent = noCourseString;
            option.disabled = true;
            select.appendChild(option);
        } else {
            courses.forEach((course) => {
                const option = document.createElement('option');
                option.value = course.id;
                option.textContent = course.fullname;
                // Restore previously selected state
                option.selected = previouslySelected.includes(
                    course.id.toString()
                );
                select.appendChild(option);
            });
        }
        // Call preserveSelectedCourses after updating options
        preserveSelectedCourses();
    };

    // const updateCourseOptions = async (select, courses) => {
    //     select.innerHTML = '';
    //     if (courses.length === 0) {
    //         const noCourseString = await getString(
    //             'nocoursesavailable',
    //             'local_equipment'
    //         );
    //         const option = document.createElement('option');
    //         option.value = '';
    //         option.textContent = noCourseString;
    //         option.disabled = true;
    //         select.appendChild(option);
    //     } else {
    //         courses.forEach((course) => {
    //             const option = document.createElement('option');
    //             option.value = course.id;
    //             option.textContent = course.fullname;
    //             select.appendChild(option);
    //         });
    //     }
    // };

    // New function: Preserve selected courses
    const preserveSelectedCourses = () => {
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
};
