<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Wesleyan  enrolment plugin main library file.
 *
 * @package    enrol
 * @subpackage wessync
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class enrol_wessync_plugin extends enrol_plugin {

    private $cache_enabled = true;
    function __construct() {
	 if ($this->cache_enabled) {
           $this->_memcache = new Memcache;
           $this->_memcache->addServer('129.133.6.61');
           $this->_memcache->addServer('129.133.6.230');
     	   if (!is_object($this->_memcache)) {
	      $this->_selfcache = array();
	      $this->_memcache_enabled = false;
           } else {
	      $this->_memcache_prefix = "wessync_";
	      $this->_memcache_enabled = true;
   	   }
        }
    }     
    public function roles_protected() {
	#locked in place
        return true;
    }

    public function wessync_cache_set ($type,$key,$value,$timeout = '3600') {
        $key = $type . "_" . $key;
	if ($this->_memcache_enabled) {
           $key = $this->_memcache_prefix . $key;
	   $this->_memcache->set($key,$value,0,$timeout);
        } else {
           $this->_selfcache[$key] = $value;
        }		
    }
    public function wessync_cache_get ($type,$key) {
	$key = $type . "_" . $key;
        if ($this->_memcache_enabled) {
	   $key = $this->_memcache_prefix . $key;
	   return $this->_memcache->get($key);
	} else {
	   return $this->_selfcache[$key];
	}
     }
     public function wessync_cache_del ($type,$key) {
	$key = $type . "_" . $key;
	if ($this->_memcache_enabled) {
	   $key = $this->_memcache_prefix . $key;
	   return $this->_memcache->delete($key);
	} else {
	   delete($this->_selfcache[$key]);
        }
      }
    /**
     * Add new instance of enrol plugin.
     * @param object $course
     * @param array instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = NULL) {
        global $DB;

        if ($DB->record_exists('enrol', array('courseid'=>$course->id, 'enrol'=>'wessync'))) {
            // only one instance allowed, sorry
            return NULL;
        }

        return parent::add_instance($course, $fields);
    }

    /* takes the "idnumber" and translates into a course hash; conveinence function */
    public function course_hash_from_idnumber ($idnumber) {
	$course_hash['term'] = substr($idnumber,0,4);
	$course_hash['crse_id'] = substr($idnumber,4,6);
	$remaining_sections = substr($idnumber,10);
	$course_hash['section'] = str_split($remaining_sections,2);
	$course_hash['idnumber'] = $idnumber;
	return $course_hash;
    }

    /*given a moodle course object and ps89prod object, returns list of usernames according to PeopleSoft */
    public function get_members_from_ps89prod ( $moodle_course, $conn) {
    	$statement = "select sysadm.wes_get_email(a.emplid) FROM sysadm.ps_class_tbl d,sysadm.ps_stdnt_enrl a where a.strm = d.strm and a.class_nbr=d.class_nbr and a.stdnt_enrl_status='E' and a.strm = d.strm and to_number(a.strm)=to_number(:strm) and d.crse_id=:crse_id and d.class_section=:section";
	$sth = oci_parse($conn,$statement);
  	$members = array();
	$errors = array();
 	$course_hash = $this->course_hash_from_idnumber($moodle_course->idnumber);
  	foreach ($course_hash['section'] as $section) {
    	  oci_bind_by_name($sth,':strm',$course_hash['term']);
   	  oci_bind_by_name($sth,':section',$section);
    	  oci_bind_by_name($sth,':crse_id',$course_hash['crse_id']);
    	  if (!oci_execute($sth)) {
	    return false;
          }
    	  while ($row = oci_fetch_array($sth)) {
            $username = explode('@',$row[0]);
            if ($username[1] == 'wesleyan.edu') {
              array_push($members,$username[0]);
            } else {
              array_push($errors,"Email returned for the user was $row[0] and non Wesleyan");
            }
          }
        }
        if (empty($members)) {
	  $members = array();
        }
        return $members;
    }
    /* given a moodle course and ps89prod data handle, returns instructors of the course */
   public function get_instructors_from_ps89prod($course,$conn) {
      $statement = "select * from sysadm.ps_wes_instr_class 
                  where strm = :strm and crse_id = :crseid and class_section = :section and CRSE_OFFER_NBR = 1";
      $sth = oci_parse($conn,$statement);
      $teachers = array();
      $course_hash = $this->course_hash_from_idnumber($course->idnumber);
      foreach ($course_hash['section'] as $section) {
        oci_bind_by_name($sth,':strm',$course_hash['term']);
        oci_bind_by_name($sth,':section',$section);
        oci_bind_by_name($sth,':crseid',$course_hash['crse_id']);
        if (!oci_execute($sth)) {
          return false;
        }
        while ($row = oci_fetch_array($sth,OCI_ASSOC)) {
          $username = explode('@',$row['EMAILID']);
          array_push($teachers,$username[0]);
        }
     }
     if (empty($teachers)) {
       return array();
     }
     return array_unique($teachers);
   }

    /* given an array of usernames, enrols and optionally unenrols the users from given role */
    public function sync_course_membership_by_role($moodle_course,$members,$roleid,$unenrol = 'true') {
    	global $DB;
        $result = array( 'errors' => array(), 'actions' => array(), 'failure' => 0, 'users_to_create' => array());
        $result['courseinfo'] = $moodle_course->idnumber . "-" . $moodle_course->shortname;
	/*add error checking */
	if ($this->wessync_cache_get("coursesync_$roleid",$moodle_course->id) == $members ) {
	  $result['actions'] = "Members unchanged from last run, skipping";
	  return $result;
	} 

	$current_users = $this->get_users_by_role_in_course($roleid,$moodle_course->id);
	$instance = $this->get_enrol_course_instance($moodle_course);
        $authoritative_ids = array();
	foreach ($members as $member) {
          $user = $this->wessync_cache_get('user',$member);
	  if (!isset($user) and !is_object($user) or !$user) {
      	    $user = $DB->get_record('user',array('username' => $member),'id,username');
            $this->wessync_cache_set('user',$member,$user);
	    
          }
          if (!$user) {
            $result['failure'] = 1;
            array_push($result['errors'],"User $member does not exist in Moodle");
            array_push($result['users_to_create'],$member);
         } else if (!array_key_exists($user->id,$current_users)) {
            array_push($result['actions'],"Assigned $user->username role $roleid in course $moodle_course->shortname");
            $this->enrol_user($instance,$user->id,$roleid);
            #log every id of an authoritative user for quicker lookups
            $authoritative_ids[$user->id] = 1;
         } else {
            $authoritative_ids[$user->id] = 1;
            array_push($result['actions'],"User $user->username had role $roleid already in course $moodle_course->shortname");
         }
       }
       if ($unenrol) {
         foreach ($current_users as $current_user) {
           $current_id = $current_user->id;
           if (!array_key_exists($current_id,$authoritative_ids)) {
             array_push($result['actions'],"Unassigned role $roleid $current_user->username from course $moodle_course->shortname");
             $this->unenrol_user($instance,$current_id,$roleid);
           }
         }
       } 
     /* if we've finished, stuff the array of auth users into cache so that
	we can skip the run if it's unchanged later */
     if (!$result['failure']) {
       $this->wessync_cache_set("coursesync_$roleid",$moodle_course->id,$members);
     }
     return $result;
    }

    /* returns users in a given role in a given course */
    public function get_users_by_role_in_course($roleid,$courseid) {
	global $DB;
 	$sql = "select u.id,u.username from {user} u join {role_assignments} ra on (ra.userid = u.id) JOIN {user_enrolments} ue on (ue.userid= u.id and ue.enrolid = ra.itemid) join {enrol} e on (e.id = ue.enrolid) join {course} c on (c.id = e.courseid) where u.deleted = 0 and ra.component='enrol_wessync' and ra.roleid=:roleid and c.id=:courseid";
  	$params = array ( 'roleid' => $roleid, 'courseid' => $courseid );
  	$users = $DB->get_records_sql($sql,$params);
  	if ($users == false ) {
   	  return array();
 	} else {
    	  return $users;
 	}
	return array();
    }

    /*grabs given course enrol instance if it exists, else creates a new one */
    public function get_enrol_course_instance($course) {
	global $DB;
   	$sql = "select c.id, c.visible, e.id as enrolid from {course} c
            JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'wessync' )
            WHERE c.id =:courseid";
   	$params = array('courseid' => $course->id);
   	$course_instance = $DB->get_record_sql($sql,$params,IGNORE_MULTIPLE);
   	if (!$course_instance) {
          $course_instance = new stdClass();
          $course_instance->id = $course->id;
          $course_instance->visible = $course->visible;
          $course_instance->enrolid = $this->add_instance($course_instance);
        }
        $instance = $DB->get_record('enrol',array('id' => $course_instance->enrolid));
        return $instance;
   }
   public function get_current_wes_semester() {
      date_default_timezone_set('America/New_York');
      $month = date("n");
      $year = date("y");
      if ($month <= 5 ) {
        $semester = 0;
      } else if ($month >= 9 ) {
        $semester = 9;
     } else if ($month >= 6 and $month <= 8 ) {
       $semester = 6;
     }
     return $year + 100 . $semester;
   }
   /* takes a hash containing term, short_name, full_name, visibility, etc and creates a moodle course out of it */
   public function create_moodle_course_from_template ($course) {
       $course_template = $this->get_moodle_semester_template($course['term']);
       $this->ERRORS = array();
       if (!$course_template) {
	 array_push($this->ERRORS,"Could not find matching template.");
         return 0;
       }
       $new_course = $this->create_template_stub($course_template);
       if (!$new_course) {
         array_push($this->ERRORS,"Could not create matching stub course.");
         return 0;
       }
       $this->sync_moodle_course_data($course,$new_course);
       return $new_course;
   }
   /* takes the term code, fetches the  moodle course that serves as that semester's template */
  public function get_moodle_semester_template ( $term = '' ) {
     #template courses "idnumber" is the semester/term code
      global $DB;
      $template_course = $DB->get_record("course",array("idnumber" => $term));
      return $template_course;
  }
  /* takes a template and creates a new empty course from it, returns the new course */
  public function create_template_stub ( $course_template) {
      global $DB,$CFG;
      require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
      require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

      #save the template id;
      $course_template_id = $course_template->id;
      #zero it out so it looks "new"
      $course_template->id = "";
      #create a new record
      $new_course_id = $DB->insert_record("course",$course_template);
      #now do the backup; haven't quite figured out how Moodle2 works perfectly, but this works for now - "2" is the admin user who has rights to do the backup
      #ideally, we'd want to make our own backup controller 
      $bc = new backup_controller(backup::TYPE_1COURSE, $course_template_id, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_SAMESITE, "2");
      $bc->execute_plan();
      $backupfile = $bc->get_results();
      $packer = new zip_packer();
      #unzip our backup to a temporary restore file
      $backupfile['backup_destination']->extract_to_pathname($packer,"$CFG->dataroot/temp/backup/$course_template_id");
      $restore = new restore_controller($course_template_id,$new_course_id,backup::INTERACTIVE_NO,backup::MODE_SAMESITE,"2",backup::TARGET_NEW_COURSE);
      $restore->execute_precheck();
      $outcome = $restore->execute_plan();
      #course restored, now lets fetch the object
      $new_course = $DB->get_record("course",array("id" => $new_course_id));
      return $new_course;
  }
  /*given a course hash with descriptions, moodle course and optional sync_only flag sync the data */
  public function sync_moodle_course_data($course,$moodle_course,$sync_only = 0) {
      global $DB;
      # course category trickery is necessary to resort the course after any change
      $course_category = $this->get_moodle_category($course);
      if (!$course_category) {
        array_push($this->ERRORS,"Could not find category update");
	return 0;
      }
      /* have to make sure to modify the context, too */
      $course_context = get_context_instance(CONTEXT_COURSE,$moodle_course->id);
      $moodle_course->idnumber = $course['idnumber'];
      $moodle_course->fullname = $course['full_name'];
      $moodle_course->shortname = $course['short_name'];
      $moodle_course->category = $course_category->id;
      $moodle_course->summary = $course['summary'];
      /* visibility doesn't make sense as a course sync, rest do? */
      if (!$sync_only) {
        $moodle_course->visible = $course['visible'];
      }
      /*update with the new object */
      $result = $DB->update_record('course',$moodle_course);
      $category_context = get_context_instance(CONTEXT_COURSECAT,$moodle_course->category);
      context_moved($course_context,$category_context);
      if (!$result) {
        array_push($this->ERRORS,"Could not update course");
        return 0;
      }
      fix_course_sortorder();
      return 1;
  }
    /* when given a course, return with the correct category */
  public function get_moodle_category ($course ) {
      global $DB;
      $term = $course['term'];
      $year = substr($term, 0, 3) + 1900;
      $season = substr($term, 3, 1);
      if ($season == 9 ) {
        $season = "Fall";
      }
      if ($season == 1) {
        $season = "Spring";
      }
      if ($season == 6) {
        $season = "Summer";
       }
      $category_name = $season . " " . $year;
      /* alright grab potential categories */
      $categories = $DB->get_records('course_categories',array('name' => $category_name));
      $category_to_return = 0;
      $glsp_types = array('GLSP','DCST','GLS');
      if (isset($course['acad_career']) and in_array($course['acad_career'],$glsp_types)) {
        /* 10 is the master GLSP category */
        $expected_parent = 10;
      } else {
        $expected_parent = 0;
      }
      foreach ($categories as $category) {
        if ($category_to_return) {
          continue;
        }
        if ($category->parent == $expected_parent) {
          $category_to_return = $category;
        }
      }
    if (!$category_to_return) {
      $category_to_return = $DB->get_record('course_categories',array('name' => 'Miscellaneous'));
    }
    return $category_to_return;
  }
}
 
