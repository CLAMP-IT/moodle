<?php
/*******
 * Helper for support of encrypt/decryption-related functionality.
 * @author jacob
 *
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 **/

/**
 * decrypt a single string for ResponseWare
 * @param $str the string to decrypt
 * @return string
 */

if ($CFG->turningtech_encryption_format == TURNINGTECH_ENCRYPTION_FORMAT_ECB) {
    define('TURNINGTECH_ENCRYPTION_FORMAT', MCRYPT_MODE_ECB);
} else {
    define('TURNINGTECH_ENCRYPTION_FORMAT', MCRYPT_MODE_CBC);
}

function decryptResponseWareString($str) {
    if (!TURNINGTECH_ENABLE_DECRYPTION) {
        return $str;
    }
    $crunch           = new TurningTechEncryptionHelper();
    $crunch->key      = base64_decode('iZRKTcYfw9G2DGrdymD23w==');
    $crunch->iv       = base64_decode('DvNdhsQOmHhMn6ZESNTTlw==');
    $crunch->mode     = MCRYPT_MODE_CBC;
    $crunch->dobase64 = false;
    $crunch->doPKCS5  = true;
    return $crunch->decryptString($str);
}

/**
 * decrypt a list of strings for ResponseWare
 * @param $strings array
 * @return string array
 */
function decryptResponseWareStrings($strings) {
    if (!TURNINGTECH_ENABLE_DECRYPTION) {
        return $strings;
    }
    for ($i = 0; $i < count($strings); $i++) {
        $strings[$i] = decryptResponseWareString($strings[$i]);
    }
    return $strings;
}

/**
 * encrypt a single string for ResponseWare
 * @param $str the string to encrypt
 * @return string
 */
function encryptResponseWareString($str) {
    if (!TURNINGTECH_ENABLE_ENCRYPTION) {
        return $str;
    }
    $crunch           = new TurningTechEncryptionHelper();
    $crunch->key      = base64_decode('iZRKTcYfw9G2DGrdymD23w==');
    $crunch->iv       = base64_decode('DvNdhsQOmHhMn6ZESNTTlw==');
    $crunch->mode     = MCRYPT_MODE_CBC;
    $crunch->dobase64 = false;
    $crunch->doPKCS5  = true;
    return $crunch->encryptString($str);
}

/**
 * encrypt a list of strings for ResponseWare
 * @param $strings array
 * @return string array
 */
function encryptResponseWareStrings($strings) {
    if (!TURNINGTECH_ENABLE_ENCRYPTION) {
        return $strings;
    }
    for ($i = 0; $i < count($strings); $i++) {
        $strings[$i] = encryptResponseWareString($strings[$i]);
    }
    return $strings;
}



/**
 * decrypt a single string for Web Services
 * @param $str the string to decrypt
 * @return string
 */


function decryptWebServicesString($str) {
    if (!TURNINGTECH_ENABLE_DECRYPTION) {
        return $str;
    }
    $crunch       = new TurningTechEncryptionHelper();
    $crunch->key  = base64_decode('Uepfjci/SJ7t+10kCtn2qA==');
    $crunch->iv   = $crunch->key;
    $crunch->mode = TURNINGTECH_ENCRYPTION_FORMAT;
    return $crunch->decryptString($str);
}

/**
 * decrypt a list of strings for Web Services
 * @param $strings array
 * @return string array
 */
function decryptWebServicesStrings($strings) {
    if (!TURNINGTECH_ENABLE_DECRYPTION) {
        return $strings;
    }
    for ($i = 0; $i < count($strings); $i++) {
        $strings[$i] = decryptWebServicesString($strings[$i]);
    }
    return $strings;
}

/**
 * encrypt a single string for Web Services
 * @param $str the string to encrypt
 * @return string
 */
function encryptWebServicesString($str) {
    if (!TURNINGTECH_ENABLE_ENCRYPTION) {
        return $str;
    }
    $crunch       = new TurningTechEncryptionHelper();
    $crunch->key  = base64_decode('Uepfjci/SJ7t+10kCtn2qA==');
    $crunch->iv   = $crunch->key;
    $crunch->mode = TURNINGTECH_ENCRYPTION_FORMAT;
    return $crunch->encryptString($str);
}

/**
 * encrypt a list of strings for Web Services
 * @param $strings array
 * @return string array
 */
function encryptWebServicesStrings($strings) {
    if (!TURNINGTECH_ENABLE_ENCRYPTION) {
        return $strings;
    }
    for ($i = 0; $i < count($strings); $i++) {
        $strings[$i] = encryptWebServicesString($strings[$i]);
    }
    return $strings;
}


/***
 * utility class that handles AES encryption/encoding
 */
class TurningTechEncryptionHelper {
    public $algorithm = MCRYPT_RIJNDAEL_128; // use MCRYPT_RIJNDAEL_128 for AES
    public $algorithm_directory = '';
    public $mode = TURNINGTECH_ENCRYPTION_FORMAT;
    public $mode_directory = '';
    public $dobase64 = true;
    public $doPKCS5 = true;
    public $key = '';
    public $iv = ''; // NOTE: must be the same value for encrypt/decrypt

