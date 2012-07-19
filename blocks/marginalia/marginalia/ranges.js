/*
 * range.js
 *
 * Requires domutil.js 
 *
 * Support for different kinds of text range (including the W3C Range object
 * and the TextRange object in IE)
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
 * $Id: ranges.js 537 2012-05-30 21:51:38Z geof.glass $
 */

/*
 * State values used by state machines
 */
STATE_SPACE = 0
STATE_WORD = 1
STATE_TARGET_SPACE = 2
STATE_TARGET_WORD = 3
STATE_FALL_FORWARD = 4
STATE_DONE = 5

/**
 * OK.  Here's how WordRange and WordPoint work.  Each point (the start and end of the range)
 * is determined from two pieces of information.  The first is the rel element.  
 * This locates a point in the document (the start of the element) after which both
 * the start and end points occur.  The points are specified relative to this rel
 * element in terms of a number of words.  Note, however, that the start and end
 * points don't have to be *within* the rel element - only after its start.
 *
 * The benefits of working this way are twofold:  First, it deals efficiently with
 * points and ranges that exist under the root element but not within a child element.
 * Second, these points can be ordered.
 */

function TextRange( startContainer, startOffset, endContainer, endOffset )
{
	this.startContainer = startContainer;
	this.startOffset = startOffset;
	this.endContainer = endContainer;
	this.endOffset = endOffset;
}

/**
 * Convert from a W3C Range object
 */
TextRange.fromW3C = function( range )
{
	return new TextRange(
		range.startContainer,
		range.startOffset,
		range.endContainer,
		range.endOffset );
}

/**
 * Shift start and end points until each begins or ends in a text node with text
 * (i.e. pass over whitespace-only and element nodes)
 * Should be rewritten without the prototype to make TextRange immutable
 */
TextRange.prototype.shrinkwrap = function( fskip )
{
	var startContainer = this.startContainer;
	var startOffset = this.startOffset;
	var endContainer = this.endContainer;
	var endOffset = this.endOffset;
	
	// Shrinkwrap start
	var node = startContainer;

	// First apply the offset
	if ( TEXT_NODE != node.nodeType && startOffset > 0)
		node = node.childNodes.item( startOffset - 1 );
		
	var foundOther = false;
	var walker = new DOMWalker( node );
	do
	{
		var node = walker.node;

		// Make sure endpoints don't pass each other
		if ( node == endContainer )
			foundOther = true;
		else if ( foundOther )
			return null;
		
		
		// Find a non-whitespace-only text node and skip leading whitespace
		if ( ( !fskip || ! fskip( node ) ) && TEXT_NODE == node.nodeType )
		{
			if ( ! node.nodeValue.match( /^(\s|\u00a0)*$/ ) )
			{
				if ( node != startContainer )
				{
					startContainer = node;
					startOffset = 0;
				}
				break;
			}
		}
	}
	while ( walker.walk( !fskip || ! fskip( node ) ) );

	
	// Shrinkwrap end
	var node = endContainer;

	// First apply the offset
	if ( TEXT_NODE != endContainer.nodeType && endOffset > 0 )
		node = node.childNodes.item( endOffset - 1 );
		
	var foundOther = false;
	var walker = new DOMWalker( node, true );
	
	// If we're at the start of a text node, need to walk backward even if that
	// node contains text
	if ( node == endContainer && endOffset == 0 )
		walker.walk( );
	do
	{
		var node = walker.node;
		
		// Make sure endpoints don't pass each other
		if ( node == startContainer )
			foundOther = true;
		else if ( foundOther )
			return false;
		
		// Find a non-whitespace-only text node and skip leading whitespace
		if ( ( !fskip || ! fskip( node ) ) && TEXT_NODE == node.nodeType )
		{
			var ws = node.nodeValue.match( /^(\s|\u00a0)*/ );
			var wslen = ws[ 1 ] ? ws[ 1 ].length : 0;
			// Fails is this is an all-whitespace node
			if ( wslen != node.nodeValue.length )
			{
				if ( node == endContainer )
				{
					// Fails if the offset is within leading whitespace
					if ( wslen < endOffset )
						break;
				}
				else
				{
					endContainer = walker.node;
					endOffset = node.nodeValue.length;
					break;
				}
			}
		}
	}
	while ( walker.walk( ! fskip || ! fskip( node ), true ) );

	return new TextRange( 
		startContainer, startOffset,
		endContainer, endOffset );
}

/**
 * Convert a word range to a text range
 * This is also inefficient, because annotation calls it repeatedly, each time from the start
 * of the document.  A better version would take advantage of the fact that highlights are
 * always shown in order.  Also, it suffers from the same inefficiency as textRangeToWordRange.
 */
TextRange.fromWordRange = function( wordRange, fskip )
{
	// These trace statements used to output the SequenceRange string for the word range,
	// but with that code moved out and without a root parameter, that no longer works
	trace( 'word-range', 'wordRangeToTextRange' );

	// Walk to the start point
	var lineNode = wordRange.start.resolveLines( );
	var walker = new WordPointWalker( lineNode, fskip );
	if ( ! walker.walkToTarget( lineNode, wordRange.start.words, wordRange.start.chars ) )
	{
		// Using document.documentElement is a slow hack
		trace( 'word-range', 'Unable to find point ' );
		// TODO: proper exceptions
		throw "Unable to find point";
	}
	var startPoint = new NodePoint( walker.currNode, walker.currChars );

	// Walk to the end point
	lineNode = wordRange.end.resolveLines( );
	//trace( null, 'lineNode(end): ' + lineNode.tagName + '#' + lineNode.id );
	if ( ! walker.walkToTarget( lineNode, wordRange.end.words, wordRange.end.chars ) )
	{
		// Using document.documentElement is a slow hack
		trace( 'word-range', 'Unable to find point ' );
		// TODO: proper exceptions
		throw "Unable to find point";
	}
	var endPoint = new NodePoint( walker.currNode, walker.currChars );

	var textRange = new TextRange( 
		startPoint.container,
		startPoint.offset,
		endPoint.container,
		endPoint.offset );

	walker.destroy( );
	walker = null;
	startPoint.destroy( );
	startPoint = null;
	endPoint.destroy( );
	endPoint = null;
	
	return textRange;
}

