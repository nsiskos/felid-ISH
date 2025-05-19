/*
 * D3 !!!!
 */

/* var w = +d3.select("div#overview_graph").attr("width");
var h = +d3.select("div#overview_graph").attr("height"); */

/*
var w = document.getElementById("overview_graph").clientWidth;
var h = document.getElementById("overview_graph").clientHeight;
*/


class scatterplot 
{
	
	constructor(opts)
	{
		this.dataset = opts.data;
		this.element = opts.element;
		this.width = opts.width;
		this.height = opts.height;
		this.bubbleClass = opts.bubbleClass
		this.padding = 30;
			
		this.daysMinX = d3.min(this.dataset, d => d['daysBetween']);

		this.daysMaxX = d3.max(this.dataset, d => d['daysBetween']);
		
		this.draw();
		
	}
	
	
	draw () {
		
		
		this.element.innerHTML = '';
		this.svg = d3.select(this.element).append("svg").attr("width", this.width).attr("height", this.height);
		
		this.majorGroup = this.svg.append("g");
		
		this.createScales();
		this.addAxes();
		this.addPoints();
	}
	
	createScales() {
		
		this.xScale = d3.scaleLinear()
			.domain([this.daysMinX-1, this.daysMaxX+50])
			.range([this.padding, this.width - this.padding * 2]);

		this.yScale = d3.scaleLinear()
			.domain([0, 5.5])
			.range([this.height-this.padding, this.padding]);
		
	}
	
	addAxes() {
		
		this.xAxis = d3.axisBottom(this.xScale).ticks(10);
		this.yAxis = d3.axisLeft(this.yScale).ticks(5);
	
	
		/* horizontal axis label */
		this.svg.append("text")             
			.attr("transform","translate(" + (this.width-30) + " ," + (this.height-this.padding+15) + ")")
			.style("text-anchor", "middle")
			.text("days");
	
	
		/* vertical axis label */
		this.svg.append("text")
			.attr("transform", "rotate(-90)")
			.attr("y", 0)
			.attr("x",-this.height/9)
			.attr("dy", "1em")
			.style("text-anchor", "middle")
			.text("rating"); 
	
		this.svg.append("g")
			.attr("class", "axis")
			.attr("transform", "translate(0,"+(this.height-this.padding)+")")
			.call(this.xAxis);
	
	
		this.svg.append("g")
			.attr("class", "axis")
			.attr("transform", "translate("+this.padding+",0)")
			.call(this.yAxis);
		
		
	}
	
	addPoints() {

		const ageGroups = this.majorGroup.selectAll("g").data(this.dataset).enter()
			.append("g")
			.attr("id", (d, i) => i)
	/*		.attr("id", function (d, i) {return i;}) */
		/*	.on("mouseover", over)
			.on("mouseout", out) */
		;
		
		const circle = ageGroups
			.append("circle")
			.attr("cx", d => this.xScale(d['daysBetween']))
			.attr("cy", d => this.yScale(d['ratingAVG']))
			.attr("r", 3)
			.attr("class", this.bubbleClass)
			.attr("id", d => "slide_id_"+d['slide_id'])
	/*	.on("mouseover", over)
		.on("mouseout", out) */
			;
	}
	
	setData (newDataSet) {
		this.dataset = newDataSet;
		
		this.draw();
	}
	
	searchGene (geneName) {
		return this.dataset.filter(function(slide) {
			return slide.GENE.toLowerCase().indexOf(geneName.toLowerCase()) !== -1
		})
	}
	
	changeBubbleClass (classRemove, classAdd, data) {
		
		data.forEach(function(element){
			var bubble = document.getElementById("slide_id_"+element.slide_id);
	
			bubble.classList.remove(classRemove);
			bubble.classList.add(classAdd);
		});
		
		return 1;
		
	}
	
	
}



	
function markSlideId (slide_id) {
	
	
	var id_token = "slide_id_"+slide_id;
	

	var bubble = document.getElementById(id_token);
	
	bubble.classList.remove("bubble_green");
	bubble.classList.add("bubble_orange");
	
/*	console.log(bubble);
	document.getElementById("slide_id_"+slide_id).classList.remove("bubble");
	document.getElementById("slide_id_"+slide_id).classList.add("bubble_orange");
*/
	
}


/*
const chart2 = new scatterplot({
	data: scoringSet,
	element: document.querySelector('#overview_graph'),
	width: 800,
	height: 400,
	bubbleClass: 'bubble'
});
*/