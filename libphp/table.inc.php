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

require_once( LIBTCWEB_DIR."dbobject.inc.php" );

/*
 * Le constructeur de la classe prend en paramètre le nom de la table
 */
class Table extends DbObject {
	var $nom_table;
	var $lst_champs;	// un tableau avec la liste des champs et leur type
										// d'affichage ('nom', 'type', 'option')
	var $action;      // L'action courante sur l'objet
	//Les données sont dynamiquement sauvés dans des variables d'instance avec
	//les noms des champs. 
	var $selected;		// Utilisé pour indiqué dans une liste, quel est l'objet de départ.

	function Table ($nom) {
		$this->DbObject();
		$this->nom_table=$nom;
		$this->charge_champs ();	
		if (isset ($_REQUEST['debug']))
			$this->debug=$_REQUEST['debug'];
	}

	// Trouve la clef et charge la liste des champs
	function charge_champs () {
		$this->msg .= "<li>Chargement des champs <ul>";
		// charge la liste des tables du schéma
		$query = "show tables";
		$lst_tables = $this->select_all($query);
		
		// charge la liste des colonnes de la table	
		$query = "show columns from $this->nom_table";
		$lignes = $this->select_all($query);
		if ($this->status == OK) {
			foreach ($lignes as $ligne) {
				$nom_champs=$ligne['Field'];
				if ($ligne['Key'] == 'PRI') {
					$this->lst_champs[$nom_champs]['nom']=$nom_champs;
					$this->lst_champs[$nom_champs]['type']="clef";
					$this->msg .= "<li>Clef primaire trouvé : $nom_champs</li>";
				} elseif ( substr($ligne['Type'],0,4) == "enum" ) {
					// Champs de type énuméré : récupérer la liste des valeurs possible
					$lst = preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$ligne['Type']);
					$this->lst_champs[$nom_champs]['nom']=$nom_champs;
					$this->lst_champs[$nom_champs]['type']="enum";
					$this->lst_champs[$nom_champs]['option']=$lst;
					$this->msg .= "<li>Enum trouvé : $nom_champs</li>";
				} elseif ( substr($ligne['Type'],0,7) == "varchar" ) {
					// Champs de type sized
					$size = preg_replace("/(varchar)\((.+?)\)/","\\2",$ligne['Type']);
					$this->lst_champs[$nom_champs]['nom']=$nom_champs;
					$this->lst_champs[$nom_champs]['type']="sized";
					$this->lst_champs[$nom_champs]['option']=$size;
					$this->msg .= "<li>sized trouvé : $nom_champs, $size</li>";
				} elseif ( substr($ligne['Type'],-4) == "text" ) {
					// champs de type text, longtext ... 
					$this->lst_champs[$nom_champs]['nom']=$nom_champs;
					$this->lst_champs[$nom_champs]['type']="area";
					$this->lst_champs[$nom_champs]['option']="ta_$nom_champs";
					$this->msg .= "<li>area trouvé : $nom_champs</li>";
				} elseif ( $ligne['Field'] == "date_modif" || $ligne['Field'] == "date_creation" ) {
					// champs non modifiable : date technique
					$this->lst_champs[$nom_champs]['nom']=$nom_champs;
					$this->lst_champs[$nom_champs]['type']="tech";
					$this->msg .= "<li>area trouvé : $nom_champs</li>";
				} else {
					//
					// autre type de champs. On recherche une clef étrangère de la forme :
					// id_<nom de la table au singulié>
					//
					$is_clef = false;
					foreach ($lst_tables as $ligne_table) {
						$nom_table = $ligne_table['Tables_in_'.$this->db_name];
						if ("id_$nom_table" == $ligne['Field']."s") {
							$this->lst_champs[$nom_champs]['nom']=$nom_champs;
							$this->lst_champs[$nom_champs]['type']="select";
							$this->lst_champs[$nom_champs]['option']=$nom_table;
							$this->msg .= "<li>Clef étrangère trouvé : $nom_champs</li>";
							$is_clef = true;
						}
					}
					if (! $is_clef) {
						// si ce n'est pas une clef, on passe sur un type générique
						$this->lst_champs[$nom_champs]['nom']=$nom_champs;
						$this->lst_champs[$nom_champs]['type']="text";
						$this->msg .= "<li>type générique : $nom_champs - ".$ligne['Type']."</li>";
					}
				}
			}
		} 
		$this->msg .= "</ul></li>";
	}
	
	/**
	 * Cette methode recherche la clef primaire de la table, elle retourne le nom
	 * du champ.
	 */ 
	function trouve_clef () {
		$key = "";
		foreach ($this->lst_champs as $valeur) {
			if ($valeur['type'] == 'clef') $key=$valeur['nom'];
		}
		return $key;
	}
	
	/**
	 * Chargement d'un enregistrement depuis l'id
	 */
	function charge ($id) {
		$this->msg.="<li>Charge l'enregistrement : $id</li>";
		//trouver le nom de la clef
		$key=$this->trouve_clef();
		//select dans la table 
		$query = "select * from $this->nom_table where $key='$id'";
		$ligne = $this->select_ligne($query);
		if ($this->status == OK) {
			if (is_array ($ligne)) {
				foreach ($ligne as $nom => $valeur ) {
					$this->{$nom}=$valeur;
				}
			} else {
				$this->msg .= "<li>L'enregistrement n'exise pas</li>";
			}
		}
	}

	/**
	 * Enregistrement en base de l'objet courant (update)
	 */
	function sauve () {
		$key=$this->trouve_clef();
		if (isset ($this->{$key}) && $this->{$key} > 0 ) {
			// si la clef existe et est positive, c'est un update
			$this->msg.="<li>Update de l'enregistrement</li>";
			$query = "update $this->nom_table set ";
			$nb_champs = count($this->lst_champs);
			$i = 0;
			foreach ($this->lst_champs as $champs ) {
				$nom = $champs['nom'];
				if (isset ($this->{$nom})) $val = $this->{$nom};
				else $val=null;
				if ($nom == "date_modif") $val = date('Y-m-d H:i:s');
				$i += 1;
				if ($i == $nb_champs) { 
					$query .= "`$nom`='$val' ";
				} else {
					$query .= "`$nom`='$val', ";
				}
			}
			$query.= "where $key='".$this->{$key}."'";
			$ligne = $this->update($query);
			if ($this->status == OK) {
				$this->usrmsg = "Mise à jour effectué";
			} else {
				$this->usrmsg = "Mise à jour échoué";
			}
		} else {
			// sinon, c'est un insert	
			$this->msg.="<li>Insert de l'enregistrement </li>";
			$query = "insert into $this->nom_table ";
			$liste_champs = " (";
			$liste_valeurs = " values (";
			$nb_champs = count($this->lst_champs);
			$i = 0;
			foreach ($this->lst_champs as $champs ) {
				$nom = $champs['nom'];
				if (isset ($this->{$nom})) $val = $this->{$nom};
				else $val=null;
				if ($nom == "date_creation") $val = date('Y-m-d H:i:s');
				$i += 1;
				if ($i == $nb_champs) { 
					$liste_champs .= "`$nom`) ";
					$liste_valeurs .= "'$val') ";
				} else {
					$liste_champs .= "`$nom`, ";
					$liste_valeurs .= "'$val', ";
				}
			}
			$query .= $liste_champs.$liste_valeurs;
			$ligne = $this->insert($query);
			if ($this->status == OK) {
				$this->usrmsg = "Ajout effectué";
				$this->{$key} = $ligne;
				$this->msg.="<li> Ajout OK, '$key' = ".$this->{$key}."</li>";
			} else {
				$this->usrmsg = "Ajout échoué";
				$this->msg.="<li> Ajout KO </li>";
			}
		}
	}

	/**
	 * Méthode qui encapsule le lancement d'une action, et vérifier que cette
	 * action est possible.
	 */
	function action ($action) {
		// Réaliser l'action à effectuer
		if (isset($action)) {
			$this->action=$action;
			switch ($action) {
				case AJOUTER :
					$this->msg.="<li>Action Ajouter</li>";
					$this->sauve();
					break;
				case MODIFIER :
					$this->msg.="<li>Action Modifier</li>";
					$this->sauve();
					break;
				case EFFACER :
					$this->msg.="<li>Action Effacer</li>";
					$this->effacer();
					break;
				case CHERCHER :
					$this->msg.="<li>Positionnement de l'indicateur de recherche, appeller la méthode chercher pour avoir la clause where</li>";
					break;
				default:
					$this->status=KO;
					$this->msg.="<li>Action Impossible : $action</li>";
					break;
			}
		}
	}

	/**
	 * Initialisation des champs de l'objet avec les éléments présent dans
	 * $_REQUEST
	 * les champs dans $_REQUEST sont de la forme : 
	 * 	<nom de la table>_<nom du champs> ou <nom de la table>_action
	 */
	function initFromRequest () {
		$this->msg.="<li>Initialisation depuis _REQUEST : <ul>";
		foreach ($_REQUEST as $clef => $valeur) {
			if (substr($clef,0,strlen($this->nom_table)+1) == $this->nom_table."_" ) {
				$variable = substr($clef, strlen($this->nom_table)+1);
				$this->msg.="<li>$variable : $valeur </li>";
				$this->{$variable} = $valeur;
			}
		}
		$this->msg.="</ul></li>";
	}

	/**
	 * retourne un tableau associatif 'clef' => 'libelle' avec la liste des
	 * enregistrements de la table, utilisé dans le cas des clés étrangères pour
	 * construire un select
	 *
	 * @champs : le champs à afficher ou une concaténation des champs 
	 * concat('champs1', 'champs2')
	 */
	function getTousTab ($champs) {
		$this->msg .= "<li>getTous ($champs)</li>";
		$key=$this->trouve_clef();
		
		$query = "select $key, $champs as lib from $this->nom_table order by lib ";
		$lignes = $this->select_all($query);
		if ($this->status == OK) {
			$this->msg .= "<li>lister les enregistrements </li>";
			foreach ($lignes as $ligne) {
				$ret_val[$ligne[$key]] = $ligne['lib'];
			}
		}
		return $ret_val;
	}

	/**
	 * retourne un tableau associatif 'clef' => 'libelle' avec la liste des
	 * enregistrements de la table, utilisé dans le cas des clés étrangères pour
	 * construire un select
	 *
	 * TODO : optimiser le chargement de cette liste d'objet
	 */
	function getTousObj () {
		$this->msg .= "<li>getTous </li>";
		$key=$this->trouve_clef();
		$ret_val = "";	
		$ordre = $this->trouve_ordre();
		$chercher = $this->chercher();
		$query = "select $key from $this->nom_table where 1=1 $chercher $ordre";
		$lignes = $this->select_all($query);
		if ($this->status == OK) {
			$this->msg .= "<li>lister les enregistrements </li>";
			foreach ($lignes as $ligne) {
				$className = get_class($this);
				$obj = new $className ($this->nom_table);
				$obj->charge($ligne[$key]);
				if (isset($this->{$key}) && $this->{$key} == $ligne[$key]) $obj->selected="1";
				$ret_val[] = $obj;
			}
		}
		return $ret_val;
	}

	/**
	 * Renvoie une clause where de recherche en fonction des champs renseignés.
	 */
	function chercher () {
		$ret_val = " ";
		if ($this->action == CHERCHER) {
			foreach ($this->lst_champs as $colone) {
				$champs = $colone['nom'];
				$valeur = $this->{$champs};
				if ($valeur != "" ) {
					switch ($colone['type']) {
						case "text":
							// cas générique : texte
							$ret_val .= " and ".$this->nom_table.".$champs like '%$valeur%' ";
							break;
						case "sized":
							// cas sized donc d'une taille fixe
							$ret_val .= " and ".$this->nom_table.".$champs like '%$valeur%' ";
							break;
						case "clef":
							// cas de la clef, on ne fait rien ...
							break;
						default:
							$ret_val .= " and ".$this->nom_table.".$champs='$valeur' ";
					}
				}
			}
		}

		return $ret_val;
	}

	/**
	 * Cette methode recherche lun champ ordre qui sera utilisé pour ordoner les
	 * résultats
	 */ 
	function trouve_ordre () {
		$ordre = "";
		foreach ($this->lst_champs as $valeur) {
			if ($valeur['nom'] == 'ordre') $ordre = " order by ordre ";
		}
		return $ordre;
	}
	
}
?>
