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
 * Mass text form JavaScript functionality.
 *
 * @module     local_equipment/mass_text_form
 * @copyright  2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification', 'core/log'], function (
    $,
    Ajax,
    Notification,
    Log
) {
    'use strict';

    /**
     * Initialize the mass text form functionality.
     *
     * @return {void}
     */
    const init = function () {
        const messageTextarea = $('#id_message');
        const charCountDisplay = $('#char-counter');
        const recipientCountDisplay = $('#recipient-count');
        const formElement = $('#mass-text-form'); // More specific form selector

        // Get max length from data attribute or use default
        const maxLength = parseInt(messageTextarea.data('max-length')) || 250;

        // Safety check - ensure required elements exist
        if (!messageTextarea.length || !charCountDisplay.length) {
            Log.debug(
                'local_equipment/mass_text_form: Required elements not found'
            );
            return;
        }

        /**
         * Update character count display with proper string replacement.
         *
         * @return {void}
         */
        const updateCharCount = function () {
            const currentLength = messageTextarea.val().length;
            const remaining = maxLength - currentLength;

            // Update the character count text properly
            if (remaining >= 0) {
                charCountDisplay.text(remaining + ' characters remaining');
            } else {
                charCountDisplay.text(
                    Math.abs(remaining) + ' characters over limit'
                );
            }

            // Update styling based on remaining characters
            if (remaining < 0) {
                charCountDisplay
                    .removeClass('text-warning text-muted')
                    .addClass('text-danger');
            } else if (remaining < 20) {
                charCountDisplay
                    .removeClass('text-muted text-danger')
                    .addClass('text-warning');
            } else {
                charCountDisplay
                    .removeClass('text-danger text-warning')
                    .addClass('text-muted');
            }
        };

        /**
         * Load recipient count via AJAX using Moodle web services.
         *
         * @return {void}
         */
        const loadRecipientCount = function () {
            Ajax.call([
                {
                    methodname: 'local_equipment_get_recipient_count',
                    args: {},
                    done: function (response) {
                        Log.debug('Web service response:', response); // DEBUG: See what we get back

                        if (response.success) {
                            recipientCountDisplay.text(
                                response.count + ' parents'
                            );

                            // Update styling based on count
                            if (response.count === 0) {
                                recipientCountDisplay
                                    .removeClass('text-success')
                                    .addClass('text-warning');
                            } else {
                                recipientCountDisplay
                                    .removeClass('text-warning')
                                    .addClass('text-success');
                            }
                        } else {
                            recipientCountDisplay.text('Error loading count');
                            recipientCountDisplay
                                .removeClass('text-success text-warning')
                                .addClass('text-danger');

                            Log.error(
                                'local_equipment/mass_text_form: Recipient count error: ' +
                                    response.error
                            );
                        }
                    },
                    fail: function (error) {
                        Log.debug('Web service error details:', error); // DEBUG: See exact error

                        recipientCountDisplay.text('Error loading count');
                        recipientCountDisplay
                            .removeClass('text-success text-warning')
                            .addClass('text-danger');

                        Log.error(
                            'local_equipment/mass_text_form: Web service error loading recipient count: ' +
                                (error.message || 'Unknown error')
                        );
                    },
                },
            ]);
        };

        /**
         * Handle form submission with validation.
         *
         * @param {Event} e - The form submission event
         * @return {boolean} - Whether to allow form submission
         */
        const handleFormSubmit = function (e) {
            const message = messageTextarea.val().trim();

            if (message.length === 0) {
                e.preventDefault();
                Notification.addNotification({
                    message: 'Please enter a message',
                    type: 'error',
                });
                return false;
            }

            if (message.length > maxLength) {
                e.preventDefault();
                Notification.addNotification({
                    message:
                        'Message is too long. Maximum ' +
                        maxLength +
                        ' characters allowed',
                    type: 'error',
                });
                return false;
            }

            // Show confirmation dialog
            if (
                !confirm(
                    'Are you sure you want to send this message to all parents?'
                )
            ) {
                e.preventDefault();
                return false;
            }

            return true;
        };

        // Initialize functionality
        messageTextarea.on('input keyup paste', updateCharCount);
        updateCharCount(); // Initial character count

        if (recipientCountDisplay.length) {
            loadRecipientCount();
        }

        // Bind form submission handler to specific form
        if (formElement.length) {
            formElement.on('submit', handleFormSubmit);
        } else {
            // Fallback to any form if specific form not found
            $('form').on('submit', handleFormSubmit);
            Log.debug(
                'local_equipment/mass_text_form: Using fallback form selector'
            );
        }
    };

    return {
        init: init,
    };
});
