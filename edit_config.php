<?php
/**
 * Author: Tristan Hoppe
 * Date: 4/09/19
 *
 * Simple form that allows a user to update the values in the configuration file
 * 
 * 
 */
?>
<html lang="en" id="html">
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
<h1 class="page-header">Configuration Page</h1>
<body>

<div class="container">
    <form name="login" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
        
        

<?php
$conString = file_get_contents('configuration.json');
$config = json_decode($conString, true);
if (count($_POST) >= 1){ 

    foreach($config as $key => $hold){
        $config[$key] = $_POST[$key];
    }
    $newJson = json_encode($config);
    file_put_contents('configuration.json', $newJson);
}
foreach($config as $key => $hold)
  {

        echo '<div class="form-group">
        <label for="user">'.$key.'</label><input type="text" id="user" name="'.$key.'" class="form-control" value="'.$hold.'" required></input><br></div>';
  }

$newJson = json_encode($config);
?>


        <button type="submit" class="btn btn-primary formbtn" id="formbtn" onclick="select()">Update</button>
    </form>
</div>

</body>

</html>