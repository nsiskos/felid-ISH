<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', false);

# pass $_POST['setting']

session_start();
require_once("standards.php");

# this page requires user permission al least = 1
$page_permission = 1;

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

$display_block = "";

if (!isset($_POST['setting'])) {
	echo "No valid slide setting provided&nbsp;".$_POST['setting'];
	die;
}

$nested_request = json_decode($_POST['setting'], TRUE);
$processed_array = array(); // store the array after removal of blank lines
$rows_array = array(); // 'clean' array, but in row format. has to be converted to columns
$final_array = array(); // final array to use, contains slide_id


/************************ PREPARE json input ************************/

$rows = sizeof($nested_request);
$columns = sizeof($nested_request[0]);


// first remove completely emtpy column
for ($i=0; $i<$columns; ++$i) // foreach column
{
	$column_not_set = 0;
	
	$indi_column = array(); // save column elements
	
	for ($j=0; $j<$rows; ++$j) //foreach row
	{
		$indi_column[] = $nested_request[$j][$i];
		if ($nested_request[$j][$i] == "not_set")
		{
			++$column_not_set;
		}
		
	}
	
	if ($column_not_set == $rows) { // we have not_set columns in this row
		continue;
	} else {
		
		$processed_array[] = $indi_column;
		
	}
	
	
}

// now remove completely empty rows

$interm_rows = sizeof($processed_array[0]);
$interm_cols = sizeof($processed_array);

for ($i=0; $i<$interm_rows; ++$i)
{
	
	$empty_row = 0;
	$trans_col = array();
	
	for ($j=0; $j<$interm_cols; ++$j)
	{
		$trans_col[] = $processed_array[$j][$i];
		if ($processed_array[$j][$i] == "not_set")
		{
			++$empty_row;
		}
		
	}

#	echo "<pre>";
#	print_r($trans_col);
#	echo "</pre>";

	
	if ($empty_row == $interm_cols)
	{
		continue;
	} else 
	{
		$rows_array[] = $trans_col;
	}
	
}




/************************ FILLER ************************/
/*
 * fetched sections
 * fills in where a slide is missing
*/

$final_ROWS = sizeof($rows_array);;
$final_COLS = sizeof($rows_array[0]);

$all_sections = array();


for ($i=0; $i<$final_ROWS;++$i) {
	
	$max_sections_on_row = 0;
	$sections_on_row = array();
	
	for ($j=0;$j<$final_COLS;++$j) {
				
		$slide_id = $rows_array[$i][$j];
		
#		echo $slide_id." ";
		
		$tmp_sections = array();
		
		if ($slide_id != "not_set") {
#			echo "YES<br>";
			$sections = new sections($db, $slide_id);
			
	#		echo "<pre>";
	#		var_dump($sections);
	#		echo "</pre>";
		
			if ( $sections->max_sections > 0) { // if there are sections registered
			
				$tmp_sections = $sections->sections;
							
				if ($sections->max_sections >= $max_sections_on_row) {

					$max_sections_on_row = $sections->max_sections;
				
				} else {
				
					$difference = $max_sections_on_row-$sections->max_sections;
					
					while ( $difference-- > 0) {
						$tmp_sections[] = "missing";
						
					}
				
				}
			
			}
			
			$all_sections[$i][] = $tmp_sections;
			
		} else { // if the slide has no sections, leave it blank
			
			$all_sections[$i][] = "blank";
	
		}

	}
#	echo "<br>";
}

/*
echo "<pre>";
#echo "Nested_request ________________________________<br />";
#print_r($nested_request);
#echo "processed_array ________________________________<br />";
#print_r($processed_array);
echo "rows_array ___________________________";
print_r($rows_array);
echo "all_sections array ________________\n";
print_r($all_sections);
echo "</pre>";
die;
*/

foreach ($all_sections as &$section_row) { // here we search for blank alements and turn them into an array of the proper size
	
	
	$row_size = 0;
	$found = 0;
	$where_is_it = 0;
	
	$section_row_size = sizeof($section_row);
	
	for ($i=0; $i<$section_row_size; ++$i) {
		
		if (is_array($section_row[$i])) {
			
			$row_size = sizeof($section_row[$i]);
		
		}
			
	}


		
	for ($i=0; $i<$section_row_size; ++$i) {
			
		if (!is_array($section_row[$i])) {
				
			$section_row[$i] = array_fill(0, $row_size, "blank");		
				
		}
			
	}


}



#echo "<pre>";
#echo "FINAL_array ________________\n";
#print_r($all_sections);
#echo "</pre>";

/************************ PRINTER ************************/

$display_block .= "<table id=\"slideSections\">\n";
foreach ($all_sections as $row) {
	
	$columns = sizeof($row);
	$rows = sizeof($row[0]);
	
	for ($i=0; $i<$rows; ++$i) {
		
		$display_block .= "<tr>";
		
		for ($j=0; $j<$columns; ++$j) {
			
			$photo_id = $row[$j][$i];
			
			if (is_array($photo_id)) {
				
				$display_block .= "<td>";
				
				$display_block .= "<div class=\"insideImageText\">";
	
				
				$display_block .= "<a href=\"".$photo_id['file_name']."\" target=\"_blank\">";
				$display_block .= "<img id=\"slideSections\" src=\"".$photo_id['file_name']."\" alt=\"".$photo_id['section_name']."\">";
				$display_block .= "</a>";

				$display_block .= "<div class=\"top-left\">";
				$display_block .= "<a class=\"inside_image\" href=\"javascript:void(0);\" onClick=\"\">";
				$display_block .= "<i class=\"arrow up\"></i>";
				$display_block .= "</a>";
				$display_block .= " ".$i."x".$j;
				$display_block .= "</div>";
								
				$display_block .= "<div class=\"bottom-right\">";
				$display_block .= "<a class=\"inside_image\" href=\"javascript:void(0);\" onClick=\"\">";
				$display_block .= "<i class=\"arrow down\"></i>";
				$display_block .= "</a>";
				$display_block .= "</div>";
				
				$display_block .= "<div class=\"center-bottom image_name_hovered\">";
				$display_block .= $photo_id['section_name'];
				$display_block .= "</div>";
				
								
				$display_block .= "</div>";
				$display_block .= "</td>";
			} else {
				$display_block .= "<td>";
#				$display_block .= $photo_id;
				$display_block .= "missing";
				$display_block .= "</td>";
			}
			
		}
	
		$display_block .= "</tr>\n";
	}
	
#	echo sizeof($row)." - ";
#	print_r($row);
}
$display_block .= "</table>\n";



echo $display_block;



?>