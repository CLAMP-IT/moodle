<?php

/*
 * annotation_summary_query.php
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
 
 
// order by value for returning annotations in document order (i.e. same order
// the highlights would be shown within the document)
define( 'AN_SUMMARY_ORDER_DOCUMENT', 'section_type, section_name, a.url, start_block, start_line, start_word, start_char, end_block, end_line, end_word, end_char' );

// order by modification time, with most recent first
define( 'AN_SUMMARY_ORDER_TIME', 'modified DESC');

require_once( $CFG->dirroot.'/blocks/marginalia/config.php' );
require_once( ANNOTATION_DIR.'/moodle_marginalia.php' );
	
/**
 * Objects of this class are effectively immutable (the derive method violates this, but it is called
 * like a constructor).  Use the derive method to generate modified version (e.g. for links to related summaries)
 */
class annotation_summary_query
{
	var $moodlemia;
	var $url = null;
	var $sheet_type = AN_SHEET_PRIVATE;
	var $text = null;
	var $user = null;
	var $ofuser = null;
	var $exactmatch = false;
	var $all = false;
	var $orderby = AN_SUMMARY_ORDER_DOCUMENT;
	var $handler = null;		// URL handlers (implements much of this class's behavior)
	
	var $sql;			// The result SQL query
	var $error;			// Any error encountered by the constructor
	
	// Call with internal names in $a
	// If initializing from a URL, call map_params( $_GET ) first
	function annotation_summary_query( $a )
	{
		$this->moodlemia = moodle_marginalia::get_instance( );
		if ( $a )
			$this->from_params( $a );
	}
	
	// map URL query parameters to internal parameter names and values
	static function map_params( $a )
	{
		$b = array( );
		
		$b[ 'text' ] = array_key_exists( 'q', $a ) ? $a[ 'q' ] : null;
		$b[ 'exactmatch' ] = array_key_exists( 'match', $a ) ? 'exact' == $a[ 'match' ] : false;
		$b[ 'all' ] = array_key_exists( 'all', $a ) ? 'yes' == $a[ 'all' ] || 'true' == $a[ 'all' ] : false;
			
		// default sort order is document order, because that's what the marginalia front-end needs
		$sort_order = array_key_exists( 'sort', $a ) ? $a[ 'sort' ] : null;
		if ( 'document' == $sort_order )
			$b[ 'orderby' ] = AN_SUMMARY_ORDER_DOCUMENT;
		elseif ( 'time' == $sort_order )
			$b[ 'orderby' ] = AN_SUMMARY_ORDER_TIME;
		else
			$b[ 'orderby' ] = AN_SUMMARY_ORDER_DOCUMENT;
		
		$sheet = array_key_exists( 'sheet', $a ) ? $a[ 'sheet' ] : null;
		$b[ 'sheet_type' ] = $sheet ? $this->moodlemia->sheet_type( $sheet ) : null;
			
		$b[ 'userid' ] = array_key_exists( 'u', $a ) ? (int)$a[ 'u' ] : null;
		$b[ 'ofuserid'] = array_key_exists( 'search-of', $a ) ? (int)$a[ 'search-of' ] : null;
		
		$b[ 'url' ] = array_key_exists( 'url', $a ) ? $a[ 'url' ] : null;
		return $b;
	}
	
	// Tedious, eh?  Maybe there's some nice PHP way to do this.
	function from_params( $a )
	{
		global $DB;
		
		if ( array_key_exists( 'text', $a ) )
			$this->text =  $a[ 'text' ];
		if ( array_key_exists( 'exactmatch', $a ) )
			$this->exactmatch = $a[ 'exactmatch' ];
		if ( array_key_exists( 'all', $a ) )
			$this->all = $a[ 'all' ];
		if ( array_key_exists( 'orderby', $a ) )
			$this->orderby = $a[ 'orderby' ];
		if ( array_key_exists( 'sheet_type', $a ) )
			$this->sheet_type = $a[ 'sheet_type' ];

		if ( array_key_exists( 'user', $a ) )
			$this->user = $a[ 'user' ];
		elseif ( array_key_exists( 'userid', $a ) )
			$this->user = $DB->get_record( 'user', array( 'id' => (int)$a[ 'userid' ] ) );

		if ( array_key_exists( 'ofuser', $a ) )
			$this->ofuser = $a[ 'ofuser' ];
		elseif ( array_key_exists( 'ofuserid', $a ) )
			$this->ofuser = $DB->get_record( 'user', array('id' => (int)$a[ 'ofuserid' ] ) );

		if ( array_key_exists( 'url', $a ) )
		{
			$this->url =  $a[ 'url' ];
			$this->handler = annotation_summary_query::handler_for_url( $this->url );
		}
	}

