/*
 * MoodleMarginalia.js
 * Annotation functions specific to moodle.php (the generic name is in case
 * non-annotation code ends up in here).
 *
 * Marginalia has been developed with funding and support from
 * BC Campus, Simon Fraser University, and the Government of
 * Canada, the UNDESA Africa i-Parliaments Action Plan, and  
 * units and individuals within those organizations.  Many 
 * thanks to all of them.  See CREDITS.html for details.
 * Copyright (C) 2005-2011 Geoffrey Glass; the United Nations
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
 * $Id: MoodleMarginalia.js 549 2012-06-22 21:05:41Z geof.glass $
 */

function MoodleMarginalia( annotationPath, url, moodleRoot, userId, prefs, params )
{
	this.annotationPath = annotationPath;
	this.url = url;
	this.moodleRoot = moodleRoot;
	this.loginUserId = userId;
	this.sessionCookie = params.sessionCookie;
	this.preferences = new Preferences( 
		new RestPreferenceService( this.annotationPath + '/user-preference.php' ),
		prefs );
	this.sheet = prefs[ Marginalia.P_SHEET ];
	this.splash = prefs[ Marginalia.P_SPLASH ] == 'true' ? params[ 'splash' ] : null;
	this.useSmartquote = params.useSmartquote;
	this.allowAnyUserPatch = params.allowAnyUserPatch;
	this.smartquoteIcon = params.smartquoteIcon;
	this.handlers = params.handlers;
	this.course = params.course;
	this.smartquoteService = params.smartquoteService;
	LocalizedAnnotationStrings = params.strings;

	var urlFunc = function( node )
	{
		// strip off leading character
		var id = node.id.substr( 1 );
		
		// Must first figure out which kind of page is being annotated,
		// then return the correct URL for an individual *post*, which may
		// not be the same as the page as a whole (e.g. in the case of the forum).
		var x = window.location.href.indexOf( '#' );
		var base = -1 == x ? window.location.href : window.location.href.substr( 0, x );
		// console.log( 'match URL ' + base );                                  
		var matches = base.match( /\/mod\/forum\/discuss.php\?d=([0-9]+)/ );
		if ( matches )
		{
			return '/mod/forum/permalink.php?p=' + node.id.substr( 1 );
		}
		else
			throw "Annotation not supported for this page URL in MoodleMarginalia.js.";
	};
	
	this.selectors = {
		post: new Selector( '.forumpost', '.forumpost table.forumpost' ),
		post_id: new Selector( function( root ) { return $( root ).prev( 'a' ); }, null, '@id' ),
		post_content: new Selector( '.content .posting', '.content .content .posting' ),
		post_title: new Selector( '.subject', '.content .subject' ),
		post_author: new Selector( '.author a', '.content .author a' ),
		post_authorid: null,
		post_date: null,
		post_url: new Selector( function( root) { return $( root ).prev( 'a' ); }, null, urlFunc ),
		mia_notes: new Selector( '.mia_margin', '.content .posting .mia_margin' )
	};
	// URL is blank!  Should be a function to construct it from window.location + '#' + id
}

/**
 * Callback on page load to initialize Marginalia.
 * Looks at the page URL to determine which URL resource this is and 
 * figure out how to set Marginalia up.  If annotation is not supported for
 * this resource nothing happens.  Checking here rather than in the PHP
 * minimizes the number of patches that need to be applied to existing Moodle
 * code.
 */
MoodleMarginalia.prototype.onload = function( )
{
	initLogging();

	// Check whether this page should have annotations enabled at all
	// The check is here rather in the PHP;  that minimizes the number of patches
	// that need to be applied to existing Moodle code.
	if ( ! this.loginUserId )
		return;
	
	// Must first figure out which kind of page is being annotated,
	// then return the correct URL for an individual *post*, which may
	// not be the same as the page as a whole (e.g. in the case of the forum).
	var x = window.location.href.indexOf( '#' );
	var base = -1 == x ? window.location.href : window.location.href.substr( 0, x );
	var matches = base.match( /\/mod\/forum\/discuss.php/ );
	if ( ! matches )
		matches = base.match( /\/mod\/forum\/post.php/ );
	if ( matches )
	{
		var selectors = {
			post: new Selector( '.forumpost', '.forumpost table.forumpost' ),
			post_id: new Selector( function( root ) { return $( root ).prev( 'a' ); }, null, '@id' ),
			post_content: new Selector( '.content .posting', '.content .content .posting' ),
			post_title: new Selector( '.subject', '.content .subject' ),
			post_author: new Selector( '.author a', '.content .author a' ),
			post_authorid: null,
			post_date: null,
			mia_notes: new Selector( '.mia_margin', '.content .posting .mia_margin' ),
			post_url: new Selector( function( root) { return $( root ).prev( 'a' ); }, null,
				function( node ) { return '/mod/forum/permalink.php?p=' + node.id.substr( 1 ); } )
		};
		this.init( selectors );
	}
}

