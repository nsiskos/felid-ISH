<?php

# pass $_GET['slide_id']

session_start();
require_once("standards.php");

# this page requires user permission al least = 1
$page_permission = 1;

$columns = 6;
$pic_block = "";

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
require_once("embryos.uni.class.php");



if (!isset($_GET['opr'])) 
{
	echo "No valid operator provided&nbsp;".$_GET['slide_opr'];
	die;
}

#$pic_block .= "<br /><button onclick=\"clearSections('sectionSlides')\">hide sections</button><br /><br />";

if ($_GET['opr'] == "single") 
{
	if (!isset($_GET['slide_id'])) 
	{
		echo "No valid slide id provided&nbsp;".$_GET['slide_id'];
		die;
	}
	
	$sections = new sections($db, $_GET['slide_id'], "ON");
	
	if ($sections->sections == 0) {
		$pic_block .= "Currently there are no sections registered.\n";
	} else {
		$pic_block .= $sections->section_table($columns);
	}
} 
elseif ($_GET['opr'] == "multiple") 
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


#	$columns = 6;  // it is defined up above
	$plethos = count($slidesFull);
	$rows = ceil($plethos / $columns);
	
	// round up the table
	$difference = ($rows*$columns) - $plethos;
#	while ($difference-->0) {
#		$this->sections[] = "blank";
#	}
	

	$pic_block .= "<table class=\"slideSections\">\n";
	
	$inside_row = 0;
	
	for ($i=0; $i<$plethos; ++$i) {
		
		if ($inside_row == 0) {
			$pic_block .= "<tr>\n";
			$inside_row = 1;
		}
		
		$pic_block .= "<td>";
		$pic_block .= "<div class=\"insideImageText\">";
		$pic_block .= "<a href=\"".$slidesFull[$i]['file_name']."\" target=\"_blank\">";
		$pic_block .= "<img class=\"slideSections\" src=\"".$slidesFull[$i]['file_name']."\" alt=\"".$slidesFull[$i]['section_name']."\" >";
		$pic_block .= "</a>";
		
#		$pic_block .= "<div class=\"top-left\">".$i."</div>";
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
else 
{
	$pic_bloc .= "unrec operator";
	
}






echo $pic_block;

?>