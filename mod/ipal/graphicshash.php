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

//Modification by Junkin
//A function to optain the question type of a given question
//Redundant with function ipal_get_question_type in /mod/ipal/graphics.php and ipal_get_qtype in /mod/ipal/locallib.php
function ipal_get_qtype($questionid){
	global $DB;
	$questiontype=$DB->get_record('question',array('id'=>$questionid));
	return($questiontype->qtype); 
}


function ipal_count_questions($question_id){
	global $DB;
	global $ipal;
	$qtype = ipal_get_qtype($question_id);
	if($qtype == 'essay'){
	     $labels[]='Responses';
		 $answers=$DB->get_records('ipal_answered',array('question_id'=>$question_id));
		 $sum=0;//The sum of the id's for all answers. If any student submits a new answer, this sum must change.
		 foreach($answers as $answers){
		      $sum =$sum + $answers->id;
		 }
		 $data[] = $sum;
	}
	else
	{
       $answers=$DB->get_records('question_answers',array('question'=>$question_id));
	   foreach($answers as $answers)
		{	
			$labels[]=$answers->answer;
			$data[]=$DB->count_records('ipal_answered', array('ipal_id'=>$_GET['ipalid'],'answer_id'=>$answers->id));        

        }
     }
return( "?data=".implode(",",$data)."&labels=".implode(",",$labels)."&total=10");

}
echo md5("graph.php".ipal_count_questions(ipal_show_current_question_id($_GET['ipalid'])));
?>

