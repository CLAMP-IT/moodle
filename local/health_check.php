<?php
/* simple health check that just makes sure the Moodle database is up and running - trying to avoid the infinite session creation of the old method */

define('NO_MOODLE_COOKIES',true);
require(dirname(dirname(__FILE__)).'/config.php');

global $DB;
$user = $DB->get_record('user',array('username' => 'admin'),'id,username,email');
if ($user->id == 2 ) {
  print "ok";
} else {
  print "fail";
}

