/*
 * rest-log.js
 * REST implementation of the connection to the logging back-end
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
 * $Id: rest-annotate.js 496 2010-02-25 19:56:25Z geof.glass $
 */


/**
 * Initialize the REST logging service
 */
function RestLogService( serviceUrl, course, features )
{
	this.course = course;
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
				case noPutDelete:
					this.noPutDelete = value;
					break;
					
				// include curuser as get parameter to requests
				// should be set to actual user ID
				// cheap way to do simple logging
				case sendCurUser:
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
 * Log an event
 */
RestLogService.prototype.createLogEvent = function( service, action, description,
	object_type, object_id, ok, fail )
{
	// Small flaw here:  the url gets sent both in the query string *and* in the
	// body when niceurls are not in use.  When the URLs are nice, it can be part of
	// the URL path.  Though that limits to annotations only of the current site.
	// Hmmm.
	var serviceUrl = this.urlTemplate.match( [ ], 'createLogEvent' );
	if ( ! serviceUrl )
		throw "No matching service URL template for createLogEvent.";

	var params = [
		[ 'course', this.course ],
		[ 'service', service, true ],
		[ 'action', action, true ],
		[ 'description', description ],
		[ 'object_type', object_type ],
		[ 'object_id', object_id ],
		[ this.csrfCookie, readCookie( this.csrfCookie ), this.csrfCookie ]
	];
	var body = restutil.queryArgsToString( params );
		
	fail2 = function( status, text ) {
		logError( "RestLogService.createLogEvent failed with code " + status + ":\n" + serviceUrl + "\n" + text );
		if ( fail )
			fail( status, text );
	};
	restutil.postResource( serviceUrl, body, ok, fail2 );
	trace( 'log-service', "RestLogService.createLogEvent " + decodeURI( serviceUrl ) + "\n" + body );
}

