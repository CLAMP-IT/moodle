Introduction
============
For a while now I have wanted to create a test suite that is automated.  Upon
realising that Moodle is starting to migrate Behat etc. I consider that this
is a good thing to do.  However, it is complicated.  Additionally the existing
tests are complicated too where they use PHPUnit.  I understand the principle
being close to JUnit but is a learning curve.  Therefore at the current moment
in time I'm going to concentrate on manual test plans and small semi-automated
bespoke unit tests as already started with 'test_image.php'.

This file contains a series of 'test plans' that are performed manually to
prove that the functionality operates correctly.  They are by no means complete.

I welcome any input / assistance in writing automated Behat etc. tests.  If you
wish to fork the project from https://github.com/gjb2048/moodle-courseformat_grid
then please do so, add tests and perform a 'pull' request to contribute to the
format.

References
==========
http://docs.moodle.org/dev/Behat_integration
http://docs.moodle.org/dev/Acceptance_testing

Testing
=======
All the tests assume that you understand how to use Moodle functionality to
perform such things as enrolment and capability management.

Test preparation
================
Create a course and set it to the grid format.  Note down the 'course id'.  The
'id' part of the URL when viewing the course.

Test Plans
==========

Image
-----
This tests that the GD Library is installed, the image conversion code works and
then goes on to confirm that the image is converted for use and the displayed
version is created.

1. Whilst logged in, open a new tab / window and type:
   your moodle installation/course/format/grid/test/test_image.php?=courseid=X

   Where 'your moodle installation' is the start of the Moodle installation URL
   and 'X' is the course id from 'Test preparation'.
2. Observe that the converted image is smaller than the original.
3. Observe that the original image has been resized to the maximum for storage.
4. Observe that the resized original has been converted to a displayed version
   as per the current course settings which are shown.

Restore from a previous version
-------------------------------
1. With the supplied backup file:
   '/test/backup-moodle2-course-21-gt-20131104-1255-nu.mbz'.
2. Restore to a new course and confirm that you see the test image
   '515-797no09sa.jpg' in the second section being called '515-797no09sa'.

Backup / restore from the current version and reset to default
--------------------------------------------------------------
1.  Ensure you have run the 'Image' test with the course setup in
    'Test preparation' and that it contains at least one image.
2.  Change the course settings so that the image width, colours etc. are
    different to the defaults that were used when the course was created.
    The defaults can be seen under 'Site administration' -> 'Plugins' ->
    'Course formats' -> 'Grid format'.
3.  Backup the course.  Confirm you end up with a backup file.
4.  Restore the course to a new course in the same category.  Confirm that
    you have a new course with the same images and settings as you set in step
    '2'.
5.  Go to the course settings and then 'Grid reset options' and reset the 
    'Image container size' for the current course (not 'all'). Confirm that
    the image container size only resets to the defaults.
6.  Go to the course settings and then 'Grid reset options' and reset the 
    'Image resize method' for the current course (not 'all'). Confirm that
    the image resize method only resets to the default.
7.  Go to the course settings and then 'Grid reset options' and reset the 
    'Image container style' for the current course (not 'all'). Confirm that
    the image container style only resets to the default.
8.  Go back to the course you used to create the backup file and confirm that
    its settings are as they were when you backed up the course and not the
    defaults.
9.  Go back to the restored course and change the all of the settings randomly
    to your choice.
10. With the backed up course in another tab on the restored course go to the
    course settings and then 'Grid reset options' and reset the 
    'Image container sizes' for the all courses (the 'all'). Confirm that
    the image container size only resets to the defaults.  Refresh the backed up
    course and confirm the image container size only resets to the defaults.
11. On the restored course go to the course settings and then
    'Grid reset options' and reset the 'Image resize methods' for the all
    courses (the 'all'). Confirm that the image resize method only resets to the
    defaults.  Refresh the backed up course and confirm the image resize method
    only resets to the default.
12. On the restored course go to the course settings and then
    'Grid reset options' and reset the 'Image container styles' for the all
    courses (the 'all'). Confirm that the image container style only resets to
    the defaults.  Refresh the backed up course and confirm the image container
    style only resets to the defaults.

