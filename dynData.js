/**
 * dynData.js
 *
 * Javascript program to take the JSON from getData and format it into a HTML table
 * with a collapsible section for Abstract and all attachments
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
            myVar = setTimeout(showPage, 500);

        });

        $(".extra").click(function (){
            $(this).slideToggle(300);
            $("td div", $(this)).hide();
        });

        //reveals hidden div with abstracts and links
        $(".source").click(function(event) {
                $(this).next().slideToggle(300);
                $(this).next().find('.content').slideToggle(300);
        });
    }
};

request.open("GET", "getData.php", true); //request info from api
request.send();

function showPage(){
    document.getElementById("loader").style.display = "none"; //hides loading icon
    document.getElementById("loadinginfo").style.display = "none"; //hides the loading message
    document.getElementById("myTable").style.display = "block"; //displays the table

    // Initialize tablesorter
    $(function()
    {
        $("#myTable").tablesorter(
            {
                widgets: ["zebra", "filter"], // Color code even and odd rows, add search boxes
            }
        );
    });

}
function makeTable(num){
    // Create single-dimensional arrays from JSON
    let Authors = num.creators;
    let Titles = num.titles;
    let ISBN = num.isbns;
    let Types = num.itemtypes;
    let Dates = num.dates;
    let Publishers = num.publishers;
    let Places = num.places;
    let Abstracts = num.abstracts;
    let URLs = num.urls;
    let Keys = num.keys;
    let ParentItems = num.parentItem;

    // Create table headers
    let tablehead = ["Title", "Author", "Year", "Type"];
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

    let Attachments = new Array(size);

    while (i < size){ // Append all attachments to their parents and remove them from the list
        if(Types[i] === "Attachment") {
        	let pkey = ParentItems[i];
        	if(pkey !== "") {
                let pindex = Keys.indexOf(pkey);
                Attachments[pindex] = '<p><strong>' + Titles[i] + ': </strong>' + '<a href="' + URLs[i] + '">' + URLs[i] + '</a></p>';
            }
            Titles[i] = ""; // Remove title to specify that it should no longer be added to table
        }
        if(Abstracts[i] === "")
            Abstracts[i] = "N/A";
        i++;
    }

    i = 0;
    while (i < size) { // Loop through all items
        let yearRE = /\b\d{4}\b/;
        let year = yearRE.exec(Dates[i]);
        if(year == null){
            year = ""; // some sources don't have dates
        }

        if (Titles[i] !== "") { // Skip empty titles or attachments
            // add these in the order of the table head array
            table += '<tr class="source">';
            table += '<td><b>' + Titles[i] + '</b></td>';
            table += '<td>' + Authors[i] + '</td>';
            table += '<td>' + year + '</td>';
            table += '<td>' + Types[i] + '</td></tr>';


            table += '<tr class="extra"><td colspan="4">';

            // this constructs the hidden div but does not yet add it to the table
            let hidden = '<div class="extra content" id="' + i + '" style="display: none;">';
            if (Abstracts[i] !== "" && Abstracts[i] !== undefined) {
                hidden += '<p><strong> Abstract</strong>: ' + Abstracts[i] + '</p>';
            }
            if (URLs[i] !== "") {
                hidden += '<p><strong>Link: </strong><a href="' + URLs[i] + '">' + URLs[i] + '</a></p>';
            }

            // Add other attachments to hidden div
            if (Attachments[i] != null && Attachments[i] !== undefined)
                hidden += Attachments[i];

            // Add Publishers, publishing location, and ISBN as available
            hidden += constructT(Publishers[i], "\n<b>Publishers</b>: ") +
                constructT(Places[i], ". ") + constructT(ISBN[i], ". <b>ISBN</b>: ") + '</div>';

            table += hidden;
            table += '</tr></td>';
        }
        i++;

    }
    table += '</tbody>'; //close off table
    var mainTable = document.getElementById('myTable'); //get the table named "myTable"
    mainTable.innerHTML +=table; // add table to html page

}

/**
 *  Method helps with formatting, checks if string is empty. Adds a "." for non-empty strings
 *
 *  parameter: the string to be formatted.
 */
function constructT(string, type){

   if (string === "")
	   return string;
   else
   		return type + string;
}