<?php
/**
* @package turnitintool
* @copyright 2012 Turnitin
*/

$handlers = array();

/* List of events thrown from turnitintool module

assessable_submitted
->modulename = 'turnitintool';
->cmid = // The cmid of the assign.
->itemid = // The submission id of the user submission (recorded in mdl_turnitintool_submissions).
->courseid = // The course id of the course the assign belongs to.
->userid = // The user id that the attempt belongs to.

*/