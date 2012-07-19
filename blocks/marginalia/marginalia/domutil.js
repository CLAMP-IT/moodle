/*
 * domutil.js
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
 * $Id: domutil.js 556 2012-06-26 06:01:22Z geof.glass $
 */

	
// DOM constants
ELEMENT_NODE = 1;
TEXT_NODE = 3;
CDATA_SECTION_NODE = 4;
DOCUMENT_NODE = 9;

domutil = {

/**
 * Returns True if this is an xhtml document (and therefore uses the XHTML namespace)
 */
isXhtml: function( doc )
{
	if ( doc.contentType )
		return /xml/i.test( doc.contentType );                
	else
		return doc.documentElement.tagName != "HTML";
},

// Hack to support XML for now
nsPrefixResolver: function( prefix )
{
	if ( 'html' == prefix )
		return "http://www.w3.org/1999/xhtml";
	return null;
},

instanceOf: function( obj, type )
{
	return type.prototype.isPrototypeOf( obj );
},


parseIsoDate: function( s )
{
	var matches = s.match( /(\d{4})-?(\d{2})-?(\d{2})T(\d{2}):?(\d{2}):?(\d{2})([+-]\d{2}:?\d{2})/ );
	if ( null == matches )
		return null;
	else
	{
		// I haven't figured out how to deal with the time zone, so it assumes that server
		// time and local time are the same - which is rather bad.
		return new Date( matches[1], matches[2]-1, matches[3], matches[4], matches[5] );
	}
},

formatIsoDate: function( d )
{
	return d.getFullYear( ) + '-' + ( d.getMonth( ) + 1 ) + '-' + d.getDate( ) + 'T'
		+ d.getHours( ) + ':' + d.getMinutes( ) + ':' + d.getSeconds( );
},

htmlEncode: function( s )
{
	s = s.replace( /&/, '&amp;' );
	s = s.replace( /</, '&lt;' );
	s = s.replace( />/, '&gt;' );
	s = s.replace( /"/, '&quot;' );
	return s;
},

// Cookies
// Based on code from Peter-Paul Koch's Quirksmode at
// http://www.quirksmode.org/js/cookies.html

createCookie: function( name, value, days, hours, minutes, milliseconds )
{
	if ( days || hours || minutes || minutes || milliseconds )
	{
		var date = new Date();
		var expireDate = date.getTime( )
			+ ( days ? days * 24 * 60 * 60 * 1000 : 0 )
			+ ( hours ? hours * 60 * 60 + 1000 : 0 )
			+ ( minutes ? minutes * 60 * 1000 : 0 )
			+ ( milliseconds ? milliseconds * 1000 : 0 );
		date.setTime( expireDate );
		var expires = "; expires=" + date.toGMTString( );
	}
	else
		var expires = "";
	// Espace semicolons (Used to separate cookie records)
	if ( value && 'string' == typeof value )
	{
		value = value.replace( '$', '$S' );
		value = value.replace( ';', '$:' );
	}
	document.cookie = name + "=" + value + expires + "; path=/";
},

readCookie: function( name )
{
	var nameEQ = name + "=";
	var ca = document.cookie.split( ';' );
	for( var i = 0;  i < ca.length;  i++)
	{
		var c = ca[ i ];
		while ( c.charAt( 0 ) == ' ')
			c = c.substring( 1, c.length );
		if ( c.indexOf( nameEQ ) == 0)
		{
			var value = c.substring( nameEQ.length, c.length );
			value = value.replace( '$:', ';' );
			value = value.replace( '$S', '$' );
			return value;
		}
	}
	return null;
},

removeCookie: function( name )
{
	createCookie( name, "", -1);
},


/**
 * Read all cookies with a given name prefix
 * returns hash on names
 * Based on the readCookie code at quirksmode.org
 */
readCookiePrefix: function( prefix )
{
	var result = [ ];
	var cookies = document.cookie.split( /;/ );
	for ( var i = 0;  i < cookies.length;  ++i )
	{
		// Strip leading whitespace
		var c = cookies[ i ];
		while ( ' ' == c.charAt( 0 ) )
			c = c.substring( 1, c.length );
		
		var parts0 = c.split( /=/ );
			
		// Check for prefix match
		if ( 0 == c.indexOf( prefix ) )
		{
			var equals = c.indexOf( '=' );
			if ( equals )
			{
				var name = c.substring( 0, equals );
				var value = c.substring( equals + 1 );
				value = value.replace( '$:', ';' );
				value = value.replace( '$S', '$' );
				result[ result.length ] = {
					name: name,
					value: value 
				};
			}
		}
	}
	return result;
},

// Stylesheets

loadStylsheet: function( url )
{
	var node = domutil.element( 'link', {
		rel: 'stylesheet',
		type: 'text/css',
		href: url
	} );
	var head = document.getElementsByTagName( 'head' )[ 0 ];
	head.appendChild( node );
},

// W3C/IE event handling:

/** Get an event */
getEvent: function( event )
{
	return event ? event : window.event;
},

/** Get an event target.  Because IE sucks */
getEventTarget: function( event )
{
	return event.target ? event.target : event.srcElement;
},

/** Stop an event from bubbling */
stopPropagation: function( event )
{
	if ( event.stopPropagation )
		event.stopPropagation();		// W3C
	else
		event.cancelBubble = true;	// IE
},


/* ******** Display Model Functions ******** */

/*
 * Return the display model (inline, block, none, or unknown) for an HTML element
 * (note that table-row, table-cell, and similar display models are currently treated
 * as 'block')
 */
htmlDisplayModel: function( tagName )
{
	var model = HTML_CONTENT_MODEL[ tagName.toLowerCase() ];
	if ( null != model )
		return model[ 'model' ];
	return 'unknown';
},

/**
 * Given an HTML string, return only the contents of the <body> element
 * Requires that <body> and </body> not occur anywhere else in the document
 * (are rare occurrence, but it could happen - so this function should only
 * be used for documents that you generate).
 */
extractBody: function( html )
{
	return html.replace( /<body>(.*)<\/body>/i, '$1' );
},
 
/**
 * Determine whether a given HTML element is block-level (as opposed to inline,
 * although there are some elements, such as script, that are neither)
 */
isBlockElement: function( tagName )
{
	return 'block' == domutil.htmlDisplayModel( tagName );
},

/*
 * Determine whether a given HTML element is breaking - i.e., whether the element boundary
 * effectively adds whitespace.  All block-level elements are breaking.  So is <br>.
 */
isBreakingElement: function( tagName )
{
	return 'block' == domutil.htmlDisplayModel( tagName ) || 'br' == tagName.toLowerCase( );
},

/*
 * Is an HTML element valid within a specified element?
 * The table used here is generated from the HTML DTD by a Perl script.
 * Not all entities are not fully expanded (although the entity definitions are)
 * This keeps the size of that definition file down, which makes it smaller to download.
 */
isValidHtmlContent: function( parentName, childName )
{
	childName = childName.toLowerCase( );
	var definitions = HTML_CONTENT_MODEL;
	var model = definitions[ parentName.toLowerCase() ];
	if ( null == model )
		return false;
	var content = model.content;
	if ( content[ childName ] )
		return true;
	else
	{
		for ( var child in content )
		{
			if ( child.charAt( 0 ) == '%' && definitions[ child ].content[ childName ] )
				return true;
		}
	}
	return false;
},

/* ******** HTML Class Maniuplation ******** */
/**
 * Test whether a node has a certain class value
 */
hasClass: function( element, className )
{
	if ( null == className )
		return false;
	// Check both className and the class attribute (just in case)
	var classNames;
	if ( element.className )
		classNames = element.className.split( ' ' );
	else if ( element.getAttribute( 'class' ) )
		classNames = element.getAttribute( 'class' ).split( ' ' );
	else
		return false;
	for ( var i = 0;  i < classNames.length;  ++i )
	{
		if ( classNames[ i ] == className )
			return true;
	}
	return false;
},

/**
 * Convenience function to test whether a node matches a tag name and a class name
 * Tag names all converted to lower case before comparison
 */
matchTagClass: function( node, tagName, className )
{
	return ( null == tagName || node.tagName.toUpperCase( ) == tagName.toUpperCase( ) )
		&& ( null == className || domutil.hasClass( node, className ) );
},

/*
 * Remove a class from the set of class names on an element
 */
removeClass: function( element, name )
{
	if ( ! element )
		element.className = element.className;		// dummy line for placing breakpoints
	if ( null == element.className )
		return;
	var classNames = element.className.split( ' ' );
	var newClassNames = "";
	for ( var i = 0;  i < classNames.length;  ++i )
	{
		if ( classNames[ i ] != name )
			newClassNames += ' ' + classNames[ i ];
	}
	if ( newClassNames.charAt( 0 ) == ' ' )
		newClassNames = newClassNames.substring( 1 );
	element.className = newClassNames;
},

/*
 * Add a class from the set of class names on an element (or do nothing if already present)
 */
addClass: function( element, name )
{
	if ( null == element )
		logError( "null element in addClass()" );
	else if ( null == element.className )
		element.className = name;
	else
	{
		var classNames = element.className.split( ' ' );
		var newClassNames = "";
		for ( var i = 0;  i < classNames.length;  ++i )
			if ( classNames[ i ] == name )
				return;
		element.className += ' ' + name;
	}
},

/*
 * Ensure a class value is present/absent
 */
setClass: function( element, name, flag )
{
	if ( flag )
		domutil.addClass( element, name );
	else
		domutil.removeClass( element, name );
},


/* ******** Find DOM Nodes ******** */

/*
 * Fetch the local name for a node (i.e., without any namespace qualifier)
 */
getLocalName: function( element )
{
	if ( element.localName )
		return element.localName;	// W3C implementation
	else if ( element.baseName )
		return element.baseName;	// IE implementation
	else
		return element.tagName;	// IE is somewhat inconsistent
},

/*
 * I considered jQuery instead of getChildByTagClass etc., but it has several weaknesses:
 * - in many cases I need to exclude subtrees using fskip
 * - CSS queries only look down the tree, not up or at siblings
 * CSS (or XPath) could be used by filtering out elements that are ancestors of a filtered
 * node.  But then why waste the time scanning what could be a long document?  (Especially when
 * Marginalia already has performance problems).
 */

 
/**
 * Apply a CSS selector using an implementation from jQuery
 * here because originally domutil was not dependent on jQuery
 */
select: function( selector, root, first )
{
	var results = jQuery( selector, root );
	if ( first )
		return results && results.length ? results[ 0 ] : null;
	else
		return results;
},

/**
 * Fetch the first child with a given class attribute value
 */
childByTagClass: function( node, tagName, className, fskip )
{
	if ( node == null )
		alert( "node not found tag=" + tagName + ", class=" + className );
	if ( node.nodeType == ELEMENT_NODE && ( ! fskip || ! fskip( node ) ) )
	{
		if ( null == tagName || tagName.toUpperCase( ) == node.tagName.toUpperCase( ) )
		{
			if ( null == className )
				return node;
			// for XML compatibility:
			var classNames = node.className ? node.className : node.getAttribute( 'class' );
			if ( classNames )
			{
				classNames = classNames.split( ' ' );
				for ( var i = 0;  i < classNames.length;  ++i )
				{
					if ( classNames[ i ] == className )
						return node;
				}
			}
		}
		if ( null != node.childNodes )
		{
			for ( var i = 0;  i < node.childNodes.length;  ++i )
			{
				var child = domutil.childByTagClass( node.childNodes[ i ], tagName, className, fskip );
				if ( child != null )
					return child;
			}
		}
	}
	return null;
},

/**
 * Get a child anchor (<a>) element by rel value
 */
childAnchor: function( node, relName, fskip )
{
	if ( node == null )
		alert( "node not found tag=" + tagName + " (looking for rel=" + relName + ")");
	if ( node.nodeType == ELEMENT_NODE && ( ! fskip || ! fskip( node ) ) )
	{
		if ( 'A' == node.tagName.toUpperCase() || 'LINK' == node.tagName.toUpperCase() )
		{
			var rel = node.getAttribute( 'rel' );
			if ( null != rel )
			{
				var relNames = rel.split( ' ' );
				for ( var i = 0;  i < relNames.length;  ++i )
				{
					if ( relNames[ i ] == relName )
						return node;
				}
			}
			//trace( null, 'rel=' + node.rel +' , href=' + node.getAttribute( 'rel' ) );
		}
		if ( null != node.childNodes )
		{
			for ( var i = 0;  i < node.childNodes.length;  ++i )
			{
				var child = domutil.childAnchor( node.childNodes[ i ], relName, fskip );
				if ( child != null )
					return child;
			}
		}
	}
	return null;
},

childrenByTagClass: function( node, tagName, className, matches, fskip )
{
	if ( null == matches )
		matches = new Array( );
	if ( node == null )
		alert( "node not found tag=" + tagName + ", class=" + className );
	if ( node.nodeType == ELEMENT_NODE && ( ! fskip || ! fskip( node ) ) )
	{
		if ( null == tagName || tagName.toUpperCase( ) == node.tagName.toUpperCase( ) )
		{
			if ( null == className )
				matches[ matches.length ] = node;
			else if ( null != node.className )
			{
				var classNames = node.className.split( ' ' );
				for ( var i = 0;  i < classNames.length;  ++i )
				{
					if ( classNames[ i ] == className )
						matches[ matches.length ] = node;
				}
			}
		}
		for ( var i = 0;  i < node.childNodes.length;  ++i )
			domutil.childrenByTagClass( node.childNodes[ i ], tagName, className, matches, fskip );
	}
	return matches;
},

/*
 * Get the value of a named field in the first ancestor with that field
 */
nestedFieldValue: function( node, field )
{
	while ( null != node && ! node[ field ] )
		node = node.parentNode;
	return null == node ? null : node[ field ];
},

/*
 * Fetch the first parent with a given class attribute value
 * this should be fixed because there can be multiple class names listed in the class attribute
 * The fskip value works a little differently here.  This function guarantees that the result
 * node is not within an element for which fskip is true.
 */
parentByTagClass: function( theNode, tagName, className, topDown, fskip )
{
	var topResult = null;
	var bottomResult = null;
	for ( var node = theNode;  node != null && DOCUMENT_NODE != node.nodeType;  node = node.parentNode )
	{
		if ( fskip && fskip( node ) )
			topResult = bottomResult = null;
		else if ( ELEMENT_NODE == node.nodeType && ( null == tagName || tagName.toUpperCase( ) == node.tagName.toUpperCase( ) ) )
		{
			if ( null == className )
			{
				topResult = node;
				if ( null == bottomResult )
					bottomResult = node;
			}
			else
			{
				var classNames = node.className.split( ' ' );
				for ( var i = 0;  i < classNames.length;  ++i )
				{
					if ( classNames[ i ] == className )
					{
						topResult = node;
						if ( null == bottomResult )
							bottomResult = node;
					}
				}
			}
		}
	}
	return topDown ? topResult : bottomResult;
},

/**
 * Get the next sibling element matchin ga tag and/or class name value
 */
nextByTagClass: function( theNode, tagName, className, fskip )
{
	var prev = null;
	for ( next = theNode.nextSibling;  next;  next = next.nextSibling )
	{
		if ( ! fskip || ! fskip( next ) )
		{
			if ( ELEMENT_NODE == next.nodeType
				&& ( ! tagName || tagName.toLowerCase( ) == next.tagName.toLowerCase( ) )
				&& ( ! className || domutil.hasClass( next, className ) ) )
			{
				break;
			}
		}
	}
	return next;
},

/**
 * Get the previous sibling element matching a tag and/or class name value
 */
prevByTagClass: function( theNode, tagName, className, fskip )
{
	var prev = null;
	for ( prev = theNode.previousSibling;  prev;  prev = prev.previousSibling )
	{
		if ( ! fskip || ! fskip( prev ) )
		{
			if ( ELEMENT_NODE == prev.nodeType
				&& ( ! tagName || tagName.toLowerCase( ) == prev.tagName.toLowerCase( ) )
				&& ( ! className || domutil.hasClass( prev, className ) ) )
			{
				break;
			}
		}
	}
	return prev;
},

/*
 * Check whether a node is a descendant of another node
 */
isElementDescendant: function( element, container )
{
	var parent = element;
	while ( parent != container && null != parent )
		parent = parent.parentNode;
	return parent == null ? false : true;
},

/*
 * Get the first ancestor of the passed node that is a block-level element, or
 * the root element - which ever is lower down the tree.
 */
getBlockParent: function( node, root )
{
	while ( root != node && null != node)
	{
		if ( ELEMENT_NODE == node.nodeType && 'block' == domutil.htmlDisplayModel( node.tagName ) )
			return node;
		node = node.parentNode;
	}
	return root;
},

/**
 * Find the closest preceding element matching some characteristic
 */
closestPrecedingMatchingElement: function( node, f )
{
	var walker = new DOMWalker( node );
	while ( walker.walk( true, true ) )
	{
		if ( ELEMENT_NODE == walker.node.nodeType && f( walker.node, walker.startTag ) )
		{
			node = walker.node;
			walker.destroy();
			return node;
		}
	}
	walker.destroy( );
	return null;
},

// Should be a jQuery function, but right now I just want to make it work
closestPrecedingFlat: function( rel, selector, orself )
{
	if ( orself && $( rel ).is( selector ) )
		return rel;
	else
	{
		return domutil.closestPrecedingMatchingElement( rel, function( node, tag ) {
			return $( node ).is( selector ); } );
	}
},

/*
 * Find the start of the closest preceding breaking element *in document order*
 * This is not the same as the closest preceding element at the same depth as the passed element
 * E.g., for <a> <b>...</b> </a> <rel/>, the closest preceding element for rel is b - not a
 */
closestPrecedingBreakingElement: function( rel, orself )
{
	if ( orself && ELEMENT_NODE == rel.nodeType && domutil.isBreakingElement( rel.tagName ) )
		return rel;
	return domutil.closestPrecedingMatchingElement( rel, function( node, isStartTag ) {
		return isStartTag && domutil.isBreakingElement( node.tagName ); } );
},

/**
 * Find the start of the closest preceding block-level element in document order
 */
closestPrecedingBlockElement: function( rel, orself )
{
	if ( orself && ELEMENT_NODE == rel.nodeType && domutil.isBreakingElement( rel.tagName ) )
		return rel;
	return domutil.closestPrecedingMatchingElement( rel, function( node, isStartTag ) {
		return isStartTag && domutil.isBlockElement( node.tagName ); } );
},

blockPathToNode: function( root, path, fskip )
{
	var node;
	// Locate the rel node based on the path
	// The simple case:  rel is root
	if ( '' == path )
		node = root;
	else
	{
		/* This will be slow because it's a linear search.  
		/* It would be well worth optimizing this by caching a list of jump points,
		 * or adding a breaknum attribute usable by xpath (e.g. /*[@breaknum=4]) */
		node = root;
		nodes = path.split( '.' );
		for ( var i = 0;  i < nodes.length;  ++i )
		{
			var count = Number( nodes[ i ] );
			for ( node = node.firstChild;  null != node;  node = node.nextSibling )
			{
				if ( ! fskip || ! fskip( node ) )
				{
					if ( ELEMENT_NODE == node.nodeType && domutil.isBlockElement( node.tagName ) )
					{
						count -= 1;
						if ( 0 == count )
							break;
					}
				}
			}
			if ( 0 != count )
				return null;
		}
	}
	return node;
},


/* ********** Dom Text Functions ********** */

/*
 * Calculate the number of characters of text in a node
 * Does this work correctly with variable-length unicode characters?
 * Any elements with a class of skipClass are ignored
 */
nodeTextLength: function( node, skipClass )
{
	// Base case
	if ( TEXT_NODE == node.nodeType || CDATA_SECTION_NODE == node.nodeType )
		return node.length;
	// Recurse
	else if ( ELEMENT_NODE == node.nodeType && ( null == skipClass || ! domutil.hasClass( node, skipClass ) ) )
	{
		var n = 0;
		for ( var i = 0;  i < node.childNodes.length;  ++i )
			n += domutil.nodeTextLength( node.childNodes[ i ] );
		return n;
	}
	else
		return 0;
},

/*
 * Uh-oh, there's a problem here:  block-level elements separate words, but
 * inline ones may not (e.g. there may be italics in the middle of a word).
 * Hmmm.
 */
nodeWordCount: function( node, fskip )
{
	// Base case
	if ( TEXT_NODE == node.nodeType || CDATA_SECTION_NODE == node.nodeType )
	{
		// collapse and trim spaces
		var s = node.nodeValue.replace( /\s+/g, ' ' );
		s = s.replace( '^\s+', '' );
		s = s.replace( '\s+$', '' );
		
	}
	// Recurse
	else if ( ELEMENT_NODE == node.nodeType && ( null == fskip || ! fskip( node ) ) )
	{
		var n = 0;
		for ( var child = node.firstChild;  null != child;  child = child.nextSibling )
			n += domutil.nodeWordCount( child );
		return n;
	}
	else
		return 0;
},

/*
 * Return the text contained within a node, with all tags stripped (this is like casting to text in XPath)
 */
getNodeText: function( node, fskip )
{
	if ( fskip )
		return domutil._getNodeText( node, fskip );
	else
		return node.textContent || node.innerText || domutil._getNodeText( node );
},

_getNodeText: function( node, fskip )
{
	if ( node.nodeType == TEXT_NODE || node.nodeType == CDATA_SECTION_NODE )
		return node.nodeValue;
	else if ( node.nodeType == ELEMENT_NODE && ( ! fskip || ! fskip( node ) ) )
	{
		var s = "";
		for ( var i = 0;  i < node.childNodes.length;  ++i )
			s += domutil._getNodeText( node.childNodes[ i ], fskip );
		return s;
	}
	else
		return '';
},

/**
 * Normalize a pair of adjacent text nodes - if that's what they are
 * The passed node will not be removed, but the one that follows it might be.
 * Returns the next node after the passed node.
 */
normalizeNodePair: function( firstNode )
{
	if ( firstNode && TEXT_NODE == firstNode.nodeType )
	{
		var nextNode = firstNode.nextSibling;
		if ( nextNode && TEXT_NODE == nextNode.nodeType )
		{
			firstNode.nodeValue += nextNode.nodeValue;
			nextNode.parentNode.removeChild( nextNode );
		}
	}
},

trim:  function( s )
{
	if ( s )
	{
		s = s.replace( /^\s+/, '' );
		s = s.replace( /\s+$/, '' );
	}
	return s;
},

/* ********** Form Controls ********** */

/* A list of attributes, used by addFields().  Not comprehensive. */
ELEMENT_ATTRIBUTES:  {
	cols: 1,
	'for': 1,
	href: 1,
	name: 1,
	rows: 1,
	selected: 1,
	style: 1,
	title: 1,
	type: 1,
	value: 1
},

EVENTS: {
	change: 1,
	click: 1,
	focus: 1,
	blur: 1
},
	
addFields:  function( node, spec )
{
	for ( var field in spec )
	{
		// Add child node (s)
		if ( 'content' == field )
		{
			domutil.addContent( node, spec, spec.content );
		}
//		else if ( 'id' == field )
//			node.id = spec[ field ];
//		else if ( 'className' == field )
//			node.className = spec[ field ];
		// Is this is a known attribute?
		else if ( domutil.ELEMENT_ATTRIBUTES[ field ] )
			node.setAttribute( field, spec[ field ] );
		// Is this an event?  (must be passed starting with "on")
		else if ( 'on' == field.substr( 0, 2 ) && domutil.EVENTS[ field.substr( 2 ) ] )
			addEvent( node, field.substr( 2 ), spec[ field ] );
		// Is this flagged as an attribute (starts with "attr_')
		else if ( 'attr_' == field.substr( 0, 5 ) )
			node.setAttribute( field.substr( 5 ), spec[ field ] );
		// Assume it's a field to set on the object (includes id, className)
		else
			node[ field ] = spec[ field ];
	}
	return node;
},

addContent: function( node, spec, content )
{
	// Add a text child
	if ( typeof content == 'string' )
		node.appendChild( document.createTextNode( content ) );
	// Add an array of passed node children
	else if ( content.constructor == Array )
	{
		for ( var i = 0;  i < content.length;  ++i )
			node.appendChild( content[ i ] );
	}
	// Add a passed node child
	else
		node.appendChild( content );
},

element: function( name, spec, content )
{
	var node = document.createElement( name );
	if ( spec )
		domutil.addFields( node, spec );
	if ( content )
		domutil.addContent( node, spec, content );
	return node;
},

button: function( spec )
{
	return domutil.element( 'button', spec );
},

/**
 * Given a plain text string, build text nodes and <a> nodes for any contained URLs
 * Appends constructed nodes to the passed node
 */
urlize: function( node )
{
	var child = node.firstChild;
	while ( child )
	{
		if ( TEXT_NODE == child.nodeType || CDATA_SECTION_NODE == child.nodeType )
		{
			var tail = child.nodeValue;
			var next = child.nextSibling;
			
			// Rebuild node content with text and links
			while ( tail.length > 0 )
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
						node.appendChild( document.createTextNode( head ) );
					domain = match[ 1 ];
					//if ( 'www.' == domain.substr( 0, 4 ) )
					//	domain = domain.substr( 4 );
					node.insertBefore( domutil.element( 'a', {
						href: url,
						title: url,
						onclick: domutil.stopPropagation,
						content: domain }), next);
					tail = tail.substr( head.length + url.length );
				}
				else
				{
					node.insertBefore( document.createTextNode( tail ), next );
					break;
				}
			}
			
			// now remove the old text node
			node.removeChild( child );
			child = next;
		}
		else
			child = child.nextSibling;
	}
},


