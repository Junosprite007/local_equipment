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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Default page for the Equipment checkout module.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// $id = required_param('id', PARAM_INT);
$context = context_system::instance();
$url = new moodle_url('/local/equipment/index.php');
$redirecturl = new moodle_url('/local/equipment/index.php');
$strequipmentcheckouts = get_string('pluginname', 'local_equipment');

require_login();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title($strequipmentcheckouts);
$PAGE->set_heading($strequipmentcheckouts);
$PAGE->navbar->add($strequipmentcheckouts);

// require_capability('local/equipment:managepartnerships', $context);
require_capability('local/equipment:seedetails', $context);

echo $OUTPUT->header();
echo $OUTPUT->heading($strequipmentcheckouts);

$PAGE->requires->js_call_amd('local_equipment/addpartnership_form', 'showAlert', ['Message', 'Hello all you people! I\'m self-executing.']);
echo $OUTPUT->footer();
