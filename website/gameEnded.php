<?php 
// Start or resume session
session_start();
$cookieLifetime = 5 * 60; // 5 minutes
setcookie(session_name(),session_id(),time()+$cookieLifetime);
$username = $_SESSION['username'];

if ($_SESSION['endDone'] != "set") {
	include 'functions.php';
	$_SESSION['cssLink'] = $cssLink;
	$username = $_SESSION['username'];
	checkLogout();
	checkGameEnded("after");
	
	$winner = returnGameField(7);
	$isWinner = FALSE;
	$isGottmos = returnFieldForUser("ActiveUsers", 5, $username);
	
	if ($winner == $isGottmos) {
		$isWinner = TRUE;
	}
	
	// Only update wins once
	if (!isset($_SESSION['endUpdatedWins'])) {
		
		if ($isWinner) {
			// Update number of wins in registered users table
			$oldWins = returnFieldForUser("RegisteredUsers", 5, $username);
			$newWins = $oldWins + 1;
			mysqli_query_trace($con, "UPDATE RegisteredUsers SET Wins='".$newWins."' WHERE Username='".$username."'");
			mysqli_query_trace($con, "UPDATE ActiveUsers SET EndFinished='1' WHERE Username='".$username."'");
		} else {
			mysqli_query_trace($con, "UPDATE ActiveUsers SET EndFinished='1' WHERE Username='".$username."'");
		}
		
		$_SESSION['endUpdatedWins'] = TRUE;
	}
	
	// Build an information base, to write the forthcoming text
	$isOutList = array();
	$gottmosWonAfter21 = FALSE;
	$gottmosNames = array();
	$fubbickNames = array();
	$otherGottmosUser = "";
	
	$usernames = getAllUsernamesList($con, "ActiveUsers");
	foreach ($usernames as $name) {
		$isOutN = returnFieldForUser("ActiveUsers", 10, $name);
		$isOutList[$name] = $isOutN;
		if (returnFieldForUser("ActiveUsers", 5, $name) > 0) {
			array_push($gottmosNames, $name);
			if ($isGottmos > 0 && $username != $name) {
				$otherGottmosUser = $name;
			}
		} else {
			array_push($fubbickNames, $name);
			if ($isOutN == 0) {
				$gottmosWonAfter21 = TRUE;
			}
		}
	}
	
	// Check and explain why the game is ended
						
	$explainer = "";
	
	if ($isWinner) {
		$explainer .= "Bra jobbat!";
		if ($isGottmos > 0) {
			// Gottmos and winner
			if ($gottmosWonAfter21) {
				$explainer .= " Du och ".$otherGottmosUser." klarade er undan Fubbickarna i 21 omgångar.";
			} else {
				// All the fubbicks are out, either gave up or was out of cards
				$explainer .= " Alla Fubbickar är ute ur spelet, och därför vann du och ".$otherGottmosUser.".";
				$explainer .= "<br>Såhär åkte alla Fubbickar ut:";
				$explainer .= "<ul>";
				foreach ($fubbickNames as $fName) {
					$explainer .= "<li>";
					$reason = $isOutList[$fName];
					$explainer .= "<b>".$fName."</b>: "; 
					if ($reason == 1) {
						// This user was out of cards
						$explainer .= "slut på nödvändiga biljetter.";
					} elseif ($reason == 2) {
						// This user gave up
						$explainer .= "gav upp.";
					}
					$explainer .= "</li>";
				}
				$explainer .= "</ul>";
			}
			 
		} elseif ($isGottmos == 0) {
			// Fubbick and winner
			// Means that both gottmosers were out
			$bothWereTaken = TRUE;
			$bothGaveUp = TRUE;
			$bothWereOutOfCards = TRUE;
			
			$gmReasons = array();
			
			foreach ($gottmosNames as $gmName) {
				$reason = $isOutList[$gmName];
				if ($reason != 3) {
					$bothWereTaken = FALSE;
				}
				if ($reason != 2) {
					$bothGaveUp = FALSE;
				}
				if ($reason != 1) {
					$bothWereOutOfCards = FALSE;
				}
				if ($reason == 3) {
					$gmReasons[$gmName] = "blev tagen";
				}
				if ($reason == 2) {
					$gmReasons[$gmName] = "gav upp";
				}
				if ($reason == 1) {
					$gmReasons[$gmName] = "hade slut på nödvändiga biljetter";
				}
			}
			
			if ($bothWereTaken) {
				// Most plausible situation
				$explainer .= " Ni tog båda Mr. Gött Mos, och vann på grund av det.";
			} elseif ($bothGaveUp) {
				$explainer .= " Båda Mr. Gött Mos gav upp, så därför vann ni.";
			} elseif ($bothWereOutOfCards) {
				$explainer .= " Ni frös ut Mr. Gött Mos på rätt sorts biljetter, skickligt gjort!";
			} else {
				$explainer .= " Du och resten av Fubbickarna vann på grund av 
				att ".$gottmosNames[0]." ".$gmReasons[$gottmosNames[0]]." och att ".$gottmosNames[1]." ".$gmReasons[$gottmosNames[1]].".";
			}
			
		}
	} else {
		// Is loser
		$explainer .= "Dåligt jobbat!";
		if ($isGottmos > 0) {
			// The gottmosers lost, too bad for them
			$bothWereTaken = TRUE;
			$bothGaveUp = TRUE;
			$bothWereOutOfCards = TRUE;
			
			$gmReasons = array();
			
			foreach ($gottmosNames as $gmName) {
				$reason = $isOutList[$gmName];
				if ($reason != 3) {
					$bothWereTaken = FALSE;
				}
				if ($reason != 2) {
					$bothGaveUp = FALSE;
				}
				if ($reason != 1) {
					$bothWereOutOfCards = FALSE;
				}
				if ($reason == 3) {
					$gmReasons[$gmName] = "blev tagen";
				}
				if ($reason == 2) {
					$gmReasons[$gmName] = "gav upp";
				}
				if ($reason == 1) {
					$gmReasons[$gmName] = "hade slut på nodvändiga biljetter";
				}
			}
			
			if ($bothWereTaken) {
				// Most plausible situation
				$explainer .= " Du och ".$otherGottmosUser." blev båda tagna av Fubbickarna, och förlorade av den anledningen.";
			} elseif ($bothGaveUp) {
				$explainer .= " Du och ".$otherGottmosUser." gav båda upp. Fegisar!";
			} elseif ($bothWereOutOfCards) {
				$explainer .= " Fubbickarna frös ut dig och ".$otherGottmosUser." på biljetter. Försök tänka strategiskt nästa gång 
				(om ni nu kan det).";
			} else {
				$explainer .= " Du och ".$otherGottmosUser." förlorade på grund av
				att du ".$gmReasons[$username]." och att ".$otherGottmosUser." ".$gmReasons[$otherGottmosUser].".";
			}
		} elseif ($isGottmos == 0) {
			// Fubbicks lost
			if ($gottmosWonAfter21) {
				$explainer .= " Ni lyckades inte ta båda Mr. Gött Mos inom de 21 omgångarna.";
			} else {
				// All the fubbicks are out, either gave up or was out of cards
				$explainer .= " Alla ni Fubbickar åkte ut ur spelet, och därför förlorade ni.";
				$explainer .= "<br>Såhär åkte alla Fubbickar ut:";
				$explainer .= "<ul>";
				foreach ($fubbickNames as $fName) {
					$explainer .= "<li>";
					$reason = $isOutList[$fName];
					if ($fName != $username) {
						$explainer .= "<b>".$fName."</b>: ";
					} else {
						$explainer .= "<b>Du</b>: ";
					}
					if ($reason == 1) {
						// This user was out of cards
						$explainer .= "slut på nödvändiga biljetter.";
					} elseif ($reason == 2) {
						// This user gave up
						$explainer .= "gav upp.";
					}
					$explainer .= "</li>";
				}
				$explainer .= "</ul>";
			}
		}
	}
	
	// Show descriptive heading
	$heading = "";
	if ($winner == 0) {
		if ($isGottmos == 0) {
			$heading = "Grattis!!! Du och resten av fubbickarna vann!";
		} else {
			$heading = "Du förlorade!!! Fubbickarna vann!";
		}
	} elseif ($winner > 0) {
		if ($isGottmos > 0) {
			$heading = "Grattis!!! Du och din medspelare Mr. Gött Mos vann!";
		} else {
			$heading = "Du förlorade!!! Mr. Gött Mos vann!";
		}
	}
	
	// Make leaderboard table array (minified for better storage)
	$leaderboardData = array();
	$usernames = getAllUsernamesList($con, "RegisteredUsers");
	foreach ($usernames as $name) {
		$wins = returnFieldForUser("RegisteredUsers", 5, $name);
		$leaderboardData[$name] = $wins;
	}
	
	$_SESSION['endExplainer'] = $explainer;
	$_SESSION['endHeading'] = $heading;
	$_SESSION['endLeaderboardData'] = $leaderboardData;
	
	
	// Check if all have updated their status
	$allHaveUpdatedWins = TRUE;
	$usernames = getAllUsernamesList($con, "ActiveUsers");
	foreach ($usernames as $name) {
		if (returnFieldForUser("ActiveUsers", 16, $name) == 0) {
			$allHaveUpdatedWins = FALSE;
		}
	}
	// If all have updated wins, then update endfinished to 2 and stop updating the leaderboard
	if ($allHaveUpdatedWins) {
		mysqli_query_trace($con, "UPDATE ActiveUsers SET EndFinished='2' WHERE Username='".$username."'");
		$_SESSION['endDone'] = "set";
		
		
		
		// Send mail, now that the leaderboard is great
		$email = returnFieldForUser("RegisteredUsers", 2, $username);
		
		$heading = "";
		if ($isWinner) {
			$heading = "Grattis till vinsten, ".$username."!";
		} else {
			$heading = "Beklagar sorgen, ".$username;
		}
		
		$body = '<html lang="sv"><head><title>Spelet är slut</title></head><body style="width: 100%; height: 100%; margin: 0; padding: 0; font-family: "American Typewriter", Georgia; font-weight: light; font-size: 12pt; padding-top: 12px;">';
		$body .= '<div style="padding: 0px 12px; margin: 0px; margin-top: 12px;">';
		$body .= $explainer;
		
		// Display highscore table
		$namesAndWins = $leaderboardData;
		$sortedNames = array();
		foreach ($namesAndWins as $name=>$wins) {
			$index = 0;
			foreach ($sortedNames as $sortedName) {
				$sortedWins = $namesAndWins[$sortedName];
				if ($sortedWins >= $wins) {
					$index++;
				}
			}
			array_splice($sortedNames, $index, 0, $name);
		}
		
		$plural = "er";
		if ($namesAndWins[$username] == 1) {
			$plural = "";
		}
		
		$body .= "<p>Du har än så länge <b>".$namesAndWins[$username]."</b> vinst".$plural.". Såhär ser highscore-tabellen ut:</p>";
		
		$body .= '</div><table style="width: 100%; background-color: black; color: white;">
				  <tr><td style="font-weight: bold; text-align: left; width:58%; padding-left: 12px;">Spelare (roll)</td><td style="font-weight: bold; text-align: right; width:42%; padding-right: 12px;">Antal vinster</td></tr>';
		foreach ($sortedNames as $name) {
			$wins = $namesAndWins[$name];
			$body .= '<tr><td style="text-align: left; width:58%; padding-left: 12px;">'.$name.'</td><td style="text-align: right; width:42%; padding-right: 12px;">'.$wins.'</td></tr>';
		}
		$body .= '</table><div style="background-color: rgba(0, 0, 0, 0.1); margin: 0px; padding: 10px 5px;">
				  <p style="margin: 0px; padding: 0px; text-align: center;">&copy; Copyright  by Arvid Lunnemark</p></div>
				  </div></body></html>';
		
		// $headers = "From: Skånepolisen <info@skanepolisen.org>" . "\r\n";
		// $headers .= 'MIME-Version: 1.0' . "\r\n";
		// $headers .= 'Content-type: text/html; charset="UTF-8"' . "\r\n";
		
		// mail($email, $heading, $body, $headers);
		
		$email_to = $email;

		$email = new \SendGrid\Mail\Mail(); 
		$email->setFrom($skanepolisen_email, "Skånepolisen");
		$email->setSubject($heading);
		$email->addTo($email_to, "$username");
		$email->addContent("text/html", $body);
		$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
		try {
			$response = $sendgrid->send($email);
		} catch (Exception $e) {
			echo 'Caught exception: '. $e->getMessage() ."\n";
		}
		
		
		
		// Check if all have updated leaderboard
		$allHaveUpdatedLeaderboard = TRUE;
		$usernames = getAllUsernamesList($con, "ActiveUsers");
		foreach ($usernames as $name) {
			if (returnFieldForUser("ActiveUsers", 16, $name) != 2) {
				$allHaveUpdatedLeaderboard = FALSE;
			}
		}
		if ($allHaveUpdatedLeaderboard) {
			mysqli_query($con, "DELETE FROM ActiveGame");
			mysqli_query($con, "DELETE FROM ActiveUsers");
		}
	}
}


