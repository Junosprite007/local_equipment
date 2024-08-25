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
import { get_string as getString } from 'core/str';

export const init = () => {
    const partnershipSelect = document.getElementById('id_partnership');
    const selectedCoursesInput = document.getElementById('id_selectedcourses');

    if (!partnershipSelect || !selectedCoursesInput) {
        return;
    }

    const partnershipData = JSON.parse(
        partnershipSelect.getAttribute('data-partnerships') || '{}'
    );

    const updateStudentCourses = async (partnershipId) => {
        const courses = partnershipData[partnershipId] || [];
        const selects = document.querySelectorAll(
            'select[name^="student_courses["]'
        );
        for (const select of selects) {
            await updateCourseOptions(select, courses);
        }
    };

    const updateCourseOptions = async (select, courses) => {
        const studentIndex = select.name.match(/\[(\d+)\]/)[1];
        select.setAttribute('data-student-index', studentIndex);

        const previouslySelected = JSON.parse(
            select.getAttribute('data-selected') || '[]'
        );
        select.innerHTML = '';

        if (courses.length === 0) {
            const noCourseString = await getString(
                'nocoursesavailable',
                'local_equipment'
            );
            select.innerHTML = `<option value="" disabled>${noCourseString}</option>`;
        } else {
            courses.forEach((course) => {
                const option = document.createElement('option');
                option.value = course.id;
                option.textContent = course.fullname;
                option.selected = previouslySelected.includes(
                    course.id.toString()
                );
                select.appendChild(option);
            });
        }
        // validateCourseSelection(select);
    };

    const updateSelectedCourses = () => {
        const allSelects = document.querySelectorAll(
            'select[name^="student_courses["]'
        );
        const selectedCourses = {};
        allSelects.forEach((select) => {
            const studentIndex = select.getAttribute('data-student-index');
            selectedCourses[studentIndex] = Array.from(
                select.selectedOptions
            ).map((option) => option.value);
        });
        selectedCoursesInput.value = JSON.stringify(selectedCourses);
    };

    // const validateCourseSelection = (select) => {
    //     const selectedCourses = Array.from(select.selectedOptions).map(
    //         (option) => option.value
    //     );
    //     const errorElement =
    //         select.parentNode.querySelector('.invalid-feedback');

    //     if (selectedCourses.length === 0) {
    //         select.classList.add('is-invalid');
    //         if (errorElement) {
    //             errorElement.textContent = 'Please select at least one course.';
    //             errorElement.style.display = 'block';
    //         }
    //     } else {
    //         select.classList.remove('is-invalid');
    //         if (errorElement) {
    //             errorElement.textContent = '';
    //             errorElement.style.display = 'none';
    //         }
    //     }
    // };

    document
        .querySelectorAll('select[name^="student_courses["]')
        .forEach((select) => {
            select.addEventListener('change', () => {
                select.setAttribute(
                    'data-selected',
                    JSON.stringify(
                        Array.from(select.selectedOptions).map(
                            (option) => option.value
                        )
                    )
                );
                updateSelectedCourses();
                // validateCourseSelection(select);
            });
        });

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', updateSelectedCourses);
    }

    partnershipSelect.addEventListener('change', (event) => {
        updateStudentCourses(event.target.value);
    });

    updateStudentCourses(partnershipSelect.value);
    updateSelectedCourses();

    // if (typeof M.form !== 'undefined' && M.form.dependencyManager) {
    //     M.form.dependencyManager.add_dependency({
    //         element: document.querySelectorAll(
    //             'select[name^="student_courses["]'
    //         ),
    //         callback: validateCourseSelection,
    //     });
    // }
};
