<!DOCTYPE html>
<html lang="sv" manifest="caching.appcache">
	<head>
		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Skånepolisen karta</title>
		<meta name="description" content="Karta över Malmö med skånepolisens specifika hållplatser och linjer utritade.">
		<meta name="author" content="Arvid Lunnemark">

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=4.0, user-scalable=1">
		
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="mobile-web-app-capable" content="yes">
		
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
		
		<style>
			body {
				padding: 0;
				margin: 0;
				font-family: "American Typewriter", Georgia;
				font-weight: 400;
				font-size: 12pt;
			}
			
			a {
				text-decoration: none;
				color: /*rgba(255, 30, 10, 0.9);*/rgba(22, 160, 133,1.0);
			}
			a:active {
				color: rgba(231, 76, 60,1.0);
			}
			
			#map {
				width: 100%;
				padding: 0;
				margin: 0;
			}
			
			footer {
				background-color: rgba(0, 0, 0, 0.1);
				margin: 0px;
				padding: 10px 0;
				width: 100%;
			}
			
			footer p {
				margin: 0px;
				padding: 0px;
				text-align: center;
			}
			
			footer table {
				padding: 0px;
				margin: 0px;
				margin-bottom: 4px;
				width: 100%;
			}
			
			footer td {
				text-align: center;
			}
			
			footer td.leftcell {
				text-align: left;
				width: 50%;
				padding-left: 12px;
			}
			footer td.rightcell {
				text-align: right;
				width: 50%;
				padding-right: 12px;
			}
		</style>
	</head>
	
	<body>
		
		<img src="skanepolisen-karta2.png" id="map">
		
		<footer>
			<table class="fullwidth">
				<tr>
					<?php
						$goBack = $_SERVER['HTTP_REFERER'];
						if ($goBack == "http://skanepolisen.org/skanepolisenkarta.php" || $goBack == "http://skanepolisen.org/rules.php") {
							$goBack = "http://skanepolisen.org/index.php";
						} elseif (strpos($goBack, 'http://skanepolisen.org') === false) {
							$goBack = "http://skanepolisen.org/index.php";
						} elseif (strpos($goBack, 'iframe.php') !== false) {
							$goBack = "http://skanepolisen.org/index.php";
						}
					?>
					<td class="leftcell"><a href="<?php echo $goBack; ?>">< Gå tillbaka</a></td>
					<td class="rightcell"><a href="/rules.php">Regler</a></td>
				</tr>
			</table>
			<p>
				&copy; Copyright  by Arvid Lunnemark
			</p>
		</footer>
		
	</body>
</html>