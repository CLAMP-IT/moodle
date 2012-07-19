<?php

/*
 * marginalia-constants.php
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
 * as published by the Free Software Foundation; either version 2
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
 * $Id: annotate.php 78 2007-07-19 23:24:23Z geof.glass $
 */

// Post Micro format:
define( 'PM_POST_CLASS', 'hentry' );				// this is an addressable fragment for annotation
define( 'PM_CONTENT_CLASS', 'entry-content' );	// the content portion of a fragment
define( 'PM_TITLE_CLASS', 'entry-title' );		// the title of an annotated fragment
define( 'PM_AUTHOR_CLASS', 'author' );				// the author of the fragment
define( 'PM_DATE_CLASS', 'published' );			// the creation/modification date of the fragment
define( 'PM_URL_REL', 'bookmark' );				// the url of this fragment (uses rel rather than class attribute)

// Annotation format:
define( 'AN_NOTES_CLASS', 'notes' );			// the notes portion of a fragment

?>
