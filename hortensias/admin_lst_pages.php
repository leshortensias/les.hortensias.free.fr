<?php
require_once("includes/utilisateur.inc.php");
session_start();
$myUser = new Utilisateur();
$myUser->initFromRequest();
require_once("includes/page.inc.php");
session_start();
$myPage = new Page();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>Liste des pages</title>
	<link rel="stylesheet" type="text/css" href="includes/admin.css">
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-15">
</head>
<body>
<h1>Liste des pages</h1>
<?php
$myUser->verif();
if ($myUser->status == OK) {
	$myPage->liste();
}
?>
<hr>
	<?php 
	$myUser->affiche();
	include("includes/admin_menu.inc.php");
	$myUser->debug() 
	?>
</body>
</html>
		
