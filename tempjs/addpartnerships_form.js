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
 * @module     local_equipment/addpartnerships_form
 * @copyright  2024 Joshua Kirby <josh@funlearningcompany.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import { get_string as getString } from 'core/str';
import Log from 'core/log';

/**
 * Initialize the add partnerships form functionality.
 * Remember that Moodle typically uses 'fieldset' type elements to encapsulate grouped form elements.
 *
 * For example, the 'addpartnerships_form.php' page allows users to add multiple partnerships. Each partnership
 * is encapsulated in its own 'fieldset' element. This is the Moodle convention I'm referring to.
 * The 'fieldset' element has an 'id' attribute, which in the example above would look like:
 * 'id_partnershipheader_0', 'id_partnershipheader_1', 'id_partnershipheader_2', etc. To make certain words
 * within the ids more dynamic, we can add variable within the jQuery selector string, like so:
 * "const selector = `fieldset[id^='id_${name}${type}_']`;". Take note of the backticks (`)
 * and the dollar sign ($) syntax.
 *
 * @param {string} name The subject name of the feature or sub-feature you're working with, such as 'student' or 'partnership'.
 * @param {string} type The general type of element, not necessarily the actual HTML element name, such as 'header'.
 * @param {string} elSelector The name of the element you want to select with jQuery, such as 'fieldset' or 'input'.
 * @param {string} attribute The name if the attribute you want to select, such as 'id'.
 */
//  * @param {string} index The index of the element. Helpful if you want to effect only the first element (0) for example.

export const init = (
    name,
    type,
    elSelector = 'fieldset',
    attribute = 'id'
) => {
    Log.debug('init function called in partnership_courses.js');
    Log.debug(`'name' arg: ${name}`);
    Log.debug(`'type' arg: ${type}`);
    Log.debug(`'elSelector' arg: ${elSelector}`);
    Log.debug(`'attribute' arg: ${attribute}`);

    const jqSelector = `${elSelector}[${attribute}^='${attribute}_${name}${type}_']`;
    Log.debug(`'attribute' arg: ${jqSelector}`);

    removeFieldsetHandler(name, jqSelector);
    Log.debug('###### End init function for partnership_courses.js ######');
};

/**
 * Handle the removal of one of the repeated <fieldset> elements on the form.
 * In other words, this function is responsible for removing something like a partnerships, a student, stuff like that.
 *
 * @param {string} name The subject name of the feature or sub-feature you're working with, such as 'student' or 'partnership'.
 * @param {string} jqSelector The jQuery selector string, which may look like this: `fieldset[id^='id_partnershipheader_']`.
 */
const removeFieldsetHandler = (name, jqSelector) => {
    Log.debug(
        'removeFieldsetHandler function called in partnership_courses.js'
    );

    $(document).on('click', `.local-equipment-remove-${name}`, (event) => {
        Log.debug(
            `'$(event.currentTarget)' variable: ${$(event.currentTarget)}`
        );
    });
    $(document).on('click', `.local-equipment-remove-${name}`, function () {
        Log.debug(`'$(event.currentTarget)' variable: ${$(this)}`);
    });
    // Here's an example of how the jqSelector string would look like without the variables:
    // `fieldset[id^='id_partnershipheader_']` .

    Log.debug("'jqSelector' variable: ");
    Log.debug(jqSelector);

    // The class '.local-equipment-remove-partnership' is (or should) attached to delete button element.
    // Remove the whatever element encapsulates the repeated sections of the form.
    // This will usually be a 'fieldset' element.
    $(document).on('click', `.local-equipment-remove-${name}`, (event) => {
        const removedID = removeElement(event, jqSelector);
        // updateRemainingElementIDs(jqSelector, extractIDNumber(removedID));
        updateHiddenFields(jqSelector);
        renumberFormElements(jqSelector);
        updateTrashIcons(jqSelector);
    });

    updateTrashIcons();
    Log.debug(
        '###### End removeFieldsetHandler function for partnership_courses.js ######'
    );
};

/**
 * Handle the removal of one of the repeated <fieldset> elements on the form.
 * In other words, this function is responsible for removing something like a partnerships, a student, stuff like that.
 *
 * @param {string} event The subject name of the feature or sub-feature you're working with, such as 'student' or 'partnership'.
 * @param {string} jqSelector The jQuery selector string, which may look like this: `fieldset[id^='id_partnershipheader_']`.
 */
