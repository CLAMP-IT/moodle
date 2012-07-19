/*
 * annotation.js
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
 * $Id: annotation.js 537 2012-05-30 21:51:38Z geof.glass $
 */
 
// namespaces
NS_PTR = 'http://www.geof.net/code/annotation/';
NS_ATOM = 'http://www.w3.org/2005/Atom';
NS_XHTML = 'http://www.w3.org/1999/xhtml';

/* ************************ Annotation Class ************************ */
/*
 * This is a data-only class with (almost) no methods.  This is because all annotation
 * function either affect the display or hit the server, so more properly belong
 * to AnnotationService or PostMicro.
 * An annotation is based on a selection range relative to a contentElement.
 * The ID of a new range is 0, as it doesn't yet exist on the server.
 */
function Annotation( params )
{
	if ( ! params )
		params = new Object( );
	
	// Used to track which fields have changed.  Must precede following code (esp. setUrl)
	this.changes = new Object( );
	this.userid = params.userid || null;
	this.userName = params.userName || null
	this.url = params.url || null;
	this.sequenceRange = params.sequenceRange || null;
	this.xpathRange = params.xpathRange || null;
	this.id = params.id || 0;
	this.quote = params.quote || '';
	this.note = params.note || '';
	this.sheet = params.sheet || '';
	this.action = params.action || '';
	this.quote = params.quote || '';
	this.quoteAuthorId = params.quoteAuthorId || '';
	this.quoteAuthorName = params.quoteAuthorName || '';
	this.isLocal = params.isLocal || false;
	// this.editing = null; -- deleted when not needed
	this.link = params.link || '';
	
	this.updated = new Date( );
	this.lastRead = null;
}

/**
 * Test whether any of the material fields (those serialized and stored in the database)
 * have changed
 */
Annotation.prototype.hasChanged = function( field )
{
	if ( field )
		return this.changes[ field ] ? true : false;
	else
	{
		for ( change in this.changes )
			return true;
		return false;
	}
}

Annotation.prototype.resetChanges = function( )
{
	this.changes = new Object( );
}

/* IMHO, getters and setters are usually not worth it for lightweight code as they
 * increase verbosity and harm readability.  In this case, I want to track which
 * fields have changed only the changed fields need be updated on the server - not
 * because that is more efficient (the benefit would be insignificant), but
 * because it makes debugging and tracking changes easier. */
Annotation.prototype.getUrl = function()
{ return this.url ? this.url : ''; }

Annotation.prototype.setUrl = function(url)
{
	if ( this.url != url )
	{
		this.url = url;
		this.changes[ 'url' ] = true;
	}
}

Annotation.prototype.getPreferredRangeType = function( )
{
	if ( this.xpathRange )
		return 'xpath';
	else if ( this.sequenceRange )
		return 'sequence';
	else
		return null;
}

Annotation.prototype.getSequenceRange = function( )
{
	return this.sequenceRange;
}

Annotation.prototype.getXPathRange = function( )
{
	return this.xpathRange;
}

Annotation.prototype.setSequenceRange = function( range )
{
	if ( this.sequenceRange == null && range != null || ! this.sequenceRange.equals( range ) )
	{
		this.sequenceRange = range;
		this.changes[ 'range/sequence' ] = true;
	}
}

Annotation.prototype.setXPathRange = function( range )
{
	if ( this.xpathRange == null && range != null || ! this.xpathRange.equals( range ) )
	{
		this.xpathRange = range;
		this.changes[ 'range/xpath' ] = true;
	}
}

Annotation.prototype.getId = function( )
{ return this.id; }

Annotation.prototype.setId = function( id )
{
	if ( this.id != id )
	{
		this.id = id;
		this.changes[ 'id' ] = true;
	}
}

Annotation.prototype.getUserId = function( )
{ return this.userid ? this.userid : ''; }

Annotation.prototype.setUserId = function( userid )
{
	if ( this.userid != userid )
	{
		this.userid = userid;
		this.changes[ 'userid' ] = true;
	}
}

Annotation.prototype.getUserName = function( )
{ return this.userName ? this.userName : ''; }

Annotation.prototype.setUserName = function( userName )
{
	if ( this.userName != userName )
	{
		this.userName = userName;
		this.changes[ 'userName' ] = true;
	}
}

Annotation.prototype.getNote = function( )
{ return this.note ? this.note : ''; }

Annotation.prototype.setNote = function( note )
{
	if ( this.note != note )
	{
		this.note = note;
		this.changes[ 'note' ] = true;
	}
}

Annotation.prototype.getQuote = function( )
{ return this.quote ? this.quote : ''; }

