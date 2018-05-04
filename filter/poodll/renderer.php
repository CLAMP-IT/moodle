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


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/outputlib.php');

/**
 * A custom renderer class that extends the plugin_renderer_base.
 *
 * @package filter_poodll
 * @copyright 2015 Justin Hunt (poodllsupport@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_poodll_renderer extends plugin_renderer_base
{


    public function fetchLiterallyCanvas($html)
    {
        global $PAGE;
        //The strings we need for js

        $PAGE->requires->strings_for_js(array('insert',
            'cancel',
            'recui_record',
            'recui_recordorchoose',
            'recui_pause',
            'recui_play',
            'recui_stop',
            'recui_save',
            'recui_continue',
            'recui_uploading',
            'recui_converting',
            'recui_uploading',
            'recui_uploadafile',
            'recui_uploadsuccess',
            'recui_openrecorderapp',
            'recui_awaitingconfirmation',
            'recui_uploaderror',
            'recui_takesnapshot',
            'recui_cancelsnapshot',
            'recui_nothingtosaveerror',
        ),
            'filter_poodll');
        return $html;

    }

    public function fetchDrawingBoard($html)
    {
        global $PAGE;
        //The strings we need for js

        $PAGE->requires->strings_for_js(array('insert',
            'cancel',
            'recui_record',
            'recui_restart',
            'recui_recordorchoose',
            'recui_pause',
            'recui_play',
            'recui_stop',
            'recui_save',
            'recui_continue',
            'recui_uploading',
            'recui_converting',
            'recui_uploading',
            'recui_uploadafile',
            'recui_uploadsuccess',
            'recui_openrecorderapp',
            'recui_awaitingconfirmation',
            'recui_uploaderror',
            'recui_takesnapshot',
            'recui_cancelsnapshot',
            'recui_nothingtosaveerror',
        ),
            'filter_poodll');
        return $html;


    }

    public function fetchAudioPlayer($html)
    {
        return $html;

    }

    public function fetchVideoPlayer($html)
    {
        return $html;

    }

    public function fetchIFrameSWFWidgetCode($widget, $paramsArray, $width, $height, $bgcolor = "#FFFFFF")
    {
        global $CFG;

        //There seems to be an internal margin on the iframe
        //which I could not cancel entirely. So we compensate here to show all the widget
        $marginadjust = 5;
        $fwidth = $marginadjust + $width;
        $fheight = $marginadjust + $height;

        //build the parameter string out of the passed in array
        $params = "?";
        foreach ($paramsArray as $key => $value) {
            $params .= '&' . $key . '=' . $value;
        }

        //add in any common params
        $params .= '&debug=false&lzproxied=false';

        //path to our js idgets folder
        $pathtoSWF = $CFG->wwwroot . '/filter/poodll/flash/';

        $retframe = "<iframe scrolling=\"no\" class=\"fitvidsignore\" frameBorder=\"0\" src=\"{$pathtoSWF}poodlliframe.php?widget={$widget}&paramstring=" . urlencode($params) . "&width={$width}&height={$height}&bgcolor={$bgcolor}\" width=\"{$fwidth}\" height=\"{$fheight}\"></iframe>";
        return $retframe;
    }

    public function fetchJSWidgetiFrame($widget, $rawparams, $width, $height, $bgcolor = "#FFFFFF", $usemastersprite = "false")
    {
        global $CFG;

        //build the parameter string out of the passed in array
        $params = "?";
        foreach ($rawparams as $key => $value) {
            $params .= '&' . $key . '=' . $value;
        }

        //add in any common params
        $params .= '&debug=false&lzproxied=false';

        //path to our js idgets folder
        $pathtoJS = $CFG->wwwroot . '/filter/poodll/js/';
        $pathtowidgetfolder = $CFG->wwwroot . '/filter/poodll/js/' . $widget . '/';


        $retframe = "<iframe scrolling=\"no\" frameBorder=\"0\" src=\"{$pathtoJS}poodlliframe.php?widget={$widget}&paramstring=" . urlencode($params) . "&width={$width}&height={$height}&bgcolor={$bgcolor}&usemastersprite={$usemastersprite}\" width=\"{$width}\" height=\"{$height}\"></iframe>";
        return $retframe;
    }

    /* TO DO: make this more generic. ie not just poodllrecorder */
    public function fetchAMDRecorderEmbedCode($widgetopts, $widgetid)
    {
        global $CFG, $PAGE;

        $widgetopts->widgetid = $widgetid;

        //The CSS selector string
        $container = $widgetid . 'Container';
        $selector = '#' . $container;
        $widgetopts->selector = $selector;

        //The strings we need for js

        $PAGE->requires->strings_for_js(array('insert',
            'cancel',
            'recui_finished',
            'recui_ready',
            'recui_playing',
            'recui_recording',
            'recui_record',
            'recui_restart',
            'recui_recordorchoose',
            'recui_pause',
            'recui_play',
            'recui_stop',
            'recui_save',
            'recui_continue',
            'recui_uploading',
            'recui_converting',
            'recui_uploading',
            'recui_uploadafile',
            'recui_uploadsuccess',
            'recui_awaitingconversion',
            'recui_openrecorderapp',
            'recui_awaitingconfirmation',
            'recui_uploaderror',
            'recui_nothingtosaveerror',
            'recui_takesnapshot',
            'recui_cancelsnapshot'
        ),
            'filter_poodll');

        //convert opts to json
        $jsonstring = json_encode($widgetopts);
        //we put the opts in html on the page because moodle/AMD doesn't like lots of opts in js
        $opts_html = html_writer::tag('input', '', array('id' => 'amdopts_' . $widgetopts->widgetid, 'type' => 'hidden', 'value' => $jsonstring));
        $PAGE->requires->js_call_amd("filter_poodll/poodllrecorder", 'init', array(array('widgetid' => $widgetid)));
        $returnhtml = $opts_html . html_writer::div('', 'filter_poodll_recorder_placeholder', array('id' => $container));
        return $returnhtml;
    }


//This is used for all the flash widgets
    public function fetchLazloEmbedCode($widgetopts, $widgetid, $jsmodule)
    {
        global $CFG, $PAGE;
        echo "what !";
        die;
        //this init the M.mod_readaloud thingy, after the page has loaded.
        $PAGE->requires->js(new \moodle_url($CFG->httpswwwroot . '/filter/poodll/flash/embed-compressed.js'));

        $PAGE->requires->js_init_call('M.filter_poodll.laszlohelper.init', array($widgetopts), false, $jsmodule);
        $returnhtml = html_writer::div('', '', array('id' => $widgetid . 'Container'));
        return $returnhtml;
    }

    public function fetchTemplateSelector($conf,$templatecount)
    {
        global $CFG, $OUTPUT;
        $options=Array();
        for ($i=1;$i<=$templatecount;$i++) {
            $options['filter_poodll_templatepage_' . $i]=$conf->{'templatename_' . $i};
        }
       // $options = array(1 => 'Page 1', 2 => 'Page 2', 3 => 'Page 3');
        $select = $OUTPUT->single_select($CFG->wwwroot . '/admin/settings.php', 'section', $options, 'template_selector');
        echo $select;
    }


}
