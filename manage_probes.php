<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', false);


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
	
	header('Location: login.php?opr=login&fm=manage_probes');
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

$pageTitle = "probes";
$display_block = "";
$head = "probe management";
$extras_in_header = "";
$probeSB = "";
$probe_id_form = "";
$age_block = "";
$img_block = "<br>";
$viewer_block = "";
$post_js = "";



	$sql = "select id, gene_name, solution_book, colour from gene order by gene_name";
#	$sql = "select id, gene_name, solution_book, colour from gene";
	$genes_h = $db->prepare($sql);
	$genes_h->execute();
	$genes = $genes_h->fetchAll(PDO::FETCH_ASSOC);
	
#echo "<pre>";
#print_r($genes);
#echo "</pre>";

	$step = 5;
	$how_many_genes = count($genes);
	$height = ceil($how_many_genes/$step);
	
	$display_block .= "<h3>click to view probe</h3>\n";
	if ($restricted_flag == "OFF") {
		$display_block .= "<p>or <a href=\"manage_probes.php?opr=new\">add a probe</a></p>\n";
	}
		
	$display_block .= "<table id=\"slidetable\">\n";
	$display_block .= "<tr>";
	
	for ($i=0;$i<($step*$height);++$i) {
		if ($i<$how_many_genes) {
			$gene_color = explode(".", $genes[$i]['colour']);
			$display_block .= "<td style=\"background-color:#".$gene_color[0].";\">";
			$display_block .= "<a style=\"text-decoration: none; color:#".$gene_color[1].";\" href=\"manage_probes.php?opr=".$genes[$i]['id']."\" >".$genes[$i]['gene_name']."<br />SB ".$genes[$i]['solution_book']."</a></td>";
			if ( (($i+1) % $step) == 0) {
				$display_block .= "</tr>\n<tr>";
			}
		} else {
			$display_block .= "<td></td>";
		}
	}
	
	$display_block .= "</tr>";
	$display_block .= "</table>";
	
