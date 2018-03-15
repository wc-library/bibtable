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
$data = 'https://api.zotero.org/users/77162/collections/89F8HEPX/items?key=[]&format=json&limit='. $dataNum .'&start=' . $start; // the link to zotero api

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
	global $i; 
	global $itemTypes;
	global $titles;
	global $shortTitles;
	global $creators;
	global $dates;
	global $places;
	global $publisher;
	global $isbns;
	global $abstracts;
	global $urls;

	foreach ($data as $work) {
		$scope = $work["data"];

		if(array_key_exists("itemType", $scope))
			$itemTypes[$i] = $scope["itemType"];
		else
			$itemTypes[$i] = "";

		if(array_key_exists("title", $scope))
			$titles[$i] = $scope["title"];
		else
			$titles[$i] = "";

		if(array_key_exists("shortTitle", $scope))
			$shortTitles[$i] = $scope["shortTitle"];
		else
			$shortTitles[$i] = "";

		if(array_key_exists("creators", $scope))
			$creators[$i] = $scope["creators"][0]["firstName"] . " " . $scope["creators"][0]["lastName"];
		else
			$creators[$i] = "";

		if(array_key_exists("date", $scope))
			$dates[$i] = $scope["date"];
		else
			$dates[$i] = "";

		if(array_key_exists("place", $scope))
			$places[$i] = $scope["place"];
		else
			$places[$i] = "";

		if(array_key_exists("publisher", $scope))
			$publishers[$i] = $scope["publisher"];
		else
			$publishers[$i] = "";

		if(array_key_exists("ISBN", $scope))
			$isbns[$i] = $scope["ISBN"];
		else
			$isbns[$i] = "";

		if(array_key_exists("abstractNote", $scope))
			$abstracts[$i] = $scope["abstractNote"];
		else
			$abstracts[$i] = "";

		if(array_key_exists("url", $scope))
			$urls[$i] = $scope["url"];
		else
			$urls[$i] = "";

		$i++;
	}

}

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

//var_dump($shortTitles);
//var_dump($creators);
//var_dump($dates);
//var_dump($places);
//var_dump($publisher);
//var_dump($isbns);
//var_dump($abstracts);
//var_dump($urls);

//$allData

echo json_encode(($allData));

?>