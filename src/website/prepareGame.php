<?php include 'functions.php';
$username = $_SESSION['username'];
checkLogout();
checkGamePrepared("after");
checkIsThere("before", $username);
checkGameEnded("before");

$script = "";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$fromWait = $_GET['fromWait'];
	if ($fromWait > 0) {
		$script = "setTimeout(function () {
        				window.location = 'http://skanepolisen.org/prepareGame.php';
    				}, ".$fromWait.");";
	}
}

?>
<!DOCTYPE html>
<html lang="sv">
	<head>
		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Förbered spelet</title>
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
				<h1>Ta dig till rätt position</h1>
			</header>
			
			<h3>Du ska ta dig till hållplats nr <b><?php 
			$position = returnFieldForUser("ActiveUsers", 4, $username);
			echo "$position";
			?></b>.</h3>
			
			<form action="/waitToGame.php" method="post">
				<input type="text" name="username" value="<?php echo $username; ?>" hidden>
				<input type="submit" class="button" value="Jag är framme!">
			</form>
			</div>
			<table id="loggedin">
				<tr>
					<td class="topcell leftcell">Spelare (roll)</td>
					<td class="topcell rightcell">Position</td>
				</tr>
				<?php 
				$usernames = getUsernamesList($con, "ActiveUsers");
				foreach ($usernames as $key=>$name) {
					$isGottmos = returnFieldForUser("ActiveUsers", 5, $name);
					$userIsGottmos = returnFieldForUser("ActiveUsers", 5, $username);
					$position = returnFieldForUser("ActiveUsers", 4, $name);
					if ($isGottmos == 1 && $userIsGottmos == 0) {
						$position = "Hemligt";
					}
					if ($isGottmos > 0) {
						$name .= " (Mr. GM)";
					} elseif ($isGottmos == NULL) {
						// Do nothing
					} else {
						$name .= " (Fubbick)";
					}
					if ($name != $username) {
						echo '<tr><td class="leftcell">'.$name.'</td><td class="rightcell">'.$position.'</td></tr>';
					}
				}
				
				?>
			</table>
			<footer>
				<table class="fullwidth">
					<tr>
						<td class="left3cell"><a href="/giveUp.php" id="giveup">Ge upp</a></td>
						<td class="mid3cell"><a href="/skanepolisenkarta.php">Karta</a></td>
						<td class="right3cell"><a href="/rules.php">Regler</a></td>
					</tr>
				</table>
				<p>
					&copy; Copyright  by Arvid Lunnemark
				</p>
			</footer>
			
			<?php
				// Change confirm-text based on if the game will end if this user will be out
				$confirmText = "men spelet kommer att fortsätta.";
				
				$gmOut = TRUE;
				$fubbOut = TRUE;
				
				$usernames = getAllUsernamesList($con, "ActiveUsers");
				foreach ($usernames as $name) {
					if ($name != $username) {
						$gottmosName = returnFieldForUser("ActiveUsers", 5, $name);
						$gottmosUsername = returnFieldForUser("ActiveUsers", 5, $username);
						$isOut = returnFieldForUser("ActiveUsers", 10, $name);
						if ($gottmosName > 0) {
							// is gottmos
							if ($isOut == 0) {
								$gmOut = FALSE;
							}
						} elseif ($gottmosName == 0) {
							if ($isOut == 0) {
								$fubbOut = FALSE;
							}
						}
					}
				}
				
				if ($gmOut) {
					$confirmText = "och spelet kommer att avslutas med Fubbickarna som vinnare.";
				} elseif ($fubbOut) {
					$confirmText = "och spelet kommer att avslutas med Mr. Gött Mos som vinnare.";
				}
			?>
			
			<script type="text/javascript">
				
				// Wait for the page to load first
        		window.onload = function() {
				
        			//Get a reference to the link on the page
        			// with an id of "mylink"
        			var a = document.getElementById("giveup");
	
	        		//Set code to run when the link is clicked
	        		// by assigning a function to "onclick"
	        		a.onclick = function() {
						
						if (confirm("Vill du verkligen ge upp?\nDu kommer att åka ut, <?php echo $confirmText; ?>") == true) {
							window.location = "giveUp.php";
						} else {
							// Do nothing
						}
	            		
	           			return false;
	          		}
		        }
    		</script>
    		
    		<script><?php echo $script; ?></script>
    		
    	</div>
    	</div>
    	
    	<!-- Iframe checking for updates on the database. Frequency is 60 seconds. -->
		
		
		<iframe src="iframe.php?frequency=30"></iframe>
    	
	</body>
</html>
<?php mysqli_close($con); ?>