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
	
	header('Location: login.php?opr=login&fm=front');
	die;
}

# this page requires user permission al least = 1

if ( ($_SESSION['user_data']['perm'] < $page_permission) or (!isset($_SESSION['user_data']['perm'])) ) {
	echo "You do not have sufficient priviledges to access this page!</br>";
	echo "<a href=\"https://en.wikipedia.org/wiki/Blue_whale\">click here to learn about an animal</a>";
	die;
} else {
	$restricted_flag = ($_SESSION['user_data']['perm'] >= 2) ? "OFF" : "ON" ;
	header('Content-type: text/html; charset=utf-8');
}

# now that we have a vaildated user load the database!
require_once("db_handle.php");
require_once("embryos.uni.class.php");

$d3_is_loaded = 0;

$pageTitle = "Fs";
$head = "";
$display_block = "";
$out_block = "";
$extras_in_header = "";
$extra_css = "";
$age_block = "";
$img_block = "";
$graphTitle = "";
$post_js = "";
$graphDiv = "";

if (!isset($_GET['opr']))  # this shows all emryos grouped by age
{
	$pageTitle = "all embryos";
	$head = "ages";
	
	$extra_css = "<link href=\"styles/emptyFrontpage.css\" rel=\"stylesheet\" type=\"text/css\">";
	
	$total_embryo_no = 0;

	// better version of second query
	$sql = "select age, 
		(select count(id) from embryo where part!='body' and set_height>0 and age=EMBL.age) as cut_no, 
		(select group_concat(name) from embryo where part!='body' and set_height>0 and age=EMBL.age) as cut_names, 
		(select count(id) from embryo where part!='body' and set_height is null and age=EMBL.age) as uncut_no, 
		(select group_concat(name) from embryo where part!='body' and set_height is null and age=EMBL.age) as uncut_names 
		from embryo EMBL group by age";
		
	$ret = $db->query($sql);
	$outer = array();
	$outer = $ret->fetchAll(PDO::FETCH_ASSOC);
	
	
	foreach ($outer as $row) {
		
		$total_embryo_no += $row['cut_no']+$row['uncut_no'];
		

		$out_block .= "<div class=\"age_group_container\">";
		
		$out_block .= "<div class=\"age_group_left\">";
		$out_block .= "<a class=\"head_age\" href=\"".FRONTPAGE."?opr=showag&age=".$row['age']."\">";
		$out_block .= "E".$row['age'];
		$out_block .= "</a>";
#		$out_block .= "E".$row['age']."<br>(n=".($row['cut_no']+$row['uncut_no']).")";
		$out_block .= "</div>\n";
		
	#	$out_block .= "<a style=\"font-size:0.7em;\" href=\"".FRONTPAGE."?opr=showag&age=".$row['age']."\">&raquo;[explore age]</a>\n";
		$out_block .= "<div class=\"age_group_middle\">";

		$out_block .= "<p class=\"bordered\">Sectioned&nbsp;(n=".$row['cut_no']."): \n";
		$sectioned_names = explode(",", $row['cut_names']);
		foreach ($sectioned_names as $sectioned_name) {
			$out_block .= "<a href=\"".FRONTPAGE."?opr=showem&name=".$sectioned_name."\">".$sectioned_name."</a> ";
		}
		$out_block .= "\n<br />\nUncut&nbsp;(n=".$row['uncut_no']."): \n";
		$uncut_names = explode(",", $row['uncut_names']);
		foreach ($uncut_names as $uncut_name) {
			$out_block .= "<a href=\"".FRONTPAGE."?opr=showem&name=".$uncut_name."\">".$uncut_name."</a> ";
		}
		
		$out_block .= "</p></div>\n";
		$out_block .= "<div class=\"age_group_right\">\n";
		$out_block .= "<div class=\"right_hidden_hover\">sectioned</div>\n";
		$out_block .= sprintf("%01.0f", $row['cut_no']*100/($row['cut_no']+$row['uncut_no']))."%";
#		$out_block .= "<div id=\"".$div_id."\">".sprintf("%01.0f", $row['cut_no']*100/($row['cut_no']+$row['uncut_no']))."%</div>";
		$out_block .= "</div>\n";
		$out_block .= "</div>\n";
	}
	
	$display_block .= "<p>There are <em>".$total_embryo_no."</em> embryos registered!</p>";
	$display_block .= $out_block;

} 
elseif ($_GET['opr'] == "showem") 
{ 
	// shows information of an individual embryo provided by $_GET['name']. This can be either the embryo_id or the embryo name. Object can handle both type of input.

	
	
	$extra_css .= "<link href=\"styles/indie_style.css\" rel=\"stylesheet\" type=\"text/css\">";
	$extra_css .= "<link href=\"styles/sectionsADD.css\" rel=\"stylesheet\" type=\"text/css\">";
	
	$extras_in_header .= "<script src=\"https://d3js.org/d3.v5.min.js\"></script>\n";
	$d3_is_loaded = 1;
	
	$extras_in_header .= "<script type=\"text/javascript\" src=\"embryonic.js\"></script>\n";
	
	$embryo = new embryo($db, $_GET['name'], $restricted_flag);
	
	$pageTitle = "F".$embryo->embryo_name." detailed";
	$head = "see F".$embryo->embryo_name;
	
	$embryo->relatedOperations();
	

	$display_block .= "<h2>F".$embryo->embryo_name." : E".$embryo->embryo_data['age']." [".$embryo->embryo_data['part']."]";
	
	if ($restricted_flag == "OFF") 
	{
		$display_block .= " <a href=\"".RECORDARE."?action=mod_embryo&embryo_id=".$embryo->embryo_id."\">";
		$display_block .= "<i class=\"fa fa-cog\" id=\"editIndie\" onmouseover=\"mouseOverStartSpinCog('editIndie')\" onmouseout=\"mouseOutStopSpinCog('editIndie')\"></i>";
		$display_block .= "</a>";
		
		$display_block .= " <a href=\"".RECORDARE."?action=del_embryo&embryo_id=".$embryo->embryo_id."\">";
		$display_block .= "<i class=\"far fa-trash-alt\"></i>";
		$display_block .= "</a>";

	}
	
	$display_block .= "</h2>\n";
	
	$display_block .= "<div class=\"information_panel_top\">\n";
	$display_block .= "<table class=\"general_info\">\n";
	$display_block .= "<tr>";
	$display_block .= "<th class=\"general_info_heads\">Mother:</th>";
	$display_block .= "<td class=\"general_info_data\"><a href=\"".FRONTPAGE."?opr=explore_mother&madre_id=".$embryo->embryo_data['madre_id']."\">F".$embryo->embryo_data['madre_id']."</a></td>";
	$display_block .= "</tr>\n";

	
	$display_block .= "<tr>";
	$display_block .= "<th class=\"general_info_heads\">Age:</th>";
	$display_block .= "<td class=\"general_info_data\"><a href=\"".FRONTPAGE."?opr=showag&age=".$embryo->embryo_data['age']."\">E".$embryo->embryo_data['age']."</a></td>";
	$display_block .= "</tr>\n";
	
	$display_block .= "<tr>";
	$display_block .= "<th class=\"general_info_heads\">Sectioned:</th>";
	$display_block .= "<td class=\"general_info_data\">";
	
	if ($embryo->cut_status != "cut") // show flag CUT embryo
	{
		$display_block .= "not yet";
		$display_block .= " <a href=\"".RECORDARE."?action=cut_embryo&embryo_id=".$embryo->embryo_id."\">[CUT embryo]</a>";
	} 
	else 
	{
		$display_block .= $embryo->embryo_data['cut']." plane";
	}
	
	$display_block .= "</td>";
	$display_block .= "</tr>\n";
	
	$display_block .= "<tr>";
	$display_block .= "<th class=\"general_info_heads\">CRL (mm):</th>";
	$display_block .= "<td class=\"general_info_data\">".$embryo->embryo_data['crl']."</td>";
	$display_block .= "</tr>\n";
		
	$display_block .= "<tr>";
	$display_block .= "<th class=\"general_info_heads\">Comments:</th>";
	$display_block .= "<td class=\"general_info_data\">".$embryo->embryo_data['comments']."</td>";
	$display_block .= "</tr>\n";	
	
	$display_block .= "</table>\n";
	

	$display_block .= "\n</div>";
	
	$display_block .= "<div class=\"slideTable\">";
	if ($embryo->cut_status == "cut") 
	{
		$embryo->relatedSlides();
		
		$display_block .= $embryo->printSlideTable();
		$display_block .= "<p>Failed slides: ".$embryo->failed_slides."; Mean slide rating: ".$embryo->animal_rating."</p>";
#		$display_block .= "<p><a href=\"#graphContainer\" onclick=\"loadGraph(".implode(",", $embryo->slides_rated).")\" >View graph stats</a>"."</p>";
	}
	
#	echo "<pre>";
#	print_r($embryo->embryo_slides);
#	echo "</pre>";
	
	if ($embryo->sections_count > 0) 
	{
		
		$display_block .= "<a href=\"serial.php?opr=organize&id=".$embryo->embryo_id."\">";
		$display_block .= "<i class=\"fa fa-arrow-right fa-fw\" aria-hidden=\"true\"></i>organize serial";
		$display_block .= "</a>";
		
#		$display_block .= "<br />";
		
#		$display_block .= "<a href=\"serial.php?opr=organize&id=".$embryo->embryo_id."\">";
#		$display_block .= "<i class=\"fa fa-arrow-right fa-fw\" aria-hidden=\"true\"></i>stats";
#		$display_block .= "</a>";
		
		$embryo->calculate_rating();
		
		$dataset = new dataset($db);
	
		$extras_in_header .= $dataset->datasetJS("scoringSet");
		
		$graphTitle = "F".$embryo->embryo_name." slide quality";
		
		$post_js .= $dataset->loadGraphClassAbFooter();
		
		$graphDiv .= "<div id=\"simpleScatter\"></div>";
		
		$post_js .= $dataset->plotGraph("simpleScatter", 800, 400, "bubble");
		
#		$post_js .= "<script type=\"text/javascript\" src=\"ScoreScatter.js\"></script>\n";
		
		$post_js .= "<script type=\"text/javascript\">showSlidePoints(".implode(",", $embryo->slides_rated).")</script>\n";
		
#		$display_block .= "<p><a href=\"serial.php?opr=organize&id=".$embryo->embryo_id."\">organize serial</a></p>";
	}
	
	$display_block .= "</div>\n";
	
	$embryo->formatOperations();
	
	
	$display_block .= "<div class=\"post_slide_info\">\n";
	
	$display_block .= "<div class=\"quad_first\">";
	$display_block .= "<h4>operations</h4><p>";
	$display_block .= $embryo->embryo_operations_html;
	$display_block .= "</p>";
	$display_block .= "<p>[<a href=\"".RECORDARE."?action=add_oper&embryo_id=".$embryo->embryo_id."\">Add operation</a>]</p>";
	$display_block .= "</div>";
	
	
	
	$display_block .= "<div class=\"quad_second\">";
	$display_block .= "<h4>related tissues</h4>";
	$display_block .= "<ul class=\"siblings\">\n";
	$embryo->get_siblings();
	$display_block .= $embryo->print_siblings();
	$display_block .= "</ul></div>";
	
#	$display_block .= "<div class=\"quad_third\">".$embryo->embryo_operations_html."</div>";
	
#	$display_block .= "<div class=\"quad_fourth\">".$embryo->embryo_operations_html."</div>";
	
	$display_block .= "</div>";
	
	$allPictures = array();
	$embryo_pics = array();
	$embryo->get_embryo_picture();

	if ($embryo->embryo_pictures == "not_found") 
	{
		$embryo_description = "embryo picture not found";
		$embryo_picture_src = REL_ICONS_LOCUS."not_found.png";
		
	} 
	elseif (is_array($embryo->embryo_pictures) && count($embryo->embryo_pictures)>0) 
	{
		
#		echo "<pre>";
#		print_r($embryo->embryo_pictures);
#		echo "</pre>";
		
		$group_description = "F".$embryo->embryo_id;
		$group_picture_src = $embryo->embryo_pictures[0][1];
		
		$embryo_pics = $embryo->embryo_pictures;
		
#		$allPictures = $embryo->embryo_pictures;
			
		
	}
	
	$mother = new madre($db, $embryo->embryo_data['madre_id']);
	$mother->fetchRelatedPics();

	
	if ($mother->allPictures == "not_found") 
	{
		$group_description = "group picture not found";
		$group_picture_src = REL_ICONS_LOCUS."not_found.png";
		$extras_in_header .= "<script type=\"text/javascript\">var allPics = ".json_encode($embryo->embryo_pictures, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)."; </script>";
		
		$allPictures = $embryo_pics;
		
	} 
	elseif (is_array($mother->allPictures) && count($mother->allPictures)>0) 
	{
		$group_description = "F".$mother->madre_id;
		$group_picture_src = $mother->allPictures[0][1];
		
#		$allPictures += $mother->allPictures;
		
		$allPictures = array_merge($embryo_pics, $mother->allPictures);
		
	}

#	echo "<pre>";
#	print_r($allPictures);
#	echo "</pre>";

	if (count($allPictures) > 0) 
	{
		
		
		$extras_in_header .= "<script type=\"text/javascript\">var allPics = ".json_encode($allPictures, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)."; </script>";
		$post_js .= "<script type=\"text/javascript\" src=\"picGallery.js\"></script>\n";
		$post_js .= "<script type=\"text/javascript\">var gallery = new imageGallery(allPics, ".$mother->madre_id."); gallery.showGal();</script>";
#		$post_js .= "<script type=\"text/javascript\">var madre_id=".$mother->madre_id."; var gallery = new imageGallery(allPics, madre_id); gallery.showGal();</script>";
		
	} 
	else 
	{
		
		$img_block .= "<div class=\"column\">\n";
		$img_block .= "<a target=\"_blank\" href=\"".$embryo_picture_src."\">\n";
		$img_block .= "<img src=\"".$embryo_picture_src."\" alt=\"".$embryo_description."\" width=\"180px\">\n";
		$img_block .= "</a>\n";
		$img_block .= "<div class=\"desc\">".$embryo_description."</div>\n";
		$img_block .= "</div>\n";
	
		$img_block .= "<div class=\"column\">\n";
		$img_block .= "<a target=\"_blank\" href=\"".$group_picture_src."\">\n";
		$img_block .= "<img src=\"".$group_picture_src."\" alt=\"".$group_description."\" width=\"180px\">\n";
		$img_block .= "</a>\n";
		$img_block .= "<div class=\"desc\">".$group_description."</div>\n";
		$img_block .= "</div>";
		
	}
	
	

	




############################################################

############################################################
#echo "<pre>";
#print_r($allPictures);
#print_r($embryo->embryo_operations);
#print_r($embryo->embryo_slides);
#echo "</pre>";

}
elseif ($_GET['opr'] == "explore_mother") 
{
	

	$mother = new madre($db, $_GET['madre_id']);
	
	
	$head = "mother F".$mother->madre_id;
	$pageTitle = $head." detailed";
		
	$extra_css = "<link href=\"styles/indie_style.css\" rel=\"stylesheet\" type=\"text/css\">";
	$extras_in_header .= "<script type=\"text/javascript\" src=\"embryonic.js\"></script>\n";
	
	$display_block .= "<h2>F".$mother->madre_id." (".$mother->madre_info['name'].")</h2>";
	$display_block .= "<div class=\"information_panel_top\">\n";
	$display_block .= "<table class=\"general_info\">\n";

	require_once("date.class.php");
	$surg_date = new date_pulldown();
	$surg_date->load_compact_date($mother->madre_info['surgery_date']);
	
	$display_block .= "<tr>";
	$display_block .= "<th class=\"general_info_heads\">Surgery date:</th>";
	$display_block .= "<td class=\"general_info_data\">".$surg_date->format_out()."</td>";
	$display_block .= "</tr>";
	
	$surg_date = null;
	
	$display_block .= "<tr>";
	$display_block .= "<th class=\"general_info_heads\">Age:</th>";
	$display_block .= "<td class=\"general_info_data\"><a href=\"".FRONTPAGE."?opr=showag&age=".$mother->embryo_age."\">Ε".$mother->embryo_age."</a></td>";
	$display_block .= "</tr>";

	$display_block .= "<tr>";
	$display_block .= "<th class=\"general_info_heads\">Littermates:</th>";
	$display_block .= "<td class=\"general_info_data\">";
	
	foreach ($mother->embryo_names as $embryo) {
		$display_block .= "<a href=\"".FRONTPAGE."?opr=showem&name=".$embryo."\">".$embryo."</a> \n";
	}
	
	$display_block .= "</td>";
	$display_block .= "</tr>";

	$display_block .= "<tr>";
	$display_block .= "<th class=\"general_info_heads\">CRL average:</th>";
	$display_block .= "<td class=\"general_info_data\">".$mother->crl_average."</td>";
	$display_block .= "</tr>";
	
	$display_block .= "<tr>";
	$display_block .= "<th class=\"general_info_heads\">Comments:</th>";
	$display_block .= "<td class=\"general_info_data\">".$mother->madre_info['comments']."</td>";
	$display_block .= "</tr>";
	
	$display_block .= "</table>";
	
	
	if ($restricted_flag == "OFF") {
		
		$display_block .= "<p><a href=\"".RECORDARE."?action=mod_mother&madre_id=".$mother->madre_id."\">[Edit data]</a></p>\n";
		
		$display_block .= "<form method=\"POST\" action=\"".RECORDARE."?action=mod_age&madre_id=".$mother->madre_id."\">\n";
		$display_block .= "<select name=\"new_age\">\n";
		
		for ($i=0; $i<64; $i+=0.5) {
			
			$selected = ($i == $mother->embryo_age) ? "selected" : "";
			
			$display_block .= "<option value=\"".$i."\" ".$selected.">".sprintf("%.1f", $i)."</option>\n";
		}
		
		$display_block .= "</select>\n";
		$display_block .= "<input type=\"submit\" class=\"button\" value=\"change age\">";
		$display_block .= "</form>";
	}
	$display_block .= "</div>\n";
	
	
	$mother->fetchRelatedPics("all");
	if ($mother->allPictures == "not_found") {
		$group_description = "mother picture not found";
		$group_picture_src = REL_ICONS_LOCUS."not_found.png";
		
	} elseif (is_array($mother->allPictures) && count($mother->allPictures)>0) {
		$group_description = "F".$mother->madre_id;
		$group_picture_src = $mother->allPictures[0][1];
		$extras_in_header .= "<script src=\"https://d3js.org/d3.v5.min.js\"></script>\n";
		$extras_in_header .= "<script type=\"text/javascript\">var allPics = ".json_encode($mother->allPictures,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)."; </script>";
	
		$post_js .= "<script type=\"text/javascript\" src=\"picGallery.js\"></script>\n";
		$post_js .= "<script type=\"text/javascript\">var madre_id=".$mother->madre_id."; const gallery = new imageGallery(allPics, madre_id); gallery.showGal();</script>";
		
	}
	
	$img_block .= "<div class=\"column\">\n";
	$img_block .= "<a target=\"_blank\" href=\"".$group_picture_src."\">\n";
	$img_block .= "<img src=\"".$group_picture_src."\" alt=\"".$group_description."\" width=\"180px\">\n";
	$img_block .= "</a>\n";
	$img_block .= "<div class=\"desc\">".$group_description."</div>\n";
	$img_block .= "</div>";
	
} 
elseif ($_GET['opr'] == "sets") 
{
	
	$pageTitle = "sectioned embryos";
	$head = "sets";
	
	
	$extras_in_header .= "<script type=\"text/javascript\" src=\"embryonic.js\"></script>\n";
	$extra_css .= "<link href=\"styles/sets_style.css\" rel=\"stylesheet\" type=\"text/css\">";
	
#	$sql = "select id from embryo where cut = 'coronal' or cut = 'sagittal' and part != 'body' order by age, name asc";
#	$sql = "select id from embryo where cut = 'coronal' or cut = 'sagittal' and part != 'body' order by age, name asc";
	
	$sql = "select id from embryo where length(cut)>1 and part != 'body' order by age, name asc";
	
	$ids_ret = $db->prepare($sql);
	$ids_ret->execute();
	$ids = array();
	while ($result = $ids_ret->fetch(PDO::FETCH_ASSOC)) {
		$ids[] = $result['id'];
	}
	
		
	if (($_SESSION['user_data']['perm'] > 1)) {
			
		
#		$post_js = "<script type=\"text/javascript\" src=\"embryonic_post.js\"></script>\n";
		
		if (isset($_GET['basket']) && $_GET['basket'] == "enable") {

			$display_block .= "<div id=\"basket\">No sections selected yet</div>";
		} elseif (isset($_SESSION['addedSlides']) && count($_SESSION['addedSlides']) > 0) {

			$display_block .= "<div id=\"basket\">".count($_SESSION['addedSlides'])." sections selected<br />";
			$display_block .= "<a href=\"viewBasket.php?opr=showBasket\">start experiment</a></div>";
		}
//echo "<pre>";
//print_r($_SESSION['addedSlides']);
//echo "</pre>";
	}
	
//	if (isset($_SESSION['addedSlides']) && count($_SESSION['addedSlides']) > 0) {
//		$display_block .= "<div id=\"basket\">".count($_SESSION['addedSlides'])." sections selected<br />";
//		$display_block .= "<a href=\"viewBasket.php?opr=showBasket\">start experiment</a></div>";
//	}
	
	foreach ($ids as $id) {
		$embryo = new embryo($db, $id, $restricted_flag);	
		$embryo->relatedSlides();
#		$display_block .= "<div class=\"embryo\">\n";
		$display_block .= "<div class=\"slideTableInside\">\n";
		
		
		$display_block .= "<div class=\"slideTableInsideHead\">";
		$display_block .= "<div class=\"animalName\">";
		
		$display_block .= "<h3 class=\"slideTableHead\">E".$embryo->embryo_data['age']." : "."<a href=\"".FRONTPAGE."?opr=showem&name=".$id."\">F".$embryo->embryo_name." [".$embryo->embryo_data['part']."] (".$embryo->embryo_data['cut'].")</a></h3>\n";

		$display_block .= "</div>";
		
		$display_block .= "<div class=\"serialButton\">";
		
		if ($embryo->sections_count != false) {
			$display_block .= "<a href=\"serial.php?opr=organize&id=".$embryo->embryo_id."\"><i class=\"fa fa-arrow-right fa-fw\" aria-hidden=\"true\"></i> serial</a>";
		}
		
		$display_block .= "</div>";
		$display_block .= "</div>\n";
		
		
		$display_block .= "</div>";
		
		
#		$display_block .= "<a href=\"serial.php?opr=organize&id=".$embryo->embryo_id."\">organize serial <i class=\"arrow right\"></i></a>";
		
#		$display_block .= "<h2><a href=\"".FRONTPAGE."?opr=showem&name=".$id."\">F".$embryo->embryo_name." : E".$embryo->embryo_data['age']." [".$embryo->embryo_data['part']."] (".$embryo->embryo_data['cut'].")</a></h2>\n";
		if ( 
			($_SESSION['user_data']['perm'] > 1) 
			&& ( ( isset($_GET['basket']) && ($_GET['basket'] == "enable"))) || isset($_SESSION['addedSlides'])) {
					
			if (isset($_SESSION['addedSlides']) && (count($_SESSION['addedSlides']) > 0) ) {
				$display_block .= $embryo->printSlideTable("basketON", $_SESSION['addedSlides']);
			} else {
				$display_block .= $embryo->printSlideTable("basketON");
			}
		} else {
			$display_block .= $embryo->printSlideTable();
		}
#		$display_block .= "\n</div>\n";

	}


} 

