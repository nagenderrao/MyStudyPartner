<?php
$username = "dbo497457451";
$password = "ser_12345";
$hostname = "db497457451.db.1and1.com"; 
$database = "db497457451";

/*$username = "root";
$password = "";
$hostname = "localhost"; 
$database = "youtube_api";*/

$con = mysql_connect($hostname, $username, $password) or die("Unable to connect to MySQL");
$selected_db = mysql_select_db($database,$con) or die("Could not select Database"); 
?>