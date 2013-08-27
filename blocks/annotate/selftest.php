<?php 
    require_once('../../config.php');
      require_once('../../lib/moodlelib.php');
    require_once('about.php');
    require_once('annotateApi.php');
    error_reporting(E_ALL);
    
    $showform = true;    
    if (optional_param("noform", 0, PARAM_INT)) {
    	$showform = false;
    }
    
    $donesend = false;
    if (optional_param("sent", 0, PARAM_INT)) {
    	$donesend = true;
    }
?>

<html>
<head>
<style type="text/css">
body {font-family : sans-serif}
p { padding : 4px; margin : 4px; font-size : 10pt}
 
.error { color : #000000; background-color : #ffc0c0; font-weight : bold;}  
.success { background-color : #a0ffa0;}
 
h1 { font-size : 14pt}
h2 { padding-top : 20px; font-size : 12pt}
table.noborders { border-collapse : separate }
table.noborders td  {background-color : #ffffff; border-width : 0px; }
table.email td { border-width : 0px; background-color : #ffffff; }
table.anno { border-collapse : separate }
table.anno td {background-color : #f0f0f0; border-width : 0px; min-width : 150px; font-family : 
monospace; font-size : 8pt}

table {border-collapse: collapse;} 
td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;} 
.e {background-color: #ccccff; font-weight: bold; color: #000000;} 
.h {background-color: #9999cc; font-weight: bold; color: #000000;} 
.v {background-color: #cccccc; color: #000000;} 
img {float: right; border: 0px;} 
 



</style>
</head>
<body style="margin : 20px">


<h1>Annotate plugin for Moodle: configuration checks</h1>


<p>Plugin version: <?php print $CFG->block_annotate_version_number . ", " . $CFG->block_annotate_version_date; ?></p>
<p>Any sections with a <span class="error">pink</span> background indicate a problem that needs correcting.</p>
 
<?php if ($donesend) { ?>
	<center><p style="background-color : yellow; font-weight : bold">Thank you. Your settings have been sent.</p></center>
<?php  }?>



<table class="noborders" cellpadding="20">
<tr>
<td width="50%" valign="top">

<h2>Moodle settings</h2>
<table class="anno" align="center" cellspacing="1" cellpadding="4">
	<tr><td>wwwroot</td><td><?php print $CFG->wwwroot;?></td></tr>
	<tr><td>dirroot</td><td><?php print $CFG->dirroot;?></td></tr>
	<tr><td>dataroot</td><td><?php print $CFG->dataroot;?></td></tr>
	<tr><td>directorypermissions</td><td><?php print $CFG->directorypermissions;?></td></tr>
</table>
 



<h2>Plugin settings</h2>

<table class="anno" align="center" cellspacing="1" cellpadding="4">  
   <tr><td>server_url</td><td><?php print $CFG->block_annotate_server_url;?></td></tr>
   <tr><td>apiuser</td><td><?php print $CFG->block_annotate_apiuser;?></td></tr>
   <tr><td>apikey</td><td><?php print $CFG->block_annotate_apikey;?></td></tr>
</table> 

<?php 
$tryserv = true;
if (strlen(trim($CFG->block_annotate_server_url)) < 10) {
	$tryserv = false;
?> <p class="error">The server url must be specified in the plugin settings screen</p> <?php 
}

if (strlen(trim($CFG->block_annotate_apiuser)) < 8 || strlen(trim($CFG->block_annotate_apikey)) < 20)  {
	$tryserv;
?> <p class="error">The API user and API key must be set in order to have user accounts automatically 
created on A.nnotate. You can use the plugin without htis, but users will be prompted to log in each time</p> 
<p>Instructions for finding the API key are on the plugin settings page.</p>
<?php 
}
if ($tryserv) {
	
	?><p class="success">OK: The necessary values have been supplied.</p> <?php 
}
?>


 

</td>
<td width="50%" valign="top">

<h2>Temporary storage</h2>
	
<?php 
	if (tempWritable()) {
?>		
	<p class="success">OK: The block's temporary directory exists and is writable.</p>	
<?php 
	} else {
?>	
	<p class="error">Either the temporary directory doe not exist or it is not writable. Please check
	permissions for <?php print "$CFG->dataroot/temp/annotate";?>.</p>	

<?php 		
	}
?>


<h2>Server connection</h2>

<?php 
if ($tryserv) {
    $phpfn = "checkAPIRequest.php";
    $validfor = 60 * 30; 
    $user = "test@textensor.com";
    $request = signRequest($phpfn, $CFG->block_annotate_apiuser, $CFG->block_annotate_apikey, $user, $validfor);
  
    // the url we will call is the api php with the signing code in the get arguments
    $url = $CFG->block_annotate_server_url."/php/$phpfn?$request";

	$res = file_get_contents($url);
	
	if (substr($res, 0, 2) == "OK") {
?> <p class="success">OK: Server connection and API access confirmed.</p><?php 
		
	} else {
?> <p class="error">Server connection failed: <?php print $res;?></p> <?php 
	}
	
	
} else {

	?> <p class="error">The server connection was not tested. Please correct the above errors first.</p>  <?php 
	
}

if ($showform) {
	 $phpself = basename($_SERVER["PHP_SELF"]);
 	 $pagecontent = file_get_contents("$CFG->wwwroot/blocks/annotate/selftest.php?noform=1");
?>

<h2>Support</h2>

<p>If there are persistent errors with the settings or you need help configuring the plugin or your 
A.nnotate server you can submit the information on this 
page to A.nnotate. Please add a description of the problem and any other debugging output in the box below
and click send.</p>

<form action="http://clients.textensor.com/moodle/pluginReport.php" method="POST">
<input type="hidden" name="page" value="<?php print urlencode($pagecontent); ?>"/>
<center>
<p><table class="email" cellspacing="0" cellpadding="3"><tr><td>Email address:</td>
<td><input name="email" id="email" type="text" size="30"/></p></td>
</tr></table>
<p>
	<textarea name="notes" id="notes" rows="8" style="width : 90%"></textarea>
</p>
<input type="submit" value="Send"/>
</center>
</form>

<?php 
}
?>



</td></tr></table>


<p style = "border-bottom : 1px dotted #a0a0a0; margin : 20px">&nbsp;</p>

<h2>PHP Settings</h2>

<?php 
ob_start() ;
phpinfo(1) ;
$pinfo = ob_get_contents() ;
ob_end_clean() ;

$t0 = strpos($pinfo, "<table");
$t1 = strrpos($pinfo, "</table>");
 
$pinfo = substr($pinfo, $t0, $t1 - $t0 + 8);

print $pinfo;

?>


</body>
</html>



<?php 
  
	
	function tempWritable() {
		global $CFG;
		$temp_dir = "$CFG->dataroot/temp/annotate";
	     if (!file_exists("$CFG->dataroot/temp")) {
    	      mkdir( "$CFG->dataroot/temp", $CFG->directorypermissions );
     	}
     	if (!file_exists( $temp_dir )) {
       	   mkdir( $temp_dir, $CFG->directorypermissions );
     	}
		$ret = false;
		if (is_writable($temp_dir)) {
			$ret = true;
		}
      	return $ret;
     } 
     
?>
