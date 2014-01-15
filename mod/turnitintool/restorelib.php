<?php

function turnitintool_restore_mods($mod,$restore) {

    global $CFG, $DB;
    $status = true;
    $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);

    if ($data) {

        $info = $data->info;

        // Recreate the turnitintool_course entries
        $course = new stdClass();
        $course->id = backup_todb($info['MOD']['#']['COURSE']['0']['#']['ID']['0']['#']);
        $course->courseid = backup_todb($info['MOD']['#']['COURSE']['0']['#']['COURSEID']['0']['#']);
        $course->ownerid = backup_todb($info['MOD']['#']['COURSE']['0']['#']['OWNERID']['0']['#']);
        $course->turnitin_ctl = backup_todb($info['MOD']['#']['COURSE']['0']['#']['TURNITIN_CTL']['0']['#']);
        $course->turnitin_cid = backup_todb($info['MOD']['#']['COURSE']['0']['#']['TURNITIN_CID']['0']['#']);
        $course->ownertiiuid = backup_todb($info['MOD']['#']['COURSE']['0']['#']['OWNERTIIUID']['0']['#']);
        $course->ownerfn = backup_todb($info['MOD']['#']['COURSE']['0']['#']['OWNERFN']['0']['#']);
        $course->ownerln = backup_todb($info['MOD']['#']['COURSE']['0']['#']['OWNERLN']['0']['#']);
        $course->ownerun = backup_todb($info['MOD']['#']['COURSE']['0']['#']['OWNERUN']['0']['#']);
        $course->owneremail = backup_todb($info['MOD']['#']['COURSE']['0']['#']['OWNEREMAIL']['0']['#']);

        $tiicourseid = $course->turnitin_cid;
        // Determine if the destination course already has a TII Class associated, add check to determine the database call to enable backward compatibility
        // if it does is it the same class as the one we are restoring
        if (is_callable(array($DB,'get_records_select'))) {
            $tiicourses = $DB->get_records_select('turnitintool_courses', 'turnitin_cid='.$tiicourseid.' OR courseid='.$restore->course_id, null, "courseid", "id,courseid,turnitin_cid");
        } else {
            $tiicourses = get_records_select('turnitintool_courses','turnitin_cid='.$tiicourseid.' OR courseid='.$restore->course_id, "courseid", "id,courseid,turnitin_cid");
        }
        $tiicourses = (!$tiicourses) ? array() : $tiicourses;

        $backupaccountid=$info['MOD']['#']['TIIACCOUNT']['0']['#'];
        if ($CFG->turnitin_account_id!=$backupaccountid) {
            if (!defined('RESTORE_SILENTLY')) {
                // Course Error
                $input = new stdClass();
                $input->current=(!empty($CFG->turnitin_account_id)) ? $CFG->turnitin_account_id : '('.get_string("notavailableyet","turnitintool").')';
                $input->backupid=$backupaccountid;
                echo "<li class=\"error\">".get_string("modulename","turnitintool")." : ".get_string("wrongaccountid","turnitintool",$input)."\"</li>";
            }
            return true; // Don't exit restore on failure...
        }

        $restorable=true;
        $thiscourse=false;
        foreach ($tiicourses as $tiicourse) {
            if (($tiicourse->turnitin_cid==$tiicourseid AND $tiicourse->courseid!=$restore->course_id)
            OR
            ($tiicourse->turnitin_cid!=$tiicourseid AND $tiicourse->courseid==$restore->course_id)) {
                // If the Turnitin Class ID exists against a course already
                // and that course is not the course we are restoring to then do not restore
                $restorable=false;
            } else if ($tiicourse->courseid==$restore->course_id) {
                $thiscourse=true;
            }
        }

        // If the course class connection does not already exist OR if it does it is linked to this host course
        $insertcourse = new stdClass();
        $insertcourse->courseid=$restore->course_id;

        // If the owner had been deleted before back up then
        // the username email address thing will have happened
        $owneremail = (empty($course->owneremail)) ? join(array_splice(explode(".",$course->ownerun),0,-1)) : $course->owneremail;

        if (is_callable(array($DB,'get_records'))) {
            $owner = $DB->get_record('user', array('email'=>$owneremail));
        } else {
            $owner = get_record('user','email',$owneremail);
        }
        if ($owner) {
            $insertcourse->ownerid=$owner->id;
        } else { // Turnitin class owner not found from email address etc create user account
            $newuser=false;
            $i=0;
            while (!$newuser) {
                // Keep trying to create a new username
                $username = ($i==0) ? $course->ownerun : $course->ownerun.'_'.$i; // Append number if username exists
                $i++;
                $newuser = create_user_record($username,substr(rand(0,9).'_'.md5($course->ownerun),0,8));
                $newuser->email = $owneremail;
                $newuser->firstname = $course->ownerfn;
                $newuser->lastname = $course->ownerln;
                if (is_callable(array($DB,'update_record'))) {
                    $DB->update_record("turnitintool_courses",$newuser);
                } else {
                    update_record("turnitintool_courses",$newuser);
                }
            }
            $insertcourse->ownerid=$newuser->id;
        }
        $insertcourse->turnitin_ctl = $course->turnitin_ctl;
        $insertcourse->turnitin_cid = $course->turnitin_cid;

        if (is_callable(array($DB,'insert_record')) AND !$thiscourse) {
            $DB->insert_record("turnitintool_courses",$insertcourse);
        } else if (!$thiscourse) {
            insert_record("turnitintool_courses",$insertcourse);
        }


        if ($restore->course_startdateoffset) {
            restore_log_date_changes(get_string('modulename','turnitintool'), $restore, $info['MOD']['#'], array('TIMEDUE', 'TIMEAVAILABLE'));
        }

        $turnitintool->course = $restore->course_id;
        $turnitintool->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
        $turnitintool->grade = backup_todb($info['MOD']['#']['GRADE']['0']['#']);
        $turnitintool->numparts = backup_todb($info['MOD']['#']['NUMPARTS']['0']['#']);
        $turnitintool->defaultdtstart = backup_todb($info['MOD']['#']['DEFAULTDTSTART']['0']['#']);
        $turnitintool->defaultdtdue = backup_todb($info['MOD']['#']['DEFAULTDTDUE']['0']['#']);
        $turnitintool->defaultdtpost = backup_todb($info['MOD']['#']['DEFAULTDTPOST']['0']['#']);
        $turnitintool->anon = backup_todb($info['MOD']['#']['ANON']['0']['#']);
        $turnitintool->portfolio = backup_todb($info['MOD']['#']['PORTFOLIO']['0']['#']);
        $turnitintool->allowlate = backup_todb($info['MOD']['#']['ALLOWLATE']['0']['#']);
        $turnitintool->reportgenspeed = backup_todb($info['MOD']['#']['REPORTGENSPEED']['0']['#']);
        $turnitintool->submitpapersto = backup_todb($info['MOD']['#']['SUBMITPAPERSTO']['0']['#']);
        $turnitintool->spapercheck = backup_todb($info['MOD']['#']['SPAPERCHECK']['0']['#']);
        $turnitintool->internetcheck = backup_todb($info['MOD']['#']['INTERNETCHECK']['0']['#']);
        $turnitintool->journalcheck = backup_todb($info['MOD']['#']['JOURNALCHECK']['0']['#']);
        $turnitintool->maxfilesize = backup_todb($info['MOD']['#']['MAXFILESIZE']['0']['#']);
        $turnitintool->intro = backup_todb($info['MOD']['#']['INTRO']['0']['#']);
        $turnitintool->introformat = backup_todb($info['MOD']['#']['INTROFORMAT']['0']['#']);
        $turnitintool->timecreated = backup_todb($info['MOD']['#']['TIMECREATED']['0']['#']);
        $turnitintool->timemodified = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);
        $turnitintool->studentreports = backup_todb($info['MOD']['#']['STUDENTREPORTS']['0']['#']);
        $turnitintool->dateformat = backup_todb($info['MOD']['#']['DATEFORMAT']['0']['#']);
        $turnitintool->usegrademark = backup_todb($info['MOD']['#']['USEGRADEMARK']['0']['#']);
        $turnitintool->gradedisplay = backup_todb($info['MOD']['#']['GRADEDISPLAY']['0']['#']);
        $turnitintool->autoupdates = backup_todb($info['MOD']['#']['AUTOUPDATES']['0']['#']);
        $turnitintool->commentedittime = backup_todb($info['MOD']['#']['COMMENTEDITTIME']['0']['#']);
        $turnitintool->commentmaxsize = backup_todb($info['MOD']['#']['COMMENTMAXSIZE']['0']['#']);
        $turnitintool->autosubmission = backup_todb($info['MOD']['#']['AUTOSUBMISSION']['0']['#']);
        $turnitintool->shownonsubmission = backup_todb($info['MOD']['#']['SHOWNONSUBMISSION']['0']['#']);

        // Add exclude small matches data if present in backup file
        if (isset($info['MOD']['#']['EXCLUDEBIBLIO']['0']['#'])) {
            $turnitintool->excludebiblio = backup_todb($info['MOD']['#']['EXCLUDEBIBLIO']['0']['#']);
            $turnitintool->excludequoted = backup_todb($info['MOD']['#']['EXCLUDEQUOTED']['0']['#']);
            $turnitintool->excludevalue = backup_todb($info['MOD']['#']['EXCLUDEVALUE']['0']['#']);
            $turnitintool->excludetype = backup_todb($info['MOD']['#']['EXCLUDETYPE']['0']['#']);
        }

        // Add exclude small matches data if present in backup file
        if (isset($info['MOD']['#']['ERATER']['0']['#'])) {
            $turnitintool->erater = backup_todb($info['MOD']['#']['ERATER']['0']['#']);
            $turnitintool->erater_handbook = backup_todb($info['MOD']['#']['ERATERHANDBOOK']['0']['#']);
            $turnitintool->erater_dictionary = backup_todb($info['MOD']['#']['ERATERDICTIONARY']['0']['#']);
            $turnitintool->erater_spelling = backup_todb($info['MOD']['#']['ERATERSPELLING']['0']['#']);
            $turnitintool->erater_grammar = backup_todb($info['MOD']['#']['ERATERGRAMMAR']['0']['#']);
            $turnitintool->erater_usage = backup_todb($info['MOD']['#']['ERATERUSAGE']['0']['#']);
            $turnitintool->erater_mechanics = backup_todb($info['MOD']['#']['ERATERMECHANICS']['0']['#']);
            $turnitintool->erater_style = backup_todb($info['MOD']['#']['ERATERSTYLE']['0']['#']);
        }

        if (is_callable(array($DB,'insert_record'))) {
            $newid = $DB->insert_record("turnitintool",$turnitintool);
        } else {
            $newid = insert_record("turnitintool",$turnitintool);
        }

        // Recreate the turnitintool_parts entries
        $newpartids = array();
        foreach ($info['MOD']['#']['PARTS']['0']['#']['PART'] as $partarray) {

            $part = new stdClass();
            $part->turnitintoolid = $newid;
            $part->partname = backup_todb($partarray['#']['PARTNAME']['0']['#']);
            $part->tiiassignid = backup_todb($partarray['#']['TIIASSIGNID']['0']['#']);
            $part->dtstart = backup_todb($partarray['#']['DTSTART']['0']['#']);
            $part->dtdue = backup_todb($partarray['#']['DTDUE']['0']['#']);
            $part->dtpost = backup_todb($partarray['#']['DTPOST']['0']['#']);
            $part->maxmarks = backup_todb($partarray['#']['MAXMARKS']['0']['#']);
            $part->deleted = backup_todb($partarray['#']['DELETED']['0']['#']);

            $oldpartid = backup_todb($partarray['#']['ID']['0']['#']);
            unset($part->id);
            if (is_callable(array($DB,'insert_record'))) {
                $newpartid = $DB->insert_record("turnitintool_parts",$part);
            } else {
                $newpartid = insert_record("turnitintool_parts",$part);
            }
            $newpartids[$oldpartid] = $newpartid;
        }

        if (!defined('RESTORE_SILENTLY')) {
            echo "<li>".get_string("modulename","turnitintool")." \"".format_string(stripslashes($turnitintool->name),true)."\"</li>";
        }
        backup_flush(300);

        if ($newid) {
            backup_putid($restore->backup_unique_code,$mod->modtype,
            $mod->id, $newid);
            if (restore_userdata_selected($restore,'turnitintool',$mod->id)) {
                $status = turnitintool_submissions_restore_mods($mod->id, $newid, $newpartids, $info, $restore) && $status;
            }
        } else {
            $status = false;
        }
    } else {
        $status = false;
    }
    return $status;
}

