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

<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
</head>
<body>
<h1>Zotero Collections</h1>

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

echo "<h3>Username: " . $username . "</h3>";
echo "<h3>User ID: " . $userID . "</h3>";

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

    $html = '<table class="head"><thead><tr>' .
        '<th scope="col">Name</th>' .
        '<th scope="col">Items</th>' .
        '<th scope="col">Subcollections</th>' .
        '<th scope="col">Link</th></tr></thead>';

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
            '<button type="submit" class="button" value="' . $keys[$i] .
            '">View table</button></form></td></tr>';
        $i++;
    }
    return $html;
}
?>
</body>
<script type="application/javascript">
    // var dynData = $.getScript("dynData.js");

    $(document).ready(function(){
        $('.button').click(function(e){
            $.ajax({
                type: "POST",
                url: 'getData.php',
                data: { 'ckey': $(this).val() }
            }).done(function(msg){
                // console.log("Data saved: " + msg);
                // $.getScript("dynData.js");
                window.location.replace("display.html");
            });
      });
    });
</script>

</html>