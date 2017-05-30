<?php

defined('MOODLE_INTERNAL') || die();

function local_tophat_has_role($roles, $userid, $context) {
    $hasrole = false;
    foreach ($roles as $roleid) {
        $hasrole = user_has_role_assignment($userid, $roleid, $context->id);
        if ($hasrole) {
            return true;
        }
    }
    return false;
}

function local_tophat_get_roleids($rolesnames) {
    global $DB;

    if (empty($rolesnames)) {
        throw new moodle_exception('emptyrolenames', 'local_tophat');
    }

    list($sqlfragment, $fragmentparams) = $DB->get_in_or_equal($rolesnames, SQL_PARAMS_NAMED);

    $sql = "SELECT id
              FROM {role} r
             WHERE r.archetype $sqlfragment";

    $rolesid = $DB->get_fieldset_sql($sql, $fragmentparams);
    return $rolesid;
}

function local_tophat_get_teacher_roleids($customroles = null) {
    $rolesnames = [
        'editingteacher',
        'teacher',
        'manager'
    ];
    if (!empty($customroles)) {
        $customroles = array_unique(explode(',', $customroles), SORT_REGULAR);
        $rolesnames = array_map(function ($rolename) {
            return clean_param($rolename, PARAM_ALPHANUMEXT);
        }, $customroles);
    }
    return local_tophat_get_roleids($rolesnames);
}
