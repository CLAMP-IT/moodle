<?php
/*
 * blocks/marginalia/moodle_marginalia.php
 *
 * Marginalia has been developed with funding and support from
 * BC Campus, Simon Fraser University, and the Government of
 * Canada, the UNDESA Africa i-Parliaments Action Plan, and  
 * units and individuals within those organizations.  Many 
 * thanks to all of them.  See CREDITS.html for details.
 * Copyright (C) 2005-2011 Geoffrey Glass; the United Nations
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
FOR DEBUGGING:
Turn off minify for javascript by going to Site Admin > Appearance > AJAX.
Switch off JS caching and YUI combo loading
*/

require_once( $CFG->dirroot.'/blocks/marginalia/config.php' );
require_once( ANNOTATION_DIR.'/marginalia-php/embed.php' );
require_once( ANNOTATION_DIR.'/annotation_summary_query.php' );

// The smartquote icon symbol(s)
define( 'AN_SMARTQUOTEICON', '\u275d' );	// \u275b\u275c: enclosed single qs, 267a: recycle

// The same thing as entities because - and this stuns the hell out of me every
// single time - PHP 5 *does not have native unicode support*!!!  Geez guys,
// I remember reading about unicode in Byte Magazine in what, the 1980s?
define( 'AN_SMARTQUOTEICON_HTML', '&#10077;' ); //'&#10075;&#10076;' );

// Icon for filtering on the summary page
define( 'AN_FILTERICON_HTML', '&#9664' ); // '&#9754;' );  //&#9756;
	
define( 'ANNOTATION_STRINGS', 'block_marginalia' );

define( 'AN_SHEET_PREF', 'annotations.sheet' ); // 'annotations.user' );
define( 'AN_NOTEEDITMODE_PREF', 'annotations.note-edit-mode' );
define( 'AN_SPLASH_PREF', 'annotations.splash' );
//define( 'SMARTCOPY_PREF', 'smartcopy' );

define( 'AN_DBTABLE', 'marginalia' );
define( 'AN_READ_TABLE', 'marginalia_read' );

define( 'AN_SHEET_NONE' , 0 );
define( 'AN_SHEET_PRIVATE', 0x1 );
define( 'AN_SHEET_AUTHOR', 0x2 );
define( 'AN_SHEET_PUBLIC', 0xffff );

// Object types
define ( 'AN_OTYPE_POST', 1 );
define ( 'AN_OTYPE_ANNOTATION', 2 );
define ( 'AN_OTYPE_DISCUSSION', 3 );

// Needed by several annotation functions - if not set, PHP will throw errors into the output
// stream which causes AJAX problems.  Doing it this way in case moodle sets the TZ at some
// future point.  Leading @ suppresses warnings.  (Sigh... try..catch didn't work.  PHP is such a mess.)
// Commented out in hopes Moodle 2.0 has fixed this problem.
// @date_default_timezone_set( date_default_timezone_get( ) );

/**
 * A page profile knows the options enabled for a particular page, and
 * how to emit relevant HTML.  Stores page-specific information like post ID.
 * Immutable:  it should be safe to construct this multiple times for the same
 * page and get exactly the same version back.
 */
abstract class mia_page_profile
{
	protected $url;
	public $moodlemia;
	
	public function __construct( $moodlemia, $url )
	{
		$this->moodlemia = $moodlemia;
		$this->url = $moodlemia->relative_url( $url );
	}
	
	/**
	 * Note that this returns an actual URL.  Moodle's moodle_url class by default
	 * does not return a URL - it returns an HTML-escaped URL.  In my opinion,
	 * this implicit magic is perverse.  It may or may not be a convenient
	 * default, but it is surprising and violates the expectation that something
	 * called a URL would be a URL.  It is the case that refurl is also not a
	 * complete URL.  In the PHP code the term "refurl" is always used to
	 * refer to such a partial URL.
	 */
	public abstract function get_refurl( );
	
