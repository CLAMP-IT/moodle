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
    global $DB;
    global $USER;
    global $COURSE;
    $contextid = $DB->get_record('context', array('instanceid'=>"$courseid",'contextlevel'=>'50'));
    $contextID = $contextid->id;
    $categories = $DB->get_records_menu('question_categories',array('contextid'=>"$contextID"));
    $categoryid = 0;
    foreach ($categories as $key => $value){
        if(preg_match("/Default\ for/",$value)){
            if(($value == "Default for ".$COURSE->shortname) or ($categoryid == 0)){$categoryid = $key;}
        }
    }
    if(!($categoryid > 0)){
        echo "\n<br />Error obtaining categoryid\n<br />";
        return false;
    }
    $qmultichoicecheckid = $DB->count_records('question', array('category'=>"$categoryid",'name'=>'Generic multichoice question (1-8)'));
    $qessaycheckid = $DB->count_records('question', array('category'=>"$categoryid",'name'=>'Generic essay question'));
    if(($qmultichoicecheckid>0) and ($qessaycheckid > 0)){
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
	$questioninsert = new stdClass();
	$date = gmdate("ymdHis");
	$stamp = $hostname .'+'. $date .'+'.ipal_random_string(6);
	$version = $hostname .'+'. $date .'+'.ipal_random_string(6);
	$questioninsert -> category = $categoryid;
	$questioninsert -> parent = 0;
	$questioninsert ->questiontextformat = 1;
	$questioninsert ->generalfeedback = ' ';
	$questioninsert ->generalfeedbackformat = 1;
	$questioninsert ->defaultgrade = 1;
	$questioninsert ->penalty = 0;
	$questioninsert ->length = 1;
	$questioninsert->stamp = $stamp;
	$questioninsert->version = $version;
	$questioninsert ->hidden = 0;
	$questioninsert->timecreated = time();
	$questioninsert->timemodified = time();
	$questioninsert->createdby = $USER -> id;
	$questioninsert->modifiedby =$USER -> id;
    if ($qmultichoicecheckid == 0){
    	$questioninsert ->name = 'Generic multichoice question (1-8)';//$title;
    	$questioninsert ->questiontext = 'Please select an answer.';//.$text;
    	$questioninsert ->qtype = 'multichoice';
    	$lastinsertid = $DB->insert_record('question', $questioninsert);
        $answerFieldArray=array('answer','answerformat','fraction','feedback','feedbackformat');
        $answerNotNullArray =array('answer','feedback');
        if(!($lastinsertid > 0)){
            echo "\n<br />Error creating Generic multichoice question\n<br />";
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
				echo "\n<br />Error inserting answer $n for Generic multichoice question (1-8)\n<br />";
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
		$qtypeTable = 'question_'.$questioninsert->qtype;
		$qtypeInsert->shuffleanswers = '0';
		$qtypeinsertid = $DB->insert_record($qtypeTable, $qtypeInsert);
	}
	if ($qessaycheckid == 0){
    	$questioninsert ->name = 'Generic essay question';//$title;
    	$questioninsert ->questiontext = 'Please answer the question in the sapce provided.';//.$text;
    	$questioninsert ->qtype = 'essay';
    	$lastinsertid = $DB->insert_record('question', $questioninsert);
		$essayoptions = new stdClass;
		$essayoptions->questionid = $lastinsertid;
		$essayoptions->responsefieldlines = 3;
		$essayoptionsid = $DB->insert_record('qtype_essay_options',$essayoptions);
   }
    return true;    
}
?>