Non-editing functionality - 'Show all sections on one page' course layout
-------------------------------------------------------------------------
1.  Ensure you have run the 'Restore from a previous version' test.
2.  Ensure that the course layout setting is 'Show all sections on one page'.
3.  Confirm that the section one icon is highlighted as the current selected
    section.
4.  Press the 'Esc' key and confirm that the shade box shows for section one.
5.  Press the 'Esc' key again and confirm that the shade box hides.
6.  Turn editing on and make section 4 the current section.  Turn editing off
    and confirm that the icon for section 4 is highlighted as the current
    section.
7.  Click on section one and confirm that section one appears in the shade
    box.
8.  Hover over the right hand side of the shade box to see that the right
    navigation arrow appears.
9.  Click on the right navigation arrow repeatedly showing each section ensuring
    that when you reach ten that you then click again and roll around to section
    one again.
10. Repeat '8' and '9' for the left navigation button confirming that when
    reaching section one another click leads to section ten.
11. Click on the 'X' close icon and confirm that the shade box is closed.
12. Confirm that section ten is the current selected icon.
13. Press the left arrow key until you reach section five confirming that the
    current selected section moves with each key press to the section expected.
14. Press the 'Esc' key and confirm that the shade box appears with section
    five.
15. Press the left arrow key until you loop back via section one to section ten.
16. Press the right arrow key until you reach section four via section one.  The
    current selected icon should move at the same time.
17. Add a label entitled 'This is a test' to section 2.  Confirm that the icon
    for section 2 now has a 'New Activity' banner upon it.

Non-editing functionality - 'Show one section per page' course layout
---------------------------------------------------------------------
1.  Ensure you have run the 'Restore from a previous version' test.
2.  On the 'Edit course settings' for the 'Course settings' page change the
    'Course layout' to 'Show one section per page'.
3.  Click on section named '515-797no09sa' and confirm that it appears on its
    own page and the shade box does not show.
4.  The rest is standard Moodle functionality.

Editing functionality
---------------------
1.  Ensure you have run the 'Restore from a previous version' test.
2.  Ensure the course layout setting is set to 'Show all sections on one page'.
3.  Turn on editing.
4.  Click on the section four icon and confirm that the page scrolls down to
    section four.
5.  Scroll back up to the top and confirm that section four is the current
    selected icon.
6.  Use the left arrow key to make section one the current selected section.
7.  Press the 'Esc' key and confirm that the page scrolls to section one.
8.  Drag section eight in the place of section six and confirm that the content
    moves along with the section names being renamed.  The icon names at the
    top will not rename until the page is refreshed, this is a known issue,
    CONTRIB-4273.
9.  Drag section three in the place of section five and confirm that the content
    moves along with the section names being renamed.  Again CONTRIB-4273 issue.
10. Click on the 'Change image' link for section eight and upload the image
    '515-797no09sa.jpg' which is in the 'test' folder, click on 'Save changes'
    and confirm that the image is displayed in the icon with the size, ratio
    and image resize method in the course settings.
11. Click on the 'Change image' link for section eight and set 'Delete image' to
    'Yes'.  Click on 'Save changes' and confirm that the image in section eight
    is no longer there.
12. Turn off editing.

Section 0 in the grid
---------------------
1.  Ensure you have run the 'Restore from a previous version' test.
2.  Ensure that the course layout setting is 'Show all sections on one page'.
3.  Turn editing on.
4.  Click on 'move section into grid' for the 'General' section and confirm that
    it moves in there with the 'information' icon image displayed.
5.  Click on 'Change image' and upload '568-111no01.jpg' from the 'test' folder.
6.  Confirm that the image is displayed.
7.  Turn off editing.
8.  Click on the 'General' section and confirm that the shade box appears.
9.  Confirm that you can click on the left arrow once to take you to section 10
    and the right arrow twice to section 1.  Observe that the current selected
    image container border moves with your clicks.
10. Close the shade box and confirm that this has happened.
11. Turn editing on and click on 'move out of grid'.  Confirm that the 'General'
    section is displayed at the top and the image container has gone.