	/**
	 * Requires for annotation features
	 */
	protected function emit_requires_annotate( )
	{
		global $PAGE;
		
		$blockpath = '/blocks/marginalia';
		$PAGE->requires->css( $blockpath."/marginalia/marginalia.css" );
		$PAGE->requires->css( $blockpath."/annotation-styles.php" );
		
		// Scripts are loaded in page header (second parameter is true)
		// This could slow things down, but for now it's needed at least for
		// jQuery as js_init is fishy.
		$anscripts = listMarginaliaJavascript( );
		for ( $i = 0;  $i < count( $anscripts );  ++$i )
			$PAGE->requires->js( $blockpath.'/marginalia/'.$anscripts[ $i ], true );
		$PAGE->requires->js( $blockpath.'/marginalia-config.js', true );
		$PAGE->requires->js( $blockpath.'/MoodleMarginalia.js', true );
		
		// Moodle has changed how YUI2 code is included. The correct way to deal
		// with this means changing how Marginalia uses YUI. I don't want to mess
		// with that now. My plan (admittedly not high priority) is to use jQuery
		// autocomplete instead, and to apply it only when users type twitter-style 
		// tags (e.g. #mytag). This also eliminates the YUI code, which makes me
		// happy (not that there's anything necessarily wrong with YUI, but for
		// jQuery is far more practical for the DOM manipulation Marginalia needs).
		// For now I'm simply disabling autocomplete. Sorry! #geof#
		//$PAGE->requires->css( "/lib/yui/autocomplete/assets/skins/sam/autocomplete.css" );
		//$PAGE->requires->yui2_lib( '/lib/yui/yahoo-dom-event/yahoo-dom-event.js' );
		//		'/lib/yui/datasource/datasource-min.js',
		//$PAGE->requires->yui2_lib( 'autocomplete' );
	}
	
	/**
	 * Requires for quoting
	 */
	protected function emit_requires_quote( )
	{
		global $PAGE;
		
		$blockpath = '/blocks/marginalia';
		$PAGE->requires->js( $blockpath.'/smartquote.js', true );
	}
	
	/**
	 * Emit JS block
	 * Tried $PAGE->requires_js_init_call, but it implicitly loads a script
	 * in mod/module.js - which for me does not exist, and its parameter
	 * mechanism is way too limited.
	 */
	public function emit_init_js( $s )
	{
		global $PAGE;
		
		// All this rigamarole with Y is an attempt to make sure this executes
		// last, after tinyMCE controls are initialized.  No such luck.  The
		// code is emitted last, but tinyMCE must set up a timer, then wipe
		// out message content.  Bleargh.
		echo "<script type='text/javascript'>\n//<![CDATA[\n"
			."function moodle_marginalia_init(Y)\n{\n"
			."Y.on('domready', function() {"
			.$s
			."});\n"
			."}\n" //\n$( document ).ready( moodle_marginalia_init );\n"
			."//]]>\n</script>\n";
		$PAGE->requires->js_init_call( 'moodle_marginalia_init', null);
	}
	
	/**
	 * Body JS for annotation margin
	 */
	public function margin_js( )
	{
		global $CFG, $USER, $PAGE, $course;
		
		$refurl = $this->get_refurl( );
		
		$canannotate = $this->moodlemia->can_annotate( $refurl );

		// Get all annotation preferences as an associative array and sets them to defaults
		// in the database if not already present.
		$prefs = array(
			AN_SHEET_PREF => $this->moodlemia->get_pref( AN_SHEET_PREF, 'public' ),
			AN_NOTEEDITMODE_PREF => $this->moodlemia->get_pref( AN_NOTEEDITMODE_PREF, 'freeform' ),
			AN_SPLASH_PREF => $this->moodlemia->get_pref( AN_SPLASH_PREF, 'true' )
		);
		
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
		$summaryurl = ANNOTATION_PATH.'/summary.php?user='.(int)$USER->id.'&url='.urlencode( $refurl );
		$helpurl = ANNOTATION_PATH.'/help.php?component=block_marginalia&topic=annotate';
		$tagsurl = ANNOTATION_PATH.'/tags.php?course='.(int)$course->id;
		
		$sitecontext = get_context_instance(CONTEXT_SYSTEM);
		$allowAnyUserPatch = AN_ADMINUPDATE && (
			has_capability( 'block/marginalia:fix_notes', $sitecontext ) );
		
		$plugin_handlers = '';
		foreach ( $this->moodlemia->plugins as $plugin )
		{
			$dropdowns = $plugin->dropdown_entries( $refurl );
			if ( $dropdowns )
			{
				foreach ( $dropdowns as $dropdown )
					$plugin_handlers .= "\n,  ".$dropdown->value.': '.$dropdown->action;
			}
		}
		
		// These variable names are prefixed with "s" for "safe" (HTML safe)
		$swwwroot = s($CFG->wwwroot);
		$smiapath = s(ANNOTATION_PATH);
		$suserid = s($USER->id);
		$srefurl = s($refurl);
		$slogger = $this->moodlemia->logger && $this->moodlemia->logger->is_active() ? 'true' : 'false';
		$scourseid = (int)$course->id;
		$sanypatch = $allowAnyUserPatch ? 'true' : 'false';
		$scanannotate = $canannotate ? 'true' : 'false';
		$susesmartquote = AN_USESMARTQUOTE ? 'true' : 'false';
		$ssmartquoteicon = s(AN_SMARTQUOTEICON);
		$ssessioncookie = 'MoodleSession' . s($CFG->sessioncookie);
		$ssummaryurl = $summaryurl;
		$shelpurl = $helpurl;
		$stagsurl = $tagsurl;
		$ssplash = 'true' == $showsplashpref ? "'".get_string('splash',ANNOTATION_STRINGS)."'" : 'null';
		$sstrings = $this->moodlemia->strings_js( );
		
		return <<<SCRIPT
	var moodleRoot = '$swwwroot';
	var annotationPath = '$smiapath';
	var url = '$srefurl';
	var userId = '$suserid';
	window.moodleMarginalia = new MoodleMarginalia(
		annotationPath, url, moodleRoot, userId, $sprefs, {
			useSmartquote: $susesmartquote,
			useLog: '$slogger',
			course: $scourseid,
			allowAnyUserPatch: $sanypatch,
			canAnnotate: $scanannotate,
			smartquoteIcon: '$ssmartquoteicon',
			sessionCookie: '$ssessioncookie',
			onKeyCreate: true,
			handlers: {
				summary: function() { window.location = '$ssummaryurl'; },
				help: function() { window.location = '$helpurl'; }
				/*,tags: function() { window.location = '$stagsurl'; }*/
				$plugin_handlers}
			, splash: $ssplash
			, strings: $sstrings
		}
	);
	window.moodleMarginalia.onload( );
SCRIPT;
	}
	
