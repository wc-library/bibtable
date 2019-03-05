<?php
/**
 * Interface to display the contents of a user's Zotero collections
 * and link to a viewing table.
 *
 * Author: Jesse Tatum
 * Date: 7/5/18
 */
$config = include('configuration.php');
?>
<html>
<head>
    <title>Collections</title>
    <link rel="stylesheet" href="stylesheets/collections.css">

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
            global $ckey;
            
            if($api_key == ''){
                echo "00";
                exit;
            }

            // Create request context with API key in headers
            $opts = array(
                'http'=>array(
                    'method'=>"GET",
                    'header'=>"Zotero-API-Key: " . $api_key
                )
            );
            $context = stream_context_create($opts);

            // Grab User Info
            $userInfo = json_decode(file_get_contents('https://api.zotero.org/keys/' . $api_key, false, $context), true);
            $username = $userInfo["username"];

            echo "<h4 class='lead' id='user'>Username: " . $username . "</h4>";

            ?>
        </div>
    </div>
</div>
<div class="loader">
</div>

<?php
// Grab all collection info for user
// TODO has not been tested with over 100 collections
//$configure = include('configuration.php');
$start = 0;
$type = $config['collectionType'];
$groupID = $config['groupID'];
$address = 'https://api.zotero.org/'.$type.'/'.$groupID.'/collections?limit=100';
while(true) { // Run until break

    $response = file_get_contents($address, false, $context);
    $jarray = json_decode($response, true); // JSON to array

    $table .= parseTable($jarray);

    if(count($jarray) < 100) // Stop loop if current is less than limit
        break;
    $start+=100;
}

echo $table;

/*
 * Parses the JSON array returned from collections in the Zotero API
 * and returns a formatted table with links to the collections
 */
function parseTable($jarray){
    $keys;
    $names;
    $items;
    $links;
    $subcollections;
    $parent;

    // Create table
    $html = '<table id="collection-table" class="table table-striped"><thead class="thead-light"><tr>' .
        '<th scope="col">Collection Name</th>' .
        '<th scope="col">Items</th>' .
        '<th scope="col">Sub-collections</th>' .
        '<th scope="col">Parent</th>' .
        '<th scope="col">Link</th></tr></thead><tbody>';

    $rows = Array();
    // Loop through array and pull desired values
    $i = 0;
    $host = gethostname();
    foreach ($jarray as $piece) {
        $names[$i] = $piece["data"]["name"];
        $keys[$i] = $piece["data"]["key"];
        $items[$i] = $piece["meta"]["numItems"];
        $links[$i] = $piece["links"]["self"]["href"];
        $subcollections[$i] = $piece["meta"]["numCollections"];
        $parent[$i] = $piece["data"]["parentCollection"];

        $row = '<tr id="' . $keys[$i] . '">';
        $row .= '<td>' . $names[$i] . '</td>';
        $row .= '<td>' . $items[$i] . '</td>';
        $row .= '<td>' . $subcollections[$i] . '</td>';
        $row .= '<td>' . $parent[$i] . '</td>';
        $row .= '<td><form class="form-inline btn-toolbar">' .
            '<button type="button" class="button btn btn-primary form-group button-display" value="' . $keys[$i] .
            '">View table</button><button type="button" class="button btn btn-primary form-group button-iframe" data-toggle="modal" data-target="#Modal' . $i . '" value="' . $keys[$i] .
            '">Get iframe</button></form></td></tr>';
        
        $row .='    <div class="modal fade" id="Modal' . $i . '" role="dialog">
            <div class="modal-dialog">

            <div class="modal-content">
                <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">iframe</h4>
                </div>
                <div class="modal-body">
                <p>&ltiframe src="//' . $host. '/display.php?ckey=' . $keys[$i] . '" width="100%" height="99%"/&gt"</p>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
            
            </div>
        </div>';
        $rows[$i] = $row;
        $html .= $row;
        $i++;
    }

//    for($j = 0; $j < count($rows); $j++){
//        if ($subcollections[$j] != 0){
//            $length = $subcollections[$j];
//            for($k = 0; $k < $length; $k++){
//                if($parent[$k] == $keys[$j]){
//                    $rows[$j] += $rows[$k];
//                    $rows[$k] = '';
//                }
//            }
//        }
//        $html .= $rows[$j];
//    }

    $html .= "</tbody><div id='loader-wrapper'><div id='loader'></div></div></table>";
    return $html;
}
?>

</body>
<script type="application/javascript">

    $(document).ready(function(){
        $('.button-display').click(function(e){
            e.preventDefault();

            // Redirect to display with collection key
            window.open('display.php?ckey=' + $(this).val());
        });
    });

</script>
</html>