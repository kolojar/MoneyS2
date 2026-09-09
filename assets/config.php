<?php
//Admin password
$_SERVER["adminPassword"] = "admin";

// DB login info
$servername = "127.0.0.1:3307";
$username = 'root';
$password = "root";
$dbname = "moneys2";

//Connect
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");
if ($conn->connect_error) {
    die("Error connecting to DB: " . $conn->connect_error);
}
