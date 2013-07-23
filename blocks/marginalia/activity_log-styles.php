<?php
header( 'Content-type: text/css' );
/*
if (!isset($themename)) {
	$themename = NULL;
}

$nomoodlecookie = true;
require_once("../config.php");
$themeurl = style_sheet_setup(filemtime("styles.php"), 300, $themename);
*/
?>

td {
	border: #aaa 1px solid;
	border-collapse: collapse;
	vertical-align: top;
}

.result-pages {
	list-style-type: none;
	margin: 2em auto;
	text-align: center;
	padding: 1px 0;
}

.result-pages li {
	display: inline;
}

.result-pages + * {
	clear: both;
}

a.unzoom:hover {
	text-decoration: line-through;
}
