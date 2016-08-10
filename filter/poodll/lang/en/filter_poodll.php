<?PHP // $Id: filter_poodll.php ,v 1.3 2012/05/16 12:47:13 Justin Hunt Exp $ 
      // PoodLl Filter
$string['filtername'] = 'PoodLL Filter';
//officially its not needed, but "new version available" email doesn't know to look for filtername
$string['pluginname'] = 'PoodLL Filter';

$string['settings'] = 'PoodLL Filter Settings';
$string['activate'] = 'Activate PoodLL?';

//headings
$string['filter_poodll_network_heading'] = 'PoodLL Network Settings';
$string['filter_poodll_audioplayer_heading'] = 'Audio Player Settings';
$string['filter_poodll_mic_heading'] = 'Microphone Settings';
$string['filter_poodll_videoplayer_heading'] = 'Video Player Settings';
$string['filter_poodll_camera_heading'] = 'Web Camera Settings';
$string['filter_poodll_videogallery_heading'] = 'Video Gallery Settings';
$string['filter_poodll_whiteboard_heading'] = 'Whiteboard Settings';
$string['filter_poodll_legacy_heading'] = 'PoodLL Legacy Settings';
$string['filter_poodll_playertypes_heading'] = 'Default Player Types';
$string['filter_poodll_intercept_heading'] = 'Filetypes PoodLL Handles by Default';
$string['filter_poodll_flowplayer_heading'] = 'Flowplayer Settings'; 
$string['filter_poodll_mp3recorder_heading'] = 'MP3 Recorder Settings';
$string['filter_poodll_registration_heading'] ='Register your PoodLL';
$string['filter_poodll_flashcards_heading'] ='Flashcards Settings';
$string['filter_poodll_registration_explanation'] ="From PoodLL version 2.8.0 you are required to obtain and enter your PoodLL registration key. Registration is currently free, and you can obtain your key in a jiffy from <a href='http://poodll.com/registration'>http://poodll.com/registration</a>";

$string['registrationkey'] = 'Registration Key';
$string['registrationkey_explanation'] ="Enter your PoodLL registration key here. It is free, but PoodLL won't work without it. You can obtain a key from <a href='http://poodll.com/registration'>http://poodll.com/registration</a>";
$string['unregistered'] = 'PoodLL not displayed because it has not been registered. Registration is free. Ask your teacher/administrator to visit the PoodLL filter settings page and complete the registration process.';

$string['flashcardstype'] ='Flashcards Type';

$string['defaultplayer'] = 'Default A/V Player';
$string['html5controls'] = 'HTML5 Controls';
$string['handleflv'] = 'Handle FLV Files';
$string['handlemp4'] = 'Handle MP4 Files';
$string['handlemov'] = 'Handle MOV Files';
$string['handlemp3'] = 'Handle MP3 Files';


$string['videowidth'] = 'Video Player Width';
$string['videoheight'] = 'Video Player Height';
$string['videosplash'] = 'Show Simple Video Splash';
$string['videosplashdetails'] = 'Splash screen is shown for Flowplayer only.';
$string['thumbnailsplash'] = 'Use Preview as Splash';
$string['thumbnailsplashdetails'] = 'Preview splash uses first frame of video as the splash image. Only use this when using server tokyo.poodll.com.';
$string['audiowidth'] = 'Audio Player Width';
$string['audioheight'] = 'Audio Player Height';
$string['audiosplash'] = 'Show Simple Audio Splash';
$string['audiosplashdetails'] = 'Splash screen is shown for Flowplayer only.';
$string['miniplayerwidth'] = 'Mini Player Width';
$string['wordplayerfontsize'] = 'Word Player Fontsize';

$string['showdownloadicon'] = 'Show download icon under players';


$string['talkbackwidth'] = 'Talkback Player Width';
$string['talkbackheight'] = 'Talkback Player Height';
$string['showwidth'] = 'Screencast Player Width';
$string['showheight'] = 'Screencast Player Height';

$string['datadir'] = 'PoodLL Data Dir';
$string['datadirdetails'] = 'A sub directory of Moodle dir, to allow some components Moodle 1.9 style file access to media resources. Should only be used for non sensitive media resources. PoodLL will not create, or manage access rights for, this folder';

$string['forum_recording'] = 'PoodLL Forum: AV Recording Enabled?';
$string['forum_audio'] = 'PoodLL Forum: Audio?';
$string['forum_video'] = 'PoodLL Forum: Video?';

