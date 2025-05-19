<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', true);


session_start();
require_once "standards.php";
# this page requires user permission al least = 1
$page_permission = 1;


$now = time();
if (isset($_SESSION['time'])) {
	$difference = $now - $_SESSION['time'];
}


// perform the login check. $temporal_allownce is found inside standards.php
if ( !isset($_SESSION['time']) or ($difference > $temporal_allowance) or !isset($_SESSION['user_data']) ) { 

	//go to login page
	
	header('Location: login.php?opr=login&fm=manage_primers');
	die;
}

# this page requires user permission al least = 2

if ( ($_SESSION['user_data']['perm'] < $page_permission) or (!isset($_SESSION['user_data']['perm'])) ) {
	echo "You do not have sufficient priviledges to access this page!</br>";
	echo "<a href=\"https://en.wikipedia.org/wiki/Blue_whale\">click here to learn about an animal</a>";
	die;
} else {
	$restricted_flag = ($_SESSION['user_data']['perm'] == 2) ? "OFF" : "ON" ;
	header('Content-type: text/html; charset=utf-8');
}

# now that we have a vaildated user load the database!
require_once("db_handle.php");
#require_once("embryos.uni.class.php");

$display_block = "";
$head = "primer management";
$extras_in_header = "";
$age_block = "";
$img_block = "<br>";
$pageTitle = "primer library";
$post_js = "";
$extra_css = "";


define("BULK_LIB", "<a href=\"manage_primers.php?opr=bulk_lib\">view the entire library</a>");

