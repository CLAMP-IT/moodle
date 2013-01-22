<?php

$string['pluginname']           = 'TurningTechnologies';
$string['pluginadministration'] = 'TurningTech administration';
$string['turningtech']          = 'TurningTech';
$string['modulename']           = 'TurningTech';
$string['modulenameplural']     = 'TurningTech';
$string['turningtechfieldset']  = 'Custom example fieldset';
$string['turningtechintro']     = 'Introduction';
$string['turningtechname']      = 'TurningTech Activity Name';
$string['turningtech:manage']   = 'Manage TurningTech';
$string['remindermessage']      = 'You have not yet registered a TurningTechnologies device for this course.  Click "Manage my Device IDs" to register a device.';

/*********** ADMIN PAGE **********************/
$string['deviceidformat']                  = 'Device ID Format';
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


/********** MANAGE DEVICE ID PAGE ***********/
$string['deviceid']                   = 'Device ID';
$string['deviceids']                  = 'Device IDs';
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
$string['importformtitle']            = 'Import TurningPoint Session File (TXT) Into Moodle Gradebook';
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
$string['responsewareuserid']         = 'Email Address';
$string['responsewarepassword']       = 'TurningTechnologies Password';
$string['lookupmydeviceid']           = 'Lookup My Device ID';
$string['mustprovideid']              = 'You must provide a ResponseWare user ID';
$string['mustprovidepassword']        = 'You must provide a ResponseWare password';
$string['responsewareheadertext']     = 'Enter your ResponseWare User ID and Password to retrieve your Device ID from ResponseWare';
$string['purgecoursewarning']         = 'NOTE: this is a dangerous operation; it cannot be undone, and deletes every Device ID-to-Student relationship for this Course';
$string['purgecoursedescription']     = 'Only Device IDs registered in just this class can be purged. Device IDs registered to All Courses are only able to be removed by your Moodle System Administrator.';
$string['sendemailreminder']          = 'Send Email to Unregistered Students';
$string['emailhasbeensent']           = 'An email has been sent to students who have not registered their Device ID';
$string['errorsendingemail']          = 'There was an error sending the email reminder!';
$string['toreceivecredit']            = 'To receive credit for your participation in-class, register your TurningTechnologies device.';
$string['responsecard']               = 'ResponseCard';
$string['handheldclickerdevice']      = 'Handheld clicker device';
$string['responseware']               = 'ResponseWare';
$string['onyourowndevice']            = 'Software installed on your own personal laptop, mobile phone, etc.';
$string['myregistereddevice']         = 'My Registered Device';
$string['register']                   = 'Register';
$string['ifyouareusingresponsecard']  = 'If you are using a ResponseCard handheld clicker device';
$string['registeradevice']            = 'Register a Device';
$string['forhelp']                    = 'For help, refer to the <a href="http://www.turningtechnologies.com/studentlounge/" target="_blank">FAQ</a> or call toll-free within the US: 1.866.746.3015';
$string['ifyouareusingresponseware']  = 'If you are using your own personal device (laptop, mobile phone, etc.) with ResponseWare';
$string['responsecardheadertext']     = 'Enter the device ID found on the back of your ResponseCard.';
$string['forgotpassword']             = 'I forgot my password';
$string['register']                   = 'Register';
$string['tocreateanaccount1']         = 'To create an account go to: ';
$string['tocreateanaccount2']         = ' and click on Manage Accounts';
$string['rctype']                     = 'Response Card';
$string['rwtype']                     = 'Response Ware';

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
$string['deviceidinwrongformat']               = 'The Device ID is in the wrong format.';
$string['errorsavingdeviceid']                 = 'Could not save Device ID!';
//$string['deviceidalreadyinuse'] = 'The Device ID is already in use for this course.';
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
$string['couldnotauthenticate']                = 'The ResponseWare User ID/Password credentials could not be validated against {$a}. If you have not registered or have forgotten your password, you can visit {$a}Login.aspx and follow the appropriate links.';
$string['sesionfileimporterror']               = 'Session file could not be imported.';
$string['importedsesionfilenotvalid']          = 'Imported session file is not valid.';
$string['importedsesionfileempty']             = 'Imported session file is empty';

/******* STATUS MESSAGES ************/
$string['gradesavedinescrow'] = 'No user found for the provided device ID.  The grade was saved in escrow.';
$string['deviceidsaved']      = 'Your device has been registered. It would be applicable for all of the courses.';
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
