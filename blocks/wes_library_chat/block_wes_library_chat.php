<?php
// class block_wes__library_chat extends block_base
class block_wes_library_chat extends block_base
{
	public function init() 
	{
		$this->title = 'Need Research Help?';
	} // init

	public function get_content() 
	{
		if ($this->content !== NULL) 
		{
			return $this->content;
		}

		$this->content = new stdClass;
		$this->content->text = '
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
