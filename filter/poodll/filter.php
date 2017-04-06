<?php
/*
 * __________________________________________________________________________
 *
 * PoodLL filter for Moodle 2.9 and above
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

class filter_poodll extends moodle_text_filter {

	protected $adminconfig=null;
	protected $courseconfig=null;

    /**
     * Apply the filter to the text
     *
     * @see filter_manager::apply_filter_chain()
     * @param string $text to be processed by the text
     * @param array $options filter options
     * @return string text after processing
     * @return string text after processing
     */
    public function filter($text, array $options = array()) {
			if (!is_string($text)) {
				// non string data can not be filtered anyway
				return $text;
			}
			$newtext = $text;
		
			//No links or poodll curlys then .. bail
			$havelinks = !(stripos($text, '</a>') ===false);
                        $have_poodll_curlys = (strpos($text,'{POODLL:')!==false);
			if(!$havelinks){		
				if(!$have_poodll_curlys){return $text;}
			}
			
			//get config
			 $this->adminconfig =get_config('filter_poodll');
			
			//if text has links, lets parse them
			if($havelinks){		
				//get handle extensions
				$exts = \filter_poodll\filtertools::fetch_extensions();
				$handleexts = array();
				foreach($exts as $ext){
					if($ext!='youtube' && $this->fetchconf('handle' . $ext)){
						$handleexts[] = $ext;
					}
				}
				//do all the non youtube extensions in one foul swoop
				if(!empty($handleexts)){
					$handleextstring = implode('|',$handleexts);
					//$oldsearch = '/<a\s[^>]*href="([^"#\?]+\.(' .  $handleextstring. '))(\?d=([\d]{1,4})x([\d]{1,4}))?"[^>]*>([^>]*)<\/a>/is';
					$search='/<a\s[^>]*href="([^"#\?]+\.(' .  $handleextstring. '))(.*?)"[^>]*>([^>]*)<\/a>/is';
					$newtext = preg_replace_callback($search, 'self::filter_poodll_allexts_callback', $newtext);
				}
                                
				//check for legacy pdl links
				$search='/<a\s[^>]*href="([^"#\?]+\.(pdl))(.*?)"[^>]*>([^>]*)<\/a>/is';
				$newtext = preg_replace_callback($search, 'self::filter_poodll_pdl_callback', $newtext);

			
			   //check for youtube
				if ($this->fetchconf('handleyoutube')) {
						 $search = '/<a\s[^>]*href="(?:https?:\/\/)?(?:www\.)?youtu(?:\.be|be\.com)\/(?:watch\?v=|v\/)?([\w-]{10,})(?:.*?)<\/a>/is';
						$newtext = preg_replace_callback($search, 'self::filter_poodll_youtube_callback', $newtext);
				}
			}// end of if $havelinks
			
			//if text has poodll curly brackets, lets parse
			if($have_poodll_curlys){
				//check for poodll curly brackets notation
				 $search = '/{POODLL:.*?}/is';
				 if (!is_string($text)) {
						// non string data can not be filtered anyway
						return $text;
				}
				$newtext = preg_replace_callback($search, 'self::filter_poodll_process', $newtext);
			}//end of if has poodl curlys
		
		//return the correct thing to wherever called us
		if (is_null($newtext) or $newtext === $text) {
			// error or not filtered
			return $text;
		}
		return $newtext;
    }
    
    private function fetchconf($prop){
    global $COURSE;

    	//I don't know why we need this whole courseconfig business.
    	//we are supposed to be able to just call $this->localconfig / $this->localconfig[$propertyname]
    	//as per here:https://docs.moodle.org/dev/Filters#Local_configuration , but its always empty
    	//at least at course context, in mod context it works ... 
    	//I just gave up and do it myself and stuff it in $this->courseconfig . bug?? Justin 20150106
    	if($this->localconfig && !empty($this->localconfig)){
    		$this->courseconfig = $this->localconfig;
    	}
    	if(!$this->courseconfig){
    		$this->courseconfig = filter_get_local_config('poodll', context_course::instance($COURSE->id)->id);
    	}
    	
		if($this->courseconfig && isset($this->courseconfig[$prop]) && $this->courseconfig[$prop] != 'sitedefault') {
			return $this->courseconfig[$prop];
		}else{
			return isset($this->adminconfig->{$prop}) ? $this->adminconfig->{$prop} : false;
		}
	}
    /**
	 * Replace youtube links with player
	 *
	 * @param  $link
	 * @return string
	 */
	private function filter_poodll_youtube_callback($link) {
		return $this->filter_poodll_process($link,'youtube');
	}
	
	
	/**
	 * Replace links with player/widget
	 *
	 * @param  $link
	 * @return string
	 */
	private function filter_poodll_allexts_callback($link) {
		return $this->filter_poodll_process($link,$link[2]);
	}
	
        /**
	 * Replace legacy pdl links with widget
	 *
	 * @param  $link
	 * @return string
	 */
        function filter_poodll_pdl_callback($link) {
                 global $CFG;

                //strip the .pdl extension
                $len = strlen($link[1]);
                $trimpoint = strpos($link[1], ".pdl");
                $key=substr($link[1],0,$trimpoint);
                if(strpos($key, "https://")===0  && $len > 12){
                	$key=substr($key,8);
                }elseif(strpos($key, "http://")===0 && $len > 11){
                	$key=substr($key,7);
                }
                $fstring='';

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
                        case "audiorecorder": $fstring = "{POODLL:type=audiorecorder}";break; //not implemented
                        case "videorecorder": $fstring = "{POODLL:type=videorecorder}";break; //not implemented
                        case "whiteboardsimple": $fstring = "{POODLL:type=whiteboard,mode=simple,standalone=true}";break;//not implemented
                        case "whiteboardfull": $fstring = "{POODLL:type=whiteboard,mode=normal,standalone=true}";break;//not implemented
                        case "snapshot": $fstring = "{POODLL:type=snapshot}";break;
                        case "stopwatch": $fstring = "{POODLL:type=stopwatch}";break;
                        case "dice": $fstring = "{POODLL:type=dice,dicecount=$param}";break;
                        case "calculator": $fstring = "{POODLL:type=calculator}";break;
                        case "countdown": $fstring = "{POODLL:type=countdown,initseconds=$param}";break;
                        case "counter": $fstring = "{POODLL:type=counter}";break;
                        case "flashcards": $fstring = "{POODLL:type=flashcards,cardset=$param}";break;
                }

                //resolve the string and return it
                if(!empty($fstring)){
                    return self::filter_poodll_process(array($fstring));
                }else{
                	return '';
                }
        }
	
	/*
	*	Main callback function , exists outside of class definition(because its a callback ...)
	*
	*/
	function filter_poodll_process(array $link, $ext =false){
		global $CFG, $COURSE, $USER, $PAGE, $DB;

		$lm = new \filter_poodll\licensemanager();
                $registration_status = $lm->validate_registrationkey($CFG->filter_poodll_registrationkey);
		if($registration_status != \filter_poodll\licensemanager::FILTER_POODLL_IS_REGISTERED){
			return $lm->fetch_unregistered_content($registration_status);
		}

		 $conf = get_object_vars(get_config('filter_poodll'));
		 
		 //we use this to see if its a web service calling this, 
		//in which case we return the alternate content
		$is_webservice = strpos($PAGE->url,$CFG->wwwroot .'/webservice/') === 0;
	
		//get our filter props
		if($ext){
			$filterprops= \filter_poodll\filtertools::fetch_filter_properties_fromurl($link,$ext, $is_webservice);
		}else{
			$filterprops= \filter_poodll\filtertools::fetch_filter_properties($link[0]);
		}

		//if we have no props, quit
		if(empty($filterprops)){return "";}
                
			//if this was a link that was filtered, and the content was not text , we don't like it
			//it most likely should be let alone or hidden as it is an icon to accompany the player
			//in teh case of attachements/resources/podcasts etc
		if($ext){
			if(empty($filterprops['TITLE'])){return $link[0];}
		}

		//if we want to ignore the filter (for "how to use poodll" or "cut and paste" this style use) we let it go
		//to use this, make the last parameter of the filter passthrough=1
		if (!empty($filterprops['passthrough'])) return str_replace( ",passthrough=1","",$link[0]);
	
		//set a default end tag of none
		$endtag=false;
            
		//determine which template we are using
		//If we have an extension then it is from link
		//get our template info
		if($ext){
			//in a special case it is possible for the player to be specified in the url params 
			//however usually it will be from preferences
			if(array_key_exists('player',$filterprops) && !empty($filterprops['player'])){
				$playerkey = $filterprops['player'];
			}else{
				$playerkey = $this->fetchconf('useplayer' . $ext);
			}
			$tempindex=0;
			$templatenumbers = \filter_poodll\filtertools::fetch_template_indexes($conf);
			foreach($templatenumbers as $templatenumber){
					if($conf['templatekey_' . $templatenumber]==$playerkey){
							$tempindex=$templatenumber;
							break;
					}
			}
			if(!$tempindex){return;}
		}else{
		//else its from a  poodll filter string                
			for($tempindex=1;$tempindex<=$conf['templatecount'];$tempindex++){
							if($filterprops['type']==$conf['templatekey_' . $tempindex]){
									break;
							}elseif($filterprops['type']==$conf['templatekey_' . $tempindex] . '_end'){
									$endtag = true;
									break;
							}
			}
			//no key could be found if got all the way to templatecount
			if($tempindex==$conf['templatecount']+1){return '';}
		}
                
		//fetch our template
		if($endtag){
			$poodlltemplate = $conf['templateend_' . $tempindex];
			$alternate_content = $conf['templatealternate_' . $tempindex];
		}else{
			$poodlltemplate = $conf['template_' . $tempindex];
			$alternate_content = $conf['templatealternate_end_' . $tempindex];
		}

		//fetch alternate content
		$alternate_content = $conf['templatealternate_' . $tempindex];


		//fetch dataset info
		$dataset_body = $conf['dataset_' . $tempindex];
		$dataset_vars = $conf['datasetvars_' . $tempindex];
	
		//js custom script
		//we really just want to be sure anything that appears in custom script
		//is stored in $filterprops and passed to js. we dont replace it server side because
		//of caching
		$js_custom_script = $conf['templatescript_' . $tempindex];
	
		//replace the specified names with spec values
		foreach($filterprops as $name=>$value){
			$poodlltemplate = str_replace('@@' . $name .'@@',$value,$poodlltemplate);
			$dataset_vars  = str_replace('@@' . $name .'@@',$value,$dataset_vars);
			$alternate_content = str_replace('@@' . $name .'@@',$value,$alternate_content);
		}
	
		//fetch defaults for this template
		$defaults = $conf['templatedefaults_'. $tempindex];
		if(!empty($defaults)){
			$defaults = "{POODLL:" . $defaults . "}";
			$defaultprops= \filter_poodll\filtertools::fetch_filter_properties($defaults);
			//replace our defaults, if not spec in the the filter string
			if(!empty($defaultprops)){
				foreach($defaultprops as $name=>$value){
					if(!array_key_exists($name,$filterprops)){
						//if we have options as defaults, lets just take the first one
						if(strpos($value,'|')!==false){
							$valuearray=explode('|',$value);
							$value = $valuearray[0];
						}
						$poodlltemplate = str_replace('@@' . $name .'@@',strip_tags($value),$poodlltemplate);
						$dataset_vars  = str_replace('@@' . $name .'@@',strip_tags($value),$dataset_vars);
						$alternate_content = str_replace('@@' . $name .'@@',strip_tags($value),$alternate_content);
						//stash for using in JS later
						$filterprops[$name]=$value;
					}
				}
			}
		}
	
		//If we have autoid lets deal with that
		$autoid = 'filterpoodll_' . time() . (string)rand(100,32767) ;
		$poodlltemplate = str_replace('@@AUTOID@@',$autoid,$poodlltemplate);
		$alternate_content = str_replace('@@AUTOID@@',$autoid,$alternate_content);
		//stash this for passing to js
		$filterprops['AUTOID']=$autoid;

		//If template requires a MOODLEPAGEID lets give them one
		//this is a bit redundant now it can be done now with @@URLPARAM:id@@ 
		$moodlepageid = optional_param('id',0,PARAM_INT);
		$poodlltemplate = str_replace('@@MOODLEPAGEID@@',$moodlepageid,$poodlltemplate);
		$dataset_vars  = str_replace('@@MOODLEPAGEID@@',$moodlepageid,$dataset_vars);
		$alternate_content = str_replace('@@MOODLEPAGEID@@',$moodlepageid,$alternate_content);
		//stash this for passing to js
		$filterprops['MOODLEPAGEID']=$moodlepageid;
	
		//we should stash our wwwroot too
		$poodlltemplate = str_replace('@@WWWROOT@@',$CFG->wwwroot,$poodlltemplate);
		$dataset_vars  = str_replace('@@WWWROOT@@',$CFG->wwwroot,$dataset_vars);
		$alternate_content = str_replace('@@WWWROOT@@',$CFG->wwwroot,$alternate_content);
		//actually this is available from JS anyway M.cfg.wwwroot . But lets make it easy for people
		$filterprops['WWWROOT']=$CFG->wwwroot;
		
		
		//if we have urlparam variables e.g @@URLPARAM:id@@
		if(strpos($poodlltemplate . ' ' . $dataset_vars . ' ' . $alternate_content . ' ' . $js_custom_script ,'@@URLPARAM:')!==false){
			$urlparamstubs = explode('@@URLPARAM:',$poodlltemplate);
			$dv_stubs = explode('@@URLPARAM:',$dataset_vars);
			
			if($dv_stubs){
				$urlparamstubs = array_merge($urlparamstubs,$dv_stubs);
			}
			$js_stubs = explode('@@URLPARAM:',$js_custom_script);
			if($js_stubs){
				$urlparamstubs = array_merge($urlparamstubs,$js_stubs);
			}
			$alt_stubs = explode('@@URLPARAM:',$alternate_content);
			if($alt_stubs){
				$urlparamstubs = array_merge($urlparamstubs,$alt_stubs);
			}
			
			//URL Param Props
			$count=0;
			foreach($urlparamstubs as $propstub){
				//we don't want the first one, its junk
				$count++;
				if($count==1){continue;}
				//init our prop value
				$propvalue=false;
				
				//fetch the property name
				//user can use any case, but we work with lower case version
				$end = strpos($propstub,'@@');
				$urlprop = substr($propstub,0,$end);
				if(empty($urlprop)){continue;}
				
				//check if it exists in the params to the url and if so, set it.
				$propvalue = optional_param($urlprop,'',PARAM_TEXT);
				$poodlltemplate = str_replace('@@URLPARAM:' . $urlprop .'@@',$propvalue,$poodlltemplate);
				$dataset_vars  = str_replace('@@URLPARAM:' . $urlprop .'@@',$propvalue,$dataset_vars);
				$alternate_content = str_replace('@@URLPARAM:' . $urlprop .'@@',$propvalue,$alternate_content);
				//stash this for passing to js
				$filterprops['URLPARAM:' . $urlprop]=$propvalue;				
			}//end of for each
		}//end of if we have@@URLPARAM
	
	
		//if we have course variables e.g @@COURSE:ID@@
		if(strpos($poodlltemplate . ' ' . $dataset_vars . ' ' . $alternate_content . ' ' . $js_custom_script ,'@@COURSE:')!==false){
				$coursevars = get_object_vars($COURSE);
				$coursepropstubs = explode('@@COURSE:',$poodlltemplate);
				$d_stubs = explode('@@COURSE:',$dataset_vars);
				if($d_stubs){
					$coursepropstubs = array_merge($coursepropstubs,$d_stubs);
				}
				$j_stubs=explode('@@COURSE:',$js_custom_script);
				if($j_stubs){
					$coursepropstubs = array_merge($coursepropstubs,$j_stubs);
				}
				$alt_stubs=explode('@@COURSE:',$alternate_content);
				if($alt_stubs){
					$coursepropstubs = array_merge($coursepropstubs,$alt_stubs);
				}


			//Course Props
				$profileprops=false;
				$count=0;
				foreach($coursepropstubs as $propstub){
					//we don't want the first one, its junk
					$count++;
					if($count==1){continue;}
					//init our prop value
					$propvalue=false;
				
					//fetch the property name
					//user can use any case, but we work with lower case version
					$end = strpos($propstub,'@@');
					$courseprop_allcase = substr($propstub,0,$end);
					$courseprop=strtolower($courseprop_allcase);
				
					//check if it exists in course
					if(array_key_exists($courseprop,$coursevars)){
						$propvalue=$coursevars[$courseprop];
					}elseif($courseprop=='contextid'){
						$context = context_course::instance($COURSE->id);
						if($context){
							$propvalue=$context->id;
						}
					}
					//if we have a propname and a propvalue, do the replace
					if(!empty($courseprop) && !empty($propvalue)){
						$poodlltemplate = str_replace('@@COURSE:' . $courseprop_allcase .'@@',$propvalue,$poodlltemplate);
						$dataset_vars  = str_replace('@@COURSE:' . $courseprop_allcase .'@@',$propvalue,$dataset_vars);
						$alternate_content = str_replace('@@COURSE:' . $courseprop_allcase .'@@',$propvalue,$alternate_content);
						//stash this for passing to js
						$filterprops['COURSE:' . $courseprop_allcase]=$propvalue;
					}
				}
		}//end of if @@COURSE

		//if we have user variables e.g @@USER:FIRSTNAME@@
		//It is a bit wordy, because trying to avoid loading a lib
		//or making a DB call if unneccessary
		if(strpos($poodlltemplate . ' ' . $dataset_vars . ' ' . $js_custom_script . ' ' . $alternate_content ,'@@USER:')!==false){
			$uservars = get_object_vars($USER);
			$userpropstubs = explode('@@USER:',$poodlltemplate);
			$d_stubs = explode('@@USER:',$dataset_vars);
			if($d_stubs){
				$userpropstubs = array_merge($userpropstubs,$d_stubs);
			}
			$j_stubs = explode('@@USER:',$js_custom_script);
			if($j_stubs){
				$userpropstubs = array_merge($userpropstubs,$j_stubs);
			}
			$alt_stubs = explode('@@USER:',$alternate_content);
			if($alt_stubs){
				$userpropstubs = array_merge($userpropstubs,$alt_stubs);
			}
		
			//User Props
			$profileprops=false;
			$count=0;
			foreach($userpropstubs as $propstub){
				//we don't want the first one, its junk
				$count++;
				if($count==1){continue;}
				//init our prop value
				$propvalue=false;
			
				//fetch the property name
				//user can use any case, but we work with lower case version
				$end = strpos($propstub,'@@');
				$userprop_allcase = substr($propstub,0,$end);
				$userprop=strtolower($userprop_allcase);
			
				//check if it exists in user, else look for it in profile fields
				if(array_key_exists($userprop,$uservars)){
					$propvalue=$uservars[$userprop];
				}else{
					if(!$profileprops){
						require_once("$CFG->dirroot/user/profile/lib.php");
						$profileprops = get_object_vars(profile_user_record($USER->id));
					}
					if($profileprops && array_key_exists($userprop,$profileprops)){
						$propvalue=$profileprops[$userprop];
					}else{
						switch($userprop){
							case 'picurl':
								require_once("$CFG->libdir/outputcomponents.php");
								global $PAGE;
								$user_picture=new user_picture($USER);
								$propvalue = $user_picture->get_url($PAGE);
								break;
							
							case 'pic':
								global $OUTPUT;
								$propvalue = $OUTPUT->user_picture($USER, array('popup'=>true));
								break;
						}
					}
				}
			
				//if we have a propname and a propvalue, do the replace
				if(!empty($userprop) && !empty($propvalue)){
					//echo "userprop:" . $userprop . '<br/>propvalue:' . $propvalue;
					$poodlltemplate = str_replace('@@USER:' . $userprop_allcase .'@@',$propvalue,$poodlltemplate);
					$dataset_vars  = str_replace('@@USER:' . $userprop_allcase .'@@',$propvalue,$dataset_vars);
					$alternate_content  = str_replace('@@USER:' . $userprop_allcase .'@@',$propvalue,$alternate_content);
					//stash this for passing to js
					$filterprops['USER:' . $userprop_allcase]=$propvalue;
				}
			}
		}//end of of we @@USER

		//if we have a dataset body
		//we split the $data_vars string passed in by user (which should have had all the replacing done)
		//into the vars array. This is passed to get_records_sql and the returned result is stored
		//in filter props. If its a single record, its available to the body area.
		//otherwise it needs to be accessewd from javascript in the DATASET variable
		$filterprops['DATASET']=false;
		if($dataset_body){
			$vars = array();
			if($dataset_vars){
				$vars=explode(',',$dataset_vars);
			}
			//turn numeric vars into numbers (not strings)
			$query_vars = array();
			for($i = 0; $i < sizeof($vars); $i++) {
				if(is_numeric($vars[$i])) {
					$query_vars[] = intval($vars[$i]);
				} else {
					$query_vars[] = $vars[$i];
				}
			}
			try {
				$alldata = $DB->get_records_sql($dataset_body, $query_vars);
				if($alldata) {
					$filterprops['DATASET'] = $alldata;
					//replace the specified names with spec values, if its a one element array
					if (sizeof($filterprops['DATASET']) == 1) {
						$thedata = get_object_vars(array_pop($alldata));
						foreach ($thedata as $name => $value) {
							$poodlltemplate = str_replace('@@DATASET:' . $name . '@@', $value, $poodlltemplate);
							$alternate_content = str_replace('@@DATASET:' . $name . '@@', $value, $alternate_content);
						}
					}
				}
			}catch(Exception $e){
				//do nothing;
			}
		}//end of if dataset
	
	    
	    //if this is a web service/mobile app we just return the alternate content
	   	if($is_webservice && !empty($alternate_content)){
			return $alternate_content;		
		}
		
		//If this is the end tag we don't need to subsequent CSS and JS stuff. We already did it.
		if($endtag){
			return $poodlltemplate;
		}
	
		//get the conf info we need for this template
		$thescript = $conf['templatescript_' . $tempindex];
		$defaults=$conf['templatedefaults_' . $tempindex];
		$require_js = $conf['templaterequire_js_' . $tempindex];
		$require_css = $conf['templaterequire_css_' . $tempindex];
		$require_css = str_replace('@@WWWROOT@@', $CFG->wwwroot ,$require_css);
		$require_js = str_replace('@@WWWROOT@@', $CFG->wwwroot ,$require_js);
		
		//are we AMD and Moodle 2.9 or more?
		$require_amd = $conf['template_amd_' . $tempindex];
	
		//figure out if this is https or http. We don't want to scare the browser
		if(strpos($PAGE->url->out(),'https:')===0){
			$scheme='https:';
		}else{
			$scheme='http:';
		}
	
	
		//massage the js URL depending on schemes and rel. links etc. Then insert it
		//with AMD we set these as dependencies, so we don't need this song and dance
		if(!$require_amd){
			$filterprops['JSLINK']=false;
			if($require_js){
				if(strpos($require_js,'//')===0){
					$require_js = $scheme . $require_js;
				}elseif(strpos($require_js,'/')===0){
					$require_js = $CFG->wwwroot . $require_js;
				}
			
				//for load method: NO AMD
				$PAGE->requires->js(new moodle_url($require_js));
		
				//for load method: AMD
				//$require_js = substr($require_js, 0, -3);
				$filterprops['JSLINK'] = $require_js;
			}

		}

		//massage the CSS URL depending on schemes and rel. links etc. 
		if(!empty($require_css)){
			if(strpos($require_css,'//')===0){
				$require_css = $scheme . $require_css;
			}elseif(strpos($require_css,'/')===0){
				$require_css = $CFG->wwwroot . $require_css;
			}
		}


        //set up our revision flag for forcing cache refreshes etc
        if (!empty($conf['revision'])) {
            $revision = $conf['revision'];
        } else {
            $revision = '0';
        }

		//if not too late: load css in header
		// if too late: inject it there via JS
		$filterprops['CSSLINK']=false;
		$filterprops['CSSCUSTOM']=false;
	
		//require any scripts from the template
		$customcssurl=false;
		if($conf['templatestyle_' . $tempindex]){
			$customcssurl =new moodle_url( '/filter/poodll/templatecss.php',array('t'=>$tempindex,'rev'=>$revision));
		}
	
		if(!$PAGE->headerprinted && !$PAGE->requires->is_head_done()){
			if($require_css){
				$PAGE->requires->css( new moodle_url($require_css));
			}
			if($customcssurl){
				$PAGE->requires->css($customcssurl);
			}
		}else{
			if($require_css){
				$filterprops['CSSLINK']=$require_css;
			}
			if($customcssurl){
				$filterprops['CSSCUSTOM']=$customcssurl->out();
			}
		
		}
	
	
		//Tell javascript which template this is
		$filterprops['TEMPLATEID'] = $tempindex;

		
		$jsmodule = array(
				'name'     => 'filter_poodll',
				'fullpath' => '/filter/poodll/module.js',
				'requires' => array('json')
			);
		

		//AMD or not, and then load our js for this template on the page
		if($require_amd){
			$generator = new \filter_poodll\templatescriptgenerator($tempindex);
			$template_amd_script = $generator->get_template_script();

			//props can't be passed at much length , Moodle complains about too many
			//so we do this ... lets hope it don't break things
			$jsonstring = json_encode($filterprops);
			$props_html = \html_writer::tag('input', '', array('id' => 'filter_poodll_amdopts_' . $filterprops['AUTOID'], 'type' => 'hidden', 'value' => $jsonstring));
			$poodlltemplate = $props_html . $poodlltemplate;
		
			//load define for this template. Later it will be called from loadtemplate
			$PAGE->requires->js_amd_inline($template_amd_script);
			//for AMD template script
			$PAGE->requires->js_call_amd('filter_poodll/template_amd','loadtemplate', array(array('AUTOID'=>$filterprops['AUTOID'])));
			//echo $filterprops['AUTOID'] . PHP_EOL;


		}else{

			//require any scripts from the template
            $customjsurl= new moodle_url('/filter/poodll/templatejs.php',array('t'=>$tempindex,'rev'=>$revision));
			$PAGE->requires->js($customjsurl);
	
			//for no AMD
			$PAGE->requires->js_init_call('M.filter_poodll_templates.loadtemplate', array($filterprops),false,$jsmodule);
		}
	
		//finally return our template text	
		return $poodlltemplate;
	}//end of function

}//end of class