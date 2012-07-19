/*
 * XPathRange.js
 *
 * Serialization formats for word ranges (see ranges.js).
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
 * $Id: XPathRange.js 537 2012-05-30 21:51:38Z geof.glass $
 */
 
/**
 * XPath representation of a range
 *
 * XPath ranges are fast (assuming the document.evaluate function is implemented in the
 * browser to resolve XPath expressions), but unlike block ranges cannot be ordered.
 */
function XPathRange( start, end )
{
	this.start = start;
	this.end = end;
}

/**
 * Can XPath points be resolved?
 * Yes on Firefox, no on IE.
 */
XPathRange.canResolve = function( root )
{
	return root.ownerDocument.evaluate ? true : false;
}

XPathRange.fromString = function( path )
{
	if ( ! path )
		return null;
	var parts = path.split( ';' );
	if ( null == parts || 2 != parts.length )
		throw "XPathRange parse error";
	return new XPathRange(
		new XPathPoint( parts[ 0 ] ),
		new XPathPoint( parts[ 1 ] ) );
}

XPathRange.prototype.toString = function( )
{
	return this.start.toString( ) + ';' + this.end.toString( );
}

XPathRange.prototype.equals = function( range2 )
{
	return this.start.equals( range2.start ) && this.end.equals( range2.end );
}

XPathRange.prototype.collapsedToStart = function( )
{
	return new XPathRange(
		this.start,
		this.start );
}

XPathRange.prototype.collapsedToEnd = function( )
{
	return new XPathRange(
		this.end,
		this.end );
}

XPathRange.prototype.needsUpdate = function( )
{
	return this.start.noLines || this.end.noLines;
}


function XPathPoint( path, lines, words, chars )
{
	if ( lines )
	{
		this.path = path;
		this.lines = lines;
		this.words = words;
		this.chars = chars;
	}
	else
	{
		var parts = path.match( /^\s*(.*)\/line\((\d+)\)\/word\((\d+)\)\/char\((\d+)\)\s*$/ );
		if ( parts )
		{
			this.path = parts[ 1 ];
			this.lines = Number( parts[ 2 ] );
			this.words = Number( parts[ 3 ] );
			this.chars = Number( parts[ 4 ] );
		}
		else
		{
			// Older format
			var parts = path.match( /^\s*(.*)\/word\((\d+)\)\/char\((\d+)\)\s*$/ );
			if ( parts )
			{
				this.path = parts[ 1 ];
				this.words = Number( parts[ 2 ] );
				this.chars = Number( parts[ 3 ] );
			}
			else
			{
				this.path = path;
				this.lines = this.words = this.chars = 0;
			}
		}
	}
	this.noLines = this.words != null && this.lines == null;
}

XPathPoint.prototype.equals = function( point2 )
{
	return this.path == point2.path
		&& this.lines == point2.lines
		&& this.words == point2.words
		&& this.chars == point2.chars;
}

XPathPoint.prototype.toString = function( )
{
	if ( this.words )
		return this.path + ( this.path ? '/' : '' ) + 'line(' + this.lines + ')/word(' + this.words + ')/char(' + this.chars + ')';
	else
		return this.path;
}

/**
 * Notice the lack of an fskip function.  None of the parent nodes of the current node
 * can be skippable for this to work.
 */
XPathPoint.fromNode = function( root, rel, lines, words, chars, idTest )
{
	var node = rel;
	var path = '';
	var foundId = false;
	
	outer: while ( null != node && root != node )
	{
		if ( foundId )
		{
			// If we found an ID, short-circuit to produce a path like .//div[@id='id1']/p[5]
			path = './/' + path;
			break;
		}
		else
		{
			// Check whether we can use this node's ID as a start point
			var id = node.getAttribute( 'id', null );
			if ( id && ( ! idTest || idTest( id ) ) && -1 == id.indexOf( "'" ) )
			{
				path = "*[@id='" + id + "']" + ( '' == path ? '' : '/' + path );
				foundId = true;
			}
			else
			{
				var count = 1;
				for ( var prev = node.previousSibling; prev && ! foundId;  prev = prev.previousSibling )
				{
					if ( ELEMENT_NODE == prev.nodeType && prev.tagName == node.tagName )
					{
						id = prev.getAttribute( 'id', null );
						if ( id && ( ! idTest || idTest( id ) ) && -1 == id.indexOf( "'" ) )
							foundId = true;
						else
							count += 1;
					}
				}
				if ( '' != path )
					path = '/' + path;
				
				if ( foundId )
				{
					{
						path = "*[@id='" + id + "']"
							+ '/following-sibling::html:' + node.tagName.toLowerCase( )
							+ '[' + String( count ) + ']'
							+ path;
					}
				}
				else
				{
					path = 'html:' + node.tagName.toLowerCase( ) + '[' + String( count ) + ']' + path;
					node = node.parentNode;
				}
			}
		}
	}
	
	return new XPathPoint( path, lines, words, chars );
}

