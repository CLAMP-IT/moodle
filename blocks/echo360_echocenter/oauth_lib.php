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
//
// This file is originally from http://oauth.googlecode.com/svn/code/php/
// It has been modified to work with the EchoSystem Rest API
//
// The original license is listed below:
// 
// The MIT License
// 
// Copyright (c) 2007 Andy Smith
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
 
/**
 * This file contains functions for calling OAuth protected resources
 *
 * @package    block                                                    
 * @subpackage echo360_echocenter
 * @copyright  2011 Echo360 Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */

defined('MOODLE_INTERNAL') || die;

/* 
 * This is a generic OAuth consumer class
 *
 * @copyright 2011 Echo360 Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */
class echo360_oauth_consumer {
    public $key;
    public $secret;

    /**
     * Store the key and secret and optionally the callbackurl (not used by Echo360)
     *
     * @param string - oauth consumer key
     * @param string - oauth consumer secret
     * @param string - callback url only used for 3 legged oauth
     */
    function __construct($key, $secret, $callback_url=NULL) {
        $this->key = $key;
        $this->secret = $secret;
        $this->callback_url = $callback_url;
    }
}

/* 
 * This is a generic OAuth Token class
 *
 * @copyright 2011 Echo360 Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */
class echo360_oauth_token {
    // access tokens and request tokens
    public $key;
    public $secret;
  
    /**
     * Store the key and secret
     * 
     * @param string - oauth token key
     * @param string - oauth token secret
     */
    function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * Generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     *
     * @return string
     */
    function to_string() {
        return "oauth_token=" . echo360_util::urlencodeRFC3986($this->key) . 
            "&oauth_token_secret=" . echo360_util::urlencodeRFC3986($this->secret);
    }

    /**
     * Generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     *
     * @return string
     */
    function __toString() {
        return $this->to_string();
    }
}

/* 
 * This is a generic class for OAuth signature methods (we use HMACSHA1)
 *
 * @copyright 2011 Echo360 Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */
class echo360_oauth_signature_method {
  
    /**
     * Generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     *
     * @param echo360_oauth_request  - The request
     * @param echo360_oauth_consumer - Contains the secret and key
     * @param echo360_oauth_token    - Can be null
     * @param string               - The signature to check
     * @return string
     */
    public function check_signature(&$request, $consumer, $token, $signature) {
        $built = $this->build_signature($request, $consumer, $token);
        return $built == $signature;
    }
}

/* 
 * This class implements HMACSHA1 auth
 *
 * @copyright 2011 Echo360 Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */
class echo360_oauth_signature_method_hmacsha1 extends echo360_oauth_signature_method {/*{{{*/

    /**
     * Return the name of this signature method. This is included as a paramenter
     * in OAuth requests so it has to be exact.
     *
     * @return string
     */
    function get_name() {
        return "HMAC-SHA1";
    }

    /**
     * Return the request signature encrypted with hash_hmac.
     *
     * @param echo360_oauth_request  - The OAuth request we are creating
     * @param echo360_oauth_consumer - The OAuth consumer contains the token and secrets
     * @param OAuthToken           - The OAuth token - can be empty for 2 legged OAuth
     * @return string
     */
    public function build_signature($request, $consumer, $token) {
        $base_string = $request->get_signature_base_string();
        $request->base_string = $base_string;
  
        $key_parts = array(
            $consumer->secret,
            ($token) ? $token->secret : ""
        );

        $key_parts = array_map(array('echo360_util','urlencodeRFC3986'), $key_parts);
  
        $key = implode('&', $key_parts);

        return base64_encode( hash_hmac('sha1', $base_string, $key, true));
    }
}

/* 
 * This class is used to construct an OAuth request
 *
 * @copyright 2011 Echo360 Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */
class echo360_oauth_request {
    private $parameters;
    private $http_method;
    private $http_url;
    public $base_string;
    public static $version = '1.0';
  
