<?PHP // $Id: filter_poodll.php ,v 1.3 2012/05/16 12:47:13 Justin Hunt Exp $ 
      // PoodLl Filter
$string['filtername'] = 'PoodLL Filter';
//officially its not needed, but "new version available" email doesn't know to look for filtername
$string['pluginname'] = 'PoodLL Filter';

$string['settings'] = 'PoodLL Filter Settings';
$string['activate'] = 'Activate PoodLL?';

$string['generalsettings'] = 'General Settings';

//headings
$string['filter_poodll_network_heading'] = 'PoodLL Network Settings';
$string['filter_poodll_audioplayer_heading'] = 'Audio Player Settings';
$string['filter_poodll_mic_heading'] = 'Microphone Settings';
$string['filter_poodll_videoplayer_heading'] = 'Video Player Settings';
$string['filter_poodll_camera_heading'] = 'Web Camera Settings';
$string['filter_poodll_videogallery_heading'] = 'Video Gallery Settings';
$string['filter_poodll_whiteboard_heading'] = 'Whiteboard Settings';
$string['filter_poodll_mp3recorder_heading'] = 'MP3 Recorder Settings';
$string['filter_poodll_registration_heading'] ='Register your PoodLL';
$string['filter_poodll_flashcards_heading'] ='Flashcards Settings';
$string['filter_poodll_registration_explanation'] ="PoodLL 3 requires a registration key. If you do not have one visit Poodll.com to get one.";

$string['registrationkey'] = 'Registration Key';
$string['registrationkey_explanation'] ="Enter your PoodLL registration key here. You can obtain a key from <a href='https://poodll.com/poodll-3-2'>https://poodll.com/poodll-3-2</a>";
$string['license_details'] ='<br> -------------- <br> License type: {$a->license_type} <br> Expires(JST): {$a->expire_date} <br> Registered URL: {$a->registered_url} ';

$string['usecloudrecording'] = 'Cloud recording';
$string['usecloudrecording_desc'] = 'PoodLL cloud recording. This enables transcoding and other services in the cloud. The PoodLL iOS app requires this, and so too do the html5 audio and video recorders. Recorded files are not hosted in the cloud.';

$string['mobile_show'] = 'Show mobile';
$string['mobile_show_desc'] = 'When the user is on an iOS device, instead of a recorder they are shown an "upload/record" button and a "use PoodLL app" button. Uncheck this to hide the PoodLL app button.'; 
$string['mobile_audio_quality'] = 'Audio quality';
$string['mobile_video_quality'] = 'Video quality';
$string['lowquality'] = 'low';
$string['mediumquality'] = 'medium';
$string['highquality'] = 'high';
$string['default_camera'] = 'Default camera';
$string['camerafront'] = 'front';
$string['cameraback'] = 'back';

$string['awssdkversion'] = 'AWS SDK';
$string['awssdkversion_desc'] = 'PoodLL cloud recording uses Amazon Web Services (AWS). Version 3.x is supported but not shipped with PoodLL. Version 2.x of the AWS SDK will work on PHP 5.3 or greater. You should not need to change this, but contact PoodLL support if the need arises. ';

$string['uploadkey'] = 'Upload key';
$string['uploadkey_desc'] = 'PoodLL cloud recording requires an upload key for recording. You should receive this when you sign up for PoodLL. Enter the upload key here.';
$string['uploadsecret'] = 'Upload secret';
$string['uploadsecret_desc'] = 'PoodLL cloud recording requires an upload secret for recording.  You should receive this when you sign up for PoodLL. Enter the upload secret here.';


$string['unregistered'] = 'PoodLL not displayed because it has not been registered. Ask your teacher/administrator to register PoodLL at PoodLL.com.';
$string['expired'] = 'PoodLL not displayed because registration has expired. Ask your teacher/administrator to renew the registration at PoodLL.com.';


$string['flashcardstype'] ='Flashcards Type';

$string['recorderorder'] = 'Preferred Recorder Order';
$string['recorderorder_desc'] = 'PoodLL will choose the best recorder it can if the user browser and platform support it. You set the order here.';
$string['showdownloadicon'] = 'Show download icon under players';


$string['servername'] = 'PoodLL Host Address';
$string['serverid'] = 'PoodLL Server Id';
$string['serverport'] = 'PoodLL Server Port (RTMP)';
$string['serverhttpport'] = 'PoodLL Server Port (HTTP)';
$string['autotryports'] = 'Try diff. ports if cannot connect';


