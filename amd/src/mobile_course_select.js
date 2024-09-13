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
 * @module     local_equipment/mobile_course_select
 * @copyright  2024 Joshua Kirby <josh@funlearningcompany.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialize the course multi-select box for mobile.
 */
export const init = () => {
    replaceMobileSelect();
};

const isMobile = () => {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
        navigator.userAgent
    );
};

const createCheckboxes = (select) => {
    const container = document.createElement('div');
    container.className = 'mobile-course-checkboxes';

    Array.from(select.options).forEach((option) => {
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.value = option.value;
        checkbox.id = `course-${option.value}`;
        checkbox.name = 'student_courses[]';

        const label = document.createElement('label');
        label.htmlFor = `course-${option.value}`;
        label.textContent = option.textContent;

        const wrapper = document.createElement('div');
        wrapper.appendChild(checkbox);
        wrapper.appendChild(label);

        container.appendChild(wrapper);
    });

    return container;
};

const replaceMobileSelect = () => {
    if (isMobile()) {
        const selects = document.querySelectorAll(
            'select[name="student_courses[]"]'
        );
        selects.forEach((select) => {
            const checkboxes = createCheckboxes(select);
            select.parentNode.replaceChild(checkboxes, select);
        });
    }
};
