<?php

/*
 * AnnotationService.php
 * Virtual base class for handling annotation HTTP requests.
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

require_once( "SequenceRange.php" );
require_once( "XPathRange.php" );
require_once( "RangeInfo.php" );
require_once( "AnnotationUserSummary.php" );
require_once( "MarginaliaHelper.php" );

/**
 * This class is incomplete;  it must be subclassed with implementations of the
 * following methods:  
 * - listAnnotations
 * - getAnnotation
 * - createAnnotation
 * - updateAnnotation
 * - deleteAnnotation
 * - feedUrl - returns the feed URL for this request
 * A subclass may also wish to override:
 * - newAnnotation if it uses a custom annotation class
 * - beginRequest to initialize database resources
 * - endRequest to free database resources
 */
class AnnotationService
{
	var $host;			// Name of this host
	var $servicePath;	// URL to the annotation service
	var $installDate;	// Date the application was first installed (needed by Atom)
	var $errorSent;		// Only send one HTTP error - if this is set, don't send another
	var $niceUrls;		// True or False
	var $currentUserId;	// ID (username) of the current user, or null if none
	var $allowAnyUserPatch;	// Allow any user to submit patch updates
	
	function AnnotationService( $host, $servicePath, $installDate, $currentUserId, $args=null )
	{
		$this->host = $host;
		$this->servicePath = $servicePath;
		$this->installDate = $installDate;
		$this->currentUserId = $currentUserId;

		$this->errorSent = False;
		
		// default for optional arguments
		$this->baseUrl = null;
		$this->niceUrls = False;
		$this->csrfCookie = null;
		$this->csrfCookieValue = null;
		$this->noPutDelete = False;
		
		if ( $args )
		{
			foreach ( array_keys( $args) as $arg )
			{
				$value = $args[ $arg ];
				switch ( $arg )
				{
					// Allow any user to submit updates (patches).  Should only
					// ever be set for the administration user.  The code at
					// least checks to make sure only range data is affected.
					case 'allowAnyUserPatch':
						$this->allowAnyUserPatch = $value;
						break;
					
					// The client will submit relative URLs, with this prefix stripped off
					// Must also be configured for client
					case 'baseUrl':
						$this->baseUrl = $value;
						break;
						
					// Use nice URLs (e.g. annotate/21 instead of annotate.php?id=21
					// Probably won't work properly as there are no active implementations
					// Must also be configured for client
					case 'niceUrls':
						$this->niceUrls = $value;
						break;
						
					// Use HTTP POST instead of PUT and DELETE for servers that refuse PUT and DELETE.
					// The actual method will be sent as a GET parameter.  This is really bad, because 
					// then the URL doesn't match the resource... but then so is not having PUT and 
					// DELETE.  The body can't be used, as in future it probably won't be 
					// application/x-www-url-encoded.
					case 'noPutDelete':
						$this->noPutDelete = $value;
						break;
						
					// The name of the session cookie and its value
					// Used to prevent cross-site request forgeries
					// Client must also configure csrfCookie (but definitely *not* csrfCookieValue)
					case 'csrfCookie':
						$this->csrfCookie = $value;
						break;
					case 'csrfCookieValue':
						$this->csrfCookieValue = $value;
						break;
				}
			}
		}
	}
	
	// Factory method, may be overriden:
	function newAnnotation( )
	{  return new Annotation( );  }
	
	// Request resource allocation, may be overriden:
	function beginRequest( )
	{  return True;  }
	
	// Request resource freeing, may be overriden:
	function endRequest( )
	{ }
	
	// OJS needs to replace this with its own version:
	function getQueryParam( $name, $default )
	{
		return MarginaliaHelper::getQueryParam( $name, $default );
	}
	
	function listBodyParams( )
	{
		return MarginaliaHelper::listBodyParams( );
	}


	// Verify that the request was sent from within a valid session
	// Used to prevent cross-site request forgery
	// Parameter name is always "csrf"
	function verifySession( $params )
	{
		return ! $this->csrfCookie ||
			( array_key_exists( 'csrf', $params )
			&& $this->csrfCookieValue == $params[ 'csrf' ] );
	}
	