$string['wboardwidth'] = 'Whiteboard Default Width ';
$string['wboardheight'] = 'Whiteboard Default Height';
$string['wboardautosave'] = 'Autosave(milliseconds)';
$string['wboardautosave_details'] = 'Saves the drawing when the user has paused drawing after X milliseconds. 0 = no autosave';

//video capture settings
$string['capturewidth'] = 'Video Recorder Capture Size';
$string['captureheight'] = 'Video Recorder Capture Height';
$string['capturefps'] = 'Video Recorder Capture FPS';
$string['studentcam'] = 'Preferred device name for camera';
$string['bandwidth'] = 'Student connection. bytes/second. Affects webcam qual. ';
$string['picqual'] = 'Target webcam qual. 1 - 10 ';



//audio capture settings 
$string['studentmic'] = 'Preferred  device name for microphone';
$string['micrate'] = 'Mic. Rate';
$string['micgain'] = 'Mic. Gain';
$string['micsilencelevel'] = 'Mic. Silence Level';
$string['micecho'] = 'Mic. Echo';
$string['micloopback'] = 'Mic. Loopback';
$string['miccanpause'] = 'Allow pause (MP3 recorder only)';
$string['mp3skin'] = 'MP3 Skin';
$string['mp3skin_details'] = 'If you want to use a recorder skin, ala theme, enter its name here. Otherwise enter: none.';

//mp3 recorder settings
$string['size'] ='Size';
$string['tiny'] ='Tiny';
$string['normal'] ='Normal';
$string['mp3_nocloud'] ='No Cloud';
$string['mp3_nocloud_details'] ='Do not submit Flash mp3 recordings to cloud for transcode and copyback.';

//transcode settings
$string['transcode_heading'] ='Audio/Video File Conversion Settings';
$string['videotranscode'] = 'Auto Conv. to MP4';
$string['videotranscodedetails'] = 'Convert recorded/uploaded video files to MP4 format before storing in Moodle. This works for recordings made on tokyo.poodll.com, or uploaded recordings if using FFMPEG';
$string['audiotranscode'] = 'Auto Conv. to MP3';
$string['audiotranscodedetails'] = 'Convert recorded/uploaded audio file to MP3 format before storing in Moodle. This works for recordings made on tokyo.poodll.com, or uploaded recordings if using FFMPEG';
$string['ffmpeg'] ='Convert uploaded media with FFMPEG';
$string['ffmpeg_details'] ='FFMPEG must be installed on your Moodle Server and on the system path. It will need to support converting to mp3, so try it out first on the command line, eg ffmpeg -i somefile.flv somefile.mp3 . ';
$string['mp4opts'] ='FFMPEG MP4 Conversion options';
$string['mp4opts_details'] ='Leave this empty if you wish to let FFMPEG make the decisions. Anything you put here will appear between [ffmpeg -i myfile.xx ] and [ myfile.mp4 ]';
$string['mp3opts'] ='FFMPEG MP3 Conversion options';
$string['mp3opts_details'] ='Leave this empty if you wish to let FFMPEG make the decisions. Anything you put here will appear between [ffmpeg -i myfile.xx ] and [ myfile.mp3 ]';

$string['mobile_os_version_warning'] ='<p>Your OS Version is too low</p>
		<p>Android requires version 4 or greater.</p>
		<p>iOS requires version 6 or greater.</p>';

$string['defaultwhiteboard'] = 'Default whiteboard';
$string['whiteboardsave'] = 'Save Picture';
$string['poodll:candownloadmedia'] = 'Can download media'; 

