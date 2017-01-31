# [Filtered course list v3.0.0]

[![Build Status](https://travis-ci.org/CLAMP-IT/moodle-blocks_filtered_course_list.svg?branch=master)](https://travis-ci.org/CLAMP-IT/moodle-blocks_filtered_course_list)

for Moodle 3.0 or higher

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

Most of the configuration will be done in the _textarea_ at the top of the page. Add one filter per line; use pipes ("|") to separate the different settings for each filter. Whitespace at the beginning or end of a value is removed automatically, so you can pad your layout to make it more readable. Here is a sample followed by an explanation:
```
category   | expanded  | 0 (category id) | 1 (depth)
shortname  | exp       | Current courses | S17
regex      | collapsed | Upcoming        | (Su|F)17$
completion | exp       | Incomplete      | incomplete
completion | col       | Completed       | complete
#category  | col       | 1 (Misc)        | 0 (show all children)
The line above will be ignored, as will this comment.
```

### Shared line elements
The first two elements of any line are common to all filter types.

#### Filter type
The first element in any line is the filter type. Currently recognized types are `category`, `shortname`, `regex` and `completion`. A line that begins with any other value will be ignored by the plugin. You can use this to disable a line that you want to reactivate later or to make notes to yourself.

#### expanded / collapsed
The second element of each line indicates the default expansion state of the rubric(s) the filter produces. If the first character of the value is `e` the plugin will interpret it as "expanded." Anything else will be interpreted as "collapsed". You may wish to enter full words for clarity or abbreviations for brevity. When a filter produces more than one rubric -- category filters can do this, for instance -- the expansion setting will apply to all of the rubrics.

### Category filters
Category filters group a user's courses under category names.

#### category id
The third element of a category filter is the Moodle-internal category id. You can find the category id by going to _Site administration > Courses > Course and category management_ and clicking on the category whose id you want. In your browser's address bar you should see `categoryid=<id>` at the end of the URL. Because it is inconvenient to find category id's, you may want to make a note to yourself in the configuration about which category a given id corresponds to. (This will make it easier on you when you want to modify the configuration.) The id field looks for an integer at the beginning of the value and then ignores everything else. Use zero ("0") as the category id if you want to display all (top-level) categories.

#### recursion depth
Categories can be organized into a hierarchy. The final element of a category filter determines how many levels of descendants will be shown. The plugin looks for an integer at the beginning of the value and ignores anything else. Use zero ("0") to show all descendants. 

### Shortname filters
Shortname filters allow a Moodle admin to group a user's courses based on patterns in course shortnames.

#### title
Each shortname filter corresponds to one rubric in the final block display. The third element of a shortname filter determines the title of that rubric. Note that pipe characters ("|") are not allowed here because they could be mistaken for a field seperator. 

#### match
The final element of a shortname filter is the match value to test shortnames against. For example, if all courses in the 'Spring 2017' semester contain 'Sp17' then you can use those four characters as your match string. Shortname matches are _not_ case sensitive. In this final field pipes ("|") are allowed.

### Regex filters
Regex filters are similar to shortname filters except that you can use regex patterns in your match string.

#### title
Each regex filter corresponds to one rubric in the final block display. The third element of a regex filter determines the title of that rubric. Note that pipe characters ("|") are not allowed here because they could be mistaken for a field seperator.

#### regex match
Regular expressions (regex) can add precision and flexibility to your match strings. For instance, you can use `Sp17$` to match shortnames that _end_ in 'Sp17'. A match like this (which is also case-sensitive) helps to limit the chance for false positives. On the other hand, you could also broaden your match to include alternative strings. `(Su17|Fa17)$` would match shortnames that end either in 'Su17' or 'Fa17'. Note that pipes ("|") _are_ allowed but backtics ("`") are not. For documentation on using PHP regular expressions please visit: http://php.net/manual/en/regexp.introduction.php

### Completion filters
Completion filters will do nothing in an installation where completion tracking has not been enabled at the site level. Completion filters will additionally apply only to courses that have completion tracking enabled at the course level.

#### title
Each completion filter corresponds to one rubric in the final block display. The third element of a completion filter determines the title of that rubric. Note that pipe characters ("|") are not allowed here because they could be mistaken for a field seperator.

#### completion state
The final field in a completion filter indicates whether to show courses that the user has completed (`complete`) or not yet completed (`incomplete`). Empty setings and settings that with the character "c" will be interpreted as "complete".

### Other settings

| Setting | Description |
|---------|-------------|
| Hide "All courses" link | Check the box to suppress the "All courses" link that otherwise appears at the bottom of the block. This link takes the user to the main course index page. Note that this setting does not remove the link from an administrator's view. |
| Hide from guests | Check this box to hide the block from guests and anonymous visitors. |
| Hide other courses | By default an "Other courses" rubric appears at the end of the list and displays any of the user's courses that have not already been mentioned under some other heading. Check the box here to suppress that rubric. |
| Max for single category | On a site with only one category, admins and guests will see all courses, but above the number specified here they will see a category link instead. [Choose an integer between 0 and 999.] Unless you have a single-category installation there is no need to adjust this setting. |
| Manager view | By default administrators and managers will see a list of categories rather than a list of their own courses. This setting allows you to change that, and it can be helpful to do so while configuring the block. Be advised, however, that admins and managers who are not enrolled in any courses will still see the generic list. |
| Sorting | The next four settings control the way coures are sorted within a rubric. |

## Changing the display name

To change the name of a block instance, turn editing on on a screen that displays the block and click on the (gear) icon to edit the settings.

## Issue reporting

Please report any bugs or feature requests to the public repository page: <https://github.com/CLAMP-IT/moodle-blocks_filtered_course_list>.

## Changelog

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
