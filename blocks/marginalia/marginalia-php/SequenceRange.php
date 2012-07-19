<?php
/*
 * sequence-range.php
 * representations for points in an HTML document and for ranges (defined by of points)
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
 * $Id: SequenceRange.php 542 2012-06-21 05:55:01Z geof.glass $
 */

/** A range in an HTML document
 * Used for locating highlights
 * Immutable (fromString should be treated only as a constructor)
 */
class SequenceRange
{
	function SequenceRange( $startPoint=null, $endPoint=null )
	{
		$this->start = $startPoint;
		$this->end = $endPoint;
	}
	
	function fromString( $s )
	{
		$r = true;
		if ( null != $this->start || null != $this->end )
			die( "Attempt to modify SequenceRange" );
		// Standard format, e.g. 2/1.3.1;2/1.3.5
		// OR old overlap format, e.g. /2/3.1;/2/3.5
		$points = split( ';', $s );
		if ( 2 == count( $points ) )
		{
			$this->start = new SequencePoint( $points[ 0 ] );
			$this->end = new SequencePoint( $points[ 1 ] );
		}
		// Old block format, e.g. /2 3.1 3.5
		elseif ( preg_match( '/^\s*(\/[\/0-9]*)\s+(\d+)\.(\d+)\s+(\d+)\.(\d+)\s*$/', $s, $matches ) )
		{
			$this->start = new SequencePoint( $matches[1], 0, (int) $matches[2], (int) $matches[3] );
			$this->end = new SequencePoint( $matches[1], 0, (int) $matches[4], (int) $matches[5] );
		}
		// Old word format, e.g. 7.1 7.5
		elseif ( preg_match( '/^\s*(\d+)\.(\d+)\s+(\d+)\.(\d+)\s*$/', $s, $matches ) )
		{
			$this->start = new SequencePoint( '', 0, (int) $matches[1], (int) $matches[2] );
			$this->end = new SequencePoint( '', 0, (int) $matches[3], (int) $matches[4] );
		}
		else
			$r = false;
		return $r;
	}

	function setStart( $point )
	{  $this->start = $point;  }
	
	function getStart( )
	{  return $this->start;  }
	
	function setEnd( $point )
	{  $this->end = $point;  }
	
	function getEnd( )
	{  return $this->end;  }
	
	function toString( )
	{
		$s = '';
		if ( $this->start )
			$s .= $this->start->toString( );
		$s .= ';';
		if ( $this->end )
			$s .= $this->end->toString( );
		return $s;
	}
	
	function makeblockLevel( )
	{
		$this->start->makeBlockLevel( );
		$this->end->makeBlockLevel( );
	}
	
	function compare( $range2 )
	{
		$r = $this->start->compare( $range2->start );
		if ( 0 !== $r )
			return $r;
		return $this->end->compare( $range2->end );
	}
	
	function equals( $range2 )
	{
		return $this->start->equals( $range2->start ) && $this->end->equals( $range2->end );
	}
}


/** Represents a point in an annotated document
 *  Used for locating start and end of highlight ranges
 *  Immutable.
 */
class SequencePoint
{
	/**
	 * Two ways to call:
	 * - BlockPoint( '2.7.1', 15, 3 )
	 * - BlockPoint( '2.7.1/15.3' )
	 */
	function SequencePoint( $blockStr, $lines=null, $words=null, $chars=null )
	{
		// Create them in case nothing sets them below
		$this->lines = $lines;
		$this->words = $words;
		$this->chars = $chars;
		
		// Standard format, e.g. 2.7.1/1.15.3
		// Also accepts block only, e.g. 2.7.1
		if ( preg_match( '/^[0-9\.]*(\/\d*\.\d*\.\d*)?$/', $blockStr ) )
		{
			$sides = split( '/', $blockStr );
			if ( $sides[ 0 ] == '' )
				$this->path = array( );
			else
			{
				$parts = split( '\\.', $sides[ 0 ] );
				$this->path = array( );
				$n = count( $parts );
				for ( $i = 0;  $i < $n;  ++$i )
					$this->path[] = (int) $parts[ $i ];
			}
			
			if ( count( $sides ) > 1 )
			{
				$counts = split( '\\.', $sides[ 1 ] );
				assert( count( $counts ) == 3 );
				if ( $this->lines === null )
					$this->lines = null === $counts[ 0 ] ? null : (int) $counts[ 0 ];
				if ( $this->words === null )
					$this->words = null === $counts[ 1 ] ? null : (int) $counts[ 1 ];
				if ( $this->chars === null )
					$this->chars = null === $counts[ 2 ] ? null : (int) $counts[ 2 ];
			}
		}
		// Old overlap format, e.g. /2/7/1/15.3
		elseif ( '/' == $blockStr[ 0 ] )
		{
			$dot = strpos( $blockStr, '.' );
			$parts = split( '/', $blockStr );
			$n = count( $parts );
			
			// Transform the second call style (all one string)
			// into the correct parameters for the first
			if ( null === $lines )
			{
				if ( false !== $dot )
				{
					$slash = strrpos( $blockStr, '/' );
					if ( $this->words === null )
						$this->words = (int) substr( $blockStr, $slash + 1, $dot - $slash );
					if ( $this->chars === null )
						$this->chars = (int) substr( $blockStr, $dot + 1 );
					$blockStr = substr( $blockStr, 0, $slash );
					$n -= 1;
				}
			}
			// The blockStr may be padded with zeros.  Strip them.
			$this->path = array( );
			for ( $i = 1;  $i < $n;  ++$i )
			{
				if ( '' != $parts[ $i ] )
					$this->path[] = (int) $parts[ $i ];
			}
		}
		
		// Treat zero as null for lines and words (but 0 is valid for chars)
		if ( $this->lines == 0 )
			$this->lines = null;
		if ( $this->words == 0 )
			$this->words = null;
	}
	
