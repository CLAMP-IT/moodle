<?php

function turnitintool_backup_mods($bf,$preferences) {
    global $CFG, $DB;
    $status = true;
    // Add check to determine the database call to enable backward compatibility
    if (is_callable(array($DB,'get_records'))) {
        $turnitintools = $DB->get_records("turnitintool", array("course"=>$preferences->backup_course),"id");
    } else {
        $turnitintools = get_records("turnitintool","course",$preferences->backup_course,"id");
    }
    if ($turnitintools) {
        foreach ($turnitintools as $turnitintool) {
            if (backup_mod_selected($preferences,'turnitintool',$turnitintool)) {
                $status = turnitintool_backup_one_mod($bf,$preferences,$turnitintool);
            }
        }
    }
    return $status;
}

function turnitintool_backup_one_mod($bf,$preferences,$turnitintool) {
    global $CFG, $DB;

    // Add check to determine the database call to enable backward compatibility
    if (is_numeric($turnitintool) AND is_callable(array($DB,'get_records'))) {
        $turnitintool = $DB->get_record('turnitintool', array('id'=>$turnitintool));
    } else if (is_numeric($turnitintool)) {
        $turnitintool = get_record('turnitintool','id',$turnitintool);
    }

    $status = true;

    fwrite ($bf,start_tag("MOD",3,true));
    fwrite ($bf,full_tag("ID",4,false,$turnitintool->id));
    fwrite ($bf,full_tag("MODTYPE",4,false,"turnitintool"));
    fwrite ($bf,full_tag("NAME",4,false,$turnitintool->name));
    fwrite ($bf,full_tag("GRADE",4,false,$turnitintool->grade));
    fwrite ($bf,full_tag("NUMPARTS",4,false,$turnitintool->numparts));
    fwrite ($bf,full_tag("TIIACCOUNT",4,false,$CFG->turnitin_account_id));
    fwrite ($bf,full_tag("DEFAULTDTSTART",4,false,$turnitintool->defaultdtstart));
    fwrite ($bf,full_tag("DEFAULTDTDUE",4,false,$turnitintool->defaultdtdue));
    fwrite ($bf,full_tag("DEFAULTDTPOST",4,false,$turnitintool->defaultdtpost));
    fwrite ($bf,full_tag("ANON",4,false,$turnitintool->anon));
    fwrite ($bf,full_tag("PORTFOLIO",4,false,$turnitintool->portfolio));
    fwrite ($bf,full_tag("ALLOWLATE",4,false,$turnitintool->allowlate));
    fwrite ($bf,full_tag("REPORTGENSPEED",4,false,$turnitintool->reportgenspeed));
    fwrite ($bf,full_tag("SUBMITPAPERSTO",4,false,$turnitintool->submitpapersto));
    fwrite ($bf,full_tag("SPAPERCHECK",4,false,$turnitintool->spapercheck));
    fwrite ($bf,full_tag("INTERNETCHECK",4,false,$turnitintool->internetcheck));
    fwrite ($bf,full_tag("JOURNALCHECK",4,false,$turnitintool->journalcheck));
    fwrite ($bf,full_tag("MAXFILESIZE",4,false,$turnitintool->maxfilesize));
    fwrite ($bf,full_tag("INTRO",4,false,$turnitintool->intro));
    fwrite ($bf,full_tag("INTROFORMAT",4,false,$turnitintool->introformat));
    fwrite ($bf,full_tag("TIMECREATED",4,false,$turnitintool->timecreated));
    fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$turnitintool->timemodified));
    fwrite ($bf,full_tag("STUDENTREPORTS",4,false,$turnitintool->studentreports));
    fwrite ($bf,full_tag("DATEFORMAT",4,false,$turnitintool->dateformat));
    fwrite ($bf,full_tag("USEGRADEMARK",4,false,$turnitintool->usegrademark));
    fwrite ($bf,full_tag("GRADEDISPLAY",4,false,$turnitintool->gradedisplay));
    fwrite ($bf,full_tag("AUTOUPDATES",4,false,$turnitintool->autoupdates));
    fwrite ($bf,full_tag("COMMENTEDITTIME",4,false,$turnitintool->commentedittime));
    fwrite ($bf,full_tag("COMMENTMAXSIZE",4,false,$turnitintool->commentmaxsize));
    fwrite ($bf,full_tag("AUTOSUBMISSION",4,false,$turnitintool->autosubmission));
    fwrite ($bf,full_tag("SHOWNONSUBMISSION",4,false,$turnitintool->shownonsubmission));

    fwrite ($bf,full_tag("EXCLUDEBIBLIO",4,false,$turnitintool->excludebiblio));
    fwrite ($bf,full_tag("EXCLUDEQUOTED",4,false,$turnitintool->excludequoted));
    fwrite ($bf,full_tag("EXCLUDEVALUE",4,false,$turnitintool->excludevalue));
    fwrite ($bf,full_tag("EXCLUDETYPE",4,false,$turnitintool->excludetype));

    fwrite ($bf,full_tag("ERATER",4,false,$turnitintool->erater));
    fwrite ($bf,full_tag("ERATERHANDBOOK",4,false,$turnitintool->erater_handbook));
    fwrite ($bf,full_tag("ERATERDICTIONARY",4,false,$turnitintool->erater_dictionary));
    fwrite ($bf,full_tag("ERATERSPELLING",4,false,$turnitintool->erater_spelling));
    fwrite ($bf,full_tag("ERATERGRAMMAR",4,false,$turnitintool->erater_grammar));
    fwrite ($bf,full_tag("ERATERUSAGE",4,false,$turnitintool->erater_usage));
    fwrite ($bf,full_tag("ERATERMECHANICS",4,false,$turnitintool->erater_mechanics));
    fwrite ($bf,full_tag("ERATERSTYLE",4,false,$turnitintool->erater_style));
    fwrite ($bf,full_tag("TRANSMATCH",4,false,$turnitintool->transmatch));

    // Get the parts for this assignment, add check to determine the database call to enable backward compatibility
    if (is_callable(array($DB,'get_records'))) {
        $parts = $DB->get_records('turnitintool_parts', array('turnitintoolid'=>$turnitintool->id));
    } else {
        $parts = get_records('turnitintool_parts','turnitintoolid',$turnitintool->id);
    }
    $parts = (!$parts) ? array() : $parts;
    fwrite ($bf,start_tag("PARTS",4,true));
    foreach ($parts as $part) {
        fwrite ($bf,start_tag("PART",5,true));
        fwrite ($bf,full_tag("ID",6,false,$part->id));
        fwrite ($bf,full_tag("TURNITINTOOLID",6,false,$part->turnitintoolid));
        fwrite ($bf,full_tag("PARTNAME",6,false,$part->partname));
        fwrite ($bf,full_tag("TIIASSIGNID",6,false,$part->tiiassignid));
        fwrite ($bf,full_tag("DTSTART",6,false,$part->dtstart));
        fwrite ($bf,full_tag("DTDUE",6,false,$part->dtdue));
        fwrite ($bf,full_tag("DTPOST",6,false,$part->dtpost));
        fwrite ($bf,full_tag("MAXMARKS",6,false,$part->maxmarks));
        fwrite ($bf,full_tag("DELETED",6,false,$part->deleted));
        fwrite ($bf,end_tag("PART",5,true));
    }
    fwrite ($bf,end_tag("PARTS",4,true));

    // Get the course data for this assignment, add check to determine the database call to enable backward compatibility
    if (is_callable(array($DB,'get_records'))) {
        $course = $DB->get_record('turnitintool_courses', array('courseid'=>$turnitintool->course));
    } else {
        $course = get_record('turnitintool_courses','courseid',$turnitintool->course);
    }
    // Get the turnitin course owner data for this assignment, add check to determine the database call to enable backward compatibility
    if (is_callable(array($DB,'get_records'))) {
        $owner = $DB->get_record('user', array('id'=>$course->ownerid));
        $tiiuser = $DB->get_record('turnitintool_users', array('userid'=>$course->ownerid));
    } else {
        $owner = get_record('user','id',$course->ownerid);
        $tiiuser = get_record('turnitintool_users', 'userid', $course->ownerid);
    }

    fwrite ($bf,start_tag("COURSE",4,true));
    fwrite ($bf,full_tag("ID",5,false,$course->id));
    fwrite ($bf,full_tag("COURSEID",5,false,$course->courseid));
    fwrite ($bf,full_tag("OWNERID",5,false,$course->ownerid));
    fwrite ($bf,full_tag("OWNERTIIUID",5,false,$tiiuser->turnitin_uid));
    fwrite ($bf,full_tag("OWNEREMAIL",5,false,$owner->email));
    fwrite ($bf,full_tag("OWNERUN",5,false,$owner->username));
    fwrite ($bf,full_tag("OWNERFN",5,false,$owner->firstname));
    fwrite ($bf,full_tag("OWNERLN",5,false,$owner->lastname));
    fwrite ($bf,full_tag("TURNITIN_CTL",5,false,$course->turnitin_ctl));
    fwrite ($bf,full_tag("TURNITIN_CID",5,false,$course->turnitin_cid));
    fwrite ($bf,end_tag("COURSE",4,true));

    if (backup_userdata_selected($preferences,'turnitintool',$turnitintool->id)) {
        $status = backup_turnitintool_submissions($bf,$preferences,$turnitintool->id);
        if ($status) {
            $status = backup_turnitintool_files_instance($bf,$preferences,$turnitintool->id);
        }
    }

    $status = fwrite ($bf,end_tag("MOD",3,true));

    return $status;
}

