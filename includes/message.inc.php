<?php
require_once("includes/dbobject.inc.php");

class Message extends DbObject {
	// les champs de l'objets ...
	var $id;
	var $date;
	var $corps;
	var $qui;
	var $mail;
	var $visuMail;
	var $visible;
	var $ip;

	// On charge la message depuis la base
	function charge ($id) {
		$this->msg.="<li>Charge la message $id</li>";
		//select dans la table or
		$query = "select `id_or`, `qui`, `date`, `message`, `ip`, `visible`, `mail`, visuMail from `or` where id_or='$id'";
		$ligne = $this->select($query);
		if ($this->status == OK) {
			$this->date=$ligne["date"];
			$this->qui=$ligne["qui"];
			$this->corps=$ligne["message"];
			$this->ip=$ligne["ip"];
			$this->visible=$ligne["visible"];
			$this->mail=$ligne["mail"];
			$this->visuMail=$ligne["visuMail"];
			$this->id=$id;
		}
	}

	// affichage de la news
	function affiche () {
		?>
			<tr>
				<td class=quiOr> <?=$qui ?> </td>
				<td class=dateOr> Le <?=$date ?> </td>
			</tr>
			<tr>
				<td colspan=2 class=messageOr><?=$message?></td>
			</tr>
			<tr><td colspan=2>&nbsp;</td></tr>
		<?php
	}

	function sauve () {
		$this->msg.="<li>Sauve le message</li>";
		$query = "update `or` ";
		$query.= "set `qui`='$this->qui', ";
		$query.= "`message`='$this->corps', ";
		$query.= "`ip`='$this->ip', ";
		$query.= "`visible`='$this->visible', ";
		$query.= "`mail`='$this->mail', ";
		$query.= "`visuMail`='$this->visuMail' ";
		$query.= "where id_or='$this->id'";
		$ligne = $this->update($query);
	}

	function ajoute () {
		$this->msg.="<li>Ajoute le message</li>";
		$this->date=date("Y-m-d H:i:s");
		$query = "insert into `or` ";
		$query.= "        (`date`,        `qui`,        `message`,        `ip`,     `visible`, mail,          visuMail) "; 
		$query.= " values ('$this->date', '$this->qui', '$this->corps', '$this->ip', '0',       '$this->mail', '$this->visuMail') "; 
		$ligne = $this->insert($query);
	}

	// Formulaire de saisie d'une message, l'action par d&eacute;faut c'est ajouter.
	function formulaire ($action = AJOUTER, $admin = 0) {
		$this->msg.="<li>Affichage du formulaire, action=$action</li>";
		if ($action == MODIFIER) $bouton="Modifier";
		else $bouton="Ajouter";
		?>
		<form method=post name=action action="<?=$_SERVER['SCRIPT_NAME']?>">
			<div class=newsForm>
				<table>
				<tr><td class=dateOr> 
					<?php $this->afficheDate ?>
				</td><td nowrap="nowrap"> 
					Qui &ecirc;tes vous ? : 
				</td><td width="60%"> 
					<input type=text name="qui" value="<?=stripslashes($this->qui)?>" style="width:100%"/>
				</td><td nowrap="nowrap"> 
					Votre mail :
				</td><td width="40%"> 
					<input type=text name="mail" value="<?=stripslashes($this->mail)?>" style="width:100%"/>
				</td>
				</tr><tr><td colspan=5>
					Message : 
				</td></tr><tr><td colspan=5> 
					<textarea name="corps" rows=5 cols=80 style="width: 100%"><?=stripslashes($this->corps)?></textarea>
				</td></tr>
				<tr>
						<?php if ($admin == 1) { ?>
					<td colspan=4> 
						Mail visible : 
						<?php } else { ?>
					<td colspan=5> 
						J'accepte que mon mail soit publi&eacute; sur le site http://les.hortensias.free.fr
						<?php } ?>
						<input type=checkbox name="visuMail" value="1" <?php if ($this->visuMail == 1) echo "checked" ?>/>
					</td>
					<?php if ($admin == 1) { ?>
					<td nowrap="nowrap"> 
					Message visible : 
					<input type=checkbox name="visible" value="1" <?php if ($this->visible == 1) echo "checked" ?>/>
					</td> 
					<?php } ?>
				</tr>
				</table>
				<input type=hidden name="action" value=<?=$action?>/>
				<input type=hidden name="page" value="<?=$_REQUEST['page']?>"/>
				<input type=hidden name="debug" value=<?=$this->debug?>/>
				<input type=hidden name="id" value=<?=$this->id?>/>
				<input type=submit value="<?=$bouton?>"/>
				<?php if ($admin == 0) { ?>
				Votre message ne sera pas tout de suite visible sur le site. En effet, suite à de trop nombreux messages non solicités, nous validons les messages avant leur publication.
				<?php } ?>
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
		$this->visible=$_REQUEST['visible'];
		$this->qui=$_REQUEST['qui'];
		$this->mail=$_REQUEST['mail'];
		$this->id=$_REQUEST['id'];
		$this->debug=$_REQUEST['debug'];
		$this->visuMail=$_REQUEST['visuMail'];
		$this->msg.="<li>IP : $this->ip </li>";
		$this->msg.="<li>visible : $this->visible </li>";
		$this->msg.="<li>qui : $this->qui </li>";
		$this->msg.="</ul></li>";
	}

	function liste ($admin = 0) {
		$this->connect();
		$query = "select `id_or`, `qui`, date_format(`date`,'%d/%m/%Y &agrave; %k:%i') as date2, `message`, `ip`, `visible`, `mail`, visuMail, `date` from `or` ";
		if ($admin == 0) $query.= "where visible=1 ";
		$query.= "order by `date` desc";
		$select = $this->query($query);
		if ($this->status == OK) {
			$this->msg .= "<li>lister les messages du livre d'or</li>";
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
						<td class=dateOr> Le <?=$ligne['date2']?> </td>
						<td class=mailOr> 
							<?php if ($admin == 1) { ?> 
								<a href="mailto:<?=$ligne['mail']?>"><?=$ligne['mail']?></a>	
							<?php } else {
								if ($ligne['visuMail'] == 1) {
									echo $ligne['mail'] ;
								}
							}
							?> 
						</td>
						<?php
						if ($admin == 1) {
							if ($ligne['visible'] == 1) echo "<td>Visible</td>"; 
							else echo "<td>Cach&eacute;</td>"; 
							?>
							<td>
							<input type=hidden value="<?=$ligne['id_or']?>" name="id_or">
							<input type=hidden name="debug" value=<?=$this->debug?>>
							<input type=submit value="Modifier">
							</td>
							<?php
						}
						?>
					</tr>
					<tr class="<?=$style?> <?=$style_visible?>">
						<td colspan=4 class=messageOr>
						<?=$ligne['message']?>
						<div class=quiOr> <?=$ligne['qui']?> </div>
						</td>
					</tr>
					</form>
					<?php
			}
			if ($admin == 1) {
				if ($style == "lignepaire") $style="ligneimpaire";
				else $style="lignepaire";
			?>
			<tr style="<?=$style?>"><td colspan=4><a href="<?=$_SERVER['SCRIPT_NAME']?>">Ajouter une message</a></td></tr>
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