elseif ($_GET['opr'] == "exper") 
{
	
	$head = $pageTitle =  "experiments";
	
	$extra_css .= "<link href=\"styles/exper_style.css\" rel=\"stylesheet\" type=\"text/css\">";
	$extra_css .= "<link href=\"styles/showMassive_style.css\" rel=\"stylesheet\" type=\"text/css\">";
	
#	$sql = "select distinct(experiment_date) from slide where experiment_date is not null order by experiment_date";
	$sql = "select distinct(experiment_date) from slide order by experiment_date";
	$date_ret = $db->prepare($sql);
	$date_ret->execute();
	$dates_frost = $date_ret->fetchAll(PDO::FETCH_COLUMN, 0);
	
	$dates = array_unique($dates_frost);

//echo sizeof($dates)."<br>";
	
	// prepare the array
	for ($i=0;$i<(count($dates));++$i) {
		if (is_numeric($dates[$i])) {
			continue;
//			settype($dates[$i], "integer");
		} else {
			array_splice($dates, $i, 1);
		}
	}


	
	$jsoned_dates = json_encode($dates);
	$to_js = "<script>\n";
	$to_js .= "var allDates=".$jsoned_dates.";\n";
	$to_js .= "</script>\n";
		
	$extras_in_header .= $to_js;
	$extras_in_header .= "<script type=\"text/javascript\" src=\"embryonic.js\"></script>\n";

	// display the first select element
	$years = array();
	$found = "NO";
	
	for ($i=0;$i<count($dates);++$i) {
#		echo $i."<br>";
		$year = substr($dates[$i], 0, 4);
		for ($j=0;$j<count($years);++$j) {
#			echo $j."<br>";
			if ($years[$j] == $year) {
				$found = "YES";
#				break;
			} else {
				continue;
			}
		}
		if ($found == "YES") {
			$found = "NO";
			continue;
		} else {
			$years[] = $year;
		}
		
	}
	
	if (isset($_SESSION['addedSlides']) && count($_SESSION['addedSlides']) > 0) {
		$display_block .= "<div id=\"basket\">".count($_SESSION['addedSlides'])." sections selected<br />";
		$display_block .= "<a href=\"viewBasket.php?opr=showBasket\">start experiment</a></div>";
	}
	
	$display_block .= "<h2>Please select:</h2>";
	$display_block .= "<p>There are <em>".sizeof($dates)."</em> different experiments registerd.";
	
#	$display_block .= " View <a href=\"javascript:void(0);\" onclick=\"showExp(\"\")\">latest</a>. ";
	
	if ($_SESSION['user_data']['perm'] > 1) {
		$display_block .= "<br />You may <a href=\"".FRONTPAGE."?opr=sets&basket=enable\">add an experiment</a>";
	}
	$display_block .= "</p>";
	$display_block .= "<div id=\"date_selector\">";
	$display_block .= "<div id=\"select_year\"><select name=\"year\" onchange=\"showMonth(this.options[this.selectedIndex].value);\">\n";
	$display_block .= "<option value=\"0\">YEAR</option>\n";
	foreach ($years as $year) {
		$display_block .= "<option value=\"".$year."\">".$year."</option>\n";
	}
	$display_block .= "</select></div>\n";
	
	$display_block .= "<div id=\"select_month\"></div>";
	$display_block .= "<div id=\"select_day\"></div>";
	$display_block .= "</div>";
	
	if ((isset($_GET['showDate'])) && ($_GET['showDate'] > 0)) {
		$display_block .= "<script>showExp(\"".$_GET['showDate']."\");</script>";
	}
	
	$display_block .= "<div id=\"experiments\"></div>";
	

#echo "<pre>";
#print_r($dates);
#echo gettype($dates[2]);
#print_r($years);
#echo "</pre>";

} 
elseif ($_GET['opr'] == "showag") 
{
	$extras_in_header .= "<script type=\"text/javascript\" src=\"embryonic.js\"></script>\n";
	$extra_css .= "<link href=\"styles/sets_style.css\" rel=\"stylesheet\" type=\"text/css\">";
	
	if ( empty($_GET['age']) || !is_numeric($_GET['age']) ) {
		echo "no valid date provided!";
		$db = null;
		die;
	}
	
	$head = "E".$_GET['age'];
	
	$pageTitle = $head." detailed";

	/****************************************************
	/*          UNCUT      
	/*****************************************************/
	$sql_uncut = "select name from embryo where part!='body' and set_height is null and age=?";
	
	$prep_uncut = $db->prepare($sql_uncut);
	$prep_uncut->bindValue(1, $_GET['age'], PDO::PARAM_STR);
	$prep_uncut->execute();
	
	$res_uncut = $prep_uncut->fetchAll(PDO::FETCH_COLUMN, 0);
	
	$how_many_uncut = count($res_uncut);
	
	if ($how_many_uncut > 0) {
		$display_block .= "<h2>Remaining embryos (n=".$how_many_uncut.")</h2>\n";
		$display_block .= "<ul>";
		foreach ($res_uncut as $a_uncut) {
			$display_block .= "<li><a href=\"".FRONTPAGE."?opr=showem&name=".$a_uncut."\">".$a_uncut."</a>";
			$uncut_embryo  = new embryo($db, $a_uncut);
			$uncut_embryo->relatedOperations();
			$display_block .= " [in ".$uncut_embryo->lastOperation()."]";
			$display_block .= "</li>\n";
			unset($uncut_embryo);
		}

		$display_block .= "</ul>";
	} else {
		$display_block .= "<h2>Remaining embryos</h2>\n";
		$display_block .= "<p>There are no embryos left behind!</p>\n";
	}
	
	
#	echo "<pre>";
#	print_r($res_uncut);
#	echo "</pre>";

/****************************************************
/*          CUT      
/*****************************************************/
	
# PROTECT so as not to print << and JS link!
	if ($restricted_flag == "OFF") {
		$restriction = "OFF";
	} else {
		$restriction = "PROTECT";
	}
	$sql_cut = "select name, cut from embryo where part!='body' and set_height>0 and age=?";
	
	$prep_cut = $db->prepare($sql_cut);
	$prep_cut->bindValue(1, $_GET['age'], PDO::PARAM_STR);
	$prep_cut->execute();
	$res_cut = $prep_cut->fetchAll(PDO::FETCH_ASSOC);
#	$res_cut = $prep_cut->fetchAll(PDO::FETCH_COLUMN, 0);
	
	$how_many_cut = count($res_cut);
	if ($how_many_cut > 0) {
		$display_block .= "<h2>Sectioned embryos (n=".count($res_cut).")</h2>\n";
		$display_block .= "<ul>";
	
		foreach ($res_cut as $a_cut) {
			$display_block .= "<li><a href=\"".FRONTPAGE."?opr=showem&name=".$a_cut['name']."\">".$a_cut['name']."</a> [".$a_cut['cut']."]</li>\n";
			$cut_embryo = new embryo ($db, $a_cut['name'], $restriction);
			$cut_embryo->relatedSlides();
#			$out_block .= "<div class=\"embryo\">";
			$out_block .= "<div class=\"slideTableInside\">";
			$out_block .= "<div class=\"slideTableInsideHead\">";
			$out_block .= "<div class=\"animalName\">";
			$out_block .= "<h3 class=\"slideTableHead\"><a href=\"".FRONTPAGE."?opr=showem&name=".$a_cut['name']."\">F".$a_cut['name']."</a></h3>";
#			$out_block .= "<a href=\"".FRONTPAGE."?opr=showem&name=".$a_cut['name']."\">F".$a_cut['name']."</a>";
			$out_block .= "</div>";
			$out_block .= "<div class=\"serialButton\">";
			if ($cut_embryo->sections_count != false) {
				$out_block .= "<a href=\"serial.php?opr=organize&id=".$cut_embryo->embryo_id."\"><i class=\"fa fa-arrow-right fa-fw\" aria-hidden=\"true\"></i> serial</a>";
			}
			$out_block .= "</div>";
			$out_block .= "</div>\n";
			$out_block .= $cut_embryo->printSlideTable()."</div>\n";
			unset($cut_embryo);
		}
	
		$display_block .= "</ul>";
	
	#	echo "<pre>";
	#	print_r($res_cut);
	#	echo "</pre>";

		$display_block .= "<h2>Sets within E".$_GET['age']."</h2>\n";
		$display_block .= $out_block;
	} else {
		$display_block .= "<h2>Sectioned embryos</h2>\n";
		$display_block .= "<p>No embryos sectioned yet!</p>\n";
	}
	
} 