TextRange.prototype.destroy = function( )
{
	this.startContainer = null;
	this.endContainer = null;
}


/** Represents a point in the document
 * The point is determined relative to a root, which should be the same for
 * all comparable points.  The position is located by finding a character offset
 * within a line and word counted relative to the beginning of rel, a block 
 * level element.
 */
function WordPoint( rel, lines, words, chars )
{
	this.rel = rel;
	this.lines = lines;
	this.words = words;
	this.chars = chars;
	this.lineNode = null;
	return this;
}

/**
 * Count the number of line breaks (caused by br elements) between a given
 * node and a preceding block-level element (in document order).  The block-level
 * element must be within root, and it must not have any ancestors (between it
 * and root) that are not themselves block-level.  That should be impossible in
 * valid HTML, but lots of HTML isn't valid so it is necessary to check.  In
 * other words, an element is only considered block level if a) it actually is
 * block level, and b) it has no non block-level ancestors.
 * Returns [ rel, br, count ] where:
 * block:  block-level element
 * line:  closest preceding br or block-level
 * count:  number of lines between block and line (starting from 1)
 */
WordPoint.countLines = function( root, node )
{
	if ( ELEMENT_NODE == node.nodeType && domutil.isBlockElement( node.tagName ) )
		return [ node, 1 ];
	
	var lineCount = 1;
	var lineNode = null;
	var blockNode = root;
	var tempLineCount = 0;
	
	// Walk backwards until we hit a block-level node
	var walker = new DOMWalker( node );
	while ( walker.walk( true, true ) )
	{
		if ( walker.node == root )
			break;
		else if ( ELEMENT_NODE == walker.node.nodeType )
		{
			if ( walker.startTag && domutil.isBlockElement( walker.node.tagName ) )
			{
				// Confirm that this node has all block-level parents
				var isValidRefNode = true;
				for ( var n = walker.node;  n && n != root;  n = n.parentNode )
				{
					if ( ! domutil.isBlockElement( n.tagName ) )
					{
						isValidRefNode = false;
						break;
					}
				}
				if ( isValidRefNode )
				{
					blockNode = walker.node;
					break;
				}
			}
			else if ( walker.startTag && 'br' == walker.node.tagName.toLowerCase( ) )
			{
				if ( null == lineNode )
					lineNode = walker.node;
				lineCount += 1;
			}
		}
	}
	
	return {
		block: blockNode,
		line:  lineNode ? lineNode : blockNode,
		count: lineCount 
	};
}

WordPoint.prototype.countLines = function( root )
{
	return WordPoint.countLines( this.rel );
}

/**
 * Find the node corresponding to the line count
 * caches the result
 * (which is why the immutability of WordPoint is so important)
 */
WordPoint.prototype.resolveLines = function( )
{
	var rel = this.rel;
	var count = this.lines;
	
	if ( true || ! this.lineNode )
	{
		if ( ! count || 1 == count )
			this.lineNode = rel;
		// Use XPath support if available (as non-Javascript it should run faster)
		else if ( rel.ownerDocument.evaluate )
		{
			var xpath = domutil.isXhtml( rel.ownerDocument )
				? '(descendant::html:br | following::html:br)[' + ( count - 1 ) + ']'
				: '(descendant::br | following::br)[' + ( count - 1 ) + ']';
			var nodes = rel.ownerDocument.evaluate( xpath, rel, domutil.nsPrefixResolver, XPathResult.ANY_TYPE, null );
			this.lineNode = nodes.iterateNext( );
		}
		else
		{
			var node = rel;
			var walker = new DOMWalker( rel );
			do
			{
				if ( count == 1 )
					break;
				else if ( ELEMENT_NODE == walker.node.nodeType && walker.startTag && 'br' == walker.node.tagName.toLowerCase( ) )
					count -= 1;
			}
			while ( walker.walk( true ) );
			this.lineNode = walker.node;
		}
	}
	return this.lineNode;
}

WordPoint.prototype.toSequencePoint = function( root )
{
	return SequencePoint.fromNode(root, this.rel, this.lines, this.words, this.chars );
}
	
WordPoint.fromSequencePoint = function( sequencePoint, root, fskip )
{
	var rel = sequencePoint.getReferenceElement( root, fskip );
	return rel == null ? null : new WordPoint(
		rel,
		sequencePoint.lines,
		sequencePoint.words,
		sequencePoint.chars );
}

WordPoint.prototype.toXPathPoint = function( root )
{
	return XPathPoint.fromNode( root, this.rel, this.lines, this.words, this.chars );
}

WordPoint.fromXPathPoint = function( xpathPoint, root, fskip )
{
	var rel = xpathPoint.getReferenceElement( root, fskip );
	return rel == null ? null : new WordPoint(
		rel,
		xpathPoint.lines,
		xpathPoint.words,
		xpathPoint.chars );
}

/*
 * Test whether two WordPoints are equal
 * doesn't account for multiple equivalent representations
 */
WordPoint.prototype.equals = function( point2 )
{
	return this.rel == point2.rel
		&& this.lines == point2.lines
		&& this.words == point2.words
		&& this.chars == point2.chars;
}
	
WordPoint.prototype.destroy = function( )
{
	delete this.root;
	this.rel = null;
}

function WordRange( start, end )
{
	this.start = start;
	this.end = end;
}

/**
 * Convert a text range to a word range
 *
 * textRange - the W3C range object (or matching Javascript object) specifying the range
 * root - a root element containing both the start and end points
 * fskip - a function that returns true when an element should not be counted
 * Returns:  A new WordRange object.
 */