/* ********** Element Position/Dimension Manipulation ********** */

/**
 * Fix the height of an element.  This is necessary for browsers that fail to obey
 * height: 100% correctly (are you listening IE?).
 */
makeFullHeight: function( element )
{
	var height = element.parentNode.offsetHeight;
	element.style.height = element.parentNode.offsetHeight + 'px';
},

/*
 * Return the node offset as a measurement from a parent reference node
 */
getElementYOffset: function( node, parent )
{
	if ( node == null )
		return 0;
	else if ( node.offsetParent == null )
		return domutil.getElementYOffset( node.parentNode, parent );
	else if ( node.offsetParent == parent )
		return node.offsetTop;
	else
		return node.offsetTop + domutil.getElementYOffset( node.offsetParent, parent );
},

getElementXOffset: function( node, parent )
{
	if ( node.offsetParent == parent )
		return node.offsetLeft ;
	else
		return node.offsetLeft + domutil.getElementXOffset( node.offsetParent, parent );
},

/*
 * Get the current window scroll position
 */
getWindowYScroll: function( )
{
	if ( window.pageYOffset )
		return window.pageYOffset;
	else if ( document.documentElement && document.documentElement.scrollTop )
		return document.documentElement.scrollTop;
	else if ( document.body )
		return document.body.scrollTop;
},

