<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../config.php');
require_once('../../course/lib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once("lib.php");

if (!is_callable('groups_get_activity_group')) {
    $adminroot = admin_get_root();
    admin_externalpage_setup('managemodules',$adminroot);
} else {
    admin_externalpage_setup('managemodules');
}

$param_module  = optional_param('module', null, PARAM_INT);  // module
$param_displaystart = optional_param('iDisplayStart', null, PARAM_INT);  // displaystart
$param_displaylength = optional_param('iDisplayLength', null, PARAM_INT);  // displaylength

$aColumns = array( 'tu.name', 'cs.shortname', 'cs.fullname', 'sb.submission_filename', 'us.firstname', 'us.lastname', 'us.email', 'fl.filename', 'sb.submission_objectid' );

$sQuery = 'SELECT
    fl.id AS id,
    cm.id AS cmid,
    tu.id AS activityid,
    tu.name AS activity,
    sb.submission_unanon AS unanon,
    us.firstname AS firstname,
    us.lastname AS lastname,
    us.email AS email,
    us.id AS userid,
    fl.mimetype AS mimetype,
    fl.filesize AS filesize,
    fl.timecreated AS created,
    fl.pathnamehash AS hash,
    fl.filename AS rawfilename,
    cs.fullname AS coursetitle,
    cs.shortname AS courseshort,
    cs.id AS course,
    sb.submission_filename AS filename,
    sb.submission_objectid AS objectid
FROM '.$CFG->prefix.'files fl
LEFT JOIN
    '.$CFG->prefix.'turnitintool_submissions sb ON fl.itemid = sb.id
LEFT JOIN
    '.$CFG->prefix.'user us ON fl.userid = us.id
LEFT JOIN
    '.$CFG->prefix.'course_modules cm ON fl.contextid = cm.id AND cm.module = '.$param_module.'
LEFT JOIN
    '.$CFG->prefix.'turnitintool tu ON cm.instance = tu.id
LEFT JOIN
    '.$CFG->prefix.'course cs ON tu.course = cs.id
WHERE
    fl.component = \'mod_turnitintool\' AND fl.filesize != 0';

$sCountQuery = 'SELECT
    fl.id AS id
FROM
    '.$CFG->prefix.'files fl
LEFT JOIN
    '.$CFG->prefix.'turnitintool_submissions sb ON fl.itemid = sb.id
WHERE
    fl.component = "mod_turnitintool" AND fl.filesize != 0';

$param_sortcol[0] = optional_param('iSortCol_0', null, PARAM_INT);  // sortcol
$param_sortingcols = optional_param('iSortingCols', 0, PARAM_INT);  // sortingcols
$sOrder = "";
if ( !is_null( $param_sortcol[0] ) ) {
    $sOrder = " ORDER BY ";
    $startOrder = $sOrder;
    for ( $i=0; $i < intval( $param_sortingcols ); $i++ ) {
        $param_sortcol[$i] = optional_param('iSortCol_'.$i, null, PARAM_INT);  // sortcol
        $param_sortable[$i] = optional_param('bSortable_'.$param_sortcol[$i], null, PARAM_TEXT);  // sortable
        $param_sortdir[$i] = optional_param('sSortDir_'.$i, null, PARAM_TEXT);  // sortdir
        if ( $param_sortable[$i] == "true" ) {
            $sOrder .= $aColumns[ $param_sortcol[$i] ] . " " . $param_sortdir[$i] . ", ";
        }
    }
    $sOrder = substr_replace( $sOrder, "", -2 );
    if ( $sOrder == $startOrder ) $sOrder = "";
}
$sOrder .= ",tu.id asc ";

$param_search = optional_param('sSearch', null, PARAM_TEXT);  // sortingcols
$start = true;
$sWhere = ' AND ( ';
$nobracket = false;
for ( $i=0; $i < count($aColumns); $i++ ) {
    $param_searchable[$i] = optional_param('bSearchable_'.$i, null, PARAM_TEXT);
    $param_search_n[$i] = optional_param('sSearch_'.$i, null, PARAM_TEXT);
    if ( !is_null($param_searchable[$i]) && $param_searchable[$i] == "true" && ( $param_search != '' OR $param_search_n[$i] != '' ) ) {
        if ( !$start ) $sWhere .= " OR ";
        
        if ( $aColumns[$i] == 'sb.submission_objectid' AND $param_search_n[$i] == '##deletable##' ) {
            $sWhere = ( $sWhere == ' AND ( ' ) ? '' : substr_replace( $sWhere, "", -3 ) . ' )';
            $sWhere .= " AND ( sb.submission_objectid IS NOT NULL OR sb.submission_filename IS NULL )";
            $nobracket = true;
        } else if ( $aColumns[$i] != ' ' ) {
            $sWhere .= "CAST(" . $aColumns[$i] . " AS CHAR) LIKE '%" . $param_search . "%'";
            $start = false;
        }
    }
}
if ( $sWhere != ' AND ( ' AND !$nobracket ) {
    $sWhere .= " ) ";
} else if ( $nobracket ) {
    $sWhere .= " ";
} else {
    $sWhere = "";
}

$sLimit = "";
if ( !is_null( $param_displaystart ) && $param_displaylength != '-1' ) {
    $limitfrom = $param_displaystart;
    $limitnum  = $param_displaylength;
} else {
    $limitfrom = ( isset($DB) AND is_callable(array($DB,'get_records_sql')) ) ? 0 : '';
    $limitnum  = ( isset($DB) AND is_callable(array($DB,'get_records_sql')) ) ? 0 : '';
}

$sQuery .= $sWhere;
$cResult = ( isset($DB) AND is_callable(array($DB,'get_records_sql')) ) ? $DB->get_records_sql( $sQuery, array() ) : get_records_sql( $sQuery );
$iTotal = count( $cResult );
$sQuery .= $sOrder . $sLimit;
$rResult = ( isset($DB) AND is_callable(array($DB,'get_records_sql')) )
                ? $DB->get_records_sql( $sQuery, array(), $limitfrom, $limitnum )
                : get_records_sql( $sQuery, $limitfrom, $limitnum  );
$iFilteredTotal = $iTotal; //count( $rResult );

$param_echo = optional_param('sEcho', 0, PARAM_INT);  // echo
$output = array(
    "sEcho" => $param_echo,
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => array()
);

foreach ( $rResult as $result ) {
    // 'cs.fullname', 'tu.name', 'fl.filename', 'us.firstname', 'us.lastname', 'us.email', 'fl.timecreated'
    if ( !empty( $param_search ) AND !is_null( $result->unanon ) AND !$result->unanon ) {
        $output['iTotalDisplayRecords'] = $output['iTotalDisplayRecords'] - 1;
        continue; // If these are search results and this is anonymised skip it
    }
    $row = array();
    if ( is_null( $result->filename ) OR is_null( $result->activityid ) ) {
        $row[] = '<i><b>'.get_string('assigngeterror','turnitintool').'</b></i>';
    } else {
        $row[] = '<a href="'.$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$result->cmid.'&do=allsubmissions">' . $result->coursetitle . ' (' . $result->courseshort . ') - ' . $result->activity . '</a>';
    }
    $row[] = $result->courseshort;
    $row[] = $result->coursetitle;
    $row[] = '<a href="'.$CFG->wwwroot.'/mod/turnitintool/extras.php?do=files&fileid='.$result->id.'&filehash='.$result->hash.'" class="fileicon">'
                . ( is_null( $result->filename ) ? $result->rawfilename : $result->filename ) . '</a>';
    $row[] = ' ';
    $row[] = ( !is_null( $result->unanon ) AND !$result->unanon ) ? get_string( 'anonenabled', 'turnitintool' ) : '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$result->userid.'">'
              .$result->lastname . ', ' . $result->firstname . '</a> (' . $result->email . ')';
    $row[] = ' ';
    $row[] = userdate( $result->created );
    $fnd = array("\n","\r");
    $rep = array('\n','\r');
    $row[] = ( !is_null( $result->objectid ) OR is_null( $result->filename ) ) ? '<a href="'.$CFG->wwwroot.'/mod/turnitintool/extras.php?do=files&fileid='
           .$result->id.'&filehash='.$result->hash.'&filerem=true" '
           .' onclick="return confirm(\''.str_replace($fnd, $rep, get_string('filedeleteconfirm','turnitintool')).'\')">'
           .'<img src="pix/delete.png" class="tiiicons" alt="'.get_string('delete','turnitintool').'" /></a>' : '';
    $output['aaData'][] = $row;
}

$output['query']=$sQuery;

echo json_encode( $output );