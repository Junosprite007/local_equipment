<?php
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

// File location: /local/equipment/classes/event/mass_text_sent.php

/**
 * Event for mass text message sent.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event triggered when mass text messages are sent.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class mass_text_sent extends \core\event\base {

    /**
     * Initialise the event data.
     */
    protected function init(): void {
        $this->data['objecttable'] = null; // No specific table
        $this->data['crud'] = 'c'; // Create action
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns localised event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('event_mass_text_sent', 'local_equipment');
    }

    /**
     * Returns non-localised event description with all details.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string('event_mass_text_description', 'local_equipment', [
            'userid' => $this->userid,
            'total' => $this->other['total_recipients'],
            'success' => $this->other['success_count'],
            'failed' => $this->other['failure_count'],
            'message' => substr($this->other['message'], 0, 100)
        ]);
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url(): \moodle_url {
        return new \moodle_url('/local/equipment/mass_text_message.php');
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data(): void {
        parent::validate_data();

        if (!isset($this->other['total_recipients'])) {
            throw new \coding_exception('The \'total_recipients\' value must be set in other.');
        }

        if (!isset($this->other['success_count'])) {
            throw new \coding_exception('The \'success_count\' value must be set in other.');
        }

        if (!isset($this->other['failure_count'])) {
            throw new \coding_exception('The \'failure_count\' value must be set in other.');
        }

        if (!isset($this->other['message'])) {
            throw new \coding_exception('The \'message\' value must be set in other.');
        }
    }

    /**
     * Create instance of event.
     *
     * @param int $userid User ID who sent the messages
     * @param string $message The message that was sent
     * @param object $results Results object with counts
     * @return mass_text_sent
     */
    public static function create_from_results(int $userid, string $message, object $results): self {
        $event = self::create([
            'context' => \context_system::instance(),
            'userid' => $userid,
            'other' => [
                'total_recipients' => $results->total_recipients,
                'success_count' => $results->success_count,
                'failure_count' => $results->failure_count,
                'message' => $message,
                'admin_confirmation_sent' => $results->admin_confirmation_sent ?? false
            ]
        ]);

        return $event;
    }
}
