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
require_once($CFG->dirroot . '/lib/coursecatlib.php');
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
    /** @var stdClass This block's context */
    public $context;
    /** @var string A type of user for purposes of list display, should be 'user', 'manager' or 'guest' */
    private $usertype;
    /** @var string The type of list to create, should be 'generic_list', 'filtered_list' or 'empty_block' */
    private $liststyle = 'generic_list';

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
     * Set the instance title just after the instance data is loaded
     */
    public function specialization() {
        $this->title = isset($this->config->title) ? $this->config->title : get_string('blockname', 'block_filtered_course_list');
    }

    /**
     * Returns the role that best describes the block... 'navigation'
     *
     * @return string 'navigation'
     */
    public function get_aria_role() {
        return 'navigation';
    }

    /**
     * Return the block contents
     *
     * @return stdClass The block contents
     */
    public function get_content() {
        global $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';
        $this->context = context_system::instance();

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

        /* Call accordion AMD module */
        if ($this->page) {
            $this->page->requires->js_call_amd('block_filtered_course_list/accordion', 'init', array());
        }

        $this->_calculate_usertype();

        // The default liststyle is 'generic_list' but ...

        if ($this->usertype == 'user' && empty($CFG->disablemycourses)) {
            $this->liststyle = "filtered_list";
        }

        if ($this->usertype == 'manager' &&
            $this->fclconfig->managerview == BLOCK_FILTERED_COURSE_LIST_ADMIN_VIEW_OWN &&
            $this->mycourses ) {
            $this->liststyle = "filtered_list";
        }

        if ($this->fclconfig->hidefromguests == BLOCK_FILTERED_COURSE_LIST_TRUE && $this->usertype == 'guest') {
            $this->liststyle = "empty_block";
        }

        $process = '_process_' . $this->liststyle;
        $this->$process();

        if (is_object($this->content) && $this->content->text != '') {
            $atts = array('role' => 'tablist', 'aria-multiselectable' => 'true');
            $this->content->text = html_writer::div($this->content->text, 'tablist', $atts);
        }

        return $this->content;
    }

    /**
     * Set the usertype for purposes of the course list display
     */
    private function _calculate_usertype() {

        global $USER;

        if (empty($USER->id) || isguestuser()) {
            $this->usertype = 'guest';
        } else if (has_capability('moodle/course:view', $this->context)) {
            $this->usertype = 'manager';
        } else {
            $this->usertype = 'user';
        }
    }

    /**
     * Set block contents to null to display an empty block
     */
    private function _process_empty_block() {
        $this->content = null;
    }

    /**
     * Build a user-specific Filtered course list block
     */
    private function _process_filtered_list() {

        if (!$this->mycourses) {
            return;
        }

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
                    $classname = "block_filtered_course_list_{$config[0]}_configline";
                    if (class_exists($classname)) {
                        $item = new $classname($config, $this->mycourses, $this->fclconfig);
                        return $item->get_rubrics();
                    }
                    return null;
                }, $filterconfigs), function($item) {
                    return is_array($item);
                }
            ),
        'array_merge', array());

        if ($this->fclconfig->hideothercourses == BLOCK_FILTERED_COURSE_LIST_FALSE) {

            $mentionedcourses = array_unique(array_reduce(array_map(function($item) {
                return $item->courses;
            }, $this->rubrics), 'array_merge', array()), SORT_REGULAR);

            $othercourses = array_udiff($this->mycourses, $mentionedcourses, function($a, $b) {
                return $a->id - $b->id;
            });

            if (!empty($othercourses)) {
                $otherrubric = new block_filtered_course_list_rubric(get_string('othercourses',
                    'block_filtered_course_list'), $othercourses);
                $this->rubrics[] = $otherrubric;
            }
        }

        $htmls = array_map(function($key, $rubric) {
            return $this->_get_rubric_html($rubric, $key);
        }, array_keys($this->rubrics), $this->rubrics);

        $this->content->text = implode($htmls);

        $this->_print_allcourseslink();
    }

    /**
     * Build a generic Filtered course list block
     */
    private function _process_generic_list() {

        global $CFG;

        // Parent = 0   ie top-level categories only.
        $categories = coursecat::get(0)->get_children();

        // Check we have categories.
        if ($categories) {
            // Just print top level category links.
            if (count($categories) > 1 ||
               (count($categories) == 1 &&
                current($categories)->coursecount > $this->fclconfig->maxallcourse)) {
                $this->content->text .= '<ul class="collapsible list">';
                foreach ($categories as $category) {
                    $linkcss = $category->visible ? "" : "dimmed";
                    $this->content->text .= html_writer::tag('li',
                        html_writer::tag('a', format_string($category->name),
                        array(
                            'href' => $CFG->wwwroot . '/course/index.php?categoryid=' . $category->id,
                            'class' => $linkcss))
                        );
                }
                $this->content->text .= '</ul>';
                $this->content->footer .= "<br><a href=\"$CFG->wwwroot/course/index.php\">" .
                                          get_string('searchcourses') .
                                          '</a> ...<br />';

                $this->_print_allcourseslink();

            } else {
                // Just print course names of single category.
                $category = array_shift($categories);
                $courses = get_courses($category->id);

                if ($courses) {
                    $this->content->text .= '<ul class="collapsible list">';
                    foreach ($courses as $course) {
                        $this->content->text .= $this->_print_single_course($course);
                    }
                    $this->content->text .= '</ul>';

                    $this->_print_allcourseslink();
                }
            }
        }
    }

    /**
     * Build the HTML to print out a single rubric and its contents.
     *
     * @param object $rubric The rubric object to be rendered
     * @param int $arraykey The numeric key of the rubric object
     * @return string HTML to display a rubric
     */
    private function _get_rubric_html($rubric, $arraykey) {
        $key = $arraykey + 1;
        $initialstate = $rubric->expanded;
        $ariaexpanded = ($initialstate == 'expanded') ? 'true' : 'false';
        $ariahidden = ($initialstate == 'expanded') ? 'false' : 'true';
        $atts = array(
            'id'            => "fcl_{$this->instance->id}_tab{$key}",
            'class'         => "course-section tab{$key} $initialstate",
            'role'          => 'tab',
            'aria-controls' => "fcl_{$this->instance->id}_tabpanel{$key}",
            'aria-expanded' => "$ariaexpanded",
            'aria-selected' => 'false',
        );
        $title = html_writer::tag('div', htmlentities($rubric->title), $atts);
        $courselinks = array_map(function($course) {
            return $this->_print_single_course($course);
        }, $rubric->courses);
        $ulatts = array(
            'id'              => "fcl_{$this->instance->id}_tabpanel{$key}",
            'class'           => "collapsible list tabpanel{$key}",
            'role'            => "tabpanel",
            'aria-labelledby' => "fcl_{$this->instance->id}_tab{$key}",
            'aria-hidden'     => "$ariahidden",
        );
        $ul = html_writer::tag('ul', implode($courselinks), $ulatts);
        return $title . $ul;
    }

    /**
     * Build the HTML to display a single course in a filtered list
     *
     * @param object $course The course to display
     * @return string HTML to display a link to a course
     */
    private function _print_single_course($course) {
        global $CFG;
        $linkcss = $course->visible ? "fcl-course-link" : "fcl-course-link dimmed";
        $html = html_writer::tag('li',
            html_writer::tag('a', format_string($course->fullname),
            array('href' => $CFG->wwwroot . '/course/view.php?id=' . $course->id,
                'title' => format_string($course->shortname), 'class' => $linkcss))
        );
        return $html;
    }

    /**
     * Print or do not print a link to all courses, depending on manager settings
     */
    private function _print_allcourseslink() {
        global $CFG;
        // If we can update any course of the view all isn't hidden.
        // Show the view all courses link.
        if ($this->usertype == 'manager' || $this->fclconfig->hideallcourseslink == BLOCK_FILTERED_COURSE_LIST_FALSE) {
            $this->content->footer .= "<a href=\"$CFG->wwwroot/course/index.php\">" .
                                      get_string('fulllistofcourses') .
                                      '</a> ...<br>';
        }
    }
}
