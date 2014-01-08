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
 * Cosmic libraries
 *
 * @package    theme_cosmic
 * @copyright  2013 Lafayette College ITS
 * @copyright  2012 Julian Ridden (original Rocket theme)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function cosmic_process_css($css, $theme) {
    // Set the theme background and highlights.
    if (!empty($theme->settings->themecolor)) {
        $themecolor = $theme->settings->themecolor;
    } else {
        $themecolor = null;
    }
    $css = cosmic_set_themecolor($css, $themecolor);

    // Set the theme trim color.
    if (!empty($theme->settings->themetrimcolor)) {
        $themetrimcolor = $theme->settings->themetrimcolor;
    } else {
        $themetrimcolor = null;
    }
    $css = cosmic_set_themetrimcolor($css, $themetrimcolor);

    // Set the custommenu color.
    if (!empty($theme->settings->menucolor)) {
        $menucolor = $theme->settings->menucolor;
    } else {
        $menucolor = null;
    }
    $css = cosmic_set_menucolor($css, $menucolor);

    // Set the custommenu hover color.
    if (!empty($theme->settings->menuhovercolor)) {
        $menuhovercolor = $theme->settings->menuhovercolor;
    } else {
        $menuhovercolor = null;
    }
    $css = cosmic_set_menuhovercolor($css, $menuhovercolor);

    // Set the custommenu trim color.
    if (!empty($theme->settings->menutrimcolor)) {
        $menutrimcolor = $theme->settings->menutrimcolor;
    } else {
        $menutrimcolor = null;
    }
    $css = cosmic_set_menutrimcolor($css, $menutrimcolor);

    // Set the custommenu link color.
    if (!empty($theme->settings->menulinkcolor)) {
        $menulinkcolor = $theme->settings->menulinkcolor;
    } else {
        $menulinkcolor = null;
    }
    $css = cosmic_set_menulinkcolor($css, $menulinkcolor);

    // Set the content link color.
    if (!empty($theme->settings->contentlinkcolor)) {
        $contentlinkcolor = $theme->settings->contentlinkcolor;
    } else {
        $contentlinkcolor = null;
    }
    $css = cosmic_set_contentlinkcolor($css, $contentlinkcolor);

    // Set the block link color.
    if (!empty($theme->settings->blocklinkcolor)) {
        $blocklinkcolor = $theme->settings->blocklinkcolor;
    } else {
        $blocklinkcolor = null;
    }
    $css = cosmic_set_blocklinkcolor($css, $blocklinkcolor);

    // Set the background image for the logo.
    if (!empty($theme->settings->logo)) {
        $logo = $theme->settings->logo;
    } else {
        $logo = null;
    }
    $css = cosmic_set_logo($css, $logo);

    // Set the banner height.
    if (!empty($theme->settings->bannerheight)) {
        $bannerheight = $theme->settings->bannerheight;
    } else {
        $bannerheight = null;
    }
    $css = cosmic_set_bannerheight($css, $bannerheight);

    // Set the screenwidth.
    if (!empty($theme->settings->screenwidth)) {
        $screenwidth = $theme->settings->screenwidth;
    } else {
        $screenwidth = null;
    }
    $css = cosmic_set_screenwidth($css, $screenwidth);

    // Set the breadcrumb width.
    $css = cosmic_set_breadcrumbwidth($css, $theme->settings->breadcrumbwidth);

    // Toggle AutoHide functionality.
    if (!empty($theme->settings->autohide)) {
        $autohide = $theme->settings->autohide;
    } else {
        $autohide = null;
    }
    $css = cosmic_set_autohide($css, $autohide);

    // Set the background image for the banner.
    if (!empty($theme->settings->banner)) {
        $banner = $theme->settings->banner;
    } else {
        $banner = null;
    }
    $css = cosmic_set_banner($css, $banner);

    // Allow for additional custom CSS from admins.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = cosmic_set_customcss($css, $customcss);

    return $css;

}

