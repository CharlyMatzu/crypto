<?php

require_once "Sodium.php";
error_reporting( E_ERROR );

//PARAMS
$action = $argv[1];
$client   = $argv[2];
$secret = $argv[3];


if( empty($action) )
    echo "Action Empty";

// Dentitions
try{
    switch( $action ){
        case 'gen_access': generateNewAccess(); break;
        case 'new_access': newAccess( $client, $secret ); break;
        //TODO: use a specific key/creds files
        case 'validate': validate( $client, $secret ); break;
        case 'read': readCreds(); break;


        default: 
                echo "$action is an invalid action\r\n";
                printHelp();
            break;
    }

} catch (Exception $e) {
    echo "Error: ".$e->getMessage();
}


/**
 * Generate a new access credentials automatically with keys
 *
 * @return void
 * @throws Exception
 */
function generateNewAccess(){
    $crypt = new Sodium();
    $crypt->generateCreds();
}

/**
 * Make a new access credentials using a specific client and secret
 *
 * @param String $client client
 * @param String $secret like password
 * @return void
 * @throws Exception
 */
function newAccess( $client, $secret ){
    if( empty($client) || empty($secret) )
        throw new Exception("<client> or <secret> empty");

    $crypt = new Sodium();
    $crypt->newAccess( $client, $secret );
    echo "New Access and keys created";
}

/**
 * @param $client
 * @param $secret
 * @throws Exception
 */
function validate( $client, $secret ){
    if( empty($client) || empty($secret) )
        throw new Exception("<client> or <secret> empty");

    $crypt = new Sodium();
    $bool = $crypt->validate( $client, $secret );
    if( $bool )
        echo "Validation success";
    else
        echo "Validation Failed. <client> or <secret> invalid";
}

/**
 * @throws Exception
 */
function readCreds(){
    try{
        $crypt = new Sodium();
        echo $crypt->getCreds();
    } catch (Exception $e) {
        echo "Error: ". $e->getMessage();
    }
}


function logger($message){
    $log_name = date("Y-m-d" );
    file_put_contents("logs/$log_name.log", print_r($message)."\r\n", FILE_APPEND);
}


function printHelp(){
    echo "<action> value can be:\r\n";
    echo "\tgen_access: generate new credentials automatically\r\n";
    echo "\tnew_access: establish new credentials using specific access data. This action requires <client> and <secret> params.\r\n";
    echo "\tvalidate: can be used to test credentials. This action requires <client> and <secret> params.\r\n";
    echo "\tread: get decrypted data for creds.ini.\r\n";
}