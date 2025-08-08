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
 * Main Equipment Plugin Dashboard - Navigation to all accessible pages
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$context = \context_system::instance();
$url = new \moodle_url('/local/equipment/index.php');
$strequipmentcheckouts = get_string('pluginname', 'local_equipment');

require_login();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title($strequipmentcheckouts);
$PAGE->set_heading($strequipmentcheckouts);
$PAGE->navbar->add($strequipmentcheckouts);

require_capability('local/equipment:seedetails', $context);

echo $OUTPUT->header();
// echo $OUTPUT->heading($strequipmentcheckouts);

// Prepare template data for equipment dashboard
$templatedata = [
    'pagetitle' => $strequipmentcheckouts,
    'sections' => [
        // Partnership Management Section
        [
            'title' => get_string('partnershipmanagement', 'local_equipment'),
            'items' => [
                [
                    'url' => (new \moodle_url('/local/equipment/partnerships.php'))->out(false),
                    'title' => get_string('viewallpartnerships', 'local_equipment'),
                    'class' => 'btn-primary',
                    'gridclass' => 'col-md-4'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/partnerships/addpartnerships.php'))->out(false),
                    'title' => get_string('addnewpartnerships', 'local_equipment'),
                    'class' => 'btn-success',
                    'gridclass' => 'col-md-4'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/addbulkfamilies.php'))->out(false),
                    'title' => get_string('addbulkfamilies', 'local_equipment'),
                    'class' => 'btn-info',
                    'gridclass' => 'col-md-4'
                ]
            ]
        ],
        // Pickup Management Section
        [
            'title' => get_string('pickupmanagement', 'local_equipment'),
            'items' => [
                [
                    'url' => (new \moodle_url('/local/equipment/pickups.php'))->out(false),
                    'title' => get_string('viewallpickups', 'local_equipment'),
                    'class' => 'btn-primary',
                    'gridclass' => 'col-md-6'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/pickups/addpickups.php'))->out(false),
                    'title' => get_string('addnewpickup', 'local_equipment'),
                    'class' => 'btn-success',
                    'gridclass' => 'col-md-6'
                ]
            ]
        ],
        // Agreement Management Section
        [
            'title' => get_string('agreementmanagement', 'local_equipment'),
            'items' => [
                [
                    'url' => (new \moodle_url('/local/equipment/agreements.php'))->out(false),
                    'title' => get_string('viewallagreements', 'local_equipment'),
                    'class' => 'btn-primary',
                    'gridclass' => 'col-md-6'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/agreements/addagreements.php'))->out(false),
                    'title' => get_string('addnewagreement', 'local_equipment'),
                    'class' => 'btn-success',
                    'gridclass' => 'col-md-6'
                ]
            ]
        ],
        // Inventory Management Section
        [
            'title' => get_string('inventorymanagement', 'local_equipment'),
            'items' => [
                [
                    'url' => (new \moodle_url('/local/equipment/inventory/manage.php'))->out(false),
                    'title' => get_string('inventorydashboard', 'local_equipment'),
                    'class' => 'btn-primary',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/inventory/products.php'))->out(false),
                    'title' => get_string('productcatalog', 'local_equipment'),
                    'class' => 'btn-info',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/inventory/locations.php'))->out(false),
                    'title' => get_string('locationmanagement', 'local_equipment'),
                    'class' => 'btn-info',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/inventory/add_items.php'))->out(false),
                    'title' => get_string('additemstoinventory', 'local_equipment'),
                    'class' => 'btn-success',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/inventory/remove_items.php'))->out(false),
                    'title' => get_string('removeitemsfromventory', 'local_equipment'),
                    'class' => 'btn-warning',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/inventory/check_inout.php'))->out(false),
                    'title' => get_string('equipmentcheckinout', 'local_equipment'),
                    'class' => 'btn-primary',
                    'gridclass' => 'col-md-3'
                ],
                // Needs to be removed from plugin
                // [
                //     'url' => (new \moodle_url('/local/equipment/inventory/checkin.php'))->out(false),
                //     'title' => get_string('equipmentcheckinonly', 'local_equipment'),
                //     'class' => 'btn-primary',
                //     'gridclass' => 'col-md-3'
                // ],
                // Needs to be removed from plugin
                // [
                //     'url' => (new \moodle_url('/local/equipment/inventory/scan.php'))->out(false),
                //     'title' => get_string('universalscanner', 'local_equipment'),
                //     'class' => 'btn-secondary',
                //     'gridclass' => 'col-md-3'
                // ],
                [
                    'url' => (new \moodle_url('/local/equipment/inventory/generate_qr.php'))->out(false),
                    'title' => get_string('generateqrcodes', 'local_equipment'),
                    'class' => 'btn-info',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/inventory/removal_history.php'))->out(false),
                    'title' => get_string('equipmentremovalhistory', 'local_equipment'),
                    'class' => 'btn-secondary',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/inventory/transactions.php'))->out(false),
                    'title' => get_string('transactionhistory', 'local_equipment'),
                    'class' => 'btn-secondary',
                    'gridclass' => 'col-md-3'
                ],
                // Needs to be removed from plugin
                // [
                //     'url' => (new \moodle_url('/local/equipment/inventory/item_details.php'))->out(false),
                //     'title' => get_string('itemdetails', 'local_equipment'),
                //     'class' => 'btn-secondary',
                //     'gridclass' => 'col-md-3'
                // ]
            ]
        ],
        // Phone Communication Section
        [
            'title' => get_string('phonecommunication', 'local_equipment'),
            'items' => [
                [
                    'url' => (new \moodle_url('/local/equipment/phonecommunication/verifyphone.php'))->out(false),
                    'title' => get_string('phoneverificationsetup', 'local_equipment'),
                    'class' => 'btn-primary',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/phonecommunication/verifyotp.php'))->out(false),
                    'title' => get_string('otpverification', 'local_equipment'),
                    'class' => 'btn-primary',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/phonecommunication/testoutgoingtextconf.php'))->out(false),
                    'title' => get_string('testoutgoingtextconfig', 'local_equipment'),
                    'class' => 'btn-warning',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/phonecommunication/verifytestotp.php'))->out(false),
                    'title' => get_string('testotpverification', 'local_equipment'),
                    'class' => 'btn-warning',
                    'gridclass' => 'col-md-3'
                ]
            ]
        ],
        // Virtual Course Consent Section
        [
            'title' => get_string('virtualcourseconsent', 'local_equipment'),
            'items' => [
                [
                    'url' => (new \moodle_url('/local/equipment/virtualcourseconsent/index.php'))->out(false),
                    'title' => get_string('consentform', 'local_equipment'),
                    'class' => 'btn-primary',
                    'gridclass' => 'col-md-4'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/virtualcourseconsent/select_exchange.php'))->out(false),
                    'title' => get_string('equipmentexchangeselection', 'local_equipment'),
                    'class' => 'btn-info',
                    'gridclass' => 'col-md-4'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/vccsubmissions.php'))->out(false),
                    'title' => get_string('viewconsentsubmissions', 'local_equipment'),
                    'class' => 'btn-secondary',
                    'gridclass' => 'col-md-4'
                ]
            ]
        ],
        // Mass Text Messaging Section
        [
            'title' => get_string('masstextmessaging', 'local_equipment'),
            'items' => [
                [
                    'url' => (new \moodle_url('/local/equipment/mass_text_message.php'))->out(false),
                    'title' => get_string('masstextmessageinterface', 'local_equipment'),
                    'class' => 'btn-primary',
                    'gridclass' => 'col-md-12'
                ]
            ]
        ],
        // Administrative Tools Section
        [
            'title' => get_string('administrativetools', 'local_equipment'),
            'items' => [
                [
                    'url' => (new \moodle_url('/admin/settings.php', ['section' => 'localsettingequipment']))->out(false),
                    'title' => get_string('equipmentpluginsettings', 'local_equipment'),
                    'class' => 'btn-secondary',
                    'gridclass' => 'col-md-12'
                ]
            ]
        ],
        // Development and Testing Tools Section
        [
            'title' => get_string('developmenttestingtools', 'local_equipment'),
            'items' => [
                [
                    'url' => (new \moodle_url('/local/equipment/test_inventory_basic.php'))->out(false),
                    'title' => get_string('basicinventorysystemtest', 'local_equipment'),
                    'class' => 'btn-warning',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/test_inventory_system.php'))->out(false),
                    'title' => get_string('comprehensiveinventorytest', 'local_equipment'),
                    'class' => 'btn-warning',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/debug_queue.php'))->out(false),
                    'title' => get_string('qrprintqueuedebugging', 'local_equipment'),
                    'class' => 'btn-danger',
                    'gridclass' => 'col-md-3'
                ],
                [
                    'url' => (new \moodle_url('/local/equipment/debug_queue_database.php'))->out(false),
                    'title' => get_string('databasequeuedebugging', 'local_equipment'),
                    'class' => 'btn-danger',
                    'gridclass' => 'col-md-3'
                ]
            ]
        ]
    ],
    'warnings' => [
        [
            'message' => get_string('developmenttoolswarning', 'local_equipment')
        ]
    ],
    'help' => [
        'title' => get_string('quickhelp', 'local_equipment'),
        'content' => get_string('dashboardhelp', 'local_equipment')
    ]
];

// Render the dashboard using the Mustache template
echo $OUTPUT->render_from_template('local_equipment/equipment_dashboard', $templatedata);

echo $OUTPUT->footer();
