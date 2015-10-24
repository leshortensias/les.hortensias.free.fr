<?php
require_once("includes/utilisateur.inc.php");
session_start();
$myUser = new Utilisateur();
$myUser->initFromRequest();
require_once("includes/nouvelle.inc.php");
session_start();
$news = new Nouvelle();
$news->initFromRequest();
// Réaliser l'action à effectuer
if (isset($action)) {
	$futur_action=MODIFIER;
	// mise à jour
	$news->action($action);
} else {
	// On charge l'utilisateur si son id est passé en paramètre
	if (isset($id_news)) {
		$news->charge($id_news);
		$futur_action=MODIFIER;
	} else {
		$futur_action=AJOUTER;
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>Administration des nouvelles</title>
	<link rel="stylesheet" type="text/css" href="includes/admin.css">
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-15" >
</head>
<body>
<h1>Administration des nouvelles</h1>
<?php
$myUser->verif();
if ($myUser->status == OK) {
?>
	<h2>Editer une nouvelle</h2>
	<?php $news->formulaire($futur_action); ?>
	<h2>Liste des nouvelles</h2>
<?php
	$news->liste(1); // le 1 c'est pour avoir les paramètres d'admin
}
?>
<hr>
	<?php 
	$myUser->affiche();
	include("includes/admin_menu.inc.php");
	$myUser->debug() ;
	$news->debug() ;
	?>
</body>
</html>
		
