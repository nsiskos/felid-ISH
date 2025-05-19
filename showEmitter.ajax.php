<?php

# pass $_GET['lab_id']

session_start();
require_once("standards.php");

# this page requires user permission al least = 1
$page_permission = 1;

$out_block = "";

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

if (!is_numeric($_GET['embryo_id'])) {
	echo "no valid ambryo id provided. All GET is: <br />\n";
	echo "<pre>";
	var_dump($_GET);
	echo "</pre>";
	die;
}

require_once("db_handle.php");
require_once("embryos.uni.class.php");

$embryo = new embryo($db, $_GET['embryo_id']);
$embryo->relatedSlides();

echo $embryo->printEmitterTable();


$embryo = null;
$db = null;

#	header('Location: manage_probes.php');
	
#	echo "<a href=\"manage_probes.php\">Click to redirect</a>";

?>