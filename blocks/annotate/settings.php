<?php 
 	require_once("about.php");
    global $CFG;
// NB block language settings broken in 1.9.
// can update to use get_string and annotate/lang/en_utf8 etc for 2.0
$intro = "Plugin version<b> $CFG->block_annotate_version_number</b>, $CFG->block_annotate_version_date: ";

$intro = $intro . '<a href="'.$CFG->wwwroot . '/blocks/annotate/selftest.php">Run configuration check.</a> ' .
' If you make any changes below, save the changes and reload the page before running the test.';
   
$settings->add(new admin_setting_heading('block_annotate_heading_server', 'A.nnotate server settings' , $intro));

$srvmsg = 'Set this to your local A.nnotate server. '. 
'For testing, if you have a default A.nnotate installation on the same server as Moodle try "http://localhost/annotate".'. 
'If both servers are on the same local subnetwork you can use their local ip addresses here to, as long as each is '.
'configured to know its own address.';
 
$settings->add(new admin_setting_configtext('block_annotate_server_url', 'Server URL', $srvmsg, 'http://localhost/annotate'));

$aumsg = 'The API user and API key are optional. They allow accounts to be automatically created on the A.nnotate server when '.
		' a Moodle user follows an A.nnotate link. The apiuser field should contain the email id of an '.
		'A.nnotate server administrator as set in the A.nnotate configuration file. The API key below should correspond '. 
		'to this user.'; 
$settings->add(new admin_setting_configtext('block_annotate_apiuser', 'API user email address', $aumsg, ''));

$akmsg = 'The API key can be found at the bottom of the account page when logged in to A.nnotate as the administrator';
$settings->add(new admin_setting_configtext('block_annotate_apikey', 'API key', $akmsg, ''));

/*

// this to go in per-instance configuration

$copyinfo = 'Deduplicate';
$copymsg = 'Minimize server storage and processing by sharing cached documents and page images. If this is not set, then each student '.
' has a completely independent copy. This requires the API user email address to be set above. If "Shared comments" above ' . 
' is set, then this setting has no effect: there will only be one copy on the server in any case.';
$settings->add(new admin_setting_configcheckbox('block_annotate_share', $copyinfo, $copymsg, 1));


$settings->add(new admin_setting_heading('block_annotate_heading_types', 'File types to add links to for viewing in A.nnotate' , ''));


$formats = array(
'pdf'=>'PDF documents', 
'doc'=>'Word documents',
'xls'=>'Excel spreadsheets',
'ppt'=>'PowerPoint presentations',
'jpg'=> 'JPEG images');

foreach ($formats as $fmt => $full) {
   $settings->add(new admin_setting_configcheckbox('block_annotate_'.$fmt, $full, '', 1));
}
*/
?>
