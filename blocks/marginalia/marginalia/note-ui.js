/*
 * note-ui.js
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
 * $Id: note-ui.js 537 2012-05-30 21:51:38Z geof.glass $
 */

Marginalia.C_DUMMY = Marginalia.PREFIX + 'dummy';				// used for dummy item in note list 
Marginalia.C_QUOTENOTFOUND = Marginalia.PREFIX + 'quote-error';	// note's corresponding highlight region not found
Marginalia.C_NOTECOLLAPSED = Marginalia.PREFIX + 'collapsed';	// only the first line of the note shows
Marginalia.C_EDITCHANGED = Marginalia.PREFIX + 'changed';		// indicates content of a text edit changed
Marginalia.C_OTHERUSER = Marginalia.PREFIX + 'other-user';
Marginalia.C_USERNAME = Marginalia.PREFIX + 'username';	// name (initials) of user who created annotation
Marginalia.C_RECENT = Marginalia.PREFIX + 'recent';	// this annotation is recent

// Classes to identify specific controls
Marginalia.C_LINKBUTTON = Marginalia.PREFIX + 'annotation-link';
Marginalia.C_DELETEBUTTON = Marginalia.PREFIX + 'annotation-delete';


/**
 * Get the list of notes.  It is a DOM element containing children,
 * each of which has an annotation field referencing an annotation.
 * There is a dummy first child because of spacing problems in IE.
 */
PostMicro.prototype.getNotesElement = function( marginalia )
{
	// Make sure it has the additional annotation properties added
	if ( ! this.notesElement )
	{
		var t = marginalia.selectors[ 'mia_notes' ].node( this.getElement( ) );
		this.notesElement = 'ol' == t.tagName.toLowerCase( ) ? t : t.getElementsByTagName( 'ol' )[ 0 ];
	}
	return this.notesElement;
}


PostMicro.prototype.initMargin = function( marginalia )
{
	var margin = marginalia.selectors[ 'mia_notes' ].node( this.getElement( ) );

	//var postId = this.getElement();
//	var margin = this.getNotesElement( marginalia );
	margin.onmousedown = function( ) {
		marginalia.cachedSelection = marginalia.getSelection( );
//		trace( null, 'cache selection: ' + marginalia.cachedSelection );
	};
	var post = this;
	margin.onclick = function( event ) {
		event = domutil.getEvent( event );
		// only create an annotation if a) this click was on the margin itself,
		// note a child node, and b) there is no edit in progress
		if ( ! marginalia.noteEditor && domutil.getEventTarget( event ) == margin )
		{
//			trace( null, 'clicked' );
			createAnnotation( post );
			domutil.stopPropagation( event );
		}
//		trace( null, 'clear selection cache' );
		marginalia.cachedSelection = null;
	};
}


PostMicro.prototype.showTip = function( marginalia, tip, onclose )
{
	var notesElement = this.getNotesElement( marginalia );
	var tipNode = domutil.element( 'li', {
		className: Marginalia.PREFIX + 'tip'
	}, tip );

	var postMicro = this;
	var f = function( ) {
		postMicro.hideTip( marginalia, tipNode );
		if ( onclose )
			onclose( );
	};
	
	var controls = domutil.element( 'div', { className: 'controls' } );
	// delete button
	controls.appendChild( domutil.button( {
		className:  Marginalia.C_DELETEBUTTON,
		title:  getLocalized( 'delete tip button' ),
		content:  marginalia.icons[ 'delete' ],
		onclick: f
	} ) );
	tipNode.appendChild( controls );
		
	for ( var node = notesElement.firstChild; node && ! node[ Marginalia.F_ANNOTATION ];  node = node.nextSibling )
		;
	notesElement.insertBefore( tipNode, node );
	this.repositionSubsequentNotes( tipNode );
	return tipNode;
}

PostMicro.prototype.hideTip = function( marginalia, tipNode )
{
	var notesElement = this.getNotesElement( marginalia );
	var next = tipNode.nextSibling;
	notesElement.removeChild( tipNode );
	this.repositionNotes( marginalia, next );
}


/**
 * Get the node that will follow this one once it is inserted in the node list
 * Slow, but necessary when the annotation has not yet been inserted in the node list
 * A return value of null indicates the annotation would be at the end of the list
 */
