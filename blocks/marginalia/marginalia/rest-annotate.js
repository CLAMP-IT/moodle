/*
 * rest-annotate.js
 * REST implementation of the connection to the annotation back-end
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
 * $Id: rest-annotate.js 537 2012-05-30 21:51:38Z geof.glass $
 */


/**
 * Initialize the REST annotation service
 */
function RestAnnotationService( serviceUrl, features )
{
	this.noPutDelete = false;
	
	// Fixed service URL.  Use this if not using nice URLs.
	if ( 'string' == typeof( serviceUrl ) )
		this.urlTemplate = new restutil.UrlTemplate( serviceUrl );
	else
		this.urlTemplate = new restutil.UrlTemplateDictionary( serviceUrl );
	
	if ( features )
	{
		for ( feature in features )
		{
			var value = features[ feature ];
			switch ( feature )
			{
				// Name of cookie to use for preventing cross-site request forgery
				case 'csrfCookie':
					this.csrfCookie = value;
					break;
				
				// Use HTTP POST instead of PUT and DELETE.  In this case, the operation could be determined as follows:
				//   create:  no id or search parameters in URL, body contains new annotation data
				//   delete: id in URL with no body
				//   update:  id in URL, body contains new annotation data
				//   bulkUpdate:  search parameters in URL, body contains substitution data
				// But that's a bad idea, as it rules out certain future possible parameters to calls.
				// Why not pass method=PUT or method=DELETE in body or URL?
				// - in URL is incorrect as this does not help identify the resource
				// - in body is incorrect if mime type is not application/x-www-url-encoded;  makes no sense in XML body
				case 'noPutDelete':
					this.noPutDelete = value;
					break;
					
				// include curuser as get parameter to requests
				// should be set to actual user ID
				// cheap way to do simple logging
				case 'sendCurUser':
					this.sendCurUser = value;
					break;
					
				default:
					if ( typeof( this[ feature ] ) != 'undefined' )
						throw 'Attempt to override feature: ' + feature;
					else
						this[ feature ] = value;
			}
		}
	}
}

/**
 * Fetch a list of annotations from the server
 */
// Recent change (2009-09-29):  userid replaced by sheet.  This filters which
// annotations are fetched.
RestAnnotationService.prototype.listAnnotations = function( url, sheet, params, ok, fail )
{
	var block = params.block;
	var mark = params.mark;
	var since = params.since;
	
	var serviceUrl = this.urlTemplate.match( [
		[ 'sheet', sheet, true ],
		[ 'mark', mark ],
		[ 'since', since ? o2s.format( since, 'yyyy-mm-ddTHH:ii:ss' ) : '' ],
		[ 'format', 'atom' ],
		[ 'url', url ],
		[ 'curuser', this.sendCurUser, this.sendCurUser ]
	], 'listAnnotations' );
	if ( ! serviceUrl )
		throw "No matching service URL template for listAnnotations.";	

	fail2 = function( status, text ) {
		logError( "AnnotationService.listAnnotations failed with code " + status + ":\n" + serviceUrl + "\n" + text );
		if ( fail )
			fail( status, text );
	};
	restutil.getResource( serviceUrl, ok, fail2, { okXml: true } );
	trace( 'annotation-service', "AnnotationService.listAnnotations " + decodeURI( serviceUrl ) );
}

/**
 * Create an annotation on the server
 * When successful, calls a function f with one parameter:  the URL of the created annotation
 */
RestAnnotationService.prototype.createAnnotation = function( annotation, ok, fail )
{
	// Small flaw here:  the url gets sent both in the query string *and* in the
	// body when niceurls are not in use.  When the URLs are nice, it can be part of
	// the URL path.  Though that limits to annotations only of the current site.
	// Hmmm.
	var serviceUrl = this.urlTemplate.match( [
		[ 'url', annotation.getUrl( ) ],
		[ 'method', 'POST', this.noPutDelete ],
		[ 'curuser', this.sendCurUser, this.sendCurUser ]
	], 'createAnnotation' );
	if ( ! serviceUrl )
		throw "No matching service URL template for createAnnotation.";

	var params = [
		[ 'url', annotation.getUrl( ), true ],
		[ 'note', annotation.getNote( ), true ],
		[ 'sheet', annotation.getSheet( ), true ],
		[ 'quote', annotation.getQuote( ), true ],
		[ 'quote_title', annotation.getQuoteTitle( ) ],
		[ 'quote_author_id', annotation.getQuoteAuthorId( ) ],
		[ 'quote_author_name', annotation.getQuoteAuthorName( ) ],
		[ 'link', annotation.getLink( ), true ],
		[ 'userid', annotation.getUserId( ), true ], // not trustworthy, but good for demo apps
		[ 'action', annotation.getAction( ) ],
		[ 'sequence-range', annotation.getSequenceRange( ) ? annotation.getSequenceRange( ).toString( ) : '' ],
		[ 'xpath-range', annotation.getXPathRange( ) ? annotation.getXPathRange( ).toString( ) : '' ],
		[ 'link_title' , annotation.getLinkTitle( ) ],
		[ 'csrf', readCookie( this.csrfCookie ), this.csrfCookie ]
	];
	var body = restutil.queryArgsToString( params );
		
	fail2 = function( status, text ) {
		logError( "AnnotationService.createAnnotation failed with code " + status + ":\n" + serviceUrl + "\n" + text );
		if ( fail )
			fail( status, text );
	};
	restutil.postResource( serviceUrl, body, ok, fail2 );
	trace( 'annotation-service', "AnnotationService.createAnnotation " + decodeURI( serviceUrl ) + "\n" + body );
}