const removeElement = (event, jqSelector) => {
    Log.debug('removeElement function called in partnership_courses.js');
    // Here's an example of how the jqSelector string would look like without the variables:
    // `fieldset[id^='id_partnershipheader_']` .

    // The 'event.currentTarget' below is referring to the HTML element (currentTarget) that the event (click the mouse)
    // is happening to, which in this case, is the delete button element.
    // We're getting the closest element to the button tag. Probably a 'fieldset' element.
    const $element = $(event.currentTarget).closest(jqSelector);
    Log.debug("'$element' variable: ");
    Log.debug($element);

    const id = $element.attr('id');
    Log.debug(`'id' variable: ${id}`);

    // Remove the element.
    $element.remove();
    Log.debug("Here's '$element' after '$element.remove()': ");
    Log.debug($element);
    Log.debug(
        `###### End removeElement function for partnership_courses.js. Returning 'id': ${id} ######`
    );
    return id;
};

/**
 * Extracts the number from the end of a fieldset id that follows the pattern 'id_*_number'.
 * @param {string} id - The jQuery object representing the fieldset element.
 * @return {string|null} The extracted number or null if not found or if the element doesn't match the expected pattern.
 */
const extractIDNumber = (id) => {
    Log.debug('extractIDNumber function called in partnership_courses.js');
    Log.debug(`'id' variable: ${id}`);

    const match = id.match(/(\d+)$/);
    Log.debug("'match' variable: ");
    Log.debug(match);

    Log.debug(
        '###### End extractIDNumber function for partnership_courses.js. ######'
    );
    if (match) {
        Log.debug(`Returning 'match[1]' ${match[1]} from fieldset id ${id}`);
        return match[1];
    } else {
        Log.warn(
            `Could not extract number from fieldset id ${id}. Returning 'null'`
        );
        return null;
    }
};

/**
 * Updates the IDs of remaning elements after one is removed.
 * @param {jQuery} selector - The jQuery selector for fieldsets.
 * @param {string} removedNumber - The number of the removed fieldset.
 */
const updateRemainingElementIDs = (selector, removedNumber) => {
    Log.debug(
        'updateRemainingElementIDs function called in partnership_courses.js'
    );
    const $elements = $(selector);
    Log.debug(`'id' variable: ${$elements}`);

    $elements.each((index, element) => {
        const $element = $(element);
        const currentId = $element.attr('id');
        const currentNumber = extractIDNumber(currentId);
        const removedInt = parseInt(removedNumber, 10);

        if (currentNumber && index >= removedInt) {
            // This means we are currently on the element that comes after the removed element.
            // So we'll need to update the id of this element by subtracting 1 from the current number at the end of the id.
            const newNumber = index.toString();
            // const match = currentId.match(/(\d+)$/);
            try {
                // Update element id
                const newId = currentId.replace(/(\d+)$/, newNumber);
                $element.attr('id', newId);

                // Update internal elements' ids
                $element.find('[id]').each((_, element) => {
                    // Even though we're on the element already, we still need to select it with actual jQuery.
                    const $element = $(element);
                    const elementId = $element.attr('id');
                    const newElementId = elementId.replace(/\d+$/, newNumber);
                    $element.attr('id', newElementId);
                });

                // Update 'name' attributes
                $element.find('[name]').each((_, element) => {
                    const $element = $(element);
                    const elementName = $element.attr('name');
                    const newElementName = elementName.replace(
                        /\[\d+\]/,
                        `[${newNumber}]`
                    );
                    $element.attr('name', newElementName);
                });

                Log.debug(`Updated element from ${currentId} to ${newId}`);
            } catch (error) {
                Log.error(
                    `Error updating element ${currentId}: ${error.message}`
                );
            }
        }

        // if (
        //     currentNumber &&
        //     parseInt(currentNumber, 10) > parseInt(removedNumber, 10)
        // ) {
        //     const newNumber = parseInt(currentNumber, 10) - 1;

        // }
    });
    Log.debug(
        '###### End updateRemainingElementIDs function for partnership_courses.js. ######'
    );
};

