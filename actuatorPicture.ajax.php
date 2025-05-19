<?php

# pass $_GET['lab_id']

#echo "<pre>";
#print_r($_POST);
#echo "</pre>";

session_start();
require_once("standards.php");

# this page requires user permission al least = 2
$page_permission = 2;

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

if (!is_numeric($_POST['pic_id'])) {
	echo "no valid picture id provided. POST is: <br />\n";
	echo "<pre>";
	var_dump($_POST);
	echo "</pre>";
	die;
} else {
	$picture_id = $_POST['pic_id'];
}

require_once("db_handle.php");

if ($_GET['opr'] == "delete")
{
	if ( $_POST['kind'] == "group" ) 
	{
		$sql = "delete from group_photos where id = ?";
	} 
	else if ($_POST['kind'] == "embryo") 
	{
		$sql = "delete from embryo_photos where id = ?";
	
	} else {
		echo "No valid kind provided";
		echo "<pre>";
		var_dump($_POST);
		echo "</pre>";
		die;
	}

	try {
	
		$db->beginTransaction();
	
		$prepare = $db->prepare($sql);
		$prepare->bindValue(1, $picture_id, PDO::PARAM_INT);
		$prepare->execute();
		$db->commit();
	
		echo "Picture deleted";
	
	} catch (Exception $e) {
		$db->rollback;
		throw $e;		
	}
}
elseif ($_GET['opr'] == "alter")
{
	
#	echo "<pre>";
#	print_r($_POST);
#	echo "</pre>";
#	die;
	
	$return_to = explode("_", $_POST['return_to']);
	
#	$animal_id = $_POST['madre_id'];
	
	if ($picture_id == 0) 
	{
		if ($return_to[0] == "embryo")
		{
			$sql = "insert into embryo_photos (embryo_id, file_name, embryo_pic_descr) values (?, ?, ?)";
			

			$animal_id = $return_to[1];
			
			$return_url = RECORDARE.'?action=mod_embryo&embryo_id='.$animal_id;
		} 
		elseif ($return_to[0] == "madre")
		{
			$sql = "insert into group_photos (madre_id, file_name, group_descr) values (?, ?, ?)";
			
			$animal_id = $return_to[1];

			$return_url = RECORDARE.'?action=mod_mother&madre_id='.$animal_id;
			
		}
		else {
			echo "Bad selector";
			die;
		}
		
		try {

			$db->beginTransaction();

			$prepare = $db->prepare($sql);
			$prepare->bindValue(1, $animal_id, PDO::PARAM_INT);
			$prepare->bindValue(2, $_POST['img_location'], PDO::PARAM_STR);
			$prepare->bindValue(3, $_POST['img_desc'], PDO::PARAM_STR);
			$prepare->execute();
			$db->commit();
		
			header('Location: '.$return_url);
	
			echo "<a href=\"".$return_url."\">Click to redirect</a>\n";
	
#			echo "Picture info changed";

		} catch (Exception $e) {
			$db->rollback;
			throw $e;		
		}
		
	}
	elseif ($picture_id >= 1)
	{
		$return_id = explode("_", $_POST['return_to']);
		
		if ( $_POST['photoKind'] == "group" ) 
		{
			$sql = "update group_photos set file_name=?, group_descr=? where id = ?";
			$return_url = RECORDARE.'?action=mod_mother&madre_id='.$return_id[1];
			
		} 
		elseif ($_POST['photoKind'] == "embryo") 
		{
			
			$sql = "update embryo_photos set file_name=?, embryo_pic_descr=? where id = ?";
			$return_url = RECORDARE.'?action=mod_embryo&embryo_id='.$return_id[1];
	
		} else {
			echo "No valid kind provided";
			echo "<pre>";
			var_dump($_POST);
			echo "</pre>";
			die;
		}
	
		try {
	
			$db->beginTransaction();
	
			$prepare = $db->prepare($sql);
			$prepare->bindValue(1, $_POST['img_location'], PDO::PARAM_STR);
			$prepare->bindValue(2, $_POST['img_desc'], PDO::PARAM_STR);
			$prepare->bindValue(3, $picture_id, PDO::PARAM_INT);
			$prepare->execute();
			$db->commit();
			
			header('Location: '.$return_url);
	
			echo "<a href=\"".$return_url."\">Click to redirect</a>\n";
		
#			echo "Picture info changed";
	
		} catch (Exception $e) {
			$db->rollback;
			throw $e;		
		}
	}
	
}
else
{
	echo "Invalid input";
	echo "<pre>";
	var_dump($_POST);
	echo "</pre>";
	die;
}





$db = null;


?>