getWindowXScroll: function( )
{
	if ( window.pageXOffset )
		return window.pageXOffset;
	else if ( document.documentElement && document.documentElement.scrollLeft )
		return document.documentElement.scrollLeft;
	else if ( document.body )
		return document.body.scrollLeft;
},

SCROLL_POS_TOP: 0,
SCROLL_POS_CENTER: 1,
SCROLL_POS_CENTER_NODE: 2,
SCROLL_POS_UP_NODE: 3,
scrollWindowToNode: function( node, position, params )
{
	if ( null != node )
	{
		var xoffset = domutil.getWindowXScroll( );
		var yoffset = domutil.getElementYOffset( node, node.ownerDocument.documentElement );
		if ( domutil.SCROLL_POS_CENTER == position )
			yoffset -= document.documentElement.clientHeight / 2;
		else if ( domutil.SCROLL_POS_CENTER_NODE == position )
			yoffset -= document.documentElement.clientHeight / 2 - Math.ceil( $( node ).innerHeight( ) / 2 );
		else if ( domutil.SCROLL_POS_UP_NODE == position )
			yoffset -= document.documentElement.clientHeight / 4;
		$( 'body' ).scrollTo( { left:xoffset, top:yoffset}, params );
//		window.scrollTo( xoffset, yoffset );
	}
},


