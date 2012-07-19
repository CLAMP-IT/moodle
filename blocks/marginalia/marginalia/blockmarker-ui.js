/*
 * marginalia-blockmarkers.js
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
 * $Id: blockmarker-ui.js 537 2012-05-30 21:51:38Z geof.glass $
 */

Marginalia.C_MARKER = Marginalia.PREFIX + 'marker';		// individual block marker
Marginalia.C_USERCOUNT = Marginalia.PREFIX + 'annotation-user-count';		// contains user count in block marker
Marginalia.C_ANNOTATIONSFETCHED = Marginalia.PREFIX + 'fetched';	// indicates a block's annotations have been fetched


/**
 * Show Per-Block User Counts
 */
Marginalia.prototype.showPerBlockUserCounts = function( url )
{
	this.annotationService.listBlocks( url, _showPerBlockUserCountsCallback );
}

function _showPerBlockUserCountsCallback( xmldoc )
{
	if ( xmldoc )
	{
		var rangeInfos = parseRangeInfoXml( xmldoc );
		var marginalia = window.marginalia;
		for ( var i = 0;  i < rangeInfos.length;  ++i )
		{
			var info = rangeInfos[ i ];
			
			// Only include markers for non-edit annotations by users other than the displayed one
			for ( var j = 0;  j < info.users.length;  ++j )
			{
				var user = info.users[ j ];
				if ( user.noteCount > 0 )
				{
					var post = marginalia.listPosts( ).getPostByUrl( info.url, marginalia.baseUrl );
					post.showPerBlockUserCount( marginalia, info );
					break;
				}
			}
		}
	}
}

/**
 * Show a perBlockCount marker
 * Assumes that markers are being shown in order
 */
PostMicro.prototype.showPerBlockUserCount = function( marginalia, info )
{
	var node = info.resolveStart( this.getContentElement( ) );
	if ( node && info.sequenceRange )
	{
		var resolver = new SequencePathResolver( node, info.sequenceRange.start.path );
		do
		{
			// TODO: Inefficient to create so many SequencePath objects
			var point = new SequencePoint( resolver.getPath() );
			if ( point.compare( info.sequenceRange.end ) > 0 )
				break;
			if ( ELEMENT_NODE == node.nodeType )
				this.showBlockMarker( marginalia, info, resolver.getNode(), point );
		}
		while ( resolver.next( ) );
	}
}

/**
 * Return a function for handling a click on a block marker
 * Creating the function this way reduces the amount of context stored by the closure
 * markerElement - the marker in the left margin
 * url - the url required to fetch the correct annotations from the server
 * pointStr - the path to the block that was clicked on (excluding word or char specification)
 */
PostMicro.prototype.getBlockMarkerClickFcn = function( marginalia, markerElement, url, pointStr )
{
	var postMicro = this;
	return function() {
		if ( domutil.hasClass( markerElement, Marginalia.C_ANNOTATIONSFETCHED ) )
		{
			domutil.removeClass( markerElement, Marginalia.C_ANNOTATIONSFETCHED );
			postMicro.hideBlockAnnotations( marginalia, pointStr );
		}
		else
		{
			domutil.addClass( markerElement, Marginalia.C_ANNOTATIONSFETCHED );
			marginalia.showBlockAnnotations( url, pointStr );
		}
	};
}

/**
 * This hiding code is tricky and feels like a big hack.  The problem is that an annotation
 * may overlap two (or more) blocks, so it could be effectively fetched for multiple blocks.
 * Hiding annotations for one block should not hide those fetched for another, nor should it
 * hide "core" annotations - that is, annotations for the current display user which were shown
 * before any were fetched for the current page.
 */
PostMicro.prototype.hideBlockAnnotations = function( marginalia, pointStr )
{
	var annotations = this.listAnnotations( marginalia );
	var point = new SequencePoint( pointStr );
	var repositionNote = null;
	for ( var i = 0;  i < annotations.length;  ++i )
	{
		var annotation = annotations[ i ];
		var range = annotation.getSequenceRange( );
		if ( range )
		{
			// displayUserId replaced by displayAccess, too tired to figure
			// out exactly what this test was for so commenting instead of deleting
			// 20100221: access is obsolete anyway, now relpaced by sheet
			//if ( annotation.getUserId( ) != marginalia.displayUserId )
			//{
				// if we've run past the last relevant annotation, don't bother with the rest
				if ( range.start.comparePath( point ) > 0 )
					break;
				else if ( range.end.comparePath( point ) >= 0 )
				{
					annotation.fetchCount -= 1;
					if ( 0 == annotation.fetchCount )
					{
						// Remove the note and keep track of the first note that needs to be
						// repositioned.
						var note = annotation.getNoteElement( );
						var nextNote = this.removeNote( marginalia, annotation );
						this.removeHighlight( marginalia, annotation );
						if ( ! repositionNote || note == repositionNote )
							repositionNote = nextNote;
					}
				}
			//}
		}
	}
	if ( repositionNote )
		this.repositionSubsequentNotes( marginalia, repositionNote );
}