PostMicro.prototype.getAnnotationNextNote = function( marginalia, annotation )
{
	var notesElement = this.getNotesElement( marginalia );
	// Go from last to first, on the assumption that this function will be called repeatedly
	// in order.  Calling in reverse order gives worst-case scenario O(n^2) behavior.
	for ( var prevNode = notesElement.lastChild;  null != prevNode;  prevNode = prevNode.previousSibling )
	{
		// In case it's a dummy list item or other
		if ( ELEMENT_NODE == prevNode.nodeType && prevNode[ Marginalia.F_ANNOTATION ] )
		{
			var prevAnnotation = prevNode[ Marginalia.F_ANNOTATION ];
			// Why on earth would this happen??
			if ( prevAnnotation.getId() == annotation.getId() )
				break;
			else if ( annotation.compareRange( prevAnnotation ) >= 0 )
				break;
		}
	}
	
	if ( prevNode )
		return prevNode.nextSibling;
	else
	{
		// Insert at the beginning of the list - but after any initial dummy nodes!
		var nextNode;
		for ( nextNode = notesElement.firstChild;  nextNode;  nextNode = nextNode.nextSibling )
		{
			if ( ELEMENT_NODE == nextNode.nodeType && nextNode[ Marginalia.F_ANNOTATION ] )
				break;
		}
		return nextNode;	// will be null if no annotations in the list
	}
}


PostMicro.prototype.getNoteId = function( marginalia, annotation )
{
//	assert( typeof annotationId == 'number' );
	return Marginalia.ID_PREFIX + annotation.getId();
}

/**
 * Find or create the list item for a margin note
 * nextNode - the node following this list element in the margin
 */
PostMicro.prototype.showNoteElement = function( marginalia, annotation, nextNode )
{
	var postMicro = this;
	var noteList = this.getNotesElement( marginalia );

	// Will need to align the note with the highlight.
	// If the highlight is not found, then the quote doesn't match - display
	// the annotation, but with an error and deactivate some behaviors.
	var highlightElement = domutil.childByTagClass( this.getContentElement( ), 'em',
		Marginalia.ID_PREFIX + annotation.getId(), null );
	var quoteFound = highlightElement != null;
	
	// Find or create the list item
	var noteElement = document.getElementById( this.getNoteId( marginalia, annotation ) );
	if ( noteElement )
	{
		trace( 'showNote', ' Note already present' );
		this.clearNote( marginalia, annotation );
		if ( ! quoteFound )
			domutil.setClass( noteElement, Marginalia.C_QUOTENOTFOUND, quoteFound );
	}
	else
	{
		trace( 'showNote', ' Create new note' );

		// Is this a recent post?
		// Don't flag this for one's own notes - that would be cluttered and confusing.
		// Currently, moodle does not seem to be storing anything in the forum_read table, so this doesn't work
		var isRecent = annotation.isRecent( );
//		isRecent = isRecent && marginalia.loginUserId && annotation.getUserId( ) != marginalia.loginUserId;
		var className = ( quoteFound ? '' : Marginalia.C_QUOTENOTFOUND ) + ' '
			+ ( isRecent && marginalia.enableRecentFlag ? Marginalia.C_RECENT : '' );

		var noteElement = domutil.element( 'li', {
			id:  Marginalia.ID_PREFIX + annotation.getId(),
			className:  className,
			title: ' ' } );
		noteElement[ Marginalia.F_ANNOTATION ] = annotation;

		// Align the note (takes no account of subsequent notes, which is OK because this note
		// hasn't yet been filled out)
		var alignElement = highlightElement ? highlightElement : this.getNoteAlignElement( marginalia, annotation );
		if ( null != alignElement )
		{
			// The margin must be relative to a preceding list item.
			var prevNode = null;
			if ( nextNode )
				prevNode = domutil.prevByTagClass( nextNode, 'li' );
			else
			{
				prevNode = domutil.childrenByTagClass( noteList, 'li' );
				if ( prevNode )
					prevNode = prevNode[ prevNode.length - 1 ];
			}
	
			// If there is no preceding note, create a dummy
			if ( null == prevNode )
			{
				prevNode = domutil.element( 'li', { className:  Marginalia.C_DUMMY } );
				noteList.insertBefore( prevNode, nextNode );
			}
			
			var pushdown = this.calculateNotePushdown( marginalia, prevNode, alignElement );
			noteElement.style.marginTop = '' + ( pushdown > 0 ? String( pushdown ) : '0' ) + 'px';
		}
		
		// Insert the note in the list
		trace( 'showNote', ' Note ' + noteElement + ' inserted before ' + nextNode + ' in ' + noteList + '(' + noteList.parentNode + ')');
		noteList.insertBefore( noteElement, nextNode );
	}	

	// Add note hover behaviors
	addEvent( noteElement, 'mouseover', _hoverAnnotation );
	addEvent( noteElement, 'mouseout', _unhoverAnnotation );
	
	return noteElement;
}


/**
 * Show a note in the margin
 * Regular annotation display with control buttons (delete, link)
 * Call showEdit if you want to show an editor instead
 */