/*
 * Browser independent function to create an XMLHttpRequest ojbect
 */
createAjaxRequest: function( )
{
	var request;
	if ( window.XMLHttpRequest )
		request = new XMLHttpRequest( );  // Gecko / XHTML / WebKit
	else
		request = new ActiveXObject( "Microsoft.XMLHTTP" );  // MS IE
//	request.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
	return request;
},

/*
 * Recursively remove markup tags of a given name and/or class
 * tagName or className can be null to indicate any tag or class
 * Note that this is an HTML implementation:  tag name comparisons are case-insensitive (ack!)
 * Originally written to strip annotation highlights.
 *
 *  node - the node to be recursively stripped
 *  test - call back returns one of 0, STRIP_TAG, or STRIP_CONTENT
 *  doNormalize - if true, normalize this node on completion.  For long documents this can
 *    be *much* faster than normalizing the whole thing
 */
STRIP_NONE: 0,
STRIP_TAG: 1,		// remove the start and end tags, but leave the content in place
STRIP_CONTENT: 2, 	// remove the start and end tags and the content
stripMarkup: function( node, test, doNormalize )
{
	var child = node.firstChild;
	while ( null != child )
	{
		var nextChild = child.nextSibling;
		// only interested in element nodes
		if ( child.nodeType == ELEMENT_NODE )
		{
			var testR = test( child );
			
			// If this and its content are to be stripped, to so immediately - don't bother with children
			if ( domutil.STRIP_CONTENT == testR )
			{
				domutil.clearEventHandlers( child, true );
				var prevNode = child.previousSibling;
				child.parentNode.removeChild( child );
				if ( doNormalize )
				{
					domutil.normalizeNodePair( prevNode );
					nextChild = prevNode.nextSibling;
				}
			}
			// If this is to be unwrapped, first recurse on children
			else if ( domutil.STRIP_TAG == testR )
			{
				domutil.stripMarkup( child, test, doNormalize );
				nextChild = domutil.unwrapElementChildren( child, doNormalize );
			}
			else
				domutil.stripMarkup( child, test, doNormalize );
		}
		child = nextChild;
	}
},

