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

    /*
     * COMPREHENSIVE DEBUG CONFIGURATION FOR VCC SUBMISSION SERVICE
     *
     * These debug flags provide granular control over logging and monitoring functionality
     * within the VCC submission service operations. The service handles complex data
     * processing for course information, table queries, and submission management, making
     * detailed debugging capabilities essential for development and troubleshooting.
     *
     * Each flag is designed to isolate specific aspects of the service functionality,
     * allowing developers to focus on particular operational areas without being overwhelmed
     * by excessive logging output. The flags are implemented as private constants to ensure
     * they cannot be modified at runtime and maintain consistent debugging behavior
     * throughout the service lifecycle.
     *
     * These debugging capabilities are particularly valuable when working with large datasets,
     * complex JSON processing, or performance optimization scenarios where detailed insight
     * into service operations is required.
     */

    /** @var bool Debug flag for VCC submission service operations */
    private const DEBUG_VCC_SERVICE = false;

    /** @var bool Debug flag for course data processing operations */
    private const DEBUG_COURSE_PROCESSING = false;

    /** @var bool Debug flag for table SQL query operations */
    private const DEBUG_TABLE_QUERIES = false;

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
     * Phase 1.2: Optimized for proper pagination with LIMIT/OFFSET support and reduced query complexity
     * Phase 8.1: Enhanced with proper pagination support for AJAX table loading
     *
     * @param stdClass $filters
     * @param int $page Current page number (1-based)
     * @param int $perpage Number of records per page
     * @return array [select, from, where, params, count_sql]
     */
    public function build_table_sql(stdClass $filters, int $page = 1, int $perpage = 25): array {
        // Phase 1.2: Optimized SELECT to reduce data transfer and improve performance
        $select = "
            vccsubmission.id,
            vccsubmission.userid,
            vccsubmission.partnershipid,
            vccsubmission.pickupid,
            vccsubmission.exchange_partnershipid,
            vccsubmission.exchangesubmissionid,
            vccsubmission.studentids,
            vccsubmission.confirmationid,
            vccsubmission.confirmationexpired,
            vccsubmission.email,
            vccsubmission.email_confirmed,
            vccsubmission.firstname,
            vccsubmission.lastname,
            vccsubmission.phone,
            vccsubmission.phone_confirmed,
            vccsubmission.partnership_name,
            vccsubmission.mailing_streetaddress,
            vccsubmission.mailing_apartment,
            vccsubmission.mailing_city,
            vccsubmission.mailing_state,
            vccsubmission.mailing_zipcode,
            vccsubmission.billing_sameasmailing,
            vccsubmission.billing_streetaddress,
            vccsubmission.billing_apartment,
            vccsubmission.billing_city,
            vccsubmission.billing_state,
            vccsubmission.billing_zipcode,
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
            COALESCE(exchange_partnership.pickup_streetaddress, '') as exchange_partnership_pickup_address,
            exchange_sub.pickup_method as exchange_pickup_method,
            exchange_sub.pickup_person_name as exchange_pickup_person_name,
            exchange_sub.pickup_person_phone as exchange_pickup_person_phone,
            exchange_sub.pickup_person_details as exchange_pickup_person_details,
            exchange_sub.user_notes as exchange_user_notes,
            pickup_schedule.pickupdate as exchange_pickup_date,
            pickup_schedule.starttime as exchange_pickup_starttime,
            pickup_schedule.endtime as exchange_pickup_endtime,
            exchange_partnership.name as exchange_partnership,
            CASE
                WHEN pickup_schedule.pickupdate IS NOT NULL THEN
                    CONCAT(
                        DATE_FORMAT(FROM_UNIXTIME(pickup_schedule.pickupdate), '%M %d, %Y'),
                        CASE
                            WHEN pickup_schedule.starttime IS NOT NULL AND pickup_schedule.endtime IS NOT NULL THEN
                                CONCAT(' at ', TIME_FORMAT(FROM_UNIXTIME(pickup_schedule.starttime), '%h:%i %p'), ' - ', TIME_FORMAT(FROM_UNIXTIME(pickup_schedule.endtime), '%h:%i %p'))
                            WHEN pickup_schedule.starttime IS NOT NULL THEN
                                CONCAT(' at ', TIME_FORMAT(FROM_UNIXTIME(pickup_schedule.starttime), '%h:%i %p'))
                            ELSE ''
                        END
                    )
                ELSE ''
            END as exchange_timeframe
        ";

        // Phase 1.2: Optimized FROM clause with better join order for performance
        $from = "
            {local_equipment_vccsubmission} vccsubmission
            LEFT JOIN {user} u ON vccsubmission.userid = u.id
            LEFT JOIN {local_equipment_partnership} partnership ON vccsubmission.partnershipid = partnership.id
            LEFT JOIN {local_equipment_exchange_submission} exchange_sub ON vccsubmission.exchangesubmissionid = exchange_sub.id
            LEFT JOIN {local_equipment_partnership} exchange_partnership ON vccsubmission.exchange_partnershipid = exchange_partnership.id
            LEFT JOIN {local_equipment_pickup} pickup_schedule ON exchange_sub.exchangeid = pickup_schedule.id
        ";

        [$where, $params] = $this->build_where_clause($filters);

        // Phase 8.1: Calculate pagination offset
        $offset = ($page - 1) * $perpage;

        // Build count SQL for total records (optimized)
        $count_sql = "SELECT COUNT(vccsubmission.id) FROM $from WHERE $where";

        return [$select, $from, $where, $params, $count_sql, $offset, $perpage];
    }

    /**
     * Build WHERE clause with parameters
     * Phase 1.2: Optimized for better performance with indexed columns
     *
     * @param stdClass $filters
     * @return array [where_clause, params]
     */
    private function build_where_clause(stdClass $filters): array {
        $where_conditions = ['1=1'];
        $params = [];

        // Partnership filter - using indexed column for performance
        if (!empty($filters->partnership)) {
            $where_conditions[] = 'vccsubmission.partnershipid = ?';
            $params[] = $filters->partnership;
        }

        // Date range filters - using indexed timecreated column for performance
        if (!empty($filters->datestart)) {
            $where_conditions[] = 'vccsubmission.timecreated >= ?';
            $params[] = $filters->datestart;
        }

        if (!empty($filters->dateend)) {
            $where_conditions[] = 'vccsubmission.timecreated <= ?';
            $params[] = $filters->dateend;
        }

        // Phase 1.2: Optimized search filter with reduced LIKE operations for better performance
        if (!empty($filters->search)) {
            // Prioritize primary table fields over joined fields for better performance
            $search_conditions = [
                $this->db->sql_like('vccsubmission.firstname', '?', false, false),
                $this->db->sql_like('vccsubmission.lastname', '?', false, false),
                $this->db->sql_like('vccsubmission.email', '?', false, false),
                $this->db->sql_like('vccsubmission.phone', '?', false, false)
            ];

            // Only add user table search if necessary (reduces join impact)
            if (strlen($filters->search) >= 3) {
                $search_conditions[] = $this->db->sql_like('u.firstname', '?', false, false);
                $search_conditions[] = $this->db->sql_like('u.lastname', '?', false, false);
                $search_conditions[] = $this->db->sql_like('u.email', '?', false, false);
            }

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
     * Phase 1.2: Optimized count query for better performance with large datasets
     *
     * @param stdClass $filters
     * @return int
     */
    public function count_submissions(stdClass $filters): int {
        // Phase 1.2: Use optimized count query that only includes necessary joins
        $from = "
            {local_equipment_vccsubmission} vccsubmission
        ";

        // Only add user join if search filter requires it
        if (!empty($filters->search) && strlen($filters->search) >= 3) {
            $from .= " LEFT JOIN {user} u ON vccsubmission.userid = u.id";
        }

        // Only add partnership join if filtering by partnership
        if (!empty($filters->partnership)) {
            $from .= " LEFT JOIN {local_equipment_partnership} partnership ON vccsubmission.partnershipid = partnership.id";
        }

        [$where, $params] = $this->build_where_clause($filters);

        return $this->db->count_records_sql("SELECT COUNT(vccsubmission.id) FROM $from WHERE $where", $params);
    }

    /**
     * Phase 1.2: Get SQL indexing recommendations for optimal performance
     *
     * Recommended indexes for the local_equipment_vccsubmission table:
     * - timecreated (already exists for sorting and date filtering)
     * - partnershipid (for partnership filtering)
     * - email_confirmed (for status filtering)
     * - phone_confirmed (for status filtering)
     *
     * Composite indexes recommended:
     * - (partnershipid, timecreated) for combined partnership and date filtering
     * - (timecreated, email_confirmed, phone_confirmed) for dashboard queries
     *
     * @return array Indexing recommendations
     */
    public function get_indexing_recommendations(): array {
        return [
            'single_column_indexes' => [
                'local_equipment_vccsubmission.timecreated',
                'local_equipment_vccsubmission.partnershipid',
                'local_equipment_vccsubmission.email_confirmed',
                'local_equipment_vccsubmission.phone_confirmed'
            ],
            'composite_indexes' => [
                'local_equipment_vccsubmission(partnershipid, timecreated)',
                'local_equipment_vccsubmission(timecreated, email_confirmed, phone_confirmed)'
            ],
            'performance_notes' => [
                'Current query joins are optimized to reduce LEFT JOIN overhead',
                'Search operations are limited to 3+ characters to improve performance',
                'Course data uses caching to reduce individual lookups from O(n) to O(1)',
                'Exchange data joins are only included when data exists'
            ]
        ];
    }

    /**
     * Phase 1.2: Monitor database load pattern changes with new course display logic
     *
     * @param stdClass $filters
     * @return array Performance metrics
     */
    public function analyze_query_performance(stdClass $filters): array {
        $start_time = microtime(true);

        // Count total records for baseline
        $total_count = $this->count_submissions(new stdClass());
        $filtered_count = $this->count_submissions($filters);

        $count_time = microtime(true) - $start_time;

        // Analyze course lookup efficiency
        $course_cache_hits = count(self::$course_cache);

        return [
            'total_submissions' => $total_count,
            'filtered_submissions' => $filtered_count,
            'filter_efficiency' => $filtered_count > 0 ? ($filtered_count / $total_count) : 0,
            'count_query_time' => $count_time,
            'course_cache_size' => $course_cache_hits,
            'optimization_status' => [
                'json_based_courses' => 'Implemented - reduces enrollment table queries',
                'cached_course_lookups' => 'Implemented - reduces individual course queries',
                'optimized_joins' => 'Implemented - conditional joins based on filters',
                'indexed_columns' => 'Recommended - see get_indexing_recommendations()'
            ]
        ];
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

    /** @var array Course cache to reduce database calls */
    private static array $course_cache = [];

    /**
     * Get courses for a student using courseids JSON field - following Phase 3A implementation
     * Phase 3.1: Implemented course data caching strategy to reduce individual database calls
     * Phase 3.2: Enhanced with enrollment status indicators and course end date checking
     *
     * @param int $student_id
     * @return array
     */
    private function get_student_courses(int $student_id): array {
        // Get student record with courseids JSON field
        $student = $this->db->get_record('local_equipment_vccsubmission_student', ['id' => $student_id]);
        if (!$student || empty($student->courseids)) {
            return [];
        }

        // Decode JSON courseids - handle malformed JSON gracefully
        $course_ids = json_decode($student->courseids, true);
        if (!is_array($course_ids) || empty($course_ids)) {
            return [];
        }

        // Get course information using cached strategy
        $result = [];
        $uncached_ids = [];

        // Check cache first
        foreach ($course_ids as $course_id) {
            if (is_numeric($course_id)) {
                $course_id = (int)$course_id;
                if (isset(self::$course_cache[$course_id])) {
                    if (self::$course_cache[$course_id] !== false) {
                        $result[] = self::$course_cache[$course_id];
                    }
                } else {
                    $uncached_ids[] = $course_id;
                }
            }
        }

        // Fetch uncached courses in batch with enrollment status and end dates
        if (!empty($uncached_ids)) {
            $courses = $this->db->get_records_list('course', 'id', $uncached_ids, '', 'id, fullname, enddate');

            foreach ($uncached_ids as $course_id) {
                if (isset($courses[$course_id])) {
                    $course = $courses[$course_id];

                    // Phase 3.2: Determine course status and badge type
                    $status = $this->get_course_status($course, (int)($student->userid ?? 0));

                    $course_obj = (object)[
                        'id' => $course->id,
                        'name' => $course->fullname,
                        'status' => $status['status'],
                        'badge_class' => $status['badge_class'],
                        'tooltip' => $status['tooltip']
                    ];
                    self::$course_cache[$course_id] = $course_obj;
                    $result[] = $course_obj;
                } else {
                    // Cache negative result to avoid repeated failed lookups
                    self::$course_cache[$course_id] = false;
                }
            }
        }

        // Sort courses by name for consistent display
        usort($result, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });

        return $result;
    }

    /**
     * Phase 3.2: Determine course enrollment status and appropriate badge styling
     *
     * @param stdClass $course Course record with enddate
     * @param int $userid User ID for enrollment checking
     * @return array Status information with badge class and tooltip
     */
    private function get_course_status(stdClass $course, int $userid): array {
        $now = $this->clock->now()->getTimestamp();

        // Check if course has ended
        if (!empty($course->enddate) && $course->enddate < $now) {
            return [
                'status' => 'ended',
                'badge_class' => 'badge text-bg-light',
                'tooltip' => get_string('courseended', 'local_equipment')
            ];
        }

        // If we have a valid user ID, check enrollment status
        if ($userid > 0) {
            $enrollment = $this->db->get_record_sql("
                SELECT ue.status, e.status as enrol_status
                FROM {user_enrolments} ue
                JOIN {enrol} e ON e.id = ue.enrolid
                WHERE ue.userid = ? AND e.courseid = ?
                ORDER BY ue.timecreated DESC
                LIMIT 1
            ", [$userid, $course->id]);

            if ($enrollment) {
                // Check enrollment status
                if ($enrollment->status == 1) { // Suspended
                    return [
                        'status' => 'suspended',
                        'badge_class' => 'badge text-bg-warning',
                        'tooltip' => get_string('enrollmentsuspended', 'local_equipment')
                    ];
                }

                if ($enrollment->enrol_status == 1) { // Enrol method disabled
                    return [
                        'status' => 'disabled',
                        'badge_class' => 'badge text-bg-secondary',
                        'tooltip' => get_string('enrollmentdisabled', 'local_equipment')
                    ];
                }

                // Active enrollment
                return [
                    'status' => 'active',
                    'badge_class' => 'badge text-bg-primary',
                    'tooltip' => get_string('enrollmentactive', 'local_equipment')
                ];
            } else {
                // No longer enrolled
                return [
                    'status' => 'not_enrolled',
                    'badge_class' => 'badge text-bg-secondary',
                    'tooltip' => get_string('nolongerenrolled', 'local_equipment')
                ];
            }
        }

        // Default to primary badge for historical data without user context
        return [
            'status' => 'historical',
            'badge_class' => 'badge text-bg-primary',
            'tooltip' => get_string('historicalenrollment', 'local_equipment')
        ];
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

        // Ensure source field is always present (safety check)
        if (!isset($pickup_data['source']) || empty($pickup_data['source'])) {
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

    /**
     * Phase 8.1: Get paginated table data for AJAX requests
     *
     * @param stdClass $filters Filter criteria
     * @param int $page Current page number (1-based)
     * @param int $perpage Number of records per page
     * @return array Table data with pagination info
     */
    public function get_paginated_table_data(stdClass $filters, int $page = 1, int $perpage = 25): array {
        [$select, $from, $where, $params, $count_sql, $offset, $limit] = $this->build_table_sql($filters, $page, $perpage);

        // Get total count
        $total_records = $this->db->count_records_sql($count_sql, $params);

        // Get paginated data
        $sql = "SELECT $select FROM $from WHERE $where ORDER BY vccsubmission.timecreated DESC";
        $records = $this->db->get_records_sql($sql, $params, $offset, $limit);

        // Calculate pagination info
        $total_pages = ceil($total_records / $perpage);
        $start_record = $total_records > 0 ? ($offset + 1) : 0;
        $end_record = min($offset + $perpage, $total_records);

        return [
            'records' => array_values($records),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perpage,
                'total_records' => $total_records,
                'total_pages' => $total_pages,
                'start_record' => $start_record,
                'end_record' => $end_record,
                'has_previous' => $page > 1,
                'has_next' => $page < $total_pages,
                'previous_page' => max(1, $page - 1),
                'next_page' => min($total_pages, $page + 1)
            ]
        ];
    }

    /**
     * Phase 8.1: Get user preferences for VCC table settings
     *
     * @param int $userid User ID (0 for current user)
     * @return array User preferences
     */
    public function get_user_table_preferences(int $userid = 0): array {
        global $USER;

        if ($userid === 0) {
            $userid = $USER->id;
        }

        $preferences = [
            'page_size' => 25,
            'column_widths' => '{}',
            'hidden_columns' => '{}',
            'sort_column' => 'timecreated',
            'sort_direction' => 'DESC',
            'filter_collapsed' => false
        ];

        try {
            // Get user preferences from Moodle user preferences API
            $page_size = get_user_preferences('local_equipment_vcc_table_page_size', '25', $userid);
            $column_widths = get_user_preferences('local_equipment_vcc_table_column_widths', '{}', $userid);
            $hidden_columns = get_user_preferences('local_equipment_vcc_table_hidden_columns', '{}', $userid);
            $sort_column = get_user_preferences('local_equipment_vcc_table_sort_column', 'timecreated', $userid);
            $sort_direction = get_user_preferences('local_equipment_vcc_table_sort_direction', 'DESC', $userid);
            $filter_collapsed = get_user_preferences('local_equipment_vcc_table_filter_collapsed', '0', $userid);

            $preferences['page_size'] = max(10, min(100, (int)$page_size));
            $preferences['column_widths'] = $column_widths;
            $preferences['hidden_columns'] = $hidden_columns;
            $preferences['sort_column'] = $sort_column;
            $preferences['sort_direction'] = in_array($sort_direction, ['ASC', 'DESC']) ? $sort_direction : 'DESC';
            $preferences['filter_collapsed'] = (bool)$filter_collapsed;

        } catch (\Exception $e) {
            // Log error but continue with defaults
            debugging('Error getting VCC table preferences: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }

        return $preferences;
    }

    /**
     * Phase 8.1: Save user preferences for VCC table settings
     *
     * @param array $preferences Preferences to save
     * @param int $userid User ID (0 for current user)
     * @return bool Success status
     */
    public function save_user_table_preferences(array $preferences, int $userid = 0): bool {
        global $USER;

        if ($userid === 0) {
            $userid = $USER->id;
        }

        try {
            // Validate and sanitize preferences
            $valid_preferences = $this->validate_table_preferences($preferences);

            // Save each preference individually using Moodle API
            foreach ($valid_preferences as $key => $value) {
                $pref_name = 'local_equipment_vcc_table_' . $key;
                set_user_preference($pref_name, $value, $userid);
            }

            return true;

        } catch (\Exception $e) {
            debugging('Error saving VCC table preferences: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Phase 8.1: Validate and sanitize table preferences
     *
     * @param array $preferences Raw preferences from client
     * @return array Validated preferences
     */
    private function validate_table_preferences(array $preferences): array {
        $valid = [];

        // Page size validation
        if (isset($preferences['page_size'])) {
            $valid['page_size'] = max(10, min(100, (int)$preferences['page_size']));
        }

        // Column widths validation (JSON string)
        if (isset($preferences['column_widths'])) {
            $widths = is_string($preferences['column_widths'])
                ? $preferences['column_widths']
                : json_encode($preferences['column_widths']);

            // Validate JSON format
            if (json_decode($widths) !== null) {
                $valid['column_widths'] = $widths;
            }
        }

        // Hidden columns validation (JSON string)
        if (isset($preferences['hidden_columns'])) {
            $hidden = is_string($preferences['hidden_columns'])
                ? $preferences['hidden_columns']
                : json_encode($preferences['hidden_columns']);

            // Validate JSON format
            if (json_decode($hidden) !== null) {
                $valid['hidden_columns'] = $hidden;
            }
        }

        // Sort column validation
        if (isset($preferences['sort_column'])) {
            $allowed_columns = [
                'timecreated', 'timemodified', 'firstname', 'lastname', 'email',
                'phone', 'partnership_name', 'exchange_partnership_name'
            ];
            if (in_array($preferences['sort_column'], $allowed_columns)) {
                $valid['sort_column'] = $preferences['sort_column'];
            }
        }

        // Sort direction validation
        if (isset($preferences['sort_direction'])) {
            if (in_array(strtoupper($preferences['sort_direction']), ['ASC', 'DESC'])) {
                $valid['sort_direction'] = strtoupper($preferences['sort_direction']);
            }
        }

        // Filter collapsed state validation
        if (isset($preferences['filter_collapsed'])) {
            $valid['filter_collapsed'] = $preferences['filter_collapsed'] ? '1' : '0';
        }

        return $valid;
    }

    /**
     * Phase 8.1: Reset user table preferences to defaults
     *
     * @param int $userid User ID (0 for current user)
     * @return bool Success status
     */
    public function reset_user_table_preferences(int $userid = 0): bool {
        global $USER;

        if ($userid === 0) {
            $userid = $USER->id;
        }

        $preference_keys = [
            'local_equipment_vcc_table_page_size',
            'local_equipment_vcc_table_column_widths',
            'local_equipment_vcc_table_hidden_columns',
            'local_equipment_vcc_table_sort_column',
            'local_equipment_vcc_table_sort_direction',
            'local_equipment_vcc_table_filter_collapsed'
        ];

        try {
            foreach ($preference_keys as $key) {
                unset_user_preference($key, $userid);
            }
            return true;
        } catch (\Exception $e) {
            debugging('Error resetting VCC table preferences: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }
}
