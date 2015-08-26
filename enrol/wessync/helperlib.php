<?php
/*contains helper functions that didn't belong in the enroll object*/
/*fetches peoplesoft database object as thor*/
function get_peoplesoft_db() {
  include "inc/db.conf.php";
  $dbh = oci_connect($ps_user,$ps_pass,$ps_dsn);
  return $dbh;
}

/*fetches moodlecreate db object */
function get_moodlecreate_db() {
  include "inc/db.conf.php";
  $moodlecreate = mysqli_connect($moodlecreate_host,$moodlecreate_user,$moodlecreate_pass,$moodlecreate_db,$moodlecreate_port);
  
  return $moodlecreate;
  
}

/* takes a hash of course data and returns hash with added peoplesoft data; certain key Moodle hash values will get filled in if empty */

function get_peoplesoft_course_data($psdbh,$course) {
  $statement = "select * from sysadm.ps_wes_section where crse_id=:crseid and strm=:strm
                and
                class_nbr = wes_host_class_nbr";
  $sth = oci_parse($psdbh,$statement);
  oci_bind_by_name($sth,':strm',$course['term']);
  oci_bind_by_name($sth,':crseid',$course['crse_id']);
  oci_execute($sth);
  $array_to_add = array('ACAD_CAREER','DESCR','COURSE_TITLE_LONG','WES_HOST_CAT_NBR','WES_HOST_SUBJECT','WES_INSTRUCTORS');
  $course_found = 0;
  while ($row = oci_fetch_array($sth,OCI_ASSOC)) {
    $course_found = 1 ;
    foreach ($array_to_add as $field) {
      $course[strtolower($field)] = $row[$field];
    }
  }
  if (!$course_found) {
    return false;
  }
  #built in default values if no values are present in passed hash
  if (!$course['visible']) {    
    $course['visible'] = 0;
  }
  if (!$course['short_name']) {
    $course['short_name'] = $course['wes_host_subject'] . trim($course['wes_host_cat_nbr']) . "-";
    if (!$course['section']) {
      #if no section specified assume only one
      $course['section'] = array('01');
    }
    foreach ( $course['section'] as $section ) {
      $course['short_name'] .= $section;
    }
    $season = get_season_from_semester($course['term']);
    $course['short_name'] .= "-" . substr($season,0,2);
    $course['short_name'] .= substr($course['term'],0,3) + 1900;  
  }
  if (!$course['full_name']) {
    $course['full_name'] = $course['wes_host_subject'] . trim($course['wes_host_cat_nbr']) . "-";
    if (!$course['section']) {
      #if no section specified assume only one
      $course['section'] = array('01');
    }
    foreach ( $course['section'] as $section ) {
      $course['full_name'] .= $section;
    }
    $course['full_name'] .= " " . $course['course_title_long'];
  }

  return $course;
}


/*checks for lock file, creates one if it does not exist - this *will* stick around if the script exits in an unpredictable fashion*/
function check_lock_file($process_name) {
  if (file_exists("/tmp/$process_name.lock")) {
    echo "File already exists, script is running, please investigate!";
    exit;
  }
  $file = fopen("/tmp/$process_name.lock",'w');
  if ($file === false ) {
    echo "Could not create lock file\n";
    exit;
  }
  if (flock ($file, LOCK_EX | LOCK_NB ) === false ) {
     echo "Script is running, please investigate!";
     exit;
  }
  fwrite($file,getmypid());
  return $file;
}
/*cleans up lock file*/
function release_lock_file ($lock_file,$process) {
   fclose($lock_file);
   unlink("/tmp/$process.lock");
}

/* fetches moodle course based on idnumber, Wesleyan's unique identifier */
function get_moodle_course ( $idnumber = '' ) {
  global $DB;
  $moodle_course = $DB->get_record('course',array('idnumber' => $idnumber));
  if (!$moodle_course) {
    return 0;
   } else {
    return $moodle_course;
  }
}

