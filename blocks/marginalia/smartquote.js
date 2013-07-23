/*
 * Smartquote functions used in Moodle
 * built on CookieBus
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
 * $Id$
 */

function Smartquote( wwwroot, selectors, extService )
{
	this.wwwroot = wwwroot;
	this.selectors = selectors;
	this.extService = extService;
}

/**
 * Enable all smartquote buttons on the page
 * Buttons are found in posts with selector button.smartquote
 */
Smartquote.prototype.enable = function( postPageInfo, skipContent, params )
{
	// Use passed-in value for speed if possible
	if ( ! postPageInfo )
		postPageInfo = PostPageInfo.getPostPageInfo( document, this.selectors );

	// Enable smartquote buttons
	var posts = postPageInfo.getAllPosts( );
	for ( var i = 0;  i < posts.length;  ++i )
	{
		var button = domutil.childByTagClass( posts[ i ].getElement( ), 'button', 'smartquote', skipContent );
		if ( button )
			this.enableButton( button, posts[ i ], skipContent );
	}
}

/**
 * Enable a specific smartquote button
 * Must be a separate function from the loop in enableSmartquote to deal
 * correctly with Javascript dynamic scoping and closures
 */
Smartquote.prototype.enableButton = function( button, post, skipContent )
{
	var smartquote = this;
	var content = post.getContentElement( );
	var postId = Smartquote.postIdFromUrl( post.getUrl( ) );
	var f = function( ) { smartquote.quotePostMicro( content, skipContent, postId ); };
	addEvent( button, 'click', f );
}
	
/**
 * Calculate a post ID based on its URL
 * must have implementations for each kind of quoteable url
 */
Smartquote.postIdFromUrl = function( url )
{
	var matches = url.match( /^.*\/mod\/forum\/permalink\.php\?p=(\d+)/ );
	if ( matches )
		return Number( matches[ 1 ] );
	else
		return 0;
},
	
/**
 * Get a quote (selected text) from a postMicro with a given ID
 * Returns the quote as HTML with metadata included.  Note, however, that
 * any HTML tags in the selected text are stripped, and whitespace is
 * collapsed.
 */
Smartquote.prototype.getPostMicroQuote = function( content, skipContent, postId )
{
	// Test for selection support (W3C or IE)
	if ( ( ! window.getSelection || null == window.getSelection().rangeCount )
		&& null == document.selection )
	{
		alert( getLocalized( 'browser support of W3C range required for smartquote' ) );
		return false;
	}
		
	var textRange0 = getPortableSelectionRange();
	if ( null == textRange0 )
	{
		alert( getLocalized( 'select text to quote' ) );
		return false;
	}
	
	// Strip off leading and trailing whitespace and preprocess so that
	// conversion to WordRange will go smoothly.
	var textRange = TextRange.fromW3C( textRange0 );
	
	// Don't need a skip handler unless we're running on a page with Marginalia
	textRange = textRange.shrinkwrap( skipContent );
	if ( ! textRange )
	{
		// this happens if the shrinkwrapped range has no non-whitespace text in it
		alert( getLocalized( 'select text to quote' ) );
		return false;
	}
	
	var quote = getTextRangeContent( textRange, skipContent );
	quote = quote.replace( /(\s|\u00a0)+/g, ' ' );
	
	var postInfo = PostPageInfo.getPostPageInfo( document, this.selectors );
	var post = postInfo.getPostByElement( textRange.startContainer );
	var leadIn = '';
	if ( post )
	{
		var url = this.wwwroot + post.getUrl( this.wwwroot );
		// console.log( 'post url: ' + url );
		leadIn = '<p>' + ( post.getAuthorName( ) ? domutil.htmlEncode( post.getAuthorName( ) ) : 'Someone' )
			+ ( post.getUrl( ) ? ' <a href="' + domutil.htmlEncode( url ) + '">wrote</a>' : 'wrote' )
			+ ",</p>";
	}
	return leadIn + '<blockquote><p>' + domutil.htmlEncode( quote ) + '</p></blockquote>';
}
	
	
/**
 * Called when a quote button is clicked on a postMicro.  Extracts the
 * selected text, builds HTML with metadata, and publishes it on the
 * CookieBus.
 */
