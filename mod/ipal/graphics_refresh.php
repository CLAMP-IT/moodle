<html>
<head>
<meta http-equiv="refresh" content="2;url=?ipalid=<?php echo $_GET['ipalid']; ?>">
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

function ipal_count_questions($question_id){
	global $DB;
	global $ipal;
       $answers=$DB->get_records('question_answers',array('question'=>$question_id));
                  foreach($answers as $answers)
{	
			$labels[]=strip_tags($answers->answer);
			$data[]=$DB->count_records('ipal_answered', array('ipal_id'=>$_GET['ipalid'],'answer_id'=>$answers->id));        

        }

return( "?data=".implode(",",$data)."&labels=".implode(",",$labels)."&total=10");

}
echo "<img src=\"graph.php".ipal_count_questions(ipal_show_current_question_id($_GET['ipalid']))."\"></img>";
?>
</body>
</html>
