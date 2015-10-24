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

if (!defined('DIR_SEP'))
  define('DIR_SEP', DIRECTORY_SEPARATOR);

if (!defined('LIBTCWEB_DIR'))
  define('LIBTCWEB_DIR', dirname(__FILE__) . DIR_SEP);

require_once( LIBTCWEB_DIR."table.inc.php" );
		
/*
 * Le constructeur de la classe prend en paramètre le nom de la table
 *
 * Pour qua la classe fonctionne, il faut que le champs qui pointe vers l'id du
 * père soit "id_pere"
 */
class Hierarchie extends Table {
	var $sous; //Ensemble des éléments présent en dessous dans la hiérarchie
	
	function Hierarchie ($nom) {
		$this->Table($nom);
		$this->lst_champs['id_pere']['type']="pere";
		$this->lst_champs['id_pere']['option']=$this->nom_table;
	}

	function charge ($id) {
		parent::charge($id);
		$this->chargeFils ($id);
	}

	function chargeFils () {
		//trouver le nom de la clef
		$key=$this->trouve_clef();
		$id=$this->{$key};
		$this->msg .= "<li>Chargement des fils de $id</li>";
		//select dans la table
		$query = "select $key from $this->nom_table where id_pere='$id'";
		$lignes = $this->select_all($query);
		if ($this->status == OK) {
			if (is_array ($lignes) ) {
				foreach ($lignes as $ligne) {
					$className = get_class($this);
					$tmp = new $className ($this->nom_table);
					$tmp->charge($ligne[$key]);
					$this->sous[] = $tmp;
				}
			}
		}
	}

	/**
	 * retourne un tableau d'objets avec la liste des enregistrements pères de la
	 * table, utilisé pour construire l'ensemble de la hiérarchie
	 */
	function getPeres () {
		$this->msg .= "<li>getTous ()</li>";
		$key=$this->trouve_clef();
		$where = $this->chercher();
		$ret_val ='';
		
		$query = "select $key from $this->nom_table where (id_pere=0 or id_pere is null) $where";
		$lignes = $this->select_all($query);
		if ($this->status == OK) {
			$this->msg .= "<li>lister les enregistrements </li>";
			if (is_array($lignes)) {
				foreach ($lignes as $ligne) {
					$className = get_class($this);
					$obj = new $className ($this->nom_table);
					$obj->charge($ligne[$key]);
					$ret_val[] = $obj;
				}
			}
		}
		return $ret_val;
	}

	function pere ($champs) {
		$this->msg .= "<li>trouver le libellé du père ()</li>";
		$key=$this->trouve_clef();
		$ret_val = "";
		$query = "select $champs from $this->nom_table where id_pere=".$this->{$key};
		$ligne = $this->select_ligne($query);
		if ($this->status == OK) {
			$ret_val = $ligne[$champs];
		}
		return $ret_val;
		
	}

}
?>
