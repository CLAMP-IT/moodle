// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * The JavaScript used in the NanoGong activity module
 *
 * @author     Ning
 * @author     Gibson
 * @package    mod
 * @subpackage nanogong
 * @copyright  2012 The Gong Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2.2
 */

M.mod_nanogong = {};

M.mod_nanogong.init = function(Y, url) {
    M.mod_nanogong.Y = Y;
    M.mod_nanogong.url = url;

    // Append the applet at the end of the page
    if (Y.one('.mod_nanogong_img') || Y.one('.nanogong_message_list')) {
        var applet = '';
        applet += '<div id="mod_nanogong_div" ';
        applet +=      'style="position:absolute;top:-40px;left:-130px;z-index:100;visibility:hidden">';
        applet +=     '<applet id="mod_nanogong_applet" archive="' + M.cfg.wwwroot + '/mod/nanogong/nanogong.jar" code="gong.NanoGong" width="130" height="40">';
        applet +=         '<param name="ShowTime" value="true" />';
        applet +=         '<param name="ShowAudioLevel" value="false" />';
        applet +=         '<param name="ShowRecordButton" value="false" />';
        applet +=     '</applet>';
        applet +=     '<p id="mod_nanogong_timecreated"></p>';
        applet += '</div>';

        var body = Y.one(document.body);
        body.append(applet);
    }

    // Initialize the show/hide icon
    Y.use('cookie', function(Y) {
        Y.all('.nanogong_showhide_icon').each(function(a) {
            var id = a.get('id').replace('_icon', '');
            var div = Y.one('#' + id);

            if (Y.Cookie.get(id) == 1)
                div.hide();

            M.mod_nanogong.showhidediv(a);
        });
    });
    
    // Set up the show/hide icon
    Y.on('mousedown', function(e) {
        M.mod_nanogong.showhidediv(e.target.ancestor('a'));
    }, '.nanogong_showhide_icon');

    // Set up the NanoGong speaker image
    Y.on('click', function(e) {
        var img = e.target;
        var path = img.getAttribute('path');
        var region = img.get('region');
        M.mod_nanogong.shownanogongapplet(path, [region.right, region.top]);
    }, '.mod_nanogong_img');

    // Set up the NanoGong message list
    Y.on('change', function(e) {
        var list = e.target;
        var selectedIndex = list.get('selectedIndex');
        if (selectedIndex >= 0) {
            var option = list.get('options').item(selectedIndex);
            var path = option.get('value');
            var timecreated = option.getAttribute('timecreated');
            var region = list.get('region');

            M.mod_nanogong.shownanogongapplet(path, [region.right + 20, (region.bottom + region.top) / 2 - 20], timecreated);
        }
    }, '.nanogong_message_list');
    Y.on('click', function(e) {
        var option = e.target;
        var path = option.get('value');
        if (M.mod_nanogong.currentpath != path) {
            var timecreated = option.getAttribute('timecreated');
            var region = option.ancestor('select').get('region');

            M.mod_nanogong.shownanogongapplet(path, [region.right + 20, (region.bottom + region.top) / 2 - 20], timecreated);
        }
    }, '.nanogong_message_list');
    
    // Set up the save audio button
    Y.on('click', function(e) {
        M.mod_nanogong.saveaudiofromstudent();
    }, '#nanogong_saveaudio_button');
    
    // Set up the delete message button
    Y.on('click', function(e) {
        M.mod_nanogong.confirmdelete();
    }, '#nanogong_confirmdelete_button');
    
    // Set up the load audio button
    Y.on('click', function(e) {
        var button = e.target;
        var applet = Y.one('#nanogonggradeinstance')._node;
        applet.sendGongRequest('LoadFromURL', button.getAttribute('path'));
    }, '#id_nanogong_loadaudio_button');
    
    // Set up the save audio button on grade form
    Y.on('click', function(e) {
        var form = e.target.ancestor(function (el) { return Y.Node.getDOMNode(el).tagName.toLowerCase() === "form"; });
        M.mod_nanogong.saveaudiofromteacher(form);
    }, '#id_nanogong_saveaudio_button');

    // Set up the catalog selection
    Y.on('change', function(e) {
        var list = e.target;
        var selectedIndex = list.get('selectedIndex');
        if (selectedIndex >= 0) {
            var url = list.getAttribute('action');
            var option = list.get('options').item(selectedIndex);

            Y.config.win.location = url + '&catalog=' + option.get('value');
        }
    }, '#nanogong_catalog');

    // Set up the page setting
    Y.on('change', function(e) {
        var list = e.target;
        var selectedIndex = list.get('selectedIndex');
        if (selectedIndex >= 0) {
            var url = list.getAttribute('action');
            var option = list.get('options').item(selectedIndex);

            Y.config.win.location = url + '&perpage=' + option.get('value');
        }
    }, '#nanogong_page_setting');

    // Set up the delete all box
    Y.on('click', function(e) {
        var checkbox = e.target;
        Y.all('.nanogong_deletebox').each(function(box) {
            box.set('checked', checkbox.get('checked'));
        });
    }, '.nanogong_deleteall');

    // Set up the confirm delete recordings button
    Y.on('click', function(e) {
        var selected = false;
        Y.all('.nanogong_deletebox').each(function(checkbox) {
            if (checkbox.get('checked')) selected = true;
        });
        if (!selected) {
            alert(M.str.nanogong.deletealertmessage);
            return;
        }
        Y.one('#nanogong_delete_form').submit();
    }, '#nanogong_confirmdeleterec_button');

    // Set up the delete recordings button
    Y.on('click', function(e) {
        var selected = false;
        Y.all('.nanogong_deletebox').each(function(checkbox) {
            if (checkbox.get('checked')) selected = true;
        });
        if (!selected) {
            alert(M.str.nanogong.deletealertmessage);
            return;
        }
        var form = Y.one('#nanogong_delete_form');
        form.one('*[name="action"]').set('value', 'deleterecordings');
        form.submit();
    }, '#nanogong_deleterecording_button');

    // Set up the edit grade button
    Y.on('click', function(e) {
        Y.config.win.location = e.target.getAttribute('action');
    }, '.nanogong_editgrade_button');
} 