	/**
	 * Get the current sheet
	 * Should be per profile (or per profile and per course),
	 * but for now the setting is global
	 */
	function get_sheet( )
	{
		return $this->moodlemia->get_sheet( );
	}
	
	/**
	 * Show the margin controls.
	 * These are currently in a drop-down menu with the following options:
	 * - which annotation set to show (multiple options)
	 * - link to summary page
	 * - help button
	 */
	function emit_margin_controls( )
	{
		global $USER;
		
		$refurl = $this->get_refurl( );
		
		$sheet = $this->get_sheet( );
		
		echo "<div class='discussioncontrols miacontrols clearfix'>";
		echo "<div class='discussioncontrol nullcontrol'>&#160;</div><div class='discussioncontrol'>&#160;</div>\n";
		echo "<select name='ansheet' class='discussioncontrol miacontrol' id='ansheet' onchange='window.moodleMarginalia.changeSheet(this,\"".$refurl."\");'>\n";

		$selected = $sheet == AN_SHEET_NONE ? " selected='selected' " : '';
		echo " <option $selected value='".$this->moodlemia->sheet_str(AN_SHEET_NONE,null)."'>".get_string('sheet_none', ANNOTATION_STRINGS)."</option>\n";

		if ( ! isguestuser() )  {
			$selected = $sheet == AN_SHEET_PRIVATE ? "selected='selected' " : '';
			echo " <option $selected"
				."value='".$this->moodlemia->sheet_str(AN_SHEET_PRIVATE,null)."'>".get_string('sheet_private', ANNOTATION_STRINGS)."</option>\n";
		}
		// Show item for all users
		if ( true )  {
			$selected = $sheet == AN_SHEET_PUBLIC ? "selected='selected' " : '';
			echo " <option $selected value='".$this->moodlemia->sheet_str(AN_SHEET_PUBLIC,null)."'>".get_string('sheet_public', ANNOTATION_STRINGS)."</option>\n";
		}
		echo "  <option disabled='disabled'>——————————</option>\n";
		echo "  <option value='summary'>".get_string('summary_link',ANNOTATION_STRINGS)."...</option>\n";
	//	echo "  <option value='tags'>".get_string('edit_keywords_link',ANNOTATION_STRINGS)."...</option>\n";
		
		foreach ( $this->moodlemia->plugins as $plugin )
		{
			$dropdowns = $plugin->dropdown_entries( $refurl );
			if ( $dropdowns )
			{
				foreach ( $dropdowns as $dropdown )
					echo "<option value='".$dropdown->value."'>".s($dropdown->name)."</option>\n";
			}
		}

		echo "  <option value='help'>".get_string('annotate_help_link',ANNOTATION_STRINGS)."...</option>\n";
		echo "</select>\n";	
		echo "</div>\n";
	}
	
	/**
	 * Emit JS to enable quote publishing
	 */
	protected function quote_publish_js( )
	{
		return "moodleMarginalia.enablePublishQuotes( );\n";
	}
	
