Moodle Direct Release Notes
------------------------------------------------------------------------------------
####################################################################################
Date:       2011-Aug-18
Release:    v2011081801

- Refactored Back up and restore to allow duplication of TII classes and assignments
- Added erater / ETS support
- Added additional email notification options in the admin config screen

####################################################################################
Date:		2011-July-29
Release:	v2011072901

- Added support Bulk Download of Submissions in PDF and Original format
- Added feature to download grade report XLS spreadsheet
- Added Multi tutor management screen

####################################################################################
Date:		2010-Nov-19
Release:	v2010111901

- Added support for Moodle groups

####################################################################################
Date:		2010-Oct-26
Release:	v2010102601

- Added pagination to the inbox
- Updated database fields and tables for Oracle support
- Added exclude small matches global assignment setting
- Added support for multi language api calls
- Added French (fr) language string file
- Fixes:
    > Fixed issue where non enrolled students were not displayed in the tutor inbox view
    > Fixed issue where user's resubmissions where incorrectly tagged as anonymous
    > Fixed issue with incorrect / incomplete ordering of anonymous inbox

####################################################################################
Date:		2010-Sept-01
Release:	v2010090101

- Added various changes to add compatibility for Moodle 2.0
	> Refactored table output to support both Moodle 1.9 - 2.0
	> Updated language pack, incorporated help into standard language strings
	> Updated Javascript and CSS file functionality
	> Added Moodle 2.0 Back Up and Restore
	> Changed file storage to use Moodle 2.0 file storage where available
	> Moved images to 'pix' directory instead of 'images'
- Added Backup and Restore for Moodle 1.9

####################################################################################
Date:		2010-June-19
Release:	v2010061901

- Added additional diagnostic logging
- Added Authenticated Proxy support

####################################################################################
Date:		2010-June-12
Release:	v2010061201

- Refactored Inbox SQL queries

####################################################################################
Date:		2010-June-2
Release:	v2010060201

- Added support for UTF-8 intepretation of API return data

####################################################################################
Date:		2010-April-23
Release:	v2010042301

- Removed redundant assignment synching cron functionality
- Now allows resubmission to the same paper ID

####################################################################################
Date: 		2010-April-06
Release:	v2010040601
 
- Provides seamless integration into Turnitin using Moodle workflow 
- Uses an activity module so that we can update Turnitin independently of Moodle 
- Uses real Turnitin accounts to allow users to log directly in to Turnitin (should they need to) 
- Uses a �pull� approach to information and has no �call-backs� to the local VLE 
- Will run behind a fire wall 
- Will handle multi-part assignments (one assignment many files) 
- Sends GradeMark marking information to the Moodle GradeBook 
- Grades are not released until the due date 
- Course recycle will correctly copy forward Turnitin information 
- Tutors can load work on behalf of students 
- Turnitin classes can only have one owner. The class owner is set to the person that created the course in Moodle. Only the class owner will be able to see the assignments when logging in to Native Turnitin. However you can change the class owner from within Moodle if you are an instructor. 

Current Limitations
- Doesn�t manage Moodle groups 
- Does not support Oracle as the Moodle database
- Will not update information changed in Turnitin by users in native Turnitin 
- Backup and restore not yet implemented
- Will not work with assignments created under the framed-in API 
- No support for revision assignment, master classes, GradeMark analytics, translations, Zip file upload, PeerMark, QuickSubmit


