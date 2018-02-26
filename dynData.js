		
		var num;
		var request = new XMLHttpRequest();
		
		request.onload = function(){
			if (this.readyState == 4 && this.status == 200){
				num = JSON.parse(this.responseText);
				console.log(num);
				makeTable(num);
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
		
		var tablehead = ["Author", "Title", "Type", "ISBN"];
		var len = tablehead.length;
		var table = '<thead><tr>';
		for(i = 0; i < len; i++)
		{
			table += '<th>' + tablehead[i] + '</th>';
		}
		table +='</tr></thead><tbody>';
		for(i = 0; i < Authors.length; i++)
		{
			
		
			table += '<tr>';
			table += '<td>' + Authors[i] + '</td>';
			console.log(Authors[i]);
			table += '<td>' + Titles[i] + '</td>';
			table += '<td>' + Types[i] + '</td>';

			table += '<td>' + ISBN[i] + '</td>';
			table += '</tr>';
		}
		table += '</tbody>';
		var mainTable = document.getElementById('myTable');
		mainTable.innerHTML +=table;
	}