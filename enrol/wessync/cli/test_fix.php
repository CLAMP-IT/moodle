<?php

global $DB;

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require(dirname(dirname(dirname(dirname(__FILE__)))).'/lib/gradelib.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_ALL;

if (!enrol_is_enabled('wessync')) {
     print "Hmm";
    die;
}

// Update enrolments -- these handlers should autocreate courses if required
$enrol = enrol_get_plugin('wessync');

$course_id = $argv[1];
$user_id = $argv[2];
$return = grade_recover_history_grades($user_id, $course_id);

