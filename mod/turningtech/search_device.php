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
 * Displays the Search form for device associations
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_device_search_form.php');
global $DB, $PAGE, $USER;
$id       = optional_param('id', null, PARAM_INT);
$deviceid = optional_param('deviceid', null, PARAM_ALPHANUM);
$deviceid = strtoupper ($deviceid);
global $DB;
$branch = explode(" ", $CFG->release);
$modlver = rtrim($branch[0], '+');
$modlver = substr($modlver, 0, 3);
$variable = false;
if (!$course = $DB->get_record('course', array(
    'id' => $id
))) {
    print_error('courseidincorrect', 'turningtech');
}
require_login($course);
$PAGE->set_url('/mod/turningtech/search_device.php', array(
    'id' => $id
));
$PAGE->set_course($course);
global $USER;
if ($CFG->version >= '2013111800.00') {
    $context = context_course::instance($course->id);
} else {
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
}
// Default URL for redirection.
$default_url = "{$CFG->wwwroot}/mod/turningtech/index.php";
$title1      = get_string('searchturningtechcourse', 'turningtech');
$title       = get_string('turningtech', 'turningtech');
$PAGE->navbar->add($title1);
$PAGE->requires->css('/mod/turningtech/css/style.css');
// Print the header.
$PAGE->set_heading($title);
$dsform = new turningtech_device_search_form("search_device.php?id={$course->id}");
if ($deviceid) {
    $redirect_url = $CFG->wwwroot . "/mod/turningtech/search_device.php?id={$course->id}";
} else {
    $redirect_url = $CFG->wwwroot;
}
if (!$dsform->is_cancelled()) {
    echo $OUTPUT->header();
}
?>
<div class="ttheader"><div id="myinnercontainer"><?php
echo $title1;
?></div></div>
<?php
if (!$deviceid) {
    ?>
    <div id="divEnterId" style="margin-top:20px; text-align:left;">
    <p><i><?php
    echo get_string('devicesearchhead', 'turningtech');
    ?></i></p>
    </div><div class="divDeviceSearch">
    <?php
} else {
    ?>
    <div id="divEnterId" style="margin-top:20px; margin-left:45%;"><p><i>Device ID <?php
    echo $deviceid;
    ?></i></p></div><div class="divDeviceSearch">
    <?php
}
$dto              = new stdClass();
$dto->all_courses = 1;
$dto->courseid    = 2;
$dsform->set_data($dto);
if ($dsform->is_cancelled()) {
    redirect($redirect_url);
} else if ($data = $dsform->get_data()) {
    $variable = turningtech_list_search_devices($deviceid, $course);
}
    ?>
<table align="center" width="900" cell-padding=0 cell-spacing=0 style="padding:0px;margin-left:25%;" id="tbldevicesearch">
<tr><td style="padding:0px;">
<?php
$dsform->display();
    ?>
</td></tr></table></div>
<?php
if ($variable && $variable->data) {
    ?>
    <table align="center" width="900" cell-padding=0 cell-spacing=0 style="background:#FFF7DE;padding:0px;margin:auto;">
        <tr>
            <td style="colspan:100%;">&nbsp;</td>
        </tr>
    </table>
    <table align="center" width="900" cell-padding=0 cell-spacing=0 style="padding:0px;margin:auto;">
        <tr>
            <td style="padding:0px;">
    <?php
    echo html_writer::start_tag('form', array(
        'action' => "{$CFG->wwwroot}/mod/turningtech/del_searched_device.php?id={$course->id}&deviceid={$deviceid}",
        'method' => 'post',
        'id' => 'selchk'
    ));
    echo html_writer::table($variable);
    echo html_writer::tag('div', '', array(
        'id' => 'divDelete'
    ));
    echo html_writer::empty_tag('input', array(
        'name' => 'Delete',
        'id' => 'btnDelete',
        'type' => 'submit',
        'value' => 'Delete',
        'onclick' => 'return DeleteClick();'
    ));
    echo html_writer::end_tag('form');
    ?></td></tr></table>
    <?php
} else if (isset($_POST['deviceid']) && $_POST['deviceid'] != '' && $dsform->get_data()
 && TurningTechTurningHelper::isdeviceidvalid($_POST['deviceid'])) {
    echo html_writer::tag('span', 'No Record Found for Device ID ' . $_POST['deviceid'], array(
        'class' => 'error'
    ));
}
echo turningtech_show_messages();
    ?>
    <script type="text/javascript">
	//<![CDATA[
        function getInternetExplorerVersion() {
            var rv = -1; // Return value assumes failure.
            if (navigator.appName == 'Microsoft Internet Explorer') {
                var ua = navigator.userAgent;
                var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
                if (re.exec(ua) != null)
                rv = parseFloat( RegExp.$1 );
                }
            return rv;
            }
        function SetAllCheckBoxes(AreaID, CheckValue) {
        
          var objCheckBoxes = document.getElementById(AreaID).getElementsByTagName('input');
          if (!objCheckBoxes)
              return;
          var countCheckBoxes = objCheckBoxes.length;
          if (!countCheckBoxes)
              objCheckBoxes.checked = CheckValue;
          else
              for (var i = 0; i < countCheckBoxes; i++)
                  objCheckBoxes[i].checked = CheckValue;
      }
 
        function CancelClick() {
            document.getElementById("divSearchResult").style.display = "none";
        }
        function checkinput() {
            if (document.getElementById("id_deviceid").value == "") {
                alert("Please provide Device ID");
                return false;
            }
            return true;
        }
        function validateDeviceInput(eventRef) {
		var updated = document.getElementById('id_deviceid').value;
                      var charCode = eventRef.keyCode ? eventRef.keyCode : 
                          ((eventRef.charCode) ? eventRef.charCode : eventRef.which);
                       if(updated=='' && eventRef.keyCode == 13)
                      {alert("Please provide Device ID");
                       return false;
                       }
            if(charCode == 8  || charCode == 27  || charCode == 9 || charCode == 13){

                            return true;

            } else if ( eventRef.keyCode != null && (eventRef.keyCode == 46 || eventRef.keyCode == 39)  
                    && eventRef.charCode != null && eventRef.charCode == 0 ) {

                            return true;

            }

            var character = String.fromCharCode(charCode);
            var alphanum = /^[0-9a-fA-F]+$/;
            if (character.match(alphanum)&& updated.length<'8') {
                return true;
            } else {
                return false;
            }
        }
        
        function DeleteClick() {
            var canDelete = false;
            var isTable = document.getElementById('searchdev_id');
            var nBoxes = document.getElementsByName('selected[]');
            var nBoxesLength = nBoxes.length;
            for (var i = 0; i <= nBoxes.length - 1; i++) {
                if (nBoxes[i].checked == true) {
                    canDelete = true;
                }
            }
            if (canDelete) {
                var r = confirm("Are you sure you want to delete selected records ?");
                var dellist= new Array();;
                if (r == true) {
                    return true;
                }
                else {
                  return false;
                }
            }
            else {
                alert("Please select a record to delete.");
                return false;
            }
        }
      //]]>
    </script>
     <?php