if (!isset($_GET['opr'])) 
{
	
	$display_block .= "<h3>click to view primers:</h3>\n";
	if ($restricted_flag == "OFF") {
		$display_block .= "<p><a href=\"oligo_rec.php?action=form\">add a primer</a> or <br />".BULK_LIB."</p>\n";
#		$display_block .= "<a href=\"\">view the entire library</a></p>";
	}
	
	
	require_once("primer.class.php");
	
	$table = new geneTable($db);
	
	$display_block .= $table->table;
	
	$display_block .= "<p>select a gene from above</p>";

} 
elseif ($_GET['opr'] == "pairs") 
{

#echo "<pre>";
#print_r($_POST);
#echo "</pre>";
	
	$pageTitle = $_POST['gene_name']." primer map";
	
	$display_block .= "<h3><em>".$_POST['gene_name']."<em> primer map</h3>\n";
	$display_block .= "<p><a href=\"manage_primers.php?opr=".$_POST['gene_name']."\">return</a></p>\n";
	
	$pairs = array();
	$viewer_block = "";
	
	foreach ($_POST['pairs'] as $pair) {
		$from_to = explode("_", $pair);
		$pairs[] = array($from_to[0], $from_to[1]);
	}
	
	
	include_once(REL_SEQ_LOCUS."sequ.class.php");
	include_once("primer.class.php");
	
	$sequence = new primer_pairs($_POST['ncbi']);
	
	foreach ($pairs as $indi_pair) {
		
		$forward = new primer($db, $indi_pair[0]);
		$reverse = new primer($db, $indi_pair[1]);

#		echo "<pre>";
#		var_dump($forward);
#		var_dump($reverse);
#		echo "</pre>";
		
		$sequence->loadPair($forward->primer_id, $forward->primer_info['complementary'], $forward->primer_info['amplifix_tm'], $forward->primer_info['manufact_tm'],
							$reverse->primer_id, $reverse->primer_info['complementary'], $reverse->primer_info['amplifix_tm'], $reverse->primer_info['manufact_tm']);
		
		
	}
	
	$dataClonesString = $sequence->assembleDataClones();
	
	
	$viewer_block .= "<br /><div id=\"mySeqViewer1\" class=\"SeqViewerApp\" data-autoload><a href=\"content=PrimerBlast&appname=CatProbe&embedded=true&appname=felissimus&label=2&";
	$range = $sequence->seqLength + 100;
	$viewer_block .= "queryrange=0:".$range."&tracks=[key:sequence_track][key:gene_model_track]&nodatacookie=true&id=".$sequence->accession_number."&v=0:1111&";
	$viewer_block .= "data=Seq-annot ::%3D { desc { name %22Primers%22	}, data ftable {";
	
	$viewer_block .= $dataClonesString;
	
	$viewer_block .= "} }\"></a></div>";
	
	$display_block .= $viewer_block;
	
	$extras_in_header = "<script type=\"text/javascript\" src=\"https://www.ncbi.nlm.nih.gov/projects/sviewer/js/sviewer.js\"></script>";

#echo "<pre>";
#print_r($dataClonesString);
#echo "</pre>";

} 
elseif ($_GET['opr'] == "bulk_lib")
{
	$sql = "select * from primer_lib";
	$res = $db->prepare($sql);
	$res->execute();
	
	$primers = $res->fetchAll(PDO::FETCH_ASSOC);
	
	$table = "";

		
	$table .= "<table id=\"bulkLib\" class=\"primerLib\">";
	$table .= "<thead>";
	$table .= "<tr>";
	
	$keys = array_keys($primers[0]);

	foreach ($keys as $index=>$key) {
		$table .= "<th onclick=\"sortTable(event, primer_lib, '".$key."')\">".$key."</th>";
#		$table .= "<th onclick=\"sortTable(".$index.", 'bulkLib')\">".$key."</th>";
	}
	
	$table .="</tr>\n";
	$table .= "</thead>";
	$table .= "<tbody id=\"primerInfo\">";
	foreach ($primers as $primer) {
		
		$table .= "<tr>";
		
		foreach ($keys as $key) {
			$table .= "<td>";
			$table .= $primer[$key];
			$table .= "</td>";
		}
		$table .= "</tr>\n";
	}
	$table .= "</tbody>";
	$table .= "</table>";
	
	
	$extra_css .= "<link href=\"styles/primerLib.css\" rel=\"stylesheet\" type=\"text/css\">";
	$extras_in_header .= "<script src=\"https://d3js.org/d3.v5.min.js\"></script>\n";
	$extras_in_header .= "<script type=\"text/javascript\">var primer_lib = ".json_encode($primers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)."; </script>";
	
	$display_block .= "<div id=\"primer_lib\">";
	$display_block .= $table;
	$display_block .="</div>";
	
	$post_js .= "<script type=\"text/javascript\" src=\"sorter.js\"></script>\n";
	
#	echo "<pre>";
#	print_r($primers);
#	echo "</pre>";
}
else // now opr equals the gene name!
{
	$pageTitle = $_GET['opr']." primers";
	
	$display_block .= "<h3>click to view primers:</h3>\n";
	if ($restricted_flag == "OFF") {
		$display_block .= "<p><a href=\"oligo_rec.php?action=form\">add a primer</a> or <br />".BULK_LIB."</p>\n";
#		$display_block .= "<a href=\"\">view the entire library</a></p>";
	}
	
	
	require_once("primer.class.php");
	
	$table = new geneTable($db);
	
	$display_block .= $table->table;

	$sql = "select lab_id, tail || complementary as sequence, orientation, from_to, ncbi_pattern from primer_lib where intended_targene=?";
	$primers_ret = $db->prepare($sql);
	$primers_ret->bindValue(1, $_GET['opr'], PDO::PARAM_STR);
	$primers_ret->execute();
	$primers = $primers_ret->fetchAll(PDO::FETCH_ASSOC);

	$ncbi = $primers[0]['ncbi_pattern'];
	
	$fwd_primers = array();
	$rev_primers = array();

	foreach ($primers as $primer) {
		if ($primer['orientation'] == "Fwd") {
			$fwd_primers[] = $primer;
		} elseif ($primer['orientation'] == "Rev") {
			$rev_primers[] = $primer;
		} else {
			echo $primer['orientation'];
		}
	}
	
	unset ($primers);
	
	// calculate the bigger array, or where do I have more primers? In rev or in fwd?
	$bigger = ( count($fwd_primers) > count($rev_primers) ) ? count($fwd_primers) : count($rev_primers);
	
#echo "<pre>";
#print_r($fwd_primers);
#echo "</pre>";

	$possible_pairs = array();
	
	$display_block .= "<h2><em>".$_GET['opr']."</em> primers</h2>";
	
	$display_block .= "<div id=\"triple_rock\">";
	
	$display_block .= "<div id=\"triple_rock_left\">\n<p>Forwdard primers:</p>";
	$display_block .= "<table class=\"primers_small\">\n";

	foreach ($fwd_primers as $fwd) {
		
		$fwd_limits = explode("_", $fwd['from_to']);

		
		foreach ($rev_primers as $indi_rev) {
			$rev_limits = explode("_", $indi_rev['from_to']);
			if ($fwd_limits[0] < $rev_limits[0]) {
				$possible_pairs[] = $fwd['lab_id']."_".$indi_rev['lab_id'];
			}
		}
		
		$display_block .= "<tr><td class=\"primer_id\">";

		
		if ($restricted_flag == "OFF") {
			$display_block .= "<a href=\"oligo_rec.php?action=form&lab_id=".$fwd['lab_id']."\">".$fwd['lab_id']."</a>";

		} elseif ($restricted_flag == "ON") {
			$display_block .= $fwd['lab_id'];

		} else {
			echo "Bad restr flag!";
		}
		
		$display_block .= "</td>";
		$display_block .= "<td><a href=\"javascript:void(0);\" onClick=\"loadPrimer('".$fwd['lab_id']."')\">(show info)</a></td></tr>\n";	
	}
	$display_block .= "</table>";
	
	$display_block .= "</div>";
	
	$display_block .= "<div id=\"triple_rock_middle\">";
	$display_block .= "<p style=\"text-align:center; color:#a0a0a0;\">click to view primer info</p>";
	$display_block .= "</div>";
	
	$display_block .= "<div id=\"triple_rock_right\">\n<p>Reverse primers:</p>\n";
	$display_block .= "<table class=\"primers_small\">\n";

	foreach ($rev_primers as $rev) {
		
		$display_block .= "<tr><td class=\"primer_id\">";

		
		if ($restricted_flag == "OFF") {
			$display_block .= "<a href=\"oligo_rec.php?action=form&lab_id=".$rev['lab_id']."\">".$rev['lab_id']."</a>";

		} elseif ($restricted_flag == "ON") {
			$display_block .= $rev['lab_id'];

		} else {
			echo "Bad restr flag!";
		}
		
		$display_block .= "</td>";
		$display_block .= "<td><a href=\"javascript:void(0);\" onClick=\"loadPrimer('".$rev['lab_id']."')\">(show info)</a></td></tr>\n";
		

	}
	$display_block .= "</table>\n";
	$display_block .= "</div>\n";
	
	$display_block .= "</div>\n"; # triple rock ends
	
	$display_block .= "<div id=\"set_up_pairs\">";
	$display_block .= "<h3>Select pairs to view:</h3>";

	// CALCULUS !!!!!
	
	$display_block .= "<div id=\"primer_pairs\">\n";

	$display_block .= "<form method=\"POST\" action=\"manage_primers.php?opr=pairs\">";
	$display_block .= "<input type=\"hidden\" name=\"ncbi\" value=\"".$ncbi."\">\n";
	$display_block .= "<input type=\"hidden\" name=\"gene_name\" value=\"".$_GET['opr']."\">\n";
	$index = 1;
	
	foreach ($possible_pairs as $pair) {
		$pair_expl = explode("_", $pair);
		$display_block .= "<input type=\"checkbox\" class=\"primer_pairs\" name=\"pairs[]\" value=\"".$pair."\">";
		
		$display_block .= "<a href=\"javascript:void(0);\" onclick=\"loadPrimerPair('".$pair."')\">";
		$display_block .= $pair_expl[0]." - ".$pair_expl[1];
		$display_block .= "</a>";
		
		if (($index % 2) == 0 ) {
			$display_block .= "<br />";
		} else {
			$display_block .= "   ";
		}
		
		++$index;
	}
	
	
	$display_block .= "<br />";
	$display_block .= "<input type=\"submit\" class=\"button\" value=\"view map\"> \n";

	$display_block .= "</div>\n";
	
	$display_block .= "<div id=\"primer_pair_info\"></div>\n";
	
#	echo "<pre>";
#	print_r($possible_pairs);
#	echo "</pre>";
	
	$display_block .= "</div>";
	

}

