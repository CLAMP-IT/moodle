<?php

    require_once('../../config.php');
    require_once($CFG->libdir.'/filelib.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once('../../repository/lib.php');
    require_once('repos/get_repo.php');

    $id=required_param('id', PARAM_INT);
    $file    = optional_param('file', '', PARAM_PATH);
    $wdir    = optional_param('wdir', '', PARAM_PATH);
    $action  = optional_param('action', '', PARAM_ACTION);
    $name    = optional_param('name', '', PARAM_FILE);
    $oldname = optional_param('oldname', '', PARAM_FILE);
    $choose  = optional_param('choose', '', PARAM_FILE); //in fact it is always 'formname.inputname'
    $save    = optional_param('save', 0, PARAM_BOOL);
    $text    = optional_param('textdata', '', PARAM_RAW);
    $confirm = optional_param('confirm', 0, PARAM_BOOL);
    $listonly= optional_param('listonly', 3, PARAM_INT);
    $itemid   = optional_param('itemid',SYSCONTEXTID, PARAM_INT);
    $client_id = optional_param('client_id', '', PARAM_ALPHANUM);
    $savepath = optional_param('savepath', '', PARAM_PATH);
    $draftpath = optional_param('draftpath', '', PARAM_PATH);
    $contextid = optional_param('ctx_id', SYSCONTEXTID, PARAM_INT);
    $shortpath = optional_param('shortpath', 0, PARAM_BOOL);
    $noview = optional_param('noview', 0, PARAM_BOOL);
    $hiderepolist = optional_param('hiderepolist', 0, PARAM_BOOL);

    global $DB, $PAGE;

    $PAGE->set_url('/blocks/repo_filemanager/index.php');

    if ($choose)
        $PAGE->set_pagelayout('popup');
    else
        $PAGE->blocks->show_only_fake_blocks();

    if ($choose)
    {
        if (count(explode('.', $choose)) > 2)
        {
            error('Incorrect format for choose parameter');
        }
    }

    if (! $course = $DB->get_record("course", array("id"=>$id)))
    {
        error("That's an invalid course id");
    }

    require_login($course);

    $context=get_context_instance(CONTEXT_COURSE, $id);

    $params = array(
        'context'=>array($context, get_system_context()),
        'currentcontext'=>$context,
        'onlyvisible'=>true,
        'type'=>null);

    $repolist = repository::get_instances($params);
    $repoinstances=array();

    /****Filter the repos list***/

    $firstmr=-1;
    $repoinstances=array();
    foreach ($repolist as $repoi)
    {
        /****Set coursefilearea as default view, since this is the only type with useful functions for now****/
        if ($repoi->get_meta()->type=="coursefilearea")
            $firstmr=$repoi->id;

        /****Ignore upload repo type, this will be handled differently****/
        if ($repoi->get_meta()->type!="upload")
        {
            if ($listonly==3)
            {
                    $repoinstances[]=$repoi;
            }
            else
            {
                /***Filter the list of repositories so that only the relevant types are shown***/
                if ($repoi->supported_returntypes()==$listonly || $repoi->supported_returntypes()==3)
                    $repoinstances[]=$repoi;
            }
        }
    }

    if ($firstmr==-1)
        $firstmr=current($repoinstances)->id;

    $repoid = optional_param('repoid', $firstmr, PARAM_INT);

    /***Find the requested repo, use current if it can't be found***/
    $repoi=false;
    foreach ($repoinstances as $instance)
    if ($instance->id==$repoid)
    {
        $repoi=$instance;
        break;
    }

    if ($repoi==false)
    {
        reset($repoinstances);
        $repoid=current($repoinstances)->id;
        $repoi=current($repoinstances);
    }

    /***Get the repo manager object***/
    $repo=get_repomanager_instance($repoi, $course);

    /***Common parameters for all urls***/
    $data_params=array('id'=>$id, 'choose'=>$choose, 'listonly'=>$listonly, 'itemid'=>$itemid, 'shortpath'=>$shortpath,
        'client_id'=>$client_id, 'draftpath'=>$draftpath, 'savepath'=>$savepath, 'ctx_id'=>$contextid, 'hiderepolist'=>$hiderepolist);

    $form_params="";
    $link_params="";
    foreach($data_params as $key => $value)
    {
        $form_params.="<input type=\"hidden\" name=\"".$key."\" value=\"".$value."\" />\n";
        $link_params.=$key."=".$value."&amp;";
    }

    $PAGE->set_url('/blocks/repo_filemanager/index.php?'.$link_params);

    /***End of configuration and access control***/

    $baseweb = $CFG->wwwroot;

    switch ($action) {

        case "upload":
            html_header($course, $wdir);

            if ($save and confirm_sesskey())
            {
                $m=$repo->upload($wdir);
                if (strlen($m)>0)
                    notify($m);
                displaydir($wdir);
            }
            else
            {
                $filesize = display_size(get_max_upload_file_size($repo->max_upload_bytes()));

                $struploadafile = get_string("uploadafile");
                $struploadthisfile = get_string("uploadthisfile");
                $strmaxsize = get_string("maxsize", "", $filesize);
                $strcancel = get_string("cancel");

                echo "<p>$struploadafile ($strmaxsize) --> <b>$wdir</b></p>\n".
                     "<form enctype=\"multipart/form-data\" method=\"post\" action=\"index.php\">\n".
                     "<div>\n".
                     "<table><tr><td colspan=\"2\">\n".
                     " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />\n".
                     $form_params.
                     " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />\n".
                     " <input type=\"hidden\" name=\"action\" value=\"upload\" />\n".
                     " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />\n";
                upload_print_form_fragment_old(1,array('userfile'),null,false,null,get_max_upload_file_size($repo->max_upload_bytes()),0,false);
                echo " </td></tr></table>\n".
                     " <input type=\"submit\" name=\"save\" value=\"$struploadthisfile\" />\n".
                     "</div>\n".
                     "</form>\n".
                     "<form action=\"index.php\" method=\"get\">\n".
                     "<div>\n".
                     " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />\n".
                     $form_params.
                     " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />\n".
                     " <input type=\"hidden\" name=\"action\" value=\"cancel\" />\n".
                     " <input type=\"submit\" value=\"$strcancel\" />\n".
                     "</div>\n".
                     "</form>\n";
            }
            break;

        case "delete":
            if ($confirm and confirm_sesskey())
            {
                html_header($course, $wdir);
                if (!empty($USER->filelist))
                 echo $repo->delete_files($USER->filelist);

                clearfilelist();
                displaydir($wdir);

            }
            else
            {
                html_header($course, $wdir);

                if (setfilelist($_POST))
                {
                    notify(get_string('deletecheckwarning').':');
                    $OUTPUT->box_start("center");
                    printfilelist($USER->filelist);
                    $OUTPUT->box_end();
                    echo "<br />";

                    require_once($CFG->dirroot.'/mod/resource/lib.php');
                    //$block = resource_delete_warning($course, $USER->filelist);

                    //if (empty($CFG->resource_blockdeletingfile) or $block == ''){
                    //if (empty($CFG->resource_blockdeletingfile))
                    //{
                        $optionsyes = array_merge(array('id'=>$id, 'repoid'=>$repoid, 'wdir'=>$wdir, 'action'=>'delete', 'confirm'=>1, 'sesskey'=>sesskey()), $data_params);
                        $optionsno  = array_merge(array('id'=>$id, 'repoid'=>$repoid, 'wdir'=>$wdir, 'action'=>'cancel'), $data_params);
                        $buttoncontinue = new single_button(new moodle_url('index.php', $optionsyes), get_string('yes'), 'post');
                        $buttoncancel = new single_button(new moodle_url('index.php', $optionsno), get_string('no'), 'post');
                        echo $OUTPUT->confirm(get_string('deletecheckfiles'), $buttoncontinue, $buttoncancel);
                    //}
                    //else
                    //{
                    //    notify(get_string('warningblockingdelete', 'block_repo_filemanager'));
                    //    $options = array_merge(array('id'=>$id, 'repoid'=>$repoid, 'wdir'=>$wdir, 'action'=>'cancel', 'choose'=>$choose, 'listonly'=>$listonly), $data_params);
                    //    echo $OUTPUT->continue_button(new moodle_url('index.php', $options));
                    //}
                } 
                else
                    displaydir($wdir);

            }
            break;

        case "move":
            html_header($course, $wdir);
            if (($count = setfilelist($_POST)) and confirm_sesskey())
            {
                $USER->fileop     = $action;
                $USER->filesource = $wdir;
                echo "<p class=\"centerpara\">";
                print_string("selectednowmove", "moodle", $count);
                echo "</p>";
            }
            displaydir($wdir);
            break;

        case "paste":
            html_header($course, $wdir);
            if (isset($USER->fileop) and ($USER->fileop == "move") and confirm_sesskey())
                    echo $repo->move_files($USER->filelist, $wdir);

            clearfilelist();
            displaydir($wdir);
            break;

        case "rename":
            if (($name != '') and confirm_sesskey())
            {
                html_header($course, $wdir);
                $repo->rename_file($oldname, $name, $wdir);
                displaydir($wdir);

            }
            else
            {
                $strrename = get_string("rename");
                $strcancel = get_string("cancel");
                $strrenamefileto = get_string("renamefileto", "moodle", $file);
                html_header($course, $wdir, "form.name");
                echo "<p>$strrenamefileto:</p>".
                     "<table><tr><td>".
                     "<form action=\"index.php\" method=\"post\">".
                     "<fieldset class=\"invisiblefieldset\">".
                     " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
                     $form_params.
                     " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
                     " <input type=\"hidden\" name=\"action\" value=\"rename\" />".
                     " <input type=\"hidden\" name=\"oldname\" value=\"$file\" />".
                     " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />".
                     " <input type=\"text\" name=\"name\" size=\"35\" value=\"$file\" />".
                     " <input type=\"submit\" value=\"$strrename\" />".
                     "</fieldset>".
                     "</form>".
                     "</td><td>\n".
                     "<form action=\"index.php\" method=\"get\">".
                     "<div>".
                     " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
                     $form_params.
                     " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
                     " <input type=\"hidden\" name=\"action\" value=\"cancel\" />".
                     " <input type=\"submit\" value=\"$strcancel\" />".
                     "</div>".
                     "</form>".
                     "</td></tr></table>";
            }
            break;

        case "makedir":
            if (($name != '') and confirm_sesskey())
            {
                html_header($course, $wdir);
                echo $repo->mkdir($wdir, $name);
                displaydir($wdir);

            }
            else
            {
                $strcreate = get_string("create");
                $strcancel = get_string("cancel");
                $strcreatefolder = get_string("createfolder", "moodle", $wdir);
                html_header($course, $wdir, "form.name");
                echo "<p>$strcreatefolder:</p>".
                     "<table><tr><td>".
                     "<form action=\"index.php\" method=\"post\">".
                     "<fieldset class=\"invisiblefieldset\">".
                     " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
                     $form_params.
                     " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
                     " <input type=\"hidden\" name=\"action\" value=\"makedir\" />".
                     " <input type=\"text\" name=\"name\" size=\"35\" />".
                     " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />".
                     " <input type=\"submit\" value=\"$strcreate\" />".
                     "</fieldset>".
                     "</form>".
                     "</td><td>".
                     "<form action=\"index.php\" method=\"get\">".
                     "<div>".
                     " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
                     $form_params.
                     " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
                     " <input type=\"hidden\" name=\"action\" value=\"cancel\" />".
                     " <input type=\"submit\" value=\"$strcancel\" />".
                     "</div>".
                     "</form>".
                     "</td></tr></table>";
            }
            break;

        case "edit":
            html_header($course, $wdir);
            if ((strlen($text)>0) && confirm_sesskey())
            {
                $repo->save_text($file, $text);
                displaydir($wdir);

            }
            else
            {
                $streditfile = get_string("edit", "", "<b>$file</b>");

                $contents=$repo->get_text($file);

                if (mimeinfo("type", $file) == "text/html")
                    $usehtmleditor = can_use_html_editor();
                else
                    $usehtmleditor = false;

                $OUTPUT->heading("$streditfile");

                echo "<form action=\"index.php\" method=\"post\">".
                     "<div style=\"text-align:center\">".
                     " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
                     $form_params.
                     " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
                     " <input type=\"hidden\" name=\"file\" value=\"$file\" />".
                     " <input type=\"hidden\" name=\"action\" value=\"edit\" />".
                     " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />";

                print_textarea($usehtmleditor, 25, 80, 680, 400, "textdata", $contents);

                echo "<br /><br /><input type=\"submit\" value=\"".get_string("savechanges")."\" />".
                     "</form><br /><br />".
                     "<form action=\"index.php\" method=\"get\">".
                     " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
                     $form_params.
                     " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
                     " <input type=\"hidden\" name=\"action\" value=\"cancel\" />".
                     " <input type=\"submit\" value=\"".get_string("cancel")."\" />".
                     "</div>".
                     "</form>";

                if ($usehtmleditor)
                    use_html_editor();

            }
            break;

        case "zip":
            if (($name != '') and confirm_sesskey())
            {
                html_header($course, $wdir);
                $m=$repo->zip_files($USER->filelist, $wdir.'/'.$name);
                if (strlen($m)>0)
                    print_error($m);
               
                clearfilelist();
                displaydir($wdir);

            }
            else
            {
                html_header($course, $wdir, "form.name");

                if (setfilelist($_POST))
                {
                    echo "<p style=\"text-align:center;\">".get_string("youareabouttocreatezip").":</p>";
                    $OUTPUT->box_start("center");
                    printfilelist($USER->filelist);
                    $OUTPUT->box_end();

                    echo "<br />".
                         "<p style=\"text-align:center;\">".get_string("whattocallzip")."</p>".
                         "<table><tr><td>".
                         "<form action=\"index.php\" method=\"post\">".
                         "<fieldset class=\"invisiblefieldset\">".
                         " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
                         $form_params.
                         " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
                         " <input type=\"hidden\" name=\"action\" value=\"zip\" />".
                         " <input type=\"text\" name=\"name\" size=\"35\" value=\"new.zip\" />".
                         " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />".
                         " <input type=\"submit\" value=\"".get_string("createziparchive")."\" />".
                         "</fieldset>".
                         "</form>".
                         "</td><td>".
                         "<form action=\"index.php\" method=\"get\">".
                         "<div>".
                         " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
                         $form_params.
                         " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
                         " <input type=\"hidden\" name=\"action\" value=\"cancel\" />".
                         " <input type=\"submit\" value=\"".get_string("cancel")."\" />".
                         "</div>".
                         "</form>".
                         "</td></tr></table>";
                }
                else
                {
                    displaydir($wdir);
                    clearfilelist();
                }
            }
            break;

        case "unzip":
            html_header($course, $wdir);
            if (($file != '') and confirm_sesskey())
            {
                $strok = get_string("ok");
                $strunpacking = get_string("unpacking", "", $file);

                echo "<p style=\"text-align:center;\">$strunpacking:</p>";

                $file = basename($file);
                $m=$repo->unzip_file($wdir.'/'.$file);
                if (strlen($m)>0)
                    print_error($m);

                echo "<div style=\"text-align:center\"><form action=\"index.php\" method=\"get\">".
                     "<div>".
                     " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
                     $form_params.
                     " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
                     " <input type=\"hidden\" name=\"action\" value=\"cancel\" />".
                     " <input type=\"submit\" value=\"$strok\" />".
                     "</div>".
                     "</form>".
                     "</div>";
            }
            else
                displaydir($wdir);

            break;

        case "listzip":
            html_header($course, $wdir);
            if (($file != '') and confirm_sesskey())
            {
                $strname = get_string("name");
                $strsize = get_string("size");
                $strmodified = get_string("modified");
                $strok = get_string("ok");
                $strlistfiles = get_string("listfiles", "", $file);

                echo "<p align=\"center\">$strlistfiles:</p>";
                $file = basename($file);

                $list=$repo->list_zip("$wdir/$file");
                if (is_string($list))
                    notify($list);
                else
                {
                    echo "<table cellpadding=\"4\" cellspacing=\"2\" border=\"0\" class=\"files\">".
                         "<tr class=\"file\"><th align=\"left\" class=\"header name\" scope=\"col\">$strname</th><th align=\"right\" class=\"header size\" scope=\"col\">$strsize</th><th align=\"right\" class=\"header date\" scope=\"col\">$strmodified</th></tr>";
                    foreach ($list as $item)
                    {
                        echo "<tr>";
                        print_cell("left", s($item->pathname));
                        if (! $item->is_directory)
                            print_cell("right", display_size($item->size));
                        else
                            echo "<td>&nbsp;</td>";

                        $filedate  = userdate($item->mtime, get_string("strftimedatetime"));
                        print_cell("right", $filedate);
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                echo "<br /><form action=\"index.php\" method=\"get\">".
                     "<div style=\"text-align:center\">".
                     " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
                     $form_params.
                     " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
                     " <input type=\"hidden\" name=\"action\" value=\"cancel\" />".
                     " <input type=\"submit\" value=\"$strok\" />".
                     "</div>".
                     "</form>";
            }
            else
                displaydir($wdir);

            break;

        case "cancel":
            clearfilelist();

        default:
            html_header($course, $wdir);
            if (get_class($repo)!="block_repofile_type")
                displaydir($wdir);
            else
            {
                if ($hiderepolist)
                    echo "<br />";
                else
                    print_repo_list();
                notify(get_string("not_manageable", "block_repo_filemanager"));
            }
            break;
    }

    html_footer();


/****Display Functions****/

    function html_header($course, $wdir, $formfield="")
    {
        global $CFG, $ME, $PAGE, $OUTPUT, $repo, $repoinstances, $repoid, $form_params, $link_params, $noview, $data_params;

        $PAGE->set_pagelayout('base');
        $PAGE->blocks->show_only_fake_blocks();

        $navlinks=$repo->get_nav_links($wdir, $data_params['choose']);
        $navigation = build_navigation($navlinks);

        if ($data_params['choose'])
        {
            $fullnav = '';
            $i = 0;
            foreach ($navlinks as $navlink)
            {
                // If this is the last link do not link
                if ($i == count($navlinks) - 1)
                    $fullnav .= $navlink['name'];
                else
                    $fullnav .= '<a href="'.$navlink['link'].'">'.$navlink['name'].'</a>';

                $fullnav .= ' -> ';
                $i++;
            }

            $fullnav = substr($fullnav, 0, -4);
            $fullnav = str_replace('->', '&raquo;', format_string($course->shortname) . " -> " . $fullnav);

            //print_header_simple($fullnav);
            $PAGE->set_title($fullnav);
            echo $OUTPUT->header();

            $chooseparts = explode('.', $data_params['choose']);
            if (count($chooseparts)==2 && $data_params['choose']!="mdl2")
            {
            ?>
            <script type="text/javascript">
            //<![CDATA[
            function set_value(txt) {
                //This doesn't work on IE 9 in some situations.
                opener.document.forms['<?php echo $chooseparts[0]."'].".$chooseparts[1] ?>.value = txt;
                window.close();
            }
            //]]>
            </script>

            <?php
            }
            elseif (count($chooseparts)==1)
            {
            ?>
            <script type="text/javascript">
            //<![CDATA[
            function set_value(txt) {
                opener.document.getElementById('<?php echo $chooseparts[0] ?>').value = txt;
                window.close();
            }
            //]]>
            </script>

            <?php

            }

            echo '<div id="navbar"><h2>'.$fullnav.'</h2></div>';

            //*********What to do about this ??*********
            //if ($course->id == SITEID and $wdir != "/backupdata") {
            //    print_heading(get_string("publicsitefileswarning3"), "center", 2);
            //}

        } else {
                //print_header($course->shortname.": ".get_string('pluginname', 'block_repo_filemanager'), $course->fullname, $navigation,  $formfield);
                $PAGE->set_title($course->shortname.": ".get_string('pluginname', 'block_repo_filemanager'));
                $PAGE->set_heading($course->fullname);
                echo $OUTPUT->header();
        }

        if ($noview)
            notify(get_string('noview', 'block_repo_filemanager'));

        echo "<table style=\"margin-left:auto;margin-right:auto;\"><tr><td>";

    }


    function print_repo_list()
    {
        global $repoinstances, $form_params, $course, $repoid;

        /******Multi line tab display seems to be broken, re-enable this if it ever works**********
        echo "<h2>".get_string("available_repo", "block_repo_filemanager").
             "</h2>";

        $rows = array();
        $row = array();
        $currenttab=$repoinstances[0]->id;

        $count=0;
        foreach ($repoinstances as $r)
        {
         $count++;
         $meta=$r->get_meta();
         $row[] = new tabobject("repo-".$r->id, "index.php?id=".$course->id."&amp;repoid=".$r->id."&amp;".$link_params, $meta->name);
         if ($r->id==$repoid)
          $currenttab="repo-".$r->id;
         if ($count>3)
         {
          $rows[]=$row;
          $count=0;
          $row = array();
         }
        }

        if ($count>0)
        {
         $rows[] = $row;
         print_tabs($rows, $currenttab);
        }
        ********************************************************************************************/

        echo "<table><tr><th>".get_string("available_repo", "block_repo_filemanager").":</th>".
             "<td><form method=\"post\" action=\"index.php\" id=\"selrepo\"><div>\n".
             $form_params.
             "<input type=\"hidden\" name=\"id\" value=\"".$course->id."\" />\n".
             "<select name=\"repoid\" onchange=\"document.getElementById('selrepo').submit()\">\n";

        foreach ($repoinstances as $r)
        {
         $meta=$r->get_meta();
         if ($r->id==$repoid)
          echo "<option value=\"".$r->id."\" selected=\"selected\">".$meta->name."</option>\n";
         else
          echo "<option value=\"".$r->id."\">".$meta->name."</option>\n";
        }

        echo "</select></div>".
             "<noscript><div><input type=\"submit\" value=\"".get_string('go')."\" /></div></noscript>".
             "</form></td></tr></table>\n";
    }

    function html_footer()
    {
        global $COURSE, $OUTPUT;

        echo '</td></tr></table>';
        echo $OUTPUT->footer($COURSE);
    }


/****File Functions*******/

function setfilelist($VARS) {
    global $USER;

    $USER->filelist = array ();
    $USER->fileop = "";

    $count = 0;
    foreach ($VARS as $key => $val)
    {
        if (substr($key,0,4) == "file")
        {
            $count++;
            $val = rawurldecode($val);
            $USER->filelist[] = clean_param($val, PARAM_PATH);
        }
    }
    return $count;
}

function clearfilelist()
{
    global $USER;

    $USER->filelist = array ();
    $USER->fileop = "";
}

function printfilelist($filelist)
{
    global $CFG, $repo, $OUTPUT;

    $strfolder = get_string("folder");
    $strfile   = get_string("file");

    $paths=$repo->get_all_paths($filelist);
    foreach($paths as $file)
    {

     if ($file->dir)
      echo $OUTPUT->pix_icon('f/folder', $strfolder);
     else
      echo $OUTPUT->pix_icon('f/'.mimeinfo("icon", $file->path), $strfolder);

     echo htmlspecialchars($file->path) .'<br />';
    }
}


function print_cell($alignment='center', $text='&nbsp;', $class='cell')
{
    $class = ' class="'.$class.'"';
    echo '<td align="'.$alignment.'" style="white-space:nowrap "'.$class.'>'.$text.'</td>';
}

function displaydir ($wdir)
{
    global $repo;
    if ($repo->is_browsable())
    {
        displayfiledir($wdir);
    }
    else
    if ($repo->is_searchable())
    {
       searchabledisplay($wdir);
    }
}


function displayfiledir($wdir)
{
//  $wdir == / or /a or /a/b/c/d  etc

    global $USER, $CFG, $OUTPUT;
    global $repo, $repoid, $data_params, $form_params, $link_params, $shortpath, $hiderepolist;

    $allfiles=$repo->get_directory_listing($wdir);
    $dirlist=$allfiles->dirlist;
    $filelist=$allfiles->filelist;

    $strname = get_string("name");
    $strsize = get_string("size");
    $strmodified = get_string("modified");
    $straction = get_string("action");
    $strmakeafolder = get_string("makeafolder");
    $struploadafile = get_string("uploadafile", "block_repo_filemanager");
    $strselectall = get_string("selectall");
    $strselectnone = get_string("deselectall");
    $strwithchosenfiles = get_string("withchosenfiles");
    $strmovetoanotherfolder = get_string("movetoanotherfolder");
    $strmovefilestohere = get_string("movefilestohere");
    $strdeletecompletely = get_string("deletecompletely");
    $strcreateziparchive = get_string("createziparchive");
    $strrename = get_string("rename");
    $stredit   = get_string("edit");
    $strunzip  = get_string("unzip");
    $strlist   = get_string("list");
    $strchoose = get_string("choose");
    $strfolder = get_string("folder");
    $strfile   = get_string("file");

    if ($hiderepolist)
        echo "<br />";
    else
        print_repo_list();

    echo "<form action=\"index.php\" method=\"post\" id=\"dirform\">".
         "<div>".
         " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
         $form_params.
         "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\" class=\"generaltable\" >".
         "<tr>".
         "<th class=\"header\" scope=\"col\"></th>".
         "<th class=\"header\" scope=\"col\">$strname</th>".
         "<th class=\"header\" scope=\"col\">$strsize</th>".
         "<th class=\"header\" scope=\"col\">$strmodified</th>".
         "<th class=\"header\" scope=\"col\">$straction</th>".
         "</tr>\n";

    if ($wdir != "")
        $dirlist[] = '..';

    $count = 0;

    if (!empty($dirlist))
    {
        asort($dirlist);
        foreach ($dirlist as $dir)
        {
            echo "<tr class=\"folder\">";

            if ($dir == '..')
            {
                $fileurl=$repo->get_parent_link($wdir);
                if ($fileurl!="..")
                {
                    print_cell();
                    // alt attribute intentionally empty to prevent repetition in screen reader
                    print_cell('left', '<a href="index.php?repoid='.$repoid.'&amp;wdir='.$fileurl.'&amp;'.$link_params.'">'.
                               $OUTPUT->pix_icon('f/parent', '').
                               '&nbsp;'.get_string('parentfolder').'</a>');
                    print_cell();
                    print_cell();
                    print_cell();
                }
            }
            else
            {
                $count++;
                if ($wdir.$dir->name === '/moddata')
                    print_cell();
                else
                    print_cell("center", "<input type=\"checkbox\" name=\"file$count\" value=\"$dir->filepath\" />");

                print_cell("left", "<a href=\"index.php?repoid=$repoid&amp;wdir=$dir->filepath&amp;$link_params\">".
                           $OUTPUT->pix_icon('f/folder', $strfolder).
                           "&nbsp;".htmlspecialchars($dir->name)."</a>");
                if ($dir->filesize>0)
                   print_cell("right", display_size($dir->filesize));
                else
                   print_cell();
                if ($dir->filedate>0)
                    print_cell("right", userdate($dir->filedate, "%e %b %Y, %H:%M:%S"));
                else
                    print_cell();
                if ($wdir.$dir->name === '/moddata' || !$repo->can_manage_files())
                {
                    print_cell();
                }
                else
                {
                    print_cell("right", "<a href=\"index.php?repoid=$repoid&amp;wdir=$wdir&amp;".
                               "file=$dir->filesafe&amp;action=rename&amp;$link_params\">$strrename</a>");
                }
            }

            echo "</tr>";
        }
    }


    if (!empty($filelist))
    {
        asort($filelist);
        foreach ($filelist as $file)
        {

            $icon = mimeinfo("icon", $file->name);

            $count++;
            $selectfile = "";
            if ($shortpath)
		$selectfile = trim($file->filepath, "/");
            else
                $selectfile = trim($file->fileurl, "/");

            echo "<tr class=\"file\">";
            print_cell("center", "<input type=\"checkbox\" name=\"file$count\" value=\"$file->filepath\" />");
            print_cell("left", $OUTPUT->action_link($file->fileurl, $OUTPUT->pix_icon("f/".$icon, $strfile)."&nbsp;".htmlspecialchars($file->name)));

            if ($file->filesize>0)
                print_cell("right", display_size($file->filesize));
            else
                print_cell();

            if ($file->filedate>0)
                print_cell("right", userdate($file->filedate, "%e %b %Y, %H:%M:%S"));
            else
                print_cell();

            if (strlen($data_params['choose'])>0)
            {
                if ($data_params['choose']=="mdl2")
                {
                    $edittext = " <strong><a href=\"choosefile.php?fileurl=".$file->filepath."&amp;action=confirm&amp;".
                        "draftpath=".$data_params['draftpath']."&amp;savepath=".$data_params['savepath']."&amp;filename=".$file->filesafe.
                        "&amp;sesskey=".$USER->sesskey."&amp;repo_id=".$repoid."&amp;maxbytes=-1&amp;course=".$data_params['id'].
                        "&amp;ctx_id=".$data_params['contextid']."&amp;".
                        "&amp;env=filemanager&amp;itemid=".$data_params['itemid']."&amp;client_id=".$data_params['client_id']."\">".
                        $strchoose."</a></strong>&nbsp;";

                }
                else
                    $edittext = " <strong><a onclick=\"return set_value('$selectfile')\" href=\"#\">$strchoose</a></strong>&nbsp;";
            }
            else
            {
                $edittext = '';
            }

            if ( is_editable_type($file->name) && $repo->can_edit())
            {
                $edittext .= " <a href=\"index.php?repoid=$repoid&amp;wdir=$wdir&amp;file=$file->filepath&amp;action=edit&amp;$link_params\">$stredit</a>";
            }
            else if (is_zipfile($file->name) && $repo->can_zip())
            {
                $edittext .= " <a href=\"index.php?repoid=$repoid&amp;wdir=$wdir&amp;file=$file->filepath&amp;action=unzip&amp;sesskey=$USER->sesskey&amp;$link_params\">$strunzip</a>&nbsp;";
                $edittext .= " <a href=\"index.php?repoid=$repoid&amp;wdir=$wdir&amp;file=$file->filepath&amp;action=listzip&amp;sesskey=$USER->sesskey&amp;$link_params\">$strlist</a> ";
            }
            if ($repo->can_manage_files())
            {
                $edittext.= " <a href=\"index.php?repoid=$repoid&amp;wdir=$wdir&amp;file=$file->filesafe&amp;action=rename&amp;$link_params\">$strrename</a>";
            }
            print_cell("right", $edittext);

            echo "</tr>";
        }
    }

    echo "</table>".
         "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\">".
         "<tr><td>".
         $form_params.
         "<input type=\"hidden\" name=\"wdir\" value=\"$wdir\" /> ".
         "<input type=\"hidden\" name=\"repoid\" value=\"$repoid\" /> ".
         "<input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />";
    /**
    $options = array (
                   "move" => "$strmovetoanotherfolder",
                   "delete" => "$strdeletecompletely",
                   "zip" => "$strcreateziparchive"
               );
    **/
    if (!empty($count))
    {

        //choose_from_menu ($options, "action", "", "$strwithchosenfiles...", "javascript:getElementById('dirform').submit()");
        //echo html_writer::select($options, "action", "", "$strwithchosenfiles...");

        /***I can't work out how to get the html_writer to auto-submit the form, so write this manually for now***/
        if ($repo->can_manage_files())
        {
            echo '<select name="action" onchange="javascript:getElementById(\'dirform\').submit()">'.
                 '<option selected="selected">'.$strwithchosenfiles.'...</option>'.
                 '<option value="move">'.$strmovetoanotherfolder.'</option>'.
                 '<option value="delete">'.$strdeletecompletely.'</option>';

            if ($repo->can_zip())
                echo '<option value="zip">'.$strcreateziparchive.'</option>';

            echo '</select>';
        }
        echo '<div id="noscriptgo" style="display: inline;">'.
             '<input type="submit" value="'.get_string('go').'" />'.
             '<script type="text/javascript">'.
               "\n//<![CDATA[\n".
               'document.getElementById("noscriptgo").style.display = "none";'.
               "\n//]]>\n".'</script>'.
             '</div>';

    }

    echo "</td></tr></table>".
         "</div>".
         "</form>".
         "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\"><tr>".
         "<td align=\"center\">";

    if (!empty($USER->fileop) and ($USER->fileop == "move") and ($USER->filesource <> $wdir))
    {
        echo "<form action=\"index.php\" method=\"get\">".
         "<div>".
         " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
         $form_params.
         " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
         " <input type=\"hidden\" name=\"action\" value=\"paste\" />".
         " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />".
         " <input type=\"submit\" value=\"$strmovefilestohere\" />".
         "</div>".
         "</form>";
    }

    echo "</td>";

    if ($repo->can_manage_files())
    {
        echo "<td align=\"right\">".
         "<form action=\"index.php\" method=\"get\">".
         "<div>".
         " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
         $form_params.
         " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
         " <input type=\"hidden\" name=\"action\" value=\"makedir\" />".
         " <input type=\"submit\" value=\"$strmakeafolder\" />".
         "</div>".
         "</form>".
         "</td>".
         "<td align=\"right\">".
         "<form action=\"index.php\" method=\"get\">".
         "<fieldset class=\"invisiblefieldset\">".
         " <input type=\"button\" value=\"$strselectall\" onclick=\"checkall();\" />".
         " <input type=\"button\" value=\"$strselectnone\" onclick=\"checknone();\" />".
         "</fieldset>".
         "</form>".
         "</td>".
         "<td align=\"right\">".
         "<form action=\"index.php\" method=\"get\">".
         "<div>".
         " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
         $form_params.
         " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />".
         " <input type=\"hidden\" name=\"action\" value=\"upload\" />".
         " <input type=\"submit\" value=\"$struploadafile\" />".
         "</div>".
         "</form>".
         "</td>";
    }
    else
    {
        echo "<td align=\"right\" colspan=\"3\">".
             "</td>";
    }

    echo "</tr>".
         "</table>";
}


/**
* Checks to see if the file is a type which can be edited
**/

function is_editable_type($file)
{
 $file=strtolower($file);
 if (strpos($file, ".html")>0 || strpos($file, ".htm")>0 || strpos($file, ".txt")>0)
     return true;

 return false;
}

/**
* Checks to see if this is a zip file
**/

function is_zipfile($file)
{
 $file=strtolower($file);
 if (strpos($file, ".zip")>0)
     return true;

 return false;
}

/**
 * This function prints out a number of upload form elements.
 *
 * @param int $numfiles The number of elements required (optional, defaults to 1)
 * @param array $names Array of element names to use (optional, defaults to FILE_n)
 * @param array $descriptions Array of strings to be printed out before each file bit.
 * @param boolean $uselabels -Whether to output text fields for file descriptions or not (optional, defaults to false)
 * @param array $labelnames Array of element names to use for labels (optional, defaults to LABEL_n)
 * @param int $coursebytes $coursebytes and $maxbytes are used to calculate upload max size ( using {@link get_max_upload_file_size})
 * @param int $modbytes $coursebytes and $maxbytes are used to calculate upload max size ( using {@link get_max_upload_file_size})
 * @param boolean $return -Whether to return the string (defaults to false - string is echoed)
 * @return string Form returned as string if $return is true
 */ 
function upload_print_form_fragment_old($numfiles=1, $names=null, $descriptions=null, $uselabels=false, $labelnames=null, $coursebytes=0, $modbytes=0, $return=false) {
    global $CFG;
    $maxbytes = get_max_upload_file_size($CFG->maxbytes, $coursebytes, $modbytes);
    $str = '<input type="hidden" name="MAX_FILE_SIZE" value="'. $maxbytes .'" />'."\n";
    for ($i = 0; $i < $numfiles; $i++)
    {
        if (is_array($descriptions) && !empty($descriptions[$i]))
        {
             $str .= '<strong>'. $descriptions[$i] .'</strong><br />';
        }
        $name = ((is_array($names) && !empty($names[$i])) ? $names[$i] : 'FILE_'.$i);
        $str .= '<input type="file" size="50" name="'. $name .'" alt="'. $name .'" /><br />'."\n";
        if ($uselabels)
        {
            $lname = ((is_array($labelnames) && !empty($labelnames[$i])) ? $labelnames[$i] : 'LABEL_'.$i);
            $str .= get_string('uploadlabel').' <input type="text" size="50" name="'. $lname .'" alt="'. $lname
                .'" /><br /><br />'."\n";
        }
    }
    if ($return)
        return $str;
    else
        echo $str;
}

function searchabledisplay($wdir)
{
    global $repo, $form_params, $repoid, $data_params, $USER;

    $repo->search("trains");
    $allfiles=$repo->get_directory_listing('');
    //$dirlist=$allfiles->dirlist;
    $filelist=$allfiles->filelist;

    $strchoose = get_string("choose");

    echo "<form action=\"index.php\" method=\"get\">".
         "<div style=\"text-align:center\">".
         " <input type=\"hidden\" name=\"repoid\" value=\"".$repoid."\" />".
         $form_params.
         " <input type=\"text\" name=\"".$repo->is_searchable()."\" value=\"\" />".
         " <input type=\"submit\" value=\"".get_string("search")."\" />".
         "</div>".
         "</form><br />";

    echo "<table><tr>\n";
    $loop=0;

    foreach ($filelist as $file)
    {
        echo "<td style=\"width:".$file->thumbnailw."px;vertical-align:top;text-align:center;\">".
         "<img src=\"".$file->thumbnail."\" width=\"".$file->thumbnailw."\" height=\"".$file->thumbnailh."\" alt=\"\" />\n".
         "<br />".$file->name."\n";

       if ($data_params->choose)
        echo "<form action=\"choosefile.php\" method=\"post\"><div>\n".
         "<input type=\"hidden\" name=\"fileurl\" value=\"".$file->filepath."\" />\n".
         "<input type=\"hidden\" name=\"action\" value=\"confirm\" />\n".
         "<input type=\"hidden\" name=\"draftpath\" value=\"".$data_params['draftpath']."\" />\n".
         "<input type=\"hidden\" name=\"savepath\" value=\"".$data_params['savepath']."\" />\n".
         "<input type=\"hidden\" name=\"filename\" value=\"".$file->filesafe."\" />\n".
         "<input type=\"hidden\" name=\"sesskey\" value=\"".$USER->sesskey."\" />\n".
         "<input type=\"hidden\" name=\"repo_id\" value=\"".$repoid."\" />\n".
         "<input type=\"hidden\" name=\"maxbytes\" value=\"-1\" />\n".
         "<input type=\"hidden\" name=\"course\" value=\"".$data_params['id']."\" />\n".
         "<input type=\"hidden\" name=\"ctx_id\" value=\"".$data_params['contextid']."\" />\n".
         "<input type=\"hidden\" name=\"env\" value=\"filemanager\" />\n".
         "<input type=\"hidden\" name=\"itemid\" value=\"".$data_params['itemid']."\" />\n".
         "<input type=\"hidden\" name=\"client_id\" value=\"".$data_params['client_id']."\" />\n".
         "<input type=\"submit\" value=\"".$strchoose."\" />\n".
         "</div></form>\n";

        echo "</td>\n";

        $loop++;
        if ($loop>3)
        {
            echo "</tr><tr>";
            $loop=0;
        }
    }

    echo "</tr></table>";
}
?>
