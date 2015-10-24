<?php
require_once("includes/dbobject.inc.php");

class Resa extends DbObject {
	// les champs de l'objets ...
	var $id;
	var $date;
	var $ip;
	var $civil;
	var $nom;
	var $prenom;
	var $mail;
	var $nba;
	var $nbe;
	var $nbchambres;
	var $datedebut;
	var $datefin;
	var $tel;
	var $adresse;
	var $message;

	// On charge la r&eacute;sa depuis la base
	function charge ($id) {
		$this->msg.="<li>Charge la nouvelle $id</li>";
		//select dans la table nouvelles
		$query = "select `id_resa`, `date`, `ip`, `civil`, `nom`, `prenom`, `mail`, `nba`, `nbe`, `nbchambre`, `datedebut`, `datefin`, `tel`, `adresse`, `message` from reservations where id_resa='$id'";
		$ligne = $this->select($query);
		if ($this->status == OK) {
			$this->date=$ligne["date"];
			$this->ip=$ligne["ip"];
			$this->civil=$ligne["civil"];
			$this->nom=$ligne["nom"];
			$this->prenom=$ligne["prenom"];
			$this->mail=$ligne["mail"];
			$this->nba=$ligne["nba"];
			$this->nbe=$ligne["nbe"];
			$this->nbchambres=$ligne["nbchambres"];
			$this->datedebut=$ligne["datedebut"];
			$this->datefin=$ligne["datefin"];
			$this->tel=$ligne["tel"];
			$this->adresse=$ligne["adresse"];
			$this->message=$ligne["message"];
			$this->id=$id;
		}
	}
  
	function chargeDepuisNom ($nom) {
		$this->msg.="<li>Charge la r&eacute;sa de $nom</li>";
		$query = "select `id_resa`, `date`, `ip`, `civil`, `nom`, `prenom`, `mail`, `nba`, `nbe`, `nbchambre`, `datedebut`, `datefin`, `tel`, `adresse`, `message` from reservations where nom='$nom'";
		$ligne = $this->select($query);
		if ($this->status == OK) {
			if ($ligne == FALSE) {
				$this->nom="non trouv&eacute;";
			} else {
				$this->date=$ligne["date"];
				$this->ip=$ligne["ip"];
				$this->civil=$ligne["civil"];
				$this->nom=$ligne["nom"];
				$this->prenom=$ligne["prenom"];
				$this->mail=$ligne["mail"];
				$this->nba=$ligne["nba"];
				$this->nbe=$ligne["nbe"];
				$this->nbchambres=$ligne["nbchambres"];
				$this->datedebut=$ligne["datedebut"];
				$this->datefin=$ligne["datefin"];
				$this->tel=$ligne["tel"];
				$this->adresse=$ligne["adresse"];
				$this->message=$ligne["message"];
				$this->id=$ligne["id_resa"];
			}
		}
	}

	
	// affichage de la r&eacute;sa
	function affiche () {
		?>
			<tr>
				<td class=dateResa> &gt; </td>
				<td class=dateResa> <?=$this->date?> </td>
				<td colspan=2 class=nomResa> <?php echo "$this->civil $this->nom $this->prenom" ?> </td>
				<td colspan=2 class=mailResa> <a href="mailto:<?=$this->mail?>"><?=$this->mail?></a> </td>
			</tr>
			<tr>
				<td rowspan=4 class=idResa> N&deg;<?=$this->id ?> </td>
				<td rowspan=3 class=adresseResa> <?=$this->adresse ?> </td>
				<td class=telResa> T&eacute;l&eacute;phone&nbsp;: </td>
				<td class=telResa> <?=$this->tel ?> </td>
				<td class=nbAdultesResa> Nombre d'adultes&nbsp;: </td>
				<td class=nbAdultesResa> <?=$this->nba ?> </td>
			</tr>
			<tr>
				<td class=dateDebutResa> Date d'arriv&eacute;e&nbsp;:  </td>
				<td class=dateDebutResa> <?=$this->datedebut ?> </td>
				<td class=nbEnfantsResa> Nombre d'enfants&nbsp;: </td>
				<td class=nbEnfantsResa> <?=$nbe ?> </td>
			</tr>
			<tr>
				<td class=dateFinResa> Date de d&eacute;part&nbsp;: </td>
				<td class=dateFinResa> <?=$this->datefin ?> </td>
				<td class=nbChambresResa> Nombre de chambres&nbsp;: </td>
				<td class=nbChambresResa> <?=$this->nbchambres ?> </td>
			</tr>
			<tr>
				<td colspan=5 class=messageResa> <?=$this->message ?> </td>
			</tr>
		<?php
	}

