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
 * Language strings for US states in the Equipment plugin.
 *
 * @package     local_equipment
 * @category    string
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/equipment/lang/en/countries.php');
require_once($CFG->dirroot . '/local/equipment/lang/en/states.php');

$string['actions'] = 'Actions';
$string['active'] = 'Active';
$string['addmorepartnership'] = 'Add {$a} more partnership(s)';
$string['addmorepartnerships'] = 'Add more partnerships';
$string['addpartnerships'] = 'Add partnerships';
$string['addpartnershipshere'] = 'Add partnerships here.';
$string['partnershipcourserelationshipnotadded'] = 'Course ID "{$a->courseid}" was not added to the partnership "{$a->partnershipname}".';
$string['address'] = 'Address';
$string['addressee'] = 'Addressee';
$string['agreements'] = 'Agreements';
$string['agreements_desc'] = 'List of agreements that users must accept in order to use this equipment checkout service.';
$string['and'] = 'and';
$string['apartment'] = 'Apartment';
$string['billing_apartment'] = 'Apartment';
$string['mailing_apartment'] = 'Apartment';
$string['physical_apartment'] = 'Apartment';
$string['pickup_apartment'] = 'Apartment';
$string['attention'] = 'Attention';
$string['billing'] = 'Billing';
$string['billing_address'] = 'Billing address';
$string['changessaved'] = 'Changes saved';
$string['city'] = 'City';
$string['billing_city'] = 'City (billing)';
$string['mailing_city'] = 'City';
$string['physical_city'] = 'City (physical)';
$string['pickup_city'] = 'City (pickup)';
$string['confirmdelete'] = 'Confirm delete';
$string['consentform'] = 'Consent form';
$string['contactusforpickup'] = 'Contact us for picking up equipment';
$string['country'] = 'Country';
$string['billing_country'] = 'Country (billing)';
$string['billingaddress'] = 'Billing address';
$string['mailing_country'] = 'Country';
$string['mailingaddress'] = 'Mailing address';
$string['phonedoesnotexist'] = 'Phone number does not exist.';
$string['physical_country'] = 'Country (physical)';
$string['physicaladdress'] = 'Physical address';
$string['pickup_country'] = 'Country (pickup)';
$string['pickupaddress'] = 'Pickup address';
$string['courseid'] = 'Course ID';
$string['courseids'] = 'Course IDs';
$string['coursename'] = 'Course name';
$string['courses'] = 'Courses';
$string['courseequipment'] = 'Courses equipment';
$string['createcategoryhere'] = 'Create a category here.';
$string['createcoursehere'] = 'Create a course here.';
$string['deletepartnership'] = 'Delete partnership';
$string['dropoffdateafterwarning'] = 'Drop-off date must be after pickup date';
$string['editpartnership'] = 'Edit partnership';
$string['editpartnerships'] = 'Edit partnerships';
$string['endtime'] = 'End time';
$string['equipment'] = 'Equipment';
$string['equipmentmanagement'] = 'Equipment management';
$string['equipmentsettings'] = 'Equipment settings';
$string['addenroll'] = 'Add & enroll';
$string['families'] = 'Families';
$string['exchanges'] = 'Exchanges';
$string['equipment:managepartnerships'] = 'Allows user to manage partnerships';
$string['equipment:seedetails'] = 'See equipment plugin menus and details';
$string['equipmentcheckout'] = 'Equipment checkout';
$string['erroraddingpartnerships'] = 'Error adding partnerships';
$string['errordeletingpartnership'] = 'Error deleting partnerships';
$string['hour'] = 'Hour';
$string['ifdifferentfrommailing'] = '(if different from mailing)';
$string['ifdifferentfromphysical'] = '(if different from physical)';
$string['extrainstructions'] = 'Extra instructions';
$string['pickup_instructions'] = 'Pickup instructions';
$string['invalidliaisonid'] = 'Invalid liaison ID';
$string['invalidphonenumber'] = 'Invalid phone number: {$a}.';
$string['invalidphonenumberformat'] = 'Invalid phone number format.';
$string['invalidpickupid'] = 'Invalid pickup ID';
$string['invalidusphonenumber'] = 'Invalid U.S. phone number.';
$string['liaisonid'] = 'Liaison ID';
$string['liaisonids'] = 'Liaison IDs';
$string['liaisonname'] = 'Liaison name';
$string['liaisons'] = 'Liaisons';
$string['mailing'] = 'Mailing';
$string['mailing_address'] = 'Mailing address';
$string['mailing_extrainstructions'] = 'Mailing extra instructions';
$string['manage'] = 'Manage';
$string['manageagreements'] = 'Manage agreements';
$string['managecourseequipment'] = 'Manage course equipment';
$string['manageequipment'] = 'Manage equipment';
$string['managekitpickuptimes'] = 'Manage kit pickup times';
$string['managepartnerships'] = 'Manage partnerships';
$string['maxlength'] = 'Max length is {$a} characters.';
$string['mediaagreement'] = 'Media agreement';
$string['mediaagreement_desc'] = 'Description of the media agreement.';
$string['minute'] = 'Minute';
$string['mustbezipcodeformat'] = 'Must be in ZIP code format, e.g. 12345 or 12345-6789.';
$string['name'] = 'Name';
$string['billing_name'] = 'Name (Billing)';
$string['nocategoryfound'] = 'No category found with the name "{$a}".';
$string['nocoursesavailable'] = 'No courses available';
$string['nocoursesfoundforpartnershipwithid'] = 'No courses found where the partnership "idname" field is "{$a}".';
$string['nocoursesfoundincategory'] = 'No courses found in category "{$a}".';
$string['notasupportedcountry'] = '{$a} is not a supported country.';
$string['partnership'] = 'Partnership';
$string['partnershipcourses'] = 'Partnership courses';
$string['partnershipdeleted'] = 'Partnership deleted';
$string['partnershipdescription'] = 'Partnership description';
$string['partnershipliaisons'] = 'Partnership liaisons';
$string['partnershipname'] = 'Partnership name';
$string['partnerships'] = 'Partnerships';
$string['partnerships_desc'] = 'List any partnerships or agreements that are relevant to this equipment checkout service.';
$string['partnershipsadded'] = 'Partnerships added successfully.';
$string['partnershipsettings'] = 'Partnership settings';
$string['partnershipsettings_desc'] = 'Settings related to the Partnership sub-feature of the Equipment plugin.';
$string['partnershipupdated'] = 'Partnership updated';
$string['physical'] = 'Physical';
$string['physical_address'] = 'Physical address';
$string['pickup'] = 'Pickup';
$string['pickup_address'] = 'Pickup address';
$string['pickup_extrainstructions'] = 'Extra pickup instructions';
$string['pickupdate'] = 'Pickup date';
$string['pickupid'] = 'Pickup ID';
$string['pickupinstructions'] = 'Pickup instructions';
$string['pickuplocation'] = 'Pickup location';
$string['pickups'] = 'Pickups';
$string['pickupsadded'] = 'Pickups added successfully';
$string['pluginadministration'] = 'Equipment checkout administration';
$string['pluginname'] = 'Equipment';
$string['removepartnership'] = 'Remove partnership';
$string['returntoaddpartnerships'] = 'addpartnerships';
$string['roomareainstruction'] = 'Room/area instructions';
$string['sameasmailing'] = 'Same as mailing address';
$string['sameasphysical'] = 'Same as physical address';
$string['savechanges'] = 'Save changes';
$string['selectcountry'] = 'Select country';
$string['selectcourses'] = 'Select courses';
$string['selectliaisons'] = 'Select liaisons';
$string['selectpartnership'] = 'Select partnership';
$string['selectpickuplocation'] = 'Select pickup location';
$string['selectpickuplocationtime'] = 'Select pickup location/time';
$string['selectstate'] = 'Select state';
$string['starttime'] = 'Start time';
$string['state'] = 'State';
$string['billing_state'] = 'State (Billing)';
$string['mailing_state'] = 'State';
$string['physical_state'] = 'State (Physical)';
$string['pickup_state'] = 'State (Pickup)';
$string['seeerrorsbelow'] = 'See errors below.';
$string['status_cancelled'] = 'Cancelled';
$string['status_completed'] = 'Completed';
$string['status_confirmed'] = 'Confirmed';
$string['status_pending'] = 'Pending';
$string['streetaddress'] = 'Street address';
$string['billing_streetaddress'] = 'Street Address (Billing)';
$string['mailing_streetaddress'] = 'Street Address';
$string['physical_streetaddress'] = 'Street Address (Physical)';
$string['pickup_streetaddress'] = 'Street Address (Pickup)';
$string['termsconditions'] = 'Terms & conditions';
$string['termsconditions_desc'] = 'Terms and conditions for equipment checkout.';
$string['timecreated'] = 'Time created';
$string['viewformsubmissions'] = 'View form submissions';
$string['viewmanagepartnerships'] = 'View/manage partnerships';
$string['viewpartnerships'] = 'View partnerships';
$string['wecurrentlyonlyacceptusphonenumbers'] = 'We currently only accept U.S. phone numbers. You can use most standard ways of typing a phone number like 2345678910 or +1 (234) 567-8910.';
$string['zipcode'] = 'ZIP Code';
$string['billing_zipcode'] = 'Zip Code (billing)';
$string['mailing_zipcode'] = 'Zip Code';
$string['physical_zipcode'] = 'Zip Code (physical)';
$string['pickup_zipcode'] = 'Zip Code (pickup)';

// Address-specific strings.
$string['billing_address'] = 'Billing address';
$string['mailing_address'] = 'Mailing address';
$string['physical_address'] = 'Physical address';
$string['pickup_address'] = 'Pickup address';
$string['sameasbilling'] = 'Same as billing address';
$string['sameasmailing'] = 'Same as mailing address';
$string['sameasphysical'] = 'Same as physical address';
$string['sameaspickup'] = 'Same as pickup address';
$string['streetaddress'] = 'Street address';
$string['apartment'] = 'Apartment';
$string['city'] = 'City';
$string['state'] = 'State';
$string['country'] = 'Country';
$string['zipcode'] = 'ZIP code';
$string['attention'] = 'Attention';
$string['extrainstructions'] = 'Extra {$a} instructions';

// Partnership-specific strings.
$string['coursesthisyear'] = 'Courses this year';
$string['errorparsingcoursesdata'] = 'Error parsing courses data';
$string['listingfrom'] = 'Listing from: {$a}';
$string['partnershipcourselist'] = 'Partnership course list';
$string['selectpartnershipforlisting'] = 'Select a partnership for listing';
$string['schoolyearrangetoautoselect_start'] = 'Starting school year range to auto-select';
$string['schoolyearrangetoautoselect_start_desc'] = 'The starting school year to use for displaying courses for a given partnership to select from.';
$string['schoolyearrangetoautoselect_end'] = 'Ending school year range to auto-select';
$string['schoolyearrangetoautoselect_end_desc'] = 'The ending school year to use for displaying courses for a given partnership to select from.';
$string['schoolyearrangetoautoselect_appendingdesc'] = 'This setting will alway default to the currect school year, which is probably what you want, so keep this setting empty to default to whatever this school year is, unless you want to define a specific school year';
$string['partnershipcategoryprefix'] = 'Partnership category prefix';
$string['partnershipcategoryprefix_desc'] = 'The prefix keyword used for defining the "Category ID number" setting for a given Partnership course category.';
$string['nopartnershipcategoriesfoundforschoolyear'] = 'No partnership categories found for the school year with \'idnumber\' "partnership#[id]_{$a}".';
$string['nocoursesfoundforthispartnership'] = 'No courses found for this partnership.';
$string['totalcourses'] = 'Total courses';
$string['collapsepartnership'] = 'Toggle partnership courses';
$string['expandall'] = 'Expand all partnerships';
$string['collapseall'] = 'Collapse all partnerships';
$string['partnershipfielddesc'] = 'The partnership this user belongs to';
$string['searchpartnerships'] = 'Search partnerships...';
$string['nopartnershipselected'] = 'No partnership selected';
$string['nopartnershipsfound'] = 'No partnership found';
$string['privacy:metadata:user_info_data'] = 'User partnership data';
$string['privacy:metadata:userid'] = 'User ID';
$string['privacy:metadata:fieldid'] = 'Field ID';
$string['privacy:metadata:data'] = 'Partnership data';


// Pickups pages strings
$string['endedpickupstoshow'] = 'Ended pickups to show';
$string['endedpickupstoshow_desc'] = 'The amount of days old that an end time can be for a specific pickup day to be shown in the list of pickups. Examples: "7" means any pickups that ended more than 7 days ago will not be shown in the list of pickups. "0" means pickups disappear as soon as they end. Use "-1" to show all pickups, regardless of end time.';

$string['addpickup'] = 'Add pickup';
$string['addpickups'] = 'Add pickups';
$string['addmorepickups'] = 'Add more pickups';
$string['confirmdeletepickup'] = 'Are you sure you want to delete this pickup?';
$string['deletepickup'] = 'Delete pickup';
$string['editpickup'] = 'Edit pickup';
$string['erroraddingpickups'] = 'Error adding pickups';
$string['errordeletingpickup'] = 'Error deleting pickup';
$string['flccoordinator'] = 'FLC coordinator';
$string['nocoordinatoradded'] = 'No coordinator added';
$string['partnershipcoordinator'] = 'Partnership coordinator';
$string['pickupdeleted'] = 'Pickup deleted';
$string['pickupendtime'] = 'Pickup end time';
$string['pickups'] = 'Pickups';
$string['pickupsadded'] = 'Pickups added';
$string['pickupsettings'] = 'Pickups settings';
$string['pickupsettings_desc'] = 'Settings related to the Pickups sub-feature of the Equipment plugin.';
$string['pickupstarttime'] = 'Pickup start time';
$string['selectflccoordinator'] = 'Select FLC coordinator';
$string['selectpartnershipcoordinator'] = 'Select partnership coordinator';
$string['status'] = 'Status';
$string['viewmanagepickups'] = 'View/manage pickups';
$string['removepickup'] = 'Remove pickup';

