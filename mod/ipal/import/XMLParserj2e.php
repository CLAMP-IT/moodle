<?php
    require_once("../../../config.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>XML Parser 2e</title>
</head>

<body>
<?php
$courseid = $_GET['courseid'];
$debug = $_GET['debug'];
//The following script works
//	$course = $DB->get_record('course', array('id'=>"$courseid"));
//	echo "\n<br />Here is the vourse name from the courses table ".$course->fullname;
//	foreach($course as $key => $value){
//		echo "\n<br />$key: ".$value;
//	}
//Use the functions at http://docs.moodle.org/dev/DML_functions
?>
Page to Import Questions from a Moodle xml question set
<form method="post" enctype="multipart/form-data">
<br />Please select the category for these questions:
<?php
$contextid = $DB->get_record('context', array('instanceid'=>"$courseid",'contextlevel'=>'50'));
	$contextID = $contextid->id;
	echo "\n<br />The context Id for the course is $contextID";
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
<br /><select name="categoryid">
<option value="0">Please choose a category</option>
<?php
foreach ($categories as $key => $value){
	echo "\n<option value='$key'>".$value."</option>";
}

?>
</select>
<br />
<br />Please post <input type="file" name="Junkinfile">
<br /><input type="submit">

</form>
<?php
if($_FILES['Junkinfile']){
	if(!($_POST['categoryid'] > 0)){
		echo "\n<br />You need to select a category.</body></html>";
		exit;
	}
	if($debug){echo "\n<br />Here is the result\n<br />";}
	$fp = fopen($_FILES['Junkinfile']['tmp_name'],'r');
	$data = fread($fp,80000);
	fclose($fp);
	//echo $data;
	echo "<br />Here are the submitted questions<br />";

//Structure of the XML file:
//Level 0 = quiz
//	Level 1 = question attribute type
//		Level 2 = name 
//			Level 3 = text (content)
//		Level 2 = questiontext attribute format
//			Level 3 = text (content)
//		Level 2 = several types of feedback each with attribute format
//			Level 3 = text (content)
//		Level 2 = defaultgrade,penalty,hidden, single, shuffleanswers,answernumbering, each with (content)
//		Level 2 = answer attribute fraction and format
//			Level 3 = text (content)
//			Level 3 = feedback attribute format
//				Level 4 = text (content)
//		Level 2 = answer (repeat)
// There is never a value when there are children. Each value takes the key of that level unless the key is text, and then 
//  the key comes from the level above. The keys for the attributes need to be modified as follows: 
// If the key is format the key needs to be changed to add in the key from that level and if the value is html, the value 
// needs to be changed to 1 otherwise zero. If the attribute is fraction, the value needs to be divided by 100
// If an attribute of a question is type, ite needs to be changed to qtype

function MoodleXMLQuiz2Array($xml){
	global $debug;
	if($debug){echo "\n<br />Starting MoodleXMLQuizArray";}
	$level = 0;
	$elem[0] = new SimpleXMLElement($xml);
	
	//Level 0
	$label[$level] = $elem[$level]->getName();
	if(!($label[$level] == 'quiz')){echo "\n<br />This document is not a valid Moodle XML set of questions.";return;}
	$numChildren[$level] = count($elem[$level] ->children());//This is the number of questions
	$n=0;//This indexes the questions
	foreach($elem[0] as $elem[1]){//This is level 1. These should be questions
		$level=1;if($debug){echo "\n<br />debug98 This is starting level $level";}//level 1 
		$label[$level] = $elem[$level]->getName();
		if(!($label[$level] == 'question')){echo "\n<br />This document has an entry that is not a valid Moodle XML question.";return;}
		$n++;//Th eindex for the first question is 1
		$qData[$n] = new stdClass();
		if($label[$level] == 'text'){$label[$level] = $label[$level-1];}
		$qData[$n]->$label[$level]=trim((string) $elem[$level]);if($debug){echo "\n<br />at 104 qData[$n][".$label[$level]."] = ".$qData[$n]->$label[$level];}
		$numChildren[$level]=count($elem[$level]);
		foreach($elem[$level]->attributes() as $key => $value){
			if($key == 'type'){$key = 'qtype';}
			if($key == 'format'){
				$key = $label[$level].'format';
				if($value == 'html'){$value=1;}else{$value=0;}
			}
			
			$qData[$n]->$key = $value;if($debug){echo "\n<br />at 113 attribute qData[$n][".$key."] = ".$qData[$n]->$key;}
		}
		
		//Starting level 2
		$na=0;//The index for the answers
		if($numChildren[$level] > 0){
		foreach($elem[1] as $elem[2]){//This is level 2. These might be questions ro answers
			$level=2;if($debug){echo "\n<br />This is level 2";}//level 2 
			$label[$level] = $elem[$level]->getName();
			if($label[$level] == 'text'){$label[$level]  = $label[$level-1];}//TExt labels take their labels from one level up

			if($label[$level] == 'answer'){
				$na++;//THe first answer has na=1
				$aData[$n][$na] = new stdClass();
				$aData[$n][$na]->$label[$level]=trim((string) $elem[$level]);
			}
			else
			{
				$qData[$n]->$label[$level]=trim((string) $elem[$level]);if($debug){echo "\n<br />at 133 qData[$n][".$label[$level]."] = ".$qData[$n]->$label[$level];}
			}
			$numChildren[$level]=count($elem[$level]);
			foreach($elem[$level]->attributes() as $key => $value){
				if($key == 'type'){$key = 'qtype';}
				if($key == 'format'){
					$key = $label[$level].'format';
					if($value == 'html'){$value=1;}else{$value=0;}
				}
				if($label[2] == 'answer'){
					$aData[$n][$na]->$key= $value;
				}
				else
				{
					$qData[$n]->$key = $value;if($debug){echo "\n<br />at 147 qData[$n][".$key."] = ".$qData[$n]->$key;}
				}
				
			}
			//Starting level 3
			if($numChildren[$level] >0){
			foreach($elem[2] as $elem[3]){//This is level 3.
				$level=3;if($debug){echo "\n<br />Starting level 3";}//level 3
				$label[$level] = $elem[$level]->getName();
				if($label[$level] == 'text'){$label[$level]  = $label[$level-1];}//TExt labels take their labels from one level up

				if($label[2] == 'answer'){
					$aData[$n][$na]->$label[$level]=trim((string) $elem[$level]);
				}
				else
				{
					$qData[$n]->$label[$level]=trim((string) $elem[$level]);if($debug){echo "\n<br />at 164 qData[$n][".$label[$level]."] = ".$qData[$n]->$label[$level];}
				}
				$numChildren[$level]=count($elem[$level]);

			
				foreach($elem[$level]->attributes() as $key => $value){
					if($key == 'type'){$key = 'qtype';}
					if($key == 'format'){
						$key = $label[$level].'format';
						if($value == 'html'){$value=1;}else{$value=0;}
					}
					if($label[2] == 'answer'){
						$aData[$n][$na]->$key = $value;
					}
					else
					{
						$qData[$n]->$key = $value;if($debug){echo "\n<br />at 180 attribute qData[$n][".$key."] = ".$qData[$n]->$key;}
					}
					
				}
				//Starting level 4
				if($numChildren[$level] >0){
				foreach($elem[3] as $elem[4]){//This is level 4.
					$level=4;if($debug){echo "\n<br />starting level 4";}//level 4 
					$label[$level] = $elem[$level]->getName();
					if($label[$level] == 'text'){$label[$level]  = $label[$level-1];}//TExt labels take their labels from one level up
					$numChildren[$level]=count($elem[$level]);
					if($debug){echo "\n<br />debug191 the various labels are 1:".$label[1]."->2:".$label[2]."->3:".$label[3]."->4:".$label[4];}

					if($label[2] == 'answer'){
						$aData[$n][$na]->$label[$level]=trim((string) $elem[$level]);
					}
					else
					{
						$qData[$n]->$label[$level]=trim((string) $elem[$level]);if($debug){echo "\n<br />at 198 qData[$n][".$label[$level]."] = ".$qData[$n]->$label[$level];}
					}

					foreach($elem[$level]->attributes() as $key => $value){
						if($key == 'type'){$key = 'qtype';}
						if($key == 'format'){
							$key = $label[$level].'format';
							if($value == 'html'){$value=1;}else{$value=0;}
						}
						if($label[2] == 'answer'){
							$aData[$n][$na]->$key = $value;if($debug){echo "\n<br />debug207 and answer $n, $na (".$key.") is ".$value;}
						}
						else
						{
							$qData[$n]->$key = $value;if($debug){echo "\n<br />at 212 attribute qData[$n][".$key."] = ".$qData[$n]->$key;}
						}
						
					}
				}
				}//IF number of children for level 4
				
				//ending level 4			
			}
			}//End of if numChildres for level 3
		
			
			//Ending level 3
		}
		}//Ending if numbCHildren to start level 2
		
		//Ending level 2
	}
	$return = array($qData,$aData);
	return $return;
	
}


	list($Data,$Qs) = MoodleXMLQuiz2Array($data);
if($debug){
	echo "\n<br /><br />Printing out the array";
	echo "\n<br />the count of Data is ".count($Data);
	for ($k=1;$k<3;$k++){
		echo "\n<br />Question $k \n<br />";
		foreach($Data[$k] as $key => $value){
			echo ", $key=".$value;
		}
		echo "\n<br /> and the answers for question $k are \n<br />";
		$numAnswers = count($Qs[$k]);
		for ($j=1;$j<=$numAnswers;$j++){
			echo "\n<br />  $j of $numAnswers";
			foreach ($Qs[$k][$j] as $key => $value){
				echo ", $key=".$value;
			}
		}
		
	}
}//End of debug print
	for ($k=1;$k<=count($Data);$k++){
		echo "\n<br /><b>Question $k</b> (qtype=".$Data[$k]->qtype.")";
		echo "\n<br />Title: <b>".$Data[$k]->name."</b>";
		echo "\n<br />Text: ".$Data[$k]->questiontext;
		$numAnswers = count($Qs[$k]);
		if($numAnswers >0){
			echo "\n<br/>and the possible answers are:";
			echo "\n<ol>";
			if($Data[$k]->answernumbering == 'abc'){
				$value = 'a';
			}
			elseif ($Data[$k]->answernumbering == 'ABCD'){
				$value = 'A';
			}
			else
			{
				$value = 1;
			}
			for ($j=1;$j<=$numAnswers;$j++){
				echo "\n\t<li value='$value'>".$Qs[$k][$j]->answer."</li>";
				$value++;
			}
			echo "</ol>";
		}
	
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
  function random_string1 ($length=15) {
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
	$stamp = $hostname .'+'. $date .'+'.random_string1(6);
	$version = $hostname .'+'. $date .'+'.random_string1(6);
	 
	
	

	$questionFieldArray=array('category','parent','name','questiontext','questiontextformat','generalfeedback','generalfeedbackformat','defaultgrade','penalty','qtype','length','stamp','version','hidden','timecreated','timemodified','createdby','modifiedby');
	$questionNotNullArray=array('name','questiontext','generalfeedback');
	$questionInsert = new stdClass();
	for($k=1;$k<3;$k++){
		foreach($Data[$k] as $qkey => $qvalue){echo "\n<br />debug284 qkey $qkey = ".$qvalue;
			$q = trim($qkey);
			if(in_array($q,$questionFieldArray)){$questionInsert->$q = $qvalue;}
		
		}
		$questionInsert->category = $_POST['categoryid'];
		$questionInsert->parent = 0;
		$questionInsert->createdby = 3;//Generalize this
		$questionInsert->modifiedby =3;
		$questionInsert->timecreated = time();
		$questionInsert->timemodified = time();
		$questionInsert->stamp = $stamp;
		$questionInsert->version = $version;
		$questionInsert->qtype = 'multichoice';
		if(strlen($questionInsert->qtype)>0){
			echo "\n<br />Here is the array:\n<br />";
			foreach($questionNotNullArray as $ky => $vle){
				if(!(strlen($questionInsert->$vle) > 0)){$questionInsert->$vle = "\n<br />";}
			}
			print_r($questionInsert);
			$lastinsertid = $DB->insert_record('question', $questionInsert);
			echo "\n<br />debug327 and the id for the insert into the question table is ".$lastinsertid;
		}
		else
		{echo "\n<br />Error: There must be a qtype (question type).";}
	}

}
?>

<br /><a href="<?php echo $CFG->wwwroot."/question/edit.php?courseid=$courseid"; ?>">Return to Question set page</a>
</body>
</html>
