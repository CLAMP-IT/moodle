<?php
// This file is part of Moodle - http://moodle.org/
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
 * Strings for component 'turningtech', language 'en'
 * @package   mod_turningtech
 * @copyright Turning Technologies
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname']           = 'TurningTechnologies';
$string['pluginnamegeneral']           = 'TurningTechnologies';
$string['pluginadministration'] = 'TurningTech administration';
$string['turningtech']          = 'TurningTech';
$string['modulename']           = 'TurningTech';
$string['modulenameplural']     = 'TurningTech';
$string['turningtechfieldset']  = 'Custom example fieldset';
$string['turningtechintro']     = 'Introduction';
$string['turningtechname']      = 'TurningTech Activity Name';
$string['turningtech:manage']   = 'Manage TurningTech';
$string['remindermessage']      = 'You have not yet registered a TurningTechnologies device for this course.  Click "Manage my Device IDs" to register a device.';
$string['turningtech:addinstance'] = 'Add a new TurningTech instance';
/*********** ADMIN PAGE **********************/
$string['deviceidformat']                  = 'Device ID Format';
$string['deviceidinwrongformatrw']                  = 'ResponseWare registration disabled';
$string['enableresponseware']                  = 'Enable';
$string['disableresponseware']                  = 'Disable';
$string['customresponseware']                  = 'Custom';
$string['displayresponseware']                  = 'Enable or Disable ResponseWare Device';
$string['displayresponsewaredesc']                  = 'Settings to Enable or Disable Response Device';
$string['deviceidformatdescription']       = 'The format that the system will use to store device IDs.';
$string['deviceidformathex']               = 'Hexadecimal';
$string['deviceidformatalpha']             = 'Alphanumeric';
$string['encryptionformat']                = 'Encryption Mode';
$string['encryptionformatdescription']     = 'The cipher mode for user data encryption.';
$string['encryptionformatecb']             = 'ECB';
$string['encryptionformatcbc']             = 'CBC';
$string['emailsettings']                   = 'Email Settings';
$string['reminderemailsubject']            = 'Reminder Email Subject';
$string['reminderemailsubjectdescription'] = 'Enter the subject line of the email that reminds students to register their device IDs.';
$string['remidneremailsubjectdefault']     = 'You must register your Device ID!';
$string['reminderemailbody']               = 'Reminder Email Body';
$string['reminderemailbodydescription']    = 'The body of the email that reminds students to register their device IDs.  You can use "@coursename" to insert the name of the course and "@courselink" to insert the URL of the course.';
$string['reminderemailbodydefault']        = <<<EOF
Your instructor for the course @coursename has chosen to use TurningTechnologies' student response system. You have not yet registered your ResponseCard or ResponseWare device ID. You can log in to the course <a href="@courselink">here</a> and register your device ID to ensure you receive class credit.
EOF;
$string['responsewareprovider']            = 'ResponseWare Provider';
$string['responsewareproviderdescription'] = 'Enter the URL of the ResponseWare provider you wish to use.  You must include the "http://" at the beginning.';
$string['studentf'] = 'First Name';
$string['studentl'] = 'Last Name';
$string['student'] = 'Student Name';
$string['email'] = 'Email Address';
$string['emailsuccess'] = 'Email successfully sent';
$string['emailfail'] = 'There was a problem sending the email. Please contact your system administrator to diagnose the email issue if there are unregistered students.';

