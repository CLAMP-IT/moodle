# Changelog

## 4.0.2 (July 8, 2024)

- [BUGFIX]: CSS classes shouldn't depend on the value of `pluginname`
- Add support for Moodle 4.4
- Drop support for Moodle 4.0

## 4.0.1 (August 21, 2023)

- Fix for sesskey validation
- Install.xml cleanup

## 4.0.0 (August 14, 2023)

- Various code cleanup and test fixes
- Refactor classes and namespaces
- Full rebranding as CLAMPMail
- Change internal references from Quickmail to CLAMPMail

## 3.11.1 (January 31, 2023)

- Drop support for Moodle 3.11
- Migrate CI builds to Github Actions
- Fixed bug which prevented viewing history when an unenrolled user was one of the recipients

## 3.11.0 (August 25, 2021)

- Added student-use feature to configuration
- Change default branch to "main"
- Update CI tool to version 3
- Update PHPDocs
- Drop support for Moodle 3.6-3.10
- Update deprecated userfields call

## 3.6.1 (March 16, 2020)

- Allow site administrator to configure maximum attachment size

## 3.6.0 (January 9, 2020)

- Improved the notification if there are no recipients (thanks to @BatesWebTech)
- Properly tag upload behat tests

## 3.5.0 (July 9, 2019)

- Added internal navigation element so that user can navigate between all Quickmail pages without returning to the course page.
- Added a link to Quickmail from the course administration; Quickmail may now be used without explicitly adding the block to the course.
- Various internal code cleanup fixes
- Dropped support for Moodle 3.3-3.4 and added support for Moodle 3.7

## 3.3.7 (April 5, 2019)

- Add activity modules as a default applicable format (supports the Poster module).

## 3.3.6 (January 30, 2019)

- Refactor email form to remove outdated jQuery dependency.

## 3.3.5 (January 8, 2019)

- Further support for GDPR compliance

## 3.3.4 (June 1, 2018)

- Updated for GDPR compliance.
- Fixed bug which prevented users from displaying when an incompatible groups mode was selected.
- Styling fix for role filter label.
- Use proper HTML labels in the form.

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
