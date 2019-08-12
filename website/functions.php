<?php include 'config.php';
$con = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_database);

$cssLink = "main_v52.css";

// Start or resume session
session_start(); 
// Extend cookie life time by an hour
$cookieLifetime = 60 * 60; // An hour in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);


// New mysqli_query named mysqli_query_trace, that updates the Modified property of the table
// Every mysqli_query other than those in this function should call mysqli_query_trace
function mysqli_query_trace($con, $query) {
	// Calls original mysqli_query
	$value = mysqli_query($con, $query);
	
	// Checks if table has been modified
	if (strpos($query, 'UPDATE') !== false || strpos($query, 'INSERT') !== false || strpos($query, 'DELETE') !== false) {
		// Only update modified if it's actually been modified for ActiveGame and ActiveUsers
		if (strpos($query, 'ActiveGame') !== false) {
			$oldModified = returnGameField(10);
			$newModified = $oldModified + 1;
			mysqli_query($con, "UPDATE ActiveGame SET Modified='".$newModified."'");
			$_SESSION['gameModifiedOld'] = $newModified;
			
		} elseif (strpos($query, 'ActiveUsers') !== false) {
			$oldModified = returnFieldForUser("ActiveUsers", 14, $_SESSION['username']);
			$newModified = intval($oldModified) + 1;
			mysqli_query($con, "UPDATE ActiveUsers SET Modified='".$newModified."'");
			$_SESSION['usersModifiedOld'] = $newModified;
		}
	}
	
	return $value;
}



