# moodle-block_accessibility_filescan

Moodle block plugin to display accessibility results from the [local_accessibility_filescan](https://github.com/Swarthmore/moodle-local_accessibility_filescan) Moodle plugin.

## Requirements

* Moodle 4.0+

## Installation

### From the CLI

```sh
MOODLE_ROOT=/path/to/your/moodle
git clone https://github.com/aweed1/moodle-block_accessibility_filescan $MOODLE_ROOT/blocks/accessibility_filescan
```

### From Moodle

Download the `main` branch of this repo, then upload via the Moodle plugin installation page.

## Configuration

There are configurations for help links for each type of accessibility problem.  These links can be set to specific URLs for remediation of the file.

## Screenshots

<img src="filescan_chart.png" alt="Screenshot of the Accessibility Filescan plugin for Moodle, showing a pie chart with results for course PDF accessibility checks: 2 PDFs pass all checks (green), 3 PDFs pass some checks (orange), and none fail all checks (no red segment). A 'Details' button is below the chart, and the last scan date is listed as Tuesday, March 26, 2024." width="40%" />

<img src="filescan_details.png" alt="Screenshot showing a table from the Moodle Accessibility Filescan plugin with columns for filename, language, text, title, tagging, and page count. Entries show varying checkmark and 'x' marks for each criterion, and a 'Download to CSV' option is available. The last scan time is noted at the bottom" />