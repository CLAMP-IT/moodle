# Mahara Local Plugin

This plugin allows your Moodle installation to subscribe to Mahara XML-RPC endpoints. Included is a table housing
portfolio's pulled from Mahara by other plugins.

While its original purpose is a requirement for the [Maraha assignment plugins][1], it can easily be extended to
support more XML-RPC endpoints.

## Installation

Installation of the plugin is very easy. There are two installation methods:

1. Download the source archive for this plugin, and extract at the following location: `{Moodle_Root}/local/mahara`
2. Execute the following command:

```
> git clone git@github.com:fellowapeman/local-mahara.git {Moodle_Root}/local/mahara
```

The remaining installation is taken care of by Moodle by clicking on *Notifications*.

## Associated plugins

There are currently two plugins that require this integration:

1. [Mahara assignment submissions][1]
2. [Mahara assignment feedback][2]

[1]: https://github.com/fellowapeman/assign-mahara
[2]: https://github.com/fellowapeman/assign-mahara-feedback

## License

The Moodle local-mahara plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

The Moodle local-mahara plugin is distributed in the hope that it will be useful,
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
