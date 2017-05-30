<?php

defined('MOODLE_INTERNAL') || die();

class local_tophat_cache {
    public static function get_students($courseid) {
        global $USER, $DB, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');
        $cache = \cache::make('local_tophat', 'students');
        $data = $cache->get($courseid);
        if (is_array($data)) {
            return $data;
        } else {
            $context = context_course::instance($courseid);
            $userfields = ['id', 'firstname', 'lastname', 'email', 'username'];
            $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
            $coursecontext = context_course::instance($courseid, IGNORE_MISSING);
            if ($courseid == SITEID) {
                $context = context_system::instance();
            } else {
                $context = $coursecontext;
            }

            if ($courseid == SITEID) {
                require_capability('moodle/site:viewparticipants', $context);
            } else {
                require_capability('moodle/course:viewparticipants', $context);
            }

            list($enrolledsql, $enrolledparams) = get_enrolled_sql($coursecontext);
            $ctxselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
            $ctxjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = u.id AND ctx.contextlevel = :contextlevel)";
            $enrolledparams['contextlevel'] = CONTEXT_USER;

            $sql = "SELECT us.*
                FROM {user} us
                JOIN (
                    SELECT DISTINCT u.id $ctxselect
                    FROM {user} u $ctxjoin
                    WHERE u.id IN ($enrolledsql)
                ) q ON q.id = us.id
                ORDER BY us.id ASC";
            $enrolledusers = $DB->get_recordset_sql($sql, $enrolledparams);
            $rolesid = local_tophat_get_roleids(['student']);
            $users = array();
            foreach ($enrolledusers as $user) {
                context_helper::preload_from_record($user);
                $isstudent = local_tophat_has_role($rolesid, $user->id, $coursecontext);
                if ($isstudent) {
                    $users[] = $user;
                }
            }
            $enrolledusers->close();
            return $users;
        }
    }
    public static function get_courses($customroles) {
        global $USER, $DB;
        $userid = $USER->id;
        $cache = \cache::make('local_tophat', 'courses');
        $data = $cache->get($userid);
        if (is_array($data)) {
            return $data;
        } else {
            $rolesid = local_tophat_get_teacher_roleids($customroles);
            $courses = enrol_get_all_users_courses($userid, false, 'summary,summaryformat');
            foreach ($courses as $id => $course) {
                context_helper::preload_from_record($course);
                $coursecontext = context_course::instance($id);
                if (!empty($rolesid) && !local_tophat_has_role($rolesid, $userid, $coursecontext)) {
                    unset($courses[$id]);
                }
            }
            $cache->set($userid, $courses);
            return $courses;
        }
    }
}
