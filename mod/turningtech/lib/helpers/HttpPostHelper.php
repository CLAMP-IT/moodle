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
 * Helper for support of HTTP Post-related functionality.
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 **/

/**
 * utility function that handles HTTP POST with AES encrypted data in RW format
 * @param string $provider string base URL for HTTP POST
 * @param string $username string plaintext user name
 * @param string $password string password
 * @param unknown_type $passismd5ed boolean is the password already MD5ed, defaults to false
 * @return string the HTTP response
 */
function turningtech_dopostrw($provider, $username, $password, $passismd5ed = false) {
    $retval = '';
    if (ini_get('allow_url_fopen') == '1') {
        $url = $provider;
        if ($url[strlen($url) - 1] != '/') {
            $url .= '/';
        }
        $url .= 'rww.aspx';
        if (!$passismd5ed) {
            $password = md5($password, true);
        }
        $password = base64_encode($password);
        $data     = $username . '|' . $password;
        $data     = turningtech_encryptrwstring($data);
        $data     = base64_encode($data);
        $data     = 'LOGIN|' . $data;
        $stream   = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'content' => $data
            )
        ));
        $file     = @fopen($url, 'r', false, $stream);
        if ($file !== false) {
            $response = stream_get_contents($file);
            if ($response !== false) {
                $pieces = explode('|', $response, 2);
                if (count($pieces) == 2) {
                    if (!in_array($pieces[0], array(
                        'ERROR',
                        'LOGOUT'
                    ))) {
                        $retval = $pieces[0];
                    } else {
                        throw new TurningTechHttpPostHelperException('stream_get_contents() returned an error value: ' . $response);
                    }
                } else {
                    throw new TurningTechHttpPostHelperException('stream_get_contents() returned an unexpected value: ' .
                                                     $response);
                }
            } else {
                throw new TurningTechHttpPostHelperIOException('stream_get_contents() returned FALSE for ' . $url);
            }
        } else {
            throw new TurningTechHttpPostHelperIOException('fopen() returned FALSE for ' . $url);
        }
    } else {
        throw new TurningTechHttpPostHelperIOException("ini_get( 'allow_url_fopen' ) returned " .
                                         ini_get('allow_url_fopen') . ' for ' . $url);
    }
    return $retval;
}


/**
 * utility class that handles HTTP POST with AES encrypted data
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TurningTechHttpPostHelper {
    /*
     * Helper Class.
     *
     */
}


/**
 * Establish an exception namespace, add output to error_log()
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TurningTechHttpPostHelperException extends Exception {
    /**
     * Convert to string
     */
    public function __toString() {
        if (TURNINGTECH_ENABLE_POSTRW_EXCEPTIONS_IN_ERROR_LOG) {
            $msg = $this->getMessage();
            $fp = @fopen($CFG->dataroot.'/ttlogfile.log', 'a');
            fwrite($fp, "$msg \n");
        }
        return parent::__toString();
    }
}


/**
 * Establish an exception namespace, add output to error_log()
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TurningTechHttpPostHelperIOException extends TurningTechHttpPostHelperException {
}