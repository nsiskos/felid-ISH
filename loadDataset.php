<?php


try {
    /*** connect to SQLite database ***/

	$db = new PDO("sqlite:embryos.db");

} catch (PDOException $e) {
    echo $e->getMessage();
    echo "<br><br>Database -- NOT -- loaded successfully .. ";
    die( "<br><br>Query Closed !!! $error");
}

if (!$db) {
	echo $db->lastErrorMsg();
}

$sql = "select 
	section.slide_id,
	embryo.id as embryo_id,
	embryo.name as embryo_name,
	round(avg(section.rating),1) as ratingAVG, 
	gene.gene_name||'_'||gene.solution_book as GENE, 
	substr(slide.cut_date, 1, 4) ||'-'||substr(slide.cut_date, 5, 2)||'-'||substr(slide.cut_date, 7, 2) as CDATE, 
	substr(slide.experiment_date, 1, 4)||'-'||substr(slide.experiment_date, 5, 2)||'-'||substr(slide.experiment_date, 7, 2) AS EDAY 
from section 
inner join slide on slide.id=section.slide_id 
inner join gene on slide.gene=gene.id
inner join embryo on slide.embryo_id=embryo.id 
group by slide_id
";

$date_ret = $db->prepare($sql);
$date_ret->execute();

$dataSet = array();

$dataSet = $date_ret->fetchAll(PDO::FETCH_ASSOC);

#echo "TEST: ".strtotime($dataSet[0]['CDATE']);


array_walk($dataSet, function(&$score, $index){
	$start = strtotime($score['CDATE']);
	$end = strtotime($score['EDAY']);
	if ($end >= $start) {
		$score['daysBetween'] = ($end-$start)/(24*60*60);
	} else {
		$score['daysBetween'] = "minus";
	}
});

#$extras_in_header .= "<script src=\"https://d3js.org/d3.v5.js\"></script>\n";

echo "<script>var scoringSet=".json_encode($dataSet, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK).";</script>\n";

#$extra_css .= "<link href=\"styles/graphADD.css\" rel=\"stylesheet\" type=\"text/css\">";

#$graphTitle .= "score and section oldness";
#$display_block .= "<div id=\"overview_graph\">\n";
#$display_block .= "</div>\n";

echo "<script type=\"text/javascript\" src=\"ScoreScatter.js\"></script>\n";
	
?>