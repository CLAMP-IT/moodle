<?php

 // summary.php
 // Part of Marginalia annotation for Moodle
 // See www.geof.net/code/annotation/ for full source and documentation.

 // Display a summary of all annotations for the current user

require_once( "../../config.php" );
require_once( "marginalia-php/MarginaliaHelper.php" );
require_once( 'marginalia-php/Annotation.php' );
require_once( 'marginalia-php/Keyword.php' );
require_once( 'config.php' );
require_once( 'lib.php' );
require_once( 'annotation_globals.php' );
require_once( "annotation_summary_query.php" );
require_once( "keywords_db.php" );

global $CFG;

class activity_log_search
{
	var $first = 1;
	var $limit = 0;
	var $course;
	var $user;
	var $discussion;
	var $object_type;
	var $object_id;
	
	function activity_log_search( $a=null )
	{
		if ( $a )
			$this->from_params( $a );
	}
	
	function url( )
	{
		// don't typecast first to int as it might be a substitution like {first}
		$url = ANNOTATION_PATH.'/activity_log.php?first='.$this->first.'&limit='.$this->limit;
		if ( $this->course )
			$url .= '&course='.(int)$this->course;
		if ( $this->user )
			$url .= '&user='.(int)$this->user;
		if ( $this->discussion )
			$url .= '&d='.(int)$this->discussion;
		if ( $this->object_type )
			$url .= '&object_type='.(int)$this->object_type;
		if ( $this->object_id )
			$url .= '&object_id='.(int)$this->object_id;
		return $url;
	}
	
	function from_params( $a )
	{
		if ( array_key_exists( 'first', $a ) )
			$this->first = (int)$a[ 'first' ];
		if ( array_key_exists( 'limit', $a ) )
			$this->limit = (int)$a[ 'limit' ];
		if ( array_key_exists( 'course', $a ) )
			$this->course = (int)$a[ 'course' ];
		if ( array_key_exists( 'user', $a ) )
			$this->user = (int)$a[ 'user' ];
		if ( array_key_exists( 'd', $a ) )
			$this->discussion = (int)$a[ 'd' ];
		if ( array_key_exists( 'object_id', $a ) )
			$this->object_type = (int)$a[ 'object_id' ];
		if ( array_key_exists( 'object_type', $a ) )
			$this->object_id = (int)$a[ 'object_type' ];
	}
	
	function derive( $a=null )
	{
		$search = new activity_log_search( array(
			'first' => $this->first,
			'limit' => $this->limit,
			'course' => $this->course,
			'user' => $this->user,
			'discussion' => $this->discussion,
			'object_type' => $this->object_type,
			'object_id' => $this->object_id
		) );
		if ( $a )
			$search->from_params( $a );
		return $search;
	}
	
	function desc( )
	{
		if ( AN_OTYPE_ANNOTATION == $this->object_type )
			 $a[] = "for annotation ".$this->unzoom_link( $this->object_id, 'object_type' );
		else if ( AN_OTYPE_POST == $this->object_type )
			$a[] = "for post ".$this->unzoom_link( $this->object_id, 'object_type' );
		if ( $this->discussion )
			$a[] = "for discussion ".$this->unzoom_link( $this->discussion, 'discussion' );
		if ( $this->course )
			$a[] = "for course ".(int)$this->course;
		if ( $this->user )
			$a[] = "by user ".$this->unzoom_link( $this->user, 'user' );
		$limitstr = 'all';
		if ( $this->limit )
			$limitstr = 'up to '.$this->unzoom_link($this->limit, 'limit' );
		$s = 'Showing '.$limitstr.' log events';
		for ( $i = 0;  $i < count( $a );  ++$i )
			$s .= ' '.$a[ $i ];
		return $s.'.';
	}
	
	function zoom_link( $params )
	{
		$tsearch = $this->derive( $params );
		return "<a class='zoom' href='".s( $tsearch->url( ) )."'>".AN_FILTERICON_HTML."</a>";
	}
	
	function unzoom_link( $prompt, $unparam )
	{
		$tsearch = $this->derive( array( $unparam => null ) );
		return "<a class='unzoom' href='".s( $tsearch->url( ) )."'>".s( $prompt )."</a>";
	}
	
	/**
	 * restrict to viewing log from a single course only
	 * this helps satisfy ethics considerations
	 */
	function is_permitted( )
	{
		if ( $this->course || $this->discussion )
			return true;
		else if ( $this->object_type == AN_OTYPE_POST )
			return true;
		else if ( $this->object_type == AN_OTYPE_ANNOTATION )
			return true;
		return false;
	}
}

class marginalia_activity_log_page
{
	var $search;
	var $maxrecords = 100;
	var $course;
	var $extracolumns = true;
	
	function marginalia_activity_log_page( $search )
	{
		$this->search = $search;
	}
	
