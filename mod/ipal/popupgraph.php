<html>
<head>
<?php
echo "<meta http-equiv=\"refresh\" content=\"3;url=?ipalid=".$_GET['ipalid']."\">";
?>
</head>
<body>
<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

//
//This function finds the current question that is active for the ipal
//that it was requested from.  
// TODO: make a prettier interface.
//
function ipal_show_current_question(){
	global $DB;
	global $state;
	$ipal = $state;
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
$ipalid= $_GET['ipalid'];
include_once('graphiframe.php');
?>
</body>
</html>