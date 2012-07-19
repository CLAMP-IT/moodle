<?php

/*
 * AnnotationUserSummary.php
 * Summarizes annotations by user
 * Useful for providing a drop-down list of users who have created annotations
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
 * $Id$
 */

class AnnotationUserSummary
{
	function AnnotationUserSummary( $annotations, $url )
	{
		$this->url = $url;
		$this->noteUsers = array();
		$this->editUsers = array();
		$this->allUsers = array();
		
		foreach ( $annotations as $annotation )
		{
			$userid = $annotation->getUserId();
			if ( 'edit' == $annotation->getAction( ) )
				$this->editUsers[ $userid ] = array_key_exists( $userid, $this->editUsers ) ? $this->editUsers[ $userid ] + 1 : 1;
			else
				$this->noteUsers[ $userid ] = array_key_exists( $userid, $this->noteUsers ) ? $this->noteUsers[ $userid ] + 1 : 1;
			$this->allUsers[ $userid ] = $annotation->getUserName( );
		}
	}
	
	function toXml( )
	{
		$s = "<annotation-summary href=\"".htmlspecialchars( $this->url ) ."\">\n";
		
		foreach ( array_keys( $this->allUsers ) as $user )
		{
			$s .= "\t<user id='" . htmlspecialchars( $user ) . "'";
			if ( $this->noteUsers[ $user ] )
				$s .= ' notes="'.$this->noteUsers[ $user ].'"';
			if ( $this->editUsers[ $user ] )
				$s .= ' edits="'.$this->editUsers[ $user ].'"';
			$s .= ">".htmlspecialchars( $this->allUsers[ $user ] )."</user>\n";
		}
		
		$s .= "</annotation-summary>\n";
		return $s;
	}
}

?>
