<?php
	#Beveiliging, er moet wel een GET category zijn
	if(($_GET['category'] == '') OR ($_GET['thread'] == '')) header('Location: ?page=forum-categories');
	
	$page = 'forum-messages';
	#Goeie taal erbij laden voor de page
	include_once('language/language-pages.php');
	
	$sql = mysql_query("SELECT forum_topics.topic_naam, forum_topics.status, forum_categorieen.categorie_id, forum_categorieen.categorie_naam
						FROM forum_topics
						INNER JOIN forum_categorieen
						ON forum_topics.categorie_id = forum_categorieen.categorie_id
						WHERE forum_topics.topic_id = '".$_GET['thread']."'");
	$prop = mysql_fetch_assoc($sql);
	if(mysql_num_rows($sql) == 0) header('Location: ?page=forum-categories');
	
	#Bericht Posten (dus niet iets bewerken of verwijderen)
	if((isset($_POST['submit'])) && ($_GET['editid'] == '') && ($_GET['deleteid'] == '')){
		if(empty($_POST['tekst']))
			$error = '<div class="red">'.$txt['alert_no_text'].'</div>';
		
		elseif(mysql_num_rows(mysql_query("SELECT id FROM forum_berichten WHERE bericht = '".$_POST['tekst']."' AND topic_id = '".$_GET['thread']."' AND user_id = '".$_SESSION['id']."'")) >= 1)
			$error = '<div class="red">'.$txt['alert_already_send'].'</div>';

		else{
			$datum = date('Y-m-d H:i:s');
			mysql_query("INSERT INTO forum_berichten (categorie_id, topic_id, user_id, bericht, datum) VALUES ('".$_GET['category']."', '".$_GET['thread']."', '".$_SESSION['id']."', '".$_POST['tekst']."', '".$datum."')");
			mysql_query("UPDATE forum_categorieen SET berichten = berichten + '1', laatste_user_id = '".$_SESSION['id']."', laatste_datum = '".$datum."' WHERE categorie_id = '".$_GET['category']."'");
			mysql_query("UPDATE forum_topics SET berichten = berichten + '1', laatste_user_id = '".$_SESSION['id']."', laatste_datum = '".$datum."' WHERE topic_id = '".$_GET['thread']."'");
			
			$error = '<div class="green">'.$txt['success_post_message'].'</div>';
		}
	}
	#Bewerken
	elseif((isset($_POST['submit'])) && ($_GET['editid'] != '')){
		if(empty($_POST['tekst']))
			$error = '<div class="red">'.$txt['alert_no_text'].'</div>';
		
		elseif($gebruiker['admin'] < 1)
			$error = '<div class="red">'.$txt['alert_not_admin'].'</div>';
		
		elseif(mysql_num_rows(mysql_query("SELECT id FROM forum_berichten WHERE id = '".$_GET['editid']."'")) == 0)
			$error = '<div class="red">'.$txt['alert_message_doesnt_exist'].'</div>';
		
		else{
			mysql_query("UPDATE forum_berichten SET bericht = '".$_POST['tekst']."' WHERE id = '".$_GET['editid']."'");
			$error = '<div class="green">'.$txt['success_edit_message'].'</div>';
		}
	}
	#Verwijderen
	elseif($_GET['deleteid'] != ''){
		if($gebruiker['admin'] < 1)
			echo '<div class="red">'.$txt['alert_not_admin'].'</div>';
		
		elseif(mysql_num_rows(mysql_query("SELECT id FROM forum_berichten WHERE id = '".$_GET['deleteid']."'")) == 0)
			echo '<div class="red">'.$txt['alert_message_doesnt_exist'].'</div>';
		
		else{
			mysql_query("DELETE FROM forum_berichten WHERE id = '".$_GET['deleteid']."'");
			mysql_query("UPDATE forum_categorieen SET berichten = berichten - '1' WHERE categorie_id = '".$_GET['category']."'");
			mysql_query("UPDATE forum_topics SET berichten = berichten - '1' WHERE topic_id = '".$_GET['thread']."'");
			echo '<div class="green">'.$txt['success_message_delete'].'</div>';
		}
	}
	
#Paginasysteem dingen
if(empty($_GET['subpage'])) $subpage = 1; 
else $subpage = $_GET['subpage']; 
#Max aantal leden per pagina
$max = 15; 

$aantal = mysql_num_rows(mysql_query("SELECT id FROM forum_berichten WHERE categorie_id = '".$_GET['category']."' AND topic_id = '".$_GET['thread']."'"));
$aantal_paginas = ceil($aantal/$max);
if($aantal_paginas == 0) $aantal_paginas = 1;
$pagina = $subpage*$max-$max;
?>

<p><a href="?page=forum-categories"><?php echo $txt['pokemon-area-forum']; ?></a> <img src="images/icons/arrow_right.png" width="16" height="16" style="margin-bottom:-3px;" /> <a href="?page=forum-threads&category=<?php echo $prop['categorie_id']; ?>"><?php echo $prop['categorie_naam']; ?></a> <img src="images/icons/arrow_right.png" width="16" height="16" style="margin-bottom:-3px;" /> <strong><?php echo $prop['topic_naam']; ?></strong><br /></p>
<?php if($_SESSION['naam'] == '') echo $txt['you_must_be_online'].'<br /><br />';
elseif($prop['status'] == 0) echo $txt['topic_closed'].'<br /><br />';
echo $txt['please_talk_english'];

#Paginasysteem
    $links = false;
    $rechts = false;
    echo '<table width="600">
			<tr>
				<td><center><br /><div class="sabrosus">';
    if($subpage == 1)
      echo '<span class="disabled"> &lt; </span>';
    else{
      $back = $subpage-1;
      echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$back.'&category='.$_GET['category'].'&thread='.$_GET['thread'].'"> &lt; </a>';
    }
    for($i = 1; $i <= $aantal_paginas; $i++) { 
      if((2 >= $i) && ($subpage == $i))
        echo '<span class="current">'.$i.'</span>';
      elseif((2 >= $i) && ($subpage != $i))
        echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$i.'&category='.$_GET['category'].'&thread='.$_GET['thread'].'">'.$i.'</a>';
      elseif(($aantal_paginas-2 < $i) && ($subpage == $i))
        echo '<span class="current">'.$i.'</span>';
      elseif(($aantal_paginas-2 < $i) && ($subpage != $i))
        echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$i.'&category='.$_GET['category'].'&thread='.$_GET['thread'].'">'.$i.'</a>';
      else{
        $max = $subpage+3;
        $min = $subpage-3;  
        if($subpage == $i)
          echo '<span class="current">'.$i.'</span>';
        elseif(($min < $i) && ($max > $i))
        	echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$i.'&category='.$_GET['category'].'&thread='.$_GET['thread'].'">'.$i.'</a>';
        else{
          if($i < $subpage){
            if(!$links){
              echo '...';
              $links = True;
            }
          }
          else{
            if(!$rechts){
              echo '...';
              $rechts = True;
            }
          }
        }
      }
    } 
    if($aantal_paginas == $subpage)
      echo '<span class="disabled"> &gt; </span>';
    else{
      $next = $subpage+1;
      echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$next.'&category='.$_GET['category'].'&thread='.$_GET['thread'].'"> &gt; </a>';
    }
    echo "</div></center></td>
		</tr>
	</table>";
#Einde paginasysteem
?>
<HR />
<?php
	#Als iemand Quote, het bericht ervan opvragen
	if($_GET['quoteid'] != '') $quote = mysql_fetch_assoc(mysql_query("SELECT bericht FROM forum_berichten WHERE id = '".$_GET['quoteid']."'"));
	elseif($_GET['editid'] != '') $edit = mysql_fetch_assoc(mysql_query("SELECT bericht FROM forum_berichten WHERE id = '".$_GET['editid']."'"));
	
	$query = mysql_query("SELECT forum_berichten.*, gebruikers.username
						 FROM forum_berichten
						 INNER JOIN gebruikers
						 ON forum_berichten.user_id = gebruikers.user_id
						 WHERE forum_berichten.categorie_id = '".$_GET['category']."'
						 AND forum_berichten.topic_id = '".$_GET['thread']."'
						 ORDER BY forum_berichten.datum ASC LIMIT ".$pagina.", ".$max."");
						 
	if(mysql_num_rows($query) == 0){
		echo $txt['no_messages'].'<HR>';
	}
	else{
		for($number = 1; $info = mysql_fetch_assoc($query); $number++){
			#Datum-tijd goed
			$datum = explode("-", $info['datum']);
			$tijd = explode(" ", $datum[2]);
			$datum = $tijd[0]."-".$datum[1]."-".$datum[0].",&nbsp;".$tijd[1];
			$datum_finished = substr_replace($datum ,"",-3);
			
			#Enters in de textarea ook weergeven als een enter
			$tekst = nl2br($info['bericht']);
			#Gebruiken voor hele lange zinnen afte kappen naar kortere zinnen. http://nl3.php.net/wordwrap
			$tekst = anti_langezin($tekst);
			#Van [player]Skank[/player] een link maken naar de player
			$tekst = eregi_replace("\[player\]([^\[]+)\[/player\]","<a href=\"?page=profile&player=\\1\">\\1</a>",$tekst);
			#Van [icon]charizard[/icon] plaatje maken naar de animatie van de pokemon
			$tekst = eregi_replace("\[icon\]([^\[]+)\[/icon\]","<img src=\"images/pokemon/icon/\\1.gif\" border=\"0\">",$tekst);
			$tekst = eregi_replace("\[icon_shiny\]([^\[]+)\[/icon_shiny\]","<img src=\"images/shiny/icon/\\1.gif\" border=\"0\">",$tekst);
			#Van [back]charizard[/back] plaatje maken naar de rug van de pokemon
			$tekst = eregi_replace("\[back\]([^\[]+)\[/back\]","<img src=\"images/pokemon/back/\\1.png\" border=\"0\">",$tekst);
			$tekst = eregi_replace("\[back_shiny\]([^\[]+)\[/back_shiny\]","<img src=\"images/shiny/back/\\1.png\" border=\"0\">",$tekst);
			#Van [back]charizard[/back] plaatje maken naar de pokemon
			$tekst = eregi_replace("\[pokemon\]([^\[]+)\[/pokemon\]","<img src=\"images/pokemon/\\1.png\" border=\"0\">",$tekst);
			$tekst = eregi_replace("\[shiny\]([^\[]+)\[/shiny\]","<img src=\"images/shiny/\\1.png\" border=\"0\">",$tekst);
			#Plaatje maken
    		$tekst = eregi_replace("\\[img]([^\\[]*)\\[/img\\]","<img src=\"\\1\" border=\"0\" OnLoad=\"if(this.width > 580) {this.width=580}\">",$tekst);
			#Tekst dik gedrukt maken
			$tekst = eregi_replace("\[b\]","<strong>",$tekst);
			$tekst = eregi_replace("\[/b\]","</strong>",$tekst);
			#Tekst onderstreept maken
			$tekst = eregi_replace("\[u\]","<u>",$tekst);
			$tekst = eregi_replace("\[/u\]","</u>",$tekst);
			#Tekst Schuin gedrukt maken
			$tekst = eregi_replace("\[i\]","<em>",$tekst);
			$tekst = eregi_replace("\[/i\]","</em>",$tekst);
			#Tekst centreren
			$tekst = eregi_replace("\[center\]","<center>",$tekst);
			$tekst = eregi_replace("\[/center\]","</center>",$tekst);
			#Lopend balkje in beeld
			$tekst = eregi_replace("\[marquee\]([^\[]+)\[/marquee\]","<marquee>\\1</marquee>",$tekst);
			#kleur veranderen
			$tekst = eregi_replace("\[color=([^\[]+)\]([^\[]+)\[/color\]","<font color=\\1>\\2</font>",$tekst);
			#Quote
			$tekst = eregi_replace("\[quote\]","<div class='quote'>",$tekst);
			$tekst = eregi_replace("\[/quote\]","</div>",$tekst);
																
			#Van plaatjes invoeren
			# Pad naar de afbeeldingen (inclusief slash aan het einde)
			$pad = "images/emoticons/";
			# UBB code => Bestandsnaam
			$smiley = array(
			  ":)" => "001.png",
			  ":D" => "002.png",
			  ":P" => "104.png",
			  ";)" => "003.png",
			  ":S" => "009.png",
			  ":O" => "004.png",
			  "8-)" => "050.png",
			  "<o)" => "075.png",
			  "(K)" => "028.png",
			  "(BOO)" => "096.png",
			  "(J)" => "086.png",
			  "(V)" => "087.png",
			  ":8)" => "088.png",
			  ":@" => "099.png",
			  ":$" => "008.png",
			  ":-#" => "048.png",
			  ":(" => "010.png",
			  ":'(" => "011.png",
			  ":|" => "012.png",
			  "(H)" => "006.png",
			  "(A)" => "014.png",
			  "|-)" => "078.png",
			  "(T)" => "034.png",
			  "+o(" => "053.png",
			  "(L)" => "015.png",
			  ":[" => "043.png", 
			  ":'|" => "093.png",
			  "(F)" => "025.png",
			  "(Y)" => "041.png",
			  "(N)" => "042.png"
			);
			foreach($smiley as $bb => $img)
			  $tekst = preg_replace("#".preg_quote($bb,'#')."#i","<img src='".$pad.$img."' width='19' height='19' alt='".$bb."' />",$tekst);
	  
			echo '<table width="600" cellpadding="0" cellspacing="0">
					<tr>
						<td class="top_first_td" width="220"><img src="images/icons/man.png" style="margin-bottom:-3px;"> <a href="?page=profile&player='.$info['username'].'">'.$info['username'].'</a></td>
						<td class="top_td" width="220"><center><img src="images/icons/datum.png" style="margin-bottom:-3px;"> '.$datum_finished.'</center></td>
						<td class="top_td" width="220">
							<div style="float: right; padding-right:10px;">';
						if(($_SESSION['naam'] != '') && ($prop['status'] == 1)){
							echo '<a href="?page=forum-messages&category='.$_GET['category'].'&thread='.$_GET['thread'].'&subpage='.$_GET['subpage'].'&quoteid='.$info['id'].'#send"><img src="images/icons/comment.png" title="'.$txt['quote_this_message'].'" style="margin-bottom:-3px;"></a>';
						}
						if($gebruiker['admin'] >= 1){
							echo ' <a href="?page=forum-messages&category='.$_GET['category'].'&thread='.$_GET['thread'].'&subpage='.$_GET['subpage'].'&editid='.$info['id'].'#send"><img src="images/icons/comment_edit.png" title="'.$txt['edit_this_message'].'"></a> 
							<a href="?page=forum-messages&category='.$_GET['category'].'&thread='.$_GET['thread'].'&subpage='.$_GET['subpage'].'&deleteid='.$info['id'].'"><img src="images/icons/comment_delete.png" title="'.$txt['delete_this_message'].'" style="margin-bottom:-3px;"></a>';
						}
						echo '</div></td>
				  	</tr>
					<tr>
						<td class="normal_first_td" style="padding-right:10px;" colspan="3">'.$tekst.'</td>
					</tr>
				  	</table>
				  	<HR>';
		}
	}
	
#Paginasysteem
    $links = false;
    $rechts = false;
    echo '<table width="600">
			<tr>
				<td><center><br /><div class="sabrosus">';
    if($subpage == 1)
      echo '<span class="disabled"> &lt; </span>';
    else{
      $back = $subpage-1;
      echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$back.'&category='.$_GET['category'].'&thread='.$_GET['thread'].'"> &lt; </a>';
    }
    for($i = 1; $i <= $aantal_paginas; $i++) { 
      if((2 >= $i) && ($subpage == $i))
        echo '<span class="current">'.$i.'</span>';
      elseif((2 >= $i) && ($subpage != $i))
        echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$i.'&category='.$_GET['category'].'&thread='.$_GET['thread'].'">'.$i.'</a>';
      elseif(($aantal_paginas-2 < $i) && ($subpage == $i))
        echo '<span class="current">'.$i.'</span>';
      elseif(($aantal_paginas-2 < $i) && ($subpage != $i))
        echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$i.'&category='.$_GET['category'].'&thread='.$_GET['thread'].'">'.$i.'</a>';
      else{
        $max = $subpage+3;
        $min = $subpage-3;  
        if($subpage == $i)
          echo '<span class="current">'.$i.'</span>';
        elseif(($min < $i) && ($max > $i))
        	echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$i.'&category='.$_GET['category'].'&thread='.$_GET['thread'].'">'.$i.'</a>';
        else{
          if($i < $subpage){
            if(!$links){
              echo '...';
              $links = True;
            }
          }
          else{
            if(!$rechts){
              echo '...';
              $rechts = True;
            }
          }
        }
      }
    } 
    if($aantal_paginas == $subpage)
      echo '<span class="disabled"> &gt; </span>';
    else{
      $next = $subpage+1;
      echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&subpage='.$next.'&category='.$_GET['category'].'&thread='.$_GET['thread'].'"> &gt; </a>';
    }
    echo "</div></center></td>
		</tr>
	</table>
	<HR>";
#Einde paginasysteem

if($_SESSION['id'] == '')
	echo $txt['first_login'];
elseif(($prop['status'] == 0) && ($_GET['editid'] == '')){
	echo $txt['topic_closed_no_reply'];
}
else{ ?>
<script type="text/javascript" src="javascripts/jquery.colorbox.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
	//Examples of how to assign the ColorBox event to elements
	$(".colorbox").colorbox({width:"850", height:"1090", iframe:true});
				
	//Example of preserving a JavaScript event for inline calls.
	$("#click").click(function(){ 
		$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("<?php echo $txt['colorbox_text']; ?>");
		return false;
	});
});
</script>
        
<form method="post" action="#send" name="bericht">
<div id="send">
<?php if($error != '') echo $error; ?>
<div style="padding-bottom:10px;"><label for="message"><img src="images/icons/page_add.png" width="16" height="16" /> <strong><?php echo $txt['add_message']; ?></strong></label></div>
<?php echo $txt['link_text_effects']; ?>
<table width="600">
    <tr>
      <td><textarea class="text_area" style="width:570px;" rows="12" name="tekst" id="message"><?php if(!empty($_POST['tekst'])) echo $_POST['tekst']; elseif($_GET['quoteid'] != '') echo '[quote]'.$quote['bericht'].'[/quote]'; elseif($_GET['editid'] != '') echo $edit['bericht']; ?></textarea></td>
    </tr>
    <tr>
      <td><a href="javascript://" onClick="document.bericht.tekst.value += ':)'"><img src="images/emoticons/001.png" width="19" height="19" alt=":)" title=":)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':D'"><img src="images/emoticons/002.png" width="19" height="19" alt=":D" title=":D" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ';)'"><img src="images/emoticons/003.png" width="19" height="19" alt=";)" title=";)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':O'"><img src="images/emoticons/004.png" width="19" height="19" alt=":O" title=":O" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':P'"><img src="images/emoticons/104.png" width="19" height="19" alt=":P" title=":P" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '(H)'"><img src="images/emoticons/006.png" width="19" height="19" alt="(H)" title="(H)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':@'"><img src="images/emoticons/099.png" width="19" height="19" alt=":@" title=":@" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':$'"><img src="images/emoticons/008.png" width="19" height="19" alt=":$" title=":$" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':S'"><img src="images/emoticons/009.png" width="19" height="19" alt=":S" title=":S" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':('"><img src="images/emoticons/010.png" width="19" height="19" alt=":(" title=":(" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':\'('"><img src="images/emoticons/011.png" width="19" height="19" alt=":'(" title=":'(" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':|'"><img src="images/emoticons/012.png" width="19" height="19" alt=":|" title=":|" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '(A)'"><img src="images/emoticons/014.png" width="19" height="19" alt="(A)" title="(A)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':-#'"><img src="images/emoticons/048.png" width="19" height="19" alt=":-#" title=":-#" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '|-)'"><img src="images/emoticons/078.png" width="19" height="19" alt="|-)" title="|-)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '(T)'"><img src="images/emoticons/034.png" width="19" height="19" alt="(T)" title="(T)" /></a>
			<a href="javascript://" onClick="document.bericht.tekst.value += '8-)'"><img src="images/emoticons/050.png" width="19" height="19" alt="8-)" title="8-)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '+o('"><img src="images/emoticons/053.png" width="19" height="19" alt="+o(" title="+o(" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '<o)'"><img src="images/emoticons/075.png" width="19" height="19" alt="<o)" title="<o)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '(L)'"><img src="images/emoticons/015.png" width="19" height="19" alt="(L)" title="(L)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '(K)'"><img src="images/emoticons/028.png" width="19" height="19" alt="(K)" title="(K)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '(F)'"><img src="images/emoticons/025.png" width="19" height="19" alt="(F)" title="(F)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':['"><img src="images/emoticons/043.png" width="19" height="19" alt=":[" title=":[" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '(J)'"><img src="images/emoticons/086.png" width="19" height="19" alt="(J)" title="(J)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '(V)'"><img src="images/emoticons/087.png" width="19" height="19" alt="(V)" title="(V)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':8)'"><img src="images/emoticons/088.png" width="19" height="19" alt=":8)" title=":8)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += ':\'|'"><img src="images/emoticons/093.png" width="19" height="19" alt=":\'|" title=":\'|" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '(BOO)'"><img src="images/emoticons/096.png" width="19" height="19" alt="(BOO)" title="(BOO)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '(Y)'"><img src="images/emoticons/041.png" width="19" height="19" alt="(Y)" title="(Y)" /></a>
            <a href="javascript://" onClick="document.bericht.tekst.value += '(N)'"><img src="images/emoticons/042.png" width="19" height="19" alt="(N)" title="(N)" /></a></td>
    </tr>
    <tr>
      <td style="padding-top:5px;"><input type="submit" value="<?php echo $txt['button']; ?>" name="submit" class="button"/></td>
    </tr>
  </table>
  </div>
</form>
<?php } ?>
