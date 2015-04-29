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
 * Helper class for support of Moodle API-related functionality.
 * 
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *         NOTE: callers which include/require this class MUST also include/require the following:
 *         - [moodle root]/config.php
 *         - mod/turningtech/lib.php
 */
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/auth/cas/CAS/CAS.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/simple_html_dom.php');
global $DB;
/**
 * Class that abstracts communication with Moodle systems
 * 
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TurningTechMoodleHelper {
    /**
     * authenticate a username/password pair
     * @copyright  2012 Turning Technologies
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     * @param string $username
     * @param string $password
     * @return object
     */
    public static function authenticateuser($username, $password) {
        $xx = authenticate_user_login($username, $password);
        $zz = false;
        if ($xx == null) {
            $authsenabled = get_enabled_auth_plugins();
            foreach ($authsenabled as $auth) {
                if ($auth == 'cas') {
                    $zz = true;
                } else if ($auth == 'shibboleth') {
                    $shib = true;
                }
            }
            if ($zz) {
                // Cas function.
		// Cookedir as empty string will keep it in memory - as long as handle is reused; /dev/null would work, not sure if
		// this is meant to try and be cross platform.
                // $cookiedir = tempnam ("/tmp", "CURLCOOKIE");
		$cookiedir = '';
                $authplug = get_auth_plugin('cas'); 
                $uri = $authplug->config->baseuri;
                $hostname = $authplug->config->hostname;
                $port = $authplug->config->port;
                $serv = get_config('moodle', null);
                $service = $serv->wwwroot."/login/index.php";
                if ($port == '443' || $port == '8443') {
                    $prefix = "https://";
                } else {
                    $prefix = "http://";
                }
		// could probably grab the CAS login URL from the auth plugin directly
                $url = $prefix . $hostname . ":" . $port . "/" . $uri . "login?service=" . urlencode($service);
                $curl_connection = curl_init($url);
                // Set options.
                curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
		#probably unnecessary
                curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
                curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl_connection, CURLOPT_COOKIEFILE, $cookiedir);
                curl_setopt($curl_connection, CURLOPT_COOKIEJAR, $cookiedir);
                curl_setopt($curl_connection, CURLOPT_HEADER, 1);
		/*Really should verify, should be able to piggyback off of settings in the plugin, will fix later */
                curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl_connection, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
                // Perform our request.
                $result = curl_exec($curl_connection);
		// No particular reason to close the connection, will reuse; keeps cookie in memory, too
                #curl_close($curl_connection);
                $html = new simple_html_dom();
                $html->load($result);
                foreach ($html->find('input') as $element) {
                    $post_data[$element->name] = $element->value;
                }
                $post_data['username'] = urlencode($username);
                $post_data['password'] = urlencode($password);
                $rt = $html->find('form', 0);
                $jsessn = explode("jsessionid=", $rt->action);
                foreach ($post_data as $key => $value) {
                    $post_items[] = $key . '=' . $value;
                }
                // Create the final string to be posted using implode().
                $post_string = implode('&', $post_items);
                // Create array of data to be posted.
		// No earthly idea what this is for; doesn't seem to be used again; Path isn't guaranteed to be /cas/ to boot.
                // $strcookie = 'JSESSIONID=' . $jsessn[1] . '; Path=/cas/; Secure';
                session_write_close();
		// no reason to re init if re-using the connection
                //$curl_connection = curl_init($url);
                // Set options.
		/*Unnecessry repetition if re-using handle 
                curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
                curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl_connection, CURLOPT_HEADER, 1);
                curl_setopt($curl_connection, CURLOPT_COOKIEFILE, $cookiedir);
                curl_setopt($curl_connection, CURLOPT_COOKIEJAR, $cookiedir);
                curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl_connection, CURLOPT_SSL_VERIFYHOST, false); */
                curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
                //curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
                // Perform our request.
                $result = curl_exec($curl_connection);
		// Logout of CAS to close potential security hole
		curl_setopt($curl_connection,CURLOPT_URL,$prefix . $hostname . ":" . $port . "/" . $uri . "/logout");
	 	curl_exec($curl_connection);
                // Close the connection.
                curl_close($curl_connection);
                if (strpos($result, 'ticket=ST') !== false) {
                    $mnetid = get_config('moodle', 'mnet_localhost_id');
                    if ($user = get_complete_user_data('username', $username, $mnetid)) {
                        $auth = 'cas';
                        $authplugin = get_auth_plugin($auth);
                        if ($user->id) {
                            // User already exists in database.
                            if (empty($user->auth)) { // For some reason auth isn't set yet.
                                $DB->set_field('user', 'auth', $auth, array ('username' => $username));
                                $user->auth = $auth;
                            }
                            update_internal_user_password($user, $password); // Just in case salt or encoding were changed (magic
                                                                             // quotes too one day).
                            if ($authplugin->is_synchronised_with_external()) { // Update user record from external DB.
                                $user = update_user_record($username);
                            }
                        }
                    } else {
                        $authpreventaccountcreation = get_config('moodle', 'authpreventaccountcreation');
                        if (empty($authpreventaccountcreation)) {
                            // Use manual if auth not set.
                            $user = create_user_record($username, $password, 'cas');
                        }
                    }
                    foreach ($authsenabled as $hau) {
                        $hauth = get_auth_plugin($hau);
                        $hauth->user_authenticated_hook($user, $username, $password);
                    }
                    return $user;
                } else {
                    return $xx;
                }

            } else if ($shib) { // Check for shiboleth.
                $cookiedir = tempnam ("/tmp", "CURLCOOKIE");
                $authplug = get_config('moodle', null);
                $url = $authplug->wwwroot . "/" .'auth/shibboleth/index.php';
                $post_data['j_username'] = $username;
                $post_data['j_password'] = $password;
                foreach ($post_data as $key => $value) {
                    $post_items[] = $key . '=' . $value;
                }
                $post_string = implode('&', $post_items);
                $curl_connection = curl_init($url);
                // Set options.
                curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
                curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl_connection, CURLOPT_HEADER, 1);
                curl_setopt($curl_connection, CURLOPT_COOKIEFILE, $cookiedir);
                curl_setopt($curl_connection, CURLOPT_COOKIEJAR, $cookiedir);
                curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl_connection, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
                // Perform our request.
                $result = curl_exec($curl_connection);
                $info = curl_getinfo($curl_connection);
                $url = $info['url'];
                $p = parse_url($url);
                $authsrvrurl = $p['scheme']."://".$p['host'].":".$p['port'];
                // Close the connection.
                curl_close($curl_connection);
                $curl_connection = curl_init($url);
                // Set options.
                curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
                curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl_connection, CURLOPT_HEADER, 1);
                curl_setopt($curl_connection, CURLOPT_COOKIEFILE, $cookiedir);
                curl_setopt($curl_connection, CURLOPT_COOKIEJAR, $cookiedir);
                curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
                curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl_connection, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
                // Perform our request.
                $result2 = curl_exec($curl_connection);
                // Close the connection.
                $info = curl_getinfo($curl_connection);
                curl_close($curl_connection);
                if ($authsrvrurl.'/idp/profile/SAML2/Redirect/SSO' == $info["url"]) {
                    $url = $authplug->wwwroot.'/Shibboleth.sso/Logout';
                    $curl_connection = curl_init($url);
                    // Set options.
                    curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
                    curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
                    curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl_connection, CURLOPT_HEADER, 1);
                    curl_setopt($curl_connection, CURLOPT_COOKIEFILE, $cookiedir);
                    curl_setopt($curl_connection, CURLOPT_COOKIEJAR, $cookiedir);
                    curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curl_connection, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
                    // Perform our request.
                    $result = curl_exec($curl_connection);
                    $info = curl_getinfo($curl_connection);
                    $url = $info['url'];
                    // Close the connection.
                    curl_close($curl_connection);
                    $f = @fopen($cookiedir, "r+");
                    if ($f !== false) {
                        ftruncate($f, 0);
                        fclose($f);
                    }
                    if ($user = get_complete_user_data('username', $username, $CFG->mnet_localhost_id)) {
                        $auth = 'shibboleth';
                        $authplugin = get_auth_plugin($auth);
                        if ($user->id) {
                            // User already exists in database.
                            if (empty($user->auth)) { // For some reason auth isn't set yet.
                                $DB->set_field('user', 'auth', $auth, array ('username' => $username));
                                $user->auth = $auth;
                            }
                            update_internal_user_password($user, $password);
                        } else {
                            if (empty($CFG->authpreventaccountcreation)) {
                                // Use manual if auth not set.
                                $user = create_user_record($username, $password, 'shibboleth');
                            }
                        }
                    }
                    foreach ($authsenabled as $hau) {
                        $hauth = get_auth_plugin($hau);
                        $hauth->user_authenticated_hook($user, $username, $password);
                    }
                    return $user;
                } else {
                    $f = @fopen($cookiedir, "r+");
                    if ($f !== false) {
                        ftruncate($f, 0);
                        fclose($f);
                    }
                    return $xx;
                }
                // End shiboleth.
            } else {
                return $xx;
            }
        } else {
            return $xx;
        }
    }
    /**
     * returns all courses for which the given user is
     * in the "teacher" role
     * 
     * @param unknown_type $user
     * @return unknown_type
     */
    public static function getinstructorcourses($user) {
        $courses = array ();
        $mycourses = enrol_get_users_courses($user->id, false);
        // Iterate through courses and verify that this user is
        // the instructor, not a student, for each course.
        foreach ($mycourses as $course) {
            // Check Version.
            if ($CFG->version >= '2013111800.00') {
                $context = context_course::instance($course->id);
            } else {
                $context = get_context_instance(CONTEXT_COURSE, $course->id);
            }
            $role_users = array ();
            $inst_roles = explode(',', TURNINGTECH_DEFAULT_TEACHER_ROLE);
            foreach ($inst_roles as $index => $roleid) {
                $role_users = array_merge($role_users, get_role_users($roleid, $context));
            }
            foreach ($role_users as $ru) {
                if ($ru->id == $user->id) {
                    $courses[] = $course;
                    break;
                }
            }
        }
        return $courses;
    }
    /**
     * returns all courses for which the given user is
     * in the "student" role
     * 
     * @param unknown_type $user
     * @return unknown_type
     */
    public static function getstudentcourses($user) {
        global $CFG;
        $courses = array ();
        $mycourses = enrol_get_users_courses($user, false);
        // Iterate through courses and verify that this user is
        // the instructor, not a student, for each course.
        foreach ($mycourses as $course) {
            //  Check Version.
            if ($CFG->version >= '2013111800.00') {
                $context = context_course::instance($course->id);
            } else {
                $context = get_context_instance(CONTEXT_COURSE, $course->id);
            }
            $role_users = array ();
            $inst_roles = explode(',', TURNINGTECH_DEFAULT_STUDENT_ROLE);
            foreach ($inst_roles as $index => $roleid) {
                $role_users = array_merge($role_users, get_role_users($roleid, $context));
            }
            foreach ($role_users as $ru) {
                if ($ru->id == $user) {
                    $courses[] = $course;
                    break;
                }
            }
        }
        return $courses;
    }
    /**
     * returns extended list of all courses for which the given user is
     * in the "teacher" role
     * 
     * @param unknown_type $user
     * @return unknown_type
     */
    public static function getextinstructorcourses($user) {
        $courses = array ();
        $mycourses = enrol_get_users_courses($user->id, false);
        // Iterate through courses and verify that this user is
        // the instructor, not a student, for each course.
        foreach ($mycourses as $course) {
            //  Check Version.
            if ($CFG->version >= '2013111800.00') {
                $context = context_course::instance($course->id);
            } else {
                $context = get_context_instance(CONTEXT_COURSE, $course->id);
            }
            $role_users = array ();
            $inst_roles = explode(',', TURNINGTECH_DEFAULT_TEACHER_ROLE);
            foreach ($inst_roles as $index => $roleid) {
                $role_users = array_merge($role_users, get_role_users($roleid, $context));
            }
            foreach ($role_users as $ru) {
                if ($ru->id == $user->id) {
                    $courses[] = $course;
                    break;
                }
            }
        }
        return $courses;
    }
    /**
     * check if user is enrolled as student in course
     * 
     * @param object $user
     * @param object $course
     * @return bool
     */
    public static function isuserstudentincourse($user, $course) {
        $found = self::getclassroster($course, false, $user->id);
        return ($found ? true : false);
    }
    /**
     * check if user is instructor for course
     * 
     * @param object $user
     * @param object $course
     * @return bool
     */
    public static function isuserinstructorincourse($user, $course) {
        global $CFG;
        if ($CFG->version >= '2013111800.00') {
            $context = context_course::instance($course->id);
        } else {
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
        }
        return has_capability('mod/turningtech:manage', $context, $user->id);
    }
    /**
     * determines whether the user has permission to view the course roster
     * 
     * @param object $user
     * @param object $course
     * @return bool
     */
    public static function userhasrosterpermission($user, $course) {
        $allowed = false;
        //  Check Version.
        if ($CFG->version >= '2013111800.00') {
            $context = context_course::instance($course->id);
        } else {
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
        }
        if ($context) {
            $allowed = has_capability('moodle/course:viewparticipants', $context, $user->id);
        }
        return $allowed;
    }
    /**
     * determines whether user has permission to create a new gradebook item in the given course
     * 
     * @param object $user
     * @param object $course
     * @return bool
     */
    public static function userhasgradeitempermission($user, $course) {
        $allowed = false;
        //  Check Version.
        if ($CFG->version >= '2013111800.00') {
            $context = context_course::instance($course->id);
        } else {
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
        }
        if ($context) {
            $allowed = has_capability('moodle/grade:manage', $context, $user->id);
        }
        return $allowed;
    }
    /**
     * fetches the class roster
     * 
     * @param object $course
     * @param unknown_type $roles array of role ids
     * @param unknown_type $userid optional id of user to quickly check if they are enrolled
     * @param unknown_type $order
     * @param unknown_type $asc
     * @param unknown_type $type
     * @return unknown_type
     */
    public static function getclassroster($course, $roles = false, $userid = false, $order = 'u.lastname',
                                     $asc = true, $type = false) {
        global $CFG, $DB;
        $params = array ();
        if (! $roles) {
            $roles = array (TURNINGTECH_DEFAULT_STUDENT_ROLE);
        }
        $sql = "SELECT u.id, u.firstname, u.lastname, u.username, u.email,
                                         d.id AS devicemapid, d.deviceid, d.deleted,
                                         d.all_courses, d.courseid, r.roleid AS Role ";
        $sql .= "FROM {user} u ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} dall ON
                                        ( dall.userid = u.id AND dall.deleted = :dalldeleted1 AND
                                         dall.all_courses = :dallcourses1 ) ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} dcrs ON
                                         ( dcrs.userid = u.id AND dcrs.deleted = :dalldeleted2 AND
                                         dcrs.all_courses = :dallcourses2 AND
                                         dcrs.courseid = :courseid1 ) ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} d ON ( ( dcrs.id IS NOT null AND
                                         dcrs.id = d.id ) OR
                                         ( dcrs.id IS null AND dall.id = d.id ) ) ";
        $sql .= "LEFT JOIN {role_assignments} r ON r.userid = u.id ";
        $sql .= "LEFT JOIN {context} c ON r.contextid = c.id ";
        $where = "r.roleid IN (" . $roles[0] . ")";
        $where .= " AND u.deleted = :udeleted AND c.contextlevel = :contextcourse
                                         AND c.instanceid = :courseid2";
        if ($userid) {
            $where .= "AND u.id= :uid ";
            $params['uid'] = $userid;
        }
        if ($type) {
            $where .= "AND d.type= :dtype ";
            $params['uid'] = $type;
        }
        $order .= ($asc ? ' ASC' : ' DESC');
        $params['dalldeleted1'] = 0;
        $params['dallcourses1'] = 1;
        $params['dalldeleted2'] = 0;
        $params['dallcourses2'] = 0;
        $params['courseid1'] = $course->id;
        $params['courseid2'] = $course->id;
        $params['contextcourse'] = CONTEXT_COURSE;
        $params['udeleted'] = 0;
        $sql = "{$sql} WHERE {$where} GROUP BY u.id, d.id, r.roleid ORDER BY  {$order}";
        return $DB->get_records_sql($sql, $params);
    }
    /**
     * fetches the extended class roster with multiple device ids.
     * 
     * @param object $course
     * @param unknown_type $roles array of role ids
     * @param unknown_type $userid optional id of user to quickly check if they are enrolled
     * @param unknown_type $order
     * @param unknown_type $asc
     * @return unknown_type
     */
    public static function getextclassroster($course, $roles = false, $userid = false, $order = 'u.lastname', $asc = true) {
        global $CFG, $DB;
        if (! $roles) {
            $roles = array (TURNINGTECH_DEFAULT_STUDENT_ROLE);
        }
        // Fix for PostgreSql compatibility.
        $sql = "SELECT u.id, u.firstname, u.lastname, u.username, u.email, dt.type AS devicetype,
                                         r.roleid AS Role ";
        $sql .= "FROM {user} u ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} dall ON (dall.userid = u.id AND
                                         dall.deleted = :dalldeleted1 AND
                                         dall.all_courses = :dallcourses1) ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} dcrs ON (dcrs.userid = u.id AND
                                         dcrs.deleted = :dalldeleted2 AND
                                         dcrs.all_courses = :dallcourses2 AND
                                         dcrs.courseid= :courseid1) ";
        $sql .= "LEFT JOIN {turningtech_device_types} dt ON dt.id = dall.typeid ";
        $sql .= "LEFT JOIN {role_assignments} r ON r.userid = u.id ";
        $sql .= "LEFT JOIN {context} c ON r.contextid = c.id ";
        $params = array ();
        $where = "r.roleid IN (" . $roles[0] . ")";
        $params['dalldeleted1'] = 0;
        $params['dallcourses1'] = 1;
        $params['dalldeleted2'] = 0;
        $params['dallcourses2'] = 0;
        $params['courseid1'] = $course->id;
        $params['courseid2'] = $course->id;
        $params['contextcourse'] = CONTEXT_COURSE;
        $params['udeleted'] = 0;
        $where .= " AND u.deleted= :udeleted AND c.contextlevel= :contextcourse AND
                                         c.instanceid= :courseid2 ";
        $groupby = "GROUP BY u.id, dt.type, r.roleid";
        if ($userid) {
            $where .= "AND u.id= :uid ";
            $params['uid'] = $userid;
        }
        $orderby = "ORDER BY :order ";
        $order = ($asc ? 'ASC' : 'DESC');
        $params['order'] = $order;
        $sql = "{$sql} WHERE {$where} {$groupby} {$orderby}";
        $classrosters = $DB->get_records_sql($sql, $params);
        $classroster = array ();
        foreach ($classrosters as $classros) {
            $sqlsub = "SELECT id AS devicemapid, deviceid, deleted, all_courses, courseid
					   FROM {turningtech_device_mapping}
					   WHERE userid = :userid AND deleted = :deleted";
            $paramssub['userid'] = $classros->id;
            $paramssub['deleted'] = 0;
            $deviceidmaps = $DB->get_records_sql($sqlsub, $paramssub);
            $varobj = new stdClass();
            $varobj->id = $classros->id;
            $varobj->firstname = $classros->firstname;
            $varobj->lastname = $classros->lastname;
            $varobj->username = $classros->username;
            $varobj->email = $classros->email;
            $varobj->deviceid = '';
            foreach ($deviceidmaps as $deviceidmap) {
                $varobj->devicemapid = $deviceidmap->devicemapid;
                if ($deviceidmap->deviceid) {
                    $varobj->deviceid .= $deviceidmap->deviceid . ',';
                }
                $varobj->deleted = $deviceidmap->deleted;
                $varobj->all_courses = $deviceidmap->all_courses;
                $varobj->courseid = $deviceidmap->courseid;
            }
            if (count($deviceidmaps) >= 1) {
                $varobj->deviceid = rtrim($varobj->deviceid, ',');
            }
            $varobj->devicetype = $classros->devicetype;
            $varobj->Role = $classros->Role;
            $classroster[$classros->id] = $varobj;
        }
        return $classroster;
    }
    /**
     * searches for users by username
     * 
     * @param string $str
     * @return unknown_type
     */
    public static function adminstudentsearch($str) {
        global $CFG, $DB;
        $roles = array (TURNINGTECH_DEFAULT_STUDENT_ROLE);
        $str = strtolower($str);
        $select = "SELECT u.id, u.firstname, u.lastname, u.username, u.email, d.id AS devicemapid,
                                         d.deviceid, d.deleted, d.all_courses, d.courseid ";
        $select .= "FROM {user} u ";
        $select .= "LEFT JOIN {turningtech_device_mapping} d ON (d.userid=u.id AND
                                         d.deleted= :dalldeleted) ";
        $select .= "LEFT JOIN {role_assignments} r ON r.userid=u.id ";
        $select .= "LEFT JOIN {context} c ON r.contextid=c.id ";
        $params = array ();
        $params['dalldeleted'] = 0;
        $params['username'] = $str;
        $params['contextcourse'] = CONTEXT_COURSE;
        $where = array ();
        $where[] = "r.roleid IN(" . $roles[0] . ")";
        $where[] = 'c.contextlevel= :contextcourse';
        $where[] = 'lower(u.username) LIKE :username';
        $wheresql = implode(' AND ', $where);
        $sql = "{$select} WHERE {$wheresql} ORDER BY u.username";
        return $DB->get_records_sql($sql, $params);
    }
    /**
     * fetches a user object by id
     * 
     * @param int $id
     * @return unknown_type
     */
    public static function getuserbyid($id) {
        return get_complete_user_data('id', $id);
    }
    /**
     * fetches a user object by username
     * 
     * @param string $username
     * @return unknown_type
     */
    public static function getuserbyusername($username) {
        global $DB;
        return $DB->get_record("user", array ("username" => $username));
    }
    /**
     * fetches the class roster
     * @param unknown_type $devicesearch
     * @param object $course
     * @param unknown_type $roles array of role ids
     * @param unknown_type $userid optional id of user to quickly check if they are enrolled
     * @param unknown_type $order
     * @param unknown_type $asc
     * @return unknown_type
     */
    public static function getsearchresult($devicesearch, $course, $roles = false, $userid = false,
                                     $order = 'u.lastname', $asc = true) {
        global $CFG, $DB;
        $params = array ();
        if (! $roles) {
            $roles = array (TURNINGTECH_DEFAULT_STUDENT_ROLE);
        }
        $sql = "SELECT u.id, u.firstname, u.lastname, u.username, u.email, d.id AS devicemapid,
                                         d.deviceid, d.deleted, d.all_courses, d.courseid, r.roleid AS Role ";
        $sql .= "FROM {user} u ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} dall ON ( dall.userid = u.id AND
                                         dall.deleted = :dalldeleted1 AND dall.all_courses = :dallcourses1 ) ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} dcrs ON ( dcrs.userid = u.id AND
                                         dcrs.deleted = :dalldeleted2 AND dcrs.all_courses = :dallcourses2 ) ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} d ON ( ( dcrs.id IS NOT null AND
                                         dcrs.id = d.id ) OR ( dcrs.id IS null AND dall.id = d.id ) ) ";
        $sql .= "LEFT JOIN {role_assignments} r ON r.userid = u.id ";
        $sql .= "LEFT JOIN {context} c ON r.contextid = c.id ";
        $where = "r.roleid IN (" . $roles[0] . ")";
        $where .= " AND u.deleted = :udeleted AND c.contextlevel = :contextcourse";
        $where .= " AND d.deviceid = :deviceid";
        if ($userid) {
            $where .= "AND u.id= :uid ";
            $params['uid'] = $userid;
        }
        $orderby = "ORDER BY :order ";
        $order .= ($asc ? ' ASC' : ' DESC');
        $params['dalldeleted1'] = 0;
        $params['dallcourses1'] = 1;
        $params['dalldeleted2'] = 0;
        $params['dallcourses2'] = 0;
        $params['deviceid'] = $devicesearch;
        $params['courseid1'] = $course->id;
        $params['courseid2'] = $course->id;
        $params['contextcourse'] = CONTEXT_COURSE;
        $params['udeleted'] = 0;
        $params['order'] = $order;
        $sql = "{$sql} WHERE {$where} GROUP BY u.id, d.id, r.roleid {$orderby}";
        return $DB->get_records_sql($sql, $params);
    }
    /**
     * fetches the class roster
     * @param unknown_type $devicesearch
     * @param object $course
     * @return unknown_type
     */
    public static function getdevicecount($devicesearch, $course) {
        global $DB;
        $sql = "SELECT dm.id, dm.deviceid ";
        $sql .= " FROM {turningtech_device_mapping} as dm ";
        $sql .= " INNER JOIN {user_enrolments} ue on ue.userid = dm.userid ";
        $sql .= " INNER JOIN {user} u ON  u.id = ue.userid ";
        $sql .= " INNER JOIN {enrol} me on me.id = ue.enrolid ";
        $sql .= " where dm.deleted = 0 AND dm.deviceid = '".$devicesearch."' AND me.courseid = $course";
        $result = $DB->get_records_sql($sql);
        return $result;
    }
    /**
     * fetches the class roster for XML
     * 
     * @param object $course
     * @param unknown_type $roles array of role ids
     * @param unknown_type $userid optional id of user to quickly check if they are enrolled
     * @param unknown_type $order
     * @param unknown_type $asc
     * @return unknown_type
     */
    public static function getclassrosterxml($course, $roles = false, $userid = false, $order = 'u.lastname', $asc = true) {
        global $CFG, $DB;
        $params = array ();
        if (! $roles) {
            $roles = array (TURNINGTECH_DEFAULT_STUDENT_ROLE);
        }
        $sql = "SELECT u.id, u.firstname, u.lastname, u.username, u.email,
        		dcrs.id AS devicemapid, d.deviceid,
        		d.deleted, d.all_courses,
        		d.courseid, r.roleid AS Role,
				(select deviceid from {turningtech_device_mapping} where userid = u.id AND
                                         typeid =1 and deleted = 0) as rcard,
        		(select deviceid from {turningtech_device_mapping} where userid = u.id AND
                                         typeid =2 and deleted = 0) as rware
        		";
        $sql .= "FROM {user} u ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} dall ON ( dall.userid = u.id AND
                                         dall.deleted = :dalldeleted1 ) ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} dcrs ON ( dcrs.userid = u.id AND
                                         dcrs.deleted = :dalldeleted2  AND
                                         dcrs.courseid = :courseid1 ) ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} d ON ( ( dcrs.id IS NOT null AND dcrs.id = d.id ) OR
                                         ( dcrs.id IS null AND dall.id = d.id ) ) ";
        $sql .= "LEFT JOIN {role_assignments} r ON r.userid = u.id ";
        $sql .= "LEFT JOIN {context} c ON r.contextid = c.id ";
        $where = "r.roleid IN (" . $roles[0] . ")";
        $where .= " AND u.deleted = :udeleted AND c.contextlevel = :contextcourse AND c.instanceid = :courseid2";
        if ($userid) {
            $where .= "AND u.id= :uid ";
            $params['uid'] = $userid;
        }
        $orderby = "ORDER BY :order ";
        $groupby = "GROUP BY :group ";
        $order .= ($asc ? ' ASC' : ' DESC');
        $group = "userid";
        $params['dalldeleted1'] = 0;
        $params['dallcourses1'] = 1;
        $params['dalldeleted2'] = 0;
        $params['dallcourses2'] = 0;
        $params['courseid1'] = $course->id;
        $params['courseid2'] = $course->id;
        $params['contextcourse'] = CONTEXT_COURSE;
        $params['udeleted'] = 0;
        $params['order'] = $order;
        $params['group'] = $group;
        $sql = "{$sql} WHERE {$where} GROUP BY u.id, dcrs.id, d.deviceid, d.deleted,
		d.all_courses, dall.created, d.courseid, r.roleid ORDER BY {$order}";
        return $DB->get_records_sql($sql, $params);
    }
    /**
     * creates a gradebook item
     * 
     * @param object  $course
     * @param unknown_type $title
     * @param unknown_type $points
     * @return unknown_type
     */
    public static function creategradebookitem($course, $title, $points) {
        // Contains possible error DTO.
        $dto = new stdClass();
        if (self::getgradebookitembycourseandtitle($course, $title)) {
            $dto->itemTitle = $title;
            $dto->errorMessage = get_string('gradebookitemalreadyexists', 'turningtech');
            return $dto;
        }
        // Create new grade item.
        $grade_item = new grade_item(array ('courseid' => $course->id), false);
        // Set parent category.
        $data = $grade_item->get_record_data();
        $parent_category = grade_category::fetch_course_category($course->id);
        $data->parentcategory = $parent_category->id;
        // Set points.
        $data->grademax = unformat_float($points);
        $data->grademin = unformat_float(0.0);
        // Set title.
        $data->itemname = $title;
        grade_item::set_properties($grade_item, $data);
        $grade_item->outcomeid = null;
        $grade_item->itemtype = TURNINGTECH_GRADE_ITEM_TYPE;
        $grade_item->itemmodule = TURNINGTECH_GRADE_ITEM_MODULE;
        $grade_item->insert();
        return false;
    }
    /**
     * updates a gradebook item
     * 
     * @param int  $id
     * @param unknown_type $data
     * @return unknown_type
     */
    public static function updategradebookitem($id, $data) {
        // Create new grade item.
        $grade_item = grade_item::fetch(array ('id' => $id));
        // Set values.
        if (is_array($data)) {
            foreach ($data as $prop => $value) {
                if ($prop == "grademax" || $prop == "grademin") {
                    $data->$prop = unformat_float($value);
                } else {
                    $data->$prop = $value;
                }
            }
            grade_item::set_properties($grade_item, $data);
			$grade_item->itemtype = TURNINGTECH_GRADE_ITEM_TYPE;
            $grade_item->itemmodule = TURNINGTECH_GRADE_ITEM_MODULE;
            $grade_item->update();
        }
        return false;
    }
    /**
     * fetches a list of all gradebook items in the course
     * 
     * @param object $course
     * @return unknown_type
     */
    public static function getgradebookitemsbycourse($course) {
        $gtree = new grade_tree($course->id, false, false);
        $items = array ();
        foreach ($gtree->top_element['children'] as $item) {
            // Do not include courses, categories, etc.
            if ($item['object']->itemmodule == TURNINGTECH_GRADE_ITEM_MODULE) {
                $items[] = $item['object'];
            }
        }
        return $items;
    }
    /**
     * fetches a gradebook item
     * 
     * @param object $course
     * @param unknown_type $title
     * @return unknown_type
     */
    public static function getgradebookitembycourseandtitle($course, $title) {
        return grade_item::fetch(array ('itemname' => $title, 'courseid' => $course->id));
    }
    /**
     * fetch a gradebook item
     * 
     * @param int $id
     * @return unknown_type
     */
    public static function getgradebookitembyid($id) {
        return grade_item::fetch(array ('id' => $id, 'itemmodule' => TURNINGTECH_GRADE_ITEM_MODULE));
    }
    /**
     * get a record from the gradebook
     * 
     * @param unknown_type $student
     * @param unknown_type $grade_item
     * @return unknown_type
     */
    public static function getgraderecord($student, $grade_item) {
        return new grade_grade(array ('userid' => $student->id, 'itemid' => $grade_item->id));
    }
    /**
     * check if the specified user already has a grade for the given item
     * 
     * @param unknown_type $user
     * @param unknown_type $grade_item
     * @return unknown_type
     */
    public static function gradealreadyexists($user, $grade_item) {
        $grade = self::getgraderecord($user, $grade_item);
        return ! empty($grade->id);
    }
    /**
     * Get course by id
     * @param unknown_type $siteid
     * @return Ambigous <mixed, stdClass, false, boolean>
     */
    public static function getcoursebyid($siteid) {
        global $DB;
        return $DB->get_record("course", array ("id" => $siteid));
    }
    /**
     * check if user is student in course
     * @param unknown_type $user
     * @param unknown_type $course
     * @return boolean
     */
    public static function isstudentincourse($user, $course) {
        global $CFG, $DB;
        $roles = array (TURNINGTECH_DEFAULT_STUDENT_ROLE);
        $sql = "SELECT ue.enrolid ";
        $sql .= "FROM {user_enrolments} ue ";
        $sql .= "INNER JOIN {user} u ON ( u.id = ue.userid AND u.id = :userid ) ";
        $sql .= "INNER JOIN {enrol} e ON ( e.id = ue.enrolid AND e.courseid = :courseid)";
        $sql .= "LEFT JOIN {role_assignments} r ON r.userid = u.id ";
        $sql .= "LEFT JOIN {context} c ON r.contextid = c.id ";
        $where = "r.roleid IN (" . $roles[0] . ")";
        $params = array ();
        $params['userid'] = $user->id;
        $params['courseid'] = $course->id;
        $sql = "{$sql} WHERE {$where}";
        $found = $DB->get_records_sql($sql, $params);
        return ($found ? true : false);
    }
}
