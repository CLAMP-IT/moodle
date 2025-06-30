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
 * Privacy functions.
 *
 * @package   block_clampmail
 * @copyright 2018 Collaborative Liberal Arts Moodle Project
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_clampmail\privacy;

defined('MOODLE_INTERNAL') || die();

use context_user;
use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\helper;
use \core_privacy\local\request\transform;
use \core_privacy\local\request\userlist;
use \core_privacy\local\request\approved_userlist;

interface my_userlist extends \core_privacy\local\request\core_userlist_provider {
}

/**
 * Privacy functions.
 *
 * @package   block_clampmail
 * @copyright 2018 Collaborative Liberal Arts Moodle Project
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        // This plugin does store personal user data.
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\metadata\provider,
        my_userlist {

    use \core_privacy\local\legacy_polyfill;

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $collection A reference to the collection to use to store the metadata.
     *
     * @return collection The updated collection of metadata items.
     */
    public static function _get_metadata(collection $collection) {
        $messagefields = [
            'userid' => 'privacy:metadata:clampmail_message:userid',
            'mailto' => 'privacy:metadata:clampmail_message:mailto',
            'subject' => 'privacy:metadata:clampmail_message:subject',
            'message' => 'privacy:metadata:clampmail_message:message',
            'time' => 'privacy:metadata:clampmail_message:time',
        ];

        $collection->add_database_table(
            'block_clampmail_log',
            $messagefields,
            'privacy:metadata:clampmail_log'
        );

        $collection->add_database_table(
            'block_clampmail_drafts',
            $messagefields,
            'privacy:metadata:clampmail_drafts'
        );

        $collection->add_database_table(
            'block_clampmail_signatures',
            [
                'userid' => 'privacy:metadata:clampmail_signatures:userid',
                'title' => 'privacy:metadata:clampmail_signatures:title',
                'signature' => 'privacy:metadata:clampmail_signatures:signature',
            ],
            'privacy:metadata:clampmail_signatures'
        );

        return $collection;
    }

    /**
     * Find all courses which have a CLAMPMail block and underlying user data.
     * This can include sent items (logs), drafts, and signatures.
     *
     * @param int $userid the user id
     *
     * @return void
     */
    public static function _get_contexts_for_userid($userid) {
        global $DB;

        $contextlist = new \core_privacy\local\request\contextlist();

        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {block_instances} bi ON bi.parentcontextid = c.id
             LEFT JOIN {block_clampmail_log} bcl ON bcl.courseid = c.instanceid
             LEFT JOIN {block_clampmail_drafts} bcd ON bcd.courseid = c.instanceid
                 WHERE bi.blockname = 'clampmail'
                   AND (
                       bcl.userid = :loguserid OR
                       bcd.userid = :draftuserid
                   )";
        $params = [
            'loguserid' => $userid,
            'draftuserid' => $userid
        ];
        $contextlist->add_from_sql($sql, $params);
        // And we also store signatures by user context -- check if there are any.
        $signatures = $DB->get_records('block_clampmail_signatures', array('userid' => $userid));
        if (count($signatures) > 0) {
            $contextlist->add_user_context($userid);
        }
        return $contextlist;
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $context = $userlist->get_context();

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        if ($context->contextlevel === CONTEXT_COURSE) {
            static::delete_course_context_data($context->instanceid, $userlist->get_userids());
        } else if ($context->contextlevel === CONTEXT_USER) {
            static::delete_user_context_data($userlist->get_userids());
        }
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     *
     * @return void
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context instanceof \context_user) {
            $userid = $context->instanceid;

            // Does user have data in user context (signatures)?
            $sql = "SELECT COUNT(*)
            FROM {user} u
       LEFT JOIN {block_clampmail_signatures} bcs ON bcs.userid = u.id
           WHERE u.id = ?
             AND bcs.userid IS NOT NULL";

            $check = $DB->count_records_sql($sql, array($userid));

            if ($check > 0) {
                $userlist->add_user($userid);
            }
        } else if ($context instanceof \context_course) {
            $courseid = $context->instanceid;

            // Find users who have data in this course context.
            $sql = "SELECT u.id
            FROM {user} u
       LEFT JOIN {block_clampmail_log} bcl ON bcl.userid = u.id
       LEFT JOIN {block_clampmail_drafts} bcd ON bcd.userid = u.id
           WHERE (
                bcl.courseid = ?
             OR bcd.courseid = ? )
             AND (
                bcl.userid IS NOT NULL
                OR bcd.userid IS NOT NULL)";

            $results = $DB->get_records_sql($sql, array($courseid, $courseid));
            $userids = array();
            array_map(function($e) use (&$userids) {
                array_push($userids, $e->id);
            }, $results);

            $userlist->add_users($userids);
        }
    }

    /**
     * Export personal data for the given approved_contextlist.
     * User and context information is contained within the contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     *
     * @return void
     */
    public static function _export_user_data(approved_contextlist $contextlist) {
        global $DB;

        // If no contexts, bail out.
        if (empty($contextlist->get_contextids())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        // Get all courses where the user has CLAMPMail data.
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $sql = "SELECT
                    ctx.id AS contextid,
                    c.*,
                    bcl.userid as logs,
                    bcd.userid as drafts
                  FROM {context} ctx
                  JOIN {block_instances} bi ON bi.parentcontextid = ctx.id
                  JOIN {course} c ON c.id = ctx.instanceid
             LEFT JOIN {block_clampmail_log} bcl ON bcl.courseid = ctx.instanceid
             LEFT JOIN {block_clampmail_drafts} bcd ON bcd.courseid = ctx.instanceid
                 WHERE (
                    ctx.id {$contextsql}
                   )
        ";
        $params = array();
        $params += $contextparams;

        // Export sent messages and drafts (course context).
        $courses = $DB->get_recordset_sql($sql, $params);
        foreach ($courses as $course) {
            $context = \context::instance_by_id($course->contextid);

            $sent = static::get_messages('log', $userid, $course->id);
            $drafts = static::get_messages('drafts', $userid, $course->id);

            $data = (object) array(
                'sent_messages' => $sent,
                'drafts' => $drafts,
            );

            $writer = writer::with_context($context);
            $writer->export_data([get_string('pluginname', 'block_clampmail')], $data);
            $writer->export_metadata(
                [get_string('pluginname', 'block_clampmail')],
                "sent_messages",
                "Description:",
                get_string('privacy:metadata:clampmail_log', 'block_clampmail'));
            $writer->export_metadata(
                [get_string('pluginname', 'block_clampmail')],
                "drafts",
                "Description:",
                get_string('privacy:metadata:clampmail_drafts', 'block_clampmail'));
        }
        $courses->close();

        // Export signatures (user context).
        $context = context_user::instance($userid);
        if (in_array($context->id, $contextlist->get_contextids())) {
            $signatures = static::get_signatures($userid);
            $data = (object) [
                'signatures' => $signatures,
            ];
            $writer = writer::with_context($context);
            $writer->export_data([get_string('pluginname', 'block_clampmail')], $data);
            $writer->export_metadata(
                [get_string('pluginname', 'block_clampmail')],
                "signatures",
                "Description:",
                get_string('privacy:metadata:clampmail_signatures', 'block_clampmail'));
        }
    }

    /**
     * Delete all user data which matches the specified deletion criteria.
     *
     * @param context $context The specific context to delete data for.
     *
     * @return void
     */
    public static function _delete_data_for_all_users_in_context(\context $context) {
        if ($context->contextlevel === CONTEXT_USER) {
            // Instanceid is userid.
            static::delete_user_context_data($context->instanceid);
        } else if ($context->contextlevel === CONTEXT_COURSE) {
            // Instanceid is courseid.
            static::delete_course_context_data($context->instanceid);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     *
     * @return void
     */
    public static function _delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        // If no contexts, bail out.
        if (empty($contextlist)) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        // Get all courses where the user has CLAMPMail data.
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        $sql = "SELECT
                    ctx.id AS contextid,
                    c.*,
                    bcl.userid as logs,
                    bcd.userid as drafts
                  FROM {context} ctx
                  JOIN {block_instances} bi ON bi.parentcontextid = ctx.id
                  JOIN {course} c ON c.id = ctx.instanceid
             LEFT JOIN {block_clampmail_log} bcl ON bcl.courseid = ctx.instanceid
             LEFT JOIN {block_clampmail_drafts} bcd ON bcd.courseid = ctx.instanceid
                 WHERE (
                    ctx.id {$contextsql}
                   )
        ";
        $params = array();
        $params += $contextparams;

        // Delete sent messages and drafts (course context).
        $courses = $DB->get_recordset_sql($sql, $params);
        foreach ($courses as $course) {
            static::delete_course_context_data($course->id, $userid);
        }
        $courses->close();

        // Delete signatures (user context).
        $context = context_user::instance($userid);
        if (in_array($context->id, $contextlist->get_contextids())) {
            static::delete_user_context_data($userid);
        }
    }

    /**
     * Retrieve signature data for a user.
     *
     * @param int $userid The user to retrieve for.
     *
     * @return object Signature data.
     */
    protected static function get_signatures($userid) {
        global $DB;

        $sigs = $DB->get_records('block_clampmail_signatures', array(
                'userid' => $userid,
            )
        );

        $signatures = array();
        foreach ($sigs as $sig) {
            $signature = (object) [
                'title' => format_string($sig->title, true),
                'signature' => $sig->signature,
            ];
            array_push($signatures, $signature);
        }
        return (object) $signatures;
    }

    /**
     * Retrieve message data (logs and drafts) for a user.
     *
     * @param string $type [logs|drafts] Defines which type of message we're getting (sent or draft).
     * @param int $userid The userid to get message data for.
     * @param int $courseid The courseid to get message data for.
     *
     * @return object Message data.
     */
    protected static function get_messages($type, $userid, $courseid) {
        global $DB;

        $records = $DB->get_records('block_clampmail_' . $type, array(
                'courseid' => $courseid,
                'userid' => $userid,
            )
        );

        $messages = array();
        foreach ($records as $record) {
            $mailtoids = explode(',', $record->mailto);
            $mailto = array();
            foreach ($mailtoids as $mid) {
                array_push($mailto, transform::user($mid));
            }
            $mailto = implode(',', $mailto);

            $message = (object) [
                'subject' => format_string($record->subject, true),
                'mailto' => $mailto,
                'message' => $record->message,
                'time' => transform::datetime($record->time),
            ];
            array_push($messages, $message);
        }
        return (object) $messages;
    }

    /**
     * Delete data in the user context (signatures).
     * Can accept an array of multiple user ids.
     *
     * @param int $userid User(s) to delete for.
     *
     * @return void
     */
    protected static function delete_user_context_data($userid) {
        global $DB;

        $select = '';
        if (is_numeric($userid)) {
            $select .= "userid = $userid";
        } else if (is_array($userid)) {
            $userids = implode(',', $userid);
            $select .= "userid IN ($userids)";
        }

        $DB->delete_records_select('block_clampmail_signatures', $select);
    }

    /**
     * Delete data in the course context (logs and drafts).
     * Can be restricted by user id or ids (pass an int or array of ids as the second argument).
     *
     * @param int $courseid The course id of the context.
     * @param int|array|null $userid The user id(s) to filter by.
     *
     * @return void
     */
    protected static function delete_course_context_data($courseid, $userid = null) {
        global $DB;

        $select = "courseid = $courseid";

        if (is_numeric($userid)) {
            $select .= " AND userid = $userid";
        } else if (is_array($userid)) {
            $userids = implode(',', $userid);
            $select .= " AND userid IN ($userids)";
        }

        $DB->delete_records_select('block_clampmail_log', $select);
        $DB->delete_records_select('block_clampmail_drafts', $select);
    }
}
