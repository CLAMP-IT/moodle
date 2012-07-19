<?php

/**
 * Load up a help page.
 * Moodle 1.x provided a help functionality that would pop up a window containing 
 * an HTML help file.  In Moodle 2.0 this was dropped in favor of displaying
 * tool tip-type help and links back to moodle.org.  Unfortunately small plain text
 * tooltips are inadequate for Marginalia help.  Furthermore, linking back to
 * webmarginalia.net introduces more problems than it solves:  help pages would
 * need to be versioned, old versions would have to be maintained, and the volume
 * of requests could be expensive.  Including help documentation with the software
 * guarantees that it will refer to the correct version.
 */
 
require_once(dirname(__FILE__) . '/../../config.php');

// PARAM_STRINGID ensures this is a valid string ID, which should make
// this safe to use.  (Otherwise an attacker could include ../ or the like).
$topic = required_param('topic', PARAM_STRINGID);
$component  = required_param('component', PARAM_SAFEDIR);
$lang = current_language( );

$PAGE->set_url( '/blocks/marginalia/help.php' );
$PAGE->set_pagelayout( 'popup' );
$PAGE->set_context( get_context_instance( CONTEXT_SYSTEM ) );
$PAGE->requires->css( '/blocks/marginalia/help.css' );
	
echo $OUTPUT->header( );

list( $plugintype, $pluginname ) = normalize_component( $component );
$location = get_plugin_directory( $plugintype, $pluginname );

// Once something matches, break from the while.
// Otherwise fall down to the next case.
do
{
	$path = "$location/lang/$lang/help/$topic.html";
	if ( $location && file_exists( $path ) )
	{
		include( $path );
		break;
	}
	
	$path = "$location/lang/en/help/$topic.html";
	if ( $location && file_exists( $path ) )
	{
		include ( $path );
		break;
	}
	
	echo get_string( 'missing_help', 'block_marginalia' );
}
while ( false );

echo $OUTPUT->footer( );

