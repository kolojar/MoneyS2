<!DOCTYPE html>
<?php
/** @var \mysqli $conn */
require __DIR__ . "/assets/sharedFunctions.php";
session_start();

?>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>List of sheets</title>
	<link rel="stylesheet" href="../formWebScripts/css/formStyle.css" />
        <link rel="stylesheet" href="../assets/style.css" />
        <meta name="form-icons-main-db" content="../formWebScripts/formIcons.json" />
        <meta name="form-locales-main" content="../formWebScripts/locales/" />
</head>
<body>
    <header>
        <div class='formButtonBoxHolder'>
        <div class='formButtonBox'>
            <h1>Index of sheets</h1>
        </div>
        <div class='formButtonBox formJustifyRight'>
            <a href="./user/newSheet.php"><button class='formOkColor'>Create new sheet</button></a>
            <a href="./admin/login.php"><button class='formWarnColor'>Admin panel</button></a>
        </div>
        </div>
    </header>
    <main>
        <?php
        echo "<div class='tableScrollHolder'>";
        echo "<table class='styledTable' id='mainTable'>";
        echo "<tr>";
        echo "<th>Name</th>";
        echo "<th>Last activity</th>";
        echo "<th>Actions</th>";
        echo "</tr>";

        //Get active sheets
        $result = $conn->query("SELECT * FROM `_tables` ORDER BY `updated` DESC");
        while ($row = $result->fetch_assoc()) {
            echo "<tr rid='" . $row["id_tables"] . "'>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td class='timeFormat'>" . $row["updated"] . "</td>";
            echo "<td class='formButtonBoxTable'>";
            echo "<a href='../user/sheet.php?id=". ConvertToBase62($row["id_tables"]) . "'><button class='formInfoColor formButtonInline'>Open</button></a>";
            echo "</td></tr>";
        }
        echo "</table></div>";
        ?>
    </main>
    <footer>
        <div class='formButtonBoxHolder'>
        <div class='formButtonBox'>
            <h1>Manual open:</h1>
        </div>
        <div class='formButtonBox formJustifyRight'>
        <button class='formInfoColor' id='openById'>Open by id</button>
         <button class='formInfoColor' id='openByName'>Open by name</button>
        </div>
    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="./index.js"></script>
</html>
