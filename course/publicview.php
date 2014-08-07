<?php
// publicview.php 201205 sryder
// modified version of /course/view.php
// displays a public version of course home page, which shows the custom course description block

//  Display the course home page.

require_once('../config.php');
require_once('lib.php');

$id          = optional_param('id', 0, PARAM_INT);
$name        = optional_param('name', '', PARAM_RAW);
$idnumber    = optional_param('idnumber', '', PARAM_RAW);

$wwwroot = '';
$signup = '';
if (empty($CFG->loginhttps)) {
    $wwwroot = $CFG->wwwroot;
} else {
    $wwwroot = str_replace("http://", "https://", $CFG->wwwroot);
}

if (!empty($CFG->registerauth)) {
    $authplugin = get_auth_plugin($CFG->registerauth);
    if ($authplugin->can_signup()) {
        $signup = $wwwroot . '/login/signup.php';
    }
}

if (isloggedin()) {
    redirect($CFG->wwwroot .'/course/view.php?id='.$id);
}
else {

    if (empty($id) && empty($name) && empty($idnumber)) {
        print_error('unspecifycourseid', 'error');
    }

    if (!empty($name)) {
        if (! ($course = $DB->get_record('course', array('shortname'=>$name)))) {
            print_error('invalidcoursenameshort', 'error');
        }
    } else if (!empty($idnumber)) {
        if (! ($course = $DB->get_record('course', array('idnumber'=>$idnumber)))) {
            print_error('invalidcourseid', 'error');
        }
    } else {
        if (! ($course = $DB->get_record('course', array('id'=>$id)))) {
            print_error('invalidcourseid', 'error');
        }
    }

    $PAGE->set_url('/course/view.php', array('id' => $course->id)); // Defined here to avoid notices on errors etc

    preload_course_contexts($course->id);
    if (!$context = get_context_instance(CONTEXT_COURSE, $course->id)) {
        print_error('nocontext');
    }

    add_to_log($course->id, 'course', 'view', "view.php?id=$course->id", "$course->id");

    $PAGE->set_pagelayout('course');

    if ($course->id == SITEID) {
        // This course is not a real course.
        redirect($CFG->wwwroot .'/');
    }

    $PAGE->set_title(get_string('course') . ': ' . $course->fullname);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();

    // Course wrapper start.
    echo html_writer::start_tag('div', array('class'=>'course-content'));

    // get data from the course description block, which is all we want to show on this page
    $desc_info = $DB->get_record('block_course_description', array('courseid' => $course->id));

    $mtg_info = '';
    if(!empty($desc_info->mtg_days)) {
        
        $desc_info->mtg_days = preg_replace("/SU/", "0", $desc_info->mtg_days);
        $desc_info->mtg_days = preg_replace("/TH/", "4", $desc_info->mtg_days);
        $desc_info->mtg_days = preg_replace("/M/", "1", $desc_info->mtg_days);
        $desc_info->mtg_days = preg_replace("/T/", "2", $desc_info->mtg_days);
        $desc_info->mtg_days = preg_replace("/W/", "3", $desc_info->mtg_days);
        $desc_info->mtg_days = preg_replace("/F/", "5", $desc_info->mtg_days);
        $desc_info->mtg_days = preg_replace("/S/", "6", $desc_info->mtg_days);

        // multiples are stored w/ ; as delimiter so this breaks em out into arrays
        $mtg_days = explode(";", $desc_info->mtg_days);
        $mtg_start_times = explode(";", $desc_info->mtg_start_times);
        $mtg_end_times = explode(";", $desc_info->mtg_end_times);
        $mtg_bldg = explode(";", $desc_info->mtg_bldg);
        $mtg_room = explode(";", $desc_info->mtg_room);
        // sets up arrays of weekdays and buildings used for display
        $weekdays = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");

        $bldg['ARB'] = 'Arts Barn';
        $bldg['ARF'] = 'Animal Research Facility';
        $bldg['ASH'] = 'Adele Simmons Hall (ASH)';
        $bldg['BLH'] = 'Blair Hall';
        $bldg['CHC'] = "Children's Center";
        $bldg['CSC'] = 'Cole Science Center';
        $bldg['DAK'] = 'Dakin House';
        $bldg['DANA'] = 'Dana House (Donut 5)';
        $bldg['DMH'] = 'Dakin Master House';
        $bldg['EDH'] = 'Emily Dickinson Hall (EDH)';
        $bldg['ENF'] = 'Enfield House';
        $bldg['FPH'] = 'Franklin Patterson Hall (FPH)';
        $bldg['GRN'] = 'Greenwich';
        $bldg['GST'] = 'Guest House';
        $bldg['KER'] = 'Kerminsky House';
        $bldg['LCD'] = 'Lemelson Center for Design';
        $bldg['LIB'] = 'Harold Johnson Library';
        $bldg['LWP'] = 'LeBron-Wiggins-Pran Cultural Center';
        $bldg['MDB'] = 'Music and Dance Building';
        $bldg['MER'] = 'Merrill House';
        $bldg['MMH'] = 'Merrill Masters House';
        $bldg['MON'] = 'Montague Hall';
        $bldg['MSC'] = 'Multi-Sports Center';
        $bldg['PFB'] = 'Photography and Film Building';
        $bldg['PLF'] = 'Playing Fields';
        $bldg['PRS'] = 'Prescott House';
        $bldg['RCC'] = 'Robert Crown Center';
        $bldg['RDB'] = 'Red Barn';
        $bldg['RSH'] = 'Robert Stiles House';
        $bldg['SAGA'] = 'Dining Commons (SAGA)';
        $bldg['STA'] = 'Student Affairs';
        $bldg['TBC'] = 'Tennis & Basketball Courts';
        $bldg['THH'] = 'Thorpe House (Farm Center)';
        $bldg['TMC'] = 'Tavern & Mixed-Nuts Co-op';
        $bldg['WAH'] = 'Warner House';
        $bldg['WRC'] = 'Writing Center';
        $bldg['YBC'] = 'Yiddish Book Center';
        $bldg['YURT'] = 'Yurt';

        $containers = count($mtg_days);
        $locations = count($mtg_room);

        for ($j=0;$j<$containers;$j++) {
            $mtg_info .= "<table cellpadding=2>";
            for ($k=0;$k<strlen($mtg_days[$j]);$k++) {
                $mtg_info .= '<tr<td><strong>'.$weekdays[substr($mtg_days[$j], $k, 1)].' </td></strong>';
                $mtg_info .= '<td>'.strtoampm(date("H:i:00",strtotime($mtg_start_times[$j]))).' - ';
                $mtg_info .= strtoampm(date("H:i:00",strtotime($mtg_end_times[$j]))).' ';

                // if there are multiple locations for a single meeting time then it will display all locations
                if($locations > $containers) {
                    for($l=0;$l<$locations;$l++) {
                        if($mtg_bldg[$l]) {
                            $mtg_info .= $bldg[$mtg_bldg[$l]].' ';
                         
                        }
                        if($mtg_room[$l]) {
                            $mtg_info .= $mtg_room[$l].' ';
                        }
                    }
                } else {
                    if($mtg_bldg[$j]) {
                        $mtg_info .= $bldg[$mtg_bldg[$j]].' ';
                 
                    }
                    if($mtg_room[$j]) {
                        $mtg_info .= $mtg_room[$j];
                    }
                }

                $mtg_info .= "</td></tr>";
            }
            $mtg_info .= "</table>";
        }

        // gets all people assigned to teacher and ta roles in the course
        $tsql="SELECT userid,roleid FROM  {$CFG->prefix}role_assignments JOIN {$CFG->prefix}context ON contextid = {$CFG->prefix}context.id AND contextlevel= 50 WHERE {$CFG->prefix}context.instanceid=$course->id AND roleid in ($CFG->block_course_description_roles)";
        if(!$teachers=$DB->get_records_sql($tsql)) {
            $teacher_info = '';
        }

        $teacher_info = '';
        $ta_info = '';

        if($teachers) {
            foreach ($teachers as $teacherid){
                $teacher = $DB->get_record('user', array('id' => $teacherid->userid));
                $roleinfo = $DB->get_record('role', array('id' => $teacherid->roleid));

                if($roleinfo->shortname == 'editingteacher' || $roleinfo->shortname == 'student_instructor') {
                    $teacher_info .= "$teacher->firstname $teacher->lastname<br>";
                    if($teacher->phone1) {
                        $teacher_info .= "Office Extension x$teacher->phone1<br>";
                    }
                }
                if($roleinfo->shortname == 'ta' || $roleinfo->shortname == 'ta_grade') {
                    $ta_info .= "$teacher->firstname $teacher->lastname<br>";
                    if($teacher->phone1) {
                        $ta_info .= "Office Extension x$teacher->phone1<br>";
                    }
                }
            }
        }


        $content = "<table border=0 cellpadding=4 cellspacing=2><tbody>";
        if(!empty($teacher_info)) {
            $content .= "<tr><td valign=top nowrap=''><strong>Instructor Info:</strong></td><td>".$teacher_info."</td></tr>";
        }
        if(!empty($ta_info)) {
            $content .= "<tr><td valign=top nowrap=''><strong>TA Info:</strong></td><td>".$ta_info."</td></tr>";
        }
        // only prints a row in the table for pieces that have content
        if(!empty($course->shortname)) {
            $shortname = explode('_',$course->shortname);
            $content .= "<tr><td valign=top nowrap=''><strong>Term: </strong></td><td>".$shortname[1]."</td></tr>";
        }
        if($mtg_info) {
            $content .= "<tr><td valign=top nowrap=''><strong>Meeting Info: </strong></td><td>".$mtg_info."</td></tr>";
        }
        if(!empty($desc_info->course_description)) {
            $content .= "<tr><td valign=top nowrap=''><strong>Description: </strong></td><td>".$desc_info->course_description."</td></tr>";
        }
        if(!empty($desc_info->course_obj)) {
            $content .= "<tr><td valign=top nowrap=''><strong>Course Objectives: </strong></td><td>".$desc_info->course_obj."</td></tr>";
        }
        if(!empty($desc_info->eval_criteria)) {
            $content .= "<tr><td valign=top nowrap=''><strong>Evaluation Criteria: </strong></td><td>".$desc_info->eval_criteria."</td></tr>";
        }
        if(!empty($desc_info->add_info)) {
            $content .= "<tr><td valign=top nowrap=''><strong>Additional Info: </strong></td><td>".$desc_info->add_info."</td></tr>";
        }
        $content .= "</tbody></table>";

    }

?>
    <table id="layout-table" summary="layout">
        <tr>
            <td id="middle-column">
<?
    print_side_block('Course Information', $content, NULL, NULL, NULL, array('class' => 'block_course_description'));

?>
            </td>
        </tr>
    </table>
<?
    echo html_writer::end_tag('div');
    echo $OUTPUT->footer();
}

// formats start/end times for display
function strtoampm($timestring) {
    $timestring = strftime("%I:%M %p",strtotime($timestring));
    return $timestring;
}

