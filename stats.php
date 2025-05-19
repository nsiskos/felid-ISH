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
	$restricted_flag = ($_SESSION['user_data']['perm'] == 2) ? "OFF" : "ON" ;
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

$kleines_menu = "
<div class=\"kleinesmenu\">
<ul>
		<li><a href=\"stats.php\">embryo distribution</a></li>&nbsp;|&nbsp;
		<li><a href=\"stats.php?opr=slidescore\">slide score</a></li>
</ul>
</div>
";

if (!isset($_GET['opr'])) // display embryo distribution graph
{
	$head = $pageTitle = "embryo distribution";
	
	$sql = "select age, (select count(id) from embryo where part!='body' and set_height>0 and age=EMBL.age) as cut_no, (select count(id) from embryo where part!='body' and set_height is null and age=EMBL.age) as uncut_no from embryo EMBL group by age";
	
	$ret = $db->query($sql);
	$outer = array();
    $outer = $ret->fetchAll(PDO::FETCH_ASSOC);
	
	$extras_in_header .= "<script src=\"https://d3js.org/d3.v5.min.js\"></script>\n";
	$extras_in_header .= "<script>var embryo_data=".json_encode($outer, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK).";</script>\n";
		
	$extra_css .= "<link href=\"styles/graphADD.css\" rel=\"stylesheet\" type=\"text/css\">";
	
	
	$display_block .= "<div id=\"graphContainer\">";
	$display_block .= "<div id=\"graphTitle\">Embryo distribution</div>";
	$display_block .= "<div id=\"overview_graph\"></div>";
	$display_block .= "</div>";
	
	
	$post_js .= "<script type=\"text/javascript\" src=\"statScript/graphs.js\"></script>\n";
	

	
}
elseif ($_GET['opr'] == "slidescore")
{
	$head = $pageTitle =  "slide scoring";
		
	$simple_scatter = new dataset($db);
	
	if (!$d3_is_loaded) {
		$extras_in_header .= "<script src=\"https://d3js.org/d3.v5.js\"></script>\n";
		$extras_in_header .= "<script src=\"https://d3js.org/d3-array.v2.min.js\"></script>";
		$d3_is_loaded = 1;
	}

	$extras_in_header .= $simple_scatter->datasetJS("scoringSet");

	$extra_css .= "<link href=\"styles/graphADD.css\" rel=\"stylesheet\" type=\"text/css\">";
	$extra_css .= "<link href=\"styles/showMassive_style.css\" rel=\"stylesheet\" type=\"text/css\">";
	
	$display_block .= "<div id=\"graphContainer\">";
	$display_block .= "<div id=\"graphTitle\">slide scoring</div>";
	$display_block .= "<div id=\"overview_graph\"></div>";
	$display_block .= "</div>";
	
	$display_block .= "<div class=\"text\" id=\"detailsDiv\">";
	
	$display_block .= "</div>";
	
	$display_block .= "<div class=\"text\">";
	$display_block .= "<table class=\"massive_table\">";
	
	$display_block .= "<thead>";
	
	$display_block .= "<tr>";
	
	$display_block .= "<th>probe</th>";
	$display_block .= "<th>slide N.</th>";
	$display_block .= "<th>score<br />(mean ; median ; min-max)</th>";
	$display_block .= "<th>SD</th>";
	
	$display_block .= "</tr>";
		
	$display_block .= "</thead>";
	
	$display_block .= "<tbody id=\"probeInfo\">";
	$display_block .= "</tbody>";
	$display_block .= "</table>";
	
	$display_block .= "</div>";
	
	$post_js .= $simple_scatter->loadGraphClassAbFooter();
	$post_js .= $simple_scatter->plotGraph("overview_graph", 800, 400, "bubble_green");
	$post_js .= "<script src=\"statScript/buildTable.js\"></script>";
	
}

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
			
			<!--div class="text"-->
				<?php echo $display_block; ?>
				<!--/div-->

							
		

			
			

			
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