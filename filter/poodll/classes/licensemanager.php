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

namespace filter_poodll;

defined('MOODLE_INTERNAL') || die();

/**
 *
 * This is a class containing functions for managing/checking licenses
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class licensemanager
{
    const FILTER_POODLL_IS_REGISTERED = 0;
    const FILTER_POODLL_IS_UNREGISTERED = 1;
    const FILTER_POODLL_IS_EXPIRED = 2;
    
    const FILTER_POODLL_LICENSE_INDIVIDUAL = 2512;
    const FILTER_POODLL_LICENSE_INSTITUTION = 2511;
    const FILTER_POODLL_LICENSE_FREETRIAL = 2583;

    private $registered_url='';
    private $validated = false;
    private $cloud_access_key='';
    private $cloud_access_secret='';
    private $expire_date='';
    private $license_type=0;
    
     
    /**
     * Check the registration key is valid
     *
     *
     */
    public function get_cloud_access_key($regkey)
    {
       if(empty($this->cloud_access_key)){
            if(empty($regkey)){return false;}
            $decrypted = $this->decrypt_registration_key($regkey);
            $this->parse_decrypted_data($decrypted);
        }
        return $this->cloud_access_key;
      
    }
    
    /**
     * Check the registration key is valid
     *
     *
     */
    public function get_cloud_access_secret($regkey)
    {
        if(empty($this->cloud_access_secret)){
            if(empty($regkey)){return false;}
            $decrypted = $this->decrypt_registration_key($regkey);
            $this->parse_decrypted_data($decrypted);
        }
        return $this->cloud_access_secret;
    }
    
     /**
     * Fetch license details in display form
     *
     *
     */
    public function fetch_license_details(){
    	$details = new \stdClass();
    	$details->expire_date = $this->expire_date;
    	switch($this->license_type){
    		case self::FILTER_POODLL_LICENSE_FREETRIAL:
    			$details->license_type='Free Trial';
    			break;
    		case self::FILTER_POODLL_LICENSE_INDIVIDUAL:
    			$details->license_type='Individual';
    			break;
    		case self::FILTER_POODLL_LICENSE_INSTITUTION:
    			$details->license_type='Institution';
    			break;
    		default:
    			$details->license_type="";
    	}
    	$details->registered_url = $this->registered_url;
    	return $details;
    }

    /**
     * Check the registration key is valid
     *
     *
     */
    public function validate_registrationkey($regkey)
    {
        global $CFG;
        
        if($this->validated){return self::FILTER_POODLL_IS_REGISTERED;}
        if(empty($regkey)){return self::FILTER_POODLL_IS_UNREGISTERED;}
        if(empty($this->registered_url)){
            $decrypted = $this->decrypt_registration_key($regkey);
            $this->parse_decrypted_data($decrypted);
        }
        //if we still have no url return false
        if(empty($this->registered_url)){
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }
        
        //if we are expired or have no expiry, return false
        if(empty($this->expire_date)){
            return self::FILTER_POODLL_IS_EXPIRED;
        }
        $expire_time = strtotime($this->expire_date);
        $diff = $expire_time - time();
        /*
        echo ($this->expire_date . ' | ');
        echo (date("D M j G:i:s T Y", time()) . ' | ');
        echo($expire_time . ' | ' . time() . ' | ' . $diff); 
       */
        if($diff < 0){return self::FILTER_POODLL_IS_EXPIRED;}
        
        //get arrays of the wwwroot and registered url
        //just in case, lowercase'ify them
        $thewwwroot =  strtolower($CFG->wwwroot);
        $theregisteredurl =  strtolower($this->registered_url);
        $theregisteredurl =trim($theregisteredurl);
        $wwwroot_bits = parse_url($thewwwroot);
        $registered_bits = parse_url($theregisteredurl);
        
        //if neither parsed successfully, that a no straight up
        if(!$wwwroot_bits || ! $registered_bits){
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }
        
        //get the subdomain widlcard address, ie *.a.b.c.d.com
         $wildcard_subdomain_wwwroot='';
        if(array_key_exists('host',$wwwroot_bits)){
            $wildcardparts = explode('.',$wwwroot_bits['host']);
            $wildcardparts[0]='*';
            $wildcard_subdomain_wwwroot = implode('.',$wildcardparts);
        }else{    
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }
        
        //match either the exact domain or the wildcard domain or fail
        if(array_key_exists('host', $registered_bits)){
            //this will cover exact matches and path matches
            if($registered_bits['host'] === $wwwroot_bits['host']){
                $this->validated = true;
                return self::FILTER_POODLL_IS_REGISTERED;
            //this will cover subdomain matches but only for institution license
            }elseif(($registered_bits['host']=== $wildcard_subdomain_wwwroot) &&
                        $this->license_type==self::FILTER_POODLL_LICENSE_INSTITUTION){
                 $this->validated = true;
                return self::FILTER_POODLL_IS_REGISTERED;
            }else{
                return self::FILTER_POODLL_IS_UNREGISTERED;
            }
        }else{
            return self::FILTER_POODLL_IS_UNREGISTERED;
        }
    }

    protected function parse_decrypted_data($decrypted_data){
       // print_r($decrypted_data);
       // die;
            $delim = '+@@@@@@+';
            $parts = explode($delim,$decrypted_data);
            if(count($parts)>4){
                $this->registered_url=$parts[0];
                $this->cloud_access_key=$parts[1];
                $this->cloud_access_secret=$parts[2];
                $this->license_type=$parts[3];
                $this->expire_date=$parts[4];
            }
    }
  

    public function fetch_unregistered_content($registration_status)
    {
        switch($registration_status){
            case self::FILTER_POODLL_IS_EXPIRED:
                    $thereason =get_string('expired','filter_poodll');
                    break;
            case self::FILTER_POODLL_IS_UNREGISTERED:
            default:
                    $thereason =get_string('unregistered','filter_poodll');
        }
        return \html_writer::div($thereason,'filter_poodll_unregistered');
            
    }
    
        /* PoodLL URL + data decryption */
    public function decrypt_registration_key($encrypted){
            $decrypted = ""; // holds text which was decrypted by the public key after being encrypted with the private key, should be same as $tocrypt
            $pubkey = self::fetch_public_key();
            $base64decrypted = base64_decode($encrypted);
            openssl_public_decrypt($base64decrypted, $decrypted, $pubkey);
            return $decrypted;
    }
    
    
/* PoodLL public key */
function fetch_public_key(){
$pubcert="-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArqaQv3yajo7dUbvxCqgA
qcb7ZBp+oUZ5PbCE36q8Fm4dI6VYd6ihuAZmQKfMJqkD6f6ZupxW7mIl6YUW6Hjf
vIQb9c+ZRQ4p5L1foQ/MB9oFaJJvZE0tb70taXO5sQzvA+3odvqjWtqZ7fS06ILC
qlaT3jAOvzOYs0B6dqE8XBPJxagGB2/OGvxtN3yAMCHQ3tNIOS85I9dkCK6tbHyK
R/WfJ67egRWgeJ83JbEEuCXUOIKXYFu5HQf0FJEQWZiwHN5h9fSS7POIhM2P9y/F
YtSPP2ag4FsnLMCzC6bt0bxEnCmHoJcr3JmX1lspnqw2OGnPUjX8JeP7+yon2Bpo
gQIDAQAB
-----END PUBLIC KEY-----
";
$pubkey = openssl_get_publickey($pubcert); 
return $pubkey;
}


}