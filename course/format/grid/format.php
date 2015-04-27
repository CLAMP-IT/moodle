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
 * Grid Format - A topics based format that uses a grid of user selectable images to popup a light box of the section.
 *
 * @package    course/format
 * @subpackage grid
 * @copyright  &copy; 2012 G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @author     Based on code originally written by Paul Krix and Julian Ridden.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/completionlib.php');

// Horrible backwards compatible parameter aliasing..
if ($topic = optional_param('topic', 0, PARAM_INT)) { // Topics and Grid old section parameter.
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic / grid param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
if ($ctopic = optional_param('ctopics', 0, PARAM_INT)) { // Collapsed Topics old section parameter.
    $url = $PAGE->url;
    $url->param('section', $ctopic);
    debugging('Outdated collapsed topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
if ($week = optional_param('week', 0, PARAM_INT)) { // Weeks old section parameter.
    $url = $PAGE->url;
    $url->param('section', $week);
    debugging('Outdated week param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing..

$context = context_course::instance($course->id);

if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// Make sure all sections are created.
$courseformat = course_get_format($course);
$course = $courseformat->get_course();
course_create_sections_if_missing($course, range(0, $course->numsections));

$renderer = $PAGE->get_renderer('format_grid');

$devicetype = core_useragent::get_device_type(); // In /lib/classes/useragent.php.
if ($devicetype == "mobile") {
    $portable = 1;
} else if ($devicetype == "tablet") {
    $portable = 2;
} else {
    $portable = 0;
}
$renderer->set_portable($portable);

$gfsettings = $courseformat->get_settings();
$imageproperties = $courseformat->calculate_image_container_properties(
$gfsettings['imagecontainerwidth'], $gfsettings['imagecontainerratio'], $gfsettings['borderwidth']);
?>
<style type="text/css" media="screen">
    /* <![CDATA[ */
    .course-content ul.gridicons li p.icon_content {
        width: <?php echo ($gfsettings['imagecontainerwidth'] + ($gfsettings['borderwidth'] * 2)); ?>px;
    }
    .course-content ul.gridicons li .image_holder {
        width: <?php echo $gfsettings['imagecontainerwidth']; ?>px;
        height: <?php echo $imageproperties['height']; ?>px;
        border-color: <?php
        if ($gfsettings['bordercolour'][0] != '#') {
            echo '#';
        }
        echo $gfsettings['bordercolour'];
        ?>;
        background-color: <?php
        if ($gfsettings['imagecontainerbackgroundcolour'][0] != '#') {
            echo '#';
        }
        echo $gfsettings['imagecontainerbackgroundcolour'];
        ?>;
        border-width: <?php echo $gfsettings['borderwidth']; ?>px;
        <?php
        if ($gfsettings['borderradius'] == 2) { // On.
            echo 'border-radius: ' . $gfsettings['borderwidth'] . 'px;';
        }
        ?>

    }

    <?php
    $startindex = 0;
    if ($gfsettings['bordercolour'][0] == '#') {
        $startindex++;
    }
    $red = hexdec(substr($gfsettings['bordercolour'], $startindex, 2));
    $green = hexdec(substr($gfsettings['bordercolour'], $startindex + 2, 2));
    $blue = hexdec(substr($gfsettings['bordercolour'], $startindex + 4, 2));
    ?>
    .course-content ul.gridicons li:hover .image_holder {
        box-shadow: 0px 0px 0px <?php echo $gfsettings['borderwidth']; ?>px rgba(<?php echo $red . ',' . $green . ',' . $blue ?>,0.3);
    }

    .course-content ul.gridicons li.currenticon .image_holder {
        box-shadow: 0px 0px 2px 4px <?php
        if ($gfsettings['currentselectedsectioncolour'][0] != '#') {
            echo '#';
        }
        echo $gfsettings['currentselectedsectioncolour'];
        ?>;
    }

    .course-content ul.gridicons li.currentselected {
        background-color: <?php
        if ($gfsettings['currentselectedimagecontainercolour'][0] != '#') {
            echo '#';
        }
        echo $gfsettings['currentselectedimagecontainercolour'];
        ?>;
    }

    .course-content ul.gridicons img.new_activity {
        margin-top: <?php echo $imageproperties['margin-top']; ?>px;
        margin-left: <?php echo $imageproperties['margin-left']; ?>px;
    }

    /* ]]> */
</style>
<?php
if (!empty($displaysection)) {
    $renderer->print_single_section_page($course, null, null, null, null, $displaysection);
} else {
    $renderer->print_multiple_section_page($course, null, null, null, null);
}

// Include course format js module.
$PAGE->requires->js('/course/format/grid/format.js');
