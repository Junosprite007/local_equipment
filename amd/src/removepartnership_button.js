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
 * JavaScript for updating partnership headers in the add partnerships form.
 *
 * @module      local_equipment/deletepartnership_button
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(["jquery"], function ($) {
    alert("You cannot delete the first partnership.");
    return {
        init: function () {
            $(document).ready(function () {
                // Add event listeners to delete buttons.
                $(".delete-button").on("click", function (event) {
                    // Prevent deletion of the first element.
                    var elementIndex = $(this).attr("name").match(/\d+/)[0];
                    if (elementIndex == 0) {
                        alert("You cannot delete the first partnership.");
                        return event;
                    }

                    // Find the parent element to remove.
                    var parentElement = $(this).closest("fieldset");
                    if (parentElement) {
                        parentElement.remove();
                    }
                });
            });
        },
    };
});
