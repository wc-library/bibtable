		
var num; //holds json parsed response from server
var request = new XMLHttpRequest();

request.onload = function(){
	if (this.readyState == 4 && this.status == 200){
		if(this.responseText == "00"){
			//CHECKS IF api key is missing
			document.getElementById("api_error").innerHTML = "error: Missing API key";
			document.getElementById("loader").style.display = "none";
			document.getElementById("loadinginfo").style.display="none";
			die("Api_key Issue");
		}
		num = JSON.parse(this.responseText);
		console.log(num);
		
		makeTable(num); // construct a table
		
		//start the loader that waits while zotero source is called, and table is constructed
		$(function(){
			if(num != ""){
				document.getElementById("loadinginfo").innerHTML = "Formatting Sources...";
			}
			myVar = setTimeout(showPage, 1000);

		});
		//reveals hidden div with abstracts and links
		$(".source").click(function(){
			
			$parentSource = $(this);
			//$parentSource.siblings().find('.extra').slideUp();
			$("div, div", $(this)).slideToggle("slow");

		});
		
	}
};

request.open("GET", "getData.php", true); //request info from api

request.send();


function showPage(){
	document.getElementById("loader").style.display = "none"; //hides loading icon
	document.getElementById("loadinginfo").style.display="none"; //hides the loading message
	document.getElementById("myTable").style.display ="block"; //displays the table
	
	//calls the tablesorter witch should return A sortable table
	$(function()
	{
		$("#myTable").tablesorter(
		{
			widgets : [ "zebra", "filter" ]
		}
		);

	});

}
function makeTable(num){
	var Authors = num.creators;
	var Titles = num.titles;
	var ISBN = num.isbns;
	var Types = num.itemtypes;
	var Dates = num.dates;
	var Publishers = num.publishers;
	var Places = num.places;
	var Abstracts = num.abstracts;
	var URLs = num.urls;
	var LastNames = num.lastnames;


	//add new sort field in this array
	var tablehead = ["Author", "Title", "Year", "Type"];
	var len = tablehead.length;
	var table = '<thead><tr>';
	for(i = 0; i < len; i++)
	{
		table += '<th>' + tablehead[i] + '</th>';
	}
	table +='</tr></thead><tbody>';

	for(i = 0; i < Authors.length; i++)
	{

		source = "";
		var yearRE = /\b\d{4}\b/;
		var year = yearRE.exec(Dates[i]);
		if(year == null){
			year = 0; // some sources dont have dates, will ask about policy on these
		}
		
		//add these in the order of the tablehead array elements
		table += '<tr>';
		table += '<td class="hidden">' + LastNames[i] + '</td>';
		table += '<td class="hidden" >' + Titles[i] + '</td>';
		table += '<td class="hidden">' + year+ '</td>';
		table += '<td class="hidden">' + Types[i] + '</td>';
		
		//this constructs the link and abstracts hidden div
		var linkNAbs = '';

		linkNAbs += '<div class="extra" id="'+ i+'" style="display: none;"><p>';
		if(Abstracts[i] != ""){
			linkNAbs += '<strong> Abstract</strong>: ';
			source = "source"; // if this has a link, make it clickable and highlightable/
		}
		linkNAbs += Abstracts[i]+ '</p><p>'; // add abstract
		if(URLs[i] != ""){
			linkNAbs += '<strong>Link: </strong>';
			source = "source"; // if this has an abstract, make it clickable and
            linkNAbs +='<a href="' + URLs[i] + '">' + URLs[i] + '</a>'; // add link
        }

        linkNAbs += '</p></div></div></td></tr>';

		//this is the beggining of the citation paragraph set up 
		table += '<td colspan=5><div class="'+ source +'">'  +'<b id ="Title">' + constructT(Titles[i]) + '</b>' + constructT(Authors[i]) + constructT(Publishers[i]) + constructT(Places[i]) + constructT(Dates[i]) +  constructT(ISBN[i])+ constructT(Types[i]);
		
		//anything that needs to be visible only in the drop down

		table+= linkNAbs; //append links and abstracts to table

		
	}

	table += '</tbody>'; //close off table
	var mainTable = document.getElementById('myTable'); //get the table named "myTable"
	mainTable.innerHTML +=table; // add table to html page
	

	
}
/**
*  Method helps with formating, checks if string is empty. Adds a "." for non-empty strings
*
*  parameter: the string to be formated. 
*/
function constructT(string){

	var toReturn = "";
	if(string != ""){
		toReturn = string + ". ";

		
	}
	return toReturn;
}