	function show_header( )
	{
		global $CFG, $USER, $course;
		
		$swwwroot = htmlspecialchars( $CFG->wwwroot );
		

		$navlinks = array();
		if ( null != $this->course && $this->course->category)
		{
			$navlinks[ ] = array(
				'name' => $this->course->shortname,
				'link' => $CFG->wwwroot.'/course/view.php?id='.$this->course->id,
				'type' => 'title' );
		}
		$navlinks[] = array(
				'name' => get_string( 'activity_log_title', ANNOTATION_STRINGS ),
				'type' => 'title');
		$navigation = build_navigation( $navlinks );
		$meta = "<link rel='stylesheet' type='text/css' href='".s( ANNOTATION_PATH )."/activity_log-styles.php'/>\n";
		print_header(get_string( 'activity_log_title', ANNOTATION_STRINGS ), null, $navigation, "", $meta, true, "", null );
	}
	
	function show( )
	{
		global $CFG;
		
		$search = $this->search;
		
		$this->errorpage = array_key_exists( 'error', $_GET ) ? $_GET[ 'error' ] : null;

		$query = "SELECT count(*) FROM {$CFG->prefix}marginalia_event_log";
		$total_events = count_records_sql( $query );
		
		$query = "SELECT e.id AS id, e.userid AS userid, e.service AS service, e.action AS action"
			.", e.description AS description, e.object_type AS object_type"
			.", e.object_id AS object_id, e.modified AS modified"
			.", e.course as course"
			.", concat(u.firstname, ' ', u.lastname) AS fullname"
			.", a.sheet_type AS sheet_type"
			.", a.quote AS quote"
			.", a.note AS note"
			.", a.object_type AS an_object_type"
			.", a.object_id AS an_object_id"
			.", concat(qu.firstname, ' ', qu.lastname) AS qu_fullname"
			.", p.id AS p_id"
			.", p.subject AS p_name"
			.", p.discussion AS p_discussion"
			.", p.created AS p_created"
			.", d.name AS d_name"
			.", d.id AS d_id"
			.", c.shortname AS c_name"
			." FROM {$CFG->prefix}marginalia_event_log e"
			." LEFT OUTER JOIN {$CFG->prefix}course c ON c.id=e.course"
			." LEFT OUTER JOIN {$CFG->prefix}marginalia_annotation_log a"
				." ON (e.object_type=".AN_OTYPE_ANNOTATION." AND a.eventid=e.id)"
			." LEFT OUTER JOIN {$CFG->prefix}user u ON u.id=e.userid"
			." LEFT OUTER JOIN {$CFG->prefix}forum_posts p"
				." ON (a.object_type=".AN_OTYPE_POST." AND p.id=a.object_id)"
			." LEFT OUTER JOIN {$CFG->prefix}forum_discussions d"
				." ON (a.object_type=".AN_OTYPE_POST." AND d.id=p.discussion)"
			." LEFT OUTER JOIN {$CFG->prefix}USER qu "
				." ON (e.object_type=".AN_OTYPE_ANNOTATION." AND qu.id=a.quote_author_id)"
			." WHERE 1=1";
				
		if ( $search->course )
			$query .= " AND e.course=".(int)$search->course."\n";
		
		if ( $search->user )
			$query .= " AND e.userid=".(int)$search->user."\n";
		
		if ( $search->discussion )
			$query .= " AND d.id=".(int)$search->discussion."\n";

		if ( $search->object_type )
		{
			$query .= " AND e.object_type=".(int)$search->object_type;
			if ( $search->object_id )
				$query .= " AND e.object_id=".(int)$search->object_id;
			$query .= "\n";
		}
		
		
		$query .= " ORDER BY e.modified DESC";
//		echo "Query: $query\n";
		$events = get_records_sql( $query, $search->first - 1, $this->maxrecords );
		
		echo "<p>".$search->desc( )."</p>\n";
		
		echo "<table id='events'>\n";
		echo " <thead><tr>\n";
		echo "  <th>Time</th><th>Course</th><th>User</th><th>Service</th><th>Action</th><th>Description</th><th>Object</th>\n";
		if ( $this->extracolumns )
			echo "<th>Sheet</th><th>Quote</th><th>Note</th><th>Post</th><th>Discussion</th><th>Post Author</th><th>Posted</th>\n";
		echo " </tr></thead><tbody>\n";
		if ( $events && count( $events ) )
		{
			foreach ( $events as $event )
			{
				echo " <tr>\n";
				echo "  <td>".s(MarginaliaHelper::timeToIso($event->modified))."</td>\n";
				
				echo "  <td>".s($event->c_name)
					.$this->zoom_link( array( 'course' => $event->course ) )."</td>\n";

				echo "  <td>".s($event->fullname)
					.$this->zoom_link( array( 'user' => $event->userid ) )."</td>\n";
				
				echo "  <td>".s($event->service)."</td>\n";
				echo "  <td>".s($event->action)."</td>\n";
				echo "  <td>".s($event->description)."</td>\n";

				if ( AN_OTYPE_ANNOTATION == $event->object_type )
				{
					echo "<td>annotation #".(int)$event->object_id
						.$this->zoom_link( array( 'object_type' => $event->object_type, 'object_id' => $event->object_id ) )
						."</td>\n";
					if ( $this->extracolumns )
					{
						if ( AN_OTYPE_ANNOTATION == $event->object_type )
						{
							echo "<td>".s(annotation_globals::sheet_str($event->sheet_type))."</td>\n";
							echo "<td>".s($event->quote)."</td>\n";
							echo "<td>".s($event->note)."</td>\n";
							if ( AN_OTYPE_POST == $event->an_object_type )
							{
								$url = $CFG->wwwroot.'/mod/forum/permalink.php?p='.$event->p_id;
								echo "<td><a href='".s($url)."'>".s($event->p_id)."</a></td>\n";
								echo "<td>".s($event->d_name)
									.$this->zoom_link( array( 'd' => $event->d_id ) )."</td>\n";
								echo "<td>".s($event->qu_fullname)."</a></td>\n";
								echo "<td>".s(MarginaliaHelper::timeToIso($event->p_created))."</td>\n";
							}
							else
								echo "<td colspan='4'></td>\n";
						}
						else
							echo "<td colspan='8'></td>\n";
					}
				}
				else if ( AN_OTYPE_POST == $event->object_type )
				{
					echo "<td>post #".(int)$event->object_id
						.$this->zoom_link( array( 'object_type' => $event->object_type, 'object_id' => $event->object_id ) )
						."</td>\n";
					echo "<td colspan='8'></td>\n";
				}
				else
					echo "<td colspan='9'></td>\n";
				echo " </tr>\n";
			}
			echo "</tbody></table>\n";
			
			
			$tsearch = $search->derive( );
			$tsearch->first = '{first}';
			marginalia_summary_lib::show_result_pages( $search->first, $total_events, $this->maxrecords, $tsearch->url( ) );
		}
	}
	