Smartquote.prototype.quotePostMicro = function( content, skipContent, postId )
{
	var pub = this.getPostMicroQuote( content, skipContent, postId );
	var bus = new CookieBus( 'smartquote' );
	if ( pub )
	{
		if ( bus.getSubscriberCount( ) > 0 )
		{
			bus.publish( pub );
	
			if ( this.extService )
				this.extService.createEvent( 'smartquote', 'send', pub, 'forum_post', postId );
		}
		else if ( this.wwwroot && postId )
		{
			bus.publish( pub );
			window.location = this.wwwroot + '/mod/forum/post.php?reply=' + postId;
	
			if ( this.extService )
				this.extService.createEvent( 'smartquote', 'new post', pub, 'forum_post', postId );
		}
	}
}
	
	
Smartquote.prototype.quoteAnnotation = function( annotation, loginUserId, postId )
{
	var quoteAuthor = annotation.getQuoteAuthorName( );
	var url = annotation.getUrl( );
	var quote = annotation.getQuote( );
	var note = annotation.getNote( );
	var noteAuthor = annotation.getUserName( );
	
	quote = quote.replace( /\s/g, ' ' );
	quote = quote.replace( /\u00a0/g, ' ' );

	var pub = '<p>' + ( quoteAuthor ? domutil.htmlEncode( quoteAuthor ) : 'Someone' )
		+ ( url ? ' <a href="' + domutil.htmlEncode( url ) + '">wrote,</a>' : ' wrote' )
		+ '</p><blockquote><p>' + domutil.htmlEncode( quote ) + '</p></blockquote>';
	if ( loginUserId == annotation.getUserId( ) )
	{
		if ( annotation.getNote( ) )
			pub += '<p>' + domutil.htmlEncode( note ) + '</p>';
	}
	else
	{
		note = note.replace( /\s/g, ' ' );
		note = note.replace( /\u00a0/g, ' ' );
		if ( note )
		{
			pub += '<p>Via ' + domutil.htmlEncode( noteAuthor ) + ', who noted,</p>'
				+ '<blockquote><p>' + domutil.htmlEncode( note ) + '</p></blockquote>';
		}
		else
			pub += '<p>(Via an annotation by ' + domutil.htmlEncode( noteAuthor ) + '.)</p>';
	}
	
	var bus = new CookieBus( 'smartquote' );
	// The user is editing a post:  paste the quote in there
	if ( bus.getSubscriberCount( ) > 0 )
	{
		bus.publish( pub );
		if ( this.extService )
			this.extService.createEvent( 'smartquote', 'send', quote, 'annotation', annotation.getId() );
	}
	// Otherwise, reply to this post and use the quoted text
	else if ( this.wwwroot && postId )
	{
		// Ideally this should never happen if the user doesn't have
		// permission to post to this forum.  Unfortunately, Moodle
		// doesn't calculate $canreply until *after* the page header
		// has been output.  This makes it difficult to know about it
		// here.  Marginalia could search for a Reply button on the
		// page, or it could calculate it itself, or it could set an
		// internal field after Moodle has done its calculation.  But
		// all of these are kludges that make relatively deep 
		// assumptions about Moodle's underlying behavior, so could
		// increase the likelihood of Marginalia breaking.
		bus.publish( pub );
		window.location = this.wwwroot + '/mod/forum/post.php?reply=' + postId;
	
		if ( this.extService )
			this.extService.createEvent( 'smartquote', 'new post', quote, 'annotation', annotation.getId() );
	}
}
	

function SmartquoteSubscriber( extService )
{
	this.extService = extService;
}

/**
 * Subscribe a named tiny MCE instance to smartquote events
 */
SmartquoteSubscriber.prototype.subscribeMCE = function( name, object_type, object_id )
{
	var subscriber = this;
	// Thank you oh thank you Moodle 2.0 for moving from HTMLArea to tinyMCE
	// This code was such a mess of weird exceptions before.
	var bus = new CookieBus( 'smartquote' );
	bus.subscribe( 1000, function( pub ) { // should it be mceInsertRawHTML?
		// Need to check whether the editor exists - if not, return false
		// so that forward,once quote items won't be used up.  (This can happen
		// if tinyMCE has not yet been initialized in a message reply window
		// opened by a quote button.)
		var mce = tinyMCE.get( name );
		if ( mce )
		{
			tinyMCE.execCommand( 'mceFocus', false, name );
			mce.execCommand( 'mceInsertContent', false, pub.value );
				
			if ( subscriber.extService )
				subscriber.extService.createEvent( 'smartquote', 'receive', pub.value, object_type, object_id );
		}
		return true;
	} );
	
	// Don't forget to unsubscribe if the window is unloaded
	addEvent( window, 'unload', function( ) { bus.unsubscribe( ); } );
	
	return bus;
}

