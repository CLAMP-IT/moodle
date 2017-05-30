<?php

defined('MOODLE_INTERNAL') || die();

class local_tophat_capability {
    public static function can_view_grade($courseid) {
        $context = context_course::instance($courseid);
        require_capability('moodle/grade:viewall', $context);
    }

    public static function can_manage_grade($courseid) {
        $context = context_course::instance($courseid);
        require_capability('moodle/grade:manage', $context);
    }
}
