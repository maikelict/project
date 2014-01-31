 <?php
//$allowdIp = array('81.83.254.155', '141.134.1.99', '141.134.12.162', '81.83.251.29', '94.209.73.228', '82.168.230.213', '83.87.185.143', '84.30.194.209', '109.131.220.243', '87.208.103.59', '213.119.44.107');
//if (!in_array($_SERVER['REMOTE_ADDR'], $allowdIp)) {
//require_once('ucpage/screen.html');
//} else {

  session_start();
	include_once('language/language-general.php');
	include_once('includes/config.php');
	include_once('includes/ingame.inc.php');
	
	#Load Page
  $page = $_GET['page'];
  
  $case = $_GET['category'];
	
  if(empty($_SESSION['id'])) $linkpartnersql = mysql_query("SELECT titel, url FROM linkpartners ORDER BY volgorde ASC");
	
	#ban_check
	if(mysql_num_rows(mysql_query("SELECT `user_id` FROM `ban` WHERE `ip`='".$_SERVER['REMOTE_ADDR']."'")) > 0) header("location: banned.php");
	
  #Ingame dingen
  if(isset($_SESSION['id'])){
    #hash maken
    $md5hash  = md5($_SERVER['REMOTE_ADDR'].",".$_SESSION['naam']);
  
    #Controleren van de hash.
    #Is de has niet goed dan uitloggen en inloggen opnieuw laden
    if ($_SESSION['hash'] <> $md5hash) include('logout.php');
  
    mysql_query("UPDATE `gebruikers` SET `online`='".time()."' WHERE `user_id`='".$_SESSION['id']."'");
	
  #Load User Information
  $gebruikers = mysql_query("SELECT * FROM gebruikers WHERE user_id = '".$_SESSION['id']."'");
  $gebruiker = mysql_fetch_assoc(mysql_query("SELECT g.*, gi.*, SUM(`Poke ball` + `Great ball` + `Ultra ball` + `Premier ball` + `Net ball` + `Dive ball` + `Nest ball` + `Repeat ball` + `Timer ball` + `Master ball` + `Potion` + `Super potion` + `Hyper potion` + `Full heal` + `Revive` + `Max revive` + `Pokedex` + `Pokedex chip` + `Fishing rod` + `Cave suit` + `Bike` + `Protein` + `Iron` + `Carbos` + `Calcium` + `HP up` + `Rare candy` + `Duskstone` + `Firestone` + `Leafstone` + `Moonstone` + `Ovalstone` + `Shinystone` + `Sunstone` + `Thunderstone` + `Waterstone` + `Dawnstone` + `TM01` + `TM02` + `TM03` + `TM04` + `TM05` + `TM06` + `TM07` + `TM08` + `TM09` + `TM10` + `TM11` + `TM12` + `TM13` + `TM14` + `TM15` + `TM16` + `TM17` + `TM18` + `TM19` + `TM20` + `TM21` + `TM22` + `TM23` + `TM24` + `TM25` + `TM26` + `TM27` + `TM28` + `TM29` + `TM30` + `TM31` + `TM32` + `TM33` + `TM34` + `TM35` + `TM36` + `TM37` + `TM38` + `TM39` + `TM40` + `TM41` + `TM42` + `TM43` + `TM44` + `TM45` + `TM46` + `TM47` + `TM48` + `TM49` + `TM50` + `TM51` + `TM52` + `TM53` + `TM54` + `TM55` + `TM56` + `TM57` + `TM58` + `TM59` + `TM60` + `TM61` + `TM62` + `TM63` + `TM64` + `TM65` + `TM66` + `TM67` + `TM68` + `TM69` + `TM70` + `TM71` + `TM72` + `TM73` + `TM74` + `TM75` + `TM76` + `TM77` + `TM78` + `TM79` + `TM80` + `TM81` + `TM82` + `TM83` + `TM84` + `TM85` + `TM86` + `TM87` + `TM88` + `TM89` + `TM90` + `TM91` + `TM92` + `HM01` + `HM02` + `HM03` + `HM04` + `HM05` + `HM06` + `HM07` + `HM08`) AS items				  FROM gebruikers AS g INNER JOIN gebruikers_item AS gi 
																  ON g.user_id = gi.user_id 
																  INNER JOIN gebruikers_tmhm AS gtmhm
																  ON g.user_id = gtmhm.user_id
																  WHERE g.user_id = '".$_SESSION['id']."'
																  GROUP BY g.user_id"));

	#Als account_code 0 is, verbannen!
	if($gebruiker['account_code'] == 0) header('Location: banned.php');
	
	#Title ff setten
	#$title_user = $gebruiker['username'].' '.$txt['online_at'].' ';
  
  if(($gebruiker['pagina'] != 'duel') AND ($page != 'pokemoncenter') AND (!$_SESSION['duel']['duel_id'])){
    $tour_sql = mysql_query("SELECT * FROM toernooi WHERE deelnemers!='' AND no_1='0' ORDER BY toernooi DESC LIMIT 1");
    if(mysql_num_rows($tour_sql) > 0){
      $tour_info = mysql_fetch_assoc($tour_sql);
      $round_sql = mysql_query("SELECT * FROM `toernooi_ronde` WHERE toernooi='".$tour_info['toernooi']."' AND winnaar_id = '0' AND (user_id_1 = '".$_SESSION['id']."' OR user_id_2 = '".$_SESSION['id']."')"); 
      if(mysql_num_rows($round_sql) > 0){ 
        $round_info = mysql_fetch_assoc($round_sql);
        $tour_over = strtotime($tour_info['tijd'])-strtotime(date("H:i:s"));
        if($tour_over < 300 AND $tour_over > 0){
          if(!$_SESSION['toernooi_sent']){
            $_SESSION['toernooi_sent'] = TRUE;
            $time = floor($tour_over/60);
            mysql_query("INSERT INTO `gebeurtenis` (`datum` ,`ontvanger_id` ,`bericht`)
              VALUES ('".date('Y-m-d H:i:s')."', '".$_SESSION[ 'id']."', 'Jouw toernooi gevecht begint over &plusmn;".$time." minuten. Zorg dat je pokemon gereed zijn.');");
          }
          header("refresh: ".$tour_over."; url=index.php?page=attack/tour_fight");
        }
        elseif(($tour_over > -90 AND $tour_over < 0) AND ($_GET['page'] != "attack/tour_fight") AND ($_GET['page'] != "attack/duel/duel-attack")){
          $_SESSION['toernooi_sent'] = FALSE;
          $page = 'attack/tour_fight';
        }
      }  
      else $_SESSION['toernooi_sent'] = FALSE; 
    }
  }
    	
	if($gebruiker['premiumaccount'] >= 1) $premium_txt =  $gebruiker['premiumaccount'].' '.$txt['stats_premiumtext'];
	else $premium_txt = '<a href="?page=area-market" style="color:#000;">'.$txt['stats_become_premium'].'</a>';
	
	$silver = highamount($gebruiker['silver']);
	  $gold = highamount($gebruiker['gold']);
	  $bank = highamount($gebruiker['bank']);
	

	if($gebruiker['rank'] == '34'){
	$gebruiker['rankexpnodig'] == '2147483647';
	if($gebruiker['rankexpnodig'] == '2147483647'){
	$gebruiker_rank['procent'] = '100';
	}
	}else{
	$gebruiker_rank = rank($gebruiker['rank']);
	if($gebruiker['rankexp'] > 0) $gebruiker_rank['procent'] = round(($gebruiker['rankexp']/$gebruiker['rankexpnodig'])*100);
	else $gebruiker_rank['procent'] = 0;
	}
	if($gebruiker['itembox'] == 'Bag') $gebruiker['item_over'] = 20-$gebruiker['items'];
	elseif($gebruiker['itembox'] == 'Yellow box') $gebruiker['item_over'] = 50-$gebruiker['items'];
	elseif($gebruiker['itembox'] == 'Blue box') $gebruiker['item_over'] = 100-$gebruiker['items'];
	elseif($gebruiker['itembox'] == 'Red box') $gebruiker['item_over'] = 250-$gebruiker['items'];
  
	$arr = explode(",", $gebruiker['pok_bezit']);
	$result = array_unique($arr);
	$gebruiker_pokemon['procent'] = round((count($result)/650)*100);
	
  #Load User Pokemon
  ############################################### pw.wereld hoeft niet als we alle 5e generatie pokemon images hebben :)
 $pokemon_sql = mysql_query("SELECT pw.spel_id, pw.naam, pw.type1, pw.type2, pw.zeldzaamheid, pw.groei, pw.aanval_1, pw.aanval_2, pw.aanval_3, pw.aanval_4, ps.* FROM pokemon_wild AS pw INNER JOIN pokemon_speler AS ps ON ps.wild_id = pw.wild_id  WHERE ps.user_id='".$_SESSION['id']."' AND ps.opzak='ja' ORDER BY ps.opzak_nummer ASC");
  $gebruiker['in_hand'] = mysql_num_rows($pokemon_sql);
  
  #Load User Messages
  $inbox = mysql_num_rows(mysql_query("SELECT `id` FROM `berichten` WHERE `ontvanger_id`='".$_SESSION['id']."'"));
  $inbox_new = mysql_num_rows(mysql_query("SELECT `id` FROM `berichten` WHERE `ontvanger_id`='".$_SESSION['id']."' AND `gelezen`='0'"));

	if($gebruiker['admin'] >= 1) $inbox_allowed = 1000;
	elseif($gebruiker['premiumaccount'] >= 1) $inbox_allowed = 60;
	else $inbox_allowed = 30;
  
  if($inbox_allowed <= $inbox) $inbox_txt = '<a href="?page=inbox" style="color:#DC0000;">'.$txt['stats_full'].'</a>';
  elseif($inbox_new >= 1) $inbox_txt = '<a href="?page=inbox" style="color:#0bbe03;">'.$inbox_new.' '.$txt['stats_new'].'</a>';
  else $inbox_txt = '<a href="?page=inbox" style="color:#000;">'.$inbox.' / '.$inbox_allowed.'</a>';

  #Load User Events
  $event_new = mysql_num_rows(mysql_query("SELECT `id` FROM `gebeurtenis` WHERE `ontvanger_id`='".$_SESSION['id']."' AND `gelezen`='0'"));

	if($event_new == 0) $event_txt = '<a href="?page=events" style="color:#000;">'.$txt['stats_none'].'</a>'; 
    else $event_txt = '<a href="?page=events" style="color:#0bbe03;">'.$event_new.' '.$txt['stats_new'].'</a>';
  }
  else{
    #Als je op de inloggen knop drukt, includes/login.php includen voor de meldingen
    if(isset($_POST['login']))
      include("includes/login.php");
  }
  #Berekenen wanneer de reclamepagina komt
  if(($_SESSION['naam'] != "") AND (($gebruiker['premiumaccount'] == 0) OR ($gebruiker['reclame'] == 1))) $reclamechance = rand(1,50);
  
  #Check if you're asked for a duel MOET OOK ANDERS -> Event! ;)
  $duel_sql = mysql_query("SELECT `id`, `datum`, `uitdager`, `tegenstander`, `bedrag`, `status` FROM `duel` WHERE `tegenstander`='".$gebruiker['username']."' AND (`status`='wait') ORDER BY id DESC LIMIT 1");
					
  #?page= systeem opbouwen
  if(empty($page)) $page = 'home';
  elseif(!file_exists($page.'.php')) $page = 'notfound';
  elseif(empty($_SESSION['id'])) $page = $page;
  elseif((($gebruiker['captcha_time']+900) < time()) && (in_array($_GET['page'], $captcha_page_check))) $page = 'captcha-ingame-check';
  elseif($page == 'attack/tour_fight') $page = $page;
  elseif($page == 'attack/wild2/wild-attack') $page = $page;
  else{
    $duel_test = mysql_query("SELECT `id` FROM `duel` WHERE `status`='wait' AND `uitdager`='".$_SESSION['naam']."'");
	  #Als deze sessie bestaat deze pagina weergeven.
    if(!empty($_SESSION['aanvalnieuw'])){
		  #Code opvragen en decoderen
		  $link = base64_decode($_SESSION['aanvalnieuw']);
		  #Code splitten, zodat informatie duidelijk word
		  list ($nieuweaanval['pokemonid'], $nieuweaanval['aanvalnaam']) = split ('[/]', $link);
		  #Andere huidige pagina toewijzen
		  $page = "includes/poke-newattack";
    }
    elseif(!empty($_SESSION['evolueren'])){
		  #Code opvragen en decoderen
		  $link = base64_decode($_SESSION['evolueren']);
		  #Code splitten, zodat informatie duidelijk word
		  list ($evolueren['pokemonid'], $evolueren['nieuw_id']) = split ('[/]', $link);
		  #Andere huidige pagina toewijzen
		  $page = "includes/poke-evolve";
    }
    elseif(($gebruiker['eigekregen'] == 0) OR ($_SESSION['eikeuze'] == 1))
      $page = "beginning";
    #Is speler bezig met aanvallen?
    elseif($gebruiker['pagina'] == 'attack'){
      $page = "attack/wild/wild-attack";     
      if($gebruiker['test'] == 1) $page = "attack/wild2/wild-attack";  
      $res = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `aanval_log` WHERE `user_id`='".$_SESSION['id']."'"));
      $_SESSION['attack']['aanval_log_id'] = $res['id'];
    }
    elseif($gebruiker['pagina'] == 'trainer-attack'){
      $page = "attack/trainer/trainer-attack";
      $res = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `aanval_log` WHERE `user_id`='".$_SESSION['id']."'"));
      $_SESSION['attack']['aanval_log_id'] = $res['id'];
    }
    elseif(($gebruiker['pagina'] == 'duel') AND (mysql_num_rows($duel_test) > 0))
      $page = $_GET['page'];
    elseif($gebruiker['pagina'] == 'duel')
      $page = "attack/duel/duel-attack";
    #Word speler uit gedaagd voor duel?
    elseif(mysql_num_rows($duel_sql) == 1)
     $page = "attack/duel/invited"; 
  }
  
  if((($page != "attack/wild/wild-attack") AND ($page != "attack/wild2/wild-attack") AND ($gebruiker['pagina'] == 'attack')) OR (($page != "attack/trainer/trainer-attack") AND ($gebruiker['pagina'] == 'trainer-attack'))){
    $res = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `aanval_log` WHERE `user_id`='".$_SESSION['id']."'"));
    mysql_query("UPDATE `gebruikers` SET `pagina`='attack_start' WHERE `user_id`='".$_SESSION['id']."'");
    mysql_query("DELETE FROM `pokemon_speler_gevecht` WHERE `user_id`='".$_SESSION['id']."'");
    mysql_query("DELETE FROM `pokemon_wild_gevecht` WHERE `aanval_log_id`='".$res['id']."'");
    mysql_query("DELETE FROM `aanval_log` WHERE `user_id`='".$_SESSION['id']."'");
  }

  if(($page != "attack/duel/duel-attack") AND ($gebruiker['pagina'] == 'duel')){
    mysql_query("UPDATE `gebruikers` SET `pagina`='duel_start' WHERE `user_id`='".$_SESSION['id']."'");
    mysql_query("DELETE FROM `pokemon_speler_gevecht` WHERE `user_id`='".$_SESSION['id']."'");
    mysql_query("DELETE FROM `duel` WHERE `uitdager`='".$_SESSION['naam']."' OR `tegenstander`='".$_SESSION['naam']."'");
  }
  
  $str_tijd_nu = strtotime(date('Y-m-d H:i:s'));
  $jail_tijd = (strtotime($gebruiker['gevangenistijdbegin'])+$gebruiker['gevangenistijd'])-$str_tijd_nu;
  $pokecen_tijd = (strtotime($gebruiker['pokecentertijdbegin'])+$gebruiker['pokecentertijd'])-$str_tijd_nu;
  
  #Work Check
  if(!empty($gebruiker['soortwerk'])){ 
    $werken_tijd = strtotime($gebruiker['werktijdbegin'])+$gebruiker['werktijd'];
    #Tijd die overblijft
    $tijdwerken = $werken_tijd-$str_tijd_nu;
    if($tijdwerken < 0)
      include_once('includes/work-inc.php');
    else{
      $wait_time = $tijdwerken;
      $type_timer = 'work';
      if(!page_timer($page,'work')) $page = 'includes/wait';
    }
  }
  elseif($jail_tijd > 0){ 
    #Tijd die overblijft
    if($jail_tijd >= 0){
      if(!page_timer($page,'jail')) $page = 'includes/wait-jail';
    }
  }
  elseif($pokecen_tijd > 0){ 
    #Tijd die overblijft
    $wait_time = $pokecen_tijd;
    if($wait_time >= 0){
      $type_timer = 'pokecenter';
      if(!page_timer($page,'jail')) $page = 'includes/wait';
    }
  }
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="content-style-type" content="text/css" />
    <meta http-equiv="content-language" content="nl, eng, pl" />
    <meta name="description" content="<?php echo $site_description; ?>" />
    <meta name="keywords" content="<?php echo $site_keywords; ?>" />
    <meta name="robots" content="index, follow" />
    <meta name="copyright" content="<?php echo $site_copyright; ?>" />
    <meta name="language" content="nl, eng, pl, es, de" />
    <title><?php echo $title_user; ?>Pokemon-Area</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
    <link type="text/css" media="screen" rel="stylesheet" href="stylesheets/colorbox.css" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <!--[if lt IE 7.]>
		<script type="text/javascript" src="javascripts/iepngfix.js"></script>
		<link rel="stylesheet" type="text/css" href="stylesheets/style-ie6.css" />
	<![endif]-->
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
    <script type="text/javascript" src="javascripts/clouds.js"></script>
    <!--<script type="text/javascript" src="javascripts/sifr.js"></script>-->
    <script type="text/javascript" src="javascripts/time.js"></script>
    <script type="text/javascript" src="javascripts/timer.js"></script>
    <script type="text/javascript" src="javascripts/tooltip.js"></script>
    <?php if(!empty($_SESSION['id'])) { ?><script type="text/javascript" src="javascripts/dropdownmenu.js"></script><?php } ?>
	<script type="text/javascript" src="javascripts/ads-bar.js"></script>  
	<script type="text/javascript">
    function popUp(URL) {
      day = new Date();
      id = day.getTime();
      eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=1,statusbar=1,menubar=0,resizable=1,width=1020,height=600');");
    }
	</script>
	<script type="text/javascript">

	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-23950355-1']);
	_gaq.push(['_trackPageview']);

	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();

	</script>
	<!-- Piwik -->
		<script type="text/javascript">
		var pkBaseURL = (("https:" == document.location.protocol) ? "https://www.sevado.nl/stats/" : "http://www.sevado.nl/stats/");
		document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
		</script><script type="text/javascript">
		try {
		var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 6);
		piwikTracker.trackPageView();
		piwikTracker.enableLinkTracking();
		} catch( err ) {}
		</script><noscript><p><img src="http://www.sevado.nl/stats/piwik.php?idsite=6" style="border:0" alt="" /></p></noscript>
	<!-- End Piwik Tracking Code -->
</head>

<body>
<div id="clouds">
	<span class="cloud-01"></span>
	<span class="cloud-02"></span>
</div>
<div id="wrap">
	<div id="menu">
	<?php if(empty($_SESSION['id'])){ ?>
    	<a href="?page=home">Home</a>       
    	<a href="?page=nieuws">Nieuws</a>          
    	<a href="?page=information&category=game-info">Speluitleg</a>    
		<a href="?page=forgot-username">Username?</a>
    	<a href="?page=forgot-password">Wachtwoord?</a>            
    	<a href="?page=register"><u>Registreer</u></a>
			<?php } else { ?>
		<a href="?page=home">Home</a>       
    	<a href="?page=nieuws">Nieuws</a>          
    	<a href="?page=information&category=game-info">Speluitleg</a>   
    	<a href="?page=forum-categories">Forum</a>
		<a href="?page=helpdesk_send">Helpdesk</a>   
		<?php if($gebruiker['admin'] >= 1){ echo'<a href="?page=admin/panel">Admin Panel</a>'; } ?>
    	<a href="?page=logout"><b><u>Uitloggen</u></b></a>
		<?php } ?>
    </div>
    <div id="header">
    	<div id="hq">
		<?php if(empty($_SESSION['id'])){ ?>
                        	<form method="post" action="<?php echo strip_tags($_SERVER['PHP_SELF']);?>">
                            <?php if($inlog_error != '') echo '<div class="red_error">'.$inlog_error.'</div>'; else echo ''; ?>
                            <div class="hq"><b>Inloggen</b></div>
							<div class="hq"></div>
							<div class="hq">Username</div>
                            <div class="hq"><input type="text" name="username" class="text_long" id="login-user" value="<?php if(isset($_POST['username'])) echo $_POST['username']; ?>" maxlength="12" /></div>
							<div class="hq">Password</div>
                            <div class="hq"><input type="password" name="password" class="text_long" id="login-pass" value="<?php if(isset($_POST['password'])) echo $_POST['password']; ?>" /></div>
                            <div class="hq"><input type="submit" name="login" class="button_mini" value="<?php echo $txt['login_button']; ?>" style="margin-bottom:8px;" /></div>

                        	</form>
						</div>
				<?php } else { ?>
          	<div class="hq">
			<img src="images/icons/man.png" width="16" height="16" alt="Username" />
            <?php echo $gebruiker['username']; ?><br />
			<img src="images/icons/wereld.png" width="16" height="16" alt="Regio" />
			<?php 
								if($gebruiker['wereld'] == Orange){ echo'Orange Islands';} else echo $gebruiker['wereld'];?></span>
<br />
			<img src="images/icons/silver.png" width="16" height="16" alt="Silver" />
			<?php echo highamount($gebruiker['silver']); ?><br />
			<img src="images/icons/gold.png" width="16" height="16" alt="Gold" />
			<?php echo highamount($gebruiker['gold']); ?><br />
			<img src="images/icons/bank.png" width="16" height="16" alt="Bank" />
			<?php echo highamount($gebruiker['bank']); ?>
			</div>	   
            

          	<div class="hq">  
			<img src="images/icons/berichtongelezen.png" width="16" height="16" alt="Berichten" />
            <?php echo $inbox_txt; ?><br />
			<img src="images/icons/gebeurtenis.png" width="16" height="16" alt="Berichten" />
			<?php echo $event_txt; ?><br />
			<img src="images/icons/star-full-little.png" width="16" height="16" alt="Berichten" />
			<? echo $premium_txt; ?><br />
			<img src="images/icons/statistieken_leden.png" width="16" height="16" alt="Rangvordering" />
			<div class="stats-container">
									<div style="width: <? if($gebruiker['rank'] == '34'){ echo '100'; }else{ echo $gebruiker_rank['procent'];} ?>%;"><? if($gebruiker['rank'] == '34'){ echo '100'; }else{ echo $gebruiker_rank['procent'];} ?>%<?php echo $gebruiker_rank['ranknaam']; ?></div>
								</div>
			<img src="images/icons/statistieken_online.png" width="16" height="16" alt="Rangvordering" />
			<div class="stats-container">
									<div style="width: <? echo $gebruiker_pokemon['procent']; ?>%;"><? echo $gebruiker_pokemon['procent']; ?>%</div>
								</div>
            </div>
			
			<div class="hqteam">
			<?php if($page == "extended"){
			echo "<div class='pokemon_hand_box_tekst'><ul><li>Team wordt bekeken</li></ul></div>";
			}elseif($page == "modify-order" || $page == "modify-order-old" || $page == "release" || $page == "pokemoncenter" || $page == "shiny-specialist"){
			echo "<div class='pokemon_hand_box_tekst'><ul><li>Team wordt bewerkt</li></ul></div>";
			}elseif($page == "attack/wild/wild-attack" || $page == "attack/trainer/trainer-attack" || $page == "attack/duel/duel-attack" || $page == "travel"){
			echo "<div class='pokemon_hand_box_tekst'><ul><li>Team wordt gebruikt</li></ul></div>";
			}else{?>
			<div class="pokemon_hand_box">
						<ul>
							<?
                      #Show ALL pokemon in hand
                      if($gebruiker['in_hand'] > 0){
                        while($pokemon = mysql_fetch_assoc($pokemon_sql)){
                          $dateadd = strtotime(date('Y-m-d H:i:s'))-600;
                          $date = date('Y-m-d H:i:s', $dateadd);
                          #Check if Pokemon have to hatch
                          if(($pokemon['ei'] == 1) AND ($pokemon['ei_tijd'] < $date)){
                            update_pokedex($pokemon['wild_id'],'','ei');
                            mysql_query("UPDATE pokemon_speler SET ei='0' WHERE id='".$pokemon['id']."'");
                          }
                          $pokemon = pokemonei($pokemon);
                          $pokemon['naam'] = pokemon_naam($pokemon['naam'],$pokemon['roepnaam']);
                          $popup = pokemon_popup($pokemon, $txt);
						  if($pokemon['leven'] == 0) $pokemonstatus = '<img src="images/icons/bullet_red.png">';
						  else $pokemonstatus = '<img src="images/icons/bullet_green.png">';
                          echo '<li><a href="#" class="tooltip" onMouseover="showhint(\''.$popup.
						  '\', this)"><div class="img"><img src="'.$pokemon['animatie'].
						  '" width="32" height="32" alt="'.$pokemon['naam'].
						  '" /></div></a></li>';
                        
						}
                      }
                      ?></ul>
                      </div>
					  <? } ?>
			</div>
			<div class="hqteamicon">
			</div>
			<?php } ?>
				
        </div> 
    </div>

</div>


<div id="wrap2">   
	<div id="berichten"> 
	
	</div>
   <div id="links">
	<?php if(empty($_SESSION['id'])){ ?>
    <div class="sub">
     <img src="images/icons/instructies.png" width="16" height="16" alt="icon" />
    Algemeen
    </div>
    
    <div class="links">
        <a href="?page=home">Home</a><br />
        <a href="?page=register">Registreer</a><br />
		<a href="?page=forgot-username">Username?</a>
    	
    </div> 

    <div class="rechts">
        <a href="?page=nieuws">Nieuws</a><br />
        <a href="?page=information&category=game-info">Uitleg</a><br />
		<a href="?page=forgot-password">Wachtwoord?</a>  
    </div> 
	<div class="sub">
     <img src="images/icons/stats.png" width="16" height="16" alt="icon" />
    Statistieken
    </div>
    <?php 
							#Tel leden online
							$expire = 10;
							$sql = "SELECT username, premiumaccount, admin, online, buddy, blocklist FROM gebruikers WHERE online+'1000'>'".time()."' ORDER BY rank DESC, rankexp DESC, username ASC";
							$records = query_cache("online",$sql,$expire);
							$stats['online'] = count($records);
							#Tel aantal leden
							$expire = "300";
							$sql = "SELECT `user_id` FROM `gebruikers` WHERE `account_code`='1'";
							$stats['aantal'] = query_cache_num('stat-aantal',$sql,$expire);
							#Aantal leden online tellen
							$sql = "SELECT `online`, `username` FROM `gebruikers` WHERE `account_code`='1' AND `aanmeld_datum` LIKE '%".date("Y-m-d")."%'  ORDER BY `user_id`";
							$stats['nieuw'] = query_cache_num('stat-nieuw',$sql,$expire);
							?>
    <div class="links">
                                    <img src="images/icons/lid.png" width="16" height="16" alt="Totaal">&nbsp;&nbsp;Aantal:<br />
                                	<img src="images/icons/lid.png" width="16" height="16" alt="Totaal">&nbsp;&nbsp;Online:<br />
                              		<img src="images/icons/lid.png" width="16" height="16" alt="Totaal">&nbsp;&nbsp;Nieuw:<br />
    </div> 
	<div class="rechts">
	<font color="#2a2a2a"><?php echo $stats['aantal']; ?></font><br />
	<font color="#2a2a2a"><?php echo $stats['online']; ?></font><br />
	<font color="#2a2a2a"><?php echo $stats['nieuw']; ?></font><br />
	</div>
	
	
	    <div class="sub">
     <img src="images/icons/lid.png" width="16" height="16" alt="icon" />
    Linkpartners
    </div>
    
    <div class="links">
       	<ul class="linkpartners">
							<?php while($linkpartner = mysql_fetch_assoc($linkpartnersql)){
                                echo '<li><a href="'.$linkpartner['url'].'">'.$linkpartner['titel'].'</a></li>';
                            }
                            ?>
                        </ul>
    	
    </div> 

	<?php } else { ?>
    <div class="sub">
     <img src="images/icons/instructies.png" width="16" height="16" alt="icon" />
    Algemeen
    </div>
    
    <div class="links">
        <a href="?page=home">Home</a><br />
        <a href="?page=Crew"><?php echo $txt['menu_area-crew']?></a><br />
        <a href="?page=rankinglist"><?php echo $txt['menu_rankinglist']; ?></a><br />
        <a href="?page=statistics"><?php echo $txt['menu_statistics']; ?></a><br />
        <a href="?page=promotion"><?php echo $txt['menu_promotion_for_silver']; ?></a>
        <u><a href="?page=logout"><?php echo $txt['menu_logout']; ?></a></u> 
    </div> 

    <div class="rechts">
        <a href="?page=account-options&category=personal">Opties</a><br />
		<a href="?page=forum-categories"><?php echo $txt['menu_forum']; ?></a><br />
		<a href="?page=inbox">Inbox</a><br />
		<a href="?page=area-market">Area Market</a>
    </div> 
    
    <div class="sub">
     <img src="images/icons/vlag.png" width="16" height="16" alt="icon" />
    Acties
    </div>
    
    <div class="links">
        <a href="?page=attack/attack_map"><?php echo $txt['menu_attack'] ?></a><br />
		<a href="?page=attack/gyms"><?php echo $txt['menu_gyms'] ?></a><br />
		<?php if($gebruiker['wereld'] == "Kanto"){ echo '<a href="?page=attack/league_kanto">League</a><br />';
		}elseif($gebruiker['wereld'] == "Johto"){ echo '<a href="?page=attack/league_johto">League</a><br />';
		}elseif($gebruiker['wereld'] == "Hoenn"){  echo '<a href="?page=attack/league_hoenn">League</a><br />';
		}elseif($gebruiker['wereld'] == "Sinnoh"){  echo '<a href="?page=attack/league_sinnoh">League</a><br />';
		}elseif($gebruiker['wereld'] == "Unova"){  echo '<a href="?page=attack/league_unova">League</a><br />';
		}elseif($gebruiker['wereld'] == "Orange"){  echo '<a href="?page=attack/league_orange">League</a><br />'; }
        echo '<a href="?page=work">'.$txt['menu_work'].'</a><br />';
        if($gebruiker['rank'] >= 5) echo '<a href="?page=attack/duel/invite">'.$txt['menu_duel'].'</a><br />';
        ?>
    </div> 

    <div class="rechts">
        <?php if($gebruiker['rank'] >= 5) echo'<a href="?page=traders">'.$txt['menu_traders'].'</a><br />';
        if($gebruiker['rank'] >= 4) echo '<a href="?page=race-invite">'.$txt['menu_race'].'</a><br />';
        if($gebruiker['rank'] >= 3) echo '<a href="?page=steal">'.$txt['menu_steal'].'</a><br />
		<a href="?page=spy">'.$txt['menu_spy'].'</a><br />';
        if($gebruiker['rank'] >= 18) echo '<a href="?page=lvl-choose">'.$txt['menu_choose_level'].'</a>'; ?>   
    </div>   

		<div class="sub">
     <img src="images/icons/gebouw.png" width="16" height="16" alt="icon" />
    Gebouwen
    </div>
    
    <div class="links">
        <a href="?page=pokemoncenter">Pok&eacute;center</a><br />
        <a href="?page=market&shopitem=balls"><?php echo $txt['menu_market']; ?></a><br />
        <a href="?page=bank"><?php echo $txt['menu_bank']; ?></a><br />
		<a href="?page=daycare"><?php echo $txt['menu_daycare']?></a><br />
		<a href="?page=travel">Travel</a>
		
    </div> 

    <div class="rechts">
					<a href="?page=house-seller"><?php echo $txt['menu_house_seller'] ?></a><br />
					<a href="?page=specialisten">Specialisten</a><br /> 
<?php if($gebruiker['rank'] >= 4) echo '<a href="?page=transferlist">'.$txt['menu_transferlist'].'</a><br />'; ?>					
					<a href="?page=jail"><?php echo $txt['menu_jail']; ?></a><br />
					<a href="?page=casino">Casino</a>
    </div> 
	
		<div class="sub">
     <img src="images/icons/ball.gif" width="16" height="16" alt="icon" />
    Mijn Pokemon
    </div>
	
	
    <div class="links">
        <a href="?page=modify-order"><?php echo $txt['menu_modify_order']; ?></a><br />
        <?php if($gebruiker['rank'] >= 4) echo '<a href="?page=sell">'.$txt['menu_sell'].'</a><br />';?>
		<a href="?page=house&option=bringaway"><?php echo $txt['menu_house_bringaway']; ?></a><br />
		<a href="?page=items"><?php echo $txt['menu_items']; ?></a>
    </div> 

    <div class="rechts">
        <?php echo '<a href="?page=extended">'.$txt['menu_extended'].'</a><br />';
				if($gebruiker['rank'] >= 5) echo '<a href="?page=release">Vrijlaten</a><br />'; ?>
		<a href="?page=house&option=pickup"><?php echo $txt['menu_house_take']; ?></a><br />
		<?php if($gebruiker['Badge case'] == 1) echo '<a href="?page=badges">'.$txt['menu_badge_box'].'</a><br />'; ?>
    </div> 
	
			<div class="sub">
     <img src="images/icons/comments.png" width="16" height="16" alt="icon" />
    Communicatie
    </div>
	
	
    <div class="links">
         <a href="?page=buddylist">Buddylijst</a><br />
		 <a href="?page=search-user">Zoek speler</a>
    </div> 

    <div class="rechts">
		 <a href="?page=blocklist">Blocklijst</a><br />
		 <a href="?page=events">Gebeurtenis</a>
    </div> 
	

	<?php } ?>
   </div> 

<div id="center">
	<div class="subtitel">Welkom op Pokemon Area</div>
		<div id="center_tekst">
		<?php include(''.$page.'.php'); ?>
        </div>
	<?php if(empty($_SESSION['id'])){ ?>
		<div class="subtitel">Laatste nieuws</div>
		<div id="center_tekst">
<?php 
$nieuws = mysql_query("SELECT * FROM nieuws WHERE nieuws_id = 1 ORDER BY id DESC LIMIT 1");
while ($selnieuws = mysql_fetch_assoc($nieuws))
    {

echo "<b>".$selnieuws['titel']."</b>";
echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<span style='float:right;'>".$selnieuws['datum']."</span>";
echo "<hr />";
echo $selnieuws['bericht'];
echo "<br /><br /><hr />
<a href='?page=nieuws'>Lees meer</a>";
}
?></div>
	<?php } else { ?>
	<div class="subtitel">Online leden</div>
		<div id="center_tekst">
<?php include("online.php");  ?>      </div>
	<?php } ?>
        
</div>
<div id="footer">
<a href="#">Algemene Voorwaarde</a>  |     <a href="?page=contact">Contact</a>    |  <a href="#">Info</a> 

<p>Pok&eacute;mon And All Respective Names are Trademark &copy; of Nintendo 1996-2013</p>

</div>

</div>


</body>
</html>
<? //} ?>