/**
 * Update an annotation on the server
 * Only updates the fields that have changed
 */
RestAnnotationService.prototype.updateAnnotation = function( annotation, ok, fail )
{
	var serviceUrl = this.urlTemplate.match( [
		[ 'url', annotation.getUrl( ), true, false ],
		[ 'id', annotation.getId( ), true ],
		[ 'method', 'PUT', this.noPutDelete ],
		[ 'curuser', this.sendCurUser, this.sendCurUser ]
	], 'updateAnnotations' );
	if ( ! serviceUrl )
		throw "No matching service URL for updateAnnotation.";	

	// May need to pass method name instead of using PUT or DELETE
	var method = this.noPutDelete ? 'POST' : 'PUT';

	var params = [
		[ 'note', annotation.getNote( ), annotation.hasChanged( 'note' ) ],
		[ 'sheet', annotation.getSheet( ), annotation.hasChanged( 'sheet' ) ],
		[ 'link', annotation.getLink( ), annotation.hasChanged( 'link' ) ],
		[ 'link_title', annotation.getLinkTitle( ), annotation.hasChanged( 'linkTitle' ) ],
		[ 'sequence-range', annotation.getSequenceRange( ) ? annotation.getSequenceRange( ).toString( ) : '', annotation.hasChanged( 'range/sequence' ) ],
		[ 'xpath-range', annotation.getXPathRange( ) ? annotation.getXPathRange( ).toString( ) : '', annotation.hasChanged( 'range/xpath' ) ],
		[ 'csrf', readCookie( this.csrfCookie ) ]
	];
	var body = restutil.queryArgsToString( params );
	
	fail2 = function( status, text ) {
		logError( "AnnotationService.updateAnnotation failed with code " + status + ":\n" + serviceUrl + "\n" + text );
		if ( fail )
			fail( status, text );
	};
	restutil.putResource( serviceUrl, body, ok, fail2, { okXml: true, noPutDelete: this.noPutDelete } );
	trace( 'annotation-service', "AnnotationService.updateAnnotation " + decodeURI( serviceUrl ) );
	trace( 'annotation-service', "  " + body );
}


/**
 * Update multiple annotations at once
 * The method signature will likely change in future;  for now it only deals with updates to
 * the note field.
 */
RestAnnotationService.prototype.bulkUpdate = function( oldNote, newNote, ok, fail )
{
	var serviceUrl = this.urlTemplate.match( [
		[ 'note', oldNote, true ],
		[ 'method', 'PUT', this.noPutDelete ],
		[ 'curuser', this.sendCurUser, this.sendCurUser ]
	], 'updateAnnotation' );
	if ( ! serviceUrl )
		throw "No matching service URL template for bulkUpdate.";	

	var params = [
		[ 'note', newNote, true ],
		[ 'csrf', readCookie( this.csrfCookie ) ]
	];
	
	var body = restutil.queryArgsToString( params );

	fail2 = function( status, text ) {
		logError( "AnnotationService.bulkUpdate failed with code " + status + ":\n" + serviceUrl + "\n" + text );
		if ( fail )
			fail( status, text );
	};
	restutil.putResource( serviceUrl, body, ok, fail2, { noPutDelete: this.noPutDelete } );
	trace( 'annotation-service', "AnnotationService.bulkUpdate " + decodeURI( serviceUrl ) + "\n" + body );
}


/**
 * Delete an annotation on the server
 */
RestAnnotationService.prototype.deleteAnnotation = function( annotation, ok, fail )
{
	var serviceUrl = this.urlTemplate.match( [
		[ 'url', annotation.getUrl( ), true, false ],
		[ 'id', annotation.id, true ],
		[ 'method', 'DELETE', this.noPutDelete ],
		[ 'csrf', readCookie( this.csrfCookie ) ],
		[ 'curuser', this.sendCurUser, this.sendCurUser ]
	], 'updateAnnotations' );
	if ( ! serviceUrl )
		throw "No matching service URL template for deleteAnnotation.";

	fail2 = function( status, text ) {
		logError( "AnnotationService.deleteAnnotation failed with code " + status + ":\n" + serviceUrl + "\n" + text );
		if ( fail )
			fail( status, text );
	};
	restutil.deleteResource( serviceUrl, ok, fail2, { noPutDelete: this.noPutDelete } );
	trace( 'annotation-service', "AnnotationService.deleteAnnotation " + decodeURI( serviceUrl ) );
}

