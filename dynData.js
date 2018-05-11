/**
 * dynData.js
 *
 * Javascript program to take the JSON from getData and format it into a HTML table
 */




var num; //holds json parsed response from server
var request = new XMLHttpRequest();

request.onload = function(){
    if (this.readyState === 4 && this.status === 200){
        if(this.responseText === "00"){
            //CHECKS IF api key is missing
            document.getElementById("api_error").innerHTML = "error: Missing API key";
            document.getElementById("loader").style.display = "none";
            document.getElementById("loadinginfo").style.display="none";
            die("Api_key Issue");
        }

        num = JSON.parse(this.responseText);
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
            $("div, div", $(this)).slideToggle("fast");

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
    var Keys = num.keys;
    var ParentItems = num.parentItem;

    // Create table headers
    let tablehead = ["Author", "Title", "Year", "Type"];
    let len = tablehead.length;
    let table = '<thead><tr>';
    let tl = 0;
    while(tl < len)
    {
        table += '<th>' + tablehead[tl] + '</th>';
        tl++;
    }
    table +='</tr></thead><tbody>';

    let i = 0;
    let size = Titles.length;

    var Attachments = new Array(size);

    while (i < size){
        if(Types[i] == "Attachment") {
        	let pkey = ParentItems[i];
        	console.assert(pkey != "");
        	let pindex = Keys.indexOf(pkey);
            Attachments[pindex] += '<p><strong>' + Titles[i] + '</strong> ' + '<a href="' + URLs[i]+ '">' + URLs[i] + '</a></p>';
            Titles[i] = ""; // Remove title to specify that it should no longer be added to table
        }
        i++;
    }

    i = 0;
    while (i < size) {
        let source = "";
        let yearRE = /\b\d{4}\b/;
        let year = yearRE.exec(Dates[i]);
        if(year == null){
            year = ""; // some sources don't have dates, will ask about policy on these
        }

        if (Titles[i] !== "") {
            //add these in the order of the tablehead array elements
            table += '<tr>';
            table += '<td class="hidden">' + LastNames[i] + '</td>';
            table += '<td class="hidden" >' + Titles[i] + '</td>';
            table += '<td class="hidden">' + year + '</td>';
            table += '<td class="hidden">' + Types[i] + '</td>';

            //this constructs the link and abstracts hidden div but does not add it yet
            let linkNAbs = '<div class="extra" id="' + i + '" style="display: none;">';
            if (Abstracts[i] != "") {
                linkNAbs += '<p><strong> Abstract</strong>: ' + Abstracts[i] + '</p><p>';
                source = "source"; // if this has a link, make it clickable and highlightable/
            } else
				linkNAbs+= '<p><strong>N/A</strong></p>';

            if (URLs[i] != "") {
                linkNAbs += '<strong>Link: </strong>';
                source = "source"; // if this has an abstract, make it clickable and
                linkNAbs += '<a href="' + URLs[i] + '">' + URLs[i] + '</a>';
            }


            // Add item info
            table += '<td colspan=5><div class="' + source + '">' + '<b id ="Title">' +
                constructT(Titles[i]) + '</b>' + constructT(Authors[i]) + constructT(Publishers[i]) +
                constructT(Places[i]) + constructT(Dates[i]) + constructT(ISBN[i]) + constructT(Types[i]);

            //anything that needs to be visible only in the drop down

            if (Attachments[i] != null && Attachments[i] != undefined)
                linkNAbs += Attachments[i];


            linkNAbs += '</div></td></tr>';
            table += linkNAbs; //append links and abstracts to table
        }
        i++;

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