	// Derive a version of this summary_query with some parameters changed
	function derive( $a )
	{
		$summary_query = new annotation_summary_query( array(
			'text' => $this->text,
			'exactmatch' => $this->exactmatch,
			'all' => $this->all,
			'orderby' => $this->orderby,
			'sheet_type' => $this->sheet_type,
			'user' => $this->user,
			'ofuser' => $this->ofuser,
			'url' => $this->url
		) );

		if ( $a )
			$summary_query->from_params( $a );
		return $summary_query;
	}

	static function handler_for_url( $url )
	{
		// A course or something *must* be specified
		if ( ! $url )
			return null;
		
		// All annotations for a course
		elseif ( preg_match( '/^.*\/course\/view\.php\?id=(\d+)/', $url, $matches ) )
			return new course_annotation_url_handler( (int) $matches[ 1 ]);

		// All annotations far a single forum
		elseif ( preg_match( '/^.*\/mod\/forum\/view\.php\?id=(\d+)/', $url, $matches ) )
			return new forum_annotation_url_handler( (int) $matches[ 1 ] );

		// Annotations for a single discussion
		elseif ( preg_match( '/^.*\/mod\/forum\/discuss\.php\?d=(\d+)/', $url, $matches ) )
			return new discussion_annotation_url_handler( (int) $matches[ 1 ] );

		// Annotations for a single post
		elseif ( preg_match( '/^.*\/mod\/forum\/permalink\.php\?p=(\d+)/', $url, $matches ) )
			return new post_annotation_url_handler( (int) $matches[ 1 ] );

		else
		{
			echo "no handler";
			return null;
		}
	}
	
	function titlehtml( )
	{
		$this->handler->fetch_metadata( );
		return $this->handler->titlehtml;
	}
	
	/** Produce a natural language description of a query */
	function desc( $titlehtml=null )
	{
		global $USER;
		
		$this->handler->fetch_metadata( );
		
		$a->title = null === $titlehtml ? $this->handler->titlehtml : $titlehtml;
		$a->who = $this->user ? s( $this->moodlemia->fullname( $this->user ) ) : get_string( 'anyone', ANNOTATION_STRINGS );
		$a->author = $this->ofuser ? s( $this->moodlemia->fullname( $this->ofuser ) ) : get_string( 'anyone', ANNOTATION_STRINGS );
		$a->search = s( $this->text );
		$a->match = get_string( $this->exactmatch ? 'matching' : 'containing', ANNOTATION_STRINGS );
		
		if ( AN_SHEET_PUBLIC == $this->sheet_type )
			$a->sheet = get_string( 'public_sheet', ANNOTATION_STRINGS );
		elseif ( AN_SHEET_PRIVATE == $this->sheet_type )
			$a->sheet = get_string( 'private_sheet', ANNOTATION_STRINGS );
		elseif ( AN_SHEET_AUTHOR == $this->sheet_type )
			$a->sheet = get_string( 'author_sheet', ANNOTATION_STRINGS );
		
		if ( null != $this->text && '' != $this->text )
			$s = $this->ofuser ? 'annotation_desc_authorsearch' : 'annotation_desc_search';
		else
			$s = $this->ofuser ? 'annotation_desc_author' : 'annotation_desc';
			
		return get_string( $s, ANNOTATION_STRINGS, $a );
		
		return $desc;
	}
	