    const ENCRYPT = 1;
    const DECRYPT = 2;

    /**
     * decrypt a single string
     * @param $str the string to decrypt
     * @return string
     */
    public function decryptString($str) {
        if (!TURNINGTECH_ENABLE_DECRYPTION) {
            return $str;
        }
        return $this->processData(self::DECRYPT, $str);
    }

    /**
     * decrypt a list of strings
     * @param $strings array
     * @return string array
     */
    public function decryptStrings($strings) {
        if (!TURNINGTECH_ENABLE_DECRYPTION) {
            return $strings;
        }
        for ($i = 0; $i < count($strings); $i++) {
            $strings[$i] = $this->decryptString($strings[$i]);
        }
        return $strings;
    }

    /**
     * encrypt a single string
     * @param $str the string to encrypt
     * @return string
     */
    public function encryptString($str) {
        if (!TURNINGTECH_ENABLE_ENCRYPTION) {
            return $str;
        }
        return $this->processData(self::ENCRYPT, $str);
    }

    /**
     * encrypt a list of strings
     * @param $strings array
     * @return string array
     */
    public function encryptStrings($strings) {
        if (!TURNINGTECH_ENABLE_ENCRYPTION) {
            return $strings;
        }
        for ($i = 0; $i < count($strings); $i++) {
            $strings[$i] = $this->encryptString($strings[$i]);
        }
        return $strings;
    }

    /**
     * generic encrypt/decrypt logic
     * @param $direction pass in either self::ENCRYPT or self::DECRYPT
     * @param $str the data to encrypt/decrypt
     * @return string
     */
    protected function processData($direction, $str) {
        if ($direction != self::ENCRYPT && $direction != self::DECRYPT) {
            throw new TurningTechEncryptionHelperException('Unknown encryption request ' . $direction);
        }
        $td = mcrypt_module_open($this->algorithm, $this->algorithm_directory, $this->mode, $this->mode_directory);
        if ($td != false) {
            if ($direction == self::ENCRYPT && $this->doPKCS5) {
                $str = $this->padWithPKCS5($str);
            }
            if ($direction == self::DECRYPT && $this->dobase64) {
                $str = base64_decode($str);
            }

            $ivsize = mcrypt_enc_get_iv_size($td);
            if ($this->iv == '') {
                $this->iv = mcrypt_create_iv($ivsize); // NOTE: must be the same value for encrypt/decrypt
            }
            if ($ivsize != strlen($this->iv)) {
                throw new TurningTechEncryptionHelperException('size of given IV ' . strlen($this->iv) . ' does not match required size ' . $ivsize);
            }

            $keysize = mcrypt_enc_get_key_size($td);
            if (strlen($this->key) > $keysize) {
                throw new TurningTechEncryptionHelperException('size of key ' . strlen($this->key) . ' is greater than the maximum allowed size ' . $keysize);
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
                // ~~ $str = mcrypt_encrypt($this->algorithm, $this->key, $str, "cbc", $this->iv);
            } else if ($direction == self::DECRYPT) {
                $str = mdecrypt_generic( $td, $str );
                // ~~ $str = mcrypt_decrypt($this->algorithm, $this->key, $str, "cbc", $this->iv);
            }

            if (!mcrypt_generic_deinit($td)) {
                throw new TurningTechEncryptionHelperException('mcrypt_generic_deinit() returned FALSE');
            }
            //if( mcrypt_module_close( $td ) )  throw new EncryptionHelperException( 'mcrypt_module_close() returned FALSE' );
            mcrypt_module_close($td); // it returns false locally, for some unknown reason
            $td = false; // a pointer handling habit :)

            if ($direction == self::ENCRYPT && $this->dobase64) {
                $str = base64_encode($str);
            }
            if ($direction == self::DECRYPT && $this->doPKCS5) {
                $str = $this->unpadWithPKCS5($str);
            }
        } else {
            throw new TurningTechEncryptionHelperException('mcrypt_module_open() returned FALSE ');
        }
        return $str;
    }

    /**
     * apply PKCS #5 padding to string
     * @param $s the string to pad
     * @return string
     */
    protected function padWithPKCS5($s) {
        $blocksize = mcrypt_get_block_size($this->algorithm, $this->mode);
        $padsize   = $blocksize - (strlen($s) % $blocksize);
        $s .= str_repeat(chr($padsize), $padsize);
        return $s;
    }

    /**
     * remove PKCS #5 padding from string
     * @param $s the string to de-pad
     * @return string
     */
    protected function unpadWithPKCS5($s) {
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
 */
class TurningTechEncryptionHelperException extends Exception {
    public function __tostring() {
        if (TURNINGTECH_ENABLE_ENCRYPTION_EXCEPTIONS_IN_ERROR_LOG) {
            error_log($this->getMessage());
        }
        return parent::__tostring();
    }
}

?>