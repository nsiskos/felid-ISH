<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', true);

session_start();
header('Content-type: text/html; charset=utf-8');
require_once("standards.php");

$pageTitle = "Slide";
$up_block = "";
$display_block = "";
$pic_block="";
$head = "";

$columns = 4;
# this page requires user permission al least:
$page_permission = 2;

$now = time();
$difference = $now - $_SESSION['time'];

if ( !isset($_SESSION['time']) or ($difference > $temporal_allowance) ) { // this var is found inside standards.php

	//go to login page
	
	header('Location: login.php?opr=login&fm=front');
	die;
}

# this page requires user permission at least = 2
if ( ($_SESSION['user_data']['perm'] < $page_permission) or (!isset($_SESSION['user_data']['perm'])) ) {
	echo "You do not have sufficient priviledges to access this page!</br>";
	echo "<a href=\"frontpage.php\">Click here</a>";
	die;
}

require_once("db_handle.php");
require_once("embryos.uni.class.php");


if ($_GET['opr'] == "select") 
{
	
	
	if ($_GET['slide_id'] < 1) {
		echo "You have not provided a valid slide_id!";
		die;
	}
	
	$sections = new sections($db, $_GET['slide_id'], "OFF");
	
	
	$head = "slides block"; 
	$up_block = $sections->show_upblock($_GET['opr']);
	
	$pageTitle = "F".$sections->embryo_info['embryo_name']." ".$sections->embryo_info['slide_name']." ".$sections->embryo_info['gene_name']." (".$sections->embryo_info['solution_book'].") sections";
	
	if ($sections->sections == 0) {
		$display_block .= "There are no sections registered. You may want to <a href=\"sections.php?opr=add_raw_form&slide_id=".$_GET['slide_id']."\">add</a>.<br>\n";
		
		$pageTitle .= " add";
		
	} else {
		$pic_block .= $sections->section_table($columns);
	}
	
#echo "<pre>";
#print_r($sections);
#echo "</pre>";


} 
elseif ($_GET['opr'] == "multiple_add") {
	
	$existing = new sections($db, $_GET['slide_id'], "OFF");
	$head = "slides details";
	$up_block = $existing->show_upblock($_GET['opr']);
	
	$pageTitle = "F".$existing->embryo_info['embryo_name']." ".$existing->embryo_info['slide_name']." ".$existing->embryo_info['gene_name']." (".$existing->embryo_info['solution_book'].") add sections";
	
	for ($i=0; $i<=$existing->max_sections; ++$i) {
		$display_block .= $existing->showImg($i);
	}
	

	
#	echo "<pre>";
#	print_r($existing->sections);
#	print_r($existing->max_sections);
#	echo "</pre>";
	
	
} 
elseif ($_GET['opr'] == "add_raw_form") {
	
	$head = "add massively";
	
	$sections = new sections($db, $_GET['slide_id'], "OFF");
	
	$pageTitle = "F".$sections->embryo_info['embryo_name']." ".$sections->embryo_info['slide_name']." ".$sections->embryo_info['gene_name']." (".$sections->embryo_info['solution_book'].") add section";

	$up_block = $sections->show_upblock($_GET['opr']);

	$display_block .= "<form method=\"POST\" action=\"sections.php?opr=write_raw_data\">\n";
	$display_block .= "<div class=\"massive_form\">";	
	$display_block .= $sections->rawInputForm();

	$display_block .= "<input type=\"hidden\" name=\"slide_id\" value=\"".$_GET['slide_id']."\">";
	
	$display_block .= "<div class=\"form_submit_buton_right\">";
	$display_block .= "<input type=\"submit\" value=\"write\" class=\"button\">\n";
	$display_block .= "</div>";
	
	$display_block .= "</div>";	
	$display_block .= "</form>";
	

	
	
}
elseif ($_GET['opr'] == "get_section_info") {
	
	$sql = "select pos_on_slide, section_name from section where slide_id=?";
	$prepare = $db->prepare($sql);
	$prepare->bindValue(1, $_GET['slide_id'], PDO::PARAM_INT);
	$prepare->execute();
	$existing_sections = $prepare->fetchAll(PDO::FETCH_ASSOC);
#echo "<pre>";
#print_r($existing_sections);
#echo "</pre>";

	$display_block .= "<form action=\"sections.php?opr=add_section&slide_id=".$_GET['slide_id']."\" method=\"POST\">\n";
	$display_block .= "<fieldset>\n<legend>add section</legend>\n";
	$display_block .= "<input type=\"hidden\" name=\"slide_id\" value=\"".$_GET['slide_id']."\">";

	$display_block .= "<p>serial position on slide:\n";
	$display_block .= "<select name=\"pos_on_slide\">\n";
	$display_block .= "<option value=\"0\" selected disabled>-serial position-</option>\n";
	for ($i=1;$i<21;++$i) {
		$display_block .= "<option value=\"".$i."\">".$i."</option>\n";
	}
	$display_block .= "</select></p>\n";
	$display_block .= "<p>name (e.g. S1T1):\n";
	$display_block .= "<input type=\"text\" name=\"section_name\" size=\"6\"></p>\n";
	$display_block .= "<p>location (paste here):\n";
	$display_block .= "<input type=\"text\" name=\"section_location\" size=\"35\"></p>\n";
	$display_block .= "<p><input type=\"submit\" value=\"record\" class=\"button\">\n";
	$display_block .= "</fieldset>\n";
	$display_block .= "</form>";
	
	$display_block .= "Existing:<br>\n";
	foreach ($existing_sections as $section) {
		$display_block .= $section['pos_on_slide']."-".$section['section_name']."\n";
	}
	
} 
elseif ($_GET['opr'] == "add_section") {
	
	$head = "write";
	
	if ($_POST['section_id'] == 0) 
	{
		$sql = "insert into section (slide_id, pos_on_slide, section_name, file_name, rating, comments) values (?, ?, ?, ?, ?, ?)";
		$prepare = $db->prepare($sql);
		$prepare->bindValue(1, $_POST['slide_id'], PDO::PARAM_INT);
		$prepare->bindValue(2, $_POST['pos_on_slide'], PDO::PARAM_INT);
		$prepare->bindValue(3, $_POST['section_name'], PDO::PARAM_STR);
		$prepare->bindValue(4, $_POST['file_name'], PDO::PARAM_STR);
		$prepare->bindValue(5, $_POST['rating'], PDO::PARAM_INT);
		$prepare->bindValue(6, $_POST['comments'], PDO::PARAM_STR);
	} 
	else 
	{
		$sql = "update section set slide_id=?, pos_on_slide=?, section_name=?, file_name=?, rating=?, comments=? where id=?";
		$prepare = $db->prepare($sql);
		$prepare->bindValue(1, $_POST['slide_id'], PDO::PARAM_INT);
		$prepare->bindValue(2, $_POST['pos_on_slide'], PDO::PARAM_INT);
		$prepare->bindValue(3, $_POST['section_name'], PDO::PARAM_STR);
		$prepare->bindValue(4, $_POST['file_name'], PDO::PARAM_STR);
		$prepare->bindValue(5, $_POST['rating'], PDO::PARAM_INT);
		$prepare->bindValue(6, $_POST['comments'], PDO::PARAM_STR);
		$prepare->bindValue(7, $_POST['section_id'], PDO::PARAM_STR);
	}
	
	$prepare->execute();
	
	header('Location: sections.php?opr=multiple_add&slide_id='.$_POST['slide_id']);
	
/*	$sql = "insert into section (slide_id, pos_on_slide, section_name, file_name) values (?, ?, ?, ?)";
	$prepare = $db->prepare($sql);
	$prepare->bindValue(1, $_POST['slide_id'], PDO::PARAM_INT);
	$prepare->bindValue(2, $_POST['pos_on_slide'], PDO::PARAM_INT);
	$prepare->bindValue(3, $_POST['section_name'], PDO::PARAM_STR);
	$prepare->bindValue(4, $_POST['section_location'], PDO::PARAM_STR);

	$prepare->execute();
		
	header('Location: sections.php?opr=select&slide_id='.$_POST['slide_id']);
			
	echo "<a href=\"sections.php?opr=select&slide_id=".$_POST['slide_id']."\">Click to redirect</a>\n";
*/
#echo "<pre>";
#print_r($_POST);
#echo "</pre>";
	
	
}
elseif ($_GET['opr'] == "write_raw_data") {
	
	$head = "write";
	$slide_id = array_pop($_POST);
	
	$slides = array();
	
	foreach ($_POST as $key => $val) {
		if (empty($val['filename']) || ($val['filename'] == "filename")) {
			continue;
		} else {
			$slides[$key] = $val;
		}
	}
	
	if (sizeof($slides) == 0) {
		echo "No valid input";
		$db = null;
		die;
	}
	
	try {
		
		$db->beginTransaction();
		
		$sql = "insert into section (slide_id, pos_on_slide, section_name, file_name) values (?, ?, ?, ?)";
		$prepare = $db->prepare($sql);
			
		foreach ($slides as $key => $val) {
			$pos_on_slide = explode("_", $key);
			$parametes = array($slide_id, $pos_on_slide[1], $val['name'], $val['filename']);
			
			$prepare->execute($parametes);
			
#			echo "<pre>";
#			var_dump($parametes);
#			echo "</pre>";
		}
		
		$db->commit();
		
		header('Location: sections.php?opr=select&slide_id='.$slide_id);
		
	} catch (Exception $e) {
#		$db->rollback();
		
		echo "Exception caught!<br />";
		throw $e;
		die;
	}
	
	
#	echo "<pre>";
#	var_dump($slide_id);
#	print_r($_POST);
#	echo "</pre>";
}
elseif ($_GET['opr'] == "truserial")
{
	
	if ( !isset($_GET['embryo_id']) or !isset($_GET['row_id']) ) {
		echo "get failed - 1st clause";
		die;
	}
	
	if ( !is_numeric($_GET['embryo_id']) or !is_numeric($_GET['row_id']) ) {
		echo "get failed - 2nd clause";
		die;
	}
	
		
	if ( ($_GET['embryo_id'] < 1) or ($_GET['row_id'] < 1) ) {
		echo "get failed - 3rd clause";
		die;
	}
	
	$row = $_GET['row_id'];
	
	$embryo = new embryo ($db, $_GET['embryo_id']);
	
	$set_width = $embryo->embryo_data['set_width'];
	settype($set_width, "int");
	$set_height = $embryo->embryo_data['set_height'];
	settype($set_height, "int");
	
	if ($set_height < 1 ) {
		echo "not cut yet";
		die;
	}
	
	if ( $row > $set_height ) {
		echo "row not found";
		die;
	}

	
	$embryo->relatedSlides();
	
#	echo "<pre>";
#	var_dump($embryo->embryo_slides);
#	echo "</pre>";
	
	$slide_ids = array();
	$slidesFull = array();
	
	$start = (($row-1)*$set_width)+1;
	$end = $set_width*$row;
	
	for ($i=$start, $j=1; $i<=$end; ++$i, ++$j) {
		$temp_obj = new sections($db, $embryo->embryo_slides[$i]['id']);
		$slide_ids[] = $embryo->embryo_slides[$i]['id'];
		
		$temp_array = $temp_obj->sections;
		
		
		
		if (!is_array($temp_obj->sections) || ($temp_obj->sections == 0) ) {
			
			continue;
			
		} else {
			foreach ($temp_array as &$section) {
			
				if (is_array($section)) {
					$section['relative_pos'] = ( ($section["pos_on_slide"] - 1) * $set_width) + $j;
					$slidesFull[] = $section;
				} else {
					continue;
				}
			
			
	#			echo "<pre>1st<br />";
	#			var_dump($section);
	#			echo "</pre>";	
			}
			
		}
#		echo "<pre>temp<br />";
#		var_dump($temp_array);
#		echo "</pre>";	
		
		
#		$slidesFull[] = $temp_array;
		$temp_obj = null;
	}
	

	usort($slidesFull, function ($a, $b) {
		return $a["relative_pos"]-$b["relative_pos"];
	});
	
#	echo "<pre>";
#	var_dump($slidesFull);
#	echo "</pre>";	


	$columns = 6;
	$plethos = count($slidesFull);
	$rows = ceil($plethos / $columns);
	
	// round up the table
	$difference = ($rows*$columns) - $plethos;
#	while ($difference-->0) {
#		$this->sections[] = "blank";
#	}
	

	$pic_block = "<table class=\"slideSections\">\n";
	
	$inside_row = 0;
	
	for ($i=0; $i<$plethos; ++$i) {
		
		if ($inside_row == 0) {
			$pic_block .= "<tr>\n";
			$inside_row = 1;
		}
		
		$pic_block .= "<td>";
		$pic_block .= "<div class=\"insideImageText\">";
		$pic_block .= "<img class=\"slideSections\" src=\"".$slidesFull[$i]['file_name']."\" alt=\"".$slidesFull[$i]['section_name']."\" >";
		
		$pic_block .= "<div class=\"top-left\">".$i."</div>";
		$pic_block .= "</div>";
		$pic_block .= "</td>\n";
		
		if ($inside_row == 1) {
			if ( (($i+1) % $columns == 0) ) {
				$pic_block .= "</tr>\n";
				$inside_row = 0;
			}
		}
		
	}
	
	
/*	
	for ($row=0;$row<$rows;++$row) {
		$pic_block .= "<tr>\n";
		for ($col=0;$col<$columns;++$col) {
			$pic_id = $col + $row*$columns;
			$pic_block .= "<td>";
			$pic_block .= "<div class=\"insideImageText\">";
			if ( ($slidesFull[$pic_id] != "blank") && ($pic_id < ($plethos+1)) ) {

				$pic_block .= "<img class=\"slideSections\" src=\"".$slidesFull[$pic_id]['file_name']."\" alt=\"".$slidesFull[$pic_id]['section_name']."\" >";

				
			} else {
				$pic_block .= ($pic_id+1)."<br>";
				$pic_block .= "section missing";
			}
			$pic_block .= "<div class=\"top-left\">".$pic_id."</div>";
			$pic_block .= "</div>";
			$pic_block .= "</td>\n";
		}
		$pic_block .= "</tr>\n";	
	}
	*/

	$pic_block .= "</table>";

	
}
else {
	echo "You have not provided a valid opr!";
	die;
}

