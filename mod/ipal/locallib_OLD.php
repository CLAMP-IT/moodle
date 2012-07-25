<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Internal library of functions for module ipal
 *
 * All the ipal specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod_ipal
 * @copyright 2011 Eckerd College 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("$CFG->libdir/formslib.php");
defined('MOODLE_INTERNAL') || die();
require_js(array('yui_yahoo', 'yui_event'));



//
//Get Answers For a particular question id
// TODO: the 2.1 question engine may make this fuction unneeded
//
function ipal_get_answers($question_id){
	global $ipal;
	global $DB;
	global $CFG;
	$line="";
	$answers=$DB->get_records('question_answers',array('question'=>$question_id));
		  foreach($answers as $answers){
			  $line .= $answers->answer;
			   $line .= "&nbsp;";
		  }
	return($line);
}


//
//
//Setup The Grid View
//todo: lots to add here
//
function ipal_grid_view(){
global $DB;
global $ipal;

$questions=explode(",",$ipal->questions);

echo "<table border=\"1\" width=\"100%\">\n";
echo "<tr><td>Name</td>\n";
foreach($questions as $question){
$question_data=$DB->get_record('question',array('id'=>$question));
echo "<td><div style=\"word-wrap: break-word;\">".substr(trim(strip_tags($question_data->name)),0,80)."</div></td>\n";
}
echo "</tr>\n";

foreach(ipal_who_sofar($ipal->id) as $user){
	echo "<tr><td>".ipal_find_student($user)."</td>\n";
		foreach($questions as $question){
        		if($question != ""){
$answer=$DB->get_record('ipal_answered',array('ipal_id'=>$ipal->id,'user_id'=>$user, 'question_id'=>$question));
if(!$answer){echo "<td>&nbsp;</td>\n";}
else{
$answer_data=$DB->get_record('question_answers',array('id'=>$answer->answer_id));
echo "<td><div style=\"word-wrap: break-word;\">".substr(trim(strip_tags($answer_data->answer)),0,40)."</div></td>\n";
}
			}
		}
echo "</tr>\n";
	}
echo "</table>\n";
}



//
//Who has answered questions so far?
//
function ipal_who_sofar($ipal_id){
global $DB;

$records=$DB->get_records('ipal_answered',array('ipal_id'=>$ipal_id));

foreach($records as $records){
$answer[]=$records->user_id;
}
return(array_unique($answer));
}



//
//find student name
//
function ipal_find_student($userid){
global $DB;
$user=$DB->get_record('user',array('id'=>$userid));
$name=$user->lastname.", ".$user->firstname;
return($name);
}

//
//find responses by Student id
//
function ipal_find_student_responses($userid,$ipal_id){
global $DB;
$responses=$DB->get_records('ipal_answered',array('ipal_id'=>$ipal_id, 'user_id'=>$userid));
foreach($responses as $records){
$temp[]="Q".$records->question_id." = ".$records->answer_id;
}
return(implode(",",$temp));
}


//
//Gets answers formated for the student display
//TODO: Replace with the rendering engine from questionlib
//
function ipal_get_answers_student($question_id){
        global $ipal;
        global $DB;
        global $CFG;

	$answerarray=array();  
	$line="";
	$answers=$DB->get_records('question_answers',array('question'=>$question_id));
			foreach($answers as $answers){
				$answerarray[$answers->id]=$answers->answer;  
			}
			
	return($answerarray);
}




//
//Get the Questions if in the student context
//
function ipal_get_questions_student($qid){

        global $ipal;
        global $DB;
        global $CFG;
		$q='';
		$pagearray2=array();
		
        $aquestions=$DB->get_record('question',array('id'=>$qid));
			if($aquestions->questiontext != ""){ 

	$pagearray2[]=array('id'=>$qid, 'question'=>$aquestions->questiontext, 'answers'=>ipal_get_answers_student($qid));
							}
        return($pagearray2);
}


//
//Get the questions in any context (like the instructor)
//
function ipal_get_questions(){ 
 
	global $ipal;
	global $DB;
	global $CFG;
	$q='';	
	$pagearray2=array();
	 // is there an quiz associated with an ipal?
//	$ipal_quiz=$DB->get_record('ipal_quiz',array('ipal_id'=>$ipal->id));
	
	// get quiz and put it into an array
	$quiz=$DB->get_record('ipal',array('id'=>$ipal->id));
	
	//Get the question ids
	$questions=explode(",",$quiz->questions);
	
	//get the questions and stuff them into an array
		foreach($questions as $q){	
			
		  $aquestions=$DB->get_record('question',array('id'=>$q));
		  if(isset($aquestions->questiontext)){
		  $pagearray2[]=array('id'=>$q, 'question'=>strip_tags($aquestions->questiontext), 'answers'=>ipal_get_answers($q));
				  
		  }
										
		}
  
	return($pagearray2);
	}



