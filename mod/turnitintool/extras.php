<?php  // $Id: extras.php,v 1.2 2010/06/25 11:49:46 paul.dawson Exp $
/**
 * @package   turnitintool
 * @copyright 2010 iParadigms LLC
 */

    require_once('../../config.php');
    require_once('../../course/lib.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once("lib.php");

    if (!is_callable('groups_get_activity_group')) {
        $adminroot = admin_get_root();
        admin_externalpage_setup('managemodules',$adminroot);
    } else {
        admin_externalpage_setup('managemodules');
    }

    if (isset($PAGE) AND @is_callable(array($PAGE->requires, 'js'))) { // Are we using new moodle or old?
        $jsurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/turnitintool.js');
        $PAGE->requires->js($jsurl,true);
        $cssurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/styles.css');
        $PAGE->requires->css($cssurl);
    } else {
        require_js($CFG->wwwroot.'/mod/turnitintool/turnitintool.js');
    }

    $a  = optional_param('a', 0, PARAM_INT);  // turnitintool ID
    $s  = optional_param('s', 0, PARAM_INT);  // submission ID
    $type  = optional_param('type', 0, PARAM_INT);  // submission ID

    if (!turnitintool_check_config()) {
        print_error('configureerror','turnitintool');
        exit();
    }

/// Print the main part of the page
    $param_do=optional_param('do',null,PARAM_CLEAN);
    $param_unlink=optional_param('unlink',null,PARAM_CLEAN);
    $param_relink=optional_param('relink',null,PARAM_CLEAN);
    $param_filedate=optional_param('filedate',null,PARAM_CLEAN);

    $post["userlinks"] = isset( $_REQUEST['userlinks'] ) ? $_REQUEST["userlinks"] : array();
    foreach ( $post["userlinks"] as $key => $value ) {
        $param_userlinks[$key] = clean_param( $value, PARAM_INT );
    }

    if (!is_null($param_do) AND ( $param_do=="viewreport" OR $param_do=="savereport" ) ) {
        if ($param_do=='viewreport') {
            echo '<pre>';
            echo "====== Turnitintool Data Dump Output ======

";
        } else if ($param_do=='savereport') {

            $filename='tii_datadump_'.$CFG->turnitin_account_id.'_'.date('dmYhm',time()).'.txt';
            header('Content-type: text/plain');
            header('Content-Disposition: attachment; filename="'.$filename.'"');


            echo "====== Turnitintool Data Dump File ======

";
        }

        $tables = array('turnitintool_users','turnitintool_courses','turnitintool','turnitintool_parts','turnitintool_submissions');

        foreach ($tables as $table) {

            echo "== ".$table." ==
";

            if ($data=turnitintool_get_records($table)) {

                $headers=array_keys(get_object_vars(current($data)));
                $columnwidth=25;

                echo str_pad('',(($columnwidth+2)*count($headers)),"=");
                if ($table=='turnitintool_users') {
                    echo str_pad('',$columnwidth+2,"=");
                }
                echo "
";

                foreach ($headers as $header) {
                    echo ' '.str_pad($header,$columnwidth," ",1).'|';
                }
                if ($table=='turnitintool_users') {
                    echo ' '.str_pad('Name',$columnwidth," ",1).'|';
                }
                echo "
";

                echo str_pad('',(($columnwidth+2)*count($headers)),"=");
                if ($table=='turnitintool_users') {
                    echo str_pad('',$columnwidth+2,"=");
                }
                echo "
";

                foreach ($data as $datarow) {
                    $datarow=get_object_vars($datarow);
                    foreach ($datarow as $datacell) {
                        echo ' '.htmlspecialchars(str_pad(substr($datacell,0,$columnwidth),$columnwidth," ",1)).'|';
                    }
                    if ($table=='turnitintool_users') {
                        $moodleuser=turnitintool_get_record('user','id',$datarow['userid']);
                        echo ' '.str_pad(substr($moodleuser->firstname.' '.$moodleuser->lastname,0,$columnwidth),$columnwidth," ",1).'|';
                    }
                    echo "
";
                }
                echo str_pad('',(($columnwidth+2)*count($headers)),"-");
                if ($table=='turnitintool_users') {
                    echo str_pad('',$columnwidth+2,"-");
                }
                echo "

";
            } else {
                echo get_string('notavailableyet','turnitintool')."
";
            }

        }

        if ($param_do=='viewreport') {
            echo "</pre>";
        }
    } else if (!is_null($param_do) AND $param_do=="unlinkusers") {

        if (!is_null($param_unlink) AND isset($param_userlinks) AND count($param_userlinks)>0) {
            foreach ($param_userlinks as $userlink) {
                $user = new stdClass();
                $user->id = $userlink;
                if ( $tuser = turnitintool_get_record('turnitintool_users','id',$userlink) AND $muser = turnitintool_get_record('user','id',$tuser->userid) ) {
                    $user->turnitin_uid = 0;
                    turnitintool_update_record('turnitintool_users',$user);
                } else {
                    turnitintool_delete_records('turnitintool_users','id',$userlink);
                }
            }
        }

        if (!is_null($param_relink) AND isset($param_userlinks) AND count($param_userlinks)>0) {
            $loaderbar = new turnitintool_loaderbarclass(count($param_userlinks));
            foreach ($param_userlinks as $userlink) {
                if ( $tuser = turnitintool_get_record('turnitintool_users','id',$userlink) AND $muser = turnitintool_get_record('user','id',$tuser->userid) ) {
                    // Get the email address if the user has been deleted
                    if ( empty( $muser->email ) OR strpos( $muser->email, '@' ) === false ) {
                        $split=explode('.',$muser->username);
                        array_pop($split);
                        $muser->email=join('.',$split);
                    }
                    $tii = new turnitintool_commclass(null,$muser->firstname,$muser->lastname,$muser->email,$tuser->turnitin_utp,$loaderbar);
                    $tii->createUser($post,get_string('userprocess','turnitintool'));
                    $user = new stdClass();
                    $user->id = $userlink;
                    $user->turnitin_uid = ( $tii->getRerror() ) ? 0 : $tii->getUserID();
                    turnitintool_update_record('turnitintool_users',$user);
                    unset($tii);
                } else {
                    turnitintool_delete_records('turnitintool_users','id',$userlink);
                }
            }
            unset($loader);
            turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/extras.php?do=unlinkusers');
        }

        turnitintool_header(NULL,NULL,$_SERVER["REQUEST_URI"],get_string("modulenameplural", "turnitintool"), $SITE->fullname);

        echo '<div id="turnitintool_style">';

        turnitintool_box_start('generalbox boxwidthwide boxaligncenter', 'general');

        echo '<b>'.get_string('unlinkrelinkusers','turnitintool').'</b><br /><br />';

        echo '<form method="POST" id="turnitin_unlink" action="'.$CFG->wwwroot.'/mod/turnitintool/extras.php?do=unlinkusers"><div style="height: 400px;overflow: auto;">
';

        if ($userrows=turnitintool_get_records('turnitintool_users')) {

            foreach ($userrows as $userdata) {
                if (!$user=turnitintool_get_record('user','id',$userdata->userid)) {
                    $user->id = $user->userid;
                    $user->lastname = get_string('nonmoodleuser','turnitintool');
                    $user->firstname = '';
                    $user->email = get_string('notavailableyet','turnitintool');
                }
                $user->turnitin_uid = $userdata->turnitin_uid;
                $user->turnitin_utp = $userdata->turnitin_utp;
                $user->linkid = $userdata->id;
                $userarray[]=$user;
            }
            $lastname=array();
            // Obtain the columns to sort on
            foreach ($userarray as $key => $row) {
                $lastname[$key]  = $row->lastname;
            }

            $table->width='100%';
            $table->tablealign='center';
            $table->id='unlink';
            $table->class='submissionTable';

            unset($cells);
            $cells[0]->class = 'header c0 iconcell';
            $cells[0]->data = '<input type="checkbox" name="toggle" onclick="checkUncheckAll(this,\'userlinks\','.count($userarray).')" />';
            $cells[1]->class = 'header c1 markscell';
            $cells[1]->data = get_string( 'turnitinid', 'turnitintool' );
            $cells[2]->class = 'header c2';
            $cells[2]->data = get_string( 'usersunlinkrelink', 'turnitintool' );
            $cells[3]->class = 'header c3';
            $cells[3]->data = get_string( 'pseudoemailaddress', 'turnitintool' );

            $table->rows[0]->cells=$cells;

            array_multisort($lastname, SORT_ASC, $userarray);
            $i = 0;
            foreach ($userarray as $user) {
                unset($cells);
                $cells[0]->class = 'cell c0 iconcell';
                $cells[0]->data = '<input type="checkbox" id="userlinks_'.$i.'" name="userlinks[]" value="'.$user->linkid.'" />';
                $cells[1]->class = 'cell c1 markscell';
                $cells[1]->data = ( $user->turnitin_uid > 0 ) ? $user->turnitin_uid : null;
                $cells[2]->class = 'cell c2';
                $cells[2]->data = ( !empty($user->firstname) ) ? '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'">' : '';
                $cells[2]->data .= $user->lastname.', '.$user->firstname;
                $cells[2]->data .= ( !empty($user->firstname) ) ? '</a>' : '';
                $cells[2]->data .=' ('.$user->email.')';
                $cells[3]->class = 'cell c3';
                $cells[3]->data = ( $user->turnitin_utp == 1 ) ? turnitintool_pseudoemail( $user->email ) : '-';
                $i++;
                $table->rows[$i]->class='row r' . (($i%2) ? 0 : 1);
                $table->rows[$i]->cells=$cells;
            }

            turnitintool_print_table($table);

        }

        echo '</div><input style="margin-top: 7px;" name="unlink" value="Unlink Users" type="submit" /> <input style="margin-top: 7px;" name="relink" value="Relink Users" type="submit" /></form>
';

        turnitintool_box_end();

        echo '</div>';

        turnitintool_footer();
    } else if ( !is_null($param_do) AND ( $param_do=="commslog" OR $param_do=="activitylog" ) ) {

        $logsdir = $CFG->dataroot . "/temp/turnitintool/logs/";
        $savefile = $param_do.'_'.$param_filedate.'.log';

        if ( !is_null( $param_filedate ) ) {

            header("Content-type: plain/text; charset=UTF-8");
            send_file( $logsdir.$savefile, $savefile, false );

        } else {

            turnitintool_header(NULL,NULL,$_SERVER["REQUEST_URI"],get_string("modulenameplural", "turnitintool"), $SITE->fullname);
            turnitintool_box_start('generalbox boxwidthwide boxaligncenter', 'general');

            $label = 'commslog';
            $tabs[] = new tabobject( $label, $CFG->wwwroot.'/mod/turnitintool/extras.php?do='.$label,
                ucfirst( $label ), ucfirst( $label ), false );
            $label = 'activitylog';
            $tabs[] = new tabobject( $label, $CFG->wwwroot.'/mod/turnitintool/extras.php?do='.$label,
                ucfirst( $label ), ucfirst( $label ), false );
            $inactive = array( $param_do );
            $selected = $param_do;
            print_tabs( array( $tabs ), $selected, $inactive );

            if ( file_exists( $logsdir ) AND $readdir = opendir( $logsdir ) ) {
                $output = '';
                while ( false !== ( $entry = readdir( $readdir ) ) ) {
                    if ( substr_count( $entry, $param_do ) > 0 ) {
                        $date = str_replace( '.log', '', array_pop( split( '_', $entry ) ) );
                        $year = substr( $date, 0, 4 );
                        $month = substr( $date, 4, 2 );
                        $day = substr( $date, 6, 2 );
                        $output .= '<a href="'.$CFG->wwwroot.'/mod/turnitintool/extras.php?do=' . $param_do . '&filedate=' . $date . '">' . ucfirst($param_do) . ' (' . userdate( strtotime( $year . '-' . $month . '-' . $day ), '%Y-%m-%d %H:%M:%S' ) . ')</a><br />'.PHP_EOL;
                    }
                }
                echo $output;
            } else {
                echo get_string("nologsfound");
            }

            echo "<br />";

            turnitintool_box_end();
            turnitintool_footer();

        }

    } else {

        $post = new stdClass();
        $post->utp='2';

        $loaderbar = new turnitintool_loaderbarclass(3);
        $tii = new turnitintool_commclass(turnitintool_getUID($USER),$USER->firstname,$USER->lastname,$USER->email,2,$loaderbar);
        $tii->startSession();

        $result=$tii->createUser($post,get_string('connecttesting','turnitintool'));

        $rcode=$tii->getRcode();
        $rmessage=$tii->getRmessage();
        $tiiuid=$tii->getUserID();

        $tii->endSession();

        turnitintool_header(NULL,NULL,$_SERVER["REQUEST_URI"],get_string("modulenameplural", "turnitintool"), $SITE->fullname);
        turnitintool_box_start('generalbox boxwidthwide boxaligncenter', 'general');
        if ($rcode>=API_ERROR_START OR empty($rcode)) {
            if (empty($rmessage)) {
                $rmessage=get_string('connecttestcommerror','turnitintool');
            }
            turnitintool_print_error('connecttesterror','turnitintool',$CFG->wwwroot.'/admin/module.php?module=turnitintool',$rmessage,__FILE__,__LINE__);
        } else {
            $data=new object();
            $data->userid=$USER->id;
            $data->turnitin_uid=$tiiuid;
            $data->turnitin_utp=$tii->utp;
            if ($tiiuser=turnitintool_get_record('turnitintool_users','userid',$USER->id)) {
                $data->id=$tiiuser->id;
                turnitintool_update_record('turnitintool_users',$data);
            } else {
                turnitintool_insert_record('turnitintool_users',$data);
            }
            print_string('connecttestsuccess','turnitintool');
        }
        turnitintool_box_end();
        turnitintool_footer();

    }

/* ?> */