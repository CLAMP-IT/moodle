# moodle-local_accessibility_filescan

This plugin is a scheduled task that scans your Moodle instance for PDFs, and performs accessibility checks on them. Results can be viewed from the plugin table in your Moodle database, or through your Moodle site using the [block_accessibility_filescan](https://github.com/swarthmore/moodle-block_accessibility_filescan) plugin.

## Dependencies

* Moodle 4.00+

## Installation

### Dependencies

`poppler-utils`

### From the command line

```sh
MOODLE_WWW_ROOT=/path/to/your/moodle
git clone https://github.com/Swarthmore/moodle-local_accessibility_filescan $MOODLE_WWW_ROOT/local/accessibility_filescan
```

### How Does it Work?

This plugin will find PDFs across your Moodle instance, then scan them for accessibility. The plugin scans for the following:

1. Does the PDF has OCR'd text?
2. Is the PDF tagged?
3. Does the PDF have a language?
4. Does the PDF have a title?
5. Page count

PDFs are scanned at intervals in accordance to Moodle's cron system and results are stored in the plugin's database table.