//
//This function counts questions based on ipal id
//TODO: Make this funtion check on a count by query
//of answer id
//
function ipal_count_questions($question_id){
	global $DB;
	global $ipal;
       $answers=$DB->get_records('question_answers',array('question'=>$question_id));
                  foreach($answers as $answers)
{	
			$labels[]=htmlentities(substr($answers->answer,10));
			$data[]=$DB->count_records('ipal_answered', array('ipal_id'=>$ipal->id,'answer_id'=>$answers->id));        

        }

return( "?data=".implode(",",$data)."&labels=".implode(",",$labels)."&total=10");

//print $DB->count_records('ipal_answered', array('ipal_id'=>$ipal->id,'answer_id'=>$answer_id));
}


//
//This function create the form for the instructors (or anyone higher than a student)
//to view.
//
function ipal_make_instructor_form(){
global $ipal;
$myform="<form action=\"?".$_SERVER['QUERY_STRING']."\" method=\"post\">\n";
	$myform .= "\n";
		foreach(ipal_get_questions() as $items){
$myform .= "<input type=\"radio\" name=\"question\" value=\"".$items['id']."\" />";

$myform .="<a href=\"show_question.php?qid=".$items['id']."&id=".$ipal->id."\" target=\"_blank\">[question]</a>";
$myform .= "<a href=\"standalone_graph.php?id=".$items['id']."&ipalid=".$ipal->id."\" target=\"_blank\">[graph]</a>".$items['question']."<br /><br />\n";
		}
if(ipal_check_active_question()){
	$myform .= "<input type=\"submit\" value=\"Send Question\" />\n</form>\n";
}else{
$myform .= "<input type=\"submit\" value=\"Start Polling\" />\n</form>\n";}

	return($myform);
}


//
//This function sets the question in the database so the client
//functions can find what quesiton is active.  And it does it fast.
//
function ipal_send_question(){
	global $ipal;
	global $DB;
	global $CFG;
	
	$ipal_course=$DB->get_record('ipal',array('id'=>$ipal->id));
	$record = new stdClass();
	$record->id = '';
	$record->course = $ipal_course->course;
	$record->ipal_id = $ipal->id;
	$record->quiz_id = $ipal->id;
	$record->question_id = $_POST['question'];
	$record->timemodified = time();
	  if($DB->record_exists('ipal_active_questions', array('ipal_id'=>$ipal->id))){
	  $mybool=$DB->delete_records('ipal_active_questions', array('ipal_id'=>$ipal->id));
	  }
	$lastinsertid = $DB->insert_record('ipal_active_questions', $record);

}

//
//
//This function clears the current question
//
//
//
function ipal_clear_question(){
	global $ipal;
	global $DB;
	
	  if($DB->record_exists('ipal_active_questions', array('ipal_id'=>$ipal->id))){
	  $mybool=$DB->delete_records('ipal_active_questions', array('ipal_id'=>$ipal->id));
	  }
}




//
//
//Java script for checking to see if the chart need to be updated.
//
//
function ipal_java_graphupdate() {
global $ipal;
global $DB;
echo "\n\n<script type=\"text/javascript\">\nvar http = false;\nvar x=\"\";\n\nif(navigator.appName == \"Microsoft Internet Explorer\") {\nhttp = new ActiveXObject(\"Microsoft.XMLHTTP\");\n} else {\nhttp = new XMLHttpRequest();
}";

echo "\n\nfunction replace() {\nvar t=setTimeout(\"replace()\",3000);\nhttp.open(\"GET\", \"graphicshash.php?ipalid=".$ipal->id."\", true);";
echo "\nhttp.onreadystatechange=function() {\nif(http.readyState == 4) {\nif(http.responseText != x){";
echo "\nx=http.responseText;\n";
$state=$DB->get_record('ipal',array('id'=>$ipal->id));
if($state->preferredbehaviour =="Graph"){
echo "document.getElementById('graphIframe').src=\"graphics.php?ipalid=".$ipal->id."\"";
}
else
{
echo "document.getElementById('graphIframe').src=\"gridview.php?id=".$ipal->id."\"";
}

