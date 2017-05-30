<?php

defined('MOODLE_INTERNAL') || die;

$functions = [
    'local_tophat_echo_message' => array(
        'classname'     => 'local_tophat_external',
        'methodname'    => 'echo_message',
        'description'   => 'echo',
        'type'          => 'read'
    ),
    'local_tophat_get_enrolled_users' => array(
        'classname'     => 'local_tophat_external',
        'methodname'    => 'get_enrolled_users',
        'description'   => 'Get enrolled students for given course',
        'type'          => 'read',
        'capabilities' => 'moodle/user:viewdetails, moodle/user:viewhiddendetails, moodle/course:useremail, moodle/user:update, moodle/site:accessallgroups'
    ),
    'local_tophat_get_courses' => array(
        'classname'     => 'local_tophat_external',
        'methodname'    => 'get_courses',
        'description'   => 'Get courses which given user has teacher role',
        'type'          => 'read',
        'capabilities' => ' moodle/course:view,moodle/course:update,moodle/course:viewhiddencourses'
    ),
    'local_tophat_update_grade' => array(
        'classname'     => 'local_tophat_external',
        'methodname'    => 'update_grade',
        'description'   => 'Update student grade',
        'type'          => 'write'
    ),
    'local_tophat_create_grade_item' => array(
        'classname'     => 'local_tophat_external',
        'methodname'    => 'create_grade_item',
        'description'   => 'Create grade item',
        'type'          => 'write'
    ),
    'local_tophat_get_grade_item' => array(
        'classname'     => 'local_tophat_external',
        'methodname'    => 'get_grade_item',
        'description'   => 'Retrive grade item',
        'type'          => 'read'
    ),
    'local_tophat_delete_grade_item' => array(
        'classname'     => 'local_tophat_external',
        'methodname'    => 'delete_grade_item',
        'description'   => 'Delete grade item',
        'type'          => 'write'
    ),
    'local_tophat_create_grade_category' => array(
        'classname'     => 'local_tophat_external',
        'methodname'    => 'create_grade_category',
        'description'   => 'Create grade category',
        'type'          => 'write'
    ),
    'local_tophat_delete_grade_category' => array(
        'classname'     => 'local_tophat_external',
        'methodname'    => 'delete_grade_category',
        'description'   => 'Delete grade category',
        'type'          => 'write'
    ),
    'local_tophat_validate_token' => array(
        'classname'     => 'local_tophat_external',
        'methodname'    => 'validate_token',
        'description'   => 'Validate user token',
        'type'          => 'read'
    ),
];
