<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace filter_poodll;

defined('MOODLE_INTERNAL') || die();


/**
 *
 * This is a class containing static functions for general PoodLL filter things
 * like embedding recorders and managing them
 *
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class filtertools
{

	const FILTER_POODLL_TEMPLATE_COUNT = 20;

	public static function fetch_template_indexes($conf){
		if($conf && array_key_exists('templatecount',$conf)){
			$templatecount = $conf['templatecount'];
		}else{
			$templatecount = self::FILTER_POODLL_TEMPLATE_COUNT;
		}
		$players = array();
		for ($i=1;$i<=$templatecount;$i++){
			$players[]=$i;
		}
		return $players;
	}


	public static function fetch_players_list($conf){

			//create player select list 

			//playeroptions may be fewer than the number of templates, hence  we check if playername
			//is default, and if we have a key to verify its a player or a blank template
			$playeroptions=array();

			if($conf && property_exists($conf,'templatecount')){
				$templatecount = $conf->templatecount;
			}else{
				$templatecount = self::FILTER_POODLL_TEMPLATE_COUNT;
			}
			for($templateid=1;$templateid<=$templatecount;$templateid++){
				//player name
				$defplayername = 'Player: ';
				$playername=$defplayername;
				$playerkey = false;
				 if($conf){ 
				 	 if(!property_exists($conf,'template_showplayers_' . $templateid) || 
				 	 		$conf->{'template_showplayers_' .$templateid}==0){
				 	 		continue;
				 	 }
				 	 
					 if(property_exists($conf,'templatename_' . $templateid)){
						$playername = $conf->{'templatename_' . $templateid};
						$playerkey = $conf->{'templatekey_' . $templateid};
						$playername = trim($playername);
						if(empty($playername)){$playername = $playerkey;}
					 }
				 }
				 //  a blank template will have no key, and maybe not a name
				 if($playername == $defplayername){$playername .= $templateid;}
				 if($playerkey){
					$playeroptions[$playerkey] = $playername;
				}
			}
			return $playeroptions;
	}


	/**
	 * Return an array of extensions we might handle
	 * @return array of variable names parsed from template string
	 */
	public static function fetch_extensions($adminconfig = false){
		if(!$adminconfig){
			$adminconfig = get_config('filter_poodll');
		}
		$default_extensions = self::fetch_default_extensions();
		$have_custom_extensions = $adminconfig && isset($adminconfig->extensions) && !empty($adminconfig->extensions);
		return $have_custom_extensions ? explode(',',$adminconfig->extensions) : $default_extensions;
	}	

	/**
	 * Return an array of extensions we might handle
	 * @return array of variable names parsed from template string
	 */
	public static function fetch_default_extensions(){
		return array('mp4','webm','ogg','ogv','flv','mp3','rss','youtube');
	}

	public static function fetch_emptyproparray(){
		$proparray=array();
		$proparray['AUTOID'] = '';
		$proparray['CSSLINK'] = '';
		return $proparray;
	}

	/**
	 * Return an array of variable names
	 * @param string template containing @@variable@@ variables 
	 * @return array of variable names parsed from template string
	 */
	public static function fetch_variables($template){
		$matches = array();
		$t = preg_match_all('/@@(.*?)@@/s', $template, $matches);
		if(count($matches)>1){
			return($matches[1]);
		}else{
			return array();
		}
	}

	public static function fetch_filter_properties_fromurl($link,$ext, $is_webservice){
		global $PAGE;
	
			$url = $link[1];
			$url = str_replace('&amp;', '&', $url);
			$rawurl = $url;
			$url = clean_param($url, PARAM_URL);
			$urlstub = substr($rawurl,0,strpos($rawurl,'.' . $ext));
			if($ext=="youtube"){
				$filename = $link[1];
				$url="https://www.youtube.com/watch?v=" . $filename;
				$videourl="https://www.youtube.com/watch?v=" . $filename;
				$autojpgfilename ="hqdefault.jpg";
				$autopngfilename ="hqdefault.png";
				$autoposterurljpg  ="http://img.youtube.com/vi/" . $filename ."/hqdefault.jpg";
				$autoposterurlpng  ="http://img.youtube.com/vi/" . $filename ."/hqdefault.png";
				$filetitle="";
				$title=$filename;
				$scheme='https:';
			}else{	
				//get the bits of the url
				$bits = parse_url($rawurl);
				if(!array_key_exists('scheme',$bits)){
					//add scheme to url if there was none
					if(strpos($PAGE->url->out(),'https:')===0){
						$scheme='https:';
					}else{
						$scheme='http:';
					}
				}else{
					$scheme = $bits['scheme'] . ':';
				}
	
				$filename = basename($bits['path']);
				$filetitle = str_replace('.' . $ext,'',$filename);
				$autopngfilename = str_replace('.' . $ext,'.png',$filename);
				$autojpgfilename = str_replace('.' . $ext,'.jpg',$filename);

				$videourl = $rawurl;
				$autoposterurljpg = $urlstub . '.jpg';
				$autoposterurlpng = $urlstub . '.png';
				$title = $link[4];
			}
		
			//init our prop array
			$proparray=array();
	
			//Add any params from url
			if(!empty($link[3])){
				//drop the first char if it is ?, whch it probably is
				$paramstring =  ltrim ($link[3], '?');
				$paramstring = str_replace('&amp;', '&', $paramstring);
				$params = array();
				parse_str($paramstring, $params);
				$proparray = array_merge($proparray,$params);
			}else{
				$paramstring="";
			}
	
			//use default widths or explicit width/heights if they were passed in ie http://url.to.video.mp4?d=640x480
			if(isset($proparray['d'])){
				$dimensions = explode('x',$proparray['d']);
				if(count($dimensions)==2){
					list($proparray['WIDTH'],$proparray['HEIGHT'])=$dimensions;
				}
			}

			//make up mime type
			switch ($ext){
				case 'mp3': $automime='audio/mpeg';break;
				case 'webm': $automime='video/webm';break;
				case 'ogg': $automime='video/ogg';break;	
				case 'mp4': 
				case 'youtube': 
				default:
					$automime='video/mp4';
			}
	
			$proparray['AUTOMIME'] = $automime;
			$proparray['URLSTUB'] = $urlstub;
			$proparray['FILENAME'] = $filename;
			$proparray['FILETITLE'] = $filetitle;
			$proparray['AUTOPNGFILENAME'] = $autopngfilename;
			$proparray['AUTOJPGFILENAME'] = $autojpgfilename;
			$proparray['RAWVIDEOURL'] =  !empty($paramstring) ?  $videourl . '?' . $paramstring : $videourl;
			if($is_webservice){
				$proparray['VIDEOURL'] = $videourl;
			}else{
				$proparray['VIDEOURL'] = $videourl . '?cachekiller=' . rand(1,9999) . time(); //add a time stamp to url
			}
			$proparray['RAWPARAMS'] = $paramstring;
			$proparray['AUTOPOSTERURLJPG'] = $autoposterurljpg;
			$proparray['AUTOPOSTERURLPNG'] = $autoposterurlpng;
			$proparray['TITLE'] = $title;
			$proparray['FILEEXT'] = $ext;
			//hardcoding for now, later we will do something better
            $proparray['AUDIOPLACEHOLDERDURATION'] = 6.138;
            $proparray['VIDEOPLACEHOLDERDURATION'] = 10.28644;
			return $proparray;
	}//end of function

	public static function fetch_filter_properties($filterstring){
        //lets do a general clean of all input here
        //see: https://github.com/justinhunt/moodle-filter_generico/issues/7
        $filterstring=clean_param($filterstring,PARAM_TEXT);

		//this just removes the {POODLL: .. } 
		$rawproperties = explode ("{POODLL:", $filterstring);
		$rawproperties = $rawproperties[1];
		$rawproperties = explode ("}", $rawproperties);

		//here we remove any html tags we find. They should not be in here
		//and we return the guts of the filter string for parsing
		$rawproperties = strip_tags($rawproperties[0]);

		//Now we just have our properties string
		//Lets run our regular expression over them
		//string should be property=value,property=value
		//got this regexp from http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs
		$regexpression='/([^=,]*)=("[^"]*"|[^,"]*)/';
		$matches=array();

		//here we match the filter string and split into name array (matches[1]) and value array (matches[2])
		//we then add those to a name value array.
		$itemprops = array();
		if (preg_match_all($regexpression, $rawproperties,$matches,PREG_PATTERN_ORDER)){		
			$propscount = count($matches[1]);
			for ($cnt =0; $cnt < $propscount; $cnt++){
				//echo $matches[1][$cnt] . "=" . $matches[2][$cnt] . " <br/>";
				$newvalue = $matches[2][$cnt];
				//this could be done better, I am sure. WE are removing the quotes from start and end
				//this wil however remove multiple quotes id they exisst at start and end. NG really
				$newvalue = trim($newvalue,'"');
				$itemprops[trim($matches[1][$cnt])]=$newvalue;
			}
		}
		return $itemprops;
	}//end of function

    /*
     * $filter = 'poodll'
     *
     */
    public static function fetch_local_filter_props($filter,$contextid){
	    global $CFG;
	    require_once ($CFG->libdir . '/filterlib.php');
        $props = filter_get_local_config($filter, $contextid);
        return $props;
    }

}//end of class
