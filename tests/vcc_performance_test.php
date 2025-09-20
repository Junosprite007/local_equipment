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
 * Performance tests for VCC submission system
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace local_equipment\tests;

use advanced_testcase;
use local_equipment\service\vcc_submission_service;
use local_equipment\external\get_table_data;
use local_equipment\tests\helpers\vcc_test_data_generator;
use core\clock;

defined('MOODLE_INTERNAL') || die();

/**
 * Test class for VCC performance benchmarking
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_performance_test extends advanced_testcase {

    /** @var vcc_submission_service */
    private vcc_submission_service $service;

    /** @var stdClass */
    private $admin_user;

    /** @var array Performance benchmarks */
    private const PERFORMANCE_THRESHOLDS = [
        'query_execution' => 0.5,      // 500ms max for complex queries
        'template_rendering' => 0.1,   // 100ms max for template rendering
        'ajax_response' => 1.0,        // 1 second max for AJAX responses
        'memory_usage' => 50 * 1024 * 1024, // 50MB max memory increase
        'large_dataset' => 2.0         // 2 seconds max for 1000+ records
    ];

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        global $DB;
        $clock = \core\di::get(clock::class);
        $this->service = new vcc_submission_service($DB, $clock);

        // Create admin user with capabilities
        $this->admin_user = $this->getDataGenerator()->create_user();
        $admin_role = $this->getDataGenerator()->create_role();
        assign_capability('local/equipment:viewvccsubmissions', CAP_ALLOW, $admin_role, \context_system::instance());
        assign_capability('local/equipment:managevccsubmissions', CAP_ALLOW, $admin_role, \context_system::instance());
        role_assign($admin_role, $this->admin_user->id, \context_system::instance());
    }

    /**
     * Test query execution performance with large datasets
     *
     * @covers \local_equipment\service\vcc_submission_service::build_table_sql
     */
    public function test_query_performance_large_dataset(): void {
        global $DB;

        // Create large dataset
        $dataset_size = 1000;
        $this->create_performance_test_data($dataset_size);

        $filters = new \stdClass();
        $filters->search = 'test';

        // Test query building performance
        $start_time = microtime(true);
        [$select, $from, $where, $params, $count_sql] = $this->service->build_table_sql($filters, 1, 25);
        $query_build_time = microtime(true) - $start_time;

        $this->assertLessThan(
            0.1,
            $query_build_time,
            'Query building should complete within 100ms'
        );

        // Test actual query execution performance
        $start_time = microtime(true);
        $count = $DB->count_records_sql($count_sql, $params);
        $count_execution_time = microtime(true) - $start_time;

        $this->assertLessThan(
            self::PERFORMANCE_THRESHOLDS['query_execution'],
            $count_execution_time,
            'Count query should complete within performance threshold'
        );

        // Test data retrieval performance
        $start_time = microtime(true);
        $sql = "SELECT $select FROM $from WHERE $where ORDER BY vccsubmission.timecreated DESC";
        $records = $DB->get_records_sql($sql, $params, 0, 25);
        $data_execution_time = microtime(true) - $start_time;

        $this->assertLessThan(
            self::PERFORMANCE_THRESHOLDS['query_execution'],
            $data_execution_time,
            'Data retrieval should complete within performance threshold'
        );

        $this->assertGreaterThan(0, $count, 'Should find test records');
        $this->assertLessThanOrEqual(25, count($records), 'Should respect pagination limit');
    }

    /**
     * Test template rendering performance
     *
     * @covers \local_equipment template rendering performance
     */
    public function test_template_rendering_performance(): void {
        global $OUTPUT;

        $test_scenarios = [
            'simple_student_cell' => [
                'template' => 'local_equipment/vcc_students_cell',
                'iterations' => 100,
                'data' => [
                    'students' => [
                        [
                            'name' => 'Test Student',
                            'courses' => [
                                [
                                    'id' => 1,
                                    'name' => 'Test Course',
                                    'status' => 'active',
                                    'badge_class' => 'badge text-bg-primary',
                                    'tooltip' => 'Active enrollment'
                                ]
                            ],
                            'courses_text' => 'Test Course'
                        ]
                    ]
                ]
            ],
            'complex_pickup_cell' => [
                'template' => 'local_equipment/vcc_exchange_pickup_cell',
                'iterations' => 100,
                'data' => [
                    'method' => 'School Pickup',
                    'person_name' => 'John Doe',
                    'person_phone' => '+1234567890',
                    'person_details' => 'Authorized pickup person',
                    'partnership_name' => 'Test Partnership',
                    'partnership_address' => '123 Test St, Test City, TX 12345',
                    'timeframe' => 'Monday 9AM-3PM',
                    'source' => 'exchange'
                ]
            ]
        ];

        foreach ($test_scenarios as $scenario_name => $scenario) {
            $start_time = microtime(true);
            $start_memory = memory_get_usage();

            for ($i = 0; $i < $scenario['iterations']; $i++) {
                $OUTPUT->render_from_template($scenario['template'], $scenario['data']);
            }

            $execution_time = microtime(true) - $start_time;
            $memory_usage = memory_get_usage() - $start_memory;

            $avg_render_time = $execution_time / $scenario['iterations'];

            $this->assertLessThan(
                self::PERFORMANCE_THRESHOLDS['template_rendering'],
                $avg_render_time,
                "Template rendering for {$scenario_name} should be under threshold"
            );

            $this->assertLessThan(
                self::PERFORMANCE_THRESHOLDS['memory_usage'],
                $memory_usage,
                "Memory usage for {$scenario_name} should be reasonable"
            );
        }
    }

    /**
     * Test AJAX response performance
     *
     * @covers \local_equipment\external\get_table_data::execute
     */
    public function test_ajax_response_performance(): void {
        $this->setUser($this->admin_user);

        // Create test data
        $this->create_performance_test_data(100);

        $test_cases = [
            'basic_request' => [
                'page' => 1,
                'perpage' => 25,
                'filters' => []
            ],
            'filtered_request' => [
                'page' => 1,
                'perpage' => 25,
                'filters' => ['search' => 'test']
            ],
            'large_page_request' => [
                'page' => 1,
                'perpage' => 100,
                'filters' => []
            ]
        ];

        foreach ($test_cases as $case_name => $case_params) {
            $start_time = microtime(true);
            $start_memory = memory_get_usage();

            try {
                $result = get_table_data::execute(
                    $case_params['page'],
                    $case_params['perpage'],
                    $case_params['filters'],
                    sesskey()
                );

                $execution_time = microtime(true) - $start_time;
                $memory_usage = memory_get_usage() - $start_memory;

                $this->assertLessThan(
                    self::PERFORMANCE_THRESHOLDS['ajax_response'],
                    $execution_time,
                    "AJAX response for {$case_name} should be under threshold"
                );

                $this->assertLessThan(
                    self::PERFORMANCE_THRESHOLDS['memory_usage'],
                    $memory_usage,
                    "Memory usage for {$case_name} should be reasonable"
                );

                $this->assertIsArray($result, "Result for {$case_name} should be array");
                $this->assertArrayHasKey('success', $result, "Result for {$case_name} should have success key");

            } catch (\Exception $e) {
                $this->fail("AJAX request {$case_name} failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Test course lookup caching performance
     *
     * @covers \local_equipment\service\vcc_submission_service::get_student_courses
     */
    public function test_course_lookup_caching_performance(): void {
        global $DB;

        // Create test courses
        $courses = [];
        for ($i = 0; $i < 10; $i++) {
            $courses[] = $this->getDataGenerator()->create_course(['fullname' => "Test Course {$i}"]);
        }

        // Create students with course references
        $students = [];
        for ($i = 0; $i < 50; $i++) {
            $course_ids = array_slice($courses, 0, rand(1, 5));
            $course_ids_json = json_encode(array_map(function($course) {
                return (string)$course->id;
            }, $course_ids));

            $student_data = [
                'firstname' => "Student{$i}",
                'lastname' => "Test",
                'userid' => $this->getDataGenerator()->create_user()->id,
                'courseids' => $course_ids_json,
                'timecreated' => time(),
                'timemodified' => time()
            ];

            $student_id = $DB->insert_record('local_equipment_vccsubmission_student', $student_data);
            $students[] = $student_id;
        }

        // Test first pass (cache population)
        $start_time = microtime(true);
        $first_pass_queries = $DB->perf_get_queries();

        foreach ($students as $student_id) {
            $this->get_student_courses_via_reflection($student_id);
        }

        $first_pass_time = microtime(true) - $start_time;
        $first_pass_query_count = $DB->perf_get_queries() - $first_pass_queries;

        // Test second pass (cache hits)
        $start_time = microtime(true);
        $second_pass_queries = $DB->perf_get_queries();

        foreach ($students as $student_id) {
            $this->get_student_courses_via_reflection($student_id);
        }

        $second_pass_time = microtime(true) - $start_time;
        $second_pass_query_count = $DB->perf_get_queries() - $second_pass_queries;

        // Cache should significantly improve performance
        $this->assertLessThan(
            $first_pass_time * 0.5,
            $second_pass_time,
            'Cached lookups should be at least 50% faster'
        );

        $this->assertLessThan(
            $first_pass_query_count * 0.3,
            $second_pass_query_count,
            'Cached lookups should require significantly fewer queries'
        );
    }

    /**
     * Test memory usage with large datasets
     *
     * @covers \local_equipment memory efficiency
     */
    public function test_memory_usage_large_datasets(): void {
        $initial_memory = memory_get_usage();

        // Process increasingly large datasets
        $dataset_sizes = [100, 500, 1000];

        foreach ($dataset_sizes as $size) {
            $start_memory = memory_get_usage();

            // Create test data
            $test_data = vcc_test_data_generator::generate_large_dataset($size);

            // Process data (simulate what would happen in real usage)
            foreach ($test_data as $record) {
                $students_data = $this->service->get_students_display_data($record);
                $pickup_data = $this->service->get_pickup_display_data($record);

                // Simulate template rendering memory usage
                unset($students_data, $pickup_data);
            }

            $memory_used = memory_get_usage() - $start_memory;
            $memory_per_record = $memory_used / $size;

            $this->assertLessThan(
                1024, // 1KB per record
                $memory_per_record,
                "Memory usage per record should be reasonable for dataset size {$size}"
            );

            // Clean up to prevent memory accumulation between tests
            unset($test_data);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }

        $total_memory_increase = memory_get_usage() - $initial_memory;

        $this->assertLessThan(
            self::PERFORMANCE_THRESHOLDS['memory_usage'],
            $total_memory_increase,
            'Total memory increase should be within acceptable limits'
        );
    }

    /**
     * Test pagination performance with various page sizes
     *
     * @covers \local_equipment pagination performance
     */
    public function test_pagination_performance(): void {
        $this->setUser($this->admin_user);

        // Create large dataset for pagination testing
        $this->create_performance_test_data(1000);

        $page_sizes = [10, 25, 50, 100];
        $page_numbers = [1, 5, 10, 20];

        foreach ($page_sizes as $page_size) {
            foreach ($page_numbers as $page_number) {
                $start_time = microtime(true);

                try {
                    $result = get_table_data::execute($page_number, $page_size, [], sesskey());

                    $execution_time = microtime(true) - $start_time;

                    $this->assertLessThan(
                        self::PERFORMANCE_THRESHOLDS['ajax_response'],
                        $execution_time,
                        "Pagination (page {$page_number}, size {$page_size}) should be performant"
                    );

                    $this->assertIsArray($result);
                    $this->assertArrayHasKey('pagination', $result);

                } catch (\Exception $e) {
                    $this->fail("Pagination failed for page {$page_number}, size {$page_size}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Create performance test data
     *
     * @param int $count Number of records to create
     */
    private function create_performance_test_data(int $count): void {
        global $DB;

        $batch_size = 100;
        $batches = ceil($count / $batch_size);

        for ($batch = 0; $batch < $batches; $batch++) {
            $records = [];
            $current_batch_size = min($batch_size, $count - ($batch * $batch_size));

            for ($i = 0; $i < $current_batch_size; $i++) {
                $index = ($batch * $batch_size) + $i;

                $record = [
                    'userid' => $this->getDataGenerator()->create_user()->id,
                    'email' => "performance_test_{$index}@example.com",
                    'firstname' => "FirstName{$index}",
                    'lastname' => "LastName{$index}",
                    'phone' => "+1555" . sprintf('%07d', 1000000 + $index),
                    'partnership_name' => "Test Partnership " . ($index % 10),
                    'timecreated' => time() - rand(0, 86400 * 30),
                    'timemodified' => time()
                ];

                $records[] = $record;
            }

            // Batch insert for better performance
            $DB->insert_records('local_equipment_vccsubmission', $records);
        }
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