    /**
     * Constructor for this class.
     *
     * @param string - "GET|POST|DELETE|PUT" (for REST)
     * @param string - The URL of the request
     * @param array  - Array of parameters for the request
     */
    function __construct($http_method, $http_url, $parameters=NULL) {
        @$parameters or $parameters = array();
        $this->parameters = $parameters;
        $this->http_method = $http_method;
        $this->http_url = $http_url;
    }


    /**
     * Pretty much a helper function to set up the request
     *
     * @param echo360_oauth_consumer - Contains the OAuth secret and key
     * @param echo360_oauth_token    - The token for the request (can be null)
     * @param string                 - "GET|POST|DELETE|PUT" (for REST)
     * @param string                 - The URL of the request
     * @param array                  - Array of parameters for the request
     * @return echo360_oauth_request
     */
    public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL) {
        @$parameters or $parameters = array();
        $defaults = array("oauth_version" => echo360_oauth_request::$version,
                      "oauth_nonce" => echo360_oauth_request::generate_nonce(),
                      "oauth_timestamp" => echo360_oauth_request::generate_timestamp(),
                      "oauth_consumer_key" => $consumer->key);
        $parameters = array_merge($defaults, $parameters);

        if ($token) {
            $parameters['oauth_token'] = $token->key;
        }
        return new echo360_oauth_request($http_method, $http_url, $parameters);
    }

    /**
     * Add a parameter to the request
     *
     * @param string - Param name
     * @param string - Param value
     */
    public function set_parameter($name, $value) {
        $this->parameters[$name] = $value;
    }

    /**
     * Get a parameter from the request
     *
     * @param string - Param name
     * @return string
     */
    public function get_parameter($name) {
        return $this->parameters[$name];
    }

    /**
     * Get a list of request parameters
     *
     * @return array
     */
    public function get_parameters() {
        return $this->parameters;
    }

    /**
     * Returns the normalized parameters of the request
     * 
     * This will be all (except oauth_signature) parameters,
     * sorted first by key, and if duplicate keys, then by
     * value.
     *
     * The returned string will be all the key=value pairs
     * concated by &.
     * 
     * @return string
     */
    public function get_signable_parameters() {
        // Grab all parameters
        $params = $this->parameters;
		
        // Remove oauth_signature if present
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }
		
        // Urlencode both keys and values
        $keys = array_map(array('echo360_util', 'urlencodeRFC3986'), array_keys($params));
        $values = array_map(array('echo360_util', 'urlencodeRFC3986'), array_values($params));
        $params = array_combine($keys, $values);

        // Sort by keys (natsort)
        uksort($params, 'strnatcmp');

        // Generate key=value pairs
        $pairs = array();
        foreach ($params as $key=>$value ) {
            if (is_array($value)) {
                // If the value is an array, it's because there are multiple 
                // with the same key, sort them, then add all the pairs
                natsort($value);
                foreach ($value as $v2) {
                    $pairs[] = $key . '=' . $v2;
                }
            } else {
                $pairs[] = $key . '=' . $value;
            }
        }
		
        // Return the pairs, concated with &
        return implode('&', $pairs);
    }

    /**
     * Returns the base string of this request
     *
     * The base string defined as the method, the url
     * and the parameters (normalized), each urlencoded
     * and the concated with &.
     */
    public function get_signature_base_string() {
        $parts = array(
            $this->get_normalized_http_method(),
            $this->get_normalized_http_url(),
            $this->get_signable_parameters()
        );

        $parts = array_map(array('echo360_util', 'urlencodeRFC3986'), $parts);
  
        return implode('&', $parts);
    }

    /**
     * Just uppercases the http method
     *
     * @return string
     */
    public function get_normalized_http_method() {
        return strtoupper($this->http_method);
    }

    /**
     * Parses the url and rebuilds it to be
     * scheme://host/path
     *
     * @return string
     */
    public function get_normalized_http_url() {
        $parts = parse_url($this->http_url);

        $port = @$parts['port'];
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $path = @$parts['path'];

        $port or $port = ($scheme == 'https') ? '443' : '80';

        if (($scheme == 'https' && $port != '443')
              || ($scheme == 'http' && $port != '80')) {
            $host = "$host:$port";
        }

        return "$scheme://$host$path";
    }

    /**
     * Builds a url usable for a GET request. 
     * ie puts the params on the query string
     *
     * @return string
     */
    public function to_url() {
        $out = $this->get_normalized_http_url() . "?";
        $out .= $this->to_postdata();
        return $out;
    }

    /**
     * Builds the data one would send in a POST request
     *
     * @return string
     */
    public function to_postdata() {
        $total = array();
        uksort($this->parameters, array('echo360_util', 'echo360cmp'));
        foreach ($this->parameters as $k => $v) {
            $total[] = echo360_util::urlencodeRFC3986($k) . "=" . echo360_util::urlencodeRFC3986($v);
        }
        $out = implode("&", $total);
        return $out;
    }

    /**
     * Builds the Authorization: header
     *
     * @return string
     */
    public function to_header() {
        $out ='Authorization: OAuth ';
        $first = true;
        $total = array();
        foreach ($this->parameters as $k => $v) {
            if (substr($k, 0, 5) != "oauth") {
                continue;
            }
            if (!$first) {
                $out .= ',';
            }
            $out .= echo360_util::urlencodeRFC3986($k) . '="' . echo360_util::urlencodeRFC3986($v) . '"';
            $first = false;
        }
        return $out;
    }

    /**
     * Returns this url as a string
     *
     * @return string
     */
    public function __toString() {
        return $this->to_url();
    }

    /**
     * Appends the OAuth signature parameter based on the values of all the other variables
     *
     * @param string                 - HTTP method
     * @param echo360_oauth_consumer - Contains the consumer key and secret
     * @param echo360_oauth_token    - Optional - for 3 legged oauth
     */
    public function sign_request($signature_method, $consumer, $token) {
        $this->set_parameter("oauth_signature_method", $signature_method->get_name());
        $signature = $this->build_signature($signature_method, $consumer, $token);
        $this->set_parameter("oauth_signature", $signature);
    }

    /**
     * Build the string that is encrypted to sign a request
     *
     * @param string                 - HTTP method
     * @param echo360_oauth_consumer - Contains the consumer key and secret
     * @param echo360_oauth_token    - Optional - for 3 legged oauth
     * @return string
     */
    public function build_signature($signature_method, $consumer, $token) {
        $signature = $signature_method->build_signature($this, $consumer, $token);
        return $signature;
    }

    /**
     * Util function: current timestamp
     *
     * @return int
     */
    private static function generate_timestamp() {
        return time();
    }

    /**
     * Util function: current nonce
     *
     * @return string
     */
    private static function generate_nonce() {
        $mt = microtime();
        $rand = mt_rand();

        return md5($mt . $rand); // md5s look nicer than numbers
    }

}

/* 
 * This class contains some util functions for oauth
 *
 * @copyright 2011 Echo360 Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */
class echo360_util {
  
    /**
     * Special URL encoding for OAuth - includes + and ~ escaping
     *
     * @param string
     * @return string
     */
    public static function urlencodeRFC3986($string) {
        return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode($string)));
    }
    
    /**
     * EchoSystem expects the first parameter to always be redirecturl (for seamless login)
     * So if you sort the parameters alphabetically, you need to make a special case for 
     * redirecturl.
     *
     * @param string
     * @param string
     * @return int
     */
    public static function echo360cmp($a, $b) {
        if ($a === 'redirecturl' && $a !== $b) {
            return -1;
        }
        if ($b === 'redirecturl' && $a !== $b) {
            return 1;
        }
    
        return strnatcmp($a, $b);
    }
    
}

?>