$string['bgtranscode_video'] = 'Perform Conversions to MP4 Background'; 
$string['bgtranscodedetails_video'] = 'This is more reliable than performing them while user waits. But the user will not get their video till cron has run after saving. Only works if you are using FFMPEG and Moodle 2.7 or higher.'; 
$string['bgtranscode_audio'] = 'Perform Conversions to MP3 Background'; 
$string['bgtranscodedetails_audio'] = 'This is more reliable than performing them while user waits. But the user will not get their audio till cron has run after saving. Only works if you are using FFMPEG and Moodle 2.7 or higher. For recordings in MP3 with the MP3 recorder, conversion takes place in the browser, not on the server. So server side conversion (FFMPEG) will not be used.'; 
$string['recui_record'] ='Record';
$string['recui_recordorchoose'] ='Record or Choose';
$string['recui_pause'] ='Pause';
$string['recui_play'] ='Play';
$string['recui_stop'] ='Stop';
$string['recui_save'] ='Upload';
$string['recui_time'] ='Time:';
$string['recui_audiogain'] ='Audio Gain';
$string['recui_silencelevel'] ='Silence Level';
$string['recui_echo'] ='Echo Suppression';
$string['recui_loopback'] ='Loopback';
$string['recui_audiorate'] ='Audio Rate';
$string['recui_on'] ='On';
$string['recui_off'] ='Off';
$string['recui_ok'] ='OK';
$string['recui_close'] ='Close';
$string['recui_continue'] ='Continue';
$string['recui_uploading'] ='uploading';
$string['recui_converting'] ='converting';
$string['recui_nothingtosaveerror'] ='Nothing was captured. Sorry .. nothing to upload.';
$string['recui_inaudibleerror'] ='We can not hear you. Please check flash and browser permissions.';
$string['recui_timeouterror'] ='The request timed out. Sorry.';
$string['recui_uploaderror'] ='An error occurred and your file has NOT been uploaded.';
$string['recui_btnupload'] ='Record or Choose a File';
$string['recui_awaitingconfirmation'] ='Awaiting confirmation';
$string['recui_openrecorderapp'] ='PoodLL App';
$string['recui_uploadafile'] ='Upload File';
$string['recui_uploadsuccess'] ='Uploaded successfully';
$string['recui_takesnapshot'] ='Take Picture';
$string['recui_cancelsnapshot'] ='Cancel';
$string['insert'] ='Insert';
$string['cancel'] ='Cancel';

$string['neverhtml5'] = 'Never use HTML5';
$string['mobileonly'] = 'Mobile devices only';
$string['mobileandwebkit'] = 'Mobile + Webkit browsers(Safari,Chrome etc)';
$string['alwayshtml5'] = 'Always use HTML5';

//extensions handling and local filter settings
$string['sitedefault'] = "Site Default";
$string['player'] = 'Player {$a}';

