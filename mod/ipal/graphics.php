<html>
<head>
</head>
<body>
<?php
include '../../config.php';
include $CFG->dirroot.'/lib/graphlib.php'; 

function ipal_show_current_question_id($ipal_id){
        global $DB;
          if($DB->record_exists('ipal_active_questions', array('ipal_id'=>$ipal_id))){
          $question=$DB->get_record('ipal_active_questions', array('ipal_id'=>$ipal_id));
          return($question->question_id);
          }
return(0);
}

function ipal_current_question_code($ipal_id){//Finds the id in the active quesstion table=ipal_code in ipal_answered table
     global $DB;
     if($DB->record_exists('ipal_active_questions', array('ipal_id'=>$ipal_id))){
          $question=$DB->get_record('ipal_active_questions', array('ipal_id'=>$ipal_id));
          return($question->id);
     }
return(0);    

}

function ipal_who_thistime($ipal_id){//What answers::user_id have been given to this specific question this time.
     global $DB;
     $question_code=ipal_current_question_code($ipal_id);
	 $records=$DB->get_records('ipal_answered',array('ipal_code'=>$question_code));
     foreach($records as $records){
           $answer[]=$records->user_id;
     }
     return(count(@array_unique($answer)));
}


function ipal_who_sofar($ipal_id){
global $DB;
$records=$DB->get_records('ipal_answered',array('ipal_id'=>$ipal_id));
foreach($records as $records){
$answer[]=$records->user_id;
}
return(count(@array_unique($answer)));
}

function ipal_count_thistime_responses(){//How many responses to this question this time 
     global $DB;
     $question_code=ipal_current_question_code((int)$_GET['ipalid']);
     $total=$DB->count_records('ipal_answered',array('ipal_code'=>$question_code));
     return((int)$total);
}


function ipal_count_active_responses(){
global $DB;
$question_id=ipal_show_current_question_id((int)$_GET['ipalid']);
$total=$DB->count_records('ipal_answered',array('question_id'=>$question_id,'ipal_id'=>(int)$_GET['ipalid']));
return((int)$total);
}


function ipal_count_question_codes($question_code){//What questions and what responses were given each time question sent
     global $DB;
     global $ipal;
     $question=$DB->get_record('ipal_active_questions', array('id'=>$question_code));
     $question_id=$question->question_id;
	 $answers=$DB->get_records('question_answers',array('question'=>$question_id));
     //$question_code =ipal_current_question_code($ipal_id->id);
	 foreach($answers as $answers){	
          $labels[]=ereg_replace("[^A-Za-z0-9 ]", "",substr(strip_tags($answers->answer),0,20));
          $data[]=$DB->count_records('ipal_answered', array('ipal_code'=>$question_code,'answer_id'=>$answers->id));        
        }

return( "?data=".implode(",",$data)."&labels=".implode(",",$labels)."&total=10");

}


function ipal_count_questions($question_id){
	global $DB;
	global $ipal;
       $answers=$DB->get_records('question_answers',array('question'=>$question_id));
                  foreach($answers as $answers)
{	
			$labels[]=ereg_replace("[^A-Za-z0-9 ]", "",substr(strip_tags($answers->answer),0,20));

			$data[]=$DB->count_records('ipal_answered', array('ipal_id'=>$_GET['ipalid'],'answer_id'=>$answers->id));        

        }

return( "?data=".implode(",",$data)."&labels=".implode(",",$labels)."&total=10");

}
//echo "Total Responses --> ".ipal_count_active_responses()."/".ipal_who_sofar($_GET['ipalid']);
echo "Total Responses --> ".ipal_count_thistime_responses()."/".ipal_who_sofar($_GET['ipalid']);

//Modification by Junkin
//A function to optain the question type of a given question
function ipal_get_question_type($questionid){
	global $DB;
	$questiontype=$DB->get_record('question',array('id'=>$questionid));
	return($questiontype->qtype); 
}

//A function to display asnwers to essay questions
function ipal_thistime_essay_answers($ipalid){//Get the essay answers for this time
	global $DB;
	$question_code = ipal_current_question_code($ipalid);
	$answerids=$DB->get_records('ipal_answered',array('ipal_code'=>$question_code));
	foreach($answerids as $id){
		//$answerdata=$DB->get_record('ipal_answered',array('id'=>$id->id));//Get answers from ipal_questions_answered table, field a_text
		$answers[]=$id->a_text;
	}
	if(!(isset($answers[0]))){
	      $answers[0]="No answers yet";
	}
	return($answers);
	
}


//A function to display asnwers to essay questions
function ipal_display_essay_answers($ipalid){
	global $DB;
	$question_id = ipal_show_current_question_id($ipalid);
	$answerids=$DB->get_records('ipal_answered',array('question_id'=>$question_id));
	foreach($answerids as $id){
		//$answerdata=$DB->get_record('ipal_answered',array('id'=>$id->id));//Get answers from ipal_questions_answered table, field a_text
		$answers[]=$id->a_text;
	}
	return($answers);
}
$ipalid = $_GET['ipalid'];
$qtype = ipal_get_question_type(ipal_show_current_question_id($ipalid));
if($qtype == 'essay'){
	//$answers = ipal_display_essay_answers($ipalid);
	$answers = ipal_thistime_essay_answers($ipalid);
	foreach($answers as $answer){
		echo "\n<br />".strip_tags($answer);
	}
}
else
{//Only show graph if question is not an essay question
	//echo "<img src=\"graph.php".ipal_count_questions(ipal_show_current_question_id($ipalid))."\"></img>";
	echo "<img src=\"graph.php".ipal_count_question_codes(ipal_current_question_code($ipalid))."\"></img>";
}
?>
</body>
</html>
