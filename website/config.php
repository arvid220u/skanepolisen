<?php
$mysql_host = getenv('DB_HOST');
$mysql_user = getenv('DB_USER');
$mysql_password = getenv('DB_PASS');
$mysql_database = getenv('DB_NAME');
$skanepolisen_url = getenv('SKANEPOLISEN_URL');
$skanepolisen_email = 'no-reply@skanepolisen.arvid.xyz';
$sendgrid_path = '../sendgrid-php/sendgrid-php.php';
$allow_any_email_signup = true;
?>