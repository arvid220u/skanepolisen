<?php include 'functions.php';
checkLogin();
?>
<!DOCTYPE html>
<html lang="sv">
	<head>
		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Bekräfta ditt konto</title>
		<meta name="description" content="">
		<meta name="author" content="Arvid Lunnemark">

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
			<header>
				<h1>Bekräfta din e-postadress</h1>
			</header>
			
			<p>För att kunna börja spela Skånepolisen, så måste du bekräfta din e-postadress genom att klicka på länken i mejlet.</p>
			</div>
			<footer>
				<table class="fullwidth">
					<tr>
						<td class="left3cell"><a href="/index.php">Hem</a></td>
						<td class="mid3cell"><a href="/skanepolisenkarta.php">Karta</a></td>
						<td class="right3cell"><a href="/rules.php">Regler</a></td>
					</tr>
				</table>
				<p>
					&copy; Copyright  by Arvid Lunnemark
				</p>
			</footer>
		</div>
		</div>
	</body>
</html>