Annotation.prototype.setQuote = function( quote )
{
	if ( this.quote != quote )
	{
		this.quote = quote;
		this.changes[ 'quote' ] = true;
	}
}

Annotation.prototype.getSheet = function( )
{ return this.sheet ? this.sheet : ''; }

Annotation.prototype.setSheet = function( sheet )
{
	if ( this.sheet != sheet )
	{
		this.sheet = sheet;
		this.changes[ 'sheet' ] = true;
	}
}

Annotation.prototype.getAction = function( )
{ return this.action ? this.action : ''; }

Annotation.prototype.setAction = function( action )
{
	if ( this.action != action )
	{
		this.action = action;
		this.changes[ 'action' ] = true;
	}
}
 
Annotation.prototype.getLink = function( )
{ return this.link ? this.link : ''; }

Annotation.prototype.setLink = function( link )
{
	if ( this.link != link )
	{
		this.link = link;
		this.changes[ 'link' ] = true;
	}
}

Annotation.prototype.getLinkTitle = function( )
{ return this.linkTitle ? this.linkTitle : ''; }

Annotation.prototype.setLinkTitle = function( title )
{
	if ( this.linkTitle != title )
	{
		this.linkTitle = title;
		this.changes[ 'linkTitle' ] = true;
	}
}

Annotation.prototype.getQuoteAuthorId = function( )
{ return this.quoteAuthorId ? this.quoteAuthorId : ''; }

Annotation.prototype.setQuoteAuthorId = function( authorId )
{
	if ( this.quoteAuthorId != authorId )
	{
		this.quoteAuthorId = authorId;
		this.changes[ 'quoteAuthorId' ] = true;
	}
}

Annotation.prototype.getQuoteAuthorName = function( )
{ return this.quoteAuthorName ? this.quoteAuthorName : ''; }

Annotation.prototype.setQuoteAuthorName = function( authorName )
{
	if ( this.quoteAuthorName != authorName )
	{
		this.quoteAuthorName = authorName;
		this.changes[ 'quoteAuthorName' ] = true;
	}
}

Annotation.prototype.getQuoteTitle = function( )
{ return this.quoteTitle ? this.quoteTitle : ''; }

Annotation.prototype.setQuoteTitle = function( title )
{
	if ( this.quoteTitle != title )
	{
		this.quoteTitle = title;
		this.changes[ 'quoteTitle' ] = true;
	}
}

Annotation.prototype.getUpdated = function( )
{  return this.updated;  }

Annotation.prototype.getLastRead = function( )
{ return this.lastRead; }

Annotation.prototype.setLastRead = function( lastRead )
{
	if ( this.lastRead != lastRead )
	{
		this.lastRead = lastRead;
		this.changes[ 'lastRead' ] = true;
	}
}

Annotation.prototype.isRecent = function( )
{
	var r;
	if ( this.getLastRead( ) )
		r = this.getUpdated( ).getTime( ) > this.getLastRead( ).getTime( );
	else
		r = true;
	return r;
}

Annotation.prototype.fieldsFromPost = function( post )
{
	this.setQuoteAuthorId( post.getAuthorId( ) );
	this.setQuoteAuthorName( post.getAuthorName( ) );
	this.setQuoteTitle( post.getTitle( ) );
}	


Annotation.prototype.compareRange = function( a2 )
{
	if ( this.sequenceRange && a2.sequenceRange )
		return this.sequenceRange.compare( a2.sequenceRange );
	else if ( this.sequenceRange )
		return -1;
	else if ( a2.sequenceRange )
		return 1;
	else
		return 0;
}

function compareAnnotationRanges( a1, a2 )
{
	var r1 = a1.sequenceRange;
	var r2 = a2.sequenceRange;
	// Note: don't use getters for efficiency.
	if ( r1 && r2 )
		return r1.compare( r2 );
	else
	{
		// Shouldn't happen, but bad data can produce this problem
		if ( r1 )
			return -1;
		else if ( r2 )
			return 1;
		else
			return 0;
	}
}

/* Does anything actually call this anymore? */
function annotationFromTextRange( marginalia, post, textRange )
{
	var range = WordRange.fromTextRange( textRange, post.getContentElement( ), marginalia.skipContent );
	if ( null == range )
		return null;  // The range is probably invalid (e.g. whitespace only)
	var annotation = new Annotation ( {
		sheet: marginalia.defaultSheet,
		url:  post.getUrl( ),
		sequenceRange:  textRange.toSequenceRange( ),
		xpathRange:  textRange.toXPathRange( ),
		quote:  getTextRangeContent( textRange, marginalia.skipContent )
	} );
	return annotation;
}