/********** MANAGE DEVICE ID PAGE ***********/
$string['deviceid']                   = 'Device ID';
$string['deviceids']                  = 'Device IDs';
$string['deviceidform']               = 'Device ID (6 or 8 characters 0-9, A-F)';
$string['deviceidrwform']               = 'Device ID (8 characters 0-9, A-F)';
$string['devicetype']                 = 'Device Type';
$string['allcourses']                 = 'All courses';
$string['justthiscourse']             = 'Just this course';
$string['importsessionfile']          = 'Import Session File';
$string['exportparticipantlist']      = 'Export Roster in TurningPoint Format';
$string['purgedeviceids']             = 'Purge Device IDs';
$string['nodevicesregistered']        = 'You have not yet registered a TurningTechnologies device.';
$string['editdevicemap']              = 'Edit Device ID';
$string['nostudentsfound']            = 'No students were found for this course.';
$string['assignmenttitle']            = 'Assignment Title';
$string['filetoimport']               = 'File to import';
$string['importformtitle']            = 'Import TurningPoint Session File (TPZX) Into Moodle Gradebook';
$string['overrideallexisting']        = 'Override all existing';
$string['purgecourseheader']          = 'Purge Device Ids for this Course';
$string['purgecourseinstructions']    = 'Click on the checkbox to verify that this is what you want to do, then click on the &quot;Purge&quot; button to continue';
$string['awareofdangers']             = 'I am aware of the dangers of this operation and wish to continue';
$string['purge']                      = 'Purge';
$string['instructions']               = 'Instructions';
$string['youmustconfirm']             = 'You must confirm';
$string['alldevicesincoursepurged']   = 'All Device ID\'s registered in just this class have been purged.';
$string['purgeddevices']         = 'All the registered Device IDs have been purged from all courses.';
$string['purgedinthiscourse']         = 'Purged {$a} Device IDs for this course.';
$string['viewunregistered']           = 'View Unregistered Devices';
$string['nounregistereddevicesfound'] = 'No unregistered devices found';
$string['needanaccount']              = 'Need an account?';
$string['responsewareuserid']         = 'ResponseWare Email Address';
$string['responsewarepassword']       = 'ResponseWare Password';
$string['lookupmydeviceid']           = 'Lookup My Device ID';
$string['mustprovideid']              = 'You must provide a ResponseWare Email Address';
$string['mustprovidepassword']        = 'You must provide a ResponseWare password';
$string['responsewareheadertext']     = 'Enter your ResponseWare Email Address and Password to retrieve your Device ID from ResponseWare.';
$string['purgecoursewarning']         = 'NOTE: this is a dangerous operation; it cannot be undone, and deletes every Device ID-to-Student relationship for this Course';
$string['purgecoursedescription']     = 'Only Device IDs registered in just this class can be purged. Device IDs registered to All Courses are only able to be removed by your Moodle System Administrator.';
$string['sendemailreminder']          = 'Send Email to Unregistered Students';
$string['sendemail']                = 'Email unregistered students';
$string['downloaddata']                = 'Download TurningPoint Participant List';
$string['emailhasbeensent']           = 'An email has been sent to students who have not registered their Device ID';
$string['errorsendingemail']          = 'There was an error sending the email reminder!';
$string['toreceivecredit']            = 'To receive credit for your participation in-class, register your TurningTechnologies device.';
$string['responsecard']               = 'ResponseCard';
$string['handheldclickerdevice']      = 'Handheld clicker device';
$string['responseware']               = 'ResponseWare';
$string['onyourowndevice']            = 'Software installed on your own personal laptop, mobile phone, etc.';
$string['myregistereddevice']         = 'My Registered Devices';
$string['myregdevice']         = 'My Registered Devices';
$string['register']                   = 'Register';
$string['ifyouareusingresponsecard']  = 'If you are using a ResponseCard handheld clicker device';
$string['registeradevice']            = 'Register a Device';
$string['forhelp']                    = 'For help please contact customer support toll-free within the US: 1.866.746.3015 or email <a href="mailto:support@turningtechnologies.com">support@turningtechnologies.com</a>';
$string['ifyouareusingresponseware']  = 'If you are using your own personal device (laptop, mobile phone, etc.) with ResponseWare';
$string['responsecardheadertext']     = 'Enter the 6 or 8 character hexadecimal Device ID located on the back of the ResponseCard<br>  The only possibilities are 0-9, A-F.  The letter \'O\' is not a possible character because it is after F.';
$string['forgotpassword']             = 'I forgot my password';
$string['createaccount']             = 'Create Account';
$string['register']                   = 'Register';
$string['tocreateanaccount1']         = 'To create an account go to: ';
$string['tocreateanaccount2']         = ' and click on Manage Accounts';
$string['rctype']                     = 'ResponseCard';
$string['rwtype']                     = 'ResponseWare';
$string['regrctype']                  = 'Register ResponseCard';
$string['regrwtype']                  = 'Register ResponseWare';
$string['deviceidlabel']              = 'Response Device ID : ';
$string['devicesearchhead']           = 'Please enter Response Device ID in the field provided below.';

/********** DEVICE MAP FORM ***************/
$string['editdevicemap']      = 'Edit DeviceID relationships';
$string['createdevicemap']    = 'Create DeviceID relationship';
$string['appliesto']          = 'Applies to';
$string['deletethisdeviceid'] = 'Delete this Device ID';
$string['deletedevicemap']    = 'Delete Device ID {$a}?';
$string['selectcourse']       = 'Select course';
$string['mustselectcourse']   = 'You must select a course unless the Device ID is for all courses';

