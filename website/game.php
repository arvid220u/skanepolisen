<?php include 'functions.php';
$username = $_SESSION['username'];
checkLogout();
checkGamePrepared("after");
checkIsThere("after", $username);
checkGameStarted("after", $username);
checkIsOut("before", $username);
checkAllAreOut();
checkGameEnded("before");
checkSamePos("before", $username);
checkDynamicMode("before");


$myTurn = FALSE;
$myTurnNum = returnFieldForUser("ActiveUsers", 9, $username);
if ($myTurnNum > 0) {
	$myTurn = TRUE;
}

$chooseTransport = TRUE;
$chooseGangPos = FALSE;
$chooseCykelPos = FALSE;
$chooseCykelradPos = FALSE;
$inTransitNum = returnFieldForUser("ActiveUsers", 6, $username);
$inTransit = FALSE;
if ($inTransitNum == 0 || $inTransitNum == NULL) {
	// Do nothing
} else if ($inTransitNum > 0) {
	$inTransit = TRUE;
	$chooseTransport = FALSE;
}

// Fix actions if user taps the transport buttons!!!!!
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Declare important specifier, what action should be taken
	$specifier = $_POST['specifier'];
	
	// Fix actions if user taps the transport buttons!!!!!
	if (!$myTurn) {
		redirect("game.php");
		die();
	}
	if ($specifier == "transportChooser") {
		// Show right module
		$transportMethod = $_POST['transport'];
		if ($transportMethod == "Gå!") {
			// Show the available options for Gang
			$chooseGangPos = TRUE;
			$chooseTransport = FALSE;
		} else if ($transportMethod == "Cykla!") {
			// Show the available options for Cykel
			$chooseCykelPos = TRUE;
			$chooseTransport = FALSE;
		} else if ($transportMethod == "Gör en cykelräd!") {
			// Show the available options for Cykelrad
			$chooseCykelradPos = TRUE;
			$chooseTransport = FALSE;
		}
	} else if ($specifier == "positionChooser") {
		$submitButton = $_POST['navigateButton'];
		if ($submitButton == "Annat färdmedel") {
			redirect("game.php");
			die();
		}
		
		// Update the position and the inTransit property!!!!!!!!!!!!!!!!!!!!!!!!!
		$chosenPos = $_POST['position'];
		if (returnFieldForUser("ActiveUsers", 5, $username) == 0) {
			mysqli_query_trace($con, "UPDATE ActiveUsers SET Position='".$chosenPos."' WHERE Username='".$username."'");
			// Remove one biljett from this user, and add one to Mr. Gött Mos
			if ($submitButton == "Gå dit!") {
				// Update this user's cards
				$gangCards = returnFieldForUser("ActiveUsers", 1, $username);
				$gangCards -= 1;
				mysqli_query_trace($con, "UPDATE ActiveUsers SET Gang='".$gangCards."' WHERE Username='".$username."'");
				
				// Update gottmos's turn
				$usernames = getAllUsernamesList($con, "ActiveUsers");
				foreach ($usernames as $name) {
					if (returnFieldForUser("ActiveUsers", 5, $name) > 0) {
						// Give a card to Mr Gott Mos
						$gottMosGangCard = returnFieldForUser("ActiveUsers", 1, $name);
						$gottMosGangCard += 1;
						mysqli_query_trace($con, "UPDATE ActiveUsers SET Gang='".$gottMosGangCard."' WHERE Username='".$name."'");
					}
				}
			} else if ($submitButton == "Cykla dit!") {
				// Update this user's cards
				$cykelCards = returnFieldForUser("ActiveUsers", 2, $username);
				$cykelCards -= 1;
				mysqli_query_trace($con, "UPDATE ActiveUsers SET Cykel='".$cykelCards."' WHERE Username='".$username."'");
				
				// Update gottmos's turn
				$usernames = getAllUsernamesList($con, "ActiveUsers");
				foreach ($usernames as $name) {
					if (returnFieldForUser("ActiveUsers", 5, $name) > 0) {
						// Give a card to Mr Gott Mos
						$gottMosCykelCard = returnFieldForUser("ActiveUsers", 2, $name);
						$gottMosCykelCard += 1;
						mysqli_query_trace($con, "UPDATE ActiveUsers SET Cykel='".$gottMosCykelCard."' WHERE Username='".$name."'");
					}
				}
			} else if ($submitButton == "Flytta dit!") {
				// Update this user's cards
				$cykelradCards = returnFieldForUser("ActiveUsers", 3, $username);
				$cykelradCards -= 1;
				mysqli_query_trace($con, "UPDATE ActiveUsers SET Cykelrad='".$cykelradCards."' WHERE Username='".$username."'");
				
				// Update gottmos's turn
				$usernames = getAllUsernamesList($con, "ActiveUsers");
				foreach ($usernames as $name) {
					if (returnFieldForUser("ActiveUsers", 5, $name) > 0) {
						// Give a card to Mr Gott Mos
						$gottMosCykelradCard = returnFieldForUser("ActiveUsers", 3, $name);
						$gottMosCykelradCard += 1;
						mysqli_query_trace($con, "UPDATE ActiveUsers SET Cykelrad='".$gottMosCykelradCard."' WHERE Username='".$name."'");
					}
				}
			}
			
		} else if (returnFieldForUser("ActiveUsers", 5, $username) > 0) {
			// Remove a card for both Mr. Gatt Moas, but first check security and then update position
			// Update number of cards
			if ($submitButton == "Gå dit!") {
				$gangCards = returnFieldForUser("ActiveUsers", 1, $username);
				// Cancel this if both gott moses have tapped the button at the same time
				if ($gangCards == 0) {
					redirect("game.php");
					die();
				}
				
				// Decrement the available cards
				$gangCards -= 1;
				// Remove a card from both Gottmoses
				mysqli_query_trace($con, "UPDATE ActiveUsers SET Gang='".$gangCards."' WHERE Gottmos='1'");
				
			} else if ($submitButton == "Cykla dit!") {
				$cykelCards = returnFieldForUser("ActiveUsers", 2, $username);
				// Cancel this if both gott moses have tapped the button at the same time
				if ($cykelCards == 0) {
					redirect("game.php");
					die();
				}
				
				// Decrement the available cards
				$cykelCards -= 1;
				// Remove a card from both Gottmoses
				mysqli_query_trace($con, "UPDATE ActiveUsers SET Cykel='".$cykelCards."' WHERE Gottmos='1'");
				
			} else if ($submitButton == "Flytta dit!") {
				$cykelradCards = returnFieldForUser("ActiveUsers", 3, $username);
				// Cancel this if both gott moses have tapped the button at the same time
				if ($cykelradCards == 0) {
					redirect("game.php");
					die();
				}
				// Decrement the available cards
				$cykelradCards -= 1;
				// Remove a card from both Gottmoses
				mysqli_query_trace($con, "UPDATE ActiveUsers SET Cykelrad='".$cykelradCards."' WHERE Gottmos='1'");
				
			}
			
			// Update position
			mysqli_query_trace($con, "UPDATE ActiveUsers SET Position='".$chosenPos."' WHERE Username='".$username."'");
			
		}

		// Set the inTransit property
		mysqli_query_trace($con, "UPDATE ActiveUsers SET InTransit='1' WHERE Username='".$username."'");
		$inTransit = TRUE;
		$chooseTransport = FALSE;
		
	} else if ($specifier == "inTransit") {
		// Update the inTransit property and maybe the isGottMosTurn and TurnsLeft properties!!!!!!!!!!!!!!!!!!!!!!
		mysqli_query_trace($con, "UPDATE ActiveUsers SET InTransit='0' WHERE Username='".$username."'");
		mysqli_query_trace($con, "UPDATE ActiveUsers SET HasTurn='0' WHERE Username='".$username."'");
		
		unset($_SESSION['buttonEnabled']);
		unset($_SESSION['enableWithin']);
		
		$myTurn = FALSE;
		$inTransit = FALSE;
		$chooseTransport = TRUE;
		$hasTurns = returnColumn("ActiveUsers", 9);
		$isOuts = returnColumn("ActiveUsers", 10);
		$allAreDone = TRUE;
		$usernames = getUsernamesList($con, "ActiveUsers");
		foreach ($usernames as $name) {
			if ($hasTurns[$name] > 0 && $isOuts[$name] == 0) {
				$allAreDone = FALSE;
			}
		}
		
		if ($allAreDone) {
			$isGottmosTurn = returnGameField(5);
			if ($isGottmosTurn == 0) {
				mysqli_query_trace($con, "UPDATE ActiveGame SET IsGottmosTurn='1'");
				
				// Decrement the number of turns left
				$numOfTurns = returnGameField(1);
				$numOfTurns -= 1;
				mysqli_query_trace($con, "UPDATE ActiveGame SET TurnsLeft='".$numOfTurns."'");
				
				// Set GottmosShowed to NULL
				mysqli_query_trace($con, "UPDATE ActiveGame SET GottmosShowed=NULL");
				
				// Fix so Gottmosare gets the hasturn property to 1
				$usernames = getUsernamesList($con, "ActiveUsers");
				foreach ($usernames as $name) {
					if (returnFieldForUser("ActiveUsers", 5, $name) > 0) {
						mysqli_query_trace($con, "UPDATE ActiveUsers SET HasTurn='1' WHERE Username='".$name."'");
					}
				}
				
				checkSamePos("before", $username);
				
				// Check if number of turns left is 0, which means that gottmos has won, and the game should end
				if ($numOfTurns <= 0) {
					mysqli_query_trace($con, "UPDATE ActiveGame SET EndGame='1'");
					mysqli_query_trace($con, "UPDATE ActiveGame SET GottmosIsWinner='1'");
					redirect("gameEnded.php");
					die();
				}
				
			} else if ($isGottmosTurn > 0) {
				mysqli_query_trace($con, "UPDATE ActiveGame SET IsGottmosTurn='0'");
				
				// Fix so Fubbickar gets the HasTurn property to 1
				$usernames = getUsernamesList($con, "ActiveUsers");
				foreach ($usernames as $name) {
					if (returnFieldForUser("ActiveUsers", 5, $name) == 0) {
						mysqli_query_trace($con, "UPDATE ActiveUsers SET HasTurn='1' WHERE Username='".$name."'");
					}
				}
				
				checkSamePos("before", $username);
			}
		} else {
			checkSamePos("before", $username);
		}
		
	} elseif ($specifier == "gottmosSpecifyShowed") {
		// This user (a gottmosare) has decided whom of the gottmosers should be shown to the fubbicks
		
		$chosenUser = $_POST['chosenUser'];
		
		mysqli_query_trace($con, "UPDATE ActiveUsers SET GottmosShowedPropose='".$chosenUser."' WHERE Username='".$username."'");
		
		$hasDecided = TRUE;
		$chosenUserList = array();
		$usernames = getUsernamesList($con, "ActiveUsers");
		foreach ($usernames as $name) {
			if (returnFieldForUser("ActiveUsers", 5, $name) > 0) {
				$propose = returnFieldForUser("ActiveUsers", 17, $name);
				if (is_null($propose)) {
					$hasDecided = FALSE;
				} else {
					$chosenUserList[] = $propose;
				}
			}
		}
		
		if ($hasDecided) {
			// Both users have decided whom they think should share their location
			// Compare if both answered the same
			if ($chosenUserList[0] == $chosenUserList[1]) {
				// They answered the same
				// Now add that to the GottmosShowed in ActiveGame
				mysqli_query_trace($con, "UPDATE ActiveGame SET GottmosShowed='".$chosenUserList[0]."'");
			} else {
				// They didn't answer same
				mysqli_query_trace($con, "UPDATE ActiveGame SET GottmosShowed='mustGetOn'");
				
			}
			
			foreach ($usernames as $name) {
				mysqli_query_trace($con, "UPDATE ActiveUsers SET GottmosShowedPropose=NULL WHERE Username='".$name."'");
			}
			
		}
		
	}
	
	// Prevent page to update again after load
	$usersResult = mysqli_query($con, "SELECT Modified FROM ActiveUsers");
	$gameResult = mysqli_query($con, "SELECT Modified FROM ActiveGame");
	$usersArray = mysqli_fetch_row($usersResult);
	$gameArray = mysqli_fetch_row($gameResult);
	$usersModifiedNew = $usersArray[0];
	$gameModifiedNew = $gameArray[0];
	$_SESSION['usersModifiedOld'] = $usersModifiedNew;
	$_SESSION['gameModifiedOld'] = $gameModifiedNew;
	
}