PostMicro.prototype.showNote = function( marginalia, annotation, nextNode )
{
	trace( 'showNote', 'Show note ' + annotation.toString() );

	var noteElement = this.showNoteElement( marginalia, annotation, nextNode );
	
	// Mark the action
	if ( marginalia.showActions && annotation.getAction() )
		domutil.addClass( noteElement, Marginalia.C_ACTIONPREFIX + annotation.getAction() );
	
	// Calculating these parameters here makes it much easier to implement note display
	var params = {
		isCurrentUser: null != marginalia.loginUserId && annotation.getUserId( ) == marginalia.loginUserId,
		linkingEnabled: marginalia.editors[ 'link' ] ? true : false,
		quoteFound: null != domutil.childByTagClass( this.getContentElement( ), 'em',
			Marginalia.ID_PREFIX + annotation.getId(), null),
		keyword: marginalia.keywordService ? marginalia.keywordService.getKeyword( annotation.getNote() ) : null
	};
	
	// Generate actual display elements and get behavior rules
	 marginalia.displayNote( marginalia, annotation, noteElement, params );
	 
	return noteElement;
}

Marginalia.prototype.bindNoteBehaviors = function( annotation, parentElement, behaviors )
{
	var marginalia = this;
	var postMicro = domutil.nestedFieldValue( parentElement, Marginalia.F_POST );

	// Apply behavior rules
	// These are separated out to insulate display implementations from changes to internal APIs
	for ( var i = 0;  i < behaviors.length;  ++i )
	{
		var nodes = jQuery( behaviors[ i ][ 0 ], parentElement );
		if ( nodes.length == 1 )
		{
			var node = nodes[ 0 ];
			var props = behaviors[ i ][ 1 ];
			for ( var property in props )
			{
				var value = props[ property ];
				this.bindNoteBehavior( node, property, value );
			}
		}
		else
			trace( 'behaviors', 'Show note behavior unable to find node: ' + behaviors[ i ][ 0 ] );
	}
}

Marginalia.prototype.bindNoteBehavior = function( node, property, value )
{
	// Functions to associate with events (click etc.)
	var eventMappings = { 
		'delete': _deleteAnnotation,
//		edit: _editAnnotation,
		save: _saveAnnotation };
		
	switch ( property )
	{
		case 'click':
			var f = eventMappings[ value ];
			if ( ! f )
			{
				var args = value.split( ' ' );
				if ( args.length >= 1 && args[ 0 ] == 'edit' )
				{
					if ( args.length == 2 )
						node.clickEditorType = args[ 1 ];
					f = _editAnnotation;
				}
			}
			if ( f )
				addEvent( node, 'click', f );
			else
				logError( 'Unknown note click behavior: ' + value );
			break;
		default:
			trace( 'behaviors', 'Unknown property: ' + property );
	}
}


/**
 * Default function for generating margin note display, stored in marginalia.displayNote
 */