echo "}\n}\n}\nhttp.send(null);\n}\nreplace();\n</script>";

}


//
//
//Java script for checking to see if the Question has Changed.
//
//
function ipal_java_questionUpdate() {
global $ipal;
echo "\n\n<script type=\"text/javascript\">\nvar http = false;\nvar x=\"\";\nvar myCount=0;\n\nif(navigator.appName == \"Microsoft Internet Explorer\") {\nhttp = new ActiveXObject(\"Microsoft.XMLHTTP\");\n} else {\nhttp = new XMLHttpRequest();
}";

echo "\n\nfunction replace() {\nvar t=setTimeout(\"replace()\",3000);\nhttp.open(\"GET\", \"currentQ.php?ipalid=".$ipal->id."\", true);";
echo "\nhttp.onreadystatechange=function() {\nif(http.readyState == 4) {\n\nif(http.responseText != x && myCount > 1){\n";
echo "window.location.reload();\n";
echo "}\nx=http.responseText;}\n}\nhttp.send(null);\nmyCount++;}\n\nreplace();\n</script>";

}
//
//
//Make the button controls on the instructor interface
//
//
function instructor_buttons(){
$disabled="";
$myform="<form action=\"?".$_SERVER['QUERY_STRING']."\" method=\"post\">\n";
	$myform .= "\n";
if(!ipal_check_active_question()){$disabled= "disabled=\"disabled\"";}

$myform .= "<input type=\"submit\" value=\"Stop Polling\" name=\"clearQuestion\" ".$disabled."/>\n</form>\n";
	
return($myform);
	
}

//
//Toggle View
//
function ipal_toggle_view($newstate){
$myform="<form action=\"?".$_SERVER['QUERY_STRING']."\" method=\"post\">\n";
        $myform .= "\n";
$myform .= "<INPUT TYPE=hidden NAME=ipal_view VALUE=\"changeState\">";
$myform .= "Change View to <input type=\"submit\" value=\"$newstate\" name=\"gridView\"/>\n</form>\n";

return($myform);

}



//
//
//Compadre button
//
//
function ipal_show_compadre($cmid){
	$myform="<form action=\"ipal_quiz_edit.php?cmid=".$cmid."\" method=\"post\">\n";
	$myform .= "\n";
	$myform .= "<input type=\"submit\" value=\"Add/Change Questions\" />\n</form>\n";
	return($myform);
}


//
//This function puts all the elements together for
//the instructors interface.  This is the last stop
//before its displayed
//
function ipal_display_instructor_interface($cmid){
global $DB;
	global $ipal;

    if(isset($_POST['clearQuestion'])){
        ipal_clear_question();
    }
    if(isset($_POST['question'])){
        ipal_send_question();
    }

	$state=$DB->get_record('ipal',array('id'=>$ipal->id));
	if($_POST['ipal_view']=="changeState"){
         if($state->preferredbehaviour == "Graph"){
             $result=$DB->set_field('ipal', 'preferredbehaviour', 'Grid',array('id'=>$ipal->id));
			 $newstate = 'Histogram';
         }else{
             $result=$DB->set_field('ipal', 'preferredbehaviour','Graph',array('id'=>$ipal->id));
			 $newstate = 'Spreadsheet';
         }
	}
	else
	{
         if($state->preferredbehaviour == "Graph"){$newstate = 'Spreadsheet';}
		 else{$newstate = 'Histogram';}
	}
    if(($newstate == 'Histogram') and (ipal_get_qtype(ipal_show_current_question_id()) == 'essay')){
	    $newstate = 'Responses';
	}

    ipal_java_graphupdate();

echo "<table><tr><td>".instructor_buttons()."</td><td>".ipal_show_compadre($cmid)."</td><td>".ipal_toggle_view($newstate)."<td></tr></table>";
echo  ipal_make_instructor_form();
echo "<br><br>";
$state=$DB->get_record('ipal',array('id'=>$ipal->id));
if($state->preferredbehaviour =="Graph"){
	if(ipal_show_current_question()==1){
echo "<br>";
echo "<br>";
echo "<iframe id= \"graphIframe\" src=\"graphics.php?ipalid=".$ipal->id."\" height=\"535\" width=\"723\"></iframe>";
echo "<br><br><a onclick=\"window.open('popupgraph.php?ipalid=".$ipal->id."', '',
     'width=620,height=450,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes');
     return false;\"
     href=\"popupgraph.php?ipalid=".$ipal->id."\" target=\"_blank\">Open a new window for the graph.</a>";}
}else{
echo "<br>";
echo "<br>";
echo "<iframe id= \"graphIframe\" src=\"gridview.php?id=".$ipal->id."\" height=\"535\" width=\"723\"></iframe>";
//echo "<br><br><a href=\"gridview.php?refresh=true&id=".$ipal->id."\" target=\"_blank\">Open a new window for the graph.</a>";
echo "<br><br><a onclick=\"window.open('popupgraph.php?id=".$ipal->id."', '',
     'width=620,height=450,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes');
     return false;\"
     href=\"popupgraph.php?ipalid=".$ipal->id."\" target=\"_blank\">Open a new window for the graph.</a>";