WordRange.fromTextRange = function( textRange, root, fskip )
{
//	var rel = domutil.closestPrecedingBreakingElement( textRange.startContainer );
	var start = WordPoint.fromNodePoint( textRange.startContainer, textRange.startOffset, root, true, fskip );

//	rel = domutil.closestPrecedingBreakingElement( textRange.endContainer );
	var end = WordPoint.fromNodePoint( textRange.endContainer, textRange.endOffset, root, false, fskip );
	
	// If there was a problem, free memory
	if ( null == start || null == end )
	{
		if ( start )
			start.destroy( );
		if ( end )
			end.destroy( );
		// TODO: a proper exception here or above
		throw "WordRange.fromTextRange failed";
	}
	
	return new WordRange( start, end );
}

/**
 * Convert a WordRange to a SequenceRange
 */
WordRange.prototype.toSequenceRange = function( root )
{
	var start = this.start.toSequencePoint( root );
	var end = this.end.toSequencePoint( root );
	return start && end ? new SequenceRange( start, end ) : null;
}

/**
 * Convert an SequenceRange to a WordRange
 * Returns false if the start and/or end poin could not be resolved
 */
WordRange.fromSequenceRange = function( sequenceRange, root, fskip )
{
	var start = WordPoint.fromSequencePoint( sequenceRange.start, root, fskip );
	var end = WordPoint.fromSequencePoint( sequenceRange.end, root, fskip );
	return start && end
		? new WordRange( start, end )
		: null;
}

/**
 * Convert a WordRange to an XPathRange
 */
WordRange.prototype.toXPathRange = function( root )
{
	var xpathRange = new XPathRange( 
		this.start.toXPathPoint( root ),
		this.end.toXPathPoint( root ) );
	return xpathRange;
}

/**
 * Convert an XPathRange to a WordRange
 * Returns false if the start and/or end poin could not be resolved
 */
WordRange.fromXPathRange = function( xpathRange, root, fskip )
{
	var start = WordPoint.fromXPathPoint( xpathRange.start, root, fskip );
	var end = WordPoint.fromXPathPoint( xpathRange.end, root, fskip );
	return start && end ? new WordRange( start, end ) : null;
}


/**
 * Partition a WordRange into a series of shorter TextRanges
 * Also returns the quote defined by the range
 * The quote has spaces stripped from either end and inserted at breaking element start/end points
 * (multiple spaces are compressed to a single space)
 */
WordRange.prototype.partition = function( fskip )
{
	var startNode = this.start.resolveLines( );
	
	var walker = new WordPointWalker( startNode, fskip );
	var targetRel = this.start.resolveLines( );
	trace( 'WordRange.partition', 'Partition from ' + targetRel.tagName + ( targetRel.id ? '#' + targetRel.id : '' ) + ' ' + this.start.words + ' ' + this.start.chars );
	walker.walkToTarget( targetRel, this.start.words, this.start.chars );
	var initialOffset = walker.currChars;
	var initialRel = walker.currNode;
	trace( 'WordRange.partition', 'initialOffset=' + initialOffset );
	
	var highlightRanges = new Array();
	targetRel = this.end.resolveLines( );
	trace( 'WordRange.partition', '-> partition to ' + targetRel.tagName + ( targetRel.id ? '#' + targetRel.id : '' ) + ' ' + this.end.words + ' ' + this.end.chars );
	walker.setTarget( targetRel, this.end.words, this.end.chars );
	var rangeNum = 0;
	var done = false;
	var actual = '';	// actual quote text
	while ( ! done )
	{
		done = walker.walk( );
		if ( 0 == rangeNum )
		{
			highlightRanges[ rangeNum ] = new TextRange( 
					walker.currNode, initialOffset,
					walker.currNode, walker.currChars );
			var t = walker.currNode.nodeValue;
			actual += t.substring( initialOffset, walker.currChars );
			trace( 'WordRange.partition', 'Current node text: ' + t );
			trace( 'WordRange.partition', 'Add text: ' + t.substring( initialOffset, walker.currChars ) + ' (' + initialOffset + ' + ' + walker.currChars + ')' );
		}
		else
		{
			highlightRanges[ rangeNum ] = new TextRange(
				walker.currNode, 0,
				walker.currNode, walker.currChars );
			var t = walker.currNode.nodeValue;
			actual += ( walker.breakBefore ? ' ' : '' ) + t.substring( 0, walker.currChars );
			trace( 'WordRange.partition', 'Current node text: ' + t );
			trace( 'WordRange.partition', 'Add text: ' + t.substring( 0, walker.currChars ) + ' (0+ ' + walker.currChars + ')' );
		}
		rangeNum += 1;
	}
	walker.destroy();
	// compress spaces in the quote
	actual = actual.replace( /(\s|\u00a0)+/g, ' ' );
	
	return {
		quote: actual,
		ranges: highlightRanges
		};
}


/**
 * Test whether two word ranges are the same
 * doesn't account for different ways of specifying the same location
 */
WordRange.prototype.equals = function( range2 )
{
	return this.start.equals( range2.start ) && this.end.equals( range2.end );
}

WordRange.prototype.destroy = function( )
{
	if ( null != this.start )
		this.start.destroy( );
	if ( null != this.end )
		this.end.destroy( );
}


/*
 * Convert a (container,offset) pair into a word count from containing node named rel
 * container must be the text node containing the point.
 * The container and offset must correspond to the output of TextRange.shrinkwrap() 
 * The first representation is browser-specific, but a word count is not.
 * A word is defined as a continuous sequence of non-space characters.  Inline elements
 * are not considered word separators, but block-level elements are.
 * fallBack - if position ends following whitespace, count an extra word?
 */
