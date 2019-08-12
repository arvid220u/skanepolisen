<?php include 'functions.php';
checkLogin();

$userErr = $passwordErr = $trickErr = "";
$email = $username = $password = "";

$emails = array("arvid"=>"arvid.lunnemark@gmail.com", "oscar"=>"owwe118@gmail.com", 
"oskar"=>"oskar.a.mansson@gmail.com", "ernst"=>"ernst.hajen@gmail.com", 
"melker"=>"melkergeorgson@gmail.com", "simon"=>"harnqvist.simon@gmail.com"); 
$userEmailsAreActive = array();
$userEmails = array();
$result = mysqli_query_trace($con, "SELECT * FROM RegisteredUsers");
while($row = mysqli_fetch_row($result)) {
	if ($row[3] != 0) {
		$userEmailsAreActive[] = $row[2];
	}
	$userEmails[] = $row[2];
}
foreach ($emails as $key=>$emailk) {
	foreach ($userEmailsAreActive as $emailx) {
		if ($emailk == $emailx) {
			unset($emails[$key]);
		}
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
	if (empty($_POST['username'])) {
		$userErr = "Du måste fylla i ett användarnamn";
	} else {
		if (strlen($_POST['username']) < 4) {
			$userErr = "Användarnamnet måste vara minst 4 tecken långt";
		} elseif (strlen($_POST['username']) > 15) {
			$userErr = "Användarnamnet måste vara kortare än 15 tecken";
		} else {
			$username = htmlspecialchars($_POST['username']);
			$query = "SELECT Username FROM RegisteredUsers WHERE Username='".$username."'";
			$search = mysqli_query_trace($con, $query);
			$match = mysqli_num_rows($search);
			if ($match > 0) {
				$userErr = "Användarnamnet är redan taget";
				$username = "";
			}
		}
	}
	
	if (empty($_POST['password'])) {
		$passwordErr = "Du måste fylla i ett lösenord";
	} else {
		if (strlen($_POST['password']) < 8) {
			$passwordErr = "Lösenordet måste vara minst 8 tecken långt";
		} else {
			$password = htmlspecialchars($_POST['password']);
		}
	}
	
	if ($_POST['trick'] != "arvidlunnemark") {
		$trickErr = "Du är inte en människa!";
	}
	
	$email = $_POST['emails'];
	
	if ($userErr == "" and $passwordErr == "" and $trickErr == "") {
		
		foreach ($userEmails as $userEmail) {
			if ($email == $userEmail) {
				mysqli_query_trace($con, "DELETE FROM RegisteredUsers WHERE Email='$userEmail'");
			}
		}
		
		
		
		// fixa databas och skicka mail
		$hash = md5( rand(1, 1000) );
		$password = hashAndSalt($password);
		
		$array = array($username, $password, $email, $hash);
		foreach ($array as $item) {
			$item = mysqli_real_escape_string($con, $item);
		}
		$query = "INSERT INTO RegisteredUsers (Username, Password, Email, Hash) 
		VALUES('$username', '$password', '$email', '$hash')";
		mysqli_query_trace($con, $query);
		
		// Skicka bekräftelsemail
		$working = mail($email, "Bekräfta ditt konto hos Skånepolisen", "Välkommen till Skånepolisen. Klicka på länken nedan för att bekräfta ditt konto.

-------------------------
Användarnamn: $username
Lösenord: Hemligt
-------------------------

Länk för att bekräfta ditt konto:
$skanepolisen_url/verify.php?username=$username&hash=$hash

Därefter kan du logga in som vanligt, och börja spela Skånepolisen!!!


Var det inte du som skapade kontot? Då kan du bara ignorera meddelandet.", "From: Skånepolisen <info@skanepolisen.org>");
		if ($working) {
			redirect("verificationEmailSent.php");
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

		<title>Skapa konto</title>
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
				<h1>Skapa konto</h1>
			</header>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
				<div class="select">E-postadress: <select name="emails">
					<?php
					foreach ($emails as $key=>$mail) {
						echo "<option value='$mail'>$mail</option>";
					}
					?>
				</select></div>
				<br>
				Användarnamn: <input type="text" name="username" value="<?php echo $username;?>"><?php if ($userErr != "") { echo "<br>"; }?><span class="error"><?php echo $userErr;?></span><br><br>
				Lösenord: <input type="password" name="password"><?php if ($passwordErr != "") { echo "<br>"; }?><span class="error"><?php echo $passwordErr;?></span><?php if ($trickErr != "") { echo "br>"; } ?>
				<input type="hidden" name="trick" value="arvidlunnemark"><span class="error"><?php echo $trickErr;?></span>
				<input type="submit" class="button" value="Bekräfta e-postadress">
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