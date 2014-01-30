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
 * Helper for support of encrypt/decryption-related functionality.
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 **/


if (isset($CFG->turningtech_encryption_format) && $CFG->turningtech_encryption_format == TURNINGTECH_ENCRYPTION_FORMAT_ECB) {
    define('TURNINGTECH_ENCRYPTION_FORMAT', MCRYPT_MODE_ECB);
} else {
    define('TURNINGTECH_ENCRYPTION_FORMAT', MCRYPT_MODE_CBC);
}

/**
 * decrypt a single string for ResponseWare
 * @param unknown_type $str
 * @return unknown|Ambigous <string, the>
 */
function turningtech_decryptrwstring($str) {
    if (!TURNINGTECH_ENABLE_DECRYPTION) {
        return $str;
    }
    $crunch           = new TurningTechEncryptionHelper();
    $crunch->key      = base64_decode('iZRKTcYfw9G2DGrdymD23w==');
    $crunch->iv       = base64_decode('DvNdhsQOmHhMn6ZESNTTlw==');
    $crunch->mode     = MCRYPT_MODE_CBC;
    $crunch->dobase64 = false;
    $crunch->dopkcs5  = true;
    return $crunch->turningtech_decryptstring($str);
}

/**
 * decrypt a list of strings for ResponseWare
 * @param array $strings
 * @return array string
 */
function turningtech_decryptrwstrings($strings) {
    if (!TURNINGTECH_ENABLE_DECRYPTION) {
        return $strings;
    }
    for ($i = 0; $i < count($strings); $i++) {
        $strings[$i] = turningtech_decryptrwstring($strings[$i]);
    }
    return $strings;
}

/**
 * encrypt a single string for ResponseWare
 * @param string $str the string to encrypt
 * @return string
 */
function turningtech_encryptrwstring($str) {
    if (!TURNINGTECH_ENABLE_ENCRYPTION) {
        return $str;
    }
    $crunch           = new TurningTechEncryptionHelper();
    $crunch->key      = base64_decode('iZRKTcYfw9G2DGrdymD23w==');
    $crunch->iv       = base64_decode('DvNdhsQOmHhMn6ZESNTTlw==');
    $crunch->mode     = MCRYPT_MODE_CBC;
    $crunch->dobase64 = false;
    $crunch->dopkcs5  = true;
    return $crunch->turningtech_encryptstring($str);
}

/**
 * encrypt a list of strings for ResponseWare
 * @param array $strings
 * @return array string 
 */
function turningtech_encryptrwstrings($strings) {
    if (!TURNINGTECH_ENABLE_ENCRYPTION) {
        return $strings;
    }
    for ($i = 0; $i < count($strings); $i++) {
        $strings[$i] = turningtech_encryptrwstring($strings[$i]);
    }
    return $strings;
}



/**
 * decrypt a single string for Web Services
 * @param string $str the string to decrypt
 * @return string
 */
function turningtech_decryptwsstring($str) {
    if (!TURNINGTECH_ENABLE_DECRYPTION) {
        return $str;
    }
    $crunch       = new TurningTechEncryptionHelper();
    $crunch->key  = base64_decode('Uepfjci/SJ7t+10kCtn2qA==');
    $crunch->iv   = $crunch->key;
    $crunch->mode = TURNINGTECH_ENCRYPTION_FORMAT;
    return $crunch->turningtech_decryptstring($str);
}

/**
 * decrypt a list of strings for Web Services
 * @param array $strings
 * @return array string 
 */
function turningtech_decryptwsstrings($strings) {
    if (!TURNINGTECH_ENABLE_DECRYPTION) {
        return $strings;
    }
    for ($i = 0; $i < count($strings); $i++) {
        $strings[$i] = turningtech_decryptwsstring($strings[$i]);
    }
    return $strings;
}

/**
 * encrypt a single string for Web Services
 * @param string $str the string to encrypt
 * @return string
 */
