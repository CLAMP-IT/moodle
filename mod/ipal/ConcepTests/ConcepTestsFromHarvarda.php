<?php
    require_once("../../../config.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>ConcepTests from Harvard version 9</title>
</head>

<body>
<form method="post">
<?php 
$conceptestID = $_POST['conceptestID'];
if(isset($_GET['courseID'])){$courseID = $_GET['courseID'];}
if(isset($_POST['courseID'])){$courseID = $_POST['courseID'];}
///Block to insert question into database tables
if(($_POST['postQuestion'] == 'certainly_now') and ($conceptestID > 0)){
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
		$questionInsert -> category = $_POST['categoryid'];
		$questionInsert -> parent = 0;
		if(isset($_POST['title']) and $_POST['title'] <> ''){$title = $_POST['title'];} else {$title = $conceptestID;}
		$questionInsert ->name = $title;
		$questionInsert ->questiontext = addslashes($_POST['questionText']);
		$text1 = $_POST['questionText'];
		for($i=0;$i<strlen($text1);$i++){
			$asct[$i] = ord(substr($text1,$i,1));
			if($asct[$i] > 127){$asct[$i] = 32;}
			$cht[$i] = chr($asct[$i]);			
		}
		$text = join('',$cht);
		$questionInsert ->questiontext = $text;
		$questionInsert ->questiontextformat = 0;
		$feedback1 = $_POST['feedback'];
		for($i=0;$i<strlen($feedback1);$i++){
			$asc[$i] = ord(substr($feedback1,$i,1));
			if($asc[$i] > 127){$asc[$i] = 32;}
			$ch[$i] = chr($asc[$i]);			
		}
		$feedback = join('',$ch);
		$questionInsert ->generalfeedback = $feedback;//"<br />".substr($_POST['feedback'],8,1);
		//echo "\n<br />The bad character is ".rawurlencode(substr($_POST['feedback'],8,1))." and its ascii value is ".ord(substr($_POST['feedback'],8,1))." and printed is looks like *".substr($_POST['feedback'],8,1)."*";
		//$questionInsert ->generalfeedback = 'feedback';
		$questionInsert ->generalfeedbackformat = 0;
		$questionInsert ->defaultgrade = 1;
		$questionInsert ->penalty = 0;
		$questionInsert ->qtype = 'multichoice';
		$questionInsert ->length = 1;
		$questionInsert->stamp = $stamp;
		$questionInsert->version = $version;
		$questionInsert ->hidden = 0;
		$questionInsert->timecreated = time();
		$questionInsert->timemodified = time();
		$questionInsert->createdby = 3;//Generalize this
		$questionInsert->modifiedby =3;
	echo "\n<br />debug71 The questionInsert array";
	foreach($questionInsert as $key =>$value){
		echo "\n<br />debug 75 $key: ".$value;
		if(in_array($key,$questionFieldArray)){echo " and is in array.";} else {echo " key not in array";exit;}
	}
	echo "\n<br />";
	print_r($questionInsert);
	$lastinsertid = $DB->insert_record('question', $questionInsert);
	echo "\n<br />debug82 (through with insert question and lastinsertid is $lastinsertid";
	$correctAnswer = $_POST['correctAnswer'];
	foreach($_POST['answer'] as $akey => $avalue){
		if($akey == $correctAnswer){$ratio = 1;}else{$ratio = 0;}
		echo "\n<br />$akey: ".$avalue." and ratio is $ratio";
		$answerInsert = new stdClass();
		$answerInsert->answer = $avalue;
		$answerInsert->question = $lastinsertid;
		$answerInsert ->format = 0;
		$answerInsert ->ratio = $ratio;
		$answerInsert ->feedback = ' ';
		$answerInsert ->feedbackformat = 0;
		$lastAnswerinsertid[$key] = $DB->insert_record('question_answers',$answerInsert);
	}
	$multichoiceFieldArray = array('question','layout','answers','single','shuffleanswers','correctfeedback','correctfeedbackformat','partiallycorrectfeedback','partiallycorrectfeedbackformat','incorrectfeedback','incorrectfeedbackformat','answernumbering');
	$multichoiceNotNullArray = array('correctfeedback','partiallycorrectfeedback','incorrectfeedback');
	$qtypeInsert = new stdClass();
	$qtypeInsert->answers = join(',',$lastAnswerinsertid);
	$qtypeInsert->single = 1;
	$qtypeInsert->correctfeedbackformat = 0;
	$qtypeInsert->partiallycorrectfeedbackformat = 0;
	$qtypeInsert->incorrectfeedbackformat = 0;
	$qtypeInsert->correctfeedback = ' ';
	$qtypeInsert->partiallycorrectfeedback = ' ';
	$qtypeInsert->incorrectfeedback = ' ';
	$qtypeInsert->answernumbering = '123';
	$qtypeInsert->question = $lastinsertid;
	$qtypeTable = 'question_'.$questionInsert->qtype;
	$qtypeinsertid = $DB->insert_record($qtypeTable, $qtypeInsert);

	echo "\n<br />Question posted";
	
}
//End of blodk to insert questions
if($_POST['submit'] == 'Next_CT'){$conceptestID++;}
?>

Please input the value for the desired ConceptTest
<br /><input type="text" name="conceptestID" value='<?php echo $conceptestID; ?>'>
<br />To use this program you must have successfully logged in at the <a href="http://galileo.seas.harvard.edu/ ">ILT-BQ site</a>
<br />Please enter the value for PHPSESSID here<input type="text" name="PHPSESSID" value="
<?php if(isset($_POST['PHPSESSID'])){echo $_POST['PHPSESSID'];} ?>
"> CourseId <input type="text" name="courseID" value="<?php echo $courseID;?>">  

<br /><input type="submit"> Click here for the next ConcepTest <input type="submit" name="conceptestID" value="<?php 
$newConcepTestID = $conceptestID +1;
echo $newConcepTestID; ?>">
</form>
<?php
$questionText = "";
$MyAnswer[0] = "";
$feedback = "";
$PHPSESSID = $_POST['PHPSESSID'];//cljnq9lm49c2e89va46d9qvih3
if($conceptestID > 0){
	$URL = 'https://galileo.seas.harvard.edu/conceptest/feedback/?PHPSESSID='.$PHPSESSID.'&entityID='.$conceptestID.'&entitytype=4';
	if($fp = fopen($URL,'rb')){
		$data = "";
		while (!feof($fp)) {
		  $data .= fread($fp, 8192);
		}//Necessary because it only reads a packet at a time.
		fclose($fp);
		//echo "\n<br />Here is the page obtained from Harvard using this URL:".$URL."\n<br />";
		list($head,$body) = preg_split("/\<B\>\W*1\.\W*\<\/B\>/",$data,2);
		//echo $body;
		$body = preg_replace("/\n/",'',$body);
		$images = preg_match("/(\<IMG.*\>)/",$body,$imgMatches);
		echo "\n<br /><hr />";//The important data is <br />";
		if(preg_match("/(.*)\<OL\>(.*)\<\/OL\>(.*)/",$body,$matches)){//.*\<\/OL\>)
			list($questionText,$rest)=preg_split("/\<\/TR\>/",$matches[1],2); 
			$images = preg_match("/\<IMG.+?SRC\=\"(.+?)\"/",$matches[1],$imgMatches);
			echo "\n<br />The question text is ".$questionText;
			if($images){
				for($i=1;$i<count($imgMatches);$i++){
					$URLstem = "https://galileo.seas.harvard.edu";
					$URLabs = $URLstem.$imgMatches[$i];
					if($fp2 = fopen($URLabs,'rb')){
						$data2 = "";
						while (!feof($fp2)) {
		  					$data2 .= fread($fp2, 8192);
						}//Necessary because it only reads a packet at a time.
						fclose($fp2);
						$URLrel = "./temp.jpg";
						$fp3=fopen ($URLrel,'wb');
						fwrite($fp3,$data2);
						fclose($fp3);
					}//Image obtained 
					//$imgTag = "<IMG src=\"".$URLstem.$imgMatches[$i];
					$pngFile = imagepng(imagecreatefromjpeg($URLrel),"tmpFile.png");
					//echo "\n<br />The value of pngFile is ".$pngFile;
					$fpPng = fopen('tmpFile.png','r');
					$dataPng = fread($fpPng,100000);
					$base64dataPng = base64_encode($dataPng);

					//echo "\n<br />Here is image $i in png form from <a href=\"$URLabs\">$URLabs</a>";
					echo "\n<img src=\"data:image/png;base64,$base64dataPng\">";
					$questionText .= "\n<img src=\"data:image/png;base64,$base64dataPng\">";
					//echo "\n<br />and the jpg image is <br /><IMG src=\"".$URLrel."\">";
				}
			}
			else
			{echo "";}
			//$options = preg_match("/LI\>(.*?)\</",$matches[2],$optionMatches);//This will cause trouble if there are < signs in the otpions
			echo "\n<hr />The options are \n";//.$matches[2];
			$optionMatches = preg_split("/\<LI\>/",$matches[2]);
			for($o=1;$o<count($optionMatches);$o++){
				echo "\n<br />$o : ".trim($optionMatches[$o]);
				$MyAnswer[$o] = trim($optionMatches[$o]);
			}
			echo "\n<hr /><br />";//and the rest is ".$matches[3];
			$answer = preg_match("/\<B\>.*?\<\/B\>.*?\<B\>(.*?)\<\/B\>(.*)/",$matches[3],$answerMatches);
			if($answer){
				echo "\nThe correct answer is ".trim($answerMatches[1]);
				list($reason,$rest)=preg_split("/\<\/TR/",$answerMatches[2],2);
				echo "\n<br />And the complete explanation is \n<br />".trim($reason);
				$feedback = trim($reason);
			}
			else
			{echo "\n<br />No answer for this problem";} 
		}
		else{
			echo "\n<br />It didn't match.";
		}
	echo "<hr /><hr />\n<form method='post'>";
	echo "Please select the category for these questions:";
	$courseid = $courseID;
	$contextid = $DB->get_record('context', array('instanceid'=>"$courseid",'contextlevel'=>'50'));
	$contextID = $contextid->id;
	//echo "\n<br />The context Id for the course is $contextID";
	$categories = $DB->get_records_menu('question_categories',array('contextid'=>"$contextID"));
	$cats = $DB->get_records('question_categories',array('contextid'=>"$contextID"));
	if($debug){
		foreach($categories as $key =>$value){echo "\n<br />category id =$key\n<br />";
			foreach($cats[$key] as $ky => $vlue){
				echo ", $ky=".$vlue;
			}
		}
	}
	?>
	<select name="categoryid">
	<option value="0">Please choose a category</option>
	<?php
	foreach ($categories as $key => $value){
		echo "\n<option value='$key'>".$value."</option>";
	}
	echo "</select>";
	echo "<hr /><hr />\n<br /><form method='post'>";
	echo "<input type='submit' value='submit_and_save'> OR Submit and Go To Next ConcepTest <input type='submit' name='submit' value='Next_CT'"; 
	echo "\n<br />Be sure the correct answer is checked below.";
	echo "\n<input type='hidden' name='PHPSESSID' value='$PHPSESSID'>";
	echo "\n<input type='hidden' name='conceptestID' value='$conceptestID'>";
	echo "\n<br /><input type='hidden' name='postQuestion' value='certainly_now'>";
	echo "\n<textarea cols = '120' rows='40' name='questionText'>$questionText</textarea>";
	for($a=1;$a<count($MyAnswer);$a++){
		if($a == trim($answerMatches[1])){$checked = 'checked';} else {$checked = '';}
		echo "\n<br /><input type='hidden' name='answer[$a]' value='".$MyAnswer[$a]."'><input type='radio' name='correctAnswer' value='$a' $checked> ".$MyAnswer[$a];
	}
	echo "\n<br /><input type='hidden' name='feedback' value='".addslashes($feedback)."'>Feedback: ".$feedback;
	echo "</form>";
	}

}
?>
</body>
</html>