Marginalia.defaultDisplayNote = function( marginalia, annotation, noteElement, params )
{
	var controls = domutil.element( 'div', { className: 'controls' } );
	noteElement.appendChild( controls );
		
	// add custom buttons
	// (do it here so they will be to the left of the standard buttons)
	if ( params.customButtons )
	{
		for ( var i = 0;  i < params.customButtons.length;  ++i )
		{
			var buttonSpec = params.customButtons[ i ];
			if ( marginalia.loginUserId == annotation.getUserId( ) ? buttonSpec.owner : buttonSpec.others )
				controls.appendChild( domutil.element( 'button', buttonSpec.params ) );
		}
	}
	
	if ( params.isCurrentUser )
	{
		// add the link button
		if ( params.linkingEnabled )
		{
			controls.appendChild( domutil.button( {
				className:  Marginalia.C_LINKBUTTON,
				title:  getLocalized( 'annotation link button' ),
				content:  marginalia.icons[ 'linkEdit' ]
			} ) );
		}

		// add the delete button
		controls.appendChild( domutil.button( {
			className:  Marginalia.C_DELETEBUTTON,
			title:  getLocalized( 'delete annotation button' ),
			content:  marginalia.icons[ 'delete' ]
		} ) );
		
		// KLUDGE ALERT #geof#
		// When a note is created and the user then hits Enter, IE8 by default
		// focuses on and then clicks the last button created.  This was auto-
		// deleting existing annotations when the user typed Enter to create a
		// new one.  button.blur() did not seem to solve the problem.  Instead,
		// I am creating a dummy button on each note, but making it so it does
		// not display.  Unbelievable $#!t that Microsoft comes up with.
		if ( 'exploder' == domutil.detectBrowser( ) )
		{
			controls.appendChild( domutil.button ( {
				style: 'visibility:hidden;position:absolute',
				content: '.'
			} ) );
		}
	}
	
	// add the text content
	// Substitutes urls with actual hyperlinks if possible
	// You may wonder why I do this with the DOM, rather using a regex to
	// insert <a> tags in the string.  I do this because the DOM is safer - I
	// *know* I can't possibly create any elements or entities other than those
	// I explicitly code here.  Escaping may be easy, but with the DOM it can't
	// be forgotten.  I only see two potential security risks here:  1) allowing
	// bad url schemes (so I limit to http and https), 2) linking to dangerous
	// sites.  The latter is unavoidable, and is presumably already a risk on
	// any site that allows user content.  At  least displaying the domain name
	// (a la Slashdot) gives some indication of safety.
	var noteText = domutil.element( 'p', {
		className: annotation.getNote() ? '' : 'empty',
		title: params.isCurrentUser ? getLocalized( 'edit annotation click' ) : '',
		content: annotation.getNote() ? annotation.getNote() : '\u261c'/*'\u203b'*/ } );
	domutil.urlize( noteText );
	
/*	while ( tail.length > 0 )
	{
		var match = tail.match( /https?:\/\/([a-zA-Z0-9\.-]+)(?:\/(?:[^ ]*[a-zA-Z0-9\/#])?)?/ );
		var url = null;
		if ( match )
			url = match[ 0 ];
		else
		{
			match = tail.match( /(www\.[a-zA-Z0-9\.-]+\.[a-zA-Z]{2,4})/ );
			if ( match )
				url = 'http://' + match[ 1 ] + '/';
		}
		if ( url )
		{
			var head = tail.substr( 0, match.index );
			if ( head.length )
				noteText.appendChild( document.createTextNode( head ) );
			domain = match[ 1 ];
			//if ( 'www.' == domain.substr( 0, 4 ) )
			//	domain = domain.substr( 4 );
			noteText.appendChild( domutil.element( 'a', {
				href: url,
				title: getLocalized( 'visit annotation link' ),
				onclick: domutil.stopPropagation,
				content: domain }));
			tail = tail.substr( head.length + url.length );
		}
		else
		{
			noteText.appendChild( document.createTextNode( tail ) );
			break;
		}
	}
	*/
	
//	var noteText = domutil.element( 'p', {
//		content: annotation.getNote() ? annotation.getNote() : '\u203b'
//	} );
	var titleText = null;

	if ( ! params.quoteFound || ! annotation.getSequenceRange( ) )
		titleText = getLocalized( 'quote not found' ) + ': \n"' + annotation.getQuote() + '"';
	else if ( params.keyword )
		titleText = params.keyword.description;
	
	if ( titleText )
		noteText.setAttribute( 'title', titleText );
	
	// This doesn't belong to the current user, add the name of the owning user
	if ( ! params.isCurrentUser )
	{
		domutil.addClass( noteElement, Marginalia.C_OTHERUSER );
		var username = annotation.getUserName( );
		if ( annotation.isRecent( ) )
			titleText = getLocalized( 'note user recent title' );
		else
			titleText = getLocalized( 'note user title' );
		titleText += annotation.getUpdated( ).toString( 'yyyy-MM-d H:mm' ); // tt' );
		// The space is not part of the note, nor is it part of the username.  This is important so
		// that it doesn't get underlined or otherwise styled with the username.
		noteText.insertBefore( document.createTextNode( ' ' ), noteText.firstChild );
		noteText.insertBefore( domutil.element( 'span', {
			className:  Marginalia.C_USERNAME,
			title:  titleText,
			content:  username + ':' } ), noteText.firstChild );
	}
	noteElement.appendChild( noteText );
	
	// Return behavior mappings
	if ( params.isCurrentUser )
	{
		marginalia.bindNoteBehaviors( annotation, noteElement, [
			[ 'button.' + Marginalia.C_LINKBUTTON, { click: 'edit link' } ],
			[ 'button.' + Marginalia.C_DELETEBUTTON, { click: 'delete' } ],
			[ 'p', { click: 'edit' } ]
		] );
	}
}


/**
 * Show a note editor in the margin
 * If there's already a note editor present, remove it
 */
