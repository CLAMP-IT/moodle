<?php
/*
 * __________________________________________________________________________
 *
 * PoodLL filter for Moodle 2.x
 *
 *  This filter will replace any PoodLL filter string with the appropriate PoodLL widget
 *
 * @package    filter
 * @subpackage poodll
 * @copyright  2012 Justin Hunt  {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * __________________________________________________________________________
 */
 
 //moved this library down into filter method, so if disabled all the poodll stuff wouldn't load


class filter_poodll extends moodle_text_filter {


		function filter($text, array $options = array()) {
			global $CFG;

			   
			if (!is_string($text)) {
				// non string data can not be filtered anyway
				return $text;
			}
			
			$newtext = $text; // fullclone is slow and not needed here
				
			//NB test regular expressions here:
			//http://www.spaweditor.com/scripts/regex/index.php
			//using match all to see what will be matched and in what index of "link" variable it will show
			//currently MP4/FLV 0 shows the whole string, 1 the link,2 the width+height param string, 3, the width, 4 the height, 5 the linked text
			//MP3 0 shows the whole string, 1 the link, 2 the linked text
			
			//I think we can optimize this whole things a bit more, anyway we try to filter as little as possible
			$havelinks = !(stripos($text, '</a>') ===false);
			
			//check for mp3
			 if ($CFG->filter_poodll_handlemp3) {
				if ($havelinks) {
				// performance shortcut - all filepicker media links  end with the </a> tag,
					$search = '/<a\s[^>]*href="([^"#\?]+\.mp3)"[^>]*>([^>]*)<\/a>/is';
					$newtext = preg_replace_callback($search, 'filter_poodll_mp3_callback', $newtext);
				}
			}
			
			//check for mp4
			if ($CFG->filter_poodll_handlemp4) {
				if ($havelinks) {
					// performance shortcut - all filepicker media links  end with the </a> tag,
					
					//justin 20120525 added ability to declare width of media by appending strings like: ?d=640x480
					//$search = '/<a\s[^>]*href="([^"#\?]+\.mp4)"[^>]*>([^>]*)<\/a>/is';
					$search = '/<a\s[^>]*href="([^"#\?]+\.mp4)(\?d=([\d]{1,4})x([\d]{1,4}))?"[^>]*>([^>]*)<\/a>/is';
					$newtext = preg_replace_callback($search, 'filter_poodll_mp4flv_callback', $newtext);
				}
			}
			
			//experimental .mov support
			if ($CFG->filter_poodll_handlemov) {
				if ($havelinks) {
					$search = '/<a\s[^>]*href="([^"#\?]+\.mov)(\?d=([\d]{1,4})x([\d]{1,4}))?"[^>]*>([^>]*)<\/a>/is';
					$newtext = preg_replace_callback($search, 'filter_poodll_mp4flv_callback', $newtext);
				}
			}
			
			//check for flv
			if ($CFG->filter_poodll_handleflv) {
				if ($havelinks) {
				// performance shortcut - all filepicker media links  end with the </a> tag,
				
					//justin 20120525 added ability to declare width of media by appending strings like: ?d=640x480
					//$search = '/<a\s[^>]*href="([^"#\?]+\.flv)"[^>]*>([^>]*)<\/a>/is';
					$search = '/<a\s[^>]*href="([^"#\?]+\.flv)(\?d=([\d]{1,4})x([\d]{1,4}))?"[^>]*>([^>]*)<\/a>/is';
					$newtext = preg_replace_callback($search, 'filter_poodll_mp4flv_callback', $newtext);
				}
			}
			
			//check for .pdl . This is a shorthand filter using presets to allow selection of PoodLL widgets
			//from the Moodle File repository
			if($havelinks){
				if (!(stripos($text, '.pdl') ===false)) {
					// performance shortcut - all filepicker media links  end with the </a> tag,
					$search = '/<a\s[^>]*href="([^"#\?]+\.pdl)"[^>]*>([^>]*)<\/a>/is';
					$newtext = preg_replace_callback($search, 'filter_poodll_pdl_callback', $newtext);
				}
			}

				/*

			//Trying this but it does not seem to improve performance
			 if (!(stripos($text, '{POODLL:') === false)) {
            	// Performance shortcut - if no poodll tag, nothing can match.
            	return $text;
       		 }		
			
	*/
			
			$search = '/{POODLL:.*?}/is';

			$newtext = preg_replace_callback($search, 'filter_poodll_callback', $newtext);
			
			if (is_null($newtext) or $newtext === $text) {
				// error or not filtered
				return $text;
			}

			return $newtext;
		}
}//end of class


