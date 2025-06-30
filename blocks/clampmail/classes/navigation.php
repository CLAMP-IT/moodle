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
 * Navigation functions.
 *
 * @package   block_clampmail
 * @copyright 2019 Collaborative Liberal Arts Moodle Project
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_clampmail;

/**
 * Navigation functions.
 *
 * @package   block_clampmail
 * @copyright 2019 Collaborative Liberal Arts Moodle Project
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navigation {

    /**
     * Get all the navigation icons.
     *
     * @param context $context the course context.
     * @return array of navigation icons
     */
    public static function get_icons($context) {
        global $OUTPUT;
        $icons = array();

        if (!has_capability('block/clampmail:cansend', $context)) {
            // No navigation without this capability.
            return $icons;
        }

        // Base icons.
        $icons = [
            $OUTPUT->pix_icon('i/email', '', 'moodle', array('class' => 'icon')),
            $OUTPUT->pix_icon('i/edit', '', 'moodle', array('class' => 'icon')),
            $OUTPUT->pix_icon('i/settings', '', 'moodle', array('class' => 'icon')),
            $OUTPUT->pix_icon('i/settings', '', 'moodle', array('class' => 'icon')),
        ];

        // Alternate email icons.
        if (has_capability('block/clampmail:allowalternate', $context)) {
            $icons[] = $OUTPUT->pix_icon('i/edit', '', 'moodle', array('class' => 'icon'));
        }

        // Configuration icons.
        if (has_capability('block/clampmail:canconfig', $context)) {
            $icons[] = $OUTPUT->pix_icon('i/settings', '', 'moodle', array('class' => 'icon'));
        }

        return $icons;
    }

    /**
     * Get all the navigation items.
     *
     * @param int $course the course id.
     * @param context $context the course context.
     * @return array of navigation links
     */
    public static function get_links($course, $context) {
        $links = array();

        if (!has_capability('block/clampmail:cansend', $context)) {
            // No navigation without this capability.
            return $links;
        }

        // Base links.
        $links = [
            \html_writer::link(
                new \moodle_url('/blocks/clampmail/email.php', array('courseid' => $course)),
                get_string('composenew', 'block_clampmail')
            ),
            \html_writer::link(
                new \moodle_url('/blocks/clampmail/signature.php', array('courseid' => $course)),
                get_string('manage_signatures', 'block_clampmail')
            ),
            \html_writer::link(
                new \moodle_url('/blocks/clampmail/emaillog.php', array('courseid' => $course, 'type' => 'drafts')),
                get_string('drafts', 'block_clampmail')
            ),
            \html_writer::link(
                new \moodle_url('/blocks/clampmail/emaillog.php', array('courseid' => $course)),
                get_string('log', 'block_clampmail')
            )
        ];

        // Alternate email configuration link.
        if (has_capability('block/clampmail:allowalternate', $context)) {
            $links[] = \html_writer::link(
                new \moodle_url('/blocks/clampmail/alternate.php', array('courseid' => $course)),
                get_string('alternate', 'block_clampmail')
            );
        }

        // Configuration link.
        if (has_capability('block/clampmail:canconfig', $context)) {
            $links[] = \html_writer::link(
                new \moodle_url('/blocks/clampmail/config.php', array('courseid' => $course)),
                get_string('config', 'block_clampmail')
            );
        }

        return $links;
    }

    /**
     * Display internal navigation.
     *
     * @param array $items the navigation items to display.
     * @param string $heading the internal page heading.
     * @return string rendered output
     */
    public static function print_navigation($items, $heading) {
        global $OUTPUT;

        $html = \html_writer::alist($items, array('class' => 'internal-navigation'));
        $html .= $OUTPUT->heading($heading, '3');
        return $html;
    }
}
