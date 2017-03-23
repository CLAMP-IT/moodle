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

/**
 * Atto text editor integration version file.
 *
 * @package    atto_poodll
 * @copyright  2013 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Initialise this plugin
 * @param string $elementid
 */
function atto_poodll_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('insert',
                                          'cancel',
                                          'chooseinsert',
                                          'fieldsheader',
                                          'nofieldsheader',
                                          'dialogtitle',
                                          'audiored5_desc',
                                          'audiomp3_desc',
                                          'video_desc',
                                          'whiteboard_desc',
                                          'snapshot_desc',
                                          'widgets_desc'),
                                    'atto_poodll');
}

/**
 * Return the js params required for this module.
 * @return array of additional params to pass to javascript init function for this module.
 */
function atto_poodll_params_for_js($elementid, $options, $fpoptions) {
	global $USER, $COURSE;
	//coursecontext
	$coursecontext=context_course::instance($COURSE->id);	
	
	//usercontextid
	$usercontextid=context_user::instance($USER->id)->id;
	$disabled=false;
	
	//config our array of data
	$params = array();
	$params['usercontextid'] = $usercontextid;

		//If they don't have permission don't show it
		if(!has_capability('atto/poodll:visible', $coursecontext) ){
			$disabled=true;
		 }
		 
		 //if this textarea allows no files, we also bail
		 if (!isset($options['maxfiles']) || $options['maxfiles'] == 0) {
                $disabled=true;
        }
        
        //add our disabled param
        $params['disabled'] = $disabled;
        
        //add our use whiteboard option
        $params['usewhiteboard'] = get_config('atto_poodll','usewhiteboard');
	
		//add icons to editor if the permissions are all ok
		$recorders = array('audiomp3','audiored5','video','whiteboard','snapshot','widgets');
		$allowedrecorders =  get_config('atto_poodll','recorderstoshow');
		if(!empty($allowedrecorders)){
			$allowedrecorders = explode(',',$allowedrecorders);
			foreach($recorders as $recorder){
				if((array_search('show_' . $recorder,$allowedrecorders)!==false) && has_capability('atto/poodll:allow' . $recorder, $coursecontext)){
					$params[$recorder]=true;
				}
			}
		}
		
	$widgetparams = atto_poodll_widgets_params_for_js();
	$params['keys']=$widgetparams['keys'];
	$params['names']=$widgetparams['names'];
	$params['instructions']=$widgetparams['instructions'];
	$params['defaults']=$widgetparams['defaults'];
	$params['variables']=$widgetparams['variables'];
	$params['ends']=$widgetparams['ends'];

    return $params;
}

/**
 * Return the js params required for this module.
 * @return array of additional params to pass to javascript init function for this module.
 */
function atto_poodll_widgets_params_for_js()
{
	global $USER, $COURSE;

	//coursecontext
	$coursecontext = context_course::instance($COURSE->id);

	//generico specific
	$templates = get_object_vars(get_config('filter_poodll'));

	$instructions = array();
	$names=array();
	$keys = array();
	$variables = array();
	$defaults = array();
	$ends = array();

	//get the no. of templates
	if (!array_key_exists('templatecount', $templates)) {
		$templatecount = \filter_poodll\filtertools::FILTER_POODLL_TEMPLATE_COUNT +1;
	} else {
		$templatecount = $templates['templatecount'] + 1;
	}
	//put our template into a form thats easy to process in JS
	for ($tempindex = 1; $tempindex < $templatecount; $tempindex++) {
		if (empty($templates['template_' . $tempindex]) && 
			empty($templates['templatescript_' . $tempindex]) &&
			empty($templates['templatestyle_' . $tempindex])
			) {
			continue;
		}
		
		//make sure its to be shown in atto
		if(!$templates['template_showatto_' . $tempindex]){
			continue;
		}

		//stash the key and name for this template
		$keys[] = $templates['templatekey_' . $tempindex];
		$usename = trim($templates['templatename_' . $tempindex]);
		if($usename==''){
			$names[] = $templates['templatekey_' . $tempindex];
		}else{
			$names[]=$usename;
		}
		

		//instructions
		//stash the instructions for this template
		$instructions[] = rawurlencode($templates['templateinstructions_' . $tempindex]);

		//NB each of the $allvariables contains an array of variables (not a string)
		//there might be duplicates where the variable is used multiple times in a template
		//se we uniqu'ify it. That makes it look complicated. But we are just removing doubles
		$allvariables = atto_poodll_widgets_fetch_variables($templates['template_' . $tempindex] . $templates['templatescript_' . $tempindex] . $templates['datasetvars_' . $tempindex]);
		$uniquevariables = array_unique($allvariables);
		$usevariables = array();

		//we need to reallocate array keys if the array size was changed in unique'ifying it
		//we also take the opportunity to remove user variables, since they aren't needed here.
		//NB DATASET can be referred to without the :
		while (count($uniquevariables) > 0) {
			$tempvar = array_shift($uniquevariables);
			if (strpos($tempvar, 'COURSE:') === false
				&& strpos($tempvar, 'USER:') === false
				&& strpos($tempvar, 'DATASET:') === false
				&& strpos($tempvar, 'URLPARAM:') === false
				&& $tempvar != 'MOODLEPAGEID' 
				&& $tempvar != 'WWWROOT' 
				&& $tempvar != 'AUTOID') {
				$usevariables[] = $tempvar;
			}
		}
		$variables[] = $usevariables;

		//stash the defaults for this template
		//$defaults[] = $templates['templatedefaults_' . $tempindex];
		$defaults[] = atto_poodll_widgets_fetch_filter_properties($templates['templatedefaults_' . $tempindex]);

		$ends[] = $templates['templateend_' . $tempindex];
	}

	
	//config our array of data
	$params = array();
	$params['keys'] = $keys;
	$params['names'] = $names;
	$params['instructions'] = $instructions;
	$params['variables'] = $variables;
	$params['defaults'] = $defaults;
	$params['ends'] = $ends;


    return $params;
}

/**
 * Return an array of variable names
 * @param string template containing @@variable@@ variables 
 * @return array of variable names parsed from template string
 */
function atto_poodll_widgets_fetch_variables($template){
	$matches = array();
	$t = preg_match_all('/@@(.*?)@@/s', $template, $matches);
	if(count($matches)>1){
		return($matches[1]);
	}else{
		return array();
	}
}

function atto_poodll_widgets_fetch_filter_properties($propstring){
	//Now we just have our properties string
	//Lets run our regular expression over them
	//string should be property=value,property=value
	//got this regexp from http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs
	$regexpression='/([^=,]*)=("[^"]*"|[^,"]*)/';
	$matches=array();

	//here we match the filter string and split into name array (matches[1]) and value array (matches[2])
	//we then add those to a name value array.
	$itemprops = array();
	if (preg_match_all($regexpression, $propstring,$matches,PREG_PATTERN_ORDER)){		
		$propscount = count($matches[1]);
		for ($cnt =0; $cnt < $propscount; $cnt++){
			// echo $matches[1][$cnt] . "=" . $matches[2][$cnt] . " ";
			$newvalue = $matches[2][$cnt];
			//this could be done better, I am sure. WE are removing the quotes from start and end
			//this wil however remove multiple quotes id they exist at start and end. NG really
			$newvalue = trim($newvalue,'"');
			$itemprops[trim($matches[1][$cnt])]=$newvalue;
		}
	}
	return $itemprops;
}