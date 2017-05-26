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
 * This is a class for creating amd script for a poodll template
 *
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class templatescriptgenerator
{
/** @var mixed int index of template*/
    public $templateindex;
    
	 /**
     * Constructor
     */
    public function __construct($templateindex) {
        $this->templateindex = $templateindex;
    }
    

    public function get_template_script(){
    	global $CFG;
    	
    	$tindex = $this->templateindex;
		$conf = get_config('filter_poodll');
		$template=$conf->{'template_' . $tindex};

		//are we AMD and Moodle 2.9 or more?
		$require_amd = $conf->{'template_amd_' . $tindex};

		//get presets
		$thescript=$conf->{'templatescript_' . $tindex};
		$defaults=$conf->{'templatedefaults_' . $tindex};


		//fetch all the variables we use (make sure we have no duplicates)
		$allvariables = \filter_poodll\filtertools::fetch_variables($thescript. $template);
		$uniquevariables = array_unique($allvariables);

		//these props are in the opts array in the allopts[] array on the page
		//since we are writing the JS we write the opts['name'] into the js, but 
		//have to remove quotes from template eg "@@VAR@@" => opts['var'] //NB no quotes.
		//thats worth knowing for the admin who writed the JS load code for the template.
		foreach($uniquevariables as $propname){
			//case: single quotes
			$thescript = str_replace("'@@" . $propname ."@@'",'opts["' . $propname . '"]',$thescript);
			//case: double quotes
			$thescript = str_replace('"@@' . $propname .'@@"',"opts['" . $propname . "']",$thescript);
			//case: no quotes
			$thescript = str_replace('@@' . $propname .'@@',"opts['" . $propname . "']",$thescript);
		}

		if($require_amd){

			//figure out if this is https or http. We don't want to scare the browser
			$scheme='http:';
			if(strpos(strtolower($CFG->wwwroot),'https')===0){$scheme='https:';}


			//this is for loading as dependencies the uploaded or linked files
			//massage the js URL depending on schemes and rel. links etc. Then insert it
			$requiredjs = $conf->{'templaterequire_js_' . $tindex};
			$requiredjs = str_replace('@@WWWROOT@@', $CFG->wwwroot ,$requiredjs);
							
			if($requiredjs){
				if(strpos($requiredjs,'//')===0){
					$requiredjs = $scheme . $requiredjs;
				}elseif(strpos($requiredjs,'/')===0){
					$requiredjs = $CFG->wwwroot . $requiredjs;
				}
			}

			//current key
			$currentkey = $conf->{'templatekey_' . $tindex};
			
			//Do we need to shim here?
			$shim_export = trim($conf->{'templaterequire_js_shim_' . $tindex});  

			switch(empty($shim_export)){
			
				case true:
					//no shim so we just set an empty string
					$theshim='';
					
					//Create the dependency stuff in the output js
					$requires = array("'" . 'jquery' . "'", "'" . 'jqueryui' . "'");
					$params = array('$','jqui');
		
					if($requiredjs){
						$requires[] =  "'" . $requiredjs . "'";
						//$requires[] = "'recjs" . $tindex . "'";
						$params[] = "requiredjs_" . $currentkey;
					}
					break;
					
				case false:
					
					//remove .js from end of js filepath if its there
					if(strrpos($requiredjs,'.js')==(strlen($requiredjs) -3)){
						$requiredjs = substr($requiredjs, 0, -3);
					}
					
					$shimpath= $requiredjs;
					$shimexport=$shim_export;
					$shimkey = $currentkey . '-requiredjs'; 
					
					//build our dependencies and params
					$requires = array("'" . $currentkey . "-jquery','" . $shimkey . "'");
					$params=array('$',$shim_export);
					
					//build our shim script
					$theshim = $this->build_shim_function($currentkey, $shimkey, $shimpath, $shimexport);
			
			}

            //add our media refresher and logging
            $requires[]="'" . 'filter_poodll/media_refresher' . "'" ;
            $params[]=  'media_refresher';
            $requires[]="'" . 'core/log' . "'" ;
            $params[]=  'log';


			$thefunction = "define('filter_poodll_d" . $tindex . "',[" . implode(',',$requires) . "], function(" . implode(',',$params) . "){ ";
			$thefunction .= "return function(opts){" . $thescript. " \r\n}; });";

		//If not AMD
		}else{
                        //no shim so we just set an empty string
			$theshim='';
			$thefunction = "if(typeof filter_poodll_extfunctions == 'undefined'){filter_poodll_extfunctions={};}";
			$thefunction .= "filter_poodll_extfunctions['" . $tindex . "']= function(opts) {" . $thescript. " \r\n};";

		}
		$return_js = $theshim . $thefunction;
		return $return_js;
    }//end of function
	
	protected function build_shim_function($currentkey, $shimkey, $shimpath, $shimexport){
			global $CFG;
			
			$theshim="";			
			if(!empty($shimkey)){
				$theshimtemplate = "requirejs.config(@@THESHIMCONFIG@@);";
				$paths = new \stdClass();
				$shim = new \stdClass();
				
				//Add a path to  a separetely loaded jquery for shimmed libraries
				$jquery_shimconfig = new \stdClass();
				$jquery_shimconfig->exports = '$';
				$jquery_shimkey = $currentkey . '-jquery'; 
				$shim->{$jquery_shimkey}=$jquery_shimconfig;
				$paths->{$jquery_shimkey} = $CFG->wwwroot  . '/filter/poodll/3rdparty/jquery/jquery-1.12.4.min'; 
		
				//add a path for the required js ibrary
				$paths->{$shimkey} = $shimpath;
				$oneshimconfig = new \stdClass();
				$oneshimconfig->exports = $shimexport;
				$oneshimconfig->deps = array($jquery_shimkey);
				$shim->{$shimkey} = $oneshimconfig;
				
				
				//buuld the actual function that will set up our shim
				//we use php object -> json to kep it simple.
				//But its still not simple
				$theshimobject = new \stdClass();
				$theshimobject->paths=$paths;
				$theshimobject->shim =$shim;
				$theshimconfig=json_encode($theshimobject,JSON_UNESCAPED_SLASHES);
				$theshim = str_replace('@@THESHIMCONFIG@@', $theshimconfig,$theshimtemplate);
			}
		return $theshim;
	}

}//end of class