if (!isset($_GET['opr'])) { # this shows genes
	
	$display_block .= "<p>select a probe from above</p>";	
} elseif ($_GET['opr'] == "comparison") {
	
	if (!isset($_GET['ncbi'])) {

		echo "No vald accession(.version) number provided!";
		die;
	}
	
	
	$dataclones = array();


	$sql = "select gene_name, solution_book, sequence from gene where NCBI_accession=?";
	$genes_h = $db->prepare($sql);
	$genes_h->bindValue(1, $_GET['ncbi'], PDO::PARAM_STR);
	$genes_h->execute();
	$genes = $genes_h->fetchAll(PDO::FETCH_ASSOC);
	
	
			
	include_once(REL_SEQ_LOCUS."sequ.class.php");
	$sequence = new sequence ($_GET['ncbi']);
	$probes = array();
	foreach ($genes as $gene) {
		$probes["sb_".$gene['solution_book']] = $sequence->findThis($gene['sequence'])[0];
	}
	
#	echo "<pre>";
#	print_r($probes);
#	echo "</pre>";
	
	$display_block .= "<h2>Comparison of <em>".$genes[0]['gene_name']."</em> probes</h2>\n";
	
	$display_block .= "<p>";
	$display_block .= "Accession number: ".$_GET['ncbi']."<br />\n";
	$display_block .="GI number: ".$sequence->GI_number."<br />\n";
	foreach ($probes as $key => $val) {
		$sol_book = explode("_", $key);
		$display_block .= "SB".$sol_book[1].": ".$val[0]." - ".$val[1]."<br />\n";

		$dataclones[] = "{ data clone { name %22SB".$sol_book[1]."%22, concordant TRUE, unique TRUE }, location mix { int { from ".$val[0].", to ".($val[0]+20).", strand plus, id gi ".$sequence->GI_number." },	int { from ".($val[1]-20).", to ".$val[1].", strand minus, id gi ".$sequence->GI_number." }	}, title %22Primer 1%22, exts {	{ type str %22DisplaySettings%22, data { { label str %22Weight%22, data int 1000 }}}}}";
		
/*		$dataclones[] = "
		{
			data clone { name %22SB".$sol_book[1]."%22, concordant TRUE, unique TRUE },
			location mix {
				int { from ".$val[0].", to ".($val[0]+20).", strand plus, id gi ".$sequence->GI_number." },
				int { from ".($val[1]-20).", to ".$val[1].", strand minus, id gi ".$sequence->GI_number." }
			},
			qual {
				{ qual %22type%22, val %22primer%22	},
				
				{ qual %22FW_TM%22, val %2260.15%22	},
				{ qual %22FW_GC%22, val %2263.16%22 },
				{ qual %22FW_SEQ%22, val %22CCACTCTGCGCCTCTCTTC%22 },
				
				{ qual %22RV_TM%22, 	val %2259.96%22 },
				{ qual %22RV_GC%22, val %2255.00%22 },
				{ qual %22RV_SEQ%22, val %22ACATGGGGAGGCTCTACCTT%22 }
			},
			title %22Primer 1%22,
			exts {
				{
					type str %22DisplaySettings%22,
					data {
						{ label str %22Weight%22, data int 1000 }
					}
				}
			}
		}
		
		";
*/

	}
	
	$dataClonesString = implode(", ", $dataclones);
	
	$display_block .= "</p>\n";	
		
	$viewer_block .= "<div id=\"mySeqViewer1\" class=\"SeqViewerApp\" data-autoload><a href=\"content=PrimerBlast&appname=CatProbe&embedded=true&appname=felissimus&label=2&";
	$range = $sequence->seqLength + 1500;
	$viewer_block .= "queryrange=0:".$range."&tracks=[key:sequence_track][key:gene_model_track]&nodatacookie=true&id=".$sequence->accession_number."&v=0:1111&";
	$viewer_block .= "data=Seq-annot ::%3D { desc { name %22Registered ".$genes[0]['gene_name']." probes%22	}, data ftable {";
	
	$viewer_block .= $dataClonesString;
	
	$viewer_block .= "} }\"></a></div>";
	
	$extras_in_header = "<script type=\"text/javascript\" src=\"https://www.ncbi.nlm.nih.gov/projects/sviewer/js/sviewer.js\"></script>";
	
	
} else { 
	
	$true_action = "update";
	$extra_attr = "required";
		
	$probe = array(
		'id' => "",
		'gene_name' => "",
		'NCBI_accession' => "",
		'solution_book' => "",
		'colour' => "",
		'sequence' => "",
		'comments' => "",
		'primers_used' => "",
		'organism' => ""
	);

	if (is_numeric($_GET['opr'])) {
		$probeSB = $_GET['opr'];
		$sb_attr = "";
		$sql = "select * from gene where id=?";
#		$sql = "select * from gene where solution_book=?";
		$probe_ret = $db->prepare($sql);
		$probe_ret->bindValue(1, $probeSB, PDO::PARAM_INT);
		$probe_ret->execute();
		$probe = $probe_ret->fetch(PDO::FETCH_ASSOC);
		
		$probe_id_form = "<input type=\"hidden\" name=\"probe_id\" value=\"".$_GET['opr']."\">";
		
		if (!isset($probe['solution_book']) || !is_numeric($probe['solution_book']) ) {
			$probe_ret = null;
			$db = null;
			echo "No valid results!";
			echo "<pre>";
			print_r($probe);
			echo "</pre>";
		} else {
			$display_block .= "<h2><em>".$probe['gene_name']."</em> probe</h2>";
			// add js snippet for calculating sequence details
			$post_js .= "<script src=\"seqSnippet.js\"></script>\n";
		}
		
			
	} elseif ($_GET['opr'] == "new") {
		if ($restricted_flag == "ON") {
			echo "Permission denied!<br />\n";
			die;
		}
		$true_action = "add_new";
		$extra_attr = "required";
		$sb_attr = $extra_attr;
		$display_block .= "<h2>Add probe</h2>";
	} else {
		echo "No valid solution book provided!<br />";
		die;
	}

	if (empty($probe['primers_used'])) {
		$primers = array('', '');
	} else {
		$primers = explode("_", $probe['primers_used']);
	}
	
	if (empty($probe['colour'])) {
		$colour = array('ffffff', '000000');
	} else {
		$colour = explode(".", $probe['colour']);
	}
	
	
	if ($restricted_flag == "OFF") {
		$display_block .= "<form action=\"oligo_rec.php?action=write_probe\" method=\"POST\">\n";
		$display_block .= "<input type=\"hidden\" name=\"true_action\" value=\"".$true_action."\">\n";
		$display_block .= $probe_id_form."\n";
		$display_block .= "<p>Relevant gene: ";
		$display_block .= "<input type=\"text\" name=\"gene_name\" size=\"10\" value=\"".$probe['gene_name']."\" ".$extra_attr.">\n</p>";
		$display_block .= "<p>Design based on: ";
		$display_block .= "<input type=\"text\" name=\"ncbi\" size=\"20\" value=\"".$probe['NCBI_accession']."\" ".$extra_attr.">";
		$display_block .= " <a href=\"manage_probes.php?opr=comparison&ncbi=".$probe['NCBI_accession']."\">[view map]</a>";		
		$display_block .= " <a href=\"https://www.ncbi.nlm.nih.gov/nuccore/".$probe['NCBI_accession']."\" target=\"_blank\">[show on ncbi]</a></p>";
		$display_block .= "<p>Solution book: ";
		$display_block .= "<input type=\"text\" name=\"solution_book\" size=\"10\" value=\"".$probe['solution_book']."\" ".$sb_attr.">\n</p>";
		$display_block .= "<p>Relevant primers: ";
		$display_block .= "<input type=\"text\" name=\"fwd_primer\" size=\"4\" value=\"".$primers[0]."\"> - ";
		$display_block .= "<input type=\"text\" name=\"rev_primer\" size=\"4\" value=\"".$primers[1]."\">\n</p>";
		$display_block .= "<p>Target orgnism: ";
		$display_block .= "<input type=\"text\" name=\"organism\" size=\"10\" value=\"".$probe['organism']."\" ".$extra_attr.">\n</p>\n";
		$display_block .= "<p>Font colour: ";
		$display_block .= "<input type=\"text\" name=\"font_colour\" size=\"6\" maxlength=\"6\" value=\"".$colour[1]."\"> BG colour: ";
		$display_block .= "<input type=\"text\" name=\"back_colour\" size=\"6\" maxlength=\"6\" value=\"".$colour[0]."\">\n</p>";
	} else {
		$display_block .= "<p>Design based on: ".$probe['NCBI_accession'];
		$display_block .= " <a href=\"manage_probes.php?opr=comparison&ncbi=".$probe['NCBI_accession']."\">[view map]</a>";
		$display_block .= " <a href=\"https://www.ncbi.nlm.nih.gov/nuccore/".$probe['NCBI_accession']."\" target=\"_blank\">[show on ncbi]</a></p>";
		$display_block .= "<p>Solution book: ";
		$display_block .= $probe['solution_book']."</p>\n";
		$display_block .= "<p>Relevant primers: ";
		$display_block .= $primers[0]." - ".$primers[1]."</p>\n";
		$display_block .= "<p>Target orgnism: ".$probe['organism']."</p>\n";
	}
#	$display_block .= "<fieldset>\n<legend>probe information</legend>\n";
	
	$display_block .= "<p>Comments:<br />";

	$display_block .= "<textarea name=\"comments\">".$probe['comments']."</textarea>\n";
	
	
	
	$display_block .= "<p>Sequence: (".strlen($probe['sequence'])." bp)<br />";

	$display_block .= "<textarea id=\"sequence\" name=\"sequence\" ".$extra_attr.">".$probe['sequence']."</textarea>\n";
	
	$display_block .= "<div id=\"seqDetails\"></div>";
	

	
	if ($restricted_flag == "OFF") {
		$display_block .= "<p><input type=\"submit\" value=\"record\" class=\"button\">";
		$display_block .= "</form>";
	}
#	$display_block .= "<a href=\"manage_primers.php\">Go back!</a></p>";
	

	
	
	
#	echo "<pre>";
#	print_r($probe);
#	echo "</pre>";
	
	
	
	
}

$db = null;

?>
<!DOCTYPE html>
<html>
<head>
<link href="styles/genes.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/x-icon" sizes="16x16" href="favicon.ico"/>
<link rel="shortcut icon" type="image/png" sizes="32x32" href="favicon32.png"/>
<link rel="apple-touch-icon" type="image/png" sizes="152x152" href="favicon152.png"/>
<?php echo $extras_in_header; ?>
<!--script type="text/javascript" src="embryonic.js"></script-->
<title>
	<?php echo $pageTitle; ?>
</title>
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
			<div id="text"><?php 

				echo $display_block;

				echo $viewer_block; 

				?>
			</div>

			<div id="footer"><?php echo $footer; ?></div>

		</div>
	</div>
	<?php echo $post_js; ?>
</body></html>