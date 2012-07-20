<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Define all the backup steps that will be used by the backup_nanogong_activity_task
 *
 * @author     Ning
 * @package    mod
 * @subpackage nanogong
 * @copyright  2012 The Gong Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2
 */
 
 
 /**
 * Define the complete nanogong structure for backup, with file and id annotations
 */     
class backup_nanogong_activity_structure_step extends backup_activity_structure_step {
 
    protected function define_structure() {
 
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
 
        // Define each element separated
        $nanogong = new backup_nested_element('nanogong', array('id'), array(
            'course', 'name', 'intro', 'introformat', 'timeavailable',
            'timedue', 'grade', 'maxduration', 'maxnumber', 'preventlate',
            'permission', 'timecreated', 'timemodified'));
 
        $messages = new backup_nested_element('messages');
 
        $message = new backup_nested_element('message', array('id'), array(
            'userid', 'message', 'supplement', 'supplementformat',
            'audio', 'comments', 'commentsformat',
            'commentedby', 'grade', 'timestamp', 'locked'));
            
        $audios = new backup_nested_element('audios');
 
        $audio = new backup_nested_element('audio', array('id'), array(
            'userid', 'type', 'title', 'name', 'timecreated'));

 
        // Build the tree
        $nanogong->add_child($messages);
        $messages->add_child($message);
        $nanogong->add_child($audios);
        $audios->add_child($audio);
 
        // Define sources
        $nanogong->set_source_table('nanogong', array('id' => backup::VAR_ACTIVITYID));
 
        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            //$message->set_source_table('nanogong_messages', array('nanogongid' => backup::VAR_PARENTID));
            $message->set_source_sql('
                SELECT *
                  FROM {nanogong_messages}
                  WHERE nanogongid = ?',
                array(backup::VAR_PARENTID));
            $audio->set_source_table('nanogong_audios', array('nanogongid' => backup::VAR_PARENTID));
        }
 
        // Define id annotations
        $nanogong->annotate_ids('scale', 'grade');
        $message->annotate_ids('user', 'userid');
        $message->annotate_ids('user', 'commentedby');
        $audio->annotate_ids('user', 'userid');
 
        // Define file annotations
        $nanogong->annotate_files('mod_nanogong', 'intro', null); // This file area hasn't itemid
        $message->annotate_files('mod_nanogong', 'message', 'id');
        $nanogong->annotate_files('mod_nanogong', 'audio', 'id');
 
        // Return the root element (nanogong), wrapped into standard activity structure
        return $this->prepare_activity_structure($nanogong); 
    }
}