function cosmic_set_themecolor($css, $themecolor) {
    $tag = '[[setting:themecolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#5faff2';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function cosmic_set_themetrimcolor($css, $themetrimcolor) {
    $tag = '[[setting:themetrimcolor]]';
    $replacement = $themetrimcolor;
    if (is_null($replacement)) {
        $replacement = '#660000';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function cosmic_set_menucolor($css, $menucolor) {
    $tag = '[[setting:menucolor]]';
    $replacement = $menucolor;
    if (is_null($replacement)) {
        $replacement = '#76777c';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function cosmic_set_menuhovercolor($css, $menuhovercolor) {
    $tag = '[[setting:menuhovercolor]]';
    $replacement = $menuhovercolor;
    if (is_null($replacement)) {
        $replacement = '#919193';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function cosmic_set_menulinkcolor($css, $menulinkcolor) {
    $tag = '[[setting:menulinkcolor]]';
    $replacement = $menulinkcolor;
    if (is_null($replacement)) {
        $replacement = '#ffffff';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function cosmic_set_contentlinkcolor($css, $contentlinkcolor) {
    $tag = '[[setting:contentlinkcolor]]';
    $replacement = $contentlinkcolor;
    if (is_null($replacement)) {
        $replacement = '#006699';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function cosmic_set_blocklinkcolor($css, $blocklinkcolor) {
    $tag = '[[setting:blocklinkcolor]]';
    $replacement = $blocklinkcolor;
    if (is_null($replacement)) {
        $replacement = '#333333';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function cosmic_set_menutrimcolor($css, $menutrimcolor) {
    $tag = '[[setting:menutrimcolor]]';
    $replacement = $menutrimcolor;
    if (is_null($replacement)) {
        $replacement = '#4c4c4c';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function cosmic_set_logo($css, $logo) {
    global $OUTPUT;
    $tag = '[[setting:logo]]';
    $replacement = $logo;
    if (is_null($replacement)) {
        $replacement = $OUTPUT->pix_url('logo/logo', 'theme');
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function cosmic_set_banner($css, $banner) {
    global $OUTPUT;
    $tag = '[[setting:banner]]';
    $replacement = $banner;
    if (is_null($replacement)) {
        $replacement = $OUTPUT->pix_url('banner/default', 'theme');
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function cosmic_set_bannerheight($css, $bannerheight) {
    $tag = '[[setting:bannerheight]]';
    $replacement = $bannerheight;
    if (is_null($replacement)) {
        $replacement = '250';
    }
    $css = str_replace($tag, ($replacement-5).'px', $css);
    return $css;
}

function cosmic_set_breadcrumbwidth($css, $width) {
    global $OUTPUT;
    $tag = '[[setting:breadcrumbwidth]]';
    $css = str_replace($tag, $width, $css);
    return $css;
}

function cosmic_set_screenwidth($css, $screenwidth) {
    $tag = '[[setting:screenwidth]]';
    $breadcrumbwidth = '[[setting:breadcrumbwidth]]';
    $screenwidthmargintag = '[[setting:screenwidthmargintag]]';
    $replacement = $screenwidth;
    if (is_null($replacement)) {
        $replacement = '1000';
    }
    if ( $screenwidth == "1000" ) {
        $css = str_replace($tag, $replacement.'px', $css);
        $css = str_replace($screenwidthmargintag, ($replacement+5).'px', $css);
        $css = str_replace($breadcrumbwidth, ($replacement-470).'px', $css);
    }
    if ( $replacement == "97" ) {
        $css = str_replace($tag, $replacement.'%', $css);
        $css = str_replace($breadcrumbwidth, '50%', $css);
    }
    return $css;
}

function cosmic_set_autohide($css, $autohide) {
    $tag = '[[setting:autohide]]';
    $replacement = $autohide;
    if (is_null($replacement)) {
        $replacement = 'enable';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function cosmic_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function cosmic_allow_autohide($device) {
    global $PAGE;
    if (!isset($PAGE->theme->settings->autohide_devices)) {
        return false;
    }
    $devices = explode(",", $PAGE->theme->settings->autohide_devices);
    if (!empty($devices)) {
        return in_array($device, $devices);
    }
    return false;
}

/**
 * Generate updated custommenu with enroled courses listed
 */
class transmuted_custom_menu_item extends custom_menu_item {
    public function __construct(custom_menu_item $menunode) {
        parent::__construct($menunode->get_text(), $menunode->get_url(), $menunode->get_title(),
            $menunode->get_sort_order(), $menunode->get_parent());
        $this->children = $menunode->get_children();

        $matches = array();
        if (preg_match('/^\[\[([a-zA-Z0-9\-\_\:]+)\]\]$/', $this->text, $matches)) {
            try {
                $this->text = get_string($matches[1], 'theme_cosmic');
            } catch (Exception $e) {
                $this->text = $matches[1];
            }
        }

        $matches = array();
        if (preg_match('/^\[\[([a-zA-Z0-9\-\_\:]+)\]\]$/', $this->title, $matches)) {
            try {
                $this->title = get_string($matches[1], 'theme_cosmic');
            } catch (Exception $e) {
                $this->title = $matches[1];
            }
        }
    }
}
