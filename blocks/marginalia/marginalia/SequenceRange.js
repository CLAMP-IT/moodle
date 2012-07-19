/*
 * SequenceRange.js
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
 * $Id: SequenceRange.js 537 2012-05-30 21:51:38Z geof.glass $
 */

/** SequenceRange
 *
 * Range representation represented as word+character offsets relative to a given nested
 * block level element.  Somewhat slow (because nodes must be counted manually to ensure
 * only block-level elements are included), but can be ordered:
 *
 * 2.1.3/1.15.0;2.1.3/1.16.4
 *
 * An older fromat looked like this:
 *
 * /2/1/3/15.0;/2/1/3/16.4
 *
 * The first portion locates the rel node as the nth breaking element child of its parent.
 * Non-element nodes are *not counted*;  this is necessary because we don't want to
 * count whitespace only text nodes which might vanish under different formatting.
 * The second part consists of line number (indexed from 1, lines are separated by br 
 * elements), word number (indexed from 1), and character offset (0..word length).
 *
 * There are usually multiple equivalent locator strings based on different rel
 * nodes.  Normalized paths always use the closest possible rel node - i.e., rel is
 * the element in the document for which the word offsets will be minima.
 * Normalized paths should always be used so that word ranges can be ordered.
 */
function SequenceRange( start, end )
{
	this.start = start;
	this.end = end;
}

SequenceRange.fromString = function( path )
{
	var parts = path.split( ';' );
	if ( parts && 2 == parts.length )
	{
		return new SequenceRange(
			new SequencePoint( parts[ 0 ] ),
			new SequencePoint( parts[ 1 ] ) );
	}
	else
		throw "SequenceRange parse error";
}

SequenceRange.prototype.needsUpdate = function( )
{
	return this.start.noLines || this.end.noLines;
}

SequenceRange.prototype.toString = function( )
{
	return this.start.toString( ) + ';' + this.end.toString( );
}

SequenceRange.prototype.compare = function( range2 )
{
	var r = this.start.compare( range2.start );
	if ( 0 != r )
		return r;
	else
		return this.end.compare( range2.end );
}

SequenceRange.prototype.equals = function( range2 )
{
	return 0 == this.compare( range2 );
}

SequenceRange.prototype.collapsedToStart = function( )
{
	return new SequenceRange( this.start, this.start );
}

SequenceRange.prototype.collapsedToEnd = function( )
{
	return new SequenceRange( this.end, this.end );
}

function SequencePoint( path, lines, words, chars )
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
		var sides = path.split( '/' );
		this.path = sides[ 0 ];
//		this.path = sides[ 1 ].split( '.' );
		if ( sides.length == 2 )
		{
			var parts = sides[ 1 ].match( /^(\d*)\.(\d*)\.(\d*)$/ );
			this.lines = '' == parts[ 1 ] ? null : Number( parts[ 1 ] );
			this.words = '' == parts[ 2 ] ? null : Number( parts[ 2 ] );
			this.chars = '' == parts[ 3 ] ? null : Number( parts[ 3 ] );
			this.noLines = this.lines == null && this.words != null;
		}
		else
			this.lines = this.words = this.chars = null;
	}
}

SequencePoint.prototype.toString = function( )
{
	if ( this.words )
		return this.path + '/' + this.lines + '.' + this.words + '.' + this.chars;
	else
		return this.path;
}

/**
 * Set the path based on a node
 */
SequencePoint.fromNode = function( root, rel, lines, words, chars, fskip )
{
	var path = '';
	var node = rel;
	while ( null != node && root != node )
	{
		// If starting from a BR, the count will be one lower
		var count = domutil.isBlockElement( rel.tagName ) ? 1 : 0;
		for ( var prev = node.previousSibling; prev;  prev = prev.previousSibling )
		{
			if ( ! fskip || ! fskip( prev ) )
			{
				if ( ELEMENT_NODE == prev.nodeType && domutil.isBlockElement( prev.tagName ) )
					count += 1;
			}
		}
		path = String( count ) + ( path ? '.' : '' ) + path;
		node = node.parentNode;
	}
	
	return new SequencePoint( path, lines, words, chars );
}

/**
 * Find the DOM node corresponding to the path portion of the point
 */
SequencePoint.prototype.getReferenceElement = function( root, fskip )
{
	var node;
	var startTime = new Date( );
	
	node = domutil.blockPathToNode( root, this.path, fskip );
	
	trace( 'range-timing', 'SequencePoint.getReferenceElement timing: ' + ( (new Date()) - startTime ) );

	return node;
}

/**
 * Find the DOM node corresponding to the line of the point
 */
SequencePoint.prototype.getLineElement = function( root, fskip )
{
	var node = this.getReferenceElement( root, fskip );
	return nodeFromLineCount( node, this.lines );
}