	/**
	 * Emit JS to enable quote subscribing for a given MCE instance
	 */
	protected function quote_subscribe_js( $mceid )
	{
		return "moodleMarginalia.enableSubscribeQuotes( '".s($mceid)."' );\n";
	}
	
	
	public function output_margin( )
	{
		$output  = html_writer::tag('ol', '<li class="mia_dummyfirst"></li>',
			array('class'=>'mia_margin'
				, 'style'=>'float:right;width:15em'
				, 'title'=>get_string('create_margin', ANNOTATION_STRINGS)));
		//$output .= html_writer::end_tag('ol');
		return $output;
	}
	
	public function output_quote_button( $canreply=true )
	{
		if ( $canreply )
		{
			$output  = html_writer::tag( 'button',
				'<span>'.get_string( 'quote_button', ANNOTATION_STRINGS ).'</span>',
				array( 'class'=>'smartquote' ) );
			//$output .= html_writer::end_tag( 'button' );
			return $output;
		}
			return '';
	}

	/**
	 * Emit require statements for head
	 */
	public abstract function emit_requires( );
	
	/**
	 * Emit additional stuff (JS) in the body
	 */
	public abstract function emit_body( );
	
	/**
	 * Get the type of object (for annotation creation).  Defaults to
	 * null.
	 */
	public function get_object_type( $url )
	{  return null;  }
	
	/**
	 * Get the id of an object (for annotation creation).  Defaults to null.
	 */
	public function get_object_id( $url )
	{  return null;  }
}

class mia_profile_forum_display extends mia_page_profile
{
	var $object_type = null;
	var $object_id = null;
	
	public function __construct( $moodlemia, $url, $object_id, $object_type )
	{
		parent::__construct( $moodlemia, $url );
		$this->object_type = $object_type;
		$this->object_id = $object_id;
	}
	
	public function get_refurl( )
	{
		return $this->url;
	}
	
	public function emit_requires( )
	{
		$this->emit_requires_annotate( );
		$this->emit_requires_quote( );
		$this->moodlemia->emit_plugin_requires( );
	}
	
	public function emit_body( )
	{
		$s = $this->margin_js( );
		$s .= $this->quote_publish_js( );
		$this->emit_init_js( $s );
		$this->moodlemia->emit_plugin_body( );
	}
	
	public function get_object_type( $url )
	{
		return $this->object_type;
	}
	
	public function get_object_id( $url )
	{
		return $this->object_id;
	}
}

class mia_profile_forum_compose extends mia_page_profile
{
	var $replypostid;	// id of the post to which this is a reply, or null
	
	public function __construct( $moodlemia, $url )
	{
		parent::__construct( $moodlemia, $url );
		$this->replypostid = optional_param('reply', 0, PARAM_INT);
	}
	
	public function get_refurl( )
	{
		return '/mod/forum/permalink.php?p='.(int)$this->replypostid;
	}
	
	public function emit_requires( )
	{
		$this->emit_requires_annotate( );
		$this->emit_requires_quote( );
		$this->moodlemia->emit_plugin_requires( );
	}
	
	public function emit_body( )
	{
		$s = $this->margin_js( );
		$s .= $this->quote_publish_js( );
		$s .= $this->quote_subscribe_js( 'id_message' );
		$this->emit_init_js( $s );
		$this->moodlemia->emit_plugin_body( );
	}
	
	public function get_object_type( $url )
	{
		return AN_OTYPE_POST;
	}
	
	public function get_object_id( $url )
	{
		throw $this->replypostid;
	}
}

/**
 * A page using JS-only to send explicit Marginalia requests through Javascript.
 * I.e., no margin.  The summary page is like this.  Really this is a bit of a
 * hack: clearly these init functions shouldn't be tied so closely to profiles
 * for margins.
 */
class mia_profile_js extends mia_page_profile
{
	var $replypostid;	// id of the post to which this is a reply, or null
	
	public function __construct( $moodlemia )
	{
		parent::__construct( $moodlemia, null );
	}
	
	public function get_refurl( )
	{
		throw "Attempt to call moodle_profile_js::get_refurl";
	}
	
	public function emit_requires( )
	{
		$this->emit_requires_annotate( );
		$this->emit_requires_quote( );
		$this->moodlemia->emit_plugin_requires( );
	}
	
	public function emit_body( )
	{
		$s = $this->quote_publish_js( );
		$s .= $this->quote_subscribe_js( 'id_message' );
		$this->emit_init_js( $s );
		$this->moodlemia->emit_plugin_body( );
	}
	