//ipal_grid_view();	
}}



//
//This function disables the question if the student has already answered it.
//Which also, confirms the question is in the database for that user.
//
function ipal_check_if_answered($user_id, $question_id, $quiz_id, $class_id, $ipal_id){
	global $DB;
	  if($DB->record_exists('ipal_answered', array('user_id'=>$user_id, 'question_id'=>$question_id, 'quiz_id'=>$quiz_id, 'class_id'=>$class_id, 'ipal_id'=>$ipal_id ))){
		  return("disabled=\"disabled\"");
		  }else{
		  return("");
	  }

}


//
//This function finds the current question that is active for the ipal
//that it was requested from.  
// TODO: make a prettier interface.
//
function ipal_show_current_question(){
	global $DB;
	global $ipal;
	  if($DB->record_exists('ipal_active_questions', array('ipal_id'=>$ipal->id))){
	  $question=$DB->get_record('ipal_active_questions', array('ipal_id'=>$ipal->id));
	  $questiontext=$DB->get_record('question',array('id'=>$question->question_id));  
	echo "The current question is -> ".strip_tags($questiontext->questiontext);
return(1);
	  }
	else
	  {
	  return(0);
	  }
}

//
//
//Is there a question active?
//
//
function ipal_check_active_question(){
        global $DB;
        global $ipal;
          if($DB->record_exists('ipal_active_questions', array('ipal_id'=>$ipal->id))){
return(1);
          }
        else
          {
          return(0);
          }
}


//
//This function finds the current question that is active for the ipal
//that it was requested from.  
// TODO: make a prettier interface.
//
function ipal_show_current_question_id(){
        global $DB;
        global $ipal;
          if($DB->record_exists('ipal_active_questions', array('ipal_id'=>$ipal->id))){
          $question=$DB->get_record('ipal_active_questions', array('ipal_id'=>$ipal->id));
	return($question->question_id);
          }
else{
return(0);
}
}

//Modification by Junkin
//A function to optain the question type of a given question
//REdundant with function ipal_tet_question_type in /mod/ipal/graphics.php
function ipal_get_qtype($questionid){
	global $DB;
	$questiontype=$DB->get_record('question',array('id'=>$questionid));
	return($questiontype->qtype); 
}



