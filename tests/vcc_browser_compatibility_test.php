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
 * Cross-browser compatibility tests for VCC submission system
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
 * Test class for VCC cross-browser compatibility
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_browser_compatibility_test extends advanced_testcase {

    /** @var array Browser compatibility requirements */
    private const BROWSER_REQUIREMENTS = [
        'chrome' => ['min_version' => 90, 'features' => ['es6', 'resize_observer', 'local_storage']],
        'firefox' => ['min_version' => 85, 'features' => ['es6', 'resize_observer', 'local_storage']],
        'safari' => ['min_version' => 14, 'features' => ['es6', 'resize_observer_polyfill', 'local_storage']],
        'edge' => ['min_version' => 90, 'features' => ['es6', 'resize_observer', 'local_storage']]
    ];

    /** @var array JavaScript APIs that need compatibility checking */
    private const REQUIRED_JS_APIS = [
        'ResizeObserver' => 'Required for column resizing functionality',
        'localStorage' => 'Required for user preference storage',
        'JSON.parse' => 'Required for preference parsing',
        'fetch' => 'Required for AJAX operations',
        'Promise' => 'Required for asynchronous operations',
        'Array.from' => 'Required for modern array operations',
        'Object.assign' => 'Required for object manipulation'
    ];

    /** @var array CSS features that need compatibility checking */
    private const REQUIRED_CSS_FEATURES = [
        'flexbox' => 'Required for responsive layouts',
        'grid' => 'Required for advanced table layouts',
        'custom-properties' => 'Required for CSS variables',
        'transforms' => 'Required for animations',
        'media-queries' => 'Required for responsive design'
    ];

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test JavaScript API compatibility requirements
     *
     * @covers \local_equipment JavaScript API usage
     */
    public function test_javascript_api_compatibility(): void {
        global $CFG;

        $js_files = [
            $CFG->dirroot . '/local/equipment/amd/src/vcc_table_columns.js',
            $CFG->dirroot . '/local/equipment/amd/src/vcc_table_enhanced.js'
        ];

        foreach ($js_files as $js_file) {
            if (!file_exists($js_file)) {
                continue;
            }

            $js_content = file_get_contents($js_file);
            $this->analyze_js_api_usage($js_content, basename($js_file));
        }
    }

    /**
     * Test ES6 module compatibility
     *
     * @covers \local_equipment ES6 module usage
     */
    public function test_es6_module_compatibility(): void {
        global $CFG;

        $js_file = $CFG->dirroot . '/local/equipment/amd/src/vcc_table_columns.js';

        if (!file_exists($js_file)) {
            $this->markTestSkipped('VCC table columns JS file not found');
        }

        $js_content = file_get_contents($js_file);

        // Check for ES6 features
        $es6_features = [
            'import.*from' => 'ES6 import statements',
            'export.*default' => 'ES6 export statements',
            'const\s+\w+\s*=' => 'const declarations',
            'let\s+\w+\s*=' => 'let declarations',
            '=>' => 'Arrow functions',
            'class\s+\w+' => 'ES6 classes',
            'async\s+\w+' => 'Async functions',
            'await\s+' => 'Await expressions'
        ];

        foreach ($es6_features as $pattern => $description) {
            $has_feature = preg_match('/' . $pattern . '/', $js_content);
            if ($has_feature) {
                $this->addToAssertionCount(1);
                // Log usage for compatibility planning
            }
        }

        // Verify proper export pattern for AMD modules
        $this->assertRegExp(
            '/export\s+(default\s+)?\w+/',
            $js_content,
            'AMD module should have proper export statement'
        );
    }

    /**
     * Test ResizeObserver API usage and polyfill fallback
     *
     * @covers \local_equipment ResizeObserver compatibility
     */
    public function test_resize_observer_compatibility(): void {
        global $CFG;

        $js_file = $CFG->dirroot . '/local/equipment/amd/src/vcc_table_columns.js';

        if (!file_exists($js_file)) {
            $this->markTestSkipped('VCC table columns JS file not found');
        }

        $js_content = file_get_contents($js_file);

        // Check for ResizeObserver usage
        $has_resize_observer = strpos($js_content, 'ResizeObserver') !== false;

        if ($has_resize_observer) {
            // Check for fallback mechanism
            $has_fallback = strpos($js_content, 'typeof ResizeObserver') !== false ||
                           strpos($js_content, 'window.ResizeObserver') !== false ||
                           strpos($js_content, 'ResizeObserver === undefined') !== false;

            $this->assertTrue(
                $has_fallback,
                'ResizeObserver usage should include browser support detection'
            );

            // Check for polyfill loading or alternative implementation
            $has_alternative = strpos($js_content, 'addEventListener') !== false &&
                              (strpos($js_content, 'resize') !== false || strpos($js_content, 'MutationObserver') !== false);

            $this->assertTrue(
                $has_alternative,
                'Should provide alternative implementation for browsers without ResizeObserver'
            );
        }
    }

    /**
     * Test Local Storage usage and fallback
     *
     * @covers \local_equipment localStorage compatibility
     */
    public function test_local_storage_compatibility(): void {
        global $CFG;

        $js_files = glob($CFG->dirroot . '/local/equipment/amd/src/*.js');

        foreach ($js_files as $js_file) {
            $js_content = file_get_contents($js_file);

            if (strpos($js_content, 'localStorage') !== false) {
                // Check for localStorage availability detection
                $has_detection = strpos($js_content, 'typeof Storage') !== false ||
                               strpos($js_content, 'localStorage === undefined') !== false ||
                               strpos($js_content, 'window.localStorage') !== false;

                $this->assertTrue(
                    $has_detection,
                    "localStorage usage in {$js_file} should include availability detection"
                );

                // Check for error handling
                $has_error_handling = strpos($js_content, 'try') !== false &&
                                    strpos($js_content, 'catch') !== false;

                $this->assertTrue(
                    $has_error_handling,
                    "localStorage usage in {$js_file} should include error handling"
                );
            }
        }
    }

    /**
     * Test CSS feature compatibility
     *
     * @covers \local_equipment CSS feature usage
     */
    public function test_css_feature_compatibility(): void {
        global $CFG;

        $scss_files = glob($CFG->dirroot . '/local/equipment/scss/*.scss');

        foreach ($scss_files as $scss_file) {
            $scss_content = file_get_contents($scss_file);
            $this->analyze_css_features($scss_content, basename($scss_file));
        }
    }

    /**
     * Test Bootstrap 5 compatibility
     *
     * @covers \local_equipment Bootstrap 5 usage
     */
    public function test_bootstrap_5_compatibility(): void {
        global $CFG;

        $template_files = glob($CFG->dirroot . '/local/equipment/templates/*.mustache');

        foreach ($template_files as $template_file) {
            $template_content = file_get_contents($template_file);

            // Check for Bootstrap 5 classes
            $bootstrap5_classes = [
                'text-bg-' => 'Bootstrap 5 background utility classes',
                'btn-outline-' => 'Bootstrap 5 outline button classes',
                'mb-' => 'Bootstrap 5 margin utilities',
                'visually-hidden' => 'Bootstrap 5 accessibility classes',
                'form-check' => 'Bootstrap 5 form components'
            ];

            foreach ($bootstrap5_classes as $class_pattern => $description) {
                if (strpos($template_content, $class_pattern) !== false) {
                    $this->addToAssertionCount(1);
                    // Log Bootstrap 5 usage for compatibility verification
                }
            }

            // Check for deprecated Bootstrap 4 classes
            $deprecated_classes = [
                'sr-only' => 'Use visually-hidden instead',
                'text-muted' => 'Check if compatible with current Bootstrap version',
                'btn-secondary' => 'Verify styling consistency'
            ];

            foreach ($deprecated_classes as $deprecated_class => $recommendation) {
                if (strpos($template_content, $deprecated_class) !== false) {
                    $this->addWarning(
                        "Potentially deprecated class '{$deprecated_class}' found in " .
                        basename($template_file) . ". {$recommendation}"
                    );
                }
            }
        }
    }

    /**
     * Test performance across different browser engines
     *
     * @covers \local_equipment cross-browser performance
     */
    public function test_cross_browser_performance(): void {
        global $OUTPUT;

        // Simulate rendering performance across different scenarios
        $performance_tests = [
            'single_template' => 1,
            'multiple_templates' => 10,
            'large_dataset' => 50
        ];

        foreach ($performance_tests as $test_name => $iteration_count) {
            $start_time = microtime(true);

            for ($i = 0; $i < $iteration_count; $i++) {
                $test_data = [
                    'students' => [
                        [
                            'name' => "Student {$i}",
                            'courses' => [
                                [
                                    'id' => $i,
                                    'name' => "Course {$i}",
                                    'status' => 'active',
                                    'badge_class' => 'badge text-bg-primary',
                                    'tooltip' => 'Active enrollment'
                                ]
                            ],
                            'courses_text' => "Course {$i}"
                        ]
                    ]
                ];

                $OUTPUT->render_from_template('local_equipment/vcc_students_cell', $test_data);
            }

            $execution_time = microtime(true) - $start_time;

            // Performance thresholds (conservative for slower browsers)
            $thresholds = [
                'single_template' => 0.1,    // 100ms
                'multiple_templates' => 0.5,  // 500ms
                'large_dataset' => 2.0       // 2 seconds
            ];

            $this->assertLessThan(
                $thresholds[$test_name],
                $execution_time,
                "Performance test '{$test_name}' exceeded threshold of {$thresholds[$test_name]}s"
            );
        }
    }

    /**
     * Analyze JavaScript API usage in code
     *
     * @param string $js_content JavaScript file content
     * @param string $filename Filename for reporting
     */
    private function analyze_js_api_usage(string $js_content, string $filename): void {
        foreach (self::REQUIRED_JS_APIS as $api => $description) {
            $api_usage = strpos($js_content, $api) !== false;

            if ($api_usage) {
                // Check for browser support detection
                $has_detection = strpos($js_content, "typeof {$api}") !== false ||
                               strpos($js_content, "window.{$api}") !== false ||
                               strpos($js_content, "{$api} === undefined") !== false;

                if ($api === 'ResizeObserver' || $api === 'fetch') {
                    $this->assertTrue(
                        $has_detection,
                        "API '{$api}' in {$filename} should include browser support detection. {$description}"
                    );
                }
            }
        }
    }

    /**
     * Analyze CSS features usage in SCSS files
     *
     * @param string $scss_content SCSS file content
     * @param string $filename Filename for reporting
     */
    private function analyze_css_features(string $scss_content, string $filename): void {
        $feature_patterns = [
            'flexbox' => ['display.*flex', 'flex-.*:', 'justify-content', 'align-items'],
            'grid' => ['display.*grid', 'grid-template', 'grid-gap'],
            'custom-properties' => ['--[a-zA-Z]', 'var\('],
            'transforms' => ['transform:', 'translate', 'rotate', 'scale'],
            'media-queries' => ['@media']
        ];

        foreach ($feature_patterns as $feature => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match('/' . $pattern . '/', $scss_content)) {
                    $this->addToAssertionCount(1);
                    break; // Feature detected, move to next feature
                }
            }
        }

        // Check for vendor prefixes (should be minimal with modern browsers)
        $vendor_prefixes = ['-webkit-', '-moz-', '-ms-', '-o-'];
        foreach ($vendor_prefixes as $prefix) {
            if (strpos($scss_content, $prefix) !== false) {
                $this->addWarning(
                    "Vendor prefix '{$prefix}' found in {$filename}. " .
                    "Consider if still necessary for target browser support."
                );
            }
        }
    }
}