PostMicro.prototype.showBlockMarker = function( marginalia, info, block, point )
{
	var markers = marginalia.selectors[ 'mia_markers' ].node( this.getElement( ) );
	if ( markers )
	{
		var countElement;
		var markerElement = block.markerElement;
		
		// Create the marker
		if ( ! markerElement )
		{
			block.blockMarkerUsers = [ ];

			markerElement = domutil.element( 'div', {
				className: Marginalia.C_MARKER,
				blockElement: block
			} );
			countElement = domutil.element( 'span', {
				className: Marginalia.C_USERCOUNT,
				onclick: this.getBlockMarkerClickFcn( window.marginalia, markerElement, info.url, point.toString() )
			} );
			markerElement.appendChild( countElement );

			block.markerElement = markerElement;
			markers.appendChild( markerElement );
			
			this.positionBlockMarker( window.marginalia, markers, markerElement );
		}
		// The marker already exists - prepare to update it
		else
		{
			var countElement = domutil.childByTagClass( markerElement, 'span', null );
			while ( countElement.firstChild )
				countElement.removeChild( countElement.firstChild );
		}
		
		for ( var i = 0;  i < info.users.length;  ++i )
		{
			var user = info.users[ i ];
			// Don't include the currently-displayed user
			if ( user.noteCount > 0 ) //&& user.userid != marginalia.displayUserId )
				block.blockMarkerUsers[ block.blockMarkerUsers.length ] = user;
		}
		
		var userStr = '';
		for ( var i = 0;  i < block.blockMarkerUsers.length;  ++i )
		{
			var user = block.blockMarkerUsers[ i ];
			userStr += userStr ? ', ' + user.userName : user.userName;
		}
			
		countElement.setAttribute( 'title', userStr );
		countElement.appendChild( document.createTextNode( String( block.blockMarkerUsers.length ) ) );
	}
}


/**
 * Adjust alignment and height of a block marker
 */
PostMicro.prototype.positionBlockMarker = function( marginalia, markers, markerElement )
{
	var blockElement = markerElement.blockElement;
	var blockOffset = domutil.getElementYOffset( blockElement, this.getElement( ) );
	var markersOffset = domutil.getElementYOffset( markers, this.getElement( ) );
	var offset = blockOffset - markersOffset;
	markerElement.style.top = String( offset ) + 'px';

	var height;
	var nextBlock;
	// Walk forward to the next breaking element
	var walker = new DOMWalker( blockElement );
	while ( walker.walk( true, false ) )
	{
		if ( ELEMENT_NODE == walker.node.nodeType && ! walker.endTag && domutil.isBreakingElement( walker.node.tagName ) )
			break;
	}
	
	// Was one found?  If so, don't extend this far down.
	if ( walker.node && ELEMENT_NODE == walker.node.nodeType && domutil.isBreakingElement( walker.node.tagName ) )
	{
		var nextTop = domutil.getElementYOffset( walker.node, this.getContentElement( ) );
		var thisTop = domutil.getElementYOffset( blockElement, this.getContentElement( ) );
		height = nextTop - thisTop;
	}
	else
		height = blockElement.offsetHeight;

	markerElement.style.height = String( height ) + 'px';
}

/**
 * Adjust the alignment and height of all block markers
 */
PostMicro.prototype.repositionBlockMarkers = function( marginalia )
{
	var markers = marginalia.selectors[ 'mia_markers' ].nodes( this.getElement( ) );
	if ( markers )
	{
		var markerElements = domutil.childrenByTagClass( this.getElement( ), null, Marginalia.C_MARKER, null );
		for ( var i = 0;  i < markerElements.length;  ++i )
			this.positionBlockMarker( marginalia, markers, markerElements[ i ] );
	}
}

