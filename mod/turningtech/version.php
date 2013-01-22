<?php // $Id: version.php,v 1.6 2009/02/27 15:54:02 stronk7 Exp $

/**
 * Code fragment to define the version of newmodule
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author
 * @version $Id: version.php,v 1.6 2009/02/27 15:54:02 stronk7 Exp $
 * @package newmodule
 **/
$module->version  = 2012050220; // The current module version (Date: YYYYMMDDXX)
$module->cron     = 0; // Period for cron to check this module (secs)
//$module->cron     = 60 * 60 * 24 * 7;       // run cron every week
$module->requires = 2012061200; // Requires this Moodle version
