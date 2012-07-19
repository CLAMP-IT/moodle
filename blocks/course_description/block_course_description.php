<?php
/**
 * The course description blocks are auto-populated by a script that cron runs
 * The script imports a csv file w/ meeting times and a txt file w/ course description
 * Course description only gets added once and then is editable by Teacher
 *
 * @package    block_course_description
 * @author     Sarah Ryder <sryder@hampshire.edu>
 * @copyright  2010 onwards Hampshire College
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_description extends block_base {

    function init() {
	    $this->title = get_string('pluginname','block_course_description');
	    $this->version = 2012031901;
    }

    // only one instance of this block is required
    function instance_allow_multiple() {
        return false;
    }

    function applicable_formats() {
        return array('course-view' => true);
    }

    function get_content() {
        global $USER, $CFG, $COURSE, $DB, $OUTPUT;
	    if ($this->content !== NULL) {
	        return $this->content;
	    }
	
	    $this->content =  new stdClass;
	    $this->content->footer = '';
	
	    if (empty($this->instance)) {
	        return $this->content;
        }

	    $desc_info = $DB->get_record('block_course_description', array('courseid' => $COURSE->id));

	    // if a detailed block entry doesn't exist, create a blank record
	    if(empty($desc_info)) {

	        $block_info = $DB->get_record('block', array('name' => 'course_description'));

            // prepare an object for the insert_record function
            $cdnew = new stdClass;
            $cdnew->courseid = $COURSE->id;
	        $cdnew->blockid = $block_info->id;
            $cdnew->course_description = '';
            $cdnew->course_obj = '';
            $cdnew->eval_criteria = '';
            $cdnew->add_info = '';
            if (!$DB->insert_record('block_course_description', $cdnew)) {
                error('Unable to create course description block details.');
            }
	    }
	
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
                    $mtg_info .= '<tr><td><strong>'.$weekdays[substr($mtg_days[$j], $k, 1)].' </td></strong>';
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
        }

        // parses the term from the course shortname
        $shortname = explode('_',$COURSE->shortname);
        
        // gets all people assigned to teacher and ta roles in the course
        $tsql="SELECT userid,roleid FROM  {$CFG->prefix}role_assignments JOIN {$CFG->prefix}context ON contextid = {$CFG->prefix}context.id AND contextlevel= 50 WHERE {$CFG->prefix}context.instanceid=$COURSE->id AND roleid in ($CFG->block_course_description_roles)";
        if(!$teachers=$DB->get_records_sql($tsql)) {
            $teacher_info = '';
        }
        
        $teacher_info = '';
        $ta_info = '';
        
        if($teachers) {
            foreach ($teachers as $teacherid){
                $teacher=$DB->get_record('user', array('id' => $teacherid->userid));

                if($ohfield = $DB->get_record('user_info_field', array('shortname' => 'officehours'))) {
                    $oh = $DB->get_record('user_info_data', array('fieldid' => $ohfield->id, 'userid' => $teacherid->userid));
                }

                $roleinfo = $DB->get_record('role', array('id' => $teacherid->roleid));

                if($roleinfo->shortname == 'editingteacher' || $roleinfo->shortname == 'student_instructor') {
                    $teacher_info .= "<table>";
                    $teacher_info .= "<tr><td colspan=100%><b><a href=\"$CFG->wwwroot/user/view.php?id=$teacher->id&course=$COURSE->id\">$teacher->firstname $teacher->lastname</a></b></td></tr>";
                    if($teacher->email) {
                        $teacher_info .= "<tr><td colspan=100%><a href=\"mailto:$teacher->email\">$teacher->email</a></td></tr>";
                    }
                    if($teacher->phone1) {
                        $teacher_info .= "<tr><td>Office Extension:</td><td>x$teacher->phone1</td></tr>";
                    }
                    if($teacher->phone2) {
                        $teacher_info .= "<tr><td>Mobile Phone:</td><td>$teacher->phone2</td></tr>";
                    }
                    if(isset($oh)) {
                        if(isset($oh->data)) {
                            $teacher_info .= "<tr><td valign=top>Office Hours:</td><td>$oh->data</td></tr>";
                        }
                    }
                    $teacher_info .= "</table>";
                }
                if($roleinfo->shortname == 'ta' || $roleinfo->shortname == 'ta_grade') {
                    $ta_info .= "<table>";
                    $ta_info .= "<tr><td colspan=100%><b><a href=\"$CFG->wwwroot/user/view.php?id=$teacher->id&course=$COURSE->id\">$teacher->firstname $teacher->lastname</a></b></td></tr>";
                    if($teacher->email) {
                        $ta_info .= "<tr><td colspan=100%><a href=\"mailto:$teacher->email\">$teacher->email</a></td></tr>";
                    }
                    if($teacher->phone1) {
                        $ta_info .= "<tr><td>Office Extension:</td><td>x$teacher->phone1</td></tr>";
                    }
                    if($teacher->phone2) {
                        $ta_info .= "<tr><td>Mobile Phone:</td><td>$teacher->phone2</td></tr>";
                    }
                    if($oh) {
                        if($oh->data) {
                            $ta_info .= "<tr><td valign=top>Office Hours:</td><td>$oh->data</td></tr>";
                        }
                    }
                    $ta_info .= "</table>";

                }
            }
        }
        
        $editing = $this->page->user_is_editing();
        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        $tlink = '';
        $talink = '';
        $editlink = '';
        
        // only shows the edit links if user has permission and editing is turned on
        if($this->check_permission() && $editing) {
            $tlink = " <a href=\"$CFG->wwwroot/enrol/users.php?id={$COURSE->id}\" title=\"".get_string('edit')."\"><img src=\"".$OUTPUT->pix_url('t/edit')."\"></a>";
            $talink = " <a href=\"$CFG->wwwroot/enrol/users.php?id={$COURSE->id}\" title=\"".get_string('edit')."\"><img src=\"".$OUTPUT->pix_url('t/edit')."\"></a>";
            $editlink = " <a href=\"$CFG->wwwroot/blocks/course_description/update_courseinfo.php?id={$COURSE->id}&amp;instanceid={$this->instance->id}&amp;cdid={$desc_info->id}\" title=\"".get_string('edit')."\"><img src=\"".$OUTPUT->pix_url('t/edit')."\"></a>";
        }
        
        $this->content->text = "<table border=0 cellpadding=4 cellspacing=2><tbody>";
        if(!empty($teacher_info) || $editing) {
            $this->content->text .= "<tr><td valign=top nowrap=''><strong>Instructor Info:</strong>".$tlink."</td><td>".$teacher_info."</td></tr>";
        }
        if(!empty($ta_info) || $editing) {
            $this->content->text .= "<tr><td valign=top nowrap=''><strong>TA Info:</strong>".$talink."</td><td>".$ta_info."</td></tr>";
        }
        // only prints a row in the table for pieces that have content
        if(!empty($shortname[1])) {
            $this->content->text .= "<tr><td valign=top nowrap=''><strong>Term: </strong></td><td>".$shortname[1]."</td></tr>";
        }
        if($mtg_info) {
            $this->content->text .= "<tr><td valign=top nowrap=''><strong>Meeting Info: </strong></td><td>".$mtg_info."</td></tr>";
        }
        if(!empty($desc_info->course_description) || $editing) {
            $this->content->text .= "<tr><td valign=top nowrap=''><strong>Description: </strong>".$editlink."</td><td>".$desc_info->course_description."</td></tr>";
        }
        if(!empty($desc_info->course_obj) || $editing) {
            $this->content->text .= "<tr><td valign=top nowrap=''><strong>Course Objectives: </strong>".$editlink."</td><td>".$desc_info->course_obj."</td></tr>";
        }
        if(!empty($desc_info->eval_criteria) || $editing) {
            $this->content->text .= "<tr><td valign=top nowrap=''><strong>Evaluation Criteria: </strong>".$editlink."</td><td>".$desc_info->eval_criteria."</td></tr>";
        }
        if(!empty($desc_info->add_info) || $editing) {
                $this->content->text .= "<tr><td valign=top nowrap=''><strong>Additional Info: </strong>".$editlink."</td><td>".$desc_info->add_info."</td></tr>";
        }
        $this->content->text .= "</tbody></table>";
        return $this->content;
    }

    function check_permission() {
        // check to see if current user's role is teacher
	    return has_capability('block/course_description:canedit', get_context_instance(CONTEXT_BLOCK, $this->instance->id));
    }
}
// formats start/end times for display
function strtoampm($timestring) {
    $timestring = strftime("%I:%M %p",strtotime($timestring));
    return $timestring;
}

?>
