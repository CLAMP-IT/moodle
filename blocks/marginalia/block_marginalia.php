<?php

/*
 * block_marginalia.php
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

class block_marginalia extends block_base
{
	function init( )
	{
		$this->title = 'Marginalia Annotation'; //get_string('annotation', 'block_annotation');
//		$this->version = 2010121800;
		$this->cron = 60 * 60 * 25.2;	// once a day is often enough, but make it a bit off to prevent sympathetic resonance
	}
	
	function get_content( )
	{
		global $USER;
		
		if ( $this->content === NULL )
		{
			//$refurl = moodle_marginalia::get_refurl( );
			
			$this->content = new stdClass;
			$this->content->text = '';
			$this->content->footer = '';
		}
		return $this->content;
	}
	
	function cron( )
	{
		global $CFG;
		
		// Delete annotations whose users no longer exist
		// this removes the need to touch admin/user.php
		// Other code should therefore be careful not to join on non-existent users
		$query = "DELETE FROM {$CFG->prefix}marginalia WHERE userid NOT IN (SELECT id FROM {$CFG->prefix}user)";
		execute_sql( $query, false );
		// This will catch all read records for non-existent users and annotations, though the latter should
		// already have been deleted with the annotation.
		$query = "DELETE FROM {$CFG->prefix}marginalia_read WHERE annotationid NOT IN (SELECT id FROM {$CFG->prefix}marginalia)";
		execute_sql( $query, false );
	}
}

$string['marginalia:view_all'] = 'View all';
$string['marginalia:fix_notes'] = 'Fix notes';