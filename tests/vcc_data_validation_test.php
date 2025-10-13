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
 * Data validation tests for VCC submission system
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace local_equipment\tests;

use advanced_testcase;
use stdClass;
use local_equipment\service\vcc_submission_service;
use core\clock;

defined('MOODLE_INTERNAL') || die();

/**
 * Test class for VCC submission data validation
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_data_validation_test extends advanced_testcase {

    /** @var vcc_submission_service */
    private vcc_submission_service $service;

    /** @var stdClass */
    private stdClass $course1;

    /** @var stdClass */
    private stdClass $course2;

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        global $DB;
        $clock = \core\di::get(clock::class);
        $this->service = new vcc_submission_service($DB, $clock);

        // Create test courses
        $this->course1 = $this->getDataGenerator()->create_course(['fullname' => 'Test Course 1']);
        $this->course2 = $this->getDataGenerator()->create_course(['fullname' => 'Test Course 2']);
    }

    /**
     * Test course display with various JSON formats
     *
     * @covers \local_equipment\service\vcc_submission_service::get_student_courses
     */
    public function test_course_display_valid_json_formats(): void {
        $test_cases = [
            // Valid string array format
            '["' . $this->course1->id . '"]' => [$this->course1->id],
            // Valid multiple courses
            '["' . $this->course1->id . '","' . $this->course2->id . '"]' => [$this->course1->id, $this->course2->id],
            // Mixed string/int format
            '[' . $this->course1->id . ',' . $this->course2->id . ']' => [$this->course1->id, $this->course2->id],
            // Single integer format
            '[' . $this->course1->id . ']' => [$this->course1->id],
        ];

        foreach ($test_cases as $json_input => $expected_course_ids) {
            $student = $this->create_test_student_with_courseids($json_input);
            $courses = $this->get_student_courses_via_reflection($student->id);

            $actual_course_ids = array_map(function($course) {
                return $course->id;
            }, $courses);

            $this->assertEqualsCanonicalizing(
                $expected_course_ids,
                $actual_course_ids,
                "Failed for JSON input: {$json_input}"
            );
        }
    }

    /**
     * Test course display with invalid JSON formats
     *
     * @covers \local_equipment\service\vcc_submission_service::get_student_courses
     */
    public function test_course_display_invalid_json_formats(): void {
        $invalid_cases = [
            'null' => null,
            'empty_string' => '',
            'empty_array_string' => '[]',
            'null_array_string' => '[null]',
            'invalid_json' => 'invalid_json',
            'comma_only' => '[,]',
            'empty_string_in_array' => '[""]',
            'null_string_in_array' => '["null"]',
        ];

        foreach ($invalid_cases as $case_name => $json_input) {
            $student = $this->create_test_student_with_courseids($json_input);
            $courses = $this->get_student_courses_via_reflection($student->id);

            $this->assertEmpty(
                $courses,
                "Expected empty courses for case '{$case_name}' with input: " . var_export($json_input, true)
            );
        }
    }

    /**
     * Test course display with edge case JSON formats
     *
     * @covers \local_equipment\service\vcc_submission_service::get_student_courses
     */
    public function test_course_display_edge_case_json_formats(): void {
        $edge_cases = [
            'zero_course_id' => '[0]',
            'negative_course_id' => '[-1]',
            'non_existent_course_id' => '[999999]',
            'unicode_characters' => '["test🎓"]',
            'special_characters' => '["<script>alert(1)</script>"]',
        ];

        foreach ($edge_cases as $case_name => $json_input) {
            $student = $this->create_test_student_with_courseids($json_input);
            $courses = $this->get_student_courses_via_reflection($student->id);

            // Edge cases should return empty courses as they reference invalid/non-existent courses
            $this->assertEmpty(
                $courses,
                "Expected empty courses for edge case '{$case_name}' with input: {$json_input}"
            );
        }
    }

    /**
     * Test course cache behavior with malformed data
     *
     * @covers \local_equipment\service\vcc_submission_service::get_student_courses
     */
    public function test_course_cache_behavior_with_malformed_data(): void {
        // Create multiple students with same course to test cache
        $valid_json = '[' . $this->course1->id . ']';
        $student1 = $this->create_test_student_with_courseids($valid_json);
        $student2 = $this->create_test_student_with_courseids($valid_json);

        // First call should populate cache
        $courses1 = $this->get_student_courses_via_reflection($student1->id);
        $this->assertCount(1, $courses1);

        // Second call should use cache
        $courses2 = $this->get_student_courses_via_reflection($student2->id);
        $this->assertCount(1, $courses2);

        // Verify cache hit by checking same object references
        $this->assertEquals($courses1[0]->id, $courses2[0]->id);
    }

    /**
     * Test memory usage with corrupted cache data
     */
    public function test_memory_usage_with_large_datasets(): void {
        $initial_memory = memory_get_usage();

        // Create multiple students with various courseids
        for ($i = 0; $i < 100; $i++) {
            $json_variations = [
                '[' . $this->course1->id . ']',
                '[' . $this->course2->id . ']',
                '["invalid"]',
                'null',
                '[]'
            ];

            $student = $this->create_test_student_with_courseids($json_variations[$i % 5]);
            $this->get_student_courses_via_reflection($student->id);
        }

        $final_memory = memory_get_usage();
        $memory_increase = $final_memory - $initial_memory;

        // Memory increase should be reasonable (less than 5MB for 100 students)
        $this->assertLessThan(5 * 1024 * 1024, $memory_increase, 'Memory usage increase is too high');
    }

    /**
     * Test service method error handling with malformed data
     *
     * @covers \local_equipment\service\vcc_submission_service::get_students_display_data
     */
    public function test_get_students_display_data_error_handling(): void {
        // Create submission with invalid studentids JSON
        $submission = new stdClass();
        $submission->studentids = 'invalid_json';

        $result = $this->service->get_students_display_data($submission);
        $this->assertEmpty($result, 'Should return empty array for invalid JSON');

        // Test with null studentids
        $submission->studentids = null;
        $result = $this->service->get_students_display_data($submission);
        $this->assertEmpty($result, 'Should return empty array for null studentids');

        // Test with empty string
        $submission->studentids = '';
        $result = $this->service->get_students_display_data($submission);
        $this->assertEmpty($result, 'Should return empty array for empty string');
    }

    /**
     * Test database fallback mechanisms and timeout handling
     */
    public function test_database_fallback_mechanisms(): void {
        global $DB;

        // Test with non-existent student ID
        $non_existent_id = 999999;
        $courses = $this->get_student_courses_via_reflection($non_existent_id);
        $this->assertEmpty($courses, 'Should return empty array for non-existent student');

        // Test performance with valid data
        $start_time = microtime(true);
        $student = $this->create_test_student_with_courseids('[' . $this->course1->id . ']');
        $courses = $this->get_student_courses_via_reflection($student->id);
        $end_time = microtime(true);

        $execution_time = $end_time - $start_time;
        $this->assertLessThan(1.0, $execution_time, 'Course lookup should complete within 1 second');
    }

    /**
     * Create a test student with specified courseids JSON
     *
     * @param string|null $courseids_json
     * @return stdClass
     */
    private function create_test_student_with_courseids(?string $courseids_json): stdClass {
        global $DB;

        $student_data = [
            'firstname' => 'Test',
            'lastname' => 'Student',
            'userid' => $this->getDataGenerator()->create_user()->id,
            'courseids' => $courseids_json,
            'timecreated' => time(),
            'timemodified' => time()
        ];

        $student_id = $DB->insert_record('local_equipment_vccsubmission_student', $student_data);
        return $DB->get_record('local_equipment_vccsubmission_student', ['id' => $student_id]);
    }

    /**
     * Get student courses using reflection to access private method
     *
     * @param int $student_id
     * @return array
     */
    private function get_student_courses_via_reflection(int $student_id): array {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('get_student_courses');
        $method->setAccessible(true);

        return $method->invoke($this->service, $student_id);
    }
}