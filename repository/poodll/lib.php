<?php // $Id$

/**
 * repository_poodll
 * Moodle user can record/play poodll audio/video items
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

//added for moodle 2
require_once($CFG->libdir . '/filelib.php');
 
class repository_poodll extends repository {


	//here we add some constants to keep it readable
	const POODLLAUDIO = 0;
	const POODLLVIDEO = 1;
	const POODLLSNAPSHOT = 2;
	const MP3AUDIO = 3;
	const POODLLWIDGET = 4;
	const POODLLWHITEBOARD = 5;
	



    /*
     * Begin of File picker API implementation
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        global $CFG, $PAGE;
        
        parent::__construct ($repositoryid, $context, $options);
     
    }
    
    public static function get_instance_option_names() {
    	return array('recording_format','hide_player_opts');
    }
    
    //2.3 requires static, 2.2 non static, what to do? Justin 20120616 
    // created a 2.3 repo Justin 20120621
    public static function instance_config_form($mform) {
        $recording_format_options = array(
        	get_string('audio', 'repository_poodll'),
        	get_string('video', 'repository_poodll'),
			get_string('snapshot', 'repository_poodll'),
			get_string('mp3recorder', 'repository_poodll'),
			get_string('widget', 'repository_poodll'),
			get_string('whiteboard', 'repository_poodll')
        );
        
        $mform->addElement('select', 'recording_format', get_string('recording_format', 'repository_poodll'), $recording_format_options);  
        $mform->addRule('recording_format', get_string('required'), 'required', null, 'client');
		/* $mform->addElement('checkbox', 'hide_player_opts', 
			get_string('hide_player_opts', 'repository_poodll'),
			get_string('hide_player_opts_details', 'repository_poodll'));
			*/
		$hide_player_opts_options = array(
        	get_string('hide_player_opts_show', 'repository_poodll'),
        	get_string('hide_player_opts_hide', 'repository_poodll'));
		 $mform->addElement('select', 'hide_player_opts', get_string('hide_player_opts', 'repository_poodll'), $hide_player_opts_options);
		//$mform->setDefault('hide_player_opts', 0);
		$mform->disabledIf('hide_player_opts', 'recording_format', 'eq', self::POODLLVIDEO);
		$mform->disabledIf('hide_player_opts', 'recording_format', 'eq', self::POODLLSNAPSHOT);
		$mform->disabledIf('hide_player_opts', 'recording_format', 'eq', self::POODLLWIDGET);
		$mform->disabledIf('hide_player_opts', 'recording_format', 'eq', self::POODLLWHITEBOARD);

    }

	//login overrride start
	//*****************************************************************
	//
     // Generate search form
     //
    public function print_login($ajax = true) {
        global $CFG,$PAGE,$USER;

        //Init our array
        $ret = array();

		
        //If we are selecting PoodLL Widgets, we don't need to show a login/search screen
        //just list the widgets
        if ($this->options['recording_format'] == self::POODLLWIDGET){
                $ret = array();
                $ret['dynload'] = true;
                $ret['nosearch'] = true;
                $ret['nologin'] = true;
                $ret['list'] = $this->fetch_poodllwidgets();
                return $ret;

        }	

        //If we are using an iframe based repo
        $search = new stdClass();
        $search->type = 'hidden';
        $search->id   = 'filename' . '_' . $this->options['recording_format'] ;
        $search->name = 's';
       // $search->value = 'winkle.mp3';
        
        //setdefault iframe height, which in ja will be adjusted
        $height=150;
        $width=310;

        $iframeid = html_writer::random_id('repository_poodll');
	$search->label = "<iframe id=\"$iframeid\" scrolling=\"no\" frameBorder=\"0\" src=\"{$CFG->wwwroot}/repository/poodll/recorder.php?repo_id={$this->id}&iframe_id=$iframeid\" height=\"". $height ."\" width=\"" . $width . "\"></iframe>";
//when called from here it did not get run ...? so call from iframe       
// $PAGE->requires->js_call_amd("filter_poodll/responsiveiframe", 'init', array(array('iframeid' => $iframeid)));
        
	$sort = new stdClass();
        $sort->type = 'hidden';
        $sort->options = array();
        $sort->id = 'poodll_sort';
        $sort->name = 'poodll_sort';
        $sort->label = '';

        $ret['login'] = array($search, $sort);
        $ret['login_btn_label'] = 'Next >>>';
        $ret['login_btn_action'] = 'search';
	
        return $ret;

    }
    

    public function check_login() {
        return !empty($this->keyword);
    }

		
	  
     // Method to get the repository content.
     //
     // @param string $path current path in the repository
     // @param string $page current page in the repository path
     // @return array structure of listing information
     //
    public function get_listing($path='', $page='') {
			return array();
		
   }
   
   ///
     // Return search results
     // @param string $search_text
     // @return array
     //
     //added $page=0 param for 2.3 compat justin 20120524
    public function search($filename, $page=0) {
        $this->keyword = $filename;
        $ret  = array();
        $ret['nologin'] = true;
		$ret['nosearch'] = true;
        $ret['norefresh'] = true;
        //echo $filename;
		$ret['list'] = $this->fetch_filelist($filename);
		
        return $ret;
    }
	
	    /**
     * Private method to fetch details on our recorded file,
	 * and filter options
     * @param string $keyword
     * @param int $start
     * @param int $max max results
     * @param string $sort
     * @return array
     */
    private function fetch_filelist($filename) {
		global $CFG,$USER;
	
		$hideoptions=false;
		if(!empty($this->options['hide_player_opts'])){
			$hideoptions=$this->options['hide_player_opts'];
		}
	
	
        $list = array();
		
		//if user did not record anything, or the recording copy failed can out sadly.
		if(!$filename){return $list;}
		//if(!$filename){$filename="houses.jpg";}
		
		//determine the file extension
		$ext = substr($filename,-4); 
		
		//determine the download source
		switch($this->options['recording_format']){
			case self::POODLLAUDIO:
			case self::POODLLVIDEO:
			case self::POODLLSNAPSHOT:	
			case self::MP3AUDIO:
			case self::POODLLWHITEBOARD:
	
				$urltofile = moodle_url::make_draftfile_url("0", "/", $filename)->out(false);
				$source=$urltofile;
		
		}
        
		//determine the player options
		switch($this->options['recording_format']){
			case self::POODLLAUDIO:
			case self::MP3AUDIO:
				
					//normal player
					if($ext==".mp3"){
						$list[] = array(
							'title'=> $filename,
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/audionormal.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>$source
						);
					
					}else{
						$list[] = array(
							'title'=> substr_replace($filename,'.audio' . $ext,-4),
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/audionormal.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>$source
						);
					}
				
				if(!$hideoptions){
					$list[] = array(
							'title'=> substr_replace($filename,'.mini'. $ext,-4),
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/miniplayer.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>$source
						);
					
					$list[] = array(
							'title'=> substr_replace($filename,'.word'. $ext,-4),
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/wordplayer.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>$source
						);
						
					$list[] = array(
							'title'=> substr_replace($filename,'.inlineword'. $ext,-4),
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/inlinewordplayer.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>$source
						);
					
					$list[] = array(
							'title'=> substr_replace($filename,'.once'. $ext,-4),
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/onceplayer.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>$source
						);
				}
				break;
		default:
				
			 $list[] = array(
                'title'=>$filename,
                'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/bigicon.png",
                'thumbnail_width'=>330,
                'thumbnail_height'=>115,
                'size'=>'',
                'date'=>'',
                'source'=>$source
            );
		
		}
           
       //return the list of files/player options
        return $list;
		
    }
	
	   /**
     * Download a file, this function can be overridden by subclass. {@link curl}
     *
     * @param string $url the url of file
     * @param string $filename save location
     * @return array with elements:
     *   path: internal location of the file
     *   url: URL to the source (from parameters)
     */
    public function get_file($url, $filename = '') {
        global $CFG,$USER;
			//get the filename as used by our recorder
			$recordedname = basename($url);
			
			//get a temporary download path
			$path = $this->prepare_file($filename);

			//fetch the file we submitted earlier
		   $fs = get_file_storage();
		   $context = context_user::instance($USER->id);
			$f = $fs->get_file($context->id, "user", "draft",
				"0", "/", $recordedname);
		
			//write the file out to the temporary location
			$fhandle = fopen($path, 'w');
			$data = $f->get_content();
			$result= fwrite($fhandle,$data);

			// Close file handler.
			fclose($fhandle);
			
			//bail if we errored out
			if ($result===false) {
				unlink($path);
				return null;
			}else{
				//clear up the original file which we no longer need
				self::delete_tempfile_from_draft("0", "/", $recordedname); 
			}
		
		//return to Moodle what it needs to know
		return array('path'=>$path, 'url'=>$url);
    }
	
	/**
     *	Return an array of widget selectors, to be displayed in search results screen 
     * @return array
     */
	private function fetch_poodllwidgets(){
	global $CFG;
					
					$list = array();
	
						//stopwatch
						$list[] = array(
							'title'=> "stopwatch.pdl.mp4",
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/repostopwatch.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>'stopwatch.pdl'
						);
						//calculator
						$list[] = array(
							'title'=> "calculator.pdl.mp4",
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/repocalculator.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>'calculator.pdl'
						);
						//countdown timer
						$list[] = array(
							'title'=> "countdown_60.pdl.mp4",
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/repocountdown.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>'countdown_60.pdl'
						);
						//dice
						$list[] = array(
							'title'=> "dice_2.pdl.mp4",
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/repodice.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>'dice_2.pdl'
						);
						//simplewhiteboard
						$list[] = array(
							'title'=> "whiteboardsimple.pdl.mp4",
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/reposimplewhiteboard.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>'whiteboardsimple.pdl'
						);
	
						//click counter
						$list[] = array(
							'title'=> "counter.pdl.mp4",
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/repoclickcounter.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>'counter.pdl'
						);
	
						//flashcards
						$list[] = array(
							'title'=> "flashcards_1234.pdl.mp4",
							'thumbnail'=>"{$CFG->wwwroot}/repository/poodll/pix/repoflashcards.jpg",
							'thumbnail_width'=>280,
							'thumbnail_height'=>100,
							'size'=>'',
							'date'=>'',
							'source'=>'flashcards_1234.pdl'
						);
	
			return $list;
	
	}
	

	  /**
     * 
     * @return string
     */
    public function supported_returntypes() {

		if(!empty($this->options['recording_format']) && $this->options['recording_format'] == self::POODLLWIDGET){
			return FILE_EXTERNAL;
		}else{
			return FILE_INTERNAL;
		}
    }
	


    /**
     * Returns the suported returns values.
     * 
     * @return string supported return value
     */
    public function supported_return_value() {
        return 'ref_id';
    }

    /**
     * Returns the suported file types
     *
     * @return array of supported file types and extensions.
     */
    public function supported_filetypes() {
		
		switch($this->options['recording_format']){
			case self::POODLLAUDIO:
			case self::POODLLVIDEO:
				$ret= array('.flv','.mp4','.mp3');
				break;
				
			case self::POODLLSNAPSHOT:
			case self::POODLLWHITEBOARD:
				$ret = array('.jpg');
				break;
				
			case self::MP3AUDIO:
				$ret = array('.mp3');
				break;
				
			case self::POODLLWIDGET:
				$ret = array('.pdl','.mp4');
				break;
		}
		return $ret;
    }
	
	   /*
     * Fetch the recorder widget
     */
    public function fetch_recorder() {
        global $USER,$CFG;
        
        $ret ="";
	
      //we get necessary info
	 $context = context_user::instance($USER->id);	
	 $component = 'user';
	 $filearea= 'draft';
	 $itemid='0';
	 $timelimit='0';
	 $callbackjs='';
	 
     $filename = 'filename' . '_' . $this->options['recording_format'] ;

		switch($this->options['recording_format']){
			case self::POODLLAUDIO:
				//$ret .= \filter_poodll\poodlltools::fetchSimpleAudioRecorder('swf','poodllrepository',$USER->id,$filename);
				$ret .= \filter_poodll\poodlltools::fetchAudioRecorderForSubmission('auto','poodllrepository', $filename,$context->id,$component,$filearea,$itemid,0);
				break;
			case self::POODLLVIDEO:
				//$ret .= \filter_poodll\poodlltools::fetchSimpleVideoRecorder('swf','poodllrepository',$USER->id,$filename,'','298', '340');
				$ret .= \filter_poodll\poodlltools::fetchVideoRecorderForSubmission('swf', 'poodllrepository', $filename, $context->id,$component,$filearea,$itemid,0);
				break;
			case self::MP3AUDIO:
				$ret .= \filter_poodll\poodlltools::fetchMP3RecorderForSubmission($filename,$context->id,$component,$filearea,$itemid );
				break;
			case self::POODLLWHITEBOARD:
				$ret .= \filter_poodll\poodlltools::fetchWhiteboardForSubmission($filename,$context->id,$component,$filearea,$itemid,400,300,"",'drawingboard');
				break;
				
			case self::POODLLSNAPSHOT:
				$ret .= \filter_poodll\poodlltools::fetchSnapshotCameraforSubmission($filename,"apic.jpg", '290','340',$context->id,$component,$filearea,$itemid);
	
				break;
		}
		return $ret;

	}
}