	function parseAnnotationId( )
	{
		$urlString = $_SERVER[ 'REQUEST_URI' ];
		$pos = strpos( $urlString, $this->servicePath );
		if ( False == $pos )
			$id = $this->getQueryParam( 'id', False );
		else
			$id = (int) substr( $urlString, $pos + strlen( $this->servicePath ) + 1 );
		if ( $id == '' || $id == 0 || !MarginaliaHelper::isnum( $id ) )
			return False;
		return $id;
	}

	
	function dispatch( $method=null )
	{
		$id = $this->parseAnnotationId( );
		if ( ! $method )
			$method = $_SERVER[ 'REQUEST_METHOD' ];
		if ( $this->noPutDelete && 'POST' == $method )
		{
			$method = $_GET[ 'method' ];
			/* Tried calculating what to do by looking at parameters.  Should work in most cases,
			 * but isn't a long term solution.  Use GET parameter instead - which is semantically
			 * bad, because it changes the resource URL depending on the operation, but then not
			 * having all the methods is also semantically bad so what can you do.
			 *
			// POST is being used instead of PUT or DELETE, so figure out actual method and operation
			if ( False === $id )
			{
				// Must be POST or PUT (bulkUpdate)
				$getParamNames = array_keys( $_GET );
				$searchParamNames = array( 'note' );
				$result = array_intersect( $getParamNames, $searchParamNames );
				if ( $result )
					$method = 'PUT';
				else
					$method = 'POST';
			}
			else
			{
				// Must be DELETE or PUT (update)
				if ( $_SERVER[ 'CONTENT_LENGTH' ] > 0 )
					$method = 'PUT';
				else
					$method = 'DELETE';
			}
			*/
		}
		
		switch( $method )
		{
			// get a list of annotations
			case 'GET':
				if ( $this->beginRequest( ) )
				{
					if ( False === $id )
						$this->listAnnotations( );
					else
						$this->getAnnotation( $id );
					$this->endRequest( );
				}
				else
					$this->httpError( 500, 'Internal Error', 'Unable to handle request' );
				break;
			
			// create a new annotation
			case 'POST':
				if ( ! $this->currentUserId )
					$this->httpError( 403, 'Forbidden', 'Must be logged in' );
				elseif ( $this->beginRequest( ) )
				{
					$this->createAnnotation( );
					$this->endRequest( );
				}
				else
					$this->httpError( 500, 'Internal Error', 'Unable to handle request' );
				break;
			
			// update an existing annotation
			case 'PUT':
				if ( ! $this->currentUserId )
					$this->httpError( 403, 'Forbidden', 'Must be logged in' );
				// No ID => bulk update
				// The logic here is that the PUT applies to the named resource - that being a collection
				// defined by the search parameters.  However, that's kind of fishy - as I read the spec,
				// PUT should really be replacing the identified resource.  I should be using POST.  Will
				// fix someday (at this point, the matter seems purely theoretical).
				elseif ( False === $id )
				{
					$this->bulkUpdate( );
					$this->endRequest( );
				}
				// ID => individual update
				elseif ( $this->beginRequest( ) )
				{
					$this->updateAnnotation( $id );
					$this->endRequest( );
				}
				else
					$this->httpError( 500, 'Internal Error', 'Unable to handle request' );
				break;
			
			// delete an existing annotation
			case 'DELETE':
				if ( False === $id )
					$this->httpError( 400, 'Bad Request', 'No such annotation #'.(int)$id );
				elseif ( ! $this->currentUserId )
					$this->httpError( 403, 'Forbidden', 'Must be logged in' );
				elseif ( $this->beginRequest( ) )
				{
					$this->deleteAnnotation( $id );
					$this->endRequest( );
				}
				else
					$this->httpError( 500, 'Internal Error', 'Unable to handle request' );
				break;
			
			default:
				header( "HTTP:/1.1 405 Method Not Allowed" );
				header( "Allow:  GET, POST, PUT, DELETE" );
				echo "<h1>405 Method Not Allowed</h1>Allow: GET, POST, PUT, DELETE";
		}
	}
	
	
	function listAnnotations()
	{
		$format = $this->getQueryParam( 'format', 'atom' );
		$url = $this->getQueryParam( 'url', null );
		$sheet = $this->getQueryParam( 'sheet', null );
		$block = $this->getQueryParam( 'block', null );
		$block = $block ? new SequencePoint( $block ) : null;
		$all = $this->getQueryParam( 'all', 'no' ) == 'yes' ? true : false;
		$mark = $this->getQueryParam( 'mark', null );
		
/*		if ( $url == null || $url == '' )
			$this->httpError( 400, 'Bad Request', 'Bad URL' );
		else
		{
*/			$annotations = $this->doListAnnotations( $url, $sheet, $block, $all, $mark );
			
			if ( null === $annotations )
				$this->httpError( 500, 'Internal Service Error', 'Failed to list annotations' );
			elseif ( 'atom' == $format )
			{
				$feedUrl = '';
				if ( $url )
					$feedUrl .= ( $feedUrl ? '&' : '?' ) . 'url=' . urlencode($url);
				if ( $sheet )
					$feedUrl .= ( $feedUrl ? '&' : '?' ) . 'sheet=' . urlencode( $sheet );
				if ( $format )
					$feedUrl .= ( $feedUrl ? '&' : '?' ) . 'format=' . urlencode( $format );
				if ( $block )
					$feedUrl .= ( $feedUrl ? '&' : '?' ) . 'block=' . urlencode( $block->toString( ) );
					
				$this->getAtom( $annotations, $this->servicePath . $feedUrl, $this->baseUrl );
			}
			elseif ( 'blocks' == $format )
				$this->getBlocks( $annotations, $url );
			elseif ( 'summary' == $format )
				$this->getSummary( $annotations, $url );
			else
				$this->httpError( 400, 'Bad Request', 'Unknown format' );
//		}
	}
	