/*
*	Callback function , exists outside of class definition(because its a callback ...)
*
*/
function filter_poodll_callback(array $link){
	global $CFG, $COURSE, $USER;

		$lm = new \filter_poodll\licensemanager();
		if(!$lm->validate_registrationkey($CFG->filter_poodll_registrationkey)) {
			return $lm->fetch_unregistered_content();
		}

	//get our filter props
	//we use a function in the poodll poodllresourcelib, because
	//parsing will also need to be done by the html editor
	$filterprops=	\filter_poodll\poodlltools::fetch_filter_properties($link[0]);

	//if we have no props, quit
	if(empty($filterprops)){return "";}
	
	//if we want to ignore the filter (for "how to use a filter" demos) we let it go
	//to use this, make the last parameter of the filter passthrough=1
	if (!empty($filterprops['passthrough'])) return str_replace( ",passthrough=1","",$link[0]);
	
	//Init our return variable 
	$returnHtml ="";
	
	//Runtime JS or Flash
	if (empty($filterprops['runtime']))$filterprops['runtime'] ='auto'; 

	//depending on the type of filter
	switch ($filterprops['type']){
			
		case 'adminconsole':
			$returnHtml= \filter_poodll\poodlltools::fetch_poodllconsole($filterprops['runtime']);
			break;
	
			
		case 'audio':
			$returnHtml= \filter_poodll\poodlltools::fetchSimpleAudioPlayer($filterprops['runtime'],
			$filterprops['path'],!empty($filterprops['protocol']) ? $filterprops['protocol'] : 'rtmp',
				!empty($filterprops['width']) ? $filterprops['width'] : $CFG->filter_poodll_audiowidth,
				!empty($filterprops['height']) ? $filterprops['height'] :  $CFG->filter_poodll_audioheight,
				!empty($filterprops['embed']) ? $filterprops['embed']=='true' : false,
				!empty($filterprops['embedstring']) ? $filterprops['embedstring'] : 'Play',
				false,
				!empty($filterprops['usepoodlldata']) ? $filterprops['usepoodlldata']=='true' : false,
				!empty($filterprops['splashurl']) ? $filterprops['splashurl'] : '') ;
			break;
			
		case 'audiolist':
			$returnHtml= \filter_poodll\poodlltools::fetchAudioListPlayer($filterprops['runtime'],$filterprops['path'],
				!empty($filterprops['filearea']) ? $filterprops['filearea'] : 'content',
				!empty($filterprops['protocol']) ? $filterprops['protocol'] : 'rtmp',
				!empty($filterprops['width']) ? $filterprops['width'] : 400,
				!empty($filterprops['height']) ? $filterprops['height'] : 250,
				!empty($filterprops['sequentialplay']) ? $filterprops['sequentialplay'] : 'true',
				!empty($filterprops['player']) ? $filterprops['player'] : $CFG->filter_poodll_defaultplayer,
				!empty($filterprops['showplaylist']) ? $filterprops['showplaylist']=='true' : true,
				!empty($filterprops['usepoodlldata']) ? $filterprops['usepoodlldata']=='true' : false);
			break;
			
		case 'audiorecorder':
			$returnHtml= \filter_poodll\poodlltools::fetchSimpleAudioRecorder($filterprops['runtime'],
						!empty($filterprops['savefolder']) ? $filterprops['savefolder'] : '');
			break;	
			
		case 'audiotest':
			$returnHtml= \filter_poodll\poodlltools::fetchAudioTestPlayer($filterprops['runtime'],$filterprops['path'],
				!empty($filterprops['protocol']) ? $filterprops['protocol'] : 'rtmp',
				!empty($filterprops['width']) ? $filterprops['width'] : 400,
				!empty($filterprops['height']) ? $filterprops['height'] : 50,
				!empty($filterprops['filearea']) ? $filterprops['filearea'] : 'content',
				!empty($filterprops['usepoodlldata']) ? $filterprops['usepoodlldata']=='true' : false);
			break;	

			
		case 'bigvideogallery':
			$returnHtml= \filter_poodll\poodlltools::fetchBigVideoGallery($filterprops['runtime'],$filterprops['path'],
				!empty($filterprops['filearea']) ? $filterprops['filearea'] : 'content',
				!empty($filterprops['protocol']) ? $filterprops['protocol'] : 'http',
				!empty($filterprops['width']) ? $filterprops['width'] : $CFG->filter_poodll_biggallwidth,
				!empty($filterprops['height']) ? $filterprops['height'] :  $CFG->filter_poodll_biggallheight,
				!empty($filterprops['usepoodlldata']) ? $filterprops['usepoodlldata']=='true' : false);
			break;	
			

		case 'calculator':
			$returnHtml= \filter_poodll\poodlltools::fetch_poodllcalc($filterprops['runtime'],!empty($filterprops['width']) ? $filterprops['width'] : 300,
				!empty($filterprops['height']) ? $filterprops['height'] : 400,
				!empty($filterprops['size']) ? $filterprops['size'] : 'normal');
			break;


		case 'countdown':
			$returnHtml= \filter_poodll\poodlltools::fetch_countdowntimer($filterprops['runtime'],$filterprops['initseconds'],
				!empty($filterprops['usepresets']) ? $filterprops['usepresets'] : 'false',
				!empty($filterprops['width']) ? $filterprops['width'] : 400,
				!empty($filterprops['height']) ? $filterprops['height'] : 300,
				!empty($filterprops['fontheight']) ? $filterprops['fontheight'] : 64,
				!empty($filterprops['mode']) ? $filterprops['mode'] : 'normal',
				!empty($filterprops['permitfullscreen']) ? $filterprops['permitfullscreen'] : false, 
				!empty($filterprops['uniquename']) ? $filterprops['uniquename'] : 'auniquename');
			break;
		
		case 'counter':
			$returnHtml= \filter_poodll\poodlltools::fetch_counter($filterprops['runtime'],!empty($filterprops['initcount']) ? $filterprops['initcount']  : 0,
				!empty($filterprops['usepresets']) ? $filterprops['usepresets'] : 'false',
				!empty($filterprops['width']) ? $filterprops['width'] : 480,
				!empty($filterprops['height']) ? $filterprops['height'] : 265,
				!empty($filterprops['fontheight']) ? $filterprops['fontheight'] : 64,
				!empty($filterprops['permitfullscreen']) ? $filterprops['permitfullscreen'] : false );
			break;	
		
		case 'dice':
			$returnHtml= \filter_poodll\poodlltools::fetch_dice($filterprops['runtime'],!empty($filterprops['dicecount']) ? $filterprops['dicecount']  : 1,
				!empty($filterprops['dicesize']) ? $filterprops['dicesize'] : 200,
				!empty($filterprops['width']) ? $filterprops['width'] : 600,
				!empty($filterprops['height']) ? $filterprops['height'] : 300);
			break;
			

		case 'flashcards':
			$returnHtml= \filter_poodll\poodlltools::fetch_flashcards($filterprops['runtime'],
				!empty($filterprops['cardset']) ? $filterprops['cardset'] : -1,
				!empty($filterprops['qname']) ? $filterprops['qname'] : "",
				!empty($filterprops['frontcolor']) ? $filterprops['frontcolor'] : "0xDDDDDD",
				!empty($filterprops['backcolor']) ? $filterprops['backcolor'] : "0x000000",
				!empty($filterprops['cardwidth']) ? $filterprops['cardwidth'] : 300,
				!empty($filterprops['cardheight']) ? $filterprops['cardheight'] : 150,
				!empty($filterprops['randomize']) ? $filterprops['randomize'] : 'yes',
				!empty($filterprops['width']) ? $filterprops['width'] : 400,
				!empty($filterprops['height']) ? $filterprops['height'] : 300,
				!empty($filterprops['flashcardstype']) ? $filterprops['flashcardstype'] : $CFG->filter_poodll_flashcards_type);
			break;
			
		case 'miniplayer':
			$returnHtml= \filter_poodll\poodlltools::fetch_miniplayer($filterprops['runtime'],$filterprops['url'],
				!empty($filterprops['protocol']) ? $filterprops['protocol'] : 'http',
				!empty($filterprops['imageurl']) ? $filterprops['imageurl'] : '',
				!empty($filterprops['width']) ? $filterprops['width'] :  $CFG->filter_poodll_miniplayerwidth,
				!empty($filterprops['height']) ? $filterprops['height'] :  $CFG->filter_poodll_miniplayerwidth,
				!empty($filterprops['iframe']) ? $filterprops['iframe']=='true' :  false);
			break;
			
		case 'onceplayer':
			$returnHtml= \filter_poodll\poodlltools::fetch_onceplayer($filterprops['runtime'],$filterprops['url'],
				!empty($filterprops['protocol']) ? $filterprops['protocol'] : 'http',
				!empty($filterprops['width']) ? $filterprops['width'] :  0,
				!empty($filterprops['height']) ? $filterprops['height'] :  0,
				!empty($filterprops['iframe']) ? $filterprops['iframe']=='true' :  false);
			break;
			
		case 'newpoodllpairwork':
			$returnHtml= \filter_poodll\poodlltools::fetch_embeddablepairclient($filterprops['runtime'],
				!empty($filterprops['width']) ? $filterprops['width'] : $CFG->filter_poodll_newpairwidth,
				!empty($filterprops['height']) ? $filterprops['height'] : $CFG->filter_poodll_newpairheight,
				!empty($filterprops['chat']) ? $filterprops['chat'] : true,
				!empty($filterprops['whiteboard']) ? $filterprops['whiteboard'] : false, 
				!empty($filterprops['showvideo']) ? $filterprops['showvideo'] : false,
				!empty($filterprops['whiteboardback']) ? $filterprops['whiteboardback'] : ''
				);
			break;
			
		case 'stopwatch':
			$returnHtml= \filter_poodll\poodlltools::fetch_stopwatch($filterprops['runtime'],!empty($filterprops['width']) ? $filterprops['width'] : 400,
				!empty($filterprops['height']) ? $filterprops['height'] : 265,!empty($filterprops['fontheight']) ? $filterprops['fontheight'] : 64,
				!empty($filterprops['mode']) ? $filterprops['mode'] : 'normal',
				!empty($filterprops['permitfullscreen']) ? $filterprops['permitfullscreen'] : false, 
				!empty($filterprops['uniquename']) ? $filterprops['uniquename'] : 'auniquename');
			break;
						
		case 'smallvideogallery':
			$returnHtml= \filter_poodll\poodlltools::fetchSmallVideoGallery($filterprops['runtime'],$filterprops['path'],
				!empty($filterprops['filearea']) ? $filterprops['filearea'] : 'content',
				!empty($filterprops['protocol']) ? $filterprops['protocol'] : 'http',
				!empty($filterprops['width']) ? $filterprops['width'] : $CFG->filter_poodll_smallgallwidth,
				!empty($filterprops['height']) ? $filterprops['height'] :  $CFG->filter_poodll_smallgallheight,
				!empty($filterprops['permitfullscreen']) ? $filterprops['permitfullscreen'] : false,
				!empty($filterprops['usepoodlldata']) ? $filterprops['usepoodlldata']=='true' : false);
			break;

		case 'poodllpalette':
			$returnHtml= \filter_poodll\poodlltools::fetch_poodllpalette($filterprops['runtime'],
			$filterprops['width'],$filterprops['height'],"swf");
			break;	
			
		case 'wordplayer':
			$returnHtml= \filter_poodll\poodlltools::fetch_wordplayer($filterprops['runtime'],
				$filterprops['url'],$filterprops['word'],
				!empty($filterprops['fontsize']) ? $filterprops['fontsize'] : $CFG->filter_poodll_wordplayerfontsize,
				!empty($filterprops['protocol']) ? $filterprops['protocol'] : 'http',
				!empty($filterprops['width']) ? $filterprops['width'] :  "0",
				!empty($filterprops['height']) ? $filterprops['height'] :  "0",
				!empty($filterprops['iframe']) ? $filterprops['iframe']=='true' :  false);
			break;
			
		case 'whiteboard':
			$returnHtml= \filter_poodll\poodlltools::fetch_whiteboard($filterprops['runtime'],!empty($filterprops['boardname']) ? $filterprops['boardname'] : "whiteboard",
				!empty($filterprops['backimage']) ? $filterprops['backimage'] : "",
				(!empty($filterprops['slave'])&& $filterprops['slave']=='true') ? $filterprops['slave'] : false,
				!empty($filterprops['rooms']) ? $filterprops['rooms'] : "",
				!empty($filterprops['width']) ? $filterprops['width'] :  $CFG->filter_poodll_whiteboardwidth,
				!empty($filterprops['height']) ? $filterprops['height'] :  $CFG->filter_poodll_whiteboardheight,
				!empty($filterprops['mode']) ? $filterprops['mode'] :  'normal',
				(!empty($filterprops['standalone'])&& $filterprops['standalone']=='true')  ? $filterprops['standalone'] :  'false'
				);
			break;									

		case 'poodllpairwork':
			$courseid = $COURSE->id;
			$username = $USER->username;
			
			$poodllpairworkplayer ="";
			$studentalias="";
			$pairmap="";
			
			if ($pairmap = get_record("poodllpairwork_usermap", "username", $username, "course", $courseid)) {
				$studentalias = $pairmap->role;
			}				
					
			//if we have a role and hence a session.
			if ($studentalias != ""){			
				$me = get_record('user', 'username', $username);
				$partner = get_record('user', 'username', $pairmap->partnername);
				$partnerpic = \filter_poodll\poodlltools::fetch_user_picture($partner,35);
				$mepic = \filter_poodll\poodlltools::fetch_user_picture($me,35);
				$poodllpairworkplayer =  "<h4>" . get_string("yourpartneris", "poodllpairwork") . fullname($partner) . "</h4>";
				$poodllpairworkplayer .= \filter_poodll\poodlltools::fetchPairworkPlayer($pairmap->username,$pairmap->partnername,$mepic, fullname($me),$partnerpic,fullname($partner));
		
			}
			
			$returnHtml= $poodllpairworkplayer;
			break;
			
		case 'quizlet':
			$returnHtml= fetch_quizlet($filterprops['id'],
				!empty($filterprops['title']) ? $filterprops['title']  : 'quizlet',
				!empty($filterprops['mode']) ? $filterprops['mode'] :  'familiarize',
				!empty($filterprops['width']) ? $filterprops['width'] :  '100%',
				!empty($filterprops['height']) ? $filterprops['height'] :  '310')
				;
			break;	
			
		case 'scrollerstart':
			$returnHtml= \filter_poodll\poodlltools::fetch_poodllscroller(true,
				!empty($filterprops['width']) ? $filterprops['width'] :  '400',
				!empty($filterprops['height']) ? $filterprops['height'] :  '200',
				!empty($filterprops['speed']) ? $filterprops['speed'] :  '10',
				!empty($filterprops['repeat']) ? $filterprops['repeat'] :  'yes',
				!empty($filterprops['axis']) ? $filterprops['axis'] :  'y',
				!empty($filterprops['pixelshift']) ? $filterprops['pixelshift'] :  '2')
				;
			break;	
		
		case 'scrollerstop':
			$returnHtml= \filter_poodll\poodlltools::fetch_poodllscroller(false);

			break;

		
		case 'snapshot':
			$returnHtml= \filter_poodll\poodlltools::fetchSnapshotCamera(!empty($filterprops['updatecontrol']) ? $filterprops['updatecontrol'] :  'filename',
				!empty($filterprops['filename']) ? $filterprops['filename'] :  'filename',
				!empty($filterprops['width']) ? $filterprops['width'] :  '350',
				!empty($filterprops['height']) ? $filterprops['height'] :  '400')
				;
			break;	

			
		case 'videorecorder':
			$returnHtml= \filter_poodll\poodlltools::fetchSimpleVideoRecorder($filterprops['runtime'],
						!empty($filterprops['savefolder']) ? $filterprops['savefolder'] : '');
			break;	
			
		case 'video': 
			//$returnHtml= fetchSimpleVideoPlayer($filterprops['path'],$filterprops['width'],$filterprops['height']);
			$returnHtml= \filter_poodll\poodlltools::fetchSimpleVideoPlayer($filterprops['runtime'],$filterprops['path'],
			!empty($filterprops['width']) ? $filterprops['width'] : $CFG->filter_poodll_videowidth,
			!empty($filterprops['height']) ? $filterprops['height'] :  $CFG->filter_poodll_videoheight,
			!empty($filterprops['protocol']) ? $filterprops['protocol'] : 'rtmp',
			!empty($filterprops['embed']) ? $filterprops['embed']=='true' : false,
			!empty($filterprops['permitfullscreen']) ? $filterprops['permitfullscreen'] : false ,
			!empty($filterprops['embedstring']) ? $filterprops['embedstring'] : 'Play',
			!empty($filterprops['splashurl']) ? $filterprops['splashurl'] : '') ;
			break;


		default:

	
	}

	//return our html
	return $returnHtml;

}//end of poodll default callback function


