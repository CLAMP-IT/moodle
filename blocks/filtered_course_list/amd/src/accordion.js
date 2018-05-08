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
 * This file contains an AMD/jQuery module to expand and collapse course rubrics.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    return {
        init: function(params) {
            var blockid = params.blockid;
            $('#' + blockid + ' .block-fcl__rubric').each(function() {
                if (!($(this).hasClass('block-fcl__rubric--expanded'))) {
                    $(this).addClass('block-fcl__rubric--collapsed');
                    $(this).attr('aria-expanded', 'false');
                    $(this).next().attr('aria-hidden', 'true');
                }
                $(this).wrapInner(document.createElement('a'));
                $(this).find('a').attr('href', '#');
                $(this).on('click', function(event) {
                    event.preventDefault();
                    $('.block-fcl__rubric').each(function() {
                        $(this).attr('aria-selected', 'false');
                    });
                    $(this).attr('aria-selected', 'true');
                    if ($(this).hasClass('block-fcl__rubric--collapsed')) {
                        $(this).removeClass('block-fcl__rubric--collapsed');
                        $(this).addClass('block-fcl__rubric--expanded');
                        $(this).attr('aria-expanded', 'true');
                        $(this).next().attr('aria-hidden', 'false');
                    } else if ($(this).hasClass('block-fcl__rubric--expanded')) {
                        $(this).removeClass('block-fcl__rubric--expanded');
                        $(this).addClass('block-fcl__rubric--collapsed');
                        $(this).attr('aria-expanded', 'false');
                        $(this).next().attr('aria-hidden', 'true');
                    }
                });
            });
        }
    };
});
