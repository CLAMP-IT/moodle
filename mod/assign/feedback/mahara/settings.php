<?php

$settings->add(new admin_setting_configcheckbox(
    'assignfeedback_mahara/default',
    new lang_string('default', 'assignfeedback_mahara'),
    new lang_string('default_help', 'assignfeedback_mahara'),
    1
));
