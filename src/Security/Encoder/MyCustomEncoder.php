<?php

namespace App\Security\Encoder;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class MyCustomEncoder implements PasswordEncoderInterface
{
    private $encryptMethod;
    private $secretKey;
    private $initializationVector;

    public function __construct()
    {
        $this->encryptMethod = ConfigEncoder::ENCRYPT_METHOD;
        $this->secretKey = substr(ConfigEncoder::SECRET_KEY, 0, 32);
        $this->initializationVector = substr(ConfigEncoder::INIT_VECTOR, 0, 16);    
    }

    public function encodePassword(string $raw, ?string $salt){
        return openssl_encrypt($raw, $this->encryptMethod, $this->secretKey, 0, $this->initializationVector);
    }

    public function decodePassword(string $encoded){
        return openssl_decrypt($encoded, $this->encryptMethod, $this->secretKey, 0, $this->initializationVector);
    }

    public function isPasswordValid(string $encoded, string $raw, ?string $salt){
        return ($raw == $this->decodePassword($encoded));
    }

    public function needsRehash(string $encoded): bool{
        return false;
    }
}