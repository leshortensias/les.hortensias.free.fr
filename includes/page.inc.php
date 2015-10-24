<?php
require_once("includes/dbobject.inc.php");
require_once("includes/css.inc.php");

class Page extends DbObject {
	// les champs de l'objets ...
	var $corps;
	var $id;
	var $mots_clef;
	var $titre;
	var $autheur;
	var $css;
	var $description;

	function Page () {
		$this->DbObject ();
		$this->css = new Css ();
	}
		
	// On charge la page depuis la base
	function charge ($id) {
		//select dans la table pages
		$query = "select `mots_clef`, `titre`, `autheur`, `id_css`, `description`, `corps`, `nom` from pages where id_page='$id'";
		$ligne = $this->select($query);
		if ($this->status == OK) {
			$this->corps=$ligne["corps"];
			$this->nom=$ligne["nom"];
			$this->mots_clef=$ligne["mots_clef"];
			$this->titre=$ligne["titre"];
			$this->autheur=$ligne["autheur"];
			$this->css=new Css ($ligne["id_css"]);
			$this->description=$ligne["description"];
			$this->id=$id;
		}
	}

	function chargeDepuisNom ($nom) {
		//select dans la table pages
		$this->msg.="<li>Charge la page $nom</li>";
		$query = "select `id_page`, `mots_clef`, `titre`, `autheur`, `id_css`, `description`, `corps`, `nom` from pages where nom='$nom'";
		$ligne = $this->select($query);
		if ($this->status == OK) {
			if ($ligne == FALSE) {
				$this->corps="<style>#menu {float: right} h1 {margin-bottom: 3em}</style><h1>Page non trouv&eacute;.</h1>";
				$this->titre="Page non trouv&eacute;";
			} else {
				$this->corps=$ligne["corps"];
				$this->nom=$ligne["nom"];
				$this->mots_clef=$ligne["mots_clef"];
				$this->titre=$ligne["titre"];
				$this->autheur=$ligne["autheur"];
				$this->css=new Css ($ligne["id_css"]);
				$this->description=$ligne["description"];
				$this->id=$ligne["id"];
			}
		}
	}

	// affichage de la page
	function affiche () {
		?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title><?=$this->titre?></title>
	<meta name="author" content="<?=$this->autheur?>">
	<meta name="copyright" content="<?=$this->autheur?>">
	<meta name="keyword" content="<?=$this->mots_clef?>">
	<meta name="description" content="<?=$this->description?>">
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-15">
	<?=$this->css->affiche()?>
</head>
<body>
	<?php include("includes/menu.php") ?>
	<div id=page>
		<?php
		echo $this->corps;
		switch ($this->nom) { 
			case "news" : 
				require_once("includes/nouvelle.inc.php");
				$news = new Nouvelle();
				$news->liste();
				break;
			case "or" : 
				require_once("includes/message.inc.php");
				$or = new Message();
				$or->debug = 1;
				$or->liste();
				$or->initFromRequest();
				// R&eacute;aliser l'action &agrave; effectuer
				if (isset($_REQUEST['action'])) {
					$or->action($_REQUEST['action']);
				}
				echo "<h2>Ajouter votre message</h2>";
				$or->formulaire(AJOUTER, 0);
				break;
		}
		?>
	</div>
<hr>
<?php include("includes/menuText.php") ?>
<hr>
<?php include("includes/pied.php") ?>
</body>
</html>
		<?php
	}

	function sauve () {
		$query = "update pages ";
		$query.= "set `mots_clef`='$this->mots_clef', ";
		$query.= "`titre`='$this->titre', ";
		$query.= "`autheur`='$this->autheur', ";
		$query.= "`id_css`='".$this->css->id."', ";
		$query.= "`description`='$this->description', ";
		$query.= "`corps`='$this->corps', ";
		$query.= "`nom`='$this->nom' ";
		$query.= "where id_page='$this->id'";
		$ligne = $this->update($query);
	}

	function ajoute () {
		$query = "insert into pages ";
		$query.= "        (`mots_clef`,        `titre`,        `autheur`,        `id_css`,        `description`,        `corps`,        `nom`) "; 
		$query.= " values ('$this->mots_clef', '$this->titre', '$this->autheur', '".$this->css->id."', '$this->description', '$this->corps', '$this->nom') "; 
		$ligne = $this->insert($query);
	}

