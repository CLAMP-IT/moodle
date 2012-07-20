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
 * Show NanoGong applet after clicking the corresponding image
 *
 * @author     Ning
 * @author     Gibson
 * @package    filter
 * @subpackage nanogong
 * @copyright  2012 The Gong Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2.2
 */

defined('MOODLE_INTERNAL') || die();

class filter_nanogong extends moodle_text_filter {

    public function filter($text, array $options = array()) {
        global $CFG, $PAGE, $COURSE, $DB;

        if (preg_match_all('/<img.*?class="mceNanogong".*?>/', $text, $imgs) == 0) {
            return $text;
        }

        foreach ($imgs[0] as $img) {
            $startpos = strpos($text, $img);
            $totallength = strlen($img);

            // Search for the sound file path from the title attribute (version 4.2),
            // from the longdesc attribute (version 4.2.1) or from the alt attribute (version 4.2.2 onwards)
            $path = "";
            if (preg_match('/title="(.*?)"/', $img, $title) > 0) { // version 4.2
                if (!empty($title[1])) {
                    $path = $title[1];
                }
            }
            if (preg_match('/longdesc="(.*?)"/', $img, $longdesc) > 0) { // version 4.2.1
                if (!empty($longdesc[1])) {
                    $path = $longdesc[1];
                }
            }
            if (empty($path)) {
                if (preg_match('/alt="(.*?)"/', $img, $alt) > 0) { // version 4.2.2
                    if (!empty($alt[1])) {
                        $path = $alt[1];
                    }
                }
            }
            if (empty($path)) continue;

            // Construct the NanoGong icon for sound file location
            $icon  = "<span style='position:relative'>";
            $icon .=   "<img class='filter_nanogong_img' src='{$CFG->wwwroot}/filter/nanogong/pix/icon.gif' ";
            $icon .=        "title='" . get_string('imgtitle', 'filter_nanogong') . "'";
            $icon .=        "style='vertical-align: middle' path='$path' />";
            $icon .= "</span>";

            $text = substr_replace($text, $icon, $startpos, $totallength); 
        }

        // Return the text plus the NanoGong JavaScript for setting up the applet
        return "<script type='text/javascript' src='{$CFG->wwwroot}/filter/nanogong/nanogong.js'></script>" . $text;
    }

}

?>
