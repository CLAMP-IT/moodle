<?php
require_once($CFG->dirroot . '/mod/ipal/version.php');//Getting IPAL version to send to ComPADRE
$ipalversion = $module -> version;
$compadreURL = 'http://www.compadre.org/ipal/index.cfm?ipalversion='.$ipalversion;
//The compadreURL will need to be put in here or have this obtained from somewhere in the moodle code.
echo "<form method='POST' action='$compadreURL'>";
echo "\n<input type='hidden' name='email' value='".$USER->email."'>";
echo "\n<input type='hidden' name='emailhash' value='".sha1($USER->email)."'>";
echo "\n<input type='hidden' name='firstName' value='".$USER->firstname."'>";
echo "\n<input type='hidden' name='lastName' value='".$USER->lastname."'>";
echo "\n<input type='hidden' name='courseName' value='".$course->shortname."'>";
echo "\n<input type='hidden' name='moodleURL' value='".$CFG->wwwroot."'>";
echo "\n<input type='hidden' name='cmid' value='".$quiz->cmid."'>";
echo "\n<input type='hidden' name='sessKeyHash' value='We will supply this'>";
echo "\n<input type='submit' name='submit' value='Get questions from ComPADRE'>";
echo "\n</form>";
//The return url will be $_POST['moodleURL'].'/ipal/compadre_questions.php?cmid='.$_POST['cmid']
//Compadre will return the sessKeyValue back to the Moodle site
?>