/**
 * Remove an element, replacing it with its children
 * Returns the next element following where the element and its children used to be
 * (might not be what's expected if it was normalized away)
 */
unwrapElementChildren: function( node, doNormalize )
{
	domutil.clearEventHandlers( node, false );
	var next = node.nextSibling;
	var firstChild = node.firstChild;
	// Node has children
	if ( firstChild )
	{
		var lastChild = node.lastChild;
		var child = firstChild;
		while ( child )
		{
			var nextChild = child.nextSibling;
			node.removeChild( child );
			node.parentNode.insertBefore( child, node );
			child = nextChild;
		}
		
		// Normalize by merging first and last children with any adjacent identical text nodes
		if ( doNormalize )
		{
			domutil.normalizeNodePair( lastChild );
			next = lastChild.nextSibling;
			domutil.normalizeNodePair( firstChild.previousSibling );
		}
	}
	// Node has no children, so remove it and normalize surrounding
	else
	{
		node.parentNode.removeChild( node );
		if ( doNormalize && next && next.previousSibling )
			domutil.normalizeNodePair( next.previusSibling );
	}
	return next;
},
	
/*
 * Recursively remove markup tags of a given name and/or class
 * tagName or className can be null to indicate any tag or class
 * Note that this is an HTML implementation:  tag name comparisons are case-insensitive (ack!)
 * Originally written to strip annotation highlights.
 * Doesn't normalize the result.
 */
stripSubtree: function( node, tagName, className )
{
	var child = node.firstChild;
	while ( child != null )
	{
		var nextChild = child.nextSibling;
		// only interested in element nodes
		if ( child.nodeType == ELEMENT_NODE )
		{
			domutil.stripSubtree( child, tagName, className );
			if ( ( tagName == null || child.tagName.toUpperCase( ) == tagName.toUpperCase( ) )
				&& ( className == null || domutil.hasClass( child, className ) ) )
			{
				domutil.clearEventHandlers( child, true );
				node.removeChild( child );
			}
		}
		child = nextChild;
	}
},
 
/*
 * TODO: Change the name so JSUnit doesn't get confused and think it's a unit test
// Test whether a mouseout event really means the mouse left an element
testMouseLeave: function( e, element )
{
	dump( "leave " );
	if ( ! e )
		var e = window.event;
	var target = (window.event) ? e.srcElement : e.target;
	// if (tg.nodeName != 'DIV') return;
	if ( target != element)
	{
		dump( "target (" + target + ") != element (" + element + ")\n" );
		return false;
	}
	var related = (e.relatedTarget) ? e.relatedTarget : e.toElement;
	while ( related != target && null != related )
		related = related.parentNode;
	dump( related == target ? "related == target\n" : "left\n\n" );
	return related != target;
},
*/

/*
 * Normalize an element and its children
 * This applies the DOM normalize function, which combines adjacent text nodes.
 * It also collapses multiple whitespace characters into a single space.  This
 * is necessary because browsers differ on how they calculate string lengths
 * etc.
 *
 * Also, IE has the absolutely horrifying habit of stripping space characters
 * if they a) are the first characters inside an open tag or b) are the first
 * characters following a closing tag.  In other words, "<span> one </span> two"
 * becomes "<span>one </span>two".  But it doesn't do it all the time;  I haven't
 * yet determined the pattern.  Perhaps there's a standard somewhere that says
 * this is correct display behavior (though if you ask me that doesn't excuse
 * lying about the document).  Collapsing multiple spaces makes good sense in the
 * context of HTML, but trying to handle this look to me a whole lot like data
 * corruption.  So explorer's offsets will differ under some rare circumstances.
 * Exploder, may thy days be numbered and may fire be the eternal rest of thy soul.
 */
