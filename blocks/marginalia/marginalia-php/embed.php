<?php

/*
 * embed.php
 * Useful functions for embedding Marginalia Javascript etc. in generated HTML pages.
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
 * $Id: annotate.php 78 2007-07-19 23:24:23Z geof.glass $
 */

function listMarginaliaJavascript( )
{
	return array (
		'3rd-party/jquery.js'
		,'3rd-party/jquery.url.js'
		,'3rd-party/jquery.dates.js'
		,'3rd-party/date.js'
		,"3rd-party/shortcut.js"
		,"3rd-party.js"
	
		,"log.js"
		,"prefs.js"
		,"html-model.js"
		,"domutil.js"
		,"restutil.js"
		,"ranges.js"
		,"SequenceRange.js"
		,"XPathRange.js"
		,"annotation.js"
		,"post-micro.js"
		
		,"marginalia.js"
		,"blockmarker-ui.js"
		,"highlight-ui.js"
		,"note-ui.js"
		,"link-ui.js"
		,"link-ui-simple.js"
		,"link-ui-clicktolink.js"
		,"linkable.js"
	
		,"RangeInfo.js"
		,"rest-annotate.js"
		,"rest-prefs.js"
		,"rest-keywords.js"
	);
}
	
?>
