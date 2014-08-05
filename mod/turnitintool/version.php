<?php
/**
 * @package   turnitintool
 * @copyright 2012 Turnitin
 */
if(!isset($module)){
	$module = new stdClass();
}
$module->version  = 2013111403;  // The current module version (Date: YYYYMMDDXX)
$module->cron     = 1800;        // Period for cron to check this module (secs)
//$module->requires = 2007101509;  // 1.9+
$module->requires = 2010112400;  // 2.0+

/* ?> */
