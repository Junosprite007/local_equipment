<?php
// This file is part of Moodle - https://moodle.org/
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
 * Installation script for local_equipment.
 *
 * @package    local_equipment
 * @copyright  2024 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Install needed dependencies.
 */
function xmldb_local_equipment_install() {
    global $CFG;

    // Check if composer is available.
    if (!file_exists($CFG->dirroot . '/local/equipment/vendor/autoload.php')) {
        mtrace('Installing AWS SDK dependencies...');

        $composerpath = __DIR__ . '/../composer.json';
        if (file_exists($composerpath)) {
            $currentdir = getcwd();
            chdir(__DIR__ . '/..');

            if (function_exists('shell_exec')) {
                $output = shell_exec('composer install --no-dev');
                mtrace($output);
            } else {
                mtrace('Warning: shell_exec is disabled. Please run composer install manually.');
            }

            chdir($currentdir);
        }
    }
}
