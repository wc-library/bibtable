<?php

/**
 * This  defines methods for parsing data from the Zotero API
 * The set up basically keep the fields in arrays for easy access
 * If one has the index of all one of the fields, i.e title, the the
 * index can be applied for other fields that are associated with the
 * title.
 *
 * publicationarray[index][somekey][possible value or even array] is the structure of the api return
 *
 *@author Robin Kelmen <robin.kelmen@my.wheaton.edu>, Jesse Tatum <jesse.tatum@my.wheaton.edu>
 */
include 'api_key.php';
$limit = 100; // the limit of sources we want to pull. This is the max supported by the API
$start = 0;

global $ckey;
if (isset($_POST['ckey']))
    $ckey = $_POST['ckey'];
echo $ckey;
// TODO Error check


// Corresponding name in dynData.js
global $creators;   // Authors
$creators = array();
global $titles;     // Titles
$titles = array();
global $isbns;      // ISBNs
$isbns = array();
global $itemtypes;  // Types
$itemtypes = array();
global $dates ;     // Dates
$dates = array();
global $publishers; // Publishers
$publishers = array();
global $places;     // Places
$places = array();
global $abstracts;  // Abstracts
$abstracts = array();
global $urls;       // URL links
$urls = array();
global $keys;       // Keys
$keys = array();
global $parentItem; // ParentItems
$parentItem = array();

echo json_cached_results();

function getApiResults(){
    global $limit, $api_key, $start, $ckey;
    if($api_key == ''){
        echo "00";
        exit;
    }

    $start = 0;
    while(true) { // Run until break

        $data = 'https://api.zotero.org/users/77162/collections/' . $ckey . '/items?key=' . $api_key .
            '&itemTypes?locale&format=json&limit=' . $limit . '&start=' . $start;
        $response = file_get_contents($data); // pulls in the data
        $info = json_decode($response, true); // decodes json and creates an object
        getClassicFields($info, $start);

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
function getClassicFields($data, $offset){

    // Remind globals
    global $creators;   // Authors
    global $titles;     // Titles
    global $isbns;      // ISBNs
    global $itemtypes;  // Types
    global $dates ;     // Dates
    global $publishers; // Publishers
    global $places;     // Places
    global $abstracts;  // Abstracts
    global $urls;       // URL links
    global $keys;       // Keys
    global $parentItem; // ParentItems


    //look through all the data
    $i = 0;
    foreach ($data as $work) {

		$keys[$i + $offset] = $work["key"]; // Guaranteed value

        if(isset($work["data"]["parentItem"]))
            $parentItem[$i + $offset] = $work["data"]["parentItem"];
        else
        	$parentItem[$i + $offset] = ""; // Assign dummy value to keep array index fill for each array


        $scope = $work["data"];

        // this handled this way because the creators data comes in different formats
        $authorString = "";
        if(array_key_exists("creators", $scope) && array_key_exists("creators", $scope) != NULL){

            if(isset($scope["creators"][0]["firstName"] )){
                $lastName = $scope["creators"][0]["lastName"];
            }else if(isset($scope["creators"][0]["name"])){
                $lastName = $scope["creators"][0]["name"];
            }

            $len = count($scope["creators"]); // length of creators array
            // will hold the string of creators built up by the while loop
            $counter = 0; // counts up the number of creators in the creators array

            if( $len > 1){ // We will need to loop through all creators
                /* loop invariant, counter is current creator,
                at end of loop, the counter will be at the last creators position
                this will help with formating
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
        else{ //not necessary, but makes it explicit that if none of the previous conditions are met, then ""
            $lastName = "";
        }

        $creators[$i + $offset] = $authorString;
        $itemtypes[$i + $offset] = itemT( "itemType", $scope);
        $titles[$i + $offset] = checknStore( "title", $scope);
        $dates[$i + $offset] = checknStore("date", $scope);
        $places[$i + $offset] = checknStore("place", $scope);
        $publishers[$i + $offset] = checknStore("publisher", $scope);
        $isbns[$i + $offset] = checknStore("ISBN", $scope);
        $abstracts[$i + $offset] = checknStore("abstractNote", $scope);
        $urls[$i + $offset] = checknStore("url", $scope);
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
    if(array_key_exists($string, $scope))
        return $scope[$string];
    else
        return  "";
}

/*
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

    if(array_key_exists($string, $scope))
        return $items[$scope[$string]];
    else
        return "";
}

//$allData = (object) array();
function makeAllData(){

    // Remind PHP of globals
	global $keys;
    global $itemtypes;
    global $titles;
    global $creators;
    global $dates;
    global $places;
    global $publishers;
    global $isbns;
    global $abstracts;
    global $urls;
    global $parentItem;

    $allData = new stdClass();
    $allData->keys = $keys;
    $allData->creators = $creators;
    $allData->itemtypes = $itemtypes;
    $allData->titles = $titles;
    $allData->dates = $dates;
    $allData->places = $places;
    $allData->publishers = $publishers;
    $allData->isbns = $isbns;
    $allData->urls = $urls;
    $allData->abstracts = $abstracts;
    $allData->parentItem = $parentItem;
    return ($allData);
}


function json_cached_results() {

    $cache_file = dirname(__FILE__) . '/cachefile.json';
    $expires = time() - 2*60*60; // 2 hours

    if(!file_exists($cache_file))
        die("Cache file is missing: $cache_file");

    // Check that the file is older than the expire time and that it's not empty
    if (filectime($cache_file) < $expires || file_get_contents($cache_file)  == '') {

        // File is too old, refresh cache
        getApiResults();
        $api_results =  json_encode(makeAllData());

        if($api_results != null) {
            file_put_contents($cache_file, $api_results);
        } else
            file_put_contents($cache_file, '');

    } else {
        // Fetch cache
        $api_results = (file_get_contents($cache_file));
    }

    return (($api_results));
}

?>
