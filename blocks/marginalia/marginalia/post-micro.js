/*
 * post-micro.js
 *
 * Support for message / blog post micro-format.  This is based on the
 * hAtom microformat.
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
 * $Id: post-micro.js 537 2012-05-30 21:51:38Z geof.glass $
 */

/*
 * This class keeps track of PostMicro stuff on a page
 * Initially that information was integrated into individual DOM nodes (especially
 * as PostMicro objects), but because of memory leak problems I'm moving it here.
 */
function PostPageInfo( doc, selectors )
{
	this.doc = doc;
	this.posts = new Array( );
	this.postsById = new Object( );
	this.postsByUrl = new Object( );
	this.selectors = selectors;
	this.IndexPosts( doc.documentElement );
}


/**
 * In order to avoid creating multiple instances for a given document,
 * keep a cache in the window.  The linear search here shouldn't be a problem
 * as I don't expect more than one to exist - but just in case, it's there.
 */
PostPageInfo.cachedPostPageInfos = [ ];
PostPageInfo.getPostPageInfo = function( doc, selectors )
{
	var info;
	for ( var i = 0;  i < PostPageInfo.cachedPostPageInfos.length; ++i )
	{
		info = PostPageInfo.cachedPostPageInfos[ i ];
		if ( info.doc == doc && info.selectors == selectors)
			return info;
	}
	info = new PostPageInfo( doc, selectors );
	PostPageInfo.cachedPostPageInfos.push( info );
	return info;
}

PostPageInfo.prototype.IndexPosts = function( root )
{
	var posts = this.selectors[ 'post' ].nodes( root );
	for ( var i = 0;  i < posts.length;  ++i )
	{
		var postElement = posts[ i ];
		var post = new PostMicro( this, postElement );
		this.posts[ this.posts.length ] = post;
		if ( null != post.getId( ) && '' != post.getId( ) )
			this.postsById[ posts[ i ].id ] = post;
		if ( null != post.getUrl( ) && '' != post.getUrl( ) )
			this.postsByUrl[ post.getUrl( ) ] = post;
		postElement[ Marginalia.F_POST ] = post;
	}
}

PostPageInfo.prototype.getPostById = function( id )
{
	return this.postsById[ id ];
}

/**
 * Get a post that is the parent of a given element
 */
PostPageInfo.prototype.getPostByElement = function( element )
{
	// Inefficient.  Probably not an issue as it isn't called that often.
	// Hard to fix when using selectors for everything (can't just walk up
	// to parents and check for a given ID).  Alternative would be to set
	// a field on posts when indexing, then look for that.
	for ( var i = 0;  i < this.posts.length;  ++i )
	{
		if ( domutil.isElementDescendant( element, this.posts[ i ].getElement( ) ) )
			return this.posts[ i ];
	}
	return null;
}
 
/*
 * Return a post with a matching URL or, if that does not exist, try stripping baseUrl off the passed Url
 */
PostPageInfo.prototype.getPostByUrl = function( url, baseUrl )
{
	if ( this.postsByUrl[ url ] )
		return this.postsByUrl[ url ];
	else if ( baseUrl && url.substring( 0, baseUrl.length ) == baseUrl )
		return this.postsByUrl[ url.substring( baseUrl.length ) ];
	// Only try prepending base url if there's no scheme on the URL
	else if ( ! url.match( /^[a-z]+:\/\// ) )
		return this.postsByUrl[ baseUrl + url ];
}

PostPageInfo.prototype.getAllPosts = function( )
{
	return this.posts;
}

/*
 * Post Class
 * This is attached to the root DOM node of a post (not the document node, rather the node
 * that matches a post selector).  It stores references to child nodes
 * containing relevant metadata.  The class also provides a place to hang useful functions,
 * e.g. for annotation or smart copy support.
 */
function PostMicro( postInfo, element )
{
	// Point the post and DOM node to each other
	this.postInfo = postInfo;
	this._element = element;
}

/*
 * Accessor for related element
 * Used so that we can avoid storing a pointer to a DOM node,
 * which tends to cause memory leaks on IE.
 */
PostMicro.prototype.getElement = function( )
{
	return this._element;
}


PostMicro.prototype.getTitle = function( )
{
	if ( ! this._fetchedTitle )
	{
		// The title
		if ( this.postInfo.selectors[ 'post_title' ] )
			this._title = this.postInfo.selectors[ 'post_title' ].value( this._element );
		else
			this._title = null;
		this._fetchedTitle = true;
	}
	return this._title;
}

PostMicro.prototype.getAuthorId = function( )
{
	if ( ! this._fetchedAuthorId )
	{
		// The author
		if ( this.postInfo.selectors[ 'post_authorid' ] )
			this._authorId = this.postInfo.selectors[ 'post_authorid' ].value( this._element );
		else
			this._authorId = null;
		this._fetchedAuthorId = true;
	}
	return this._authorId;
}

PostMicro.prototype.getAuthorName = function( )
{
	if ( ! this._fetchedAuthorName )
	{
		// The author
		if ( this.postInfo.selectors[ 'post_author' ] )
			this._authorName = this.postInfo.selectors[ 'post_author' ].value( this._element );
		else
			this._authorName = null;
		this._fetchedAuthorName = true;
	}
	return this._authorName;
}

PostMicro.prototype.getDate = function( )
{
	if ( ! this._fetchedDate )
	{
		if ( this.postInfo.selectors[ 'post_date' ] )
		{
			var s = this.postInfo.selectors[ 'post_date' ].value( this._element, true );
			if ( null == s )
				this._date = null;
			else
			{
				var matches = s.match( /(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})([+-]\d{4})/ );
				if ( null == matches )
					this._date = null;
				else
					// I haven't figured out how to deal with the time zone, so it assumes that server
					// time and local time are the same - which is rather bad.
					this._date = new Date( matches[1], matches[2]-1, matches[3], matches[4], matches[5] );
			}
		}
		else
			this._date = null;
		this._fetchedDate = true;
	}

	return this._date;
}

PostMicro.prototype.getId = function( )
{
	if ( ! this._fetchedId )
	{
		this._id = this.postInfo.selectors[ 'post_id' ].node( this._element );
	}
	return this._id;
}

PostMicro.prototype.getUrl = function( baseUrl )
{
	if ( ! this._fetchedUrl )
	{
		// The node containing the url
		if ( this.postInfo.selectors[ 'post_url' ] )
			this._url = this.postInfo.selectors[ 'post_url' ].value( this._element );
		// Otherwise grab the request url, but strip it of any fragment identifier
		else
		{
			this._url = String( window.location );
			var parts = this._url.split( '#' );
			if ( parts.length > 1 )
				this._url = parts[ 0 ];
		}
		this._fetchedUrl = true;
	}
	return ( baseUrl && this._url && this._url.substring( 0, baseUrl.length ) == baseUrl )
		? this._url.substring( baseUrl.length )
		: this._url;
}

/*
 * Accessor for content element
 * Used so that we can avoid storing a pointer to a DOM node,
 * which tends to cause memory leaks on IE.
 */
PostMicro.prototype.getContentElement = function( )
{
	if ( ! this._contentElement )
	{
		// The node containing the content
		// Any offsets (e.g. as used by annotations) are from the start of this node's children
		this._contentElement = this.postInfo.selectors[ 'post_content' ].node( this._element );
	}
	return this._contentElement;
}


