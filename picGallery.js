/*
 * D3 !!!!
 */

/* var w = +d3.select("div#overview_graph").attr("width");
var h = +d3.select("div#overview_graph").attr("height"); */



class imageGallery {
	
	constructor(dataset, madre_id) {
		this.dataset = dataset;
		this.madre_id = madre_id;
/*		var return_to = madre_id.split("_");
		this.return_page = return_to[0];
		this.return_id = return_to[1]; */
	}
	
	showGal() {
		
		var appendImages = d3.select(".row").selectAll(".column")
		.data(this.dataset)
		.enter()
		.append("div")
		.attr("class", "column")
		;
	
		appendImages
/*		.append("a")
		.attr("href", "javascript:void(0);")
		.attr("href", function (d) { return d[1] })
		.attr("target", "_blank") */
		.append("img")
		.on("click", clickImg)
		.attr("src", function (d) {return d[1];})
		.attr("alt", function (d) {
			return d[0]+d[2];
		})
		.attr("width", "180px")
		.attr("id", function(d) { return "img_id_"+d[3];})
		;
	
		appendImages.append("div")
		.attr("class", "desc")
		.text(function (d) {
			return d[0]+d[2];
		})
		;
		
	}
	
}

function clickImg(d, i) {
	source = d[1];
	picId = d[3];
	document.getElementById("sectionSlides").innerHTML = "<img id=\"imgId_"+picId+"\" src=\""+source+"\" style=\"max-width:800px\"></img>";
	document.getElementById("pics_wrap").style.display = "block";
}

class formGallery extends imageGallery {
	
	constructor(dataset, madre_id) {
		super(dataset, madre_id);
	}
	
	showForm() {
		
		var appendForm = d3.select(".row").selectAll(".imageContainer")
		.data(this.dataset)
		.enter()
		.append("div")
		.attr("class", "imageContainer")
		.attr("id", function (d) {return "image_"+d[0]+"_"+d[3];})
		;
		
		appendForm
		.append("div")
		.attr("class", "imagePlace")
		.append("img")
		.attr("src", function (d) {return d[1];})
		.attr("alt", function (d) {
			return d[0]+d[2];
		})
		.attr("width", "180px")
		;
		
		var preForm = appendForm
		.append("div")
		.attr("class", "formPlace")
		;
		
		var theForm = preForm
		.append("form")
		.attr("action", "actuatorPicture.ajax.php?opr=alter")
		.attr("method", "POST")
		;
		
		theForm
		.append("input")
		.attr("type", "hidden")
		.attr("name", "photoKind")
		.attr("value", function (d) {return d[0];})
		;
		
		theForm
		.append("input")
		.attr("type", "hidden")
		.attr("name", "pic_id")
		.attr("value", function (d) {return d[3];})
		;
		
		theForm
		.append("input")
		.attr("type", "hidden")
		.attr("name", "return_to")
		.attr("value", this.madre_id)
		;
		
		theForm
		.append("p")
		.text(function (d) {return "F"+d[2]+" "+d[0]+":";})
		;
		
		theForm
		.append("p")
		.append("input")
		.attr("type", "text")
		.attr("size", 70)
		.attr("name", "img_location")
		.attr("placeholder", "File location")
		.attr("value", function (d) {
			return d[1];
		})
		;
		
		
		var secondParagraph = theForm
		.append("p")
		.text("Short description:")
		;
		
		secondParagraph.append("br");
		
		secondParagraph
		.append("input")
		.attr("type", "text")
		.attr("size", 70)
		.attr("name", "img_desc")
		.attr("value", function (d) {
			return d[4];
		})
		;
		
		theForm
		.append("input")
		.attr("class", "button")
		.attr("type", "submit")
		.attr("value", "alter")
		;
		
		preForm
		.append("br");
		
		preForm
		.append("button")
		.attr("class", "button")
		.attr("onclick", function (d) {
			return "deletePicture('image_"+d[0]+"_"+d[3]+"')";
		})
		.text("delete")
		;
		
		
	}
}

function deletePicture (div_id) {
	
	var picture_args = div_id.split("_");
	
	var picture_id = picture_args[2];
	var picture_kind = picture_args[1];
	
	var response = document.getElementById("ajaxResponse");
	
	response.innerHtml = "<img src=\"icons/ajax_loader.gif\" width=\"16px\" height=\"16px\">";
	
	
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("ajaxResponse").innerHTML = this.responseText;
		}
	};
	xhttp.open("POST", "actuatorPicture.ajax.php?opr=delete", true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("kind="+picture_kind+"&pic_id="+picture_id);
	
	var removal = document.getElementById(div_id).remove();
	
/*	console.log("delete picture no:"+picture_id);
	console.log("delete "+picture_kind+" picture");
*/	
}