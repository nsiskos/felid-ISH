<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', true);

session_start();
require_once("standards.php");
$head="";
$display_block = "";
$img_block = "";
$extras_in_header = "";
$post_js = "";
$extra_css = "";

# this page requires user permission at least = 2
$page_permission = 2;

$now = time();
if (isset($_SESSION['time'])) {
	$difference = $now - $_SESSION['time'];
}

// perform the login check. $temporal_allownce is found inside standards.php
if ( !isset($_SESSION['time']) or ($difference > $temporal_allowance) or !isset($_SESSION['user_data']) ) { 
	
	// go to login page
	header('Location: login.php?opr=login&fm=front');
	die;
}

# this page requires user permission at least = 2
if ( ($_SESSION['user_data']['perm'] < $page_permission) or (!isset($_SESSION['user_data']['perm'])) ) {
	echo "You do not have sufficient priviledges to access this page!</br>";
	echo "<a href=\"".FRONTPAGE."\">Click here</a>";
	die;
}

header('Content-type: text/html; charset=utf-8');
require_once("db_handle.php");

function image_path_tester($image_path) 
{
	if (!isset($image_path)) { return false; }
	
	if (!is_string($image_path)) { return false; }
	
	if (strlen($image_path) < 4) { return false; }
	
	$matches = array();
	$pattern = '/((\.\.\/)*(\w+\/)*\w+)+\.(jpeg|png|jpg)/';
	
	if (preg_match($pattern, $image_path, $matches)) {
		return $matches[0];
	} else {
		return false;
	}
	
}


