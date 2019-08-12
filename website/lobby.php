<?php include 'functions.php';
checkLogout();
checkChosen("after");
checkGamePrepared("before");

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$second = $_GET['second'];
	if ($second > 0) {
		mysqli_query_trace($con, "UPDATE ActiveUsers SET PreparedForStart='0' WHERE Username='".$username."'");
	}
}

checkUserPrepared("before", $username);

?>
<!DOCTYPE html>
<html lang="sv">
	<head>
		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Lobby</title>
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
				<h1>Lobby</h1>
			</header>
			Inloggade spelare:<br><br>
		</div>
			<table id="loggedin">
				<tr>
					<td class="topcell leftcell">Spelare</td>
					<td class="topcell rightcell">Roll</td>
				</tr>
				<?php 
				$usernames = getAllUsernamesList($con, "ActiveUsers");
				foreach ($usernames as $key=>$name) {
					$isGottmos = returnFieldForUser("ActiveUsers", 5, $name);
					$sida = "";
					if ($isGottmos > 0) {
						$sida = "Mr. Gött Mos";
					} elseif ($isGottmos == NULL) {
						$sida = "Ambivalent";
					} else {
						$sida = "Fubbick";
					}
					if ($name == $username) {
						$name = "Du ($username)";
					}
					
					echo '<tr><td class="leftcell">'.$name.'</td><td class="rightcell">'.$sida.'</td></tr>';
				}
				
				?>
			</table>
		<div class="indented">
			<form action="/waitToPrepareGame.php" method="post">
				<input type="text" name="username" value="<?php echo $username; ?>" hidden>
				<input type="submit" class="button" value="Starta spelet"<?php
				$numUsers = getLengthOfTable("ActiveUsers");
				$numError = "";
				$usernames = getAllUsernamesList($con, "ActiveUsers");
				$shouldEcho = FALSE;
				foreach ($usernames as $key=>$namex) {
					$isGottmos = returnFieldForUser("ActiveUsers", 5, $namex);
					if ($isGottmos == NULL) {
						$shouldEcho = TRUE;
						$numError = "Alla spelare måste ha valt sida först.";
					}
				}
				if ($numUsers < 5) {
					$shouldEcho = TRUE;
					$numError = "Minst 5 spelare.";
				}
				if ($shouldEcho) {
					echo ' disabled';
				}
				?>> <?php if ($numError != "") { ?><span class="error center"><?php echo $numError; ?></span><br><br><?php } ?>
			</form>
			
			</div>
			<footer>
				<table class="fullwidth">
					<tr>
						<td><a href="/chooseSide.php?secondTime=1">< Gå tillbaka</a></td>
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
		
		<!-- Iframe checking for updates on the database. Frequency is 8 seconds. -->
		
		<iframe src="iframe.php?frequency=8"></iframe>
			
	</body>
</html>
<?php mysqli_close($con); ?>
