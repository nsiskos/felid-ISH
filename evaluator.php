<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', false);


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

$pageTitle = "evaluate sections!";
$head = "score sections";
$rateInfo = "";
$imageFile = "";

$extras_in_header = "";
$extra_css = "";

$post_js = "";

if (!isset($_GET['opr'])) {
	
	$sql = "select count(id) as sectionsNum from section where rating IS NULL";

	$ret = $db->query($sql);
	$outer = array();
	$outer = $ret->fetch(PDO::FETCH_ASSOC);

	#echo "<pre>";
	#print_r($outer);
	#echo "</pre>";

	
	if ($outer['sectionsNum'] == 0) {
		
		$howManyMore = "All sections appear to be rated!<br />There are no unrated sections left!";
		
	} else {
		$howManyMore = "There are ".$outer['sectionsNum']." sections without rating waiting for you!";

		$sql2 = "select * from section where rating IS NULL order by random() limit 1;";

		$ret2 = $db->query($sql2);
		$outer = array();
		$outer2 = $ret2->fetch(PDO::FETCH_ASSOC);

		$imageFile = "<img style=\"width: 65%;\" src=\"".$outer2['file_name']."\" alt=\"".$outer2['section_name']."\">";

		$rateInfo .= $outer2['section_name']."<br />";

		$rateInfo .= "Set your rating: ";

		for ($i=1;$i<6;++$i) {
			$rateInfo .= "<a href=\"evaluator.php?opr=".$outer2['id']."&stars=".$i."\">";
			$rateInfo .= "<i id=\"star_".$i."\" class=\"far fa-star\" onmouseover=\"mouseOver('".$i."')\" onmouseout=\"mouseOut('".$i."')\"></i>";
			$rateInfo .= "</a>";
		}



	#	echo "<pre>";
	#	print_r($outer2);
	#	echo "</pre>";
	}
	
	

	
	
} elseif (is_numeric($_GET['opr'])) {
	
	if (!isset($_GET['stars']) || !is_numeric($_GET['stars'])) {
		echo "Bad selection";
		die;
	}
	
	settype($_GET['opr'], "integer");
	settype($_GET['stars'], "integer");
	
	$sql = "update section set rating=? where id=?";
	$prepare = $db->prepare($sql);
	$prepare->bindValue(1, $_GET['stars'], PDO::PARAM_INT);
	$prepare->bindValue(2, $_GET['opr'], PDO::PARAM_INT);
	$prepare->execute();
	
	header('Location: evaluator.php');
			
	echo "<a href=\"evaluator.php\">Click to redirect</a>\n";
	
#	echo "<pre>";
#	var_dump($_GET);
#	echo "</pre>";
	
} else {
	echo "problem occurred. check your get";
	echo "<pre>";
	var_dump($_GET);
	echo "</pre>";
	die;
}


$db = null;

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

<style>
	
	.sectionsLeft {
		padding: 7px 1px 7px 1px;
	}
	
</style>

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
			<div class="sectionsLeft">
				<?php echo $howManyMore; ?>
			</div>
			
			<div class="rateSection">
				<?php echo $rateInfo; ?>
			</div>
			
			<div class="image">
				<?php echo $imageFile; ?>
			</div>
			
			

			<div id="sectionSlides"></div>
			
					
			<!-- response here! -->

			
			

			
			<div class="go_to_top"><a href="#pageTop">
				<i class="fa fa-arrow-circle-up fa-3x"></i>
				<!--img src="icons/goTop.png" alt="go to top"-->
			</a></div>
			<div class="footer"><?php echo $footer; ?></div>
		</div>
	</div>

<script>
	
	function mouseOver (i) {
		var j;
		for (j=i; j>0; j--) {
			document.getElementById("star_"+j).classList.replace("far", "fas");
/*			console.log(j+" of "+i); */
		}
			
/*		document.getElementById("star_"+i).style.color = "red"; */
	}
	
	function mouseOut (i) {
		
		var j;
		for (j=i; j>0; j--) {
			document.getElementById("star_"+j).classList.replace("fas", "far");
/*			console.log(j+" of "+i); */
		}
		
	}
	
</script>

</body>
</html>