	/** A natural language description, with elements as links to more general queries */
	function desc_with_links( $titlehtml=null )
	{
		global $USER;
		
		$this->handler->fetch_metadata( );
		
		$a->title = null === $titlehtml ? $this->handler->titlehtml : $titlehtml;
		
		// Show link to parent search
		$this->handler->fetch_metadata( );
		$parent_summary = $this->handler->parenturl ? $this->derive( array( 'url' => $this->handler->parenturl ) ) : null;
		if ( $parent_summary ) {
			$a->title = '<a class="opt-link" href="'.s( $parent_summary->summary_url( ) )
				. '" title="'.get_string( 'unzoom_url_hover', ANNOTATION_STRINGS ).'">'
				. '<span class="current">'.$a->title.'</span>'
				. '<span class="alt">'.$parent_summary->titlehtml( ).'</span></a>';
		}
		
		// Unzoom from user to anyone
		if ( $this->user )  {
			$summary_anyone = $this->derive( array( 'user' => null ) );
			$a->who = '<a class="opt-link" href="'.s( $summary_anyone->summary_url( ) )
				.'" title="'.get_string( 'unzoom_user_hover', ANNOTATION_STRINGS )
				.'"><span class="current">'.s( $this->moodlemia->fullname( $this->user ) ).'</span><span class="alt">'
				.get_string( 'anyone', ANNOTATION_STRINGS ).'</a></a>';
		}
		else
			$a->who = get_string( 'anyone', ANNOTATION_STRINGS );
		
		// Unzoom from of user to of anyone
		if ( $this->ofuser )  {
			$summary_anyone = $this->derive( array( 'ofuser' => null ) );
			$a->author = '<a class="opt-link" href="'.s( $summary_anyone->summary_url( ) )
				.'" title="'.get_string( 'unzoom_author_hover', ANNOTATION_STRINGS )
				.'"><span class="current">'.s( $this->moodlemia->fullname( $this->ofuser ) ).'</span><span class="alt">'
				.get_string( 'anyone', ANNOTATION_STRINGS ).'</span></a>';
		}
		else
			$a->author = null;
		
		$a->search = $this->text;
		
		// Toggle exact match
		$summary_match = $this->derive( array( 'exactmatch' => ! $this->exactmatch ) );
		$hover = get_string( $this->exactmatch ? 'unzoom_match_hover' : 'zoom_match_hover', ANNOTATION_STRINGS );
		$m1 = get_string( $this->exactmatch ? 'matching' : 'containing', ANNOTATION_STRINGS );
		$m2 = get_string( $this->exactmatch ? 'containing' : 'matching', ANNOTATION_STRINGS );
		$a->match = '<a class="opt-link" href="'.s( $summary_match->summary_url( ) )
			.'" title="'.$hover
			.'"><span class="current">'.$m1
			.'</span><span class="alt">'.$m2.'</span></a>';

		if ( $this->text )
			$s = ( null != $this->ofuser ) ? 'annotation_desc_authorsearch' : 'annotation_desc_search';
		else
			$s = ( null != $this->ofuser ) ? 'annotation_desc_author' : 'annotation_desc';
		
		$desc = get_string( $s, ANNOTATION_STRINGS, $a );

		return $desc;
	}
	
