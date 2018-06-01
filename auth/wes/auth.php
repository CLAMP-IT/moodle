<?php

require_once($CFG->libdir.'/authlib.php');

class auth_plugin_wes extends auth_plugin_base {
  private $attribs_to_sync = array('firstname','lastname','idnumber');

  function auth_plugin_wes() {
    $this->authtype = 'wes'; 
    $this->sso_url = "https://wesep.wesleyan.edu/cgi-perl/sso/sso.cgi";

  }
  function logoutpage_hook() {
    global $redirect;
    global $SESSION;
    if ($SESSION->is_wesep) {
      $redirect = "https://wesep.wesleyan.edu/cgi-perl/session.cgi";
    }
   
  }
  /* when given an ldapauth object and an array of members, writes them out to Moodle */
  function sync_users($ldapauth,$data,$clobber = 0,$key = 'idnumber') {
    global $DB;
    $result = array( 'action' => array(), 'error' => array(), 'debug' => array());

    foreach ($data as $element) {
      $user = $DB->get_record('user',array($key => $element[$key]));
      if (!is_object($user) or !$user) {
	#fallback to username
        $user = $DB->get_record('user',array('username' => $element['username']));
 	if (!$user or !is_object($user)) {
          if ($this->create_user_from_ad($ldapauth,$element['username'],$element['idnumber'])) {
            array_push($result['action'],"User " . $element['username'] ." created");
           } else {
            array_push($result['error'],"User " . $element['username'] ." could not be created");
           }
         } else {
          print "Could not find user " . $element['username'] . " by wesid " . $element['idnumber'] ." but could find by username\n"; 
          array_push($result['debug'],"User " . $element['username'] ." exists");
          $this->sync_user_from_ad($ldapauth,$user->username,$element['idnumber'],$clobber);
         }
       } else {
        array_push($result['debug'],"User " . $element['username'] ." exists");
        $this->sync_user_from_ad($ldapauth,$user->username,$element['idnumber'],$clobber);
       }
    }
    return $result;

  }
  /* given ldapauth object, username and optional clobber, syncs data from AD over, clobbering if clobber is 1; username is only unique guarantee in AD */
  function sync_user_from_ad($ldapauth,$username,$wesid,$clobber=1) {
    global $DB,$CFG;
    $user = $ldapauth->get_userinfo_asobj(addslashes($username));
    if (!$user->idnumber && $wesid) {
      $user->idnumber = $wesid;
    }
    $update_user = 0;
    $result = 0;
    $moodle_user = $DB->get_record('user',array('username' => $username));
    foreach ($this->attribs_to_sync as $attribute) {
      if ($clobber) {
	$moodle_user->$attribute = $user->$attribute;
	$update_user = 1;
      } else {
        if ((isset($user->$attribute) and $user->$attribute) 
        and (!isset($moodle_user->$attribute) or !$moodle_user->$attribute)) {
	  $moodle_user->$attribute = $user->$attribute;
	  $update_user = 1;
        }
      }
    }
    if ($update_user) {
      $result = $DB->update_record('user',$moodle_user);
      return $result;
    }
  } 
 /*when given the global ldapauth object and username, create new Moodle user from AD object */
  function create_user_from_ad ($ldapauth,$username) {

    global $DB,$CFG;
    $user = $ldapauth->get_userinfo_asobj(addslashes($username));
    if (!$user->lastname) {
      return 0;
    }
    $user->modified = time();
    $user->confirmed = 1;
    $user->auth = 'cas';
    $user->mnethostid = $CFG->mnet_localhost_id;
    $user->username = trim(core_text::strtolower($username));
    #AD lies sometimes, so hard code it to be username for now
    $user->email = $user->username . "@wesleyan.edu";
    $user->trackforums = 1;
    $user->autosubscribe = 0;
    if (empty($user->lang)) {
      $user->lang = $CFG->lang;
    }
    #$user = addslashes_recursive($user);
    $id = $DB->insert_record('user',$user);
    if ($id) {
      return 1;
    } else {
      return 0;
    }
  }

  function user_login($username, $password) {
    global $SESSION;
    if (isset($SESSION->_cached_password) && isset($SESSION->_cached_username) && $password == $SESSION->_cached_password  && $_COOKIE['enc_token'] && $username == $SESSION->_cached_username ) {
        unset($SESSION->_cached_password);
        unset($SESSION->_cached_username);
         return true;
    }
    #alright, if this doesn't work test 'em with ldap
    $ldap = get_auth_plugin('ldap');
    return $ldap->user_login($username,$password);
  }
  function is_internal() {
	return false;
  }
  function can_change_password() {
      return false;
  }

  function wes_sso( $enc_token = null, $ipaddr = null ) {
      $data = array( 'enc_token' => $enc_token,
		     'ipaddress' => $ipaddr ); 
      $ch = curl_init($this->sso_url);
      curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch,CURLOPT_POST,true);
      curl_setopt($ch,CURLOPT_HEADER,true);
      curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
      curl_setopt($ch,CURLOPT_TIMEOUT,5);
      $result = curl_exec($ch);
      curl_close($ch);	
      preg_match('/X-wes-username: (\w+)/', $result,$matches);
     #matches1 is first ()
    if ($matches[1] == "NULL" ) {
      return NULL;
    } else {
      return $matches[1];
    }
  }


  function loginpage_hook() {
    global $frm;
    global $CFG;
    global $SESSION;
    if (isset($_COOKIE['enc_token'])) {
      $username = $this->wes_sso($_COOKIE['enc_token'],$_SERVER['REMOTE_ADDR']);
      if ($username) {
        $frm->username = $username;
        $frm->password = generate_password();
        $SESSION->_cached_username = $frm->username;
        $SESSION->_cached_password = $frm->password;
        $SESSION->is_wesep = 1;
      }
    }
    return;
  }
}
