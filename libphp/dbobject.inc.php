<?php
/*
 * This file is part of libTcWeb a collection of free php objects
 *
 * libTcWeb is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * libTcWeb is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * libTcWeb; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * Street, Fifth Floor, Boston, MA 02110-1301, USA 
 */
define ("OK",0);
define ("KO",1);

define ("AJOUTER",0);
define ("MODIFIER",1);
define ("CHERCHER",2);
define ("EFFACER",3);

define ("DB_OK_MYSQL",0);
define ("DB_ERR_MYSQL",1);

if (!defined('DIR_SEP'))
  define('DIR_SEP', DIRECTORY_SEPARATOR);

if (!defined('LIBTCWEB_DIR'))
  define('LIBTCWEB_DIR', dirname(__FILE__) . DIR_SEP);
	
if (!defined('DB_INI_DIR'))
  define('DB_INI_DIR', dirname(__FILE__) . DIR_SEP);

class DbObject {
	var $password;
	var $host;
	var $login;
	var $db;
	var $db_name;
	var $debug=0;

	// état / status
	var $status;
	var $msg;    // tous les messages d'erreurs expliqué :-)
	var $usrmsg; // le dernier message d'erreur à destination des utilisateurs

	/**
	 * Constructeur de l'objet.
	 * 
	 * @ini : emplacement du fichier d'init, par défaut : rep/d'install de libtcweb/db.ini
	 */
	function DbObject ($ini = "none") {
		if ($ini == "none") {
			$ini = DB_INI_DIR."db.ini";
		}
		if (file_exists($ini)) {
			$ini_data = parse_ini_file($ini);
			if (isset ($ini_data['login'])) $this->login=$ini_data['login'];
			else $this->msg = "<li>Il manque le login</li>";
			
			if (isset ($ini_data['password'])) $this->password=$ini_data['password'];
			else $this->msg = "<li>Il manque le password</li>";
			
			if (isset ($ini_data['host'])) $this->host=$ini_data['host'];
			else $this->msg = "<li>Il manque le nom du serveur (host)</li>";
			
			if (isset ($ini_data['db'])) $this->db_name=$ini_data['db'];
			else $this->msg = "<li>Il manque le nom de la base (db)</li>";
			
			$className = get_class($this);
			$this->msg = "<li>Création de l'objet '$className'</li>";
		} else {
			$this->msg = "<li>Création de l'objet Impossible, il manque le fichier $ini</li>";
			$this->status = KO;
		}
		//$this->connect ();
	}

	function connect () {
		//$this->msg .= "<li>Connection à la base : \"$this->login@$this->host\" </li>";
		if ( function_exists ('mysql_connect') == TRUE ) {
			$this->db = mysql_connect ($this->host, $this->login, $this->password);
			if ($this->db != 0) {
				$rep = mysql_select_db ($this->db_name, $this->db);
				if ($rep == TRUE ) {
					$this->msg .= "<li>connection et sélection du schéma OK</li>";
				} else {
					$this->status=KO;
					$this->msg .= "<li>sélection du schéma \"$this->login\" impossible</li>";
				}
			} else {
				$this->status=KO;
				$this->msg .= "<li>connection impossible à la base de données</li>";
			}
		} else {
			$this->status=KO;
			$this->msg .= "<li>librairie php mysql non chargée<li>";
		}
	}

	function deconnect () {
		if ( function_exists ('mysql_close') == TRUE ) {
			if ($this->status == OK) {
				if (mysql_close ($this->db) == FALSE ) {
					$this->status=KO;
					$this->msg .= "<li>fermeture de la connection mysql impossible</li>";
				} 
			}
		} else {
			$this->status=KO;
			$this->msg .= "<li>librairie php mysql non chargée</li>";
		}
	}

	function debug () {
		if ($this->debug > 0) {
			print ("<div class=debug style='color:red'><ul>$this->msg</ul></div>");
		}
	}

	function message () {
		print ("<div class=message>$this->usrmsg<div>");
	}

	function query ($query) {
		$select = null;
		//$this->msg .="	<li>query : $query</li>\n";
		if ($this->status == OK) {
			$select = mysql_query ($query, $this->db);
			if ($select == 0) {
				$this->status=KO;
				$this->msg .="<ul>\n";
				$this->msg .="	<li>ERREUR MySQL numéro : ".mysql_errno()."</li>\n";
				$this->msg .="	<li>Message : ".mysql_error()."</li>\n";
				$this->msg .="	<li>requette en erreur : $query</li>\n";
				$this->msg .="</ul>\n";
			}
		}
		return $select;
	}

	function select ($query) {
		return $this->select_ligne($query);
	}

	function select_ligne ($query) {
		$this->connect();
		$ligne=null;
		$select = $this->query($query);
		if ($this->status == OK) {
			$ligne = mysql_fetch_array($select, MYSQL_ASSOC);
		}
		$this->deconnect();
		return $ligne;
	}

	function select_all ($query) {
		$this->connect();
		$ligne=null;
		$lignes=null;
		$select = $this->query($query);
		if ($this->status == OK) {
			while ($ligne = mysql_fetch_array($select, MYSQL_ASSOC)) {
				$lignes[] = $ligne;
			}
		}
		$this->deconnect();
		return $lignes;
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
		$this->query($query);
		$this->deconnect();
	}

	function delete ($query) {
		$this->connect();
		$this->query($query);
		$this->deconnect();
	}
}
?>
