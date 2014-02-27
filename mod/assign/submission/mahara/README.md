# Mahara Assignment Submission Plugin

This repository houses an updated Mahara assignment submission plugin.

## Features

- XML-RPC integration with a Mahara installation
- Select portfolio
- If multiple submissions are allowed, it releases the previously selected submission
- Abides by Moodle assignments config options
- Popups for quickly previewing submissions (no windows... no tabs)

## Requirements

- Moodle 2.3+
- PHP 5.4+
- [Fully integrated Moodle -> Mahara instances][3]
- [Updated Mahara local plugin][1]
- [Mahara Feedback plugin][2] (Optional, but reccommended)

## Installation

Be sure that you have a version of Moodle that is equal or greater than 2.3. Also make sure you have successfully
integrated your Moodle installation with a Mahara instance.

Mahara has detailed documenation on how to achieve that [on their wiki][3]. Once you have done that, you must
install the [updated Mahara local plugin][1] on your Moodle installation. Then, install this plugin in one of two ways:

1. Download the source archive and extract its contents to the following location `{Moodle_Root}/mod/assign/submission/mahara`
2. Execute the following command:

```
> git clone git@github.com:fellowapeman/moodle-assign_mahara.git {Moodle_Root}/mod/assign/submission/mahara
```

The remainder of the install is taken care of by Moodle by clicking on _Notifcations_.

## Feedback plugin

The only reason the feedback plugin exists is for properly releasing graded submissions and for Mahara outcomes. It's an optional install because it's technically not required for a fully functional Mahara assignment, but it's certainly reccommeded.

[1]: https://github.com/fellowapeman/moodle-local_mahara
[2]: https://github.com/fellowapeman/moodle-assignfeedback_mahara
[3]: http://manual.mahara.org/en/1.5/mahoodle/mahoodle.html

## License

The Moodle assign-mahara plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

The Moodle assign-mahara plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

For a copy of the GNU General Public License see http://www.gnu.org/licenses/.

## Credits

Developed for the University of Portland by Philip Cali and Tony Box (box@up.edu).

The original Moodle 1.9 version of these plugins were funded through a grant from the New Hampshire Department of Education to a collaborative group of the following New Hampshire school districts:

- Exeter Region Cooperative
- Windham
- Oyster River
- Farmington
- Newmarket
- Timberlane School District
  
The upgrade to Moodle 2.0 and 2.1 was written by Aaron Wells at Catalyst IT, and supported by:

- NetSpot
- Pukunui Technology
