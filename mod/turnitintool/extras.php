<?php  // $Id: extras.php,v 1.2 2010/06/25 11:49:46 paul.dawson Exp $
/**
 * @package   turnitintool
 * @copyright 2012 Turnitin
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
        $jsurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/scripts/jquery-1.7.2.min.js');
        $PAGE->requires->js($jsurl,true);
        $jsurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/scripts/datatables.min.js');
        $PAGE->requires->js($jsurl);
        $jsurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/scripts/datatables.plugins.js');
        $PAGE->requires->js($jsurl);
        $jsurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/scripts/turnitintool.js');
        $PAGE->requires->js($jsurl,true);
        $cssurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/styles.css');
        $PAGE->requires->css($cssurl);
    } else {
        require_js($CFG->wwwroot.'/mod/turnitintool/scripts/jquery-1.7.2.min.js');
        require_js($CFG->wwwroot.'/mod/turnitintool/scripts/turnitintool.js');
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
    $param_fileid=optional_param('fileid',null,PARAM_CLEAN);
    $param_filerem=optional_param('filerem',null,PARAM_CLEAN);
    $param_filehash=optional_param('filehash',null,PARAM_CLEAN);

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
                    if ($table=='turnitintool_users' AND $moodleuser=turnitintool_get_record('user','id',$datarow['userid'])) {
                        echo ' '.str_pad(substr($moodleuser->firstname.' '.$moodleuser->lastname,0,$columnwidth),$columnwidth," ",1).'|';
                    } else {
                        echo ' '.str_pad(' ',$columnwidth," ",1).'|';
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
                    turnitintool_delete_records_select('turnitintool_users','id',$userlink);
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
                    $tuser->turnitin_utp = ( $tuser->turnitin_utp != 0 ) ? $tuser->turnitin_utp : 1;
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

        turnitintool_box_start('generalbox boxaligncenter', 'general');

        echo '<b>'.get_string('unlinkrelinkusers','turnitintool').'</b><br /><br />';

        // 'tu.userid', 'tu.turnitin_uid', 'tu.turnitin_utp', 'mu.firstname', 'mu.lastname', 'mu.email', 'tu.turnitin_uid'
        
        if ( isset($CFG->turnitin_enablepseudo) AND $CFG->turnitin_enablepseudo == 1 ) {
            $pseudo = 1;
            $pseudo_visible = 'true';
        } else {
            $pseudo = 0;
            $pseudo_visible = 'false';
        }
        
        echo '
    <style>
    #unlink .header.sort div {
        background: url(pix/sortnone.png) no-repeat right center;
    }
    #unlink .header.asc div {
        background: url(pix/sortdown.png) no-repeat right center;
    }
    #unlink .header.desc div {
        background: url(pix/sortup.png) no-repeat right center;
    }
    #turnitintool_style .paginate_disabled_previous {
        background: url(pix/prevdisabled.png) no-repeat left center;
    }
    #turnitintool_style .paginate_enabled_previous {
        background: url(pix/prevenabled.png) no-repeat left center;
    }
    #turnitintool_style .paginate_disabled_next {
        background: url(pix/nextdisabled.png) no-repeat right center;
    }
    #turnitintool_style .paginate_enabled_next {
        background: url(pix/nextenabled.png) no-repeat right center;
    }
    #turnitintool_style .dataTables_processing {
        background: url(pix/loaderanim.gif) no-repeat center top;
    }
    </style>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery.fn.dataTableExt.oStdClasses.sSortable = "header sort";
            jQuery.fn.dataTableExt.oStdClasses.sSortableNone = "header nosort";
            jQuery.fn.dataTableExt.oStdClasses.sSortAsc = "header asc";
            jQuery.fn.dataTableExt.oStdClasses.sSortDesc = "header desc";
            jQuery.fn.dataTableExt.oStdClasses.sWrapper = "submissionTable";
            jQuery.fn.dataTableExt.oStdClasses.sStripeOdd = "row r0";
            jQuery.fn.dataTableExt.oStdClasses.sStripeEven = "row r1";
            jQuery("#unlink").dataTable( {
                "bProcessing": true,
                "bServerSide": true,
                "aoColumns": [
                            { "sClass": "toggle c0", "sWidth": "5%" },
                            { "sClass": "turnitin_uid c1", "sWidth": "15%" },
                            {},
                            { "sClass": "fullname c2", "sWidth": "'.(($pseudo) ? '40%' : '75%' ).'" },
                            { "sClass": "pseudo c3", "sWidth": "35%" },
                            {}
                        ],
                "aoColumnDefs": [
                            { "bSearchable": true, "bVisible": true, "bSortable": false, "aTargets": [ 0 ] },
                            { "bSearchable": true, "bVisible": true, "aTargets": [ 1 ] },
                            { "bSearchable": true, "bVisible": false, "aTargets": [ 2 ] },
                            { "bSearchable": true, "bVisible": true, "aTargets": [ 3 ] },
                            { "bSearchable": true, "bVisible": '.$pseudo_visible.', "aTargets": [ 4 ] },
                            { "bSearchable": true, "bVisible": false, "aTargets": [ 5 ] }
                        ],
                "aaSortingFixed": [[ 0, "asc" ]],
                "sAjaxSource": "userlinktable.php?pseudo='.$pseudo.'",
                "oLanguage": '.turnitintool_datatables_strings().',
                "sDom": "r<\"dt_page nav\"pi><\"top\"lf>t<\"bottom\"><\"dt_page\"pi>",
                "bStateSave": true
            } );
            var oTable = jQuery(".dataTable").dataTable();
            oTable.fnSetFilteringDelay(1000);
            jQuery("#unlink_filter").append( "<label id=\"check_filter\"><input class=\"linkcheck\" type=\"checkbox\" /> ' . get_string( 'unlinkedusers', 'turnitintool' ) . '</label>" );
            var oSettings = oTable.fnSettings();
            if ( oSettings ) {
                var checkval = oSettings.aoPreSearchCols[1].sSearch;
                if ( checkval == "##linked##" ) {
                    jQuery("#check_filter .linkcheck").attr( "checked", "checked" );
                }
            }
            jQuery("#check_filter input").change( function () {
                var filter = "";
                if (this.checked) {
                    filter = "##linked##";
                }
                oTable.fnFilter( filter, 1 );
            } );
            jQuery("#toggle").change( function () {
                checkUncheckAll(this,\'userlinks\');
            } );
        } );
    </script>';
        
        echo '<form method="POST" id="turnitin_unlink" action="'.$CFG->wwwroot.'/mod/turnitintool/extras.php?do=unlinkusers">
';
        echo '   
    <table id="unlink">
        <thead>
            <tr>
                <th class="toggle"><div><input type="checkbox" name="toggle" id="toggle" /></div></th>
                <th class="turnitin_uid"><div>'.get_string( 'turnitinid', 'turnitintool' ).'</div></th>
                <th></th>
                <th class="fullname"><div>'.get_string( 'usersunlinkrelink', 'turnitintool' ).'</div></th>
                <th class="pseudo"><div>'.get_string( 'pseudoemailaddress', 'turnitintool' ).'</div></th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>';

        echo '<input style="margin-top: 7px;" name="unlink" value="Unlink Users" type="submit" /> <input style="margin-top: 7px;" name="relink" value="Relink Users" type="submit" /></form>
';

        turnitintool_box_end();

        echo '</div>';

        if (isset($PAGE) AND @is_callable(array($PAGE->requires, 'js'))) { // Are we using new moodle or old?
            // We already added the Moodle 2.0+ stuff
        } else {
            // These need to go to the botton here to avoid conflicts
            require_js($CFG->wwwroot.'/mod/turnitintool/scripts/datatables.min.js');
            require_js($CFG->wwwroot.'/mod/turnitintool/scripts/datatables.plugins.js');
            require_js($CFG->wwwroot.'/mod/turnitintool/scripts/inboxtable.js');
        }

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
                        $split = preg_split( "/_/", $entry );
                        $pop = array_pop( $split );
                        $date = str_replace( '.log', '', $pop );
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

    } else if ( !is_null($param_do) AND $param_do=="files" ) {
        
        if (!is_callable("get_file_storage")) {
            turnitintool_print_error( "moodle2only", "turnitintool" );
            exit();
        }
        
        if ( !is_null( $param_fileid ) ) {
            
            if ( $filedata = $DB->get_record( "files", array( "id" => $param_fileid, "component" => "mod_turnitintool", "pathnamehash" => $param_filehash ) ) ) {
                $submission = $DB->get_record( "turnitintool_submissions", array( "id" => $filedata->itemid ) );
            } else {
                turnitintool_print_error( "submissiongeterror", "turnitintool" );
                exit();
            }
            
            if ( !is_null( $param_filerem ) ) {
                $fs = get_file_storage();
                $file = $fs->get_file($filedata->contextid,'mod_turnitintool','submission',$filedata->itemid,'/',$filedata->filename);
                $file->delete();
                turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/extras.php?do=files');
                exit();
            } else {
                $fs = get_file_storage();
                $file = $fs->get_file($filedata->contextid,'mod_turnitintool','submission',$filedata->itemid,'/',$filedata->filename);
                $filename = isset( $submission->submission_filename ) ? $submission->submission_filename : $filedata->filename;
                try {
                    send_stored_file($file, 0, 0, true, $filename );
                } catch ( Exception $e ) {
                    send_stored_file($file, 0, 0, true, array( "filename" => $filename ) );
                }
            }

        } else {
        
            turnitintool_header(NULL,NULL,$_SERVER["REQUEST_URI"],get_string("modulenameplural", "turnitintool"), $SITE->fullname);
            $modules = $DB->get_record( 'modules', array( 'name' => 'turnitintool' ) );
            echo '
    <style>
    #files .header.sort div {
        background: url(pix/sortnone.png) no-repeat right center;
    }
    #files .header.asc div {
        background: url(pix/sortdown.png) no-repeat right center;
    }
    #files .header.desc div {
        background: url(pix/sortup.png) no-repeat right center;
    }
    #files a.fileicon {
        padding-left: 18px;
        display: inline-block;
        min-height: 16px;
        background: url(pix/fileicon.gif) no-repeat left center;
    }
    #turnitintool_style .paginate_disabled_previous {
        background: url(pix/prevdisabled.png) no-repeat left center;
    }
    #turnitintool_style .paginate_enabled_previous {
        background: url(pix/prevenabled.png) no-repeat left center;
    }
    #turnitintool_style .paginate_disabled_next {
        background: url(pix/nextdisabled.png) no-repeat right center;
    }
    #turnitintool_style .paginate_enabled_next {
        background: url(pix/nextenabled.png) no-repeat right center;
    }
    #turnitintool_style .dataTables_processing {
        background: url(pix/loaderanim.gif) no-repeat center top;
    }
    </style>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery.fn.dataTableExt.oStdClasses.sSortable = "header sort";
            jQuery.fn.dataTableExt.oStdClasses.sSortAsc = "header asc";
            jQuery.fn.dataTableExt.oStdClasses.sSortDesc = "header desc";
            jQuery.fn.dataTableExt.oStdClasses.sWrapper = "submissionTable";
            jQuery.fn.dataTableExt.oStdClasses.sStripeOdd = "row r0";
            jQuery.fn.dataTableExt.oStdClasses.sStripeEven = "row r1";
            jQuery("#files").dataTable( {
                "fnDrawCallback": function ( oSettings ) {
                    if ( oSettings.aiDisplay.length == 0 )
                    {
                        return;
                    }

                    var nTrs = jQuery("#files tbody tr");
                    var iColspan = nTrs[0].getElementsByTagName("td").length;
                    var sLastGroup = "";
                    for ( var i=0 ; i<nTrs.length ; i++ )
                    {
                        var iDisplayIndex = oSettings._iDisplayStart + i;
                        var sGroup = oSettings.aoData[ oSettings.aiDisplay[i] ]._aData[0];
                        if ( sGroup != sLastGroup )
                        {
                            var nGroup = document.createElement( "tr" );
                            var nCell = document.createElement( "td" );
                            nCell.colSpan = iColspan;
                            nCell.className = "group";
                            nCell.innerHTML = sGroup;
                            nGroup.appendChild( nCell );
                            nTrs[i].parentNode.insertBefore( nGroup, nTrs[i] );
                            sLastGroup = sGroup;
                        }
                    }
                },
                "bProcessing": true,
                "bServerSide": true,
                "aoColumns": [
                            null,
                            null,
                            null,
                            { "sClass": "filename c0", "sWidth": "40%" },
                            null,
                            { "sClass": "fullname c1", "sWidth": "35%" },
                            null,
                            { "sClass": "created c2", "sWidth": "22%" },
                            { "sClass": "remove c3", "sWidth": "3%" }
                        ],
                "aoColumnDefs": [
                            { "bSearchable": true, "bVisible": false, "aTargets": [ 0 ] },
                            { "bSearchable": true, "bVisible": false, "aTargets": [ 1 ] },
                            { "bSearchable": true, "bVisible": false, "aTargets": [ 2 ] },
                            { "bSearchable": true, "bVisible": true, "aTargets": [ 3 ] },
                            { "bSearchable": true, "bVisible": false, "aTargets": [ 4 ] },
                            { "bSearchable": true, "bVisible": true, "aTargets": [ 5 ] },
                            { "bSearchable": true, "bVisible": false, "aTargets": [ 6 ] },
                            { "bSearchable": true, "bVisible": true, "aTargets": [ 7 ] },
                            { "bSearchable": true, "bVisible": true, "aTargets": [ 8 ] }
                        ],
                "aaSortingFixed": [[ 0, "asc" ]],
                "sAjaxSource": "filestable.php?module='.$modules->id.'",
                "oLanguage": '.turnitintool_datatables_strings().',
                "sDom": "r<\"dt_page\"pi><\"top nav\"lf>t<\"bottom\"><\"dt_page\"pi>",
                "bStateSave": true
            } );
            var oTable = jQuery(".dataTable").dataTable();
            oTable.fnSetFilteringDelay(1000);
            jQuery("#files_filter").append( "<label id=\"check_filter\"><input class=\"deletecheck\" type=\"checkbox\" /> ' . get_string( 'deletable', 'turnitintool' ) . '</label>" );
            var oSettings = oTable.fnSettings();
            if ( oSettings ) {
                var checkval = oSettings.aoPreSearchCols[8].sSearch;
                if ( checkval == "##deletable##" ) {
                    jQuery("#check_filter .deletecheck").attr( "checked", "checked" );
                }
            }
            jQuery("#check_filter input").change( function () {
                var filter = "";
                if (this.checked) {
                    filter = "##deletable##";
                }
                oTable.fnFilter( filter, 8 );
            } );
        } );
    </script>';
            echo '<div id="turnitintool_style">';
            turnitintool_box_start('generalbox boxaligncenter', 'general');
            echo '
    <b>' . get_string( 'filebrowser', 'turnitintool' ) . '</b><br /><br />
    <table id="files">
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th class="filename"><div>' . get_string( 'filename', 'turnitintool' ) . '</div></th>
                <th></th>
                <th class="fullname"><div>' . get_string( 'user', 'turnitintool' ) . '</div></th>
                <th></th>
                <th class="created"><div>' . get_string( 'created', 'turnitintool' ) . '</div></th>
                <th class="delete"><div>&nbsp;</div></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table></div>';
            turnitintool_box_end();

            if (isset($PAGE) AND @is_callable(array($PAGE->requires, 'js'))) { // Are we using new moodle or old?
                // We already added the Moodle 2.0+ stuff
            } else {
                // These need to go to the botton here to avoid conflicts
                require_js($CFG->wwwroot.'/mod/turnitintool/scripts/datatables.min.js');
                require_js($CFG->wwwroot.'/mod/turnitintool/scripts/datatables.plugins.js');
                require_js($CFG->wwwroot.'/mod/turnitintool/scripts/inboxtable.js');
            }

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
        if ($rcode>=TURNITINTOOL_API_ERROR_START OR empty($rcode)) {
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