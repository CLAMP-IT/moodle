<?php
/**
 * @package   turnitintool
 * @copyright 2012 Turnitin
 */
////////////////////////////////////////////////////////////////////////////////////////////////////
/**
 * Encryption value passed to the API
 */
defined("TURNITINTOOL_ENCRYPT") or define("TURNITINTOOL_ENCRYPT","0");
/**
 * The pause in between API calls
 */
defined("TURNITINTOOL_LATENCY_SLEEP") or define("TURNITINTOOL_LATENCY_SLEEP","4");
/**
 * API Error: Start Error Code
 */
defined("TURNITINTOOL_API_ERROR_START") or define("TURNITINTOOL_API_ERROR_START","100");
/**
 * API Error: Database Error inserting unique ID into the database
 */
defined("TURNITINTOOL_DB_UNIQUEID_ERROR") or define("TURNITINTOOL_DB_UNIQUEID_ERROR","218");
/**
 * API Error: Creating/Updating/Deleting assignment failed in fid 4
 */
defined("TURNITINTOOL_ASSIGNMENT_UPDATE_ERROR") or define("TURNITINTOOL_ASSIGNMENT_UPDATE_ERROR","411");
/**
 * API Error: The assignment you are trying to access does not exist
 * in Turnitin for this class
 */
defined("TURNITINTOOL_ASSIGNMENT_NOTEXIST_ERROR") or define("TURNITINTOOL_ASSIGNMENT_NOTEXIST_ERROR","206");
/**
 * API Error: The assignment with the assignment id that you entered does
 * not belong to the class with the class id you entered
 */
defined("TURNITINTOOL_ASSIGNMENT_WRONGCLASS_ERROR") or define("TURNITINTOOL_ASSIGNMENT_WRONGCLASS_ERROR","228");
/**
 * API SRC Value: The src value defines the integration namespace area
 * in the Turnitin integrations database tables
 */
defined("TURNITINTOOL_APISRC") or define("TURNITINTOOL_APISRC","12");

/**
 * Include the loaderbar class file
 */
require_once("loaderbar.php");
/**
 * Include the comms class file
 */
require_once("comms.php");

/**
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function turnitintool_supports($feature) {
    defined("FEATURE_SHOW_DESCRIPTION") or define("FEATURE_SHOW_DESCRIPTION",null);
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}
/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @global object
 * @param object $turnitintool add turnitintool instance
 * @return int intance id
 */
