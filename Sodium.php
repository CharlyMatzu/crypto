<?php

//require_once "/path/to/sodium_compat/autoload.php";
require_once "vendor/autoload.php";

class Sodium
{
    // https://github.com/paragonie/sodium_compat
    // https://paragonie.com/book/pecl-libsodium
    // https://paragonie.com/book/pecl-libsodium/read/04-secretkey-crypto.md#crypto-secretbox
    // https://paragonie.com/blog/2017/06/libsodium-quick-reference-quick-comparison-similar-functions-and-which-one-use
    // https://download.libsodium.org/doc/public-key_cryptography/authenticated_encryption

    private $alice_kp;
    private $alice_sk;
    private $alice_pk;

    /**
     * Sodium constructor.
     */
    public function __construct() {
//        $this->alice_kp = sodium_crypto_sign_keypair();
//        $this->alice_sk = sodium_crypto_sign_secretkey( $this->alice_kp );
//        $this->alice_pk = sodium_crypto_sign_publickey( $this->alice_kp );
    }

//    /**
//     * @param $message String data to verify
//     * @return TRUE
//     * @throws Exception
//     */
//    private function verify($message){
//        $signature = sodium_crypto_sign_detached($message, $this->alice_sk);
//        $res = sodium_crypto_sign_verify_detached($signature, $message, $this->alice_pk);
//        if ( $res ) {
//            return true;
//        } else {
//            throw new Exception('Invalid signature');
//        }
//    }

//    /**
//     * Encrypt a message
//     * @param $message String data to encrypt
//     * @return string
//     *
//     * @throws Exception
//     */
//    public function encrypt($message){
//        // Generating your encryption key
//        $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
//
//        // Using your key to encrypt information
//        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
//        $ciphertext = sodium_crypto_secretbox($message, $nonce, $key);
//        return $ciphertext;
//    }
//
//    /**
//     * Decrypt a message
//     *
//     * @param $ciphertext String data to decrypt
//     * @return string Decrypt String
//     * @throws Exception
//     */
//    public function decrypt($ciphertext){
//        $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
//        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
//
//        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
//        if ($plaintext === false) {
//            throw new Exception("Bad ciphertext");
//        }
//        return $plaintext;
//    }


    /**
     * @return string
     * @throws Exception
     */
    public function generateKeypair(){
        $key = random_bytes( SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $this->writeFile( "key", $key );
        $this->writeFile( "nonce", $nonce );

        return "New KeyPair created on secret/";
    }

    /**
     * @param $file String Path and file name
     * @param $data String data to put
     * @throws Exception
     */
    private function writeFile($file, $data){
        if( !file_exists( "secret/".$file ) )
            mkdir( "secret" );

        if( !file_put_contents( "secret/".$file, $data) )
            throw new Exception("Error to write file $file");
    }

    /**
     * @param $file String Path and file name
     * @return String data read
     * @throws Exception
     */
    private function readFile($file){
        if( !file_exists( "secret/".$file ) )
            throw new Exception("File $file does not exist");

        $read = file_get_contents( "secret/".$file );
        if( !$read )
            throw new Exception("Error to read file $file");

        return $read;
    }



//    /**
//     * @param $message String data to verify
//     * @return TRUE
//     * @throws Exception
//     */
//    private function verify($message){
//        $signature = sodium_crypto_sign_detached($message, $this->readFile( "key" ));
//        $res = sodium_crypto_sign_verify_detached($signature, $message, $this->readFile( "nonce" ));
//        if ( $res ) {
//            return true;
//        } else {
//            throw new Exception('Invalid signature');
//        }
//    }


    /**
     * Encrypt a message
     * @param $message String data to encrypt
     * @return string
     *
     * @throws Exception
     */
    public function encrypt($message){
        $ciphertext = sodium_crypto_secretbox($message,
                                            $this->readFile( "nonce" ),
                                            $this->readFile( "key" ) );
        return $ciphertext;
    }

    /**
     * Decrypt a message
     *
     * @param $ciphertext String data to decrypt
     * @return string Decrypt String
     * @throws Exception
     */
    public function decrypt($ciphertext){
//        $this->verify( $ciphertext );

        $plaintext = sodium_crypto_secretbox_open($ciphertext,
                                                $this->readFile( "nonce" ),
                                                $this->readFile( "key" ) );
        if ($plaintext === false) {
            throw new Exception("Bad ciphertext");
        }
        return $plaintext;
    }

    //-------------------
    // TEST
    //-------------------

    /**
     * @param $user String
     * @param $pass String
     * @throws Exception
     */
    public function newLogin($user, $pass){
        $result = [ "user" => $user, "pass" => $pass ];
        $this->writeFile( "data.ini", $this->encrypt( json_encode($result) ) );
    }

    /**
     * @param $user String
     * @param $pass String
     * @return bool
     * @throws Exception
     */
    public function validate($user, $pass){
        $result = $this->readFile( "data.ini" );
        $data = $this->decrypt( $result );
        $data = json_decode( $data );

        if( $data->user === $user && $data->pass === $pass )
            return true;
        else
            return false;
    }


}