function turningtech_encryptwsstring($str) {
    if (!TURNINGTECH_ENABLE_ENCRYPTION) {
        return $str;
    }
    $crunch       = new TurningTechEncryptionHelper();
    $crunch->key  = base64_decode('Uepfjci/SJ7t+10kCtn2qA==');
    $crunch->iv   = $crunch->key;
    $crunch->mode = TURNINGTECH_ENCRYPTION_FORMAT;
    return $crunch->turningtech_encryptstring($str);
}

/**
 * encrypt a list of strings for Web Services
 * @param array $strings
 * @return array string 
 */
function turningtech_encryptwsstrings($strings) {
    if (!TURNINGTECH_ENABLE_ENCRYPTION) {
        return $strings;
    }
    for ($i = 0; $i < count($strings); $i++) {
        $strings[$i] = turningtech_encryptwsstring($strings[$i]);
    }
    return $strings;
}


/**
 * utility class that handles AES encryption/encoding
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TurningTechEncryptionHelper {
    /**
     * @var unknown_type
     */
    public $algorithm = MCRYPT_RIJNDAEL_128; // Use MCRYPT_RIJNDAEL_128 for AES.
    /**
     * @var unknown_type
     */
    public $algorithm_directory = '';
    /**
     * @var unknown_type
     */
    public $mode = TURNINGTECH_ENCRYPTION_FORMAT;
    /**
     * @var unknown_type
     */
    public $mode_directory = '';
    /**
     * @var unknown_type
     */
    public $dobase64 = true;
    /**
     * @var unknown_type
     */
    public $dopkcs5 = true;
    /**
     * @var unknown_type
     */
    public $key = '';
    /**
     * @var unknown_type
     */
    public $iv = ''; // NOTE: must be the same value for encrypt/decrypt.
    /**
     * @var unknown_type
     */
    const ENCRYPT = 1;
    /**
     * @var unknown_type
     */
    const DECRYPT = 2;

    /**
     * decrypt a single string
     * @param string $str the string to decrypt
     * @return string
     */
    public function turningtech_decryptstring($str) {
        if (!TURNINGTECH_ENABLE_DECRYPTION) {
            return $str;
        }
        return $this->turningtech_processdata(self::DECRYPT, $str);
    }

    /**
     * decrypt a list of strings
     * @param array $strings
     * @return array string 
     */
    public function turningtech_decryptstrings($strings) {
        if (!TURNINGTECH_ENABLE_DECRYPTION) {
            return $strings;
        }
        for ($i = 0; $i < count($strings); $i++) {
            $strings[$i] = $this->turningtech_decryptstring($strings[$i]);
        }
        return $strings;
    }

    /**
     * encrypt a single string
     * @param string $str the string to encrypt
     * @return string
     */
    public function turningtech_encryptstring($str) {
        if (!TURNINGTECH_ENABLE_ENCRYPTION) {
            return $str;
        }
        return $this->turningtech_processdata(self::ENCRYPT, $str);
    }

    /**
     * encrypt a list of strings
     * @param array $strings
     * @return array string 
     */
    public function turningtech_encryptstrings($strings) {
        if (!TURNINGTECH_ENABLE_ENCRYPTION) {
            return $strings;
        }
        for ($i = 0; $i < count($strings); $i++) {
            $strings[$i] = $this->turningtech_encryptstring($strings[$i]);
        }
        return $strings;
    }

    /**
     * generic encrypt/decrypt logic
     * @param unknown_type $direction pass in either self::ENCRYPT or self::DECRYPT
     * @param string $str the data to encrypt/decrypt
     * @return string
     */
    protected function turningtech_processdata($direction, $str) {
        if ($direction != self::ENCRYPT && $direction != self::DECRYPT) {
            throw new TurningTechEncryptionHelperException('Unknown encryption request ' . $direction);
        }
        $td = mcrypt_module_open($this->algorithm, $this->algorithm_directory, $this->mode, $this->mode_directory);
        if ($td != false) {
            if ($direction == self::ENCRYPT && $this->dopkcs5) {
                $str = $this->turningtech_padwithpkcs5($str);
            }
            if ($direction == self::DECRYPT && $this->dobase64) {
                $str = base64_decode($str);
            }

            $ivsize = mcrypt_enc_get_iv_size($td);
            if ($this->iv == '') {
                $this->iv = mcrypt_create_iv($ivsize); // NOTE: must be the same value for encrypt/decrypt.
            }
            if ($ivsize != strlen($this->iv)) {
                throw new TurningTechEncryptionHelperException('size of given IV ' . strlen($this->iv) .
                                                 ' does not match required size ' . $ivsize);
            }

            $keysize = mcrypt_enc_get_key_size($td);
            if (strlen($this->key) > $keysize) {
                throw new TurningTechEncryptionHelperException('size of key ' . strlen($this->key) .
                                                 ' is greater than the maximum allowed size ' . $keysize);
            }
            if (strlen($this->key) <= 0) {
                throw new TurningTechEncryptionHelperException('size of key ' . strlen($this->key) . ' is too small');
            }

            $initstatus = mcrypt_generic_init($td, $this->key, $this->iv);
            if ($initstatus === false || $initstatus < 0) {
                throw new TurningTechEncryptionHelperException('mcrypt_generic_init() returned error code ' . $initstatus);
            }

            if ($direction == self::ENCRYPT) {
                $str = mcrypt_generic( $td, $str );
            } else if ($direction == self::DECRYPT) {
                $str = mdecrypt_generic( $td, $str );
            }

            if (!mcrypt_generic_deinit($td)) {
                throw new TurningTechEncryptionHelperException('mcrypt_generic_deinit() returned FALSE');
            }
            // If( mcrypt_module_close( $td ) )  throw new EncryptionHelperException( 'mcrypt_module_close() returned FALSE' );.
            mcrypt_module_close($td); // It returns false locally, for some unknown reason.
            $td = false; // A pointer handling habit :).

            if ($direction == self::ENCRYPT && $this->dobase64) {
                $str = base64_encode($str);
            }
            if ($direction == self::DECRYPT && $this->dopkcs5) {
                $str = $this->turningtech_unpadwithpkcs5($str);
            }
        } else {
            throw new TurningTechEncryptionHelperException('mcrypt_module_open() returned FALSE ');
        }
        return $str;
    }

    /**
     * apply PKCS #5 padding to string
     * @param string $s the string to pad
     * @return string
     */
    protected function turningtech_padwithpkcs5($s) {
        $blocksize = mcrypt_get_block_size($this->algorithm, $this->mode);
        $padsize   = $blocksize - (strlen($s) % $blocksize);
        $s .= str_repeat(chr($padsize), $padsize);
        return $s;
    }

    /**
     * remove PKCS #5 padding from string
     * @param string $s the string to de-pad
     * @return string
     */
    protected function turningtech_unpadwithpkcs5($s) {
        $ssize   = strlen($s);
        $padsize = ord($s[$ssize - 1]);
        if ($padsize <= $ssize && strspn($s, chr($padsize), $ssize - $padsize) == $padsize) {
            $s = substr($s, 0, -$padsize);
        }
        return $s;
    }


}


/**
 * Establish an exception namespace, add output to error_log()
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TurningTechEncryptionHelperException extends Exception {
    /**
     * Converts to string
     */
    public function __toString() {
        if (TURNINGTECH_ENABLE_ENCRYPTION_EXCEPTIONS_IN_ERROR_LOG) {
            $msg = $this->getMessage();
            $fp = @fopen($CFG->dataroot.'/ttlogfile.log', 'a');
            fwrite($fp, "$msg \n");
            fclose($fp);
        }
        return parent::__toString();
    }
}