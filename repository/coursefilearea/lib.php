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

//global $CFG;
//require_once("$CFG->libdir/filelib.php");

if ($CFG->version >= 2012061800)
 require_once($CFG->dirroot."/repository/coursefilearea/lib23.php");
else
 require_once($CFG->dirroot."/repository/coursefilearea/lib20.php");

/**
 * repository_coursefilearea class is used to browse course files. This is a composite of the coursefiles and
 * filesystem repository code with some new code added.
 *
 * @since 2.0
 * @package contrib
 * @subpackage repository
 * @copyright 2011 Tim Williams <tmw@autotrain.org> (Modified from code originally written by Dongsheng Cai <dongsheng@moodle.com>)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class repository_coursefilearea extends repository_coursefilearea_abs
{

    /**
     * Initialize recent plugin
     * @param int $repositoryid
     * @param int $context
     * @param array $options
     */

    public static function get_type_option_names()
    {
        return array('allowinternal', 'pluginname');
    }

    private function get_course()
    {
      global $CFG;
      //For Moodle 2.2 to 2.2.3+
      if ($CFG->version > 2011120500 && $CFG->version < 2011120503.06)
      {
       global $USER, $DB;
       $cid=array_keys($USER->currentcourseaccess);
       $cid=$cid[0];
       return $DB->get_record("course", array("id"=>$cid));
      }

      global $COURSE;
      return $COURSE;
    }

    //public static function type_config_form($mform)
    public static function type_config_form_real($mform)
    {
        repository::type_config_form($mform);
        $allowinternal = get_config('coursefilearea', 'allowinternal');
        if (empty($allowinternal))
            $allowinternal=0;

        $desc='<p>'.get_string('allowinternaldesc', 'repository_coursefilearea').'</p>';
        if (repository_coursefilearea::is_patched())
            $desc.='<p class="warning">'.get_string('redirectpatchdetected', 'repository_coursefilearea').'</p>';
        else
            $desc.='<p>'.get_string('redirectpatchnotdetected', 'repository_coursefilearea').'</p>';

        $mform->addElement('checkbox', 'allowinternal', get_string('allowinternaltitle', 'repository_coursefilearea'), 
            $desc);
    }

    /**
     * course file area plugin doesn't require login, so list all files
     * @return mixed
     */
    public function print_login()
    {
        return $this->get_listing();
    }

    /**
     * Get file listing
     *
     * @param string $encodedpath
     * @return mixed
     */
    public function get_listing($path = '', $page = '')
    {

        global $CFG, $USER, $OUTPUT;
        $courseid=$this->get_course()->id;

        $list = array();
        $list['list'] = array();
        // process breacrumb trail
        $list['path'] = array(
            array('name'=>'Root', 'path'=>'')
        );
        $trail = '';
        if (!empty($path))
        {
            $parts = explode('/', $path);
            if (count($parts) > 1)
            {
                foreach ($parts as $part)
                {
                    if (!empty($part))
                    {
                        $trail .= ('/'.$part);
                        $list['path'][] = array('name'=>$part, 'path'=>$trail);
                    }
                }
            }
            else
            {
                $list['path'][] = array('name'=>$path, 'path'=>$path);
            }
        }
        $list['manage'] = false;
        $list['dynload'] = true;
        $list['nologin'] = true;
        $list['nosearch'] = true;

        $root_path=$CFG->dataroot."/".$courseid."/".$path."/";

        if (!file_exists($root_path))
            mkdir($root_path, $CFG->directorypermissions);

        if ($dh = opendir($root_path))
        {
            while (($file = readdir($dh)) != false)
            {
                if ( $file != '.' and $file !='..')
                {
                    if (filetype($root_path.$file) == 'file')
                    {
                        $list['list'][] = array(
                            'title' => $file,
                            'source' => $path.'/'.$file,
                            'size' => filesize($root_path.$file),
                            'date' => filemtime($root_path.$file),
                            'thumbnail' => $OUTPUT->pix_url(file_extension_icon($root_path.$file, 32))->out(false)
                        );
                    }
                    else
                    {
                        if (!empty($path))
                        {
                            $current_path = $path . '/'. $file;
                        }
                        else
                        {
                            $current_path = $file;
                        }
                        $list['list'][] = array(
                            'title' => $file,
                            'children' => array(),
                            'thumbnail' => $OUTPUT->pix_url('f/folder-32')->out(false),
                            'path' => $current_path
                            );
                    }
                }
            }
        }
        $list['list'] = array_filter($list['list'], array($this, 'filter'));
        return $list;
    }

    public function get_link($info)
    {
        global $CFG;
		$course=$this->get_course();
        if (strpos($info, "/")!=0)
            $info="/".$info;
        if ($CFG->slasharguments)
            return file_encode_url($CFG->wwwroot, "/repository/coursefilearea/file.php/".$course->id.$info);
        else
            return $CFG->wwwroot."/repository/coursefilearea/file.php?file=/".$this->checkURL($course->id.$info);
    }

    public function checkURL($u)
    {
        if (!strpos($u, "/"))
            return rawurlencode($u);

        $all=explode("/", $u);
  
        $f="";
        foreach($all as $e)
        {
            $f=$f.rawurlencode($e)."/";
        }
        return substr($f, 0, strlen($f)-1);
    }

    /**
     * course files area only uses external links, all files need to be live
     *
     * @return int
     */

    public function supported_returntypes()
    {
        if (get_config('coursefilearea', 'allowinternal'))
            return (FILE_INTERNAL | FILE_EXTERNAL);
        else
            return FILE_EXTERNAL;
    }

    /**
     * Links or copies the file to the main file store
     *
     * @global object $CFG
     * @param string $url the url of file
     * @param string $filename save location
     * @return string the location of the file
     * @see curl package
     */
    public function get_file($url, $filename = '')
    {
        global $CFG;
		$course=$this->get_course();
        $link = $this->prepare_file($filename);
        if (strpos($url, "/")>0)
            $url="/".$url;

        $target='';

        if (repository_coursefilearea::is_patched())
        {
         $target=$CFG->dataroot.'/'.$course->id."/".$url;
         $h=fopen($link, 'w');

         fwrite($h, $target);
         fclose($h);
        }
        else
        {
         $target=$CFG->dataroot.'/'.$course->id."/".$url;
         copy($target, $link);
        }

        return array('path'=>$link, 'url'=>"coursefilearea;".$target);
    }

    public static function is_patched()
    {
        if (property_exists("stored_file", "HAS_COURSEFILEAREA_PATCH") &&
            stored_file::$HAS_COURSEFILEAREA_PATCH==true)
            return true;

        return false;
    }
}
