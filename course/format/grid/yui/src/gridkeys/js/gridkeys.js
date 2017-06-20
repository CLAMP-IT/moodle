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
 * @copyright  &copy; 2013 onwards G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.format_grid = M.format_grid || {};
M.format_grid.gridkeys = M.format_grid.gridkeys || {};
M.format_grid.gridkeys = {
    currentGridBox: false,
    currentGridBoxIndex: 0,
    findfocused: function() {
        var focused = document.activeElement;
        if (!focused || focused == document.body) {
            focused = null;
        } else if (document.querySelector) {
            focused = document.querySelector(":focus");
        }
        M.format_grid.gridkeys.currentGridBox = false;
        if (focused && focused.id) {
            Y.log('Focus id: ' + focused.id);
            if (focused.id.indexOf('gridsection-') > -1) {
                Y.log('Grid id: ' + focused.id);
                M.format_grid.gridkeys.currentGridBox = true;
                M.format_grid.gridkeys.currentGridBoxIndex = parseInt(focused.id.replace("gridsection-", ""), 10);
            }
        }
        return M.format_grid.gridkeys.currentGridBox;
    },
    init: function(params) {
        Y.on('esc', function (e) {
            e.preventDefault();
            Y.log("Esc pressed");
            Y.log("Selected section no: " + M.format_grid.selected_section_no);
            M.format_grid.icon_toggle(e);
        });
        // Initiated in CONTRIB-3240...
        Y.on('enter', function (e) {
            if (M.format_grid.gridkeys.currentGridBox) {
                e.preventDefault();
                if (e.shiftKey) {
                    Y.log("Shift Enter pressed");
                    Y.log("Selected section no: " + M.format_grid.selected_section_no);
                    M.format_grid.icon_toggle(e);
                } else {
                    Y.log("Enter pressed");
                    Y.log("Selected section no: " + M.format_grid.selected_section_no);
                    M.format_grid.icon_toggle(e);
                }
            }
        });
        Y.on('tab', function (/*e*/) {
            setTimeout(function() {
                // Cope with the fact that the default event happens after us.
                // Therefore we need to react after focus has moved.
                if (M.format_grid.gridkeys.findfocused()) {
                    M.format_grid.tab(M.format_grid.gridkeys.currentGridBoxIndex);
                }
            }, 250);
        });
        Y.on('space', function (e) {
            if (M.format_grid.gridkeys.currentGridBox) {
                e.preventDefault();
                Y.log("Space pressed");
                Y.log("Selected section no: " + M.format_grid.selected_section_no);
                M.format_grid.icon_toggle(e);
            }
        });
        Y.on('left', function (e) {
            e.preventDefault();
            Y.log("Left pressed");
            if (params.rtl) {
                M.format_grid.next_section(e);
            } else {
                M.format_grid.previous_section(e);
            }
        });
        Y.on('right', function (e) {
            e.preventDefault();
            Y.log("Right pressed");
            if (params.rtl) {
                M.format_grid.previous_section(e);
            } else {
                M.format_grid.next_section(e);
            }
        });
    }
};