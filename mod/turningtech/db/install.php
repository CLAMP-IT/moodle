<?php

// This file replaces:
//   * STATEMENTS section in db/install.xml
//   * lib.php/modulename_install() post installation hook
//   * partially defaults.php

function xmldb_turningtech_install() {
    global $DB;

    /// insert turningtech data
    $device_type       = new stdClass();
    $device_type->type = 'Response Card';
    $device_type->id   = $DB->insert_record('turningtech_device_types', $device_type);

    $device_type       = new stdClass();
    $device_type->type = 'Response Ware';
    $device_type->id   = $DB->insert_record('turningtech_device_types', $device_type);
}