12. Click on 'move section into grid' for the 'General' section and confirm that
    it moves in there with the '568-111no01.jpg' image displayed.  Move section
    'General' out of the grid and turn off editing.

Settings functionality
----------------------
1.  Ensure you have run the 'Restore from a previous version' test.
2.  On the 'Edit course settings' for the 'Course settings' page ('ecs') change
    the 'Set the image container width' to '384' and 'Save changes'.  Confirm
    that the image icon containers increase size with only section two having
    an image smaller in both axis.
3.  On the 'ecs' change 'Set the image container ratio relative to the width' to
    '3-4' and 'Save changes'.  Confirm that the ratio of the image container
    changes to 3 by 4.
4.  On the 'ecs' change 'Set the image resize method' to 'Crop' and 'Save
    changes'.  Confirm that all the images are resized to fit the image
    container icon and that the image for section two is pixelated.
5.  On the 'ecs' change 'Set the border colour' to 'FFD800' (do not use the
    colour picker) and 'Save changes', confirm that the border changes to
    'yellow'.
6.  On the 'ecs' change 'Set the border width' to '7' and 'Save changes',
    confirm that the border is bigger.
7.  On the 'ecs' change 'Set the border radius on / off' to 'Off' and 'Save
    changes', confirm that the border has square corners.
8.  On the 'ecs' change the 'Set the image container background colour' by using
    the colour picker to a colour of your choice, 'Save changes' and confirm
    that the image container background for sections 7 to 10 has changed to that
    colour and section 2 shows it above and below the image.
9.  Turn editing on and make section 5 the current section, turn editing off.
10. On the 'ecs' change the 'Set the current selected section colour' by using
    the colour picker to a colour of your choice, 'Save changes' and confirm
    that section 5's border is that colour.
11. On the 'ecs' change the 'Set the current selected image container colour'
    using the colour picker to a colour of your choice, 'Save changes' and
    confirm that section 1 has that as its border colour.
12. Using the keyboard, press the right cursor key and confim that section 2
    now has the 'current selected image container colour' and that section 1
    does not.

Capabilities
------------
1.  Ensure you have run the 'Restore from a previous version' test.
2.  Create / use a test teacher account and enrol them on the course as a
    'teacher' on the course.
3.  Under 'Course Administration' -> 'Users', click on 'Permissions', filter
    with the word 'grid' and prevent the role 'Teacher' for the capability
    'Change or reset the image container size'.
4.  In another web browser, login to Moodle using the test 'teacher' account
    and navigate to the 'Edit settings' for the course.
5.  Observe that the teacher is no longer able to change the size or ratio and
    that under the Grid reset options, the option 'Image container size' is
    no longer shown.
6.  On the other browser prevent the 'Change or reset the image resize method'
    for the 'Teacher' role.
7.  On the other browser refresh the page and confirm that the teacher can no
    longer see the image resize method or reset it.
8.  On the other browser prevent the 'Change or reset the image container style'
    for the 'Teacher' role.
9.  On the other browser refresh the page and confirm that the teacher can no
    longer change any of the colours or border attributes or indeed reset them.
10. On the other browser put back the capabilities for the teacher.
11. On the other browser refresh the page and confirm that the teacher can see
    and reset all of the options that you, the administrator can, bar the
    'Reset all' group under 'Grid reset options'.

Delete course
-------------
1.  Ensure you have run the 'Restore from a previous version' test.
2.  Note down the course id in the URL.
3.  In the database look at the entries in the 'format_grid_icon' table and
    observe that there are the following rows with the attribute of 'image'
    set to: 360-110no09.jpg, 515-797no09sa.jpg, 633-396no10.jpg,
    515-797no10.jpg, 568-111no01.jpg and 515-797no12.jpg where the 'courseid'
    attribute is set to the number you noted down in step 2.
4.  Also look at the entries in the 'format_grid_summary' table and confirm that
    there is one row for the course id you noted down in step 2.
5.  Back at the course, edit the course settings and change the format to
    'Topics format'.
6.  Now delete the course.  Confirm that you have been able to do this.
7.  Refresh the entries for the 'format_grid_icon' and 'format_grid_summary'
    tables and confirm that there are no rows for the course id you noted down
    in step 2.
