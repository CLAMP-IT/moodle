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

namespace block_clampmail;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

class email_form extends \moodleform {
    /**
     * Moves users into the selected select box.
     * @return string
     */
    private function reduce_users($in, $user) {
        return $in . $this->display_option($user);
    }

    /**
     * Return formatted user display for the given user.
     * @param stdClass $user the user object.
     * @return string
     */
    private function get_user_display($user) {
        if ($this->_customdata['groupmode'] == NOGROUPS) {
            return fullname($user);
        }

        if (empty($user->groups)) {
            $groups = get_string('no_group', 'block_clampmail');
        } else {
            $groups = array();
            foreach ($user->groups as $group) {
                $groups[] = $this->_customdata['groups'][$group]->name;
            }
            $groups = implode(',', $groups);
        }
        return sprintf("%s (%s)", fullname($user), $groups);
    }

    /**
     * Return an HTML-formatted option tag for a selectable user.
     *
     * @param stdClass $user the user object.
     * @return string
     */
    private function display_option($user) {
        // Get the formatted display text.
        $userdisplay = $this->get_user_display($user);

        // Add select all functionality.
        if ($this->_customdata['groupmode'] != NOGROUPS) {
            $user->groups[] = 'all';
        }
        $user->roles[]  = 'none';

        $option = \html_writer::tag(
            'option',
            $userdisplay,
            array(
                'value' => $user->id,
                'data-groups' => '["' . implode('", "', $user->groups) . '"]',
                'data-roles' => '["' . implode('", "', $user->roles) . '"]'
            )
        );
        return $option;
    }

    private function display_options($users) {
        $options = '';
        foreach ($users as $userid => $user) {
            $options .= $this->display_option($user);
        }
        return $options;
    }

    public function definition() {
        global $USER, $COURSE, $OUTPUT;

        $mform =& $this->_form;

        $mform->addElement('hidden', 'mailto', '');
        $mform->setType('mailto', PARAM_RAW);
        $mform->addElement('hidden', 'userid', $USER->id);
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'type', '');
        $mform->setType('type', PARAM_ALPHA);
        $mform->addElement('hidden', 'typeid', 0);
        $mform->setType('typeid', PARAM_INT);

        $roleoptions = array('none' => get_string('no_filter', 'block_clampmail'));
        foreach ($this->_customdata['roles'] as $role) {
            $roleoptions[$role->shortname] = $role->name;
        }

        $groupoptions = empty($this->_customdata['groups']) ? array() : array(
            'all' => get_string('all_groups', 'block_clampmail')
        );
        foreach ($this->_customdata['groups'] as $group) {
            $groupoptions[$group->id] = $group->name;
        }
        $groupoptions[0] = get_string('no_group', 'block_clampmail');

        $links = array();
        $genurl = function($type) use ($COURSE) {
            $emailparam = array('courseid' => $COURSE->id, 'type' => $type);
            return new \moodle_url('emaillog.php', $emailparam);
        };

        $draftlink = \html_writer::link ($genurl('drafts'), get_string('drafts', 'block_clampmail'));
        $links[] =& $mform->createElement('static', 'draft_link', '', $draftlink);

        $context = \context_course::instance($COURSE->id);

        $config = config::load_configuration($COURSE);

        $cansend = (
            has_capability('block/clampmail:cansend', $context) or
            !empty($config['allowstudents'])
        );

        if ($cansend) {
            $historylink = \html_writer::link($genurl('log'), get_string('log', 'block_clampmail'));
            $links[] =& $mform->createElement('static', 'history_link', '', $historylink);
        }

        $mform->addGroup($links, 'links', '&nbsp;', array(' | '), false);

        $reqimg = $OUTPUT->pix_icon('req', get_string('requiredelement', 'form'), 'moodle', array('class' => 'req'));

        $table = new \html_table();
        $table->attributes['class'] = 'emailtable';

        $selectedrequiredlabel = new \html_table_cell();
        $selectedrequiredlabel->text = \html_writer::tag('strong',
            get_string('selected', 'block_clampmail') . $reqimg, array('class' => 'required'));

        $rolefilterlabel = new \html_table_cell();
        $rolefilterlabel->colspan = "2";
        $rolefilterlabel->text = \html_writer::tag('div',
            get_string('role_filter', 'block_clampmail'), array('class' => 'object_labels'));

