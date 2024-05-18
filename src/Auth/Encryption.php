<?php

namespace PikaJew002\Handrolled\Auth;

use PikaJew002\Handrolled\Support\Configuration;

class Encryption
{
    public Configuration $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    public function encrypt(string $unencryptedString): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encrypted_result = sodium_crypto_secretbox($unencryptedString, $nonce, $this->getEncryptionKey());

        return base64_encode($nonce . $encrypted_result);
    }

    public function decrypt(string $encryptedString): string
    {
        if($encryptedString = base64_decode($encryptedString)) {
            $nonce = mb_substr($encryptedString, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
            $encrypted_result = mb_substr($encryptedString, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

            if($decryptedString = sodium_crypto_secretbox_open($encrypted_result, $nonce, $this->getEncryptionKey())) {
                return $decryptedString;
            }
            
            throw new EncryptionException('Error decrypting encrypted string');
        }
        
        throw new EncryptionException('Error decoding encrypted string');
    }

    public function getEncryptionKey(): string
    {
        if($key = base64_decode($this->config->get('app.encryption_key'))) {
            return $key;
        }

        throw new EncryptionException('Error decoding encryption key');
    }
}
