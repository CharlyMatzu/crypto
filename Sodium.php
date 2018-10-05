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
    const DS = DIRECTORY_SEPARATOR;
    const SECRET_PATH = __DIR__ . self::DS . "secret" . self::DS;

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
     * @param $file String Path and file name
     * @param $data String data to put
     * @throws Exception
     */
    private function writeFile($file, $data){
        //TODO: specify folder to create
        if( !file_exists( $file ) )
            mkdir( "secret" );

        if( !file_put_contents( $file, $data) )
            throw new Exception("Error to write file $file");
    }

    /**
     * @param $file String Path and file name
     * @return String data read
     * @throws Exception
     */
    private function readFile($file){
        if( !file_exists( $file ) )
            throw new Exception("File $file does not exist");

        $read = file_get_contents( $file );
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
     * @return string
     * @throws Exception
     */
    private function generateKeypair(){
        $key = random_bytes( SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $this->writeFile( self::SECRET_PATH . "key", $key );
        $this->writeFile( self::SECRET_PATH . "nonce", $nonce );

        return "New KeyPair created on secret/";
    }


    /**
     * Encrypt a message
     * @param $message String data to encrypt
     * @return string
     *
     * @throws Exception
     */
    public function encrypt($message){
        $ciphertext = sodium_crypto_secretbox($message,
                                            $this->readFile( self::SECRET_PATH . "nonce" ),
                                            $this->readFile( self::SECRET_PATH . "key" ) );
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

        $plaintext = sodium_crypto_secretbox_open(
            $ciphertext,
            $this->readFile( self::SECRET_PATH . "nonce" ),
            $this->readFile( self::SECRET_PATH . "key" )
        );

        if ($plaintext === false) {
            throw new Exception("Bad ciphertext");
        }
        return $plaintext;
    }

    /**
     * @return string decrypted text
     * @throws Exception
     */
    public function getCreds( ){
        $ciphertext = $this->readFile( self::SECRET_PATH . "creds.ini" );
        return $this->decrypt( $ciphertext );
    }

    /**
     * @param $client String
     * @param $secret String
     * @return bool
     * @throws Exception
     */
    public function validate($client, $secret){
        $result = $this->readFile( self::SECRET_PATH . "creds.ini" );
        $data = $this->decrypt( $result );
        $data = json_decode( $data, true );

        if( $data['client'] === $client && $data['secret'] === $secret )
            return true;
        else
            return false;
    }



    /**
     * @param $client String
     * @param $secret String
     * @throws Exception
     */
    public function newAccess($client, $secret){
        //generate keys
        $this->generateKeypair();

        //define access
        $uid = sha1( $uid = uniqid() );
        $result = [ "client" => $client, "secret" => $secret, "uid" => $uid ];
        $this->writeFile( self::SECRET_PATH . "creds.ini", $this->encrypt( json_encode($result) ) );
        $this->writeFile( self::SECRET_PATH . "plain.txt", json_encode($result)."\r\n SAVE THIS FILE IN A SECURE DIRECTORY" );
    }

    /**
     * @throws Exception
     */
    public function generateCreds() {
        $client = $this->randHash();
//        $secret = $this->randHash();
        $secret = $this->randNumberHash();

        $this->newAccess( $client, $secret );
    }


    /**
     * @return string generated numeric hash
     */
    private function randNumberHash(){
        $val = "";
        try{
            for( $i = 0; $i < 32; $i++ )
                $val .= '' . random_int( 0, 9 );
        }catch (Exception $e) {
            echo "An Exception has ocurred";
            die();
        }
        return $val;
    }

    /**
     * @param int $len
     * @return bool|string
     */
    private function randHash( $len = 32 ){
        return substr( md5(openssl_random_pseudo_bytes(20)), -$len);
    }




}