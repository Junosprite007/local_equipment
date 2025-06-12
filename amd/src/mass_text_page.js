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
 * Mass text messaging page JavaScript functionality.
 *
 * @module     local_equipment/mass_text_page
 * @copyright  2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import Log from 'core/log';

/**
 * Initialize the mass text page functionality.
 *
 * @return {void}
 */
const init = () => {
    let retryCount = 0;
    const maxRetries = 50; // 5 seconds max (50 * 100ms)

    // Wait for DOM to be ready and try multiple times if elements aren't found
    const initializePage = () => {
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
        const submitButton = document.querySelector('input[type="submit"]');

        // Safety check - ensure required elements exist
        if (!messageTextarea) {
            retryCount++;
            if (retryCount < maxRetries) {
                Log.debug(
                    `local_equipment/mass_text_page: Message textarea not found, retrying... (${retryCount}/${maxRetries})`
                );
                // Try again after a short delay
                setTimeout(initializePage, 100);
                return;
            } else {
                Log.error(
                    'local_equipment/mass_text_page: Message textarea not found after maximum retries. ' +
                        'Page initialization failed.'
                );
                return;
            }
        }

        Log.debug(
            'local_equipment/mass_text_page: Elements found, initializing...'
        );

        // Get max length from data attribute or use default
        const maxLength = parseInt(messageTextarea.dataset.maxLength) || 250;

        /**
         * Update character count display with proper styling.
         *
         * @return {void}
         */
        const updateCharCount = () => {
            if (!charCountDisplay) {
                return;
            }

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
                            'local_equipment/mass_text_page: Recipient count error:',
                            response.error
                        );
                    }
                })
                .catch((error) => {
                    Log.debug('Web service error details:', error);
                    recipientCountDisplay.textContent = 'Error loading count';
                    recipientCountDisplay.className = 'badge bg-danger';
                    Log.error(
                        'local_equipment/mass_text_page: Web service error:',
                        error.message || 'Unknown error'
                    );
                });
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
                    'Are you sure you want to send this message to all parents with verified phone numbers?'
                )
            ) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.value = 'Sending Messages...';
            }

            return true;
        };

        /**
         * Handle successful form submission results.
         *
         * @return {void}
         */
        const handleFormSuccess = () => {
            // Clear the form
            if (messageTextarea) {
                messageTextarea.value = '';
                updateCharCount();
            }

            // Re-enable submit button
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.value = 'Send Messages';
            }

            // Reload recipient count
            loadRecipientCount();

            // Scroll to top to show results
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        // Initialize functionality
        if (messageTextarea) {
            messageTextarea.addEventListener('input', updateCharCount);
            messageTextarea.addEventListener('keyup', updateCharCount);
            messageTextarea.addEventListener('paste', () => {
                // Use setTimeout to ensure paste content is processed
                setTimeout(updateCharCount, 10);
            });

            // Initial character count
            updateCharCount();
        }

        // Load recipient count
        loadRecipientCount();

        // Handle form submission if form exists
        if (form) {
            form.addEventListener('submit', handleFormSubmit);
        }

        // Handle successful submissions (check for success messages)
        const successAlert = document.querySelector('.alert-success');
        const warningAlert = document.querySelector('.alert-warning');
        if (successAlert || warningAlert) {
            handleFormSuccess();
        }

        // Auto-dismiss alerts after 10 seconds
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach((alert) => {
            setTimeout(() => {
                const closeButton = alert.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.click();
                }
            }, 10000);
        });
    };

    // Start the initialization process
    initializePage();
};

export default {
    init: init,
};