MoodleMarginalia.prototype.init = function( selectors )
{
	var annotationService = new RestAnnotationService( this.annotationPath + '/annotate.php', {
		noPutDelete: true,
		csrfCookie: this.sessionCookie } );
	var keywordService = new RestKeywordService( this.annotationPath + '/keywords.php', true);
	keywordService.init( null );
	var moodleMarginalia = this;
	window.marginalia = new Marginalia( annotationService, this.loginUserId, this.sheet, {
		preferences: this.preferences,
		keywordService: keywordService,
		baseUrl:  this.moodleRoot,
		showBlockMarkers:  false,
		showActions:  false,
		onkeyCreate:  true,
		enableRecentFlag: true,
		allowAnyUserPatch: this.allowAnyUserPatch ? true : false,
		displayNote: function(m,a,e,p,i) { moodleMarginalia.displayNote(m,a,e,p,i); },
		editors: {
			link: null,
			'default':  Marginalia.newEditorFunc( YuiAutocompleteNoteEditor )
		},
		onMarginHeight: function( post ) { moodleMarginalia.fixControlMargin( post ); },
		selectors: selectors
	} );
	
	// Ensure the sheet drop-down reflects the actual sheet to be shown
	// This relies on preferences being saved correctly.  Otherwise, the user may
	// change the dropdown, visit another page, click back and find that the 
	// sheet control shows the wrong thing.
	var sheetCtrl = document.getElementById( 'ansheet' );
	if ( sheetCtrl )
	{
		for ( var i = 0;  i < sheetCtrl.options.length;  ++i )
		{
			if ( sheetCtrl.options[ i ].value == this.sheet )
			{
				sheetCtrl.selectedIndex = i;
				break;
			}
		}
	}
	this.cleanUpPostContent( );
	// The following is actually part of the initialization routine.
	// It enables clicking on the margin to create annotations
	// (clickCreateAnnotation)
	window.marginalia.listPosts( );
	
	// Display annotations
	var url = this.url;
	if ( Marginalia.SHEET_NONE != this.sheet )	
		window.marginalia.showAnnotations( url );
	
	// Fix all control margins
	this.fixAllControlMargins( );
	
	// Highlight the margin when the mouse is over it - but not one of its children.
	// Lets user know s/he can click to create an annotation.
	var margin = jQuery( '.mia_margin' );
	margin.mouseover( function( e ) { margin.toggleClass( 'hover', e.target == margin[0] ); } );
	margin.mouseleave( function( ) { margin.removeClass( 'hover' ); } );
	
	// Enable smartquotes and quote logging
	if ( this.useSmartquote )
	{
		this.smartquote = new Smartquote( this.moodleRoot, this.selectors, this.smartquoteService );
		this.smartquote.enable( marginalia.listPosts( ), marginalia.skipContent );
	}
	
	if ( this.splash && this.sheet != Marginalia.SHEET_NONE )
	{
		var onclose = function() {
			window.marginalia.preferences.setPreference( Marginalia.P_SPLASH, 'false', null);
		};
		window.marginalia.showTip( this.splash, onclose );
	}
};

MoodleMarginalia.prototype.enablePublishQuotes = function( )
{
	this.smartquote = new Smartquote( this.moodleRoot, this.selectors, this.smartquoteService );
	this.smartquote.enable( marginalia.listPosts( ), marginalia.skipContent );
}

MoodleMarginalia.prototype.enableSubscribeQuotes = function( name )
{
	// object_type and object_id are needed by extservice, whic doesn't  right now
	this.quoteSubscriber = new SmartquoteSubscriber( null );
	this.quoteSubscriber.subscribeMCE( name, null, null );
}


