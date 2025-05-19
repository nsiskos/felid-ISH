/*
 * D3 !!!!
 */

/* var w = +d3.select("div#overview_graph").attr("width");
var h = +d3.select("div#overview_graph").attr("height"); */

var w = document.getElementById("overview_graph").clientWidth;
var h = document.getElementById("overview_graph").clientHeight;

var padding = 30;

var dataset = embryo_data;

var totalEmbryos = 0;
/* calculate total embryo number */
for (var i=0; i<dataset.length; ++i) {
	totalEmbryos += dataset[i]["cut_no"]+dataset[i]["uncut_no"];
}


var agesMinX = d3.min(dataset, function(d){
	return d['age'];
});

var agesMaxX = d3.max(dataset, function(d){
	return d['age'];
});

var plethosMinY = d3.min(dataset, function(d){
	return d['cut_no']+d['uncut_no'];
});

var plethosMaxY = d3.max(dataset, function(d){
	return d['cut_no']+d['uncut_no'];
});



var xScale = d3.scaleLinear()
	 .domain([agesMinX-1, agesMaxX+1])
	.range([padding, w - padding * 2]);

var yScale = d3.scaleLinear()
	.domain([plethosMinY-1, plethosMaxY+1])
.range([h-padding, padding]);

var rScale = d3.scaleLinear()
	.domain([plethosMinY, plethosMaxY])
.range([3, 50]);

var svg = d3.select("#overview_graph").append("svg").attr("width", w).attr("height", h);

var majorGroup = svg.append("g");

var ageGroups = majorGroup.selectAll("g").data(dataset).enter()
	.append("g")
	.attr("id", function (d, i) {return i;})
/*	.on("mouseover", over)
	.on("mouseout", out) */
;
/*.attr("transform", function (d, i) {return "translate(0," + i*10 + ")";}); */


var circle = ageGroups
	.append("a")
	.attr("href", function (d) { return "frontpage.php?opr=showag&age="+d["age"] })
/*	.attr("target", "_blank") */
	.append("circle")
	.attr("cx", function(d){
		return xScale(d['age']);
	})
	.attr("cy", function(d){
		return yScale(d['cut_no']+d['uncut_no']);
	})
	.attr("r", function(d){
		return rScale(d['cut_no']+d['uncut_no']);
	})
	.attr("class", "bubble")
	.on("mouseover", over)
	.on("mouseout", out)
	;


var vLine = ageGroups
	.append("line")
	.attr("x1", function (d) {
		return xScale(d['age']);
	})
	.attr("y1", function (d) {
		return yScale(d['cut_no']+d['uncut_no']) + rScale(d['cut_no']+d['uncut_no']);
	})
	.attr("x2", function (d) {
		return xScale(d['age']);
	})
	.attr("y2", h-padding)
	.attr("class", "vertLine");

var div = d3.select("#overview_graph").append("div")
	.attr("class", "tooltip")
	.style("opacity", 0);

/*
var hLine = ageGroups
	.append("line")
	.attr("x1", padding)
	.attr("y1", function (d) {
		return yScale(parseInt(d['cut_no'], 10)+parseInt(d['uncut_no'], 10));
	})
	.attr("x2", function (d) {
		return xScale(d['age'])-rScale(parseInt(d['cut_no'], 10)+parseInt(d['uncut_no'], 10));
	})
	.attr("y2", function (d) {
		return yScale(parseInt(d['cut_no'], 10)+parseInt(d['uncut_no'], 10));
	} )
	.attr("class", "vertLine");
*/

var xAxis = d3.axisBottom(xScale).ticks(10);
var yAxis = d3.axisLeft(yScale).ticks(5);
	
	
/* horizontal axis label */
svg.append("text")             
	.attr("transform","translate(" + (w-30) + " ," + (h-padding+15) + ")")
	.style("text-anchor", "middle")
	.text("E day");
	
	
/* vertical axis label */
svg.append("text")
	.attr("transform", "rotate(-90)")
	.attr("y", 0)
	.attr("x",-h/9)
	.attr("dy", "1em")
	.style("text-anchor", "middle")
	.text("embryos (n)"); 
	
svg.append("g")
	.attr("class", "axis")
	.attr("transform", "translate(0,"+(h-padding)+")")
	.call(xAxis);
	
	
svg.append("g")
	.attr("class", "axis")
	.attr("transform", "translate("+padding+",0)")
	.call(yAxis);

var legend = svg.append("text")
    .style("font-size","12px")
	.attr("transform", "translate("+(w-80)+" ," + padding + ")")
    .text("n = "+totalEmbryos)
;

function over (d, i) {
	d3.select(this).transition().duration(50).style("fill", "green");
	
	var plethos = d['cut_no']+d['uncut_no'];
	
	var text = "E"+d["age"]+" embryos<br />"+"sectioned: "+ d['cut_no'] + " of " + plethos;
	div.transition().duration(50).style("opacity", .9);
	div.html(text)
		.style("left", (d3.event.pageX) + "px")
		.style("top", (d3.event.pageY - 28) + "px")
	;
}

function out (d, i) {
	d3.select(this).transition().duration(1000).style("fill", null);
	
	div.transition().duration(500).style("opacity", 0);
}
