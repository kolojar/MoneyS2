<?php
// Přístupové údaje k databázi (Localhost)
$servername = "127.0.0.1:3307";
$username = 'root';
$password = "root";
$dbname = "moneys2";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");
if ($conn->connect_error) {
    die("Chyba při připojování k databázi: " . $conn->connect_error);
}
