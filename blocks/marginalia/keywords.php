<?php

/*
 * keywords.php
 * Handles annotation keyword requests
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
 * $Id: keywords.php 543 2012-06-21 05:56:48Z geof.glass $
 */

require_once( "../../config.php" );
require_once( 'config.php' );
require_once( 'marginalia-php/Keyword.php' );
require_once( 'marginalia-php/KeywordService.php' );
require_once( 'marginalia-php/MarginaliaHelper.php' );
require_once( 'keywords_db.php' );
require_once( 'moodle_marginalia.php' );

require_login();

class moodle_keyword_service extends KeywordService
{
	function moodle_keyword_service( $userid )
	{
		global $CFG;
		$moodlemia = moodle_marginalia::get_instance( );
		KeywordService::KeywordService( 
			$moodlemia->get_host(),
			$moodlemia->get_keyword_service_path(),
			$userid,
			$CFG->wwwroot );
		$this->tablePrefix = $CFG->prefix;
	}
	
	function doListKeywords( )
	{
		$keywords = annotation_keywords_db::list_keywords( $this->currentUserId );
		$logurl = 'keywords.php';
//		add_to_log( null, 'annotation', 'list', $logurl );
		return $keywords;                              
	}
	
	/**
	 * Because keywords are automatically generated from margin notes,
	 * they cannot be created, updated, or deleted, nor is there any reason
	 * to fetch them individually.
	 */
	function doGetKeyword( $name )
	{
		header( 'HTTP/1.1 501 Not Implemented' );
		echo "Individual keywords cannot be fetched";
		return False;
	}
	
	function doCreateKeyword( $keyword )
	{
		header( 'HTTP/1.1 501 Not Implemented' );
		echo "Keywords are automatically generated";
		return False;
	}
	
	function doUpdateKeyword( $keyword )
	{
		header( 'HTTP/1.1 501 Not Implemented' );
		echo "Keywords are automatically generated";
		return False;
	}
	
	function doDeleteKeyword( $name )
	{
		header( 'HTTP/1.1 501 Not Implemented' );
		echo "Keywords are automatically generated";
		return False;
	}
}

if ( AN_USEKEYWORDS )  {
	$service = new moodle_keyword_service( isguestuser() ? null : $USER->id );
	$service->dispatch( );
}
else  {
	header( 'HTTP/1.1 501 Not Implemented' );
	echo "This Moodle installation does not support keywords";
}

?>
