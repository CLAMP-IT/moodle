Moodle Meta-course Group Synchronization
=========================================

Requirements
------------
- Moodle 2.6 (build 2013111800 or later)
- Meta-course enrolment (build 2013110500 or later)

Installation
------------
Copy the metasync folder into your Moodle /local directory and visit your Admin Notification page to complete the installation.

Usage
-----
After installation you may need to synchronize existing meta-course groups, to do this run the cli/sync.php script (use the --help
switch for further instructions on script usage).

Any future amendments to groups in 'child' courses will be reflected in 'parent' courses that use groups.

Author
------
Paul Holden (pholden@greenhead.ac.uk)

- Updates: https://moodle.org/plugins/view.php?plugin=local_metasync
- Latest code: https://github.com/paulholden/moodle-local_metasync

Changes
-------
Release 1.2 (build 2014080500)
- Only synchronize parent courses that use groups.

Release 1.1 (build 2014031300)
- Prevent synchronized group memberships being removed.

Release 1.0 (build 2014021001)
- First release.
