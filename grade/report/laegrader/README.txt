LAEgrader Report Overview
The LAEgrader report takes the place of the Grader report as the grades repository interface for the instructor for the course. It is based off from, and shares many library functions and classes with the Grader report. Therefore, the Grader report directory on your Moodle installation cannot be removed. The most notable difference between the LAEgrader and Grader is that the LAEgrader freezes both the column headers (grade item titles) and the student rows, while still allowing the user to scroll vertically and horizontally.

LAEgrader Report Installation
1. Download the LAEgrader report from https://github.com/bobpuffer/laegrader/archive/master.zip.  
2. (If zipped) unzip the contents of the zip file and change the name of the unzipped folder from laegrader-master to laegrader.
3. Place all contents of the grade/report/laegrader folder in the grade/report folder of your Moodle installation (you should then have a grade/report/laegrader folder)
4. Under Site administration block run Notifications.
5. Check to see that your permissions for your various roles are as you desire. It is recommended that access to the regular Grader report be set to "Prohibit" to avoid confusion. The admin will always see both reports. 

User Configurable settings
- Height in pixels of scrollable portion of LAE grader report (300,340,380,420,460,500,540,580,620,660,700,740,780,820,860,900). Each increment represents roughly one student line.
- Accurate points calculation on or off (see below).

The defaults for these options can be set at site level by going to Administration->Grades->Report settings->LAE grader report.

Layout Differences From Grader Report
LAE Grader report in non-editing mode
- Grade item and category headers are all on one line, not nested. LAEgrader does not collapse or expand categories.
- LAEgrader allows wrapping of grade item and grade category names (at approximately 30 characters) to avoid excessively wide reports
- Category items always follow the items contained in the category
- All items (including the category column) included in a category are color-coded the same to group them together, alternately orange/blue
- The last column is always the Course Total. 
- Items not contained in a category have a background of white as does the Course Total. 
- Hidden items and categories are gray.
- If your gradebook exceeds 95% of the width of the document page, a horizontal scrollbar will be applied at the bottom. If the height of the number of student rows to display exceeds the user-configurable height a vertical scrollbar will be displayed. Whenever the grades are scrolled, either horizontally or vertically, the grade item titles )and optionally, range and average rows) will remain fixed as will the student columns (including "id", if displayed). This is the biggest difference between the LAE and core gradebooks.  It negates the need for mouseover descriptions of which student and which item on which you're currently working.
- Rows added such as 'range' or 'average' are placed at the top (instead of the bottom) and are frozen along with the grade item titles row so the user never loses sight of them.

Functionality Enhancements
A. Letter and Percentage Input: Whenever the user is editing grades they have the option of inputting a "real" number, a letter grade or a percentage grade. A letter will be compared against the letter grade setup for the course (configurable Grade letters) and will be converted to 1 point less than the maximum available for that letter grade. Percentage grades will be converted to the input percentage times the maximum grade earnable for that item.
B. "Dump to Google" button: A button is provided above the student names column to allow quick "dumps" of LAE grader report contents. The data is exported in a format that facilitates calculations and checking of grades in Google (or Excel). 
C. Accurate Points Calculation: Unless Sum of Grades aggregation method is used, Moodle's core gradebook does not automatically maintain accurate category and course points totals. LAE maintains these on the fly (if $CFG->accuratetotals is chosen) without making changes in the values stored in Moodle's grades tables.  There are also instances when the core gradebook's lack of maintaining accurate totals affects percentage and letter grade calculations.  LAE corrects those.
NOTE: to maintain the enhancements inside the report "plugin" the accurately calculated points for categories will not be reflected in the Categories and Items screen (unless you request Luther's hack for that).

Problems
Grades not submitting. Similar problems exist for existing Moodle gradebook). If you're using suhosin to harden your PHP you should set suhosin.post.max_vars four times the columns times the rows of your anticipated largest gradebook.
If you're using PHP 5.3.9 you need a line like:
 php_value max_input_vars XXXXX

where XXXXX is a similar calculation as above with suhosin.