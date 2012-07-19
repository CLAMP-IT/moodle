/*
 * restutil.js
 */

restutil = {

/**
 * Oops.  This didn't conform to the URI spec - all those nice juice characters are
 * reserved.  So now all it does is replace %20 with + to make URIs easier to read.
 *
 * I'm tired of the standard Javascript encodeURIComponent encoding slashes
 * and colons in query parameters.  This makes debugging information difficult
 * to read, and there's really no point to it (at least for URI parameters).  
 * This function uses encodeURIComponent, then converts back some translations 
 * to make everything easier to read and debug.
 */
encodeURIParameter: function( s )
{
	s = encodeURIComponent( s );
//	s = s.replace( /%2[fF]/g, '/' );
//	s = s.replace( /%3[aA]/g, ':' );
	s = s.replace( /%20/g, '+' );
//	s = s.replace( /%5[bB]/g, '[' );
//	s = s.replace( /%5[dD]/g, ']' );
//	s = s.replace( /%2[cC]/g, ',' );
//	s = s.replace( /%3[bB]/g, ';' );
	return s;
},

/**
 * Takes an argument list in the format:
 * [
 * [ arg1, value1, include ],
 * [ arg2, value2, include ]
 * ]
 * An argument can be excluded by setting include to false, or if include is
 * absent if the value is false.
 */
filterQueryArgs: function( args )
{
	var r = [ ];
	for ( var i = 0;  i < args.length;  ++i )
	{
		var arg = args[ i ];
		if ( arg[ 0 ] && ( arg.length > 2 ? arg[ 2 ] : arg[ 1 ] ) )
			r.push( arg );
	}
	return r;
},

queryArgsToString: function( args )
{
	args = restutil.filterQueryArgs( args );
	var s = [ ]
	for ( var i = 0;  i < args.length;  ++i )
	{
		s.push(
			restutil.encodeURIParameter( args[ i ][ 0 ] ) + '=' +
			restutil.encodeURIParameter( args[ i ][ 1 ] ) );
	}
	return s.join( '&' );
},

		/*
	this.serviceUrls = {
		listAnnotations: '{url}/annotations',
		createAnnotation: '/annotations',
		updateAnnotation: '/annotations/{id}',
		deleteAnnotation: '/annotations/{id}',
		listBlockAnnotations: '{url}/annotations?format=blocks',
		bulkUpdateAnnotations: '/annotations'
	};
	
	// The caller specifies a number of parameters that must be plugged in
	// to the template.  Then take the first template that is able to resolve
	// all its substitutions.  E.g., if url is in the template, then the
	// caller must supply a url.  So the templates are listed from most to
	// least specific.
	this.serviceUrls = {
		listAnnotations: [	'{url}/annotations/?user={user}&format={format},
							'{url}/annotations/?format={format}',
							'/annotations/?user={user}&format={format}' ],
		createAnnotation: '/annotations/',
		updateAnnotations: ['/annotations/?note={note},
							'/annotations/{id}' ],
	};
	// A more compact and equivalent set for listAnnotations:
		listAnnotations: [ 	'{url}/annotations',
							'/annotations' ],
	// Note:  updateAnnotation is also used for delete
	
	// Here is the caller specifying its parameters:
	var serviceUrl = bindUrlTemplate( this.serviceUrls, 'listAnnotations', [
		[ 'url', url, url ? true : false ],
		[ 'user', userid, userid ? true : false ],
		[ 'curuser', window.marginalia.loginUserId, window.marginalia.loginUserId ? true : false ]
	] );
	// Third array item for each parameter indicates whether to actually require it
	// (makes constructing the array inline possible, as a row can be present but
	// ignored).
	// The true/false tests can be shortened - if not present, assume true:
	var serviceUrl = bindUrlTemplate( this.serviceUrls, 'listAnnotations', [
		[ 'url', url ]
		[ 'user', userid ],
		[ 'curuser', window.marginalia.loginUserId ]
	] );
	// So for something always required, set third parameter true
	// (though strictly speaking id can never be zero, so there's no need, but
	// this is clearer because explicit):
	var serviceurl = bindUrlTemplate( this.serviceUrls, 'deleteAnnotation', [
		[ 'id', annotation.id, true ]
	] );
	// Fourth array item is optional.  If present, it indicates whether the
	// argument is necessary.  If absent or True, it indicates that the url should
	// be added to the GET parameters if not substituted into the path.
	*/

UrlTemplate: function( template )
{
	this.template = template;
	this.params = null;
	this.paramMatcher = /\{([a-zA-Z0-9_]+)\}/g;
	this.manySlashMatcher = /(\/+)/g;

	this.match = function( args )
	{
//		trace( null, 'test template ' + this.template );
		var url = this.template;
		
		// Fill this.params with a hash of named template parameters
		if ( null == this.params )
		{
			this.params = [ ];
			var matches = template.match( this.paramMatcher );
			if ( matches )
			{
				for ( var i = 0;  i < matches.length;  ++i )
					this.params.push( matches[ i ].substr( 1, matches[ i ].length - 2 ) );
			}
		}
		
		args = restutil.filterQueryArgs( args );
		
		for ( var i = 0;  i < this.params.length;  ++i )
		{
			// Find a matching argument
			// Why worry about performance?  The argument list is almost invariably short,
			// so indexing it would probably take longer than linear search.
			var found = false;
			for ( var j = 0;  j < args.length;  ++j )
			{
				// Does this argument have same name as the param?
				// If arg[2] is present and false, this param will be ignored.
				// If arg[2] is absent or true, and arg[1] is false, this param
				// will be ignored.
				if ( args[ j ][ 0 ] == this.params[ i ] )
				{
					// Flag it as found and do the substitution
					found = true;
					var search = '{' + this.params[ i ] + '}';
					var index = url.indexOf( search );
//					trace( null, 'replace {' + this.params[ i ] + '} with ' + args[ j ][ 1 ] );
					url = url.substring( 0, index ) + args[ j ][ 1 ] + url.substring( index + search.length );
//					trace( null, '=> ' + url );
					break;
				}
			}
			
			// Every parameter must be filled by an argument
			if ( ! found )
				return null;
		}
		
		// Reduce multiple slashes to a single slash
		var index = url.indexOf ( '://' );
		if ( -1 == index )
			url = url.replace( this.manySlashMatcher, '/' );
		else
			url = url.substring( 0, index ) + '://' + url.substring( index + 3 ).replace( this.manySlashMatcher, '/' );

		// Now add arguments that were not substituted in as query params
		var queryParams = [ ];
		for ( i = 0;  i < args.length;  ++i )
		{
			// Check fourth array item to see whether argument is required in
			// path or GET params.
			if ( args[ i ].length < 4 || args[ i ][ 3 ] )
			{
				var found = false;
				for ( j = 0;  j < this.params.length;  ++j )
				{
					if ( this.params[ j ] == args[ i ][ 0 ] )
					{
						found = true;
						break;
					}
				}
				if ( ! found )
					queryParams.push( restutil.encodeURIParameter( args[ i ][ 0 ] ) + '=' + restutil.encodeURIParameter( args[ i ][ 1 ] ) );
			}
		}
		
		if ( queryParams.length > 0 )
		{
			url += -1 == url.indexOf( '?' ) ? '?' : '&';
			url += queryParams.join( '&' );
		}
		
//		trace( null, 'Use template ' + this.template );
//		trace( null, '=> ' + url );
		return url;
	}
},

UrlTemplateSet: function( templates )
{
	this.templates = [ ];
	for ( var i = 0;  i < templates.length;  ++i )
		this.templates[ i ] = new restutil.UrlTemplate( templates[ i ] );
	
	this.match = function( args )
	{
		for ( var i = 0;  i < this.templates.length;  ++i )
		{
			var url = this.templates[ i ].match( args );
			if ( url )
				return url;
		}
		return null;
	}
},

UrlTemplateDictionary: function( templates )
{
	this.dictionary = new domutil.Hash( );
	for ( operation in templates )
		this.dictionary.setItem( operation, new restutil.UrlTemplateSet( templates[ operation ] ) );
	
	this.match = function( args, operation )
	{
		var templateSet = this.dictionary.getItem( operation );
		return templateSet.match( args );
	}
},


getResource: function( serviceUrl, ok, fail, args )
{
	var xmlhttp = domutil.createAjaxRequest( );
	xmlhttp.open( 'GET', serviceUrl );
	xmlhttp.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
	if ( args && args.headers )
	{
		for ( header in args.headers )
			xmlhttp.setRequestHeader( header, args[headers] );
	}
	xmlhttp.onreadystatechange = function( ) {
		if ( xmlhttp.readyState == 4 ) {
			if ( xmlhttp.status == 200 ) {
				if ( ok )
					ok( args && args.okXml ? xmlhttp.responseXML : xmlhttp.responseText );
			}
			else if ( fail )
				fail( xmlhttp.status, args && args.failXml ? xmlhttp.responseXML : xmlhttp.responseText );
			xmlhttp = null;
		}
	}
	xmlhttp.send( null );
},


postResource: function( serviceUrl, body, ok, fail, args )
{
	var xmlhttp = domutil.createAjaxRequest( );
	xmlhttp.open( 'POST', serviceUrl, true );
	xmlhttp.setRequestHeader( 'Content-Type', args && args.contentType ? args.contentType : 'application/x-www-form-urlencoded; charset=UTF-8' );
	xmlhttp.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
	if ( args && args.headers )
	{
		for ( header in args.headers )
			xmlhttp.setRequestHeader( header, args.headers[header] );
	}
	//xmlhttp.setRequestHeader( 'Content-length', body.length );
	xmlhttp.onreadystatechange = function( ) {
		if ( xmlhttp.readyState == 4 ) {
			// No need for Safari hack, since Safari can't create annotations anyway.
			// Status could should ideally be 201, but some services don't support that (django 1.0)
			// console.log( 'status=' + xmlhttp.status );
			if ( xmlhttp.status == 201 || xmlhttp.status == 200 ) {
				// console.log( 'got response ' + xmlhttp.responseText + "\nOR\n" + xmlhttp.responseXml );
				var url = xmlhttp.getResponseHeader( 'Location' );
				if ( ok )
					ok( url, args && args.okXml ? xmlhttp.responseXml : xmlhttp.responseText );
			}
			else if ( fail )
				fail( xmlhttp.status, args && args.failXml ? xmlhttp.responseXml : xmlhttp.responseText );
			xmlhttp = null;
		}
	}
	xmlhttp.send( body );
},


putResource: function( serviceUrl, body, ok, fail, args )
{
	var xmlhttp = domutil.createAjaxRequest( );
	xmlhttp.open( args && args.noPutDelete ? 'POST' : 'PUT', serviceUrl, true );
	xmlhttp.setRequestHeader( 'Content-Type', args && args.contentType ? args.contentType : 'application/x-www-form-urlencoded; charset=UTF-8' );
	xmlhttp.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
	if ( args && args.headers )
	{
		for ( header in args.headers )
			xmlhttp.setRequestHeader( header, args[headers] );
	}
	//xmlhttp.setRequestHeader( 'Content-length', body.length );
	xmlhttp.onreadystatechange = function( ) {
		if ( xmlhttp.readyState == 4 ) {
			// Safari is braindead here:  any status code other than 200 is converted to undefined
			// IE invents its own 1223 status code
			// See http://www.trachtenberg.com/blog/?p=74
			if ( 200 == xmlhttp.status || 204 == xmlhttp.status || xmlhttp.status == null || xmlhttp.status == 1223 )
			{
				if ( ok )
					ok( args && args.okXml ? xmlhttp.responseXML : xmlhttp.responseText );
			}
			else if ( fail )
				fail( xmlhttp.status, args && args.failXml ? xmlhttp.responseXml : xmlhttp.responseText );
			xmlhttp = null;
		}
	}
	xmlhttp.send( body );
},

deleteResource: function( serviceUrl, ok, fail, args )
{
	var xmlhttp = domutil.createAjaxRequest( );
	xmlhttp.open( args && args.noPutDelete ? 'POST' : 'DELETE', serviceUrl, true );
	xmlhttp.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
	
	if ( args && args.headers )
	{
		for ( header in args.headers )
			xmlhttp.setRequestHeader( header, args[headers] );
	}
	xmlhttp.onreadystatechange = function( ) {
		if ( xmlhttp.readyState == 4 ) {
			// Safari is braindead here:  any status code other than 200 is converted to undefined
			// IE invents its own 1223 status code
			if ( 204 == xmlhttp.status || xmlhttp.status == null || xmlhttp.status == 1223 ) {
				if ( ok )
					ok( args && args.okXml ? xmlhttp.responseXML : xmlhttp.responseText );
			}
			else if ( fail )
				fail( xmlhttp.status, args && args.failXml ? xmlhttp.responseXml : xmlhttp.responseText );
			xmlhttp = null;
		}
	}
	xmlhttp.send( null );
}
};