	/** Callback used by handlers to get standard query WHERE clause conditions */
	function get_sql_conds( &$params )
	{
		global $USER;
		
		$query = '';
		
		$accessvisible = '';
		// If not logged in, only the public sheet is visible
		if ( ! isloggedin( ) )
		{
			if ( AN_SHEET_PUBLIC == $this->sheet_type )
			{
				$query = 'a.sheet_type = :sheet_type ';
				$params[ 'sheet_type' ] = AN_SHEET_PUBLIC;
			}
			else
				$query = '1=0';	// automatic fail
		}
		// If the all flag is set, see if this is an admin user with permission to
		// export all annotations.
		elseif ( $this->all )  {
			$sitecontext = get_context_instance( CONTEXT_SYSTEM );
			if ( has_capability( 'blocks/marginalia:view_all', $sitecontext ) )
				$query = '1=1';
		}
		// If a sheet is specified, we need to check that the annotation is on that
		// sheet and that this user has appropriate access.
		elseif ( $this->sheet_type )
		{
			$query = 'a.sheet_type = :sheet_type';
			$params[ 'sheet_type' ] = $this->sheet_type;
			if ( AN_SHEET_PRIVATE == $this->sheet_type )
			{
				$query .= ' AND a.userid = :user_id';
				$params[ 'user_id' ] = $USER->id;
			}
			elseif ( AN_SHEET_AUTHOR == $this->sheet_type )
			{
				$query .= ' AND (a.userid = :user_id OR a.quote_author_id = :quote_author_id)';
				$params[ 'user_id' ] = $USER->id;
				$params[ 'quote_author_id' ] = $USER->id;
			}
		}
		// If a sheet is *not* specified, we need to be more general
		else
		{
			$query = 'a.sheet_type = :sheet_type OR a.userid = :user_id'
				.' OR (a.sheet_type = :sheet_author AND a.quote_author_id = :quote_author_id)';
			$params[ 'sheet_type' ] = AN_SHEET_PUBLIC;
			$params[ 'user_id' ] = $USER->id;
			$params[ 'sheet_author' ] = AN_SHEET_AUTHOR;
			$params[ 'quote_author_id' ] = $USER->id;
		}
		

		$query = '( ' . $query . ' )';
		
		// Filter by annotation creator
		if ( $this->user )
		{
			$query .= ' AND a.userid = :creator_user_id';
			$params[ 'creator_user_id' ] = (int)$this->user->id;
		}
		
		// These are the fields to use for a search;  specific annotations may add more fields
		$stdsearchfields = array( 'a.note', 'a.quote', 'u.firstname', 'u.lastname' );

		// Searching limits also;  fields searched are not alone those of the annotation:
		// add to them also those a page of this type might use.
		if ( null != $this->text && '' != $this->text )  {
			if ( $this->exactmatch )
			{
				$query .= "\n   AND a.note = :exactmatch";
				$params[ 'exactmatch' ] = $this->text;
			}
			else  {
				$handler = $this->handler;
				$searchcond = '';
				$addsearchfields = $handler->get_search_fields( );
				$searchcond = '';
				$querywords = split( ' ', $this->text );
				$n = 1;
				foreach ( $querywords as $word )
				{
					$sword = '%' . str_replace('%', '%%', $word) . '%';
					foreach ( $stdsearchfields as $field )
					{
						$searchcond .= ( ( $searchcond == '' ) ? '' : ' OR ' ) . "$field LIKE :match$n";
						$params[ 'match'.$n ] = $sword;
						$n += 1;
					}
					foreach ( $addsearchfields as $field )
					{
						$searchcond .= " OR $field LIKE :match$n";
						$params[ 'match'.$n ] = $sword;
						$n += 1;
					}
				}
				$query .= "\n   AND ($searchcond)";
			}
		}

		// This search is always limited by permissions and sheet
		return $query;
	}
	
	/** Callback used by handlers to get standard query FROM clause tables */
	function get_sql_tables( &$params )
	{
		global $USER;
		
		// Standard tables apply to all (but note the outer join of user, which if gone
		// should not steal the annotation from its owner):
		$params[ 'annotation_user_id' ] = (int) $USER->id;
		return ' {'.AN_DBTABLE."} a"
			. "\n INNER JOIN {user} u ON u.id=a.userid"
			. "\n LEFT OUTER JOIN {user} qu on qu.id=a.quote_author_id"
			. "\n LEFT OUTER JOIN {".AN_READ_TABLE."} r ON (r.annotationid=a.id AND r.userid= :annotation_user_id )";
	}
	
