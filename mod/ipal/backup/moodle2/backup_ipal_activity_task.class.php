<?php
 
require_once($CFG->dirroot . '/mod/ipal/backup/moodle2/backup_ipal_stepslib.php');
require_once($CFG->dirroot . '/mod/ipal/backup/moodle2/backup_ipal_settingslib.php');
 
class backup_ipal_activity_task extends backup_activity_task {
 
    protected function define_my_settings() {
    }
 
    protected function define_my_steps() {
        $this->add_step(new backup_ipal_activity_structure_step('ipal_structure', 'ipal.xml'));
    }
 
    static public function encode_content_links($content) {
        return $content;
    }
}
