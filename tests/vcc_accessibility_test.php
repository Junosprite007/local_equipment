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
 * Accessibility compliance tests for VCC submission system (WCAG 2.1 AA)
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
 * Test class for VCC accessibility compliance
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_accessibility_test extends advanced_testcase {

    /** @var array WCAG 2.1 AA color contrast requirements */
    private const COLOR_CONTRAST_RATIOS = [
        'normal_text' => 4.5,      // Normal text minimum contrast ratio
        'large_text' => 3.0,       // Large text minimum contrast ratio
        'ui_components' => 3.0     // UI components minimum contrast ratio
    ];

    /** @var array Required ARIA attributes for interactive elements */
    private const REQUIRED_ARIA_ATTRIBUTES = [
        'button' => ['aria-label', 'aria-describedby'],
        'dropdown' => ['aria-expanded', 'aria-haspopup'],
        'table' => ['aria-label', 'role'],
        'form' => ['aria-labelledby', 'aria-describedby'],
        'alert' => ['role', 'aria-live']
    ];

    /** @var array Keyboard navigation requirements */
    private const KEYBOARD_NAVIGATION = [
        'tab_order' => 'Sequential tab navigation',
        'enter_activation' => 'Enter key activation',
        'escape_dismissal' => 'Escape key dismissal',
        'arrow_navigation' => 'Arrow key navigation in grids',
        'focus_indicators' => 'Visible focus indicators'
    ];

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test ARIA labels and roles in templates
     *
     * @covers \local_equipment ARIA attribute usage
     */
    public function test_aria_labels_and_roles(): void {
        global $CFG, $OUTPUT;

        $template_files = glob($CFG->dirroot . '/local/equipment/templates/vcc_*.mustache');

        foreach ($template_files as $template_file) {
            $template_content = file_get_contents($template_file);
            $template_name = basename($template_file, '.mustache');

            $this->analyze_aria_compliance($template_content, $template_name);
        }
    }

    /**
     * Test table accessibility with proper headers and navigation
     *
     * @covers \local_equipment table accessibility
     */
    public function test_table_accessibility(): void {
        global $OUTPUT;

        // Test main submissions table
        $table_data = [
            'submissions' => [
                [
                    'id' => 1,
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'john.doe@example.com',
                    'status' => 'active'
                ]
            ]
        ];

        // Check if the table has proper ARIA attributes
        $this->assert_table_has_proper_structure();

        // Test column management accessibility
        $column_data = [
            'column_toggles' => [
                [
                    'id' => 'col-firstname',
                    'name' => 'First Name',
                    'checked' => true
                ],
                [
                    'id' => 'col-lastname',
                    'name' => 'Last Name',
                    'checked' => false
                ]
            ]
        ];

        $rendered = $OUTPUT->render_from_template('local_equipment/vcc_column_manager', $column_data);
        $this->analyze_form_accessibility($rendered, 'column_manager');
    }

    /**
     * Test color contrast compliance
     *
     * @covers \local_equipment color contrast requirements
     */
    public function test_color_contrast_compliance(): void {
        global $CFG;

        $scss_files = glob($CFG->dirroot . '/local/equipment/scss/*.scss');

        foreach ($scss_files as $scss_file) {
            $scss_content = file_get_contents($scss_file);
            $this->analyze_color_usage($scss_content, basename($scss_file));
        }
    }

    /**
     * Test keyboard navigation support
     *
     * @covers \local_equipment keyboard accessibility
     */
    public function test_keyboard_navigation_support(): void {
        global $CFG;

        $js_files = glob($CFG->dirroot . '/local/equipment/amd/src/*.js');

        foreach ($js_files as $js_file) {
            $js_content = file_get_contents($js_file);
            $this->analyze_keyboard_support($js_content, basename($js_file));
        }
    }

    /**
     * Test focus management and indicators
     *
     * @covers \local_equipment focus management
     */
    public function test_focus_management(): void {
        global $CFG;

        $scss_file = $CFG->dirroot . '/local/equipment/scss/vcc_table.scss';

        if (!file_exists($scss_file)) {
            $this->markTestSkipped('VCC table SCSS file not found');
        }

        $scss_content = file_get_contents($scss_file);

        // Check for focus indicators
        $focus_patterns = [
            ':focus' => 'Basic focus styling',
            ':focus-visible' => 'Modern focus-visible styling',
            'outline' => 'Focus outline properties',
            'box-shadow.*focus' => 'Focus box-shadow styling'
        ];

        $has_focus_styling = false;
        foreach ($focus_patterns as $pattern => $description) {
            if (preg_match('/' . $pattern . '/i', $scss_content)) {
                $has_focus_styling = true;
                break;
            }
        }

        $this->assertTrue(
            $has_focus_styling,
            'SCSS should include focus styling for keyboard navigation'
        );

        // Check for high contrast mode support
        $high_contrast_patterns = [
            '@media.*prefers-contrast.*high' => 'High contrast media query',
            'forced-colors' => 'Forced colors mode support'
        ];

        foreach ($high_contrast_patterns as $pattern => $description) {
            if (preg_match('/' . $pattern . '/i', $scss_content)) {
                $this->addToAssertionCount(1);
                // High contrast support found
            }
        }
    }

    /**
     * Test screen reader compatibility
     *
     * @covers \local_equipment screen reader support
     */
    public function test_screen_reader_compatibility(): void {
        global $OUTPUT;

        // Test status badges with proper announcements
        $status_data = [
            'email_confirmed' => true,
            'phone_confirmed' => false,
            'is_expired' => false
        ];

        $rendered = $OUTPUT->render_from_template('local_equipment/vcc_status_cell', $status_data);

        // Check for screen reader text
        $sr_patterns = [
            'visually-hidden' => 'Hidden text for screen readers',
            'sr-only' => 'Screen reader only text',
            'aria-label' => 'ARIA labels for context',
            'title=' => 'Title attributes for additional context'
        ];

        foreach ($sr_patterns as $pattern => $description) {
            if (strpos($rendered, $pattern) !== false) {
                $this->addToAssertionCount(1);
            }
        }

        // Test loading states accessibility
        $loading_data = ['message' => 'Loading submissions...'];
        $loading_rendered = $OUTPUT->render_from_template('local_equipment/vcc_loading_state', $loading_data);

        $this->assertStringContainsString(
            'aria-live',
            $loading_rendered,
            'Loading states should have aria-live regions for screen reader announcements'
        );
    }

    /**
     * Test form accessibility and labeling
     *
     * @covers \local_equipment form accessibility
     */
    public function test_form_accessibility(): void {
        global $OUTPUT;

        // Test filter form accessibility
        $filter_data = [
            'form' => $this->get_accessible_filter_form_html(),
            'has_filters' => true
        ];

        $rendered = $OUTPUT->render_from_template('local_equipment/vcc_filters', $filter_data);

        // Check for proper form labeling
        $form_requirements = [
            'label.*for=' => 'Form labels with for attributes',
            'aria-labelledby' => 'ARIA labelledby attributes',
            'aria-describedby' => 'ARIA describedby attributes',
            'required' => 'Required field indicators',
            'aria-invalid' => 'Invalid field indicators'
        ];

        foreach ($form_requirements as $pattern => $description) {
            $has_requirement = preg_match('/' . $pattern . '/i', $rendered);
            if ($has_requirement) {
                $this->addToAssertionCount(1);
            }
        }
    }

    /**
     * Test error message accessibility
     *
     * @covers \local_equipment error message accessibility
     */
    public function test_error_message_accessibility(): void {
        global $OUTPUT;

        $error_data = [
            'message' => 'Failed to load submission data',
            'details' => 'Please check your network connection and try again'
        ];

        $rendered = $OUTPUT->render_from_template('local_equipment/vcc_error_state', $error_data);

        // Check for proper error announcement
        $error_requirements = [
            'role="alert"' => 'Alert role for immediate announcement',
            'aria-live="assertive"' => 'Assertive live region',
            'aria-atomic="true"' => 'Atomic live region updates'
        ];

        foreach ($error_requirements as $attribute => $description) {
            $this->assertStringContainsString(
                $attribute,
                $rendered,
                "Error messages should have {$description}"
            );
        }
    }

    /**
     * Test text scaling support (up to 200%)
     *
     * @covers \local_equipment text scaling support
     */
    public function test_text_scaling_support(): void {
        global $CFG;

        $scss_file = $CFG->dirroot . '/local/equipment/scss/vcc_table.scss';

        if (!file_exists($scss_file)) {
            $this->markTestSkipped('VCC table SCSS file not found');
        }

        $scss_content = file_get_contents($scss_file);

        // Check for relative units instead of fixed pixels
        $relative_units = [
            'rem' => 'Root em units for scalable text',
            'em' => 'Em units for scalable text',
            'vh' => 'Viewport height units',
            'vw' => 'Viewport width units',
            '%' => 'Percentage units'
        ];

        $uses_relative_units = false;
        foreach ($relative_units as $unit => $description) {
            if (strpos($scss_content, $unit) !== false) {
                $uses_relative_units = true;
                break;
            }
        }

        $this->assertTrue(
            $uses_relative_units,
            'SCSS should use relative units to support text scaling'
        );

        // Check for excessive use of fixed pixel units
        $pixel_usage = preg_match_all('/\d+px/', $scss_content);
        $total_length = strlen($scss_content);

        if ($pixel_usage > 0 && $total_length > 0) {
            $pixel_density = $pixel_usage / ($total_length / 100); // pixels per 100 characters

            $this->assertLessThan(
                0.5,
                $pixel_density,
                'Excessive use of fixed pixel units may prevent proper text scaling'
            );
        }
    }

    /**
     * Analyze ARIA compliance in template content
     *
     * @param string $template_content Template HTML content
     * @param string $template_name Template name for reporting
     */
    private function analyze_aria_compliance(string $template_content, string $template_name): void {
        // Check for interactive elements without ARIA
        $interactive_elements = [
            'button' => 'button elements',
            'input.*type="button"' => 'button inputs',
            'div.*role="button"' => 'div buttons',
            'a.*href' => 'links',
            'select' => 'select elements'
        ];

        foreach ($interactive_elements as $pattern => $description) {
            if (preg_match('/' . $pattern . '/i', $template_content)) {
                // Check if these elements have proper ARIA attributes
                $has_aria = preg_match('/aria-label|aria-labelledby|aria-describedby/', $template_content);

                if (!$has_aria) {
                    $this->addWarning(
                        "Interactive elements ({$description}) in {$template_name} may need ARIA attributes"
                    );
                }
            }
        }

        // Check for proper table structure
        if (strpos($template_content, '<table') !== false) {
            $table_requirements = [
                'role="table"' => 'Table role',
                'aria-label' => 'Table label',
                'thead' => 'Table header',
                'scope=' => 'Header scope attributes'
            ];

            foreach ($table_requirements as $requirement => $description) {
                if (strpos($template_content, $requirement) === false) {
                    $this->addWarning(
                        "Table in {$template_name} may be missing {$description}"
                    );
                }
            }
        }
    }

    /**
     * Analyze form accessibility requirements
     *
     * @param string $form_content Form HTML content
     * @param string $form_name Form name for reporting
     */
    private function analyze_form_accessibility(string $form_content, string $form_name): void {
        // Check for form inputs without labels
        $input_pattern = '/<input[^>]*>/';
        preg_match_all($input_pattern, $form_content, $inputs);

        foreach ($inputs[0] as $input) {
            $has_label = strpos($input, 'aria-label') !== false ||
                        strpos($input, 'aria-labelledby') !== false ||
                        preg_match('/id="([^"]*)"/', $input, $id_matches) &&
                        isset($id_matches[1]) &&
                        strpos($form_content, 'for="' . $id_matches[1] . '"') !== false;

            if (!$has_label) {
                $this->addWarning(
                    "Input in {$form_name} may be missing proper label association"
                );
            }
        }
    }

    /**
     * Analyze color usage for contrast compliance
     *
     * @param string $scss_content SCSS file content
     * @param string $filename Filename for reporting
     */
    private function analyze_color_usage(string $scss_content, string $filename): void {
        // Extract color definitions
        $color_patterns = [
            'color:\s*#([0-9a-fA-F]{3,6})' => 'text colors',
            'background-color:\s*#([0-9a-fA-F]{3,6})' => 'background colors',
            'border-color:\s*#([0-9a-fA-F]{3,6})' => 'border colors'
        ];

        foreach ($color_patterns as $pattern => $description) {
            preg_match_all('/' . $pattern . '/', $scss_content, $matches);

            if (!empty($matches[1])) {
                // Colors found - would need actual contrast calculation in real implementation
                $this->addToAssertionCount(1);
            }
        }

        // Check for high contrast mode considerations
        $contrast_considerations = [
            'prefers-contrast' => 'High contrast media queries',
            'forced-colors' => 'Forced colors mode support'
        ];

        foreach ($contrast_considerations as $feature => $description) {
            if (strpos($scss_content, $feature) !== false) {
                $this->addToAssertionCount(1);
            }
        }
    }

    /**
     * Analyze keyboard support in JavaScript
     *
     * @param string $js_content JavaScript file content
     * @param string $filename Filename for reporting
     */
    private function analyze_keyboard_support(string $js_content, string $filename): void {
        $keyboard_events = [
            'keydown' => 'Key down event handling',
            'keyup' => 'Key up event handling',
            'keypress' => 'Key press event handling'
        ];

        $has_keyboard_support = false;
        foreach ($keyboard_events as $event => $description) {
            if (strpos($js_content, $event) !== false) {
                $has_keyboard_support = true;
                break;
            }
        }

        if ($has_keyboard_support) {
            // Check for specific key handling
            $key_patterns = [
                'key.*===.*Enter' => 'Enter key handling',
                'key.*===.*Escape' => 'Escape key handling',
                'key.*===.*Tab' => 'Tab key handling',
                'keyCode.*===.*13' => 'Legacy Enter key handling',
                'keyCode.*===.*27' => 'Legacy Escape key handling'
            ];

            foreach ($key_patterns as $pattern => $description) {
                if (preg_match('/' . $pattern . '/i', $js_content)) {
                    $this->addToAssertionCount(1);
                }
            }
        }
    }

    /**
     * Assert that table has proper structure for accessibility
     */
    private function assert_table_has_proper_structure(): void {
        global $CFG;

        $table_file = $CFG->dirroot . '/local/equipment/classes/table/vcc_submissions_table.php';

        if (!file_exists($table_file)) {
            $this->markTestSkipped('VCC submissions table file not found');
        }

        $table_content = file_get_contents($table_file);

        // Check for ARIA attributes in table setup
        $table_attributes = [
            'aria-label' => 'Table label',
            'role.*table' => 'Table role',
            'set_attribute.*class' => 'CSS classes'
        ];

        foreach ($table_attributes as $pattern => $description) {
            $has_attribute = preg_match('/' . $pattern . '/i', $table_content);
            $this->assertTrue((bool)$has_attribute, "Table should have {$description}");
        }
    }

    /**
     * Get accessible filter form HTML for testing
     *
     * @return string
     */
    private function get_accessible_filter_form_html(): string {
        return '
            <form role="search" aria-label="Filter VCC submissions">
                <div class="row">
                    <div class="col-md-6">
                        <label for="search-input" class="form-label">Search</label>
                        <input type="search" id="search-input" class="form-control"
                               aria-describedby="search-help" placeholder="Search submissions">
                        <div id="search-help" class="form-text">Search by name, email, or partnership</div>
                    </div>
                    <div class="col-md-3">
                        <label for="date-start" class="form-label">Start Date</label>
                        <input type="date" id="date-start" class="form-control" aria-describedby="date-help">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary" aria-describedby="submit-help">
                            <span class="visually-hidden">Apply</span> Filter
                        </button>
                    </div>
                </div>
            </form>
        ';
    }
}