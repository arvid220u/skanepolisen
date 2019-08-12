<?php include 'functions.php';
$username = $_SESSION['username'];
checkLogout();
checkGamePrepared("after");
checkIsThere("after", $username);
checkGameStarted("after", $username);
checkIsOut("before", $username);
checkAllAreOut();
checkGameEnded("before");
checkDynamicIsBack("before", $username);

$frequency = 20;

if (isset($_GET['samepos'])) {
	if ($_GET['samepos'] == "true") {
		checkSamePos("after", $username);
		
	} else {
		checkSamePos("before", $username);
		checkDynamicMode("after");
	} 
} else {
	checkSamePos("before", $username);
	checkDynamicMode("after");
}

$isGottmos = returnFieldForUser("ActiveUsers", 5, $username);
$samePosArray = returnAnySamePos();
$samePosNames = array_keys($samePosArray);

$showUserList = FALSE;

$waitToDynamicMode = returnFieldForUser("ActiveUsers", 11, $username);
$dynamicMode = returnGameField(3);
$dynamicEnd = returnGameField(9);
$dynamicPause = returnGameField(6);

if (isset($_POST['username'])) {
	if ($_POST['username'] != $username) {
		redirect($_SERVER["REQUEST_URI"]);
		die();
	}
	
	$specifier = $_POST['specifier'];
	
	if ($specifier == "startDynamicMode") {
	
		mysqli_query_trace($con, "UPDATE ActiveUsers SET PreparedForDynamicMode='1' WHERE Username='".$username."'");
		
		// check if both are PreparedForDynamicMode, then updates dynamicMode of ActiveGame
		if (returnFieldForUser("ActiveUsers", 11, $samePosNames[0]) > 0 && returnFieldForUser("ActiveUsers", 11, $samePosNames[1]) > 0) {
			mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicMode='1' WHERE DynamicMode='0'");
		}

	} elseif ($specifier == "dynamicEnd") {
		// Timer has just stopped, set the dynamicend property to 1
		if ($dynamicPause == 0) {
			mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicEnd='1' WHERE DynamicEnd='0'");
		}
		
	} elseif ($specifier == "reportBusted") {
		// Someone has reported that someone is taken, set $showUserList to TRUE
		$showUserList = TRUE;
		
	} elseif ($specifier == "specifyBusted") {
		if ($_POST['navigateButton'] == "< Gå tillbaka") {
			// User has clicked busted-button, but has then regretted it
			redirect($_SEVER['REQUEST_URI']);
			die();
		}
		// User has reported another user as taken / taking (not so likely, however)
		// The timer should pause, and a dialog should show up on the affected user (by updating that user's IsBusted property in the ActiveUsers table)
		// All other users should remain with the paused timer, and no else information
		if ($dynamicPause == 0) {
			mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicPause='1' WHERE DynamicPause='0'");
			$affectedUser = $_POST['takenOrTakingUser'];
			
			// Set IsBusted to 1 for the user being taken (gottmos), and to 2 for the user (fubbick) who took the user being taken
			if ($isGottmos == 0) {
				mysqli_query_trace($con, "UPDATE ActiveUsers SET IsBusted='2' WHERE Username='".$username."'");
				mysqli_query_trace($con, "UPDATE ActiveUsers SET IsBusted='1' WHERE Username='".$affectedUser."'");
			} else {
				mysqli_query_trace($con, "UPDATE ActiveUsers SET IsBusted='1' WHERE Username='".$username."'");
				mysqli_query_trace($con, "UPDATE ActiveUsers SET IsBusted='2' WHERE Username='".$affectedUser."'");
			}
			
			// Set the session variable "setBusted", to not show the dialog in the paused area
			$_SESSION['setBusted'] = "isSet";
			
			// Update the DynamicModeStart with how many seconds have gone
			$currentTime = intval(ntp_time());
			$timeElapsed = $currentTime - returnGameField(8);
			mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicModeStart='".$timeElapsed."'");
		}
		
	} elseif ($specifier == "confirmBusted") {
		// This user has either confirmed or denied what the otherUser has reported
		$otherUser = $_POST['otherUser'];
		$answer = $_POST['navigateButton'];
		
		if (strpos($answer, 'Ja,') !== false) {
			// One user will be sent out of play (isOut property), and the timer and whole dynamicMode will stop.
			// Every other user will be sent back to their original positions
			
			// Activate DynamicEnd (the 2 stands for 'someone is out')
			mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicEnd='2'");
			mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicPause='0'");
			
			if ($isGottmos > 0) {
				// This user will be sent out of play
				mysqli_query_trace($con, "UPDATE ActiveUsers SET IsOut='3' WHERE Username='".$username."'");
				redirect("isOut.php");
				die();
			} elseif ($isGottmos == 0) {
				// The otherUser will be sent out of play
				mysqli_query_trace($con, "UPDATE ActiveUsers SET IsOut='3' WHERE Username='".$otherUser."'");
			}
			
		} elseif (strpos($answer, 'Nej,') !== false) {
			// The two busted users don't agree, show both of them new dialogs
			// The isBusted number has three values: not answered (0), answered yes (1), and andwered no (2)
			// In the beginning, they'll be set to three, which moduled by three is 0: not answered
			// When they answer, the isBusted number will grow by either 1 or 2, and will therefore have another value
			// After the forthcoming dialog has been shown 3 times (that is, their number is 3 + 3*3 = 12), the game will be paused
			// and all users will be brought together to decide
			
			// set both isBusted numbers to 3
			$usernames = getUsernamesList($con, "ActiveUsers");
			foreach ($usernames as $name) {
				if (returnFieldForUser("ActiveUsers", 12, $name) > 0) {
					// is a busted user, not gettin on
					mysqli_query_trace($con, "UPDATE ActiveUsers SET IsBusted='3' WHERE Username='".$name."'");
				}
			}
			
		}
		
	} elseif ($specifier == "tryingToAgree") {
		// The users didn't agree at the first time, now update the isBusted property to reflect choice, and check if the other user
		// has answered
		$otherUser = $_POST['otherUser'];
		$answer = $_POST['navigateButton'];
		
		if (strpos($answer, 'Ja,') !== false) {
			// The user said yes, which corresponds to a 1 (look above, line 119)
			// Increment the isBusted value by one
			
			$isBustedOldVal = returnFieldForUser("ActiveUsers", 12, $username);
			$isBustedNewVal = $isBustedOldVal + 1;
			mysqli_query_trace($con, "UPDATE ActiveUsers SET IsBusted='".$isBustedNewVal."' WHERE Username='".$username."'");
			
		} elseif (strpos($answer, 'Nej,') !== false) {
			// The user said no, which corresponds to a 2 (look above, line 119)
			// Increment the isBusted value by two
			
			$isBustedOldVal = returnFieldForUser("ActiveUsers", 12, $username);
			$isBustedNewVal = $isBustedOldVal + 2;
			mysqli_query_trace($con, "UPDATE ActiveUsers SET IsBusted='".$isBustedNewVal."' WHERE Username='".$username."'");
			
		}
		
		// Check if the other user has chosen, if not just ignore
		if (returnFieldForUser("ActiveUsers", 12, $otherUser) % 3 > 0) {
			// The otherUser has chosen, now compare the two and perform different actions based on that (four possible)
			$thisBusted = returnFieldForUser("ActiveUsers", 12, $username);
			$otherBusted = returnFieldForUser("ActiveUsers", 12, $otherUser);
			if ($thisBusted == $otherBusted) {
				// They agree!!! Now determine if one user should go out of play, or if the timer should start again
				if ($thisBusted % 3 == 1) {
					// One user will be sent out of play (isOut property), and the timer and whole dynamicMode will stop.
					// Every other user will be sent back to their original positions
					
					// Activate DynamicEnd (the 2 stands for 'someone is out')
					mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicEnd='2'");
					mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicPause='0'");
					
					if ($isGottmos > 0) {
						// This user will be sent out of play
						mysqli_query_trace($con, "UPDATE ActiveUsers SET IsOut='3' WHERE Username='".$username."'");
						redirect("isOut.php");
						die();
					} elseif ($isGottmos == 0) {
						// The otherUser will be sent out of play
						mysqli_query_trace($con, "UPDATE ActiveUsers SET IsOut='3' WHERE Username='".$otherUser."'");
					}
					
				} elseif ($thisBusted % 3 == 2) {
					// The timer should resume, and dynamicPause should be disabled
					// IsBusted property should also be reset
					
					$timeElapsed = returnGameField(8);
					$currentTime = intval(ntp_time());
					$newStartTime = $currentTime - $timeElapsed;
					// Add 5 seconds for the dots
					$newStartTime += 5000;
					
					mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicModeStart='".$newStartTime."'");
					
					// The gottmos-label should disappear 15 seconds after currentTime
					$gmDisappear = ntp_time() + 15000;
					$gmDisappear = floor($gmDisappear / 1000);
					$gmDisappear = $gmDisappear * 1000;
					$gmDisappear = intval($gmDisappear);
					mysqli_query_trace($con, "UPDATE ActiveGame SET GottmosLabelDisappear='".$gmDisappear."'");
					
					mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicPause='0'");
					
					mysqli_query_trace($con, "UPDATE ActiveUsers SET IsBusted='0'");
				}
			} else {
				// They didn't agree, now continue to show the dialog
				$newThisBusted = $thisBusted + 3 - ($thisBusted % 3);
				$newOtherBusted = $otherBusted + 3 - ($otherBusted % 3);
				mysqli_query_trace($con, "UPDATE ActiveUsers SET IsBusted='".$newThisBusted."' WHERE Username='".$username."'");
				mysqli_query_trace($con, "UPDATE ActiveUsers SET IsBusted='".$newOtherBusted."' WHERE Username='".$otherUser."'");
			}
		}
		
	} elseif ($specifier == "askIfTaken") {
		$navigateButton = $_POST['navigateButton'];
		if (strpos($navigateButton, 'Nej') !== false) {
			// This user didn't take anyone
			mysqli_query_trace($con, "UPDATE ActiveUsers SET DynamicEndPassed='1' WHERE Username='".$username."'");
			
			// Check if all users have passed dynamicend
			$allHavePassed = TRUE;
			$usernames = getUsernamesList($con, "ActiveUsers");
			foreach ($usernames as $name) {
				if (returnFieldForUser("ActiveUsers", 15, $name) == 0) {
					$allHavePassed = FALSE;
				}
			}
			
			if ($allHavePassed) {
				// Update the dynamicEnd property
				mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicEnd='3'");
			}
		} elseif (strpos($navigateButton, 'Ja') !== false) {
			// This user did take someone / become taken
			// Update showUserList
			$showUserList = TRUE;
		}
	}
}