	/** Callback used by handlers to get standard query SELECT clause fields */	
	function get_sql_fields( &$params )
	{
		global $CFG, $DB;
		
		// Need range field if present, so instead of querying all fields by name
		// use a.*.
		$params[ 'note_author_url_base' ] = $CFG->wwwroot.'/user/view.php?id=';
		$params[ 'quote_author_url_base' ] = $CFG->wwwroot.'/user/view.php?id=';
		return ' a.* '
			. ", r.lastread AS lastread"
			. ", u.username AS username"
			. ",\n u.firstname AS firstname, u.lastname AS lastname"
	//		. ",\n concat(u.firstname, ' ', u.lastname) AS fullname"
			. ",\n ".$DB->sql_concat(':note_author_url_base','u.id')." AS note_author_url"
			. ",\n qu.username AS quote_author_username"
			. ",\n qu.id AS quote_author_id"
			. ",\n qu.firstname as quote_author_firstname, qu.lastname AS quote_author_lastname"
	//		. ",\n concat(qu.firstname, ' ', qu.lastname) AS quote_author_fullname"
			. ",\n ".$DB->sql_concat(':quote_author_url_base', 'qu.id')." AS quote_author_url";
		/*
		return " a.id AS id, a.url AS url, a.userid AS userid"
		. ", a.start_block, a.start_xpath, a.start_line, a.start_word, a.start_char"
		. ", a.end_block, a.end_xpath, a.end_line, a.end_word, a.end_char"
		. ", a.link AS link, a.link_title AS link_title, a.action AS action"
		. ", a.sheet_type AS sheet_type"
		. ", a.created AS created, a.modified AS modified"
		. ", r.lastread AS lastread"
		. ", u.username AS username"
		. ",\n u.firstname AS firstname, u.lastname AS lastname"
//		. ",\n concat(u.firstname, ' ', u.lastname) AS fullname"
		. ",\n concat('$CFG->wwwroot/user/view.php?id=',u.id) AS note_author_url"
		. ",\n a.note note, a.quote, a.quote_title AS quote_title"
		. ",\n qu.username AS quote_author_username"
		. ",\n qu.id AS quote_author_id"
		. ",\n qu.firstname as quote_author_firstname, qu.lastname AS quote_author_lastname"
//		. ",\n concat(qu.firstname, ' ', qu.lastname) AS quote_author_fullname"
		. ",\n concat('$CFG->wwwroot/user/view.php?id=',qu.id) AS quote_author_url";
		*/
	}

	/** Return a query for performing a search */
	function sql( &$params )
	{
		// The query is a UNION of separate queries, one for each type of annotation
		// This is unfortunate:  with a common table structure, one for parent-child
		// URL relationships, another with URL properties (title and owner would
		// suffice), would forgo UNIONs and simplify this code.
		
		// this was originally intended to allow more than one handler to respond to a request.
		// That may still be necessary someday, but perhaps a compound handler would be the
		// best way to respond to it.  I eliminated the handler list because YAGNI.
		$handler = $this->handler;
		
		// The handler must construct the query, which might be a single SELECT or a UNION of multiple SELECTs
		return $handler->get_sql( $params, $this );
	}
	
	/** Generate SQL for finding out how many records exist for a query */
	function count_sql( &$params )
	{
		$handler = $this->handler;
		return $handler->get_count_sql( $params, $this );
	}
	
	/** Generate a summary URL corresponding to this query */
	function summary_url( $first=0 )
	{
		global $CFG;
		
		$s = ANNOTATION_PATH."/summary.php?url=".urlencode($this->url);
		if ( null != $this->sheet_type )
			$s .= '&sheet='.urlencode($this->moodlemia->sheet_str( $this->sheet_type ) );
		if ( null != $this->text && '' != $this->text )
			$s .= '&q='.urlencode($this->text);
		if ( null != $this->user )
			$s .= '&u='.urlencode($this->user->id);
		if ( null != $this->ofuser )
			$s .= '&search-of='.urlencode($this->ofuser->id);
		if ( $this->exactmatch )
			$s .= '&match=exact';
		if ( $first )
			$s .= '&first='.$first; // doesn't cast first to int, as it might be a substitution like {first}
		if ( AN_SUMMARY_ORDER_TIME == $this->orderby )
			$s .= '&sort=time';
		return $s;
	}
	
	/** Generate a feed URL corresponding to this query */
	function get_feed_url( $format='atom' )
	{
		if ( 'atom' == $format )
			return $this->summary_url( ) . '&format=atom';
		else
			return null;
	}
}

class annotation_url_handler
{
	function annotation_url_handler( )
	{ }
	
	// This pulls together the query from the standard portions (which are passed in)
	// and from the handler-specific portions.  Some handlers may override this, e.g. in order
	// to construct a UNION.
	function get_sql( &$params, $summary )
	{
		return 'SELECT' . $summary->get_sql_fields( $params ) . $this->get_fields( $params )
			. "\n FROM" . $summary->get_sql_tables( $params ) . $this->get_tables( $params )
			. "\n WHERE" . $summary->get_sql_conds( $params ) . $this->get_conds( $params, $summary )
			. ( $summary->orderby ? "\nORDER BY $summary->orderby" : '' );
	}
	
	function get_count_sql( &$params, $summary )
	{
		return 'SELECT count(*)'
			. "\n FROM" . $summary->get_sql_tables( $params ) . $this->get_tables( $params )
			. "\n WHERE" . $summary->get_sql_conds( $params ) . $this->get_conds( $params, $summary );
	}
	