PostMicro.prototype.showNoteEditor = function( marginalia, annotation, editor, nextNode )
{
	var noteElement = this.showNoteElement( marginalia, annotation, nextNode );

	// Ensure the window doesn't scroll by saving and restoring scroll position
	var scrollY = domutil.getWindowYScroll( );
	var scrollX = domutil.getWindowXScroll( );

	// Check whether there's already an editor present.  If so, remove it.
	// annotationOrig is a backup of the original unchanged annotation.  It can be used
	// to restore the original annotation state if the edit is cancelled.
	var setEvents = false;
	if ( marginalia.noteEditor )
	{
		editor.annotationOrig = marginalia.noteEditor.annotationOrig;
		if ( marginalia.noteEditor.save )
			marginalia.noteEditor.save( marginalia );
		if ( marginalia.noteEditor.clear )
			marginalia.noteEditor.clear( marginalia );
		if ( marginalia.noteEditor.annotation != annotation )
			_saveAnnotation( );
	}
	
	if ( ! marginalia.noteEditor || marginalia.noteEditor.noteElement != noteElement )
	{
		// Since we're editing, set the appropriate class on body
		domutil.addClass( document.body, Marginalia.C_EDITINGNOTE );
		this.flagAnnotation( marginalia, annotation, Marginalia.C_EDITINGNOTE, true );
		
		setEvents = true;
		editor.annotationOrig = clone( annotation );
	}

	while ( noteElement.firstChild )
		noteElement.removeChild( noteElement.firstChild );

	// Initialize the new editor
	editor.bind( marginalia, this, annotation, noteElement );

	marginalia.noteEditor = editor;
	editor.show( marginalia );
	this.repositionNotes( marginalia, this.nextSibling );
	editor.focus( marginalia );
	window.scrollTo( scrollX, scrollY );

	// If anywhere outside the note area is clicked, the annotation will be saved.
	// Beware serious flaws in IE's model (see addAnonBubbleEventListener code for details),
	// so this only works because I can figure out which element was clicked by looking for
	// AN_EDITINGNOTE_CLASS.
	
	/*
	 * FIXED:  Turns out a <script/></script> combination caused this!  Wow.
	 * Removed closing slash on first tag and problem went away.
	 *
	// Another problem:  Safari 4 scrolls the page down 240px between the
	// firing of textInput and keyup.  There appears to be no way to stop this.  So
	// this event handler checks for the scroll, and undoes it if necessary.
	// Bleargh.  Thanks Safari.
	var yscroll = domutil.getWindowYScroll( );
	var fixSafariScroll = function( event )
	{
		if ( domutil.getWindowYScroll() != yscroll )
		{
			var xscroll = domutil.getWindowXScroll();
			window.scrollTo( xscroll, yscroll );
			window.status = 'Apparently a Safari bug scrolls the window when you type.  Marginalia is trying to undo the damage.';
		}
	}
	*/
	
	if ( setEvents )
	{
		addEvent( document.documentElement, 'click', _saveAnnotation );
		addEvent( noteElement, 'click', domutil.stopPropagation );
		addEvent( noteElement, 'keypress', domutil.stopPropagation );
	//	addEvent( noteElement, 'keyup', fixSafariScroll );
	}

	return noteElement;
}


function debugSafariScroll( event )
{
	trace( null, 'Key event ' + event.type + ', vertical position ' + domutil.getWindowYScroll() );
	domutil.stopPropagation( event );
}

/**
 * Freeform margin note editor
 */
function FreeformNoteEditor( )
{
	this.editNode = null;
}

FreeformNoteEditor.prototype.bind = function( marginalia, postMicro, annotation, noteElement )
{
	this.marginalia = marginalia;
	this.postMicro = postMicro;
	this.annotation = annotation;
	this.noteElement = noteElement;
}

FreeformNoteEditor.prototype.clear = function( marginalia )
{
	this.editNode = null;
}

FreeformNoteEditor.prototype.save = function( marginalia )
{
	this.annotation.setNote( this.editNode.value );
}

FreeformNoteEditor.REMAINING_THRESHOLD = 50;
FreeformNoteEditor.prototype.show = function( marginalia )
{
	var postMicro = this.postMicro;
	var marginalia = this.marginalia;
	var annotation = this.annotation;
	var noteElement = this.noteElement;
	var noteText = annotation.getNote( );
	
	// Create the edit box
	this.editNode = document.createElement( "textarea" );
	this.editNode.rows = 3;
	this.editNode.appendChild( document.createTextNode( noteText ) );
	this.noteElement.appendChild( this.editNode );
	
	// Create the place for showing how many characters remain
	var threshold = marginalia.maxNoteLength - FreeformNoteEditor.REMAINING_THRESHOLD;
	var remainingNode = domutil.element( 'p', {
		className: Marginalia.PREFIX + 'charsremaining',
		style: 'display:none' } );
	noteElement.appendChild( remainingNode, null );
	FreeformNoteEditor.showCharsRemaining( marginalia, postMicro, this.editNode, remainingNode, noteElement, prompt );

	// Set focus after making visible later (IE requirement; it would be OK to do it here for Gecko)
	var editNode = this.editNode;
	var prompt = getLocalized( 'chars remaining' );
	var onkey = function( e ) {
		FreeformNoteEditor.showCharsRemaining( marginalia, postMicro, editNode, remainingNode, noteElement, prompt );
		_editChangedKeyup( e );
	};
	this.editNode.annotationId = this.annotation.getId();
	addEvent( this.editNode, 'keypress', _editNoteKeypress );
	addEvent( this.editNode, 'keyup', onkey );
}

FreeformNoteEditor.prototype.focus = function( marginalia )
{
	this.editNode.focus( );
	// Yeah, ain't IE great.  You gotta focus TWICE for it to work.  I don't
	// want to burden other browsers with its childish antics.
	if ( 'exploder' == domutil.detectBrowser( ) )
		this.editNode.focus( );
}
		
