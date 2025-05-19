/*
 * embryonic JS lib
 *
 *
 */


// AJAX request!
function loadDoc (id_of_slide) {
	
	document.getElementById("pics_wrap").style.display = "block";
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("sectionSlides").innerHTML = this.responseText;
		}
	};
	xhttp.open("GET", "showImages.php?opr=single&slide_id=" + id_of_slide, true);
//	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send();
}

function loadLine (embryo_id, row_id) {
	
	document.getElementById("pics_wrap").style.display = "block";
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("sectionSlides").innerHTML = this.responseText;
		}
	};
	xhttp.open("GET", "showImages.php?opr=multiple&embryo_id=" + embryo_id+"&row_id="+row_id, true);
//	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send();
}

function showSlidePoints () {
	
	var i=0;

	for (i=0; i<arguments.length; ++i) {
		
		var id_token = "slide_id_"+arguments[i];
	

		var bubble = document.getElementById(id_token);
	
		bubble.classList.remove("bubble");
		bubble.classList.add("bubble_green");
		
/*		console.log(arguments[i]); */
	}
	
}

function addSildeToBasket (id_of_slide) {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("basket").innerHTML = this.responseText;
		}
	};
	xhttp.open("GET", "addToBasket.ajax.php?action=addSlide&slide_id=" + id_of_slide, true);
//	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send();
	document.getElementById("section_"+id_of_slide).innerHTML = "added";
}

function loadProbe (probeId, probeName, NCBI, solBook, fontColour, bgColour) {
	
	var dataToSend = "probeId=" + probeId + "&name=" + probeName + "&ncbi=" + NCBI + "&solBook=" + solBook + "&fontColour=" + fontColour + "&bgColour=" + bgColour;
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("probeDetails").innerHTML = this.responseText;
		}
	};
	
	xhttp.open("POST", "showProbe.php", true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send(dataToSend);
	
//	xhttp.open("GET", "showProbe.php?probe_id=" + probeId, true);
//	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
//	xhttp.send();
}

// clear inside the element with id = id_of_element !
function clearSections (id_of_element) {
	document.getElementById("sectionSlides").innerHTML = "";
	document.getElementById("pics_wrap").style.display = "none";
}


// functions below are used in serials
function allowDrop(ev) {
  ev.preventDefault();
}

function drag(ev) {
  ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev) {
  ev.preventDefault();
  var data = ev.dataTransfer.getData("text");
  ev.target.appendChild(document.getElementById(data));
}

function selectAll () {
	var all_pairs = document.querySelectorAll(".primer_pairs");
	var i;
	for (i=0; i<all_pairs.length; ++i) {
		all_pairs[i].checked = true;
	}
	
}

function selectNone () {
	var all_pairs = document.querySelectorAll(".primer_pairs");
	var i;
	for (i=0; i<all_pairs.length; ++i) {
		all_pairs[i].checked = false;
	}
	
}

function getPairs() {
	
	var all_pairs = document.querySelectorAll(".primer_pairs");
	var i;
	var checked_pairs = [];
	var jsoned_array;
	
	for (i=0; i<all_pairs.length; ++i) {
		if (all_pairs[i].checked == true) {
			checked_pairs.push(all_pairs[i].value);
		}
	}
	jsoned_array = JSON.stringify(checked_pairs);
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("seqViewerDiv").innerHTML = this.responseText;
		}
	};
	xhttp.open("POST", "showSeqViewer.php", true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("primer_pairs=" + jsoned_array);

//console.log(checked_pairs);
	

}

function renderReceiverData(table_width) {
// https://stackoverflow.com/questions/27629122/how-to-get-all-ids-inside-of-a-div-using-pure-javascript
	var everyChild = document.querySelectorAll(".div3");
	var i; // this is index
//	console.log(everyChild);
	var total_rows = everyChild.length / table_width;

	var hierarchy = [total_rows]; // this is the array of arrays
	var transient = []; // this is the transient array
	var row_number = 1;
	
	var jsoned_array; // this speaks for itself
	
	for (i = 0; i < everyChild.length; ++i) {
		
		if (everyChild[i].firstElementChild == null) {
			transient.push("not_set");
		} else {
			transient.push(everyChild[i].firstElementChild.id);
		}
		
		if ( ( (i+1) % table_width ) == 0 ) {
			hierarchy[row_number-1] = transient;
			row_number++;
			transient = [];
			
		}
		

	}
	jsoned_array = JSON.stringify(hierarchy);
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("trans_out").innerHTML = this.responseText;
		}
	};
	xhttp.open("POST", "showSerial.php", true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("setting=" + jsoned_array);
	
//	document.getElementById("trans_out").innerHTML = hierarchy.length+"<br />"+hierarchy[0]+"<br />"+hierarchy[1]+"<br />"+hierarchy[2];
//	document.getElementById("trans_out").innerHTML = jsoned_array;
}

