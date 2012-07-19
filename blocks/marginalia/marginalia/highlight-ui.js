/*
 * highlight-ui.js
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
 * $Id: highlight-ui.js 537 2012-05-30 21:51:38Z geof.glass $
 */

Marginalia.C_HIGHLIGHT = Marginalia.PREFIX + 'annotation';// class given to em nodes for highlighting

PostMicro.prototype.wordRangeFromAnnotation = function( marginalia, annotation )
{
	var wordRange = null;
	// XPath range is faster, but we can only use it if the browser supports it
	if ( annotation.getXPathRange( ) && this.getContentElement( ).ownerDocument.evaluate )
		wordRange = WordRange.fromXPathRange( annotation.getXPathRange( ), this.getContentElement( ), marginalia.skipContent );
	if ( ! wordRange && annotation.getSequenceRange( ) )
		wordRange = WordRange.fromSequenceRange( annotation.getSequenceRange( ), this.getContentElement( ), marginalia.skipContent );
	return wordRange;
}

/**
 * Display a single highlighted range
 * Inserts em tags of class annotation were appropriate
 */
PostMicro.prototype.showHighlight = function( marginalia, annotation )
{
	var startTime = new Date( );
	
	// TODO: How to handle zero-length ranges?  (Currently Marginalia hangs.)
	// I think the answer is to fix all these conversion functions etc. so that they
	// work consistently and correctly when dealing with zero-length ranges.
	// A zero-length range should be represented by an <em> element in the text with
	// no content;  for insert edit actions this code would then do the right thing.
	
	trace( 'show-highlight', 'Show highlight for annotation ' + annotation.toString( ) );
	
	// Word range needed for conversion to text range and for later calculations
	var wordRange = this.wordRangeFromAnnotation( marginalia, annotation );
	if ( null == wordRange )
	{
		trace( 'find-quote', 'Annotation ' + annotation.getId() + ' not within the content area.' );
		return false;
	}
	trace( 'show-highlight', 'WordRange constructed' );
	
	//setTrace( 'WordPointWalker', true );		// Show return values from WordPointWalker
	// TODO: check whether the range even falls within the content element
	var parts = wordRange.partition( marginalia.skipContent );
	highlightRanges = parts.ranges;
	actual = parts.quote;
	
	// Confirm whether the actual text matches what's expected in the annotation quote
	var quote = annotation.getQuote() ? annotation.getQuote() : '';
	actual = actual.replace( /\s+|\u00a0\s*/g, ' ' );
	quote = quote.replace( /\s+|\u00a0\s*/g, ' ' );
	if ( actual != quote )
	{
		// Older versions (before 2007-06-05) have some context calculation code which could be
		// modified and used here.
		var rangeStr = annotation.getSequenceRange( ) ? annotation.getSequenceRange( ).toString() : '';
		trace( 'find-quote', 'Annotation ' + annotation.getId() + ' range (' + rangeStr + ') actual \"' + actual + '\" doesn\'t match quote "' + quote + '"' );
		return false;
	}
	else
		trace( 'find-quote', 'Quote found: ' + actual );
	trace( 'show-highlight', 'Found quote' );
	
//setTrace( 'WordPointWalker', false );		// Show return values from WordPointWalker
	
	// Now iterate over the ranges and highlight each one
	var lastHighlight = null;  // stores the last highlighted area
	for ( var i = 0;  i < highlightRanges.length;  ++i )
	{
		var range = highlightRanges[ i ];
		var node = range.startContainer;
		
		//trace( 'show-highlight', 'Range ' + String(i) + ': ' + range.startContainer.nodeValue.substr(0,40) );

		// Is <em> valid in this position in the document?  (It might well not be if
		// this is a script or style element, or if this is whitespace text in
		// certain other nodes (ul, ol, table, tr, etc.))
		if ( domutil.isValidHtmlContent( node.parentNode.tagName, 'em' ) )
		{
			var newNode;
			var text = node.nodeValue + "";
			// break the portion of the node before the annotation off and insert it
			if ( range.startOffset > 0 )
			{
				newNode = document.createTextNode( text.substring( 0, range.startOffset ) );
				node.parentNode.insertBefore( newNode, node );
			}
			// replace node content with annotation
			newNode = document.createElement( 'em' );
			
			newNode.className = Marginalia.C_HIGHLIGHT + ' ' + Marginalia.ID_PREFIX + annotation.getId();
			if ( marginalia.showActions && annotation.getAction() )
				newNode.className += ' ' + Marginalia.C_ACTIONPREFIX + annotation.getAction();
			newNode.onmouseover = _hoverAnnotation;
			newNode.onmouseout = _unhoverAnnotation;
			newNode[ Marginalia.F_ANNOTATION ] = annotation;
			node.parentNode.replaceChild( newNode, node );
			
			if ( marginalia.showActions && 'edit' == annotation.getAction() && annotation.getQuote() )
			{
				var delNode = document.createElement( 'del' );
				delNode.appendChild( node );
				newNode.appendChild( delNode );
			}
			else
				newNode.appendChild( node );
			
			node.nodeValue = text.substring( range.startOffset, range.endOffset );
			lastHighlight = newNode;
			node = newNode;	// necessary for the next bit to work right

			// break the portion of the node after the annotation off and insert it
			if ( range.endOffset < text.length )
			{
				newNode = document.createTextNode( text.substring( range.endOffset ) );
				if ( node.nextSibling )
					node.parentNode.insertBefore( newNode, node.nextSibling );
				else
					node.parentNode.appendChild( newNode );
			}
		}
	}
	
	if ( lastHighlight )
	{
		domutil.addClass( lastHighlight, Marginalia.C_LASTHIGHLIGHT );
		// If this was a substitution or insertion action, insert the text
		if ( marginalia.showActions && 'edit' == annotation.getAction() && annotation.getNote() )
			this.showActionInsert( marginalia, annotation );
		// If there's a link from this annotation, add the link icon
		if ( annotation.getLink() )
			this.showLink( marginalia, annotation );
	}
	var endTime = new Date( );
	trace( 'highlight-timing', 'ShowAnnotation took ' + ( endTime - startTime ) + 'ms for ' + annotation.toString( ) );
	return true;
}