// Checks if logged in, redirects to right page
function checkLogin() {
	$usernames = getAllUsernamesList($GLOBALS['con'], "ActiveUsers");
	$bool = FALSE;
	foreach ($usernames as $name) {
		if ($name == $_SESSION['username']) {
			$bool = TRUE;
		}
	}
	if ($_SESSION['authenticated'] && $bool) {
		redirect("chooseSide.php");
		die();
	}
}
// Checks if logged out and try to visit a secure page, redirects to home
function checkLogout() {
	if (!$_SESSION['authenticated']) {
		redirect("index.php");
		die();
	}
	$usernames = getAllUsernamesList($GLOBALS['con'], "ActiveUsers");
	$bool = TRUE;
	foreach ($usernames as $name) {
		if ($name == $_SESSION['username']) {
			$bool = FALSE;
		}
	}
	if ($bool) {
		redirect("index.php");
		die();
	}
}
// Checks if chosen side, redirects to chooseSide.php
function checkChosen($when) {
	$con = $GLOBALS['con'];
	$username = mysqli_real_escape_string($con, $_SESSION['username']);
	$result = mysqli_query_trace($con, "SELECT * FROM ActiveUsers WHERE Username='".$username."'");
	$row = mysqli_fetch_array($result);
	$isGottMos = $row['Gottmos'];
	if ($when == "after") {
		if ($isGottMos == NULL) {
			redirect("chooseSide.php");
			die();
		}
	} elseif ($when == "before") {
		if ($isGottMos != NULL) {
			redirect("lobby.php");
			die();
		}
	}
}
// Checks if chosen side, redirects to chooseSide.php
function checkGamePrepared($when, $username = "username") {
	$con = $GLOBALS['con'];
	$search = mysqli_query_trace($con, "SELECT * FROM ActiveGame");
	$numRows = mysqli_num_rows($search);
	if ($when == "after") {
		if ($numRows == 0) {
			redirect("lobby.php");
			die();
		}
	} elseif ($when == "before") {
		if ($username != "username") {
			$position = returnFieldForUser("ActiveUsers", 4, $username);
			if ($position != NULL) {
				if ($numRows > 0) {
					redirect("prepareGame.php");
					die();
				}
			}
		}
	}
}
// Checks if the user has tapped to wait to prepare, fixes the player limit
function checkUserPrepared($when, $usrname) {
	$con = $GLOBALS['con'];
	$prepared = returnFieldForUser("ActiveUsers", 7, $usrname);
	if ($when == "after") {
		if ($prepared == 0 || $prepared == NULL) {
			redirect("lobby.php");
			die();
		}
	} elseif ($when == "before") {
		if ($prepared > 0) {
			redirect("waitToPrepareGame.php");
			die();
		}
	}
}
// Check if user has tapped the "is here" button, and waits to start the game
function checkIsThere($when, $username) {
	$con = $GLOBALS['con'];
	$isThere = returnFieldForUser("ActiveUsers", 8, $username);
	if ($when == "after") {
		if ($isThere == 0 || $isThere == NULL) {
			redirect("prepareGame.php");
			die();
		}
	} elseif ($when == "before") {
		if ($isThere > 0) {
			redirect("waitToGame.php");
			die();
		}
	}
}
// Checks if the game has started
function checkGameStarted($when, $username) {
	$gameStarted = returnGameField(2);
	if ($when == "after") {
		if ($gameStarted == 0 || $gameStarted == NULL) {
			redirect("prepareGame.php");
			die();
		}
	} elseif ($when == "before") {
		if ($gameStarted > 0) {
			redirect("game.php");
			die();
		}
	}
}
// Checks whether this user is out
function checkIsOut($when, $username) {
	$isOut = returnFieldForUser("ActiveUsers", 10, $username);
	if ($when == "after") {
		if ($isOut == 0) {
			redirect("game.php");
			die();
		}
	} else if ($when == "before") {
		if ($isOut > 0) {
			redirect("isOut.php");
			die();
		}
	}
}
// Checks if all members of a team is out. If that evaluates to true, it will shut down the game.
function checkAllAreOut() {
	$con = $GLOBALS['con'];
	$gottMosAreOut = TRUE;
	$fubbicksAreOut = TRUE;
	$usernames = getAllUsernamesList($con, "ActiveUsers");
	foreach ($usernames as $name) {
		if (returnFieldForUser("ActiveUsers", 5, $name) == 0) {
			// Is a fubbick
			if (returnFieldForUser("ActiveUsers", 10, $name) == 0) {
				// This fubbick is living
				$fubbicksAreOut = FALSE;
			}
		} else if (returnFieldForUser("ActiveUsers", 5, $name)) {
			// Is a Gottmosare
			if (returnFieldForUser("ActiveUsers", 10, $name) == 0) {
				// This gotymosaer is living
				$gottMosAreOut = FALSE;
			}
		}
	}
	
	if ($gottMosAreOut) {
		mysqli_query_trace($con, "UPDATE ActiveGame SET EndGame='1'");
		mysqli_query_trace($con, "UPDATE ActiveGame SET GottmosIsWinner='0'");
		redirect("gameEnded.php");
		die();
	} else if ($fubbicksAreOut) {
		mysqli_query_trace($con, "UPDATE ActiveGame SET EndGame='1'");
		mysqli_query_trace($con, "UPDATE ActiveGame SET GottmosIsWinner='1'");
		redirect("gameEnded.php");
		die();
	}
}
// Checks whether the game has already ended
function checkGameEnded($when) {
	$gameEnded = returnGameField(4);
	if ($when == "after") {
		if ($gameEnded == 0) {
			redirect("game.php");
			die();
		}
	} else if ($when == "before") {
		if ($gameEnded > 0) {
			redirect("gameEnded.php");
			die();
		}
	}
}
// Checks the DynamicMode property of the ActiveGame table
function checkDynamicMode($when) {
	$dynamicMode = returnGameField(3);
	if ($when == "after") {
		if ($dynamicMode == 0) {
			redirect("game.php");
			die();
		}
	} else if ($when == "before") {
		if ($dynamicMode > 0) {
			redirect("dynamicMode.php");
			die();
		}
	}
}
// Checks if this user is on the same location as a player of the other team, if true sets the DynamicMode column of the ActiveGame table
function checkSamePos($when, $username) {
	$con = $GLOBALS['con'];
	$userPos = returnFieldForUser("ActiveUsers", 4, $username);
	$isGottmos = returnFieldForUser("ActiveUsers", 5, $username);
	$samePos = FALSE;
	$samePosArray = returnAnySamePos();
	$sameNameArray = array_keys($samePosArray);
	foreach ($samePosArray as $samePosName=>$samePosPos) {
		if ($samePosName == $username) {
			if (returnFieldForUser("ActiveUsers", 6, $sameNameArray[0]) == 0 && returnFieldForUser("ActiveUsers", 6, $sameNameArray[1]) == 0) {
				$samePos = TRUE;
			}
		}
	}
	
	if ($when == "before") {
		if ($samePos) {
			redirect("dynamicMode.php?samepos=true");
			die();
		}
	} elseif ($when == "after") {
		if (!$samePos) {
			redirect("dynamicMode.php");
			die();
		}
	}
}
// Checks if this user has clicked the isback-button in dynamicMode.php. redirects to appropriate page
function checkDynamicIsBack($when, $username) {
	$isBack = returnFieldForUser("ActiveUsers", 13, $username);
	if ($when == "before") {
		if ($isBack > 0) {
			redirect("dynamicIsBackWait.php");
			die();
		}
	} elseif ($when == "after") {
		if ($isBack == 0) {
			redirect("dynamicMode.php");
			die();
		}
	}
}

function redirect($url) {
	mysqli_close($GLOBALS['con']);
	if (strpos($url,'http://skanepolisen.org') === false && strpos($url,'/') === false) {
		$lastUrl = $url;
		$url = "http://skanepolisen.org/".$lastUrl;
	} elseif (strpos($url,'http://skanepolisen.org') === false && strpos($url,'/') !== false) {
		$lastUrl = $url;
		$url = "http://skanepolisen.org".$lastUrl;
	}
	header("Location: $url");
}