function turnitintool_submissions_restore_mods($old_turnitintool_id, $new_turnitintool_id, $newpartids, $info, $restore) {

    global $CFG, $DB;

    $status = true;

    //Get the submissions array - it might not be present
    if (isset($info['MOD']['#']['SUBMISSIONS']['0']['#']['SUBMISSION'])) {
        $submissions = $info['MOD']['#']['SUBMISSIONS']['0']['#']['SUBMISSION'];
    } else {
        $submissions = array();
    }

    //Iterate over submissions
    for($i = 0; $i < sizeof($submissions); $i++) {
        $sub_info = $submissions[$i];

        //We'll need this later!!
        $oldid = backup_todb($sub_info['#']['ID']['0']['#']);
        $olduserid = backup_todb($sub_info['#']['USERID']['0']['#']);
        $oldpartid = backup_todb($sub_info['#']['SUBMISSION_PART']['0']['#']);

        //Now, build the ASSIGNMENT_SUBMISSIONS record structure.
        $submission = new stdClass();
        $submission->userid = backup_todb($sub_info['#']['USERID']['0']['#']);
        $submission->turnitintoolid = $new_turnitintool_id;
        $submission->submission_part = $newpartids[$oldpartid];
        $submission->submission_title = backup_todb($sub_info['#']['SUBMISSION_TITLE']['0']['#']);
        $submission->submission_type = backup_todb($sub_info['#']['SUBMISSION_TYPE']['0']['#']);
        $submission->submission_filename = backup_todb($sub_info['#']['SUBMISSION_FILENAME']['0']['#']);
        $submission->submission_objectid = backup_todb($sub_info['#']['SUBMISSION_OBJECTID']['0']['#']);
        $submission->submission_score = backup_todb($sub_info['#']['SUBMISSION_SCORE']['0']['#']);
        $submission->submission_grade = backup_todb($sub_info['#']['SUBMISSION_GRADE']['0']['#']);
        $submission->submission_gmimaged = backup_todb($sub_info['#']['SUBMISSION_GMIMAGED']['0']['#']);
        $submission->submission_status = backup_todb($sub_info['#']['SUBMISSION_STATUS']['0']['#']);
        $submission->submission_queued = backup_todb($sub_info['#']['SUBMISSION_QUEUED']['0']['#']);
        $submission->submission_attempts = backup_todb($sub_info['#']['SUBMISSION_ATTEMPTS']['0']['#']);
        $submission->submission_modified = backup_todb($sub_info['#']['SUBMISSION_MODIFIED']['0']['#']);
        $submission->submission_parent = backup_todb($sub_info['#']['SUBMISSION_PARENT']['0']['#']);
        $submission->submission_nmuserid = backup_todb($sub_info['#']['SUBMISSION_NMUSERID']['0']['#']);
        $submission->submission_nmfirstname = backup_todb($sub_info['#']['SUBMISSION_NMFIRSTNAME']['0']['#']);
        $submission->submission_nmlastname = backup_todb($sub_info['#']['SUBMISSION_NMLASTNAME']['0']['#']);
        $submission->submission_unanon = backup_todb($sub_info['#']['SUBMISSION_UNANON']['0']['#']);
        $submission->submission_unanonreason = backup_todb($sub_info['#']['SUBMISSION_UNANONREASON']['0']['#']);

        //We have to recode the userid field
        $user = backup_getid($restore->backup_unique_code,"user",$submission->userid);
        if ($user) {
            $submission->userid = $user->new_id;
        }

        $dbobject=false;
        if (is_callable(array($DB,'get_record'))) {
            $dbobject=true;
        }

        // Search to see if we already have the Turnitin User for the submitting moodle user
        $tiiuser = new stdClass();
        $tiiuser->userid = $submission->userid;
        $tiiuser->turnitin_uid = backup_todb($sub_info['#']['TIIUSERID']['0']['#']);
        if ($dbobject) {
            if (!$DB->get_record("turnitintool_users",array('turnitin_uid'=>$tiiuser->turnitin_uid))) {
                $DB->insert_record("turnitintool_users",$tiiuser);
            }
        } else {
            if (!get_record("turnitintool_users","turnitin_uid",$tiiuser->turnitin_uid)) {
                insert_record("turnitintool_users",$tiiuser);
            }
        }

        //Add comments to the 'turnitin_comments' table in the db, so insert into turnitintool_submissions, check for DB type
        if ($dbobject) {
            $newid = $DB->insert_record("turnitintool_submissions",$submission);
        } else {
            $newid = insert_record("turnitintool_submissions",$submission);
        }

        //$comments = unserialize(html_entity_decode(backup_todb($sub_info['#']['COMMENTS']['0']['#'])));
        if (isset($sub_info['#']['COMMENTS']['0']['#']['COMMENT'])) {
            foreach ($sub_info['#']['COMMENTS']['0']['#']['COMMENT'] as $commentarray) {
                $commentuser = backup_getid($restore->backup_unique_code,"user",backup_todb($commentarray['#']['USERID']['0']['#']));

                if (isset($commentuser->new_id)) {
                    unset($comment);
                    $comment = new stdClass();
                    $comment->submissionid = $newid;
                    $comment->userid = $commentuser->new_id;

                    // Field names changed keep old fieldnames in XML for backward compatibility
                    $comment->commenttext = backup_todb($commentarray['#']['COMMENT']['0']['#']);
                    $comment->dateupdated = backup_todb($commentarray['#']['DATE']['0']['#']);

                    $comment->deleted = backup_todb($commentarray['#']['DELETED']['0']['#']);

                    //Insert the updated comment data object into turnitintool_comments, check for DB object version
                    if (is_callable(array($DB,'insert_record'))) {
                        $newcommentid = $DB->insert_record("turnitintool_comments",$comment);
                    } else {
                        $newcommentid = insert_record("turnitintool_comments",$comment);
                    }
                }

            }
        }

        //Do some output
        if (($i+1) % 50 == 0) {
            if (!defined('RESTORE_SILENTLY')) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
            }
            backup_flush(300);
        }

        if ($newid) {
            //We have the newid, update backup_ids
            backup_putid($restore->backup_unique_code,"turnitintool_submissions",$oldid,
                         $newid);

            //Now copy moddata associated files
            $status = turnitintool_restore_files($old_turnitintool_id, $new_turnitintool_id,
                                                $olduserid, $submission->userid, $restore);

        } else {
            $status = false;
        }
    }

    return $status;
}

