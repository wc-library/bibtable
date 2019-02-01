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
var ckey = $('script[src*=dynData]').attr('data-ckey');

request.onload = function(){
    document.getElementById("loader").style.display = "block";
    document.getElementById("loader-wrapper").style.display = "block";

    if (this.readyState === 4 && this.status === 200){
        if(this.responseText === "00"){
            // CHECKS IF api key is missing
            document.getElementById("api_error").innerHTML = "error: Missing API key";
            document.getElementById("loader").style.display = "none";
            die("Api_key Issue");
        }

        tmp = this.responseText; // Split source from PHP file
        console.log(tmp); 
        num = JSON.parse(tmp);
        makeTable(num); // construct a table

        // Build table from API response
        $(function(){
            myVar = setTimeout(showPage, 500);
        });

        // Allow extra to collapse
        $(".extra").click(function (){
            $(this).slideToggle(300);
            $("td div", $(this)).hide();
        });

        // Don't collapse when links are selected
        $(".extra a").click(function (e){
            e.stopPropagation();
        });

        // Reveal hidden div with abstracts and links
        $(".source").click(function() {
            $(this).next().slideToggle(300);
            $(this).next().find('.content').slideToggle(300);
        });

        $("#tags").click(function (e){
            e.stopPropagation();
            $('.tablesorter-childRow:visible').hide();
            $('.content').hide();
        })
    }
};

// TODO: Request is failing in Chrome when call is async
request.open("POST", "getData.php", true); //request info from api
request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded'); // Encode URL (https)
request.send('ckey=' + ckey); // Send with collection key