function backup_turnitintool_submissions($bf,$preferences,$turnitintool) {
    global $CFG, $DB;
    $status = true;

    // Get the course data for this assignment, add check to determine the database call to enable backward compatibility
    if (is_callable(array($DB,'get_records'))) {
        $turnitintool_submissions = $DB->get_record('turnitintool_submissions', array('turnitintoolid'=>$tii_sub->id));
    } else {
        $turnitintool_submissions = get_records("turnitintool_submissions","turnitintoolid",$turnitintool,"id");
    }

    if ($turnitintool_submissions) {
        $status =fwrite ($bf,start_tag("SUBMISSIONS",4,true));
        foreach ($turnitintool_submissions as $tii_sub) {
            $status =fwrite ($bf,start_tag("SUBMISSION",5,true));
            fwrite ($bf,full_tag("ID",6,false,$tii_sub->id));
            fwrite ($bf,full_tag("USERID",6,false,$tii_sub->userid));
            fwrite ($bf,full_tag("SUBMISSION_PART",6,false,$tii_sub->submission_part));
            fwrite ($bf,full_tag("SUBMISSION_TITLE",6,false,$tii_sub->submission_title));
            fwrite ($bf,full_tag("SUBMISSION_TYPE",6,false,$tii_sub->submission_type));
            fwrite ($bf,full_tag("SUBMISSION_FILENAME",6,false,$tii_sub->submission_filename));
            fwrite ($bf,full_tag("SUBMISSION_OBJECTID",6,false,$tii_sub->submission_objectid));
            fwrite ($bf,full_tag("SUBMISSION_SCORE",6,false,$tii_sub->submission_score));
            fwrite ($bf,full_tag("SUBMISSION_GRADE",6,false,$tii_sub->submission_grade));
            fwrite ($bf,full_tag("SUBMISSION_GMIMAGED",6,false,$tii_sub->submission_gmimaged));
            fwrite ($bf,full_tag("SUBMISSION_STATUS",6,false,$tii_sub->submission_status));
            fwrite ($bf,full_tag("SUBMISSION_QUEUED",6,false,$tii_sub->submission_queued));
            fwrite ($bf,full_tag("SUBMISSION_ATTEMPTS",6,false,$tii_sub->submission_attempts));
            fwrite ($bf,full_tag("SUBMISSION_MODIFIED",6,false,$tii_sub->submission_modified));
            fwrite ($bf,full_tag("SUBMISSION_PARENT",6,false,$tii_sub->submission_parent));
            fwrite ($bf,full_tag("SUBMISSION_NMUSERID",6,false,$tii_sub->submission_nmuserid));
            fwrite ($bf,full_tag("SUBMISSION_NMFIRSTNAME",6,false,$tii_sub->submission_nmfirstname));
            fwrite ($bf,full_tag("SUBMISSION_NMLASTNAME",6,false,$tii_sub->submission_nmlastname));
            fwrite ($bf,full_tag("SUBMISSION_UNANON",6,false,$tii_sub->submission_unanon));
            fwrite ($bf,full_tag("SUBMISSION_UNANONREASON",6,false,$tii_sub->submission_unanonreason));
            fwrite ($bf,full_tag("SUBMISSION_TRANSMATCH",6,false,$tii_sub->submission_transmatch));

            // Get the turnitin user data for this submission, add check to determine the database call to enable backward compatibility
            if (is_callable(array($DB,'get_record'))) {
                $user = $DB->get_record('turnitintool_users', array('userid'=>$tii_sub->userid));
            } else {
                $user = get_record('turnitintool_users','userid',$tii_sub->userid);
            }
            fwrite($bf,full_tag("TIIUSERID",6,false,$user->turnitin_uid));

            // Get the course data for this assignment, add check to determine the database call to enable backward compatibility
            if (is_callable(array($DB,'get_record'))) {
                $comments = $DB->get_records('turnitintool_comments', array('submissionid'=>$tii_sub->id));
            } else {
                $comments = get_records('turnitintool_comments','submissionid',$tii_sub->id);
            }
            $comments = (!$comments) ? array() : $comments;

            fwrite ($bf,start_tag("COMMENTS",6,true));
            foreach ($comments as $comment) {
                fwrite ($bf,start_tag("COMMENT",7,true));
                fwrite ($bf,full_tag("ID",8,false,$comment->id));
                fwrite ($bf,full_tag("SUBMISSIONID",8,false,$comment->submissionid));
                fwrite ($bf,full_tag("USERID",8,false,$comment->userid));
                fwrite ($bf,full_tag("COMMENT",8,false,$comment->commenttext));
                fwrite ($bf,full_tag("DATE",8,false,$comment->dateupdated));
                fwrite ($bf,full_tag("DELETED",8,false,$comment->deleted));
                fwrite ($bf,end_tag("COMMENT",7,true));
            }
            fwrite ($bf,end_tag("COMMENTS",6,true));

            $status = fwrite($bf,end_tag("SUBMISSION",5,true));
        }
        $status = fwrite($bf,end_tag("SUBMISSIONS",4,true));
    }
    return $status;
}

