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
 *  RTMP streaming media filter plugin
 *
 *  This filter will replace any rtmp links to a media file with
 *  a media plugin that plays that media inline
 *
 * @package    filter_rtmp
 * @author     Lacey Vickery, Fred Woolard (based on mediaplugin filter {@link http://moodle.com})
 * @copyright  2012 Appalachian State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


class filter_rtmp extends moodle_text_filter
{

  // Inherited member vars
  //
  ///** @var object The context we are in. */
  //protected $context;
  ///** @var array Any local configuration for this filter in this context. */
  //protected $localconfig;

    /** @var bool True if currently filtering trusted text */
    private $trusted;
    /** @var core_media_renderer Media renderer */
    private $mediarenderer;
    /** @var string Partial regex pattern indicating possible embeddable content */
    private $embedmarkers;



    /**
     * Search for specific href values within <a> tags beginning
     * with rtmp protocol and modify the tag for the flowplayer
     *
     * @param string    $text
     * @param array     $options
     *
     * @return string
     * @uses $CFG, $PAGE
     */
    public function filter($text, array $options = array())
    {
        global $CFG, $PAGE;
        static $js_init_called = false;

        // Need this test unfortunately because the page content
        // is pre-rendered in some places such as question preview
        // which goofs the $js_init_called assignment--which is
        // used to prevent some necessary inline JavaScript from
        // being emitted more than once
        if ($PAGE->pagetype == 'question-preview' && $PAGE->state != moodle_page::STATE_IN_BODY) {
            return $text;
        }

        if (!is_string($text) or empty($text)) {
            // non string data can not be filtered anyway
            return $text;
        }

        if (stripos($text, '</a>') === false) {
            // Performance shortcut - if not </a> tag, nothing can match.
            return $text;
        }

        if (!$this->mediarenderer) {
            $this->mediarenderer = $PAGE->get_renderer('filter_rtmp');
            $this->embedmarkers = $this->mediarenderer->get_embeddable_markers();
        }

        // Check permissions, will be examined in the preg_replace callback
        $this->trusted = !empty($options['noclean']) or !empty($CFG->allowobjectembed);

        // Handle all links that contain any 'embeddable' marker text (it could
        // do all links, but the embeddable markers thing should make it faster
        // by meaning for most links it doesn't drop into PHP code).
        $regex = '~<a\s[^>]*href="(rtmp:\/\/(?:playlist=[^"]*|[^"]*(?:' . $this->embedmarkers . '))[^"]*)"[^>]*>([^>]*)</a>~is';
        $newtext = preg_replace_callback($regex, array($this, 'callback'), $text);

        // If no joy or no change then return original
        if (empty($newtext) || $newtext === $text) {
            return $text;
        }

        // Only want one init per page, so one and done
        if (!$js_init_called) {

            $js_init_called = true;

            // Need to emit page (global) js vars so our js module
            // can load the currently available Flowplayer files
            $newtext .= "\n" . html_writer::script(self::get_flowplayer_filenames());

            if (empty($CFG->cachetext)) {
                // If not caching filter output, then cleaner
                // to add a page requirement
                $PAGE->requires->js_init_call('M.filter_rtmp.init', null, true, array('name' => 'filter_rtmp', 'fullpath' => '/filter/rtmp/module.js', 'requires' => array('node')));
            } else {
                // If caching, this filter will never get called
                // until cache is refreshed, side-effect is that
                // needed call to M.filter_rtmp.init is not made
                // when needed, so have to make that js call part
                // of the substituted (and cached) content
                $newtext .= "\n"
                         . html_writer::script("M.yui.add_module({ filter_rtmp: { name: 'filter_rtmp', fullpath: '{$CFG->wwwroot}/filter/rtmp/module.js', requires: ['node'] }});\n"
                         .                     "YUI().use('node', function(Y) { Y.on('domready', function() { Y.use('filter_rtmp', function(Y) { M.filter_rtmp.init(Y); }); }); });");
            }

        }

        return $newtext;

    }



    /**
     * Callback routine passed to preg_replace_callback(). Replace
     * link with embedded content, if supported.
     *
     * @param array     $matches        Array provided by preg_replace_callback. [0] original text, [1] href attribute, [2] anchor label.
     * @return string
     */
    private function callback(array $matches)
    {

        // Check if we ignore it.
        if (preg_match('/class="[^"]*nomediaplugin/i', $matches[0])) {
            return $matches[0];
        }

        // Get name, use default if empty
        $name = trim($matches[2]);
        if (empty($name)) {
            $name = 'Media Stream (RTMP)';
        }

        // Split provided URL into alternatives.
        list($urls, $options) = self::split_alternatives($matches[1], $width, $height);


        // Trusted if $CFG allowing object embed and 'noclean'
        // was passed to the filter method as an option
        if ($this->trusted) {
            $options[core_media::OPTION_TRUSTED] = true;
        }

        // We could test whether embed is possible using can_embed, but to save
        // time, let's just embed it with the 'fallback to blank' option which
        // does most of the same stuff anyhow.
        $options[core_media::OPTION_FALLBACK_TO_BLANK] = true;

        // NOTE: Options are not passed through from filter because the 'embed'
        // code does not recognise filter options (it's a different kind of
        // option-space) as it can be used in non-filter situations.
        $result = $this->mediarenderer->embed_alternatives($urls, $name, $width, $height, $options);

        // If something was embedded, return it, otherwise return original.
        return (empty($result) ? $matches[0] : $result);

    }


    /**
     * Lifted from lib/medialib.php. Need to omit the call to clean_param
     * until 'rtmp' is added as a valid scheme in the Moodle core libs.
     *
     * @param string $combinedurl String of 1 or more alternatives separated by #
     * @param int    $width        Output variable: width (will be set to 0 if not specified)
     * @param int    $height       Output variable: height (0 if not specified)
     * @return array               Containing two elements, an array of 1 or more moodle_url objects, and an array of names (optional)
     * @uses $DB, $COURSE, $CFG
     */
    private static function split_alternatives($combinedurl, &$width, &$height)
    {
        global $DB, $COURSE, $CFG;


        $orig_urls    = array_map('trim', explode('#', $combinedurl));
        $width        = 0;
        $height       = 0;
        $clip_urls    = array();
        $clip_names   = array();
        $options      = array();


        // First pass through the array to expand any playlist entries
        // and look for height-width parameters
        $expanded_list = array();

        foreach ($orig_urls as $url) {

            $matches = null;

            if (preg_match('/^rtmp:\/\/playlist=(.+)/', $url, $matches)) {

                // The HTML editor content (where URLs with which we
                // are concerned are placed) is massaged, converting
                // ampersands. We need to put them back to match the
                // playlist name
                $playlist_record = self::get_playlist($COURSE->id, htmlspecialchars_decode($matches[1]));
                if (!$playlist_record) {
                    continue;
                }

                foreach (explode("\n", $playlist_record->list) as $list_item) {
                    $expanded_list[] = trim($list_item);
                }

                // With a playlist, do not want the page littered
                // with links while waiting for the JavaScript to
                // replace them with the media player
                $options[core_media::OPTION_NO_LINK] = true;

            } elseif (preg_match('/^d=([\d]{1,4})x([\d]{1,4})$/i', $url, $matches)) {

                // You can specify the size as a separate part of the array like
                // #d=640x480 without actually including as part of a url.
                $width  = $matches[1];
                $height = $matches[2];
                continue;

            } else {

                // Append as is
                $expanded_list[] = $url;

            }

        } // foreach - first pass


        // Second pass, massage the URLs and parse any height or width
        // or clip name or close caption directive
        foreach($expanded_list as $list_item) {

            // First parse using comma delimter to separate playlist
            // names (if present) from the URL
            @list($list_item_url, $list_item_name) = array_map('trim', explode(',', $list_item, 2));

            // Clean up url. But first substitute the rtmp scheme with
            // http to allow validation against everything else, then
            // put the rtmp back.
            $list_item_url = preg_replace('/^rtmp:\/\//i', 'http://', $list_item_url, 1);
            $list_item_url = clean_param($list_item_url, PARAM_URL);
            if (empty($list_item_url)) {
                continue;
            }
            $list_item_url = preg_replace('/^http:\/\//', 'rtmp://', $list_item_url, 1);

            // Turn it into moodle_url object.
            $clip_urls[]    = new moodle_url($list_item_url);
            $clip_names[]   = empty($list_item_name) ? '' : htmlspecialchars_decode($list_item_name);

        } // foreach - second pass

        $options['PLAYLIST_NAMES'] = $clip_names;

        return array($clip_urls, $options);

    }


    /**
     * Determine which flowplayer files are present, and from the names
     * the version is apparent.
     *
     * @access private
     * @static
     *
     * @return array Array of filenames
     * @uses $CFG
     */
    private static function get_flowplayer_filenames()
    {
        global $CFG;



        $flowlibpath    = $CFG->libdir  . "/flowplayer";
        $filterpath     = $CFG->dirroot . "/filter/rtmp";

        $glob_paths = array(
            'js'        => $flowlibpath . "/flowplayer-[0-9].[0-9].?*.min.js",
            'playswf'   => $flowlibpath . "/flowplayer-[0-9].[0-9].?*.swf.php",
            'ctrlswf'   => $flowlibpath . "/flowplayer.controls-[0-9].[0-9].?*.swf.php",
            'secure'  => $filterpath  . "/flowplayer.securestreaming-[0-9].[0-9].?*.swf.php"
        );

        $retval = '';
        foreach ($glob_paths as $key => $path) {
            if (($hit = glob($path))) {
                $relpath = str_replace($CFG->dirroot, '', $hit[0]);
                $retval .= "var filter_rtmp_flowplayer_$key='$relpath';";
            }
        }
        $hls_fallback = $CFG->filter_rtmp_hls_fallback ? 'true' : 'false';
        $retval .= "var filter_rtmp_hls_fallback={$hls_fallback};";

        return $retval;

    }


    /**
     * Fetch a playlist entry
     *
     * @access private
     * @static
     *
     * @param int       $course_id
     * @param string    $name
     * @return mixed                Playlist record (object) or false if not found
     *
     * @uses $DB
     */
    private static function get_playlist($course_id, $name)
    {
       global $DB;
       static $cache = array();

       $key = "{$course_id}:{$name}";
       if (array_key_exists($key, $cache))
           return $cache[$key];

       try {
           $cache[$key] = $DB->get_record('playlist', array('course' => $course_id, 'name' => $name));
           return $cache[$key];
       }
       catch (Exception $exc) {
           // Squelch it, assume playlist table not present
           return false;
       }

    } // get_playlist


} // class filter_rtmp
