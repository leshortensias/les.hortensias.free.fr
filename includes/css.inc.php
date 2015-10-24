<?php
require_once("includes/dbobject.inc.php");

class Css extends DbObject {
	// les champs de l'objets ...
	var $corps;
	var $id;
	var $nom;

	function Css ($id = -1) {
		$this->DbObject ();
		if ($id > -1) {
			$this->charge ($id);
		}
	}
	
	// On charge la page de style depuis la base
	function charge ($id) {
		//select dans la table css
		$this->msg .= "<li>Charge la page css depuis la base (id=$id)</li>";
		$query = "select `corps`, `nom` from css where id_css='$id'";
		$ligne = $this->select($query);
		if ($this->status == OK) {
			$this->corps=$ligne["corps"];
			$this->nom=$ligne["nom"];
			$this->id=$id;
		}
	}

	// affichage de la page de style
	function affiche () {
		echo "<style type=\"text/css\">\n";
		echo $this->corps;
		echo "</style>\n";
	}

	function sauve () {
		$query = "update css ";
		$query.= "set `nom`='$this->nom', ";
		$query.= "`corps`='$this->corps' ";
		$query.= "where id_css='$this->id'";
		$ligne = $this->update($query);
	}

	function ajoute () {
		$query = "insert into css ";
		$query.= "        (`corps`,        `nom`) "; 
		$query.= " values ('$this->corps', '$this->nom') "; 
		$ligne = $this->insert($query);
	}

	// Formulaire de saisie d'une page de style, l'action par d&eacute;faut c'est ajouter.
	function formulaire ($action = AJOUTER) {
		$this->msg.="<li>Affichage du formulaire, action=$action</li>";
		if ($action == MODIFIER) $bouton="Modifier";
		else $bouton="Ajouter";
		?>
		<form method=post name=action action="<?=$_SERVER['SCRIPT_NAME']?>">
			<div class=cssForm>
				<table style="width:100%">
				<tr><td> 
					Nom : 
				</td><td> 
					<input type=text name="nom" value="<?=stripslashes($this->nom)?>" style="width:100%"/>
				</td></tr><tr><td colspan=2>
				<textarea id="corps" name="corps" rows="25" cols="80" style="width:100%"><?=stripslashes($this->corps)?></textarea>
				</td></tr></table>
				<input type=hidden name="action" value=<?=$action?>>
				<input type=hidden name="debug" value=<?=$this->debug?>>
				<input type=hidden name="id" value=<?=$this->id?>>
				<input type=submit value="<?=$bouton?>">
			</div>
		</form>
		<?php
	}

	function action ($action) {
		// R&eacute;aliser l'action &agrave; effectuer
		if (isset($action)) {
			switch ($action) {
				case AJOUTER :
					$this->msg.="<li>Action Ajouter</li>";
					$this->ajoute();
					break;
				case MODIFIER :
					$this->msg.="<li>Action Modifier</li>";
					$this->sauve();
					break;
				default:
					$this->status=KO;
					$this->msg.="<li>Action Impossible : $action</li>";
					break;
			}
		}
	}

	function initFromRequest () {
		$this->corps=$_REQUEST['corps'];
		$this->nom=$_REQUEST['nom'];
		$this->id=$_REQUEST['id'];
	}

	function liste () {
		$this->connect();
		$query = "select `id_css`, `corps`, `nom` from css order by `nom`";
		$select = $this->query($query);
		if ($this->status == OK) {
			$this->msg .= "<li>lister les pages de style</li>";
			echo "<table>\n";
			echo "<tr><th>Nom</th></tr>";
			$style="lignepaire";
			while ($ligne = mysql_fetch_array($select)) {
				if ($style == "lignepaire") $style="ligneimpaire";
				else $style="lignepaire";
				?>
					<tr class="<?=$style?>">
					<td><a href="admin_css.php?id_css=<?=$ligne['id_css']?>"><?=$ligne["nom"]?></a></td>
					</tr>
					<?php
			}
			echo "<tr><td colspan=3><a href=\"admin_css.php\">Nouvelle page de style</a></td>";
			echo "</table>";
		}
		$this->deconnect();
	}
		
	function listeSelect () {
		$this->connect();
		$query = "select `id_css`, `corps`, `nom` from css order by `nom`";
		$select = $this->query($query);
		if ($this->status == OK) {
			$this->msg .= "<li>Dropdown des pages de style</li>";
			echo "<select name=id_css>\n";
			while ($ligne = mysql_fetch_array($select)) {
				if ($ligne['id_css'] == $this->id) $str_sel="selected";
				else $str_sel = "";
				?>
					<option value="<?=$ligne['id_css']?>" <?=$str_sel?>><?=$ligne["nom"]?></option>
					<?php
			}
			echo "</select>\n";
		}
		$this->deconnect();
	}
		
}
?>
