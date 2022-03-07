<?php
/**
 * Display a given collection as a table pulled from the cache
 *
 * Author: Jesse Tatum
 * Date: 7/5/18
 */
include 'api_key.php';
global $ckey;
$ckey = $_GET['ckey'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bibtable</title>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="tablesorter-master/css/theme.default.css">

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
    <link rel="stylesheet" type="text/css" href="stylesheets/bibtable.css">

</head>
<body>
<h1 class="hidden">Bibtable</h1>
<br/>
<div>
    <div class="form-inline">
        <div class="form-group">
        <input class="search pull-left form-control" type="search" id="search" data-column="all" placeholder="Search all fields" autocomplete="on">
        </div>
        <div class="form-group">
      <select id="tags" class="pull-left filter-match multiple form-control mb-2 mr-sm-2 mb-sm-0" data-width="fit" type="search" data-column="4">
            <option value="">Filter
            by Tags</option>
        </select>
        </div> 
        <div class="form-group">
        <button type="button" class="reset btn-default btn">Reset Sorting and Searches</button>
    </div>
    </div>
</div>

<div id='loader-wrapper'>
    <div id='loader'></div>
</div>

<div id="api_key_error">
    <h2 style="text-align:center; color: grey;" id="api_error"></h2>
</div>

<table style="display:none; table-layout:fixed;" id="myTable" class="tablesorter" ></table>
<script type="text/javascript" data-ckey="<?php echo htmlspecialchars($ckey) ?>" src="dynData.js"></script>
</body>
</html>

