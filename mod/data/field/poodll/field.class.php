<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 1999-onwards Moodle Pty Ltd  http://moodle.com          //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   // //                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

require_once($CFG->dirroot.'/lib/filelib.php');
require_once($CFG->dirroot.'/repository/lib.php');

define('DBP_AUDIO',0);
define('DBP_VIDEO',1);
define('DBP_AUDIOMP3',2);
define('DBP_WHITEBOARDSIMPLE',3);
define('DBP_WHITEBOARDFULL',4);
define('DBP_SNAPSHOT',5);


class data_field_poodll extends data_field_base {

    var $type = 'poodll';

    /**
     * Returns options for embedded files
     *
     * @return array
     */
    private function get_options() {
    	//max bytes field
        if (!isset($this->field->param5)) {
            $this->field->param5 = 0;
        }
		//response type
        if (!isset($this->field->param4)) {
            $this->field->param4 = DBP_AUDIO;
        }

        $options = array();
       // $options['responsetype'] = $this->field->param4;
        $options['trusttext'] = false;
        $options['forcehttps'] = false;
        $options['subdirs'] = false;
        $options['maxfiles'] = -1;
        $options['context'] = $this->context;
        $options['maxbytes'] = $this->field->param5;
        $options['changeformat'] = 0;
        $options['noclean'] = false;
        return $options;
    }

