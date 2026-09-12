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

$LETTERS = "0123456789abcdefghijklmnopqstuvwxyzABCDEFGHIJLKMNOPQERSTUVWXYZ";
function ConvertToBase62(int $value): string {
    global $LETTERS;
    $result = "";
    do {
        $result = $LETTERS[$value % 62] . $result;
        $value = intdiv($value, 62);
    } while ($value > 0);
    return $result;
}

function ConvertFromBase62(string $value): int {
    global $LETTERS;
    $num = 0;
    for ($i = 0, $len = strlen($value); $i < $len; $i++) {
        $num = $num * 62 + strpos($LETTERS, $value[$i]);
    }
    return $num;
}