/********** BLOCK STRINGS ***************/
$string['blocktitle']              = 'TurningTechnologies';
$string['usingdeviceid']           = 'This course is using the Device ID {$a} for grading purposes.';
$string['nodeviceforthiscourse']   = 'You do not have a Device ID registered for this course.';
$string['managemydevices']         = 'Manage my Device IDs';
$string['manageturningtechcourse'] = 'Administer TurningTechnologies';
$string['searchturningtechcourse'] = 'Turning Technologies Device ID Search Tool';
$string['deviceregistration'] = 'Turning Technologies Device Registration';

/****** ERROR MESSAGES ***********/
$string['nogradeitempermission']               = 'The current user does not have permission to create a new Gradebook Item.';
$string['errorcreatinggradebookitem']          = 'Could not create gradebook item.';
$string['gradebookitemalreadyexists']          = 'A gradebook item with that title already exists.';
$string['missinggradedtofield']                = 'Grade request was missing field {$a->field}.';
$string['couldnotfindgradeitem']               = 'Could not find gradebook item with title {$a->itemTitle}';
$string['errorsavinggradeitemsavedinescrow']   = 'There was an error writing to the gradebook.  This action was saved in the grade escrow.';
$string['cannotoverridegrade']                 = 'Cannot override existing gradebook entry.';
$string['errorsavingescrow']                   = 'Could not save grade item in escrow.';
$string['existingitemnotfound']                = 'Could not find existing gradebook item.';
$string['deviceidinwrongformat']               = 'Device ID Invalid - Device IDs can only be 6 or 8 characters (0-9, A-F)';
$string['deviceidinwrongformatold']               = 'Device ID Invalid - Device IDs can only be 6 or 8 characters (0-9, A-F)';
$string['errorsavingdeviceid']                 = 'Could not save Device ID!';
$string['deviceidcorrectform']        = 'Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)';
$string['websystem']            = 'Web enabled response system';
$string['deviceidalreadyinuse']                = 'The Device ID is already in use.';
$string['courseidincorrect']                   = 'Course ID is incorrect';
$string['couldnotfinddeviceid']                = 'Could not find Device ID association with id {$a}.';
$string['notpermittedtoeditdevicemap']         = 'You do not have permission to edit this Device ID.';
$string['nocourseselectedloadingparticipants'] = 'Tried to get course participants without selecting course.';
$string['errorsavingsessionfile']              = 'Error saving session file.';
$string['couldnotparsesessionfile']            = 'Could not parse session file.';
$string['importfilecontainednogrades']         = 'Imported file contained no grades.';
$string['erroronimport']                       = 'Line {$a->line}: error: {$a->message}';
$string['importcouldnotcomplete']              = 'Import could not complete; the import file had errors.';
$string['couldnotpurge']                       = 'Could not purge Device Ids for this course';
$string['nostudentdatareceived']               = 'No student data received.';
$string['studentidincorrect']                  = 'Student ID is incorrect';
$string['couldnotauthenticate']                = 'The ResponseWare Email address and password do not exist. If you have not registered or have forgotten your password use the appropriate link below.';
$string['sesionfileimporterror']               = 'Session file could not be imported.';
$string['importedsesionfilenotvalid']          = 'Imported session file is not valid.';
$string['importedsesionfileempty']             = 'Imported session file is empty';

/******* STATUS MESSAGES ************/
$string['gradesavedinescrow'] = 'No user found for the provided device ID.  The grade was saved in escrow.';
$string['deviceidsaved']      = 'Your Device ID has been successfully registered for all courses.';
$string['deviceidupdated']      = 'Your Device ID has been successfully updated for all courses.';
$string['successfulimport']   = 'Successfully imported {$a} grade records.';
$string['deviceiddeleted']    = 'Device ID deleted';

/********* SOAP messages *************/
$string['userisnotinstructor']      = "This user is not an instructor for the course.";
$string['siteconnecterror']         = 'Cannot connect to site {$a}';
$string['couldnotgetlistofcourses'] = "Could not get list of courses";
$string['couldnotgetroster']        = 'Could not read roster for course {$a}';
$string['norosterpermission']       = 'User does not have permission to read roster';
$string['getcoursesforteacherdesc'] = "Gets the courses for a given teacher";

/********* Admin search page *************/
$string['usersearch']            = 'TurningTech User Device Search';
$string['studentusername']       = 'Student Username';
$string['mustbe3chars']          = 'Your search must use at least 3 characters';
$string['nostudentsfound']       = 'No matching students found.';
$string['adminpurgeheader']      = 'Purge Device IDs';
$string['admincouldnotpurge']    = 'Could not purge Device IDs';
$string['adminalldevicespurged'] = 'All Device IDs have been purged.';
$string['numberdevicespurged']   = 'Successfully purged {$a} Device IDs';

/********* Support info page *************/
$string['supportinfo']   = 'Support Information';
$string['moduleversion'] = 'Module Version';
