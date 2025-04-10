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
$string['nopartnershipcategoriesfoundforschoolyear'] = 'No partnership categories found for the school year with \'idnumber\' "{$a}".';
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
$string['attnparents_useyouraccount'] = 'ATTENTION, PARENTS! You must be logged into your own, personal {$a} account to fill out this form.';
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
$string['message'] = 'This is a test message to confirm that you have successfully configured your site\'s outgoing mail.  Sent: {$a}';
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
$string['reminder_template_default'] = 'REMINDER: Equipment exchange in {DAYS} days on {DATE} at {TIME}. Location: {LOCATION}.';
$string['reminder_template_days'] = 'Your equipment exchange is scheduled for {DAYS} day(s) from now, on {DATE} at {TIME}. Location: {LOCATION}.';
$string['reminder_template_hours'] = 'REMINDER: Your equipment exchange is TOMORROW ({DATE}) at {TIME}. Location: {LOCATION}. Don\'t forget!';
$string['reminderheader'] = 'Reminder settings';
$string['remindersettings_desc'] = 'Settings for sending reminders to users about upcoming equipment exchange dates.';
$string['reminder_template_days_desc'] = 'Template for the reminder message sent days before an exchange.';
$string['reminder_template_hours_desc'] = 'Template for the reminder message sent hours before an exchange.';
$string['reminder_template_days_default'] = 'REMINDER: Your equipment exchange is in {DAYS} days on {DATE} at {TIME}. Location: {LOCATION}.';
$string['reminder_template_hours_default'] = 'REMINDER: Your equipment exchange is in {HOURS} hour(s) today at {TIME}. Location: {LOCATION}. Don\'t forget!';


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