        $selectfilter = new \html_table_cell();
        $selectfilter->text = \html_writer::tag('select',
            array_reduce($this->_customdata['selected'], array($this, 'reduce_users'), ''),
            array('id' => 'mail_users', 'multiple' => 'multiple', 'size' => 30));

        $embed = function ($text, $id) {
            return \html_writer::tag('p',
                \html_writer::empty_tag('input', array(
                    'value' => $text, 'type' => 'button', 'id' => $id
                ))
            );
        };

        $embedquick = function ($text) use ($embed) {
            return $embed(get_string($text, 'block_clampmail'), $text);
        };

        $centerbuttons = new \html_table_cell();
        $centerbuttons->text = (
            $embed($OUTPUT->larrow() . ' ' . get_string('add_button', 'block_clampmail'), 'add_button') .
            $embed(get_string('remove_button', 'block_clampmail') . ' ' . $OUTPUT->rarrow(), 'remove_button') .
            $embedquick('add_all') .
            $embedquick('remove_all')
        );

        $filters = new \html_table_cell();
        $filters->text = \html_writer::tag('div',
            \html_writer::select($roleoptions, '', 'none', null, array('id' => 'roles'))
        ) . \html_writer::tag('div',
            get_string('potential_groups', 'block_clampmail'),
            array('class' => 'object_labels')
        ) . \html_writer::tag('div',
            \html_writer::select($groupoptions, '', 'all', null,
            array('id' => 'groups', 'multiple' => 'multiple', 'size' => 5))
        ) . \html_writer::tag('div',
            get_string('potential_users', 'block_clampmail'),
            array('class' => 'object_labels')
        ) . \html_writer::tag('div',
            \html_writer::tag('select', $this->display_options($this->_customdata['users']),
            array('id' => 'from_users', 'multiple' => 'multiple', 'size' => 20))
        );

        $table->data[] = new \html_table_row(array($selectedrequiredlabel, $rolefilterlabel));
        $table->data[] = new \html_table_row(array($selectfilter, $centerbuttons, $filters));

        if (has_capability('block/clampmail:allowalternate', $context)) {
            $alternates = $this->_customdata['alternates'];
        } else {
            $alternates = array();
        }

        if (empty($alternates)) {
            $mform->addElement('static', 'from', get_string('from', 'block_clampmail'), $USER->email);
            $mform->setType('from', PARAM_EMAIL);
        } else {
            $options = array(0 => $USER->email) + $alternates;
            $mform->addElement('select', 'alternateid', get_string('from', 'block_clampmail'), $options);
            $mform->setType('alternateid', PARAM_INT);
        }

        $mform->addElement('static', 'selectors', '', \html_writer::table($table));
        $mform->setType('selectors', PARAM_RAW);

        $mform->addElement('filemanager', 'attachments', get_string('attachment', 'block_clampmail'));
        $mform->setType('attachments', PARAM_FILE);

        $mform->addElement('text', 'subject', get_string('subject', 'block_clampmail'));
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', null, 'required');
        $mform->addRule('subject', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('editor', 'message_editor', get_string('message', 'block_clampmail'),
            $this->_customdata['editor_options']);

        $options = $this->_customdata['sigs'] + array(-1 => 'No '. get_string('sig', 'block_clampmail'));
        $mform->addElement('select', 'sigid', get_string('signature', 'block_clampmail'), $options);

        $radio = array(
            $mform->createElement('radio', 'receipt', '', get_string('yes'), 1),
            $mform->createElement('radio', 'receipt', '', get_string('no'), 0)
        );

        $mform->addGroup($radio, 'receipt_action', get_string('receipt', 'block_clampmail'), array(' '), false);
        $mform->addHelpButton('receipt_action', 'receipt', 'block_clampmail');
        $mform->setDefault('receipt', !empty($config['receipt']));

        $buttons = array();
        $buttons[] =& $mform->createElement('submit', 'send', get_string('send_email', 'block_clampmail'));
        $buttons[] =& $mform->createElement('submit', 'draft', get_string('save_draft', 'block_clampmail'));
        $buttons[] =& $mform->createElement('cancel');

        $mform->addGroup($buttons, 'buttons', get_string('actions', 'block_clampmail'), array(' '), false);
    }
}