/**
 * Destructor to prevent IE memory leaks
 */
Annotation.prototype.destruct = function( )
{
	this.sequenceRange = null;
	this.xpathRange = null;
}

/**
 * Handy representation for debugging
 */
Annotation.prototype.toString = function( )
{
	// Don't use getters for efficiency
	if ( this.xpathRange )
		return this.xpathRange.toString( );
	else
		return this.sequenceRange.toString( );
}

/**
 * Figure out whether note editing should be in keywords or freeform mode
 * If the note text is a keyword, default to keywords.  Otherwise, check
 * preferences.
 */
Annotation.prototype.defaultNoteEditMode = function( preferences, keywordService )
{
	return Marginalia.EDIT_NOTE_FREEFORM;
}


Annotation.idFromUrl = function( url )
{
	if ( matches = url.match( /\/(\w+)[^\/]*$/ ) )
		return matches[ 1 ];
	else if ( matches = url.match( /\/(\w+)\/[^\/]*$/ ) )
		return matches[ 1 ];
}

Annotation.prototype.fromAtom = function( entry )
{
	var hOffset, hLength, text, url, id;
	var rangeStr = null;
	for ( var field = entry.firstChild;  field != null;  field = field.nextSibling )
	{
		if ( ELEMENT_NODE == field.nodeType )
		{
			if ( field.namespaceURI == NS_ATOM && domutil.getLocalName( field ) == 'content' )
			{
				if ( 'xhtml' == field.getAttribute( 'type' ) )
				{
					// Find the enclosed div
					var child;
					for ( child = field.firstChild;  child;  child = child.nextSibling )
						if ( child.namespaceURI == NS_XHTML && child.nodeName == 'div' && domutil.hasClass( child, 'annotation' ) )
							break;
					if ( child )
						this.fromAtomContent( child );	
				}
			}
			else if ( field.namespaceURI == NS_ATOM && domutil.getLocalName( field ) == 'link' )
			{
				var rel = field.getAttribute( 'rel' );
				var href = field.getAttribute( 'href' );
				// What is the role of this link element?  (there are several links in each entry)
				if ( 'self' == rel )
					this.id = Annotation.idFromUrl( href );
				else if ( 'related' == rel )
					this.link = href;
				else if ( 'alternate' == rel )
					this.url = href;
			}
			else if ( NS_ATOM == field.namespaceURI && 'author' == domutil.getLocalName( field ) )
			{
				for ( var nameElement = field.firstChild;  null != nameElement;  nameElement = nameElement.nextSibling )
				{
					if ( NS_ATOM == nameElement.namespaceURI && 'name' == domutil.getLocalName( nameElement ) )
						this.userName = nameElement.firstChild ? nameElement.firstChild.nodeValue : null;
					else if ( NS_PTR == nameElement.namespaceURI && 'userid' == domutil.getLocalName( nameElement ) )
						this.userid = nameElement.firstChild ? nameElement.firstChild.nodeValue : null;
				}
			}
			else if ( field.namespaceURI == NS_PTR && domutil.getLocalName( field ) == 'range' )
			{
				var format = field.getAttribute( 'format' );
				// These ranges may throw parse errors
				if ( 'sequence' == format )
					this.setSequenceRange( SequenceRange.fromString( domutil.getNodeText( field ) ) );
				else if ( 'xpath' == format )
					this.setXPathRange( XPathRange.fromString( domutil.getNodeText( field ) ) );
				// ignore unknown formats
			}
			else if ( field.namespaceURI == NS_PTR && domutil.getLocalName( field ) == 'sheet' )
				this.sheet = null == field.firstChild ? 'private' : domutil.getNodeText( field );
			else if ( field.namespaceURI == NS_PTR && domutil.getLocalName( field ) == 'action' )
				this.action = null == field.firstChild ? '' : domutil.getNodeText( field );
			else if ( field.namespaceURI == NS_ATOM && domutil.getLocalName( field ) == 'updated' )
				this.updated = domutil.parseIsoDate( domutil.getNodeText( field ) );
			else if ( field.namespaceURI == NS_PTR && domutil.getLocalName( field ) == 'lastread' )
				this.lastRead = domutil.parseIsoDate( domutil.getNodeText( field ) );
		}
	}
	// This is here because annotations are only parsed from XML when being initialized.
	// In future who knows, this might not be the case - and the reset would have to
	// be moved elsewhere.
	this.resetChanges( );	
}

