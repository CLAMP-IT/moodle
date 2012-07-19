/* 
 * marginalia.js
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
 * $Id: marginalia.js 548 2012-06-22 20:59:30Z geof.glass $
 */

/* ************************ User Functions ************************ */

/**
 * Must be called before any other annotation functions
 * service - used to connect to the server side
 * loginUserId - the current user
 * string and anything preceeding it is chopped off.  This is necessary because IE lies about
 * hrefs:  it provides an absolute URL for that attribute, rather than the actual text.  In some
 * cases, absolute URLs aren't desirable (e.g. because the annotated resources might be moved
 * to another host, in which case the URLs would all break).
 */
function Marginalia( service, loginUserId, sheet, features )
{
	this.annotationService = service;
	this.loginUserId = loginUserId;
	this.sheet = sheet;
	this.editing = null;	// annotation currently being edited (if any)
	this.noteEditor = null;	// state for note currently being edited (if any) - should replace editing, above
	this.annotationCache = [ ];	 // array of arrays of annotations that have been fetched from the server
	this.annotationCacheIndex = 0; // entries below this have already been displayed
	
	this.maxNoteLength = 250;
	this.maxQuoteLength = 1000;
	this.maxNoteHoverLength = 24;
	this.userInRequest = false;
	this.preferences = null;
	this.keywordService = null;
	this.urlBase = null;
	this.actions = false;
	this.defaultAction = null;
	this.skipContent = function(node) {
		return _skipAnnotationLinks(node) || _skipAnnotationActions(node) || _skipCaret(node); };
	this.displayNote = Marginalia.defaultDisplayNote;
	this.allowAnyUserPatch = false;
	this.onMarginHeight = null;
	this.serviceErrorCallback = Marginalia.defaultErrorCallback;
	this.enableRecentFlag = false;
	this.lastUpdate = null;
	
	this.selectors = {
		post: new Selector( '.hentry', '.hentry .hentry' ),
		post_content: new Selector( '.entry-content', '.entry-content .entry-content' ),
		post_title: new Selector( '.entry-title', '.entry-content .title', 'text()' ),
		post_author: new Selector( '.author', '.entry-content .author', 'text()' ),
		post_authorid: new Selector( '.author', '.entry-content .author', '@title' ),
		post_date: new Selector( 'abbr.published', '.entry-content abbr.published', '@title' ),
		post_url: new Selector( 'a[rel="bookmark"]', '.entry-content a[rel="bookmark"]', '@href' ),
		mia_notes: new Selector( '.notes', '.entry-content .notes' ),
		mia_markers: new Selector( '.markers', '.entry-content .markers' )
	};
		
	this.editors = {
		'default': Marginalia.newDefaultEditor,
		freeform: Marginalia.newEditorFunc( FreeformNoteEditor )
//		link: Marginalia.newEditorFunc( SimpleLinkUi )
	};

	this.icons = {
		'public': '\u25cb', // SUN_SYMBOL '\u263c'; '\u26aa';
		'private': '\u25c6', // MOON_SYMBOL '\u2641';
		link: '\u263c', //'\u238b'; circle-arrow // \u2318 (point of interest) \u2020 (dagger) \u203b (reference mark) \u238b (circle arrow)
		linkEdit: '\u263c', //'\u238b'; circle-arrow// \u2021 (double dagger)
		collapsed: '+', // '\u25b7'; triangle
		expanded: '-', // '\u25bd';
		'delete': '\u00d7'
	};
	
	for ( var feature in features )
	{
		var value = features[ feature ];
		switch ( feature )
		{
			// The default sheet (e.g. public or private)
			case 'sheetDefault':
				this.defaultSheet = value;
				break;
				
			// Set the default action for a new annotation ("edit" for track changes)
			case 'action':
				this.defaultAction = value;
				break;
			
			// Allow this user to submit patches for out-of-date annotations by any other user
			case 'allowAnyUserPatch':
				this.allowAnyUserUpdate = value;
				break;
			
			// The baseUrl should be stripped from annotation URLs.  The server must also do this.
			case 'baseUrl':
				this.baseUrl = value;
				break;
				
			// Override the function for displaying a note in the margin
			case 'displayNote':
				this.displayNote = value;
				break;
				
			// Override or add editors for editing margin notes
			case 'editors':
				for ( var name in value )
					this.editors[ name ] = value[ name ];
				break;
				
			// Show flag for recent annotations
			case 'enableRecentFlag':
				this.enableRecentFlag = value;
				break;
			
			// Override or add icons
			case 'icons':
				for ( var name in value )
					this.icons[ name ] = value[ name ];
				break;
			
			// The keyword service to provide the drop-down list of keywords
			case 'keywordService':
				this.keywordService = value;
				break;
				
			// The maximum length of a margin note, in characters
			case 'maxNoteLength':
				this.maxNoteLength = value;
				break;
				
			// The maximum length of hover text over a note or link
			case 'maxNoteHoverLength':
				this.maxNoteHoverLength = value;
				break;

			// Maximum length of a quote, in characters
			case 'maxQuoteLength':
				this.maxQuoteLength = value;
				break;
			
			// Toggle: Create an annotation when the user presses the Enter key
			case 'onkeyCreate':
				if ( value )
					addEvent( document, 'keyup', _keyupCreateAnnotation );
				break;
				
			// Callback for when margin height changes
			case 'onMarginHeight':
				this.onMarginHeight = value;
				break;
				
			// A Preferences object used to store/retrieve preferences on the server
			case 'preferences':
				this.preferences = value;
				break;
				
			// Callback for displaying an error when a service call to the server
			// fails
			case 'serviceErrorCallback':
					this.serviceErrorCallback = value;
					break;
					
			// Selectors for finding parts of the document (posts, urls, titles, etc.)
			// Default selectors can be overriden individually
			case 'selectors':
				for ( var selector in value )
					this.selectors[ selector ] = value[ selector ];
				break;
			
			case 'showActions':
				this.showActions = value;
				break;
				
			// Show a caret where the user clicks the mouse.  Do not use.
			case 'showCaret':
				if ( value )
				{
					document.addEventListener( 'mouseup', _caretUpHandler, true );
					document.addEventListener( 'mousedown', _caretDownHandler, false );
				}
				break;
				
			// Show block markers in the left margin, indicating how many users have annotated a block
//			case 'showBlockMarkers':
//				this.showBlockMarkers = value;
//				break;
				
			// Function for ignoring elements embedded in annotatable content
			case 'skipContent':
				var oldSkipContent = this.skipContent;
				var customSkipContentFunc = value;
				this.skipContent = function(node) { return oldSkipContent(node) || customSkipContentFunc(node); };
				break;
				
			// Send the user ID in requests (for the demo - shouldn't use in actual systems)
			case 'userInRequest':
				this.userInRequest = value;
				break;
				
			// Toggle: Are you sure you want to delete this note? alert box
			case 'warnDelete':
				this.warnDelete = value;
				break;
			
			default:
				if ( typeof( this[ feature ] ) != 'undefined' )
					throw 'Attempt to override feature: ' + feature;
				else
					this[ feature ] = value;
				break;
		}
	}
}

