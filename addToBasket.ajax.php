<?php

# pass $_GET['lab_id']

session_start();
require_once("standards.php");

# this page requires user permission al least = 1
$page_permission = 2;

$out_block = "";

$now = time();
$difference = $now - $_SESSION['time'];

// perform the login check. $temporal_allownce is found inside standards.php
if ( !isset($_SESSION['time']) or ($difference > $temporal_allowance) or !isset($_SESSION['user_data']) ) { 

	echo "unauthorized access";
	die;

}

# this page requires user permission at least = 2
if ( ($_SESSION['user_data']['perm'] < $page_permission) or (!isset($_SESSION['user_data']['perm'])) ) {
	echo "You do not have sufficient priviledges to access this page!</br>";
	die;
}

if (!isset($_GET['action'])) {
	
	echo "No action intended. Exiting";
	die;
} elseif ($_GET['action'] == "addSlide") {

	if (!isset($_GET['slide_id']) or !is_numeric($_GET['slide_id'])) {
		echo "No valid slide id";
		die;
	}
	
	
	$_SESSION['addedSlides'][] = $_GET['slide_id'];
	
#	echo "View basket";
	echo sizeof($_SESSION['addedSlides'])." slides selected.<br />";
	echo "<a href=\"viewBasket.php?opr=showBasket\">start experiment</a>";
	
}	


?>