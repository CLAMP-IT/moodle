/*
 * track-changes.js
 *
 * Track change support for those that need it.  If your app doesn't need this
 * feature, don't include it.
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
 * $Id: track-changes.js 537 2012-05-30 21:51:38Z geof.glass $
 */
 
/**
 * Editor for selecting action type before proceding to actual editor
 */
function SelectActionNoteEditor( )
{ }

SelectActionNoteEditor.prototype.bind = FreeformNoteEditor.prototype.bind;

SelectActionNoteEditor.prototype.clear = function( marginalia )
{
	while ( this.noteElement.firstChild )
	{
		if ( this.noteElement.onclick )
			this.noteElement.onclick = null;
		this.noteElement.removeChild( this.noteElement.firstChild );
	}
}

SelectActionNoteEditor.prototype.show = function( marginalia )
{
	var postMicro = this.postMicro;
	var marginalia = this.marginalia;
	var annotation = this.annotation;
	var noteElement = this.noteElement;

	// Overlapping changes aren't permitted, so create a regular note instead
	if ( trackchanges.changeOverlaps( this.marginalia, this.postMicro, this.annotation ) )
		postMicro.showNoteEditor( marginalia, annotation, marginalia.newEditor(), noteElement );
	else
	{
		var ul = this.noteElement.appendChild( domutil.element( 'ul', {
			className: 'select-action',
			content: [
				domutil.element( 'li', {
					content: domutil.element( 'button', {
						content: getLocalized( 'action annotate button' ),
						onclick: function( event ) {
							postMicro.showNoteEditor( marginalia, annotation, marginalia.newEditor(), noteElement );
						}
					} )	
				} ),
				domutil.element( 'li', {
					content: domutil.element( 'button', {
						content: getLocalized( 'action insert before button' ),
						onclick: function( event ) {
							postMicro.showNoteEditor( marginalia, annotation, trackchanges.newInsertBeforeEditor(), noteElement );
						}
					} )	
				} ),
				domutil.element( 'li', {
					content: domutil.element( 'button', {
						content: getLocalized( 'action insert after button' ),
						onclick: function( event ) {
							postMicro.showNoteEditor( marginalia, annotation, trackchanges.newInsertAfterEditor(), noteElement );
						}
					} )	
				} ),
				domutil.element( 'li', {
					content: domutil.element( 'button', {
						content: getLocalized( 'action replace button' ),
						onclick: function( event ) {
							postMicro.showNoteEditor( marginalia, annotation, trackchanges.newReplaceEditor(), noteElement );
						}
					} )	
				} ),
				domutil.element( 'li', {
					content: domutil.element( 'button', {
						content: getLocalized( 'action delete button' ),
						onclick: function( event ) {
							postMicro.showNoteEditor( marginalia, annotation, trackchanges.newDeleteEditor(), noteElement );
						}
					} )	
				} )
			] } ) ) ;
	}
}

SelectActionNoteEditor.prototype.focus = function( marginalia )
{ }


function ActionNoteEditor( faction )
{
	this.faction = faction;
}

ActionNoteEditor.prototype.bind = function( marginalia, postMicro, annotation, noteElement )
{
	this.marginalia = marginalia;
	this.postMicro = postMicro;
	this.annotation = annotation;
	this.noteElement = noteElement;
	this.editor = marginalia.newEditor( );
	this.editor.bind( marginalia, postMicro, annotation, noteElement );
}

ActionNoteEditor.prototype.show = function( marginalia )
{
	this.faction( this );
	this.editor.show( marginalia );
}

ActionNoteEditor.prototype.focus = function( marginalia )
{
	this.editor.focus( marginalia );
}

ActionNoteEditor.prototype.clear = function( )
{
	this.editor.clear( marginalia );
}

ActionNoteEditor.prototype.save = function( )
{
	this.editor.save( marginalia );
}


function DummyEditor( faction )
{
	this.faction = faction;
}

DummyEditor.prototype.bind = function( marginalia, postMicro, annotation, noteElement )
{
	this.marginalia = marginalia;
	this.postMicro = postMicro;
	this.annotation = annotation;
	this.noteElement = noteElement;
}

DummyEditor.prototype.show = function( marginalia )
{
	this.faction( this );
}

DummyEditor.prototype.focus = function( marginalia )
{ }

DummyEditor.prototype.clear = function( marginalia )
{ }

DummyEditor.prototype.save = function( marginalia )
{ }

