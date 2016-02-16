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
 * @copyright  2015 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/externallib.php');
require_once($CFG->dirroot . '/lib/coursecatlib.php');
require_once(dirname(__FILE__) . '/locallib.php');

/**
 * The Filtered course list block class
 *
 * @package    block_filtered_course_list
 * @copyright  2015 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_filtered_course_list extends block_base {

    /** @var array Admin settings for the FCL block */
    private $fclconfig;
    /** @var array A variable number of custom labels to organize courses by */
    private $customlabels = array();
    /** @var array A variable number of custom shortnames to match against */
    private $customshortnames = array();
    /** @var array A list of labels to be expanded by default */
    private $labelexpanded = array();
    /** @var string The string to use when writing HTML for collapsible list sections */
    private $collapsibleclass = '';
    /** @var arrray A list of courses for the current user */
    private $mycourses = array();
    /** @var stdClass This block's context */
    public $context;
    /** @var string A type of user for purposes of list display, should be 'user', 'admin' or 'guest' */
    private $usertype;
    /** @var string The type of list to create, should be 'generic_list', 'filtered_list' or 'empty_block' */
    private $liststyle = 'generic_list';

    /**
     * Set the initial properties for the block
     */
    public function init() {
        $this->title   = get_string('blockname', 'block_filtered_course_list');
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

        $this->mycourses = enrol_get_my_courses(null, 'visible DESC, fullname ASC');

        // Obtain values from our config settings.

        $this->fclconfig = get_config('block_filtered_course_list');
        $this->_calculate_settings();

        /* Call accordion YUI module */
        if ($this->fclconfig->collapsible == BLOCK_FILTERED_COURSE_LIST_TRUE && $this->page) {
            $this->page->requires->yui_module('moodle-block_filtered_course_list-accordion',
                'M.block_filtered_course_list.accordion.init', array());
        }

        $this->_calculate_usertype();

        // The default liststyle is 'generic_list' but ...

        if ($this->usertype == 'user' && empty($CFG->disablemycourses)) {
            $this->liststyle = "filtered_list";
        }

        if ($this->usertype == 'admin' &&
            $this->fclconfig->adminview == BLOCK_FILTERED_COURSE_LIST_ADMIN_VIEW_OWN &&
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
     * Determine admin settings for the block
     */
    private function _calculate_settings() {

        $this->collapsibleclass = ($this->fclconfig->collapsible == BLOCK_FILTERED_COURSE_LIST_TRUE) ? 'collapsible ' : '';

        if ($this->fclconfig->collapsible) {
            $this->labelexpanded[get_string('currentcourses', 'block_filtered_course_list')]
                = $this->fclconfig->currentexpanded;
            $this->labelexpanded[get_string('futurecourses', 'block_filtered_course_list')] = $this->fclconfig->futureexpanded;
        }

        for ($i = 1; $i <= $this->fclconfig->labelscount; $i++) {
            $labelsetting = 'customlabel' . $i;
            if (array_key_exists($labelsetting, $this->fclconfig)) {
                $label = $this->fclconfig->$labelsetting;
                $this->customlabels[$i] = $label;
                $shortnamesetting = 'customshortname' . $i;
                $this->customshortnames[$i] = $this->fclconfig->$shortnamesetting;
                if ($this->fclconfig->collapsible) {
                    $expandedsetting = 'labelexpanded' . $i;
                    if (array_key_exists($expandedsetting, $this->fclconfig)) {
                        $this->labelexpanded[$label] = $this->fclconfig->$expandedsetting;
                    }
                }
            }
        }
    }

    /**
     * Set the usertype for purposes of the course list display
     */
    private function _calculate_usertype() {

        global $USER;

        if (empty($USER->id) || isguestuser()) {
            $this->usertype = 'guest';
        } else if (has_capability('moodle/course:view', $this->context)) {
            $this->usertype = 'admin';
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

        if ($this->mycourses) {
            switch ($this->fclconfig->filtertype) {
                case 'shortname':
                    $filteredcourses = $this->_filter_by_shortname();
                    break;

                case 'categories':
                    $filteredcourses = $this->_filter_by_category();
                    break;

                case 'custom':
                    // We do not yet have a handler for custom filter types.

                    break;

                default:
                    // This is unexpected.
                    break;
            }

            $sectioncount = 1;
            $id = $this->instance->id;

            foreach ($filteredcourses as $section => $courslist) {
                if (count($courslist) == 0) {
                    continue;
                }
                $initialstate = 'collapsed';
                $ariaexpanded = 'false';
                $ariahidden = 'true';
                if ($this->fclconfig->collapsible && array_key_exists($section, $this->labelexpanded)) {
                    if ($this->labelexpanded[$section] == 1) {
                        $initialstate = 'expanded';
                        $ariaexpanded = 'true';
                        $ariahidden = 'false';
                    }
                }
                $sectionatts = array(
                    'id'            => "fcl_{$id}_tab{$sectioncount}",
                    'class'         => "course-section tab{$sectioncount} $initialstate",
                    'role'          => 'tab',
                    'aria-controls' => "fcl_{$id}_tabpanel{$sectioncount}",
                    'aria-expanded' => "$ariaexpanded",
                    'aria-selected' => 'false',
                );
                $this->content->text .= html_writer::tag('div', $section, $sectionatts);

                $ulatts = array(
                    'id'              => "fcl_{$id}_tabpanel{$sectioncount}",
                    'class'           => "$this->collapsibleclass list tabpanel{$sectioncount}",
                    'role'            => "tabpanel",
                    'aria-labelledby' => "fcl_{$id}_tab{$sectioncount}",
                    'aria-hidden'     => "$ariahidden",
                );
                $listitems = '';
                foreach ($courslist as $course) {
                    $listitems .= $this->_print_single_course($course);
                }
                $this->content->text .= html_writer::tag('ul', $listitems, $ulatts);

                ++$sectioncount;
            }

            $this->_print_allcourseslink();
        }
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
                $this->content->text .= '<ul class="' . $this->collapsibleclass . 'list">';
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
                    $this->content->text .= '<ul class="' . $this->collapsibleclass . 'list">';
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
     * Apply filtering based on a shortname match
     *
     * @return array The structured list of courses as organized by the filter
     */
    private function _filter_by_shortname() {

        $results = array(get_string('currentcourses', 'block_filtered_course_list') => array(),
                         get_string('futurecourses', 'block_filtered_course_list')  => array());

        foreach ($this->customlabels as $label) {
            $results[$label] = array();
        }

        $other = $this->mycourses;

        foreach ($this->mycourses as $key => $course) {
            if ($course->id == SITEID) {
                unset($this->mycourses[$key]);
                unset($other[$key]);
                continue;
            }

            $currentshortname = $this->fclconfig->currentshortname;
            if (!empty($currentshortname) && $this->_satisfies_match($course->shortname, $currentshortname)) {
                $results[get_string('currentcourses', 'block_filtered_course_list')][] = $course;
                unset($other[$key]);
            }
            $futureshortname = $this->fclconfig->futureshortname;
            if (!empty($futureshortname) && $this->_satisfies_match($course->shortname, $futureshortname)) {
                $results[get_string('futurecourses', 'block_filtered_course_list')][] = $course;
                unset($other[$key]);
            }
            for ($i = 1; $i <= $this->fclconfig->labelscount; $i++) {
                if (isset($this->customlabels[$i])) {
                    if ($this->customshortnames[$i] && $this->_satisfies_match($course->shortname, $this->customshortnames[$i])) {
                        $label = $this->customlabels[$i];
                        $results[$label][] = $course;
                        unset($other[$key]);
                    }
                }
            }
        }

        if ($this->fclconfig->hideothercourses == BLOCK_FILTERED_COURSE_LIST_FALSE) {
            $results[get_string('othercourses', 'block_filtered_course_list')] = $other;
        }

        return $results;
    }

    /**
     * Test whether a course shortname matches a string. Use regex if indicated by the admin settings.
     *
     * @param string $coursename The shortname of a course
     * @param string $teststring The string to match against
     * @return bool
     */
    private function _satisfies_match($coursename, $teststring) {
        if ($this->fclconfig->useregex == BLOCK_FILTERED_COURSE_LIST_FALSE) {
            $satisfies = (core_text::strpos($coursename, $teststring) !== false) ?: false;
        } else {
            $teststring = str_replace('`', '', $teststring);
            $satisfies = (preg_match("`$teststring`", $coursename) == 1) ?: false;
        }
        return $satisfies;
    }

    /**
     * Apply filtering based on a shortname match
     *
     * @return array The structured list of courses as organized by the filter
     */
    private function _filter_by_category() {

        if ( $this->fclconfig->categories == BLOCK_FILTERED_COURSE_LIST_DEFAULT_CATEGORY ) {
            $mycats = core_course_external::get_categories();
        } else {
            $criteria = array(array('key' => 'id', 'value' => $this->fclconfig->categories));
            $mycats = core_course_external::get_categories($criteria);
        }

        $results = array();
        $other = array();

        foreach ($mycats as $cat) {
            foreach ($this->mycourses as $key => $course) {
                if ($course->id == SITEID) {
                    continue;
                }
                if ($course->category == $cat['id']) {
                    $results[$cat['name']][] = $course;
                    unset($this->mycourses[$key]);
                }
            }
        }

        if ($this->fclconfig->hideothercourses == BLOCK_FILTERED_COURSE_LIST_FALSE) {
            foreach ($this->mycourses as $course) {
                $other[] = $course;
            }
            $results[get_string('othercourses', 'block_filtered_course_list')] = $other;
        }

        return $results;
    }

    /**
     * Print or do not print a link to all courses, depending on admin settings
     */
    private function _print_allcourseslink() {
        global $CFG;
        // If we can update any course of the view all isn't hidden.
        // Show the view all courses link.
        if ($this->usertype == 'admin' || $this->fclconfig->hideallcourseslink == BLOCK_FILTERED_COURSE_LIST_FALSE) {
            $this->content->footer .= "<a href=\"$CFG->wwwroot/course/index.php\">" .
                                      get_string('fulllistofcourses') .
                                      '</a> ...<br>';
        }
    }
}
