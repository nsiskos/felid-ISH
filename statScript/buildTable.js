

/******************************************************/


var aNest = Array.from(d3.group(scoringSet, d => d["GENE"]), ([geneName, slideInfo]) => ({geneName, slideInfo}));

var tBody = d3.select("#probeInfo").selectAll("tr").data(aNest).enter().append("tr");

tBody
	.append("td")
	.append("a")
	.attr("href", "#overview_graph")
	.text(d => d.geneName)
	.on("click", clickGene)
;

tBody.append("td").text(a => a.slideInfo.length);
tBody.append("td").text(a => Number(d3.mean(a.slideInfo, d => d["ratingAVG"])).toFixed(2)+' ; '+Number(d3.median(a.slideInfo, d => d["ratingAVG"])).toFixed(2)+' ; '+Number(d3.min(a.slideInfo, d => d["ratingAVG"])).toFixed(1)+'-'+Number(d3.max(a.slideInfo, d => d["ratingAVG"])).toFixed(1) );
tBody.append("td").text(a => Number(d3.deviation(a.slideInfo, d => d["ratingAVG"])).toFixed(2) );

function clickGene (d) {
	var circles = d3.selectAll("circle");
	circles.attr("class", null);
	circles.attr("class", "bubble_gray");
	chart2.changeBubbleClass("bubble_gray", "bubble_orange", chart2.searchGene(d.geneName))
	
	/*
	var bNest = Array.from(d3.group(d.slideInfo, a => a["embryo_name"]), ([embryoName, slideInfo]) => ({embryoName, slideInfo}));
	
	var probeInfoDiv = d3.select("#detailsDiv").selectAll("p").data(bNest).enter();
	var embryoParag = probeInfoDiv.append("p");
	embryoParag.text(b => "F"+b.embryoName+': n='+b.slideInfo.length+' slides');

	
	
	console.log(bNest);
	*/

}
