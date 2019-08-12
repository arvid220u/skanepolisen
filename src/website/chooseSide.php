<?php include 'functions.php';
$username = $_SESSION['username'];
checkLogout();
checkGamePrepared("before");

$secondTime = 0;
$error = "";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$secondTime = $_GET['secondTime'];
}
if ($secondTime != 1) {
	checkChosen("before");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$isGottmos = mysqli_real_escape_string($con, $_POST['gottmos']);
	if ($isGottmos == "gottmos") {
		$gottmosers = checkUsers("gottmos");
		if ($gottmosers < 2) {
			mysqli_query_trace($con, "UPDATE ActiveUsers SET Gottmos='1' WHERE Username='".$username."'");
			redirect("lobby.php");
			die();
		} else {
			$error = "Det finns redan 2 Mr. Gött Mos. Välj Fubbickarna istället.";
		}
	} elseif ($isGottmos == "notGottmos") {
		$fubbickar = checkUsers("fubbick");
		$numPlayers = mysqli_num_rows(mysqli_query_trace($con, "SELECT * FROM ActiveUsers"));
		$maxFubbicks = 4;
		if ($numPlayers < 6) {
			$maxFubbicks = 3;
		}
		if ($fubbickar < $maxFubbicks) {
			mysqli_query_trace($con, "UPDATE ActiveUsers SET Gottmos='0' WHERE Username='".$username."'");
			redirect("lobby.php");
			die();
		} else {
			$error = "Det finns redan max antal Fubbickar. Vänta på att fler spelare ska logga in, eller välj Mr. Gött Mos istället.";
		}
	} else {
		$error = "Du måste välja sida.";
	}
}
?>
<!DOCTYPE html>
<html lang="sv">
	<head>
		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Välj sida</title>
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
		
		<div class="whiteBackground" id="nomargin">
		<div class="indented">
			<header>
				<h1>Välj sida</h1>
			</header>
			Vill du spela som Mr. Gött Mos eller som en Fubbick?<br><br>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
				
				<table class="radioTable">
					<tr>
						<td class="radioButton">
							<input type="radio" name="gottmos" value="gottmos"<?php 
							if ($secondTime > 0) {
								$isGottMos = returnFieldForUser("ActiveUsers", 5, $username);
								if ($isGottMos > 0) {
									echo ' checked';
								}
							} ?>>
						</td>
						<td class="radioLabel">Mr. Gött Mos</td>
					</tr>
					<tr>
						<td class="radioButton">
							<input type="radio" name="gottmos" value="notGottmos"<?php 
							if ($secondTime > 0) {
								$isGottMos = returnFieldForUser("ActiveUsers", 5, $username);
								if ($isGottMos == 0 && $isGottMos != NULL) {
									echo ' checked';
								}
							}?>>
						</td>
						<td class="radioLabel">Fubbick</td>
					</tr>
				</table>
				<?php if ($error != "") { ?>
					<p class="error" style="margin-top:0.7em;"><?php echo $error; ?></p>
				<?php } ?>
				<input type="submit" class="button" value="Välj">
			</form>
		</div>
			<footer>
				<table class="fullwidth">
					<tr>
						<td class="left3cell"><a href="/signout.php">Logga ut</a></td>
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
<?php 
if ($secondTime > 0) {
	mysqli_query_trace($con, "UPDATE ActiveUsers SET Gottmos=NULL WHERE Username='".$username."'");
}
mysqli_close($con); ?>