trackchanges = {
	validate: function( marginalia, post, annotation )
	{
		// Make sure edit annotations don't overlap
		if ( 'edit' == annotation.action )
		{
			if ( trackchanges.changeOverlaps( marginalia, post, annotation ) )
			{
				alert( getLocalized( 'create overlapping edits' ) );
				return false;
			}
		}
		return true;
	},
	
	changeOverlaps: function( marginalia, post, annotation )
	{
		// This takes linear time unfortunately, but is very straightforward.
		// It may be a candidate for optimization (e.g. by caching the annotation
		// list and/or doing a binary search).
		var annotations = post.listAnnotations( );
		var sequenceRange = annotation.getSequenceRange( );
		for ( var i = 0;  i < annotations.length;  ++i  )
		{
			if ( annotations[ i ].getAction( ) == 'edit' && annotations[ i ].getId() != annotation.getId() )
			{
				var tRange = annotations[ i ].getSequenceRange( );
				if ( tRange.start.compare( sequenceRange.end ) <= 0 )
				{
					if ( tRange.end.compare( sequenceRange.start ) >= 0 )
						return true;
				}
				// At least save walking through the rest of the list
				else
					break;
			}
		}
		return false;
	},
	
	makeInsertBefore: function( annotation )
	{
		annotation.setAction( 'edit' );
		annotation.setSequenceRange( annotation.getSequenceRange( ).collapsedToStart( ) );
		annotation.setXPathRange( annotation.getXPathRange( ).collapsedToStart( ) );
		annotation.setQuote( '' );
	},
	
	makeInsertAfter: function( annotation )
	{
		annotation.setAction( 'edit' );
		annotation.setSequenceRange( annotation.getSequenceRange( ).collapsedToEnd( ) );
		annotation.setXPathRange( annotation.getXPathRange( ).collapsedToEnd( ) );
		annotation.setQuote( '' );
	},

	newInsertBeforeEditor: function( )
	{
		return new ActionNoteEditor( function( e ) {
			trackchanges.makeInsertBefore( e.annotation );
			if ( ! trackchanges.validate( e.marginalia, e.postMicro, e.annotation ) )
				_cancelAnnotationEdit( );
//			e.postMicro.showNoteEditor( e.marginalia, e.annotation, e.editor );
		} );
	},
	
	newInsertAfterEditor: function( )
	{
		return new ActionNoteEditor( function( e ) {
			trackchanges.makeInsertAfter( e.annotation );
			if ( ! trackchanges.validate( e.marginalia, e.postMicro, e.annotation ) )
				_cancelAnnotationEdit( );
//			e.postMicro.showNoteEditor( e.marginalia, e.annotation, e.editor );
		} );
	},
	
	newReplaceEditor: function( )
	{
		return new ActionNoteEditor( function( e ) {
			e.annotation.setAction( 'edit' );
			if ( ! trackchanges.validate( e.marginalia, e.postMicro, e.annotation ) )
				_cancelAnnotationEdit( );
			e.postMicro.removeHighlight( e.marginalia, e.annotation );
			e.postMicro.showHighlight( e.marginalia, e.annotation );
//			e.postMicro.showNoteEditor( e.marginalia, e.annotation, e.editor );
		} );
	},
	
	newDeleteEditor: function( )
	{
		return new DummyEditor( function( e ) {
			e.annotation.setAction( 'edit' );
			if ( ! trackchanges.validate( e.marginalia, e.postMicro, e.annotation ) )
				_cancelAnnotationEdit( );
			e.postMicro.removeHighlight( e.marginalia, e.annotation );
			e.postMicro.showHighlight( e.marginalia, e.annotation );
			_saveAnnotation( );
		} );
	},
	
	addEditShortcuts: function( )
	{
		// Insert before
		shortcut.add( 'a', function( ) {
			createAnnotation(null, false, trackchanges.newInsertBeforeEditor() );
		}, {
			disable_in_input: true
		} );
		
		// Insert after
		shortcut.add( 'z', function( ) {
			createAnnotation( null, false, trackchanges.newInsertAfterEditor( ) );
		}, {
			disable_in_input: true
		} );
		
		// Replace
		shortcut.add( 'r', function( ) {
			createAnnotation(null, false, trackchanges.newReplaceEditor( ));
		}, {
			disable_in_input: true
		} );
		
		// Delete
		shortcut.add( 'x', function( ) {
			createAnnotation(null, false, trackchanges.newDeleteEditor( ) );
		}, {
			disable_in_input: true
		} );
	}
}
