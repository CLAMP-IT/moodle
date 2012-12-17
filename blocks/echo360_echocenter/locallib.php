<?php

// This file is part of the Echo360 Moodle Plugin - http://moodle.org/
//
// The Echo360 Moodle Plugin is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// The Echo360 Moodle Plugin is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with the Echo360 Moodle Plugin.  If not, see <http://www.gnu.org/licenses/>.
 
/**
 * This file provides a wrapper for the EchoSystem seamless login api
 *
 * @package    block
 * @subpackage echo360_echocenter
 * @copyright  2011 Echo360 Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */

defined('MOODLE_INTERNAL') || die;
require_once("oauth_lib.php");

/**
 * This class is a wrapper around the EchoSystem Seamless login API
 *
 * @copyright 2011 Echo360 Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */
class echosystem_remote_api {

    private $baseurl = 'https://localhost:8443/';
    private $consumerkey = 'moodle';
    private $consumersecret = '';
    private $realm = '';

    private $sessionkey = '';

    /**
     * Save the baseurl, consumer key, consumer secret and realm.
     *
     * If the base url does not end in '/' add one.
     *
     * @param string - baseurl
     * @param string - consumer key
     * @param string - consumer secret
     * @param string - realm
     */
    function __construct($baseurl, $consumerkey, $consumersecret, $realm) {
        if ($baseurl != null) {
            $this->baseurl = $baseurl;
            if (!$this->baseurl[strlen($this->baseurl) -1] === '/') {
                $this->baseurl .= '/';
            }
        }
        if ($consumerkey != null) {
            $this->consumerkey = $consumerkey;
        }
        if ($consumersecret != null) {
            $this->consumersecret = trim($consumersecret);
        }
        if ($realm != null) {
            $this->realm = $realm;
        }
    }

    /**
     * Sign a request
     *
     * Returned is an array with multiple values
     * the response['success'] is a boolean to indicate a failure
     * the response['message'] is a description of any failures
     * the response['url'] is the signed url
     *
     * @param string - url to request
     * @param array - the parameters
     * @param string - the http method
     * @return array
     */
    private function sign_oauth_request($url, $params, $method) {
        $response = array('success' => false,
                            'url' => '',
                            'message' => '');
        try {
            $consumer = new echo360_oauth_consumer($this->consumerkey, $this->consumersecret, NULL);

            // empty token for 2 legged oauth
            $oauthrequest = echo360_oauth_request::from_consumer_and_token($consumer, new echo360_oauth_token('', ''), $method, $url, $params);

            $oauthrequest->sign_request(new echo360_oauth_signature_method_hmacsha1(), $consumer, NULL);

            $url = $oauthrequest->to_url();

            $response['success'] = true;
            $response['message'] = 'success';
            $response['url'] = $url;
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = print_r($e);
            $response['url'] = '';
        }
        return $response;
    }

    /**
     * Returns a curl handle set with the standard set of options required to talk to EchoSystem
     *
     * @return curl
     */
    public function get_curl_with_defaults() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        return $ch;
    }

    public function get_headers($curl, $url, $redirects=1) {
        $headers = array();
        $cookie = '';
        while ($redirects >= 0) {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            if ($cookie != '') {
                $cookieheaders = array("Cookie: $cookie");
                curl_setopt($curl, CURLOPT_HTTPHEADER, $cookieheaders);
            }

            $result = curl_exec($curl);
            $error = curl_error($curl);
            if ($error !== '') {
                $headers['error'] = $error;
                return $headers;
            }

            // add new entry to headers array
            $headers[] = array();
            $headers[count($headers) - 1]['http'] = strtok($result, "\r\n");

            $header = strtok("\r\n");
            while ($header !== false) {
                $split = explode(": ", $header, 2);
                if (count($split) > 1) {
                    $headers[count($headers) - 1][$split[0]] = $split[1];
                    // get the next url
                    if ($split[0] === "Location") {
                        $url = $split[1];
                    }
                    // get the cookies
                    if ($split[0] === "Set-Cookie") {
                        $cookie = strtok($split[1], ";");
                    }
                }
                $header = strtok("\r\n");
            }

            $redirects -= 1;
        }
        return $headers;
    }

    
    /**
     * Generate a SSO URL for this course.
     * The response is the same as sign_oauth_request above.
     *
     * @param string - username
     * @param boolean - is an instructor
     * @param string - the course id (whichever field is configured)
     * @param boolean - show a heading with branding for the course (false for iframe)
     * @return array
     */
    public function generate_sso_url($username, $is_instructor, $externalid, $show_heading) {
        // this is the url for seamless login with a redirect to the echocenter course page
        $echocenterurl = $this->baseurl . 'ess/portal/section/' . urlencode($externalid) . '?showheading=' . ($show_heading?"true":"false");
        $apiurl = $this->baseurl . 'ess/personapi/v1/' . urlencode($username) . '/session';
        $apiparams = array('redirecturl' => $echocenterurl,
                            'instructor' => ($is_instructor?'true':'false'),
                            'security-realm' => $this->realm);

        //  oauth
        $urlresponse = $this->sign_oauth_request($apiurl, $apiparams, 'GET');
        
        return $urlresponse; 
    }
    
    
}

?>
