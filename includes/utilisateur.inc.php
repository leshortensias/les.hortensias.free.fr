<?php
require_once("includes/dbobject.inc.php");
define  ("DELOG",10);

class Utilisateur extends DbObject {
	// les champs de l'objets ...
	var $nom;
	var $prenom;
	var $userLogin;
	var $pass;
	var $id;

	// Constructeur
	function Utilisateur () {
		$this->DbObject ();
		if ($_REQUEST['debug'] == DELOG) $this->action(DELOG);
		if (isset ($_SESSION['id_user'])) {
			$this->msg .= "<li>Utilisateur en session</li>";
			$this->charge($_SESSION['id_user']);
		} else {
			$this->msg .= "<li>Session vide</li>";
		}
	}

	// On charge les donn&eacute;es de l'utilisateur depuis la base
	function charge ($id) {
		//select dans la table pages
		$query = "select `id_user`, `nom`, `prenom`, `login`, `pass` from utilisateurs where id_user='$id'";
		$ligne = $this->select($query);
		if ($this->status == OK) {
			if ($ligne == FALSE) {
				$this->status = KO;
				$this->msg.="<li>id introuvable</li>";
				$this->nom="Utilisateur inconnu";
				$this->pass="";
			} else {
				$this->nom=$ligne["nom"];
				$this->prenom=$ligne["prenom"];
				$this->userLogin=$ligne["login"];
				$this->pass=$ligne["pass"];
				$this->id=$ligne["id_user"];
				$_SESSION['id_user']=$this->id;
				$this->msg .= "<li>Sauver l'Utilisateur ".$this->id." en session</li>";
			}
		}
	}

	// Verif du userLogin/password (md5) et on charge les donn&eacute;es utilisateurs 
	function verif () {
		if (! isset ($_SESSION['id_user']) || $_SESSION['id_user'] != $this->id) {
			$query = "select `id_user`, `nom`, `prenom`, `login`, `pass` from utilisateurs where login='$this->userLogin'";
			$ligne = $this->select($query);
			if ($this->status == OK) {
				if ($ligne == FALSE) {
					// On v&eacute;rifit que la base n'est pas vide
					$query2 = "select count(1) as nb from utilisateurs ";
					$ligne2 = $this->select($query2);
					if ($ligne2["nb"] > 0 ) {
						$this->status = KO;
						$this->msg.="<li>login introuvable</li>";
						$this->nom="Utilisateur inconnu";
						$this->pass="";
					} else {
						$this->nom="Il n'existe pas encore d'utilisateur";
					}
				} else {
					if ($this->pass == $ligne["pass"] )	{
						$this->nom=$ligne["nom"];
						$this->prenom=$ligne["prenom"];
						$this->userLogin=$ligne["login"];
						$this->pass=$ligne["pass"];
						$this->id=$ligne["id_user"];
						$_SESSION['id_user']=$this->id;
						$this->msg .= "<li>Sauver l'Utilisateur ".$this->id." en session</li>";
					} else {
						$this->status = KO;
						$this->nom="Mauvais mot de passe";
						$this->msg.="<li>mot de passe incorrecte</li>";
					}
				}
			}
		}
	}

	// affichage 
	function affiche () {
		$this->msg.="<li>Affichage du login</li>";
		if ($this->status == OK ) {
			?>
				<div id="user">
				<?=$this->prenom?>
				<?=$this->nom?>
				</div>
				<?php
		} else {
			?>
				<form method=post name=action action="<?=$_SERVER['SCRIPT_NAME']?>">
				<div class=userLoginForm>
				login : <input type=text name="userLogin" value="<?=stripslashes($this->userLogin)?>"/>
				mot de passe : <input type=password name="pass" value=""/>
				<input type=hidden name="debug" value=<?=$this->debug?>>
				<input type=submit value="Go">
				</div>
				</form>
				<?php
		}
	}

	function sauve () {
		$query = "update utilisateurs ";
		$query.= "set `nom`='$this->nom', ";
		$query.= "`prenom`='$this->prenom', ";
		$query.= "`login`='$this->userLogin', ";
		$query.= "`pass`='$this->pass' ";
		$query.= "where id_user='$this->id'";
		$ligne = $this->update($query);
	}