// Recorded in every annotation created by this client
Marginalia.VERSION = 2;

// Fields (i.e. properties added to DOM nodes)
Marginalia.F_ANNOTATION = 'mia_annotation';	// reference to Annotation object
Marginalia.F_POST = 'mia_post';				// reference to PostMicro object

// The timeout between coop multitasking calls.  Should be short so most time is spent doing
// something rather than timing out.
Marginalia.COOP_TIMEOUT = 50;

// The maximum time to spend on one coop multitasking call.  Should be short enough to be
// fairly unnoticeable, but long enough to get some work done.
Marginalia.COOP_MAXTIME = 200;

// Prefix for all Marginalia class and ID values
Marginalia.PREFIX = 'mia_';

// Class and ID prefixes
Marginalia.ID_PREFIX = Marginalia.PREFIX + 'id_';	// prefix for annotation IDs

// The names of HTML/CSS classes used by the annotation code.
Marginalia.C_HOVER = Marginalia.PREFIX + 'hover';				// assigned to highlights and notes when the mouse is over the other
Marginalia.C_ANNOTATED = Marginalia.PREFIX + 'annotated';		// class added to fragment when annotation is on
Marginalia.C_SELFANNOTATED = Marginalia.PREFIX + 'self-annotated';  // annotations are by the current user (and therefore editable)
Marginalia.C_EDITINGNOTE = Marginalia.PREFIX + 'editing-note';	// (on body) indicates a note is being edited
Marginalia.C_EDITINGLINK = Marginalia.PREFIX + 'editing-link';
Marginalia.C_LASTHIGHLIGHT = Marginalia.PREFIX + 'last';		// used to flag the last highlighted regin for a single annotation
Marginalia.C_ACTIONPREFIX = Marginalia.PREFIX + 'action-';		// prefix for class names for actions (e.g. action-delete)
Marginalia.C_ERRORBOX = Marginalia.PREFIX + 'errorbox';	// used for displaying pop-up errors
Marginalia.ID_RANGECARET = Marginalia.PREFIX + 'range-caret';	// identifies caret used to show zero-length ranges

// Preferences
Marginalia.P_SHEET = 'annotations.sheet';
Marginalia.P_NOTEEDITMODE = 'annotations.note-edit-mode';
Marginalia.P_SPLASH = 'annotations.splash';

// Default values for annotation.sheet (other sheets are possible)
Marginalia.SHEET_NONE = 'none';
Marginalia.SHEET_PUBLIC = 'public';
Marginalia.SHEET_PRIVATE = 'private';

// values for annotation.editing (field is deleted when not editing)
Marginalia.EDIT_NOTE_FREEFORM = 'note freeform';
Marginalia.EDIT_NOTE_KEYWORDS = 'note keywords';
Marginalia.EDIT_LINK = 'link';


Marginalia.prototype.newEditor = function( annotation, editorName )
{
	var f = editorName ? this.editors[ editorName ] : this.editors[ 'default' ];
	return f( this, annotation );
}

/**
 * Call this to create a function for constructing an editor with the given constructor
 * Avoids excessive context in the lambda
 */
Marginalia.newEditorFunc = function( constructor, params )
{
	var f = function( marginalia, annotation ) { return new constructor( params ); };
	return f;
}

/**
 * Figure out whether note editing should be in keywords or freeform mode
 * If the note text is a keyword, default to keywords.  Otherwise, check
 * preferences.
 */
Marginalia.newDefaultEditor = function( marginalia, annotation )
{
	if ( ! marginalia.keywordService )
		return new FreeformNoteEditor( );
	else if ( ! annotation || '' == annotation.getNote() )
	{
		var pref = marginalia.preferences.getPreference( Marginalia.P_NOTEEDITMODE );
		if ( pref == Marginalia.EDIT_NOTE_KEYWORDS )
			return new KeywordNoteEditor( );
		else
			return new FreeformNoteEditor( );
	}
	else if ( marginalia.keywordService.isKeyword( annotation.getNote() ) )
		return new KeywordNoteEditor( );
	else
		return new FreeformNoteEditor( );
}

Marginalia.defaultErrorCallback = function( object, operation, status, text )
{
	var msgKey = object + '.' + operation;
	var node = domutil.element( 'div', {
		className: Marginalia.C_ERRORBOX }, [
		domutil.element( 'h3', { },
			getLocalized( 'service error title ' + msgKey ) ),
		domutil.element( 'p', { },
			getLocalized( 'service error ' + msgKey ) + ' ' ),
		domutil.element( 'p', { }, msgKey + ' ' +
			getLocalized( 'service error ' + status ) ),
		domutil.element( 'p', { }, text ) ] );
	document.body.appendChild( node );
	setTimeout( function( ) {
		jQuery( node ).fadeOut( 'def ', function( ) {
		document.body.removeChild( node ); } ) },
		7000 );
}
	

/**
 * Could do this in the initializer, but by leaving it until now we can avoid
 * forcing clients to have an onload handler
 */
Marginalia.prototype.listPosts = function( )
{
	if ( ! this.posts )
	{
		this.posts = PostPageInfo.getPostPageInfo( document, this.selectors );
		for ( var i = 0;  i < this.posts.posts.length;  ++i )
			this.posts.posts[ i ].initMargin( this );
	}
	return this.posts;
}

