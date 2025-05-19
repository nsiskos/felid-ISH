<?php
date_default_timezone_set('UTC');

#echo date('l jS \of F Y h:i:s A');

// how much can I be loged in, in seconds, set 1800 for online use
$temporal_allowance = 1800;


define("REL_SEQ_LOCUS", "../seq/"); // relative sequences locus - for inclusions
define("ABS_SEQ_LOCUS", "../seq/"); // Absolute locus - for inclusions
define("ABS_FELIDS_LOCUS", "felids/");

define("REL_ICONS_LOCUS", "./icons/");

define("FRONTPAGE", "frontpage.php");
define("RECORDARE", "recordare.php");

#$sequences_location = "http://dentate.mipropia.com/";

$header_row = array("&nbsp;", "Α", "Β", "Γ", "Δ", "Ε", "ΣΤ", "Z", "Η", "Θ", "I", "ΙΑ", "ΙΒ", "ΙΓ", "ΙΔ");

$favicons = "<link rel=\"shortcut icon\" type=\"image/x-icon\" sizes=\"16x16\" href=\"favicon.ico\"/>
<link rel=\"shortcut icon\" type=\"image/png\" sizes=\"32x32\" href=\"favicon32.png\"/>
<link rel=\"apple-touch-icon\" type=\"image/png\" sizes=\"152x152\" href=\"favicon152.png\"/>";


/*
$navigation_menu = "
	<ul>
	<li><a href=\"".ABS_SEQ_LOCUS."\">sequences</a></li>
		&nbsp;|&nbsp;	
	<li><a href=\"".ABS_FELIDS_LOCUS."\">felids</a></li>
	</ul>
";
*/



/*$navigation_menu_w3 = "
	<a href=\"".FRONTPAGE."\" class=\"w3-bar-item w3-button w3-mobile\">ages</a>
	<a href=\"".FRONTPAGE."?opr=showmoth\" class=\"w3-bar-item w3-button w3-mobile\">mothers</a>
	<a href=\"".FRONTPAGE."?opr=showallemb\" class=\"w3-bar-item w3-button w3-mobile\">all babies</a>
	<a href=\"".FRONTPAGE."?opr=sets\" class=\"w3-bar-item w3-button w3-mobile\">sectioned</a>
	<a href=\"".FRONTPAGE."?opr=exper\" class=\"w3-bar-item w3-button w3-mobile\">experiments</a>
	<a href=\"manage_probes.php\" class=\"w3-bar-item w3-button w3-mobile\">probes</a>
	<a href=\"manage_primers.php\" class=\"w3-bar-item w3-button w3-mobile\">primers</a>
	<div class=\"w3-dropdown-hover w3-mobile\">
	<button class=\"w3-button\">stats <i class=\"fa fa-caret-down\"></i></button>
	<div class=\"w3-dropdown-content w3-bar-block w3-sand w3-card-4\">
		<a href=\"stats.php\" class=\"w3-bar-item w3-button w3-mobile\">embryo distribution</a>
		<a href=\"stats.php?opr=slidescore\" class=\"w3-bar-item w3-button w3-mobile\">section scoring</a>
	</div>
	</div>
	
	<a href=\"login.php?opr=logout\" class=\"w3-bar-item w3-button w3-mobile w3-right\">".$_SESSION['user_data']['legal_name']." <i class=\"fa fa-sign-out\"></i></a>
	
	<a href=\"#pageTop\" class=\"w3-bar-item w3-button w3-mobile w3-right\"><i class=\"fa fa-arrow-circle-up\"></i></a>
";
*/

$navigation_menu = "
<ul>
		<li><a href=\"".FRONTPAGE."\">ages</a></li>&nbsp;|&nbsp;
		<li><a href=\"".FRONTPAGE."?opr=showmoth\">mothers</a></li>&nbsp;|&nbsp;
		<li><a href=\"".FRONTPAGE."?opr=showallemb\">all babies</a></li>&nbsp;|&nbsp;
		<li><a href=\"".FRONTPAGE."?opr=sets\">sectioned</a></li>&nbsp;|&nbsp;
		<li><a href=\"".FRONTPAGE."?opr=exper\">experiments</a></li>&nbsp;|&nbsp;
		<li><a href=\"manage_probes.php\">probes</a></li>&nbsp;|&nbsp;
		<li><a href=\"manage_primers.php\">primers</a></li>&nbsp;|&nbsp;
		<li><a href=\"evaluator.php\">evaluator</a></li>&nbsp;|&nbsp;
		<li><a href=\"stats.php\">stats</a></li>
	</ul>
";

#$kleines_menu = "select from above";

/*
$kleines_menu = "
<ul>
		<li><a href=\"".FRONTPAGE."\">ages</a></li>&nbsp;|&nbsp;
		<li><a href=\"".FRONTPAGE."?opr=showmoth\">mothers</a></li>&nbsp;|&nbsp;
		<li><a href=\"".FRONTPAGE."?opr=showallemb\">all babies</a></li>&nbsp;|&nbsp;
		<li><a href=\"".FRONTPAGE."?opr=sets\">sectioned</a></li>&nbsp;|&nbsp;
		<li><a href=\"".FRONTPAGE."?opr=exper\">experiments</a></li>&nbsp;|&nbsp;
		<li><a href=\"manage_probes.php\">probes</a></li>&nbsp;|&nbsp;
		<li><a href=\"manage_primers.php\">primers</a></li>&nbsp;|&nbsp;
		<li><a href=\"".FRONTPAGE."?opr=stats\">graphs</a></li>&nbsp;|&nbsp;
		<li><a href=\"".FRONTPAGE."?opr=fails\">fails</a></li>
	</ul>
";
*/

$footer_w3 = "
	<a href=\"http://orcid.org/0000-0001-9301-314X\">NS</a> &amp; 
	(<a href=\"http://php.net\">php</a>, 
	<a href=\"https://www.w3.org/html/\">html</a>,
	<a href=\"https://www.w3.org/Style/CSS/\">css</a>,
	<a href=\"http://www.sqlite.org\">sqlite3</a>,
	<a href=\"https://d3js.org/\">D3.js</a>,
	<a href=\"https://fontawesome.com/\">FA</a>)
";
$footer = "
	<p><a href=\"http://orcid.org/0000-0001-9301-314X\">NS</a> &amp; 
	(<a href=\"http://php.net\">php</a>, 
	<a href=\"https://www.w3.org/html/\">html</a>,
	<a href=\"https://www.w3.org/Style/CSS/\">css</a>,
	<a href=\"http://www.sqlite.org\">sqlite3</a>,
	<a href=\"https://d3js.org/\">D3.js</a>,
	<a href=\"https://fontawesome.com/\">FA</a>)
	</p>
";

?>
