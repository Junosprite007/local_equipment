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

declare(strict_types=1);

namespace local_equipment\service;

use stdClass;
use moodle_database;
use core\clock;

/**
 * Service class for VCC submission operations following Moodle 5.0 conventions
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_submission_service {

    /** @var moodle_database */
    private moodle_database $db;

    /** @var clock */
    private clock $clock;

    /**
     * Constructor with dependency injection
     *
     * @param moodle_database $db Database instance
     * @param clock $clock Clock service for time operations
     */
    public function __construct(moodle_database $db, clock $clock) {
        $this->db = $db;
        $this->clock = $clock;
    }

    /**
     * Delete a VCC submission
     *
     * @param int $submission_id
     * @throws \moodle_exception
     */
    public function delete_submission(int $submission_id): void {
        if (!$this->db->record_exists('local_equipment_vccsubmission', ['id' => $submission_id])) {
            throw new \moodle_exception('submissionnotfound', 'local_equipment');
        }

        $this->db->delete_records('local_equipment_vccsubmission', ['id' => $submission_id]);
    }

    /**
     * Get default filters for current school year
     *
     * @param clock $clock
     * @return stdClass
     */
    public function get_default_filters(clock $clock): stdClass {
        $now = $clock->now()->getTimestamp();
        $current_year = (int)date('Y', $now);
        $current_month = (int)date('n', $now);

        $filters = new stdClass();
        $filters->partnership = 0;
        $filters->search = '';

        // School year starts in August
        if ($current_month >= 8) {
            $filters->datestart = mktime(0, 0, 0, 8, 1, $current_year);
            $filters->dateend = mktime(23, 59, 59, 7, 31, $current_year + 1);
        } else {
            $filters->datestart = mktime(0, 0, 0, 8, 1, $current_year - 1);
            $filters->dateend = mktime(23, 59, 59, 7, 31, $current_year);
        }

        return $filters;
    }

    /**
     * Build SQL query for table with filters - includes ALL VCC submission fields and exchange data
     *
     * @param stdClass $filters
     * @return array [select, from, where, params]
     */
    public function build_table_sql(stdClass $filters): array {
        $select = "
            vccsubmission.id,
            vccsubmission.userid,
            vccsubmission.partnershipid,
            vccsubmission.pickupid,
            vccsubmission.exchange_partnershipid,
            vccsubmission.exchangesubmissionid,
            vccsubmission.studentids,
            vccsubmission.agreementids,
            vccsubmission.confirmationid,
            vccsubmission.confirmationexpired,
            vccsubmission.email,
            vccsubmission.email_confirmed,
            vccsubmission.firstname,
            vccsubmission.lastname,
            vccsubmission.phone,
            vccsubmission.phone_confirmed,
            vccsubmission.partnership_name,
            vccsubmission.mailing_extrainput,
            vccsubmission.mailing_streetaddress,
            vccsubmission.mailing_apartment,
            vccsubmission.mailing_city,
            vccsubmission.mailing_state,
            vccsubmission.mailing_country,
            vccsubmission.mailing_zipcode,
            vccsubmission.mailing_extrainstructions,
            vccsubmission.billing_extrainput,
            vccsubmission.billing_sameasmailing,
            vccsubmission.billing_streetaddress,
            vccsubmission.billing_apartment,
            vccsubmission.billing_city,
            vccsubmission.billing_state,
            vccsubmission.billing_country,
            vccsubmission.billing_zipcode,
            vccsubmission.billing_extrainstructions,
            vccsubmission.pickup_locationtime,
            vccsubmission.electronicsignature,
            vccsubmission.pickupmethod,
            vccsubmission.pickuppersonname,
            vccsubmission.pickuppersonphone,
            vccsubmission.pickuppersondetails,
            vccsubmission.usernotes,
            vccsubmission.adminnotes,
            vccsubmission.timecreated,
            vccsubmission.timemodified,
            u.firstname as u_firstname,
            u.lastname as u_lastname,
            u.email as u_email,
            u.phone1 as u_phone1,
            u.phone2 as u_phone2,
            partnership.name as p_name,
            exchange_partnership.name as exchange_partnership_name,
            " . $this->db->sql_concat_join("' '", ['exchange_partnership.physical_streetaddress', 'exchange_partnership.physical_city', 'exchange_partnership.physical_state']) . " as exchange_partnership_address,
            " . $this->db->sql_concat_join("' '", ['exchange_partnership.mailing_streetaddress', 'exchange_partnership.mailing_city', 'exchange_partnership.mailing_state']) . " as exchange_partnership_mailing_address,
            " . $this->db->sql_concat_join("' '", ['exchange_partnership.pickup_streetaddress', 'exchange_partnership.pickup_city', 'exchange_partnership.pickup_state']) . " as exchange_partnership_pickup_address,
            exchange_sub.pickup_method as exchange_pickup_method,
            exchange_sub.pickup_person_name as exchange_pickup_person_name,
            exchange_sub.pickup_person_phone as exchange_pickup_person_phone,
            exchange_sub.pickup_person_details as exchange_pickup_person_details,
            exchange_sub.user_notes as exchange_user_notes,
            pickup_schedule.pickupdate as exchange_pickup_date,
            pickup_schedule.starttime as exchange_pickup_starttime,
            pickup_schedule.endtime as exchange_pickup_endtime,
            " . $this->db->sql_concat_join("' '", ['pickup_schedule.pickup_streetaddress', 'pickup_schedule.pickup_city', 'pickup_schedule.pickup_state']) . " as exchange_pickup_location_address
        ";

        $from = "
            {local_equipment_vccsubmission} vccsubmission
            LEFT JOIN {user} u ON vccsubmission.userid = u.id
            LEFT JOIN {local_equipment_partnership} partnership ON vccsubmission.partnershipid = partnership.id
            LEFT JOIN {local_equipment_partnership} exchange_partnership ON vccsubmission.exchange_partnershipid = exchange_partnership.id
            LEFT JOIN {local_equipment_exchange_submission} exchange_sub ON vccsubmission.exchangesubmissionid = exchange_sub.id
            LEFT JOIN {local_equipment_pickup} pickup_schedule ON exchange_sub.exchangeid = pickup_schedule.id
        ";

        [$where, $params] = $this->build_where_clause($filters);

        return [$select, $from, $where, $params];
    }

    /**
     * Build WHERE clause with parameters
     *
     * @param stdClass $filters
     * @return array [where_clause, params]
     */
    private function build_where_clause(stdClass $filters): array {
        $where_conditions = ['1=1'];
        $params = [];

        // Partnership filter
        if (!empty($filters->partnership)) {
            $where_conditions[] = 'vccsubmission.partnershipid = ?';
            $params[] = $filters->partnership;
        }

        // Date range filters
        if (!empty($filters->datestart)) {
            $where_conditions[] = 'vccsubmission.timecreated >= ?';
            $params[] = $filters->datestart;
        }

        if (!empty($filters->dateend)) {
            $where_conditions[] = 'vccsubmission.timecreated <= ?';
            $params[] = $filters->dateend;
        }

        // Search filter with proper SQL escaping
        if (!empty($filters->search)) {
            $search_conditions = [
                $this->db->sql_like('vccsubmission.firstname', '?', false, false),
                $this->db->sql_like('vccsubmission.lastname', '?', false, false),
                $this->db->sql_like('vccsubmission.email', '?', false, false),
                $this->db->sql_like('vccsubmission.phone', '?', false, false),
                $this->db->sql_like('u.firstname', '?', false, false),
                $this->db->sql_like('u.lastname', '?', false, false),
                $this->db->sql_like('u.email', '?', false, false)
            ];

            $where_conditions[] = '(' . implode(' OR ', $search_conditions) . ')';

            // Add search parameter for each condition
            $search_param = "%{$filters->search}%";
            for ($i = 0; $i < count($search_conditions); $i++) {
                $params[] = $search_param;
            }
        }

        return [implode(' AND ', $where_conditions), $params];
    }

    /**
     * Count submissions with filters
     *
     * @param stdClass $filters
     * @return int
     */
    public function count_submissions(stdClass $filters): int {
        [, $from, $where, $params] = $this->build_table_sql($filters);

        return $this->db->count_records_sql("SELECT COUNT(*) FROM $from WHERE $where", $params);
    }

    /**
     * Get students display data for table cell
     *
     * @param stdClass $row
     * @return array
     */
    public function get_students_display_data(stdClass $row): array {
        if (empty($row->studentids)) {
            return [];
        }

        $student_ids = json_decode($row->studentids, true);
        if (empty($student_ids)) {
            return [];
        }

        $students = [];
        foreach ($student_ids as $student_id) {
            $student = $this->db->get_record('local_equipment_vccsubmission_student', ['id' => $student_id]);
            if ($student) {
                $courses = $this->get_student_courses((int)$student_id);
                $students[] = [
                    'name' => $student->firstname . ' ' . $student->lastname,
                    'courses' => $courses,
                    'courses_text' => empty($courses) ? 'No courses' : implode(', ', array_column($courses, 'name'))
                ];
            }
        }

        return $students;
    }

    /**
     * Get courses for a student with proper type safety
     *
     * @param int $student_id
     * @return array
     */
    private function get_student_courses(int $student_id): array {
        $sql = "SELECT c.id, c.fullname as name
                FROM {local_equipment_vccsubmission_student_course} sc
                JOIN {course} c ON sc.courseid = c.id
                WHERE sc.studentid = ?
                ORDER BY c.fullname";

        $courses = $this->db->get_records_sql($sql, [$student_id]);
        $result = [];
        foreach ($courses as $course) {
            $result[] = (object)[
                'id' => $course->id,
                'name' => $course->name
            ];
        }
        return $result;
    }

    /**
     * Format mailing address for display
     *
     * @param int $user_id
     * @return string
     */
    public function format_mailing_address(int $user_id): string {
        $user_record = $this->db->get_record('local_equipment_user', ['userid' => $user_id]);

        if (!$user_record) {
            return '-';
        }

        $address_parts = array_filter([
            $user_record->mailing_streetaddress ?? '',
            $user_record->mailing_apartment ? get_string('apt', 'local_equipment') . ' ' . $user_record->mailing_apartment : '',
            $user_record->mailing_city ?? '',
            $user_record->mailing_state ?? '',
            $user_record->mailing_zipcode ?? ''
        ]);

        return empty($address_parts) ? '-' : implode(', ', $address_parts);
    }

    /**
     * Get pickup display data for template with exchange data priority
     *
     * @param stdClass $row
     * @return array
     */
    public function get_pickup_display_data(stdClass $row): array {
        $pickup_data = [];

        // Priority 1: Exchange submission data
        if (!empty($row->exchange_pickup_method) || !empty($row->exchange_pickup_person_name) || !empty($row->exchange_partnership_name)) {
            $pickup_data['method'] = $row->exchange_pickup_method ?? '';
            $pickup_data['person_name'] = $row->exchange_pickup_person_name ?? '';
            $pickup_data['person_phone'] = $row->exchange_pickup_person_phone ?? '';
            $pickup_data['person_details'] = $row->exchange_pickup_person_details ?? '';
            $pickup_data['partnership_name'] = $row->exchange_partnership_name ?? '';
            $pickup_data['partnership_address'] = $this->format_exchange_address($row, 'pickup');
            $pickup_data['timeframe'] = $this->format_exchange_timeframe($row);
            $pickup_data['source'] = 'exchange';
        } else {
            // Priority 2: Fallback to VCC submission data
            if ($row->pickupid) {
                $sql = "SELECT p.id, p.partnershipid, partnership.name as partnership_name
                        FROM {local_equipment_pickup} p
                        LEFT JOIN {local_equipment_partnership} partnership ON p.partnershipid = partnership.id
                        WHERE p.id = ?";
                $pickup = $this->db->get_record_sql($sql, [$row->pickupid]);
                if ($pickup) {
                    $pickup_data['partnership_name'] = $pickup->partnership_name;
                }
            }

            $pickup_data['method'] = $row->pickupmethod ?? '';
            $pickup_data['person_name'] = $row->pickuppersonname ?? '';
            $pickup_data['person_phone'] = $row->pickuppersonphone ?? '';
            $pickup_data['person_details'] = $row->pickuppersondetails ?? '';
            $pickup_data['timeframe'] = $row->pickup_locationtime ?? '';
            $pickup_data['source'] = 'vcc';
        }

        return $pickup_data;
    }

    /**
     * Format exchange address based on type
     *
     * @param stdClass $row
     * @param string $type physical|mailing|pickup
     * @return string
     */
    private function format_exchange_address(stdClass $row, string $type = 'pickup'): string {
        $field_name = "exchange_partnership_{$type}_address";

        if (!empty($row->{$field_name})) {
            return trim($row->{$field_name});
        }

        return '';
    }

    /**
     * Format exchange timeframe from pickup schedule
     *
     * @param stdClass $row
     * @return string
     */
    private function format_exchange_timeframe(stdClass $row): string {
        if (empty($row->exchange_pickup_date)) {
            return '';
        }

        $date_str = userdate($row->exchange_pickup_date, get_string('strftimedate', 'core_langconfig'));

        $time_parts = [];
        if (!empty($row->exchange_pickup_starttime)) {
            $time_parts[] = userdate($row->exchange_pickup_starttime, get_string('strftimetime12', 'core_langconfig'));
        }
        if (!empty($row->exchange_pickup_endtime)) {
            $time_parts[] = userdate($row->exchange_pickup_endtime, get_string('strftimetime12', 'core_langconfig'));
        }

        $time_str = empty($time_parts) ? '' : implode(' - ', $time_parts);

        return trim($date_str . ($time_str ? ' at ' . $time_str : ''));
    }

    /**
     * Get export data with all records (no pagination)
     *
     * @param stdClass $filters
     * @return array
     */
    public function get_export_data(stdClass $filters): array {
        [$select, $from, $where, $params] = $this->build_table_sql($filters);

        $sql = "SELECT $select FROM $from WHERE $where ORDER BY vccsubmission.timecreated DESC";

        return array_values($this->db->get_records_sql($sql, $params));
    }

    /**
     * Get students text for export (simplified format)
     *
     * @param stdClass $row
     * @return string
     */
    public function get_students_text_for_export(stdClass $row): string {
        if (empty($row->studentids)) {
            return '-';
        }

        $student_ids = json_decode($row->studentids, true);
        if (empty($student_ids)) {
            return '-';
        }

        $students_text = [];
        foreach ($student_ids as $student_id) {
            $student = $this->db->get_record('local_equipment_vccsubmission_student', ['id' => $student_id]);
            if ($student) {
                $courses = $this->get_student_courses((int)$student_id);
                $courses_text = empty($courses) ? 'No courses' : implode('; ', array_column($courses, 'name'));
                $students_text[] = $student->firstname . ' ' . $student->lastname . ' (' . $courses_text . ')';
            }
        }

        return implode(' | ', $students_text);
    }

    /**
     * Get pickup text for export with exchange data priority
     *
     * @param stdClass $row
     * @return string
     */
    public function get_pickup_text_for_export(stdClass $row): string {
        $pickup_parts = [];

        // Priority 1: Exchange submission data
        if (!empty($row->exchange_pickup_method) || !empty($row->exchange_pickup_person_name) || !empty($row->exchange_partnership_name)) {
            if ($row->exchange_partnership_name) {
                $pickup_parts[] = 'Partnership: ' . $row->exchange_partnership_name;
            }

            if ($row->exchange_pickup_method) {
                $pickup_parts[] = 'Method: ' . $row->exchange_pickup_method;
            }

            $timeframe = $this->format_exchange_timeframe($row);
            if ($timeframe) {
                $pickup_parts[] = 'Timeframe: ' . $timeframe;
            }

            if ($row->exchange_pickup_person_name) {
                $pickup_parts[] = 'Person: ' . $row->exchange_pickup_person_name;
                if ($row->exchange_pickup_person_phone) {
                    $pickup_parts[] = 'Phone: ' . $row->exchange_pickup_person_phone;
                }
            }

            if ($row->exchange_pickup_person_details) {
                $pickup_parts[] = 'Details: ' . $row->exchange_pickup_person_details;
            }

            $address = $this->format_exchange_address($row, 'pickup');
            if ($address) {
                $pickup_parts[] = 'Address: ' . $address;
            }

            $pickup_parts[] = 'Source: Exchange';
        } else {
            // Priority 2: Fallback to VCC submission data
            if ($row->pickupid) {
                $sql = "SELECT p.id, p.partnershipid, partnership.name as partnership_name
                        FROM {local_equipment_pickup} p
                        LEFT JOIN {local_equipment_partnership} partnership ON p.partnershipid = partnership.id
                        WHERE p.id = ?";
                $pickup = $this->db->get_record_sql($sql, [$row->pickupid]);
                if ($pickup) {
                    $pickup_parts[] = 'Location: ' . $pickup->partnership_name;
                }
            }

            if ($row->pickupmethod) {
                $pickup_parts[] = 'Method: ' . $row->pickupmethod;
            }

            if ($row->pickup_locationtime) {
                $pickup_parts[] = 'Time: ' . $row->pickup_locationtime;
            }

            if ($row->pickuppersonname) {
                $pickup_parts[] = 'Person: ' . $row->pickuppersonname;
                if ($row->pickuppersonphone) {
                    $pickup_parts[] = 'Phone: ' . $row->pickuppersonphone;
                }
            }

            if ($row->pickuppersondetails) {
                $pickup_parts[] = 'Details: ' . $row->pickuppersondetails;
            }

            $pickup_parts[] = 'Source: VCC';
        }

        return empty($pickup_parts) ? '-' : implode('; ', $pickup_parts);
    }

    /**
     * Get exchange partnership details for display
     *
     * @param stdClass $row
     * @return array
     */
    public function get_exchange_partnership_details(stdClass $row): array {
        return [
            'name' => $row->exchange_partnership_name ?? '',
            'physical_address' => $this->format_exchange_address($row, 'physical'),
            'mailing_address' => $this->format_exchange_address($row, 'mailing'),
            'pickup_address' => $this->format_exchange_address($row, 'pickup')
        ];
    }

    /**
     * Get complete exchange data for display
     *
     * @param stdClass $row
     * @return array
     */
    public function get_exchange_data(stdClass $row): array {
        return [
            'partnership' => $this->get_exchange_partnership_details($row),
            'timeframe' => $this->format_exchange_timeframe($row),
            'pickup_method' => $row->exchange_pickup_method ?? '',
            'pickup_person_name' => $row->exchange_pickup_person_name ?? '',
            'pickup_person_phone' => $row->exchange_pickup_person_phone ?? '',
            'pickup_person_details' => $row->exchange_pickup_person_details ?? '',
            'user_notes' => $row->exchange_user_notes ?? '',
            'pickup_location_address' => $row->exchange_pickup_location_address ?? ''
        ];
    }
}
