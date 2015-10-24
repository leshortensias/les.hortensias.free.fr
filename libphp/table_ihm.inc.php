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

if (!defined('LIBTCWEB_IMG_DIR'))
  define('LIBTCWEB_IMG_DIR', "img".DIR_SEP);
	
require_once( LIBTCWEB_DIR."table.inc.php" );
		
/*
 * Le constructeur de la classe prend en paramètre le nom de la table
 */
class TableIHM extends Table {

	function TableIHM ($nom) {
		$this->Table($nom);
	}

	/*
	 * Affiche le formulaire de saisie d'un enregistrement, l'action par défaut
	 * c'est ajouter.
	 */
	function formulaire ($action = AJOUTER, $mode="ligne") {
		$this->msg.="<li>Affichage du formulaire, action=$action</li>";
		if ($action == MODIFIER) $bouton="Modifier";
		else $bouton="Ajouter";
		$titre = "";
		$input = "";
		$hidden = "";
		$ligne = "";
		foreach ($this->lst_champs as $colone) {
			if ($mode != "ligne") {
				$ligne .= "<tr>$titre$input</tr>\n";
				$titre = "";
				$input = "";
			}
			$nom = $colone['nom'];
			if ( isset ($this->{$colone['nom']})) $val = $this->{$colone['nom']};
			else $val ="";
			switch ($colone['type']) {
				case "enum":
					$input .= '<td><select name="'.$this->nom_table."_".$nom.'">';
					// récupérer la liste des valeurs possible
					$lst = explode("','",$colone['option']);
					foreach ($lst as $option) {
						if ($option == $val ) $selected = "selected";
						else  $selected = "";
						$input .= "<option value=\"$option\" $selected>".ucfirst($option)."</option>";
					}
					$input .= "</select></td>\n";
					$titre .= "<th>".ucfirst($colone['nom'])."</th>\n";
					$this->msg.="<li>type enum</li>";
					break;
				case "select":		
					$className = get_class($this);
					$select = new $className ($colone['option']);
					//$select->debug=1;
					$tmp = $select->getHtmlSelect($this->nom_table, "libelle", $val, $nom);
					$input .= "<td>$tmp</td>";
					//$select->debug();
					unset ($select);
					$titre .= "<th>".ucfirst($colone['nom'])."</th>\n";
					$this->msg.="<li>type select</li>";
					break;
				case "text":
					// cas générique : texte
					$input .= "<td><input type=\"text\" name=\"".$this->nom_table."_".$colone['nom']."\" value=\"".stripslashes($val)."\"/></td>\n";
					$this->msg.="<li>type text</li>";
					$titre .= "<th>".ucfirst($colone['nom'])."</th>\n";
					break;
				case "sized":
					// cas varchar donc d'une taille fixe
					$size = $colone['option'];
					if ($size > 100) $size = 100;
					$input .= "<td><input type=\"text\" name=\"".$this->nom_table."_".$colone['nom']."\" value=\"".stripslashes($val)."\" size=\"$size\" maxlength=\"$size\" /></td>\n";
					$this->msg.="<li>type sized</li>";
					$titre .= "<th>".ucfirst($colone['nom'])."</th>\n";
					break;
				case "clef":
					$hidden .= '<input type=hidden name="'.$this->nom_table."_".$colone['nom'].'" value="'.$val.'"/>';
					$this->msg.="<li>type clef, pas d'affichage</li>";
					break;
				case "tech":
					$this->msg.="<li>type date technique, rien à  afficher</li>";
					break;
				case "area":
					// cas area, utlisation d'htmlarea si disponible
					$ta = $colone['option'];
					$input .= "<td><textarea name=\"".$this->nom_table."_".$colone['nom']."\" id=\"$ta\" rows=\"23\" cols=\"80\" style=\"width:100%\">".stripslashes($val)."</textarea></td>\n";
					$this->msg.="<li>type area</li>";
					$titre .= "<th>".ucfirst($colone['nom'])."</th>\n";
					break;
				default : 
					$this->msg.="<li>Aucun type trouvé ".print_r($colone)."</li>";
					break;
			}
		}
		// ajouter le dernier champs
		$ligne .= "<tr>$titre$input</tr>\n";
		?>
		<form method=post name=action action="<?=$_SERVER['SCRIPT_NAME']?>">
			<div class=form>
				<table>
				<?php
				if ($mode != "ligne") {
				?>
				<?=$ligne?>
				<?php
				} else {
				?>
				<tr><?=$titre?></tr> 
				<tr><?=$input?></tr> 
				<?php
				}
				?>
				</table>
				<input type=hidden name="table" value="<?=$this->nom_table?>"/>
				<input type=hidden name="debug" value="<?=$this->debug?>"/>
				<?=$hidden?>
				<button name=action type=submit value="<?=$action?>"><?=$bouton?></button>
				<button name=action type=submit value="<?=CHERCHER?>">Chercher</button>
				<button type=reset>Annuler</button>
			</div>
		</form>
		<?php
	}

