<?php
/* unfortunately not well thought out - whether something is in helperlib
   or the enrol object is vaguely random feeling right now; will try and
   refactor - might make sense for different objects for each data source;
   i.e. moodlecreate+peoplesoft vs. AD + hash, etc, etc.. */

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

if (!enrol_is_enabled('wessync')) {
    die;
}

// Update enrolments -- these handlers should autocreate courses if required
$enrol = enrol_get_plugin('wessync');
require(dirname(dirname(__FILE__)).'/helperlib.php');
$lock = check_lock_file($argv[1]);
if ($argv[1] == 'peoplesoft_enrol' ) {
  $results = peoplesoft_enrol($enrol,$lock);
} else if ($argv[1] == 'ldap_enrol' ) {
  $results = ldap_enrol($enrol,$lock);
} else if ($argv[1] == 'fy_enrol') {
  $results = fy_enrol($enrol,$lock);
} else if ($argv[1] == 'idnumber_enrol') {
  $results = idnumber_enrol($enrol,$lock,$argv[2]);
} else {
  print "Unknown enrol request!";
}
release_lock_file($lock,$argv[1]);
var_dump($results);

function idnumber_enrol($enrol,$lock,$cs_courses) {
  $ps89prod = get_ps89prod_db();
  if (!$cs_courses) {
    print "This syncing method requires an argument of comma-separated idnumbers";
    die;
  }
  $idnumber_courses = split(',',$cs_courses);
  foreach ($idnumber_courses as $idnumber) {
    $moodle_course = get_moodle_course($idnumber);
    if (!$moodle_course) {
      $course_hash = $enrol->course_hash_from_idnumber($idnumber);
      $course = get_peoplesoft_course_data($ps89prod,$course_hash);
      $auth_teachers = $enrol->get_instructors_from_ps89prod($course,$ps89prod);
      foreach ($auth_teachers as $teacher) {
        $course['summary'] .= "<p>Instructor: $teacher</p>";
      }
      $moodle_course = $enrol->create_moodle_course_from_template($course);
    }
    if (!$moodle_course) {
      print "No moodle course available, bailing.";
      die;
    }
    $auth_students = $enrol->get_members_from_ps89prod($moodle_course,$ps89prod);
    $auth_teachers = $enrol->get_instructors_from_ps89prod($moodle_course,$ps89prod);
    $result = $enrol->sync_course_membership_by_role($moodle_course,$auth_students,"5");
    $master_results[$result['courseinfo']]['student_sync'] = $result;
    /* role id 3 == teacher */
    $result = $enrol->sync_course_membership_by_role($moodle_course,$auth_teachers,"3");
    $master_results[$result['courseinfo']]['teacher_sync'] = $result;
  }
  return $master_results;
}

function peoplesoft_enrol ($enrol,$lock) {
  $master_results = array();

  $moodlecreate = get_moodlecreate_db();
  if (mysqli_connect_errno()) {
    print mysqli_connect_error();
    release_lock_file($lock,'peoplesoft_enrol');
    die;
  }
  $ps89prod = get_ps89prod_db();
  if (!$ps89prod) {
    print oci_error($ps89prod);
    release_lock_file($lock,'peoplesoft_enrol');
    die;
  }
  $semester = $enrol->get_current_wes_semester();
  /* handle the moodlecreate creations */
  $moodlecreate_courses = get_moodlecreate_courses($moodlecreate,$semester);
  $email_results = array();
  foreach ($moodlecreate_courses as $course) {
    $moodle_course = get_moodle_course($course['idnumber']);
    if (!$moodle_course) {
      print "Course " . $course['short_name'] . " does not exist, creating...\n";
      if ($course['status'] == 'Y' ) {
	print "Course " . $course['short_name'] . " says it is already created, refusing to continue.\n";
        continue;
      } else {
        $course = get_peoplesoft_course_data($ps89prod,$course);
        $moodle_course = $enrol->create_moodle_course_from_template($course);
        if ($moodle_course) {
  	  /*call back to MoodleCreate database to flag as created */
  	 # flag_as_created($moodlecreate,$course['id']);
	  $email_results[] = $course['short_name'] . " newly created.";
	  $email_results[] = "\tRequested by:" . $course['requested_by'];
        } else {
	  print "Could not create course " . $course['short_name'] . " because of the following:\n" . implode('\n',$enrol->ERRORS) . "\n";
        }
      }
    }
    if (!$moodle_course) {
      continue;
    }
    $auth_students = $enrol->get_members_from_ps89prod($moodle_course,$ps89prod);
    if ($auth_students === false ) {
      print "Database errors";
      continue;
    }
    $auth_teachers = $enrol->get_instructors_from_ps89prod($moodle_course,$ps89prod);
    if ($email_results) {
      foreach ($auth_teachers as $teacher) {
        $email_results[] = "\tInstructor: $teacher";
      }
    }    
    if ($auth_teachers === false ) {
      print "Database errors";
      continue;
    }
    /* role id 5 == student */
    $result = $enrol->sync_course_membership_by_role($moodle_course,$auth_students,"5");
    $master_results[$result['course_info']]['student_sync'] = $result;
    /* role id 3 == teacher */
    $result = $enrol->sync_course_membership_by_role($moodle_course,$auth_teachers,"3");
    $master_results[$result['course_info']]['teacher_sync'] = $result;
  } 
  if ($email_results) {
     $body = implode("\n",$email_results);
     mail("melson@wesleyan.edu","Moodle Course Creation Report",$body,"From: moodle_admins@wesleyan.edu");
  }
  return $master_results;
}

/*alright, now for one offs defined by LDAP groups */
function ldap_enrol ($enrol,$lock) {
  global $DB;
  $ldapauth = get_auth_plugin('cas');
  $ldapconnection = $ldapauth->ldap_connect();
  #format is Moodle ShortName => array of AD groups
  $one_off_syncs = array( 'CBC-Disc' => array('list_all_faculty','list_librarians','list_admin_fac_priv'),
                          'Staff-Disc' => array('list_ben_astf'),
                          'AdHocCommRpts' => array ('list_all_faculty','list_admin_fac_priv'), 
                          'faculty-chair' => array('voting_faculty'),
                          'Host-Training' => array('2012'));


  foreach ($one_off_syncs as $course => $ldap_groups) {
    $moodle_course = $DB->get_record('course',array('shortname' => $course));
    if (!$moodle_course) {
      /*no such Moodle course */
      continue;
    }
    $authoritative_members = array();
    foreach ($ldap_groups as $ldap_group) {
      $group_members = get_from_ad($ldap_group,$ldapconnection);
      $authoritative_members = array_merge($authoritative_members,$group_members);
    }
    $results = $enrol->sync_course_membership_by_role($moodle_course,$authoritative_members,"5");
    $master_results[$results['course_info']]['student_sync'] = $results;
  }
  return $master_results;
}

/*alright, now for first year categories */
function fy_enrol ($enrol,$lock) {
  $master_results = array();
  $semester = $enrol->get_current_wes_semester();
  $ps89prod = get_ps89prod_db();
  if (!$ps89prod) {
    print oci_error($ps89prod);
    release_lock_file($lock,'fy_enrol');
    die;
  }
  $fy_courses = wes_get_first_year_courses($semester);
  $fy_students = wes_get_first_year_students($ps89prod,$semester);
  
  foreach ($fy_courses as $moodle_course) {
     $result = $enrol->sync_course_membership_by_role($moodle_course,$fy_students,"5");
     $master_results[$result['course_info']]['student_sync'] = $result;
  }
  return $master_results;
}

