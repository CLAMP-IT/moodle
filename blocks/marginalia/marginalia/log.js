/* 
 * log.js
 *
 * Logging code
 *
 * Marginalia has been developed with funding and support from
 * BC Campus, Simon Fraser University, and the Government of
 * Canada, and units and individuals within those organizations.
 * Many thanks to all of them.  See CREDITS.html for details.
 * Copyright (C) 2005-2007 Geoffrey Glass www.geof.net
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
 * $Id: log.js 537 2012-05-30 21:51:38Z geof.glass $
 */

/**
 * First argument:  switch on logging
 * Second argument:  log to pop-up log window (not just to console)
 */
function ErrorLogger( on, popup )
{
	this.on = on;
	this.popup = popup;
	this.traceSettings = new Object( );
	this.indentLevel = 0;
	return this;
}

ErrorLogger.initLogging = function( on, popup )
{
	window.log = new ErrorLogger( on, popup );	
}

ErrorLogger.getLogger = function( )
{
	return window.log;
}

ErrorLogger.prototype.getLogElement =  function( )
{
	if ( ! this.logElement )
	{
		this.logWindow = window.open( "", "Log" );
		// May fail to open if the html file cannot be found
		if ( this.logWindow )
		{
			this.logDocument = this.logWindow.document;
			this.logDocument.open( "text/html", "replace" );
			this.logDocument.write( 
	//			"<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>\n"
				"<html>\n<head>\n"
	//			+ "\t<link rel='stylesheet' type='text/css' href='log.css'/>\n"
				+ "\t<title>Marginalia Log</title>\n"
				+ "</head>\n<body>\n<ul id='log'>\n</ul>\n</body>\n</html>" );
			this.logDocument.close( );
	// For some reason, if I add the stylesheet the logging information doesn't show up.
	// Looks like a browser bug.
	//		var stylesheet = this.logDocument.createElement( 'link' );
	//		stylesheet.setAttribute( 'rel', 'stylesheet' );
	//		stylesheet.setAttribute( 'type', 'text/css' );
	//		stylesheet.setAttribute( 'href', 'log.css' );
	//		var headElement = getChildByTagClass( this.logDocument.documentElement, 'head', null );
	//		headElement.appendChild( stylesheet );
			this.logElement = this.logDocument.getElementById( 'log' );
		}
	}
	return this.logElement;
}

ErrorLogger.prototype.logError = function( s )
{
	if ( this.on )
	{
		// Only works on Mozilla - other browsers have no dump function
		if ( window.dump )
			dump( "ERROR: " + s + "\n" );
		if ( this.popup )
		{
			var dumpElement = this.getLogElement( );
			if ( dumpElement )
			{
				var li = this.logDocument.createElement( 'li' );
				li.appendChild( this.logDocument.createTextNode( s ) );
				dumpElement.appendChild( li );
			}
		}
	}
}

ErrorLogger.prototype.setTrace = function( topic, b )
{
	var oldSetting = this.traceSettings[ topic ];
	this.traceSettings[ topic ] = b;
	return oldSetting;
}

ErrorLogger.prototype.getTrace = function( topic )
{
	return this.traceSettings[ topic ];
}

ErrorLogger.prototype.indent = function( )
{
	this.indentLevel++;
}

ErrorLogger.prototype.outdent = function( )
{
	this.indentLevel--;
}

ErrorLogger.prototype.trace = function( topic, s )
{
	if ( ! this.on )
		alert( 'logging not on' );
	for ( var i = 0;  i < this.indentLevel;  ++i )
		s = '  ' + s;
	if ( this.on && ( !topic || this.traceSettings[ topic ] ) )
	{
		if ( window.console && window.console.log )
			window.console.log( s );
		else if ( window.dump )
			dump( s + "\n");
		if ( this.popup )
		{
			var dumpElement = this.getLogElement( );
			if ( dumpElement )
			{
				var li = this.logDocument.createElement( 'li' );
				li.appendChild( this.logDocument.createTextNode( s ) );
				dumpElement.appendChild( li );
			}
		}
	}
}

ErrorLogger.prototype.traceNode = function( topic, node )
{
	var s;
	if ( ELEMENT_NODE == node.nodeType )
	{
		s = node.tagName;
		if ( node.id )
			s += '#' + node.id;
		if ( node.className )
		{
			var c = node.className.split( ' ' );
			s += '.' + c.join( '.' );
		}
	}
	if ( TEXT_NODE == node.nodeType )
		s = 'Text: "' + node.nodeValue.substr( 0, 20 ) + '"';
	this.trace( topic, s );
}

function logError( s )
{
	if ( window.log )
		window.log.logError( s );
}

function trace( topic, s )
{
	if ( window.log )
		window.log.trace( topic, s );
}

function assert( b, x )
{
	if ( ! x )
		x = 'Assertion Error';
	if ( ! b )
		throw x;
}
