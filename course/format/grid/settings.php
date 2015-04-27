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
 * @copyright  &copy; 2013 G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com, about.me/gjbarnard and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/format/grid/lib.php'); // For format_grid static constants.

if ($ADMIN->fulltree) {
    /* Default course display.
     * Course display default, can be either one of:
     * COURSE_DISPLAY_SINGLEPAGE or - All sections on one page.
     * COURSE_DISPLAY_MULTIPAGE     - One section per page.
     * as defined in moodlelib.php.
     */
    $name = 'format_grid/defaultcoursedisplay';
    $title = get_string('defaultcoursedisplay', 'format_grid');
    $description = get_string('defaultcoursedisplay_desc', 'format_grid');
    $default = COURSE_DISPLAY_SINGLEPAGE;
    $choices = array(
        COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
        COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi')
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Icon width. */
    $name = 'format_grid/defaultimagecontainerwidth';
    $title = get_string('defaultimagecontainerwidth', 'format_grid');
    $description = get_string('defaultimagecontainerwidth_desc', 'format_grid');
    $default = format_grid::get_default_image_container_width();
    $choices = format_grid::get_image_container_widths();
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Icon ratio. */
    $name = 'format_grid/defaultimagecontainerratio';
    $title = get_string('defaultimagecontainerratio', 'format_grid');
    $description = get_string('defaultimagecontainerratio_desc', 'format_grid');
    $default = format_grid::get_default_image_container_ratio();
    $choices = format_grid::get_image_container_ratios();
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Resize method - 1 = scale, 2 = crop. */
    $name = 'format_grid/defaultimageresizemethod';
    $title = get_string('defaultimageresizemethod', 'format_grid');
    $description = get_string('defaultimageresizemethod_desc', 'format_grid');
    $default = format_grid::get_default_image_resize_method();;
    $choices = array(
        1 => new lang_string('scale', 'format_grid'),   // Scale.
        2 => new lang_string('crop', 'format_grid')   // Crop.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default border colour in hexadecimal RGB with preceding '#'.
    $name = 'format_grid/defaultbordercolour';
    $title = get_string('defaultbordercolour', 'format_grid');
    $description = get_string('defaultbordercolour_desc', 'format_grid');
    $default = format_grid::get_default_border_colour();
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    /* Border width. */
    $name = 'format_grid/defaultborderwidth';
    $title = get_string('defaultborderwidth', 'format_grid');
    $description = get_string('defaultborderwidth_desc', 'format_grid');
    $default = format_grid::get_default_border_width();
    $choices = format_grid::get_border_widths();
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Border radius on / off. */
    $name = 'format_grid/defaultborderradius';
    $title = get_string('defaultborderradius', 'format_grid');
    $description = get_string('defaultborderradius_desc', 'format_grid');
    $default = format_grid::get_default_border_radius();
    $choices = array(
        1 => new lang_string('off', 'format_grid'),   // Off.
        2 => new lang_string('on', 'format_grid')   // On.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default imagecontainer background colour in hexadecimal RGB with preceding '#'.
    $name = 'format_grid/defaultimagecontainerbackgroundcolour';
    $title = get_string('defaultimagecontainerbackgroundcolour', 'format_grid');
    $description = get_string('defaultimagecontainerbackgroundcolour', 'format_grid');
    $default = format_grid::get_default_image_container_background_colour();
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default current selected section colour in hexadecimal RGB with preceding '#'.
    $name = 'format_grid/defaultcurrentselectedsectioncolour';
    $title = get_string('defaultcurrentselectedsectioncolour', 'format_grid');
    $description = get_string('defaultcurrentselectedsectioncolour', 'format_grid');
    $default = format_grid::get_default_current_selected_section_colour();
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default current selected imagecontainer colour in hexadecimal RGB with preceding '#'.
    $name = 'format_grid/defaultcurrentselectedimagecontainercolour';
    $title = get_string('defaultcurrentselectedimagecontainercolour', 'format_grid');
    $description = get_string('defaultcurrentselectedimagecontainercolour', 'format_grid');
    $default = format_grid::get_default_current_selected_image_container_colour();
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    /* Show new activity notification image - 1 = no, 2 = yes. */
    $name = 'format_grid/defaultnewactivity';
    $title = get_string('defaultnewactivity', 'format_grid');
    $description = get_string('defaultnewactivity_desc', 'format_grid');
    $default = 2;
    $choices = array(
        1 => new lang_string('no'),   // No.
        2 => new lang_string('yes')   // Yes.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
}