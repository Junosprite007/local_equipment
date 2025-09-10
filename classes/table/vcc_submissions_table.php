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

declare(strict_types=1);

namespace local_equipment\table;

use table_sql;
use moodle_url;
use stdClass;
use local_equipment\service\vcc_submission_service;

// Ensure Moodle core classes are properly imported
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/lib/weblib.php');
require_once($CFG->libdir . '/classes/url.php');

/**
 * Table for displaying VCC submissions with proper Moodle 5.0 conventions
 *
 * @package     local_equipment
 * @copyright   2025 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vcc_submissions_table extends table_sql {

    /** @var vcc_submission_service */
    private vcc_submission_service $vcc_service;

    /** @var stdClass */
    private stdClass $filters;

    /**
     * Constructor
     *
     * @param string $uniqueid Unique identifier for this table
     * @param vcc_submission_service $vcc_service Service for VCC operations
     */
    public function __construct(string $uniqueid, vcc_submission_service $vcc_service) {
        parent::__construct($uniqueid);

        $this->vcc_service = $vcc_service;
        $this->filters = new stdClass();

        $this->setup_table();
    }

    /**
     * Set up table configuration
     */
    private function setup_table(): void {
        // Define columns with proper type safety - ALL VCC submission fields + exchange fields for admin visibility
        $columns = [
            'timecreated',
            'firstname',
            'lastname',
            'email',
            'phone',
            'partnership_name',
            'students_info',
            'mailing_address',
            'billing_address',
            'pickup_info',
            'exchange_partnership',
            'exchange_timeframe',
            'exchange_pickup_method',
            'exchange_pickup_person',
            'exchange_pickup_phone',
            'exchange_pickup_details',
            'exchange_address_details',
            'electronicsignature',
            'confirmationid',
            'usernotes',
            'adminnotes',
            'status_info',
            'actions'
        ];

        $headers = [
            get_string('timecreated'),
            get_string('firstname'),
            get_string('lastname'),
            get_string('email'),
            get_string('phone', 'local_equipment'),
            get_string('partnership', 'local_equipment'),
            get_string('students', 'local_equipment'),
            get_string('mailingaddress', 'local_equipment'),
            get_string('billingaddress', 'local_equipment'),
            get_string('pickup', 'local_equipment'),
            get_string('exchangepartnership', 'local_equipment'),
            get_string('exchangetimeframe', 'local_equipment'),
            get_string('exchangepickupmethod', 'local_equipment'),
            get_string('exchangepickupperson', 'local_equipment'),
            get_string('exchangepickupphone', 'local_equipment'),
            get_string('exchangepickupdetails', 'local_equipment'),
            get_string('exchangeaddressdetails', 'local_equipment'),
            get_string('electronicsignature', 'local_equipment'),
            get_string('confirmationid', 'local_equipment'),
            get_string('usernotes', 'local_equipment'),
            get_string('adminnotes', 'local_equipment'),
            get_string('status', 'local_equipment'),
            get_string('actions', 'local_equipment')
        ];

        $this->define_columns($columns);
        $this->define_headers($headers);

        // Configure table behavior
        $this->sortable(true, 'timecreated', SORT_DESC);
        $this->no_sorting('students_info');
        $this->no_sorting('mailing_address');
        $this->no_sorting('billing_address');
        $this->no_sorting('pickup_info');
        $this->no_sorting('exchange_pickup_details');
        $this->no_sorting('exchange_address_details');
        $this->no_sorting('usernotes');
        $this->no_sorting('adminnotes');
        $this->no_sorting('status_info');
        $this->no_sorting('actions');

        $this->collapsible(false);
        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);

        // Set Bootstrap 5 compatible classes
        $this->set_attribute('class', 'table table-striped table-hover vcc-submissions-table');
        $this->set_attribute('id', 'vccsubmissions');

        // Column styling with Bootstrap 5
        $this->column_class('timecreated', 'text-nowrap');
        $this->column_class('partnership_name', 'text-nowrap');
        $this->column_class('exchange_partnership', 'text-nowrap');
        $this->column_class('exchange_timeframe', 'text-nowrap');
        $this->column_class('exchange_pickup_method', 'text-nowrap');
        $this->column_class('exchange_pickup_person', 'text-nowrap');
        $this->column_class('exchange_pickup_phone', 'text-nowrap');
        $this->column_class('actions', 'text-nowrap text-center');
        $this->column_class('status_info', 'text-center');
    }

    /**
     * Set filters for the table
     *
     * @param stdClass $filters Filter data object
     */
    public function set_filters(stdClass $filters): void {
        $this->filters = $filters;

        // Build SQL with filters
        [$select, $from, $where, $params] = $this->vcc_service->build_table_sql($filters);
        $this->set_sql($select, $from, $where, $params);
    }

    /**
     * Format the timecreated column using proper Moodle 5.0 humandate classes
     */
    public function col_timecreated(stdClass $row): string {
        global $OUTPUT;

        // Use Moodle 5.0 humandate renderer for better date display
        $humandate = \core_calendar\output\humandate::create_from_timestamp((int)$row->timecreated);
        return $OUTPUT->render($humandate);
    }

    /**
     * Format the phone column with null safety
     */
    public function col_phone(stdClass $row): string {
        return $row->phone ?? ($row->u_phone2 ?? ($row->u_phone1 ?? '-'));
    }

    /**
     * Format the partnership name column
     */
    public function col_partnership_name(stdClass $row): string {
        return $row->partnership_name ?? ($row->p_name ?? '-');
    }

    /**
     * Format the students info column using template
     */
    public function col_students_info(stdClass $row): string {
        global $OUTPUT;

        $students_data = $this->vcc_service->get_students_display_data($row);

        return $OUTPUT->render_from_template('local_equipment/vcc_students_cell', [
            'students' => $students_data
        ]);
    }

    /**
     * Format the mailing address column
     */
    public function col_mailing_address(stdClass $row): string {
        $address_parts = array_filter([
            $row->mailing_streetaddress ?? '',
            $row->mailing_apartment ? get_string('apt', 'local_equipment') . ' ' . $row->mailing_apartment : '',
            $row->mailing_city ?? '',
            $row->mailing_state ?? '',
            $row->mailing_zipcode ?? ''
        ]);
        return empty($address_parts) ? '-' : implode(', ', $address_parts);
    }

    /**
     * Format the billing address column
     */
    public function col_billing_address(stdClass $row): string {
        if ($row->billing_sameasmailing) {
            return get_string('sameasmailing', 'local_equipment');
        }

        $address_parts = array_filter([
            $row->billing_streetaddress ?? '',
            $row->billing_apartment ? get_string('apt', 'local_equipment') . ' ' . $row->billing_apartment : '',
            $row->billing_city ?? '',
            $row->billing_state ?? '',
            $row->billing_zipcode ?? ''
        ]);
        return empty($address_parts) ? '-' : implode(', ', $address_parts);
    }

    /**
     * Format the electronic signature column
     */
    public function col_electronicsignature(stdClass $row): string {
        return !empty($row->electronicsignature) ? s($row->electronicsignature) : '-';
    }

    /**
     * Format the confirmation ID column
     */
    public function col_confirmationid(stdClass $row): string {
        return !empty($row->confirmationid) ? s($row->confirmationid) : '-';
    }

    /**
     * Format the user notes column
     */
    public function col_usernotes(stdClass $row): string {
        if (empty($row->usernotes)) {
            return '-';
        }
        $notes = s($row->usernotes);
        return strlen($notes) > 50 ? substr($notes, 0, 50) . '...' : $notes;
    }

    /**
     * Format the admin notes column
     */
    public function col_adminnotes(stdClass $row): string {
        if (empty($row->adminnotes)) {
            return '-';
        }
        $notes = s($row->adminnotes);
        return strlen($notes) > 50 ? substr($notes, 0, 50) . '...' : $notes;
    }

    /**
     * Format the pickup info column using template
     */
    public function col_pickup_info(stdClass $row): string {
        global $OUTPUT;

        $pickup_data = $this->vcc_service->get_pickup_display_data($row);

        return $OUTPUT->render_from_template('local_equipment/vcc_pickup_cell', $pickup_data);
    }

    /**
     * Format the status info column using template with proper Bootstrap 5 badges
     */
    public function col_status_info(stdClass $row): string {
        global $OUTPUT;

        $status_data = [
            'email_confirmed' => (bool)$row->email_confirmed,
            'phone_confirmed' => (bool)$row->phone_confirmed,
            'is_expired' => (bool)$row->confirmationexpired
        ];

        return $OUTPUT->render_from_template('local_equipment/vcc_status_cell', $status_data);
    }

    /**
     * Format the exchange partnership column with fallback to VCC data
     */
    public function col_exchange_partnership(stdClass $row): string {
        // Priority: exchange submission -> VCC submission partnership
        return $row->exchange_partnership_name ?? ($row->partnership_name ?? ($row->p_name ?? '-'));
    }

    /**
     * Format the exchange timeframe column with fallback
     */
    public function col_exchange_timeframe(stdClass $row): string {
        // Format exchange timeframe from pickup schedule data
        if (!empty($row->exchange_pickup_date)) {
            $date_str = userdate($row->exchange_pickup_date, get_string('strftimedate', 'core_langconfig'));

            $time_parts = [];
            if (!empty($row->exchange_pickup_starttime)) {
                $time_parts[] = userdate($row->exchange_pickup_starttime, get_string('strftimetime12', 'core_langconfig'));
            }
            if (!empty($row->exchange_pickup_endtime)) {
                $time_parts[] = userdate($row->exchange_pickup_endtime, get_string('strftimetime12', 'core_langconfig'));
            }

            $time_str = empty($time_parts) ? '' : implode(' - ', $time_parts);
            return trim($date_str . ($time_str ? ' at ' . $time_str : ''));
        }

        // Fallback to VCC pickup timeframe if available
        return !empty($row->pickup_locationtime) ? s($row->pickup_locationtime) : '-';
    }

    /**
     * Format the exchange pickup method column with fallback
     */
    public function col_exchange_pickup_method(stdClass $row): string {
        // Priority: exchange submission -> VCC submission pickup method
        $method = $row->exchange_pickup_method ?? ($row->pickupmethod ?? null);

        if (!empty($method)) {
            // Check if the string exists, fallback to raw value if not
            if (get_string_manager()->string_exists('pickupmethod_' . $method, 'local_equipment')) {
                return get_string('pickupmethod_' . $method, 'local_equipment');
            }
            return s($method);
        }

        return '-';
    }

    /**
     * Format the exchange pickup person column with fallback
     */
    public function col_exchange_pickup_person(stdClass $row): string {
        // Priority: exchange submission -> VCC submission pickup person
        $name = $row->exchange_pickup_person_name ?? ($row->pickuppersonname ?? null);
        return !empty($name) ? s($name) : '-';
    }

    /**
     * Format the exchange pickup phone column with fallback
     */
    public function col_exchange_pickup_phone(stdClass $row): string {
        // Priority: exchange submission -> VCC submission pickup phone
        $phone = $row->exchange_pickup_person_phone ?? ($row->pickuppersonphone ?? null);
        return !empty($phone) ? s($phone) : '-';
    }

    /**
     * Format the exchange pickup details column with fallback
     */
    public function col_exchange_pickup_details(stdClass $row): string {
        // Priority: exchange submission -> VCC submission pickup details
        $details = $row->exchange_pickup_person_details ?? ($row->pickuppersondetails ?? null);

        if (!empty($details)) {
            $formatted_details = s($details);
            return strlen($formatted_details) > 50 ? substr($formatted_details, 0, 50) . '...' : $formatted_details;
        }

        return '-';
    }

    /**
     * Format the exchange address details column (partnership address info)
     */
    public function col_exchange_address_details(stdClass $row): string {
        // Priority: exchange partnership address -> regular partnership address
        $address = $row->exchange_partnership_pickup_address ??
            ($row->exchange_partnership_address ??
                ($row->exchange_partnership_mailing_address ?? ''));

        if (empty($address) && !empty($row->p_name)) {
            // Fallback to constructing address from VCC partnership data if available
            $address_parts = array_filter([
                $row->mailing_streetaddress ?? '',
                $row->mailing_apartment ? get_string('apt', 'local_equipment') . ' ' . $row->mailing_apartment : '',
                $row->mailing_city ?? '',
                $row->mailing_state ?? '',
                $row->mailing_zipcode ?? ''
            ]);
            $address = implode(', ', $address_parts);
        }

        if (!empty($address)) {
            $trimmed_address = trim($address);
            return strlen($trimmed_address) > 60 ? substr($trimmed_address, 0, 60) . '...' : $trimmed_address;
        }

        return '-';
    }

    /**
     * Format the actions column with proper Bootstrap 5 button groups
     */
    public function col_actions(stdClass $row): string {
        global $OUTPUT;

        $actions_data = [
            'edit_url' => (new moodle_url(
                '/local/equipment/vccsubmissions/editvccsubmission.php',
                ['id' => $row->id]
            ))->out(false),
            'delete_url' => (new moodle_url('/local/equipment/vccsubmissions.php', [
                'delete' => $row->id,
                'sesskey' => sesskey()
            ]))->out(false),
            'confirm_delete' => get_string('confirmdeletevccsubmission', 'local_equipment')
        ];

        return $OUTPUT->render_from_template('local_equipment/vcc_actions_cell', $actions_data);
    }


    /**
     * Get mailing address text for export
     */
    private function get_mailing_address_text_for_export(stdClass $record): string {
        $address_parts = array_filter([
            $record->mailing_streetaddress ?? '',
            $record->mailing_apartment ?? '',
            $record->mailing_city ?? '',
            $record->mailing_state ?? '',
            $record->mailing_zipcode ?? ''
        ]);
        return empty($address_parts) ? '-' : implode(', ', $address_parts);
    }

    /**
     * Get billing address text for export
     */
    private function get_billing_address_text_for_export(stdClass $record): string {
        if ($record->billing_sameasmailing) {
            return 'Same as mailing';
        }

        $address_parts = array_filter([
            $record->billing_streetaddress ?? '',
            $record->billing_apartment ?? '',
            $record->billing_city ?? '',
            $record->billing_state ?? '',
            $record->billing_zipcode ?? ''
        ]);
        return empty($address_parts) ? '-' : implode(', ', $address_parts);
    }

    /**
     * Get status text for export
     */
    private function get_status_text_for_export(stdClass $record): string {
        $status_parts = [];

        $status_parts[] = $record->email_confirmed ? 'Email✓' : 'Email✗';
        $status_parts[] = $record->phone_confirmed ? 'Phone✓' : 'Phone✗';

        if ($record->confirmationexpired) {
            $status_parts[] = 'Expired';
        }

        return implode(' ', $status_parts);
    }
}
