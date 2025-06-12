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
 * Mass text dynamic form JavaScript functionality.
 *
 * @module     local_equipment/mass_text_dynamic_form
 * @copyright  2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import { get_string as getString } from 'core/str';
import ModalForm from 'core_form/modalform';
import { add as addToast } from 'core/toast';
import Log from 'core/log';

/**
 * Initialize the mass text dynamic form functionality.
 *
 * @return {void}
 */
const init = () => {
    let retryCount = 0;
    const maxRetries = 50; // 5 seconds max (50 * 100ms)

    // Wait for DOM to be ready and try multiple times if elements aren't found
    const initializeForm = () => {
        // Use more robust selectors that work with dynamic forms
        const messageTextarea =
            document.querySelector('textarea[name="message"]') ||
            document.querySelector('#id_message') ||
            document.querySelector('textarea[id*="message"]');

        const charCountDisplay =
            document.querySelector('#char-counter') ||
            document.querySelector('[id*="char-counter"]') ||
            document.querySelector('.char-counter');

        const recipientCountDisplay =
            document.querySelector('#recipient-count') ||
            document.querySelector('[id*="recipient-count"]') ||
            document.querySelector('.recipient-count');

        const form = document.querySelector('form');

        // Safety check - ensure required elements exist
        if (!messageTextarea) {
            retryCount++;
            if (retryCount < maxRetries) {
                Log.debug(
                    `local_equipment/mass_text_dynamic_form: Message textarea not found, retrying... (${retryCount}/${maxRetries})`
                );
                // Try again after a short delay
                setTimeout(initializeForm, 100);
                return;
            } else {
                Log.error(
                    'local_equipment/mass_text_dynamic_form: Message textarea not found after maximum retries. ' +
                        'Form initialization failed.'
                );
                return;
            }
        }

        Log.debug(
            'local_equipment/mass_text_dynamic_form: Elements found, initializing...'
        );

        // Get max length from data attribute or use default
        const maxLength = parseInt(messageTextarea.dataset.maxLength) || 250;

        /**
         * Update character count display with proper styling.
         *
         * @return {void}
         */
        const updateCharCount = () => {
            const currentLength = messageTextarea.value.length;
            const remaining = maxLength - currentLength;

            // Update the character count text
            if (remaining >= 0) {
                charCountDisplay.textContent = `${remaining} characters remaining`;
            } else {
                charCountDisplay.textContent = `${Math.abs(
                    remaining
                )} characters over limit`;
            }

            // Update styling based on remaining characters
            charCountDisplay.classList.remove(
                'text-danger',
                'text-warning',
                'text-muted'
            );

            if (remaining < 0) {
                charCountDisplay.classList.add('text-danger');
            } else if (remaining < 20) {
                charCountDisplay.classList.add('text-warning');
            } else {
                charCountDisplay.classList.add('text-muted');
            }
        };

        /**
         * Load recipient count via AJAX using Moodle web services.
         *
         * @return {void}
         */
        const loadRecipientCount = () => {
            if (!recipientCountDisplay) {
                return;
            }

            Ajax.call([
                {
                    methodname: 'local_equipment_get_recipient_count',
                    args: {},
                },
            ])[0]
                .then((response) => {
                    Log.debug('Web service response:', response);

                    if (response.success) {
                        recipientCountDisplay.textContent = `${response.count} parents`;
                        recipientCountDisplay.className = 'badge bg-secondary';

                        // Update styling based on count
                        if (response.count === 0) {
                            recipientCountDisplay.classList.remove(
                                'bg-secondary'
                            );
                            recipientCountDisplay.classList.add('bg-warning');
                        } else {
                            recipientCountDisplay.classList.remove(
                                'bg-warning'
                            );
                            recipientCountDisplay.classList.add('bg-success');
                        }
                    } else {
                        recipientCountDisplay.textContent =
                            'Error loading count';
                        recipientCountDisplay.className = 'badge bg-danger';
                        Log.error(
                            'local_equipment/mass_text_dynamic_form: Recipient count error:',
                            response.error
                        );
                    }
                })
                .catch((error) => {
                    Log.debug('Web service error details:', error);
                    recipientCountDisplay.textContent = 'Error loading count';
                    recipientCountDisplay.className = 'badge bg-danger';
                    Log.error(
                        'local_equipment/mass_text_dynamic_form: Web service error:',
                        error.message || 'Unknown error'
                    );
                });
        };

        /**
         * Handle successful form submission.
         *
         * @param {Object} response - The response from the form submission
         * @return {void}
         */
        const handleFormSuccess = (response) => {
            if (response.success_message) {
                addToast(response.success_message, {
                    type: 'success',
                    autohide: true,
                    delay: 5000,
                });
            }

            if (response.failure_message) {
                addToast(response.failure_message, {
                    type: 'warning',
                    autohide: true,
                    delay: 8000,
                });
            }

            // Show detailed error information if available
            if (response.error_details && response.error_details.length > 0) {
                showErrorDetails(response.error_details);
            }

            // Clear the form
            if (messageTextarea) {
                messageTextarea.value = '';
                updateCharCount();
            }

            // Reload recipient count
            loadRecipientCount();
        };

        /**
         * Show detailed error information in a collapsible format.
         *
         * @param {Array} errorDetails - Array of error detail objects
         * @return {void}
         */
        const showErrorDetails = (errorDetails) => {
            let errorHtml = '<div class="alert alert-danger mt-3">';
            errorHtml += '<h5>Error Details</h5>';
            errorHtml += '<div class="collapse" id="errorDetails">';
            errorHtml += '<ul class="mb-0">';

            errorDetails.forEach((error) => {
                errorHtml += '<li>';
                errorHtml += `<strong>${error.recipient}</strong> (${error.phone}): `;
                errorHtml += error.error_message;
                if (error.aws_error_code) {
                    errorHtml += ` (AWS Error: ${error.aws_error_code})`;
                }
                errorHtml += '</li>';
            });

            errorHtml += '</ul>';
            errorHtml += '</div>';
            errorHtml += `<button class="btn btn-sm btn-outline-danger mt-2" type="button"
                      data-bs-toggle="collapse" data-bs-target="#errorDetails"
                      aria-expanded="false" aria-controls="errorDetails">`;
            errorHtml += `View Error Details (${errorDetails.length} errors)`;
            errorHtml += '</button>';
            errorHtml += '</div>';

            // Insert the error details after the form
            const formContainer =
                document.querySelector('.local_equipment_mass_text') ||
                document.querySelector('form') ||
                document.body;

            const errorDiv = document.createElement('div');
            errorDiv.innerHTML = errorHtml;
            formContainer.appendChild(errorDiv);
        };

        /**
         * Handle form submission confirmation.
         *
         * @param {Event} e - The form submission event
         * @return {boolean} - Whether to allow form submission
         */
        const handleFormSubmit = (e) => {
            const message = messageTextarea.value.trim();

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
                    message: `Message is too long. Maximum ${maxLength} characters allowed`,
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
        messageTextarea.addEventListener('input', () => {
            if (charCountDisplay) {
                updateCharCount();
            }
        });
        messageTextarea.addEventListener('keyup', () => {
            if (charCountDisplay) {
                updateCharCount();
            }
        });
        messageTextarea.addEventListener('paste', () => {
            // Use setTimeout to ensure paste content is processed
            setTimeout(() => {
                if (charCountDisplay) {
                    updateCharCount();
                }
            }, 10);
        });

        // Initial character count
        if (charCountDisplay) {
            updateCharCount();
        }

        // Load recipient count
        loadRecipientCount();

        // Handle form submission if form exists
        if (form) {
            form.addEventListener('submit', handleFormSubmit);
        }

        // Set up dynamic form handling if using modal forms
        const modalTriggers = document.querySelectorAll(
            '[data-action="mass-text-modal"]'
        );
        modalTriggers.forEach((trigger) => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();

                const modalForm = new ModalForm({
                    formClass: 'local_equipment\\form\\mass_text_dynamic_form',
                    args: {},
                    modalConfig: {
                        title: getString(
                            'masstextmessaging',
                            'local_equipment'
                        ),
                        size: 'lg',
                    },
                    returnFocus: trigger,
                });

                modalForm.addEventListener(
                    modalForm.events.FORM_SUBMITTED,
                    (e) => {
                        handleFormSuccess(e.detail);
                    }
                );

                modalForm.show();
            });
        });
    };

    // Start the initialization process
    initializeForm();
};

export default {
    init: init,
};