	public function get_object_type( $url )
	{
		throw "Attempt to call moodle_profile_js::get_object_type";
	}
	
	public function get_object_id( $url )
	{
		throw "Attempt to call moodle_profile_js::get_object_id";
	}
}

class moodle_marginalia
{
	static $singleton = null;
	var $logger = null;
	var $plugins = array( );

	// I would think Moodle might cache the capabilities to make has_capability fast, but it doesn't.
	var $viewfullnames = False;
	var $viewfullnames_set = False;
	
	var $page_profiles = array( );

	public static function get_instance( )
	{
		if ( ! moodle_marginalia::$singleton )
			moodle_marginalia::$singleton = new moodle_marginalia( );
		return moodle_marginalia::$singleton;
	}
	
	/**
	 * Strip wwwroot from the start of a URL to create a URL relative only
	 * to this instance of moodle.  Used internally by Marginalia so that if
	 * Moodle is moved Marginalia will not break.
	 */
	public static function relative_url( $url )
	{
		global $CFG;
		
		$wwwroot = $CFG->wwwroot;
		
		return ( substr( $url, 0, strlen( $wwwroot ) ) == $wwwroot )
			? substr( $url, strlen( $CFG->wwwroot ) ) : $url;
	}

	/**
	 * Get the Marginalia profile for a given URL.
	 * Usually this will be for $PAGE->url.
	 */
	public function get_profile( $url )
	{
		if ( preg_match( '/^.*\/mod\/forum\/permalink\.php\?p=(\d+)/', $url, $matches ) )
			return new mia_profile_forum_display( $this, $url, (int) $matches[ 1 ], AN_OTYPE_POST);
		elseif ( preg_match( '/^.*\/mod\/forum\/discuss\.php\?d=(\d+)/', $url, $matches ) )
			return new mia_profile_forum_display( $this, $url, (int) $matches[ 1 ], AN_OTYPE_DISCUSSION );
		elseif ( preg_match( '/^.*\/mod\/forum\/post\.php/', $url, $matches ) )
			return new mia_profile_forum_compose( $this, $url );
		elseif ( preg_match( '/^.*\/blocks\/marginalia\/summary.php/', $url, $matches ) )
			return new mia_profile_js( $this );
		return null;
	}
	
	public function moodle_marginalia( )
	{
		global $CFG, $DB;
		
		// Load up the logger, if available
		$blocks = $DB->get_records('block');
		if ( $blocks )
		{
			$prefix = 'marginalia_';
			$prefixlen = strlen( 'marginalia_' );
			foreach ( $blocks as $block )
			{
				if ( substr( $block->name, 0, $prefixlen ) == $prefix )
				{
					require_once( $CFG->dirroot.'/blocks/'.$block->name.'/lib.php' );
					$plugin = new $block->name( );
					if ( $plugin->is_active( ) )
					{
						array_push( $this->plugins, $plugin );
						if ( $block->name == 'marginalia_log' )
							$this->logger = $plugin;
					}
				}
			}
		}
		
		/*
		 * Profiles for Marginalia functionality on a particular page.  Used by
		 * moodle_marginalia to decide which files to include and which functionality
		 * to activate.
		 *
		 * This could be done by having the page itself set up the configuration.  But
		 * that would require more changes to Moodle core code.  Since Marginalia has
		 * to patch Moodle, it's best to make the patch as small and unchanging as
		 * possible.  Instead the page can simply select a profile.  This can even be
		 * made automatic based on the page URL, which Moodle pages already set.
		 * 
		 * What's with PHP's retarded refusal to parse array( new ... )?  Why
		 * do people put up with this crap language?
		 */
		$this->page_profiles[ ] = new mia_profile_forum_display( $this,
			'forum_post',
			'/^.*\/mod\/forum\/permalink\.php\?p=(\d+)/',
			AN_OTYPE_POST);
		$this->page_profiles[ ] = new mia_profile_forum_display( $this,
			'forum_discussion',
			'/^.*\/mod\/forum\/discuss\.php\?d=(\d+)/',
			AN_OTYPE_DISCUSSION );
		$this->page_profiles[ ] = new mia_profile_forum_compose( $this,
			'forum_compose', 
			'/^.*\/mod\/forum\/post\.php/' );
	}

	function fullname($user)
	{
		// must be able to handle null user
		if ( ! $user )
			return 'NONE';
		if ( ! $this->viewfullnames_set )
		{
			$context = get_context_instance( CONTEXT_SYSTEM );
			$this->viewfullnames = has_capability( 'moodle/site:viewfullnames', $context );
			$this->viewfullnames_set = True;
		}
		return fullname( $user, $this->viewfullnames );
	}
	
