<?php
include_once('../../config.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
require_once('version.php');
print_header_simple(get_string('supportinfo', 'turningtech'));


echo get_string('moduleversion', 'turningtech') . ": " . $module->version;
//phpinfo();

print_footer();

?>