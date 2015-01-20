<?php
// block to add library resource list
class block_wes_library_resources extends block_list
{
	function init()
	{
		$this->title = 'Research Help';
	} // init

	function get_content()
	{
		if ($this->content !== NULL)
		{
			return $this->content;
		}

		$this->content = new stdClass;

/* Contents of the Block:
1. How to get started with your research
2. Make an appt with a librarian
3. How to cite your sources
4. Library FAQ
5. Library how-to videos
*/
	$this->content->items = array();
	$this->content->icons = array();	
	$this->content->items[] = '<a href="http://libraryfaq.wesleyan.edu/questions/3456" target="_blank">Plan and start your research</a>';
	$this->content->items[] = '<a href="http://www.wesleyan.edu/libr/services/personalresearch.html" target="_blank">Meet with a librarian</a>';
	$this->content->items[] = '<a href="http://libguides.wesleyan.edu/citing" target="_blank">How to cite your sources</a>';
	$this->content->items[] = '<a href="http://libraryfaq.wesleyan.edu/" target="_blank">Library FAQ</a>';
	$this->content->items[] = '<a href="http://www.wesleyan.edu/libr/find/showmehow.html" target="_blank">Library how-to videos</a>';
	$this->content->icons[] = '';
	$this->content->icons[] = '';
	$this->content->icons[] = '';
	$this->content->footer = '';

		return $this->content;
	} // get_content

} // block_wes_library_resources
