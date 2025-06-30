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
 * This file contains the class used to display a Filtered course list block.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

/**
 * The Filtered course list block class
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_filtered_course_list extends block_base {

    /** @var array Admin settings for the FCL block */
    private $fclconfig;
    /** @var array A list of rubric objects for display */
    private $rubrics = array();
    /** @var arrray A list of courses for the current user */
    private $mycourses = array();
    /** @var string A type of user for purposes of list display, should be 'user', 'manager' or 'guest' */
    private $usertype;
    /** @var string Type of list: 'generic_list', 'filtered_list' or 'empty_block' */
    private $liststyle;

    /**
     * Set the initial properties for the block
     */
    public function init() {
        $this->title   = get_string('blockname', 'block_filtered_course_list');
        $this->fclconfig = get_config('block_filtered_course_list');
    }

    /**
     * The FCL block uses a settings.php file
     *
     * @return bool Returns true
     */
    public function has_config() {
        return true;
    }

    /**
     * Set the instance title and merge instance config as soon as nstance data is loaded
     */
    public function specialization() {

        if (isset($this->config->title) && $this->config->title != '') {
            $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        }

        if (isset($this->config->filters) && $this->config->filters != '') {
            $this->fclconfig->filters = $this->config->filters;
        }
    }

    /**
     * Allow multiple instances
     *
     * @return bool Returns true
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Returns the role that best describes the block... 'region'
     *
     * @return string 'region'
     */
    public function get_aria_role() {
        return 'region';
    }

    /**
     * Returns the rubrics data
     *
     * We provide this function to facilitate forks that render the block for the mobile app.
     *
     * @return array
     */
    public function get_rubrics() {
        return $this->rubrics;
    }

    /**
     * Return the block contents
     *
     * @return stdClass The block contents
     */
    public function get_content($page=null) {

        // We allow unit tests to pass in the global $PAGE object.
        if ($page !== null) {
            $this->page = $page;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';

        $sortsettings = array(
            array(
                $this->fclconfig->primarysort,
                $this->fclconfig->primaryvector,
            ),
            array(
                $this->fclconfig->secondarysort,
                $this->fclconfig->secondaryvector,
            ),
        );

        $sortstring = "visible DESC";

        foreach ($sortsettings as $sortsetting) {
            if ($sortsetting[0] == 'none') {
                continue;
            } else {
                $sortstring .= ", " . $sortsetting[0] . " " . $sortsetting[1];
            }
        }

        $this->mycourses = enrol_get_my_courses(null, "$sortstring");

        $this->_calculate_usertype();
        $this->liststyle = $this->_set_liststyle();

        if ($this->liststyle != 'empty_block') {
            if ($this->liststyle == 'generic_list') {
                $this->fclconfig->filters = BLOCK_FILTERED_COURSE_LIST_GENERIC_CONFIG;
            }
            $this->_process_filtered_list();
        }

        $output = $this->page->get_renderer('block_filtered_course_list');
        $params = array(
            'usertype'           => $this->usertype,
            'liststyle'          => $this->liststyle,
            'hideallcourseslink' => $this->fclconfig->hideallcourseslink,
        );
        $footer = new \block_filtered_course_list\output\footer($params);
        $this->content->footer = $output->render($footer);

        return $this->content;
    }

    /**
     * Set the usertype for purposes of the course list display
     */
    private function _calculate_usertype() {

        global $USER;

        if (empty($USER->id) || isguestuser()) {
            $this->usertype = 'guest';
        } else if (has_capability('moodle/course:view', context_system::instance())) {
            $this->usertype = 'manager';
        } else {
            $this->usertype = 'user';
        }
    }

    /**
     * Set the list style
     */
    private function _set_liststyle() {
        global $CFG;

        // The default liststyle is 'filtered_list' but ...
        $liststyle = 'filtered_list';

        if (!empty($CFG->disablemycourses) && $this->usertype != 'manager') {
            $liststyle = 'generic_list';
        }

        if ($this->usertype == 'manager' &&
            $this->fclconfig->managerview != BLOCK_FILTERED_COURSE_LIST_ADMIN_VIEW_OWN) {
            $liststyle = "generic_list";
        }

        if ($this->fclconfig->hidefromguests == BLOCK_FILTERED_COURSE_LIST_TRUE && $this->usertype == 'guest') {
            $liststyle = "empty_block";
        }

        return $liststyle;
    }

    /**
     * Build a user-specific Filtered course list block
     */
    private function _process_filtered_list() {

        $output = $this->page->get_renderer('block_filtered_course_list');

        // Parse the textarea settings into an array of arrays.
        $filterconfigs = array_map(function($line) {
            return array_map(function($item) {
                return trim($item);
            }, explode('|', $line, 2));
        }, explode("\n", $this->fclconfig->filters));

        // Get the arrays of rubrics based on the config lines, filter out failures, and merge them into one array.
        $this->rubrics = array_reduce(
            array_filter(
                array_map(function($config) {
                    $classname = get_filter($config[0], $this->fclconfig->externalfilters);
                    if (!empty($classname) && class_exists($classname)) {
                        $item = new $classname($config, $this->mycourses, $this->fclconfig);
                        return $item->get_rubrics();
                    }
                    return null;
                }, $filterconfigs), function($item) {
                    return is_array($item);
                }
            ),
        'array_merge', array());

        if ($this->fclconfig->hideothercourses == BLOCK_FILTERED_COURSE_LIST_FALSE &&
                $this->liststyle != 'generic_list') {

            $mentionedcourses = array_unique(array_reduce(array_map(function($item) {
                return $item->courses;
            }, $this->rubrics), 'array_merge', array()), SORT_REGULAR);

            $othercourses = array_udiff($this->mycourses, $mentionedcourses, function($a, $b) {
                return $a->id - $b->id;
            });

            if (!empty($othercourses)) {
                $otherrubric = new block_filtered_course_list_rubric(get_string('othercourses',
                    'block_filtered_course_list'), $othercourses, $this->fclconfig);
                $this->rubrics[] = $otherrubric;
            }
        }

        if (count($this->rubrics) > 0) {
            $content = new \block_filtered_course_list\output\content($this->instance->id, $this->rubrics);
            $this->content->text = $output->render($content);
        } else if ($this->fclconfig->filters != BLOCK_FILTERED_COURSE_LIST_GENERIC_CONFIG) {
            $this->liststyle = 'generic_list';
            $this->fclconfig->filters = 'generic|e';
            $this->_process_filtered_list();
        }
    }
}
