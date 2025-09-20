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
 * Test data generator for VCC submission testing
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace local_equipment\tests\helpers;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * VCC test data generator helper class
 *
 * @package     local_equipment
 * @category    test
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_test_data_generator {

    /**
     * Generate various courseids JSON formats for testing
     *
     * @return array Array of test cases with descriptions
     */
    public static function get_courseids_test_cases(): array {
        return [
            // Valid formats
            'valid_single_string' => [
                'json' => '["841"]',
                'expected_valid' => true,
                'description' => 'Single course ID as string in array'
            ],
            'valid_multiple_strings' => [
                'json' => '["841","842","843"]',
                'expected_valid' => true,
                'description' => 'Multiple course IDs as strings'
            ],
            'valid_single_integer' => [
                'json' => '[841]',
                'expected_valid' => true,
                'description' => 'Single course ID as integer'
            ],
            'valid_multiple_integers' => [
                'json' => '[841,842,843]',
                'expected_valid' => true,
                'description' => 'Multiple course IDs as integers'
            ],
            'valid_mixed_types' => [
                'json' => '[841,"842",843]',
                'expected_valid' => true,
                'description' => 'Mixed string and integer course IDs'
            ],

            // Invalid formats
            'invalid_null' => [
                'json' => null,
                'expected_valid' => false,
                'description' => 'Null value'
            ],
            'invalid_empty_string' => [
                'json' => '',
                'expected_valid' => false,
                'description' => 'Empty string'
            ],
            'invalid_empty_array' => [
                'json' => '[]',
                'expected_valid' => false,
                'description' => 'Empty array string'
            ],
            'invalid_null_array' => [
                'json' => '[null]',
                'expected_valid' => false,
                'description' => 'Array containing null'
            ],
            'invalid_json_syntax' => [
                'json' => 'invalid_json',
                'expected_valid' => false,
                'description' => 'Invalid JSON syntax'
            ],
            'invalid_comma_only' => [
                'json' => '[,]',
                'expected_valid' => false,
                'description' => 'Array with comma only'
            ],
            'invalid_empty_string_element' => [
                'json' => '[""]',
                'expected_valid' => false,
                'description' => 'Array with empty string element'
            ],
            'invalid_null_string' => [
                'json' => '["null"]',
                'expected_valid' => false,
                'description' => 'Array with string "null"'
            ],

            // Edge cases
            'edge_zero_id' => [
                'json' => '[0]',
                'expected_valid' => false,
                'description' => 'Zero course ID'
            ],
            'edge_negative_id' => [
                'json' => '[-1]',
                'expected_valid' => false,
                'description' => 'Negative course ID'
            ],
            'edge_very_large_id' => [
                'json' => '[999999999]',
                'expected_valid' => false,
                'description' => 'Very large course ID (non-existent)'
            ],
            'edge_unicode_text' => [
                'json' => '["test🎓"]',
                'expected_valid' => false,
                'description' => 'Unicode characters in course ID'
            ],
            'edge_xss_attempt' => [
                'json' => '["<script>alert(1)</script>"]',
                'expected_valid' => false,
                'description' => 'XSS attempt in course ID'
            ],
            'edge_sql_injection' => [
                'json' => '["1\'; DROP TABLE courses; --"]',
                'expected_valid' => false,
                'description' => 'SQL injection attempt'
            ],
            'edge_very_long_string' => [
                'json' => '["' . str_repeat('a', 1000) . '"]',
                'expected_valid' => false,
                'description' => 'Very long string as course ID'
            ],
            'edge_special_characters' => [
                'json' => '["!@#$%^&*()"]',
                'expected_valid' => false,
                'description' => 'Special characters as course ID'
            ],

            // Malformed JSON structures
            'malformed_unclosed_bracket' => [
                'json' => '[841',
                'expected_valid' => false,
                'description' => 'Unclosed bracket'
            ],
            'malformed_unclosed_quote' => [
                'json' => '["841]',
                'expected_valid' => false,
                'description' => 'Unclosed quote'
            ],
            'malformed_trailing_comma' => [
                'json' => '[841,]',
                'expected_valid' => false,
                'description' => 'Trailing comma'
            ],
            'malformed_double_quotes' => [
                'json' => '[""841""]',
                'expected_valid' => false,
                'description' => 'Double quotes around course ID'
            ],
        ];
    }

    /**
     * Generate realistic VCC submission data for testing
     *
     * @param array $overrides Override specific fields
     * @return stdClass
     */
    public static function create_test_vcc_submission(array $overrides = []): stdClass {
        $defaults = [
            'userid' => rand(1, 1000),
            'partnershipid' => rand(1, 10),
            'pickupid' => rand(1, 20),
            'exchange_partnershipid' => rand(1, 10),
            'exchangesubmissionid' => rand(1, 100),
            'studentids' => json_encode([rand(1, 100), rand(101, 200)]),
            'confirmationid' => 'CONF' . rand(10000, 99999),
            'confirmationexpired' => false,
            'email' => 'test' . rand(1, 1000) . '@example.com',
            'email_confirmed' => (bool)rand(0, 1),
            'firstname' => 'TestFirst' . rand(1, 100),
            'lastname' => 'TestLast' . rand(1, 100),
            'phone' => '+1555' . sprintf('%07d', rand(1000000, 9999999)),
            'phone_confirmed' => (bool)rand(0, 1),
            'partnership_name' => 'Test Partnership ' . rand(1, 10),
            'mailing_streetaddress' => rand(100, 9999) . ' Test St',
            'mailing_apartment' => rand(0, 1) ? 'Apt ' . rand(1, 200) : null,
            'mailing_city' => 'Test City',
            'mailing_state' => 'TX',
            'mailing_zipcode' => sprintf('%05d', rand(10000, 99999)),
            'billing_sameasmailing' => (bool)rand(0, 1),
            'billing_streetaddress' => rand(100, 9999) . ' Billing St',
            'billing_apartment' => rand(0, 1) ? 'Unit ' . rand(1, 50) : null,
            'billing_city' => 'Billing City',
            'billing_state' => 'CA',
            'billing_zipcode' => sprintf('%05d', rand(90000, 99999)),
            'pickup_locationtime' => 'Weekdays 9AM-5PM',
            'electronicsignature' => 'John Doe',
            'pickupmethod' => 'School Pickup',
            'pickuppersonname' => 'Jane Smith',
            'pickuppersonphone' => '+1555' . sprintf('%07d', rand(1000000, 9999999)),
            'pickuppersondetails' => 'Authorized pickup person',
            'usernotes' => 'User notes for testing',
            'adminnotes' => 'Admin notes for testing',
            'timecreated' => time() - rand(0, 86400 * 30), // Within last 30 days
            'timemodified' => time()
        ];

        $data = array_merge($defaults, $overrides);

        $submission = new stdClass();
        foreach ($data as $key => $value) {
            $submission->$key = $value;
        }

        return $submission;
    }

    /**
     * Create test data for column preference validation
     *
     * @return array
     */
    public static function get_column_preference_test_cases(): array {
        return [
            // Valid preferences
            'valid_hidden_columns' => [
                'preference' => json_encode(['hidden_columns' => ['mailing_address', 'billing_address']]),
                'expected_valid' => true,
                'description' => 'Valid hidden columns preference'
            ],
            'valid_column_widths' => [
                'preference' => json_encode(['timecreated' => '120px', 'firstname' => '150px']),
                'expected_valid' => true,
                'description' => 'Valid column widths preference'
            ],

            // Invalid JSON preferences
            'invalid_json_syntax' => [
                'preference' => '{invalid_json}',
                'expected_valid' => false,
                'description' => 'Invalid JSON syntax in preference'
            ],
            'invalid_malformed_json' => [
                'preference' => '{"hidden_columns": [}',
                'expected_valid' => false,
                'description' => 'Malformed JSON structure'
            ],

            // Edge cases
            'edge_extremely_large_width' => [
                'preference' => json_encode(['timecreated' => '999999px']),
                'expected_valid' => false,
                'description' => 'Extremely large column width'
            ],
            'edge_negative_width' => [
                'preference' => json_encode(['timecreated' => '-100px']),
                'expected_valid' => false,
                'description' => 'Negative column width'
            ],
            'edge_zero_width' => [
                'preference' => json_encode(['timecreated' => '0px']),
                'expected_valid' => false,
                'description' => 'Zero column width'
            ],
            'edge_non_numeric_width' => [
                'preference' => json_encode(['timecreated' => 'invalid']),
                'expected_valid' => false,
                'description' => 'Non-numeric width value'
            ],

            // Security test cases
            'security_xss_attempt' => [
                'preference' => json_encode(['hidden_columns' => ['<script>alert(1)</script>']]),
                'expected_valid' => false,
                'description' => 'XSS attempt in column name'
            ],
            'security_sql_injection' => [
                'preference' => json_encode(['hidden_columns' => ['column\'; DROP TABLE users; --']]),
                'expected_valid' => false,
                'description' => 'SQL injection attempt in column name'
            ],
        ];
    }

    /**
     * Generate large dataset for performance testing
     *
     * @param int $count Number of records to generate
     * @return array
     */
    public static function generate_large_dataset(int $count): array {
        $records = [];

        for ($i = 0; $i < $count; $i++) {
            $records[] = self::create_test_vcc_submission([
                'id' => $i + 1,
                'email' => "testuser{$i}@example.com",
                'firstname' => "FirstName{$i}",
                'lastname' => "LastName{$i}",
                // Mix of valid and invalid courseids for testing
                'studentids' => $i % 5 === 0 ? 'invalid_json' : json_encode([rand(1, 100)])
            ]);
        }

        return $records;
    }

    /**
     * Create test data with international characters and edge cases
     *
     * @return array
     */
    public static function get_international_test_data(): array {
        return [
            'unicode_names' => [
                'firstname' => 'José',
                'lastname' => 'García-Martínez',
                'email' => 'josé.garcía@université.fr'
            ],
            'chinese_characters' => [
                'firstname' => '王',
                'lastname' => '小明',
                'mailing_streetaddress' => '北京市朝阳区'
            ],
            'arabic_text' => [
                'firstname' => 'محمد',
                'lastname' => 'الأحمد',
                'mailing_city' => 'الرياض'
            ],
            'emoji_content' => [
                'usernotes' => 'Great student! 👍🎓✨',
                'adminnotes' => 'Needs follow-up 📞'
            ],
            'long_text_fields' => [
                'usernotes' => str_repeat('This is a very long note that tests field length limits. ', 50),
                'mailing_streetaddress' => str_repeat('Very Long Street Name ', 20)
            ]
        ];
    }
}