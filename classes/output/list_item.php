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
 * Contains class block_filtered_course_list\output\list_item
 *
 * @package    block_filtered_course_list
 * @copyright  2025 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list\output;

use core_course_category;

/**
 * Helper class for list items.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class list_item implements \renderable, \templatable {
    /** @var array of CSS classes for the list item */
    public $classes = ['block-fcl__list__item'];
    /** @var string Display text for the list item link */
    public $displaytext;
    /** @var array of CSS classes for the list item link */
    public $linkclasses = ['block-fcl__list__link'];
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

        switch ($type) {
            case 'course':
                $this->classes[] = 'block-fcl__list__item--course';
                $completionclass = $this->completion_class($itemobject, $USER->id);
                if (!empty($completionclass)) {
                    $this->classes[] = $completionclass;
                }
                $this->displaytext = \block_filtered_course_list\lib::coursedisplaytext($itemobject, $config->coursenametpl);
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
                        \context_course::instance($itemobject->id)
                    );
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
        $data = [
            'classes'     => implode(' ', $this->classes),
            'displaytext' => $this->displaytext,
            'linkclasses' => implode(' ', $this->linkclasses),
            'title'       => $this->title,
            'url'         => $this->url,
            'summaryurl'  => $this->summaryurl,
            'icon'        => $this->icon,
        ];
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