WordPoint.fromNodePoint = function( container, offset, root, fallForward, fskip )
{
	trace( 'word-range', 'WordPoint.fromNodePoint( ' + container + ',' + offset + ')' );

	// The container could be inline.  Find its line count and the block-level rel
	var x = WordPoint.countLines( root, container );
	
	var state = new NodeToWordPoint_Machine( container, offset, x.line, fallForward );
	RecurseOnElement( state, x.line, fskip );
	var node = x.line;
	while ( STATE_DONE != state.state )
	{
		while ( ! node.nextSibling )
			node = node.parentNode;
		if ( null == node )
		{
			state.destroy( );
			return null;
		}
		node = node.nextSibling;
		RecurseOnElement( state, node, fskip )
	}
	state.destroy( );
	
	return new WordPoint( x.block, x.count, state.words, state.chars );
}

NodeToWordPoint_Machine.prototype.trace = function( input )
{
	trace( 'word-range', 'State ' + this.state + ' at ' + this.words + '.' + this.chars + ' (' + this.offset + ' offset) input "' + input + '"' );
}

function RecurseOnElement( state, node, fskip )
{
	if ( null == node )
		throw( "RecurseOnElement: node is null" );
	if ( ELEMENT_NODE == node.nodeType && ( null == fskip || ! fskip( node ) ) )
	{
		var r = state.startElement( node );
		if ( STATE_DONE == state.state )
			return true;
		if ( r )
		{
			for ( var child = node.firstChild;  null != child;  child = child.nextSibling )
			{
				RecurseOnElement( state, child, fskip )
				if ( STATE_DONE == state.state )
					return true;
			}
			state.endElement( node );
		}
		if ( STATE_DONE == state.state )
			return true;
	}
	else if ( TEXT_NODE == node.nodeType || CDATA_SECTION_NODE == node.nodeType )
	{
		state.text( node );
		if ( STATE_DONE == state.state )
			return true;
	}
	return false;
}


function NodeToWordPoint_Machine( container, offset, rel, fallForward )
{
	this.targetContainer = container;
	this.targetOffset = offset;
	this.fallForward = fallForward;
	this.container = rel;
	this.words = 0;
	this.chars = 0;
	this.state = STATE_SPACE;
	this.offset = 0;
	return this;
}

NodeToWordPoint_Machine.prototype.destroy = function( )
{
	this.targetContainer = null;
	this.container = null;
}


/** Callback when a start element is encountered */
NodeToWordPoint_Machine.prototype.startElement =
NodeToWordPoint_Machine.prototype.endElement = function( node )
{
	this.trace( '<' + node.tagName + '>' );
	if ( domutil.isBreakingElement( node.tagName ) )
	{
		if ( STATE_WORD == this.state )
			this.state = STATE_SPACE;
		else if ( STATE_FALL_FORWARD == this.state )
		{
			this.state = STATE_DONE;
			return false;
		}
	}
	return true;
}

/* Callback when an end element is encountered
NodeToWordPoint_Machine.prototype.endElement = function( node )
{
	this.trace( '</' + node.tagName + '>' );
	if ( domutil.isBreakingElement( node.tagName ) )
	{
		if ( STATE_WORD == this.state )
			this.state = STATE_SPACE;
		else if ( STATE_FALL_FORWARD == this.state )
		{
			this.state = STATE_DONE;
			return false;
		}
	}
	return true;
}
*/
NodeToWordPoint_Machine.prototype.text = function( node )
{
	if ( node == this.targetContainer || STATE_TARGET_WORD == this.state || STATE_TARGET_SPACE == this.state )
	{
		if ( 0 == this.targetOffset )
		{
			if ( this.fallForward )
			{
				if ( STATE_SPACE == this.state || STATE_TARGET_SPACE == this.state )
				{
					this.words += 1;
					this.chars = 0;
					this.state = STATE_DONE;
					return;
				}
				else
					this.state = STATE_FALL_FORWARD;
			}
			else
			{
				this.state = STATE_DONE;
				return;
			}
		}
		else
		{
			if ( STATE_SPACE == this.state )
				this.state = STATE_TARGET_SPACE;
			else if ( STATE_WORD == this.state )
				this.state = STATE_TARGET_WORD;
		}
		trace( 'word-range', 'In container, state ' + this.state + ' at ' + this.words + '.' + this.chars + ' looking for offset ' + this.targetOffset );
	}
	
	var s = node.nodeValue.replace( /(\s|\u00a0)/g, ' ' );
	trace( 'word-range', "Searching in:\n" + s );
	for ( var i = 0;  i < s.length;  ++i )
	{
		var c = s.charAt( i );
		this.trace( c );
		if ( STATE_SPACE == this.state )
		{
			if ( ' ' != c )
			{
				this.chars = 1;
				this.words += 1;
				this.state = STATE_WORD;
			}
		}
		else if ( STATE_WORD == this.state )
		{
			if ( ' ' == c )
				this.state = STATE_SPACE;
			else
			{
				// Don't iterate through every character in the word.  This produces a noticeable
				// speed increase (gut instinct places it at 3-5x).  Also, don't optimize
				// STATE_TARGET_WORD - that's too much complexity for too little benefit. 
				var j = s.indexOf( ' ', i );
				if ( -1 == j )
					this.chars += 1;	// only action required for unoptimized version
				else
				{
					i = j;
					this.chars += j - i;
					this.state = STATE_SPACE;
				}
			}
		}
		else if ( STATE_TARGET_SPACE == this.state )
		{
			if ( ' ' != c )
			{
				this.chars = 1;
				this.words += 1;
				this.state = STATE_TARGET_WORD;
				trace( 'word-range', 'TARGET_SPACE -> TARGET_WORD, offset=' + (this.offset + 1) );
			}
			this.offset += 1;
			if ( this.offset == this.targetOffset )
			{
				if ( this.fallForward )
				{
					if ( this.state == STATE_TARGET_SPACE )
					{
						// We now know the node point immediately precedes a space, so 
						// execute a fall forward (i.e. place the point at the start of the
						// next word).  Don't sent fall forward state, because that will look
						// ahead one more character and will produce a different (incorrect)
						// result if that character is non-whitespace.
						this.words += 1;
						this.chars = 0;
						this.state = STATE_DONE;
						return;
					}
					else
						this.state = STATE_FALL_FORWARD;
				}
				else
				{
					this.state = STATE_DONE;
					return;
				}
			}
		}
		else if ( STATE_TARGET_WORD == this.state )
		{
			this.offset += 1;
			if ( ' ' == c )
				this.state = STATE_TARGET_SPACE;
			else
				this.chars += 1;
			if ( this.offset == this.targetOffset )
			{
				if ( this.fallForward )
				{
					if ( ' ' == c )
					{
						this.words += 1;
						this.chars = 0;
						this.state = STATE_DONE;
						return;
					}
					else
						this.state = STATE_FALL_FORWARD;
				}
				else
				{
					trace( 'word-range', 'Success at ' + this.words + '.' + this.chars );
					this.state = STATE_DONE;
					return;
				}
			}
		}
		// Try to fall forward to the next word if there is one, otherwise stick with
		// the current one.
		else if ( STATE_FALL_FORWARD == this.state )
		{
			if ( ' ' == c )
			{
				// TODO: Uh, does this work if the selection ends on a space and that's
				// the end of the document or content area?  Or is it safe because
				// this.fallForward is only set for start points, not end points?  (So
				// that a start point at document end would be represented this way)
				this.words += 1;
				this.chars = 0;
			}
			trace( 'word-range', 'Success: fall forward to ' + this.words + '.' + this.chars );
			this.state = STATE_DONE;
			return;
		}
	}

	// It is possible that this is the target container, but that there's no match yet
	// because we're trying to fall forward.
	// If this is the element and there's no match yet, perhaps fall forward
	if ( node == this.targetContainer && this.fallForward && this.offset == this.targetOffset )
		this.state = STATE_FALL_FORWARD;
	return;
}


