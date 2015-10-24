<?php
define ("OK",0);
define ("KO",1);

define ("AJOUTER",0);
define ("MODIFIER",1);

class DbObject {
	var $password;
	var $host;
	var $login;
	var $db;
	var $debug=0;

	// &eacute;tat / status
	var $status;
	var $msg;

	function DbObject () {
		if (strstr($_SERVER['SERVER_NAME'],'tcweb.org')) {
			$this->login="hortensias";
			$this->password="trevoux";
			$this->host="localhost";
		} else {
			$this->login="les.hortensias";
			$this->password="trevoux";
			$this->host="sql.free.fr";
		}
		$this->msg = "<li>Cr&eacute;ation de l'objet DbObject</li>";
	}

	function connect () {
		$this->msg .= "<li>Connection &agrave; la base : \"$this->login@$this->host\" </li>";
		if ( function_exists ('mysql_connect') == TRUE ) {
			$this->db = mysql_connect ($this->host, $this->login, $this->password);
			if ($this->db != 0) {
				$rep = mysql_select_db ($this->login, $this->db);
				if ($rep == TRUE ) {
					return DB_ERR_MYSQL;
				} else {
					$this->status=KO;
					$this->msg .= "<li>s&eacute;lection du sch&eacute;ma \"$this->login\" impossible</li>";
				}
			} else {
				$this->status=KO;
				$this->msg .= "<li>connection impossible &agrave; la base de donn&eacute;es</li>";
			}
		} else {
			$this->status=KO;
			$this->msg .= "<li>librairie php mysql non charg&eacute;e<li>";
		}
	}

	function deconnect () {
		if ( function_exists ('mysql_close') == TRUE ) {
			if (mysql_close ($this->db) == FALSE ) {
				$this->status=KO;
				$this->msg .= "<li>fermeture de la connection mysql impossible<li>";
			} 
		} else {
			$this->status=KO;
			$this->msg .= "<li>librairie php mysql non charg&eacute;e<li>";
		}
	}

	function debug () {
		if ($this->debug > 0) {
			print ("<div class=debug style='color:red'>$this->msg<div>");
		}
	}

	function query ($query) {
		$this->msg .="	<li>query : $query</li>\n";
		$select = mysql_query ($query, $this->db);
		if ($select == 0) {
			$this->status=KO;
			$this->msg .="<ul>\n";
			$this->msg .="	<li>ERREUR MySQL num&eacute;ro : ".mysql_errno()."</li>\n";
			$this->msg .="	<li>Message : ".mysql_error()."</li>\n";
			$this->msg .="	<li>requette en erreur : $query</li>\n";
			$this->msg .="</ul>\n";
		}
		return $select;
	}

	function select ($query) {
		$this->connect();
		$ligne=null;
		$select = $this->query($query);
		if ($this->status == OK) {
			$ligne = mysql_fetch_array($select);
		}
		$this->deconnect();
		return $ligne;
	}

	function insert ($query) {
		$this->connect();
		$retCode=null;
		$this->query($query);
		if ($this->status == OK) {
			$retCode = mysql_insert_id();
		}
		$this->deconnect();
		return $retCode;
	}

	function update ($query) {
		$this->connect();
		$retCode=null;
		$this->query($query);
		$this->deconnect();
	}
}
?>