$string['journal_recording'] = 'PoodLL Journal: AV Recording Enabled?';
$string['journal_audio'] = 'PoodLL Journal: Audio?';
$string['journal_video'] = 'PoodLL Journal: Video?';

$string['servername'] = 'PoodLL Host Address';
$string['serverid'] = 'PoodLL Server Id';
$string['serverport'] = 'PoodLL Server Port (RTMP)';
$string['serverhttpport'] = 'PoodLL Server Port (HTTP)';
$string['autotryports'] = 'Try diff. ports if cannot connect';

//$string['useproxy'] = 'Use Moodle Proxy?';

$string['usecourseid'] = 'Use Course ID?';
$string['filename'] = 'Default Filename';
$string['overwrite'] = 'Overwrite Same?';

$string['screencapturedevice'] = 'Screencast Capture Device Name';

$string['nopoodllresource'] = '--- Select PoodLL Resource ---';

$string['biggallwidth'] = 'Vid. Gallery (big) Width';
$string['biggallheight'] = 'Vid. Gallery (big) Height';

$string['smallgallwidth'] = 'Vid. Gallery (small) Width';
$string['smallgallheight'] = 'Vid. Gallery (small) Height';

$string['newpairwidth'] = 'Pairwork Widget Width ';
$string['newpairheight'] = 'Pairwork Widget Height';

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


//fpembedtype
$string['fpembedtype'] = 'Flowplayer Embed Method';
$string['fp_embedtypedescr'] = 'SWF Object is the most reliable. Flowplayer JS handles preview splash images better. If you use Flowplayer JS consider turning off Multimedia Plugins filter MP3/FLV/MP4 handling to avoid double-filtering. ';
$string['fp_bgcolor'] = 'Flowplayer Color';
$string['fp_enableplaylist'] = 'Enable Flowplayer Audiolist';
$string['fp_enableplaylistdescr'] = 'This requires the JQuery javascript library and adds about 100kb to the page download size. Moodle will cache it though, so there should be no noticeable slowdown. You should also set the Flowplayer embed setting to Flowplayer js. Purge the cache after changing this or any flowplayer config setting.';

//html5 settings
$string['html5use_heading'] ='When to use HTML5';
$string['html5rec'] ='HTML5 Recording';
$string['html5play'] ='HTML5 Playback';
$string['html5widgets'] ='HTML5 PoodLL Widgets';
$string['html5fancybutton'] ='Use Fancy Upload Button';

//mp3 recorder settings
$string['size'] ='Size';
$string['tiny'] ='Tiny';
$string['normal'] ='Normal';


//transcode settings
$string['transcode_heading'] ='Audio/Video File Conversion Settings';
$string['videotranscode'] = 'Auto Conv. to MP4';
$string['videotranscodedetails'] = 'Convert recorded/uploaded video files to MP4 format before storing in Moodle. This works for recordings made on tokyo.poodll.com, or uploaded recordings if using FFMPEG';
$string['audiotranscode'] = 'Auto Conv. to MP3';
$string['audiotranscodedetails'] = 'Convert recorded/uploaded audio file to MP3 format before storing in Moodle. This works for recordings made on tokyo.poodll.com, or uploaded recordings if using FFMPEG';
$string['ffmpeg'] ='Convert uploaded media with FFMPEG';
$string['ffmpeg_details'] ='FFMPEG must be installed on your Moodle Server and on the system path. It will need to support converting to mp3, so try it out first on the command line, eg ffmpeg -i somefile.flv somefile.mp3 . This is still *experimental*';
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
$string['recui_pause'] ='Pause';
$string['recui_play'] ='Play';
$string['recui_stop'] ='Stop';
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
$string['recui_inaudibleerror'] ='We can not hear you. Please check flash and browser permissions.';
$string['recui_timeouterror'] ='The request timed out. Sorry.';
$string['recui_uploaderror'] ='An error occurred and your file has NOT been uploaded.';
$string['recui_btnupload'] ='Record or Choose a File';

$string['neverhtml5'] = 'Never use HTML5';
$string['mobileonly'] = 'Mobile devices only';
$string['mobileandwebkit'] = 'Mobile + Webkit browsers(Safari,Chrome etc)';
$string['alwayshtml5'] = 'Always use HTML5';
//$string['wboard29problem_details'] = 'The Literally Canvas whiteboard does not work in Moodle 2.9 (or greater) yet. On Moodle 2.9, even if selected, DrawingBoard will be displayed in its place';