$frequencyOverride = NULL;

?>
<!DOCTYPE html>
<html lang="sv">
	<head>
		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>Skånepolisen!</title>
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
				<h1><?php
				$turnLabel = "";
				$turnOrNot = returnGameField(5);
				$gottmos = returnFieldForUser("ActiveUsers", 5, $username);
				if ($turnOrNot == 0) {
					// Fubbickarnas turn
					if ($gottmos > 0) {
						$turnLabel = "Det är Fubbickarnas tur!";
					} else if ($gottmos == 0 && $myTurn) {
						$turnLabel = "Det är din tur!";
					} else if ($gottmos == 0 && !$myTurn) {
						$turnLabel = "Alla Fubbickar har inte flyttat än.";
						
					}
				} else if ($turnOrNot > 0){
					// Mr. Gött Mos ^2 tur
					if ($gottmos > 0 && $myTurn) {
						$turnLabel = "Det är din tur!";
					} else if ($gottmos > 0 && !$myTurn) {
						$turnLabel = "Din medspelare Mr. Gött Mos har inte flyttat än.";
					} else {
						$turnLabel = "Det är Mr. Gött Mos tur!";
					} 
				}
				echo $turnLabel;
				?></h1>
				<?php if (returnFieldForUser("ActiveUsers", 6, $username) == 0) {?>
				<h3>Du är på position nr <b>
				<?php } else if (returnFieldForUser("ActiveUsers", 6, $username) > 0) {?>
				<h3>Du ska till position nr <b><?php } ?>
				<?php 
				$position = returnFieldForUser("ActiveUsers", 4, $username);
				echo $position;
				?></b>.</h3>
				
				<?php
					// Fixa omgångsvarning och om Mr. Gött Mos ska visa sig.
					// En Mr. Gött Mos ska visa sig varannan omgång, de bestämmer vem
					
					$turnNum = returnGameField(1);
					$turnNum = 22 - $turnNum;
					$shouldBecomeVisible = FALSE;
					$turnsToVisible = 0;
					
					if ($turnNum % 2 == 0) {
						$shouldBecomeVisible = TRUE;
					}
					
					if ($shouldBecomeVisible) {
						$turnInfo = "Omgång ".$turnNum." av 21. Mr. Gött Mos ska visa sig under den här omgången.";
					} else {
						$turnInfo = "Omgång ".$turnNum." av 21. Mr. Gött Mos ska visa sig nästa omgång.";
					}
					
					
					if ($turnNum == 21) {
						// Fixa varning om att det bara är 1 gång kvar innan gottmos vinner
						$turnWarning = "Detta är den sista omgången! ";
						$isGottmos = returnFieldForUser("ActiveUsers", 5, $username);
						if ($isGottmos > 0) {
							$turnWarning .= "Blir du inte tagen nu, så vinner du spelet!";
						} elseif ($isGottmos == 0) {
							$turnWarning .= "Tar ni inte Mr. Gött Mos nu, så förlorar ni!";
						}
						echo '<br><div class="error">'.$turnWarning.'</div>';
					}
					
					// Fixa varning högst upp här, om Mr. Gött Mos ska visa sig den här omgången
					$gottmosPosWarning = '';
					
					if ($shouldBecomeVisible) {
						$gottmosPosWarning .= '<br><div class="error">';
						$cardInfo = "";
						if (returnFieldForUser("ActiveUsers", 5, $username) == 0) {
							// This user is a fubbick
							$gottmosShowed = returnGameField(12);
							
							if ($gottmosShowed == "mustGetOn") {
								$gottmosShowed = NULL;
							}
							
							// Find out if gottmos is alone
							$usernames = getUsernamesList($con, "ActiveUsers");
							$count = 0;
							$gottmosIsAlone = FALSE;
							foreach ($usernames as $name) {
								if (returnFieldForUser("ActiveUsers", 5, $name) > 0) {
									$count += 1;
								}
							}
							if ($count == 1) {
								// Gottmos is alone, and automatically selected
								$gottmosIsAlone = TRUE;
							}
							
							if (!is_null($gottmosShowed)) {
								// The gottmosers has chosen a user to show
								// Check if that user has moved
								$hasTurn = returnFieldForUser("ActiveUsers", 9, $gottmosShowed);
								if ($hasTurn == 0) {
									// This user has moved, and will show his position now
									
									$pos = returnFieldForUser("ActiveUsers", 4, $gottmosShowed);
									$gang = returnFieldForUser("ActiveUsers", 1, $gottmosShowed);
									$gangname = "gång-biljetter";
									if ($gang == 1) {
										$gangname = "gång-biljett";
									}
									$cykel = returnFieldForUser("ActiveUsers", 2, $gottmosShowed);
									$cykelname = "cykel-biljetter";
									if ($cykel == 1) {
										$cykelname = "cykel-biljett";
									}
									$cykelrad = returnFieldForUser("ActiveUsers", 3, $gottmosShowed);
									$cykelradname = "cykelräd-biljetter";
									if ($cykelrad == 1) {
										$cykelradname = "cykelräd-biljett";
									}
									
									if (!$gottmosIsAlone) {
										$cardInfo = "<br>De har ".$gang." ".$gangname.", ".$cykel." ".$cykelname." och ".$cykelrad." ".$cykelradname." tillsammans.<br>";
									} else {
										$cardInfo = "<br>Han har ".$gang." ".$gangname.", ".$cykel." ".$cykelname." och ".$cykelrad." ".$cykelradname.".<br>";
									}
									
									$gottmosPosWarning .= ucfirst($gottmosShowed)." är på position nr <b>".$pos."</b>.<br>";
									
								} else {
									if (!$gottmosIsAlone) {
										// This user hasn't moved, show informative text
										$gottmosPosWarning .= "Mr. Gött Mos har valt att ".$gottmosShowed." ska visa sig den här omgången. Hen har dock inte flyttat än.";
									} else {
										// Gottmos is automatically selected
										$gottmosPosWarning .= ucfirst($gottmosShowed)." ska visa sig den här omgången. Hen har dock inte flyttat än.";
									}
								}
							
							} else {
								if (!$gottmosIsAlone) {
									// Gottmosers haven't chosen who to show
									$gottmosPosWarning .= "Mr. Gött Mos har ännu inte valt vem av dem som ska visa sig den här omgången.";
								} else {
									// Gottmos is automatically selected
									$gottmosPosWarning .= ucfirst($gottmosShowed)." ska visa sig den här omgången. Hen har dock inte flyttat än.";
								}
							}
						
						} else if (returnFieldForUser("ActiveUsers", 5, $username) > 0) {
							// User is gottmos, will be shown warning that position will be shared or is shared,
							// Or shown dialog to choose who to show
							
							$gottmosShowed = returnGameField(12);
							
							
							$gottmosIsAlone = FALSE;
							$usernames = getUsernamesList($con, "ActiveUsers");
							$gottmosList = array();
							$otherGottmos = "";
							foreach ($usernames as $name) {
								if (returnFieldForUser("ActiveUsers", 5, $name) > 0) {
									$gottmosList[] = $name;
									if ($name != $username) {
										$otherGottmos = $name;
									}
								}
							}
							if (count($gottmosList) < 2) {
								$gottmosIsAlone = TRUE;
							}
							
							
							if (is_null($gottmosShowed) || $gottmosShowed == "mustGetOn") {
								
								if (!$gottmosIsAlone) {
									
									$hasAnswered = !is_null(returnFieldForUser("ActiveUsers", 17, $username));
									
									if (!$hasAnswered) {
												
										// They haven't yet decided who should be showed to the fubbicks
										// now show dialog to choose
										
										
										if ($gottmosShowed == "mustGetOn") {
											// They have been shown this dialog earlier
											$gottmosPosWarning .= 'Ni måste vara överens om vem som ska visa sig. Prata med varandra, och svara nedan.<br><br>';
										} else {
											$gottmosPosWarning .= 'En av er ska visa sig den här omgången. Vem tycker du ska göra det?<br><br>';
										}
										
										$gottmosPosWarning .= '</div>';
										
										$gottmosPosWarning .= '<form action="'.htmlspecialchars($_SERVER["REQUEST_URI"]).'" method="post" class="fullwidth">';
										$gottmosPosWarning .= '<input type="text" name="username" value="'.$username.'" hidden>';
										$gottmosPosWarning .= '<input type="text" name="specifier" value="gottmosSpecifyShowed" hidden>';
										$gottmosPosWarning .= '<table class="radioTable">';
										
										$checked = "checked";
										foreach ($gottmosList as $name) {
											$gottmosPosWarning .= "<tr>";
											$gottmosPosWarning .= '<td class="radioButton"><input type="radio" name="chosenUser" value="'.$name.'" '.$checked.'></td>';
											$gottmosPosWarning .= '<td class="radioLabel">'.$name.'</td>';
											$gottmosPosWarning .= "</tr>";
											$checked = "";
										}
										
										$gottmosPosWarning .= '</table>';
										$gottmosPosWarning .= '<input type="submit" class="button" name="navigateButton" value="Bestäm">';
										$gottmosPosWarning .= '</form>';
									
									} else {
										// This user has decided, but not the other user.
										$gottmosPosWarning .= ucfirst($otherGottmos)." har ännu inte bestämt sig för vem som ska visa sig.";
										
										$frequencyOverride = 8;
									}
								
								} else {
									
									// This user is the only gottmos, then auto select him and show appropriate text
									
									mysqli_query_trace($con, "UPDATE ActiveGame SET GottmosShowed='".$username."'");
									
									$gottmosPosWarning .= "Eftersom du är den enda Mr. Gött Mos, så kommer positionen du flyttar till att visas för Fubbickarna den här omgången!<br>";
									
								}
								
							} else {
								// Show warning that either the position is being shared, or will be shared
								
								if ($username == $gottmosShowed) {
									// This user will share information
								
									if (returnFieldForUser("ActiveUsers", 9, $gottmosShowed) == 0) {
										// User has already moved
										if (!$gottmosIsAlone) {
											$gottmosPosWarning .= "Din position och era gemensamma biljetter visas nu för Fubbickarna.<br>";
										} else {
											$gottmosPosWarning .= "Din position och dina biljetter visas nu för Fubbickarna.<br>";
										}
										
									} else if (returnFieldForUser("ActiveUsers", 9, $gottmosShowed) > 0) {
										// User hasn't moved
										if (!$gottmosIsAlone) {
											$gottmosPosWarning .= "Positionen du flyttar till kommer att visas för Fubbickarna den här omgången, eftersom ni bestämde det.<br>";
										} else {
											$gottmosPosWarning .= "Positionen du flyttar till kommer att visas för Fubbickarna den här omgången.<br>";
										}
									}
									
								} else {
									// the otherGottmos will share info
									
									if (returnFieldForUser("ActiveUsers", 9, $gottmosShowed) == 0) {
										// User has already moved
										$gottmosPosWarning .= ucfirst($gottmosShowed)."s position och era gemensamma biljetter visas nu för Fubbickarna.<br>";
									} else if (returnFieldForUser("ActiveUsers", 9, $gottmosShowed) > 0) {
										// User hasn't moved
										$gottmosPosWarning .= "Positionen ".$gottmosShowed." flyttar till kommer att visas för Fubbickarna den här omgången, eftersom ni bestämde det.<br>";
									}
									
								}
								
							}
						}
						$gottmosPosWarning .= $cardInfo;
						if (strpos($gottmosPosWarning, "</div>") === false) {
							$gottmosPosWarning .= "</div>";
						}
					}
					
					echo $gottmosPosWarning;
					
				?>
				
			</header>
			
			<?php if($chooseTransport) { ?>
				
			<?php 
				$disabledNum = 0;
			
			?>
			<br>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
				<input type="text" name="specifier" value="transportChooser" hidden>
				<table id="travel">
					<tr>
						<td class="topcell left3cell">Färdmedel</td>
						<td class="topcell mid3cell">Möjliga positioner</td>
						<td class="topcell right3cell">Biljetter kvar</td>
					</tr>
					<tr>
						<!-- Gång -->
						<td class="left3cell"><input type="submit" class="button" name="transport" value="Gå!" <?php 
							$positions = listRelatedPoints(intval($position), "GangRelations");
							$posLabel = "Inga";
							foreach ($positions as $pos) {
								if ($posLabel == "Inga") {
									$posLabel = $pos;
								} else {
									$posLabel .= ", ".$pos;
								}
							}
							
							$gangsLeft = returnFieldForUser("ActiveUsers", 1, $username);
							$gangCardLabel = "Inga";
							if ($gangsLeft > 0) {
								$gangCardLabel = "".$gangsLeft."";
							}
							
							if (!$myTurn) {
								echo "disabled";
							} else if ($posLabel == "Inga") {
								$disabledNum += 1;
								echo "disabled";
							} else if ($gangCardLabel == "Inga") {
								$disabledNum += 1;
								echo "disabled";
							}
							?>></td>
						<td class="mid3cell"><?php 
							echo $posLabel;
							?>
						</td>
						<td class="right3cell"><?php
							echo $gangCardLabel;
							?>
						</td>
					</tr>
					<tr>
						<!-- Cykel -->
						<td class="left3cell"><input type="submit" class="button" name="transport" value="Cykla!" <?php 
							$positions = listRelatedPoints(intval($position), "CykelRelations");
							$cykelLabel = "Inga";
							foreach ($positions as $pos) {
								if ($cykelLabel == "Inga") {
									$cykelLabel = $pos;
								} else {
									$cykelLabel .= ", ".$pos;
								}
							}
							
							$cykelsLeft = returnFieldForUser("ActiveUsers", 2, $username);
							$cykelCardLabel = "Inga";
							if ($cykelsLeft > 0) {
								$cykelCardLabel = "".$cykelsLeft."";
							}
							
							if (!$myTurn) {
								echo "disabled";
							} else if ($cykelLabel == "Inga") {
								$disabledNum += 1;
								echo "disabled";
							} else if ($cykelCardLabel == "Inga") {
								$disabledNum += 1;
								echo "disabled";
							}
							?>></td>
						<td class="mid3cell"><?php 
							echo $cykelLabel;
							?>
						</td>
						<td class="right3cell"><?php 
							echo $cykelCardLabel;
							?>
						</td>
					</tr>
					<tr>
						<!-- Cykelräd -->
						<td class="left3cell"><input type="submit" class="button" name="transport" value="Gör en cykelräd!" <?php 
							$positions = listRelatedPoints(intval($position), "CykelradRelations");
							$cykelradLabel = "Inga";
							foreach ($positions as $pos) {
								if ($cykelradLabel == "Inga") {
									$cykelradLabel = $pos;
								} else {
									$cykelradLabel .= ", ".$pos;
								}
							}
							
							$cykelradsLeft = returnFieldForUser("ActiveUsers", 3, $username);
							$cykelradCardLabel = "Inga";
							if ($cykelradsLeft > 0) {
								$cykelradCardLabel = "".$cykelradsLeft."";
							}
							
							if (!$myTurn) {
								echo "disabled";
							} else if ($cykelradLabel == "Inga") {
								$disabledNum += 1;
								echo "disabled";
							} else if ($cykelradCardLabel == "Inga") {
								$disabledNum += 1;
								echo "disabled";
							}
							?>></td>
						<td class="mid3cell"><?php 
							echo $cykelradLabel;
							?>
						</td>
						<td class="right3cell"><?php 
							echo $cykelradCardLabel;
							?>
						</td>
					</tr>
				</table>
			</form><br>
			
			<?php 
				if ($disabledNum == 3) {
					// User can't move even if it's his turn
					// Send this user to the isOut.php page with a form and the post method
					mysqli_query_trace($con, "UPDATE ActiveUsers SET IsOut='1' WHERE Username='".$username."'");
					mysqli_query_trace($con, "UPDATE ActiveUsers SET HasTurn='0' WHERE Username='".$username."'");
					
					redirect("isOut.php");
					die();
				}
			
			?>
			
			<?php } else if ($chooseGangPos) { ?><br>
			
			<p>Välj position att gå till:</p>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" class="fullwidth">
				<input type="text" name="specifier" value="positionChooser" hidden>
				
				<table class="radioTable">
					<?php
					$positions = listRelatedPoints(intval($position), "GangRelations");
					$checked = "checked";
					foreach ($positions as $pos) {
						echo "<tr>";
						echo '<td class="radioButton"><input type="radio" name="position" value="'.$pos.'" '.$checked.'></td>';
						echo '<td class="radioLabel">'.$pos.'</td>';
						$checked = "";
						echo "</tr>";
					}
					?>
				</table>
				
				<input type="submit" class="leftbutton" name="navigateButton" value="Annat färdmedel" <?php 
				$cykelradPos = listRelatedPoints(intval($position), "CykelradRelations");
				$cykelPos = listRelatedPoints(intval($position), "CykelRelations");
				if (count($cykelradPos) == 0 && count($cykelPos) == 0) {
					echo "disabled";
				}
				?>>
				<input type="submit" class="rightbutton" name="navigateButton" value="Gå dit!" 
				<?php
					$gmNotShared = FALSE;
					if ($shouldBecomeVisible) {
						if (returnFieldForUser("ActiveUsers", 5, $username) > 0) {
							$gottmosShowed = returnGameField(12);
							if (is_null($gottmosShowed) || $gottmosShowed == "mustGetOn") {
								$gmNotShared = TRUE;
							}
						}
					}
					if ($gmNotShared) {
						echo "disabled";
					}
				?>>
				<?php
					if ($gmNotShared) {
						echo '<div class="error">Ni måste först bestämma vem som ska visa sig, innan du kan förflytta dig.</div>';
					}
				?>
			</form><br><br>
			
			<!-- Don't update page, because that would reset transport choice -->
			<?php $script = ""; ?>
			
			<?php } else if ($chooseCykelPos) { ?><br>
			
			<p>Välj position att cykla till:</p>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" class="fullwidth">
				<input type="text" name="specifier" value="positionChooser" hidden>
				
				<table class="radioTable">
					<?php
					$positions = listRelatedPoints(intval($position), "CykelRelations");
					$checked = "checked";
					foreach ($positions as $pos) {
						echo "<tr>";
						echo '<td class="radioButton"><input type="radio" name="position" value="'.$pos.'" '.$checked.'></td>';
						echo '<td class="radioLabel">'.$pos.'</td>';
						$checked = "";
						echo "</tr>";
					}
					?>
				</table>
				
				<input type="submit" class="leftbutton" name="navigateButton" value="Annat färdmedel">
				<input type="submit" class="rightbutton" name="navigateButton" value="Cykla dit!"
				<?php
					$gmNotShared = FALSE;
					if ($shouldBecomeVisible) {
						if (returnFieldForUser("ActiveUsers", 5, $username) > 0) {
							$gottmosShowed = returnGameField(12);
							if (is_null($gottmosShowed) || $gottmosShowed == "mustGetOn") {
								$gmNotShared = TRUE;
							}
						}
					}
					if ($gmNotShared) {
						echo "disabled";
					}
				?>>
				<?php
					if ($gmNotShared) {
						echo '<div class="error">Ni måste först bestämma vem som ska visa sig, innan du kan förflytta dig.</div>';
					}
				?>
			</form><br><br>
			
			<!-- Don't update page, because that would reset transport choice -->
			<?php $script = ""; ?>
			
			<?php } else if ($chooseCykelradPos) { ?><br>
			
			<p>Välj position att göra en cykelräd till:</p>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" class="fullwidth">
				<input type="text" name="specifier" value="positionChooser" hidden>
				
				<table class="radioTable">
					<?php
					$positions = listRelatedPoints(intval($position), "CykelradRelations");
					$checked = "checked";
					foreach ($positions as $pos) {
						echo "<tr>";
						echo '<td class="radioButton"><input type="radio" name="position" value="'.$pos.'" '.$checked.'></td>';
						echo '<td class="radioLabel">'.$pos.'</td>';
						$checked = "";
						echo "</tr>";
					}
					?>
				</table>
				
				<input type="submit" class="leftbutton" name="navigateButton" value="Annat färdmedel">
				<input type="submit" class="rightbutton" name="navigateButton" value="Flytta dit!"
				<?php
					$gmNotShared = FALSE;
					if ($shouldBecomeVisible) {
						if (returnFieldForUser("ActiveUsers", 5, $username) > 0) {
							$gottmosShowed = returnGameField(12);
							if (is_null($gottmosShowed) || $gottmosShowed == "mustGetOn") {
								$gmNotShared = TRUE;
							}
						}
					}
					if ($gmNotShared) {
						echo "disabled";
					}
				?>>
				<?php
					if ($gmNotShared) {
						echo '<div class="error">Ni måste först bestämma vem som ska visa sig, innan du kan förflytta dig.</div>';
					}
				?>
			</form><br><br>
			
			<?php } else if ($inTransit) { ?>
			
			<?php
				$isGottmos = returnFieldForUser("ActiveUsers", 5, $username);
				
				if ($isGottmos > 0) {
					
					if (!isset($_SESSION['buttonEnabled'])) {
						$_SESSION['buttonEnabled'] = FALSE;
					}
					if (!isset($_SESSION['enableWithin'])) {
						// Enable button within 30 seconds
						$_SESSION['enableWithin'] = intval(ntp_time()) + 45000;
					}
					
					$timeLeft = $_SESSION['enableWithin'] - intval(ntp_time());
					
					if ($timeLeft < 0) {
						$_SESSION['buttonEnabled'] = TRUE;
					}
				}
				
			?>
			
			<form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]);?>" method="post" class="fullwidth">
				<input type="text" name="specifier" value="inTransit" hidden>
				<input type="submit" class="button" name="navigateButton" value="Jag är framme!" 
				<?php if ($isGottmos > 0 && !$_SESSION['buttonEnabled']) { echo " disabled"; } ?>>
			</form>
			
			<?php if ($isGottmos > 0 && !$_SESSION['buttonEnabled']) {
				echo '<p class="error">Fubbickarna måste få en chans att se vilket färdmedel du har använt.
				Därför måste du vänta i minst 45 sekunder.</p><br>';
			} ?>
			
			<!-- Disable button for 45 seconds for the gottmosers -->
			<?php if ($isGottmos > 0 && !$_SESSION['buttonEnabled']) { ?>
				<script>
					setTimeout(function() {
    						window.location = '<?php echo $_SERVER["REQUEST_URI"]; ?>';
    					}, <?php echo $timeLeft; ?>);
				</script>
			<?php } ?>
			
			<?php } ?>
			
			<?php // Get the transportation method for each user
				$usernames = getUsernamesList($con, "ActiveUsers");
				$sqlCardVals = array();
				foreach ($usernames as $name) {
					$gangs = returnFieldForUser("ActiveUsers", 1, $name);
					$cykels = returnFieldForUser("ActiveUsers", 2, $name);
					$cykelrads = returnFieldForUser("ActiveUsers", 3, $name);
					$inTransitChange = returnFieldForUser("ActiveUsers", 6, $name);
					array_push($sqlCardVals, array($gangs, $cykels, $cykelrads, $name, $inTransitChange));
				}
				
				if (is_null($_SESSION['usedCards'])) {
					$_SESSION['usedCards'] = array();
				}
				if (is_null($_SESSION['cardVals'])) {
					$_SESSION['cardVals'] = $sqlCardVals;
				}
				
				$usedCards = $_SESSION['usedCards'];
				$cardVals = $_SESSION['cardVals'];
				
				$tempCardArray = array('<span style="color:white;">Går</span>', '<span style="color:rgb(30, 230, 70);">Cyklar</span>', '<span style="color:rgb(60, 100, 255);">Gör en cykelräd</span>');
				for ($i = 0; $i < 3; $i++) {
					foreach ($sqlCardVals as $sqlCardVals1) {
						$cardVals1 = array();
						foreach ($cardVals as $cardVals1temp) {
							if ($cardVals1temp[3] == $sqlCardVals1[3]) {
								$cardVals1 = $cardVals1temp;
							}
						}
						
						if ($sqlCardVals1[$i] < $cardVals1[$i]) {
							if ($sqlCardVals1[4] > 0 && $cardVals1[4] == 0) {
								$usedCards[$cardVals1[3]] = $tempCardArray[$i];
							}
						}
					}
				}
				
				$_SESSION['cardVals'] = $sqlCardVals;
				$_SESSION['usedCards'] = $usedCards;
			?>
			
			</div>
			
			<table id="loggedin">
				<tr>
					<td class="topcell leftcell">Spelare (roll)</td>
					<td class="topcell rightcell">Position</td>
				</tr>
				<?php 
				$usernames = getAllUsernamesList($con, "ActiveUsers");
				foreach ($usernames as $key=>$name) {
					$isGottmos = returnFieldForUser("ActiveUsers", 5, $name);
					$userIsGottmos = returnFieldForUser("ActiveUsers", 5, $username);
					$position = returnFieldForUser("ActiveUsers", 4, $name);
					$isInTransit = returnFieldForUser("ActiveUsers", 6, $name);
					$isOut = returnFieldForUser("ActiveUsers", 10, $name);
					if ($isGottmos == 1 && $userIsGottmos == 0) {
						if ($shouldBecomeVisible && returnFieldForUser("ActiveUsers", 9, $name) > 0) {
							$position = "Hemligt";
						} else if (!$shouldBecomeVisible) {
							$position = "Hemligt";
						}
					}
					if ($isInTransit > 0) {
						$usedCards = $_SESSION['usedCards'];
						foreach ($usedCards as $cardName=>$cardVal) {
							if ($cardName == $name) {
								$position = $cardVal;
							}
						}
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
			
			<div class="indented">
			
			<p><?php echo $turnInfo; ?></p>
			
			</div>
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
			
    	</div>
    	</div>
    	
    	<!-- Iframe checking for updates on the database. Frequency is 17 seconds. -->
		
		<?php
			$frequency = 17;
			$samePosArray = returnAnySamePos();
			if (count($samePosArray) > 0) {
				$frequency = 2;
			}
			
			if (!is_null($frequencyOverride)) {
				$frequency = $frequencyOverride;
			}
		?>
		
		<iframe src="iframe.php?frequency=<?php echo $frequency; ?>"></iframe>
    	
	</body>
</html>
<?php mysqli_close($con); ?>