/**
 * Update partnership numbers.
 * @param {string} name The jQuery selector string, which may look like this: `fieldset[id^='id_partnershipheader_']`.
 * @param {number} number The id number of partnerships.
 */
const updateIDNumbers = (name, number) => {
    Log.debug('updateIDNumbers function called in partnership_courses.js');

    $(`.local-equipment-${name}-header`).each((index, element) => {
        Log.debug(`'index' variable: ${index}`);
        Log.debug("'element' variable: ");
        Log.debug(element);

        getString('partnership', 'local_equipment', index + 1)
            .then((string) => {
                Log.debug(`'string' variable: ${string}`);

                $(element).text(string);
                return string;
            })
            .catch((error) => {
                Log.error('Error updating partnership header:', error);
            });
    });

    Log.debug(
        '###### End updateIDNumbers function for partnership_courses.js ######'
    );
};

/**
 * Update hidden fields.
 * @param {string} jqSelector The jQuery selector string, which may look like this: `fieldset[id^='id_partnershipheader_']`.
 */
const updateHiddenFields = (jqSelector) => {
    Log.debug('updateHiddenFields function called in partnership_courses.js');

    const partnershipsCount = $(jqSelector).length;
    Log.debug("'partnershipsCount' variable: ");
    Log.debug(partnershipsCount);

    const inputValue = $('input[name="partnerships"]').val(partnershipsCount);
    Log.debug("'inputValue' variable: ");
    Log.debug(inputValue);

    // Update the URL if necessary
    const url = new URL(window.location.href);
    Log.debug("'url' variable: ");
    Log.debug(url);

    url.searchParams.set('repeatno', partnershipsCount);
    window.history.replaceState({}, '', url);

    Log.debug(
        '###### End updateHiddenFields function for partnership_courses.js ######'
    );
};

// /**
//  * Renumber form elements.
//  * @param {string} jqSelector The jQuery selector string, which may look like this: `fieldset[id^='id_partnershipheader_']`.
//  */
// const renumberFormElements = (jqSelector) => {
//     Log.debug('renumberFormElements function called in partnership_courses.js');

//     $(jqSelector).each((index, fieldset) => {
//         Log.debug("'index' variable: ");
//         Log.debug(index);
//         Log.debug("'fieldset' variable: ");
//         Log.debug(fieldset);

//         // Find all 'input', 'select', and 'textarea' element decedents — children, childrens' children,
//         // childrens' childrens' children, you get the idea — within the fieldset element.
//         $(fieldset)
//             .find('input, select, textarea')
//             .each((_, element) => {
//                 // The underscore (_) is just the 'index' positional argument,
//                 // only it's not going to be used, hence the underscore.
//                 Log.debug("'_' variable: ");
//                 Log.debug(_);
//                 Log.debug("'element' variable: ");
//                 Log.debug(element);

//                 const name = $(element).attr('name');
//                 Log.debug("'name' variable: ");
//                 Log.debug(name);

//                 if (name) {
//                     const newName = name.replace(/\[\d+\]/, `[${index}]`);
//                     Log.debug("'newName' variable: ");
//                     Log.debug(newName);

//                     $(element).attr('name', newName);
//                 }
//                 const id = $(element).attr('id');
//                 Log.debug("'id' variable: ");
//                 Log.debug(id);

//                 if (id) {
//                     const newId = id.replace(/_\d+_/, `_${index}_`);
//                     Log.debug("'newId' variable: ");
//                     Log.debug(newId);

//                     $(element).attr('id', newId);
//                 }
//             });
//     });

//     Log.debug(
//         '###### End renumberFormElements function for partnership_courses.js ######'
//     );
// };

/**
 * Update trash icons visibility.
 * @param {string} jqSelector The jQuery selector string, which may look like this: `fieldset[id^='id_partnershipheader_']`.
 */
const updateTrashIcons = (jqSelector) => {
    Log.debug('updateTrashIcons function called in partnership_courses.js');

    const partnerships = $(jqSelector);
    if (partnerships.length > 1) {
        $('.local-equipment-remove-partnership').show();
    } else {
        $('.local-equipment-remove-partnership').hide();
    }

    Log.debug(
        '###### End updateTrashIcons function for partnership_courses.js ######'
    );
};
