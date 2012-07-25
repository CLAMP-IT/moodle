<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>XML Parser-&gt;fullname</title>
</head>

<body>
<?php
    require_once("../../../config.php");
$courseid = $_GET['courseid'];
//The following script works
//	$course = $DB->get_record('course', array('id'=>"$courseid"));
//	echo "\n<br />Here is the vourse name from the courses table ".$course->fullname;
//	foreach($course as $key => $value){
//		echo "\n<br />$key: ".$value;
//	}
//Use the functions at http://docs.moodle.org/dev/DML_functions
?>
<form method="post" enctype="multipart/form-data">
<br />Please post <input type="file" name="Junkinfile">
<br /><input type="submit">

</form>
<?php
if($_FILES['Junkinfile']){
echo "\n<br />Here is the result\n<br />";
$fp = fopen($_FILES['Junkinfile']['tmp_name'],'r');
$data = fread($fp,80000);
fclose($fp);
//echo $data;
echo "<br />Here is the XML parsed data<br />";
//From http://www.phpfreaks.com/tutorial/handling-xml-data
$qnum = 0;
//simplexml_load_string()
//simplexml_load_file()
$xml = <<<EOF
<people>
 <person name="Person 1">
  <child/>
  <child/>
  <child/>
 </person>
 <person name="Person 2">
  <child/>
  <child/>
  <child/>
  <child/>
  <child/>
 </person>
</people>
EOF;

$elem = new SimpleXMLElement($xml);

foreach ($elem as $person) {
    printf("%s has got %d children.\n", $person['name'], $person->count());
}
//$xml = new SimpleXMLElement($data);
//foreach($xml as $person){
//	printf("%s has got %d children.\n", $person['name'], $person->count());;
//}
function parse_recursive(SimpleXMLElement $element, $level = 0)
{
global $qnum;
global $questiondat;
global $element2;
global $key;
       //Questions start with question 1
		if($element->getname() == 'question'){$qnum ++;echo "\n<br />qnum is now $qnum";}
		$indent     = str_repeat("\t", $level); // determine how much we'll indent
        
        $value      = trim((string) $element);  // get the value and trim any whitespace from the start and end
        $attributes = $element->attributes();   // get all attributes
        $children   = $element->children();     // get all children
	echo "\n<br />The level is $level and the element is *".$element->getname()."* and the value is ".$value."\n<br />";
	if($level==2){
		if($value =='answer'){$key = 'answer';
		
		}
		else {$key = 'question';
			$element2 = $element->getname();
			$questiondat[$qnum][$element2]= $value;

		}  
	} 
	
	if($level == 3){echo "\n<br />debug58 and qnum and element 2 and key are $qnum and $element2 and $key";
		if($key=='question'){$questiondat[$qnum][$element2] .= $value;
		echo "\n<br />debug59 and qnum and element 2 are $qnum and $element2";}
	} 
	    
        echo "{$indent}Parsing '{$element->getName()}'...".PHP_EOL;
        if(count($children) == 0 && !empty($value)) // only show value if there is any and if there aren't any children
        {
                echo "{$indent}Value: {$element}".PHP_EOL;
        }
        
        // only show attributes if there are any
        if(count($attributes) > 0)
        {
                echo $indent.'Has '.count($attributes).' attribute(s):'.PHP_EOL;
                foreach($attributes as $attribute)
                {
                        echo "{$indent}- {$attribute->getName()}: {$attribute}".PHP_EOL;
                }
        }
        
        // only show children if there are any
        if(count($children))
        {
                echo $indent.'Has '.count($children).' child(ren):'.PHP_EOL;
                foreach($children as $child)
                {
                        parse_recursive($child, $level+1,$qnum); // recursion :)
                }
        }
        if(($level == 1) and ($qnum == 2)){
			echo "|n<br />Queestion $qnum and the prior values are ".$questiondat[1]['name'];
			
		}
        echo $indent.PHP_EOL; // just to make it "cleaner"
		return;
}

$parsedData = new SimpleXMLElement($data, null, false);


parse_recursive($parsedData);

	for($n=1;$n<3;$n++){
		echo "\n<br />Question $n";
		foreach ($questiondat[$n] as $ky =>$value){
			echo "\n<br />$ky: ".$value;
		}
	}
}
?>
</body>
</html>