// Agreements pages strings
$string['activeendtime'] = 'Active end time';
$string['activestarttime'] = 'Active start time';
$string['addagreement'] = 'Add agreement';
$string['addagreements'] = 'Add agreements';
$string['agreementadded'] = 'Agreement added successfully';
$string['agreementcontent'] = 'Agreement content';
$string['agreementdeleted'] = 'Agreement deleted successfully';
$string['agreementtitle'] = 'Agreement title';
$string['agreementtype'] = 'Agreement type';
$string['agreementtype_informational'] = 'Informational';
$string['agreementtype_optinout'] = 'Opt-in/Opt-out';
$string['agreementupdated'] = 'Agreement updated successfully';
$string['confirmdeletedialog'] = 'Are you sure you want to delete this agreement?';
$string['content'] = 'Content';
$string['currentversion'] = 'Current version';
$string['activeenddatewillalsoneedtobeupdated'] = 'The active end date will also need to be updated.';
$string['editagreement'] = 'Edit agreement';
$string['enddate'] = 'End date';
$string['enddateafterstart'] = 'End date must be after start date';
$string['ended'] = 'Ended';
$string['manageagreements'] = 'Manage agreements';
$string['notstarted'] = 'Not started';
$string['requireelectronicsignature'] = 'Require electronic signature';
$string['requiresignature'] = 'Require signature';
$string['startdate'] = 'Start date';
$string['startdatecannotbeinthepast'] = 'Start date cannot be in the past.';
$string['startdatewillneedtobeupdated'] = 'The start date will need to be updated to the current date or later because the original start date is now in the past.';
$string['title'] = 'Title';
$string['type'] = 'Type';
$string['unknown'] = 'Unknown';
$string['viewmanageagreements'] = 'View/manage agreements';
$string['version'] = 'Version';
$string['wheneditinganexistingagreement'] = 'When editing an existing agreement, the start time must be moved to the current day or later.';

// Virtual course consent form strings
$string['a'] = '{$a}';
$string['addnote'] = 'Add note';
$string['addstudent'] = 'Add student';
$string['addvccsubmission'] = 'Add VCC Submission';
$string['admin_notes'] = 'Admin notes';
$string['adminnotes'] = 'Admin notes';
$string['apt'] = 'Apt.';
$string['attnparents_useyouraccount'] = 'ATTENTION, PARENTS! You must be logged into your own, personal {$a->site} account to fill out the {$a->form} form.<br /><br />
Need help? {$a->help}.';
$string['vccformredirect_notaparent'] = 'VCC form non-parent redirect';
$string['vccformredirect_notaparent_desc'] = 'The warning text that non-parent account will see after being redirected to their home page. You can include links to help docs and things like that here.';
$string['vccformredirect_isguestuser'] = 'VCC form guest user redirect';
$string['vccformredirect_isguestuser_desc'] = 'The warning text that guest user accounts will see after being redirected to their home page. You can include links to help docs and things like that here.';
$string['currentlyloggedinassiteadmin'] = 'Please note that you are currently logged in as a site administrator. You can still fill out the form if you\'d like, though.';

$string['chooseoption'] = 'Choose an option';
$string['confirmationid'] = 'Confirmation ID';
$string['confirmdeletevccsubmission'] = 'Are you sure you want to delete this VCC submission?';
$string['consentformheading'] = 'Parent Consent for Virtual Courses';
$string['consentformsubmitted'] = 'Consent form submitted successfully';
$string['consentformtitle'] = 'Virtual Course Consent Form';
$string['dateofbirth'] = 'Date of birth';
$string['deletestudent'] = 'Delete student';
$string['editvccsubmission'] = 'Edit VCC submission';
$string['errorsubmittingform'] = 'Error submitting the Virtual Course Consent form';
$string['electronicsignature'] = 'Electronic signature';
$string['formdidnotsubmit'] = 'Form was not submitted! Please make sure you have filled out all required fields, then try submitting again.';
$string['formsubmitted'] = 'Your {$a} form has been submitted successfully!';
$string['howeverwecouldnotsendacodetoyourphoneforverification'] = 'However, we could not send a code to your phone for verification for the following reason: {$a}';
$string['id'] = 'ID';
$string['inputmismatch'] = 'Input mismatch for {$a}. Make sure you\'re logged into the correct account or contact a system administrator for further assistance.';
$string['invalidpartnership'] = 'Invalid partnership selected';
$string['invalidpickuptime'] = 'Invalid pickup time selected';
$string['needsatleastonecourseselected'] = '{$a} needs at least one course selected.';
$string['needstobeatleastsixmonthsold'] = '{$a} needs to be at least 6 months old.';
$string['manageconsentforms'] = 'Manage consent forms';
$string['managevccsubmissions'] = 'Manage VCC Submissions';
$string['musthaveaphoneonrecord'] = 'Must have a phone number on record.';
$string['mustlogintoyourownaccount'] = 'You cannot fill out the {$a->form} form unless you are logged into your own, personal {$a->site} account.';
$string['optin'] = 'Opt in!';
$string['optout'] = 'Opt out.';
$string['usernotes'] = 'Additional notes';
$string['parent'] = 'Parent';
$string['u_email'] = 'Parent email';
$string['u_firstname'] = 'Parent first name';
$string['u_name'] = 'Parent name';
$string['u_notes'] = 'Parent notes';
$string['u_lastname'] = 'Parent last name';
$string['u_phone2'] = 'User mobile phone';
$string['parent_mailing_address'] = 'Parent mailing address';
$string['parent_mailing_apartment'] = 'Parent apartment';
$string['parent_mailing_city'] = 'Parent city';
$string['parent_mailing_extrainstructions'] = 'Parent extra instructions';
$string['parent_mailing_state'] = 'Parent state';
$string['parent_mailing_streetaddress'] = 'Parent street address';
$string['parent_mailing_zipcode'] = 'Parent ZIP code';
$string['parent_phone'] = 'Parent phone';
$string['partnershipheader'] = 'Partnership: {$a}';
$string['partnership_name'] = 'Partnership name';
$string['pickup_details'] = 'Pickup details';
$string['pickup_info'] = 'Pickup info';
$string['pickup_method'] = 'Pickup method';
$string['pickup_notes'] = 'Pickup notes';
$string['pickup_person'] = 'Pickup person';
$string['pickup_personname'] = 'Pickup person name';
$string['pickup_personphone'] = 'Pickup person phone';
$string['pickuplocationtime'] = 'Pickup location & time';
$string['exchangelocation'] = 'Exchange location';
$string['pickupdatetime'] = 'Pickup date & time';
$string['haveuscontactyou'] = 'Have us contact you with pickup/drop-off timeframes';
$string['exchangelocationnotice'] = 'Please note: If specific pickup times are not available for your selected location, we will contact you with available timeframes.';
$string['pickupmethod'] = 'Equipment pickup method';
$string['pickupother'] = 'Someone else will pick up all equipment on my behalf';
$string['pickuppersondetails'] = 'Additional details for pickup';
$string['pickuppersonname'] = 'Name of person picking up equipment';
$string['pickuppersonphone'] = 'Phone number of person picking up equipment';
$string['pickuppurchased'] = 'I\'ve already purchased all necessary equipment';
$string['pickupself'] = 'I will pick up the equipment';
$string['pickupship'] = 'I\'ll pay FLC to ship equipment to my address';
$string['pickuptime'] = 'Equipment pickup time';
$string['phone'] = 'Phone';
$string['pleaseoptinoroutoftheagreement'] = 'Please opt in or out of the {$a}.';
$string['savenote'] = 'Save note';
$string['signature'] = 'Signature';
$string['signaturemismatch'] = 'Electronic signature does not match your full name';
$string['thesignaturewasnotsaved'] = 'The signature was not saved, though we know a parent did sign the form upon submission; we just don\'t know which parent signed.';
$string['signaturewarning'] = 'By typing your full name in the box below, you are electronically signing this form and agreeing to all the terms listed above.';
$string['student'] = 'Student';
$string['studentemailgenerated'] = 'Student email generated automatically';
$string['studentheader'] = 'Student: {$a}';
$string['students'] = 'Students';
$string['sub_timecreated'] = 'Time created';
$string['timelastmodified'] = 'Time last modified';
$string['toeditprofile'] = 'To edit your email or name, go to {$a}.';
$string['user'] = 'User';
$string['vccsubmissiondeleted'] = 'VCC submission deleted successfully';
$string['vccsubmissionupdated'] = 'VCC submission updated successfully';
$string['viewnotes'] = 'View notes';
$string['virtualcourseconsent'] = 'Virtual Course Consent (VCC)';
$string['virtualcourseconsentsubmission'] = 'Virtual course consent submission';
$string['virtualcourseconsentsubmissions'] = 'Virtual course consent submissions';
$string['youmustselectapartnership'] = 'You must select a partnership';
$string['youmustselectatleastonecourse'] = 'You must select at least one course';
$string['wecurrentlyonlyacceptcertainnumbers'] = 'We currently only accept {$a} phone numbers. You can use most standard ways of typing a phone number like 2345678910 or +1 (234) 567-8910.';


// Phone communication
$string['allphonesalreadyverified'] = 'All of your phone numbers have already been verified!';
$string['aws'] = 'AWS';
$string['awssmsvoice'] = 'AWS End User Messaging';
$string['awssmsvoice_desc'] = 'Enter AWS End User Messaging configuration here. The AWS End User Messaging service can be found {$a}.';
$string['awsaccesskey'] = 'AWS Access Key';
$string['awsaccesskey_desc'] = 'Enter your AWS Access Key here.';
$string['awssmsfailedwithcode'] = 'AWS SMS failed with code: {$a}';
$string['awsregion'] = 'AWS Region';
$string['awsregion_desc'] = 'Enter your AWS Region here.';
$string['awssecretkey'] = 'AWS Secret Key';
$string['awssecretkey_desc'] = 'Enter your AWS Secret Key here.';
$string['awsotppoolid'] = 'AWS OTP pool ID';
$string['awsotppoolid_desc'] = 'Enter the AWS End User Messeging phone pool ID from which to send one-time passwords.';
$string['awsinfopoolid'] = 'AWS info pool ID';
$string['awsinfopoolid_desc'] = 'Enter the AWS End User Messeging phone pool ID from which to send informational messages.';
$string['awsotporiginatorphone'] = 'AWS OTP originator phone number';
$string['awsotporiginatorphone_desc'] = 'Enter the AWS End User Messeging originator phone number from which to send one-time passwords.';
$string['awsinfooriginatorphone'] = 'AWS info originator phone number';
$string['awsinfooriginatorphone_desc'] = 'Enter the AWS End User Messeging originator phone number from which to send informational messages.';
$string['awssnsinvalidmessagetype'] = 'Invalid message type for AWS SNS: "{$a}". Please check AWS SNS documentation for types.';
$string['awssmsinvalidmessagetype'] = 'Invalid message type for AWS End User Messaging: "{$a}". Please check AWS End User Messaging documentation for types.';
$string['awssmsinvalidresponse'] = 'Invalid response: {$a}';
$string['awssns'] = 'AWS SNS';
$string['awssns_desc'] = 'Enter AWS SNS configuration here. An account for AWS SNS can be created {$a}.';
$string['awssnsaccesskey'] = 'AWS SNS Access Key';
$string['awssnsaccesskey_desc'] = 'Enter your AWS SNS Access Key here.';
$string['awssnsregion'] = 'AWS SNS Region';
$string['awssnsregion_desc'] = 'Enter your AWS SNS Region here.';
$string['awssnssecretkey'] = 'AWS SNS Secret Key';
$string['awssnssecretkey_desc'] = 'Enter your AWS SNS Secret Key here.';
$string['caughtexception'] = 'Caught exception: {$a}';
$string['codeconfirmed'] = 'Code confirmed! Your phone number {$a->tophonenumber} is now verified.';
$string['dbrecordidnotset'] = 'The database record ID was not set properly.';
$string['equipmentexchangeselection'] = 'Equipment exchange selection';
$string['updatedyourexchangetime'] = 'You already had an exchange time, so we updated it to the one you just submitted.';
$string['enterexactly6digits'] = '- Must be exactly 6 digits.';
$string['entermobilephone'] = 'Enter your mobile phone number';
$string['enternumbersonly'] = '- Enter numbers only.';
$string['errorcommunications'] = 'Your site couldn\'t communicate with your mail server. Please check your outgoing mail configuration.';
$string['fillouttheform'] = 'Fill out the {$a} form here.';
$string['fromtext'] = 'From username or email address';
$string['fromtext_help'] = 'This field emulates sending the message from that user, but the From header used in the real email sent will depend on other settings such as allowedemaildomains';
$string['fromtext_invalid'] = 'Invalid From username or email. Must be a valid email format or an existing username in Moodle.';
$string['haventfilledoutform'] = 'No phone number found, which means you haven\'t filled out the correct form! {$a}';
$string['here'] = 'here';
$string['httprequestfailed'] = 'HTTP request failed.';
$string['httprequestfailedwithcode'] = 'HTTP request failed with code {$a->httpcode}<br />cURL code: {$a->curlcode}';
$string['smsgatewaystouse'] = 'SMS gateways to use';
$string['smsgatewaystouse_desc'] = 'Select the SMS gateway to use for sending various types of SMS messages. Create a gateway {$a}.';
$string['otpgateway'] = 'OTP gateway';
$string['otpgateway_desc'] = 'Select the gateway to use for sending one-time passwords for phone verification.';
$string['infogateway'] = 'Info gateway';
$string['infogateway_desc'] = 'Select the gateway to use for sending informational messages via SMS.';
$string['infogatewaynotset'] = 'Info gateway not set!';
$string['invalidotpformat'] = 'Invalid OTP format.';
$string['selectagateway'] = 'Select a gateway';
$string['enduser_nosmsgatewayselected'] = 'Oops! No message was sent. It looks like we didn\'t set up an SMS gateway. Please contact a system administrator.';
$string['infobip'] = 'Infobip';
$string['infobip_desc'] = 'Enter Infobip configuration here. An account for Infobip can be accessed/created {$a}.';
$string['infobipapibaseurl'] = 'Infobip API base URL';
$string['infobipapibaseurl_desc'] = 'Enter the API base URL for Infobip.';
$string['infobipapikey'] = 'Infobip API key';
$string['infobipapikey_desc'] = 'Enter the API key for Infobip.';
$string['invalidphonenumberformat'] = 'Invalid phone number format.';
$string['textmessage'] = 'Text message';
$string['mustverifyphone'] = 'Please verify your phone number before continuing.';
$string['clickheretoverify'] = 'Click here to verify your phone number.';
$string['acodehasbeensent'] = 'A verification code has been sent to your phone number. Please enter it here.';
$string['nophonefound'] = 'No phone found! Go to {$a} > Optional to add your phone number.';
$string['nophonestoverify'] = 'It looks like you don\'t have any phones that need verification.';
$string['noproviderfound'] = 'Provider not configured! Configure a provider here: {$a}';
$string['noproviderfound_user'] = 'Provider not configured! Please contact a system administrator.';
$string['notasupportedcountry'] = '{$a} is not a supported country.';
$string['novalidotpsfound'] = 'No valid OTPs found for you. You\'ll need to send a verification code first. You can do that here: {$a}';
$string['novalidphonefound'] = 'No valid phone found! Go to {$a} > Optional to add your phone number.';
$string['otp'] = 'OTP';
$string['otpdoesnotmatch'] = 'The code you entered does not match the one we sent you. Please try again.';
$string['otperror'] = 'OTP error:<br /><br />{$a}';
$string['otphasexpired'] = 'It looks like this code has expired! Don\'t worry, though, you can request another one {$a}.';
$string['otpsdonotmatch'] = 'The code you entered does not match any of the verification codes we have for you. Please try again.';
$string['otpforthisnumberalreadyexists'] = 'A code for this number already exists. Please check your text messages. It may take a few minutes to receive the code.';
$string['otpverification'] = 'OTP verification';
$string['phone'] = 'Phone';
$string['phonealreadyverified'] = 'It looks like this phone number has already been verified.';
$string['phonefieldsdonotexist'] = 'Phone fields "phone1" or "phone2" do not exist.';
$string['phoneproviderconfiguration'] = 'Phone provider configuration';
$string['phonesettings'] = 'Phone settings';
$string['phoneverification'] = 'Phone verification';
$string['phoneverificationcodefor'] = '{$a->otp} is your phone verification code for {$a->site}.';
$string['phoneverificationrequire'] = 'Phone verification required: since you have students taking courses with us, you must verify your phone number before continuing.';
$string['profilesettings'] = 'profile settings';
$string['provider'] = 'Provider';
$string['provider_desc'] = 'Select the provider to use for sending SMS messages.';
$string['recipientphone_invalid'] = 'Invalid recipient phone number. Must be a valid phone number.';
$string['somethingwrong_phone'] = 'Something\'s wrong with your phone number. Go to {$a} > Optional to add your phone number.';
$string['somethingwrong_phone1'] = 'Something\'s wrong with your phone number (phone1). Please correct it in your profile, under Optional > Phone.';
$string['somethingwrong_phone2'] = 'Something\'s wrong with your mobile phone number (phone2). Please correct it in your profile, under Optional > Mobile phone.';
$string['showinnavigation'] = 'Show in navigation';
$string['showinnavigation_desc'] = 'This setting determines whether the phone verification plugin will be shown in the navigation.';
$string['selectphonetoverify'] = 'Select a phone number to verify';
$string['selectprovider'] = 'Select provider to use';
$string['sendtest'] = 'Send a test message';
$string['senttextsuccess'] = 'Text message for verification was successfully sent to {$a->tonumber}<br />Now use the code to verify your phone number here: {$a->link}.';
$string['senttextfailure'] = 'Message did not send:<br /><br />{$a}';
$string['somethingwentwrong'] = 'Something went wrong... This probably needs to be looked at by a programmer.';
$string['subject'] = '{$a->site}: test message. {$a->additional} Sent: {$a->time}';
$string['subjectadditional'] = 'Additional subject';
$string['testoutgoingtextconf'] = 'Test outgoing text configuration';
$string['testoutgoingtextdetail'] = 'Note: Before testing, please save your configuration.<br />{$a}';
$string['testoutgoingtextconf_message'] = 'Here\'s your text from {$a->shortname} via {$a->provider}!';
$string['testmessage'] = 'Test message';
$string['twilio'] = 'Twilio';
$string['twilio_desc'] = 'Enter Twilio configuration here. An account for Twilio can be accessed/created {$a}.';
$string['twilioaccountsid'] = 'Twilio account SID';
$string['twilioaccountsid_desc'] = 'Enter the account SID for Twilio.';
$string['twilioauthtoken'] = 'Twilio auth token';
$string['twilioauthtoken_desc'] = 'Enter the auth token for Twilio.';
$string['twilionumber'] = 'Twilio number';
$string['twilionumber_desc'] = 'Enter the Twilio number to send messages from.';
$string['verificationcode'] = 'Verification code';
$string['verificationstatus'] = 'Verification status';
$string['verifyotp'] = 'Verify OTP';
$string['verifyotpdetail'] = 'Verify your phone number with an existing code.<br />{$a}';
$string['verifytestotp'] = 'Verify test OTP';
$string['verifytestotpdetail'] = 'Verify your phone number with an existing code.<br />{$a}';
$string['verifyphonenumber'] = 'Verify phone number';
$string['verifyphonenumber_desc'] = 'Please enter a phone number to be verified for our texting services.';
$string['wait10minutes'] = 'You\'ll have to wait 10 minutes before you can request another code.';
$string['wecurrentlyonlyacceptusphonenumbers'] = 'We currently only accept U.S. phone numbers. You can use most standard ways of typing a phone number like 2345678910 or +1 (234) 567-8910.';