	/**
	 * Retrieve a single annotation by ID
	 */
	function getAnnotation( $id )
	{
		$format = (int) $this->getQueryParam( 'format', null );
		$mark = $this->getQueryParam( 'mark', null );

		$annotation = $this->doGetAnnotation( $id, $mark );
			
		if ( null === $annotation )
			$this->httpError( 404, 'Not Found Error', 'No such annotation' );
		else
		{
			$annotations = array( $annotation );
			if ( null == $format || 'atom' == $format )
			{
				$feedUrl = $this->servicePath;
				if ( $this->niceUrls )
					$feedUrl .= '/' . urlencode( $id );
				else
					$feedUrl .= '?id=' . urlencode( $id );
				$this->getAtom( $annotations, $feedUrl, $this->baseUrl );
			}
			else
				$this->httpError( 400, 'Bad Request', 'Format unknown or unsupported for individual annotations' );
		}
	}
	
	
	function createAnnotation()
	{
		$params = $this->listBodyParams( );

		// Check for cross-site request forgery
		if ( ! $this->verifySession( $params ) )
		{
			$this->httpError( 403, 'Forbidden', 'Illegal request.' );
			return;
		}
		
		// Parse annotation values
		$annotation = $this->newAnnotation( );
		$error = MarginaliaHelper::annotationFromParams( $annotation, $params );
		if ( $error )
			$this->httpError( MarginaliaHelper::httpResultCodeForError( $error ), 'Error', $error );
		else
		{
			$annotation->setUserId( $this->currentUserId );
			$annotation->setCreated( date( 'Y-m-d H:m' ) );
			$id = $this->doCreateAnnotation( $annotation );
			if ( $id != 0 )
			{
				$feedUrl = $this->servicePath;
				if ( $this->niceUrls )
					$feedUrl .= '/' . urlencode( $id );
				else
					$feedUrl .= '?id=' . urlencode( $id );
				header( 'HTTP/1.1 201 Created' );
				header( "Location: $this->servicePath/$id" );
				$this->getAtom( array( $annotation ), $feedUrl, $this->baseUrl );
			}
			else
				$this->httpError( 500, 'Internal Service Error', 'Create failed' );	
		}
	}
	
	
	function updateAnnotation( $id )
	{
		$params = $this->listBodyParams( );
		
		// Check for cross-site request forgery
		if ( ! $this->verifySession( $params ) )
		{
			$this->httpError( 403, 'Forbidden', 'Illegal request' );
			return;
		}

		$annotation = $this->doGetAnnotation( $id );
		if ( null === $annotation )
			$this->httpError( 404, 'Not Found', 'No such annotation' );
		elseif ( $this->currentUserId != $annotation->getUserId( ) && ! $this->allowAnyUserPatch )
			$this->httpError( 403, 'Forbidden', 'Not your annotation' );
		else
		{
			// If this is a patch update by another user, restrict the update to ranges parameters only
			if ( $this->currentUserId != $annotation->getUserId( ) )
			{
				$newParams = array( );
				if ( array_key_exists( $params[ 'sequence-range' ] ) )
					$newParams[ 'sequence-range' ] = $params[ 'sequence-range' ];
				if ( array_key_exists( $params[ 'xpath-range' ] ) )
					$newParams[ 'xpath-range' ] = $params[ 'xpath-range' ];
				$params = $newParams;
			}
			
			// Set only the fields that were passed in
			$error = $annotation->fromArray( $params );
			if ( $error )
				$this->httpError( MarginaliaHelper::httpResultCodeForError( $error ), 'Error', $error);
			else
			{
				// Update the annotation in the database
				if ( $this->doUpdateAnnotation( $annotation ) )
					header( 'HTTP/1.1 204 Updated' );
				else
					$this->httpError( 500, 'Internal Service Error', 'Update failed' );
			}
		}
	}

	
	function bulkUpdate( )
	{
		$oldNote = $this->getQueryParam( 'note', null );
		$bodyParams = $this->listBodyParams( );
		
		// Check for cross-site request forgery
		if ( ! $this->verifySession( $bodyParams ) )
		{
			$this->httpError( 403, 'Forbidden', 'Illegal request' );
			return;
		}

		if ( ! method_exists( $this, 'doBulkUpdate' ) )
			$this->httpError( 400, 'Bad Request', 'This service does not support bulk updates' );
		elseif ( null === $oldNote )
			$this->httpError( 400, 'Bad Request', 'Bad bulk query' );
		elseif ( ! array_key_exists( 'note', $bodyParams ) )
			$this->httpError( 400, 'Bad Request', 'Bad bulk substitution' );
		else
		{
			$newNote = $bodyParams[ 'note' ];
			$n = $this->doBulkUpdate( $oldNote, $newNote );
			if ( False === $n )
				$this->httpError( 500, 'Internal Service Error', 'Bulk update failed' );
			else
			{
				header( 'HTTP/1.1 200 Bulk Update Complete' );
				echo htmlspecialchars( '' + $n );
			}
		}
	}
			
