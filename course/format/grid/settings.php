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

    /* Container alignment. */
    $name = 'format_grid/defaultimagecontaineralignment';
    $title = get_string('defaultimagecontaineralignment', 'format_grid');
    $description = get_string('defaultimagecontaineralignment_desc', 'format_grid');
    $default = format_grid::get_default_image_container_alignment();
    $choices = format_grid::get_horizontal_alignments();
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
    $default = format_grid::get_default_image_resize_method();
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
    $description = get_string('defaultimagecontainerbackgroundcolour_desc', 'format_grid');
    $default = format_grid::get_default_image_container_background_colour();
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default current selected section colour in hexadecimal RGB with preceding '#'.
    $name = 'format_grid/defaultcurrentselectedsectioncolour';
    $title = get_string('defaultcurrentselectedsectioncolour', 'format_grid');
    $description = get_string('defaultcurrentselectedsectioncolour_desc', 'format_grid');
    $default = format_grid::get_default_current_selected_section_colour();
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default current selected image container text colour in hexadecimal RGB with preceding '#'.
    $name = 'format_grid/defaultcurrentselectedimagecontainertextcolour';
    $title = get_string('defaultcurrentselectedimagecontainertextcolour', 'format_grid');
    $description = get_string('defaultcurrentselectedimagecontainertextcolour_desc', 'format_grid');
    $default = format_grid::get_default_current_selected_image_container_text_colour();
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default current selected image container colour in hexadecimal RGB with preceding '#'.
    $name = 'format_grid/defaultcurrentselectedimagecontainercolour';
    $title = get_string('defaultcurrentselectedimagecontainercolour', 'format_grid');
    $description = get_string('defaultcurrentselectedimagecontainercolour_desc', 'format_grid');
    $default = format_grid::get_default_current_selected_image_container_colour();
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    /* Hide section title - 1 = no, 2 = yes. */
    $name = 'format_grid/defaulthidesectiontitle';
    $title = get_string('defaulthidesectiontitle', 'format_grid');
    $description = get_string('defaulthidesectiontitle_desc', 'format_grid');
    $default = format_grid::get_default_hide_section_title();
    $choices = array(
        1 => new lang_string('no'),   // No.
        2 => new lang_string('yes')   // Yes.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Section title in grid box maximum length with 0 for no truncation. */
    $name = 'format_grid/defaultsectiontitlegridlengthmaxoption';
    $title = get_string('defaultsectiontitlegridlengthmaxoption', 'format_grid');
    $description = get_string('defaultsectiontitlegridlengthmaxoption_desc', 'format_grid');
    $default = format_grid::get_default_section_title_grid_length_max_option();
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, PARAM_INT));

    /* Section title box position - 1 = Inside, 2 = Outside. */
    $name = 'format_grid/defaultsectiontitleboxposition';
    $title = get_string('defaultsectiontitleboxposition', 'format_grid');
    $description = get_string('defaultsectiontitleboxposition_desc', 'format_grid');
    $default = format_grid::get_default_section_title_box_position();
    $choices = array(
        1 => new lang_string('sectiontitleboxpositioninside', 'format_grid'),
        2 => new lang_string('sectiontitleboxpositionoutside', 'format_grid')
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Section title box inside position - 1 = Top, 2 = Middle, 3 = Bottom. */
    $name = 'format_grid/defaultsectiontitleboxinsideposition';
    $title = get_string('defaultsectiontitleboxinsideposition', 'format_grid');
    $description = get_string('defaultsectiontitleboxinsideposition_desc', 'format_grid');
    $default = format_grid::get_default_section_title_box_inside_position();
    $choices = array(
        1 => new lang_string('sectiontitleboxinsidepositiontop', 'format_grid'),
        2 => new lang_string('sectiontitleboxinsidepositionmiddle', 'format_grid'),
        3 => new lang_string('sectiontitleboxinsidepositionbottom', 'format_grid')
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Section title box height. */
    $name = 'format_grid/defaultsectiontitleboxheight';
    $title = get_string('defaultsectiontitleboxheight', 'format_grid');
    $description = get_string('defaultsectiontitleboxheight_desc', 'format_grid');
    $default = format_grid::get_default_section_title_box_height();
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, PARAM_INT));

    /* Section title box opacity. */
    $name = 'format_grid/defaultsectiontitleboxopacity';
    $title = get_string('defaultsectiontitleboxopacity', 'format_grid');
    $description = get_string('defaultsectiontitleboxopacity_desc', 'format_grid');
    $default = format_grid::get_default_section_title_box_opacity();
    $choices = format_grid::get_default_opacities();
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Section title font size. */
    $name = 'format_grid/defaultsectiontitlefontsize';
    $title = get_string('defaultsectiontitlefontsize', 'format_grid');
    $description = get_string('defaultsectiontitlefontsize_desc', 'format_grid');
    $default = format_grid::get_default_section_title_font_size();
    $choices = format_grid::get_default_section_font_sizes();
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Section title alignment. */
    $name = 'format_grid/defaultsectiontitlealignment';
    $title = get_string('defaultsectiontitlealignment', 'format_grid');
    $description = get_string('defaultsectiontitlealignment_desc', 'format_grid');
    $default = format_grid::get_default_section_title_alignment();
    $choices = format_grid::get_horizontal_alignments();
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default section title text colour in hexadecimal RGB with preceding '#'.
    $name = 'format_grid/defaultsectiontitleinsidetitletextcolour';
    $title = get_string('defaultsectiontitleinsidetitletextcolour', 'format_grid');
    $description = get_string('defaultsectiontitleinsidetitletextcolour_desc', 'format_grid');
    $default = format_grid::get_default_section_title_inside_title_text_colour();
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default section title background colour in hexadecimal RGB with preceding '#'.
    $name = 'format_grid/defaultsectiontitleinsidetitlebackgroundcolour';
    $title = get_string('defaultsectiontitleinsidetitlebackgroundcolour', 'format_grid');
    $description = get_string('defaultsectiontitleinsidetitlebackgroundcolour_desc', 'format_grid');
    $default = format_grid::get_default_section_title_inside_title_background_colour();
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    /* Show section title summary on hover - 1 = no, 2 = yes. */
    $name = 'format_grid/defaultshowsectiontitlesummary';
    $title = get_string('defaultshowsectiontitlesummary', 'format_grid');
    $description = get_string('defaultshowsectiontitlesummary_desc', 'format_grid');
    $default = format_grid::get_default_show_section_title_summary();
    $choices = array(
        1 => new lang_string('no'),   // No.
        2 => new lang_string('yes')   // Yes.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Show section title summary on hover position - 1 = top, 2 = bottom, 3 = left and 4 = right. */
    $name = 'format_grid/defaultsetshowsectiontitlesummaryposition';
    $title = get_string('defaultsetshowsectiontitlesummaryposition', 'format_grid');
    $description = get_string('defaultsetshowsectiontitlesummaryposition_desc', 'format_grid');
    $default = format_grid::get_default_set_show_section_title_summary_position();
    $choices = array(
        1 => new lang_string('top', 'format_grid'),
        2 => new lang_string('bottom', 'format_grid'),
        3 => new lang_string('left', 'format_grid'),
        4 => new lang_string('right', 'format_grid')
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Section title summary maximum length with 0 for no truncation. */
    $name = 'format_grid/defaultsectiontitlesummarymaxlength';
    $title = get_string('defaultsectiontitlesummarymaxlength', 'format_grid');
    $description = get_string('defaultsectiontitlesummarymaxlength_desc', 'format_grid');
    $default = format_grid::get_default_section_title_summary_max_length();
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, PARAM_INT));

    // Default section title summary text colour on hover in hexadecimal RGB with preceding '#'.
    $name = 'format_grid/defaultsectiontitlesummarytextcolour';
    $title = get_string('defaultsectiontitlesummarytextcolour', 'format_grid');
    $description = get_string('defaultsectiontitlesummarytextcolour_desc', 'format_grid');
    $default = format_grid::get_default_section_title_summary_text_colour();
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default section title summary background colour on hover in hexadecimal RGB with preceding '#'.
    $name = 'format_grid/defaultsectiontitlesummarybackgroundcolour';
    $title = get_string('defaultsectiontitlesummarybackgroundcolour', 'format_grid');
    $description = get_string('defaultsectiontitlesummarybackgroundcolour_desc', 'format_grid');
    $default = format_grid::get_default_section_title_summary_background_colour();
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    /* Section title title summary opacity on hover. */
    $name = 'format_grid/defaultsectiontitlesummarybackgroundopacity';
    $title = get_string('defaultsectiontitlesummarybackgroundopacity', 'format_grid');
    $description = get_string('defaultsectiontitlesummarybackgroundopacity_desc', 'format_grid');
    $default = format_grid::get_default_section_title_summary_opacity();
    $choices = format_grid::get_default_opacities();
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

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

    /* Fix the section container popup to the screen. 1 = no, 2 = yes */
    $name = 'format_grid/defaultfitsectioncontainertowindow';
    $title = get_string('defaultfitsectioncontainertowindow', 'format_grid');
    $description = get_string('defaultfitsectioncontainertowindow_desc', 'format_grid');
    $default = 1;
    $choices = array(
        1 => new lang_string('no'),   // No.
        2 => new lang_string('yes')   // Yes.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Grey out hidden sections. */
    $name = 'format_grid/defaultgreyouthidden';
    $title = get_string('greyouthidden', 'format_grid');
    $description = get_string('greyouthidden_desc', 'format_grid');
    $default = 1;
    $choices = array(
        1 => new lang_string('no'),   // No.
        2 => new lang_string('yes')   // Yes.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Custom mouse pointers - 1 = no, 2 = yes. */
    $name = 'format_grid/defaultcustommousepointers';
    $title = get_string('custommousepointers', 'format_grid');
    $description = get_string('custommousepointers_desc', 'format_grid');
    $default = 2;
    $choices = array(
        1 => new lang_string('no'),   // No.
        2 => new lang_string('yes')   // Yes.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
}
