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

# now that we have a validated user load the database!
require_once("db_handle.php");
require_once("embryos.uni.class.php");

$pageTitle = "serial organizer";
$display_block = "";
$head = "";
$extras_in_header = "";
$age_block = "";
$img_block = "<br>";


if (!isset($_GET['opr'])) { 
	
	echo "Error: Bad selector on start<br />";
	die;

} 
elseif ($_GET['opr'] == "organize") 
{ // please provide an animal id vie GET
	
	if (!is_numeric($_GET['id'])) {
		echo "No valid animal id provided!";
		die;
	}
	
	$extras_in_header .= "<script type=\"text/javascript\" src=\"embryonic.js\"></script>\n";
	$extras_in_header .= "<script type=\"text/javascript\" src=\"shifting.js\"></script>\n";
	

	
	$embryo = new embryo($db, $_GET['id']);
	$embryo->relatedSlides();

	$pageTitle = "F".$embryo->embryo_name." serial";

	$head = "organize an F".$embryo->embryo_name." serial";

	$display_block .= "<p><a href=\"".FRONTPAGE."?opr=showem&name=".$_GET['id']."\">F".$embryo->embryo_name."</a> (<a href=\"".FRONTPAGE."?opr=showag&age=".$embryo->embryo_data['age']."\" target=\"_blank\">E".$embryo->embryo_data['age']."</a>) is cut in the ".$embryo->embryo_data['cut']." plane</p>\n";
		
	$display_block .= "<p>Slides registered so far:</p>\n";
	$display_block .= $embryo->printEmitterTable();
	
	
	$display_block .= "<div class=\"select_new_embryo\">";
	$display_block .= "Add another embryo:\n";
	$embryoList = new allEmbryos($db);
	
	$display_block .= "<select onchange=\"showEmitter(this.options[this.selectedIndex].value);\">\n";
	$display_block .= "<option value=\"0\" selected>choose</option>\n";
	$display_block .= $embryoList->selectListItems();
	$display_block .= "</select>\n";
	
	$display_block .= "</div>";
	
	$display_block .= "<div id=\"emitter1\"></div>";
	
	$display_block .= "<p>Drag below elements from above to organize the serial:</p>\n";
	
	$display_block .= "<div id=\"receiver_fix\">";
	$display_block .= $embryo->printReceiverTable();
	$display_block .= "</div>";
	
	$display_block .= "<br /><button class=\"button\" value=\"Show\" onclick=\"renderReceiverData(".$embryo->embryo_data['set_width'].")\">Show</button>";
	$display_block .= "&nbsp;<a href=\"serial.php?opr=organize&id=".$_GET['id']."\">Reset</a>\n";
	
	
#	$display_block .= "&nbsp;<button class=\"button\" value=\"Reset\" onClick=\"window.location.href=window.location.href\">Reset</button>\n";

#echo "<pre>";
#print_r($_GET);
#echo "</pre>";
	
} 
else 
{
	
	echo "Error: Bad selector on finish<br />";
	die;	
}

$db = null;

?>


<!DOCTYPE html>
<html>
<head>
<link href="styles/embryonicB.css" rel="stylesheet" type="text/css">
<link href="styles/serialADD.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/x-icon" sizes="16x16" href="favicon.ico"/>
<link rel="shortcut icon" type="image/png" sizes="32x32" href="favicon32.png"/>
<link rel="apple-touch-icon" type="image/png" sizes="152x152" href="favicon152.png"/>

<style>
	.sticky {
	  position: fixed;
	  top: 0px;
	  left: 300px;
	  width: auto;
	  background-color:#ffffff;
	  border: none;
	  opacity: 0.6;
	  
	}


	/* Add some top padding to the page content to prevent sudden quick movement (as the header gets a new position at the top of the page (position:fixed and top:0) */
	.sticky + .content {
	  padding-top: 102px;
	}
	
	
	
</style>



<?php echo $extras_in_header; ?>

<title>
<?php echo $pageTitle;?>
</title>

</head>


<body>
	<div class="container">
		<div class="into">
			<div class="bind"></div>

			<div class="facultyid" id="pageTop"><h2><?php echo $head; ?></h2></div>
			<div class="bind"></div>
			<div class="menu"><?php echo $navigation_menu; ?></div>
			<?php if (isset($kleines_menu)) { echo $kleines_menu; } ?>

			<div class="loginData">
				Signed in as <?php echo $_SESSION['user_data']['legal_name']; ?><br />
				<a href="login.php?opr=logout">Log out</a>
			</div>

			<div class="text"><?php echo $display_block; ?></div>


			<div id="trans_out" class="content"></div>
			
			<div class="go_to_top"><a href="#pageTop"><img src="icons/goTop.png" alt="go to top"></a></div>

			<div class="footer"><?php echo $footer; ?></div>

		</div>
	</div>



</body></html>