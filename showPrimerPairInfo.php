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

$primerpairPattern = '/(\d+)_(\d+)/';
$matches = array();
$match = preg_match($primerpairPattern, $_GET['pair_ids'], $matches);

if (
	!isset($_GET['pair_ids']) ||
	!$match
	)
{
	echo "<pre>";
	echo "Bad input. pair: ".print_r($_GET);
	echo "</pre>";
	die;
}

#echo "Fwd is ".$matches[1]."<br />";
#echo "Rev is ".$matches[2];

require_once("db_handle.php");
require_once("primer.class.php");
require_once(REL_SEQ_LOCUS."sequ.class.php");

$primer_a = new primer($db, $matches[1]);
$primer_b = new primer($db, $matches[2]);

$pair = new primer_pair_product();
$pair->setPrimer($primer_a->primer_info['from'], $primer_a->primer_info['to'], $primer_a->primer_info['tail'], $primer_a->primer_info['ncbi']);
$pair->setPrimer($primer_b->primer_info['from'], $primer_b->primer_info['to'], $primer_b->primer_info['tail'], $primer_b->primer_info['ncbi']);



if ($pair->ncbi == FALSE) {
	echo "Ncbi does not match.";
	die;
}

$out_block .= "<h4>Pair: ".$primer_a->primer_id." - ".$primer_b->primer_id."</h4>\n";

$out_block .= "<p>Hybridizes: ".$pair->getBasic()." bp";

$seq2 = new sequence($pair->ncbi, "PHP_GET");

$lim = $pair->interval();

$pair->setSeq($seq2->getChunk($lim[0], $lim[1]));

$subseq = $pair->sequence;
$out_block .= "<br />actual length: ".strlen($subseq)." (bp)</p>";
$out_block .= "<textarea name=\"sequence\">".$subseq."</textarea>";

#$out_block .= "Sequence: ".$pair->getSeq();;
#$pair->setInterval();


#echo "<pre>";
#print_r($pair->interval());
#echo "</pre>";

	echo $out_block;

#	header('Location: manage_probes.php');
	
#	echo "<a href=\"manage_probes.php\">Click to redirect</a>";

?>