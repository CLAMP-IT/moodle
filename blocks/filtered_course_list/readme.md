# [Filtered course list v2.8.0]

[![Build Status](https://travis-ci.org/CLAMP-IT/moodle-blocks_filtered_course_list.svg?branch=master)](https://travis-ci.org/CLAMP-IT/moodle-blocks_filtered_course_list)

for Moodle 2.8 or higher

The Filtered course list block displays a configurable list of courses. It is intended as a replacement for the My Courses block, although both may be used. It is maintained by the Collaborative Liberal Arts Moodle Project (CLAMP).

## Installation

Unzip files into your Moodle blocks directory. This will create a folder called filtered_course_list. Alternatively, you may install it with git. In the top-level folder of your Moodle install, type the command: 

```
git clone https://github.com/CLAMP-IT/moodle-blocks_filtered_course_list blocks/filtered_course_list
```

Then visit the admin screen to allow the install to complete.

## Upgrading from v2.3 or lower

During the upgrade you will be shown only the "new settings" but it is important to look at the new and the old settings together, so be sure to look at the block configuration once the upgrade is complete. 

## Configuration ##

To configure the block, go to Site Administration > Modules > Blocks > Current Courses List

On this page, you can choose to filter by Shortnames or by Categories.

Choose "shortnames" if you organize courses by including a certain string within the course shortname (e.g. BIO101-01S09, where the S09 at the end indicates the semester). If you activate the relevant setting, you can also use regex for these matches.

Choose "categories" if you organize your courses into categories.

If you choose "categories" the block will also display any subcategories of the main category you select.

You may also choose whether to suppress "Other Courses", whether to hide the block from guests, whether to hide the "all courses" link that appears at the bottom of the box, and whether admins will see all courses or only their own.

## Changing the display name ##

To change the name of the block, turn editing on on a screen that displays the block and click on the (gear) icon to edit the settings.

## Issue reporting ##

Please report any bugs or feature requests to the public repository page: <https://github.com/CLAMP-IT/moodle-blocks_filtered_course_list>.

## Changelog ##

### [v2.8.1] ###
* Back end: Confirms functionality for PHP7
* Bug: Adds support for matching non ascii characters
* Back end: PHPDoc compliance

### [v2.8.0] ###
* Back end: The FCL block now stores preferences in Moodle's plugin config table. Settings from older versions should migrate seamlessly.
* Back end: Automated testing has been considerably expanded.

### [v2.7.0] ###
* Feature: An admin can set arbitrary rubrics to be expanded by default
* Feature: Aria accessibility improvements 
* Bug fix: Admins should see a block under all circumstances
* Back end: Continuous integration with Travis CI

### [v2.6.0] ###
Dependency: Requires Moodle 2.8
Behind the scenes: Updates automated testing for newer Moodles

### [v2.5.1] ###
* Feature: Admin can designate "Top" when organizing by categories
* Behind the scenes: automated testing and healthier code

### [v2.5] ###
* Feature: Course rubrics can now be set to be collapsible
* Feature: Shortname matches can be powered by regex
* Bug fix: One course can now satisfy multiple shortname matches
* Testing: Adds comprehensive PHPunit testing
* Testing: Adds Behat acceptance tests for selected features

### [v2.4] ###
* Fixes style issues for the Clean family of themes
* Introduces better handling for sites with one category but many courses
* Allows admin to edit the display title
* Allows category based display, including subcategory logic
* Allows admin to see 'own courses' instead of all courses
* Allows optional 'other courses' catch-all category
* Allows admins to define up to ten custom rubrics to match shortcodes against
* Allows admins to hide the block from guests

### [v2.3] ###
* Separate release for Moodle versions earlier than v2.5.0
* Minor code cleanup

### [v2.2] ###
* Rewrote the block to use block_base instead of block_list
* Added an option to suppress Other Courses

### [v2.1.1] ###
* Added a missing language string

### [v2.1] ###
* Compatibility with Moodle v2.5.0

### [v2.0] ###
* Resolved various code-checker issues
* Compatibility with Moodle v2.4.0