normalizeSpace: function( node )
{
	domutil.portableNormalize( node );
	
// Don't do this anymore, for two reasons:  first, it modifies the page content
// unnecessarily; second, Gecko considers nbsp to be a space character, while
// IE does not.
//	normalizeSpaceRecurse( node );
},

normalizeSpaceRecurse: function( node )
{
	for (var i = 0; i < node.childNodes.length; i++ )
	{
		var childNode = node.childNodes[ i ];
		if ( TEXT_NODE == childNode.nodeType )
		{
			childNode.nodeValue = childNode.nodeValue.replace( /(\s)\s+/g, '$1' );
			// See comment at the start of this function about why the following
			// perverted logic has been removed.
			//if ( null == childNode.previousSibling || ELEMENT_NODE == childNode.previousSibling.nodeType )
			//	childNode.nodeValue = childNode.nodeValue.replace( /^\s/, '' );
		}
		if ( ELEMENT_NODE == childNode.nodeType )
			domutil.normalizeSpaceRecurse( childNode )
	}
},

/*
 * An implementation of the W3C DOM normalize function for IE
 * IE often crashes when its implementation is called
 * I'm not going to bother implementing CDATA support
 */
portableNormalize: function( node )
{
	// Internet Explorer often crashes when it tries to normalize
	if ( 'exploder' != domutil.detectBrowser( ) )
		node.normalize( );
	else
	{
		if ( ELEMENT_NODE != node.nodeType )
			return;
		var child = node.firstChild;
		var next;
		while ( null != child )
		{
			if ( ELEMENT_NODE == child.nodeType )
			{
				domutil.portableNormalize( child );
				child = child.nextSibling;
			}
			else if ( TEXT_NODE == child.nodeType )
			{
				if ( '' == child.nodeValue )
				{
					next = child.nextSibling; 
					child.parentNode.removeChild( child );
				}
				else
				{
					next = child.nextSibling;
					var s = '';
					while ( null != next && TEXT_NODE == next.nodeType )
					{
						s += next.nodeValue;
						var t = next.nextSibling;
						child.parentNode.removeChild( next );
						next = t;
					}
					if ( '' != s )
					{
						// this means there was more than one text node in sequence
						child.nodeValue = child.nodeValue + s;
					}
				}
				child = next;
			}
			else
				child = child.nextSibling;
		}
	}
},


/*
 * Browser detects are bad.
 * However, this is needed because IE simply can't handle some things (it crashes)
 * The only value I care about is IE, so this function only returns "opera", "exploder", or "other"
 */
detectBrowser: function( )
{
	var agent = navigator.userAgent.toLowerCase()
	if ( -1 != agent.indexOf( 'opera' ) )  return "opera";  // opera masquerades as ie, so check for it first
	else if ( -1 != agent.indexOf( 'msie' ) )  return "exploder";
	else return "other";
},

/*
 * Return the next node after the current one while walking
 * I'm trying to replace this with the DOMWalker
 */
walkNextNode: function( node, fskip )
{
	var next = node;
	do
	{
		if ( ELEMENT_NODE == next.nodeType && next.firstChild && ( null == fskip || ! fskip( next ) ) )
		{
			trace( 'node-walk', 'walkNextNode (' + node + '=' + node.nodeValue + ') child' );
			next = next.firstChild;
		}
		else if ( next.nextSibling )
		{
			trace( 'node-walk', 'walkNextNode (' + node + '=' + node.nodeValue + ') sibling' );
			next = next.nextSibling;
		}
		else
		{
			trace( 'node-walk', 'walkNextNode (' + node + '=' + node.nodeValue + ') parent' );
			next = next.parentNode;
			while ( null != next && null == next.nextSibling )
				next = next.parentNode;
			if ( null != next )
				next = next.nextSibling;
		}
	}
	while ( null != next && fskip && fskip( next ) );
	trace( 'node-walk', "walkNextNode (" + node + "=" + node.nodeValue + " -> " + next + " (" + next.nodeValue + ")");
	return next;
},

/*
 * Walk r forward until len characters have been passed
 * r.container - the current container element
 * r.offset = the offset within the node to start looking
 */
walkUntilLen: function( node, len )
{
	while ( node != null)
	{
		if ( TEXT_NODE == node.nodeType || CDATA_SECTION_NODE == node.nodeType )
		{
			if ( len <= node.length )
			{
//				var parent = node.parentNode;
				var result = new Object();
				result.container = node; //parent;
				result.offset = len;
				// Now figure out where this is in the context of the parent element
//				for ( var child = parent.firstChild;  child != node;  child = child.nextSibling )
//					result.offset += nodeTextLength( child, null );
				return result;
			}
			else
			{
				len -= node.length;
				node = domutil.walkNextNode( node, null );
			}
		}
		else
			node = domutil.walkNextNode( node, null );
	}
	return null;
},

clearEventHandlers: function( element, recurse, childrenOnly )
{
	if ( ! childrenOnly )
	{
		// tests necessary for IE
		if ( element.onmousedown )
			element.onmousedown = null;
		if ( element.onmouseup )
			element.onmouseup = null;
		if ( element.onkeydown )
			element.onkeydown = null;
		if ( element.onkeypress )
			element.onkeypress = null;
		if ( element.onkeyup )
			element.onkeyup = null;
		if ( element.onmouseover )
			element.onmouseover = null;
		if ( element.onmouseout )
			element.onmouseout = null;
		if ( element.onfocus )
			element.onfocus = null;
		if ( element.onblur )
			element.onblur = null;
		if ( element.onclick )
			element.onclick = null;
	}

	if ( recurse )
	{
		for ( var child = element.firstChild;  null != child;  child = child.nextSibling )
		{
			if ( ELEMENT_NODE == child.nodeType )
				domutil.clearEventHandlers( child, true );
		}
	}
},

clearObjectRefs: function( element, fields )
{
	if ( isString( fields ) )
		delete element[ fields ];
	else
	{
		for ( var i = 0;  i < fields.length;  ++i )
			delete element[ fields[ i ] ];
	}
	for ( var child = element.firstChild;  null != child;  child = child.nextSibling )
	{
		if ( ELEMENT_NODE == child.nodeType )
			domutil.clearObjectRefs( child, fields );
	}
},

isString: function( s )
{
	if ( typeof( s ) == 'string' )
		return true;
	else if ( typeof( s ) == 'object' )
		// Based on Kas Thomas's analysis:
		return null != s.constructor.toString( ).match( /string/i );
	else
		return false;
},