$db = null;

?>
<!DOCTYPE html>
<html>
<head>

<link href="styles/genes.css" rel="stylesheet" type="text/css">

<?php echo $extra_css; ?>
<link rel="shortcut icon" type="image/x-icon" sizes="16x16" href="favicon.ico"/>
<link rel="shortcut icon" type="image/png" sizes="32x32" href="favicon32.png"/>
<link rel="apple-touch-icon" type="image/png" sizes="152x152" href="favicon152.png"/>
<?php echo $extras_in_header; ?>
<script type="text/javascript" src="embryonic.js"></script>
<title><?php echo $pageTitle; ?></title>
</head>

<body>
	<div id="container">
		<div id="into">
			<div class="bind"></div>

			<div id="facultyid"><h2><?php echo $head; ?></h2></div>
			<div class="bind"></div>
			<div id="menu"><?php echo $navigation_menu; ?></div>
			<?php if (isset($kleines_menu)) { echo $kleines_menu; } ?>
			<div class="bind"></div>
			<div id="loginData">
				Signed in as <?php echo $_SESSION['user_data']['legal_name']; ?><br />
				<a href="login.php?opr=logout">Log out</a>
			</div>
			<div class="bind"></div>
			<div id="text"><?php echo $display_block; ?></div>

			<div id="footer"><?php echo $footer; ?></div>

		</div>
	</div>
	<?php echo $post_js; ?>
</body>
</html>