	function get_search_fields( )
	{
		return array( );
	}
}


/*
 * Oh, for a language with proper lists...
 */
/*
 A course handler is a nice enough idea, but what does it mean?  Does it retrieve all annotations for
 that course, or shoud there actually be a way to get all discussion annotations for a course?  How
 does it know about all of the sub-level entities that can be annotated (forum posts etc.)?  For now,
 I think adding an optional courseId parameter to ForumAnnotationHandler may be a better option, though
 it will look like a bit of a hack.
*/
class course_annotation_url_handler extends annotation_url_handler
{
	var $courseid;
	var $titlehtml;
	var $parenturl;
	var $parenttitlehtml;
	
	function course_annotation_url_handler( $courseid )
	{
		$this->courseid = $courseid;
		$this->titlehtml = null;
	}
	
	/** Internal function to fetch title etc. setting the following fields:
	 *  title, parenturl, parenttitle, courseid.  Will used cached results in preference
	 *  to querying the database. */
	function fetch_metadata( )
	{
		global $DB;
		
		if ( null != $this->titlehtml )
			return;
		$query = "SELECT fullname FROM {course} WHERE id = :courseid";
		$params = array( 'courseid' => $this->courseid );
		$row = $DB->get_record_sql( $query, $params );
		if ( False !== $row )
			$this->titlehtml = s( $row->fullname );
		else
			$this->titlehtml = get_string( 'unknown course', ANNOTATION_STRINGS );
		$this->parenturl = null;
		$this->parenttitlehtml = null; 
	}
	
	// Override the default implementation of get_sql
	function get_sql( &$params, $summary )
	{
		global $CFG, $DB;
		
		// First section:  discussion posts
		$params[ 'section_url_base' ] = $CFG->wwwroot.'/mod/forum/view.php?id=';
		$q = "SELECT" . $summary->get_sql_fields( $params )
			 . ",\n 'forum' AS section_type, 'content' AS row_type"
			 . ",\n f.name AS section_name"
			 . ",\n ".$DB->sql_concat(':section_url_base','f.id')." AS section_url"
			. "\n FROM" . $summary->get_sql_tables( $params ) . $this->get_tables( $params, $summary )
			. "\n WHERE" . $summary->get_sql_conds( $params ) . $this->get_conds( $params, $summary )
			. ( $summary->orderby ? "ORDER BY $summary->orderby" : '' );
		
		// If further types of objects can be annotated, additional SELECT statements must be added here
		// as part of a UNION.		
		
		return $q;
	}

	function get_tables( &$params )
	{
		$params[ 'courseid' ] = $this->courseid;
		return
			"\n INNER JOIN {forum_discussions} d ON d.course= :courseid "
			 . "\n INNER JOIN {forum_posts} p ON p.discussion=d.id AND a.object_type=".AN_OTYPE_POST." AND p.id=a.object_id "
			 . "\n INNER JOIN {forum} f ON f.id=d.forum ";
	}
	
	function get_conds( &$params, $summary )
	{
		$params[ 'object_type' ] = AN_OTYPE_POST;
		$cond = "\n  AND a.object_type= :object_type";
		if ( $summary->ofuser )
		{
			$params[ 'ofuserid' ] = (int) $summary->ofuser->id;
			$cond .= " AND a.quote_author_id= :ofuserid";
		}
		return $cond;
	}
}


class forum_annotation_url_handler extends annotation_url_handler
{
	var $f;
	var $titlehtml;
	var $parenturl;
	var $parenttitlehtml;
	var $courseid;
	var $capannotate;
	
	function forum_annotation_url_handler( $f )
	{
		$this->f = $f;
		$this->titlehtml = null;
		$this->capannotate = 'mod/forum:replypost';
	}
	
