Course Description block for Moodle

This block allows configuration of roles to be considered "Teachers" of a course.  The block will
then display a list of these teachers for the current course in the block, with a link to the 
profile of each one.

This block can be configured for each course to display a list of "Teachers" and a list of "TAs" 
with a link to their profiles, their phone number and office hours.

This block has its own database (populated by a cron job processing a csv file w/ course meeting 
info from Datatel. This way we can display the term and the meeting days/times/locations of the class.
There is also a custom format called "class by date" that is only usable if this block is installed 
because the format shows its sections based on the start date (in the course settings) and the meeting
days and times.

This block is also configurable by the teacher to display 4 extra fields:
Description, Course Objectives, Evaluation Criteria, and Additional Info.

Hampshire College uses a hack-to-core patch called "center block" which allows blocks to be
displayed in the center of the course page. We place this block in each course by default
and put it at the top center of the page.

To install, place all files in /blocks/course_description and visit /admin/index.php in your browser

This block was written by Sarah Ryder <sryder@hampshire.edu> and is
Copyright Hampshire College. It is currently maintained by Sarah Ryder.

Released Under the GNU General Public Licence http://www.gnu.org/copyleft/gpl.html