/** A NodePoint specifies a point by container and character offset
 * This is the model used by the W3C Range object (e.g. startContainer, startOffset)
 * Note the differences between this and a WordPoint:
 *  - in a NodePoint, container is a text node;  in a WordPoint, it is a block-level element
 *  - in a NodePoint, the offset is contained *within* the container;  in a WordPoint,
 *    the offset follows the start of rel, and may be after the end of the element
 */
function NodePoint( container, offset )
{
	this.container = container;
	this.offset = offset;
	return this;
}

NodePoint.prototype.destroy = function()
{
	this.container = null;
}


/** Convert a word point (rel,word,char) triple to a node point
 */
function wordPointToNodePoint( root, wordPoint, fskip )
{
	var node = wordPoint.resolveLines( );
	var walker = new WordPointWalker( node, fskip );
	if ( ! walker.walkToTarget( wordPoint.resolveLines( ), wordPoint.words, wordPoint.chars ) )
	{
		// Using document.documentElement is a slow hack
		trace( 'word-range', 'Unable to find point ' + wordPoint.toString( document.documentElement ) );
		return null;
	}
	return new NodePoint( walker.currNode, walker.currChars );
}

/** Walk forward, counting words and characters
 * rel - the walk starts at the beginning of this node
 * fskip - function to test nodes that should be ignored in the walk
 */ 
function WordPointWalker( rel, fskip )
{
	this.walker = new DOMWalker( rel );
	// Constant:
	this.fskip = fskip;		// function for skipping over elements

	// Changed only externally:
	this.targetRel = null;
	this.targetWord = 0;
	this.targetChar = 0;
	
	// State info:
	this.currNode = rel;	// the current node while walking
	this.currChars = 0;		// (was charOffset) chars inside currNode
	
	this.inTargetRel = false;	// are we after targetPoint.rel been encountered?
	this.inTargetWord = false;	// are we in the word referred to by targetPoint.words?
	this.targetWords = 0;		// total words counted since targetPoint.rel
	this.targetWordChar = 0;	// chars counted inside target word
	
	this.inWord = false;	// is the walker currently in a word?
	this.endTag = false;	// distinguishes start from end tags when element returned
	this.atNodeEnd = false;//true if the walker is at the end of the current text node
	this.eof = false;		// is the walker at document end?
	
	trace ( 'WordPointWalker', 'WordPointWalker INIT' );
	return this;
}

/** Set a destination point for the walk
 * The container for the destination point must be the current node, or it must
 * be ahead in the document. */
WordPointWalker.prototype.setTarget = function( rel, words, chars )
{
	trace( 'WordPointWalker', ' WordPointWalker setTarget(' + rel.tagName + ( rel.id ? '#' + rel.id : '' ) + ', ' + words + ', ' + chars + ')' );
	if ( this.currNode == rel || rel == this.targetRel )	// because node might not be an element
	{
		this.inTargetRel = true;
		if ( this.targetWords == words )
		{
			trace( 'WordPointWalker', ' WordPointWalker - start in target word' );
			this.inTargetWord = true;
		}
		else
		{
			trace( 'WordPointWalker', ' WordPointWalker - start in target node (' + this.targetWords + ' != ' + words + ')' );
			this.inTargetWord = false;
			this.targetWordChar = 0;
		}
	}
	else
	{
		this.inTargetRel = false;
		this.targetWords = this.targetWordChar = 0;
	}
	// mustn't do this before some of the above tests
	this.targetRel = rel;
	this.targetWord = words;
	this.targetChar = chars;
}

WordPointWalker.prototype.walkToTarget = function( rel, words, chars )
{
	this.setTarget( rel, words, chars );
	while ( ! this.walk( ) )
		;
	return ! this.eof;
}

/** Get the next block of text.  Returns true if the destination is found.
 *  If the walker passes the end of the document, it returns true and
 *  sets eof to true. */
