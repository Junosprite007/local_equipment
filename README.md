# Equipment plugin for Moodle

## Introduction

*Equipment* is a local plugin (for now) that allows Moodle administrators to easily manage and track equipment flow within a classroom or for remote learners on a per-student basis, providing a user-friendly interface and robust functionality to streamline the equipment check-out/check-in/retention process.

## Features

- **Partnerships:** manage and track company-school/partner relationships.
- **Custom agreements:** create and edit learner-/parent-organiztion agreement/consent forms.
- **Parent form:** a form for parents to add one or many students in one or many course, select equipment pickup time and location, select partnership, verify phone, and agree to terms and conditions.
- **OTP phone verification:** improve equipment dispersal logistics and pickup/drop-off schedules with automated text notifications to users' verified phone numbers..
- **Checkout system:** set times and locations for equipment pickups and drop-offs.
- **Equipment inventory:** browse available equipment, assign equipment to users, track location and checkout history, and manage general inventory.

Future potential features:
- **Reporting:** Generate reports on equipment usage, availability, and user activity.

## Installation

1. Navigate to the `local` directory of your Moodle project:
   `cd /path/to/moodle/local`

2. Clone the plugin into the `local` directory:
   `git clone git@github.com:Junosprite007/local_equipment.git ./equipment`

3. Run the installation script (not yet applicable):
   `php equipment/db/install.php`

4. Upgrade Moodle:
`php /path/to/moodle/admin/upgrade.php`

## Usage

After installation, the *Equipment* plugin settings can be accessed from *Site administration > Plugins > Equipment*. Administrators should start by entering all necessary data, like inventory, agreements, partnerships, and liaisons.

## Contributing

Contributions to *Equipment* are welcome! Whether it's bug reports, feature requests, or code contributions, please feel free to reach out or submit a pull request.

## License

TBD

## Contact

For support or any questions:

- Email: josh@funlearningcompany.com
- Issue tracker: https://github.com/Junosprite007/local_equipment/issues

# Claude information
This plugin began with a proompt to Claude 3.5, which has since been removed. It was extremely helpful, you don't even know.

## Prooompt

[Here, I copied and pasted my company's Google Form virtual consent and equipment checkout form, which I've since removed.]

This current form above which is made with Google forms, requires a parent to fill out a single form per student. I don't want that I want to be able to fill out a single form for students they have regardless of how many courses each students are taking with Fun Learning Company (that's the name of my company). My custom installation of Moodle is also known as FLIP, so anytime I use the term "FLIP", I'm referring to my custom Moodle installation.

This plug-in should have administrator settings, where an admin can go in an ad all the partnerships that we currently have all of the courses for each of those partnerships and our current agreements which include: (1) "Course equipment checkout & retention terms & conditions", and (2) "Media agreement". These two agreements should be editable by system admins only, and once an agreement is set for the year, it cannot be edited in future years. Each school year should have a specified version of the agreements. Previous agreements should be saved within the database or managed with some form of version control.

Let me know if there are any other aspects that I need to explain to you.

## Response

Thank you for providing such a detailed description of your requirements for the "Equipment" plugin for Moodle. I understand you want to create a custom plugin that will handle the dispatch, retention, and return of equipment to students, parents, or school districts. Let's break down the key components and features you've mentioned:

Plugin Name: Equipment
Core Functionality:

Handle equipment dispatch, retention, and return
Allow parents to fill out a form through a secure, unique link

Form Improvements:

Single form for multiple students and courses per parent
Collect necessary information for equipment checkout

Administrator Settings:

Add/manage partnerships
Add/manage courses for each partnership
Manage agreements (Course equipment checkout & retention terms & conditions, Media agreement)
Version control for agreements per school year

User Interface:

Secure, unique link for parents
Form for parents to fill out

Database Requirements:

Store partnerships
Store courses
Store agreements and their versions
Store student and parent information
Store equipment checkout information

To start developing this plugin, we'll need to create the basic structure for a Moodle plugin. Here's an outline of the initial files and directories we'll need:
Copylocal_equipment/
├── db/
│ ├── install.xml
│ └── upgrade.php
├── lang/
│ └── en/
│ └── equipment.php
├── classes/
│ ├── form/
│ │ └── checkout_form.php
│ ├── output/
│ │ └── renderer.php
│ └── privacy/
│ └── provider.php
├── templates/
│ └── main.mustache
├── settings.php
├── lib.php
├── version.php
├── index.php
└── view.php
Let's start with the version.php file, which is required for all Moodle plugins:
phpCopy<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_equipment';
$plugin->version = 2024071700;
$plugin->requires = 2022112800; // Moodle 4.1 and up
$plugin->maturity = MATURITY_ALPHA;
$plugin->release = 'v0.1';
Next, let's create a basic lib.php file:
phpCopy<?php
defined('MOODLE_INTERNAL') || die();

function equipment_add_instance($data) {
global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;

    $id = $DB->insert_record('equipment', $data);

    return $id;

}

function equipment_update_instance($data) {
global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    $DB->update_record('equipment', $data);

    return true;

}

function equipment_delete_instance($id) {
global $DB;

    $DB->delete_records('equipment', array('id' => $id));

    return true;

}
Now, let's create a basic settings.php file to add administrator settings:
phpCopy<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
$settings->add(new admin_setting_configtext(
'local_equipment/partnerships',
get_string('partnerships', 'local_equipment'),
get_string('partnerships_desc', 'local_equipment'),
'',
PARAM_TEXT
));

    $settings->add(new admin_setting_configtextarea(
        'local_equipment/terms_conditions',
        get_string('terms_conditions', 'local_equipment'),
        get_string('terms_conditions_desc', 'local_equipment'),
        '',
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_equipment/media_agreement',
        get_string('media_agreement', 'local_equipment'),
        get_string('media_agreement_desc', 'local_equipment'),
        '',
        PARAM_RAW
    ));

}
This is just the beginning of the plugin development. We'll need to create more files and implement the full functionality you've described. Some key next steps would be:

Design the database schema for storing partnerships, courses, agreements, and checkout information.
Implement the form for parents to fill out.
Create the secure, unique link system for parents.
Develop the administrator interface for managing partnerships, courses, and agreements.
Implement version control for agreements.
Create the equipment checkout and return workflow.

Would you like me to elaborate on any specific part of the plugin development, or shall we move on to the next step?







form/consent_form = form/virtualcourseconsent_form
manage_consents = vccsubmissions
local/equipment/consent_form.php = local/equipment/virtualcourseconsent/index.php



id_0
id_1
id_2
id_3

i=0    id_0
i=1    id_1
i=2    id_3

i=0    id_0
i=1    id_1
i=2    id_2

