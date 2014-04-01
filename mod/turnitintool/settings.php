<?php
/**
 * @package   turnitintool
 * @copyright 2012 Turnitin
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    require_once($CFG->dirroot.'/mod/turnitintool/lib.php');
    require_once($CFG->dirroot.'/mod/turnitintool/version.php');

    $upgrade = turnitintool_updateavailable( $module );
    $upgradeavailable = ( is_null( $upgrade ) ) ? '' : ' <a href="'.$upgrade.'"><i><b>'.get_string('upgradeavailable','turnitintool').'</b></i></a> ';

    $toplinks = '<div><a href="'.$CFG->wwwroot.'/mod/turnitintool/extras.php">'.get_string("connecttest", "turnitintool")
                .'</a> | <a href="'.$CFG->wwwroot.'/mod/turnitintool/extras.php?do=viewreport">'.get_string("showusage", "turnitintool")
                .'</a> | <a href="'.$CFG->wwwroot.'/mod/turnitintool/extras.php?do=savereport">'.get_string("saveusage", "turnitintool")
                .'</a> | <a href="'.$CFG->wwwroot.'/mod/turnitintool/extras.php?do=commslog">'.get_string("logs")
                .'</a> | <a href="'.$CFG->wwwroot.'/mod/turnitintool/extras.php?do=unlinkusers">'.get_string("unlinkusers", "turnitintool");

    if (is_callable("get_file_storage")) {
        $toplinks .= '</a> | <a href="'.$CFG->wwwroot.'/mod/turnitintool/extras.php?do=files">'.get_string("files", "turnitintool");
    }

    $toplinks .= '</a> - ('.get_string('moduleversion','turnitintool').': '.( isset( $module->version ) ? $module->version : $module->versiondb ) . $upgradeavailable . ')</div>';

    $settings->add(new admin_setting_heading('turnitin_header', '', $toplinks));

    $settings->add(new admin_setting_configtext('turnitin_account_id', get_string("turnitinaccountid", "turnitintool"),
                       get_string("turnitinaccountid_desc", "turnitintool"),''));

    $settings->add(new admin_setting_configpasswordunmask('turnitin_secretkey', get_string("turnitinsecretkey", "turnitintool"),
                       get_string("turnitinsecretkey_desc", "turnitintool"),''));

    $settings->add(new admin_setting_configtext('turnitin_apiurl', get_string("turnitinapiurl", "turnitintool"),
                       get_string("turnitinapiurl_desc", "turnitintool"),''));

    $options = array(0 => get_string('no', 'turnitintool'),
                        1 => get_string('yes', 'turnitintool'),
                     );
    $settings->add(new admin_setting_configselect('turnitin_usegrademark', get_string('turnitinusegrademark', 'turnitintool'),
                       get_string('turnitinusegrademark_desc', 'turnitintool'), 1, $options));

    $settings->add(new admin_setting_configselect('turnitin_useerater', get_string('turnitinuseerater', 'turnitintool'),
                        get_string('turnitinuseerater_desc', 'turnitintool'), 0, $options));

    $settings->add(new admin_setting_configselect('turnitin_userepository', get_string('turnitinuserepository', 'turnitintool'),
                       get_string('turnitinuserepository_desc', 'turnitintool'), 0, $options));

    $settings->add(new admin_setting_configselect('turnitin_useanon', get_string('turnitinuseanon', 'turnitintool'),
                       get_string('turnitinuseanon_desc', 'turnitintool'), 0, $options));

    $settings->add(new admin_setting_configselect('turnitin_transmatch', get_string('transmatch', 'turnitintool'),
                       get_string('transmatch_desc', 'turnitintool'), 0, $options));

    if (!isset($CFG->turnitin_agreement)) {
        $CFG->turnitin_agreement=get_string('turnitintoolagreement_default','turnitintool');
    }

    $settings->add(new admin_setting_configtextarea('turnitin_agreement', get_string('turnitintoolagreement', 'turnitintool'),
                       get_string('turnitintoolagreement_desc', 'turnitintool'), ''));

    $settings->add(new admin_setting_configselect('turnitin_studentemail', get_string('turnitinstudentemail', 'turnitintool'),
                       get_string('turnitinstudentemail_desc', 'turnitintool'), 1, $options));

    $settings->add(new admin_setting_configselect('turnitin_tutoremail', get_string('turnitintutoremail', 'turnitintool'),
                       get_string('turnitintutoremail_desc', 'turnitintool'), 1, $options));

    $settings->add(new admin_setting_configselect('turnitin_receiptemail', get_string('turnitinreceiptemail', 'turnitintool'),
                       get_string('turnitinreceiptemail_desc', 'turnitintool'), 1, $options));

    $settings->add(new admin_setting_configselect('turnitin_enablediagnostic', get_string('turnitindiagnostic', 'turnitintool'),
                       get_string('turnitindiagnostic_desc', 'turnitintool'), 0, $options));

    $settings->add(new admin_setting_configtext('turnitin_proxyurl', get_string("proxyurl", "turnitintool"),
                       get_string("proxyurl_desc", "turnitintool"),''));

    $settings->add(new admin_setting_configtext('turnitin_proxyport', get_string("proxyport", "turnitintool"),
                       get_string("proxyport_desc", "turnitintool"),''));

    $settings->add(new admin_setting_configtext('turnitin_proxyuser', get_string("proxyuser", "turnitintool"),
                       get_string("proxyuser_desc", "turnitintool"),''));

    $settings->add(new admin_setting_configpasswordunmask('turnitin_proxypassword', get_string("proxypassword", "turnitintool"),
                       get_string("proxypassword_desc", "turnitintool"),''));

    // Following are values for student privacy settings

    $settings->add(new admin_setting_heading('turnitin_privacy', get_string('studentdataprivacy','turnitintool'),
                       get_string('studentdataprivacy_desc', 'turnitintool')));

    if ( turnitintool_count_records_select( 'turnitintool_users' ) > 0 AND isset( $CFG->turnitin_enablepseudo ) ) {
        $selectionarray = ( $CFG->turnitin_enablepseudo == 1 ) ? array( 1 => get_string('yes', 'turnitintool') ) : array( 0 => get_string('no', 'turnitintool') );
        $pseudoselect = new admin_setting_configselect('turnitin_enablepseudo', get_string('enablepseudo', 'turnitintool'),
                       get_string('enablepseudo_desc', 'turnitintool'), 0, $selectionarray);
        $pseudoselect->nosave = true;
    } else if ( turnitintool_count_records_select( 'turnitintool_users' ) > 0 ) {
        $pseudoselect = new admin_setting_configselect('turnitin_enablepseudo', get_string('enablepseudo', 'turnitintool'),
                       get_string('enablepseudo_desc', 'turnitintool'), 0, array( 0 => get_string('no', 'turnitintool') ) );
    } else {
        $pseudoselect = new admin_setting_configselect('turnitin_enablepseudo', get_string('enablepseudo', 'turnitintool'),
                       get_string('enablepseudo_desc', 'turnitintool'), 0, $options);
    }

    $settings->add( $pseudoselect );

    if ( isset( $CFG->turnitin_enablepseudo ) AND $CFG->turnitin_enablepseudo ) {

        $CFG->turnitin_pseudofirstname = ( isset( $CFG->turnitin_pseudofirstname ) )
                ? $CFG->turnitin_pseudofirstname : get_string('defaultcoursestudent');

        $settings->add(new admin_setting_configtext('turnitin_pseudofirstname', get_string('pseudofirstname', 'turnitintool'),
                        get_string('pseudofirstname_desc', 'turnitintool'), get_string('defaultcoursestudent')));

        $lnoptions = array( 0 => get_string('user') );

        $user_profiles = turnitintool_get_records( 'user_info_field' );
        foreach ( $user_profiles as $profile ) {
            $lnoptions[ $profile->id ] = get_string( 'profilefield', 'admin' ) . ': ' . $profile->name;
        }

        $settings->add(new admin_setting_configselect('turnitin_pseudolastname', get_string('pseudolastname', 'turnitintool'),
                        get_string('pseudolastname_desc', 'turnitintool'), 0, $lnoptions));

        $settings->add(new admin_setting_configselect('turnitin_lastnamegen', get_string('psuedolastnamegen', 'turnitintool'),
                        get_string('psuedolastnamegen_desc', 'turnitintool' ), 0, $options));

        $settings->add(new admin_setting_configtext('turnitin_pseudosalt', get_string('pseudoemailsalt', 'turnitintool'),
                        get_string('pseudoemailsalt_desc', 'turnitintool'), ''));

        $settings->add(new admin_setting_configtext('turnitin_pseudoemaildomain', get_string('pseudoemaildomain', 'turnitintool'),
                        get_string('pseudoemaildomain_desc', 'turnitintool'), ''));

    }


    // Following are default values for new instance

    $settings->add(new admin_setting_heading('turnitin_defaults', get_string('defaults','turnitintool'),
                       get_string('defaults_desc', 'turnitintool')));

    $settings->add(new admin_setting_configselect('turnitin_default_type', get_string('type','turnitintool'),
                       '', 0, turnitintool_filetype_array()));

    $settings->add(new admin_setting_configselect('turnitin_default_numparts', get_string('numberofparts','turnitintool'),
                       '', 1, array(1=>1,2=>2,3=>3,4=>4,5=>5)));

    $options = array();
    $scales = get_scales_menu();
    foreach ($scales as $value => $scale) {
        $options[-$value] = $scale;
    }
    for ($i=100; $i>=1; $i--) {
        $options[$i] = $i;
    }
    $settings->add(new admin_setting_configselect('turnitin_default_grade', get_string('overallgrade','turnitintool'),
                       '', 100, $options));
    unset( $options );

    $ynoptions = array(0 => get_string('no', 'turnitintool'),
                       1 => get_string('yes', 'turnitintool')
                     );

    if ( $CFG->turnitin_useanon ) {
        $settings->add(new admin_setting_configselect('turnitin_default_anon', get_string('anon','turnitintool'),
                        '', 0, $ynoptions ));
    }

    $settings->add(new admin_setting_configselect('turnitin_default_studentreports', get_string('studentreports','turnitintool'),
                       '', 0, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitin_default_allowlate', get_string('allowlate','turnitintool'),
                       '', 0, $ynoptions ));

    $genoptions = array( 0 => get_string('genimmediately1','turnitintool'), 1 => get_string('genimmediately2','turnitintool'), 2 => get_string('genduedate','turnitintool'));
    $settings->add(new admin_setting_configselect('turnitin_default_reportgenspeed', get_string('reportgenspeed','turnitintool'),
                       '', 0, $genoptions ));

    $suboptions = array( 0 => get_string('norepository','turnitintool'), 1 => get_string('standardrepository','turnitintool'));
    if ( $CFG->turnitin_userepository ) {
        array_push( $suboptions, get_string('institutionalrepository','turnitintool') );
    }

    $settings->add(new admin_setting_configselect('turnitin_default_submitpapersto', get_string('submitpapersto','turnitintool'),
                       '', 1, $suboptions ));

    $settings->add(new admin_setting_configselect('turnitin_default_spapercheck', get_string('spapercheck','turnitintool'),
                       '', 1, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitin_default_internetcheck', get_string('internetcheck','turnitintool'),
                       '', 1, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitin_default_journalcheck', get_string('journalcheck','turnitintool'),
                       '', 1, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitin_default_excludebiblio', get_string('excludebiblio','turnitintool'),
                       '', 0, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitin_default_excludequoted', get_string('excludequoted','turnitintool'),
                       '', 0, $ynoptions ));

}
/* ?> */