?>
<!DOCTYPE html>
<html lang="sv">
	<head>
		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Spelet är slut</title>
		<meta name="description" content="">
		<meta name="author" content="Arvid Lunnemark">

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="mobile-web-app-capable" content="yes">
		
		<link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['cssLink']; ?>" />
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
						<h1>
						<?php
							echo $_SESSION['endHeading'];
						?>
						</h1>
					</header>
					
					<p>
						<?php
							echo $_SESSION['endExplainer'];
						?>
					</p>
				
					<?php
						// Highscore
						$namesAndWins = $_SESSION['endLeaderboardData'];
						$sortedNames = array();
						foreach ($namesAndWins as $name=>$wins) {
							$index = 0;
							foreach ($sortedNames as $sortedName) {
								$sortedWins = $namesAndWins[$sortedName];
								if ($sortedWins >= $wins) {
									$index++;
								}
							}
							array_splice($sortedNames, $index, 0, $name);
						}
					?>
					
					
					<p>
						Du har än så länge <b><?php echo $namesAndWins[$username]; ?></b> vinst<?php if ($namesAndWins[$username] != 1) { echo "er"; } ?>.
						Såhär ser highscore-tabellen ut:
					</p>
				
				</div>
				
				<table id="loggedin">
					<tr>
						<td class="topcell leftcell">Spelare (roll)</td>
						<td class="topcell rightcell">Antal vinster</td>
					</tr>
					<?php
						// Array is sorted, now display table
						foreach ($sortedNames as $name) {
							$wins = $namesAndWins[$name];
							echo '<tr><td class="leftcell">'.$name.'</td><td class="rightcell">'.$wins.'</td></tr>';
						}
					?>
				</table>
			
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
		
		<?php if (!isset($_SESSION['endDone'])) { ?>
			
			<!-- Iframe checking for updates on the database. Frequency is 8 seconds. -->
			
			
			<iframe src="iframe.php?frequency=8"></iframe>
		
		<?php } ?>	
		
	</body>
</html>
<?php mysqli_close($con); ?>