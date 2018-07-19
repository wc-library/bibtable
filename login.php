<?php
/**
 * Created by PhpStorm.
 * User: jessetatum
 * Date: 7/18/18
 * Time: 3:42 PM
 */
include 'api_key.php';

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
if ($user == $username || $user == $userID)
    if ($api == $api_key)
        //login
        echo "nice";

function handleError(msg){
    echo "Error: " . msg;
}

?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <link rel="stylesheet" href="stylesheets/login.css">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- tablesorter -->
    <script src="tablesorter-master/js/jquery.tablesorter.js"></script>
    <script type="application/javascript" src="tablesorter-master/js/jquery.tablesorter.widgets.js"></script>

    <!-- Bootstrap -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

</head>
<h1 class="page-header">Bibtable Login Page</h1>
<body>

<div class="container">
    <form>
        <div class="form-group">
            <label for="user">Username or User ID</label>
            <input type="text" id="user" class="form-control" placeholder="Username or User ID" required><br>
        </div>
        <div class="form-group">
            <label for="api">API Key</label>
            <input type="text" class="form-control" id="api" placeholder="API Key" required>
            <small id="apiHelp" class="form-text text-muted">This needs to be generated from your <a href="https://www.zotero.org/settings/keys">Zotero</a> account.  </small>

        </div>
        <button type="submit" class="btn btn-primary formbtn">Submit</button>
    </form>
</div>

</body>
<script type="application/javascript">

    let user = document.getElementById(user);
    let api = document.getElementById(api);

    console.log("User: " + user);
    console.log("API: " + api);

    $(document).ready(function(){
        $('.formbtn').click(function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: 'login.php',
                data: {'user': user, 'api': api},
                success: function (msg) {
                    console.log(msg);
                    window.location.href = 'collections.php';
                },
                error: function (error) {
                    alert("Error, please try again.\nError details: " + JSON.stringify(error));
                }
            })
        });
    });
</script>

</html>
