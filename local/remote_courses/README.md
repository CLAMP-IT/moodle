Moodle Remote Courses
=====================

This local module provides a web service which returns a given user's courses based on username. It returns the courses sorted by access time, with the most recently-access course at the top. The standard use case is to create deep links to courses in one Moodle installation in another Moodle installation.

Configuration
-------------
To use this service you will need to create the following:

1. A web service on a Moodle installation
2. A user with sufficient permissions to use the web service
3. A token for that user

See [Using web services](https://docs.moodle.org/29/en/Using_web_services) in the Moodle documentation for information about creating and enabling web services. The user will need the following capabilities in addition to whichever protocol you enable:

- `moodle/course:view`
- `moodle/course:viewhiddencourses`
- `moodle/course:viewparticipants`

Requirements
------------
- Moodle 2.6 (build 2013111800 or later)

Installation
------------
Copy the remote_courses folder into your /local directory and visit your Admin Notification page to complete the installation.

Author
------
Charles Fulton (fultonc@lafayette.edu)