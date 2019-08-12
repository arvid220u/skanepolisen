<?php include 'functions.php';
$username = $_SESSION['username'];
checkLogout();
checkGamePrepared("after");
checkIsOut("before", $username);

mysqli_query_trace($con, "UPDATE ActiveUsers SET IsOut='2' WHERE Username='".$username."'");
mysqli_close($con);

redirect("isOut.php");

?>