$waitToDynamicMode = returnFieldForUser("ActiveUsers", 11, $username);
$dynamicMode = returnGameField(3);
$dynamicEnd = returnGameField(9);
$dynamicPause = returnGameField(6);

$isBusted = returnFieldForUser("ActiveUsers", 12, $username);
$dynamicEndPassed = returnFieldForUser("ActiveUsers", 15, $username);

?>
<!DOCTYPE html>
<html lang="sv">
	<head>
		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Dynamiskt läge</title>
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
				<h1>
				<?php
					if ($dynamicMode == 0 && $dynamicEnd == 0) {
						if ($isGottmos == 0) {
							echo "Du ska jaga Mr. Gött Mos inom kort!";
						} else if ($isGottmos > 0) {
							echo "Du ska bli jagad av Fubbickarna inom kort!";
						}
					} elseif ($dynamicMode > 0 && $dynamicEnd == 0) {
						if ($isGottmos == 0) {
							echo "Du jagar just nu Mr. Gött Mos!";
						} else if ($isGottmos > 0) {
							echo "Du jagas just nu av Fubbickarna!";
						}
					} elseif ($dynamicEnd > 0) {
						echo "Jakten är över!";
					}
					
				?></h1>
			</header>
			
			
			<?php 
			
			$otherName = "";
			
			if ($isGottmos == 0) {
				$otherName = $samePosNames[1];
			} elseif ($isGottmos > 0) {
				$otherName = $samePosNames[0];
			}
			
			?>
			
			
			<!-- Show rules and advice, and the button that starts the dynamicMode -->
			<?php if ($waitToDynamicMode == 0 && $dynamicMode == 0) { ?>
			
			<?php
				if ($isGottmos == 0) {
					echo "Bra jobbat! Du är nu på samma position som <b>".$otherName."</b>. 
					För att den 5 minuter långa jakten på båda Mr. Gött Mos ska börja, där alla får röra sig fritt,
					så måste både du och <b>".$otherName."</b> trycka på knappen nedan. Innan dess måste ni dock ha
					skakat hand med varandra (eller något), och ni får inte gå iväg innan jakten startat.";
				} else if ($isGottmos > 0) {
					echo "Klant! <b>".ucfirst($otherName)."</b> är nu på samma position som du. Hen och de andra 
					fubbickarna kommer nu att jaga dig i 5 minuter. Klarar du dig utan att någon fubbick nuddar dig,
					så är du kvar i spelet. Annars är du ute.<br>Jakten startar först när både du och <b>".$otherName."</b>
					har tryckt på knappen nedan. Innan dess måste ni dock ha skakat hand med varandra (eller något), och ni får inte gå iväg innan jakten startat.";
				}
			
			?>
			
			<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
				<input type="text" name="username" value="<?php echo $username; ?>" hidden>
				<input type="text" name="specifier" value="startDynamicMode" hidden>
				<input type="submit" class="button" name="navigateButton" value="Starta jakten!">
			</form>
			
			<p style="padding-top: 0;">
			
			<!-- Show a label declaring that the other user hasn't yet pushed the button -->
			<?php } elseif ($waitToDynamicMode > 0 && $dynamicMode == 0) {
				
				echo "<b>".ucfirst($otherName)."</b> har ännu inte tryckt på knappen. Först när hen gör det så startar nedräkningen, och i och med det jakten.<br><br>";
				
				$frequency = 2;
				
			?>
			
			<p style="padding-top: 0;">
				
			<?php } ?>
			
			<?php $dynamicEndNorPause = $dynamicEnd == 0 && $dynamicPause == 0; ?>
			
			<?php if ($dynamicMode > 0) { ?>
				<span id="gmLabel"></span>
				<div id="timer">...</div>
				<?php $frequency = 5; ?>
			<?php } ?>
			
			<!-- Show the countdown, a button that says "i'm touched", or "i touched mr. gm", a button that says "he cheated" -->
			
			<?php if ($dynamicMode > 0 && $dynamicEndNorPause) { ?>
				
				<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post" name="dynamicEndForm">
					<input type="text" name="username" value="<?php echo $username; ?>" hidden>
					<input type="text" name="specifier" value="dynamicEnd" hidden>
					<input type="submit" value="dynamicEnd" hidden>
				</form>
				
				<script>
					
					var mins = 5;  //Set the number of minutes you need
    				var secs = mins * 60;
    				var currentSeconds = 0;
    				var currentMinutes = 0;
    				
    				var startIn = 0;
    				
    				var gmLabel = -5;
    				
    				var gottmos = 0;
    				
    				<?php 
    					$mysqlTime = returnGameField(8);
    					$startTime = intval($mysqlTime);
    					if (is_null($mysqlTime)) {
    						$startTime = ntp_time() + 5000;
							$startTime = floor($startTime / 1000);
							$startTime = $startTime * 1000;
							$startTime = intval($startTime);
							mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicModeStart='".$startTime."'");
							// The gottmos-label should disappear 10 seconds after the timer begins
							$gmDisappear = $startTime + 10000;
							mysqli_query_trace($con, "UPDATE ActiveGame SET GottmosLabelDisappear='".$gmDisappear."'");				
						}
						
						$startIn = $startTime - intval(ntp_time());
						
						$couldRun = TRUE;
						
						if ($startIn < 0) {
							$millisecsLeft = ($startTime + 5 * 60 * 1000) - intval(ntp_time());
							$secsLeft = intval(floor($millisecsLeft / 1000));
							echo "secs = ".$secsLeft.";";
							$startIn = $millisecsLeft % 1000;
						} else {
							// Prevent the next-next if to run now
							$couldRun = FALSE;
						}
						
						$gmDisappear = returnGameField(11);
						
						if ($gmDisappear - intval(ntp_time()) > -5000) {
							$gmLabel = $gmDisappear - intval(ntp_time());
							$gmLabel = intval(floor($gmLabel / 1000));
							echo "gmLabel = ".$gmLabel.";";
							if (!$couldRun) {
								echo "gmLabel = 10;";
							}
						}
						$test;
						if ($gmDisappear - intval(ntp_time()) > 10000 && $couldRun) {
							// The timer starts again, after paused mode, and after they've agreed on nothing's happened
							// The three dots should show up
							// Calculate new secsLeft
							
							$wait = intval(floor(($gmDisappear - intval(ntp_time()) - 10000) / 1000) * 1000);
							
							$startIn += $wait;
							
							$millisecsLeft = ($startTime + 5 * 60 * 1000) - intval(ntp_time());
							$secsLeft = intval(floor($millisecsLeft / 1000));
							$secsLeft = $secsLeft - $wait / 1000;
							echo "secs = ".$secsLeft.";";
							
							echo "gmLabel = 10;";
							
						}
						
						echo "startIn = ".$startIn.";";
						
						echo "gottmos = ".$isGottmos.";";
    				?>
    				
    				var waitToDynamicMode = <?php echo $waitToDynamicMode; ?>;
					var dynamicMode = <?php echo $dynamicMode; ?>;
    				
    				var counter;
    				var heartbeat = (new Date()).getTime();
    				var lastInterval = 0;
    				
    				if (startIn > 1000) {
    					document.getElementById("gmLabel").innerHTML = "Ingen får starta än. (Men gångjakten börjar snart.)";
    				}
    				
    				setTimeout(function() {
    					counter = setInterval(decrement, 1000);
    				}, startIn);
    				
					
    				function decrement() {
        				currentMinutes = Math.floor(secs / 60);
        				currentSeconds = secs % 60;
        				if(currentSeconds <= 9) {
        					currentSeconds = "0" + currentSeconds;
        				}
        				secs--;
        				
        				
        				heartbeat = (new Date()).getTime();
        				// Heartbeat
        				if (lastInterval == 0) {
        					lastInterval = heartbeat;
        				}
        				if (heartbeat - lastInterval > 1100) {
        					if (dynamicMode == "1" && waitToDynamicMode == "0") {
								window.location = '<?php echo $skanepolisen_url; ?>/dynamicMode.php';
							} else {
								window.location = '<?php echo $skanepolisen_url; ?>/dynamicMode.php?samepos=true';
							}
        				}
        				lastInterval = heartbeat;
        				
        				
        				if(secs <= -1) {
        					clearInterval(counter);
        					
        					document.forms["dynamicEndForm"].submit();
        					
        					return;
        				}
        				
        				document.getElementById("timer").innerHTML = currentMinutes + " : " + currentSeconds; //Set the element id you need the time put into.
        				
        				// Fix the gmLabel
        				if (gmLabel > -5) {
        					if (gmLabel > 0) {
        						if (gottmos == 0) {
        							document.getElementById("gmLabel").innerHTML = "Mr. Gött Mos har " + gmLabel + " sekunders försprång.<br>Du får inte starta än.";
        						} else if (gottmos > 0) {
        							document.getElementById("gmLabel").innerHTML = "Du har " + gmLabel + " sekunders försprång.<br>Gå iväg!";
        						}
        					} else if (gmLabel <= 0) {
        						if (gottmos == 0) {
        							document.getElementById("gmLabel").innerHTML = "Du får starta nu!";
        						} else if (gottmos > 0) {
        							document.getElementById("gmLabel").innerHTML = "Fubbickarna får starta nu!";
        						}
        					}
        					
        					gmLabel--;
        					
        				} else if (gmLabel <= -5) {
        					document.getElementById("gmLabel").innerHTML = "";
        					document.getElementById("gmLabel").style.display = "none";
        					document.getElementById("gmLabel").style.padding = "0";
        					document.getElementById("gmLabel").style.margin = "0";
        					gmLabel--;
        				}
    				}
					
				</script>
				
				<!-- FIXA NEDRÄKNING MED JAVASCRIPT, FIXA DIVERSE KNAPPAR!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! ----->
				
				<?php if (!$showUserList && $dynamicEndNorPause) { ?>
					<!-- The standard interface for when the timer is going -->
				
					<!-- busted!-knapp -->
					<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
						<input type="text" name="username" value="<?php echo $username; ?>" hidden>
						<input type="text" name="specifier" value="reportBusted" hidden>
						
						<?php if ($isGottmos > 0) { ?>
							<input type="submit" class="bigbutton" name="navigateButton" value="Jag är tagen!">
						<?php } elseif ($isGottmos == 0) { ?>
							<input type="submit" class="bigbutton" name="navigateButton" value="Mr. GM är tagen!">
						<?php } ?>
					</form>
				
				<?php } elseif ($showUserList && $dynamicEndNorPause) { ?>
					<!-- The user has taken someone / been taken, and will now select who did take them / who they did take -->
					
					<p><?php if ($isGottmos > 0) {
						echo "Vem tog dig?";
					} elseif ($isGottmos == 0) {
						echo "Vem tog du?";
					} ?></p>
					
					<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]);?>" method="post" class="fullwidth">
						<input type="text" name="username" value="<?php echo $username; ?>" hidden>
						<input type="text" name="specifier" value="specifyBusted" hidden>
						
						<table class="radioTable">
							<?php
								$usernames = getUsernamesList($con, "ActiveUsers");
								$gottmosOrFubbickList = array();
								foreach ($usernames as $name) {
									if (returnFieldForUser("ActiveUsers", 5, $name) > 0 && $isGottmos == 0) {
										array_push($gottmosOrFubbickList, $name);
									} elseif (returnFieldForUser("ActiveUsers", 5, $name) == 0 && $isGottmos > 0) {
										array_push($gottmosOrFubbickList, $name);
									}
								}
								$checked = "checked";
								foreach ($gottmosOrFubbickList as $name) {
									echo "<tr>";
									echo '<td class="radioButton"><input type="radio" name="takenOrTakingUser" value="'.$name.'" '.$checked.'></td>';
									echo '<td class="radioLabel">'.$name.'</td>';
									echo "</tr>";
									$checked = "";
								}
							?>
						</table>
				
						<input type="submit" class="leftbutton" name="navigateButton" value="< Gå tillbaka">
						<input type="submit" class="rightbutton" name="navigateButton" value="Rapportera">
					</form>
					
					<br><br>
					
					
				<?php } ?>
				
				
			<?php } elseif ($dynamicMode > 0 && $dynamicPause > 0) { ?>
				
				<!-- Make timer show Pausat läge!, and maybe present the busted dialog (check the IsBusted property on Activeusers) -->
				<script>document.getElementById("timer").innerHTML = "Pausat läge.";</script>
				
				<?php
					$agreementLost = FALSE;
					
					// Get the usernames of the isBusted users (the ones in the fight)
					$usernames = getUsernamesList($con, "ActiveUsers");
					$isBustedUsers = array();
					foreach ($usernames as $name) {
						if (returnFieldForUser("ActiveUsers", 12, $name) > 0) {
							$isBustedUsers[returnFieldForUser("ActiveUsers", 5, $name)] = $name;
							if (returnFieldForUser("ActiveUsers", 12, $name) >= 12) {
								$agreementLost = TRUE;
							}
						}
					}
				?>
				
				<?php if ($isBusted > 0 && $isBusted < 3) { 
					// User has either been busted or has busted someone, as reported by someone else
					// This is the first time, noone has answered 'no', in this forthcoming dialog
					
					$otherUser;
					
					if ($isBusted == 1) { 
						$otherUser = $isBustedUsers[0]; ?>
						
						<?php if (!isset($_SESSION['setBusted'])) { ?>
							<!--important!: the one reporting, typically a fubbick, will not be shown of this-->
							
							<p><?php echo ucfirst($otherUser); ?> har rapporterat att hen tagit (nuddat vid) dig under jakten. Det skulle
								betyda att du åker ut ur spelet. Stämmer det att hen tagit dig? (P.S. Det är inte kul att fuska.)</p>
							
							<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]);?>" method="post" class="fullwidth">
								<input type="text" name="username" value="<?php echo $username; ?>" hidden>
								<input type="text" name="otherUser" value="<?php echo $otherUser; ?>" hidden>
								<input type="text" name="specifier" value="confirmBusted" hidden>
						
								<input type="submit" class="button" name="navigateButton" value="Nej, <?php echo $otherUser; ?> har inte tagit mig">
								<input type="submit" class="button boldbutton" name="navigateButton" value="Ja, <?php echo $otherUser; ?> har tagit mig">
							</form>
							
						<?php } else { ?>
							<!-- the one reporting will be shown some information about the process -->
							<p><?php echo ucfirst($otherUser); ?> måste först godkänna att hen tagit dig. Först då åker du ut.</p>
							
						<?php } ?>
						
					<?php } elseif ($isBusted == 2) { 
						$otherUser = $isBustedUsers[1]; ?>
						
						<?php if (!isset($_SESSION['setBusted'])) { ?>
							<!--important!: the one reporting, typically a fubbick, will not be shown of this-->
						
							<p><?php echo ucfirst($otherUser); ?> har rapporterat att du har tagit (nuddat vid) honom under jakten. Det skulle
								betyda att hen åker ut ur spelet. Stämmer det att du tagit honom?</p>
							
							<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]);?>" method="post" class="fullwidth">
								<input type="text" name="username" value="<?php echo $username; ?>" hidden>
								<input type="text" name="otherUser" value="<?php echo $otherUser; ?>" hidden>
								<input type="text" name="specifier" value="confirmBusted" hidden>
						
								<input type="submit" class="button" name="navigateButton" value="Nej, jag har inte tagit <?php echo $otherUser; ?>">
								<input type="submit" class="button boldbutton" name="navigateButton" value="Ja, jag har tagit <?php echo $otherUser; ?>">
							</form>
								
						<?php } else { ?>
							<!-- the one reporting will be shown some information about the process -->
							<p><?php echo ucfirst($otherUser); ?> måste först godkänna att du tagit honom. Först då åker hen ut.</p>
							
						<?php } ?>
						
					<?php } ?>
				<?php } elseif ($isBusted > 2 && $isBusted < 12) { 
					// They didn't agree, now show this dialog again for both.
					// If thy have been shown three of these dialogs yet didn't agree, then the next elseif will be called
					
					$otherUser;
					
					// Unset setBusted session variable
					if (isset($_SESSION['setBusted'])) {
						unset($_SESSION['setBusted']);
					}
					
					if ($isBusted % 3 == 0) {
						// User hasn't chosen if no or yes, will be shown the dialog
						
						$timeDone = $isBusted / 3;
						$timeLeft = 4 - $timeDone;
						$plural = "";
						if ($timeLeft > 1) {
							$plural = "er";
						} 
						
						if ($isGottmos > 0) {
							// Is gottmos
							$otherUser = $isBustedUsers[0];  ?>
							 
							<p>Du och <?php echo $otherUser; ?> kan inte komma överens i frågan om du blev tagen eller inte. 
								Ni har från och med denna gång <b><?php echo $timeLeft; ?> gång<?php echo $plural; ?></b>
								på er att komma överens. Gör ni inte det, så kallas alla samman för att bråka, mitt på Stortorget.<br>
								Vill du undvika att skämma ut dig, så får du komma överens med <?php echo $otherUser; ?>
								och svara sanningsenligt nedan.</p>
							
							<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]);?>" method="post" class="fullwidth">
								<input type="text" name="username" value="<?php echo $username; ?>" hidden>
								<input type="text" name="otherUser" value="<?php echo $otherUser; ?>" hidden>
								<input type="text" name="specifier" value="tryingToAgree" hidden>
														
								<input type="submit" class="button" name="navigateButton" value="Nej, <?php echo $otherUser; ?> har inte tagit mig">
								<input type="submit" class="button boldbutton" name="navigateButton" value="Ja, <?php echo $otherUser; ?> har tagit mig">
							</form>
							
						<?php } elseif ($isGottmos == 0) {
							// Is a fubbick
							$otherUser = $isBustedUsers[1]; ?>
						
							<p>Du och <?php echo $otherUser; ?> kan inte komma överens i frågan om du blev tagen eller inte. 
								Ni har från och med denna gång <b><?php echo $timeLeft; ?> gång<?php echo $plural; ?></b>
								på er att komma överens. Gör ni inte det, så kallas alla samman för att bråka, mitt på Stortorget.<br>
								Vill du undvika att skämma ut dig, så får du komma överens med <?php echo $otherUser; ?>
								och svara sanningsenligt nedan.</p>
							
							<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]);?>" method="post" class="fullwidth">
								<input type="text" name="username" value="<?php echo $username; ?>" hidden>
								<input type="text" name="otherUser" value="<?php echo $otherUser; ?>" hidden>
								<input type="text" name="specifier" value="tryingToAgree" hidden>
						
								<input type="submit" class="button" name="navigateButton" value="Nej, jag har inte tagit <?php echo $otherUser; ?>">
								<input type="submit" class="button boldbutton" name="navigateButton" value="Ja, jag har tagit <?php echo $otherUser; ?>">
							</form>
								
						<?php } ?>
						
					<?php } elseif ($isBusted % 3 > 0) {
						// This user has chosen, but not the other one
						// Show descriptive text
						
						if ($isGottmos > 0) {
							$otherUser = $isBustedUsers[0];
						} elseif ($isGottmos == 0) {
							$otherUser = $isBustedUsers[1];
						}
						
						echo "<p>".ucfirst($otherUser)." har inte svarat än. Prata med honom, och försök få honom att trycka på samma
						alternativ som du. Du får vänta.</p>";
						
					} ?>
					
				<?php } elseif ($isBusted >= 12) {
					// The two isBusted users couldn't get on with each other
					// Now the dialogs will continue into infinity, and all players are suggested to meet at the Great Square
					// This is shown for the two iSBusted-users
					
					$otherUser;
					
					if ($isBusted % 3 == 0) {
						// User hasn't chosen if no or yes, will be shown the dialog
						
						
						if ($isGottmos > 0) {
							// Is gottmos
							$otherUser = $isBustedUsers[0];  ?>
							 
							<p>Du och <?php echo $otherUser; ?> kan fortfarande inte komma överens i frågan om du blev tagen eller inte.
								Ni borde skämmas!!! Alla spelare ska nu träffas mitt på Stortorget för att diskutera frågan,
								och avgöra den. Svara vad ni kommit överens om nedan.<br>
								Hur kul är det att bråka, i stället för att spela Skånepolisen???</p>
							
							<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]);?>" method="post" class="fullwidth">
								<input type="text" name="username" value="<?php echo $username; ?>" hidden>
								<input type="text" name="otherUser" value="<?php echo $otherUser; ?>" hidden>
								<input type="text" name="specifier" value="tryingToAgree" hidden>
														
								<input type="submit" class="button" name="navigateButton" value="Nej, <?php echo $otherUser; ?> har inte tagit mig">
								<input type="submit" class="button boldbutton" name="navigateButton" value="Ja, <?php echo $otherUser; ?> har tagit mig">
							</form>
							
						<?php } elseif ($isGottmos == 0) {
							// Is a fubbick
							$otherUser = $isBustedUsers[1]; ?>
						
							<p>Du och <?php echo $otherUser; ?> kan fortfarande inte komma överens i frågan om hen blev tagen eller inte.
								Ni borde skämmas!!! Alla spelare ska nu träffas mitt på Stortorget för att diskutera frågan,
								och avgöra den. Svara vad ni kommit överens om nedan.<br>
								Hur kul är det att bråka, i stället för att spela Skånepolisen???</p>
							
							<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]);?>" method="post" class="fullwidth">
								<input type="text" name="username" value="<?php echo $username; ?>" hidden>
								<input type="text" name="otherUser" value="<?php echo $otherUser; ?>" hidden>
								<input type="text" name="specifier" value="tryingToAgree" hidden>
						
								<input type="submit" class="button" name="navigateButton" value="Nej, jag har inte tagit <?php echo $otherUser; ?>">
								<input type="submit" class="button boldbutton" name="navigateButton" value="Ja, jag har tagit <?php echo $otherUser; ?>">
							</form>
								
						<?php } ?>
						
					<?php } elseif ($isBusted % 3 > 0) {
						// This user has chosen, but not the other one
						// Show descriptive text
						
						if ($isGottmos > 0) {
							$otherUser = $isBustedUsers[0];
						} elseif ($isGottmos == 0) {
							$otherUser = $isBustedUsers[1];
						}
						
						echo "<p>".ucfirst($otherUser)." har inte svarat än. Prata med honom, och försök få honom att trycka på samma
						alternativ som du. Du får vänta.</p>";
						
					}
					
				} elseif ($agreementLost) {
					// The two isBusted users couldn't get on with each other
					// Now the dialogs will continue into infinity, and all players are suggested to meet at the Great Square
					// This is shown for those outside the conflict
					?>
					
					<p style="padding-top: 0.4em;">
						<?php echo ucfirst($isBustedUsers[1]); ?> och <?php echo $isBustedUsers[0]; ?> kan inte komma överens
						i fråga om <?php echo $isBustedUsers[1]; ?> blev tagen eller inte av <?php echo $isBustedUsers[0]; ?>.
						Alla spelare måste nu träffas på Stortorget för att avgöra saken. Ni som inte var med i konflikten
						från början ska försöka medla mellan parterna. När ni kommit överens trycker ni på knapparna på
						deras mobiler.
					</p>
					
					<?php
				} else {
					echo '<p style="padding-top:0.4em;">Någon har rapporterat att '.$isBustedUsers[0].' har tagit 
					'.$isBustedUsers[1].', vilket i så fall skulle betyda att '.$isBustedUsers[1].' skulle åka ut.
					Än så länge kan du bara vänta. Du får inte röra dig under tiden timern är pausad.</p>';
				} ?>
				
			<?php } elseif ($dynamicMode > 0 && $dynamicEnd > 0) {
				if ($dynamicPause == 0) { ?>
					
					<?php $pos = returnFieldForUser("ActiveUsers", 4, $username); ?>
					
					<?php if ($dynamicEnd == 2) { ?>
						<!-- Some user has left the game (isOut), show descriptive text and redirect to game.php -->
						
						<?php
							$allUsernames = getAllUsernamesList($con, "ActiveUsers");
							$outUser = "";
							$takingUser = "";
							foreach ($allUsernames as $name) {
								$teamN = returnFieldForUser("ActiveUsers", 5, $name);
								$isBustedN = returnFieldForUser("ActiveUsers", 12, $name);
								if ($teamN > 0 && $isBustedN > 0) {
									$outUser = $name;
								} elseif ($teamN == 0 && $isBustedN > 0) {
									$takingUser = $name;
								}
							}
							
							$hasChangedPos = FALSE;
							
							// Get new position if the user that is out wasn't the one landing at the same position
							if (returnFieldForUser("ActiveUsers", 11, $outUser) == 0 && $isGottmos > 0) {
								getNewPos($username);
								$pos = returnFieldForUser("ActiveUsers", 4, $username);
								
								$oldTurn = returnGameField(5);
								if ($oldTurn == 0) {
									$oldTurnsLeft = returnGameField(1);
									$newTurnsLeft = $oldTurnsLeft - 1;
									mysqli_query_trace($con, "UPDATE ActiveGame SET TurnsLeft='".$newTurnsLeft."'");
									
									// Check if number of turns left is 0, which means that gottmos has won, and the game should end
									if ($newTurnsLeft <= 0) {
										mysqli_query_trace($con, "UPDATE ActiveGame SET EndGame='1'");
										mysqli_query_trace($con, "UPDATE ActiveGame SET GottmosIsWinner='1'");
										mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicMode='0'");
										redirect("gameEnded.php");
										die();
									}
								}
								
								// Gottmos should have the turn
								mysqli_query_trace($con, "UPDATE ActiveGame SET IsGottmosTurn='1'");
								
								$usernames = getUsernamesList($con, "ActiveUsers");
								foreach ($usernames as $name) {
									if (returnFieldForUser("ActiveUsers", 5, $name) > 0) {
										mysqli_query_trace($con, "UPDATE ActiveUsers SET HasTurn='1' WHERE Username='".$name."'");
									} else {
										mysqli_query_trace($con, "UPDATE ActiveUsers SET HasTurn='0' WHERE Username='".$name."'");
									}
								}
								
								$hasChangedPos = TRUE;
							} else {
								$usernames = getUsernamesList($con, "ActiveUsers");
								$nooneGotTurn = TRUE;
								foreach ($usernames as $name) {
									if (returnFieldForUser("ActiveUsers", 9, $name) > 0) {
										$nooneGotTurn = FALSE;
									}
								}
								if ($nooneGotTurn) {
									$oldTurn = returnGameField(5);
									$newTurn = 0;
									if ($oldTurn == 0) {
										$newTurn = 1;
									}
									mysqli_query_trace($con, "UPDATE ActiveGame SET IsGottmosTurn='".$newTurn."'");
									foreach ($usernames as $name) {
										if (returnFieldForUser("ActiveUsers", 5, $name) > 0 && $newTurn == 1) {
											mysqli_query_trace($con, "UPDATE ActiveUsers SET HasTurn='1' WHERE Username='".$name."'");
										} elseif (returnFieldForUser("ActiveUsers", 5, $name) == 0 && $newTurn == 0) {
											mysqli_query_trace($con, "UPDATE ActiveUsers SET HasTurn='1' WHERE Username='".$name."'");
										}
									}
								}
							}
						?>
						
						<p style="padding-top: 0.4em;">Jakten är över. <?php echo ucfirst($outUser); ?> har åkt ut ur spelet, eftersom
						<?php echo $takingUser; ?> tog honom. Spelet kommer nu att fortsätta som vanligt, med undantaget att
						<?php echo $outUser; ?> inte längre är med.
						<?php if ($hasChangedPos) {
							echo "Du har förflyttats till en ny position, så att du inte ska bli tagen direkt när spelet startar.";
						} ?>
						<br>Tryck på knappen nedan när du är tillbaka på position nr
						<?php echo $pos; ?>.</p>
							
						<form action="/dynamicIsBackWait.php" method="post">
							<input type="text" name="username" value="<?php echo $username; ?>" hidden>
							<input type="text" name="specifier" value="isBack" hidden>
							<input type="submit" class="button" value="Jag är tillbaka!">
						</form>
						
						
					<?php } elseif ($dynamicEnd == 1 && $dynamicEndPassed == 0) { ?>
						<!-- FIXA SÅ ATT MAN FÅR EN FRÅGA "BLEV DU TAGEN?"/"TOG DU NÅGON?", FÖLJT AV "VEM?" OM JA ÄR SVARET -->
						
						
						<?php if (!$showUserList) { ?>
							<!-- Ask one last time if they had taken someone / been taken by somone -->
							
							<p>
								<?php if ($isGottmos > 0) {
									echo "Blev du tagen under jakten? Svara sanningsenligt.";
								} elseif ($isGottmos == 0) {
									echo "Tog du någon under jakten? Svara sanningsenligt.";
								} ?>
							</p>
							<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
								<input type="text" name="username" value="<?php echo $username; ?>" hidden>
								<input type="text" name="specifier" value="askIfTaken" hidden>
								
								<input type="submit" class="leftbutton" style="font-size: 1.5em;" name="navigateButton" value="Nej">
								<input type="submit" class="rightbutton" style="font-size: 1.5em;" name="navigateButton" value="Ja">
							</form>
							<br><br><br>
						
						<?php } elseif ($showUserList) { ?>
							
							<!-- This user pressed that they hade taken someone / been taken by somone in the last dialog -->
							<!-- Now get more information -->
							
							<p><?php if ($isGottmos > 0) {
								echo "Vem tog dig?";
							} elseif ($isGottmos == 0) {
								echo "Vem tog du?";
							} ?></p>
							
							<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]);?>" method="post" class="fullwidth">
								<input type="text" name="username" value="<?php echo $username; ?>" hidden>
								<input type="text" name="specifier" value="specifyBusted" hidden>
						
								<table class="radioTable">
									<?php
										$usernames = getUsernamesList($con, "ActiveUsers");
										$gottmosOrFubbickList = array();
										foreach ($usernames as $name) {
											if (returnFieldForUser("ActiveUsers", 5, $name) > 0 && $isGottmos == 0) {
												array_push($gottmosOrFubbickList, $name);
											} elseif (returnFieldForUser("ActiveUsers", 5, $name) == 0 && $isGottmos > 0) {
												array_push($gottmosOrFubbickList, $name);
											}
										}
										$checked = "checked";
										foreach ($gottmosOrFubbickList as $name) {
											echo "<tr>";
											echo '<td class="radioButton"><input type="radio" name="takenOrTakingUser" value="'.$name.'" '.$checked.'></td>';
											echo '<td class="radioLabel">'.$name.'</td>';
											echo "</tr>";
											$checked = "";
										}
									?>
								</table>
						
								<input type="submit" class="leftbutton" name="navigateButton" value="< Gå tillbaka">
								<input type="submit" class="rightbutton" name="navigateButton" value="Rapportera">
							</form>
							
							<br><br>
							
						<?php } ?>
							
							
					<?php } elseif ($dynamicEnd == 1 && $dynamicEndPassed > 0) {
						// This user has answered no in the last dialog
						// This user just has to wait for it
						?>
						
						<p>
							Alla har ännu inte svarat på föregående fråga. Du får vänta tills alla har gjort det.
						</p>
						
						
					<?php } elseif ($dynamicEnd == 3) {
						// All users has answered last dialog, and mr. gottmos wins!!!

						$hasChangedPos = FALSE;
						if ($waitToDynamicMode > 0) {
							// User is on the same position as another user
							if ($isGottmos > 0) {
								// User is the gottmos on the same position as a fubbick
								// This user should get a new position, as close as possible to the current position
								getNewPos($username);
								$pos = returnFieldForUser("ActiveUsers", 4, $username);
								
								$oldTurn = returnGameField(5);
								if ($oldTurn == 0) {
									$oldTurnsLeft = returnGameField(1);
									$newTurnsLeft = $oldTurnsLeft - 1;
									mysqli_query_trace($con, "UPDATE ActiveGame SET TurnsLeft='".$newTurnsLeft."'");
									
									// Check if number of turns left is 0, which means that gottmos has won, and the game should end
									if ($newTurnsLeft <= 0) {
										mysqli_query_trace($con, "UPDATE ActiveGame SET EndGame='1'");
										mysqli_query_trace($con, "UPDATE ActiveGame SET GottmosIsWinner='1'");
										mysqli_query_trace($con, "UPDATE ActiveGame SET DynamicMode='0'");
										redirect("gameEnded.php");
										die();
									}
								}
								
								// Gottmos should have the turn
								mysqli_query_trace($con, "UPDATE ActiveGame SET IsGottmosTurn='1'");
								
								$usernames = getUsernamesList($con, "ActiveUsers");
								foreach ($usernames as $name) {
									if (returnFieldForUser("ActiveUsers", 5, $name) > 0) {
										mysqli_query_trace($con, "UPDATE ActiveUsers SET HasTurn='1' WHERE Username='".$name."'");
									} else {
										mysqli_query_trace($con, "UPDATE ActiveUsers SET HasTurn='0' WHERE Username='".$name."'");
									}
								}
								
								$hasChangedPos = TRUE;
							}
						}
						?>
						<p style="padding-top: 0.4em;">
							<?php 
								if ($isGottmos > 0) {
									echo "Jakten är över. Du klarade dig från fubbickarna, så nu kommer spelet att fortsätta som vanligt. ";
									if ($hasChangedPos) {
										echo "Du har förflyttats till en ny position, så att du inte ska bli tagen direkt när spelet startar. ";
									}
									echo "<br>Tryck på knappen nedan när du är tillbaka på position nr ".$pos.".";
								} else {
									echo "Jakten är över. Ni lyckades inte fånga någon Mr. Gött Mos den här gången. Spelet kommer nu att
									fortsätta, och alla kommer sändas tillbaka till sina tidigare positioner.<br>Tryck på knappen nedan
									när du är tillbaka på position nr ".$pos.".";
								}
							?>
						</p>
						
						<form action="/dynamicIsBackWait.php" method="post">
							<input type="text" name="username" value="<?php echo $username; ?>" hidden>
							<input type="text" name="specifier" value="isBack" hidden>
							<input type="submit" class="button" value="Jag är tillbaka!">
						</form>
							
					<?php } ?>
				<?php } ?>
			<?php } ?>
			
			<?php if ($dynamicEnd > 0) {
				?> <script>document.getElementById("timer").innerHTML = "Slut!";</script> <?php
			} ?>
			
			<?php if ($dynamicMode > 0) { ?>
				<p style="padding-top: 0.4em;">
			<?php } ?>
					<b>Regler</b>:
					<ul>
						<li>Endast gång är tillåten. Man måste alltid ha minst en fot i marken.</li>
						<li>Inga cyklar.</li>
						<li>Man får inte gå mot rött ljus.</li>
						<li>Man får bara gå över gator på övergångsställen.</li>
						<li>Mr. Gött Mos måste alltid få 10 sekunders försprång.</li>
					</ul>
				</p>
			
			</div>
			
			<table id="loggedin">
				<tr>
					<td class="topcell leftcell">Spelare (roll)</td>
					<td class="topcell rightcell">Tidigare position</td>
				</tr>
				<?php 
				$usernames = getAllUsernamesList($con, "ActiveUsers");
				foreach ($usernames as $key=>$name) {
					$isGottmos = returnFieldForUser("ActiveUsers", 5, $name);
					$userIsGottmos = returnFieldForUser("ActiveUsers", 5, $username);
					$position = returnFieldForUser("ActiveUsers", 4, $name);
					$isInTransit = returnFieldForUser("ActiveUsers", 6, $name);
					$isOut = returnFieldForUser("ActiveUsers", 10, $name);
					$isOnSamePos = returnFieldForUser("ActiveUsers", 11, $name);
					
					if ($isGottmos == 1 && $userIsGottmos == 0 && $isOnSamePos == 0) {
						$position = "Hemligt";
					}

					if ($isOut > 0) {
						$position = "Ute ur spelet";
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
						$nameIsOut = returnFieldForUser("ActiveUsers", 10, $name);
						if ($gottmosName > 0) {
							// is gottmos
							if ($nameIsOut == 0) {
								$gmOut = FALSE;
							}
						} elseif ($gottmosName == 0) {
							if ($nameIsOut == 0) {
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
			
		</div>
		
		</div>
		
		<?php
			$usernames = getUsernamesList($con, "ActiveUsers");
			foreach ($usernames as $name) {
				if (returnFieldForUser("ActiveUsers", 12, $name) > 0) {
					$frequency = 3;
				}
			}
		?>
		
		<iframe src="iframe.php?frequency=<?php echo $frequency; ?>"></iframe>
		
	</body>
</html>
<?php mysqli_close($con); ?>