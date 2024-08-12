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
 * JavaScript for managing multiple pickups in the add pickups form.
 *
 * @module      local_equipment/addpickup_form
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery", "core/str"], function ($, Str) {
    return {
        init: function () {
            var module = this;

            $(document).ready(function () {
                module.updatePickupNumbers();
                module.updateTrashIcons();

                $(document).on(
                    "click",
                    ".local-equipment-remove-pickup",
                    function (e) {
                        e.preventDefault();
                        module.removePickup($(this));
                    }
                );

                $("#id_addpickup").on("click", function () {
                    setTimeout(function () {
                        module.updatePickupNumbers();
                        module.updateTrashIcons();
                    }, 100);
                });
            });
        },

        updatePickupNumbers: function () {
            $(".local-equipment-pickup-header").each(function (index) {
                Str.get_string("pickup", "local_equipment", index + 1).then(
                    function (string) {
                        $(this).text(string);
                    }.bind(this)
                );
            });
        },

        updateTrashIcons: function () {
            var pickups = $('fieldset[id^="id_pickupheader_"]');
            if (pickups.length > 1) {
                $(".local-equipment-remove-pickup").show();
            } else {
                $(".local-equipment-remove-pickup").hide();
            }
        },

        removePickup: function (button) {
            var pickup = button.closest("fieldset");
            pickup.remove();
            this.updatePickupNumbers();
            this.updateTrashIcons();
            this.updateHiddenFields();
        },

        updateHiddenFields: function () {
            var pickupCount = $('fieldset[id^="id_pickupheader_"]').length;
            $('input[name="pickups"]').val(pickupCount);

            var url = new URL(window.location.href);
            url.searchParams.set("repeatno", pickupCount);
            window.history.replaceState({}, "", url);
        },
    };
});