$db = null;

?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="styles/embryonicB.css">
<link rel="stylesheet" type="text/css" href="styles/sectionsADD.css">
<link rel="stylesheet" type="text/css" href="styles/recordareADD.css">
<link rel="shortcut icon" type="image/x-icon" sizes="16x16" href="favicon.ico"/>
<link rel="shortcut icon" type="image/png" sizes="32x32" href="favicon32.png"/>
<link rel="apple-touch-icon" type="image/png" sizes="152x152" href="favicon152.png"/>
<title>
<?php echo $pageTitle; ?>
</title>
<body>
<div class="container">
	<div class="into">

		<div class="bind"></div>
		<div class="facultyid"><h2><?php echo $head; ?></h2></div>
		<div class="bind"></div>
		<div class="menu">	<?php echo $navigation_menu; ?></div>
		<?php if (isset($kleines_menu)) { echo $kleines_menu; } ?>
		
		<?php
		if (!empty($up_block)) {
			echo "<div class=\"kleinesmenu kleinesRED\">".$up_block."</div>";
#			echo "<div class=\"kleinesmenu\" style=\"background-color: #ffe4e1;\">".$up_block."</div>";
		}
		?>
		
		<div class="bind"></div>
		<div class="loginData">
			Signed in as <?php echo $_SESSION['user_data']['legal_name']; ?><br />
			<a href="login.php?opr=logout">Log out</a>
		</div>
		<div class="bind"></div>
		<div class="text">
			<?php echo $display_block; ?>
		</div>
	</div>
	

		<div class="images"><?php echo $pic_block; ?></div>

	
<!-- here go the pictures -->
</div>
</body>
</html>