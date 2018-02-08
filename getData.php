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
$dataNum = 100; // the limit of sources we want to pull
$start = 99;
$data = 'https://api.zotero.org/users/77162/collections/89F8HEPX/items?key=&format=json&limit='. $dataNum .'&start=' . $start; // the link to zotero api
var_dump($data);
$response = file_get_contents($data); // pulls in the data 
$info = json_decode($response, true); // decodes jason and creates an object


$itemTypes = array();//getDataField($info, "itemType");
$titles = array();//getDataField($info, "title");
$shortTitles = array();
$creators = array();//getDataField($info, "creators");
$dates = array();//getDataField($info,"date");
$places = array();//getDataField($info,"place");
$publisher = array();//getDataField($info,"publisher");
$isbns = array();//getDataField($info,"ISBN");
$abstracts = array();//getDataField($info, "abstractNote");
$urls = array(); //getDataField($info, "url");

getClassicFields($info);
var_dump($itemTypes);
var_dump($titles);
var_dump($shortTitles);
var_dump($creators);
var_dump($dates);
var_dump($places);
var_dump($publisher);
var_dump($isbns);
var_dump($abstracts);
var_dump($urls);


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
* for any publication, is faster because it does loops once for all the publications 
* 
*
*@param data the whole json array or associative entries e.g "key": "value" or "key" : array {...}
*@param field the field being sought or key
*@return returnField an indexed array of the requested field
*/
function getClassicFields($data){
	$i = 0;
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
			$itemTypes[$i] = "not";

		if(array_key_exists("title", $scope))
			$titles[$i] = $scope["title"];
		else
			$titles[$i] = "not";

		if(array_key_exists("shortTitle", $scope))
			$shortTitles[$i] = $scope["shortTitle"];
		else
			$shortTitles[$i] = "not";

		if(array_key_exists("creators", $scope))
			$creators[$i] = $scope["creators"];
		else
			$creators[$i] = "not";

		if(array_key_exists("date", $scope))
			$dates[$i] = $scope["date"];
		else
			$dates[$i] = "not";

		if(array_key_exists("place", $scope))
			$places[$i] = $scope["place"];
		else
			$places[$i] = "not";

		if(array_key_exists("publisher", $scope))
			$publishers[$i] = $scope["publisher"];
		else
			$publishers[$i] = "not";

		if(array_key_exists("ISBN", $scope))
			$isbns[$i] = $scope["ISBN"];
		else
			$isbns[$i] = "not";

		if(array_key_exists("abstractNote", $scope))
			$abstracts[$i] = $scope["abstractNote"];
		else
			$abstracts[$i] = "not";

		if(array_key_exists("url", $scope))
			$urls[$i] = $scope["url"];
		else
			$urls[$i] = "not";

		$i++;
	}

}

echo count($info);
?>