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

form#annotation-search {
	display: block;
	text-align: center;
	margin: 1em auto;
}

form#annotation-search fieldset {
	border: none;
	margin: 0;
	padding: 0;
	background: none;
	width: 100%;
}

form#annotation-search #search-text {
	width: 30em;	
}

.tags {
	width: 100%;
	text-align: right;
	font-size: 90%;
}

p {
	margin: 1ex;
}

p#query {
	margin-top: 1.5em;
}

p#query a .alt {display: none;}
p#query a .alt,
p#query a:hover .current {
	xdisplay: none;
	text-decoration: line-through;
}

p#query a:hover {
	text-decoration: none;
}

/*
p#query a:hover .alt {
	display: inline;
}
*/

p.error em.range-error {
	color: white;
	background: red;
	font-weight: bold;
	width: 1em;
	display: block;
	float: left;
	font-style: normal;
	text-align: center;
	margin-right: .5ex;
}

table.annotations {
	/* These aren't really compatible, but for practical purposes they work
	 * (man, I hate the W3C box model): */
	margin: .5em 2em ;
	width: 90%;
}

table.annotations thead.labels th {
	font-weight: normal;
	text-transform: lowercase;
	font-size: 80%;
	text-align: left;
	margin: 0;
	padding-top: .25ex;
	font-style: italic;
	border-top: black 1px solid;
}

table.annotations thead.labels th:before {
	font-style: normal;
	font-size: 120%;
	margin-right: .5ex;
	content: '\2191';
}

table.annotations thead.labels.top th:before {
	content: '';
}

table.annotations th,
table.annotations td {
	padding:3px ;
	margin: 0 ;
	vertical-align: top ;
}

table.annotations tbody th {
	background: none ;
	font-weight: normal ;
	font-size: 80% ;
	text-align: left ;
}

table.annotations tbody tr th,
table.annotations tbody tr td {
	/*border-top: <?PHP echo $THEME->cellcontent; ?> 1px solid ;*/
	border-top:  white 2px solid;
}

table.annotations tbody tr.fragment.first th,
table.annotations tbody tr.fragment.first td {
	border-top: black 1px solid;
}

table.annotations thead th {
	text-align: left ;
	font-weight: bold ;
	background: none ;
	padding-top: 1ex;
}

table.annotations thead th h3 {
	display: inline;
	text-transform: capitalize;
	margin: 0;
}

table.annotations tbody td.quote-author {
	font-size: 80%;
	width: 10em;
}

table.annotations tbody td.quote {
	background: white ;
	width: 40%;
}

table.annotations tbody td.note {
	font-size: 80% ;
	width: 30%;
}

table.annotations tbody td.modified {
	font-size: 80%;
}

table.annotations tbody td.user {
	font-size: 80%;
}

table.annotations tbody td.user.isloginuser .user-name {
	display: none;
}

table.annotations tbody tr:hover td.quote,
table.annotations tbody tr:hover td.note {
	color: red;
}

table.annotations a.zoom {
	visibility: hidden;
	margin-left: 1ex;
	font-size: 120%;
}

table.annotations tr:hover a.zoom {
	visibility: visible;
}

table.annotations a.zoom:hover {
	font-weight: bold;
	text-decoration: none;
}


/* buttons */
table.annotations button {
	padding-left: .25ex ;
	padding-right: .25ex;
	width: 1em;
	background: none;
	border: none;
	cursor: pointer;
}

table.annotations button:hover {
	font-weight: bold;
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

/* smartcopy tip */
p#smartcopy-help {
	margin:  3em 4em 0 4em;
	font-size: smaller;
	position: relative;
}

.tip {
	font-weight: bold;
	position: 	absolute;
	width: 3em;
	left: -3em;
	text-transform: uppercase;
}

.tip:after {
	content: ':';
}