function loadPrimer(labId) {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("triple_rock_middle").innerHTML = this.responseText;
		}
	};
	xhttp.open("GET", "showPrimer.php?lab_id=" + labId, true);
//	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send();
}

function loadPrimerPair(pairIds) {
	
	document.getElementById("primer_pair_info").innerHTML = "<img src=\"icons/ajax_loader.gif\" style=\"border:none;\"></img><br />efetching from NCBI...";
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("primer_pair_info").innerHTML = this.responseText;
		}
	};
	xhttp.open("GET", "showPrimerPairInfo.php?pair_ids=" + pairIds, true);
//	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send();
}


function showMonth(year) {
	var i, j;
	var months = [];
	var uniqueMonths = [];
	var months_selector;
	var found = "NO";
	var months_en = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
//	console.log(allDates[0].toString().substr(0, 4));
	
//	document.getElementById("select_month").innerHTML = "";
	document.getElementById("select_day").innerHTML = "";
//	document.getElementById("expriments").innerHTML = "";
	
	if (year<2015) {
		document.getElementById("select_month").innerHTML = "";
		return;
	}
	
	for (i=0;i<allDates.length;++i) {
		if (allDates[i].substr(0, 4) == year.toString()) {
			months.push(allDates[i]);
		}
	}
	
	for (i=0;i<months.length;++i) {
		let yearMonth = months[i].substr(0, 6);
		for (j=0;j<uniqueMonths[j];++j) {
			if (uniqueMonths[j] == yearMonth) {
				found = "YES";
			} else {
				continue;
			}
		}
		if (found == "YES") {
			found = "NO";
			continue;
		} else {
//			console.log(yearMonth);
			uniqueMonths.push(yearMonth);
		}
	}
	
	uniqueMonths.sort();
	
	months_selector = "<select name=\"month\" onchange=\"showDay(this.options[this.selectedIndex].value);\">";
	months_selector += "<option value=\"0\">MONTH</option>";
	for (i=0;i<uniqueMonths.length;++i) {
//		console.log(uniqueMonths[i].substr(4,2));
		let thisMonth = uniqueMonths[i].substr(4,2);
		let month_index = Number(thisMonth) - 1;
		months_selector += "<option value="+year+"_"+thisMonth+">"+months_en[month_index]+"</option>";
	}
	months_selector += "</select>";
	
	document.getElementById("select_month").innerHTML = months_selector;
	
//	console.log(uniqueMonths);
}

function showDay(yearMonth) {
	
//	console.log(yearMonth);
	
	// splitted: yearMonthExploded[0] contains year and yearMonthExploded[1] contains month
	var yearMonthExploded = yearMonth.split("_");
	var days = [];	
	if (yearMonthExploded[1]>12 || yearMonthExploded[1]<1) {
		document.getElementById("select_day").innerHTML = "";
		return;
	}
	
	var i;
	var output;
	var months_en = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
	let month_index = Number(yearMonthExploded[1]) - 1;
	
	for (i=0;i<allDates.length;++i) {
		if (allDates[i].substr(0, 6) == (yearMonthExploded[0]+yearMonthExploded[1]).toString()) {
//			console.log(allDates[i].substr(4, 2));
//			console.log(yearMonthExploded[1].toString());
// 			console.log(allDates[i]);
			days.push(allDates[i]);
//			days.push();
		}
	}
	
	days.sort();
//	console.log(allDates);
//	console.log(days);
	output = "<select name=\"day\" onchange=\"showExp(this.options[this.selectedIndex].value);\">";
	output += "<option value=\"0\">EXPERIMENT</option>";
	for (i=0;i<days.length;++i) {
		let theDay = days[i].substr(6,2);
		output += "<option value="+days[i]+">"+theDay+" - "+months_en[month_index]+" - "+yearMonthExploded[0]+"</option>";
	}
	output += "</select>";
	
	document.getElementById("select_day").innerHTML = output;
//	console.log(days);
}

function showExp(experimentDate) {
	
	
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("experiments").innerHTML = this.responseText;
		}
	};
	xhttp.open("GET", "showExperiment.php?date=" + experimentDate, true);
//	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send();
	
/*	document.getElementById("sectionSlides").innerHTML = ""; */
	
//	console.log(yearMonth);
}

function off() {
  document.getElementById("overlay").style.display = "none";
}

function showMapOf(ncbi) {
//	console.log(ncbi);
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("seqViewerContainer").innerHTML = this.responseText;
		}
	};
	xhttp.open("GET", "showSeqViewer.php?ncbi=" + ncbi, true);

	xhttp.send();
	
	document.getElementById("overlay").style.display = "block";
//	document.getElementById("seqViewerContainer").innerHTML = ncbi;
}

function mouseOverStartSpinCog (indieId) {
	
	document.getElementById(indieId).classList.add("fa-spin");
	
}

function mouseOutStopSpinCog (indieId) {
	
	document.getElementById(indieId).classList.remove("fa-spin");
	
}
