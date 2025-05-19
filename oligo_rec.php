<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', false);

session_start();
require_once("standards.php");
$head="";
$display_block = "";
#$img_block = "<br>";

# this page requires user permission at least = 2
$page_permission = 2;

$now = time();
if (isset($_SESSION['time'])) {
	$difference = $now - $_SESSION['time'];
}

// perform the login check. $temporal_allownce is found inside standards.php
if ( !isset($_SESSION['time']) or ($difference > $temporal_allowance) or !isset($_SESSION['user_data']) ) { 
	
	// go to login page
	if (isset($_GET['lab_id'])) {
		header('Location: login.php?opr=login&fm=oligo_rec&lab_id='.$_GET['lab_id']);
	} else {
		header('Location: login.php?opr=login&fm=manage_primers');
	}
	die;
}

# this page requires user permission at least = 2
if ( ($_SESSION['user_data']['perm'] < $page_permission) or (!isset($_SESSION['user_data']['perm'])) ) {
	echo "You do not have sufficient priviledges to access this page!</br>";
	echo "<a href=\"frontpage.php\">Click here</a>";
	die;
}

header('Content-type: text/html; charset=utf-8');
require_once("db_handle.php");

	$primer = array(
		'lab_id' => "",
		'tail' => "",
		'complementary' => "",
		're_site' => "",
		'orientation' => "",
		'ncbi_pattern' => "",
		'intended_targene' => "",
		'from_to' => "",
		'template' => "",
		'organism' => "",
		'commments' => "",
		'manufact_tm' => "",
		'amplifix_tm' => ""
	);

$true_action = "add_new";
$extra_attr = "required";

