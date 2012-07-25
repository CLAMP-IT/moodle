<html>
<head>
<?php
if(isset($_GET['refresh'])){
echo "<meta http-equiv=\"refresh\" content=\"3;url=?refresh=true&ipalid=".$_GET['ipalid']."\">";
}
?>
</head>
<body>
<?php
include '../../config.php';
include $CFG->dirroot.'/lib/graphlib.php'; 

function ipal_who_sofar($ipal_id){
global $DB;
$records=$DB->get_records('ipal_answered',array('ipal_id'=>$ipal_id));
foreach($records as $records){
$answer[]=$records->user_id;
}
return(count(@array_unique($answer)));
}



function ipal_show_current_question_id($ipal_id){
	if(!isset($_GET['id'])){
        global $DB;
		if($DB->record_exists('ipal_active_questions', array('ipal_id'=>$ipal_id))){
          $question=$DB->get_record('ipal_active_questions', array('ipal_id'=>$ipal_id));
          return($question->question_id);
		}
		return(0);
	}
	else{
		return((int)$_GET['id']);
	}
}

function ipal_count_active_responses(){
	global $DB;
	$question_id=ipal_show_current_question_id((int)$_GET['ipalid']);
	$total=$DB->count_records('ipal_answered',array('question_id'=>$question_id,'ipal_id'=>(int)$_GET['ipalid']));
	return((int)$total);
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

//A function to display asnwers to essay questions
function ipal_display_essay_by_id($question_id){
	global $DB;
	//$question_id = ipal_show_current_question_id($ipalid);
	$answerids=$DB->get_records('ipal_answered',array('question_id'=>$question_id));
	foreach($answerids as $id){
		//$answerdata=$DB->get_record('ipal_answered',array('id'=>$id->id));//Get answers from ipal_questions_answered table, field a_text
		$answers[]=$id->a_text;
	}
	return($answers);
}

echo "Total Responses --> ".ipal_count_active_responses()."/".ipal_who_sofar($_GET['ipalid']);

//A function to optain the question type of a given question
function ipal_get_question_type($questionid){
	global $DB;
	$questiontype=$DB->get_record('question',array('id'=>$questionid));
	return($questiontype->qtype); 
}

$ipalid = $_GET['ipalid'];
$qtype = ipal_get_question_type(ipal_show_current_question_id($ipalid));
if($qtype == 'essay'){
	//$answers = ipal_display_essay_answers($ipalid);
	$answers = ipal_display_essay_by_id(ipal_show_current_question_id($_GET['ipalid']));
	foreach($answers as $answer){
		echo "\n<br />".strip_tags($answer);
	}
}
else
{//Only show graph if question is not an essay question
	echo "<br><img src=\"graph.php".ipal_count_questions(ipal_show_current_question_id($_GET['ipalid']))."\"></img>";
}
?>
</body>
</html>
