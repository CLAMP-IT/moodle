PDF Submission / Feedback plugin
--------------------------------

These plugins for the assignment module allows a teacher to annotate and
return PDF files that have been submitted by students. It is based on my
previous 'UploadPDF' assignment type, updated to work with the Moodle 2.3+
'assign' module (rather than the Moodle 2.0-2.2 'assignment' module).

Teachers can add and position comments and draw lines, ovals, stamps,
rectangles and highlights onto the student's work, from within the browser,
before returning the work to the student.

This plugin is available in Moodle 2.3+, Moodle 2.0-2.2 and Moodle 1.9 versions.
This is the **Moodle 2.3+** version - you can download the Moodle 1.9 version
from here:
https://github.com/davosmith/moodle-uploadpdf/zipball/MOODLE_19_STABLE

and the Moodle 2.0-2.2 version here:
https://github.com/davosmith/moodle-uploadpdf/zipball/master

The latest version of the Moodle 2.3+ version is available here:
https://github.com/davosmith/moodle-assignsubmission_pdf/zipball/master
https://github.com/davosmith/moodle-assignfeedback_pdf/zipball/master

!! THERE ARE A FEW IMPORTANT ITEMS TO NOTE IN THE INSTALLATION, PLEASE
   READ CAREFULLY !!

==Recent changes==

* 2013-10-31 - Now able to create new submission from previous submission.
* 2013-09-29 - Hide 'Download final submission' link when no files submitted.
* 2013-05-31 - Fix support for team submissions
* 2013-03-25 - Plugin is disabled if the ghostscipt path is incorrect
* 2013-02-22 - Now correctly handles 'landscape' pages in PDFs
* 2013-01-30 - Updating to 'stable' as no known issues to fix
* 2013-01-03 - Fix javascript compatibility with Moodle 2.3
* 2012-12-31 - Complete rewrite to work with Moodle 2.3+ assign type

==Installation==

Note - the assignsubmission_pdf plugin can, theoretically, be used without the
assignfeedback_pdf plugin. On its own, however, it has little advantage over
the standard 'file' submission plugin (other than coversheet handling). The
assignfeedback_pdf will not do anything on its own (and cannot be installed
without the submission plugin).

