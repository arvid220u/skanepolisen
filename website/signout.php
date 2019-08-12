<?php include 'functions.php';
$username = $_SESSION['username'];
checkLogout();
checkIsOut("before", $username);
checkGameStarted("before", $username);

$usernames = getAllUsernamesList($con, "ActiveUsers");
foreach ($usernames as $username) {
	if ($username == $_SESSION['username']) {
		mysqli_query_trace($con, "DELETE FROM ActiveUsers WHERE Username='".$username."'");
	}
}

if ($_SESSION['authenticated']) {
	session_destroy();
}

redirect("index.php");

?>
<?php mysqli_close($con); ?>