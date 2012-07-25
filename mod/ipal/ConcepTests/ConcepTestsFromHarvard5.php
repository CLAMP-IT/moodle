<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>ConcepTests from Harvard version 4</title>
</head>

<body>
<form method="post">
Please input the value for the desired ConceptTest
<br /><input type="text" name="conceptestID">
<br />To use this program you must have successfully logged in at the <a href="http://galileo.seas.harvard.edu/ ">ILT-BQ site</a>
<br />Please enter the value for PHPSESSID here<input type="text" name="PHPSESSID" value="
<?php if(isset($_POST['PHPSESSID'])){echo $_POST['PHPSESSID'];} ?>
">  

<br /><input type="submit">
</form>
<?php
$conceptestID = $_POST['conceptestID'];
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

					echo "\n<br />Here is image $i in png form from <a href=\"$URLabs\">$URLabs</a>";
					echo "\n<br /><img src=\"data:image/png;base64,$base64dataPng\">";
					//echo "\n<br />and the jpg image is <br /><IMG src=\"".$URLrel."\">";
				}
			}
			else
			{echo "\n<br />No images";}
			//$options = preg_match("/LI\>(.*?)\</",$matches[2],$optionMatches);//This will cause trouble if there are < signs in the otpions
			echo "\n<hr /><br />The options are \n";//.$matches[2];
			$optionMatches = preg_split("/\<LI\>/",$matches[2]);
			for($o=1;$o<count($optionMatches);$o++){
				echo "\n<br />$o : ".trim($optionMatches[$o]);
			}
			echo "\n<hr /><br />";//and the rest is ".$matches[3];
			$answer = preg_match("/\<B\>.*?\<\/B\>.*?\<B\>(.*?)\<\/B\>(.*)/",$matches[3],$answerMatches);
			if($answer){
				echo "\n<br />The correct answer is ".trim($answerMatches[1]);
				list($reason,$rest)=preg_split("/\<\//",$answerMatches[2],2);
				echo "\n<br />And the explanation is \n<br />".trim($reason);
			}
			else
			{echo "\n<br />No answer for this problem";} 
		}
		else{
			echo "\n<br />It didn't match.";
		}
	}

}
?>
</body>
</html>
