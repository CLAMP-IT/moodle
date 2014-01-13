<?php 
	require_once('about.php');   
	require_once('annotateUtil.php');
	
 	class block_annotate extends block_base {
  		function init() {
  			 global $PAGE;
  			 global $CFG;
   			 $this->title   = get_string('annotate', 'block_annotate');
   			
   			 $PAGE->requires->js_init_call('M.block_annotate.init', 
   		     					array('pdf-doc-xls-ppt-jpg', $CFG->wwwroot));
   		     
   		   
  		}
 	
     	function instance_allow_config() {
           return true;
        }
  		
 	 	function has_config() {
 	  	   return true;
	    }
        
	 	function instance_allow_multiple() {
  			return FALSE;
		}
	    
  		
	
	    function get_content() {
	        if ($this->content !== NULL) {
   		        return $this->content;
    	    }
  	        $this->content         =  new stdClass;
   		    $this->content->text   = 'Use the annotate buttons beside resources, or ' .
   		    '<a href="' . simpleLoginURL() . '">Log on directly</a>. ';
   		    
   		    if ($this->config->access == 1) {
				$this->content->text .= 
   		    	'<input type="hidden" name="annotate_shareuser" id="annotate_shareuser" value="' . $this->config->shareuser . '"/>';
   		    }
   		    
    	    $this->content->footer = '';
   		    return $this->content;
  	    }
  	
  	  

	  
   }  
	
?>
