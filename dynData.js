		
var num;
var request = new XMLHttpRequest();

request.onload = function(){
	if (this.readyState == 4 && this.status == 200){
		num = JSON.parse(this.responseText);
		console.log(num);
		makeTable(num);
		
		$(".source").click(function(){
			
			$parentSource = $(this);
			$parentSource.siblings().find('.extra').slideUp();
			$("div, div", $(this)).slideToggle("slow");
			
			



		});
		$(function()
		{
			$("#myTable").tablesorter();

		});
	}
};
request.open("GET", "getData.php", true);
request.send();

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


	var tablehead = ["Author", "Title", "Year", "ISBN", "Type"];
	var len = tablehead.length;
	var table = '<thead><tr>';
	for(i = 0; i < len; i++)
	{
		table += '<th>' + tablehead[i] + '</th>';
	}
	table +='</tr></thead><tbody>';
	for(i = 0; i < Authors.length; i++)
	{


		var yearRE = /\b\d{4}\b/;
		var year = yearRE.exec(Dates[i]);
		if(year == null){
			year = 0;
		}
		

		table += '<tr>';
		table += '<td class="hidden">' + Authors[i] + '</td>';

		table += '<td class="hidden" >' + Titles[i] + '</td>';
		table += '<td class="hidden">' + year+ '</td>';
		table += '<td class="hidden">' + ISBN[i] + '</td>';
		table += '<td class="hidden">' + Types[i] + '</td>';

		//this is the beggining of the citation paragraph set up 
		table += '<td colspan=5><div class="source">'  + constructT(Authors[i]) + '<i>' + constructT(Titles[i]) + '</i>' + constructT(Publishers[i]) + constructT(Dates[i]) + constructT(Places[i]) + constructT(ISBN[i])+ constructT(Types[i]);
		
		//anything that needs to be visible only in the drop down

		table += '<div class="extra" id="'+ i+'" style="display: none;"> <p>';
		if(Abstracts[i] != ""){
			table += '<strong> Abstract</strong>: ';
		}
		table += Abstracts[i]+ '</p><p>';
		if(URLs[i] != ""){
			table += '<strong>Link: </strong>';
		}
		
		
		table +='<a href="' + URLs[i] + '">' + URLs[i] + '</a><p></div></div></td>';
		table += '</tr>';

		
	}

	table += '</tbody>';
	var mainTable = document.getElementById('myTable');
	mainTable.innerHTML +=table;
	

	
}
function constructT(string){

	var toReturn = "";
	if(string != ""){
		toReturn = string + ". "
	}
	return toReturn;
}