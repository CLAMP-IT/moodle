# [Filtered Course List v2.2]

This is a block which displays a configurable list of courses. It is intended as a replacement for the My Courses block, although both may be used. It is maintained by the Collaborative Liberal Arts Moodle Project (CLAMP).

## Installation

Unzip files into your Moodle blocks directory. This will create a folder called filtered_course_list. Alternatively, you may install it with git. In the top-level folder of your Moodle install, type the command: git clone https://github.com/CLAMP-IT/moodle-blocks_filtered_course_list blocks/filtered_course_list.

Then visit the admin screen to allow the install to complete.

## Configuration ##

To configure the block, go to Site Administration > Modules > Blocks > Current Courses List

On this page, you can choose to filter by Terms or by Categories.

Choose "terms" if you organize courses by including a certain string within the course shortname (e.g. BIO101-01S09 = the S09 at the end indicates the term).

Choose "categories" if you organize your courses into categories by term.

You may also choose to suppress "Other Courses" altogether.

## Issue reporting ##

Please report any bugs or feature requests to the public repository page: <https://github.com/CLAMP-IT/moodle-blocks_filtered_course_list>.

## Changelog ##

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
