<?php

  //
  // Utility functions for A.nnotate API.
  // 
  // Functions:
  //   hex2b64 --- convert a string to base64
  //   signRequest 


  // for the Crypt_HMAC digest code for signing requests.
require_once("HMAC.php");


// for json_encode and json_decode, in case php_json module not installed.
require_once("JSON.php");

// Default path for curl (can override in test_config,php)
$curl = "/usr/bin/curl";
$win32exe = "";

// Get config settings
// require_once("test_config.php");



// Convert a string to base64
function hex2b64($str) {
  $raw = '';
  for ($i=0; $i < strlen($str); $i+=2) {
    $raw .= chr(hexdec(substr($str, $i, 2)));
  }
  return base64_encode($raw);
}


//
// Return a GET string signed for request,
// e.g. 'api-user=joe@textensor& ... &api-auth=abc123'
//
// phpfn         : the method called, e.g. 'listDocuments.php'.
// apiuser       : the email of the licensed API user, e.g. 'joe@example.com'.
// apikey        : the secret key used to sign the request.
// annotateuser  : the a.nnotate account being requested, e.g. 'jill@example.com'
// validfor      : the extra number of seconds this req is valid for
//
// Returns: a GET string to append to the request.
//
function signRequest( $phpfn, $apiuser, $apikey, $annotateuser, $validfor=0) {

  // Include the timestamp.
  $requesttime = time() + $validfor;

  $stringToSign = "$phpfn\n$apiuser\n$requesttime\n$annotateuser";
  $t=new Crypt_HMAC($apikey, "sha1");
  $hasher =& $t;
  $signature = hex2b64($hasher->hash($stringToSign));

  return "api-user=".rawurlencode($apiuser)."&api-requesttime=".$requesttime."&api-annotateuser=".rawurlencode($annotateuser)."&api-auth=".rawurlencode($signature);
}
 
function signString($apikey, $msg) {
  $t=new Crypt_HMAC($apikey, "sha1");
  $hasher =& $t;
  $signature = hex2b64($hasher->hash($msg));
  return $signature;
}

function getIfSet($ary, $fld) {
  if (isset($ary[$fld])) { return $ary[$fld]; }
  return "";
}

// strip post slashes if added
function stripPostSlashes() {
  global $_POST;
  if (get_magic_quotes_gpc()) {
    foreach ($_POST as $k=>$v) {
      $_POST[$k] = stripslashes($v);
    }  
  }
}

// strip get slashes if added
function stripGetSlashes() {
  global $_GET;
  if (get_magic_quotes_gpc()) {
    foreach ($_GET as $k=>$v) {
      $_GET[$k] = stripslashes($v);
    }  
  }
}

//
// Check if a signed request is valid.
// $phpfn - name of the php script, e.g. 'fetchDocuments.php'
// $get - the $_GET array.
// $apikey - the secret key for the apiuser.
//
// Returns: 
// OK  if ok
// ERR + msg if not.
function checkRequest($phpfn, $apiuser, $apikey, $get) {
  $apiuser = getIfSet($get, "api-user");
  $requesttime = getIfSet($get, "api-requesttime");
  $annotateuser = getIfSet($get, "api-annotateuser");
  $apiauth = getIfSet($get, "api-auth");

  $stringToSign = "$phpfn\n$apiuser\n$requesttime\n$annotateuser";
  
  $nowtime = time();
 
  $t=new Crypt_HMAC($apikey, "sha1");
  $hasher =& $t;
  $signature = hex2b64($hasher->hash($stringToSign));
  
  if ($signature == $apiauth) {
    if ($nowtime - $requesttime < 180) {
      // Request valid for 3 minutes to allow for clock skew
      return "OK";
    }
    else {
      return "ERR - authorization expired - check clock settings on server (request date: $requesttime, server time: $nowtime)";
    }
  }
  else {
    return "ERR - signature not valid";
  }

}


//
// Do a HTTP POST using curl
// params provided as php array( field => val ) etc not URI encoded.
// files provided as php array( field => filename ) 
// val can be @filename to post a file.
// Writes params to a temp file - so the web user needs
// write access to the system temp dir.
// If files supplied, puts all params on command line, so limited
// to 8kb param length limit on windows, also " chars replaced with '
function doPost($url, $params, $files=false) {
  global $curl, $win32exe;
  $curlcmd = $curl." ";
  $tempfile = "";

 
  // For file upload, use curl -F :
  if ($files) {
    foreach ($files as $k => $v) {
      // sanitize the filename just in case:
      $v = strtr($v, "\"&'`;", "______");
      $curlcmd .= " -F \"$k=@$v\"";
    }
    foreach ($params as $k => $v) {
      $v = strtr($v, "\"", "'"); // to avoid cmd line problems
      $curlcmd .= " --form-string \"$k=$v\"";
    }
  }
  else if ($params) {  
    // For simple post, use -d :
    $tempfile = tempnam( "tmp", "api");      
    $uparams = array();
    foreach ($params as $k => $v) {
      $uparams[$k] = "$k=".rawurlencode($v);
    }
    
    $fp = fopen($tempfile, "w");
    fwrite($fp, implode("&", $uparams));
    fclose($fp);

    $curlcmd .= " -d \"@$tempfile\"";
  }

  $curlcmd .= " \"$url\" -s -o -";
  if ($win32exe) {
    // work-around for windows quoting bugs
    $curlcmd = "echo off & $curlcmd --libcurl libcurl.tmp";
  }

  //  print "Running: ".$curlcmd."\n";
    
  $retary =array();
  $retval = 0;
  exec($curlcmd, $retary, $retval);

  // Remove temp file if present
  // print file_get_contents($tempfile);
  if ($tempfile && file_exists($tempfile)) { unlink($tempfile); }

  return implode("\n", $retary);
}


?>