<?php include 'functions.php';
$username = $_SESSION['username'];
checkLogout();
checkGamePrepared("after");
checkIsOut("after", $username);
checkAllAreOut();
checkGameEnded("before");

$outOfCards = FALSE;
$gaveUp = FALSE;
$taken = FALSE;

// The isOut property can be set to 0, 1, 2 and 3 – each which corresponds to:
// 0 = not out
// 1 = out, no cards
// 2 = out, gave up
// 3 = out, taken

$isOut = returnFieldForUser("ActiveUsers", 10, $username);

if ($isOut == 1) {
	$outOfCards = TRUE;
} elseif ($isOut == 2) {
	$gaveUp = TRUE;
} elseif ($isOut == 3) {
	$taken = TRUE;
}


?>
<!DOCTYPE html>
<html lang="sv">
	<head>
		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Du är ute!</title>
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
				<h1>Du är ute ur spelet!</h1>
			</header>
			
			<h3><?php if ($outOfCards) {
				echo "Du kunde inte flytta till någon position på grund av brist på rätt sorts biljetter.";
			} elseif ($gaveUp) {
				echo "Du gav upp, och är nu ute.";
			} elseif ($taken) {
				echo "Du blev tagen av en fubbick under jakten.";
			}?></h3>
			
			<p>Ring en kompis som är i samma lag som du. Är du en fubbick, 
			så kan du fortfarande vara med i jakten som uppstår om någon annan fubbick hamnar på samma position som Mr. Gött Mos. 
			Är du däremot Mr. Gött Mos, så får du helt enkelt bara följa med din medspelare.</p>
			
			</div>
			
			<footer>
				<table class="fullwidth">
					<tr>
						<td class="leftcell"><a href="/skanepolisenkarta.php">Karta</a></td>
						<td class="rightcell"><a href="/rules.php">Regler</a></td>
					</tr>
				</table>
				<p>
					&copy; Copyright  by Arvid Lunnemark
				</p>
			</footer>
		</div>
		</div>
		
		<!-- Iframe checking for updates on the database. Frequency is 60 seconds. -->
			
		<iframe src="iframe.php?frequency=60"></iframe>
		
	</body>
</html>
<?php mysqli_close($con); ?>