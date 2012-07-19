/*
 * link-ui-linkable.js
 *
 * Click-to-link support (used in OJS)
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
 * $Id: link-ui-clicktolink.js 537 2012-05-30 21:51:38Z geof.glass $
 */
 

/**
 * Don't initialize this until the DOM has been loaded
 */
function ClickToLinkUi( extlinks )
{
	this.extlinks = extlinks;	// permit links to other hosts?
}

ClickToLinkUi.prototype.bind = FreeformNoteEditor.prototype.bind;

ClickToLinkUi.prototype.clear = function( marginalia )
{
	this.editNode = null;
	if ( this.linkablePosts )
		this.linkablePosts.disableLinkTargets( );
	removeCookie( LinkablePosts.CK_LINKING );
	removeCookie( LinkablePosts.CK_LINKURL );
	domutil.removeClass( document.body, Marginalia.C_EDITINGLINK );
}

ClickToLinkUi.prototype.show = function( marginalia )
{
	var marginalia = this.marginalia;
	var annotation = this.annotation;
	var post = this.postMicro;
	var noteElement = this.noteElement;
	
	var controlId = Marginalia.ID_PREFIX + annotation.getId() + '-linkedit';
	
	// add the link label
	noteElement.appendChild( domutil.element( 'label', {
		title:  getLocalized( 'annotation link label' ),
		attr_for:  controlId,
		content:  marginalia.icons[ 'linkEdit' ] } ) );

	// Add the URL input field
	this.editNode = noteElement.appendChild( domutil.element( 'input', {
		id:  controlId,
		value:  annotation.getLink() ? annotation.getLink() : '',
		type:  this.extlinks ? 'text' : 'hidden' } ) );
	if ( this.extlinks )
	{
		addEvent( this.editNode, 'keypress', _editNoteKeypress );
		//addEvent( editNode, 'keyup', _editChangedKeyup );
	}
	
	// add the delete button
	noteElement.appendChild( domutil.button( {
		className:  Marginalia.C_LINKDELETEBUTTON,
		title:  getLocalized( 'delete annotation link button' ),
		content:  'x',
		annotationId:  annotation.getId(),
		onclick: SimpleLinkUi._deleteLink } ) );
	
	this.linkablePosts = new LinkablePosts( marginalia.posts );
	domutil.addEventListener( window, 'focus', _updateLinks );
	
	// Tell this window and others to be accept clicks for link creation
	domutil.addClass( document.body, Marginalia.C_EDITINGLINK );
	createCookie( LinkablePosts.CK_LINKING, annotation.id, 1 );
	_enableLinkTargets( );
}

ClickToLinkUi.prototype.focus = function( marginalia )
{
	if ( this.extlinks )
		this.editNode.focus( );
}

ClickToLinkUi.prototype.save = function( marginalia )
{
	this.annotation.setLink( this.editNode.value );
	this.annotation.setLinkTitle( '' );
}

ClickToLinkUi.prototype.setLink = function( link )
{
	this.editNode.value = link;
}

/**
 * Check whether a link-in-progress is consumated
 * Callback set up (in this browser window and others) by
 * _enableLinkTargets, removed by _disableLinkTargets
 */
function _updateLinks( )
{
	var marginalia = window.marginalia;
	if ( domutil.hasClass( document.body, Marginalia.C_EDITINGLINK ) )
	{
		var annotationId = readCookie( LinkablePosts.CK_LINKING );
		var newLink = readCookie( LinkablePosts.CK_LINKURL );
		if ( annotationId && newLink )
		{
			var annotationNode = document.getElementById( Marginalia.ID_PREFIX + annotationId );
			if ( annotationNode )
			{
				var marginalia = window.marginalia;
				var post = domutil.nestedFieldValue( annotationNode, Marginalia.F_POST );
				var annotation = domutil.nestedFieldValue( annotationNode, Marginalia.F_ANNOTATION );
				var editNode = domutil.childByTagClass( annotationNode, 'input', null, null );
				marginalia.noteEditor.setLink( newLink );
				_saveAnnotation( );
			}
		}
	}
}

