<?php

# at least 1
$db_handle_permission = 1;

# this page requires user permission at least = 1
if ( (!isset($page_permission)) or (!isset($_SESSION['user_data']['perm'])) or $page_permission < $db_handle_permission) {
	echo "DB: You do not have sufficient priviledges to access this page!</br>";
	die;
}

try {
    /*** connect to SQLite database ***/

	$db = new PDO("sqlite:embryos.db");

} catch (PDOException $e) {
    echo $e->getMessage();
    echo "<br><br>Database -- NOT -- loaded successfully .. ";
    die( "<br><br>Query Closed !!! $error");
}

function format_date ($input_date) {
	return substr($input_date, 0, 4)."-".substr($input_date, 4, 2)."-".substr($input_date, 6, 2);
}



if (!$db) {
	echo $db->lastErrorMsg();
}

?>