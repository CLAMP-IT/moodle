History
=============
Version 3.0.1.1
  1. Updated php4 constructor - #27 - thanks to Mark Sharp.
  2. Added the ability to grey out and unlink grid icons for unavailable sections - #30 - thanks to Justin Hunt.
  3. Travis CI fixes.
  4. Fixes for #30 in IE.

Version 3.0.1
  1. Implement MDL-26226.

Version 3.0.0.4
  1. Use custom user profile fields for accessibility - #23.
  2. Section 0 not shown on its own when in the Grid and 'Show one section per page' - #24.
  3. TravisCI - tidy up.

Version 3.0.0.3
  1. Change colour setting type from 'text' to 'colourtext - 'ftext' -> 'fcolourtext' for theme styling.
  2. Fix logic flaw with named anchor and editing is on.

Version 3.0.0.2
  1. Add Travis CI Support.
  2. Fix #22 - Grid sometimes loads with blocks misaligned.
  3. Show the correct shadebox section in response to a named anchor in the URL of the format:
     "#section-X" where 'X' is the section number.

Version 3.0.0.1
  1. First 'beta' version for M3.0.
  2. Apply MDL-10405, MDL-48947 and MDL-51610.

Version 2.9.1.5.
  1. Do not halt on missing icon error (see: https://moodle.org/mod/forum/discuss.php?d=316768) but just
     continue and allow errors to be stored in the php.log file.

Version 2.9.1.4.
  1. Improved RTL support thanks to Nadav Kavalerchik.
  2. Delete format_grid_icon record when a section is deleted.
  3. Update language string for 'cannotfinduploadedimage'.
  4. Improved accessibility control by finally completing CONTRIB-3240 to use tab / shift tab, enter / shift enter controls.

Version 2.9.1.3.
  1. Fix CONTRIB-5881 - Renaming of items not allowing spacebar.

Version 2.9.1.2.
  1. Fixed coding fault when restoring a course from backup file.
  2. Attempt self repair if grid image not in the files table.

Version 2.9.1.1.
  1. Add the 'space' key as a means of opening / closing the shade box for accessibility.
  2. Attempt to set focus on first activity.
  3. Add composer.json file - #17 - thanks to Daniel Neis Araujo.
  4. Update readme.
  5. Added debug code for: https://moodle.org/mod/forum/discuss.php?d=316768.

Version 2.9.1.
  1. First stable version for Moodle 2.9.

Version 2.9.0.4.
  1. Optimise styles.

Version 2.9.0.3.
  1. Fix 'showavailability', ref: https://moodle.org/mod/forum/discuss.php?d=315452.

Version 2.9.0.2.
  1. Added the capability to delete a section.
  2. Fixed 'Pointless try...catch block in setup_displayed_image() (lib.php)' - #15.
  3. Add option to fit content popup to window size - #16 - thanks to Anthony Kuske - https://github.com/antriver.
  4. Tidied up, added reset options and improved #16.
  5. Optimised 'absolute' positioning of shadebox in light of #16.

Version 2.9.0.1.
  1. First 'release candidate' version for Moodle 2.9.
  2. Note: Need to seriously think about using AMD and transitioning to using jQuery instead of YUI as AJAX is possible in jQuery,
           but almost certainly not in the first instance as YUI is still supported in core.  Modularisation is do-able in AMD
           with parameters required: https://docs.moodle.org/dev/Javascript_Modules.

3rd April 2015 Version 2.8.2.2
  1. Fixed 'importing of grid icons.' - #11 - patch gratefully received from Tim Lock.

25th February 2015 Version 2.8.2.1
  1. Fixed 'Arrows still show when editing is on.' - #13.

21st December 2014 Version 2.8.2
  1. Revamped the arrows such that they are always visible.
  2. Arrow and close image size responsive to screen size.
  3. Changed the arrow images to FontAwesome: http://fontawesome.io/license/ and extracted with: https://icomoon.io/app/#/select.
  4. Added change in cursor for the arrow areas.
  5. Fix CONTRIB-5511: Navigation arrows should be hidden when only one section.

16th November 2014 Version 2.8.1 - Stable version for Moodle 2.8.
  1. Always show shadebox arrows on Tablets and Mobiles with the latter being half size.

10th November 2014 Version 2.8.0.1 - Release Candidate
  1. Release candidate for Moodle 2.8 - NOT for production servers.

5th November 2014 Version 2.7.1.4
  1. Fixed CONTRIB-5256.  Thanks to Marius Jugariu for reporting the problem.
  2. Made the arrows easier to find by placing them always 75px from the top.
  3. Added German 'New activity' image.  Thanks to Guido Hornig.
  4. Fixed section 0 editing drop down in Bootstrap v3 themes: https://github.com/gjb2048/moodle-format_grid/issues/9.

18th August 2014 Version 2.7.1.3
  1. Fixed issue whereby the grid shade box is underneath certain theme components:
     https://moodle.org/mod/forum/discuss.php?d=266192#p1152232.

9th August 2014 Version 2.7.1.2
  1. Fixed CONTRIB-5191.  This is a workaround that depends on MDL-43525 being implemented before it is reversed.

12th June 2014 - Version 2.7.1.1
  1. Slight change to CSS for Bootstrap V3.
  2. Fixed CONTRIB-5106 - thanks to Hanna Edelman for this.

20th May 2014 Version 2.7.1 - Stable.
  1. Stable release for M2.7.

27th April 2014 Version 2.7.0.1 - BETA
  1. First beta version for Moodle 2.7beta.

23rd April 2014 Version 2.6.1.6
Change by G J Barnard
  1. Fixed CONTRIB-5001 - Cannot use the same image in the section summary as well as the grid.
  2. Note: Julian has departed from Moodle work so I have taken over full responsibility for the format.

28th March 2014 Version 2.6.1.5
Change by G J Barnard
  1. Fix to RTL CSS thanks to Nadav Kavalerchik - https://github.com/gjb2048/moodle-courseformat_grid/pull/3 being:
     Right align "New activity" notification image in RTL mode.
  2. Fixed missing strings in 'en' language file for 'One section per page' mode.
  3. Fixed number format on ordered lists as reported on https://moodle.org/mod/forum/discuss.php?d=252497.
  4. Changed current selected image container to #ffc540 from #00cc00.  As the latter was too 'in your face'.
  5. Added a new setting so that you can choose if you want to display the "New activity" notification image.  The default is yes.
  6. Removed colour and font size attribute of icon text from styles.css.
  7. Removed redundant icons.
  8. Fixed box-sizing method for Bootstrap V3 in styles.css.
  9. Slightly shrunk grid icon outside to make better use of available space.
 10. Changed current section colour in the grid to use a box-shadow instead of a background to avoid confusion with
     the current selected section.  This is a compromised as the latter is there for accessibility reasons and hence
     rationally should stand out more.  This change will not be back-ported as it's not IE8 compatible,
     http://caniuse.com/#search=box-shadow and hence requires a version of Moodle that does not mandate it.
 11. Fixed CONTRIB-4861 - Glossary pop-ups do not display correctly.

18th December 2013 Version 2.6.1.4
Change by G J Barnard
  1. Fixed path and message confusion issue with the M1.9 upgrade script.
  2. Fixed issue reported by Graham Woodsford whereby teachers could not create Collapsed Topics courses.  The code in the area
     concerned is identical to the Grid format and hence the same issue affects it too.  This is because the validation method
     'edit_form_validation' in 'lib.php' was failing the values passed to it.  These happened to be the hidden label values from
     'course_format_options' which were being used because the 'Course creator' role that teachers have before becoming an
     'editingteacher' role as defined in 'db/access.php' does not allow the teacher to have the
     'format/grid:changeimagecontainersize', 'format/grid:changeimageresizemethod' and 'format/grid:changeimagecontainerstyle'
     capabilities.  This also implies that the values of the other settings are wrong, which in fact they are, causing courses to
     be created (after fixing the colour settings for 'edit_form_validation') with odd values and not the defaults resulting in no
     icon set etc.  And therefore needing to go back to edit the course settings.

     Ok, this now leads on to a dilemma.  Currently the course creator role does not have the Grid capabilities listed above.  If
     they were added to 'access.php' then the role would have them (existing Grid admins would have to add manually).  Then the
     teacher would see all the options when first creating a course as they do whilst editing.  However, this means that if you
     wish to restrict the teacher from changing things as is the purpose of the capabilities in the first place, then you have
     to remove the capability in both the 'coursecreator' and 'editingteacher' roles.  This is because by default 'coursecreator'
     is above 'editingteacher' and once enrolled on the course after having created it, the teacher has both.  This makes things
     a bit complex and to be honest not that admin friendly.  Therefore to keep things simple in what is in reality an event
     that is rare, I have decided not to add the capabilities to the 'coursecreator' role.  This is additionally based on the
     presumed work-flow of a teacher where they create the course using the defaults, look at it and then decide what to change
     in the settings.  The fix as it stands will facilitate this.

13th December 2013 Version 2.6.1.3 - Zombie release.
Change by G J Barnard
  1. Fixed shade box still being shown in editing mode when update capability is denied.  Historical
     issue that has not been spotted before.  Given http://docs.moodle.org/26/en/Capabilities/moodle/course:update
     I have no idea why that specific capability was in the format for checking editing
     capability in conjunction with actual editing capability in the first place.  Fix
     is to remove the use of it completely which should marginally speed things up a bit.
  2. Fixed sections disappearing when the icon is clicked on in editing mode.
  3. Fixed icon image eventually disappearing when the section name / summary is changed.  See CONTRIB-4784, a
     big thank you to Nadav Kavalerchik for spotting what was the cause of this and thus providing a reliable
     means of replicating the issue.

26th November 2013 Version 2.6.1.2
Change by G J Barnard
  1. Fix automated backups including displayed images when they should not.
  2. Fix 'reset_grid_setting' in 'lib.php' not resetting a course if it's only on default options.
  3. Fix restore using the wrong name for the 'image' field.
  4. Only delete the old image file if it exists after the new one has been successfully
     created in 'setup_displayed_image' in 'lib.php'.
  5. Fix course id set to default for courses created with versions prior to
     13/7/2012 causing an exception to be raised when a section's image record
     cannot be found and yet the record exists already.  This is due to new optimisation
     code relying on using 'courseid' to find the section records for the course in
     one go rather than getting them individually.
  6. Ensure a course sets its settings when it is created and then detaches itself from the
     global defaults.

NOTE: I did find in changing the backup / restore code that the changes did not take hold until I
      restarted the web server (in my case the Apache service) if you encounter a situation where
      the automated backup files are larger than the manual ones, then please restart the web server
      service.

The mystery of the disappearing images as reported on: https://moodle.org/mod/forum/discuss.php?d=244390
by Dan Trockman and by Llywelyn Morgan where overnight the displayed images on old courses
disappear.  This could be caused by the fact that the backup mechanism intentionally removes the displayed
images so that they are not in the backup file which causes issues on restore.  Then the next time the
course is viewed they are automatically regenerated - this is intentional.  But, the automated
functionality calling the code within the format set the course id to '1' being the site course
and not to the course id of the course being backed up (point 1 above addresses this).  And hence
the displayed images for course 1 would be deleted by 'delete_images()', however this would not have
been an issue as there should be no records in the 'format_grid_icon' table for course id '1', but
because of '5' above, old courses had the 'course id' set to '1' and not the true course id of the
course.  And so the method 'delete_images()' deleted them.  This would have been fine as the regeneration
code should have put the images back, but because the code could not find the record containing the image
this did not happen (which '5' above fixes).  But what I cannot explain yet is why with Dan Trockman's
set up the 'database error' ('5') did not manifest itself.  But I hope that fixes '1' and '5' will repair
courses automatically when they are first viewed.  Currently I am unable to work exactly why this
is happening as have been unable to replicate it.  If you encounter the same problem, please let me
know with as much information as possible, like additional add-ons and when you notice it happening.

So, I have decided to release as is as the fixes above are important to distribute to the community.

20th November 2013 Version 2.6.1.1
Change by G J Barnard
  1. Fix incorrect detection of non-existent entry in format_grid_icon table.  Thanks to
     Llywelyn Morgan for reporting this.

18th November 2013 Version 2.6.1
Change by G J Barnard
  1. Stable release for Moodle 2.6.
  2. Fixed slight issue with lack of prefixing '#' for colour settings in default settings.

14th November 2013 Version 2.6.0.1
Change by G J Barnard
  1. Initial BETA code for Moodle 2.6.

14th November 2013 Version 2.5.5
Change by G J Barnard
  1. Fixed issue over prefixed '#' in colour picker.
  2. Added validation to HEX RGB colour codes.
  3. Added US English language file.
  4. Implemented MDL-21097.
  5. Fixed an issue when deleting a course that was not in the grid format where
     the function 'format_grid_delete_course' in 'lib.php' was being called even
     though the course is not in the format and the wrong format object was being
     retrieved.  This then resulted in a method call on a non-existent method.

4th November 2013 Version 2.5.5 - CONTRIB-4099 - BETA
Improvement by G J Barnard
  1. Implemented CONTRIB-4099 to add image icon size and colour changes.
     Gratefully crowd funded by:
       WL Wong
       Guido Gautsch
       Marcel de Leeuwe
       Joseph Thibault
       Gideon Williams
       Jean-Marc Doucet
       Nigel Barnett
  2. Changes for 'Accessibility' based upon MDL-41252.
  3. Fully implemented MDL-39542.
  4. Started implementing WAI-ARIA roles - http://www.w3.org/TR/wai-aria/roles.  If you have information to
     help with this, then please contact us.

23rd October 2013 Version 2.5.4.4 - Stable.
Change by G J Barnard
  1. Fix broken call to '_is_empty_text' as reported on CONTRIB-4589.
  2. Optimised fetching of icon data from database to one call instead of one call per shown section.
  3. Changed icon border size and radius to provide greater definition to the icon.
  4. Fixed shade box showing when pressing the 'Esc' key in 'Show one section per page' course layout - CONTRIB-4709.
  5. Fixed highlight this section text referring to 'topics'.

2nd October 2013 Version 2.5.4.3 - Stable.
Change by G J Barnard
  1. Fixed sections not being shown when in 'Show one section per page' mode and editing.  Thanks to
     Zdravko Stoimenov for reporting this.
  2. Changed 'editimage.php' to ensure that only the icon is removed when changing it.  No specific
     issue just refactoring the code as a preventative measure.
  3. Fixed section 0 content displaying when it's in the grid, you first load a page and click on another
     section.  Thanks to Llywelyn Morgan for reporting this.

12th September 2013 Version 2.5.4.2 - Stable.
Change by G J Barnard
  1. Changed 'JSON' code in 'module.js' to use the YUI library for JSON to support situations where the 'JSON'
     library is not built into the browser.  Thanks to Colin Taylor for providing information of a situation I
     could not have possibly tested.
Note: If you have already installed V2.5.4 or V2.5.4.1 then this is not an essential upgrade.

12th September 2013 Version 2.5.4.1 - Stable.
Change by G J Barnard
  1. Commented out 'console.log' code in 'module.js'.
  2. Removed old 'gridkeys.js' from 'javascript' folder.
Note: If you have already installed V2.5.4 then this is not an essential upgrade.

10th September 2013 Version 2.5.4 - Stable.
Change by G J Barnard
  1. Partial implementation of CONTRIB-3240.  Thanks to Andrew Nicols for helping with the YUI module code
     on: https://moodle.org/mod/forum/discuss.php?d=237275.
     This means that it is now possible to navigate using the keyboard with the 'left' / 'right' cursor keys
     being used to perform previous section / next section respectively and the 'esc' key to toggle open / closed
     the shade box.  As a bonus of this change I've added in navigation arrows to the shade box which appear when
     you hover over the middle of the sides - cool eh?
     Initially I also added Shift-TAB (previous section) / TAB (next section) / Enter (open shade box) /
     Shift-Enter (close shade box) keys to but after much deliberation (and logic issues) I have decided that until
     WIA-ARIA is fully understood I'll leave them out.  Once much more information is known I'll put them back in.
     Also thanks to Enrico Canale and Darren Britten of La Trobe University for their support and information.

     Note:  If you're wondering where the M2.3 and M2.4 versions are, well I intend to release them a few days after
            this M2.5 version so that any bugs that have not been found can be fixed once without having to re-release
            three versions.
  2. 'module.js' has been completely reworked so that it is efficient and documented.
  3. Added Pirate language.

30th August 2013 Version 2.5.3.3 - Stable
Change by G J Barnard
  1. Implemented CONTRIB-4580 - Highlight current section.
  2. Implemented CONTRIB-4579, thanks to all who helped on https://moodle.org/mod/forum/discuss.php?d=236075.
  3. At the request of Tim St.Clair I've changed the code such that the sections underneath the icons are hidden
     by CSS when JavaScript is enabled so that there is no 'flash' as previously JS would perform the hiding.
  4. Added 'Downgrading' instructions above.
  5. Added 'Upgrading' instructions above.
  6. Added 'Known Issues' above.

22nd August 2013 Version 2.5.3.2 - Stable
Change by G J Barnard
  1. Fixed icon container size relative to icon size.
  2. Added 'alt' image attribute information being that of the section name.
  3. Tidied up more styles such that to pre-empt conflicts.

10th August 2013 Version 2.5.3.1 - Stable
Change by G J Barnard
  1. Fixed CONTRIB-4216 - Error importing quizzes with grid course format.
  2. Fixed CONTRIB-4253 - mdl_log queried too often to generate New Activity tag.  This has been fixed by using the 'course_sections'
     table instead to spot when a new activity / resource has been added since last login.

4th August 2013 Version 2.5.3 - Stable
Change by G J Barnard
  1. Fixed scroll to top when clicking on an icon.  Thanks to Javier Dorfsman for reporting this.
  2. Added in code developed by Nadav Kavalerchik to facilitate multi-lingual support for the 'new activity' icon.  Thank
     you Nadav :).
  3. Adapted the width of the shade box such that it is dynamic against the size of the window.

