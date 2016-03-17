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
    _swf_cfg_play    : M.cfg.wwwroot + filter_rtmp_flowplayer_playswf,
    _swf_cfg_ctrl    : M.cfg.wwwroot + filter_rtmp_flowplayer_ctrlswf,
    _swf_cfg_rtmp    : M.cfg.wwwroot + '/filter/rtmp/flowplayer.rtmp.swf.php',
    _swf_cfg_caption : M.cfg.wwwroot + '/filter/rtmp/flowplayer.captions.swf.php',
    _swf_cfg_content : M.cfg.wwwroot + '/filter/rtmp/flowplayer.content.swf.php',
    _swf_cfg_secure  : M.cfg.wwwroot + '/filter/rtmp/flowplayer.securestreaming-3.2.9.swf.php',
//filter_rtmp_flowplayer_secure,
    _min_video_width : 240,
    _default_volume  : 80,

    playlist_styles    : { playingClass: 'playlist_playing', pausedClass: 'playlist_paused', progressClass: 'playlist_progress' },
    clear_playlist_css : function(node) { if (null == node) return; node.removeClass(M.filter_rtmp.playlist_styles.playingClass).removeClass(M.filter_rtmp.playlist_styles.pausedClass).removeClass(M.filter_rtmp.playlist_styles.progressClass); },


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

        var flashConfig = { src: M.filter_rtmp._swf_cfg_play };
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
                controls: { url: M.filter_rtmp._swf_cfg_ctrl, autoHide: true },
                rtmp: { url: M.filter_rtmp._swf_cfg_rtmp, objectEncoding: 0 },
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
            M.filter_rtmp.init_flowplayer_playlist();
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

        var flashConfig = { src: M.filter_rtmp._swf_cfg_play };
        var flowConfig = {
            plugins: {
                controls: { url: M.filter_rtmp._swf_cfg_ctrl,
                            autoHide: 'never', fullscreen: false, next: false, previous: false, scrubber: true,
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
            M.filter_rtmp.init_flowplayer_playlist();
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
            // Determine if Flash plugin present
            var flashVersion = flashembed.getVersion();
            if (typeof(flashVersion) == 'undefined' || flashVersion[0] == 0) {
                // No Flash, render HTML5 video if allowed
                if (filter_rtmp_hls_fallback) {
                    Y.Node.DOM_EVENTS.playing = Y.Node.DOM_EVENTS.pause = Y.Node.DOM_EVENTS.ended = 1;
                    Y.all('.filter_rtmp_video').each(M.filter_rtmp.init_hls_video);
                    Y.all('.filter_rtmp_audio').each(M.filter_rtmp.init_hls_audio);
                }
            } else {
                // Flash found, apply Flowplayer
                Y.all('.filter_rtmp_video').each(M.filter_rtmp.init_flowplayer_rtmp_video);
                Y.all('.filter_rtmp_audio').each(M.filter_rtmp.init_flowplayer_rtmp_audio);
            }
        });

    }, // init


    /* Alternate playlist plugin for Flowplayer, based upon
     * their standard playlist plugin by Tero Piirainen, but
     * uses YUI rather than jQuery, and is stripped down to
     * essentials for use with rtmp_filter
     */
    init_flowplayer_playlist: function() {
    $f.addPlugin("playlist", function(playlistNodes) {

        if (null == playlistNodes || playlistNodes.size() == 0) return thisPlayer;

        var thisPlayer = this;

        // Set up event handlers for the Flowplayer player
        thisPlayer.onBegin(function(clip) {
            playlistNodes.each(M.filter_rtmp.clear_playlist_css); playlistNodes.item(clip.index).addClass(M.filter_rtmp.playlist_styles.playingClass);
        });
        thisPlayer.onPause(function(clip) {
            playlistNodes.item(clip.index).removeClass(M.filter_rtmp.playlist_styles.playingClass).addClass(M.filter_rtmp.playlist_styles.pausedClass);
        });
        thisPlayer.onResume(function(clip) {
            playlistNodes.item(clip.index).removeClass(M.filter_rtmp.playlist_styles.pausedClass).addClass(M.filter_rtmp.playlist_styles.playingClass);
        });
        thisPlayer.onUnload(function() {
            playlistNodes.each(M.filter_rtmp.clear_playlist_css);
        });

        // Set up click handler on clip links
        playlistNodes.detach('click');
        playlistNodes.on('click', function(e) {
            e.preventDefault();
            if (e.currentTarget.hasClass(M.filter_rtmp.playlist_styles.playingClass) || e.currentTarget.hasClass(M.filter_rtmp.playlist_styles.pausedClass)) {
                thisPlayer.toggle();
            } else {
                e.currentTarget.addClass(M.filter_rtmp.playlist_styles.progressClass);
                thisPlayer.play(playlistNodes.indexOf(e.currentTarget));
            }
        });

        return thisPlayer;

    }); // $f.addPlugin('playlist')
    },  // init_flowplayer_playlist


    init_hls_video: function(playerNode) {

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

        playerNode.setHTML('');

        var playlistNodes = M.filter_rtmp.Y.all('span.filter_rtmp_video_playlist.' + playerId + ' a.clip');

        if (playlistNodes.size() == 0) {
            var hlsUrl = playerNode.getAttribute('data-media-hls-url');
            if (hlsUrl != null && hlsUrl != '') {
                playerNode.append(Y.Node.create('<video controls="true"></video>').set('width', mediaWidth).set('height', mediaHeight).append(Y.Node.create('<source type="video/mp4">').set('src', hlsUrl)));
            }
        } else {
            playerNode.append(Y.Node.create('<video controls="true"></video>').set('width', mediaWidth).set('height', mediaHeight));
            M.filter_rtmp.init_hls_playlist(playerNode.one('video'), playlistNodes);
            M.filter_rtmp.Y.one('.filter_rtmp_video_playlist.' + playerId).setStyle('height', mediaHeight);
        }

    }, // init_hls_video


    init_hls_audio: function(playerNode) {

        // Get the attributes for the playerNode
        var playerId = playerNode.get('id');
        if (typeof(playerId) == 'undefined') { return; }

        playerNode.setHTML('');

        var playlistNodes = M.filter_rtmp.Y.all('span.filter_rtmp_audio_playlist.' + playerId + ' a.clip');

        if (playlistNodes.size() == 0) {
            var hlsUrl = playerNode.getAttribute('data-media-hls-url');
            if (hlsUrl != null && hlsUrl != '') {
                playerNode.append(Y.Node.create('<audio controls="true"></audio>').append(Y.Node.create('<source type="audio/mp3">').set('src', hlsUrl)));
            }
        } else {
            playerNode.append(Y.Node.create('<audio controls="true"></audio>'));
            M.filter_rtmp.init_hls_playlist(playerNode.one('audio'), playlistNodes);
        }


    }, // init_hls_audio


    init_hls_playlist: function(playerNode, playlistNodes) {

        if (null == playlistNodes || playlistNodes.size() == 0) { return };

        var thisPlayer = playerNode.getDOMNode();

        playerNode.on('playing', function(e) {
            var clipIndex = playerNode.getAttribute('data-media-clip-inx');
            playlistNodes.item(clipIndex).removeClass(M.filter_rtmp.playlist_styles.progressClass).removeClass(M.filter_rtmp.playlist_styles.pausedClass).addClass(M.filter_rtmp.playlist_styles.playingClass);
        });
        playerNode.on('pause', function(e) {
            var clipIndex = playerNode.getAttribute('data-media-clip-inx');
            var clipNode  = playlistNodes.item(clipIndex);
            M.filter_rtmp.clear_playlist_css(clipNode);
            clipNode.addClass(M.filter_rtmp.playlist_styles.pausedClass);
        });
        playerNode.on('ended', function(e) {
            playlistNodes.each(M.filter_rtmp.clear_playlist_css);
            playerNode.removeAttribute('data-media-clip-inx');
        });

        // Set up click handler on clip links
        playlistNodes.detach('click');
        playlistNodes.on('click', function(e) {
            e.preventDefault();
            var currClipIndex = playerNode.getAttribute('data-media-clip-inx');
            var nextClipIndex = playlistNodes.indexOf(e.currentTarget);
            if (currClipIndex == null) {
                playlistNodes.each(M.filter_rtmp.clear_playlist_css);
                e.currentTarget.addClass(M.filter_rtmp.playlist_styles.progressClass);
                playerNode.setAttribute('data-media-clip-inx', nextClipIndex);
                playerNode.setAttribute('src', e.currentTarget.getAttribute('data-media-hls-url'));
                thisPlayer.load(); thisPlayer.play();
            } else {
                if (currClipIndex == nextClipIndex) {
                    if (thisPlayer.paused) {
                        thisPlayer.play();
                    } else {
                        thisPlayer.pause();
                    }
                } else {
                    thisPlayer.pause();
                    M.filter_rtmp.clear_playlist_css(playlistNodes.item(currClipIndex));
                    e.currentTarget.addClass(M.filter_rtmp.playlist_styles.progressClass);
                    playerNode.setAttribute('data-media-clip-inx', nextClipIndex);
                    playerNode.setAttribute('src', e.currentTarget.getAttribute('data-media-hls-url'));
                    thisPlayer.load(); thisPlayer.play();
                }
            }
        });

        playerNode.setAttribute('data-media-clip-inx', '0');
        playerNode.setAttribute('src', playlistNodes.item(0).getAttribute('data-media-hls-url'));
        thisPlayer.load();

    }, // init_hls_playlist

}; // M.filter_rtmp
