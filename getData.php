<?php

/**
* This  defines methods for parsing data from the Zotero API
* The set up bassically keep the fields in arrays for easy access
* If one has the index of all one of the fields, i.e title, the the 
* index can be applied for other fields that are associated with the 
* title. There is some unused space in the arrays for some empty fields
* but this is less of the usual case. Well, I hope. Also, I dont know how to 
* document in php. Forgivenesss!!!
*
* publicationarray[index][somekey][possible value or even array] is the struction of the api return
*
*@author Robin Kelmen <robin.kelmen@my.wheaton.edu>
*/
include 'api_key.php';
$dataNum = 100; // the limit of sources we want to pull
$start = 0;
// the link to zotero api

$itemTypes = array();//getDataField($info, "itemType");
$titles = array();//getDataField($info, "title");
$shortTitles = array();
$creators = array();//getDataField($info, "creators");
$dates = array();//getDataField($info,"date");
$places = array();//getDataField($info,"place");
$publishers = array();//getDataField($info,"publisher");
$isbns = array();//getDataField($info,"ISBN");
$abstracts = array();//getDataField($info, "abstractNote");
$urls = array(); //getDataField($info, "url");
$i = 0; // keeps track of array position


function getApiResults(){
	global $dataNum, $start, $api_key;
$isEmpty = false;
while(!$isEmpty && $start < 900){
	$data = 'https://api.zotero.org/users/77162/collections/89F8HEPX/items?key='. $api_key .'&format=json&limit='. $dataNum .'&start=' . $start;

		$response = file_get_contents($data); // pulls in the data 
		if($response == "[]"){
			$isEmpty = true;
		}
		$info = json_decode($response, true); // decodes jason and creates an object

		getClassicFields($info);
		
		$start +=100;
	}


}





/**
* Searches Through the data field finds fields within data
* Can be used to return  itemTypes, version, key, title, and creators 
* in an indexed array
* 
*@param data the object to search, 
*@param field the field (within the data key) of the object to seach
*@return returnField the the field requested in an indexed array
*/
function getDataField($data, $field){
	$returnField= array();
	$i = 0;
	foreach ($data as $work) {
		$scope = $work["data"];
		if(array_key_exists($field, $scope))
			$returnField[$i] = $scope[$field];
		else
			$returnField[$i] = "not";
		$i++;

	}

	var_dump($returnField);
	return $returnField;
}

/**
* Searches the whole object for the given field
* for any publication,  can return an indexed array of
* the fields
*
*@param data the whole json array or associative entries e.g "key": "value" or "key" : array {...}
*@param field the field being sought or key
*@return returnField an indexed array of the requested field
*/
function getClassicFields($data){

	//aparently one cannot manipulate global variables within a method 
	//without specifying that they are global
	global $i; 
	global $itemTypes;
	global $titles;
	global $shortTitles;
	global $creators;
	global $dates;
	global $places;
	global $publishers;
	global $isbns;
	global $abstracts;
	global $urls;

	//look through all the data 
	foreach ($data as $work) {
		$scope = $work["data"];

		
		$itemTypes[$i] = checknStore( "itemType", $scope);
		$titles[$i] = checknStore( "title", $scope);
		$shortTitles[$i] = checknStore("shortTitle", $scope);

		//this handled this way because the creators data comes in different formats
		if(array_key_exists("creators", $scope)){
			
			if(array_key_exists("firstName", $scope["creators"][0]))
				$creators[$i] = $scope["creators"][0]["firstName"] . " " . $scope["creators"][0]["lastName"];
			else
				$creators[$i] = $scope["creators"][0]["name"];
		}
		else
			$creators[$i] = "";

		$dates[$i] = checknStore("date", $scope);
		$places[$i] = checknStore("place", $scope);
		$publishers[$i] = checknStore("publisher", $scope);
		$isbns[$i] = checknStore("ISBN", $scope);
		$abstracts[$i] = checknStore("abstractNote", $scope);
		$urls[$i] = checknStore("url", $scope);
		$i++;
	}

}

/**
* This is a helper method for isolating a field
* it helps reduce clatter in the getClassicFields
* bassically checks if key exists because some json objects
* dont have the field, e.g URLs 
* @param The string that is the key to the value we are looking for e.g creators => "Jane Deer"
* @param The scope of our search, certain fields occur within objects within objects
*/
function checknStore($string, $scope){
	
	//if the key doest exist leave it black otherwise store it
	if(array_key_exists($string, $scope))
		return $scope[$string];
	else
		return  "";
}

//$allData = (object) array();
function makeAllData(){
	global $itemTypes;
	global $titles;
	global $shortTitles;
	global $creators;
	global $dates;
	global $places;
	global $publishers;
	global $isbns;
	global $abstracts;
	global $urls;

$allData = new stdClass();
$allData->itemtypes = $itemTypes;
$allData->titles = $titles;
$allData->shorttitles = $shortTitles;
$allData->creators = $creators;
$allData->dates = $dates;
$allData->places = $places;
$allData->publishers = $publishers;
$allData->isbns = $isbns;
$allData->urls = $urls;
$allData->abstracts = $abstracts;
return ($allData);
}

//var_dump($shortTitles);
//var_dump($creators);
//var_dump($dates);
//var_dump($places);
//var_dump($publisher);
//var_dump($isbns);
//var_dump($abstracts);
//var_dump($urls);

//$allData
	
	

	function json_cached_results() {
    
    $expires = NULL;
    $cache_file = dirname(__FILE__) . '/cachefile.json';
    $expires = time() - 2*60*60;
    
    if( !file_exists($cache_file) ) die("Cache file is missing: $cache_file");

    //chmod($cache_file, 0755);
    // Check that the file is older than the expire time and that it's not empty
    if ( filectime($cache_file) < $expires || file_get_contents($cache_file)  == '' ) {

        // File is too old, refresh cache
        getApiResults();
        $api_results =  json_encode(makeAllData()); 
        
      
        						//returnData();
        

        // Remove cache file on error to avoid writing wrong xml
        if ( $api_results  != null){
        	
            file_put_contents($cache_file,  $api_results);
        }
        else
           file_put_contents($cache_file, "");
    } else {
        
        // Fetch cache
        $api_results = (file_get_contents($cache_file));
      
        
        
    }

    return (($api_results));
}
echo json_cached_results();
?>