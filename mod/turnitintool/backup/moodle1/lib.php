<?php

defined('MOODLE_INTERNAL') || die();

class moodle1_mod_turnitintool_handler extends moodle1_mod_handler {

    /** @var moodle1_file_manager */
    protected $fileman = null;

    /** @var int cmid */
    protected $moduleid = null;

    public function get_paths() {

        return array(
            new convert_path( 'turnitintool', '/MOODLE_BACKUP/COURSE/MODULES/MOD/TURNITINTOOL' ),
            new convert_path( 'turnitintool_parts', '/MOODLE_BACKUP/COURSE/MODULES/MOD/TURNITINTOOL/PARTS' ),
            new convert_path( 'turnitintool_part', '/MOODLE_BACKUP/COURSE/MODULES/MOD/TURNITINTOOL/PARTS/PART' ),
            new convert_path( 'turnitintool_course', '/MOODLE_BACKUP/COURSE/MODULES/MOD/TURNITINTOOL/COURSE' ),
            new convert_path( 'turnitintool_submissions', '/MOODLE_BACKUP/COURSE/MODULES/MOD/TURNITINTOOL/SUBMISSIONS' ),
            new convert_path( 'turnitintool_submission', '/MOODLE_BACKUP/COURSE/MODULES/MOD/TURNITINTOOL/SUBMISSIONS/SUBMISSION' ),
            new convert_path( 'turnitintool_comments', '/MOODLE_BACKUP/COURSE/MODULES/MOD/TURNITINTOOL/SUBMISSIONS/SUBMISSION/COMMENTS' ),
            new convert_path( 'turnitintool_comment', '/MOODLE_BACKUP/COURSE/MODULES/MOD/TURNITINTOOL/SUBMISSIONS/SUBMISSION/COMMENTS/COMMENT' ),
        );

    }

    public function process_turnitintool($data) {

        // get the course module id and context id
        $instanceid = $data['id'];
        $cminfo     = $this->get_cminfo($instanceid);
        $moduleid   = $cminfo['id'];
        $contextid  = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

        // we now have all information needed to start writing into the file
        $this->open_xml_writer("activities/turnitintool_{$moduleid}/turnitintool.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $moduleid,
            'modulename' => 'turnitintool', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('turnitintool', array('id' => $instanceid));

        unset($data['id']); // we already write it as attribute, do not repeat it as child element
        foreach ($data as $field => $value) {
            $this->xmlwriter->full_tag($field, $value);
        }

        // prepare the file manager for this instance
        $this->fileman = $this->converter->get_file_manager($contextid, 'mod_turnitintool');

    }

    public function process_turnitintool_parts($data) {
        // Nothing to do yet
    }

    public function on_turnitintool_parts_start() {
        $this->xmlwriter->begin_tag('parts');
    }

    public function on_turnitintool_parts_end() {
        $this->xmlwriter->end_tag('parts');
    }

    public function process_turnitintool_part($data) {
        $this->write_xml('part', $data, array('/part/id'));
    }

    public function process_turnitintool_course($data) {
        $this->write_xml('course', $data, array('/course/id'));
    }

    public function process_turnitintool_submissions($data) {
        $this->fileman->filearea = 'submission';
    }

    public function on_turnitintool_submissions_start() {
        $this->xmlwriter->begin_tag('submissions');
    }

    public function on_turnitintool_submissions_end() {
        $this->xmlwriter->end_tag('submissions');
    }

    public function process_turnitintool_submission($data) {
        $this->fileman->itemid = $data['id'];
        $this->fileman->userid = $data['userid'];
        $this->fileman->migrate_directory('moddata/turnitintool/'.$data['id']);
        $this->write_xml('submission', $data, array('/submission/id'));
    }

    public function process_turnitintool_comments($data) {
        // Nothing to do yet
    }

    public function on_turnitintool_comments_start() {
        $this->xmlwriter->begin_tag('comments');
    }

    public function on_turnitintool_comments_end() {
        $this->xmlwriter->end_tag('comments');
    }

    public function process_turnitintool_comment($data) {
        $this->write_xml('comment', $data, array('/comment/id'));
    }

    public function on_turnitintool_end() {
        $this->xmlwriter->end_tag('turnitintool');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();
    }

}
