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

define(["jquery", "core/str", "core/log"], function ($, Str, Log) {
	"use strict";

	/**
	 * Initialize the module.
	 */
	function init() {
		Str.get_string("partnership", "local_equipment")
			.done(function (partnershipstring) {
				setupEventListeners(partnershipstring);
			})
			.fail(Log.error);
		console.log("AMD module initialized");
	}
	/**
	 * Set up event listeners for partnership name inputs.
	 *
	 * @param {string} partnershipstring The localized string for 'partnership'.
	 */
	// function setupEventListeners(partnershipstring) {
	// 	$(document).on("input", 'input[name^="name"]', function () {
	// 		updatePartnershipHeader($(this), partnershipstring);
	// 	});
	// }
	function setupEventListeners(partnershipString) {
		$(document).on("input", ".partnership-name-input", function () {
			updatePartnershipHeader($(this), partnershipString);
		});
	}

	/**
	 * Update the partnership header based on the input value.
	 *
	 * @param {jQuery} $input The input element that triggered the event.
	 * @param {string} partnershipstring The localized string for 'partnership'.
	 */
	// function updatePartnershipHeader($input, partnershipstring) {
	// 	var index = $input.closest(".fitem").index(".fitem");
	// 	var $header = $("partnership_header_" + (index + 1));

	// 	if ($header.length) {
	// 		var headerText = $input.val()
	// 			? partnershipstring + " (" + $input.val() + ")"
	// 			: partnershipstring + " " + (index + 1);
	// 		$header.text(headerText);
	// 	} else {
	// 		Log.debug("Header element not found for index: " + index);
	// 	}
	// }

	function updatePartnershipHeader($input, partnershipString) {
		var index = $input.closest(".fitem").index(".fitem");
		var $header = $("#id_partnership_header_" + (index + 1));

		if ($header.length) {
			var headerText = $input.val()
				? $input.val()
				: partnershipString + " " + (index + 1);
			$header.text(headerText);
		} else {
			Log.debug("Header element not found for index: " + index);
		}
	}

	return {
		init: init,
	};
});
