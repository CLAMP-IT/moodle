/*
 * RangeInfo.js
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
 * $Id: RangeInfo.js 537 2012-05-30 21:51:38Z geof.glass $
 */
 
function parseRangeInfoXml( xmldoc )
{
	var listElement = xmldoc.documentElement;
	if ( domutil.getLocalName( listElement ) != 'range-infos' )
		return null;
	
	var infoArray = new Array();
	for ( var blockElement = listElement.firstChild;  blockElement;  blockElement = blockElement.nextSibling )
	{
		if ( ELEMENT_NODE == blockElement.nodeType && 'range-info' == domutil.getLocalName( blockElement ) )
		{
			var info = new RangeInfo( );
			info.fromXml( blockElement );
			infoArray[ infoArray.length ] = info;
		}
	}
	return infoArray;
}

function RangeInfo( xpathRange, sequenceRange )
{
	this.users = new Array();
	this.xpathRange = xpathRange;
	this.sequenceRange = sequenceRange;
	this.url = null;
}

RangeInfo.prototype.resolveStart = function( root )
{
	if ( this.xpathRange && this.xpathRange.start)
		return this.xpathRange.start.getReferenceElement( root );
	else
		return this.sequenceRange.start.getReferenceElement( root );
}

RangeInfo.prototype.fromXml = function( blockElement )
{
	this.url = blockElement.getAttribute( 'url' );
	for ( var node = blockElement.firstChild;  node;  node = node.nextSibling )
	{
		if ( ELEMENT_NODE == node.nodeType)
		{
			if ( 'range' == domutil.getLocalName( node ) )
			{
				var format = node.getAttribute( 'format' );
				if ( 'xpath' == format )
					this.xpathRange = XPathRange.fromString( domutil.getNodeText( node ) );
				else if ( 'sequence' == format )
					this.sequenceRange = SequenceRange.fromString( domutil.getNodeText( node ) );
			}
			else if ( 'user' == node.tagName )
			{
				this.users[ this.users.length ] = new UserInfo( );
				this.users[ this.users.length - 1 ].fromXml( node );
			}
		}
	}
}

function UserInfo( userid, userName, noteCount, editCount )
{
	this.userid = userid;
	this.userName = userName;
	this.noteCount = noteCount;
	this.editCount = editCount;
	return this;
}

UserInfo.prototype.fromXml = function( userElement )
{
	this.userName = domutil.getNodeText( userElement );
	this.userid = userElement.getAttribute( 'id' );
	this.userid = this.userid ? this.userid : '';
	this.noteCount = userElement.getAttribute( 'notes' );
	this.noteCount = Number( this.noteCount ? this.noteCount : 0 );
	this.editCount = userElement.getAttribute( 'edits' );
	this.editCount = Number( this.editCount ? this.editCount : 0 );
}
