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
 * This page displays the device lookup
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once('../../course/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_device_form.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_responseware_form.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/EncryptionHelper.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/HttpPostHelper.php');
global $PAGE;
// Set up javascript requirements.
if (file_exists('/lib/yui/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js')) {
    $PAGE->requires->js('/lib/yui/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js');
} else {
    $PAGE->requires->js('/mod/turningtech/js/yahoo-dom-event.js');
}
$PAGE->requires->js('/mod/turningtech/js/turningtech.js');
$PAGE->requires->css('/mod/turningtech/css/style.css');
$id = required_param('id', PARAM_INT); // Get course.
global $DB;
if (!$course = $DB->get_record('course', array(
                'id' => $id
))) {
    print_error('courseidincorrect', 'turningtech');
}
require_login($course);
$PAGE->set_url('/mod/turningtech/index.php', array(
                'id' => $id
));
$PAGE->set_course($course);
add_to_log($course->id, 'turningtech', 'view devices', "index.php?id=$course->id", '');
global $USER, $CFG;
if ($CFG->version >= '2013111800.00') {
    $context = context_course::instance($course->id);
} else {
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
}
$title   = get_string('searchturningtechcourse', 'turningtech');
$title1   = get_string('blocktitle', 'turningtech');
$PAGE->navbar->add($title);
$PAGE->set_heading($course->fullname);
// Print the header.
echo $OUTPUT->header();
echo $OUTPUT->heading($title1);
if (TurningTechMoodleHelper::isuserinstructorincourse($USER, $course)) {
    // Determine if this is a student or instructor.
    if (!TurningTechMoodleHelper::isuserstudentincourse($USER, $course)) {
        // So user is a member of course, but not a student.  Let's make sure they have.
        require_capability('mod/turningtech:manage', $context);
        $action = optional_param('action', 'deviceid', PARAM_ALPHA);
        $status = optional_param('status', false, PARAM_INT);
        // List actions.
        turningtech_list_instructor_lookup($USER, $course, $action);
        if (isset($_SESSION['email_message'])) {
            if ($status==1 && ($_SESSION['email_message']=='true')) {
                echo "<p class='error'>".get_string('emailsuccess', 'turningtech')."</p>";
                unset($_SESSION['email_message']);
            }
            if ($status==0 && ($_SESSION['email_message']=='false')) {
                echo "<p class='error'>".get_string('emailfail', 'turningtech')."</p>";
                unset($_SESSION['email_message']);
            }
        }
        switch ($action) {
            case 'deviceid':
                turningtech_list_course_devices_instructor($course);
                break;
            case 'sessionfile':
                turningtech_import_session_file($course);
                break;
        }
    }
}
?>
<script type="text/javascript">
//<![CDATA[
 function EditResponseCard(caller) {
            var str = caller.id;
            var n = str.split("_");
            var divToHideId = "divEditRC_" + n[1];
            var divToShowId = "divUpdateCancelRC_" + n[1];
            var textDivToShowId = "divTextResponseCard_" + n[1];
            var lblDivToHideId = "divResponseCard_" + n[1];
            var divToHide = document.getElementById(divToHideId);
            var divToShow = document.getElementById(divToShowId);
            var lblToHide = document.getElementById(lblDivToHideId);
            var textdivToShow = document.getElementById(textDivToShowId);
            var deviceid = document.getElementById(lblDivToHideId).innerHTML;
            document.getElementById('txtResponseCard_'+n[1]).value = deviceid;
            divToHide.style.display = 'none';
            lblToHide.style.display = 'none';
            divToShow.style.display = 'inline';
            textdivToShow.style.display = 'inline';
        }
 function EditResponseCardr(caller) {
     var str = caller.id;
     var n = str.split("_");
     var divToHideId = "divEditRC_" + n[1];
     var divToShowId = "divUpdateCancelRC_" + n[1];
     var textDivToShowId = "divTextResponseCard_" + n[1];
     var lblDivToHideId = "divResponseCard_" + n[1];
     var divToHide = document.getElementById(divToHideId);
     var divToShow = document.getElementById(divToShowId);
     var lblToHide = document.getElementById(lblDivToHideId);
     var textdivToShow = document.getElementById(textDivToShowId);
     document.getElementById('txtResponseCard_'+n[1]).value = '';
     divToHide.style.display = 'none';
     lblToHide.style.display = 'none';
     divToShow.style.display = 'inline';
     textdivToShow.style.display = 'inline';
 }
        function EditResponseWare(caller) {
            var str = caller.id;
            var n = str.split("_");
            var divToHideId = "divEditRW_" + n[1];
            var divToShowId = "divUpdateCancelRW_" + n[1];
            var textDivToShowId = "divTextResponseWare_" + n[1];
            var lblDivToHideId = "divResponseWare_" + n[1];
            var divToHide = document.getElementById(divToHideId);
            var divToShow = document.getElementById(divToShowId);
            var lblToHide = document.getElementById(lblDivToHideId);
            var textdivToShow = document.getElementById(textDivToShowId);
            divToHide.style.display = 'none';
            lblToHide.style.display = 'none';
            divToShow.style.display = 'inline';
            textdivToShow.style.display = 'inline';
        }
        function DeleteResponseCard(caller) {
            var canDelete = true;
            var str = caller.id;
            var n = str.split("_");
            if (canDelete) {
                var r = confirm("Are you sure you want to delete this Device ID?");
                if (r) {
                	 window.location = document.getElementById('lnkDeleteRC_1').href;                   
                }
            } else {
            }
        }
        function DeleteResponseWare(caller) {
            var canDelete = true;
            var str = caller.id;
            var n = str.split("_");
            if (canDelete) {
                var r = confirm("Are you sure you want to delete this Device ID?");
                if (r) {
               	 window.location = document.getElementById('lnkDeleteRW_1').href;                   
               }
            } else {
            }
        }
                 function validateRC(){
           var registerval = document.getElementById("id_deviceid");
           var alphanum = /^[0-9a-fA-F]+$/;
           if(registerval.value==""){
				alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
				return false;
               }
           else if(registerval.value.length!= '6'){
				alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
               return false;
           }
           else if(registerval.value.match(alphanum)){
        	   return true;
				}
           else {
        	   alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
               return false;
           }
        }
        function UpdateResponseCard(caller) {
        	var str = caller.id;
            var n = str.split("_");
        	var updated = document.getElementById('txtResponseCard_'+n[1]).value;
            var alphanum = /^[0-9a-fA-F]+$/;
            if(updated==""){
 				alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
 				return false;
                }
            else if(updated.length!= '6'){
 				alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
                return false;
            }
            else if(updated.match(alphanum)){
            	var updatedurl = document.getElementById('lnkUpdateRC_'+n[1]).href;
            	var updurl = updatedurl.split("&deviceid");
    	        document.getElementById('lnkUpdateRC_'+n[1]).href = updurl[0]+'&deviceid='+updated;
         	   return true;
 				}
            else {
         	   alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
                return false;
            }
        }
        function UpdateRCinput(caller) {
        	var str = caller.id;
            var n = str.split("_");
        	var updated = document.getElementById('txtResponseCard_'+n[1]).value;
            var alphanum = /^[0-9a-fA-F]+$/;
            if(updated==""){
 				alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
 				return false;
                }
            else if(updated.length!= '6'){
 				alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
                return false;
            }
            else if(updated.match(alphanum)){
            	var updatedurl = document.getElementById('lnkUpdateRC_'+n[1]).getAttribute("name");
    	        window.location =updatedurl+'&deviceid='+updated;
         	   return true;
 				}
            else {
         	   alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
                return false;
            }
        }
                function validateDeviceInput(eventRef) {
		var updated = document.getElementById('id_deviceid').value;
                      var charCode;
                      charCode = eventRef.keyCode ? eventRef.keyCode : ((eventRef.charCode) ? eventRef.charCode : eventRef.which);
                       if(updated=='' && eventRef.keyCode == 13)
                      {
                       return false;
                       }
            if(charCode == 8  || charCode == 27  || charCode == 9 || charCode == 13){
                            return true;
            } else if ( eventRef.keyCode != null && (eventRef.keyCode == 46 || eventRef.keyCode == 39 || eventRef.keyCode == 37)  
                    && eventRef.charCode != null && eventRef.charCode == 0 ) {
                            return true;
            }
            var character = String.fromCharCode(charCode);
            var alphanum = /^[0-9a-fA-F]+$/;
            if (character.match(alphanum)&& updated.length<'6') {
                return true;
            } else {
                return false;
            }
        }
                        function validateDeviceInputupdate(eventRef, caller) {
                        	var str = caller.id;
                            var n = str.split("_");
		var updated = document.getElementById('txtResponseCard_'+n[1]).value;
                      var charCode;
                      charCode = eventRef.keyCode ? eventRef.keyCode : ((eventRef.charCode) ? eventRef.charCode : eventRef.which);
                      if(charCode == 13){
                    	  document.getElementById('lnkUpdateRC_'+n[1]).click();
                      }
                       if(charCode == 8  || charCode == 27  || charCode == 9 || charCode == 13){
                            return true;
            } else if ( eventRef.keyCode != null && (eventRef.keyCode == 46 || eventRef.keyCode == 39 || eventRef.keyCode == 37)  
                    && eventRef.charCode != null && eventRef.charCode == 0 ) {
                            return true;
            }
            var character = String.fromCharCode(charCode);
            var alphanum = /^[0-9a-fA-F]+$/;
            if (character.match(alphanum)&& updated.length<'6') {
                return true;
            } else {
                return false;
            }
        }
        function UpdateResponseWare(caller) {
        	var updated = document.getElementById('txtResponseWare_1').value;
        	var alphanum = /^[0-9a-fA-F]+$/;
            if(updated==""){
 				alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
 				return false;
                }
            else if(updated.length!= '6'){
 				alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
                return false;
            }
            else if(updated.match(alphanum)){
	        var updatedurl = document.getElementById('lnkUpdateRW_1').href;
	        document.getElementById('lnkUpdateRW_1').href = updatedurl+'&deviceid='+updated;
            alert("Update ResponseWare Device ID");
         	   return true;
 				}
            else {
         	   alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
                return false;
            }
        }
        function CancelResponseCard(caller) {
            var str = caller.id;
            var n = str.split("_");
            var divToShowId = "divEditRC_" + n[1];
            var divToHideId = "divUpdateCancelRC_" + n[1];
            var divToHide = document.getElementById(divToHideId);
            var divToShow = document.getElementById(divToShowId);
            var textDivToHideId = "divTextResponseCard_" + n[1];
            var lblDivToShowId = "divResponseCard_" + n[1];
            var lblToShow = document.getElementById(lblDivToShowId);
            var textdivToHide = document.getElementById(textDivToHideId);
            divToHide.style.display = 'none';
            divToShow.style.display = 'inline';
            textdivToHide.style.display = 'none';
            lblToShow.style.display = 'inline';
        }
        function CancelResponseWare(caller) {
            var str = caller.id;
            var n = str.split("_");
            var divToShowId = "divEditRW_" + n[1];
            var divToHideId = "divUpdateCancelRW_" + n[1];
            var divToHide = document.getElementById(divToHideId);
            var divToShow = document.getElementById(divToShowId);
            var textDivToHideId = "divTextResponseWare_" + n[1];
            var lblDivToShowId = "divResponseWare_" + n[1];
            var lblToShow = document.getElementById(lblDivToShowId);
            var textdivToHide = document.getElementById(textDivToHideId);
            divToHide.style.display = 'none';
            divToShow.style.display = 'inline';
            textdivToHide.style.display = 'none';
            lblToShow.style.display = 'inline';
        }
        function RegisterRC() {
            var registerText = document.getElementById("txtRegisterDeviceId");
            if (registerText.value == "") {
                document.getElementById("spnMessage").style.display = "inline";
                document.getElementById("spnMessage").innerHTML = "Please provide a Device ID to Register.";
            }
            else {
                document.getElementById("spnMessage").style.display = "inline";
                var getspnhtml = document.getElementById("spnMessage");
                getspnhtml.innerHTML = "Your Device ID has been successfully registered for all courses.";
            }
        }
        function RegisterRW() {
            var eMail = document.getElementById("txtResponseWareEmail");
            var password = document.getElementById("txtResponseWarePassword");
            if (eMail.value == "" && password.value == "") {
                document.getElementById("spnEmailMessage").style.display = "inline";
                document.getElementById("spnEmailMessage").innerHTML = "You must provide a ResponseWare Email Address.";
                document.getElementById("spnPasswordMessage").style.display = "inline";
                document.getElementById("spnPasswordMessage").innerHTML = "Please provide a Password.";
                document.getElementById("spnResponseWareMessage").style.display = "none";
            }
            else if (password.value == "" && eMail.value != "") {
                document.getElementById("spnPasswordMessage").style.display = "inline";
                document.getElementById("spnPasswordMessage").innerHTML = "Please provide a Password.";
                document.getElementById("spnEmailMessage").style.display = "none";
                document.getElementById("spnEmailMessage").innerHTML = "";
                document.getElementById("spnResponseWareMessage").style.display = "none";
            }
            else if (eMail.value == "" && password.value != "") {
                document.getElementById("spnEmailMessage").style.display = "inline";
                document.getElementById("spnEmailMessage").innerHTML = "You must provide a ResponseWare Email Address.";
                document.getElementById("spnPasswordMessage").style.display = "none";
                document.getElementById("spnPasswordMessage").innerHTML = "";
                document.getElementById("spnResponseWareMessage").style.display = "none";
            }
            else {
                document.getElementById("spnPasswordMessage").style.display = "none";
                document.getElementById("spnPasswordMessage").innerHTML = "";
                document.getElementById("spnEmailMessage").style.display = "none";
                document.getElementById("spnEmailMessage").innerHTML = "";
                document.getElementById("spnResponseWareMessage").style.display = "inline";
                var getspnwrmsg = document.getElementById("spnResponseWareMessage");
                getspnwrmsg.innerHTML = "Your Device ID has been successfully registered for all courses.";
            }
        }
        function ToggleRespCard(){
            var divToToggle=document.getElementById('responsecard-collapse-group');
        	 var list = divToToggle.getElementsByClassName('collapsed');
        	    if(list.length > 0) {
            	   	   for(var i=0;i< list.length ;i++){
                	   list[i].className='uncollapsed';
                }
        	    }
        	    else {
        	    	 var list = divToToggle.getElementsByClassName('uncollapsed');
        	    	 for(var i=0;i<list.length ;i++){
                  	   list[i].className='collapsed';
                  }
        	    }
        }
        function ToggleRespWare(){
            var divToToggle=document.getElementById('responseware-collapse-group');
       	 var list = divToToggle.getElementsByClassName('collapsed');
       	    if(list.length > 0) {
           	   	   for(var i=0;i<list.length ;i++){
               	   list[i].className='uncollapsed';
               }
       	    }
       	    else {
       	    	 var list = divToToggle.getElementsByClassName('uncollapsed');
       	    	 for(var i=0;i<list.length ;i++){
                 	   list[i].className='collapsed';
                 }
       	    }
        }
        function unhide(divID) {
            var item = document.getElementById(divID);
            if (item) {
                item.className = (item.className == 'hiddens') ? 'unhidden' : 'hiddens';
            }
        }
      //]]>
</script>
<script
	type='text/javascript' src='js/jQuery.min.js'></script>
<script>
jQuery(document).ready(function(){
	$("td.c3").css("width","318px").css('padding','4px');
	$('.fp-repo-area').css("display","block");
	$("ul").css("list-style","none");
});
</script>
<?php
echo $OUTPUT->footer();