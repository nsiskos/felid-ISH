<?php

# pass $_GET['lab_id']

session_start();
require_once("standards.php");

# this page requires user permission al least = 1
$page_permission = 1;

$out_block = "";
#$sequences_location = "../seqtest/sequences4STABLE_CORE.php?operator=sites";
$sequences_location = ABS_SEQ_LOCUS."index.php?operator=sites";
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

require_once("db_handle.php");
#require_once("embryos.uni.class.php");

	
	$sql = "select * from primer_lib where lab_id=?";
	$prepare = $db->prepare($sql);
#print_r($db->errorInfo());
	$prepare->bindValue(1, $_GET['lab_id'], PDO::PARAM_INT);
			
	$prepare->execute();
	
	$result = $prepare->fetch(PDO::FETCH_ASSOC);
	
//echo "<pre>";
//print_r($result);
//echo "</pre>";

	$out_block .= "<h3>Primer ".$result['lab_id']." ".$result['orientation']." for <em>".$result['intended_targene']."</em></h3>\n";
	$out_block .= "<p>Non-hybrid. tail: ".$result['tail']." (".strlen($result['tail'])." bp)<br>\n";
	$out_block .= "Primer sequence: ".$result['complementary']." (".strlen($result['complementary'])." bp)<br>\n";
	$out_block .= "Target organism: ".$result['organism']."<br />\n";
	$out_block .= "Design based on: <a href=\"https://www.ncbi.nlm.nih.gov/nuccore/".$result['ncbi_pattern']."\" target=\"_blank\">".$result['ncbi_pattern']."</a></p>\n";
	
	$range = explode("_", $result['from_to']);

	
	$out_block .= "<form method=\"POST\" action=\"".$sequences_location."\" target=\"_blank\">\n";
	$out_block .= "<p>Exact location: Nt. ".$range[0]."-".$range[1];
	$out_block .= "<input type=\"hidden\" name=\"seq\" value=\"".$result['ncbi_pattern']."\">\n";
	$out_block .= "<input type=\"hidden\" name=\"string_subject[]\" value=\"".$range[0]."-".$range[1]."\">\n";
	$out_block .= " &raquo; <input type=\"submit\" class=\"button\" value=\"see it\">\n</p>";
	$out_block .= "</form>\n";
	
#	$out_block .= "<br />\n";
	$out_block .= "<p>Template: ".$result['template']."<br />\n";
	$out_block .= "Tm (centigrade): ".$result['manufact_tm']." (manufacturer) - ".$result['amplifix_tm']." (amplifix)<br />\n";
	$out_block .= "Restriction site: ".$result['re_site']."<br />\n";
	$out_block .= "Comments: ".$result['comments'];
	
	$out_block .= "</p>\n";

	echo $out_block;

#	header('Location: manage_probes.php');
	
#	echo "<a href=\"manage_probes.php\">Click to redirect</a>";

?>