<?php

require_once "Sodium.php";
error_reporting( E_ERROR );


//$message = "Hola soy un mensaje muy loco";
//try {
//    $cipher = $crypt->encrypt($message);
//    echo "Cipher: " . $cipher;
//    echo "<br>\r\n";
//    echo "Plain: " . $crypt->decrypt($cipher);
//} catch (Exception $e) {
//    echo $e->getMessage();
//}

//echo $crypt->generateKeypair();
//echo "<br><br>";
//
//echo $res = $crypt->encrypt("HOLA COMO ESTAS");
//echo "<br><br>";
//
//echo $crypt->decrypt($res);
//echo "<br><br>";


$action = $argv[1];
$user   = $argv[2];
$secret = $argv[3];

if( empty($action) )
    echo "Action Empty";


try{
    $crypt = new Sodium();

    //when a new keys are crated are required new access credentials too
    if( $action === 'genkeys' ){
        echo $crypt->generateKeypair();
    }

    //New keys generation
    else if( $action === 'new_access' ){
        if( empty($user) )
            echo "User Empty";
        if( empty($secret) )
            echo "Secret Empty";

        $crypt->newLogin($user, $secret);
        echo "Access credentials changed";
    }

    //User decrypt validation
    else if( $action === 'validate' ){
        if( empty($user) )
            echo "User Empty";
        if( empty($secret) )
            echo "Secret Empty";


        if( $crypt->validate( $user, $secret ) )
            echo "Success";
        else
            echo "Fail";
    }
    else
        echo "$action is an invalid action";

} catch (Exception $e) {
    echo "Error: ".$e->getMessage();
}


