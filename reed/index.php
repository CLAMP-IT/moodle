<?php

require_once('../config.php');

// If HTTP_X_FORWARDED_USER is set we're behind a proxy.
if (isset($_SERVER['HTTP_X_FORWARDED_USER'])) {
  $ruser = $_SERVER['HTTP_X_FORWARDED_USER'];
 } else {
  $ruser = $_SERVER['REMOTE_USER'];
 }

$allowed = array('benp', 'meinzerj', 'marmaret', 'salzberg', 'palomint', 'lindseyw', 'smitht', 'dlandau', 'bottk');

if(!in_array($ruser, $allowed)) {
  header('Location: https://moodle.reed.edu');
  die();
}

function rc_user_exists($uid) {
  $c = curl_init();

  curl_setopt($c, CURLOPT_URL, 'https://porthole.reed.edu/api/accounts/' . $uid . '.xml');
  curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($c, CURLOPT_USERAGENT, 'Moodle+CoSign/0.3');
  curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

  $xml = curl_exec($c);

  return (curl_getinfo($c, CURLINFO_HTTP_CODE) == 200);
}

if($_POST && $_POST['add']) {
  $msgs   = array();
  $people = explode(' ', $_POST['add']);

  global $DB; 

  foreach($people as $uid) {
    if($user = $DB->get_record('user', array('username' => $uid))) {
      $msgs[] = "$uid already exists in Moodle";
    } else {
      if(rc_user_exists($uid)) {
        if($user = create_user_record($uid, '', 'ldap')) {
          $msgs[] = "Imported $uid from LDAP";
        }
      } else {
        $msgs[] = "There is no $uid in LDAP";
      }
    }
  }
}
?>
<html>
 <head>
  <title>Manual LDAP import</title>
 </head>
 <body>
  <?php if (isset($msgs)): ?>
  <pre>
   <?php print_r($msgs); ?>
  </pre>
  <?php endif; ?>
  <form method="POST" action="index.php">
   <p><label>Enter usernames separated by spaces:</label>&nbsp;<input type="text" name="add" size="32"/>&nbsp;<input type="submit" name="action" value="Add to Moodle"/></p>
  </form>
 </body>
</html>
