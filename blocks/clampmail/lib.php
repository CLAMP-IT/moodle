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
 * @package   block_clampmail
 * @copyright 2013 Collaborative Liberal Arts Moodle Project
 * @copyright 2012 Louisiana State University (original Quickmail block)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

abstract class clampmail {
    public static function format_time($time) {
        return userdate($time, '%A, %d %B %Y, %I:%M %P');
    }

    public static function cleanup($table, $contextid, $itemid) {
        global $DB;

        // Clean up the files associated with this email
        // Fortunately, they are only db references, but
        // they shouldn't be there, nonetheless.
        $tablename = explode('_', $table);
        $filearea = end($tablename);

        $fs = get_file_storage();

        $fs->delete_area_files(
            $contextid, 'block_clampmail',
            'attachment_' . $filearea, $itemid
        );

        $fs->delete_area_files(
            $contextid, 'block_clampmail',
            $filearea, $itemid
        );

        return $DB->delete_records($table, array('id' => $itemid));
    }

    public static function history_cleanup($contextid, $itemid) {
        return self::cleanup('block_clampmail_log', $contextid, $itemid);
    }

    public static function draft_cleanup($contextid, $itemid) {
        return self::cleanup('block_clampmail_drafts', $contextid, $itemid);
    }

    /**
     * Process the attached file(s). If multiple files, create a zip file.
     */
    public static function process_attachments($context, $email, $table, $id) {
        global $CFG, $USER;

        $basepath = "block_clampmail/{$USER->id}";
        $moodlebase = "$CFG->tempdir/$basepath";

        if (!file_exists($moodlebase)) {
            mkdir($moodlebase, $CFG->directorypermissions, true);
        }

        $filename = $file = $actualfile = '';

        if (!empty($email->attachment)) {
            $fs = get_file_storage();
            $storedfiles = array();
            $safepath = preg_replace('/\//', "\\/", $CFG->dataroot);
            $basefilepath = preg_replace("/$safepath\\//", '', $moodlebase);

            $files = $fs->get_area_files(
                $context->id,
                'block_clampmail',
                'attachment_' . $table,
                $id,
                'id'
            );

            // Cycle through files.
            foreach ($files as $item) {
                if ($item->is_directory() && $item->get_filename() == '.') {
                    continue;
                }
                $storedfiles[$item->get_filepath().$item->get_filename()] = $item;
            }

            // Create a zip archive if more than one file.
            if (count($storedfiles) == 1) {
                $obj = current($storedfiles);
                $filename = $obj->get_filename();

                // Ensure that bad periods and ellipses are removed.
                while (preg_match( "~\\.\\.~" , $filename)) {
                    $filename = str_replace('..', '.', $filename);
                }
                $file = $basefilepath . '/' . $filename;
                $actualfile = $moodlebase . '/' . $filename;

                $obj->copy_content_to($actualfile);
            } else {
                $filename = 'attachment.zip';
                $file = $basefilepath . '/' . $filename;
                $actualfile = $moodlebase . '/' . $filename;
                $packer = get_file_packer();
                $packer->archive_to_pathname($storedfiles, $actualfile);
            }
        }
        return array($filename, $file, $actualfile);
    }

    public static function attachment_names($draft) {
        global $USER;

        $usercontext = context_user::instance($USER->id);

        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draft, 'id');

        $onlyfiles = array_filter($files, function($file) {
            return !$file->is_directory() and $file->get_filename() != '.';
        });

        $onlynames = function ($file) { return $file->get_filename();
        };

        $onlynamedfiles = array_map($onlynames, $onlyfiles);

