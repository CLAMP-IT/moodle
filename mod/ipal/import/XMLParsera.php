<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>XML Parser a</title>
</head>

<body>
<?php
    require_once("../../../config.php");
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
	$elem[$level] = new SimpleXMLElement($xml);
	
	//Level 0
	$label[$level] = $elem[$level]->getName();
	if(!($label[$level] == 'quiz')){echo "\n<br />This document is not a valid Moodle XML set of questions.";return;}
	$numChildren[$level] = count($elem[$level] ->children());//This is the number of questions
	$n=0;//This indexes the questions
	foreach($elem[$level] as $elem[$level + 1]){//This is level 1. These should be questions
		$level=1;if($debug){echo "\n<br />debug98 This is starting level $level";}//level 1 
		$label[$level] = $elem[$level]->getName();
		if(!($label[$level] == 'question')){echo "\n<br />This document has an entry that is not a valid Moodle XML question.";return;}
		$n++;//Th eindex for the first question is 1
		$qData[$n] = new stdClass();echo "\n<br />debug102";
		if($label[$level] == 'text'){$label[$level] = $label[$level-1];}
		$qData[$n]->$label[$level]=trim((string) $elem[$level]);
		$numChildren[$level]=count($elem[$level]);
		foreach($elem[$level]->attributes() as $key[$level] => $value[$level]){
			if($key[$level] == 'type'){$key[$level] = 'qtype';}
			if($key[$level] == 'format'){
				$key[$level] = $label[$level-1].'format';
				if($value[$level] == 'html'){$value[$level]=1;}else{$value[$level]=0;}
			}
			echo "\n<br />debug109";
			$qData[$n]->$key[$level] = $value[$level];
		}echo "\n<br />debug110";
		//if($value[$level] == 'text'){$key[$level]  = $key[$level-1];}echo "\n<br />debug111 and key[$level]=".$key[$level];//TExt values take their values from one level up
		
		echo "\n<br />debug113";
		//Starting level 2
		$na=0;//The index for the answers
		if($numChildren[$level] > 0){
		foreach($elem[$level] as $elem[$level + 1]){//This is level 2. These might be questions ro answers
			$level=2;if($debug){echo "\n<br />This is level 2";}//level 2 
			$label[$level] = $elem[$level]->getName();echo "\n<br />debug123 and the label for level 2 is ".$label[$level];
			if($label[$level] == 'text'){$label[$level]  = $label[$level-1];}echo "\n<br />debug132";//TExt labels take their labels from one level up

			if($label[$level] == 'answer'){
				$na++;//THe first answer has na=1
				$aData[$n][$na] = new stdClass();
				$aData[$n][$na]->$label[$level]=trim((string) $elem[$level]);
			}
			else
			{
				$qData[$n]->$label[$level]=trim((string) $elem[$level]);if($debug){echo"\n<br />debug133 and qData[$n][".$label[$level]."] is ".trim((string) $elem[$level]);}
			}
			$numChildren[$level]=count($elem[$level]);
			foreach($elem[$level]->attributes() as $key[$level] => $value[$level]){
				if($key[$level] == 'type'){$key[$level] = 'qtype';}
				if($key[$level] == 'format'){
					$key[$level] = $label[$level-1].'format';
					if($value[$level] == 'html'){$value[$level]=1;}else{$value[$level]=0;}
				}
				if($label[2] == 'answer'){
					$aData[$n][$na]->$label[$level]= $value[$level];
				}
				else
				{
					$qData[$n]->$label[$level] = $value[$level];
				}
				
			}echo "\n<br />debug131";
			echo"\n<br />debug 140";
			//Starting level 3
			if($numChildren[$level] >0){
			foreach($elem[$level] as $elem[$level + 1]){//This is level 3.
				$level=3;if($debug){echo "\n<br />Starting level 3";}//level 3
				$label[$level] = $elem[$level]->getName();
				if($label[$level] == 'text'){$label[$level]  = $label[$level-1];}echo "\n<br />debug157";//TExt labels take their labels from one level up

				if($label[2] == 'answer'){
					$aData[$n][$na]->$label[$level]=trim((string) $elem[$level]);echo "\n<br />debuganswer 160";
				}
				else
				{
					$qData[$n]->$label[$level]=trim((string) $elem[$level]);
				}
				$numChildren[$level]=count($elem[$level]);

			
				foreach($elem[$level]->attributes() as $key[$level] => $value[$level]){
					if($key[$level] == 'type'){$key[$level] = 'qtype';}
					if($key[$level] == 'format'){
						$key[$level] = $label[$level-1].'format';
						if($value[$level] == 'html'){$value[$level]=1;}else{$value[$level]=0;}
					}
					if($label[2] == 'answer'){
						$aData[$n][$na]->$key[$level] = $value[$level];
					}
					else
					{
						$qData[$n]->$key[$level] = $value[$level];
					}
					
				}
				//Starting level 4
				if($numChildren[$level] >0){
				foreach($elem[$level] as $elem[$level + 1]){//This is level 4.
					$level=4;if($debug){echo "\n<br />starting level 4";}//level 4 
					$label[$level] = $elem[$level]->getName();
					if($label[$level] == 'text'){$label[$level]  = $label[$level-1];}echo "\n<br />debug188";//TExt labels take their labels from one level up
					$numChildren[$level]=count($elem[$level]);
					if($debug){echo "\n<br />debug191 the various labels are 1:".$label[1]."->2:".$label[2]."->3:".$label[3]."->4:".$label[4];}

					if($label[2] == 'answer'){
						$aData[$n][$na]->$label[$level]=trim((string) $elem[$level]);echo "\n<br />debuganswer 193";
					}
					else
					{
						$qData[$n]->$label[$level]=trim((string) $elem[$level]);
					}

					foreach($elem[$level]->attributes() as $key[$level] => $value[$level]){
						if($key[$level] == 'type'){$key[$level] = 'qtype';}
						if($key[$level] == 'format'){
							$key[$level] = $label[$level-1].'format';
							if($value[$level] == 'html'){$value[$level]=1;}else{$value[$level]=0;}
						}
						if($label[2] == 'answer'){
							$aData[$n][$na]->$key[$level] = $value[$level];if($debug){echo "\n<br />debug207 and answer $n, $na (".$key[$level].") is ".$value[$level];}
						}
						else
						{
							$qData[$n]->$key[$level] = $value[$level];
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

function MoodleXML2Array($xml){//This function accepts a Moodle XML question set and returns an arrays for the questions and answers 
	$elem = new SimpleXMLElement($xml);
	$numQuestions = count($elem ->children());
	//echo "\n<br />There are $numQuestions";
	$n = 0;
	foreach ($elem as $question) {//Once for each question $question is Level 1
		$na = 0;
		$n++;
		foreach ($question->attributes() as $key => $value){//Gets all the attributes of each question or answer
			$label = $question->getName();
			if($label =='question'){//This label at this level for Moodle xml should be question
				$qData[$n] = new stdClass();
				if($key == 'type'){$key = 'qtype';}//Correcting to correspond to field name
				$qData[$n]->$key=$value;//The key should be type
				echo "\n<br />Question ($label) $n has $key= ".$value;
				foreach($question as $questionData){//$questionData is level 2
					$numChild = count($questionData->children());
					$label2 = $questionData->getName();//This is a level 2 label
					if($label2 == 'answer'){
						$aData[$n][$na] = new stdClass();//One per answer
						$aData[$n][$na]->$label2 = $value;echo "\n<br />debug93 and value for question $n and answer $na with label $label is ".$value;
						
						foreach($questionData->attributes() as $ky => $vlue){
							if($ky=='format'){
								$ky = $lable2.$ky;
								if($vlue =='html'){$vlue = 1;}else{$vlue = 0;}
								$aData[$n][$na]->$ky = $vlue;
							}
							
						}
						if($numChild > 0){
							foreach($questionData as $q2Data){//q2Data is level 3
								$numChild3 = count($q2Data);
								if($numChild3 == 0){
									$value3 = trim((string) $q2Data);
									$label3 = $q2Data->getName();
									if(!($label3 == 'text'))
									{
										$aData[$n][$na]->$label3=$value3;
									}
								}elseif($numDhild3 > 0){
									foreach($q2Data->attributes() as $ky3 =>$value3){
										if($ky3 == 'format'){
											$ky3=$label3.$ky3;
											if($value3=='html'){$value3=1;}else{$value3 = 0;}
										}
										$aData[$n][$na]->$ky3 = $value3;
									}
									foreach($s2Data as $q4Data){
										$aData[$n][$na]->$label3=trim((string) $q4Data);
									}
								}
								

							}							
						}
						$na++;
					}
					else
					{
						//It isn not an answer
					}
				}
			}
			else
			{
				echo "\n<br />Question $n is not in the Moodle xml format";
			}
		}
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
			foreach ($Qs[$k][$j-1] as $key => $value){
				echo ", $key=".$value;
			}
		}
		
	}
}//End of debug print
	for ($k=1;$k<=count($Data);$k++){
		echo "\n<br /><b>Question $k</b> (type=".$Data[$k]->type.")";
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
				echo "\n\t<li value='$value'>".$Qs[$k][$j-1]->answer."</li>";
				$value++;
			}
			echo "</ol>";
		}
	
	}
	
	
}
?>

<br /><a href="<?php echo $CFG->wwwroot."/question/edit.php?courseid=$courseid"; ?>">Return to Question set page</a>
</body>
</html>
