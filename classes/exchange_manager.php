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

/**
 * Exchange manager service for equipment exchanges.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment;

defined('MOODLE_INTERNAL') || die();

/**
 * Service class for managing equipment exchanges and reminders.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class exchange_manager {
    /** @var \moodle_database Database instance */
    protected $db;

    /**
     * Constructor
     */
    public function __construct() {
        global $DB;
        $this->db = $DB;
    }

    /**
     * Get users who need reminders for exchanges within a specified date range.
     *
     * @param int $startdate Start timestamp
     * @param int $enddate End timestamp
     * @param int $remindertype Type of reminder (1=days, 0=hours)
     * @return array Array of user and exchange records
     */
    public function get_users_needing_reminders(int $startdate, int $enddate, int $remindertype): array {
        // Determine reminder code to look for
        $remindercode = ($remindertype == 1) ? 0 : (($remindertype == 0) ? 1 : 0);

        // Query to find users who have VCC records but haven't received this reminder yet
        $sql = "SELECT ue.id, ue.userid, ue.exchangeid, ue.reminder_code, ue.reminder_method,
                   p.exchangedate, p.location, p.courseid, p.flccoordinatorid
            FROM {local_equipment_user_exchange} ue
            JOIN {local_equipment_pickup} p ON p.id = ue.exchangeid
            WHERE p.exchangedate >= :startdate
              AND p.exchangedate <= :enddate
              AND (ue.reminder_code = :remindercode OR ue.reminder_code = 0)
            ORDER BY p.exchangedate ASC";

        $params = [
            'startdate' => $startdate,
            'enddate' => $enddate,
            'remindercode' => $remindercode
        ];

        return $this->db->get_records_sql($sql, $params);
    }

    /**
     * Get a specific exchange record.
     *
     * @param int $exchangeid Exchange ID
     * @return object|false Exchange record or false if not found
     */
    public function get_exchange(int $exchangeid) {
        return $this->db->get_record('local_equipment_pickup', ['id' => $exchangeid]);
    }

    /**
     * Update the reminder status for a user's exchange.
     *
     * @param int $userid User ID
     * @param int $exchangeid Exchange ID
     * @param int $remindercode New reminder code (1=days reminder sent, 2=hours reminder sent)
     * @return bool Success status
     */
    public function update_reminder_status(int $userid, int $exchangeid, int $remindercode): bool {
        // Get the current record
        $record = $this->db->get_record('local_equipment_user_exchange', [
            'userid' => $userid,
            'exchangeid' => $exchangeid
        ]);

        if (!$record) {
            return false;
        }

        // Update reminder code based on which reminder was sent and current status
        if ($remindercode == 1) {
            // First reminder (days)
            if ($record->reminder_code == 2) {
                $record->reminder_code = 9; // Both reminders now sent
            } else {
                $record->reminder_code = 1; // Only first reminder sent
            }
        } else if ($remindercode == 2) {
            // Second reminder (hours)
            if ($record->reminder_code == 1) {
                $record->reminder_code = 9; // Both reminders now sent
            } else {
                $record->reminder_code = 2; // Only second reminder sent
            }
        }

        $record->timemodified = time();

        return $this->db->update_record('local_equipment_user_exchange', $record);
    }

    /**
     * Get equipment list for an exchange.
     *
     * @param int $exchangeid Exchange ID
     * @return string Formatted equipment list
     */
    public function get_equipment_list(int $exchangeid): string {
        // Get the exchange record
        $exchange = $this->get_exchange($exchangeid);
        if (!$exchange) {
            return '';
        }

        // Get the course
        $course = $this->db->get_record('course', ['id' => $exchange->courseid]);
        if (!$course) {
            return '';
        }

        // Find the assignment containing equipment details
        // Assuming there's a field in local_equipment_pickup that references an assignment
        // or using course module info to find the right assignment
        if (!empty($exchange->assignmentid)) {
            $assignment = $this->db->get_record('assign', ['id' => $exchange->assignmentid]);

            if ($assignment) {
                // Extract equipment names from assignment
                $content = strip_tags($assignment->intro, '<br><p>');
                $content = str_replace(['<br>', '<br/>', '<br />', '</p><p>'], "\n", $content);
                $content = str_replace(['<p>', '</p>'], "", $content);

                $lines = explode("\n", $content);
                $equipment = [];

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $equipment[] = $line;
                    }
                }

                if (count($equipment) > 0) {
                    return implode(", ", $equipment);
                }
            }
        }

        // If no specific equipment is found, return generic text
        return "equipment for " . $course->fullname;
    }
}
