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
 * Mass text manager service for sending text messages to parents.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment;

defined('MOODLE_INTERNAL') || die();

/**
 * Service class for managing mass text messages to parents.
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 */
class mass_text_manager {
    /** @var \moodle_database Database instance */
    protected $db;

    /** @var \core\clock Clock instance */
    protected $clock;

    /** @var mixed The origination phone number from AWS End User Messaging service */
    protected $originationnumber;

    /**
     * Constructor
     */
    public function __construct() {
        global $DB;
        $this->db = $DB;
        $this->clock = \core\di::get(\core\clock::class);
        $this->originationnumber = get_config('local_equipment', 'awsinfooriginatorphone');
    }

    /**
     * Get students enrolled in courses with end dates.
     *
     * @return array Array of unique student user IDs
     */
    public function get_students_in_courses_with_end_dates(): array {
        $currenttime = $this->clock->now()->getTimestamp();

        $sql = "SELECT DISTINCT u.id as studentid
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {course} c ON c.id = e.courseid
                WHERE c.enddate > :currenttime
                AND c.enddate IS NOT NULL
                AND ue.status = 0
                AND u.deleted = 0
                ORDER BY u.id";

        $params = ['currenttime' => $currenttime];
        $records = $this->db->get_records_sql($sql, $params);

        return array_keys($records);
    }

    /**
     * Get students enrolled in courses with end dates that are in the future, i.e. courses that are still ongoing.
     *
     * @return array Array of unique student user IDs
     */
    public function get_students_in_courses_with_future_end_dates(): array {
        $currenttime = $this->clock->now()->getTimestamp();

        $sql = "SELECT DISTINCT u.id as studentid
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {course} c ON c.id = e.courseid
                JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel
                JOIN {role_assignments} ra ON ra.userid = u.id AND ra.contextid = ctx.id
                JOIN {role} r ON r.id = ra.roleid
                WHERE c.enddate > :currenttime
                AND c.enddate IS NOT NULL
                AND ue.status = 0
                AND u.deleted = 0
                AND r.shortname = :studentrole
                ORDER BY u.id";

        $params = [
            'currenttime' => $currenttime,
            'contextlevel' => CONTEXT_COURSE,  // Context level for courses (50)
            'studentrole' => 'student'         // Standard student role shortname
        ];
        $records = $this->db->get_records_sql($sql, $params);

        return array_keys($records);
    }

    /**
     * Get verified parents for a list of students (first verified parent per student).
     *
     * @param array $studentids Array of student user IDs
     * @return array Array of parent records with phone numbers
     */
    public function get_verified_parents_for_students(array $studentids): array {
        if (empty($studentids)) {
            return [];
        }

        $parents = [];

        foreach ($studentids as $studentid) {
            // Get parents for this student
            $studentparents = local_equipment_get_parents_of_student($studentid);

            foreach ($studentparents as $parent) {
                // Check if this parent's phone is verified
                if ($this->is_phone_verified($parent->id)) {
                    // Get the verified phone number
                    $verifiedphone = $this->get_verified_phone_number($parent->id);
                    if ($verifiedphone) {
                        // Ensure user object has all required fields for fullname()
                        $parent->firstnamephonetic = $parent->firstnamephonetic ?? '';
                        $parent->lastnamephonetic = $parent->lastnamephonetic ?? '';
                        $parent->middlename = $parent->middlename ?? '';
                        $parent->alternatename = $parent->alternatename ?? '';

                        $parents[$parent->id] = (object)[
                            'id' => $parent->id,
                            'firstname' => $parent->firstname,
                            'lastname' => $parent->lastname,
                            'firstnamephonetic' => $parent->firstnamephonetic,
                            'lastnamephonetic' => $parent->lastnamephonetic,
                            'middlename' => $parent->middlename,
                            'alternatename' => $parent->alternatename,
                            'phone' => $verifiedphone,
                            'studentid' => $studentid
                        ];
                        // Only take the first verified parent per student
                        break;
                    }
                }
            }
        }

        return $parents;
    }

    /**
     * Check if a user's phone number is verified.
     *
     * @param int $userid User ID
     * @return bool True if phone is verified
     */
    public function is_phone_verified(int $userid): bool {
        return $this->db->record_exists('local_equipment_phonecommunication_otp', [
            'userid' => $userid,
            'phoneisverified' => 1
        ]);
    }

    /**
     * Get the verified phone number for a user.
     *
     * @param int $userid User ID
     * @return string|false Verified phone number or false if not found
     */
    public function get_verified_phone_number(int $userid) {
        $record = $this->db->get_record('local_equipment_phonecommunication_otp', [
            'userid' => $userid,
            'phoneisverified' => 1
        ], 'tophonenumber', IGNORE_MULTIPLE);

        return $record ? $record->tophonenumber : false;
    }