	// Formulaire de saisie d'une page, l'action par d&eacute;faut c'est ajouter.
	function formulaire ($action = AJOUTER) {
		$this->msg.="<li>Affichage du formulaire, action=$action</li>";
		if ($action == MODIFIER) $bouton="Modifier";
		else $bouton="Ajouter";
		?>
		<script type="text/javascript">
			_editor_url = "/htmlarea/";
			_editor_lang = "fr";
		</script>
		<script type="text/javascript" src="/htmlarea/htmlarea.js"></script>
		<script type="text/javascript" src="/htmlarea/lang/en.js"></script>

		<script type="text/javascript">
			var editor = null;
			HTMLArea.loadPlugin("TableOperations");
			function initEditor() {
				editor = new HTMLArea("corps");
				editor.registerPlugin(TableOperations);
				// That's it, pretty easy huh!
				editor.generate();
				return false;
			}
		</script>
		<style type="text/css">
			@import url(../htmlarea/htmlarea.css);
		</style>

		<form method=post name=action action="<?=$_SERVER['SCRIPT_NAME']?>">
			<div class=pageForm>
				<table style="width:100%">
				<tr><td nowrap="nowrap"> 
					Titre : 
				</td><td style="width:100%"> 
					<input type=text name="titre" value="<?=stripslashes($this->titre)?>" style="width:100%"/>
				</td></tr><tr><td nowrap="nowrap">
					Nom technique de la page : 
				</td><td> 
					<input type=text name="nom" value="<?=stripslashes($this->nom)?>" style="width:100%"/>
				</td></tr><tr><td nowrap="nowrap">
					Mots clefs : 
				</td><td> 
					<input type=text name="mots_clef" value="<?=stripslashes($this->mots_clef)?>" style="width:100%"/>
				</td></tr><tr><td nowrap="nowrap">
					Description : 
				</td><td> 
					<input type=text name="description" value="<?=stripslashes($this->description)?>" style="width:100%"/>
				</td></tr><tr><td nowrap="nowrap">
					Auteur : 
				</td><td> 
					<input type=text name="autheur" value="<?=stripslashes($this->autheur)?>" style="width:100%"/>
				</td></tr><tr><td nowrap="nowrap">
					Page de style : 
				</td><td> 
					<?=$this->css->listeSelect()?>
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
		$this->mots_clef=$_REQUEST['mots_clef'];
		$this->nom=$_REQUEST['nom'];
		$this->titre=$_REQUEST['titre'];
		$this->autheur=$_REQUEST['autheur'];
		$this->description=$_REQUEST['description'];
		$this->id=$_REQUEST['id'];
		$this->css=new Css ($_REQUEST['id_css']);
		$this->debug=$_REQUEST['debug'];
		$this->css->debug=$_REQUEST['debug'];
	}

	function liste () {
		$this->connect();
		$query = "select `id_page`, `mots_clef`, `titre`, `autheur`, `id_css`, `description`, `nom` from pages order by `nom`";
		$select = $this->query($query);
		if ($this->status == OK) {
			$this->msg .= "<li>lister les utilisateurs</li>";
			echo "<table>\n";
			echo "<tr><th>Nom</th><th>Titre</th><th>Description</th></tr>";
			$style="lignepaire";
			while ($ligne = mysql_fetch_array($select)) {
				if ($style == "lignepaire") $style="ligneimpaire";
				else $style="lignepaire";
				?>
					<tr class="<?=$style?>">
					<td><a href="admin_edt_page.php?id_page=<?=$ligne['id_page']?>"><?=$ligne["nom"]?></a></td>
					<td><?=$ligne["titre"]?></td>
					<td><?=$ligne["description"]?></td>
					</tr>
					<?php
			}
			if ($style == "lignepaire") $style="ligneimpaire";
			else $style="lignepaire";
			echo "<tr class=$style><td colspan=3><a href=\"admin_edt_page.php\">Nouvelle page</a></td>";
			echo "</table>";
		}
		$this->deconnect();
	}
		
}
?>
