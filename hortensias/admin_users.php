<?php
require_once("includes/utilisateur.inc.php");
session_start();
$myUser = new Utilisateur();
$myUser->initFromRequest();

$newUser = new Utilisateur();
$newUser->initFromRequest("new_");
// Réaliser l'action à effectuer
if (isset($action)) {
	$futur_action=MODIFIER;
	// mise à jour
	$newUser->action($action);
} else {
	// On charge l'utilisateur si son id est passé en paramètre
	if (isset($new_id_user)) {
		$newUser->charge($new_id_user);
		$futur_action=MODIFIER;
	} else {
		$futur_action=AJOUTER;
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>Edition d'un utilisateur</title>
	<link rel="stylesheet" type="text/css" href="includes/admin.css">
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-15" >
</head>
<body>
<h1>Administration des utilisateurs</h1>
<?php
$myUser->verif();
if ($myUser->status == OK) {
?>
	<table>
		<tr><td valign=top>
			<?php $newUser->liste() ?>
		</td><td valign=top>
			<?php
			// Affichage du formulaire
			$newUser->formulaire($futur_action, "new_");
			?>
		</td></tr>
	</table>
<?php
}
?>
<hr>
	<?php 
	$myUser->affiche();
	include("includes/admin_menu.inc.php");
	$myUser->debug();
	$newUser->debug();
	?>
</body>
</html>
		
