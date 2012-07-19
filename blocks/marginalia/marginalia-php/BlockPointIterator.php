<?php

/*
 * BlockPointIterator.php
 * Currently unused.
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
 
class BlockPointIterator
{
	function BlockPointIterator( &$blocks )
	{
		$this->blocks =& $blocks;
		
		// Create two arrays:  one of range starts, the other of range ends
		$this->starts = $blocks;
		$this->ends = $blocks;
		usort( $this->starts, 'blockCompareStart' );
		usort( $this->ends, 'blockCompareEnd' );
		
		$this->start_i = 0;
		$this->end_i = 0;
		$this->comp = 0;
		
		// Current reference
		$this->sequencePoint = null;
		$this->xpathPoint = null;
		$this->block = null;
	}
	
	function hasMore( )
	{
		return $this->end_i < count( $this->ends );
	}
	
	function isStartPoint( )
	{
		return $this->comp < 0;
	}
	
	/**
	 * Treat an end/start pair as an end point, then iterate past and look at the start
	 * on next()
	 */
	function isEndPoint( )
	{
		return $this->comp >= 0;
	}
	
	function getSequencePoint( )
	{
		return $this->sequencePoint;
	}
	
	function getXPathPoint( )
	{
		return $this->xpathPoint;
	}
	
	function getBlock( )
	{
		return $this->block;
	}
	
	function next( )
	{
		if ( $this->end_i < count( $this->ends ) )
		{
			$end =& $this->ends[ $this->end_i ];
			$endSequence =& $end->sequenceRange;
			$endXPath =& $end->xpathRange;
			
			if ( $this->start_i < count( $this->starts ) )
			{
				$start =& $this->starts[ $this->start_i ];
				$startSequence =& $start->sequenceRange;
				$startXPath =& $start->xpathRange;
				//echo "start: ".$startSequence . "<br/>";
				$this->comp = $startSequence->start->compare( $endSequence->end );
			}
			else
				$this->comp = 1;	// Only ends remain
				
			//echo "comp: ".$this->comp."<br/>";
			if ( $this->comp >= 0 )
			{
				$this->block =& $end;
				
				if ( $endSequence )
					$this->sequencePoint =& $endSequence->end;
				else
					$this->sequencePoint = null;
					
				if ( $endXPath )
					$this->xpathPoint =& $endXPath->end;
				else
					$this->xpathPoint = null;
				
				++$this->end_i;
			}
			elseif ( $this->comp < 0 )
			{
				$this->block =& $start;
				
				if ( $startSequence )
					$this->sequencePoint =& $startSequence->start;
				else
					$this->sequencePoint = null;
				
				if ( $startXPath )
					$this->xpathPoint =& $startXPath->start;
				else
					$this->xpathPoint = null;
				
				++$this->start_i;
			}
			return True;
		}
		else
			return False;
	}
}

// Useful for sorting by range start position:
function blockCompareStart( $a1, $a2 )
{
	return $a1->sequenceRange->start->compare( $a2->sequenceRange->start );
}

// Useful for sorting by range end position:
function blockCompareEnd( $a1, $a2 )
{
	return $a1->sequenceRange->end->compare( $a2->sequenceRange->end );
}

?>
