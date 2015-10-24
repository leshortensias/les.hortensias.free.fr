<?php
require_once("includes/utilisateur.inc.php");
session_start();
$myUser = new Utilisateur();
$myUser->initFromRequest();
require_once("includes/message.inc.php");
$or = new Message();
$or->initFromRequest();
// Réaliser l'action à effectuer
if (isset($action)) {
	$futur_action=MODIFIER;
	// mise à jour
	$or->action($action);
} else {
	// On charge l'utilisateur si son id est passé en paramètre
	if (isset($id_or)) {
		$or->charge($id_or);
		$futur_action=MODIFIER;
	} else {
		$futur_action=AJOUTER;
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>Administration du livre d'or</title>
	<link rel="stylesheet" type="text/css" href="includes/admin.css">
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-15" >
</head>
<body>
<h1>Administration du livre d'or</h1>
<?php
$myUser->verif();
if ($myUser->status == OK) {
?>
	<h2>Editer un message</h2>
	<?php $or->formulaire($futur_action, 1); ?>
	<h2>Liste des messages </h2>
<?php
	$or->liste(1); // le 1 c'est pour avoir les paramètres d'admin
}
?>
<hr>
	<?php 
	$myUser->affiche();
	include("includes/admin_menu.inc.php");
	$myUser->debug() ;
	$or->debug() ;
	?>
</body>
</html>
		
