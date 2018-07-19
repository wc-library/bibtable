/**
 * dynData.js
 *
 * Javascript program to take the JSON from getData and format it into a HTML table
 * with a collapsible section for Abstract and all attachments
 *
 * Authors: Robin Kelmen, Jesse Tatum
 */

var num; //holds json parsed response from server
var request = new XMLHttpRequest();

request.onload = function(){
    if (this.readyState === 4 && this.status === 200){
        if(this.responseText === "00"){
            // CHECKS IF api key is missing
            document.getElementById("api_error").innerHTML = "error: Missing API key";
            document.getElementById("loader").style.display = "none";
            die("Api_key Issue");
        }

        // console.log(this.responseText);
        num = JSON.parse(this.responseText);
        makeTable(num); // construct a table

        // start the loader that waits while zotero source is called, and table is constructed
        $(function(){

            myVar = setTimeout(showPage, 500);
        });

        // Allow extra to minimize
        $(".extra").click(function (){
            $(this).slideToggle(300);
            $("td div", $(this)).hide();
        });

        // Don't minimize when links are selected
        $(".extra a").click(function (e){
            e.stopPropagation();
        });

        // reveals hidden div with abstracts and links
        $(".source").click(function(event) {
            // $(this).find(".extra").hide();
            $(this).next().slideToggle(300);
            $(this).next().find('.content').slideToggle(300);
        });
    }
};

// TODO: Request is failing in Chrome when call is async
request.open("GET", "getData.php", false); //request info from api
request.send();

function showPage(){
    document.getElementById("myTable").style.display = "block"; //displays the table

    // Initialize tablesorter
    $(function()
    {
        $table = $("#myTable").tablesorter({
            theme: 'blue',
            widthFixed : true,

            widgets: ["zebra", "filter", "pager"], // Color code even and odd rows, add search boxes
            widget_options: {
                filter_childRows: false,
                filter_startsWith: false,
                filter_ignoreCase: true,
                filter_external: '.search',
                filter_reset: '.reset',
                filter_searchDelay : 200,
                filter_saveFilters : true
            }
        });

        $.tablesorter.filter.bindSearch($table, $('.search'));
        $.tablesorter.fixColumnWidth($table);

        $('.reset').click(function() {
            $('table').trigger('sortReset');
            // TODO: find way to clear dropdown
            return false;
        });
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
    let table = '<thead><tr><th>Title</th>';
    table += '<th>Author</th>';
    table += '<th>Year</th>';
    table += '<th class="filter-select filter-onlyAvail">Type</th>';
    table +='</tr></thead><tbody>';

    let i = 0;
    let size = Titles.length;

    let Attachments = new Array(size);

    while (i < size){ // Append all attachments to their parents and remove them from the list
        if(Types[i] === "Attachment") {
            let pkey = ParentItems[i];
            if(pkey !== "") {
                let pindex = Keys.indexOf(pkey);
                Attachments[pindex] = '<p><strong>' + Titles[i] + ': </strong>' + '<a href="' + URLs[i] + '" target="_blank">' + URLs[i] + '</a></p>';
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


            table += '<tr class="extra tablesorter-childRow"><td colspan="4">';

            // this constructs the hidden div but does not yet add it to the table
            let hidden = '<div class="extra content " id="' + i + '" style="display: none;">';
            if (Abstracts[i] !== "" && Abstracts[i] !== undefined) {
                hidden += '<p><strong> Abstract</strong>: ' + Abstracts[i] + '</p>';
            }
            if (URLs[i] !== "") {
                hidden += '<p><strong>Link: </strong><a href="' + URLs[i] + '" target="_blank">' + URLs[i] + '</a></p>';
            }

            // Add other attachments to hidden div
            if (Attachments[i] != null && Attachments[i] !== undefined)
                hidden += Attachments[i];

            // Add Publishers, publishing location, and ISBN as available
            hidden += constructT(Publishers[i], "\n<b>Publishers</b>: ") +
                constructT(Places[i], " ") + constructT(ISBN[i], ". <b>ISBN</b>: ") + '</div>';

            table += hidden;
            table += '</tr></td>';
        }
        i++;

    }
    table += '</tbody>'; // close off table

    var mainTable = document.getElementById('myTable'); // get the table named "myTable"
    mainTable.innerHTML +=table; // add table to html page
    document.getElementById("loader").style.display = "none";
    document.getElementById("loader-wrapper").style.display = "none";
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