// Verify user has permission to delete this devicemap.
if (!empty($devicemap) && ($USER->id != $devicemap->getField('userid'))) {
    // Current user is not the owner of the devicemap.  So
    // Verify current user is a teacher.
    if ($CFG->version >= '2013111800.00') {
            $context = context_course::instance($course->id);
    } else {
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
    }
    if (!has_capability('mod/turningtech:manage', $context)) {
        print_error('notpermittedtoeditdevicemap', 'turningtech', $default_url);
    }
}
    ?>
<script type='text/javascript' src='js/jQuery.min.js'></script>

<script >
jQuery(document).ready(function(){
     // <span class="error">Device ID Invalid - Device IDs can only be 6 or 8 characters (0-9, A-F)</span>
    
    jQuery('span.error').each(function(){
        jQuery(this).prependTo(jQuery('#mform1').parent());
    });


    jQuery("label[for=id_deviceid]").css('font-weight','normal');
    jQuery('div.error > br').remove();
	jQuery('#id_cancel').removeClass('btn-cancel');
   jQuery('#id_deviceid').val('');
   jQuery('#fgroup_id_buttonar').children('fieldset').css('margin-left','15%');
    //    jQuery(document).keydown(function(event) {
     //   if (event.ctrlKey==true && (event.which == '118' || event.which == '86')) {
     //       event.preventDefault();
      //   }
   // });
   var ver = getInternetExplorerVersion();
            if(ver != -1) {
            	jQuery('#fgroup_id_buttonar').css('margin-left','16%');
            	jQuery('#fgroup_id_buttonar').children('fieldset').css('margin-left','0%');
            }
            <?php if ($modlver >= '2.4') { ?>
					jQuery('#fgroup_id_buttonar').css('margin-left','0%');
            <?php 
}
            ?>
             <?php if ($modlver == '2.7') { ?>
					jQuery('.divDeviceSearch').css('width','80%');
					jQuery('.divDeviceSearch').children('table').css('width','100%').css('margin-left','0%');
					jQuery('#fitem_id_deviceid').css('margin-left','20%');
					jQuery('#id_submitbutton').css('margin-left','28%');
					
            <?php 
}
            ?>
            
             <?php if ($modlver == '2.8') { ?>
					jQuery('.divDeviceSearch').css('width','80%').css('margin-left','10%').css('margin-right','10%');
					jQuery('#tbldevicesearch').css('width','100%').css('margin','auto');
					
            <?php 
}
            ?>
            <?php if ($modlver == '3.0') { ?>
					jQuery('.divDeviceSearch').css('width','80%').css('margin-left','10%').css('margin-right','10%');
					jQuery('#tbldevicesearch').css('width','100%').css('margin','auto');
					
            <?php 
}
            ?>
});
$('#selchk input[name="selected[]"]').click(function() {
	var all_checkboxes = $('#selchk input[name="selected[]"]');
	if (all_checkboxes.not(":checked").length === 0) {
		$('#selchk input[name="checkall"]').attr('checked',true);
		}
	else {
		$('#selchk input[name="checkall"]').attr('checked',false);
		}
});
</script>
<?php
echo $OUTPUT->footer();