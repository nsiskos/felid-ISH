<?php

#error_reporting(E_ALL);
#ini_set('display_errors', true);
#ini_set('html_errors', false);


session_start();
require_once "standards.php";
# this page requires user permission al least = 1
$page_permission = 2;


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
$head = "";
$extras_in_header = "";
$age_block = "";
$img_block = "<br>";
$months_en = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

if ($_GET['opr'] == "showBasket") { 
	
	$head = "view basket";
	
	$display_block .= "<div id=\"basket\"><a href=\"viewBasket.php?opr=deleteBasket\">delete selection</a></div><br /><br />";
	
#	echo "<pre>";
#	print_r($_SESSION['addedSlides']);
#	echo "</pre>";
	
	include_once("experiment.class.php");
	
	$exp = new experiment($db, $_SESSION['addedSlides']);
	
	$display_block .= "<form action=\"recordare.php?action=record_experiment\" method=\"POST\">";
	
	if (isset($_GET['alert']) && ($_GET['alert'] == "date") ) {
		$display_block .= "<p>Select a <span style=\"color: red;\">valid</span> date: ";
	} else {
		$display_block .= "<p>Enter date: ";
	}
	
	$display_block .= "<select name=\"year\">\n";
	$display_block .= "<option value=\"YEAR\" selected>YEAR</option>\n";

	$year = date('Y');
	for ($i=2016; $i<=$year; ++$i) {
		$display_block .= "<option value=\"".$i."\">".$i."</option>\n";
	}
	$display_block .= "</select>\n";
	
	$display_block .= "<select name=\"month\">\n";
	$display_block .= "<option value=\"month\" selected>month</option>\n";

	for ($i=1; $i<=12; ++$i) {
		$display_block .= "<option value=\"".$i."\">".$months_en[$i-1]."</option>\n";
	}
	$display_block .= "</select>\n";
	
	$display_block .= "<select name=\"day\">\n";
	$display_block .= "<option value=\"day\" selected>day</option>\n";

	for ($i=1; $i<=31; ++$i) {
		$display_block .= "<option value=\"".$i."\">".$i."</option>\n";
	}
	$display_block .= "</select>\n";
	
	$display_block .= "<input type=\"submit\" class=\"button\" value=\"submit\">";
	$display_block .= "</p>";
	
	$display_block .= $exp->printRecorderTable();
	
	$display_block .= "</form>";
	

} elseif ($_GET['opr'] == "deleteBasket") { 
	$db = null;
	unset($_SESSION['addedSlides']);
	header('Location: frontpage.php?opr=sets');
	echo "<a href=\"frontpage.php?opr=sets\">Click to redirect</a>";
	
} else {
	echo "Bad selector";
	$db = null;
	die;
}

$db = null;

?>


<!DOCTYPE html>
<html>
<head>
<link href="styles/embryonicB.css" rel="stylesheet" type="text/css">
<link href="styles/recordareADD.css" rel="stylesheet" type="text/css">
<link href="styles/showMassive_style.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/x-icon" sizes="16x16" href="favicon.ico"/>
<link rel="shortcut icon" type="image/png" sizes="32x32" href="favicon32.png"/>
<link rel="apple-touch-icon" type="image/png" sizes="152x152" href="favicon152.png"/>
<?php echo $extras_in_header; ?>

<title>Fs</title>
</head>


<body>

	<div class="container">
		<div class="into">

			<div class="bind"></div>

			<div class="facultyid"><h2><?php echo $head; ?></h2></div>
			<div class="bind"></div>
			<div class="menu"><?php echo $navigation_menu; ?></div>
			<div class="kleinesmenu"><?php echo $kleines_menu; ?></div>
			<div class="bind"></div>
			<div class="loginData">
				Signed in as <?php echo $_SESSION['user_data']['legal_name']; ?><br />
				<a href="login.php?opr=logout">Log out</a>
			</div>
			<div class="bind"></div>
			<div class="text">
				<?php echo $display_block; #echo $img_block; ?>
			</div>

			<?php echo $img_block; ?>
			
			<div class="go_to_top"><a href="#facultyid"><img src="icons/goTop.png" alt="go to top"></a></div>
			<div class="footer"><?php echo $footer; ?></div>

		</div>
	</div>
</body>
</html>