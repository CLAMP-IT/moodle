# Mahara Assignment Feedback Plugin

This feedback plugin offers a purely supporting role to its [submission sibling][1]. If a teacher enables this plugin on a Moodle assignment that is using the Mahara assignment submission plugin, then any submitted Mahara pages & collections will be unlocked on the Mahara side once their Moodle submission is graded.

(Without this plugin, Mahara pages that are submitted through the Moodle submission plugin, remain permanently locked in Mahara, to provide a grading audit trail.)

## Requirements

- Moodle 2.8
- [Fully integrated Moodle -> Mahara instances][2]
- [The Mahara assignment submission plugin for Moodle][1]

## Installation

Make sure your Moodle installation is fully integrated with a Mahara instance. Then you must install this
plugin in one of two ways:

1. Download the source archive and extract it to the following directory: `{Moodle_Root}/mod/assign/feedback/mahara`
2. Execute the folowing command:

```
> git clone git@github.com:MaharaProject/moodle-assignfeedback_mahara.git {Moodle_Root}/mod/assign/feedback/mahara
```

The remainder of the installation can be achieved within Moodle by clicking on the _Notifications_ link.

## Upgrading

If you are upgrading from an earlier version of this plugin, you will need to do the following:

1. Make sure your Mahara assignment submission plugin is updated to the latest version from [Catalyst IT's github repository][1].

2. Run the Installation steps listed above.

3. Uninstall the [Mahara local plugin][3] if it is present.

## License

The Mahara assignment feedback plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

The Mahara assignment feedback plugin is distributed in the hope that it will be useful,
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

[1]: https://github.com/MaharaProject/moodle-assignsubmission_mahara
[2]: http://manual.mahara.org/en/1.9/mahoodle/mahoodle.html
[3]: https://github.com/fellowapeman/moodle-local_mahara
