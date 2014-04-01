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

$param_pseudo  = optional_param('pseudo', null, PARAM_INT);  // module
$param_displaystart = optional_param('iDisplayStart', null, PARAM_INT);  // displaystart
$param_displaylength = optional_param('iDisplayLength', null, PARAM_INT);  // displaylength

$aColumns = array( 'tu.userid', 'tu.turnitin_uid', 'tu.turnitin_utp', 'mu.firstname', 'mu.lastname', 'mu.email' );

$sQuery = 'SELECT
    tu.id AS id,
    tu.userid AS userid,
    tu.turnitin_uid AS turnitin_uid,
    tu.turnitin_utp AS turnitin_utp,
    mu.firstname AS firstname,
    mu.lastname AS lastname,
    mu.email AS email
FROM '.$CFG->prefix.'turnitintool_users tu
LEFT JOIN
    '.$CFG->prefix.'user mu ON tu.userid = mu.id';

$sCountQuery = 'SELECT
    tu.id AS id
FROM
    '.$CFG->prefix.'turnitintool_users tu';

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
    if ( $sOrder == $startOrder ) {
        $sOrder = "";
    } else {
        $sOrder = substr_replace( $sOrder, "", -2 );
    }
}

$param_search = optional_param('sSearch', null, PARAM_TEXT);  // sortingcols
$start = true;
$sWhere = ' WHERE ( ';
$bracket = false;
for ( $i=0; $i < count($aColumns); $i++ ) {
    $param_searchable[$i] = optional_param('bSearchable_'.$i, null, PARAM_TEXT);
    $param_search_n[$i] = optional_param('sSearch_'.$i, null, PARAM_TEXT);
    if ( !is_null($param_searchable[$i]) && $param_searchable[$i] == "true" && ( $param_search != '' OR $param_search_n[$i] != '' ) ) {
        if ( !$start ) $sWhere .= " OR ";
        if ( $aColumns[$i] == 'tu.turnitin_uid' AND $param_search_n[$i] == '##linked##' ) {
            if ( $sWhere != ' WHERE ( ' ) {
                $sWhere = substr_replace( $sWhere, "", -3 );
                $sWhere = $sWhere . ' ) AND ( ';
            }
            $sWhere .= "tu.turnitin_uid = 0";
            if ( $bracket ) $sWhere .= " )";
        } else if ( $aColumns[$i] != ' ' ) {
            // If using postgres, cast as VARCHAR rather than CHAR
            if ( ( is_callable( array($DB,'get_dbfamily') ) ) && ( $DB->get_dbfamily() == 'postgres' ) ) {
                $sWhere .= "CAST(" . $aColumns[$i] . " AS VARCHAR) LIKE '%" . $param_search . "%'";
            } else {
                $sWhere .= "CAST(" . $aColumns[$i] . " AS CHAR) LIKE '%" . $param_search . "%'";
            }

            $start = false;
        }
    }
}
if ( $sWhere == ' WHERE ( ' ) {
    $sWhere = "";
} else {
    $sWhere .= " )";
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
$cResult = ( isset($DB) AND is_callable(array($DB,'get_records_sql')) ) ? $DB->get_records_sql( $sQuery, array() ) : get_records_sql( $sQuery  );
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

$rResult = ( !is_array( $rResult ) ) ? array() : $rResult;

$i = 0;
foreach ( $rResult as $result ) {
    // 'cs.fullname', 'tu.name', 'fl.filename', 'us.firstname', 'us.lastname', 'us.email', 'fl.timecreated'
    $row = array();
    $row[] = '<input type="checkbox" id="userlinks_'.$i.'" name="userlinks[]" value="'.$result->id.'" />';
    $row[] = ( $result->turnitin_uid == 0 ) ? '' : $result->turnitin_uid;
    $row[] = '&nbsp;';
    $row[] = ( !is_null( $result->firstname ) ) ? '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$result->userid.'">' .$result->lastname . ', ' . $result->firstname . '</a> (' . $result->email . ')'
                                                : get_string( 'nonmoodleuser', 'turnitintool' );
    $row[] = ( $result->turnitin_utp == 1 ) ? turnitintool_pseudoemail( $result->email ) : '-';
    $row[] = '&nbsp;';
    $output['aaData'][] = $row;
    $i++;
}

echo json_encode( $output );