<?php include 'config.php';
	
	// This file should be as small as possible, therfor it's not calling functions.php, and must implement own functions
	
	session_start();
    $con = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_database);
	$username = $_SESSION['username'];
	
	$frequency = $_GET['frequency'];
	
	function databaseHasChanged() {
		$con = $GLOBALS['con'];
		
		if (!isset($_SESSION['usersModifiedOld'])) {
			$_SESSION['usersModifiedOld'] = 0;
		}
		if (!isset($_SESSION['gameModifiedOld'])) {
			$_SESSION['gameModifiedOld'] = 0;
		}
		
		$usersResult = mysqli_query($con, "SELECT Modified FROM ActiveUsers");
		$gameResult = mysqli_query($con, "SELECT Modified FROM ActiveGame");
		mysqli_close($con);
		$usersArray = mysqli_fetch_row($usersResult);
		$gameArray = mysqli_fetch_row($gameResult);
		$usersModifiedNew = $usersArray[0];
		$gameModifiedNew = $gameArray[0];
		
		if ($usersModifiedNew != $_SESSION['usersModifiedOld'] || $gameModifiedNew != $_SESSION['gameModifiedOld']) {
			$_SESSION['usersModifiedOld'] = $usersModifiedNew;
			$_SESSION['gameModifiedOld'] = $gameModifiedNew;
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	if (databaseHasChanged()) {
		echo "<!DOCTYPE html>";
   		echo "<head>";
   		echo "<title>.</title>";
    	echo "<script>window.top.location.href = window.top.location;</script>";
    	echo "</head>";
    	echo "<body></body></html>";
	} else {
		header("Refresh:".$frequency."; URL=".$_SERVER["REQUEST_URI"]."");
	}
	
?>