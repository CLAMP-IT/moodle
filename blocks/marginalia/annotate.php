<?php // handles annotation actions

/*
 * blocks/marginalia/annotate.php
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

 require_once( "../../config.php" );
require_once( 'config.php' );
require_once( 'marginalia-php/Annotation.php' );
require_once( 'marginalia-php/AnnotationService.php' );
require_once( 'marginalia-php/MarginaliaHelper.php' );
require_once( 'moodle_marginalia.php' );
require_once( 'annotation_summary_query.php' );

global $DB;

if ( $CFG->forcelogin || ANNOTATION_REQUIRE_USER )
   require_login();
 
class moodle_annotation extends Annotation
{
	function isActionValid( $action )
	{
		return null === $action || '' === $action;
	}
	
	function isSheetValid( $sheet )
	{
		return ! $sheet || 'public' == $sheet || 'private' == $sheet
			|| 'author' == $sheet;
	}	
}

class moodle_annotation_service extends AnnotationService
{
	var $extService = null;
	
	/**
	 * Figure out whether annotation is permitted here
	 */
	function can_annotate( $url )
	{
		global $USER, $miagloberror;
		$miagloberror = "none";
		
		if ( isguestuser() or ! isloggedin() )
		{
			$miagloberror = "not logged in";
			return false;
		}
		$handler = annotation_summary_query::handler_for_url( $url );
		if ( ! $handler )
		{
			$miagloberror = "not on this page " . $url;
			return false;
		}
		$handler->fetch_metadata( );
		if ( $handler->modulename && $handler->courseid )
		{
			$cm = get_coursemodule_from_instance( $handler->modulename, $handler->modinstanceid, $handler->courseid);
			if ( $cm )
			{
				$modcontext = get_context_instance( CONTEXT_MODULE, $cm->id );
				if ( ! $handler->capannotate )
				{
					$miagloberror = "never on this resource";
					return false;	// annotation of this resource is never permitted
				}
				else
					return has_capability($handler->capannotate, $modcontext);
			}
			else
			{
				$miagloberror = "no cm";
				return false;
			}
		}
		else
		{
			$miagloberror = "no handler";
			return false;
		}
	}

	function moodle_annotation_service( $userid, $extService=null )
	{
		global $CFG;
		
		$this->extService = $extService;

		// Note: Cross-site request forgery protection requires cookies, so it will not be
		// activated if $CFG->usesid=true
		$csrfprotect = ! empty( $CFG->usesid ) && $CFG->usesid;
		
		$cookiename = 'MoodleSession'.$CFG->sessioncookie; // was MoodleSessionTest.
		$cookievalue = $_COOKIE[$cookiename];
		//$_SESSION['SESSION']->session_test;
		
		$moodlemia = moodle_marginalia::get_instance( );
		AnnotationService::AnnotationService( 
			$moodlemia->get_host(),
			$moodlemia->get_service_path(),
			$moodlemia->get_install_date(),
			$userid,
			array(
				'baseUrl' => $CFG->wwwroot,
				'csrfParam' => $csrfprotect ? null : 'csrf',
				'csrfCookieValue' => $csrfprotect ? null : $cookievalue,
				'noPutDelete' => true )
			);
	}
	
	function doListAnnotations( $url, $sheet, $block, $all, $mark )
	{
		global $USER, $DB;
		
		$moodlemia = moodle_marginalia::get_instance( );
		$handler = annotation_summary_query::handler_for_url( $url );
		$sheet_type = $moodlemia->sheet_type( $sheet );
		$summary = new annotation_summary_query( array(
			'url' => $url
			,'sheet_type' => $sheet_type
			,'all' => $all ) );
		if ( $summary->error ) 
		{
			$this->httpError( 400, 'Bad Request', 'Bad URL 1' );
			return null;
		}
		elseif ( !isloggedin( ) && ANNOTATION_REQUIRE_USER )
		{
			$this->httpError( 403, 'Forbidden', 'Anonymous listing not allowed' );
			return null;
		}
		else
		{
			$queryparams = array( );
			$querysql = $summary->sql( $queryparams );
			//echo "QUERY: $querysql\n";
			//echo "PARAMS: \n";
			//foreach ( $queryparams as $key => $value )
			//	echo "  $key => $value\n";
			$annotations = Array( );
			$annotations_read = Array( );
			$annotations_unread = Array( );
			$i = 0;
			
			// Prep as much possible now for lastread updates
			$now = time( );	// lastread time
			
			// Open the record set 
			/*
			echo "Query: $querysql<br/>";
			echo "Params: $queryparams<br/>";
			foreach ( $queryparams as $p => $v )
			{
				echo "Param: $p = $v<br/>";
			}
			*/
			$annotation_set = $DB->get_recordset_sql( $querysql, $queryparams );
			foreach ( $annotation_set as $r )
			{
				$annotations[ $i ] = $moodlemia->record_to_annotation( $r );
				$annotation = $annotations[ $i ];
				if ( 'read' == $mark )
				{
					// Will do a bulk update later
					if ( $annotation->getLastRead( ) )
						$annotations_read[ ] = $annotation->id;
					else
						$annotations_unread[ ] = $annotation->id;
				}
				$i++;
			}
			// Close the recordset
			$annotation_set->close( );
			
			// Bulk update of lastread
			if ( 'read' == $mark )
			{
				if ( $annotations_read && count( $annotations_read ) )
				{
					list( $in_sql, $in_params ) = $DB->get_in_or_equal( $annotations_read, SQL_PARAMS_NAMED );
					$query = 'UPDATE {'.AN_READ_TABLE.'}'
						."\n SET lastread=:lastread"
						."\n WHERE userid=:userid AND annotationid $in_sql";
					$query_params = array( 'userid' => $USER->id, 'lastread' => (int)$now );
					$params = array_merge( $in_params, $query_params );
					$DB->execute( $query, $params );
				}
				
				if ( $annotations_unread && count( $annotations_unread ) )
				{
					list( $in_sql, $in_params ) = $DB->get_in_or_equal( $annotations_unread, SQL_PARAMS_NAMED );
					$query = 'INSERT INTO {'.AN_READ_TABLE.'}'
						."\n (annotationid, userid, firstread, lastread)"
						."\n SELECT a.id, :userid, :now1, :now2"
						."\n FROM {".AN_DBTABLE."} a"
						."\n WHERE a.id $in_sql";
					$query_params = array( 'userid' => $USER->id, 'now1' => (int)$now, 'now2' => (int)$now );
					$params = array_merge( $in_params, $query_params );
					$DB->execute( $query, $params );
				}
			}
			
			if ( $this->extService )
			{
				$extService = $this->extService;
				$extService->listAnnotations( $url, $sheet, $block, $all, $mark );
			}
			
			$format = $this->getQueryParam( 'format', 'atom' );
			$moodlemia->moodle_log( 'list', 'annotate.php?format='.$format.'&url='.$url );
			return $annotations;
		}
	}
	
	function doGetAnnotation( $id, $mark )
	{
		global $DB;
	
		$moodlemia = moodle_marginalia::get_instance( );
		
		/*
		 * use a.* now instead
		// Check whether the range column exists (for backwards compatibility)
		$range = ( column_type( 'annotation', 'range' ) ) ? ', a.range AS range ' : '';
		a.id, a.course, a.userid, a.url,
			  a.start_block, a.start_xpath, a.start_line, a.start_word, a.start_char,
			  a.end_block, a.end_xpath, a.end_line, a.end_word, a.end_char,
			  a.note, a.sheet_type, a.quote, a.quote_title, a.quote_author_id,
			  qu.id as quote_author_userid,
			  a.link, a.link_title, a.action,
			  a.created, a.modified $range
		*/
		// Caller should ensure that id is numeric
		$query = "SELECT a.*
			  FROM {".AN_DBTABLE."} a
			  JOIN {user} u ON u.id=a.userid
			  JOIN {user} qu ON qu.id=a.quote_author_id
			  WHERE a.id = :id";
		$r = $DB->get_record_sql( $query, array( 'id' => $id ) );
		if ( $r )  {
			$annotation = $moodlemia->record_to_annotation( $r );
			// Record lastread
			if ( 'read' == $mark )
			{
				$now = time( );
				$lastread = $DB->get_record_select( AN_READ_TABLE,
					'annotationid = :annotationid AND userid = :userid',
					array( 'annotationid' => $id, 'userid' => $USER->id ) );
				if ( $lastread )
				{
					$lastread->lastread = (int) $now;
					$DB->update_record( AN_READ_TABLE, $lastread );
				}
				else
				{
					$lastread = new stdClass;
					$lastread->annotationid = (int) $id;
					$lastread->userid = (int) $USER->id;
					$lastread->lastread = (int) $now;
					$lastread->firstread = (int) $now;
					$DB->insert_record( AN_READ_TABLE, $lastread );
				}
			}
			return $annotation;
		}
		else
			return null;
	}
	
	function doCreateAnnotation( $annotation )
	{
		global $USER, $DB;
		
		if ( ! $this->can_annotate( $annotation->url ) )
			$this->httpError( 403, 'Forbidden', 'User lacks permission to annotate this resource.' );
		elseif ( strlen( $annotation->getNote( ) ) > MAX_NOTE_LENGTH )
			$this->httpError( 400, 'Bad Request', 'Note too long' );
		elseif ( strlen( $annotation->getQuote( ) ) > MAX_QUOTE_LENGTH )
			$this->httpError( 400, 'Bad Request', 'Quote too long' );
		else
		{
			$moodlemia = moodle_marginalia::get_instance( );

			$time = time( );
			$annotation->setCreated( $time );
			$annotation->setModified( $time );
			$record = $moodlemia->annotation_to_record( $annotation );
			
			// Figure out the object type and ID from the url
			// Doing this here avoids infecting the caller with application-specific mumbo-jumbo
			// The cost of doing it here is low because annotations are created one-by one.  In essence,
			// this is really caching derived fields in the database to make queries easier.  (If only
			// MySQL had added views before v5).
			$profile = $moodlemia->get_profile( $annotation->getUrl( ) );
			if ( $profile )
			{
				$record->object_type = $profile->get_object_type( $annotation->getUrl( ) );
				$record->object_id = $profile->get_object_id( $annotation->getUrl( ) );
				// Find the post author
				$query = 'SELECT p.userid AS quote_author_id, p.subject AS quote_title, d.course as course'
					." FROM {forum_posts} p "
					." JOIN {forum_discussions} d ON p.discussion=d.id"
					." WHERE p.id=:object_id";
				$resultset = $DB->get_record_sql( $query, array( 'object_id' => $record->object_id ) );
				if ( $resultset && count ( $resultset ) != 0 )  {
					$record->quote_author_id = (int)$resultset->quote_author_id;
					$record->quote_title = $resultset->quote_title;
					$record->course = $resultset->course;
				}
				else  {
					$this->httpError( 400, 'Bad Request', 'No such forum post' );
					return 0;
				}
			}
			else
				echo "UNKNOWN URL ".$annotation->getUrl( )."\n";
	
			// must preprocess fields
			$id = $DB->insert_record( AN_DBTABLE, $record, true );
			
			if ( $id )  {
				// Record that this user has read the annotation.
				// This may be superfluous, as the read flag is not shown for the current user,
				// but for consistency it seems like a good idea.
				$record = new object( );
				$record->annotationid = $id;
				$record->userid = $USER->id;
				$record->firstread = $time;
				$record->lastread = $time;
				$DB->insert_record( AN_READ_TABLE, $record, true );

				if ( $this->extService )
				{
					$extService = $this->extService;
					$extService->createAnnotation( $annotation, $record );
				}
				
				// Moodle logging
				// TODO: fill in queryStr for the log
				$urlquerystr = '';
				$logurl = 'annotate.php' . ( $urlquerystr ? '?'.$urlquerystr : '' );
				$moodlemia->moodle_log( 'create', $logurl, "$id" );	// null course
				return $id;
			}
		}
		return 0;
	}
	
	function doUpdateAnnotation( $annotation )
	{
		global $USER, $DB;
		
		if ( ! $this->can_annotate( $annotation->url ) )
		{
			$this->httpError( 403, 'Forbidden', 'User lacks permission to annotate this resource.' );
			return False;
		}
		else
		{
			$moodlemia = moodle_marginalia::get_instance( );
			$urlquerystr = '';
			$annotation->setModified( time( ) );
			$record = $moodlemia->annotation_to_record( $annotation );
	
			$r = $DB->update_record( AN_DBTABLE, $record );
			
			if ( $this->extService )
			{
				$extService = $this->extService;
				$extService->updateAnnotation( $annotation, $record );
			}
			
			// Moodle logging
			$logurl = 'annotate.php' . ( $urlquerystr ? '?'.$urlquerystr : '' );
			$moodlemia->moodle_log( 'update', $logurl, "{$annotation->id}" );
	
			return $r;
		}
	}
	
	function doBulkUpdate( $oldnote, $newnote )
	{
		return false;
		
		if ( $this->extService )
		{
			$extService = $this->extService;
			$extService->bulkUpdateAnnotations( $oldnote, $newnote, $n );
		}

		header( 'Content-type: text/plain' );
		return $n;
	}
	
	function doDeleteAnnotation( $annotation )
	{
		global $USER, $DB, $miagloberror;
		
		if ( ! $this->can_annotate( $annotation->url ) )
		{
			$this->httpError( 403, 'Forbidden', 'User lacks permission to annotate.'  . $miagloberror . ", url=" . $annotation->url . ", id=" . $annotation->id );
			return False;
		}
		else
		{
			$DB->delete_records( AN_DBTABLE, array( 'id' => $annotation->id ) );
			$DB->delete_records( AN_READ_TABLE, array( 'annotationid' => $annotation->id ) );
			
			if ( $this->extService )
			{
				$extService = $this->extService;
				$extService->deleteAnnotation( $annotation->id );
			}
	
			$moodlemia = moodle_marginalia::get_instance( );
			$logurl = "annotate.php?id=".$annotation->id;
			$moodlemia->moodle_log( 'delete', $logurl, $annotation->id );
			return True;
		}
	}

	function listBodyParams( )
	{
		return MarginaliaHelper::ListBodyParams( true );
	}
}

// Load up the logger, if available
$logblock = $DB->get_record('block', array( 'name'=>'marginalia_log' ) );
$logger = null;
if ( $logblock )
{
	require_once( $CFG->dirroot.'/blocks/marginalia_log/lib.php' );
 	$logger = new marginalia_log();
}

$service = new moodle_annotation_service( isguestuser() ? null : $USER->id,
	$logger && $logger->is_active() ? $logger : null );
$service->dispatch( );