/**
 * Replace pdl links with appropriate PoodLL widget
 *
 * @param  $link
 * @return string
 */
function filter_poodll_pdl_callback($link) {
global $CFG;

	$lm = new \filter_poodll\licensemanager();
	if(!$lm->validate_registrationkey($CFG->filter_poodll_registrationkey)) {
		return $lm->fetch_unregistered_content();
	}

	//strip the .pdl extension
	$len = strlen($link[2]);
	$trimpoint = strpos($link[2], ".pdl");
	$key=substr($link[2],0,$trimpoint);
	
	//see if there is a parameter to this widget
	$pos = strpos($key, "_");
	$param="";
	
	//if yes, trim it off the key and get its value
	if($pos){
		$param=substr($key,$pos+1);
		$key=substr($key,0,$pos);
	}

	//depending on the widget, make up a filter string
	switch ($key){
		case "audiorecorder": $fstring = "{POODLL:type=audiorecorder}";break;
		case "videorecorder": $fstring = "{POODLL:type=videorecorder}";break;
		case "snapshot": $fstring = "{POODLL:type=snapshot}";break;
		case "stopwatch": $fstring = "{POODLL:type=stopwatch}";break;
		case "dice": $fstring = "{POODLL:type=dice,dicecount=$param}";break;
		case "calculator": $fstring = "{POODLL:type=calculator}";break;
		case "countdown": $fstring = "{POODLL:type=countdown,initseconds=$param}";break;
		case "counter": $fstring = "{POODLL:type=counter}";break;
		case "whiteboardsimple": $fstring = "{POODLL:type=whiteboard,mode=simple,standalone=true}";break;
		case "whiteboardfull": $fstring = "{POODLL:type=whiteboard,mode=normal,standalone=true}";break;
		case "sliderocket": $fstring = "{POODLL:type=sliderocket,id=$param}";break;
		case "quizlet": $fstring = "{POODLL:type=quizlet,id=$param}";break;
		case "flashcards": $fstring = "{POODLL:type=flashcards,cardset=$param}";break;
	}
	
	//resolve the string and return it
	$returnHtml= filter_poodll_callback(array($fstring));	
	return $returnHtml;
}