FreeformNoteEditor.showCharsRemaining = function( marginalia, postMicro, editNode, remainingNode, noteElement, prompt )
{
	var threshold = marginalia.maxNoteLength - FreeformNoteEditor.REMAINING_THRESHOLD;
	var reposition = false;
	if ( editNode.value.length > threshold && ! editNode.mia_showremaining )
	{
		jQuery( remainingNode ).css( 'display', 'block' );
		editNode.mia_showremaining = true;
		reposition = true;
	}
	if ( editNode.mia_showremaining )
	{
		var remaining = Math.max( marginalia.maxNoteLength - editNode.value.length, 0 );
		jQuery( remainingNode ).text( remaining + ' ' + prompt );
		if ( reposition )
			postMicro.repositionSubsequentNotes( marginalia, noteElement );
	}
}


/**
 * YUI Autocomplete margin note editor
 * Autocompletes to the keyword list
 * Requires YUI autocomplete
 */
function YuiAutocompleteNoteEditor( )
{
	this.editNode = null;
	this.queryNode = null;
	this.autocomplete = null;
}

YuiAutocompleteNoteEditor.prototype.bind = FreeformNoteEditor.prototype.bind;
YuiAutocompleteNoteEditor.prototype.clear = FreeformNoteEditor.prototype.clear;
YuiAutocompleteNoteEditor.prototype.save = FreeformNoteEditor.prototype.save;
YuiAutocompleteNoteEditor.prototype.focus = FreeformNoteEditor.prototype.focus;

YuiAutocompleteNoteEditor.prototype.show = function( marginalia )
{
	var postMicro = this.postMicro;
	var marginalia = this.marginalia;
	var annotation = this.annotation;
	var noteElement = this.noteElement;
	var noteText = annotation.getNote( );
	
	// Create the edit box
	this.editNode = document.createElement( "textarea" );
	this.editNode.rows = 3;
	this.editNode.appendChild( document.createTextNode( noteText ) );
	
	// Create the query results box
	this.queryNode = domutil.element( 'div' );

	var wrapperNode = domutil.element( 'div', { className: 'yui-skin-sam' } );
	wrapperNode.appendChild( this.editNode );
	wrapperNode.appendChild( this.queryNode );
	// this.queryNode.style.display = 'none';
	this.noteElement.appendChild( wrapperNode );

	// autocomplete is blowing up on IE8 and I need to get this working, so 
	// I'm (very mildly) sorry to say I'm just going to disable it for IE.
	if ( 'exploder' != domutil.detectBrowser( ) && YAHOO.widget && YAHOO.widget.AutoComplete
		&& ( YAHOO.util && YAHOO.util.LocalDataSource || YAHOO.widget.DS_JSArray ) )
	{
		var keywords = marginalia.keywordService.keywords;
		var keywordArray = [ ];
		for ( var i = 0;  i < keywords.length;  ++i )
			keywordArray[ keywordArray.length ] = keywords[ i ].name;
	
		var datasource;
		if ( YAHOO.util.LocalDataSource )
			datasource = new YAHOO.util.LocalDataSource( keywordArray ); 
		else if ( YAHOO.widget.DS_JSArray )
			datasource = new YAHOO.widget.DS_JSArray( keywordArray );
	
		// The autocomplete uses absolute positioning on the noteElement, resulting
		// in an incorrect height and then an incorrect pushdown for following
		// notes.  So grab the height here and reset it later.  repositionNotes is
		// needed otherwise wrapperNode.style won't be set below (why I don't know).
		var wrapperHeight = wrapperNode.offsetHeight;
		postMicro.repositionNotes( marginalia, this.noteElement.nextSibling );
	
		this.autocomplete = new YAHOO.widget.AutoComplete( this.editNode, this.queryNode, datasource, {
//			autoHighlight: false,
			typeAhead: true //  -- disabled as drop-down must be shown anyway
		} );
		var autocomplete = this.autocomplete;
		this.autocomplete.doBeforeExpandContainer = function ( elTextbox , elContainer , sQuery , aResults ) {
//			elContainer.style.top = wrapperNode.style.height;
			return false;
		};
	
		wrapperNode.style.height = String( wrapperHeight ) + 'px';
	}
	
	// Set focus after making visible later (IE requirement; it would be OK to do it here for Gecko)
	// Create the place for showing how many characters remain
	var editNode = this.editNode;
	var prompt = getLocalized( 'chars remaining' );
	var threshold = marginalia.maxNoteLength - FreeformNoteEditor.REMAINING_THRESHOLD;
	var remainingNode = domutil.element( 'p', {
		className: Marginalia.PREFIX + 'charsremaining',
		style: 'display:none' } );
	noteElement.appendChild( remainingNode, null );
	FreeformNoteEditor.showCharsRemaining( marginalia, postMicro, editNode, remainingNode, noteElement, prompt );

	// Set focus after making visible later (IE requirement; it would be OK to do it here for Gecko)
	var onkey = function( e ) {
		FreeformNoteEditor.showCharsRemaining( marginalia, postMicro, editNode, remainingNode, noteElement, prompt );
		_editChangedKeyup( e );
	};

	this.editNode.annotationId = this.annotation.getId();
	addEvent( this.editNode, 'keypress', _editNoteKeypress );
	addEvent( this.editNode, 'keyup', onkey );
}