    function display_add_field($recordid=0) {
        global $CFG, $DB, $OUTPUT, $PAGE, $USER;

        $text   = '';
        $format = 0;

        $str = '<div title="'.$this->field->description.'">';

        //editors_head_setup();
        $options = $this->get_options();
        $itemid = $this->field->id;
        $field = 'field_'.$itemid;
        $vectordata="";
		$draftitemid=0;
		$oldfilename="";
        
        if ($recordid && $content = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))){
            $format = $content->content1;
            if($content->content2){
            	$vectordata = $content->content2;
            }
          
			$draftitemid = file_get_submitted_draft_itemid('field_'. $this->field->id. '_itemid');
			$oldfilename = str_replace("@@PLUGINFILE@@/", '',$content->content);
            $text = clean_text($content->content, $format);
            $text = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_data', 'content', $content->id, $options, $text);
        } else {
            $draftitemid = file_get_unused_draft_itemid();
        }
	
		$updatecontrol = $field;
		$idcontrol = $field . '_itemid';
		$vectorcontrol = $field . '_vectorcontrol';
		
		$str .= '<input type="hidden" id="'. $updatecontrol .'" name="'. $updatecontrol .'" value="' . $oldfilename . '" />';
		$str .= "<input type='hidden' id='". $vectorcontrol ."' name='". $vectorcontrol ."' value='" . $vectordata . "' />";
        $str .= '<input type="hidden"  name="'. $idcontrol .'" value="'.$draftitemid.'" />';
        
       // $type = DBP_AUDIOMP3;
        $usercontextid=context_user::instance($USER->id)->id;
        switch ($this->field->param4){
        	case DBP_AUDIO:
        		$str .= \filter_poodll\poodlltools::fetchAudioRecorderForSubmission('auto','ignore',$updatecontrol,$usercontextid,"user","draft",$draftitemid);
        		break;
        	
        	case DBP_VIDEO:
        		$str .= \filter_poodll\poodlltools::fetchVideoRecorderForSubmission('auto','ignore',$updatecontrol,$usercontextid,"user","draft",$draftitemid);
        		break;
        	
        	case DBP_AUDIOMP3:
        		$str .= \filter_poodll\poodlltools::fetchMP3RecorderForSubmission($updatecontrol,$usercontextid,"user","draft",$draftitemid);
        		break;
        	
        	case DBP_WHITEBOARDSIMPLE:
        	case DBP_WHITEBOARDFULL:
				if(isset( $this->field->param6)){
					$backimageurl = $this->field->param6;
				}else{
					$backimageurl = "";
				}
        		$str .= \filter_poodll\poodlltools::fetchWhiteboardForSubmission($updatecontrol,$usercontextid,"user","draft",$draftitemid,0,0,$backimageurl,"",false, $vectorcontrol,$vectordata);
        		break;
        		
        	case DBP_SNAPSHOT:

        		$str .= \filter_poodll\poodlltools::fetchSnapshotCameraforSubmission($updatecontrol,'apic.jpg',350,400,$usercontextid,"user","draft",$draftitemid);
        		break;

		}
        
        return $str;
    }


	function get_file($recordid, $content=null) {
        global $DB;
        if (empty($content)) {
            if (!$content = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
                return null;
            }
        }
        $fs = get_file_storage();
		//remove @@pluginfile
		$filename = str_replace('@@PLUGINFILE@@/','',$content->content);
        if (!$file = $fs->get_file($this->context->id, 'mod_data', 'content', $content->id, '/', $filename)) {
            return null;
        }

        return $file;
    }
	
	
    function display_search_field($value = '') {
        return '<input type="text" size="16" name="f_'.$this->field->id.'" value="'.$value.'" />';
    }

    function parse_search_field() {
        return optional_param('f_'.$this->field->id, '', PARAM_NOTAGS);
    }

    function generate_sql($tablealias, $value) {
        global $DB;

        static $i=0;
        $i++;
        $name = "df_poodll_$i";
        return array(" ({$tablealias}.fieldid = {$this->field->id} AND ".$DB->sql_like("{$tablealias}.content", ":$name", false).") ", array($name=>"%$value%"));
    }

    function print_after_form() {
    }


    function update_content($recordid, $value, $name='') {
        global $DB;

        $content = new stdClass();
        $content->fieldid = $this->field->id;
        $content->recordid = $recordid;
        

        $names = explode('_', $name);
        if (!empty($names[2])) {
            if ($names[2] == 'itemid') {
                // the value will be retrieved by file_get_submitted_draft_itemid
                return true;
            }elseif($names[2] == 'vectorcontrol'){
            	 $content->content2 = $value;
            } else {
                $content->$names[2] = clean_param($value, PARAM_NOTAGS);  // content[1-4]
            }
        } else {
            $content->content = clean_param($value, PARAM_CLEAN);
        }

        if ($oldcontent = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
            $content->id = $oldcontent->id;
        } else {
            $content->id = $DB->insert_record('data_content', $content);
            if (!$content->id) {
                return false;
            }
        }
        if (!empty($content->content)) {
            $draftitemid = file_get_submitted_draft_itemid('field_'. $this->field->id. '_itemid');
            $options = $this->get_options();
            $content->content = file_save_draft_area_files($draftitemid, $this->context->id, 
					'mod_data', 'content', $content->id, $options, $content->content);
        	$content->content = "@@PLUGINFILE@@/" . $content->content;
        }
        $rv = $DB->update_record('data_content', $content);
        return $rv;
    }

    /**
     * Display the content of the field in browse mode
     *
     * @param int $recordid
     * @param object $template
     * @return bool|string
     */
    function display_browse_field($recordid, $template) {
        global $DB, $CFG;
        
        //We have quite the old javascript.php based "click to play"
		//This just make sure double sure doesn't get called.
		$embedstring = 'empty';
		$embed='false';


        if ($content = $DB->get_record('data_content', array('fieldid' => $this->field->id, 'recordid' => $recordid))) {
            if (isset($content->content)) {
                $options = new stdClass();
                if ($this->field->param1 == '1') {  // We are autolinking this field, so disable linking within us
                    $options->filter = false;
                }
                $options->para = false;
                $mediapath = file_rewrite_pluginfile_urls($content->content, 'pluginfile.php', 
					$this->context->id, 'mod_data', 'content', $content->id, $this->get_options());
               			
         switch ($this->field->param4){
        	case DBP_AUDIOMP3:
        	case DBP_AUDIO:
        	 	$str = format_text('{POODLL:type=audio,path='.	urlencode($mediapath) 
						.',protocol=http,embed=' . $embed . ',embedstring='. $embedstring .'}', FORMAT_HTML);
        		//this lower string though more efficient didn't load flowplayer embed js on time, so better to defer to the filter
        		//$str= fetchSimpleAudioPlayer('auto', $mediapath, "http",  $CFG->filter_poodll_audiowidth, 					           $CFG->filter_poodll_audioheight,$embed, $embedstring,false);
        		break;
        	
        	case DBP_VIDEO:
        		$str = format_text('{POODLL:type=video,path='.	urlencode($mediapath) 
						.',protocol=http,embed=' . $embed . ',embedstring='. $embedstring .'}', FORMAT_HTML);
				//this lower string though more efficient didn't load flowplayer embed js on time, so better to defer to the filter
				//$str .= fetchSimpleVideoPlayer('auto',$mediapath,$CFG->filter_poodll_videowidth,$CFG->filter_poodll_videoheight,'http',false,true,'Play');
				break;
				
        	case DBP_WHITEBOARDSIMPLE:
        	case DBP_WHITEBOARDFULL:
        		$str = "<img alt=\"submittedimage\" width=\"" . $CFG->filter_poodll_videowidth . "\"  src=\"" . $mediapath . "\" />";
        		break;
        		
        	case DBP_SNAPSHOT:
        		$str = "<img alt=\"submittedimage\" width=\"" . $CFG->filter_poodll_videowidth . "\" src=\"" . $mediapath . "\" />";
        		break;
			
			}
        
            } else {
                $str = '';
            }
            return $str;
        }
        return false;
    }

    /**
     * Whether this module support files
     *
     * @param string $relativepath
     * @return bool
     */
    function file_ok($relativepath) {
        return true;
    }
}

