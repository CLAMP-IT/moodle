/*
 * summary.js
 *
 * Marginalia has been developed with funding and support from
 * BC Campus, Simon Fraser University, and the Government of
 * Canada, the UNDESA Africa i-Parliaments Action Plan, and  
 * units and individuals within those organizations.  Many 
 * thanks to all of them.  See CREDITS.html for details.
 * Copyright (C) 2005-2011 Geoffrey Glass; the United Nations
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
 */

function summaryOnLoad( )
{
	jQuery('.annotations .note').each( function( index, node ) {
		domutil.urlize( this );
	} );
}
jQuery(window).load(summaryOnLoad);

/*
 * Must be called before any other annotation functions
 */
function AnnotationSummary( wwwroot, params )
{
	this.wwwroot = wwwroot;
	this.annotationService = params.annotationService;
	this.loginUserId = params.loginUserId;
	this.csrfCookie = null;
	this.useLog = false;
	this.extService = params.extService;
	
	for ( var param in params )
	{
		switch ( param )
		{
			case 'annotationService':
				this.annotationService = params[ param ];
				break;
			
			case 'userid':
				this.userid = params[ param ];
				break;
			
			case 'csrfCookie':
				this.csrfCookie = params[ param ];
				break;
				
			case 'useLog':
				this.useLog = params[ param ];
				break;
				
			default:
				throw 'Unknown paramater to AnnotationSummary: ' + param;
		}
	}
	
	this.smartquote = new Smartquote( this.wwwroot, null, this.extService );
}

AnnotationSummary.prototype.deleteAnnotation = function( id, annotationid )
{
	var element = document.getElementById( id );
	var row = domutil.parentByTagClass( element, 'tr', null );
	var annotation = this.annotationFromRow( row, {
		id: annotationid } );

	var f = function( xmldoc ) {
		window.location.reload( );
	};
	this.annotationService.deleteAnnotation( annotation, f );
}

AnnotationSummary.skipZoom = function( node )
{
	if ( ELEMENT_NODE == node.nodeType )
	{
		if ( domutil.hasClass( node, 'zoom' ) )
			return true;
	}
	return false;
}

AnnotationSummary.prototype.quote = function( id, userid, postId )
{
	var element = document.getElementById( id );
	var row = domutil.parentByTagClass( element, 'tr', null );
	var annotation = this.annotationFromRow( row, {
		userid: userid } );
	
	var postId = Smartquote.postIdFromUrl( annotation.getUrl( ) );
	this.smartquote.quoteAnnotation( annotation, this.userid, postId );
}

AnnotationSummary.prototype.annotationFromRow = function( row, fields )
{
	var node = domutil.childByTagClass( row, null, 'quote' );
	var quote = domutil.getNodeText( node, AnnotationSummary.skipZoom );
	
	node = domutil.childByTagClass( row, null, 'note' );
	var note = domutil.getNodeText( node, AnnotationSummary.skipZoom );
	
	node = domutil.childByTagClass( row, null, 'user-name' );
	var userName = domutil.getNodeText( node, AnnotationSummary.skipZoom );

	// This one is tricky because of rowspan
	for ( var prevrow = row;  prevrow;  prevrow = prevrow.precedingSibling )
	{
		node = domutil.childByTagClass( prevrow, null, 'quote-author' );
		if ( node )
			break;
	}
	var quoteAuthorName = node ? domutil.getNodeText( node, AnnotationSummary.skipZoom ) : '';
	
	for ( var prevrow = row;  prevrow;  prevrow = prevrow.precedingSibling )
	{
		node = domutil.childByTagClass( prevrow, null, 'url' );
		if ( node )
			break;
	}
	var url = node ? node.getAttribute( 'href' ) : '';
	
	var annotation = new Annotation( {
		id: fields.id,
		userId: fields.userid,
		url: url,
		quote: quote,
		note: note,
		userName: userName,
		quoteAuthorName: quoteAuthorName
	} );
	annotation.resetChanges( );
	
	return annotation;
}

