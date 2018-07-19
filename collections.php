<?php
/**
 * Interface to display the contents of a user's Zotero collections
 * and link to a viewing table.
 *
 * Author: Jesse Tatum
 * Date: 7/5/18
 * Time: 3:02 PM
 */
?>
<html>
<head>
    <title>Collections</title>
    <link rel="stylesheet" href="stylesheets/collections.css">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Bootstrap -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
    <div class="jumbotron jumbotron-fluid">
        <h1 class="display-4" id="title">Zotero Collections</h1>
        <div>

<?php
include 'api_key.php';
global $links;
global $api_key;
global $ckey;

if($api_key == ''){
    echo "00";
    exit;
}

$opts = array(
    'http'=>array(
        'method'=>"GET",
        'header'=>"Zotero-API-Key: " . $api_key
    )
);
$context = stream_context_create($opts); // Create request with API key in headers

// Grab User Info
$userInfo = json_decode(file_get_contents('https://api.zotero.org/keys/' . $api_key, false, $context), true);
$username = $userInfo["username"];
$userID = $userInfo["userID"];

echo "<h4 class='lead' id='user'>Username: " . $username . "</h4>";
echo "<h4 class='lead' id='pwd'>User ID: " . $userID . "</h4>";

?>
    </div>
</div>
</div>
<div class="loader">
</div>

<?php
//require 'cachefile.json';
// Grab collection info for user
$response = file_get_contents('https://api.zotero.org/users/77162/collections/top', false, $context);
$jarray = json_decode($response, true); // json to array

$table = parse($jarray);
echo $table;

function parse($jarray){
    global $keys;
    global $names;
    global $items;
    global $links;
    global $subcollections;

    $html = '<table id="collection-table" class="table table-striped"><thead class="thead-light"><tr>' .
        '<th scope="col">Collection Name</th>' .
        '<th scope="col">Items</th>' .
        '<th scope="col">Sub-collections</th>' .
        '<th scope="col">Link</th></tr></thead><tbody>';

    $i = 0;
    foreach ($jarray as $piece) {
        $names[$i] = $piece["data"]["name"];
        $keys[$i] = $piece["data"]["key"];
        $items[$i] = $piece["meta"]["numItems"];
        $links[$i] = $piece["links"]["self"]["href"];
        $subcollections[$i] = $piece["meta"]["numCollections"];

        $html .= '</td>' . '<tr><td>' . $names[$i];
        $html .= '<td>' . $items[$i] . '</td>';
        $html .= '<td>' . $subcollections[$i] . '</td>';
        $html .= '<td><form method="post">' .
            '<button type="submit" class="button btn btn-primary" value="' . $keys[$i] .
            '">View table</button></form></td></tr>';
        $i++;
    }
    $html .= "</tbody><div id='loader-wrapper'><div id='loader'></div></div></table>";
    return $html;
}
?>

<div class="container">
    <h1 class="page-header">Bibtable</h1>
    <div>
        <a href="collections.php">Back</a><br>
        <input class="search pull-left" type="search" data-column="all" placeholder="Search all" autocomplete="off">
        <!--<input class="filter-select filter-onlyAvail pull-left" type="search" data-column="all" placeholder="Tags" autocomplete="off">-->
        <!--curl &#45;&#45;header "Zotero-API-Key: DnmDsLoJFyrJ0UrgMjS6gfJ3" https://api.zotero.org/users/77162/collections/MW994N9D/tags-->
        <button type="button" id="reset" class="reset">Reset Sort</button>

    </div>

    <div id="api_key_error">
        <h2 style="text-align:center; color: grey;" id="api_error"></h2>
    </div>
    <table style="display:none;" id="myTable" class="tablesorter-default" ></table>
</div>

</body>
<script type="application/javascript">

    // var collection = document.getElementById("collection-table");
    // var myTable = document.getElementById("myTable");

    let loader = document.getElementById("loader");
    let loaderdiv = document.getElementById("loader-wrapper");
    loader.style.display="none";

    $(document).ready(function(){
        // myTable.style.display = "none";
        $('.button').click(function(e){
            e.preventDefault();
            loader.style.display = "block";
            loaderdiv.style.display= "block";
            // window.location.href = "display.html";
            $.ajax({
                type: "POST",
                url: 'getData.php',
                data: { 'ckey': $(this).val() },
                success: function(msg) {
                    console.log(msg);
                    loader.style.display = "none";
                    loaderdiv.style.display= "none";
                    // collection.style.display= "none";
                    // myTable.style.display= "block";
                    // $.getScript("dynData.js");

                    window.location.replace('display.html');
                },
                error: function(error){
                    window.location.href = "collections.php";
                    alert("Error loading table. Please try again.\nError details: " + JSON.stringify(error));
                }
            }).done(function(msg) {
                $.getScript('dynData.js');
            });
        });
    });

    // $(function()
    // {
    //     $("#myTable").tablesorter({
    //         theme: 'blue',
    //         widthFixed : true,
    //
    //         widgets: ["zebra", "filter", "pager"], // Color code even and odd rows, add search boxes
    //         widget_options: {
    //             filter_childRows: false,
    //             filter_startsWith: false,
    //             filter_ignoreCase: true,
    //             filter_external: '.search',
    //             filter_reset: '.reset',
    //             filter_searchDelay : 200,
    //             filter_saveFilters : true
    //         }
    //     });
    //
    //     $.tablesorter.filter.bindSearch($table, $('.search'));
    //     $.tablesorter.fixColumnWidth($table);
    //
    //     $('#reset').click(function() {
    //         $('table').trigger('sortReset');
    //         // TODO: find way to clear dropdown
    //         return false;
    //     });
    // });

</script>
</html>