1. Download and install GhostScript ( http://pages.cs.wisc.edu/~ghost )
  - or install from standard respositories, if using Linux.
  Under Windows, do not install to a path with a space in it - that
  means you should install to something like 'c:\gs'
  NOT 'c:\Program Files\gs' (note you only need the files
  'gswin32c.exe' and the dll file from the 'bin' folder, all other
  files are unnecessary for this to work).

2. Unzip the submission pdf and feedback pdf plugin files to folders on
  your local machine

3. Upload the plugin files to <siteroot>/mod/assign/submission/pdf
  and <siteroot>/mod/assign/feedback/pdf

4. Log in to Moodle as administrator, then click on 'Home'.

5. Visit 'Site admin > Plugins > Assignment plugins > Feedback Plugins >
  PDF Submission'. Adjust the 'Ghostscript path' to where ghostscript
  is installed (should not need changing on a Linux install).
  Example paths - Linux / Mac: gs  OR  /usr/bin/gs
                  Windows: c:\gs\gswin32c.exe

All being well, you should now be able to add submission and feedback
type 'pdf' to assignments.

If you get an 'Unrecoverable error' when attempting to annotate an assignment
it may well be related to not having ghostscript fonts installed on your
system. See https://moodle.org/mod/forum/discuss.php?d=218897#p1010615 for
more details.

==How to use==

* Add a new Assignment to a course.

* Configure all the usual settings - you should be aware of the following
  additions:

  PDF submission - set to 'Yes' to allow students to submit PDFs for annotation

  PDF feedback - set to 'Yes' to allow the submitted PDFs to be annotated (note
  this ONLY works with PDFs submitted via the 'PDF submission' plugin).

  Coversheet - this is a PDF that will be automatically added to the start of
  any files submitted by your students

  Template - before submission your students can be (optionally) asked
  to fill in some text fields, the template is used to add these
  entries to the coversheet (this is ignored, if no coversheet is selected).

  Edit Templates... - see section below

* It is recommended this is used with the 'Require students click submit button'
  option, as then the processing and combining of the submission PDFs is only
  done once they click that button. Otherwise, the processing is done every time
  the student updates their submission.

* When a student uploads their files and clicks 'Submit' they will be combined
  them together into a single submission (along with the coversheet).

(Hint: to help students generate PDF files, install a PDF printer, such as
 PDF Creator - http://sourceforge.net/projects/pdfcreator).

* The teacher can then log in, go to the usual marking screen and click on
  'Annotate submission', which will bring up the first page of the
  student's work on screen.

* Note that, as with all other feedback types, if you want to be able to access
  the annotation features directly from the grading overview page you will need
  to turn on the 'quick grading' feature.

* Click anywhere on the image of the PDF to add a comment. Use the
  resize handle in the bottom-right corner of a comment to resize it,
  click & drag on a comment to move it. Click (without dragging) on a
  comment to edit it, delete all the text in a comment to remove it.

* Right-click on a comment to add it to a 'Comment Quicklist'. You can
  then right-click anywhere on a page to insert comments from this
  'Comment Quicklist' (with the same text, width and background as the
  original). Comments can be delete from the 'Comment Quicklist' by
  clicking on the 'X' to the right of the comment.

* You can add lines to the PDF by holding 'Ctrl' ('Alt' on Apple Macs)
  whilst you click and drag with the mouse (or alternatively hold 'Ctrl'
  then click once for the start and once for the end of the line).

* You can also choose different drawing tools by clicking on the icons
  or by using the keys c (comments), l (lines), r (rectangles),
  o (ovals), f (freehand lines), e (erase lines), [ & ] (change comment
  colour), { & } (change line colour)

* Navigate between the pages by clicking on the 'Next' and 'Prev'
  buttons or by pressing 'n' and 'p' on the keyboard.

* Click on 'Save Draft and Close' (or just navigate to a different page) to save
  the work in progress.

* Click on the 'Generate Response' icon to create a new PDF with all your
  annotations present (that the student will be able to access).

* You can view the comments you have made on a student's previous
  submissions by choosing that submission from the 'compare to' list

* You can quickly find comments you have previously made by clicking
  on the 'find comment' list.

* Add any feedback / grades to the usual form and save them.

==Edit Templates==

* Click on the 'Edit Templates...' link on the 'Settings' page

* Choose the name of the Template to edit (or select 'New Template...')

* You can change the name of the template, delete the template or make
  it available to everyone on the site (administrators only, for this
  last option). Only administrators can edit site templates.
  Note: you cannot delete templates that are in use (click 'show' to
  find out where it is currently being used)

* The list at the bottom allows you to choose an item in the template
  to edit, or choose 'New Item...' to add a new one.

* The types of item you can add are:
  text - a block of text, which will re-flow at 'width' pixels
      'value' will be the prompt the student sees to fill this in
  shorttext - similar to text, but without word-wrapping
      useful for 'name' or 'type your initials to state this is all
      your own work'
  date - fills in the date that the assignment was submitted
      'value' is the format to record the date

* To position the items on the template, upload an example PDF
  coversheet (using the bottom form) then type in the position
  you want to place the PDF (x position, y position, in pixels).
  Alternatively, click on the coversheet image to set the position of
  that template item.

* When you are finished, save any items you have changed, then
  close the window. The list of templates on the 'settings'
  page should have been updated.

==Known issues==
There is no way to annotate the PDFs without JavaScript.
Backup & restore will not transfer coversheet templates to a different site
(it will work fine on a single site). This is a limitation of the assignment
backup & restore process.

==Thanks==
This makes use of GhostScript and the FPDI and TCPDF libraries
for PDF manipulation; Mootools is used to help with the JavaScript and
Raphael provides the cross-browser annotation support.

Thanks to the creators of all those libraries, as this wouldn't have
been possible without their hard work (and their free software licensing)

==Contact==
moodle AT davosmith DOT co DOT uk

Davo Smith
