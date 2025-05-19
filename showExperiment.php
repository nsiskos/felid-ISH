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

require_once("db_handle.php");

if (!isset($_GET['date'])) {
	
	echo "No date provided. Exiting";
	die;
}
		
	//	echo $out_block;

include_once("experiment.class.php");


function format_date_2 ($input_date) {
#	$months_en = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
	return substr($input_date, 0, 4)." ".substr($input_date, 4, 2)." ".substr($input_date, 6, 2);
}

echo "<h3>Experiment of: ".format_date($_GET['date'])."</h3><br />";
#echo "Experiment of: T<br />";
$exp = new experiment($db, $_GET['date']);
#$table = $exp->printSimpleTable();
$table = $exp->printTable();
	

echo $table;

	
$db = null;

#	header('Location: manage_probes.php');
	
#	echo "<a href=\"manage_probes.php\">Click to redirect</a>";

?>