	function fullname2( $firstname, $lastname )
	{
		$u = new object();
		$u->firstname = $firstname;
		$u->lastname = $lastname;
		return $this->fullname($u);
	}
	
	function get_host( )
	{
		global $CFG;
		$urlparts = parse_url( $CFG->wwwroot );
		return $urlparts[ 'host' ];
	}
	
	function get_service_path( )
	{
		global $CFG;
		return $CFG->wwwroot . ANNOTATION_PATH . '/annotate.php';
//		return $this->getMoodlePath( ) . ANNOTATE_SERVICE_PATH;
	}
	
	function get_keyword_service_path( )
	{
		global $CFG;
		return $CFG->wwwroot . ANNOTATION_PATH . '/keywords.php';
	}
	
	/** Get the moodle path - that is, the path to moodle from the root of the server.  Typically this is 'moodle/'.
	 * REQUEST_URI starts with this. */
	function get_moodle_path( )
	{
		global $CFG;
		$urlparts = parse_url( $CFG->wwwroot );
		return $urlparts[ 'path' ];
	}
	
	/**
	 * Get the server part of the moodle path.
	 * This is the absolute path, with the getMoodlePath( ) portion chopped off.
	 * Useful, because appending a REQUEST_URI to it produces an absolute URI. */
	function get_moodle_server( )
	{
		global $CFG;
		$urlparts = parse_url( $CFG->wwwroot );
		if ( $urlparts[ 'path' ] == '/' )
			return $CFG->wwwroot;
		else
			return substr( $CFG->wwwroot, 0, strpos( $CFG->wwwroot, $urlparts[ 'path' ] ) );
	}

	function get_install_date( )
	{
		// Hardcoded because I'm not aware of Moodle recording an install date anywhere
		date_default_timezone_set( date_default_timezone_get( ) );
		return strtotime( '2005-07-20' );
	}
	
	function get_feed_tag_uri( )
	{
		return "tag:" . $this->get_host() . ',' . date( 'Y-m-d', $this->get_install_date() ) . ":annotation";
	}
	
	/**
	 * get sheet type for sheet string
	 */
	function sheet_type( $sheet_str )
	{
		if ( 'public' == $sheet_str )
			return AN_SHEET_PUBLIC;
		elseif ( 'private' == $sheet_str )
			return AN_SHEET_PRIVATE;
		elseif ( 'author' == $sheet_str )
			return AN_SHEET_AUTHOR;
		else
			return AN_SHEET_NONE;
	}
	
	/**
	 * get sheet string for type and group
	 */
	function sheet_str( $sheet_type )
	{
		if ( AN_SHEET_PUBLIC == $sheet_type )
			return 'public';
		elseif ( AN_SHEET_PRIVATE == $sheet_type )
			return 'private';
		elseif ( AN_SHEET_AUTHOR == $sheet_type )
			return 'author';
		else
			return 'none';
	}