	function ajoute () {
		$this->msg.="<li>Ajoute la nouvelle</li>";
		$this->date=date("Y-m-d H:i:s");
		$query = "insert into nouvelles ";
		$query.= "        (`date`,        `titre`,        `nouvelle`,        `ip`,        `visible`) "; 
		$query.= " values ('$this->date', '$this->titre', '$this->corps', '$this->ip', '$this->visible') "; 
		$ligne = $this->insert($query);
	}

	// Formulaire de saisie d'une nouvelle, l'action par d&eacute;faut c'est ajouter.
	function formulaire ($action = AJOUTER) {
		$this->msg.="<li>Affichage du formulaire, action=$action</li>";
		if ($action == MODIFIER) $bouton="Modifier";
		else $bouton="Ajouter";
		?>
		<form method=post name=action action="<?=$_SERVER['SCRIPT_NAME']?>">
			<div class=newsForm>
				<table>
				<tr><td class=dateNews> 
					<?php $this->afficheDate ?>
				</td><td> 
					Titre : 
				</td><td> 
					<input type=text name="titre" value="<?=stripslashes($this->titre)?>" size="50" />
				</td><td> 
					Visible : 
					<input type=checkbox name="visible" value="1" <?php if ($this->visible == 1) echo "checked" ?>/>
				</td></tr><tr><td colspan=4>
					Message : 
				</td></tr><tr><td colspan=4> 
					<textarea name="corps" rows=5 cols=80><?=stripslashes($this->corps)?></textarea>
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
		$this->msg.="<li>Initialisation depuis _REQUEST<ul>";
		$this->corps=$_REQUEST['corps'];
		$this->ip=$_SERVER['REMOTE_ADDR'];
		$this->msg.="<li>IP : $this->ip </li>";
		$this->visible=$_REQUEST['visible'];
		$this->msg.="<li>visible : $this->visible </li>";
		$this->titre=$_REQUEST['titre'];
		$this->msg.="<li>titre : $this->titre </li>";
		$this->lang=$_REQUEST['lang'];
		$this->id=$_REQUEST['id'];
		$this->debug=$_REQUEST['debug'];
		$this->msg.="</ul></li>";
	}

	function liste ($admin = 0) {
		$this->connect();
		$query = "select `id_news`, `titre`, date_format(`date`,'%d/%m/%Y &agrave; %k:%i') as date, `nouvelle`, `ip`, `visible`, `lang` from nouvelles ";
		if ($admin == 0) $query.= "where visible=1 ";
		$query.= "order by `date` desc";
		$select = $this->query($query);
		if ($this->status == OK) {
			$this->msg .= "<li>lister les nouvelles</li>";
			echo "<table>\n";
			$style="lignepaire";
			while ($ligne = mysql_fetch_array($select)) {
				if ($style == "lignepaire") $style="ligneimpaire";
				else $style="lignepaire";
				?>
					<form method=post name=modif action="<?=$_SERVER['SCRIPT_NAME']?>">
					<tr class="<?=$style?>">
						<td class=titreNews> <?=$ligne['titre']?> </td>
						<td class=dateNews> Le <?=$ligne['date']?> </td>
						<?php
						if ($admin == 1) {
							if ($ligne['visible'] == 1) echo "<td>Visible</td>"; 
							else echo "<td>Cach&eacute;</td>"; 
							?>
							<td>
							<input type=hidden value="<?=$ligne['id_news']?>" name="id_news">
							<input type=hidden name="debug" value=<?=$this->debug?>>
							<input type=submit value="Modifier">
							</td>
							<?php
						}
						?>
					</tr>
					<tr class="<?=$style?>">
						<td colspan=4 class=messageNews>
						<?=$ligne['nouvelle']?>
						</td>
					</tr>
					</form>
					<?php
			}
			if ($admin == 1) {
			?>
			<tr><td colspan=4><a href="<?=$_SERVER['SCRIPT_NAME']?>">Ajouter une nouvelle</a></td></tr>
			<?php
			}
			echo "</table>";
		}
		$this->deconnect();
	}
	
	function afficheDate () {
		list ($date, $time) = explode (' ', $this->date);
		list ($year, $month, $day) = explode ("-", $date);
		list ($hours, $minutes, $seconds) = explode (":", $time);
		//$format = "d/m/Y";
		$format = "d/m/Y H:i:s";
		$format_date = date ($format, mktime ($hours, $minutes, $seconds, $month, $day, $year));
		echo $format_date;
	}

}
?>
