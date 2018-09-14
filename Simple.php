<?php

require_once "vendor/autoload.php";

/**
 * Wrap crypto_aead_*_encrypt() in a drop-dead-simple encryption interface
 *
 * @link https://paragonie.com/b/kIqqEWlp3VUOpRD7
 * @param string $message
 * @param string $key
 * @return string
 * @throws SodiumException
 */
function simpleEncrypt($message, $key)
{
    $nonce = random_bytes(24); // NONCE = Number to be used ONCE, for each message
    $encrypted = ParagonIE_Sodium_Compat::crypto_aead_xchacha20poly1305_ietf_encrypt(
        $message,
        $nonce,
        $nonce,
        $key
    );
    return $nonce . $encrypted;
}

/**
 * Wrap crypto_aead_*_decrypt() in a drop-dead-simple decryption interface
 *
 * @link https://paragonie.com/b/kIqqEWlp3VUOpRD7
 * @param string $message - Encrypted message
 * @param string $key     - Encryption key
 * @return string
 * @throws Exception
 */
function simpleDecrypt($message, $key)
{
    $nonce = mb_substr($message, 0, 24, '8bit');
    $ciphertext = mb_substr($message, 24, null, '8bit');
    $plaintext = ParagonIE_Sodium_Compat::crypto_aead_xchacha20poly1305_ietf_decrypt(
        $ciphertext,
        $nonce,
        $nonce,
        $key
    );
    if (!is_string($plaintext)) {
        throw new Exception('Invalid message');
    }
    return $plaintext;
}

$secretKey = random_bytes(32);
$message = 'Test message';

/* Encrypt the message: */
$ciphertext = simpleEncrypt($message, $secretKey);

/* Decrypt the message: */
try {
    $decrypted = simpleDecrypt($ciphertext, $secretKey);
    echo $decrypted;
    //var_dump(hash_equals($decrypted, $message));
    /* bool(true) */
} catch (Exception $ex) {
    /* Someone is up to no good */
    exit(255);
}