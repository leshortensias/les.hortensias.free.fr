<?php
	require_once("includes/page.inc.php");

	if (! isset($page) ) $page = 'index';
	$myPage = new Page();
	if (isset($debug)) $myPage->debug=$debug;
	if (isset($debug)) $myPage->css->debug=$debug;
	$myPage->chargeDepuisNom($page);
	$myPage->affiche();
	$myPage->debug();
	$myPage->css->debug();
?>
