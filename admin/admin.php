<?php
session_start();
if(!isset($_SESSION["isAdmin"]) || $_SESSION["isAdmin"] != true) {
    header("Location: ./login.php");
    exit();
}
if(!isset($_GET["view"]) || ($_GET["view"] != "active" && $_GET["view"] != "archived")) {
    $_GET["view"] = "active";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin panel</title>
    <link rel="stylesheet" href="../formWebScripts/css/formStyle.css" />
    <link rel="stylesheet" href="../assets/style.css" />
    <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json" />
</head>
<body>
    <header>
        <div class='formButtonBoxHolder'>
        <div class='formButtonBox'>
            <a href="?view=active"><button class='<?php echo $_GET["view"] == "active" ? "formOkColor" : "formInfoColor" ?>'>Active sheets</button></a>
            <a href="?view=archived"><button class='<?php echo $_GET["view"] == "archived" ? "formOkColor" : "formInfoColor" ?>'>Archived sheets</button></a>
        </div>
        <div class='formButtonBox formJustifyRight'>
            <a href="./logout.php"><button class='formErrorColor'>Logout</button></a>
        </div>
        </div>
    </header>
    <main>
        <?php

        ?>
    </main>
    <footer>

    </footer>
</body>
</html>