WordPointWalker.prototype.walk = function()
{
	// When true, this indicates there was a wordbreak (due to a breaking element)
	// before the returned chunk of text.
	this.breakBefore = false;
	
	// Walk to the next node
	while ( true )
	{
		// Only read the next node when ready for it
		if ( this.atNodeEnd )
		{
			if ( this.fskip ? ! this.walker.walk( ! this.fskip( this.walker.node ) )
				: ! this.walker.walk( true ) )
			{
				this.eof = true;
				trace( 'WordPointWalker', ' WordWalker DONE(1)');
				return true;
			}
			this.currNode = this.walker.node;
			this.endTag = this.walker.endTag;
			this.currChars = 0;
			this.atNodeEnd = false;
			if ( this.currNode == this.targetRel )
			{
				this.inTargetRel = true;
				if ( ! this.walker.endTag )
					this.targetWords = 0;
				// I'm not sure why I had this and can't convince myself it makes sense:
				// this.targetWords = this.inWord ? 1 : 0;
			}
			if ( ELEMENT_NODE == this.currNode.nodeType && domutil.isBreakingElement( this.currNode.tagName ) )
				this.breakBefore = true;
			trace( 'WordPointWalker', ' WordPointWalker in <' + this.currNode.tagName + '>'
				+ ( this.currNode == this.targetRel && ! this.endTag ? ' (target rel)' : '' ) );
		}
		
		// Only if we're past the target rel should we look inside the text
		if ( this.inTargetRel )
		{
			// All words are in text elements
			if ( TEXT_NODE == this.currNode.nodeType )
			{
				// trace( 'WordPointWalker', ' WordPointWalker - text node' );
				var s = this.currNode.nodeValue.replace( /(\s|\u00a0)/g, ' ' );
				
				// We're currently in a word that has already been counted
				if ( this.inWord )
				{
					// We're in the destination word
					if ( this.inTargetWord )
					{
						// inword remains true even crossing whitespace boundaries now
						
						// See if we can get all the characters we need
						if ( s.length - this.currChars >= this.targetChar - this.targetWordChars )
						{
							this.currChars += this.targetChar - this.targetWordChars;
							this.targetWordChars = this.targetChar;
							trace( 'WordPointWalker', ' WordWalker DONE(2) at ' + this.targetWords + '/' + this.currChars );
							return true;
						}
						// If not, get what we can and return
						else
						{
							this.targetWordChars += s.length - this.currChars;
							this.currChars = s.length;
							this.atNodeEnd = true;
							trace( 'WordPointWalker', ' WordWalker node end(3) at ' + this.targetWords + '/' + this.currChars );
							return false;
						}
					}
					// The normal case is to skip the initial non-whitespace sequence
					else
					{
						if ( ' ' != s.charAt( this.currChars ) )
						{
							var spaceOffset = s.indexOf( ' ', this.currChars );
							if ( -1 == spaceOffset )
							{
								// we've hit the end of the current node;  get what we can and return
								this.currChars = s.length;
								this.atNodeEnd = true;
								trace( 'WordPointWalker', ' WordWalker node end(4) at ' + this.targetWords + '/' + this.currChars );
								return false;
							}
							else
								// jump past the current word
								this.currChars = spaceOffset;
						}
						this.inWord = false;
					}
				}
				else
					this.currChars = 0;
				
				// Now iterate over subsequent words
				while ( true )
				{
					// pass over leading whitespace
					while ( ' ' == s.charAt( this.currChars ) && this.currChars < s.length )
						++this.currChars;
					
					// Even if this character offset is the start of a word, it
					// hasn't been counted as such yet.
					this.inWord = false;
					
					// If there's no more in this block of text, return
					if ( s.length == this.currChars )
					{
						this.atNodeEnd = true;
						trace( 'WordPointWalker', ' WordWalker node end(5) at ' + this.targetWords + '/' + this.currChars );
						return false;
					}
					
					// Count this word
					++this.targetWords;
					this.inWord = true;
					
					// Is this the target word?
					if ( this.targetWords == this.targetWord )
					{
						// OK, just grab the characters
						if ( s.length - this.currChars >= this.targetChar )
						{
							this.targetWordChars = this.targetChar;
							this.currChars += this.targetChar;
							trace( 'WordPointWalker', ' WordWalker DONE(6) at ' + this.targetWords + '/' + this.currChars );
							return true;
						}
						// Otherwise keep going to get all those characters
						else
						{
							this.targetWordChars = s.length - this.currChars;
							this.currChars += this.targetWordChars;
							this.inTargetWord = true;
							this.atNodeEnd = true;
							trace( 'WordPointWalker', ' WordWalker node end(7) at ' + this.targetWords + '/' + this.currChars );
							return false;
						}
					}
						
					// Move past this word to the next whitespace sequence
					var wsPos = s.indexOf( ' ', this.currChars );
					
					// If the word isn't here, return s.length
					if ( -1 == wsPos )
					{
						this.inWord = true;
						this.atNodeEnd = true;
						this.currChars = s.length;
						trace( 'WordPointWalker', ' WordPointWalker node end(8) at ' + this.targetWords + '/' + this.currChars );
						return false;
					}
					else
					{
						this.currChars = wsPos;
						this.inWord = false;
					}
				}
			}
			// Words break on block elements
			else if ( ELEMENT_NODE == this.currNode.nodeType )
			{
				//trace( 'WordPointWalker', ' WordPointWalker - element node (' + this.currNode.tagName + ')' );
				// Note that ELEMENT_NODE is returned at both start and end tags
				if ( domutil.isBreakingElement( this.currNode.tagName ) )
				{
					this.inWord = false;
					this.breakBefore = true;
				}
				this.atNodeEnd = true;
			}
		}
		// Not in the target rel, so move along
		else
		{
			trace( 'WordPointWalker', ' WordPointWalker - still looking for target node' );
			// Haven't encountered the target rel: move along
			this.atNodeEnd = true;
			if ( TEXT_NODE == this.currNode.nodeType )
			{
				var s = this.currNode.nodeValue.replace( /(\s|\u00a0)/g, ' ' );
				this.currChars = s.length;
				trace( 'WordPointWalker', ' WordPointWalker node end(9)' );
				return false;
			}
		}
	}
}