        return implode(',', $onlynamedfiles);
    }

    public static function filter_roles($userroles, $masterroles) {
        return array_uintersect($masterroles, $userroles, function($a, $b) {
            return strcmp($a->shortname, $b->shortname);
        });
    }

    public static function load_config($courseid) {
        global $DB;

        $fields = 'name,value';
        $params = array('coursesid' => $courseid);
        $table = 'block_clampmail_config';

        $config = $DB->get_records_menu($table, $params, '', $fields);

        if (empty($config)) {
            $m = 'moodle';
            $roleselection = get_config($m, 'block_clampmail_roleselection');
            $prepender = get_config($m, 'block_clampmail_prepend_class');
            $receipt = get_config($m, 'block_clampmail_receipt');

            $config = array(
                'roleselection' => $roleselection,
                'prepend_class' => $prepender,
                'receipt' => $receipt
            );
        }

        return $config;
    }

    public static function default_config($courseid) {
        global $DB;

        $params = array('coursesid' => $courseid);
        $DB->delete_records('block_clampmail_config', $params);
    }

    public static function save_config($courseid, $data) {
        global $DB;

        self::default_config($courseid);

        foreach ($data as $name => $value) {
            $config = new stdClass;
            $config->coursesid = $courseid;
            $config->name = $name;
            $config->value = $value;

            $DB->insert_record('block_clampmail_config', $config);
        }
    }

    public static function delete_dialog($courseid, $type, $typeid) {
        global $DB, $OUTPUT;

        $email = $DB->get_record('block_clampmail_'.$type, array('id' => $typeid));

        if (empty($email)) {
            print_error('not_valid_typeid', 'block_clampmail', '', $typeid);
        }

        $params = array('courseid' => $courseid, 'type' => $type);
        $yesparams = $params + array('typeid' => $typeid, 'action' => 'confirm');

        $optionyes = new moodle_url('/blocks/clampmail/emaillog.php', $yesparams);
        $optionno = new moodle_url('/blocks/clampmail/emaillog.php', $params);

        $table = new html_table();
        $table->head = array(get_string('date'), get_string('subject', 'block_clampmail'));
        $table->data = array(
            new html_table_row(array(
                new html_table_cell(self::format_time($email->time)),
                new html_table_cell($email->subject))
            )
        );

        $msg = get_string('delete_confirm', 'block_clampmail', html_writer::table($table));

        $html = $OUTPUT->confirm($msg, $optionyes, $optionno);
        return $html;
    }

    public static function list_entries($courseid, $type, $page, $perpage, $userid, $count, $candelete) {
        global $DB, $OUTPUT;

        $dbtable = 'block_clampmail_'.$type;

        $table = new html_table();

        $params = array('courseid' => $courseid, 'userid' => $userid);
        $logs = $DB->get_records($dbtable, $params,
            'time DESC', '*', $page * $perpage, $perpage * ($page + 1));

        $table->head = array(get_string('date'), get_string('subject', 'block_clampmail'),
            get_string('attachment', 'block_clampmail'), get_string('action'));

        $table->data = array();

        foreach ($logs as $log) {
            $date = self::format_time($log->time);
            $subject = $log->subject;
            $attachments = $log->attachment;

            $params = array(
                'courseid' => $log->courseid,
                'type' => $type,
                'typeid' => $log->id
            );

            $actions = array();

            // Open link.
            $actions[] = html_writer::link(
                new moodle_url('/blocks/clampmail/email.php', $params),
                $OUTPUT->pix_icon('i/search', get_string('open_email', 'block_clampmail'))
            );

            if ($candelete) {
                // Delete link.
                $actions[] = html_writer::link (
                    new moodle_url('/blocks/clampmail/emaillog.php',
                        $params + array('action' => 'delete')
                    ),
                    $OUTPUT->pix_icon("t/delete", get_string('delete_email', 'block_clampmail'))
                );
            }

            $table->data[] = array($date, $subject, $attachments, implode(' ', $actions));
        }

        $paging = $OUTPUT->paging_bar($count, $page, $perpage,
            '/blocks/clampmail/emaillog.php?type='.$type.'&amp;courseid='.$courseid);

        $html = $paging;
        $html .= html_writer::table($table);
        $html .= $paging;
        return $html;
    }

    public static function get_signatures($userid) {
        global $DB;
        $signatures = $DB->get_records('block_clampmail_signatures',
            array('userid' => $userid), 'default_flag DESC');
        return $signatures;
    }
}

function block_clampmail_pluginfile($course, $record, $context, $filearea, $args, $forcedownload) {
    $fs = get_file_storage();
    global $DB;

    list($itemid, $filename) = $args;
    $params = array(
        'component' => 'block_clampmail',
        'filearea' => $filearea,
        'itemid' => $itemid,
        'filename' => $filename
    );

    $instanceid = $DB->get_field('files', 'id', $params);

    if (empty($instanceid)) {
        send_file_not_found();
    } else {
        $file = $fs->get_file_by_id($instanceid);
        send_stored_file($file);
    }
}
