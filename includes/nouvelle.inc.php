<?php
require_once("includes/dbobject.inc.php");

class Nouvelle extends DbObject {
	// les champs de l'objets ...
	var $id;
	var $date;
	var $titre;
	var $corps;
	var $ip;
	var $visible;
	var $lang;
	var $categorie;

	// On charge la nouvelle depuis la base
	function charge ($id) {
		$this->msg.="<li>Charge la nouvelle $id</li>";
		//select dans la table nouvelles
		$query = "select `id_news`, `titre`, `date`, `nouvelle`, `ip`, `visible`, `lang`, `categorie` from nouvelles where id_news='$id'";
		$ligne = $this->select($query);
		if ($this->status == OK) {
			$this->date=$ligne["date"];
			$this->titre=$ligne["titre"];
			$this->corps=$ligne["nouvelle"];
			$this->ip=$ligne["ip"];
			$this->visible=$ligne["visible"];
			$this->lang=$ligne["lang"];
			$this->categorie=$ligne["categorie"];
			$this->id=$id;
		}
	}

	// affichage de la news
	function affiche () {
		?>
			<tr>
				<td class="titre<?=ucfirst($categorie)?>"> <?=$titre ?> </td>
				<td class="date<?=ucfirst($categorie)?>"> Le <?=$date ?> </td>
			</tr>
			<tr>
				<td colspan=2 class="message<?=ucfirst($categorie)?>"><?=$message?></td>
			</tr>
			<tr><td colspan=2>&nbsp;</td></tr>
		<?php
	}

	function sauve () {
		$this->msg.="<li>Sauve l'objet</li>";
		$query = "update nouvelles ";
		$query.= "set `titre`='$this->titre', ";
		$query.= "`nouvelle`='$this->corps', ";
		$query.= "`ip`='$this->ip', ";
		$query.= "`visible`='$this->visible', ";
		$query.= "`categorie`='$this->categorie', ";
		$query.= "`lang`='$this->lang' ";
		$query.= "where id_news='$this->id'";
		$ligne = $this->update($query);
	}

	function ajoute () {
		$this->msg.="<li>Ajoute la nouvelle</li>";
		$this->date=date("Y-m-d H:i:s");
		$query = "insert into nouvelles ";
		$query.= "        (`date`,        `titre`,        `nouvelle`,        `ip`,        `visible`,     `categorie`) "; 
		$query.= " values ('$this->date', '$this->titre', '$this->corps', '$this->ip', '$this->visible', $this->categorie) "; 
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
				</td><td nowrap="nowrap"> 
					Titre : 
				</td><td style="width:100%"> 
					<input type=text name="titre" value="<?=stripslashes($this->titre)?>" size="50" style="width:100%"/>
				</td><td nowrap="nowrap"> 
					Categorie :
					<select name="categorie">
						<option value="nouvelle" <?if ($this->categorie == 'nouvelle') echo 'selected="selected"';?>>Nouvelle</option>
						<option value="promo" <?if ($this->categorie == 'promo') echo 'selected="selected"';?>>Promotion</option>
					</select>
				</td><td nowrap="nowrap"> 
					Visible : 
					<input type=checkbox name="visible" value="1" <?php if ($this->visible == 1) echo "checked" ?>/>
				</td></tr><tr><td colspan=5>
					Message : 
				</td></tr><tr><td colspan=5> 
					<textarea name="corps" rows=5 cols=80><?=stripslashes($this->corps)?></textarea>
				</td></tr></table>
				<input type=hidden name="action" value="<?=$action?>">
				<input type=hidden name="debug" value="<?=$this->debug?>">
				<input type=hidden name="id" value="<?=$this->id?>">
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
		$this->categorie=$_REQUEST['categorie'];
		$this->msg.="<li>categorie : $this->categorie </li>";
		$this->lang=$_REQUEST['lang'];
		$this->id=$_REQUEST['id'];
		$this->debug=$_REQUEST['debug'];
		$this->msg.="</ul></li>";
	}

	function liste ($admin = 0) {
		$this->connect();
		$query = "select `id_news`, `titre`, date_format(`date`,'%d/%m/%Y &agrave; %k:%i') as date2, `nouvelle`, `ip`, `visible`, `lang`, `date`, `categorie` from nouvelles ";
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
				if ($ligne['visible'] == 1) $style_visible="visible";
				else $style_visible="cache";
				?>
					<form method=post name=modif action="<?=$_SERVER['SCRIPT_NAME']?>">
					<tr class="<?=$style?> <?=$style_visible?>">
						<td class="titre<?=ucfirst($ligne['categorie'])?>"> <?=$ligne['titre']?> </td>
						<td class="date<?=ucfirst($ligne['categorie'])?>"> Le <?=$ligne['date']?> </td>
						<?php
						if ($admin == 1) {
							if ($ligne['visible'] == 1) echo "<td>Visible</td>"; 
							else echo "<td>Cach&eacute;</td>"; 
							echo "<td>".$ligne['categorie']."</td>"; 
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
					<tr class="<?=$style?> <?=$style_visible?>">
						<td colspan=5 class="message<?=ucfirst($ligne['categorie'])?>">
						<?=$ligne['nouvelle']?>
						</td>
					</tr>
					</form>
					<?php
			}
			if ($admin == 1) {
				if ($style == "lignepaire") $style="ligneimpaire";
				else $style="lignepaire";
			?>
			<tr class="<?=$style?>"><td colspan="5"><a href="<?=$_SERVER['SCRIPT_NAME']?>">Ajouter une nouvelle</a></td></tr>
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
