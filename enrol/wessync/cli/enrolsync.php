<?php
/* unfortunately not well thought out - whether something is in helperlib
   or the enrol object is vaguely random feeling right now; will try and
   refactor - might make sense for different objects for each data source;
   i.e. moodlecreate+peoplesoft vs. AD + hash, etc, etc.. */

/* moodle errors just print to standard out, so any errors are just prints */
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
$valid_methods = array ('peoplesoft_enrol','ldap_enrol,fy_enrol','idnumber_enrol');
if (!isset($argv[1]) or !in_array($argv[1],$valid_methods)) {
  print "Must pass enrol request that matches one of the following " . join(',',$valid_methods) . "\n";
  die;
}
$lock = check_lock_file($argv[1]);
if ($argv[1] == 'peoplesoft_enrol' ) {
  if (!isset($argv[2])) {
    $redirect = 0;
  } else {
    $redirect = 1;
  }
  $results = peoplesoft_enrol($enrol,$lock,$redirect);
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
$fh = fopen("/tmp/" . $argv[1] . $argv[2] . "_results","a+");
fwrite($fh,print_r($results,true));
fclose($fh);

function idnumber_enrol($enrol,$lock,$cs_courses) {
  $ps89prod = get_ps89prod_db();
  if (!$ps89prod) {
    print oci_error($ps89prod);
    release_lock_file($lock,'idnumber_enrol');
    die;
  }
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
      if (!$course) {
        $master_results[$idnumber] = "Could not find information on $idnumber";
        continue;
      }
      $auth_teachers = $enrol->get_instructors_from_ps89prod($course,$ps89prod);
      foreach ($auth_teachers as $teacher) {
        $course['summary'] .= "<p>Instructor: $teacher</p>";
      }
      $moodle_course = $enrol->create_moodle_course_from_template($course);
    }
    if (!$moodle_course) {
      $master_results[$idnumber] = "No moodle course available for $idnumber, skipping.";
      continue;
    }
    /* just a unique identifier for tagging results hash */
    $courseinfo = $moodle_course->idnumber . "-" . $moodle_course->shortname;
    $auth_students = $enrol->get_members_from_ps89prod($moodle_course,$ps89prod);
    if (!$auth_students) {
      continue;
    }
    $auth_teachers = $enrol->get_instructors_from_ps89prod($moodle_course,$ps89prod);
    if (!$auth_teachers) {
       continue;
    }
    foreach ($auth_students as $student) {
      global $DB;
      $user = $DB->get_record('user',array('username' => $student),'id,username');
      #$return = grade_recover_history_grades($user->id, $moodle_course->id);

    }
    $result = $enrol->sync_course_membership_by_role($moodle_course,$auth_students,"5");
    $master_results[$courseinfo]['student_sync'] = $result;
    /* role id 3 == teacher */
    $result = $enrol->sync_course_membership_by_role($moodle_course,$auth_teachers,"3");
    $master_results[$courseinfo]['teacher_sync'] = $result;
  }
  return $master_results;
}

function peoplesoft_enrol ($enrol,$lock,$redirect=0) {
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
  $moodlecreate_courses = get_moodlecreate_courses($moodlecreate,$semester,$redirect);
  $email_results = array();
  foreach ($moodlecreate_courses as $course) {
    if ($redirect && ($course['term'] <= 1129 or $semseter >= 1139)) {
      continue;
    }
    $moodle_course = get_moodle_course($course['idnumber']);
    if (!$moodle_course) {
      if ($redirect and $course['alt_status'] == 'Y') {
        print "Redirect Course " . $course['short_name'] . " is already created, refusing to continue...";
        continue;
      }
      else if (!$redirect and $course['status'] == 'Y' ) {
	print "Course " . $course['short_name'] . " says it is already created, refusing to continue.\n";
        continue;
      } else {
	/* found a need to hold on to orig course incase peoplesoft data returns null */
	$orig_course = $course;
        $course = get_peoplesoft_course_data($ps89prod,$course);
	if (!$course) {
	   $master_results[$orig_course['idnumber']]['warn'] = "Could not find course in Peoplesoft, skipping\n";
	   continue;
	}
        $moodle_course = $enrol->create_moodle_course_from_template($course,$redirect);
        if ($moodle_course) {
  	  /*call back to MoodleCreate database to flag as created */
  	  flag_as_created($moodlecreate,$course['id'],$redirect);
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
    /* just a unique identifier for tagging results hash */
    $courseinfo = $moodle_course->idnumber . "-" . $moodle_course->shortname;

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
    $master_results[$courseinfo]['student_sync'] = $result;
    /* role id 3 == teacher */
    $result = $enrol->sync_course_membership_by_role($moodle_course,$auth_teachers,"3");
    $master_results[$courseinfo]['teacher_sync'] = $result;
  }
  /* no need to notify on redirect course creation */ 
  if ($email_results && !$redirect) {
     $body = implode("\n",$email_results);
     $recipients = array ('jwest@wesleyan.edu','kwiliarty@wesleyan.edu','dschnaidt@wesleyan.edu','jgoetz@wesleyan.edu','eparis@wesleyan.edu','melson@wesleyan.edu');
     $subject = "Moodle2 Course Creation Report";
     mail(join(',',$recipients),$subject,$body,"From: moodle_admins\@wesleyan.edu\r\nPrecedence: Bulk\r\n");
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
    $master_results[$results['courseinfo']]['student_sync'] = $results;
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
     $master_results[$result['courseinfo']]['student_sync'] = $result;
  }
  return $master_results;
}

