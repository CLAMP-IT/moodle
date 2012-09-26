<?php // $Id: index.php,v 1.2 2010/06/25 11:49:46 paul.dawson Exp $
/**
 * @package   turnitintool
 * @copyright 2010 iParadigms LLC
 */
    require_once("../../config.php");
    require_once("lib.php");

    if (isset($PAGE) AND is_callable(array($PAGE->requires, 'js'))) { // Are we using new moodle or old?
        $jsurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/turnitintool.js');
        $PAGE->requires->js($jsurl,true);
        $cssurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/styles.css');
        $PAGE->requires->css($cssurl);
    } else {
        require_js($CFG->wwwroot.'/mod/turnitintool/turnitintool.js');
    }

    $id = required_param('id', PARAM_INT);   // course

    if (! $course = turnitintool_get_record("course", "id", $id)) {
        turnitintool_print_error('courseiderror','turnitintool');
    }

    require_login($course->id);

    add_to_log($course->id, "turnitintool", "view all", "index.php?id=$course->id", "");


/// Get all required stringsnewmodule

    $strturnitintools = get_string("modulenameplural", "turnitintool");
    $strturnitintool  = get_string("modulename", "turnitintool");
	

    if (!is_callable('build_navigation')) {
        $navigation = array(
        array('title' => $course->shortname, 'url' => $CFG->wwwroot."/course/view.php?id=$course->id", 'type' => 'course'),
        array('title' => $strturnitintools, 'url' => '', 'type' => 'activity')
                          );
    } else {
        $navigation = array(
        array('name' => $strturnitintools, 'url' => '', 'type' => 'activity')
                                  );
        $navigation = build_navigation($navigation,"");
    }

    /// Print the header
    
    turnitintool_header(NULL,$course,$_SERVER["REQUEST_URI"],$strturnitintools, $SITE->fullname, $navigation, '', '', true, '', '');

    //print_header_simple($strturnitintools, '', $navigation, "", "", true, "", navmenu($course));
    echo '<div id="turnitintool_style">';

/// Get all the appropriate data

    if (! $turnitintools = get_all_instances_in_course("turnitintool", $course)) {
        notice("There are no ".$strturnitintools, "../../course/view.php?id=$course->id");
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");
    $strdtstart = get_string("dtstart","turnitintool");
    $strsubmissions = get_string("submissions","turnitintool");
    $strnumparts = get_string("numberofparts","turnitintool");

    if ($course->format == "weeks") {
        $cells[0]->data=$strweek;
        $cells[0]->class="header c0 cellcenter cellthin";
        $cells[1]->data=$strname;
        $cells[1]->class="header c1 cellleft";
        $cells[2]->data=$strdtstart;
        $cells[2]->class="header c2 cellcenter cellthin";
        $cells[3]->data=$strnumparts;
        $cells[3]->class="header c3 cellcenter cellthin";
        $cells[4]->data=$strsubmissions;
        $cells[4]->class="header c4 cellcenter cellthin";
        $table->rows[]->cells=$cells;
    } else if ($course->format == "topics") {
        $cells[0]->data=$strtopic;
        $cells[0]->class="header c0 cellcenter cellthin";
        $cells[1]->data=$strname;
        $cells[1]->class="header c1 cellleft";
        $cells[2]->data=$strdtstart;
        $cells[2]->class="header c2 cellcenter cellthin";
        $cells[3]->data=$strnumparts;
        $cells[3]->class="header c3 cellcenter cellthin";
        $cells[4]->data=$strsubmissions;
        $cells[4]->class="header c4 cellcenter cellthin";
        $table->rows[]->cells=$cells;
    } else {
        $cells[0]->data=$strname;
        $cells[0]->class="header c0 cellleft";
        $cells[1]->data=$strdtstart;
        $cells[1]->class="header c1 cellcenter cellthin";
        $cells[2]->data=$strnumparts;
        $cells[2]->class="header c2 cellcenter cellthin";
        $cells[3]->data=$strsubmissions;
        $cells[3]->class="header c3 cellcenter cellthin";
        $table->rows[]->cells=$cells;
    }
    $table->class='';
    $table->width='100%';



    foreach ($turnitintools as $turnitintool) {
        $dimmed='';
        if (!$turnitintool->visible) {
            //Show dimmed if the mod is hidden
            $dimmed=' class="dimmed"';
        }

        $link = '<a'.$dimmed.' href="view.php?id='.$turnitintool->coursemodule.'">'.$turnitintool->name.'</a>';
        $part=turnitintool_get_record_select('turnitintool_parts','turnitintoolid='.$turnitintool->id.' AND deleted=0','MIN(dtstart) AS dtstart');
        $dtstart = '<span'.$dimmed.'>'.userdate($part->dtstart,get_string('strftimedatetimeshort','langconfig')).'</span>';
        $partcount=turnitintool_count_records_select('turnitintool_parts','turnitintoolid='.$turnitintool->id.' AND deleted=0');
		if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $turnitintool->coursemodule))) {
	        $submissioncount='<a'.$dimmed.' href="view.php?id='.$turnitintool->coursemodule.'&do=allsubmissions">'.turnitintool_count_records('turnitintool_submissions','turnitintoolid',$turnitintool->id).'</a>';
		} else {
			$submissioncount='<a'.$dimmed.' href="view.php?id='.$turnitintool->coursemodule.'&do=submissions">'.turnitintool_count_records_select('turnitintool_submissions','turnitintoolid='.$turnitintool->id.' AND userid='.$USER->id).'</a>';
		}
        if ($course->format == "weeks" or $course->format == "topics") {
            unset($cells);
            $cells[0]->data=$turnitintool->section;
            $cells[0]->class="cell c0 cellcenter cellthin";
            $cells[1]->data=$link;
            $cells[1]->class="cell c1 cellleft";
            $cells[2]->data=$dtstart;
            $cells[2]->class="cell c2 cellcenter cellthin";
            $cells[3]->data=$partcount;
            $cells[3]->class="cell c3 cellcenter cellthin";
            $cells[4]->data=$submissioncount;
            $cells[4]->class="cell c4 cellcenter cellthin";
            $table->rows[]->cells=$cells;
        } else {
            unset($cells);
            $cells[0]->data=$link;
            $cells[0]->class="cell c0 cellleft";
            $cells[1]->data=$dtstart;
            $cells[1]->class="cell c1 cellcenter cellthin";
            $cells[2]->data=$partcount;
            $cells[2]->class="cell c2 cellcenter cellthin";
            $cells[3]->data=$submissioncount;
            $cells[3]->class="cell c3 cellcenter cellthin";
            $table->rows[]->cells=$cells;

            $table->data[] = array ($link, $dtstart, $partcount, $submissioncount);
        }
    }

    echo "<br />";

    turnitintool_print_table($table);

/// Finish the page

    echo "</div>";
    turnitintool_footer($course);

/* ?> */