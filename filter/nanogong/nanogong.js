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
 * Javascript helper function for the NanoGong filter
 *
 * @author     Gibson
 * @package    filter
 * @subpackage nanogong
 * @copyright  2012 The Gong Project {@link http://nanogong.ust.hk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2.2
 */

if (!M.filter_nanogong) {

    M.filter_nanogong = {};

    YUI(M.yui.loader).use("node", "event", function (Y) {
        Y.on("domready", function (e) {

            // Append the applet at the end of the page
            var applet = '';
            applet += '<div id="filter_nanogong_div" ';
            applet +=      'style="position:absolute;top:-40px;left:-130px;z-index:100;visibility:hidden">';
            applet +=     '<applet id="filter_nanogong_applet" archive="' + M.cfg.wwwroot + '/filter/nanogong/nanogong.jar" code="gong.NanoGong" width="130" height="40">';
            applet +=         '<param name="ShowTime" value="true" />';
            applet +=         '<param name="ShowAudioLevel" value="false" />';
            applet +=         '<param name="ShowRecordButton" value="false" />';
            applet +=     '</applet>';
            applet += '</div>';

            var body = Y.one(document.body);
            body.append(applet);

            Y.on('click', function(e) {
                var img = e.target;
                var div = Y.one('#filter_nanogong_div');
                var path = img.getAttribute('path');

                // Switch off the NanoGong player when the speaker icon is clicked again
                if (M.filter_nanogong.currentpath == path) {
                    div.setStyle('visibility', 'hidden');
                    M.filter_nanogong.currentpath = '';
                    return;
                }

                // Switch on the NanoGong applet when the speaker icon is clicked
                div.setStyle('visibility', 'visible');
                M.filter_nanogong.currentpath = path;

                // Put the NanoGong applet to the right of the img icon
                var region = img.get('region');
                div.setXY([region.right, region.top]);

                // Load the sound from the page in the applet
                var applet = Y.one("#filter_nanogong_applet")._node;
                applet.sendGongRequest('LoadFromURL', path);
            }, '.filter_nanogong_img');
        });  
    });
    
}
