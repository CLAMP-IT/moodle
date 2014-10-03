<?php

require_once('generic.php');

/**
* Generic implementation for a remote manageable Moodle repository type, this will translate the json strings into
* the expected format used by the repo file manager and allow all repositories on the system to be browsed
* @author Tim Williams (tmw@autotrain.org) for EA LLP 2010
* @licence GPL v3
**/

class block_repofile_filesystem extends block_repofile_generic
{

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
  global $COURSE, $CFG;
  $entry=new stdclass();
  $entry->name=$item['title'];
  $entry->filedate=$item['date'];

  //$uns=unserialize(base64_decode($item['source']));
  //foreach ($uns as $key => $value)
  // echo $key.':'.$value.'<br />';

  $entry->fileurl=$CFG->wwwroot."/blocks/repo_filemanager/index.php?id=".$COURSE->id."&noview=1&repoid=".$this->baserepo->id;
  $entry->filepath=$item['source'];
  $entry->filesafe=rawurlencode($item['title']);
  $entry->filesize = $item['size'];

  return $entry;
 }

}
?> 
