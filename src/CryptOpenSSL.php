<?php
/**
 * OpenSSL Encrypter/Decrypter class
 * 
 * This source file is subject to the Apache-2.0 License 
 */

class CryptOpenSSL 
{
    protected $di = NULL;

    const METHOD = 'aes-256-cbc';

    public function __construct($di)
    {
        $this->setDi($di);
    }

    public function setDi($di)
    {
        $this->di = $di;
    }

    public function getDi()
    {
        return $this->di;
    }

    public function encrypt($message, $pass = null)
    {
        $key = $this->_getSalt($pass);
    
        $ivsize = openssl_cipher_iv_length(self::METHOD);
        $iv = openssl_random_pseudo_bytes($ivsize);

        $ciphertext = openssl_encrypt(
            $message,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($iv . $ciphertext);
    }

    public function decrypt($message, $pass = null)
    {
        if (is_null($message)){
            return false;
        }
        $key = $this->_getSalt($pass);

        $message = base64_decode($message);

        $ivsize = openssl_cipher_iv_length(self::METHOD);
        $iv = mb_substr($message, 0, $ivsize, '8bit');
        $ciphertext = mb_substr($message, $ivsize, null, '8bit');

        $result = openssl_decrypt(
            $ciphertext,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        $result = trim($result);

        return $result;
    }

    private function _getSalt($pass = null)
    {
        if (null == $pass) {
            $pass = $this->di['config']['salt'];
        }
        return pack('H*', hash('md5', $pass));
    }
}