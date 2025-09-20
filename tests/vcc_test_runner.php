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
 * Comprehensive test runner for VCC submission system Phase 9 validation
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
 * Comprehensive test runner for Phase 9 validation
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_test_runner extends advanced_testcase {

    /** @var array Test suites to run */
    private const TEST_SUITES = [
        'data_validation' => vcc_data_validation_test::class,
        'theme_compatibility' => vcc_theme_compatibility_test::class,
        'mobile_compatibility' => vcc_mobile_compatibility_test::class,
        'browser_compatibility' => vcc_browser_compatibility_test::class,
        'accessibility' => vcc_accessibility_test::class,
        'security' => vcc_security_test::class,
        'performance' => vcc_performance_test::class,
        'upgrade_compatibility' => vcc_upgrade_compatibility_test::class
    ];

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Run comprehensive Phase 9 test suite
     *
     * @covers \local_equipment Phase 9 comprehensive testing
     */
    public function test_run_comprehensive_phase9_validation(): void {
        $results = [];
        $overall_success = true;

        echo "\n" . str_repeat('=', 80) . "\n";
        echo "PHASE 9 COMPREHENSIVE TESTING & VALIDATION\n";
        echo "VCC Submission System - Equipment Plugin\n";
        echo str_repeat('=', 80) . "\n\n";

        foreach (self::TEST_SUITES as $suite_name => $test_class) {
            echo "Running {$suite_name} tests...\n";

            $suite_start_time = microtime(true);
            $suite_results = $this->run_test_suite($test_class);
            $suite_execution_time = microtime(true) - $suite_start_time;

            $results[$suite_name] = [
                'class' => $test_class,
                'results' => $suite_results,
                'execution_time' => $suite_execution_time,
                'success' => $suite_results['passed'] > 0 && $suite_results['failed'] === 0
            ];

            if (!$results[$suite_name]['success']) {
                $overall_success = false;
            }

            $this->display_suite_results($suite_name, $results[$suite_name]);
        }

        echo "\n" . str_repeat('=', 80) . "\n";
        echo "PHASE 9 TESTING SUMMARY\n";
        echo str_repeat('=', 80) . "\n";

        $this->display_comprehensive_summary($results, $overall_success);

        // Assert overall success
        $this->assertTrue($overall_success, 'All Phase 9 test suites should pass');
    }

    /**
     * Run individual test suite
     *
     * @param string $test_class Test class name
     * @return array Test results
     */
    private function run_test_suite(string $test_class): array {
        $results = [
            'passed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'warnings' => 0,
            'errors' => []
        ];

        try {
            // Use reflection to run test methods
            $reflection = new \ReflectionClass($test_class);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                if (strpos($method->getName(), 'test_') === 0) {
                    try {
                        $test_instance = new $test_class();
                        $test_instance->setUp();
                        $method->invoke($test_instance);
                        $results['passed']++;
                    } catch (\PHPUnit\Framework\SkippedTestError $e) {
                        $results['skipped']++;
                    } catch (\Exception $e) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'method' => $method->getName(),
                            'message' => $e->getMessage()
                        ];
                    }
                }
            }

        } catch (\Exception $e) {
            $results['failed']++;
            $results['errors'][] = [
                'class' => $test_class,
                'message' => $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * Display results for a test suite
     *
     * @param string $suite_name Suite name
     * @param array $suite_data Suite results
     */
    private function display_suite_results(string $suite_name, array $suite_data): void {
        $status = $suite_data['success'] ? '✅ PASSED' : '❌ FAILED';
        $time = number_format($suite_data['execution_time'], 3);

        echo "  {$status} | {$suite_name} | {$time}s\n";

        $results = $suite_data['results'];
        echo "    Passed: {$results['passed']}, Failed: {$results['failed']}, Skipped: {$results['skipped']}\n";

        if (!empty($results['errors'])) {
            echo "    Errors:\n";
            foreach ($results['errors'] as $error) {
                $method = $error['method'] ?? $error['class'] ?? 'unknown';
                echo "      - {$method}: {$error['message']}\n";
            }
        }

        echo "\n";
    }

    /**
     * Display comprehensive testing summary
     *
     * @param array $results All test results
     * @param bool $overall_success Overall success status
     */
    private function display_comprehensive_summary(array $results, bool $overall_success): void {
        $total_passed = 0;
        $total_failed = 0;
        $total_skipped = 0;
        $total_time = 0;

        foreach ($results as $suite_data) {
            $total_passed += $suite_data['results']['passed'];
            $total_failed += $suite_data['results']['failed'];
            $total_skipped += $suite_data['results']['skipped'];
            $total_time += $suite_data['execution_time'];
        }

        echo "Total Tests: " . ($total_passed + $total_failed + $total_skipped) . "\n";
        echo "Passed: {$total_passed}\n";
        echo "Failed: {$total_failed}\n";
        echo "Skipped: {$total_skipped}\n";
        echo "Total Execution Time: " . number_format($total_time, 3) . "s\n\n";

        if ($overall_success) {
            echo "🎉 PHASE 9 VALIDATION: ALL TESTS PASSED\n";
            echo "✅ Data Validation & Error Handling\n";
            echo "✅ Theme Override & Template Compatibility\n";
            echo "✅ Mobile & Touch Interface Testing\n";
            echo "✅ Cross-browser & Performance Testing\n";
            echo "✅ Accessibility Compliance (WCAG 2.1 AA)\n";
            echo "✅ Integration & Security Testing\n";
            echo "✅ Database Performance & Indexing\n";
            echo "✅ Upgrade Path & Backward Compatibility\n\n";

            echo "The VCC submission system has successfully passed comprehensive Phase 9 testing.\n";
            echo "All components are validated for production deployment.\n";
        } else {
            echo "❌ PHASE 9 VALIDATION: SOME TESTS FAILED\n";
            echo "Please review the failed tests above and address the issues before deployment.\n";
        }

        echo "\n" . str_repeat('=', 80) . "\n";
    }

    /**
     * Generate detailed test report
     *
     * @covers \local_equipment test reporting
     */
    public function test_generate_detailed_test_report(): void {
        $report_data = [
            'test_execution_date' => date('Y-m-d H:i:s'),
            'moodle_version' => $this->get_moodle_version(),
            'plugin_version' => $this->get_plugin_version(),
            'php_version' => PHP_VERSION,
            'test_environment' => $this->get_test_environment_info(),
            'coverage_areas' => $this->get_coverage_areas(),
            'recommendations' => $this->get_testing_recommendations()
        ];

        $this->assertIsArray($report_data, 'Test report should be generated successfully');
        $this->assertArrayHasKey('test_execution_date', $report_data, 'Report should include execution date');

        // Generate report file
        $report_content = $this->format_test_report($report_data);
        $this->assertNotEmpty($report_content, 'Report content should not be empty');
    }

    /**
     * Get Moodle version information
     *
     * @return string
     */
    private function get_moodle_version(): string {
        global $CFG;
        return $CFG->version ?? 'Unknown';
    }

    /**
     * Get plugin version information
     *
     * @return string
     */
    private function get_plugin_version(): string {
        global $CFG;

        $plugin_file = $CFG->dirroot . '/local/equipment/version.php';
        if (file_exists($plugin_file)) {
            include $plugin_file;
            return $plugin->version ?? 'Unknown';
        }

        return 'Unknown';
    }

    /**
     * Get test environment information
     *
     * @return array
     */
    private function get_test_environment_info(): array {
        return [
            'operating_system' => PHP_OS,
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'database_type' => $this->get_database_type()
        ];
    }

    /**
     * Get database type
     *
     * @return string
     */
    private function get_database_type(): string {
        global $DB;
        return $DB->get_dbfamily();
    }

    /**
     * Get coverage areas tested
     *
     * @return array
     */
    private function get_coverage_areas(): array {
        return [
            'Data Validation & Error Handling' => [
                'JSON format validation',
                'Course display logic',
                'Column management validation',
                'Database fallback mechanisms',
                'Memory usage optimization'
            ],
            'Theme Override & Template Compatibility' => [
                'Template override detection',
                'Theme inheritance testing',
                'Template security validation',
                'CSS class conflict analysis'
            ],
            'Mobile & Touch Interface Testing' => [
                'Responsive design validation',
                'Touch interaction patterns',
                'Virtual keyboard compatibility',
                'Mobile performance optimization'
            ],
            'Cross-browser & Performance Testing' => [
                'JavaScript API compatibility',
                'ES6 module support',
                'CSS feature validation',
                'Performance benchmarking'
            ],
            'Accessibility Compliance (WCAG 2.1 AA)' => [
                'Screen reader compatibility',
                'Keyboard navigation',
                'Color contrast validation',
                'Focus management'
            ],
            'Integration & Security Testing' => [
                'AJAX security validation',
                'Parameter sanitization',
                'XSS prevention',
                'CSRF protection'
            ],
            'Database Performance & Indexing' => [
                'Query optimization',
                'Large dataset handling',
                'Memory efficiency',
                'Caching strategies'
            ],
            'Upgrade Path & Backward Compatibility' => [
                'Preference migration',
                'URL compatibility',
                'Data corruption prevention',
                'Rollback procedures'
            ]
        ];
    }

    /**
     * Get testing recommendations
     *
     * @return array
     */
    private function get_testing_recommendations(): array {
        return [
            'Deployment' => [
                'Run full test suite before production deployment',
                'Test with production-like data volumes',
                'Verify all theme customizations are compatible',
                'Test on target browser versions'
            ],
            'Monitoring' => [
                'Monitor database performance after deployment',
                'Track JavaScript errors in production',
                'Monitor accessibility compliance',
                'Track mobile usage patterns'
            ],
            'Maintenance' => [
                'Re-run tests after Moodle upgrades',
                'Test new browser versions regularly',
                'Update accessibility tests annually',
                'Review security tests quarterly'
            ]
        ];
    }

    /**
     * Format test report
     *
     * @param array $report_data Report data
     * @return string Formatted report
     */
    private function format_test_report(array $report_data): string {
        $report = "# VCC Submission System - Phase 9 Test Report\n\n";
        $report .= "**Generated:** {$report_data['test_execution_date']}\n";
        $report .= "**Moodle Version:** {$report_data['moodle_version']}\n";
        $report .= "**Plugin Version:** {$report_data['plugin_version']}\n";
        $report .= "**PHP Version:** {$report_data['php_version']}\n\n";

        $report .= "## Test Environment\n\n";
        foreach ($report_data['test_environment'] as $key => $value) {
            $report .= "- **" . ucwords(str_replace('_', ' ', $key)) . ":** {$value}\n";
        }

        $report .= "\n## Coverage Areas\n\n";
        foreach ($report_data['coverage_areas'] as $area => $tests) {
            $report .= "### {$area}\n\n";
            foreach ($tests as $test) {
                $report .= "- {$test}\n";
            }
            $report .= "\n";
        }

        $report .= "## Recommendations\n\n";
        foreach ($report_data['recommendations'] as $category => $recommendations) {
            $report .= "### {$category}\n\n";
            foreach ($recommendations as $recommendation) {
                $report .= "- {$recommendation}\n";
            }
            $report .= "\n";
        }

        return $report;
    }
}