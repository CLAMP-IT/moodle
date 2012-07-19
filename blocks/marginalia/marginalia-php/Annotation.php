<?php

/*
 * Annotation.php
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

require_once( 'SequenceRange.php' );
require_once( 'XPathRange.php' );

/**
 * Class representing an annotation
 * For an extra bit of safety, setter methods typecast some fields and check
 * to see whether others are valid, and (silently) fail if they aren't.
 * That's right I'm paranoid - who knows, someone could get into the database 
 * but this would still protect users.
 */
class Annotation
{
	function Annotation( )
	{
		$this->id = null;
		$this->url = null;
		$this->userId = null;
		$this->userName = null;
		$this->sequenceRange = null;
		$this->xpathRange = null;
		$this->note = null;
		$this->sheet = null;
		$this->action = null;
		$this->quote = null;
		$this->quoteTitle = null;
		$this->quoteAuthorId = null;
		$this->quoteAuthorName = null;
		$this->link = null;
		$this->linkTitle = null;
		$this->created = null;
		$this->modified = null;
		$this->version = 1;
		$this->lastRead = null;
	}
	
	/** This method is intended to be called when an annotation is created via 
	 * a POST or PUT operation.  An associative array contains the values of
	 * various fields in string format.  If a field is not present in the array, 
	 * it will not be set.  The userid field cannot be set this way, because 
	 * that is session information (i.e. it must be the current user).
	 */
	function fromArray( $params )
	{
		return MarginaliaHelper::annotationFromParams( $this, $params );
	}

	function setAnnotationId( $id )
	{ $this->id = (int) $id; }
	
	function getAnnotationId( )
	{ return $this->id; }
	
	function setUrl( $url )
	{
		if ( MarginaliaHelper::isUrlSafe( $url ) )
			$this->url = $url;
	}
	
	function getUrl( )
	{ return $this->url; }
	
	function setUserId( $id )
	{ $this->userId = $id; }
	
	function getUserId( )
	{ return $this->userId; }
	
	function setUserName( $name )
	{ $this->userName = $name; }
	
	function getUserName( )
	{ return $this->userName; }
	
	function setSequenceRange( &$range )
	{ $this->sequenceRange = $range; }
		
	function getSequenceRange( )
	{ return $this->sequenceRange; }
	
	function setXPathRange( &$range )
	{ $this->xpathRange = $range; }
	
	function getXPathRange( )
	{ return $this->xpathRange; }
	
	function setNote( $note )
	{ $this->note = $note; }
	
	function getNote( )
	{ return $this->note; }
	
	function setAction( $action )
	{
		if ( $this->isActionValid( $action ) )
			$this->action = $action;
	}
	
	function getAction( )
	{ return $this->action; }
	
	function setSheet( $sheet )
	{
		if ( $this->isSheetValid( $sheet ) )
			$this->sheet = $sheet;
	}
	
	function getSheet( )
	{ return $this->sheet; }
	
	function setQuote( $quote )
	{ $this->quote = $quote; }
	
	function getQuote( )
	{ return $this->quote; }
	
	function setQuoteTitle( $quoteTitle )
	{ $this->quoteTitle = $quoteTitle; }
	
	function getQuoteTitle( )
	{ return $this->quoteTitle; }
	
	function setQuoteAuthorId( $quoteAuthorId )
	{ $this->quoteAuthorId = $quoteAuthorId; }
	
	function getQuoteAuthorId( )
	{ return $this->quoteAuthorId; }
	
	function setQuoteAuthorName( $quoteAuthorName )
	{ $this->quoteAuthorName = $quoteAuthorName; }
	
	function getQuoteAuthorName( )
	{ return $this->quoteAuthorName; }
	
	function setLink( $link )
	{
		if ( ! $link || MarginaliaHelper::isUrlSafe( $link ) )
			$this->link = $link;
	}
	
	function getLink( )
	{ return $this->link; }
	
	function setLinkTitle( $title )
	{ $this->linkTitle = $title; }
	
	function getLinkTitle( )
	{ return $this->linkTitle; }
	
	function setCreated( $created )
	{  $this->created = is_string( $created ) ? strtotime( $created ) : $created;  }
	
	function getCreated( )
	{ return $this->created; }
	
	function setModified( $modified )
	{ $this->modified = is_string( $modified ) ? strtotime( $modified ) : $modified; }
	
	function getModified( )
	{ return $this->modified; }
	
	function setVersion( $version )
	{ $this->version = (int) $version; }
	
	function getVersion( )
	{ return (int) $this->version; }
	
	function setLastRead( $lastread )
	{ $this->lastRead = is_string( $lastread ) ? strtotime( $lastread ) : $lastread; }
	
	function getLastRead( )
	{ return $this->lastRead; }
	
	/**
	 * Convert to an Atom entry
	 */
	function toAtom( $tagHost, $servicePath )
	{
		return MarginaliaHelper::annotationToAtom( $this, $tagHost, $servicePath );
	}
	
	/** Check whether an action value is valid (overrideable) */
	function isActionValid( $action )
	{
		return null === $action || '' === $action || 'edit' == $action;
	}
	
	/** Check whether a sheet value is valid (overrideable) */
	function isSheetValid( $sheet )
	{
		return ! $sheet || 'public' == $sheet || 'private' == $sheet;
	}	
}

?>
