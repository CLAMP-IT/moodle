<?php
$state=$DB->get_record('ipal',array('id'=>$ipalid));
if($state->preferredbehaviour =="Graph"){
	echo "<br>";
	echo "<br>";
	if(ipal_show_current_question()==1){
		echo "<iframe id= \"graphIframe\" src=\"graphics.php?ipalid=".$ipalid."\" height=\"535\" width=\"723\"></iframe>";
	}
}else{
	echo "<iframe id= \"graphIframe\" src=\"gridview.php?id=".$ipalid."\" height=\"535\" width=\"723\"></iframe>";
}