	/** Internal function to fetch title etc. setting the following fields:
	 *  title, parentUrl, parentTitle, courseId.  Will used cached results in preference
	 *  to querying the database. */
	function fetch_metadata( )
	{
		global $DB;
		
		if ( null != $this->titlehtml )
			return;
		else  {
			$query = "SELECT id, name, course FROM {forum} WHERE id= :forumid";
			$row = $DB->get_record_sql( $query, array( 'forumid' => $this->f ) );
			if ( False !== $row )
			{
				$a->name = s( $row->name );
				$this->titlehtml = get_string( 'forum_name', ANNOTATION_STRINGS, $a );
				$this->courseid = (int) $row->course;
			}
			else
			{
				$this->titlehtml = get_string( 'unknown_forum', ANNOTATION_STRINGS );
				$this->courseid = null;
			}
			$this->modulename = 'forum';
			$this->modinstanceid = (int) $this->f;
			$this->parenturl = '/course/view.php?id='.$this->courseid;
			$this->parenttitlehtml = get_string( 'whole_course', ANNOTATION_STRINGS ); 
		}
	}
	
	function get_fields( &$params )
	{
		global $CFG, $DB;
		$params[ 'section_url_base' ] = $CFG->wwwroot.'/mod/forum/discuss.php?d=';
		return ",\n 'discussion' AS section_type, 'post' AS row_type"
			. ",\n d.name AS section_name"
			. ",\n ".$DB->sql_concat(':section_url_base','d.id')." AS section_url";
	}
	
	function get_tables( &$params )
	{
		global $CFG;
		$s = '';
		if ( null == $this->f )  {
			$s = "\n LEFT OUTER JOIN {forum_posts p ON p.id=a.object_id"
				. "\n LEFT OUTER JOIN {forum_discussions d ON p.discussion=d.id";
		}
		else  {
			$params[ 'forumid' ] = $this->f;
			$s = "\n JOIN {forum_discussions} d ON d.forum= :forumid"
				. "\n JOIN {forum_posts} p ON p.discussion=d.id AND p.id=a.object_id";
		}
		return $s;
	}
	
	function get_conds( &$params, $summary )
	{
		$cond = "\n  AND a.object_type=".AN_OTYPE_POST;
		if ( $summary->ofuser )
			$cond .= " AND a.quote_author_id=".$summary->ofuser->id;
		return $cond;
	}
	
	function get_search_fields( )
	{
		return array( 'd.name' );
	}
}


class discussion_annotation_url_handler extends annotation_url_handler
{
	var $d;
	var $titlehtml;
	var $parenturl;
	var $parenttitlehtml;
	var $courseid;
	var $forumid;
	var $capannotate;
	
	function discussion_annotation_url_handler( $d )
	{
		$this->d = $d;
		$this->titlehtml = null;
		$this->capannotate = 'mod/forum:replypost';
	}
	
	/** Internal function to fetch title etc. setting the following fields:
	 *  title, parentUrl, parentTitle, courseId.  Will used cached results in preference
	 *  to querying the database. */
	function fetch_metadata( )
	{
		global $DB;
	
		if ( null != $this->titlehtml )
			return;
		elseif ( null == $this->d )  {
			$this->titlehtml = get_string( 'all_discussions', ANNOTATION_STRINGS );
			$this->parenturl = null;
			$this->parenttitlehtml = null;
			$this->courseid = null;
		}
		else  {
			$query = "SELECT d.id AS id, d.name AS name, d.course AS course, d.forum AS forum, f.name AS forum_name"
				. " FROM {forum_discussions} d "
				. " INNER JOIN {forum} f ON f.id=d.forum "
				. " WHERE d.id= :discussionid";
			$row = $DB->get_record_sql( $query, array( 'discussionid' => $this->d ) );
			$forumname = 'unknown';
			if ( False !== $row )  {
				$a->name = s( $row->name );
				$this->titlehtml = get_string( 'discussion_name', ANNOTATION_STRINGS, $a );
				$this->courseid = (int) $row->course;
				$this->forumid = (int) $row->forum;
				$forumname = $row->forum_name;
				$this->modulename = 'forum';
				$this->modinstanceid = (int) $row->forum;
			}
			else  {
				$this->titlehtml = get_string( 'unknown_discussion', ANNOTATION_STRINGS );
				$this->courseid = null;
				$this->forumid = null;
				$this->modulename = null;
				$this->modinstanceid = null;
			}
			$this->parenturl = '/mod/forum/view.php?id='.$this->forumid;
			$a->name = s( $forumname );
			$this->parenttitlehtml = get_string( 'forum_name', ANNOTATION_STRINGS, $a );
		}
	}
	
