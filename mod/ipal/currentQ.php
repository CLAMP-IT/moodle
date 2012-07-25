<?php
include '../../config.php';
global $DB;
$ipalid=$_GET['ipalid'];
          if($DB->record_exists('ipal_active_questions', array('ipal_id'=>$ipalid))){
          $question=$DB->get_record('ipal_active_questions', array('ipal_id'=>$ipalid));
          //echo $question->question_id;
          echo $question->id;
          }
?>
