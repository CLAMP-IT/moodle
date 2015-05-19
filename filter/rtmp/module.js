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

M.filter_rtmp = {

    Y: null, transaction: [],

    _js_flowplayer   : M.cfg.wwwroot + filter_rtmp_flowplayer_js,
    _swf_cfg_base    : M.cfg.wwwroot + filter_rtmp_flowplayer_swf,
    _swf_cfg_rtmp    : M.cfg.wwwroot + filter_rtmp_flowplayer_rtmp,
    _swf_cfg_caption : M.cfg.wwwroot + filter_rtmp_flowplayer_caption,
    _swf_cfg_content : M.cfg.wwwroot + filter_rtmp_flowplayer_content,
    _swf_cfg_secure  : M.cfg.wwwroot + filter_rtmp_flowplayer_secure,
    _min_video_width : 240,
    _default_volume  : 80,


    init_flowplayer_rtmp_video: function(playerNode) {

        // Get the attributes for the playerNode
        var playerId = playerNode.get('id');
        if (typeof(playerId) == 'undefined') { return; }

        var mediaHeight = playerNode.getAttribute('data-media-height');
        if (typeof(mediaHeight) == 'undefined') { mediaHeight = 0; }
        var mediaWidth  = playerNode.getAttribute('data-media-width');
        if (typeof(mediaWidth) == 'undefined') { mediaWidth = 0; }
        var mediaAutosize = playerNode.getAttribute('data-media-autosize');
        if (typeof(mediaAutosize) == 'undefined') { mediaAutosize = true; }
        else { mediaAutosize = (isNaN(mediaAutosize) ? false : parseInt(mediaAutosize) == 1); }
        var useCaptions = playerNode.getAttribute('data-media-captions');
        if (typeof(useCaptions) == 'undefined') { useCaptions = false; }
        else { useCaptions = (isNaN(useCaptions) ? false : parseInt(useCaptions) == 1); }
        var mediaSecureStreaming = playerNode.getAttribute('data-media-securestreaming');
        if (typeof(mediaSecureStreaming) == 'undefined') { mediaSecureStreaming = false; }
        else { mediaSecureStreaming = (isNaN(mediaSecureStreaming) ? false: parseInt(mediaSecureStreaming) == 1); }

        var flashConfig = { src: M.filter_rtmp._swf_cfg_base };
        // If dimensions specified, pass along in Flash configs
        if (mediaHeight > 0 && mediaWidth > 0) {
            flashConfig.width = mediaWidth; flashConfig.height = mediaHeight;
        }

        var baseClip = {
            provider: 'rtmp', myMeta: { autosize: mediaAutosize, resized: false },
            autoPlay: false, autoBuffering: true, scaling: 'fit',
            onMetaData: function(clip) {
                // Get out if no autosizing or already resized
                if (!clip.myMeta.autosize || clip.myMeta.resized) {
                    return;
                }
                clip.myMeta.resized = true;
                // Default dimensions (from Flash plugin?)
                var width = clip.width, height = clip.height;
                // Prefer dimensions in the clip metadata
                if (typeof(clip.metaData.width) != 'undefined' && typeof(clip.metaData.height) != 'undefined') {
                    width = clip.width; height = clip.height;
                }
                // If too small, adjust but keep same aspect ratio
                if (width < M.filter_rtmp._min_video_width) {
                    height = (height/width) * M.filter_rtmp._min_video_width;
                    width  = M.filter_rtmp._min_video_width;
                }
                this._api().width = width; this._api().height = height;
            }
        };

        var flowConfig = {
            plugins: {
                controls: { autoHide: true }, rtmp: { url: M.filter_rtmp._swf_cfg_rtmp, objectEncoding: 0 },
            },
            clip: baseClip,
            onLoad: function() {
                this.setVolume(M.filter_rtmp._default_volume); this.unmute();
            }
        };
        var captionsConfig = { url: M.filter_rtmp._swf_cfg_caption, captionTarget: 'content' };
        var contentConfig  = {
            url: M.filter_rtmp._swf_cfg_content,
            top: 0, width: '85%', height: 40,
            backgroundColor: 'transparent', backgroundGradient: 'none',
            border: 0, textDecoration: 'outline',
            style: { 'body': { fontSize: '14', fontFamily: 'verdana,arial,helvetica,sans-serif', textAlign: 'center', color: '#ffffff' } }
        };

        if (mediaSecureStreaming) {
            flowConfig.plugins.secure = { url: M.filter_rtmp._swf_cfg_secure };
            flowConfig.clip.connectionProvider = 'secure';
        }

        var playlistNodes = M.filter_rtmp.Y.all('span.filter_rtmp_video_playlist.' + playerId + ' a.clip');
        if (playlistNodes.size() == 0) {
            baseClip.netConnectionUrl = playerNode.getAttribute('data-media-conx');
            baseClip.url = playerNode.getAttribute('data-media-path');
            if (useCaptions) {
                flowConfig.plugins.captions = captionsConfig;
                flowConfig.plugins.content  = contentConfig;
            }
            flowplayer(playerId, flashConfig, flowConfig);
        } else {
            M.filter_rtmp.attachPlaylistPlugin();
            M.filter_rtmp.Y.one('.filter_rtmp_video_playlist.' + playerId).setStyle('height', mediaHeight);
            var playlist = []; var needCC = false;
            playlistNodes.each(function(node, nodeIndex) {
                playlist[nodeIndex] = { index: nodeIndex, url: node.getAttribute('data-media-path'), netConnectionUrl: node.getAttribute('data-media-conx'), showCC: node.getAttribute('data-media-captions') == '1' };
                needCC = playlist[nodeIndex].showCC;
            });
            flowConfig.playlist = playlist;
            if (needCC) {
                flowConfig.plugins.captions = captionsConfig;
                flowConfig.plugins.content  = contentConfig;
                flowConfig.clip.onBegin = function(clip) {
                    var cap = this.getPlugin('captions');
                    var cnt = this.getPlugin('content');
                    if (this.getPlaylist()[clip.index].showCC) { cap.showButton(); cnt.show(); }
                    else { cap.hideButton(); cnt.hide(); }
                    return true;
                };
            }
            flowplayer(playerId, flashConfig, flowConfig).playlist(playlistNodes);
        }

    }, // init_flowplayer_rtmp_video


    init_flowplayer_rtmp_audio: function(playerNode) {

        // Get the attributes for the playerNode
        var playerId = playerNode.get('id');
        if (typeof(playerId) == 'undefined') { return; }

        var flashConfig = { src: M.filter_rtmp._swf_cfg_base };
        var flowConfig = {
            plugins: {
                controls: { autoHide: 'never', fullscreen: false, next: false, previous: false, scrubber: true,
                            play: true, pause: true, volume: true, mute: true, backgroundGradient: [0.5,0,0.3],
                            controlall: true, height: '100%', time: true },
                rtmp: { url: M.filter_rtmp._swf_cfg_rtmp, durationFunc: 'getStreamLength' }
            },
            clip: { provider: 'rtmp', autoPlay: false },
            play: null,
            onLoad: function() {
                this.setVolume(M.filter_rtmp._default_volume); this.unmute();
            }
        };
        
        var playlistNodes = M.filter_rtmp.Y.all('span.filter_rtmp_audio_playlist.' + playerId + ' a.clip');
        if (playlistNodes.size() == 0) {
            flowConfig.clip.netConnectionUrl = playerNode.getAttribute('data-media-conx');
            flowConfig.clip.url = playerNode.getAttribute('data-media-path');
            flowplayer(playerId, flashConfig, flowConfig);
        } else {
            M.filter_rtmp.attachPlaylistPlugin();
            var playlist = [];
            playlistNodes.each(function(node, nodeIndex) {
                playlist[nodeIndex] = { index: nodeIndex, url: node.getAttribute('data-media-path'), netConnectionUrl: node.getAttribute('data-media-conx') };
            });
            flowConfig.playlist = playlist;
            flowplayer(playerId, flashConfig, flowConfig).playlist(playlistNodes);
        }
        
    }, // init_flowplayer_rtmp_audio


    init: function(Y) {

        this.Y = Y;

        // Check to see if flowplayer.js loaded, if not ask YUI
        // to go fetch it
        var useModules = ['node'];
        if (typeof (flowplayer) == 'undefined') {
            var loader = new Y.Loader({ modules: { yflowplayer: { fullpath: this._js_flowplayer } }, require: ['yflowplayer'] });
            useModules.push('yflowplayer');
        }

        // Need a new sandbox instance to which to attach the
        // flowplayer module (in the case where the flowplayer
        // script had not yet been loaded by javascript-static
        YUI().use(useModules, function() {
            // Find nodes to which flowplayer should be applied
            Y.all('.filter_rtmp_video').each(M.filter_rtmp.init_flowplayer_rtmp_video);
            Y.all('.filter_rtmp_audio').each(M.filter_rtmp.init_flowplayer_rtmp_audio);
        });

    }, // init

    /* Alternate playlist plugin for Flowplayer, based upon
     * their standard playlist plugin by Tero Piirainen, but
     * uses YUI rather than jQuery, and is stripped down to
     * essentials for use with rtmp_filter
     */
    attachPlaylistPlugin: function() {
    $f.addPlugin("playlist", function(playlistNodes) {

        if (null == playlistNodes || playlistNodes.size() == 0) return thisPlayer;

        var opts = { playingClass: 'playing', pausedClass: 'paused', progressClass:'progress', stoppedClass:'stopped' };
        var thisPlayer = this;

        function buildPlaylist()
        {
            playlistNodes.detach('click');
            playlistNodes.on('click', function(e) {
                e.preventDefault();
                play(e.currentTarget);
            });
        }

        function play(clickedNode)
        {
            if (clickedNode.hasClass(opts.playingClass) || clickedNode.hasClass(opts.pausedClass)) {
                thisPlayer.toggle();
            } else {
                clickedNode.addClass(opts.progressClass);
                thisPlayer.play(playlistNodes.indexOf(clickedNode));
            }
        }

        function clearCSS(node)
        {
            if (null == node) return;
            node.removeClass(opts.playingClass).removeClass(opts.pausedClass).removeClass(opts.progressClass).removeClass(opts.stoppedClass);
        }

        thisPlayer.onBegin(function(clip) {
            playlistNodes.each(clearCSS); playlistNodes.item(clip.index).addClass(opts.playingClass);
        });
        thisPlayer.onPause(function(clip) {
            playlistNodes.item(clip.index).removeClass(opts.playingClass).addClass(opts.pausedClass);
        });
        thisPlayer.onResume(function(clip) {
            playlistNodes.item(clip.index).removeClass(opts.pausedClass).addClass(opts.playingClass);
        });
        thisPlayer.onUnload(function() {
            playlistNodes.each(clearCSS);
        });

        buildPlaylist();
        return thisPlayer;

    }); // $f.addPlugin('playlist')
    },  // attachPlaylistPlugin

}; // M.filter_rtmp
