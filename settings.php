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
 * Equipment checkout settings.
 *
 * @package     local_equipment
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Let's define some variables for all the name of the part of the admin tree where the setting will be added.

// The Site administration tab that these settings will be seen under.
// The 'Plugins' tab is actually called 'modules' in the code. Took me forever to figure that out.
$component = 'local_equipment';

// Let's create all the categories and subcategories we will need for the admins to access.
// Remember, categories and subcategories do not appear until they have a page or link added to them.

// Main category must be created first!
// Create a new category under the Site administration > Plugins tab, which is actually called 'modules' in the codebase. Took me forever to figure that out.
$ADMIN->add(
    'modules',
    new admin_category(
        $component,
    new lang_string('equipment', 'local_equipment')
));

// This is just creating a standard settings page for the Equipment plugin, which doesn't contain anything yet.
$ADMIN->add(
    $component,
    new admin_settingpage(
        "{$component}_settings",
    new lang_string('pluginsettings', 'local_equipment')
    )
);

// Add 'Partnerships' subcategory.
$ADMIN->add(
    $component,
    new admin_category(
        "{$component}_partnerships_cat",
        new lang_string('partnerships', 'local_equipment')
    )
);
// Add 'Pickups' subcategory.
$ADMIN->add(
    $component,
    new admin_category(
        "{$component}_pickups_cat",
        new lang_string('pickups', 'local_equipment')
    )
);
// Add 'Agreements' subcategory.
$ADMIN->add(
    $component,
    new admin_category(
        "{$component}_agreements_cat",
        new lang_string('agreements', 'local_equipment')
    )
);
// Add 'Consent form' subcategory.
$ADMIN->add(
    $component,
    new admin_category(
        "{$component}_consentform_cat",
        new lang_string('consentform', 'local_equipment')
    )
);

// An external page is a link to a page outside of the Moodle admin settings, i.e. to a file within the custom plugin.
// Add the manage partnerships page.
$ADMIN->add(
    "{$component}_partnerships_cat",
    new admin_externalpage(
        "{$component}_partnerships", // Needs to match the parameter in 'admin_externalpage_setup()' on the partnerships.php page.
        new lang_string('viewmanagepartnerships', 'local_equipment'),
        new moodle_url('/local/equipment/partnerships.php'),
    )
);
// Add a link to the add partnership page.
$ADMIN->add(
    "{$component}_partnerships_cat",
    new admin_externalpage(
        "{$component}_addpartnerships",
        new lang_string('addpartnerships', 'local_equipment'),
        new moodle_url('/local/equipment/partnerships/addpartnerships.php')
    )
);
// Add the manage pickups page.
$ADMIN->add(
    "{$component}_pickups_cat",
    new admin_externalpage(
        "{$component}_pickups",
        new lang_string('viewmanagepickups', 'local_equipment'),
        new moodle_url('/local/equipment/pickups.php'),
    )
);
// Add a link to the add partnership page.
$ADMIN->add(
    "{$component}_pickups_cat",
    new admin_externalpage(
        "{$component}_addpickups",
        new lang_string('addpickups', 'local_equipment'),
        new moodle_url('/local/equipment/pickups/addpickups.php')
    )
);
