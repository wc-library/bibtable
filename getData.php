<?php

/**
 * This script is designed to create a request to the Zotero API and
 * parse the fields and store them in arrays for easy access.
 *
 * The arrays are then sent as JSON to dynData.js for the table creation.
 * Results are cached to speed load time with a cache expiry time of 2 hours.
 *
 *
 *@author Robin Kelmen <robin.kelmen@my.wheaton.edu>, Jesse Tatum <jesse.tatum@my.wheaton.edu>
 */
include 'api_key.php';
include 'json_cache.php';
global $config;
$config = include('configuration.php');
// include 'display.php';

$limit = 100; // the limit of sources we want to pull. This is the max supported by the API
$start = 0;
global $dataValue;
$dataValues = array(
// Corresponding name in dynData.js
'creators' => array(), // Authors
'titles' => array(),// Titles
'isbns' => array(),// ISBNs
'itemtypes' => array(),// Types
'dates' => array(),// Dates
'publishers' => array(),// Publishers
'places' => array(),// Places
'abstracts' => array(),// Abstracts
'urls' => array(),// URL links
'keys' => array(),// Keys
'parentItem' => array(),// ParentItems
'tags' => array(),    // Description Tags
'publication' => array() //publication title for articles
);
global $ckey;
if(isset($_POST['ckey']))
    $ckey = $_POST['ckey'];
else
    $ckey = $_GET['ckey'];

if ($ckey === null)
    die("Error obtaining collection key. Please go back and try again.");

$cache_dir = dirname(__FILE__) . '/cache/' . $ckey . '.json';

if(isset($_POST['refresh'])){
    $cache_is_stale = $_POST['refresh'];
}
else if(isset($_GET['refresh'])){
    $cache_is_stale = $_GET['refresh'];
}
else {
    $cache_is_stale = false;
}
// error_log('cache status: '. var_dump($cache_is_stale));
if (!$cache_is_stale){
    print json_cached_results($cache_dir);
} else {
    // The following section is to send a quick response so it doesn't wait for the full cache refresh
    // Start 
    ob_start();
    // Send your response. Irrelevant as the refreshCache() function doesn't return anything. 
    print "Received request for cache refresh";
    // Get the size of the output.
    $size = ob_get_length();
    // Disable compression (in case content length is compressed).
    header("Content-Encoding: none");
    // Set the content length of the response.
    header("Content-Length: {$size}");
    // Close the connection.
    header("Connection: close");
    // Flush all output.
    ob_end_flush();
    ob_flush();
    flush();
    // Close current session (if it exists).
    if(session_id()) session_write_close();

    // Do the actual work of updating the requested cache
    writeCache($cache_dir); 
}

// Pull all data from Zotero. This (with parsing) is the biggest bottleneck
function getApiResults(){
    global $limit, $api_key, $ckey, $start, $config;
    if($api_key == ''){
        echo "00";
        exit;
    }

    $start = 0;
    $type = $config['collectionType'];
    $groupID = $config['groupID'];
    while(true) { // Run until break

        $data = 'https://api.zotero.org/'.$type.'/'.$groupID.'/collections/'. $ckey  . '/items?key=' . $api_key .
            '&itemTypes?locale&format=json&limit=' . $limit . '&start=' . $start;
        $response = file_get_contents($data); // pulls in the data
        $info = json_decode($response, true); // decodes json and creates an object
        parseFields($info, $start);

        if(count($info) < 100) // Stop loop if current is less than limit
            break;
        $start+=100;
    }
}

/**
 * Searches the whole object for the given field
 * for any publication,  can return an indexed array of
 * the fields
 *
 *@param data the whole json array or associative entries e.g "key": "value" or "key" : array {...}
 *@param field the field being sought or key
 */
