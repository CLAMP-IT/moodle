<?php
include_once('../../config.php');
function findInstructor($c_num){
global $DB;
$query="SELECT u.id FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c WHERE u.id = r.userid AND r.contextid = cx.id AND cx.instanceid = c.id AND r.roleid =3 AND 
c.id = ".$c_num." AND cx.contextlevel =50";

$result=$DB->get_record_sql($query);echo "\n<br />result is ".print_r($result);//id.$result->ipal	;
echo "\n<br />query is ".$query;
return md5($result->id.$result->ipal);
}
$c_num = $_GET['courseid'];
echo "\n<br />input courseid as a GET value";
echo "\n<br />findINstructor is ".findInstructor($c_num);
echo "\n<br />the hash of 8 is ".md5('8');