if ($_GET['action'] == "form") { // load the primer edit form
	
	if (!(isset($_GET['lab_id']))) {
		
		$head .= "New primer";
		
#		echo "No/false primer id loaded. Exit.";
#		die;
	} else {

		
		$sql = "select * from primer_lib where lab_id=?";
		$primers_ret = $db->prepare($sql);
		$primers_ret->bindValue(1, $_GET['lab_id'], PDO::PARAM_INT);
		$primers_ret->execute();
		$primers = $primers_ret->fetch(PDO::FETCH_ASSOC);
		
		if (isset($primers)) {
			$primer = $primers;
			$head .= "Edit primer: ".$primer['lab_id'];
			$true_action = "update";
			$extra_attr = "readonly";
			
		} else {
			echo "select stm returned nothing";
			die;
		}
		
		unset($sql);
		unset($primers_ret);
		unset($primers);

#echo "<pre>";
#print_r($primer);
#echo "</pre>";
	}
	
	
	$display_block .= "<form method=\"POST\" action=\"oligo_rec.php?action=write_oligo\">\n";
	$display_block .= "<input type=\"hidden\" name=\"true_action\" value=\"".$true_action."\">";
	$display_block .= "<fieldset>\n<legend>primer information</legend>\n";
	$display_block .= "<p>Laboratory id:\n";
	$display_block .= "<input type=\"text\" name=\"lab_id\" size=\"4\" value=\"".$primer['lab_id']."\" ".$extra_attr.">\n</p>";
	
	$display_block .= "<p>Primer:\n";
	$display_block .= "5'-<input type=\"text\" name=\"tail\" size=\"15\" maxlength=\"15\" placeholder=\"tail\" value=\"".$primer['tail']."\">-";
	$display_block .= "<input type=\"text\" name=\"complementary\" size=\"50\" maxlength=\"50\" value=\"".$primer['complementary']."\" placeholder=\"complementary\" required>-3'</p>";

	$display_block .= "<p>Tm (°C):\n";
	$display_block .= "Manufacturer: <input type=\"text\" name=\"manufact_tm\" size=\"5\" maxlength=\"5\" value=\"".$primer['manufact_tm']."\">\n";
	$display_block .= "Amplifix: <input type=\"text\" name=\"amplifix_tm\" size=\"5\" maxlength=\"5\" value=\"".$primer['amplifix_tm']."\"></p>";

	$display_block .= "<p>Orientation:\n";
	
	if ($true_action == "update") {
		if ($primer['orientation'] == "Fwd") {
			$display_block .= "<input type=\"radio\" name=\"orient\" value=\"Fwd\" checked=\"checked\">forward\n";
			$display_block .= "<input type=\"radio\" name=\"orient\" value=\"Rev\">reverse\n</p>";
		} else {
			$display_block .= "<input type=\"radio\" name=\"orient\" value=\"Fwd\">forward\n";
			$display_block .= "<input type=\"radio\" name=\"orient\" value=\"Rev\" checked=\"checked\">reverse\n</p>";
		}
	} else {
		$display_block .= "<input type=\"radio\" name=\"orient\" value=\"Fwd\" checked=\"checked\">forward\n";
		$display_block .= "<input type=\"radio\" name=\"orient\" value=\"Rev\">reverse\n</p>";
	}

	$display_block .= "<p>Restriction enzyme site:\n";
	$display_block .= "<input type=\"text\" name=\"re_site\" size=\"10\" maxlength=\"10\" value=\"".$primer['re_site']."\">&nbsp;\n</p>";
	
	$display_block .= "<p>Intended target:\n";
	$display_block .= "<input type=\"text\" name=\"intended_targene\" size=\"30\" maxlength=\"30\" value=\"".$primer['intended_targene']."\" required>&nbsp;\n</p>";
	
	$display_block .= "<p>NCBI accession.version design N.:\n";
	$display_block .= "<input type=\"text\" name=\"ncbi\" size=\"30\" maxlength=\"30\" value=\"".$primer['ncbi_pattern']."\">&nbsp;\n</p>";
	
	$range = array();
	if ($true_action == "update") {
		$range = explode("_", $primer['from_to']);
	} else {
		$range[0] = "";
		$range[1] = "";
	}
	
	$display_block .= "<p>Location on NCBI pattern:\n";
	$display_block .= "<input type=\"text\" name=\"from_to[]\" size=\"5\" maxlength=\"5\" value=\"".$range[0]."\" placeholder=\"from\">-";
	$display_block .= "<input type=\"text\" name=\"from_to[]\" size=\"5\" maxlength=\"5\" value=\"".$range[1]."\" placeholder=\"to\"></p>";
	
	$display_block .= "<p>Binds on:\n";
	
	$gDNA_check = "";
	$cDNA_check = "";
	$both_check = "";
	
	if ($true_action == "update") {
		if ($primer['template'] == "gDNA") {
			$gDNA_check = "checked=\"checked\"";
		} elseif ($primer['template'] == "cDNA") {
			$cDNA_check = "checked=\"checked\"";
		} elseif ($primer['template'] == "both") {
			$cDNA_check = "checked=\"checked\"";
		} else {
			echo "problem = ".$primer_template;
			die;
		}
	}

	$display_block .= "<input type=\"radio\" name=\"template\" value=\"cDNA\" ".$cDNA_check.">cDNA\n";
	$display_block .= "<input type=\"radio\" name=\"template\" value=\"gDNA\" ".$gDNA_check.">gDNA\n";
	$display_block .= "<input type=\"radio\" name=\"template\" value=\"both\" ".$both_check.">both\n</p>";
	
	$display_block .= "<p>Target organism:\n";
	$display_block .= "<input type=\"text\" name=\"organism\" size=\"30\" maxlength=\"30\" value=\"".$primer['organism']."\">&nbsp;\n</p>";
	
	if ($true_action == "update") {
		$comment = $primer['comments'];
	} else {
		$comment = "";
	}
	
	$display_block .= "<p>Are there any comments?<br>";
	$display_block .= "<textarea name=\"comments\">".$comment."</textarea></p>\n";
	$display_block .= "</fieldset>\n";
	
	
	$display_block .= "<p><input type=\"submit\" value=\"record\" class=\"button\">&nbsp;";
	$display_block .= "<a href=\"manage_primers.php\">Go back!</a></p>";
	$display_block .= "</form>";

} elseif ($_GET['action'] == "write_oligo") {

//echo "<pre>";
//print_r($_POST);
//echo "</pre>";
	
	if ($_POST['true_action'] == "update") {
		
		$sql = "update primer_lib set tail=?, complementary=?, re_site=?, orientation=?, ncbi_pattern=?, intended_targene=?, from_to=?, template=?, organism=?, comments=?, manufact_tm=?, amplifix_tm=? where lab_id=?";
		$prep = $db->prepare($sql);
		$prep->bindValue(1, $_POST['tail'], PDO::PARAM_STR);
		$prep->bindValue(2, $_POST['complementary'], PDO::PARAM_STR);
		$prep->bindValue(3, $_POST['re_site'], PDO::PARAM_STR);
		$prep->bindValue(4, $_POST['orient'], PDO::PARAM_STR);
		$prep->bindValue(5, $_POST['ncbi'], PDO::PARAM_STR);
		$prep->bindValue(6, $_POST['intended_targene'], PDO::PARAM_STR);
		$prep->bindValue(7, $_POST['from_to'][0]."_".$_POST['from_to'][1], PDO::PARAM_STR);
		$prep->bindValue(8, $_POST['template'], PDO::PARAM_STR);
		$prep->bindValue(9, $_POST['organism'], PDO::PARAM_STR);
		$prep->bindValue(10, $_POST['comments'], PDO::PARAM_STR);
		$prep->bindValue(11, $_POST['manufact_tm'], PDO::PARAM_STR);
		$prep->bindValue(12, $_POST['amplifix_tm'], PDO::PARAM_STR);
		$prep->bindValue(13, $_POST['lab_id'], PDO::PARAM_STR);
		
	} elseif ($_POST['true_action'] == "add_new") {
		
		$sql = "insert into primer_lib (lab_id, tail, complementary, re_site, orientation, ncbi_pattern, intended_targene, from_to, template, organism, comments, manufact_tm, amplifix_tm) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$prep = $db->prepare($sql);
		$prep->bindValue(1, $_POST['lab_id'], PDO::PARAM_STR);
		$prep->bindValue(2, $_POST['tail'], PDO::PARAM_STR);
		$prep->bindValue(3, $_POST['complementary'], PDO::PARAM_STR);
		$prep->bindValue(4, $_POST['re_site'], PDO::PARAM_STR);
		$prep->bindValue(5, $_POST['orient'], PDO::PARAM_STR);
		$prep->bindValue(6, $_POST['ncbi'], PDO::PARAM_STR);
		$prep->bindValue(7, $_POST['intended_targene'], PDO::PARAM_STR);
		$prep->bindValue(8, $_POST['from_to'][0]."_".$_POST['from_to'][1], PDO::PARAM_STR);
		$prep->bindValue(9, $_POST['template'], PDO::PARAM_STR);
		$prep->bindValue(10, $_POST['organism'], PDO::PARAM_STR);
		$prep->bindValue(11, $_POST['comments'], PDO::PARAM_STR);
		$prep->bindValue(12, $_POST['manufact_tm'], PDO::PARAM_STR);
		$prep->bindValue(13, $_POST['amplifix_tm'], PDO::PARAM_STR);

		
	} else {
		echo "PRIMER: TI NA KANW? ME MPERDEPSES!!!";
	}
	
	$prep->execute();
	$db = null;	
	header('Location: oligo_rec.php?action=form&lab_id='.$_POST['lab_id']);
	
	echo "<br /><a href=\"oligo_rec.php?action=form&lab_id=".$_POST['lab_id']."\">Click to redirect</a><br />\n";

} elseif ($_GET['action'] == "write_probe") {
	
#	echo "<pre>";
#	print_r($_POST);
#	echo "</pre>";
	
	$colour = $_POST['back_colour'].".".$_POST['font_colour'];
	$primers = $_POST['fwd_primer']."_".$_POST['rev_primer'];
	$sequence = preg_replace('/\s+/', '', trim($_POST['sequence']));
	
	
	if ($_POST['true_action'] == "update") {
		
		$sql = "update gene set gene_name=?, NCBI_accession=?, solution_book=?, colour=?, sequence=?, comments=?, primers_used=?, organism=? where id=?";
		$prep = $db->prepare($sql);
		
		$prep->bindValue(1, $_POST['gene_name'], PDO::PARAM_STR);
		$prep->bindValue(2, trim($_POST['ncbi']), PDO::PARAM_STR);
		$prep->bindValue(3, $_POST['solution_book'], PDO::PARAM_INT);
		$prep->bindValue(4, $colour, PDO::PARAM_STR);
		$prep->bindValue(5, $sequence, PDO::PARAM_STR);
		$prep->bindValue(6, $_POST['comments'], PDO::PARAM_STR);
		$prep->bindValue(7, $primers, PDO::PARAM_STR);
		$prep->bindValue(8, $_POST['organism'], PDO::PARAM_STR);
		$prep->bindValue(9, $_POST['probe_id'], PDO::PARAM_INT);
		$prep->execute();
		
		$id = $_POST['probe_id'];
		
	} elseif ($_POST['true_action'] == "add_new") {
		
		$sql = "insert into gene (gene_name, NCBI_accession, solution_book, colour, sequence, comments, primers_used, organism) values (?, ?, ?, ?, ?, ?, ?, ?)";
		$prep = $db->prepare($sql);
		
		$prep->bindValue(1, $_POST['gene_name'], PDO::PARAM_STR);
		$prep->bindValue(2, trim($_POST['ncbi']), PDO::PARAM_STR);
		$prep->bindValue(3, $_POST['solution_book'], PDO::PARAM_INT);
		$prep->bindValue(4, $colour, PDO::PARAM_STR);
		$prep->bindValue(5, $sequence, PDO::PARAM_STR);
		$prep->bindValue(6, $_POST['comments'], PDO::PARAM_STR);
		$prep->bindValue(7, $primers, PDO::PARAM_STR);
		$prep->bindValue(8, $_POST['organism'], PDO::PARAM_STR);
		$prep->execute();
		
		$id = 	$db->lastInsertId();
		
	} else {
		echo "PROBE: TI NA KANW? ME MPERDEPSES!!!";
	}
 	
	
	$db = null;	
	header('Location: manage_probes.php?opr='.$id);
	
	echo "<br /><a href=\"manage_probes.php?opr=".$id."\">Click to redirect</a><br />\n";
	
} else {
	echo "Bad input";
	die;
}

$db = null;

?>
<!DOCTYPE html>
<html>
<head>
<link href="styles/embryonicB.css" rel="stylesheet" type="text/css">
<link href="styles/recordareADD.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/x-icon" sizes="16x16" href="favicon.ico"/>
<link rel="shortcut icon" type="image/png" sizes="32x32" href="favicon32.png"/>
<link rel="apple-touch-icon" type="image/png" sizes="152x152" href="favicon152.png"/>
<title>oligo rec</title>
</head>

<body>
	<div class="container">
		<div class="into">

			<div class="bind"></div>

			<div class="facultyid"><h2><?php echo $head; ?></h2></div>
			<div class="bind"></div>
			<div class="text"><?php echo $display_block; ?></div>
			<div class="footer"><?php echo $footer; ?></div>

		</div>
	</div>
</body>
</html>