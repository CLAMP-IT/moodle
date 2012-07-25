<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>XML Parser-&gt;fullname</title>
</head>

<body>
<?php
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
    printf("%s has got %d children.\n", $person['name'], count($elem->children()));
//	printf("%s has got %d children.\n", $person['name'], $person->count());
}
//$xml = new SimpleXMLElement($data);
//foreach($xml as $person){
//	printf("%s has got %d children.\n", $person['name'], $person->count());;
//}
?>
</body>
</html>