5th July 2013 Version 2.5.2 - Stable
Change by G J Barnard
  1. Code refactoring to reduce and separate the format as a separate entity.
  2. Corrected as much as possible as detected by 'Code Checker' version 2013060600 release 2.2.7.
  3. Once the first box is shown then the 'Enter' key will toggle the 'current' box hidden and shown.
  4. Changed the order of the history so that the latest change is at the top.

14th May 2013 Version 2.5.1 - Stable
Change by G J Barnard
  1. First stable version for Moodle 2.5 stable.

12th May 2013 - Version 2.5.0.2 - Beta
Change by G J Barnard
  1. Removed '.jumpmenu' from styles.css because of MDL-38907.
  2. Added automatic 'Purge all caches' when upgrading.  If this appears not to work by lack of display etc. then perform a
     manual 'Purge all caches' under 'Home -> Site administration -> Development -> Purge all caches'.
  3. Changes for MDL-39542.

13th April 2013 - Version 2.5.0.1 - Beta version.
Change by G J Barnard
  1. First 'Beta' release for Moodle 2.5 Beta.

24th February 2013 - Version 2.4.1 - Stable version.
Change by G J Barnard
  1. Changes because of MDL-37901.
  2. Invisible section fix for Tim Wilde - https://moodle.org/mod/forum/discuss.php?d=218505#p959249.
  3. This version considered 'Stable' from feedback of Theo Konings on CONTRIB-3534.

