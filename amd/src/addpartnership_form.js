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
 * JavaScript for deleting partnerships in the add partnerships form.
 *
 * @module      local_equipment/addpartnership_form
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery", "core/log", "core/str"], ($, log, Str) => {
    return {
        init: () => {
            $(document).ready(function () {
                log.debug("Add Partnership Form JS initialized");

                const updatePartnershipNumbers = () => {
                    $(".local-equipment-partnership-header").each(
                        (index, element) => {
                            Str.get_string(
                                "partnership",
                                "local_equipment",
                                index + 1
                            )
                                .then((string) => {
                                    $(element).text(string);
                                })
                                .catch((error) => {
                                    log.error(
                                        "Error updating partnership header:",
                                        error
                                    );
                                });
                        }
                    );
                };

                const updateHiddenFields = () => {
                    log.debug("updateHiddenFields");
                    const partnershipsCount = $("fieldset").length;
                    log.debug(`Number of fieldsets: ${partnershipsCount}`);
                    $('input[name="partnerships"]').val(partnershipsCount);

                    // Update the URL if necessary
                    const url = new URL(window.location.href);
                    url.searchParams.set("repeatno", partnershipsCount);
                    window.history.replaceState({}, "", url);
                };

                const renumberFormElements = () => {
                    log.debug("renumberFormElements");
                    $("fieldset").each((index, fieldset) => {
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
                    const partnerships = $("fieldset");
                    if (partnerships.length > 1) {
                        $(".local-equipment-remove-partnership").show();
                    } else {
                        $(".local-equipment-remove-partnership").hide();
                    }
                };

                // updatePartnershipNumbers();
                // updateHiddenFields();
                updateTrashIcons();

                $(document).on(
                    "click",
                    ".local-equipment-remove-partnership",
                    function () {
                        log.debug("Event triggered");
                        const $fieldset = $(this).closest("fieldset");
                        // log.debug($fieldset);
                        // log.debug($fieldset.is(":first-of-type"));

                        // const isFirstPartnership =
                        //     $fieldset.is(":first-of-type");

                        // if (isFirstPartnership) {
                        //     Str.get_string(
                        //         "cannotremovefirstpartnership",
                        //         "local_equipment"
                        //     )
                        //         .then((string) => {
                        //             alert(string);
                        //         })
                        //         .catch((error) => {
                        //             log.error("Error getting string:", error);
                        //         });
                        //     return;
                        // }

                        log.debug($fieldset);
                        const removedfieldset = $fieldset.remove();
                        log.debug("Fieldset removed");
                        log.debug(
                            "Here's what returned from the '$fieldset.remove()' command:"
                        );
                        log.debug(removedfieldset);
                        updatePartnershipNumbers();
                        updateHiddenFields();
                        renumberFormElements();
                        updateTrashIcons();
                    }
                );
            });
        },
    };
});

