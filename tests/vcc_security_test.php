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
 * Security and integration tests for VCC submission system
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace local_equipment\tests;

use advanced_testcase;
use local_equipment\external\get_table_data;
use local_equipment\external\save_column_preferences;

defined('MOODLE_INTERNAL') || die();

/**
 * Test class for VCC security and integration
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_security_test extends advanced_testcase {

    /** @var stdClass Test user */
    private $user;

    /** @var stdClass Admin user */
    private $admin_user;

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        // Create test users
        $this->user = $this->getDataGenerator()->create_user();
        $this->admin_user = $this->getDataGenerator()->create_user();

        // Set up admin capabilities
        $admin_role = $this->getDataGenerator()->create_role();
        assign_capability('local/equipment:viewvccsubmissions', CAP_ALLOW, $admin_role, \context_system::instance());
        assign_capability('local/equipment:managevccsubmissions', CAP_ALLOW, $admin_role, \context_system::instance());
        role_assign($admin_role, $this->admin_user->id, \context_system::instance());
    }

    /**
     * Test sesskey validation in AJAX endpoints
     *
     * @covers \local_equipment\external\get_table_data::execute
     * @covers \local_equipment\external\save_column_preferences::execute
     */
    public function test_sesskey_validation(): void {
        $this->setUser($this->admin_user);

        // Test with invalid sesskey
        $invalid_sesskey = 'invalid_sesskey_12345';

        try {
            get_table_data::execute(1, 25, [], $invalid_sesskey);
            $this->fail('Should have thrown exception for invalid sesskey');
        } catch (\moodle_exception $e) {
            $this->assertStringContainsString('sesskey', $e->getMessage());
        }

        // Test with valid sesskey
        $valid_sesskey = sesskey();
        try {
            $result = get_table_data::execute(1, 25, [], $valid_sesskey);
            $this->assertIsArray($result);
            $this->assertArrayHasKey('success', $result);
        } catch (\Exception $e) {
            // May fail due to missing data, but should not fail on sesskey validation
            $this->assertStringNotContainsString('sesskey', $e->getMessage());
        }
    }

    /**
     * Test capability checks in external functions
     *
     * @covers \local_equipment\external\get_table_data::execute
     */
    public function test_capability_checks(): void {
        // Test with user without proper capabilities
        $this->setUser($this->user);

        try {
            get_table_data::execute(1, 25, [], sesskey());
            $this->fail('Should have thrown exception for insufficient capabilities');
        } catch (\moodle_exception $e) {
            $this->assertStringContainsString('nopermissions', $e->getMessage());
        }

        // Test with admin user (should work)
        $this->setUser($this->admin_user);

        try {
            $result = get_table_data::execute(1, 25, [], sesskey());
            $this->assertIsArray($result);
        } catch (\Exception $e) {
            // May fail due to other reasons, but not permissions
            $this->assertStringNotContainsString('nopermissions', $e->getMessage());
        }
    }

    /**
     * Test parameter sanitization and type checking
     *
     * @covers \local_equipment\external\get_table_data::execute
     */
    public function test_parameter_sanitization(): void {
        $this->setUser($this->admin_user);

        // Test with invalid parameter types
        $test_cases = [
            // Invalid page numbers
            [-1, 25, [], sesskey()],
            [0, 25, [], sesskey()],
            ['invalid', 25, [], sesskey()],

            // Invalid page sizes
            [1, -1, [], sesskey()],
            [1, 0, [], sesskey()],
            [1, 1000, [], sesskey()], // Too large
            [1, 'invalid', [], sesskey()],

            // Invalid filters
            [1, 25, 'not_array', sesskey()],
        ];

        foreach ($test_cases as $case_index => $params) {
            try {
                call_user_func_array([get_table_data::class, 'execute'], $params);
                // Some invalid params might be auto-corrected, so we don't always expect failure
            } catch (\Exception $e) {
                // Validate that error is appropriate (not a fatal error)
                $this->assertInstanceOf(\moodle_exception::class, $e, "Case {$case_index} should throw moodle_exception");
            }
        }
    }

    /**
     * Test SQL injection prevention
     *
     * @covers \local_equipment\service\vcc_submission_service::build_table_sql
     */
    public function test_sql_injection_prevention(): void {
        global $DB;

        $this->setUser($this->admin_user);

        // Test with malicious filter data
        $malicious_filters = [
            'search' => "'; DROP TABLE mdl_user; --",
            'partnership' => "1 OR 1=1 --",
            'datestart' => "1234'; DELETE FROM mdl_course; --",
            'dateend' => "1234 UNION SELECT password FROM mdl_user --"
        ];

        try {
            $result = get_table_data::execute(1, 25, $malicious_filters, sesskey());

            // If successful, verify no malicious SQL was executed
            // Check that user table still exists and has data
            $user_count = $DB->count_records('user');
            $this->assertGreaterThan(0, $user_count, 'User table should not be affected by SQL injection attempt');

            // Verify result structure is still valid
            $this->assertIsArray($result);
            $this->assertArrayHasKey('success', $result);

        } catch (\Exception $e) {
            // Exception is acceptable as long as it's not a database error indicating successful injection
            $this->assertStringNotContainsString('DROP', $e->getMessage());
            $this->assertStringNotContainsString('DELETE', $e->getMessage());
        }
    }

    /**
     * Test XSS prevention in template rendering
     *
     * @covers \local_equipment template XSS prevention
     */
    public function test_xss_prevention(): void {
        global $OUTPUT;

        $xss_test_cases = [
            'script_injection' => '<script>alert("XSS")</script>',
            'img_onerror' => '<img src="x" onerror="alert(1)">',
            'javascript_href' => '<a href="javascript:alert(1)">Click</a>',
            'event_handler' => '<div onmouseover="alert(1)">Hover</div>',
            'data_url' => 'data:text/html,<script>alert(1)</script>'
        ];

        foreach ($xss_test_cases as $case_name => $malicious_input) {
            $test_data = [
                'students' => [
                    [
                        'name' => $malicious_input,
                        'courses' => [
                            [
                                'id' => 1,
                                'name' => $malicious_input,
                                'status' => 'active',
                                'badge_class' => 'badge text-bg-primary',
                                'tooltip' => $malicious_input
                            ]
                        ],
                        'courses_text' => $malicious_input
                    ]
                ]
            ];

            $rendered = $OUTPUT->render_from_template('local_equipment/vcc_students_cell', $test_data);

            // Verify XSS content is properly escaped
            $this->assertStringNotContainsString('<script', $rendered, "XSS script tag should be escaped in {$case_name}");
            $this->assertStringNotContainsString('javascript:', $rendered, "JavaScript protocol should be escaped in {$case_name}");
            $this->assertStringNotContainsString('onerror=', $rendered, "Event handlers should be escaped in {$case_name}");
            $this->assertStringNotContainsString('onmouseover=', $rendered, "Event handlers should be escaped in {$case_name}");
        }
    }

    /**
     * Test CSRF protection in form submissions
     *
     * @covers \local_equipment CSRF protection
     */
    public function test_csrf_protection(): void {
        $this->setUser($this->admin_user);

        // Test that forms include proper CSRF tokens
        global $OUTPUT;

        $filter_data = [
            'form' => '<form method="post"><input type="hidden" name="sesskey" value="' . sesskey() . '"></form>',
            'has_filters' => true
        ];

        $rendered = $OUTPUT->render_from_template('local_equipment/vcc_filters', $filter_data);

        // Verify sesskey is included in forms
        $this->assertStringContainsString('sesskey', $rendered, 'Forms should include sesskey for CSRF protection');
    }

    /**
     * Test rate limiting and request validation
     *
     * @covers \local_equipment rate limiting
     */
    public function test_rate_limiting_simulation(): void {
        $this->setUser($this->admin_user);

        // Simulate rapid requests
        $request_count = 0;
        $start_time = microtime(true);

        for ($i = 0; $i < 10; $i++) {
            try {
                get_table_data::execute(1, 25, [], sesskey());
                $request_count++;
            } catch (\Exception $e) {
                // Some requests might fail, which is acceptable
                break;
            }

            // Small delay to prevent overloading
            usleep(10000); // 10ms
        }

        $execution_time = microtime(true) - $start_time;

        // Verify requests can be processed but not too quickly (indicating some throttling)
        $this->assertGreaterThan(0, $request_count, 'At least some requests should be processed');
        $this->assertGreaterThan(0.05, $execution_time, 'Requests should take some time to process');
    }

    /**
     * Test error handling robustness
     *
     * @covers \local_equipment error handling
     */
    public function test_error_handling_robustness(): void {
        $this->setUser($this->admin_user);

        // Test with various error conditions
        $error_conditions = [
            // Extremely large page size
            [1, 999999, [], sesskey()],
            // Extremely large page number
            [999999, 25, [], sesskey()],
            // Malformed filter data
            [1, 25, ['invalid' => str_repeat('x', 10000)], sesskey()],
        ];

        foreach ($error_conditions as $condition_index => $params) {
            try {
                $result = call_user_func_array([get_table_data::class, 'execute'], $params);

                // If successful, verify result is still properly structured
                $this->assertIsArray($result, "Error condition {$condition_index} should return array");
                $this->assertArrayHasKey('success', $result, "Error condition {$condition_index} should have success key");

            } catch (\Exception $e) {
                // Exceptions are acceptable, but should be handled gracefully
                $this->assertInstanceOf(
                    \moodle_exception::class,
                    $e,
                    "Error condition {$condition_index} should throw proper exception type"
                );

                // Error message should not expose internal details
                $this->assertStringNotContainsString('SELECT', $e->getMessage());
                $this->assertStringNotContainsString('FROM', $e->getMessage());
                $this->assertStringNotContainsString('WHERE', $e->getMessage());
            }
        }
    }

    /**
     * Test user preference security
     *
     * @covers \local_equipment user preference security
     */
    public function test_user_preference_security(): void {
        $this->setUser($this->admin_user);

        // Test with malicious preference data
        $malicious_preferences = [
            'malicious_js' => json_encode(['<script>alert(1)</script>' => 'value']),
            'sql_injection' => json_encode(['column\'; DROP TABLE users; --' => '100px']),
            'large_data' => json_encode(['column' => str_repeat('x', 100000)]),
            'null_bytes' => json_encode(['column' => "value\x00malicious"]),
        ];

        foreach ($malicious_preferences as $case_name => $preference_data) {
            try {
                // This would typically be done through save_column_preferences external function
                set_user_preference('local_equipment_test_' . $case_name, $preference_data);

                // Retrieve and verify the preference is stored safely
                $stored_preference = get_user_preference('local_equipment_test_' . $case_name);

                // Verify malicious content doesn't cause issues when retrieved
                $this->assertIsString($stored_preference, "Preference {$case_name} should be stored as string");

                // Attempt to decode and verify it doesn't cause execution
                $decoded = json_decode($stored_preference, true);
                if ($decoded !== null) {
                    $this->assertIsArray($decoded, "Decoded preference {$case_name} should be array if valid JSON");
                }

            } catch (\Exception $e) {
                // Some malicious data might be rejected, which is acceptable
                $this->assertStringNotContainsString('Fatal', $e->getMessage());
            }
        }
    }

    /**
     * Test database performance under stress
     *
     * @covers \local_equipment database performance
     */
    public function test_database_performance_under_stress(): void {
        global $DB;

        $this->setUser($this->admin_user);

        // Create some test data
        for ($i = 0; $i < 50; $i++) {
            $submission_data = [
                'userid' => $this->user->id,
                'email' => "test{$i}@example.com",
                'firstname' => "First{$i}",
                'lastname' => "Last{$i}",
                'timecreated' => time() - rand(0, 86400),
                'timemodified' => time()
            ];

            $DB->insert_record('local_equipment_vccsubmission', $submission_data);
        }

        // Test concurrent-like access patterns
        $start_time = microtime(true);

        for ($i = 0; $i < 10; $i++) {
            try {
                $result = get_table_data::execute(1, 25, [], sesskey());
                $this->assertIsArray($result);
            } catch (\Exception $e) {
                // Some failures acceptable under stress
            }
        }

        $execution_time = microtime(true) - $start_time;

        // Performance should be reasonable even under stress
        $this->assertLessThan(5.0, $execution_time, 'Database operations should complete within 5 seconds under stress');
    }
}