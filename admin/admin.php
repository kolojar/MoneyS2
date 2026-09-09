<?php
session_start();
if (!isset($_SESSION["isAdmin"]) || $_SESSION["isAdmin"] != true) {
    header("Location: ./login.php");
    exit();
}

/** @var \mysqli $conn */
require __DIR__ . "/../assets/config.php";
if (!isset($_GET["view"]) || ($_GET["view"] != "active" && $_GET["view"] != "archived")) {
    $_GET["view"] = "active";
}

function encodeURIComponent($str) {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
}

//Process POST
if (isset($_POST["action"])) {
    if(!isset($_POST["sheet"])) {
        //No sheet
        http_response_code(400);
        echo "Missing sheet value.";
        exit();
    }
    $sheet = $_POST["sheet"];
    switch($_POST["action"]) {
        case "archive": {
            //Run SQL
            $stmt = $conn->prepare("UPDATE `tables` SET archived=1 WHERE name=?");
            if(!$stmt->bind_param("s", $sheet) || !$stmt->execute() || $stmt->affected_rows == 0) {
                http_response_code(400);
                echo "Invalid sheet name.";
                exit();
            }
            http_response_code(201);
            echo "ok";
            exit();
        }
        case "delete": {
            //Run SQL
            $stmt = $conn->prepare("DELETE FROM `tables` WHERE name=?");
            if(!$stmt->bind_param("s", $sheet) || !$stmt->execute() || $stmt->affected_rows == 0) {
                http_response_code(400);
                echo "Invalid sheet name.";
                exit();
            }
            http_response_code(201);
            echo "ok";
            exit();
        }
    }
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
            <a href="?view=active"><button class='<?php echo $_GET["view"] == "active" ? "formOkColor" : "formInfoColor"; ?>'>Active sheets</button></a>
            <a href="?view=archived"><button class='<?php echo $_GET["view"] == "archived" ? "formOkColor" : "formInfoColor"; ?>'>Archived sheets</button></a>
        </div>
        <div class='formButtonBox formJustifyRight'>
            <a href="./user/newSheet.php"><button class='formWarnColor'>Create new sheet</button></a>
            <a href="./logout.php"><button class='formErrorColor'>Logout</button></a>
        </div>
        </div>
    </header>
    <main>
        <?php if ($_GET["view"] == "active") {
            //Echo table header
            echo "<h1>Active sheets</h1>";
            echo "<table class='styledTable'>";
            echo "<tr>";
            echo "<th>Name</th>";
            echo "<th>Last activity</th>";
            echo "<th>Actions</th>";
            echo "</tr>";

            //Get active sheets
            $result = $conn->query("SELECT * FROM `tables` WHERE archived=0");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["name"] . "</td>";
                echo "<td class='timeFormat'>" . $row["updated"] . "</td>";
                echo "<td class='formButtonBoxTable'>";
                echo "<a href='../user/sheet.php?id=". encodeURIComponent($row["name"]) . "'><button class='formInfoColor formButtonInline'>Open</button></a>";
                echo "<button class='archiveSheetBtn formWarnColor formButtonInline' sheet='" . encodeURIComponent($row["name"]) . "'>Archive</button>";
                echo "<button class='deleteSheetBtn formErrorColor formButtonInline' sheet='" . encodeURIComponent($row["name"]) . "'>Delete</button>";
                echo "</td></tr>";
            }
            echo "</table>";
        } else {
            //Echo table header
            echo "<h1>Archived sheets</h1>";
            echo "<table class='styledTable'>";
            echo "<tr>";
            echo "<th>Name</th>";
            echo "<th>Last activity</th>";
            echo "<th>Actions</th>";
            echo "</tr>";

            //Get active sheets
            $result = $conn->query("SELECT * FROM `tables` WHERE archived=1");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["name"] . "</td>";
                echo "<td class='timeFormat'>" . $row["updated"] . "</td>";
                echo "<td class='formButtonBoxTable'>";
                echo "<a href='../user/sheet.php?id=". encodeURIComponent($row["name"]) . "'><button class='formInfoColor formButtonInline'>Open</button></a>";
                echo "<button class='deleteSheetBtn formErrorColor formButtonInline' sheet='" . encodeURIComponent($row["name"]) . "'>Delete</button>";
                echo "</td></tr>";
            }
            echo "</table>";
        } ?>
    </main>
    <footer>

    </footer>
</body>
<script type="module" src="../formWebScripts/js/formScript.js"></script>
<script type="module" src="./admin.js"></script>
</html>
