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
	
require_once( LIBTCWEB_DIR."hierarchie.inc.php" );
require_once( LIBTCWEB_DIR."table_ihm.inc.php" );
		
/*
 * Le constructeur de la classe prend en paramètre le nom de la table
 */
class HierarchieIHM extends Hierarchie {

	function HierarchieIHM ($nom) {
		$this->Hierarchie($nom);
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
					$select = new TableIHM($colone['option']);
					//$select->debug=1;
					$tmp = $select->getHtmlSelect($this->nom_table, "libelle", $val);
					$input .= "<td>$tmp</td>";
					//$select->debug();
					unset ($select);
					$titre .= "<th>".ucfirst($colone['nom'])."</th>\n";
					$this->msg.="<li>type select</li>";
					break;
				case "pere":		
					$select = new HierarchieIHM($colone['option']);
					//$select->debug=1;
					$tmp = $select->getHtmlSelect($this->nom_table, "libelle", $val, true);
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
					$this->msg.="<li>type clef</li>";
					break;
				default : 
					$this->msg.="<li>Aucun type trouvé".print_r($colone)."</li>";
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
	function liste () {
		//echo "<table cellspacing=0 cellpadding=0>\n";
		echo "<table>\n";
		$titre = "";
		foreach ($this->lst_champs as $ligne) {
			if ($ligne['type'] == 'clef') {
				// rien à faire
			} elseif ($ligne['type'] == 'select') {
				//TODO: rechercher un champ en varchar à afficher
				$titre .= "<th>".ucfirst($ligne['option'])."</th>\n";
			} else {
				$titre .= "<th>".ucfirst($ligne['nom'])."</th>\n";
			}
			
		}
			
		echo $titre;
		$lst_obj = $this->getPeres();
		$style="lignepaire";
		if (is_array($lst_obj)) {
			$this->msg .= "<li>lister les enregistrements peres</li>";
			foreach ($lst_obj as $ligne) {
				if ($style == "lignepaire") $style="ligneimpaire";
				else $style="lignepaire";
				$ligne->afficheLigne($style, "");
			}
		}
		if ($style == "lignepaire") $style="ligneimpaire";
		else $style="lignepaire";
		?>
		<tr class="<?=$style?>"><td colspan="<?=count($this->lst_champs) -1?>" align=center><a href="<?=$_SERVER['SCRIPT_NAME']."?table=".$this->nom_table?>">Nouvelle ligne</a></td></tr>
		<?php
		echo "</table>";
		
	}

	/**
	 * Affiche une ligne de la liste
	 */
	function afficheLigne ($style, $prefix) {
		$key=$this->trouve_clef();
		if (is_array($this->sous)) {
			$cur_prefix = $prefix.'*';
		} else {
			$cur_prefix = $prefix;
		}
		?>
		<form method=post name=modif action="<?=$_SERVER['SCRIPT_NAME']?>">
			<tr class="<?=$style?>">
			<?php
			foreach ($this->lst_champs as $champ) {
				$clef = $champ['nom'];
				$valeur = $this->{$clef};
				if ($clef == $key) {
					?>
					<input type=hidden value="<?=$valeur?>" name="<?=$this->nom_table."_".$clef?>"/>
					<?php
				} elseif ($champ['type'] == "pere") {
					?>
					<td><?=$this->decodeTree($cur_prefix)?></td>
					<?php
				} else {
					?>
					<td><?=$valeur?></td>
					<?php
				}
			}
		?>
			<td>
			<input type=hidden name="debug" value="<?=$this->debug?>"/>
			<input type=hidden name="table" value="<?=$this->nom_table?>"/>
			<input type=submit value="Modifier">
			</td>
			</tr>
		</form>
		<?php
		if (is_array($this->sous)) {
			$num_count = count($this->sous);
			$cur_num = 0;
			foreach ($this->sous as $fils) {
				$cur_num += 1;
				if ($style == "lignepaire") $style="ligneimpaire";
				else $style="lignepaire";
				$tree=str_replace("+","i",$prefix);
				$tree=str_replace("-",".",$tree);
				$tree=str_replace("`",".",$tree);
				if ($cur_num == $num_count ) {
					$cur_prefix = $tree.'`';
				} else {
					$cur_prefix = $tree.'+';
				}
				$cur_prefix .= '-';
				$fils->afficheLigne($style, $cur_prefix);
			}
		}
	}
	
	/**
	 * Affiche le formulaire et fait les actions qui vont bien
	 * à déplacer dans l'IHM
	 */
	function form_plus () {
		$this->msg .= "<li>form plus</li>";
		$futur_action=AJOUTER;
		// Réaliser l'action à effectuer
		if (isset($_REQUEST['action'])) {
			$cur_action=$_REQUEST['action'];
			if ($cur_action == AJOUTER) $futur_action=MODIFIER;
			if ($cur_action == MODIFIER) $futur_action=MODIFIER;
			if ($cur_action == CHERCHER) $futur_action=AJOUTER;
			// affectation des variables passé en paramètre.
			$this->initFromRequest();
			// mise à jour
			$this->action($cur_action);
			$this->chargeFils();	
		} else {
			// On charge l'enregistrement si son id est passé en paramètre
			$key=$this->nom_table."_".$this->trouve_clef();
			$this->msg .= "<li>recherche '$key' dans _REQUEST</li>";
			if (isset($_REQUEST[$key])) {
				$this->charge($_REQUEST[$key]);
				$futur_action=MODIFIER;
			} else {
				$futur_action=AJOUTER;
			}
		}
		$this->formulaire($futur_action, "colone");

	}

	/**
	 * retourne un <select> avec la liste des enregistrements, utilisé dans le
	 * cas des clés étrangères
	 *
	 * @table : table d'origine 
	 * @champs : le champs à afficher, Attention pas de concat
	 * @selected : l'id de l'option à positionner selected
	 * @pere : indique si c'est une liste des pères ou non
	 */
	function getHtmlSelect ($table, $champs, $selected=null, $pere=false) {
		$ret_val = "";
		$this->msg .= "<li>getHtmlSelect ($table, $champs, $selected)</li>";
		$key=$this->trouve_clef();
		if ($pere == true) {
			$ret_val .= "<select name=\"$table"."_id_pere\">\n";
			$ret_val .= "<option value=\"null\">racine</option>\n";
		} else {
			$ret_val .= "<select name=\"$table"."_"."$key\">\n";
		}
		
		$peres = $this->getPeres();
		foreach ($peres as $pere) {
			$id = $pere->{$key};
			$val = $pere->{$champs};
			if ($id == $selected) $str_sel="selected";
			else $str_sel="";
			$ret_val .= "<option $str_sel value=\"$id\">- $val</option>";
			$ret_val .= $pere->getHtmlSousSelect($selected, $champs, "--");
		}
		$ret_val .= "</select>";
		return $ret_val;
	}

	/**
	 * retourne les éléments options des éléments fils
	 */
	function getHtmlSousSelect($selected, $champs, $prefix) {
		$this->msg .= "<li>getHtmlSousSelect</li>";
		$key=$this->trouve_clef();

		$ret_val = "";
		if (is_array($this->sous)) {
			foreach ($this->sous as $fils) {
				$this->msg .= "<li>lister les fils </li>";
				$id = $fils->{$key};
				$val = $fils->{$champs};
				if ($id == $selected) $str_sel="selected";
				else $str_sel="";
				$ret_val .= "<option $str_sel value=\"$id\">$prefix $val</option>";
				$ret_val .= $fils->getHtmlSousSelect($selected, $champs, $prefix.$prefix);
			}
		}
		return $ret_val;
	}

	function decodeTree($tree) {
		$return="";
		for ($o=0 ; $o<strlen($tree) ; $o++) {
			$return .= '<img src="'.LIBTCWEB_IMG_DIR;
			$k=substr($tree,$o,1);
			$alt=$k;
			switch ($k) {
				case "o":
					$return .= 'k1.gif';
					break;
				case "*":
					$return .= 'k2.gif';
					break;
				case "i":
					$return .= 'I.gif';
					$alt='|';
					break;
				case "-":
					$return .= 's.gif';
					break;
				case "+":
					$return .= 'T.gif';
					break;
				case "`":
					$return .= 'L.gif';
					break;
				case ".":
					$return .= 'e.gif';
					$alt='&nbsp;';
					break;
			}
			$return .= '" alt="'.$alt.'" class="thread_image"';
			//if (strcmp($k,".") == 0) $return .=(' width="9" height="21"');
			$return .=(' width="12" height="21"');
			$return .= '>';
		}
		return($return);
	}

	

}
?>