function showPage(){
    document.getElementById("myTable").style.display = "table"; // Displays the table as a table

    // Initialize tablesorter
    $(function()
    {
        let $table = $("#myTable").tablesorter({
            widthFixed : false, // Allow for table-fixed to work in bibtable.css
            widgets: ["zebra", "filter"], // Color code even and odd rows, add search boxes
            widget_options: {
                filter_childRows: false,
                filter_startsWith: false,
                filter_ignoreCase: true,
                filter_reset: '.reset',
                filter_searchDelay : 200,
                filter_saveFilters : true,
                filter_resetOnEsc: true,
                filter_searchFiltered: false,
                filter_defaultFilter: {
                    '.year': 'tablesorter-headerDesc'
                }
            }
        });

        let array = $.tablesorter.filter.getOptions($table, 4, true); // Get tags array (data-column 4)

        let sorted = Array();
        let x;
        let y;
        for(x = 0; x < array.length; x++) {
            let tmp = array[x].trim().split(';'); // Create whitespace trimmed array
            for(y = 0; y < tmp.length; y++) {
                if (tmp[y].length > 1 && jQuery.inArray(tmp[y], sorted) === -1) {
                    let val = tmp[y].toLowerCase().split(' ').map(function (word) {
                        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase(); // Capitalize first letter of each word
                    }).join(' ');
                    sorted.push(val);
                }
            }
        }

        // Sort items ignoring case (already normalized)
        sorted.sort(function (a, b){
            if (a < b)
                return -1;
            else if (b < a)
                return 1;
            else
                return 0;
        });

        var used = [];
        for(x = 0; x < sorted.length; x++) { // Go through list of used keys to avoid duplicates
            tmp = used[sorted[x]];
            if(tmp !== true)
                $('#tags').append('<option>' + sorted[x] + '</option>'); // Add unique keys to Tags dropdown
            used[sorted[x]] = true;
        }

        // Bind searches to element
        $.tablesorter.filter.bindSearch($table, $('#tags'));
        $.tablesorter.filter.bindSearch($table, $('.search'));
        $.tablesorter.fixColumnWidth($table);

        // To set default sort on Year
        // $('th .tablesorter-header [data-column="2"]').click();
        // console.log($('th .tablesorter-header [data-column="2"]'));

        $('.reset').click(function() {
            // Hide expanded childRow divs
            $('.tablesorter-childRow:visible').hide();
            $('.content').hide();

            $('table').trigger('filterAndSortReset'); // Reset input fields
            // $('.tablesorter-filter-row [data-column="3"] .tablesorter-filter')[0].selectedIndex = 0; // Type field
            $('.search').val(""); // Search all box
            $('#tags')[0].selectedIndex = 0; // Tags dropdown

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
    let Tags = num.tags;
    let Publication = num.publication;
    let Pub = "";

    let tokenized = Array();
    let i;

    // Split Tags into a sanitized array with all items and a tokenized string separated by commas
    for(i = 0; i < Tags.length; i++) {
        if (Tags[i].length >= 1)
            tokenized[i] = Tags[i].join('; ');
        else
            tokenized[i] = '';
    }


    // Create table headers
    let table = '<thead><tr><th>Title</th>';
    table += '<th>Author</th>';
    table += '<th> Publication/Publisher</th>';
    table += '<th class="year">Year</th>';
    table += '<th class="tags">Tags</th>';
    table += '</tr></thead><tbody>';
    //filter-onlyAvail
    i = 0;
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

    let validTable = false;

    i = 0;
    while (i < size) { // Loop through all items
        let yearRE = /\b\d{4}\b/;
        let year = yearRE.exec(Dates[i]);
        if(year == null){
            year = ""; // some sources don't have dates
        }

        if (Titles[i] !== "") { // Skip empty titles or attachments
            // add these in the order of the table head array

            if(Publication[i] !== null && Publication[i] !== ''){
                Pub = Publication[i];
            }else if(Publishers[i] !== null && Publishers[i] !== ''){
                Pub = Publishers[i];
            }else {
                Pub = "";
            }

            table += '<tr class="source">';
            table += '<td class="title"><b>' + Titles[i] + '</b></td>';
            table += '<td class="author">' + Authors[i] + '</td>';
            table += '<td class="publication ">' + Pub + '</td>';
            table += '<td class="year">' + year + '</td>';

            // table += '<td class="type">';
           
            // if (Types[i] !== null) // Avoid null type
            //     table += Types[i];
            // else
            //     table += 'N/A';
            // table += '</td>';
            table += '<td class="tags">' + tokenized[i] + '</td></tr>'; // Invisible row for sorting
            

            table += '<tr class="extra tablesorter-childRow"><td colspan="5">';

            // this constructs the hidden div but does not yet add it to the table
            let hidden = '<div class="extra content " id="' + i + '" style="display: none;">';
            if (Abstracts[i] !== "" && Abstracts[i] !== undefined) {
                hidden += '<p><strong> Abstract: </strong>  ' + Abstracts[i] + '</p>';
            }
            if (URLs[i] !== "") {
                hidden += '<p><strong>Link: </strong><a href="' + URLs[i] + '" target="_blank">' + URLs[i] + '</a></p>';
            }

            // Add other attachments to hidden div
            if (Attachments[i] != null && Attachments[i] !== undefined)
                hidden += Attachments[i];

            // Verify that table has meaningful contents
            if (hidden.length > 0)
                validTable = true;

            // Add Publishers, publishing location, and ISBN as available
            hidden += constructT(Publishers[i], "\n<b>Publishers</b>: ") +
                constructT(Places[i], "") + constructT(ISBN[i], "<b>ISBN</b>: ") + '</div>';

            table += hidden;
            table += '</td></tr>';
        }
        i++;

    }
    table += '</tbody>'; // close off table
    if (!validTable) // Display error message if needed
        table += '<div class="invalid"><h2>Table is empty</h2><p>Please try selecting collection again.</p></div>';

    // Stop loaders
    document.getElementById("loader").style.display = "none";
    document.getElementById("loader-wrapper").style.display = "none";

    var mainTable = document.getElementById('myTable'); // get the table named "myTable"
    mainTable.innerHTML +=table; // add table to html page

}

/**
 *  Add ". " after non-empty strings for formatting
 *
 *  string: the string to be formatted
 *  type: string to preface the given item if it exists
 */
function constructT(string, type){

    if (string === "")
        return string;
    else
        return type + string + ". ";
}