function turnitintool_restore_files($oldtiiid, $newtiiid, $olduserid, $newuserid, $restore) {

    global $CFG;

    $status = true;
    $todo = false;
    $moddata_path = "";
    $turnitintool_path = "";
    $temp_path = "";

    //First, we check to "course_id" exists and create is as necessary
    //in CFG->dataroot
    $dest_dir = $CFG->dataroot."/".$restore->course_id;
    $status = check_dir_exists($dest_dir,true);

    //Now, locate course's moddata directory
    $moddata_path = $CFG->dataroot."/".$restore->course_id."/".$CFG->moddata;

    //Check it exists and create it
    $status = check_dir_exists($moddata_path,true);

    //Now, locate assignment directory
    if ($status) {
        $turnitintool_path = $moddata_path."/turnitintool";
        //Check it exists and create it
        $status = check_dir_exists($turnitintool_path,true);
    }

    //Now locate the temp dir we are gong to restore
    if ($status) {
        $temp_path = $CFG->dataroot."/temp/backup/".$restore->backup_unique_code.
                     "/moddata/turnitintool/".$oldtiiid."/".$olduserid;
        //Check it exists
        if (is_dir($temp_path)) {
            $todo = true;
        }
    }

    //If todo, we create the neccesary dirs in course moddata/assignment
    if ($status and $todo) {
        //First this assignment id
        $this_turnitintool_path = $turnitintool_path."/".$newtiiid;
        $status = check_dir_exists($this_turnitintool_path,true);
        //Now this user id
        $user_turnitintool_path = $this_turnitintool_path."/".$newuserid;
        //And now, copy temp_path to user_assignment_path
        $status = backup_copy_file($temp_path, $user_turnitintool_path);
    }

    return $status;
}

