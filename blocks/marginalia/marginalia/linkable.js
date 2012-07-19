/*
 * linkable.js
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
 * $Id: linkable.js 537 2012-05-30 21:51:38Z geof.glass $
 */

/**
 * Enable link targets for when user clicks on a post
 */
function LinkablePosts( postPageInfo )
{
	this.postInfo = postPageInfo;
	this.prefix = prefix;
	
	var linkablePosts = this;
	
	this.fenable = function( )  {
		linkablePosts.enableLinkTargets( );
	};

	this.fdisable = function( )  {
		linkablePosts.disableLinkTargets( );
	};
	
	this.fclick = function( event )  {
		linkablePosts.clickLinkTarget( event );
	};
	
	this.fflash = function( )  {
		linkablePosts.flashLinkTarget( );
	};
	
	domutil.addEventListener( window, 'focus', this.fenable, false );
	domutil.addEventListener( window, 'blur', this.fdisable, false );
}

// These class names do not require a prefix
LinkablePosts.C_MAKELINKTARGET = 'linkableposts-makelinktarget';// this content element's children can be made link targets by clicking
LinkablePosts.C_LINKTARGET = 'linkableposts-linktarget';			// the node is the clicked target of a link
LinkablePosts.C_FLASH = 'linkableposts-flash';					// the display is flashing this node

// Cookies:
LinkablePosts.CK_LINKING = 'marginalia-linking';
LinkablePosts.CK_LINKURL = 'marginalia-link-url';




/**
 * If the window gains focus and linking is on, enable link targets
 */
LinkablePosts.prototype.enableLinkTargets = function( )
{
	if ( readCookie( LinkablePosts.CK_LINKING ) )
	{
		var posts = this.postInfo.getAllPosts( );
		for ( var i = 0;  i < posts.length;  ++i )
		{
			var post = posts[ i ];
			var content = post.getContentElement( );
			domutil.addClass( content, LinkablePosts.C_MAKELINKTARGET );
			domutil.addEventListener( content, 'click', this.fclick, false );
		}
	}
}


/**
 * If the window loses focus, disable link targets
 */
LinkablePosts.prototype.disableLinkTargets = function( )
{	
	var postInfo = PostPageInfo.getPostPageInfo( document );
	var posts = postInfo.getAllPosts( );
	for ( var i = 0;  i < posts.length;  ++i )
	{
		var post = posts[ i ];
		var content = post.getContentElement( );
		domutil.removeClass( content, LinkablePosts.C_MAKELINKTARGET );
		domutil.removeEventListener( content, 'click', this.fclick, false );
	}
	domutil.removeEventListener( window, 'blur', this.fdisable, false );
}


/**
 * The user has clicked on a link target.  Update the relevant annotation with
 * the new link.
 */
LinkablePosts.prototype.clickLinkTarget = function( event )
{
	event = domutil.getEvent( event );
	domutil.stopPropagation( event );
	
	var post = this.postInfo.postByElement( domutil.getEventTarget( event ) );
	if ( post )
	{
		var content = post.getContentElement( );
	
		// Need to look at parent targets until a block level element is found
		var target = domutil.getEventTarget( event, target );
		while ( 'block' != domutil.htmlDisplayModel( target.tagName ) )
			target = target.parentNode;
		
		// Calculate path to target
		var point = SequencePoint.fromNode( content, target );
		var path = point.toString( );
		//	var path = NodeToPath( content, target );
		var link = '' + window.location;
		if ( -1 != link.indexOf( '#' ) )
			link = link.substring( 0, link.indexOf( '#' ) );
		link = link + '#node-path:' + path;
		
		// Get the annotation
		var annotationId = readCookie( LinkablePosts.CK_LINKING );
		if ( null != annotationId )
		{
			// TODO: must replace ; characters in cookie
			createCookie( LinkablePosts.CK_LINKURL, link, 1 );
	
			domutil.removeEventListener( content, 'click', _clickLinkTarget, false );
			domutil.removeClass( content, LinkablePosts.C_MAKELINKTARGET );
			domutil.addClass( target, LinkablePosts.C_FLASH );
			target.flashcount = 4;
			setTimeout( this.fflash, 240 );
		}
	}
	
	// If the link was made from this window, leave editing mode
	this.disableLinkTargets( );
	_updateLinks( );
}

LinkablePosts.prototype.flashLinkTarget = function( )
{
	var target = domutil.childByTagClass( document.documentElement, null, LinkablePosts.C_FLASH, null );
	if ( target.flashcount > 0 )
	{
		if ( target.flashcount % 2 )
			domutil.removeClass( target, LinkablePosts.C_LINKTARGET );
		else
			domutil.addClass( target, LinkablePosts.C_LINKTARGET );
		target.flashcount -= 1;
		setTimeout( this.fflash, 240 );
	}
	else
	{
		domutil.removeClass( target, LinkablePosts.C_LINKTARGET );
		domutil.removeClass( target, LinkablePosts.C_FLASH );
		if ( target.flashcount )
			delete target.flashcount;
	}
}

