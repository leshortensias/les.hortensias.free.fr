<?php
require_once("includes/utilisateur.inc.php");
session_start();
$myUser = new Utilisateur();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Administration du site</title>
	<link rel="stylesheet" type="text/css" href="includes/admin.css">
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-15" >
</head>
<body>
	<h1>Administration du site</h1>
<?php
// au cas ou des données sont passés en paramètres
$myUser->initFromRequest();
$myUser->verif();
if ($myUser->status == OK) {
?>
	<ul>
		<li><a href="admin_lst_pages.php">les pages du site</a></li>
		<li><a href="admin_css.php">les pages de style (css)</a></li> 
		<li><a href="admin_nouvelles.php">les news</a></li> 
		<li><a href="admin_or.php">le livre d'or</a></li>
		<li>les réservations</li>
		<li><a href="admin_users.php">les utilisateurs</a></li>
	</ul>
	<hr>
<?php
}
$myUser->affiche();
include("includes/admin_menu.inc.php");
$myUser->debug(); ?>
</body>
</html>