function parseFields($data, $offset){

    // Remind globals
    global $dataValues;

    //look through all the data
    $i = 0;
    foreach ($data as $work) {

        $dataValues['keys'][$i + $offset] = $work["key"]; // Guaranteed value

        if(isset($work["data"]["parentItem"]))
        $dataValues['parentItem'][$i + $offset] = $work["data"]["parentItem"];
        else
        $dataValues['parentItem'][$i + $offset] = ""; // Empty string to avoid null


        $scope = $work["data"];

        // this handled this way because the creators data comes in different formats
        $authorString = "";
        if(array_key_exists("creators", $scope) && array_key_exists("creators", $scope) != NULL){

            $len = count($scope["creators"]); // length of creators array
            // will hold the string of creators built up by the while loop
            $counter = 0; // counts up the number of creators in the creators array

            if($len > 1){ // We will need to loop through all creators
                /* loop invariant, counter is current creator,
                at end of loop, the counter will be at the last creators position
                this will help with formatting
                */
                do {
                    if(isset($scope["creators"][$counter]["firstName"] )){ // check if key is set
                        $authorString = $authorString . $scope["creators"][$counter]["firstName"] . " " . $scope["creators"][$counter]["lastName"] . "; "; // the .; is a format from the old broken zotero parser
                    }
                    else{
                        $authorString = $authorString . $scope["creators"][$counter]["name"] . ".; ";
                    }

                    $counter++; // increase counter, to get to next position
                } while($counter < $len-1 );
                //at the end of the loop we now hold the postion of last creator,
                //unfortunately, at this moment there are lots of if blocks, but .... parsing is like this
                if($len > 1) {
                    if (isset($scope["creators"][$counter]["firstName"])) {
                        $authorString = $authorString . "and " . $scope["creators"][$counter]["firstName"] . " " . $scope["creators"][$counter]["lastName"];
                    } else {
                        $authorString = $authorString . "and " . $scope["creators"][$counter]["name"];
                    }
                }
            } else if ($len == 1) {
                if (isset($scope["creators"][$counter]["firstName"]))// check if key is set
                    $authorString = $authorString . $scope["creators"][0]["firstName"] . " " . $scope["creators"][$counter]["lastName"]; // the .; is a format from the old broken zotero parser
                else
                    $authorString = $authorString . $scope["creators"][0]["name"];
            }
        }
        // Grab array of associated tags or empty string
        if (isset($scope["tags"]) && count($scope["tags"]) > 0) {
            $j = 0;
            $content = array();
            while (isset($scope["tags"][$j]["tag"])){
                $content[$j] = $scope["tags"][$j]["tag"];
                $j++;
            }
            $dataValues['tags'][$i + $offset] = $content;
        } else
        $dataValues['tags'][$i + $offset] = "";
        $dataValues['creators'][$i + $offset] = $authorString;
        $dataValues['itemtypes'][$i + $offset] = itemT( "itemType", $scope);
        $dataValues['titles'][$i + $offset] = checknStore( "title", $scope);
        $dataValues['dates'][$i + $offset] = checknStore("date", $scope);
        $dataValues['places'][$i + $offset] = checknStore("place", $scope);
        $dataValues['publishers'][$i + $offset] = checknStore("publisher", $scope);
        $dataValues['isbns'][$i + $offset] = checknStore("ISBN", $scope);
        $dataValues['abstracts'][$i + $offset] = checknStore("abstractNote", $scope);
        $dataValues['urls'][$i + $offset] = checknStore("url", $scope);
        $dataValues['publication'][$i + $offset] = checknStore("publicationTitle", $scope);
       
        $i++;
    }
}

/**
 * This is a helper method for isolating a field
 * it helps reduce clutter in the getClassicFields
 * basically checks if key exists because some json objects
 * don't have the field, e.g URLs
 * @param String string - The string that is the key to the value we are looking for e.g creators => "Jane Deer"
 * @param ArrayObject scope - The scope of our search, certain fields occur within objects within objects
 */
function checknStore($string, $scope){

    // Store key if found
    if(array_key_exists($string, $scope) && $scope[$string] != null)
        return $scope[$string];
    else
        return  "";
}

/**
* This method is similar to the checkNStore, except it works on itemtypes
* The items associative array maps a key which is an item type and
* returns the same item type in a better format
* @param The string that is the key to the value we are looking for e.g creators => "Jane Deer"
* @param The scope of our search, certain fields occur within objects within objects
*/
function itemT($string, $scope){

    $items = array(
        "journalArticle" => "Journal Article",
        "book" => "Book",
        "document" => "Document",
        "attachment" => "Attachment",
        "webpage" => "Web Page",
        "bookSection" => "Book Section",
        "thesis" => "Thesis",
        "blogPost" => "Blog Post",
        "magazineArticle" =>"Magazine Article",
        "conferencePaper" => "Conference Paper"
    );

    if(array_key_exists($string, $scope) && $scope[$string] != null)
        return $items[$scope[$string]];
    else
        return "";
}

//$allData = (object) array();
function makeAllData(){

    // Remind PHP of globals
    global $dataValues;

    $allData = new stdClass();
    $allData->keys = $dataValues['keys'];
    $allData->creators = $dataValues['creators'];
    $allData->itemtypes = $dataValues['itemtypes'];
    $allData->titles = $dataValues['titles'];
    $allData->dates = $dataValues['dates'];
    $allData->places = $dataValues['places'];
    $allData->publishers = $dataValues['publishers'];
    $allData->isbns = $dataValues['isbns'];
    $allData->urls = $dataValues['urls'];
    $allData->abstracts = $dataValues['abstracts'];
    $allData->parentItem = $dataValues['parentItem'];
    $allData->tags = $dataValues['tags'];
    $allData->publication = $dataValues['publication'];

    return ($allData);
}

function writeCache($cache_dir) {
//TODO: This function shouldn't keep the cache file open so long
    // Idea one: get the api_results first and then open the file for writing
    // Idea two: write to a temp file and then copy it into the cache file aftewards. 
    //global $cache_dir;

    // fopen will create or open as needed
    // we only open it for writing after we know we'll need 
    // to write to the file (otherwise it's 0 when we check for size)
    $cfh = fopen($cache_dir, 'wb');

    // Refresh cache
    getApiResults();
    $api_results = json_encode(makeAllData()); 

    // Write back to cache if results are valid
    if ($api_results != null && $api_results != '')
        fwrite($cfh, $api_results);
    else 
        fwrite($cfh, '');

    // Always close files
    fclose($cfh);

    // Results might be needed
    return $api_results;
}

function refreshCache(){
    global $ckey;

    $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "?refresh='true'&ckey=".$ckey;

    $result = file_get_contents($url);
    if ($result === FALSE) { 
        error_log('Something went wrong while requesting the cache to be refreshed.');
    } else {
        error_log('refresh cache request accepted');
    }
}
?>