	/**
	 * Affiche une liste des enregistrements présent dans la table
	 */
	function liste ($limite = 0, $edit_page = 'self') {
		echo "<table>\n";
		// Création de la ligne de titre
		$titre = "<tr>";
		foreach ($this->lst_champs as $ligne) {
			if ($ligne['type'] == 'clef') {
				$key=$this->trouve_clef();
			} elseif ($ligne['type'] == 'select') {
				$titre .= "<th>".ucfirst($ligne['option'])."</th>\n";
			} else {
				$titre .= "<th>".ucfirst($ligne['nom'])."</th>\n";
			}
		}
		$titre .= "</tr>\n";
		echo $titre;
		// lister tous les objets de la table
		$lst_lignes = $this->getTousObj();
		// afficher un ligne par objet
		$style="lignepaire";
		if (is_array($lst_lignes)) {
			$this->msg .= "<li>lister les enregistrements de la table</li>";
			foreach ($lst_lignes as $ligne) {
				if ($style == "lignepaire") $style="ligneimpaire";
				else $style="lignepaire";
				$ligne->afficheLigne($style, $edit_page);
			}
		}
		if ($style == "lignepaire") $style="ligneimpaire";
		else $style="lignepaire";
		?>
		<tr class="<?=$style?>"><td colspan="<?=count($this->lst_champs) -1?>" align=center><a href="<?=$edit_page."?table=".$this->nom_table?>">Nouvelle ligne</a></td></tr>
		<?php
		echo "</table>\n";

		
	}

	function afficheLigne ($style, $edit_page= 'self') {
		if ($edit_page == 'self') $edit_page = $_SERVER['SCRIPT_NAME'];
		$key=$this->trouve_clef();
		?>
		<tr class="<?=$style?>">
		<?php
		foreach ($this->lst_champs as $champ) {
			if ($champ['type'] == 'select') {
				$clef = $champ['nom'];
				$className = get_class($this);
				$obj = new $className ($champ['option']);
				$obj->charge($this->{$clef});
				$valeur = $obj->getLibelle();
			} elseif ($champ['type'] == 'area') {
				$clef = $champ['nom'];
				$valeur = htmlentities(substr($this->{$clef},0,50))."...";
			} elseif ($champ['type'] == 'clef') {
				$clef = $champ['nom'];
				$valeur_clef = $this->{$clef};
				$param_clef = $this->nom_table."_".$clef;
			} else {
				$clef = $champ['nom'];
				$valeur = $this->{$clef};
			}
			if ($clef != $key) {
				?>
					<td><?=$valeur?></td>
					<?php
			}
		}
		if (isset($this->debug)) $debug="&debug=".$this->debug;
		?>
			<td>
			<a href="<?=$edit_page?>?<?=$param_clef?>=<?=$valeur_clef?>&table=<?=$this->nom_table?><?=$debug?>"><img src="<?=LIBTCWEB_IMG_DIR?>edit.png" alt="Modifier" title="Modifier" border="0"/></a>
			</td>
			</tr>
			</form>
			<?php
	}
	