// Toggle the div using the icon
M.mod_nanogong.showhidediv = function(a) {
    Y = M.mod_nanogong.Y;

    var id = a.get('id').replace('_icon', '');
    var img = a.one('img');
    var div = Y.one('#' + id);

    if (div.getStyle('display') == 'none') {
        div.show();

        img.set('src', 'pix/switch_minus.gif');
        img.set('title', M.str.nanogong.hide);

        document.cookie = id + '=1';
    }
    else {
        div.hide();

        img.set('src', 'pix/switch_plus.gif');
        img.set('title', M.str.nanogong.show);

        document.cookie = id + '=0';

        if (Y.one('#mod_nanogong_div')) {
            Y.one('#mod_nanogong_div').setStyle('visibility', 'hidden');
            M.mod_nanogong.currentpath = '';
        }
    }
}

// Show the NanoGong applet
M.mod_nanogong.shownanogongapplet = function(path, position, timecreated) {
    Y = M.mod_nanogong.Y;

    var div = Y.one('#mod_nanogong_div');

    // Switch off the NanoGong player when the speaker icon is clicked again
    if (M.mod_nanogong.currentpath == path) {
        div.setStyle('visibility', 'hidden');
        M.mod_nanogong.currentpath = '';
        return;
    }

    // Switch on the NanoGong applet when the speaker icon is clicked
    div.setStyle('visibility', 'visible');
    M.mod_nanogong.currentpath = path;

    // Set the time created if needed
    var time = Y.one('#mod_nanogong_timecreated');
    if (timecreated) {
        time.setContent(M.str.nanogong.submitted + ' ' + timecreated);
        time.show();
    }
    else {
        time.hide();
    }

    // Put the NanoGong applet to the proper position
    div.setXY(position);

    // Load the sound from the page in the applet
    var applet = Y.one('#mod_nanogong_applet')._node;
    applet.sendGongRequest('LoadFromURL', path);
}

// Confirm deleting a message
M.mod_nanogong.confirmdelete = function() {
    var list = Y.one('#nanogong_student_list');
    var selectedIndex = list.get('selectedIndex');
    if (selectedIndex < 0) {
        alert(M.str.nanogong.deletealertmessage);
        return;
    }

    var option = list.get('options').item(selectedIndex);
    var filename = option.getAttribute('filename');

    Y.config.win.location = M.mod_nanogong.url + '&filename=' + filename + '&action=confirmdelete';
}

// Save the audio message on server
M.mod_nanogong.saveaudiofromstudent = function() {
    Y = M.mod_nanogong.Y;

    // Check the title
    var title = Y.one('#nanogongtitle').get('value');
    title = Y.Lang.trim(title);
    //title = title.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
    if (title == '') {
        alert(M.str.nanogong.voicetitle);
        return;
    }

    // Save the recording on the server
    var applet = Y.one('#nanogonginstance')._node;
    if (applet) {
        applet.sendGongRequest('StopMedia', 'audio');
        if (applet.sendGongRequest('GetMediaDuration', 'audio') <= 0) {
            alert(M.str.nanogong.emptymessage);
            return;
        }

        var url = 'saveaudio.php?id=' + Y.one('#cmid').get('value');
        var ret = applet.sendGongRequest('PostToForm', url, 'nanogong_upload_file', '', 'nanogongaudio');
    
        if (M.mod_nanogong.showsaveaudioerror(ret)) return;

        Y.one('#action').set('value', 'savemessage');
        Y.one('#submiturl').set('value', ret);

        document.cookie = 'submissionarea=1';

        Y.one('#nanogong_submit_form').submit();
        return;
    }

    alert(M.str.nanogong.emptymessage);
}

// Save the audio message on server for grade form
M.mod_nanogong.saveaudiofromteacher = function(form) {
    Y = M.mod_nanogong.Y;

    // Save any new recording on the server
    var applet = Y.one('#nanogonggradeinstance')._node;
    if (applet) {
        applet.sendGongRequest('StopMedia', 'audio');
        if (applet.sendGongRequest('GetMediaDuration', 'audio') > 0) {
            var url = 'saveaudio.php?id=' + form.one('*[name="id"]').get('value');
            var ret = applet.sendGongRequest('PostToForm', url, 'nanogong_upload_file', '', 'nanogongaudio');
    
            if (M.mod_nanogong.showsaveaudioerror(ret)) return;

            form.one('*[name="url"]').set('value', ret);
        }
    }

    form.submit();
}

// Print the error from saving the audio file, if any
M.mod_nanogong.showsaveaudioerror = function(ret) {
    if (ret == null || ret == "") {
        alert(M.str.nanogong.servererror);
        return true;
    }
    else if (!ret.match(/^\d{14}\.\w{3}$/)) {
        if (ret.match(/^\[.*\]$/)) {
            str = ret.substr(1, ret.length - 2);
            if (M.str.nanogong[str])
                alert(M.str.nanogong[str]);
            else
                alert(str);
            return true;
        }
        else {
            alert(M.str.nanogong.servererror);
            return true;
        }
    }

    return false;
}