/* Takes moodlecreate db handle and term and returns that have been requested */
/* To Do: Clean up method that sections and instructors are fetched */
function get_moodlecreate_courses( $db_handle = '', $term = '', $redirect=0) {
  if (!$db_handle) {
    return false;
  }
  $moodlecreate_courses = array();
  if ($term == '' && !$redirect) {
#    $stmt = $db_handle->prepare("SELECT a.TERM,a.CRSE_ID,a.SHORT_NAME,a.LONG_NAME from course a join o
#n a.id == instructor.course_id and a.id == section.course_id  where a.created = 'Y' and a.term !='0'");
#    $stmt = $db_handle->prepare("select c.TERM, c.CRSE_ID,s.section,c.SHORT_NAME,c.LONG_NAME, i.USERNAME
#                                               from course c
#                                               join instructor i on c.ID = i.COURSE_ID
#                                               join section s on c.ID = s.course_ID
#                                               order by c.CREATED_ON DESC, c.SHORT_NAME, i.USERNAME");
     $stmt = $db_handle->prepare("select ID,TERM,CRSE_ID,SHORT_NAME,LONG_NAME,VISIBLE,REQUESTED_BY,STATUS,ALT_STATUS from course where MOODLE2_COURSE='Y'");
  } else if (!$redirect && $term) {
     /* hardcoded to only Create fall and up courses */
     $stmt = $db_handle->prepare("select ID,TERM,CRSE_ID,SHORT_NAME,LONG_NAME,VISIBLE,REQUESTED_BY,STATUS,ALT_STATUS from course where MOODLE2_COURSE='Y' and TERM >= '$term' and TERM > '1157'");
   #TO BE IMPLEMENTED
  } else if ($term == '' && $redirect) {
     $stmt = $db_handle->prepare("select ID,TERM,CRSE_ID,SHORT_NAME,LONG_NAME,VISIBLE,REQUESTED_BY,STATUS,ALT_STATUS from course where MOODLE2_COURSE='N' and TERM >= '$term'");
  } else if ($redirect) {
     $stmt = $db_handle->prepare("select ID,TERM,CRSE_ID,SHORT_NAME,LONG_NAME,VISIBLE,REQUESTED_BY,STATUS,ALT_STATUS from course where MOODLE2_COURSE='N'");
  }
  if (mysqli_error($db_handle)) {
     return false;
  }
  $stmt->bind_result($id,$term,$crse_id,$short_name,$long_name,$visible,$requested_by,$status,$alt_status);
	
  $stmt->execute();
  while ($stmt->fetch()) {
    $course['id'] = $id;
    $course['short_name'] = $short_name;
    $course['full_name'] = $long_name;
    /* add slashes don't seem required in Moodle2 */
#    #addslashes($short_name);
#    $course['full_name'] = addslashes($long_name);
    $course['term'] = $term;
    $course['idnumber'] = $term . $crse_id;
    $course['crse_id'] = $crse_id;
    $course['instructor'] = array();
    $course['section'] = array();
    $course['status'] = $status;
    $course['alt_status'] = $alt_status;
    if ($visible == 'Y' ) {
      $course['visible'] = 1;
    } else {
      $course['visible'] = 0;
    }
    $course['requested_by'] = $requested_by;
    $course['summary'] = '';
    array_push($moodlecreate_courses,$course);
  }
 $stmt->close();
  foreach ($moodlecreate_courses as &$sync_course) {
    $stmt = $db_handle->prepare("SELECT SECTION FROM section where COURSE_ID=?");
    $stmt->bind_param("i",$sync_course['id']);
    $stmt->bind_result($section);
    $stmt->execute();
    while ($stmt->fetch()) {
        $sync_course['idnumber'] .= $section;
        array_push($sync_course['section'],$section);
    }
    $stmt = $db_handle->prepare("SELECT distinct USERNAME FROM instructor where COURSE_ID=?");
    $stmt->bind_param("i",$sync_course['id']);
    $stmt->bind_result($instructor);
    $stmt->execute();
    
    while ($stmt->fetch()) {
        array_push($sync_course['instructor'],$instructor);
        $sync_course['summary'] .= "<p>Instructor: $instructor</p>";
    }
    $stmt->close();
  }
  return $moodlecreate_courses;
}
/*given an ldap_group return an array of usernames in that group */
function get_from_ad ($ldap_group,$ldapconnection) { 
 $sr = ldap_search($ldapconnection,"ou=AutoGroups,ou=WesUsers,dc=wesad,dc=wesleyan,dc=edu","(&(cn=$ldap_group))");
  $entries = ldap_get_entries($ldapconnection,$sr);
  $members = array();
  if ($entries['count'] == 0) {
    echo "No such group corresponding to $ldap_group\n";
    return false;
  } else {    $group = $entries[0];
    for ($i = 0 ; $i < $group['member']['count'] ; $i++ ) {
      $member = $group['member'][$i];
      $sr = ldap_read($ldapconnection,$member,"(&(objectclass=user))",array('samaccountname'));
      $entry = ldap_get_entries($ldapconnection,$sr);
      array_push($members,$entry[0]['samaccountname'][0]);
    }
  }
  if (sizeof($members) < 1 or !$members) {
    return false;
  }
  return $members;
}

/*given a semester code finds all courses in a given year category */

