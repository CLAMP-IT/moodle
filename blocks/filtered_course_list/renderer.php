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
 * This file defines the renderer for the Filtered course list block.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list\output;

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/locallib.php');

/**
 * Helper class for list items.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class list_item implements \renderable, \templatable {

    /** @var array of CSS classes for the list item */
    public $classes = array('block-fcl__list__item');
    /** @var string Display text for the list item link */
    public $displaytext;
    /** @var array of CSS classes for the list item link */
    public $linkclasses = array('block-fcl__list__link');
    /** @var string Text to display when the list item link is hovered */
    public $title;
    /** @var moodle_url object The destination for the list item link */
    public $url;
    /** @var mixed Empty string or link to course summary URL */
    public $summaryurl;
    /** @var icon object The icon to display */
    public $icon;

    /**
     * Class constructor
     *
     * @param mixed $itemobject A object from which to derive the class properties
     * @param object $config The plugin options object
     */
    public function __construct($itemobject, $config) {
        global $USER;

        $type = (get_class($itemobject) == 'core_course_category') ? 'category' : 'course';

        switch ($type){
            case 'course':
                $this->classes[] = 'block-fcl__list__item--course';
                $completionclass = $this->completion_class($itemobject, $USER->id);
                if (!empty($completionclass)) {
                    $this->classes[] = $completionclass;
                }
                $this->displaytext = \block_filtered_course_list_lib::coursedisplaytext($itemobject, $config->coursenametpl);
                if (!$itemobject->visible) {
                    $this->linkclasses[] = 'dimmed';
                }
                $this->title = format_string($itemobject->shortname);
                $this->url = new \moodle_url('/course/view.php?id=' . $itemobject->id);
                $this->summaryurl = new \moodle_url('/course/info.php?id=' . $itemobject->id);

                if (isloggedin($USER)) {
                    $usercontext = \context_user::instance($USER->id);
                    $userservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);
                    $isfave = $userservice->favourite_exists(
                        'core_course',
                        'courses',
                        $itemobject->id,
                        \context_course::instance($itemobject->id));
                    $fastring = ($isfave) ? 'fa-star' : 'fa-graduation-cap';
                    $srtitle = ($isfave) ? get_string('favourite', 'core_course') : get_string('course', 'core');
                    $this->icon = new icon($itemobject->id, $isfave, $fastring, $srtitle);
                } else {
                    $this->icon = new icon($itemobject->id, false, 'fa-graduation-cap', get_string('course', 'core'));
                }
                break;
            case 'category':
                $this->classes[] = 'block-fcl__list__item--category';
                $this->displaytext = format_string($itemobject->name);
                if (!$itemobject->visible) {
                    $this->linkclasses[] = 'dimmed';
                }
                $this->title = '';
                $this->url = new \moodle_url('/course/index.php?categoryid=' . $itemobject->id);
                $this->summaryurl = '';
                $this->icon = new icon($itemobject->id, false, 'fa-folder', get_string('category', 'core'));
                break;
        }
    }

    /**
     * Export the object data for use by a template
     *
     * @param renderer_base $output A renderer_base object
     * @return array $data Template-ready data
     */
    public function export_for_template(\renderer_base $output) {
        $data = array(
            'classes'     => implode(' ', $this->classes),
            'displaytext' => $this->displaytext,
            'linkclasses' => implode(' ', $this->linkclasses),
            'title'       => $this->title,
            'url'         => $this->url,
            'summaryurl'  => $this->summaryurl,
            'icon'        => $this->icon,
        );
        return $data;
    }

    /**
     * Return a completion class for a user in a courses
     *
     * @param stdClass $course object
     * @param string $userid
     * @return string $completionclass
     */
    private function completion_class($course, $userid) {
        if (\completion_info::is_enabled_for_site()) {
            $completioninfo = new \completion_info($course);
            if ($completioninfo->is_enabled()) {
                if ($completioninfo->is_course_complete($userid)) {
                    return 'block-fcl__list__item--complete';
                }
                return '.block-fcl__list__item--incomplete';
            }
            return;
        }
        return;
    }
}

/**
 * Helper class for rendering icons
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class icon implements \renderable, \templatable {

    /** @var int course id */
    public $id;
    /** @var bool is favourite */
    public $isfavourite;
    /** @var str Font awesome class to use for item icon */
    public $icon;
    /** @var str Title for screen reader */
    public $title;

    /**
     * Constructor
     *
     * @param int $id course id
     * @param bool $isfavourite is favourite
     * @param str $icon Font-awesome class to use for icon
     * @param str $title The title attribute for the icon link
     */
    public function __construct($id, $isfavourite, $icon, $title) {
        $this->id = $id;
        $this->isfavourite = $isfavourite;
        $this->icon = $icon;
        $this->title = $title;
    }

    /**
     * Export the object data for use by a template
     *
     * @param renderer_base $output A renderer_base object
     * @return array $data Template-ready data
     */
    public function export_for_template(\renderer_base $output) {
        $data = array(
            'id' => $this->id,
            'isfavourite' => $this->isfavourite,
            'icon' => $this->icon,
            'title' => $this->title,
        );
        return $data;
    }
}

