<?php include 'functions.php'; ?>
<!DOCTYPE html>
<html lang="sv">
	<head>
		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Skånepolisen regler</title>
		<meta name="description" content="">
		<meta name="author" content="Arvid Lunnemark">
		<meta name="keywords" content="skånepolisen,spel,regler,irl,scotland yard,fubbickarna,mr. gött mos">

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="mobile-web-app-capable" content="yes">
		
		<link rel="stylesheet" type="text/css" href="<?php echo $cssLink; ?>" />
		<script type="text/javascript" charset="utf-8">(function(a,b,c){if(c in b&&b[c]){var d,e=a.location,f=/^(a|html)$/i;a.addEventListener("click",function(a){d=a.target;while(!f.test(d.nodeName))d=d.parentNode;"href"in d&&(chref=d.href).replace(e.href,"").indexOf("#")&&(!/^[a-z\+\.\-]+:/i.test(chref)||chref.indexOf(e.protocol+"//"+e.host)===0)&&(a.preventDefault(),e.href=d.href)},!1)}})(document,window.navigator,"standalone");</script>
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		
		<!-- Launch images -->
		<link rel="apple-touch-startup-image" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)" 
		href="/apple/skanepolisen-launch-image-big.png">
		<link rel="apple-touch-startup-image" media="(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 2)" 
		href="/apple/skanepolisen-launch-image-small.png">

		<!-- Icons -->
		<link rel="shortcut icon" href="/favicon.ico">
		<link rel="apple-touch-icon" sizes="120x120" href="/apple/skanepolisen-touch-icon.png">
		<link rel="apple-touch-icon" sizes="80x80" href="/apple/skanepolisen-touch-icon-spotlight.png">
		<link rel="icon" sizes="196x196" href="/apple/skanepolisen-android-icon.png">
	</head>

	<body>
		
		<div id="background-img"></div>
		
		<div id="scrolling">
		
		<div class="whiteBackground">
			<div class="indented">
			
			<?php if (returnGameField(3) > 0 && $_SESSION["authenticated"]) { ?>
				<div style="text-align: center; margin-top: 1em;">
					<a href="dynamicMode.php" style="color:red;">Jakten på Mr. Gött Mos är igång! Klicka här för att komma till den.</a>
				</div>
			<?php } ?>
			
			<header>
				<h1>Regler</h1>
			</header>
			<ul>
				<li>Lite som Scotland Yard.</li>
				<li>Man får inte cykla genom Palladium.</li>
				<li>Fubbickarna får stå på samma position.</li>
				<li>Man får inte gå mot rött ljus, gå över gatan om inte det är på övergångsställen, eller bryta mot någon trafikregel.</li>
				<li>Båda Mr. Gött Mos delar på biljetterna.</li>
				<li>Mr. Gött Mos får 6 gång-biljetter, 4 cykel-biljetter och 3 cykelräd-biljetter tillsammans vid starten. De får Fubbickarnas använda biljetter.</li>
				<li>Fubbickarna börjar med 10 gång-biljetter, 8 cykel-biljetter och 4 cykelräd-biljetter var.</li>
				<li>Efter varannan omgång så ska en Mr. Gött Mos alltid redovisa sin position och de gemensamma biljetterna. Båda Mr. Gött Mos bestämmer tillsammans vem som ska visa sig.</li>
				<li>När en fubbick hamnat på samma position som Mr. Gött Mos startar en 5 minuter lång jakt. I den får fubbicken som hittade Mr. GM och båda Mr. GM endast gå, det vill säga de måste alltid ha minst en fot i marken. De övriga fubbickarna får använda sin cykel, men inte springa.</li>
				<li>Platsdelning via GPS får inte förekomma.</li>
				<li><b>Man får inte fuska.</b></li>
			</ul>
			</div>
			<footer>
				<table class="fullwidth">
					<tr>
						<?php
							$goBack = $_SERVER['HTTP_REFERER'];
							if ($goBack == $skanepolisen_url."/rules.php") {
								$goBack = $skanepolisen_url."/index.php";
							} elseif (strpos($goBack, $skanepolisen_url) === false) {
								$goBack = $skanepolisen_url."index.php";
							} elseif (strpos($goBack, 'iframe.php') !== false) {
								$goBack = $skanepolisen_url."/index.php";
							}
						?>
						<td class="leftcell"><a href="<?php echo $goBack; ?>">< Gå tillbaka</a></td>
						<td class="rightcell"><a href="/skanepolisenkarta.php">Karta</a></td>
					</tr>
				</table>
				<p>
					&copy; Copyright  by Arvid Lunnemark
				</p>
			</footer>
		</div>
		</div>
		
		<!-- Iframe checking for updates on the database. Frequency is 30 seconds. (Only if user is logged in and some users are at the some pos)-->
		<?php if ($_SESSION['authenticated'] && count(returnAnySamePos()) > 0) { ?>
			<iframe src="iframe.php?frequency=5"></iframe>
		<?php } elseif ($_SESSION['authenticated']) { ?>
			<iframe src="iframe.php?frequency=45"></iframe>
		<?php } ?>
		
	</body>
</html>