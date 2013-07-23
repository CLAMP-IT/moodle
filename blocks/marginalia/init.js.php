<?php
/*
 * init.js.php
 *
 * Marginalia has been developed with funding and support from
 * BC Campus, Simon Fraser University, and the Government of
 * Canada, the UNDESA Africa i-Parliaments Action Plan, and  
 * units and individuals within those organizations.  Many 
 * thanks to all of them.  See CREDITS.html for details.
 * Copyright (C) 2005-2007 Geoffrey Glass; the United Nations
 * http://www.geof.net/code/annotation
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * $Id$
 */

 /*
 * This is the Javascript that actually initializes Marginalia.  It is
 * not pure JS because it needs access to many settings within the server,
 * so it's built by PHP.  To the extent possible pure JS is elsewhere,
 * leaving configuration stuff here.
 */
global $CFG, $USER, $course;

require_once('../../config.php');
require_once( $CFG->dirroot.'/blocks/marginalia/config.php' );
require_once( ANNOTATION_DIR.'/lib.php' );

$marginalia = moodle_marginalia::get_instance( );

// Get all annotation preferences as an associative array and sets them to defaults
// in the database if not already present.
$prefs = array(
	AN_SHEET_PREF => $marginalia->get_pref( AN_SHEET_PREF, 'public' ),
	AN_SHOWANNOTATIONS_PREF => $marginalia->get_pref( AN_SHOWANNOTATIONS_PREF, 'false' ),
	AN_NOTEEDITMODE_PREF => $marginalia->get_pref( AN_NOTEEDITMODE_PREF, 'freeform' ),
	AN_SPLASH_PREF => $marginalia->get_pref( AN_SPLASH_PREF, 'true' )
);

$showannotationspref = $prefs[ AN_SHOWANNOTATIONS_PREF ];
$showsplashpref = $prefs[ AN_SPLASH_PREF ];

// Build a string of initial preference values for passing to Marginalia
$first = true;
$sprefs = '';
foreach ( array_keys( $prefs ) as $name )
{
	$value = $prefs[ $name ];
	if ( $first )
		$first = false;
	else
		$sprefs .= "\n, ";
	$sprefs .= "'".s( $name )."': '".s( $prefs[ $name ] )."'";
}
$sprefs = '{ '.$sprefs.' }';;

// URLs used by drop-down menu handlers
$summaryurl = ANNOTATION_PATH.'/summary.php?user='.(int)$USER->id.'&url='; //.urlencode( $refurl );
//$tagsurl = ANNOTATION_PATH.'/tags.php?course='.(int)$course->id;

$sitecontext = get_context_instance(CONTEXT_SYSTEM);
$allowAnyUserPatch = AN_ADMINUPDATE && (
	has_capability( 'block/marginalia:fix_notes', $sitecontext ) );

$plugin_handlers = '';
foreach ( $marginalia->plugins as $plugin )
{
	$dropdowns = $plugin->dropdown_entries( $refurl );
	if ( $dropdowns )
	{
		foreach ( $dropdowns as $dropdown )
			$plugin_handlers .= "\n,  ".$dropdown->value.': '.$dropdown->action;
	}
}
?>

/**
* Expected params:
* canannotate
* courseid
* refurl
* sessioncookie
* subscribe
*/
function moodle_marginalia_init( params )
//courseid, refurl, canannotate, subscribe )
{
	var canannotate = params.canannotate;
	var courseid = params.courseid;
	var refurl = params.refurl;
	var subscribe = params.subscribe;
	
	var moodleRoot = '<?php echo s($CFG->wwwroot);?>';
	var annotationPath = '<?php echo s(ANNOTATION_PATH)?>';
	var url = refurl;
	var userId = '<?php echo s($USER->id);?>';
	window.moodleMarginalia = new MoodleMarginalia(
		annotationPath, url, moodleRoot, userId, '<?php echo $sprefs;?>', {'
			useSmartquote: <?php echo s(AN_USESMARTQUOTE)?>,
			useLog: <?php echo ($marginalia->logger && $marginalia->logger->is_active() ? 'true' : 'false');?>,
			course: (int)$courseid,
			allowAnyUserPatch: '<?php echo $allowAnyUserPatch ? 'true' : 'false';?>',
			canAnnotate: canannotate ? 'true' : 'false',
			smartquoteIcon: '<?php echo AN_SMARTQUOTEICON;?>',
			sessionCookie: 'MoodleSessionTest.<?php echo '$CFG->sessioncookie;?>',
			handlers: {
				 summary: function(){ window.location = '<?php echo $summaryurl;?>+encodeURIComponentx(refurl)'; }
				/*,tags: function(){ window.location = '<?php echo $tagsurl;?>';*/ }
				<?php echo $plugin_handlers;?>}
			<?php if ( $showsplashpref == 'true' ) {?>
			, splash: '<?php echo get_string('splash',ANNOTATION_STRINGS);?>'
			<?php } ?>
		}
	);
	window.moodleMarginalia.onload( );
	
	<?php if ( $subscribe ) {?>
		window.moodleMarginalia.subscribeHtmlAreas(<?php echo (int)$course->id;?>);
	<?php } ?>
}

