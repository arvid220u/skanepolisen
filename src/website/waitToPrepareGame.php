<?php include 'functions.php';
$username = $_SESSION['username'];
checkLogout();
checkChosen("after");
checkGamePrepared("before", $username);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if ($username != $_POST['username']) {
		redirect("lobby.php");
		die();
	}
	mysqli_query_trace($con, "UPDATE ActiveUsers SET PreparedForStart='1' WHERE Username='".$username."'");
}
// Check if user has entered URL directly, or actually tapped the button where there is a player limit. ESSENTIAL
checkUserPrepared("after", $username);

$usernames = getUsernamesList($con, "ActiveUsers");
$allHaveStarted = TRUE;
foreach ($usernames as $name) {
	if (returnFieldForUser("ActiveUsers", 7, $name) == 0) {
		$allHaveStarted = FALSE;
	}
}

if ($allHaveStarted) {
	$position = returnFieldForUser("ActiveUsers", 4, $username);
	if ($position == NULL) {
		setPositionAndCards($username);
	}
	if (getLengthOfTable("ActiveGame") == 0) {
		mysqli_query_trace($con, "INSERT INTO ActiveGame () VALUES()");
		redirect("prepareGame.php?fromWait=8000");
		die();
	}
	redirect("prepareGame.php?fromWait=5000");
	die();
}

?>
<!DOCTYPE html>
<html lang="sv">
	<head>
		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Väntar...</title>
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
				<h1>Väntar på att alla spelare ska starta...</h1>
			</header>
		</div>
			<footer>
				<table class="fullwidth">
					<tr>
						<td><a href="/lobby.php?second=1">< Gå tillbaka</a></td>
						<td><a href="/signout.php">Logga ut</a></td>
						<td><a href="/skanepolisenkarta.php">Karta</a></td>
						<td><a href="/rules.php">Regler</a></td>
					</tr>
				</table>
				<p>
					&copy; Copyright  by Arvid Lunnemark
				</p>
			</footer>
		</div>
		</div>
		
		<!-- Iframe checking for updates on the database. Frequency is 6 seconds. -->
		
		<iframe src="iframe.php?frequency=6"></iframe>
		
	</body>
</html>
<?php mysqli_close($con); ?>