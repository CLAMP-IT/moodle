<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Table Structure Temp</title>
</head>

<body>
<?php
    echo "\n<br />debug10";
	require_once("../../../config.php");echo "\n<br />debug11";
	$table = 'question';echo "\n<br />debug12";
	$dbman = $DB->get_manager();
	if($dbman->field_exists($table,'questiontexts')){echo "\n<br />questiontext in $table exists";}
	else{echo "\n<br />$table does not exist";}
	
?>

<br /><a href="<?php echo $CFG->wwwroot."/question/edit.php?courseid=$courseid"; ?>">Return to Question set page</a>
</body>
</html>
