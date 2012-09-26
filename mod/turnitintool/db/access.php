<?php
/**
 * @package   turnitintool
 * @copyright 2010 iParadigms LLC
 */



$capabilities = array(

    'mod/turnitintool:view' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'guest' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        )
    ),

    'mod/turnitintool:submit' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'student' => CAP_ALLOW
        )
    ),

    'mod/turnitintool:grade' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        )
    )
);

// Check for version older than 2.0
if (!is_callable('upgrade_plugins_modules')) {
	$mod_turnitintool_capabilities = $capabilities;
}



/* ?> */