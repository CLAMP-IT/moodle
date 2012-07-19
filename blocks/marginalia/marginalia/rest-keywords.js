/*
 * rest-keywords.js
 * Fetch a list of keywords from the server
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
 * $Id: rest-keywords.js 537 2012-05-30 21:51:38Z geof.glass $
 */

function Keyword( name, description )
{
	this.name = name;
	this.description = description;
}

function RestKeywordService( serviceUrl, canRefresh )
{
	this.serviceUrl = serviceUrl;
	this.keywords = new Array();
	this.keywordHash = new Object();
	this.canRefresh = canRefresh;
}

/**
 * Pass in a keyword list to initialize, or nothing to trigger a fetch from the server
 */
RestKeywordService.prototype.init = function( keywords, f )
{
	if ( keywords )
	{
		this.keywords = keywords;
		for ( var i = 0;  i < this.keywords.length;  ++i )
			this.keywordHash[ keywords[ i ].name ] = this.keywords[ i ];
	}
	else
	{
		var keywordService = this;
		_cacheKeywords = function( responseText )
		{
			keywordService.cacheKeywords( responseText );
			if ( f )
				f( );
		}
		this.listKeywords( _cacheKeywords );
	}
}

/**
 * Refresh the keyword list
 */
RestKeywordService.prototype.refresh = function( f )
{
	if ( this.canRefresh )
	{
		this.keywords = new Array();
		this.init( null, f );
	}
}

RestKeywordService.prototype.cacheKeywords = function( responseText )
{
	var lines = responseText.split( "\n" );
	for ( var i = 0;  i < lines.length;  ++i )
	{
		var x = lines[ i ].indexOf( ':' );
		if ( -1 != x )
		{
			var name = lines[ i ].substr( 0, x );
			var description = lines[ i ].substr( x + 1 );
			this.keywords[ i ] = new Keyword( name, description );
			this.keywordHash[ name ] = this.keywords[ i ];
			trace( 'keywords', 'Keyword:  ' + name + ' (' + description + ') ' );
		}
	}
}

RestKeywordService.prototype.isKeyword = function( word )
{
	return this.keywordHash[ word ] ? true : false;
}

RestKeywordService.prototype.getKeyword = function( word )
{
	return this.keywordHash[ word ];
}

RestKeywordService.prototype.listKeywords = function( f )
{
	var xmlhttp = domutil.createAjaxRequest( );
	xmlhttp.open( 'GET', this.serviceUrl, true );
	xmlhttp.onreadystatechange = function( ) {
		if ( xmlhttp.readyState == 4 )
		{
			if ( 200 == xmlhttp.status )
			{
				if ( f )
					f( xmlhttp.responseText );
			}
			else
				alert( "listKeywords failed with code " + xmlhttp.status + "\n" + xmlhttp.responseText );
		}
	}
	xmlhttp.send( null );
}


RestKeywordService.prototype.createKeyword = function( keyword, f )
{
	var serviceUrl = this.serviceUrl;
		
	var body
		= 'name=' + encodeURIComponent( keyword.name )
		+ '&description=' + encodeURIComponent( keyword.description );
		
	var xmlhttp = domutil.createAjaxRequest( );
	
	xmlhttp.open( 'POST', serviceUrl, true );
	xmlhttp.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
	//xmlhttp.setRequestHeader( 'Accept', 'application/xml' );
	//xmlhttp.setRequestHeader( 'Content-length', body.length );
	xmlhttp.onreadystatechange = function( ) {
		if ( xmlhttp.readyState == 4 ) {
			// No need for Safari hack, since Safari can't create annotations anyway.
			if ( xmlhttp.status == 201 ) {
				var url = xmlhttp.getResponseHeader( 'Location' );
				if ( null != f )
				{
					trace( 'annotation-service', 'Create annotation body: ' + xmlhttp.responseText );
					f( keyword, url );
				}
			}
			else {
				logError( "AnnotationService.createAnnotation failed with code " + xmlhttp.status + ":\n" + serviceUrl + "\n" + xmlhttp.responseText );
			}
			xmlhttp = null;
		}
	}
	trace( 'annotation-service', "AnnotationService.createAnnotation " + decodeURI( serviceUrl ) + "\n" + body );
	xmlhttp.send( body );
}


RestKeywordService.prototype.updateKeyword = function( keyword, f )
{
	var serviceUrl = this.serviceUrl;
	serviceUrl += this.niceUrls ? ( '/' + keyword.name ) : ( '?name=' + keyword.name );
	
	var body = '';
	body = 'description=' + encodeURIComponent( keyword.description );

	var xmlhttp = domutil.createAjaxRequest( );
	xmlhttp.open( 'PUT', serviceUrl, true );
	xmlhttp.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
	//xmlhttp.setRequestHeader( 'Accept', 'application/xml' );
	//xmlhttp.setRequestHeader( 'Content-length', body.length );
	xmlhttp.onreadystatechange = function( ) {
		if ( xmlhttp.readyState == 4 ) {
			// Safari is braindead here:  any status code other than 200 is converted to undefined
			// IE invents its own 1223 status code
			// See http://www.trachtenberg.com/blog/?p=74
			if ( 204 == xmlhttp.status || xmlhttp.status == null || xmlhttp.status == 1223 )
			{
				if ( null != f )
				{
					this.keywordHash[ keyword.name ] = keyword;
					f( keyword, xmlhttp.responseText );
				}
			}
			else
				logError( "KeywordService.updateKeyword failed with code " + xmlhttp.status + " (" + xmlhttp.statusText + ")\n" + xmlhttp.statusText + "\n" + xmlhttp.responseText );
			xmlhttp = null;
		}
	}
	trace( 'keyword-service', "KeywordService.updateKeyword " + decodeURI( serviceUrl ) );
	trace( 'keyword-service', "  " + body );
	xmlhttp.send( body );
}

RestKeywordService.prototype.deleteKeyword = function( name, f )
{
	var serviceUrl = this.serviceUrl;
	serviceUrl += this.niceUrls ? ( '/' + name ) : ( '?name=' + name );
	
	var xmlhttp = domutil.createAjaxRequest( );
	xmlhttp.open( 'DELETE', serviceUrl, true );
	//xmlhttp.setRequestHeader( 'Accept', 'application/xml' );
	xmlhttp.onreadystatechange = function( ) {
		if ( xmlhttp.readyState == 4 ) {
			// Safari is braindead here:  any status code other than 200 is converted to undefined
			// IE invents its own 1223 status code
			if ( 204 == xmlhttp.status || xmlhttp.status == null || xmlhttp.status == 1223 ) {
				if ( null != f )
					f( name, xmlhttp.responseXML );
			}
			else
				logError( "AnnotationService.deleteAnnotation failed with code " + xmlhttp.status + "\n" + xmlhttp.responseText );
			xmlhttp = null;
		}
	}
	trace( 'annotation-service', "AnnotationService.deleteAnnotation " + decodeURI( serviceUrl ) );
	xmlhttp.send( null );
}

