<?php
class restore_ipal_activity_structure_step extends restore_activity_structure_step {
 
    protected function define_structure() {
 
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');
 
        $paths[] = new restore_path_element('ipal', '/activity/ipal');
        $paths[] = new restore_path_element('answer', '/activity/ipal/answered/answer');
        if ($userinfo) {
            $paths[] = new restore_path_element('answered_archive_element', '/activity/ipal/answered_archive/answered_archive_element');
        }
 

        return $this->prepare_activity_structure($paths);
    }
 
    protected function process_ipal($data) {
        global $DB;
 
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
 
        $data->timecreated = $this->apply_date_offset($data->timecreated);
 
     
        $newitemid = $DB->insert_record('ipal', $data);
        $this->apply_activity_instance($newitemid);
    }
 
    protected function process_answer($data) {
        global $DB;
 
        $data = (object)$data;
        $oldid = $data->id;
 
        $data->ipalid = $this->get_new_parentid('ipal');
        $data->time_created = $this->apply_date_offset($data->time_created);
 
        $data->ipal_id = $this->get_new_parentid('ipal');
        $data->quiz_id = $this->get_new_parentid('ipal');
        $data->class_id = $this->get_courseid();
        $data->ipal_code = "0";

        $newitemid = $DB->insert_record('ipal_answered', $data);
        $this->set_mapping('answers', $oldid, $newitemid);
    }
 
    protected function process_answered_archive_element($data) {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;
        
        $data->ipalid = $this->get_new_parentid('ipal');
        $data->time_created = $this->apply_date_offset($data->time_created);
        $data->class_id = $this->get_courseid();
        
        $data->ipal_id = $this->get_new_parentid('ipal');
        $data->quiz_id = $this->get_new_parentid('ipal');
        
        $newitemid = $DB->insert_record('ipal_answered_archive', $data);
        $this->set_mapping('answered_archive_element', $oldid, $newitemid);
    }
 
    protected function after_execute() {
        $this->add_related_files('mod_ipal', 'intro', null);
    }
}
