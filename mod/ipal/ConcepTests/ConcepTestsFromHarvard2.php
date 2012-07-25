<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>ConcepTests from Harvard version 2</title>
</head>

<body>
<form method="post">
Please input the value for the desired ConceptTest
<br /><input type="text" name="conceptestID">
<br /><input type="submit">
</form>
<?php
$conceptestID = $_POST['conceptestID'];
if($conceptestID > 0){
	$URL = 'https://galileo.seas.harvard.edu/conceptest/feedback/?PHPSESSID=h0j95tadnm1u4du3sutpvh8sa3&entityID='.$conceptestID.'&entitytype=4';
	if($fp = fopen($URL,'rb')){
		$data = "";
		while (!feof($fp)) {
		  $data .= fread($fp, 8192);
		}//Necessary because it only reads a packet at a time.
		fclose($fp);
		echo "\n<br />Here is the page obtained from Harvard using this URL:".$URL."\n<br />";
		list($head,$body) = preg_split("/\<B\>\W*1\.\W*\<\/B\>/",$data,2);
		echo $body;
		$body = preg_replace("/\n/",'',$body);
		$images = preg_match("/(\<IMG.*\>)/",$body,$imgMatches);
		echo "\n<br /><hr />The important data is <br />";
		if(preg_match("/(.*)(\<OL\>.*\<\/OL\>)(.*)/",$body,$matches)){//.*\<\/OL\>)
			list($questionText,$rest)=preg_split("/\<\/TR\>/",$matches[1],2); 
			$images = preg_match("/(\<IMG.*\>)/",$matches[1],$imgMatches);
			echo "\n<br />The question text is ".$questionText;
			if($images){
				for($i=1;$i<=count($imgMatches);$i++){
					$URLstem = "https://galileo.seas.harvard.edu/";
					$imgTag = preg_replace("/SRC\=\"\/images/",'SRC="'.$URLstem.'images',$imgMatches[$i]);
					echo "\n<br />Here is image $i";
					echo "\n<br />".$imgTag;
				}
			}
			else
			{echo "\n<br />No images";}
			echo "\n<hr /><br />The options are \n".$matches[2];
			echo "\n<hr /><br />and the rest is \n<table>";$matches[3]; 
		}
		else{
			echo "\n<br />It didn't match.";
		}
	}

}
?>
</body>
</html>
