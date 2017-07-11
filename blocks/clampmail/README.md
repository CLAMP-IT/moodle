# Quickmail

[![Build Status](https://travis-ci.org/CLAMP-IT/clampmail.svg)](https://travis-ci.org/CLAMP-IT/clampmail)

Quickmail is a Moodle block that provides selective, bulk emailing within courses. CLAMPMail is a fork maintained by the Collaborative Liberal Arts Moodle Project which sends attachments via email instead of providing a download link within Moodle.

## Features

* Multiple attachments
* Drafts
* Signatures
* Filter by Role
* Filter by Groups
* Optionally allow Students to email people within their group.
* Alternate sending email
* Embed images and other content in emails and signatures

### Multiple attachments

Quickmail supports multiple attachments by zipping up a Moodle filearea, and
sending it along to `email_to_user`.

1. Quickmail assumes that `$CFG->tempdir` is in `$CFG->dataroot`. This
limitation exists because Quickmail uses `email_to_user`.
2. Make sure your email service supports zip files, otherwise they will be filtered.

### Alternate emails

Teachers may define alternate emails for sending. These are available course-wide for anyone with the proper role.

## Installation

Visit <https://github.com/CLAMP-IT/clampmail> to either download a package or clone the git repository. Then visit the admin screen to allow the install to complete.

## License

Quickmail adopts the same license that Moodle does.