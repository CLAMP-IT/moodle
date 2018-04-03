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

interface clampmail_alternate_actions {
    const VIEW = 'view';
    const DELETE = 'delete';
    const INTERACT = 'interact';
    const CONFIRMED = 'confirmed';
    const INFORMATION = 'inform';
    const VERIFY = 'verify';
}

abstract class clampmail_alternate implements clampmail_alternate_actions {

    private static function base_url($courseid, $additional= array()) {
        $params = array('courseid' => $courseid) + $additional;
        return new moodle_url('/blocks/clampmail/alternate.php', $params);
    }

    public static function get($course) {
        global $DB;

        $params = array('courseid' => $course->id);
        return $DB->get_records('block_clampmail_alternate', $params, 'valid DESC');
    }

    public static function get_one($id) {
        global $DB;

        $params = array('id' => $id);
        return $DB->get_record('block_clampmail_alternate', $params, '*', MUST_EXIST);
    }

    public static function delete($course, $id) {
        global $OUTPUT;

        $email = self::get_one($id);

        $confirmurl = self::base_url($course->id, array(
            'id' => $email->id, 'action' => self::CONFIRMED
        ));

        $cancelurl = self::base_url($course->id);

        return $OUTPUT->confirm(get_string('alternate_delete_confirm', 'block_clampmail', $email), $confirmurl, $cancelurl);
    }

    public static function confirmed($course, $id) {
        global $DB;

        $DB->delete_records('block_clampmail_alternate', array('id' => $id));

        return redirect(self::base_url($course->id, array('flash' => 1)));
    }

    public static function verify($course, $id) {
        global $DB, $OUTPUT;

        $entry = self::get_one($id);

        $value = optional_param('key', null, PARAM_TEXT);
        $userid = optional_param('activator', null, PARAM_INT);

        $params = array(
            'instance' => $course->id,
            'value' => $value,
            'userid' => $userid,
            'script' => 'blocks/clampmail'
        );

        $backurl = self::base_url($course->id);

        // Pass through already valid entries.
        if ($entry->valid) {
            redirect($backurl);
        }

        // Verify key.
        if (empty($value) or !$DB->get_record('user_private_key', $params)) {
            $reactivate = self::base_url($course->id, array(
                'id' => $id, 'action' => self::INFORMATION
            ));

            $html = $OUTPUT->notification(get_string('alternate_invalid', 'block_clampmail', $entry));
            $html .= $OUTPUT->continue_button($reactivate);
            return $html;
        }

        // One at a time...They can resend the link if they want.
        delete_user_key('blocks/clampmail', $userid);

        $entry->valid = 1;
        $DB->update_record('block_clampmail_alternate', $entry);

        $entry->course = $course->fullname;

        $html = $OUTPUT->notification(get_string('alternate_activated', 'block_clampmail', $entry), 'notifysuccess');
        $html .= $OUTPUT->continue_button($backurl);

        return $html;
    }

    public static function inform($course, $id) {
        global $OUTPUT, $USER;

        $entry = self::get_one($id);

        // No restriction.
        // Valid forever.
        $value = get_user_key('blocks/clampmail', $USER->id, $course->id);

        $url = self::base_url($course->id);

        $approvalurl = self::base_url($course->id, array(
            'id' => $id, 'action' => self::VERIFY,
            'activator' => $USER->id, 'key' => $value
        ));

        $a = new stdClass;
        $a->address = $entry->address;
        $a->url = html_writer::link($approvalurl, $approvalurl->out());
        $a->course = $course->fullname;
        $a->fullname = fullname($USER);

        $from = get_string('alternate_from', 'block_clampmail');
        $subject = get_string('alternate_subject', 'block_clampmail');
        $htmlbody = get_string('alternate_body', 'block_clampmail', $a);
        $body = strip_tags($htmlbody);

        // Send email.
        $user = clone($USER);
        $user->email = $entry->address;
        $user->firstname = get_string('pluginname', 'block_clampmail');
        $user->lastname = get_string('alternate', 'block_clampmail');

        $result = email_to_user($user, $from, $subject, $body, $htmlbody);

        // Create the event, trigger it.
        $event = \block_clampmail\event\alternate_email_added::create(array(
            'courseid' => $course->id,
            'context' => context_course::instance($course->id),
            'other'    => array(
                'address' => $entry->address
            )
        ));
        $event->trigger();

        $html = $OUTPUT->box_start();

        if ($result) {
            $html .= $OUTPUT->notification(get_string('alternate_saved', 'block_clampmail', $entry), 'notifysuccess');
            $html .= html_writer::tag('p', get_string('alternate_success', 'block_clampmail', $entry));
        } else {
            $html .= $OUTPUT->notification(get_string('alternate_failure', 'block_clampmail', $entry));
        }

        $html .= $OUTPUT->continue_button($url);
        $html .= $OUTPUT->box_end();

        return $html;
    }

