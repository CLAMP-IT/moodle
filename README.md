Filtered course list
================

![Moodle Plugin
CI](https://github.com/CLAMP-IT/moodle-blocks_filtered_course_list/workflows/Moodle%20Plugin%20CI/badge.svg)

The _Filtered course list_ block displays a configurable list of a user's courses. It is intended as a replacement for the _My courses_ block, although both may be used. It is maintained by the Collaborative Liberal Arts Moodle Project (CLAMP).

## Requirements

- Moodle 4.4 (build 2024042200 or later)

## Installation
Unzip files into your Moodle blocks directory. This will create a folder called `filtered_course_list`. Alternatively, you may install it with git. In the top-level folder of your Moodle install, type the command: 
```
git clone https://github.com/CLAMP-IT/moodle-blocks_filtered_course_list blocks/filtered_course_list
```
Then visit the admin screen to allow the install to complete.

## Configuration ##
To configure the block, go to _Site Administration > Plugins > Blocks > Filtered course list._

### External filters ###
If any external filters are available on your Moodle installation you can
activate them by checking the appropriate boxes. If there are no external
filters, this setting will not display.

### Filters ###
Most of the configuration will be done in the _textarea_ near the top of the page. Add one filter per line; use pipes ("|") to separate the different settings for each filter. Whitespace at the beginning or end of a value is removed automatically, so you can pad your layout to make it more readable. Here is a sample of the possibilities:
```
category   | expanded  | 0 (category id) | 1 (depth)
shortname  | exp       | Current courses | S17
regex      | collapsed | Upcoming        | (Su|F)17$
idnumber   | col       | History courses | HIST_
starred    | exp       | My starred courses
completion | exp       | Incomplete      | incomplete
completion | col       | Completed       | complete
generic    | exp       | Categories      | Courses
enrolment  | col       | guest, self     | Open courses
#category  | col       | 1 (Misc)        | 0 (show all children)
The line above will be ignored, as will this comment.
```

Please see the usage guide for fuller details: https://github.com/CLAMP-IT/moodle-blocks_filtered_course_list/wiki


### Other settings

| Setting | Description |
|---------|-------------|
| Hide "All courses" link | Check the box to suppress the "All courses" link that otherwise appears at the bottom of the block. This link takes the user to the main course index page. Note that this setting does not remove the link from an administrator's view. |
| Hide from guests | Check this box to hide the block from guests and anonymous visitors. |
| Hide other courses | By default an "Other courses" rubric appears at the end of the list and displays any of the user's courses that have not already been mentioned under some other heading. Check the box here to suppress that rubric. |
| Persistent expansion | If activated, we will use cookies to persist expansion states for the duration of a session. |
| Max for single category | On a site with only one category, admins and guests will see all courses, but above the number specified here they will see a category link instead. [Choose an integer between 0 and 999.] Unless you have a single-category installation there is no need to adjust this setting. |
| Course name template | Use replacement tokens (FULLNAME, SHORTNAME, IDNUMBER or CATEGORY) to control the way links to courses are displayed. Add a character limit to any token by suffixing it in curly braces to the token. For instance: FULLNAME{20} |
| Category rubric template | Use replacement tokens (NAME, IDNUMBER, PARENT or ANCESTRY) to control the way rubrics display when using a category filter. Add a character limit to any token by suffixing it in curly braces to the token. For instance: NAME{20} |
| Category separator | Customize the separator between ancestor categories when using the ANCESTRY token above. |
| Manager view | By default administrators and managers will see a list of categories rather than a list of their own courses. This setting allows you to change that, and it can be helpful to do so while configuring the block. Be advised, however, that admins and managers who are not enrolled in any courses will still see the generic list. |
| Sorting | The next four settings control the way courses are sorted within a rubric. |

## Changing the display name

To change the name of a block instance, turn editing on on a screen that displays the block and click on the (gear) icon to edit the settings.

## Issue reporting

Please report any bugs or feature requests to the public repository page: <https://github.com/CLAMP-IT/moodle-blocks_filtered_course_list>.

## Developers

Use Grunt to manage LESS/CSS and Javascript as described in the Moodle dev documentation: https://docs.moodle.org/dev/Grunt.