/* MASSIVE tables */
elseif ($_GET['opr'] == "showmoth") 
{
	
	$head = $pageTitle =  "The Mothers (of Invention)";
	
	$extra_css .= "<link href=\"styles/showMassive_style.css\" rel=\"stylesheet\" type=\"text/css\">";
	
	$madre_sql = "select madre.id, madre.name, madre.surgery_date, madre.comments, embryo.age, count(*) as embr_ple from embryo inner join madre on embryo.madre_id = madre.id where embryo.part != 'body' group by madre.id";

	$madre_ret = $db->prepare($madre_sql);
	$madre_ret->execute();
	$mothers = $madre_ret->fetchAll(PDO::FETCH_ASSOC);

	
	if ($restricted_flag == "OFF") {
		$display_block .= "<p style=\"text-align: center;\"><a href=\"".RECORDARE."?action=add_mother\">add a mother</a></p>\n";
	}
	
	
	
	$display_block .= "<table class=\"massive_table\">\n";
	$display_block .= "<tr><th>id</th><th>name</th><th>OHE date</th><th>embryos</th><th>age</th><th>comments</th></tr>\n";
	
	foreach ($mothers as $indie_mother) {
		$display_block .= "<tr>";
		$display_block .= "<td>".$indie_mother['id']."</td>";
#		$display_block .= "<td><a href=\"".RECORDARE."?action=mod_mother&madre_id=".$indie_mother['id']."\">".$indie_mother['name']."</a></td>";
		$display_block .= "<td><a href=\"".FRONTPAGE."?opr=explore_mother&madre_id=".$indie_mother['id']."\">".$indie_mother['name']."</a></td>";
		$display_block .= "<td>".format_date($indie_mother['surgery_date'])."</td>";
		$display_block .= "<td>".$indie_mother['embr_ple']."</td>";
		$display_block .= "<td><a href=\"".FRONTPAGE."?opr=showag&age=".$indie_mother['age']."\">".$indie_mother['age']."</a></td>";
		$display_block .= "<td>".$indie_mother['comments']."</td>";
		$display_block .= "</tr>\n";
	}

	$display_block .= "</table>";
}
elseif ($_GET['opr'] == "showallemb") 
{ // show all embryos
	
	$head = $pageTitle = "all embryos";
	
	$extra_css .= "<link href=\"styles/showMassive_style.css\" rel=\"stylesheet\" type=\"text/css\">";

		
	if ( isset($_GET['part']) and ($_GET['part'] == "all") ) {
		$display_block .= "<p>[<a href=\"".FRONTPAGE."?opr=showallemb\">exclude bodies</a>]</p>";
		$baby_sql = "select id, name, part, cut, age, comments, set_width, set_height from embryo order by name asc";
	} else {
		$display_block .= "<p>[<a href=\"".FRONTPAGE."?opr=showallemb&part=all\">show all</a>]</p>";

#		$display_block .= "<p>Choose range:&nbsp;From:<select name=\"age_from\">";

		$baby_sql = "select id, name, part, cut, age, comments, set_width, set_height from embryo where part !='body' order by name asc";	
	}

	$baby_ret = $db->prepare($baby_sql);
	$baby_ret->execute();
	
	$babies = $baby_ret->fetchAll(PDO::FETCH_ASSOC);
	
#	$display_block .= "<p>[<a href=\"".FRONTPAGE."?handle=baby&part=all\">show all</a>&nbsp;|&nbsp;<a href=\"".FRONTPAGE."?handle=baby\">ex bodies</a>]</p>";
	
	$display_block .= "<table class=\"massive_table\">\n";
	$display_block .= "<tr><th>id</th><th>name</th><th>age</th><th>part</th><th>cut</th><th>comments</th></tr>\n";
	
	foreach ($babies as $baby) {
//		echo "w:".gettype($baby['set_width']);
//		echo " -h".gettype($baby['set_height']);
//		if ( $baby['set_width']*$baby['set_height'] != 0 ) {
		if ( $baby['set_width'] != NULL or $baby['set_height'] != NULL ) {
			$display_block .= "<tr class=\"cutalready\">";
		} else {
			$display_block .= "<tr>";
		}
		$display_block .= "<td><a href=\"".FRONTPAGE."?opr=showem&name=".$baby['id']."\">".$baby['id']."</a>";
		
		if ($restricted_flag === "OFF") {
			$display_block .= " -- <a href=\"recordare.php?action=del_embryo&embryo_id=".$baby['id']."\"><i class=\"far fa-trash-alt\"></i></a>";
		}
		
		
		$display_block .= "</td>";
		$display_block .= "<td>".$baby['name']."</td>";
		$display_block .= "<td><a href=\"".FRONTPAGE."?opr=showag&age=".$baby['age']."\">E".$baby['age']."</a></td>";
		$display_block .= "<td>".$baby['part']."</td>";
		$display_block .= "<td>".$baby['cut']."&nbsp;(".$baby['set_width']."x".$baby['set_height'].")"."</td>";
		$display_block .= "<td>".$baby['comments']."</td>";
		$display_block .= "</tr>\n";
	}

	$display_block .= "</table>";


} 
elseif ($_GET['opr'] == "about") 
{
	
	$head = $pageTitle =  "hi!";
	
	$display_block = "<div style=\"text-indent:2em; text-align:justify; line-height:1.2;\">		
		<p>This site is built in an effort to organize my PhD and contains unpublished results. It was developed (and is still under construction) in a period spaning more than four years. Tools used are php, sqlite, html5, javascript as well as the javascript library D3.js and the icon collection Font Awesome. Unfortunately, few functions that require the <a href=\"https://www.ncbi.nlm.nih.gov/books/NBK25501/\" target=\"_blank\">NCBI toolkit</a> and the <a href=\"https://www.ncbi.nlm.nih.gov/tools/sviewer/\" target=\"_blank\">SViewer</a> are inactivated by the hosting server.</p>
		<p>Allthough this site is built-up nearly from scratch, I would be at least ungrateful, in case I forgot to mention that the basic css template comes from my dearest classmate (back in the vet school days), applied ethologist and (undercover, nevertheless) professional web designer <a href=\"https://unstoppablevet.net\" target=\"_blank\">unstoppable vet</a>, who (back then) developed wonderful templates and was generous enough to grant their use to everyone (through a creative commons license) including me!</p></div>";
		
		if ($_SESSION['user_data']['usr'] == "thunderkit") {
			
			$display_block .= "<p>Μαρία επιτέλους καλοσώρισες! Μόνο εσύ βλέπεις το παρόν, ελληνικό, μήνυμα. Έχω διαβάσει τα εντός του νου σου και είμαι ευτυχισμένος που κατάφερες να λύσεις με τον, κατά τη γνώμη μου, πλέον σωστό τρόπο την κατάσταση που περιγράφεις. Ελπίζω να σου άρεσε η ιστοσελίδα. Σου στέλνω ειλικρινή αγάπη!</p>";
			
		}
		
	
/*	$display_block = "<div style=\"text-indent:2em; text-align:justify; line-height:1.2;\">
		This site is built in an effort to organize my PhD. It was developed (and is still under construction) in a period spaning for over than two years.
		During this time course, I participate in the active process of learning techniques regarding web (as well as feline brain) develeopment.<br />
		Tools used include html5 (perhaps the easiest way to build window-based interfaces), php (full credit for getting this project to life), css (invaluable for web design), javascript (my most-recent discovery, greatly enhancing interactivity) and of course (the most important last - but not least) sql (the backbone of this site). <br />
	Allthough this site is built-up nearly from scratch, I would be at least ungrateful, in case I forgot to mention that the basic css template comes from my dearest classmate (back in the vet school days), applied ethologist and (undercover, nevertheless) professional web designer <a href=\"https://unstoppablevet.net\" target=\"_blank\">unstoppable vet</a>, who (back then) developed wonderful templates and was generous enough to grant their use to everyone (through a creative commons license) including me!</div>";
*/

} 
else {
	header('Location: '.FRONTPAGE);
}

