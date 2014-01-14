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

require_once($CFG->dirroot . '/course/lib.php');

class block_filtered_course_list extends block_base {
    public function init() {
        $this->title   = get_string('blockname', 'block_filtered_course_list');
    }

    public function has_config() {
        return true;
    }

    public function get_content() {
        global $CFG, $USER, $DB, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';

        // Obtain values from our config settings.
        $filter_type = 'term';
        if (isset($CFG->block_filtered_course_list_filtertype)) {
            $filter_type = $CFG->block_filtered_course_list_filtertype;
        }

        $term_current = ' ';
        if (isset($CFG->block_filtered_course_list_termcurrent)) {
            $term_current = $CFG->block_filtered_course_list_termcurrent;
        }

        $term_future = ' ';
        if (isset($CFG->block_filtered_course_list_termfuture)) {
            $term_future = $CFG->block_filtered_course_list_termfuture;
        }

        $category_ids = ' ';
        if (isset($CFG->block_filtered_course_list_categories)) {
            $category_ids = $CFG->block_filtered_course_list_categories;
        }

        $adminseesall = true;
        if (isset($CFG->block_filtered_course_list_adminview)) {
            if ($CFG->block_filtered_course_list_adminview == 'own') {
                $adminseesall = false;
            }
        }

        if (empty($CFG->disablemycourses) and
            !empty($USER->id) and
            !(has_capability('moodle/course:view', get_context_instance(CONTEXT_SYSTEM))) and
            !isguestuser()) {
            // If user can't view all courses, just print My Courses.
            $all_courses = enrol_get_my_courses(null, 'visible DESC, fullname ASC');

            if ($all_courses) {
                switch ($filter_type) {
                    case 'term':
                        $filtered_courses = $this->_filter_by_term($all_courses,
                                                                   $term_current,
                                                                   $term_future);
                        break;

                    case 'categories':
                        $filtered_courses = $this->_filter_by_category($all_courses,
                                                                       $category_ids);
                        break;

                    case 'custom':
                        //$filtered_courses = $this->_filter_by_custom($all_courses,
                        //                                             $category_ids);
                        break;

                    default:
                        // This is unexpected.
                        break;
                }

                foreach ($filtered_courses as $section => $course_list) {
                    if (count($course_list) == 0) {
                        continue;
                    }
                    $this->content->text .= html_writer::tag('div', $section, array('class' => 'course-section'));
                    $this->content->text .= '<ul class="list">';

                    foreach ($course_list as $course) {
                        $this->content->text .= $this->_print_single_course($course);
                    }
                    $this->content->text .= '</ul>';
                    // If we can update any course of the view all isn't hidden.
                    // Show the view all courses link.
                    if (has_capability('moodle/course:update', get_context_instance(CONTEXT_SYSTEM)) ||
                        empty($CFG->block_filtered_course_list_hideallcourseslink)) {
                        $this->content->footer = "<a href=\"$CFG->wwwroot/course/index.php\">" .
                                                 get_string("fulllistofcourses") .
                                                 "</a> ...";
                    }
                }
            }
        } else {
            // Parent = 0   ie top-level categories only.
            $categories = get_categories("0");

            // Check we have categories.
            if ($categories) {
                // Just print top level category links.
                if (count($categories) > 1 ||
                   (count($categories) == 1 &&
                    count($course_list) > 100)) {
                    $this->content->text .= '<ul class="list">';
                    foreach ($categories as $category) {
                        $linkcss = $category->visible ? "" : "dimmed";
                        $this->content->text .= html_writer::tag('li',
                            html_writer::tag('a', format_string($category->name),
                            array('href' => $CFG->wwwroot . '/course/category.php?id=' . $category->id)),
                            array('class' => $linkcss));
                    }
                    $this->content->text .= '</ul>';
                    $this->content->footer .= "<br><a href=\"$CFG->wwwroot/course/index.php\">" .
                                              get_string('searchcourses') .
                                              '</a> ...<br />';

                    // If we can update any course of the view all isn't hidden.
                    // Show the view all courses link.
                    if (has_capability('moodle/course:update', get_context_instance(CONTEXT_SYSTEM)) ||
                        empty($CFG->block_filtered_course_list_hideallcourseslink)) {
                        $this->content->footer .= "<a href=\"$CFG->wwwroot/course/index.php\">" .
                                                  get_string('fulllistofcourses') .
                                                  '</a> ...<br>';
                    }

                    $this->title = get_string('blockname', 'block_filtered_course_list');
                } else {
                    // Just print course names of single category.
                    $category = array_shift($categories);
                    $courses = get_courses($category->id);

                    if ($courses) {
                        $this->content->text .= '<ul class="list">';
                        foreach ($courses as $course) {
                            $this->content->text .= $this->_print_single_course($course);
                        }
                        $this->content->text .= '</ul>';

                        // If we can update any course of the view all isn't hidden.
                        // Show the view all courses link.
                        if (has_capability('moodle/course:update', get_context_instance(CONTEXT_SYSTEM)) ||
                            empty($CFG->block_filtered_course_list_hideallcourseslink)) {
                            $this->content->footer .= "<a href=\"$CFG->wwwroot/course/index.php\">" .
                                                      get_string('fulllistofcourses') .
                                                      '</a> ...';
                        }
                    }
                }
            }
        }
        return $this->content;
    }

    private function _print_single_course($course) {
        global $CFG;
        $linkcss = $course->visible ? "" : "dimmed";
        $html = html_writer::tag('li',
            html_writer::tag('a', format_string($course->fullname),
                array('href' => $CFG->wwwroot . '/course/view.php?id=' . $course->id, 'title' => format_string($course->shortname))),
                array('class' => $linkcss));
        return $html;
    }

    private function _filter_by_term($courses, $term_current, $term_future) {
        global $CFG;
        $results = array('Current Courses' => array(),
                         'Future Courses'  => array(),
                         'Other Courses'     => array());

        foreach ($courses as $course) {
            if ($course->id == SITEID) {
                continue;
            }

            if (stristr($course->shortname, $term_current)) {
                $results['Current Courses'] []= $course;
            } else if (stristr($course->shortname, $term_future)) {
                $results['Future Courses']  []= $course;
            } else if (empty($CFG->block_filtered_course_list_hideothercourses)
                || (!$CFG->block_filtered_course_list_hideothercourses)){
                $results['Other Courses']     []= $course;
            }
        }

        return $results;
    }

    private function _filter_by_category($courses, $cat_ids) {
        $filter_categories = explode(',', $cat_ids);
        $results           = array('Course List' => array());

        foreach ($courses as $course) {
            if ($course->id == SITEID) {
                continue;
            }

            foreach ($filter_categories as $filter_category) {
                if ($course->category == $filter_category) {
                    $results['Course List'] []= $course;
                    break;
                }
            }
        }

        return $results;
    }
}