function turnitintool_add_instance($turnitintool) {

    global $USER,$CFG;

    $turnitintool->timecreated = time();

    $total=($turnitintool->numparts*2)+2;

    // Get Moodle Course Object [[[[
    if (!$course = turnitintool_get_record("course", "id", $turnitintool->course)) {
        turnitintool_print_error('coursegeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }
    // ]]]]

    // Find out if this Course already has a Turnitin Owner [[[[
    if (!turnitintool_is_owner($course->id)) {
        $owner=turnitintool_get_owner($course->id);
        // If the Course has no Turnitin Owner ie above get owner returned NULL
        if (is_null($owner)) {
            $owner=$USER;
        }
    } else {
        $owner=$USER;
    }
    // ]]]]

    $loaderbar = null;
    $tii = new turnitintool_commclass(turnitintool_getUID($owner),$owner->firstname,$owner->lastname,$owner->email,2,$loaderbar);
    $tii->startSession();

    // Set this user up with a Turnitin Account or check to see if an account has already been set up
    // Either return the stored ID OR store the New Turnitin User ID then return it [[[[
    $turnitinuser=turnitintool_usersetup($owner,get_string('userprocess','turnitintool'),$tii,$loaderbar); // PROC 1
    if ($tii->getRerror()) {
        if ($tii->getAPIunavailable()) {
            turnitintool_print_error('apiunavailable','turnitintool',NULL,NULL,__FILE__,__LINE__);
        } else {
            turnitintool_print_error($tii->getRmessage(),NULL,NULL,NULL,__FILE__,__LINE__);
        }
        exit();
    }
    $turnitinuser_id=$turnitinuser->turnitin_uid;
    // ]]]]

    // Set this course up in Turnitin or check to see if it has been already
    // Either return the stored ID OR store the New Turnitin Course ID then return it [[[[
    $turnitincourse=turnitintool_classsetup($course,$owner,
            get_string('classprocess','turnitintool'),$tii,$loaderbar); // PROC 2
    $turnitincourse_id=$turnitincourse->turnitin_cid;
    // ]]]]

    // Insert the Submitted turnitin form data in the database and retreive the id [[[[
    $turnitintool->timemodified=time();

    // Insert the default options
    $turnitintool->dateformat="d/m/Y"; // deprecated (Now using langconfig.php)
    $turnitintool->usegrademark=1;
    $turnitintool->gradedisplay=1;
    $turnitintool->autoupdates=1;
    $turnitintool->commentedittime=1800;
    $turnitintool->commentmaxsize=800;
    $turnitintool->autosubmission=1;
    $turnitintool->shownonsubmission=1;

    $turnitintool->courseid   = $course->id; // compatibility with modedit assignment obj

    $insertid=turnitintool_insert_record("turnitintool", $turnitintool);
    // ]]]]

    // Do the multiple Assignment creation on turnitin
    //## We are creating an assignment for each Moodle Assignment Part [[[[
    for ($i=1;$i<=$turnitintool->numparts;$i++) {
        // Do the turnitin assignment set call to the API [[[[
        $tiipost = new stdClass();
        $tiipost->courseid=$course->id;
        $tiipost->ctl=turnitintool_getCTL($course->id);
        $tiipost->dtstart=time(); // Set as today and update to a date in the past if needed to later
        $tiipost->dtdue=strtotime('+7 days');
        $tiipost->dtpost=strtotime('+7 days');
        $uniquestring=strtoupper(uniqid());
        $tiipost->name=$turnitintool->name." - ".get_string('turnitinpart','turnitintool',$i).
                " (".$uniquestring.")";
        $tiipost->s_view_report=$turnitintool->studentreports;
        $tiipost->max_points=($turnitintool->grade < 0) ? 100 : $turnitintool->grade;

        $tiipost->anon=$turnitintool->anon;
        $tiipost->report_gen_speed=$turnitintool->reportgenspeed;
        $tiipost->late_accept_flag=$turnitintool->allowlate;
        $tiipost->submit_papers_to=$turnitintool->submitpapersto;
        $tiipost->s_paper_check=$turnitintool->spapercheck;
        $tiipost->internet_check=$turnitintool->internetcheck;
        $tiipost->journal_check=$turnitintool->journalcheck;

        // Add in Exclude small matches, biblio, quoted etc 20102009
        $tiipost->exclude_biblio=$turnitintool->excludebiblio;
        $tiipost->exclude_quoted=$turnitintool->excludequoted;
        $tiipost->exclude_value=$turnitintool->excludevalue;
        $tiipost->exclude_type=$turnitintool->excludetype;
        $tiipost->transmatch=$turnitintool->transmatch;

        // Add erater settings
        $tiipost->erater=(isset($turnitintool->erater)) ? $turnitintool->erater : 0;
        $tiipost->erater_handbook=(isset($turnitintool->erater_handbook)) ? $turnitintool->erater_handbook : 0;
        $tiipost->erater_dictionary=(isset($turnitintool->erater_dictionary)) ? $turnitintool->erater_dictionary : 'en_US';
        $tiipost->erater_spelling=(isset($turnitintool->erater_spelling)) ? $turnitintool->erater_spelling : 0;
        $tiipost->erater_grammar=(isset($turnitintool->erater_grammar)) ? $turnitintool->erater_grammar : 0;
        $tiipost->erater_usage=(isset($turnitintool->erater_usage)) ? $turnitintool->erater_usage : 0;
        $tiipost->erater_mechanics=(isset($turnitintool->erater_mechanics)) ? $turnitintool->erater_mechanics : 0;
        $tiipost->erater_style=(isset($turnitintool->erater_style)) ? $turnitintool->erater_style : 0;
        $tiipost->transmatch=(isset($turnitintool->transmatch)) ? $turnitintool->transmatch : 0;

        // == Create the assignment with no IDs in order to retreive the correct ==
        // == Assignment ID for future use.                                      ==
        // ## Needed because if one UID is used then all Must Be,                ##
        // ## we do not have the assignment ID as it has not been created yet    ##

        $tiipost->cid='';
        $tiipost->assignid='';

        $tii->createAssignment($tiipost,'INSERT',get_string('assignmentprocess','turnitintool',$i));

        if ($tii->getRerror()) {
            $reason=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool')
                    : $tii->getRmessage();
            turnitintool_delete_records('turnitintool','id',$insertid);
            turnitintool_print_error('<strong>'.get_string('inserterror','turnitintool')
                    .'</strong><br />'.$reason);
            exit();
        }

        $tiipost->cid=turnitintool_getCID($course->id);
        $part = new stdClass();
        $part->tiiassignid=$tii->getAssignid();
        $tiipost->assignid=$part->tiiassignid;
        $tiipost->dtstart=$turnitintool->defaultdtstart;
        $tiipost->dtdue=$turnitintool->defaultdtdue;
        $tiipost->dtpost=$turnitintool->defaultdtpost;
        $tiipost->currentassign=$tiipost->name;
        $tiipost->name=str_replace(" (".$uniquestring.")"," (Moodle ".$part->tiiassignid.")",$tiipost->name);

        // Now individualise the Assignment Name and set the date to allow dates in the past

        $tii->createAssignment($tiipost,'UPDATE',get_string('assignmentindividualise',
                'turnitintool',$i)); // PROC 3+

        if ($tii->getRerror()) {
            $reason=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool')
                    : $tii->getRmessage();
            turnitintool_delete_records('turnitintool','id',$insertid);
            turnitintool_print_error('<strong>'.get_string('inserterror','turnitintool')
                    .'</strong><br />'.$reason);
            exit();
        }

        $part->turnitintoolid=$insertid;
        $part->partname=get_string('turnitinpart','turnitintool',$i);

        $part->dtstart=$turnitintool->defaultdtstart;
        $part->dtdue=$turnitintool->defaultdtdue;
        $part->dtpost=$turnitintool->defaultdtpost;
        $part->maxmarks=($turnitintool->grade < 0) ? 100 : $turnitintool->grade;
        $part->deleted=0;
        if (!$insert=turnitintool_insert_record('turnitintool_parts',$part,false)) {
            turnitintool_delete_records('turnitintool','id',$insertid);
            turnitintool_print_error('partdberror','turnitintool',NULL,$i,__FILE__,__LINE__);
        }

        $event = new object();
        $event->name        = $turnitintool->name.' - '.$part->partname;
        $event->description = ($turnitintool->intro==NULL) ? '' : $turnitintool->intro;
        $event->courseid    = $turnitintool->course;
        $event->groupid     = 0;
        $event->userid      = 0;
        $event->modulename  = 'turnitintool';
        $event->instance    = $insertid;
        $event->eventtype   = 'due';
        $event->timestart   = $part->dtdue;
        $event->timeduration = 0;
        add_event($event);

        // ]]]]

    }

    // Define grade settings in Moodle 1.9 and above
    $turnitintool->id = $insertid;
    turnitintool_grade_item_update( $turnitintool );
    // ]]]]

    $cm=get_coursemodule_from_instance("turnitintool", $turnitintool->id, $turnitintool->course);
    add_to_log($turnitintool->course, "turnitintool", "add turnitintool", 'view.php?id='.$cm->id, "Assignment created '$turnitintool->name'", $cm->id);

    $tii->endSession();
    return $insertid;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @global object
 * @param object $turnitintool update turnitintool instance
 * @return bool success
 */
function turnitintool_update_instance($turnitintool) {

    global $USER,$CFG, $DB;

    $turnitintool->timemodified = time();
    $turnitintool->id = $turnitintool->instance;

    // Set the checkbox settings for updates
    $turnitintool->erater_spelling  = (isset($turnitintool->erater_spelling))  ? $turnitintool->erater_spelling : 0;
    $turnitintool->erater_grammar   = (isset($turnitintool->erater_grammar))   ? $turnitintool->erater_grammar : 0;
    $turnitintool->erater_usage     = (isset($turnitintool->erater_usage))     ? $turnitintool->erater_usage : 0;
    $turnitintool->erater_mechanics = (isset($turnitintool->erater_mechanics)) ? $turnitintool->erater_mechanics : 0;
    $turnitintool->erater_style     = (isset($turnitintool->erater_style))     ? $turnitintool->erater_style : 0;
    $turnitintool->transmatch       = (isset($turnitintool->transmatch))       ? $turnitintool->transmatch : 0;

    // Get Moodle Course Object [[[[
    if (!$course = turnitintool_get_record("course", "id", $turnitintool->course)) {
        turnitintool_print_error('coursegeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }
    // ]]]]

    // Get Current Moodle Turnitin Tool Object (Assignment) [[[
    if (!$turnitintoolnow = turnitintool_get_record("turnitintool", "id", $turnitintool->id)) {
        turnitintool_print_error('turnitintoolgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }
    // ]]]]

    // Get Current Moodle Turnitin Tool Parts Object [[[
    if (is_callable(array($DB,'sql_compare_text'))) {
        $part_select_query = $DB->sql_compare_text('turnitintoolid').' = :turnitintoolid
        AND '.$DB->sql_compare_text('deleted').' = :deleted';
        if (!$parts = turnitintool_get_records_select("turnitintool_parts", $part_select_query,array('turnitintoolid' => $turnitintool->id, 'deleted' => 0),'id DESC')) {
            turnitintool_print_error('partgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }
    } else {
        $part_select_query = "turnitintoolid='".$turnitintool->id."' AND deleted=0";
        if (!$parts = turnitintool_get_records_select("turnitintool_parts", $part_select_query,'id DESC')) {
            turnitintool_print_error('partgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }
    }

    // ]]]]

    $partids=array_keys($parts);

    $proc=0;
    $total=$turnitintool->numparts+2;
    if (count($partids)>$turnitintool->numparts) {
        // Add the number of deletes needed
        $total+=count($partids)-$turnitintool->numparts;
    }
    if ($turnitintoolnow->numparts<$turnitintool->numparts) {
        // Add the number of Insert Individualising required
        $total+=$turnitintool->numparts-count($partids);
    }

    if (turnitintool_is_owner($course->id)) {
        $owner=$USER;
    } else {
        $owner=turnitintool_get_owner($course->id);
    }

    $loaderbar = null;
    $tii = new turnitintool_commclass(turnitintool_getUID($owner),$owner->firstname,$owner->lastname,$owner->email,2,$loaderbar);
    $tii->startSession();

    if ($turnitintool->numparts<count($partids) AND turnitintool_count_records('turnitintool_submissions','turnitintoolid',$turnitintool->id)>0) {
        turnitintool_print_error('reducepartserror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    } else if ($turnitintool->numparts<count($partids)) {                       // REDUCE THE NUMBER OF PARTS BY CHOPPING THE PARTS OFF THE END

        for ($i=0;$i<count($partids);$i++) {
            $n=$i+1;
            if ($n>$turnitintool->numparts) {
                $proc++;

                // Get the Turnitin UIDs [[[[
                $tiipost = new stdClass();
                $tiipost->cid=turnitintool_getCID($course->id);
                $tiipost->assignid=turnitintool_getAID($partids[$i]);
                // ]]]]

                // Do the turnitin assignment set call to the API [[[[
                $tiipost->courseid=$course->id;
                $tiipost->ctl=turnitintool_getCTL($course->id);
                $tiipost->dtstart=time();
                $tiipost->dtdue=strtotime('+1 week');
                $tiipost->dtpost=strtotime('+1 week');
                $tiipost->name=$turnitintool->name.' - '.turnitintool_partnamefromnum($partids[$i]).' (Moodle '.$tiipost->assignid.')';

                $tii->deleteAssignment($tiipost,get_string('assignmentdeleteprocess','turnitintool',$n));

                if ($tii->getRerror() AND $tii->getRcode()!=TURNITINTOOL_ASSIGNMENT_UPDATE_ERROR AND $tii->getRcode()!=TURNITINTOOL_ASSIGNMENT_NOTEXIST_ERROR) {
                    if (!$tii->getAPIunavailable()) {
                        $reason=($tii->getRcode()==TURNITINTOOL_DB_UNIQUEID_ERROR) ? get_string('assignmentdoesnotexist','turnitintool') : $tii->getRmessage();
                    } else {
                        $reason=get_string('apiunavailable','turnitintool');
                    }
                    turnitintool_print_error('<strong>'.get_string('deleteerror','turnitintool').'</strong><br />'.$reason);
                    exit();
                } else {
                    if (!$submissions=turnitintool_delete_records('turnitintool_submissions','turnitintoolid',$turnitintool->id,'submission_part',$partids[$i])) {
                        turnitintool_print_error('submissiondeleteerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                        exit();
                    }
                }

                $part = new stdClass();
                $part->id=$partids[$i];
                $part->partname=turnitintool_partnamefromnum($part->id);
                $part->deleted=1;
                if (!$delete=turnitintool_update_record('turnitintool_parts',$part,false)) {
                    turnitintool_print_error('partdberror','turnitintool',NULL,$i,__FILE__,__LINE__);
                }

                if (is_callable(array($DB,'sql_compare_text'))) {
                    $event_delete_query = $DB->sql_compare_text('modulename').'=:turnitintool
                    AND '.$DB->sql_compare_text('instance').'=:instance
                    AND '.$DB->sql_compare_text('name').'=:name';
                } else {
                    $event_delete_query = 'modulename=:turnitintool
                    AND instance=:instance
                    AND name=:name';
                }

                turnitintool_delete_records_select('event', $event_delete_query, array('turnitintool' => 'turnitintool', 'instance' => $turnitintool->id, 'name' => $turnitintool->name." - ".$part->partname));
                // ]]]]
            }
        }
    }
    unset($tiipost);

    for ($i=0;$i<$turnitintool->numparts;$i++) {
        $n=$i+1;
        $proc++;

        // Update Turnitin Assignment via the API [[[[

        $tiipost = new stdClass();
        $tiipost->courseid=$course->id;
        $tiipost->ctl=turnitintool_getCTL($course->id);

        $thisid = isset($partids[$i]) ? $partids[$i] : null;

        if ($i>=count($partids)) {
            $tiipost->dtstart=time();       // Now
            $tiipost->dtdue=strtotime('+1 week');   // 7 days time
            $tiipost->dtpost=strtotime('+1 week');  // 7 days time
        } else {
            $tiipost->dtstart=$parts[$thisid]->dtstart;
            $tiipost->dtdue=$parts[$thisid]->dtdue;
            $tiipost->dtpost=$parts[$thisid]->dtpost;
        }

        $tiipost->s_view_report=$turnitintool->studentreports;
        $tiipost->anon=$turnitintool->anon;
        $tiipost->report_gen_speed=$turnitintool->reportgenspeed;
        $tiipost->late_accept_flag=$turnitintool->allowlate;
        $tiipost->submit_papers_to=$turnitintool->submitpapersto;
        $tiipost->s_paper_check=$turnitintool->spapercheck;
        $tiipost->internet_check=$turnitintool->internetcheck;
        $tiipost->journal_check=$turnitintool->journalcheck;

        // Add in Exclude small matches, biblio, quoted etc 20102009
        $tiipost->exclude_biblio=$turnitintool->excludebiblio;
        $tiipost->exclude_quoted=$turnitintool->excludequoted;
        $tiipost->exclude_value=$turnitintool->excludevalue;
        $tiipost->exclude_type=$turnitintool->excludetype;

        // Add erater settings
        $tiipost->erater=(isset($turnitintool->erater)) ? $turnitintool->erater : 0;
        $tiipost->erater_handbook=(isset($turnitintool->erater_handbook)) ? $turnitintool->erater_handbook : 0;
        $tiipost->erater_dictionary=(isset($turnitintool->erater_dictionary)) ? $turnitintool->erater_dictionary : 'en_US';
        $tiipost->erater_spelling=(isset($turnitintool->erater_spelling)) ? $turnitintool->erater_spelling : 0;
        $tiipost->erater_grammar=(isset($turnitintool->erater_grammar)) ? $turnitintool->erater_grammar : 0;
        $tiipost->erater_usage=(isset($turnitintool->erater_usage)) ? $turnitintool->erater_usage : 0;
        $tiipost->erater_mechanics=(isset($turnitintool->erater_mechanics)) ? $turnitintool->erater_mechanics : 0;
        $tiipost->erater_style=(isset($turnitintool->erater_style)) ? $turnitintool->erater_style : 0;
        $tiipost->transmatch=(isset($turnitintool->transmatch)) ? $turnitintool->transmatch : 0;

        if (turnitintool_is_owner($course->id)) {
            $owner=$USER;
        } else {
            $owner=turnitintool_get_owner($course->id);
        }

        if ($i<count($partids)) {
            $individualise=false;
            $partname=turnitintool_partnamefromnum($partids[$i]);

            $tiipost->cid=turnitintool_getCID($course->id);
            $tiipost->assignid=turnitintool_getAID($partids[$i]);

            $tiipost->name=$turnitintool->name.' - '.$partname.' (Moodle '.$tiipost->assignid.')';
            $tiipost->currentassign=$turnitintoolnow->name.' - '.turnitintool_partnamefromnum($partids[$i]).' (Moodle '.$tiipost->assignid.')';

            $tii->createAssignment($tiipost,'UPDATE',get_string('assignmentupdate','turnitintool',$n));
        } else {
            $individualise=true;
            $tiipost->cid='';
            $tiipost->assignid='';
            $tiipost->dtstart=strtotime("now"); // Set time to now and change to the correct date later to allow dates in the past
            $tiipost->dtdue=strtotime("+1 day"); // Set time to now +1 day and change to the correct date later to allow dates in the past
            $tiipost->dtpost=strtotime("+1 day"); // Set time to now +1 day and change to the correct date later to allow dates in the past
            $partname=get_string('turnitinpart','turnitintool',$n);
            $tiipost->name=$turnitintool->name.' - '.$partname;

            $tii->createAssignment($tiipost,'INSERT',get_string('assignmentprocess','turnitintool',$n));
        }

        if ($tii->getRerror()) {
            if ($tii->getAPIunavailable()) {
                $reason=get_string('apiunavailable','turnitintool');
            } else {
                $reason=($tii->getRcode()==TURNITINTOOL_DB_UNIQUEID_ERROR) ? get_string('assignmentdoesnotexist','turnitintool') : $tii->getRmessage();
            }
            turnitintool_print_error('<strong>'.get_string('updateerror','turnitintool').'</strong><br />'.$reason);
            exit();
        }

        $part = new stdClass();
        $part->tiiassignid=$tii->getAssignid();

        if ($individualise) {

            $tiipost->cid=turnitintool_getCID($course->id);
            $tiipost->assignid=$part->tiiassignid;
            $tiipost->currentassign=$tiipost->name;
            $tiipost->name.=' (Moodle '.$part->tiiassignid.')';
            $tiipost->max_points=($turnitintool->grade < 0) ? 100 : $turnitintool->grade;

            // Now individualise the Assignment Name and allow to set the date to any date even dates in the past for new assignment part

            $proc++;
            $tii->createAssignment($tiipost,'UPDATE',get_string('assignmentindividualise','turnitintool',$n)); // PROC 3+

            if ($tii->getRerror()) {
                $reason=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                turnitintool_print_error('<strong>'.get_string('inserterror','turnitintool').'</strong><br />'.$reason);
                exit();
            }

        }

        $part->turnitintoolid=$turnitintool->id;
        $part->partname=$partname;
        $part->deleted=0;

        if ($i>=count($partids)) {
            $part->dtstart=time();
            $part->dtdue=strtotime('+1 week');
            $part->dtpost=strtotime('+1 week');
        } else {
            $part->dtdue=$parts[$thisid]->dtdue;
            $part->dtpost=$parts[$thisid]->dtpost;
        }

        $part->dtstart=$tiipost->dtstart;
        $part->dtdue=$tiipost->dtdue;
        $part->dtpost=$tiipost->dtpost;

        $event = new object();
        $event->name        = $turnitintool->name.' - '.$part->partname;
        $event->description = $turnitintool->intro;
        $event->courseid    = $turnitintool->course;
        $event->groupid     = 0;
        $event->userid      = 0;
        $event->modulename  = 'turnitintool';
        $event->instance    = $turnitintool->id;
        $event->eventtype   = 'due';
        $event->timestart   = $part->dtdue;
        $event->timeduration = 0;

        if ($i<count($partids)) {
            $part->id=$partids[$i];
            if (!$dbpart=turnitintool_update_record('turnitintool_parts',$part,false)) {
                turnitintool_print_error('partdberror','turnitintool',NULL,$i,__FILE__,__LINE__);
                exit();
            }
        } else {
            $part->maxmarks=($turnitintool->grade < 0) ? 100 : $turnitintool->grade;
            if (!$dbpart=turnitintool_insert_record('turnitintool_parts',$part,false)) {
                turnitintool_print_error('partdberror','turnitintool',NULL,$i,__FILE__,__LINE__);
                exit();
            }
        }

        // Delete existing events for this assignment / part
        // turnitintool_delete_records('turnitintool_submissions','turnitintoolid',$turnitintool->id,'submission_part',$part->id)
        $name = $turnitintoolnow->name.' - '.$part->partname;

        // $DB is not available for Moodle 1.9
        if (is_callable(array($DB,'sql_compare_text'))) {
            $deletewhere = 'modulename = :modulename
            AND '.$DB->sql_compare_text('instance').' = :id
            AND '.$DB->sql_compare_text('name').' = :name';
        } else {
            $deletewhere = 'modulename = \'turnitintool\'
            AND instance = \''.$turnitintool->id.'\'
            AND name = \''.$name.'\'';
        }
        turnitintool_delete_records_select('event', $deletewhere, array('modulename' => 'turnitintool', 'id' => $turnitintool->id, 'name' => $name));

        add_event($event);

        unset($tiipost);

    }

    // ]]]]

    $turnitintool->timemodified=time();
    $update=turnitintool_update_record("turnitintool", $turnitintool);

    // Define grade settings in Moodle 1.9 and above
    turnitintool_grade_item_update( $turnitintool );
    $cm=get_coursemodule_from_instance("turnitintool", $turnitintool->id, $turnitintool->course);
    add_to_log($turnitintool->course, "turnitintool", "update turnitintool", 'view.php?id='.$cm->id, "Assignment updated '$turnitintool->name'", $cm->id);

    $tii->endSession();

    return $update;
}

/**
 * Create grade item for given assignment
 *
 * @param object $turnitintool object with extra cmidnumber (if available)
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */

function turnitintool_grade_item_update( $turnitintool, $grades=null ) {
    global $CFG;
    @include_once($CFG->dirroot."/lib/gradelib.php");
    if ( function_exists( 'grade_update' ) ) {
        $params = array();
        $cm=get_coursemodule_from_instance("turnitintool", $turnitintool->id, $turnitintool->course);
        $params['itemname'] = $turnitintool->name;
        $params['idnumber'] = isset( $cm->idnumber ) ? $cm->idnumber : null;

        if ($turnitintool->grade < 0) { // If we're using a grade scale
            $params['gradetype'] = GRADE_TYPE_SCALE;
            $params['scaleid']   = -$turnitintool->grade;
        } else if ($turnitintool->grade > 0) { // If we are using a grade value
            $params['gradetype'] = GRADE_TYPE_VALUE;
            $params['grademax']  = $turnitintool->grade;
            $params['grademin']  = 0;
        } else { // If we aren't using a grade at all
            $params['gradetype'] = GRADE_TYPE_NONE;
        }

        $lastpart=turnitintool_get_record('turnitintool_parts','turnitintoolid',$turnitintool->id,'','','','','max(dtpost)');
        $lastpart=current($lastpart);
        $params['hidden']=$lastpart;

        $params['grademin']  = 0;
        return grade_update('mod/turnitintool', $turnitintool->course, 'mod', 'turnitintool', $turnitintool->id, 0, $grades, $params);
    }
    return;
}

/**
 * Not used but needed for instance name update via quick update name on the course home page
 *
 * @param  stdClass  $turnitintool
 * @param  integer $userid
 * @param  boolean $nullifnone
 */
function turnitintool_update_grades($turnitintool, $userid=0, $nullifnone=true) {

}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @global object
 * @param int $id turnitintool instance id
 * @return bool success
 */
function turnitintool_delete_instance($id) {
    global $USER,$CFG;

    $result = true;

    // Get the Moodle Turnitintool (Assignment) and Course Object [[[[
    if (!$turnitintool = turnitintool_get_record("turnitintool", "id", $id)) {
        return false;
    }
    if (!$course = turnitintool_get_record("course", "id", $turnitintool->course)) {
        return false;
    }
    // ]]]]

    // Get Current Moodle Turnitin Tool Parts Object [[[
    $parts = turnitintool_get_records("turnitintool_parts", "turnitintoolid", $turnitintool->id);
    // ]]]]

    $partids=array_keys($parts);
    $total=count($partids)+2;
    $proc=0;

    foreach ($parts as $part) {
        $proc++;

        if (!$submissions=turnitintool_delete_records('turnitintool_submissions','turnitintoolid',$turnitintool->id,'submission_part',$part->id)) {
            $result=false;
        }
        // ]]]]

        # Delete any dependent records here #

        if (!turnitintool_delete_records("turnitintool_parts", "id", $part->id)) {
            $result = false;
        }

    }

    $cm = get_coursemodule_from_instance( "turnitintool", $turnitintool->id, $turnitintool->course );
    add_to_log( $turnitintool->course, "turnitintool", "delete turnitintool", 'view.php?id='.$cm->id, 'Assignment deleted "'.$turnitintool->name.'"', $cm->id );

    // Delete events for this assignment / part
    turnitintool_delete_records('event', 'modulename','turnitintool','instance',$turnitintool->id);

    if (!turnitintool_delete_records("turnitintool", "id", $turnitintool->id)) {
        $result = false;
    }

    if ($oldcourses=turnitintool_get_records("turnitintool_courses")) {
        foreach ($oldcourses as $oldcourse) { // General Clean Up
            if (!turnitintool_count_records("course","id",$oldcourse->courseid)>0) {
                // Delete the Turnitin Classes data if the Moodle courses no longer exists
                turnitintool_delete_records("turnitintool_courses","courseid",$oldcourse->courseid);
            }
            if (!turnitintool_count_records("turnitintool","course",$oldcourse->courseid)>0) {
                // Delete the Turnitin Class data if no more turnitin assignments exist in it
                turnitintool_delete_records("turnitintool_courses","courseid",$oldcourse->courseid);
            }
        }
    }

    // Define grade settings in Moodle 1.9 and above

    @include_once($CFG->dirroot."/lib/gradelib.php");
    if (function_exists('grade_update')) {
        $params['deleted'] = 1;
        grade_update('mod/turnitintool', $turnitintool->course, 'mod', 'turnitintool', $turnitintool->id, 0, NULL, $params);
    }

    return $result;
}

/**
 * This is a standard Moodle module that checks to make sure there are events for each activity
 *
 * @param var $courseid The ID of the course this activity belongs to (default 0 for all courses)
 * @return bool success
 */
function turnitintool_refresh_events($courseid=0) {

    if ($courseid == 0) {
        if (!$turnitintools = turnitintool_get_records("turnitintool")) {
            $result=true;
        }
    } else {
        if (!$turnitintools = turnitintool_get_records("turnitintool", "course",$courseid)) {
            $result=true;
        }
    }

    $module = turnitintool_get_records_select('modules',"name='turnitintool'",NULL,'id');
    $moduleid=current(array_keys($module));

    foreach ($turnitintools as $turnitintool) {
        $event = new stdClass();
        $event->description = $turnitintool->intro;
        if (!$parts = turnitintool_get_records("turnitintool_parts","turnitintoolid",$turnitintool->id)) {
            $result=false;
        }
        foreach ($parts as $part) {
            $event->timestart=$part->dtdue;

            if ($events = turnitintool_get_record_select('event', "modulename='turnitintool' AND instance=".$turnitintool->id." AND name='".$turnitintool->name." - ".$part->partname."'")) {
                $event->id = $events->id;
                update_event($event);
            } else {
                $event->courseid    = $turnitintool->course;
                $event->groupid     = 0;
                $event->userid      = 0;
                $event->modulename  = 'turnitintool';
                $event->instance    = $turnitintool->id;
                $event->eventtype   = 'due';
                $event->timeduration = 0;
                $event->name        = $turnitintool->name.' - '.$part->partname;

                $coursemodule = turnitintool_get_record('course_modules','module',$moduleid,'instance',$turnitintool->id);
                $event->visible = $coursemodule->visible;
                add_event($event);
            }
        }
        $result=true;

    }

    return $result;

}

/**
 * This is a standard Moodle module that prints out a summary of all activities
 * of this kind in the My Moodle page for a user
 *
 * @param object $courses
 * @param object $htmlarray
 * @return bool success
 */
function turnitintool_print_overview($courses, &$htmlarray) {
    global $USER, $CFG, $DB;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if (!$turnitintools=get_all_instances_in_courses('turnitintool',$courses)) {
        return;
    }

    $ids = array();

    $tiidata=array();
    foreach ($turnitintools as $key => $turnitintool) {
        $now = time();
        $parts=turnitintool_get_records_select('turnitintool_parts','turnitintoolid='.$turnitintool->id.' AND deleted=0',NULL,'id');
        $context = get_context_instance(CONTEXT_MODULE, $turnitintool->coursemodule);

        // Get Early and Late Date Boundries for each part of this assignment
        $earlydate=0;
        $latedate=0;
        $partsarray=array();
        foreach ($parts as $part) {
            $earlydate = ($part->dtstart < $earlydate OR $earlydate==0) ? $part->dtstart : $earlydate;
            $latedate = ($part->dtpost > $latedate) ? $part->dtpost : $latedate;

            $partsarray[$part->id]['name']=$part->partname;
            $partsarray[$part->id]['dtdue']=$part->dtdue;

            if (has_capability('mod/turnitintool:grade', $context)) { // If user is a grader
                $subquery=turnitintool_get_records_select('turnitintool_submissions','turnitintoolid='.$turnitintool->id.
                        ' AND submission_part='.$part->id.' AND submission_objectid IS NOT NULL AND userid!=0',NULL,'','count(userid)');
                $numsubmissions=key($subquery);

                $gradequery=turnitintool_get_records_select('turnitintool_submissions','turnitintoolid='.$turnitintool->id.
                        ' AND submission_part='.$part->id.' AND userid!=0 AND submission_grade IS NOT NULL',NULL,'','count(userid)');
                $numgrades=key($gradequery);

                $allusers=get_users_by_capability($context, 'mod/turnitintool:submit', 'u.id', '', '', '', 0, '', false);
                $input = new stdClass();
                $input->submitted=$numsubmissions;
                $input->graded=$numgrades;
                $input->total=count($allusers);
                $input->gplural=($numgrades!=1) ? 's' : '';
                $partsarray[$part->id]['status']=get_string('tutorstatus','turnitintool',$input);
            } else { // If user is a student
                if ($submission=turnitintool_get_record_select('turnitintool_submissions','turnitintoolid='.$turnitintool->id.
                ' AND submission_part='.$part->id.' AND userid='.$USER->id.' AND submission_objectid IS NOT NULL')) {

                    $input = new stdClass();
                    $input->modified=userdate($submission->submission_modified,get_string('strftimedatetimeshort','langconfig'));
                    $input->objectid=$submission->submission_objectid;
                    $partsarray[$part->id]['status']=get_string('studentstatus','turnitintool',$input);

                } else {
                    $partsarray[$part->id]['status']=get_string('nosubmissions','turnitintool');
                }
            }
        }

        if ($earlydate <= $now AND $latedate >= $now) { // Turnitin Assignment Is Active for this user

            $str = '<div class="turnitintool overview"><div class="name">'.get_string('modulename','turnitintool'). ': '.
                    '<a '.($turnitintool->visible ? '':' class="dimmed"').
                    'title="'.get_string('modulename','turnitintool').'" href="'.$CFG->wwwroot.
                    '/mod/turnitintool/view.php?id='.$turnitintool->coursemodule.'">'.
                    $turnitintool->name.'</a></div>';

            foreach ($partsarray as $thispart) {
                $str .= '<div class="info"><b>'.$thispart['name'].' - '.get_string('dtdue','turnitintool').': '.userdate($thispart['dtdue'],get_string('strftimedatetimeshort','langconfig'),$USER->timezone).'</b><br />
                        <i>'.$thispart['status'].'</i></div>';
            }

            $str .= '</div>';

            if (empty($htmlarray[$turnitintool->course]['turnitintool'])) {
                $htmlarray[$turnitintool->course]['turnitintool'] = $str;
            } else {
                $htmlarray[$turnitintool->course]['turnitintool'] .= $str;
            }

        }

    }

}

/**
 * A function to return a Turnitin User ID if one exists in turnitintool_users
 * or returns NULL if we do not have a record for that user yet
 *
 * @param object $owner A data object for the owner user of the Turnitin Class
 * @return var A Turnitin User ID or NULL
 */
function turnitintool_getUID($owner) {
    if (is_null($owner) OR !$turnitintool_user = turnitintool_get_record("turnitintool_users", "userid", $owner->id)) {
        return NULL;
    } else {
        return ( isset($turnitintool_user->turnitin_uid) AND $turnitintool_user->turnitin_uid > 0 )
                ? $turnitintool_user->turnitin_uid : NULL;
    }
}

/**
 * A function to return a Turnitin Class ID if one exists in turnitintool_courses
 * or fails with an error if the record does not exist
 *
 * @param var $courseid The ID of the Moodle course to check against
 * @return var A Turnitin Class ID
 */
function turnitintool_getCID($courseid) {
    if (!$turnitintool_course = turnitintool_get_record("turnitintool_courses", "courseid", $courseid)) {
        turnitintool_print_error('coursegeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    } else {
        return $turnitintool_course->turnitin_cid;
    }
}

/**
 * A function to return the Turnitin Class Title if one exists in turnitintool_courses
 * or fails with an error if the record does not exist
 *
 * @param var $courseid The ID of the Moodle course to check against
 * @return var A Turnitin Class Title
 */
function turnitintool_getCTL($courseid) {
    if (!$turnitintool_course = turnitintool_get_record("turnitintool_courses", "courseid", $courseid)) {
        turnitintool_print_error('coursegeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    } else {
        return $turnitintool_course->turnitin_ctl;
    }
}

/**
 * A function to return the Turnitin Assignment ID if one exists in turnitintool
 * or fails with an error if the record does not exist
 *
 * @param var $courseid The ID of the Moodle assignment to check against
 * @return var A Turnitin Assignment ID
 */
function turnitintool_getAID($partid) {
    if (!$turnitintool_assign = turnitintool_get_record("turnitintool_parts", "id", $partid)) {
        turnitintool_print_error('assigngeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    } else {
        return $turnitintool_assign->tiiassignid;
    }
}

/**
 * Does a user setup up routine, if the user exists in turnitintool_users then the Turnitin User ID is returned for that user
 * if not then A call to FID1 to Create the User is called and the ID is returned and stored in turnitintool_users
 *
 * This is required when user not present calls are made or when there may not yet be a class owner to make the call through the API
 * specifically through reset course and add_instance
 *
 * @param object $userdata The moodle user object for the user we are setting up
 * @param string $status The status message that is displayed in the loader bar if the user need to be created
 * @param object $tii The turnitintool_commclass object is passed by reference
 * @param object $loaderbar The turnitintool_loaderbarclass object is passed by reference can be NULL if no loader bar is to be used
 * @return object A turnitintool_users data object that was either created or found during this call
 */
function turnitintool_usersetup($userdata,$status='',&$tii,&$loaderbar) {
    global $CFG;

    if (!turnitintool_check_config()) {
        turnitintool_print_error('configureerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    if (!$turnitintool_user = turnitintool_get_record("turnitintool_users", "userid", $userdata->id)
            // If the user has been unlinked
            OR (isset($turnitintool_user->turnitin_uid) AND $turnitintool_user->turnitin_uid < 1)) {

        if (isset($loaderbar->total)) {
            $loaderbar->total=$loaderbar->total+3;
        }

        // Do call with NO IDs to check the email address and retrieve the Turnitin UID

        $post = new stdClass();
        $post->idsync=1;
        if ($tii->utp==1 AND $CFG->turnitin_studentemail!="1") {
            $post->dis=1;
        } else {
            $post->dis=0;
        }
        $tii->createUser($post,$status);

        if ($tii->getRerror()) {
            return null;
        }

        $turnitinid=$tii->getUserID();
        $turnitinuser=new object();

        if ( isset( $turnitintool_user->id ) AND $turnitintool_user->id ) {
            $turnitinuser->id = $turnitintool_user->id;
        }

        $turnitinuser->userid=$userdata->id;
        $turnitinuser->turnitin_uid=$turnitinid;
        $turnitinuser->turnitin_utp=$tii->utp;
        $tii->uid=$turnitinid;

        if ( ( isset( $turnitinuser->id ) AND !turnitintool_update_record('turnitintool_users',$turnitinuser) ) ) {
            turnitintool_print_error('userupdateerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        } else if ( !isset( $turnitinuser->id ) AND !$insertid=turnitintool_insert_record('turnitintool_users',$turnitinuser) ) {
            turnitintool_print_error('userupdateerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }
        $turnitinuser->id=( isset($insertid) ) ? $insertid : $turnitinuser->id;

        return $turnitinuser;

    } else {
        return $turnitintool_user;
    }

}

/**
 * Does a class setup up routine, if the class exists in turnitintool_class then the Turnitin Class ID is returned for that class
 * if not then A call to FID2 to Create the Class is called and the ID is returned and stored in turnitintool_courses
 *
 * @param object $course The moodle course object for the course we are setting up
 * @param object $owner The moodle user object for the turnitin class owner of the course
 * @param string $status The status message that is displayed in the loader bar if the class needs to be created
 * @param object $tii The turnitintool_commclass object is passed by reference
 * @param object $loaderbar The turnitintool_loaderbarclass object is passed by reference can be NULL if no loader bar is to be used
 * @return object A turnitintool_courses data object that was either created or found during this call
 */
function turnitintool_classsetup($course,$owner,$status='',&$tii,&$loaderbar) {

    // Make sure the Turnitin Module has been fully configured
    if (!turnitintool_check_config()) {
        turnitintool_print_error('configureerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    // Check to see if we have an ID and Title stored for this course
    if (!$turnitintool_course = turnitintool_get_record("turnitintool_courses", "courseid", $course->id)) {
        $classexists=false;
    } else {
        $classexists=true;
    }

    // We do not have an ID stored
    // Action: Create the class on turnitin and store relevant details
    if (!$classexists) {

        // Create a Turnitin Comm Object
        if (isset($loaderbar->total)) {
            $loaderbar->total=$loaderbar->total+1;
        }

        // Create without IDs initially
        $uniquestring=strtoupper(uniqid());
        $turnitin_ctl=(strlen($course->fullname) > 76)
                ? substr($course->fullname,0,76)."... (".$uniquestring.")" : $course->fullname." (".$uniquestring.")";

        $post = new stdClass();
        $post->ctl=$turnitin_ctl;
        $post->idsync=1;
        $tii->createClass($post,$status);

        if ($tii->getRerror()) {
            if ($tii->getAPIunavailable()) {
                turnitintool_print_error('apiunavailable','turnitintool',NULL,NULL,__FILE__,__LINE__);
            } else {
                turnitintool_print_error($tii->getRmessage(),NULL,NULL,NULL,__FILE__,__LINE__);
            }
            exit();
        }

        $post->cid=$tii->getClassID();
        $turnitin_ctl=str_replace("(".$uniquestring.")","(Moodle ".$post->cid.")",$turnitin_ctl);
        $turnitincourse=new object();
        $turnitincourse->courseid=$course->id;
        $turnitincourse->ownerid=$owner->id;
        $turnitincourse->turnitin_cid=$post->cid;
        $turnitincourse->turnitin_ctl=$turnitin_ctl;

        if (!$insertid=turnitintool_insert_record('turnitintool_courses',$turnitincourse)) {
            turnitintool_print_error('classupdateerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }
        $turnitincourse->id=$insertid;
    }
    // We already have an ID stored
    // Action: Do a call to Turnitin with the Stored IDs to ensure the class has not changed

    if (isset($insertid) AND !$turnitintool_course = turnitintool_get_record("turnitintool_courses", "courseid", $course->id)) {
        turnitintool_print_error('classgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    // Create a Turnitin Comm Object
    if (isset($loaderbar->total)) {
        $loaderbar->total=$loaderbar->total+2;
    }

    $post = new stdClass();
    $post->cid=$turnitintool_course->turnitin_cid;
    $post->ctl=$turnitintool_course->turnitin_ctl;
    $post->idsync=0;

    $tii->createClass($post,$status);

    if ($tii->getRerror()) {
        if ($tii->getAPIunavailable()) {
            turnitintool_print_error('apiunavailable','turnitintool',NULL,NULL,__FILE__,__LINE__);
        } else {
            turnitintool_print_error($tii->getRmessage(),NULL,NULL,NULL,__FILE__,__LINE__);
        }
        exit();
    }

    return $turnitintool_course;
}


/**
 * Prints the tab link menu across the top of the activity module
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $do The query string parameter to determine the page we are on
 */
function turnitintool_draw_menu($cm,$do) {
    global $CFG,$USER;
    $tabs[] = new tabobject('intro', $CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.
                    '&do=intro', get_string('turnitintoolintro','turnitintool'), get_string('turnitintoolintro','turnitintool'), false);

    if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {

        $tabs[] = new tabobject('submissions', $CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=submissions',
                get_string('submitpaper','turnitintool'), get_string('submitpaper','turnitintool'), false);

        $tabs[] = new tabobject('allsubmissions', $CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=allsubmissions',
                get_string('allsubmissions','turnitintool'), get_string('allsubmissions','turnitintool'), false);

        $tabs[] = new tabobject('options', $CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=options',
                get_string('options','turnitintool'), get_string('options','turnitintool'), false);

    } else {
        $tabs[] = new tabobject('submissions', $CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=submissions',
                get_string('mysubmissions','turnitintool'), get_string('mysubmissions','turnitintool'), false);
    }
    echo '<div class="clearfix"></div>';
    if ($do=='notes') {
        $tabs[] = new tabobject('notes', '',
                get_string('notes','turnitintool'), get_string('notes','turnitintool'), false);
        $inactive=array('notes');
        $selected='notes';
    } else if ($do=='tutors') {
        $tabs[] = new tabobject('tutors', '',
                get_string('turnitintutors','turnitintool'), get_string('turnitintutors','turnitintool'), false);
        $inactive=array('tutors');
        $selected='tutors';
    } else {
        $inactive=array();
        $selected=$do;
    }

    print_tabs(array($tabs),$selected,$inactive);
}

/**
 * Processes the data passed by the part update form from the summary page
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object is for this activity
 * @param array $post The post array from the part update form
 * @return array A notice array contains error details for display on page load in the case of an error nothing returned if no errors occur
 */
function turnitintool_update_partnames($cm,$turnitintool,$post) {
    global $CFG,$USER;
    if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        $notice['message']='';
        $error=false;
        $dtstart=make_timestamp(
                    $post["dtstart"]["year"],
                    $post["dtstart"]["month"],
                    $post["dtstart"]["day"],
                    $post["dtstart"]["hour"],
                    $post["dtstart"]["minute"],
                    0,
                    get_user_timezone()
                 );
        $dtdue=make_timestamp(
                    $post["dtdue"]["year"],
                    $post["dtdue"]["month"],
                    $post["dtdue"]["day"],
                    $post["dtdue"]["hour"],
                    $post["dtdue"]["minute"],
                    0,
                    get_user_timezone()
                 );
        $dtpost=make_timestamp(
                    $post["dtpost"]["year"],
                    $post["dtpost"]["month"],
                    $post["dtpost"]["day"],
                    $post["dtpost"]["hour"],
                    $post["dtpost"]["minute"],
                    0,
                    get_user_timezone()
                 );

        if ($dtstart>=$dtdue) {
            $notice['message'].=get_string('partstarterror','turnitintool');
            $error=true;
        }
        if ($dtpost<$dtstart) {
            $notice['message'].=get_string('partdueerror','turnitintool');
            $error=true;
        }
        if (empty($post['partname'])) {
            $notice['message'].=get_string('partnameerror','turnitintool');
            $error=true;
        }
        if (strlen($post['partname'])>35) {
            $input = new stdClass();
            $input->length=35;
            $input->field=get_string('partname','turnitintool');
            $notice['message'].=get_string('maxlength','turnitintool',$input);
            $error=true;
        }
        if (!preg_match("/^[-]?[0-9]+([\.][0-9]+)?$/", $post['maxmarks'])) { // ENTRY IS NOT A NUMBER
            $notice['message'].=get_string('partmarkserror','turnitintool');
            $error=true;
        }
        if ($error) {
            $notice['error']=$post['submitted'];
            $notice['post']=$post;
        }
        if (!$error) {

            // Update Turnitin Assignment via the API [[[[
            $tiipost = new stdClass();
            $tiipost->ctl=turnitintool_getCTL($turnitintool->course);
            $tiipost->dtstart=$dtstart;
            $tiipost->dtdue=$dtdue;
            $tiipost->dtpost=$dtpost;

            if (turnitintool_is_owner($turnitintool->course)) {
                $owner=$USER;
            } else {
                $owner=turnitintool_get_owner($turnitintool->course);
            }
            if (!$course=turnitintool_get_record('course','id',$turnitintool->course)) {
                turnitintool_print_error('coursegeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }

            $loaderbar = new turnitintool_loaderbarclass(3);
            $tii = new turnitintool_commclass(turnitintool_getUID($owner),$owner->firstname,$owner->lastname,$owner->email,2,$loaderbar);
            $tii->startSession();
            turnitintool_usersetup($owner,get_string('userprocess','turnitintool'),$tii,$loaderbar);
            turnitintool_classsetup( $course, $owner, get_string('classprocess','turnitintool'), $tii, $loaderbar );
            if ($tii->getRerror()) {
                if ($tii->getAPIunavailable()) {
                    turnitintool_print_error('apiunavailable','turnitintool',NULL,NULL,__FILE__,__LINE__);
                } else {
                    turnitintool_print_error($tii->getRmessage(),NULL,NULL,NULL,__FILE__,__LINE__);
                }
                exit();
            }

            $tiipost->cid=turnitintool_getCID($turnitintool->course);
            $tiipost->assignid=turnitintool_getAID($post['submitted']);
            $tiipost->s_view_report=$turnitintool->studentreports;
            $tiipost->max_points=$post['maxmarks'];

            $tiipost->anon=$turnitintool->anon;
            $tiipost->report_gen_speed=$turnitintool->reportgenspeed;
            $tiipost->late_accept_flag=$turnitintool->allowlate;
            $tiipost->submit_papers_to=$turnitintool->submitpapersto;
            $tiipost->s_paper_check=$turnitintool->spapercheck;
            $tiipost->internet_check=$turnitintool->internetcheck;
            $tiipost->journal_check=$turnitintool->journalcheck;

            // Add in Exclude small matches, biblio, quoted etc 20102009
            $tiipost->exclude_biblio=$turnitintool->excludebiblio;
            $tiipost->exclude_quoted=$turnitintool->excludequoted;
            $tiipost->exclude_value=$turnitintool->excludevalue;
            $tiipost->exclude_type=$turnitintool->excludetype;

            // Add in the erater settings
            $tiipost->erater=$turnitintool->erater;
            $tiipost->erater_handbook=$turnitintool->erater_handbook;
            $tiipost->erater_dictionary=$turnitintool->erater_dictionary;
            $tiipost->erater_spelling=$turnitintool->erater_spelling;
            $tiipost->erater_grammar=$turnitintool->erater_grammar;
            $tiipost->erater_usage=$turnitintool->erater_usage;
            $tiipost->erater_mechanics=$turnitintool->erater_mechanics;
            $tiipost->erater_style=$turnitintool->erater_style;
            $tiipost->transmatch=$turnitintool->transmatch;

            $tiipost->name=$turnitintool->name.' - '.$post['partname'].' (Moodle '.$tiipost->assignid.')';
            $tiipost->currentassign=$turnitintool->name.' - '.turnitintool_partnamefromnum($post['submitted']).' (Moodle '.$tiipost->assignid.')';

            $tii->createAssignment($tiipost,'UPDATE',get_string('assignmentupdate','turnitintool',''));

            if ($tii->getRerror()) {
                if ($tii->getRcode()==TURNITINTOOL_DB_UNIQUEID_ERROR) {
                    $reason=get_string('assignmentdoesnotexist','turnitintool');
                } else {
                    $reason=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                }
                turnitintool_print_error('<strong>'.get_string('updateerror','turnitintool').'</strong><br />'.$reason);
                exit();
            }

            $part = new stdClass();
            $part->id=$post['submitted'];
            $part->dtstart=$dtstart;
            $part->dtdue=$dtdue;
            $part->dtpost=$dtpost;
            $part->partname=$post['partname'];
            $part->maxmarks=$post['maxmarks'];
            $part->deleted=0;

            $event = new stdClass();
            $event->timestart=$part->dtdue;
            $event->name=$turnitintool->name.' - '.$part->partname;
            $currentevent=$turnitintool->name.' - '.turnitintool_partnamefromnum($post['submitted']);

            if (!$dbpart=turnitintool_update_record('turnitintool_parts',$part,false)) {
                turnitintool_print_error('partdberror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            }

            if ($events = turnitintool_get_record_select('event', "modulename='turnitintool' AND instance='".$turnitintool->id."' AND name='".$currentevent."'")) {
                $event->id = $events->id;
                update_event($event);
            }

            @include_once($CFG->dirroot."/lib/gradelib.php"); // Set the view time for Grade Book viewing
            if (function_exists('grade_update')) {

                $lastpart=turnitintool_get_record('turnitintool_parts','turnitintoolid',$turnitintool->id,'','','','','max(dtpost)');
                $lastpart=current($lastpart);
                $params['hidden']=$lastpart;

                grade_update('mod/turnitintool', $turnitintool->course, 'mod', 'turnitintool', $turnitintool->id, 0, NULL, $params);
            }
            $tii->endSession();
            turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=intro');
            exit();
        } else {
            return $notice;
        }
    } else {
        turnitintool_print_error('permissiondeniederror','turnitintool',NULL,NULL,__FILE__,__LINE__);
    }
}

/**
 * Processes the request from a user to delete a part from an activity
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object is for this activity
 * @param var $partid The ID of the assignment part stored in turnitintool_parts
 * @return array A notice array contains error details for display on page load in the case of an error nothing returned if no errors occur
 */
function turnitintool_delete_part($cm,$turnitintool,$partid) {
    global $CFG,$USER;
    $notice['message']='';
    if ($turnitintool->numparts==1) {
        $error=true;
        turnitintool_print_error('onepartdeleteerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
    } else if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {

        if (turnitintool_is_owner($turnitintool->course)) {
            $owner=$USER;
        } else {
            $owner=turnitintool_get_owner($turnitintool->course);
        }

        if (!$submissions=turnitintool_delete_records('turnitintool_submissions','turnitintoolid',$turnitintool->id,'submission_part',$partid)) {
            turnitintool_print_error('submissiondeleteerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }

        $result = true;
        // ]]]]

        # Delete any dependent records here #
        $part = new stdClass();
        $part->id=$partid;
        $part->deleted=1;
        if (!turnitintool_update_record("turnitintool_parts", $part, false)) {
            turnitintool_print_error('partdeleteerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        }

        // Delete events for this assignment / part
        turnitintool_delete_records_select('event', "modulename='turnitintool' AND instance=".$turnitintool->id." AND name='".$turnitintool->name." - ".turnitintool_partnamefromnum($partid)."'");

        $update = new stdClass();
        $update->id=$turnitintool->id;
        $update->numparts=$turnitintool->numparts-1;
        if (!turnitintool_update_record("turnitintool",$update)) {
            turnitintool_print_error('turnitintooldeleteerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        }
        turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=intro');
        exit();
    } else {
        turnitintool_print_error('permissiondeniederror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }
    return $notice;
}

/**
 * Outputs the part form HTML
 * @param object $cm The moodle course module object for this instance
 * @param object $part The moodle course module object for this instance
 * @return string Return the part form HTML output
 */
function turnitintool_partform($cm,$part) {
    global $CFG, $OUTPUT;

    $output='<form name="partform" method="POST" action="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=intro">'.PHP_EOL;

    $element=new MoodleQuickForm_hidden('submitted');
    $element->setValue($part->id);
    $output.=$element->toHTML().PHP_EOL;

    $table = new stdClass();
    $table->width='100%';
    $table->id='uploadtableid';
    $table->class='uploadtable';

    // Part Name Field
    unset($cells);
    $cells[0] = new stdClass();
    $cells[0]->data=get_string('partname','turnitintool');
    $cells[0]->class='cell c0';
    $attr=array('class'=>"formwide");
    $element=new MoodleQuickForm_text('partname',null,$attr);
    $element->setValue($part->partname);
    $cells[1] = new stdClass();
    $cells[1]->data=$element->toHTML();
    $cells[1]->class='cell c1';
    $table->rows[0] = new stdClass();
    $table->rows[0]->cells=$cells;

    $dateoptions=array('startyear' => date( 'Y', strtotime( '-6 years' )), 'stopyear' => date( 'Y', strtotime( '+6 years' )),
                    'timezone' => 99, 'applydst' => true, 'step' => 1, 'optional' => false);

    // Part Start Date
    unset($cells);
    $cells[0] = new stdClass();
    $cells[0]->data=get_string('dtstart','turnitintool');
    $cells[0]->class='cell c0';
    $element=new MoodleQuickForm_date_time_selector('dtstart',null,$dateoptions);
    $date = array('hour'=>userdate($part->dtstart,'%H'),
                  'minute'=>userdate($part->dtstart,'%M'),
                  'day'=>userdate($part->dtstart,'%d'),
                  'month'=>userdate($part->dtstart,'%m'),
                  'year'=>userdate($part->dtstart,'%Y')
                  );
    $element->setValue($date);
    $cells[1] = new stdClass();
    $cells[1]->data=$element->toHTML();
    $cells[1]->class='cell c1';
    $table->rows[1] = new stdClass();
    $table->rows[1]->cells=$cells;

    // Part Due Date
    unset($cells);
    $cells[0] = new stdClass();
    $cells[0]->data=get_string('dtdue','turnitintool');
    $cells[0]->class='cell c0';
    $element=new MoodleQuickForm_date_time_selector('dtdue',null,$dateoptions);
    $date = array('hour'=>userdate($part->dtdue,'%H'),
                  'minute'=>userdate($part->dtdue,'%M'),
                  'day'=>userdate($part->dtdue,'%d'),
                  'month'=>userdate($part->dtdue,'%m'),
                  'year'=>userdate($part->dtdue,'%Y')
                  );
    $element->setValue($date);
    $cells[1] = new stdClass();
    $cells[1]->data=$element->toHTML();
    $cells[1]->class='cell c1';
    $table->rows[2] = new stdClass();
    $table->rows[2]->cells=$cells;

    // Part Post Date
    unset($cells);
    $cells[0] = new stdClass();
    $cells[0]->data=get_string('dtpost','turnitintool');
    $cells[0]->class='cell c0';
    $element=new MoodleQuickForm_date_time_selector('dtpost',null,$dateoptions);
    $date = array('hour'=>userdate($part->dtpost,'%H'),
                  'minute'=>userdate($part->dtpost,'%M'),
                  'day'=>userdate($part->dtpost,'%d'),
                  'month'=>userdate($part->dtpost,'%m'),
                  'year'=>userdate($part->dtpost,'%Y')
                  );
    $element->setValue($date);
    $cells[1] = new stdClass();
    $cells[1]->data=$element->toHTML();
    $cells[1]->class='cell c1';
    $table->rows[3] = new stdClass();
    $table->rows[3]->cells=$cells;

    // Part Max Marks
    unset($cells);
    $cells[0] = new stdClass();
    $cells[0]->data=get_string('maxmarks','turnitintool');
    $cells[0]->class='cell c0';
    $element=new MoodleQuickForm_text('maxmarks');
    $element->setValue($part->maxmarks);
    $cells[1] = new stdClass();
    $cells[1]->data=$element->toHTML();
    $cells[1]->class='cell c1';
    $table->rows[4] = new stdClass();
    $table->rows[4]->cells=$cells;

    // Submit / Cancel
    unset($cells);
    $cells[0] = new stdClass();
    $cells[0]->data='&nbsp;';
    $cells[0]->class='cell c0';
    $attr=array('onclick'=>"location.href='".$CFG->wwwroot."/mod/turnitintool/view.php?id=".$cm->id."';");
    $element=new MoodleQuickForm_button('cancel','Cancel',$attr);
    $cells[1] = new stdClass();
    $cells[1]->data=$element->toHTML();
    $element=new MoodleQuickForm_submit('submit','Submit');
    $cells[1]->data.=$element->toHTML();
    $cells[1]->class='cell c1';
    $table->rows[5] = new stdClass();
    $table->rows[5]->cells=$cells;

    $output.=turnitintool_print_table($table,true);
    $output.='</form>'.PHP_EOL;

    return $output;
}

/**
 * Prints the summary page
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object is for this activity
 * @param array $notice A notice array of error details passed if a part update or delete requests fails
 * @return string Returns the output to print to screen
 */
function turnitintool_introduction($cm,$turnitintool,$notice='') {
    global $CFG,$USER;
    $output='<style language="text/css">
        .generaltable .c0 {
            font-weight: bold;
            background-color: #F3F3F3;
        }
    </style>';

    $output.=turnitintool_box_start('generalbox boxwidthwide boxaligncenter eightyfive','introduction',true);

    $table = new stdClass();
    $table->width='100%';
    $table->id='uploadtableid';
    $table->class='uploadtable';

    unset($cells);
    $cells[0] = new stdClass();
    $cells[0]->data=get_string('turnitintoolname', 'turnitintool');
    $cells[0]->class='cell c0';
    $cells[1] = new stdClass();
    $cells[1]->data=$turnitintool->name;
    $cells[1]->class='cell c1';
    $table->rows[0] = new stdClass();
    $table->rows[0]->cells=$cells;

    $exportdisabled=false;
    // Get the post date for the last available part
    if (!$part=turnitintool_get_record_select('turnitintool_parts','turnitintoolid='.$turnitintool->id.' AND deleted = 0',NULL,'max(dtpost) AS dtpost')) {
        turnitintool_print_error('partgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    } else if ( $part->dtpost > time() AND $turnitintool->anon > 0 ) {
        // Check to see if we make the exports available or not
        $exportdisabled=true;
    }

    // Get the start date for the first available part
    if (!$part=turnitintool_get_record_select('turnitintool_parts','turnitintoolid='.$turnitintool->id.' AND deleted = 0',NULL,'min(dtstart) AS dtstart')) {
        turnitintool_print_error('partgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    if ($part->dtstart < time() OR has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        if ( is_callable('format_module_intro') ) {
            $intro=format_module_intro( 'turnitintool', $turnitintool, $cm->id );
        } else {
            $intro=$turnitintool->intro;
        }
    } else {
        $intro=get_string('notavailableyet','turnitintool');
    }

    unset($cells);
    $cells[0] = new stdClass();
    $cells[0]->data=get_string('turnitintoolintro', 'turnitintool');
    $cells[0]->class='cell c0';
    $cells[1] = new stdClass();
    $cells[1]->data=$intro;
    $cells[1]->class='cell c1';
    $table->rows[1] = new stdClass();
    $table->rows[1]->cells=$cells;

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    if (has_capability('mod/turnitintool:grade', $context)) {
        unset($cells);
        $cells[0] = new stdClass();
        $cells[0]->data=get_string('turnitintutors','turnitintool');
        $cells[0]->class='cell c0';
        $cells[1] = new stdClass();
        $cells[1]->data='<a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=tutors" title="'.
                get_string('edit','turnitintool').'"><img src="pix/user-group-edit.png" class="tiiicons" alt="'.get_string('edit','turnitintool').'" /></a>';
        $cells[1]->class='cell c1';
        $table->rows[2] = new stdClass();
        $table->rows[2]->cells=$cells;
    }

    $output.=turnitintool_print_table($table,true);
    $output.=turnitintool_box_end(true);

    unset($table);

    $output.=turnitintool_box_start('generalbox boxwidthwide boxaligncenter eightyfive', 'partsummary',true);

    if (!$parts=turnitintool_get_records_select("turnitintool_parts","turnitintoolid='".$turnitintool->id."' AND deleted=0",NULL,"dtstart,dtdue,dtpost,id")) {
        turnitintool_print_error('partgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
    }

    $vars='';
    foreach ($parts as $part) {
        if (!empty($vars)) {
            $vars.=':';
        }
        $vars.=$part->id;
        if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) { // TUTOR ONLY
            $param_part=optional_param('part',null,PARAM_CLEAN);
            if (!is_null($param_part) AND $param_part==$part->id) {
                $output.=turnitintool_partform($cm,$part);
                $output.=turnitintool_box_end(true);
                return $output;
            }
        }
    }

    // Create the actual initial Table with the part summaries
    $table = new stdClass();
    $table->width='100%';
    $table->id='submissionTableId';
    $table->class='submissionTable';

    if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) { // DO TUTOR HEADERS
        unset($cells);
        $cells[0] = new stdClass();
        $cells[0]->data=get_string('partname','turnitintool');
        $cells[0]->class="header c0 partcell";
        $cells[1] = new stdClass();
        $cells[1]->data=get_string('dtstart','turnitintool');
        $cells[1]->class="header c1 datecell";
        $cells[2] = new stdClass();
        $cells[2]->data=get_string('dtdue','turnitintool');
        $cells[2]->class="header c2 datecell";
        $cells[3] = new stdClass();
        $cells[3]->data=get_string('dtpost','turnitintool');
        $cells[3]->class="header c3 datecell";
        $cells[4] = new stdClass();
        $cells[4]->data=get_string('maxmarks','turnitintool');
        $cells[4]->class="header c4 markscell";
        $cells[5] = new stdClass();
        $cells[5]->data=get_string('downloadexport','turnitintool');
        $cells[5]->class="header c5 markscell";
        $cells[6] = new stdClass();
        $cells[6]->data='&nbsp;';
        $cells[6]->class="header c6 iconcell";
        $cells[7] = new stdClass();
        $cells[7]->data='&nbsp;';
        $cells[7]->class="header c7 iconcell";
        $table->rows[0] = new stdClass();
        $table->rows[0]->cells=$cells;
        unset($cells);
    } else { // Do Student Headers
        unset($cells);
        $cells[0] = new stdClass();
        $cells[0]->data=get_string('partname','turnitintool');
        $cells[0]->class="header c0 partcell";
        $cells[1] = new stdClass();
        $cells[1]->data=get_string('dtstart','turnitintool');
        $cells[1]->class="header c1 datecell";
        $cells[2] = new stdClass();
        $cells[2]->data=get_string('dtdue','turnitintool');
        $cells[2]->class="header c2 datecell";
        $cells[3] = new stdClass();
        $cells[3]->data=get_string('dtpost','turnitintool');
        $cells[3]->class="header c3 datecell";
        $cells[4] = new stdClass();
        $cells[4]->data=get_string('maxmarks','turnitintool');
        $cells[4]->class="header c4 markscell";
        $table->rows[0] = new stdClass();
        $table->rows[0]->cells=$cells;
        unset($cells);
    }

    $row=0;
    foreach($parts as $part) {
        $row++;
        if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) { // DO TUTOR VIEW
            $cells[0] = new stdClass();
            $cells[0]->data='<span id="partnametext_'.$part->id.'">'.$part->partname.'</span>';
            $cells[0]->class="cell c0 partcell";

            $cells[1] = new stdClass();
            $cells[1]->data='<span id="dtstarttext_'.$part->id.'">'.userdate($part->dtstart,get_string('strftimedatetimeshort','langconfig')).'</span>';
            $cells[1]->class="cell c1 datecell";

            $cells[2] = new stdClass();
            $cells[2]->data='<span id="dtduetext_'.$part->id.'">'.userdate($part->dtdue,get_string('strftimedatetimeshort','langconfig')).'</span>';
            $cells[2]->class="cell c2 datecell";

            $cells[3] = new stdClass();
            $cells[3]->data='<span id="dtposttext_'.$part->id.'">'.userdate($part->dtpost,get_string('strftimedatetimeshort','langconfig')).'</span>';
            $cells[3]->class="cell c3 datecell";

            $cells[4] = new stdClass();
            $cells[4]->data='<span id="maxmarkstext_'.$part->id.'">'.$part->maxmarks.'</span>';
            $cells[4]->class="cell c4 markscell";

            $cells[5] = new stdClass();
            if (turnitintool_count_records_select('turnitintool_submissions','submission_part='.$part->id.' AND submission_objectid IS NOT NULL') AND !$exportdisabled ) {

                $url = $CFG->wwwroot . '/mod/turnitintool/view.php?id=' . $cm->id;
                $url .= '&jumppage=zipfile&userid=' . $USER->id . '&partid=' . $part->id . '&utp=2';

                $cells[5]->data='<a href="' . $url . '&export_data=1" onclick="screenOpen(this.href,\'\',\'0\',null,\'width=450,height=200\');'
                        .'return false;" target="_blank" title="'.get_string('downloadorigzip','turnitintool')
                        .'"><img src="pix/file.png" class="tiiicons" alt="'.get_string('downloadorigzip','turnitintool')
                        .'" id="orig_'.$row.'" /></a></span>'.PHP_EOL;

                $cells[5]->data.='<a href="' . $url . '&export_data=2' .'" onclick="screenOpen(this.href,\'\',\'0\',null,\'width=450,height=200\');'
                        .'return false;" target="_blank" title="'.get_string('downloadpdfzip','turnitintool')
                        .'"><img src="pix/file-pdf.png" class="tiiicons" alt="'.get_string('downloadpdfzip','turnitintool')
                        .'" id="pdf_'.$row.'" /></a></span>'.PHP_EOL.'<a href="'.$CFG->wwwroot.'/mod/turnitintool/filelink.php?id='.$cm->id
                        .'&part='.$part->id.'" title="'.get_string('downloadgradexls','turnitintool')
                        .'"><img src="pix/file-xls.png" class="tiiicons" alt="'.get_string('downloadgradexls','turnitintool')
                        .'" id="excel_'.$row.'" /></a></span>';

            } else {
                $cells[5]->data='-';
            }

            $cells[5]->class="cell c5 markscell";

            $cells[6] = new stdClass();
            $cells[6]->data='<span id="ticktext_'.$part->id.'"><a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id
                    .'&part='.$part->id.'" title="'.get_string('edit','turnitintool')
                    .'"><img src="pix/window-osx-edit.png" class="tiiicons" alt="'.get_string('edit','turnitintool').'" id="edit_'.$row.'" /></a></span>';
            $cells[6]->class="cell c6 iconcell";

            if (turnitintool_count_records('turnitintool_submissions','turnitintoolid',$turnitintool->id,'submission_part',$part->id)>0) {
                $fnd = array("\n","\r");
                $rep = array('\n','\r');
                $warning=' onclick="return confirm(\''.str_replace($fnd, $rep, get_string('partdeletewarning','turnitintool')).'\');"';
            } else {
                $warning='';
            }

            $cells[7] = new stdClass();
            $cells[7]->data=(count($parts)>1) ? '<a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.
                    '?id='.$cm->id.'&do=intro&delpart='.$part->id.'" title="'.get_string('delete','turnitintool').
                    '"'.$warning.'><img src="pix/delete.png" class="tiiicons" alt="'.get_string('delete','turnitintool').'" /></a>' : '';
            $cells[7]->class="cell c9 iconcell";

        } else { // DO STUDENT VIEW
            $cells[0] = new stdClass();
            $cells[0]->data=$part->partname;
            $cells[0]->class="cell c0 partcell";
            $cells[1] = new stdClass();
            $cells[1]->data=userdate($part->dtstart,get_string('strftimedatetimeshort','langconfig'));
            $cells[1]->class="cell c1 datecell";
            $cells[2] = new stdClass();
            $cells[2]->data=userdate($part->dtdue,get_string('strftimedatetimeshort','langconfig'));
            $cells[2]->class="cell c2 datecell";
            $cells[3] = new stdClass();
            $cells[3]->data=userdate($part->dtpost,get_string('strftimedatetimeshort','langconfig'));
            $cells[3]->class="cell c3 datecell";
            $cells[4] = new stdClass();
            $cells[4]->data=$part->maxmarks;
            $cells[4]->class="cell c4 markscell";
        }
        $table->rows[$row] = new stdClass();
        $table->rows[$row]->cells = new stdClass();
        $table->rows[$row]->cells=$cells;
        $table->rows[$row]->class="row r".(($row%2) ? 0 : 1);
        unset($cells);

    }

    $output.=turnitintool_print_table($table,true);
    if (isset($editpart)) {
        $output.=$editpart;
    }

    $output.=turnitintool_box_end(true);

    return $output;
}
/**
 * Queries Turnitin for the tutors enrolled on the Turnitin Class
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object is for this activity
 * @return string Returns the output to print to screen
 */
function turnitintool_get_tiitutors($cm,$turnitintool) {
    $return=null;
    if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        $loaderbar = null;
        $owner=turnitintool_get_owner($turnitintool->course);
        $owneruid=turnitintool_getUID($owner);
        $tii = new turnitintool_commclass($owneruid,$owner->firstname,$owner->lastname,$owner->email,2,$loaderbar);
        $post = new stdClass();
        $post->cid=turnitintool_getCID($turnitintool->course);
        $post->ctl=turnitintool_getCTL($turnitintool->course);
        $tutors=$tii->getTutors($post,get_string('turnitintutorsretrieving','turnitintool'));
        if ($tii->getRerror()) {
            $return = new stdClass();
            $return->error=$tii->getRmessage();
            $return->array=null;
        } else {
            $return = new stdClass();
            $return->error=null;
            $return->array=$tutors;
        }
    }
    return $return;
}
/**
 * Removes tutor from enrolled tutors on Turnitin
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object is for this activity
 * @param int $tutor The moodle user id to unenrol
 * @return string Returns the output to print to screen
 */
function turnitintool_remove_tiitutor($cm,$turnitintool,$tutor) {
    $return=null;
    if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        $loaderbar = new turnitintool_loaderbarclass(4);
        $thisuser=turnitintool_get_moodleuser($tutor);
        $thisuid=turnitintool_getUID($thisuser);
        $tii = new turnitintool_commclass($thisuid,$thisuser->firstname,$thisuser->lastname,$thisuser->email,2,$loaderbar);
        $tii->startSession();
        $post = new stdClass();
        $post->cid=turnitintool_getCID($turnitintool->course);
        $post->ctl=turnitintool_getCTL($turnitintool->course);

        $return = new stdClass();
        $return->error=null;
        $return->array=null;

        $tutors=$tii->getTutors($post,get_string('turnitintutorsretrieving','turnitintool'));
        if (count($tutors)==1) {
            $return->error=get_string('turnitintutorsremove_errorlast','turnitintool');
            $return->array=null;
        } else {
            if ($owner=turnitintool_get_owner($turnitintool->course) AND $owner->id==$tutor) {
                foreach ($tutors as $tutorobj) {
                    if ((string)$tutorobj->email!=$owner->email) {
                        $loaderbar->total=$loaderbar->total+1;
                        $post->new_teacher_email=(string)$tutorobj->email;
                        $tii->changeOwner($post,get_string('changingowner','turnitintool'));
                        unset($post->new_teacher_email);
                        $newowner=turnitintool_get_record_select('user',"email='".$tutorobj->email."'");
                        $tiicourse=turnitintool_get_record('turnitintool_courses','courseid',$turnitintool->course);
                        $tiicourse->ownerid=$newowner->id;
                        turnitintool_update_record('turnitintool_courses',$tiicourse);
                        break;
                    }
                }
            }
            $tii->unenrolUser($post,get_string('turnitintutorsretrieving','turnitintool'));
        }
        if ($tii->getRerror()) {
            $return->error=$tii->getRmessage();
            $return->array=null;
        } else {
            $return->array=$tutors;
        }
        $tii->endSession();
    }
    return $return;
}
/**
 * Outputs the tutors enrolled on the Turnitin Class
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object is for this activity
 * @return string Returns the output to print to screen
 */
function turnitintool_view_tiitutors($cm,$turnitintool,$tutors) {
    global $CFG;
    $table = new stdClass();
    $table->width='85%';
    $table->tablealign='center';
    $table->class='submissionTable';

    $table->rows[0] = new stdClass();
    $table->rows[0]->cells[0] = new stdClass();
    $table->rows[0]->cells[0]->class='header c0 iconcell';
    $table->rows[0]->cells[0]->data='';
    $table->rows[0]->cells[1] = new stdClass();
    $table->rows[0]->cells[1]->class='header c1 iconcell';
    $table->rows[0]->cells[1]->data='';
    $table->rows[0]->cells[2] = new stdClass();
    $table->rows[0]->cells[2]->class='header c2';
    $table->rows[0]->cells[2]->data=get_string('turnitintutors','turnitintool');

    $i=0;
    foreach ($tutors->array as $value) {
        $uid = (string)$value->uid;
        if (!$tiiuser=turnitintool_get_record('turnitintool_users','turnitin_uid',$uid)) {
            continue;
        } else {
            $i++;
            $user=turnitintool_get_moodleuser($tiiuser->userid);
            $table->rows[$i]->cells[0] = new stdClass();
            $table->rows[$i]->cells[0]->class='cell c0 iconcell';
            $table->rows[$i]->cells[0]->data='<a href="'.$CFG->wwwroot
                    .'/mod/turnitintool/view.php?id='.$cm->id.'&do=tutors&unenrol='
                    .$tiiuser->userid.'" title="'.get_string('turnitintutorsremove','turnitintool')
                    .'"><img src="pix/delete.png" alt="'
                    .get_string('turnitintutorsremove','turnitintool').'" class="tiiicons" /></a>';
            $table->rows[$i]->cells[1] = new stdClass();
            $table->rows[$i]->cells[1]->class='cell c1 iconcell';
            $owner=turnitintool_get_owner($turnitintool->course);
            if ($owner->id==$user->id) {
                $table->rows[$i]->cells[1]->data='<a><img src="pix/ownerstar.gif" alt="'
                        .get_string('turnitintoolowner','turnitintool').'" class="tiiicons" /></a>';
            } else {
                $table->rows[$i]->cells[1]->data='<a href="'.$CFG->wwwroot
                        .'/mod/turnitintool/view.php?id='.$cm->id.'&do=changeowner&owner='
                        .$tiiuser->userid.'" title="'.get_string('changeowner','turnitintool')
                        .'"><img src="pix/ownerstar_grey.gif" alt="'
                        .get_string('changeowner','turnitintool').'" class="tiiicons" /></a>';
            }
            $table->rows[$i]->cells[2] = new stdClass();
            $table->rows[$i]->cells[2]->class='cell c2';
            $table->rows[$i]->cells[2]->data='<a href="'.$CFG->wwwroot
                    .'/user/view.php?id='.$tiiuser->userid.'&course='.$turnitintool->course
                    .'">'.(string)$value->lastname.', '.(string)$value->firstname.'</a>'.' ('.$user->username.')';
        }
    }

    turnitintool_box_start('generalbox boxwidthwide boxaligncenter', 'general');
    echo print_string('turnitintutors_desc','turnitintool');
    turnitintool_box_end();
    turnitintool_print_table($table);

    unset($table);
    $table = new stdClass();
    $table->width='85%';
    $table->tablealign='center';
    $table->class='uploadtable';

    $table->rows[0] = new stdClass();
    $table->rows[0]->cells[0] = new stdClass();
    $table->rows[0]->cells[0]->class='cell c0';
    $table->rows[0]->cells[0]->data=get_string('turnitintutors','turnitintool');
    $table->rows[0]->cells[1] = new stdClass();
    $table->rows[0]->cells[1]->class='cell c1';
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    $availabletutors=get_users_by_capability($context,'mod/turnitintool:grade','u.id,u.firstname,u.lastname,u.username','','','',0,'',false);
    $tutorselection=get_string('turnitintutorsallenrolled','turnitintool');
    foreach ($tutors->array as $value) {
        $idarray[]=(string)$value->userid;
    }
    $options='';
    foreach ($availabletutors as $available) {
        if (!in_array(turnitintool_getUID($available),$idarray)) {
            $options.='<option value="'.$available->id.'" label="'.$available->lastname.', '.$available->firstname
                    .' ('.$available->username.')">'.$available->lastname.', '
                    .$available->firstname.' ('.$available->username.')</option>';
        }
    }
    if (!empty($options)) {
        $tutorselection='<select name="enroltutor">'.PHP_EOL;
        $tutorselection.=$options.PHP_EOL;
        $tutorselection.='</select>'.PHP_EOL;
    }

    $table->rows[0]->cells[1]->data=$tutorselection;

    if (!empty($options)) {
        $table->rows[1] = new stdClass();
        $table->rows[1]->cells[0] = new stdClass();
        $table->rows[1]->cells[0]->class='cell c0';
        $table->rows[1]->cells[0]->data='&nbsp;';
        $table->rows[1]->cells[1] = new stdClass();
        $table->rows[1]->cells[1]->class='cell c1';
        $table->rows[1]->cells[1]->data='<input type="submit" value="'.get_string('turnitintutorsadd','turnitintool').'" />';
    }
    turnitintool_box_start('generalbox boxwidthwide boxaligncenter', 'general');
    echo '<form method="POST" action="'.$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=tutors">';
    turnitintool_print_table($table);
    echo '</form>';
    turnitintool_box_end();
}
/**
 * Adds / Deletes a tutor to the list enrolled on the Turnitin Class
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object is for this activity
 * @param int $tutor The moodle user id to unenrol
 * @return string Returns the output to print to screen
 */
function turnitintool_add_tiitutor($cm,$turnitintool,$tutor) {
    $return=null;
    if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        $loaderbar = new turnitintool_loaderbarclass(4);
        $thisuser=turnitintool_get_moodleuser($tutor);

        $thisuid=turnitintool_getUID($thisuser);
        if (is_null($thisuid)) {
            $tii = new turnitintool_commclass($thisuid,$thisuser->firstname,$thisuser->lastname,$thisuser->email,2,$loaderbar);
            $tii->startSession();
            turnitintool_usersetup($thisuser,get_string('userprocess','turnitintool'),$tii,$loaderbar);
            $tii->endSession();
            $thisuid=turnitintool_getUID($thisuser);
        }

        $tii = new turnitintool_commclass($thisuid,$thisuser->firstname,$thisuser->lastname,$thisuser->email,2,$loaderbar);
        $tii->startSession();
        $post = new stdClass();
        $post->cid=turnitintool_getCID($turnitintool->course);
        $post->ctl=turnitintool_getCTL($turnitintool->course);

        $return = new stdClass();
        $return->error=null;
        $return->array=null;

        $tutors=$tii->getTutors($post,get_string('turnitintutorsretrieving','turnitintool'));
        $tii->enrolTutor($post,get_string('turnitintutorsadding','turnitintool'));

        if ($tii->getRerror()) {
            $return->error=$tii->getRmessage();
            $return->array=null;
        } else {
            $return->array=$tutors;
        }
        $tii->endSession();
    }
    return $return;
}
/**
 * Processes the owner change form
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object is for this activity
 * @param var $newid The Moodle User ID of the proposed new Turnitin Class Owner
 */
function turnitintool_ownerprocess($cm,$turnitintool,$newid) {
    // Firstly Double Check to make sure this user is a tutor on this course
    if ($newid!='NULL') {

        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        $isadmin=false;
        if (has_capability('moodle/site:config',get_context_instance(CONTEXT_SYSTEM))) {
            $isadmin=true;
        }
        $allusers=get_users_by_capability($context, 'mod/turnitintool:grade', "u.id AS id", '', '', '', 0, '', true);
        if (!isset($allusers[$newid]) AND !$isadmin) {
            turnitintool_print_error('permissiondeniederror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();

        } else {
            $owner=turnitintool_get_owner($turnitintool->course);
            $owneruid=turnitintool_getUID($owner);
            $newowner=turnitintool_get_moodleuser($newid);
            $newowneruid=turnitintool_getUID($newowner);

            $loaderbar = new turnitintool_loaderbarclass(3);
            $tii = new turnitintool_commclass($newowneruid,$newowner->firstname,$newowner->lastname,$newowner->email,2,$loaderbar);
            if (is_null($newowneruid)) { // User doesnt exist -- create Turnitin user
                $tii->startSession();
                turnitintool_usersetup($newowner,get_string('userprocess','turnitintool'),$tii,$loaderbar);
                if ($tii->getRerror()) {
                    if ($tii->getAPIunavailable()) {
                        turnitintool_print_error('apiunavailable','turnitintool',NULL,NULL,__FILE__,__LINE__);
                    } else {
                        turnitintool_print_error($tii->getRmessage(),NULL,NULL,NULL,__FILE__,__LINE__);
                    }
                    exit();
                }
                $tii->endSession();
            }

            $post = new stdClass();
            $post->cid=turnitintool_getCID($turnitintool->course);
            $post->ctl=turnitintool_getCTL($turnitintool->course);

            $tii->enrolTutor($post,get_string('changingowner','turnitintool'));

            if ($tii->getRerror()) {
                $reason=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                turnitintool_print_error('<strong>'.get_string('turnitintoolupdateerror','turnitintool').'</strong><br />'.$reason,NULL,NULL,NULL,__FILE__,__LINE__);
                exit();
            } else {
                $currcourse=turnitintool_get_record('turnitintool_courses','courseid',$turnitintool->course);
                $update = new stdClass();
                $update->id=$currcourse->id;
                $update->ownerid=$newid;
                if (!$dodb=turnitintool_update_record('turnitintool_courses',$update)) {
                    turnitintool_print_error('classupdateerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                    exit();
                }
            }
            $tii->endSession();

        }

    }
}

/**
 * Prints the date selection form elements in the format specified in the options for the activity
 *
 * @param string $fieldname A string used in the name attributes to distinguish one instance of this date selector form another
 * @param array $selectedarray An array that stores details of the currently stored settings
 * @return string Returns the output to print to screen
 */
function turnitintool_dateselect($fieldname,$selectedarray=NULL) {
    $date['h']='<select name="hour_'.$fieldname.'">';
    for ($i=0;$i<=23;$i++) { // Days
        if (!is_null($selectedarray) AND $selectedarray['h']==$i) {
            $selected=' selected';
        } else if (is_null($selectedarray) AND date('H',time())==$i) {
            $selected=' selected';
        } else {
            $selected='';
        }
        $date['h'].='<option label="'.str_pad($i,2,"0",0).'" value="'.str_pad($i,2,"0",0).'"'.$selected.'>'.str_pad($i,2,"0",0).'</option>';
        $selected='';
    }
    $date['h'].='</select>';

    $date['min']='<select name="min_'.$fieldname.'">';
    for ($i=0;$i<=59;$i++) { // Days
        if (!is_null($selectedarray) AND $selectedarray['min']==$i) {
            $selected=' selected';
        } else if (is_null($selectedarray) AND date('i',time())==$i) {
            $selected=' selected';
        } else {
            $selected='';
        }
        $date['min'].='<option label="'.str_pad($i,2,"0",0).'" value="'.str_pad($i,2,"0",0).'"'.$selected.'>'.str_pad($i,2,"0",0).'</option>';
        $selected='';
    }
    $date['min'].='</select>';

    $date['d']='<select name="day_'.$fieldname.'">';
    for ($i=1;$i<=31;$i++) { // Days
        if (!is_null($selectedarray) AND $selectedarray['d']==$i) {
            $selected=' selected';
        } else if (is_null($selectedarray) AND date('j',time())==$i) {
            $selected=' selected';
        } else {
            $selected='';
        }
        $date['d'].='<option label="'.str_pad($i,2,"0",0).'" value="'.str_pad($i,2,"0",0).'"'.$selected.'>'.str_pad($i,2,"0",0).'</option>';
        $selected='';
    }
    $date['d'].='</select>';

    $date['m']='<select name="month_'.$fieldname.'">';
    for ($i=1;$i<=12;$i++) { // Month
        if (!is_null($selectedarray) AND $selectedarray['m']==$i) {
            $selected=' selected';
        } else if (is_null($selectedarray) AND date('n',time())==$i) {
            $selected=' selected';
        } else {
            $selected='';
        }
        $date['m'].='<option label="'.str_pad($i,2,"0",0).'" value="'.str_pad($i,2,"0",0).'"'.$selected.'>'.str_pad($i,2,"0",0).'</option>';
        $selected='';
    }
    $date['m'].='</select>';

    $date['Y']='<select name="year_'.$fieldname.'">';
    $theyear=date('Y',strtotime('-10 years'));
    for ($i=1;$i<=20;$i++) { // Year
        if (!is_null($selectedarray) AND $selectedarray['Y']==$theyear) {
            $selected=' selected';
        } else if (is_null($selectedarray) AND date('Y',time())==$theyear) {
            $selected=' selected';
        } else {
            $selected='';
        }
        $date['Y'].='<option label="'.$theyear.'" value="'.$theyear.'"'.$selected.'>'.$theyear.'</option>';
        $theyear++;
        $selected='';
    }
    $date['Y'].='</select>';

    $output='';
    $output.=$date['h'].':'.$date['min'].',<br />';
    $output.=$date['Y'].'/'.$date['m'].'/'.$date['d'];

    return $output;
}

/**
 * Outputs the file path for the user passed in the $userid parameter
 *
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param var $userid The moodle user ID for the user to return the file path of
 * @return string Returns the file path
 */
function turnitintool_file_path($cm,$turnitintool,$userid) {
    global $CFG;
    $seperator='/';
    $output=$turnitintool->course.$seperator.$CFG->moddata.$seperator.'turnitintool'.$seperator.$turnitintool->id.$seperator.$userid;
    return $output;
}

/**
 * Outputs the file path for the user passed in the $userid parameter
 *
 * @global object
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param object $submission A data object for the submission in turnitintool_submissions
 * @return string A formatted html similarity score box with similarity score or '-' if it is not to be displayed or unavailable
 */
function turnitintool_draw_similarityscore($cm,$turnitintool,$submission) {
    global $CFG,$USER;

    if (empty($submission->submission_objectid)) {
        $score='-';
    } else {
        $result=$submission->submission_score;
        $objectid=$submission->submission_objectid;

        if (!is_null($objectid) AND (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id)) OR $turnitintool->studentreports)) {

            if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
                $utp=2;
            } else {
                $utp=1;
            }

            $thisuser=$USER;
            if ((!is_null($result) AND !empty($result)) OR $result=="0") {
                $style=turnitintool_percent_to_gradpos($result);
                $style2="";
                $result.='%';

                $reportlink = $CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id . '&jumppage=report';
                $reportlink .= '&userid=' . $thisuser->id . '&objectid=' . $submission->submission_objectid . '&utp=' . $utp;

                $transmatch = ( $submission->submission_transmatch == 1 ) ? 'EN' : '&nbsp;';

                $score='<div class="origLink"><a href="'.$reportlink.'" target="_blank" title="'.get_string('viewreport','turnitintool').
                        '" class="scoreLink" onclick="screenOpen(\''.$reportlink.'\',\''.$submission->id.'\',\''.
                        $turnitintool->autoupdates.'\');return false;"><span class="scoreBox"'.$style2.'>'.$result.'<span class="scoreColor"'.$style.'>'.$transmatch.'</span></span></a></div>';
            } else {
                $color='#FCFCFC';
                $style=' style="background-color: '.$color.';text-align: center;"';
                $style2=' style="padding: 0px;"';
                $result=get_string('pending','turnitintool');
                $score='<div class="origLink">
            <a name="Pending" class="scoreLink"'.$style.'><span class="scoreBox"'.$style2.'>'.$result.'</span></a></div>';
            }

        } else {
            $score='-';
        }
    }

    return $score;

}

/**
 * Outputs the file link for the submission passed in the $submission parameter
 *
 * @global object
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param object $submission A data object for the submission in turnitintool_submissions
 * @param boolean $download A boolean value that determines (if this is a Turnitin submission) whether to show the submission screen or download the file
 * @return string A formatted html file link
 */
function turnitintool_get_filelink($cm,$turnitintool,$submission,$download=false) {

    global $CFG,$USER;
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    if (empty($submission->submission_objectid)) {
        if (is_callable("get_file_storage")) {
            $filelink=$CFG->wwwroot.'/mod/turnitintool/filelink.php?id='.$cm->id.'&sub='.$submission->id;
        } else {
            $filelink=$CFG->wwwroot.'/file.php?file=/'.turnitintool_file_path($cm,$turnitintool,$submission->userid).'/'.str_replace(" ","_",$submission->submission_filename);
        }

    } else {

        if (has_capability('mod/turnitintool:grade', $context)) {
            $utp=2;
        } else {
            $utp=1;
        }

        $thisuser=$USER;

        if (!$download) {
            $filelink=$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&jumppage=submission&userid='.$thisuser->id.'&utp='.$utp.'&partid='.$submission->submission_part.'&objectid='.$submission->submission_objectid;
        } else {
            $filelink=$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&jumppage=download&userid='.$thisuser->id.'&utp='.$utp.'&partid='.$submission->submission_part.'&objectid='.$submission->submission_objectid;
        }
    }

    return $filelink;
}

/**
 * Outputs Student Submission Inbox
 *
 * @global object
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @return string Outputs the Students Submission Inbox
 */
function turnitintool_view_student_submissions($cm,$turnitintool) {
    global $CFG,$USER;
    $output = '';
    $param_do=optional_param('do',null,PARAM_CLEAN);

    $i=0;

    if (!has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        // If a grading user (Tutor then this list is not needed)
        if (!$submissions = turnitintool_get_records_select('turnitintool_submissions','userid='.$USER->id.' AND turnitintoolid='.$turnitintool->id,NULL,'id')) {
            $output.=turnitintool_box_start('generalbox boxwidthwide boxaligncenter centertext eightyfive', 'nosubmissions',true);
            $output.=get_string('nosubmissions','turnitintool');

            $output.='<p>[<a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do='.$param_do.
                    '&update=1" title="'.get_string('synchyoursubmissions','turnitintool').
                    '" class="refreshLink">'.get_string('synchyoursubmissions','turnitintool').'</a>]</p>';
            $output.=turnitintool_box_end(true);

        } else {
            $output.='
    <div class="tabLinks">
        <div style="display: none;" id="inboxNotice"><span style="background: url(pix/ajax-loader.gif) no-repeat left center;padding-left: 80px;">
        <span style="background: url(pix/ajax-loader.gif) no-repeat right center;padding-right: 80px;">'
                .get_string('turnitinloading','turnitintool').'</span></span></div>
        <a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do='.$param_do.
                    '&update=1" onclick="refreshSubmissionsAjax();return false;"><img src="'.$CFG->wwwroot.'/mod/turnitintool/pix/refresh.gif" alt="'.
                    get_string('turnitinrefreshsubmissions','turnitintool').'" class="tiiicons" /> '.
                    get_string('turnitinrefreshsubmissions','turnitintool').'</a></div>';

            $table = new stdClass();
            $table->width='85%';
            $table->tablealign='center';
            $table->class='submissionTable';
            $table->id='inboxTable';
            $table->rows[0] = new stdClass();
            $table->rows[0]->hcells[0] = new stdClass();
            $table->rows[0]->hcells[0]->class='header c0 namecell';
            $table->rows[0]->hcells[0]->data='<div>'.get_string('submission','turnitintool').'</div>';
            $table->rows[0]->hcells[1] = new stdClass();
            $table->rows[0]->hcells[1]->class='header c1 datecell';
            $table->rows[0]->hcells[1]->data='<div>'.get_string('posted','turnitintool').'</div>';
            $table->rows[0]->hcells[2] = new stdClass();
            $table->rows[0]->hcells[2]->class='header c2 markscell';
            $table->rows[0]->hcells[2]->data='<div>'.get_string('submissionorig','turnitintool').'</div>';
            $table->rows[0]->hcells[3] = new stdClass();
            $table->rows[0]->hcells[3]->class='header c3 markscell';
            $table->rows[0]->hcells[3]->data='<div>'.get_string('submissiongrade','turnitintool').'</div>';
            $table->rows[0]->hcells[4] = new stdClass();
            $table->rows[0]->hcells[4]->class='header c4 markscell';
            $table->rows[0]->hcells[4]->data='<div>'.get_string('feedback','turnitintool').'</div>';
            $table->rows[0]->hcells[5] = new stdClass();
            $table->rows[0]->hcells[5]->class='header c5 iconcell';
            $table->rows[0]->hcells[5]->data='<div>&nbsp;</div>';
            $table->rows[0]->hcells[6] = new stdClass();
            $table->rows[0]->hcells[6]->class='header c6 iconcell';
            $table->rows[0]->hcells[6]->data='<div>&nbsp;</div>';

            $table->size=array('','14%','8%','8%','4%','4%','4%');
            $table->align=array('left','center','center','center','center','center','center');
            $table->valign=array('top','center','center','center','center','center','center');
            $table->wrap=array(NULL,'nowrap','nowrap','nowrap','nowrap','nowrap','nowrap');

            if (!$parts=turnitintool_get_records_select('turnitintool_parts','turnitintoolid='.$turnitintool->id.' AND deleted = 0')) {
                turnitintool_print_error('partgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }

            $submissionids = array_keys( $submissions );
            $submission_string = join( ',', $submissionids );
            $comments = turnitintool_get_records_sql( 'SELECT submissionid, count( id ) AS count FROM {turnitintool_comments} WHERE submissionid IN ( '.$submission_string.' ) GROUP BY submissionid' );

            $i=0;
            foreach ($submissions as $submission) {
                unset($cell);
                $cell=array();

                $part = $parts[ $submission->submission_part ];

                $filelink=turnitintool_get_filelink($cm,$turnitintool,$submission);
                $downloadlink=turnitintool_get_filelink($cm,$turnitintool,$submission,$download=true);

                $link='';

                if ($submission->submission_type==1) {
                    $submission_type_label=get_string('fileupload','turnitintool');
                } else if ($submission->submission_type==2) {
                    $submission_type_label=get_string('textsubmission','turnitintool');
                } else if ($submission->submission_type==3) {
                    $submission_type_label=get_string('webpage','turnitintool');
                }

                $doscript='';
                if (!empty($submission->submission_objectid)) {
                    $doscript=' onclick="screenOpen(\''.$filelink.'\',\''.$submission->id.'\',\''.$turnitintool->autoupdates.'\');return false;"';
                }

                if ($turnitintool->numparts>1) {
                    $partnameoutput=$part->partname.' - ';
                } else {
                    $partnameoutput='';
                }


                $downloadurl=turnitintool_get_filelink($cm,$turnitintool,$submission,$download=true);
                $downscript='';
                if (!empty($submission->submission_objectid)) {
                    $downscript=' onclick="screenOpen(\''.$downloadurl.'\',\''.$submission->id.'\',\''.$turnitintool->autoupdates.'\');return false;"';
                }
                $downloadlink='<a href="'.$downloadurl.'" class="tiiicons" target="_blank"'.$downscript.'>*</a>';

                $link.='<a href="'.$filelink.'" target="_blank"'.$doscript.'>'.$partnameoutput.$submission->submission_title.'</a><br />';

                // ###############################
                // Do Submission to Turnitin Form
                // ###############################

                $modified='-';
                if (empty($submission->submission_objectid)) {
                    $modified='<div class="submittoLinkSmall"><img src="'.$CFG->wwwroot.'/mod/turnitintool/icon.gif" /><a href="'.
                            $CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&up='.$submission->id.'">'.
                            get_string('submittoturnitin','turnitintool').'</a></div>';
                } else if (!is_null($submission->id)) {
                    $modified=(empty($submission->submission_objectid)) ? '-' : userdate($submission->submission_modified,get_string('strftimedatetimeshort','langconfig'));
                    if ($submission->submission_modified>$part->dtdue) {
                        $modified='<span style="color: red;">'.$modified.'</span>';
                    }
                }

                // ###################################
                // Get originality score if available
                // ###################################

                $score = turnitintool_draw_similarityscore($cm,$turnitintool,$submission);

                // ###################################
                // get Grade if available ############
                // ###################################

                $i++;
                $grade=turnitintool_dogradeoutput($cm,$turnitintool,$submission,$part->dtdue,$part->dtpost,$part->maxmarks,'black','transparent',false);

                $status='<b>'.get_string('status','turnitintool').':</b> '.get_string('submissionnotyetuploaded','turnitintool');
                if (!empty($submission->submission_status)) {
                    $status='<b>'.get_string('status','turnitintool').':</b> '.$submission->submission_status;
                }

                // ###################################
                // Get Comments / Feedback ###########
                // ###################################

                $num = isset( $comments[$submission->id] ) ? $comments[$submission->id]->count : 0;
                $notes=turnitintool_getnoteslink($cm,$turnitintool,$submission,$num);

                // ###################################
                // Print Download Link ###############
                // ###################################

                if (!is_null($submission->submission_objectid)) {
                    $downscript=' onclick="screenOpen(this.href,\''.$submission->id.'\',false,null,\'width=450,height=200\');return false;"';
                    $download='<a href="'.turnitintool_get_filelink($cm,$turnitintool,$submission,true).'" title="'.
                            get_string('downloadsubmission','turnitintool').'" target="_blank"'.$downscript.'><img src="pix/file-download.png" alt="'.
                            get_string('downloadsubmission','turnitintool').'" class="tiiicons" /></a>';
                } else {
                    $download='';
                }

                // ###################################
                // Print Delete Button ###############
                // ###################################

                $fnd = array("\n","\r");
                $rep = array('\n','\r');
                if (empty($submission->submission_objectid)) {
                    $confirm=' onclick="return confirm(\''.str_replace($fnd, $rep, get_string('deleteconfirm','turnitintool')).'\');"';
                } else if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
                    $confirm=' onclick="return confirm(\''.str_replace($fnd, $rep, get_string('turnitindeleteconfirm','turnitintool')).'\')"';
                } else {
                    $confirm=' onclick="return confirm(\''.str_replace($fnd, $rep, get_string('studentdeleteconfirm','turnitintool')).'\')"';
                }

                if (empty($submission->submission_objectid)
                        OR has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
                    $delete='<a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&delete='.$submission->id.
                            '&do='.$param_do.'"'.$confirm.' title="'.get_string('deletesubmission','turnitintool').
                            '"><img src="pix/delete.png" alt="'.get_string('deletesubmission','turnitintool').'" class="tiiicons" /></a>';
                } else {
                    $delete='-';
                }

                $cell[0] = new stdClass();
                $cell[0]->data=$link.$status;
                $cell[0]->class='cell c0';
                $cell[1] = new stdClass();
                $cell[1]->data=$modified;
                $cell[1]->class='cell c1 datecell';
                $cell[2] = new stdClass();
                $cell[2]->data=$score;
                $cell[2]->class='cell c2 markscell';
                $cell[3] = new stdClass();
                $cell[3]->data=$grade;
                $cell[3]->class='cell c3 markscell';
                $cell[4] = new stdClass();
                $cell[4]->data=$notes;
                $cell[4]->class='cell c4 markscell';
                $cell[5] = new stdClass();
                $cell[5]->data=$download;
                $cell[5]->class='cell c5 iconcell';
                $cell[6] = new stdClass();
                $cell[6]->data=$delete;
                $cell[6]->class='cell c6 iconcell';

                $key=($i%2) ? 0 : 1;
                $table->rows[$i] = new stdClass();
                $table->rows[$i]->cells=$cell;
                $table->rows[$i]->class='row r'.$key;
                $i++;
            }

            $sessionrefresh = (isset($_SESSION['updatedscores'][$turnitintool->id]) AND $_SESSION['updatedscores'][$turnitintool->id]>0) ? '' : 'refreshSubmissionsAjax();';

            $output .= '
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#inboxTable").dataTable( { "sPaginationType": "full_numbers", "sDom": "r<\"top navbar\"lf><\"dt_page\"pi>t<\"bottom\"><\"dt_page\"pi>", } );
        ' . $sessionrefresh . '
    });
</script>';
            $output.=turnitintool_print_table($table,true).'<br />';
            if (!$turnitintool->studentreports) {
                $output.=turnitintool_box_start('generalbox boxwidthwide boxaligncenter eightyfive', 'introduction',true);
                $output.=get_string('studentnotallowed','turnitintool');
                $output.=turnitintool_box_end(true);
            }

        }
    }
    return $output;
}

/**
 * Converts an array into a data object
 *
 * @param array $input An array to convert to an object
 * @return object The converted array
 */
function turnitintool_array_to_object($input) {
    $output = new object();
    foreach ($input as $key=>$value) {
        if (!empty($key)) {
            $output->$key = $value;
        }
    }
    return $output;
}

/**
 * Calculates and returns the overall grade for this activity
 *
 * @param array $input An array to convert to an object
 * @return object The converted array
 */
function turnitintool_overallgrade_old($turnitintool,$usersubmissions,$userid,$parts,$scale) {
    $overallgrade=NULL;
    $i=1;
    foreach ($parts as $part) {
        $weightarray[$part->id]=$part->maxmarks;
    }
    $overallweight=array_sum($weightarray);
    if ($turnitintool->grade<0) { // Scale in use
        $maxgrade=count(explode(",",$scale->scale));
    } else {
        $maxgrade=$turnitintool->grade;
    }
    foreach ($usersubmissions[$userid] as $usersubmission) {
        if (!is_nan($usersubmission->submission_grade) AND !is_null($usersubmission->submission_grade) AND $weightarray[$usersubmission->submission_part]!=0) {
            $overallgrade+=($usersubmission->submission_grade/$weightarray[$usersubmission->submission_part])
                    *($weightarray[$usersubmission->submission_part]/$overallweight)
                    *$maxgrade;
        }
    }
    if (!is_null($overallgrade) AND $turnitintool->grade<0) {
        return ($overallgrade==0) ? 1 : ceil($overallgrade);
    } else {
        return (!is_nan($overallgrade) AND !is_null($usersubmission->submission_grade)) ? number_format($overallgrade,1) : '-';
    }
}

/**
 * Calculates and returns the overall grade for this activity
 *
 * @param array $inboxarray Grade array for this calculation keyed by partid
 * @param array $grade Max grade for this Turnitin activity
 * @param object $parts turnitintool_parts DB data transfer object
 * @param object $scale turnitintool_parts DB data transfer object
 * @return object The converted array
 */
function turnitintool_overallgrade($inboxarray,$maxgrade,$parts,$scale) {
    $overallgrade=NULL;
    foreach ( $parts as $part ) {
        $weightarray[ $part->id ] = $part->maxmarks;
    }
    $overallweight = array_sum( $weightarray );
    if ( $maxgrade < 0 ) { // Scale in use
        $maxgrade = count( explode( ",", $scale->scale ) );
    }
    foreach ( $inboxarray as $inboxrow ) {
        if ( !is_nan($inboxrow->submission_grade) AND !is_null($inboxrow->submission_grade) AND $weightarray[$inboxrow->submission_part] != 0 ) {
            $overallgrade += ( $inboxrow->submission_grade / $weightarray[$inboxrow->submission_part] )
                    * ( $weightarray[$inboxrow->submission_part] / $overallweight )
                    * $maxgrade;
        }
    }
    if ( !is_null( $overallgrade ) AND gettype( $scale ) == 'object' ) {
        return ( $overallgrade == 0 ) ? 1 : ceil( $overallgrade );
    } else {
        return ( !is_nan( $overallgrade ) AND !is_null( $overallgrade ) ) ? number_format( $overallgrade, 1 ) : '-';
    }
}

/**
 * Outputs the Notes / Submission Comments screen
 *
 * @global object
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param var $view The Submission ID that the comments are attached to
 * @param array $post The posted comments from the comments edit / add form
 * @return string Outputs the screen contents
 */
function turnitintool_view_notes($cm,$turnitintool,$view,$post) {
    global $CFG,$USER;
    $output='';
    if (!$submission=turnitintool_get_record('turnitintool_submissions','id',$view)) {
        turnitintool_print_error('submissiongeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }
    if ($submission->userid==$USER->id OR has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        if ($comments=turnitintool_get_records_select('turnitintool_comments',"submissionid=".$submission->id." AND deleted=0")) {
            foreach ($comments as $comment) {

                $commentuser=turnitintool_get_moodleuser($comment->userid,NULL,__FILE__,__LINE__);

                $drawcomment=true;
                if (isset($post['action']) AND ($post['action']=='view' OR $post['action']=='edit') AND $post['comment']!=$comment->id) {
                    $drawcomment=false;
                } else {
                    $output.=turnitintool_box_start('generalbox boxwidthwide boxaligncenter eightyfive','notes',true);

                    if ($submission->submission_unanon OR !has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
                        $commentname=$commentuser->firstname.' '.$commentuser->lastname;
                    } else {
                        $commentname=get_string('anonenabled','turnitintool');
                    }

                    $output.='<div class="commentBlock"><div class="commentLeft"><b>'.get_string('commentby','turnitintool').
                            ':</b> '.$commentname.'</div>';
                    $output.='<div class="commentRight"><b>'.get_string('posted','turnitintool').':</b> '.userdate($comment->dateupdated,get_string('strftimerecentfull','langconfig')).'</div>';
                    $output.='<hr class="commentRule" />';
                    $output.='<div class="commentComments">'.nl2br($comment->commenttext).'</div>';
                    $output.='<br />
                    <div class="clearBlock">&nbsp;</div>';
                    if ($comment->userid==$USER->id AND ($comment->dateupdated>=time()-$turnitintool->commentedittime
                                    OR
                                    has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id)))) {
                        $fnd = array("\n","\r");
                        $rep = array('\n','\r');
                        $output.='<div class="commentBottom">
                        <form action="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=notes&s='.$submission->id.
                                '" class="commentLeft" method="POST" onsubmit="return confirm(\''.str_replace($fnd, $rep, get_string('deletecommentconfirm','turnitintool')).'\')">
                        <input name="action" value="delete" type="hidden" />
                        <input name="comment" value="'.$comment->id.'" type="hidden" />
                        <input name="submitted" value="'.get_string('deletecomment','turnitintool').'" type="submit" />
                        </form>';
                        $output.='<form action="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=notes&s='.$submission->id.'" method="POST" class="commentRight">
                        <input name="action" value="view" type="hidden" />
                        <input name="comment" value="'.$comment->id.'" type="hidden" />
                        <input name="submitted" value="'.get_string('editcomment','turnitintool').'" type="submit" />
                        </form>';
                        if (!has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
                            $output.='<span class="editNotice">'.get_string('edituntil','turnitintool').' '.userdate($comment->dateupdated+$turnitintool->commentedittime,get_string('strftimerecentfull','langconfig')).'</span>';
                        }
                        $output.='</div>
                        <div class="clearBlock">&nbsp;</div>';
                    }
                    $output.='</div>';
                    $output.=turnitintool_box_end(true);
                }
            }
        } else {
            $output.=turnitintool_box_start('generalbox boxwidthwide boxaligncenter eightyfive','notes',true);
            $output.=get_string('nocomments','turnitintool');
            $output.=turnitintool_box_end(true);
        }
    } else {
        turnitintool_print_error('permissiondeniederror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }
    return $output;
}

/**
 * Outputs the Notes / Submission for for editting / adding comments
 *
 * @global object
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param var $view The Submission ID that the comments are attached to
 * @param array $post The posted comments from the comments edit / add form
 * @param array $notice An array that contains error data from the posted form
 * @return string Outputs the comment add / edit form
 */
function turnitintool_addedit_notes($cm,$turnitintool,$view,$post,$notice) {
    global $CFG,$USER;
    $output='';
    if (!$submission=turnitintool_get_record('turnitintool_submissions','id',$view)) {
        turnitintool_print_error('submissiongeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }
    if ($submission->userid==$USER->id OR has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        $title=get_string('addeditcomment','turnitintool');
        $action='<input name="action" value="add" type="hidden" />';
        if (isset($post['action']) AND ($post['action']=='view' OR $post['action']=='edit')) {
            if (!$comment=turnitintool_get_record('turnitintool_comments','id',$post["comment"])) {
                turnitintool_print_error('submissiongeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }
            $action='<input name="action" value="edit" type="hidden" /><input name="comment" value="'.$post["comment"].'" type="hidden" />';
            $comments=$comment->commenttext;
        } else if (isset($post["comments"])) {
            $comments=$post["comments"];
        } else {
            $comments='';
        }
        $output.=turnitintool_box_start('generalbox boxwidthwide boxaligncenter eightyfive','notes',true);
        $output.='<b>'.$title.'</b><br />';

        $table = new stdClass();
        $table->width='100%';
        $table->tablealign='center';
        $table->class='uploadtable';

        $cell[0] = new stdClass();
        $cell[0]->data=get_string('comment','turnitintool').' (<span id="charsBlock">'.strlen($comments).'/'.$turnitintool->commentmaxsize.'</span> chars)';
        $cell[0]->class='cell c0';
        $cell[1] = new stdClass();
        $cell[1]->data='<textarea class="submissionText" name="comments" onkeyup="turnitintool_countchars(this,\'charsBlock\','.$turnitintool->commentmaxsize.',\''.
                get_string('maxcommentjserror','turnitintool',$turnitintool->commentmaxsize).'\')" onclick="turnitintool_countchars(this,\'charsBlock\','.
                $turnitintool->commentmaxsize.',\''.get_string('maxcommentjserror','turnitintool',$turnitintool->commentmaxsize).'\')">'.$comments.'</textarea>';
        $cell[1]->class='cell c1';

        $table->rows[0] = new stdClass();
        $table->rows[0]->cells=$cell;
        unset($cell);

        $cell[0] = new stdClass();
        $cell[0]->data='&nbsp;';
        $cell[0]->class='cell c0';
        $cell[1] = new stdClass();
        $cell[1]->data=$action.'<a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=notes&s='.$submission->id.'"><input name="cancel" value="Cancel" type="button" /></a>
        <input name="submitted" value="'.$title.'" type="submit" />';
        $cell[1]->class='cell c1';

        $table->rows[1] = new stdClass();
        $table->rows[1]->cells=$cell;
        unset($cell);

        $output.='<form action="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=notes&s='.$submission->id.'" method="post">';
        $output.=turnitintool_print_table($table,true);
        $output.='</form>';

        $output.=turnitintool_box_end(true);
    } else {
        turnitintool_print_error('permissiondeniederror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }
    return $output;
}

/**
 * Processes the comments entered in the add / edit comments form
 *
 * @global object
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object is for this activity
 * @param var $view The Submission ID that the comments are attached to
 * @param array $post The posted comments from the comments edit / add form
 * @return array Returns the error array used as $notice in the other comments functions
 */
function turnitintool_process_notes($cm,$turnitintool,$view,$post) {
    global $CFG,$USER;
    $output=array();
    if (!$submission=turnitintool_get_record('turnitintool_submissions','id',$view)) {
        turnitintool_print_error('submissiongeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }
    if ($submission->userid==$USER->id OR has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) { // Revalidate user

        $isgrader=has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id));

        if (isset($post["action"]) AND $post["action"]!="view") {
            $comment=new object();
            if (($post["action"]=="edit" OR $post["action"]=="delete") AND !$comment=turnitintool_get_record('turnitintool_comments','id',$post["comment"])) {
                turnitintool_print_error('commentgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }

            if ($post["action"]=="delete" AND (($comment->userid==$USER->id AND $comment->dateupdated>=time()-$turnitintool->commentedittime) OR $isgrader)) {
                turnitintool_delete_records('turnitintool_comments','id',$post["comment"]);
                turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=notes&s='.$submission->id);
                exit();
            } else if (($post["action"]=="edit" AND (($comment->userid==$USER->id AND $comment->dateupdated>=time()-$turnitintool->commentedittime) OR $isgrader)) OR $post["action"]=="add") {
                if (empty($post["comments"])) {
                    $output['error']=true;
                    $output['message']=get_string('nocommenterror','turnitintool');
                } else if (strlen($post["comments"])>$turnitintool->commentmaxsize) {
                    $input = new stdClass();
                    $input->actual=strlen($post["comments"]);
                    $input->allowed=$turnitintool->commentmaxsize;
                    $output['error']=true;
                    $output['message']=get_string('maxcommenterror','turnitintool',$input);
                } else if ($post["action"]=="edit") {
                    $update = new stdClass();
                    $update->id=$post["comment"];
                    $update->commenttext=strip_tags($post["comments"]);
                    $update->dateupdated=time();
                    turnitintool_update_record('turnitintool_comments',$update);
                    turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=notes&s='.$submission->id);
                    exit();
                } else {
                    $insert = new stdClass();
                    $insert->submissionid=$submission->id;
                    $insert->userid=$USER->id;
                    $insert->commenttext=strip_tags($post["comments"]);
                    $insert->dateupdated=time();
                    $insert->deleted=0;
                    turnitintool_insert_record('turnitintool_comments',$insert);
                    turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=notes&s='.$submission->id);
                    exit();
                }
            } else {
                turnitintool_print_error('permissiondeniederror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }
        }

    } else {
        turnitintool_print_error('permissiondeniederror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }
    return $output;
}

/**
 * Returns the Moodle User Data object for a user regardless of wether they have been deleted or not
 *
 * @param var $userid The moodle userid
 * @param var $arg1 The optional parameter $a for the print_error call
 * @param var $arg2 The passed in __FILE__ the error occured on if an error occurs
 * @param var $arg3 The passed in __LINE__ the error occured on if an error occurs
 * @return object A properly built Moodle User Data object with complete rebuilt email address
 */
function turnitintool_get_moodleuser($userid,$arg1=NULL,$arg2=NULL,$arg3=NULL) {
    if (!$user=turnitintool_get_record('user','id',$userid)) {
        turnitintool_print_error('usergeterror','turnitintool',NULL,$arg1,$arg2,$arg3);
        exit();
    }
    // Moodle 2.0 replaces email with a hash on deletion, moodle 1.9 deletes the email address check both
    if ( empty( $user->email ) OR strpos( $user->email, '@' ) === false ) {
        $split=explode('.',$user->username);
        array_pop($split);
        $user->email=join('.',$split);
    }
    return $user;
}

/**
 * Processes the options that were set and submitted via the options screen
 *
 * @global object
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param array $post The POST array that has been submitted via the form
 * @return string Returns the notice displaying whether the Options set suceeded or failed
 */
function turnitintool_process_options($cm,$turnitintool,$post) {
    global $CFG, $turnitintool;
    $turnitintool->autosubmission=$post['autosubmission'];
    $turnitintool->shownonsubmission=$post['shownonsubmission'];
    if (isset($post['usegrademark'])) {
        $turnitintool->usegrademark=$post['usegrademark'];
    }
    $turnitintool->gradedisplay=$post['gradedisplay'];
    $turnitintool->autoupdates=$post['autoupdates'];
    $turnitintool->commentedittime=$post['commentedittime'];
    $turnitintool->commentmaxsize=$post['commentmaxsize'];
    if (turnitintool_update_record('turnitintool',$turnitintool,false)) {
        return get_string('optionsupdatesaved','turnitintool');
    } else {
        return get_string('optionsupdateerror','turnitintool');
    }
}

/**
 * Outputs the options screen that is used to set the global options for the activity
 *
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @return string Output of the options screen form
 */
function turnitintool_view_options($cm,$turnitintool) {
    global $CFG;
    $output='<form action="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=options" method="post">';
    $output.='<input name="submitted" type="hidden" value="1" />';

    $output.=turnitintool_box_start('generalbox boxwidthwide boxaligncenter eightyfive','notes',true);

    $output.='<fieldset class="clearfix"><legend>'.get_string('generalsettings','turnitintool').'</legend>';

    $table = new stdClass();
    $table->width='100%';
    $table->tablealign='center';
    $table->class='optionstable1';
    $table->class='uploadtable';

    $selected=array('1'=>'','0'=>'');
    $selected[$turnitintool->autosubmission]=' selected';

    $row = 0;

    unset($cell);
    $cell[0] = new stdClass();
    $cell[0]->class='cell c0';
    $cell[0]->data=get_string('autosubmit','turnitintool').turnitintool_help_icon('autosubmit',get_string('autosubmit','turnitintool'),'turnitintool',true,false,'',true);
    $cell[1] = new stdClass();
    $cell[1]->class='cell c1';
    $cell[1]->data='<select name="autosubmission" class="formwide">';

    $cell[1]->data.='<option label="'.get_string('autosubmiton','turnitintool').'" value="1"'.$selected['1'].'>'.get_string('autosubmiton','turnitintool').'</option>';

    $cell[1]->data.='<option label="'.get_string('autosubmitoff','turnitintool').'" value="0"'.$selected['0'].'>'.get_string('autosubmitoff','turnitintool').'</option>';
    $cell[1]->data.='</select>';
    $table->rows[$row] = new stdClass();
    $table->rows[$row]->cells=$cell;
    $row++;
    unset($cell);

    $output.=turnitintool_print_table($table,true);
    $output.=turnitintool_box_end(true);

    $output.='</fieldset>';

    unset($table->rows);
    $output.=turnitintool_box_start('generalbox boxwidthwide boxaligncenter eightyfive','notes',true);

    $output.='<fieldset class="clearfix">';
    $output.='<legend>'.get_string('gradessettings','turnitintool').'</legend>';

    if ($CFG->turnitin_usegrademark) {

        unset($selected);
        $selected=array('1'=>'','0'=>'');
        $selected[$turnitintool->usegrademark]=' selected';

        unset($cell);
        $cell[0] = new stdClass();
        $cell[0]->class='cell c0';
        $cell[0]->data=get_string('turnitinusegrademark','turnitintool').turnitintool_help_icon(  'turnitinusegrademark',
                get_string('turnitinusegrademark','turnitintool'),
                'turnitintool',
                true,
                false,
                '',
                true
        );

        $cell[1] = new stdClass();
        $cell[1]->class='cell c1';
        $cell[1]->data='<select name="usegrademark" class="formwide">';
        $cell[1]->data.='<option label="'.get_string('yesgrademark','turnitintool').'" value="1"'.$selected['1'].'>'.get_string('yesgrademark','turnitintool').'</option>';
        $cell[1]->data.='<option label="'.get_string('nogrademark','turnitintool').'" value="0"'.$selected['0'].'>'.get_string('nogrademark','turnitintool').'</option>';
        $cell[1]->data.='</select>';
        $table->rows[$row] = new stdClass();
        $table->rows[$row]->cells=$cell;
        $row++;

    }

    unset($selected);
    $selected=array('1'=>'','2'=>'');
    $selected[$turnitintool->gradedisplay]=' selected';

    unset($cell);
    $cell[0] = new stdClass();
    $cell[0]->class='cell c0';
    $cell[0]->data=get_string('displaygradesas','turnitintool').turnitintool_help_icon('displaygradesas',get_string('displaygradesas','turnitintool'),'turnitintool',true,false,'',true);
    $cell[1] = new stdClass();
    $cell[1]->class='cell c1';
    $cell[1]->data='<select name="gradedisplay" class="formwide">';
    $cell[1]->data.='<option label="'.get_string('displaygradesaspercent','turnitintool').'" value="1"'.$selected['1'].'>'.get_string('displaygradesaspercent','turnitintool').'</option>';
    $cell[1]->data.='<option label="'.get_string('displaygradesasfraction','turnitintool').'" value="2"'.$selected['2'].'>'.get_string('displaygradesasfraction','turnitintool').'</option>';
    $cell[1]->data.='</select>';
    $table->rows[$row] = new stdClass();
    $table->rows[$row]->cells=$cell;
    $row++;

    unset($selected);
    $selected=array('1'=>'','0'=>'');
    $selected[$turnitintool->autoupdates]=' selected';

    unset($cell);
    $cell[0] = new stdClass();
    $cell[0]->class='cell c0';
    $cell[0]->data=get_string('autorefreshgrades','turnitintool').turnitintool_help_icon('autorefreshgrades',get_string('autorefreshgrades','turnitintool'),'turnitintool',true,false,'',true);
    $cell[1] = new stdClass();
    $cell[1]->class='cell c1';
    $cell[1]->data='<select name="autoupdates" class="formwide">';
    $cell[1]->data.='<option label="'.get_string('yesgrades','turnitintool').'" value="1"'.$selected['1'].'>'.get_string('yesgrades','turnitintool').'</option>';
    $cell[1]->data.='<option label="'.get_string('nogrades','turnitintool').'" value="0"'.$selected['0'].'>'.get_string('nogrades','turnitintool').'</option>';
    $cell[1]->data.='</select>';
    $table->rows[$row] = new stdClass();
    $table->rows[$row]->cells=$cell;
    $row++;

    unset($selected);
    $selected=array('1'=>'','0'=>'');
    $selected[$turnitintool->shownonsubmission]=' selected';

    unset($cell);
    $cell[0] = new stdClass();
    $cell[0]->class='cell c0';
    $cell[0]->data=get_string('submissionlist','turnitintool').turnitintool_help_icon('submissionlist',get_string('submissionlist','turnitintool'),'turnitintool',true,false,'',true);
    $cell[1] = new stdClass();
    $cell[1]->class='cell c1';
    $cell[1]->data='<select name="shownonsubmission" class="formwide">';

    $cell[1]->data.='<option label="'.get_string('shownonsubmissions','turnitintool').'" value="1"'.$selected['1'].'>'.get_string('shownonsubmissions','turnitintool').'</option>';

    $cell[1]->data.='<option label="'.get_string('showonlysubmissions','turnitintool').'" value="0"'.$selected['0'].'>'.get_string('showonlysubmissions','turnitintool').'</option>';
    $cell[1]->data.='</select>';
    $table->rows[$row] = new stdClass();
    $table->rows[$row]->cells=$cell;
    $row++;

    $output.=turnitintool_print_table($table,true);
    $output.=turnitintool_box_end(true);

    $output.='</fieldset>';

    unset($table->rows);
    $row = 0;
    $output.=turnitintool_box_start('generalbox boxwidthwide boxaligncenter eightyfive','notes',true);

    $output.='<fieldset class="clearfix">';
    $output.='<legend>'.get_string('commentssettings','turnitintool').'</legend>';

    unset($cell);

    $selected=array('0'=>'','300'=>'','600'=>'','900'=>'','1200'=>'','1500'=>'','1800'=>'','3600'=>'','7200'=>'','10800'=>'','14400'=>'','18000'=>'','21600'=>'','43200'=>'','86400'=>'');
    $selected[$turnitintool->commentedittime]=' selected';

    $cell[0] = new stdClass();
    $cell[0]->class='cell c0';
    $cell[0]->data=get_string('commenteditwindow','turnitintool').turnitintool_help_icon('commenteditwindow',get_string('commenteditwindow','turnitintool'),'turnitintool',true,false,'',true);
    $cell[1] = new stdClass();
    $cell[1]->class='cell c1';
    $cell[1]->data='<select name="commentedittime" class="formwide">';
    $cell[1]->data.='<option label="'.get_string('nolimit','turnitintool').'" value="0"'.$selected['0'].'>'.get_string('nolimit','turnitintool').'</option>';
    $cell[1]->data.='<option label="5 '.get_string('minutes','turnitintool').'" value="300"'.$selected['300'].'>5 '.get_string('minutes','turnitintool').'</option>';
    $cell[1]->data.='<option label="10 '.get_string('minutes','turnitintool').'" value="600"'.$selected['600'].'>10 '.get_string('minutes','turnitintool').'</option>';
    $cell[1]->data.='<option label="15 '.get_string('minutes','turnitintool').'" value="900"'.$selected['900'].'>15 '.get_string('minutes','turnitintool').'</option>';
    $cell[1]->data.='<option label="20 '.get_string('minutes','turnitintool').'" value="1200"'.$selected['1200'].'>20 '.get_string('minutes','turnitintool').'</option>';
    $cell[1]->data.='<option label="25 '.get_string('minutes','turnitintool').'" value="1500"'.$selected['1500'].'>25 '.get_string('minutes','turnitintool').'</option>';
    $cell[1]->data.='<option label="30 '.get_string('minutes','turnitintool').'" value="1800"'.$selected['1800'].'>30 '.get_string('minutes','turnitintool').'</option>';
    $cell[1]->data.='<option label="1 '.get_string('hours','turnitintool').'" value="3600"'.$selected['3600'].'>1 '.get_string('hours','turnitintool').'</option>';
    $cell[1]->data.='<option label="2 '.get_string('hours','turnitintool').'" value="7200"'.$selected['7200'].'>2 '.get_string('hours','turnitintool').'</option>';
    $cell[1]->data.='<option label="3 '.get_string('hours','turnitintool').'" value="10800"'.$selected['10800'].'>3 '.get_string('hours','turnitintool').'</option>';
    $cell[1]->data.='<option label="4 '.get_string('hours','turnitintool').'" value="14400"'.$selected['14400'].'>4 '.get_string('hours','turnitintool').'</option>';
    $cell[1]->data.='<option label="5 '.get_string('hours','turnitintool').'" value="18000"'.$selected['18000'].'>5 '.get_string('hours','turnitintool').'</option>';
    $cell[1]->data.='<option label="6 '.get_string('hours','turnitintool').'" value="21600"'.$selected['21600'].'>6 '.get_string('hours','turnitintool').'</option>';
    $cell[1]->data.='<option label="12 '.get_string('hours','turnitintool').'" value="43200"'.$selected['43200'].'>12 '.get_string('hours','turnitintool').'</option>';
    $cell[1]->data.='<option label="24 '.get_string('hours','turnitintool').'" value="86400"'.$selected['86400'].'>24 '.get_string('hours','turnitintool').'</option>';

    $cell[1]->data.='</select>';
    $table->rows[$row] = new stdClass();
    $table->rows[$row]->cells=$cell;
    $row++;

    unset($cell);

    $selected=array('100'=>'','200'=>'','300'=>'','400'=>'','500'=>'','600'=>'','700'=>'','800'=>'','900'=>'','1000'=>'','1100'=>'','1200'=>'','1300'=>'','1400'=>'','1500'=>'');
    $selected[$turnitintool->commentmaxsize]=' selected';

    $cell[0] = new stdClass();
    $cell[0]->class='cell c0';
    $cell[0]->data=get_string('maxcommentsize','turnitintool').turnitintool_help_icon('maxcommentsize',get_string('maxcommentsize','turnitintool'),'turnitintool',true,false,'',true);
    $cell[1] = new stdClass();
    $cell[1]->class='cell c1';
    $cell[1]->data='<select name="commentmaxsize" class="formwide">';

    for ($i=1;$i<=15;$i++) {
        $n=$i*100;
        $cell[1]->data.='<option label="'.$n.' '.get_string('characters','turnitintool').'" value="'.$n.'"'.$selected[$n].'>'.$n.' '.get_string('characters','turnitintool').'</option>';
    }

    $cell[1]->data.='</select>';
    $table->rows[$row] = new stdClass();
    $table->rows[$row]->cells=$cell;
    $row++;

    $output.=turnitintool_print_table($table,true);
    $output.=turnitintool_box_end(true);

    $output.='</fieldset>';

    unset($table->rows);
    $row = 0;

    $output.=turnitintool_box_start('generalbox boxwidthwide boxaligncenter eightyfive','notes',true);

    unset($cell);
    $cell[0] = new stdClass();
    $cell[0]->class='cell c0';
    $cell[0]->data='';
    $cell[1] = new stdClass();
    $cell[1]->class='cell c1';
    $cell[1]->data='<input type="submit" value="'.get_string('savechanges','turnitintool').'" />';
    $table->rows[$row] = new stdClass();
    $table->rows[$row]->cells=$cell;
    $row++;

    $output.=turnitintool_print_table($table,true);
    $output.=turnitintool_box_end(true);

    $output.='</form>';
    return $output;
}

/**
 * Outputs header links for the inbox table including the hi lo toggle for order bys
 *
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param string $title The title of the table header
 * @param boolean $hilo Shows if this is ordered high to low values
 * @param boolean $lohi Shows if this is ordered low to high values
 * @return string Output of the header link
 */
function turnitintool_doheaderlinks($cm,$turnitintool,$title,$hilo,$lohi) {
    global $CFG;
    $param_do=optional_param('do',null,PARAM_CLEAN);
    $param_ob=optional_param('ob',null,PARAM_CLEAN);
    $param_sh=optional_param('sh',null,PARAM_CLEAN);
    $param_fr=optional_param('fr',null,PARAM_CLEAN);
    $baselink=$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do='.$param_do;
    $baselink.=(!is_null($param_sh)) ? '&sh='.$param_sh : '';
    $baselink.=(!is_null($param_fr)) ? '&fr='.$param_fr : '';

    $headerlink='<a href="'.$baselink.'&ob=%%OB%%">'.$title.'</a>';

    if (isset($param_ob) AND $param_ob==$hilo) {
        $headerlink.=' <img src="pix/order_down.gif" class="ordericons" />';
        $headerlink=str_replace('%%OB%%',$lohi,$headerlink);
    } else if (isset($param_ob) AND $param_ob==$lohi) {
        $headerlink.=' <img src="pix/order_up.gif" class="ordericons" />';
        $headerlink=str_replace('%%OB%%',$hilo,$headerlink);
    } else if (!isset($param_ob) AND $hilo==1) {
        $headerlink.=' <img src="pix/order_down.gif" class="ordericons" />';
        $headerlink=str_replace('%%OB%%',$lohi,$headerlink);
    } else {
        $headerlink=str_replace('%%OB%%',$hilo,$headerlink);
    }

    // ORDER BY: 1: Av. Originality Score - Max-Min,
    //           2: Av. Originality Score - Min-Max,
    //           3: Av. Grade - Max-Min,
    //           4: Av. Grade - Min-Max,
    //           5: Modified - Earliest-Latest,
    //           6: Modified - Latest-Earliest,
    //           7: Students Last Name - A-Z,
    //           8: Student Last Name - Z-A

    return $headerlink;
}

/**
 * Processes the tutor request to reveal an anonymous students name
 *
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param array $post The POST values from the reveal student form
 */
function turnitintool_revealuser($cm,$turnitintool,$post,$loaderbar=null) {
    global $CFG;
    $anonid=$post["anonid"];
    // For the sake of anonymity we get the userid from the object id
    $submission=turnitintool_get_record('turnitintool_submissions','submission_objectid',$anonid);

    $reason=$post["reason"][$anonid];

    if ($reason==get_string('revealreason','turnitintool') OR empty($reason)) {
        turnitintool_print_error('revealerror','turnitintool',$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=allsubmissions',NULL,__FILE__,__LINE__);
        exit();
    } else {
        $owner=turnitintool_get_owner($turnitintool->course);
        $tii = new turnitintool_commclass(turnitintool_getUID($owner),$owner->firstname,$owner->lastname,$owner->email,2,$loaderbar);

        $tiipost = new stdClass();
        $tiipost->cid=turnitintool_getCID($turnitintool->course);
        $tiipost->paperid=$submission->submission_objectid;
        $tiipost->anon_reason=(strlen($reason)>1 AND strlen($reason)<6) ? str_pad($reason,6," ") : $reason;

        $input = new stdClass();
        $input->from=1;
        $input->to=1;
        $result=$tii->revealAnon($tiipost,get_string('updatestudent','turnitintool',$input));

        if ($tii->getRerror()) {
            $reason=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
            turnitintool_print_error('<strong>'.get_string('submissionupdateerror','turnitintool').'</strong><br />'.$reason,NULL,NULL,NULL,__FILE__,__LINE__);
            exit();
        }

        $update = new stdClass();
        $update->id=$submission->id;
        $update->submission_unanon=1;
        $update->submission_unanonreason=$reason;
        turnitintool_update_record('turnitintool_submissions',$update);
        unset($tii);
        unset($loaderbar);
    }
}
/**
 * Outputs submission inbox for student submissions (Teacher View)
 *
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param string $orderby The query string option for the ordering of the column
 * @return string Output of the tutor view submission inbox
 */
function turnitintool_view_all_submissions($cm,$turnitintool,$orderby='1') {
    global $CFG, $USER;

    $param_do=optional_param('do',null,PARAM_CLEAN);
    $param_reason=optional_param('reason',array(),PARAM_CLEAN);

    $module_group = turnitintool_module_group( $cm );
    $context = get_context_instance(CONTEXT_COURSE, $turnitintool->course);
    $studentusers = get_users_by_capability($context,'mod/turnitintool:submit','u.id,u.firstname,u.lastname','','','',$module_group,'',false);
    $studentuser_array = array_keys($studentusers);
    $scale=turnitintool_get_record('scale','id',$turnitintool->grade*-1);
    $parts=turnitintool_get_records_select('turnitintool_parts','turnitintoolid='.$turnitintool->id.' AND deleted != 1');
    $concat = turnitintool_sql_concat("COALESCE(u.id,0)","'-'","COALESCE(s.id,0)","'-'","COALESCE(s.submission_objectid,0)");

    $usifieldid = 'NULL';
    $usifield = '';
    $displayusi = 'false';
    if ( $CFG->turnitin_enablepseudo AND $CFG->turnitin_pseudolastname > 0 ) {
        $uf = turnitintool_get_record( 'user_info_field', 'id', $CFG->turnitin_pseudolastname );
        $usifield = $uf->name;
        $usifieldid = $CFG->turnitin_pseudolastname;
    }

    $group_in = join(',', $studentuser_array);
    $groupselect = ( $module_group != 0 ) ? "u.id IN ( select gm.userid from {groups_members} gm where gm.groupid = $module_group ) AND" : "";

    $query = "
SELECT
    $concat AS keyid,
    s.id AS id,
    u.id AS userid,
    u.firstname AS firstname,
    u.lastname AS lastname,
    ud.data AS usi,
    tu.turnitin_uid AS turnitin_uid,
    p.id AS partid,
    p.partname AS partname,
    p.dtstart AS dtstart,
    p.dtdue AS dtdue,
    p.dtpost AS dtpost,
    p.maxmarks AS maxmarks,
    t.name AS assignmentname,
    t.grade AS overallgrade,
    t.anon AS anon,
    t.id AS turnitintoolid,
    s.submission_part AS submission_part,
    s.submission_title AS submission_title,
    s.submission_type AS submission_type,
    s.submission_filename AS submission_filename,
    s.submission_objectid AS submission_objectid,
    s.submission_score AS submission_score,
    s.submission_grade AS submission_grade,
    s.submission_gmimaged AS submission_gmimaged,
    s.submission_status AS submission_status,
    s.submission_queued AS submission_queued,
    s.submission_attempts AS submission_attempts,
    s.submission_modified AS submission_modified,
    s.submission_parent AS submission_parent,
    s.submission_nmuserid AS submission_nmuserid,
    s.submission_nmfirstname AS submission_nmfirstname,
    s.submission_nmlastname AS submission_nmlastname,
    s.submission_unanon AS submission_unanon,
    s.submission_unanonreason AS submission_unanonreason,
    s.submission_transmatch AS submission_transmatch
FROM {turnitintool_submissions} s
    LEFT JOIN
        {user} u ON u.id = s.userid
    LEFT JOIN
        {turnitintool_parts} p ON p.id = s.submission_part
    LEFT JOIN
        {turnitintool} t ON t.id = p.turnitintoolid
    LEFT JOIN
        {turnitintool_users} tu ON u.id = tu.userid
    LEFT JOIN
        {user_info_data} ud ON u.id = ud.userid AND ud.fieldid = $usifieldid
WHERE
    $groupselect
    s.turnitintoolid = ".$turnitintool->id."
ORDER BY s.submission_grade DESC
";

    $records = turnitintool_get_records_sql( $query );
    $records = is_array( $records ) ? $records : array();

    $userrows = array();
    $subuser_array = array();
    $submissionids = array();
    foreach ( $records as $record ) {
        $turnitin_uid = ( !is_null( $record->turnitin_uid ) ) ? $record->turnitin_uid : $record->submission_nmuserid;
        $key = $record->userid . '-' . $turnitin_uid;
        $record->nonmoodle = ( $record->submission_nmuserid ) ? true : false;
        $userrows[$key][]=$record;
        if ( !is_null( $record->id ) ) $submissionids[] = $record->id;
        if ( !is_null( $record->userid ) ) $subuser_array[] = $record->userid;
    }

    $comments = array();
    if ( count( $submissionids ) > 0 ) {
        $submission_string = join( ',', $submissionids );
        $comments = turnitintool_get_records_sql( 'SELECT submissionid, count( id ) AS count FROM {turnitintool_comments} WHERE deleted = 0 AND submissionid IN ( '.$submission_string.' ) GROUP BY submissionid' );
    }

    $nosubuser_array = ( !$turnitintool->shownonsubmission OR $turnitintool->anon ) ? array() : array_diff( $studentuser_array, $subuser_array );

    foreach ( $nosubuser_array as $user ) {
        $key = $studentusers[$user]->id . '-' . 0;
        $record = new stdClass();
        $record->userid = $studentusers[$user]->id;
        $record->firstname = $studentusers[$user]->firstname;
        $record->lastname = $studentusers[$user]->lastname;
        $userrows[$key][]=$record;
    }

    $table = new stdClass();
    $table->style = 'display: none;';
    $table->width = '100%';
    $table->id = 'inboxTable';
    $table->class = 'gradeTable';
    $table->tablealign = 'center';
    $n = 0;
    $table->rows[0] = new stdClass();
    $table->rows[0]->class = 'header';
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' iconcell';
    $table->rows[0]->hcells[$n]->data = '<div>&nbsp;</div>';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' iconcell';
    $table->rows[0]->hcells[$n]->data = '<div>&nbsp;</div>';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c'.$n.' namecell';
    $table->rows[0]->hcells[$n]->data = '<div>'.get_string( 'submissionstudent', 'turnitintool' ).'</div>';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' markscell';
    $table->rows[0]->hcells[$n]->data = '&nbsp;';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' markscell';
    $table->rows[0]->hcells[$n]->data = '<div>&nbsp;'.$usifield.'</div>';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' markscell';
    $table->rows[0]->hcells[$n]->data = '<div>'.get_string( 'objectid', 'turnitintool' ).'</div>';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' datecell';
    $table->rows[0]->hcells[$n]->data = '<div>'.get_string( 'posted', 'turnitintool' ).'</div>';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' markscell';
    $table->rows[0]->hcells[$n]->data = '&nbsp;';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' markscell';
    $table->rows[0]->hcells[$n]->data = '<div>'.get_string( 'submissionorig', 'turnitintool' ).'</div>';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' markscell';
    $table->rows[0]->hcells[$n]->data = '&nbsp;';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' markscell';
    $table->rows[0]->hcells[$n]->data = '&nbsp;';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' markscell';
    $table->rows[0]->hcells[$n]->data = '<div>'.get_string( 'submissiongrade', 'turnitintool' ).'</div>';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' iconcell';
    $table->rows[0]->hcells[$n]->data = '<div>&nbsp;</div>';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' iconcell';
    $table->rows[0]->hcells[$n]->data = '<div>&nbsp;</div>';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' iconcell';
    $table->rows[0]->hcells[$n]->data = '<div>&nbsp;</div>';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' iconcell';
    $table->rows[0]->hcells[$n]->data = '<div>&nbsp;</div>';
    $n++;
    $table->rows[0]->hcells[$n] = new stdClass();
    $table->rows[0]->hcells[$n]->class = 'header c' . $n . ' iconcell';
    $table->rows[0]->hcells[$n]->data = '<div>&nbsp;</div>';
    $n++;

    $i = 1;

    // $postdatepassed controls whether the inbox displays part view or student name view.
    $postdatepassed = 0;

    // Get the parts, count how many are passed the post date
    $parts=turnitintool_get_records_select('turnitintool_parts',"turnitintoolid=".$turnitintool->id." AND deleted=0");
    $postdate_count = 0;

    foreach ( $parts as $part ) {
        if ( $part->dtpost < time() ){
            $postdate_count++;
        }
    }

    // If every part has passed the due date, switch to student name view.
    if ($postdate_count == count($parts)) {
        $postdatepassed = 1;
    }

    foreach ( $userrows as $key => $userrow ) {

        if ( isset( $userrow[0]->id ) ) $overall_grade = turnitintool_overallgrade( $userrow, $turnitintool->grade, $parts, $scale );
        $rowcount = ( count( $userrow ) == 1 && !isset( $userrow[0]->id ) ) ? 0 : count( $userrow );

        $submissionstring=( $rowcount == 1 ) ? get_string('submission','turnitintool') : get_string('submissions','turnitintool');
        if ( is_null( $userrow[0]->firstname ) ) {
            $student='<i>'.$userrow[0]->submission_nmlastname.', '.$userrow[0]->submission_nmfirstname.
                    ' ('.get_string('nonmoodleuser','turnitintool').')</i> - ('.$rowcount.
                    ' '.$submissionstring.')';
        } else {
            $student='<b><a href="'.$CFG->wwwroot.'/user/view.php?id='.
                    $userrow[0]->userid.'&course='.$turnitintool->course.'">'.$userrow[0]->lastname.', '.$userrow[0]->firstname.
                    '</a></b> - ('.$rowcount.' '.$submissionstring.')';
        }

        foreach ( $userrow as $submission ) {
            $submission_postdate = ( isset( $submission->submission_part ) AND $parts[$submission->submission_part]->dtpost < time() ) ? 0 : 1;

            $displayusi = ( ( $turnitintool->anon == 1 AND !$postdatepassed )
                    OR !isset( $CFG->turnitin_enablepseudo ) OR $CFG->turnitin_enablepseudo === "0" ) ? 'false' : 'true';

            $n = 0;
            if ( !isset( $submission->id ) OR is_null( $submission->id ) ) {
                // Do blank user line and continue
                $nmuserid = ( isset( $submission->submission_nmuserid ) ) ? $submission->submission_nmuserid : 0;
                $grouprow=$submission->userid . '-' . $nmuserid;
                $table->rows[$i]->cells[$n] = new stdClass();
                $table->rows[$i]->cells[$n]->class = 'cell c' . $n;
                $table->rows[$i]->cells[$n]->data = $grouprow;
                $n++;
                $table->rows[$i]->cells[$n] = new stdClass();
                $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' hide';
                $table->rows[$i]->cells[$n]->data = $student;
                $n++;
                for ( $j = 0; $j < 15; $j++ ) {

                    $table->rows[$i]->cells[$n] = new stdClass();
                    $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' hide';
                    if ( $j == 4 ) {
                        $output = '00/00/00, 00:00:00';
                    } else {
                        $output = '&nbsp;&nbsp;';
                    }
                    $table->rows[$i]->cells[$n]->data = $output;
                    $n++;
                }
                $i++;
                continue;
            }


            $entryCount[$submission->userid]=(!isset($entryCount[$submission->userid]))
                        ? 1 : $entryCount[$submission->userid]+1;
            $i++;
            $lastclass=($i==$rowcount) ? ' lastmark' : ' leftmark';

            // Do Sort Row, Part Name if Anon and User Name if not
            if ( $submission->anon AND !$postdatepassed ) {
                $grouprow=$submission->partid;
            } else {
                $grouprow=$submission->userid . '-' . $submission->submission_nmuserid;
            }
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n;
            $table->rows[$i]->cells[$n]->data = $grouprow;
            $n++;

            // Do Sort header table
            if ( $submission->anon AND !$postdatepassed ) {
                $grouptable='<b>'.$submission->partname.'</b>';
            } else {
                $grouptable=$student;
            }

            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' hide';
            $table->rows[$i]->cells[$n]->data = $grouptable;
            $n++;

            // Do Submission Filelink / Anon Button Column
            $filelink=turnitintool_get_filelink($cm,$turnitintool,$submission);
            $doscript='';
            if (!empty($submission->submission_objectid)) {
                $doscript=' onclick="screenOpen(\''.$filelink.'\',\''.$submission->id.'\',\''.$turnitintool->autoupdates.'\');return false;"';
            }

            $length = 60;
            $truncate = (strlen($submission->submission_title)>$length)
                    ? substr($submission->submission_title,0,$length).'...'
                    : $submission->submission_title;


            if ( !$turnitintool->anon OR $postdatepassed ) {

                 $submission_link = '<b>'.$submission->partname.'</b>: <a href="'.$filelink.'" target="_blank" class="fileicon"'
                        .$doscript.' title="'.$submission->submission_title.'">'.$truncate.'</a>';

            } else if ( $submission->submission_unanon AND $turnitintool->anon ) {

                $submission_link = '<b><a href="'.$CFG->wwwroot.'/user/view.php?id='.
                    $userrow[0]->userid.'&course='.$turnitintool->course.'">'.$userrow[0]->lastname.', '.$userrow[0]->firstname.
                    '</a></b>: ';
                $submission_link .= '<a href="'.$filelink.'" target="_blank" class="fileicon"'
                        .$doscript.' title="'.$submission->submission_title.'">'.$truncate.'</a>';

            } else if ( $turnitintool->anon AND !$postdatepassed ) {

                $reason=(isset($param_reason[$submission->submission_objectid])) ? $param_reason[$submission->submission_objectid] : get_string('revealreason','turnitintool');
                $submission_link = '<a href="'.$filelink.'" target="_blank" class="fileicon"'
                        .$doscript.' title="'.$submission->submission_title.'" style="line-height: 1.8em;">'.$truncate.'</a><br /><span id="anonform_'.$submission->submission_objectid.
                        '" style="display: none;"><form action="'.$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.
                        '&do=allsubmissions" method="POST" class="" onsubmit="return anonValidate(this.reason);">&nbsp;&nbsp;&nbsp;<input id="reason" name="reason['.
                        $submission->submission_objectid.']" value="'.$reason.'" type="text" onclick="this.value=\'\';" /><input id="anonid" name="anonid" value="'.
                        $submission->submission_objectid.'" type="hidden" />&nbsp;<input value="'.get_string('reveal','turnitintool').
                        '" type="submit" /></form></span><button id="studentname_'.$submission->submission_objectid.
                        '" onclick="document.getElementById(\'anonform_'.$submission->submission_objectid.'\').style.display = \'block\';this.style.display = \'none\';">'.
                        get_string('anonenabled','turnitintool').'</button>';

            }

            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . $lastclass;
            $table->rows[$i]->cells[$n]->data = $submission_link;
            $n++;

            // Output USI if required
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' markscell';
            $table->rows[$i]->cells[$n]->data = ( !isset($submission->usi) OR ( $submission->anon AND !$postdatepassed ) ) ? '&nbsp;' : $submission->usi;
            $n++;
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' markscell';
            $table->rows[$i]->cells[$n]->data = '&nbsp;';
            $n++;

            // Do Paper ID column
            $objectid=(is_null($submission->submission_objectid)
                                OR empty($submission->submission_objectid))
                                    ? '-' : $submission->submission_objectid;

            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' markscell';
            $table->rows[$i]->cells[$n]->data = $objectid;
            $n++;

            // Do Submission to Turnitin Form
            $modified='-';
            if (empty($submission->submission_objectid) AND $turnitintool->autosubmission) {
                $modified='<div class="submittoLinkSmall"><img src="'.$CFG->wwwroot.'/mod/turnitintool/icon.gif" /><a href="'.$CFG->wwwroot.
                        '/mod/turnitintool/view.php'.'?id='.$cm->id.'&up='.$submission->id.'">'.get_string('submittoturnitin','turnitintool').'</a></div>';
            } else if (!is_null($submission->id)) {
                $modified=(empty($submission->submission_objectid)) ? '-' : userdate($submission->submission_modified,get_string('strftimedatetimeshort','langconfig'));
                if ($submission->submission_modified>$submission->dtdue) {
                    $modified='<span style="color: red;">'.$modified.'</span>';
                }
            }
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' datecell';
            $table->rows[$i]->cells[$n]->data = $modified;
            $n++;

            // Get originality score if available
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' markscell';
            $table->rows[$i]->cells[$n]->data = $submission->submission_score;
            $n++;
            $score = turnitintool_draw_similarityscore($cm,$turnitintool,$submission);
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' markscell';
            $table->rows[$i]->cells[$n]->data = $score;
            $n++;

            // Get grade if available
            $grade=turnitintool_dogradeoutput($cm,$turnitintool,$submission,$submission->dtdue,$submission->dtpost,$submission->maxmarks);
            $grade='<form action="'.$CFG->wwwroot.
                    '/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=allsubmissions" method="POST">'.$grade.'</form>';
            // Raw grade goes in hidden column for sorting
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' markscell';

            if ($turnitintool->grade==0 OR $overall_grade==='-') {
                $overall_grade='-';
            } else if ($turnitintool->grade < 0) { // Scale
                $scalearray=explode(",",$scale->scale);
                // Array is zero indexed
                // Scale positions are from 1 upward
                $index = $overall_grade-1;
                $overall_grade = ( $index < 0 ) ? $scalearray[0] : $scalearray[$index];
            } else if ($turnitintool->gradedisplay==2) { // 2 is fraction
                $overall_grade.='/'.$turnitintool->grade;
            } else if ($turnitintool->gradedisplay==1) { // 1 is percentage
                $overall_grade=round($overall_grade/$turnitintool->grade*100,1).'%';
            }
            $overall_grade = ( $turnitintool->anon AND !$postdatepassed ) ? '-'.$submission->submission_part : $overall_grade;
            $table->rows[$i]->cells[$n]->data = $overall_grade;
            $n++;

            $subgrade = $submission->submission_grade;
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' markscell';
            $table->rows[$i]->cells[$n]->data = ( !is_null( $subgrade ) AND $subgrade != '-' ) ? $subgrade : 0;
            $n++;
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' markscell';
            $table->rows[$i]->cells[$n]->data = $grade;
            $n++;

            // Get Student View indicator
            $grademarkurl = $CFG->wwwroot . '/mod/turnitintool/view.php?id=' . $cm->id . '&jumppage=grade';
            $grademarkurl .= '&userid=' . $USER->id . '&utp=2&objectid=' . $submission->submission_objectid;
            $warn=($turnitintool->reportgenspeed>0 AND $submission->dtdue > time()) ? $warn=',\''.get_string('resubmissiongradewarn','turnitintool').'\'' : '';

            if ( $submission->submission_attempts > 0 ) {
                $cells['studentview']='<a href="' . $grademarkurl . '" title="' . get_string( 'student_read', 'turnitintool' ) . ' ' . userdate($submission->submission_attempts) . '" ';
                $cells['studentview'].=' onclick="screenOpen(this.href,\''.$submission->id.'\',\''.$turnitintool->autoupdates.'\''.$warn.');return false;"';
                $cells['studentview'].='><img style="position: relative; top: 4px;" src="'.$CFG->wwwroot.'/mod/turnitintool/pix/icon-student-read.png" class="tiiicons" /></a>';
            } else {
                $cells['studentview']='<a href="' . $grademarkurl . '" title="' . get_string( 'student_notread', 'turnitintool' ) . '" ';
                $cells['studentview'].=' onclick="screenOpen(this.href,\''.$submission->id.'\',\''.$turnitintool->autoupdates.'\''.$warn.');return false;"';
                $cells['studentview'].='><img style="position: relative; top: 4px;" src="'.$CFG->wwwroot.'/mod/turnitintool/pix/icon-dot.png" class="tiiicons" /></a>';
            }

            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' iconcell';
            $table->rows[$i]->cells[$n]->data = $cells['studentview'];
            $n++;

            // Get Feedback Icon if needed
            if (!$submission->nonmoodle) {
                $comment_count = ( isset($comments[$submission->id]) ) ? $comments[$submission->id]->count : 0;
                $notes=turnitintool_getnoteslink($cm,$turnitintool,$submission,$comment_count);
            } else {
                $notes='-';
            }
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' iconcell';
            $table->rows[$i]->cells[$n]->data = $notes;
            $n++;

            // Get Download Icon if needed
            if (!is_null($submission->submission_objectid)) {
                $downscript=' onclick="screenOpen(this.href,\''.$submission->id.'\',false,null,\'width=450,height=200\');return false;"';
                $download='<a href="'.turnitintool_get_filelink($cm,$turnitintool,$submission,$download=true).'" title="'.
                        get_string('downloadsubmission','turnitintool').'" target="_blank"'.$downscript.'><img src="pix/file-download.png" alt="'.
                        get_string('downloadsubmission','turnitintool').'" class="tiiicons" /></a>';
            } else {
                $download='';
            }
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' iconcell';
            $table->rows[$i]->cells[$n]->data = $download;
            $n++;

            // Get Refresh Icon if needed
            if (!is_null($submission->submission_objectid) && $submission->userid > 0) {
                $refresh='<a class="refreshrow" style="cursor: pointer;" id="refreshrow-'.$cm->id.'-'.$turnitintool->id.'-'.$submission->id.'-'.$submission->submission_objectid.'" title="'.
                        get_string('refresh','turnitintool').'"><img src="pix/refresh.gif" alt="'.
                        get_string('refresh','turnitintool').'" class="tiiicons" /></a>';
            } else {
                $refresh='';
            }
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' iconcell';
            $table->rows[$i]->cells[$n]->data = $refresh;
            $n++;

            // Get Delete Icon if needed
            $fnd = array("\n","\r");
            $rep = array('\n','\r');
            if (empty($submission->submission_objectid)) {
                $confirm=' onclick="return confirm(\''.str_replace($fnd, $rep, get_string('deleteconfirm','turnitintool')).'\');"';
            } else {
                $confirm=' onclick="return confirm(\''.str_replace($fnd, $rep, get_string('turnitindeleteconfirm','turnitintool')).'\')"';
            }

            $delete='<a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&delete='.$submission->id.'&do='.$param_do.
                        '"'.$confirm.' title="'.get_string('deletesubmission','turnitintool').'"><img src="pix/delete.png" alt="'.
                        get_string('deletesubmission','turnitintool').'" class="tiiicons" /></a>';
            $table->rows[$i]->cells[$n] = new stdClass();
            $table->rows[$i]->cells[$n]->class = 'cell c' . $n . ' iconcell';
            $table->rows[$i]->cells[$n]->data = $delete;
            $n++;
            $i++;
        }
    }

$sessionrefresh = (isset($_SESSION['updatedscores'][$turnitintool->id]) AND $_SESSION['updatedscores'][$turnitintool->id]>0) ? '' : 'refreshSubmissionsAjax();';

$output = "
<script type=\"text/javascript\">
    var users = ".json_encode($studentuser_array).";
    var message = '".get_string('turnitinenrollstudents','turnitintool')."';
    jQuery(document).ready(function($) {
        $.inboxTable.init( '".$cm->id."', ".$displayusi.", ".turnitintool_datatables_strings()." );
        jQuery('#loader').css( 'display', 'none' );
        $sessionrefresh
    });
</script>";

$output .= '
        <div class="tabLinks">
        <div style="display: none;" id="inboxNotice"><span style="background: url(pix/ajax-loader.gif) no-repeat left center;padding-left: 80px;">
        <span style="background: url(pix/ajax-loader.gif) no-repeat right center;padding-right: 80px;">'
                .get_string('turnitinloading','turnitintool').'</span></span></div>
            <a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=allsubmissions'.
        '&update=1" onclick="refreshSubmissionsAjax();return false;" class="rightcor"><img src="'.$CFG->wwwroot.'/mod/turnitintool/pix/refresh.gif" alt="'.
        get_string('turnitinrefreshsubmissions','turnitintool').'" class="tiiicons" /> '.
        get_string('turnitinrefreshsubmissions','turnitintool').'</a>
            <a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=allsubmissions'.
        '&enroll=1" onclick="enrolStudentsAjax( users, message );return false;" class="rightcor"><img src="'.$CFG->wwwroot.'/mod/turnitintool/pix/enrollicon.gif" alt="'.
        get_string('turnitinenrollstudents','turnitintool').'" class="tiiicons" /> '.
        get_string('turnitinenrollstudents','turnitintool').'</a>
        </div>';

if ( count( $table->rows ) == 1 ) { // If we only have one row it's a header and we found no data to display

    $output .= '<div style="padding: 18px; margin: 0;text-align: center;vertical-align: center" class="navbar" id="loader">'
        .get_string('nosubmissions','turnitintool').'</div><br /><br />';

} else {

    $output .= '<div id="loader" style="padding: 18px; margin: 0;text-align: center;vertical-align: center" class="navbar">
        <noscript>Javascript Required</noscript>
        <script>
        jQuery("#loader span").css( "display", "inline" );
        </script><span style="display: none;background: url(pix/ajax-loader.gif) no-repeat left center;padding-left: 80px;">
        <span style="background: url(pix/ajax-loader.gif) no-repeat right center;padding-right: 80px;">'.get_string('turnitinloading','turnitintool').'</span></span></div>';

    $output .= turnitintool_print_table( $table, true );

}

    return $output;
}
/**
 * Outputs the table for the inbox, this is called by turnitintool_view_all_submissions()
 *
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param array $input The array of data containing table elements
 * @return string Output of the tutor view submission inbox
 */
function turnitintool_draw_submission_table($cm, $turnitintool, $input=array()) {
    global $CFG;
    // Input Multi Dimensional Array of table data
    $table = new stdClass();
    $table->width='85%';
    $table->tablealign='center';
    $table->class='gradeTable';
    if (!$turnitintool->anon) {
        $student_header=turnitintool_doheaderlinks($cm,$turnitintool,get_string('submissionstudent','turnitintool'),'7','8');
    } else {
        $student_header=get_string('submissionstudent','turnitintool');
    }
    $objectid_header=get_string('objectid','turnitintool');
    $modified_header=turnitintool_doheaderlinks($cm,$turnitintool,get_string('posted','turnitintool'),'5','6');
    $submissionorig_header=turnitintool_doheaderlinks($cm,$turnitintool,get_string('submissionorig','turnitintool'),'1','2');
    $submissiongrade_header=turnitintool_doheaderlinks($cm,$turnitintool,get_string('submissiongrade','turnitintool'),'3','4');
    $feedback_header=get_string('feedback','turnitintool');

    $cells[0] = new stdClass();
    $cells[0]->data=$student_header;
    $cells[0]->class='header c0';
    $plus=0;
    if ( $CFG->turnitin_enablepseudo == 1 AND $CFG->turnitin_pseudolastname > 0 ) {
        $user_info = turnitintool_get_record( 'user_info_field', 'id', $CFG->turnitin_pseudolastname );
        $cells[1] = new stdClass();
        $cells[1]->data=$user_info->name;
        $cells[1]->class='header c1 markscell';
        $plus=1;
    }
    $cells[1+$plus] = new stdClass();
    $cells[1+$plus]->data=$objectid_header;
    $cells[1+$plus]->class='header c'.(1+$plus).' markscell';
    $cells[2+$plus] = new stdClass();
    $cells[2+$plus]->data=$modified_header;
    $cells[2+$plus]->class='header c'.(2+$plus).' datecell';
    $cells[3+$plus] = new stdClass();
    $cells[3+$plus]->data=$submissionorig_header;
    $cells[3+$plus]->class='header c'.(3+$plus).' markscell';
    $cells[4+$plus] = new stdClass();
    $cells[4+$plus]->data=$submissiongrade_header;
    $cells[4+$plus]->class='header c'.(4+$plus).' markscell';
    $cells[5+$plus] = new stdClass();
    $cells[5+$plus]->data='';
    $cells[5+$plus]->class='header c'.(5+$plus).' iconcell';
    $cells[6+$plus] = new stdClass();
    $cells[6+$plus]->data='';
    $cells[6+$plus]->class='header c'.(6+$plus).' markscell';
    $cells[7+$plus] = new stdClass();
    $cells[7+$plus]->data='';
    $cells[7+$plus]->class='header c'.(7+$plus).' iconcell';
    $cells[8+$plus] = new stdClass();
    $cells[8+$plus]->data='';
    $cells[8+$plus]->class='header c'.(8+$plus).' iconcell';
    $table->rows[0] = new stdClass();
    $table->rows[0]->cells=$cells;
    $table->rows[0]->id='tableHeader';
    $table->rows[0]->class='header';
    unset($cells);
    $i=0;
    foreach ($input as $row) {
        $i++;
        $cells[0] = new stdClass();
        $cells[0]->class='cell c0';
        $cells[0]->data=$row['student'];
        $plus=0;
        if ( $CFG->turnitin_enablepseudo == 1 AND $CFG->turnitin_pseudolastname > 0 ) {
            $cells[1] = new stdClass();
            $cells[1]->data=( isset( $row['usi'] ) ) ? $row['usi'] : '';
            $cells[1]->class='cell c1 markscell';
            $plus=1;
        }
        $cells[1+$plus] = new stdClass();
        $cells[1+$plus]->class='cell c'.(1+$plus).' markscell';
        $cells[1+$plus]->data=$row['objectid'];
        $cells[2+$plus] = new stdClass();
        $cells[2+$plus]->class='cell c'.(1+$plus).' datecell';
        $cells[2+$plus]->data=$row['modified'];
        $cells[3+$plus] = new stdClass();
        $cells[3+$plus]->class='cell c'.(1+$plus).' markscell';
        $cells[3+$plus]->data=$row['score'];
        $cells[4+$plus] = new stdClass();
        $cells[4+$plus]->class='cell c'.(1+$plus).' markscell';
        $cells[4+$plus]->data=$row['grade'];
        $cells[5+$plus] = new stdClass();
        $cells[5+$plus]->class='cell c'.(1+$plus).' iconcell';
        $cells[5+$plus]->data=$row['studentview'];
        $cells[6+$plus] = new stdClass();
        $cells[6+$plus]->class='cell c'.(1+$plus).' markscell';
        $cells[6+$plus]->data=$row['feedback'];
        $cells[7+$plus] = new stdClass();
        $cells[7+$plus]->class='cell c'.(1+$plus).' iconcell';
        $cells[7+$plus]->data=$row['download'];
        $cells[8+$plus] = new stdClass();
        $cells[8+$plus]->class='cell c'.(1+$plus).' iconcell';
        $cells[8+$plus]->data=$row['delete'];
        $table->rows[$i] = new stdClass();
        $table->rows[$i]->cells=$cells;
        $table->rows[$i]->id=(isset($row['rowid'])) ? $row['rowid'] : '';
        $table->rows[$i]->class=(isset($row['rowid'])) ? $row['class'] : '';
        unset($cells);
    }

    // Draw Full Table
    return turnitintool_print_table($table,true);
}

function turnitintool_reloadinbox_row( $cm, $turnitintool, $objectid ) {

    global $CFG;

    // Must be instructor on the class
    if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id)) OR $turnitintool->studentreports OR $trigger>0) {

        // Get the current submission values from the database
        if ( !$submissions = turnitintool_get_records_select('turnitintool_submissions','submission_objectid='.$objectid.' AND turnitintoolid='.$turnitintool->id,NULL,'id DESC') ) {
            header('HTTP/1.0 400 Bad Request');
            echo get_string('submissiongeterror','turnitintool');
            exit();
        }

        $first_submission = current( $submissions );
        $userid = isset($first_submission->userid) ? $first_submission->userid : 0;
        if ( $userid < 1 ) {
            header('HTTP/1.0 400 Bad Request');
            echo get_string('usergeterror','turnitintool');
            exit();
        }

        $user = turnitintool_get_moodleuser( $userid );

        // Instantiate the TII Comms Class
        $loaderbar = null;
        $tii=new turnitintool_commclass(turnitintool_getUID($user),$user->firstname,$user->lastname,$user->email,2,$loaderbar);

        // Set the user up with a TII account if they do not already have one
        turnitintool_usersetup($user,get_string('userprocess','turnitintool'),$tii,$loaderbar);
        if (isset( $tii->result) AND $tii->getRerror() ) {
            header('HTTP/1.0 400 Bad Request');
            if ($tii->getAPIunavailable()) {
                echo get_string('apiunavailable','turnitintool');
            } else {
                echo $tii->getRmessage();
            }
            exit();
        }

        $grade = null;
        $score = null;
        $transmatch = null;

        // Get all submissions for this user and this assignment part
        $post = new stdClass();
        $owner=turnitintool_get_owner($turnitintool->course);
        $post->ctl=turnitintool_getCTL($turnitintool->course);
        $post->cid=turnitintool_getCID($turnitintool->course);
        $post->tem=$owner->email;
        if (!$part=turnitintool_get_record('turnitintool_parts','id',$first_submission->submission_part)) {
            header('HTTP/1.0 400 Bad Request');
            echo get_string('partgeterror', 'turnitintool');
            exit();
        }
        $post->assignid=turnitintool_getAID($part->id); // Get the Assignment ID for this Assignment / Turnitintool instance
        $post->assign=$turnitintool->name.' - '.$part->partname.' (Moodle '.$post->assignid.')';

        $tii->listSubmissions($post,'');
        $tiisub_array=$tii->getSubmissionArray();

        // loop through the submission array and grab the score and grade
        foreach ( $tiisub_array as $index => $value ) {
            if ( $index == $objectid ) {
                $grade=turnitintool_processgrade($value["grademark"],$part,$owner,$post,$index,$tii,$loaderbar);
                $score = $value["overlap"];
                if ( $value["overlap"] !== '0' && empty( $value["overlap"] ) ) {
                    $score = null;
                }
                $transmatch = ($value["transmatch"]==1) ? 1 : 0;
                break;
            }
        }

        // If there are more than one row, which rarely happens but nice to deal with that here as part of this process
        if ( count( $submissions ) > 1 ) {
            $num_exists = 0;
            // For each submission check to see if there are comments if there are do not delete the row, if not delete all and reinsert a single submission row
            foreach ( $submissions as $submission ) {
                if ( turnitintool_count_records('turnitintool_comments', 'submissionid', $submission->id) < 1 ) {
                    turnitintool_delete_records( 'turnitintool_submissions', 'id', $submission->id );
                } else {
                    // There were comments, update what we have and increment the numebr that existed $num_exists
                    $submission->submission_score = $score;
                    if ( $submission->submission_score !== '0' && empty( $submission->submission_score ) ) {
                        $submission->submission_score = null;
                    }
                    $submission->submission_grade = $grade;
                    $submission->submission_transmatch = $transmatch;
                    turnitintool_update_record('turnitintool_submissions',$submission);
                    $num_exists++;
                }
            }
            // If we didn't find any associated comments then we deleted all rows to insert the highest id submission values back into the table
            if ( $num_exists == 0 ) {
                $submission = array_shift( $submissions );
                $submission->id = null;
                if ( $submission->submission_score !== '0' && empty( $submission->submission_score ) ) {
                    $submission->submission_score = null;
                }
                $submission->submission_grade = $grade;
                $submission->submission_transmatch = $transmatch;
                turnitintool_insert_record('turnitintool_submissions',$submission);
            }
        } else {
            // We should get here most times, this is where we only had one submission, in this case we update the row we have with the new values from the API
            $submission = array_shift( $submissions );
            $submission->submission_score = $score;
            if ( $submission->submission_score !== '0' && empty( $submission->submission_score ) ) {
                $submission->submission_score = null;
            }
            $submission->submission_grade = $grade;
            $submission->submission_transmatch = $transmatch;
            turnitintool_update_record('turnitintool_submissions',$submission);
        }

        @include_once($CFG->dirroot."/lib/gradelib.php");
        if (function_exists('grade_update')) {
           $grades=turnitintool_buildgrades($turnitintool,$user);
           $params['idnumber'] = $cm->idnumber;
           grade_update('mod/turnitintool', $turnitintool->course, 'mod', 'turnitintool', $turnitintool->id, 0, $grades, $params);
        }

        // Return the submission details in a JSON response message
        echo $submission->id;

    } else {
        header('HTTP/1.0 403 Not Found');
        echo get_string('permissiondeniederror','turnitintool');
        exit();
    }

}

function turnitintool_getnoteslink( $cm, $turnitintool, $submission, $num=null ) {
    global $CFG;
    $num = ( is_null( $num ) ) ? turnitintool_count_records_select('turnitintool_comments','submissionid='.$submission->id.' AND deleted<1') : $num;

    $notes='(<a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=notes&s='.$submission->id.'" title="'.get_string('notes','turnitintool').'">'.$num.'</a>)';
    return $notes;
}
/**
 * Enrolls a single Moodle user as a student onto the Turnitin Class
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param integer $userid The moodle student user id
 */
function turnitintool_enroll_student($cm,$turnitintool,$userid) {
    if (!$user = turnitintool_get_record('user','id',$userid)) {
        $reason=get_string('usergeterror','turnitintool');
        $response["status"] = 'error';
        $response["description"] = get_string('updateerror','turnitintool').': '.get_string('turnitinenrollstudents','turnitintool');
        $response["msg"] = $reason." (".$userid.")\n\n";
        echo json_encode( $response );
        exit();
    }
    if ( !has_capability( 'mod/turnitintool:submit', get_context_instance( CONTEXT_MODULE, $cm->id ) ) ) {
        $reason=get_string('permissiondeniederror','turnitintool');
        $response["status"] = 'error';
        $response["description"] = get_string('updateerror','turnitintool').': '.get_string('turnitinenrollstudents','turnitintool');
        $response["msg"] = $user->lastname.", ".$user->firstname." (".$user->email.")\n".$reason."\n\n";
        echo json_encode( $response );
        exit();
    }

    if (!$course = turnitintool_get_record('course','id',$turnitintool->course)) {
        $reason=get_string('coursegeterror','turnitintool');
        $response["status"] = 'error';
        $response["description"] = get_string('updateerror','turnitintool').': '.get_string('turnitinenrollstudents','turnitintool');
        $response["msg"] = $user->lastname.", ".$user->firstname." (".$user->email.")\n".$reason."\n\n";
        echo json_encode( $response );
        exit();
    }

    $post = new stdClass();
    $post->cid=turnitintool_getCID($course->id); // Get the Turnitin Class ID for Course
    $post->ctl=turnitintool_getCTL($course->id);
    $owner = turnitintool_get_owner($turnitintool->course);
    $post->tem=turnitintool_get_tutor_email($owner->id);

    $tii = new turnitintool_commclass(turnitintool_getUID($user),$user->firstname,$user->lastname,$user->email,1,$loaderbar);
    $tii->startSession();
    $loaderbar = null;
    $newuser=turnitintool_usersetup($user,get_string('userprocess','turnitintool'),$tii,$loaderbar);

    if ($tii->getRerror()) {
        $reason=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
        $response["status"] = 'error';
        $response["description"] = get_string('updateerror','turnitintool').': '.get_string('turnitinenrollstudents','turnitintool');
        $response["msg"] = $user->lastname.", ".$user->firstname." (".$user->email.")\n".$reason."\n\n";
        $tii->endSession();
        echo json_encode( $response );
        exit();
    }

    $tii->uid=$newuser->turnitin_uid;
    $tii->joinClass($post,'');

    if ($tii->getRerror()) {
        $reason=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
        $response["status"] = 'error';
        $response["description"] = get_string('updateerror','turnitintool').': '.get_string('turnitinenrollstudents','turnitintool');
        $response["msg"] = $user->lastname.", ".$user->firstname." (".$user->email.")\n".$reason."\n\n";
    } else {
        $response["status"] = 'success';
        $response["description"] = '';
        $response["msg"] = '';
    }
    $tii->endSession();
    echo json_encode( $response );
    exit();
}
/**
 * Enrolls all of the students enrolled in the Moodle Course onto the Turnitin Class
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 */
function turnitintool_enroll_all_students($cm,$turnitintool) {

    $context=get_context_instance(CONTEXT_MODULE, $cm->id);
    $courseusers=get_users_by_capability($context, 'mod/turnitintool:submit', '', '', '', '', 0, '', false);
    $courseusers=(!is_array($courseusers)) ? array() : $courseusers;

    if (count($courseusers)>0) {
        $total=(count($courseusers)*3);
        $loaderbar = new turnitintool_loaderbarclass($total);

        if (!$course = turnitintool_get_record('course','id',$turnitintool->course)) {
            turnitintool_print_error('coursegeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }

        $post = new stdClass();
        $post->cid=turnitintool_getCID($course->id); // Get the Turnitin Class ID for Course
        $post->ctl=turnitintool_getCTL($course->id);
        $owner = turnitintool_get_owner($turnitintool->course);
        $post->tem=turnitintool_get_tutor_email($owner->id);

        $thisstudent=0;
        $totalstudents=count($courseusers);
        foreach ($courseusers as $courseuser) {

            $tii = new turnitintool_commclass(turnitintool_getUID($courseuser),$courseuser->firstname,$courseuser->lastname,$courseuser->email,1,$loaderbar);
            $tii->startSession();

            $thisstudent++;
            $newuser=turnitintool_usersetup($courseuser,get_string('userprocess','turnitintool'),$tii,$loaderbar);

            if ($tii->getRerror()) {
                $reason=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                $usererror[]='<br /><b>'.$courseuser->lastname.', '.$courseuser->firstname.' ('.$courseuser->email.')</b><br />'.$reason.'<br />';
                $tii->endSession();
                continue;
            }

            $tii->uid=$newuser->turnitin_uid;
            $tii->joinClass($post,get_string('joiningclass','turnitintool','('.$thisstudent.'/'.$totalstudents.' '.get_string('students').')'));

            if ($tii->getRerror()) {
                $reason=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                $usererror[]='<br /><b>'.$courseuser->lastname.', '.$courseuser->firstname.' ('.$courseuser->email.')</b><br />'.$reason.'<br />';
            }
            $tii->endSession();

        }
        if (isset($usererror) AND count($usererror)>0) {
            $errorstring=get_string('updateerror','turnitintool').': '.get_string('turnitinenrollstudents','turnitintool').'<br />';
            $errorstring.=implode($usererror).'<br />';
            turnitintool_print_error($errorstring,NULL,NULL,NULL,__FILE__,__LINE__);
            exit();
        }
        // Force Grade refresh on next page load
        $_SESSION['updatedscores'][$turnitintool->id]==0;
    }
}

/**
 * Outputs the grade formatted with either a grademark for or moodle grade form depending on the options set
 *
 * @global object
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param object $submission The submission object from the turnitintool_submissions table
 * @param int $duedate The due date for this assignment part
 * @param int $postdate The post date for this assignment part
 * @param int $maxmarks The maximum marks allowed for this assignment part
 * @param string $textcolour The colour of the grade text
 * @param string $background The colour of the background for the grade box
 * @param boolean $gradeable Is the grade gradeable or read only
 * @return string Output of the grade form / display
 */
function turnitintool_dogradeoutput($cm,$turnitintool,$submission,$duedate,$postdate,$maxmarks,$textcolour='#666666',$background='transparent',$gradeable=true) {
    global $CFG, $USER;

    if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        $utp=2;
    } else {
        $utp=1;
    }

    $thisuser=$USER;

    $output='';
    if ($CFG->turnitin_usegrademark AND $turnitintool->usegrademark AND ($utp==2 OR ($utp==1 AND $postdate<=time()))) {

        $grademarkurl = $CFG->wwwroot . '/mod/turnitintool/view.php?id=' . $cm->id . '&jumppage=grade';
        $grademarkurl .= '&userid=' . $thisuser->id . '&utp=' . $utp . '&objectid=' . $submission->submission_objectid;

        $doscript='';
        if (!empty($submission->submission_objectid)) {
            $warn=($turnitintool->reportgenspeed>0 AND $duedate > time()) ? $warn=',\''.get_string('resubmissiongradewarn','turnitintool').'\'' : '';
            $doscript=' onclick="screenOpen(\''.$grademarkurl.'\',\''.$submission->id.'\',\''.$turnitintool->autoupdates.'\''.$warn.');return false;"';
        }
        if (!empty($submission->submission_grade) OR $submission->submission_grade==0) {
            if (is_null($submission->submission_grade)) {
                $submission->submission_grade='-';
            }
            $output.='<input name="grade['.$submission->id.']" type="text" readonly="readonly" size="3" class="gradebox" value="'.
                    $submission->submission_grade.'" style="border: 0px solid white;background-color: '.$background.';color: '.$textcolour.';" />/'.$maxmarks.' ';
        }
        if (!empty($submission->submission_objectid) AND ($utp==2 OR ($utp == 1 AND $postdate <= time()))) {
            if (!$submission->submission_gmimaged) {
                $output.='<img src="pix/icon-edit-grey.png" class="tiiicons" />';
            } else {
                $output.='<a href="'.$grademarkurl.'"'.$doscript.'><img src="pix/icon-edit.png" class="tiiicons" /></a>';
            }
        }

    } else {

        if ($utp==2 AND $gradeable) {
            $warn=($turnitintool->reportgenspeed>0 AND $postdate > time()) ? $warn=',\''.get_string('resubmissiongradewarn','turnitintool').'\'' : '';
            $output.='
            <span id="hideshow_'.$submission->id.'"><input name="grade['.$submission->id.']" id="grade_'.$submission->id.
                    '" type="text" size="3" class="gradebox" value="'.$submission->submission_grade.'" style="border: 1px inset;color: black;" />/'.$maxmarks.'</span>';
            $output.='<input src="pix/tickicon.gif" name="updategrade" value="updategrade" id="tick_'.$submission->id.'" class="tiiicons" type="image" />';
            $output.='<script language="javascript" type="text/javascript">
                viewgrade(\''.$submission->id.'\',\''.$textcolour.'\',\''.$background.'\''.$warn.');
            </script>';
        } else if ($utp==2 OR $postdate<=time()) {
            $output.='
            <input name="grade['.$submission->id.']" id="grade_'.$submission->id.'" type="text" size="3" class="gradebox" value="'.$submission->submission_grade.
                    '" style="border: 0px;color: black;background-color: '.$background.'" readonly="readonly" />/'.$maxmarks;
        }

    }
    if (empty($output)) {
        $output.='
            <input name="grade['.$submission->id.']" id="grade_'.$submission->id.
                '" type="text" size="3" class="gradebox" value="-" style="border: 0px;color: black;background-color: '.
                $background.'" readonly="readonly" />/'.$maxmarks;
    }
    return $output;

}

/**
 * Processes the grade entry if grades are updated via Moodle only
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param array $post The POST array supplied from the grade update form
 * @return string Returns $notice in the event of an error
 */
function turnitintool_update_form_grades($cm,$turnitintool,$post) {
    global $CFG;
    $notice='';
    $total=0;
    $owner=turnitintool_get_owner($turnitintool->course);
    foreach ($post['grade'] as $id => $thisgrade) {
        if (!$submission=turnitintool_get_record('turnitintool_submissions','id',$id)) {
            turnitintool_print_error('submissiongeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }
        if ($thisgrade!=$submission->submission_grade) {
            $total++;
        }
    }
    $loaderbar = new turnitintool_loaderbarclass($total+2);
    $tii = new turnitintool_commclass(turnitintool_getUID($owner),$owner->firstname,$owner->lastname,$owner->email,2,$loaderbar);
    $tii->startSession();
    $proc=0;
    foreach ($post['grade'] as $id => $thisgrade) {
        $thisgrade=round($thisgrade); // round the grade to an integer / Turnitin won't accept a null grade via the API
        if (!$submission=turnitintool_get_record('turnitintool_submissions','id',$id)) {
            turnitintool_print_error('submissiongeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }
        if (!$part=turnitintool_get_record_select('turnitintool_parts',"id=".$submission->submission_part." AND deleted=0")) {
            turnitintool_print_error('partgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }

        // work out if the grade has changed from what is stored
        if (empty($thisgrade) OR $thisgrade!=$submission->submission_grade) {

            $user=turnitintool_get_moodleuser($submission->userid,NULL,__FILE__,__LINE__);
            $update = new object;
            $update->id=$id;
            $update->submission_grade=$thisgrade;

            print_object( $update );

            if ($thisgrade>$part->maxmarks) {
                $input = new stdClass();
                $input->fullname=$user->firstname.' '.$user->lastname;
                $input->part=turnitintool_partnamefromnum($submission->submission_part);
                $input->maximum=$part->maxmarks;
                $notice.=get_string('submissiongradetoohigh','turnitintool',$input);
            } else {
                if (!$result=turnitintool_update_record('turnitintool_submissions',$update)) {
                    $notice=get_string('submissionupdateerror','turnitintool');
                }
            }

            // now push the grade to Turnitin
            $post=new object();
            $post->oid=$submission->submission_objectid;
            $post->score=$thisgrade;
            $post->cid=turnitintool_getCID($turnitintool->course);

            $proc++;

            $add = new stdClass();
            $add->num=$proc;
            $add->total=$total;
            $tii->setGradeMark($post,get_string('pushinggrade','turnitintool',$add));

            if ($tii->getRerror()) {
                if ($tii->getAPIunavailable()) {
                    turnitintool_print_error('apiunavailable','turnitintool',NULL,NULL,__FILE__,__LINE__);
                } else {
                    turnitintool_print_error($tii->getRmessage().' CODE: '.$tii->getRcode(),NULL,NULL,NULL,__FILE__,__LINE__);
                }
                exit();
            }

            @include_once($CFG->dirroot."/lib/gradelib.php");
            if (function_exists('grade_update')) {
                $grades=turnitintool_buildgrades($turnitintool,$user);
                $cm=get_coursemodule_from_instance("turnitintool", $turnitintool->id, $turnitintool->course);
                $params['idnumber'] = $cm->idnumber;
                grade_update('mod/turnitintool', $turnitintool->course, 'mod', 'turnitintool', $turnitintool->id, 0, $grades, $params);

            }

        }

    }
    $tii->endSession();
    $loaderbar->endloader();
    // Update gradebook grades in Moodle 1.9 and above
    turnitintool_redirect($CFG->wwwroot."/mod/turnitintool/view.php?id=".$cm->id."&do=allsubmissions&update=1");

    return $notice;
}

/**
 * Updates all originality report scores and grades from a turnitin FID10 call
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param int $trigger 0 = allow once per session, 1 = allow once every two minutes, 2 = allow immediately
 * @param object $loaderbar The loaderbar object passed by reference can be NULL if no loader bar is used
 */
function turnitintool_update_all_report_scores($cm,$turnitintool,$trigger,$loaderbar=null) {

    global $USER,$CFG,$notice;
    $param_type=optional_param('type',null,PARAM_CLEAN);
    $param_do=optional_param('do',null,PARAM_CLEAN);
    $param_ob=optional_param('ob',null,PARAM_CLEAN);
    $param_sh=optional_param('sh',null,PARAM_CLEAN);
    $param_fr=optional_param('fr',null,PARAM_CLEAN);
    $param_ajax=optional_param('ajax', null,PARAM_CLEAN);

    $api_error=false;

    // Check to see if the results for this user's session has been updated from
    // Turnitin in the last two minutes. If they have then we should skip this refresh
    // to avoid system load issues that can occur during busy submission periods
    // where many tutors will enter inbox screens to check progress
    if ($trigger < 2 AND isset($_SESSION['updatedscores'][$turnitintool->id]) AND $_SESSION['updatedscores'][$turnitintool->id]>strtotime('-2 minutes')) {
        return false;
    }

    // Update the score only when neccessary determined by a session variable
    // This ensures the scores are only updated once per session
    // or if the user requests update specifically [[[[
    if (((isset($_SESSION['updatedscores'][$turnitintool->id]) AND $_SESSION['updatedscores'][$turnitintool->id]>0) OR isset($param_type)) AND $trigger<1) {
        return false;
    } else {

        // Get Moodle user object [[[[
        if (!$owner = turnitintool_get_owner($turnitintool->course)) {
            turnitintool_print_error('tutorgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }
        // ]]]]
        // get Moodle Course Object [[[[
        if (!$course = turnitintool_get_record('course','id',$turnitintool->course)) {
            turnitintool_print_error('coursegeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }
        // ]]]]
        // get Moodle Parts Object [[[[
        if (!$parts=turnitintool_get_records_select('turnitintool_parts',"turnitintoolid=".$turnitintool->id." AND deleted=0")) {
            turnitintool_print_error('partgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }
        // ]]]]

        if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id)) OR $turnitintool->studentreports OR $trigger>0) {

            $total=count($parts);
            $loaderbar = ( is_null( $loaderbar ) ) ? new turnitintool_loaderbarclass( 0 ) : $loaderbar;
            $loaderbar->total=$loaderbar->total+$total;
            $tii=new turnitintool_commclass(turnitintool_getUID($owner),$owner->firstname,$owner->lastname,$owner->email,2,$loaderbar);

            turnitintool_usersetup($owner,get_string('userprocess','turnitintool'),$tii,$loaderbar);
            if (isset( $tii->result) AND $tii->getRerror() ) {
                if ($tii->getAPIunavailable()) {
                    turnitintool_print_error('apiunavailable','turnitintool',NULL,NULL,__FILE__,__LINE__);
                } else {
                    turnitintool_print_error($tii->getRmessage(),NULL,NULL,NULL,__FILE__,__LINE__);
                }
                exit();
            }

            if (!$ids=turnitintool_get_records_select('turnitintool_submissions','turnitintoolid='.$turnitintool->id.' AND submission_objectid IS NOT NULL',NULL,'','submission_objectid,id,submission_grade,submission_score,submission_modified,submission_status,submission_attempts,userid,submission_unanon')) {
                $ids=array();
            }

            $context = get_context_instance(CONTEXT_COURSE, $turnitintool->course);
            $studentusers = get_users_by_capability($context,'mod/turnitintool:submit','u.id,u.firstname,u.lastname','','','',0,'',false);
            $studentuser_array = array_keys($studentusers);

            $users_string = join( $studentuser_array, "," );
            $users = ( empty($users_string) ) ? array() : turnitintool_get_records_sql('SELECT turnitin_uid,userid FROM {turnitintool_users} WHERE userid IN ('.$users_string.')');

            $post = new stdClass();
            $post->cid=turnitintool_getCID($course->id); // Get the Turnitin Class ID for Course
            $post->tem=$owner->email; // Get the Turnitin Course Tutor Email
            $post->ctl=turnitintool_getCTL($course->id);
            foreach ($parts as $part) {
                $post->assignid=turnitintool_getAID($part->id); // Get the Assignment ID for this Assignment / Turnitintool instance
                $post->assign=$turnitintool->name.' - '.$part->partname.' (Moodle '.$post->assignid.')';

                $status = new stdClass();
                $status->user=get_string('student','turnitintool');
                $status->proc=(isset($loaderbar->proc)) ? $loaderbar->proc : null;

                $tii->listSubmissions($post,get_string('updatingscores','turnitintool',$status));

                $resultArray=$tii->getSubmissionArray();

                if ($tii->getRerror() AND isset($param_do) AND $param_do=='allsubmissions') {
                    $notice=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                } else if ($tii->getRerror() AND isset($param_do) AND $param_do=='submissions') {
                    $notice['error']=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                }

                if ($tii->getRerror()) {
                    $api_error=true;
                } else {
                    // Create an array of submission IDs with the TII Paper ID as key to repatriate the feedback with submissions
                    unset($inserts);
                    $inserts=array();
                    foreach ($resultArray as $key => $value) {

                        $insert=new object;
                        $insert->turnitintoolid=$turnitintool->id;
                        $insert->submission_part=$part->id;
                        $insert->submission_title=$value["title"];
                        $insert->submission_type=1;
                        $insert->submission_filename=str_replace(array("&#39;","&rsquo;","&lsquo;","'"," "),"_",$value["title"]).'.doc';
                        $insert->submission_objectid=$key;
                        $insert->submission_score=$value["overlap"];
                        if ( $value["overlap"] !== '0' && empty( $value["overlap"] ) ) {
                            $insert->submission_score = null;
                        }
                        $insert->submission_grade=turnitintool_processgrade($value["grademark"],$part,$owner,$post,$key,$tii,$loaderbar);
                        $insert->submission_status=get_string('submissionuploadsuccess','turnitintool');
                        $insert->submission_queued=0;
                        $insert->submission_attempts=( $value["student_view"] > 0 ) ? strtotime($value["student_view"]) : 0;
                        $insert->submission_gmimaged = $value["grademarkstatus"]>0 ? 1 : 0;
                        $insert->submission_modified=strtotime($value["date_submitted"]);
                        $insert->submission_parent=0;
                        $insert->submission_unanon=($value["anon"]==1) ? 0 : 1;
                        $insert->submission_transmatch=($value["transmatch"]==1) ? 1 : 0;

                        if ( isset($users[$value["userid"]]) ) {
                            // If returned userid is already stored and the user is enrolled on the course
                            // we can use real Moodle user to store against
                            $insert->submission_nmuserid=0;
                            $insert->submission_nmfirstname=NULL;
                            $insert->submission_nmlastname=NULL;
                            $insert->userid=$users[$value["userid"]]->userid;

                        } else {
                            // If userid is not already stored we can not use real user to store against, use (Non Moodle) Marker

                            $insert->submission_nmuserid=($value["userid"]=="-1") ? md5($value["firstname"].$value["lastname"]) : $value["userid"];

                            if (!empty($value["firstname"])) {
                                $insert->submission_nmfirstname=$value["firstname"];
                            } else {
                                $insert->submission_nmfirstname='Unnamed';
                            }
                            if (!empty($value["lastname"])) {
                                $insert->submission_nmlastname=$value["lastname"];
                            } else {
                                $insert->submission_nmlastname='User';
                            }
                            $insert->userid=0;
                        }
                        // Only do DB update if the record has changed, saves DB calls
                        if ( !isset( $ids[$key] ) OR $insert->submission_grade != $ids[$key]->submission_grade
                                OR $insert->submission_score != $ids[$key]->submission_score
                                OR $insert->submission_modified != $ids[$key]->submission_modified
                                OR $insert->submission_attempts != $ids[$key]->submission_attempts
                                OR $insert->userid != $ids[$key]->userid
                                OR $insert->submission_unanon != $ids[$key]->submission_unanon ) {
                            $inserts[]=$insert;
                        }
                        $keys_found[] = $key;
                    }
                    // Purge old submissions listings
                    foreach ( $ids as $submission ) {
                        if ( !in_array( $submission->submission_objectid, $keys_found ) ) {
                            turnitintool_delete_records_select('turnitintool_submissions','submission_objectid='.$submission->submission_objectid.' AND submission_part='.$part->id);
                        }
                    }

                    // Now insert the new submissions and update existing submissions
                    foreach ($inserts as $insert) {
                        $key = $insert->submission_objectid;
                        if (isset($ids[$key])) {
                            $insert->id=$ids[$key]->id;
                            $insertid=turnitintool_update_record('turnitintool_submissions',$insert);
                        } else {
                            $insertid=turnitintool_insert_record('turnitintool_submissions',$insert);
                        }
                        $submission=$insert;
                        unset($insert);

                        // Update gradebook grades in Moodle 1.9 and above

                        @include_once($CFG->dirroot."/lib/gradelib.php");
                        if (function_exists('grade_update') AND $submission->userid!=0) {
                            $user=turnitintool_get_moodleuser($submission->userid,NULL,__FILE__,__LINE__);
                            $cm=get_coursemodule_from_instance("turnitintool", $turnitintool->id, $turnitintool->course);
                            $grades=turnitintool_buildgrades($turnitintool,$user);
                            $grades->userid=$user->id;
                            $params['idnumber'] = $cm->idnumber;
                            grade_update('mod/turnitintool', $turnitintool->course, 'mod', 'turnitintool', $turnitintool->id, 0, $grades, $params);
                        }

                        // ]]]]

                    }
                }
            }
            if ( $api_error ) {
                $notice=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                $loaderbar->endloader();
                $_SESSION['updatedscores'][$turnitintool->id]=time();
                if (!empty($param_ajax) && $param_ajax ) {
                    header('HTTP/1.0 400 Bad Request');
                    echo $notice;
                    $param_ajax=0;
                    exit();
                }
                return false;
            }

            $loaderbar->endloader();

            $_SESSION['updatedscores'][$turnitintool->id]=time();
            $redirectlink=$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id;
            $redirectlink.=(!is_null($param_do)) ? '&do='.$param_do : '&do=intro';
            $redirectlink.=(!is_null($param_fr)) ? '&fr='.$param_fr : '';
            $redirectlink.=(!is_null($param_sh)) ? '&sh='.$param_sh : '';
            $redirectlink.=(!is_null($param_ob)) ? '&ob='.$param_ob : '';

            turnitintool_redirect($redirectlink);

        }

    }

}

/**
 * Processes the grade and makes sure it is no higher than the Max Grade (which is possible via GradeMark)
 * the grade is set to the maximum grade if higher than maximum
 *
 * @param var $grade The grade input
 * @param object $part The part object for this assignment part
 * @param object $owner The moodle user object of the Turnitin owner of this class
 * @return string Returns the corrected / processed grade
 */
function turnitintool_processgrade($grade,$part,$owner,$post,$objectid,&$tii,&$loaderbar) {
    $grade=($grade==='0' OR $grade===0 OR !empty($grade)) ? $grade : NULL;
    if ($grade<=$part->maxmarks) { // If grade is LOWER or equal to max grade fine.... RETURN GRADE
        $output=$grade;
    } else {                       // If grade is HIGHER than max grade correct it.... UPDATE TII GRADE TO MAXMARKS and RETURN MAXMARKS
        $output=$part->maxmarks;
        $post = new stdClass();
        $post->oid=$objectid;
        $post->score=$output;
        if (!is_null($loaderbar)) {
            $loaderbar->total=$loaderbar->total+1;
        }
        $tii->setGradeMark($post,get_string('correctingovergrade','turnitintool'));
    }
    return $output;
}

/**
 * Converts a percentage to the gradient position for colouring the Originality score block
 *
 * @param var $percent The percentage originality score input
 * @param string $left The left offset of the coloured block part of the originality score output box
 * @return string Outputs the style attribute for the originality box
 */
function turnitintool_percent_to_gradpos($percent,$left='0') {
    $pos=floor(($percent/100)*(-380));
    $style=' style="background: url(pix/gradback.jpg) no-repeat '.$left.'px '.$pos.'px";';
    return $style;
}

/**
 * Deletes the submission from both Moodle and Turnitin OR just Moodle if the submission is a draft
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param var $userid The Moodle User ID of the user deletingh the submission
 * @param object $submission The submission data object from turnitintool_submissions
 */
function turnitintool_delete_submission($cm,$turnitintool,$userid,$submission) {
    global $USER,$CFG;
    $param_do=optional_param('do',null,PARAM_CLEAN);

    // If user is student and has not finalized to turnitin OK
    // If user is grader / tutor
    // Or If resubmissions are allowed -- OK [[[[
    if (empty($submission->submission_objectid)
            OR
            has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))
            OR
            $turnitintool->reportgenspeed==1
            OR
            $turnitintool->reportgenspeed==2
    ) {

        // Trap any possible attempt to delete someone elses submission unless they are a tutor
        // Should not happen but trapping is easy [[[[
        if ($userid!=$submission->userid AND !has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
            turnitintool_print_error('permissiondeniederror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }
        // ]]]]

        // Everything is OK delete the Moodle Stored Submission data first [[[[
        if (!turnitintool_delete_records('turnitintool_submissions','id',$submission->id)) {
            turnitintool_print_error('submissiondeleteerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }
        // ]]]]

        add_to_log($turnitintool->course, "turnitintool", "delete submission", "view.php?id=$cm->id", "User deleted submission '$submission->submission_title'", "$cm->id");

        // Only do this at this point if the user is a grader OR resubmissions allowed [[[[
        if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))
                OR
                $turnitintool->reportgenspeed==1
                OR
                $turnitintool->reportgenspeed==2) {

            // Has the submission been made to Turnitin if there is no stored TII Object ID
            // We can assume this to be the case [[[
            if (!empty($submission->submission_objectid)) {
                // DELETE FROM TURNITIN

                $owner=turnitintool_get_owner($turnitintool->course);

                $loaderbar = new turnitintool_loaderbarclass(3);
                $tii = new turnitintool_commclass(turnitintool_getUID($owner),$owner->firstname,$owner->lastname,$owner->email,2,$tii,$loaderbar);
                $tii->startSession();

                turnitintool_usersetup($owner,get_string('userprocess','turnitintool'),$tii,$loaderbar);
                if ($tii->getRerror()) {
                    if ($tii->getAPIunavailable()) {
                        turnitintool_print_error('apiunavailable','turnitintool',NULL,NULL,__FILE__,__LINE__);
                    } else {
                        turnitintool_print_error($tii->getRmessage(),NULL,NULL,NULL,__FILE__,__LINE__);
                    }
                    exit();
                }

                // Gather the variables required for this Function [[[[
                $post = new stdClass();
                $post->paperid=$submission->submission_objectid;
                // ]]]]

                $tii->deleteSubmission($post,get_string('deletingsubmission','turnitintool'));

                if ($tii->getRerror()) {
                    $reason=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                    $reason.='<br />'.get_string('turnitindeletionerror','turnitintool');
                    turnitintool_print_error($reason,NULL,NULL,NULL,__FILE__,__LINE__);
                    exit();
                }
                $tii->endSession();

            }
            // ]]]]

            turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&user='.$userid.'&do='.$param_do);
            exit();
        } else {
            turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do='.$param_do);
            exit();
        }
        // ]]]]
    }
    // ]]]]
}

/**
 *
 * @param object $turnitintool The turnitintool object for this activity
 */
function turnitintool_update_choice_cookie($turnitintool) {
    global $CFG;
    // If a submission is deleted or submitted we need to update the cookie and correct the submission count
    $userCookieArray = array();
    $newUserCookie='';
    $newCountCookie='';
    if (isset($_COOKIE["turnitintool_choice_user"])) {
        $userCookie=$_COOKIE["turnitintool_choice_user"];
        $userCookieArray=explode("_",$userCookie);
    }
    if (isset($_COOKIE["turnitintool_choice_count"])) {
        $countCookie=$_COOKIE["turnitintool_choice_count"];
        $countCookieArray=explode("_",$countCookie);
    }
    for ($i=0;$i<count($userCookieArray);$i++) {
        if (substr_count($userCookieArray[$i],'nm-')>0 AND !$turnitintool->anon) {
            $nmuserid=str_replace('nm-','',$userCookieArray[$i]);
            $numsubmissions = turnitintool_count_records('turnitintool_submissions','submission_nmuserid',$nmuserid,'turnitintoolid',$turnitintool->id);
        } else if (!$turnitintool->anon) {
            $numsubmissions = turnitintool_count_records('turnitintool_submissions','userid',$userCookieArray[$i],'turnitintoolid',$turnitintool->id);
        } else {
            $nmsubmissions = turnitintool_count_records('turnitintool_submissions','submission_part',$userCookieArray[$i],'turnitintoolid',$turnitintool->id,'userid',0);
            $cm=get_coursemodule_from_instance('turnitintool',$turnitintool->id,$turnitintool->course);
            $context = get_context_instance(CONTEXT_MODULE, $cm->id);
            $studentusers=get_users_by_capability($context,'mod/turnitintool:submit','u.id','','','','','',false);
            $numusers=(!is_array($studentusers)) ? 0 : count($studentusers);
            $numsubmissions=$nmsubmissions+$numusers;
        }
        if ($numsubmissions!=0) {
            $newUserCookie.=$userCookieArray[$i];
            $newCountCookie.=$numsubmissions;
        }
        if ($i!=count($userCookieArray)-1) {
            $newUserCookie.='_';
            $newCountCookie.='_';
        }
    }
    setcookie("turnitintool_choice_user",$newUserCookie,0,"/");
    setcookie("turnitintool_choice_count",$newCountCookie,0,"/");

}

/**
 * Function to get the email address of the tutor
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param var $userid The Moodle User ID of the user
 * @return string The email address of the tutor
 */
function turnitintool_get_tutor_email($userid) {
    $user=turnitintool_get_moodleuser($userid,NULL,__FILE__,__LINE__);
    // If the user has been deleted from Moodle there is a remnant of the Email address
    // left in the username field of the database. So if the email address is empty we can
    // get this email address by removing the timestamp by popping it off the end of the string [[[[
    if (empty($user->email)) {
        $email_array=explode('.',$user->username);
        array_pop($email_array);
        $email=join('.',$email_array);
    } else {
        // If the user has not been deleted this becomes simple get data
        $email=$user->email;
    }
    // ]]]]
    return $email;
}

/**
 * Determines whether a user is allowed to submit to any part of this assignment
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param object $user The Moodle User Object for the user viewing the submission screen
 * @return array Returns true if executed by a user and they can submit or returns an array of users that can submit if executed by a tutor
 */
function turnitintool_cansubmit($cm,$turnitintool,$user) { // Returns an array of users that can still submit if executed by a tutor or true if executed by a user

    $return=false;

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    $studentusers=get_users_by_capability($context, 'mod/turnitintool:submit', 'u.id,u.firstname,u.lastname', 'u.lastname', '', '', turnitintool_module_group($cm), '', false);

    // Count the number of parts still available to be submitted from start date and end date
    if ($turnitintool->allowlate==1) {
        $partsavailable=turnitintool_get_records_select('turnitintool_parts', "turnitintoolid=".$turnitintool->id." AND deleted=0 AND dtstart < ".time()."");
    } else {
        $partsavailable=turnitintool_get_records_select('turnitintool_parts', "turnitintoolid=".$turnitintool->id." AND deleted=0 AND dtstart < ".time()." AND dtdue > ".time()."");
    }
    if (!$partsavailable) {
        $partsavailable=array();
    }
    $numpartsavailable=count($partsavailable);

    // Check to see if the submitter is a tutor / grader
    if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        $return=array();
        foreach ($partsavailable as $partavailable) {
            if (is_array($studentusers)) {
                foreach ($studentusers as $studentuser) {
                    $submitted=(!turnitintool_get_records_select('turnitintool_submissions','turnitintoolid='.$turnitintool->id.
                            ' AND submission_part='.$partavailable->id.' AND userid='.$studentuser->id)) ? 0 : 1;
                    if (!$submitted // Student has made no submissions
                            OR
                         ( $turnitintool->reportgenspeed > 0 AND $partavailable->dtdue >= time() AND $submitted ) // Resubmissions Allowed and due date hasn't passed
                        ) {
                        // If the student has not made a submission or reportgenspeed is 1 or 2 ie reports can be overwritten add student to the array
                        // If late submissions is enabled one submission is allowed after due date even if resubmission is on
                        $return[$studentuser->id]=$studentuser;
                    }
                }
            }
        }
    } else {
        foreach ($partsavailable as $partavailable) {
            $submitted=(!turnitintool_get_records_select('turnitintool_submissions','turnitintoolid='.$turnitintool->id.
                    ' AND submission_part='.$partavailable->id.' AND userid='.$user->id)) ? 0 : 1;
            if (!$submitted // Student has made no submissions
                    OR
                 ($turnitintool->reportgenspeed>0 AND $partavailable->dtdue>=time() AND $submitted) // Resubmissions Allowed and due date hasn't passed
                ) {
                // If the student has not made all possible submissions or is allowed to resubmit
                // If late submissions is enabled one submission is allowed after due date even if resubmission is on
                $return=true; // A boolean that tells me whether this student has more parts that they can submit to
            }
        }
    }
    return $return;
}

/**
 * Outputs the HTML for the submission form
 *
 * @global object
 * @global object
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param var $submissionid The Submission ID of the submission in turnitintool_submission
 * @return string returns the HTML of the form
 */
function turnitintool_view_submission_form($cm,$turnitintool,$submissionid=NULL) {
    global $CFG,$USER,$COURSE;
    $param_type=optional_param('type',0,PARAM_CLEAN);
    $param_userid=optional_param('userid',null,PARAM_CLEAN);
    $param_agreement=optional_param('agreement',null,PARAM_CLEAN);

    $cansubmit=turnitintool_cansubmit($cm,$turnitintool,$USER);

    $totalusers = (!$cansubmit) ? 0 : count($cansubmit);

    if ($cansubmit AND $totalusers>0) {

        $output=turnitintool_box_start('generalbox boxwidthwide boxaligncenter eightyfive', 'submitbox',true);

        if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
            $submissions=turnitintool_get_records_select('turnitintool_submissions','turnitintoolid='.$turnitintool->id);
        } else {
            $submissions=turnitintool_get_records_select('turnitintool_submissions','userid='.$USER->id.' AND turnitintoolid='.$turnitintool->id);
        }

        $output.='<script language="javascript" type="text/javascript">'.PHP_EOL;
        $output.='var stringsArray = new Array("'.get_string('addsubmission','turnitintool').'","'
                .get_string('resubmit','turnitintool').'","'
                .get_string('resubmission','turnitintool').'","'
                .get_string('resubmissionnotenabled','turnitintool').'","'
                .get_string('anonenabled','turnitintool').'");'.PHP_EOL;
        $output.='var submissionArray = new Array();'.PHP_EOL;

        if ($turnitintool->allowlate==1) {
            $parts=turnitintool_get_records_select('turnitintool_parts',
                    "turnitintoolid='".$turnitintool->id."' AND deleted=0 AND dtstart < '".time()."'", null,
                    'dtstart,dtdue,dtpost,id');
        } else {
            $parts=turnitintool_get_records_select('turnitintool_parts',
                    "turnitintoolid='".$turnitintool->id."' AND deleted=0 AND dtstart < '".time()."' AND dtdue > '".time()."'",
                    NULL,'dtstart,dtdue,dtpost,id');
        }

        if (is_array($submissions)) {
            $i=0;
            foreach ($submissions as $submission) {
                $lockresubmission=0;
                if (isset($parts[$submission->submission_part]) AND $parts[$submission->submission_part]->dtdue < time()) {
                    $lockresubmission=1;
                }
                $output.='submissionArray['.$i.'] = new Array("'.$submission->userid.'","'.$submission->submission_part.'","'.$submission->submission_title.'","'.$submission->submission_unanon.'",'.$lockresubmission.');'.PHP_EOL;
                $submittedparts[]=$submission->submission_part;
                $i++;
            }
        }
        $output.='</script>'.PHP_EOL;

        $table = new stdClass();
        $table->width='100%';
        $table->id='uploadtable';
        $table->class='uploadtable';

        unset($cells);
        $cells=array();
        $cells[0] = new stdClass();
        $cells[0]->class='cell c0';
        $cells[0]->data=get_string('submissiontype', 'turnitintool').turnitintool_help_icon('submissiontype',get_string('submissiontype','turnitintool'),'turnitintool',true,false,'',true);

        if ($turnitintool->type==0) {

            if ($param_type==1) {
                $selected=array('',' selected','','');
            } else if ($param_type==2) {
                $selected=array('','',' selected','');
            } else if ($param_type==3) {
                $selected=array('','','',' selected');
            } else {
                $selected=array(' selected','','','');
                $param_type=0;
            }

            $cells[1] = new stdClass();
            $cells[1]->class='cell c1';
            $cells[1]->data='<select onchange="turnitintool_jumptopage(this.value)">';
            $cells[1]->data.='<option label="Select Submission Type" value="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.
                    $cm->id.'&do=submissions"'.$selected[0].'>Select Submission Type</option>';

            $cells[1]->data.='<option label="-----------------" value="#">-----------------</option>';
            $typearray=turnitintool_filetype_array(false);

            foreach ($typearray as $typekey => $typevalue) {
                $cells[1]->data.='
                <option label="'.$typevalue.'" value="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=submissions&type='.
                        $typekey.'"'.$selected[$typekey].'>'.$typevalue.'</option>';
            }

            $cells[1]->data.='
            </select>
            <input id="submissiontype" name="submissiontype" type="hidden" value="'.$param_type.'" />';
        } else if ($turnitintool->type==1) {
            $param_type=1;
            $cells[1] = new stdClass();
            $cells[1]->data='<input id="submissiontype" name="submissiontype" type="hidden" value="1" />'.get_string('fileupload','turnitintool');
        } else if ($turnitintool->type==2) {
            $param_type=2;
            $cells[1] = new stdClass();
            $cells[1]->data='<input id="submissiontype" name="submissiontype" type="hidden" value="2" />'.get_string('textsubmission','turnitintool');
        }

        $output.='<b>'.get_string('submit','turnitintool').'</b><br />
    <form enctype="multipart/form-data" action="'.$CFG->wwwroot.'/mod/turnitintool/view.php'.'?id='.$cm->id.'&do=submissions&type='.$param_type.'" method="POST" name="submissionform">';

        $table->rows[0] = new stdClass();
        $table->rows[0]->class='r0';
        $table->rows[0]->cells=$cells;
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        if ($param_type!=0) {
            $submissiontitle=optional_param('submissiontitle','',PARAM_CLEAN);
            $disableform=false;
            if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
                $utype="tutor";
                // If tutor submitting on behalf of student
                unset($cells);
                $cells[0] = new stdClass();
                $cells[0]->class='cell c0';
                $cells[0]->data=get_string('studentsname', 'turnitintool').turnitintool_help_icon('studentsname',get_string('studentsname','turnitintool'),'turnitintool',true,false,'',true);

                if (count($cansubmit)>0) {
                    $cells[1] = new stdClass();
                    $cells[1]->data='<select name="userid" id="userid" onchange="updateSubForm(submissionArray,stringsArray,this.form,'.$turnitintool->reportgenspeed.')">';

                    $module_group = turnitintool_module_group( $cm );
                    $studentusers = array_keys( get_users_by_capability($context,'mod/turnitintool:submit','u.id','','','',$module_group,'',false) );

                    foreach ($cansubmit as $courseuser) {
                        // Filter Guest users, admins and grader users
                        if (in_array( $courseuser->id, $studentusers ) ) {

                            if (!is_null($param_userid) AND $param_userid==$courseuser->id) {
                                $selected=' selected';
                            } else {
                                $selected='';
                            }
                            $cells[1]->data.='<option label="'.$courseuser->firstname.' '.$courseuser->lastname.
                                    '" value="'.$courseuser->id.'"'.$selected.'>'.$courseuser->lastname.
                                    ', '.$courseuser->firstname.'</option>';
                        }
                    }
                    $cells[1]->data.='</select>';

                    if ($cells[1]->data=='<select id="userid" name="userid">'.'</select>') {
                        $cells[1]->data='<i>'.get_string('allsubmissionsmade','turnitintool').'</i>';
                        $disableform=true;
                    }
                } else {
                    $cells[1] = new stdClass();
                    $cells[1]->data='<i>'.get_string('noenrolledstudents','turnitintool').'</i>';
                }
                $table->rows[1] = new stdClass();
                $table->rows[1]->class='r1';
                $table->rows[1]->cells=$cells;
            } else {
                $utype="student";
                // If student submitting
                unset($cells);
                $cells[0] = new stdClass();
                $cells[0]->class='cell c0';
                $cells[0]->data='';
                $cells[1] = new stdClass();
                $cells[1]->data='<input id="userid" name="userid" type="hidden" value="'.$USER->id.'" />';
                $table->rows[1] = new stdClass();
                $table->rows[1]->class='r1';
                $table->rows[1]->cells=$cells;
            }

            if (!$disableform) {

                unset($cells);
                $cells[0] = new stdClass();
                $cells[0]->class='cell c0';
                $cells[0]->data=get_string('submissiontitle', 'turnitintool').turnitintool_help_icon('submissiontitle',
                        get_string('submissiontitle','turnitintool'),
                        'turnitintool',
                        true,
                        false,
                        '',
                        true);
                $cells[1] = new stdClass();
                $cells[1]->data='<input type="text" name="submissiontitle" class="formwide" maxlength="200" value="'.$submissiontitle.'" />&nbsp;<span id="submissionnotice"></span>';
                $table->rows[2] = new stdClass();
                $table->rows[2]->class='r0';
                $table->rows[2]->cells=$cells;

                if (count($parts)>1) {

                    unset($cells);
                    $cells[0] = new stdClass();
                    $cells[0]->class='cell c0';
                    $cells[0]->data=get_string('submissionpart', 'turnitintool').turnitintool_help_icon('submissionpart', get_string('submissionpart','turnitintool'),
                            'turnitintool',true,false,'',true);
                    $cells[1] = new stdClass();
                    $cells[1]->data='<select name="submissionpart" class="formwide" onchange="updateSubForm(submissionArray,stringsArray,this.form,'.$turnitintool->reportgenspeed.',\''.$utype.'\')">';

                    $i=0;
                    foreach ($parts as $part) { // Do parts that have not yet been submitted to
                        $cells[1]->data.='<option label="'.$part->partname.'" value="'.$part->id.'">'.$part->partname.'</option>';
                        $i++;
                    }

                    $cells[1]->data.='</select>';
                    $table->rows[3] = new stdClass();
                    $table->rows[3]->class='r1';
                    $table->rows[3]->cells=$cells;

                } else {
                    unset($cells);
                    $cells[0] = new stdClass();
                    $cells[0]->class='cell c0';
                    $cells[0]->data=get_string('submissionpart', 'turnitintool').
                            turnitintool_help_icon('submissionpart',get_string('submissionpart','turnitintool'),'turnitintool',true,false,'',true);

                    foreach ($parts as $part) { // Do parts that have not yet been submitted to
                        $cells[1] = new stdClass();
                        $cells[1]->data=$part->partname.'<input type="hidden" name="submissionpart" value="'.$part->id.'" />';
                        break;
                    }
                    $table->rows[3] = new stdClass();
                    $table->rows[3]->class='r1';
                    $table->rows[3]->cells=$cells;
                }

                if ($param_type==1) {
                    unset($cells);
                    $cells[0] = new stdClass();
                    $cells[0]->class='cell c0';
                    $cells[0]->data=get_string('filetosubmit', 'turnitintool').turnitintool_help_icon('filetosubmit',get_string('filetosubmit','turnitintool'),'turnitintool',true,false,'',true);
                    $cells[1] = new stdClass();
                    $cells[1]->data='<input type="hidden" name="MAX_FILE_SIZE" value="'.$turnitintool->maxfilesize.'" />';
                    $cells[1]->data.='<input type="file" name="submissionfile" size="55%" />';
                    $table->rows[4] = new stdClass();
                    $table->rows[4]->class='r0';
                    $table->rows[4]->cells=$cells;
                }

                if ($param_type==2) {
                    unset($cells);
                    $submissiontext=optional_param('submissiontext','',PARAM_CLEAN);

                    $cells[0] = new stdClass();
                    $cells[0]->class='cell c0';
                    $cells[0]->data=get_string('texttosubmit', 'turnitintool').turnitintool_help_icon('texttosubmit',get_string('texttosubmit','turnitintool'),'turnitintool',true,false,'',true);
                    $cells[1] = new stdClass();
                    $cells[1]->data='<textarea name="submissiontext" class="submissionText">'.$submissiontext.'</textarea>';
                    $table->rows[5] = new stdClass();
                    $table->rows[5]->class='r1';
                    $table->rows[5]->cells=$cells;
                }

                if ($param_type==3) {
                    unset($cells);
                    $submissionurl=optional_param('submissionurl','',PARAM_CLEAN);

                    $cells[0] = new stdClass();
                    $cells[0]->class='cell c0';
                    $cells[0]->data=get_string('urltosubmit', 'turnitintool').turnitintool_help_icon('urltosubmit',get_string('urltosubmit','turnitintool'),'turnitintool',true,false,'',true);
                    $cells[1] = new stdClass();
                    $cells[1]->data='<input type="text" name="submissionurl" class="formwide" value="'.$submissionurl.'" />';
                    $table->rows[6] = new stdClass();
                    $table->rows[6]->class='r0';
                    $table->rows[6]->cells=$cells;
                }

                $checked='';
                if (!is_null($param_agreement)) {
                    $checked=' checked';
                }

                if ( has_capability('mod/turnitintool:grade', $context) OR empty($CFG->turnitin_agreement) ) {
                    unset($cells);
                    $cells[0] = new stdClass();
                    $cells[0]->class='cell c0';
                    $cells[0]->data='';
                    $cells[1] = new stdClass();
                    $cells[1]->data='<input type="hidden" name="agreement" value="1" />';
                    $table->rows[7] = new stdClass();
                    $table->rows[7]->class='r1';
                    $table->rows[7]->cells=$cells;
                } else {
                    unset($cells);
                    $cells[0] = new stdClass();
                    $cells[0]->class='cell c0';
                    $cells[0]->data='<input type="checkbox" name="agreement" value="1"'.$checked.' />';
                    $cells[1] = new stdClass();
                    $cells[1]->data=$CFG->turnitin_agreement;
                    $table->rows[7] = new stdClass();
                    $table->rows[7]->class='r1';
                    $table->rows[7]->cells=$cells;
                }

                unset($cells);
                $cells[0] = new stdClass();
                $cells[0]->class='cell c0';
                $cells[0]->data='&nbsp;';
                $cells[1] = new stdClass();
                $cells[1]->data='<input name="submitbutton" type="submit" value="'.get_string('addsubmission', 'turnitintool').'" />';
                $table->rows[8] = new stdClass();
                $table->rows[8]->class='r0';
                $table->rows[8]->cells=$cells;
            }

        }

        $output.=turnitintool_print_table($table,true);

        if ($param_type>0) {
            $output.='
                    <script language="javascript" type="text/javascript">updateSubForm(submissionArray,stringsArray,document.submissionform,'.$turnitintool->reportgenspeed.',"'.$utype.'");</script>
            </form>
                    ';
        }
        $output.=turnitintool_box_end(true).'<br />';
    } else {
        $output=turnitintool_box_start('generalbox boxwidthwide boxaligncenter eightyfive', 'submitbox',true);
        if (turnitintool_count_records_select('turnitintool_parts', "turnitintoolid='".$turnitintool->id."' AND deleted=0 AND dtstart < ".time()." AND dtdue > ".time())==0) {
            // Due date has passed
            $output.=get_string('nosubmissionsdue','turnitintool');
        } else {
            // Due date has not passed
            // Count the number of students (numusers) enrolled on this course
            $context = get_context_instance(CONTEXT_MODULE, $cm->id);
            $users=get_users_by_capability($context,'mod/turnitintool:submit','u.id','','','',turnitintool_module_group($cm),'',false);
            $numusers=(!is_array($users)) ? 0 : count($users);
            if ($numusers>0) {
                $output.=get_string('submittedmax','turnitintool');
            } else {
                $output.=get_string('noenrolledstudents','turnitintool');
            }
        }
        $output.=turnitintool_box_end(true).'<br />';
    }

    return $output;
}
/**
 * Get the turnitintool_parts part name from the part number
 *
 * @param var $partid The Part ID of the part in turnitintool_parts
 * @return string Returns the part name or false if the part is not found
 */
function turnitintool_partnamefromnum($partid) {
    if ($part=turnitintool_get_record('turnitintool_parts','id',$partid)) {
        return $part->partname;
    } else {
        return false;
    }
}
/**
 * Checks to see if the same submission title has been used in the assignment already
 *
 * @param object $turnitintool The turnitintool object for this activity
 * @param string $title The title to check against
 * @return boolean Duplicate was found / not found
 */
function turnitintool_duplicate_submission_title($turnitintool,$title,$userid) {
    if (!$result=turnitintool_get_records_select('turnitintool_submissions',"turnitintoolid=".$turnitintool->id." AND submission_title='".$title."' AND userid=".$userid)) {
        $return=false;
    } else {
        $return=true;
    }
    return $return;
}
/**
 * Checks to see if the same submission has already been made to this assignment part
 *
 * @global object
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param var $partid Part ID of the assignment part to check
 * @param var $userid User ID of the user to check
 * @return boolean Submission was found / not found
 */
function turnitintool_checkforsubmission($cm,$turnitintool,$partid,$userid) {
    global $USER,$CFG;

    if (!$userdata=turnitintool_get_record('user','id',$userid)) {
        turnitintool_print_error('usergeterror', 'turnitintool', NULL, NULL, __FILE__, __LINE__);
        exit();
    }
    $loaderbar=NULL;
    $tii = new turnitintool_commclass(turnitintool_getUID($userdata),$userdata->firstname,$userdata->lastname,$userdata->email,1,$loaderbar);
    $tii->startSession();
    $turnitinuser=turnitintool_usersetup($userdata,get_string('userprocess','turnitintool'),$tii,$loaderbar);

    if ($tii->getRerror()) {
        if ($tii->getAPIunavailable()) {
            turnitintool_print_error('apiunavailable','turnitintool',NULL,NULL,__FILE__,__LINE__);
        } else {
            turnitintool_print_error($tii->getRmessage(),NULL,NULL,NULL,__FILE__,__LINE__);
        }
        exit();
    }
    $tii->endSession();
    $loaderbar = new turnitintool_loaderbarclass(2);

    $owner=turnitintool_get_owner($turnitintool->course);
    $post = new stdClass();
    $post->ctl=turnitintool_getCTL($turnitintool->course);
    $post->cid=turnitintool_getCID($turnitintool->course);
    $post->tem=$owner->email;
    if (!$part=turnitintool_get_record('turnitintool_parts','id',$partid)) {
        turnitintool_print_error('partgeterror', 'turnitintool', null, null, __FILE__, __LINE__);
        exit();
    }
    $post->assignid=turnitintool_getAID($part->id); // Get the Assignment ID for this Assignment / Turnitintool instance
    $post->assign=$turnitintool->name.' - '.$part->partname.' (Moodle '.$post->assignid.')';

    $status = new stdClass();
    $status->user=get_string('student','turnitintool');
    $status->proc=1;
    $tii->listSubmissions($post,get_string('updatingscores','turnitintool',$status));
    $tiisub_array=$tii->getSubmissionArray();
    foreach ($tiisub_array as $key => $sub_object) {
        // Check to see if we already have the submission entry for this Object ID
        if (!$tiisubcheck=turnitintool_get_record('turnitintool_submissions','submission_objectid',$key)) {
            // No we dont have it, add it to the submission table
            $subinsert['userid']=$userid;
            $subinsert['turnitintoolid']=$turnitintool->id;
            $subinsert['submission_part']=$part->id;
            $subinsert['submission_type']=1;
            $subinsert['submission_title']=$sub_object['title'];
            $subinsert['submission_filename']=str_replace(" ","_",$sub_object['title']).'.doc';
            $subinsert['submission_objectid']=$key;
            $subinsert['submission_score']=$sub_object['overlap'];
            $subinsert['submission_grade']=$sub_object['grademark'];
            $subinsert['submission_status']=get_string('submissionuploadsuccess','turnitintool');
            $subinsert['submission_queued']=0;
            $subinsert['submission_attempts']=( $sub_object["student_view"] > 0 ) ? strtotime($value["student_view"]) : 0;
            $subinsert['submission_modified']=strtotime($sub_object['date_submitted']);
            $subinsert['submission_nmuserid']=0;
            $subinsert['submission_unanon']=(!isset($sub_object['anon']) AND !is_null($sub_object['anon']) AND !$sub_object['anon']) ? 1 : 0;
            if (!$insertid=turnitintool_insert_record('turnitintool_submissions',$subinsert)) {
                turnitintool_print_error('submissionupdateerror', 'turnitintool', null, null, __FILE__, __LINE__);
                exit();
            }
        } else { // Found it, convert Non Moodle Entry to real user submission
            $subupdate['id']=$tiisubcheck->id;
            $subupdate['userid']=$userid;
            $subupdate['submission_nmuserid']=0;
            $subupdate['submission_nmfirstname']='';
            $subupdate['submission_nmlastname']='';
            if (!$updateid=turnitintool_update_record('turnitintool_submissions',$subupdate)) {
exit();
                turnitintool_print_error('submissionupdateerror', 'turnitintool', null, null, __FILE__, __LINE__);
                exit();
            }
        }
    }

    $submitted=turnitintool_get_records_select('turnitintool_submissions','submission_part='.$partid.' AND userid='.$userid);

    $loaderbar->endloader();
    unset($loaderbar);
    if (!$submitted) {
        return 0;
    } else {
        $submitted=current($submitted);
        $part=turnitintool_get_record('turnitintool_parts','id',$partid);
        $submitted->dtdue=$part->dtdue;
        $submitted->dtpost=$part->dtpost;
        return $submitted;
    }
}

/**
 * Takes the submitted file and adds it to the Moodle file area
 *
 * @global object
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param var $userid User ID of the user to check
 * @param array $post POST Array of the submission form of the user to check
 * @return boolean Submission was found / not found
 */
function turnitintool_dofileupload($cm,$turnitintool,$userid,$post) {
    global $USER,$CFG;
    $param_do=optional_param('do',null,PARAM_CLEAN);

    $error=false;
    $notice=array("error"=>'',"subid"=>'');

    $submissiontitle='';
    if (isset($post['submissiontitle'])) {
        $submissiontitle=str_replace("<","",$post['submissiontitle']);
        $submissiontitle=str_replace(">","",$submissiontitle);
    }

    if (empty($_FILES['submissionfile']['name'])) {
        $notice["error"].=get_string('submissionfileerror','turnitintool').'<br />';
        $error=true;
    }

    if (empty($submissiontitle)) {
        $notice["error"].=get_string('submissiontitleerror','turnitintool').'<br />';
        $error=true;
    }

    if (!isset($post['agreement'])) {
        $notice["error"].=get_string('submissionagreementerror','turnitintool').'<br />';
        $error=true;
    }

    $checksubmission=turnitintool_checkforsubmission($cm,$turnitintool,$post['submissionpart'],$userid);

    if (!$error AND isset($checksubmission->id) AND $turnitintool->reportgenspeed==0) {
        // Kill the script here as we do not want double errors
        // We only get here if there are no other errors
        turnitintool_print_error('alreadysubmitted','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    $resubmission=false;
    if (isset($checksubmission->id) AND $turnitintool->reportgenspeed>0) {
        $resubmission=true;
    }

    if ($resubmission AND $checksubmission->dtdue<time()) {
        turnitintool_print_error('alreadysubmitted','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    $explode = explode('.',$_FILES['submissionfile']['name']);
    $extension=array_pop($explode);
    $_FILES['submissionfile']['name']=$post['submissionpart'].'_'.time().'_'.$userid.'.'.$extension;

    $upload = new upload_manager();
    if (!$upload->preprocess_files()) {
        $notice["error"].=$upload->notify;
        $error=true;
    }

    if (!$error) {
        $submitobject = new object();
        $submitobject->userid=$userid;
        $submitobject->turnitintoolid=$turnitintool->id;
        $submitobject->submission_part=$post['submissionpart'];
        $submitobject->submission_type=$post['submissiontype'];
        $submitobject->submission_filename=$_FILES['submissionfile']['name'];
        $submitobject->submission_queued=null;
        $submitobject->submission_attempts=0;
        $submitobject->submission_gmimaged=0;
        $submitobject->submission_status=null;
        $submitobject->submission_modified=time();
        $submitobject->submission_objectid=(!isset($checksubmission->submission_objectid))
                ? null : $checksubmission->submission_objectid;

        if (!isset($checksubmission->submission_unanon) OR $checksubmission->submission_unanon) {
            // If non anon resubmission or new submission set the title as what was entered in the form
            $submitobject->submission_title=$submissiontitle;
            if (!$turnitintool->anon) {
                // If not anon assignment and this is a non anon resubmission or a new submission set the unanon flag to true (1)
                $submitobject->submission_unanon=1;
            }
        }

        if (!$resubmission) {
            if (!$submitobject->id=turnitintool_insert_record('turnitintool_submissions',$submitobject)) {
                turnitintool_print_error('submissioninserterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }
        } else {
            $submitobject->id=$checksubmission->id;
            $submitobject->submission_score=null;
            $submitobject->submission_grade=null;
            if (!turnitintool_update_record('turnitintool_submissions',$submitobject)) {
                turnitintool_print_error('submissionupdateerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            } else {
                $submitobject->id=$checksubmission->id;
            }
        }

        if (is_callable("get_file_storage")) {
            $fs = get_file_storage();
            $file_record = array('contextid'=>$cm->id,
                                 'component'=>'mod_turnitintool',
                                 'filearea'=>'submission',
                                 'itemid'=>$submitobject->id,
                                 'filepath'=>'/',
                                 'filename'=>$submitobject->submission_filename,
                                 'userid'=>$submitobject->userid);
            if (!$fs->create_file_from_pathname($file_record, $_FILES['submissionfile']['tmp_name'])) {
                turnitintool_delete_records('turnitintool_submissions','id',$submitobject->id);
                turnitintool_print_error('fileuploaderror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }
        } else {
            $destination=turnitintool_file_path($cm,$turnitintool,$userid);
            if (!$upload->save_files($destination)) {
                turnitintool_delete_records('turnitintool_submissions','id',$submitobject->id);
                turnitintool_print_error('fileuploaderror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }
        }

        if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id)) AND !$turnitintool->autosubmission) {
            turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=allsubmissions');
            exit();
        } else if (!$turnitintool->autosubmission) {
            turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do='.$param_do);
            exit();
        }
        $notice["subid"]=$submitobject->id;

    }

    return $notice;
}
/**
 * Takes the submitted text and creates a file and adds it to the Moodle file area
 *
 * @global object
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param var $userid User ID of the user to check
 * @param array $post POST Array of the submission form of the user to check
 * @return boolean Submission was found / not found
 */
function turnitintool_dotextsubmission($cm,$turnitintool,$userid,$post) {
    global $USER,$CFG;
    $param_do=optional_param('do',null,PARAM_CLEAN);

    $error=false;
    $notice=array("error"=>'',"subid"=>'');

    $submissiontitle='';
    if (isset($post['submissiontitle'])) {
        $submissiontitle=str_replace("<","",$post['submissiontitle']);
        $submissiontitle=str_replace(">","",$submissiontitle);
    }

    if (empty($submissiontitle)) {
        $notice["error"].=get_string('submissiontitleerror','turnitintool').'<br />';
        $error=true;
    }

    if (empty($post['submissiontext'])) {
        $notice["error"].=get_string('submissiontexterror','turnitintool').'<br />';
        $error=true;
    }

    if (!isset($post['agreement'])) {
        $notice["error"].=get_string('submissionagreementerror','turnitintool').'<br />';
        $error=true;
    }

    $checksubmission=turnitintool_checkforsubmission($cm,$turnitintool,$post['submissionpart'],$userid);

    if (!$error AND isset($checksubmission->id) AND $turnitintool->reportgenspeed==0) {
        // Kill the script here as we do not want double errors
        // We only get here if there are no other errors
        turnitintool_print_error('alreadysubmitted','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    $resubmission=false;
    if (isset($checksubmission->id) AND $turnitintool->reportgenspeed>0) {
        $resubmission=true;
    }

    if ($resubmission AND $checksubmission->dtdue<time()) {
        turnitintool_print_error('alreadysubmitted','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    $filedata=stripslashes($post['submissiontext']);

    if (!$error) {

        $filename=$post['submissionpart'].'_'.time().'_'.$userid.'.txt';

        $submitobject = new object();
        $submitobject->userid=$userid;
        $submitobject->turnitintoolid=$turnitintool->id;

        $submitobject->submission_part=$post['submissionpart'];
        $submitobject->submission_type=$post['submissiontype'];
        $submitobject->submission_filename=$filename;
        $submitobject->submission_queued=null;
        $submitobject->submission_attempts=0;
        $submitobject->submission_gmimaged=0;
        $submitobject->submission_status=null;
        $submitobject->submission_modified=time();
        $submitobject->submission_objectid=(!isset($checksubmission->submission_objectid))
                ? null : $checksubmission->submission_objectid;

        if (!isset($checksubmission->submission_unanon) OR $checksubmission->submission_unanon) {
            // If non anon resubmission or new submission set the title as what was entered in the form
            $submitobject->submission_title=$submissiontitle;
            if (!$turnitintool->anon) {
                // If not anon assignment and this is a non anon resubmission or a new submission set the unanon flag to true (1)
                $submitobject->submission_unanon=1;
            }
        }

        if (!$resubmission) {
            if (!$submitobject->id = turnitintool_insert_record('turnitintool_submissions',$submitobject)) {
                turnitintool_print_error('submissioninserterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }
        } else {
            $submitobject->id=$checksubmission->id;
            $submitobject->submission_score=null;
            $submitobject->submission_grade=null;
            if (!turnitintool_update_record('turnitintool_submissions',$submitobject)) {
                turnitintool_print_error('submissionupdateerror','turnitintool',NULL,null,__FILE__,__LINE__);
                exit();
            }
        }

        if (is_callable("get_file_storage")) {
            $fs = get_file_storage();
            $file_record = array('contextid'=>$cm->id,
                                 'component'=>'mod_turnitintool',
                                 'filearea'=>'submission',
                                 'itemid'=>$submitobject->id,
                                 'filepath'=>'/',
                                 'filename'=>$submitobject->submission_filename,
                                 'userid'=>$submitobject->userid);
            if (!$fs->create_file_from_string($file_record, $filedata)) {
                turnitintool_delete_records('turnitintool_submissions','id',$submitobject->id);
                turnitintool_print_error('fileuploaderror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }
        } else {
            $filedir=$CFG->dataroot.'/'.turnitintool_file_path($cm,$turnitintool,$userid);
            if (!file_exists($filedir)) {
                mkdir($filedir,0777,true);
            }
            $fOpen=fopen($filedir.'/'.$filename,'w+');
            if (!fwrite($fOpen,$filedata)) {
                turnitintool_delete_records('turnitintool_submissions','id',$submitobject->id);
                turnitintool_print_error('filewriteerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }
        }

        $notice["subid"]=$submitobject->id;

        if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id)) AND !$turnitintool->autosubmission) {
            turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=allsubmissions');
            exit();
        } else if (!$turnitintool->autosubmission) {
            turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do='.$param_do);
            exit();
        }

    }

    return $notice;
}
/**
 * Creates a temp file for submission to Turnitin uses a random number
 * suffixed with the stored filename
 *
 * @param string $suffix The file extension for the upload
 * @return string $file The filepath of the temp file
 */
function turnitintool_tempfile($suffix) {
    global $CFG;
    $fp=false;
    $temp_dir=$CFG->dataroot.'/temp/turnitintool';
    if ( !file_exists( $temp_dir ) ) {
        mkdir( $temp_dir, $CFG->directorypermissions, true );
    }
    while(!$fp) {
        $file = $temp_dir.DIRECTORY_SEPARATOR.mt_rand().'.'.$suffix;
        $fp = @fopen($file, 'w');
    }
    fclose($fp);
    return $file;
}
/**
 * Takes the submission file and uploads it to Turnitin
 *
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param object $submission The submission object for this submission
 */
function turnitintool_upload_submission($cm,$turnitintool,$submission) {
    global $CFG, $USER;

    if (!$course = turnitintool_get_record('course','id',$turnitintool->course)) {
        turnitintool_print_error('coursegeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    if (!$part = turnitintool_get_record('turnitintool_parts','id',$submission->submission_part)) {
        turnitintool_print_error('partgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    $user=turnitintool_get_moodleuser($submission->userid,NULL,__FILE__,__LINE__);
    $owner = turnitintool_get_owner($turnitintool->course);
    $post = new stdClass();
    $post->oid=(!is_null($submission->submission_objectid)) ? $submission->submission_objectid : '';

    $loaderbar = new turnitintool_loaderbarclass(4); // (2xStart/End Session and Submit Paper total 3
    $tii = new turnitintool_commclass(turnitintool_getUID($user),$user->firstname,$user->lastname,$user->email,1,$loaderbar);
    $tii->startSession();

    // Set this user up with a Turnitin Account or check to see if an account has already been set up
    // Either return the stored ID OR store the New Turnitin User ID then return it
    // Use UTP student (1) as this is a submission [[[[
    $turnitinuser=turnitintool_usersetup($user,get_string('userprocess','turnitintool'),$tii,$loaderbar);
    if ($tii->getRerror()) {
        if ($tii->getAPIunavailable()) {
            turnitintool_print_error('apiunavailable','turnitintool',NULL,NULL,__FILE__,__LINE__);
        } else {
            turnitintool_print_error($tii->getRmessage(),NULL,NULL,NULL,__FILE__,__LINE__);
        }
        exit();
    }
    $turnitinuser_id=$turnitinuser->turnitin_uid;
    // ]]]]

    $post->cid=turnitintool_getCID($course->id); // Get the Turnitin Class ID for Course
    $post->assignid=turnitintool_getAID($part->id); // Get the Assignment ID for this Assignment / Turnitintool instance

    $post->ctl=turnitintool_getCTL($course->id);
    $post->assignname=$turnitintool->name.' - '.$part->partname.' (Moodle '.$post->assignid.')';

    $post->tem=turnitintool_get_tutor_email($owner->id);

    $post->papertitle=$submission->submission_title;

    if (is_callable("get_file_storage")) {
        $fs = get_file_storage();
        $file = $fs->get_file($cm->id,'mod_turnitintool','submission',$submission->id,'/',$submission->submission_filename);
        $tempname = turnitintool_tempfile('_'.$submission->submission_filename);
        $tempfile=fopen($tempname,"w");
        fwrite($tempfile,$file->get_content());
        fclose($tempfile);
        $filepath=$tempname;
        turnitintool_activitylog("SUBID: ".$submission->id." - Using 2.0 File API","UPLOAD");
        turnitintool_activitylog("FILE PATH: ".$filepath);
    } else {
        $tempname=null;
        $filepath=$CFG->dataroot.'/'.turnitintool_file_path($cm,$turnitintool,$submission->userid).'/'.$submission->submission_filename;
        turnitintool_activitylog("SUBID: ".$submission->id." - Using pre 2.0 File API","UPLOAD");
        turnitintool_activitylog("FILE PATH: ".$filepath);
    }

    // Give join class 3 tries, fix for 220 errors, fail over and log failure in activity logs
    for ( $i = 0; $i < 3; $i++ ) {
        $tii->joinClass($post,get_string('joiningclass','turnitintool',''));
        if ( !$tii->getRerror() ) {
            break;
        } else {
            $loaderbar->total = $loaderbar->total + 1;
            turnitintool_activitylog( "Failed: " . $tii->getRcode(), "JOINCLASS FAILED" );
        }
    }

    if ($tii->getRerror()) {
        $reason=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
        turnitintool_print_error($reason." CODE: ".$tii->getRcode(),NULL,NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    // Upload the file to Turnitin
    $tii->submitPaper($post,$filepath,get_string('uploadingtoturnitin','turnitintool'));
    if (!is_null($tempname)) {
        unlink($tempname); // If we made a temp file earlier for the Moodle 2 file API delete it here
    }

    $update=new object();

    if ($tii->getRerror()) {
        if ($tii->getAPIunavailable()) {
            $status=get_string('apiunavailable','turnitintool');
            $queued=1;
        } else {
            $status=$tii->getRmessage();
            $queued=0;
        }
        if (!$queued) {
            $update->submission_score=null;
            $update->submission_status=$status;
            $update->submission_queued=$queued;
            $update->submission_modified=time();
            $update->id=$submission->id;

            if (!turnitintool_update_record('turnitintool_submissions',$update)) {
                turnitintool_print_error('submissionupdateerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }
            turnitintool_print_error($tii->getRmessage()." CODE: ".$tii->getRcode(),NULL,NULL,NULL,__FILE__,__LINE__);
            exit();
        }
    } else {
        $status=get_string('submissionuploadsuccess','turnitintool');
        $update->submission_objectid=$tii->getObjectid();
        $queued=0;
    }

    $update->submission_status=$status;
    $update->submission_queued=$queued;
    $update->submission_attempts=0;
    $update->submission_modified=time();
    $update->id=$submission->id;

    if (!turnitintool_update_record('turnitintool_submissions',$update)) {
        turnitintool_print_error('submissionupdateerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    if (function_exists('events_trigger')) {
        // Trigger assessable_submitted event on submission.
        $eventdata = new stdClass();
        $eventdata->modulename = 'turnitintool';
        $eventdata->cmid = $cm->id;
        $eventdata->itemid = $submission->id;
        $eventdata->courseid = $course->id;
        $eventdata->userid = $USER->id;
        events_trigger('assessable_submitted', $eventdata);
    }

    add_to_log($turnitintool->course, "turnitintool", "submit", "view.php?id=$cm->id", "User submitted '$submission->submission_title'", "$cm->id", $user->id);

    $tii->endSession();

    if (has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=allsubmissions');
        exit();
    } else {
        turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=submissions');
        exit();
    }
    unset($tii);
    exit();

}

/**
 * Outputs the file type array for acceptable file type uploads
 *
 * @param boolean $setup True if the call is from the assignment activity setup screen
 * @param array The array of filetypes ready for the modform parameter
 */
function turnitintool_filetype_array($setup=true) {
    $output = array(
        1 => get_string('fileupload','turnitintool'),
        2 => get_string('textsubmission','turnitintool')
    );
    if ($setup) {
        $output[3] = '--------------------';
        $output[0] = get_string('anytype','turnitintool');
    }
    return $output;
}
/**
 * A Standard Moodle function that moodle executes at the time the cron runs
 */
function turnitintool_cron() {
}
/**
 * Synchronises the assignment part settings with the settings that Turnitin has for the assignment parts
 *
 * @global object
 * @param object $cm The moodle course module object for this instance
 * @param object $turnitintool The turnitintool object for this activity
 * @param boolean $forced True if we want ot force the operation otherwise determined by a session array updatedconfig
 * @param object $loaderbar Passed by reference can be NULL if no loader bar is used
 * @return boolean Returns false on failure
 */
function turnitintool_synch_parts($cm,$turnitintool,$forced=false,$loaderbar=NULL) {
    $param_type=optional_param('type',null,PARAM_CLEAN);

    if (((isset($_SESSION['updatedconfig'][$turnitintool->id]) AND $_SESSION['updatedconfig'][$turnitintool->id]==1) OR !is_null($param_type)) AND !$forced) {
        return false;
    } else {
        if (!$parts=turnitintool_get_records_select('turnitintool_parts','turnitintoolid='.$turnitintool->id.' AND deleted=0')) {
            mtrace(get_string('partgeterror','turnitintool').' - ID: '.$turnitintool->id.'\n');
        }
        $partsarray=array();
        $owner=turnitintool_get_owner($turnitintool->course);
        $tii=new turnitintool_commclass(turnitintool_getUID($owner),$owner->firstname,$owner->lastname,$owner->email,2,$loaderbar);
        $tii->startSession();
        $tiitooldone=false;
        foreach ($parts as $part) {
            $post = new stdClass();
            $post->cid=turnitintool_getCID($turnitintool->course);
            $post->ctl=turnitintool_getCTL($turnitintool->course);
            $post->assign=$turnitintool->name.' - '.$part->partname.' (Moodle: '.$part->tiiassignid.')';
            $post->assignid=$part->tiiassignid;
            $post->assignid=$part->maxmarks;

            $tii->queryAssignment($post,get_string('synchassignments','turnitintool'));

            $assignObj=$tii->getAssignmentObject();

            if (!$tii->getRerror()) {

                if (!$tiitooldone) {

                    $tiiupdate = new stdClass();
                    $tiiupdate->id=$turnitintool->id;
                    $tiiupdate->anon=$assignObj->anon;
                    $tiiupdate->reportgenspeed=$assignObj->report_gen_speed;
                    $tiiupdate->studentreports=$assignObj->s_view_report;
                    $tiiupdate->allowlate=$assignObj->late_accept_flag;
                    $tiiupdate->submitpapersto=$assignObj->submit_papers_to;
                    $tiiupdate->internetcheck=$assignObj->internet_check;
                    $tiiupdate->journalcheck=$assignObj->journal_check;
                    if (!$update=turnitintool_update_record('turnitintool',$tiiupdate)) {
                        mtrace(get_string('turnitintoolupdateerror','turnitintool').' - ID: '.$turnitintool->id.'\n');
                    }

                    $partupdate = new stdClass();
                    $partupdate->id=$part->id;
                    $partupdate->maxmarks=$assignObj->maxpoints;
                    $partupdate->dtstart=$assignObj->dtstart;
                    $partupdate->dtdue=$assignObj->dtdue;
                    $partupdate->dtpost=$assignObj->dtpost;
                    if (!$update=turnitintool_update_record('turnitintool_parts',$partupdate)) {
                        mtrace(get_string('partupdateerror','turnitintool').' - ID: '.$part->id.'\n');
                    }

                }

            }

        }
        $tii->endSession();

    }

}

/**
 * Function called by course/reset.php when resetting moodle course Moodle 1.8
 *
 * @param object $course The moodle course object for the course
 */
function turnitintool_reset_course_form($course) {
    echo '<p>'.get_string('turnitintoolresetinfo', 'turnitintool').'<br />';
    $options = array(
            '0'=>get_string('turnitintoolresetdata0','turnitintool').'<br />',
            '1'=>get_string('turnitintoolresetdata1','turnitintool').'<br />',
            '2'=>get_string('turnitintoolresetdata2','turnitintool').'<br />'
    );
    choose_from_menu($options,'reset_turnitintool','0');
    echo '</p>';
}
/**
 * Function called by course/reset.php when resetting moodle course Moodle 1.8
 *
 * @param object $data The data object passed by course reset
 */
function turnitintool_delete_userdata($data) {
    if ($data->reset_turnitintool==0) {
        $action='NEWCLASS';
        $status=turnitintool_duplicate_recycle($data->courseid,$action,true);
        if (!$status['error']) {
            notify(get_string('modulenameplural','turnitintool').': '.get_string('copyassigndata','turnitintool'), 'notifysuccess');
        } else {
            notify(get_string('modulenameplural','turnitintool').': '.get_string('copyassigndataerror','turnitintool'));
        }
    } else if ($data->reset_turnitintool==1) {
        $action='OLDCLASS';
        $status=turnitintool_duplicate_recycle($data->courseid,$action,true);
        if (!$status['error']) {
            notify(get_string('modulenameplural','turnitintool').': '.get_string('replaceassigndata','turnitintool'), 'notifysuccess');
        } else {
            notify(get_string('modulenameplural','turnitintool').': '.get_string('replaceassigndataerror','turnitintool'));
        }
    }

    turnitintool_duplicate_recycle($data->courseid,$action);
}

/**
 * Function called by course/reset.php when resetting moodle course on Moodle 1.9+
 * To build the element for the reset form
 *
 * @param object $mform The mod form object passed by reference by course reset
 */
function turnitintool_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'turnitintoolheader', get_string('modulenameplural', 'turnitintool'));
    $options = array(
            '0'=>get_string('turnitintoolresetdata0','turnitintool').'<br />',
            '1'=>get_string('turnitintoolresetdata1','turnitintool').'<br />',
            '2'=>get_string('turnitintoolresetdata2','turnitintool').'<br />'
    );
    $mform->addElement('select', 'reset_turnitintool', get_string('selectoption','turnitintool'),$options);
}
/**
 * Function called by course/reset.php when resetting moodle course on Moodle 1.9+
 * To actually reset / recycle the data
 *
 * @param object $data The data object passed by course reset
 * @return array The Result of the turnitintool_duplicate_recycle call
 */
function turnitintool_reset_userdata($data) {
    $status = array();
    if ($data->reset_turnitintool==0) {
        $status = turnitintool_duplicate_recycle($data->courseid,'NEWCLASS');
    } else if ($data->reset_turnitintool==1) {
        $status = turnitintool_duplicate_recycle($data->courseid,'OLDCLASS');
    }
    return $status;
}
/**
 * Function called by course/reset.php when resetting moodle course on Moodle 1.9+
 * To reset / recycle the course data using default values
 *
 * @param object $course The course object passed by moodle
 * @return array The result array
 */
function turnitintool_reset_course_form_defaults($course) {
    return array('reset_turnitintool'=>0);
}

/**
 * Function called by course/reset.php when resetting moodle course on Moodle 1.9+
 * Used to duplicate or reset a courses Turnitin activities
 *
 * @global object
 * @param var $courseid The course ID for the course to reset
 * @param string $action The action to use OLDCLASS or NEWCLASS
 * @param boolean $legacy True for 1.8 Moodle False for 1.9+ Moodle
 * @return array The status array to pass to turnitintool_reset_userdata
 */
function turnitintool_duplicate_recycle($courseid,$action,$legacy=false) {
    set_time_limit(0);
    global $CFG, $USER;
    if (!$turnitintools=turnitintool_get_records('turnitintool','course',$courseid)) {
        turnitintool_print_error('assigngeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }
    if (!$course=turnitintool_get_record('course','id',$courseid)) {
        turnitintool_print_error('coursegeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }
    $partsarray=array();
    $loaderbar=NULL;
    $owner=turnitintool_get_owner($courseid);
    $uid=turnitintool_getUID($owner);
    if (is_null($uid)) {
        // In the unlikely event we have no Turnitin owner for this class to reset/recycle then we need
        // to make a new owner based on the logged in user. This scenario may occur if all classes have been reset
        // or all of the Turnitin data has been imported from backups without user data.
        $tii = new turnitintool_commclass(turnitintool_getUID($USER),$USER->firstname,$USER->lastname,$USER->email,2,$loaderbar);
        $userid=turnitintool_usersetup($USER, '', $tii, $loaderbar);
        $uid=$userid->turnitin_uid;
        $owner=$USER;
    } else {
        $tii = new turnitintool_commclass(turnitintool_getUID($owner),$owner->firstname,$owner->lastname,$owner->email,2,$loaderbar);
    }
    $tii->startSession();
    foreach ($turnitintools as $turnitintool) {
        if (!$parts=turnitintool_get_records_select('turnitintool_parts','turnitintoolid='.$turnitintool->id.' AND deleted=0')) {
            turnitintool_print_error('partgeterror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        }
        // Now build up the part array
        foreach ($parts as $part) {
            $partsarray[$courseid][$turnitintool->id][$part->id]['cid']=turnitintool_getCID($courseid);
            $partsarray[$courseid][$turnitintool->id][$part->id]['ctl']=turnitintool_getCTL($courseid);
            $partsarray[$courseid][$turnitintool->id][$part->id]['assign']=$turnitintool->name.' - '.$part->partname.' (Moodle: '.$part->tiiassignid.')';
            $partsarray[$courseid][$turnitintool->id][$part->id]['assignid']=$part->tiiassignid;
            $partsarray[$courseid][$turnitintool->id][$part->id]['max_points']=$part->maxmarks;
            $partsarray[$courseid][$turnitintool->id][$part->id]['turnitintool']=$turnitintool;
        }
    }


    // ----------------------------------
    // IF action EQUALS 'OLDCLASS'
    // ----------------------------------
    // Class on TII: Dont Create New
    // Class in DB: Leave Alone
    // Parts in DB: Replace IDs with New
    // Old Parts on TII: Delete
    // ----------------------------------
    // ELSE IF action EQUALS 'NEWCLASS'
    // ----------------------------------
    // Class on TII: Create New
    // Class in DB: Replace IDs with New
    // Parts in DB: Replace IDs with New
    // Old Parts on TII: Leave Alone
    // ----------------------------------

    if ($action=="OLDCLASS") {
        $newclassid=turnitintool_getCID($courseid);
        $newclasstitle=turnitintool_getCTL($courseid);
    } else {
        $oldclassid=turnitintool_getCID($courseid);
        $oldclasstitle=turnitintool_getCTL($courseid);

        // Delete old TII Class Link Data
        if (!$delete=turnitintool_delete_records('turnitintool_courses','courseid',$courseid)) {
            turnitintool_print_error('coursedeleteerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
            exit();
        }

        // Create a new class to use with new parts
        $turnitincourse=turnitintool_classsetup($course,$owner,'',$tii,$loaderbar);
        $newclassid=$turnitincourse->turnitin_cid;
        $newclasstitle=$turnitincourse->turnitin_ctl;
    }

    // Do the loop to create the new parts and swap over the stored TII Part IDs
    foreach ($partsarray as $classid => $v1) {
        foreach ($v1 as $assignid => $v2) {
            foreach ($v2 as $partid => $data) {

                // Get the current assignment part settings from Turnitin
                $post = new stdClass();
                $post->cid=$data['cid'];
                $post->ctl=$data['ctl'];
                $post->assign=$data['assign'];
                $post->assignid=$data['assignid'];
                $tii->queryAssignment($post,'');

                $assignObj=$tii->getAssignmentObject();
                if ($tii->getRerror()) {
                    $erroroutput[]=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                }

                $post->cid='';
                $post->uid='';
                $post->assignid='';

                $post->ctl=$newclasstitle;
                $uniquestring=strtoupper(uniqid());
                $post->name=current(explode(' (Moodle: ',$post->assign)).' (Moodle: '.$uniquestring.')';
                $post->dtstart=time();
                $post->dtdue=strtotime('+7 days');
                $post->dtpost=strtotime('+7 days');
                $post->s_view_report=$assignObj->s_view_report;
                $post->anon=$assignObj->anon;
                $post->report_gen_speed=$assignObj->report_gen_speed;
                $post->late_accept_flag=$assignObj->late_accept_flag;
                $post->submit_papers_to=$assignObj->submit_papers_to;
                $post->s_paper_check=$assignObj->s_paper_check;
                $post->internet_check=$assignObj->internet_check;
                $post->journal_check=$assignObj->journal_check;
                $post->max_points=$assignObj->maxpoints;

                // Synch the rest of the reset data from the turnitintool table
                $post->exclude_biblio=$data['turnitintool']->excludebiblio;
                $post->exclude_quoted=$data['turnitintool']->excludequoted;
                $post->exclude_value=$data['turnitintool']->excludevalue;
                $post->exclude_type=$data['turnitintool']->excludetype;
                $post->erater=$data['turnitintool']->erater;
                $post->erater_handbook=$data['turnitintool']->erater_handbook;
                $post->erater_dictionary=$data['turnitintool']->erater_dictionary;
                $post->erater_spelling=$data['turnitintool']->erater_spelling;
                $post->erater_grammar=$data['turnitintool']->erater_grammar;
                $post->erater_usage=$data['turnitintool']->erater_usage;
                $post->erater_mechanics=$data['turnitintool']->erater_mechanics;
                $post->erater_style=$data['turnitintool']->erater_style;

                // Create the Part on TII without IDs
                $tii->createAssignment($post,'INSERT','');

                if ($tii->getRerror()) {
                    $erroroutput[]=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                }

                $newassignid=$tii->getAssignid();
                $post->currentassign=current(explode(' (Moodle: ',$post->assign)).' (Moodle: '.$uniquestring.')';
                $post->name=current(explode(' (Moodle: ',$post->name)).' (Moodle: '.$newassignid.')';
                $post->assignid=$newassignid;
                $post->dtstart=$assignObj->dtstart;
                $post->dtdue=$assignObj->dtdue;
                $post->dtpost=$assignObj->dtpost;
                $post->cid=$newclassid;

                // Create the Part on TII with IDs
                $tii->createAssignment($post,'UPDATE','');

                if ($tii->getRerror()) {
                    $erroroutput[]=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                }

                $dbpart=new object();
                $dbpart->id=$partid;
                $dbpart->tiiassignid=$newassignid;
                if (!$update=turnitintool_update_record('turnitintool_parts',$dbpart)) {
                    turnitintool_print_error('partupdateerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                    exit();
                }

                if (!$delete=turnitintool_delete_records('turnitintool_submissions','submission_part',$partid)) {
                    turnitintool_print_error('submissiondeleteerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                    exit();
                }
                unset($post);
            }
        }
    }

    // Do the loop again for deletes this ensures we dont have a partly executed create delete run breaking things
    if ($action=="OLDCLASS") { // Only do this if we are recycling the same TII class

        foreach ($partsarray as $classid => $v1) {
            foreach ($v1 as $assignid => $v2) {
                foreach ($v2 as $partid => $data) {
                    //$null=NULL;
                    //$tii = new turnitintool_commclass($data['uid'],$data['ufn'],$data['uln'],$data['uem'],2,$null);
                    //$tii->startSession();

                    $post->cid=$data['cid'];
                    $post->ctl=$data['ctl'];
                    $post->name=$data['assign'];
                    $post->assignid=$data['assignid'];

                    $tii->deleteAssignment($post,'');

                    if ($tii->getRerror()) {
                        $erroroutput[]=($tii->getAPIunavailable()) ? get_string('apiunavailable','turnitintool') : $tii->getRmessage();
                    }
                    //$tii->endSession();

                }
            }
        }

    } else {
        if (isset($erroroutput)) { // If there was a comms error roll back the class ID data changes
            $classupdatedata=new object();
            $classupdatedata->id=$courseid;
            $classupdatedata->turnitin_cid=$oldclassid;
            $classupdatedata->turnitin_ctl=$oldclasstitle;

            if (!$classupdate=turnitintool_update_record('turnitintool_courses',$classupdatedata)) {
                turnitintool_print_error('classupdateerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
                exit();
            }
        }
    }

    $tii->endSession();

    if (isset($erroroutput) AND $CFG->turnitin_enablediagnostic=='1') {
        $errors='-----------<br />['.join(']<br />-----------<br />[',$erroroutput).']';
    } else {
        $errors='';
    }

    $status=NULL;
    if (!$legacy) {
        if (isset($erroroutput) AND $action=="NEWCLASS") {
            $status[] = array('component'=>get_string('modulenameplural','turnitintool'), 'item'=>get_string('copyassigndata','turnitintool').$errors, 'error'=>true);
        } else if (!isset($erroroutput) AND $action=="NEWCLASS") {
            $status[] = array('component'=>get_string('modulenameplural','turnitintool'), 'item'=>get_string('copyassigndata','turnitintool'), 'error'=>false);
        } else if (isset($erroroutput) AND $action=="OLDCLASS") {
            $status[] = array('component'=>get_string('modulenameplural','turnitintool'), 'item'=>get_string('replaceassigndata','turnitintool').$errors, 'error'=>true);
        } else if (!isset($erroroutput) AND $action=="OLDCLASS") {
            $status[] = array('component'=>get_string('modulenameplural','turnitintool'), 'item'=>get_string('replaceassigndata','turnitintool'), 'error'=>false);
        }
    } else {
        if (isset($erroroutput) AND $action=="NEWCLASS") {
            $status['error'] = true;
        } else if (!isset($erroroutput) AND $action=="NEWCLASS") {
            $status['error'] = false;
        } else if (isset($erroroutput) AND $action=="OLDCLASS") {
            $status['error'] = true;
        } else if (!isset($erroroutput) AND $action=="OLDCLASS") {
            $status['error'] = false;
        }
    }

    return $status;
}

/**
 * Function called by 1.9+ Moodles to populate gradebook
 *
 * @param object $turnitintool The turnitintool object for this activity
 * @param object $thisuser The user object for this user
 * @return object Returns a Grade Object
 */
function turnitintool_buildgrades($turnitintool,$thisuser) {
    if ($submissions=turnitintool_get_records_select('turnitintool_submissions','turnitintoolid='.$turnitintool->id.' AND userid='.$thisuser->id.' AND submission_unanon=1')) {
        $grades = new stdClass();
        $grades->userid = $thisuser->id;
        $gradearray=turnitintool_grades($turnitintool->id);

        if ($turnitintool->grade < 0) {
            //Using a scale
            $overallgrade=( $gradearray->grades[$thisuser->id]=='-' ) ? NULL : $gradearray->grades[$thisuser->id];
        } else {
            $overallgrade=( $gradearray->grades[$thisuser->id]=='-' ) ? NULL : number_format( $gradearray->grades[$thisuser->id],1 );
        }
        $grades->rawgrade=$overallgrade;
        return $grades;
    } else {
        return new stdClass();
    }
}

/**
 * Function called by pre 1.9 Moodles to populate gradebook
 *
 * @param var $turnitintoolid The turnitintool id for this activity
 * @return object Returns a Grade Object
 */
function turnitintool_grades($turnitintoolid) {
    $return=false;
    if ($submissions=turnitintool_get_records_select('turnitintool_submissions','turnitintoolid='.$turnitintoolid.' AND submission_unanon=1')) {
        $turnitintool=turnitintool_get_record('turnitintool','id',$turnitintoolid);
        $parts=turnitintool_get_records_select('turnitintool_parts',"turnitintoolid=".$turnitintoolid." AND deleted=0",NULL,"dtpost DESC");
        $partsarray=array_keys($parts);
        $cm=get_coursemodule_from_instance("turnitintool", $turnitintool->id, $turnitintool->course);
        $scale=turnitintool_get_record('scale','id',$turnitintool->grade*-1);

        // Convert the data object into a userid based more useful array
        // ie submission[userid][submission][key]=data [[[[
        foreach ($submissions as $submission) {
            $submission=get_object_vars($submission);
            $keys=array_keys($submission);
            $thisuserid=$submission["userid"];
            $thissubmissionid=$submission["id"];
            $usersubmissions[$thisuserid][$thissubmissionid] = new stdClass();
            for ($i=0;$i<count($keys);$i++) {
                if (in_array($submission['submission_part'],$partsarray)) {
                    $thiskey=$keys[$i];
                    $thisvalue=$submission[$thiskey];
                    $usersubmissions[$thisuserid][$thissubmissionid]->$thiskey=$thisvalue;
                }
            }
        }
        // ]]]]

        foreach ( $usersubmissions as $userid => $userdataarray ) {
            $grades[$userid]=turnitintool_overallgrade($userdataarray,$turnitintool->grade,$parts,$scale);
        }

        $return = new stdClass();
        $return->grades = $grades;
        $return->maxgrade = $turnitintool->grade;

    }

    return $return;
}

/**
 * Function called by moodle when installing module
 *
 * @return boolean
 */
//function turnitintool_install() {
//    return true;
//}
/**
 * Function called by moodle when uninstalling module
 *
 * @return boolean
 */
//function turnitintool_uninstall() {
//    return true;
//}
/**
 * Function called to check to see if the configuration screen has been populated
 *
 * @return boolean True if it has been populated False if it hasnt
 */
function turnitintool_check_config() {
    global $CFG;
    if (   !isset($CFG->turnitin_account_id)
            OR !isset($CFG->turnitin_enablediagnostic)
            OR !isset($CFG->turnitin_secretkey)
            OR !isset($CFG->turnitin_apiurl)
            OR !isset($CFG->turnitin_usegrademark)
            OR !isset($CFG->turnitin_useanon)
            OR !isset($CFG->turnitin_studentemail)) {
        return false;
    } else {
        return true;
    }
}
/**
 * Function called to check to see if the current user is the owner of the class
 *
 * @global object
 * @global object
 * @param var $courseid The ID of the course to check the owner of
 * @return boolean True if the current user is the owner False if not
 */
function turnitintool_is_owner($courseid) {
    global $USER,$CFG;
    if ($course=turnitintool_get_record('turnitintool_courses','courseid',$courseid)) {
        $ownerid=$course->ownerid;
    } else {
        $ownerid=NULL;
    }
    if ($ownerid==$USER->id) {
        return true;
    } else {
        return false;
    }
}
/**
 * Returns the Turnitin Class owner of the current course
 *
 * @global object
 * @param var $courseid The ID of the course to check the owner of
 * @return object The user object for the Turnitin Class Owner or NULL if there is no owner stored
 */
function turnitintool_get_owner($courseid) {
    global $CFG;
    if ($course=turnitintool_get_record('turnitintool_courses','courseid',$courseid)) {
        return turnitintool_get_moodleuser($course->ownerid,NULL,__FILE__,__LINE__);
    } else {
        return NULL;
    }
}

/**
 * Redirects the user to login to a Turnitin page
 *
 * @global object
 * @param var $userid The User ID of the user requesting the Turnitin Page
 * @param string $jumppage A string that represents the page to jump to possible strings are 'grade','report','submission' and 'download'
 * @param array The Query Array passed to this function
 */
function turnitintool_url_jumpto($userid,$jumppage,$turnitintool,$utp=null,$objectid=null,$partid=null,$export_data=null) {
    global $CFG;
    $thisuser=turnitintool_get_moodleuser($userid,NULL,__FILE__,__LINE__);
    $cm=get_coursemodule_from_instance("turnitintool", $turnitintool->id, $turnitintool->course);
    if ( $utp > 1 AND !has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id)) ) {
        turnitintool_print_error('permissiondeniederror','turnitintool',NULL,NULL,__FILE__,__LINE__);
    }
    $loaderbar = NULL;
    $tii = new turnitintool_commclass(turnitintool_getUID($thisuser),$thisuser->firstname,$thisuser->lastname,$thisuser->email,$utp,$loaderbar,false);
    $tii->startSession();
    $newuser=turnitintool_usersetup($thisuser,get_string('userprocess','turnitintool'),$tii,$loaderbar);

    $post = new stdClass();
    $post->cid = turnitintool_getCID( $turnitintool->course );
    $post->ctl = turnitintool_getCTL( $turnitintool->course );
    $post->paperid = $objectid;
    $owner = turnitintool_get_owner( $turnitintool->course ); // Get the default main tutor (used in tem only)
    $post->tem = $owner->email;
    $post->assignid = !is_null( $partid ) ? turnitintool_getAID( $partid ) : null;
    $post->assign = !is_null( $partid ) ? $turnitintool->name . ' - ' . turnitintool_partnamefromnum( $partid ) . ' (Moodle '.$post->assignid.')' : null;
    $post->export_data = $export_data;

    if ($utp > 1 AND !is_null($turnitintool)) {
        // If this is a tutor enrol them on the Turnitin class before redirecting
        $tii->enrolTutor($post,get_string('turnitintutorsadding','turnitintool'));
    }
    if ($tii->getRerror()) {
        if ($tii->getAPIunavailable()) {
            turnitintool_print_error('apiunavailable','turnitintool',NULL,NULL,__FILE__,__LINE__);
        } else {
            turnitintool_print_error($tii->getRmessage(),NULL,NULL,NULL,__FILE__,__LINE__);
        }
        exit();
    }
    $tii->endSession();
    $tii->uid=$newuser->turnitin_uid;
    if ($jumppage=='grade') {
        $url=$tii->getGradeMarkLink($post);
    } else if ($jumppage=='report') {
        $url=$tii->getReportLink($post);
    } else if ($jumppage=='submission') {
        $url=$tii->getSubmissionURL($post);
    } else if ($jumppage=='zipfile') {
        $url=$tii->bulkDownload($post);
    } else {
        header('Content-Disposition: attachment;');
        $url=$tii->getSubmissionDownload($post);
    }

    turnitintool_redirect($url);
    exit();
}
/**
 * Redirect function uses Javascript / User click redirect if the headers have been sent or header() if not
 *
 * @param string $url The URL to redirect to
 */
function turnitintool_redirect($url) {
    if (!headers_sent($f,$l)) {
        turnitintool_activitylog("header() REDIRECT START ".$url,"REDIRECT");
        header('Location: '.$url);
        turnitintool_activitylog("header() REDIRECT END ".$url,"REDIRECT");
    } else {
        turnitintool_activitylog("JS / META REDIRECT START ".$url,"REDIRECT");
        echo '
        <a href="'.$url.'" id="redirectlink">'.get_string('redirect','turnitintool').'</a>
        <script language="javascript">
        document.getElementById("redirectlink").style.display="none";
        location.href="'.$url.'";
        </script>
        <noscript>
        <meta http-equiv="Refresh" content="0;url='.$url.'" />
        </noscript>
        ';
        turnitintool_activitylog("JS / META REDIRECT END ".$url,"REDIRECT");
    }
    exit();
}

/**
 * Abstracted version of get_records_sql() to work with Moodle 1.8 through 2.0
 *
 * @param string $sql The SQL Query
 * @param array $params array of sql parameters
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return array An array of data objects
 */
function turnitintool_get_records_sql($sql,$params=NULL,$limitfrom=0,$limitnum=0) {
    global $DB, $CFG;
    if (is_callable(array($DB,'get_records_sql'))) {
        $return = $DB->get_records_sql($sql,$params,$limitfrom,$limitnum);
    } else {
        $sql = preg_replace('/\{([a-z][a-z0-9_]*)\}/', $CFG->prefix.'$1', $sql);
        $params = addslashes_recursive( $params );
        $sql = vsprintf( str_replace("?","'%s'",$sql), $params );
        //$sql = sql_magic_quotes_hack( $sql );
        $return = get_records_sql($sql,$limitfrom,$limitnum);
    }
    return $return;
}
/**
 * Abstracted version of count_records_sql() to work with Moodle 1.8 through 2.0
 *
 * @param string $sql The SQL Query
 * @param array $params array of sql parameters
 * @return array An array of data objects
 */
function turnitintool_count_records_sql($sql,$params=NULL) {
    global $DB;
    if (is_callable(array($DB,'count_records_sql'))) {
        return $DB->count_records_sql($sql,$params);
    } else {
        return count_records_sql($sql,$params);
    }
}
/**
 * Abstracts the sql_concat function to work across versions
 *
 * @param string $element
 * @return string
 */
function turnitintool_sql_concat() {
    global $DB;
    $args = func_get_args();
    if (is_callable(array($DB,'sql_concat'))) {
        return call_user_func_array( array($DB, 'sql_concat'), $args );
    } else {
        return call_user_func_array( 'sql_concat', $args );
    }
}
/**
 * Abstracted version of get_record() to work with Moodle 1.8 through 2.0
 *
 * @param string $table The Database Table
 * @param string $field1 the fieldname of the first field to check
 * @param string $value1 the value of the first field to check
 * @param string $field2 the fieldname of the second field to check
 * @param string $value2 the value of the second field to check
 * @param string $field3 the fieldname of the three field to check
 * @param string $value3 the value of the three field to check
 * @param string $fileds the fields to return in the array
 * @return array An array of data objects
 */
function turnitintool_get_record($table,$field1='',$value1='',$field2='',$value2='',$field3='',$value3='',$fields='*') {
    global $DB;
    if (is_callable(array($DB,'get_record'))) {
        if (!empty($field1)) {
            $select[$field1]=$value1;
        }
        if (!empty($field2)) {
            $select[$field2]=$value2;
        }
        if (!empty($field3)) {
            $select[$field3]=$value3;
        }
        $return = $DB->get_record($table,$select,$fields);
    } else {
        $return = get_record($table,$field1,$value1,$field2,$value2,$field3,$value3,$fields);
    }
    return $return;
}
/**
 * Abstracted version of get_records() to work with Moodle 1.8 through 2.0
 *
 * @param string $table The Database Table
 * @param string $field the fieldname of the field to check
 * @param string $value the value of the field to check
 * @param string $sort the sort order
 * @param string $fields the columns to return
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return array An array of data objects
 */
function turnitintool_get_records($table,$field='',$value='',$sort='',$fields='*',$limitfrom='',$limitnum='') {
    global $DB;
    if (is_callable(array($DB,'get_records'))) {
        if (!empty($field)) {
            $select[$field]=$value;
        } else {
            $select=array();
        }
        $return = $DB->get_records($table,$select,$sort,$fields,$limitfrom,$limitnum);
    } else {
        $return = get_records($table,$field,$value,$sort,$fields,$limitfrom,$limitnum);
    }
    return $return;
}
/**
 * Abstracted version of get_records_select() to work with Moodle 1.8 through 2.0
 *
 * @param string $table The Database Table
 * @param string $select the select SQL query
 * @param string $sort the sort order
 * @param string $fields the columns to return
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @return array An array of data objects
 */
function turnitintool_get_records_select($table,$select, $params = array(), $sort='',$fields='*',$limitfrom='',$limitnum='') {
    global $DB;
    if (is_callable(array($DB,'get_records_select'))) {
        $return = $DB->get_records_select($table,$select, $params,$sort,$fields,$limitfrom,$limitnum);
    } else {
        $return = get_records_select($table,$select,$sort,$fields,$limitfrom,$limitnum);
    }
    return $return;
}
/**
 * Abstracted version of get_records_select() to work with Moodle 1.8 through 2.0
 *
 * @param string $table The Database Table
 * @param string $select the select SQL query
 * @param string $fields the columns to return
 * @return array An array of data objects
 */
function turnitintool_get_record_select($table,$select,$params = array(),$fields='*') {
    global $DB;
    if (is_callable(array($DB,'get_record_select'))) {
        $return = $DB->get_record_select($table,$select,$params,$fields);
    } else {
        $return = get_record_select($table,$select,$fields);
    }
    return $return;
}
/**
 * Abstracted version of update_record() to work with Moodle 1.8 through 2.0
 *
 * @global object
 * @param string $table The Database Table
 * @param string $dataobject the data object to populate the database table with
 * @return boolean
 */
function turnitintool_update_record($table,$dataobject) {
    global $DB;
    $dataobject = json_decode(json_encode($dataobject));
    if (is_callable(array($DB,'update_record'))) {
        return $DB->update_record($table,$dataobject);
    } else {
        $cleanobj = turnitintool_cleanobject($dataobject);
        return update_record($table,$cleanobj);
    }
}
/**
 * Abstracted version of insert_record() to work with Moodle 1.8 through 2.0
 *
 * @global object
 * @param string $table The Database Table
 * @param string $dataobject the data object to populate the database table with
 * @param boolean $returnid return an ID or not
 * @param string $primarykey sets the primary key
 * @return int The ID of the row created
 */
function turnitintool_insert_record($table,$dataobject,$returnid=true,$primarykey='id') {
    global $DB;
    $dataobject = json_decode(json_encode($dataobject));
    if (is_callable(array($DB,'insert_record'))) {
        return $DB->insert_record($table,$dataobject,$returnid,$primarykey);
    } else {
        $cleanobj = turnitintool_cleanobject($dataobject);
        return insert_record($table,$cleanobj,$returnid,$primarykey);
    }
}
/**
 * Removes single quotes from strings being written to the database
 * Designed to remove database issues in Moodle 1.9 builds
 *
 * @param object $dataobject The object that we want to clean
 * @return object The cleaned object
 */
function turnitintool_cleanobject( $dataobject ) {
    foreach ($dataobject as &$value) {
        // Prevents issue with apostrophes being escaped in Moodle 1.9
        if(substr($value, -1) == "'"){
            $value = substr($value, 0, -2);
        }
        $value = str_replace(array("‘","’","‛","'","&#39;","&lsquo;","&rsquo;","`"), "", $value);
    }
    return $dataobject;
}
/**
 * Abstracted version of delete_records_select() to work with Moodle 1.8 through 2.0
 *
 * @param string $table The Database Table
 * @param string $select the select SQL query
 * @return boolean
 */
function turnitintool_delete_records_select($table,$select='', $params) {
    global $DB;
    if (is_callable(array($DB,'delete_records_select'))) {
        return $DB->delete_records_select($table,$select, $params);
    } else {
        return delete_records_select($table,$select);
    }
}
/**
 * Abstracted version of delete_records() to work with Moodle 1.8 through 2.0
 *
 * @param string $table The Database Table
 * @param string $field1 the fieldname of the first field to check
 * @param string $value1 the value of the first field to check
 * @param string $field2 the fieldname of the second field to check
 * @param string $value2 the value of the second field to check
 * @param string $field3 the fieldname of the three field to check
 * @param string $value3 the value of the three field to check
 * @return boolean
 */
function turnitintool_delete_records($table,$field1='',$value1='',$field2='',$value2='',$field3='',$value3='') {
    global $DB;
    if (is_callable(array($DB,'delete_records'))) {
        if (!empty($field1)) {
            $select[$field1]=(empty($value1)) ? null : $value1;
        }
        if (!empty($field2)) {
            $select[$field2]=(empty($value2)) ? null : $value2;
        }
        if (!empty($field3)) {
            $select[$field3]=(empty($value3)) ? null : $value3;
        }
        return $DB->delete_records($table,$select);
    } else {
        return delete_records($table,$field1,$value1,$field2,$value2,$field3,$value3);
    }
}
/**
 * Abstracted version of count_records_select() to work with Moodle 1.8 through 2.0
 *
 * @param string $table The Database Table
 * @param string $select the select SQL query
 * @param string $countitem The item to count
 * @return int Result count
 */
function turnitintool_count_records_select($table,$select='',$countitem='COUNT(*)') {
    global $DB;
    if (is_callable(array($DB,'count_records_select'))) {
        return $DB->count_records_select($table,$select,NULL,$countitem);
    } else {
        return count_records_select($table,$select,$countitem);
    }
}
/**
 * Abstracted version of count_records() to work with Moodle 1.8 through 2.0
 *
 * @param string $table The Database Table
 * @param string $field1 the fieldname of the first field to check
 * @param string $value1 the value of the first field to check
 * @param string $field2 the fieldname of the second field to check
 * @param string $value2 the value of the second field to check
 * @param string $field3 the fieldname of the three field to check
 * @param string $value3 the value of the three field to check
 * @return int Result count
 */
function turnitintool_count_records($table,$field1='',$value1='',$field2='',$value2='',$field3='',$value3='') {
    global $DB;
    if (is_callable(array($DB,'count_records'))) {
        if (!empty($field1)) {
            $select[$field1]=(empty($value1)) ? null : $value1;
        }
        if (!empty($field2)) {
            $select[$field2]=(empty($value2)) ? null : $value2;
        }
        if (!empty($field3)) {
            $select[$field3]=(empty($value3)) ? null : $value3;
        }
        return $DB->count_records($table,$select);
    } else {
        return count_records($table,$field1,$value1,$field2,$value2,$field3,$value3);
    }
}
/**
 * Abstracted version of search_users() to work with Moodle 1.8 through 2.0
 *
 * @param int $courseid The ID of the course
 * @param int $groupid The ID of the group
 * @param string $searchtext The text to search for
 * @param string $sort The column sort order
 * @param string $exceptions Comma separated list of users to exclude from the search
 * @return array
 */
function turnitintool_search_users($courseid, $groupid, $searchtext, $sort='', $exceptions='') {
    global $DB;
    if (is_callable(array($DB,'count_records')) AND !empty($exceptions)) {
        $explode=explode(",",$exceptions);
    } else if (is_callable(array($DB,'count_records'))) {
        $explode=array();
    } else {
        $explode=$exceptions;
    }
    return search_users($courseid, $groupid, $searchtext, $sort, $explode);
}
/**
 * Abstracted version of box_start() / print_box_start() to work with Moodle 1.8 through 2.0
 *
 * @param string $classes The CSS class for the box HTML element
 * @param string $ids An optional ID
 * @param boolean Return the output or print it to screen directly
 * @return string the HTML to output.
 */
function turnitintool_box_start($classes='generalbox', $ids='', $return=false) {
    global $OUTPUT;
    if (is_callable(array($OUTPUT,'box_start')) AND !$return) {
        echo $OUTPUT->box_start($classes,$ids);
    } else if (is_callable(array($OUTPUT,'box_start'))) {
        return $OUTPUT->box_start($classes,$ids);
    } else {
        return print_box_start($classes,$ids,$return);
    }
}
/**
 * Abstracted version of box_end() / print_box_end() to work with Moodle 1.8 through 2.0
 *
 * @param boolean Return the output or print it to screen directly
 * @return string the HTML to output.
 */
function turnitintool_box_end($return=false) {
    global $OUTPUT;
    if (is_callable(array($OUTPUT,'box_end')) AND !$return) {
        echo $OUTPUT->box_end();
    } else if (is_callable(array($OUTPUT,'box_end'))) {
        return $OUTPUT->box_end();
    } else {
        return print_box_end($return);
    }
}
/**
 * Abstracted version of helpbutton() / help_icon() to work with Moodle 1.8 through 2.0
 *
 * @param string $page The keyword that defines a help page
 * @param string $title The title of links
 * @param string $module Which module is the page defined in
 * @param mixed $image Use a help image for the link?  (true/false/"both")
 * @param boolean $linktext If true, display the title next to the help icon.
 * @param string $text If defined then this text is used in the page
 * @param boolean $return If true then the output is returned as a string, if false it is printed to the current page.
 * @param string $imagetext The full text for the helpbutton icon. If empty use default help.gif
 * @return string|void Depending on value of $return
 */
function turnitintool_help_icon($page, $title, $module='moodle', $image=true, $linktext=false, $text='', $return=false, $imagetext='') {
    global $OUTPUT;
    if (is_callable(array($OUTPUT,'help_icon'))) {
        if (!$return) {
            echo $OUTPUT->help_icon($page, 'turnitintool');
        } else {
            return $OUTPUT->help_icon($page, 'turnitintool');
        }
    } else {
        return helpbutton($page, $title, $module, $image, $linktext, $text, $return, $imagetext);
    }
}
/**
 * Abstracted version of mod_form help_icon to work with Moodle 1.8 through 2.0
 *
 * @param string $element The form element to apply help icon to
 * @param string $string The language string to use
 * @param string $module Which module is the page defined in
 * @param object $mform The mod_form object
 */
function turnitintool_modform_help_icon($element, $string, $module, $mform) {
    if (is_callable(array($mform,'addHelpButton'))) {
        $mform->addHelpButton($element, $string, $module);
    } else {
        $mform->setHelpButton($element, array($string, get_string($string, $module), $module));
    }
}
/**
 * Custom replacement for print_table removes dependencies on Moodles various table
 * methods and serve the turnitintool use perfectly and be backwards compatible
 *
 * @param object $table The table data object
 * @param boolean Return the output or print it to screen directly
 * @return string the HTML to output.
 */
function turnitintool_print_table($table, $return=false) {
    // Table Object use:
    // $table = new stdClass();
    // $table->width = '';
    // $table->id = '';
    // $table->class = '';
    // $table->tablealign = '';
    // $table->rows[0] = new stdClass();
    // $table->rows[0]->id = '';
    // $table->rows[0]->class = '';
    // $table->rows[0]->cells[0]->id = '';
    // $table->rows[0]->cells[0]->class = '';
    // $table->rows[0]->cells[0]->data = '';
    $width=(isset($table->width) AND strlen($table->width)>0) ? " width=\"".$table->width."\"" : "";
    $id=(isset($table->id) AND strlen($table->id)>0) ? " id=\"".$table->id."\"" : "";
    $class=(isset($table->class) AND strlen($table->class)>0) ? " class=\"".$table->class."\"" : "";
    $tablealign=(isset($table->tablealign) AND strlen($table->tablealign)>0) ? " align=\"".$table->tablealign."\"" : "";
    $style=(isset($table->style) AND strlen($table->style)>0) ? " style=\"".$table->style."\"" : "";
    $output="<table".$width.$id.$class.$tablealign.$style."><thead>\n";
    $thead = false;
    foreach ($table->rows as $row) {
        $class=(isset($row->class) AND strlen($row->class)>0) ? " class=\"".$row->class."\"" : "";
        $id=(isset($row->id) AND strlen($row->id)>0) ? " id=\"".$row->id."\"" : "";
        $output.="\t<tr".$id.$class.">\n";
        if ( isset( $row->cells ) ) {
            $celltag = 'td';
            $cells = $row->cells;
        } else if ( isset( $row->hcells ) ) {
            $celltag = 'th';
            $cells = $row->hcells;
        }
        foreach ($cells as $cell) {
            $class=(isset($cell->class) AND strlen($cell->class)>0) ? " class=\"".$cell->class."\"" : "";
            $id=(isset($cell->id) AND strlen($cell->id)>0) ? " id=\"".$cell->id."\"" : "";
            $data=(isset($cell->data) AND strlen($cell->data)>0) ? $cell->data : "&nbsp;";
            $output.="\t\t<".$celltag.$id.$class.">".$data."</".$celltag.">\n";
        }
        if ( !$thead ) {
            $thead = true;
            $body_ot = ( count( $table->rows ) == 1 ) ? '' : '<tbody>';
            $body_ct = ( count( $table->rows ) == 1 ) ? '' : '</tbody>';
            $output.="\t</tr></thead>$body_ot\n";
        } else {
            $output.="\t</tr>\n";
        }
    }
    $output.="$body_ct</table><br /><br />\n";
    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}
/**
 * Abstracted version of print_header() / header() to work with Moodle 1.8 through 2.0
 *
 * @param object $cm The moodle course module object for this instance
 * @param object $course The course object for this activity
 * @param string $title Appears at the top of the window
 * @param string $heading Appears at the top of the page
 * @param string $navigation Array of $navlinks arrays (keys: name, link, type) for use as breadcrumbs links
 * @param string $focus Indicates form element to get cursor focus on load eg  inputform.password
 * @param string $meta Meta tags to be added to the header
 * @param boolean $cache Should this page be cacheable?
 * @param string $button HTML code for a button (usually for module editing)
 * @param string $menu HTML code for a popup menu
 * @param boolean $usexml use XML for this page
 * @param string $bodytags This text will be included verbatim in the <body> tag (useful for onload() etc)
 * @param bool $return If true, return the visible elements of the header instead of echoing them.
 * @return mixed If return=true then string else void
 */
function turnitintool_header($cm,$course,$url,$title='', $heading='', $navigation='', $focus='',
        $meta='', $cache=true, $button='&nbsp;', $menu=null,
        $usexml=false, $bodytags='', $return=false) {
    global $DB,$PAGE,$OUTPUT,$CFG;

    if (is_callable(array($OUTPUT,'header'))) {

        $cmid=($cm!=NULL) ? $cm->id : NULL;
        $courseid=($course!=NULL) ? $course->id : NULL;

        if (!is_null($cmid)) {
            $category = $DB->get_record('course_categories', array('id'=>$course->category));
            $PAGE->navbar->ignore_active();
            if (isset($category->name)) $PAGE->navbar->add($category->name, new moodle_url($CFG->wwwroot.'/course/category.php', array('id'=>$course->category)));
            $PAGE->navbar->add($course->shortname, new moodle_url($CFG->wwwroot.'/course/view.php', array('id'=>$course->id)));
            $PAGE->navbar->add(get_string('modulenameplural', 'turnitintool'), new moodle_url($CFG->wwwroot.'/mod/turnitintool/index.php', array('id'=>$course->id)));
            $PAGE->navbar->add($title);
            $PAGE->set_button(update_module_button($cmid, $courseid, get_string('modulename', 'turnitintool')));
        }

        $url_array = explode( '/mod/turnitintool', $url, 2 );
        $url = '/mod/turnitintool/'.$url_array[1];
        $PAGE->set_url($url);
        $PAGE->set_title($title);
        $PAGE->set_heading($heading);

        if ($return) {
            return $OUTPUT->header();
        } else {
            echo $OUTPUT->header();
        }
    } else {
        return print_header($title,$heading,$navigation,$focus,$meta,$cache,$button,$menu,$usexml,$bodytags,$return);
    }
}
/**
 * Abstracted version of print_footer() / footer() to work with Moodle 1.8 through 2.0
 *
 * @param object $course The moodle course module object for this instance
 * @param object $usercourse The usercourse object
 * @param bool $return If true, return the visible elements of the header instead of echoing them.
 * @return mixed If return=true then string else void
 */
function turnitintool_footer($course = NULL, $usercourse = NULL, $return = false) {
    global $PAGE,$OUTPUT;
    if (is_callable(array($OUTPUT,'footer'))) {
        if ($return) {
            return $OUTPUT->footer();
        } else {
            echo $OUTPUT->footer();
        }
    } else {
        return print_footer($course,$usercourse,$return);
    }
}
/**
* Warning display to indicate duplicated assignments, normally as a result of a backup and restore
*
* @param object $cm The moodle course module object for this instance
* @param object $turnitintool The turnitin assignment data object
* @return mixed Returns HTML duplication warning if the logged in users has grade rights otherwise null
*/
function turnitintool_duplicatewarning($cm, $turnitintool) {
    global $CFG;
    if ( has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id)) ) {
        $parts = turnitintool_get_records('turnitintool_parts','turnitintoolid',$turnitintool->id);
        $dups = array();
        $output = '';
        foreach ($parts as $part) {
            $dup_parts = turnitintool_get_records_select('turnitintool_parts',
                                                         'tiiassignid='.$part->tiiassignid.
                                                         ' AND turnitintoolid!='.$part->turnitintoolid.' AND deleted=0');
            $dup_parts = (is_array($dup_parts)) ? $dup_parts : array();
            foreach ($dup_parts as $dup_part) {
                $dups[] = $dup_part;
            } if (is_array($dup_parts));
        }
        if ( count($dups) > 0 ) {
            $output .= turnitintool_box_start('generalbox boxwidthwide boxaligncenter notepost', 'warning', true);
            $output .= '<h3 class="error">' . get_string('notice') . '</h3>';
            $output .= '<p>' . get_string('duplicatesfound','turnitintool') . '</p>';
            $output .= '<ul>'.PHP_EOL;
        }
        foreach ( $dups as $dup_part ) {
            $dup_tii = turnitintool_get_record('turnitintool','id',$dup_part->turnitintoolid);
            $dup_cm = get_coursemodule_from_instance('turnitintool',$dup_tii->id);
            $dup_course = turnitintool_get_record('course','id',$dup_tii->course);
            $output .= '<li><a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$dup_cm->id.'">';
            $output .= $dup_course->fullname.' (' . $dup_course->shortname . ') - '.$dup_tii->name.' - ' . $dup_part->partname . '<br />';
            $output .= '</a></li>'.PHP_EOL;
        }
        if ( count($dups) > 0 ) {
            $output .= '</ul>'.PHP_EOL;
            $output .= turnitintool_box_end(true);
        }
        return $output;
    } else {
        return '';
    }
}
/**
 * Abstracted version of print_error() to work with Moodle 1.8 through 2.0
 *
 * @param string $input The error string if module=NULL otherwise the language string called by get_string()
 * @param string $module The module string
 * @param string $param The parameter to send to use as the $a optional object in get_string()
 * @param string $file The file where the error occured
 * @param string $line The line number where the error occured
 */
function turnitintool_print_error($input,$module=NULL,$link=NULL,$param=NULL,$file=__FILE__,$line=__LINE__) {
    global $CFG;
    turnitintool_activitylog($input,"PRINT_ERROR");

    if (is_null($module)) {
        $message=$input;
    } else {
        $message=get_string($input,$module,$param);
    }

    if (!empty($param_ajax) && $param_ajax ) {
        header('HTTP/1.0 400 Bad Request');
        echo $message;
        $param_ajax=0;
        exit();
    }

    $linkid=optional_param('id',null,PARAM_CLEAN);

    if (is_null($link) AND substr_count($_SERVER["PHP_SELF"],"turnitintool/view.php")>0) {
        $link=(!is_null($linkid)) ? $CFG->wwwroot.'/mod/turnitintool/view.php?id='.$linkid : $CFG->wwwroot;
    }

    if (isset($CFG->turnitin_enablediagnostic) AND $CFG->turnitin_enablediagnostic=="1") {
        $message.=' ('.basename($file).' | '.$line.')';
    }
    if (!headers_sent()) {  // If there nothing in the output buffer then the loaderbar wasn't used print the error
        print_error('string','turnitintool',$link,'<span style="color: #888888;">'.$message.'</span>');
        exit();
    } else {
        // If there is something in the output buffer then the loaderbar was used and we need to redirect out and show an error afterwards
        // Reason for this is we have printed the HTML and BODY tags to the screen print_error will do the same and disrupt the loader bar javascript
        $errorarray['input']=$input;
        $errorarray['module']=$module;
        $errorarray['link']=$link;
        $errorarray['param']=$param;
        $errorarray['file']=$file;
        $errorarray['line']=$line;
        $_SESSION['turnitintool_errorarray']=$errorarray;
        turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php');
        exit();
    }


}
/**
 * Logging function to log activity / errors
 *
 * @param string $string The string describing the activity
 * @param string $activity The activity prompting the log
 * e.g. PRINT_ERROR (default), API_ERROR, INCLUDE, REQUIRE_JS, REQUIRE_ONCE, REQUEST, REDIRECT
 */
function turnitintool_activitylog($string,$activity) {
    global $CFG;
    if (isset($CFG->turnitin_enablediagnostic) AND $CFG->turnitin_enablediagnostic) {
        // ###### DELETE SURPLUS LOGS #########
        $numkeeps=10;
        $prefix="activitylog_";
        $dirpath=$CFG->dataroot."/temp/turnitintool/logs";
        if (!file_exists($dirpath)) {
            mkdir($dirpath,0777,true);
        }
        $dir=opendir($dirpath);
        $files=array();
        while ($entry=readdir($dir)) {
            if (substr(basename($entry),0,1)!="." AND substr_count(basename($entry),$prefix)>0) {
                $files[]=basename($entry);
            }
        }
        sort($files);
        for ($i=0;$i<count($files)-$numkeeps;$i++) {
            unlink($dirpath."/".$files[$i]);
        }
        // ####################################
        $filepath=$dirpath."/".$prefix.date('Ymd',time()).".log";
        $file=fopen($filepath,'a');
        $output=date('Y-m-d H:i:s O')." (".$activity.")"." - ".$string."\r\n";
        fwrite($file,$output);
        fclose($file);
    }
}
/**
 * Checks for error session array and if it is present display the error stored and exit
 */
function turnitintool_process_api_error() {
    if (isset($_SESSION['turnitintool_errorarray'])) {
        $errorarray=$_SESSION['turnitintool_errorarray'];
        turnitintool_activitylog($errorarray['input'],"API_ERROR");
        unset($_SESSION['turnitintool_errorarray']);
        turnitintool_print_error($errorarray['input'],$errorarray['module'],$errorarray['link'],$errorarray['param'],$errorarray['file'],$errorarray['line']);
        exit();
    }
}
/**
 * Abstract 1.8 and 1.9+ get group for module function
 *
 * @param object $cm The course module
 * @param object $user The user module
 * @return array Array of groups the user belongs to
 */
function turnitintool_module_group($cm) {
    if (!is_callable('groups_get_activity_group')) {
        return $cm->currentgroup;
    } else {
        return groups_get_activity_group($cm);
    }
}

/**
 * Convert a regular email into the pseudo equivelant for student data privacy purpose
 *
 * @param string $email The user' module's lastname
 * @return string A psuedo email address
 */
function turnitintool_pseudoemail( $email ) {
    global $CFG;
    $salt = !isset( $CFG->turnitin_pseudosalt ) ? '' : $CFG->turnitin_pseudosalt;
    $domain = empty( $CFG->turnitin_pseudoemaildomain ) ? '@tiimoodle.com' : $CFG->turnitin_pseudoemaildomain;
    if ( substr( $domain, 0, 1 ) != '@' ) {
        $domain = '@' . $domain;
    }
    return sha1( $email.$salt ) . $domain;
}

/**
 * Convert a regular firstname into the pseudo equivelant for student data privacy purpose
 *
 * @return string A psuedo firstname address
 */
function turnitintool_pseudofirstname() {
    global $CFG;
    return $CFG->turnitin_pseudofirstname;
}

/**
 * Convert a regular lastname into the pseudo equivelant for student data privacy purpose
 *
 * @param string $email The users email address
 * @return string A psuedo lastname address
 */
function turnitintool_pseudolastname( $email ) {
    global $CFG;
    $user = turnitintool_get_record( 'user', 'email', $email );
    $user_info = turnitintool_get_record( 'user_info_data', 'userid', $user->id, 'fieldid', $CFG->turnitin_pseudolastname );
    if ( ( !isset( $user_info->data ) OR empty( $user_info->data ) ) AND $CFG->turnitin_pseudolastname != 0 AND $CFG->turnitin_lastnamegen == 1 ) {
        $uniqueid = strtoupper(strrev(uniqid()));
        $userinfo = new stdClass();
        $userinfo->userid = $user->id;
        $userinfo->fieldid = $CFG->turnitin_pseudolastname;
        $userinfo->data = $uniqueid;
        if ( isset( $user_info->data ) ) {
            $userinfo->id = $user_info->id;
            turnitintool_update_record( 'user_info_data', $userinfo );
        } else {
            turnitintool_insert_record( 'user_info_data', $userinfo );
        }
    } else if ( $CFG->turnitin_pseudolastname != 0 ) {
        $uniqueid = isset( $user_info->data ) ? $user_info->data : 'Unset';
    } else {
        $uniqueid = get_string( 'user' );
    }
    return $uniqueid;
}

/**
 * Checks to see if the user is a turnitin tutor based on email address
 *
 * @param string $email The users email address
 * @return boolean True if user is a tutor
 */
function turnitintool_istutor( $email ) {
    $user = turnitintool_get_record( 'user', 'email', $email );
    $tiiuser = turnitintool_get_record( 'turnitintool_users', 'userid', $user->id );
    return ( isset($tiiuser->turnitin_utp) AND $tiiuser->turnitin_utp == 2 ) ? true : false;
}

/**
    * Get the latest version info from the XML file https://www.turnitin.com/static/resources/files/moodledirect_latest.xml
    *
    * @return string url of latest if this version is not the latest null if update is not available
    */
function turnitintool_updateavailable( $module ) {
    $basedir = "https://www.turnitin.com/static/resources/files/";
    $loaderbar = null;
    // Use the comms class so we can make sure the call is using any proxy in place
    $tii = new turnitintool_commclass('','','','','',$loaderbar);
    $result = $tii->doRequest("GET", $basedir . "moodledirect_latest.xml", "");
    $tii->xmlToSimple( $result, false );
    $moduleversion = ( isset( $module->version ) ) ? $module->version : $module->versiondb;
    if ( strlen( $result ) > 0 AND isset( $tii->simplexml->version ) ) {
        $version = $tii->simplexml->version;
        if ( $version <= $moduleversion ) {
            // No update available
            return null;
        } else {
            // Update available return URL
            return $tii->simplexml->filename;
        }
    }
    // Could not find the xml file can't return URL so return null
    return null;
}

/**
 * Build a datatables localization array
 *
 * @return array Array of translatable Datatable strings
 */
function turnitintool_datatables_strings() {

    $return = array();
    $return["oPaginate"]["sPrevious"] = get_string( 'sprevious', 'turnitintool' );
    $return["oPaginate"]["sNext"] = get_string( 'snext', 'turnitintool' );
    $return["sEmptyTable"] = get_string( 'semptytable', 'turnitintool' );

    $a = '<select>
        <option value="10">10</option>
        <option value="20">20</option>
        <option value="30">30</option>
        <option value="40">40</option>
        <option value="50">50</option>
        <option value="-1">All</option>
    </select>';
    $return["sLengthMenu"] = get_string( 'slengthmenu', 'turnitintool', $a );
    $return["sSearch"] = get_string( 'ssearch', 'turnitintool' );
    $return["sProcessing"] = get_string( 'sprocessing', 'turnitintool' );
    $return["sZeroRecords"] = get_string( 'szerorecords', 'turnitintool' );

    $a = new stdClass();
    $a->start = '_START_';
    $a->end = '_END_';
    $a->total = '_TOTAL_';
    $return["sInfo"] = get_string( 'sinfo', 'turnitintool', $a );

    return json_encode($return);
}
/**
 * Moodle participation hook method for views
 *
 * @return array Array of available log labels
 */
function turnitintool_get_view_actions() {
    return array('view');
}
/**
 * Moodle participation hook method for views
 *
 * @return array Array of available log labels
 */
function turnitintool_get_post_actions() {
    return array('submit');
}

/* ?> */