    public static function interact($course, $id) {
        $form = new block_clampmail\alternate_form(null, array(
            'course' => $course, 'action' => self::INTERACT
        ));

        if ($form->is_cancelled()) {
            redirect(self::base_url($course->id));
        } else if ($data = $form->get_data()) {
            global $DB;

            // Check if email exists in this course.
            $older = $DB->get_record('block_clampmail_alternate', array(
                'address' => $data->address, 'courseid' => $data->courseid
            ));

            if ($older) {
                $data->id = $older->id;
                $data->valid = $older->valid;
            } else if (!empty($data->id)) {
                // Changed address?
                if ($data->valid) {
                    $older = self::get_one($id);

                    $valid = $older->address != $data->address ? 0 : 1;

                    $data->valid = $valid;
                }

                $DB->update_record('block_clampmail_alternate', $data);
            } else {
                unset($data->id);
                $data->id = $DB->insert_record('block_clampmail_alternate', $data);
            }

            $action = $data->valid ? self::VERIFY : self::INFORMATION;

            redirect(self::base_url($course->id, array(
                'action' => $action, 'id' => $data->id
            )));
        }

        if ($id) {
            $form->set_data(self::get_one($id));
        }

        return $form->render();
    }

    public static function view($course) {
        global $OUTPUT;

        $alternates = self::get($course);

        $newurl = self::base_url($course->id, array('action' => self::INTERACT));

        if (empty($alternates)) {

            $html = $OUTPUT->notification(get_string('no_alternates', 'block_clampmail', $course));
            $html .= $OUTPUT->continue_button($newurl);
            return $html;
        }

        $table = new html_table();
        $table->head = array(
            get_string('email'),
            get_string('alternate_activation_status', 'block_clampmail'),
            get_string('action')
        );

        $approval = array(get_string('alternate_waiting', 'block_clampmail'),
            get_string('alternate_approved', 'block_clampmail'));

        $icons = array(
            self::INTERACT => $OUTPUT->pix_icon('i/edit', get_string('edit')),
            self::DELETE => $OUTPUT->pix_icon('t/delete', get_string('delete'))
        );

        foreach ($alternates as $email) {
            $editurl = self::base_url($course->id, array(
                'action' => self::INTERACT, 'id' => $email->id
            ));

            $edit = html_writer::link($editurl, $icons[self::INTERACT]);

            $deleteurl = self::base_url($course->id, array(
                'action' => self::DELETE, 'id' => $email->id
            ));

            $delete = html_writer::link($deleteurl, $icons[self::DELETE]);

            $row = array(
                $email->address,
                $approval[$email->valid],
                implode(' | ', array($edit, $delete))
            );

            $table->data[] = new html_table_row($row);
        }

        $newlink = html_writer::link($newurl, get_string('alternate_new', 'block_clampmail'));

        $html = html_writer::tag('div', $newlink, array('class' => 'new_link'));
        $html .= $OUTPUT->box_start();
        $html .= html_writer::table($table);
        $html .= $OUTPUT->box_end();
        return $html;
    }
}