/**
 * Helper class for rendering rubrics.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderable_rubric implements \renderable, \templatable {

    /** @var object Rubric */
    public $rubric;
    /** @var string Block instance id */
    public $instid;
    /** @var string Rubric key */
    public $key;

    /**
     * Constructor
     *
     * @param array $rubric An original rubric object
     * @param string $instid The instance id of the calling block
     * @param string $key The array index of the rubric
     */
    public function __construct($rubric, $instid, $key) {
        $this->rubric = $rubric;
        $this->instid = $instid;
        $this->key = $key;
    }

    /**
     * Export the object data for use by a template
     *
     * @param renderer_base $output A renderer_base object
     * @return array $data Template-ready data
     */
    public function export_for_template(\renderer_base $output) {
        $itemdata = array_map(function($item) use ($output) {
            $renderable = new list_item($item, $this->rubric->config);
            $export = $renderable->export_for_template($output);
            return $export;
        }, $this->rubric->courses);
        $key = $this->key + 1;
        $hash = 'block-fcl_' . md5("{$this->instid}{$key}{$this->rubric->title}");
        if (array_key_exists($hash, $_COOKIE)
                && property_exists($this->rubric->config, 'persistentexpansion')
                && $this->rubric->config->persistentexpansion) {
            $this->rubric->expanded = ($_COOKIE[$hash] == 'expanded') ? 'expanded' : 'collapsed';
        }
        $exp = ($this->rubric->expanded == 'expanded') ? 'true' : 'false';
        $hidden = ($this->rubric->expanded != 'expanded') ? 'true' : 'false';
        $data = array(
            'state'  => $this->rubric->expanded,
            'exp'    => $exp,
            'label'  => $this->rubric->title,
            'hidden' => $hidden,
            'instid' => $this->instid,
            'key'    => $key,
            'items'  => array_values($itemdata),
            'hash'   => $hash,
        );
        return $data;
    }
}

/**
 * Helper class for the main block content
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content implements \renderable, \templatable {

    /** @var array Rubrics to display */
    public $rubrics = array();
    /** @var string Block instance id */
    public $instid;

    /**
     * Constructor
     *
     * @param string $instid The instance id of the calling block
     * @param array $rubrics The list of rubrics to display
     */
    public function __construct($instid, $rubrics = array()) {
        $this->rubrics = $rubrics;
        $this->instid = $instid;
    }

    /**
     * Export the object data for use by a template
     *
     * @param renderer_base $output A renderer_base object
     * @return array $data Template-ready data
     */
    public function export_for_template(\renderer_base $output) {
        $rubricdata = array_map(function($rubric, $key) use ($output) {
            $rubrichelper = new renderable_rubric($rubric, $this->instid, $key);
            $export = $rubrichelper->export_for_template($output);
            return $export;
        }, $this->rubrics, array_keys($this->rubrics));
        $data = array(
            'instid'  => $this->instid,
            'rubrics' => $rubricdata,
            'persist' => $this->rubrics[0]->config->persistentexpansion,
        );
        return $data;
    }
}

/**
 * Helper class for the "All courses" link.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class footer implements \renderable, \templatable {

    /** @var string Text for footer link */
    public $linktext;
    /** @var string Link to the Course index page */
    public $url;
    /** @var boolean Whether or not to display the link */
    public $visible = false;

    /**
     * Constructor
     *
     * @param array $params A list of settings used to determine the link display
     */
    public function __construct($params = array()) {

        if ($params['usertype'] == 'manager' || $params['hideallcourseslink'] == BLOCK_FILTERED_COURSE_LIST_FALSE) {
            $this->visible = true;
        }
        if ($params['liststyle'] == 'empty_block') {
            $this->visible = false;
        }

        $this->url = new \moodle_url('/course/index.php');

        $this->linktext = get_string('fulllistofcourses');
        if ($params['liststyle'] == 'generic_list') {
            $this->linktext = get_string('searchcourses');
        }
    }

    /**
     * Export the object data for use by a template
     *
     * @param renderer_base $output A renderer_base object
     * @return array $data Template-ready data
     */
    public function export_for_template(\renderer_base $output) {
        $data = array(
            'linktext' => $this->linktext,
            'url'      => $this->url,
            'visible'  => $this->visible,
        );
        return $data;
    }
}

/**
 * Renderer for the Filtered course list block.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {

    /**
     * Render HTML for the "All courses" link
     *
     * @param footer $footer A footer object
     * @return string The rendered html
     */
    protected function render_footer (footer $footer) {
        $data = $footer->export_for_template($this);
        return $this->render_from_template('block_filtered_course_list/footer', $data);
    }

    /**
     * Render HTML for list item
     *
     * @param list_item $listitem
     * @return string The rendered html
     */
    protected function render_list_item (list_item $listitem) {
        $data = $listitem->export_for_template($this);
        return $this->render_from_template('block_filtered_course_list/list_item', $data);
    }

    /**
     * Render HTML for icon
     *
     * @param icon $icon
     * @return string The rendered html
     */
    protected function render_icon (icon $icon) {
        $data = $icon->export_for_template($this);
        return $this->render_from_template('block_filtered_course_list/icon', $data);
    }

    /**
     * Render HTML for rubric
     *
     * @param rubric $rubric
     * @return string The rendered html
     */
    protected function render_rubric (rubric $rubric) {
        $data = $rubric->export_for_template($this);
        return $this->render_from_template('block_filtered_course_list/rubric', $data);
    }

    /**
     * Render HTML for content
     *
     * @param content $content
     * @return string The rendered html
     */
    protected function render_content (content $content) {
        $data = $content->export_for_template($this);
        return $this->render_from_template('block_filtered_course_list/content', $data);
    }
}