//template strings
$string['templatepageheading'] = '(T): {$a}';
$string['templatepagewidgetheading'] = '(W): {$a}';
$string['templatepageplayerheading'] = '(P): {$a}';
$string['templatepageheading'] = '(T): {$a}';
$string['templateheading'] = 'Settings for Poodll Template {$a}';
$string['template'] = 'The body of template {$a}';
$string['template_desc'] = 'Put the template here, define variables by surrounding them with @@ marks at either e. eg @@variable@@';
$string['templatename'] = 'The display name for the template {$a}';
$string['templatename_desc'] = 'The name can contain numbers and letters, underscores, hyphens and dots .';
$string['templatekey'] = 'The key that identifies template {$a}';
$string['templatekey_desc'] = 'The key should be one word and only contain numbers and letters, underscores, hyphens and dots .';
$string['templateversion'] = 'The version of this template {$a}';
$string['templateversion_desc'] = 'When sharing templates it is best to maintain a clear version per release. The version format is up to you.';
$string['templateinstructions'] = 'Instructions (template {$a})';
$string['templateinstructions_desc'] = 'Any instructions entered here will be displayed on the PoodLL atto form if this template is available to be shown there. Keep them short or it will look bad.';
$string['template_showatto'] = 'Show in Atto (template {$a})';
$string['template_showatto_desc'] = 'Display a button and form for this widget in the PoodLL Widgets dialog for Atto.';
$string['template_showplayers'] = 'Show in players list (template {$a})';
$string['template_showplayers_desc'] = 'Display in the dropdown list of players available to be associated with a file extension.';
$string['templateend'] = 'End tags(template {$a})';
$string['templateend_desc'] = 'If your template encloses user content, eg an info box, put the closing tags here. The user will enter something like {POODLL:mytag_end} to close out the filter.';
$string['templatescript'] = 'Custom JS (template {$a})';
$string['templatescript_desc'] = 'If your template needs to run custom javascript, enter that here. It will be run once all the elements have loaded on the page.';
$string['templatedefaults'] = 'variable defaults (template {$a})';
$string['templatedefaults_desc'] = 'Define the defaults in comma delimited sets of name=value pairs. eg width=800,height=900,feeling=joy';
$string['templaterequire_css'] = 'Requires CSS (template {$a})';
$string['templaterequire_js'] = 'Requires JS (template {$a})';
$string['templaterequire_js_shim'] = 'Shim Export(template {$a})';
$string['templaterequire_jquery'] = 'Requires JQuery (template {$a})';
$string['templaterequire_css_desc'] = 'A link(1 only) to an external CSS file that this template requires. optional.';
$string['templaterequire_js_desc'] = 'A link(1 only) to an external JS file that this template requires. optional.';
$string['templaterequire_js_shim_desc'] = 'Enter the shim exports value if and ONLY if you need to shim.';
$string['templaterequire_jquery_desc'] = 'Its best NOT to check this. Many non AMD templates require JQuery. Checking here will load JQuery, but not very well. Your theme may already load JQuery anyway. If not, add this string to Site Administration -> Appearance -> Additional HTML (within HEAD):<br/> &lt;script src="https://code.jquery.com/jquery-1.11.2.min.js"&gt;&lt;/script&gt;';
$string['templatecount'] = 'Template Count';
$string['templatecount_desc'] = 'The number of templates you can have. Default is 20.';
$string['templateheadingjs'] = 'Javascript Settings.';
$string['templateheadingcss'] = 'CSS/Style Settings.';
$string['templatestyle'] = 'Custom CSS (template {$a})';
$string['templatestyle_desc'] = 'Enter any custom CSS that your template uses here. Template variables will not work here. Just plain old css.';
$string['templaterequire_amd'] = 'Load via AMD';
$string['templaterequire_amd_desc'] = 'AMD is a javascript loading mechanism. If you upload or link to javascript libraries in your template, you might have to uncheck this. It only applies if on Moodle 2.9 or greater';
$string['templatealternate'] = 'Alternate content (template {$a})'; 
$string['templatealternate_desc'] = 'Content that can be used when the custom CSS and javascript content is not available. Currently this is used when the template is processed by a webservice, probably for content on the mobile app';
$string['templatealternate_end'] = 'Alternate content end ((template {$a})'; 
$string['templatealternate_end_desc'] = 'Closing alternate content tags for templates that enclose user content with start and end PoodLL tags';
$string['extensions'] = 'File Extensions';
$string['extensions_desc'] = 'A CSV (comma separated value) list of file extensions this filter can parse.';
$string['presets'] = 'Autofill template with a Preset';
$string['presets_desc'] = 'PoodLL comes with some default presets you can use out of the box, or to help you get started with your own template. Choose one of those here, or just create your own template from scratch. You can export a template as a bundle by clicking on the green box above. You can import a bundle by dragging it onto the green box.';
$string['bundle'] = 'Bundle';
$string['useplayer'] = '{$a} Player';
$string['useplayerdesc'] = "The player selected will use the information from the appropriate template.";
$string['handle'] = 'Handle {$a}';
$string['widgetsettings'] = 'Widget Settings';
$string['supportinfo'] = 'Support Info';
$string['extensionsettings'] = 'File Extension Settings';
$string['mobilesettings'] = 'iOS App Settings';
$string['dataset'] = 'Dataset';
$string['dataset_desc'] = 'Poodll allows you to pull a dataset from the database for use in your template. This is an advanced feature. Enter the sql portion of a $DB->get_records_sql call here.';
$string['datasetvars'] = 'Dataset Variables';
$string['datasetvars_desc'] = 'Put a comma separated list of variables that make up the vars for the SQL. You can and probably will want to use variables here.';
$string['value']='value';

$string['poodllsupportinfo']='PoodLL Support Information';
$string['exportdiagnostics']="Export";

//html5 recorder
$string['plain_recorder']="Plain";
$string['burntrose_recorder']="Burnt Rose";
$string['html5recorder_skin']="HTML5 Recorder Skin";
$string['filter_poodll_html5recorder_heading']="HTML5 Recorder Settings";

//events
$string['event_adhoc_registered'] = 'Poodll Adhoc task registered';
$string['event_adhoc_move_registered'] = 'Poodll Adhoc move task registered';
$string['event_adhoc_convert_registered'] = 'Poodll Adhoc convert task registered';
$string['event_adhoc_completed'] = 'Poodll Adhoc task completed';
$string['event_adhoc_move_completed'] = 'Poodll Adhoc move task completed';
$string['event_adhoc_convert_completed'] = 'Poodll Adhoc convert task completed';
$string['event_debug_log'] = 'Poodll debug note';

//debug settings
$string['debug_heading'] = 'Poodll Debugging';
$string['debug_enable'] = 'Enable Debugging';
$string['debug_enable_details'] = 'If enabled, information about recordings will be stored in the Moodle logs. These are intended to assist in troubleshooting and support from the Poodll helpdesk. Turn off when not needed or you will have useless junk in your Moodle log.';