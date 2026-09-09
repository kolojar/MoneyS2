<?php
session_start();
$_SESSION["isAdmin"] = false;
header("Location: ./login.php");
exit();
?>
