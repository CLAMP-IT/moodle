<?php

/*
 * KeywordService.php
 * Virtual base class for handling keyword HTTP requests.
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

require_once( "MarginaliaHelper.php" );

/**
 * This class is incomplete;  it must be subclassed with implementations of the
 * following methods: 
 * - listKeywords
 * - getKeyword
 * - createKeyword
 * - updateKeyword
 * - deleteKeyword 
 * A subclass may also wish to override:
 * - beginRequest to initialize database resources
 * - endRequest to free database resources
 */
class KeywordService
{
	var $host;			// Name of this host
	var $servicePath;	// URL to the annotation service
	var $errorSent;		// Only send one HTTP error - if this is set, don't send another
	var $niceUrls;		// True or False
	var $currentUserId;	// ID of the current user, or null if none
	
	function KeywordService( $host, $servicePath, $currentUserId, $baseUrl='', $niceUrls=False )
	{
		$this->host = $host;
		$this->servicePath = $servicePath;
		$this->errorSent = False;
		$this->niceUrls = $niceUrls;
		$this->currentUserId = $currentUserId;
		$this->baseUrl = $baseUrl;
	}
	
	// Factory method, may be overriden:
	function newKeyword( )
	{  return new MarginaliaKeyword( );  }
	
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

	
	function parseKeywordName( )
	{
		$urlString = $_SERVER[ 'REQUEST_URI' ];
		$pos = strpos( $urlString, $this->servicePath );
		if ( False == $pos )
			$name = $this->getQueryParam( 'name', False );
		return $name;
	}

	
	function dispatch( $method=null )
	{
		if ( ! $method )
			$method = $_SERVER[ 'REQUEST_METHOD' ];
		
		$name = $this->parseKeywordName( );
		switch( $method )
		{
			// get a list of keywords
			case 'GET':
				if ( $this->beginRequest( ) )
				{
					if ( False === $name )
						$this->listKeywords( );
					else
						$this->getKeyword( $name );
					$this->endRequest( );
				}
				else
					$this->httpError( 500, 'Internal Error', 'Unable to handle request' );
				break;
			
			// create a new keyword
			case 'POST':
				if ( ! $this->currentUserId )
					$this->httpError( 403, 'Forbidden', 'Must be logged in' );
				else if ( $this->beginRequest( ) )
				{
					$this->createKeyword( );
					$this->endRequest( );
				}
				else
					$this->httpError( 500, 'Internal Error', 'Unable to handle request' );
				break;
			
			// update an existing keyword
			case 'PUT':
				if ( False === $name )
					$this->httpError( 400, 'Bad Request', 'No such keyword '.htmlspecialchars($name) );
				elseif ( ! $this->currentUserId )
					$this->httpError( 403, 'Forbidden', 'Must be logged in' );
				elseif ( $this->beginRequest( ) )
				{
					$this->updateKeyword( $name );
					$this->endRequest( );
				}
				else
					$this->httpError( 500, 'Internal Error', 'Unable to handle request' );
				break;
			
			// delete an existing annotation
			case 'DELETE':
				if ( False === $name )
					$this->httpError( 400, 'Bad Request', 'No such keyword '.htmlspecialchars($name) );
				elseif ( ! $this->currentUserId )
					$this->httpError( 403, 'Forbidden', 'Must be logged in' );
				elseif ( $this->beginRequest( ) )
				{
					if ( $this->doDeleteKeyword( $name ) )
						header( "HTTP/1.1 204 Deleted" );
					else
						$this->httpError( 500, 'Internal Error', 'Delete failed' );
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
	
	
	function listKeywords()
	{
		$keywords = $this->doListKeywords( );
		
		if ( null === $keywords )
			$this->httpError( 500, 'Internal Service Error', 'Failed to list keywords' );
		else
		{
			header( 'Content-Type: text/plain' );
			foreach ( $keywords as $keyword )
				echo htmlspecialchars($keyword->name).':'.htmlspecialchars($keyword->description)."\n";
		}
	}
	
	/**
	 * Retrieve a single keyword
	 */
	function getAnnotation( $id )
	{
		$keyword = $this->doGetKeyword( $name );
			
		if ( null === $keyword )
			$this->httpError( 404, 'Not Found Error', 'No such keyword' );
		else
		{
			header( 'Content-Type: text/plain' );
			echo htmlspecialchars($keyword->name).':'.htmlspecialchars($keyword->description)."\n";
		}
	}
	
	
	function createKeyword()
	{
		$params = $this->listBodyParams( );

		$keyword = $this->newKeyword( );
		$keyword->name = $params[ 'name' ];
		$keyword->description = $params[ 'description' ];
		
		// Validate that name is safe!
		if ( preg_match( '/:/', $keyword->name ) )
			$this->httpError( 400, 'Bad Request', 'Keyword may not include :' );
		elseif ( $this->doCreateKeyword( $keyword ) )
		{
			$feedUrl = $this->servicePath;
			if ( $this->niceUrls )
				$feedUrl .= '/' . urlencode( $keyword->name );
			else
				$feedUrl .= '?name=' . urlencode( $keyword->name );
			header( 'HTTP/1.1 201 Created' );
			header( "Location: $this->servicePath/".urlencode( $keyword->name ) );
		}
		else
			$this->httpError( 500, 'Internal Service Error', 'Create failed' );	
	}
	
	
	function updateKeyword( $name )
	{
		$params = $this->listBodyParams( );
		
		$keyword = $this->doGetKeyword( $name );
		if ( null === $keyword )
			$this->httpError( 404, 'Not Found', 'No such keyword' );
		else
		{
			$keyword->description = $params[ 'description' ];
			if ( $this->doUpdateKeyword( $keyword ) )
				header( 'HTTP/1.1 204 Updated' );
			else
				$this->httpError( 500, 'Internal Service Error', 'Update failed' );
		}
	}

	
	function deleteKeyword( $name )
	{
		$keyword = $this->doGetKeyword( $name );
		if ( null === $keyword )
			$this->httpError( 404, 'Not Found', 'No such keyword' );
		elseif ( $this->doDeleteKeyword( $name ) )
			header( "HTTP/1.1 204 Deleted" );
		else
			$this->httpError( 500, 'Internal Service Error', 'Deleted failed' );
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
