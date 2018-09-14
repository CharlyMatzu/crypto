<?php

require_once "Sodium.php";
$crypt = new Sodium();


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

//$crypt->newLogin("carlos", "123");
if( $crypt->validate( "carlos", "12543" ) )
    echo "Success";
else
    echo "Fail";