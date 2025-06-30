# Roster

![moodle-ci](https://github.com/LafColITS/moodle-report_roster/workflows/moodle-ci/badge.svg)

This is a simple report which displays the user pictures for everyone enrolled in the given course.

## Requirements
- Moodle 4.1 (build 2022112800 or later)

## Installation
Copy the roster folder into your /report directory and visit your Admin Notification page to complete the installation.

## Administration
There are several options available to the admin from the Roster Report settings page.

### General

**Profile fields to display:** A newline separated list of profile fields to display. Note that custom profile fields must be entered as `profile_field_{shortname}`. The following special values are also supported:

* `fullname` - displays fullname according to site settings.
* `currenttime` - displays date/time in user's configured timezone (you can specify PHP strftime style formatting like so: `currenttime %l:%M %p`)

**Display name:** The display string used by the Roster Report on the front end. Will appear in the flat navigation (if enabled) and on the Roster Report page.

### User Image Size

Any size set to `0` will not display in the drop-down on the report page. If there is only one non-zero size option, the drop-down will not be displayed.

On initial load of the report page, the report will attempt to use the size configured as default. If this size is 0, the report will fall back to the next highest size option. If all three size options are `0`, the size will hard default to 100.

**Default size:** The default size of the user images in the Roster Report.

**Size: Small:** The size, in pixels, of the user images when the user has selected "Small".

**Size: Medium:** ...when the user has selected "Medium".

**Size: Large:** ... when the user has selected "Large".

## Usage
Once the plugin is installed, you can access the functionality by going to
Reports > Roster within the course, or by going to the URL `<mymoodle>/report/roster/index.php?id=<courseid>.`

There are several options available when viewing the Roster Report:

**Learning mode** turns the display of names on and off
**Group** filtering to display only users in the selected group
**Role** filtering to display only users with the selected role
**Size** changes the display size of the user images based on levels configured in the admin settings (see above)
**Display mode** which toggles between the regular web view and a version of the report suitable for printing

## Author
Charles Fulton (fultonc@lafayette.edu)