// Thanks to Dan Allen (?) at mojavelinux for the hash implementation
// http://www.mojavelinux.com/articles/javascript_hashes.html
Hash: function( )
{
	this.length = 0;
	this.items = new Array();
	for (var i = 0; i < arguments.length; i += 2) {
		if (typeof(arguments[i + 1]) != 'undefined') {
			this.items[arguments[i]] = arguments[i + 1];
			this.length++;
		}
	}
   
	this.removeItem = function(in_key)
	{
		var tmp_previous;
		if (typeof(this.items[in_key]) != 'undefined') {
			this.length--;
			var tmp_previous = this.items[in_key];
			delete this.items[in_key];
		}
	   
		return tmp_previous;
	}

	this.getItem = function(in_key) {
		return this.items[in_key];
	}

	this.setItem = function(in_key, in_value)
	{
		var tmp_previous;
		if (typeof(in_value) != 'undefined') {
			if (typeof(this.items[in_key]) == 'undefined') {
				this.length++;
			}
			else {
				tmp_previous = this.items[in_key];
			}

			this.items[in_key] = in_value;
		}
	   
		return tmp_previous;
	}

	this.hasItem = function(in_key)
	{
		return typeof(this.items[in_key]) != 'undefined';
	}

	this.clear = function()
	{
		for (var i in this.items) {
			delete this.items[i];
		}

		this.length = 0;
	}
}
}


/**
 * include: CSS selector or function determining which nodes to include
 * exclude: CSS selector or function determining which nodes to exclude
 * text: when extracting a value, take this attribute or node text or call this function
 */
function Selector( include, exclude, text )
{
	this.include = include;
	this.exclude = exclude;
	this.text = text;
}

/**
 * Used internally to determine whether a selector is a CSS selector string
 * or a function, call it, and return the result
 */
Selector.prototype.select = function( sel, root )
{
	// If it's not a string, assume it's a function
	return domutil.isString( sel ) ? domutil.select( sel, root ) : sel( root );
}

/**
 * Return all nodes matching the selector
 */
Selector.prototype.nodes = function( root )
{
	if ( this.exclude )
	{
		var result0 = this.select( this.include, root );
		if ( ! result0 )
			return [ ];
		else
		{
			var subtract = this.select( this.exclude, root );
			if ( subtract )
			{
				// This is god-awful inefficient.  I'd like to use a hash,
				// but don't think I can use an object as an array index.
				// Though it isn't as horribly bad as it might be because though
				// it is O(n*m), m is expected to be very small.
				var result = [ ];
				for ( var i = 0;  i < result0.length;  ++i )
				{
					var b = true;
					for ( var j = 0;  j < subtract.length;  ++j )
					{
						if ( subtract[ j ] == result[ i ] )
						{
							b = false;
							break;
						}
					}
					if ( b )
						result.push( result0[ i ] );
				}
				return result;
			}
			else
				return result0;
		}
	}
	else
		return this.select( this.include, root );
}

/**
 * Return the first node matching the selector
 */
Selector.prototype.node = function( root )
{
	var v = this.nodes( root );
	return ( v && v.length ) ? v[ 0 ] : null;
}

/**
 * Return the values of all nodes matching the selector
 */
Selector.prototype.values = function( root )
{
	var nodes = this.nodes( root );
	
	if ( ! nodes || ! nodes.length )
		return [ ];
	
	var results = [ ];
	var resolved = false;
	if ( ! this.text || this.text == 'text()' ) // default, or if ( 'text()' == this.text )
	{
		for ( var i = 0;  i < nodes.length;  ++i )
			results[ i ] = domutil.getNodeText( nodes[ i ] );
	}
	else
	{
		if ( domutil.isString( this.text ) )
		{
			if ( '@' == this.text.charAt( 0 ) && nodes )
			{
				var attribute = this.text.substring( 1 );
				for ( var i = 0;  i < nodes.length;  ++i )
					results[ i ] = nodes[ i ].getAttribute( attribute );
				resolved = true;
			}
			else
				throw 'Bad selector text value ' + this.text;
		}
		else
		{
			for ( i = 0;  i < nodes.length;  ++i )
				results[ i ] = this.text( nodes[ i ] );
			resolved = true;
		}
	}
	
	return results;
}

/**
 * Return the value of the first node matching the selector
 */
Selector.prototype.value = function( root )
{
	var v = this.values( root );
	return ( v && v.length ) ? v[ 0 ]: null;
}


/**
 * Walk through nodes in document order
 */
function DOMWalker( startNode, atEnd )
{
	this.node = startNode;
	this.endTag = atEnd;
	this.startTag = ! atEnd;
}

DOMWalker.prototype.destroy = function( )
{
	this.node = null;
}

WALK_REVERSE = true; /* Pass this to DOMWalker.walk for clarity */

/** Walk through nodes
 * reverse - if true, walk *backwards* instead of forwards
 * I don't know whether it's safe to alternate backward and forward movement with a single walker
 * (I was a bit lax with the startTag/endTag code, which might cause problems)
 */
DOMWalker.prototype.walk = function( gointo, reverse )
{
	if ( ELEMENT_NODE != this.node.nodeType || ! gointo || ( reverse ? this.startTag : this.endTag ) )
	{
		if ( ! reverse && this.node.nextSibling )
		{
			this.node = this.node.nextSibling;
			this.endTag = false;
			this.startTag = true;
		}
		else if ( reverse && this.node.previousSibling )
		{
			this.node = this.node.previousSibling;
			this.startTag = false;
			this.endTag = true;
		}
		else
		{
			this.node = this.node.parentNode;
			this.endTag = ! reverse;
			this.startTag = reverse;
		}
	}
	else if ( reverse ) // ( reverse && ELEMENT_NODE == this.node.nodeType && gointo )
	{
		if ( this.node.lastChild )
			this.node = this.node.lastChild;
		else
		{
			this.startTag = true;
			this.endTag = false;
		}
	}
	else // ( ! reverse && ELEMENT_NODE == this.node.nodeType && gointo )
	{
		if ( this.node.firstChild )
			this.node = this.node.firstChild;
		else
		{
			this.endTag = true;
			this.startTag = false;
		}
	}
	return null == this.node ? false : true;
}


/**
 * Simple publish/subscribe between browser windows using cookies
 * Will only work within a domain of course
 * Beware: if different browser windows run Javascript in different threads,
 * there could be concurrency issues (ouch).  So this should not be used for
 * critical communications (I'm using it for quoting, which should be ok).
 *
 * Cookies used are:
 * <name>_subscribers - the number of subscribers to the bus
 * <name>_read_counts - array of [ publication #, count ] of how many subscribers
 *                      have read a given publication
 * <name>_publication_n - content of publication #n
 * <name>_publish_count - maximum publication # used so far
 *
 * An item on the bus consists of the following:
 * name - an identifier
 * text - the item value
 * once - a control field indicating that only one subscriber should see the item
 * forward - a control field indicating that future subscribers should see the item
 */
function CookieBus( cookieName )
{
	this.cookieName = cookieName;
	this.subscribed = false;
	this.interval = null;
	this.readPubs = new Object( );
	this.nextToRead = 0;
}

/**
 * Fetch a named publication from the bus
 */
CookieBus.prototype.getPublication = function( name )
{
	var rawPub = domutil.readCookie( this.cookieName + '_publication_' + name );
	if ( rawPub )
	{
		var r = CookieBus.parseCookieValue( rawPub );
		return {
			name: name,
			once: r[ 'once' ] == 'true' ? true : false,
			forward: r[ 'forward' ] == 'true' ? true : false,
			value: r.value
		};
	}
	else
		return null;
}