/*
 * This doesn't do much checking on the incoming xpath.
 * TODO: Figure out how to handle tag case name inconsistencies between HTML and XHTML
 */
XPathPoint.prototype.getReferenceElement = function( root )
{
	var rel;	// will be the result	
	var xpath = this.path;
	var myroot = root;

	var startTime = new Date( );
	trace( 'xpath-range', 'XPathPoint.getReferenceElement for path ' + xpath );

	if ( ! this.isXPathSafe( xpath ) )
		return null;
	else if ( xpath == '' )
		return root;
	
	// If this document is not XHTML, need to strip the html: prefixes
	if ( ! domutil.isXhtml( document ) )
	{
		xpath = xpath.replace( /(\/)html:/g, '$1' );
		xpath = xpath.replace( /(\/[a-z-]+::)html:/g, '$1' );
		xpath = xpath.replace( /^html:/, '' );
	}
	
	// Use XPath support if available (as non-Javascript it should run faster)
	if ( root.ownerDocument.evaluate )
	{
		// Current Safari 4 implementation can't handle something like
		// div[1]/div[1]/p[1] - don't know why, but it claims it's an invalid
		// expression.  Even though it can calculate count() of that.  So:
		// catch the error and return null, then the caller can try the sequenc
		// range instead.  Bleargh.  I can't find any proper WebKit documentation
		// of this behavior.
		try
		{
			result = root.ownerDocument.evaluate( xpath, root, null /*domutil.nsPrefixResolver*/, XPathResult.ANY_TYPE, null );
			rel = result.iterateNext( );
		}
		catch( e )
		{
			return null;
		}
	}
	else
		return null;
	/*
	// Internet Explorer's xpath support might look like the following if it existed for
	// HTML document nodes (duh):
	else if ( root.selectSingleNode )
	{
		rel = root.selectSingleNode( xpath );
	}
	*/

	trace( 'range-timing', 'XPathPoint.getReferenceElement timing: ' + ( (new Date()) - startTime ) );
		
	// Ensure that the found node is a child of the root
	// This is necessary to reject xpath attempts to get at secure information
	// elsewhere in the page.  It could happen by accident if an ID is used in the xpath,
	// and that ID is used or moved to outside the root.
	if ( ! domutil.isElementDescendant( rel, root ) )
		return null;
	
	return rel;
}

// This is not a generic xpath checker.  It whitelists just enough to handle
// the auto-generated paths used by XPathPoint
XPathPoint.prototype.isXPathSafe = function( xpath )
{
	// Path could be blank
	if ( '' == xpath )
		return true;
	// Path may start with .//
	if ( './/' == xpath.substr(0,3) )
		xpath = xpath.substr(3);
	var parts = xpath.split( '/' );
	for ( var i = 0;  i < parts.length;  ++i )
	{
		var part = parts[ i ];
		// should perhaps trim it, but won't bother
		var matches = part.match( '^[a-zA-Z0-9_:\*-]+\s*(.*)$' );
		if ( matches )
		{
			var tail = matches[ 1 ];
			// Simple tag name witho or without axis or namespace
			if ( '' == tail )
				;
			// Qualification in [brackets]
			else if ( matches = tail.match( /^\[([^\]]+)\]\s*$/ ) )
			{
				var test = matches[ 1 ];
				// Simple number index
				if ( test.match( /^\d+$/ ) )
					;
				// Comparison of an attribute with a quoted value
				else if ( matches = test.match( /^@[a-zA-Z0-9_-]+\s*=\s*([\'"])[a-zA-Z0-9:._-]+([\'"])$/ ) )
				{
					if ( matches[ 1 ] == matches[ 1 ] )
						;
					else
					{
						trace( 'isXPathSafe', 'XPath test failed: @attribute="value"' );
						return false;
					}
				}
				else
				{
					trace( 'isXPathSafe', 'XPath test failed: unknown subscript' );
					return false;
				}
			}
			else
			{
				trace ('isXPathSafe', 'XPath test failed: improper text after tag name: ' + tail );
				return false;
			}
		}
		else
		{
			trace( 'isXPathSafe', 'XPath test failed: bad tag name' );
			return false;
		}
	}
	return true;
}