// Time custom format strings
$string['strftimedate'] = '%B %d, %Y';
$string['strftimedate'] = '%d %B %Y';
$string['strfdaymonthdateyear'] = '%A, %B %d, %Y';
$string['strftime24date_mdy'] = '%H:%M, %b %d %Y';

$string['strftimedatemonthabbr'] = '%d %b %Y';
$string['strftimedatemonthtimeshort'] = '%d %b %Y, %I:%M';
$string['strftimedatefullshort'] = '%m/%d/%y';
$string['strftimedaymonth'] = '%A, %B %d';
$string['strftimedateshort'] = '%d %B';
$string['strftimedateshortmonthabbr'] = '%d %b';
$string['strftimedatetime'] = '%d %B %Y, %I:%M %p';
$string['strftimedatetimeaccurate'] = '%d %B %Y, %I:%M:%S %p';
$string['strftimedatetimeshort'] = '%m/%d/%y, %H:%M';
$string['strftimedatetimeshortaccurate'] = '%m/%d/%y, %H:%M:%S';
$string['strftimedaydate'] = '%A, %d %B %Y';
$string['strftimedaydatetime'] = '%A, %d %B %Y, %I:%M %p';
$string['strftimedayshort'] = '%A, %d %B';
$string['strftimedaytime'] = '%a, %H:%M';
$string['strftimemonth'] = '%B';
$string['strftimemonthyear'] = '%B %Y';
$string['strftimerecent'] = '%d %b, %H:%M';
$string['strftimerecentfull'] = '%a, %d %b %Y, %I:%M %p';
$string['strftimetime'] = '%I:%M %p';
$string['strftimetime12'] = '%I:%M %p';
$string['strftimetime24'] = '%H:%M';



// Bulk family upload and enroll strings

$string['addbulkfamilies'] = 'Add bulk families';
$string['bulkfamilyupload'] = 'Bulk family upload and enroll';

