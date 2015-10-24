<?php
require_once("includes/utilisateur.inc.php");
session_start();
$user = new Utilisateur();
$user->initFromRequest();

require_once("includes/css.inc.php");
$css = new Css();
$css->initFromRequest("new_");
// Réaliser l'action à effectuer
if (isset($action)) {
	$futur_action=MODIFIER;
	// mise à jour
	$css->action($action);
} else {
	// On charge l'utilisateur si son id est passé en paramètre
	if (isset($id_css)) {
		$css->charge($id_css);
		$futur_action=MODIFIER;
	} else {
		$futur_action=AJOUTER;
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>Administration des pages de style</title>
	<link rel="stylesheet" type="text/css" href="includes/admin.css">
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-15">
</head>
<body>
<h1>Administration des pages de style</h1>
<?php
$user->verif();
if ($user->status == OK) {
?>
	<table>
		<tr><td valign=top>
			<?php $css->liste() ?>
		</td><td valign=top>
			<?php
			// Affichage du formulaire
			$css->formulaire($futur_action);
			?>
		</td></tr>
	</table>
<?php
}
?>
<hr>
	<?php 
	$user->affiche();
	include("includes/admin_menu.inc.php");
	$user->debug();
	$css->debug();
	?>
</body>
</html>
		
