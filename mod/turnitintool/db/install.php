<?php
/**
 * @package   turnitintool
 * @copyright 2012 Turnitin
 */

function xmldb_turnitintool_install() {
    global $DB;

    if (!is_callable(array($DB,'get_record'))) {
		/// Install logging support (pre 2.0 only)
	    update_log_display_entry('turnitintool', 'view', 'turnitintool', 'name');
	    update_log_display_entry('turnitintool', 'add', 'turnitintool', 'name');
	    update_log_display_entry('turnitintool', 'update', 'turnitintool', 'name');
    }
}

/* ?> */