$string['familiesinputdata'] = 'Families data';
$string['familiesinputdata_help'] = 'Be sure to format your text input to match the format of the template below:<br /><br />
Parent 1 Name <br />
555-555-5555 <br />
parent1email@domain.com <br />
Parent 2 Name <br />
555-555-5555 <br />
parent2email@domain.com <br />
1
* Student 1 Name <br />
555-555-5555 <br />
student1email@domain.com <br />
** courseid1, courseid2, courseid3, etc. <br />
* Student 2 Name <br />
555-555-5555 <br />
student2email@domain.com <br />
** courseid1, courseid2, courseid3, etc. <br />
<br />
<br />
Parent 3 Name <br />
555-555-5555 <br />
parent3email@domain.com <br />
Parent 4 Name <br />
555-555-5555 <br />
parent4email@domain.com <br />
2
* Student 3 Name <br />
555-555-5555 <br />
student3email@domain.com <br />
** courseid1, courseid2, courseid3, etc. <br />
* Student 4 Name <br />
555-555-5555 <br />
student4email@domain.com <br />
** courseid1, courseid2, courseid3, etc. <br />
<br />
Here are the formatting rules: <br />
- Each family must be separated by a blank line. <br />
- You can have an arbitrary number of parents or students. <br />
- The family\'s partnership ID must be listed at the end of the parent list. <br />
- If the partnership ID is not listed, the family will not be assigned to any partnership. <br />
- Phone numbers are optional but can be written in most common formats. <br />
- Parents must have at least a name and email. <br />
- Parents without students will be added as normal users, not associated with any student or course. <br />
- Emails should be the final thing listed for a given parent. <br />
- Student names must start with a single asterisk character (*). <br />
- Students with at least one parent must have at least a first name and a list of courses. <br />
- If no last name is provided for a student, the last name of the first listed parent will be used. <br />
- Students without parents must have both a first AND last name, an email, and a list of courses. <br />
- Students only need an email listed if there are no parents defined. <br />
- If no email is listed for a student, one will be generated automatically based on the first listed parent\'s email <br />
- A generated student email will not be used if the parent already has a student by the same name. <br />
- A list of courses must start with two asterisk characters (**). <br />
- The list of courses should be the final thing listed for a given student. <br />
- You can add as many families as you want, but the more you add, the longer it will take the data to process. <br />
- This form will create accounts if they do not already exist (based on the user\'s email), enroll students in their courses, assign parents to their students, and enroll parents in all the courses their students were enrolled in.
';
$string['uploadresults'] = 'Upload Results';
$string['newparentcreated'] = 'Created new parent: {$a}';
$string['connotaddmorethanonepartnership'] = '{$a}: cannot add more than one partnership per family. Please remove the extra partnership ID.';
$string['existingparentfound'] = 'Found existing parent: {$a}';
$string['errorprocessingfamily'] = 'Error processing family. Please check make sure the family has all the required fields and is formatted correctly.';
$string['errorprocessingparents'] = 'Error processing parents. Please check make sure each parent has all the required fields and is formatted correctly.';
$string['errorprocessingstudents'] = 'Error processing students. Please check make sure each student has all the required fields and is formatted correctly.';
$string['familyhasnousers'] = 'Family has no users.';
$string['familyaddedsuccessfully'] = '{$a} family added successfully.';
$string['errorprocessingline'] = 'Error processing line: {$a}';
$string['onlyonenameprovided'] = 'Only one name provided: {$a}';
$string['newstudentcreated'] = 'Created new student: {$a}';
$string['existingstudentfound'] = 'Found existing student: {$a}';
$string['expectedanonemptystring'] = 'Expected a non-empty string';
$string['parentneedsemail'] = 'The parent, {$a}, needs an email';
$string['parentroleassigned'] = 'Assigned parent role to: {$a}';
$string['studentroleassigned'] = 'Assigned student role to: {$a}';
$string['thesecoursesneedastudent'] = 'These courses need a student: {$a}';
$string['userenrolled'] = 'Enrolled user {$a->firstname} {$a->firstname} in course {$a->coursename}';
$string['invalidformat'] = 'Invalid data format on line {$a}';
$string['invalidinput'] = 'Invalid input: {$a}';
$string['processingcompleted'] = 'Processing completed. {$a->created} users created, {$a->updated} users updated.';
$string['emailexists'] = 'Email already exists: {$a}';
$string['invalidcourseid'] = 'Invalid course ID: {$a}';
$string['invalidpartnershipid'] = 'Invalid partnership ID: {$a}';
$string['nopermission'] = 'You do not have permission to perform this action.';
$string['studentneedsemail'] = '{$a} needs an email if there are no parents defined.';
$string['studentneedscourse'] = '{$a} needs at least one course to enroll into.';
$string['studentneedsname'] = '{$a} needs a name listed. You can also make sure the courses are listed last for each student.';
$string['errorvalidatingfamilydata'] = 'Error validating family data:';
$string['newfamily'] = 'Line {$a}: New family';
$string['parentname'] = 'p_name: {$a}';
$string['parentemail'] = 'p_email: {$a}';
$string['parentphone'] = 'p_phone: {$a}';
$string['noerrorsfound'] = 'No errors found!';
$string['shownexterror'] = 'Show next error';
$string['studentname'] = 's_name: {$a}';
$string['studentemail'] = 's_email: {$a}';
$string['studentphone'] = 'p_phone: {$a}';
$string['studentcourses'] = 's_courses: {$a}';
$string['partnershipid'] = 'Partnership ID';
$string['preprocess'] = 'Pre-process';
$string['uploadandenroll'] = 'Upload & enroll';
$string['unrecognizedformat'] = '{$a}: Unrecognized format';
$string['nocoursesfoundforthispartnership'] = 'No courses found for this partnership';
$string['partnershipnumbernotfound'] = 'Partnership ID #{$a} not found';
$string['coursealreadyadded'] = 'Course ID #{$a} already added';
$string['courseidnotfound'] = 'Course ID #{$a} not found';
$string['courseidnotfoundinpartnership'] = 'Course ID #{$a->c_id} not found in partnership ID #{$a->p_id}';
$string['preprocessing_success'] = 'Preprocessing successful';
$string['preprocessing_failure'] = 'Preprocessing failed';
$string['useralreadyenrolled'] = '{$a->firstname} {$a->lastname} is already enrolled in course {$a->coursename}.';
$string['userenrolled'] = '{$a->firstname} {$a->lastname} enrolled successfully in course {$a->coursename}.';
$string['userassignedtootheruserwithrole'] = '{$a->parent} assigned as {$a->role} to {$a->student}';
$string['rolealreadyassigned'] = '{$a->parent} is already assigned as {$a->role} to {$a->student}';
$string['errorcreatinguser'] = 'For some reason, {$a->firstname} {$a->lastname} was not created but also was not found in the database.';
$string['usernotaddedtofamily'] = '{$a->firstname} {$a->lastname} was not added to their family.';
$string['accountcreatedsuccessfully'] = '{$a->firstname} {$a->lastname}\'s account was created successfully.';
$string['accountalreadyexists'] = '{$a->firstname} {$a->lastname}\'s account already exists.';
$string['accountalreadyexistsbutwithdifferentname'] = 'An account already exists with the email {$a->email}, but it\'s under a different name. You entered {$a->firstname} {$a->lastname}, but we\'re keeping the existing account\'s name: {$a->otherfirst} {$a->otherlast}. Changing the user\'s name must be done within their preferences.';
$string['nocoursesfoundforuser'] = 'No courses found for {$a->firstname} {$a->lastname}.';
$string['familyaddedwithwarnings'] = 'The {$a} family was added with some warnings';
$string['familyaddedwitherrors'] = 'The {$a} family was added with errors';
$string['familyprocessingresults'] = 'Processing results for {$a} family';
$string['enrollmentinstancedoesnotexist'] = 'Enrollment instance does not exist, so we\'re creating it.';

$string['studentwelcomesubject'] = '{$a->sitename} Courses Access for Students';
$string['parentwelcomesubject'] = '{$a->sitename} Courses Access for Parents';
$string['genericwelcomesubject'] = '{$a->sitename} Courses Access';
$string['welcomemessage_user'] = 'To {$a->user}, hello from {$a->sitename}!';
$string['parentenrollmessage_partnership'] = 'One or more of your students ({$a->students}) have been enrolled in {$a->sitename} via {$a->partnership}, and they are taking the following courses:<br /><br />
{$a->courses}

<br /><br />

You have been assigned as a parent to each of your students. You can view their profiles and courses by clicking on the student/course names&mdash;right within this email! Remember, you\'ll need to login to your personal, parent {$a->sitename} account to be able to view everything. <br /><br />

Please contact the course instructors or your partnership coordinator with any questions. We\'re excited to have {$a->students} learning with us!
';
$string['parentenrollmessage'] = 'One or more of your students ({$a->students}) have been enrolled in {$a->sitename}, and they are taking the following courses:<br /><br />
{$a->courses}

<br /><br />

You have been assigned as a parent to each of your students. You can view their profiles and courses by clicking on the student/course names&mdash;right within this email! Remember, you\'ll need to login to your personal, parent {$a->sitename} account to be able to view everything. <br /><br />

Please contact the course instructors with any questions. We\'re excited to have {$a->students} learning with us!
';
$string['studentenrollmessage'] = 'Welcome to the {$a->schoolyear} school year in {$a->sitename}! You\'ve been enrolled in the following courses:<br /><br />
{$a->courses}

<br /><br />

You can view your courses by clicking on the names&mdash;right within this email! Remember, you\'ll need to login to your personal, student {$a->sitename} account to be able to view everything and complete your assignments. <br /><br />

Please contact the course instructors with any questions. We\'re excited to have you learning with us!
';
$string['genericenrollmessage'] = 'Welcome to the {$a->schoolyear} school year in {$a->sitename}! You\'ve been enrolled in the following courses:<br /><br />
{$a->courses}

<br /><br />

You can view your courses by clicking on the names&mdash;right within this email! Remember, you\'ll need to login to your personal, student {$a->sitename} account to be able to view everything and complete your assignments. <br /><br />

Please contact the course instructors with any questions. We\'re excited to have you learning with us!
';


$string['welcomeemail_subject'] = '{$a->siteshortname} Login Information';
$string['welcomeemail_body'] = 'This message is for {$a->personname} and contains their login information for {$a->sitefullname}.<br /><br />

Welcome! Here\'s your login information:<br /><br />
Login URL: {$a->loginurl}<br />
Email: {$a->email}<br />
Username: {$a->username}<br />
Password: {$a->password}<br /><br />

Note that you\'ll need to change your password upon your first login. Please login in as soon as possible to make sure you can successfully access everything you need!<br /><br />

Thanks!';



$string['welcomemessage'] = 'Hey there, {$a}!';

// Notification settings
$string['notificationsettings'] = 'Notification settings';
$string['notifyparents'] = 'Notify parents';
$string['notifyparents_desc'] = 'Send welcome messages to parents when they are given access to their child\'s courses.';
$string['notifystudents'] = 'Notify students';
$string['notifystudents_desc'] = 'Send welcome messages to students when they are enrolled in courses.';
$string['messagesender'] = 'Send welcome message from';
$string['messagesender_desc'] = 'When sending course welcome messages, who should the message appear to be from?';
$string['fromcoursecontact'] = 'From the course contact';
$string['fromkeyholder'] = 'From the key holder';
$string['fromnoreply'] = 'From the no-reply address';
$string['notificationsdisabledforusertype'] = 'Yo, {$a->user}. Just so you know, notification emails are currently disabled for {$a->role}s, so no {$a->role} will receive an email notification during this enrolment process. If you\'d like to enable email notifications specifically for {$a->role}s, you can do that here: {$a->link}.';
$string['notificationsdisabledforusertypes'] = 'Yo, {$a->user}. Just so you know, notification emails are currently disabled for {$a->role1}s and {$a->role2}s, so none of these users will receive an email notification during this enrolment process. If you\'d like to enable email notifications, you can do that here: {$a->link}.';

// Reminder settings
$string['equipmentexchangereminder'] = 'Equipment exchange reminder';
$string['reminderheading'] = 'Reminder settings';
$string['reminderheading_desc'] = 'Settings for sending reminders to users about upcoming equipment exchange dates.';
$string['reminder_inadvance_days'] = 'Reminder in advance (days)';
$string['reminder_inadvance_days_desc'] = 'Number of days in advance to send the first reminder message to users for upcoming equipment exchanges, specific to the user.';
$string['reminder_inadvance_hours'] = 'Reminder in advance (hours)';
$string['reminder_inadvance_hours_desc'] = 'Number of hours in advance to send the second reminder message to users for upcoming equipment exchanges, specific to the user.';
$string['reminder_timeout'] = 'Reminder timeout';
$string['reminder_timeout_desc'] = 'The end of the timeframe in which a reminder can be sent to the user. If for some reason the reminder is not sent within the timeframe of between reminder_inadvance and (reminder_inadvance + reminder_timeout), the reminder will not be sent.';
$string['reminder_template_default'] = 'REMINDER: Equipment exchange in {DAYS} days on {DATE} from {START} to {END}. Location: {LOCATION}.';
$string['reminder_template_days'] = 'Your equipment exchange is scheduled for {DAYS} day(s) from now, on {DATE} from {START} to {END}. Location: {LOCATION}.';
$string['reminder_template_hours'] = 'REMINDER: Your equipment exchange is TOMORROW ({DATE}) from {START} to {END}. Location: {LOCATION}. Don\'t forget!';
$string['reminderheader'] = 'Reminder settings';
$string['remindersettings_desc'] = 'Settings for sending reminders to users about upcoming equipment exchange dates.';
$string['reminder_template_days_desc'] = 'Template for the reminder message sent days before an exchange.';
$string['reminder_template_hours_desc'] = 'Template for the reminder message sent hours before an exchange.';
$string['reminder_template_days_default'] = 'REMINDER: Your equipment exchange is in {DAYS} days on {DATE} from {START} to {END}. Location: {LOCATION}.';
$string['reminder_template_hours_default'] = 'REMINDER: Your equipment exchange is in {HOURS} hour(s) today from {START} to {END}. Location: {LOCATION}. Don\'t forget!';
$string['reminder_inadvance_days'] = 'Reminder in advance (days)';


// Welcome messages
$string['studentwelcomemessage'] = 'Hi {$a->firstname},

Welcome to {$a->coursename}! You have been enrolled as a {$a->roletype}.

To access your course, visit: {$a->courseurl}

Best regards,
{$a->sitename} Team';

$string['parentwelcomemessage'] = 'Hi {$a->firstname},

You have been granted parent access to {$a->coursename}.

You can monitor your student\'s progress at: {$a->courseurl}

Best regards,
{$a->sitename} Team';

$string['messageprovider:coursewelcome'] = 'Course welcome notifications';
$string['coursewelcome'] = 'Course welcome message';
$string['coursewelcome_help'] = 'Notifications sent to users when they are enrolled in a course through the Equipment plugin.';
$string['enrollmentemailsenttouserforcourses'] = 'Enrollment email sent to user {$a->user} ({$a->email}) for courses: {$a->courses_comma}';
$string['enrollmentemailnotsenttouserforcourses'] = 'Welcome Enrollment email not sent to user {$a->user} ({$a->email}) for courses: {$a->courses_comma}';
$string['notsendingemailtouser_nocourses'] = 'Not sending email to {$a} because they weren\'t enrolled in any new courses.';
$string['newuseremailsenttouser'] = 'New user email sent to {$a->personname} at the address: {$a->email}.';
$string['emailfailedtosendtouser'] = 'Email failed to send to {$a->personname} at the address: {$a->email}.';
$string['contactusforyourpassword'] = 'Contact us for your password.';

// Equipment task strings
$string['taskname_sendexchangereminders'] = 'Send exchange reminders';

// Exchange form strings
$string['selectexchange'] = 'Select Equipment Exchange';
$string['selectexchangedescription'] = 'Please select an available equipment exchange time and provide pickup information.';
$string['exchange_locationandtime'] = 'Exchange location and time';
$string['pickupinformation'] = 'Pickup information';
$string['pickupmethod'] = 'Equipment pickup method';
$string['pickuppersonname'] = 'Name of person picking up equipment';
$string['pickuppersonphone'] = 'Phone number of person picking up equipment';
$string['pickuppersondetails'] = 'Additional details for pickup';
$string['submitexchange'] = 'Submit Exchange Request';
$string['exchangesubmitted'] = 'Exchange request submitted successfully';
$string['exchangesubmissionfailed'] = 'Exchange request submission failed';
$string['noexchangesavailable'] = 'No equipment exchanges are currently available';
$string['exchangenotavailable'] = 'Selected exchange is no longer available';

// Mass text messaging strings
$string['masstextmessaging'] = 'Mass Text Messaging';
$string['masstextinfo'] = 'Send a text message to all parents of students enrolled in courses with future end dates. Only parents with verified phone numbers will receive the message.';
$string['masstextplaceholder'] = 'Enter your message here (max 250 characters)...';
$string['sendtexts'] = 'Send texts';
$string['preview'] = 'Preview';
$string['estimatedrecipients'] = 'Estimated recipients';
$string['calculating'] = 'Calculating...';
$string['parents'] = 'parents';
$string['errorloadingcount'] = 'Error loading count';
$string['charactersremaining'] = '{count} characters remaining';
$string['charactersover'] = '{count} characters over limit';
$string['minimumchars'] = 'Message must be at least {$a} characters';
$string['maximumchars'] = 'Message cannot exceed {$a} characters';
$string['nostudentsinactivecourses'] = 'No students found in courses with future end dates';
$string['noparentsverifiedphones'] = 'No parents found with verified phone numbers';
$string['masstextsuccess'] = 'Successfully sent messages to {$a->sent} of {$a->total} recipients';
$string['masstextfailures'] = 'Failed to send messages to {$a->failed} of {$a->total} recipients';
$string['messagesent'] = 'Message sent to {$a->name} ({$a->phone})';
$string['messagefailed'] = 'Message failed to {$a->name}: {$a->error}';
$string['masstextconfirmation'] = 'Mass text sent: {$a->sent} successful, {$a->failed} failed of {$a->total} total. Message: "{$a->message}"';
$string['adminconfirmationsent'] = 'Confirmation message sent to your phone';
$string['nosmsgatewayconfigured'] = 'No SMS gateway is configured';
$string['confirmmasstextmessage'] = 'Are you sure you want to send this message to all parents?';
$string['pleasentermessage'] = 'Please enter a message';
$string['messagetoolong'] = 'Message is too long. Maximum 250 characters allowed';
$string['mass_text_title'] = 'Send Mass Text Message';
$string['mass_text_description'] = 'Send a text message to all verified parents in the equipment system.';
$string['message'] = 'Text Message';
$string['message_placeholder'] = 'Enter your message here (max 250 characters)...';
$string['send_message'] = 'Send Message';
$string['preview'] = 'Preview';
$string['estimated_recipients'] = 'Estimated recipients';
$string['mass_text_tips'] = 'Messages will only be sent to parents with verified phone numbers. Keep messages concise and clear.';
$string['charactersremaining'] = '{$a} characters remaining';
$string['calculating'] = 'Calculating...';
$string['sendmessages'] = 'Send Messages';
$string['masstextinstructions_title'] = 'Mass Text Messaging Instructions';
$string['masstextinstructions'] = 'Use this form to send a text message to all parents with verified phone numbers who have students enrolled in courses with future end dates. Messages are limited to 250 characters and will be sent immediately upon submission.';
$string['event_mass_text_sent'] = 'Mass text message sent';
$string['event_mass_text_description'] = 'The user with id \'{$a->userid}\' sent a mass text message to {$a->total} recipients. Success: {$a->success}, Failed: {$a->failed}. Message: "{$a->message}"';

// Enhanced AWS error messages
$string['awssmssendfailed'] = 'AWS SMS sending failed';
$string['awsquotaexceeded'] = 'AWS SMS quota exceeded. Please check your AWS account limits and usage.';
$string['awsinvalidphone'] = 'Invalid phone number: {$a}. Please verify the phone number format.';
$string['awsunreachablephone'] = 'Phone number {$a} is unreachable or blocked.';
$string['awsaccessdenied'] = 'AWS access denied. Please check your AWS credentials and permissions.';
$string['awsserviceerror'] = 'AWS service error. Please try again later or contact support.';
$string['awsgeneralerror'] = 'AWS error: {$a}';
$string['smsgeneralerror'] = 'SMS sending error: {$a}';


// Task and reminder strings
$string['taskname_sendexchangereminders'] = 'Send equipment exchange reminders';
$string['equipmentexchangereminder'] = 'Equipment exchange reminder';

// Enhanced error messages for SMS sending
$string['smsvalidationerror'] = 'SMS validation error: {$a}';
$string['gatewaynotfound'] = 'SMS gateway not found or disabled';
$string['configurationerror'] = 'SMS gateway configuration error';
$string['clientinitializationerror'] = 'Failed to initialize SMS client';
$string['unsupportedgateway'] = 'Unsupported SMS gateway type: {$a}';
$string['phonevalidationerror'] = 'Phone number validation failed: {$a}';
$string['messageidmissing'] = 'SMS service did not return a message ID';

// AWS specific error messages (if not already present)
$string['awsquotaexceeded'] = 'AWS SMS quota exceeded. Please check your AWS account limits and usage.';
$string['awsinvalidphone'] = 'Invalid phone number: {$a}. Please verify the phone number format.';
$string['awsunreachablephone'] = 'Phone number {$a} is unreachable or blocked.';
$string['awsaccessdenied'] = 'AWS access denied. Please check your AWS credentials and permissions.';
$string['awsserviceerror'] = 'AWS service error. Please try again later or contact support.';
$string['awsgeneralerror'] = 'AWS error: {$a}';
$string['smsgeneralerror'] = 'SMS sending error: {$a}';

// Pool-related strings
$string['poolid'] = 'Pool ID';
$string['pooltype'] = 'Pool type';
$string['pooltypeinfo'] = 'Info pool';
$string['pooltypeotp'] = 'OTP pool';
$string['poolnotfound'] = 'Pool {$a} not found or not configured';
$string['poolvalidated'] = 'Pool {$a} validated successfully';
$string['usingpool'] = 'Using pool: {$a}';
$string['usingoriginationphone'] = 'Using origination phone: {$a}';
$string['poolorconfigurationmissing'] = 'Either pool ID or origination phone number must be configured';

// AWS End User Messaging specific strings
$string['awsendusermessaging'] = 'AWS End User Messaging';
$string['awsendusermessaging_desc'] = 'Configure AWS End User Messaging service for SMS delivery. This service replaces the deprecated AWS Pinpoint service.';
$string['awsconfigurationerror'] = 'AWS configuration error: {$a}';
$string['awsinvalidpoolid'] = 'Invalid AWS pool ID: {$a}';
$string['awspoolnotfound'] = 'AWS pool not found: {$a}';
$string['awsendusermessagingresponse'] = 'AWS End User Messaging response: {$a}';
$string['awsendusermessagingsuccess'] = 'Message sent successfully via AWS End User Messaging';
$string['awsendusermessagingfailed'] = 'Failed to send message via AWS End User Messaging';

// CLI debug tool strings
$string['clidebugsmstool'] = 'CLI SMS Debug Tool';
$string['clitestphone'] = 'Test phone number';
$string['clipoolid'] = 'Pool ID to use for testing';
$string['clipooltype'] = 'Pool type (info or otp)';
$string['clisendtest'] = 'Actually send test SMS';
$string['cliverbose'] = 'Show detailed information';
$string['clipoolconfiguration'] = 'Pool Configuration';
$string['clismstesting'] = 'SMS Testing';
$string['climissingphone'] = 'Test phone number required for SMS testing';
$string['clinopoolconfigured'] = 'No pool configured for the specified type';
$string['cliinvalidpooltype'] = 'Invalid pool type. Use \'info\' or \'otp\'';
$string['cliusingdefaultpool'] = 'Using default info pool';
$string['cliusingspecifiedpool'] = 'Using specified pool';
$string['clismssent'] = 'SMS sent successfully';
$string['clismsfailed'] = 'SMS sending failed';
$string['clidebugtoolcomplete'] = 'Debug tool execution complete';

// Enhanced error categorization
$string['validationerror'] = 'Validation error';
$string['gatewayerror'] = 'Gateway error';
$string['phonevalidationerror'] = 'Phone validation error';
$string['awsclienterror'] = 'AWS client initialization error';
$string['awsresponseerror'] = 'AWS response error';
$string['awsserviceerror'] = 'AWS service error';
$string['generalexception'] = 'General exception occurred';

// Pool selection messages
$string['autopoolselection'] = 'Automatic pool selection based on message type';
$string['manualPoolselection'] = 'Manual pool selection';
$string['fallbacktoorigination'] = 'Falling back to origination phone number';
$string['nopoolorconfigured'] = 'No pool or origination phone configured';

// Success/status messages
$string['smssentviapool'] = 'SMS sent via pool {$a->pool} to {$a->phone}';
$string['smssentviaorigination'] = 'SMS sent via origination phone {$a->origination} to {$a->phone}';
$string['smspoolvalidated'] = 'SMS pool {$a} validated successfully';
$string['poolconfigurationvalid'] = 'Pool configuration is valid';
$string['poolconfigurationinvalid'] = 'Pool configuration is invalid: {$a}';

// AWS Configuration Set strings
$string['awsconfigurationset'] = 'AWS Configuration Set';
$string['awsconfigurationset_desc'] = 'Enter the AWS End User Messaging configuration set name (optional). Leave empty if you haven\'t created a configuration set in AWS. Configuration sets allow you to track delivery metrics and configure event destinations.';

// Enhanced error messages for configuration set issues
$string['awsconfigurationsetnotfound'] = 'AWS configuration set not found: {$a}';
$string['awsconfigurationsetinvalid'] = 'Invalid AWS configuration set name: {$a}';
$string['awsconfigurationsetmissing'] = 'AWS configuration set \'{$a}\' not found. Either create it in AWS End User Messaging console or leave the setting empty.';

// Additional helpful strings
$string['awsconfigurationsetoptional'] = 'AWS configuration set is optional and can be left empty';
$string['awsconfigurationsethelp'] = 'Configuration sets help track SMS delivery and configure event destinations in AWS End User Messaging';
$string['noconfigurationset'] = 'No configuration set configured - using default AWS settings';

// Debugging and development strings
$string['debuggingawssms'] = 'AWS SMS debugging information';
$string['debugpoolselection'] = 'Pool selection: Using {$a->type} pool {$a->id}';
$string['debugoriginationselection'] = 'Origination selection: Using {$a->type} phone {$a->number}';
$string['debugawsparams'] = 'AWS parameters: {$a}';
$string['debugsmsstatus'] = 'SMS status: {$a->status} - Message ID: {$a->messageid}';

// Debugging and admin strings
$string['smsgatewayvalidated'] = 'SMS gateway validated: {$a}';
$string['reminderprocessingstarted'] = 'Processing {$a->type} reminders for {$a->count} users';
$string['reminderprocessingcomplete'] = 'Reminder processing complete. Processed: {$a->processed}, Errors: {$a->errors}';
$string['userphonemissing'] = 'User {$a} has no mobile phone number configured';
$string['invalidremindermethod'] = 'Invalid reminder method "{$a->method}" for user {$a->userid}';

// Time format strings for templates
$string['strftimedaymonth'] = '%A, %B %d';
$string['strftimetime12'] = '%I:%M %p';

// Inventory Management System strings
$string['inventory'] = 'Inventory';
$string['manageinventory'] = 'Manage inventory';
$string['inventorymanagement'] = 'Inventory management';
$string['inventorydashboard'] = 'Inventory dashboard';
$string['generateqr'] = 'Generate QR codes';
$string['generateqrcodes'] = 'Generate QR codes';
$string['scanqr'] = 'Scan QR code';
$string['checkin'] = 'Check in';
$string['checkout'] = 'Check out';
$string['checkinout'] = 'Check in/out';
$string['checkinequipment'] = 'Check in equipment';
$string['checkoutequipment'] = 'Check out equipment';
$string['equipmentitem'] = 'Equipment item';
$string['equipmentitems'] = 'Equipment items';
$string['product'] = 'Product';
$string['products'] = 'Products';
$string['productcatalog'] = 'Product catalog';
$string['manageproducts'] = 'Manage products';
$string['addproduct'] = 'Add product';
$string['editproduct'] = 'Edit product';
$string['deleteproduct'] = 'Delete product';
$string['productname'] = 'Product name';
$string['productdescription'] = 'Product description';
$string['manufacturer'] = 'Manufacturer';
$string['model'] = 'Model';
$string['category'] = 'Category';
$string['isconsumable'] = 'Is consumable';
$string['consumable'] = 'Consumable';
$string['nonconsumable'] = 'Non-consumable';
$string['productimage'] = 'Product image';
$string['productimage_help'] = 'Upload an image for this product. Supported formats: JPG, PNG, GIF. Maximum size: 10MB. Maximum dimensions: 10,000x10,000 pixels.';
$string['uploadimage'] = 'Upload image';
$string['changeimage'] = 'Change image';
$string['removeimage'] = 'Remove image';
$string['noimage'] = 'No image';
$string['imagetoobig'] = 'Image file is too large. Maximum size allowed is {$a}MB.';
$string['imagedimensionstoobig'] = 'Image dimensions are too large. Maximum dimensions allowed are {$a}x{$a} pixels.';
$string['invalidimageformat'] = 'Invalid image format. Only JPG, PNG, and GIF files are allowed.';
$string['imageuploaded'] = 'Image uploaded successfully';
$string['imageremoved'] = 'Image removed successfully';
$string['imageuploaderror'] = 'Error uploading image: {$a}';
$string['expectedshelflife'] = 'Expected shelf life';
$string['purchasevalue'] = 'Purchase value';
$string['location'] = 'Location';
$string['locations'] = 'Locations';
$string['managelocations'] = 'Manage locations';
$string['addlocation'] = 'Add location';
$string['editlocation'] = 'Edit location';
$string['deletelocation'] = 'Delete location';
$string['locationname'] = 'Location name';
$string['locationdescription'] = 'Location description';
$string['zone'] = 'Zone';
$string['uuid'] = 'UUID';
$string['serialnumber'] = 'Serial number';
$string['status'] = 'Status';
$string['available'] = 'Available';
$string['checkedout'] = 'Checked out';
$string['intransit'] = 'In transit';
$string['maintenance'] = 'Maintenance';
$string['damaged'] = 'Damaged';
$string['lost'] = 'Lost';
$string['conditionstatus'] = 'Condition status';
$string['excellent'] = 'Excellent';
$string['good'] = 'Good';
$string['fair'] = 'Fair';
$string['poor'] = 'Poor';
$string['needsrepair'] = 'Needs repair';
$string['conditionnotes'] = 'Condition notes';
$string['lasttested'] = 'Last tested';
$string['currentuser'] = 'Current user';
$string['studentlabel'] = 'Student label';
$string['transferdestination'] = 'Transfer destination';
$string['iscompleteset'] = 'Is complete set';
$string['expectedreturndate'] = 'Expected return date';
$string['configuration'] = 'Configuration';
$string['configurations'] = 'Configurations';
$string['manageconfigurations'] = 'Manage configurations';
$string['addconfiguration'] = 'Add configuration';
$string['editconfiguration'] = 'Edit configuration';
$string['deleteconfiguration'] = 'Delete configuration';
$string['configurationname'] = 'Configuration name';
$string['configurationcode'] = 'Configuration code';
$string['configurationdescription'] = 'Configuration description';
$string['quantityperstudent'] = 'Quantity per student';
$string['isrequired'] = 'Is required';
$string['sortorder'] = 'Sort order';
$string['courseconfigurations'] = 'Course configurations';
$string['assignconfiguration'] = 'Assign configuration';
$string['transaction'] = 'Transaction';
$string['transactions'] = 'Transactions';
$string['transactiontype'] = 'Transaction type';
$string['transactionlog'] = 'Transaction log';
$string['viewtransactions'] = 'View transactions';
$string['fromuser'] = 'From user';
$string['touser'] = 'To user';
$string['fromlocation'] = 'From location';
$string['tolocation'] = 'To location';
$string['processedby'] = 'Processed by';
$string['notes'] = 'Notes';
$string['conditionbefore'] = 'Condition before';
$string['conditionafter'] = 'Condition after';
$string['pickupexchange'] = 'Pickup/exchange';
$string['timestamp'] = 'Timestamp';
$string['transactionhistory'] = 'Transaction History';
$string['allocation'] = 'Allocation';
$string['allocations'] = 'Allocations';
$string['allocated'] = 'Allocated';
$string['assigned'] = 'Assigned';
$string['returned'] = 'Returned';
$string['allocatedby'] = 'Allocated by';
$string['assignedby'] = 'Assigned by';
$string['quantity'] = 'Quantity';
$string['qrcode'] = 'QR code';
$string['qrcodes'] = 'QR codes';
$string['printablesheet'] = 'Printable sheet';
$string['generatesheet'] = 'Generate sheet';
$string['qrcodegenerator'] = 'QR code generator';
$string['numberofcodes'] = 'Number of codes';
$string['printsheet'] = 'Print sheet';
$string['qrcodesheetgenerated'] = 'QR code sheet generated successfully';
$string['scanequipment'] = 'Scan equipment';
$string['scantoidentify'] = 'Scan QR code to identify equipment';
$string['equipmentnotfound'] = 'Equipment not found';
$string['equipmentfound'] = 'Equipment found';
$string['selectstudent'] = 'Select student';
$string['selectuser'] = 'Select user';
$string['checkinsuccessful'] = 'Check-in successful';
$string['checkoutsuccessful'] = 'Check-out successful';
$string['transfersuccessful'] = 'Transfer successful';
$string['equipmentalreadycheckedout'] = 'Equipment is already checked out';
$string['equipmentalreadycheckedin'] = 'Equipment is already checked in';
$string['equipmentnotavailable'] = 'Equipment is not available for checkout';
$string['invalidequipmentid'] = 'Invalid equipment ID';
$string['invaliduserid'] = 'Invalid user ID';
$string['invalidlocationid'] = 'Invalid location ID';
$string['transfer'] = 'Transfer';
$string['transferequipment'] = 'Transfer equipment';
$string['initiatetransfer'] = 'Initiate transfer';
$string['completetransfer'] = 'Complete transfer';
$string['transferinitiated'] = 'Transfer initiated successfully';
$string['transfercompleted'] = 'Transfer completed successfully';
$string['itemsintransit'] = 'Items in transit';
$string['transferwarning'] = 'Transfer warning';
$string['locationmismatch'] = 'Location mismatch detected';
$string['forcelocationcorrection'] = 'Force location correction';
$string['stocklevel'] = 'Stock level';
$string['stocklevels'] = 'Stock levels';
$string['lowstock'] = 'Low stock';
$string['outofstock'] = 'Out of stock';
$string['stockalert'] = 'Stock alert';
$string['stockmonitoring'] = 'Stock monitoring';
$string['availablestock'] = 'Available stock';
$string['allocatedstock'] = 'Allocated stock';
$string['totalstock'] = 'Total stock';
$string['reorderpoint'] = 'Reorder point';
$string['reorderalert'] = 'Reorder alert';
$string['inventoryreports'] = 'Inventory reports';
$string['equipmentreports'] = 'Equipment reports';
$string['usagereport'] = 'Usage report';
$string['locationreport'] = 'Location report';
$string['conditionreport'] = 'Condition report';
$string['allocationreport'] = 'Allocation report';
$string['transactionreport'] = 'Transaction report';
$string['generatereport'] = 'Generate report';
$string['exportreport'] = 'Export report';
$string['reportgenerated'] = 'Report generated successfully';
$string['uuidhistory'] = 'UUID history';
$string['viewuuidhistory'] = 'View UUID history';
$string['activeuuid'] = 'Active UUID';
$string['inactiveuuid'] = 'Inactive UUID';
$string['deactivateuuid'] = 'Deactivate UUID';
$string['deactivatedreason'] = 'Deactivated reason';
$string['uuiddeactivated'] = 'UUID deactivated successfully';
$string['regenerateuuid'] = 'Regenerate UUID';
$string['newuuidgenerated'] = 'New UUID generated successfully';
$string['labelprinter'] = 'Label printer';
$string['printlabel'] = 'Print label';
$string['labelprinted'] = 'Label printed successfully';
$string['labelprinternotavailable'] = 'Label printer not available';
$string['labelprinteroffline'] = 'Label printer is offline';
$string['labelprinterror'] = 'Label printer error';
$string['queuelabelprint'] = 'Queue label for printing';
$string['labelqueuedforprinting'] = 'Label queued for printing';
$string['bulkoperations'] = 'Bulk operations';
$string['bulkcheckin'] = 'Bulk check-in';
$string['bulkcheckout'] = 'Bulk check-out';
$string['bulktransfer'] = 'Bulk transfer';
$string['selectitems'] = 'Select items';
$string['selecteditems'] = 'Selected items';
$string['bulkoperationsuccessful'] = 'Bulk operation completed successfully';
$string['bulkoperationfailed'] = 'Bulk operation failed';
$string['partiallysuccessful'] = 'Partially successful';
$string['offlinemode'] = 'Offline mode';
$string['offlinesync'] = 'Offline sync';
$string['syncpending'] = 'Sync pending';
$string['syncsuccessful'] = 'Sync successful';
$string['syncfailed'] = 'Sync failed';
$string['queuedtransactions'] = 'Queued transactions';
$string['syncqueuedtransactions'] = 'Sync queued transactions';
$string['conflictresolution'] = 'Conflict resolution';
$string['resolveconflicts'] = 'Resolve conflicts';
$string['conflictsresolved'] = 'Conflicts resolved successfully';
$string['facultydashboard'] = 'Faculty dashboard';
$string['studentsneedingequipment'] = 'Students needing equipment';
$string['equipmentready'] = 'Equipment ready';
$string['markasready'] = 'Mark as ready';
$string['equipmentreadyforpickup'] = 'Equipment ready for pickup';
$string['noequipmentneeded'] = 'No equipment needed';
$string['allequipmentassigned'] = 'All equipment assigned';
$string['pendingassignments'] = 'Pending assignments';
$string['completedassignments'] = 'Completed assignments';
$string['equipmenthistory'] = 'Equipment history';
$string['viewhistory'] = 'View history';
$string['equipmentlifecycle'] = 'Equipment lifecycle';
$string['acquisitiondate'] = 'Acquisition date';
$string['retirementdate'] = 'Retirement date';
$string['totalusage'] = 'Total usage';
$string['maintenancehistory'] = 'Maintenance history';
$string['maintenanceschedule'] = 'Maintenance schedule';
$string['schedulemaintenance'] = 'Schedule maintenance';
$string['maintenancecompleted'] = 'Maintenance completed';
$string['maintenancedue'] = 'Maintenance due';
$string['maintenanceoverdue'] = 'Maintenance overdue';
$string['equipmentcondition'] = 'Equipment condition';
$string['conditionassessment'] = 'Condition assessment';
$string['assesscondition'] = 'Assess condition';
$string['conditionupdated'] = 'Condition updated successfully';
$string['damageassessment'] = 'Damage assessment';
$string['reportdamage'] = 'Report damage';
$string['damagereported'] = 'Damage reported successfully';
$string['repairneeded'] = 'Repair needed';
$string['repairinprogress'] = 'Repair in progress';
$string['repaircompleted'] = 'Repair completed';
$string['replacementneeded'] = 'Replacement needed';
$string['equipmentreplaced'] = 'Equipment replaced';
$string['disposalrequired'] = 'Disposal required';
$string['equipmentdisposed'] = 'Equipment disposed';
$string['inventorysummary'] = 'Inventory summary';
$string['totalitems'] = 'Total items';
$string['availableitems'] = 'Available items';
$string['checkedoutitems'] = 'Checked out items';
$string['intransititems'] = 'In transit items';
$string['maintenanceitems'] = 'Maintenance items';
$string['damageditems'] = 'Damaged items';
$string['lostitems'] = 'Lost items';
$string['inventoryvalue'] = 'Inventory value';
$string['totalvalue'] = 'Total value';
$string['availablevalue'] = 'Available value';
$string['checkedoutvalue'] = 'Checked out value';
$string['depreciatedvalue'] = 'Depreciated value';
$string['replacementcost'] = 'Replacement cost';
$string['inventoryturns'] = 'Inventory turns';
$string['utilizationrate'] = 'Utilization rate';
$string['averagecheckouttime'] = 'Average checkout time';
$string['peakusageperiods'] = 'Peak usage periods';
$string['demandforecasting'] = 'Demand forecasting';
$string['seasonaltrends'] = 'Seasonal trends';
$string['usagepatterns'] = 'Usage patterns';
$string['equipmentpopularity'] = 'Equipment popularity';
$string['mostusedequipment'] = 'Most used equipment';
$string['leastusedequipment'] = 'Least used equipment';
$string['equipmentefficiency'] = 'Equipment efficiency';
$string['costperuse'] = 'Cost per use';
$string['returnrate'] = 'Return rate';
$string['lossrate'] = 'Loss rate';
$string['damagerate'] = 'Damage rate';
$string['maintenancecost'] = 'Maintenance cost';
$string['operationalcost'] = 'Operational cost';
$string['totalcostofownership'] = 'Total cost of ownership';
$string['roianalysis'] = 'ROI analysis';
$string['costbenefit'] = 'Cost benefit';
$string['budgetplanning'] = 'Budget planning';
$string['procurementplanning'] = 'Procurement planning';
$string['capacityplanning'] = 'Capacity planning';
$string['resourceallocation'] = 'Resource allocation';
$string['optimizationrecommendations'] = 'Optimization recommendations';
$string['performancemetrics'] = 'Performance metrics';
$string['kpidashboard'] = 'KPI dashboard';
$string['alertsandnotifications'] = 'Alerts and notifications';
$string['systemalerts'] = 'System alerts';
$string['usernotifications'] = 'User notifications';
$string['emailnotifications'] = 'Email notifications';
$string['smsnotifications'] = 'SMS notifications';
$string['pushnotifications'] = 'Push notifications';
$string['notificationsettings'] = 'Notification settings';
$string['alertthresholds'] = 'Alert thresholds';
$string['escalationrules'] = 'Escalation rules';
$string['notificationtemplates'] = 'Notification templates';
$string['customnotifications'] = 'Custom notifications';
$string['automatednotifications'] = 'Automated notifications';
$string['manualnotifications'] = 'Manual notifications';
$string['notificationhistory'] = 'Notification history';
$string['deliverystatus'] = 'Delivery status';
$string['notificationdelivered'] = 'Notification delivered';
$string['notificationfailed'] = 'Notification failed';
$string['notificationpending'] = 'Notification pending';
$string['retrynotification'] = 'Retry notification';
$string['notificationretried'] = 'Notification retried';
$string['suppressnotifications'] = 'Suppress notifications';
$string['notificationssuppressed'] = 'Notifications suppressed';
$string['enablenotifications'] = 'Enable notifications';
$string['notificationsenabled'] = 'Notifications enabled';
$string['testnotification'] = 'Test notification';
$string['notificationtestsent'] = 'Test notification sent';
$string['notificationpreferences'] = 'Notification preferences';
$string['updatepreferences'] = 'Update preferences';
$string['preferencesupdate'] = 'Preferences updated';
$string['defaultpreferences'] = 'Default preferences';
$string['resetpreferences'] = 'Reset preferences';
$string['preferencesreset'] = 'Preferences reset';

// Additional inventory strings
$string['scanqrcode'] = 'Scan QR code';
$string['startscanner'] = 'Start scanner';
$string['stopscanner'] = 'Stop scanner';
$string['equipmentdetails'] = 'Equipment details';
$string['manualentry'] = 'Manual entry';
$string['equipmentuuid'] = 'Equipment UUID';
$string['lookup'] = 'Lookup';
$string['recenttransactions'] = 'Recent transactions';
$string['description'] = 'Description';
$string['notificationchannel'] = 'Notification channel';
$string['notificationchannels'] = 'Notification channels';
$string['primarychannel'] = 'Primary channel';
$string['secondarychannel'] = 'Secondary channel';
$string['fallbackchannel'] = 'Fallback channel';
$string['channelpreferences'] = 'Channel preferences';
$string['channelconfiguration'] = 'Channel configuration';
$string['channelstatus'] = 'Channel status';
$string['channelactive'] = 'Channel active';
$string['channelinactive'] = 'Channel inactive';
$string['channelerror'] = 'Channel error';
$string['channeltest'] = 'Channel test';
$string['channeltested'] = 'Channel tested';
$string['channelvalidation'] = 'Channel validation';
$string['channelvalidated'] = 'Channel validated';
$string['channelinvalid'] = 'Channel invalid';
$string['channelsetup'] = 'Channel setup';
$string['channelconfigured'] = 'Channel configured';
$string['channelmisconfigured'] = 'Channel misconfigured';
$string['channelnotconfigured'] = 'Channel not configured';
$string['configurechannel'] = 'Configure channel';
$string['reconfigureChannel'] = 'Reconfigure channel';
$string['disablechannel'] = 'Disable channel';
$string['enablechannel'] = 'Enable channel';
$string['channeldisabled'] = 'Channel disabled';
$string['channelenabled'] = 'Channel enabled';
$string['channelremoved'] = 'Channel removed';
$string['removechannel'] = 'Remove channel';
$string['addchannel'] = 'Add channel';
$string['channeladded'] = 'Channel added';
$string['channelexists'] = 'Channel already exists';
$string['channelnotfound'] = 'Channel not found';
$string['channelrequired'] = 'Channel required';
$string['channeloptional'] = 'Channel optional';
$string['channeldefault'] = 'Default channel';
$string['channelcustom'] = 'Custom channel';
$string['channeltype'] = 'Channel type';
$string['channeltypes'] = 'Channel types';
$string['channelname'] = 'Channel name';
$string['channeldescription'] = 'Channel description';
$string['channelparameters'] = 'Channel parameters';
$string['channelsettings'] = 'Channel settings';
$string['channeloptions'] = 'Channel options';
$string['channelfeatures'] = 'Channel features';
$string['channelcapabilities'] = 'Channel capabilities';
$string['channellimitations'] = 'Channel limitations';
$string['channelrequirements'] = 'Channel requirements';
$string['channelcompatibility'] = 'Channel compatibility';
$string['channelintegration'] = 'Channel integration';
$string['channelapi'] = 'Channel API';
$string['channelapikey'] = 'Channel API key';
$string['channelapiurl'] = 'Channel API URL';
$string['channelapiversion'] = 'Channel API version';
$string['channelapiformat'] = 'Channel API format';
$string['channelapimethod'] = 'Channel API method';
$string['channelapiheaders'] = 'Channel API headers';
$string['channelapipayload'] = 'Channel API payload';
$string['channelapiresponse'] = 'Channel API response';
$string['channelapierror'] = 'Channel API error';
$string['channelapisuccess'] = 'Channel API success';
$string['channelapifailure'] = 'Channel API failure';
$string['channelapitimeout'] = 'Channel API timeout';
$string['channelapiretry'] = 'Channel API retry';
$string['channelapilimit'] = 'Channel API limit';
$string['channelapiquota'] = 'Channel API quota';
$string['channelapiusage'] = 'Channel API usage';
$string['channelapicost'] = 'Channel API cost';
$string['channelapibilling'] = 'Channel API billing';
$string['channelapiplan'] = 'Channel API plan';
$string['channelapisubscription'] = 'Channel API subscription';
$string['channelapiaccount'] = 'Channel API account';
$string['channelapiprofile'] = 'Channel API profile';
$string['channelapitoken'] = 'Channel API token';
$string['channelapiauth'] = 'Channel API authentication';
$string['channelapiauthorization'] = 'Channel API authorization';
$string['channelapipermissions'] = 'Channel API permissions';
$string['channelapiaccess'] = 'Channel API access';
$string['channelapisecurity'] = 'Channel API security';
$string['channelapiencryption'] = 'Channel API encryption';
$string['channelapissl'] = 'Channel API SSL';
$string['channelapitls'] = 'Channel API TLS';
$string['channelapicertificate'] = 'Channel API certificate';
$string['channelapivalidation'] = 'Channel API validation';
$string['channelapiverification'] = 'Channel API verification';
$string['channelapiconfirmation'] = 'Channel API confirmation';
$string['channelapinotification'] = 'Channel API notification';
$string['channelapialert'] = 'Channel API alert';
$string['channelapiwarning'] = 'Channel API warning';
$string['channelapiinfo'] = 'Channel API info';
$string['channelapidebug'] = 'Channel API debug';
$string['channelapilog'] = 'Channel API log';
$string['channelapihistory'] = 'Channel API history';
$string['channelapiaudit'] = 'Channel API audit';
$string['channelapireport'] = 'Channel API report';
$string['channelapianalytics'] = 'Channel API analytics';
$string['channelapimetrics'] = 'Channel API metrics';
$string['channelapistatistics'] = 'Channel API statistics';
$string['channelapiperformance'] = 'Channel API performance';
$string['channelapimonitoring'] = 'Channel API monitoring';
$string['channelapihealthcheck'] = 'Channel API health check';
$string['channelapistatus'] = 'Channel API status';
$string['channelapiuptime'] = 'Channel API uptime';
$string['channelapidowntime'] = 'Channel API downtime';
$string['channelapimaintenance'] = 'Channel API maintenance';
$string['channelapiupgrade'] = 'Channel API upgrade';
$string['channelapiupdate'] = 'Channel API update';
$string['channelapimigration'] = 'Channel API migration';
$string['channelapibackup'] = 'Channel API backup';
$string['channelapirestore'] = 'Channel API restore';
$string['channelapirecovery'] = 'Channel API recovery';
$string['channelapidisaster'] = 'Channel API disaster recovery';
$string['channelapibusinesscontinuity'] = 'Channel API business continuity';
$string['channelapicontingency'] = 'Channel API contingency';
$string['channelapiemergency'] = 'Channel API emergency';
$string['channelapiincident'] = 'Channel API incident';
$string['channelapiproblem'] = 'Channel API problem';
$string['channelapiissue'] = 'Channel API issue';
$string['channelapiticket'] = 'Channel API ticket';
$string['channelapisupport'] = 'Channel API support';
$string['channelapihelp'] = 'Channel API help';
$string['channelapidocumentation'] = 'Channel API documentation';
$string['channelapimanual'] = 'Channel API manual';
$string['channelapiguide'] = 'Channel API guide';
$string['channelapitutorial'] = 'Channel API tutorial';
$string['channelapiexample'] = 'Channel API example';
$string['channelapisample'] = 'Channel API sample';
$string['channelapitemplate'] = 'Channel API template';
$string['channelapiboilerplate'] = 'Channel API boilerplate';
$string['channelapistarter'] = 'Channel API starter';
$string['channelapiskeleton'] = 'Channel API skeleton';
$string['channelapiframework'] = 'Channel API framework';
$string['channelapilibrary'] = 'Channel API library';
$string['channelapipackage'] = 'Channel API package';
$string['channelapimodule'] = 'Channel API module';
$string['channelapicomponent'] = 'Channel API component';
$string['channelapiwidget'] = 'Channel API widget';
$string['channelapiplugin'] = 'Channel API plugin';
$string['channelapiextension'] = 'Channel API extension';
$string['channelapiaddon'] = 'Channel API addon';
$string['channelapiintegration'] = 'Channel API integration';
$string['channelapiconnector'] = 'Channel API connector';
$string['channelapiadapter'] = 'Channel API adapter';
$string['channelapibridge'] = 'Channel API bridge';
$string['channelapigateway'] = 'Channel API gateway';
$string['channelapiproxy'] = 'Channel API proxy';
$string['channelapimiddleware'] = 'Channel API middleware';
$string['channelapiinterceptor'] = 'Channel API interceptor';
$string['channelapifilter'] = 'Channel API filter';
$string['channelapihandler'] = 'Channel API handler';
$string['channelapiprocessor'] = 'Channel API processor';
$string['channelapicontroller'] = 'Channel API controller';
$string['channelapiservice'] = 'Channel API service';
$string['channelapimanager'] = 'Channel API manager';
$string['channelapiprovider'] = 'Channel API provider';
$string['channelapifactory'] = 'Channel API factory';
$string['channelapibuilder'] = 'Channel API builder';
$string['channelapicreator'] = 'Channel API creator';
$string['channelapigenerator'] = 'Channel API generator';
$string['channelapiparser'] = 'Channel API parser';
$string['channelapivalidator'] = 'Channel API validator';
$string['channelapisanitizer'] = 'Channel API sanitizer';
$string['channelapinormalizer'] = 'Channel API normalizer';
$string['channelapitransformer'] = 'Channel API transformer';
$string['channelapiconverter'] = 'Channel API converter';
$string['channelapimapper'] = 'Channel API mapper';
$string['channelapirenderer'] = 'Channel API renderer';
$string['channelapiformatter'] = 'Channel API formatter';
$string['channelapiencoder'] = 'Channel API encoder';
$string['channelapidecoder'] = 'Channel API decoder';
$string['channelapicompressor'] = 'Channel API compressor';
$string['channelapidecompressor'] = 'Channel API decompressor';
$string['channelapiencryptor'] = 'Channel API encryptor';
$string['channelapidecryptor'] = 'Channel API decryptor';
$string['channelapisigner'] = 'Channel API signer';
$string['channelapiverifier'] = 'Channel API verifier';
$string['channelapiauthenticator'] = 'Channel API authenticator';
$string['channelapiauthorizer'] = 'Channel API authorizer';
$string['channelapiauditor'] = 'Channel API auditor';
$string['channelapilogger'] = 'Channel API logger';
$string['channelapimonitor'] = 'Channel API monitor';
$string['channelapitracker'] = 'Channel API tracker';
$string['channelapicollector'] = 'Channel API collector';
$string['channelapianalyzer'] = 'Channel API analyzer';
$string['channelapireporter'] = 'Channel API reporter';
$string['channelapinotifier'] = 'Channel API notifier';
$string['channelapialerter'] = 'Channel API alerter';
$string['channelapiwarner'] = 'Channel API warner';
$string['channelapiinformer'] = 'Channel API informer';
$string['channelapidebugger'] = 'Channel API debugger';
$string['channelapiprofiler'] = 'Channel API profiler';
$string['channelapibenchmarker'] = 'Channel API benchmarker';
$string['channelapitester'] = 'Channel API tester';
$string['channelapivalidater'] = 'Channel API validater';
$string['channelapimockr'] = 'Channel API mocker';
$string['channelapistubber'] = 'Channel API stubber';
$string['channelapifaker'] = 'Channel API faker';
$string['channelapisimulator'] = 'Channel API simulator';
$string['channelapiemulator'] = 'Channel API emulator';

// Inventory Management System strings
$string['inventory'] = 'Inventory';
$string['manageinventory'] = 'Manage inventory';
$string['inventorymanagement'] = 'Inventory management';
$string['inventorydashboard'] = 'Inventory dashboard';
$string['generateqr'] = 'Generate QR codes';
$string['generateqrcodes'] = 'Generate QR codes';
$string['scanqr'] = 'Scan QR code';
$string['checkin'] = 'Check in';
$string['checkout'] = 'Check out';
$string['checkinout'] = 'Check in/out';
$string['checkinequipment'] = 'Check in equipment';
$string['checkoutequipment'] = 'Check out equipment';
$string['equipmentitem'] = 'Equipment item';
$string['equipmentitems'] = 'Equipment items';
$string['product'] = 'Product';
$string['products'] = 'Products';
$string['productcatalog'] = 'Product catalog';
$string['manageproducts'] = 'Manage products';
$string['addproduct'] = 'Add product';
$string['editproduct'] = 'Edit product';
$string['deleteproduct'] = 'Delete product';
$string['productname'] = 'Product name';
$string['productdescription'] = 'Product description';
$string['manufacturer'] = 'Manufacturer';
$string['model'] = 'Model';
$string['category'] = 'Category';
$string['isconsumable'] = 'Is consumable';
$string['consumable'] = 'Consumable';
$string['nonconsumable'] = 'Non-consumable';
$string['location'] = 'Location';
$string['locations'] = 'Locations';
$string['managelocations'] = 'Manage locations';
$string['addlocation'] = 'Add location';
$string['editlocation'] = 'Edit location';
$string['deletelocation'] = 'Delete location';
$string['locationname'] = 'Location name';
$string['locationdescription'] = 'Location description';
$string['zone'] = 'Zone';
$string['uuid'] = 'UUID';
$string['serialnumber'] = 'Serial number';
$string['status'] = 'Status';
$string['available'] = 'Available';
$string['checkedout'] = 'Checked out';
$string['intransit'] = 'In transit';
$string['maintenance'] = 'Maintenance';
$string['damaged'] = 'Damaged';
$string['lost'] = 'Lost';
$string['conditionstatus'] = 'Condition status';
$string['excellent'] = 'Excellent';
$string['good'] = 'Good';
$string['fair'] = 'Fair';
$string['poor'] = 'Poor';
$string['needsrepair'] = 'Needs repair';
$string['conditionnotes'] = 'Condition notes';
$string['lasttested'] = 'Last tested';
$string['currentuser'] = 'Current user';
$string['studentlabel'] = 'Student label';
$string['transferdestination'] = 'Transfer destination';
$string['configuration'] = 'Configuration';
$string['configurations'] = 'Configurations';
$string['manageconfigurations'] = 'Manage configurations';
$string['addconfiguration'] = 'Add configuration';
$string['editconfiguration'] = 'Edit configuration';
$string['deleteconfiguration'] = 'Delete configuration';
$string['configurationname'] = 'Configuration name';
$string['configurationcode'] = 'Configuration code';
$string['configurationdescription'] = 'Configuration description';
$string['quantityperstudent'] = 'Quantity per student';
$string['isrequired'] = 'Is required';
$string['sortorder'] = 'Sort order';
$string['courseconfigurations'] = 'Course configurations';
$string['assignconfiguration'] = 'Assign configuration';
$string['transaction'] = 'Transaction';
$string['transactions'] = 'Transactions';
$string['transactiontype'] = 'Transaction type';
$string['transactionlog'] = 'Transaction log';
$string['viewtransactions'] = 'View transactions';
$string['fromuser'] = 'From user';
$string['touser'] = 'To user';
$string['fromlocation'] = 'From location';
$string['tolocation'] = 'To location';
$string['processedby'] = 'Processed by';
$string['notes'] = 'Notes';
$string['conditionbefore'] = 'Condition before';
$string['conditionafter'] = 'Condition after';
$string['pickupexchange'] = 'Pickup/exchange';
$string['timestamp'] = 'Timestamp';
$string['allocation'] = 'Allocation';
$string['allocations'] = 'Allocations';
$string['allocated'] = 'Allocated';
$string['assigned'] = 'Assigned';
$string['returned'] = 'Returned';
$string['allocatedby'] = 'Allocated by';
$string['assignedby'] = 'Assigned by';
$string['quantity'] = 'Quantity';
$string['qrcode'] = 'QR code';
$string['qrcodes'] = 'QR codes';
$string['printablesheet'] = 'Printable sheet';
$string['generatesheet'] = 'Generate sheet';
$string['qrcodegenerator'] = 'QR code generator';
$string['numberofcodes'] = 'Number of codes';
$string['printsheet'] = 'Print sheet';
$string['qrcodesheetgenerated'] = 'QR code sheet generated successfully';
$string['scanequipment'] = 'Scan equipment';
$string['scantoidentify'] = 'Scan QR code to identify equipment';
$string['equipmentnotfound'] = 'Equipment not found';
$string['equipmentfound'] = 'Equipment found';
$string['selectstudent'] = 'Select student';
$string['selectuser'] = 'Select user';
$string['checkinsuccessful'] = 'Check-in successful';
$string['checkoutsuccessful'] = 'Check-out successful';
$string['transfersuccessful'] = 'Transfer successful';
$string['equipmentalreadycheckedout'] = 'Equipment is already checked out';
$string['equipmentalreadycheckedin'] = 'Equipment is already checked in';
$string['equipmentnotavailable'] = 'Equipment is not available for checkout';
$string['invalidequipmentid'] = 'Invalid equipment ID';
$string['invaliduserid'] = 'Invalid user ID';
$string['invalidlocationid'] = 'Invalid location ID';
$string['transfer'] = 'Transfer';
$string['transferequipment'] = 'Transfer equipment';
$string['initiatetransfer'] = 'Initiate transfer';
$string['completetransfer'] = 'Complete transfer';
$string['transferinitiated'] = 'Transfer initiated successfully';
$string['transfercompleted'] = 'Transfer completed successfully';
$string['itemsintransit'] = 'Items in transit';
$string['transferwarning'] = 'Transfer warning';
$string['locationmismatch'] = 'Location mismatch detected';
$string['forcelocationcorrection'] = 'Force location correction';
$string['stocklevel'] = 'Stock level';
$string['stocklevels'] = 'Stock levels';
$string['lowstock'] = 'Low stock';
$string['outofstock'] = 'Out of stock';
$string['stockalert'] = 'Stock alert';
$string['stockmonitoring'] = 'Stock monitoring';
$string['availablestock'] = 'Available stock';
$string['allocatedstock'] = 'Allocated stock';
$string['totalstock'] = 'Total stock';
$string['reorderpoint'] = 'Reorder point';
$string['reorderalert'] = 'Reorder alert';
$string['inventoryreports'] = 'Inventory reports';
$string['equipmentreports'] = 'Equipment reports';
$string['usagereport'] = 'Usage report';
$string['locationreport'] = 'Location report';
$string['conditionreport'] = 'Condition report';
$string['allocationreport'] = 'Allocation report';
$string['transactionreport'] = 'Transaction report';
$string['generatereport'] = 'Generate report';
$string['exportreport'] = 'Export report';
$string['reportgenerated'] = 'Report generated successfully';
$string['uuidhistory'] = 'UUID history';
$string['viewuuidhistory'] = 'View UUID history';
$string['activeuuid'] = 'Active UUID';
$string['inactiveuuid'] = 'Inactive UUID';
$string['deactivateuuid'] = 'Deactivate UUID';
$string['deactivatedreason'] = 'Deactivated reason';
$string['uuiddeactivated'] = 'UUID deactivated successfully';
$string['regenerateuuid'] = 'Regenerate UUID';
$string['newuuidgenerated'] = 'New UUID generated successfully';
$string['labelprinter'] = 'Label printer';
$string['printlabel'] = 'Print label';
$string['labelprinted'] = 'Label printed successfully';
$string['labelprinternotavailable'] = 'Label printer not available';
$string['labelprinteroffline'] = 'Label printer is offline';
$string['labelprinterror'] = 'Label printer error';
$string['queuelabelprint'] = 'Queue label for printing';
$string['labelqueuedforprinting'] = 'Label queued for printing';
$string['bulkoperations'] = 'Bulk operations';
$string['bulkcheckin'] = 'Bulk check-in';
$string['bulkcheckout'] = 'Bulk check-out';
$string['bulktransfer'] = 'Bulk transfer';
$string['selectitems'] = 'Select items';
$string['selecteditems'] = 'Selected items';
$string['bulkoperationsuccessful'] = 'Bulk operation completed successfully';
$string['bulkoperationfailed'] = 'Bulk operation failed';
$string['partiallysuccessful'] = 'Partially successful';
$string['offlinemode'] = 'Offline mode';
$string['offlinesync'] = 'Offline sync';
$string['syncpending'] = 'Sync pending';
$string['syncsuccessful'] = 'Sync successful';
$string['syncfailed'] = 'Sync failed';
$string['queuedtransactions'] = 'Queued transactions';
$string['syncqueuedtransactions'] = 'Sync queued transactions';
$string['conflictresolution'] = 'Conflict resolution';
$string['resolveconflicts'] = 'Resolve conflicts';
$string['conflictsresolved'] = 'Conflicts resolved successfully';
$string['facultydashboard'] = 'Faculty dashboard';
$string['studentsneedingequipment'] = 'Students needing equipment';
$string['equipmentready'] = 'Equipment ready';
$string['markasready'] = 'Mark as ready';
$string['equipmentreadyforpickup'] = 'Equipment ready for pickup';
$string['noequipmentneeded'] = 'No equipment needed';
$string['allequipmentassigned'] = 'All equipment assigned';
$string['pendingassignments'] = 'Pending assignments';
$string['completedassignments'] = 'Completed assignments';
$string['equipmenthistory'] = 'Equipment history';
$string['viewhistory'] = 'View history';
$string['equipmentlifecycle'] = 'Equipment lifecycle';
$string['acquisitiondate'] = 'Acquisition date';
$string['retirementdate'] = 'Retirement date';
$string['totalusage'] = 'Total usage';
$string['maintenancehistory'] = 'Maintenance history';
$string['maintenanceschedule'] = 'Maintenance schedule';
$string['schedulemaintenance'] = 'Schedule maintenance';
$string['maintenancecompleted'] = 'Maintenance completed';
$string['maintenancedue'] = 'Maintenance due';
$string['maintenanceoverdue'] = 'Maintenance overdue';
$string['equipmentcondition'] = 'Equipment condition';
$string['conditionassessment'] = 'Condition assessment';
$string['assesscondition'] = 'Assess condition';
$string['conditionupdated'] = 'Condition updated successfully';
$string['damageassessment'] = 'Damage assessment';
$string['reportdamage'] = 'Report damage';
$string['damagereported'] = 'Damage reported successfully';
$string['repairneeded'] = 'Repair needed';
$string['repairinprogress'] = 'Repair in progress';
$string['repaircompleted'] = 'Repair completed';
$string['replacementneeded'] = 'Replacement needed';
$string['equipmentreplaced'] = 'Equipment replaced';
$string['disposalrequired'] = 'Disposal required';
$string['equipmentdisposed'] = 'Equipment disposed';
$string['inventorysummary'] = 'Inventory summary';
$string['totalitems'] = 'Total items';
$string['availableitems'] = 'Available items';
$string['checkedoutitems'] = 'Checked out items';
$string['intransititems'] = 'In transit items';
$string['maintenanceitems'] = 'Maintenance items';
$string['damageditems'] = 'Damaged items';
$string['lostitems'] = 'Lost items';
$string['inventoryvalue'] = 'Inventory value';
$string['totalvalue'] = 'Total value';
$string['availablevalue'] = 'Available value';
$string['checkedoutvalue'] = 'Checked out value';
$string['depreciatedvalue'] = 'Depreciated value';
$string['replacementcost'] = 'Replacement cost';
$string['inventoryturns'] = 'Inventory turns';
$string['utilizationrate'] = 'Utilization rate';
$string['averagecheckouttime'] = 'Average checkout time';
$string['peakusageperiods'] = 'Peak usage periods';
$string['demandforecasting'] = 'Demand forecasting';
$string['seasonaltrends'] = 'Seasonal trends';
$string['usagepatterns'] = 'Usage patterns';
$string['equipmentpopularity'] = 'Equipment popularity';
$string['mostusedequipment'] = 'Most used equipment';
$string['leastusedequipment'] = 'Least used equipment';
$string['equipmentefficiency'] = 'Equipment efficiency';
$string['costperuse'] = 'Cost per use';
$string['returnrate'] = 'Return rate';
$string['lossrate'] = 'Loss rate';
$string['damagerate'] = 'Damage rate';
$string['maintenancecost'] = 'Maintenance cost';
$string['operationalcost'] = 'Operational cost';
$string['totalcostofownership'] = 'Total cost of ownership';
$string['roianalysis'] = 'ROI analysis';
$string['costbenefit'] = 'Cost benefit';
$string['noteupdate'] = 'Note update';
$string['budgetplanning'] = 'Budget planning';
$string['procurementplanning'] = 'Procurement planning';
$string['capacityplanning'] = 'Capacity planning';
$string['resourceallocation'] = 'Resource allocation';
$string['optimizationrecommendations'] = 'Optimization recommendations';
$string['performancemetrics'] = 'Performance metrics';
$string['kpidashboard'] = 'KPI dashboard';
$string['alertsandnotifications'] = 'Alerts and notifications';
$string['systemalerts'] = 'System alerts';
$string['usernotifications'] = 'User notifications';
$string['emailnotifications'] = 'Email notifications';
$string['smsnotifications'] = 'SMS notifications';
$string['pushnotifications'] = 'Push notifications';
$string['notificationsettings'] = 'Notification settings';
$string['alertthresholds'] = 'Alert thresholds';
$string['escalationrules'] = 'Escalation rules';
$string['notificationtemplates'] = 'Notification templates';
$string['customnotifications'] = 'Custom notifications';
$string['automatednotifications'] = 'Automated notifications';
$string['manualnotifications'] = 'Manual notifications';
$string['notificationhistory'] = 'Notification history';
$string['deliverystatus'] = 'Delivery status';
$string['notificationdelivered'] = 'Notification delivered';
$string['notificationfailed'] = 'Notification failed';
$string['notificationpending'] = 'Notification pending';
$string['retrynotification'] = 'Retry notification';
$string['notificationretried'] = 'Notification retried';
$string['suppressnotifications'] = 'Suppress notifications';
$string['notificationssuppressed'] = 'Notifications suppressed';
$string['enablenotifications'] = 'Enable notifications';
$string['notificationsenabled'] = 'Notifications enabled';
$string['testnotification'] = 'Test notification';
$string['notificationtestsent'] = 'Test notification sent';
$string['notificationpreferences'] = 'Notification preferences';
$string['updatepreferences'] = 'Update preferences';
$string['preferencesupdate'] = 'Preferences updated';
$string['defaultpreferences'] = 'Default preferences';
$string['resetpreferences'] = 'Reset preferences';
$string['preferencesreset'] = 'Preferences reset';
$string['upc'] = 'UPC';
$string['upccode'] = 'UPC code';
$string['scanupccode'] = 'Scan UPC code';
$string['productdetails'] = 'Product details';
$string['productimage'] = 'Product image';
$string['productimage_help'] = 'Upload an image for this product. Supported formats: JPG, PNG, GIF. Maximum size: 10MB.';
$string['removeimage'] = 'Remove image';
$string['additems'] = 'Add items';
$string['additemstoinventory'] = 'Add items to inventory';
$string['removeitems'] = 'Remove items';
$string['removeitemsfromInventory'] = 'Remove items from inventory';
$string['scanupctoadd'] = 'Scan UPC to add items';
$string['manualupcentry'] = 'Manual UPC entry';
$string['sessioncount'] = 'Session count';
$string['sessionsummary'] = 'Session summary';
$string['printqrforsessionitems'] = 'Print QR codes for session items';
$string['itemaddedsuccessfully'] = 'Item added successfully';
$string['productnotfound'] = 'Product not found';
$string['addproductfirst'] = 'Add product first';
$string['scannerinterfaceplaceholder'] = 'QR/Barcode scanner will be implemented here';
$string['removal'] = 'Removal';
$string['removed'] = 'Removed';
$string['removalreason'] = 'Removal reason';
$string['removaldate'] = 'Removal date';
$string['removedby'] = 'Removed by';
$string['removeitem'] = 'Remove item';
$string['itemremoved'] = 'Item removed successfully';
$string['equipmentalreadyremoved'] = 'This equipment has already been removed from inventory';
$string['reasonforremoving'] = 'Reason for removing';
$string['endoflife'] = 'End of life';
$string['disposed'] = 'Disposed';
$string['stolen'] = 'Stolen';
$string['returnedtovendor'] = 'Returned to vendor';

// Scanning interface strings
$string['scanequipment'] = 'Scan equipment';
$string['scanequipment_desc'] = 'Use this interface to scan QR codes and barcodes for equipment management.';
$string['camerascan'] = 'Camera scan';
$string['camerascan_instructions'] = 'Use your device camera to scan QR codes or barcodes.';
$string['uploadscan'] = 'Upload scan';
$string['uploadscan_instructions'] = 'Upload an image containing a QR code or barcode to scan.';
$string['manualscan'] = 'Manual entry';
$string['manualscan_instructions'] = 'Manually enter barcode data if scanning is not available.';
$string['startscan'] = 'Start scanning';
$string['stopscan'] = 'Stop scanning';
$string['selectimage'] = 'Select image';
$string['processimage'] = 'Process image';
$string['barcodedata'] = 'Barcode data';
$string['enterbarcode'] = 'Enter barcode data';
$string['scantype'] = 'Scan type';
$string['autodetect'] = 'Auto-detect';
$string['qrcode'] = 'QR code';
$string['upccode'] = 'UPC code';
$string['processscan'] = 'Process scan';
$string['scanresults'] = 'Scan results';
$string['availableactions'] = 'Available actions';
$string['equipment:checkinout'] = 'Check in/out equipment';

// Additional inventory management strings for templates
$string['additemsdesc'] = 'Add new equipment items to inventory';
$string['removeitemsdesc'] = 'Remove equipment items from inventory';
$string['managelocationsdesc'] = 'Add and manage storage locations';
$string['manageproductsdesc'] = 'Add and manage product catalog';
$string['generateqrdesc'] = 'Generate QR codes for equipment tracking';
$string['checkinoutdesc'] = 'Check equipment in or out using QR codes';
$string['viewtransactionsdesc'] = 'View all equipment transaction history';
$string['managementactions'] = 'Management Actions';
$string['quickactions'] = 'Quick Actions';
$string['recentactivity'] = 'Recent Activity';
$string['lastweek'] = 'Last 7 days';
$string['time'] = 'Time';
$string['type'] = 'Type';
$string['equipment'] = 'Equipment';
$string['user'] = 'User';
$string['location'] = 'Location';
$string['viewalltransactions'] = 'View all transactions';
$string['unexpectederror'] = 'An unexpected error occurred';
$string['itemnotfound'] = 'Equipment item not found';
$string['itemnotavailable'] = 'Equipment item is not available';
$string['itemnotcheckedout'] = 'Equipment item is not checked out';
$string['itemcheckedout'] = 'Equipment item checked out successfully';
$string['itemcheckedin'] = 'Equipment item checked in successfully';
$string['bulkcheckinresults'] = 'Bulk check-in results';
$string['bulkcheckoutresults'] = 'Bulk check-out results';
$string['inventorysystemtest'] = 'Inventory system test';
$string['backtocheckinout'] = 'Back to check in/out';
$string['showingrecords'] = 'Showing records {$a->from} to {$a->to} of {$a->total}';
$string['perpage'] = 'Per page';

// QR code reprint functionality strings
$string['itemdetails'] = 'Item details';
$string['addtoqrqueue'] = 'Add QR code to print queue';
$string['qrcodequeued'] = 'QR code queued for printing';
$string['qrcodequeuefailed'] = 'Failed to add QR code to queue';
$string['qrcodequeuedalready'] = 'QR code already in printing queue';
$string['reprintqrcode'] = 'Re-print QR code';
$string['field'] = 'Field';
$string['value'] = 'Value';
$string['checkedoutto'] = 'Checked out to';
$string['dayscheckedout'] = 'Days checked out';
$string['lasttransaction'] = 'Last transaction';
$string['qrgenerationfailed'] = 'QR code generation failed';
$string['date'] = 'Date';
$string['from'] = 'From';
$string['to'] = 'To';
$string['notransactions'] = 'No transactions found';
$string['recenttransactions'] = 'Recent transactions';
$string['printqueue'] = 'Print queue';
$string['printfromqueue'] = 'Print from queue';
$string['queuecount'] = 'Queue count';
$string['clearqueue'] = 'Clear queue';
$string['queueempty'] = 'Print queue is empty';
$string['markasprinted'] = 'Mark as printed';
$string['removefromqueue'] = 'Remove from queue';
$string['queuemanagement'] = 'Queue management';
$string['printqueueitems'] = 'Print queue items';
$string['condition'] = 'Condition';
$string['timemodified'] = 'Time modified';
$string['clearprinteditems'] = 'Clear printed items';
$string['clearqueueconfirm'] = 'Are you sure you want to clear all printed items from the queue? This action cannot be undone.';
$string['clearedprinteditems'] = 'Successfully cleared {$a} printed items from the queue';
$string['noprinteditemstoclear'] = 'No printed items found to clear';
$string['clearingfailed'] = 'Failed to clear printed items from queue';

// Print queue cleanup strings
$string['cleanupqueuemessage'] = 'Removed {$a->count} QR code(s) from queue (equipment no longer in inventory)';
$string['cleanupqueuesingle'] = 'Removed 1 QR code from queue (equipment no longer in inventory)';
$string['cleanupqueuemultiple'] = 'Removed {$a} QR codes from queue (equipment no longer in inventory)';
$string['queuecleanuperror'] = 'Error occurred during queue cleanup';
$string['orphanedqueueitemsremoved'] = 'Orphaned queue items removed';
$string['queuevalidationperformed'] = 'Queue validation performed';
$string['invalidqueueitemsdetected'] = 'Invalid queue items detected and removed';

// Item details page strings for removed items
$string['item_removed_notice'] = 'This item has been removed from inventory and is no longer available for use.';
$string['action_disabled_item_removed'] = 'Action disabled: item has been removed from inventory';
$string['actions_disabled_explanation'] = 'All equipment actions are disabled because this item has been removed from inventory.';
$string['action_disabled_not_available'] = 'Action disabled: item is not available';
$string['qr_actions_only_available'] = 'QR code actions are only available for equipment with "Available" status.';
$string['backtoinventory'] = 'Back to inventory';
$string['cannot_perform_action_removed_item'] = 'Cannot perform action on equipment that has been removed from inventory.';
$string['can_only_queue_available_items'] = 'Can only add available items to QR print queue.';
$string['can_only_print_available_items'] = 'Can only print QR codes for available items.';

// Removal history strings
$string['removal_history'] = 'Removal History';
$string['view_removal_history'] = 'View removal history';
$string['removalhistory'] = 'Removal history';

// Error messages for equipment removal validation
$string['invalidqrformat'] = 'Invalid QR code format for inventory system';
$string['qrnotfound'] = 'QR code not found in inventory system';
$string['itempreviouslyremoved'] = 'This item was previously removed from inventory';
$string['cannotremovecheckedout'] = 'Cannot remove item that is currently checked out';

// Dashboard navigation strings
$string['partnershipmanagement'] = 'Partnership Management';
$string['viewallpartnerships'] = 'View All Partnerships';
$string['addnewpartnerships'] = 'Add New Partnerships';
$string['addbulkfamilies'] = 'Add Bulk Families';
$string['pickupmanagement'] = 'Pickup Management';
$string['viewallpickups'] = 'View All Pickups';
$string['addnewpickup'] = 'Add New Pickup';
$string['agreementmanagement'] = 'Agreement Management';
$string['viewallagreements'] = 'View All Agreements';
$string['addnewagreement'] = 'Add New Agreement';
$string['inventorymanagement'] = 'Inventory Management';
$string['inventorydashboard'] = 'Inventory Dashboard';
$string['productcatalog'] = 'Product Catalog';
$string['locationmanagement'] = 'Location Management';
$string['additemstoinventory'] = 'Add Items to Inventory';
$string['removeitemsfromventory'] = 'Remove Items from Inventory';
$string['equipmentcheckinout'] = 'Equipment Check-In/Out';
$string['equipmentcheckinonly'] = 'Equipment Check-In Only';
$string['universalscanner'] = 'Universal Scanner';
$string['generateqrcodes'] = 'Generate QR Codes';
$string['equipmentremovalhistory'] = 'Equipment Removal History';
$string['transactionhistory'] = 'Transaction History';
$string['itemdetails'] = 'Item Details';
$string['phonecommunication'] = 'Phone Communication';
$string['phoneverificationsetup'] = 'Phone Verification Setup';
$string['otpverification'] = 'OTP Verification';
$string['testoutgoingtextconfig'] = 'Test Outgoing Text Configuration';
$string['testotpverification'] = 'Test OTP Verification';
$string['virtualcourseconsent'] = 'Virtual Course Consent';
$string['consentform'] = 'Consent Form';
$string['equipmentexchangeselection'] = 'Equipment Exchange Selection';
$string['viewconsentsubmissions'] = 'View Consent Submissions';
$string['masstextmessaging'] = 'Mass Text Messaging';
$string['masstextmessageinterface'] = 'Mass Text Message Interface';
$string['administrativetools'] = 'Administrative Tools';
$string['equipmentpluginsettings'] = 'Equipment Plugin Settings';
$string['developmenttestingtools'] = 'Development & Testing Tools';
$string['basicinventorysystemtest'] = 'Basic Inventory System Test';
$string['comprehensiveinventorytest'] = 'Comprehensive Inventory Test';
$string['qrprintqueuedebugging'] = 'QR Print Queue Debugging';
$string['databasequeuedebugging'] = 'Database Queue Debugging';
$string['developmenttoolswarning'] = 'These tools are for development and debugging purposes only. Use with caution in production environments.';
$string['quickhelp'] = 'Quick Help';
$string['dashboardhelp'] = 'This dashboard provides access to all Equipment plugin functionality. Click on any button to navigate to the specific feature you need.';

// New VCC form strings
$string['theformyouattemptedtoaccessisnotcurrentlyavailable'] = 'The form you attempted to access is not currently available. We think this may be the form you\'re actually trying to access instead, but who knows? We could be wrong.';
$string['incompletestudentdata'] = 'Incomplete student data';
$string['nofutureenrollments'] = 'No future enrollments';
$string['nostudentsenrolled'] = 'No students enrolled';
$string['nostudentsinsystem'] = 'You cannot complete this action because you do not have any students enrolled in our system.';
$string['enrolledstudentswouldshowhere'] = 'Enrolled students would show here, but it looks like you don\'t have any students enrolled.';
$string['agreementsandconsent'] = 'Agreements & Consent';
$string['studentnoenrollments'] = 'No active enrollments found for {$a}.';
$string['somestudentsnottakingcourses'] = 'It looks like you have other students in our system who are not enrolled in any current courses for this year. If this is expected, you can disregard this message. Otherwise, please contact us to get the rest of your students enrolled!';
$string['nousersfound'] = 'No users found';
$string['vccformsettings'] = 'VCC form settings';
$string['vccformsettings_desc'] = 'All settings for the Virtual Course Consent (VCC) form.';
$string['vccformwarning'] = 'VCC form warning';
$string['vccformwarning_desc'] = 'A warning alert box that will appear at the top of the VCC form. This should be used if you want parents filling out the form to be notified of something important.
<br /><br />

Leave this setting blank if you don\'t want anything to appear at the top of the form.';
$string['selectexchangelocation'] = 'Select exchange location';
