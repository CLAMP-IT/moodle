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
 * JavaScript for the NanoGong TinyMCE plugin
 *
 * @author     Ning
 * @author     Gibson
 * @copyright  2012 The Gong Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2.2
 */

var NanogongDialog = {
    insert : function(itemid) {
        var applet = document.getElementById("nanogong");
        var message = document.getElementById("nanogong_message");

        if (applet) {
            // Tell the applet to post the voice recording to the backend PHP code
            var url = tinyMCEPopup.getWindowArg('plugin_url') + '/upload_audio.php?itemid=' + itemid;
            var soundfile = applet.sendGongRequest("PostToForm", url, "nanogong_upload_file", "", "nanogongaudio");
        
            if (soundfile == null || soundfile == "") {
                message.innerHTML = "Nothing has been recorded yet.";
            }
            else {
                message.innerHTML = "Successful!";
                
                // Insert the contents from the input into the document
                tinyMCEPopup.execCommand('mceInsertContent', false, tinyMCEPopup.editor.dom.createHTML('img', {
                    'class' : 'mceNanogong',
                    src : tinyMCEPopup.getWindowArg('plugin_url') + '/img/nanogong.gif',
                    style : 'vertical-align: middle',
                    alt : soundfile
                }));
                tinyMCEPopup.restoreSelection();
                tinyMCEPopup.close();
            }
        }
        else {
            tinyMCEPopup.restoreSelection();
            tinyMCEPopup.close();
        }
    }
};