/**
 * Display insertion text for insert or substitute actions
 */
PostMicro.prototype.showActionInsert = function( marginalia, annotation )
{
	trace( 'actions', 'showActionInsert for ' + annotation.getQuote() );
	var highlights = domutil.childrenByTagClass( this.getContentElement( ), 'em', Marginalia.ID_PREFIX + annotation.getId(), null, marginalia.skipContent );
	for ( var i = 0;  i < highlights.length;  ++i )
	{
		if ( domutil.hasClass( highlights[ i ], Marginalia.C_LASTHIGHLIGHT ) )
		{
			// TODO: should check whether <ins> is valid in this position
			var lastHighlight = highlights[ i ];
			var insNode = document.createElement( 'ins' );
			insNode.appendChild( document.createTextNode( annotation.getNote() ) );
			lastHighlight.appendChild( insNode );
			trace( 'actions', 'Insert text is ' + annotation.getNote() );
/*			// Insert *after* the annotation highlight
			if ( lastHighlight.nextSibling )
				lastHighlight.parentNode.insertBefore( insNode, lastHighlight.nextSibling );
			else
				lastHighlight.parentNode.appendChild( insNode );
*/			domutil.addClass( insNode, Marginalia.ID_PREFIX + annotation.getId() );
		}
	}
}


/**
 * Test function for removing highlights, edits, and annotation links
 */
PostMicro.prototype.highlightStripTest = function( tnode, emclass )
{
	if ( domutil.matchTagClass( tnode, 'em', Marginalia.C_HIGHLIGHT ) && ( ! emclass || domutil.hasClass( tnode, emclass ) ) )
		return domutil.STRIP_TAG;
	else if ( tnode.parentNode && domutil.hasClass( tnode.parentNode, Marginalia.C_HIGHLIGHT )
		&& ( ! emclass || domutil.hasClass( tnode.parentNode, emclass ) ) )
	{
		if ( domutil.matchTagClass( tnode, 'ins', null ) || domutil.matchTagClass( tnode, 'a', null ) )
			return domutil.STRIP_CONTENT;
		else if ( domutil.matchTagClass( tnode, 'del', null ) )
			return domutil.STRIP_TAG;
	}
	return domutil.STRIP_NONE;
}

/**
 * Recursively remove highlight markup for an annotation
 */
PostMicro.prototype.removeHighlight = function ( marginalia, annotation )
{
	this.hideLink( marginalia, annotation );
	
	var post = this;
	var contentElement = this.getContentElement( );
	var emClass = annotation ? Marginalia.ID_PREFIX + annotation.getId() : Marginalia.C_HIGHLIGHT;
	var stripTest = function( tnode )  {  return post.highlightStripTest( tnode, emClass );  };

	var highlights = domutil.childrenByTagClass( contentElement, 'em', emClass, null, null );
	for ( var i = 0;  i < highlights.length;  ++i )
	{
		highlights[ i ][ Marginalia.F_ANNOTATION ] = null;
		if ( highlights[ i ].parentNode) // might already have been stripped
		{
			domutil.stripMarkup( highlights[ i ], stripTest, true );
			domutil.unwrapElementChildren( highlights[ i ], true );
		}
	}
	
	// This normalization was (erroneously) commented out - I think because it's so slow.
	// The best solution would be to a) modify stripMarkup to join adjacent text elements
	// as it goes, or b) write a walker to join relevant text elements.
	// Frankly, it seems fast enough to me.  Perhaps I removed it while making necessary
	// removals elsewhere, or perhaps my short document isn't a sufficient speed test.
	// 
	// No longer necessary - stripMarkup does normalization itself
	//portableNormalize( contentElement );
}


/** 
 * Recursively remove all highlights
 */
PostMicro.prototype.removeAllHighlights = function( marginalia )
{
	var contentElement = this.getContentElement( );
	for ( var i = 0;  i < highlights.length;  ++i )
		highlights[ i ][ Marginalia.F_ANNOTATION ] = null;
	var stripTest = function( tnode )  {  return post.highlightStripTest( tnode, null );  };
	domutil.stripMarkup( contentElement, stripTest, true );	
}
