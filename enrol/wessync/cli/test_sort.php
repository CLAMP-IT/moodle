<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

global $DB;
$cat = $DB->get_record('course_categories', array ('name' => 'Fall 2012'));
$courses = $DB->get_records('course', array('category'=>$cat->id),'shortname ASC, id DESC','id, sortorder');
$i = 1;
foreach ($courses as $course) {
     if ($course->sortorder != $cat->sortorder + $i) {
                $course->sortorder = $cat->sortorder + $i;
                $DB->update_record_raw('course', $course, true);
            }
            $i++;
        }

