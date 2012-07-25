<?php
    require_once("../../../config.php");

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Create generic question</title>
</head>

<body>
<?php
  function ipal_random_string ($length=15) {
      $pool  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $pool .= 'abcdefghijklmnopqrstuvwxyz';
      $pool .= '0123456789';
      $poollen = strlen($pool);
      mt_srand ((double) microtime() * 1000000);
      $string = '';
      for ($i = 0; $i < $length; $i++) {
          $string .= substr($pool, (mt_rand()%($poollen)), 1);
      }
      return $string;
  }
function ipal_filter_var($text){
		if(strlen($text) == 0){return ;}
		for($i=0;$i<strlen($text);$i++){
			$asc[$i] = ord(substr($text,$i,1));
			if($asc[$i] > 127){
				$ch[$i] = '&#'.$asc[$i];
			}else{$ch[$i] = chr($asc[$i]);}			
		}
		$cleanText = join('',$ch);
		return $cleanText;
}

//Function to create a generic multichoice question if it does not exist
//The question is created in the default category for the course and thename of the question is Generic multichoice (1-8)
//The function requires the ipal_random_string() function and ipal_filter_var() function

function ipal_create_genericq($courseid){
//	return true;
//}
//function temp(){
	global $DB;
	global $USER;
	$contextid = $DB->get_record('context', array('instanceid'=>"$courseid",'contextlevel'=>'50'));
	$contextID = $contextid->id;echo "\n<br />debug126";
	$categories = $DB->get_records_menu('question_categories',array('contextid'=>"$contextID"));
	$categoryid = 0;
	foreach ($categories as $key => $value){
		if(preg_match("/Default\ for/",$value)){$categoryid = $key;}
		//echo "\n<br />id is $key and value is ".$value;
	}
	if(!($categoryid > 0)){
		echo "\n<br />Error obtaining categoryid";
		return false;
	}
	$qCheckid = $DB->count_records('question', array('category'=>"$categoryid",'name'=>'Generic multichoice question (1-8)'));
	if($qCheckid>0){
		echo "\n<br />The Generic multichoice question (1-8) already exists";
		//echo "</body></html>";
		return true;
	}
	      $hostname = 'unknownhost';
      if (!empty($_SERVER['HTTP_HOST'])) {
          $hostname = $_SERVER['HTTP_HOST'];
      } else if (!empty($_ENV['HTTP_HOST'])) {
          $hostname = $_ENV['HTTP_HOST'];
      } else if (!empty($_SERVER['SERVER_NAME'])) {
          $hostname = $_SERVER['SERVER_NAME'];
      } else if (!empty($_ENV['SERVER_NAME'])) {
          $hostname = $_ENV['SERVER_NAME'];
      }
	$date = gmdate("ymdHis");
	$stamp = $hostname .'+'. $date .'+'.ipal_random_string(6);
	$version = $hostname .'+'. $date .'+'.ipal_random_string(6);
	$questionFieldArray=array('category','parent','name','questiontext','questiontextformat','generalfeedback','generalfeedbackformat','defaultgrade','penalty','qtype','length','stamp','version','hidden','timecreated','timemodified','createdby','modifiedby');
	$questionNotNullArray=array('name','questiontext','generalfeedback');
	$answerFieldArray=array('answer','answerformat','fraction','feedback','feedbackformat');
	$answerNotNullArray =array('answer','feedback');
		$questionInsert = new stdClass();
		$date = gmdate("ymdHis");
		$stamp = $hostname .'+'. $date .'+'.ipal_random_string(6);
		$version = $hostname .'+'. $date .'+'.ipal_random_string(6);
		$questionInsert -> category = $categoryid;
		$questionInsert -> parent = 0;
		$questionInsert ->name = 'Generic multichoice question (1-8)';//$title;
		$questionInsert ->questiontext = 'Please select an answer.';//.$text;
		$questionInsert ->questiontextformat = 1;
		$questionInsert ->generalfeedback = ' ';
		$questionInsert ->generalfeedbackformat = 1;
		$questionInsert ->defaultgrade = 1;
		$questionInsert ->penalty = 0;
		$questionInsert ->qtype = 'multichoice';
		$questionInsert ->length = 1;
		$questionInsert->stamp = $stamp;
		$questionInsert->version = $version;
		$questionInsert ->hidden = 0;
		$questionInsert->timecreated = time();
		$questionInsert->timemodified = time();
		$questionInsert->createdby = $USER -> id;
		$questionInsert->modifiedby =$USER -> id;
	$lastinsertid = $DB->insert_record('question', $questionInsert);
	if(!($lastinsertid > 0)){
		echo "\n<br />Error creating Generic multichoice question";
		return false;
	}
	for($n=1;$n<9;$n++){
		$answerInsert = new stdClass();
		$answerInsert->answer = $n;
		$answerInsert->question = $lastinsertid;
		$answerInsert ->format = 1;
		$answerInsert ->fraction = 1;
		$answerInsert ->feedback = ' ';
		$answerInsert ->feedbackformat = 1;
		$lastAnswerinsertid[$n] = $DB->insert_record('question_answers',$answerInsert);
		if(!($lastAnswerinsertid[$n] > 0)){
			echo "\n<br />Error inserting answer $n for Generic multichoice question (1-8)";
			return false;
		}
	}
	$multichoiceFieldArray = array('question','layout','answers','single','shuffleanswers','correctfeedback','correctfeedbackformat','partiallycorrectfeedback','partiallycorrectfeedbackformat','incorrectfeedback','incorrectfeedbackformat','answernumbering');
	$multichoiceNotNullArray = array('correctfeedback','partiallycorrectfeedback','incorrectfeedback');
	$qtypeInsert = new stdClass();
	$qtypeInsert->answers = join(',',$lastAnswerinsertid);
	$qtypeInsert->single = 1;
	$qtypeInsert->correctfeedbackformat = 1;
	$qtypeInsert->partiallycorrectfeedbackformat = 1;
	$qtypeInsert->incorrectfeedbackformat = 1;
	$qtypeInsert->correctfeedback = ' ';
	$qtypeInsert->partiallycorrectfeedback = ' ';
	$qtypeInsert->incorrectfeedback = ' ';
	$qtypeInsert->answernumbering = '123';
	$qtypeInsert->question = $lastinsertid;
	$qtypeTable = 'question_'.$questionInsert->qtype;
	$qtypeInsert->shuffleanswers = '0';
	$qtypeinsertid = $DB->insert_record($qtypeTable, $qtypeInsert);
	return true;	
}

echo "\n<br />debug15";
if(isset($_GET['courseID'])){
	$courseid = $_GET['courseID'];echo "\n<br />courseID = $courseid";
	if(ipal_create_genericq($courseid)){
		echo "\n<br />Success";
	}
	else
	{
		echo '\n<br />Error creating Generic multichoice question (1-8)';
	}
	
}
else
{
	echo "Enter courseID as a get value";
}

?>

</body>
</html>