	/**
	 * Affiche le formulaire et fait les actions qui vont bien
	 */
	function form_plus () {
		$this->msg .= "<li>form plus\n<ul>";
		$futur_action=AJOUTER;
		// Réaliser l'action à effectuerer
		if (isset($_REQUEST['action'])) {
			$cur_action=$_REQUEST['action'];
			if ($cur_action == AJOUTER) $futur_action=MODIFIER;
			if ($cur_action == MODIFIER) $futur_action=MODIFIER;
			if ($cur_action == CHERCHER) $futur_action=AJOUTER;
			// affectation des variables passé en paramètre.
			$this->initFromRequest();
			// mise à jour
			$this->action($cur_action);
		} else {
			// On charge l'enregistrement si son id est passé en paramètre
			$key=$this->nom_table."_".$this->trouve_clef();
			$this->msg .= '<li>recherche "'.$key.'" dans $_REQUEST</li>';
			if (isset($_REQUEST[$key])) {
				$this->charge($_REQUEST[$key]);
				$futur_action=MODIFIER;
			} else {
				$futur_action=AJOUTER;
			}
		}
		$this->formulaire($futur_action, "colone");
		$this->msg .= "</ul></li>\n";

	}

	/**
	 * retourne un <select> avec la liste des enregistrements, utilisé dans le
	 * cas des clés étrangères
	 *
	 * @table : table d'origine 
	 * @champs : le champs à afficher
	 * @selected : l'id de l'option à positionner selected
	 * TODO : réécrire la fonction avec getTousObj();
	 */
	function getHtmlSelect ($table, $champs, $selected=null, $nom_input = null) {
		$ret_val = "";
		$this->msg .= "<li>getHtmlSelect ($table, $champs, $selected)<ul>";
		// vérifier que $champs existe
		$tmp_champs = "";
		foreach ($this->lst_champs as $cur_champs) {
			if ($cur_champs['nom'] == $champs) {
				// Le champs existe
				$tmp_champs = $champs;
			} elseif ($cur_champs['type'] == 'sized' || $cur_champs['type'] == "text") {
				if ($tmp_champs == "") {
					//$champs pas encore trouvé => on prend ce champs temporaire
					$tmp_champs = $cur_champs['nom'];
				}
			}
		}
		$champs = $tmp_champs;
		$key=$this->trouve_clef();
		if ($nom_input == null) $nom_input = $key;
		$ret_val .= "<select name=\"$table"."_"."$nom_input\">\n";
		
		$query = "select $key, $champs as lib from $this->nom_table order by lib ";
		$this->msg .= "<li>query : $query</li>";
		$lignes = $this->select_all($query);
		if ($this->status == OK) {
			$this->msg .= "<li>lister les enregistrements <ul>";
			foreach ($lignes as $ligne) {
				if ($ligne[$key] == $selected) $str_sel="selected";
				else $str_sel="";
				$ret_val .= "<option $str_sel value=\"".$ligne[$key]."\">".$ligne['lib']."</option>";
				$this->msg .= "<li>".$ligne[$key]." : ".$ligne['lib']."</li>";
			}
			$this->msg .= "</ul></li>";
		}
		$ret_val .= "</select>";
		$this->msg .= "</ul></li>";
		return $ret_val;
	}

	function getLibelle () {
		$this->msg .= "<li>getLibelle</li>";
		$champs = "libelle";
		$tmp_champs = "";
		foreach ($this->lst_champs as $cur_champs) {
			if ($cur_champs['nom'] == $champs) {
				// Le champs existe
				$tmp_champs = $champs;
			} elseif ($cur_champs['type'] == 'sized' || $cur_champs['type'] == "text") {
				if ($tmp_champs == "") {
					//$champs pas encore trouvé => on prend ce champs temporaire
					$tmp_champs = $cur_champs['nom'];
				}
			}
		}
		$champs = $tmp_champs;
		$this->msg .= "<li>Trouvé libellé : $champs</li>";
		if (isset($this->{$champs})) return $this->{$champs};
		else return "-";
	}
}
?>