function wes_get_first_year_courses($semester) {
  global $DB;
  $fy_category =  $DB->get_record('course_categories',array('name' => 'First Year'));
  $top_path = $fy_category->path;
  $year = substr($semester,0,3)+ 1900; 
  $season = get_season_from_semester($semester);
  
  if ($season == "Spring") {
    $year--;
  }
  $sql = "path like '$top_path/%' and name like ?";
  $category = $DB->get_record_select('course_categories',$sql,array($year),'id');
  if (!isset($category->id)) {
    return array();
  } 
  $courses = $DB->get_records('course',array('category' => $category->id));
  return $courses; 
}
/*gets all "first year" students */
   
function wes_get_first_year_students($psdb,$semester) {   
  $members = array();
  /*from pturenne*/
  $year = substr($semester,0,3)+ 1900;

/*  $statement = "SELECT A.EMPLID
  FROM SYSADM.PS_ACAD_PROG A, SYSADM.PS_ACAD_PLAN B
  WHERE A.ACAD_CAREER = 'UGRD'
     AND A.admit_term>=:strm
     AND A.EMPLID = B.EMPLID
     AND A.ACAD_CAREER = B.ACAD_CAREER
     AND A.STDNT_CAR_NBR = B.STDNT_CAR_NBR
     AND A.EFFSEQ = B.EFFSEQ
     AND B.EFFDT = A.EFFD
     AND B.ACAD_PLAN in ('PRE-MATRIC','TCEX','VINT','FYST','TRAN') UNION SELECT C.EMPLID   FROM SYSADM.PS_SRVC_IND_DATA C   WHERE C.SRVC_IND_CD = 'NEW'      AND C.SRVC_IND_REASON IN ('ADVIS','INTER') AND C.AMOUNT = :year";*/
  $statement = "SELECT A.EMPLID FROM SYSADM.PS_WES_NEW_STU_TRM A WHERE A.STRM >=:strm UNION SELECT C.EMPLID   FROM SYSADM.PS_SRVC_IND_DATA C   WHERE C.SRVC_IND_CD = 'NEW' AND C.SRVC_IND_REASON IN ('ADVIS','INTER','JTRAN') AND C.AMOUNT = :year";
  /* now see if it's Spring and if we have to go back a semester because the
     year of Spring is 2013, but it's the "2012" school year for the above
     query */
  $season = get_season_from_semester($semester);
  if ($season == "Spring") {
    $year--;
  }
  $sth = oci_parse($psdb,$statement);
  oci_bind_by_name($sth,':strm',$semester);
  oci_bind_by_name($sth,':year',$year);
  if (!oci_execute($sth)) { 
    var_dump(oci_error($sth));
    return false;
  }
  /*wesid->username */
  $wesid_statement = "select sysadm.wes_get_email(:wesid) from dual";
  $wesid_sth = oci_parse($psdb,$wesid_statement);
  while ($row = oci_fetch_array($sth,OCI_ASSOC)) {
    $emplid = $row['EMPLID'];
    oci_bind_by_name($wesid_sth,':wesid',$emplid);
    if (!oci_execute($wesid_sth)) {
      var_dump(oci_error($sth));
      return false;
    }
    $wesid_row = oci_fetch_row($wesid_sth);
    $username = explode('@',$wesid_row[0]);
    if ($username[1] == 'wesleyan.edu') {
      array_push($members,$username[0]);
    } else {
      print "Email is $wesid_row[0] and is not Wesleyan for user id $emplid, skipping\n";
    }
  }
  if ($season == "Spring") {
    $ps_year = substr($semester,0,3);
    $ps_year--;
    $prev_semester = $ps_year . '9';
    $prev_students = wes_get_first_year_students($psdb,$prev_semester);
    $members = array_merge($prev_students,$members);
  }
  print $members;

  return array_unique($members);
}
/*updates moodlecreate database with updated status */
function flag_as_created( $db_handle, $idnumber,$redirect=0 ) {
   if (!$redirect) {
     $stmt = $db_handle->prepare("update course set STATUS='Y' where ID=? and STATUS='N'");
   } else {
     $stmt = $db_handle->prepare("update course set ALT_STATUS='Y' where ID=? and ALT_STATUS='N'");
  }
   $stmt->bind_param('d',$idnumber);
   return $stmt->execute();
}

/*gets the season given a given semester */
function get_season_from_semester($semester) {
  $season = substr($semester,-1,1);
  if ($season == 9) {
    $season = "Fall";
  } else if ($season == 1 ) {
    $season = "Spring";
  } else if ($season == 0 ) {
    $season = "Winter";
  } else if ($season == 6 ) {
    $season = "Summer";
  }
  return $season;
}

