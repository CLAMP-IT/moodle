<?php
require_once("../../config.php");



$recorder = optional_param('recorder', "", PARAM_TEXT);
$filename = optional_param('filename', "", PARAM_TEXT);

 
//if receiving a file of raw data, write it to temp, ie from poodll snapshot
if(isset($GLOBALS["HTTP_RAW_POST_DATA"])) {
	//make sure we are logged in
	require_login();
	//make a filename randomly
	$filename = date("Y-m-d_H_i_s", time())."_".rand(100000,900000).".jpg";
	
	//open a stream on the posted data, 
	//this is better than $GLOBALS["HTTP_RAW_POST_DATA"], php.ini settings dont affect
	$input = fopen("php://input", "r");
	file_put_contents($CFG->dataroot . '/temp/download/' . $filename ,$input);
	
	//we should really check the filesize here but I don't know how
	//HERE: check file size
	
	
	//tell our widget what the filename we made up is 
	echo $filename; 

//if receiveing a file with a name, eg from mp3recorder
}elseif(isset($_FILES["newfile"])){
	
	//make sure the user is logged in
	require_login();
	
	// make sure the file is not too big
	$user_context = context_user::instance($USER->id);
	$maxbytes = get_user_max_upload_file_size($user_context);
	if (($maxbytes!==-1) && (filesize($_FILES[$newfile]['tmp_name']) > $maxbytes)) {
            throw new file_exception('maxbytes');
    }

	//make sure the filename is clean, and then make the savepath
	$filename = clean_param($_FILES["newfile"]['name'], PARAM_FILE);
	 if (preg_match('/\.([a-z0-9]+)$/i', $filename, $match)) {
                if (isset($match[1])) {
                    $ext = $match[1];
                }
    }
    $ext = !empty($ext) ? $ext : '';
    if($ext != 'mp3'){
    	throw new moodle_exception('invalidfiletype', 'repository', '', get_mimetype_description(array('filename' => $filename)));
    }
	
	//make savepath
	$newfilepath = $CFG->dataroot . '/temp/download/' . $filename;
	
	//write out the file
	move_uploaded_file($_FILES["newfile"]["tmp_name"],$newfilepath );
	
	//this for PoodLL recorder
	echo $filename;
	return;
	
	//this for paul nichols recorder
	//this next is so hacky, I feel like having a shower .. if only it wasn't open source ..
	//basically we cant control the recorder behaviour well enough to behave like the other PoodLL widgets
	//unlike the ajax like uploads of poodll recorders, the mp3recorder forwards page on to the POST page, 
	//so we need to return something or the screen will be blank
	//we just return a page containing javascript that pushes the next button in the parent frame
		?>
		<html>
			<head>
				<script type="text/javascript">
					function load()
					{
						var ffield = parent.document.getElementById('filename');
						ffield.value = '<?php echo $filename; ?>';
						parent.document.getElementsByClassName('fp-login-submit')[0].click();
					}
				</script>
			</head>

			<body onload="load()" />
		</html>
		<?php

//if not receiveing a file, but being asked to return a previously received file, ie by moodle
//This url of this script is passed to repo, which then uses it as the download url
//this way we leave all the saving and file system to standard repo behaviour
//if server can't resolve it own DNS it will fail, that should never happen ... right?
}else{
	
	
	$fullPath=$CFG->dataroot . '/temp/download/' . $filename;
	if ($fd = fopen ($fullPath, "r")) {
    $fsize = filesize($fullPath);
    $path_parts = pathinfo($fullPath);
    $ext = strtolower($path_parts["extension"]);
    switch ($ext) {
        case "jpg":
        header("Content-type: image/jpeg"); // add here more headers for diff. extensions
        header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
        break;
        
        case "mp3":
        header("Content-type: audio/mpeg3"); // add here more headers for diff. extensions
        header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
        break;
        
        default;
        header("Content-type: application/octet-stream");
        header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
    }
    header("Content-length: $fsize");
    header("Cache-control: private"); //use this to open files directly
    while(!feof($fd)) {
        $buffer = fread($fd, 2048);
        echo $buffer;
    }
}
fclose ($fd);
exit;
}

?>