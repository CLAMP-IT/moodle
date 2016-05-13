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
 * This is a class containing static functions for general PoodLL filter things
 * like embedding recorders and managing them
 *
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class licensemanager
{
    const FILTER_POODLL_IS_UNLICENSED = 0;
    const FILTER_POODLL_IS_EXPIRED = 1;
    const FILTER_POODLL_IS_VALID = 2;
    const FILTER_POODLL_IS_OTHER = 3;
    const FILTER_POODLL_IS_UNAVAILABLE = 4;
    const FILTER_POODLL_GUMROAD_TOKEN = 4;
    const FILTER_POODLL_CURL_TIMEOUT = 5000;
    const FILTER_POODLL_GUMROAD_PRODUCT = 'ABCDFY';
    const FILTER_POODLL_MD5KEY = 'wotlookhere4NGgoWay';

    private $lk = false;


    /**
     * Check the registration key is valid
     * This is just a temporary pre-license enforcement thing
     *  Technically registration and being licensed are different
     * But eventually users will require a license key,
     * which will be the same as their registration key
     * For now we just have a very trusting requirement to register
     * Later those early registrants will gain a much better deal on licenses
     *
     */
    public function validate_registrationkey($regkey)
    {
        if(empty($regkey)){return false;}
        $regkey = trim($regkey);
        if(empty($regkey)){return false;}
        $regkey=strtolower($regkey);
        return md5(self::FILTER_POODLL_MD5KEY) == $regkey;
    }

    /**
     * Loads license key
     *
     */
   public function fetch_license_key()
    {
        if (!$this->lk) {
            $this->lk = get_config('filter_poodll', 'licensekey');
        }
        return $this->lk;
    }

    public function validate_license_key(){
        $gum_raw_response = ping_gumroad();
        $gumresponse = false;
        if($gum_raw_response){
            $gumresponse = json_decode($gum_raw_response);
        }

        //is the response a data object we can use
        if(!$gumresponse || !$gumresponse->success){
            return self::FILTER_POODLL_IS_UNAVAILABLE;
        }

        //is the response indicates a bad license key
        if(!$gumresponse->success){
            return self::FILTER_POODLL_IS_UNLICENSED;
        }

        //is the subscription still valid?
        $today = new DateTime('now');
        $startdate = substr($gumresponse->created_at,1,10);
        $interval = date_diff($startdate, date('YYYY-MM-DD'));
        if($interval > 365){
            return self::FILTER_POODLL_IS_EXPIRED;
        }

        //lets assume its valid
        return self::FILTER_POODLL_IS_VALID;

    }

    public function ping_gumroad(){
        # Build request
        $url = "https://api.gumroad.com/v2/licenses/verify";
        $method = "POST";
        $ch   = curl_init();
        $params['product_permalink'] = self::FILTER_POODLL_GUMROAD_PRODUCT;
        $params['license_key'] = $this->fetch_license_key();

        $query = http_build_query($params);
        switch ( $method) {
            case 'HEAD':
                curl_setopt($ch, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                if ( !empty($query) ) {
                    $url = $url . '?' . $query;
                }
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
                break;
            default:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
                break;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::FILTER_POODLL_CURL_TIMEOUT);
        # End of build request
        $response = curl_exec($ch);

        # Check response for cURL errors
        $curlError = curl_error($ch);
        if ( $curlError ) {
            throw new Gumroad_Exception($curlError);
        }
        # Convert JSON response to an associative array
        # and append the HTTP status code
        $response = json_decode($response, TRUE);
        $response['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $response;
    }

    public function fetch_unregistered_content()
    {
        return \html_writer::div(get_string('unregistered','filter_poodll'),'filter_poodll_unregistered');
    }

}