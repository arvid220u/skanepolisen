<?php include 'functions.php';
checkLogin();

$error = "";
$testUsername = $testPassword = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$testUsername = mysqli_real_escape_string($con, $_POST['testUsername']);
	$testPassword = mysqli_real_escape_string($con, hashAndSalt($_POST['testPassword']));
	
	$usernameSearch = mysqli_query_trace($con, "SELECT Username, Password FROM RegisteredUsers WHERE Username='".$testUsername."' AND Password='".$testPassword."'");
	$match = mysqli_num_rows($usernameSearch);
	if ($match > 0) {
		// Correct username and password
		$activeSearch = mysqli_query_trace($con, "SELECT Active FROM RegisteredUsers WHERE Username='".$testUsername."' AND Password='".$testPassword."' AND Active='1'");
		$match = mysqli_num_rows($activeSearch);
		if ($match > 0) {
			// Account is active
			if (getLengthOfTable("ActiveGame") == 0) {
				// Account is active and there is no game already started, now sign in
				session_destroy();
				session_start();
				session_regenerate_id(true);
				$_SESSION['authenticated'] = TRUE;
				$_SESSION['username'] = $testUsername;
				addToActiveUsers($con, $testUsername);
				redirect("chooseSide.php");
				die();
			} else {
				// Check if this user is in ActiveUsers. If not, don't sign in
				$usernames = getAllUsernamesList($con, "ActiveUsers");
				$isInActiveUsers = FALSE;
				foreach ($usernames as $name) {
					if ($name == $testUsername) {
						$isInActiveUsers = TRUE;
					}
				}
				if ($isInActiveUsers) {
					// This player is already playing, now sign in
					session_destroy();
					session_start();
					$_SESSION['authenticated'] = TRUE;
					$_SESSION['username'] = $testUsername;
					addToActiveUsers($con, $testUsername);
					redirect("chooseSide.php");
					die();
				} else {
					// There is an active game without this user. Make him wait
					$error = "Ett spel pågår just nu. Därför kan du inte logga in.";
				}
			}
		} else {
			// Account isn't active
			$error = "Kontot är inte aktiverat än. Klicka på länken i bekräftelse-mailet först.";
		}
	} else {
		// Wrong username or password
		$error = "Felaktigt användarnamn eller lösenord.";
	}
}

?>
<!DOCTYPE html>
<html lang="sv">
	<head>
		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Logga in</title>
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
				<h1>Logga in</h1>
			</header>
			
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
				Användarnamn: <input type="text" name="testUsername" value="<?php echo $testUsername;?>"><br><br>
				Lösenord: <input type="password" name="testPassword"><?php if ($error != "") { echo "<br><br>"; } ?>
				<span class="error"><?php echo $error;?></span>
				<input type="submit" class="button" value="Logga in">
			</form>
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
<?php mysqli_close($con); ?>