    /**
     * Send mass text messages to parents and admin.
     *
     * @param string $message Message to send
     * @param array $recipients Array of parent records
     * @param int $adminuserid Admin user ID for confirmation
     * @return object Results object with success/failure counts and messages
     */
    public function send_mass_messages(string $message, array $recipients, int $adminuserid): object {
        $results = (object)[
            'success_count' => 0,
            'failure_count' => 0,
            'total_recipients' => count($recipients),
            'successes' => [],
            'failures' => [],
            'admin_confirmation_sent' => false,
            'error_details' => []
        ];

        // Get the default SMS gateway
        $gateway = $this->get_default_sms_gateway();
        if (!$gateway) {
            $results->failures[] = get_string('nosmsgatewayconfigured', 'local_equipment');
            return $results;
        }

        // Send messages to parents
        foreach ($recipients as $parent) {
            try {
                $response = local_equipment_send_sms($gateway->id, $parent->phone, $message, 'Transactional', $this->originationnumber);

                if ($response->success) {
                    $results->success_count++;
                    $results->successes[] = get_string('messagesent', 'local_equipment', [
                        'name' => fullname($parent),
                        'phone' => $parent->phone
                    ]);
                } else {
                    $results->failure_count++;

                    // Store detailed error information for admin review
                    $errordetail = [
                        'recipient' => fullname($parent),
                        'phone' => $parent->phone,
                        'error_type' => $response->errortype ?? 'unknown',
                        'error_message' => $response->errormessage,
                        'aws_error_code' => $response->errorobject->awserrorcode ?? null
                    ];
                    $results->error_details[] = $errordetail;

                    $results->failures[] = get_string('messagefailed', 'local_equipment', [
                        'name' => fullname($parent),
                        'error' => $response->errormessage
                    ]);
                }
            } catch (\Exception $e) {
                $results->failure_count++;

                // Store exception details
                $errordetail = [
                    'recipient' => fullname($parent),
                    'phone' => $parent->phone,
                    'error_type' => 'exception',
                    'error_message' => $e->getMessage(),
                    'aws_error_code' => null
                ];
                $results->error_details[] = $errordetail;

                $results->failures[] = get_string('messagefailed', 'local_equipment', [
                    'name' => fullname($parent),
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Send confirmation message to admin
        $this->send_admin_confirmation($adminuserid, $message, $results, $gateway->id);

        // Log the mass text operation
        $this->log_mass_text_operation($adminuserid, $message, $results);

        return $results;
    }

    /**
     * Send confirmation message to admin.
     *
     * @param int $adminuserid Admin user ID
     * @param string $originalmessage Original message sent
     * @param object $results Results object
     * @param int $gatewayid SMS gateway ID
     */
    protected function send_admin_confirmation(int $adminuserid, string $originalmessage, object $results, int $gatewayid): void {
        $admin = $this->db->get_record('user', ['id' => $adminuserid]);
        if (!$admin) {
            return;
        }

        // Check if admin has verified phone
        $adminphone = $this->get_verified_phone_number($adminuserid);
        if (!$adminphone) {
            return;
        }

        $confirmationmessage = get_string('masstextconfirmation', 'local_equipment', [
            'sent' => $results->success_count,
            'failed' => $results->failure_count,
            'total' => $results->total_recipients,
            'message' => substr($originalmessage, 0, 50) . (strlen($originalmessage) > 50 ? '...' : '')
        ]);

        try {
            $response = local_equipment_send_sms($gatewayid, $adminphone, $confirmationmessage, 'Transactional', $this->originationnumber);
            $results->admin_confirmation_sent = $response->success;
        } catch (\Exception $e) {
            $results->admin_confirmation_sent = false;
        }
    }

    /**
     * Get the default SMS gateway.
     *
     * @return object|false Gateway record or false if not found
     */
    protected function get_default_sms_gateway() {
        return $this->db->get_record('sms_gateways', ['enabled' => 1], '*', IGNORE_MULTIPLE);
    }
    /**
     * Log the mass text operation using Moodle's event system.
     *
     * @param int $adminuserid Admin user ID
     * @param string $message Message sent
     * @param object $results Results object
     */
    protected function log_mass_text_operation(int $adminuserid, string $message, object $results): void {
        // Use Moodle's modern event system for proper logging
        $event = \local_equipment\event\mass_text_sent::create_from_results(
            $adminuserid,
            $message,
            $results
        );
        $event->trigger();

        // Optional: Also log to server error log for external monitoring
        error_log("Mass text operation: The user with id '{$adminuserid}' a mass text message to {$results->total_recipients} recipients. " .
            "Success: {$results->success_count}, Failed: {$results->failure_count}");
    }
}