Marginalia.prototype.createAnnotation = function( annotation, ok, fail )
{
	var ok2 = null;
	if ( this.keywordService )
	{
		var keywordService = this.keywordService;
		ok2 = function( url ) {
			ok( url );
			keywordService.refresh( );
		};
	}

	this.annotationService.createAnnotation( annotation, ok2 ? ok2 : ok, fail );
}

Marginalia.prototype.updateAnnotation = function( annotation )
{
	if ( annotation.hasChanged() )
	{
		var marginalia = this;
		var ok = function( xml )	{
			annotation.resetChanges();
			if ( marginalia.keywordService )
				marginalia.keywordService.refresh( );
		};
		fail = function( status, text ) {
			if ( marginalia.serviceErrorCallback )
				marginalia.serviceErrorCallback( 'annotation', 'update', status, text );
		};
		this.annotationService.updateAnnotation( annotation, ok, fail );
	}
}

Marginalia.prototype.deleteAnnotation = function( annotation, ok )
{
	var ok = null;
	if ( this.keywordService )
	{
		var keywordService = this.keywordService;
		ok = function( xml ) {
			keywordService.refresh( );
		};
	}
	fail = function( status, text ) {
		if ( marginalia.serviceErrorCallback )
			marginalia.serviceErrorCallback( 'annotation', 'delete', status, text );
	};
	this.annotationService.deleteAnnotation( annotation, ok, fail );
}


/**
 * Show all annotations on the page
 * Make sure to call showMarginalia too
 * There used to be showAnnotations and hideAnnotations functions which could
 * apply to individual posts on a page.  Unused, I removed them - they added
 * complexity because annotations needed to be stored but not displayed.  IMHO,
 * the best way to do this is with simple dynamic CSS (using display:none).
 *
 * params:
 * - recent:    fetch only recent annotations
 * - callback:  call this function before showing retrieved annotations, if it
 *              returns true go ahead and show them.  Useful for polling
 *              for updates.
 */
Marginalia.prototype.showAnnotations = function( url, params )
{
	// Must set the class here so that annotations margins will expand so that,
	// in turn, any calculations done by the caller (e.g. to resize margin
	// buttons) will take the correct size into account.
	domutil.addClass( document.body, Marginalia.C_ANNOTATED );
	var marginalia = this;
	this.annotationService.listAnnotations( url, this.sheet, {
		mark: 'read',
		since: marginalia.lastUpdate,
		recent: params && params.recent },
		function( xmldoc ) {
			var annotations = parseAnnotationXml( xmldoc );
			if ( annotations.length && ( ! params || ! params.callback || params.callback( annotations )  ) )
				marginalia.loadAnnotations( annotations );
		} );
}
	

/**
 * Process annotations returned from the server.
 * The new annotations are appended to the annotationCache
 * (so that we don't have two update interval going on at once)
 * and an interval is created (if necessary) to actually
 * show them.  Showing can be slow, so we're doing coop
 * multitasking to give control back to the browser.
 */
Marginalia.prototype.loadAnnotations = function( annotations )
{
	domutil.addClass( document.body, Marginalia.C_ANNOTATED );
	this.annotationCache = this.annotationCache.concat( annotations );
	// Danger: coopLoadAnnotations says it needs annotations
	// sorted by url.  So should sort the new array. #geof#
	if ( ! this.loadInterval )
	{
		var marginalia = this;
		this.loadInterval = setInterval( function( ) {
			marginalia.coopLoadAnnotations( );
		}, Marginalia.COOP_TIMEOUT );
	}
}

/**
 * This callback is used to display annotations from the cache in the marginalia object.
 * It will spend a certain amount of time displaying annotations;  if it can't show them
 * all in that time, it will call setTimeout to trigger continued display later.  This
 * is basically a way to implement cooperative multitasking so that if many annotations
 * need to be displayed the browser won't lock up.
 */
