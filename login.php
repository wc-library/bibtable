<?php
/**
 * Author: Jesse Tatum
 * Date: 7/18/18
 *
 * Simple login form that checks the given username against the given API key
 * The API key is not public facing and is a sufficient substitute for a password
 * that doesn't necessitate a database.
 */

include 'api_key.php';

if (count($_POST) <= 1) // Don't run until POST with credentials
    return false;

$user = $_POST['user'];
$api = $_POST['api'];

$opts = array(
    'http'=>array(
        'method'=>"GET",
        'header'=>"Zotero-API-Key: " . $api_key
    )
);
$context = stream_context_create($opts); // Create request with API key in headers

// Grab User Info
$userInfo = json_decode(file_get_contents('https://api.zotero.org/keys/' . $api, false, $context), true);
if (json_last_error() == JSON_ERROR_NONE){
    $username = $userInfo["username"];
    $userID = $userInfo["userID"];
} else
    handleError("Invalid API key");

// Check values
if ($user == $username || $user == $userID) {
    if ($api == $api_key) {
        //login
        make_session($user, $api);
        echo '<script> window.location.href = "collections.php";</script>';
        header("Location: collections.php");
        exit();
    }
}

function handleError($msg){
    die("Error: " . $msg);
}

function make_session($user, $api){
    session_start();
    $_SESSION['user'] = $user;
    $_SESSION['password'] = $api;
}

function destroy_session(){
    if(isset($_SESSION['user']))
        unset($_SESSION['user']);
    if(isset($_SESSION['password']))
        unset($_SESSION['password']);
    session_destroy();
}
