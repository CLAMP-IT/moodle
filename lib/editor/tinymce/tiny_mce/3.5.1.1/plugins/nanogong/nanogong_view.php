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
 * The NanoGong TinyMCE plugin
 *
 * @author     Gibson
 * @copyright  2012 The Gong Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2.2
 */

define('NO_MOODLE_COOKIES', false); // Session not used here

require_once(dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))) . '/config.php');

require_login();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Listen to NanoGong recording</title>
    <script type="text/javascript" src="../../tiny_mce_popup.js"></script>
    <script type="text/javascript" src="js/nanogong.js"></script>
    <link href="css/nanogong.css" rel="stylesheet" type="text/css" />
</head>

<body>

<form>
    <table class="main">
        <tr><td colspan="2">Press the play button to hear the recording.</td></tr>
        <tr>
            <td class="applet">
                <table>
                    <tr>
                        <td><img src="img/nanogong.gif" alt="NanoGong Sound" /></td>
                        <td><applet id="nanogong" archive="<?php echo "{$CFG->wwwroot}/filter/nanogong/nanogong.jar"; ?>" code="gong.NanoGong" width="130" height="40">
                            <param name="ShowTime" value="true" />
                            <param name="ShowAudioLevel" value="false" />
                            <param name="ShowRecordButton" value="false" />
                            <script type="text/javascript">
                            var ed = tinyMCEPopup.editor;
                            var node = ed.selection.getNode();
                            var url = ed.dom.getAttrib(node, 'alt');
                            if (url.indexOf("pluginfile.php") == 0) { // version 4.2 and 4.2.1
                                url = "<?php echo "{$CFG->wwwroot}/draftfile.php"; ?>" + url.substring("pluginfile.php".length);
                            }
                            document.write("<param name=\"SoundFileURL\" value=\"" + url + "\" />");
                            </script>
                        </applet></td>
                        <td style="width: 16px">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr><td colspan="2">Click OK to continue.</td></tr>
    </table>
    <input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
</form>

</body>

</html>
