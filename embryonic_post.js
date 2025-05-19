	window.onscroll = function() {top_receiver()};

	var header = document.getElementById("basket");

	var sticky = header.offsetTop;

	
	function top_receiver() {
	  if (window.pageYOffset > sticky) {
	    header.classList.add("sticky");
	  } else {
	    header.classList.remove("sticky");
	  }
	} 