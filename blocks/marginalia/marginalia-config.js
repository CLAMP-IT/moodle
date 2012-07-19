/*
 * Annotation configuration settings
 * These are sample settings.  They may need to change for debugging,
 * or when integrating with different web applications.
 *
 * Marginalia has been developed with funding and support from
 * BC Campus, Simon Fraser University, and the Government of
 * Canada, and units and individuals within those organizations.
 * Many thanks to all of them.  See CREDITS.html for details.
 * Copyright (C) 2005-2007 Geoffrey Glass www.geof.net
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
 * $Id: marginalia-config.js 80 2007-07-20 03:36:11Z geof.glass $
 */

ANNOTATION_ACCESS_DEFAULT = 'public';	// default access

function initLogging( )
{
	ErrorLogger.initLogging( true, false );
	
	// Set these to true to view certain kinds of events
	// Most of these are only useful for debugging specific areas of code.
	// annotation-service, however, is particularly useful for most debugging
	log.setTrace( 'annotation-service', false );	// XMLHttp calls to the annotation service
	log.setTrace( 'word-range', false );			// Word Range calculations (e.g. converting from Text Range)
	log.setTrace( 'xpath-range', false );			// Trace XPath ranges
	log.setTrace( 'find-quote', false );			// Check if quote matches current state of document
	log.setTrace( 'node-walk', false );			// Used for going through nodes in document order
	log.setTrace( 'show-highlight', false );		// Text highlighting calculations
	log.setTrace( 'align-notes', false );			// Aligning margin notes with highlighting
	log.setTrace( 'range-compare', false );		// Compare range positions
	log.setTrace( 'range-string', false );			// Show conversions of word ranges to/from string
	log.setTrace( 'list-annotations-xml', false );// Show the full Atom XML coming back from listAnnotations
	log.setTrace( 'WordPointWalker', false );		// Show return values from WordPointWalker
	log.setTrace( 'prefs', false );				// List fetched preferences
	log.setTrace( 'keywords', false );				// List fetched keywords
	log.setTrace( 'SequencePoint.compare', false );	// Compare two BlockPoints
	log.setTrace( 'range-timing', false );			// Calculate the speed of range calculations
	log.setTrace( 'highlight-timing', false );	// Calculate the speed of highlight display
	log.setTrace( 'actions', false );				// Insertion of action text
	log.setTrace( 'markers', false );				// left margin marker calculations
	log.setTrace( 'showNote', false );
	log.setTrace( 'stripMarkup', false );
}
