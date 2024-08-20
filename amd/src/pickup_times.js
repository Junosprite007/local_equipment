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
 * JavaScript for getting pickup times based on selected pickup location.
 *
 * @module     local_equipment/pickup_times
 * @copyright  2024 Joshua Kirby <josh@funlearningcompany.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Log from 'core/log';
import { get_string as getString } from 'core/str';

export const init = () => {
    const pickupLocationSelect = document.getElementById('id_pickuplocation');
    const pickupTimeSelect = document.getElementById('id_pickuptime');

    if (!pickupLocationSelect || !pickupTimeSelect) {
        Log.error('Pickup location or time select element not found');
        return;
    }

    let pickupTimeData;
    try {
        pickupTimeData = JSON.parse(
            pickupLocationSelect.getAttribute('data-pickuptimes') || '{}'
        );
    } catch (e) {
        Log.error('Error parsing pickup time data:', e);
        return;
    }

    const updatePickupTimes = async (locationId) => {
        const contactUsString = await getString(
            'contactusforpickup',
            'local_equipment'
        );
        pickupTimeSelect.innerHTML = `<option value="0">${contactUsString}</option>`;

        const times = pickupTimeData[locationId] || [];
        times.forEach((time) => {
            const option = document.createElement('option');
            option.value = time.id;
            option.textContent = time.datetime;
            pickupTimeSelect.appendChild(option);
        });
    };

    pickupLocationSelect.addEventListener('change', (event) => {
        updatePickupTimes(event.target.value);
    });

    // Initial update
    updatePickupTimes(pickupLocationSelect.value);
};
