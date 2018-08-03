<?php
/**
 * Author: Jesse Tatum
 * Date: 7/18/18
 *
 * Simple login form that checks the given username against the given API key
 * The API key is not public facing and is a sufficient substitute for a password
 * that doesn't necessitate a database.
 */

if (count($_POST) <= 1) // Don't run until POST with credentials
    return false;

$user = $_POST['user'];
$api = $_POST['api'];

// Try to use API key
$opts = array(
    'http'=>array(
        'method'=>"GET",
        'header'=>"Zotero-API-Key: " . $api
    )
);
$context = stream_context_create($opts); // Create request with API key in headers

// Grab User Info
$userInfo = json_decode(file_get_contents('https://api.zotero.org/keys/' . $api, false, $context), true);
if (json_last_error() == JSON_ERROR_NONE){ // If valid, grab ID and username
    $username = $userInfo["username"];
    $userID = $userInfo["userID"];
} else                                      // Otherwise the API key given is wrong
    return false;

// Check values
if ($user == $username || $user == $userID) {
    //login
//        make_session($user, $api);

    // Writing to api_key.php is equivalent to creating a session
    $dir = dirname(__FILE__) . '/api_key.php';
    $fh = fopen($dir, 'w+');
    fwrite($fh,'<?php $api_key = ' . $api . '?>');
    header("Location: collections.php");
    return true;
} else
    return false;

//function make_session($user, $api){
//    session_start();
//    $_SESSION['user'] = $user;
//    $_SESSION['password'] = $api;
//}
//
//function destroy_session(){
//    if(isset($_SESSION['user']))
//        unset($_SESSION['user']);
//    if(isset($_SESSION['password']))
//        unset($_SESSION['password']);
//    session_destroy();
//}