function turnitintool_decode_content_links($content,$restore) {
    global $CFG;

    $result = $content;

    $searchstring='/\$@(TURNITINTOOLINDEX)\*([0-9]+)@\$/';
    //We look for it
    preg_match_all($searchstring,$content,$foundset);
    //If found, then we are going to look for its new id (in backup tables)
    if ($foundset[0]) {
        //print_object($foundset);                                     //Debug
        //Iterate over foundset[2]. They are the old_ids
        foreach($foundset[2] as $old_id) {
            //We get the needed variables here (course id)
            $rec = backup_getid($restore->backup_unique_code,"course",$old_id);
            //Personalize the searchstring
            $searchstring='/\$@(TURNITINTOOLINDEX)\*('.$old_id.')@\$/';
            //If it is a link to this course, update the link to its new location
            if($rec->new_id) {
                //Now replace it
                $result=preg_replace($searchstring,$CFG->wwwroot.'/mod/turnitintool/index.php?id='.$rec->new_id,$result);
            } else {
                //It's a foreign link so leave it as original
                $result=preg_replace($searchstring,$restore->original_wwwroot.'/mod/turnitintool/index.php?id='.$old_id,$result);
            }
        }
    }

    //Link to assignment view by moduleid

    $searchstring='/\$@(TURNITINTOOLVIEWBYID)\*([0-9]+)@\$/';
    //We look for it
    preg_match_all($searchstring,$result,$foundset);
    //If found, then we are going to look for its new id (in backup tables)
    if ($foundset[0]) {
        //print_object($foundset);                                     //Debug
        //Iterate over foundset[2]. They are the old_ids
        foreach($foundset[2] as $old_id) {
            //We get the needed variables here (course_modules id)
            $rec = backup_getid($restore->backup_unique_code,"course_modules",$old_id);
            //Personalize the searchstring
            $searchstring='/\$@(TURNITINTOOLVIEWBYID)\*('.$old_id.')@\$/';
            //If it is a link to this course, update the link to its new location
            if($rec->new_id) {
                //Now replace it
                $result=preg_replace($searchstring,$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$rec->new_id,$result);
            } else {
                //It's a foreign link so leave it as original
                $result=preg_replace($searchstring,$restore->original_wwwroot.'/mod/turnitintool/view.php?id='.$old_id,$result);
            }
        }
    }

    return $result;
}

