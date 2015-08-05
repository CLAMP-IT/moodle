Moodle Remote Courses block
===========================

This block prints a list of courses from another Moodle instance. It is designed for use with the [Remote Courses local plugin](https://github.com/LafColITS/moodle-local_remote_courses).

Configuration
-------------
To use the block you'll need to configure the local web service on the remote installation. The block is hard-coded to query the `local_remote_courses_get_courses_by_username` function over REST.

Requirements
------------
- Moodle 2.6 (build 2013111800 or later)

Installation
------------
Copy the remote_courses folder into your /local directory and visit your Admin Notification page to complete the installation.

Author
------
Charles Fulton (fultonc@lafayette.edu)