	function get_fields( &$params )
	{
		global $CFG, $DB;
		$params[ 'section_url_base' ] = $CFG->wwwroot.'/mod/forum/discuss.php?d=';
		return ",\n 'discussion' AS section_type, 'post' AS row_type"
			. ",\n d.name AS section_name"
			. ",\n ".$DB->sql_concat(':section_url_base','d.id')." AS section_url";
	}
	
	function get_tables( &$params )
	{
		$s = '';
		if ( null == $this->d )  {
			$s = "\n LEFT OUTER JOIN {forum_posts} p ON p.id=a.object_id"
			. "\n LEFT OUTER JOIN {forum_discussions} d ON p.discussion=d.id";
		}
		else  {
			$params[ 'discussionid' ] = $this->d;
			$s = "\n JOIN {forum_discussions} d ON d.id= :discussionid"
			. "\n JOIN {forum_posts} p ON p.discussion=d.id AND p.id=a.object_id";
		}
		return $s;
	}
	
	function get_conds( &$params, $summary )
	{
		$params[ 'object_type' ] = AN_OTYPE_POST;
		$cond = "\n  AND a.object_type= :object_type";
		if ( $summary->ofuser )  {
			$params[ 'ofuserid' ] = $summary->ofuser->id;
			$cond .= " AND a.quote_author_id= :ofuserid";
		}
		return $cond;
	}
	
	function get_search_fields( )
	{
		return array( 'd.name' );
	}
}

class post_annotation_url_handler extends annotation_url_handler
{
	var $p;
	var $titlehtml;
	var $parenturl;
	var $parenttitlehtml;
	var $courseid;
	var $capannotate;
	
	function post_annotation_url_handler( $p )
	{
		$this->annotation_url_handler( );
		$this->p = $p;
		$this->title = null;
		$this->capannotate = 'mod/forum:replypost';
	}
	
	function fetch_metadata( )
	{
		global $CFG, $DB;
		
		if ( null != $this->titlehtml )
			return;
		
		$query = "SELECT p.subject AS pname, d.id AS did, d.name AS dname, d.course AS course, d.forum AS forum"
			. " FROM {forum_posts} p"
			. " INNER JOIN {forum_discussions} d ON d.id=p.discussion"
			. " WHERE p.id= :postid";
		$row = $DB->get_record_sql( $query, array( 'postid' => $this->p ) );
		if ( False === $row )  {
			$this->titlehtml = get_string( 'unknown_post', ANNOTATION_STRINGS );
			$this->parenturl = null;
			$this->parenttitlehtml = null;
			$this->courseid = null;
			$this->modulename = null;
			$this->modinstanceid = null;
		}
		else  {
			$a->name = s( $row->pname );
			$this->titlehtml = get_string( 'post_name', ANNOTATION_STRINGS, $a );
			$this->parenturl = $CFG->wwwroot.'/mod/forum/discuss.php?d='.$row->did;
			$a->name = s( $row->dname );
			$this->parenttitlehtml = get_string( 'discussion_name', ANNOTATION_STRINGS, $a );
			$this->courseid = (int) $row->course;
			$this->modulename = 'forum';
			$this->modinstanceid = (int) $row->forum;
		}
	}

	function get_fields( &$params )
	{
		global $CFG, $DB;
		$params[ 'section_url_base' ] = $CFG->wwwroot.'/mod/forum/discuss.php?d=';
		return ",\n 'post' AS section_type, 'post' AS row_type"
			. ",\n d.name AS section_name"
			. ",\n ".$DB->sql_concat(":section_url_base",'d.id')." AS section_url"
			. ",\n 'post' AS object_type"
			. ",\n p.id AS object_id";
	}
	
	function get_tables( &$params )
	{
		return 	"\n LEFT OUTER JOIN {forum_posts} p ON p.id=a.object_id"
			. "\n LEFT OUTER JOIN {forum_discussions} d ON d.id=p.discussion";
	}
	
	function get_conds( &$params, $summary )
	{
		$params[ 'object_type' ] = AN_OTYPE_POST;
		$cond = "\n AND a.object_type= :object_type";
		if ( $summary->ofuser )  {
			$params[ 'ofuserid' ] = $summary->ofuser->id;
			$cond .= " AND a.quote_author_id= :ofuserid";
		}
		return $cond;
	}
}