function turnitintool_decode_content_links_caller($restore) {
    global $CFG, $DB;
    $status = true;

    //Convert turnitintool->description, check for DB type
    if (is_callable(array($DB,'get_records_sql'))) {
        $turnitintools = $DB->get_records_sql ("SELECT t.id, t.intro
                               FROM {$CFG->prefix}turnitintool t
                               WHERE t.course = $restore->course_id");
    } else {
        $turnitintools = get_records_sql ("SELECT t.id, t.intro
                               FROM {$CFG->prefix}turnitintool t
                               WHERE t.course = $restore->course_id");
    }

    if ($turnitintools) {
        //Iterate over each turnitintool->intro
        $i = 0;   //Counter to send some output to the browser to avoid timeouts
        foreach ($turnitintools as $turnitintool) {
            //Increment counter
            $i++;
            $content = $turnitintool->intro;
            $result = restore_decode_content_links_worker($content,$restore);
            if ($result != $content) {
                //Update record
                $turnitintool->intro = addslashes($result);
                $status = update_record("turnitintool",$turnitintool);
                if (debugging()) {
                    if (!defined('RESTORE_SILENTLY')) {
                        echo '<br /><hr />'.s($content).'<br />changed to<br />'.s($result).'<hr /><br />';
                    }
                }
            }
            //Do some output
            if (($i+1) % 5 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 100 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }
        }
    }
    return $status;
}

function turnitintool_restore_wiki2markdown($restore) {
    global $CFG;

    $status = true;

    //Convert assignment->description
    if ($records = get_records_sql ("SELECT a.id, a.intro, a.introformat
                                     FROM {$CFG->prefix}turnitintool a,
                                          {$CFG->prefix}backup_ids b
                                     WHERE a.course = $restore->course_id AND
                                           a.introformat = ".FORMAT_WIKI. " AND
                                           b.backup_code = $restore->backup_unique_code AND
                                           b.table_name = 'turnitintool' AND
                                           b.new_id = a.id")) {
        foreach ($records as $record) {
            //Rebuild wiki links
            $record->description = restore_decode_wiki_content($record->intro, $restore);
            //Convert to Markdown
            $wtm = new WikiToMarkdown();
            $record->intro = $wtm->convert($record->intro, $restore->course_id);
            $record->introformat = FORMAT_MARKDOWN;
            $status = update_record('turnitintool', addslashes_object($record));
            //Do some output
            $i++;
            if (($i+1) % 1 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 20 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }
        }

    }
    return $status;
}

function turnitintool_restore_logs($restore,$log) {

    $status = false;

    //Depending of the action, we recode different things
    switch ($log->action) {
    case "add":
        if ($log->cmid) {
            //Get the new_id of the module (to recode the info field)
            $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
            if ($mod) {
                $log->url = "view.php?id=".$log->cmid;
                $log->info = $mod->new_id;
                $status = true;
            }
        }
        break;
    case "update":
        if ($log->cmid) {
            //Get the new_id of the module (to recode the info field)
            $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
            if ($mod) {
                $log->url = "view.php?id=".$log->cmid;
                $log->info = $mod->new_id;
                $status = true;
            }
        }
        break;
    case "view":
        if ($log->cmid) {
            //Get the new_id of the module (to recode the info field)
            $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
            if ($mod) {
                $log->url = "view.php?id=".$log->cmid;
                $log->info = $mod->new_id;
                $status = true;
            }
        }
        break;
    case "view all":
        $log->url = "index.php?id=".$log->course;
        $status = true;
        break;
    case "upload":
        if ($log->cmid) {
            //Get the new_id of the module (to recode the info field)
            $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
            if ($mod) {
                $log->url = "view.php?a=".$mod->new_id;
                $log->info = $mod->new_id;
                $status = true;
            }
        }
        break;
    default:
        if (!defined('RESTORE_SILENTLY')) {
            echo "action (".$log->module."-".$log->action.") unknown. Not restored<br />";                 //Debug
        }
        break;
    }

    if ($status) {
        $status = $log;
    }
    return $status;
}

// ?>