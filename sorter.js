

function sortTable (event, dataset, n)
{
	
	var table = event.target.parentNode.parentNode.parentNode;
	var tbodyCont = table.getElementsByTagName("tbody");
	
/*	console.log(n); */
	
	var sorted_dataset = dataset.slice().sort((a, b) => d3.descending(a.orientation, b.orientation));
	
	var head_length = dataset.slice().length;
	
	var rows = d3.select("tbody").selectAll("tr")
	.data(sorted_dataset)
	.enter()
	.append("tr")
	;

	var cells = rows.selectAll("td")
	.data(function (d) {
		console.log("here"+d);
		return d;
	})
	.enter()
	.append("td")
	.text(function(d) {
		return d;
	});
/*	console.log(sorted_dataset);	*/
/*	
	for (i=0;i<head_length;++i) {
		
		var cells = rows
		.append("td")
		.text(function (d) {
			return d[i];
		})
		;
		
		console.log(cells);
		
	}
 
*/	
/*	console.log(tbodyId); 
	
	console.log(tbody); */
}

/*
function sortTable_tooHeavy (event, n)
{
	
	var tableId = event.target.parentNode.parentNode.parentNode.id;
	
	var table = document.getElementById(tableId);
	var switching = true;
	var position;
	var shouldSwitch;
	var switchcount = 0;	
	
	while (switching) 
	{
		switching = false;
		var rows = table.rows;
		
		for (i=1;i<(rows.length-1); i++)
		{
			
			var elem1 = rows[i].getElementsByTagName("td")[n];
			var elem2 = rows[i+1].getElementsByTagName("td")[n];
			
			if (elem1.innerHTML.toLowerCase() > elem2.innerHTML.toLowerCase())
			{
				shouldSwitch = true;
				position = i;
				break;
			}
		}
		
		if (shouldSwitch) {
			rows[position].parentNode.insertBefore(rows[position+1], rows[position]);
			switching = true;
			switchcount++;
		}
		
	}
	
	
}
*/