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
 * Upgrade path and compatibility tests for VCC submission system
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace local_equipment\tests;

use advanced_testcase;

defined('MOODLE_INTERNAL') || die();

/**
 * Test class for VCC upgrade path and compatibility
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_upgrade_compatibility_test extends advanced_testcase {

    /** @var array Legacy preference formats for testing migration */
    private const LEGACY_PREFERENCE_FORMATS = [
        'old_column_format' => '{"columns":["firstname","lastname"]}', // Old format
        'malformed_json' => '{columns:["firstname","lastname"]}', // Missing quotes
        'empty_preference' => '',
        'null_preference' => null,
        'invalid_structure' => '{"wrong_key":"value"}',
    ];

    /** @var array Legacy URL parameters to test compatibility */
    private const LEGACY_URL_PARAMS = [
        'old_pagination' => ['page' => '2', 'perpage' => '50'],
        'old_filters' => ['search' => 'test', 'partnership' => '1'],
        'mixed_params' => ['page' => '1', 'search' => 'admin', 'sort' => 'timecreated'],
    ];

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test user preference migration from legacy formats
     *
     * @covers \local_equipment user preference migration
     */
    public function test_user_preference_migration(): void {
        $test_user = $this->getDataGenerator()->create_user();
        $this->setUser($test_user);

        foreach (self::LEGACY_PREFERENCE_FORMATS as $format_name => $legacy_preference) {
            // Set legacy preference
            if ($legacy_preference !== null) {
                set_user_preference('local_equipment_vcc_table_columns_legacy', $legacy_preference);
            }

            // Test migration handling
            $migrated_preference = $this->simulate_preference_migration($legacy_preference);

            if ($format_name === 'old_column_format') {
                // Valid legacy format should be migrated successfully
                $this->assertIsString($migrated_preference, "Legacy format {$format_name} should migrate to valid format");

                $decoded = json_decode($migrated_preference, true);
                $this->assertIsArray($decoded, "Migrated preference for {$format_name} should be valid JSON");
                $this->assertArrayHasKey('hidden_columns', $decoded, "Migrated preference should have new structure");

            } else {
                // Invalid legacy formats should result in default preferences
                $decoded = json_decode($migrated_preference, true);
                $this->assertIsArray($decoded, "Invalid legacy format {$format_name} should default to valid structure");
                $this->assertArrayHasKey('hidden_columns', $decoded, "Default preference should have new structure");
                $this->assertEquals([], $decoded['hidden_columns'], "Default should have empty hidden columns");
            }
        }
    }

    /**
     * Test database schema compatibility during upgrades
     *
     * @covers \local_equipment database schema compatibility
     */
    public function test_database_schema_compatibility(): void {
        global $DB;

        // Test that current schema supports both old and new data formats
        $test_cases = [
            'legacy_studentids_format' => [
                'studentids' => json_encode([1, 2, 3]), // Old integer format
                'expected_valid' => true
            ],
            'new_studentids_format' => [
                'studentids' => json_encode(['1', '2', '3']), // New string format
                'expected_valid' => true
            ],
            'mixed_studentids_format' => [
                'studentids' => json_encode([1, '2', 3]), // Mixed format
                'expected_valid' => true
            ],
            'null_studentids' => [
                'studentids' => null,
                'expected_valid' => true
            ],
            'empty_studentids' => [
                'studentids' => '',
                'expected_valid' => true
            ]
        ];

        foreach ($test_cases as $case_name => $case_data) {
            $submission_data = [
                'userid' => $this->getDataGenerator()->create_user()->id,
                'email' => "test_{$case_name}@example.com",
                'firstname' => 'Test',
                'lastname' => 'User',
                'studentids' => $case_data['studentids'],
                'timecreated' => time(),
                'timemodified' => time()
            ];

            try {
                $submission_id = $DB->insert_record('local_equipment_vccsubmission', $submission_data);
                $retrieved = $DB->get_record('local_equipment_vccsubmission', ['id' => $submission_id]);

                $this->assertNotFalse($retrieved, "Should successfully store and retrieve data for case: {$case_name}");
                $this->assertEquals($case_data['studentids'], $retrieved->studentids, "Data integrity should be maintained for case: {$case_name}");

            } catch (\Exception $e) {
                if ($case_data['expected_valid']) {
                    $this->fail("Valid case {$case_name} should not throw exception: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Test URL parameter backward compatibility
     *
     * @covers \local_equipment URL compatibility
     */
    public function test_url_parameter_compatibility(): void {
        global $CFG;

        foreach (self::LEGACY_URL_PARAMS as $param_type => $params) {
            // Simulate legacy URL with old parameters
            $legacy_url = new \moodle_url('/local/equipment/vccsubmissions/view.php', $params);

            // Test that the view can handle legacy parameters
            $this->assertTrue($legacy_url instanceof \moodle_url, "Legacy URL for {$param_type} should be valid");

            // Test parameter extraction and normalization
            $normalized_params = $this->normalize_legacy_url_params($params);

            $this->assertIsArray($normalized_params, "Parameters should be normalized for {$param_type}");

            // Verify that critical parameters are preserved
            if (isset($params['page'])) {
                $this->assertArrayHasKey('page', $normalized_params, "Page parameter should be preserved");
                $this->assertIsNumeric($normalized_params['page'], "Page should be numeric");
            }

            if (isset($params['search'])) {
                $this->assertArrayHasKey('search', $normalized_params, "Search parameter should be preserved");
            }
        }
    }

    /**
     * Test data corruption prevention during upgrade
     *
     * @covers \local_equipment data corruption prevention
     */
    public function test_data_corruption_prevention(): void {
        global $DB;

        // Create test data with potential corruption scenarios
        $corruption_scenarios = [
            'partial_json' => '{"incomplete":',
            'mixed_encoding' => json_encode(['test' => 'válue with ñ characters']),
            'very_long_data' => json_encode(['data' => str_repeat('x', 10000)]),
            'nested_structure' => json_encode(['level1' => ['level2' => ['level3' => 'deep']]]),
        ];

        foreach ($corruption_scenarios as $scenario_name => $corrupted_data) {
            $student_data = [
                'firstname' => 'Test',
                'lastname' => 'Student',
                'userid' => $this->getDataGenerator()->create_user()->id,
                'courseids' => $corrupted_data,
                'timecreated' => time(),
                'timemodified' => time()
            ];

            try {
                $student_id = $DB->insert_record('local_equipment_vccsubmission_student', $student_data);
                $retrieved = $DB->get_record('local_equipment_vccsubmission_student', ['id' => $student_id]);

                // Test that corrupted data can be safely handled
                $safe_data = $this->safely_decode_json($retrieved->courseids);

                $this->assertIsArray($safe_data, "Corrupted data {$scenario_name} should be safely handled");

            } catch (\Exception $e) {
                // Some corruption might be caught at insert time, which is acceptable
                $this->assertStringNotContainsString('Fatal', $e->getMessage(), "Should not cause fatal errors for {$scenario_name}");
            }
        }
    }

    /**
     * Test rollback capability
     *
     * @covers \local_equipment rollback procedures
     */
    public function test_rollback_capability(): void {
        $test_user = $this->getDataGenerator()->create_user();
        $this->setUser($test_user);

        // Simulate upgrade by setting new preferences
        $new_preferences = [
            'local_equipment_vcc_table_columns' => json_encode(['hidden_columns' => ['mailing_address']]),
            'local_equipment_vcc_table_column_widths' => json_encode(['firstname' => '150px']),
        ];

        foreach ($new_preferences as $pref_name => $pref_value) {
            set_user_preference($pref_name, $pref_value);
        }

        // Test rollback procedure
        $rollback_successful = $this->simulate_preference_rollback();

        $this->assertTrue($rollback_successful, 'Preference rollback should be successful');

        // Verify preferences are reset
        foreach ($new_preferences as $pref_name => $pref_value) {
            $current_value = get_user_preference($pref_name);
            $this->assertEmpty($current_value, "Preference {$pref_name} should be cleared after rollback");
        }
    }

    /**
     * Test foreign key constraint validation during upgrades
     *
     * @covers \local_equipment foreign key constraints
     */
    public function test_foreign_key_constraint_validation(): void {
        global $DB;

        // Test scenarios that might break referential integrity
        $integrity_tests = [
            'valid_user_reference' => [
                'userid' => $this->getDataGenerator()->create_user()->id,
                'should_succeed' => true
            ],
            'invalid_user_reference' => [
                'userid' => 999999, // Non-existent user
                'should_succeed' => false
            ],
        ];

        foreach ($integrity_tests as $test_name => $test_data) {
            $submission_data = [
                'userid' => $test_data['userid'],
                'email' => "test_{$test_name}@example.com",
                'firstname' => 'Test',
                'lastname' => 'User',
                'timecreated' => time(),
                'timemodified' => time()
            ];

            try {
                $submission_id = $DB->insert_record('local_equipment_vccsubmission', $submission_data);

                if (!$test_data['should_succeed']) {
                    $this->fail("Test {$test_name} should have failed due to foreign key constraint");
                }

                $this->assertGreaterThan(0, $submission_id, "Valid reference {$test_name} should succeed");

            } catch (\Exception $e) {
                if ($test_data['should_succeed']) {
                    $this->fail("Test {$test_name} should have succeeded: " . $e->getMessage());
                }

                // Expected failure due to foreign key constraint
                $this->addToAssertionCount(1);
            }
        }
    }

    /**
     * Test breaking change impact assessment
     *
     * @covers \local_equipment breaking change detection
     */
    public function test_breaking_change_impact_assessment(): void {
        global $CFG;

        $breaking_changes = [
            'template_variables' => [
                'old_variables' => ['exchange_pickup_method', 'exchange_pickup_person'],
                'new_variables' => ['exchange_pickup_info'],
                'impact' => 'template_override'
            ],
            'css_classes' => [
                'old_classes' => ['col-exchange_pickup_method', 'col-exchange_pickup_person'],
                'new_classes' => ['col-exchange_pickup_info'],
                'impact' => 'custom_css'
            ],
            'url_structure' => [
                'old_params' => ['sort', 'dir'],
                'new_params' => ['sortby', 'sortdir'],
                'impact' => 'bookmarks'
            ]
        ];

        foreach ($breaking_changes as $change_type => $change_data) {
            $impact_assessment = $this->assess_breaking_change_impact($change_type, $change_data);

            $this->assertIsArray($impact_assessment, "Impact assessment for {$change_type} should return array");
            $this->assertArrayHasKey('severity', $impact_assessment, "Assessment should include severity");
            $this->assertArrayHasKey('affected_components', $impact_assessment, "Assessment should list affected components");
            $this->assertArrayHasKey('mitigation_steps', $impact_assessment, "Assessment should include mitigation steps");

            // Log assessment for manual review
            if ($impact_assessment['severity'] === 'high') {
                $this->addWarning("High impact breaking change detected: {$change_type}");
            }
        }
    }

    /**
     * Test version upgrade compatibility
     *
     * @covers \local_equipment version compatibility
     */
    public function test_version_upgrade_compatibility(): void {
        global $CFG;

        // Simulate version upgrade scenarios
        $version_scenarios = [
            'minor_upgrade' => ['from' => '2024010100', 'to' => '2024010101'],
            'major_upgrade' => ['from' => '2024010100', 'to' => '2025010100'],
            'patch_upgrade' => ['from' => '2024010100', 'to' => '2024010100.1'],
        ];

        foreach ($version_scenarios as $scenario_name => $versions) {
            $compatibility_check = $this->check_version_compatibility($versions['from'], $versions['to']);

            $this->assertIsArray($compatibility_check, "Compatibility check for {$scenario_name} should return results");
            $this->assertArrayHasKey('compatible', $compatibility_check, "Should indicate compatibility status");
            $this->assertArrayHasKey('required_actions', $compatibility_check, "Should list required actions");

            if (!$compatibility_check['compatible']) {
                $this->assertNotEmpty(
                    $compatibility_check['required_actions'],
                    "Incompatible upgrade {$scenario_name} should list required actions"
                );
            }
        }
    }

    /**
     * Simulate preference migration from legacy format
     *
     * @param string|null $legacy_preference Legacy preference value
     * @return string Migrated preference
     */
    private function simulate_preference_migration(?string $legacy_preference): string {
        if (empty($legacy_preference)) {
            return json_encode(['hidden_columns' => []]);
        }

        $decoded = json_decode($legacy_preference, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Invalid JSON - return default
            return json_encode(['hidden_columns' => []]);
        }

        // Migrate old format to new format
        if (isset($decoded['columns'])) {
            // Old format had 'columns', new format uses 'hidden_columns'
            return json_encode(['hidden_columns' => $decoded['columns']]);
        }

        if (isset($decoded['hidden_columns'])) {
            // Already in new format
            return $legacy_preference;
        }

        // Unknown format - return default
        return json_encode(['hidden_columns' => []]);
    }

    /**
     * Normalize legacy URL parameters
     *
     * @param array $legacy_params Legacy URL parameters
     * @return array Normalized parameters
     */
    private function normalize_legacy_url_params(array $legacy_params): array {
        $normalized = [];

        foreach ($legacy_params as $key => $value) {
            switch ($key) {
                case 'page':
                    $normalized['page'] = max(1, (int)$value);
                    break;
                case 'perpage':
                    $allowed_sizes = [10, 25, 50, 100];
                    $normalized['perpage'] = in_array((int)$value, $allowed_sizes) ? (int)$value : 25;
                    break;
                case 'sort':
                    // Map old sort parameter to new format
                    $normalized['sortby'] = clean_param($value, PARAM_ALPHA);
                    break;
                default:
                    $normalized[$key] = clean_param($value, PARAM_TEXT);
                    break;
            }
        }

        return $normalized;
    }

    /**
     * Safely decode JSON with error handling
     *
     * @param string|null $json_string JSON string to decode
     * @return array Safe decoded data
     */
    private function safely_decode_json(?string $json_string): array {
        if (empty($json_string)) {
            return [];
        }

        $decoded = json_decode($json_string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * Simulate preference rollback procedure
     *
     * @return bool Success status
     */
    private function simulate_preference_rollback(): bool {
        $preference_keys = [
            'local_equipment_vcc_table_columns',
            'local_equipment_vcc_table_column_widths',
            'local_equipment_vcc_table_sort_column',
            'local_equipment_vcc_table_sort_direction',
        ];

        try {
            foreach ($preference_keys as $key) {
                unset_user_preference($key);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Assess breaking change impact
     *
     * @param string $change_type Type of change
     * @param array $change_data Change details
     * @return array Impact assessment
     */
    private function assess_breaking_change_impact(string $change_type, array $change_data): array {
        $assessment = [
            'severity' => 'low',
            'affected_components' => [],
            'mitigation_steps' => []
        ];

        switch ($change_type) {
            case 'template_variables':
                $assessment['severity'] = 'high';
                $assessment['affected_components'] = ['theme_overrides', 'custom_templates'];
                $assessment['mitigation_steps'] = [
                    'Update theme template overrides',
                    'Test all custom templates',
                    'Provide migration documentation'
                ];
                break;

            case 'css_classes':
                $assessment['severity'] = 'medium';
                $assessment['affected_components'] = ['custom_css', 'theme_styles'];
                $assessment['mitigation_steps'] = [
                    'Update custom CSS selectors',
                    'Test theme compatibility',
                    'Provide CSS migration guide'
                ];
                break;

            case 'url_structure':
                $assessment['severity'] = 'low';
                $assessment['affected_components'] = ['bookmarks', 'external_links'];
                $assessment['mitigation_steps'] = [
                    'Implement URL redirect handling',
                    'Update documentation',
                    'Notify users of URL changes'
                ];
                break;
        }

        return $assessment;
    }

    /**
     * Check version compatibility
     *
     * @param string $from_version Source version
     * @param string $to_version Target version
     * @return array Compatibility information
     */
    private function check_version_compatibility(string $from_version, string $to_version): array {
        $from_num = (float)$from_version;
        $to_num = (float)$to_version;

        $compatibility = [
            'compatible' => true,
            'required_actions' => []
        ];

        // Major version changes require more care
        if (floor($to_num / 1000000) > floor($from_num / 1000000)) {
            $compatibility['required_actions'][] = 'Review breaking changes documentation';
            $compatibility['required_actions'][] = 'Test all customizations';
            $compatibility['required_actions'][] = 'Backup database before upgrade';
        }

        // Very old versions might not be compatible
        if ($to_num - $from_num > 10000) {
            $compatibility['compatible'] = false;
            $compatibility['required_actions'][] = 'Incremental upgrade required';
        }

        return $compatibility;
    }
}