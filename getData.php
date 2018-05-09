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

*@author Robin Kelmen <robin.kelmen@my.wheaton.edu>
*/
include 'api_key.php';
$dataNum = 100; // the limit of sources we want to pull
$start = 0;
// the link to zotero api
$lastnames = array();
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
	if($api_key == ''){
		echo "00";

		exit;
	}
	while(!$isEmpty && $start < 900){
		$data = 'https://api.zotero.org/users/77162/collections/89F8HEPX/items?key='. $api_key .'&itemTypes?locale&format=json&limit='. $dataNum .'&start=' . $start;

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
	global $lastnames;



	//look through all the data 
	foreach ($data as $work) {
		$scope = $work["data"];


		//this handled this way because the creators data comes in different formats
		$authorString = "";
		$lastName = "";
		if(array_key_exists("creators", $scope) && array_key_exists("creators", $scope) != NULL){
			
			if(isset($scope["creators"][0]["firstName"] )){
				$lastName = $scope["creators"][0]["lastName"];
			}else{
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
				while($counter < $len-1 ){
					if(isset($scope["creators"][$counter]["firstName"] )){ // check if key is set
						$authorString = $authorString . $scope["creators"][$counter]["firstName"] . " " . $scope["creators"][$counter]["lastName"] . ".; "; // the .; is a format from the old brocken zotero parser
					}
					else{
						$authorString = $authorString . $scope["creators"][$counter]["name"] . ".; ";
					}
					$counter++; // increase counter, to get to next position

				}
				//at the end of the loop we now hold the postion of last creator, 
				//unfortunately, at this moment there are lots of if blocks, but .... parsing is like this
				if(isset($scope["creators"][$counter]["firstName"] )){
					$authorString = $authorString . "and " . $scope["creators"][$counter]["firstName"] . " " . $scope["creators"][$counter]["lastName"];

				}
				else{
					$authorString = $authorString .  "and " . $scope["creators"][$counter]["name"] ;
				}

				//$creators[$i] = $authorString; // save the author string 

			}else {
				//if its a single creator, this seems very much like repeated code, I will endevour to improve it, 
				//but for a single author the format is really simple and different
				if(isset($scope["creators"][0]["firstName"] )){
					$authorString = $scope["creators"][0]["firstName"] . " " . $scope["creators"][0]["lastName"];
				}
				else{
					if(isset($scope["creators"][0]["name"])){
						$authorString = $scope["creators"][0]["name"];
					}
				}
			}
		}
		else{ //not necessary, but makes it explicit that if none of the previous conditions are met, then ""
		$authorString = ""; 
		$lastName = "";
	}
	$creators[$i] = $authorString;
	$lastnames[$i] = $lastName;


	$itemTypes[$i] = itemT( "itemType", $scope);
	$titles[$i] = checknStore( "title", $scope);
	$shortTitles[$i] = checknStore("shortTitle", $scope);
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
	global $lastnames;

	
	$allData = new stdClass();
	$allData->creators = $creators;
	$allData->lastnames = $lastnames;
	$allData->itemtypes = $itemTypes;
	$allData->titles = $titles;
	$allData->shorttitles = $shortTitles;
	
	$allData->dates = $dates;
	$allData->places = $places;
	$allData->publishers = $publishers;
	$allData->isbns = $isbns;
	$allData->urls = $urls;
	$allData->abstracts = $abstracts;
	return ($allData);
}


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