Marginalia.prototype.coopLoadAnnotations = function( )
{
	var startTime = new Date( );
	
	// Display cached annotations
	// Do this by merging the new annotations with those already displayed
	// For this to work, annotations must be sorted by URL
	var url = null;			// there may be annotations for multiple URLs;  this is the current one
	var post = null;		// post for the current url
	var notes = null;		// current notes element
	var nextNode = null;
	
	var mostRecent = null;
	while ( this.annotationCacheIndex < this.annotationCache.length )
	{
		var annotation = this.annotationCache[ this.annotationCacheIndex ];
		this.annotationCacheIndex += 1;
		
		// Don't want to fail completely just because an annotation is malformed
		if ( annotation )
		{
			// Determine whether we're moving on to a new post (hence a new note list)
			if ( annotation.getUrl( ) != url )
			{
				// Margin height callback
				if ( post && marginalia.onMarginHeight )
					marginalia.onMarginHeight( post );
				
				url = annotation.getUrl( );
				post = marginalia.listPosts( ).getPostByUrl( url, marginalia.baseUrl );
				
				// Find the first note in the list (if there is one)
				if ( post )
				{
					notes = post.getNotesElement( marginalia );
					nextNode = notes.firstCild;
				}
				else
					logError( 'Post not found for URL "' + url + "'" );
			}
			
			// The server shouldn't normally return URLs that not on this page, but it
			// could (e.g. if the target has been deleted).  In that case, don't crash!
			if ( post )
			{
				// Find the position of the annotation by walking through the note list
				// (binary search would be nice here, but not practical unless the list is
				// stored somewhere other than in the DOM - plus, since multiple annotations
				// are dealt with here at once, the speed hit shouldn't be too bad)
				while ( nextNode )
				{
					if ( ELEMENT_NODE == nextNode.nodeType && nextNode[ Marginalia.F_ANNOTATION ] )
					{
						if ( annotation.compareRange( nextNode[ Marginalia.F_ANNOTATION ] ) < 0 )
							break;
					}
					nextNode = nextNode.nextSibling;
				}
				
				// Now insert before beforeNote
				var success = post.addAnnotation( marginalia, annotation, nextNode );
				
				// If the annotation was added it may need to be patched (to update the XPathRange
				// or SequenceRange to the current format)
				if ( success )
				{
					if ( annotation.getUserId( ) == marginalia.loginUserId || marginalia.allowAnyUserPatch )
						marginalia.patchAnnotation( annotation, post );
				}
				// If the highlight could not be located, try to fix the annotation
				else
				{
					if ( annotation.getUserId( ) == marginalia.loginUserId || marginalia.allowAnyUserPatch )
						marginalia.fixAnnotation( annotation, post );
				}
				
				// Use the incoming annotations to figure out how recent our last update was.
				// This info needs to come from the server so we have a single point of truth
				// about time, and doing this avoids having the server explicitly send a
				// last update value.
				if ( ! this.lastUpdate || annotation.getUpdated( ) > this.lastUpdate )
					this.lastUpdate = annotation.getUpdated( );
			}
		}
		
		if ( Date( ) - startTime >= Marginalia.COOP_MAXTIME )
			break;
	}
	
	if ( this.annotationCache.length == this.annotationCacheIndex )
	{
		this.annotationCache = [ ];
		this.annotationCacheIndex = 0;
		clearInterval( this.loadInterval );
		this.loadInterval = null;
		
		// Now that annotation display is complete, check whether there is
		// a fragment identifier in the URL telling us to scroll to a
		// particular annotation.
		var url = '' + window.location;
		var match = url.match( /#annotation@(\w+)$/ );
		if ( match )
		{
			var id = Marginalia.ID_PREFIX + match[ 1 ];
			var node = document.getElementById( id );
			if ( node )
				domutil.scrollWindowToNode( node, domutil.SCROLL_POS_CENTER );
		}
	}
}


/**
 * Older annotations may be using old path formats or other saved data which is
 * incorrect or inconsistent with current formatting.  This function checks
 * for the possibility, makes the changes, and submits the any updates to the
 * server.  This only applies to annotations owned by the current user.
 *
 * Ranges can be out of date for the following reasons:
 * - because there is no XPath range stored (older version lacked this)
 * - because the sequence range is in word.char format (e.g. 215.0 215.3)
 * - because the sequence range lacks a second block (e.g. /3/4/15.0 15.3)
 * - because the sequence range lacks a line number (e.g. /3/4/15.0;/3/4/15.3)
 * Current sequence range format looks like 3.4/1.15.0;3.4/1.15.3
 */
Marginalia.prototype.patchAnnotation = function( annotation, post )
{
	var root = post.getContentElement( );
	var sequenceRange = annotation.getSequenceRange( );
	var xpathRange = annotation.getXPathRange( );
	
	// Don't even try fixing xpath ranges if they can't be resolved on this browser
	if ( sequenceRange.needsUpdate( ) || (
			XPathRange.canResolve( root ) && ( null == xpathRange || xpathRange.needsUpdate( ) ) ) )
	{
		// Determine the physical word range in the document
		var wordRange = post.wordRangeFromAnnotation( this, annotation );
		var textRange = TextRange.fromWordRange( wordRange, this.skipContent );
		wordRange = WordRange.fromTextRange( textRange, root, this.skipContent );
		
		// Update the annotation
		var quote;
		if ( sequenceRange.needsUpdate( ) )
		{
			var newSequenceRange = wordRange.toSequenceRange( root );
			// Verify that the new sequence range is correct
			// Don't want to break existing data because of bad resolution or a code bug
			wordRange = WordRange.fromSequenceRange( newSequenceRange, root, this.skipContent );
			textRange = TextRange.fromWordRange( wordRange, this.skipContent );
			quote = getTextRangeContent( textRange, this.skipContent );
			quote = quote.replace( /(\s|\u00a0)+/g, ' ' );
			if ( annotation.getQuote( ) == quote )
				annotation.setSequenceRange( newSequenceRange );
		}
		if ( XPathRange.canResolve( root ) && ( null == xpathRange || xpathRange.needsUpdate( ) ) )
		{
			var newXPathRange = wordRange.toXPathRange( root );
			// Verify that the new xpath range is correct
			// Don't want to break existing data because of bad resolution or a code bug
			wordRange = WordRange.fromXPathRange( newSequenceRange, root, this.skipContent );
			textRange = TextRange.fromWordRange( wordRange, this.skipContent );
			quote = getTextRangeContent( textRange, this.skipContent );
			quote = quote.replace( /(\s|\u00a0)+/g, ' ' );
			if ( annotation.getQuote( ) == quote )
				annotation.setXPathRange( newXPathRange );
		}
		marginalia.updateAnnotation( annotation, null );

		// Replace the editable note display
		post.removeNote( this, annotation );
		var nextNode = post.getAnnotationNextNote( this, annotation );
		noteElement = post.showNote( this, annotation, nextNode );
		post.repositionNotes( this, noteElement.nextSibling );
	
		// Reposition block markers
//		post.repositionBlockMarkers( this );
	}
}

/**
 * Fix a broken annotation range by searching for the quote text
 */
Marginalia.prototype.fixAnnotation = function( annotation, post )
{
	while( window.find( annotation.getQuote( ) ) )
	{
		// the find function places a text range over the found text
		var textRange = marginalia.getSelection( );
		var contentElement = post.getContentElement( );
		// if the found text is within the post then we're good to go
		if ( ( domutil.isElementDescendant( textRange.startContainer, contentElement )
			|| textRange.startContainer == contentElement )
			&& ( domutil.isElementDescendant( textRange.endContainer, contentElement )
			|| textRange.endContainer == contentElement ) )
		{
			// Calculate the ranges
			var wordRange = WordRange.fromTextRange( textRange, contentElement, marginalia.skipContent );
			var sequenceRange = wordRange.toSequenceRange( contentElement );
			var xpathRange = wordRange.toXPathRange( contentElement );
			annotation.setSequenceRange( sequenceRange );
			annotation.setXPathRange( xpathRange );
			
			// Update the annotation
			marginalia.updateAnnotation( annotation, null );
	
			// Show the highlight
			post.showHighlight( marginalia, annotation );

			// Replace the editable note display
			post.removeNote( this, annotation );
			var nextNode = post.getAnnotationNextNote( this, annotation );
			noteElement = post.showNote( this, annotation, nextNode );
			post.repositionNotes( this, noteElement.nextSibling );
		
			// Reposition block markers
//			post.repositionBlockMarkers( this );
			return true;
		}
	}
	return false;
}

/**
 * Hide all annotations on the page
 */
Marginalia.prototype.hideAnnotations = function( )
{
	domutil.removeClass( document.body, Marginalia.C_ANNOTATED );
	domutil.removeClass( document.body, Marginalia.C_SELFANNOTATED );
	
	var posts = this.listPosts( ).getAllPosts( );
	for ( var i = 0;  i < posts.length;  ++i )
	{
		var post = posts[ i ];
		// Should also destruct each annotation
		var annotations = post.removeAnnotations( marginalia );
		for ( var j = 0;  j < annotations.length;  ++j )
			annotations[ j ].destruct( );
		// normalizeSpace( post.element );
	}
}


/**
 * Display a tip to the user in the margin
 * current implementation only shows the tip in the top post
 */
Marginalia.prototype.showTip = function( tip, onclose )
{
	var posts = this.listPosts( ).getAllPosts( );
	if ( posts.length >= 1 )
		return posts[ 0 ].showTip( this, tip, onclose );
	else
		return null;
}

Marginalia.prototype.hideTip = function ( tipNode )
{
	var post = marginalia.posts.getPostByElement( tipNode );
	if ( post )
		post.hideTip( this, tipNode );
}


/* *****************************
 * Additions to Annotation class
 */
 
/**
 * Convenience method for getting the note element for a given annotation
 */
Annotation.prototype.getNoteElement = function( marginalia )
{
	return document.getElementById( Marginalia.ID_PREFIX + this.getId() );
}


/* ************************ Add/Show Functions ************************ */
/* These are for adding an annotation to the post and display.
 * addAnnotation calls the other three in order:
 * showNote, highlightRange, positionNote
 * None of these do anything with the server, but they do create interface
 * elements which when activated call server functions.
 *
 * In order to achieve a single point of truth, the only annotation list
 * is the list of annotation notes attached to each post in the DOM.
 * On the one hand, the two can't vary independently.  But I can't think
 * why they would need to.  This way, they can't get out of sync.
 */

/**
 * Add an annotation to the local annotation list and display, or
 * replace an existing one that matches.
 * Returns true if the annotation highlight was located successfully
 */
PostMicro.prototype.addAnnotation = function( marginalia, annotation, nextNode, editor )
{
	if ( ! nextNode )
		nextNode = this.getAnnotationNextNote( marginalia, annotation );
	// If the annotation is already displayed, remove the existing display
	var existing = annotation.getNoteElement( marginalia );
	if ( existing )
		this.removeAnnotation( marginalia, annotation );
	var quoteFound = this.showHighlight( marginalia, annotation );
	// Go ahead and show the note even if the quote wasn't found
	var r;
	if ( editor )
		r = this.showNoteEditor( marginalia, annotation, editor, nextNode );
	else
		r = this.showNote( marginalia, annotation, nextNode );
	// Reposition any following notes that need it
	this.repositionSubsequentNotes( marginalia, nextNode );
	return quoteFound;
}

/**
 * Get all annotations on a post by looking at HTML (no check with server)
 */
PostMicro.prototype.listAnnotations = function( marginalia )
{
	var notesElement = this.getNotesElement( marginalia );
	var child = notesElement.firstChild;
	var annotations = new Array( );
	while ( null != child )
	{
		if ( child[ Marginalia.F_ANNOTATION ] )
			annotations[ annotations.length ] = child[ Marginalia.F_ANNOTATION ];
		child = child.nextSibling;
	}
	return annotations;
}

/* ************************ Remove/Hide Functions ************************ */
/* These are counterparts to the add/show functions above */

/*
 * Remove all annotations from a post
 * Returns an array of removed annotations so the caller can destruct them if necessary
 */
PostMicro.prototype.removeAnnotations = function( marginalia )
{
	var notesElement = this.getNotesElement( marginalia );
	var child = notesElement.firstChild;
	var annotations = new Array( );
	while ( null != child )
	{
		if ( child[ Marginalia.F_ANNOTATION ] )
		{
			annotations[ annotations.length ] = child[ Marginalia.F_ANNOTATION ];
			child[ Marginalia.F_ANNOTATION ] = null;
		}
		notesElement.removeChild( child );
		child = notesElement.firstChild;
	}
	var micro = this;
	var stripTest = function( tnode )
		{ return micro.highlightStripTest( tnode, null ); };
	domutil.stripMarkup( this.getContentElement( ), stripTest, true );
	domutil.removeClass( this.getElement( ), Marginalia.C_ANNOTATED );
	return annotations;
}

/**
 * Remove an individual annotation from a post
 */
PostMicro.prototype.removeAnnotation = function( marginalia, annotation )
{
	var next = this.removeNote( marginalia, annotation );
	this.removeHighlight( marginalia, annotation );

	// Reposition markers if necessary
//	if ( 'edit' == annotation.action )
//		this.repositionBlockMarkers( marginalia );
	
	return null == next ? null : next[ Marginalia.F_ANNOTATION ];
}

/* ************************ Display Actions ************************ */
/* These are called by event handlers.  Unlike the handlers, they are
 * not specific to controls or events (they should make no assumptions
 * about the event that triggered them). */

/**
 * Indicate an annotation is under the mouse cursor by lighting up the note and the highlight
 * If flag is false, this will remove the lit-up indication instead.
 * Works even if the highlight region is missing.
 */
PostMicro.prototype.flagAnnotation = function( marginalia, annotation, className, flag )
{
	// Activate the note
	var noteNode = document.getElementById( Marginalia.ID_PREFIX + annotation.getId() );
	if ( flag )
		domutil.addClass( noteNode, className );
	else
		domutil.removeClass( noteNode, className );

	// Activate the highlighted areas
	var highlights = domutil.childrenByTagClass( this.getContentElement( ), null, Marginalia.C_HIGHLIGHT, null, null );
	for ( var i = 0;  i < highlights.length;  ++i )
	{
		var node = highlights[ i ];
		// Need to change to upper case in case this is HTML rather than XHTML
		if ( node.tagName.toUpperCase( ) == 'EM' && node[ Marginalia.F_ANNOTATION ] == annotation )
		{
			if ( flag )
				domutil.addClass( node, className );
			else
				domutil.removeClass( node, className );
		}
	}
}


/**
 * Called to start editing a new annotation
 * the annotation isn't saved to the db until edit completes
 */
PostMicro.prototype.createAnnotation = function( marginalia, annotation, editor )
{
	// Ensure the window doesn't scroll by saving and restoring scroll position
	var scrollY = domutil.getWindowYScroll( );
	var scrollX = domutil.getWindowXScroll( );

	annotation.isLocal = true;
	
	// Show the annotation and highlight
	var nextNode = this.getAnnotationNextNote( marginalia, annotation );
	this.addAnnotation( marginalia, annotation, nextNode, editor );
	
	// HACK: Some editors may perform an action, then revert to the regular annotation
	// display by calling _saveAnnotation or _cancelAnnotationEdit.  If this is the
	// case, edit mode will be cancelled and there's no more to do display-wise.
	// I say this is a hack, because there should be a better way to indicate that
	// outcome (or to trigger it - a show() function that doesn't actually show is
	// very confusing).
	if ( marginalia.noteEditor )
	{
		// Focus on the text edit
		var noteElement = document.getElementById( Marginalia.ID_PREFIX + annotation.getId() );
		// Sequencing here (with focus last) is important
		this.repositionNotes( marginalia, noteElement.nextSibling );
		marginalia.noteEditor.focus( );
		
		window.scrollTo( scrollX, scrollY );
		
		if ( marginalia.onMarginHeight )
			marginalia.onMarginHeight( this );
	}
}


/**
 * Cancel an annotation edit in progress
 * This restores the annotation's previous values or removes one that wasn't
 * yet created on the server.
 */
PostMicro.prototype.cancelAnnotationEdit = function( marginalia, annotation )
{
	if ( ! marginalia.noteEditor )
		return false;
	
	this.removeAnnotation( marginalia, annotation );
	if ( ! annotation.isLocal )
	{
		copy( marginalia.noteEditor.annotationOrig, annotation );
		this.addAnnotation( marginalia, annotation );
	}
	
	this.flagAnnotation( marginalia, annotation, Marginalia.C_EDITINGNOTE, false );
	domutil.removeClass( document.body, Marginalia.C_EDITINGNOTE );
}


/**
 * Save an annotation after editing
 */
PostMicro.prototype.saveAnnotation = function( marginalia, annotation )
{
	// Don't allow this to happen more than once
	if ( ! marginalia.noteEditor )
		return false;
	
	// Save any changes to the annotation
	if ( marginalia.noteEditor.save )
		marginalia.noteEditor.save( );
	
	// ---- Validate the annotation ----

	// Check the length of the note.  If it's too long, do nothing, but restore focus to the note
	// (which is awkward, but we can't save a note that's too long, we can't allow the note
	// to appear saved, and truncating it automatically strikes me as an even worse solution.) 
	if ( marginalia.noteEditor.annotation.getNote().length > marginalia.maxNoteLength )
	{
		alert( getLocalized( 'note too long' ) );
		marginalia.noteEditor.focus( );
		return false;
	}
	
	// Similarly for the length of a link
	if ( marginalia.noteEditor.annotation.getLink().length > Marginalia.MAX_LINK_LENGTH )
	{
		alert( getLocalized( 'link too long' ) );
		marginalia.noteEditor.focus( );
		return false;
	}
	
	// Note and quote length cannot both be zero
	var sequenceRange = annotation.getSequenceRange( );
	if ( sequenceRange.start.compare( sequenceRange.end ) == 0 && annotation.getNote( ).length == 0 )
	{
		alert( getLocalized( 'blank quote and note' ) );
		marginalia.noteEditor.focus( );
		return false;
	}
	
	// Ensure the window doesn't scroll by saving and restoring scroll position
	var scrollY = domutil.getWindowYScroll( );
	var scrollX = domutil.getWindowXScroll( );
	
	// Remove events and editor display
	this.stopEditing( marginalia, annotation );

	// TODO: listItem is an alias for noteElement
	var listItem = document.getElementById( Marginalia.ID_PREFIX + annotation.getId() );
	
	// For annotations with links; insert, or substitute actions, must update highlight also
	if ( 'edit' == annotation.action && annotation.hasChanged( 'note' ) || annotation.hasChanged( 'link' ) )
	{
		this.removeHighlight( marginalia, annotation );
		this.showHighlight( marginalia, annotation );
	}
	
	// ---- Update the annotation contents ----
	
	// The annotation is local and needs to be created in the DB
	if ( annotation.isLocal )
	{
		var postMicro = this;
		// Callback for successful creation
		var ok = function( url ) {
			// update the annotation with the created ID
			annotation.setId( Annotation.idFromUrl( url ) );
			annotation.resetChanges( );
			annotation.isLocal = false;
			var noteElement = document.getElementById( Marginalia.ID_PREFIX + '0' );
			noteElement.id = Marginalia.ID_PREFIX + annotation.getId();
			var highlightElements = domutil.childrenByTagClass( postMicro.getContentElement( ), 'em', Marginalia.ID_PREFIX + '0', null, null );
			for ( var i = 0;  i < highlightElements.length;  ++i )
			{
				domutil.removeClass( highlightElements[ i ], Marginalia.ID_PREFIX + '0' );
				domutil.addClass( highlightElements[ i ], Marginalia.ID_PREFIX + annotation.getId() );
			}
		};
		
		var fail = function( status, text ) {
			if ( marginalia.serviceErrorCallback )
				marginalia.serviceErrorCallback( 'annotation', 'create', status, text );
			postMicro.deleteAnnotation( marginalia, annotation, false );
		};
		
		annotation.setUrl( this.getUrl( ) );
		// IE may have made a relative URL absolute, which could cause problems
		if ( null != marginalia.baseUrl && annotation.url
			&& annotation.url.substring( 0, marginalia.baseUrl.length ) == marginalia.baseUrl )
		{
			annotation.setUrl( annotation.getUrl().substring( marginalia.baseUrl.length ) );
		}

		annotation.setQuoteTitle( this.getTitle( ) );
		annotation.setQuoteAuthorId( this.getAuthorId( ) );
		annotation.setQuoteAuthorName( this.getAuthorName( ) );
		// ^ author name is usually ignored, as the server will know from the ID
		//   but conceivably there might be systems where this is not so
		marginalia.createAnnotation( annotation, ok, fail );
	}
	// The annotation already exists and needs to be updated
	else
		marginalia.updateAnnotation( annotation, null );
	
	// ---- Redraw the note ----
	// Update the link hover (if present)
	this.showLink( marginalia, annotation );
	
	// Replace the editable note display
	var nextNode = this.removeNote( marginalia, annotation );
	noteElement = this.showNote( marginalia, annotation, nextNode );
	this.repositionNotes( marginalia, noteElement.nextSibling );

	// May need to reposition block markers
//	if ( annotation.action == 'edit' && annotation.hasChanged( 'note' ) || annotation.hasChanged( 'link' ) )
//		this.repositionBlockMarkers( marginalia );
	
	window.scrollTo( scrollX, scrollY );
	
	// Margin height callback
	if ( marginalia.onMarginHeight )
		marginalia.onMarginHeight( this );
	
	return true;
}


/**
 * Stop edit mode
 * This removes the editor display, but doesn't replace it with anything
 */
PostMicro.prototype.stopEditing = function( marginalia, annotation )
{
	// Remove events
	removeEvent( document.documentElement, 'click', _saveAnnotation );
	var noteElement = document.getElementById( Marginalia.ID_PREFIX + annotation.getId() );
	removeEvent( noteElement, 'click', domutil.stopPropagation );
	
	// Clear the editor
	marginalia.noteEditor.clear();
	marginalia.noteEditor = null;
	while ( noteElement.firstChild )
		noteElement.removeChild( noteElement.firstChild );

	this.flagAnnotation( marginalia, annotation, Marginalia.C_EDITINGNOTE, false );
	domutil.removeClass( document.body, Marginalia.C_EDITINGNOTE );
}


/**
 * Delete an annotation
 */
PostMicro.prototype.deleteAnnotation = function( marginalia, annotation, warnDelete )
{
	// Pop up a warning (if configured)
	if ( warnDelete )
	{
		if ( ! confirm( getLocalized( 'warn delete' ) ) )
			return;
	}
	
	// Ensure the window doesn't scroll by saving and restoring scroll position
	var scrollY = domutil.getWindowYScroll( );
	var scrollX = domutil.getWindowXScroll( );

	// Check whether this annotation is being edited - if so, cancel the edit
	if ( marginalia.noteEditor && annotation == marginalia.noteEditor.annotation )
		this.stopEditing( marginalia, annotation );
	
	// Delete it on the server
	if ( ! annotation.isLocal )
		marginalia.deleteAnnotation( annotation, null );
	
	// Find the annotation
	var next = this.removeAnnotation( marginalia, annotation );
	if ( null != next )
	{
		var nextElement = document.getElementById( Marginalia.ID_PREFIX + next.id );
		this.repositionNotes( marginalia, nextElement );
	}
	annotation.destruct( );
	
	window.scrollTo( scrollX, scrollY );
	
	// Margin height callback
	if ( marginalia.onMarginHeight )
		marginalia.onMarginHeight( this );
}


/* ************************ Event Handlers ************************ */
/* Each of these should capture an event, obtain the necessary information
 * to execute it, and dispatch it to something else to do the work */

/**
 * Mouse hovers over an annotation note or highlight
 */
function _hoverAnnotation( event )
{
	var post = domutil.nestedFieldValue( this, Marginalia.F_POST );
	var annotation = domutil.nestedFieldValue( this, Marginalia.F_ANNOTATION );
	post.flagAnnotation( marginalia, annotation, Marginalia.C_HOVER, true );
}

/**
 * Mouse hovers off an annotation note or highlight
 */
function _unhoverAnnotation( event )
{
	// IE doesn't have a source node for the event, so use this
	var post = domutil.nestedFieldValue( this, Marginalia.F_POST );
	var annotation = domutil.nestedFieldValue( this, Marginalia.F_ANNOTATION );
	post.flagAnnotation( marginalia, annotation, Marginalia.C_HOVER, false );
}

/**
 * Hit any key in document
 */
function _keyupCreateAnnotation( event )
{
	var marginalia = window.marginalia;
	if ( null != marginalia.loginUserId )
	{
		// Enter to create a regular note
		if ( 13 == event.keyCode )
		{
			if ( createAnnotation( null, false, marginalia.newEditor() ) )
				event.stopPropagation( );
		}
	}
}

/**
 * When the user creates a zero-length text range, show the position with a marker
 */
function _caretUpHandler( event )
{
	// Verify W3C range support
	if ( ! window.getSelection )
		return;
	
	// Strip any existing caret (in case it wasn't already caught)
	_caretDownHandler( );
	
	var selection = window.getSelection();
	if ( selection.rangeCount == null )
		return;
	if ( selection.rangeCount == 0 )
		return;
	var textRange = selection.getRangeAt( 0 );

	if ( null != textRange )
	{
		// Save selection position
		var container = textRange.startContainer;
		var offset = textRange.startOffset;
		var marginalia = window.marginalia;

		
		var post = marginalia.posts.getPostByElement( container );
		var contentElement = post.getContentElement( );
		if ( post && ( container == contentElement || domutil.isElementDescendent( container, contentElement ) ) )
		{
			// Only show the caret if it's a point (i.e. the range has zero length)
			if ( container == textRange.endContainer && offset == textRange.endOffset )
			{
				// Insert the caret
				var caret = document.createElement( 'span' );
				caret.appendChild( document.createTextNode( '>' ) );
				caret.setAttribute( 'id', Marginalia.ID_RANGECARET );
				textRange.insertNode( caret );
/*
				
				if ( ELEMENT_NODE == container.nodeType )
				{
					return false;
				}
				else if ( TEXT_NODE == container.nodeType )
				{
					var textBefore = container.nodeValue.substring( 0, offset );
					var textAfter = container.nodeValue.substring( offset );
					
					container.nodeValue = textBefore;
					container.parentNode.insertBefore( caret, container.nextSibling );
					var afterNode = document.createTextNode( textAfter );
					container.parentNode.insertBefore( afterNode, caret.nextSibling );
					
					if ( selection.removeAllRanges && selection.addRange )
					{
						selection.removeAllRanges( );
						textRange.setStart( afterNode, 0 );
						textRange.setEnd( afterNode, 0 );
						selection.addRange( textRange );
					}
					else
					{
						textRange.setStart( container, offset );
						textRange.setEnd( container, offset );
					}
				}
				*/
			}
		}
	}
}

// Remove any carets when the mouse button goes down
function _caretDownHandler( event )
{
	// Verify W3C range support
	if ( ! window.getSelection )
		return;
	
	var caret = document.getElementById( Marginalia.ID_RANGECARET );
	if ( caret )
	{
		var selection = window.getSelection();
		
		var remove = false;
		if ( selection.rangeCount == null )
			remove = true;
		else if ( selection.rangeCount == 0 )
			remove = true;
		else
		{
			var textRange = selection.getRangeAt( 0 );
			if ( textRange.prevSibling != caret )
			{
				var before = caret.prevSibling;
				caret.parentNode.removeChild( caret );
				domutil.normalizeNodePair( before );
			}
		}
	}
}

function _skipCaret( node )
{
	return node.id == Marginalia.ID_RANGECARET;
}

/**
 * Skip embedded action text created by Marginalia
 * Not needed - ins and del nodes are instead highlight em
 */
function _skipAnnotationActions( node )
{
	if ( ELEMENT_NODE == node.nodeType && 'ins' == domutil.getLocalName( node ).toLowerCase() )
	{
		if ( node.parentNode && domutil.hasClass( node.parentNode, Marginalia.C_HIGHLIGHT ) )
			return true;
	}
	return false;
}


/*
 * Handler for standard createAnnotation button
 * Application may choose to do things otherwise (e.g. for edit actions)
 */
function clickCreateAnnotation( event, id, editor )
{
	// This might be called from a handler not set up by addEvent,
	// so use the clumsy functions.
	event = domutil.getEvent( event );
	domutil.stopPropagation( event );
	post = marginalia.listPosts( ).getPostById( id );
	createAnnotation( post, true, editor );
}


Marginalia.prototype.getSelection = function( warn )
{
	// Can't create an annotation while one is being edited
	if ( marginalia.noteEditor )
		return false;
	
	// Test for selection support (W3C or IE)
	if ( ( ! window.getSelection || null == window.getSelection().rangeCount )
		&& null == document.selection )
	{
		if ( warn )
			alert( getLocalized( 'browser support of W3C range required for annotation creation' ) );
		return false;
	}
		
	var textRange0 = getPortableSelectionRange();
	if ( ! textRange0 )
		return null;
	
	// Strip off leading and trailing whitespace and preprocess so that
	// conversion to WordRange will go smoothly.
	var textRange = TextRange.fromW3C( textRange0 );
	textRange = textRange.shrinkwrap( marginalia.skipContent );
	if ( ! textRange )
		// this happens if the shrinkwrapped range has no non-whitespace text in it
		return false;

	return textRange;
}



/**
 * Create a highlight range based on user selection.
 *
 * This is not in the event handler section above because it's up to the calling
 * application to decide what control creates an annotation.  Deletes and edits,
 * on the other hand, are built-in to the note display.
 *
 * That said, the standard interface calls this from clickCreateAnnotation
 */
function createAnnotation( post, warn, editor )
{
	var marginalia = window.marginalia;

	// Can't create an annotation while one is being edited
	if ( marginalia.noteEditor )
		return false;
	
	textRange = marginalia.cachedSelection;
	if ( ! textRange )
		var textRange = marginalia.getSelection( warn );
	
	if ( ! textRange )
	{
		if ( warn )
			alert( getLocalized( 'select text to annotate' ) );
		return false;
	}
	
	// Check for an annotation with id 0.  If one exists, we can't send another request
	// because the code would get confused by the two ID values coming back.  In that
	// case (hopefully very rare), silently fail.  (I figure the user doesn't want to
	// see an alert pop up, and the natural human instinct would be to try again).
	if ( null != document.getElementById( Marginalia.ID_PREFIX + '0' ) )
		return;
	

/*	// Find the post
	var post;
	if ( null == postId )
	{
		post = marginalia.posts.getPostByElement( textRange.startContainer );
		if ( null == post )
			return false;
	}
	else
		post = marginalia.listPosts( ).getPostById( postId );
*/	
	// Confirm that the selection is within the post
	var contentElement = post.getContentElement( );
	if ( ! ( ( domutil.isElementDescendant( textRange.startContainer, contentElement )
		|| textRange.startContainer == contentElement )
		&& ( domutil.isElementDescendant( textRange.endContainer, contentElement )
		|| textRange.endContainer == contentElement ) ) )
	{
		if ( warn )
			alert( getLocalized( 'invalid selection' ) );
		return false;
	}
	
	var annotation = new Annotation( {
		url: post.getUrl( ),
		'userid': marginalia.loginUserId,	// don't know why I need the quotes.  suspicious.
		quoteAuthorId: post.getAuthorId( ),
		quoteAuthorName: post.getAuthorName( ),
		sheet: marginalia.sheet
	} );
	
	var wordRange = WordRange.fromTextRange( textRange, contentElement, marginalia.skipContent );
	var sequenceRange = wordRange.toSequenceRange( contentElement );
	var xpathRange = wordRange.toXPathRange( contentElement );
	
	// Compress whitespace in quote down to a single space
	var quote = getTextRangeContent( textRange, marginalia.skipContent );
	quote = quote.replace( /(\s|\u00a0)+/g, ' ' );
	annotation.setQuote( quote );
	
	if ( 0 == annotation.getQuote().length )
	{
		annotation.destruct( );
		if ( warn )
			alert( getLocalized( 'zero length quote' ) );
		return false;
	}
	
	annotation.setSequenceRange( sequenceRange );
	annotation.setXPathRange( xpathRange );

	// TODO: test selection properly
	if ( null == annotation )
	{
		if ( warn )
			alert( getLocalized( 'invalid selection' ) );
		return false;
	}
	
	// Check to see whether the quote is too long (don't do this based on the raw text 
	// range because the quote strips leading and trailing spaces)
	if ( annotation.getQuote().length > Marginalia.maxQuoteLength )
	{
		annotation.destruct( );
		if ( warn )
			alert( getLocalized( 'quote too long' ) );
		return false;
	}
	
	// If no editor is specified, use the default
	if ( ! editor )
		editor = marginalia.newEditor( annotation );
	
	post.createAnnotation( marginalia, annotation, editor );
	
	return true;
}