	/**
	 * Compare only the path components of two points
	 */
	function comparePath( $point2 )
	{
		$len1 = count( $this->path );
		$len2 = count( $point2->path );
		for ( $i = 0;  $i  < min( $len1, $len2 );  $i += 1 )
		{
			if ( $this->path[ $i ] < $point2->path[ $i ] )
				return -1;
			elseif ( $this->path[ $i ] > $point2->path[ $i ] )
				return 1;
		}
		if ( $len1 < $len2 )
			return -1;
		elseif ( $len1 > $len2 )
			return 1;
		else
			return 0;
	}
	
	/**
	 * Compare location with another point.
	 * 0 - same point, -1 this one preceeds the other, 1 this one follows the other
	 */
	function compare( $point2 )
	{
		$r = $this->comparePath( $point2 );
		if ( $r != 0 )
			return $r;
		elseif ( $this->lines === null && $point2->lines === null )
			return 0;
		elseif ( $this->lines === null )
			return -1;
		elseif ( $point2->lines === null )
			return 1;
		elseif ( $this->lines < $point2->lines )
			return -1;
		elseif ( $this->lines > $point2->lines )
			return 1;
		elseif ( $this->words === null && $point2->words === null )
			return 0;
		elseif ( $this->words === null )
			return -1;
		elseif ( $point2->words === null )
			return 1;
		elseif ( $this->words < $point2->words )
			return -1;
		elseif ( $this->words > $point2->words )
			return 1;
		elseif ( $this->chars === null && $point2->chars === null )
			return 0;
		elseif ( $this->chars === null )
			return -1;
		elseif ( $point2->chars === null )
			return 1;
		elseif ( $this->chars < $point2->chars )
			return -1;
		elseif ( $this->chars > $point2->chars )
			return 1;
		return 0;
	}
	
	function equals( $point2 )
	{
		return 0 === $this->compare( $point2 );
	}
	
	/**
	 * Get the block path as a string of slash-separated indices
	 */
	function getPathStr( )
	{
		return join( '.', $this->path );
	}
	
	/**
	 * Get the block path a string of slash-separated indices, each one zero-padded to 4 places
	 * This is the storage format used in the database to allow string comparisons to order
	 * paths; it should not be used externally (use getPathStr instead).
	 */
	function getPaddedPathStr( )
	{
		$s = '';
		for ( $i = 0;  $i < count( $this->path );  ++$i )
			$s .= ( $i > 0 ? '.' : '' ) . sprintf( '%04d', $this->path[ $i ] );
		return $s;
	}
	
	function getLines( )
	{
		return $this->lines;
	}
	
	function getWords( )
	{
		return $this->words;
	}
	
	function getChars( )
	{
		return $this->chars;
	}
	
	function toString( )
	{
		$s = $this->getPathStr( );
		if ( $this->lines !== null || $this->words !== null || $this->chars !== null )
		{
			$s .= '/';
			$s .= ( $this->lines === null ? '' : $this->lines ) . '.';
			$s .= ( $this->words === null ? '' : $this->words ) . '.';
			$s .= ( $this->chars === null ? '' : $this->chars );
		}
		return $s;
	}
	
	function makeBlockLevel( )
	{
		$this->lines = null;
		$this->words = null;
		$this->chars = null;
	}
}

?>
