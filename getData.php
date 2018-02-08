<?php

$dataNum = 100; // the limit of sources we want to pull
$start = 99;
$data = 'https://api.zotero.org/users/77162/collections/89F8HEPX/items?key=&format=json&limit='. $dataNum .'&start=' . $start; // the link to zotero api
var_dump($data);
$response = file_get_contents($data); // pulls in the data 
$info = json_decode($response, true); // decodes jason and creates an object


$itemTypes = [];
$titles = [];
$shortTitles = [];
$creators = [];
$authors = [];

getCreators($info);
//var_dump($info);
function getCreators($data){
	$creators = array();
	$i = 0;

	foreach($data as $work){
		$scope = $work["data"];
		if(array_key_exists("creators", $scope))
			$creators[$i] = $scope["creators"];
		else
			$creators[$i] = "";
		$i++;
	}

	var_dump($creators);

}
echo count($info);
?>