<?php

/*
 * blocks/marginalia/upgrade.php
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
 
global $CFG;

require_once( $CFG->dirroot.'/blocks/marginalia/config.php' );
require_once( ANNOTATION_DIR.'/lib.php' );
require_once( ANNOTATION_DIR.'/marginalia-php/Annotation.php' );
require_once( ANNOTATION_DIR.'/marginalia-php/MarginaliaHelper.php' );
require_once( ANNOTATION_DIR.'/marginalia-php/SequenceRange.php' );
require_once( ANNOTATION_DIR.'/marginalia-php/XPathRange.php' );
	
function xmldb_block_marginalia_upgrade( $oldversion )
{
	global $CFG, $DB;
	$result = true;
	
	$dbman = $DB->get_manager( );
	
	if ( $oldversion < 2008121000 )  {
		
		/// Define table annotation to be created
		$table = new xmldb_table('marginalia');
		
		/// Adding fields to table marginalia
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('access_perms', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('url', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
		$table->add_field('start_block', XMLDB_TYPE_CHAR, '255', null, null, null, null);
		$table->add_field('start_xpath', XMLDB_TYPE_CHAR, '255', null, null, null, null);
		$table->add_field('start_line', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('start_word', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('start_char', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('end_block', XMLDB_TYPE_CHAR, '255', null, null, null, null);
		$table->add_field('end_xpath', XMLDB_TYPE_CHAR, '255', null, null, null, null);
		$table->add_field('end_line', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('end_word', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('end_char', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('note', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
		$table->add_field('quote', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
		$table->add_field('quote_title', XMLDB_TYPE_CHAR, '255', null, null, null, null);
		$table->add_field('quote_author_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('action', XMLDB_TYPE_CHAR, '30', null, null, null, null);
		$table->add_field('link', XMLDB_TYPE_CHAR, '255', null, null, null, null);
		$table->add_field('link_title', XMLDB_TYPE_CHAR, '255', null, null, null, null);
		$table->add_field('created', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('modified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('object_type', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('object_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		
		/// Adding keys to table marginalia
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		
		/// Adding indexes to table marginalia
		$table->add_index('object', XMLDB_INDEX_NOTUNIQUE, array('object_type', 'object_id'));
		
		/// Launch create table for marginalia
		$dbman->create_table($table);
		
		// Check for old annotation table and convert its data over
		if ( $result )  {
			$query = "SELECT a.*, u.id AS uid, qa.id as aid "
				." FROM {annotation} a"
				." JOIN {user} u ON u.username=a.userid"
				." LEFT JOIN {user} qa ON qa.username=a.quote_author";
			$data = $DB->get_recordset_sql( $query );
			foreach ( $data as $r )  {
/*					// This method and the range classes are clever enough to handle
				// ranges in the old format.
				if ( array_key_exists( 'start_block', $r ) && $r->start_block !== null )  {
					$r->start_block = preg_replace( '/^\//', '', $r->start_block );
					$r->start_block = preg_replace( '/\//', '\.', $r->start_block );
				}
				if ( array_key_exists( 'end_block', $r ) && $r->end_block !== null )  {
					$r->end_block = preg_replace( '/^\//', '', $r->end_block );
					$r->end_block = preg_replace( '/\//', '\.', $r->end_block );
				}
*/
				$r->username = $r->userid;
				$r->userid = 0;
				$r->quote_author_username = $r->quote_author;

				$annotation = annotation_globals::record_to_annotation( $r );

				// Will handle 'public' or 'private'
				if ( array_key_exists( 'access', $r) )
					$annotation->setAccess( $r->access );
				
				// Fix backslashes (\' and \") in annotation and note
				if ( AN_DBFIXBACKSLASHES )
				{
					$quote = $annotation->getQuote( );
					$quote = preg_replace( '/\\\\\'/', "'", $quote );
					$quote = preg_replace( '/\\\\"/', '"', $quote );
					$annotation->setQuote( $quote );
					
					$note = $annotation->getNote( );
					$note = preg_replace( '/\\\\\'/', "'", $note );
					$note = preg_replace( '/\\\\"/', '"', $note );
					$annotation->setNote( $note );
				}
				
				$record = annotation_globals::annotation_to_record( $annotation );
				
				// Make sure start_line and end_line are not null
				if ( ! array_key_exists( 'start_line', $r ) )
					$record->start_line = 0;
				if ( ! array_key_exists( 'end_line', $r ) )
					$record->end_line = 0;

				$record->userid = $r->uid;
				$record->quote_author_id = $r->aid;
				$record->object_type = AN_OTYPE_POST;
				$record->object_id = $r->object_id;

				$DB->insert_record( AN_DBTABLE, $record, true );
			}
			$data->close( );
		}
		upgrade_mod_savepoint(true, 2008121000, 'marginalia');
		
		// Should perhaps delete old annotation table?
	}
	
	if ( $oldversion < 2010012800 )  {
		$table = new xmldb_table( 'marginalia' );
		
		/* ---- course ---- */
		// Give in to Moodle's architecture and associate each annotation with a course
		// (I don't like that Moodle is course-centric: I think it should be user-centric instead.
		// I see annotations as belonging to the people who create them, not the things they
		// annotate or the courses they were created in.  A student over the course of his or her
		// career accumulates knowledge and content, synthesizing it into new knowledge and practice.
		// In contrast, a course-oriented system is rooted in administrative structure.)
		$field = new xmldb_field( 'course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id' );
        if (!$dbman->field_exists($table,$field)) {
            $dbman->add_field( $table, $field );
        }
        /*
		$query = "UPDATE {marginalia} a, {forum_posts} p, {forum_discussions} d"
			." SET a.course=d.course"
			." WHERE a.object_id=p.id AND d.id=p.discussion";
        */
		$query = "UPDATE {marginalia}
                     SET course = 
                                 (SELECT d.course 
                                    FROM {forum_posts} p, {forum_discussions} d 
                                   WHERE d.id=p.discussion 
                                     AND {marginalia}.object_id=p.id
                                 )
                   WHERE EXISTS  (SELECT fd.id 
                                    FROM {forum_posts} fp, {forum_discussions} fd 
                                   WHERE fd.id=fp.discussion 
                                     AND {marginalia}.object_id=fp.id
                                 )";
		$DB->execute( $query );

        // Cannot have entries where cousrse = 0
        $deletequery = "DELETE FROM {marginalia} WHERE course=0";
		$DB->execute($deletequery);

		/* ---- sheet_type ---- */
		// Replace access_perms with sheet_type
		// private used to have access bits 0x0;  now has access 0x1
		$field = new xmldb_field( 'access_perms', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field( $table, $field, 'sheet_type');
        }
		$query = "UPDATE {marginalia} SET sheet_type=1 WHERE sheet_type=0";
		$result = $DB->execute( $query );

		/* ---- marginalia_read.* ---- */
		/// Define marginalia_read table, which tracks which annotations have been read by which users
		$table = new xmldb_table('marginalia_read');
		
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('annotationid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('firstread', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
		$table->add_field('lastread', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

		/// Adding keys to table marginalia_read
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

		/// Adding indexes to table marginalia_read
		$table->add_index('object', XMLDB_INDEX_UNIQUE, array('annotationid', 'userid'));
		
        if (!$dbman->table_exists($table)) {
            $dbman->create_table( $table );
        }
		
		/* ---- user_preferences.* ---- */
		// Delete obsolete preferences
	    $DB->delete_records('user_preferences', array( 'name' => 'annotations.user'));
	    $DB->delete_records('user_preferences', array( 'name' => 'smartcopy'));
	    
	    upgrade_block_savepoint(true, 2010121800, 'marginalia' );
	}
	
	return $result;
}

