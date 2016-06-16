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
 * A block which displays turningtech links
 *
 * @package    block_turningtech
 * @copyright  Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
/**
 * Class for turningtech block
 * @copyright  Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class block_turningtech extends block_base {
    // Maintain reference to integration service.
    /**
     * @var unknown_type
     */
    private $service;
    /**
     * set values for the block
     * 
     * @return unknown_type
     */
    public function init() {
        $this->title = get_string('blocktitle', 'block_turningtech');
        $this->service = new TurningTechIntegrationServiceProvider();
        if (!during_initial_install()) {
            TurningTechTurningHelper::updateuserdevicemappings();
        }
    }
    /**
     * (non-PHPdoc)
     * 
     * @see docroot/blocks/block_base#specialization()
     */
    public function specialization() {
    }
    /**
     * (non-PHPdoc)
     * 
     * @see docroot/blocks/block_base#get_content()
     */
    public function get_content() {
        global $CFG, $USER, $COURSE;
        // Set up content.
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        // Verify the user is a student in the current course.
        $tooltip = get_string('managemydevicestool', 'block_turningtech');
        if (TurningTechMoodleHelper::isuserstudentincourse($USER, $COURSE)) {
        error_log("Inside Block : user is a student");
            $devicemap = TurningTechTurningHelper::getdeviceidbycourseandstudent($COURSE, $USER);
            if ($devicemap) {
                $link = $devicemap->displayLink();
                error_log("Inside Block : user has a device assosciated");
                $this->content->text = get_string('usingdeviceid', 'block_turningtech', $link);
            } else {
                error_log("Inside Block : user does not have a device assosciated");
                $this->content->text = get_string('nodeviceforthiscourse', 'block_turningtech');
            }
            $this->content->footer .= "<div class='homelink'>
            <a href='{$CFG->wwwroot}/mod/turningtech/index.php?id={$COURSE->id}'
              title = '{$tooltip}'>" .
                                             get_string('managemydevices', 'block_turningtech') . "</a></div>\n";
        } else if (TurningTechMoodleHelper::isuserinstructorincourse($USER, $COURSE)) {
            if ($CFG->version >= '2013111800.00') {
                $context = context_course::instance($COURSE->id);
            } else {
                $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
            }
            error_log("Inside Block : user is instructor");
            if (!has_capability('moodle/site:config', $context)) {
                $this->content->text = "<a href='{$CFG->wwwroot}/mod/turningtech/device_lookup.php?id={$COURSE->id}'>" .
                 get_string('searchturningtechcourse', 'block_turningtech') .
                                                 "</a>\n";
            } else {
            error_log("Inside Block : user is admin");
                $this->content->text = "<a href='{$CFG->wwwroot}/mod/turningtech/search_device.php?id={$COURSE->id}'>" .
                 get_string('searchturningtechcourse', 'block_turningtech') .
                                                 "</a>\n";
            }
        }
        if (! empty($this->content->text)) {
            $this->content->text .= "<link rel='stylesheet' type='text/css' href=
            '{$CFG->wwwroot}/mod/turningtech/css/style.css'>";
        }
        return $this->content;
    }
}