	/**
	 * Remember: This the Annotation class does not store Moodle user IDs, so
	 * you must be sure to query for username and quote_author_username if you
	 * want userid and quoteAuthorId set.
	 */
	function record_to_annotation( $r )
	{
		$annotation = new Annotation( );
		
		$annotation->setAnnotationId( $r->id );
		
		if ( array_key_exists( 'userid', $r ) )
			$annotation->setUserId( $r->userid );
		if ( array_key_exists( 'firstname', $r ) )
			$annotation->setUserName( $this->fullname2( $r->firstname, $r->lastname ) );
		
		if ( array_key_exists( 'sheet_type', $r ) )
			$annotation->setSheet( $this->sheet_str( $r->sheet_type ) );
		if ( array_key_exists( 'url', $r ) )
			$annotation->setUrl( $r->url );
		if ( array_key_exists( 'note', $r ) )
			$annotation->setNote( $r->note );
		if ( array_key_exists( 'quote', $r ) )
			$annotation->setQuote( $r->quote );
		if ( array_key_exists( 'quote_title', $r ) )
			$annotation->setQuoteTitle( $r->quote_title );
		if ( array_key_exists( 'quote_author_id', $r ) )
			$annotation->setQuoteAuthorId( $r->quote_author_id );
		elseif ( array_key_exists( 'quote_author', $r ) )	// to support old mdl_annotation table
			$annotation->setQuoteAuthorId( $r->quote_author );
		if ( array_key_exists( 'quote_author_firstname', $r ) )
			$annotation->setQuoteAuthorName( $this->fullname2( $r->quote_author_firstname, $r->quote_author_lastname ) );
		if ( array_key_exists( 'link', $r ) )
			$annotation->setLink( $r->link );
		if ( array_key_exists( 'link_title', $r ) )
			$annotation->setLinkTitle( $r->link_title );
		if ( array_key_exists( 'created', $r ) )
			$annotation->setCreated( (int) $r->created );
		if ( array_key_exists( 'modified', $r ) )
			$annotation->setModified( (int) $r->modified );
		if ( array_key_exists( 'lastread', $r ) )
			$annotation->setLastRead( (int) $r->lastread );
		
		$start_line = array_key_exists( 'start_line', $r ) ? $r->start_line : 0;
		$end_line = array_key_exists( 'end_line', $r ) ? $r->end_line : 0;
		// The second and subsequente lines of the test are to catch cases where everything is blank,
		// which can happen if the range is really old and uses the range field
		if ( array_key_exists( 'start_block', $r ) && $r->start_block !== null 
			&& ( ! array_key_exists( 'range', $r )
				|| ( $start_line || $end_line || $r->start_block || $r->end_block || $r->start_word || $r->end_word || $r->start_char || $r->end_char ) ) )
		{
			$range = new SequenceRange( );
			$range->setStart( new SequencePoint( $r->start_block, $start_line, $r->start_word, $r->start_char ) );
			$range->setEnd( new SequencePoint( $r->end_block, $end_line, $r->end_word, $r->end_char ) );
			$annotation->setSequenceRange( $range );
		}
		// Older versions used a range string column.  Check and translate that field here:
		else if ( array_key_exists( 'range', $r ) && $r->range !== null )  {
			$range = new SequenceRange( );
			$range->fromString( $r->range );
			$annotation->setSequenceRange( $range );
		}
		
		if ( array_key_exists( 'start_xpath', $r ) && $r->start_xpath !== null )  {
			$range = new XPathRange( );
			$range->setStart( new XPathPoint( $r->start_xpath, $start_line, $r->start_word, $r->start_char ) );
			$range->setEnd( new XpathPoint( $r->end_xpath, $end_line, $r->end_word, $r->end_char ) );
			$annotation->setXPathRange( $range );
		}
			
		return $annotation;
	}
		
	function annotation_to_record( $annotation )
	{
		global $DB;
		
		$record = new object();
		
		$id = $annotation->getAnnotationId( );
		if ( $id )
			$record->id = $id;
		
		// Map username to id #
		$userid = $annotation->getUserId( );
		$user = $DB->get_record( 'user', array( 'id' => (int) $userid ) );
		$record->userid = $user ? $user->id : null;

		$sheet = $annotation->getSheet( );
		$record->sheet_type = $this->sheet_type( $sheet );
			
		$record->url = $annotation->getUrl( );
		$record->note = $annotation->getNote( );
		$record->quote = $annotation->getQuote( );
		$record->quote_title = $annotation->getQuoteTitle( );
		
		// Map author username to id #
		$userid = $annotation->getQuoteAuthorId( );
		$user = $DB->get_record( 'user', array( 'id' => (int) $userid ) );
		$record->quote_author_id = $user ? $user->id : null;
		
		$record->link = $annotation->getLink( );
		$record->link_title = $annotation->getLinkTitle( );
		$record->created = $annotation->getCreated( );
		$record->modified = $annotation->getModified( );

		$sequenceRange = $annotation->getSequenceRange( );
		$sequenceStart = $sequenceRange->getStart( );
		$sequenceEnd = $sequenceRange->getEnd( );
		$xpathRange = $annotation->getXPathRange( );
		if ( null !== $xpathRange )  {
			$xpathStart = $xpathRange->getStart( );
			$xpathEnd = $xpathRange->getEnd( );
		}
		
		$record->start_block = $sequenceStart->getPaddedPathStr( );
		$record->start_xpath = null === $xpathRange ? null : $xpathStart->getPathStr( );
		$record->start_line = $sequenceStart->getLines( );
		$record->start_word = $sequenceStart->getWords( ) ? $sequenceStart->getWords( ) : 0;
		$record->start_char = $sequenceStart->getChars( );
		
		$record->end_block = $sequenceEnd->getPaddedPathStr( );
		$record->end_xpath = null === $xpathRange ? null : $xpathEnd->getPathStr( );
		$record->end_line = $sequenceEnd->getLines( );
		$record->end_word = $sequenceEnd->getWords( ) ? $sequenceEnd->getWords( ) : 0;
		$record->end_char = $sequenceEnd->getChars( );
		return $record;
	}

