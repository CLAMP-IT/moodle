<?php

    require_once("../../config.php");
    require_once($CFG->libdir.'/adminlib.php');
	
    admin_externalpage_setup('poodlldiagnostics');

	//detect if its a CSV or not
	$exportcsv = optional_param('csv',0,PARAM_INT);
	
	//get diagnostics info
	$diagnostics = new \filter_poodll\diagnosticstools();
	$dprops = $diagnostics->fetch_props();

	//if we are exporting CSV, do that
	if($exportcsv){
		header("Content-Disposition: attachment; filename=poodll_support_info.csv");
		header("Content-Type: text/comma-separated-values");
		$quote = '"';
		$delim= ",";
		$newline = "\r\n";
		//echo data rows
		$datarow = '';
        foreach ($dprops as $propname=>$propvalue) {
			$datarow .= $quote . $propname . $quote . $delim . $quote . $propvalue . $quote . $newline ;
		}
		echo $datarow;
        exit();
	}
	
	//if we are exporting html, do that
    echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('poodllsupportinfo','filter_poodll'),3);
	
	$csvexportbutton = new single_button(
				new moodle_url('/filter/poodll/poodlldiagnostics.php',array('csv'=>1)), 
				get_string('exportdiagnostics','filter_poodll'));
	
    echo '<div class="filter_poodll_diagnostics">';
	echo $OUTPUT->render($csvexportbutton);
	
	//make html table
	$table = new html_table();
    $table->head  = array(get_string('name'),get_string('value','filter_poodll'));
    $table->colclasses = array('leftalign', 'leftalign');
    $table->id = 'filter_poodll_diag_props';
    $table->attributes['class'] = 'admintable generaltable';
    $table->data  = array();
	//add a row for each item	
	foreach ($dprops as $propname=>$propvalue) {
    	$table->data[] = array($propname, $propvalue);
	}
	//display it
	echo html_writer::table($table);
   
	echo $OUTPUT->render($csvexportbutton);
	echo '</div>';

    echo $OUTPUT->footer();