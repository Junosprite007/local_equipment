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
 * JavaScript for the add pickups form.
 *
 * @module     local_equipment/addpickups_form
 * @copyright  2024 Joshua Kirby <josh@funlearningcompany.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import { get_string as getString } from 'core/str';
import Log from 'core/log';

/**
 * Initialize the add pickups form functionality.
 */
export const init = () => {
    Log.debug('Add Pickups Form JS initialized');
    setupPickupsHandling();
};

/**
 * Set up pickups handling.
 */
const setupPickupsHandling = () => {
    const selector = "fieldset[id^='id_pickupheader_']";

    $(document).on('click', '.local-equipment-remove-pickup', function () {
        const $fieldset = $(this).closest(selector);
        const removedFieldset = $fieldset.remove();
        Log.debug('Fieldset removed');
        Log.debug(
            "Here's what returned from the '$fieldset.remove()' command:"
        );
        Log.debug(removedFieldset);
        updatePickupNumbers();
        updateHiddenFields();
        renumberFormElements();
        updateTrashIcons();
    });

    updateTrashIcons();
};

/**
 * Update pickup numbers.
 */
const updatePickupNumbers = () => {
    $('.local-equipment-pickup-header').each((index, element) => {
        getString('pickup', 'local_equipment', index + 1)
            .then((string) => {
                $(element).text(string);
            })
            .catch((error) => {
                Log.error('Error updating pickup header:', error);
            });
    });
};

/**
 * Update hidden fields.
 */
const updateHiddenFields = () => {
    const pickupsCount = $("fieldset[id^='id_pickupheader_']").length;
    $('input[name="pickups"]').val(pickupsCount);

    // Update the URL if necessary
    const url = new URL(window.location.href);
    url.searchParams.set('repeatno', pickupsCount);
    window.history.replaceState({}, '', url);
};

/**
 * Renumber form elements.
 */
const renumberFormElements = () => {
    $("fieldset[id^='id_pickupheader_']").each((index, fieldset) => {
        Log.debug(`Renumbering fieldset ${index}`);
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
    const pickups = $("fieldset[id^='id_pickupheader_']");
    if (pickups.length > 1) {
        $('.local-equipment-remove-pickup').show();
    } else {
        $('.local-equipment-remove-pickup').hide();
    }
};