	function zoom_link( $params )
	{
		return $this->search->zoom_link( $params );
	}
}

function do_logging( )
{
	global $CFG, $USER;
	
	// Cross-site request forgery protection requires cookies, so it will not be
	// activated if $CFG->usesid=true
	$csrfprotect = ! empty( $CFG->usesid ) && $CFG->usesid;
	if ( ! $csrfprotect )
	{
		$csrfCookie = 'MoodleSessionTest' . $CFG->sessioncookie;
		$csrfCookieValue = $_SESSION['SESSION']->session_test;
		if ( ! array_key_exists( $csrfCookie, $_POST )
			|| $csrfCookieValue != $_POST[ $csrfCookie ] )
		{
			$this->httpError( 403, 'Forbidden', 'Illegal request' );
			return;
		}
	}

	$service = array_key_exists( 'service', $_POST ) ? $_POST[ 'service' ] : null;
	if ( ! $service || $service != 'smartquote' )
	{
		header( 'HTTP/1.1 400 Bad Request' );
		echo "<h1>Bad Request</h1><p>The specified service (".s($service).") does not exist or cannot be logged via HTTP.</p>";
	}
	else
	{
		$course = array_key_exists( 'course', $_POST ) ? (int) $_POST[ 'course' ] : null;
		$action = array_key_exists( 'action', $_POST ) ? $_POST[ 'action' ] : null;
		$description = array_key_exists( 'description', $_POST ) ? $_POST[ 'description' ] : null;
		$object_type = array_key_exists( 'object_type', $_POST ) ? $_POST[ 'object_type' ] : null;
		$object_id = array_key_exists( 'object_id', $_POST ) ? (int)$_POST[ 'object_id' ] : null;
		
		if ( 'annotation' == $object_type )
			$object_type = AN_OTYPE_ANNOTATION;
		elseif ( 'forum_post' == $object_type )
			$object_type = AN_OTYPE_POST;
		elseif ( 'forum_discussion' == $object_type )
			$object_Type = AN_OTYPE_DISCUSSION;
		
		$event = new object( );
		$event->course = $course;
		$event->userid = $USER->id;
		$event->service = 'quote';
		$event->action = $action;
		$event->description = $description;
		$event->object_type = $object_type;
		$event->object_id = $object_id;
		$event->modified = time( );
		insert_record( AN_EVENTLOG_TABLE, $event, true );
	}
}

$context = get_context_instance( CONTEXT_SYSTEM );
require_capability( 'block/marginalia:view_log', $context );

$urlString = $_SERVER[ 'REQUEST_URI' ];

switch ( $_SERVER[ 'REQUEST_METHOD' ] )
{
	case 'GET':
		$first = array_key_exists( 'first', $_GET ) ? (int)$_GET[ 'first' ] : 0;
			
		$search = new activity_log_search( $_GET );
		if ( ! $search->is_permitted( ) )  {
			header( 'HTTP/1.1 403 Forbidden' );
			echo "<h1>Forbidden</h1><p>Log data can only be retrieved for one course at a time.</p>";
			return;
		}
			
		$page = new marginalia_activity_log_page( $search );
		$page->show_header( );
		$page->show( );
		print_footer( );
		break;
		
	// Used for logging activities that are client-side only, like quoting
	case 'POST':
		if ( AN_USELOGGING )
			do_logging( );
		else
			header( 'HTTP/1.1 405 Method Not Allowed' );
			header( 'Allow: GET, POST' );
		break;
		
	default:
		header( 'HTTP/1.1 405 Method Not Allowed' );
		header( 'Allow: GET' + AN_LOGGING ? ', POST' : '' );
}

