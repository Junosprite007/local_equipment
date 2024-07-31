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
 * @module      local_equipment/addpartnership_form
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from "jquery";
import * as Str from "core/str";
import Log from "core/log";
import Notification from "core/notification";

const SELECTORS = {
    PARTNERSHIP_NAME_INPUT: ".partnership-name-input",
    PARTNERSHIP_HEADER: ".partnership-header",
};

/**
 * Initialize the module.
 */
export const init = () => {
    Str.get_string("partnership", "local_equipment")
        .then((partnershipString) => {
            setupEventListeners(partnershipString);
            Log.debug("AMD module initialized");
        })
        .catch((error) => {
            Log.error("Error initializing AMD module:", error);
        });
};

/**
 * Set up event listeners for partnership name inputs.
 *
 * @param {string} partnershipString The localized string for 'partnership'.
 */
const setupEventListeners = (partnershipString) => {
    $("body").on("input", SELECTORS.PARTNERSHIP_NAME_INPUT, function () {
        updatePartnershipHeader($(this), partnershipString);
    });
};

/**
 * Update the partnership header based on the input value.
 *
 * @param {jQuery} $input The input element that triggered the event.
 * @param {string} partnershipString The localized string for 'partnership'.
 */
const updatePartnershipHeader = ($input, partnershipString) => {
    const $header = $input.closest(".fitem").find(SELECTORS.PARTNERSHIP_HEADER);
    if ($header.length) {
        const headerText = $input.val() ? $input.val() : partnershipString;
        $header.text(headerText);
    } else {
        Log.debug("Header element not found for input");
    }
};

/**
 * Display an alert box. It's actually working!
 *
 * @param {string} title - The title of the alert.
 * @param {string} message - The message of the alert.
 */
export const showAlert = (title, message) => {
    Notification.alert(title, message);
};
