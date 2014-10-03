<?php

require_once('block_repofile_type.php');

/**
* Generic implementation for a remote manageable Moodle repository type, this will translate the json strings into
* the expected format used by the repo file manager and allow all repositories on the system to be browsed
* @author Tim Williams (tmw@autotrain.org) for EA LLP 2010
* @licence GPL v3
**/

class block_repofile_generic extends block_repofile_type
{
 var $baserepo;
 var $lastref;
 var $cacheddata;

 public function __construct($base)
 {
  $this->baserepo=$base;
 }

 /**
 * Recursively gets all the subfiles and directories represented by the initial list of files
 * @return all the files
 **/

 public function get_all_paths($filelist, $level=0)
 {
  //foreach($filelist as $f)
  //{
  // echo $f;
  //}

  return array();
 }

 private function get_list_data($ref)
 {
  if ($ref==$this->lastref && !empty($this->cacheddata))
   return $this->cacheddata;

  $this->cacheddata=$this->baserepo->get_listing($ref);
  return $this->cacheddata;
 }

 public function get_parent_link($wdir)
 {
  $rpdata=$this->get_list_data($wdir);
  $index=count($rpdata['path'])-2;
  if ($index>-1)
   return $rpdata['path'][$index]['path'];
  else
   return "..";
 }

 public function get_nav_links($wdir, $choose)
 {
  global $COURSE;
  $rpdata=$this->get_list_data($wdir);

  /***Moodle sometimes returns the same path for index 0 and index 1, when index 1 should point at the default
      page, detect this here and set the path for index 1 to an empty string if the paths match***/
  //if ($rpdata['path'][0]['path']==$rpdata['path'][1]['path'])
  // $rpdata['path'][1]['path']="";
  /*
  foreach ($rpdata['path'] as $keya => $valuea)
  {
      echo "---->".$keya."<br />";
      foreach ($valuea as $keyb => $valueb)
          echo $keyb.':'.$valueb.'<br />';
      echo '<br /><br />';
  }
  */
  $navlinks = array();

  if (!isset($rpdata['path']))
   return $navlinks;

  foreach ($rpdata['path'] as $key => $value)
  {
   $link = $value['path'];
   $navlinks[] = array('name' => $value['name'],
   'link' => "?id=$COURSE->id&amp;choose=$choose&amp;wdir=$link",
   'type' => 'misc');
  }

  return $navlinks;
 }

 public function get_directory_listing($wdir)
 {
  global $CFG;
  //$this->get_path($wdir);
  //echo '<br /><br />';
  
  $rpdata=$this->get_list_data($wdir);

  //foreach ($rpdata as $key => $value)
  //    echo $key.':'.$value.'<br />';

  //echo '<br /><br />';


  $data=new stdclass();
  $data->dirlist=array();
  $data->filelist=array();

  foreach ($rpdata['list'] as $item)
  {
   //foreach ($item as $key => $value)
   // echo $key.':'.$value.'<br />';

   //foreach ($item['children'] as $key => $value)
   // echo $key.':'.$value.'<br />';

   if (array_key_exists('path', $item) && $item['path'])
    $data->dirlist[]=$this->process_directory_direnntry($item);
   else
    $data->filelist[]=$this->process_directory_fileentry($item);

   //echo '<br />';
  }

  return $data;
 }

 public function process_directory_direnntry($item)
 {
  $entry=new stdclass();
  $entry->name=$item['title'];
  $entry->filedate=$item['date'];
  $uns=unserialize(base64_decode($item['path']));
  $entry->filesafe=rawurlencode($item['title']);
  $entry->filesize=-1;
  $entry->filepath=$item['path'];

  return $entry;
 }

 public function process_directory_fileentry($item)
 {
  $entry=new stdclass();
  $entry->name=$item['title'];
  $entry->filedate=$item['date'];

  $uns=unserialize(base64_decode($item['source']));
  //foreach ($uns as $key => $value)
  // echo $key.':'.$value.'<br />';
  //echo "<br />";

  if ($uns['filearea']=="private")
      $entry->fileurl=$CFG->wwwroot."/pluginfile.php/".$uns['contextid']."/".$uns['component']."/".$uns['filearea']."/".rawurlencode($uns['filename']);
  else
      $entry->fileurl=$CFG->wwwroot."/pluginfile.php/".$uns['contextid']."/".$uns['component']."/".$uns['filearea']."/".$uns['itemid']."/".rawurlencode($uns['filename']);

  $entry->filepath=$item['source'];
  $entry->filesafe=rawurlencode($item['title']);
  $entry->filesize = $item['size'];

  /***Some of the repos don't return the file size/date, so try to get it straight from the database***/
  if ($item['size']==0 || empty($item['date']) &&
     (isset($uns['contextid']) && isset($uns['filearea']) && isset($uns['component'])))
  {
   global $DB;
   $data=array(
    "contextid"=>$uns['contextid'],
    "filearea"=>$uns['filearea'],
    "component"=>$uns['component'],
    "filename"=>$uns['filename'],
    "filepath"=>$uns['filepath']
   );
   $rec=$DB->get_record("files", $data);
   if ($rec)
   {
    $entry->filesize = $rec->filesize;
    $entry->filedate=$rec->timemodified;
   }
  }
  return $entry;
 }

 private function get_path_data($wdir)
 {
  $uns=unserialize(base64_decode($wdir));
  
  //foreach ($uns as $key => $value)
  // echo $key.':'.$value.'<br />';
  //echo '<hr />';
  return $uns['filepath'];
 }

 /**
 * Returns true if the repository type can :
 * - Move files
 * - Delete files
 * - Make new directories
 * - Accept file uploads
 **/

 public function can_manage_files()
 {
  return false;
 }

 /**
 * Returns true if the repository can provide zip/unzip capabilities
 **/

 public function can_zip()
 {
  return false;
 }

 /**
 * Returns true if the repository allows text content to be retrieved, edited and saved
 **/

 public function can_edit()
 {
  return false;
 }

 /**
 * Deletes the specified files from the repository
 * @param filelist An array of file paths
 * @return Any error messages
 **/

 public function delete_files($filelist)
 {
  return "";
 }

 /**
 * Moves the specified files in repository
 * @param filelist An array of file paths
 * @param dest The destination folder
 * @return Any error messages
 **/

 public function move_files($filelist, $dest)
 {
  return "";
 }

 /**
 * renames the specified file
 * @param oldfile The old file name
 * @param newname The new file name
 * @param dir The directory or path reference for the file
 **/

 public function rename_file($oldname, $newname, $dir)
 {
  return "";
 }

 /**
 * Makes a new direcrory inside the repository
 * @param folder The directory in which the new folder is to be make
 * @param name The name of the new directory
 **/

 public function mkdir($folder, $name)
 {
  return "";
 }

 /**
 * Uploads a file to the specified destination directory
 * @param destdir The destination
 **/

 public function upload($destdir)
 {
  return "";
 }

 /**
 * Gets the maximum number of bytes that can be uploaded to this repository
 **/

 public function max_upload_bytes()
 {
  return 0;
 }

 /**
 * Saves an edited text file to the given file name in the repository
 **/

 public function save_text($file, $text)
 {
 }

 /**
 * Gets a text file from the repository for editing
 **/

 public function get_text($file)
 {
  return "";
 }

 public function zip_files($filelist, $zipfile)
 {
  return "";
 }

 public function unzip_file($file)
 {
  return "";
 }

 public function list_zip($file)
 {
  return array();
 }

 public function is_browsable()
 {
  return true;
 }
}
?> 