/**
 * Position the notes for an annotation next to the highlight
 * It is not necessary to call this method when creating notes, only when the positions of
 * existing notes are changing
 */
PostMicro.prototype.positionNote = function( marginalia, annotation )
{
	var note = annotation.getNoteElement( marginalia );
	while ( null != note )
	{
		var alignElement = this.getNoteAlignElement( marginalia, annotation );
		// Don't push down if no align element was found
		if ( null != alignElement )
		{
			// #geof# doesn't work right.  Current fix is to have no intervening text nodes:
			// <div class="mia_margin"><ol><li class="mia_dummyfirst"></li></ol></div>
			var prev = jQuery( note ).prev( ); // note.previousSibling
			var pushdown = this.calculateNotePushdown( marginalia, prev, alignElement );
			note.style.marginTop = ( pushdown > 0 ? String( pushdown ) : '0' ) + 'px';
		}
		note = note.nextSibling;
	}
}

/**
 * Determine where an annotation note should be aligned vertically
 */
PostMicro.prototype.getNoteAlignElement = function( marginalia, annotation )
{
	// Try to find the matching highlight element
	var alignElement = domutil.childByTagClass( this.getContentElement( ), 'em', Marginalia.ID_PREFIX + annotation.getId(), null );
	// If there is no matching highlight element, pick the paragraph.  Prefer XPath range representation.
	if ( null == alignElement && annotation.getXPathRange( ) )
		alignElement = annotation.getXPathRange( ).start.getReferenceElement( this.getContentElement( ) );
	if ( null == alignElement && annotation.getSequenceRange( ) )
		alignElement = annotation.getSequenceRange( ).start.getReferenceElement( this.getContentElement( ) );
	return alignElement;
}

/**
 * Calculate the pixel offset from the previous displayed note to this one
 * by setting the top margin to the appropriate number of pixels.
 * The previous note and the highlight must already be displayed, but this note
 * does not yet need to be part of the DOM.
 */
PostMicro.prototype.calculateNotePushdown = function( marginalia, previousNoteElement, alignElement )
{
	var noteY = domutil.getElementYOffset( previousNoteElement, null ) + previousNoteElement.offsetHeight;
	var alignY = domutil.getElementYOffset( alignElement, null );
	var pushdown = alignY - noteY;
	return pushdown > 0 ? pushdown : 0;
}

/**
 * Reposition notes, starting with the note list element passed in
 * Repositioning consists of two things:
 * 1. Updating the margin between notes
 * 2. Collapsing a note if the two notes following it are both pushed down
 */
PostMicro.prototype.repositionNotes = function( marginalia, element )
{
	// We don't want the browser to scroll, which it might under some circumstances
	// (I believe it's a timing thing)
	while ( element )
	{
		this.repositionNote( marginalia, element );
		element = element.nextSibling;
	}
}

PostMicro.prototype.repositionNote = function( marginalia, element )
{
	var annotation = element[ Marginalia.F_ANNOTATION ];
	if ( annotation )
	{
		var alignElement = this.getNoteAlignElement( marginalia, annotation );
		if ( alignElement )
		{
			var goback = false;
			
			for ( var previous = element.previousSibling; previous;  previous = previous.previousSibling )
				if ( ELEMENT_NODE == previous.nodeType )
					break;
			var pushdown = this.calculateNotePushdown( marginalia, previous, alignElement );

		/* uncomment this to automatically collapse some notes: *
			// If there's negative pushdown, check whether the preceding note also has pushdown
			if ( pushdown < 0
				&& previous 
				&& previous[ Marginalia.F_ANNOTATION ] 
				&& ! hasClass( previous, AN_NOTECOLLAPSED_CLASS )
				&& previous.pushdown
				&& previous.pushdown < 0 )
			{
				// So now two in a row have negative pushdown.
				// Go back two elements and collapse, then restart pushdown 
				// calculations at the previous element.
				var collapseElement = previous.previousSibling;
				if ( collapseElement && collapseElement[ Marginalia.F_ANNOTATION ] )
				{
					addClass( collapseElement, AN_NOTECOLLAPSED_CLASS );
					element = previous;
					goback = true;
				}
			}
		*/
			// If we didn't have to go back and collapse a previous element,
			// set this note's pushdown correctly.
			if ( ! goback )
			{
				element.style.marginTop = ( pushdown > 0 ? String( pushdown ) : '0' ) + 'px';
				domutil.removeClass( element, Marginalia.C_NOTECOLLAPSED );
				element.pushdown = pushdown;
			}
		}
	}
}