21st January 2013 - Version 2.4.0.2 - Alpha version, not for production servers.
Change by G J Barnard
  1. Changes to 'renderer.php' because of MDL-36095 hence requiring Moodle version 2012120301.02 release 2.4.1+ (Build: 20130118) and above.

12th January 2013 - Version 2.5.0.1 - Alpha version, not for production servers.
1. Migrated code to Moodle 2.5 development version.

9th January 2013 - Version 2.4.0.5 - Beta version, not for production servers.
Change by G J Barnard
  1. Fixed issue in editimage.php where the GD library needs to be used for image conversion for transparent PNG's.
  2. Perform a 'Purge all caches' under 'Home -> Site administration -> Development -> Purge all caches' after this is installed.

3rd January 2013 - Version 2.4.0.4 - Beta version, not for production servers.
Change by G J Barnard
  1. Fixed issue where the grid did not function in 'One section per page mode' on the course settings.

21st December 2012 - Version 2.4.0.3 - Beta version, not for production servers.
Change by G J Barnard
  1. Hopefully eliminated BOM issue (http://docs.moodle.org/24/en/UTF-8_and_BOM) that was causing the failure of the images to display.

18th December 2012 - Version 2.4.0.2 - Alpha version, not for production servers.
Change by G J Barnard
  1. Second alpha release for Moodle 2.4

18th December 2012 - Version 2.4.0.1 - Alpha version, not for production servers.
Change by G J Barnard
  1. First alpha release for Moodle 2.4