MoodleMarginalia.prototype.displayNote = function( marginalia, annotation, noteElement, params, isEditing )
{
	var moodleMarginalia = this;
	var wwwroot = this.moodleRoot;
	buttonParams = this.useSmartquote ?
		{
			className: 'quote',
			title: getLocalized( 'annotation quote button' ),
			content: this.smartquoteIcon,
			onclick: function( ) { 
				moodleMarginalia.smartquote.quoteAnnotation(
					annotation,
					marginalia.loginUserId,
					Smartquote.postIdFromUrl( annotation.getUrl( ) ) );
			}
		}
		: { };

	params.customButtons = [
		{
			owner: true,
			others: true,
			params: buttonParams
		}
	];
	return Marginalia.defaultDisplayNote( marginalia, annotation, noteElement, params, isEditing );
};

MoodleMarginalia.prototype.createAnnotation = function( event, postId )
{
	clickCreateAnnotation( event, postId );
};


/*
 * This fixes the height of create annotation buttons.
 *
 * I used to have to do this for IE, and complained bitterly.  Now I have to do it for Firefox
 * and Safari too:  apparently you can't set a table cell child to be the full height of the cell.
 * Probably this is CSS compliant.  Why can't they make sane standards?
 *
 * Note:  if the annotated content changes length (e.g. because of many inserted links or edit
 * actions), the button won't resize to match.  Hmmm.
 */
MoodleMarginalia.prototype.fixControlMargin = function( post )
{
	var margin = jQuery( 'ol.mia_margin', post.getElement( ) );
	var postContent = jQuery( '.content .posting', post.getElement( ) );
	margin.css( 'min-height', postContent.height( ) );
	postContent.css( 'min-height', margin.height( ) );
/*	margin.height( 'auto' );
	postContent.height( 'auto' );
	if ( margin.height( ) < postContent.height( ) )
		margin.height( postContent.height( ) );
	if ( postContent.height( ) < margin.height( ) )
		postContent.height( margin.height( ) );
		*/
};

MoodleMarginalia.prototype.fixAllControlMargins = function( )
{
	var postInfo = window.marginalia.listPosts( );
	for ( var i = 0;  i < postInfo.posts.length;  ++i )
		this.fixControlMargin( postInfo.posts[ i ] );
}

MoodleMarginalia.prototype.cleanUpPostContent = function( )
{
	var f = function( node ) {
		for ( var child = node.firstChild;  child;  child = child.nextSibling )
		{
			if ( child.nodeType == ELEMENT_NODE )
			{
//				domutil.removeClass( child, PM_POST_CLASS );
//				domutil.removeClass( child, PM_CONTENT_CLASS );
//				domutil.removeClass( child, AN_NOTES_CLASS );
				// #geof# for now, simply clear all class names
				child.removeAttribute( 'class' );
				child.removeAttribute( 'id' );
				if ( child.id )
					delete child.id;
				f( child );
			}
		}
	};
	var posts = marginalia.listPosts( ).getAllPosts( );
	for ( var i = 0;  i < posts.length;  ++i )
	{
		f ( posts[ i ].getContentElement( ) );
	}
}

MoodleMarginalia.prototype.changeSheet = function( sheetControl, url )
{
	var marginalia = window.marginalia;
	var sheet = sheetControl.value;

	// Check to see whether this is a special case with a named handler
	if ( this.handlers[ sheet ] )
		this.handlers[ sheet ]( this, marginalia );
	// This is simply a sheet name: go to that sheet
	else
	{
		marginalia.hideAnnotations( );
		marginalia.preferences.setPreference( Marginalia.P_SHEET, sheet, null );
		if ( Marginalia.SHEET_NONE != sheet )
		{
			marginalia.sheet = sheet;
			marginalia.showAnnotations( url );
			this.fixAllControlMargins( );
		}
	}
};


/*
 * Fetch a localized string
 * This is a function so that it can be replaced with another source of strings if desired
 * (e.g. in a database).  The application uses short English-language strings as keys, so
 * that if the language source is lacking the key can be returned instead.
 */
function getLocalized( s )
{
	return LocalizedAnnotationStrings[ s ];
}

