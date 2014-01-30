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
 * This page lists all the instances of turningtech in a particular course
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
$branch = explode(" ", $CFG->release);
$modlver = rtrim($branch[0], '+');
$modlver = substr($modlver, 0, 3);
$pluginconfig = get_config('moodle', 'turningtech_device_selection');
// Set up javascript requirements.
if (file_exists('/lib/yui/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js')) {
    $PAGE->requires->js('/lib/yui/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js');
} else {
    $PAGE->requires->js('/mod/turningtech/js/yahoo-dom-event.js');
}
$PAGE->requires->js('/mod/turningtech/js/turningtech.js');
$PAGE->requires->js('/mod/turningtech/js/jscript.js');
$id = required_param('id', PARAM_INT); // Course.
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

global $USER;
if ($CFG->version >= '2013111800.00') {
    $context = context_course::instance($course->id);
} else {
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
}
$title   = get_string('deviceregistration', 'turningtech');
$PAGE->navbar->add($title);
$PAGE->set_heading($course->fullname);
$PAGE->requires->css('/mod/turningtech/css/style.css');

// Print the header.

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
// Determine if this is a student or instructor?
if (TurningTechMoodleHelper::isUserStudentInCourse($USER, $course)) {
    // Initializing the Response Card Form Opening as false.
    $leavereswarefrmopen = false;
    $rwid                = null;
    $rwform              = new turningtech_responseware_form("index.php?id={$id}");
    // Process responseware form.
    if ($rwdata = $rwform->get_data()) {
        try {
            $rwid                   = turningtech_dopostrw(
            $CFG->turningtech_responseware_provider, $rwdata->username, $rwdata->password);
            $allparams              = new stdClass();
            $allparams->userid      = $USER->id;
            $allparams->all_courses = 1;
            $allparams->typeid      = $rwdata->typeid;
            $allparams->deviceid    = $rwid;
            $allparams->deleted     = 0;
            $allparams->courseid    = $course->id;
            $params              = new stdClass();
            $params->userid      = $USER->id;
            $params->deviceid    = $rwid;
            $params->deleted     = 0;
            $params->all_courses = 1;
            $params->courseid    = $course->id;
            $map = TurningTechDeviceMap::generate($allparams, false);
            /*if ($existing = TurningTechDeviceMap::fetch($params, FALSE) || TurningTechDeviceMap::isRWAlreadyInUse($allparams)) {
             turningtech_set_message(get_string('deviceidalreadyinuse', 'turningtech'));
            $leavereswarefrmopen = true;
            } else*/
            if ($map->save()) {
                turningtech_set_message(get_string('deviceidsaved', 'turningtech'));
            } else {
                turningtech_set_message(get_string('errorsavingdeviceid', 'turningtech'), 'error');
                $leavereswarefrmopen = true;
            }
        } catch (Exception $e) {
            turningtech_set_message(get_string('couldnotauthenticate', 'turningtech', $CFG->turningtech_responseware_provider));
            $leavereswarefrmopen = true;
        }
        // Post values got but validation fails, hence show the form with message.
        // If user has tried to register Response Ware device.
    } else if (array_key_exists('typeid', $_POST) && $_POST['typeid'] == 2) {
        $leavereswarefrmopen = true;
    }
    $dto              = new stdClass();
    $dto->all_courses = 1;
    $dto->typeid      = 2;
    $rwform->set_data($dto);
    // Initializing the Response Card Form Opening as false.
    $leaverescardfrmopen = false;
    ?>
    <script type="text/javascript">
    //<![CDATA[
 
    //]]>
    </script>
    <?php
    // Process the edit form.
    $default_url = "{$CFG->wwwroot}/mod/turningtech/index.php?id={$course->id}";
    $editform = new turningtech_device_form("index.php?id={$id}");
    if ($editform->is_cancelled()) {
            redirect($default_url);
    } else if ($data = $editform->get_data()) {
        if (strlen($data->deviceid)== '8') {
            $data->typeid = 2;
        }
        $map = TurningTechDeviceMap::generateFromForm($data);
        if ($map->save()) {
            turningtech_set_message(get_string('deviceidsaved', 'turningtech'));
        } else {
            turningtech_set_message(get_string('errorsavingdeviceid', 'turningtech'), 'error');
            $leaverescardfrmopen = true;
        }
        // Post values got but validation fails, hence show the form with message.
        // If user has tried to register Response Card device.
    } else if (array_key_exists('typeid', $_POST) && $_POST['typeid'] == 1) {
        $leaverescardfrmopen = true;
    }
    // Show list of existing devices.
    $device_list      = turningtech_list_user_devices($USER, $course);
    // Set up and display form for new device map.
    $dto              = new stdClass();
    $dto->userid      = $USER->id;
    $dto->courseid    = $course->id;
    $dto->all_courses = 1;
    $dto->typeid      = 1;
    $editform->set_data($dto);
    // Call the template to render.
    require_once($CFG->dirroot . '/mod/turningtech/lib/templates/student_index.php');
} else {
    // So user is a member of course, but not a student.  Let's make sure they have
    // Permission to manage devices.
    require_capability('mod/turningtech:manage', $context);
    $action = optional_param('action', 'deviceid', PARAM_ALPHA);
    echo turningtech_show_messages();
    // List actions.
    /*turningtech_list_instructor_actions($USER, $course, $action);
    switch ($action) {
        case 'deviceid':
            turningtech_list_course_devices($course);
            break;
        case 'sessionfile':
            turningtech_import_session_file($course);
            break;
        case 'purge':
            turningtech_import_purge_course_devices($course);
            break;
    } */
}
    ?>
    <script
	type='text/javascript' src='js/jQuery.min.js'></script>
    <script>
	//<![CDATA[
    jQuery(document).ready(function () {
        $('#turningtechdevicemapheaderstudent').css('border', 'none');
        <?php if ($modlver == '2.5' || $modlver == '2.6') { ?>
        $('.mform fieldset').css('border', 'none');
            <?php 
}
            ?>
        $('#responsewareheader').css('border', 'none');
        $('#fitem_id_username').children('div:first').css('white-space', 'noWrap');
        $('#fitem_id_password').children('div:first').css('white-space', 'noWrap');
        $('#fitem_id_username').css('width', '100%');
        $('#id_username').css('margin-right', '25%').css('margin-top', '6px');
        $('#id_password').css('margin-right', '25%').css('margin-top', '6px');


        $('#fitem_id_username').children('div:last').css('text-align', 'right').css('float', 'x');
        $('#fitem_id_password').css('width', '100%');
        $('#fitem_id_password').children('div:last').css('text-align', 'right').css('float', 'x');

        $('#turningtechdevicemapheaderstudent').children('legend:first').text(' ');
        $('#mform2').find('#fitem_id_deviceid').children('div:first').css('width', '48%');
        $('#mform2').find('#id_deviceid').parent('div:first').css('margin-left', '50%');
        $('.error').css('display', 'block').css('float', 'x').css('text-align', 'right');
        $('.fsubmit').css('margin-left', '50%');
        $('#mform1').find('#fitem_id_submitbutton').children('input.fitemtitle').html("");
        $('#mform1').find('#fitem_id_submitbutton').find('#id_submitbutton').css('margin-left', '0');
        jQuery('#mform2 span.error').each(function () {
            jQuery(this).prependTo(jQuery('#mform2').parent());
        });
        jQuery('#fitem_id_username span.error').each(function () {
            jQuery(this).prependTo(jQuery('#fitem_id_username').parent()).css('margin-right', '-6%');
        });
        jQuery('#fitem_id_password span.error').each(function () {
            jQuery(this).prependTo(jQuery('#fitem_id_password').parent());
        });
        <?php if ($modlver == '2.5') { ?>
        jQuery('#id_responsewareheader legend.ftoggler').css('display','none');
        jQuery('#id_turningtechdevicemapheaderstudent legend.ftoggler').css('display','none');
          <?php 
}
          ?>
        jQuery('#divResponseCard span.error').each(function () {
           jQuery('#errormessage').html(jQuery(this).html());
           jQuery(this).html(" ");
           jQuery('#id_deviceid').parent('div').removeClass('error');
           jQuery('#id_deviceid').parent('div').css('display','inline').css('text-align','left');
           if (jQuery('#divResponseCard span.error').html()!='') {
        	   $('#mform2').find('#id_deviceid').parent('div:first').css('margin-left', '5%');
           }
            
       });
        // jQuery('#responsewareheader div.error').each(function(){
        //     jQuery(this).prependTo(jQuery('#mform1').parent().parent());
        //  });
        var l = jQuery('ul.message li').html();

        jQuery('span.error > br').remove();
        jQuery('span.error').css('background-color','#ffffff').css('border','none').css('padding','1px');
        jQuery('label').css('font-weight','normal');
        jQuery('#id_deviceid').parent().find('br').remove();
        jQuery('div.error > br').remove();
        jQuery('#errormessage').html(jQuery('ul.message li').html());
        jQuery('#errormessage').css('color', 'red');
        jQuery('ul.message').html(" ");

        var k = $('#fitem_id_username').parent('div').children('span').html();
        var j = $('#fitem_id_password').parent('div').children('span').html();

        if (l == '<?php echo get_string('couldnotauthenticate', 'turningtech');?>') {
            unhide('divResponseWare');
        }
		
        if (j || k) {
            unhide('divResponseWare');
        }
        <?php if ($pluginconfig ==TURNINGTECH_DISABLE_RESPONSEWARE) { ?>
        jQuery('div.responsecard-container').css('float','none').css('margin','auto');
        jQuery('#errormessage').html(jQuery('ul.error li').html());
        jQuery('#errormessage').css('color', 'red');
        jQuery('ul.error').html(" ");
        <?php 
}
        ?>
    });
    //]]>
	</script>
    <script>
    //<![CDATA[

        jQuery(document).ready(function () {
            var ver = getInternetExplorerVersion();
            var btype = getBrowserType();
            if(ver != -1) {
                $('#id_deviceid').parent('div:last').css('width', '45%').css('margin-left', '10px');
                $('#mform2').find('#fitem_id_deviceid').children('div:first').css('width', '50%');
                $('#mform2').find('#id_submitbutton').parent('div:first').css('margin-left', '55%');
                $('#id_username').parent('div:last').css('width', '60%');
                $('#id_password').parent('div:last').css('width', '60%');
                $('.fstatic').css('width', '50%');
           }
           <?php if ($modlver == '2.5') { ?>
           if(ver == 9 or ver == 10) {
           $('#turningtech-device-page form#mform1').css('width', '100%');
           }
		              <?php 
}
           ?>
		   <?php if ($modlver == '2.6' or $modlver == '2.3' or $modlver == '2.4') { ?>
           if(ver == 10) {
           $('#id_username').css('margin', '0');
		   $('#id_password').css('margin', '0');
		   $('.fstatic').css('margin-left', '28%');
		   $('#id_deviceid').parent('div').css('width', '84%');
		   $('#id_deviceid').css('margin-left', '20px');
           }
           <?php 
}
           ?>
            if(btype==2) {
                $('#id_deviceid').parent('div:last').css('width', '50%').css('margin-left', '10px');
                $('#mform2').find('#fitem_id_deviceid').children('div:first').css('width', '48%');
                $('#mform2').find('#id_submitbutton').parent('div:first').css('margin-left', '50%');
                $('#id_username').parent('div:last').css('width', '60%');
                $('#id_password').parent('div:last').css('width', '60%');
                $('.fstatic').css('width', '50%');
           }
        });

            //]]>
    </script>
    <?php
    echo $OUTPUT->footer();