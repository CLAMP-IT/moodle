<?php
$compadreurl = 'http://www.compadre.org/ipal/';
$compadrexmlurl = $compadreurl.$_POST['xmlurl'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $compadrexmlurl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$contents = curl_exec($ch);
curl_close($ch);
//$questionXMLencode = $_POST['questionXMLencode'];
$categoryid = $_POST['categoryid'];
$lenxml = strlen($contents);//questionXMLencode);

$xmlDATA = $contents;//rawurldecode($questionXMLencode);

	//The fields in the question, answer, and multichoice tables in Moodle 2.1 REvise if needed for other versions
	$questionFieldArray=array('category','parent','name','questiontext','questiontextformat','generalfeedback','generalfeedbackformat','defaultgrade','penalty','qtype','length','stamp','version','hidden','timecreated','timemodified','createdby','modifiedby');
	$questionNotNullArray=array('name','questiontext','generalfeedback');
	$answerFieldArray=array('answer','answerformat','fraction','feedback','feedbackformat');
	$answerNotNullArray =array('answer','feedback');
	$multichoiceFieldArray = array('question','layout','answers','single','shuffleanswers','correctfeedback','correctfeedbackformat','partiallycorrectfeedback','partiallycorrectfeedbackformat','incorrectfeedback','incorrectfeedbackformat','answernumbering');
	$multichoiceNotNullArray = array('correctfeedback','partiallycorrectfeedback','incorrectfeedback');
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
  function ipal_random_string($length=15) {
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

	list($Data,$Qs) = MoodleXMLQuiz2Array($xmlDATA);

	//The questions start numbering with #1. Data is the data for each question.
	for ($k=1;$k<=count($Data);$k++){

		if(isset($Data[$k]->name)){
			$questionInsert = new stdClass();
			$date = gmdate("ymdHis");
			$stamp = $hostname .'+'. $date .'+'.ipal_random_string(6);
			$Data[$k]->version = $Data[$k]->hashtext;
//			$version = $hostname .'+'. $date .'+'.random_string1(6);

			foreach($Data[$k] as $qkey => $qvalue){if($debug){echo "\n<br />debug284 qkey $qkey = ".$qvalue;}
				$q = trim($qkey);
				if(in_array($q,$questionFieldArray)){$questionInsert->$q = strval($qvalue);}
		
			}

			$questionInsert->category = $categoryid;
			$questionInsert->parent = 0;
			$questionInsert->createdby = $USER->id;
			$questionInsert->modifiedby =$USER->id;
			$questionInsert->timecreated = time();
			$questionInsert->timemodified = time();
			$questionInsert->stamp = $stamp;

			if(strlen($questionInsert->qtype)>0){
				if($debug){echo "\n<br />Here is the array:\n<br />";}
				foreach($questionNotNullArray as $ky => $vle){
					if(!(strlen($questionInsert->$vle) > 0)){$questionInsert->$vle = "\n<br />";}
				}
				if($debug){print_r($questionInsert);}
				$lastinsertid = $DB->insert_record('question', $questionInsert);
				$questionIdList[$k]=$lastinsertid;
				if($debug){echo "\n<br />debug327 and the id for the insert into the question table is ".$lastinsertid;}
			}
			else
			{echo "\n<br />Error: There must be a qtype (question type).";}

			$qtypeInsert = new stdClass();
			for($a=1;$a<=count($Qs[$k]);$a++){
				$answerInsert = new stdClass();
				foreach($Qs[$k][$a] as $akey =>$avalue){
					$ak=trim($akey);
					if(in_array($ak,$answerFieldArray)){$answerInsert->$ak = $avalue;}
				}
				$answerInsert->question = $lastinsertid;
				foreach($answerNotNullArray as $aky=>$avle){
					if(!(strlen($answerInsert->$avle)>0)){$answerInsert->$avle = "\n<br/>";}
				}
				$answerInsert->fraction=($answerInsert->fraction)/100;//The values from ComPADRE are percentages, not fractions.
				if($debug){echo "\n<br />debug357 and here is the answerInsert array.";
					print_r($answerInsert);
				}
				$lastAnswerinsertid[$a] = $DB->insert_record('question_answers',$answerInsert);
				if($debug){echo "\n<br />debug358 and the id for answer $a for question $k is ".$lastAnswerinsertid[$a];}
				if($questionInsert->qtype == 'truefalse'){
					if($debug){echo "\n<br />debug365 a is $a and answerInsert->fraction is ".$answerInsert->fraction;}
					if($answerInsert->fraction > 0){$qtypeInsert -> trueanswer = $lastAnswerinsertid[$a];}
					else {$qtypeInsert -> falseanswer = $lastAnswerinsertid[$a];}
					if($debug){echo "\n<br />debug367 and qtypeInsert falseanswer and trueanswer are ".$qtypeInsert->falseanswer." and ".$qtypeInsert->trueanswer;}
				}
			}
			if($questionInsert->qtype == 'truefalse'){
				$qtypeInsert ->question = $lastinsertid;
				if($debug){echo "\n<br />debug372 the array for question_".$questionInsert->qtype." is ";
					print_r($qtypeInsert);
				}
			}
			if($questionInsert->qtype == 'multichoice'){
				$qtypeInsert->answers = join(',',$lastAnswerinsertid);
				foreach($multichoiceNotNullArray as $ky => $vle){
					$qtypeInsert->$vle = "\n<br />";
				}
				$qtypeInsert->single = 1;
				$qtypeInsert->correctfeedbackformat = 0;
				$qtypeInsert->partiallycorrectfeedbackformat = 0;
				$qtypeInsert->incorrectfeedbackformat = 0;
				foreach($Data[$k] as $key => $value){
					$ky = trim($key);
					if($ky=='single'){
						$value = 1;
					}
					if($ky=='shuffleanswers'){
						$value = 0;
					}
					if(in_array($ky,$multichoiceFieldArray)){$qtypeInsert->$ky = $value;}
				}
				$qtypeInsert ->question = $lastinsertid;
				if($debug){echo "\n<br />debug386 for the table question_".$questionInsert->qtype." here is the array:";
					print_r($qtypeInsert);
				}
			}
			if(($questionInsert->qtype == 'truefalse') or ($questionInsert->qtype=='multichoice')){
				$qtypeTable = 'question_'.$questionInsert->qtype;
				$qtypeinsertid = $DB->insert_record($qtypeTable, $qtypeInsert);
			}

		}
	}

		$priorQIDs = $ipal->questions;
		$addedQIDs = implode(",",$questionIdList);

		$ipal->questions = $addedQIDs.','.$priorQIDs;

	    $DB->set_field('ipal', 'questions', $ipal->questions, array('id' => $ipal->id));

?>