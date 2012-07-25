<?php
    require_once("../../../config.php");echo "\n<br />debug2 in importF";
    require_once("editlib.php");echo "\n<br />debug3 in importF";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="http://three.blueparadox.org/theme/standard/styles.php" />
<link rel="stylesheet" type="text/css" href="http://three.blueparadox.org/theme/standardwhite/styles.php" />

<!--[if IE 7]>
    <link rel="stylesheet" type="text/css" href="http://three.blueparadox.org/theme/standard/styles_ie7.css" />
<![endif]-->
<!--[if IE 6]>
    <link rel="stylesheet" type="text/css" href="http://three.blueparadox.org/theme/standard/styles_ie6.css" />
<![endif]-->


    <meta name="keywords" content="moodle, bjTest1: Import questions from file " />
    <title>bjTest1: Import questions from file</title>

    <link rel="shortcut icon" href="http://three.blueparadox.org/theme/standardwhite/favicon.ico" />
    <!--<style type="text/css">/*<![CDATA[*/ body{behavior:url(http://three.blueparadox.org/lib/csshover.htc);} /*]]>*/</style>-->

</head>

<body  class="question course-2 dir-ltr lang-en_utf8" id="question-import">

<div id="page">

    <div id="header" class=" clearfix">        <h1 class="headermain">Junkin Test 1 </h1>
        <div class="headermenu"><div class="logininfo">You are logged in as <a  href="http://three.blueparadox.org/user/view.php?id=3&amp;course=2">Bill Junkin</a>  (<a  href="http://three.blueparadox.org/login/logout.php?sesskey=wWUY5hfaJo">Logout</a>)</div></div>

    </div>    <div class="navbar clearfix">
        <div class="breadcrumb"><h2 class="accesshide " >You are here</h2> <ul>
<li class="first"><a  onclick="this.target='_top'" href="http://three.blueparadox.org/">iPal Development 1.9.11</a></li><li> <span class="accesshide " >/&nbsp;</span><span class="arrow sep">&#x25BA;</span> <a  onclick="this.target='_top'" href="http://three.blueparadox.org/course/view.php?id=2">bjTest1</a></li><li> <span class="accesshide " >/&nbsp;</span><span class="arrow sep">&#x25BA;</span> Import questions from file</li></ul></div>

        <div class="navbutton">&nbsp;</div>
    </div>
    <!-- END OF HEADER -->

<h2 class="main help">Import questions from file<span class="helplink"><a title="Help with Import questions from file (new window)" href="http://three.blueparadox.org/help.php?module=quiz&amp;file=import.html&amp;forcelang="  onclick="this.target='popup'; return openpopup('/help.php?module=quiz&amp;file=import.html&amp;forcelang=', 'popup', 'menubar=0,location=0,scrollbars,resizable,width=500,height=400', 0);"><img class="iconhelp" alt="Help with Import questions from file (new window)" src="http://three.blueparadox.org/pix/help.gif" /></a></span></h2>

<form action="./import.php" method="post" accept-charset="utf-8" id="mform1" class="mform" enctype="multipart/form-data">

	<div style="display: none;"><input type="hidden" name="courseid" value="2" />
<input name="MAX_FILE_SIZE" type="hidden" value="8388608" />
<input name="sesskey" type="hidden" value="<?php echo sesskey(); ?>" />
<input name="_qf__question_import_form" type="hidden" value="1" />
<input name="format" type="hidden" value="xml" />
<input name="category" type="hidden" value="8,15" />
<input name="matchgrades" type="hidden" value="nearest" />
<input name="stoponerror" type="hidden" value="1" />

</div>
<br />Debug The category is 8,15 for testing in Moodle 2.0.2
<br />debug the matchgrades value has the value of nearest and stoponerror has the value of 1
<br />debug this version is going to /ipal/import/import.php
<br />and sesskey is <?php echo sesskey(); ?>
	<fieldset class="clearfix"  id="importfileupload">
		<legend class="ftoggler">Import from file upload...</legend>
<div class="fcontainer clearfix">
		
		<div class="fitem">
		<input name="newfile" type="file" id="id_newfile" />
		</div>
		<input name="submitbutton" value="Upload this file" type="submit" id="id_submitbutton" />
		</div></fieldset>

</form>
</body>
</html>
