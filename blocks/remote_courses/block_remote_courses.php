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
 * Prints a list of courses from another Moodle instance.
 *
 * @package   block_remote_courses
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_remote_courses extends block_base {
    public function init() {
        $this->title = get_string('remote_courses', 'block_remote_courses');
    }

    public function applicable_formats() {
        return array(
            'site-index' => true
        );
    }

    public function get_content() {
        require_login();

        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';

        // Default content.
        if (!empty($this->config->introtext)) {
            $this->content->text .= $this->config->introtext['text'];
        }

        // Quit if remote URL and token aren't set.
        if (empty($this->config->wstoken) || empty($this->config->remotesite)) {
            $this->content->text = 'Webservice not configured';
            return $this->content;
        }

        // Function call is hard-coded.
        $url = $this->config->remotesite
            . '/webservice/rest/server.php?wstoken='
            . $this->config->wstoken . '&wsfunction=local_remote_courses_get_courses_by_username';
        $format = 'json';

        // Params: we use the username for consistency.
        $params = array('username' => $USER->username);

        // Retrieve data.
        $curl = new curl;
        $resp = json_decode($curl->post($url. '&moodlewsrestformat='.$format.'&'.http_build_query($params)));
        if (!is_null($resp) && is_array($resp) && count($resp) > 0) {
            $this->content->text .= '<ul class="list">';
            $coursesprinted = 0;
            foreach ($resp as $course) {
                $this->content->text .= html_writer::tag('li',
                    html_writer::tag('a', $course->fullname,
                        array('href' => $this->config->remotesite . '/course/view.php?id='. $course->id)),
                        array('class' => 'remote_courses'));
                $coursesprinted++;
                if ($coursesprinted == $this->config->numcourses) {
                    break;
                }
            }
            $this->content->text .= '</ul>';
        }

        return $this->content;
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        } else {
            $this->title = 'Remote Courses';
        }
    }
}
