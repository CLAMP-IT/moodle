<?php 
include_once('../../config.php');
include $CFG->dirroot.'/lib/graphlib.php'; 
$line = new graph(700,500); 
$line->parameter['title']   = ''; 
$line->parameter['y_label_left'] = 'Number of Responses'; 
$line->x_data=explode(",",$_GET["labels"]);
$line->y_data['responses'] = explode(",",$_GET["data"]);
$line->y_format['responses'] =array('colour' => 'blue', 'bar' => 'fill', 'shadow_offset' => 3); 
$line->y_order = array('responses');
$line->parameter['y_min_left'] = 0; 
$line->parameter['y_max_left'] = $_GET["total"];  
$line->parameter['y_decimal_left'] = 0;  
$line->draw(); 
?>






