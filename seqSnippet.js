var sequence;

function loadSeq (dom_id) {
	var seq = document.getElementById(dom_id).innerHTML;
	seq = seq.replace(/\s+/g, "").trim();
	
	var letters = seq.split('');
/*	return seq.split(''); */
	
	var frequencies = {};
	var found = false;
	
	letters.foreach(function (value, index) {
		
		for (i=0;i<frequencies.keys().length;++i) {
			
			if (index == frequencies.keys()[i]) {
				found = i;
				break;
			} else {
				continue;
			}
			
		}
		
		if (found) {
			++frequencies[found];
		} else {
			frequencies[found] = 1;
		}
		
	});
	
}