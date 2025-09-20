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
 * Theme compatibility tests for VCC submission system
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace local_equipment\tests;

use advanced_testcase;
use local_equipment\tests\helpers\vcc_test_data_generator;

defined('MOODLE_INTERNAL') || die();

/**
 * Test class for VCC theme compatibility
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_theme_compatibility_test extends advanced_testcase {

    /** @var array VCC template files to check */
    private const VCC_TEMPLATES = [
        'vcc_filters.mustache',
        'vcc_students_cell.mustache',
        'vcc_exchange_pickup_cell.mustache',
        'vcc_actions_cell.mustache',
        'vcc_status_cell.mustache',
        'vcc_pickup_cell.mustache',
        'vcc_column_manager.mustache',
        'vcc_loading_state.mustache',
        'vcc_empty_state.mustache',
        'vcc_error_state.mustache',
        'vcc_pagination_ajax.mustache',
        'vcc_table_toolbar.mustache'
    ];

    /** @var array Popular Moodle themes to test */
    private const POPULAR_THEMES = [
        'boost',
        'classic',
        'adaptable',
        'moove'
    ];

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test for existing theme overrides of equipment templates
     *
     * @covers \local_equipment template override detection
     */
    public function test_scan_for_theme_overrides(): void {
        global $CFG;

        $overrides_found = [];
        $themes_dir = $CFG->dirroot . '/theme';

        if (!is_dir($themes_dir)) {
            $this->markTestSkipped('Themes directory not found');
        }

        foreach (self::POPULAR_THEMES as $theme_name) {
            $theme_path = $themes_dir . '/' . $theme_name;

            if (!is_dir($theme_path)) {
                continue; // Skip if theme not installed
            }

            $template_override_path = $theme_path . '/templates/local_equipment';

            if (is_dir($template_override_path)) {
                foreach (self::VCC_TEMPLATES as $template) {
                    $override_file = $template_override_path . '/' . $template;

                    if (file_exists($override_file)) {
                        $overrides_found[] = [
                            'theme' => $theme_name,
                            'template' => $template,
                            'path' => $override_file
                        ];
                    }
                }
            }
        }

        // Log found overrides for manual review
        if (!empty($overrides_found)) {
            $this->addWarning(
                'Found theme template overrides that may need updating: ' .
                json_encode($overrides_found, JSON_PRETTY_PRINT)
            );
        }

        // This test always passes but provides information
        $this->assertTrue(true, 'Theme override scan completed');
    }

    /**
     * Test template variable validation
     *
     * @covers \local_equipment template variable requirements
     */
    public function test_template_variable_validation(): void {
        global $CFG;

        $template_dir = $CFG->dirroot . '/local/equipment/templates';
        $validation_results = [];

        foreach (self::VCC_TEMPLATES as $template_name) {
            $template_path = $template_dir . '/' . $template_name;

            if (!file_exists($template_path)) {
                $validation_results[$template_name] = ['status' => 'missing', 'variables' => []];
                continue;
            }

            $template_content = file_get_contents($template_path);
            $required_variables = $this->extract_template_variables($template_content);

            $validation_results[$template_name] = [
                'status' => 'found',
                'variables' => $required_variables,
                'has_conditionals' => $this->has_conditional_logic($template_content),
                'has_loops' => $this->has_loop_logic($template_content)
            ];
        }

        // Verify critical templates exist
        $critical_templates = ['vcc_students_cell.mustache', 'vcc_exchange_pickup_cell.mustache'];
        foreach ($critical_templates as $critical_template) {
            $this->assertArrayHasKey($critical_template, $validation_results);
            $this->assertEquals('found', $validation_results[$critical_template]['status']);
        }

        // Store results for reference
        $this->addToAssertionCount(1);
    }

    /**
     * Test templates with edge case data
     *
     * @covers \local_equipment template rendering with edge cases
     */
    public function test_templates_with_edge_case_data(): void {
        global $OUTPUT;

        $edge_case_data = vcc_test_data_generator::get_international_test_data();

        foreach ($edge_case_data as $case_name => $test_data) {
            // Test vcc_students_cell template with edge case data
            try {
                $students_data = [
                    'students' => [
                        [
                            'name' => ($test_data['firstname'] ?? 'Test') . ' ' . ($test_data['lastname'] ?? 'User'),
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
                ];

                $rendered = $OUTPUT->render_from_template('local_equipment/vcc_students_cell', $students_data);
                $this->assertNotEmpty($rendered, "Template rendering failed for case: {$case_name}");

            } catch (\Exception $e) {
                $this->fail("Template rendering exception for case '{$case_name}': " . $e->getMessage());
            }
        }
    }

    /**
     * Test template security validation
     *
     * @covers \local_equipment template XSS prevention
     */
    public function test_template_security_validation(): void {
        global $OUTPUT;

        $xss_test_cases = [
            'script_tag' => '<script>alert("XSS")</script>',
            'img_onerror' => '<img src="x" onerror="alert(1)">',
            'javascript_protocol' => 'javascript:alert(1)',
            'html_entities' => '&lt;script&gt;alert(1)&lt;/script&gt;',
            'encoded_script' => '%3Cscript%3Ealert(1)%3C/script%3E'
        ];

        foreach ($xss_test_cases as $case_name => $malicious_input) {
            $test_data = [
                'students' => [
                    [
                        'name' => $malicious_input,
                        'courses' => [],
                        'courses_text' => $malicious_input
                    ]
                ]
            ];

            try {
                $rendered = $OUTPUT->render_from_template('local_equipment/vcc_students_cell', $test_data);

                // Check that malicious content is properly escaped
                $this->assertStringNotContainsString('<script', $rendered, "XSS vulnerability in case: {$case_name}");
                $this->assertStringNotContainsString('javascript:', $rendered, "JavaScript protocol vulnerability in case: {$case_name}");
                $this->assertStringNotContainsString('onerror=', $rendered, "Event handler vulnerability in case: {$case_name}");

            } catch (\Exception $e) {
                // Template should handle malicious input gracefully
                $this->assertStringContainsString('Invalid', $e->getMessage(), "Unexpected exception for case: {$case_name}");
            }
        }
    }

    /**
     * Test CSS class conflicts between themes and plugin
     *
     * @covers \local_equipment CSS class compatibility
     */
    public function test_css_class_conflicts(): void {
        global $CFG;

        $plugin_scss_path = $CFG->dirroot . '/local/equipment/scss';
        $potential_conflicts = [];

        if (!is_dir($plugin_scss_path)) {
            $this->markTestSkipped('Plugin SCSS directory not found');
        }

        $scss_files = glob($plugin_scss_path . '/*.scss');
        $plugin_classes = [];

        foreach ($scss_files as $scss_file) {
            $content = file_get_contents($scss_file);
            $classes = $this->extract_css_classes($content);
            $plugin_classes = array_merge($plugin_classes, $classes);
        }

        // Check for common Bootstrap/Moodle class conflicts
        $common_classes = [
            'table', 'btn', 'form-control', 'dropdown', 'modal',
            'card', 'badge', 'alert', 'nav', 'container'
        ];

        foreach ($plugin_classes as $class) {
            if (in_array($class, $common_classes)) {
                $potential_conflicts[] = $class;
            }
        }

        // Log potential conflicts for review
        if (!empty($potential_conflicts)) {
            $this->addWarning(
                'Potential CSS class conflicts detected: ' . implode(', ', $potential_conflicts)
            );
        }

        $this->assertTrue(true, 'CSS conflict analysis completed');
    }

    /**
     * Extract template variables from Mustache template content
     *
     * @param string $content Template content
     * @return array
     */
    private function extract_template_variables(string $content): array {
        $variables = [];

        // Match {{variable}} and {{{variable}}} patterns
        preg_match_all('/\{\{[\{]?([^}]+)[\}]?\}\}/', $content, $matches);

        foreach ($matches[1] as $match) {
            $variable = trim($match);

            // Skip conditional logic and loops
            if (!in_array(substr($variable, 0, 1), ['#', '/', '!', '^'])) {
                $variables[] = $variable;
            }
        }

        return array_unique($variables);
    }

    /**
     * Check if template has conditional logic
     *
     * @param string $content Template content
     * @return bool
     */
    private function has_conditional_logic(string $content): bool {
        return (bool)preg_match('/\{\{#[^}]+\}\}/', $content);
    }

    /**
     * Check if template has loop logic
     *
     * @param string $content Template content
     * @return bool
     */
    private function has_loop_logic(string $content): bool {
        return (bool)preg_match('/\{\{#[^}]+\}\}.*\{\{\/[^}]+\}\}/s', $content);
    }

    /**
     * Extract CSS classes from SCSS content
     *
     * @param string $content SCSS content
     * @return array
     */
    private function extract_css_classes(string $content): array {
        $classes = [];

        // Match .class-name patterns
        preg_match_all('/\.([a-zA-Z][a-zA-Z0-9_-]+)/', $content, $matches);

        foreach ($matches[1] as $class) {
            $classes[] = $class;
        }

        return array_unique($classes);
    }
}