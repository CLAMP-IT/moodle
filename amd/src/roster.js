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
 * This file contains an AMD/jQuery module which supports learning mode.
 *
 * @copyright  2018 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/str'], function($, str) {

    return {
        init: function() {
            var toggle = $('button#report-roster-toggle');
            var displaynone = str.get_string('learningmodeoff', 'report_roster');
            var displayblock = str.get_string('learningmodeon', 'report_roster');

            $.when(displaynone, displayblock).done(function(localizedDisplayNone, localizedDisplayBlock) {
                toggle.on('click', function() {
                    if (toggle.html() === localizedDisplayNone) {
                        $('ul.report-roster').find('li span').css('display', 'none');
                        toggle.html(localizedDisplayBlock);
                    } else {
                        $('ul.report-roster').find('li span').css('display', 'block');
                        toggle.html(localizedDisplayNone);
                    }
                });
            });
        }
    };
});