function getUsernamesList($con, $table) {
	$usernames = returnColumn($table, 0);
	if ($table == "ActiveUsers") {
		$censoredUsernames = array();
		foreach ($usernames as $nameKey=>$nameVal) {
			if (returnFieldForUser("ActiveUsers", 10, $nameVal) == 0) {
				$censoredUsernames[$nameKey] = $nameVal;
			}
		}
		return $censoredUsernames;
	} else {
		return $usernames;
	}
}

function getAllUsernamesList($con, $table) {
	$usernames = returnColumn($table, 0);
	return $usernames;
}

function addToActiveUsers($con, $username) {
	$bool = TRUE;
	
	$usernames = getAllUsernamesList($con, "ActiveUsers");
	foreach ($usernames as $key=>$name) {
		if ($name == $username) {
			$bool = FALSE;
		}
	}
	if ($bool) {
		$query = "INSERT INTO ActiveUsers (Username) VALUES('".$username."')";
		mysqli_query_trace($con, $query);
	}
}

// Checks how many active users there are of each team, returns one value
function checkUsers($gottmosOrFubbick) {
	$gottmosUsers = 0;
	$fubbickUsers = 0;
	$activeUsers = mysqli_query_trace($GLOBALS['con'], "SELECT * FROM ActiveUsers");
	while ($row = mysqli_fetch_array($activeUsers)) {
		if ($row['Gottmos'] > 0) {
			$gottmosUsers++;
		} elseif ($row['Gottmos'] == NULL) {
			// Nothing happens
		} elseif ($row['Gottmos'] == 0) {
			$fubbickUsers++;
		}
	}
	if ($gottmosOrFubbick == "gottmos") {
		return $gottmosUsers;
	} elseif ($gottmosOrFubbick == "fubbick") {
		return $fubbickUsers;
	} else {
		return NULL;
	}
}

function returnColumn($table, $field) {
	$con = $GLOBALS['con'];
	$column = array();
	$search = mysqli_query_trace($con, "SELECT * FROM ".$table."");
	while ($row = mysqli_fetch_row($search)) {
		$column[$row[0]] = $row[$field];
	}
	return $column;
}

// Returns one specific field for one sepcific user from one specific table
function returnFieldForUser($table, $field, $username) {
	$wholeField = returnColumn($table, $field);
	return $wholeField[$username];
}

// Check if two points on the map are related in any way (gang, cykel or cykelrad)
function checkIfRelated($point1, $point2) {
	$bool = FALSE;
	
	$relatedGangPoints = listRelatedPoints($point1, "GangRelations");
	$relatedCykelPoints = listRelatedPoints($point1, "CykelRelations");
	$relatedCykelradPoints = listRelatedPoints($point1, "CykelradRelations");
	$relatedPoints = array_merge($relatedGangPoints, $relatedCykelPoints, $relatedCykelradPoints);
	foreach ($relatedPoints as $relatedPoint) {
		if ($relatedPoint == $point2) {
			$bool = TRUE;
		}
	}
	
	if ($point1 == $point2) {
		$bool = TRUE;
	}
	
	return $bool;
}

function listRelatedPoints($point, $table) {
	$con = $GLOBALS['con'];
	$relatedPoints = array();
	$search = mysqli_query_trace($con, "SELECT * FROM ".$table." WHERE Stop1='".$point."'");
	while ($row = mysqli_fetch_row($search)) {
		$relatedPoints[] = $row[1];
	}
	return $relatedPoints;
}

// Sets a random position for the user, and changes the cards if it's a Gottmosare
function setPositionAndCards($username, $secondTime = 0) {
	$con = $GLOBALS['con'];
	
	$userPosition = returnFieldForUser("ActiveUsers", 4, $username);
	
	if ($userPosition == NULL || $secondTime > 0) {
		// Set position
		$position = rand (1, 52);
		// Get other players' positions
		$otherPositions = returnColumn("ActiveUsers", 4);
		foreach ($otherPositions as $name=>$otherPos) {
			if (checkIfRelated($position, $otherPos)) {
				$isGottmosUsername = returnFieldForUser("ActiveUsers", 5, $username);
				$isGottmosName = returnFieldForUser("ActiveUsers", 5, $name);
				if ($isGottmosName != $isGottmosUsername || $position == $otherPos) {
					setPositionAndCards($username, 1);
					die();
				}
			}
		}
		mysqli_query_trace($con, "UPDATE ActiveUsers SET Position='".$position."' WHERE Username='".$username."'");
	}
	
	// Fix cards if user is Mr. Gott Mos
	if (returnFieldForUser("ActiveUsers", 5, $username) > 0) {
		mysqli_query_trace($con, "UPDATE ActiveUsers SET Gang='6' WHERE Username='".$username."'");
		mysqli_query_trace($con, "UPDATE ActiveUsers SET Cykel='4' WHERE Username='".$username."'");
		mysqli_query_trace($con, "UPDATE ActiveUsers SET Cykelrad='3' WHERE Username='".$username."'");
		mysqli_query_trace($con, "UPDATE ActiveUsers SET HasTurn='1' WHERE Username='".$username."'");
	}
}

