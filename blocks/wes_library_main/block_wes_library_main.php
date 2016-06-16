<?php
// class block_wes__library_main extends block_base
class block_wes_library_main extends block_base
{
	public function init() 
	{
		$this->title = 'Library Research Help';
	} // init

	public function get_content() 
	{
		if ($this->content !== NULL) 
		{
			return $this->content;
		}

		$this->content = new stdClass;
		$this->content->text = '
<ul>
<li><a href="http://libraryfaq.wesleyan.edu/questions/3456" target="_blank">Plan and start your research</a></li>
<li><a href="http://www.wesleyan.edu/libr/howdoi/makeanappointment.html" target="_blank">Meet with a librarian</a></li>
<li><a href="http://libguides.wesleyan.edu/citing" target="_blank">How to cite your sources</a></li>
<li><a href="http://libraryfaq.wesleyan.edu/" target="_blank">Library FAQ</a></li>
<li><a href="http://www.wesleyan.edu/libr/find/showmehow.html" target="_blank">Library how-to videos</a></li>
</ul>
<!-- Place this div in your web page where you want your chat widget to appear. -->
<div class="needs-js"></div>

<!-- Place this script as near to the end of your BODY as possible. -->
<script type="text/javascript">
  (function() {
    var x = document.createElement("script"); x.type = "text/javascript"; x.async = true;
    x.src = (document.location.protocol === "https:" ? "https://" : "http://") + "us.libraryh3lp.com/js/libraryh3lp.js?5043";
    var y = document.getElementsByTagName("script")[0]; y.parentNode.insertBefore(x, y);
  })();
</script>';
		$this->content->footer = '';
		return $this->content;
	} // get_content

} // class
