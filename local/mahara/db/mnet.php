<?php
defined('MOODLE_INTERNAL') || die();

$publishes = array(
    'mahara' => array(
        'apiversion' => 1,
        'classname'  => 'mahara_mnetservice',
        'filename'   => 'mnetlib.php',
        'methods'    => array(
            'donothing',
        ),
    ),
);

$subscribes = array(
    'mahara' => array(
        'get_views_for_user' => 'mod/mahara/rpclib.php/get_views_for_user',
        'submit_view_for_assignment' => 'mod/mahara/rpclib.php/submit_view_for_assessment',
        'release_submitted_view' => 'mod/mahara/rpclib.php/release_submitted_view',
        'get_groups_for_user' => 'mod/mahara/rpclib.php/get_groups_for_user',
        'get_notifications_for_user' => 'mod/mahara/rpclib.php/get_notifications_for_user',
        'get_watchlist_for_user' => 'mod/mahara/rpclib.php/get_watchlist_for_user',
    ),
);
