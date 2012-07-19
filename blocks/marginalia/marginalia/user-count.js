/*
 * user-count.js
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

 function parseBlockUserCountsXml( xmldoc )
{
	var listElement = xmldoc.documentElement;
	if ( listElement.tagName != 'block-users' )
		return null;
	
	var counts = new Array();
	for ( var countElement = listElement.firstChild;  countElement;  countElement = countElement.nextSibling )
	{
		if ( ELEMENT_NODE == countElement.nodeType && 'element' == countElement.tagName )
		{
			var count = new UserCount( );
			count.fromXml( countElement );
			counts[ counts.length ] = count;
		}
	}
	return counts;
}

function UserCount( xpath, blockpath )
{
	this.users = new Array();
	this.xpath = xpath;
	this.blockpath = blockpath;
	this.url = null;
}

UserCount.prototype.fromXml = function( element )
{
	this.xpath = element.getAttribute( 'xpath' );
	this.blockpath = element.getAttribute( 'block' );
	this.url = element.getAttribute( 'url' );
	for ( var userElement = element.firstChild;  userElement;  userElement = userElement.nextSibling )
	{
		if ( ELEMENT_NODE == userElement.nodeType && 'user' == userElement.tagName )
			this.users[ this.users.length ] = getNodeText( userElement );
	}
}

UserCount.prototype.resolveBlock = function( root )
{
	if ( this.xpath )
	{
		var node = root.ownerDocument.evaluate( this.xpath, root, null, XPathResult.ANY_TYPE, null );
		return node.iterateNext();
	}
}
