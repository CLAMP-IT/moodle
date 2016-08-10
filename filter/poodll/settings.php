<?php  //$Id: settings.php,v 0.0.0.1 2010/01/15 22:40:00 thomw Exp $


defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

$items = array();



$items[] = new admin_setting_heading('filter_poodll_registration_settings', get_string('filter_poodll_registration_heading', 'filter_poodll'), get_string('filter_poodll_registration_explanation', 'filter_poodll'));
    $items[] = new admin_setting_configtext('filter_poodll_registrationkey', get_string('registrationkey', 'filter_poodll'), get_string('registrationkey_explanation', 'filter_poodll'), '');

//$items[] = new admin_setting_heading('filter_poodll_settings', get_string('settings', 'filter_poodll'), '');
	//PoodLL Network Settings.
$items[] = new admin_setting_heading('filter_poodll_network_settings', get_string('filter_poodll_network_heading', 'filter_poodll'), 'You strange monkey');
$items[] = new admin_setting_configtext('filter_poodll_servername', get_string('servername', 'filter_poodll'), '', 'tokyo.poodll.com');
$items[] = new admin_setting_configtext('filter_poodll_serverid', get_string('serverid', 'filter_poodll'), '', 'poodll');
$items[] = new admin_setting_configtext('filter_poodll_serverport', get_string('serverport', 'filter_poodll'), '', '1935', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_serverhttpport', get_string('serverhttpport', 'filter_poodll'), '', '443', PARAM_INT);
$items[] = new admin_setting_configcheckbox('filter_poodll_autotryports', get_string('autotryports', 'filter_poodll'), '', 1);

	//PoodLL player type settings.
$items[] = new admin_setting_heading('filter_poodll_playertypes_settings', get_string('filter_poodll_playertypes_heading', 'filter_poodll'), '');
$options = array('pd' => 'PoodLL', 'fp' => 'Flowplayer', 'jw' => 'JWPlayer');
$items[] = new admin_setting_configselect('filter_poodll_defaultplayer', get_string('defaultplayer', 'filter_poodll'), '', 'fp', $options);
$options = array('native' => 'Native', 'js' => 'Javascript');
$items[] = new admin_setting_configselect('filter_poodll_html5controls', get_string('html5controls', 'filter_poodll'), '', 'native', $options);
$items[] = new admin_setting_configcheckbox('filter_poodll_download_media_ok', get_string('showdownloadicon', 'filter_poodll'), '', 0);

// PoodLL Flashcards
    $items[] = new admin_setting_heading('filter_poodll_flashcards_settings', get_string('filter_poodll_flashcards_heading', 'filter_poodll'), '');
    $options = array('poodll' => 'PoodLL', 'reveal' => 'Reveal.JS', 'owl'=>"Owl");
$items[] = new admin_setting_configselect('filter_poodll_flashcards_type', get_string('flashcardstype', 'filter_poodll'), '', 'poodll', $options);


	//PoodLL Filepicker intercept settings.
$items[] = new admin_setting_heading('filter_poodll_intercept_settings', get_string('filter_poodll_intercept_heading', 'filter_poodll'), '');
$items[] = new admin_setting_configcheckbox('filter_poodll_handleflv', get_string('handleflv', 'filter_poodll'), '', 1);
$items[] = new admin_setting_configcheckbox('filter_poodll_handlemp4', get_string('handlemp4', 'filter_poodll'), '', 1);
$items[] = new admin_setting_configcheckbox('filter_poodll_handlemov', get_string('handlemov', 'filter_poodll'), '', 1);
$items[] = new admin_setting_configcheckbox('filter_poodll_handlemp3', get_string('handlemp3', 'filter_poodll'), '', 1);

	//audio player settings.	
$items[] = new admin_setting_heading('filter_poodll_audioplayer_settings', get_string('filter_poodll_audioplayer_heading', 'filter_poodll'), '');
$items[] = new admin_setting_configtext('filter_poodll_audiowidth', get_string('audiowidth', 'filter_poodll'), '', '320', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_audioheight', get_string('audioheight', 'filter_poodll'), '', '40', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_miniplayerwidth', get_string('miniplayerwidth', 'filter_poodll'), '', '32', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_wordplayerfontsize', get_string('wordplayerfontsize', 'filter_poodll'), '', '24', PARAM_INT);

//audio capture settings
$items[] = new admin_setting_heading('filter_poodll_mic_settings', get_string('filter_poodll_mic_heading', 'filter_poodll'), '');
$items[] = new admin_setting_configtext('filter_poodll_studentmic', get_string('studentmic', 'filter_poodll'), '', '');
$items[] = new admin_setting_configtext('filter_poodll_micrate', get_string('micrate', 'filter_poodll'), '','22', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_micsilencelevel', get_string('micsilencelevel', 'filter_poodll'), '', '1', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_micgain', get_string('micgain', 'filter_poodll'), '', '50', PARAM_INT); 
$items[] = new admin_setting_configtext('filter_poodll_micecho', get_string('micecho', 'filter_poodll'), '', 'yes');
$items[] = new admin_setting_configtext('filter_poodll_micloopback', get_string('micloopback', 'filter_poodll'), '', 'no');
$items[] = new admin_setting_configcheckbox('filter_poodll_miccanpause', get_string('miccanpause', 'filter_poodll'), '', 0);
$items[] = new admin_setting_configtext('filter_poodll_mp3skin', get_string('mp3skin', 'filter_poodll'), 
		get_string('mp3skin_details', 'filter_poodll'), 'none');


//video player settings.
$items[] = new admin_setting_heading('filter_poodll_videoplayer_setting', get_string('filter_poodll_videoplayer_heading', 'filter_poodll'), '');
$items[] = new admin_setting_configtext('filter_poodll_videowidth', get_string('videowidth', 'filter_poodll'), '', '480', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_videoheight', get_string('videoheight', 'filter_poodll'), '', '360', PARAM_INT);




//flow player settings.
$items[] = new admin_setting_heading('filter_poodll_flowplayer_setting', get_string('filter_poodll_flowplayer_heading', 'filter_poodll'), '');
$items[] = new admin_setting_configcheckbox('filter_poodll_audiosplash', get_string('audiosplash', 'filter_poodll'), get_string('audiosplashdetails', 'filter_poodll'), 0);
$items[] = new admin_setting_configcheckbox('filter_poodll_videosplash', get_string('videosplash', 'filter_poodll'), get_string('videosplashdetails', 'filter_poodll'), 1);
$items[] = new admin_setting_configcheckbox('filter_poodll_thumbnailsplash', get_string('thumbnailsplash', 'filter_poodll'), get_string('thumbnailsplashdetails', 'filter_poodll'), 1);
$embedoptions = array('swfobject' => 'SWF Object', 'flowplayer' => 'Flowplayer JS', 'flashembed' => 'Flashembed JS');
$items[] = new admin_setting_configselect('filter_poodll_fp_embedtype', get_string('fpembedtype', 'filter_poodll'), get_string('fp_embedtypedescr', 'filter_poodll'), 'swfobject', $embedoptions);
$items[] = new admin_setting_configtext('filter_poodll_fp_bgcolor', get_string('fp_bgcolor', 'filter_poodll'), '', '#0a2bb5');
$items[] = new admin_setting_configcheckbox('filter_poodll_fp_playlist', get_string('fp_enableplaylist', 'filter_poodll'), get_string('fp_enableplaylistdescr', 'filter_poodll'), 0);


//video capture settings.
$items[] = new admin_setting_heading('filter_poodll_camera_settings', get_string('filter_poodll_camera_heading', 'filter_poodll'), '');
$items[] = new admin_setting_configtext('filter_poodll_studentcam', get_string('studentcam', 'filter_poodll'), '', '');
$options = array('160' => '160x120', '320' => '320x240','480' => '480x360','640' => '640x480','800'=>'800x600','1024'=>'1024x768','1280'=>'1280x1024','1600' => '1600x1200',);
$items[] = new admin_setting_configselect('filter_poodll_capturewidth', get_string('capturewidth', 'filter_poodll'), '', '480', $options);
//$items[] = new admin_setting_configtext('filter_poodll_captureheight', get_string('captureheight', 'filter_poodll'), '', '240', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_capturefps', get_string('capturefps', 'filter_poodll'), '', '17', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_bandwidth', get_string('bandwidth', 'filter_poodll'), '', '0', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_picqual', get_string('picqual', 'filter_poodll'), '', '7', PARAM_INT);

//mp3 recorder settings.
$items[] = new admin_setting_heading('filter_poodll_mp3recorder_settings', get_string('filter_poodll_mp3recorder_heading', 'filter_poodll'), '');
$options = array('normal' => get_string('normal', 'filter_poodll'), 'tiny' => get_string('tiny', 'filter_poodll'));
$items[] = new admin_setting_configselect('filter_poodll_mp3recorder_size', get_string('size', 'filter_poodll'), '', 'normal', $options);

/*
//HTML5 Recording
*/
$items[] = new admin_setting_heading('filter_poodll_html5use_settings', get_string('html5use_heading', 'filter_poodll'), '');
$options = array('never' => get_string('neverhtml5', 'filter_poodll'),'mobile' => get_string('mobileonly', 'filter_poodll'), 'webkit' => get_string('mobileandwebkit', 'filter_poodll'),'always' => get_string('alwayshtml5', 'filter_poodll'));
$items[] = new admin_setting_configselect('filter_poodll_html5rec', get_string('html5rec', 'filter_poodll'), '', 'mobile', $options);
$items[] = new admin_setting_configselect('filter_poodll_html5play', get_string('html5play', 'filter_poodll'), '', 'mobile', $options);
$items[] = new admin_setting_configselect('filter_poodll_html5widgets', get_string('html5widgets', 'filter_poodll'), '', 'mobile', $options);
$items[] = new admin_setting_configcheckbox('filter_poodll_html5fancybutton', get_string('html5fancybutton', 'filter_poodll'), '', 1);

/*
//File Conversions
*/
$items[] = new admin_setting_heading('filter_poodll_transcode_settings', get_string('transcode_heading', 'filter_poodll'), '');
$items[] = new admin_setting_configcheckbox('filter_poodll_videotranscode', get_string('videotranscode', 'filter_poodll'), get_string('videotranscodedetails', 'filter_poodll'), 0);
$items[] = new admin_setting_configcheckbox('filter_poodll_audiotranscode', get_string('audiotranscode', 'filter_poodll'), get_string('audiotranscodedetails', 'filter_poodll'), 0);
$items[] = new admin_setting_configcheckbox('filter_poodll_ffmpeg', get_string('ffmpeg', 'filter_poodll'), get_string('ffmpeg_details', 'filter_poodll'), 0);
$items[] = new admin_setting_configtext('filter_poodll_ffmpeg_mp3opts', get_string('mp3opts', 'filter_poodll'), get_string('mp3opts_details', 'filter_poodll'), '');
$items[] = new admin_setting_configtext('filter_poodll_ffmpeg_mp4opts', get_string('mp4opts', 'filter_poodll'), get_string('mp4opts_details', 'filter_poodll'), '');
$items[] = new admin_setting_configcheckbox('filter_poodll_bgtranscode_video', get_string('bgtranscode_video', 'filter_poodll'), get_string('bgtranscodedetails_video', 'filter_poodll'), 0);
$items[] = new admin_setting_configcheckbox('filter_poodll_bgtranscode_audio', get_string('bgtranscode_audio', 'filter_poodll'), get_string('bgtranscodedetails_audio', 'filter_poodll'), 0);


//PoodLL Whiteboard
$items[] = new admin_setting_heading('filter_poodll_whiteboard_setting', get_string('filter_poodll_whiteboard_heading', 'filter_poodll'), '');
$options = array('poodll' => 'PoodLL Whiteboard(Flash)','drawingboard' => 'Drawing Board(js)', 'literallycanvas' => 'Literally Canvas(js)');
$items[] = new admin_setting_configselect('filter_poodll_defaultwhiteboard', get_string('defaultwhiteboard', 'filter_poodll'), '', 'literallycanvas', $options);
$items[] = new admin_setting_configtext('filter_poodll_whiteboardwidth', get_string('wboardwidth', 'filter_poodll'), '', '600', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_whiteboardheight', get_string('wboardheight', 'filter_poodll'), '', '350', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_autosavewhiteboard', get_string('wboardautosave', 'filter_poodll'), get_string('wboardautosave_details', 'filter_poodll'), 2000, PARAM_INT);


//Video Gallery Settings
$items[] = new admin_setting_heading('filter_poodll_videogallery_setting', get_string('filter_poodll_videogallery_heading', 'filter_poodll'), '');
$items[] = new admin_setting_configtext('filter_poodll_biggallwidth', get_string('biggallwidth', 'filter_poodll'), '', '850', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_biggallheight', get_string('biggallheight', 'filter_poodll'), '', '680', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_smallgallwidth', get_string('smallgallwidth', 'filter_poodll'), '', '450', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_smallgallheight', get_string('smallgallheight', 'filter_poodll'), '', '320', PARAM_INT);


//Legacy headings
$items[] = new admin_setting_heading('filter_poodll_legacy_setting', get_string('filter_poodll_legacy_heading', 'filter_poodll'), '');
$items[] = new admin_setting_configtext('filter_poodll_datadir', get_string('datadir', 'filter_poodll'), get_string('datadirdetails', 'filter_poodll'), 'poodlldata');

$items[] = new admin_setting_configtext('filter_poodll_newpairwidth', get_string('newpairwidth', 'filter_poodll'), '', '750', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_newpairheight', get_string('newpairheight', 'filter_poodll'), '', '480', PARAM_INT);


foreach ($items as $item) {
    $settings->add($item);
}

}
?>