if ($_GET['action'] == "add_mother") // add a new mother record
{
	
	require_once("date.class.php");
	$date = new date_pulldown();
	
	$head = "add new mother";
	
	$display_block .= "<form action=\"".RECORDARE."?action=do_add_mother\" method=\"POST\">\n";
	$display_block .= "<fieldset>\n<legend>madre information</legend>\n";
	$display_block .= "<p>provide name:<br>\n";
	$display_block .= "<input type=\"text\" name=\"madre_name\" size=\"48\" required></p>\n";
	$display_block .= "<p>provide surgery date<br>";
	$display_block .= "year: ";
	$display_block .= $date->select_year();
#	$display_block .= "<input type=\"text\" name=\"year\" size=\"4\" maxlength=\"4\">&nbsp;";
	$display_block .= "month: ";
	$display_block .= $date->select_month();
#	$display_block .= "<input type=\"text\" name=\"month\" size=\"2\" maxlength=\"2\">&nbsp;";
	$display_block .= "day: ";
	$display_block .= $date->select_day();
#	$display_block .= "<input type=\"text\" name=\"day\" size=\"2\" maxlength=\"2\">&nbsp;\n";
	$display_block .= "<p>are there any comments?<br>";
	$display_block .= "<textarea name=\"comments\"></textarea></p>\n";
	$display_block .= "</fieldset>\n";
	
	$display_block .= "<fieldset>\n<legend>embryo information</legend>\n";
	$display_block .= "<p>age (E): ";
	$display_block .= "<input type=\"text\" name=\"age\" size=\"4\" maxlength=\"4\" required></p>\n";
	$display_block .= "<p>how many: ";
	
	$display_block .= "<select name=\"howmany\">";
	for ($i=1;$i<12;++$i) {
		
		$display_block .= "<option value=\"".$i."\"";
		
		if ($i == 4) {
			$display_block .= " selected";
		}
		$display_block .= ">".$i."</option>\n";
			
	}
	$display_block .= "</select>";
	
	
	$display_block .= "<input type=\"radio\" name=\"embryo_part\" value=\"whole\" checked=\"checked\">whole\n";
	$display_block .= "<input type=\"radio\" name=\"embryo_part\" value=\"body_head\">body &amp; head\n</p>\n";
	$display_block .= "<p>received on: <br>";
	$display_block .= "year: ";
	$display_block .= $date->select_year("rec_year");
#	$display_block .= "<input type=\"text\" name=\"rec_year\" size=\"4\" maxlength=\"4\">&nbsp;";
	$display_block .= "month: ";
	$display_block .= $date->select_month("rec_month");
#	$display_block .= "<input type=\"text\" name=\"rec_month\" size=\"2\" maxlength=\"2\">&nbsp;";
	$display_block .= "day: ";
	$display_block .= $date->select_day("rec_day");
#	$display_block .= "<input type=\"text\" name=\"rec_day\" size=\"2\" maxlength=\"2\">&nbsp;\n";
	$display_block .= "hour: ";
	$display_block .= $date->select_hour("rec_hour");
#	$display_block .= "<input type=\"text\" name=\"rec_hour\" size=\"2\" maxlength=\"2\">&nbsp;\n";
	$display_block .= "min: ";
	$display_block .= $date->select_minute("rec_min");
#	$display_block .= "<input type=\"text\" name=\"rec_min\" size=\"2\" maxlength=\"2\">&nbsp;\n";
	$display_block .= "</p>\n";
	$display_block .= "<p>pfa immersion: <br>";
	$display_block .= "year: ";
	$display_block .= $date->select_year("pfa_year");
#	$display_block .= "<input type=\"text\" name=\"pfa_year\" size=\"4\" maxlength=\"4\">&nbsp;";
	$display_block .= "month: ";
	$display_block .= $date->select_month("pfa_month");
#	$display_block .= "<input type=\"text\" name=\"pfa_month\" size=\"2\" maxlength=\"2\">&nbsp;";
	$display_block .= "day: ";
	$display_block .= $date->select_day("pfa_day");
#	$display_block .= "<input type=\"text\" name=\"pfa_day\" size=\"2\" maxlength=\"2\">&nbsp;\n";
	$display_block .= "hour: ";
	$display_block .= $date->select_hour("pfa_hour");
#	$display_block .= "<input type=\"text\" name=\"pfa_hour\" size=\"2\" maxlength=\"2\">&nbsp;\n";
	$display_block .= "min: ";
	$display_block .= $date->select_minute("pfa_min");
#	$display_block .= "<input type=\"text\" name=\"pfa_min\" size=\"2\" maxlength=\"2\">&nbsp;\n";
	$display_block .= "</p>\n";
	$display_block .= "</fieldset>\n";
	$display_block .= "<p><input type=\"submit\" value=\"record\" class=\"button\">&nbsp;";
	
	$display_block .= "<a href=\"".FRONTPAGE."?opr=showmoth\">Cancel</a></p>";
	
#	$display_block .= "<input type=\"submit\" formaction=\"".RECORDARE."?action=torna_a_casa\" value=\"cancel\" class=\"button\"></p>";
	$display_block .= "</form>";

} 
elseif ($_GET['action'] == "do_add_mother") 
{
	
	$surgery_date = $_POST['year'].$_POST['month'].$_POST['day'];
	settype($surgery_date, "integer");
	$receipt_date = $_POST['rec_year'].$_POST['rec_month'].$_POST['rec_day'].$_POST['rec_hour'].$_POST['rec_min'];
	settype($receipt_date, "integer");
	$pfa_date = $_POST['pfa_year'].$_POST['pfa_month'].$_POST['pfa_day'].$_POST['pfa_hour'].$_POST['pfa_min'];
	settype($pfa_date, "integer");

	
	$sql = "insert into madre (name, surgery_date, comments) values (?, ?, ?)";
	$prepare = $db->prepare($sql);
	$prepare->bindValue(1, $_POST['madre_name'], PDO::PARAM_STR);
	$prepare->bindValue(2, $surgery_date, PDO::PARAM_INT);
	$prepare->bindValue(3, $_POST['comments'], PDO::PARAM_STR);
	$prepare->execute();
			
	$madre_id = $db->lastInsertId();
	
	for ($i=1;$i<=$_POST['howmany'];++$i) {
		if ($_POST['embryo_part'] == "whole") {
			$whole_embryo_sql = "insert into embryo (madre_id, name, part, age) values (?, ?, 'whole', ?)";

			$whole_prepare = $db->prepare($whole_embryo_sql);
			$whole_prepare->bindParam(1, $madre_id, PDO::PARAM_INT);
			$whole_prepare->bindParam(2, strval($madre_id.".".$i), PDO::PARAM_STR);
			$whole_prepare->bindParam(3, strval($_POST['age']), PDO::PARAM_STR);

			$whole_prepare->execute();
			$current_embryo_id = $db->lastInsertId();
					
			$operations_sql = "insert into operations (name, when_date, embryo_id) values (?, ?, ?)";

			$operations_prepare = $db->prepare($operations_sql);
					
			$operations_prepare->bindParam(1, strval("receipt"), PDO::PARAM_STR);
			$operations_prepare->bindParam(2, $receipt_date, PDO::PARAM_LOB);
			$operations_prepare->bindParam(3, $current_embryo_id, PDO::PARAM_INT);
			$operations_prepare->execute();

			$operations_prepare->bindParam(1, strval("pfa"), PDO::PARAM_STR);
			$operations_prepare->bindParam(2, $pfa_date, PDO::PARAM_LOB); # LOB because YYYYMMDDMMSS is too big to be an int, turned to float: not PDO supported
			$operations_prepare->bindParam(3, $current_embryo_id, PDO::PARAM_INT);
			$operations_prepare->execute();
		} elseif ($_POST['embryo_part'] == "body_head") {
			$embryo_sql = "insert into embryo (madre_id, name, part, age) values (?, ?, ?, ?)";
					
			$embryo_prepare = $db->prepare($embryo_sql);
					
			$embryo_prepare->bindParam(1, $madre_id, PDO::PARAM_INT);
			$embryo_prepare->bindParam(2, strval($madre_id.".".$i), PDO::PARAM_STR);
			$embryo_prepare->bindParam(3, strval("head"), PDO::PARAM_STR);
			$embryo_prepare->bindParam(4, strval($_POST['age']), PDO::PARAM_STR);

			$embryo_prepare->execute();
					
			$current_embryo_id = $db->lastInsertId();
					
			$operations_sql = "insert into operations (name, when_date, embryo_id) values (?, ?, ?)";

			$operations_prepare = $db->prepare($operations_sql);
					
			$operations_prepare->bindParam(1, strval("receipt"), PDO::PARAM_STR);
			$operations_prepare->bindParam(2, $receipt_date, PDO::PARAM_LOB);
			$operations_prepare->bindParam(3, $current_embryo_id, PDO::PARAM_INT);
			$operations_prepare->execute();

			$operations_prepare->bindParam(1, strval("pfa"), PDO::PARAM_STR);
			$operations_prepare->bindParam(2, $pfa_date, PDO::PARAM_LOB); # LOB because YYYYMMDDMMSS is too big to be an int, turned to float: not PDO supported
			$operations_prepare->bindParam(3, $current_embryo_id, PDO::PARAM_INT);
			$operations_prepare->execute();
					
			$embryo_prepare->bindParam(1, $madre_id, PDO::PARAM_INT);
			$embryo_prepare->bindParam(2, strval($madre_id.".".$i), PDO::PARAM_STR);
			$embryo_prepare->bindParam(3, strval("body"), PDO::PARAM_STR);
			$embryo_prepare->bindParam(4, strval($_POST['age']), PDO::PARAM_STR);

			$embryo_prepare->execute();
					
			$current_embryo_id = $db->lastInsertId();
					
			$operations_sql = "insert into operations (name, when_date, embryo_id) values (?, ?, ?)";

			$operations_prepare = $db->prepare($operations_sql);
					
			$operations_prepare->bindParam(1, strval("receipt"), PDO::PARAM_STR);
			$operations_prepare->bindParam(2, $receipt_date, PDO::PARAM_LOB);
			$operations_prepare->bindParam(3, $current_embryo_id, PDO::PARAM_INT);
			$operations_prepare->execute();

			$operations_prepare->bindParam(1, strval("pfa"), PDO::PARAM_STR);
			$operations_prepare->bindParam(2, $pfa_date, PDO::PARAM_LOB); # LOB because YYYYMMDDMMSS is too big to be an int, turned to float: not PDO supported
			$operations_prepare->bindParam(3, $current_embryo_id, PDO::PARAM_INT);
			$operations_prepare->execute();
		}
	}

//	echo "<pre>";
//	print_r($db->errorInfo());
//	echo "</pre>";

			
	header('Location: '.FRONTPAGE);

} 
elseif ($_GET['action'] == "mod_mother") // modify an existing mother record ** PASS madre_id
{ 
	header('Content-type: text/html; charset=utf-8');
	
	include_once("embryos.uni.class.php");
	require_once("date.class.php");
	$head = "review mother no.".$_GET['madre_id'];
	
	$extra_css .= "<link href=\"styles/imagePanelForm.css\" rel=\"stylesheet\" type=\"text/css\">";

	$madre = new madre($db, $_GET['madre_id']);
	$date = new date_pulldown();
	$date->load_compact_date($madre->madre_info['surgery_date']);
	
#	$retrieve_madre_sql = "select * from madre where id = ?";
#	$prepare = $db->prepare($retrieve_madre_sql);
#	$prepare->bindValue(1, $_GET['madre_id'], PDO::PARAM_INT);
#	$prepare->execute();
	
#	$madre = $prepare->fetch(PDO::FETCH_ASSOC);
	
	$display_block .= "<form id=\"mother_info\" action=\"".RECORDARE."?action=do_mod_mother\" method=\"POST\">\n";
	
	$display_block .= "<fieldset form=\"mother_info\">";
	$display_block .= "<legend>general info</legend>";
	
	$display_block .= "<input type=\"hidden\" name=\"subject\" value=\"madre\">";
	$display_block .= "<input type=\"hidden\" name=\"action\" value=\"update\">";
	$display_block .= "<input type=\"hidden\" name=\"madre_id\" value=\"".$madre->madre_id."\">";
	$display_block .= "<p>review name:<br>\n";
	$display_block .= "<input type=\"text\" name=\"madre_name\" size=\"48\" value=\"".$madre->madre_info['name']."\"></p>";
	

	$display_block .= "<p>review surgery date<br>";
	$display_block .= "year: ";
	$display_block .= $date->select_year();

	$display_block .= "month: ";
	$display_block .= $date->select_month();

	$display_block .= "day: ";
	$display_block .= $date->select_day();


	$display_block .= "<p>review comments<br>";
	$display_block .= "<textarea name=\"comments\">".$madre->madre_info['comments']."</textarea></p>\n";

	
#	$display_block .= "<input type=\"submit\" formaction=\"".FRONTPAGE."?opr=explore_mother&madre_id=".$madre->madre_id."\" value=\"cancel\" class=\"button\">&nbsp;";
	
#	$display_block .= "<input type=\"submit\" formaction=\"".RECORDARE."?action=del_mother\" value=\"delete mother\" class=\"button\">";


	$madre->fetchRelatedPics();
	
	if ($madre->allPictures == "not_found") {
		$group_description = "Add a picture";
		$group_picture_src = REL_ICONS_LOCUS."not_found.png";
		$group_picture_src_input = "";
		$group_or_indi = "group";
		$button = "add";
		$photo_id = 0;
		$short_description = "not found";
		

		
	} elseif (is_array($madre->allPictures) && count($madre->allPictures)>0) {
		

		
		$display_block .= "<p>Add another image:<br />";
		$display_block .= "<input type=\"text\" size=\"70\" name=\"img_location\" placeholder=\"file path\">\n";
		$display_block .= "<br />Short description:<br /><input type=\"text\" size=\"70\" name=\"img_desc\">\n";
		$display_block .= "</p>";
		
		$group_description = "F".$madre->allPictures[0][2]." ".$madre->allPictures[0][0];
		$group_picture_src = $group_picture_src_input = $madre->allPictures[0][1];
		$group_or_indi = $madre->allPictures[0][0];
		$short_description = $madre->allPictures[0][4];;
		$button = "alter";
		$photo_id = $madre->allPictures[0][3];
		$extras_in_header .= "<script src=\"https://d3js.org/d3.v5.min.js\"></script>\n";
		$extras_in_header .= "<script type=\"text/javascript\">var return_to='madre_".$madre->madre_id."'; var allPics = ".json_encode($madre->allPictures, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)."; </script>";
	
		$post_js .= "<script type=\"text/javascript\" src=\"picGallery.js\"></script>\n";
		$post_js .= "<script type=\"text/javascript\">const gallery = new formGallery(allPics, return_to); gallery.showForm();</script>";
		
	}

	
	$display_block .= "<input type=\"submit\" value=\"review\" class=\"button\">&nbsp;";
	
	$display_block .= "<a href=\"".FRONTPAGE."?opr=explore_mother&madre_id=".$madre->madre_id."\">[Cancel]</a>";
	
	
	$display_block .= "</fieldset>";
	$display_block .= "</form>";
	
	$display_block .= "<div id=\"ajaxResponse\"></div>";
	
	
	$div_id = "image_".$group_or_indi."_".$photo_id;
	$img_block .= "<div class=\"imageContainer\" id=\"".$div_id."\">";
	$img_block .= "<div class=\"imagePlace\">";
	$img_block .= "<img src=\"".$group_picture_src."\" alt=\"".$group_description."\" width=\"180px\">";
	$img_block .= "</div>";

	$img_block .= "<div class=\"formPlace\">";
	$img_block .= "<form action=\"actuatorPicture.ajax.php?opr=alter\" method=\"POST\">\n";

	$img_block .= "<input type=\"hidden\" name=\"photoKind\" value=\"".$group_or_indi."\">";
	$img_block .= "<input type=\"hidden\" name=\"pic_id\" value=\"".$photo_id."\">";
	$img_block .= "<input type=\"hidden\" name=\"return_to\" value=\"madre_".$madre->madre_id."\">";
	$img_block .= "<p>".$group_description.":</p>";
	$img_block .= "<p><input type=\"text\" size=\"70\" name=\"img_location\" value=\"".$group_picture_src_input."\" placeholder=\"File location\"></p>\n";
	$img_block .= "<p>Short description:<br /><input type=\"text\" size=\"70\" name=\"img_desc\" value=\"".$short_description."\"></p>\n";
	
	$img_block .= "<input type=\"submit\" value=\"".$button."\" class=\"button\">\n";
	$img_block .= "</form>";
	$img_block .= "<br /><button onclick=\"deletePicture('".$div_id."')\" class=\"button\">delete</button>";
	$img_block .= "</div>\n"; // formPlace 
	$img_block .= "</div>"; // imageContainer div



} 
elseif ($_GET['action'] == "do_mod_mother") 
{
	
#	echo "<pre>";
#	print_r($_POST);
#	echo "</pre>";
	
	$surgery_date = $_POST['year'].$_POST['month'].$_POST['day'];
	settype($surgery_date, "integer");


	$sql = "update madre set name=?, surgery_date=?, comments=? where id=?";
	$prepare = $db->prepare($sql);
	$prepare->bindValue(1, $_POST['madre_name'], PDO::PARAM_STR);
	$prepare->bindValue(2, $surgery_date, PDO::PARAM_LOB);
	$prepare->bindValue(3, $_POST['comments'], PDO::PARAM_STR);
	$prepare->bindValue(4, $_POST['madre_id'], PDO::PARAM_INT);
			
	$prepare->execute();

	
	$img_locus = image_path_tester($_POST['img_location']);
	if ($img_locus) {
		$sql2 = "insert into group_photos (madre_id, file_name, group_descr) values (?, ?, ?)";
		$prepare2 = $db->prepare($sql2);
		$prepare2->bindValue(1, $_POST['madre_id'], PDO::PARAM_INT);
		$prepare2->bindValue(2, $img_locus, PDO::PARAM_STR);
#		$prepare2->bindValue(2, $_POST['img_location'], PDO::PARAM_STR);
		$prepare2->bindValue(3, $_POST['img_desc'], PDO::PARAM_STR);
		
		$prepare2->execute();
	}

	

			
	header('Location: '.FRONTPAGE.'?opr=explore_mother&madre_id='.$_POST['madre_id']);


} 
elseif ($_GET['action'] == "mod_embryo") 
{
	
	include_once("embryos.uni.class.php");
	
/*	$retrieve_baby_sql = "select * from embryo where id = ?";
	$prepare = $db->prepare($retrieve_baby_sql);
	$prepare->bindValue(1, $_GET['embryo_id'], PDO::PARAM_INT);
	$prepare->execute();
	
	$baby = $prepare->fetch(PDO::FETCH_ASSOC);
*/
	$baby = new embryo($db, $_GET['embryo_id']);
	
	
	$head = "modify no.".$_GET['embryo_id'];
	$head .= "&nbsp;(F".$baby->embryo_name.")\n";
	
	
	$extra_css .= "<link href=\"styles/imagePanelForm.css\" rel=\"stylesheet\" type=\"text/css\">";
	
	$display_block .= "<form action=\"".RECORDARE."?action=do_mod_embryo\" method=\"POST\">\n";
	$display_block .= "<fieldset>\n<legend>embryo information</legend>\n";
	$display_block .= "<input type=\"hidden\" name=\"embryo_id\" value=\"".$baby->embryo_id."\">";
	$display_block .= "<input type=\"hidden\" name=\"return_to\" value=\"embryo_".$baby->embryo_id."\">";
	$display_block .= "<p>review crl (in mm):&nbsp;\n";
	$display_block .= "<input type=\"text\" name=\"baby_crl\" size=\"8\" value=\"".$baby->embryo_data['crl']."\"></p>\n";

	$display_block .= "<p>review comments<br>\n";
	$display_block .= "<textarea name=\"comments\">".$baby->embryo_data['comments']."</textarea></p>\n";

	
	$baby->get_embryo_picture();
	
	if ($baby->embryo_pictures == "not_found")
	{
		
		$group_description = "Add a picture";
		$group_picture_src = REL_ICONS_LOCUS."not_found.png";
		$group_picture_src_input = "";
#		$group_or_indi = "group";
		$button = "add";
		$photo_id = 0;
		$short_description = "not found";
		
		
	}
	elseif (is_array($baby->embryo_pictures) && count($baby->embryo_pictures)>0)
	{
		$extras_in_header .= "<script src=\"https://d3js.org/d3.v5.min.js\"></script>\n";
		$extras_in_header .= "<script type=\"text/javascript\">var return_to='embryo_".$baby->embryo_id."'; var allPics = ".json_encode($baby->embryo_pictures, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)."; </script>";
		
		$display_block .= "<p>Add another image:<br />";
		$display_block .= "<input type=\"text\" size=\"70\" name=\"img_location\" placeholder=\"file path\">\n";
		$display_block .= "<br />Short description:<br /><input type=\"text\" size=\"70\" name=\"img_desc\">\n";
		
		$display_block .= "</p>";
		
		$group_description = "F".$baby->embryo_pictures[0][2]." ".$baby->embryo_pictures[0][0];
		$group_picture_src = $group_picture_src_input = $baby->embryo_pictures[0][1];
#		$group_or_indi = $madre->allPictures[0][0];
		$short_description = $baby->embryo_pictures[0][4];;
		$button = "alter";
		$photo_id = $baby->embryo_pictures[0][3];
		
		$post_js .= "<script type=\"text/javascript\" src=\"picGallery.js\"></script>\n";
		$post_js .= "<script type=\"text/javascript\">const gallery = new formGallery(allPics, return_to); gallery.showForm();</script>";
	}

/*	
	if ( (!isset($baby->embryo_pictures)) or ($baby->embryo_pictures == FALSE) ) {
		$display_block .= "<p>add image<br>";
		$display_block .= "<input type=\"text\" size=\"70\" name=\"img_location\"></p>";
	} else {
		
		$display_block .= "<p>modify image<br>";
		$display_block .= "<input type=\"text\" size=\"70\" name=\"img_location\" value=\"".$baby->embryo_picture."\"></p>";
		
		$img_block .= "<img alt=\"".$baby->embryo_name."\" src=\"".$baby->embryo_picture."\" width=\"180px\">";
	}
*/	
	
	$display_block .= "<input type=\"submit\" value=\"update\" class=\"button\">&nbsp;";
#	$display_block .= "<input type=\"submit\" formaction=\"".FRONTPAGE."?opr=showem&name=".$_GET['embryo_id']."\" value=\"cancel\" class=\"button\">&nbsp;";
	$display_block .= "<a href=\"".FRONTPAGE."?opr=showem&name=".$baby->embryo_id."\">[Cancel]</a>";

	$display_block .= "</fieldset>\n";
	$display_block .= "</form>\n";
	
	$display_block .= "<div id=\"ajaxResponse\"></div>";
	
	
	$div_id = "image_embryo_".$photo_id;
	$img_block .= "<div class=\"imageContainer\" id=\"".$div_id."\">";
	$img_block .= "<div class=\"imagePlace\">";
	$img_block .= "<img src=\"".$group_picture_src."\" alt=\"".$group_description."\" width=\"180px\">";
	$img_block .= "</div>";

	$img_block .= "<div class=\"formPlace\">";
	$img_block .= "<form action=\"actuatorPicture.ajax.php?opr=alter\" method=\"POST\">\n";

	$img_block .= "<input type=\"hidden\" name=\"photoKind\" value=\"embryo\">";
	$img_block .= "<input type=\"hidden\" name=\"pic_id\" value=\"".$photo_id."\">";
	$img_block .= "<input type=\"hidden\" name=\"return_to\" value=\"embryo_".$baby->embryo_id."\">"; // madre_id as name is retained for compatibility with ajax response
	$img_block .= "<p>".$group_description.":</p>";
	$img_block .= "<p><input type=\"text\" size=\"70\" name=\"img_location\" value=\"".$group_picture_src_input."\" placeholder=\"File location\"></p>\n";
	$img_block .= "<p>Short description:<br /><input type=\"text\" size=\"70\" name=\"img_desc\" value=\"".$short_description."\"></p>\n";
	
	$img_block .= "<input type=\"submit\" value=\"".$button."\" class=\"button\">\n";
	$img_block .= "</form>";
	$img_block .= "<br /><button onclick=\"deletePicture('".$div_id."')\" class=\"button\">delete</button>";
	$img_block .= "</div>\n"; // formPlace 
	$img_block .= "</div>"; // imageContainer div
	
	

} 
elseif ($_GET['action'] == "do_mod_embryo") 
{
	
	$sql = "update embryo set comments=?, crl=? where id=?";
	$prepare = $db->prepare($sql);
	$prepare->bindValue(1, $_POST['comments'], PDO::PARAM_STR);
	$prepare->bindValue(2, $_POST['baby_crl'], PDO::PARAM_STR);
	$prepare->bindValue(3, $_POST['embryo_id'], PDO::PARAM_INT);
	$prepare->execute();
	
	
	$img_locus = image_path_tester($_POST['img_location']);
	if ($img_locus) {
		$img_sql = "insert into embryo_photos (embryo_id, file_name, embryo_pic_descr) values (?, ?, ?)";
		$img_prepare = $db->prepare($img_sql);
		$img_prepare->bindValue(1, $_POST['embryo_id'], PDO::PARAM_INT);
		$img_prepare->bindValue(2, $img_locus, PDO::PARAM_STR);
#		$img_prepare->bindValue(2, $_POST['img_location'], PDO::PARAM_STR);
		$img_prepare->bindValue(3, $_POST['img_desc'], PDO::PARAM_STR);
		$img_prepare->execute();
	}
	
	header('Location: '.FRONTPAGE.'?opr=showem&name='.$_POST['embryo_id']);

#	echo "<pre>";
#	var_dump($_POST);
#	echo gettype($_POST['baby_crl']);
#	echo "</pre>";

} 
elseif ($_GET['action'] == "del_embryo") 
{
	include_once("embryos.uni.class.php");
	
/*	$retrieve_baby_sql = "select * from embryo where id = ?";
	$prepare = $db->prepare($retrieve_baby_sql);
	$prepare->bindValue(1, $_GET['embryo_id'], PDO::PARAM_INT);
	$prepare->execute();
	
	$baby = $prepare->fetch(PDO::FETCH_ASSOC);
*/
	$baby = new embryo($db, $_GET['embryo_id']);
	
	
	$extras_in_header .= "<script src=\"https://kit.fontawesome.com/b236ea9bb0.js\" crossorigin=\"anonymous\"></script>";
	
	$head = "delete embryo F".$baby->embryo_name." (no.".$_GET['embryo_id'].")\n";
	
	$display_block .= "Are you sure ?<br /><br />";
	
	$display_block .= "  <a href=\"".RECORDARE."?action=do_del_embryo&embryo_id=".$baby->embryo_id."\"><i class=\"far fa-trash-alt\"></i>Yes, delete</a> ";
	
	$display_block .= "  <a href=\"".FRONTPAGE."?opr=showem&embryo_id=".$baby->embryo_id."\" class=\"deleteButton\">No! I've made up my mind</a>";
	
	$baby = null;



}
elseif ($_GET['action'] == "do_del_embryo") 
{
	include_once("embryos.uni.class.php");
	
	$baby = new embryo($db, $_GET['embryo_id'], "ON");
	$baby->relatedSlides();
	$eraser = new eraser($db, $_GET['embryo_id'], "OFF");
	
	
	if ($baby->cut_status === "cut")
	{
		

		if ((count($baby->embryo_slides) == 1) && ($baby->embryo_slides[0] == "ZERP") )
		{
			echo "empty";
		}
		else
		{
			
			
			if ($baby->sections_count > 0)
			{
				$eraser->deleteSections();
			}
					

			$eraser->deleteSlides();
#			echo "<pre>";
#			var_dump($slides->slideIdsArray);
#			echo "</pre>";
		}
		
		
		
	}

	$test = $eraser->deleteEmbryo();
#	echo $test;
	$madre_id = $baby->embryo_data['madre_id'];
	$eraser = null;
	$baby = null;
	header('Location: '.FRONTPAGE.'?opr=explore_mother&madre_id='.$madre_id);
	
	
}
elseif ($_GET['action'] == "cut_embryo") // modify an existing embryo record ** PASS embryo_id
{ 

	header('Content-type: text/html; charset=utf-8');

	
	
	$retrieve_baby_sql = "select * from embryo where id = ?";
	$prepare = $db->prepare($retrieve_baby_sql);
	$prepare->bindValue(1, $_GET['embryo_id'], PDO::PARAM_INT);
	$prepare->execute();
	
	$baby = $prepare->fetch(PDO::FETCH_ASSOC);

	if ( ($baby['set_width'] > 1) or ($baby['set_width'] > 1) ) {
		
		$head = "attention!";
		
		$display_block .= "F".$baby['name']."&nbsp; can not be modifed. configuration already exists!<br>";
		$display_block .= "<a href=\"".FRONTPAGE."?opr=showem&name=".$_GET['embryo_id']."\">Click here to go back</a>";
		
	} else {

		$retrieve_babyop_sql = "select * from operations where embryo_id = ?";
		$prepare_op = $db->prepare($retrieve_babyop_sql);
		$prepare_op->bindValue(1, $_GET['embryo_id'], PDO::PARAM_INT);
		$prepare_op->execute();
	
		$baby_op = $prepare_op->fetchAll(PDO::FETCH_ASSOC);

	
#	echo "<pre>";
#	print_r($baby);
#	print_r($baby_op);
#	echo "</pre>";

		$head = "modify no.".$_GET['embryo_id'];
		$head .= "&nbsp;(F".$baby['name'].")\n";
	
		
		require_once("date.class.php");
		$date = new date_pulldown();
		
	
		$display_block .= "<form action=\"".RECORDARE."?action=do_cut_embryo\" method=\"POST\">\n";
		$display_block .= "<fieldset>\n<legend>embryo ".$baby['part']." information</legend>\n";
		$display_block .= "<input type=\"hidden\" name=\"embryo_id\" value=\"".$baby['id']."\">";
#		$display_block .= "<p>review name:<br>\n";
#		$display_block .= "<input type=\"text\" name=\"baby_name\" size=\"48\" value=\"".$baby['name']."\"></p>";
		$display_block .= "<p>review cut:<br>\n";
		$display_block .= "<input type=\"text\" name=\"baby_cut\" size=\"48\" value=\"".$baby['cut']."\"></p>";
		$display_block .= "<p>review set width:&nbsp;\n";
		$display_block .= "<input type=\"text\" name=\"set_width\" size=\"2\" maxlength=\"2\" value=\"".$baby['set_width']."\">&nbsp;\n";
		$display_block .= "height: <input type=\"text\" name=\"set_height\" size=\"2\" maxlength=\"2\" value=\"".$baby['set_height']."\"></p>";
		
		$display_block .= "<p> Cut year:&nbsp;";
		$display_block .= $date->select_year("cut_year");
#		$display_block .= "<input type=\"text\" name=\"cut_year\" size=\"4\" maxlength=\"4\">&nbsp;";
		
		$display_block .= "cut month:&nbsp;";
		$display_block .= $date->select_month("cut_month");
#		$display_block .= "<input type=\"text\" name=\"cut_month\" size=\"2\" maxlength=\"2\">&nbsp;";

		$display_block .= "cut day:&nbsp;";
		$display_block .= $date->select_day("cut_day");
#		$display_block .= "<input type=\"text\" name=\"cut_day\" size=\"2\" maxlength=\"2\"></p>\n";
	
		$display_block .= "<p>review comments<br>";
		$display_block .= "<textarea name=\"comments\">".$baby['comments']."</textarea></p>";
		$display_block .= "<input type=\"submit\" value=\"update\" class=\"button\">&nbsp;";
		$display_block .= "<input type=\"submit\" formaction=\"".FRONTPAGE."?opr=showem&name=".$_GET['embryo_id']."\" value=\"cancel\" class=\"button\">&nbsp;";
#		$display_block .= "<input type=\"submit\" formaction=\"".RECORDARE."?action=del_embryo\" value=\"delete embryo\" class=\"button\">";
		$display_block .= "</fieldset>\n";
		$display_block .= "</form>\n";
	


	}

} 
elseif ($_GET['action'] == "do_cut_embryo") 
{
	
	if ( ($_POST['set_width'] < 1) or ($_POST['set_height'] < 1) ) { // in case we did not cut the embryo
				
		$sql = "update embryo set comments=? where id=?";
		$prepare = $db->prepare($sql);
		$prepare->bindValue(1, $_POST['comments'], PDO::PARAM_STR);
		$prepare->bindValue(2, $_POST['embryo_id'], PDO::PARAM_INT);
		$prepare->execute();
				
	} else { // in case we did
				// update embryo info
		$sql = "update embryo set cut=?, comments=?, set_width=?, set_height=? where id=?";
		$prepare = $db->prepare($sql);
		$prepare->bindValue(1, $_POST['baby_cut'], PDO::PARAM_STR);
		$prepare->bindValue(2, $_POST['comments'], PDO::PARAM_STR);
		$prepare->bindValue(3, $_POST['set_width'], PDO::PARAM_INT);
		$prepare->bindValue(4, $_POST['set_height'], PDO::PARAM_INT);
		$prepare->bindValue(5, $_POST['embryo_id'], PDO::PARAM_INT);
		$prepare->execute();
				
		// update table 'operations'
		$sectioning_date = $_POST['cut_year'].$_POST['cut_month'].$_POST['cut_day']."0900";
		settype($sectioning_date, "integer");
			
		$sql2 = "insert into operations (name, when_date, embryo_id) values ('sectioning', ?, ?)";
		$prepare2 = $db->prepare($sql2);
		$prepare2->bindValue(1, $sectioning_date, PDO::PARAM_LOB);
		$prepare2->bindValue(2, $_POST['embryo_id'], PDO::PARAM_INT);
		$prepare2->execute();
			
		// update table 'slide'
		
		try {
			$db->beginTransaction();
			$sql_slide = "insert into slide (embryo_id, name, slide_position, cut_date) values (?, ?, ?, ?)";
			$slide_prepare = $db->prepare($sql_slide);			
			
			for ($h=0; $h<$_POST['set_height']; ++$h) {
				for ($w=1; $w<=$_POST['set_width']; ++$w) {
					$slide_id = $w + $_POST['set_width']*$h;
					$lettering = $header_row[$w].($h+1);
						
					$parameters = array($_POST['embryo_id'], $lettering, $slide_id, $sectioning_date);
					$slide_prepare->execute($parameters);
						
				}
			}
			
			$db->commit();
			
			header('Location: '.FRONTPAGE.'?opr=showem&name='.$_POST['embryo_id']);
			
			echo "<a href=\"".FRONTPAGE."?opr=showem&name=".$_POST['embryo_id']."\">Click to redirect</a>\n";
			
		} catch (Exception $e) {
			$db->rollback;
			throw $e;
			
		}
				
/*		for ($h=0; $h<$_POST['set_height']; ++$h) {
			for ($w=1; $w<=$_POST['set_width']; ++$w) {
				$slide_id = $w + $_POST['set_width']*$h;
				$lettering = $header_row[$w].($h+1);
						
				$sql_slide = "insert into slide (embryo_id, name, slide_position, cut_date) values (?, ?, ?, ?)";
				$slide_prepare = $db->prepare($sql_slide);
				$slide_prepare->bindValue(1, $_POST['embryo_id'], PDO::PARAM_INT);
				$slide_prepare->bindValue(2, $lettering, PDO::PARAM_STR);
				$slide_prepare->bindValue(3, $slide_id, PDO::PARAM_INT);
				$slide_prepare->bindValue(4, $sectioning_date, PDO::PARAM_LOB);
				$slide_prepare->execute();
						
			}
		} */
				
	}			
			
			
#	echo "<pre>";
#	print_r($prepare2->errorInfo());
#	echo "</pre>";
		
	

} 
elseif ($_GET['action'] == "add_oper") 
{
	$head = "what have you done?";
	
	require_once("date.class.php");
	$date = new date_pulldown();
	
	$display_block .= "<form action=\"".RECORDARE."?action=do_add_oper\" method=\"POST\">\n";
	$display_block .= "<fieldset>\n<legend>add operation</legend>\n";
	$display_block .= "<input type=\"hidden\" name=\"embryo_id\" value=\"".$_GET['embryo_id']."\">";
	$display_block .= "<p>operation:\n";
	$display_block .= "<input type=\"text\" name=\"name\" size=\"35\"></p>\n";
	$display_block .= "<p>operation date<br>";
	$display_block .= "year: ";
	$display_block .= $date->select_year("when_year");
	$display_block .= "month: ";
	$display_block .= $date->select_month("when_month");
	$display_block .= "day: ";
	$display_block .= $date->select_day("when_day");
	$display_block .= "hour: ";
	$display_block .= $date->select_hour("when_hour");
	$display_block .= "min: ";
	$display_block .= $date->select_minute("when_min");
	$display_block .= "<p>comments:\n";
	$display_block .= "<input type=\"text\" name=\"comments\" size=\"35\"></p>\n";
	$display_block .= "<p><input type=\"submit\" value=\"add\" class=\"button\">\n";
	$display_block .= "<input type=\"submit\" formaction=\"".FRONTPAGE."?opr=showem&name=".$_GET['embryo_id']."\" value=\"cancel\" class=\"button\"></p>";
	$display_block .= "</fieldset>\n";
	$display_block .= "</form>";

} 
elseif ($_GET['action'] == "do_add_oper") 
{
	
	$when_date = $_POST['when_year'].$_POST['when_month'].$_POST['when_day'].$_POST['when_hour'].$_POST['when_min'];
	settype($when_date, "integer");
	
	require_once("embryos.uni.class.php");
	$embryo = new embryo ($db, $_POST['embryo_id'], "OFF");
	
	$embryo->addOperation ($_POST['name'], $when_date, $_POST['comments']);
	
	header('Location: '.FRONTPAGE.'?opr=showem&name='.$_POST['embryo_id']);
			
	echo "<a href=\"".FRONTPAGE."?opr=showem&name=".$_POST['embryo_id']."\">Click to redirect</a>\n";

} 
elseif ($_GET['action'] == "mod_oper") 
{
	
	$head .= "modify operation";
	
	$sql = "select * from operations where id=?";
	$prepare = $db->prepare($sql);
	$prepare->bindValue(1, $_GET['oper_id'], PDO::PARAM_INT);
	$prepare->execute();
	$operation = $prepare->fetch(PDO::FETCH_ASSOC);
	
	require_once("date.class.php");
	$date = new date_pulldown();
	$date->load_compact_date($operation['when_date']);
	
	$display_block .= "<form action=\"".RECORDARE."?action=do_mod_oper\" method=\"POST\">\n";
	$display_block .= "<fieldset>\n<legend>operation</legend>\n";
	$display_block .= "<input type=\"hidden\" name=\"operation_id\" value=\"".$_GET['oper_id']."\">";
	$display_block .= "<input type=\"hidden\" name=\"embryo_id\" value=\"".$operation['embryo_id']."\">";
	$display_block .= "<p>operation:\n";
	$display_block .= "<input type=\"text\" name=\"name\" size=\"35\" value=\"".$operation['name']."\"></p>\n";
	$display_block .= "<p>operation date<br>";
	$display_block .= "year: ";
	$display_block .= $date->select_year("when_year");
	$display_block .= "month: ";
	$display_block .= $date->select_month("when_month");
	$display_block .= "day: ";
	$display_block .= $date->select_day("when_day");
	$display_block .= "hour: ";
	$display_block .= $date->select_hour("when_hour");
	$display_block .= "min: ";
	$display_block .= $date->select_minute("when_min");

	$display_block .= "<p>comments:\n";
	$display_block .= "<input type=\"text\" name=\"comments\" size=\"35\" value=\"".$operation['comments']."\"></p>\n";
	$display_block .= "<p><input type=\"submit\" value=\"alter\" class=\"button\">\n";
	$display_block .= "<input type=\"submit\" formaction=\"".RECORDARE."?action=del_oper&embryo_id=".$operation['embryo_id']."&oper_id=".$_GET['oper_id']."\" value=\"delete\" class=\"button\">&nbsp;";
	$display_block .= "<input type=\"submit\" formaction=\"".FRONTPAGE."?opr=showem&name=".$operation['embryo_id']."\" value=\"cancel\" class=\"button\"></p>";
	$display_block .= "</fieldset>\n";
	$display_block .= "</form>";

#echo "<pre>";
#print_r($operation);
#print_r($prepare->errorInfo());
#print_r($prepare);
#echo "</pre>";

} 
elseif ($_GET['action'] == "do_mod_oper") 
{
	
	$sql = "update operations set name=?, when_date=?, comments=? where id=?";
	$prepare = $db->prepare($sql);
	$prepare->bindValue(1, $_POST['name'], PDO::PARAM_STR);
	
	$when_date = $_POST['when_year'].$_POST['when_month'].$_POST['when_day'].$_POST['when_hour'].$_POST['when_min'];
	settype($when_date, "integer");
	$prepare->bindValue(2, $when_date, PDO::PARAM_LOB);
	$prepare->bindValue(3, $_POST['comments'], PDO::PARAM_STR);
	$prepare->bindValue(4, $_POST['operation_id'], PDO::PARAM_INT);
	$prepare->execute();
	
	header('Location: '.FRONTPAGE.'?opr=showem&name='.$_POST['embryo_id']);
			
	echo "<a href=\"".FRONTPAGE."?opr=showem&name=".$_POST['embryo_id']."\">Click to redirect</a>\n";

#echo "<pre>";
#print_r($_POST);
#print_r($prepare->errorInfo());
#echo "</pre>";

} 
elseif ($_GET['action'] == "del_oper") 
{
	
	$sql = "delete from operations where id=?";
	$prepare = $db->prepare($sql);
	$prepare->bindValue(1, $_GET['oper_id'], PDO::PARAM_INT);
	$prepare->execute();
	
	
	
	header('Location: '.FRONTPAGE.'?opr=showem&name='.$_GET['embryo_id']);
			
	echo "<a href=\"".FRONTPAGE."?opr=showem&name=".$_GET['embryo_id']."\">Click to redirect</a>\n";
	
} 
elseif ($_GET['action'] == "add_slide") 
{
	
	$head = "modify slide";
	

	$sql = "select slide.name as slide, slide.id, slide.experiment_date, slide.gene, slide.status, slide.cut_date, embryo.name as embryo, embryo.id as embryo_id from slide inner join embryo on embryo.id=slide.embryo_id where slide.id=?";
	$prepare = $db->prepare($sql);
	$prepare->bindValue(1, $_GET['slide_id'], PDO::PARAM_INT);
	$prepare->execute();
	$slide = $prepare->fetch(PDO::FETCH_ASSOC);
	
	$sql2 = "select id, gene_name, solution_book from gene order by solution_book asc";
	$prepare2 = $db->prepare($sql2);
	$prepare2->execute();
	$genes = $prepare2->fetchAll(PDO::FETCH_ASSOC);
	
#echo "<pre>";
#print_r($slide);
#echo "</pre>";
	$display_block .= "<h3>F".$slide['embryo']." slide ".$slide['slide']."</h3>";
	$display_block .= "<form action=\"".RECORDARE."?action=do_add_slide\" method=\"POST\">\n";
	$display_block .= "<fieldset>\n<legend>slide information</legend>\n";
	$display_block .= "<input type=\"hidden\" name=\"slide_id\" value=\"".$slide['id']."\">";
	$display_block .= "<input type=\"hidden\" name=\"embryo_id\" value=\"".$slide['embryo_id']."\">";
	$display_block .= "<p>gene:\n";
		

	$display_block .= "<select name=\"gene\">\n";
	if ($slide['gene'] < 1) {
		$display_block .= "<option value=\"0\" selected disabled>-select gene-</option>\n";
	}
	foreach ($genes as $gene) {
		$display_block .= "<option value=\"".$gene['id']."\"";
		if ($slide['gene'] == $gene['id']) {
			$display_block .= " selected";
		}
			
		$display_block .= ">".$gene['solution_book']." - ".$gene['gene_name']."</option>\n";
	}
	
	$display_block .= "</select></p>\n";

	require_once("date.class.php");
	
	$date = new date_pulldown();
	$date->load_compact_date($slide['experiment_date']);

	$display_block .= "<p>experiment date<br>";
	$display_block .= "year: ";
	$display_block .= $date->select_year("exp_year");

	$display_block .= "month: ";
	$display_block .= $date->select_month("exp_month");

	$display_block .= "day: ";
	$display_block .= $date->select_day("exp_day");

	$display_block .= "hour: ";
	$display_block .= $date->select_hour("exp_hour");

	$display_block .= "min: ";
	$display_block .= $date->select_minute("exp_min");


	$date->load_compact_date($slide['cut_date']);
	$display_block .= "<p>cut date<br>";
	$display_block .= "year: ";
	$display_block .= $date->select_year("cut_year");

	$display_block .= "month: ";
	$display_block .= $date->select_month("cut_month");

	$display_block .= "day: ";
	$display_block .= $date->select_day("cut_day");

	$display_block .= "hour: ";
	$display_block .= $date->select_hour("cut_hour");

	$display_block .= "min: ";
	$display_block .= $date->select_minute("cut_min");

	$display_block .= "<p>status (fail, OK etc):\n";
	$display_block .= "<input type=\"text\" name=\"status\" size=\"35\" value=\"".$slide['status']."\"></p>\n";
	$display_block .= "<p><input type=\"submit\" value=\"record\" class=\"button\">\n";
	$display_block .= "<input type=\"submit\" formaction=\"".FRONTPAGE."?opr=showem&name=".$slide['embryo_id']."\" value=\"cancel\" class=\"button\"></p>";
	$display_block .= "</fieldset>\n";
	$display_block .= "</form>";
	
} 
elseif ($_GET['action'] == "do_add_slide") 
{

	$experiment_date = $_POST['exp_year'].$_POST['exp_month'].$_POST['exp_day'].$_POST['exp_hour'].$_POST['exp_min'];
	settype($experiment_date, "float");
	$cut_date = $_POST['cut_year'].$_POST['cut_month'].$_POST['cut_day'].$_POST['cut_hour'].$_POST['cut_min'];
	settype($cut_date, "float");
	
	$sql = "update slide set experiment_date=?, gene=?, status=?, cut_date=? where id=?";
	$prepare = $db->prepare($sql);
	$prepare->bindValue(1, $experiment_date, PDO::PARAM_LOB);
	$prepare->bindValue(2, $_POST['gene'], PDO::PARAM_STR);
	$prepare->bindValue(3, $_POST['status'], PDO::PARAM_STR);
	$prepare->bindValue(4, $cut_date, PDO::PARAM_LOB);
	$prepare->bindValue(5, $_POST['slide_id'], PDO::PARAM_INT);

	$prepare->execute();
		
	header('Location: '.FRONTPAGE.'?opr=showem&name='.$_POST['embryo_id']);
			
	echo "<a href=\"".FRONTPAGE."?opr=showem&name=".$_POST['embryo_id']."\">Click to redirect</a>\n";

#echo "<pre>";
#print_r($_POST);
#print_r($prepare->errorInfo());
#echo "</pre>";

} 
elseif ($_GET['action'] == "record_experiment") 
{
#	echo "<pre>";
#	print_r($_POST);
	#print_r($prepare->errorInfo());
#	echo "</pre>";
	
	if ( ($_POST['year'] == "YEAR") || ($_POST['month'] == "month") || ($_POST['day'] == "day")) {
		header('Location: viewBasket.php?opr=showBasket&alert=date');
		echo "<p>no valid date provided. <a href=\"viewBasket.php?opr=showBasket&alert=date\">click to go back</a></p>";
	} else {
	
		$year = "";
		$month = "";
		$day = "";
		$hour_min = 1700;
		$slides = array();
	
		foreach ($_POST as $key => $val) {
			
			$key_starter = substr($key, 0, 3);
			
			if ($key_starter == "yea") {
				$year = $val;
			} elseif ($key_starter == "mon") {
				$month = sprintf("%'.02d", $val);
			} elseif ($key_starter == "day") {
				$day = sprintf("%'.02d", $val);
			} elseif ($key_starter == "sli") {
				$slide_id_key = explode("_", $key);
				$slides[$slide_id_key[1]] = $val;
			} elseif ($key_starter == "sta") {
				$slide_id_key = explode("_", $key);
				$status[$slide_id_key[1]] = $val;
			}
		}
	
		$date = $year.$month.$day.$hour_min;
		settype($date, "float");
	#	unset($_POST);

#			echo "<pre>";
#			print_r($_POST);
#print_r($slides);
#print_r($status);
			#print_r($prepare->errorInfo());
#			echo "</pre>";
	
		try {
			
			$db->beginTransaction();
			
			$sql = "update slide set experiment_date=?, gene=?, status=? where id=?";
			$prep = $db->prepare($sql);
			
			foreach ($slides as $key => $val) {
				
				$params = array($date, $val, $status[$key], $key);
				$prep->execute($params);


#				$sql = "update slide set experiment_date=?, gene=?, status=? where id=?";
#				$prep = $db->prepare($sql);
#				$prep->bindValue(1, $date, PDO::PARAM_LOB);
#				$prep->bindValue(2, $val, PDO::PARAM_INT);
#				$prep->bindValue(3, $status[$key], PDO::PARAM_STR);
#				$prep->bindValue(4, $key, PDO::PARAM_INT);
				
#				$prep->execute();
			}
			
			$db->commit();
			
			header('Location: '.FRONTPAGE.'?opr=exper&showDate='.$date);
		
		} catch (Exception $e) {
			
			$db->rollback();
			
			echo "Exception caught!<br />";
			throw $e;
			die;
		
		}
	
		$db = null;
		unset($_SESSION['addedSlides']);
#		header('Location: '.FRONTPAGE.'?opr=exper&showDate='.$date);
	}

#echo "<pre>";
#echo $date."<br>";
#print_r($slides);
#print_r($prepare->errorInfo());
#echo "</pre>";

} 
elseif ($_GET['action'] == "mod_age") 
{
	
	if (!isset($_GET['madre_id']) || !is_numeric($_GET['madre_id']) || !is_numeric($_POST['new_age'])) {
		echo "No valid mother id provided";
		$db=null;
		die;
	}
	
	try {
		
		$db->beginTransaction();
		
		$sql = "update embryo set age=? where madre_id=?";
		
		$prepare = $db->prepare($sql);
		$prepare->bindValue(1, $_POST['new_age'], PDO::PARAM_INT);
		$prepare->bindValue(2, $_GET['madre_id'], PDO::PARAM_INT);
		
		$prepare->execute();
		
		$db->commit();
		
		header('Location: '.FRONTPAGE.'?opr=explore_mother&madre_id='.$_GET['madre_id']);
		
	} catch (Exception $e) {
		
		$db->rollback();
		
		echo "Exception caught!<br />";
		throw $e;
		die;
	}
	
#	echo "<pre>";
#	print_r($_GET);
#	print_r($_POST);
#	echo "</pre>";
	

}
elseif ($_GET['action'] == "add_row") 
{
	if (!is_numeric($_GET['embryo_id'])) {
		
		$display_block .= "<p>Invalid embryo id<br />";
		$display_block .= "<a href=\"".FRONTPAGE."\">return</a></p>";
		
	} else {
		
		require_once("date.class.php");
		$date = new date_pulldown();
		
		$head = "add a row";
		
		
		$display_block .= "<form method=\"POST\" action=\"".RECORDARE."?action=do_add_row\">";
		$display_block .= "<input type=\"hidden\" name=\"embryo_id\" value=\"".$_GET['embryo_id']."\">";
		$display_block .= "<h2>Select date:</h2>";
		$display_block .= "<div id=\"date_selector_small\">";
		$display_block .= "<div id=\"select_year\">";
		$display_block .= $date->select_year();
		$display_block .= "</div>";

		$display_block .= "<div id=\"select_month\">";
		$display_block .= $date->select_month();
		$display_block .= "</div>";
		
		$display_block .= "<div id=\"select_day\">";
		$display_block .= $date->select_day();
		$display_block .= "</div>";
		$display_block .= "</div>";
		
		$display_block .= "<p>Comments:<br>";
		$display_block .= "<textarea name=\"comments\"></textarea></p>\n";
		
		$display_block .= "<input type=\"submit\" class=\"button\" value=\"Add\">";
		$display_block .= " <a href=\"".FRONTPAGE."?opr=showem&name=".$_GET['embryo_id']."\">cancel</a>";
		$display_block .= "</form>";
		
		
	}
	
#	$embryo = new embryo($db, $_GET['embryo_id']);
	

}
elseif ($_GET['action'] == "do_add_row")
{
#	echo "<pre>";
#	print_r($_POST);
#	echo "</pre>";
	
	$when_date = $_POST['year'].$_POST['month'].$_POST['day']."0900";
	settype($when_date, "integer");
	
	require_once("embryos.uni.class.php");
	$embryo = new embryo ($db, $_POST['embryo_id'], "OFF");
	
	$embryo->addSlideRow ($when_date, 1, $_POST['comments']);
	
	header('Location: '.FRONTPAGE.'?opr=showem&name='.$_POST['embryo_id']);
	
	echo "<a href=\"".FRONTPAGE."?opr=showem&name=".$_POST['embryo_id']."\">Click to redirect</a>\n";
	
	
}
elseif ($_GET['action'] == "torna_a_casa") 
{ // Torna a casa Paperino!!!
	header('Location: '.FRONTPAGE);
} 
else {
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
<?php echo $extra_css; ?>

<link rel="shortcut icon" type="image/x-icon" sizes="16x16" href="favicon.ico"/>
<link rel="shortcut icon" type="image/png" sizes="32x32" href="favicon32.png"/>
<link rel="apple-touch-icon" type="image/png" sizes="152x152" href="favicon152.png"/>
<title>Recordare</title>

<?php echo $extras_in_header; ?>

</head>


<body>
	<div class="container">

		<div class="into">

			<div class="bind"></div>

			<div class="facultyid"><h2><?php echo $head; ?></h2></div>
			<div class="bind"></div>

			<div class="text">
				<?php echo $display_block; ?>
			</div>
			
			<div class="row">
				<?php echo $img_block; ?>
			</div>

			<div class="footer"><?php echo $footer; ?></div>
		</div>
	</div>

<?php echo $post_js; ?>
</body>
</html>