WordPointWalker.prototype.destroy = function()
{
	this.currNode = null;
	this.walker.destroy();
	this.walker = null;
}

/**
 * Note that words index from 1, but chars index from zero (!)
 */
function WordOffsetToCharOffset( s, words, chars, inword )
{
	// If inword is true, don't count the first non-whitespace sequence as a word
	if ( inword )
	{
		if ( ' ' != s.charAt( chars ) )
			chars = s.indexOf( ' ', chars );
	}
	
	while ( true )
	{
		// pass over leading whitespace
		while ( ' ' == s.charAt( chars ) && chars < s.length )
			++chars;
		
		// If the word isn't here, return s.length
		if ( s.length == chars )
			return s.length;
		
		// Is this the desired word?
		if ( words == 1 )
			return chars;
		
		// Count this word
		--words;
		
		// Move past this word to the next whitespace sequence
		chars = s.indexOf( ' ', chars );
		
		// If the word isn't here, return s.length
		if ( -1 == chars )
			return s.length;
	}
}



/* ********************
 * W3C Range object and IE text range stuff
 * ********************/

/*
 * Used for converting a (container,offset) pair as used by the W3C Range object
 * to a character offset relative to a specific element.
 */
function getContentOffset( rel, container, offset, fskip )
{
	var sofar = 0;
	
	// Start with rel and walk forward until we hit the range reference
	var node = rel;
	while ( node != container && node != null)
	{
		if ( TEXT_NODE == node.nodeType || CDATA_SECTION_NODE == node.nodeType )
			sofar += node.length;
		node = domutil.walkNextNode( node, fskip );
	}
	if ( null == node )
		return 0;

	// First case:  a character offset in a text node (most common case for selection ranges)
	if ( TEXT_NODE == node.nodeType || CDATA_SECTION_NODE == node.nodeType )
	{
		//trace( 'getContentOffset ' + container + ',' + offset + ' -> ' + (sofar+offset) );
		return sofar + offset;
	}
	// Second case:  a child element offset within a non-text node
	else
	{
		// Walk forward through child nodes until we hit the specified offset
		node = node.firstChild;
		for ( var i = 0;  i < offset;  ++i )
		{
			if ( null == node )
				debug( 'Error in getContentOffset:  invalid element offset' );
			sofar += domutil.nodeTextLength( node );
			node = node.nextSibling;
		}
		return sofar;
	}
}

function getPortableSelectionRange( )
{
	var range;
	// W3C Range object.  getSelection supported in Mozilla, proposed for HTML5
	if ( window.getSelection )
	{
		var selection = window.getSelection( );
		if ( null == selection.rangeCount || 0 == selection.rangeCount )
			return null;
		// TODO: it has never failed, but this may not work if there are multiple ranges.
		// How to know which one is the focus?
		return selection.getRangeAt( 0 );
	}
	// Internet Explorer
	else if ( document.selection )
	{
		return getSelectionRangeIE( );
		if ( null == range )
			return null;
	}
	// No support
	else
		return null;
}	

/**
 * Get the position and length of a text selection in Internet Explorer
 * returns an object with the following properties:
 * .container
 * .offset
 * .length
 */
function getSelectionRangeIE( fskip )
{
	// Return if there's no selection
	if ( document.selection.type == 'None' )
		return null;
	
	// This will be the return value
	var result = new Object();
	
	// Now get the selection and its length.
	// I will try to restrain my frustration.  Because there's a mismatch between the text
	// that IE returns here, and the sequence of text nodes it displays in the DOM tree.
	// If there's a paragraph break, for example, IE will return CR-LF here.  But in the DOM,
	// it will not report any whitespace between the end of one and the start of the other.
	// So, *we can't trust this length*.  Instead, calculate a non-whitespace length and work
	// with *that*.  I will not swear.  I will not swear.  I will not strike microsoft in its
	// big bloody nose with a cluestick the size of manhattan.  Morons.
	var range = document.selection.createRange( );
	var length = range.text.length;
	var nws_length = range.text.replace( /(\s|\u00a0)/g, '' ).length;
	
	// It is necessary to shrink the range so that there are no element
	// boundaries within it.  Otherwise, IE will add missing start and
	// end tags on copy, and add more strange tags on paste, so writing
	// the marker wouldn't be safe.
	range.moveEnd( 'character', 1 - length );

	// Write a marker with a unique ID at the start of the range.
	// A search for this will find the location of the selection.
	var html = range.htmlText;
	range.pasteHTML( '<span id="rangeStart"></span>' + html );
	var markerElement = document.getElementById( 'rangeStart' );
	
	// Find the location of the marker relative to its parent element
	if ( markerElement.previousSibling )
	{
		result.startContainer = markerElement.previousSibling;
		result.startOffset = getContentOffset( result.startContainer, markerElement, 0, null );
	}
	else
	{
		// Special case, because startContainer *must* be a text node.  See below:
		// in this case, after the marker has been deleted the start container must
		// be updated.
		result.startContainer = markerElement.parentNode;
		result.startOffset = 0;
	}
	
	// If the text starts with a space, IE will strip it, so we need to add it back in.
	if ( html.substr( 0, 1 ) == ' ' )
		markerElement.parentNode.insertBefore( document.createTextNode( ' ' ), markerElement );
	// Remove the marker.
	markerElement.parentNode.removeChild( markerElement );
	domutil.portableNormalize( markerElement.parentNode );
	
	var walker;
	// Make sure the start marker is a text node.  This may not be the case if there was no node 
	// preceding the marker (see special case above).
	if ( TEXT_NODE != result.startContainer.nodeType )
	{
		walker = new DOMWalker( result.startContainer );
		while ( null != walker.node && TEXT_NODE != walker.node.nodeType )
			walker.walk( ! fskip || ! fskip( walker.node ) );
		result.startContainer = walker.node;
	}
	
	// Convert the length to a container,offset pair as used by W3C
	//var end = walkUntilLen( result.startContainer, result.startOffset + length );
	//result.endContainer = end.container;
	//result.endOffset = end.offset;
	
	// Now we have to count the correct number of non-whitespace characters (see explanation
	// of Microsoft mental disibility above).
	
	var remains = nws_length;
	walker = new DOMWalker( result.startContainer );
	while ( null != walker.node && remains > 0 )
	{
		if ( TEXT_NODE == walker.node.nodeType )
		{
			// So that we only need to compare with spaces later
			var nodeStr = walker.node.nodeValue.replace( /\s/g, ' ' );
			nodeStr = nodeStr.replace( /\u00a0/g, ' ' );
			// Iterate over characters, counting only those that aren't spaces
			var i = ( walker.node == result.startContainer ) ? result.startOffset : 0;
			while ( i < nodeStr.length )
			{
				if ( ' ' != nodeStr.charAt( i ) )
					--remains;
				// If we've counted enough spaces, then this is the end point
				if ( 0 == remains )
				{
					result.endContainer = walker.node;
					result.endOffset = i + 1;
					break;
				}
				++i;
			}
		}
		walker.walk( ! fskip || ! fskip( walker.node ) );
	}
	
	// A full implementation would need to replace the selection here, because
	// adding and removing text clears it.  For annotation, that's not necessary.
	
	return result;
}


