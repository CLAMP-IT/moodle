<?php
//https://docs.moodle.org/dev/NEWMODULE_Adding_capabilities
$capabilities = array(
		'block/annotate:addinstance' => array(
				'riskbitmask' => RISK_SPAM | RISK_XSS,

				'captype' => 'write',
				'contextlevel' => CONTEXT_BLOCK,
				'archetypes' => array(
						'editingteacher' => CAP_ALLOW,
						'manager' => CAP_ALLOW
				),

				'clonepermissionsfrom' => 'moodle/site:manageblocks'
		),
);