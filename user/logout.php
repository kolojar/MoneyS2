<?php
session_start();
$_SESSION["loggedIn"] = "";
header("Location: ./login.php");
exit();
?>