CookieBus.parseCookieValue = function( raw )
{
	var x = raw.indexOf( ':' );
	var control = raw.substr(0, x );
	var text = raw.substr( x + 1 );
	var paramstrs = control.split( '&' );
	var params = { value: text };
	for ( var i = 0;  i < paramstrs.length;  ++i )
	{
		var p = paramstrs[ i ].split( '=' );
		params[ p[ 0 ] ] = p[ 1 ];
	}
	return params;
}

CookieBus.prototype.getAllPublications = function( )
{
	var prefix = this.cookieName + '_publication_';
	var rawPubs = domutil.readCookiePrefix( prefix );
	var publications = [ ];
	for ( var i = 0;  i < rawPubs.length;  ++i )
	{
		var pub = rawPubs[ i ];
		var r = CookieBus.parseCookieValue( pub.value );
		publications[ publications.length ] = {
			name: pub.name.substring( prefix.length, pub.name.length ),
			value: r.value,
			once: r.once ? true : false,
			forward: r.forward ? true : false
		};
	}
	return publications;
}

/**
 * Get the subscriber count
 */
CookieBus.prototype.getSubscriberCount = function( )
{
	// Update the subscriber count so it won't expire
	var n = domutil.readCookie( this.cookieName + '_subscriber_count' );
	return n ? Number( n ) : 0;
}

/**
 * Get the subscriber count, and update it so it will last a little longer
 * Since each subscriber will be polling the bus, this should ensure that the 
 * subscriber count stays alive as long as it is needed, but
 * clears shortly after it is not.
 */
CookieBus.prototype.getUpdateSubscriberCount = function( )
{
	// Update the subscriber count so it won't expire
	var n = this.getSubscriberCount( );
	domutil.createCookie( this.cookieName + '_subscriber_count', n, 0, 0, 2 );
	return n;
}

/**
 * If f is called, the bus is automatically closed down
 */
CookieBus.prototype.subscribe = function( interval, f, opts )
{
	if ( ! this.subscribed )
	{
		this.subscribed = true;
		this.nextToRead = 0;
		
		// Get and update the subscriber count (expires in 2 minutes)
		var n = domutil.readCookie( this.cookieName + '_subscriber_count' );
		n = n ? Number( n ) + 1 : 1;
		domutil.createCookie( this.cookieName + '_subscriber_count', n, 0, 0, 2 );
		
		// We won't read existing items unless the forward flag is set,
		// so record them as read
		var publications = this.getAllPublications( );
		for ( var i = 0;  i < publications.length;  ++i )
		{
			var pub = publications[ i ];
			if ( ! pub.forward )
				this.readPubs[ pub.name ] = true;
		}
		
		if ( interval && f )
		{
			var bus = this;
			this.interval = setInterval( function( ) {
//				while ( bus.subscribed )
//				{
					bus.getUpdateSubscriberCount( );
					var pub = bus.nextUnread( );
					if ( pub )
					{
						// Only mark as read if the the callback indicates 
						// that it has handled the cookie.
						if ( f( pub ) )
						{
							bus.markAsRead( pub.name );
							if ( pub.once );
								bus.unpublish( pub.name );
							return;
						}
					}
//				}
			}, interval );
		}
	}
}

CookieBus.prototype.unsubscribe = function( )
{
	if ( this.subscribed )
	{
		this.subscribed = false;
		// Get and update the subscriber count (expires in 2 minutes)
		var n = domutil.readCookie( this.cookieName + '_subscriber_count' );
		n = n ? Number( n ) - 1 : 0;
		domutil.createCookie( this.cookieName + '_subscriber_count', n, 0, 0, 2 );
		
		if ( this.interval )
		{
			clearInterval( this.interval );
			this.interval = null;
		}
	}
}

CookieBus.prototype.terminate = function( )
{
	domutil.removeCookie( this.cookieName + '_publish_count' );
	domutil.removeCookie( this.cookieName + '_subscriber_count' );
	var publications = domutil.readCookiePrefix( this.cookieName + '_publication_' );
	for ( var i = 0;  i < publications.length;  ++i )
		domutil.removeCookie( publications[ i ].name );
}

/**
 * Publish on the bus
 * s - the text to publish
 * once - if true, the first subscriber to see this will delete it
 * forward - if true the item should be seen by subscribers that appear
 *   after it has been published
 */
CookieBus.prototype.publish = function( s, params )
{
	// Find the highest publish count
	var n = domutil.readCookie( this.cookieName + '_publish_count' );
	n = n ? Number( n ) + 1 : 1;

	var once = false, forward = false;
	for ( var p in params )
	{
		var setting = params[ p ];
		switch ( p )
		{
			case 'once':
				once = setting ? true : false;
				break;
				
			case 'forward':
				forward = setting ? true : false;
				break;
				
			default:
				throw "Unknown CookieBus parameter.";
		}
	}
	
	var control = 'once=' + ( once ? 'true' : 'false' )
		+ '&forward=' + ( forward ? 'true' : 'false');
	var text = control + ':' + s;
	// Publish a new item
	// Item expires in 1 minute
	// Count expires in 2 minutes
	domutil.createCookie( this.cookieName + '_publish_count', n, 0, 0, 2 );
	domutil.createCookie( this.cookieName + '_publication_' + n, text, 0, 0, 1 );
	return n;
}

CookieBus.prototype.unpublish = function( n )
{
	domutil.removeCookie( this.cookieName + '_publication_' + n );
}

/**
 * Fetch the next unread item
 * This is a bit tricky, because it's possible that this particular item might
 * not be read successfully.  In that case the subsequent call to this should
 * continue from the publication following this one.  This is done with the
 * nextToRead index.  That's not foolproof is publications are removed from
 * the list, but it's probably good enough.
 */
CookieBus.prototype.nextUnread = function( )
{
	if ( this.subscribed )
	{
		var publications = this.getAllPublications( );
		for ( var i = 0;  i < publications.length;  ++i )
		{
			var x = ( i + this.nextToRead ) % publications.length;
			this.nextToRead = ( x + 1 ) % publications.length;
			var pub = publications[ i ];
			if ( ! this.readPubs[ pub.name ] )
				return pub;
		}
	}
	return null;
}

/**
 * When a publication is handled it should be marked as read so it won't be
 * seen again.                                                                                            
 */
CookieBus.prototype.markAsRead = function( name )
{
	this.readPubs[ name ] = true;
}

/*
CookieBus.prototype.read = function( )
{
	if ( this.subscribed )
	{
		var publications = this.getAllPublications( );
		for ( var i = 0;  i < publications.length;  ++i )
		{
			var pub = publications[ i ];
			if ( ! this.readPubs[ pub.name ] )
			{
				this.readPubs[ pub.name ] = true;
				return pub;
			}
		}
	}
	return null;
}
*/