/**
 * Replace mp3 links with player
 *
 * @param  $link
 * @return string
 */
function filter_poodll_mp3_callback($link) {
global $CFG;

	$lm = new \filter_poodll\licensemanager();
	if(!$lm->validate_registrationkey($CFG->filter_poodll_registrationkey)) {
		return $lm->fetch_unregistered_content();
	}

	//get the url and massage it a little
    $url = $link[1];
    $rawurl = str_replace('&amp;', '&', $url);
	
	//test for presence of player selectors and serve up the correct player
	$len = strlen($link[2]);
	if (strrpos($link[2],'.mini.mp3')=== $len-9){
		$returnHtml= \filter_poodll\poodlltools::fetch_miniplayer('auto',$rawurl,'http','',0,0,true);
		
	}else if (strrpos($link[2],'.once.mp3')=== $len-9){
		$returnHtml= \filter_poodll\poodlltools::fetch_onceplayer('auto',$rawurl,'http');
	
	}elseif(strrpos($link[2],'.word.mp3')=== $len-9){
		$word=substr($link[2],0,$len-9);
		$returnHtml=  \filter_poodll\poodlltools::fetch_wordplayer('auto',$rawurl,$word,0,'http',0,0,true);
	
	}elseif(strrpos($link[2],'.inlineword.mp3')=== $len-15){
		$word=substr($link[2],0,$len-15);
		$returnHtml=  \filter_poodll\poodlltools::fetch_wordplayer('js',$rawurl,$word,0,'http',0,0,true);
		
	}else{
		$returnHtml=  \filter_poodll\poodlltools::fetchSimpleAudioPlayer('auto',$rawurl,'http',$CFG->filter_poodll_audiowidth,$CFG->filter_poodll_audioheight,false,'Play');
	}
	
	return $returnHtml;
}

