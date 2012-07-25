<?php
    require_once("../../../config.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>XML Parser k</title>
</head>

<body>
<?php

$record = new stdClass();
$record->createdby         = '3';
$record->qtype = 'multichoice';
$lastinsertid = $DB->insert_record('question', $record);
echo "\n<br />debug335 and lastinsertid = ".$lastinsertid;


?>

<br /><a href="<?php echo $CFG->wwwroot."/question/edit.php?courseid=$courseid"; ?>">Return to Question set page</a>
</body>
</html>
