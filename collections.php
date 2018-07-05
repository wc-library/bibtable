<?php
/**
 * Created by PhpStorm.
 * User: jessetatum
 * Date: 7/5/18
 * Time: 3:02 PM
 */
include 'api_key.php';
global $links;
global $api_key;

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

$context = stream_context_create($opts);
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
        '<th scope="col">Subcollections</th></tr></thead>';

    $i = 0;
    foreach ($jarray as $piece) {
        $names[$i] = $piece["data"]["name"];
        $keys[$i] = $piece["data"]["key"];
        $items[$i] = $piece["meta"]["numItems"];
        $links[$i] = $piece["links"]["self"]["href"];
        $subcollections[$i] = $piece["meta"]["numCollections"];

        $html .= '</td>' . '<tr><td>' . $names[$i];
        $html .= '<td>' . $items[$i] . '</td>';
        $html .= '<td>' . $subcollections[$i] . '</td></tr>';
        $i++;
    }
    return $html;
}
?>