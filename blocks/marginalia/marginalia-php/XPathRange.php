<?php
/*
 * XPathRange.php
 * representation of range in an HTML document as specified by two XPath expressions
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
 * $Id: XPathRange.php 542 2012-06-21 05:55:01Z geof.glass $
 */

class XPathRange
{
	function XPathRange( $startPoint=null, $endPoint=null )
	{
		$this->start = $startPoint;
		$this->end = $endPoint;
	}
	
	function fromString( $s )
	{
		if ( null != $this->start || null != $this->end )
			die( "Attempt to modify XPathRange" );
		$points = split( ';', $s );
		$this->start = new XPathPoint( $points[ 0 ] );
		$this->end = new XPathPoint( $points[ 1 ] );
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
			$s = $this->start->toString( );
		$s .= ';';
		if ( $this->end )
			$s .= $this->end->toString( );
		return $s;
	}
	
	function makeBlockLevel( )
	{
		$this->start->makeBlockLevel( );
		$this->end->makeBlockLevel( );
	}
}

class XPathPoint
{
	/**
	 * Two ways to call:
	 * - XPathPoint( '/p[2]/p[7]', 15, 3 )
	 * - XPathPoint( '/p[2]/p[7]/word(15,3)' )
	 */
	function XPathPoint( $xpathStr, $lines=null, $words=null, $chars=null )
	{
		$path = $xpathStr;		
		while ( $path != '' )
		{
			$x = strrpos( $path, '/' );
			$tail = FALSE === $x ? $path : substr( $path, $x + 1 );

			if ( preg_match( '/^(line|word|char)\((\d+)\)$/', $tail, $matches ) )
			{
				if ( 'line' == $matches[ 1 ] )
					$this->lines = (int) $matches[ 2 ];
				elseif ( 'word' == $matches[ 1 ] )
					$this->words = (int) $matches[ 2 ];
				elseif ( 'char' == $matches[ 1 ] )
					$this->chars = (int) $matches[ 2 ];
				$path = FALSE === $x ? '' : substr( $path, 0, $x );
			}
			else
				break;
		}

		if ( XPathPoint::isXPathSafe( $path ) )
			$this->path = $path;
		else
			$this->path = null;

		if ( null !== $lines )
			$this->lines = $lines;
		if ( null !== $words )
			$this->words = $words;
		if ( null !== $chars )
			$this->chars = $chars;

		// Treat zero as null for lines and words (but 0 is valid for chars)
		if ( $this->lines == 0 )
			$this->lines = null;
		if ( $this->words == 0 )
			$this->words = null;
	}
	
	/**
	 * Get the xpath (specifying a particular element in the HTML document)
	 */
	function getPathStr( )
	{
		return $this->path;
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
		$s = $this->path;
		if ( $this->lines !== null )
			$s .= '/line(' . $this->lines . ')';
		if ( $this->words !== null )
			$s .= '/word(' . $this->words . ')';
		if ( $this->chars !== null )
			$s .= '/char(' . $this->chars . ')';
		return $s;
	}
	
	/**
	 * Check whether an untrested XPath expression is safe.  Calls to
	 * document(), for example, are dangerous.  This implementation
	 * only accepts a tiny subset of possible XPath expressions and
	 * may need to be extended.
	 * Will accept only xpaths components like the following:
	 *  p[1]
	 *  html:p
	 *  following-sibling::p
	 *  p[@attribute='value']
	 */
	function isXPathSafe( $xpath )
	{
		if ( '' == $xpath )
			return true;
		if ( preg_match( '/^\.\/\/(.*)$/', $xpath, $matches ) )
			$xpath = $matches[ 1 ];
		$parts = split( '/', $xpath );
		foreach ( $parts as $part )
		{
			$part = trim( $part );
			if ( preg_match( '/^[a-zA-Z0-9_:\*-]+\s*(.*)$/', $part, $matches) )
			{
				$tail = trim( $matches[ 1 ] );
				// Simple tag name, with or without axis and/or namespace
				if ( '' == $tail )
					;
				// Qualification in [brackets]
				elseif ( preg_match( '/^\[([^\]]+)\]\s*$/', $tail, $matches ) )
				{
					$test = trim( $matches[ 1 ] );
					// Simple number index
					if ( preg_match( '/^\d+$/', $test ) )
						;
					// Comparison of an attribute with a quoted value
					elseif ( preg_match( '/^@[a-zA-Z0-9_-]+\s*=\s*([\'"])[a-zA-Z0-9:._-]+([\'"])$/', $test, $matches ) )
					{
						if ( $matches[ 1 ] == $matches[ 2 ] ) // ensure quotes match
							;
						else
						{
//							echo "isXPathSafe failed(4)";
							return false;
						}
					}
					else
					{
//						echo "isXPathSafe failed(3)";
						return false;
					}
				}
				else
				{
//					echo "isXPathSafe failed(2)";
					return false;
				}
			}
			elseif ( '' == $part )
				;
			elseif ( '.' == $part )
				;
			else
			{
//				echo "isXPathSafe failed(1)";
				return false;
			}
		}
//		echo "Range is safe";
		return true;
	}
	
	function makeBlockLevel( )
	{
		$this->lines = null;
		$this->words = null;
		$this->chars = null;
	}
}

?>