/**
 * Replace mp4 or flv links with player
 *
 * @param  $link
 * @return string
 */
function filter_poodll_mp4flv_callback($link) {
global $CFG;

	$lm = new \filter_poodll\licensemanager();
	if(!$lm->validate_registrationkey($CFG->filter_poodll_registrationkey)) {
		return $lm->fetch_unregistered_content();
	}

	//clean up url
	$url = $link[1];
    $url = str_replace('&amp;', '&', $url);
	$url = clean_param($url, PARAM_URL);
	
	//use default widths or explicit width/heights if they were passed in ie http://url.to.video.mp4?d=640x480
	if (empty($link[3]) or empty($link[4])) {
		$width = $CFG->filter_poodll_videowidth;
		$height = $CFG->filter_poodll_videoheight;
	}else{
		$width = $link[3];
		$height = $link[4];
	}
	
	//get the url and massage it a little
    $url = $link[1];
    $rawurl = str_replace('&amp;', '&', $url);
	
	//test for presence of player selectors and serve up the correct player
	//determine the file extension
	$ext = substr($link[5],-3); 
	$len = strlen($link[5]);
	if (strrpos($link[5],'.mini.' . $ext)=== $len-9){
		$returnHtml= \filter_poodll\poodlltools::fetch_miniplayer('auto',$rawurl,'http','',0,0,true);
	
	}else if (strrpos($link[2],'.once.' . $ext)=== $len-9){
		$returnHtml= \filter_poodll\poodlltools::fetch_onceplayer('auto',$rawurl,'http');
		
	}elseif(strrpos($link[5],'.word.' . $ext)=== $len-9){
		$word=substr($link[5],0,$len-9);
		$returnHtml= \filter_poodll\poodlltools::fetch_wordplayer('auto',$rawurl,$word,0,'http',0,0,true);
		
	}elseif(strrpos($link[5],'.audio.' . $ext)=== $len-10){
		$returnHtml=  \filter_poodll\poodlltools::fetchSimpleAudioPlayer('auto',$rawurl,'http',$CFG->filter_poodll_audiowidth,$CFG->filter_poodll_audioheight,false,'Play');
		
	}elseif(strrpos($link[5],'.inlineword.' . $ext)=== $len-15){
		$word=substr($link[5],0,$len-15);
		$returnHtml= \filter_poodll\poodlltools::fetch_wordplayer('js',$rawurl,$word,0,'http',0,0,true);
	
		
	}else{
		$returnHtml=  \filter_poodll\poodlltools::fetchSimpleVideoPlayer('auto',$url,$width,$height,'http',false,true , 'Play');
	}
	
	return $returnHtml;
}
