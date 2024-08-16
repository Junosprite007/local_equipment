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
 * JavaScript for deleting pickups in the add pickups form.
 *
 * @module      local_equipment/addpickups_form
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.util.js_pending("local_equipment/addpickups_form");

define(["jquery", "core/log", "core/str"], ($, log, Str) => {
    return {
        init: () => {
            $(document).ready(function () {
                const selector = "fieldset[id^='id_pickupheader_']";
                log.debug("Add Pickup Form JS initialized");

                const updatePickupNumbers = () => {
                    log.debug("updatePickupNumbers");
                    $(".local-equipment-pickup-header").each(
                        (index, element) => {
                            Str.get_string(
                                "pickup",
                                "local_equipment",
                                index + 1
                            )
                                .then((string) => {
                                    $(element).text(string);
                                })
                                .catch((error) => {
                                    log.error(
                                        "Error updating pickup header:",
                                        error
                                    );
                                });
                        }
                    );
                };

                const updateHiddenFields = () => {
                    log.debug("updateHiddenFields");
                    const pickupsCount = $(selector).length;
                    log.debug(`Number of fieldsets: ${pickupsCount}`);
                    $('input[name="pickups"]').val(pickupsCount);

                    // Update the URL if necessary
                    const url = new URL(window.location.href);
                    url.searchParams.set("repeatno", pickupsCount);
                    window.history.replaceState({}, "", url);
                };

                const renumberFormElements = () => {
                    log.debug("renumberFormElements");
                    $(selector).each((index, fieldset) => {
                        log.debug(`Renumbering fieldset ${index}`);
                        $(fieldset)
                            .find("input, select, textarea")
                            .each((_, element) => {
                                const name = $(element).attr("name");
                                if (name) {
                                    const newName = name.replace(
                                        /\[\d+\]/,
                                        `[${index}]`
                                    );
                                    $(element).attr("name", newName);
                                }
                                const id = $(element).attr("id");
                                if (id) {
                                    const newId = id.replace(
                                        /_\d+_/,
                                        `_${index}_`
                                    );
                                    $(element).attr("id", newId);
                                }
                            });
                    });
                };

                const updateTrashIcons = () => {
                    log.debug("updateTrashIcons");
                    const pickups = $(selector);
                    log.debug("pickups");
                    log.debug(pickups);
                    log.debug('$(".local-equipment-remove-pickup")');
                    log.debug($(".local-equipment-remove-pickup"));
                    if (pickups.length > 1) {
                        log.debug("if");
                        $(".local-equipment-remove-pickup").show();
                    } else {
                        log.debug("else");
                        $(".local-equipment-remove-pickup").hide();
                    }
                };

                $(document).on(
                    "click",
                    ".local-equipment-remove-pickup",
                    function () {
                        log.debug("Event triggered");
                        const $fieldset = $(this).closest(selector);
                        log.debug($fieldset);
                        const removedfieldset = $fieldset.remove();
                        log.debug("Fieldset removed");
                        log.debug(
                            "Here's what returned from the '$fieldset.remove()' command:"
                        );
                        log.debug(removedfieldset);
                        updatePickupNumbers();
                        updateHiddenFields();
                        renumberFormElements();
                        updateTrashIcons();
                    }
                );

                updateTrashIcons();
            });
            M.util.js_complete("local_equipment/addpickups_form");
        },
    };
});
