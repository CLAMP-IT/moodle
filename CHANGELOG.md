## Changelog

### [v4.4.6]

* Rename `master` branch to `main`
* Drop testing support on `main` branch for pre-Moodle 4.4 releases
* Create `MOODLE_403_STABLE` branch for pre-Moodle 4.4 releases
* Code cleanup

### [v4.4.5]
* Code cleanup: compatibility changes for PHP 8.1-8.2

### [v4.4.4]
* Code cleanup: compatibility changes for PHP 8.0

### [v4.4.3]
* Add support for Moodle 4.0+
* Drop support for Moodles 3.11-3.9
* Bugfix: prevent external filter functional from traversing hidden directories

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
