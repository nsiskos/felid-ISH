<?php

# pass $_GET['lab_id']
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', false);

session_start();
require_once("standards.php");



# this page requires user permission al least = 1
$page_permission = 1;

$out_block = "";

$now = time();
$difference = $now - $_SESSION['time'];

// perform the login check. $temporal_allownce is found inside standards.php
if ( !isset($_SESSION['time']) or ($difference > $temporal_allowance) or !isset($_SESSION['user_data']) ) { 

	echo "unauthorized access";
	die;

}

# this page requires user permission at least = 1
if ( ($_SESSION['user_data']['perm'] < $page_permission) or (!isset($_SESSION['user_data']['perm'])) ) {
	echo "You do not have sufficient priviledges to access this page!</br>";
	die;
}

$display_block = "";
$viewer_block = "";

require_once("db_handle.php");

if (!isset($_GET['ncbi'])) {
	
	echo "No ncbi provided. Exiting";
	die;
}
		
	//	echo $out_block;

	

$ncbi = $_GET['ncbi'];

$dataclones = array();
$seq_locus = "../seqtest/";
	
$sql = "select gene_name, solution_book, sequence from gene where NCBI_accession=?";
$genes_h = $db->prepare($sql);
$genes_h->bindValue(1, $ncbi, PDO::PARAM_STR);
$genes_h->execute();
$genes = $genes_h->fetchAll(PDO::FETCH_ASSOC);
#echo "<pre>";
#print_r($genes);
#print_r($genes->errorInfo());
#echo "</pre>";
	
		
include_once($seq_locus."sequ.class.php");
$sequence = new sequence($ncbi);
$probes = array();
foreach ($genes as $gene) {
	$probes["sb_".$gene['solution_book']] = $sequence->findThis($gene['sequence'])[0];
}

#echo "<pre>";
#print_r($probes);
#print_r($genes->errorInfo());
#echo "</pre>";	

$display_block .= "<h2>Comparison of <em>".$genes[0]['gene_name']."</em> probes</h2>\n";

$display_block .= "<p>";
$display_block .= "Accession number: ".$_GET['ncbi']."<br />\n";
$display_block .="GI number: ".$sequence->GI_number."<br />\n";
foreach ($probes as $key => $val) {
	$sol_book = explode("_", $key);
	$display_block .= "SB".$sol_book[1].": ".$val[0]." - ".$val[1]."<br />\n";

	$dataclones[] = "{ data clone { name %22SB".$sol_book[1]."%22, concordant TRUE, unique TRUE }, location mix { int { from ".$val[0].", to ".($val[0]+20).", strand plus, id gi ".$sequence->GI_number." },	int { from ".($val[1]-20).", to ".$val[1].", strand minus, id gi ".$sequence->GI_number." }	}, title %22Primer 1%22, exts {	{ type str %22DisplaySettings%22, data { { label str %22Weight%22, data int 1000 }}}}}";
		


}
	
$dataClonesString = implode(", ", $dataclones);
	
$display_block .= "</p>\n";	
		
$viewer_block .= "<div id=\"mySeqViewer1\" class=\"SeqViewerApp\" data-autoload><a href=\"content=PrimerBlast&appname=CatProbe&embedded=true&appname=felissimus&label=2&";
$range = $sequence->seqLength + 1500;
$viewer_block .= "queryrange=0:".$range."&tracks=[key:sequence_track][key:gene_model_track]&nodatacookie=true&id=".$sequence->accession_number."&v=0:1111&";
$viewer_block .= "data=Seq-annot ::%3D { desc { name %22Registered ".$genes[0]['gene_name']." probes%22	}, data ftable {";
	
$viewer_block .= $dataClonesString;
	
$viewer_block .= "} }\"></a></div>";

echo $display_block;
echo $viewer_block;


#echo "Test2";
$db = null;

#	header('Location: manage_probes.php');
	
#	echo "<a href=\"manage_probes.php\">Click to redirect</a>";

?>