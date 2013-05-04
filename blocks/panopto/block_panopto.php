<?php
require_once("lib/panopto_data.php");

class block_panopto extends block_base
{
	var $blockname = "panopto";
	
	// Set system properties of plugin.
	function init()
	{
        $this->title = "Panopto Focus";
        $this->version = 2010102830;
    }

    // Block has global config (display "Settings" link on blocks admin page)
	function has_config()
	{
  		return true;
	}

	// Save global block data in mdl_config_plugins table instead of global CFG variable 
    function config_save($data)
    {
        foreach ($data as $name => $value)
        {
            set_config($name, trim($value), $this->blockname);
        }
        return true;
    }
    	
    // Block has per-instance config (display edit icon in block header)
	function instance_allow_config()
	{
		return true;
	}
	
	// Save per-instance config in custom table instead of mdl_block_instance configdata column
	function instance_config_save($data)
	{
		global $COURSE;

		if(!empty($data->panopto_course_publicid))
		{
			return panopto_data::set_panopto_course_id($COURSE->id, $data->panopto_course_publicid);
		}
		else
		{
			// If server is not set globally, there will be no other form values to push into config.
			return true;
		}
	}
    
	// Generate HTML for block contents
	function get_content()
	{
		global $CFG, $COURSE, $USER;
		
		if ($this->content !== NULL)
        {
            return $this->content;
        }

        $this->content = new stdClass;
        
        // Construct the Panopto data proxy object
        $panopto_data = new panopto_data($COURSE->id);
        
        if(empty($panopto_data->servername) || empty($panopto_data->instancename) || empty($panopto_data->applicationkey))
        {
			$this->content->text = "Global block configuration is incomplete.<br /><br />Please contact your system administrator.";
			$this->content->footer = "";
			
        	return $this->content;
        }

        try
        {
			if(!$panopto_data->sessiongroup_id)
			{
				$this->content->text .= "No Panopto course selected.";
			}
	        else
	        {
        		// Get course info from SOAP service.
	        	$course_info = $panopto_data->get_course();
	        	
		        // Panopto course was deleted, or an exception was thrown while retrieving course data.
	        	if($course_info->Access == "Error")
		        {
		        	$this->content->text .= "<span class='error'>Error retrieving Panopto course.</span>";
		        }
				else
				{
		        	// SSO form passes instance name in POST to keep URLs portable.
		        	$this->content->text .= "
		        		<form name='SSO' method='post'>
							<input type='hidden' name='instance' value='$panopto_data->instancename' />
						</form>";
		        	
		        	$this->content->text .= "<div><b>Live Sessions</b></div>";
					$live_sessions = $panopto_data->get_live_sessions();
			        if(!empty($live_sessions))
			        {
			        	$i = 0;
				        foreach($live_sessions as $live_session)
				        {
				        	// Alternate gray background for readability.
				        	$altClass = ($i % 2) ? "listItemAlt" : "";
				        	
				        	$live_session_display_name = s($live_session->Name);
					        $this->content->text .= "<div class='listItem $altClass'>
														$live_session_display_name
														 <span class='nowrap'>
														 	[<a href='javascript:launchNotes(\"$live_session->LiveNotesURL\")'
														 		>take notes</a
													 	  	 >]";
							if($live_session->BroadcastViewerURL)
							{
						        $this->content->text .= "
	        											 	[<a href='$live_session->BroadcastViewerURL' onclick='return startSSO(this)'
														 		>watch live</a
													 	  	 >]";
							}
							$this->content->text .= "
												 	  	 </span>
													</div>";
							$i++;
						}
			        }
			        else
			        {
			        	$this->content->text .= "<div class='listItem'>No live sessions.</div>";
			        }
			        
		        	$this->content->text .= "<div class='sectionHeader'><b>Completed Recordings</b></div>";
			        $completed_deliveries = $panopto_data->get_completed_deliveries();
			        if(!empty($completed_deliveries))
			        {
			        	$i = 0;
			        	foreach($completed_deliveries as $completed_delivery)
			        	{
			        		// Collapse to 3 lectures by default
			        		if($i == 3)
			        		{
			        			$this->content->text .= "<div id='hiddenLecturesDiv'>";
			        		}
        		        	
			        		// Alternate gray background for readability.
				        	$altClass = ($i % 2) ? "listItemAlt" : "";
			        		
			        		$completed_delivery_display_name = s($completed_delivery->DisplayName); 
					        $this->content->text .= "<div class='listItem $altClass'>
					        							<a href='$completed_delivery->ViewerURL' onclick='return startSSO(this)'>
				        									$completed_delivery_display_name
					        							</a>
				        							</div>";
        					$i++;
			        	}
			        	
			        	// If some lectures are hidden, display "Show all" link.
			        	if($i > 3)
			        	{
			        		$this->content->text .= "</div>";
			        		$this->content->text .= "<div id='showAllDiv'>";
			        		$this->content->text .= "[<a id='showAllToggle' href='javascript:toggleHiddenLectures()'>show all</a>]";
			        		$this->content->text .= "</div>";
			        	}
			        }
			        else
			        {
			        	$this->content->text .= "<div class='listItem'>No completed recordings.</div>";
			        }
			        
			        if($course_info->AudioPodcastURL)
			        {
				        $this->content->text .= "<div class='sectionHeader'><b>Podcast Feeds</b></div>
				        						 <div class='listItem'>
				        						 	<img src='$CFG->wwwroot/blocks/panopto/images/feed_icon.gif' />
				        							<a href='$course_info->AudioPodcastURL'>Audio Podcast</a>
				        							<span class='rssParen'>(</span
				        								><a href='$course_info->AudioRssURL' target='_blank' class='rssLink'>RSS</a
			        								><span class='rssParen'>)</span>
		                        				 </div>";
				        if($course_info->VideoPodcastURL)
				        {
				        	$this->content->text .= "
				        						 <div class='listItem'>
			        								<img src='$CFG->wwwroot/blocks/panopto/images/feed_icon.gif' />	
				        						 	<a href='$course_info->VideoPodcastURL'>Video Podcast</a>
				        							<span class='rssParen'>(</span
				        								><a href='$course_info->VideoRssURL' target='_blank' class='rssLink'>RSS</a
			        								><span class='rssParen'>)</span>
		                        				 </div>";
				        }
			        }
			        if(isteacheredit($COURSE->id))
			        {
				        $this->content->text .= "<div class='sectionHeader'><b>Links</b></div>
				        						 <div class='listItem'>
				        							<a href='$course_info->CourseSettingsURL' onclick='return startSSO(this)'
				        								>Course Settings</a>
			        							 </div>\n";
				        $system_info = $panopto_data->get_system_info();
				        $this->content->text .= "<div class='listItem'>
				        							Download Recorder
					        							<span class='nowrap'>
					        								(<a href='$system_info->RecorderDownloadUrl'>Windows</a>
								   							| <a href='$system_info->MacRecorderDownloadUrl'>Mac</a>)</span>
			        							</div>";
			        }
			        
					$this->content->text .= '
						<script type="text/javascript">
			                // Function to pop up Panopto live note taker.
			                function launchNotes(url)
			        		{
								// Open empty notes window, then POST SSO form to it.
								var notesWindow = window.open("", "PanoptoNotes", "width=500,height=800,resizable=1,scrollbars=0,status=0,location=0");
								document.SSO.action = url;
								document.SSO.target = "PanoptoNotes";
								document.SSO.submit();
			
								// Ensure the new window is brought to the front of the z-order.
								notesWindow.focus();
							}
							
							function startSSO(linkElem)
							{
								document.SSO.action = linkElem.href;
								document.SSO.target = "_blank";
								document.SSO.submit();
								
								// Cancel default link navigation.
					  			return false;
					  		}
					  		
					  		function toggleHiddenLectures()
					  		{
					  			var showAllToggle = document.getElementById("showAllToggle");
					  			var hiddenLecturesDiv = document.getElementById("hiddenLecturesDiv");
					  			
					  			if(hiddenLecturesDiv.style.display == "block")
					  			{
					  				hiddenLecturesDiv.style.display = "none";
					  				showAllToggle.innerHTML = "show all";
					  			}
					  			else
								{
					  				hiddenLecturesDiv.style.display = "block";
					  				showAllToggle.innerHTML = "show less";
					  			}
					  		}
				    	</script>';
		        }
	        }
        }
        catch(Exception $e){
        	$this->content->text .= "<br><br><span class='error'>Error getting Panopto course content.</span>";
        }
        		        
        // Block instance configuration convenience link.
        // Duplicates "edit" icon for discoverability. 
        if(isteacheredit($COURSE->id))
		{
	        $this->content->text .= "<div id='instanceConfigLink'>
	        							<a href='$CFG->wwwroot/course/view.php?id=$COURSE->id&instanceid={$this->instance->id}&sesskey=$USER->sesskey&blockaction=config'>
	        								Configure Block...
        								</a>
        							 </div>";
		}
        
		$this->content->footer = '';

        return $this->content;
    }
}

?>