// Return field from ActiveGame table
function returnGameField($field) {
	$con = $GLOBALS['con'];
	$column = array();
	$search = mysqli_query_trace($con, "SELECT * FROM ActiveGame");
	$count = 0;
	while ($row = mysqli_fetch_row($search)) {
		$column[$count] = $row[$field];
		$count += 1;
	}
	return $column[0];
}

function getLengthOfTable($table) {
	$con = $GLOBALS['con'];
	$search = mysqli_query_trace($con, "SELECT * FROM ".$table."");
	$length = mysqli_num_rows($search);
	return $length; 
}

// Gets the positions in an array for either the fubbick or the gottmos
function getTeamPos($team) {
	$con = $GLOBALS['con'];
	$usernames = getUsernamesList($con, "ActiveUsers");
	$posArray = array();
	foreach ($usernames as $name) {
		if ($team == "fubbick" && returnFieldForUser("ActiveUsers", 5, $name) == 0) {
			$namePos = returnFieldForUser("ActiveUsers", 4, $name);
			$posArray[$name] = $namePos;
		} elseif ($team == "gottmos" && returnFieldForUser("ActiveUsers", 5, $name) > 0) {
			$namePos = returnFieldForUser("ActiveUsers", 4, $name);
			$posArray[$name] = $namePos;
		}
	}
	
	return $posArray;
}

// Returns an array with usernames if two users are on the same position from different teams
function returnAnySamePos() {
	$returnArray = array();
	
	$fubbickPoses = getTeamPos("fubbick");
	$gottmosPoses = getTeamPos("gottmos");
	foreach ($fubbickPoses as $fubbickName=>$fubbickPos) {
		foreach ($gottmosPoses as $gottmosName=>$gottmosPos) {
			if ($gottmosPos == $fubbickPos) {
				$returnArray[$fubbickName] = $fubbickPos;
				$returnArray[$gottmosName] = $gottmosPos;
			}
		}
	}
	
	return $returnArray;
}

// Gets a new position for the mr gottmos who is at the same position as a fubbick
function getNewPos($username) {
	$con = $GLOBALS['con'];
	
	// Check if he is on the same position (security check)
	$samePos = returnAnySamePos();
	if (empty($samePos)) {
		die();
	}
	
	// get the positions of all fubbicks
	$fubbickPos = getTeamPos("fubbick");
	
	$oldPos = returnFieldForUser("ActiveUsers", 4, $username);
	$relatedPos = listRelatedPoints($oldPos, "GangRelations");
	$considerPos = $relatedPos;
	foreach ($relatedPos as $index=>$relPos) {
		foreach ($fubbickPos as $name => $fubbPos) {
			if ($fubbPos == $relPos) {
				unset($considerPos[$index]);
			}
		}
	}
	
	$newPos = null;
	$i = 0;
	while (!isset($newPos)) {
		$conPos = $considerPos[$i];
		if (!isset($conPos)) {
			$newPos = $considerPos[0];
		}
		$relatedPos = listRelatedPoints($conPos, "GangRelations");
		$isGood = TRUE;
		foreach ($relatedPos as $index=>$relPos) {
			foreach ($fubbickPos as $name => $fubbPos) {
				if ($fubbPos == $relPos && $fubbPos != $oldPos) {
					$isGood = FALSE;
				}
			}
		}
		
		if ($isGood) {
			$newPos = $conPos;
		}
	}
	
	// Set newPos as username's position
	mysqli_query_trace($con, "UPDATE ActiveUsers SET Position='".$newPos."' WHERE Username='".$username."'");
	
	// Set PreparedForDynamicMode to be 0 for all
	mysqli_query_trace($con, "UPDATE ActiveUsers SET PreparedForDynamicMode='0'");
}

// Return time as synced by the server
function ntp_time() {
	return round(microtime(true) * 1000);
}

// Returns a salted version of the password hash for extra security
function hashAndSalt($password) {
	// Salt password
	// Take first charcter, convert it to a number, and concatenate it to the hash
	$salt = ord($password);
	$md5pass = md5($password);
	$saltedHash = $md5pass.strval($salt);
	return $saltedHash;
}

function endGame() {
	// Only to remember that I should clear the ActiveUsers table at the end of the game
	global $con;
	mysqli_query_trace($con, "DELETE FROM ActiveUsers");
}

?>