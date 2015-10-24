<?php
require_once("includes/page.inc.php");
$myPage = new Page();
if (isset($debug)) $myPage->debug=$debug;
if (isset($debug)) $myPage->css->debug=$debug;
		
require_once("includes/utilisateur.inc.php");
session_start();
$myUser = new Utilisateur();
$myUser->initFromRequest();

// Réaliser l'action à effectuer
if (isset($action)) {
	$futur_action=MODIFIER;
	// affectation des variables passé en paramètre.
	$myPage->initFromRequest();
	// mise à jour
	$myPage->action($action);
} else {
	// On charge la page si son id est passé en paramètre
	if (isset($id_page)) { 
		$myPage->charge($id_page);
		$futur_action=MODIFIER;
	} else {
		$futur_action=AJOUTER;
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Edition d'un page</title>
	<link rel="stylesheet" type="text/css" href="includes/admin.css">
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-15" >
</head>
<body onload="initEditor()">
<?php
// Affichage du formulaire
$myUser->verif();
if ($myUser->status == OK) {
	$myPage->formulaire($futur_action);
}
echo "<hr>";
$myUser->affiche();
include("includes/admin_menu.inc.php");
$myPage->debug();
$myPage->css->debug();
?>
</body>
</html>
