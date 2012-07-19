<?php
/*
 * blocks/marginalia/lib.php
 *
 * Code has been moved to moodle_marginalia.php to make it possible to more
 * finely determine which code is needed for a given page.
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
 * $Id$
 */

require_once( $CFG->dirroot.'/blocks/marginalia/config.php' );
require_once( ANNOTATION_DIR.'/moodle_marginalia.php' );

/*
 * Moodle automatically loads this lib.php file on every page if the 
 * Marginalia block is enabled.  It would be really cool if we could 
 * emit the headers here.  But... it cannot be.  Because we need PAGE->url
 * to figure out what to emit.  So there's no way to avoid a patch point.
 */

