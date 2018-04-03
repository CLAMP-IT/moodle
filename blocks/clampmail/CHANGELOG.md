# Changelog

## 3.3.3 (January 11, 2018)

- Styling fix for Boost.
- Bug fix for the required icon on the selected recipients element.
- Added Moodle 3.4 stable branch to test matrix.

## 3.3.2 (January 10, 2018)

- Bug fix which prevented the upgrade from completing successfully.

## 3.3.1 (September 27, 2017)

- Overhauled how groups are handled; Separate groups remains the default behavior but the teacher may choose a different mode at the block level. The site administrator may set a default for new blocks.
- Added a new capability, `block/clampmail:cansendtoall`, which controls whether someone may email all groups regardless of group membership.
- The block will now respect the `emailstop` setting in user profiles.
- The "there are no users you are capable of emailing" error drops the user on to the course page instead of site home.
- Code cleanup and minor bug fixes.

## 3.3.0 (August 28, 2017)

- Fixed a bug in which the real email address was not set as reply-to.
- Changed version numbering to match stable version

## 1.3.3 (June 23, 2017)

- Minor code cleanup
- Updated Behat tests for Moodle 3.3

## 1.3.2 (January 11, 2017)

- Set the alternate address page to use the report layout
- Updated Behat tests for Moodle 3.2

## 1.3.1 (February 1, 2016)

- Fixed a misspelled string
- Minor code cleanup

## 1.3.0 (January 31, 2016)

- Fixed bug where emails would sometimes be sent without formatting
- Fixed bug where plaintext editor did not remember format choice
- Fixed bug where attachments with extra periods in the filename were not sent in some cases
- Changed "section" verbiage to "group"
- Adopted sentence case for text and cleaned up language file

## 1.2.0 (January 8, 2016)

- Added Behat acceptance test coverage
- Resolved numerous standards issues
- Verified Moodle 3.0 compatibility

## 1.1.1 (July 5, 2015)

- Added Moodle 2.9 compatibility
- Added support for revised `fullname` function

## 1.1.0 (November 10, 2014)

- Various bug fixes
- Don't display suspended users
