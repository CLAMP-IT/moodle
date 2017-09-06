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

require_once($CFG->dirroot . '/filter/poodll/poodllfilelib.php');


/**
 *
 * This is a dataset manager for things like PoodLL flashcards
 *
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dataset_manager {
                                                                     

	static function cmpFilenames($a, $b)
	{
		return strcasecmp($a->get_filename(), $b->get_filename());
	}

	static function cmpDirnames($a, $b)
	{
		return strcasecmp(basename($a['dirfile']->get_filepath(),"/"), basename($b['dirfile']->get_filepath(),"/"));
	}
	function poodllizeCardFace($cardtext,$urlbase){
		//try to extract the url for any audio or images and replace the @@poodlldataimage@@ holder
			$startpos = strpos($cardtext,'@@poodlldataimage@@');
			$newtext=$cardtext;
			if($startpos!==false){
				 $url = str_replace( '@@poodlldataimage@@',$urlbase,strip_tags(substr($cardtext,$startpos)));
				 $starttext = substr($cardtext,0,$startpos);
				 $starttext = strip_tags($starttext); 
				$imagetext='<p><img src="' .$url. '" alt="" width="50" align="middle" /></p>';
				$newtext = $starttext . $imagetext;
			}	
			return $newtext;
	}


	
	//Fetch a deck of flashcards  
	function fetch_poodllflashcards($courseid, $cardsetid=-1,$cardsetname="",$frontcolor=-1, $backcolor=-1){
	global $CFG, $DB;	


		//Get question index from db if a question name was specified
		if($cardsetname != ""){
			$question = $DB->get_record('question', array('name'=>$cardsetname,'qtype'=>'match'));
			if($question){$cardsetid=$question->id;}
		}
		
		//get card data from db
		if($CFG->version < 2013051400){
			$subquestions = $DB->get_records('question_match_sub', array('question'=>$cardsetid));
		}else{
			$subquestions = $DB->get_records('qtype_match_subquestions', array('questionid'=>$cardsetid));
		}
		
		if (empty($subquestions)) {
		   // notice(get_string('nosubquestions', 'poodllflashcard'));
			return "nothing nothing nothing dang fang it";          
		}
		
		//We really need to put formatting into the filter string itself, not mix it in with the data.
		if($frontcolor==-1){$fgcolor='0xDDDDDD';}
		if($backcolor==-1){$bgcolor='0x000000';}
		
		//set up xml to return
		$xml_output = "<stack frontfgcolor='$frontcolor' frontbgcolor='0x0000FF' backfgcolor='$backcolor' backbgcolor='0xDDDDDD'>\n";
		
		//get our poodll data url base. If it starts with http it is assumed to be off site
		if(strpos('http',$CFG->filter_poodll_datadir)===0){
			$urlbase =  $CFG->filter_poodll_datadir;
		}else{
			$urlbase = $CFG->wwwroot . '/' . $CFG->filter_poodll_datadir;
		}
		
		//loop through card data amd make xml doc.
		//see for poodllflashcards freeplayview for extending this with media etc
		foreach ($subquestions as $card) {
			//try to extract the url for any audio or images and replace the @@poodlldataimage@@ holder

			$newtext= $this->poodllizeCardFace($card->questiontext,$urlbase);

			$qinnerheight=" ";
			if($newtext != $card->questiontext){
				$card->questiontext = $newtext;
				$qinnerheight=" innerheight=\"0.8\" ";
			}
			
			$newtext=$this->poodllizeCardFace($card->answertext,$urlbase);
			
			$ainnerheight=" ";
			if($newtext != $card->answertext){
				$card->answertext = $newtext;
				$ainnerheight=" innerheight=\"0.8\" ";
			}
			
			$xml_output .=  "\t<card>\n";
			$xml_output .=  "\t\t<background>0xCCCCCC</background>\n";
			$xml_output .=  "\t\t<front fontsize='18' type='text' " . $qinnerheight . "><![CDATA[" . $card->questiontext . "]]></front>\n";
			$xml_output .=  "\t\t<back fontsize='18' type='text' " . $ainnerheight . "><![CDATA[" . $card->answertext . "]]></back>\n";
			$xml_output .=  "\t</card>\n";
		}

		//close xml to return
		$xml_output .= "</stack>";

		//Return the data
		return $xml_output;


	}
}