//
//This is the function that makes the form for the student
//to answer from.  
//
function ipal_make_student_form(){
	global $ipal;
	global $DB;
	global $CFG;
	global $USER;
	global $course;
	$disabled='';
	
//	$ipal_quiz=$DB->get_record('ipal_quiz',array('ipal_id'=>$ipal->id));
	
	
	if($DB->record_exists('ipal_active_questions', array('ipal_id'=>$ipal->id))){
		$question=$DB->get_record('ipal_active_questions', array('ipal_id'=>$ipal->id));
		$qid=$question->question_id;
		$myFormArray= ipal_get_questions_student($qid);
//		$disabled=ipal_check_if_answered($USER->id,$myFormArray[0]['id'],$ipal_quiz->quiz_id,$course->id,$ipal->id);
		echo "<br><br><br>";
		echo "<form action=\"?".$_SERVER['QUERY_STRING']."\" method=\"post\">\n"; 
		echo $myFormArray[0]['question'];
		echo "<br>";
		if(ipal_get_qtype($qid) == 'essay'){
			echo  "<INPUT TYPE=\"text\" NAME=\"a_text\" >\n<br />";
			echo  "<INPUT TYPE=hidden NAME=\"answer_id\" value=\"-1\">";
		}
		else
		{
			foreach($myFormArray[0]['answers'] as $k=>$v){
			echo "<input type=\"radio\" name=\"answer_id\" value=\"$k\" ".$disabled."/> ".strip_tags($v)."<br />\n";
//			echo "<br>";
			}
			echo "<INPUT TYPE=hidden NAME=a_text VALUE=\" \">";
		}
	echo "<INPUT TYPE=hidden NAME=question_id VALUE=\"".$myFormArray[0]['id']."\">";
	echo "<INPUT TYPE=hidden NAME=active_question_id VALUE=\"$question->id\">";
	echo "<INPUT TYPE=hidden NAME=course_id VALUE=\"$course->id\">";
	echo "<INPUT TYPE=hidden NAME=user_id VALUE=\"$USER->id\">";
	echo "<INPUT TYPE=submit NAME=submit VALUE=\"Submit\" ".$disabled.">";
	echo "<INPUT TYPE=hidden NAME=ipal_id VALUE=\"$ipal->id\">";
        echo "<INPUT TYPE=hidden NAME=instructor VALUE=\"".findInstructor($course->id)."\">";
	echo "</form>";
	}else{
	echo "<br><br>No Current Question.";}
}


function findInstructor($c_num){
global $DB;
$query="SELECT u.id FROM mdl_user u, mdl_role_assignments r, mdl_context cx, mdl_course c WHERE u.id = r.userid AND r.contextid = cx.id AND cx.instanceid = c.id AND r.roleid =3 AND 
c.id = ".$c_num." AND cx.contextlevel =50";

$result=$DB->get_record_sql($query);
return md5($result->id.$result->ipal);
}


//
//This is the code to insert the student responses into the database
function ipal_save_student_response($question_id, $answer_id, $active_question_id,$a_text,$instructor){
	global $ipal;
	global $DB;
	global $CFG;
	global $USER;
	global $course;

// Create insert for archive
	$record_arc = new stdClass();
	$record_arc->id = '';
	$record_arc->user_id = $USER->id;
	$record_arc->question_id = $question_id;
	$record_arc->quiz_id = $ipal->id;
	$record_arc->answer_id = $answer_id;
	$record_arc->a_text = $a_text;
	$record_arc->class_id = $course->id;
	$record_arc->ipal_id = $ipal->id;
	$record_arc->ipal_code = '1';
	$record_arc->shortname = $course->shortname;
	$record_arc->instructor = $instructor;
	$record_arc->time_created = time();
	$lastinsertid = $DB->insert_record('ipal_answered_archive', $record_arc);

//Create insert for current question
        $record = new stdClass();
        $record->id = '';
        $record->user_id = $USER->id;
        $record->question_id = $question_id;
        $record->quiz_id = $ipal->id;
        $record->answer_id = $answer_id;
        $record->a_text = $a_text;
        $record->class_id = $course->id;
        $record->ipal_id = $ipal->id;
        $record->ipal_code = $active_question_id;
        $record->time_created = time();

   if($DB->record_exists('ipal_answered', array('user_id'=>$USER->id, 'question_id'=>$question_id, 'ipal_id'=>$ipal->id ))){
$mybool=$DB->delete_records('ipal_answered', array('user_id'=>$USER->id, 'question_id'=>$question_id, 'ipal_id'=>$ipal->id ));
}
 $lastinsertid = $DB->insert_record('ipal_answered', $record);

}


//
//This is the function that puts the student interface together.
//it is the last stop before the display
//
function ipal_display_student_interface(){
	global $DB;
	ipal_java_questionUpdate();
	$priorresponse = '';
	if(isset($_POST['answer_id'])){
	  if($_POST['answer_id'] == '-1'){
	  	$priorresponse = "\n<br />Last answer you submitted: ".strip_tags($_POST['a_text']);
	  }
	  else
	  {
	       $answer_id = $_POST['answer_id'];
		   $answer = $DB->get_record('question_answers', array('id'=>$answer_id));
		   //$answer = $DB
		   $priorresponse = "\n<br />Last answer you submitted: ".strip_tags($answer->answer);
	  }
	  ipal_save_student_response($_POST['question_id'],$_POST['answer_id'],$_POST['active_question_id'],$_POST['a_text']);
	}
  echo $priorresponse;
  ipal_make_student_form();	
}


	



?>	
