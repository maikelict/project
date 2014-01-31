<?		
//Script laden zodat je nooit pagina buiten de index om kan laden
include("includes/security.php");

//Admin controle
if($gebruiker['admin'] < 1){
  header('location: index.php?page=home');
}

      #Kijken hoeveel nieuwe tickets er zijn.
      $count_tickets = mysql_query("SELECT * FROM helpdesk WHERE status = '0'");
      $New_tickets = mysql_num_rows($count_tickets);
?>
		<center>
                 <table width="180" border="0">
                   <tr>
                     <td width="50"><center><img src="images/icons/user_admin.png" /></center></td>
                     <td width="130"><a href="index.php?page=admin/admins">Administratoren</a></td>
                   </tr>
                   <tr>
                     <td><center><img src="images/icons/user_ban.png" /></center></td>
                     <td><a href="index.php?page=admin/ban-ip">Ban IP</a></td>
                   </tr>
				   <tr>
                     <td><center><img src="images/icons/user_view.png" /></center></td>
                     <td><a href="index.php?page=admin/search-on-ip">Zoek op IP</a></td>
                   </tr>
                   <tr>
                     <td><center><img src="images/icons/groep_magnify.png" /></center></td>
                     <td><a href="index.php?page=admin/more-accounts">Dubbel Account</a></td>
                   </tr>
                   <tr>
                     <td><center><img src="images/icons/key_delete.png" /></center></td>
                     <td><a href="index.php?page=admin/wrong-login">Inlog Fout</a></td>
                   </tr>
                   <tr>
                     <td colspan="2"><div style="padding-top:20px;"></div></td>
                   </tr>
                   <tr>
                     <td><center><img src="images/icons/gebeurtenis.png" alt="" /></center></td>
                     <td><a href="index.php?page=admin/change-homepage">Homepagina</a></td>
                   </tr>
				   <tr>
                     <td><center><img src="images/icons/gebeurtenis.png" alt="" /></center></td>
                     <td><a href="index.php?page=admin/nieuws">Add nieuws</a></td>
                   </tr>
                   <tr>
                     <td><center><img src="images/icons/tekst_add.png" /></center></td>
                     <td><a href="index.php?page=admin/mass-message">Stuur Bericht</a></td>
                   </tr>
				   <tr>
                     <td><center><img src="images/icons/tekst_add.png" /></center></td>
                     <td><a href="index.php?page=admin/helpdesk-admin">Helpdesk (<?php echo $New_tickets; ?>)</a></td>
                   </tr>
                   <tr>
                     <td><center><img src="images/icons/comments.png" /></center></td>
                     <td><a href="index.php?page=admin/messages">Berichten</a></td>
                   </tr>
                   <tr>
                     <td><center><img src="images/icons/email.png" /></center></td>
                     <td><a href="index.php?page=admin/mass-mail">Massa mail</a></td>
                   </tr>
				   <tr>
                     <td><center><img src="images/icons/email.png" /></center></td>
                     <td><a target="_blank" href="/squirrelmail/src/login.php">Mail</a></td>
                   </tr>
                   <tr>
                     <td colspan="2"><div style="padding-top:20px;"></div></td>
                   </tr>
                   <tr>
                     <td><center><img src="images/icons/doneer.png" /></center></td>
                     <td><a href="index.php?page=admin/pay-list">Betaald lijst</a></td>
                   </tr>
                   <tr>
                     <td><center><img src="images/icons/egg2.gif" /></center></td>
                     <td><a href="index.php?page=admin/give-egg">Geef beginner ei</a></td>
                   </tr>
                   <tr>
                   	<td><center><img src="images/icons/pokeball.gif" /></center></td>
                    <td><a href="index.php?page=admin/give-pokemon">Geef Pokemon</a></td>
                   </tr>
                   <tr>
                   	<td><center><img src="images/icons/basket_put.png" /></center></td>
                    <td><a href="index.php?page=admin/give-pack">Geef pack</a></td>
                   </tr>
                   <tr>
                     <td colspan="2"><div style="padding-top:20px;"></div></td>
                   </tr>
                   <tr>
                   	<td><center><img src="images/icons/on-transferlist.gif" /></center></td>
                    <td><a href="index.php?page=admin/tournament">Tournaments</a></td>
                   </tr>
                   <tr>
                     <td colspan="2"><div style="padding-top:20px;"></div></td>
                   </tr>
                   <tr>
                   	<td><center><img src="images/icons/on-transferlist.gif" /></center></td>
                    <td><a href="index.php?page=attack/attack_map2">Attack Test</a></td>
                   </tr>
                 </table>
		</center>
