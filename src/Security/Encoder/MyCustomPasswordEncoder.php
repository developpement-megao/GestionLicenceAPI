<?php

namespace App\Security\Encoder;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class MyCustomPasswordEncoder implements PasswordEncoderInterface
{
    private $encrypt_method;
    private $key;
    private $iv;

    public function __construct()
    {
        $this->encrypt_method = "AES-256-CBC";
        $this->key = '1NyS!Oa.YOp-8d%P';
        $this->iv = substr('hYjA!2pZG7jD1WzK', 0, 16);    
    }

    public function encodePassword(string $raw, ?string $salt){
        return base64_encode(openssl_encrypt($raw, $this->encrypt_method, $this->key, 0, $this->iv));
    }

    public function decodePassword(string $encoded){
        return openssl_decrypt(base64_decode($encoded), $this->encrypt_method, $this->key, 0, $this->iv);
    }

    public function isPasswordValid(string $encoded, string $raw, ?string $salt){
        return ($raw == $this->decodePassword($encoded));
    }

    public function needsRehash(string $encoded): bool{
        return false;
    }
}