/**
 * Create a normalized range, that is, a range consisting of a start and length, both
 * calculated from the same containing element, which is passed in.
 * skipClass - any elements with this class will not be included in the count
 */
function NormalizedRange( range, rel, fskip )
{
	// must ensure that the range starts and ends within the passed rel element
	//if ( ! isElementDescendant( range.startContainer, rel ) || ! isElementDescendant( range.endContainer, rel ) )
	//	return null;

	var nrange = new Object();
	nrange.container = rel;
	nrange.offset = getContentOffset( rel, range.startContainer, range.startOffset, fskip );
	nrange.length = getContentOffset( rel, range.endContainer, range.endOffset, fskip ) - nrange.offset;
	return nrange;
}

/**
 * Get the text inside a TextRange
 * All whitespace (including that resulting from breaking elements) is reduced to a single space.
 * While the built-in toString() method would do this, we need the ability to skip
 * content (e.g. marginalia insertions).  There are certain cases this may not handle.
 * This also assumes that the range points to text nodes at the start and end (otherwise 
 * walking won't work).
 * mode can be one of:
 *  'text' - text content only (default)
 *  'breaking-tags' - embed breaking tags (no attributes)
 *  'tags' - embed all tags (no attributes)
 * If tags are embedded, breaking tags are always preceeded/folowed by a space.  Thus tags can be stripped
 * and &amp; &lt; &gt; replaced and the result will be the same as 'text' mode.
 */
RANGETEXT_TEXT = 0;
RANGETEXT_BREAKING = 1;
RANGETEXT_TAGS = 2;
function getTextRangeContent( range, fskip, mode )
{
	var s;
	// Special case
	if ( range.startContainer == range.endContainer )
		s = range.startContainer.nodeValue.substring( range.startOffset, range.endOffset );
	else
	{
		s = range.startContainer.nodeValue.substring( range.startOffset, range.startContainer.length );
		var walker = new DOMWalker( range.startContainer );
		walker.walk( ); // I'm uncertain about the need for this call
		while ( null != walker.node && walker.node != range.endContainer )
		{
			if ( TEXT_NODE == walker.node.nodeType )
			{
				if ( RANGETEXT_TEXT == mode )
					s += walker.node.nodeValue;
				else
					s += walker.node.nodeValue.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
			}
			else if ( ELEMENT_NODE == walker.node.nodeType )
			{
				if ( domutil.isBreakingElement( walker.node.tagName ) )
				{
					if ( RANGETEXT_TEXT == mode )
						s += ' ';
					else if ( RANGETEXT_BREAKING == mode || RANGETEXT_TAGS == mode )
						s += ( walker.startTag ? ' <' : '</' ) + walker.node.tagName + ( walker.startTag ? '>' : '> ' );
				}
				else if ( RANGETEXT_TAGS == mode )
				{
					s += '<' + ( walker.startTag ? '' : '/' ) + walker.node.tagName + '>';
				}
			}
			walker.walk( ! fskip( walker.node ) );	
		}
	
		// Pick up content from the last node
		s += range.endContainer.nodeValue.substring( 0, range.endOffset );
		walker.destroy( );
	}
	
	// Normalize spaces
	s = s.replace( /(\s|\u00a0)\s*/g, '$1' );
	s = s.replace( /(\s|\u00a0)$/, '' );
	s = s.replace( /^(\s|\u00a0)/, '' );
	
	return s;
}

/**
 * Get the length of a text range, in characters
 */
function getTextRangeLength( range, fskip )
{
	// We might be pointing to a skipable node to start with.  Move past it.
	var node = range.startContainer;
	if ( fskip( node ) )
		node = walkNextNode( node, fskip );

	var len = 0;
	while ( null != node )
	{
		// grab text if appropriate
		if ( TEXT_NODE == node.nodeType )
		{
			// This case might be broken;  I don't think I've ever tested it.
			if ( node == range.startContainer && node == range.endContainer )
				return range.endOffset - range.startOffset;
			else if ( node == range.startContainer )
				len = node.length - range.startOffset; 
			else if ( node == range.endContainer )
				return len + range.endOffset;
		}
		node = walkNextNode( node, fskip );
			
	}
	return -1;
}

