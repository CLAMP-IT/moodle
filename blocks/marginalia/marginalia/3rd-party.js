/* The following cookie functions are from Peter-Paul Koch's Quirksmode at
 * http://www.quirksmode.org/js/cookies.html
 * Added semicolon escaping.  Not quite perfect:  if $: occurs in the original
 * text, it will be replaced by $$:, which will then be replaced by $;.  Argh.
 * $Id: 3rd-party.js 316 2008-11-20 07:31:20Z geof.glass $
 */
function createCookie( name, value, days)
{
	if (days)
	{
		var date = new Date();
		date.setTime ( date.getTime( ) + ( days * 24 * 60 * 60 * 1000 ) );
		var expires = "; expires=" + date.toGMTString( );
	}
	else
		var expires = "";
	if ( value && 'string' == typeof value )
	{
		value = value.replace( '$', '$S' );
		value = value.replace( ';', '$:' );
	}
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie( name )
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
			value.replace( '$:', ';' );
			value.replace( '$S', '$' );
			return value;
		}
	}
	return null;
}

function removeCookie( name )
{
	createCookie( name, "", -1);
}


/*
Script by RoBorg
RoBorg@geniusbug.com
http://javascript.geniusbug.com | http://www.roborg.co.uk
Please do not remove or edit this message
Please link to this website if you use this script!
*/
function clone(myObj)
{
	if(typeof(myObj) != 'object') return myObj;
	if(myObj == null) return myObj;

	var myNewObj = new Object();

	for(var i in myObj)
		myNewObj[i] = clone(myObj[i]);

	return myNewObj;
}

/**
 * Also need to be able to copy (in order to maintain same reference in case it has been aliased)
 */
function copy( fromObj, toObj, deep )
{
	for ( var i in fromObj )
		toObj[ i ] = deep ? clone( fromObj[ i ] ) : fromObj[ i ];
}

/*
 * The fellowing functions are
 * written by Dean Edwards, 2005
 * with input from Tino Zijdel, Matthias Miller, Diego Perini
 *
 * http://dean.edwards.name/weblog/2005/10/add-event/
 */
function addEvent(element, type, handler, capture) {
	// #Geof#  Can pass a CSS selector as a string instead of an actual element reference
	if ( typeof element == 'string' )
	{
		var elements = cssQuery( element );
		for ( var i = 0;  i < elements.length;  ++i )
			addEvent( elements[i], type, handler, capture );
	}
	else
	{
		if (element.addEventListener) {
			// #Geof# Support event capture in capable browsers:
			element.addEventListener(type, handler, capture ? true : false);
		} else {
			// assign each event handler a unique ID
			if (!handler.$$guid) handler.$$guid = addEvent.guid++;
			// create a hash table of event types for the element
			if (!element.events) element.events = {};
			// create a hash table of event handlers for each element/event pair
			var handlers = element.events[type];
			if (!handlers) {
				handlers = element.events[type] = {};
				// store the existing event handler (if there is one)
				if (element["on" + type]) {
					handlers[0] = element["on" + type];
				}
			}
			// store the event handler in the hash table
			handlers[handler.$$guid] = handler;
			// assign a global event handler to do all the work
			element["on" + type] = handleEvent;
		}
	}
};
// a counter used to create unique IDs
addEvent.guid = 1;

function removeEvent(element, type, handler) {
	// #Geof#  Can pass a CSS selector as a string instead of an actual element reference
	if ( typeof element == 'string' )
	{
		var elements = cssQuery( element );
		for ( var i = 0;  i < elements.length;  ++i )
			removeEvent( elements[i], type, handler );
	}
	else
	{
		if (element.removeEventListener) {
			element.removeEventListener(type, handler, false);
		} else {
			// delete the event handler from the hash table
			if (element.events && element.events[type]) {
				delete element.events[type][handler.$$guid];
			}
		}
	}
};

function handleEvent(event) {
	var returnValue = true;
	// grab the event object (IE uses a global event object)
	event = event || fixEvent(((this.ownerDocument || this.document || this).parentWindow || window).event);
	// get a reference to the hash table of event handlers
	var handlers = this.events[event.type];
	// execute each event handler
	for (var i in handlers) {
		this.$$handleEvent = handlers[i];
		if (this.$$handleEvent(event) === false) {
			returnValue = false;
		}
	}
	return returnValue;
};

function fixEvent(event) {
	// add W3C standard event methods
	event.preventDefault = fixEvent.preventDefault;
	event.stopPropagation = fixEvent.stopPropagation;
	return event;
};
fixEvent.preventDefault = function() {
	this.returnValue = false;
};
fixEvent.stopPropagation = function() {
	this.cancelBubble = true;
};