function backup_turnitintool_files($bf,$preferences) {
    global $CFG;
    $status = true;
    $status = check_and_create_moddata_dir($preferences->backup_unique_code);
    if ($status) {
        if (is_dir($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/turnitintool")) {
            $status = backup_copy_file($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/turnitintool",
                                       $CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/turnitintool");
        }
    }
    return $status;
}

function backup_turnitintool_files_instance($bf,$preferences,$instanceid) {
    global $CFG;
    $status = true;
    $status = check_and_create_moddata_dir($preferences->backup_unique_code);
    $status = check_dir_exists($CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/turnitintool/",true);
    if ($status) {
        if (is_dir($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/turnitintool/".$instanceid)) {
            $status = backup_copy_file($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/turnitintool/".$instanceid,
                                       $CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/turnitintool/".$instanceid);
        }
    }
    return $status;
}

function turnitintool_encode_content_links($content,$preferences) {
    global $CFG;
    $base = preg_quote($CFG->wwwroot,"/");

    //Link to the list of turnitintool
    $buscar="/(".$base."\/mod\/turnitintool\/index.php\?id\=)([0-9]+)/";
    $result= preg_replace($buscar,'$@TURNITINTOOLINDEX*$2@$',$content);

    //Link to turnitintool view by moduleid
    $buscar="/(".$base."\/mod\/turnitintool\/view.php\?id\=)([0-9]+)/";
    $result= preg_replace($buscar,'$@TURNITINTOOLVIEWBYID*$2@$',$result);

    return $result;
}

function turnitintool_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {
    if (!empty($instances) && is_array($instances) && count($instances)) {
        $info = array();
        foreach ($instances as $id => $instance) {
            $info += turnitintool_check_backup_mods_instances($instance,$backup_unique_code);
        }
        return $info;
    }

    $info[0][0] = get_string("modulenameplural","turnitintool");
    if ($ids = turnitintool_ids($course)) {
        $info[0][1] = count($ids);
    } else {
        $info[0][1] = 0;
    }

    if ($user_data) {
        $info[1][0] = get_string("submissions","turnitintool");
        if ($ids = turnitintool_submission_ids_by_course($course)) {
            $info[1][1] = count($ids);
        } else {
            $info[1][1] = 0;
        }
    }
    return $info;
}

function turnitintool_check_backup_mods_instances($instance,$backup_unique_code) {
    $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
    $info[$instance->id.'0'][1] = '';
    if (!empty($instance->userdata)) {
        $info[$instance->id.'1'][0] = get_string("submissions","turnitintool");
        if ($ids = turnitintool_submission_ids_by_instance($instance->id)) {
            $info[$instance->id.'1'][1] = count($ids);
        } else {
            $info[$instance->id.'1'][1] = 0;
        }
    }
    return $info;
}

function turnitintool_ids($course) {
    global $CFG, $DB;
    // Add check to determine the database call to enable backward compatibility
    if (is_callable(array($DB,'get_records'))) {
        return $DB->get_records_sql("SELECT t.id, t.course
                             FROM {$CFG->prefix}turnitintool t
                             WHERE t.course = '$course'");
    } else {
        return get_records_sql("SELECT t.id, t.course
                             FROM {$CFG->prefix}turnitintool t
                             WHERE t.course = '$course'");
    }
}

function turnitintool_submission_ids_by_course($course) {
    global $CFG, $DB;
    // Add check to determine the database call to enable backward compatibility
    if (is_callable(array($DB,'get_records'))) {
        return $DB->get_records_sql("SELECT s.id , s.turnitintoolid
                             FROM {$CFG->prefix}turnitintool_submissions s,
                                  {$CFG->prefix}turnitintool t
                             WHERE t.course = '$course' AND
                                   s.turnitintoolid = t.id");
    } else {
        return get_records_sql("SELECT s.id , s.turnitintoolid
                             FROM {$CFG->prefix}turnitintool_submissions s,
                                  {$CFG->prefix}turnitintool t
                             WHERE t.course = '$course' AND
                                   s.turnitintoolid = t.id");
    }
}

function turnitintool_submission_ids_by_instance($instanceid) {
    global $CFG, $DB;
    // Add check to determine the database call to enable backward compatibility
    if (is_callable(array($DB,'get_records'))) {
        return $DB->get_records_sql("SELECT s.id, s.turnitintoolid
                                FROM {$CFG->prefix}turnitintool_submissions s
                                WHERE s.turnitintoolid = $instanceid");
    } else {
        return get_records_sql("SELECT s.id , s.turnitintoolid
                             FROM {$CFG->prefix}turnitintool_submissions s
                             WHERE s.turnitintoolid = $instanceid");
    }
}

/* ?> */