$db = null;

?>
<!DOCTYPE html>
<html>
<head>
<link href="styles/embryonicB.css" rel="stylesheet" type="text/css">

<?php 
echo $extra_css; 
echo $favicons;
?>

<!--script src="https://use.fontawesome.com/b69a8f0e1d.js"></script-->
<script src="https://kit.fontawesome.com/b236ea9bb0.js" crossorigin="anonymous"></script>
<?php echo $extras_in_header; ?>

<title><?php echo $pageTitle; ?></title>
</head>
<body>

	<div class="container">
		<div class="into">

			<div class="bind"></div>

			<div class="facultyid" id="pageTop"><h2><?php echo $head; ?></h2></div>
			<div class="bind"></div>
			<div class="menu"><?php echo $navigation_menu; ?></div>
			<?php if (isset($kleines_menu)) { echo $kleines_menu; } ?>
			
			<div class="bind"></div>
			<div class="loginData">
				Signed in as <?php echo $_SESSION['user_data']['legal_name']; ?><br />
				<a href="login.php?opr=logout">Sign out <i class="fa fa-sign-out"></i></a>
			</div>
			<div class="bind"></div>
			
			<div class="text">
				<?php echo $display_block; ?>
			</div>

			<div id="pics_wrap" class="modal">
				
				<div class="modalContent">
				
					<div class="modalHeader">
						<span class="closeBtn" onclick="clearSections('sectionSlides')">&times;</span>
					</div>
					
					<div class="modalInner" id="sectionSlides"></div>
					
					<!--a href="javascript:void(0)" onclick="clearSections('sectionSlides')">
						<i class="fas fa-window-close fa-4x"></i>
						</a-->
	
				
						<div class="modalFooter"></div>
					</div>
				</div>
			
					
			<div class="row">
				<?php echo $img_block; ?>
			</div>
			
				
			<!-- response here! -->

			<div id="graphContainer">
				
				<div id="acc"></div>
				<div id="graphTitle"><?php echo $graphTitle; ?></div>
				<?php echo $graphDiv ?>
				
				<!--div id="overview_graph"></div-->
				
			
				
			</div>
			

			
			<div class="go_to_top"><a href="#pageTop">
				<i class="fa fa-arrow-circle-up fa-3x"></i>
				<!--img src="icons/goTop.png" alt="go to top"-->
			</a></div>
			<div class="footer"><?php echo $footer; ?></div>
		</div>
	</div>

<?php echo $post_js; ?>

</body>
</html>