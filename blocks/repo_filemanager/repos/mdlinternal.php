<?php

require_once('generic.php');

/**
* Generic implementation for a remote manageable Moodle repository type, this will translate the json strings into
* the expected format used by the repo file manager and allow all repositories on the system to be browsed
* @author Tim Williams (tmw@autotrain.org) for EA LLP 2011
* @licence GPL v3
**/

class block_repofile_mdlinternal extends block_repofile_generic
{

 /**
 * Returns true if the repository type can :
 * - Move files
 * - Delete files
 * - Make new directories
 * - Accept file uploads
 **/

 public function can_manage_files()
 {
  return true;
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
 * @param dir The directory containing the file
 **/

 public function rename_file($oldname, $newname, $dir)
 {
  echo $oldname." ".$newname;
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

}
?> 
