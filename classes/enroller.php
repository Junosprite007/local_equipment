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
 * Handles enrollment operations for the equipment plugin.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment;

defined('MOODLE_INTERNAL') || die();

use local_equipment\hook\equipment_user_enrolled;

/**
 * Handles course enrollments for the equipment plugin
 */
class enroller {
    /** @var \stdClass The course being enrolled into */
    private $course;

    /** @var \enrol_manual_plugin The manual enrollment plugin instance */
    private $manualplugin;

    /** @var \stdClass The enrollment instance for this course */
    private $instance;

    /**
     * Constructor
     *
     * @param int|\stdClass $courseorid The course or course ID to enroll into
     * @throws \moodle_exception If the course doesn't exist or manual enrollment isn't available
     */
    public function __construct($courseorid) {
        global $DB;

        // Get the course
        if (is_object($courseorid)) {
            $this->course = $courseorid;
        } else {
            $this->course = $DB->get_record('course', ['id' => $courseorid], '*', MUST_EXIST);
        }

        // Get manual enrollment plugin
        $this->manualplugin = enrol_get_plugin('manual');
        if (empty($this->manualplugin)) {
            throw new \moodle_exception('manualpluginnotinstalled', 'enrol_manual');
        }

        // Get or create manual enrollment instance
        $this->instance = $this->get_or_create_enrol_instance();
    }

    /**
     * Get or create a manual enrollment instance for this course
     *
     * @return \stdClass The enrollment instance
     */
    private function get_or_create_enrol_instance(): \stdClass {
        global $DB;

        // Try to find an existing instance
        $instance = $DB->get_record('enrol', [
            'courseid' => $this->course->id,
            'enrol' => 'manual',
            'status' => ENROL_INSTANCE_ENABLED
        ]);

        if ($instance) {
            return $instance;
        }

        // No instance found, create one
        $fields = $this->manualplugin->get_instance_defaults();
        $fields['status'] = ENROL_INSTANCE_ENABLED;
        $fields['courseid'] = $this->course->id;

        $instanceid = $this->manualplugin->add_instance($this->course, $fields);
        return $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);
    }

    /**
     * Enroll a user in the course
     *
     * @param \stdClass|int $userorid The user or user ID to enroll
     * @param int $roleid The role ID to assign
     * @param int $timestart Enrollment start time (0 = now)
     * @param int $timeend Enrollment end time (0 = no end)
     * @return \stdClass Result object containing success/error messages
     */
    public function enrol_user($userorid, int $roleid, int $timestart = 0, int $timeend = 0): \stdClass {
        global $DB;

        $result = new \stdClass();
        $result->successes = [];
        $result->warnings = [];
        $result->errors = [];
        $result->coursename = $this->course->fullname;

        try {
            // Get user if needed
            if (is_object($userorid)) {
                $user = $userorid;
            } else {
                $user = $DB->get_record('user', ['id' => $userorid], '*', MUST_EXIST);
            }

            // Check if course is visible
            if (!$this->course->visible) {
                throw new \moodle_exception('coursenotvisible', 'error');
            }

            // Get role shortname for the hook
            $roletype = $DB->get_field('role', 'shortname', ['id' => $roleid]);
            if (!$roletype) {
                throw new \moodle_exception('invalidroleid', 'error');
            }

            // Check if user is already enrolled
            $context = \context_course::instance($this->course->id);
            if (is_enrolled($context, $user->id)) {
                $msg = (object)[
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'coursename' => $this->course->fullname
                ];
                $result->warnings[] = get_string('useralreadyenrolled', 'local_equipment', $msg);
                return $result;
            }

            // Do the enrollment
            try {
                $this->manualplugin->enrol_user(
                    $this->instance,
                    $user->id,
                    $roleid,
                    $timestart,
                    $timeend,
                    ENROL_USER_ACTIVE
                );
            } catch (\Exception $e) {
                // If enrollment failed due to existing grades, try unenrolling first
                if (strpos($e->getMessage(), 'already has grades') !== false) {
                    $this->manualplugin->unenrol_user($this->instance, $user->id);
                    $this->manualplugin->enrol_user(
                        $this->instance,
                        $user->id,
                        $roleid,
                        $timestart,
                        $timeend,
                        ENROL_USER_ACTIVE
                    );
                } else {
                    throw $e;
                }
            }

            // Build success message
            $msg = (object)[
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'coursename' => $this->course->fullname
            ];
            $result->successes[] = get_string('userenrolled', 'local_equipment', $msg);

            // Dispatch our custom enrollment hook
            $hook = new equipment_user_enrolled($user->id, $this->course->id, $roletype);
            \core\di::get(\core\hook\manager::class)->dispatch($hook);
        } catch (\Throwable $e) {
            debugging('Enrollment error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $result->errors[] = $e->getMessage();
        }

        return $result;
    }
}