	function ajoute () {
		$query = "insert into utilisateurs ";
		$query.= "        (`nom`,        `prenom`,        `login`,            `pass`       ) "; 
		$query.= " values ('$this->nom', '$this->prenom', '$this->userLogin', '$this->pass') "; 
		$ligne = $this->insert($query);
	}

	// Formulaire de saisie d'un utilisateur
	function formulaire ($action = AJOUTER, $prefix = "") {
		$this->msg.="<li>Affichage du formulaire, action=$action</li>";
		if ($action == MODIFIER) $bouton="Modifier";
		else $bouton="Ajouter";
		?>
			<form method=post name=action action="<?=$_SERVER['SCRIPT_NAME']?>">
			<div class=userForm>
			<table>
			<tr><td> 
			nom : 
			</td><td> 
			<input type=text name="<?=$prefix?>nom" value="<?=stripslashes($this->nom)?>" style="width:100%"/>
			</td></tr><tr><td>
			prenom :
			</td><td> 
			<input type=text name="<?=$prefix?>prenom" value="<?=stripslashes($this->prenom)?>" style="width:100%"/>
			</td></tr><tr><td>
			login : 
			</td><td> 
			<input type=text name="<?=$prefix?>userLogin" value="<?=stripslashes($this->userLogin)?>" style="width:100%"/>
			</td></tr><tr><td>
			mot de passe :
			</td><td> 
			<input type=password name="<?=$prefix?>pass" value="" style="width:100%"/>
			</td></tr></table>
			<input type=hidden name="action" value=<?=$action?>>
			<input type=hidden name="debug" value=<?=$this->debug?>>
			<input type=hidden name="<?=$prefix?>id_user" value=<?=$this->id?>>
			<input type=submit value="<?=$bouton?>">
			</div>
			</form>
			<?php
	}

	// formulaire de userLogin
	function userLoginForm () {
		$this->msg.="<li>Affichage du formulaire de login</li>";
		?>
			<form method=post name=action action="<?=$_SERVER['SCRIPT_NAME']?>">
			<div class=userLoginForm>
			login : <input type=text name="userLogin" value="<?=stripslashes($this->userLogin)?>"/>
			mot de passe : <input type=password name="pass" value="<?=stripslashes($this->pass)?>"/>
			<input type=hidden name="debug" value=<?=$this->debug?>>
			<input type=submit value="Go">
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
				case DELOG : 
					$this->msg.="<li>Action d&eacute;loguer</li>";
					unset($_SESSION['id_user']);
					break;
				default:
					$this->status=KO;
					$this->msg.="<li>Action Impossible : $action</li>";
					break;
			}
		}
	}

	function initFromRequest ($prefix = "") {
		if (! isset ($_SESSION['id_user']) || $_SESSION['id_user'] != $this->id || $prefix != "" ) {
			$this->nom=$_REQUEST[$prefix."nom"];
			$this->prenom=$_REQUEST[$prefix."prenom"];
			$this->userLogin=$_REQUEST[$prefix."userLogin"];
			if (isset($_REQUEST[$prefix."pass"])) $this->pass=md5($_REQUEST[$prefix."pass"]);
			$this->id=$_REQUEST[$prefix."id_user"];
		}
		$this->debug=$_REQUEST['debug'];
		$this->msg .= "<li>initialisation depuis les param&egrave;tres, prefix : $prefix</li>";
	}

	function liste () {
		$this->connect();
		$query = "select `id_user`, `nom`, `prenom`, `login`, `pass` from utilisateurs order by `login`";
		$select = $this->query($query);
		if ($this->status == OK) {
			$this->msg .= "<li>lister les utilisateurs</li>";
			echo "<table>";
			while ($ligne = mysql_fetch_array($select)) {
				?>
				<tr>
					<td><a href="<?=$_SERVER['SCRIPT_NAME']?>?new_id_user=<?=$ligne['id_user']?>"><?=$ligne["login"]?></a><td>
					<td><?=$ligne["nom"]?></td>
					<td><?=$ligne["prenom"]?></td>
				</tr>
				<?php
			}
			echo "</table>";
		}
		$this->deconnect();
	}
}
?>
