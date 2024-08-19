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
 * JavaScript for managing consents.
 *
 * @module     local_equipment/manage_consents
 * @copyright  2024 Joshua Kirby <josh@funlearningcompany.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Ajax from 'core/ajax';
import Notification from 'core/notification';
import { get_string as getString } from 'core/str';

/**
 * Initialize the manage consents functionality.
 */
export const init = () => {
    setupViewNotes();
    setupAddNote();
};

/**
 * Set up view notes functionality.
 */
const setupViewNotes = () => {
    $('.view-notes').on('click', (e) => {
        e.preventDefault();
        const consentId = $(e.currentTarget).data('id');
        Ajax.call([
            {
                methodname: 'local_equipment_get_consent_notes',
                args: { consentid: consentId },
                done: (notes) => {
                    $('#note-modal .modal-body').html(notes);
                    $('#note-modal').modal('show');
                },
                fail: Notification.exception,
            },
        ]);
    });
};

/**
 * Set up add note functionality.
 */
const setupAddNote = () => {
    $('.add-note').on('click', (e) => {
        e.preventDefault();
        const consentId = $(e.currentTarget).data('id');
        $('#note-modal .modal-body').html(
            '<textarea id="admin-note" class="form-control" rows="5"></textarea>'
        );
        $('#note-modal').modal('show');

        $('#save-note')
            .off('click')
            .on('click', () => {
                const note = $('#admin-note').val();
                Ajax.call([
                    {
                        methodname: 'local_equipment_add_consent_note',
                        args: { consentid: consentId, note: note },
                        done: () => {
                            $('#note-modal').modal('hide');
                            getString('notesaved', 'local_equipment')
                                .then((s) => {
                                    Notification.addNotification({
                                        message: s,
                                        type: 'success',
                                    });
                                })
                                .catch(Notification.exception);
                        },
                        fail: Notification.exception,
                    },
                ]);
            });
    });
};