// Only compare the path portion
SequencePoint.prototype.comparePath = function( point2 )
{
	var p1 = this.path;
	var p2 = point2.path;
	
	if ( p1 == p2 )
		return 0;
	// Path shouldn't be empty, but if it is this should prevent the app from breaking
	else if ( ! this.path )
		return -1;
	else if ( ! point2.path )
		return 1;
	else
	{
		p1 = p1.split('.');
		p2 = p2.split('.');
		for ( var i = 0;  i < p1.length;  ++i )
		{
			if ( i >= p2.length )
			{
				trace( 'SequencePoint.comparePath', 'Compare ' + p1 + ' > ' + p2 );
				return 1;
			}
			var x1 = Number( p1[ i ] );
			var x2 = Number( p2[ i ] );
			if ( x1 < x2 )
			{
				trace( 'SequencePoint.comparePath', 'Compare ' + p1 + ' < ' + p2 );
				return -1;
			}
			if ( x1 > x2 )
			{
				trace( 'SequencePoint.comparePath', 'Compare ' + p1 + ' > ' + p2 );
				return 1;
			}
		}
		if ( i < p2.length )
		{
			trace( 'SequencePoint.comparePath', 'Compare ' + p1 + ' < ' + p2 );
			return -1;
		}
		else
			throw "Error in SequencePoint.comparePath";
	}
}

// Compare the whole point (path-only points are treated as if they collpased to start)
SequencePoint.prototype.compare = function( point2 )
{
	var r = this.comparePath( point2 );
	if ( r )
		return r;
	else
	{
		if ( this.lines < point2.lines
			|| this.lines == point2.lines && this.words < point2.words
			|| this.lines == point2.lines && this.words == point2.words && this.chars < point2.chars )
			return -1;
		else if ( this.lines > point2.lines
			|| this.lines == point2.lines && this.words > point2.words
			|| this.lines == point2.lines && this.words == point2.words && this.chars > point2.chars )
			return 1;
		else
			return 0;
	}
}


/**
 * A resolver is used for locating and walking sequence paths (paths without regard to words and characters)
 *
 * path - the starting path
 * node - the node corresponding to that starting path
 */
function SequencePathResolver( node, path )
{
	this.walker = new DOMWalker( node );
	parts = path.split( '.' );
	this.path = [ ];
	for ( var i = 0;  i < parts.length;  ++i )
		this.path[ i ] = Number( parts[ i ] );
	if ( ELEMENT_NODE == node.nodeType && domutil.isBlockElement( node.tagName ) )
		this.depth = this.path.length;
	else
		this.depth = this.path.length - 1;
	return this;	
}

/**
 * Move to the next node in the document
 * Return the current path (which can be compared with a destination to see whether
 * the path has been resolved)
 * Reverse walking doesn't work
 */
SequencePathResolver.prototype.next = function( )
{
	while ( this.walker.walk( true, false ) && this.depth >= 0 )
	{
		var node = this.walker.node;
		
		if ( ELEMENT_NODE == node.nodeType && domutil.isBlockElement( node.tagName ) )
		{
			if ( this.walker.startTag )
			{
				if ( this.path.length < this.depth )
				{
					this.path.push( 1 );
					this.path.splice( this.depth );
				}
				else
					this.path[ this.depth ] += 1;
				this.depth += 1;
				return true;
			}
			if ( this.walker.endTag )
				this.depth -= 1;
		}
		
/*		// Child is sent when first entering child nodes, which may or may not be elements
		if ( LAST_WALK_CHILD == this.walker.lastMove && isBlockElement( this.walker.node.parentNode.tagName ) )
			this.depth += 1;
		// Parent is sent when an end-of-element tag is encountered
		else if ( LAST_WALK_PARENT == this.walker.lastMove && isBlockElement( this.walker.node.tagName ) )
			this.depth -= 1;
		
		// Child and Next can both produce an element
		if ( ( LAST_WALK_NEXT == this.walker.lastMove || LAST_WALK_CHILD == this.walker.lastMove ) && ! this.walker.endTag )
		{
			if ( this.path.length > this.depth )
				this.path.splice( this.path.depth );
			else while ( this.path.length < this.depth )
				this.path.push( 0 );
			this.path[ this.path.length - 1 ] += 1;
		}
*/	}
	return false;
}

SequencePathResolver.prototype.getPath = function( )
{
	return this.path.join( '.' );	
}

SequencePathResolver.prototype.getNode = function( )
{
	return this.walker.node;
}

/**
 * Move forward until the passed path is resolved
 * TODO: Doesn't make sense unless relative to a passed root
 * (backward would be possible, but is not implemented)
 * Faster than calling next() because there's no need to drop down into subtrees
 *
SequencePathResolver.prototype.resolve( path )
{
	var node = walker.node;
	// Locate the rel node based on the path
	// The simple case:  rel is root
	if ( '/' == path || '' == path )
		;
	else
	{
		var nodes = this.path.split( '/' );
		for ( var i = 1;  i < nodes.length;  ++i )
		{
			var count = Number( nodes[ i ] );
			for ( node = node.firstChild;  null != node;  node = node.nextSibling )
			{
				if ( fskip && ! fskip( node ) )
				{
					if ( ELEMENT_NODE == node.nodeType && isBlockElement( node.tagName ) )
					{
						count -= 1;
						if ( 0 == count )
							break;
					}
				}
			}
			if ( 0 != count )
				return null;
		}
	}

	this.walker = new DOMWalker( node );
	return node;
}
*/

