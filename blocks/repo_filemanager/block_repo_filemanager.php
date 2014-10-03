<?php

class block_repo_filemanager extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_repo_filemanager');
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function get_content () {
        global $CFG, $COURSE;

        if (!has_capability('moodle/course:managefiles', get_context_instance(CONTEXT_COURSE, $COURSE->id)))
        {
            $this->content = new stdClass;
            $this->content->footer = '';
            $this->content->text = '';
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = '<a href="'.$CFG->wwwroot.'/blocks/repo_filemanager/index.php?id='.$COURSE->id.'">'.
         get_string("manage_files", "block_repo_filemanager").'</a>';

        return $this->content;
    }
}


?>