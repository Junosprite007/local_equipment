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
 * Hook for after a user is enrolled in a course through the bulk family upload feature of the equipment plugin
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_equipment\hook;

use core\attribute;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Hook dispatched after a user is enrolled through the equipment plugin.
 * This allows the plugin to handle notifications differently for students and parents.
 */
#[attribute\label('Hook dispatched after a user is enrolled through the equipment plugin')]
#[attribute\tags('enrollment', 'notification')]
final class equipment_user_enrolled implements StoppableEventInterface {
    /** @var bool Whether event propagation is stopped */
    private bool $stopped = false;

    /**
     * Constructor
     *
     * @param int $userid The ID of the enrolled user
     * @param int $courseid The ID of the course
     * @param string $roletype The type of role ('student' or 'parent')
     */
    public function __construct(
        public readonly int $userid,
        public readonly int $courseid,
        public readonly string $roletype
    ) {
    }

    /**
     * Check if event propagation is stopped
     *
     * @return bool
     */
    public function isPropagationStopped(): bool {
        return $this->stopped;
    }

    /**
     * Stop event propagation
     */
    public function stopPropagation(): void {
        $this->stopped = true;
    }
}
