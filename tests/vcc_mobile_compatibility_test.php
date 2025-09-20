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
 * Mobile and touch interface tests for VCC submission system
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
 * Test class for VCC mobile compatibility
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_mobile_compatibility_test extends advanced_testcase {

    /** @var array CSS breakpoints to test */
    private const BREAKPOINTS = [
        'mobile_small' => 320,    // Small mobile devices
        'mobile_large' => 480,    // Large mobile devices
        'tablet_portrait' => 768, // Tablet portrait
        'tablet_landscape' => 1024, // Tablet landscape
        'desktop' => 1200        // Desktop
    ];

    /** @var array User agents to simulate */
    private const USER_AGENTS = [
        'ios_safari' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
        'android_chrome' => 'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36',
        'mobile_edge' => 'Mozilla/5.0 (Windows Phone 10.0; Android 6.0.1; Microsoft; Lumia 950) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Mobile Safari/537.36 Edge/16.16299'
    ];

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test CSS breakpoint behavior at different screen sizes
     *
     * @covers \local_equipment responsive design breakpoints
     */
    public function test_css_breakpoint_behavior(): void {
        global $CFG;

        $scss_file = $CFG->dirroot . '/local/equipment/scss/vcc_table.scss';

        if (!file_exists($scss_file)) {
            $this->markTestSkipped('VCC table SCSS file not found');
        }

        $scss_content = file_get_contents($scss_file);

        // Check for responsive breakpoints
        $breakpoint_patterns = [
            '@media.*max-width.*768px' => 'Mobile breakpoint',
            '@media.*max-width.*480px' => 'Small mobile breakpoint',
            '@media.*min-width.*1024px' => 'Desktop breakpoint'
        ];

        foreach ($breakpoint_patterns as $pattern => $description) {
            $has_breakpoint = preg_match('/' . $pattern . '/i', $scss_content);
            $this->assertTrue((bool)$has_breakpoint, "Missing responsive breakpoint: {$description}");
        }

        // Check for mobile-specific CSS properties
        $mobile_properties = [
            'overflow-x.*auto' => 'Horizontal scrolling for mobile',
            'flex-wrap.*wrap' => 'Flexible wrapping for mobile layouts',
            'font-size.*smaller' => 'Smaller font sizes for mobile'
        ];

        foreach ($mobile_properties as $property => $description) {
            $has_property = preg_match('/' . $property . '/i', $scss_content);
            $this->assertTrue((bool)$has_property, "Missing mobile property: {$description}");
        }
    }

    /**
     * Test virtual keyboard interaction scenarios
     *
     * @covers \local_equipment mobile form interactions
     */
    public function test_virtual_keyboard_interactions(): void {
        global $OUTPUT;

        // Test filter form rendering with mobile-friendly inputs
        $filter_data = [
            'form' => '<form><input type="search" class="form-control"><input type="date" class="form-control"></form>',
            'has_filters' => true
        ];

        $rendered = $OUTPUT->render_from_template('local_equipment/vcc_filters', $filter_data);

        // Check for mobile-friendly input attributes
        $this->assertStringContainsString('type="search"', $rendered, 'Search input should use search type for mobile keyboards');

        // Check for responsive form classes
        $mobile_classes = ['form-control', 'mb-2', 'mb-sm-0'];
        foreach ($mobile_classes as $class) {
            $this->assertStringContainsString($class, $rendered, "Missing mobile-friendly class: {$class}");
        }
    }

    /**
     * Test touch interaction patterns
     *
     * @covers \local_equipment touch interface design
     */
    public function test_touch_interaction_patterns(): void {
        global $CFG;

        $js_file = $CFG->dirroot . '/local/equipment/amd/src/vcc_table_columns.js';

        if (!file_exists($js_file)) {
            $this->markTestSkipped('VCC table columns JS file not found');
        }

        $js_content = file_get_contents($js_file);

        // Check for touch event handling
        $touch_events = [
            'touchstart' => 'Touch start event handling',
            'touchmove' => 'Touch move event handling',
            'touchend' => 'Touch end event handling'
        ];

        $has_touch_support = false;
        foreach ($touch_events as $event => $description) {
            if (strpos($js_content, $event) !== false) {
                $has_touch_support = true;
                break;
            }
        }

        // Either touch events or pointer events should be supported
        $has_pointer_events = strpos($js_content, 'pointerdown') !== false ||
                             strpos($js_content, 'pointermove') !== false;

        $this->assertTrue(
            $has_touch_support || $has_pointer_events,
            'Touch or pointer event support should be implemented for mobile devices'
        );

        // Check for minimum touch target sizes (44px minimum recommended)
        $this->assertStringContainsString(
            'min-width',
            $js_content,
            'Minimum touch target sizes should be enforced'
        );
    }

    /**
     * Test column resizing on touch devices
     *
     * @covers \local_equipment touch column resizing
     */
    public function test_touch_column_resizing(): void {
        global $CFG;

        $js_file = $CFG->dirroot . '/local/equipment/amd/src/vcc_table_columns.js';

        if (!file_exists($js_file)) {
            $this->markTestSkipped('VCC table columns JS file not found');
        }

        $js_content = file_get_contents($js_content);

        // Check for touch-friendly resize handles
        $resize_indicators = [
            'cursor.*col-resize' => 'Column resize cursor',
            'width.*4px' => 'Resize handle width',
            'position.*absolute' => 'Positioned resize handles'
        ];

        foreach ($resize_indicators as $pattern => $description) {
            $has_indicator = preg_match('/' . $pattern . '/i', $js_content);
            $this->assertTrue((bool)$has_indicator, "Missing resize indicator: {$description}");
        }

        // Check for minimum handle size for touch
        $min_touch_size = preg_match('/width.*([0-9]+)px/', $js_content, $matches);
        if ($min_touch_size && isset($matches[1])) {
            $handle_size = (int)$matches[1];
            $this->assertGreaterThanOrEqual(4, $handle_size, 'Resize handle should be at least 4px wide for touch');
        }
    }

    /**
     * Test dropdown menu touch accessibility
     *
     * @covers \local_equipment mobile dropdown interactions
     */
    public function test_dropdown_touch_accessibility(): void {
        global $OUTPUT;

        // Test column manager dropdown
        $dropdown_data = [
            'column_toggles' => [
                [
                    'id' => 'col-1',
                    'name' => 'Test Column',
                    'checked' => true
                ]
            ]
        ];

        $rendered = $OUTPUT->render_from_template('local_equipment/vcc_column_manager', $dropdown_data);

        // Check for touch-friendly dropdown attributes
        $touch_attributes = [
            'data-bs-toggle="dropdown"' => 'Bootstrap dropdown toggle',
            'aria-expanded=' => 'ARIA expanded state',
            'role=' => 'ARIA role definitions'
        ];

        foreach ($touch_attributes as $attribute => $description) {
            $this->assertStringContainsString($attribute, $rendered, "Missing touch attribute: {$description}");
        }

        // Check for adequate spacing between interactive elements
        $this->assertStringContainsString('mb-2', $rendered, 'Adequate margin for touch targets');
    }

    /**
     * Test table scrolling behavior on mobile
     *
     * @covers \local_equipment mobile table scrolling
     */
    public function test_mobile_table_scrolling(): void {
        global $CFG;

        $scss_file = $CFG->dirroot . '/local/equipment/scss/vcc_table.scss';

        if (!file_exists($scss_file)) {
            $this->markTestSkipped('VCC table SCSS file not found');
        }

        $scss_content = file_get_contents($scss_file);

        // Check for horizontal scrolling setup
        $scroll_properties = [
            'overflow-x.*auto' => 'Horizontal scrolling enabled',
            'table-responsive' => 'Bootstrap responsive table class',
            '-webkit-overflow-scrolling.*touch' => 'iOS momentum scrolling'
        ];

        foreach ($scroll_properties as $property => $description) {
            $has_property = preg_match('/' . $property . '/i', $scss_content);
            $this->assertTrue((bool)$has_property, "Missing scroll property: {$description}");
        }

        // Check for fixed table layout for better mobile performance
        $this->assertRegExp(
            '/table-layout.*fixed/i',
            $scss_content,
            'Fixed table layout should be used for better mobile performance'
        );
    }

    /**
     * Test mobile-specific filter form behavior
     *
     * @covers \local_equipment mobile filter interactions
     */
    public function test_mobile_filter_form_behavior(): void {
        global $OUTPUT;

        $filter_data = [
            'form' => $this->get_mock_filter_form_html(),
            'has_filters' => true
        ];

        $rendered = $OUTPUT->render_from_template('local_equipment/vcc_filters', $filter_data);

        // Check for mobile-optimized form elements
        $mobile_optimizations = [
            'col-12.*col-md-' => 'Mobile-first responsive columns',
            'mb-3.*mb-md-0' => 'Mobile margin adjustments',
            'btn-sm' => 'Smaller buttons for mobile'
        ];

        foreach ($mobile_optimizations as $pattern => $description) {
            $has_optimization = preg_match('/' . $pattern . '/i', $rendered);
            $this->assertTrue((bool)$has_optimization, "Missing mobile optimization: {$description}");
        }
    }

    /**
     * Test performance on mobile devices simulation
     *
     * @covers \local_equipment mobile performance
     */
    public function test_mobile_performance_simulation(): void {
        // Simulate slower mobile processor with timing constraints
        $start_time = microtime(true);

        // Simulate rendering multiple templates (as would happen on mobile)
        global $OUTPUT;

        for ($i = 0; $i < 10; $i++) {
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

        // Template rendering should be fast even on mobile
        $this->assertLessThan(
            0.5,
            $execution_time,
            'Template rendering should complete within 500ms for mobile performance'
        );
    }

    /**
     * Generate mock filter form HTML for testing
     *
     * @return string
     */
    private function get_mock_filter_form_html(): string {
        return '
            <form class="row g-3">
                <div class="col-12 col-md-6">
                    <input type="search" class="form-control" placeholder="Search">
                </div>
                <div class="col-12 col-md-3">
                    <input type="date" class="form-control">
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
            </form>
        ';
    }
}