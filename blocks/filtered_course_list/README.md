# [Filtered course list v4.4.2]

For Moodle 3.8, 3.9, 3.10, and 3.11

![Moodle Plugin
CI](https://github.com/CLAMP-IT/moodle-blocks_filtered_course_list/workflows/Moodle%20Plugin%20CI/badge.svg)

The _Filtered course list_ block displays a configurable list of a user's courses. It is intended as a replacement for the _My courses_ block, although both may be used. It is maintained by the Collaborative Liberal Arts Moodle Project (CLAMP).

## Installation
Unzip files into your Moodle blocks directory. This will create a folder called `filtered_course_list`. Alternatively, you may install it with git. In the top-level folder of your Moodle install, type the command: 
```
git clone https://github.com/CLAMP-IT/moodle-blocks_filtered_course_list blocks/filtered_course_list
```
Then visit the admin screen to allow the install to complete.

## Upgrading 
### From 2.8.3 or lower
All of your configuration will automatically be converted to the new _textarea_ style. Filters that were set but not active will be listed as `DISABLED`. In addition, pipe characters ("|") within custom titles will be converted to hyphens ("-") in the new config.
### From v2.3 or lower
During the upgrade you will be shown only the "new settings" but it is important to look at the new and the old settings together, so be sure to look at the block configuration once the upgrade is complete. 

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

Use Grunt to manage LESS/CSS and Javascript as described in the Moodle dev documentation: https://docs.moodle.org/dev/Grunt

## Changelog

### [v4.4.2]
* Add support for Moodle 3.11
* Ensure PHPUnit test can access renderer

### [v4.4.1]
* Migrates CI to Github Actions
* Adds method to facilitate mobile rendering

### [v4.4.0]
* Drops support for Moodle 3.7
* Adds support for Moodle 3.10
* Converts to plugin CI from Moodle HQ

### [v4.3.0]
* Drops support for Moodle 3.6
* Adds support for Moodle 3.9

### [v4.2.2]
* Adds test coverage for Moodle 3.8

### [v4.2.1]
* Bug: Instance config defaults to empty, multilang fix
* Dev: npm update

### [v4.2.0]
* Feature: Adds filtering by idnumber

### [v4.1.5]
* Testing: Isolates/namespaces our custom behat step

### [v4.1.4]
* Bug: Hops over hidden categories when drilling down category trees

### [v4.1.3]
* Testing: gitignores a file created during testing
* Testing: expands coverage to Moodle 3.7

### [v4.1.2]
* Bug: Handles visible categories nested within hidden categories

### [v4.1.1]
* Bug: Fixes some inconsistent persistence behaviours
* Bug: Applies content filters when appropriate to rubric and course titles
* Backend: adds thirdpartylibs.xml

### [v4.1.0]
* Feature: Enables persistent expansion states
* Feature: Displays stars for starred courses
* Backend: Removes GDPR polyfill

### [v4.0.0]
* Requirements: Requires Moodle 3.6
* Feature: Filters are now pluggable. Details in the wiki.
* Feature: Adds a starred courses filter
* Feature: Adds enrolment filter
* Testing: Fixes some superficial testing errors
* Backend: updates deprecated coursecat calls to core_course_category
* Backend: Updates node modules

### [v3.3.7]
* Requirements: Requires Moodle 3.3 or higher
* Feature: Option to truncate template values after a certain length
* Feature: CSS classes corresponding to course completion status
* Backend: Reorganizes filter classes

### [v3.3.6]
* Bug: Fixes fatal error with Privacy API

### [v3.3.5]
* Backend: Better compliance with GDPR for multiple PHP versions
* Testing: Covers Moodle 3.5

### [v3.3.4]
* Policy: complies with GDPR
* Bug: Better performance when fetching category ancestry
* Backend: minor refactor

### [v3.3.3]
* Bug: Provides additional language strings

### [v3.3.2]
* Bug: Missed an AMOS string

### [v3.3.1]
* Bug: Simplifies strings for AMOS Compatibility
* Bug: Complies with Moodle's CSS styles

### [v3.3.0]
* New: adds a generic filters
* New: accepts display templates for category rubrics
* Bug fix: now handles HTML entities correctly in display text
* Back end: generates HTML solely via mustache templates
* Back end: uses LESS to manage CSS

### [v3.2.2]
* Makes course summary URLs available to the list_item template

### [v3.2.1]
* Requirement bump: 3.2 and higher
* Front end: Minor style and HTML tweaks
* Back end: New mustache template to manage rubrics
* Automated Testing:
  * Now using moodlerooms-plugin-ci v2
  * Covers 3.2 to 3.4

### [v3.2.0]
* Feature: Display templates for course names
* Bug: Style fixes for docked blocks in Clean themes
* Bug: Allow permission overrides
* Back end: streamlining the item link template

### [v3.1.0]
* Supports multiple instances, each with their own configuration
* Now uses folder icons for category links
* Renders list items and block footer from template

### [v3.0.0]
* Settings:
  * Provides clearer overview of multiple filters
  * Makes it easier to modify and reorder filters
  * Allows admin to set expansion preference for category filters
  * Allows multiple category filters
  * Introduces course completion filters
  * Allows recursion depth on category filters
  * Allows admin to intersperse filter types freely
  * Allows unlimited number of filters
  * Allows course sorting within rubrics
  * Replaces 'admin' with 'manager' where the latter is more accurate
* Appearance:
  * Better support for core themes.
* Back end:
  * Makes it easier to add new filter types
  * Refactors YUI module as AMD/jQuery
  * Requires `MOODLE_30_STABLE`
* Testing:
  * Drops automated testing for `MOODLE_29_STABLE`
  * Correctly disables xdebug for Travis CI

### [v2.8.3]
* Testing: Drops automated testing for `MOODLE_28_STABLE`

### [v2.8.2]
* Back end: Uses core coursecat functions instead of external lib
* Testing: Automated testing now covers `MOODLE_31_STABLE`

### [v2.8.1]
* Back end: Confirms functionality for PHP7
* Bug: Adds support for matching non ascii characters
* Back end: PHPDoc compliance

### [v2.8.0]
* Back end: The FCL block now stores preferences in Moodle's plugin config table. Settings from older versions should migrate seamlessly.
* Back end: Automated testing has been considerably expanded.

### [v2.7.0]
* Feature: An admin can set arbitrary rubrics to be expanded by default
* Feature: Aria accessibility improvements 
* Bug fix: Admins should see a block under all circumstances
* Back end: Continuous integration with Travis CI

### [v2.6.0]
* Dependency: Requires Moodle 2.8
* Behind the scenes: Updates automated testing for newer Moodles

### [v2.5.1]
* Feature: Admin can designate "Top" when organizing by categories
* Behind the scenes: automated testing and healthier code

### [v2.5]
* Feature: Course rubrics can now be set to be collapsible
* Feature: Shortname matches can be powered by regex
* Bug fix: One course can now satisfy multiple shortname matches
* Testing: Adds comprehensive PHPunit testing
* Testing: Adds Behat acceptance tests for selected features

### [v2.4]
* Fixes style issues for the Clean family of themes
* Introduces better handling for sites with one category but many courses
* Allows admin to edit the display title
* Allows category based display, including subcategory logic
* Allows admin to see 'own courses' instead of all courses
* Allows optional 'other courses' catch-all category
* Allows admins to define up to ten custom rubrics to match shortcodes against
* Allows admins to hide the block from guests

### [v2.3]
* Separate release for Moodle versions earlier than v2.5.0
* Minor code cleanup

### [v2.2]
* Rewrote the block to use block_base instead of block_list
* Added an option to suppress Other Courses

### [v2.1.1]
* Added a missing language string

### [v2.1]
* Compatibility with Moodle v2.5.0

### [v2.0]
* Resolved various code-checker issues
* Compatibility with Moodle v2.4.0
