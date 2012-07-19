/*
 * link-ui-simple.js
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
 * $Id: link-ui-simple.js 537 2012-05-30 21:51:38Z geof.glass $
 */
 
/**
 * This class defines default behavior for elements of the linking user interface
 * An instance is held in th Marginalia object.  Some applications will implement
 * their own versions to provide customized UI behavior.
 */
function SimpleLinkUi( )
{ }

SimpleLinkUi.prototype.bind = FreeformNoteEditor.prototype.bind;

SimpleLinkUi.prototype.clear = function( marginalia )
{
	this.editNode = null;
}

SimpleLinkUi.prototype.show = function( marginalia )
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
		type:  'text' } ) );
	addEvent( this.editNode, 'keypress', _editNoteKeypress );
	//addEvent( editNode, 'keyup', _editChangedKeyup );
	
	// add the delete button
	noteElement.appendChild( domutil.button( {
		className:  Marginalia.C_LINKDELETEBUTTON,
		title:  getLocalized( 'delete annotation link button' ),
		content:  'x',
		annotationId:  annotation.getId(),
		onclick: SimpleLinkUi._deleteLink } ) );
}

SimpleLinkUi.prototype.focus = function( )
{
	this.editNode.focus( );
}

SimpleLinkUi.prototype.save = function( )
{
	this.annotation.setLink( this.editNode.value );
	this.annotation.setLinkTitle( '' );
}


/**
 * Delete a link
 */
SimpleLinkUi._deleteLink = function( event )
{
	event.stopPropagation( );
	window.marginalia.noteEditor.editNode.value = '';
	_saveAnnotation( event );
}