/**
 * Reposition a note and any following notes that need it
 * Stop when a note is found that doesn't need to be pushed down
 */
PostMicro.prototype.repositionSubsequentNotes = function( marginalia, firstNote )
{
	for ( var note = firstNote;  note;  note = note.nextSibling )
	{
		if ( ELEMENT_NODE == note.nodeType && note[ Marginalia.F_ANNOTATION ] )
		{
			var alignElement = this.getNoteAlignElement( marginalia, note[ Marginalia.F_ANNOTATION ] );
			if ( alignElement )
			{
				var pushdown = this.calculateNotePushdown( marginalia, note.previousSibling, alignElement );
				if ( note.pushdown && note.pushdown == pushdown )
					break;
				note.style.marginTop = ( pushdown > 0 ? String( pushdown ) : '0' ) + 'px';
				note.pushdown = pushdown;
			}
		}
	}
}


/**
 * Remove an note from the displayed list
 * Returns the next list item in the list
 */
PostMicro.prototype.removeNote = function( marginalia, annotation )
{
	var listItem = annotation.getNoteElement( marginalia );
	var next = domutil.nextByTagClass( listItem, 'li' );
	listItem.parentNode.removeChild( listItem );
	listItem[ Marginalia.F_ANNOTATION ] = null; // dummy item won't have this field
	domutil.clearEventHandlers( listItem, true );	
//	if ( next )
//		this.repositionNotes( marginalia, next );
	return next;
}

/**
 * Remove the contents of a not element (but keep the element - it may have
 * important class/id etc. values on it.
 */
PostMicro.prototype.clearNote = function( marginalia, annotation )
{
	var note = annotation.getNoteElement( marginalia );
	domutil.clearEventHandlers( note, true, true );
	while ( note.firstChild )
		note.removeChild( note.firstChild );
	return note;
}


/**
 * Click on annotation to edit it
 */
function _editAnnotation( event )
{
	var marginalia = window.marginalia;

	var post = domutil.nestedFieldValue( this, Marginalia.F_POST );
	var annotation = domutil.nestedFieldValue( this, Marginalia.F_ANNOTATION );

	// If an annotation is already being edited and it's not *this* annotation,
	// return (don't stop propagation)
	if ( marginalia.noteEditor && marginalia.noteEditor.annotation != annotation )
		return;

	event.stopPropagation( );
	
//	var nextNode = post.removeNote( marginalia, annotation );
	
	// If a specific editor type is to be invoked, its name is stored in clickEditorType.
	// This is better than spinning off a whole pile of lambda functions, each with its
	// own huge context.
	var editor = marginalia.newEditor( annotation, this.clickEditorType );
	post.showNoteEditor( marginalia, annotation, editor );
}


/**
 * Hit a key while editing an annotation note
 * Handles Enter to save
 */
function _editNoteKeypress( event )
{
	var target = domutil.getEventTarget( event );
	var post = domutil.nestedFieldValue( target, Marginalia.F_POST );
	var annotation = domutil.nestedFieldValue( target, Marginalia.F_ANNOTATION );
	if ( event.keyCode == 13 )
	{
		post.saveAnnotation( window.marginalia, annotation );
		event.stopPropagation( );
		return false;
	}
	// should check for 27 ESC to cancel edit
	else
	{
		return true;
	}
}


/**
 * Update a text field to indicate whether its content has changed when a key is pressed
 */
function _editChangedKeyup( event )
{
	var marginalia = window.marginalia;
	var target = domutil.getEventTarget( event );
	var annotation = domutil.nestedFieldValue( target, Marginalia.F_ANNOTATION );
	if ( target.value != annotation.note )
		domutil.addClass( target, Marginalia.C_EDITCHANGED );
	else
		domutil.removeClass( target, Marginalia.C_EDITCHANGED );
}


/**
 * Save an annotation being edited
 */
function _saveAnnotation( event )
{
	// Can't truest IE events for information, so go elsewhere
	var annotation = window.marginalia.noteEditor.annotation;
	var post = window.marginalia.noteEditor.postMicro;
	post.saveAnnotation( window.marginalia, annotation );
}

/**
 * Cancel an annotation edit in progress
 */
function _cancelAnnotationEdit( event )
{
	var annotation = window.marginalia.noteEditor.annotation;
	var post = window.marginalia.noteEditor.postMicro;
	post.cancelAnnotationEdit( window.marginalia, annotation );
}

/**
 * Click annotation delete button
 */
function _deleteAnnotation( event )
{
	event.stopPropagation( );
	var post = domutil.nestedFieldValue( this, Marginalia.F_POST );
	var annotation = domutil.nestedFieldValue( this, Marginalia.F_ANNOTATION );
	post.deleteAnnotation( window.marginalia, annotation, marginalia.warnDelete );
}

