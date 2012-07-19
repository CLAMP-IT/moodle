<?php

/*
 * RangeInfo.php
 * Represents aggregated annotation information about specific ranges in an
 * annotated document.
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

class RangeInfo
{
	function RangeInfo( $url, $xpathRange, $sequenceRange )
	{
		$this->url = $url;
		$this->sequenceRange = $sequenceRange;
		$this->xpathRange = $xpathRange;
		$this->annotations = array();
	}
	
	function addAnnotation( &$annotation )
	{
		$this->annotations[ $annotation->getAnnotationId( ) ] = $annotation;
	}
	
	function getAnnotations( )
	{
		return array_values( $this->annotations );
	}
	
	function makeBlockLevel( )
	{
		if ( $this->xpathRange )
			$this->xpathRange->makeBlockLevel( );
		if ( $this->sequenceRange )
			$this->sequenceRange->makeBlockLevel( );
	}
	
	function toXml( )
	{
		$s = "\t<range-info url=\"".htmlspecialchars($this->url)."\">\n";
		
		if ( $this->sequenceRange )
			$s .= "\t\t<range format=\"sequence\">".htmlspecialchars( $this->sequenceRange->toString( ) )."</range>\n";
		
		if ( $this->xpathRange )
			$s .= "\t\t<range format=\"xpath\">".htmlspecialchars( $this->xpathRange->toString( ) )."</range>\n";

		$noteUsers = array();
		$editUsers = array();
		$allUsers = array();
		$annotations = array_values( $this->annotations );
		foreach ( $annotations as $annotation )
		{
			$userid = $annotation->getUserId( );
			if ( 'edit' == $annotation->getAction( ) )
				$editUsers[ $userid ] = array_key_exists( $userid, $editUsers ) ? $editUsers[ $userid ] + 1 : 1;
			else
				$noteUsers[ $userid ] = array_key_exists( $userid, $noteUsers ) ? $noteUsers[ $userid ] + 1 : 1;
			$allUsers[ $userid ] = $annotation->getUserName( );
		}
		foreach ( array_keys( $allUsers ) as $user )
		{
			$s .= "\t\t<user id='".htmlspecialchars( $user )."'";
			if ( array_key_exists( $user, $noteUsers ) )
				$s .= ' notes="'.$noteUsers[ $user ].'"';
			if ( array_key_exists( $user, $editUsers ) )
				$s .= ' edits="'.$editUsers[ $user ].'"';
			$s .= '>'.htmlspecialchars( $allUsers[ $user ] )."</user>\n";
		}
		$s .= "\t</range-info>\n";
		return $s;
	}
}

// Compare two blocks (sort first on start, then on end)
function rangeInfoCompare( $b1, $b2 )
{
	return $b1->sequenceRange->compare( $b2->sequenceRange );
}

?>