/**
 * Pull annotation fields from the content area of the Atom entry
 * Now this is truly tedious code.  Some good DOM parsing routines would help a lot.
 * But for now I just want to get it working.
 */
Annotation.prototype.fromAtomContent = function( parent, mode )
{
	var pquote = domutil.childByTagClass( parent, 'p', 'quote' );
	var quote = domutil.childByTagClass( pquote, 'q' );
	this.quote = quote ? domutil.getNodeText( quote ) : '';
	
	var cite = domutil.childByTagClass( pquote, 'cite' );
	var a = domutil.childByTagClass( cite, 'a' );
	this.url = a ? a.getAttribute( 'href' ) : '';
	this.quoteTitle = a ? domutil.getNodeText( a ) : '';
	
	var quoteAuthor = domutil.childByTagClass( pquote, null, 'quoteAuthor' );
	this.quoteAuthorName = quoteAuthor ? domutil.getNodeText( quoteAuthor ) : null;
	this.quoteAuthorId = quoteAuthor ? quoteAuthor.getAttribute( 'title' ) : null;
	
	var note = domutil.childByTagClass( parent, 'p', 'note' );
	this.note = note ? domutil.getNodeText( note ) : '';
	
	if ( domutil.childByTagClass( quote, 'del' ) || domutil.childByTagClass( quote, 'ins' ) )
		this.action = 'edit';
	var link = domutil.childByTagClass( parent, 'p', 'see-also' );
	if ( link )
	{
		var a = domutil.childByTagClass( link, 'a' );
		this.link = a ? a.getAttribute( 'href' ) : null;
		var cite = domutil.childByTagClass( link, 'cite' );
		this.linkTitle = cite ? domutil.getNodeText( cite ) : '';
	}
	
	/* cssQuery doesn't work for XML documents in IE (argh):
	this.quote = this.atomContentText( parent, 'p.quote q' );
	this.url = this.atomContentAttrib( parent, 'p.quote cite a', 'href' );
	this.quoteTitle = this.atomContentText( parent, 'p.quote cite a' );
	this.quoteAuthor = this.atomContentText( parent, 'p.quote .quoteAuthor' );
	this.note = this.atomContentText( parent, 'p.note' );
	if ( cssQuery( 'p.quote del', parent ).length > 0 || cssQuery( 'p.note ins', parent ).length > 0 )
		this.action = 'edit';
	var link = cssQuery( 'p.see-also', parent );
	if ( link.length > 0 )
	{
		link = link[ 0 ];
		this.link = cssQuery( 'a', link )[0].getAttribute( 'href' );
		this.linkTitle = this.atomContentText( link, 'cite' );
	}
	*/
}

Annotation.prototype.atomContentText = function( parent, css )
{
	var node = jQuery( css, parent );
	if ( node && node.length > 0 )
	{
		var s = domutil.getNodeText( node[0] );
		if ( s )
			return domutil.trim( s );
	}
	return '';
}

Annotation.prototype.atomContentAttrib = function( parent, css, attrib )
{
	var node = jQuery( css, parent );
	if ( node && node.length > 0 )
		return node[0].getAttribute( attrib );
	trace( null, 'CSS (' + css + ') did not resolve for "' + attrib + '" in ' + parent.innerHtml );
	return '';
}

/**
 * Parse Atom containing annotation info and return an array of annotation objects
 */
function parseAnnotationXml( xmlDoc )
{
	var annotations = new Array( );
	
	if ( !xmlDoc || xmlDoc.documentElement.tagName == "error" )
	{
		if ( xmlDoc )
			logError( "parseAnnotationXML Error: " + xmlDoc.documentElement.textValue() );
		alert( getLocalized( 'corrupt XML from service' ) );
		return null;
	}
	else
	{
		for ( var i = 0;  i < xmlDoc.documentElement.childNodes.length;  ++i ) {
			child = xmlDoc.documentElement.childNodes[ i ];
			// obliged to use tagName here rather than localName due to IE
			if ( child.namespaceURI == NS_ATOM && domutil.getLocalName( child ) == 'entry' )
			{
				// An exception may be thrown if there's a format error, in which case we
				// don't want to parse or list the annotation - if we did, it might cause
				// the whole application to fail, making it impossible to view other annotations
				// or to fix the problem (e.g. through Marginalia Direct).
//				try
//				{
					var annotation = new Annotation( );
					annotation.fromAtom( child );
					annotations[ annotations.length ] = annotation;
/*				}
				catch ( exception )
				{
					logError( "Annotation parse error:  " + exception );
				}
*/			}
		}
		annotations.sort( compareAnnotationRanges );
		return annotations;
	}
}