	function deleteAnnotation( $id )
	{
		// Check for cross-site request forgery
		// unfortunately the csrf param is in the GET params
		if ( ! $this->verifySession( $_GET ) )
		{
			$this->httpError( 403, 'Forbidden', 'Illegal request' );
			return;
		}

		$annotation = $this->doGetAnnotation( $id, null );
		if ( null === $annotation )
			$this->httpError( 404, 'Not Found', 'No such annotation' );
		elseif ( $this->currentUserId != $annotation->getUserId( ) )
			$this->httpError( 403, 'Forbidden', 'Not your annotation' );
		elseif ( $this->doDeleteAnnotation( $annotation ) )
			header( "HTTP/1.1 204 Deleted" );
		else
			$this->httpError( 500, 'Internal Service Error', 'Deleted failed' );
	}

	/**
	 * Emit an Atom document for a list of annotations
	 * The annotations should already be sorted
	 */
	function getAtom( $annotations, $feedUrl, $baseUrl )
	{
		$feedLastModified = MarginaliaHelper::getLastModified( $annotations, $this->installDate );
		$feedTagUri = "tag:" . $this->host . ',' . date( 'Y-m-d', $this->installDate ) . ":annotation";
		
		header( 'Content-Type: application/xml' );
		echo( '<?xml version="1.0" encoding="utf-8"?>' . "\n" );
		echo MarginaliaHelper::generateAnnotationFeed( $annotations, $feedTagUri, $feedLastModified, $this->servicePath, $this->host, $feedUrl, $baseUrl );
	}

	
	function getBlocks( $annotations, $url )
	{
		$infos = MarginaliaHelper::annotationsToRangeInfos( $annotations );
		for ( $i = 0;  $i < count( $infos );  ++$i )
			$infos[ $i ]->makeBlockLevel( );
		$infos = MarginaliaHelper::mergeRangeInfos( $infos );
		
		header( 'Content-Type: application/xml' );
		echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
		echo MarginaliaHelper::getRangeInfoXml( $infos );
	}
	
	function getSummary( $annotations, $url )
	{
		$summary = new AnnotationUserSummary( $annotations, $url );
		header( 'Content-Type: application/xml' );
		echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
		echo $summary->toXml( );
	}
	
	function httpError( $code, $message, $description )
	{
		if ( ! $this->errorSent )
		{
			header( "HTTP/1.1 $code $message" );
			echo ( "<h1>$message</h1>\n$description" );
			$this->errorSent = True;
		}
	}	
}


?>
