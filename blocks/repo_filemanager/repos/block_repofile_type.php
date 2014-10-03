<?php

global $CFG;
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/adminlib.php');

/**
* Base class for a remote manageable Moodle repository type
* @author Tim Williams (tmw@autotrain.org) for EA LLP 2010
* @licence GPL v3
**/

class block_repofile_type
{

 /**
 * Recursively gets all the subfiles and directories represented by the initial list of files
 * @return all the files
 **/

 public function get_all_paths($filelist, $level=0)
 {
  return array();
 }

 public function get_parent_link($wdir)
 {
  return "";
 }

 public function get_nav_links($wdir, $choose)
 {
  return array();
 }

 public function get_directory_listing($wdir)
 {
  $data=new stdclass();
  $data->dirlist=array();
  $data->filelist=array();
  return $data;
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
  return false;
 }

 public function is_searchable()
 {
  return false;
 }

 public function search($st)
 {

 }
}
?> 