	/**
	 * Get an annotations preference value;  if the preference doesn't exist, create it
	 * so that the Javascript client will have permission to set it later (to prevent
	 * client creation of random preferences, only existing preferences can be set)
	 */
	public function get_pref( $name, $default )
	{
		$value = get_user_preferences( $name, null );
		if ( null == $value ) {
			$value = $default;
			set_user_preference( $name, $default );
		}
		return $value;
	}
	
	/**
	 * Get the sheet whose annotations are to be shown
	 */
	public function get_sheet( )
	{
		return $this->get_pref( AN_SHEET_PREF, 'public' );
	}
	
	/**
	 * Get JS for strings
	 */
	public function strings_js( )
	{
		$mgr = get_string_manager( );
		$strings = $mgr->load_component_strings( 'block_marginalia', current_language( ) );
		if ( ! $strings )
			$strings = $mgr->load_component_strings( 'block_marginalia', 'en' );
		$first = True;
		$s = '';
		foreach ( $strings as $key => $value )
		{
			if ( substr( $key, 0, 3 ) == 'js_' )
			{
				// IE will break if there's a trailing comma
				if ( ! $first )	
					$s .= ",\n";
				$s .= "'".str_replace('_', ' ', substr($key, 3))."': '".s($value)."'";
				$first = False;
			}
		}
		return "{\n$s\n}\n";
	}
	
	/**
	 * Marginalia init that must be done before head generation
	 */		 
	public function emit_plugin_requires( )
	{
		foreach ( $this->plugins as $plugin )
			$plugin->emit_requires( $this );
	}
	
	/**
	 * Initialize Marginalia on the page
	 * Emits the require_js to initialize Marginalia.
	 * If necessary, also creates relevant user preferences 
	 * (necessary for Marginalia to function correctly).
	 */
	public function emit_plugin_body( )
	{ }
	
	/**
	 * Figure out whether annotation is permitted on a given page
	 * Should be refactored - same code is in annotate.php
	 * #geof# should go into a mia_page_profile
	 */
	function can_annotate( $url )
	{
		global $USER;
		
		if ( isguestuser() or ! isloggedin() )
			return false;
		$handler = annotation_summary_query::handler_for_url( $url );
		if ( ! $handler )
			return false;
		$handler->fetch_metadata( );
		if ( $handler->modulename && $handler->courseid )
		{
			$cm = get_coursemodule_from_instance( $handler->modulename, $handler->modinstanceid, $handler->courseid);
			if ( $cm )
			{
				$modcontext = get_context_instance( CONTEXT_MODULE, $cm->id );
//				if ( has_capability('moodle/legacy:guest', $context, $USER->id, false ) )
//					return false;
				if ( ! $handler->capannotate )
					return false;	// annotation of this resource is never permitted
				else
					return has_capability($handler->capannotate, $modcontext);
			}
			else
				return false;
		}
		else
			return false;
	}	

	/**
	 * Deletes all annotations of a specific user
	 * This is here rather than in the annotation code so that not everything will have to
	 * include the annotation code.
	 *
	 * @param int $userid
	 * @return boolean
	 */
	function annotations_delete_user( $userid )
	{
		return delete_records( AN_DBTABLE, 'id', $userid );
	}
	
	/**
	 * Stub for calling Moodle log function
	 * This started breaking in Moodle 2.0.  Frankly I don't see the need for it,
	 * but I'll maintain calls to this stub instead of deleting them.
	 */
	function moodle_log( $op, $url, $args=null )
	{
		//global $course
		//add_to_log( $course->id, 'annotation', $op, $url, $args );
	}
}

class marginalia_summary_lib
{
	/**
	 * Pass in a url with {first} where the first item number should go
	 */
	static function show_result_pages( $first, $total, $perpage, $url )
	{
		// Show the list of result pages
		if ( $perpage )	//0 => no list, because everything is shown
		{
			$npages = ceil( $total / $perpage );
			if ( $npages > 1 )
			{
				$this_page = 1 + floor( ( $first - 1 ) / $perpage );
				echo "<ol class='result-pages'>\n";
				for ( $i = 1; $i <= $npages;  ++$i )
				{
					if ( $i == $this_page )
						echo "  <li>".$i."</li>\n";
					else
					{
						$page = 1 + ($i - 1) * $perpage;
						$turl = str_replace( '{first}', $page, $url);
						echo "  <li><a href='".s($turl)."'>$i</a></li>\n";
					}
				}
				echo "</ol>\n";
			}
		}
	}
}

